<?php

namespace App\FrontOffice\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PointFraicheur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Assistant IA du front office : chatbot qui répond en français sur les
 * points de fraîcheur et la chaleur (fournisseur OpenRouter, modèle
 * configurable via OPENROUTER_MODEL dans .env).
 */
class AssistantController extends Controller
{
    /**
     * Page publique du chat.
     */
    public function index(): View
    {
        return view('front.assistant.index');
    }

    /**
     * Point d'entrée AJAX : renvoie { reponse: string } en JSON.
     */
    public function chat(Request $request): JsonResponse
    {
        $donnees = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:1000'],
            'contexte' => ['nullable', 'string', 'max:300'],
            'historique' => ['nullable', 'array', 'max:12'],
            'historique.*.role' => ['required', 'in:user,assistant'],
            'historique.*.content' => ['required', 'string', 'max:2000'],
        ]);

        $cle = (string) config('services.openrouter.key');

        if ($cle === '') {
            return response()->json([
                'reponse' => "L'assistant IA n'est pas encore configuré sur le serveur (clé manquante).",
            ], 503);
        }

        // Contexte de la page (filtres de l'utilisateur sur la liste).
        $systeme = $this->contexte();

        if (!empty($donnees['contexte'])) {
            $systeme .= "\n\nFiltres actuellement appliqués par l'utilisateur sur la page des points : "
                . trim($donnees['contexte']) . ".";
        }

        $messages = array_merge(
            [
                ['role' => 'system', 'content' => $systeme],
            ],
            $this->historique($donnees['historique'] ?? []),
            [
                ['role' => 'user', 'content' => trim($donnees['message'])],
            ]
        );

        $options = [];
        $caBundle = config('services.openrouter.ca_bundle');
        if ($caBundle && file_exists($caBundle)) {
            $options['verify'] = $caBundle;
        }

        try {
            $reponse = Http::withToken($cle)
                ->withOptions($options)
                ->timeout(30)
                ->post(config('services.openrouter.url'), [
                    'model' => config('services.openrouter.model', 'openai/gpt-4o-mini'),
                    'messages' => $messages,
                    'max_tokens' => 700,
                    'temperature' => 0.4,
                ]);
        } catch (\Throwable $exception) {
            Log::warning('Assistant IA : erreur réseau — ' . $exception->getMessage());

            return response()->json([
                'reponse' => "Je n'ai pas pu contacter le moteur d'IA. Réessayez dans un instant.",
            ], 502);
        }

        $texte = $reponse->ok()
            ? trim((string) $reponse->json('choices.0.message.content'))
            : '';

        if ($texte === '') {
            Log::warning('Assistant IA : réponse du fournisseur inexploitable', [
                'statut' => $reponse->status(),
            ]);

            return response()->json([
                'reponse' => "Le moteur d'IA est momentanément indisponible. Réessayez plus tard.",
            ], 502);
        }

        return response()->json(['reponse' => $texte]);
    }

    /**
     * Instructions envoyées au modèle + liste des points (RAG minimal) :
     * l'IA répond sur données réelles issues de la base.
     */
    private function contexte(): string
    {
        $points = PointFraicheur::query()
            ->with('type:id,nom')
            ->get(['id', 'type_point_id', 'nom', 'adresse', 'latitude', 'longitude', 'horaires', 'accessible']);

        $liste = $points->map(fn (PointFraicheur $point): string => sprintf(
            '- %s (%s) : %s | latitude %s, longitude %s | horaires : %s | %s',
            $point->nom,
            $point->type?->nom ?? 'sans type',
            $point->adresse,
            $point->latitude,
            $point->longitude,
            $point->horaires ?? 'non renseignés',
            $point->accessible ? 'accessible au public' : 'accès limité'
        ))->implode("\n");

        return <<<TXT
        Tu es l'assistant IA du site « Alerte Canicule », une application web
        française qui diffuse des alertes de canicule et recense des points de
        fraîcheur (parcs, salles climatisées, plages) en Tunisie, notamment en
        Grande Tunis.

        Règles :
        - Réponds TOUJOURS en français, en 4 à 8 lignes maximum, avec un ton
          utile et courtois.
        - N'invente jamais de lieu absent de la liste ci-dessous : cite le nom
          et l'adresse des points utiles.
        - Si l'utilisateur indique sa position (quartier ou ville), conseille
          les lieux les plus proches de la liste.
        - Pour la chaleur et l'hydratation, donne des conseils standards
          (boire de l'eau régulièrement, éviter le soleil entre 12h et 16h,
          se rafraîchir dans un lieu climatisé).
        - Hors sujet, réponds poliment que tu es spécialisé dans les points de
          fraîcheur et les vagues de chaleur.

        Points de fraîcheur connus ({$points->count()} au total) :
        {$liste}
        TXT;
    }

    /**
     * Historique limité aux 12 derniers échanges valides.
     *
     * @param  array<int, array{role: string, content: string}>  $historique
     * @return array<int, array{role: string, content: string}>
     */
    private function historique(array $historique): array
    {
        $messages = [];

        foreach (array_slice($historique, -12) as $tour) {
            $messages[] = [
                'role' => $tour['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => mb_substr(trim($tour['content']), 0, 2000),
            ];
        }

        return $messages;
    }
}
