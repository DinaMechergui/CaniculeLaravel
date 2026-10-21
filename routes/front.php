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

use App\FrontOffice\Controllers\AssistantController;
use App\FrontOffice\Controllers\HomeController;
use App\FrontOffice\Controllers\CoupureController;
use App\FrontOffice\Controllers\CampagneConseilController;
use App\FrontOffice\Controllers\PointFraicheurController;
use App\FrontOffice\Controllers\ProfileController;
use App\FrontOffice\Controllers\SignalementController;
use App\Http\Controllers\Front\AlerteController as FrontAlerteController;
use App\Http\Controllers\Front\ReservationController as FrontReservationController;
use Illuminate\Support\Facades\Route;
use App\FrontOffice\Controllers\ConseilController;
use App\FrontOffice\Controllers\PlanUrgenceController;
use App\FrontOffice\Controllers\PlanAiController;

// Page d'accueil.
Route::get('/', [HomeController::class, 'index'])->name('front.home');

// Assistant IA (chatbot) : page publique + point d'entrée AJAX.
Route::get('/assistant', [AssistantController::class, 'index'])->name('assistant.index');
Route::post('/assistant', [AssistantController::class, 'chat'])->name('assistant.chat');

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

// Module « Réservations » : le client réserve un créneau depuis la fiche du
// point de fraîcheur (création), puis suit sa réservation via son code.
Route::get('/points-fraicheur/{pointFraicheur}/reserver', [FrontReservationController::class, 'create'])
    ->name('reservations.create');
// Créneaux disponibles d'une journée (JSON, appelé en AJAX par le formulaire).
Route::get('/points-fraicheur/{pointFraicheur}/creneaux', [FrontReservationController::class, 'creneaux'])
    ->name('reservations.creneaux');
Route::post('/reservations', [FrontReservationController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('reservations.store');
// Assistant de réservation en langage naturel (AJAX).
Route::post('/reservations/assistant', [FrontReservationController::class, 'assistant'])
    ->middleware('throttle:20,1')
    ->name('reservations.assistant');
// Recherche d'une réservation par code + e-mail (déclarée avant la route
// avec paramètre {code} pour éviter toute collision d'URL).
Route::get('/reservations/recherche', [FrontReservationController::class, 'rechercheForm'])
    ->name('reservations.recherche');
Route::post('/reservations/recherche', [FrontReservationController::class, 'recherche'])
    ->name('reservations.recherche.trouver');
// La contrainte sur le code évite toute collision avec d'éventuelles autres
// routes « /reservations/.... ».
Route::get('/reservations/{code}', [FrontReservationController::class, 'show'])
    ->where('code', '[A-Za-z0-9]{8}')
    ->name('reservations.show');
// Annulation d'une réservation depuis le front (par son code).
Route::post('/reservations/{code}/annuler', [FrontReservationController::class, 'annuler'])
    ->where('code', '[A-Za-z0-9]{8}')
    ->name('reservations.annuler');
// Validation d'arrivée : page ouverte en scannant le QR code du reçu.
// Le scan marque la réservation comme terminée (check-in) et affiche un reçu.
Route::get('/reservations/{code}/arrivee', [FrontReservationController::class, 'arrivee'])
    ->where('code', '[A-Za-z0-9]{8}')
    ->name('reservations.arrivee');
// QR code (SVG) menant à la page de suivi de la réservation.
Route::get('/reservations/{code}/qr', [FrontReservationController::class, 'qr'])
    ->where('code', '[A-Za-z0-9]{8}')
    ->name('reservations.qr');

// Module « Conseils » : pages publiques.
Route::get('/conseils', [ConseilController::class, 'index'])->name('conseils.index');
Route::get('/conseils/{conseil}', [ConseilController::class, 'show'])->name('conseils.show');

// Module « Conseils » : campagnes de sensibilisation (pages publiques).
Route::get('/campagnes', [CampagneConseilController::class, 'index'])->name('campagnes.index');
Route::get('/campagnes/{campagneConseil}', [CampagneConseilController::class, 'show'])->name('campagnes.show');
Route::get('/plans-urgence', [PlanUrgenceController::class, 'index'])->name('plans-urgence.index');
Route::get('/plans-urgence/{planUrgence}/pdf', [PlanUrgenceController::class, 'pdf'])->name('plans-urgence.pdf');
Route::get('/plans-urgence/{planUrgence}', [PlanUrgenceController::class, 'show'])->name('plans-urgence.show');
Route::post('/plans-urgence/{planUrgence}/ia', [PlanAiController::class, 'transformer'])->middleware('throttle:10,1')->name('plans-urgence.ia.transformer');
Route::middleware('auth')->group(function () {
    Route::post('/plans-urgence/ia/personnalise', [PlanAiController::class, 'personnalise'])->middleware('throttle:10,1')->name('plans-urgence.ia.personnalise');
    Route::get('/mes-plans', [PlanUrgenceController::class, 'mesPlans'])->name('mes-plans.index');
    Route::get('/mon-foyer', [PlanUrgenceController::class, 'foyerEdit'])->name('foyer.edit');
    Route::post('/mon-foyer', [PlanUrgenceController::class, 'foyerUpdate'])->name('foyer.update');
    Route::post('/plans-urgence/cocher', [PlanUrgenceController::class, 'cocher'])->name('plans-urgence.cocher');
    Route::post('/plans-urgence/{planUrgence}/reinitialiser', [PlanUrgenceController::class, 'reinitialiser'])->name('plans-urgence.reinitialiser');
});
// Module « Coupures » : pages publiques.
Route::get('/coupures/carte', [CoupureController::class, 'map'])->name('coupures.map');
Route::get('/coupures/carte/data', [CoupureController::class, 'mapData'])->name('coupures.map.data');
Route::get('/coupures', [CoupureController::class, 'index'])->name('coupures.index');
Route::get('/coupures/{coupure}', [CoupureController::class, 'show'])->name('coupures.show');

// Module « Alertes » : pages publiques.
Route::get('/alertes', [FrontAlerteController::class, 'index'])
    ->name('alertes.index');
Route::get('/alertes/{id}', [FrontAlerteController::class, 'show'])
    ->name('alertes.show');
Route::get('/alertes-meteo', fn () => redirect()->route('alertes.index'))
    ->name('alertes-meteo.index');
Route::get('/alertes-meteo/{id}', fn ($id) => redirect()->route('alertes.show', $id))
    ->name('alertes-meteo.show');

// Module « Signalements » : pages publiques.
Route::get('/signalements', [SignalementController::class, 'index'])
    ->name('signalements.index');

// Création d'un signalement depuis le front (utilisateur connecté).
Route::middleware('auth')->group(function () {
    Route::get('/signalements/creer', [SignalementController::class, 'create'])
        ->name('signalements.create');
    Route::post('/signalements', [SignalementController::class, 'store'])
        ->name('signalements.store');
});

// IMPORTANT : La route avec paramètre {signalement} doit être APRES la route /creer
Route::get('/signalements/{signalement}', [SignalementController::class, 'show'])
    ->name('signalements.show');

// Profil de l'utilisateur connecté.
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Authentification : connexion, inscription, mots de passe, vérification d'e-mail.
require __DIR__.'/auth.php';
