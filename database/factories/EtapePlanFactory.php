<?php
namespace Database\Factories;
use App\Models\EtapePlan;
use App\Models\PlanUrgence;
use Illuminate\Database\Eloquent\Factories\Factory;
class EtapePlanFactory extends Factory
{
    protected $model=EtapePlan::class;
    public function definition(): array { return ['plan_urgence_id'=>PlanUrgence::factory(),'titre'=>$this->faker->sentence(),'description'=>$this->faker->sentence(),'moment'=>'avant','ordre'=>1,'obligatoire'=>false]; }
}
