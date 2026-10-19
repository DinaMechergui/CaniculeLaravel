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
use App\Http\Controllers\Back\MesurePreventionController;
use App\Http\Controllers\Back\NiveauAlerteController;
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

    // Module « Coupures » : les quartiers (parent) et les coupures (enfant).
    Route::resource('quartiers', QuartierController::class);
    Route::post('coupures/predire-fin', [CoupureController::class, 'predireFin'])
        ->name('coupures.predire-fin');
    Route::post('notifications/{id}/mark-as-read', [CoupureController::class, 'markNotificationAsRead'])
        ->name('notifications.mark-as-read');
    Route::resource('coupures', CoupureController::class);

    // Module « Alertes météo » : les niveaux d'alerte (parent).
    Route::resource('niveaux-alerte', NiveauAlerteController::class)
        ->parameters(['niveaux-alerte' => 'niveau_alerte']);

    // Module « Alertes météo » : les alertes météo (enfant).
    Route::post('alertes-meteo/ai-generate', [BackAlerteMeteoController::class, 'aiGenerate'])
        ->name('alertes-meteo.ai-generate');
    Route::resource('alertes-meteo', BackAlerteMeteoController::class)
        ->parameters(['alertes-meteo' => 'alerte_meteo']);

    // Module « Mesures de prévention » (lié aux alertes météo).
    Route::resource('mesures-prevention', MesurePreventionController::class)
        ->parameters(['mesures-prevention' => 'mesure_prevention']);

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
