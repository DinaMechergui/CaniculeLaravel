<?php
namespace Database\Factories;
use App\Models\ProfilFoyer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
class ProfilFoyerFactory extends Factory
{
    protected $model=ProfilFoyer::class;
    public function definition(): array { return ['user_id'=>User::factory(),'nb_adultes'=>2,'nb_enfants'=>0,'nb_personnes_agees'=>0,'a_animaux'=>false,'type_logement'=>'appartement']; }
}
