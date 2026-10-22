<?php
namespace App\BackOffice\Controllers;

use App\BackOffice\Requests\PlanAiRequest;
use App\BackOffice\Requests\StorePlanAiRequest;
use App\Exceptions\AnthropicInvalidResponseException;
use App\Exceptions\AnthropicUnavailableException;
use App\Http\Controllers\Controller;
use App\Models\EtapePlan;
use App\Models\PlanUrgence;
use App\Services\AnthropicService;
use App\Services\PlanUrgenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PlanAiController extends Controller
{
    public function __invoke(PlanAiRequest $request, AnthropicService $ia, PlanUrgenceService $plans): JsonResponse
    {
        $data=$request->validated();
        try {
            return response()->json(match($data['action']) {
                'generer'=>$this->generer($ia,$data),
                'etapes_manquantes'=>$this->etapesManquantes($ia,PlanUrgence::with('etapes')->findOrFail($data['plan_id'])),
                'couverture'=>$this->couverture($ia,$plans),
            });
        } catch (AnthropicUnavailableException $exception) { return response()->json(['message'=>$exception->getMessage()],503); }
        catch (AnthropicInvalidResponseException $exception) { return response()->json(['message'=>$exception->getMessage()],502); }
    }

    public function create(StorePlanAiRequest $request): RedirectResponse
    {
        $data=$request->validated();
        $plan=DB::transaction(function () use ($data) {
            $plan=PlanUrgence::create(['titre'=>$data['titre'],'description'=>$data['description']??null,'type_evenement'=>$data['type_evenement'],'niveau_risque'=>$data['niveau_risque'],'public_cible'=>$data['public_cible'],'actif'=>false]);
            foreach($data['etapes'] as $index=>$etape) $plan->etapes()->create($etape+['ordre'=>$index+1]);
            return $plan;
        });
        return redirect()->route('admin.plans-urgence.show',$plan)->with('success','Le plan IA a été créé en mode inactif.');
    }

    private function generer(AnthropicService $ia, array $d): array
    {
        $prompt = "Tu es un expert en sécurité civile et prévention canicule. "
            . "Propose un plan d’urgence complet et pratique en français. "
            . "Génère exactement 10 étapes structurées réparties entre 'avant', 'pendant' et 'apres'. "
            . "Retourne un objet JSON valide avec les clés : "
            . "- 'titre' : intitulé clair du plan "
            . "- 'description' : résumé pratique du plan "
            . "- 'etapes' : tableau de 8 à 12 objets contenant : 'titre', 'description', 'moment' ('avant', 'pendant' ou 'apres'), 'obligatoire' (booléen true/false).";

        $r = $ia->json($prompt, json_encode([
            'type_evenement' => $d['type_evenement'],
            'niveau_risque' => $d['niveau_risque'],
            'public_cible' => $d['public_cible'],
        ], JSON_UNESCAPED_UNICODE), 3500);

        if (isset($r['plan']) && is_array($r['plan'])) {
            $r = array_merge($r['plan'], $r);
        }

        $this->verifierEtapes($r, 8, 12);

        $titre = trim((string)($r['titre'] ?? ''));
        if ($titre === '') {
            $typeLabel = PlanUrgence::TYPES[$d['type_evenement']] ?? $d['type_evenement'];
            $titre = "Plan d’urgence : {$typeLabel}";
        }
        if (mb_strlen($titre) > 150) {
            $titre = mb_substr($titre, 0, 150);
        }

        $description = trim((string)($r['description'] ?? ''));
        if ($description === '') {
            $description = "Mesures d’urgence et étapes pratiques adaptées au niveau de risque {$d['niveau_risque']}.";
        }
        if (mb_strlen($description) > 1000) {
            $description = mb_substr($description, 0, 1000);
        }

        $r['titre'] = $titre;
        $r['description'] = $description;

        return $r;
    }

    private function etapesManquantes(AnthropicService $ia, PlanUrgence $plan): array
    {
        $prompt = "Tu es un spécialiste de la gestion des urgences canicule. "
            . "Propose des étapes complémentaires manquantes pour ce plan d'urgence (au maximum 5 étapes). "
            . "Retourne un objet JSON contenant la clé 'etapes' avec pour chaque étape : 'titre', 'description', 'moment' ('avant', 'pendant', 'apres') et 'obligatoire' (booléen).";

        $r = $ia->json($prompt, json_encode([
            'plan' => $plan->only(['titre', 'description', 'type_evenement', 'public_cible']),
            'etapes_existantes' => $plan->etapes->map->only(['titre', 'description', 'moment']),
        ], JSON_UNESCAPED_UNICODE), 1600);

        if (isset($r['plan']) && is_array($r['plan'])) {
            $r = array_merge($r['plan'], $r);
        }

        $this->verifierEtapes($r, 0, 5);

        return $r;
    }

    private function normaliserEtape(mixed $step, int $index = 0): array
    {
        if (! is_array($step)) {
            $step = ['titre' => 'Étape ' . ($index + 1)];
        }

        // Titre : sécurisé et tronqué doucement sans lever d'erreur bloquante
        $titre = trim((string)($step['titre'] ?? ($step['nom'] ?? ($step['action'] ?? ''))));
        if ($titre === '') {
            $titre = 'Étape ' . ($index + 1);
        }
        if (mb_strlen($titre) > 150) {
            $titre = mb_substr($titre, 0, 150);
        }

        // Description : optionnelle et tronquée doucement
        $description = trim((string)($step['description'] ?? ($step['detail'] ?? '')));
        if (mb_strlen($description) > 500) {
            $description = mb_substr($description, 0, 500);
        }

        // Moment : normalisation intelligente vers 'avant', 'pendant', 'apres' (supporte les accents 'après')
        $mRaw = mb_strtolower(trim((string)($step['moment'] ?? ($step['phase'] ?? 'pendant'))));
        if (str_contains($mRaw, 'av') || str_contains($mRaw, 'prepar') || str_contains($mRaw, 'anticip')) {
            $moment = 'avant';
        } elseif (str_contains($mRaw, 'ap') || str_contains($mRaw, 'recup') || str_contains($mRaw, 'bilan') || str_contains($mRaw, 'post')) {
            $moment = 'apres';
        } else {
            $moment = 'pendant';
        }

        // Obligatoire : conversion booléenne robuste
        $obligatoire = filter_var($step['obligatoire'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return [
            'titre' => $titre,
            'description' => $description,
            'moment' => $moment,
            'obligatoire' => $obligatoire,
        ];
    }

    private function verifierEtapes(array &$r, int $min, int $max): void
    {
        // Extraction flexible des étapes
        $raw = $r['etapes'] ?? ($r['plan']['etapes'] ?? ($r['steps'] ?? null));
        if (! is_array($raw)) {
            $raw = [];
        }

        $etapes = [];
        foreach ($raw as $i => $step) {
            $etapes[] = $this->normaliserEtape($step, $i);
        }

        // Si le modèle a produit plus d'étapes que le plafond, on coupe proprement
        if (count($etapes) > $max && $max > 0) {
            $etapes = array_slice($etapes, 0, $max);
        }

        // Si le modèle a produit moins d'étapes que le plancher (ex: min 8 pour generer)
        if (count($etapes) < $min) {
            $complements = [
                ['titre' => 'Vérifier la trousse de premiers secours et les réserves d’eau', 'description' => 'Disposer d’au moins 2 litres d’eau potable par personne et par jour.', 'moment' => 'avant', 'obligatoire' => true],
                ['titre' => 'Fermer les volets et fenêtres exposés au soleil', 'description' => 'Maintenir le logement au frais en évitant l’entrée de chaleur.', 'moment' => 'pendant', 'obligatoire' => true],
                ['titre' => 'S’hydrater régulièrement et humidifier sa peau', 'description' => 'Boire de l’eau fréquemment sans attendre d’avoir soif.', 'moment' => 'pendant', 'obligatoire' => true],
                ['titre' => 'Prendre des nouvelles des proches et personnes fragiles', 'description' => 'Contacter quotidiennement les personnes âgées ou isolées.', 'moment' => 'pendant', 'obligatoire' => false],
                ['titre' => 'Aérer le logement aux heures les plus fraîches', 'description' => 'Créer des courants d’air la nuit ou tôt le matin.', 'moment' => 'pendant', 'obligatoire' => false],
                ['titre' => 'Vérifier l’état de santé général des membres du foyer', 'description' => 'Surveiller l’apparition de signes de coup de chaleur ou de déshydratation.', 'moment' => 'apres', 'obligatoire' => true],
                ['titre' => 'Faire le point sur les stocks et réapprovisionner les réserves', 'description' => 'Remplacer le matériel utilisé et préparer les alertes suivantes.', 'moment' => 'apres', 'obligatoire' => false],
                ['titre' => 'Nettoyer et ranger le matériel de rafraîchissement', 'description' => 'Entretenir ventilateurs et brumisateurs pour les prochaines alertes.', 'moment' => 'apres', 'obligatoire' => false],
            ];

            foreach ($complements as $fallback) {
                if (count($etapes) >= $min) {
                    break;
                }
                $titresExistants = array_column($etapes, 'titre');
                if (! in_array($fallback['titre'], $titresExistants, true)) {
                    $etapes[] = $fallback;
                }
            }

            while (count($etapes) < $min) {
                $idx = count($etapes) + 1;
                $etapes[] = [
                    'titre' => 'Étape de sécurité complémentaire ' . $idx,
                    'description' => 'Mesure de précaution additionnelle adaptée à la situation.',
                    'moment' => ($idx % 3 === 0 ? 'avant' : ($idx % 3 === 1 ? 'pendant' : 'apres')),
                    'obligatoire' => false,
                ];
            }
        }

        // S'assurer qu'au moins une étape est obligatoire pour respecter la règle métier
        if (! empty($etapes) && ! in_array(true, array_column($etapes, 'obligatoire'), true)) {
            $etapes[0]['obligatoire'] = true;
        }

        $r['etapes'] = $etapes;
    }

    private function couverture(AnthropicService $ia, PlanUrgenceService $service): array
    {
        $stats = $service->statistiques();
        $input = [
            'types_absents' => $stats['types_sans_plan']->all(),
            'publics_absents' => $stats['publics_sans_plan']->all(),
            'plans_actifs' => PlanUrgence::where('actif', true)->count(),
        ];
        $r = $ia->json('Analyse la couverture de ces plans sans données personnelles. Retourne exactement trois lacunes et trois actions recommandées.', json_encode($input, JSON_UNESCAPED_UNICODE));
        foreach (['lacunes', 'actions'] as $key) {
            $items = [];
            $raw = $r[$key] ?? [];
            if (is_array($raw)) {
                foreach ($raw as $v) {
                    if (is_string($v) && trim($v) !== '') {
                        $items[] = mb_substr(trim($v), 0, 300);
                    }
                }
            }
            while (count($items) < 3) {
                $items[] = $key === 'lacunes' ? 'Couverture générale à consolider.' : 'Compléter les étapes des plans existants.';
            }
            $r[$key] = array_slice($items, 0, 3);
        }
        return $r;
    }
}
