<?php

namespace App\Http\Controllers\Back;

use App\BackOffice\Requests\StoreCoupureRequest;
use App\BackOffice\Requests\UpdateCoupureRequest;
use App\Http\Controllers\Controller;
use App\Models\Coupure;
use App\Models\Quartier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoupureController extends Controller
{
    public function index(Request $request): View
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

        return view('back.coupures.index', compact('coupures', 'quartiers', 'filters', 'hasFilters'));
    }

    public function create(): View
    {
        return view('back.coupures.create', [
            'coupure' => new Coupure,
            'quartiers' => Quartier::orderBy('nom')->get(),
        ]);
    }

    public function store(StoreCoupureRequest $request): RedirectResponse
    {
        Coupure::create($request->validated());

        return redirect()->route('admin.coupures.index')->with('success', 'La coupure a été créée avec succès.');
    }

    public function show(Coupure $coupure): View
    {
        return view('back.coupures.show', [
            'coupure' => $coupure->load([
                'quartier',
                'interventions' => fn ($query) => $query->latest('date_intervention'),
                'impacts' => fn ($query) => $query->latest('date_signalement'),
            ]),
        ]);
    }

    public function edit(Coupure $coupure): View
    {
        return view('back.coupures.edit', [
            'coupure' => $coupure,
            'quartiers' => Quartier::orderBy('nom')->get(),
        ]);
    }

    public function update(UpdateCoupureRequest $request, Coupure $coupure): RedirectResponse
    {
        $coupure->update($request->validated());

        return redirect()->route('admin.coupures.index')->with('success', 'La coupure a été modifiée avec succès.');
    }

    public function destroy(Coupure $coupure): RedirectResponse
    {
        if ($coupure->interventions()->exists() || $coupure->impacts()->exists()) {
            return redirect()->route('admin.coupures.index')
                ->with('error', 'Impossible de supprimer cette coupure : des interventions ou des impacts y sont encore rattachés.');
        }

        $coupure->delete();

        return redirect()->route('admin.coupures.index')->with('success', 'La coupure a été supprimée avec succès.');
    }
}
