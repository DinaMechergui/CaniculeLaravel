<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mesures_prevention', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alerte_meteo_id')
                ->constrained('alertes_meteo')
                ->cascadeOnDelete();
            $table->string('titre', 150);
            $table->string('type', 50);
            $table->text('description');
            $table->string('responsable', 100);
            $table->dateTime('date_debut');
            $table->string('statut', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesures_prevention');
    }
};
