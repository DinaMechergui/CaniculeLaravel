<?php

namespace Database\Seeders;

use App\Models\Coupure;
use App\Models\ImpactCoupure;
use Illuminate\Database\Seeder;

class ImpactCoupureSeeder extends Seeder
{
    public function run(): void
    {
        Coupure::query()->each(function (Coupure $coupure): void {
            if ($coupure->impacts()->exists()) {
                return;
            }

            ImpactCoupure::factory()
                ->count(fake()->numberBetween(1, 2))
                ->for($coupure)
                ->create();
        });
    }
}
