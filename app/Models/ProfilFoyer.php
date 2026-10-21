<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilFoyer extends Model
{
    use HasFactory;
    protected $table = 'profils_foyer';
    protected $fillable = ['user_id', 'nb_adultes', 'nb_enfants', 'nb_personnes_agees', 'a_animaux', 'type_logement'];
    protected $casts = ['a_animaux' => 'boolean'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
