<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
class ProfilFoyerSeeder extends Seeder
{
    public function run(): void { $user=User::where('email','user@example.com')->first(); if($user) $user->profilFoyer()->firstOrCreate([],['nb_adultes'=>2,'nb_enfants'=>1,'nb_personnes_agees'=>0,'a_animaux'=>false,'type_logement'=>'appartement']); }
}
