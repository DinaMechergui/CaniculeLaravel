<?php

        /*
        |--------------------------------------------------------------------------
        | Back office — réservé aux administrateurs
        |--------------------------------------------------------------------------
        |
        | Préfixe : /admin — protégé par les middleware « auth » + « admin ».
        |
        | Note : les routes du back office sont préfixées par « admin. » et ne
        | sont jamais utilisées par le front office.
        |
        */

use App\BackOffice\Controllers\DashboardController;
use App\BackOffice\Controllers\CoupureController;
use App\BackOffice\Controllers\CategorieConseilController;
use App\BackOffice\Controllers\ConseilController;
use App\BackOffice\Controllers\PointFraicheurController;
use App\BackOffice\Controllers\QuartierController;
use App\BackOffice\Controllers\SignalementController;
use App\BackOffice\Controllers\TypePointController;
use App\BackOffice\Controllers\TypeSignalementController;
use App\BackOffice\Controllers\UserController;
use App\Http\Controllers\Back\AlerteMeteoController as BackAlerteMeteoController;
use App\Http\Controllers\Back\NiveauAlerteController;
use App\Http\Controllers\Back\ReservationController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Tableau de bord : /admin
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Gestion des utilisateurs (tâche commune à l'équipe).
    Route::resource('users', UserController::class);

    // Module « Points de fraîcheur » : les types de points (parent).
    Route::resource('types-point', TypePointController::class)
        ->parameters(['types-point' => 'typePoint']);

    // Module « Points de fraîcheur » : les points (enfant).
    Route::resource('points-fraicheur', PointFraicheurController::class)
        ->parameters(['points-fraicheur' => 'pointFraicheur']);

    // Module « Réservations » : le back office ne crée pas de réservation
    // (la création se fait en front office, sur la fiche du point). L'admin
    // consulte, modifie (statut, notes), supprime et exporte.
    //
    // IMPORTANT : ces routes sans modèle doivent être déclarées AVANT la
    // ressource pour ne pas être capturées par « reservations/{reservation} ».
    Route::get('reservations/check-in', [ReservationController::class, 'checkInPage'])
        ->name('reservations.check-in');
    Route::post('reservations/check-in', [ReservationController::class, 'checkInStore'])
        ->name('reservations.check-in.store');
    Route::get('reservations/calendrier', [ReservationController::class, 'calendrier'])
        ->name('reservations.calendrier');
    Route::get('reservations/events', [ReservationController::class, 'events'])
        ->name('reservations.events');
    Route::get('reservations/export', [ReservationController::class, 'export'])
        ->name('reservations.export');
    // Analyse IA : prévisions et indicateurs (déclarée avant la ressource).
    Route::get('reservations/analytique', [ReservationController::class, 'analytique'])
        ->name('reservations.analytique');
    Route::post('reservations/analytique/generer', [ReservationController::class, 'genererAnalytique'])
        ->name('reservations.analytique.generer');
    Route::resource('reservations', ReservationController::class)
        ->except(['create', 'store']);
    // Actions rapides en un clic depuis la liste.
    Route::post('reservations/{reservation}/confirmer', [ReservationController::class, 'confirmer'])
        ->name('reservations.confirmer');
    Route::post('reservations/{reservation}/annuler', [ReservationController::class, 'annuler'])
        ->name('reservations.annuler');
    Route::post('reservations/{reservation}/check-in', [ReservationController::class, 'checkIn'])
        ->name('reservations.marquer-check-in');
    // Rédaction assistée par l'IA (AJAX depuis la fiche réservation).
    Route::post('reservations/{reservation}/reponse-ia', [ReservationController::class, 'reponseIa'])
        ->name('reservations.reponse-ia');

    // Module « Coupures » : les quartiers (parent) et les coupures (enfant).
    Route::resource('quartiers', QuartierController::class);
    Route::resource('coupures', CoupureController::class);

    // Module « Alertes météo » : les niveaux d'alerte (parent).
    Route::resource('niveaux-alerte', NiveauAlerteController::class)
        ->parameters(['niveaux-alerte' => 'niveau_alerte']);

    // Module « Alertes météo » : les alertes météo (enfant).
    Route::post('alertes-meteo/ai-generate', [BackAlerteMeteoController::class, 'aiGenerate'])
        ->name('alertes-meteo.ai-generate');
    Route::resource('alertes-meteo', BackAlerteMeteoController::class)
        ->parameters(['alertes-meteo' => 'alerte_meteo']);

    // Module « Conseils » : les catégories (parent).
    Route::resource('categories-conseil', CategorieConseilController::class)
        ->parameters(['categories-conseil' => 'categorieConseil']);

    // Module « Conseils » : génération du contenu d'un conseil par IA
    // (appelée en JavaScript, déclarée avant la ressource « conseils »).
    Route::post('conseils/generer', [ConseilController::class, 'generer'])->name('conseils.generer');

    // Module « Conseils » : les conseils (enfant).
    Route::resource('conseils', ConseilController::class);

    // Module « Signalements » : les types (parent).
    Route::resource('types-signalement', TypeSignalementController::class)
        ->parameters(['types-signalement' => 'typeSignalement']);

    // Module « Signalements » : les signalements (enfant).
    Route::resource('signalements', SignalementController::class);
});
