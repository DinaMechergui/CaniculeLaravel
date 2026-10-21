<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diffusion extends Model
{
    use HasFactory;

    protected $table = 'diffusions';

    protected $fillable = [
        'bulletin_alerte_id',
        'canal',
        'contenu',
        'destinataires_estimes',
        'date_envoi',
        'statut',
    ];

    protected $casts = [
        'date_envoi' => 'datetime',
        'destinataires_estimes' => 'integer',
    ];

    public const CANAUX = [
        'sms' => 'SMS d\'alerte',
        'email' => 'Courrier électronique',
        'notification_push' => 'Notification Push mobile',
        'sirene' => 'Sirène municipale d\'urgence',
        'affichage' => 'Panneaux d\'affichage lumineux',
    ];

    public const STATUTS = [
        'planifiee' => 'Planifiée',
        'envoyee' => 'Envoyée',
        'echec' => 'Échec d\'envoi',
    ];

    /**
     * Relation vers le bulletin lié.
     */
    public function bulletinAlerte(): BelongsTo
    {
        return $this->belongsTo(BulletinAlerte::class, 'bulletin_alerte_id');
    }

    /**
     * Libellé lisible du canal.
     */
    public function getCanalLabelAttribute(): string
    {
        return self::CANAUX[$this->canal] ?? ucfirst((string) $this->canal);
    }

    /**
     * Icône du canal.
     */
    public function getCanalIconAttribute(): string
    {
        return match ($this->canal) {
            'sms' => 'bx-message-rounded-dots',
            'email' => 'bx-envelope',
            'notification_push' => 'bx-bell',
            'sirene' => 'bx-alarm',
            'affichage' => 'bx-tv',
            default => 'bx-broadcast',
        };
    }

    /**
     * Classe CSS du statut.
     */
    public function getStatutBadgeClassAttribute(): string
    {
        return match ($this->statut) {
            'envoyee' => 'bg-success text-white',
            'planifiee' => 'bg-info text-dark',
            'echec' => 'bg-danger text-white',
            default => 'bg-secondary text-white',
        };
    }
}
