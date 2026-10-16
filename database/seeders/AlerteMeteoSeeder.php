<?php

namespace Database\Seeders;

use App\Models\AlerteMeteo;
use App\Models\NiveauAlerte;
use Illuminate\Database\Seeder;

class AlerteMeteoSeeder extends Seeder
{
    /**
     * Run the database seeds for Météo Tunisie (INM).
     */
    public function run(): void
    {
        $alertesParCouleur = [
            'vert' => [
                [
                    'titre' => 'Vigilance Verte : Conditions Climatologiques Normales - Grand Tunis',
                    'message' => 'Temps généralement peu nuageux sur le Grand Tunis (Tunis, Ariana, Ben Arous, Manouba). Températures maximales conformes aux moyennes de saison (entre 28°C et 31°C). Activités extérieures normales autorisées.',
                    'source' => 'Institut National de la Météorologie (INM)',
                ],
                [
                    'titre' => 'Vigilance Verte : Brise Marine Rafraîchissante - Littoral de Bizerte et Tabarka',
                    'message' => 'Vent de secteur Nord faible à modéré, mer peu agitée. Températures douces oscillant entre 26°C et 29°C sur l\'ensemble des côtes septentrionales de Tunisie.',
                    'source' => 'Institut National de la Météorologie (INM)',
                ],
                [
                    'titre' => 'Vigilance Verte : Ciel Dégagé et Excellente Visibilité - Sahel (Sousse & Monastir)',
                    'message' => 'Ciel clair à peu nuageux sur les régions du Sahel tunisien. Vents modérés et températures douces propices à la baignade et aux activités maritimes.',
                    'source' => 'Institut National de la Météorologie (INM)',
                ],
                [
                    'titre' => 'Vigilance Verte : Climat Marin Estival Calme - Région du Cap Bon (Nabeul)',
                    'message' => 'Plein soleil avec vent faible. Aucune anomalie météorologique à signaler par la station synoptique de Kélibia. Indice UV modéré.',
                    'source' => 'Institut National de la Météorologie (INM)',
                ],
                [
                    'titre' => 'Vigilance Verte : Températures de Saison Agréables - Djerba et Golfe de Gabès',
                    'message' => 'Temps ensoleillé et calme sur l\'île de Djerba et la région de Zarzis. Mer belle et brise marine continue durant la journée.',
                    'source' => 'Protection Civile Tunisienne (ONPC)',
                ],
            ],
            'jaune' => [
                [
                    'titre' => 'Vigilance Jaune : Hausse Modérée des Températures (36°C-38°C) - Sfax et Kerkennah',
                    'message' => 'L\'INM annonce une hausse graduelle du mercure atteignant 38°C dans l\'intérieur de Sfax. Restez attentifs si vous pratiquez des activités extérieures sous le soleil.',
                    'source' => 'Institut National de la Météorologie (INM)',
                ],
                [
                    'titre' => 'Vigilance Jaune : Vents Modérés et Légère Poussière en Suspension - Sidi Bouzid',
                    'message' => 'Vent de secteur Sud soulevant localement du sable et de la poussière. Baisse modérée de la visibilité sur les axes routiers de la région centrale.',
                    'source' => 'Protection Civile Tunisienne (ONPC)',
                ],
                [
                    'titre' => 'Vigilance Jaune : Risque d\'Ondées Orageuses Isolées en Après-Midi - Siliana et Zaghouan',
                    'message' => 'Des cellules orageuses isolées sont attendues sur les hauteurs en fin d\'après-midi avec possibilité de brèves averses et de coups de vent locaux.',
                    'source' => 'Institut National de la Météorologie (INM)',
                ],
                [
                    'titre' => 'Vigilance Jaune : Vents Assez Forts sur les Côtes Nord - Canal de Bizerte',
                    'message' => 'Renforcement du vent atteignant temporairement 50 km/h sur les côtes nord de la Tunisie. Mer agitée, baigneurs et marins appelés à la prudence.',
                    'source' => 'Institut National de la Météorologie (INM)',
                ],
                [
                    'titre' => 'Vigilance Jaune : Chaleur Humide et Risque d\'Inconfort - Ben Arous et Mornag',
                    'message' => 'Forte humidité conjuguée à des températures proches de 37°C. Pensez à boire régulièrement de l\'eau et à ventiler les pièces de vie.',
                    'source' => 'Observatoire National du Climat (Tunisie)',
                ],
            ],
            'orange' => [
                [
                    'titre' => 'Vigilance Orange : Vague de Chaleur et Sirocco (Chhili) Dépassant 43°C - Kairouan',
                    'message' => 'Vague de chaleur intense avec vent de sirocco saharien (Chhili) touchant le gouvernorat de Kairouan. Températures maximales comprises entre 42°C et 44°C. Évitez les sorties aux heures les plus chaudes.',
                    'source' => 'Institut National de la Météorologie (INM)',
                ],
                [
                    'titre' => 'Vigilance Orange : Risque Élevé d\'Incendies de Forêts - Jendouba et Aïn Draham',
                    'message' => 'Alerte sécheresse et vent chaud dans les massifs forestiers de Kroumirie. La Direction Générale des Forêts et la Protection Civile appellent à l\'interdiction totale des feux en plein air.',
                    'source' => 'Direction Générale des Forêts (DGF Tunisie)',
                ],
                [
                    'titre' => 'Vigilance Orange : Pic Thermique Sévère et Risque de Coup de Chaleur - Gafsa et Tozeur',
                    'message' => 'Hausse spectaculaire des températures avec 44°C attendus. Les personnes âgées, enfants et travailleurs en extérieur doivent observer un repos à l\'ombre régulier. Urgences : 198.',
                    'source' => 'Protection Civile Tunisienne (ONPC)',
                ],
                [
                    'titre' => 'Vigilance Orange : Orages Violents et Chutes de Grêle Locales - Le Kef et Béja',
                    'message' => 'Développement d\'orages violents accompagnés de chutes de grêle par endroits et de rafales de vent pouvant dépasser 70 km/h. Évitez la proximité des cours d\'eau et des arbres isolés.',
                    'source' => 'Institut National de la Météorologie (INM)',
                ],
                [
                    'titre' => 'Vigilance Orange : Vents Forts de Sud avec Soulèvement de Sable - Kébili et Tataouine',
                    'message' => 'Vent saharien violent réduisant fortement la visibilité sur les pistes et routes nationales du Sud. Conduite déconseillée sans précaution absolue.',
                    'source' => 'Institut National de la Météorologie (INM)',
                ],
            ],
            'rouge' => [
                [
                    'titre' => 'Vigilance Rouge : Canicule Historique Extrême (Pic à 48°C) - Tozeur et Kébili',
                    'message' => 'URGENCE MÉTÉO TUNISIE : Pic de canicule extrême atteignant 47°C à 49°C à l\'ombre dans les oasis du Djérid et du Nefzaoua. Risque vital de coup de chaleur et de déshydratation aiguë. Restez impérativement confinés au frais. Services d\'urgence joignables au 198 (Protection Civile) et 190 (SAMU).',
                    'source' => 'Institut National de la Météorologie (INM)',
                ],
                [
                    'titre' => 'Vigilance Rouge : Canicule Sévère et Sirocco Violent - Kairouan et Sidi Bouzid',
                    'message' => 'Situation météorologique critique. Températures au-delà de 46°C associées à un vent de sirocco ardent. Déclenchement de la commission régionale de lutte contre les catastrophes.',
                    'source' => 'Protection Civile Tunisienne (ONPC)',
                ],
                [
                    'titre' => 'Vigilance Rouge : Risque Majeur d\'Incendies et Chaleur Extrême - Tabarka et Sejnane',
                    'message' => 'Alerte maximale déclenchée. Combinaison de chaleur record (44°C) et de rafales de sirocco asséchant les forêts du Nord-Ouest. Mobilisation totale des colonnes de pompiers de la Protection Civile.',
                    'source' => 'Direction Générale des Forêts (DGF Tunisie)',
                ],
                [
                    'titre' => 'Vigilance Rouge : Pic Thermique Historique et Risque de Surtension - Grand Tunis',
                    'message' => 'Canicule exceptionnelle sur la capitale et sa banlieue (Tunis, Ariana, Manouba) avec 45°C prévus. Risque de perturbations sur les réseaux d\'électricité et d\'eau. Les points de fraîcheur communaux sont ouverts 24h/24.',
                    'source' => 'Institut National de la Météorologie (INM)',
                ],
                [
                    'titre' => 'Vigilance Rouge : Tempête de Sable Saharienne et Visibilité Nulle - Région de Remada',
                    'message' => 'Conditions extrêmes dans le Sahara tunisien. Vents violents dépassant 80 km/h avec tempête de sable dense bloquant la circulation sur les routes du Grand Sud.',
                    'source' => 'Protection Civile Tunisienne (ONPC)',
                ],
            ],
        ];

        $niveaux = NiveauAlerte::all();

        foreach ($niveaux as $niveau) {
            $alertesListe = $alertesParCouleur[$niveau->couleur] ?? [];

            foreach ($alertesListe as $data) {
                // firstOrCreate : évite les doublons si le seeder est relancé.
                AlerteMeteo::firstOrCreate(
                    ['titre' => $data['titre']],
                    [
                        'niveau_alerte_id' => $niveau->id,
                        'message' => $data['message'],
                        'source' => $data['source'],
                        'date_debut' => now()->subHours(rand(1, 18)),
                        'date_fin' => now()->addHours(rand(24, 72)),
                    ]
                );
            }
        }
    }
}
