<?php

namespace App\BackOffice\Controllers;

use App\BackOffice\Requests\StorePointFraicheurRequest;
use App\BackOffice\Requests\UpdatePointFraicheurRequest;
use App\Http\Controllers\Controller;
use App\Models\PointFraicheur;
use App\Models\TypePoint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestion des points de fraîcheur (back office).
 */
class PointFraicheurController extends Controller
{
    /**
     * Liste paginée des points (+ recherche facultative).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $points = PointFraicheur::query()
            // Chargement du type pour éviter le problème du N+1.
            ->with('type')
            // Recherche sur le nom ou l'adresse.
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                        ->orWhere('adresse', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('back.points-fraicheur.index', [
            'points' => $points,
            'search' => $search,
        ]);
    }

    /**
     * Formulaire de création d'un point de fraîcheur.
     */
    public function create(): View
    {
        return view('back.points-fraicheur.create', [
            'pointFraicheur' => new PointFraicheur(),
            'types' => TypePoint::orderBy('nom')->get(),
        ]);
    }

    /**
     * Enregistrement d'un nouveau point de fraîcheur.
     */
    public function store(StorePointFraicheurRequest $request): RedirectResponse
    {
        PointFraicheur::create($request->validated());

        return redirect()
            ->route('admin.points-fraicheur.index')
            ->with('success', 'Le point de fraîcheur a été créé avec succès.');
    }

    /**
     * Détail d'un point de fraîcheur.
     */
    public function show(PointFraicheur $pointFraicheur): View
    {
        return view('back.points-fraicheur.show', [
            'pointFraicheur' => $pointFraicheur,
        ]);
    }

    /**
     * Formulaire de modification d'un point de fraîcheur.
     */
    public function edit(PointFraicheur $pointFraicheur): View
    {
        return view('back.points-fraicheur.edit', [
            'pointFraicheur' => $pointFraicheur,
            'types' => TypePoint::orderBy('nom')->get(),
        ]);
    }

    /**
     * Mise à jour d'un point de fraîcheur.
     */
    public function update(UpdatePointFraicheurRequest $request, PointFraicheur $pointFraicheur): RedirectResponse
    {
        $pointFraicheur->update($request->validated());

        return redirect()
            ->route('admin.points-fraicheur.index')
            ->with('success', 'Le point de fraîcheur a été modifié avec succès.');
    }

    /**
     * Suppression d'un point de fraîcheur.
     */
    public function destroy(PointFraicheur $pointFraicheur): RedirectResponse
    {
        // Un point rattaché à des réservations (même supprimées) ne peut pas être supprimé.
        if ($pointFraicheur->reservations()->withTrashed()->exists()) {
            return redirect()
                ->route('admin.points-fraicheur.index')
                ->with('error', 'Impossible de supprimer ce point : des réservations y sont rattachées.');
        }

        $pointFraicheur->delete();

        return redirect()
            ->route('admin.points-fraicheur.index')
            ->with('success', 'Le point de fraîcheur a été supprimé avec succès.');
    }
}
