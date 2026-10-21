<?php

namespace Tests\Feature;

use App\Models\PointFraicheur;
use App\Models\Reservation;
use App\Notifications\ReservationAnnulee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Tests des fonctionnalités front office du module « Réservations » (phase 3) :
 * recherche par code + e-mail, annulation côté client, protection anti-robot
 * (honeypot) et limitation de débit (throttle).
 */
class ReservationFrontTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Recherche par code + e-mail
    |--------------------------------------------------------------------------
    */

    public function test_search_page_is_accessible(): void
    {
        $this->get('/reservations/recherche')->assertOk();
    }

    public function test_search_with_valid_code_and_email_redirects_to_show(): void
    {
        $point = PointFraicheur::factory()->create();
        $reservation = Reservation::factory()->create([
            'point_fraicheur_id' => $point->id,
            'email' => 'cherche@example.com',
        ]);

        $this->post('/reservations/recherche', [
            'code' => $reservation->code,
            'email' => 'cherche@example.com',
        ])->assertRedirect('/reservations/' . $reservation->code);
    }

    public function test_search_is_case_insensitive_on_code(): void
    {
        $point = PointFraicheur::factory()->create();
        $reservation = Reservation::factory()->create([
            'point_fraicheur_id' => $point->id,
            'email' => 'majuscules@example.com',
        ]);

        $this->post('/reservations/recherche', [
            'code' => strtolower($reservation->code),
            'email' => 'majuscules@example.com',
        ])->assertRedirect('/reservations/' . $reservation->code);
    }

    public function test_search_with_wrong_email_shows_error(): void
    {
        $point = PointFraicheur::factory()->create();
        $reservation = Reservation::factory()->create([
            'point_fraicheur_id' => $point->id,
            'email' => 'vrai@example.com',
        ]);

        $this->post('/reservations/recherche', [
            'code' => $reservation->code,
            'email' => 'faux@example.com',
        ])->assertSessionHas('error');
    }

    /*
    |--------------------------------------------------------------------------
    | Annulation côté client
    |--------------------------------------------------------------------------
    */

    public function test_client_can_cancel_an_active_reservation(): void
    {
        Notification::fake();
        $point = PointFraicheur::factory()->create();
        $reservation = Reservation::factory()->create([
            'point_fraicheur_id' => $point->id,
            'statut' => Reservation::STATUT_CONFIRMEE,
            'date_reservation' => now()->addDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
        ]);

        $this->post('/reservations/' . $reservation->code . '/annuler')
            ->assertRedirect('/reservations/' . $reservation->code);

        $this->assertSame(Reservation::STATUT_ANNULEE, $reservation->fresh()->statut);
        Notification::assertSentOnDemand(ReservationAnnulee::class);
    }

    public function test_client_cannot_cancel_a_past_reservation(): void
    {
        Notification::fake();
        $point = PointFraicheur::factory()->create();
        $reservation = Reservation::factory()->create([
            'point_fraicheur_id' => $point->id,
            'statut' => Reservation::STATUT_CONFIRMEE,
            'date_reservation' => now()->subDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
        ]);

        $this->post('/reservations/' . $reservation->code . '/annuler')
            ->assertSessionHas('error');

        $this->assertSame(Reservation::STATUT_CONFIRMEE, $reservation->fresh()->statut);
        Notification::assertNothingSent();
    }

    /*
    |--------------------------------------------------------------------------
    | Anti-robot et limitation de débit
    |--------------------------------------------------------------------------
    */

    public function test_honeypot_field_rejects_bots(): void
    {
        $point = PointFraicheur::factory()->create();

        $this->post('/reservations', [
            'point_fraicheur_id' => $point->id,
            'nom' => 'Robot',
            'email' => 'robot@example.com',
            'nombre_personnes' => 1,
            'date_reservation' => now()->addDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'website' => 'http://spam.example.com',
        ])->assertSessionHasErrors('website');

        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_store_endpoint_is_rate_limited(): void
    {
        // 10 tentatives acceptées puis refus (429) par le throttle « 10,1 ».
        for ($i = 0; $i < 10; $i++) {
            $this->post('/reservations', [])->assertStatus(302);
        }

        $this->post('/reservations', [])->assertStatus(429);
    }

    /*
    |--------------------------------------------------------------------------
    | Validation d'arrivée par scan du QR code
    |--------------------------------------------------------------------------
    */

    public function test_scanning_validates_a_confirmed_reservation(): void
    {
        $point = PointFraicheur::factory()->create(['nom' => 'Parc Test Arrivée']);
        $reservation = Reservation::factory()->create([
            'point_fraicheur_id' => $point->id,
            'statut' => Reservation::STATUT_CONFIRMEE,
            'check_in_le' => null,
            'date_reservation' => now()->toDateString(),
        ]);

        $this->get('/reservations/' . $reservation->code . '/arrivee')
            ->assertOk()
            ->assertSee('Arrivée')
            ->assertSee($reservation->code)
            ->assertSee('Parc Test Arrivée');

        $fraiche = $reservation->fresh();
        $this->assertSame(Reservation::STATUT_TERMINEE, $fraiche->statut);
        $this->assertNotNull($fraiche->check_in_le);
    }

    public function test_scanning_does_not_validate_a_cancelled_reservation(): void
    {
        $point = PointFraicheur::factory()->create();
        $reservation = Reservation::factory()->create([
            'point_fraicheur_id' => $point->id,
            'statut' => Reservation::STATUT_ANNULEE,
        ]);

        $this->get('/reservations/' . $reservation->code . '/arrivee')
            ->assertOk()
            ->assertSee('non valide');

        $this->assertSame(Reservation::STATUT_ANNULEE, $reservation->fresh()->statut);
        $this->assertNull($reservation->fresh()->check_in_le);
    }

    public function test_scanning_twice_keeps_the_first_check_in(): void
    {
        $point = PointFraicheur::factory()->create();
        $reservation = Reservation::factory()->create([
            'point_fraicheur_id' => $point->id,
            'statut' => Reservation::STATUT_TERMINEE,
        ]);

        $checkInInitial = $reservation->fresh()->check_in_le;

        $this->get('/reservations/' . $reservation->code . '/arrivee')
            ->assertOk()
            ->assertSee('déjà validée');

        $this->assertEquals($checkInInitial, $reservation->fresh()->check_in_le);
    }
}
