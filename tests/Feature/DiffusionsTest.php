<?php

namespace Tests\Feature;

use App\Models\Alerte;
use App\Models\BulletinAlerte;
use App\Models\Diffusion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiffusionsTest extends TestCase
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

    public function test_diffusion_relations_and_accessors(): void
    {
        $alerte = Alerte::factory()->create();
        $bulletin = BulletinAlerte::factory()->create(['alerte_id' => $alerte->id]);

        $diffusion = Diffusion::factory()->create([
            'bulletin_alerte_id' => $bulletin->id,
            'canal' => 'sms',
            'statut' => 'envoyee',
        ]);

        $this->assertInstanceOf(BulletinAlerte::class, $diffusion->bulletinAlerte);
        $this->assertEquals($bulletin->id, $diffusion->bulletinAlerte->id);
        $this->assertEquals('SMS d\'alerte', $diffusion->canal_label);
        $this->assertStringContainsString('bx-message-rounded-dots', $diffusion->canal_icon);
        $this->assertStringContainsString('bg-success', $diffusion->statut_badge_class);
    }

    /*
    |--------------------------------------------------------------------------
    | Back Office : Affichage & Filtres
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_list_and_filter_diffusions(): void
    {
        $bulletin = BulletinAlerte::factory()->create();

        Diffusion::factory()->create([
            'bulletin_alerte_id' => $bulletin->id,
            'canal' => 'sms',
            'statut' => 'envoyee',
            'contenu' => 'Diffusion SMS Spécifique',
        ]);

        Diffusion::factory()->create([
            'bulletin_alerte_id' => $bulletin->id,
            'canal' => 'email',
            'statut' => 'planifiee',
            'contenu' => 'Diffusion Email Spécifique',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.diffusions.index', [
            'canal' => 'sms',
            'statut' => 'envoyee',
        ]));

        $response->assertOk();
        $response->assertViewIs('back.diffusions.index');
        $response->assertSee('Diffusion SMS Spécifique');
        $response->assertDontSee('Diffusion Email Spécifique');
    }

    public function test_admin_can_render_create_page(): void
    {
        $bulletin = BulletinAlerte::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.diffusions.create', [
            'bulletin_alerte_id' => $bulletin->id,
        ]));

        $response->assertOk();
        $response->assertViewIs('back.diffusions.create');
        $response->assertSee($bulletin->alerte->titre);
    }

    /*
    |--------------------------------------------------------------------------
    | Back Office : CRUD Diffusion
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_diffusion(): void
    {
        $bulletin = BulletinAlerte::factory()->create();

        $data = [
            'bulletin_alerte_id' => $bulletin->id,
            'canal' => 'notification_push',
            'contenu' => 'Alerte météo immédiate : restez informés.',
            'destinataires_estimes' => 12500,
            'date_envoi' => now()->format('Y-m-d H:i:s'),
            'statut' => 'envoyee',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.diffusions.store'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('diffusions', [
            'bulletin_alerte_id' => $bulletin->id,
            'canal' => 'notification_push',
            'statut' => 'envoyee',
            'destinataires_estimes' => 12500,
        ]);
    }

    public function test_create_diffusion_validation_rules(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.diffusions.store'), [
            'contenu' => str_repeat('a', 200), // > 160
            'canal' => 'canal_inexistant',
            'statut' => 'statut_invalide',
        ]);

        $response->assertSessionHasErrors(['bulletin_alerte_id', 'canal', 'contenu', 'date_envoi', 'statut']);
    }

    public function test_admin_can_render_edit_page(): void
    {
        $diffusion = Diffusion::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.diffusions.edit', $diffusion));
        $response->assertOk();
        $response->assertViewIs('back.diffusions.edit');
        $response->assertSee($diffusion->contenu);
    }

    public function test_admin_can_update_diffusion(): void
    {
        $diffusion = Diffusion::factory()->create([
            'canal' => 'sms',
            'statut' => 'planifiee',
            'contenu' => 'Contenu avant modification',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.diffusions.update', $diffusion), [
            'bulletin_alerte_id' => $diffusion->bulletin_alerte_id,
            'canal' => 'email',
            'statut' => 'envoyee',
            'contenu' => 'Contenu après modification réussie',
            'destinataires_estimes' => 45000,
            'date_envoi' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('admin.diffusions.show', $diffusion));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('diffusions', [
            'id' => $diffusion->id,
            'canal' => 'email',
            'statut' => 'envoyee',
            'contenu' => 'Contenu après modification réussie',
            'destinataires_estimes' => 45000,
        ]);
    }

    public function test_admin_can_delete_diffusion(): void
    {
        $diffusion = Diffusion::factory()->create();
        $bulletin = $diffusion->bulletinAlerte;

        $response = $this->actingAs($this->admin)->delete(route('admin.diffusions.destroy', $diffusion));

        $response->assertRedirect(route('admin.diffusions.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('diffusions', ['id' => $diffusion->id]);
    }

    public function test_admin_can_view_diffusion_detail(): void
    {
        $diffusion = Diffusion::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.diffusions.show', $diffusion));
        $response->assertOk();
        $response->assertViewIs('back.diffusions.show');
        $response->assertSee($diffusion->contenu);
    }

    public function test_diffusion_all_channel_icons_and_badge_classes(): void
    {
        $canaux = ['sms', 'email', 'notification_push', 'sirene', 'affichage', 'inconnu'];
        $statuts = ['envoyee', 'planifiee', 'echec', 'inconnu'];

        foreach ($canaux as $c) {
            $d = new Diffusion(['canal' => $c, 'statut' => 'envoyee']);
            $this->assertNotEmpty($d->canal_icon);
            $this->assertNotEmpty($d->canal_label);
        }

        foreach ($statuts as $s) {
            $d = new Diffusion(['canal' => 'sms', 'statut' => $s]);
            $this->assertNotEmpty($d->statut_badge_class);
        }
    }
}
