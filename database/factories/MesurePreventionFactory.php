<?php

namespace Database\Factories;

use App\Models\AlerteMeteo;
use App\Models\MesurePrevention;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MesurePrevention>
 */
class MesurePreventionFactory extends Factory
{
    protected $model = MesurePrevention::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mesuresData = [
            'evacuation' => [
                'titres' => [
                    'Évacuation préventive des résidences en lisière de forêt',
                    'Plan d\'évacuation d\'urgence des campements et zones inondables',
                    'Transfert préventif des riverains vers le gymnase municipal sécurisé',
                    'Évacuation des campings et centres de vacances exposés au sirocco',
                ],
                'descriptions' => [
                    'Mise en place d\'une colonne de bus de transport public sous escorte policière pour transférer les résidents vulnérables vers le centre d\'accueil sécurisé.',
                    'Déploiement des équipes de secours pour assister les personnes âgées et à mobilité réduite vers les structures d\'hébergement temporaires.',
                ],
            ],
            'fermeture_site' => [
                'titres' => [
                    'Fermeture exceptionnelle des chantiers et travaux publics de 11h à 17h',
                    'Fermeture temporaire des parcs publics et massifs forestiers',
                    'Suspension des activités sportives en plein air et fermeture des stades',
                    'Arrêt momentané des activités maritimes et fermeture des plages à la baignade',
                ],
                'descriptions' => [
                    'Interdiction d\'accès au public afin de prévenir les accidents liés aux rafales et au stress thermique. Affichage des arrêtés municipaux aux entrées.',
                    'Suspension de tout travail sous exposition solaire directe en application des directives du ministère de la Santé et des Affaires Sociales.',
                ],
            ],
            'distribution_eau' => [
                'titres' => [
                    'Distribution massive de packs d\'eau minérale aux gares et stations',
                    'Installation de citernes d\'eau potable réfrigérées en centre-ville',
                    'Ravitaillement d\'urgence en eau potable pour les quartiers isolés',
                    'Distribution de brumisateurs et gourdes d\'eau aux personnes sans-abri',
                ],
                'descriptions' => [
                    'Mise à disposition gratuite de bouteilles d\'eau minérale et d\'électrolytes aux points de passage névralgiques par les volontaires du Croissant Rouge.',
                    'Approvisionnement continu des réservoirs publics et distribution d\'eau fraîche aux personnes âgées isolées identifiées par les services sociaux.',
                ],
            ],
            'renfort_secours' => [
                'titres' => [
                    'Mobilisation des renforts de la Protection Civile (198)',
                    'Déploiement d\'une antenne médicale avancée du SAMU (190)',
                    'Patrouilles mobiles de surveillance incendie de la Direction des Forêts',
                    'Renfort des effectifs des urgences hospitalières et postes avancés',
                ],
                'descriptions' => [
                    'Pré-positionnement d\'ambulances et de camions-citernes dans les secteurs à risque accru pour garantir un délai d\'intervention inférieur à 7 minutes.',
                    'Présence renforcée d\'équipes médicales d\'urgence avec unités d\'oxygénation et matériel de réhydratation rapide.',
                ],
            ],
        ];

        $responsables = [
            'Protection Civile Tunisienne (ONPC)',
            'Croissant Rouge Tunisien - Comité Régional',
            'Direction Générale des Forêts (DGF Tunisie)',
            'SAMU 190 et Direction Régionale de la Santé',
            'Municipalité et Services Techniques de la Ville',
            'Commission Régionale de Prévention des Catastrophes',
            'Société Nationale d\'Exploitation et de Distribution des Eaux (SONEDE)',
        ];

        $types = ['evacuation', 'fermeture_site', 'distribution_eau', 'renfort_secours'];
        $statuts = ['prevue', 'en_cours', 'terminee'];

        $type = fake()->randomElement($types);
        $typeGroup = $mesuresData[$type];
        $titre = fake()->randomElement($typeGroup['titres']);
        $description = fake()->randomElement($typeGroup['descriptions']);
        $statut = fake()->randomElement($statuts);

        return [
            'alerte_meteo_id' => AlerteMeteo::factory(),
            'titre' => $titre,
            'type' => $type,
            'description' => $description,
            'responsable' => fake()->randomElement($responsables),
            'date_debut' => now()->subHours(fake()->numberBetween(1, 24)),
            'statut' => $statut,
        ];
    }

    /**
     * Mesure prévue.
     */
    public function prevue(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => MesurePrevention::STATUT_PREVUE,
            'date_debut' => now()->addHours(6),
        ]);
    }

    /**
     * Mesure en cours.
     */
    public function enCours(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => MesurePrevention::STATUT_EN_COURS,
            'date_debut' => now()->subHours(4),
        ]);
    }

    /**
     * Mesure terminée.
     */
    public function terminee(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => MesurePrevention::STATUT_TERMINEE,
            'date_debut' => now()->subDays(2),
        ]);
    }
}
