<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\StoreReservationRequest;
use App\Jobs\AnalyserReservationIa;
use App\Models\PointFraicheur;
use App\Models\Reservation;
use App\Notifications\ReservationAnnulee;
use App\Notifications\ReservationConfirmee;
use App\Notifications\ReservationListeAttente;
use App\Services\ReservationAiService;
use App\Services\ReservationService;
use App\Services\SpamDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Réservation de créneaux côté front office.
 *
 * Le client réserve directement depuis la fiche du point de fraîcheur : il
 * n'a pas besoin de compte, il est identifié par son nom, son e-mail et le
 * code de réservation généré automatiquement.
 */
class ReservationController extends Controller
{
    public function __construct(private readonly ReservationService $service)
    {
    }

    /**
     * Formulaire de réservation pour un point de fraîcheur donné.
     */
    public function create(PointFraicheur $pointFraicheur): View
    {
        return view('front.reservations.create', [
            'point' => $pointFraicheur->load('type'),
        ]);
    }

    /**
     * Enregistre la réservation (code généré par le modèle) puis redirige
     * vers la page de confirmation affichant le code de suivi.
     */
    public function store(StoreReservationRequest $request): RedirectResponse
    {
        $reservation = $this->service->creer($request->validated());

        // Analyse IA en arrière-plan (non bloquante) : besoins, priorité,
        // score de non-présentation.
        AnalyserReservationIa::dispatch($reservation->id);

        if ($reservation->statut === Reservation::STATUT_LISTE_ATTENTE) {
            Notification::route('mail', $reservation->email)
                ->notify(new ReservationListeAttente($reservation));

            return redirect()
                ->route('reservations.show', $reservation->code)
                ->with('success', 'Créneau complet : votre demande a été placée sur liste d\'attente.');
        }

        Notification::route('mail', $reservation->email)
            ->notify(new ReservationConfirmee($reservation));

        return redirect()
            ->route('reservations.show', $reservation->code)
            ->with('success', 'Votre réservation a bien été enregistrée.');
    }

    /**
     * Confirmation / suivi d'une réservation à partir de son code.
     */
    public function show(string $code): View
    {
        $reservation = Reservation::query()
            ->where('code', $code)
            ->with('pointFraicheur.type')
            ->firstOrFail();

        return view('front.reservations.show', [
            'reservation' => $reservation,
        ]);
    }

    /**
     * Page du formulaire de recherche (code + e-mail).
     */
    public function rechercheForm(): View
    {
        return view('front.reservations.recherche');
    }

