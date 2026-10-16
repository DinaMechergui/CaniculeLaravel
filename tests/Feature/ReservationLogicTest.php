<?php

namespace Tests\Feature;

use App\Models\PointFraicheur;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\PlaceLiberee;
use App\Notifications\ReservationConfirmee;
use App\Notifications\ReservationListeAttente;
use App\Notifications\RappelReservation;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Tests de la logique métier du module « Réservations » (phase 2) :
 * créneaux, capacité, liste d'attente, règles métier, actions rapides,
 * check-in, calendrier et commandes planifiées.
 */
class ReservationLogicTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Créneaux disponibles (endpoint JSON)
    |--------------------------------------------------------------------------
    */

    public function test_creneaux_endpoint_returns_12_hourly_slots(): void
    {
        $point = PointFraicheur::factory()->create(['capacite' => 20]);
        $date = now()->addDay()->toDateString();

        $response = $this->getJson("/points-fraicheur/{$point->id}/creneaux?date={$date}");

        $response->assertOk()
            ->assertJsonPath('date', $date)
            ->assertJsonPath('capacite', 20)
            ->assertJsonCount(12, 'creneaux')
            ->assertJsonPath('creneaux.0.debut', '08:00')
            ->assertJsonPath('creneaux.0.disponible', true);
    }

    public function test_creneaux_endpoint_reflects_occupancy_and_affluence(): void
    {
        $point = PointFraicheur::factory()->create(['capacite' => 4]);

        // 4 personnes déjà réservées sur le créneau 09:00 → 10:00 (complet).
        Reservation::create([
            'point_fraicheur_id' => $point->id,
            'nom' => 'Occupant',
            'email' => 'occupant@example.com',
            'nombre_personnes' => 4,
            'date_reservation' => now()->addDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'statut' => Reservation::STATUT_CONFIRMEE,
        ]);

        $response = $this->getJson("/points-fraicheur/{$point->id}/creneaux?date=" . now()->addDay()->toDateString());

        $creneau = collect($response->json('creneaux'))->firstWhere('debut', '09:00');

        $this->assertSame(4, $creneau['utilisees']);
        $this->assertSame(0, $creneau['restantes']);
        $this->assertSame('complet', $creneau['affluence']);
        $this->assertFalse($creneau['disponible']);
    }

    /*
    |--------------------------------------------------------------------------
    | QR code
    |--------------------------------------------------------------------------
    */

    public function test_qr_endpoint_returns_svg(): void
    {
        $point = PointFraicheur::factory()->create();
        $reservation = Reservation::factory()->create(['point_fraicheur_id' => $point->id]);

        $this->get("/reservations/{$reservation->code}/qr")
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml')
            ->assertSee('<svg', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Liste d'attente
    |--------------------------------------------------------------------------
    */

    public function test_full_slot_without_waitlist_option_is_rejected(): void
    {
        Notification::fake();
        $point = PointFraicheur::factory()->create(['capacite' => 5]);

        Reservation::create([
            'point_fraicheur_id' => $point->id,
            'nom' => 'Occupant',
            'email' => 'occupant@example.com',
            'nombre_personnes' => 5,
            'date_reservation' => now()->addDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'statut' => Reservation::STATUT_CONFIRMEE,
        ]);

        $this->post('/reservations', [
            'point_fraicheur_id' => $point->id,
            'nom' => 'Client Test',
            'email' => 'client@example.com',
            'nombre_personnes' => 2,
            'date_reservation' => now()->addDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
        ])->assertSessionHasErrors('nombre_personnes');

        $this->assertDatabaseMissing('reservations', ['email' => 'client@example.com']);
    }

    public function test_full_slot_with_waitlist_option_is_queued(): void
    {
        Notification::fake();
        $point = PointFraicheur::factory()->create(['capacite' => 5]);

        Reservation::create([
            'point_fraicheur_id' => $point->id,
            'nom' => 'Occupant',
            'email' => 'occupant@example.com',
            'nombre_personnes' => 5,
            'date_reservation' => now()->addDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'statut' => Reservation::STATUT_CONFIRMEE,
        ]);

        $this->post('/reservations', [
            'point_fraicheur_id' => $point->id,
            'nom' => 'Client Attente',
            'email' => 'attente@example.com',
            'nombre_personnes' => 2,
            'date_reservation' => now()->addDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'liste_attente' => 1,
        ])->assertRedirect();

        $reservation = Reservation::where('email', 'attente@example.com')->first();
        $this->assertNotNull($reservation);
        $this->assertSame(Reservation::STATUT_LISTE_ATTENTE, $reservation->statut);

        Notification::assertSentOnDemand(ReservationListeAttente::class);
    }

    public function test_cancelling_a_reservation_promotes_the_waitlist(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $point = PointFraicheur::factory()->create(['capacite' => 5]);
        $date = now()->addDay()->toDateString();

        $pleine = Reservation::create([
            'point_fraicheur_id' => $point->id,
            'nom' => 'Occupe Tout',
            'email' => 'occupe@example.com',
            'nombre_personnes' => 5,
            'date_reservation' => $date,
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'statut' => Reservation::STATUT_CONFIRMEE,
        ]);

        $attente = Reservation::create([
            'point_fraicheur_id' => $point->id,
            'nom' => 'En Attente',
            'email' => 'attente@example.com',
            'nombre_personnes' => 3,
            'date_reservation' => $date,
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'statut' => Reservation::STATUT_LISTE_ATTENTE,
        ]);

        $this->actingAs($admin)
            ->post("/admin/reservations/{$pleine->id}/annuler")
            ->assertRedirect();

        $this->assertSame(Reservation::STATUT_ANNULEE, $pleine->fresh()->statut);
        $this->assertSame(Reservation::STATUT_CONFIRMEE, $attente->fresh()->statut);

        Notification::assertSentOnDemand(PlaceLiberee::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Règles métier
    |--------------------------------------------------------------------------
    */

    public function test_reservation_duration_cannot_exceed_three_hours(): void
    {
        $point = PointFraicheur::factory()->create();

        $this->post('/reservations', [
            'point_fraicheur_id' => $point->id,
            'nom' => 'Client Test',
            'email' => 'client@example.com',
            'nombre_personnes' => 1,
            'date_reservation' => now()->addDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '13:00',
        ])->assertSessionHasErrors('heure_fin');
    }

    public function test_same_day_reservation_must_respect_thirty_minute_lead(): void
    {
        $point = PointFraicheur::factory()->create();

        // Créneau du jour même en début de matinée : toujours passé d'ici 30 min.
        $this->post('/reservations', [
            'point_fraicheur_id' => $point->id,
            'nom' => 'Client Test',
            'email' => 'client@example.com',
            'nombre_personnes' => 1,
            'date_reservation' => now()->toDateString(),
            'heure_debut' => '00:00',
            'heure_fin' => '01:00',
        ])->assertSessionHasErrors('heure_debut');
    }

    public function test_same_email_cannot_double_book_overlapping_slots(): void
    {
        $point = PointFraicheur::factory()->create();
        $date = now()->addDay()->toDateString();

        Reservation::create([
            'point_fraicheur_id' => $point->id,
            'nom' => 'Client Déjà',
            'email' => 'dupli@example.com',
            'nombre_personnes' => 1,
            'date_reservation' => $date,
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'statut' => Reservation::STATUT_CONFIRMEE,
        ]);

        $this->post('/reservations', [
            'point_fraicheur_id' => $point->id,
            'nom' => 'Client Déjà',
            'email' => 'dupli@example.com',
            'nombre_personnes' => 1,
            'date_reservation' => $date,
            'heure_debut' => '09:30',
            'heure_fin' => '10:30',
        ])->assertSessionHasErrors('heure_debut');
    }

    public function test_email_is_limited_to_two_active_reservations_per_day(): void
    {
        $point = PointFraicheur::factory()->create();
        $date = now()->addDay()->toDateString();

        foreach ([['08:00', '09:00'], ['11:00', '12:00']] as [$debut, $fin]) {
            Reservation::create([
                'point_fraicheur_id' => $point->id,
                'nom' => 'Régulier',
                'email' => 'regulier@example.com',
                'nombre_personnes' => 1,
                'date_reservation' => $date,
                'heure_debut' => $debut,
                'heure_fin' => $fin,
                'statut' => Reservation::STATUT_CONFIRMEE,
            ]);
        }

        // Troisième réservation le même jour (créneau non chevauchant).
        $this->post('/reservations', [
            'point_fraicheur_id' => $point->id,
            'nom' => 'Régulier',
            'email' => 'regulier@example.com',
            'nombre_personnes' => 1,
            'date_reservation' => $date,
            'heure_debut' => '14:00',
            'heure_fin' => '15:00',
        ])->assertSessionHasErrors('nombre_personnes');
    }

    /*
    |--------------------------------------------------------------------------
    | Actions rapides (back office)
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_confirm_a_reservation_quickly(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $point = PointFraicheur::factory()->create();
        $reservation = Reservation::factory()->create([
            'point_fraicheur_id' => $point->id,
            'statut' => Reservation::STATUT_EN_ATTENTE,
        ]);

        $this->actingAs($admin)
            ->post("/admin/reservations/{$reservation->id}/confirmer")
            ->assertRedirect();

        $this->assertSame(Reservation::STATUT_CONFIRMEE, $reservation->fresh()->statut);
        $this->assertNotNull($reservation->fresh()->confirmee_le);
        Notification::assertSentOnDemand(ReservationConfirmee::class);
    }

    public function test_admin_can_check_in_by_code(): void
    {
        $admin = User::factory()->admin()->create();
        $point = PointFraicheur::factory()->create();
        $reservation = Reservation::factory()->create([
            'point_fraicheur_id' => $point->id,
            'statut' => Reservation::STATUT_CONFIRMEE,
        ]);

        $this->actingAs($admin)
            ->post('/admin/reservations/check-in', ['code' => $reservation->code])
            ->assertRedirect();

        $this->assertNotNull($reservation->fresh()->check_in_le);
        $this->assertSame(Reservation::STATUT_TERMINEE, $reservation->fresh()->statut);
    }

    public function test_unknown_code_returns_error_on_check_in(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/reservations/check-in', ['code' => 'ZZZZZZZZ'])
            ->assertSessionHas('error');
    }

    public function test_check_in_page_is_accessible_to_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/reservations/check-in')
            ->assertOk();
    }

    /*
    |--------------------------------------------------------------------------
    | Calendrier
    |--------------------------------------------------------------------------
    */

    public function test_calendar_events_endpoint_returns_shaped_events(): void
    {
        $admin = User::factory()->admin()->create();
        $point = PointFraicheur::factory()->create();
        $reservation = Reservation::factory()->create(['point_fraicheur_id' => $point->id]);

        $response = $this->actingAs($admin)
            ->get('/admin/reservations/events')
            ->assertOk()
            ->assertJsonCount(1);

        $event = $response->json('0');
        $this->assertSame($reservation->id, $event['id']);
        $this->assertNotEmpty($event['start']);
        $this->assertNotEmpty($event['color']);
    }

    public function test_calendar_page_is_accessible_to_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/reservations/calendrier')
            ->assertOk();
    }

    /*
    |--------------------------------------------------------------------------
    | Commandes planifiées
    |--------------------------------------------------------------------------
    */

    public function test_expirer_command_expires_stale_pending_reservations(): void
    {
        $point = PointFraicheur::factory()->create();
        $passe = Reservation::create([
            'point_fraicheur_id' => $point->id,
            'nom' => 'Passé',
            'email' => 'passe@example.com',
            'nombre_personnes' => 1,
            'date_reservation' => now()->subDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'statut' => Reservation::STATUT_EN_ATTENTE,
        ]);

        $this->artisan('reservations:expirer')->assertSuccessful();

        $this->assertSame(Reservation::STATUT_EXPIREE, $passe->fresh()->statut);
    }

    public function test_rappels_command_sends_a_single_reminder(): void
    {
        Notification::fake();
        $point = PointFraicheur::factory()->create();
        $reservation = Reservation::create([
            'point_fraicheur_id' => $point->id,
            'nom' => 'Demain',
            'email' => 'demain@example.com',
            'nombre_personnes' => 1,
            'date_reservation' => now()->addDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'statut' => Reservation::STATUT_CONFIRMEE,
        ]);

        $this->artisan('reservations:rappels')->assertSuccessful();
        $this->artisan('reservations:rappels')->assertSuccessful();

        Notification::assertSentOnDemandTimes(RappelReservation::class, 1);
        $this->assertNotNull($reservation->fresh()->rappel_envoye_le);
    }

    public function test_no_show_command_marks_unattended_reservations(): void
    {
        Notification::fake();
        $point = PointFraicheur::factory()->create();
        $reservation = Reservation::create([
            'point_fraicheur_id' => $point->id,
            'nom' => 'Absent',
            'email' => 'absent@example.com',
            'nombre_personnes' => 1,
            'date_reservation' => now()->subDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'statut' => Reservation::STATUT_CONFIRMEE,
            'check_in_le' => null,
        ]);

        $this->artisan('reservations:no-show')->assertSuccessful();

        $this->assertSame(Reservation::STATUT_NO_SHOW, $reservation->fresh()->statut);
    }

    /*
    |--------------------------------------------------------------------------
    | Service
    |--------------------------------------------------------------------------
    */

    public function test_affluence_levels_are_calculated_from_occupancy(): void
    {
        $service = app(ReservationService::class);

        $this->assertSame('libre', $service->niveauAffluence(0, 100));
        $this->assertSame('modere', $service->niveauAffluence(50, 100));
        $this->assertSame('presque_complet', $service->niveauAffluence(80, 100));
        $this->assertSame('complet', $service->niveauAffluence(100, 100));
    }
}
