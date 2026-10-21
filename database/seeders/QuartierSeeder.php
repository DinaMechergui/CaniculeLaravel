<?php

namespace Database\Seeders;

use App\Models\Quartier;
use Illuminate\Database\Seeder;

class QuartierSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['nom' => 'El Menzah', 'ville' => 'Tunis', 'code_postal' => '1004'],
            ['nom' => 'La Marsa', 'ville' => 'Tunis', 'code_postal' => '2070'],
            ['nom' => 'Ariana Ville', 'ville' => 'Ariana', 'code_postal' => '2080'],
            ['nom' => 'Le Bardo', 'ville' => 'Tunis', 'code_postal' => '2000'],
            ['nom' => 'El Mourouj', 'ville' => 'Ben Arous', 'code_postal' => '2074'],
        ] as $quartier) {
            Quartier::firstOrCreate(['nom' => $quartier['nom']], $quartier);
        }
    }
}
