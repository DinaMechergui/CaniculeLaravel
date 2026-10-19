<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MesurePrevention extends Model
{
    use HasFactory;

    /**
     * Nom de la table associée au modèle.
     */
    protected $table = 'mesures_prevention';

    /**
     * Les attributs assignables en masse.
     */
    protected $fillable = [
        'alerte_meteo_id',
        'titre',
        'type',
        'description',
        'responsable',
        'date_debut',
        'statut',
    ];

    /**
     * Les attributs à caster.
     */
    protected $casts = [
        'date_debut' => 'datetime',
    ];

    public const TYPE_EVACUATION = 'evacuation';
    public const TYPE_FERMETURE_SITE = 'fermeture_site';
    public const TYPE_DISTRIBUTION_EAU = 'distribution_eau';
    public const TYPE_RENFORT_SECOURS = 'renfort_secours';

    public const TYPES = [
        self::TYPE_EVACUATION => 'Évacuation',
        self::TYPE_FERMETURE_SITE => 'Fermeture de site',
        self::TYPE_DISTRIBUTION_EAU => 'Distribution d\'eau',
        self::TYPE_RENFORT_SECOURS => 'Renfort de secours',
    ];

    public const STATUT_PREVUE = 'prevue';
    public const STATUT_EN_COURS = 'en_cours';
    public const STATUT_TERMINEE = 'terminee';

    public const STATUTS = [
        self::STATUT_PREVUE => 'Prévue',
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_TERMINEE => 'Terminée',
    ];

    /**
     * Alerte météo liée à cette mesure.
     */
    public function alerteMeteo(): BelongsTo
    {
        return $this->belongsTo(AlerteMeteo::class, 'alerte_meteo_id');
    }

    /**
     * Scope pour filtrer les mesures prévues ou en cours (utilisé sur le front).
     */
    public function scopeActives(Builder $query): Builder
    {
        return $query->whereIn('statut', [self::STATUT_PREVUE, self::STATUT_EN_COURS]);
    }

    /**
     * Libellé lisible du type de mesure.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    /**
     * Libellé lisible du statut.
     */
    public function getStatutLabelAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? ucfirst(str_replace('_', ' ', $this->statut));
    }

    /**
     * Classe CSS badge pour le statut.
     */
    public function getStatutBadgeClassAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_PREVUE => 'bg-warning text-dark',
            self::STATUT_EN_COURS => 'bg-primary text-white',
            self::STATUT_TERMINEE => 'bg-success text-white',
            default => 'bg-secondary text-white',
        };
    }

    /**
     * Classe CSS badge pour le type.
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_EVACUATION => 'bg-danger text-white',
            self::TYPE_FERMETURE_SITE => 'bg-warning text-dark',
            self::TYPE_DISTRIBUTION_EAU => 'bg-info text-white',
            self::TYPE_RENFORT_SECOURS => 'bg-danger text-white',
            default => 'bg-secondary text-white',
        };
    }
}
