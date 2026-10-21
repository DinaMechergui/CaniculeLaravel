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
        Schema::create('alertes', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 150);
            $table->string('type', 50); // canicule, orage, inondation, vent_fort, incendie_foret, pollution_air
            $table->string('zone_concernee', 150);
            $table->text('description');
            $table->string('source', 100)->nullable();
            $table->dateTime('date_debut');
            $table->dateTime('date_fin')->nullable();
            $table->string('statut', 30)->default('en_cours'); // en_cours, terminee
            $table->timestamps();

            $table->index('statut');
            $table->index('type');
            $table->index('date_debut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertes');
    }
};
