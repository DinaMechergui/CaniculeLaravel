<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service d'appel IA générique et résilient du module « Réservations ».
 *
 * Toutes les fonctions IA du module passent par ce service, qui garantit :
 *
 *  - **Fournisseurs** : Gemini si `GEMINI_API_KEY` est présent, sinon
 *    OpenRouter si `OPENROUTER_API_KEY` est présent, sinon aucun appel réseau.
 *  - **Robustesse** : délai maximum de 15 s, 2 tentatives en cas d'échec
 *    réseau, et SECOURS SYSTÉMATIQUE (jamais d'exception remontée : le appelant
 *    reçoit toujours la valeur de repli fournie).
 *  - **Parsing JSON sûr** : les réponses entourées de barres de code ou
 *    d'un texte sont nettoyées avant décodage ; sinon secours.
 *  - **Cache** : les réponses réussies sont mises en cache (10 min par défaut)
 *    afin de ne pas reconsommer de quota et de rester réactif.
 *  - **Confidentialité** : les adresses e-mail et numéros de téléphone sont
 *    masqués avant envoi au fournisseur (aucune donnée personnelle ne quitte
 *    le serveur).
 */
class AiService
{
    /**
     * Durée de cache par défaut des réponses IA (secondes).
     */
    public const DUREE_CACHE = 600;

    /**
     * Demande structurée (JSON) : retourne TOUJOURS un tableau exploitable.
     *
     * Les clés du tableau `fallback` sont conservées : la réponse de l'IA ne
     * peut que compléter le repli, jamais le vider.
     *
     * @param  array<string, mixed>  $fallback  Valeurs utilisées si l'IA est indisponible.
     * @return array<string, mixed>
     */
    public function json(string $systeme, string $prompt, array $fallback, int $secondes = self::DUREE_CACHE): array
    {
        $reponse = $this->enCache($systeme, $prompt, $secondes, function () use ($systeme, $prompt) {
            $brut = $this->appeler($systeme, $prompt, true);

            return $brut === null ? null : $this->extraireJson($brut);
        });

        if (! is_array($reponse)) {
            return $fallback;
        }

        return array_merge($fallback, $reponse);
    }

    /**
     * Demande de texte libre : retourne TOUJOURS une chaîne non vide.
     */
    public function texte(string $systeme, string $prompt, string $fallback, int $secondes = self::DUREE_CACHE): string
    {
        $reponse = $this->enCache($systeme, $prompt, $secondes, function () use ($systeme, $prompt) {
            return $this->appeler($systeme, $prompt, false);
        });

        if (! is_string($reponse) || trim($reponse) === '') {
            return $fallback;
        }

        return trim($reponse);
    }

    /**
     * Un fournisseur d'IA est-il configuré ?
     */
    public function configure(): bool
    {
        return $this->fournisseur() !== null;
    }

    /**
     * Lecture/écriture du cache : seul un résultat exploitable est conservé,
     * pour que l'appel soit retenté dès que le fournisseur redevient
     * disponible (un échec n'est jamais mis en cache).
     *
     * @param  callable(): (array<string, mixed>|string|null)  $production
     * @return array<string, mixed>|string|null
     */
    protected function enCache(string $systeme, string $prompt, int $secondes, callable $production): array|string|null
    {
        $cle = 'ai-service:' . hash('sha256', $systeme . "\n" . $prompt);

        $resultat = Cache::remember($cle, $secondes, $production);

        if (is_array($resultat)) {
            return $resultat;
        }

        if (is_string($resultat) && trim($resultat) !== '') {
            return $resultat;
        }

        return null;
    }

    /**
     * Fournisseur effectivement utilisable (et sécurité : aucune clé => null).
     */
    protected function fournisseur(): ?string
    {
        if (filled(config('services.gemini.key'))) {
            return 'gemini';
        }

        if (filled(config('services.openrouter.key'))) {
            return 'openrouter';
        }

        return null;
    }

