<?php

namespace Database\Seeders;

use App\Models\Signalement;
use App\Models\TypeSignalement;
use App\Models\User;
use Illuminate\Database\Seeder;

class SignalementSeeder extends Seeder
{
    /**
     * Crée 5 signalements par type de signalement,
     * rattachés à des utilisateurs existants.
     *
     * Pré-requis : UserSeeder et TypeSignalementSeeder doivent s'exécuter avant.
     */
    public function run(): void
    {
        // Récupère tous les types existants.
        $types = TypeSignalement::all();

        // Récupère tous les utilisateurs existants.
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('Aucun utilisateur trouvé. Le SignalementSeeder requiert le UserSeeder.');
            return;
        }

        foreach ($types as $type) {
            // Évite de recréer des signalements déjà présents pour ce type.
            if (Signalement::where('type_signalement_id', $type->id)->exists()) {
                continue;
            }

            for ($i = 0; $i < 5; $i++) {
                Signalement::create([
                    'type_signalement_id' => $type->id,
                    'user_id'             => $users->random()->id,
                    'description'         => fake()->paragraph(),
                    'statut'              => fake()->randomElement(array_keys(Signalement::STATUTS)),
                ]);
            }
        }
    }
}
