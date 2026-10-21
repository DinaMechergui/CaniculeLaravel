<?php

namespace Database\Factories;

use App\Models\Alerte;
use App\Models\BulletinAlerte;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BulletinAlerte>
 */
class BulletinAlerteFactory extends Factory
{
    protected $model = BulletinAlerte::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $niveaux = array_keys(BulletinAlerte::NIVEAUX);
        $evolutions = ['aggravation', 'stable', 'amelioration'];

        return [
            'alerte_id' => Alerte::factory(),
            'niveau' => $this->faker->randomElement($niveaux),
            'evolution' => $this->faker->randomElement($evolutions),
            'message' => $this->faker->sentence(12),
            'conduite_a_tenir' => $this->faker->paragraph(2),
            'temperature_max' => $this->faker->optional(0.7)->numberBetween(24, 48),
            'date_publication' => $this->faker->dateTimeBetween('-3 days', 'now'),
        ];
    }

    public function niveau(string $niveau): static
    {
        return $this->state(fn (array $attributes) => [
            'niveau' => $niveau,
        ]);
    }

    public function evolution(string $evolution): static
    {
        return $this->state(fn (array $attributes) => [
            'evolution' => $evolution,
        ]);
    }

    public function levee(): static
    {
        return $this->state(fn (array $attributes) => [
            'niveau' => 'vert',
            'evolution' => 'levee',
            'message' => 'Fin de vigilance météo, retour à la normale.',
            'conduite_a_tenir' => 'Les mesures d\'urgence sont levées.',
        ]);
    }
}
