<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Point de fraîcheur : lieu où les habitants peuvent se rafraîchir.
 */
class PointFraicheur extends Model
{
    /** @use HasFactory<\Database\Factories\PointFraicheurFactory> */
    use HasFactory;

    /**
     * Nom de la table dans la base de données.
     */
    protected $table = 'points_fraicheur';

    protected $fillable = [
        'type_point_id',
        'nom',
        'adresse',
        'latitude',
        'longitude',
        'horaires',
        'accessible',
        'capacite',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'accessible' => 'boolean',
            'capacite' => 'integer',
        ];
    }

    /**
     * Type du point de fraîcheur (parc, salle climatisée, fontaine...).
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(TypePoint::class, 'type_point_id');
    }

    /**
     * Réservations de créneaux liées à ce point (1 point → N réservations).
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'point_fraicheur_id');
    }

    /**
     * Scope : les points les plus proches d'un point géographique (Haversine).
     *
     * Formule de Haversine : distance en vol d'oiseau (km) entre deux points :
     *
     *   d = R * acos( cos(lat1)*cos(lat2)*cos(lng2 - lng1) + sin(lat1)*sin(lat2) )
     *
     * avec R = 6371 km (rayon terrestre) et tous les angles convertis en
     * radians via RADIANS(). LEAST(1, GREATEST(-1, ...)) protège acos() des
     * erreurs d'arrondi flottant (un argument hors de [-1 ; 1] ferait
     * retourner NULL). La distance calculée est exposée sous l'alias
     * « distance », utilisé ensuite par orderBy('distance').
     *
     * Les coordonnées passent exclusivement par des bindings SQL (?),
     * jamais par concaténation de variables dans la requête.
     */
    public function scopeLesPlusProches(Builder $query, float $lat, float $lng): Builder
    {
        return $query
            ->select('points_fraicheur.*')
            ->selectRaw(
                '6371 * acos(LEAST(1, GREATEST(-1, '
                . 'COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?))'
                . ' + SIN(RADIANS(?)) * SIN(RADIANS(latitude))'
                . '))) AS distance',
                [$lat, $lng, $lat]
            )
            ->orderBy('distance');
    }
}
