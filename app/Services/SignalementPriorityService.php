<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SignalementPriorityService
{
    private const PRIORITIES = ['faible', 'moyenne', 'urgente'];

    public function determinePriority(string $description): string
    {
        $apiKey = config('services.gemini.key');
        $model  = config('services.gemini.model', 'gemini-2.5-flash');

        if (empty($apiKey)) {
            Log::warning('Clé API Gemini non configurée, utilisation du fallback local.');
            return $this->fallbackPriority($description);
        }

       $prompt = "Tu es un assistant de tri de signalements. Évalue la gravité du signalement ci-dessous.\n\n"
    . "- urgente : danger pour une personne, problème médical, incendie, fuite de gaz, risque important ou interruption critique.\n"
    . "- moyenne : problème qui gêne réellement l'activité (panne d'équipement, service dégradé) et doit être traité sous quelques jours, mais sans danger.\n"
    . "- faible : gêne mineure, défaut esthétique, intermittent ou sans impact réel sur l'activité ; peut attendre une maintenance planifiée.\n\n"
    . "Si le texte indique que le problème n'est pas très gênant ou peut attendre, choisis faible.\n\n"
    . "Description :\n" . $description;

        try {
            $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
                ->timeout(15)
                ->retry(2, 500, fn ($e) => $e instanceof \Illuminate\Http\Client\ConnectionException, throw: false)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'temperature'      => 0,
                        'maxOutputTokens'  => 20,
                        // Force une réponse parmi les 3 valeurs exactement
                        'responseMimeType' => 'text/x.enum',
                        'responseSchema'   => [
                            'type' => 'STRING',
                            'enum' => self::PRIORITIES,
                        ],
                        // Désactive la "réflexion" : plus rapide et moins de quota consommé
                        'thinkingConfig'   => ['thinkingBudget' => 0],
                    ],
                ]);

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text');

                if (is_string($text)) {
                    $value = preg_replace('/[^a-z]/', '', strtolower(trim($text)));

                    if (in_array($value, self::PRIORITIES, true)) {
                        return $value;
                    }
                }

                Log::warning('Réponse inattendue de l\'IA pour la priorité: ' . $response->body());
            } else {
                Log::error('Erreur API Gemini (' . $response->status() . '): ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error('Exception lors de l\'appel à l\'IA pour la priorité: ' . $e->getMessage());
        }

        return $this->fallbackPriority($description);
    }

    /**
     * Secours simple basé sur des mots-clés si l'IA est indisponible.
     */
    private function fallbackPriority(string $description): string
    {
        $text = mb_strtolower($description);

        $urgent = ['urgence', 'urgent', 'danger', 'blessé', 'blesse', 'incendie', 'feu', 'fumée', 'gaz',
                   'inondation', 'accident', 'malaise', 'ambulance', 'inconscient', 'agression', 'électrocut'];
        foreach ($urgent as $word) {
            if (str_contains($text, $word)) {
                return 'urgente';
            }
        }

$low = ['mineur', 'esthétique', 'suggestion', 'peinture', 'petit', 'pas très gênant', 'de temps en temps', 'clignote', 'pas urgent'];        foreach ($low as $word) {
            if (str_contains($text, $word)) {
                return 'faible';
            }
        }

        return 'moyenne';
    }
}