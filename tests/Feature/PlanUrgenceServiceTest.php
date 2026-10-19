<?php
namespace Tests\Feature;

use App\Models\EtapePlan;
use App\Models\PlanUrgence;
use App\Models\User;
use App\Services\PlanUrgenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanUrgenceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_activation_requires_three_steps_and_one_mandatory_step(): void
    {
        $plan=PlanUrgence::factory()->create();
        EtapePlan::factory()->count(2)->create(['plan_urgence_id'=>$plan->id,'obligatoire'=>true]);
        $service=new PlanUrgenceService();
        $this->assertFalse($service->activer($plan));
        EtapePlan::factory()->create(['plan_urgence_id'=>$plan->id]);
        $this->assertTrue($service->activer($plan));
        $this->assertTrue($plan->fresh()->actif);
    }

    public function test_duplicate_is_inactive_and_copies_steps(): void
    {
        $plan=PlanUrgence::factory()->create(['actif'=>true]);
        EtapePlan::factory()->count(3)->create(['plan_urgence_id'=>$plan->id]);
        $copy=(new PlanUrgenceService())->dupliquer($plan);
        $this->assertFalse($copy->actif);
        $this->assertSame(3,$copy->etapes()->count());
        $this->assertStringStartsWith('Copie de ',$copy->titre);
    }

    public function test_progression_tracks_completed_and_required_steps(): void
    {
        $plan=PlanUrgence::factory()->create();
        $required=EtapePlan::factory()->create(['plan_urgence_id'=>$plan->id,'obligatoire'=>true]);
        EtapePlan::factory()->create(['plan_urgence_id'=>$plan->id]);
        $user=User::factory()->create();
        $user->etapesFaites()->attach($required->id,['fait_le'=>now()]);
        $progression=(new PlanUrgenceService())->progression($plan,$user);
        $this->assertSame(50,$progression['pourcentage']);
        $this->assertTrue($progression['pret']);
    }
}
