<?php

namespace App\FrontOffice\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Conseil;
use App\Models\CampagneConseil;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Page d'accueil du front office (avec le « conseil du jour »).
     */
    public function index(): View
    {
        // Conseil du jour : le même toute la journée, différent le lendemain.
        $total = Conseil::count();
        $conseilDuJour = $total > 0
            ? Conseil::with('categorie')->orderBy('id')->skip(now()->dayOfYear % $total)->first()
            : null;

        $campagneEnVedette = CampagneConseil::with('conseil')->where('statut', 'active')
            ->whereNotNull('date_fin')->where('date_fin', '>=', now()->startOfDay())
            ->orderBy('date_fin')->first();

        return view('front.pages.home', compact('conseilDuJour', 'campagneEnVedette'));
    }
}
