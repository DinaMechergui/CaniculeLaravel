<?php
namespace App\Console\Commands;
use App\Models\AlerteMeteo;
use App\Models\PlanUrgence;
use App\Models\User;
use App\Notifications\RappelPlanIncomplet;
use Illuminate\Console\Command;
class RappelerPlansIncomplets extends Command
{
    protected $signature = 'plans:rappeler-incomplets {--dry-run : Affiche les rappels sans les envoyer}';
    protected $description = 'Rappelle les étapes obligatoires pendant une alerte météo active.';
    public function handle(): int
    {
        if (! AlerteMeteo::active()->exists()) { $this->info('Aucune alerte météo active.'); return self::SUCCESS; }
        $nombre=0;
        PlanUrgence::with('etapes')->where('actif',true)->chunkById(100,function($plans) use (&$nombre) {
            foreach($plans as $plan) {
                $obligatoires=$plan->etapes->where('obligatoire',true);
                if($obligatoires->isEmpty()) continue;
                User::whereHas('etapesFaites',fn($q)=>$q->whereIn('etapes_plan.id',$plan->etapes->pluck('id')))->with('etapesFaites')->chunkById(100,function($users) use($plan,$obligatoires,&$nombre) {
                    foreach($users as $user) {
                        $faites=$user->etapesFaites->pluck('id'); $reste=$obligatoires->whereNotIn('id',$faites);
                        if($reste->isNotEmpty()) { $nombre++; if(!$this->option('dry-run')) $user->notify(new RappelPlanIncomplet($plan->titre,$reste->count())); }
                    }
                });
            }
        });
        $this->info(($this->option('dry-run')?'Rappels à envoyer : ':'Rappels envoyés : ').$nombre);
        return self::SUCCESS;
    }
}
