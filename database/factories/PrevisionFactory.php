<?php

namespace Database\Factories;

use App\Models\PointFraicheur;
use App\Models\Prevision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prevision>
 */
class PrevisionFactory extends Factory
{
    /**
     * @var class-string<Prevision>
     */
    protected $model = Prevision::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $capacite = 50;
        $prevues = fake()->numberBetween(5, $capacite);

        return [
            'point_fraicheur_id' => PointFraicheur::factory(),
            'date' => now()->addDay()->toDateString(),
            'capacite' => $capacite,
            'reservations_prevues' => $prevues,
            'taux_remplissage' => round(($prevues / $capacite) * 100, 2),
            'source' => Prevision::SOURCE_LOCALE,
            'explication' => null,
        ];
    }
}
