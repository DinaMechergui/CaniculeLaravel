<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Signalement émis par un utilisateur.
 */
class Signalement extends Model
{
    /** @use HasFactory<\Database\Factories\SignalementFactory> */
    use HasFactory;

    /**
     * Nom de la table dans la base de données.
     */
    protected $table = 'signalements';

    /**
     * Statuts possibles d'un signalement.
     */
    public const STATUT_NOUVEAU    = 'nouveau';
    public const STATUT_EN_COURS   = 'en_cours';
    public const STATUT_TRAITE     = 'traite';

    public const STATUTS = [
        self::STATUT_NOUVEAU  => 'Nouveau',
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_TRAITE   => 'Traité',
    ];

    /**
     * Priorités possibles d'un signalement (gérées par l'IA).
     */
    public const PRIORITE_FAIBLE  = 'faible';
    public const PRIORITE_MOYENNE = 'moyenne';
    public const PRIORITE_URGENTE = 'urgente';

    public const PRIORITES = [
        self::PRIORITE_FAIBLE  => 'Faible',
        self::PRIORITE_MOYENNE => 'Moyenne',
        self::PRIORITE_URGENTE => 'Urgente',
    ];

    protected $fillable = [
        'type_signalement_id',
        'user_id',
        'description',
        'statut',
        'priorite',
    ];

    protected $attributes = [
        'statut'   => self::STATUT_NOUVEAU,
        'priorite' => self::PRIORITE_MOYENNE,
    ];

    /**
     * Type de signalement (N:1).
     */
    public function typeSignalement(): BelongsTo
    {
        return $this->belongsTo(TypeSignalement::class, 'type_signalement_id');
    }

    /**
     * Utilisateur auteur du signalement (N:1).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Diagnostic de la panne (1:1).
     * La suppression en cascade est gérée au niveau de la migration (cascadeOnDelete).
     */
    public function diagnostic(): HasOne
    {
        return $this->hasOne(Diagnostic::class);
    }
}
