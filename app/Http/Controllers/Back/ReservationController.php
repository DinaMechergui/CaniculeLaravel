<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Http\Requests\Back\UpdateReservationRequest;
use App\Jobs\AnalyserReservationIa;
use App\Models\Dashboard;
use App\Models\PointFraicheur;
use App\Models\Prevision;
use App\Models\Reservation;
use App\Notifications\ReservationAnnulee;
use App\Notifications\ReservationConfirmee;
use App\Notifications\ReservationReponse;
use App\Services\DashboardService;
use App\Services\PrevisionService;
use App\Services\ReservationAiService;
use App\Services\ReservationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Gestion des réservations de créneaux (back office).
 */
class ReservationController extends Controller
{
    public function __construct(private readonly ReservationService $service)
    {
    }

    /**
     * Liste paginée des réservations + filtres, recherche et export CSV.
     */
    public function index(Request $request): View
    {
        $reservations = $this->requeteFiltree($request)
            ->paginate(10)
            ->withQueryString();

        return view('back.reservations.index', [
            'reservations' => $reservations,
            'points' => PointFraicheur::orderBy('nom')->get(),
            'filtres' => $this->filtres($request),
        ]);
    }

    /**
     * Détail d'une réservation.
     *
     * Si l'analyse IA n'a jamais été exécutée (ex. réservation créée sans worker ou issue d'un seeder),
     * elle est déclenchée automatiquement pour afficher immédiatement les indicateurs IA.
     */
    public function show(Reservation $reservation, ReservationAiService $ia): View
    {
        $reservation->load('pointFraicheur.type');

        if ($reservation->score_risque_no_show === null) {
            $ia->analyser($reservation);
            $reservation->refresh();
        }

        return view('back.reservations.show', [
            'reservation' => $reservation,
        ]);
    }

    /**
     * Déclenchement manuel ou réanalyse avec l'IA.
     */
    public function analyserIa(Reservation $reservation, ReservationAiService $ia): RedirectResponse
    {
        $ia->analyser($reservation);

        return back()->with('success', "L'analyse IA de la réservation {$reservation->code} a été effectuée avec succès.");
    }

    /**
     * Formulaire de modification d'une réservation.
     */
    public function edit(Reservation $reservation): View
    {
        return view('back.reservations.edit', [
            'reservation' => $reservation,
            'points' => PointFraicheur::orderBy('nom')->get(),
        ]);
    }

    /**
     * Mise à jour d'une réservation.
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation): RedirectResponse
    {
        $reservation->update($request->validated());

        // L'analyse IA (score de no-show, étiquettes, priorité) dépend des
        // champs modifiés : on la relance pour éviter un score périmé.
        AnalyserReservationIa::dispatch($reservation->id);

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', "La réservation {$reservation->code} a été modifiée avec succès.");
    }

    /**
     * Suppression (douce) d'une réservation.
     */
    public function destroy(Reservation $reservation): RedirectResponse
    {
        $code = $reservation->code;
        $reservation->delete();

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', "La réservation {$code} a été supprimée avec succès.");
    }

