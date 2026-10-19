<?php

namespace Database\Factories;

use App\Models\CampagneConseil;
use App\Models\Conseil;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<CampagneConseil>
 */
class CampagneConseilFactory extends Factory
{
    protected $model = CampagneConseil::class;

    public function definition(): array
    {
        $debut = Carbon::instance(fake()->dateTimeBetween('-1 month', '+1 month'));

        return [
            'conseil_id'      => Conseil::factory(),
            'titre'           => fake()->sentence(4),
            'public_cible'    => fake()->randomElement(array_keys(CampagneConseil::PUBLICS)),
            'canal'           => fake()->randomElement(array_keys(CampagneConseil::CANAUX)),
            'date_debut'      => $debut->toDateString(),
            'date_fin'        => $debut->copy()->addDays(fake()->numberBetween(7, 45))->toDateString(),
            'statut'          => fake()->randomElement(array_keys(CampagneConseil::STATUTS)),
            'activation_auto' => false,
            'message'         => null,
        ];
    }

    /** État campagne active. */
    public function active(): static
    {
        return $this->state(fn () => ['statut' => 'active']);
    }

    /** État campagne brouillon. */
    public function brouillon(): static
    {
        return $this->state(fn () => ['statut' => 'brouillon']);
    }

    /** État campagne terminée. */
    public function terminee(): static
    {
        return $this->state(fn () => ['statut' => 'terminee']);
    }
}