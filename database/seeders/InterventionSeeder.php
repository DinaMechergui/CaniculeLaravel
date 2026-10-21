<?php

namespace Database\Seeders;

use App\Models\Coupure;
use App\Models\Intervention;
use Illuminate\Database\Seeder;

class InterventionSeeder extends Seeder
{
    public function run(): void
    {
        Coupure::query()->each(function (Coupure $coupure): void {
            if ($coupure->interventions()->exists()) {
                return;
            }

            Intervention::factory()
                ->count(fake()->numberBetween(1, 3))
                ->for($coupure)
                ->create();
        });
    }
}
