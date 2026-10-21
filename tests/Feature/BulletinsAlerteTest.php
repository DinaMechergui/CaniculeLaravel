<?php

namespace Tests\Feature;

use App\Models\Alerte;
use App\Models\BulletinAlerte;
use App\Models\Diffusion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulletinsAlerteTest extends TestCase
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
    | Relations Eloquent
    |--------------------------------------------------------------------------
    */

    public function test_bulletin_relations_and_accessors(): void
    {
        $alerte = Alerte::factory()->create();
        $bulletin = BulletinAlerte::factory()->create([
            'alerte_id' => $alerte->id,
            'niveau' => 'orange',
            'evolution' => 'aggravation',
        ]);

        $diffusion = Diffusion::factory()->create([
            'bulletin_alerte_id' => $bulletin->id,
        ]);

        $this->assertInstanceOf(Alerte::class, $bulletin->alerte);
        $this->assertEquals($alerte->id, $bulletin->alerte->id);
        $this->assertCount(1, $bulletin->fresh()->diffusions);
        $this->assertEquals($diffusion->id, $bulletin->fresh()->diffusions->first()->id);
        $this->assertEquals('Vigilance orange', $bulletin->niveau_label);
        $this->assertEquals('Aggravation de la situation', $bulletin->evolution_label);
        $this->assertStringContainsString('bx-trending-up', $bulletin->evolution_icon);
    }

    /*
    |--------------------------------------------------------------------------
    | Back Office : Affichage & Filtres
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_list_and_filter_bulletins(): void
    {
        $alerte1 = Alerte::factory()->create(['titre' => 'Alerte Canicule']);
        $alerte2 = Alerte::factory()->create(['titre' => 'Alerte Orage']);

        BulletinAlerte::factory()->create([
            'alerte_id' => $alerte1->id,
            'niveau' => 'rouge',
            'evolution' => 'aggravation',
            'message' => 'Message Rouge Canicule',
        ]);

        BulletinAlerte::factory()->create([
            'alerte_id' => $alerte2->id,
            'niveau' => 'jaune',
            'evolution' => 'stable',
            'message' => 'Message Jaune Orage',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.bulletins-alerte.index', [
            'niveau' => 'rouge',
            'evolution' => 'aggravation',
        ]));

        $response->assertOk();
        $response->assertViewIs('back.bulletins-alerte.index');
        $response->assertSee('Message Rouge Canicule');
        $response->assertDontSee('Message Jaune Orage');
    }

    public function test_admin_can_render_create_page(): void
    {
        $alerte = Alerte::factory()->enCours()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.bulletins-alerte.create', [
            'alerte_id' => $alerte->id,
        ]));

        $response->assertOk();
        $response->assertViewIs('back.bulletins-alerte.create');
        $response->assertSee($alerte->titre);
    }

    /*
    |--------------------------------------------------------------------------
    | Règles Métier : Création et validation
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_bulletin_for_active_alerte(): void
    {
        $alerte = Alerte::factory()->enCours()->create();

        $data = [
            'alerte_id' => $alerte->id,
            'niveau' => 'orange',
            'evolution' => 'stable',
            'message' => 'Météo stationnaire et chaleur maintenue.',
            'conduite_a_tenir' => 'Rester au frais.',
            'temperature_max' => 41,
            'date_publication' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.bulletins-alerte.store'), $data);

        $response->assertRedirect(route('admin.alertes.show', $alerte));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bulletins_alerte', [
            'alerte_id' => $alerte->id,
            'niveau' => 'orange',
            'evolution' => 'stable',
        ]);
    }

    public function test_cannot_create_bulletin_for_terminated_alerte(): void
    {
        $alerte = Alerte::factory()->terminee()->create();

        $data = [
            'alerte_id' => $alerte->id,
            'niveau' => 'jaune',
            'evolution' => 'stable',
            'message' => 'Tentative de mise à jour.',
            'date_publication' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.bulletins-alerte.store'), $data);

        $response->assertSessionHasErrors(['alerte_id']);
        $this->assertDatabaseMissing('bulletins_alerte', [
            'message' => 'Tentative de mise à jour.',
        ]);
    }

    public function test_aggravation_fails_if_niveau_is_lower_than_previous_bulletin(): void
    {
        $alerte = Alerte::factory()->enCours()->create();

        BulletinAlerte::factory()->create([
            'alerte_id' => $alerte->id,
            'niveau' => 'orange', // rang 3
            'evolution' => 'stable',
            'date_publication' => now()->subHours(2),
        ]);

        // Tentative d'aggravation vers jaune (rang 2 < 3)
        $data = [
            'alerte_id' => $alerte->id,
            'niveau' => 'jaune',
            'evolution' => 'aggravation',
            'message' => 'Aggravation incohérente vers jaune.',
            'date_publication' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.bulletins-alerte.store'), $data);

        $response->assertSessionHasErrors(['niveau']);
        $this->assertDatabaseMissing('bulletins_alerte', [
            'message' => 'Aggravation incohérente vers jaune.',
        ]);
    }

    public function test_aggravation_succeeds_when_niveau_is_equal_or_higher(): void
    {
        $alerte = Alerte::factory()->enCours()->create();

        BulletinAlerte::factory()->create([
            'alerte_id' => $alerte->id,
            'niveau' => 'orange', // rang 3
            'evolution' => 'stable',
            'date_publication' => now()->subHours(2),
        ]);

        // Aggravation vers rouge (rang 4 >= 3)
        $data = [
            'alerte_id' => $alerte->id,
            'niveau' => 'rouge',
            'evolution' => 'aggravation',
            'message' => 'Aggravation légitime vers le niveau maximal.',
            'date_publication' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.bulletins-alerte.store'), $data);

        $response->assertRedirect(route('admin.alertes.show', $alerte));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bulletins_alerte', [
            'alerte_id' => $alerte->id,
            'niveau' => 'rouge',
            'evolution' => 'aggravation',
        ]);
    }

    public function test_levee_automatically_terminates_alerte_and_sets_date_fin(): void
    {
        $alerte = Alerte::factory()->enCours()->create([
            'statut' => 'en_cours',
            'date_fin' => null,
        ]);

        $pubDate = now()->format('Y-m-d H:i:s');

        $data = [
            'alerte_id' => $alerte->id,
            'niveau' => 'vert',
            'evolution' => 'levee',
            'message' => 'Fin de vigilance météo.',
            'date_publication' => $pubDate,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.bulletins-alerte.store'), $data);

        $response->assertRedirect(route('admin.alertes.show', $alerte));
        $response->assertSessionHas('success');

        $alerteFresh = $alerte->fresh();
        $this->assertEquals('terminee', $alerteFresh->statut);
        $this->assertNotNull($alerteFresh->date_fin);
        $this->assertEquals($pubDate, $alerteFresh->date_fin->format('Y-m-d H:i:s'));
    }

    /*
    |--------------------------------------------------------------------------
    | Modification & Suppression
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_update_bulletin(): void
    {
        $bulletin = BulletinAlerte::factory()->create([
            'niveau' => 'jaune',
            'message' => 'Message initial',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.bulletins-alerte.update', $bulletin), [
            'alerte_id' => $bulletin->alerte_id,
            'niveau' => 'orange',
            'evolution' => 'aggravation',
            'message' => 'Message corrigé',
            'date_publication' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('admin.bulletins-alerte.show', $bulletin));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bulletins_alerte', [
            'id' => $bulletin->id,
            'message' => 'Message corrigé',
            'niveau' => 'orange',
        ]);
    }

    public function test_admin_can_delete_bulletin_without_diffusions(): void
    {
        $alerte = Alerte::factory()->create();
        $bulletin = BulletinAlerte::factory()->create(['alerte_id' => $alerte->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.bulletins-alerte.destroy', $bulletin));

        $response->assertRedirect(route('admin.alertes.show', $alerte));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('bulletins_alerte', ['id' => $bulletin->id]);
    }

    public function test_admin_cannot_delete_bulletin_with_diffusions(): void
    {
        $bulletin = BulletinAlerte::factory()->create();
        Diffusion::factory()->create(['bulletin_alerte_id' => $bulletin->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.bulletins-alerte.destroy', $bulletin));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('bulletins_alerte', ['id' => $bulletin->id]);
    }

    public function test_admin_can_view_bulletin_detail(): void
    {
        $bulletin = BulletinAlerte::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.bulletins-alerte.show', $bulletin));
        $response->assertOk();
        $response->assertViewIs('back.bulletins-alerte.show');
        $response->assertSee($bulletin->message);
    }

    public function test_admin_can_render_edit_page(): void
    {
        $bulletin = BulletinAlerte::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.bulletins-alerte.edit', $bulletin));
        $response->assertOk();
        $response->assertViewIs('back.bulletins-alerte.edit');
        $response->assertSee($bulletin->message);
    }

    public function test_updating_bulletin_to_levee_terminates_parent_alerte(): void
    {
        $alerte = Alerte::factory()->enCours()->create();
        $bulletin = BulletinAlerte::factory()->create([
            'alerte_id' => $alerte->id,
            'niveau' => 'jaune',
            'evolution' => 'stable',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.bulletins-alerte.update', $bulletin), [
            'alerte_id' => $alerte->id,
            'niveau' => 'vert',
            'evolution' => 'levee',
            'message' => 'Fin définitive de la crise',
            'date_publication' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('admin.bulletins-alerte.show', $bulletin));
        $this->assertEquals('terminee', $alerte->fresh()->statut);
    }

    public function test_bulletin_all_accessors_and_badges(): void
    {
        $niveaux = ['rouge', 'orange', 'jaune', 'vert', 'inconnu'];
        $evolutions = ['aggravation', 'stable', 'amelioration', 'levee', 'inconnu'];

        foreach ($niveaux as $n) {
            $b = new BulletinAlerte(['niveau' => $n, 'evolution' => 'stable']);
            $this->assertNotEmpty($b->niveau_badge_class);
            $this->assertNotEmpty($b->niveau_label);
        }

        foreach ($evolutions as $e) {
            $b = new BulletinAlerte(['niveau' => 'vert', 'evolution' => $e]);
            $this->assertNotEmpty($b->evolution_icon);
            $this->assertNotEmpty($b->evolution_label);
        }
    }
}
