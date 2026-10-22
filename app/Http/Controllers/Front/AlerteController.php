<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlerteController extends Controller
{
    /**
     * Liste publique des alertes en cours avec leur niveau actuel.
     */
    public function index(Request $request): View
    {
        $query = Alerte::enCours()->with(['bulletins' => fn ($q) => $q->orderBy('date_publication', 'desc')]);

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        $search = trim((string) $request->input('search', $request->input('q', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('zone_concernee', 'like', "%{$search}%");
            });
        }

        // Tri par gravité du niveau de vigilance du dernier bulletin
        // rouge (4) > orange (3) > jaune (2) > vert (1) > autre (0)
        $alertes = $query->get()->sortByDesc(function (Alerte $alerte) {
            return $alerte->gravite_score;
        })->values();

        // Pagination manuelle sur collection pour respecter le tri par gravité calculé
        $page = (int) $request->input('page', 1);
        $perPage = 6;
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $alertes->forPage($page, $perPage),
            $alertes->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $selectedType = $request->query('type');
        $types = Alerte::TYPES;

        return view('front.alertes.index', [
            'alertes' => $paginated,
            'selectedType' => $selectedType,
            'types' => $types,
            'search' => $search,
        ]);
    }

    /**
     * Page de détail d'une alerte avec la chronologie des bulletins et leurs canaux de diffusion.
     */
    public function show(int $id): View
    {
        $alerte = Alerte::with([
            'bulletins' => fn ($q) => $q->orderBy('date_publication', 'asc'),
            'bulletins.diffusions' => fn ($q) => $q->where('statut', 'envoyee')->orderBy('date_envoi', 'desc'),
        ])->findOrFail($id);

        return view('front.alertes.show', compact('alerte'));
    }
}
