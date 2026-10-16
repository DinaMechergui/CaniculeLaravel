<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;

/**
 * Expire les réservations « en attente » dont le créneau est déjà passé
 * (jamais confirmées administrativement avant l'heure).
 */
class ExpirerReservations extends Command
{
    protected $signature = 'reservations:expirer';

    protected $description = 'Expire les réservations en attente dont le créneau est passé';

    public function handle(): int
    {
        $reservations = Reservation::query()
            ->where('statut', Reservation::STATUT_EN_ATTENTE)
            ->get()
            ->filter(function (Reservation $reservation) {
                $fin = $reservation->date_reservation?->copy()->setTimeFromTimeString($reservation->heure_fin_courte);

                return $fin !== null && $fin->isPast();
            });

        foreach ($reservations as $reservation) {
            $reservation->update(['statut' => Reservation::STATUT_EXPIREE]);
        }

        $this->info($reservations->count() . ' réservation(s) expirée(s).');

        return self::SUCCESS;
    }
}
