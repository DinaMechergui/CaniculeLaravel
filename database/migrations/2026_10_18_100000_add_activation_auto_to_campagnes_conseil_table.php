<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute la colonne activation_auto (booléen, défaut false) à la table
 * campagnes_conseil pour permettre l'activation automatique planifiée.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('campagnes_conseil', function (Blueprint $table) {
            $table->boolean('activation_auto')->default(false)->after('statut');
        });
    }

    public function down(): void
    {
        Schema::table('campagnes_conseil', function (Blueprint $table) {
            $table->dropColumn('activation_auto');
        });
    }
};
