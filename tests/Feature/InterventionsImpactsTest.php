<?php

namespace Tests\Feature;

use App\Models\Coupure;
use App\Models\ImpactCoupure;
use App\Models\Intervention;
use App\Models\Quartier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterventionsImpactsTest extends TestCase
{
    use RefreshDatabase;

    public function test_intervention_crud_and_report_requirement(): void
    {
        $admin = User::factory()->admin()->create();
        $coupure = Coupure::factory()->create();
        $payload = $this->interventionData($coupure);

        $this->actingAs($admin)->get('/admin/interventions/create')->assertOk();
        $this->post('/admin/interventions', $payload)
            ->assertRedirect('/admin/interventions')
            ->assertSessionHas('success');
        $intervention = Intervention::firstOrFail();

        $this->get('/admin/interventions/'.$intervention->id)->assertOk()->assertSee($payload['technicien']);
        $this->get('/admin/interventions/'.$intervention->id.'/edit')->assertOk();
        $this->put('/admin/interventions/'.$intervention->id, [
            ...$payload,
            'equipe' => 'Équipe modifiée',
            'statut' => 'terminee',
            'rapport' => 'Rapport final.',
        ])->assertRedirect('/admin/interventions')->assertSessionHas('success');
        $this->assertDatabaseHas('interventions', [
            'id' => $intervention->id,
            'equipe' => 'Équipe modifiée',
            'rapport' => 'Rapport final.',
        ]);

        $this->put('/admin/interventions/'.$intervention->id, [
            ...$payload,
            'statut' => 'terminee',
            'rapport' => '',
        ])->assertSessionHasErrors('rapport');
        $this->delete('/admin/interventions/'.$intervention->id)
            ->assertRedirect('/admin/interventions')
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('interventions', ['id' => $intervention->id]);
    }

    public function test_intervention_rejects_invalid_foreign_key_and_duration(): void
    {
        $admin = User::factory()->admin()->create();
        $coupure = Coupure::factory()->create();
        $payload = $this->interventionData($coupure);

        $this->actingAs($admin)->post('/admin/interventions', [...$payload, 'coupure_id' => 999999])
            ->assertSessionHasErrors('coupure_id');
        $this->post('/admin/interventions', [...$payload, 'duree_estimee' => 0])
            ->assertSessionHasErrors('duree_estimee');
        $this->post('/admin/interventions', [...$payload, 'duree_estimee' => -10])
            ->assertSessionHasErrors('duree_estimee');
        $this->post('/admin/interventions', [
            ...$payload,
            'equipe' => '',
            'technicien' => '',
            'date_intervention' => 'pas-une-date',
        ])->assertSessionHasErrors(['equipe', 'technicien', 'date_intervention']);
        $this->post('/admin/interventions', [])->assertSessionHasErrors([
            'coupure_id',
            'equipe',
            'technicien',
            'date_intervention',
            'duree_estimee',
            'statut',
        ]);
    }

    public function test_interventions_can_be_filtered_and_paginated_and_report_is_not_public(): void
    {
        $admin = User::factory()->admin()->create();
        $quartier = Quartier::factory()->create();
        $coupure = Coupure::factory()->for($quartier)->create(['statut' => 'en_cours']);
        $autreQuartier = Quartier::factory()->create();
        $autreCoupure = Coupure::factory()->for($autreQuartier)->create();

        Intervention::factory()->count(11)->for($coupure)->create([
            'equipe' => 'Équipe Réseau',
            'technicien' => 'Technicien Test',
            'statut' => 'en_cours',
            'rapport' => 'Rapport confidentiel',
        ]);
        Intervention::factory()->for($autreCoupure)->create([
            'equipe' => 'Autre équipe',
            'statut' => 'prevue',
        ]);

        $response = $this->actingAs($admin)->get('/admin/interventions?'.http_build_query([
            'search' => 'Technicien Test',
            'quartier_id' => $quartier->id,
            'statut' => 'en_cours',
        ]))->assertOk();

        $this->assertCount(10, $response->viewData('interventions')->getCollection());
        $response->assertSee('page=2', false);
        $this->get('/coupures/'.$coupure->id)
            ->assertOk()
            ->assertSee('Équipe Réseau')
            ->assertDontSee('Rapport confidentiel');
    }

    public function test_impact_crud_and_validation(): void
    {
        $admin = User::factory()->admin()->create();
        $coupure = Coupure::factory()->create();
        $payload = $this->impactData($coupure);

        $this->actingAs($admin)->get('/admin/impacts-coupures/create')->assertOk();
        $this->post('/admin/impacts-coupures', $payload)
            ->assertRedirect('/admin/impacts-coupures')
            ->assertSessionHas('success');
        $impact = ImpactCoupure::firstOrFail();

        $this->get('/admin/impacts-coupures/'.$impact->id)->assertOk()->assertSee($payload['nom_structure']);
        $this->get('/admin/impacts-coupures/'.$impact->id.'/edit')->assertOk();
        $this->put('/admin/impacts-coupures/'.$impact->id, [
            ...$payload,
            'statut' => 'resolu',
            'personnes_affectees' => 0,
        ])->assertRedirect('/admin/impacts-coupures')->assertSessionHas('success');
        $this->assertDatabaseHas('impacts_coupures', ['id' => $impact->id, 'statut' => 'resolu']);

        $this->put('/admin/impacts-coupures/'.$impact->id, [
            ...$payload,
            'niveau_gravite' => 'inconnu',
        ])->assertSessionHasErrors('niveau_gravite');
        $this->put('/admin/impacts-coupures/'.$impact->id, [
            ...$payload,
            'personnes_affectees' => -1,
        ])->assertSessionHasErrors('personnes_affectees');
        $this->post('/admin/impacts-coupures', [
            ...$payload,
            'coupure_id' => 999999,
        ])->assertSessionHasErrors('coupure_id');
        $this->post('/admin/impacts-coupures', [])->assertSessionHasErrors([
            'coupure_id',
            'nom_structure',
            'categorie',
            'adresse',
            'personnes_affectees',
            'niveau_gravite',
            'date_signalement',
            'statut',
        ]);
        $this->delete('/admin/impacts-coupures/'.$impact->id)
            ->assertRedirect('/admin/impacts-coupures')
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('impacts_coupures', ['id' => $impact->id]);
    }

    public function test_impact_filters_and_database_statistics(): void
    {
        $admin = User::factory()->admin()->create();
        $quartier = Quartier::factory()->create();
        $coupure = Coupure::factory()->for($quartier)->create(['statut' => 'en_cours']);
        $otherCoupure = Coupure::factory()->create();

        ImpactCoupure::factory()->for($coupure)->create([
            'nom_structure' => 'École du Centre',
            'categorie' => 'ecole',
            'niveau_gravite' => 'critique',
            'personnes_affectees' => 15,
            'statut' => 'en_traitement',
            'adresse' => 'Adresse à ne pas publier',
            'mesures_urgence' => 'Groupe de secours public.',
            'description' => 'Description administrative confidentielle.',
        ]);
        ImpactCoupure::factory()->for($otherCoupure)->create([
            'nom_structure' => 'Commerce extérieur',
            'personnes_affectees' => 8,
            'niveau_gravite' => 'moyen',
            'statut' => 'resolu',
        ]);

        $response = $this->actingAs($admin)->get('/admin/impacts-coupures?'.http_build_query([
            'quartier_id' => $quartier->id,
            'coupure_id' => $coupure->id,
            'categorie' => 'ecole',
            'niveau_gravite' => 'critique',
            'statut' => 'en_traitement',
            'search' => 'École du Centre',
        ]))->assertOk();

        $this->assertCount(1, $response->viewData('impacts')->getCollection());
        $this->assertSame(2, $response->viewData('statistiques')['total']);
        $this->assertSame(23, $response->viewData('statistiques')['personnes']);
        $this->assertSame(1, $response->viewData('statistiques')['critiques']);
        $this->assertSame(1, $response->viewData('statistiques')['resolus']);
        $this->assertSame(1, $response->viewData('statistiques')['en_traitement']);
        $this->get('/coupures/'.$coupure->id)
            ->assertOk()
            ->assertSee('École du Centre')
            ->assertSee('Groupe de secours public.')
            ->assertDontSee('Adresse à ne pas publier')
            ->assertDontSee('Description administrative confidentielle.');
    }

    public function test_impact_list_is_paginated(): void
    {
        $admin = User::factory()->admin()->create();
        $coupure = Coupure::factory()->create();
        ImpactCoupure::factory()->count(11)->for($coupure)->create();

        $response = $this->actingAs($admin)->get('/admin/impacts-coupures')->assertOk();

        $this->assertCount(10, $response->viewData('impacts')->getCollection());
        $response->assertSee('page=2', false);
    }

    public function test_coupure_cannot_be_deleted_while_related_records_exist_and_admin_routes_are_protected(): void
    {
        $coupure = Coupure::factory()->create();
        Intervention::factory()->for($coupure)->create();
        ImpactCoupure::factory()->for($coupure)->create();

        $this->delete('/admin/coupures/'.$coupure->id)->assertRedirect('/login');

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->delete('/admin/coupures/'.$coupure->id)
            ->assertRedirect('/admin/coupures')
            ->assertSessionHas('error');
        $this->assertDatabaseHas('coupures', ['id' => $coupure->id]);
        $this->get('/admin/interventions')->assertOk();
        $this->get('/admin/impacts-coupures')->assertOk();
    }

    private function interventionData(Coupure $coupure): array
    {
        return [
            'coupure_id' => $coupure->id,
            'equipe' => 'Équipe Réseau',
            'technicien' => 'Sami Ben Ali',
            'date_intervention' => now()->format('Y-m-d\TH:i'),
            'duree_estimee' => 90,
            'statut' => 'en_cours',
            'rapport' => '',
        ];
    }

    private function impactData(Coupure $coupure): array
    {
        return [
            'coupure_id' => $coupure->id,
            'nom_structure' => 'École du Centre',
            'categorie' => 'ecole',
            'adresse' => '12 rue de Tunis',
            'personnes_affectees' => 15,
            'niveau_gravite' => 'eleve',
            'date_signalement' => now()->format('Y-m-d\TH:i'),
            'mesures_urgence' => 'Groupe électrogène installé.',
            'statut' => 'en_traitement',
            'description' => 'Description administrative.',
        ];
    }
}
