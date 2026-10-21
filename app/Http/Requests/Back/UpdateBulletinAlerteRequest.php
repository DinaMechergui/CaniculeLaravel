<?php

namespace App\Http\Requests\Back;

use App\Models\BulletinAlerte;

class UpdateBulletinAlerteRequest extends StoreBulletinAlerteRequest
{
    /**
     * Pour une mise à jour d'un bulletin existant, ne pas bloquer si l'alerte est terminée.
     */
    protected function checkStatutTerminee(): bool
    {
        return false;
    }

    /**
     * Exclut le bulletin courant lors de la comparaison de rang d'aggravation.
     */
    protected function getExcludedBulletinId(): ?int
    {
        $param = $this->route('bulletin_alerte');

        return $param instanceof BulletinAlerte ? $param->id : (is_numeric($param) ? (int) $param : null);
    }
}
