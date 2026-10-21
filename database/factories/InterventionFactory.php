<?php

namespace Database\Factories;

use App\Models\Coupure;
use App\Models\Intervention;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Intervention> */
class InterventionFactory extends Factory
{
    public function definition(): array
    {
        $statut = fake()->randomElement(['prevue', 'en_cours', 'terminee']);

        return [
            'coupure_id' => Coupure::factory(),
            'equipe' => 'Équipe '.fake()->numberBetween(1, 12),
            'technicien' => fake()->name(),
            'date_intervention' => fake()->dateTimeBetween('-3 days', '+3 days'),
            'duree_estimee' => fake()->numberBetween(30, 240),
            'statut' => $statut,
            'rapport' => $statut === 'terminee' ? fake()->paragraph() : null,
        ];
    }
}
