<?php

namespace Tests\Feature;

use App\Models\Dashboard;
use App\Models\PointFraicheur;
use App\Models\Prevision;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\ReservationConfirmee;
use App\Notifications\ReservationReponse;
use App\Services\DashboardService;
use App\Services\PrevisionService;
use App\Services\ReservationAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Tests de la phase IA du module « Réservations » :
 * analyse des besoins (étiquettes + priorité + score), assistant en langage
 * naturel, détection de spam, personnalisation d'e-mail, réponse assistée,
 * prévisions et indicateurs.
 */
class ReservationAiTest extends TestCase
{
    use RefreshDatabase;

    private function ouvrirIa(): void
    {
        config([
            'services.gemini.key' => null,
            'services.openrouter.key' => 'cle-de-test',
        ]);
    }

    private function reponseIa(string $contenu): array
    {
        return ['choices' => [['message' => ['content' => $contenu]]]];
    }

    /*
    |--------------------------------------------------------------------------
    | Analyse des besoins (repli local, sans fournisseur IA)
    |--------------------------------------------------------------------------
    */

    public function test_analysis_job_runs_on_creation_with_local_fallback(): void
    {
        $point = PointFraicheur::factory()->create(['capacite' => 50]);

        $this->post('/reservations', [
            'point_fraicheur_id' => $point->id,
            'nom' => 'Amina Ben Salah',
            'email' => 'amina@example.com',
            'nombre_personnes' => 1,
            'date_reservation' => now()->addDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'besoins_specifiques' => 'Personne âgée, mobilité réduite.',
        ])->assertRedirect();

        $reservation = Reservation::firstOrFail();

        $this->assertContains('personne_agee', $reservation->tags_ia);
        $this->assertContains('mobilite_reduite', $reservation->tags_ia);
        $this->assertSame(Reservation::PRIORITE_HAUTE, $reservation->priorite);
        $this->assertNotNull($reservation->score_risque_no_show);
    }

    public function test_analysis_uses_ai_when_available(): void
    {
        $this->ouvrirIa();
        Http::fake(['*' => Http::response($this->reponseIa(json_encode([
            'tags' => ['risque_medical'],
            'priorite' => 'urgente',
            'explication' => 'Risque médical élevé.',
        ])))]);

        $reservation = Reservation::factory()->create([
            'statut' => Reservation::STATUT_EN_ATTENTE,
            'date_reservation' => now()->addDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'besoins_specifiques' => 'Je souffre du cœur.',
        ]);

        app(ReservationAiService::class)->analyser($reservation);
        $reservation->refresh();

        $this->assertSame(['risque_medical'], $reservation->tags_ia);
        $this->assertSame(Reservation::PRIORITE_URGENTE, $reservation->priorite);
        $this->assertSame('Risque médical élevé.', $reservation->explication_ia);
    }

    public function test_priority_is_never_lowered_by_analysis(): void
    {
        $this->ouvrirIa();
        Http::fake(['*' => Http::response($this->reponseIa(json_encode([
            'tags' => ['aucun'],
            'priorite' => 'normale',
            'explication' => 'Rien de particulier.',
        ])))]);

        $reservation = Reservation::factory()->create([
            'priorite' => Reservation::PRIORITE_URGENTE,
            'besoins_specifiques' => null,
        ]);

        app(ReservationAiService::class)->analyser($reservation);

        $this->assertSame(Reservation::PRIORITE_URGENTE, $reservation->fresh()->priorite);
    }

    /*
    |--------------------------------------------------------------------------
    | Score de risque de non-présentation
    |--------------------------------------------------------------------------
    */

    public function test_editing_a_reservation_recalculates_the_no_show_score(): void
    {
        $admin = User::factory()->admin()->create();
        $point = PointFraicheur::factory()->create(['capacite' => 50]);

        $reservation = Reservation::factory()->create([
            'point_fraicheur_id' => $point->id,
            'email' => 'fiable@example.com',
            'statut' => Reservation::STATUT_EN_ATTENTE,
            'date_reservation' => now()->addDays(10)->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'nombre_personnes' => 1,
            'besoins_specifiques' => null,
        ]);

        app(ReservationAiService::class)->analyser($reservation);
        $avant = (float) $reservation->fresh()->score_risque_no_show;

        // Modification côté back office : le score doit être recalculé,
        // sinon il reste celui calculé à la création (valeurs périmées).
        $this->actingAs($admin)->put("/admin/reservations/{$reservation->id}", [
            'point_fraicheur_id' => $point->id,
            'nom' => $reservation->nom,
            'email' => 'robot@yopmail.com',
            'telephone' => $reservation->telephone,
            'nombre_personnes' => 2,
            'date_reservation' => $reservation->date_reservation->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'statut' => Reservation::STATUT_EN_ATTENTE,
            'besoins_specifiques' => null,
            'notes_admin' => null,
        ])->assertRedirect('/admin/reservations');

        $apres = (float) $reservation->fresh()->score_risque_no_show;

        // +20 pour l'adresse jetable : le score augmente après édition.
        $this->assertGreaterThan($avant, $apres);
        $this->assertSame(
            $apres,
            (float) app(ReservationAiService::class)->scoreNoShow($reservation->fresh())
        );
    }

