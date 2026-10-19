<?php

namespace Database\Factories;

use App\Models\TypePoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PointFraicheur>
 */
class PointFraicheurFactory extends Factory
{
    /**
     * Définition des valeurs par défaut du point de fraîcheur.
     *
     * Coordonnées situées autour de Tunis (latitude / longitude).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type_point_id' => TypePoint::factory(),
            'nom' => fake()->company(),
            'adresse' => fake()->address(),
            'latitude' => fake()->latitude(36.7, 36.9),
            'longitude' => fake()->longitude(10.0, 10.3),
            'horaires' => '08:00 - 20:00',
            'accessible' => fake()->boolean(80),
            'capacite' => fake()->numberBetween(20, 120),
        ];
    }
}
