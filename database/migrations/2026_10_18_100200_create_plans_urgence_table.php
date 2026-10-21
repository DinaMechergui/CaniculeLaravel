<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plans_urgence', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 150);
            $table->text('description')->nullable();
            $table->string('type_evenement');   // canicule, coupure, les_deux
            $table->string('niveau_risque');    // faible, modere, eleve
            $table->string('public_cible');     // tous, personnes_agees, enfants, familles, animaux
            $table->boolean('actif')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans_urgence');
    }
};
