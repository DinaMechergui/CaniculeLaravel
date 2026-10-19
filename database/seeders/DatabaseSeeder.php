<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Comptes de test :
     *  - admin@example.com / password  (administrateur)
     *  - user@example.com   / password  (utilisateur simple)
     *  - 10 utilisateurs aléatoires
     */
    public function run(): void
    {
        // Administrateur de test (ré-exécutable sans erreur de doublon).
        if (! User::where('email', 'admin@example.com')->exists()) {
            User::factory()->admin()->create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
            ]);
        }

        // Utilisateur simple de test.
        if (! User::where('email', 'user@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Utilisateur Test',
                'email' => 'user@example.com',
            ]);
        }

        // Utilisateurs aléatoires : créés une seule fois.
        if (User::count() <= 2) {
            User::factory(10)->create();
        }

        // Parents avant enfants pour chaque module.
        $this->call([
            TypePointSeeder::class,
            PointFraicheurSeeder::class,
            ReservationSeeder::class,
            PrevisionSeeder::class,
            DashboardSeeder::class,
            QuartierSeeder::class,
            CoupureSeeder::class,
            NiveauAlerteSeeder::class,
            AlerteMeteoSeeder::class,
            CategorieConseilSeeder::class,
            ConseilSeeder::class,
            TypeSignalementSeeder::class,
            SignalementSeeder::class,
            DiagnosticSeeder::class,  // Après SignalementSeeder (dépendance).
        ]);
    }
}
