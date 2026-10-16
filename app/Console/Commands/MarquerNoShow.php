<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Console\Command;

/**
 * Marque « non présenté » (no-show) les réservations confirmées dont le
 * créneau a commencé depuis plus de 30 minutes sans check-in, puis tente de
 * promouvoir la liste d'attente sur les places ainsi libérées.
 */
class MarquerNoShow extends Command
{
    protected $signature = 'reservations:no-show {--delai=30 : Délai de tolérance en minutes}';

    protected $description = 'Marque les réservations confirmées non honorées (no-show)';

    public function __construct(private readonly ReservationService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $delai = (int) $this->option('delai');

        $reservations = Reservation::query()
            ->where('statut', Reservation::STATUT_CONFIRMEE)
            ->whereNull('check_in_le')
            ->get()
            ->filter(function (Reservation $reservation) use ($delai) {
                $debut = $reservation->date_reservation?->copy()->setTimeFromTimeString($reservation->heure_debut_courte);

                return $debut !== null && $debut->addMinutes($delai)->isPast();
            });

        foreach ($reservations as $reservation) {
            $reservation->update(['statut' => Reservation::STATUT_NO_SHOW]);
            $this->service->promouvoirListeAttente($reservation);
        }

        $this->info($reservations->count() . ' réservation(s) marquée(s) non présentée(s).');

        return self::SUCCESS;
    }
}
