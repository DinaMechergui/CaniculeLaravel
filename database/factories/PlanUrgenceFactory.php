<?php
namespace Database\Factories;
use App\Models\PlanUrgence;
use Illuminate\Database\Eloquent\Factories\Factory;
class PlanUrgenceFactory extends Factory
{
    protected $model=PlanUrgence::class;
    public function definition(): array { return ['titre'=>$this->faker->sentence(4),'description'=>$this->faker->paragraph(),'type_evenement'=>'canicule','niveau_risque'=>'modere','public_cible'=>'tous','actif'=>false]; }
}
