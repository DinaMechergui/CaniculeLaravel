<?php

namespace Tests\Feature;

use App\Models\Diagnostic;
use App\Models\Signalement;
use App\Models\TypeSignalement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignalementTest extends TestCase
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

    public function test_front_signalements_index_is_public(): void
    {
        $type = TypeSignalement::factory()->create();
        Signalement::factory()->create(['type_signalement_id' => $type->id]);

        $response = $this->get(route('signalements.index'));

        $response->assertOk();
        $response->assertViewHas('signalements');
    }

    public function test_front_signalement_show_displays_details(): void
    {
        $type = TypeSignalement::factory()->create();
        $signalement = Signalement::factory()->create(['type_signalement_id' => $type->id]);

        $response = $this->get(route('signalements.show', $signalement));

        $response->assertOk();
        $response->assertViewHas('signalement');
    }

    public function test_guest_cannot_access_front_signalement_creation(): void
    {
        $this->get(route('signalements.create'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_front_signalement_creation(): void
    {
        TypeSignalement::factory()->create();

        $response = $this->actingAs($this->user)->get(route('signalements.create'));

        $response->assertOk();
        $response->assertViewIs('front.signalements.create');
    }

    public function test_authenticated_user_can_store_signalement_with_diagnostic(): void
    {
        $type = TypeSignalement::factory()->create();

        $data = [
            'type_signalement_id' => $type->id,
            'description'         => 'Coupure soudaine dans le quartier nord',
            'statut'              => Signalement::STATUT_NOUVEAU,
            'avec_diagnostic'     => '1',
            'zone_touchee'        => array_key_first(Diagnostic::ZONES),
            'heure_panne'         => '14:30',
            'temperature'         => 25.5,
            'odeur_brule'         => '1',
        ];

        $response = $this->actingAs($this->user)->post(route('signalements.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('signalements', [
            'description' => 'Coupure soudaine dans le quartier nord',
            'user_id'     => $this->user->id,
        ]);
        $this->assertDatabaseHas('diagnostics', [
            'zone_touchee' => array_key_first(Diagnostic::ZONES),
            'heure_panne'  => '14:30',
        ]);
    }

    // --- BACK OFFICE ---

    public function test_admin_can_access_back_signalements_index(): void
    {
        $type = TypeSignalement::factory()->create();
        Signalement::factory()->create(['type_signalement_id' => $type->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.signalements.index'));

        $response->assertOk();
        $response->assertViewHas('signalements');
    }

    public function test_admin_can_create_and_store_signalement(): void
    {
        $type = TypeSignalement::factory()->create();

        $createResponse = $this->actingAs($this->admin)->get(route('admin.signalements.create'));
        $createResponse->assertOk();

        $storeResponse = $this->actingAs($this->admin)->post(route('admin.signalements.store'), [
            'type_signalement_id' => $type->id,
            'description'         => 'Nouveau problème technique urgent',
            'statut'              => Signalement::STATUT_NOUVEAU,
        ]);

        $storeResponse->assertRedirect(route('admin.signalements.index'));
        $this->assertDatabaseHas('signalements', [
            'description' => 'Nouveau problème technique urgent',
            'statut'      => Signalement::STATUT_NOUVEAU,
        ]);
    }

    public function test_admin_can_show_and_edit_signalement(): void
    {
        $type = TypeSignalement::factory()->create();
        $signalement = Signalement::factory()->create(['type_signalement_id' => $type->id]);

        $showResponse = $this->actingAs($this->admin)->get(route('admin.signalements.show', $signalement));
        $showResponse->assertOk();

        $editResponse = $this->actingAs($this->admin)->get(route('admin.signalements.edit', $signalement));
        $editResponse->assertOk();
    }

    public function test_admin_can_update_signalement_status(): void
    {
        $type = TypeSignalement::factory()->create();
        $signalement = Signalement::factory()->create([
            'type_signalement_id' => $type->id,
            'statut'              => Signalement::STATUT_NOUVEAU,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.signalements.update', $signalement), [
            'type_signalement_id' => $type->id,
            'description'         => $signalement->description,
            'statut'              => Signalement::STATUT_TRAITE,
        ]);

        $response->assertRedirect(route('admin.signalements.index'));
        $this->assertDatabaseHas('signalements', [
            'id'     => $signalement->id,
            'statut' => Signalement::STATUT_TRAITE,
        ]);
    }

    public function test_admin_can_delete_signalement(): void
    {
        $type = TypeSignalement::factory()->create();
        $signalement = Signalement::factory()->create(['type_signalement_id' => $type->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.signalements.destroy', $signalement));

        $response->assertRedirect(route('admin.signalements.index'));
        $this->assertDatabaseMissing('signalements', ['id' => $signalement->id]);
    }

    public function test_admin_can_manage_type_signalements(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.types-signalement.index'));
        $response->assertOk();

        $createResponse = $this->actingAs($this->admin)->get(route('admin.types-signalement.create'));
        $createResponse->assertOk();

        // Créer un type
        $postResponse = $this->actingAs($this->admin)->post(route('admin.types-signalement.store'), [
            'libelle' => 'Inondation',
        ]);
        $postResponse->assertRedirect(route('admin.types-signalement.index'));
        $this->assertDatabaseHas('type_signalements', ['libelle' => 'Inondation']);

        $type = TypeSignalement::where('libelle', 'Inondation')->first();

        // Show & Edit
        $this->actingAs($this->admin)->get(route('admin.types-signalement.show', $type))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.types-signalement.edit', $type))->assertOk();

        // Update
        $updateResponse = $this->actingAs($this->admin)->put(route('admin.types-signalement.update', $type), [
            'libelle' => 'Inondation Majeure',
        ]);
        $updateResponse->assertRedirect(route('admin.types-signalement.index'));
        $this->assertDatabaseHas('type_signalements', ['libelle' => 'Inondation Majeure']);

        // Delete without attached signalements
        $delResponse = $this->actingAs($this->admin)->delete(route('admin.types-signalement.destroy', $type));
        $delResponse->assertRedirect(route('admin.types-signalement.index'));
        $this->assertDatabaseMissing('type_signalements', ['id' => $type->id]);
    }

    public function test_cannot_delete_type_signalement_with_attached_signalements(): void
    {
        $type = TypeSignalement::factory()->create();
        Signalement::factory()->create(['type_signalement_id' => $type->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.types-signalement.destroy', $type));
        $response->assertRedirect(route('admin.types-signalement.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('type_signalements', ['id' => $type->id]);
    }
}
