<?php

namespace App\FrontOffice\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Coupure;
use App\Models\Quartier;
use App\Models\ImpactCoupure;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoupureController extends Controller
{
    public function map(): View
    {
        return view('front.coupures.map', [
            'quartiers' => Quartier::query()->orderBy('nom')->get(['id', 'nom', 'ville']),
        ]);
    }

    public function mapData(Request $request): \Illuminate\Http\JsonResponse
    {
        $filters = $request->validate([
            'quartier_id' => ['nullable', 'integer', 'exists:quartiers,id'],
            'statut' => ['nullable', 'in:prevue,en_cours'],
        ]);

        $coupures = Coupure::query()
            ->with(['quartier', 'interventions', 'impacts'])
            ->whereIn('statut', ['en_cours', 'prevue'])
            ->when($filters['quartier_id'] ?? null, fn ($query, $id) => $query->where('quartier_id', $id))
            ->when($filters['statut'] ?? null, fn ($query, $status) => $query->where('statut', $status))
            ->latest('date_debut')
            ->get();

        $impacts = ImpactCoupure::query()
            ->with('coupure.quartier')
            ->whereIn('coupure_id', $coupures->modelKeys())
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(fn (ImpactCoupure $impact) => [
                'latitude' => (float) $impact->latitude,
                'longitude' => (float) $impact->longitude,
                'nom_structure' => $impact->nom_structure,
                'categorie' => $impact->categorie,
                'coupure' => $impact->coupure?->quartier?->nom,
                'personnes_affectees' => $impact->personnes_affectees,
                'niveau_gravite' => $impact->niveau_gravite,
                'statut' => $impact->statut,
                'mesures_urgence' => $impact->mesures_urgence,
            ])->values();

        return response()->json([
            'coupures' => $coupures->map(fn (Coupure $coupure) => array_merge(
                $coupure->toMapSummary(),
                [
                    'interventions_count' => $coupure->interventions->count(),
                    'interventions' => $coupure->interventions->sortByDesc('date_intervention')->values()->map(fn ($intervention) => [
                        'equipe' => $intervention->equipe,
                        'date' => $intervention->date_intervention->format('d/m/Y H:i'),
                        'statut' => $intervention->statut,
                    ]),
                    'detail_url' => route('coupures.show', $coupure),
                ]
            ))->values(),
            'impacts' => $impacts,
        ]);
    }

    public function index(Request $request): View
    {
        $quartierId = $request->query('quartier');
        $coupures = Coupure::query()
            ->with('quartier')
            ->whereIn('statut', ['en_cours', 'prevue'])
            ->when($quartierId, fn ($query, $quartierId) => $query->where('quartier_id', $quartierId))
            ->orderByRaw("CASE WHEN statut = 'en_cours' THEN 0 ELSE 1 END")
            ->orderBy('date_debut')
            ->paginate(9)
            ->withQueryString();

        return view('front.coupures.index', [
            'coupures' => $coupures,
            'quartiers' => Quartier::orderBy('nom')->get(),
            'selectedQuartier' => $quartierId,
        ]);
    }

    public function show(Coupure $coupure): View
    {
        abort_unless(in_array($coupure->statut, ['en_cours', 'prevue'], true), 404);

        return view('front.coupures.show', [
            'coupure' => $coupure->load([
                'quartier',
                'interventions' => fn ($query) => $query->latest('date_intervention'),
                'impacts' => fn ($query) => $query->latest('date_signalement'),
            ]),
        ]);
    }
}
