<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\AlerteMeteo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlerteMeteoController extends Controller
{
    /**
     * Liste publique des alertes météo actives avec pagination.
     */
    public function index(Request $request): View
    {
        $filtre = $request->query('filtre', 'actives');

        $query = AlerteMeteo::query()->with('niveauAlerte');

        if ($filtre === 'actives') {
            $query->active();
        }

        $alertes = $query->latest('date_debut')
            ->paginate(10)
            ->withQueryString();

        return view('front.alertes-meteo.index', [
            'alertes' => $alertes,
            'filtre' => $filtre,
        ]);
    }

    /**
     * Détail d'une alerte météo publique avec mesures de prévention associées.
     */
    public function show(int $id): View
    {
        $alerte = AlerteMeteo::with([
            'niveauAlerte',
            'mesures' => function ($query) {
                $query->whereIn('statut', ['prevue', 'en_cours'])
                    ->orderBy('date_debut', 'asc');
            },
        ])->findOrFail($id);

        return view('front.alertes-meteo.show', [
            'alerte' => $alerte,
        ]);
    }
}
