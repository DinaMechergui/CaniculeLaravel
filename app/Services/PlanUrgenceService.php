<?php

namespace App\Services;

use App\Models\AlerteMeteo;
use App\Models\EtapePlan;
use App\Models\PlanUrgence;
use App\Models\ProfilFoyer;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlanUrgenceService
{
    public function progression(PlanUrgence $plan, User $user): array
    {
        $ids = $plan->etapes()->pluck('etapes_plan.id');
        $faites = DB::table('etape_plan_user')->where('user_id', $user->id)->whereIn('etape_plan_id', $ids)->pluck('etape_plan_id');
        $obligatoires = $plan->etapes()->where('obligatoire', true)->pluck('id');
        $reste = $obligatoires->diff($faites);
        return ['total' => $ids->count(), 'faites' => $faites->count(), 'pourcentage' => $ids->isEmpty() ? 0 : (int) round($faites->count() * 100 / $ids->count()), 'obligatoires_restantes' => $reste->count(), 'pret' => $reste->isEmpty()];
    }

    public function activer(PlanUrgence $plan): bool
    {
        if ($plan->etapes()->count() < 3 || $plan->etapes()->where('obligatoire', true)->doesntExist()) return false;
        $plan->update(['actif' => true]);
        return true;
    }

    public function dupliquer(PlanUrgence $plan): PlanUrgence
    {
        return DB::transaction(function () use ($plan) {
            $copie = $plan->replicate();
            $copie->titre = 'Copie de '.$plan->titre;
            $copie->actif = false;
            $copie->save();
            foreach ($plan->etapes as $etape) {
                $nouvelle = $etape->replicate();
                $nouvelle->plan_urgence_id = $copie->id;
                $nouvelle->save();
            }
            return $copie;
        });
    }

    public function recommandes(?User $user, Collection $plans): Collection
    {
        $profil = $user?->profilFoyer;
        $alerte = AlerteMeteo::active()->exists();
        return $plans->map(function (PlanUrgence $plan) use ($profil, $alerte) {
            $score = $plan->public_cible === 'tous' ? 1 : 0;
            if ($profil) {
                if ($profil->nb_enfants > 0 && $plan->public_cible === 'enfants') $score += 5;
                if ($profil->nb_personnes_agees > 0 && $plan->public_cible === 'personnes_agees') $score += 5;
                if ($profil->a_animaux && $plan->public_cible === 'animaux') $score += 5;
                if ($profil->nb_enfants > 0 && $plan->public_cible === 'familles') $score += 3;
            }
            if ($alerte && in_array($plan->type_evenement, ['canicule', 'les_deux'], true)) $score += 4;
            $plan->setAttribute('score_recommandation', $score);
            return $plan;
        })->sortByDesc('score_recommandation')->values();
    }

    public function statistiques(): array
    {
        $plans = PlanUrgence::with('etapes.utilisateurs')->get();
        $etapes = $plans->flatMap->etapes;
        $utilisateurs = DB::table('etape_plan_user')->distinct('user_id')->count('user_id');
        $total = $etapes->count();
        $faites = DB::table('etape_plan_user')->whereIn('etape_plan_id', $etapes->pluck('id'))->count();
        return [
            'par_type' => $plans->where('actif', true)->groupBy('type_evenement')->map->count(),
            'par_niveau' => $plans->where('actif', true)->groupBy('niveau_risque')->map->count(),
            'taux_completion' => $total ? round($faites * 100 / ($total * max(1, User::count()))) : 0,
            'etapes_populaires' => $etapes->sortByDesc(fn ($e) => $e->utilisateurs->count())->take(5),
            'etapes_peu_cochees' => $etapes->sortBy(fn ($e) => $e->utilisateurs->count())->take(5),
            'utilisateurs' => $utilisateurs,
            'types_sans_plan' => collect(PlanUrgence::TYPES)->keys()->diff($plans->where('actif', true)->pluck('type_evenement')->unique()),
            'publics_sans_plan' => collect(PlanUrgence::PUBLICS)->keys()->diff($plans->where('actif', true)->pluck('public_cible')->unique()),
            'actions' => ['Créer des plans pour les types et publics absents.', 'Compléter les étapes obligatoires des plans peu suivis.', 'Réviser les plans actifs avant la prochaine période de forte chaleur.'],
        ];
    }

    public function reorganiser(EtapePlan $etape, int $direction): void
    {
        $etapes = EtapePlan::where('plan_urgence_id', $etape->plan_urgence_id)->orderBy('ordre')->orderBy('id')->get();
        $index = $etapes->search(fn ($item) => $item->id === $etape->id);
        $target = $index + $direction;
        if ($index === false || ! $etapes->has($target)) return;
        $etapes->splice($index, 1, [$etapes[$target]]);
        $etapes->splice($target, 1, [$etape]);
        DB::transaction(fn () => $etapes->values()->each(fn ($item, $i) => $item->update(['ordre' => $i + 1])));
    }
}
