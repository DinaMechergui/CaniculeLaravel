<?php

namespace App\Services;

use App\Models\Dashboard;
use App\Models\PointFraicheur;
use App\Models\Reservation;

/**
 * Calcul des instantanés d'indicateurs (« dashboard ») du module.
 *
 * Pour une date donnée, on enregistre une ligne globale (tous points) et une
 * ligne par point de fraîcheur : volume de réservations, taux de présence,
 * de non-présentation et d'annulation, liste d'attente et score de risque
 * moyen.
 */
class DashboardService
{
    /**
     * (Re)calcule et enregistre les indicateurs d'une date (aujourd'hui par
     * défaut) : une ligne globale + une ligne par point.
     */
    public function snapshot(?string $date = null): void
    {
        $date = $date ?? now()->toDateString();

        // Indicateurs globaux (point_fraicheur_id nul).
        $this->enregistrer($date, null);

        // Indicateurs par point.
        PointFraicheur::query()->pluck('id')->each(
            fn ($id) => $this->enregistrer($date, (int) $id)
        );
    }

    /**
     * Calcule et enregistre les indicateurs d'une date pour un point (ou
     * globalement si `$pointId` est nul).
     */
    protected function enregistrer(string $date, ?int $pointId): void
    {
        $query = Reservation::query()->whereDate('date_reservation', $date);

        if ($pointId !== null) {
            $query->where('point_fraicheur_id', $pointId);
        }

        $total = (clone $query)->count();
        $personnes = (int) (clone $query)->sum('nombre_personnes');
        $attente = (clone $query)->where('statut', Reservation::STATUT_LISTE_ATTENTE)->count();
        $terminees = (clone $query)->where(function ($q) {
            $q->where('statut', Reservation::STATUT_TERMINEE)
              ->orWhereNotNull('check_in_le');
        })->count();
        $noShow = (clone $query)->where(function ($q) {
            $q->where('statut', Reservation::STATUT_NO_SHOW)
              ->orWhere('statut', Reservation::STATUT_EXPIREE)
              ->orWhere(function ($sub) {
                  $sub->where('statut', Reservation::STATUT_CONFIRMEE)
                      ->whereNull('check_in_le')
                      ->whereDate('date_reservation', '<', now()->toDateString());
              });
        })->count();
        $annulees = (clone $query)->where('statut', Reservation::STATUT_ANNULEE)->count();
        $risque = (clone $query)->whereNotNull('score_risque_no_show')->avg('score_risque_no_show');

        if ($risque === null && $total > 0) {
            $risque = Reservation::whereNotNull('score_risque_no_show')->avg('score_risque_no_show');
        }

        Dashboard::updateOrCreate(
            [
                // Carbon plutôt que chaîne : la comparaison fonctionne sur
                // MySQL (DATE) comme sur SQLite ('Y-m-d H:i:s' stocké).
                'date' => \Illuminate\Support\Carbon::parse($date),
                'point_fraicheur_id' => $pointId,
            ],
            [
                'total_reservations' => $total,
                'total_personnes' => $personnes,
                'en_liste_attente' => $attente,
                'taux_presence' => $this->pourcentage($terminees, $total),
                'taux_no_show' => $this->pourcentage($noShow, $total),
                'taux_annulation' => $this->pourcentage($annulees, $total),
                'score_risque_moyen' => $risque === null ? null : round((float) $risque, 2),
                'source' => 'local',
            ]
        );
    }

    /**
     * Pourcentage arrondi (0 si le total est nul).
     */
    protected function pourcentage(int $partie, int $total): float
    {
        return $total > 0 ? round($partie / $total * 100, 2) : 0.0;
    }
}
