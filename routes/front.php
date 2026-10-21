<?php

/*
|--------------------------------------------------------------------------
| Front office — site public et espace utilisateur
|--------------------------------------------------------------------------
|
| Toutes les routes accessibles aux visiteurs (et à l'utilisateur connecté)
| sont déclarées ici : page d'accueil, profil, module public à venir, ainsi
| que l'authentification (fichier auth.php).
|
*/

use App\FrontOffice\Controllers\HomeController;
use App\FrontOffice\Controllers\CoupureController;
use App\FrontOffice\Controllers\PointFraicheurController;
use App\FrontOffice\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Page d'accueil.
Route::get('/', [HomeController::class, 'index'])->name('front.home');

// Module « Points de fraîcheur » : pages publiques.
Route::get('/points-fraicheur', [PointFraicheurController::class, 'index'])
    ->name('points-fraicheur.index');
// Carte interactive et points proches : déclarées AVANT la route avec
// paramètre {pointFraicheur} pour éviter tout conflit d'URL.
Route::get('/points-fraicheur/carte', [PointFraicheurController::class, 'carte'])
    ->name('points-fraicheur.carte');
Route::get('/points-fraicheur/proches', [PointFraicheurController::class, 'proches'])
    ->name('points-fraicheur.proches');
Route::get('/points-fraicheur/{pointFraicheur}', [PointFraicheurController::class, 'show'])
    ->name('points-fraicheur.show');

// Module « Coupures » : pages publiques.
Route::get('/coupures/carte', [CoupureController::class, 'map'])->name('coupures.map');
Route::get('/coupures/carte/data', [CoupureController::class, 'mapData'])->name('coupures.map.data');
Route::get('/coupures', [CoupureController::class, 'index'])->name('coupures.index');
Route::get('/coupures/{coupure}', [CoupureController::class, 'show'])->name('coupures.show');

// Profil de l'utilisateur connecté.
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Authentification : connexion, inscription, mots de passe, vérification d'e-mail.
require __DIR__.'/auth.php';
