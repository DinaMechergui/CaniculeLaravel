<?php

namespace App\Models;

use Database\Factories\CoupureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupure extends Model
{
    /** @use HasFactory<CoupureFactory> */
    use HasFactory;

    protected $table = 'coupures';

    protected $fillable = [
        'quartier_id',
        'type',
        'statut',
        'date_debut',
        'date_fin',
        'description',
        'latitude',
        'longitude',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'date_debut' => 'datetime',
            'date_fin' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function quartier(): BelongsTo
    {
        return $this->belongsTo(Quartier::class, 'quartier_id');
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class, 'coupure_id');
    }

    public function impacts(): HasMany
    {
        return $this->hasMany(ImpactCoupure::class, 'coupure_id');
    }
}
