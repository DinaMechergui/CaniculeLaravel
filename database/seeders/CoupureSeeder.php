<?php

namespace Database\Seeders;

use App\Models\Coupure;
use App\Models\Quartier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CoupureSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['delestage', 'panne', 'surcharge'];

        foreach (Quartier::all() as $quartier) {
            // Évite de recréer les coupures déjà générées pour ce quartier.
            if (Coupure::where('quartier_id', $quartier->id)->exists()) {
                continue;
            }

            foreach (range(0, 5) as $index) {
                $statut = ['en_cours', 'prevue', 'terminee'][$index % 3];
                $dateDebut = match ($statut) {
                    'en_cours' => Carbon::now()->subHour(),
                    'prevue' => Carbon::now()->addDays($index + 1),
                    default => Carbon::now()->subDays($index + 1),
                };

                Coupure::factory()->create([
                    'quartier_id' => $quartier->id,
                    'type' => $types[$index % count($types)],
                    'statut' => $statut,
                    'date_debut' => $dateDebut,
                    'date_fin' => $statut === 'terminee' ? $dateDebut->copy()->addHours(2) : null,
                ]);
            }
        }
    }
}
