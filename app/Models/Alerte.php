<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alerte extends Model
{
    use HasFactory;

    protected $table = 'alertes';

    protected $fillable = [
        'titre',
        'type',
        'zone_concernee',
        'description',
        'source',
        'date_debut',
        'date_fin',
        'statut',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    public const TYPES = [
        'canicule' => 'Canicule',
        'orage' => 'Orage violent',
        'inondation' => 'Inondation',
        'vent_fort' => 'Vents violents',
        'incendie_foret' => 'Incendie de forêt',
        'pollution_air' => "Pic de pollution de l'air",
    ];

    /**
     * Relation vers les bulletins d'évolution (triés par date de publication).
     */
    public function bulletins(): HasMany
    {
        return $this->hasMany(BulletinAlerte::class, 'alerte_id')->orderBy('date_publication', 'asc');
    }

    /**
     * Accessor pour le bulletin le plus récent.
     */
    public function getDernierBulletinAttribute(): ?BulletinAlerte
    {
        if ($this->relationLoaded('bulletins')) {
            return $this->bulletins->sortByDesc('date_publication')->first();
        }

        return $this->hasOne(BulletinAlerte::class, 'alerte_id')->latestOfMany('date_publication')->first();
    }

    /**
     * Accessor pour le niveau de vigilance actuel (celui du dernier bulletin ou "gris").
     */
    public function getNiveauActuelAttribute(): string
    {
        return $this->dernierBulletin?->niveau ?? 'gris';
    }

    /**
     * Score de gravité numérique pour le tri front (rouge > orange > jaune > vert > gris).
     */
    public function getGraviteScoreAttribute(): int
    {
        return match ($this->niveau_actuel) {
            'rouge' => 4,
            'orange' => 3,
            'jaune' => 2,
            'vert' => 1,
            default => 0,
        };
    }

    /**
     * Scope pour filtrer les alertes en cours (utilisé par le front office).
     */
    public function scopeEnCours(Builder $query): Builder
    {
        return $query->where('statut', 'en_cours');
    }

    /**
     * Scope pour les alertes terminées.
     */
    public function scopeTerminees(Builder $query): Builder
    {
        return $query->where('statut', 'terminee');
    }

    /**
     * Libellé lisible du type d'événement.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', (string) $this->type));
    }
}