    public function test_disposable_email_increases_no_show_score(): void
    {
        $date = now()->addDays(10)->toDateString();

        $normal = Reservation::factory()->create([
            'email' => 'fiable@gmail.com',
            'date_reservation' => $date,
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'nombre_personnes' => 1,
        ]);

        $jetable = Reservation::factory()->create([
            'email' => 'robot@yopmail.com',
            'date_reservation' => $date,
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'nombre_personnes' => 1,
        ]);

        $service = app(ReservationAiService::class);

        $this->assertGreaterThan($service->scoreNoShow($normal), $service->scoreNoShow($jetable));
    }

    public function test_past_no_shows_increase_the_score(): void
    {
        $date = now()->addDays(10)->toDateString();

        Reservation::factory()->create([
            'email' => 'historique@example.com',
            'statut' => Reservation::STATUT_NO_SHOW,
            'date_reservation' => now()->subWeek()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
        ]);

        $avecHistorique = Reservation::factory()->create([
            'email' => 'historique@example.com',
            'date_reservation' => $date,
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
        ]);

        $sansHistorique = Reservation::factory()->create([
            'email' => 'nouveau@example.com',
            'date_reservation' => $date,
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
        ]);

        $service = app(ReservationAiService::class);

        $this->assertGreaterThan(
            $service->scoreNoShow($sansHistorique),
            $service->scoreNoShow($avecHistorique)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Assistant de réservation en langage naturel
    |--------------------------------------------------------------------------
    */

    public function test_assistant_extracts_booking_fields_locally(): void
    {
        $point = PointFraicheur::factory()->create(['nom' => 'Parc Central']);

        $reponse = $this->postJson('/reservations/assistant', [
            'message' => 'Je voudrais réserver 2 places demain matin au Parc Central',
        ]);

        $reponse->assertOk();
        $donnees = $reponse->json();

        $this->assertSame('reservation', $donnees['intention']);
        $this->assertSame(2, $donnees['nombre_personnes']);
        $this->assertSame(now()->addDay()->toDateString(), $donnees['date_reservation']);
        $this->assertSame('09:00', $donnees['heure_debut']);
        $this->assertSame('10:00', $donnees['heure_fin']);
        $this->assertSame($point->id, $donnees['point_fraicheur_id']);
    }

    public function test_assistant_uses_ai_when_available(): void
    {
        $this->ouvrirIa();
        $point = PointFraicheur::factory()->create();

        Http::fake(['*' => Http::response($this->reponseIa(json_encode([
            'intention' => 'reservation',
            'point_fraicheur_id' => $point->id,
            'nombre_personnes' => 4,
            'date' => now()->addDays(2)->toDateString(),
            'heure_debut' => '15:00',
            'heure_fin' => '16:00',
            'reponse' => 'C\'est noté.',
        ])))]);

        $donnees = $this->postJson('/reservations/assistant', [
            'message' => 'Bonjour, je cherche un coin frais pour ma famille',
        ])->assertOk()->json();

        $this->assertSame(4, $donnees['nombre_personnes']);
        $this->assertSame('15:00', $donnees['heure_debut']);
        $this->assertSame($point->id, $donnees['point_fraicheur_id']);
        $this->assertSame('C\'est noté.', $donnees['message']);
    }

    public function test_assistant_rejects_spam(): void
    {
        $this->postJson('/reservations/assistant', [
            'message' => 'viagra casino http://a.com http://b.com',
        ])->assertStatus(422)->assertJson(['intention' => 'spam']);
    }

    /*
    |--------------------------------------------------------------------------
    | Détection de spam sur les besoins spécifiques
    |--------------------------------------------------------------------------
    */

    public function test_spammy_specific_needs_are_rejected(): void
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
            'besoins_specifiques' => 'viagra casino bitcoin',
        ])->assertSessionHasErrors('besoins_specifiques');

