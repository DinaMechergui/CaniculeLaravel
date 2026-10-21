<?php

namespace Tests\Feature;

use App\Models\Coupure;
use App\Models\Quartier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoupuresTest extends TestCase
{
    use RefreshDatabase;

    public function test_front_index_lists_only_active_and_planned_outages_and_filters_by_quartier(): void
    {
        $quartier = Quartier::factory()->create(['nom' => 'El Menzah']);
        $autreQuartier = Quartier::factory()->create(['nom' => 'La Marsa']);
        Coupure::factory()->for($quartier)->create(['type' => 'panne', 'statut' => 'en_cours']);
        Coupure::factory()->for($quartier)->create(['type' => 'delestage', 'statut' => 'prevue']);
        Coupure::factory()->for($autreQuartier)->create([
            'type' => 'surcharge',
            'statut' => 'prevue',
            'description' => 'Contenu réservé à La Marsa.',
        ]);
        Coupure::factory()->for($quartier)->create(['type' => 'panne', 'statut' => 'terminee']);

        $this->get('/coupures?quartier='.$quartier->id)
            ->assertOk()
            ->assertSee('El Menzah')
            ->assertSee('Panne')
            ->assertDontSee('Contenu réservé à La Marsa.')
            ->assertDontSee('Terminée');
    }

    public function test_front_show_displays_outage_details_and_hides_completed_outages(): void
    {
        $coupure = Coupure::factory()->create([
            'type' => 'delestage',
            'statut' => 'prevue',
            'description' => 'Intervention technique programmée.',
        ]);

        $this->get('/coupures/'.$coupure->id)
            ->assertOk()
            ->assertSee($coupure->quartier->nom)
            ->assertSee('Intervention technique programmée.');

        $terminee = Coupure::factory()->create(['statut' => 'terminee']);
        $this->get('/coupures/'.$terminee->id)->assertNotFound();
    }

    public function test_admin_can_create_and_update_a_quartier_and_cannot_delete_one_with_outages(): void
    {
        $admin = User::factory()->admin()->create();
        $quartier = Quartier::factory()->create();

        $this->actingAs($admin)->post('/admin/quartiers', [
            'nom' => 'Carthage',
            'ville' => 'Tunis',
            'code_postal' => '2016',
        ])->assertRedirect('/admin/quartiers')->assertSessionHas('success');
        $this->assertDatabaseHas('quartiers', ['nom' => 'Carthage']);

        $this->put('/admin/quartiers/'.$quartier->id, [
            'nom' => 'Quartier modifié',
            'ville' => 'Ariana',
            'code_postal' => '2080',
        ])->assertRedirect('/admin/quartiers')->assertSessionHas('success');
        $this->assertDatabaseHas('quartiers', ['id' => $quartier->id, 'nom' => 'Quartier modifié']);

        Coupure::factory()->for($quartier)->create();
        $this->delete('/admin/quartiers/'.$quartier->id)
            ->assertRedirect('/admin/quartiers')
            ->assertSessionHas('error');
        $this->assertDatabaseHas('quartiers', ['id' => $quartier->id]);
    }

    public function test_quartier_name_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        Quartier::factory()->create(['nom' => 'El Menzah']);

        $this->actingAs($admin)->post('/admin/quartiers', [
            'nom' => 'El Menzah',
            'ville' => 'Tunis',
            'code_postal' => '1004',
        ])->assertSessionHasErrors('nom');

        $quartier = Quartier::firstOrFail();
        $this->put('/admin/quartiers/'.$quartier->id, [
            'nom' => $quartier->nom,
            'ville' => $quartier->ville,
            'code_postal' => $quartier->code_postal,
        ])->assertRedirect('/admin/quartiers')->assertSessionHas('success');
    }

    public function test_quartier_and_coupure_reject_missing_fields_invalid_values_and_unknown_relations(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/quartiers', [])->assertSessionHasErrors([
            'nom',
            'ville',
            'code_postal',
        ]);

        $quartier = Quartier::factory()->create();
        $this->put('/admin/quartiers/'.$quartier->id, [
            'nom' => '',
            'ville' => '',
            'code_postal' => '',
        ])->assertSessionHasErrors(['nom', 'ville', 'code_postal']);

        $this->post('/admin/coupures', [
            'quartier_id' => 999999,
            'type' => 'inconnu',
            'statut' => 'annulee',
            'date_debut' => '',
        ])->assertSessionHasErrors(['quartier_id', 'type', 'statut', 'date_debut']);

        $attributes = [
            'quartier_id' => $quartier->id,
            'type' => 'panne',
            'statut' => 'prevue',
            'date_debut' => '2026-10-08T10:00',
            'date_fin' => '2026-10-08T10:00',
        ];
        $this->post('/admin/coupures', $attributes)->assertSessionHasErrors('date_fin');
        $this->assertDatabaseCount('coupures', 0);

        $coupure = Coupure::factory()->for($quartier)->create();
        $this->put('/admin/coupures/'.$coupure->id, [
            ...$attributes,
            'quartier_id' => 999999,
            'type' => 'type-invalide',
        ])->assertSessionHasErrors(['quartier_id', 'type']);
        $this->assertDatabaseHas('coupures', ['id' => $coupure->id]);
    }

    public function test_quartier_list_is_paginated(): void
    {
        $admin = User::factory()->admin()->create();
        Quartier::factory()->count(11)->create();

        $response = $this->actingAs($admin)->get('/admin/quartiers')->assertOk();

        $this->assertCount(10, $response->viewData('quartiers')->getCollection());
        $response->assertSee('page=2', false);
    }

    public function test_admin_can_combine_quartier_search_and_outage_count_filters(): void
    {
        $admin = User::factory()->admin()->create();
        $busyQuartier = Quartier::factory()->create([
            'nom' => 'El Menzah',
            'ville' => 'Tunis',
            'code_postal' => '1004',
        ]);
        $quietQuartier = Quartier::factory()->create([
            'nom' => 'La Marsa',
            'ville' => 'Tunis',
            'code_postal' => '2070',
        ]);
        Coupure::factory()->count(3)->for($busyQuartier)->create();
        Coupure::factory()->for($quietQuartier)->create();

        $response = $this->actingAs($admin)->get('/admin/quartiers?'.http_build_query([
            'search' => '100',
            'ville' => 'Tunis',
            'coupures_min' => 2,
            'coupures_max' => 4,
            'sort' => 'coupures_count',
            'direction' => 'desc',
        ]))->assertOk();

        $this->assertSame(['El Menzah'], $response->viewData('quartiers')->getCollection()->pluck('nom')->all());
    }

    public function test_admin_can_combine_outage_filters_and_keep_them_in_pagination(): void
    {
        $admin = User::factory()->admin()->create();
        $quartier = Quartier::factory()->create(['nom' => 'El Menzah']);
        $matches = Coupure::factory()->count(11)->for($quartier)->create([
            'type' => 'panne',
            'statut' => 'prevue',
            'date_debut' => '2026-10-10 14:00:00',
            'date_fin' => '2026-10-11 16:00:00',
        ]);
        Coupure::factory()->for($quartier)->create([
            'type' => 'panne',
            'statut' => 'en_cours',
            'date_debut' => '2026-10-10 14:00:00',
            'date_fin' => '2026-10-11 16:00:00',
        ]);

        $query = http_build_query([
            'search' => 'El Menzah',
            'quartier_id' => $quartier->id,
            'type' => 'panne',
            'statut' => 'prevue',
            'date_debut_from' => '2026-10-10',
            'date_debut_to' => '2026-10-10',
            'date_fin_from' => '2026-10-11',
            'date_fin_to' => '2026-10-11',
            'sort' => 'date_debut',
            'direction' => 'asc',
        ]);

        $response = $this->actingAs($admin)->get('/admin/coupures?'.$query)->assertOk();
        $this->assertSame(
            $matches->sortBy('id')->take(10)->pluck('id')->all(),
            $response->viewData('coupures')->getCollection()->pluck('id')->all()
        );
        $response->assertSee('page=2', false)->assertSee('quartier_id='.$quartier->id, false);
    }

    public function test_admin_can_render_all_quartier_and_coupure_pages(): void
    {
        $admin = User::factory()->admin()->create();
        $quartier = Quartier::factory()->create();
        $coupure = Coupure::factory()->for($quartier)->create();

        $this->actingAs($admin)->get('/admin/quartiers')->assertOk();
        $this->get('/admin/quartiers/create')->assertOk();
        $this->get('/admin/quartiers/'.$quartier->id)->assertOk();
        $this->get('/admin/quartiers/'.$quartier->id.'/edit')->assertOk();
        $this->get('/admin/coupures')->assertOk();
        $this->get('/admin/coupures/create')->assertOk();
        $this->get('/admin/coupures/'.$coupure->id)->assertOk();
        $this->get('/admin/coupures/'.$coupure->id.'/edit')->assertOk();
    }

    public function test_admin_can_manage_outages_and_end_date_must_follow_start_date(): void
    {
        $admin = User::factory()->admin()->create();
        $quartier = Quartier::factory()->create();
        $attributes = [
            'quartier_id' => $quartier->id,
            'type' => 'delestage',
            'statut' => 'prevue',
            'date_debut' => '2026-10-08T10:00',
            'date_fin' => '2026-10-08T12:00',
            'description' => 'Coupure planifiée.',
        ];

        $this->actingAs($admin)->post('/admin/coupures', $attributes)
            ->assertRedirect('/admin/coupures')->assertSessionHas('success');
        $coupure = Coupure::firstOrFail();

        $this->put('/admin/coupures/'.$coupure->id, [...$attributes, 'statut' => 'en_cours'])
            ->assertRedirect('/admin/coupures')->assertSessionHas('success');
        $this->assertDatabaseHas('coupures', ['id' => $coupure->id, 'statut' => 'en_cours']);

        $this->put('/admin/coupures/'.$coupure->id, [...$attributes, 'date_fin' => '2026-10-08T09:00'])
            ->assertSessionHasErrors('date_fin');

        $this->delete('/admin/coupures/'.$coupure->id)
            ->assertRedirect('/admin/coupures')->assertSessionHas('success');
        $this->assertDatabaseMissing('coupures', ['id' => $coupure->id]);
    }
}
