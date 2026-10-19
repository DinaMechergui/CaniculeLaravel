<?php

namespace Tests\Feature;

use App\Models\CampagneConseil;
use App\Models\CategorieConseil;
use App\Models\Conseil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ConseilTest extends TestCase
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

    // --- FRONT OFFICE ---

    public function test_front_conseils_index_renders_with_filters_and_search(): void
    {
        $cat1 = CategorieConseil::factory()->create(['nom' => 'Hydratation']);
        $cat2 = CategorieConseil::factory()->create(['nom' => 'Alimentation']);

        $c1 = Conseil::factory()->create([
            'categorie_conseil_id' => $cat1->id,
            'titre' => 'Boire régulièrement de l eau',
        ]);
        $c2 = Conseil::factory()->create([
            'categorie_conseil_id' => $cat2->id,
            'titre' => 'Manger des fruits frais',
        ]);

        // Access index without filters
        $response = $this->get(route('conseils.index'));
        $response->assertOk();
        $response->assertViewHas('conseils');
        $response->assertViewHas('categories');

        // Filter by category
        $filterResponse = $this->get(route('conseils.index', ['categorie' => $cat1->id]));
        $filterResponse->assertOk();
        $filterResponse->assertSee($c1->titre);

        // Search query
        $searchResponse = $this->get(route('conseils.index', ['search' => 'fruits']));
        $searchResponse->assertOk();
        $searchResponse->assertSee($c2->titre);
    }

    public function test_front_conseil_show_displays_conseil_and_similaires(): void
    {
        $cat = CategorieConseil::factory()->create();
        $conseil = Conseil::factory()->create(['categorie_conseil_id' => $cat->id]);
        $autreConseil = Conseil::factory()->create(['categorie_conseil_id' => $cat->id]);

        $response = $this->get(route('conseils.show', $conseil));
        $response->assertOk();
        $response->assertViewHas('conseil');
        $response->assertViewHas('similaires');
    }

    // --- BACK OFFICE : CATEGORIES ---

    public function test_admin_can_manage_categories_conseil(): void
    {
        // Index & create
        $this->actingAs($this->admin)->get(route('admin.categories-conseil.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.categories-conseil.create'))->assertOk();

        // Store
        $storeResponse = $this->actingAs($this->admin)->post(route('admin.categories-conseil.store'), [
            'nom' => 'Santé & Bien-être',
            'description' => 'Conseils médicaux et bien-être.',
        ]);
        $storeResponse->assertRedirect(route('admin.categories-conseil.index'));
        $this->assertDatabaseHas('categorie_conseils', ['nom' => 'Santé & Bien-être']);

        $cat = CategorieConseil::where('nom', 'Santé & Bien-être')->first();

        // Show & Edit
        $this->actingAs($this->admin)->get(route('admin.categories-conseil.show', $cat))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.categories-conseil.edit', $cat))->assertOk();

        // Update
        $updateResponse = $this->actingAs($this->admin)->put(route('admin.categories-conseil.update', $cat), [
            'nom' => 'Santé et Climat',
            'description' => 'Nouvelle description.',
        ]);
        $updateResponse->assertRedirect(route('admin.categories-conseil.index'));
        $this->assertDatabaseHas('categorie_conseils', ['nom' => 'Santé et Climat']);

        // Destroy without relations
        $destroyResponse = $this->actingAs($this->admin)->delete(route('admin.categories-conseil.destroy', $cat));
        $destroyResponse->assertRedirect(route('admin.categories-conseil.index'));
        $this->assertDatabaseMissing('categorie_conseils', ['id' => $cat->id]);
    }

    public function test_admin_cannot_delete_category_with_attached_conseils(): void
    {
        $cat = CategorieConseil::factory()->create();
        Conseil::factory()->create(['categorie_conseil_id' => $cat->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.categories-conseil.destroy', $cat));
        $response->assertRedirect(route('admin.categories-conseil.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categorie_conseils', ['id' => $cat->id]);
    }

    // --- BACK OFFICE : CONSEILS ---

    public function test_admin_can_manage_conseils(): void
    {
        $cat = CategorieConseil::factory()->create();

        // Index & Create
        $this->actingAs($this->admin)->get(route('admin.conseils.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.conseils.create'))->assertOk();

        // Store
        $storeResponse = $this->actingAs($this->admin)->post(route('admin.conseils.store'), [
            'categorie_conseil_id' => $cat->id,
            'titre' => 'Fermer les volets le jour',
            'contenu' => 'Gardez votre maison fraîche en fermant les fenêtres et volets.',
        ]);
        $storeResponse->assertRedirect(route('admin.conseils.index'));
        $this->assertDatabaseHas('conseils', ['titre' => 'Fermer les volets le jour']);

        $conseil = Conseil::where('titre', 'Fermer les volets le jour')->first();

        // Show & Edit
        $this->actingAs($this->admin)->get(route('admin.conseils.show', $conseil))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.conseils.edit', $conseil))->assertOk();

        // Update
        $updateResponse = $this->actingAs($this->admin)->put(route('admin.conseils.update', $conseil), [
            'categorie_conseil_id' => $cat->id,
            'titre' => 'Fermer les volets pendant la journée',
            'contenu' => 'Texte modifié.',
        ]);
        $updateResponse->assertRedirect(route('admin.conseils.index'));
        $this->assertDatabaseHas('conseils', ['titre' => 'Fermer les volets pendant la journée']);

        // Destroy
        $destroyResponse = $this->actingAs($this->admin)->delete(route('admin.conseils.destroy', $conseil));
        $destroyResponse->assertRedirect(route('admin.conseils.index'));
        $this->assertDatabaseMissing('conseils', ['id' => $conseil->id]);
    }

    public function test_admin_cannot_delete_conseil_with_active_campagnes(): void
    {
        $conseil = Conseil::factory()->create();
        CampagneConseil::factory()->create(['conseil_id' => $conseil->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.conseils.destroy', $conseil));
        $response->assertRedirect(route('admin.conseils.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('conseils', ['id' => $conseil->id]);
    }

    public function test_admin_can_generate_conseil_via_ai(): void
    {
        config(['services.openrouter.key' => 'fake-test-key']);
        config(['services.openrouter.model' => 'openrouter/free']);

        Http::fake([
            'https://openrouter.ai/api/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Voici un conseil pratique rédigé par l IA pour rester au frais.',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.conseils.generer'), [
            'titre' => 'Bien aérer la nuit',
            'categorie' => 'Ventilation',
        ]);

        $response->assertOk();
        $response->assertJson([
            'contenu' => 'Voici un conseil pratique rédigé par l IA pour rester au frais.',
        ]);
    }

    public function test_admin_generate_ai_returns_503_when_no_api_key(): void
    {
        config(['services.openrouter.conseil_key' => '']);
        config(['services.openrouter.key' => '']);

        $response = $this->actingAs($this->admin)->postJson(route('admin.conseils.generer'), [
            'titre' => 'Test sans clé',
        ]);

        $response->assertStatus(503);
    }
}
