<?php

namespace Database\Seeders;

use App\Models\Diagnostic;
use App\Models\Signalement;
use Illuminate\Database\Seeder;

/**
 * Crée un diagnostic pour chaque signalement de type « Coupure observée » ou « Panne ».
 *
 * La cause réelle n'est renseignée que pour les signalements traités
 * (l'administrateur la confirme après intervention).
 *
 * Pré-requis d'exécution (ordre obligatoire) :
 *   TypeSignalementSeeder → SignalementSeeder → DiagnosticSeeder
 */
class DiagnosticSeeder extends Seeder
{
    public function run(): void
    {
        $signalements = Signalement::query()
            ->whereHas('typeSignalement', fn ($q) => $q->whereIn('libelle', ['Coupure observée', 'Panne']))
            ->doesntHave('diagnostic')
            ->get();

        if ($signalements->isEmpty()) {
            $this->command->warn('DiagnosticSeeder : aucun signalement à diagnostiquer.');
            return;
        }

        foreach ($signalements as $signalement) {
            $factory = Diagnostic::factory();

            if ($signalement->statut !== Signalement::STATUT_TRAITE) {
                $factory = $factory->nonConfirme();
            }

            $factory->create(['signalement_id' => $signalement->id]);
        }

        $this->command->info('DiagnosticSeeder : ' . $signalements->count() . ' diagnostic(s) créé(s).');
    }
}
