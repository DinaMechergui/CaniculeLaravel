<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

/**
 * Réponse rédigée par l'administrateur (avec assistance de l'IA puis
 * modifiée par ses soins) envoyée au visiteur par e-mail.
 *
 * Mise en file d'attente comme les autres notifications du module.
 */
class ReservationReponse extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
        public string $reponse,
    ) {
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $r = $this->reservation;

        $message = (new MailMessage())
            ->subject('Réponse à votre réservation '.$r->code)
            ->greeting('Bonjour '.$r->nom.',');

        // Chaque paragraphe du texte de l'admin devient une ligne de l'e-mail.
        foreach (preg_split('/\R{2,}/u', trim($this->reponse)) ?: [] as $paragraphe) {
            if (trim($paragraphe) !== '') {
                $message->line(trim($paragraphe));
            }
        }

        return $message
            ->line('Réservation : '.$r->code.' — '.($r->pointFraicheur?->nom ?? '—'))
            ->line('Date : '.$r->date_reservation?->format('d/m/Y')
                .' de '.$r->heure_debut_courte.' à '.$r->heure_fin_courte)
            ->action('Voir ma réservation', route('reservations.show', $r->code))
            ->salutation('— L\'équipe '.config('app.name'));
    }
}
