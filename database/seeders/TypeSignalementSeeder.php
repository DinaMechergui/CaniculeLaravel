<?php

namespace Database\Seeders;

use App\Models\TypeSignalement;
use Illuminate\Database\Seeder;

class TypeSignalementSeeder extends Seeder
{
    /**
     * Insère 4 types de signalements réalistes.
     */
    public function run(): void
    {
        $types = [
            'Coupure observée',
            'Point de fraîcheur fermé',
            'Panne',
            'Autre incident',
        ];

        foreach ($types as $libelle) {
            TypeSignalement::firstOrCreate(['libelle' => $libelle]);
        }
    }
}
