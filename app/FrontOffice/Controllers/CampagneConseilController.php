<?php

namespace App\FrontOffice\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CampagneConseil;
use Illuminate\Http\Request;

class CampagneConseilController extends Controller
{
    public function index(Request $request)
    {
        $public = $request->query('public');
        $canal  = $request->query('canal');

        $campagnes = CampagneConseil::with('conseil.categorie')
            ->where('statut', 'active')
            ->when(
                array_key_exists((string) $public, CampagneConseil::PUBLICS),
                fn ($query) => $query->where('public_cible', $public)
            )
            ->when(
                array_key_exists((string) $canal, CampagneConseil::CANAUX),
                fn ($query) => $query->where('canal', $canal)
            )
            ->orderByRaw('CASE WHEN date_fin IS NULL THEN 1 ELSE 0 END')
            ->orderBy('date_fin')
            ->paginate(9)
            ->withQueryString();

        return view('front.campagnes.index', [
            'campagnes'   => $campagnes,
            'publics'     => CampagneConseil::PUBLICS,
            'canaux'      => CampagneConseil::CANAUX,
            'publicFiltre' => $public,
            'canalFiltre'  => $canal,
        ]);
    }

    public function show(CampagneConseil $campagneConseil)
    {
        // Seules les campagnes actives sont visibles par le public.
        abort_unless($campagneConseil->statut === 'active', 404);

        $campagneConseil->load('conseil.categorie');

        return view('front.campagnes.show', [
            'campagne' => $campagneConseil,
        ]);
    }
}