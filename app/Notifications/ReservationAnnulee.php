<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

/**
 * Confirmation d'annulation envoyée au client.
 */
class ReservationAnnulee extends Notification implements ShouldQueue
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
            ->subject('Annulation de votre réservation — ' . $r->code)
            ->greeting('Bonjour ' . $r->nom . ',')
            ->line('Votre réservation a bien été annulée.')
            ->line('Code de réservation : ' . $r->code)
            ->line('Lieu : ' . ($r->pointFraicheur?->nom ?? '—'))
            ->line('Date : ' . $r->date_reservation?->format('d/m/Y'))
            ->line('Créneau : ' . $r->heure_debut_courte . ' – ' . $r->heure_fin_courte)
            ->line('Vous pouvez réserver un nouveau créneau à tout moment.')
            ->salutation('— L\'équipe ' . config('app.name'));
    }
}
