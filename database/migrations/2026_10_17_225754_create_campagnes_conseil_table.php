<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campagnes_conseil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conseil_id')->constrained('conseils')->restrictOnDelete();
            $table->string('titre', 150);
            $table->string('public_cible');
            $table->string('canal');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->string('statut');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campagnes_conseil');
    }
};