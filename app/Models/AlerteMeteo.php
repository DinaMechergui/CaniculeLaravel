<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlerteMeteo extends Model
{
    use HasFactory;

    /**
     * Nom de la table associée au modèle.
     */
    protected $table = 'alertes_meteo';

    /**
     * Les attributs assignables en masse.
     */
    protected $fillable = [
        'niveau_alerte_id',
        'titre',
        'message',
        'date_debut',
        'date_fin',
        'source',
    ];

    /**
     * Les attributs à caster.
     */
    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    /**
     * Niveau d'alerte rattaché.
     */
    public function niveauAlerte(): BelongsTo
    {
        return $this->belongsTo(NiveauAlerte::class, 'niveau_alerte_id');
    }

    /**
     * Mesures de prévention déclenchées par cette alerte.
     */
    public function mesures(): HasMany
    {
        return $this->hasMany(MesurePrevention::class, 'alerte_meteo_id');
    }

    /**
     * Scope pour les alertes météo actives / en cours.
     * Une alerte est active si elle a débuté (date_debut <= now)
     * et n'est pas encore expirée (date_fin null ou date_fin >= now).
     */
    public function scopeActive(Builder $query): Builder
    {
        $now = now();

        return $query->where('date_debut', '<=', $now)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('date_fin')
                    ->orWhere('date_fin', '>=', $now);
            });
    }

    /**
     * Vérifie si l'alerte est actuellement active.
     */
    public function isActif(): bool
    {
        $now = now();

        return $this->date_debut <= $now && ($this->date_fin === null || $this->date_fin >= $now);
    }
}
