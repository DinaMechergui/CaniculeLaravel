<?php
namespace Database\Seeders;
use App\Models\PlanUrgence;
use Illuminate\Database\Seeder;
class PlanUrgenceSeeder extends Seeder
{
    public function run(): void
    {
        $rows=[
            ['Préparer son foyer à la canicule','canicule','modere','tous'],
            ['Protéger une personne âgée pendant la canicule','canicule','eleve','personnes_agees'],
            ['Protéger les enfants lors des fortes chaleurs','canicule','modere','enfants'],
            ['Gérer une coupure de courant en famille','coupure','eleve','familles'],
            ['Préparer le foyer avec des animaux','les_deux','modere','animaux'],
            ['Canicule et coupure : plan familial','les_deux','eleve','familles'],
        ];
        foreach($rows as [$titre,$type,$niveau,$public]) PlanUrgence::firstOrCreate(['titre'=>$titre],['description'=>'Mesures pratiques pour protéger les personnes du foyer.','type_evenement'=>$type,'niveau_risque'=>$niveau,'public_cible'=>$public,'actif'=>true]);
    }
}
