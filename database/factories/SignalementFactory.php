<?php

namespace Database\Factories;

use App\Models\Signalement;
use App\Models\TypeSignalement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Signalement>
 */
class SignalementFactory extends Factory
{
    /**
     * Définition des valeurs par défaut d'un signalement.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type_signalement_id' => TypeSignalement::inRandomOrder()->first()?->id
                                    ?? TypeSignalement::factory(),
            'user_id'             => User::inRandomOrder()->first()?->id
                                    ?? User::factory(),
            'description'         => fake()->paragraph(),
            'statut'              => fake()->randomElement(array_keys(Signalement::STATUTS)),
        ];
    }
}
