<?php

namespace App\BackOffice\Controllers;

use App\BackOffice\Requests\StoreSignalementRequest;
use App\BackOffice\Requests\UpdateSignalementRequest;
use App\Http\Controllers\Controller;
use App\Models\Signalement;
use App\Models\TypeSignalement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Gestion des signalements (back office).
 */
class SignalementController extends Controller
{
    /**
     * Liste paginée des signalements.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $statut = $request->query('statut');

        $signalements = Signalement::query()
            ->with(['typeSignalement', 'user'])
            ->when($search, function ($query, $search) {
                $query->where('description', 'like', "%{$search}%");
            })
            ->when($statut, function ($query, $statut) {
                $query->where('statut', $statut);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('back.signalements.index', [
            'signalements' => $signalements,
            'search'       => $search,
            'statut'       => $statut,
            'statuts'      => Signalement::STATUTS,
        ]);
    }

    /**
     * Formulaire de création d'un signalement.
     */
    public function create(): View
    {
        return view('back.signalements.create', [
            'signalement'      => new Signalement(),
            'typesSignalement' => TypeSignalement::orderBy('libelle')->get(),
            'statuts'          => Signalement::STATUTS,
        ]);
    }

    /**
     * Enregistrement d'un nouveau signalement.
     * Le user_id est automatiquement rempli avec l'utilisateur connecté.
     */
    public function store(StoreSignalementRequest $request, \App\Services\SignalementPriorityService $priorityService): RedirectResponse
    {
        $priorite = $priorityService->determinePriority($request->description);

        Signalement::create(array_merge($request->validated(), [
            'user_id'  => Auth::id(),
            'priorite' => $priorite,
        ]));

        return redirect()
            ->route('admin.signalements.index')
            ->with('success', 'Le signalement a été créé avec succès.');
    }

    /**
     * Détail d'un signalement.
     */
    public function show(Signalement $signalement): View
    {
        return view('back.signalements.show', [
            'signalement' => $signalement->load(['typeSignalement', 'user', 'diagnostic']),
        ]);
    }

    /**
     * Formulaire de modification d'un signalement.
     */
    public function edit(Signalement $signalement): View
    {
        return view('back.signalements.edit', [
            'signalement'      => $signalement,
            'typesSignalement' => TypeSignalement::orderBy('libelle')->get(),
            'statuts'          => Signalement::STATUTS,
        ]);
    }

    /**
     * Mise à jour d'un signalement.
     */
    public function update(UpdateSignalementRequest $request, Signalement $signalement): RedirectResponse
    {
        $signalement->update($request->validated());

        return redirect()
            ->route('admin.signalements.index')
            ->with('success', 'Le signalement a été modifié avec succès.');
    }

    /**
     * Suppression d'un signalement.
     */
    public function destroy(Signalement $signalement): RedirectResponse
    {
        $signalement->delete();

        return redirect()
            ->route('admin.signalements.index')
            ->with('success', 'Le signalement a été supprimé avec succès.');
    }
}
