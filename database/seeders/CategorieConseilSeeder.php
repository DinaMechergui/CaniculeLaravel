<?php

namespace Database\Seeders;

use App\Models\CategorieConseil;
use Illuminate\Database\Seeder;

class CategorieConseilSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nom' => 'Énergie', 'description' => 'Économiser l\'électricité pendant les pics de chaleur et les coupures.'],
            ['nom' => 'Hydratation', 'description' => 'Rester bien hydraté et protéger les personnes sensibles.'],
            ['nom' => 'Équipements', 'description' => 'Protéger et utiliser correctement les appareils sensibles.'],
        ];

        foreach ($categories as $categorie) {
            CategorieConseil::updateOrCreate(['nom' => $categorie['nom']], $categorie);
        }
    }
}
