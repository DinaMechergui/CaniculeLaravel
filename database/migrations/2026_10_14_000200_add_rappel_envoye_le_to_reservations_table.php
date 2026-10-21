<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute la date d'envoi du rappel de réservation.
     *
     * Cette colonne permet à la commande « reservations:rappels » d'être
     * idempotente : un client ne reçoit qu'un seul avertissement.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dateTime('rappel_envoye_le')->nullable()->after('check_in_le');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('rappel_envoye_le');
        });
    }
};
