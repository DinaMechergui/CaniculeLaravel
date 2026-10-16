<?php

namespace App\Console\Commands;

use App\Services\DashboardService;
use Illuminate\Console\Command;

/**
 * Recalcule les indicateurs (« dashboard ») du module Réservations pour une
 * date (aujourd'hui si aucune date n'est fournie).
 */
class CalculerDashboard extends Command
{
    protected $signature = 'reservations:dashboard {--date= : Date au format AAAA-MM-JJ (défaut : aujourd\'hui)}';

    protected $description = 'Calcule les indicateurs du module Réservations pour une date';

    public function handle(DashboardService $service): int
    {
        $date = (string) ($this->option('date') ?: now()->toDateString());

        $service->snapshot($date);

        $this->info("Indicateurs recalculés pour le {$date}.");

        return self::SUCCESS;
    }
}
