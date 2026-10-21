<?php

namespace Database\Factories;

use App\Models\Dashboard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dashboard>
 */
class DashboardFactory extends Factory
{
    /**
     * @var class-string<Dashboard>
     */
    protected $model = Dashboard::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => now()->toDateString(),
            // Nul = indicateurs globaux.
            'point_fraicheur_id' => null,
            'total_reservations' => fake()->numberBetween(0, 40),
            'total_personnes' => fake()->numberBetween(0, 80),
            'en_liste_attente' => fake()->numberBetween(0, 5),
            'taux_presence' => fake()->randomFloat(2, 50, 100),
            'taux_no_show' => fake()->randomFloat(2, 0, 20),
            'taux_annulation' => fake()->randomFloat(2, 0, 15),
            'score_risque_moyen' => fake()->randomFloat(2, 0, 60),
            'source' => 'local',
        ];
    }
}
