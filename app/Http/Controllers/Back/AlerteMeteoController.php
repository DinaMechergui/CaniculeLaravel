<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Http\Requests\Back\StoreAlerteMeteoRequest;
use App\Http\Requests\Back\UpdateAlerteMeteoRequest;
use App\Models\AlerteMeteo;
use App\Models\NiveauAlerte;
use App\Services\AlerteAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlerteMeteoController extends Controller
{
    /**
     * Liste paginée des alertes météo (+ filtres facultatifs).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $niveauId = $request->query('niveau_alerte_id');

        $alertesMeteo = AlerteMeteo::query()
            ->with('niveauAlerte')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('titre', 'like', "%{$search}%")
                        ->orWhere('source', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->when($niveauId, function ($query, $niveauId) {
                $query->where('niveau_alerte_id', $niveauId);
            })
            ->latest('date_debut')
            ->paginate(10)
            ->withQueryString();

        $niveauxAlerte = NiveauAlerte::orderBy('niveau', 'asc')->get();

        return view('back.alertes-meteo.index', [
            'alertesMeteo' => $alertesMeteo,
            'niveauxAlerte' => $niveauxAlerte,
            'search' => $search,
            'niveauId' => $niveauId,
        ]);
    }

    /**
     * Formulaire de création d'une alerte météo.
     */
    public function create(): View
    {
        return view('back.alertes-meteo.create', [
            'alerteMeteo' => new AlerteMeteo(),
            'niveauxAlerte' => NiveauAlerte::orderBy('niveau', 'asc')->get(),
        ]);
    }

    /**
     * Enregistrement d'une nouvelle alerte météo.
     */
    public function store(StoreAlerteMeteoRequest $request): RedirectResponse
    {
        AlerteMeteo::create($request->validated());

        return redirect()
            ->route('admin.alertes-meteo.index')
            ->with('success', 'L\'alerte météo a été créée avec succès.');
    }

    /**
     * Affichage détaillé d'une alerte météo.
     */
    public function show(AlerteMeteo $alerteMeteo): View
    {
        $alerteMeteo->load(['niveauAlerte', 'mesures']);

        return view('back.alertes-meteo.show', [
            'alerteMeteo' => $alerteMeteo,
        ]);
    }

    /**
     * Formulaire d'édition d'une alerte météo.
     */
    public function edit(AlerteMeteo $alerteMeteo): View
    {
        return view('back.alertes-meteo.edit', [
            'alerteMeteo' => $alerteMeteo,
            'niveauxAlerte' => NiveauAlerte::orderBy('niveau', 'asc')->get(),
        ]);
    }

    /**
     * Mise à jour d'une alerte météo.
     */
    public function update(UpdateAlerteMeteoRequest $request, AlerteMeteo $alerteMeteo): RedirectResponse
    {
        $alerteMeteo->update($request->validated());

        return redirect()
            ->route('admin.alertes-meteo.index')
            ->with('success', 'L\'alerte météo a été modifiée avec succès.');
    }

    /**
     * Suppression d'une alerte météo.
     * Refus de suppression si des mesures de prévention y sont encore rattachées.
     */
    public function destroy(AlerteMeteo $alerteMeteo): RedirectResponse
    {
        if ($alerteMeteo->mesures()->exists()) {
            return redirect()
                ->route('admin.alertes-meteo.index')
                ->with('error', 'Impossible de supprimer cette alerte météo : des mesures de prévention y sont encore rattachées.');
        }

        $alerteMeteo->delete();

        return redirect()
            ->route('admin.alertes-meteo.index')
            ->with('success', 'L\'alerte météo a été supprimée avec succès.');
    }

    /**
     * Génération de bulletin et recommandation de niveau par IA.
     */
    public function aiGenerate(Request $request, AlerteAiService $aiService): JsonResponse
    {
        $request->validate([
            'prompt' => ['required', 'string', 'max:500'],
        ]);

        $result = $aiService->generateAlert($request->input('prompt'));

        return response()->json($result);
    }
}
