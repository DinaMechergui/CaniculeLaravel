<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlerteAiService
{
    /**
     * Génère un bulletin d'alerte météo complet par IA à partir de mots-clés.
     *
     * @param string $prompt Mots-clés saisis par l'utilisateur (ex: "Kairouan, 46°C, Sirocco violent")
     * @return array Résultat structuré (titre, niveau_id, couleur, message, source, mode)
     */
    public function generateAlert(string $prompt): array
    {
        $apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');

        if (!empty($apiKey)) {
            try {
                $aiResponse = $this->callGeminiApi($prompt, $apiKey);
                if ($aiResponse) {
                    return $this->formatResult($aiResponse, 'Gemini 1.5 Flash (IA Cloud)');
                }
            } catch (\Throwable $e) {
                Log::warning('Échec appel Gemini API, tentative avec OpenRouter : ' . $e->getMessage());
            }
        }

        $openRouterKey = config('services.openrouter.key') ?? env('OPENROUTER_API_KEY');
        if (!empty($openRouterKey)) {
            try {
                $aiResponse = $this->callOpenRouterApi($prompt, $openRouterKey);
                if ($aiResponse) {
                    return $this->formatResult($aiResponse, 'OpenRouter GPT-4o-mini (IA Cloud)');
                }
            } catch (\Throwable $e) {
                Log::warning('Échec appel OpenRouter API pour alerte météo, bascule sur le moteur expert local : ' . $e->getMessage());
            }
        }

        // Moteur expert intelligent de secours (fonctionne hors-ligne et sans clé)
        $localResult = $this->generateWithLocalExpert($prompt);
        return $this->formatResult($localResult, 'Moteur IA Expert Météo Tunisie (Local)');
    }

    /**
     * Appel à l'API OpenRouter (GPT-4o-mini).
     */
    protected function callOpenRouterApi(string $prompt, string $apiKey): ?array
    {
        $systemInstruction = "Tu es un expert météorologue officiel de l'Institut National de la Météorologie de Tunisie (INM) et de la Protection Civile Tunisienne. "
            . "À partir des mots-clés fournis par l'utilisateur, rédige un bulletin officiel d'alerte météo pour la Tunisie. "
            . "Tu dois impérativement répondre UNIQUEMENT au format JSON strict avec les champs suivants : "
            . "{"
            . "  \"titre\": \"string court et percutant de max 120 caractères (ex: Vigilance Rouge : Canicule Extrême et Sirocco - Kairouan)\","
            . "  \"couleur\": \"vert|jaune|orange|rouge (choisis selon la gravité de la température et du phénomène)\","
            . "  \"message\": \"bulletin détaillé en français contenant le diagnostic météorologique, les prévisions, les consignes médicales claires (hydratation, personnes âgées, volets fermés) et les numéros d'urgence tunisiens 198 (Protection Civile) et 190 (SAMU).\","
            . "  \"source\": \"Institut National de la Météorologie (INM)\""
            . "}";

        $caBundle = config('services.openrouter.ca_bundle');
        $options = ($caBundle && file_exists($caBundle)) ? ['verify' => $caBundle] : [];

        $model = config('services.openrouter.model', 'openai/gpt-4o-mini');
        $url = config('services.openrouter.url', 'https://openrouter.ai/api/v1/chat/completions');

        $response = Http::withToken($apiKey)
            ->withOptions($options)
            ->timeout(25)
            ->post($url, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemInstruction],
                    ['role' => 'user', 'content' => "Mots-clés de l'alerte : " . $prompt]
                ],
                'temperature' => 0.3,
            ]);

        if ($response->successful()) {
            $content = $response->json('choices.0.message.content');
            if ($content) {
                $clean = preg_replace('/^```(?:json)?\s*/i', '', trim($content));
                $clean = preg_replace('/\s*```$/', '', $clean);
                $decoded = json_decode($clean, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        return null;
    }

    /**
     * Appel à l'API Google Gemini (Free Tier).
     */
    protected function callGeminiApi(string $prompt, string $apiKey): ?array
    {
        $systemInstruction = "Tu es un expert météorologue officiel de l'Institut National de la Météorologie de Tunisie (INM) et de la Protection Civile Tunisienne. "
            . "À partir des mots-clés fournis par l'utilisateur, rédige un bulletin officiel d'alerte météo pour la Tunisie. "
            . "Tu dois impérativement répondre au format JSON strict avec les champs suivants : "
            . "{"
            . "  \"titre\": \"string court et percutant de max 120 caractères (ex: Vigilance Rouge : Canicule Extrême et Sirocco - Kairouan)\","
            . "  \"couleur\": \"vert|jaune|orange|rouge (choisis selon la gravité de la température et du phénomène)\","
            . "  \"message\": \"bulletin détaillé en français contenant le diagnostic météorologique, les prévisions, les consignes médicales claires (hydratation, personnes âgées, volets fermés) et les numéros d'urgence tunisiens 198 (Protection Civile) et 190 (SAMU).\","
            . "  \"source\": \"Institut National de la Météorologie (INM)\""
            . "}";

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

        $response = Http::timeout(10)->post($url, [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $systemInstruction . "\n\nMots-clés de l'alerte : " . $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.4,
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if ($text) {
                return json_decode($text, true);
            }
        }

        return null;
    }

    /**
     * Moteur expert local déterministe et intelligent pour la Tunisie.
     */
    protected function generateWithLocalExpert(string $prompt): array
    {
        $lower = mb_strtolower($prompt);

        // Détection de la température
        preg_match('/(\d{1,2})\s*(°c|degres|c)?/i', $prompt, $matches);
        $temp = isset($matches[1]) ? (int) $matches[1] : null;

        // Détection de la ville tunisienne
        $villes = [
            'kairouan' => 'Kairouan',
            'tozeur' => 'Tozeur',
            'kebili' => 'Kébili',
            'kébili' => 'Kébili',
            'tunis' => 'Grand Tunis (Tunis, Ariana)',
            'ariana' => 'Ariana',
            'ben arous' => 'Ben Arous',
            'manouba' => 'Manouba',
            'sousse' => 'Sousse',
            'monastir' => 'Monastir',
            'mahdia' => 'Mahdia',
            'sfax' => 'Sfax & Kerkennah',
            'bizerte' => 'Bizerte',
            'nabeul' => 'Nabeul & Cap Bon',
            'jendouba' => 'Jendouba & Aïn Draham',
            'beja' => 'Béja',
            'béja' => 'Béja',
            'le kef' => 'Le Kef',
            'kef' => 'Le Kef',
            'siliana' => 'Siliana',
            'zaghouan' => 'Zaghouan',
            'gafsa' => 'Gafsa',
            'sidi bouzid' => 'Sidi Bouzid',
            'kasserine' => 'Kasserine',
            'gabes' => 'Gabès',
            'gabès' => 'Gabès',
            'medenine' => 'Médenine & Djerba',
            'médenine' => 'Médenine & Djerba',
            'tataouine' => 'Tataouine & Remada',
            'tabarka' => 'Tabarka & Littoral Nord',
        ];

        $villeDetectee = 'Tunisie (Régions intérieures)';
        foreach ($villes as $key => $nom) {
            if (str_contains($lower, $key)) {
                $villeDetectee = $nom;
                break;
            }
        }

        // Détermination du niveau de gravité (vert, jaune, orange, rouge)
        $couleur = 'jaune';
        if (($temp && $temp >= 45) || str_contains($lower, 'extrême') || str_contains($lower, 'extreme') || str_contains($lower, 'record') || str_contains($lower, 'critique') || str_contains($lower, 'danger')) {
            $couleur = 'rouge';
        } elseif (($temp && $temp >= 40) || str_contains($lower, 'sirocco') || str_contains($lower, 'chhili') || str_contains($lower, 'incendie') || str_contains($lower, 'violent') || str_contains($lower, 'vague')) {
            $couleur = 'orange';
        } elseif (($temp && $temp <= 32) || str_contains($lower, 'normal') || str_contains($lower, 'brise') || str_contains($lower, 'calme') || str_contains($lower, 'doux')) {
            $couleur = 'vert';
        }

        // Construction du titre et du message officiel
        switch ($couleur) {
            case 'rouge':
                $titre = "Vigilance Rouge : Canicule Extrême et Pic Thermique - {$villeDetectee}";
                $message = "URGENCE MÉTÉOROLOGIQUE - INM TUNISIE :\n"
                    . "Des conditions météorologiques exceptionnelles et particulièrement dangereuses sont en cours sur {$villeDetectee}. "
                    . ($temp ? "Les températures maximales atteignent {$temp}°C à l'ombre avec présence d'un vent de sirocco ardent. " : "Températures extrêmes bien au-dessus des normales saisonnières. ")
                    . "Risque vital immédiat de déshydratation aiguë et de coup de chaleur, en particulier pour les nourrissons et les aînés.\n\n"
                    . "CONSIGNES SANITAIRES IMPÉRATIVES :\n"
                    . "• Évitez toute sortie extérieure ou activité physique entre 11h et 17h.\n"
                    . "• Buvez de l'eau fréquemment même en l'absence de soif.\n"
                    . "• Maintenez les fenêtres et volets fermés le jour, aérez en soirée.\n"
                    . "• Rejoignez les points de fraîcheur communaux climatisés si votre logement est trop chaud.\n\n"
                    . "NUMÉROS D'URGENCE ACTIFS 24H/24 :\n"
                    . "• Protection Civile : 198\n"
                    . "• SAMU Tunisie : 190";
                break;

            case 'orange':
                $titre = "Vigilance Orange : Vague de Chaleur et Sirocco Ardent - {$villeDetectee}";
                $message = "BULLETIN D'ALERTE OFFICIEL DE L'INM :\n"
                    . "Une vague de chaleur marquée accompagnée de vent de sirocco (Chhili) touche le secteur de {$villeDetectee}. "
                    . ($temp ? "Le mercure devrait grimper jusqu'à {$temp}°C aux heures de pointe. " : "Hausse sensible des températures diurnes. ")
                    . "La sécheresse de l'air augmente également le risque d'incendies dans les zones de couvert végétal.\n\n"
                    . "RECOMMANDATIONS DE PRÉVENTION :\n"
                    . "• Hydratez-vous régulièrement et privilégiez les repas légers.\n"
                    . "• Ne laissez jamais d'enfants ou d'animaux dans un véhicule stationné.\n"
                    . "• Prenez des nouvelles des proches et voisins vulnérables ou isolés.\n\n"
                    . "En cas de malaise ou début de feu : contactez le 198 (Protection Civile).";
                break;

            case 'vert':
                $titre = "Vigilance Verte : Conditions Météorologiques Favorables - {$villeDetectee}";
                $message = "BULLETIN DE SITUATION NORMALE - INM :\n"
                    . "Le temps est calme et les températures sont conformes aux moyennes de saison sur {$villeDetectee}. "
                    . ($temp ? "Température modérée relevée : {$temp}°C. " : "")
                    . "Ciel généralement dégagé et brise marine agréable sur le littoral.\n\n"
                    . "Aucune restriction particulière pour les activités quotidiennes et les déplacements.";
                break;

            default: // jaune
                $titre = "Vigilance Jaune : Hausse des Températures - {$villeDetectee}";
                $message = "AVIS DE VIGILANCE - INM TUNISIE :\n"
                    . "Une élévation modérée des températures est signalée sur {$villeDetectee}. "
                    . ($temp ? "Température prévue : {$temp}°C. " : "")
                    . "Une sensation de chaleur lourde peut se faire ressentir en milieu de journée.\n\n"
                    . "CONSIGNES :\n"
                    . "• Soyez attentifs si vous pratiquez des activités en plein soleil.\n"
                    . "• Pensez à emporter une gourde d'eau lors de vos déplacements.\n"
                    . "• Portez des vêtements amples et un chapeau.";
                break;
        }

        return [
            'titre' => $titre,
            'couleur' => $couleur,
            'message' => $message,
            'source' => 'Institut National de la Météorologie (INM)',
        ];
    }

    /**
     * Formate la réponse finale et associe l'ID du NiveauAlerte en base.
     */
    protected function formatResult(array $data, string $provider): array
    {
        $couleur = strtolower($data['couleur'] ?? 'jaune');
        if (!in_array($couleur, ['vert', 'jaune', 'orange', 'rouge'])) {
            $couleur = 'jaune';
        }

        $libelle = match ($couleur) {
            'vert' => 'Vigilance verte',
            'jaune' => 'Vigilance jaune',
            'orange' => 'Vigilance orange',
            'rouge' => 'Vigilance rouge (danger maximal)',
            default => ucfirst($couleur),
        };

        return [
            'success' => true,
            'titre' => mb_substr($data['titre'] ?? 'Alerte Météorologique', 0, 150),
            'couleur' => $couleur,
            'niveau' => $couleur,
            'niveau_libelle' => $libelle,
            'message' => $data['message'] ?? '',
            'source' => $data['source'] ?? 'Institut National de la Météorologie (INM)',
            'provider' => $provider,
        ];
    }
}
