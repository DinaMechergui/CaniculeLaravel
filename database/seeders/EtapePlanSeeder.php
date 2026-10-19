<?php
namespace Database\Seeders;
use App\Models\PlanUrgence;
use Illuminate\Database\Seeder;
class EtapePlanSeeder extends Seeder
{
    public function run(): void
    {
        $base=[['avant','Repérer une pièce fraîche','Identifiez un espace ombragé et ventilé.',true],['avant','Préparer de l’eau','Gardez de l’eau potable à portée de main.',true],['avant','Vérifier les contacts utiles','Notez les numéros des proches et services utiles.',false],['pendant','Boire régulièrement','Proposez de l’eau sans attendre la soif.',true],['pendant','Rafraîchir le logement','Fermez les volets le jour et aérez la nuit.',true],['pendant','Surveiller les signes de malaise','Appelez les secours si l’état se dégrade.',true],['apres','Réapprovisionner les réserves','Remplacez l’eau et les fournitures utilisées.',false]];
        foreach(PlanUrgence::all() as $plan) if($plan->etapes()->doesntExist()) foreach($base as $i=>[$moment,$titre,$description,$obligatoire]) $plan->etapes()->create(compact('moment','titre','description','obligatoire')+['ordre'=>$i+1]);
    }
}
