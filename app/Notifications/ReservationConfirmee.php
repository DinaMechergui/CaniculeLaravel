<?php

namespace App\Notifications;

use App\Models\Reservation;
use App\Services\ReservationAiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Confirmation de réservation, envoyée par e-mail avec le billet PDF en pièce
 * jointe. Mise en file d'attente (queue) pour ne pas bloquer le client.
 */
class ReservationConfirmee extends Notification implements ShouldQueue
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

        // Phrase personnalisée par l'IA (repli local si indisponible).
        $phrasePersonnalisee = app(ReservationAiService::class)->personnaliserEmail($r);

        $message = (new MailMessage())
            ->subject('Votre réservation ' . $r->code . ' est confirmée')
            ->greeting('Bonjour ' . $r->nom . ',')
            ->line($phrasePersonnalisee)
            ->line('Code de réservation : ' . $r->code)
            ->line('Lieu : ' . ($r->pointFraicheur?->nom ?? '—'))
            ->line('Date : ' . $r->date_reservation?->format('d/m/Y'))
            ->line('Créneau : ' . $r->heure_debut_courte . ' – ' . $r->heure_fin_courte)
            ->line('Nombre de personnes : ' . $r->nombre_personnes)
            ->action('Voir ma réservation', route('reservations.show', $r->code))
            ->line('Présentez le code ou le billet PDF ci-joint à votre arrivée.')
            ->salutation('— L\'équipe ' . config('app.name'));

        // Le billet PDF est généré à l'envoi. En cas d'échec (extensions
        // manquantes sur l'hôte), l'e-mail est tout de même envoyé.
        try {
            $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(130)
                ->margin(1)
                ->generate(route('reservations.show', $r->code));

            $pdf = Pdf::loadView('emails.reservations.ticket-pdf', [
                'reservation' => $r,
                'qrSvg' => $qrSvg,
            ])->output();

            $message->attachData($pdf, 'reservation-' . $r->code . '.pdf', ['mime' => 'application/pdf']);
        } catch (\Throwable $e) {
            Log::warning('Génération du billet PDF impossible : ' . $e->getMessage());
        }

        return $message;
    }
}
