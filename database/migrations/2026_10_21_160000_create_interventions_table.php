<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupure_id')->constrained('coupures')->restrictOnDelete();
            $table->string('equipe', 100);
            $table->string('technicien', 100);
            $table->dateTime('date_intervention');
            $table->unsignedInteger('duree_estimee');
            $table->string('statut', 20);
            $table->text('rapport')->nullable();
            $table->timestamps();
            $table->index(['statut', 'date_intervention']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
