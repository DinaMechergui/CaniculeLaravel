<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategorieConseilFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
        ];
    }
}
