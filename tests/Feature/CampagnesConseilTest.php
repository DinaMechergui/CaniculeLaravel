<?php

namespace Tests\Feature;

use App\Models\CampagneConseil;
use App\Models\Conseil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampagnesConseilTest extends TestCase
{
    use RefreshDatabase;

    // ---------- helpers ----------

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function user(): User
    {
        return User::factory()->create();
    }

    private function conseil(): Conseil
    {
        return Conseil::factory()->create();
    }

    private function validData(Conseil $conseil, array $overrides = []): array
    {
        return array_merge([
            'conseil_id'   => $conseil->id,
            'titre'        => 'Campagne de test',
            'public_cible' => 'tous',
            'canal'        => 'affichage',
            'date_debut'   => '2026-11-01',
            'date_fin'     => '2026-11-30',
            'statut'       => 'brouillon',
        ], $overrides);
    }

    // ---------- accès back réservé aux admins ----------

    public function test_guest_cannot_access_back_campagnes(): void
    {
        $this->get('/admin/campagnes-conseil')->assertRedirect('/login');
    }

    public function test_simple_user_cannot_access_back_campagnes(): void
    {
        $this->actingAs($this->user())->get('/admin/campagnes-conseil')->assertForbidden();
    }

    // ---------- création valide ----------

    public function test_admin_can_create_a_campagne(): void
    {
        $admin   = $this->admin();
        $conseil = $this->conseil();

        $this->actingAs($admin)
            ->post('/admin/campagnes-conseil', $this->validData($conseil))
            ->assertRedirect('/admin/campagnes-conseil')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('campagnes_conseil', [
            'titre'        => 'Campagne de test',
            'conseil_id'   => $conseil->id,
            'statut'       => 'brouillon',
        ]);
    }

    // ---------- refus de validation ----------

    public function test_campagne_requires_titre(): void
    {
        $admin   = $this->admin();
        $conseil = $this->conseil();

        $this->actingAs($admin)
            ->post('/admin/campagnes-conseil', $this->validData($conseil, ['titre' => '']))
            ->assertSessionHasErrors('titre');
    }

    public function test_campagne_requires_valid_conseil_id(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/campagnes-conseil', $this->validData($this->conseil(), ['conseil_id' => 99999]))
            ->assertSessionHasErrors('conseil_id');
    }

    public function test_campagne_rejects_invalid_public_cible(): void
    {
        $admin   = $this->admin();
        $conseil = $this->conseil();

        $this->actingAs($admin)
            ->post('/admin/campagnes-conseil', $this->validData($conseil, ['public_cible' => 'invalide']))
            ->assertSessionHasErrors('public_cible');
    }

    public function test_campagne_rejects_invalid_canal(): void
    {
        $admin   = $this->admin();
        $conseil = $this->conseil();

        $this->actingAs($admin)
            ->post('/admin/campagnes-conseil', $this->validData($conseil, ['canal' => 'tele']))
            ->assertSessionHasErrors('canal');
    }

    public function test_campagne_rejects_date_fin_before_date_debut(): void
    {
        $admin   = $this->admin();
        $conseil = $this->conseil();

        $this->actingAs($admin)
            ->post('/admin/campagnes-conseil', $this->validData($conseil, [
                'date_debut' => '2026-11-30',
                'date_fin'   => '2026-11-01',
            ]))
            ->assertSessionHasErrors('date_fin');
    }

    public function test_campagne_rejects_invalid_statut(): void
    {
        $admin   = $this->admin();
        $conseil = $this->conseil();

        $this->actingAs($admin)
            ->post('/admin/campagnes-conseil', $this->validData($conseil, ['statut' => 'suspendue']))
            ->assertSessionHasErrors('statut');
    }

    public function test_campagne_requires_date_debut(): void
    {
        $admin   = $this->admin();
        $conseil = $this->conseil();

        $this->actingAs($admin)
            ->post('/admin/campagnes-conseil', $this->validData($conseil, ['date_debut' => '']))
            ->assertSessionHasErrors('date_debut');
    }

    // ---------- modification ----------

    public function test_admin_can_update_a_campagne(): void
    {
        $admin    = $this->admin();
        $conseil  = $this->conseil();
        $campagne = CampagneConseil::factory()->for($conseil)->create(['statut' => 'brouillon']);

        $this->actingAs($admin)
            ->put('/admin/campagnes-conseil/' . $campagne->id, $this->validData($conseil, [
                'titre'  => 'Titre modifié',
                'statut' => 'brouillon',
            ]))
            ->assertRedirect('/admin/campagnes-conseil')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('campagnes_conseil', ['id' => $campagne->id, 'titre' => 'Titre modifié']);
    }

    // ---------- suppression ----------

    public function test_admin_can_delete_a_campagne(): void
    {
        $admin    = $this->admin();
        $conseil  = $this->conseil();
        $campagne = CampagneConseil::factory()->for($conseil)->create();

        $this->actingAs($admin)
            ->delete('/admin/campagnes-conseil/' . $campagne->id)
            ->assertRedirect('/admin/campagnes-conseil')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('campagnes_conseil', ['id' => $campagne->id]);
    }

    // ---------- refus suppression d'un conseil avec campagnes ----------

    public function test_cannot_delete_conseil_with_campagnes(): void
    {
        $admin    = $this->admin();
        $conseil  = $this->conseil();
        CampagneConseil::factory()->for($conseil)->create();

        $this->actingAs($admin)
            ->delete('/admin/conseils/' . $conseil->id)
            ->assertRedirect('/admin/conseils')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('conseils', ['id' => $conseil->id]);
    }

    // ---------- front : uniquement les actives ----------

    public function test_front_index_shows_only_active_campagnes(): void
    {
        $conseil = $this->conseil();

        CampagneConseil::factory()->for($conseil)->create(['titre' => 'Campagne active',    'statut' => 'active']);
        CampagneConseil::factory()->for($conseil)->create(['titre' => 'Campagne brouillon', 'statut' => 'brouillon']);
        CampagneConseil::factory()->for($conseil)->create(['titre' => 'Campagne terminée',  'statut' => 'terminee']);

        $this->get('/campagnes')
            ->assertOk()
            ->assertSee('Campagne active')
            ->assertDontSee('Campagne brouillon')
            ->assertDontSee('Campagne terminée');
    }

    public function test_front_show_returns_404_for_non_active_campagne(): void
    {
        $conseil  = $this->conseil();
        $brouillon = CampagneConseil::factory()->for($conseil)->create(['statut' => 'brouillon']);

        $this->get('/campagnes/' . $brouillon->id)->assertNotFound();
    }

    public function test_front_show_displays_active_campagne(): void
    {
        $conseil  = $this->conseil();
        $campagne = CampagneConseil::factory()->for($conseil)->create([
            'titre'  => 'Ma campagne active',
            'statut' => 'active',
        ]);

        $this->get('/campagnes/' . $campagne->id)
            ->assertOk()
            ->assertSee('Ma campagne active');
    }

    public function test_front_index_filters_by_public_cible(): void
    {
        $conseil = $this->conseil();

        CampagneConseil::factory()->for($conseil)->create([
            'titre'        => 'Pour les aînés',
            'statut'       => 'active',
            'public_cible' => 'personnes_agees',
        ]);
        CampagneConseil::factory()->for($conseil)->create([
            'titre'        => 'Pour tous',
            'statut'       => 'active',
            'public_cible' => 'tous',
        ]);

        $this->get('/campagnes?public=personnes_agees')
            ->assertOk()
            ->assertSee('Pour les aînés')
            ->assertDontSee('Pour tous');
    }
}
