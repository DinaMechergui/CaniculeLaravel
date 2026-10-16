<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prévisions de fréquentation du module « Réservations ».
 *
 * Une ligne = une prévision pour un point de fraîcheur et une date donnés
 * (nombre de places attendues, taux de remplissage prévisionnel, source).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('previsions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('point_fraicheur_id')
                ->constrained('points_fraicheur')
                ->cascadeOnDelete();

            $table->date('date');
            $table->unsignedInteger('capacite')->default(0);
            $table->unsignedInteger('reservations_prevues')->default(0);
            $table->decimal('taux_remplissage', 5, 2)->default(0);

            // Origine de la prévision : « ia » ou calcul local « local ».
            $table->string('source', 20)->default('local');
            $table->text('explication')->nullable();

            $table->timestamps();

            $table->unique(['point_fraicheur_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('previsions');
    }
};
