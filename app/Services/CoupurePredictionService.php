<?php

namespace App\Services;

use App\Models\Coupure;
use App\Models\Quartier;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CoupurePredictionService
{
    /**
     * Durées types par défaut en Tunisie (en minutes) selon la cause STEG.
     */
    private const DUREES_DEFAUT = [
        'delestage' => 120, // 2h pour un délestage tournant standard
        'panne'     => 210, // 3h30 pour une panne de transformateur/ligne
        'surcharge' => 240, // 4h pour une surcharge thermique estivale
    ];

    /**
     * Prédit la date/heure de fin estimée pour une coupure d'électricité.
     *
     * @param int|null $quartierId
     * @param string $type ('delestage', 'panne', 'surcharge')
     * @param string|Carbon|null $dateDebut
     * @param string|null $description
     * @return array
     */
    public function predireFin(?int $quartierId, string $type, $dateDebut = null, ?string $description = null): array
    {
        $quartier = $quartierId ? Quartier::find($quartierId) : null;
        $debut = $dateDebut ? Carbon::parse($dateDebut) : Carbon::now();

        // 1. Calcul de la moyenne historique des coupures résolues
        $statsHistoriques = $this->calculerMoyenneHistorique($quartierId, $type);
        $dureeEstimee = $statsHistoriques['duree_minutes'];

        // 2. Appel à l'IA OpenRouter (GPT-4o-mini) pour un calcul contextuel et des explications
        $aiResult = $this->appelerIaPrediction($quartier, $type, $debut, $description, $dureeEstimee);

        if ($aiResult && !empty($aiResult['duree_minutes'])) {
            $dureeEstimee = (int) $aiResult['duree_minutes'];
            $confiance = $aiResult['confiance'] ?? 'Élevée (85%)';
            $explication = $aiResult['explication'] ?? 'Estimation calculée par IA selon le profil du réseau.';
            $facteurs = $aiResult['facteurs'] ?? ['Type d\'incident', 'Historique local'];
            $source = 'Intelligence Artificielle (GPT-4o-mini)';
        } else {
            // Moteur déterministe expert
            $confiance = $statsHistoriques['nb_echantillons'] > 0 ? 'Moyenne (75%)' : 'Standard (65%)';
            $explication = $statsHistoriques['nb_echantillons'] > 0
                ? "Basée sur l'historique de {$statsHistoriques['nb_echantillons']} coupure(s) résolue(s) dans ce quartier."
                : "Durée moyenne constatée sur le réseau STEG pour un incident de type " . ucfirst($type) . ".";
            $facteurs = ['Statistiques historiques STEG', 'Type de coupure'];
            $source = 'Moteur statistique historique';
        }

        $dateFinEstimee = $debut->copy()->addMinutes($dureeEstimee);

        $heures = intdiv($dureeEstimee, 60);
        $minutes = $dureeEstimee % 60;
        $dureeFormatee = ($heures > 0 ? "{$heures}h" : '') . ($minutes > 0 ? sprintf('%02dmin', $minutes) : '');
        if ($dureeFormatee === '') {
            $dureeFormatee = '1h';
        }

        return [
            'duree_minutes'       => $dureeEstimee,
            'duree_formatee'      => $dureeFormatee,
            'date_debut'          => $debut->format('Y-m-d H:i'),
            'date_fin_estimee'    => $dateFinEstimee->format('Y-m-d H:i'),
            'date_fin_iso'        => $dateFinEstimee->format('Y-m-d\TH:i'),
            'date_fin_affichage'  => $dateFinEstimee->translatedFormat('d F Y à H:i'),
            'confiance'           => $confiance,
            'explication'         => $explication,
            'facteurs'            => $facteurs,
            'source'              => $source,
            'quartier'            => $quartier?->nom ?? 'Non spécifié',
            'ville'               => $quartier?->ville ?? 'Tunis',
        ];
    }

    /**
     * Analyse l'historique des coupures passées pour dériver la durée moyenne.
     */
    protected function calculerMoyenneHistorique(?int $quartierId, string $type): array
    {
        $dureeDefaut = self::DUREES_DEFAUT[$type] ?? 180;

        if (!$quartierId) {
            return ['duree_minutes' => $dureeDefaut, 'nb_echantillons' => 0];
        }

        // Chercher les coupures passées terminées avec dates valides
        $coupuresPassees = Coupure::query()
            ->where('quartier_id', $quartierId)
            ->whereNotNull('date_debut')
            ->whereNotNull('date_fin')
            ->where('date_fin', '>', 'date_debut')
            ->get();

        if ($coupuresPassees->isEmpty()) {
            return ['duree_minutes' => $dureeDefaut, 'nb_echantillons' => 0];
        }

        // Calculer la durée moyenne en minutes
        $durees = [];
        foreach ($coupuresPassees as $c) {
            $diff = $c->date_debut->diffInMinutes($c->date_fin);
            if ($diff > 10 && $diff < 1440) { // Entre 10 min et 24h
                // Poids double pour le même type
                if ($c->type === $type) {
                    $durees[] = $diff;
                    $durees[] = $diff;
                } else {
                    $durees[] = $diff;
                }
            }
        }

        if (empty($durees)) {
            return ['duree_minutes' => $dureeDefaut, 'nb_echantillons' => 0];
        }

        $moyenneLocale = (int) round(array_sum($durees) / count($durees));
        // Pondération : 60% historique local, 40% norme théorique STEG
        $dureeFinale = (int) round(($moyenneLocale * 0.6) + ($dureeDefaut * 0.4));

        return [
            'duree_minutes' => max(30, min($dureeFinale, 600)),
            'nb_echantillons' => $coupuresPassees->count(),
        ];
    }

    /**
     * Fait appel à OpenRouter pour estimer intelligemment la fin de la coupure.
     */
    protected function appelerIaPrediction(?Quartier $quartier, string $type, Carbon $debut, ?string $description, int $dureeHistorique): ?array
    {
        $apiKey = config('services.openrouter.key') ?? env('OPENROUTER_API_KEY');
        if (empty($apiKey)) {
            return null;
        }

        $quartierNom = $quartier ? "{$quartier->nom} ({$quartier->ville})" : "Grand Tunis";
        $typeLibelle = match($type) {
            'delestage' => 'Délestage tournant préventif',
            'panne' => 'Panne matérielle / incident sur transformateur',
            'surcharge' => 'Surcharge due à la forte consommation électrique',
            default => $type,
        };

        $systemPrompt = "Tu es un ingénieur expert du réseau électrique tunisien (STEG) et de la Protection Civile. "
            . "À partir des paramètres de la coupure d'électricité fournie, estime avec réalisme la durée restante avant rétablissement et l'heure de fin prévisible. "
            . "Réponds impérativement et UNIQUEMENT avec un objet JSON strict au format suivant : "
            . "{"
            . "  \"duree_minutes\": <nombre entier de minutes, ex: 150>,"
            . "  \"confiance\": \"Élevée (85%)\" ou \"Moyenne (75%)\","
            . "  \"explication\": \"Courte phrase explicative claire et professionnelle (max 150 caractères)\","
            . "  \"facteurs\": [\"facteur 1\", \"facteur 2\"]"
            . "}";

        $userPrompt = "Incident électrique : \n"
            . "- Quartier / Localisation : {$quartierNom}\n"
            . "- Type d'incident : {$typeLibelle}\n"
            . "- Date et heure de début : {$debut->format('d/m/Y H:i')}\n"
            . "- Description : " . ($description ?: 'Aucune description spécifique') . "\n"
            . "- Durée moyenne observée dans ce secteur : {$dureeHistorique} minutes.";

        $caBundle = config('services.openrouter.ca_bundle');
        $options = ($caBundle && file_exists($caBundle)) ? ['verify' => $caBundle] : [];
        $model = config('services.openrouter.model', 'openai/gpt-4o-mini');
        $url = config('services.openrouter.url', 'https://openrouter.ai/api/v1/chat/completions');

        try {
            $response = Http::withToken($apiKey)
                ->withOptions($options)
                ->timeout(15)
                ->post($url, [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => 0.2,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                if ($content) {
                    $clean = preg_replace('/^```(?:json)?\s*/i', '', trim($content));
                    $clean = preg_replace('/\s*```$/', '', $clean);
                    $decoded = json_decode($clean, true);
                    if (is_array($decoded) && isset($decoded['duree_minutes'])) {
                        return $decoded;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Échec prédiction IA coupure : ' . $e->getMessage());
        }

        return null;
    }
}
