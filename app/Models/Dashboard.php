<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Instantané d'indicateurs du module « Réservations » pour une date.
 *
 * `point_fraicheur_id` nul signifie des indicateurs globaux (tous les points).
 */
class Dashboard extends Model
{
    /** @use HasFactory<\Database\Factories\DashboardFactory> */
    use HasFactory;

    /**
     * Nom de la table dans la base de données.
     */
    protected $table = 'dashboards';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'date',
        'point_fraicheur_id',
        'total_reservations',
        'total_personnes',
        'en_liste_attente',
        'taux_presence',
        'taux_no_show',
        'taux_annulation',
        'score_risque_moyen',
        'source',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_reservations' => 'integer',
            'total_personnes' => 'integer',
            'en_liste_attente' => 'integer',
            'taux_presence' => 'decimal:2',
            'taux_no_show' => 'decimal:2',
            'taux_annulation' => 'decimal:2',
            'score_risque_moyen' => 'decimal:2',
        ];
    }

    /**
     * Point de fraîcheur concerné (N:1), nul pour les indicateurs globaux.
     */
    public function pointFraicheur(): BelongsTo
    {
        return $this->belongsTo(PointFraicheur::class, 'point_fraicheur_id');
    }
}
