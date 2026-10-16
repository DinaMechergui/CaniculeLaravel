<?php

namespace Database\Seeders;

use App\Models\PointFraicheur;
use App\Models\Reservation;
use Illuminate\Database\Seeder;

/**
 * Crée des réservations de démonstration pour chaque point de fraîcheur.
 *
 * 4 réservations par point, réparties sur les 30 derniers jours et les 14
 * prochains jours, avec des besoins spécifiques variés afin de pouvoir
 * tester l'analyse par l'IA.
 */
class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PointFraicheur::all() as $point) {
            // Évite de recréer des réservations déjà présentes pour ce point
            // (le seeder doit pouvoir être relancé sans créer de doublons).
            if (Reservation::where('point_fraicheur_id', $point->id)->exists()) {
                continue;
            }

            $nombre = 4;

            for ($i = 0; $i < $nombre; $i++) {
                Reservation::factory()->create([
                    'point_fraicheur_id' => $point->id,
                ]);
            }
        }
    }
}
