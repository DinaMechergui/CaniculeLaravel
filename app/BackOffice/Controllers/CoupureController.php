<?php

namespace App\BackOffice\Controllers;

use App\BackOffice\Requests\StoreCoupureRequest;
use App\BackOffice\Requests\UpdateCoupureRequest;
use App\Http\Controllers\Controller;
use App\Models\Coupure;
use App\Models\Quartier;
use App\Services\CoupureAlertService;
use App\Services\CoupurePredictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoupureController extends Controller
{
    public function index(Request $request, CoupureAlertService $alertService): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'quartier_id' => ['nullable', 'integer', 'exists:quartiers,id'],
            'type' => ['nullable', 'in:delestage,panne,surcharge'],
            'statut' => ['nullable', 'in:prevue,en_cours,terminee'],
            'date_debut_from' => ['nullable', 'date'],
            'date_debut_to' => ['nullable', 'date'],
            'date_fin_from' => ['nullable', 'date'],
            'date_fin_to' => ['nullable', 'date'],
            'sort' => ['nullable', 'in:date_debut,date_fin,created_at'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);

        $sort = $filters['sort'] ?? 'date_debut';
        $direction = $filters['direction'] ?? 'desc';
        $coupures = Coupure::query()->with('quartier')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search) {
                $query->where('type', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('quartier', fn ($quartier) => $quartier
                        ->where('nom', 'like', "%{$search}%")
                        ->orWhere('ville', 'like', "%{$search}%")
                        ->orWhere('code_postal', 'like', "%{$search}%"));
            }))
            ->when($filters['quartier_id'] ?? null, fn ($query, $quartierId) => $query->where('quartier_id', $quartierId))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['statut'] ?? null, fn ($query, $statut) => $query->where('statut', $statut))
            ->when($filters['date_debut_from'] ?? null, fn ($query, $date) => $query->whereDate('date_debut', '>=', $date))
            ->when($filters['date_debut_to'] ?? null, fn ($query, $date) => $query->whereDate('date_debut', '<=', $date))
            ->when($filters['date_fin_from'] ?? null, fn ($query, $date) => $query->whereDate('date_fin', '>=', $date))
            ->when($filters['date_fin_to'] ?? null, fn ($query, $date) => $query->whereDate('date_fin', '<=', $date))
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate(10)
            ->withQueryString();

        $quartiers = Quartier::query()->orderBy('nom')->get(['id', 'nom', 'ville']);
        $hasFilters = collect($filters)->contains(fn ($value) => $value !== null && $value !== '');

        // Détection des quartiers à haute fréquence de coupures pour alerter l'administrateur
        $quartiersCritiques = $alertService->getQuartiersCritiques(3);

        return view('back.coupures.index', compact('coupures', 'quartiers', 'filters', 'hasFilters', 'quartiersCritiques'));
    }

    public function create(): View
    {
        return view('back.coupures.create', [
            'coupure' => new Coupure,
            'quartiers' => Quartier::orderBy('nom')->get(),
        ]);
    }

    public function store(StoreCoupureRequest $request, CoupureAlertService $alertService): RedirectResponse
    {
        $coupure = Coupure::create($request->validated());

        // Alerte automatique si le quartier dépasse le seuil critique de coupures
        $alerte = $alertService->verifierEtAlerter($coupure->quartier_id);

        $response = redirect()->route('admin.coupures.index')
            ->with('success', 'La coupure a été créée avec succès.');

        if ($alerte) {
            $response->with('warning', $alerte['message']);
        }

        return $response;
    }

    public function show(Coupure $coupure, CoupurePredictionService $predictionService): View
    {
        // Calcul d'une prédiction indicative si la coupure est active
        $prediction = null;
        if (in_array($coupure->statut, ['en_cours', 'prevue'])) {
            $prediction = $predictionService->predireFin(
                $coupure->quartier_id,
                $coupure->type,
                $coupure->date_debut,
                $coupure->description
            );
        }

        return view('back.coupures.show', [
            'coupure' => $coupure->load('quartier'),
            'prediction' => $prediction,
        ]);
    }

    public function edit(Coupure $coupure): View
    {
        return view('back.coupures.edit', [
            'coupure' => $coupure,
            'quartiers' => Quartier::orderBy('nom')->get(),
        ]);
    }

    public function update(UpdateCoupureRequest $request, Coupure $coupure, CoupureAlertService $alertService): RedirectResponse
    {
        $coupure->update($request->validated());

        // Alerte automatique si le quartier dépasse le seuil critique
        $alerte = $alertService->verifierEtAlerter($coupure->quartier_id);

        $response = redirect()->route('admin.coupures.index')
            ->with('success', 'La coupure a été modifiée avec succès.');

        if ($alerte) {
            $response->with('warning', $alerte['message']);
        }

        return $response;
    }

    public function destroy(Coupure $coupure): RedirectResponse
    {
        $coupure->delete();

        return redirect()->route('admin.coupures.index')->with('success', 'La coupure a été supprimée avec succès.');
    }

    /**
     * Endpoint API AJAX pour prédire l'heure de fin par IA.
     */
    public function predireFin(Request $request, CoupurePredictionService $predictionService): JsonResponse
    {
        $data = $request->validate([
            'quartier_id' => ['nullable', 'integer', 'exists:quartiers,id'],
            'type'        => ['required', 'in:delestage,panne,surcharge'],
            'date_debut'  => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $resultat = $predictionService->predireFin(
            $data['quartier_id'] ?? null,
            $data['type'],
            $data['date_debut'] ?? null,
            $data['description'] ?? null
        );

        return response()->json($resultat);
    }

    /**
     * Marquer une notification d'alerte comme lue.
     */
    public function markNotificationAsRead(string $id): JsonResponse
    {
        $user = auth()->user();
        if ($user) {
            $notification = $user->notifications()->where('id', $id)->first();
            if ($notification) {
                $notification->markAsRead();
            }
        }

        return response()->json(['success' => true]);
    }
}
