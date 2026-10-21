<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impacts_coupures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupure_id')->constrained('coupures')->restrictOnDelete();
            $table->string('nom_structure', 150);
            $table->string('categorie', 30);
            $table->string('adresse', 255);
            $table->unsignedInteger('personnes_affectees');
            $table->string('niveau_gravite', 20);
            $table->dateTime('date_signalement');
            $table->text('mesures_urgence')->nullable();
            $table->string('statut', 20);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['niveau_gravite', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impacts_coupures');
    }
};
