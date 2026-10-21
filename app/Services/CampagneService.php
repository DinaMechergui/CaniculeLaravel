<?php

namespace App\Services;

use App\Models\CampagneConseil;
use App\Models\Conseil;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service métier des campagnes de sensibilisation.
 *
 * Responsabilités :
 *  - Transitions de statut contrôlées
 *  - Actualisation automatique des statuts (commande planifiée)
 *  - Duplication d'une campagne
 *  - Statistiques du portefeuille
 *  - Export CSV
 */
class CampagneService
{
    // ─── Transitions de statut ────────────────────────────────────────────────

    /**
     * Tente d'activer une campagne (brouillon -> active).
     * Exige que la date_fin ne soit pas déjà passée.
     *
     * @return string|null Message d'erreur, null si succès.
     */
    public function activer(CampagneConseil $campagne): ?string
    {
        if (! $campagne->peutTransitionnerVers('active')) {
            return 'Cette campagne ne peut pas être activée depuis son statut actuel.';
        }

        if ($campagne->date_fin && $campagne->date_fin->lt(now()->startOfDay())) {
            return 'Impossible d\'activer une campagne dont la date de fin est déjà passée.';
        }

        $campagne->update(['statut' => 'active']);

        return null;
    }

    /**
     * Tente de terminer une campagne (active -> terminee).
     *
     * @return string|null Message d'erreur, null si succès.
     */
    public function terminer(CampagneConseil $campagne): ?string
    {
        if (! $campagne->peutTransitionnerVers('terminee')) {
            return 'Cette campagne ne peut pas être terminée depuis son statut actuel.';
        }

        $campagne->update(['statut' => 'terminee']);

        return null;
    }

    // ─── Actualisation automatique (commande planifiée) ──────────────────────

    /**
     * Termine les campagnes actives dont la date_fin est passée.
     * Active les brouillons à activation_auto dont date_debut est atteinte.
     *
     * @param  bool  $dryRun  Si true, ne modifie rien mais retourne les comptes.
     * @return array{terminees: int, activees: int}
     */
    public function actualiserStatuts(bool $dryRun = false): array
    {
        $today = Carbon::today();

        // Campagnes actives expirées
        $aTerminer = CampagneConseil::where('statut', 'active')
            ->whereNotNull('date_fin')
            ->where('date_fin', '<', $today)
            ->get();

        // Brouillons à activation automatique dont la date de début est atteinte
        $aActiver = CampagneConseil::where('statut', 'brouillon')
            ->where('activation_auto', true)
            ->where('date_debut', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('date_fin')->orWhere('date_fin', '>=', $today);
            })
            ->get();

        if (! $dryRun) {
            DB::transaction(function () use ($aTerminer, $aActiver) {
                foreach ($aTerminer as $campagne) {
                    $campagne->update(['statut' => 'terminee']);
                }
                foreach ($aActiver as $campagne) {
                    $campagne->update(['statut' => 'active']);
                }
            });
        }

        return [
            'terminees' => $aTerminer->count(),
            'activees'  => $aActiver->count(),
        ];
    }

    // ─── Duplication ──────────────────────────────────────────────────────────

    /**
     * Crée un brouillon "Copie de …" avec les dates décalées du nombre de jours donné.
     */
    public function dupliquer(CampagneConseil $campagne, int $decalageJours = 30): CampagneConseil
    {
        return DB::transaction(function () use ($campagne, $decalageJours) {
            return CampagneConseil::create([
                'conseil_id'      => $campagne->conseil_id,
                'titre'           => 'Copie de ' . $campagne->titre,
                'public_cible'    => $campagne->public_cible,
                'canal'           => $campagne->canal,
                'date_debut'      => $campagne->date_debut->copy()->addDays($decalageJours),
                'date_fin'        => $campagne->date_fin
                    ? $campagne->date_fin->copy()->addDays($decalageJours)
                    : null,
                'statut'          => 'brouillon',
                'activation_auto' => false,
                'message'         => null,
            ]);
        });
    }

    // ─── Statistiques ─────────────────────────────────────────────────────────

    /**
     * Retourne toutes les statistiques du portefeuille des campagnes.
     *
     * @return array<string, mixed>
     */
    public function statistiques(): array
    {
        // Total par statut
        $parStatut = CampagneConseil::selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->toArray();

        // Total par canal
        $parCanal = CampagneConseil::selectRaw('canal, COUNT(*) as total')
            ->groupBy('canal')
            ->pluck('total', 'canal')
            ->toArray();

        // Total par public
        $parPublic = CampagneConseil::selectRaw('public_cible, COUNT(*) as total')
            ->groupBy('public_cible')
            ->pluck('total', 'public_cible')
            ->toArray();

        // Top 5 des conseils les plus diffusés
        $top5Conseils = CampagneConseil::selectRaw('conseil_id, COUNT(*) as nb_campagnes')
            ->groupBy('conseil_id')
            ->orderByDesc('nb_campagnes')
            ->limit(5)
            ->with('conseil:id,titre')
            ->get();

        // Conseils sans campagne
        $conseilsSansCampagne = Conseil::whereDoesntHave('campagnes')->with('categorie:id,nom')->get();

        // Campagnes qui finissent dans 7 jours
        $finDansSetJours = CampagneConseil::where('statut', 'active')
            ->whereNotNull('date_fin')
            ->whereBetween('date_fin', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
            ->with('conseil:id,titre')
            ->get();

        return compact(
            'parStatut',
            'parCanal',
            'parPublic',
            'top5Conseils',
            'conseilsSansCampagne',
            'finDansSetJours'
        );
    }

    // ─── Export CSV ──────────────────────────────────────────────────────────

    /**
     * Génère un CSV UTF-8 avec BOM (séparateur point-virgule) de la collection.
     *
     * @param  \Illuminate\Support\Collection<int, CampagneConseil>  $campagnes
     */
    public function exportCsv(Collection $campagnes): string
    {
        $bom = "\xEF\xBB\xBF"; // UTF-8 BOM pour Excel
        $lignes = [];

        $lignes[] = implode(';', [
            'ID', 'Titre', 'Conseil', 'Public cible', 'Canal',
            'Date début', 'Date fin', 'Statut', 'Activation auto', 'Message',
        ]);

        foreach ($campagnes as $c) {
            $lignes[] = implode(';', [
                $c->id,
                '"' . str_replace('"', '""', $c->titre) . '"',
                '"' . str_replace('"', '""', $c->conseil->titre ?? '') . '"',
                $c->public_label,
                $c->canal_label,
                $c->date_debut->format('d/m/Y'),
                $c->date_fin ? $c->date_fin->format('d/m/Y') : '',
                $c->statut_label,
                $c->activation_auto ? 'Oui' : 'Non',
                '"' . str_replace('"', '""', $c->message ?? '') . '"',
            ]);
        }

        return $bom . implode("\n", $lignes);
    }

    // ─── Calendrier mensuel ───────────────────────────────────────────────────

    /**
     * Retourne les campagnes groupées par mois (format "Y-m").
     *
     * @return Collection<string, Collection<int, CampagneConseil>>
     */
    public function calendrierMensuel(): Collection
    {
        return CampagneConseil::with('conseil:id,titre')
            ->orderBy('date_debut')
            ->get()
            ->groupBy(fn (CampagneConseil $c) => $c->date_debut->format('Y-m'));
    }
}
