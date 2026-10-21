<?php

namespace Database\Factories;

use App\Models\CategorieConseil;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConseilFactory extends Factory
{
    public function definition(): array
    {
        return [
            // La clé étrangère passe par la factory du parent : si aucune
            // catégorie n'est donnée, une catégorie est créée automatiquement.
            'categorie_conseil_id' => CategorieConseil::factory(),
            'titre' => fake()->sentence(5),
            'contenu' => fake()->paragraphs(3, true),
        ];
    }
}
