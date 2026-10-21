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
use App\BackOffice\Controllers\DiagnosticController;
use App\BackOffice\Controllers\CategorieConseilController;
use App\BackOffice\Controllers\CampagneConseilController;
use App\BackOffice\Controllers\CampagneAiController;
use App\BackOffice\Controllers\ConseilController;
use App\BackOffice\Controllers\PointFraicheurController;
use App\BackOffice\Controllers\QuartierController;
use App\BackOffice\Controllers\SignalementController;
use App\BackOffice\Controllers\TypePointController;
use App\BackOffice\Controllers\TypeSignalementController;
use App\BackOffice\Controllers\UserController;
use App\BackOffice\Controllers\PlanUrgenceController;
use App\BackOffice\Controllers\PlanAiController;
use App\BackOffice\Controllers\EtapePlanController;
use App\Http\Controllers\Back\AlerteController;
use App\Http\Controllers\Back\BulletinAlerteController;
use App\Http\Controllers\Back\DiffusionController;
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
    Route::post('coupures/predire-fin', [CoupureController::class, 'predireFin'])
        ->name('coupures.predire-fin');
    Route::post('notifications/{id}/mark-as-read', [CoupureController::class, 'markNotificationAsRead'])
        ->name('notifications.mark-as-read');
    Route::resource('coupures', CoupureController::class);

    // Module « Alertes » : cycle de vie d'une alerte (parent Alerte, enfant BulletinAlerte).
    Route::resource('alertes', AlerteController::class);
    Route::resource('bulletins-alerte', BulletinAlerteController::class)
        ->parameters(['bulletins-alerte' => 'bulletinAlerte']);

    // Module complémentaire « Diffusions des bulletins » (lié à BulletinAlerte).
    Route::resource('diffusions', DiffusionController::class);

    // Module « Conseils » : les catégories (parent).
    Route::resource('categories-conseil', CategorieConseilController::class)
        ->parameters(['categories-conseil' => 'categorieConseil']);

    // Module « Conseils » : génération du contenu d'un conseil par IA
    // (appelée en JavaScript, déclarée avant la ressource « conseils »).
    Route::post('conseils/generer', [ConseilController::class, 'generer'])->middleware('throttle:10,1')->name('conseils.generer');

    // Module « Conseils » : les conseils (enfant).
    Route::resource('conseils', ConseilController::class);

         // Module « Conseils » : campagnes de sensibilisation.
    Route::post('campagnes-conseil/ia/selection', [CampagneAiController::class, 'storeSelected'])->name('campagnes-conseil.ia.selection')->middleware('throttle:10,1');
    Route::post('campagnes-conseil/ia', CampagneAiController::class)->name('campagnes-conseil.ia')->middleware('throttle:10,1');
    // Routes spéciales déclarées AVANT la resource pour éviter les collisions.
    Route::get('campagnes-conseil/statistiques', [CampagneConseilController::class, 'statistiques'])
        ->name('campagnes-conseil.statistiques');
    Route::get('campagnes-conseil/export-csv', [CampagneConseilController::class, 'exportCsv'])
        ->name('campagnes-conseil.export-csv');
    Route::post('campagnes-conseil/{campagneConseil}/activer', [CampagneConseilController::class, 'activer'])
        ->name('campagnes-conseil.activer');
    Route::post('campagnes-conseil/{campagneConseil}/terminer', [CampagneConseilController::class, 'terminer'])
        ->name('campagnes-conseil.terminer');
    Route::post('campagnes-conseil/{campagneConseil}/dupliquer', [CampagneConseilController::class, 'dupliquer'])
        ->name('campagnes-conseil.dupliquer');
    Route::resource('campagnes-conseil', CampagneConseilController::class)
        ->parameters(['campagnes-conseil' => 'campagneConseil']);

    Route::post('plans-urgence/{planUrgence}/activer', [PlanUrgenceController::class, 'activer'])->name('plans-urgence.activer');
    Route::post('plans-urgence/{planUrgence}/desactiver', [PlanUrgenceController::class, 'desactiver'])->name('plans-urgence.desactiver');
    Route::post('plans-urgence/{planUrgence}/dupliquer', [PlanUrgenceController::class, 'dupliquer'])->name('plans-urgence.dupliquer');
    Route::post('plans-urgence/ia/creer', [PlanAiController::class, 'create'])->name('plans-urgence.ia.creer')->middleware('throttle:10,1');
    Route::post('plans-urgence/ia', PlanAiController::class)->name('plans-urgence.ia')->middleware('throttle:10,1');
    Route::get('plans-urgence/statistiques', [PlanUrgenceController::class, 'statistiques'])->name('plans-urgence.statistiques');
    Route::post('plans-urgence/{planUrgence}/etapes/{etapePlan}/ordre', [PlanUrgenceController::class, 'reorganiser'])->name('plans-urgence.etapes.ordre');
    Route::post('plans-urgence/{planUrgence}/etapes', [EtapePlanController::class, 'store'])->name('plans-urgence.etapes.store');
    Route::get('etapes-plan/{etapePlan}/edit', [EtapePlanController::class, 'edit'])->name('etapes-plan.edit');
    Route::put('etapes-plan/{etapePlan}', [EtapePlanController::class, 'update'])->name('etapes-plan.update');
    Route::delete('etapes-plan/{etapePlan}', [EtapePlanController::class, 'destroy'])->name('etapes-plan.destroy');
    Route::resource('plans-urgence', PlanUrgenceController::class)->parameters(['plans-urgence' => 'planUrgence']);

    // Module « Signalements » : les types (parent).
    Route::resource('types-signalement', TypeSignalementController::class)
        ->parameters(['types-signalement' => 'typeSignalement']);

    // Module « Signalements » : les signalements (enfant).
    Route::resource('signalements', SignalementController::class);

    // Module « Signalements » : les diagnostics de pannes.
    Route::resource('diagnostics', DiagnosticController::class);
});
