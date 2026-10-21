<?php

namespace Database\Factories;

use App\Models\Alerte;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Alerte>
 */
class AlerteFactory extends Factory
{
    protected $model = Alerte::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = array_keys(Alerte::TYPES);
        $type = $this->faker->randomElement($types);

        $zones = [
            'District de Tunis (Tunis, Ariana, Ben Arous, Manouba)',
            'Zone côtière du Cap Bon (Nabeul, Hammamet)',
            'Région du Sahel (Sousse, Monastir, Mahdia)',
            'Gouvernorat de Bizerte et littoral Nord',
            'Nord-Ouest (Béja, Jendouba, Siliana)',
            'Centre-Ouest (Kairouan, Kasserine, Sidi Bouzid)',
            'Grand Sfax et îles Kerkennah',
            'Sud-Est (Gabès, Médenine, Tataouine)',
        ];

        $titres = [
            'canicule' => 'Vague de chaleur intense et températures record',
            'orage' => 'Orages violents accompagnés de chutes de grêle',
            'inondation' => 'Risque majeur de crues subites et inondations urbaines',
            'vent_fort' => 'Rafales de vent violentes et tempête de sable',
            'incendie_foret' => 'Risque extrême de départs de feux de forêt',
            'pollution_air' => 'Pic de concentration de particules fines dans l’air',
        ];

        $dateDebut = $this->faker->dateTimeBetween('-5 days', '-1 day');

        return [
            'titre' => $titres[$type] ?? 'Alerte environnementale d’urgence',
            'type' => $type,
            'zone_concernee' => $this->faker->randomElement($zones),
            'description' => $this->faker->paragraphs(2, true),
            'source' => $this->faker->randomElement([
                'Institut National de la Météorologie (INM)',
                'Office National de la Protection Civile',
                'Direction Générale des Forêts',
                'Ministère de la Santé et de l’Environnement',
            ]),
            'date_debut' => $dateDebut,
            'date_fin' => null,
            'statut' => 'en_cours',
        ];
    }

    /**
     * Indicate that the alerte is active (en cours).
     */
    public function enCours(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'en_cours',
            'date_fin' => null,
        ]);
    }

    /**
     * Indicate that the alerte is finished (terminee).
     */
    public function terminee(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'terminee',
            'date_fin' => now(),
        ]);
    }
}
