<?php

namespace App\BackOffice\Controllers;

use App\BackOffice\Requests\ActivateCampagneConseilRequest;
use App\BackOffice\Requests\DupliquerCampagneConseilRequest;
use App\BackOffice\Requests\StoreCampagneConseilRequest;
use App\BackOffice\Requests\TerminerCampagneConseilRequest;
use App\BackOffice\Requests\UpdateCampagneConseilRequest;
use App\Http\Controllers\Controller;
use App\Models\CampagneConseil;
use App\Models\Conseil;
use App\Services\CampagneService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CampagneConseilController extends Controller
{
    public function __construct(private readonly CampagneService $service) {}

    // ─── CRUD ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $statut    = $request->query('statut');
        $recherche = trim((string) $request->query('q', ''));
        $canal     = $request->query('canal');
        $public    = $request->query('public');

        $campagnes = CampagneConseil::with('conseil')
            ->when(
                array_key_exists((string) $statut, CampagneConseil::STATUTS),
                fn ($query) => $query->where('statut', $statut)
            )
            ->when(
                array_key_exists((string) $canal, CampagneConseil::CANAUX),
                fn ($query) => $query->where('canal', $canal)
            )
            ->when(
                array_key_exists((string) $public, CampagneConseil::PUBLICS),
                fn ($query) => $query->where('public_cible', $public)
            )
            ->when(
                $recherche !== '',
                fn ($query) => $query->where('titre', 'like', '%' . $recherche . '%')
            )
            ->latest('date_debut')
            ->paginate(10)
            ->withQueryString();

        return view('back.campagnes-conseil.index', [
            'campagnes'   => $campagnes,
            'statuts'     => CampagneConseil::STATUTS,
            'canaux'      => CampagneConseil::CANAUX,
            'publics'     => CampagneConseil::PUBLICS,
            'statutFiltre' => $statut,
            'canalFiltre'  => $canal,
            'publicFiltre' => $public,
            'recherche'   => $recherche,
        ]);
    }

    public function create()
    {
        return view('back.campagnes-conseil.create', [
            'campagne' => new CampagneConseil(['statut' => 'brouillon']),
            'conseils' => Conseil::orderBy('titre')->get(),
        ]);
    }

    public function store(StoreCampagneConseilRequest $request)
    {
        CampagneConseil::create($request->validated());

        return redirect()
            ->route('admin.campagnes-conseil.index')
            ->with('success', 'La campagne a été créée avec succès.');
    }

    public function show(CampagneConseil $campagneConseil)
    {
        $campagneConseil->load('conseil.categorie');

        return view('back.campagnes-conseil.show', [
            'campagne' => $campagneConseil,
        ]);
    }

    public function edit(CampagneConseil $campagneConseil)
    {
        return view('back.campagnes-conseil.edit', [
            'campagne' => $campagneConseil,
            'conseils' => Conseil::orderBy('titre')->get(),
        ]);
    }

    public function update(UpdateCampagneConseilRequest $request, CampagneConseil $campagneConseil)
    {
        $campagneConseil->update($request->validated());

        return redirect()
            ->route('admin.campagnes-conseil.index')
            ->with('success', 'La campagne a été modifiée avec succès.');
    }

    public function destroy(CampagneConseil $campagneConseil)
    {
        $campagneConseil->delete();

        return redirect()
            ->route('admin.campagnes-conseil.index')
            ->with('success', 'La campagne a été supprimée.');
    }

    // ─── Transitions de statut ────────────────────────────────────────────────

    public function activer(ActivateCampagneConseilRequest $request, CampagneConseil $campagneConseil)
    {
        $erreur = $this->service->activer($campagneConseil);

        if ($erreur) {
            return redirect()
                ->route('admin.campagnes-conseil.show', $campagneConseil)
                ->with('error', $erreur);
        }

        return redirect()
            ->route('admin.campagnes-conseil.show', $campagneConseil)
            ->with('success', 'La campagne a été activée.');
    }

    public function terminer(TerminerCampagneConseilRequest $request, CampagneConseil $campagneConseil)
    {
        $erreur = $this->service->terminer($campagneConseil);

        if ($erreur) {
            return redirect()
                ->route('admin.campagnes-conseil.show', $campagneConseil)
                ->with('error', $erreur);
        }

        return redirect()
            ->route('admin.campagnes-conseil.show', $campagneConseil)
            ->with('success', 'La campagne a été terminée.');
    }

    // ─── Duplication ──────────────────────────────────────────────────────────

    public function dupliquer(DupliquerCampagneConseilRequest $request, CampagneConseil $campagneConseil)
    {
        $decalage = (int) ($request->validated()['decalage_jours'] ?? 30);
        $copie    = $this->service->dupliquer($campagneConseil, $decalage);

        return redirect()
            ->route('admin.campagnes-conseil.edit', $copie)
            ->with('success', 'La campagne a été dupliquée. Vous pouvez la modifier avant de l\'activer.');
    }

    // ─── Statistiques ─────────────────────────────────────────────────────────

    public function statistiques()
    {
        $stats            = $this->service->statistiques();
        $calendrier       = $this->service->calendrierMensuel();

        return view('back.campagnes-conseil.statistiques', compact('stats', 'calendrier'));
    }

    // ─── Export CSV ──────────────────────────────────────────────────────────

    public function exportCsv(Request $request)
    {
        $statut    = $request->query('statut');
        $canal     = $request->query('canal');
        $public    = $request->query('public');
        $recherche = trim((string) $request->query('q', ''));

        $campagnes = CampagneConseil::with('conseil')
            ->when(array_key_exists((string) $statut, CampagneConseil::STATUTS), fn ($q) => $q->where('statut', $statut))
            ->when(array_key_exists((string) $canal, CampagneConseil::CANAUX), fn ($q) => $q->where('canal', $canal))
            ->when(array_key_exists((string) $public, CampagneConseil::PUBLICS), fn ($q) => $q->where('public_cible', $public))
            ->when($recherche !== '', fn ($q) => $q->where('titre', 'like', '%' . $recherche . '%'))
            ->orderBy('date_debut')
            ->get();

        $csv = $this->service->exportCsv($campagnes);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="campagnes_' . now()->format('Ymd_His') . '.csv"',
        ]);
    }
}