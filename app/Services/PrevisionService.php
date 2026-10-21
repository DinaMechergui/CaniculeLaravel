<?php

namespace App\Services;

use App\Models\PointFraicheur;
use App\Models\Prevision;
use App\Models\Reservation;
use Illuminate\Support\Carbon;

/**
 * Génération des prévisions de fréquentation.
 *
 * Estimation locale déterministe : pour chaque point de fraîcheur et chaque
 * jour à venir, on moyenne le nombre de personnes accueillies lors des
 * dernières occurrences du même jour de la semaine (4 dernières semaines).
 */
class PrevisionService
{
    /**
     * (Re)génère les prévisions des prochains jours.
     *
     * @return int Nombre de prévisions enregistrées.
     */
    public function generer(int $jours = 7): int
    {
        $jours = max(1, min($jours, 30));
        $compte = 0;

        PointFraicheur::query()->chunkById(50, function ($points) use ($jours, &$compte): void {
            /** @var PointFraicheur $point */
            foreach ($points as $point) {
                for ($i = 1; $i <= $jours; $i++) {
                    $date = Carbon::today()->addDays($i);
                    $capacite = max(1, (int) $point->capacite);
                    $prevues = $this->prevoirPour($point, $date);

                    Prevision::updateOrCreate(
                        [
                            'point_fraicheur_id' => $point->id,
                            // Carbon plutôt que chaîne : évite les doublons
                            // quand la colonne est un datetime (SQLite).
                            'date' => $date,
                        ],
                        [
                            'capacite' => $capacite,
                            'reservations_prevues' => $prevues,
                            'taux_remplissage' => round(min($prevues / $capacite * 100, 100), 2),
                            'source' => Prevision::SOURCE_LOCALE,
                            'explication' => 'Estimation à partir de la moyenne des 4 dernières occurrences du même jour de la semaine.',
                        ]
                    );

                    $compte++;
                }
            }
        });

        return $compte;
    }

    /**
     * Nombre de personnes attendues pour un point et un jour donnés.
     */
    protected function prevoirPour(PointFraicheur $point, Carbon $date): int
    {
        $debut = $date->copy()->subWeeks(4)->toDateString();

        $historique = Reservation::query()
            ->where('point_fraicheur_id', $point->id)
            ->whereDate('date_reservation', '<', $date->toDateString())
            ->whereDate('date_reservation', '>=', $debut)
            ->get(['date_reservation', 'nombre_personnes']);

        $memeJour = $historique->filter(
            fn (Reservation $reservation): bool => $reservation->date_reservation->dayOfWeek === $date->dayOfWeek
        );

        $source = $memeJour->isNotEmpty() ? $memeJour : $historique;

        $totauxParJour = $source
            ->groupBy(fn (Reservation $reservation): string => $reservation->date_reservation->toDateString())
            ->map(fn ($lot): int => (int) $lot->sum('nombre_personnes'));

        return (int) round($totauxParJour->avg() ?? 0);
    }
}
