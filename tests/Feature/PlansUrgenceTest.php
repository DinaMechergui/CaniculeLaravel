<?php

namespace Tests\Feature;

use App\Models\EtapePlan;
use App\Models\PlanUrgence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlansUrgenceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User { return User::factory()->admin()->create(); }
    private function plan(array $data = []): PlanUrgence { return PlanUrgence::factory()->create($data); }

    public function test_admin_can_create_a_plan_and_it_starts_inactive(): void
    {
        $this->actingAs($this->admin())->post('/admin/plans-urgence', [
            'titre'=>'Plan canicule', 'description'=>'Préparer le foyer', 'type_evenement'=>'canicule',
            'niveau_risque'=>'modere', 'public_cible'=>'tous', 'actif'=>1,
        ])->assertRedirect();
        $this->assertDatabaseHas('plans_urgence',['titre'=>'Plan canicule','actif'=>false]);
    }

    public function test_plan_validation_rejects_invalid_public(): void
    {
        $this->actingAs($this->admin())->post('/admin/plans-urgence', [
            'titre'=>'Plan', 'type_evenement'=>'canicule', 'niveau_risque'=>'faible', 'public_cible'=>'inconnu',
        ])->assertSessionHasErrors('public_cible');
    }

    public function test_admin_can_add_an_etape_and_delete_cascades(): void
    {
        $plan=$this->plan();
        $this->actingAs($this->admin())->post("/admin/plans-urgence/{$plan->id}/etapes", [
            'titre'=>'Boire de l’eau','moment'=>'pendant','obligatoire'=>1,
        ])->assertRedirect();
        $this->assertDatabaseHas('etapes_plan',['plan_urgence_id'=>$plan->id,'titre'=>'Boire de l’eau']);
        $plan->delete();
        $this->assertDatabaseMissing('etapes_plan',['plan_urgence_id'=>$plan->id]);
    }

    public function test_back_access_is_admin_only(): void
    {
        $this->get('/admin/plans-urgence')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/plans-urgence')->assertForbidden();
    }

    public function test_public_only_sees_active_plans_and_inactive_plan_is_404(): void
    {
        $visible=$this->plan(['actif'=>true]);
        $hidden=$this->plan(['actif'=>false]);
        $this->get('/plans-urgence')->assertOk()->assertSee($visible->titre)->assertDontSee($hidden->titre);
        $this->get('/plans-urgence/'.$hidden->id)->assertNotFound();
    }

    public function test_checking_an_etape_requires_login_and_updates_progress(): void
    {
        $plan=$this->plan(['actif'=>true]);
        $etape=EtapePlan::factory()->create(['plan_urgence_id'=>$plan->id]);
        $this->post('/plans-urgence/cocher',['etape_plan_id'=>$etape->id,'fait'=>1])->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->post('/plans-urgence/cocher',['etape_plan_id'=>$etape->id,'fait'=>1])->assertRedirect();
        $this->assertDatabaseHas('etape_plan_user',['etape_plan_id'=>$etape->id]);
    }

    public function test_foyer_profile_accepts_valid_data_and_rejects_zero_adults(): void
    {
        $user=User::factory()->create();
        $valid=['nb_adultes'=>2,'nb_enfants'=>1,'nb_personnes_agees'=>0,'type_logement'=>'appartement'];
        $this->actingAs($user)->post('/mon-foyer',$valid)->assertRedirect();
        $this->assertDatabaseHas('profils_foyer',['user_id'=>$user->id,'nb_adultes'=>2]);
        $this->actingAs($user)->post('/mon-foyer',array_merge($valid,['nb_adultes'=>0]))->assertSessionHasErrors('nb_adultes');
    }

    public function test_pdf_route_returns_a_pdf_for_an_active_plan(): void
    {
        $plan=$this->plan(['actif'=>true]);
        EtapePlan::factory()->create(['plan_urgence_id'=>$plan->id]);
        $this->get('/plans-urgence/'.$plan->id.'/pdf')->assertOk()->assertHeader('content-type','application/pdf');
    }
}