        $this->assertDatabaseCount('reservations', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | E-mail personnalisé et réponse assistée
    |--------------------------------------------------------------------------
    */

    public function test_personalized_email_line_uses_local_fallback(): void
    {
        $point = PointFraicheur::factory()->create(['nom' => 'Jardin de l\'Ombre']);

        $reservation = Reservation::factory()->create([
            'point_fraicheur_id' => $point->id,
            'nom' => 'Amina',
            'date_reservation' => now()->addDay()->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
        ])->load('pointFraicheur');

        $texte = app(ReservationAiService::class)->personnaliserEmail($reservation);

        $this->assertStringContainsString('Jardin de l\'Ombre', $texte);
        $this->assertStringContainsString('Amina', $texte);
    }

    public function test_assisted_reply_uses_local_fallback(): void
    {
        $reservation = Reservation::factory()->create(['nom' => 'Karim'])->load('pointFraicheur');

        $texte = app(ReservationAiService::class)->redigerReponse($reservation);

        $this->assertStringContainsString('Karim', $texte);
    }

    public function test_assisted_reply_contains_no_placeholder(): void
    {
        $this->ouvrirIa();
        Http::fake(['*' => Http::response($this->reponseIa(
            "Bonjour [Nom de l'administrateur],\n\n"
            ."Votre demande est prise en compte.\n\n"
            ."Cordialement,\nL'équipe « Alerte Canicule » [Votre nom] [Votre fonction] [Vos coordonnées]"
        ))]);

        $reservation = Reservation::factory()->create(['nom' => 'Karim'])->load('pointFraicheur');

        $texte = app(ReservationAiService::class)->redigerReponse($reservation, 'Admin Test');

        // Aucun emplacement à remplir : le texte est prêt à l'envoi.
        $this->assertStringNotContainsString('[', $texte);
        $this->assertStringContainsString('Cordialement', $texte);
        $this->assertStringContainsString('prise en compte', $texte);
    }

    public function test_admin_can_generate_an_assisted_reply(): void
    {
        $admin = User::factory()->admin()->create();
        $reservation = Reservation::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.reservations.reponse-ia', $reservation))
            ->assertOk()
            ->assertJsonStructure(['reponse']);
    }

    public function test_admin_can_send_the_assisted_reply_to_the_visitor(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $reservation = Reservation::factory()->create(['email' => 'visiteur@example.com']);

        $this->actingAs($admin)
            ->postJson(route('admin.reservations.envoyer-reponse', $reservation), [
                'reponse' => "Bonjour,\n\nVotre créneau est bien maintenu.",
            ])
            ->assertOk()
            ->assertJson(['message' => 'Réponse envoyée à visiteur@example.com']);

        Notification::assertSentOnDemand(
            ReservationReponse::class,
            fn (ReservationReponse $notification): bool => $notification->reservation->is($reservation)
                && str_contains($notification->reponse, 'créneau est bien maintenu')
        );
    }

    public function test_sending_a_reply_requires_a_meaningful_text(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $reservation = Reservation::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.reservations.envoyer-reponse', $reservation), ['reponse' => ''])
            ->assertStatus(422);

        $this->actingAs($admin)
            ->postJson(route('admin.reservations.envoyer-reponse', $reservation), ['reponse' => 'court'])
            ->assertStatus(422);

        Notification::assertNothingSent();
    }

    /*
    |--------------------------------------------------------------------------
    | Prévisions et indicateurs (modèles Prevision / Dashboard)
    |--------------------------------------------------------------------------
    */

    public function test_prevision_service_generates_forecasts(): void
    {
        PointFraicheur::factory()->create(['capacite' => 40]);

        $nombre = app(PrevisionService::class)->generer(3);

        $this->assertSame(3, $nombre);
        $this->assertSame(3, Prevision::count());
        $this->assertNotNull(Prevision::first()->pointFraicheur);
    }

    public function test_dashboard_service_computes_indicators(): void
    {
        $point = PointFraicheur::factory()->create();
        $date = now()->toDateString();

        Reservation::factory()->count(3)->create([
            'point_fraicheur_id' => $point->id,
            'statut' => Reservation::STATUT_TERMINEE,
            'date_reservation' => $date,
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'nombre_personnes' => 2,
        ]);

        Reservation::factory()->create([
            'point_fraicheur_id' => $point->id,
            'statut' => Reservation::STATUT_NO_SHOW,
            'date_reservation' => $date,
            'heure_debut' => '09:00',
            'heure_fin' => '10:00',
            'nombre_personnes' => 1,
        ]);

        app(DashboardService::class)->snapshot($date);

        $global = Dashboard::whereNull('point_fraicheur_id')->firstOrFail();

        $this->assertSame(4, $global->total_reservations);
        $this->assertSame(7, $global->total_personnes);
        $this->assertSame(75.0, (float) $global->taux_presence);
        $this->assertSame(25.0, (float) $global->taux_no_show);
        $this->assertNotNull(Dashboard::where('point_fraicheur_id', $point->id)->first());
    }

    public function test_analytics_commands_run(): void
    {
        PointFraicheur::factory()->create(['capacite' => 30]);

        $this->artisan('reservations:previsions', ['--jours' => 2])->assertSuccessful();
        $this->assertSame(2, Prevision::count());

        $this->artisan('reservations:dashboard')->assertSuccessful();
        $this->assertTrue(Dashboard::query()->exists());
    }

    public function test_analytics_page_is_accessible_to_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.reservations.analytique'))
            ->assertOk()
            ->assertSee('Analyse IA');
    }

