<?php

namespace Database\Seeders;

use App\Models\Dashboard;
use App\Services\DashboardService;
use Illuminate\Database\Seeder;

/**
 * Calcule les indicateurs de démonstration des 14 derniers jours (global et
 * par point de fraîcheur) à partir des réservations existantes.
 */
class DashboardSeeder extends Seeder
{
    public function run(): void
    {
        // Ré-exécutable sans recalcul inutile.
        if (Dashboard::query()->exists()) {
            return;
        }

        $service = app(DashboardService::class);

        for ($i = 0; $i < 14; $i++) {
            $service->snapshot(now()->subDays($i)->toDateString());
        }
    }
}
