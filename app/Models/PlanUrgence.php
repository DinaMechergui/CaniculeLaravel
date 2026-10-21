<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanUrgence extends Model
{
    use HasFactory;

    protected $table = 'plans_urgence';
    protected $fillable = ['titre', 'description', 'type_evenement', 'niveau_risque', 'public_cible', 'actif'];
    protected $casts = ['actif' => 'boolean'];

    public const TYPES = ['canicule' => 'Canicule', 'coupure' => 'Coupure de courant', 'les_deux' => 'Canicule et coupure'];
    public const NIVEAUX = ['faible' => 'Faible', 'modere' => 'Modéré', 'eleve' => 'Élevé'];
    public const PUBLICS = ['tous' => 'Tous', 'personnes_agees' => 'Personnes âgées', 'enfants' => 'Enfants', 'familles' => 'Familles', 'animaux' => 'Animaux'];
    public const MOMENTS = ['avant' => 'Avant', 'pendant' => 'Pendant', 'apres' => 'Après'];

    public function etapes(): HasMany { return $this->hasMany(EtapePlan::class, 'plan_urgence_id')->orderBy('ordre'); }
    public function getNombreEtapesAttribute(): int { return $this->etapes()->count(); }
    public function getNombreEtapesObligatoiresAttribute(): int { return $this->etapes()->where('obligatoire', true)->count(); }
}
