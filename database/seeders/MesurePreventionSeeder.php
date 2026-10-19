<?php

namespace Database\Seeders;

use App\Models\AlerteMeteo;
use App\Models\MesurePrevention;
use Illuminate\Database\Seeder;

class MesurePreventionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Crée 2 à 3 mesures de prévention par alerte météo existante avec des types et statuts variés.
     */
    public function run(): void
    {
        $alertes = AlerteMeteo::all();

        $mesuresCatalogue = [
            [
                'titre' => 'Distribution de bouteilles d\'eau fraîche et sel d\'hydratation',
                'type' => 'distribution_eau',
                'description' => 'Déploiement de tentes de ravitaillement dans les carrefours clés et distribution d\'eau réfrigérée aux citoyens.',
                'responsable' => 'Croissant Rouge Tunisien & SONEDE',
                'statuts' => ['en_cours', 'prevue'],
            ],
            [
                'titre' => 'Fermeture préventive des chantiers de construction (11h - 17h)',
                'type' => 'fermeture_site',
                'description' => 'Arrêt obligatoire des travaux exposés au soleil durant les pics de température pour préserver la santé des ouvriers.',
                'responsable' => 'Inspection du Travail & Municipalité',
                'statuts' => ['en_cours', 'terminee'],
            ],
            [
                'titre' => 'Renfort des postes d\'intervention rapide de la Protection Civile',
                'type' => 'renfort_secours',
                'description' => 'Positionnement stratégique de 4 camions citernes et 2 ambulances équipées pour parer à tout départ d\'incendie ou malaise.',
                'responsable' => 'Protection Civile Tunisienne (ONPC 198)',
                'statuts' => ['en_cours', 'prevue'],
            ],
            [
                'titre' => 'Évacuation préventive des résidents à proximité immédiate des massifs forestiers',
                'type' => 'evacuation',
                'description' => 'Évacuation assistée et mise à l\'abri des familles vulnérables dans le complexe sportif communal climatisé.',
                'responsable' => 'Commission Régionale de Gestion des Catastrophes',
                'statuts' => ['prevue', 'en_cours', 'terminee'],
            ],
            [
                'titre' => 'Ouverture 24h/24 des salles municipales climatisées pour personnes vulnérables',
                'type' => 'fermeture_site',
                'description' => 'Accès libre et gratuit aux salles rafraîchies de la commune avec présence d\'infirmiers et distribution de boissons.',
                'responsable' => 'Services Sociaux Municipaux & SAMU 190',
                'statuts' => ['en_cours', 'prevue'],
            ],
            [
                'titre' => 'Ronde mobile de contrôle sanitaire et réhydratation d\'urgence',
                'type' => 'distribution_eau',
                'description' => 'Équipes itinérantes de secouristes circulant en ville pour apporter de l\'eau et des soins de premier secours.',
                'responsable' => 'Direction Régionale de la Santé Publique',
                'statuts' => ['prevue', 'en_cours'],
            ],
            [
                'titre' => 'Interdiction de baignade et fermeture d\'accès aux digues et jetées',
                'type' => 'fermeture_site',
                'description' => 'Mise en place de barrières de sécurité et surveillance des plages en raison de fortes rafales et d\'une mer démontée.',
                'responsable' => 'Garde Maritime & Protection Civile',
                'statuts' => ['en_cours', 'terminee'],
            ],
        ];

        foreach ($alertes as $alerte) {
            // 2 à 3 mesures par alerte météo
            $nbMesures = rand(2, 3);
            $selectedCatalog = fake()->randomElements($mesuresCatalogue, $nbMesures);

            foreach ($selectedCatalog as $index => $modele) {
                $statut = fake()->randomElement($modele['statuts']);
                $dateDebut = match ($statut) {
                    'prevue' => now()->addHours(rand(2, 24)),
                    'en_cours' => now()->subHours(rand(1, 12)),
                    'terminee' => now()->subDays(rand(1, 3)),
                };

                MesurePrevention::create([
                    'alerte_meteo_id' => $alerte->id,
                    'titre' => \Illuminate\Support\Str::limit($modele['titre'], 145, ''),
                    'type' => $modele['type'],
                    'description' => $modele['description'] . ' Concerne le secteur rattaché à l\'alerte : ' . $alerte->titre . '.',
                    'responsable' => $modele['responsable'],
                    'date_debut' => $dateDebut,
                    'statut' => $statut,
                ]);
            }
        }
    }
}
