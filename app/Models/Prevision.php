<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Prévision de fréquentation d'un point de fraîcheur pour une date donnée.
 */
class Prevision extends Model
{
    /** @use HasFactory<\Database\Factories\PrevisionFactory> */
    use HasFactory;

    /**
     * Nom de la table dans la base de données.
     */
    protected $table = 'previsions';

    /**
     * Origine de la prévision.
     */
    public const SOURCE_IA = 'ia';
    public const SOURCE_LOCALE = 'local';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'point_fraicheur_id',
        'date',
        'capacite',
        'reservations_prevues',
        'taux_remplissage',
        'source',
        'explication',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'capacite' => 'integer',
            'reservations_prevues' => 'integer',
            'taux_remplissage' => 'decimal:2',
        ];
    }

    /**
     * Point de fraîcheur concerné (N:1).
     */
    public function pointFraicheur(): BelongsTo
    {
        return $this->belongsTo(PointFraicheur::class, 'point_fraicheur_id');
    }

    /**
     * Scope : prévisions à venir (à partir d'aujourd'hui).
     */
    public function scopeAVenir(Builder $query): Builder
    {
        return $query->whereDate('date', '>=', now()->toDateString());
    }
}
