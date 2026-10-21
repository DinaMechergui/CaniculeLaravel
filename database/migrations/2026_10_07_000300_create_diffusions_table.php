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
        Schema::create('diffusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulletin_alerte_id')->constrained('bulletins_alerte')->onDelete('cascade');
            $table->string('canal', 30); // sms, email, notification_push, sirene, affichage
            $table->string('contenu', 160);
            $table->unsignedInteger('destinataires_estimes')->default(0);
            $table->dateTime('date_envoi');
            $table->string('statut', 30)->default('planifiee'); // planifiee, envoyee, echec
            $table->timestamps();

            $table->index('canal');
            $table->index('statut');
            $table->index('date_envoi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diffusions');
    }
};
