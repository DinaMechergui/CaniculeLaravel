<?php

namespace App\BackOffice\Controllers;

use App\BackOffice\Requests\StoreConseilRequest;
use App\BackOffice\Requests\UpdateConseilRequest;
use App\Http\Controllers\Controller;
use App\Models\CategorieConseil;
use App\Models\Conseil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ConseilController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $conseils = Conseil::with('categorie')
            ->recherche($search)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('back.conseils.index', compact('conseils', 'search'));
    }

    public function create(): View
    {
        return view('back.conseils.create', [
            'conseil' => new Conseil(),
            'categories' => CategorieConseil::orderBy('nom')->get(),
        ]);
    }

    public function store(StoreConseilRequest $request): RedirectResponse
    {
        Conseil::create($request->validated());

        return redirect()
            ->route('admin.conseils.index')
            ->with('success', 'Le conseil a été créé avec succès.');
    }

    public function show(Conseil $conseil): View
    {
        return view('back.conseils.show', compact('conseil'));
    }

    public function edit(Conseil $conseil): View
    {
        return view('back.conseils.edit', [
            'conseil' => $conseil,
            'categories' => CategorieConseil::orderBy('nom')->get(),
        ]);
    }

    public function update(
        UpdateConseilRequest $request,
        Conseil $conseil
    ): RedirectResponse {
        $conseil->update($request->validated());

        return redirect()
            ->route('admin.conseils.index')
            ->with('success', 'Le conseil a été modifié avec succès.');
    }

    public function destroy(Conseil $conseil): RedirectResponse
    {
        $conseil->delete();

        return redirect()
            ->route('admin.conseils.index')
            ->with('success', 'Le conseil a été supprimé avec succès.');
    }

    /**
     * Génération du contenu d'un conseil par IA.
     */
    public function generer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'titre' => ['required', 'string', 'max:150'],
            'categorie' => ['nullable', 'string', 'max:100'],
        ]);

        $cle = config('services.openrouter.conseil_key') ?: config('services.openrouter.key');
        $model = config('services.openrouter.conseil_model') ?: config('services.openrouter.model') ?: 'openrouter/free';

        if (! $cle) {
            return response()->json([
                'message' => 'La clé API OpenRouter est absente. Configurez OPENROUTER_CONSEIL_API_KEY ou OPENROUTER_API_KEY dans le fichier .env.',
            ], 503);
        }

        if (! $model) {
            return response()->json([
                'message' => 'Le modèle OpenRouter est absent. Configurez OPENROUTER_CONSEIL_MODEL dans le fichier .env.',
            ], 503);
        }

        try {
            $options = [];
            $caBundle = config('services.openrouter.ca_bundle');
            if ($caBundle && file_exists($caBundle)) {
                $options['verify'] = $caBundle;
            }

            $reponse = Http::withToken($cle)
                ->withOptions($options)
                ->acceptJson()
                ->asJson()
                ->timeout(60)
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => $model,
                    'max_tokens' => 1200,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Tu rediges des conseils pratiques, clairs et prudents, en francais, '
                                . 'pour les habitants du Grand Tunis pendant les canicules et les coupures de courant. '
                                . 'Reponds uniquement avec le texte du conseil : 2 ou 3 courts paragraphes, '
                                . 'environ 100 a 150 mots, sans titre, sans liste et sans mise en forme.',
                        ],
                        [
                            'role' => 'user',
                            'content' => 'Redige le contenu du conseil intitule : "'
                                . $data['titre']
                                . '"'
                                . (
                                    ! empty($data['categorie'])
                                        ? ' (categorie : ' . $data['categorie'] . ')'
                                        : ''
                                )
                                . '.',
                        ],
                    ],
                ]);
        } catch (\Throwable $e) {
            Log::error('Erreur connexion OpenRouter', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Impossible de contacter le service IA. Verifiez votre connexion puis reessayez.',
            ], 502);
        }

        if ($reponse->failed()) {
            $detailsErreur = strtolower((string) $reponse->json('error.message', ''));

            Log::error('Erreur API OpenRouter', [
                'status' => $reponse->status(),
                'message' => $reponse->json('error.message'),
            ]);

            $message = match ($reponse->status()) {
                401, 403 => 'La cle API OpenRouter est invalide ou inactive. Verifiez OPENROUTER_API_KEY dans le fichier .env.',
                429 => 'La limite de requetes gratuites est atteinte ou les modeles gratuits sont occupes. Reessayez dans quelques instants.',
                402 => 'OpenRouter demande des credits pour cette requete. Verifiez que le modele configure est openrouter/free.',
                default => str_contains($detailsErreur, 'no endpoints')
                    ? 'Aucun modele gratuit n\'est disponible pour le moment. Reessayez dans quelques instants.'
                    : 'Le service IA n\'a pas pu generer le texte. Reessayez plus tard.',
            };

            return response()->json([
                'message' => $message,
            ], $reponse->status() === 429 ? 429 : 502);
        }

        $texte = trim((string) $reponse->json('choices.0.message.content', ''));

        if ($texte === '') {
            $choix = $reponse->json('choices.0', []);
            Log::warning('OpenRouter a termine sans texte final', [
                'model' => $reponse->json('model'),
                'finish_reason' => $choix['finish_reason'] ?? null,
                'has_reasoning' => ! empty($choix['message']['reasoning']),
            ]);

            return response()->json([
                'message' => ($choix['finish_reason'] ?? null) === 'length'
                    ? 'Le modele gratuit a atteint sa limite avant de terminer le texte. Cliquez de nouveau sur « Rediger avec l’IA ». '
                        . 'Si le probleme continue, choisissez un autre modele gratuit dans la configuration.'
                    : 'Le modele gratuit n\'a pas retourne de texte final. Reessayez dans quelques instants.',
            ], 502);
        }

        return response()->json([
            'contenu' => $texte,
        ]);
    }
}
