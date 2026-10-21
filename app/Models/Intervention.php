<?php

namespace App\Models;

use Database\Factories\InterventionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Intervention extends Model
{
    /** @use HasFactory<InterventionFactory> */
    use HasFactory;

    protected $table = 'interventions';

    protected $fillable = [
        'coupure_id',
        'equipe',
        'technicien',
        'date_intervention',
        'duree_estimee',
        'statut',
        'rapport',
    ];

    protected function casts(): array
    {
        return [
            'date_intervention' => 'datetime',
            'duree_estimee' => 'integer',
        ];
    }

    public function coupure(): BelongsTo
    {
        return $this->belongsTo(Coupure::class, 'coupure_id');
    }
}
