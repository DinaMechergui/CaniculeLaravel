<?php

namespace Database\Seeders;

use App\Models\PointFraicheur;
use App\Models\TypePoint;
use Illuminate\Database\Seeder;

/**
 * Crée les points de fraîcheur de démonstration à partir de lieux réels
 * de la Grande Tunis (données OpenStreetMap, relevé du 4 octobre 2026) :
 * coordonnées, adresse (géocodage inverse Nominatim) et horaires
 * lorsqu'ils sont renseignés sur OpenStreetMap.
 */
class PointFraicheurSeeder extends Seeder
{
    /**
     * Lieux réels groupés par type : nom, adresse, coordonnées et horaires.
     *
     * @var array<string, list<array{nom: string, adresse: string, latitude: float, longitude: float, horaires?: string}>>
     */
    private const LIEUX = [
        'Parc' => [
            [
                'nom' => 'Parc du martyre Helmi Manai',
                'adresse' => 'Avenue Hédi Chaker, Hedi Chaker, Tunis',
                'latitude' => 36.810212,
                'longitude' => 10.174161,
            ],
            [
                'nom' => 'Parc El Gorjani',
                'adresse' => 'Boulevard du 9 Avril 1938, Montfleury, Tunis',
                'latitude' => 36.788135,
                'longitude' => 10.167996,
            ],
            [
                'nom' => 'Jardin botanique',
                'adresse' => 'Rue Salah Eddine El Ayoubi, El Izdihar, Ariana',
                'latitude' => 36.855377,
                'longitude' => 10.185174,
            ],
            [
                'nom' => 'Park Central - Jardins de l\'Aouina',
                'adresse' => 'Rue des Vergers, Cité Taeib M\'hiri, Tunis',
                'latitude' => 36.854926,
                'longitude' => 10.254297,
            ],
            [
                'nom' => 'Parc Salammbô',
                'adresse' => 'Impasse Diar El Bahar, Carthage Plage, Tunis',
                'latitude' => 36.838620,
                'longitude' => 10.325614,
            ],
            [
                'nom' => 'Jardin Publique',
                'adresse' => 'Rue du 2 Mars 1934, Sidi Bou Saïd, Tunis',
                'latitude' => 36.870177,
                'longitude' => 10.345762,
            ],
            [
                'nom' => 'Jardin mourouj 5',
                'adresse' => 'Rue de Jilma, El Mourouj (5), Ben Arous',
                'latitude' => 36.711108,
                'longitude' => 10.207957,
            ],
            [
                'nom' => 'Parc de loisirs Zahoor',
                'adresse' => 'Avenue Habib Thameur, Ez-Zahra Ville',
                'latitude' => 36.741303,
                'longitude' => 10.319714,
            ],
            [
                'nom' => 'Parc Al Nassim',
                'adresse' => 'Rue Abou El Kacem Chebbi, Cité Ennassim, La Manouba',
                'latitude' => 36.826806,
                'longitude' => 10.091018,
            ],
            [
                'nom' => 'Parc Errafaha',
                'adresse' => 'Jardins d\'El Menzah 2, Errafaha, Ariana',
                'latitude' => 36.848346,
                'longitude' => 10.117883,
            ],
            [
                'nom' => 'Parc Casino',
                'adresse' => 'Rue de l\'Avenir, La Goulette Casino, Tunis',
                'latitude' => 36.821840,
                'longitude' => 10.306906,
            ],
        ],
        'Salle climatisée' => [
            [
                'nom' => 'Centre Commercial Lafayette',
                'adresse' => 'Rue du Pakistan, Lafayette, Tunis',
                'latitude' => 36.812394,
                'longitude' => 10.181076,
            ],
            [
                'nom' => 'Taraji',
                'adresse' => 'Rue du Royaume d\'Arabie Saoudite, Montplaisir, Tunis',
                'latitude' => 36.815581,
                'longitude' => 10.186456,
            ],
            [
                'nom' => 'L\'Émeraude Tunis',
                'adresse' => 'Rue du Japon, Montplaisir, Tunis',
                'latitude' => 36.819813,
                'longitude' => 10.191487,
            ],
            [
                'nom' => 'Centre Commerciale Menzah 6',
                'adresse' => 'Rue du Marché, El Menzah 6, Tunis',
                'latitude' => 36.847088,
                'longitude' => 10.166299,
            ],
            [
                'nom' => 'Manar City',
                'adresse' => 'Avenue du Roi Abdelaziz El Saoud, El Manar 2, Tunis',
                'latitude' => 36.842328,
                'longitude' => 10.163357,
            ],
            [
                'nom' => 'Centre Commercial Azur City',
                'adresse' => 'Autoroute Tunis - Sousse - Sfax, El Bassatine El Gharbya, Ben Arous',
                'latitude' => 36.726076,
                'longitude' => 10.256554,
            ],
            [
                'nom' => 'Tunis City',
                'adresse' => 'RL470, Ennahli, Ariana',
                'latitude' => 36.900277,
                'longitude' => 10.123161,
            ],
            [
                'nom' => 'Médiathèque Ariana',
                'adresse' => 'Place de l\'Indépendance, El Yasmina, Ariana',
                'latitude' => 36.853947,
                'longitude' => 10.196322,
            ],
            [
                'nom' => 'MACAM Tunis',
                'adresse' => 'Rue de Jordanie, Lafayette, Tunis',
                'latitude' => 36.810554,
                'longitude' => 10.186457,
            ],
            [
                'nom' => 'Bibliothèque publique Carthage Byrsa',
                'adresse' => 'Avenue de l\'Indépendance, Carthage Byrsa, Tunis',
                'latitude' => 36.845852,
                'longitude' => 10.320835,
            ],
            [
                'nom' => 'CDI : centre de documentation et d\'information',
                'adresse' => 'RL 451, Mutuelleville, Tunis',
                'latitude' => 36.834355,
                'longitude' => 10.174978,
                'horaires' => 'Lun-Ven 08:00 - 17:00',
            ],
            [
                'nom' => 'Musée océanographique Dar El Hout',
                'adresse' => 'Rue du 2 Mars 1934, Carthage Plage, Tunis',
                'latitude' => 36.843721,
                'longitude' => 10.326288,
                'horaires' => 'Mar-Dim 10:00 - 18:00',
            ],
        ],
        'Plage' => [
            [
                'nom' => 'Plage Amilcar',
                'adresse' => 'Avenue de l\'Union, Carthage Plage, Tunis',
                'latitude' => 36.861652,
                'longitude' => 10.341609,
            ],
            [
                'nom' => 'Plage de Sidi Bou Saïd',
                'adresse' => 'Avenue John Kennedy, Sidi Bou Saïd, Tunis',
                'latitude' => 36.865984,
                'longitude' => 10.347082,
            ],
            [
                'nom' => 'Plage de La Marsa',
                'adresse' => 'Sidi Abdelaziz, La Marsa, Tunis',
                'latitude' => 36.893786,
                'longitude' => 10.325246,
            ],
            [
                'nom' => 'Corniche de La Marsa',
                'adresse' => 'Rue Arbi Kabadi, Marsa Corniche, Tunis',
                'latitude' => 36.883937,
                'longitude' => 10.337597,
            ],
            [
                'nom' => 'Plage du Kram',
                'adresse' => 'Rue Hassen Hosni Abdelwaheb, Le Kram Est, Tunis',
                'latitude' => 36.830759,
                'longitude' => 10.318850,
            ],
            [
                'nom' => 'Plage de Salammbô',
                'adresse' => 'Rue Aristote, Carthage Plage, Tunis',
                'latitude' => 36.836811,
                'longitude' => 10.324580,
            ],
            [
                'nom' => 'Plage de Gomrath',
                'adresse' => 'RR23, La Marsa, Tunis',
                'latitude' => 36.932357,
                'longitude' => 10.273630,
            ],
        ],
    ];

    /**
     * Capacité d'accueil par type de point (nombre de personnes).
     *
     * @var array<string, int>
     */
    private const CAPACITES = [
        'Parc' => 100,
        'Salle climatisée' => 40,
        'Plage' => 150,
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::LIEUX as $typeNom => $lieux) {
            $type = TypePoint::where('nom', $typeNom)->first();

            if ($type === null) {
                $this->command?->warn("Type « {$typeNom} » introuvable, lieux ignorés.");

                continue;
            }

            foreach ($lieux as $lieu) {
                PointFraicheur::updateOrCreate(
                    ['nom' => $lieu['nom'], 'adresse' => $lieu['adresse']],
                    [
                        'type_point_id' => $type->id,
                        'latitude' => $lieu['latitude'],
                        'longitude' => $lieu['longitude'],
                        'horaires' => $lieu['horaires'] ?? null,
                        'accessible' => true,
                        'capacite' => self::CAPACITES[$typeNom] ?? 50,
                    ]
                );
            }
        }
    }
}
