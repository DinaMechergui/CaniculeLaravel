<?php

namespace App\BackOffice\Controllers;

use App\BackOffice\Requests\StoreTypeSignalementRequest;
use App\BackOffice\Requests\UpdateTypeSignalementRequest;
use App\Http\Controllers\Controller;
use App\Models\TypeSignalement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestion des types de signalements (back office).
 */
class TypeSignalementController extends Controller
{
    /**
     * Liste paginée des types de signalements.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $typesSignalement = TypeSignalement::query()
            ->when($search, function ($query, $search) {
                $query->where('libelle', 'like', "%{$search}%");
            })
            ->withCount('signalements')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('back.types-signalement.index', [
            'typesSignalement' => $typesSignalement,
            'search'           => $search,
        ]);
    }

    /**
     * Formulaire de création d'un type de signalement.
     */
    public function create(): View
    {
        return view('back.types-signalement.create', [
            'typeSignalement' => new TypeSignalement(),
        ]);
    }

    /**
     * Enregistrement d'un nouveau type de signalement.
     */
    public function store(StoreTypeSignalementRequest $request): RedirectResponse
    {
        TypeSignalement::create($request->validated());

        return redirect()
            ->route('admin.types-signalement.index')
            ->with('success', 'Le type de signalement a été créé avec succès.');
    }

    /**
     * Détail d'un type de signalement (avec ses signalements rattachés).
     */
    public function show(TypeSignalement $typeSignalement): View
    {
        return view('back.types-signalement.show', [
            'typeSignalement' => $typeSignalement,
            'signalements'    => $typeSignalement->signalements()->with('user')->latest()->get(),
        ]);
    }

    /**
     * Formulaire de modification d'un type de signalement.
     */
    public function edit(TypeSignalement $typeSignalement): View
    {
        return view('back.types-signalement.edit', [
            'typeSignalement' => $typeSignalement,
        ]);
    }

    /**
     * Mise à jour d'un type de signalement.
     */
    public function update(UpdateTypeSignalementRequest $request, TypeSignalement $typeSignalement): RedirectResponse
    {
        $typeSignalement->update($request->validated());

        return redirect()
            ->route('admin.types-signalement.index')
            ->with('success', 'Le type de signalement a été modifié avec succès.');
    }

    /**
     * Suppression d'un type de signalement (refusée s'il a encore des signalements).
     */
    public function destroy(TypeSignalement $typeSignalement): RedirectResponse
    {
        if ($typeSignalement->signalements()->exists()) {
            return redirect()
                ->route('admin.types-signalement.index')
                ->with('error', 'Impossible de supprimer ce type : des signalements y sont encore rattachés.');
        }

        $typeSignalement->delete();

        return redirect()
            ->route('admin.types-signalement.index')
            ->with('success', 'Le type de signalement a été supprimé avec succès.');
    }
}
