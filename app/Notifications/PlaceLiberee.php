<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

/**
 * Annonce au client qu'une place s'est libérée et que sa réservation est
 * désormais confirmée (promotion depuis la liste d'attente).
 */
class PlaceLiberee extends Notification implements ShouldQueue
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
            ->subject('Bonne nouvelle : une place s\'est libérée — ' . $r->code)
            ->greeting('Bonjour ' . $r->nom . ',')
            ->line('Une place s\'est libérée sur le créneau que vous attendiez : votre réservation est maintenant confirmée.')
            ->line('Code de réservation : ' . $r->code)
            ->line('Lieu : ' . ($r->pointFraicheur?->nom ?? '—'))
            ->line('Date : ' . $r->date_reservation?->format('d/m/Y'))
            ->line('Créneau : ' . $r->heure_debut_courte . ' – ' . $r->heure_fin_courte)
            ->action('Voir ma réservation', route('reservations.show', $r->code))
            ->salutation('— L\'équipe ' . config('app.name'));
    }
}
