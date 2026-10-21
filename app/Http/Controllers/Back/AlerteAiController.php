<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\BulletinAlerte;
use App\Models\Diffusion;
use App\Services\AlerteAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlerteAiController extends Controller
{
    /**
     * Génère une proposition de bulletin d'alerte par IA.
     */
    public function generateBulletin(Request $request, AlerteAiService $aiService): JsonResponse
    {
        $validated = $request->validate([
            'alerte_id' => ['required', 'integer', 'exists:alertes,id'],
            'keywords' => ['nullable', 'string', 'max:500'],
        ]);

        $alerte = Alerte::with('bulletins')->findOrFail($validated['alerte_id']);

        if ($alerte->statut === 'terminee') {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de générer un bulletin : cette alerte est déjà terminée.',
            ], 422);
        }

        $result = $aiService->generateBulletin($alerte, $validated['keywords'] ?? null);

        return response()->json($result);
    }

    /**
     * Adapte le contenu d'un bulletin aux spécificités d'un canal de diffusion via l'IA.
     */
    public function adaptDiffusion(Request $request, AlerteAiService $aiService): JsonResponse
    {
        $validated = $request->validate([
            'bulletin_alerte_id' => ['required', 'integer', 'exists:bulletins_alerte,id'],
            'canal' => ['required', 'string', 'in:' . implode(',', array_keys(Diffusion::CANAUX))],
        ]);

        $bulletin = BulletinAlerte::with('alerte')->findOrFail($validated['bulletin_alerte_id']);

        $result = $aiService->adaptDiffusion($bulletin, $validated['canal']);

        return response()->json($result);
    }
}
