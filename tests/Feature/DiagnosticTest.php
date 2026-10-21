<?php

namespace Tests\Feature;

use App\Models\Diagnostic;
use App\Models\Signalement;
use App\Models\TypeSignalement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiagnosticTest extends TestCase
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

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.diagnostics.index'))->assertRedirect(route('login'));
    }

    public function test_simple_user_cannot_access_diagnostics(): void
    {
        $this->actingAs($this->user)->get(route('admin.diagnostics.index'))->assertForbidden();
    }

    public function test_admin_can_list_diagnostics_and_filter_by_zone(): void
    {
        $type = TypeSignalement::factory()->create();
        $sig1 = Signalement::factory()->create(['type_signalement_id' => $type->id]);
        $sig2 = Signalement::factory()->create(['type_signalement_id' => $type->id]);

        Diagnostic::factory()->create(['signalement_id' => $sig1->id, 'zone_touchee' => 'logement']);
        Diagnostic::factory()->create(['signalement_id' => $sig2->id, 'zone_touchee' => 'quartier']);

        $response = $this->actingAs($this->admin)->get(route('admin.diagnostics.index', ['zone' => 'logement']));

        $response->assertOk();
        $response->assertViewHas('diagnostics');
    }

    public function test_admin_can_access_create_diagnostic_page(): void
    {
        $type = TypeSignalement::factory()->create();
        Signalement::factory()->create(['type_signalement_id' => $type->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.diagnostics.create'));

        $response->assertOk();
        $response->assertViewIs('back.diagnostics.create');
    }

    public function test_admin_can_store_a_valid_diagnostic(): void
    {
        $type = TypeSignalement::factory()->create();
        $signalement = Signalement::factory()->create(['type_signalement_id' => $type->id]);

        $data = [
            'signalement_id'         => $signalement->id,
            'zone_touchee'           => 'immeuble',
            'cause_reelle'           => 'disjoncteur_domestique',
            'heure_panne'            => '14:30',
            'temperature'            => 35.5,
            'disjoncteur_saute'      => 1,
            'baisse_tension_avant'   => 0,
            'odeur_brule'            => 0,
            'etincelles'             => 0,
            'recurrent'              => 0,
            'personnes_vulnerables'  => 0,
            'remarque'               => 'Vérifier le tableau électrique',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.diagnostics.store'), $data);

        $response->assertRedirect(route('admin.diagnostics.index'));
        $this->assertDatabaseHas('diagnostics', [
            'signalement_id' => $signalement->id,
            'zone_touchee'   => 'immeuble',
        ]);
    }

    public function test_admin_can_view_a_diagnostic(): void
    {
        $type = TypeSignalement::factory()->create();
        $signalement = Signalement::factory()->create(['type_signalement_id' => $type->id]);
        $diagnostic = Diagnostic::factory()->create(['signalement_id' => $signalement->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.diagnostics.show', $diagnostic));

        $response->assertOk();
        $response->assertViewIs('back.diagnostics.show');
    }

    public function test_admin_can_access_edit_diagnostic_page(): void
    {
        $type = TypeSignalement::factory()->create();
        $signalement = Signalement::factory()->create(['type_signalement_id' => $type->id]);
        $diagnostic = Diagnostic::factory()->create(['signalement_id' => $signalement->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.diagnostics.edit', $diagnostic));

        $response->assertOk();
        $response->assertViewIs('back.diagnostics.edit');
    }

    public function test_admin_can_update_a_diagnostic(): void
    {
        $type = TypeSignalement::factory()->create();
        $signalement = Signalement::factory()->create(['type_signalement_id' => $type->id]);
        $diagnostic = Diagnostic::factory()->create(['signalement_id' => $signalement->id, 'zone_touchee' => 'logement']);

        $updateData = [
            'signalement_id'         => $diagnostic->signalement_id,
            'zone_touchee'           => 'quartier',
            'cause_reelle'           => 'surcharge_reseau',
            'heure_panne'            => '16:00',
            'temperature'            => 41.0,
            'disjoncteur_saute'      => 0,
            'baisse_tension_avant'   => 1,
            'odeur_brule'            => 0,
            'etincelles'             => 0,
            'recurrent'              => 1,
            'personnes_vulnerables'  => 1,
            'remarque'               => 'Alerter le gestionnaire du réseau',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.diagnostics.update', $diagnostic), $updateData);

        $response->assertRedirect(route('admin.diagnostics.index'));
        $this->assertDatabaseHas('diagnostics', [
            'id'           => $diagnostic->id,
            'zone_touchee' => 'quartier',
        ]);
    }

    public function test_admin_can_delete_a_diagnostic(): void
    {
        $type = TypeSignalement::factory()->create();
        $signalement = Signalement::factory()->create(['type_signalement_id' => $type->id]);
        $diagnostic = Diagnostic::factory()->create(['signalement_id' => $signalement->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.diagnostics.destroy', $diagnostic));

        $response->assertRedirect(route('admin.diagnostics.index'));
        $this->assertDatabaseMissing('diagnostics', ['id' => $diagnostic->id]);
    }
}
