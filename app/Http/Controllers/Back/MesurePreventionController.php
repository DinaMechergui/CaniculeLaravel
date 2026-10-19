<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Http\Requests\Back\StoreMesurePreventionRequest;
use App\Http\Requests\Back\UpdateMesurePreventionRequest;
use App\Models\AlerteMeteo;
use App\Models\MesurePrevention;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MesurePreventionController extends Controller
{
    /**
     * Liste paginée des mesures de prévention avec filtres par type, statut et recherche.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $type = $request->query('type');
        $statut = $request->query('statut');
        $alerteId = $request->query('alerte_meteo_id');

        $mesuresPrevention = MesurePrevention::query()
            ->with(['alerteMeteo.niveauAlerte'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('titre', 'like', "%{$search}%")
                        ->orWhere('responsable', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($statut, function ($query, $statut) {
                $query->where('statut', $statut);
            })
            ->when($alerteId, function ($query, $alerteId) {
                $query->where('alerte_meteo_id', $alerteId);
            })
            ->latest('date_debut')
            ->paginate(10)
            ->withQueryString();

        $alertesMeteo = AlerteMeteo::with('niveauAlerte')->latest('date_debut')->get();

        return view('back.mesures-prevention.index', [
            'mesuresPrevention' => $mesuresPrevention,
            'alertesMeteo' => $alertesMeteo,
            'search' => $search,
            'type' => $type,
            'statut' => $statut,
            'alerteId' => $alerteId,
            'types' => MesurePrevention::TYPES,
            'statuts' => MesurePrevention::STATUTS,
        ]);
    }

    /**
     * Formulaire de création d'une mesure de prévention.
     */
    public function create(Request $request): View
    {
        $mesurePrevention = new MesurePrevention();
        if ($request->filled('alerte_meteo_id')) {
            $mesurePrevention->alerte_meteo_id = $request->query('alerte_meteo_id');
        }

        $alertesMeteo = AlerteMeteo::with('niveauAlerte')->latest('date_debut')->get();

        return view('back.mesures-prevention.create', [
            'mesurePrevention' => $mesurePrevention,
            'alertesMeteo' => $alertesMeteo,
            'types' => MesurePrevention::TYPES,
            'statuts' => MesurePrevention::STATUTS,
        ]);
    }

    /**
     * Enregistrement d'une nouvelle mesure de prévention.
     */
    public function store(StoreMesurePreventionRequest $request): RedirectResponse
    {
        MesurePrevention::create($request->validated());

        return redirect()
            ->route('admin.mesures-prevention.index')
            ->with('success', 'La mesure de prévention a été créée avec succès.');
    }

    /**
     * Affichage détaillé d'une mesure de prévention.
     */
    public function show(MesurePrevention $mesurePrevention): View
    {
        $mesurePrevention->load(['alerteMeteo.niveauAlerte']);

        return view('back.mesures-prevention.show', [
            'mesurePrevention' => $mesurePrevention,
        ]);
    }

    /**
     * Formulaire d'édition d'une mesure de prévention.
     */
    public function edit(MesurePrevention $mesurePrevention): View
    {
        $alertesMeteo = AlerteMeteo::with('niveauAlerte')->latest('date_debut')->get();

        return view('back.mesures-prevention.edit', [
            'mesurePrevention' => $mesurePrevention,
            'alertesMeteo' => $alertesMeteo,
            'types' => MesurePrevention::TYPES,
            'statuts' => MesurePrevention::STATUTS,
        ]);
    }

    /**
     * Mise à jour d'une mesure de prévention.
     */
    public function update(UpdateMesurePreventionRequest $request, MesurePrevention $mesurePrevention): RedirectResponse
    {
        $mesurePrevention->update($request->validated());

        return redirect()
            ->route('admin.mesures-prevention.index')
            ->with('success', 'La mesure de prévention a été modifiée avec succès.');
    }

    /**
     * Suppression d'une mesure de prévention.
     */
    public function destroy(MesurePrevention $mesurePrevention): RedirectResponse
    {
        $mesurePrevention->delete();

        return redirect()
            ->route('admin.mesures-prevention.index')
            ->with('success', 'La mesure de prévention a été supprimée avec succès.');
    }
}
