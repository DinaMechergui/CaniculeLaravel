<?php

namespace App\Services;

use App\Models\Coupure;
use Illuminate\Support\Collection;

class SmartGridPriorityService
{
    /**
     * Deterministic score: impact severity (0-40), affected people (0-25),
     * sensitive facilities (school or hospital, 0-15), elapsed outage duration (0-15),
     * no active intervention (0-5).
     */
    public function score(Coupure $coupure): array
    {
        if ($coupure->statut === 'terminee') {
            return [
                'score' => 0,
                'level' => 'faible',
                'factors' => [
                    'gravite' => 0,
                    'personnes' => 0,
                    'etablissements_sensibles' => 0,
                    'duree' => 0,
                    'absence_intervention_active' => 0,
                ],
            ];
        }

        $impacts = $coupure->relationLoaded('impacts') ? $coupure->impacts : $coupure->impacts()->get();
        $gravity = (int) $impacts->max(fn ($impact) => [
            'moyen' => 10,
            'eleve' => 25,
            'critique' => 40,
        ][$impact->niveau_gravite] ?? 0);
        $people = min(25, (int) floor($impacts->sum('personnes_affectees') / 20));
        $sensitiveFacilities = $impacts->contains(
            fn ($impact) => in_array($impact->categorie, ['ecole', 'hopital'], true)
        ) ? 15 : 0;
        $elapsedHours = max(0, $coupure->date_debut->diffInHours(now(), false));
        $duration = min(15, (int) floor($elapsedHours / 2));
        $interventions = $coupure->relationLoaded('interventions')
            ? $coupure->interventions
            : $coupure->interventions()->get();
        $activeIntervention = $interventions->contains(fn ($intervention) => $intervention->statut === 'en_cours');
        $availability = $activeIntervention ? 0 : 5;
        $score = min(100, $gravity + $people + $sensitiveFacilities + $duration + $availability);

        return [
            'score' => $score,
            'level' => match (true) {
                $score >= 70 => 'critique',
                $score >= 40 => 'elevee',
                $score >= 20 => 'moyenne',
                default => 'faible',
            },
            'factors' => [
                'gravite' => $gravity,
                'personnes' => $people,
                'etablissements_sensibles' => $sensitiveFacilities,
                'duree' => $duration,
                'absence_intervention_active' => $availability,
            ],
        ];
    }

    /** @return Collection<int, array{coupure: Coupure, priority: array}> */
    public function ranked(): Collection
    {
        return Coupure::query()
            ->with(['quartier', 'impacts', 'interventions'])
            ->whereIn('statut', ['en_cours', 'prevue'])
            ->get()
            ->map(fn (Coupure $coupure) => ['coupure' => $coupure, 'priority' => $this->score($coupure)])
            ->sortByDesc(fn (array $row) => $row['priority']['score'])
            ->values();
    }
}
