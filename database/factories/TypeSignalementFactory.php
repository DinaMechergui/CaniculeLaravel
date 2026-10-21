<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TypeSignalement>
 */
class TypeSignalementFactory extends Factory
{
    /**
     * Définition des valeurs par défaut du type de signalement.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'libelle' => fake()->unique()->words(2, true),
        ];
    }
}
