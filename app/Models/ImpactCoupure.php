<?php

namespace App\Models;

use Database\Factories\ImpactCoupureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImpactCoupure extends Model
{
    /** @use HasFactory<ImpactCoupureFactory> */
    use HasFactory;

    protected $table = 'impacts_coupures';

    protected $fillable = [
        'coupure_id',
        'nom_structure',
        'categorie',
        'adresse',
        'personnes_affectees',
        'niveau_gravite',
        'date_signalement',
        'mesures_urgence',
        'statut',
        'description',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'date_signalement' => 'datetime',
            'personnes_affectees' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function coupure(): BelongsTo
    {
        return $this->belongsTo(Coupure::class, 'coupure_id');
    }
}
