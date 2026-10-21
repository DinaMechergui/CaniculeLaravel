<?php

namespace Database\Factories;

use App\Models\Diagnostic;
use App\Models\Signalement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Génère des diagnostics réalistes : les symptômes sont cohérents avec une
 * cause tirée au hasard (avec un peu de bruit), ce qui permet plus tard
 * d'entraîner un modèle IA sur ces données.
 *
 * @extends Factory<Diagnostic>
 */
class DiagnosticFactory extends Factory
{
    /**
     * Profil de symptômes par cause : zones probables, probabilité de chaque
     * symptôme, plage horaire et plage de température typiques.
     */
    private const PROFILS = [
        'disjoncteur_domestique' => [
            'zones' => ['logement'],
            'probas' => ['disjoncteur_saute' => 0.9, 'baisse_tension_avant' => 0.1, 'odeur_brule' => 0.1, 'etincelles' => 0.05, 'recurrent' => 0.3],
            'heures' => [0, 23], 'temperature' => [30, 44],
        ],
        'surcharge_reseau' => [
            'zones' => ['rue', 'quartier'],
            'probas' => ['disjoncteur_saute' => 0.1, 'baisse_tension_avant' => 0.85, 'odeur_brule' => 0.05, 'etincelles' => 0.05, 'recurrent' => 0.7],
            'heures' => [12, 18], 'temperature' => [39, 47],
        ],
        'panne_transformateur' => [
            'zones' => ['immeuble', 'rue'],
            'probas' => ['disjoncteur_saute' => 0.05, 'baisse_tension_avant' => 0.4, 'odeur_brule' => 0.8, 'etincelles' => 0.2, 'recurrent' => 0.3],
            'heures' => [0, 23], 'temperature' => [35, 46],
        ],
        'cable_endommage' => [
            'zones' => ['immeuble', 'rue'],
            'probas' => ['disjoncteur_saute' => 0.2, 'baisse_tension_avant' => 0.2, 'odeur_brule' => 0.4, 'etincelles' => 0.9, 'recurrent' => 0.1],
            'heures' => [0, 23], 'temperature' => [28, 42],
        ],
        'delestage_planifie' => [
            'zones' => ['quartier'],
            'probas' => ['disjoncteur_saute' => 0.02, 'baisse_tension_avant' => 0.1, 'odeur_brule' => 0.02, 'etincelles' => 0.02, 'recurrent' => 0.6],
            'heures' => [10, 20], 'temperature' => [38, 46],
        ],
        'panne_equipement' => [
            'zones' => ['logement', 'immeuble'],
            'probas' => ['disjoncteur_saute' => 0.3, 'baisse_tension_avant' => 0.1, 'odeur_brule' => 0.5, 'etincelles' => 0.1, 'recurrent' => 0.4],
            'heures' => [8, 23], 'temperature' => [33, 46],
        ],
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cause  = $this->faker->randomElement(array_keys(self::PROFILS));
        $profil = self::PROFILS[$cause];

        $symptomes = [];
        foreach ($profil['probas'] as $symptome => $proba) {
            $symptomes[$symptome] = $this->faker->boolean((int) round($proba * 100));
        }

        // 10 % de bruit sur la zone pour éviter des données trop « parfaites ».
        $zone = $this->faker->boolean(90)
            ? $this->faker->randomElement($profil['zones'])
            : $this->faker->randomElement(array_keys(Diagnostic::ZONES));

        return array_merge($symptomes, [
            'signalement_id'        => Signalement::inRandomOrder()->first()?->id
                                       ?? Signalement::factory(),
            'zone_touchee'          => $zone,
            'personnes_vulnerables' => $this->faker->boolean(25),
            'heure_panne'           => sprintf('%02d:%02d',
                $this->faker->numberBetween(...$profil['heures']),
                $this->faker->numberBetween(0, 59)
            ),
            'temperature'           => $this->faker->randomFloat(1, ...$profil['temperature']),
            'cause_reelle'          => $cause,
            'remarque'              => null,
        ]);
    }

    /**
     * Diagnostic pas encore confirmé par l'administrateur.
     */
    public function nonConfirme(): static
    {
        return $this->state(fn () => ['cause_reelle' => null]);
    }
}
