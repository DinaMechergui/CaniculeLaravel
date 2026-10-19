<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

/**
 * Information du client placé sur liste d'attente (créneau complet).
 */
class ReservationListeAttente extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Reservation $reservation)
    {
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

        return (new MailMessage())
            ->subject('Vous êtes sur la liste d\'attente — ' . $r->code)
            ->greeting('Bonjour ' . $r->nom . ',')
            ->line('Le créneau demandé est actuellement complet. Votre demande a été placée sur liste d\'attente.')
            ->line('Code de réservation : ' . $r->code)
            ->line('Lieu : ' . ($r->pointFraicheur?->nom ?? '—'))
            ->line('Date : ' . $r->date_reservation?->format('d/m/Y'))
            ->line('Créneau : ' . $r->heure_debut_courte . ' – ' . $r->heure_fin_courte)
            ->line('Nous vous préviendrons par e-mail dès qu\'une place se libère.')
            ->action('Suivre ma réservation', route('reservations.show', $r->code))
            ->salutation('— L\'équipe ' . config('app.name'));
    }
}
