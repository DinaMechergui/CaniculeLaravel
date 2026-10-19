<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerSqliteMathFunctions();
    }

    /**
     * SQLite (utilisé par les tests en mémoire) ne fournit pas les fonctions
     * mathématiques de MySQL utilisées par le scope « lesPlusProches »
     * (Haversine) : RADIANS, ACOS, LEAST et GREATEST. On les recrée en PHP
     * afin de garder exactement la même formule de part et d'autre.
     *
     * Sans effet en production : seule une connexion SQLite est concernée.
     */
    private function registerSqliteMathFunctions(): void
    {
        $connection = config('database.default');

        if (config("database.connections.{$connection}.driver") !== 'sqlite') {
            return;
        }

        try {
            $pdo = DB::connection()->getPdo();
        } catch (\Throwable $e) {
            return;
        }

        if (! method_exists($pdo, 'sqliteCreateFunction')) {
            return;
        }

        $pdo->sqliteCreateFunction('radians', fn ($degrees) => deg2rad((float) $degrees));
        $pdo->sqliteCreateFunction('degrees', fn ($radians) => rad2deg((float) $radians));
        $pdo->sqliteCreateFunction('sin', fn ($value) => sin((float) $value));
        $pdo->sqliteCreateFunction('cos', fn ($value) => cos((float) $value));

        // Comme MySQL : ACOS() hors de [-1 ; 1] renvoie NULL.
        $pdo->sqliteCreateFunction('acos', static function ($value) {
            $value = (float) $value;

            return ($value < -1.0 || $value > 1.0) ? null : acos($value);
        });

        $pdo->sqliteCreateFunction('least', static fn (...$values) => min($values));
        $pdo->sqliteCreateFunction('greatest', static fn (...$values) => max($values));
    }
}
