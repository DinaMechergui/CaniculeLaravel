<?php

namespace App\Http\Controllers\Back;

use App\BackOffice\Requests\StoreQuartierRequest;
use App\BackOffice\Requests\UpdateQuartierRequest;
use App\Http\Controllers\Controller;
use App\Models\Quartier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuartierController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'ville' => ['nullable', 'string', 'max:100'],
            'code_postal' => ['nullable', 'string', 'max:10'],
            'coupures_min' => ['nullable', 'integer', 'min:0'],
            'coupures_max' => ['nullable', 'integer', 'min:0'],
            'sort' => ['nullable', 'in:nom,ville,code_postal,coupures_count'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);

        $sort = $filters['sort'] ?? 'nom';
        $direction = $filters['direction'] ?? 'asc';
        $quartiers = Quartier::query()
            ->withCount('coupures')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search) {
                $query->where('nom', 'like', "%{$search}%")
                    ->orWhere('ville', 'like', "%{$search}%")
                    ->orWhere('code_postal', 'like', "%{$search}%");
            }))
            ->when($filters['ville'] ?? null, fn ($query, $ville) => $query->where('ville', $ville))
            ->when($filters['code_postal'] ?? null, fn ($query, $codePostal) => $query->where('code_postal', $codePostal))
            ->when(isset($filters['coupures_min']), fn ($query) => $query->has('coupures', '>=', (int) $filters['coupures_min']))
            ->when(isset($filters['coupures_max']), fn ($query) => $query->has('coupures', '<=', (int) $filters['coupures_max']))
            ->orderBy($sort, $direction)
            ->when($sort !== 'nom', fn ($query) => $query->orderBy('nom'))
            ->paginate(10)
            ->withQueryString();

        $villes = Quartier::query()->select('ville')->distinct()->orderBy('ville')->pluck('ville');
        $hasFilters = collect($filters)->contains(fn ($value) => $value !== null && $value !== '');

        return view('back.quartiers.index', compact('quartiers', 'filters', 'villes', 'hasFilters'));
    }

    public function create(): View
    {
        return view('back.quartiers.create', ['quartier' => new Quartier]);
    }

    public function store(StoreQuartierRequest $request): RedirectResponse
    {
        Quartier::create($request->validated());

        return redirect()->route('admin.quartiers.index')->with('success', 'Le quartier a été créé avec succès.');
    }

    public function show(Quartier $quartier): View
    {
        return view('back.quartiers.show', [
            'quartier' => $quartier,
            'coupures' => $quartier->coupures()->with('quartier')->latest('date_debut')->get(),
        ]);
    }

    public function edit(Quartier $quartier): View
    {
        return view('back.quartiers.edit', compact('quartier'));
    }

    public function update(UpdateQuartierRequest $request, Quartier $quartier): RedirectResponse
    {
        $quartier->update($request->validated());

        return redirect()->route('admin.quartiers.index')->with('success', 'Le quartier a été modifié avec succès.');
    }

    public function destroy(Quartier $quartier): RedirectResponse
    {
        if ($quartier->coupures()->exists()) {
            return redirect()->route('admin.quartiers.index')
                ->with('error', 'Impossible de supprimer ce quartier : des coupures y sont encore rattachées.');
        }

        $quartier->delete();

        return redirect()->route('admin.quartiers.index')->with('success', 'Le quartier a été supprimé avec succès.');
    }
}
