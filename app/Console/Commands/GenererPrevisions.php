<?php

namespace App\Console\Commands;

use App\Services\PrevisionService;
use Illuminate\Console\Command;

/**
 * Génère les prévisions de fréquentation des prochains jours pour chaque point
 * de fraîcheur.
 */
class GenererPrevisions extends Command
{
    protected $signature = 'reservations:previsions {--jours=7 : Nombre de jours à prévoir (1 à 30)}';

    protected $description = 'Génère les prévisions de fréquentation des prochains jours';

    public function handle(PrevisionService $service): int
    {
        $jours = (int) $this->option('jours');
        $compte = $service->generer($jours);

        $this->info("{$compte} prévision(s) générée(s) pour les {$jours} prochains jours.");

        return self::SUCCESS;
    }
}
