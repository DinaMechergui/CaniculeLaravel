<?php

namespace App\BackOffice\Controllers;

use App\BackOffice\Requests\StoreDiagnosticRequest;
use App\BackOffice\Requests\UpdateDiagnosticRequest;
use App\Http\Controllers\Controller;
use App\Models\Diagnostic;
use App\Models\Signalement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Gestion des diagnostics de pannes (back office).
 */
class DiagnosticController extends Controller
{
    /**
     * Liste paginée des diagnostics, filtrable par zone touchée.
     */
    public function index(Request $request): View
    {
        $zone = $request->query('zone');

        $diagnostics = Diagnostic::query()
            ->with('signalement.typeSignalement')
            ->when($zone, function ($query, $zone) {
                $query->where('zone_touchee', $zone);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('back.diagnostics.index', [
            'diagnostics' => $diagnostics,
            'zone'        => $zone,
        ]);
    }

    /**
     * Formulaire de création d'un diagnostic.
     */
    public function create(Request $request): View
    {
        $diagnostic = new Diagnostic([
            'signalement_id' => $request->query('signalement'),
        ]);

        return view('back.diagnostics.create', [
            'diagnostic'   => $diagnostic,
            'signalements' => $this->signalementsDisponibles(),
        ]);
    }

    /**
     * Enregistrement d'un nouveau diagnostic.
     */
    public function store(StoreDiagnosticRequest $request): RedirectResponse
    {
        Diagnostic::create($request->validated());

        return redirect()
            ->route('admin.diagnostics.index')
            ->with('success', 'Le diagnostic a été créé avec succès.');
    }

    /**
     * Détail d'un diagnostic.
     */
    public function show(Diagnostic $diagnostic): View
    {
        return view('back.diagnostics.show', [
            'diagnostic' => $diagnostic->load('signalement.typeSignalement'),
        ]);
    }

    /**
     * Formulaire de modification d'un diagnostic.
     */
    public function edit(Diagnostic $diagnostic): View
    {
        return view('back.diagnostics.edit', [
            'diagnostic'   => $diagnostic,
            'signalements' => $this->signalementsDisponibles($diagnostic),
        ]);
    }

    /**
     * Mise à jour d'un diagnostic.
     */
    public function update(UpdateDiagnosticRequest $request, Diagnostic $diagnostic): RedirectResponse
    {
        $diagnostic->update($request->validated());

        return redirect()
            ->route('admin.diagnostics.index')
            ->with('success', 'Le diagnostic a été modifié avec succès.');
    }

    /**
     * Suppression d'un diagnostic.
     */
    public function destroy(Diagnostic $diagnostic): RedirectResponse
    {
        $diagnostic->delete();

        return redirect()
            ->route('admin.diagnostics.index')
            ->with('success', 'Le diagnostic a été supprimé avec succès.');
    }

    /**
     * Signalements sans diagnostic (+ celui du diagnostic en cours de modification).
     */
    private function signalementsDisponibles(?Diagnostic $diagnostic = null): Collection
    {
        return Signalement::query()
            ->with('typeSignalement')
            ->where(function ($query) use ($diagnostic) {
                $query->doesntHave('diagnostic');

                if ($diagnostic) {
                    $query->orWhere('id', $diagnostic->signalement_id);
                }
            })
            ->orderByDesc('id')
            ->get();
    }
}