    /**
     * Export CSV des réservations filtrées (ouvrable dans Excel).
     */
    public function export(Request $request): StreamedResponse
    {
        $reservations = $this->requeteFiltree($request)
            ->orderByDesc('date_reservation')
            ->orderBy('heure_debut')
            ->get();

        $nomFichier = 'reservations-'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($reservations) {
            $sortie = fopen('php://output', 'w');

            // BOM UTF-8 pour un affichage correct des accents dans Excel.
            fwrite($sortie, "\xEF\xBB\xBF");

            fputcsv($sortie, [
                'Code', 'Nom', 'E-mail', 'Téléphone', 'Point', 'Date',
                'Début', 'Fin', 'Personnes', 'Statut', 'Priorité',
            ], ';');

            foreach ($reservations as $reservation) {
                fputcsv($sortie, [
                    $reservation->code,
                    $reservation->nom,
                    $reservation->email,
                    $reservation->telephone,
                    $reservation->pointFraicheur?->nom,
                    $reservation->date_reservation?->format('d/m/Y'),
                    $reservation->heure_debut_courte,
                    $reservation->heure_fin_courte,
                    $reservation->nombre_personnes,
                    $reservation->statut_label,
                    $reservation->priorite_label,
                ], ';');
            }

            fclose($sortie);
        }, $nomFichier, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Confirme une réservation (action rapide depuis la liste).
     */
    public function confirmer(Reservation $reservation): RedirectResponse
    {
        $reservation->update([
            'statut' => Reservation::STATUT_CONFIRMEE,
            'confirmee_le' => now(),
        ]);

        Notification::route('mail', $reservation->email)
            ->notify(new ReservationConfirmee($reservation));

        return back()->with('success', "La réservation {$reservation->code} a été confirmée.");
    }

    /**
     * Annule une réservation et libère la place (liste d'attente promue).
     */
    public function annuler(Reservation $reservation): RedirectResponse
    {
        $this->service->annuler($reservation);

        Notification::route('mail', $reservation->email)
            ->notify(new ReservationAnnulee($reservation));

        return back()->with('success', "La réservation {$reservation->code} a été annulée.");
    }

    /**
     * Marque une réservation comme honorée (check-in).
     */
    public function checkIn(Reservation $reservation): RedirectResponse
    {
        $reservation->update([
            'check_in_le' => now(),
            'statut' => Reservation::STATUT_TERMINEE,
        ]);

        return back()->with('success', "Check-in effectué pour {$reservation->code}.");
    }

    /**
     * Page de check-in par code de réservation.
     */
    public function checkInPage(Request $request): View
    {
        $code = $request->query('code');
        $reservation = $code
            ? Reservation::with('pointFraicheur')->where('code', $code)->first()
            : null;

        return view('back.reservations.check-in', [
            'code' => $code,
            'reservation' => $reservation,
        ]);
    }

    /**
     * Recherche d'une réservation par code puis check-in.
     */
    public function checkInStore(Request $request): RedirectResponse
    {
        $code = $request->validate([
            'code' => ['required', 'string', 'size:8'],
        ])['code'];

        $reservation = Reservation::with('pointFraicheur')->where('code', $code)->first();

        if ($reservation === null) {
            return redirect()
                ->route('admin.reservations.check-in', ['code' => $code])
                ->with('error', "Aucune réservation trouvée avec le code {$code}.");
        }

        if ($reservation->check_in_le !== null) {
            return redirect()
                ->route('admin.reservations.check-in', ['code' => $code])
                ->with('error', "Le check-in de {$reservation->code} a déjà été effectué.");
        }

        $reservation->update([
            'check_in_le' => now(),
            'statut' => Reservation::STATUT_TERMINEE,
        ]);

        return redirect()
            ->route('admin.reservations.check-in', ['code' => $code])
            ->with('success', "Check-in effectué pour {$reservation->code}.");
    }

    /**
     * Vue calendrier des réservations.
     */
    public function calendrier(): View
    {
        return view('back.reservations.calendrier', [
            'points' => PointFraicheur::orderBy('nom')->get(),
        ]);
    }

    /**
     * Événements du calendrier (JSON consommé par FullCalendar).
     */
    public function events(Request $request): JsonResponse
    {
        $pointId = $request->query('point');

        $reservations = Reservation::query()
            ->with('pointFraicheur')
            ->when($pointId, fn (Builder $q) => $q->where('point_fraicheur_id', $pointId))
            ->orderBy('date_reservation')
            ->get();

        $events = $reservations->map(function (Reservation $reservation) {
            $date = $reservation->date_reservation?->format('Y-m-d');

            return [
                'id' => $reservation->id,
                'title' => $reservation->code . ' · ' . $reservation->nom,
                'start' => $date . 'T' . $reservation->heure_debut_courte . ':00',
                'end' => $date . 'T' . $reservation->heure_fin_courte . ':00',
                'url' => route('admin.reservations.show', $reservation),
                'color' => $this->couleurStatut($reservation->statut),
                'allDay' => false,
            ];
        })->values();

        return response()->json($events);
    }

    /**
     * Page d'analyse IA : prévisions de fréquentation et indicateurs du module.
     */
    public function analytique(): View
    {
        $previsions = Prevision::query()
            ->with('pointFraicheur')
            ->aVenir()
            ->orderBy('date')
            ->orderByDesc('reservations_prevues')
            ->limit(60)
            ->get();

        $indicateurs = Dashboard::query()
            ->whereNull('point_fraicheur_id')
            ->orderByDesc('date')
            ->limit(14)
            ->get();

        $totalResas14j = $indicateurs->sum('total_reservations');
        $presencePonderee = $totalResas14j > 0
            ? round($indicateurs->sum(fn ($i) => $i->total_reservations * (float) $i->taux_presence) / $totalResas14j, 2)
            : 0.0;
        $noShowPondere = $totalResas14j > 0
            ? round($indicateurs->sum(fn ($i) => $i->total_reservations * (float) $i->taux_no_show) / $totalResas14j, 2)
            : 0.0;

        $scoresValides = $indicateurs->whereNotNull('score_risque_moyen');
        $scoreMoyenPondere = $scoresValides->isNotEmpty()
            ? round($scoresValides->avg('score_risque_moyen'), 2)
            : round((float) (Reservation::whereNotNull('score_risque_no_show')->avg('score_risque_no_show') ?? 25.0), 2);

        return view('back.reservations.analytique', [
            'previsions' => $previsions,
            'indicateurs' => $indicateurs,
            'totalResas14j' => $totalResas14j,
            'presencePonderee' => $presencePonderee,
            'noShowPondere' => $noShowPondere,
            'scoreMoyenPondere' => $scoreMoyenPondere,
        ]);
    }

    /**
     * Recalcule les prévisions et les indicateurs à la demande de l'admin.
     */
    public function genererAnalytique(PrevisionService $previsions, DashboardService $dashboard): RedirectResponse
    {
        // Purge des prévisions périmées (démo du seeder et dates passées) :
        // seules les prévisions recalculées des 7 prochains jours restent.
        Prevision::query()->whereDate('date', '<=', now()->toDateString())->delete();

        // Indicateurs des 14 derniers jours (et non plus du seul jour
        // courant) : les instantanés figés issus du seed sont écrasés.
        foreach (range(13, 0) as $ecart) {
            $dashboard->snapshot(now()->subDays($ecart)->toDateString());
        }

        $nombre = $previsions->generer(7);

        return back()->with('success', "{$nombre} prévision(s) recalculée(s) et indicateurs des 14 derniers jours mis à jour.");
    }

    /**
     * Réponse assistée par l'IA d'une réponse à envoyer au visiteur.
     */
    public function reponseIa(Reservation $reservation, ReservationAiService $ia): JsonResponse
    {
        $reservation->loadMissing('pointFraicheur');

        return response()->json([
            'reponse' => $ia->redigerReponse($reservation, auth()->user()?->name),
        ]);
    }

    /**
     * Envoie au visiteur la réponse rédigée (et modifiée) par l'admin.
     */
    public function envoyerReponse(Request $request, Reservation $reservation): JsonResponse
    {
        $valide = $request->validate([
            'reponse' => ['required', 'string', 'min:10', 'max:4000'],
        ], [
            'reponse.required' => 'La réponse ne peut pas être vide.',
            'reponse.min' => 'La réponse doit contenir au moins 10 caractères.',
            'reponse.max' => 'La réponse ne doit pas dépasser 4 000 caractères.',
        ]);

        Notification::route('mail', $reservation->email)
            ->notify(new ReservationReponse($reservation, $valide['reponse']));

        return response()->json([
            'message' => "Réponse envoyée à {$reservation->email}",
        ]);
    }

    /**
     * Couleur d'un statut pour le calendrier.
     */
    private function couleurStatut(string $statut): string
    {
        return match ($statut) {
            Reservation::STATUT_CONFIRMEE     => '#198754',
            Reservation::STATUT_EN_ATTENTE    => '#ffc107',
            Reservation::STATUT_LISTE_ATTENTE => '#0dcaf0',
            Reservation::STATUT_ANNULEE       => '#6c757d',
            Reservation::STATUT_TERMINEE      => '#0d6efd',
            Reservation::STATUT_NO_SHOW       => '#dc3545',
            Reservation::STATUT_EXPIREE       => '#212529',
            default                           => '#6c757d',
        };
    }

    /**
     * Requête filtrée partagée par la liste et l'export.
     */
    private function requeteFiltree(Request $request): Builder
    {
        $filtres = $this->filtres($request);

        return Reservation::query()
            // Chargement du point pour éviter le problème du N+1.
            ->with('pointFraicheur')
            ->when($filtres['search'], function (Builder $query, string $search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('nom', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->parStatut($filtres['statut'])
            ->when($filtres['priorite'], fn (Builder $query, string $priorite) => $query->where('priorite', $priorite))
            ->when($filtres['point'], fn (Builder $query, string $point) => $query->where('point_fraicheur_id', $point))
            ->when($filtres['date'], fn (Builder $query, string $date) => $query->whereDate('date_reservation', $date))
            ->orderByDesc('date_reservation')
            ->orderBy('heure_debut');
    }

    /**
     * Normalise les filtres de la requête HTTP.
     *
     * @return array<string, string|null>
     */
    private function filtres(Request $request): array
    {
        return [
            'search' => $request->query('search'),
            'statut' => $request->query('statut'),
            'priorite' => $request->query('priorite'),
            'point' => $request->query('point'),
            'date' => $request->query('date'),
        ];
    }
}
