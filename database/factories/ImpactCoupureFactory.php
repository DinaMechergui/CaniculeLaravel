<?php

namespace Database\Factories;

use App\Models\Coupure;
use App\Models\ImpactCoupure;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ImpactCoupure> */
class ImpactCoupureFactory extends Factory
{
    public function definition(): array
    {
        return [
            'coupure_id' => Coupure::factory(),
            'nom_structure' => fake()->company(),
            'categorie' => fake()->randomElement(['hopital', 'ecole', 'commerce', 'residence', 'autre']),
            'adresse' => fake()->address(),
            'personnes_affectees' => fake()->numberBetween(0, 250),
            'niveau_gravite' => fake()->randomElement(['moyen', 'eleve', 'critique']),
            'date_signalement' => fake()->dateTimeBetween('-7 days', 'now'),
            'mesures_urgence' => fake()->optional()->sentence(),
            'statut' => fake()->randomElement(['en_traitement', 'resolu']),
            'description' => fake()->optional()->paragraph(),
        ];
    }
}
