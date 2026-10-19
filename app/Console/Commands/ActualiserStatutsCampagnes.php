<?php

namespace App\Console\Commands;

use App\Services\CampagneService;
use Illuminate\Console\Command;

/**
 * Actualise automatiquement les statuts des campagnes de sensibilisation :
 *  - termine les campagnes actives dont la date_fin est passée ;
 *  - active les brouillons à activation_auto dont la date_debut est atteinte.
 */
class ActualiserStatutsCampagnes extends Command
{
    protected $signature = 'campagnes:actualiser-statuts {--dry-run : Simuler sans modifier la base}';

    protected $description = 'Actualise les statuts des campagnes de sensibilisation (activation auto et expiration)';

    public function __construct(private readonly CampagneService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->info('[Mode simulation] Aucune modification ne sera effectuée.');
        }

        $resultat = $this->service->actualiserStatuts($dryRun);

        $mode = $dryRun ? ' (simulation)' : '';

        $this->info("{$resultat['terminees']} campagne(s) terminée(s){$mode}.");
        $this->info("{$resultat['activees']} campagne(s) activée(s){$mode}.");

        return self::SUCCESS;
    }
}
