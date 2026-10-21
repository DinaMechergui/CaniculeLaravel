<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Coupure;
use App\Models\ImpactCoupure;
use App\Models\Intervention;
use App\Models\Quartier;
use App\Services\SmartGridAIService;
use App\Services\SmartGridPriorityService;
use App\Services\SmartGridRoutingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class SmartGridController extends Controller
{
    public function __construct(
        private readonly SmartGridPriorityService $priorityService,
        private readonly SmartGridAIService $aiService,
        private readonly SmartGridRoutingService $routingService,
    ) {}

    public function index(): View
    {
        return view('back.smartgrid.index', [
            'quartiers' => Quartier::query()->orderBy('nom')->get(['id', 'nom', 'ville']),
            'routingConfigured' => $this->routingService->isConfigured(),
            'aiConfigured' => $this->aiService->isConfigured(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'quartier_id' => ['nullable', 'integer', 'exists:quartiers,id'],
            'ville' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:delestage,panne,surcharge'],
            'statut' => ['nullable', 'in:prevue,en_cours,terminee'],
            'niveau_gravite' => ['nullable', 'in:moyen,eleve,critique'],
            'categorie' => ['nullable', 'in:hopital,ecole,commerce,residence,autre'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        $coupures = $this->filteredCoupures($filters)->get();
        $coupureIds = $coupures->modelKeys();
        $cutFeatures = $coupures->map(function (Coupure $coupure): array {
            $priority = $this->priorityService->score($coupure);

            return array_merge(
                $coupure->toMapSummary(),
                [
                    'impacts_count' => $coupure->impacts->count(),
                    'interventions_count' => $coupure->interventions->count(),
                    'interventions' => $coupure->interventions->sortByDesc('date_intervention')->values()->map(fn ($intervention) => [
                        'equipe' => $intervention->equipe,
                        'technicien' => $intervention->technicien,
                        'date' => $intervention->date_intervention->format('d/m/Y H:i'),
                        'duree' => $intervention->duree_estimee,
                        'statut' => $intervention->statut,
                    ]),
                    'priority' => $priority,
                    'detail_url' => route('admin.coupures.show', $coupure),
                ]
            );
        })->values();

        $impacts = ImpactCoupure::query()
            ->with('coupure.quartier')
            ->whereIn('coupure_id', $coupureIds)
            ->when($filters['niveau_gravite'] ?? null, fn ($query, $value) => $query->where('niveau_gravite', $value))
            ->when($filters['categorie'] ?? null, fn ($query, $value) => $query->where('categorie', $value))
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(fn (ImpactCoupure $impact) => [
                'id' => $impact->id,
                'latitude' => (float) $impact->latitude,
                'longitude' => (float) $impact->longitude,
                'nom_structure' => $impact->nom_structure,
                'categorie' => $impact->categorie,
                'coupure' => '#'.$impact->coupure_id.' · '.$impact->coupure?->quartier?->nom,
                'personnes_affectees' => $impact->personnes_affectees,
                'niveau_gravite' => $impact->niveau_gravite,
                'statut' => $impact->statut,
                'mesures_urgence' => $impact->mesures_urgence,
            ])->values();

        $geolocated = $cutFeatures->filter(fn (array $cut) => $cut['latitude'] !== null && $cut['longitude'] !== null);

        return response()->json([
            'coupures' => $cutFeatures,
            'impacts' => $impacts,
            'statistics' => [
                'geolocalisees' => $geolocated->count(),
                'sans_position' => $cutFeatures->count() - $geolocated->count(),
                'en_cours' => $cutFeatures->where('statut', 'en_cours')->count(),
                'prevues' => $cutFeatures->where('statut', 'prevue')->count(),
                'interventions_en_cours' => Intervention::query()->whereIn('coupure_id', $coupureIds)
                    ->where('statut', 'en_cours')->count(),
                'personnes_affectees' => (int) $coupures->sum(fn (Coupure $coupure) => $coupure->impacts->sum('personnes_affectees')),
                'impacts_critiques' => (int) $coupures->sum(fn (Coupure $coupure) => $coupure->impacts->where('niveau_gravite', 'critique')->count()),
            ],
            'routing_configured' => $this->routingService->isConfigured(),
            'ai_configured' => $this->aiService->isConfigured(),
        ]);
    }

    public function analyze(Coupure $coupure): JsonResponse
    {
        try {
            $analysis = $this->aiService->analyze($coupure);
            $coupure->loadMissing(['impacts', 'interventions']);

            return response()->json([
                'analysis' => $analysis,
                'deterministic_priority' => $this->priorityService->score($coupure),
            ]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }
    }

    public function analyzeZone(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quartier_id' => ['required', 'integer', 'exists:quartiers,id'],
        ]);

        try {
            return response()->json(['analysis' => $this->aiService->analyzeZone((int) $validated['quartier_id'])]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }
    }

    public function route(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'origin.latitude' => ['required', 'numeric', 'between:-90,90'],
            'origin.longitude' => ['required', 'numeric', 'between:-180,180'],
            'coupure_ids' => ['required', 'array', 'min:1', 'max:10'],
            'coupure_ids.*' => ['required', 'integer', 'distinct', 'exists:coupures,id'],
        ]);

        $coupures = Coupure::query()
            ->with(['quartier', 'impacts', 'interventions'])
            ->whereIn('id', $validated['coupure_ids'])
            ->whereIn('statut', ['en_cours', 'prevue'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->keyBy('id');

        if ($coupures->count() !== count($validated['coupure_ids'])) {
            return response()->json([
                'message' => 'Chaque coupure sélectionnée doit exister, être prévue ou en cours, et avoir une position précise.',
            ], 422);
        }

        $stops = $coupures->values()->map(fn (Coupure $coupure) => [
            'id' => $coupure->id,
            'label' => '#'.$coupure->id.' · '.$coupure->quartier?->nom,
            'latitude' => (float) $coupure->latitude,
            'longitude' => (float) $coupure->longitude,
            'priority' => $this->priorityService->score($coupure),
        ])->all();

        try {
            return response()->json($this->routingService->optimize($validated['origin'], $stops));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }
    }

    private function filteredCoupures(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        return Coupure::query()
            ->with([
                'quartier',
                'impacts' => fn ($query) => $query
                    ->when($filters['niveau_gravite'] ?? null, fn ($query, $value) => $query->where('niveau_gravite', $value))
                    ->when($filters['categorie'] ?? null, fn ($query, $value) => $query->where('categorie', $value)),
                'interventions',
            ])
            ->when($filters['quartier_id'] ?? null, fn ($query, $id) => $query->where('quartier_id', $id))
            ->when($filters['ville'] ?? null, fn ($query, $ville) => $query->whereHas('quartier', fn ($query) => $query->where('ville', $ville)))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['statut'] ?? null, fn ($query, $statut) => $query->where('statut', $statut))
            ->when($filters['date_debut'] ?? null, fn ($query, $date) => $query->whereDate('date_debut', '>=', $date))
            ->when($filters['date_fin'] ?? null, fn ($query, $date) => $query->whereDate('date_debut', '<=', $date))
            ->when($filters['niveau_gravite'] ?? null, fn ($query, $value) => $query->whereHas('impacts', fn ($impacts) => $impacts->where('niveau_gravite', $value)))
            ->when($filters['categorie'] ?? null, fn ($query, $value) => $query->whereHas('impacts', fn ($impacts) => $impacts->where('categorie', $value)))
            ->latest('date_debut');
    }
}
