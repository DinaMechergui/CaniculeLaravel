<?php

namespace Tests\Feature;

use App\Models\Alerte;
use App\Models\BulletinAlerte;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertesTest extends TestCase
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

    /*
    |--------------------------------------------------------------------------
    | Modèle et Relations
    |--------------------------------------------------------------------------
    */

    public function test_alerte_has_bulletins_and_accessors(): void
    {
        $alerte = Alerte::factory()->create([
            'statut' => 'en_cours',
        ]);

        $b1 = BulletinAlerte::factory()->create([
            'alerte_id' => $alerte->id,
            'niveau' => 'jaune',
            'date_publication' => now()->subHours(2),
        ]);

        $b2 = BulletinAlerte::factory()->create([
            'alerte_id' => $alerte->id,
            'niveau' => 'rouge',
            'date_publication' => now()->subHour(),
        ]);

        $this->assertCount(2, $alerte->fresh()->bulletins);
        $this->assertEquals($b2->id, $alerte->fresh()->dernierBulletin->id);
        $this->assertEquals('rouge', $alerte->fresh()->niveau_actuel);
        $this->assertEquals(4, $alerte->fresh()->gravite_score);
    }

    public function test_alerte_scopes(): void
    {
        Alerte::factory()->enCours()->create();
        Alerte::factory()->terminee()->create();

        $this->assertEquals(1, Alerte::enCours()->count());
        $this->assertEquals(1, Alerte::terminees()->count());
    }

    /*
    |--------------------------------------------------------------------------
    | Sécurité & Autorisations Back Office
    |--------------------------------------------------------------------------
    */

    public function test_guest_is_redirected_to_login_from_admin_alertes(): void
    {
        $response = $this->get(route('admin.alertes.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_simple_user_cannot_access_admin_alertes(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.alertes.index'));
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));
    }

    /*
    |--------------------------------------------------------------------------
    | Back Office : CRUD Alerte
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_view_alertes_list(): void
    {
        Alerte::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.alertes.index'));
        $response->assertOk();
        $response->assertViewIs('back.alertes.index');
    }

    public function test_admin_can_filter_alertes_list(): void
    {
        Alerte::factory()->create(['statut' => 'en_cours', 'type' => 'canicule', 'titre' => 'Canicule Tunis']);
        Alerte::factory()->create(['statut' => 'terminee', 'type' => 'inondation', 'titre' => 'Inondation Sousse']);

        $response = $this->actingAs($this->admin)->get(route('admin.alertes.index', [
            'type' => 'canicule',
            'statut' => 'en_cours',
            'search' => 'Tunis',
        ]));

        $response->assertOk();
        $response->assertSee('Canicule Tunis');
        $response->assertDontSee('Inondation Sousse');
    }

    public function test_admin_can_render_create_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.alertes.create'));
        $response->assertOk();
        $response->assertViewIs('back.alertes.create');
    }

    public function test_admin_can_create_an_alerte(): void
    {
        $data = [
            'titre' => 'Nouvelle canicule record',
            'type' => 'canicule',
            'zone_concernee' => 'Grand Tunis',
            'description' => 'Fortes chaleurs attendues sur toute la région métropolitaine.',
            'source' => 'INM',
            'date_debut' => now()->format('Y-m-d H:i:s'),
            'statut' => 'en_cours',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.alertes.store'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('alertes', [
            'titre' => 'Nouvelle canicule record',
            'type' => 'canicule',
            'zone_concernee' => 'Grand Tunis',
        ]);
    }

    public function test_create_alerte_validation_errors(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.alertes.store'), []);

        $response->assertSessionHasErrors(['titre', 'type', 'zone_concernee', 'description', 'date_debut']);
    }

    public function test_admin_can_view_alerte_detail(): void
    {
        $alerte = Alerte::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.alertes.show', $alerte));
        $response->assertOk();
        $response->assertViewIs('back.alertes.show');
        $response->assertSee($alerte->titre);
    }

    public function test_admin_can_render_edit_page(): void
    {
        $alerte = Alerte::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.alertes.edit', $alerte));
        $response->assertOk();
        $response->assertViewIs('back.alertes.edit');
        $response->assertSee($alerte->titre);
    }

    public function test_admin_can_update_alerte(): void
    {
        $alerte = Alerte::factory()->create(['titre' => 'Ancien Titre']);

        $response = $this->actingAs($this->admin)->put(route('admin.alertes.update', $alerte), [
            'titre' => 'Titre Modifié',
            'type' => 'orage',
            'zone_concernee' => 'Bizerte',
            'description' => 'Description modifiée avec succès.',
            'date_debut' => now()->format('Y-m-d H:i:s'),
            'statut' => 'en_cours',
        ]);

        $response->assertRedirect(route('admin.alertes.show', $alerte));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('alertes', [
            'id' => $alerte->id,
            'titre' => 'Titre Modifié',
            'type' => 'orage',
        ]);
    }

    public function test_admin_can_delete_alerte_without_bulletins(): void
    {
        $alerte = Alerte::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.alertes.destroy', $alerte));

        $response->assertRedirect(route('admin.alertes.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('alertes', ['id' => $alerte->id]);
    }

    public function test_admin_cannot_delete_alerte_with_bulletins(): void
    {
        $alerte = Alerte::factory()->create();
        BulletinAlerte::factory()->create(['alerte_id' => $alerte->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.alertes.destroy', $alerte));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('alertes', ['id' => $alerte->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Front Office
    |--------------------------------------------------------------------------
    */

    public function test_front_index_shows_active_alertes_sorted_by_gravity(): void
    {
        $alerteJaune = Alerte::factory()->create(['titre' => 'Alerte Jaune', 'statut' => 'en_cours']);
        BulletinAlerte::factory()->create([
            'alerte_id' => $alerteJaune->id,
            'niveau' => 'jaune',
            'date_publication' => now(),
        ]);

        $alerteRouge = Alerte::factory()->create(['titre' => 'Alerte Rouge', 'statut' => 'en_cours']);
        BulletinAlerte::factory()->create([
            'alerte_id' => $alerteRouge->id,
            'niveau' => 'rouge',
            'date_publication' => now(),
        ]);

        $response = $this->get(route('alertes.index'));
        $response->assertOk();
        $response->assertViewIs('front.alertes.index');

        // Rouge doit apparaître avant Jaune
        $content = $response->getContent();
        $posRouge = strpos($content, 'Alerte Rouge');
        $posJaune = strpos($content, 'Alerte Jaune');
        $this->assertTrue($posRouge < $posJaune, 'L\'alerte rouge doit précéder l\'alerte jaune par ordre de gravité.');
    }

    public function test_front_show_displays_timeline(): void
    {
        $alerte = Alerte::factory()->create(['statut' => 'en_cours']);
        $b1 = BulletinAlerte::factory()->create([
            'alerte_id' => $alerte->id,
            'niveau' => 'jaune',
            'message' => 'Premier bulletin météo',
            'date_publication' => now()->subDay(),
        ]);
        $b2 = BulletinAlerte::factory()->create([
            'alerte_id' => $alerte->id,
            'niveau' => 'orange',
            'message' => 'Deuxième bulletin aggravation',
            'date_publication' => now(),
        ]);

        $response = $this->get(route('alertes.show', $alerte));
        $response->assertOk();
        $response->assertViewIs('front.alertes.show');
        $response->assertSee('Premier bulletin météo');
        $response->assertSee('Deuxième bulletin aggravation');
    }

    public function test_legacy_front_route_redirects(): void
    {
        $response = $this->get('/alertes-meteo');
        $response->assertRedirect(route('alertes.index'));
    }

    public function test_front_alertes_filters_and_scope(): void
    {
        $a1 = Alerte::factory()->create(['titre' => 'Canicule Spéciale Tunis', 'type' => 'canicule', 'statut' => 'en_cours']);
        $a2 = Alerte::factory()->create(['titre' => 'Orage Sahel', 'type' => 'orage', 'statut' => 'en_cours']);

        $response = $this->get(route('alertes.index', [
            'type' => 'canicule',
            'search' => 'Tunis',
        ]));

        $response->assertOk();
        $response->assertSee('Canicule Spéciale Tunis');
        $response->assertDontSee('Orage Sahel');
    }

    public function test_alerte_type_labels_and_unknown(): void
    {
        $known = new Alerte(['type' => 'canicule']);
        $this->assertEquals('Canicule', $known->type_label);

        $custom = new Alerte(['type' => 'phenomene_inconnu']);
        $this->assertEquals('Phenomene inconnu', $custom->type_label);
    }
}
