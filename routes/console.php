<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Ordonnanceur (scheduler) — module Réservations
|--------------------------------------------------------------------------
| Ces tâches s'exécutent via « php artisan schedule:run » (cron / tâche
| planifiée) et reprennent toutes les règles métier côté serveur.
*/

// Expiration des réservations en attente dont le créneau est passé.
Schedule::command('reservations:expirer')->everyFifteenMinutes();

// Marquage « non présenté » des réservations confirmées non honorées.
Schedule::command('reservations:no-show')->everyThirtyMinutes();

// Rappels par e-mail la veille des réservations confirmées.
Schedule::command('reservations:rappels')->hourly();

// Prévisions de fréquentation et indicateurs (IA / analyse locale) du jour.
Schedule::command('reservations:previsions')->dailyAt('05:00');
Schedule::command('reservations:dashboard')->dailyAt('23:30');
