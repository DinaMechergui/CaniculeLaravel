<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Campagne de sensibilisation diffusant un conseil sur un canal donné.
 *
 * @property int         $id
 * @property int         $conseil_id
 * @property string      $titre
 * @property string      $public_cible
 * @property string      $canal
 * @property Carbon      $date_debut
 * @property Carbon|null $date_fin
 * @property string      $statut
 * @property bool        $activation_auto
 * @property string|null $message
 */
class CampagneConseil extends Model
{
    use HasFactory;

    protected $table = 'campagnes_conseil';

    public const PUBLICS = [
        'tous'           => 'Tous publics',
        'personnes_agees' => 'Personnes âgées',
        'enfants'        => 'Enfants',
        'familles'       => 'Familles',
    ];

    public const CANAUX = [
        'affichage'      => 'Affichage',
        'reseaux_sociaux' => 'Réseaux sociaux',
        'sms'            => 'SMS',
    ];

    public const STATUTS = [
        'brouillon' => 'Brouillon',
        'active'    => 'Active',
        'terminee'  => 'Terminée',
    ];

    /**
     * Transitions autorisées : brouillon -> active -> terminee (jamais en arrière).
     */
    public const TRANSITIONS = [
        'brouillon' => ['active'],
        'active'    => ['terminee'],
        'terminee'  => [],
    ];

    protected $fillable = [
        'conseil_id',
        'titre',
        'public_cible',
        'canal',
        'date_debut',
        'date_fin',
        'statut',
        'activation_auto',
        'message',
    ];

    protected $casts = [
        'date_debut'      => 'date',
        'date_fin'        => 'date',
        'activation_auto' => 'boolean',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function conseil()
    {
        return $this->belongsTo(Conseil::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /** Campagnes dont la date de début est dans le futur. */
    public function scopeAVenir(Builder $query): Builder
    {
        return $query->where('date_debut', '>', now());
    }

    /** Campagnes actuellement en cours (date_debut passée, date_fin future ou nulle). */
    public function scopeEnCours(Builder $query): Builder
    {
        return $query->where('date_debut', '<=', now())
            ->where(function (Builder $q) {
                $q->whereNull('date_fin')->orWhere('date_fin', '>=', now());
            });
    }

    /** Campagnes dont la date de fin est passée. */
    public function scopeExpirees(Builder $query): Builder
    {
        return $query->whereNotNull('date_fin')->where('date_fin', '<', now());
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getPublicLabelAttribute(): string
    {
        return self::PUBLICS[$this->public_cible] ?? $this->public_cible;
    }

    public function getCanalLabelAttribute(): string
    {
        return self::CANAUX[$this->canal] ?? $this->canal;
    }

    public function getStatutLabelAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function getStatutBadgeAttribute(): string
    {
        return match ($this->statut) {
            'active'   => 'success',
            'terminee' => 'secondary',
            default    => 'warning',
        };
    }

    /**
     * État calendaire : affichage humain ("Active", "Commence dans 3 jours", etc.)
     */
    public function getEtatCalendrierAttribute(): string
    {
        $now = now()->startOfDay();

        if ($this->date_debut->gt($now)) {
            $jours = (int) $now->diffInDays($this->date_debut);
            return "Commence dans {$jours} jour" . ($jours > 1 ? 's' : '');
        }

        if ($this->date_fin && $this->date_fin->lt($now)) {
            return 'Terminée';
        }

        if ($this->date_fin) {
            $jours = (int) $now->diffInDays($this->date_fin);
            return "Se termine dans {$jours} jour" . ($jours > 1 ? 's' : '');
        }

        return 'En cours (sans date de fin)';
    }

    /** Durée totale en jours (null si pas de date de fin). */
    public function getDureeEnJoursAttribute(): ?int
    {
        if (! $this->date_fin) {
            return null;
        }

        return (int) $this->date_debut->diffInDays($this->date_fin);
    }

    /** Jours restants avant la fin (null si pas de date de fin ou déjà terminée). */
    public function getJoursRestantsAttribute(): ?int
    {
        if (! $this->date_fin) {
            return null;
        }

        $diff = (int) now()->startOfDay()->diffInDays($this->date_fin, false);

        return $diff >= 0 ? $diff : null;
    }

    /** Indique si la transition vers le nouveau statut est autorisée. */
    public function peutTransitionnerVers(string $nouveauStatut): bool
    {
        return in_array($nouveauStatut, self::TRANSITIONS[$this->statut] ?? [], true);
    }

    /** Couleur Bootstrap selon le statut (pour le calendrier). */
    public function getCouleurCalendrierAttribute(): string
    {
        return match ($this->statut) {
            'active'   => 'success',
            'terminee' => 'secondary',
            default    => 'warning',
        };
    }
}