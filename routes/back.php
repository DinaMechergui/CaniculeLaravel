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
use App\BackOffice\Controllers\PointFraicheurController;
use App\BackOffice\Controllers\TypePointController;
use App\BackOffice\Controllers\UserController;
use App\Http\Controllers\Back\CoupureController;
use App\Http\Controllers\Back\ImpactCoupureController;
use App\Http\Controllers\Back\InterventionController;
use App\Http\Controllers\Back\QuartierController;
use App\Http\Controllers\Back\SmartGridController;
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
    Route::resource('coupures', CoupureController::class);
    Route::resource('interventions', InterventionController::class);
    Route::resource('impacts-coupures', ImpactCoupureController::class)
        ->parameters(['impacts-coupures' => 'impactCoupure']);
    Route::get('/smartgrid', [SmartGridController::class, 'index'])->name('smartgrid.index');
    Route::get('/smartgrid/data', [SmartGridController::class, 'data'])->name('smartgrid.data');
    Route::post('/smartgrid/route', [SmartGridController::class, 'route'])
        ->middleware('throttle:5,1')->name('smartgrid.route');
    Route::post('/smartgrid/analyze-zone', [SmartGridController::class, 'analyzeZone'])
        ->middleware('throttle:3,1')->name('smartgrid.analyze-zone');
    Route::post('/smartgrid/analyze/{coupure}', [SmartGridController::class, 'analyze'])
        ->middleware('throttle:3,1')->name('smartgrid.analyze');
});
