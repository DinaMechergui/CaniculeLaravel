<?php

namespace Tests\Feature;

use App\Models\Coupure;
use App\Models\ImpactCoupure;
use App\Models\Intervention;
use App\Models\Quartier;
use App\Models\User;
use App\Services\SmartGridPriorityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmartGridTest extends TestCase
{
    use RefreshDatabase;

    public function test_smartgrid_dashboard_and_data_endpoint_require_an_admin(): void
    {
        $this->get('/admin/smartgrid')->assertRedirect('/login');
        $this->get('/admin/smartgrid/data')->assertRedirect('/login');

        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin/smartgrid')->assertForbidden();
        $this->actingAs($user)->get('/admin/smartgrid/data')->assertForbidden();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/admin/smartgrid')->assertOk();
        $this->get('/admin/smartgrid/data')->assertOk()->assertJsonStructure([
            'coupures',
            'impacts',
            'statistics' => ['geolocalisees', 'sans_position', 'en_cours', 'prevues'],
        ]);
    }

    public function test_geographic_coordinates_are_optional_paired_and_range_checked(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post('/admin/quartiers', [
            'nom' => 'Centre SmartGrid',
            'ville' => 'Tunis',
            'code_postal' => '1000',
            'latitude' => 36.8065,
            'longitude' => 10.1815,
        ])->assertRedirect('/admin/quartiers');

        $this->assertDatabaseHas('quartiers', [
            'nom' => 'Centre SmartGrid',
            'latitude' => '36.8065000',
            'longitude' => '10.1815000',
        ]);

        $this->post('/admin/quartiers', [
            'nom' => 'Coordonnées invalides',
            'ville' => 'Tunis',
            'code_postal' => '1001',
            'latitude' => 100,
            'longitude' => '',
        ])->assertSessionHasErrors(['latitude', 'longitude']);

        $quartier = Quartier::firstOrFail();
        $this->post('/admin/coupures', [
            'quartier_id' => $quartier->id,
            'type' => 'panne',
            'statut' => 'en_cours',
            'date_debut' => now()->subHour()->format('Y-m-d\TH:i'),
            'latitude' => '',
            'longitude' => '10.1815000',
        ])->assertSessionHasErrors('latitude');

        $this->post('/admin/impacts-coupures', [
            'coupure_id' => 1,
            'nom_structure' => 'Structure sans coupure de test',
            'categorie' => 'ecole',
            'adresse' => 'Adresse',
            'personnes_affectees' => 0,
            'niveau_gravite' => 'moyen',
            'date_signalement' => now()->format('Y-m-d\TH:i'),
            'statut' => 'en_traitement',
            'latitude' => 36.8,
            'longitude' => 181,
        ])->assertSessionHasErrors(['coupure_id', 'longitude']);

        $this->assertDatabaseHas('quartiers', ['nom' => 'Centre SmartGrid']);
        $this->assertDatabaseCount('coupures', 0);
        $this->assertDatabaseCount('impacts_coupures', 0);
    }

    public function test_admin_map_filters_use_database_coordinates_and_do_not_assume_neighborhood_centres(): void
    {
        $admin = User::factory()->admin()->create();
        $quartier = Quartier::factory()->create(['ville' => 'Tunis', 'latitude' => 36.8, 'longitude' => 10.1]);
        $located = Coupure::factory()->for($quartier)->create([
            'type' => 'panne',
            'statut' => 'en_cours',
            'latitude' => 36.81,
            'longitude' => 10.18,
        ]);
        $notLocated = Coupure::factory()->for($quartier)->create([
            'statut' => 'prevue',
            'latitude' => null,
            'longitude' => null,
        ]);
        $done = Coupure::factory()->for($quartier)->create(['statut' => 'terminee', 'latitude' => 36.82, 'longitude' => 10.19]);
        ImpactCoupure::factory()->for($located)->create([
            'latitude' => 36.811,
            'longitude' => 10.181,
            'categorie' => 'hopital',
            'niveau_gravite' => 'critique',
            'personnes_affectees' => 12,
            'mesures_urgence' => 'Mesure publique',
            'adresse' => 'Adresse privée de test',
            'description' => 'Note administrative privée',
        ]);
        Intervention::factory()->for($located)->create([
            'statut' => 'en_cours',
            'rapport' => 'Rapport technique confidentiel',
        ]);

        $response = $this->actingAs($admin)->get('/admin/smartgrid/data?'.http_build_query([
            'ville' => 'Tunis',
            'statut' => 'en_cours',
            'niveau_gravite' => 'critique',
            'categorie' => 'hopital',
        ]))->assertOk();

        $response->assertJsonCount(1, 'coupures')
            ->assertJsonPath('coupures.0.id', $located->id)
            ->assertJsonPath('coupures.0.latitude', 36.81)
            ->assertJsonPath('coupures.0.interventions_count', 1)
            ->assertJsonPath('impacts.0.personnes_affectees', 12)
            ->assertJsonPath('impacts.0.mesures_urgence', 'Mesure publique')
            ->assertJsonMissingPath('coupures.0.interventions.0.rapport')
            ->assertJsonMissingPath('impacts.0.adresse')
            ->assertJsonMissingPath('impacts.0.description');

        $all = $this->get('/admin/smartgrid/data')->assertOk();
        $this->assertSame(2, $all->json('statistics.geolocalisees'));
        $this->assertSame(1, collect($all->json('coupures'))->where('id', $notLocated->id)->whereNull('latitude')->count());
        $this->assertSame(1, collect($all->json('coupures'))->where('id', $done->id)->count());
    }

    public function test_public_map_only_exposes_active_public_fields_and_exact_positions(): void
    {
        $quartier = Quartier::factory()->create();
        $visible = Coupure::factory()->for($quartier)->create([
            'statut' => 'en_cours',
            'latitude' => 36.81,
            'longitude' => 10.18,
        ]);
        $withoutPosition = Coupure::factory()->for($quartier)->create([
            'statut' => 'prevue',
            'latitude' => null,
            'longitude' => null,
        ]);
        Coupure::factory()->for($quartier)->create(['statut' => 'terminee', 'latitude' => 36.82, 'longitude' => 10.19]);
        ImpactCoupure::factory()->for($visible)->create([
            'latitude' => 36.811,
            'longitude' => 10.181,
            'adresse' => 'Adresse privée publique',
            'description' => 'Description interne non publique',
            'mesures_urgence' => 'Consigne publique',
        ]);
        ImpactCoupure::factory()->for($withoutPosition)->create([
            'latitude' => 36.812,
            'longitude' => 10.182,
            'nom_structure' => 'Structure géolocalisée indépendamment',
        ]);
        Intervention::factory()->for($visible)->create([
            'technicien' => 'Technicien privé',
            'rapport' => 'Rapport privé',
        ]);

        $this->get('/coupures/carte')->assertOk();
        $response = $this->get('/coupures/carte/data')->assertOk();
        $response->assertJsonCount(2, 'coupures')
            ->assertJsonFragment(['id' => $visible->id, 'latitude' => 36.81, 'longitude' => 10.18])
            ->assertJsonFragment(['id' => $withoutPosition->id, 'latitude' => null, 'longitude' => null])
            ->assertJsonFragment(['nom_structure' => 'Structure géolocalisée indépendamment', 'latitude' => 36.812, 'longitude' => 10.182])
            ->assertJsonPath('impacts.0.mesures_urgence', 'Consigne publique')
            ->assertJsonMissingPath('impacts.0.adresse')
            ->assertJsonMissingPath('impacts.0.description')
            ->assertJsonMissingPath('coupures.0.interventions.0.technicien')
            ->assertJsonMissingPath('coupures.0.interventions.0.rapport');
    }

    public function test_priority_score_is_deterministic_and_accounts_for_sensitive_impacts_and_intervention(): void
    {
        $coupure = Coupure::factory()->create([
            'statut' => 'en_cours',
            'date_debut' => now()->subHours(12),
        ]);
        ImpactCoupure::factory()->for($coupure)->create([
            'categorie' => 'hopital',
            'niveau_gravite' => 'critique',
            'personnes_affectees' => 500,
        ]);

        $service = app(SmartGridPriorityService::class);
        $priority = $service->score($coupure->load(['impacts', 'interventions']));
        $this->assertSame(91, $priority['score']);
        $this->assertSame('critique', $priority['level']);
        $this->assertSame($priority, $service->score($coupure));

        Intervention::factory()->for($coupure)->create(['statut' => 'en_cours']);
        $priorityWithActiveIntervention = $service->score($coupure->fresh()->load(['impacts', 'interventions']));
        $this->assertSame(86, $priorityWithActiveIntervention['score']);
    }

    public function test_hospital_impacts_are_valid_and_count_as_sensitive_facilities(): void
    {
        $admin = User::factory()->admin()->create();
        $coupure = Coupure::factory()->create(['statut' => 'en_cours']);

        $this->actingAs($admin)->post('/admin/impacts-coupures', [
            'coupure_id' => $coupure->id,
            'nom_structure' => 'Hôpital de test',
            'categorie' => 'hopital',
            'adresse' => 'Adresse de test',
            'personnes_affectees' => 20,
            'niveau_gravite' => 'moyen',
            'date_signalement' => now()->format('Y-m-d\TH:i'),
            'statut' => 'en_traitement',
        ])->assertRedirect('/admin/impacts-coupures');

        $impact = ImpactCoupure::query()->where('nom_structure', 'Hôpital de test')->firstOrFail();
        $priority = app(SmartGridPriorityService::class)->score($coupure->fresh()->load(['impacts', 'interventions']));

        $this->assertSame('hopital', $impact->categorie);
        $this->assertSame(15, $priority['factors']['etablissements_sensibles']);
    }

    public function test_missing_ai_and_routing_keys_return_clear_service_messages_without_fake_data(): void
    {
        config(['services.gemini.api_key' => null, 'services.openrouteservice.api_key' => null]);
        $admin = User::factory()->admin()->create();
        $coupure = Coupure::factory()->create([
            'statut' => 'en_cours',
            'latitude' => 36.81,
            'longitude' => 10.18,
        ]);

        $this->actingAs($admin)->post('/admin/smartgrid/analyze/'.$coupure->id)
            ->assertStatus(503)
            ->assertJsonPath('message', 'L’assistant IA n’est pas configuré. Ajoutez GEMINI_API_KEY dans le fichier .env.');
        $this->postJson('/admin/smartgrid/route', [
            'origin' => ['latitude' => 36.8, 'longitude' => 10.1],
            'coupure_ids' => [$coupure->id],
        ])->assertStatus(503)
            ->assertJsonPath('message', 'Le routage n’est pas configuré. Ajoutez OPENROUTESERVICE_API_KEY dans le fichier .env.');
        $this->postJson('/admin/smartgrid/analyze-zone', [
            'quartier_id' => $coupure->quartier_id,
        ])->assertStatus(503)
            ->assertJsonPath('message', 'L’assistant IA n’est pas configuré. Ajoutez GEMINI_API_KEY dans le fichier .env.');
    }

    public function test_gemini_analysis_is_server_side_and_only_receives_curated_operational_fields(): void
    {
        config(['services.gemini.api_key' => 'test-secret-key', 'services.gemini.model' => 'gemini-test']);
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => json_encode([
                    'resume' => 'Analyse de test',
                    'facteurs_risque' => [],
                    'priorite_recommandee' => 'elevee',
                    'justification' => 'Test',
                    'actions_suggerees' => [],
                    'incertitudes' => [],
                ])]]]],
            ], 200),
        ]);

        $admin = User::factory()->admin()->create();
        $coupure = Coupure::factory()->create();
        ImpactCoupure::factory()->for($coupure)->create(['description' => 'Information qui ne doit pas être envoyée.']);
        Intervention::factory()->for($coupure)->create(['rapport' => 'Rapport privé à exclure.']);

        $this->actingAs($admin)->postJson('/admin/smartgrid/analyze/'.$coupure->id)
            ->assertOk()
            ->assertJsonPath('analysis.resume', 'Analyse de test');

        Http::assertSent(function ($request): bool {
            $content = json_encode($request->data(), JSON_UNESCAPED_UNICODE);

            return $request->hasHeader('x-goog-api-key', 'test-secret-key')
                && ! str_contains($content, 'Information qui ne doit pas être envoyée.')
                && ! str_contains($content, 'Rapport privé à exclure.');
        });
    }

    public function test_route_endpoint_rejects_coupures_without_precise_coordinates(): void
    {
        config(['services.openrouteservice.api_key' => 'configured-test-key']);
        $admin = User::factory()->admin()->create();
        $coupure = Coupure::factory()->create(['latitude' => null, 'longitude' => null]);

        $this->actingAs($admin)->postJson('/admin/smartgrid/route', [
            'origin' => ['latitude' => 36.8, 'longitude' => 10.1],
            'coupure_ids' => [$coupure->id],
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Chaque coupure sélectionnée doit exister, être prévue ou en cours, et avoir une position précise.');
    }

    public function test_route_uses_real_provider_distance_and_returns_priority_order(): void
    {
        config([
            'services.openrouteservice.api_key' => 'test-route-key',
            'services.openrouteservice.base_url' => 'https://api.openrouteservice.test',
        ]);
        Http::fake([
            'api.openrouteservice.test/v2/matrix/*' => Http::response([
                'durations' => [[0, 100, 300], [100, 0, 200], [300, 200, 0]],
            ], 200),
            'api.openrouteservice.test/v2/directions/*' => Http::response([
                'type' => 'FeatureCollection',
                'features' => [[
                    'type' => 'Feature',
                    'geometry' => ['type' => 'LineString', 'coordinates' => [[10.1, 36.8], [10.2, 36.9]]],
                    'properties' => ['summary' => ['distance' => 12.5, 'duration' => 1800]],
                ]],
            ], 200),
        ]);

        $admin = User::factory()->admin()->create();
        $quartier = Quartier::factory()->create();
        $lowerPriority = Coupure::factory()->for($quartier)->create([
            'statut' => 'en_cours',
            'latitude' => 36.9,
            'longitude' => 10.2,
            'date_debut' => now(),
        ]);
        $higherPriority = Coupure::factory()->for($quartier)->create([
            'statut' => 'en_cours',
            'latitude' => 36.8,
            'longitude' => 10.1,
            'date_debut' => now()->subHours(20),
        ]);
        ImpactCoupure::factory()->for($higherPriority)->create([
            'niveau_gravite' => 'critique',
            'categorie' => 'ecole',
            'personnes_affectees' => 1000,
        ]);

        $response = $this->actingAs($admin)->postJson('/admin/smartgrid/route', [
            'origin' => ['latitude' => 36.7, 'longitude' => 10.0],
            'coupure_ids' => [$lowerPriority->id, $higherPriority->id],
        ])->assertOk()
            ->assertJsonPath('distance_km', 12.5)
            ->assertJsonPath('duration_seconds', 1800)
            ->assertJsonPath('steps.0.id', $higherPriority->id)
            ->assertJsonPath('geometry.type', 'LineString');

        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'test-route-key'));
    }

    public function test_route_provider_errors_are_reported_without_invented_distance(): void
    {
        config([
            'services.openrouteservice.api_key' => 'test-route-key',
            'services.openrouteservice.base_url' => 'https://api.openrouteservice.test',
        ]);
        Http::fake([
            'api.openrouteservice.test/v2/matrix/*' => Http::response(['error' => 'unavailable'], 503),
        ]);
        $admin = User::factory()->admin()->create();
        $coupure = Coupure::factory()->create([
            'statut' => 'en_cours',
            'latitude' => 36.81,
            'longitude' => 10.18,
        ]);

        $this->actingAs($admin)->postJson('/admin/smartgrid/route', [
            'origin' => ['latitude' => 36.8, 'longitude' => 10.1],
            'coupure_ids' => [$coupure->id],
        ])->assertStatus(503)
            ->assertJsonPath('message', 'Le service de routage est momentanément indisponible. Les distances n’ont pas été estimées.')
            ->assertJsonMissingPath('distance_km');
    }
}
