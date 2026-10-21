<?php

namespace Database\Seeders;

use App\Models\Alerte;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AlerteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $alertes = [
            // --- 5 Alertes EN COURS ---
            [
                'id' => 1,
                'titre' => 'Vague de canicule extrême et dôme de chaleur saharien',
                'type' => 'canicule',
                'zone_concernee' => 'Grand Tunis (Tunis, Ariana, Ben Arous, Manouba)',
                'description' => 'Un dôme thermique persistant fait grimper les températures au-delà de 45°C avec une humidité étouffante. Vigilance accrue pour les personnes vulnérables et risques de pannes sur le réseau électrique.',
                'source' => 'Institut National de la Météorologie (INM)',
                'date_debut' => $now->copy()->subDays(3)->setTime(8, 0),
                'date_fin' => null,
                'statut' => 'en_cours',
            ],
            [
                'id' => 2,
                'titre' => 'Alerte crues soudaines et ruissellements torrentiels',
                'type' => 'inondation',
                'zone_concernee' => 'Gouvernorat de Nabeul et littoral du Cap Bon',
                'description' => 'Fortes précipitations orageuses stationnaires provoquant des crues rapides des oueds côtiers et submersion des voies urbaines.',
                'source' => 'Direction Générale des Ressources en Eau & Protection Civile',
                'date_debut' => $now->copy()->subDays(2)->setTime(14, 30),
                'date_fin' => null,
                'statut' => 'en_cours',
            ],
            [
                'id' => 3,
                'titre' => 'Cellules orageuses violentes avec chutes de grêle',
                'type' => 'orage',
                'zone_concernee' => 'Région du Sahel (Sousse, Monastir, Mahdia)',
                'description' => 'Développement d’orages supercellulaires avec forte activité électrique, rafales descendantes violentes et risques de grêlons destructeurs.',
                'source' => 'Institut National de la Météorologie (INM)',
                'date_debut' => $now->copy()->subDays(1)->setTime(16, 0),
                'date_fin' => null,
                'statut' => 'en_cours',
            ],
            [
                'id' => 4,
                'titre' => 'Alerte feux de forêt et canicule en zones montagneuses',
                'type' => 'incendie_foret',
                'zone_concernee' => 'Massifs forestiers de Tabarka, Aïn Draham et Kroumirie',
                'description' => 'Indice d’inflammabilité forestière maximal en raison d’un sirocco violent et d’une hygrométrie inférieure à 15%. Risque extrême de départs de feux spontanés.',
                'source' => 'Direction Générale des Forêts',
                'date_debut' => $now->copy()->subDays(2)->setTime(10, 0),
                'date_fin' => null,
                'statut' => 'en_cours',
            ],
            [
                'id' => 5,
                'titre' => 'Pic critique de particules fines et stagnation atmosphérique',
                'type' => 'pollution_air',
                'zone_concernee' => 'Grand Sfax et agglomération industrielle',
                'description' => 'Concentration alarmante de PM10 et dioxyde de soufre causée par une inversion thermique prolongée au-dessus du bassin industriel.',
                'source' => 'Agence Nationale de Protection de l’Environnement (ANPE)',
                'date_debut' => $now->copy()->subHours(36)->setTime(9, 0),
                'date_fin' => null,
                'statut' => 'en_cours',
            ],

            // --- 3 Alertes TERMINÉES ---
            [
                'id' => 6,
                'titre' => 'Tempête hivernale et coups de vent violents sur le littoral nord',
                'type' => 'vent_fort',
                'zone_concernee' => 'Gouvernorat de Bizerte et côtes septentrionales',
                'description' => 'Dépression méditerranéenne ayant provoqué des rafales à plus de 115 km/h et une houle côtière dangereuse. Phénomène entièrement dissipé.',
                'source' => 'Institut National de la Météorologie (INM)',
                'date_debut' => $now->copy()->subDays(8)->setTime(6, 0),
                'date_fin' => $now->copy()->subDays(5)->setTime(18, 0),
                'statut' => 'terminee',
            ],
            [
                'id' => 7,
                'titre' => 'Canicule précoce et vents de sable dans les oasis',
                'type' => 'canicule',
                'zone_concernee' => 'Régions de Kébili, Douz et Tozeur',
                'description' => 'Vague de chaleur accompagnée d’un vent de sable ayant perturbé la circulation et provoqué des malaises thermiques. Situation normalisée.',
                'source' => 'Office National de la Protection Civile',
                'date_debut' => $now->copy()->subDays(12)->setTime(11, 0),
                'date_fin' => $now->copy()->subDays(9)->setTime(20, 0),
                'statut' => 'terminee',
            ],
            [
                'id' => 8,
                'titre' => 'Épisode méditerranéen de précipitations torrentielles',
                'type' => 'inondation',
                'zone_concernee' => 'Ariana Ville, Raoued et Soukra',
                'description' => 'Infiltrations d’eau et accumulation pluviale importante dans les zones basses. Débit des canaux revenu à la normale après pompage.',
                'source' => 'Commission Régionale de Gestion des Catastrophes',
                'date_debut' => $now->copy()->subDays(15)->setTime(7, 30),
                'date_fin' => $now->copy()->subDays(13)->setTime(15, 0),
                'statut' => 'terminee',
            ],
        ];

        foreach ($alertes as $data) {
            Alerte::updateOrCreate(['id' => $data['id']], $data);
        }
    }
}
