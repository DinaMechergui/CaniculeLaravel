<?php

/*
|--------------------------------------------------------------------------
| Routes de l'application
|--------------------------------------------------------------------------
|
| Le projet est découpé en deux espaces totalement séparés :
|
|   front.php  → front office (public, authentification, espace utilisateur)
|   back.php   → back office (réservé aux administrateurs)
|
| Ce fichier se contente de les charger : les deux fichiers héritent du
| middleware « web » et ne partagent aucune déclaration de route.
|
*/

require __DIR__.'/front.php';
require __DIR__.'/back.php';

// Point de terminaison pour la collecte Prometheus
Route::get('/metrics', \App\Http\Controllers\MetricsController::class)->name('metrics');
