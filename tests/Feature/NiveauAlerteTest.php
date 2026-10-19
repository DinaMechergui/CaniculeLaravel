<?php

namespace Tests\Feature;

use App\Models\AlerteMeteo;
use App\Models\NiveauAlerte;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NiveauAlerteTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->user = User::factory()->create();
    }

    public function test_guest_is_redirected_from_niveaux_alerte(): void
    {
        $this->get(route('admin.niveaux-alerte.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_index_and_search(): void
    {
        $n1 = NiveauAlerte::factory()->create(['libelle' => 'Vigilance Jaune', 'couleur' => 'jaune', 'niveau' => 2]);
        $n2 = NiveauAlerte::factory()->create(['libelle' => 'Vigilance Rouge', 'couleur' => 'rouge', 'niveau' => 4]);

        $response = $this->actingAs($this->admin)->get(route('admin.niveaux-alerte.index'));
        $response->assertOk();
        $response->assertViewHas('niveauxAlerte');

        // Search
        $searchResponse = $this->actingAs($this->admin)->get(route('admin.niveaux-alerte.index', ['search' => 'Rouge']));
        $searchResponse->assertOk();
        $searchResponse->assertSee('Vigilance Rouge');
    }

    public function test_admin_can_create_and_store_niveau_alerte(): void
    {
        $this->actingAs($this->admin)->get(route('admin.niveaux-alerte.create'))->assertOk();

        $response = $this->actingAs($this->admin)->post(route('admin.niveaux-alerte.store'), [
            'libelle' => 'Vigilance Orange Tempête',
            'couleur' => 'orange',
            'niveau'  => 3,
        ]);

        $response->assertRedirect(route('admin.niveaux-alerte.index'));
        $this->assertDatabaseHas('niveau_alertes', [
            'libelle' => 'Vigilance Orange Tempête',
            'couleur' => 'orange',
            'niveau'  => 3,
        ]);
    }

    public function test_admin_can_show_and_edit_niveau_alerte(): void
    {
        $niveau = NiveauAlerte::factory()->create();

        $this->actingAs($this->admin)->get(route('admin.niveaux-alerte.show', $niveau))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.niveaux-alerte.edit', $niveau))->assertOk();
    }

    public function test_admin_can_update_niveau_alerte(): void
    {
        $niveau = NiveauAlerte::factory()->create(['libelle' => 'Ancien Libelle', 'couleur' => 'jaune', 'niveau' => 2]);

        $response = $this->actingAs($this->admin)->put(route('admin.niveaux-alerte.update', $niveau), [
            'libelle' => 'Nouveau Libelle Modifie',
            'couleur' => 'rouge',
            'niveau'  => 4,
        ]);

        $response->assertRedirect(route('admin.niveaux-alerte.index'));
        $this->assertDatabaseHas('niveau_alertes', [
            'id'      => $niveau->id,
            'libelle' => 'Nouveau Libelle Modifie',
            'couleur' => 'rouge',
            'niveau'  => 4,
        ]);
    }

    public function test_admin_can_delete_niveau_alerte_without_alertes(): void
    {
        $niveau = NiveauAlerte::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.niveaux-alerte.destroy', $niveau));
        $response->assertRedirect(route('admin.niveaux-alerte.index'));
        $this->assertDatabaseMissing('niveau_alertes', ['id' => $niveau->id]);
    }

    public function test_admin_cannot_delete_niveau_alerte_with_alertes(): void
    {
        $niveau = NiveauAlerte::factory()->create();
        AlerteMeteo::factory()->create(['niveau_alerte_id' => $niveau->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.niveaux-alerte.destroy', $niveau));
        $response->assertRedirect(route('admin.niveaux-alerte.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('niveau_alertes', ['id' => $niveau->id]);
    }
}
