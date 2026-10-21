<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Instantanés d'indicateurs (« dashboard ») du module « Réservations ».
 *
 * Une ligne = les indicateurs agrégés d'une date, éventuellement pour un
 * point de fraîcheur précis (sinon `point_fraicheur_id` est nul = global).
 * Ces instantanés alimentent la page d'analyse du back office.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboards', function (Blueprint $table) {
            $table->id();

            $table->date('date');

            // Nul = indicateurs globaux (tous points confondus).
            $table->foreignId('point_fraicheur_id')
                ->nullable()
                ->constrained('points_fraicheur')
                ->cascadeOnDelete();

            $table->unsignedInteger('total_reservations')->default(0);
            $table->unsignedInteger('total_personnes')->default(0);
            $table->unsignedInteger('en_liste_attente')->default(0);

            $table->decimal('taux_presence', 5, 2)->default(0);
            $table->decimal('taux_no_show', 5, 2)->default(0);
            $table->decimal('taux_annulation', 5, 2)->default(0);
            $table->decimal('score_risque_moyen', 5, 2)->nullable();

            $table->string('source', 20)->default('local');
            $table->timestamps();

            $table->unique(['date', 'point_fraicheur_id'], 'dashboards_date_point_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboards');
    }
};
