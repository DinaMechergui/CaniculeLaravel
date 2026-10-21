<?php

namespace App\Http\Controllers\Back;

use App\BackOffice\Requests\StoreInterventionRequest;
use App\BackOffice\Requests\UpdateInterventionRequest;
use App\Http\Controllers\Controller;
use App\Models\Coupure;
use App\Models\Intervention;
use App\Models\Quartier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InterventionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'statut' => ['nullable', 'in:prevue,en_cours,terminee'],
            'quartier_id' => ['nullable', 'integer', 'exists:quartiers,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $interventions = Intervention::query()
            ->with('coupure.quartier')
            ->when($filters['statut'] ?? null, fn ($query, $statut) => $query->where('statut', $statut))
            ->when($filters['quartier_id'] ?? null, fn ($query, $id) => $query->whereHas(
                'coupure',
                fn ($coupures) => $coupures->where('quartier_id', $id)
            ))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search) {
                $query->where('technicien', 'like', "%{$search}%")
                    ->orWhere('equipe', 'like', "%{$search}%");
            }))
            ->latest('date_intervention')
            ->paginate(10)
            ->withQueryString();

        $quartiers = Quartier::query()->orderBy('nom')->get(['id', 'nom']);

        return view('back.interventions.index', compact('interventions', 'quartiers', 'filters'));
    }

    public function create(): View
    {
        return view('back.interventions.create', [
            'intervention' => new Intervention,
            'coupures' => Coupure::with('quartier')->latest('date_debut')->get(),
        ]);
    }

    public function store(StoreInterventionRequest $request): RedirectResponse
    {
        Intervention::create($request->validated());

        return redirect()->route('admin.interventions.index')
            ->with('success', 'L’intervention a été créée avec succès.');
    }

    public function show(Intervention $intervention): View
    {
        return view('back.interventions.show', ['intervention' => $intervention->load('coupure.quartier')]);
    }

    public function edit(Intervention $intervention): View
    {
        return view('back.interventions.edit', [
            'intervention' => $intervention,
            'coupures' => Coupure::with('quartier')->latest('date_debut')->get(),
        ]);
    }

    public function update(UpdateInterventionRequest $request, Intervention $intervention): RedirectResponse
    {
        $intervention->update($request->validated());

        return redirect()->route('admin.interventions.index')
            ->with('success', 'L’intervention a été modifiée avec succès.');
    }

    public function destroy(Intervention $intervention): RedirectResponse
    {
        $intervention->delete();

        return redirect()->route('admin.interventions.index')
            ->with('success', 'L’intervention a été supprimée avec succès.');
    }
}
