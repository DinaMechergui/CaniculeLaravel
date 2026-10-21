<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catégorie (thème) de conseils : énergie, hydratation, équipements...
 */
class CategorieConseil extends Model
{
    use HasFactory;

    // Nom explicite de la table (convention du projet).
    protected $table = 'categorie_conseils';

    // Champs autorisés pour create() et update().
    protected $fillable = ['nom', 'description'];

    // Relation 1-N : une catégorie regroupe plusieurs conseils.
    public function conseils(): HasMany
    {
        return $this->hasMany(Conseil::class, 'categorie_conseil_id');
    }
}
