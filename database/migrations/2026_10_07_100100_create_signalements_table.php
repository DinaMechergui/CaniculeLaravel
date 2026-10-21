<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Création de la table signalements.
     */
    public function up(): void
    {
        Schema::create('signalements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_signalement_id')
                  ->constrained('type_signalements')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            $table->text('description');
            $table->string('statut')->default('nouveau');
            $table->timestamps();
        });
    }

    /**
     * Suppression de la table signalements.
     */
    public function down(): void
    {
        Schema::dropIfExists('signalements');
    }
};
