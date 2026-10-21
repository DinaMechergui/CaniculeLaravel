<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Http\Requests\Back\StoreDiffusionRequest;
use App\Http\Requests\Back\UpdateDiffusionRequest;
use App\Models\BulletinAlerte;
use App\Models\Diffusion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiffusionController extends Controller
{
    /**
     * Liste des diffusions de bulletins avec filtres par canal et statut.
     */
    public function index(Request $request): View
    {
        $query = Diffusion::with(['bulletinAlerte.alerte']);

        if ($request->filled('canal')) {
            $query->where('canal', $request->query('canal'));
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->query('statut'));
        }

        if ($request->filled('bulletin_alerte_id')) {
            $query->where('bulletin_alerte_id', $request->query('bulletin_alerte_id'));
        }

        $totalDestinatairesEnvoyes = (clone $query)
            ->where('statut', 'envoyee')
            ->sum('destinataires_estimes');

        $diffusions = $query->latest('date_envoi')->paginate(10)->withQueryString();

        return view('back.diffusions.index', compact('diffusions', 'totalDestinatairesEnvoyes'));
    }

    /**
     * Formulaire de création d'une nouvelle diffusion de bulletin.
     */
    public function create(Request $request): View
    {
        $bulletins = BulletinAlerte::with('alerte')
            ->latest('date_publication')
            ->get();

        $selectedBulletinId = $request->query('bulletin_alerte_id');

        return view('back.diffusions.create', compact('bulletins', 'selectedBulletinId'));
    }

    /**
     * Enregistrement d'une nouvelle diffusion.
     */
    public function store(StoreDiffusionRequest $request): RedirectResponse
    {
        $diffusion = Diffusion::create($request->validated());

        return redirect()
            ->route('admin.diffusions.show', $diffusion)
            ->with('success', 'La diffusion du bulletin a été programmée / enregistrée avec succès.');
    }

    /**
     * Fiche détaillée d'une diffusion.
     */
    public function show(Diffusion $diffusion): View
    {
        $diffusion->load(['bulletinAlerte.alerte']);

        return view('back.diffusions.show', compact('diffusion'));
    }

    /**
     * Formulaire d'édition d'une diffusion.
     */
    public function edit(Diffusion $diffusion): View
    {
        $bulletins = BulletinAlerte::with('alerte')
            ->latest('date_publication')
            ->get();

        return view('back.diffusions.edit', compact('diffusion', 'bulletins'));
    }

    /**
     * Mise à jour d'une diffusion.
     */
    public function update(UpdateDiffusionRequest $request, Diffusion $diffusion): RedirectResponse
    {
        $diffusion->update($request->validated());

        return redirect()
            ->route('admin.diffusions.show', $diffusion)
            ->with('success', 'La diffusion a été mise à jour avec succès.');
    }

    /**
     * Suppression d'une diffusion.
     */
    public function destroy(Diffusion $diffusion): RedirectResponse
    {
        $diffusion->delete();

        return redirect()
            ->route('admin.diffusions.index')
            ->with('success', 'La diffusion a été supprimée avec succès.');
    }
}
