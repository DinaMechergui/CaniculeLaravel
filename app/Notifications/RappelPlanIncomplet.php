<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
class RappelPlanIncomplet extends Notification
{
    use Queueable;
    public function __construct(public string $titrePlan, public int $etapesRestantes) {}
    public function via(object $notifiable): array { return ['database']; }
    public function toArray(object $notifiable): array { return ['message'=>"Pensez à compléter le plan « {$this->titrePlan} » : {$this->etapesRestantes} étape(s) obligatoire(s) restent à faire.",'titre_plan'=>$this->titrePlan,'etapes_restantes'=>$this->etapesRestantes]; }
}