    /**
     * Recherche d'une réservation par code ET e-mail (formulaire du front).
     *
     * Redirige vers la page de suivi si la correspondance est exacte, sinon
     * affiche un message d'erreur sans révéler lequel des champs est faux.
     */
    public function recherche(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'code' => ['required', 'string', 'size:8'],
            'email' => ['required', 'email', 'max:150'],
        ], [], [
            'code' => 'code',
            'email' => 'e-mail',
        ]);

        $reservation = Reservation::query()
            ->where('code', strtoupper($donnees['code']))
            ->where('email', $donnees['email'])
            ->first();

        if ($reservation === null) {
            return back()->with('error', 'Aucune réservation ne correspond à ce code et à cette adresse e-mail.');
        }

        return redirect()->route('reservations.show', $reservation->code);
    }

    /**
     * Annulation d'une réservation depuis le front (par son code de suivi).
     *
     * Seules les réservations actives et dont le créneau n'est pas passé
     * peuvent être annulées.
     */
    public function annuler(string $code): RedirectResponse
    {
        $reservation = Reservation::query()->where('code', $code)->firstOrFail();

        if (! in_array($reservation->statut, [
            Reservation::STATUT_EN_ATTENTE,
            Reservation::STATUT_CONFIRMEE,
            Reservation::STATUT_LISTE_ATTENTE,
        ], true)) {
            return redirect()
                ->route('reservations.show', $code)
                ->with('error', 'Cette réservation ne peut plus être annulée.');
        }

        $fin = $reservation->date_reservation?->copy()->setTimeFromTimeString($reservation->heure_fin_courte);

        if ($fin !== null && $fin->isPast()) {
            return redirect()
                ->route('reservations.show', $code)
                ->with('error', 'Ce créneau est déjà passé, l\'annulation n\'est plus possible.');
        }

        $this->service->annuler($reservation);

        Notification::route('mail', $reservation->email)
            ->notify(new ReservationAnnulee($reservation));

        return redirect()
            ->route('reservations.show', $code)
            ->with('success', 'Votre réservation a bien été annulée.');
    }

    /**
     * Créneaux disponibles d'une journée (JSON consommé en AJAX par le
     * formulaire de réservation).
     */
    public function creneaux(Request $request, PointFraicheur $pointFraicheur): JsonResponse
    {
        $date = $request->query('date', now()->toDateString());

        // Sécurité : la date doit être valide, sinon on retombe sur aujourd'hui.
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date)) {
            $date = now()->toDateString();
        }

        return response()->json([
            'date' => $date,
            'capacite' => (int) $pointFraicheur->capacite,
            'creneaux' => $this->service->creneauxDisponibles($pointFraicheur, $date),
        ]);
    }

    /**
     * Assistant de réservation en langage naturel (AJAX).
     *
     * Reçoit une phrase libre (« je voudrais 2 places demain matin ») et
     * renvoie les champs du formulaire pré-remplis (JSON).
     */
    public function assistant(Request $request, ReservationAiService $ia): JsonResponse
    {
        $donnees = $request->validate([
            'message' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        if (app(SpamDetectionService::class)->estSpam($donnees['message'])) {
            return response()->json([
                'intention' => 'spam',
                'message' => 'Votre message a été identifié comme indésirable.',
            ], 422);
        }

        return response()->json($ia->assistantReservation($donnees['message']));
    }

    /**
     * QR code (SVG) menant à la page de validation d'arrivée de la
     * réservation (le scan déclenche le check-in).
     *
     * La sortie SVG évite la dépendance à l'extension GD (absente de
     * certains hébergements) tout en restant affichable par les navigateurs.
     */
    public function qr(string $code): Response
    {
        $reservation = Reservation::query()->where('code', $code)->firstOrFail();

        // On encode l'URL absolue définie par APP_URL (et non l'hôte de la
        // requête) : ainsi un téléphone qui scanne le QR atteint le bon hôte
        // (ex. l'IP du réseau local) même si la page a été ouverte via
        // « 127.0.0.1 » sur le poste de développement.
        $url = rtrim((string) config('app.url'), '/')
            . route('reservations.arrivee', $reservation->code, false);

        $svg = class_exists(QrCode::class)
            ? QrCode::format('svg')->size(240)->margin(1)->generate($url)
            : '<svg xmlns="http://www.w3.org/2000/svg" width="240" height="240"><rect width="240" height="240" fill="#fff"/></svg>';

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }

    /**
     * Page de validation d'arrivée ouverte en scannant le QR code.
     *
     * Le scan marque la réservation comme honorée (statut « terminée » +
     * horodatage de check-in) puis affiche un reçu. L'opération est
     * idempotente : rescanner une réservation déjà validée réaffiche
     * simplement le reçu sans rien modifier.
     */
    public function arrivee(string $code): View
    {
        $reservation = Reservation::query()
            ->where('code', $code)
            ->with('pointFraicheur.type')
            ->firstOrFail();

        $dejaValidee = $reservation->check_in_le !== null;

        $statutsValidables = [
            Reservation::STATUT_EN_ATTENTE,
            Reservation::STATUT_CONFIRMEE,
            Reservation::STATUT_LISTE_ATTENTE,
        ];

        if (! $dejaValidee && in_array($reservation->statut, $statutsValidables, true)) {
            $reservation->update([
                'check_in_le' => now(),
                'statut' => Reservation::STATUT_TERMINEE,
            ]);

            $reservation->refresh();
        }

        return view('front.reservations.recu', [
            'reservation' => $reservation,
            'validee' => $reservation->statut === Reservation::STATUT_TERMINEE,
            'dejaValidee' => $dejaValidee,
        ]);
    }
}
