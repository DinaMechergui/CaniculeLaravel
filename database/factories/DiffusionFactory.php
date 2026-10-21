<?php

namespace Database\Factories;

use App\Models\BulletinAlerte;
use App\Models\Diffusion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Diffusion>
 */
class DiffusionFactory extends Factory
{
    protected $model = Diffusion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $canaux = array_keys(Diffusion::CANAUX);
        $statuts = array_keys(Diffusion::STATUTS);

        return [
            'bulletin_alerte_id' => BulletinAlerte::factory(),
            'canal' => $this->faker->randomElement($canaux),
            'contenu' => mb_substr($this->faker->sentence(14), 0, 155),
            'destinataires_estimes' => $this->faker->numberBetween(500, 85000),
            'date_envoi' => $this->faker->dateTimeBetween('-2 days', '+1 day'),
            'statut' => $this->faker->randomElement($statuts),
        ];
    }

    public function envoyee(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'envoyee',
            'date_envoi' => now()->subMinutes(rand(5, 120)),
        ]);
    }

    public function planifiee(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'planifiee',
            'date_envoi' => now()->addMinutes(rand(10, 240)),
        ]);
    }

    public function echec(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'echec',
        ]);
    }

    public function canal(string $canal): static
    {
        return $this->state(fn (array $attributes) => [
            'canal' => $canal,
        ]);
    }
}
