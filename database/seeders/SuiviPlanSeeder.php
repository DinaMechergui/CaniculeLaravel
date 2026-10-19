<?php
namespace Database\Seeders;
use App\Models\PlanUrgence;
use App\Models\User;
use Illuminate\Database\Seeder;
class SuiviPlanSeeder extends Seeder
{
    public function run(): void { $user=User::where('email','user@example.com')->first(); $plan=PlanUrgence::where('actif',true)->first(); if($user && $plan) $user->etapesFaites()->syncWithoutDetaching([$plan->etapes()->orderBy('ordre')->first()->id=>['fait_le'=>now()]]); }
}
