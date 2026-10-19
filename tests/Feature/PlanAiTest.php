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
}
