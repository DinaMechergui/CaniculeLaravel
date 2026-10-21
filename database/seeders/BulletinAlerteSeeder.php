<?php

namespace Database\Seeders;

use App\Models\Alerte;
use App\Models\BulletinAlerte;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BulletinAlerteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $bulletins = [
            // --- Alerte 1 : Canicule Grand Tunis (en cours, actuelle ROUGE) ---
            [
                'alerte_id' => 1,
                'niveau' => 'jaune',
                'evolution' => 'stable',
                'message' => 'Températures en hausse sensible atteignant 38°C à 40°C sur le Grand Tunis. Vigilance jaune activée.',
                'conduite_a_tenir' => 'Hydratez-vous régulièrement et évitez les efforts intenses aux heures les plus chaudes.',
                'temperature_max' => 40,
                'date_publication' => $now->copy()->subDays(3)->setTime(8, 0),
            ],
            [
                'alerte_id' => 1,
                'niveau' => 'orange',
                'evolution' => 'aggravation',
                'message' => 'Renforcement marqué de la chaleur. Les maximales dépassent 43°C avec des nuits tropicales à plus de 29°C.',
                'conduite_a_tenir' => 'Fermez volets et fenêtres le jour. Veillez sur les aînés et les enfants en bas âge.',
                'temperature_max' => 43,
                'date_publication' => $now->copy()->subDays(2)->setTime(10, 0),
            ],
            [
                'alerte_id' => 1,
                'niveau' => 'rouge',
                'evolution' => 'aggravation',
                'message' => 'DANGER EXTRÊME : Seuil caniculaire rouge franchi avec 46°C attendus sous abri. Risque critique pour la santé.',
                'conduite_a_tenir' => 'Suspendez toute activité sportive en extérieur. Restez dans des espaces climatisés ou des îlots de fraîcheur.',
                'temperature_max' => 46,
                'date_publication' => $now->copy()->subDays(1)->setTime(9, 30),
            ],
            [
                'alerte_id' => 1,
                'niveau' => 'rouge',
                'evolution' => 'stable',
                'message' => 'Maintien de la vigilance rouge. Pic d’intensité toujours en cours sur la capitale.',
                'conduite_a_tenir' => 'Appliquez strictement les consignes du plan d’urgence canicule communal.',
                'temperature_max' => 45,
                'date_publication' => $now->copy()->subHours(8),
            ],

            // --- Alerte 2 : Inondation Cap Bon (en cours, actuelle ORANGE) ---
            [
                'alerte_id' => 2,
                'niveau' => 'jaune',
                'evolution' => 'stable',
                'message' => 'Pluies éparses localement modérées. Risque de ruissellement sur sols saturés.',
                'conduite_a_tenir' => 'Évitez les zones basses et soyez attentifs près des oueds.',
                'temperature_max' => 26,
                'date_publication' => $now->copy()->subDays(2)->setTime(14, 30),
            ],
            [
                'alerte_id' => 2,
                'niveau' => 'orange',
                'evolution' => 'aggravation',
                'message' => 'Cumuls attendus de 60 à 90 mm en quelques heures. Débordements imminents des oueds côtiers.',
                'conduite_a_tenir' => 'Ne vous engagez jamais sur une route submergée à pied ou en voiture.',
                'temperature_max' => 24,
                'date_publication' => $now->copy()->subDays(1)->setTime(18, 0),
            ],
            [
                'alerte_id' => 2,
                'niveau' => 'orange',
                'evolution' => 'stable',
                'message' => 'Le front orageux pluvieux persiste sur le Cap Bon. Écoulements d’eau très rapides.',
                'conduite_a_tenir' => 'Mettez vos biens hors d’eau dans les rez-de-chaussée vulnérables.',
                'temperature_max' => 23,
                'date_publication' => $now->copy()->subHours(6),
            ],

            // --- Alerte 3 : Orage Sahel (en cours, actuelle ORANGE) ---
            [
                'alerte_id' => 3,
                'niveau' => 'vert',
                'evolution' => 'stable',
                'message' => 'Conditions météo calmes avant l’arrivée d’une dépression orageuse active.',
                'conduite_a_tenir' => 'Surveillez les prochains bulletins météo.',
                'temperature_max' => 31,
                'date_publication' => $now->copy()->subDays(1)->setTime(16, 0),
            ],
            [
                'alerte_id' => 3,
                'niveau' => 'jaune',
                'evolution' => 'aggravation',
                'message' => 'Formation rapide de foyers orageux au large et sur le littoral sahélien.',
                'conduite_a_tenir' => 'Fixez les objets sensibles au vent et débranchez les appareils électroniques.',
                'temperature_max' => 30,
                'date_publication' => $now->copy()->subHours(20),
            ],
            [
                'alerte_id' => 3,
                'niveau' => 'orange',
                'evolution' => 'aggravation',
                'message' => 'Orages violents en cours avec impacts de foudre fréquents et grêlons atteignant 2 cm.',
                'conduite_a_tenir' => 'Abritez-vous immédiatement dans un bâtiment en dur. Ne stationnez pas sous les arbres.',
                'temperature_max' => 27,
                'date_publication' => $now->copy()->subHours(4),
            ],

            // --- Alerte 4 : Feux de forêt Tabarka (en cours, actuelle ROUGE) ---
            [
                'alerte_id' => 4,
                'niveau' => 'jaune',
                'evolution' => 'stable',
                'message' => 'Vent de sirocco modéré et température élevée. Indice météo feu en progression.',
                'conduite_a_tenir' => 'Interdiction absolue de faire du feu ou d’utiliser des barbecues en lisière.',
                'temperature_max' => 39,
                'date_publication' => $now->copy()->subDays(2)->setTime(10, 0),
            ],
            [
                'alerte_id' => 4,
                'niveau' => 'orange',
                'evolution' => 'aggravation',
                'message' => 'Conditions très défavorables : hygrométrie sous 15% et rafales à 60 km/h.',
                'conduite_a_tenir' => 'Évitez l’accès aux massifs forestiers et signalez toute fumée au 198.',
                'temperature_max' => 42,
                'date_publication' => $now->copy()->subDays(1)->setTime(12, 0),
            ],
            [
                'alerte_id' => 4,
                'niveau' => 'rouge',
                'evolution' => 'aggravation',
                'message' => 'Niveau d’alerte maximal : foyers multiples attisés par des vents tourbillonnants.',
                'conduite_a_tenir' => 'Évacuez les habitations isolées si les autorités en donnent l’ordre.',
                'temperature_max' => 45,
                'date_publication' => $now->copy()->subHours(10),
            ],

            // --- Alerte 5 : Pollution Sfax (en cours, actuelle JAUNE) ---
            [
                'alerte_id' => 5,
                'niveau' => 'jaune',
                'evolution' => 'stable',
                'message' => 'Hausse des taux de particules fines liée au trafic et aux émissions industrielles.',
                'conduite_a_tenir' => 'Limitez les activités physiques en extérieur pour les personnes asthmatiques.',
                'temperature_max' => 32,
                'date_publication' => $now->copy()->subHours(36)->setTime(9, 0),
            ],
            [
                'alerte_id' => 5,
                'niveau' => 'orange',
                'evolution' => 'aggravation',
                'message' => 'Dépassement du seuil d’information et recommandation pour les particules fines PM10.',
                'conduite_a_tenir' => 'Privilégiez les transports collectifs et réduisez la vitesse automobile.',
                'temperature_max' => 33,
                'date_publication' => $now->copy()->subHours(22),
            ],
            [
                'alerte_id' => 5,
                'niveau' => 'jaune',
                'evolution' => 'amelioration',
                'message' => 'Dispersion partielle des polluants grâce à une brise marine bienfaisante.',
                'conduite_a_tenir' => 'Reprise progressive des activités habituelles avec maintien de la vigilance.',
                'temperature_max' => 30,
                'date_publication' => $now->copy()->subHours(5),
            ],

            // --- Alerte 6 : Vents violents Bizerte (TERMINÉE) ---
            [
                'alerte_id' => 6,
                'niveau' => 'jaune',
                'evolution' => 'stable',
                'message' => 'Renforcement progressif des vents du nord-ouest sur les côtes de Bizerte.',
                'conduite_a_tenir' => 'Vérifiez l’arrimage des embarcations et des toitures légères.',
                'temperature_max' => 19,
                'date_publication' => $now->copy()->subDays(8)->setTime(6, 0),
            ],
            [
                'alerte_id' => 6,
                'niveau' => 'orange',
                'evolution' => 'aggravation',
                'message' => 'Tempête en cours avec rafales enregistrées à 110 km/h au cap Blanc.',
                'conduite_a_tenir' => 'Limitez vos déplacements et ne vous approchez pas du rivage.',
                'temperature_max' => 17,
                'date_publication' => $now->copy()->subDays(7)->setTime(14, 0),
            ],
            [
                'alerte_id' => 6,
                'niveau' => 'jaune',
                'evolution' => 'amelioration',
                'message' => 'Les vents faiblissent sensiblement, rafales sous la barre des 60 km/h.',
                'conduite_a_tenir' => 'Attention aux objets et câbles tombés sur la chaussée.',
                'temperature_max' => 20,
                'date_publication' => $now->copy()->subDays(6)->setTime(9, 0),
            ],
            [
                'alerte_id' => 6,
                'niveau' => 'vert',
                'evolution' => 'levee',
                'message' => 'Retour complet au calme. Toutes les restrictions météorologiques sont levées.',
                'conduite_a_tenir' => 'Fin de la phase de vigilance.',
                'temperature_max' => 21,
                'date_publication' => $now->copy()->subDays(5)->setTime(18, 0),
            ],

            // --- Alerte 7 : Canicule Kébili (TERMINÉE) ---
            [
                'alerte_id' => 7,
                'niveau' => 'jaune',
                'evolution' => 'stable',
                'message' => 'Hausse des températures dans le Sud saharien.',
                'conduite_a_tenir' => 'Hydratation continue.',
                'temperature_max' => 41,
                'date_publication' => $now->copy()->subDays(12)->setTime(11, 0),
            ],
            [
                'alerte_id' => 7,
                'niveau' => 'orange',
                'evolution' => 'aggravation',
                'message' => 'Vent de sable et 45°C enregistrés à Kébili.',
                'conduite_a_tenir' => 'Restez confinés au frais.',
                'temperature_max' => 45,
                'date_publication' => $now->copy()->subDays(11)->setTime(15, 0),
            ],
            [
                'alerte_id' => 7,
                'niveau' => 'vert',
                'evolution' => 'levee',
                'message' => 'Chute des températures de 10°C suite à une bascule des vents. Alerte levée.',
                'conduite_a_tenir' => 'Retour à la situation normale.',
                'temperature_max' => 34,
                'date_publication' => $now->copy()->subDays(9)->setTime(20, 0),
            ],

            // --- Alerte 8 : Inondations Ariana (TERMINÉE) ---
            [
                'alerte_id' => 8,
                'niveau' => 'orange',
                'evolution' => 'stable',
                'message' => 'Épisode pluvieux abondant provoquant des montées d’eau dans les quartiers bas.',
                'conduite_a_tenir' => 'Surélevez les appareils électriques.',
                'temperature_max' => 22,
                'date_publication' => $now->copy()->subDays(15)->setTime(7, 30),
            ],
            [
                'alerte_id' => 8,
                'niveau' => 'rouge',
                'evolution' => 'aggravation',
                'message' => 'Niveau d’eau critique dans plusieurs axes routiers de Raoued et Soukra.',
                'conduite_a_tenir' => 'Refugiez-vous dans les étages supérieurs et contactez les secours si nécessaire.',
                'temperature_max' => 20,
                'date_publication' => $now->copy()->subDays(14)->setTime(11, 0),
            ],
            [
                'alerte_id' => 8,
                'niveau' => 'orange',
                'evolution' => 'amelioration',
                'message' => 'Fin des pluies continues, opérations d’évacuation des eaux en bonne progression.',
                'conduite_a_tenir' => 'Respectez le balisage des voies temporairement coupées.',
                'temperature_max' => 22,
                'date_publication' => $now->copy()->subDays(14)->setTime(20, 0),
            ],
            [
                'alerte_id' => 8,
                'niveau' => 'vert',
                'evolution' => 'levee',
                'message' => 'Toutes les voies sont réouvertes et le niveau des eaux est stabilisé. Alerte terminée.',
                'conduite_a_tenir' => 'Nettoyage des résidus sous contrôle municipal.',
                'temperature_max' => 24,
                'date_publication' => $now->copy()->subDays(13)->setTime(15, 0),
            ],
        ];

        foreach ($bulletins as $data) {
            BulletinAlerte::create($data);
        }
    }
}
