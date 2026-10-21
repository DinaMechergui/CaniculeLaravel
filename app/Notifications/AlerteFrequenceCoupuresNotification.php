<?php

namespace App\Notifications;

use App\Models\Quartier;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlerteFrequenceCoupuresNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Quartier $quartier,
        public int $count,
        public int $seuil = 3
    ) {}

    /**
     * Canaux de notification : Base de données + Mail (si configuré).
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Envoi par email si l'adresse est valide et le mailer configuré
        if (!empty($notifiable->email) && config('mail.default') !== 'null') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Représentation stockée dans la table notifications.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'titre'        => "Alerte Fréquence Élevée : {$this->quartier->nom}",
            'message'      => "Le quartier {$this->quartier->nom} ({$this->quartier->ville}) a cumulé {$this->count} coupures d'électricité (seuil : {$this->seuil}). Une vérification réseau urgente est préconisée.",
            'quartier_id'  => $this->quartier->id,
            'quartier_nom' => $this->quartier->nom,
            'ville'        => $this->quartier->ville,
            'count'        => $this->count,
            'seuil'        => $this->seuil,
            'type'         => 'frequence_coupures',
            'created_at'   => now()->toIso8601String(),
        ];
    }

    /**
     * Version Email pour l'administrateur.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('admin.coupures.index', ['quartier_id' => $this->quartier->id]);

        return (new MailMessage)
            ->error()
            ->subject("🚨 Alerte Admin : Forte concentration de coupures à {$this->quartier->nom}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Une récurrence anormale de coupures d'électricité a été détectée sur le réseau.")
            ->line("**Quartier concerné :** {$this->quartier->nom} ({$this->quartier->ville})")
            ->line("**Nombre de coupures enregistrées :** {$this->count} (seuil d'alerte : {$this->seuil})")
            ->action('Gérer les coupures du quartier', $url)
            ->line("Veuillez alerter les services de maintenance ou la permanence STEG.");
    }

    /**
     * Données par défaut du tableau.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