    public function test_admin_can_regenerate_forecasts_and_indicators(): void
    {
        PointFraicheur::factory()->create(['capacite' => 20]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.reservations.analytique.generer'))
            ->assertRedirect();

        $this->assertSame(7, Prevision::count());
    }

    public function test_regenerating_analytics_purges_demo_data_and_rebuilds_14_days(): void
    {
        $point = PointFraicheur::factory()->create(['capacite' => 20]);
        $admin = User::factory()->admin()->create();

        // Fausse prévision de démo datée d'aujourd'hui (comme le seeder).
        Prevision::create([
            'point_fraicheur_id' => $point->id,
            'date' => now()->toDateString(),
            'capacite' => 20,
            'reservations_prevues' => 18,
            'taux_remplissage' => 90.0,
            'source' => Prevision::SOURCE_LOCALE,
            'explication' => 'Prévision de démonstration.',
        ]);

        // Instantané périmé de démo (valeurs inventées) daté d'il y a 10 jours.
        Dashboard::create([
            'date' => now()->subDays(10)->toDateString(),
            'point_fraicheur_id' => null,
            'total_reservations' => 99,
            'total_personnes' => 999,
            'en_liste_attente' => 0,
            'taux_presence' => 50,
            'taux_no_show' => 25,
            'taux_annulation' => 10,
            'score_risque_moyen' => null,
            'source' => 'local',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.reservations.analytique.generer'))
            ->assertRedirect();

        // La fausse prévision du jour est purgée : il ne reste que les
        // 7 jours réellement recalculés (tous à venir).
        $this->assertSame(7, Prevision::count());
        $this->assertSame(
            7,
            Prevision::whereDate('date', '>', now()->toDateString())->count()
        );

        // Les 14 derniers jours ont une ligne globale, recalculée à partir
        // des vraies réservations : le faux total de 99 est écrasé.
        $this->assertSame(14, Dashboard::whereNull('point_fraicheur_id')->count());

        $recalcule = Dashboard::whereNull('point_fraicheur_id')
            ->whereDate('date', now()->subDays(10)->toDateString())
            ->firstOrFail();

        $this->assertSame(0, (int) $recalcule->total_reservations);
    }

    /*
    |--------------------------------------------------------------------------
    | Vérifications avancées
    |--------------------------------------------------------------------------
    */

    public function test_confirmation_email_contains_the_personalized_line(): void
    {
        $point = PointFraicheur::factory()->create(['nom' => 'Jardin de l\'Ombre']);

        $reservation = Reservation::factory()->create([
            'point_fraicheur_id' => $point->id,
            'nom' => 'Amina',
        ])->load('pointFraicheur');

        $mail = (new ReservationConfirmee($reservation))->toMail((object) []);

        $this->assertNotEmpty($mail->introLines);
        $this->assertStringContainsString('Jardin de l\'Ombre', $mail->introLines[0]);
    }

    public function test_assistant_endpoint_is_rate_limited(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->postJson('/reservations/assistant', ['message' => 'Bonjour'])
                ->assertStatus(200);
        }

        $this->postJson('/reservations/assistant', ['message' => 'Bonjour'])->assertStatus(429);
    }
}
