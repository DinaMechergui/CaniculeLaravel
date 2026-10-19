<?php

namespace Tests\Feature;

use App\Models\PointFraicheur;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests du module « Réservations » :
 * création depuis le front office (sur la fiche du point), suivi par code,
 * et gestion en lecture/modification/suppression depuis le back office.
 */
class ReservationTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Front office — création depuis la fiche du lieu
    |--------------------------------------------------------------------------
    */

    public function test_front_reservation_form_is_accessible(): void
    {
        $point = PointFraicheur::factory()->create();

        $this->get('/points-fraicheur/' . $point->id . '/reserver')
            ->assertOk()
            ->assertSee($point->nom);
    }

    public function test_front_can_create_a_reservation(): void
    {
        $point = PointFraicheur::factory()->create(['capacite' => 50]);

        $response = $this->post('/reservations', [
            'point_fraicheur_id' => $point->id,
            'nom' => 'Amina Ben Salah',
            'email' => 'amina@example.com',
            'telephone' => '20 123 456',
            'nombre_personnes' => 2,
            'date_reservation' => now()->addDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'besoins_specifiques' => 'Personne âgée, prévoir un endroit ombragé.',
        ]);

        $reservation = Reservation::first();

        $this->assertNotNull($reservation);
        $response->assertRedirect('/reservations/' . $reservation->code);
        $this->assertSame(Reservation::STATUT_EN_ATTENTE, $reservation->statut);
        $this->assertNotEmpty($reservation->code);
        $this->assertDatabaseHas('reservations', [
            'point_fraicheur_id' => $point->id,
            'email' => 'amina@example.com',
        ]);
    }

    public function test_front_reservation_requires_valid_data(): void
    {
        $response = $this->post('/reservations', []);

        $response->assertSessionHasErrors([
            'point_fraicheur_id',
            'nom',
            'email',
            'nombre_personnes',
            'date_reservation',
            'heure_debut',
            'heure_fin',
        ]);
        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_front_reservation_date_cannot_be_in_the_past(): void
    {
        $point = PointFraicheur::factory()->create();

        $this->post('/reservations', [
            'point_fraicheur_id' => $point->id,
            'nom' => 'Client Test',
            'email' => 'client@example.com',
            'nombre_personnes' => 1,
            'date_reservation' => now()->subDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
        ])->assertSessionHasErrors('date_reservation');
    }

    public function test_front_reservation_end_time_must_be_after_start(): void
    {
        $point = PointFraicheur::factory()->create();

        $this->post('/reservations', [
            'point_fraicheur_id' => $point->id,
            'nom' => 'Client Test',
            'email' => 'client@example.com',
            'nombre_personnes' => 1,
            'date_reservation' => now()->addDay()->toDateString(),
            'heure_debut' => '10:00',
            'heure_fin' => '09:00',
        ])->assertSessionHasErrors('heure_fin');
    }

    public function test_front_reservation_respects_point_capacity(): void
    {
        $point = PointFraicheur::factory()->create(['capacite' => 5]);

        // 4 places déjà réservées sur le créneau.
        Reservation::create([
            'point_fraicheur_id' => $point->id,
            'nom' => 'Premier Client',
            'email' => 'premier@example.com',
            'nombre_personnes' => 4,
            'date_reservation' => now()->addDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'statut' => Reservation::STATUT_CONFIRMEE,
        ]);

        // 3 personnes demandées → dépasse les 1 place restante.
        $this->post('/reservations', [
            'point_fraicheur_id' => $point->id,
            'nom' => 'Deuxieme Client',
            'email' => 'deuxieme@example.com',
            'nombre_personnes' => 3,
            'date_reservation' => now()->addDay()->toDateString(),
            'heure_debut' => '09:30',
            'heure_fin' => '10:30',
        ])->assertSessionHasErrors('nombre_personnes');

        // 1 personne → tient dans la place restante.
        $this->post('/reservations', [
            'point_fraicheur_id' => $point->id,
            'nom' => 'Troisieme Client',
            'email' => 'troisieme@example.com',
            'nombre_personnes' => 1,
            'date_reservation' => now()->addDay()->toDateString(),
            'heure_debut' => '09:30',
            'heure_fin' => '10:30',
        ])->assertRedirect();

        $this->assertDatabaseHas('reservations', ['email' => 'troisieme@example.com']);
    }

    public function test_front_reservation_confirmation_page_shows_the_code(): void
    {
        $point = PointFraicheur::factory()->create();

        $this->post('/reservations', [
            'point_fraicheur_id' => $point->id,
            'nom' => 'Client Test',
            'email' => 'client@example.com',
            'nombre_personnes' => 1,
            'date_reservation' => now()->addDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
        ]);

        $code = Reservation::first()->code;

        $this->get('/reservations/' . $code)
            ->assertOk()
            ->assertSee($code);
    }

    public function test_unknown_reservation_code_returns_404(): void
    {
        $this->get('/reservations/ZZZZZZZZ')->assertNotFound();
    }

    /*
    |--------------------------------------------------------------------------
    | Back office — consultation et gestion (pas de création)
    |--------------------------------------------------------------------------
    */

    public function test_guest_is_redirected_to_login_from_back_office(): void
    {
        $this->get('/admin/reservations')->assertRedirect('/login');
    }

    public function test_simple_user_gets_403_on_back_office(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/reservations')->assertForbidden();
    }

    public function test_admin_can_view_reservations_index(): void
    {
        $admin = User::factory()->admin()->create();
        $point = PointFraicheur::factory()->create();
        Reservation::factory()->create(['point_fraicheur_id' => $point->id]);

        $this->actingAs($admin)
            ->get('/admin/reservations')
            ->assertOk()
            ->assertSee('Réservations');
    }

    public function test_back_office_has_no_creation_routes(): void
    {
        $admin = User::factory()->admin()->create();

        // La page de création n'existe plus.
        $this->actingAs($admin)->get('/admin/reservations/create')->assertNotFound();
        // L'enregistrement POST n'existe plus (405 : la route GET/HEAD existe).
        $this->actingAs($admin)->post('/admin/reservations', [])->assertStatus(405);
    }

    public function test_admin_can_update_a_reservation_status(): void
    {
        $admin = User::factory()->admin()->create();
        $point = PointFraicheur::factory()->create();
        $reservation = Reservation::factory()->create([
            'point_fraicheur_id' => $point->id,
            'statut' => Reservation::STATUT_EN_ATTENTE,
        ]);

        $this->actingAs($admin)->put('/admin/reservations/' . $reservation->id, [
            'point_fraicheur_id' => $point->id,
            'nom' => $reservation->nom,
            'email' => $reservation->email,
            'telephone' => $reservation->telephone,
            'nombre_personnes' => 2,
            'date_reservation' => $reservation->date_reservation->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'statut' => Reservation::STATUT_CONFIRMEE,
            'besoins_specifiques' => null,
            'notes_admin' => 'Confirmée par téléphone.',
        ])->assertRedirect('/admin/reservations');

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'statut' => Reservation::STATUT_CONFIRMEE,
            'notes_admin' => 'Confirmée par téléphone.',
        ]);
    }

    public function test_admin_cannot_delete_a_point_that_has_reservations(): void
    {
        $admin = User::factory()->admin()->create();
        $point = PointFraicheur::factory()->create();
        Reservation::factory()->create(['point_fraicheur_id' => $point->id]);

        $this->actingAs($admin)
            ->delete('/admin/points-fraicheur/' . $point->id)
            ->assertRedirect('/admin/points-fraicheur')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('points_fraicheur', ['id' => $point->id]);
    }
}
