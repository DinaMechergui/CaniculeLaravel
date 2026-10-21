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
        // Administrateur de test.
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        // Utilisateur simple de test.
        User::factory()->create([
            'name' => 'Utilisateur Test',
            'email' => 'user@example.com',
        ]);

        // Utilisateurs aléatoires.
        User::factory(10)->create();

        // Parents avant enfants pour chaque module.
        $this->call([
            TypePointSeeder::class,
            PointFraicheurSeeder::class,
            QuartierSeeder::class,
            CoupureSeeder::class,
            InterventionSeeder::class,
            ImpactCoupureSeeder::class,
        ]);
    }
}
