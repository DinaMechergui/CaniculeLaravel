<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Services\ReservationAiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Analyse IA d'une réservation, exécutée en arrière-plan (non bloquante).
 *
 * L'analyse IA (besoins, priorité, score de non-présentation) ne doit jamais
 * ralentir la confirmation au visiteur : elle est traitée par un job.
 */
class AnalyserReservationIa implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $reservationId)
    {
    }

    public function handle(ReservationAiService $service): void
    {
        $reservation = Reservation::find($this->reservationId);

        if ($reservation === null) {
            return;
        }

        $service->analyser($reservation);
    }
}
