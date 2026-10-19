<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute la capacité d'accueil (nombre de personnes) à chaque point de
     * fraîcheur. Une nouvelle migration est utilisée (sans modifier l'ancienne)
     * afin de rester compatible avec les bases déjà en place.
     */
    public function up(): void
    {
        Schema::table('points_fraicheur', function (Blueprint $table) {
            $table->integer('capacite')->default(50)->after('accessible');
        });
    }

    public function down(): void
    {
        Schema::table('points_fraicheur', function (Blueprint $table) {
            $table->dropColumn('capacite');
        });
    }
};
