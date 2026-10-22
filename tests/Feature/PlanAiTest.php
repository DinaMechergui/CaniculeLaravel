<?php
namespace Tests\Feature;

use App\Models\PlanUrgence;
use App\Models\EtapePlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlanAiTest extends TestCase
{
    use RefreshDatabase;

    private function etapes(int $number=8): array
    {
        return array_map(fn($i)=>['titre'=>'Étape '.($i+1),'description'=>'Description pratique','moment'=>['avant','pendant','apres'][$i%3],'obligatoire'=>$i<2],range(0,$number-1));
    }

    public function test_admin_can_generate_a_plan_proposal_with_http_fake(): void
    {
        config(['services.openrouter.key'=>'test-key','services.openrouter.model'=>'test-model']);
        Http::fake(['openrouter.ai/*'=>Http::response(['choices'=>[['message'=>['content'=>json_encode(['titre'=>'Plan canicule','description'=>'Préparation familiale.','etapes'=>$this->etapes()])]]]],200)]);
        $this->actingAs(User::factory()->admin()->create())->postJson('/admin/plans-urgence/ia',['action'=>'generer','type_evenement'=>'canicule','niveau_risque'=>'modere','public_cible'=>'tous'])->assertOk()->assertJsonCount(8,'etapes');
    }

    public function test_generated_plan_creation_validates_steps_and_keeps_plan_inactive(): void
    {
        $payload=['titre'=>'Plan généré','description'=>'Description','type_evenement'=>'canicule','niveau_risque'=>'modere','public_cible'=>'tous','etapes'=>$this->etapes()];
        $this->actingAs(User::factory()->admin()->create())->post('/admin/plans-urgence/ia/creer',$payload)->assertRedirect();
        $plan=PlanUrgence::where('titre','Plan généré')->firstOrFail();
        $this->assertFalse($plan->actif);
        $this->assertSame(8,$plan->etapes()->count());
    }

    public function test_plan_ai_returns_503_without_a_key_and_falls_back_for_personalized_plans(): void
    {
        config(['services.openrouter.key'=>null,'services.openrouter.model'=>'test-model']);
        Http::fake();
        $user=User::factory()->create();
        PlanUrgence::factory()->create(['actif'=>true,'public_cible'=>'tous']);
        $this->actingAs(User::factory()->admin()->create())->postJson('/admin/plans-urgence/ia',['action'=>'generer','type_evenement'=>'canicule','niveau_risque'=>'faible','public_cible'=>'tous'])->assertStatus(503);
        $this->actingAs($user)->postJson('/plans-urgence/ia/personnalise')->assertOk()->assertJsonPath('source','regles_locales');
        Http::assertNothingSent();
    }

    public function test_plan_ai_rejects_invalid_input_before_provider_call(): void
    {
        Http::fake();
        $this->actingAs(User::factory()->admin()->create())->postJson('/admin/plans-urgence/ia',['action'=>'generer','type_evenement'=>'bad'])->assertStatus(422);
        Http::assertNothingSent();
    }

    public function test_plan_translation_is_generated_without_changing_saved_data(): void
    {
        config(['services.openrouter.key'=>'test-key','services.openrouter.model'=>'test-model']);
        Http::fake(['openrouter.ai/*'=>Http::response(['choices'=>[['message'=>['content'=>json_encode(['titre'=>'خطة','description'=>'وصف','etapes'=>[['titre'=>'اشرب الماء','moment'=>'avant','description'=>'']]])]]]],200)]);
        $plan=PlanUrgence::factory()->create(['actif'=>true,'titre'=>'Plan original']);
        EtapePlan::factory()->create(['plan_urgence_id'=>$plan->id]);
        $this->postJson('/plans-urgence/'.$plan->id.'/ia',['action'=>'traduire'])->assertOk()->assertJsonPath('dir','rtl');
        $this->assertSame('Plan original',$plan->fresh()->titre);
    }

    public function test_plan_ai_accepts_steps_with_accented_moments_and_long_texts(): void
    {
        config(['services.openrouter.key'=>'test-key','services.openrouter.model'=>'test-model']);
        $longTitle = str_repeat('Titre d’étape détaillé ', 10); // > 150 chars
        $longDesc = str_repeat('Description très complète avec beaucoup de détails pour le foyer. ', 15); // > 500 chars

        $steps = [];
        for ($i = 0; $i < 9; $i++) {
            $steps[] = [
                'titre' => $longTitle,
                'description' => $longDesc,
                'moment' => ($i % 3 === 0 ? 'Avant' : ($i % 3 === 1 ? 'Pendant' : 'Après')), // with accent and uppercase
                'obligatoire' => ($i % 2 === 0 ? 'true' : 'false'), // string booleans
            ];
        }

        Http::fake(['openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => json_encode([
            'titre' => str_repeat('Titre du plan ', 20), // > 150 chars
            'description' => str_repeat('Description du plan ', 60), // > 1000 chars
            'etapes' => $steps,
        ])]]]], 200)]);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->postJson('/admin/plans-urgence/ia', [
                'action' => 'generer',
                'type_evenement' => 'canicule',
                'niveau_risque' => 'eleve',
                'public_cible' => 'tous',
            ])
            ->assertOk();

        $this->assertLessThanOrEqual(150, mb_strlen($response->json('titre')));
        $this->assertLessThanOrEqual(1000, mb_strlen($response->json('description')));

        $returnedSteps = $response->json('etapes');
        $this->assertCount(9, $returnedSteps);
        foreach ($returnedSteps as $step) {
            $this->assertLessThanOrEqual(150, mb_strlen($step['titre']));
            $this->assertLessThanOrEqual(500, mb_strlen($step['description']));
            $this->assertContains($step['moment'], ['avant', 'pendant', 'apres']);
            $this->assertIsBool($step['obligatoire']);
        }
    }

    public function test_plan_ai_auto_completes_if_fewer_than_eight_steps(): void
    {
        config(['services.openrouter.key'=>'test-key','services.openrouter.model'=>'test-model']);

        Http::fake(['openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => json_encode([
            'titre' => 'Plan court',
            'description' => 'Description courte.',
            'etapes' => $this->etapes(4),
        ])]]]], 200)]);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->postJson('/admin/plans-urgence/ia', [
                'action' => 'generer',
                'type_evenement' => 'canicule',
                'niveau_risque' => 'modere',
                'public_cible' => 'tous',
            ])
            ->assertOk();

        $this->assertGreaterThanOrEqual(8, count($response->json('etapes')));
    }

    public function test_plan_ai_slices_if_more_than_twelve_steps(): void
    {
        config(['services.openrouter.key'=>'test-key','services.openrouter.model'=>'test-model']);

        Http::fake(['openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => json_encode([
            'titre' => 'Plan très long',
            'description' => 'Description.',
            'etapes' => $this->etapes(15),
        ])]]]], 200)]);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->postJson('/admin/plans-urgence/ia', [
                'action' => 'generer',
                'type_evenement' => 'canicule',
                'niveau_risque' => 'modere',
                'public_cible' => 'tous',
            ])
            ->assertOk();

        $this->assertCount(12, $response->json('etapes'));
    }
}
