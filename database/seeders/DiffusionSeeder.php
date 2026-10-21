<?php

namespace Database\Seeders;

use App\Models\BulletinAlerte;
use App\Models\Diffusion;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DiffusionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bulletins = BulletinAlerte::with('alerte')->get();

        $canalTemplates = [
            'sms' => [
                'prefix' => '[ALERTE DEVSPARK] ',
                'destinataires' => [15000, 45000],
            ],
            'notification_push' => [
                'prefix' => '⚠️ Notification Urgente : ',
                'destinataires' => [35000, 85000],
            ],
            'email' => [
                'prefix' => 'Bulletin d\'information municipale : ',
                'destinataires' => [20000, 60000],
            ],
            'sirene' => [
                'prefix' => 'Signal sonore municipal déclenché pour : ',
                'destinataires' => [5000, 15000],
            ],
            'affichage' => [
                'prefix' => 'Panneaux urbains : ',
                'destinataires' => [25000, 70000],
            ],
        ];

        foreach ($bulletins as $bulletin) {
            $isRouge = $bulletin->niveau === 'rouge';
            $isTermine = $bulletin->evolution === 'levee';

            // Pour une alerte rouge, on utilise davantage de canaux critiques (sirène, SMS, push)
            $availableCanaux = ['sms', 'notification_push', 'email', 'affichage'];
            if ($isRouge) {
                $availableCanaux[] = 'sirene';
            }

            // Choisir 1 à 3 canaux
            $count = $isRouge ? 3 : rand(1, 2);
            shuffle($availableCanaux);
            $selectedCanaux = array_slice($availableCanaux, 0, $count);

            foreach ($selectedCanaux as $idx => $canal) {
                $config = $canalTemplates[$canal];
                $dEstimes = rand($config['destinataires'][0], $config['destinataires'][1]);

                // Statut basé sur la date de publication
                if ($bulletin->date_publication->isPast()) {
                    // La plupart sont envoyées, un petit nombre en échec pour réalisme
                    $statut = ($idx === 0) ? 'envoyee' : (rand(1, 10) === 1 ? 'echec' : 'envoyee');
                    $dateEnvoi = $bulletin->date_publication->copy()->addMinutes(rand(5, 30));
                } else {
                    $statut = 'planifiee';
                    $dateEnvoi = $bulletin->date_publication->copy()->addMinutes(15);
                }

                $msgBase = $config['prefix'] . $bulletin->alerte->zone_concernee . ' - ' . $bulletin->niveau_label . '. ' . $bulletin->message;
                $contenu = mb_substr($msgBase, 0, 160);

                Diffusion::create([
                    'bulletin_alerte_id' => $bulletin->id,
                    'canal' => $canal,
                    'contenu' => $contenu,
                    'destinataires_estimes' => $dEstimes,
                    'date_envoi' => $dateEnvoi,
                    'statut' => $statut,
                ]);
            }
        }
    }
}
