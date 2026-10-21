<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Http\Requests\Back\StoreAlerteRequest;
use App\Http\Requests\Back\UpdateAlerteRequest;
use App\Models\Alerte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlerteController extends Controller
{
    /**
     * Liste des alertes avec filtres et compteurs de bulletins.
     */
    public function index(Request $request): View
    {
        $query = Alerte::with(['bulletins' => fn ($q) => $q->orderBy('date_publication', 'desc')])
            ->withCount('bulletins');

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->query('statut'));
        }

        if ($request->filled('q')) {
            $search = '%' . trim((string) $request->query('q')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', $search)
                    ->orWhere('zone_concernee', 'like', $search)
                    ->orWhere('source', 'like', $search);
            });
        }

        $alertes = $query->latest('date_debut')->paginate(10)->withQueryString();

        return view('back.alertes.index', compact('alertes'));
    }

    /**
     * Formulaire de création d'une nouvelle alerte.
     */
    public function create(): View
    {
        return view('back.alertes.create');
    }

    /**
     * Enregistrement d'une nouvelle alerte.
     */
    public function store(StoreAlerteRequest $request): RedirectResponse
    {
        $alerte = Alerte::create($request->validated());

        return redirect()
            ->route('admin.alertes.show', $alerte)
            ->with('success', 'L\'alerte a été enregistrée avec succès. Vous pouvez maintenant y ajouter un premier bulletin de suivi.');
    }

    /**
     * Fiche détaillée d'une alerte avec sa chronologie complète de bulletins.
     */
    public function show(Alerte $alerte): View
    {
        $alerte->load([
            'bulletins' => fn ($q) => $q->orderBy('date_publication', 'desc'),
            'bulletins.diffusions',
        ]);

        return view('back.alertes.show', compact('alerte'));
    }

    /**
     * Formulaire d'édition d'une alerte.
     */
    public function edit(Alerte $alerte): View
    {
        return view('back.alertes.edit', compact('alerte'));
    }

    /**
     * Mise à jour des informations de l'alerte.
     */
    public function update(UpdateAlerteRequest $request, Alerte $alerte): RedirectResponse
    {
        $alerte->update($request->validated());

        return redirect()
            ->route('admin.alertes.show', $alerte)
            ->with('success', 'L\'alerte a été mise à jour avec succès.');
    }

    /**
     * Suppression d'une alerte (refusée si elle contient des bulletins).
     */
    public function destroy(Alerte $alerte): RedirectResponse
    {
        if ($alerte->bulletins()->exists()) {
            return back()->with('error', 'Impossible de supprimer cette alerte car elle possède un historique de bulletins de suivi.');
        }

        $alerte->delete();

        return redirect()
            ->route('admin.alertes.index')
            ->with('success', 'L\'alerte a été supprimée avec succès.');
    }
}
