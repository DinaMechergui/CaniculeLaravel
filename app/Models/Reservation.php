<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Réservation de créneau sur un point de fraîcheur.
 *
 * Le client n'a pas de compte : il est identifié par son nom, son e-mail et
 * un code de réservation unique généré automatiquement.
 */
class Reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Nom de la table dans la base de données.
     */
    protected $table = 'reservations';

    /**
     * Statuts possibles d'une réservation.
     */
    public const STATUT_EN_ATTENTE   = 'en_attente';
    public const STATUT_CONFIRMEE    = 'confirmee';
    public const STATUT_LISTE_ATTENTE = 'liste_attente';
    public const STATUT_ANNULEE      = 'annulee';
    public const STATUT_EXPIREE      = 'expiree';
    public const STATUT_TERMINEE     = 'terminee';
    public const STATUT_NO_SHOW      = 'no_show';

    public const STATUTS = [
        self::STATUT_EN_ATTENTE    => 'En attente',
        self::STATUT_CONFIRMEE     => 'Confirmée',
        self::STATUT_LISTE_ATTENTE => 'Liste d\'attente',
        self::STATUT_ANNULEE       => 'Annulée',
        self::STATUT_EXPIREE       => 'Expirée',
        self::STATUT_TERMINEE      => 'Terminée',
        self::STATUT_NO_SHOW       => 'Non présenté',
    ];

    /**
     * Priorités possibles d'une réservation (calculées par l'IA).
     */
    public const PRIORITE_NORMALE = 'normale';
    public const PRIORITE_HAUTE   = 'haute';
    public const PRIORITE_URGENTE = 'urgente';

    public const PRIORITES = [
        self::PRIORITE_NORMALE => 'Normale',
        self::PRIORITE_HAUTE   => 'Haute',
        self::PRIORITE_URGENTE => 'Urgente',
    ];

    /**
     * Les attributs assignables en masse.
     *
     * Le champ « code » n'y figure pas : il est toujours généré par le modèle.
     *
     * @var list<string>
     */
    protected $fillable = [
        'point_fraicheur_id',
        'nom',
        'email',
        'telephone',
        'nombre_personnes',
        'date_reservation',
        'heure_debut',
        'heure_fin',
        'statut',
        'besoins_specifiques',
        'tags_ia',
        'priorite',
        'score_risque_no_show',
        'explication_ia',
        'notes_admin',
        'confirmee_le',
        'annulee_le',
        'check_in_le',
        'rappel_envoye_le',
    ];

    /**
     * Valeurs par défaut des attributs.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'statut'   => self::STATUT_EN_ATTENTE,
        'priorite' => self::PRIORITE_NORMALE,
    ];

    /**
     * Les attributs à caster.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nombre_personnes'     => 'integer',
            'date_reservation'     => 'date',
            'tags_ia'              => 'array',
            'score_risque_no_show' => 'decimal:2',
            'confirmee_le'         => 'datetime',
            'annulee_le'           => 'datetime',
            'check_in_le'          => 'datetime',
            'rappel_envoye_le'     => 'datetime',
        ];
    }

    /**
     * Génère un code de réservation unique de 8 caractères alphanumériques
     * majuscules. Réessaie en cas de collision (très rare).
     */
    public static function genererCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (static::withTrashed()->where('code', $code)->exists());

        return $code;
    }

    /**
     * Attribue automatiquement un code lors de la création.
     */
    protected static function booted(): void
    {
        static::creating(function (self $reservation) {
            if (empty($reservation->code)) {
                $reservation->code = static::genererCode();
            }
        });
    }

    /**
     * Point de fraîcheur concerné (N:1).
     */
    public function pointFraicheur(): BelongsTo
    {
        return $this->belongsTo(PointFraicheur::class, 'point_fraicheur_id');
    }

    /**
     * Scope : réservations actives (ni annulées, ni terminées, ni expirées).
     */
    public function scopeActives(Builder $query): Builder
    {
        return $query->whereIn('statut', [
            self::STATUT_EN_ATTENTE,
            self::STATUT_CONFIRMEE,
            self::STATUT_LISTE_ATTENTE,
        ]);
    }

    /**
     * Scope : réservations à venir (à partir d'aujourd'hui).
     */
    public function scopeAVenir(Builder $query): Builder
    {
        return $query->whereDate('date_reservation', '>=', now()->toDateString());
    }

    /**
     * Scope : réservations du jour.
     */
    public function scopeDuJour(Builder $query): Builder
    {
        return $query->whereDate('date_reservation', now()->toDateString());
    }

    /**
     * Scope : filtre facultatif par statut.
     */
    public function scopeParStatut(Builder $query, ?string $statut): Builder
    {
        return $query->when($statut, fn (Builder $q) => $q->where('statut', $statut));
    }

    /**
     * Libellé lisible du statut.
     */
    public function getStatutLabelAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? ucfirst((string) $this->statut);
    }

    /**
     * Classe de badge Bootstrap selon le statut.
     */
    public function getStatutBadgeAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_CONFIRMEE     => 'success',
            self::STATUT_EN_ATTENTE    => 'warning',
            self::STATUT_LISTE_ATTENTE => 'info',
            self::STATUT_TERMINEE      => 'primary',
            self::STATUT_ANNULEE       => 'secondary',
            self::STATUT_EXPIREE       => 'dark',
            self::STATUT_NO_SHOW       => 'danger',
            default                    => 'secondary',
        };
    }

    /**
     * Classe de badge Bootstrap selon la priorité.
     */
    public function getPrioriteBadgeAttribute(): string
    {
        return match ($this->priorite) {
            self::PRIORITE_URGENTE => 'danger',
            self::PRIORITE_HAUTE   => 'warning',
            default                => 'secondary',
        };
    }

    /**
     * Libellé lisible de la priorité.
     */
    public function getPrioriteLabelAttribute(): string
    {
        return self::PRIORITES[$this->priorite] ?? ucfirst((string) $this->priorite);
    }

    /**
     * Heure de début au format « H:i » (pour les champs de formulaire).
     */
    public function getHeureDebutCourteAttribute(): string
    {
        return substr((string) $this->heure_debut, 0, 5);
    }

    /**
     * Heure de fin au format « H:i ».
     */
    public function getHeureFinCourteAttribute(): string
    {
        return substr((string) $this->heure_fin, 0, 5);
    }
}
