<?php

namespace App\Http\Controllers\Back;

use App\BackOffice\Requests\StoreImpactCoupureRequest;
use App\BackOffice\Requests\UpdateImpactCoupureRequest;
use App\Http\Controllers\Controller;
use App\Models\Coupure;
use App\Models\ImpactCoupure;
use App\Models\Quartier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImpactCoupureController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'quartier_id' => ['nullable', 'integer', 'exists:quartiers,id'],
            'coupure_id' => ['nullable', 'integer', 'exists:coupures,id'],
            'categorie' => ['nullable', 'in:hopital,ecole,commerce,residence,autre'],
            'niveau_gravite' => ['nullable', 'in:moyen,eleve,critique'],
            'statut' => ['nullable', 'in:en_traitement,resolu'],
            'search' => ['nullable', 'string', 'max:150'],
        ]);

        $impacts = ImpactCoupure::query()
            ->with('coupure.quartier')
            ->when($filters['quartier_id'] ?? null, fn ($query, $id) => $query->whereHas(
                'coupure',
                fn ($coupures) => $coupures->where('quartier_id', $id)
            ))
            ->when($filters['coupure_id'] ?? null, fn ($query, $id) => $query->where('coupure_id', $id))
            ->when($filters['categorie'] ?? null, fn ($query, $value) => $query->where('categorie', $value))
            ->when($filters['niveau_gravite'] ?? null, fn ($query, $value) => $query->where('niveau_gravite', $value))
            ->when($filters['statut'] ?? null, fn ($query, $value) => $query->where('statut', $value))
            ->when($filters['search'] ?? null, fn ($query, $value) => $query->where('nom_structure', 'like', "%{$value}%"))
            ->latest('date_signalement')
            ->paginate(10)
            ->withQueryString();

        $quartiers = Quartier::query()->orderBy('nom')->get(['id', 'nom']);
        $coupures = Coupure::query()->with('quartier')->latest('date_debut')->get();
        $statistiques = [
            'total' => ImpactCoupure::count(),
            'personnes' => ImpactCoupure::sum('personnes_affectees'),
            'critiques' => ImpactCoupure::where('niveau_gravite', 'critique')->count(),
            'resolus' => ImpactCoupure::where('statut', 'resolu')->count(),
            'en_traitement' => ImpactCoupure::where('statut', 'en_traitement')->count(),
        ];

        return view('back.impacts-coupures.index', compact('impacts', 'quartiers', 'coupures', 'filters', 'statistiques'));
    }

    public function create(): View
    {
        return view('back.impacts-coupures.create', [
            'impact' => new ImpactCoupure,
            'coupures' => Coupure::with('quartier')->latest('date_debut')->get(),
        ]);
    }

    public function store(StoreImpactCoupureRequest $request): RedirectResponse
    {
        ImpactCoupure::create($request->validated());

        return redirect()->route('admin.impacts-coupures.index')
            ->with('success', 'L’impact a été créé avec succès.');
    }

    public function show(ImpactCoupure $impactCoupure): View
    {
        return view('back.impacts-coupures.show', [
            'impact' => $impactCoupure->load('coupure.quartier'),
        ]);
    }

    public function edit(ImpactCoupure $impactCoupure): View
    {
        return view('back.impacts-coupures.edit', [
            'impact' => $impactCoupure,
            'coupures' => Coupure::with('quartier')->latest('date_debut')->get(),
        ]);
    }

    public function update(UpdateImpactCoupureRequest $request, ImpactCoupure $impactCoupure): RedirectResponse
    {
        $impactCoupure->update($request->validated());

        return redirect()->route('admin.impacts-coupures.index')
            ->with('success', 'L’impact a été modifié avec succès.');
    }

    public function destroy(ImpactCoupure $impactCoupure): RedirectResponse
    {
        $impactCoupure->delete();

        return redirect()->route('admin.impacts-coupures.index')
            ->with('success', 'L’impact a été supprimé avec succès.');
    }
}
