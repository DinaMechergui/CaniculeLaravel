<?php

namespace Database\Seeders;

use App\Models\PointFraicheur;
use App\Models\Prevision;
use Illuminate\Database\Seeder;

/**
 * Crée des prévisions de fréquentation de démonstration (7 prochains jours)
 * pour chaque point de fraîcheur.
 */
class PrevisionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PointFraicheur::all() as $point) {
            // Ré-exécutable sans doublon.
            if (Prevision::where('point_fraicheur_id', $point->id)->exists()) {
                continue;
            }

            $capacite = max(1, (int) $point->capacite);

            for ($i = 1; $i <= 7; $i++) {
                $prevues = fake()->numberBetween(0, (int) round($capacite * 0.9));

                Prevision::create([
                    'point_fraicheur_id' => $point->id,
                    'date' => now()->addDays($i)->toDateString(),
                    'capacite' => $capacite,
                    'reservations_prevues' => $prevues,
                    'taux_remplissage' => round(min($prevues / $capacite * 100, 100), 2),
                    'source' => Prevision::SOURCE_LOCALE,
                    'explication' => 'Prévision de démonstration.',
                ]);
            }
        }
    }
}