    /**
     * Appel réseau protégé : délai 15 s, 2 tentatives, exceptions avallées.
     * Retourne null si le fournisseur est absent ou si l'appel échoue.
     */
    protected function appeler(string $systeme, string $prompt, bool $json): ?string
    {
        $fournisseur = $this->fournisseur();

        if ($fournisseur === null) {
            return null;
        }

        // Confidentialité : aucune donnée personnelle ne part vers le cloud.
        $systeme = $this->masquerDonneesPerso($systeme);
        $prompt = $this->masquerDonneesPerso($prompt);

        try {
            $texte = $fournisseur === 'gemini'
                ? $this->gemini($systeme, $prompt, $json)
                : $this->openrouter($systeme, $prompt, $json);
        } catch (\Throwable $exception) {
            Log::warning('AiService : appel IA échoué — ' . $exception->getMessage());

            return null;
        }

        return (is_string($texte) && trim($texte) !== '') ? trim($texte) : null;
    }

    /**
     * Appel à l'API Gemini (réponse en JSON si demandé).
     */
    protected function gemini(string $systeme, string $prompt, bool $json): ?string
    {
        $cle = (string) config('services.gemini.key');
        $modele = (string) config('services.gemini.model', 'gemini-2.5-flash');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modele}:generateContent";

        $corps = [
            'systemInstruction' => [
                'parts' => [['text' => $systeme]],
            ],
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $prompt]]],
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 1024,
            ],
        ];

        if ($json) {
            $corps['generationConfig']['responseMimeType'] = 'application/json';
        }

        $reponse = Http::withHeaders(['x-goog-api-key' => $cle])
            ->timeout(15)
            ->retry(2, 500)
            ->post($url, $corps);

        if (! $reponse->successful()) {
            Log::warning('AiService Gemini : HTTP ' . $reponse->status());

            return null;
        }

        return $reponse->json('candidates.0.content.parts.0.text');
    }

    /**
     * Appel à l'API OpenRouter (chat completions).
     */
    protected function openrouter(string $systeme, string $prompt, bool $json): ?string
    {
        $corps = [
            'model' => (string) config('services.openrouter.model', 'openrouter/free'),
            'messages' => [
                ['role' => 'system', 'content' => $systeme],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.2,
            'max_tokens' => 1024,
        ];

        // Instruction explicite pour les modèles qui n'ont pas de mode JSON natif.
        if ($json) {
            $corps['messages'][0]['content'] .= "\n\nRéponds UNIQUEMENT par un objet JSON valide, sans texte autour ni barres de code.";
        }

        $options = [];
        $caBundle = config('services.openrouter.ca_bundle');

        if (is_string($caBundle) && $caBundle !== '' && file_exists($caBundle)) {
            $options['verify'] = $caBundle;
        }

        $reponse = Http::withToken((string) config('services.openrouter.key'))
            ->withOptions($options)
            ->timeout(15)
            ->retry(2, 500)
            ->post((string) config('services.openrouter.url'), $corps);

        if (! $reponse->successful()) {
            Log::warning('AiService OpenRouter : HTTP ' . $reponse->status());

            return null;
        }

        return $reponse->json('choices.0.message.content');
    }

    /**
     * Décodage JSON tolérant : JSON pur, bloc {...} isolé ou code markdown.
     *
     * @return array<string, mixed>|null
     */
    protected function extraireJson(string $texte): ?array
    {
        $texte = trim($texte);

        // Suppression d'une éventuelle barre de code « ```json ... ``` ».
        $texte = (string) preg_replace('/^```(?:json)?\s*/i', '', $texte);
        $texte = (string) preg_replace('/\s*```$/', '', $texte);

        $decode = json_decode($texte, true);

        if (is_array($decode)) {
            return $decode;
        }

        // Recherche du premier objet JSON de la réponse.
        if (preg_match('/\{.*\}/s', $texte, $correspondance) === 1) {
            $decode = json_decode($correspondance[0], true);

            if (is_array($decode)) {
                return $decode;
            }
        }

        return null;
    }

    /**
     * Masque les données personnelles avant transmission au fournisseur.
     */
    protected function masquerDonneesPerso(string $texte): string
    {
        // Adresses e-mail.
        $texte = (string) preg_replace('/[\w.+-]+@[\w-]+\.[\w.]{2,}/i', '[e-mail masqué]', $texte);

        // Numéros de téléphone (Tunisie : +216 / 0 suivis de 8 chiffres).
        $texte = (string) preg_replace(
            '/(?:\+?216[\s.-]?)?[2-57-9]\d[\s.-]?\d{3}[\s.-]?\d{3}/',
            '[téléphone masqué]',
            $texte
        );

        return $texte;
    }
}
