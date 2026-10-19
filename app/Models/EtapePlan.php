<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EtapePlan extends Model
{
    use HasFactory;
    protected $table = 'etapes_plan';
    protected $fillable = ['plan_urgence_id', 'titre', 'description', 'moment', 'ordre', 'obligatoire'];
    protected $casts = ['obligatoire' => 'boolean'];
    public function planUrgence(): BelongsTo { return $this->belongsTo(PlanUrgence::class, 'plan_urgence_id'); }
    public function utilisateurs(): BelongsToMany { return $this->belongsToMany(User::class, 'etape_plan_user')->withPivot('fait_le'); }
}
