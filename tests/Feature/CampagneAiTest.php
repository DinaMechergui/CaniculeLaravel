<?php
namespace Tests\Feature;

use App\Models\Conseil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CampagneAiTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_ai_generates_a_valid_draft_without_calling_a_real_provider(): void
    {
        config(['services.openrouter.key'=>'test-key','services.openrouter.model'=>'test-model']);
        Http::fake(['openrouter.ai/*'=>Http::response(['choices'=>[['message'=>['content'=>json_encode(['titre'=>'Hydratez-vous','message'=>'Buvez régulièrement.'])]]]],200)]);
        $conseil=Conseil::factory()->create();
        $this->actingAs(User::factory()->admin()->create())->postJson('/admin/campagnes-conseil/ia',['action'=>'rediger','conseil_id'=>$conseil->id,'canal'=>'affichage'])->assertOk()->assertJsonPath('titre','Hydratez-vous');
        Http::assertSent(fn($request)=>$request->hasHeader('Authorization','Bearer test-key')&&$request['messages'][0]['role']==='system');
    }

    public function test_campaign_ai_returns_503_when_key_is_missing(): void
    {
        config(['services.openrouter.key'=>null,'services.openrouter.model'=>'test-model']);
        Http::fake();
        $conseil=Conseil::factory()->create();
        $this->actingAs(User::factory()->admin()->create())->postJson('/admin/campagnes-conseil/ia',['action'=>'rediger','conseil_id'=>$conseil->id])->assertStatus(503);
        Http::assertNothingSent();
    }

    public function test_campaign_ai_returns_502_for_invalid_json(): void
    {
        config(['services.openrouter.key'=>'test-key','services.openrouter.model'=>'test-model']);
        Http::fake(['openrouter.ai/*'=>Http::response(['choices'=>[['message'=>['content'=>'not json']]]],200)]);
        $conseil=Conseil::factory()->create();
        $this->actingAs(User::factory()->admin()->create())->postJson('/admin/campagnes-conseil/ia',['action'=>'rediger','conseil_id'=>$conseil->id])->assertStatus(502);
    }

    public function test_campaign_ai_validates_input_before_calling_provider(): void
    {
        Http::fake();
        $this->actingAs(User::factory()->admin()->create())->postJson('/admin/campagnes-conseil/ia',['action'=>'rediger'])->assertStatus(422)->assertJsonValidationErrors('conseil_id');
        Http::assertNothingSent();
    }

    public function test_campaign_ai_rediger_clamps_lengths_gracefully(): void
    {
        config(['services.openrouter.key'=>'test-key','services.openrouter.model'=>'test-model']);
        $longTitle = str_repeat('Titre ', 40); // 240 chars > 150
        $longSms = str_repeat('Buvez de l eau fraiche. ', 15); // 360 chars > 160
        Http::fake(['openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => json_encode(['titre' => $longTitle, 'message' => $longSms])]]]], 200)]);
        $conseil = Conseil::factory()->create();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->postJson('/admin/campagnes-conseil/ia', ['action' => 'rediger', 'conseil_id' => $conseil->id, 'canal' => 'sms'])
            ->assertOk();

        $this->assertLessThanOrEqual(150, mb_strlen($response->json('titre')));
        $this->assertLessThanOrEqual(160, mb_strlen($response->json('message')));
    }

    public function test_campaign_ai_diffusion_parses_markdown_json_and_normalizes_synonyms(): void
    {
        config(['services.openrouter.key'=>'test-key','services.openrouter.model'=>'test-model']);
        $rawMarkdown = "```json\n" . json_encode([
            'public_cible' => 'personnes âgées',
            'canal' => 'réseaux sociaux',
            'date_debut' => '2026-11-01',
            'date_fin' => '2026-10-20' // fin before debut, should auto-fix
        ]) . "\n```";

        Http::fake(['openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => $rawMarkdown]]]], 200)]);
        $conseil = Conseil::factory()->create();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->postJson('/admin/campagnes-conseil/ia', ['action' => 'diffusion', 'conseil_id' => $conseil->id])
            ->assertOk();

        $this->assertEquals('personnes_agees', $response->json('public_cible'));
        $this->assertEquals('reseaux_sociaux', $response->json('canal'));
        $this->assertEquals('2026-11-01', $response->json('date_debut'));
        $this->assertGreaterThanOrEqual('2026-11-01', $response->json('date_fin'));
    }

    public function test_campaign_ai_qualite_parses_float_score_and_bullet_points(): void
    {
        config(['services.openrouter.key'=>'test-key','services.openrouter.model'=>'test-model']);
        $payload = [
            'score' => '8.5/10',
            'remarques' => [
                '• Premier point très clair.',
                '• Deuxième point utile.',
                '• Troisième remarque.',
                '• Quatrième remarque à ignorer car plus de 3.',
            ]
        ];
        Http::fake(['openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => json_encode($payload)]]]], 200)]);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->postJson('/admin/campagnes-conseil/ia', ['action' => 'qualite', 'message' => 'Restez hydraté.'])
            ->assertOk();

        $this->assertEquals(9, $response->json('score'));
        $this->assertCount(3, $response->json('remarques'));
        $this->assertEquals('Premier point très clair.', $response->json('remarques.0'));
    }

    public function test_only_valid_selected_campaign_proposals_are_saved_as_drafts(): void
    {
        $conseil = Conseil::factory()->create();
        $payload = ['campagnes' => [['conseil_id' => $conseil->id, 'titre' => 'Brouillon IA', 'message' => 'Restez au frais.', 'public_cible' => 'tous', 'canal' => 'affichage', 'date_debut' => '2026-11-01', 'date_fin' => '2026-11-05']]];
        $this->actingAs(User::factory()->admin()->create())->post('/admin/campagnes-conseil/ia/selection', $payload)->assertRedirect();
        $this->assertDatabaseHas('campagnes_conseil', ['titre' => 'Brouillon IA', 'statut' => 'brouillon', 'activation_auto' => false]);
    }
}
