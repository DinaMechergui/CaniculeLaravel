<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute la colonne message (text nullable) à la table campagnes_conseil
 * pour stocker le message de diffusion généré ou saisi (IA phase 5).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('campagnes_conseil', function (Blueprint $table) {
            $table->text('message')->nullable()->after('activation_auto');
        });
    }

    public function down(): void
    {
        Schema::table('campagnes_conseil', function (Blueprint $table) {
            $table->dropColumn('message');
        });
    }
};
