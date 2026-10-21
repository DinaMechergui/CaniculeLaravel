<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Http\Requests\Back\StoreBulletinAlerteRequest;
use App\Http\Requests\Back\UpdateBulletinAlerteRequest;
use App\Models\Alerte;
use App\Models\BulletinAlerte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BulletinAlerteController extends Controller
{
    /**
     * Liste des bulletins d'alerte avec filtres par niveau et évolution.
     */
    public function index(Request $request): View
    {
        $query = BulletinAlerte::with(['alerte', 'diffusions']);

        if ($request->filled('niveau')) {
            $query->where('niveau', $request->query('niveau'));
        }

        if ($request->filled('evolution')) {
            $query->where('evolution', $request->query('evolution'));
        }

        if ($request->filled('alerte_id')) {
            $query->where('alerte_id', $request->query('alerte_id'));
        }

        $bulletins = $query->latest('date_publication')->paginate(10)->withQueryString();

        return view('back.bulletins-alerte.index', compact('bulletins'));
    }

    /**
     * Formulaire de publication d'un nouveau bulletin de suivi.
     */
    public function create(Request $request): View
    {
        $alertes = Alerte::enCours()->orderBy('titre')->get();
        $selectedAlerteId = $request->query('alerte_id');

        return view('back.bulletins-alerte.create', compact('alertes', 'selectedAlerteId'));
    }

    /**
     * Enregistrement d'un nouveau bulletin avec application des règles métier.
     */
    public function store(StoreBulletinAlerteRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $alerte = Alerte::findOrFail($data['alerte_id']);

        if ($alerte->statut === 'terminee') {
            return back()
                ->with('error', 'Impossible d\'ajouter un bulletin à une alerte déjà terminée.')
                ->withInput();
        }

        $bulletin = BulletinAlerte::create($data);

        // Règle métier : quand un bulletin avec evolution = levee est enregistré,
        // l'alerte passe en statut terminee et sa date_fin est remplie avec la date_publication
        if ($bulletin->evolution === 'levee') {
            $alerte->update([
                'statut' => 'terminee',
                'date_fin' => $bulletin->date_publication ?? now(),
            ]);
        }

        return redirect()
            ->route('admin.alertes.show', $alerte)
            ->with('success', 'Le bulletin de suivi a été publié avec succès.');
    }

    /**
     * Consultation détaillée d'un bulletin et de ses diffusions.
     */
    public function show(BulletinAlerte $bulletinAlerte): View
    {
        $bulletinAlerte->load(['alerte', 'diffusions']);

        return view('back.bulletins-alerte.show', compact('bulletinAlerte'));
    }

    /**
     * Formulaire d'édition d'un bulletin.
     */
    public function edit(BulletinAlerte $bulletinAlerte): View
    {
        $alertes = Alerte::orderBy('titre')->get();

        return view('back.bulletins-alerte.edit', compact('bulletinAlerte', 'alertes'));
    }

    /**
     * Mise à jour d'un bulletin.
     */
    public function update(UpdateBulletinAlerteRequest $request, BulletinAlerte $bulletinAlerte): RedirectResponse
    {
        $data = $request->validated();
        $bulletinAlerte->update($data);

        if ($bulletinAlerte->evolution === 'levee') {
            $bulletinAlerte->alerte->update([
                'statut' => 'terminee',
                'date_fin' => $bulletinAlerte->date_publication ?? now(),
            ]);
        }

        return redirect()
            ->route('admin.bulletins-alerte.show', $bulletinAlerte)
            ->with('success', 'Le bulletin a été mis à jour avec succès.');
    }

    /**
     * Suppression d'un bulletin (refusée si diffusions attachées).
     */
    public function destroy(BulletinAlerte $bulletinAlerte): RedirectResponse
    {
        if ($bulletinAlerte->diffusions()->exists()) {
            return back()->with('error', 'Impossible de supprimer ce bulletin car il possède des canaux de diffusion enregistrés.');
        }

        $alerte = $bulletinAlerte->alerte;
        $bulletinAlerte->delete();

        return redirect()
            ->route('admin.alertes.show', $alerte)
            ->with('success', 'Le bulletin a été supprimé.');
    }
}
