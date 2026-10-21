<?php

namespace App\Services;

use App\Models\Coupure;
use App\Models\Quartier;
use App\Models\User;
use App\Notifications\AlerteFrequenceCoupuresNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CoupureAlertService
{
    /**
     * Seuil à partir duquel un quartier est considéré comme critique (haute fréquence).
     */
    public const SEUIL_CRITIQUE = 3;

    /**
     * Vérifie si un quartier a dépassé le seuil de coupures et envoie une alerte aux administrateurs.
     *
     * @param int|Quartier $quartier
     * @return array|null Informations sur l'alerte si déclenchée, null sinon.
     */
    public function verifierEtAlerter(int|Quartier $quartier): ?array
    {
        $quartierModel = $quartier instanceof Quartier ? $quartier : Quartier::find($quartier);

        if (!$quartierModel) {
            return null;
        }

        $nbCoupures = Coupure::where('quartier_id', $quartierModel->id)->count();

        if ($nbCoupures >= self::SEUIL_CRITIQUE) {
            $admins = User::where('role', 'admin')->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new AlerteFrequenceCoupuresNotification($quartierModel, $nbCoupures, self::SEUIL_CRITIQUE));
            }

            Log::warning("Alerte fréquence coupures émise : Le quartier {$quartierModel->nom} cumule {$nbCoupures} coupures.");

            return [
                'alerte'       => true,
                'quartier_id'  => $quartierModel->id,
                'quartier_nom' => $quartierModel->nom,
                'ville'        => $quartierModel->ville,
                'count'        => $nbCoupures,
                'seuil'        => self::SEUIL_CRITIQUE,
                'message'      => "Attention : Le quartier {$quartierModel->nom} ({$quartierModel->ville}) cumule {$nbCoupures} coupures d'électricité ! Une alerte a été envoyée aux administrateurs.",
            ];
        }

        return null;
    }

    /**
     * Retourne tous les quartiers ayant atteint ou dépassé le seuil de coupures.
     *
     * @param int $seuil
     * @return Collection
     */
    public function getQuartiersCritiques(int $seuil = self::SEUIL_CRITIQUE): Collection
    {
        return Quartier::has('coupures', '>=', $seuil)
            ->withCount('coupures')
            ->orderByDesc('coupures_count')
            ->get();
    }
}
