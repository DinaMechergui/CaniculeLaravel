<?php

namespace Tests\Feature;

use App\Models\AlerteMeteo;
use App\Models\MesurePrevention;
use App\Models\NiveauAlerte;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests exhaustifs du module « Mesures de prévention » :
 * - Relations Eloquent (belongsTo / hasMany) avec AlerteMeteo
 * - CRUD complet (Back Office)
 * - Validation stricte par Form Requests
 * - Filtres par type et statut et pagination
 * - Refus de suppression d'une alerte avec mesures
 * - Affichage sur le Front Office (détail d'une alerte)
 */
class MesuresPreventionTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Relations Eloquent et Modèles
    |--------------------------------------------------------------------------
    */

    public function test_relations_between_alerte_and_mesure_prevention(): void
    {
        $niveau = NiveauAlerte::factory()->create(['couleur' => 'rouge', 'niveau' => 4]);
        $alerte = AlerteMeteo::factory()->create(['niveau_alerte_id' => $niveau->id]);

        $mesure = MesurePrevention::factory()->create([
            'alerte_meteo_id' => $alerte->id,
            'titre' => 'Plan de ravitaillement d\'eau',
            'type' => 'distribution_eau',
            'statut' => 'en_cours',
        ]);

        // BelongsTo relation
        $this->assertInstanceOf(AlerteMeteo::class, $mesure->alerteMeteo);
        $this->assertEquals($alerte->id, $mesure->alerteMeteo->id);

        // HasMany relation
        $this->assertTrue($alerte->mesures->contains($mesure));
        $this->assertEquals(1, $alerte->mesures()->count());
    }

    /*
    |--------------------------------------------------------------------------
    | Back Office : Contrôle d'accès
    |--------------------------------------------------------------------------
    */

    public function test_guest_is_redirected_to_login_from_mesures_prevention(): void
    {
        $this->get('/admin/mesures-prevention')->assertRedirect('/login');
        $this->get('/admin/mesures-prevention/create')->assertRedirect('/login');
    }

    public function test_simple_user_cannot_access_admin_mesures_prevention(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/mesures-prevention')->assertForbidden();
        $this->actingAs($user)->get('/admin/mesures-prevention/create')->assertForbidden();
    }

    public function test_admin_can_access_mesures_pages(): void
    {
        $admin = User::factory()->admin()->create();
        $mesure = MesurePrevention::factory()->create();

        $this->actingAs($admin)->get('/admin/mesures-prevention')->assertOk();
        $this->actingAs($admin)->get('/admin/mesures-prevention/create')->assertOk();
        $this->actingAs($admin)->get('/admin/mesures-prevention/' . $mesure->id)->assertOk();
        $this->actingAs($admin)->get('/admin/mesures-prevention/' . $mesure->id . '/edit')->assertOk();
    }

    /*
    |--------------------------------------------------------------------------
    | Back Office : CRUD MesurePrevention
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_list_and_filter_mesures_prevention(): void
    {
        $admin = User::factory()->admin()->create();
        $alerte = AlerteMeteo::factory()->create();

        $mesureEvac = MesurePrevention::factory()->create([
            'alerte_meteo_id' => $alerte->id,
            'titre' => 'Évacuation des campings du nord',
            'type' => 'evacuation',
            'statut' => 'prevue',
        ]);

        $mesureEau = MesurePrevention::factory()->create([
            'alerte_meteo_id' => $alerte->id,
            'titre' => 'Distribution d\'eau aux gares',
            'type' => 'distribution_eau',
            'statut' => 'terminee',
        ]);

        // Filtrage par type
        $responseType = $this->actingAs($admin)->get('/admin/mesures-prevention?type=evacuation');
        $responseType->assertOk()
            ->assertSee('Évacuation des campings du nord')
            ->assertDontSee('Distribution d\'eau aux gares');

        // Filtrage par statut
        $responseStatut = $this->actingAs($admin)->get('/admin/mesures-prevention?statut=terminee');
        $responseStatut->assertOk()
            ->assertSee('Distribution d\'eau aux gares')
            ->assertDontSee('Évacuation des campings du nord');
    }

    public function test_admin_can_create_mesure_prevention(): void
    {
        $admin = User::factory()->admin()->create();
        $alerte = AlerteMeteo::factory()->create();

        $payload = [
            'alerte_meteo_id' => $alerte->id,
            'titre' => 'Mise en place de citernes mobiles',
            'type' => 'distribution_eau',
            'description' => 'Installation de citernes dans les arrondissements prioritaires.',
            'responsable' => 'SONEDE & Protection Civile',
            'date_debut' => now()->format('Y-m-d\TH:i'),
            'statut' => 'en_cours',
        ];

        $response = $this->actingAs($admin)->post('/admin/mesures-prevention', $payload);

        $response->assertRedirect('/admin/mesures-prevention')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mesures_prevention', [
            'titre' => 'Mise en place de citernes mobiles',
            'type' => 'distribution_eau',
            'responsable' => 'SONEDE & Protection Civile',
            'statut' => 'en_cours',
        ]);
    }

    public function test_mesure_prevention_validation_fails_with_invalid_data(): void
    {
        $admin = User::factory()->admin()->create();

        // Alerte inconnue, titre manquant, type et statut invalides
        $response = $this->actingAs($admin)->post('/admin/mesures-prevention', [
            'alerte_meteo_id' => 999999,
            'titre' => '',
            'type' => 'type_inconnu',
            'description' => '',
            'responsable' => '',
            'date_debut' => 'date-invalide',
            'statut' => 'statut_inconnu',
        ]);

        $response->assertSessionHasErrors([
            'alerte_meteo_id',
            'titre',
            'type',
            'description',
            'responsable',
            'date_debut',
            'statut',
        ]);
    }

    public function test_admin_can_update_mesure_prevention(): void
    {
        $admin = User::factory()->admin()->create();
        $alerte = AlerteMeteo::factory()->create();
        $mesure = MesurePrevention::factory()->create([
            'alerte_meteo_id' => $alerte->id,
            'statut' => 'prevue',
        ]);

        $payload = [
            'alerte_meteo_id' => $alerte->id,
            'titre' => 'Titre Mesure Modifié',
            'type' => 'renfort_secours',
            'description' => 'Description révisée.',
            'responsable' => 'Croissant Rouge Tunisien',
            'date_debut' => now()->format('Y-m-d\TH:i'),
            'statut' => 'en_cours',
        ];

        $response = $this->actingAs($admin)->put('/admin/mesures-prevention/' . $mesure->id, $payload);

        $response->assertRedirect('/admin/mesures-prevention')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mesures_prevention', [
            'id' => $mesure->id,
            'titre' => 'Titre Mesure Modifié',
            'type' => 'renfort_secours',
            'statut' => 'en_cours',
        ]);
    }

    public function test_admin_can_delete_mesure_prevention(): void
    {
        $admin = User::factory()->admin()->create();
        $mesure = MesurePrevention::factory()->create();

        $response = $this->actingAs($admin)->delete('/admin/mesures-prevention/' . $mesure->id);

        $response->assertRedirect('/admin/mesures-prevention')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('mesures_prevention', [
            'id' => $mesure->id,
        ]);
    }

    public function test_admin_cannot_delete_alerte_with_existing_mesures(): void
    {
        $admin = User::factory()->admin()->create();
        $alerte = AlerteMeteo::factory()->create();
        MesurePrevention::factory()->create(['alerte_meteo_id' => $alerte->id]);

        $response = $this->actingAs($admin)->delete('/admin/alertes-meteo/' . $alerte->id);

        $response->assertRedirect('/admin/alertes-meteo')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('alertes_meteo', [
            'id' => $alerte->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Front Office : Détail d'une alerte avec ses mesures
    |--------------------------------------------------------------------------
    */

    public function test_front_show_displays_active_and_planned_measures_and_hides_completed(): void
    {
        $alerte = AlerteMeteo::factory()->create([
            'titre' => 'Canicule sévère Tozeur',
        ]);

        $mesurePrevue = MesurePrevention::factory()->create([
            'alerte_meteo_id' => $alerte->id,
            'titre' => 'Distribution d\'eau minérale prévue',
            'type' => 'distribution_eau',
            'statut' => 'prevue',
        ]);

        $mesureEnCours = MesurePrevention::factory()->create([
            'alerte_meteo_id' => $alerte->id,
            'titre' => 'Renfort SAMU en cours',
            'type' => 'renfort_secours',
            'statut' => 'en_cours',
        ]);

        $mesureTerminee = MesurePrevention::factory()->create([
            'alerte_meteo_id' => $alerte->id,
            'titre' => 'Évacuation terminée hier',
            'type' => 'evacuation',
            'statut' => 'terminee',
        ]);

        $response = $this->get('/alertes-meteo/' . $alerte->id);

        $response->assertOk()
            ->assertSee('Canicule sévère Tozeur')
            ->assertSee('Distribution d\'eau minérale prévue')
            ->assertSee('Renfort SAMU en cours')
            ->assertDontSee('Évacuation terminée hier');
    }
}
