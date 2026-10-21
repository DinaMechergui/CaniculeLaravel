<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BulletinAlerte extends Model
{
    use HasFactory;

    protected $table = 'bulletins_alerte';

    protected $fillable = [
        'alerte_id',
        'niveau',
        'evolution',
        'message',
        'conduite_a_tenir',
        'temperature_max',
        'date_publication',
    ];

    protected $casts = [
        'date_publication' => 'datetime',
        'temperature_max' => 'integer',
    ];

    public const NIVEAUX = [
        'vert' => 'Vigilance verte',
        'jaune' => 'Vigilance jaune',
        'orange' => 'Vigilance orange',
        'rouge' => 'Vigilance rouge (danger maximal)',
    ];

    public const EVOLUTIONS = [
        'aggravation' => 'Aggravation de la situation',
        'stable' => 'Situation stable',
        'amelioration' => 'Amélioration progressive',
        'levee' => "Levée de l'alerte",
    ];

    public const NIVEAU_RANKS = [
        'vert' => 1,
        'jaune' => 2,
        'orange' => 3,
        'rouge' => 4,
    ];

    /**
     * Relation vers l'alerte parente.
     */
    public function alerte(): BelongsTo
    {
        return $this->belongsTo(Alerte::class, 'alerte_id');
    }

    /**
     * Relation vers les diffusions envoyées pour ce bulletin.
     */
    public function diffusions(): HasMany
    {
        return $this->hasMany(Diffusion::class, 'bulletin_alerte_id');
    }

    /**
     * Libellé lisible du niveau.
     */
    public function getNiveauLabelAttribute(): string
    {
        return self::NIVEAUX[$this->niveau] ?? ucfirst((string) $this->niveau);
    }

    /**
     * Libellé lisible de l'évolution.
     */
    public function getEvolutionLabelAttribute(): string
    {
        return self::EVOLUTIONS[$this->evolution] ?? ucfirst((string) $this->evolution);
    }

    /**
     * Icône ou flèche d'évolution.
     */
    public function getEvolutionIconAttribute(): string
    {
        return match ($this->evolution) {
            'aggravation' => 'bx-trending-up text-danger',
            'stable' => 'bx-minus text-info',
            'amelioration' => 'bx-trending-down text-success',
            'levee' => 'bx-check-circle text-secondary',
            default => 'bx-info-circle',
        };
    }

    /**
     * Classe CSS du badge pour le niveau.
     */
    public function getNiveauBadgeClassAttribute(): string
    {
        return match ($this->niveau) {
            'rouge' => 'bg-danger text-white',
            'orange' => 'bg-warning text-dark',
            'jaune' => 'bg-info text-dark',
            'vert' => 'bg-success text-white',
            default => 'bg-secondary text-white',
        };
    }
}
