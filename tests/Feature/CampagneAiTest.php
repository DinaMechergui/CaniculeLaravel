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

    public function test_only_valid_selected_campaign_proposals_are_saved_as_drafts(): void
    {
        $conseil=Conseil::factory()->create();
        $payload=['campagnes'=>[['conseil_id'=>$conseil->id,'titre'=>'Brouillon IA','message'=>'Restez au frais.','public_cible'=>'tous','canal'=>'affichage','date_debut'=>'2026-11-01','date_fin'=>'2026-11-05']]];
        $this->actingAs(User::factory()->admin()->create())->post('/admin/campagnes-conseil/ia/selection',$payload)->assertRedirect();
        $this->assertDatabaseHas('campagnes_conseil',['titre'=>'Brouillon IA','statut'=>'brouillon','activation_auto'=>false]);
    }
}
