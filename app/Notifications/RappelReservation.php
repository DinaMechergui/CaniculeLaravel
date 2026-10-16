<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

/**
 * Rappel envoyé la veille du rendez-vous.
 */
class RappelReservation extends Notification implements ShouldQueue
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
            ->subject('Rappel : votre créneau est prévu demain — ' . $r->code)
            ->greeting('Bonjour ' . $r->nom . ',')
            ->line('Petit rappel : votre réservation est prévue demain.')
            ->line('Code de réservation : ' . $r->code)
            ->line('Lieu : ' . ($r->pointFraicheur?->nom ?? '—'))
            ->line('Date : ' . $r->date_reservation?->format('d/m/Y'))
            ->line('Créneau : ' . $r->heure_debut_courte . ' – ' . $r->heure_fin_courte)
            ->line('Merci de vous présenter à l\'heure, avec votre code ou le billet PDF.')
            ->action('Voir ma réservation', route('reservations.show', $r->code))
            ->salutation('— L\'équipe ' . config('app.name'));
    }
}
