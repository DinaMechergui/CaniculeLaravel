<?php

namespace App\Services;

use App\Models\Alerte;
use App\Models\BulletinAlerte;
use App\Models\Diffusion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlerteAiService
{
    /**
     * Génère un bulletin de suivi complet par IA pour une alerte donnée.
     *
     * @param Alerte $alerte L'alerte parente
     * @param string|null $keywords Mots-clés ou contexte saisi par l'admin
     * @return array Résultat structuré (niveau, evolution, message, conduite_a_tenir, temperature_max, provider)
     */
    public function generateBulletin(Alerte $alerte, ?string $keywords = null): array
    {
        $dernierBulletin = $alerte->dernierBulletin;
        $prevLevel = $dernierBulletin?->niveau ?? 'jaune';

        $apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
        if (!empty($apiKey)) {
            try {
                $result = $this->callGeminiForBulletin($alerte, $keywords, $apiKey);
                if ($result) {
                    return $this->formatBulletinResult($result, $prevLevel, 'Google Gemini 1.5 Flash (IA Cloud)');
                }
            } catch (\Throwable $e) {
                Log::warning('Échec appel Gemini pour bulletin : ' . $e->getMessage());
            }
        }

        $openRouterKey = config('services.openrouter.key') ?? env('OPENROUTER_API_KEY');
        if (!empty($openRouterKey)) {
            try {
                $result = $this->callOpenRouterForBulletin($alerte, $keywords, $openRouterKey);
                if ($result) {
                    return $this->formatBulletinResult($result, $prevLevel, 'OpenRouter GPT-4o-mini (IA Cloud)');
                }
            } catch (\Throwable $e) {
                Log::warning('Échec appel OpenRouter pour bulletin : ' . $e->getMessage());
            }
        }

        // Moteur expert météorologique local déterministe
        $local = $this->generateLocalBulletin($alerte, $keywords, $prevLevel);
        return $this->formatBulletinResult($local, $prevLevel, 'Moteur Expert IA Météo Tunisie (Local)');
    }

    /**
     * Adapte le contenu d'un bulletin aux contraintes d'un canal de diffusion spécifique.
     *
     * @param BulletinAlerte $bulletin
     * @param string $canal
     * @return array Résultat adapté (contenu max 160 chars, destinataires_estimes, provider)
     */
    public function adaptDiffusion(BulletinAlerte $bulletin, string $canal): array
    {
        $apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
        if (!empty($apiKey)) {
            try {
                $result = $this->callGeminiForDiffusion($bulletin, $canal, $apiKey);
                if ($result && !empty($result['contenu'])) {
                    return $this->formatDiffusionResult($result, $canal, 'Google Gemini 1.5 Flash (IA Cloud)');
                }
            } catch (\Throwable $e) {
                Log::warning('Échec appel Gemini pour diffusion : ' . $e->getMessage());
            }
        }

        $openRouterKey = config('services.openrouter.key') ?? env('OPENROUTER_API_KEY');
        if (!empty($openRouterKey)) {
            try {
                $result = $this->callOpenRouterForDiffusion($bulletin, $canal, $openRouterKey);
                if ($result && !empty($result['contenu'])) {
                    return $this->formatDiffusionResult($result, $canal, 'OpenRouter GPT-4o-mini (IA Cloud)');
                }
            } catch (\Throwable $e) {
                Log::warning('Échec appel OpenRouter pour diffusion : ' . $e->getMessage());
            }
        }

        // Moteur expert de condensation multi-canaux local
        $local = $this->generateLocalDiffusion($bulletin, $canal);
        return $this->formatDiffusionResult($local, $canal, 'Moteur Expert IA Multi-Canaux (Local)');
    }

    /*
    |--------------------------------------------------------------------------
    | Appels Cloud (Gemini & OpenRouter)
    |--------------------------------------------------------------------------
    */

    protected function callGeminiForBulletin(Alerte $alerte, ?string $keywords, string $apiKey): ?array
    {
        $prompt = "Tu es un expert météorologue officiel de l'Institut National de la Météorologie de Tunisie (INM) et de la Protection Civile Tunisienne. "
            . "Rédige le prochain bulletin de suivi pour l'alerte en cours suivante :\n"
            . "- Titre : {$alerte->titre}\n"
            . "- Type : {$alerte->type_label}\n"
            . "- Zone concernée : {$alerte->zone_concernee}\n"
            . "- Dernier niveau de vigilance connu : " . ($alerte->niveau_actuel ?? 'jaune') . "\n"
            . "- Contexte / observations : " . ($keywords ?: 'Maintien de la vigilance météo') . "\n\n"
            . "Réponds STRICTEMENT au format JSON avec ces champs exacts :\n"
            . "{\n"
            . "  \"niveau\": \"vert|jaune|orange|rouge\",\n"
            . "  \"evolution\": \"aggravation|stable|amelioration|levee\",\n"
            . "  \"message\": \"Texte du bulletin officiel avec diagnostic météo en Tunisie\",\n"
            . "  \"conduite_a_tenir\": \"Consignes sanitaires et sécuritaires concrètes avec numéros 198 (Protection Civile) et 190 (SAMU)\",\n"
            . "  \"temperature_max\": 42\n"
            . "}";

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

        $response = Http::timeout(10)->post($url, [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.3,
            ]
        ]);

        if ($response->successful()) {
            $text = $response->json('candidates.0.content.parts.0.text');
            if ($text) {
                return json_decode($text, true);
            }
        }

        return null;
    }

    protected function callOpenRouterForBulletin(Alerte $alerte, ?string $keywords, string $apiKey): ?array
    {
        $prompt = "Expert INM Tunisie. Bulletin météo pour alerte {$alerte->titre} ({$alerte->zone_concernee}), type {$alerte->type_label}, niveau actuel {$alerte->niveau_actuel}. Mots-clés : " . ($keywords ?: 'vigilance');

        $url = config('services.openrouter.url', 'https://openrouter.ai/api/v1/chat/completions');
        $model = config('services.openrouter.model', 'openai/gpt-4o-mini');

        $response = Http::withToken($apiKey)->timeout(15)->post($url, [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => "Réponds UNIQUEMENT en JSON avec : niveau (vert|jaune|orange|rouge), evolution (aggravation|stable|amelioration|levee), message, conduite_a_tenir, temperature_max (int ou null)."],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.3,
        ]);

        if ($response->successful()) {
            $content = $response->json('choices.0.message.content');
            if ($content) {
                $clean = preg_replace('/^```(?:json)?\s*/i', '', trim($content));
                $clean = preg_replace('/\s*```$/', '', $clean);
                return json_decode($clean, true);
            }
        }

        return null;
    }

    protected function callGeminiForDiffusion(BulletinAlerte $bulletin, string $canal, string $apiKey): ?array
    {
        $alerte = $bulletin->alerte;
        $prompt = "Tu es responsable de la communication d'urgence de la Protection Civile Tunisienne. "
            . "Condense et adapte le bulletin météo suivant spécifiquement pour le canal '{$canal}' :\n"
            . "- Alerte : {$alerte->titre} ({$alerte->zone_concernee})\n"
            . "- Niveau : {$bulletin->niveau_label}\n"
            . "- Évolution : {$bulletin->evolution_label}\n"
            . "- Message : {$bulletin->message}\n\n"
            . "Contraintes impératives :\n"
            . "- Pour canal 'sms' : MAXIMUM 160 caractères strictement. Clair, concis, percutant.\n"
            . "- Pour 'notification_push' : court avec emoji d'alerte (max 120 caractères).\n"
            . "- Pour 'affichage' : court en majuscules pour panneaux lumineux urbains (max 90 caractères).\n"
            . "- Pour 'sirene' : message d'annonce d'urgence sonore (max 140 caractères).\n"
            . "- Pour 'email' : message clair avec consignes de sécurité (max 160 caractères).\n"
            . "Réponds UNIQUEMENT en JSON :\n"
            . "{\"contenu\": \"texte adapté sous 160 caractères\", \"destinataires_estimes\": 25000}";

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

        $response = Http::timeout(10)->post($url, [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.2,
            ]
        ]);

        if ($response->successful()) {
            $text = $response->json('candidates.0.content.parts.0.text');
            if ($text) {
                return json_decode($text, true);
            }
        }

        return null;
    }

    protected function callOpenRouterForDiffusion(BulletinAlerte $bulletin, string $canal, string $apiKey): ?array
    {
        $url = config('services.openrouter.url', 'https://openrouter.ai/api/v1/chat/completions');
        $model = config('services.openrouter.model', 'openai/gpt-4o-mini');

        $prompt = "Adapte ce bulletin météo pour le canal {$canal} en Tunisie : {$bulletin->alerte->zone_concernee}, {$bulletin->niveau_label}, {$bulletin->message}.";

        $response = Http::withToken($apiKey)->timeout(15)->post($url, [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => "Réponds en JSON avec 'contenu' (maximum absolu 160 caractères) et 'destinataires_estimes' (nombre entier)."],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.2,
        ]);

        if ($response->successful()) {
            $content = $response->json('choices.0.message.content');
            if ($content) {
                $clean = preg_replace('/^```(?:json)?\s*/i', '', trim($content));
                $clean = preg_replace('/\s*```$/', '', $clean);
                return json_decode($clean, true);
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Moteurs Experts Locaux (Offline / Fallback 100% fiable)
    |--------------------------------------------------------------------------
    */

    protected function generateLocalBulletin(Alerte $alerte, ?string $keywords, string $prevLevel): array
    {
        $kw = mb_strtolower((string) $keywords);
        $zone = $alerte->zone_concernee;
        $type = $alerte->type;

        // Détection de température dans le texte
        preg_match('/(\d{1,2})\s*(°c|degres|c)?/i', (string) $keywords, $matches);
        $temp = isset($matches[1]) ? (int) $matches[1] : ($type === 'canicule' ? 44 : null);

        // Détermination logique de l'évolution et du niveau
        if (str_contains($kw, 'fin') || str_contains($kw, 'normal') || str_contains($kw, 'levee') || str_contains($kw, 'calme')) {
            $evolution = 'levee';
            $niveau = 'vert';
            $message = "Fin de vigilance météorologique sur {$zone}. Les paramètres observés sont revenus aux normales saisonnières.";
            $conduite = "Les restrictions et consignes d'urgence sont levées. Reprise normale de toutes les activités.";
        } elseif (str_contains($kw, 'aggrav') || str_contains($kw, 'pire') || str_contains($kw, 'record') || str_contains($kw, 'danger') || ($temp && $temp >= 45)) {
            $evolution = 'aggravation';
            $niveau = 'rouge';
            $message = "VIGILANCE MAXIMALE - INM TUNISIE : Aggravation critique de la situation sur {$zone}."
                . ($temp ? " Maximales attendues jusqu'à {$temp}°C avec sirocco violent." : " Phénomène d'intensité majeure en cours.");
            $conduite = "Restez confinés dans des zones fraîches ou climatisées. Évitez tout déplacement non urgent. Urgences : 198 (Protection Civile) ou 190 (SAMU).";
        } elseif (str_contains($kw, 'amelior') || str_contains($kw, 'baisse') || str_contains($kw, 'brise')) {
            $evolution = 'amelioration';
            $niveau = ($prevLevel === 'rouge') ? 'orange' : 'jaune';
            $message = "Amélioration progressive des conditions météo sur {$zone}. Le pic d'intensité commence à faiblir.";
            $conduite = "Maintenez la prudence. Continuez d'hydrater les personnes vulnérables en attendant le retour au calme complet.";
        } else {
            // Évolution stable par défaut
            $evolution = 'stable';
            $niveau = in_array($prevLevel, ['jaune', 'orange', 'rouge']) ? $prevLevel : 'orange';
            $message = "Situation météorologique stationnaire sur {$zone}. Les conditions restent sensibles et nécessitent une attention soutenue.";
            $conduite = "Fermez les fenêtres aux heures les plus chaudes, buvez de l'eau régulièrement et surveillez les prochains bulletins de l'INM.";
        }

        return [
            'niveau' => $niveau,
            'evolution' => $evolution,
            'message' => $message,
            'conduite_a_tenir' => $conduite,
            'temperature_max' => $temp,
        ];
    }

    protected function generateLocalDiffusion(BulletinAlerte $bulletin, string $canal): array
    {
        $alerte = $bulletin->alerte;
        $zone = $bulletin->alerte->zone_concernee;
        $niveauUpper = strtoupper($bulletin->niveau);

        switch ($canal) {
            case 'sms':
                $text = "ALERTE INM [{$niveauUpper}] : {$zone}. {$bulletin->evolution_label}. Restez vigilants. Consignes & urgence au 198.";
                $destinataires = 25000;
                break;

            case 'notification_push':
                $text = "⚠️ Alerte Météo {$niveauUpper} sur {$zone} : {$bulletin->evolution_label}. Suivez les consignes officielles.";
                $destinataires = 60000;
                break;

            case 'affichage':
                $text = "ALERTE METEO {$niveauUpper} - SECTEUR {$zone} - REDUISEZ VOTRE VITESSE ET RESTEZ AU FRAIS";
                $destinataires = 45000;
                break;

            case 'sirene':
                $text = "Déclenchement sirène communale d'urgence : {$zone} en vigilance {$bulletin->niveau}. Rejoignez un abri sécurisé.";
                $destinataires = 12000;
                break;

            default: // email
                $text = "Vigilance {$bulletin->niveau_label} en cours sur {$zone}. Consultez le bulletin officiel et appliquez les consignes du 198.";
                $destinataires = 35000;
                break;
        }

        return [
            'contenu' => mb_substr($text, 0, 160),
            'destinataires_estimes' => $destinataires,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Sanitisation & Validation des sorties
    |--------------------------------------------------------------------------
    */

    protected function formatBulletinResult(array $data, string $prevLevel, string $provider): array
    {
        $niveau = strtolower($data['niveau'] ?? 'jaune');
        if (!in_array($niveau, ['vert', 'jaune', 'orange', 'rouge'])) {
            $niveau = 'jaune';
        }

        $evolution = strtolower($data['evolution'] ?? 'stable');
        if (!in_array($evolution, ['aggravation', 'stable', 'amelioration', 'levee'])) {
            $evolution = 'stable';
        }

        // Règle métier : si aggravation, le rang doit être >= au niveau précédent
        $ranks = BulletinAlerte::NIVEAU_RANKS;
        if ($evolution === 'aggravation' && isset($ranks[$prevLevel], $ranks[$niveau])) {
            if ($ranks[$niveau] < $ranks[$prevLevel]) {
                $niveau = $prevLevel; // Ajustement automatique par cohérence
            }
        }

        // Si levée, le niveau recommandé est vert
        if ($evolution === 'levee') {
            $niveau = 'vert';
        }

        return [
            'success' => true,
            'niveau' => $niveau,
            'niveau_label' => BulletinAlerte::NIVEAUX[$niveau] ?? ucfirst($niveau),
            'evolution' => $evolution,
            'evolution_label' => BulletinAlerte::EVOLUTIONS[$evolution] ?? ucfirst($evolution),
            'message' => (string) ($data['message'] ?? 'Bulletin météorologique officiel.'),
            'conduite_a_tenir' => (string) ($data['conduite_a_tenir'] ?? 'Respecter les consignes de prudence de la Protection Civile.'),
            'temperature_max' => !empty($data['temperature_max']) ? (int) $data['temperature_max'] : null,
            'provider' => $provider,
        ];
    }

    protected function formatDiffusionResult(array $data, string $canal, string $provider): array
    {
        $contenu = trim((string) ($data['contenu'] ?? ''));
        // Enforce maximum 160 characters
        if (mb_strlen($contenu) > 160) {
            $contenu = mb_substr($contenu, 0, 157) . '...';
        }

        $destinataires = !empty($data['destinataires_estimes']) ? (int) $data['destinataires_estimes'] : 20000;

        return [
            'success' => true,
            'canal' => $canal,
            'canal_label' => Diffusion::CANAUX[$canal] ?? ucfirst($canal),
            'contenu' => $contenu,
            'destinataires_estimes' => $destinataires,
            'provider' => $provider,
        ];
    }
}
