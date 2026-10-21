<?php

namespace App\Rules;

use App\Models\CampagneConseil;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Refuse qu'une campagne active ait un chevauchement de période avec une autre
 * campagne active portant le même conseil, le même canal et un public identique
 * ou "tous".
 *
 * Usage : lors de la validation Store/Update d'une campagne dont le statut est
 * "active" ou qui va passer à "active".
 */
class SansChevauchementCampagne implements ValidationRule
{
    public function __construct(
        private readonly int     $conseilId,
        private readonly string  $canal,
        private readonly string  $publicCible,
        private readonly string  $dateDebut,
        private readonly ?string $dateFin,
        private readonly ?int    $ignorerId = null  // ID de la campagne en cours d'édition
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = CampagneConseil::where('statut', 'active')
            ->where('conseil_id', $this->conseilId)
            ->where('canal', $this->canal)
            ->where(function ($q) {
                // Public identique OU l'une des deux est "tous"
                $q->where('public_cible', $this->publicCible)
                    ->orWhere('public_cible', 'tous')
                    ->orWhere(function ($qq) {
                        if ($this->publicCible === 'tous') {
                            $qq->whereIn('public_cible', array_keys(CampagneConseil::PUBLICS));
                        }
                    });
            })
            ->where(function ($q) {
                // Chevauchement de période
                // [debut1, fin1] chevauche [debut2, fin2] si debut1 <= fin2 ET fin1 >= debut2
                $debut = $this->dateDebut;
                $fin   = $this->dateFin;

                if ($fin) {
                    $q->where('date_debut', '<=', $fin)
                        ->where(function ($qq) use ($debut) {
                            $qq->whereNull('date_fin')->orWhere('date_fin', '>=', $debut);
                        });
                } else {
                    // Campagne sans fin : chevauche toute campagne commençant avant maintenant
                    $q->where(function ($qq) use ($debut) {
                        $qq->whereNull('date_fin')->orWhere('date_fin', '>=', $debut);
                    });
                }
            });

        if ($this->ignorerId !== null) {
            $query->where('id', '!=', $this->ignorerId);
        }

        if ($query->exists()) {
            $fail('Une campagne active avec ce conseil, ce canal et ce public se déroule déjà sur cette période.');
        }
    }
}
