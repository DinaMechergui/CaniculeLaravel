<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Notifications\RappelReservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * Envoie un rappel par e-mail la veille des réservations confirmées.
 *
 * La colonne « rappel_envoye_le » garantit qu'un client n'est prévenu qu'une
 * seule fois, même si la commande est exécutée plusieurs fois dans la journée.
 */
class EnvoyerRappelsReservations extends Command
{
    protected $signature = 'reservations:rappels';

    protected $description = 'Envoie un rappel la veille des réservations confirmées';

    public function handle(): int
    {
        $demain = now()->addDay()->toDateString();

        $reservations = Reservation::query()
            ->where('statut', Reservation::STATUT_CONFIRMEE)
            ->whereDate('date_reservation', $demain)
            ->whereNull('rappel_envoye_le')
            ->get();

        foreach ($reservations as $reservation) {
            Notification::route('mail', $reservation->email)
                ->notify(new RappelReservation($reservation));

            $reservation->update(['rappel_envoye_le' => now()]);
        }

        $this->info($reservations->count() . ' rappel(s) envoyé(s).');

        return self::SUCCESS;
    }
}
