<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Création de la table des réservations de créneaux.
     *
     * Chaque réservation appartient à un point de fraîcheur (relation 1-N).
     * Aucune clé étrangère vers un autre module : le client est identifié par
     * son nom, son e-mail et un code de réservation unique.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('point_fraicheur_id')
                ->constrained('points_fraicheur')
                ->restrictOnDelete();

            // Code de réservation à 8 caractères majuscules, généré par l'app.
            $table->string('code', 8)->unique();

            $table->string('nom', 100);
            $table->string('email', 150);
            $table->string('telephone', 20)->nullable();

            $table->integer('nombre_personnes')->default(1);
            $table->date('date_reservation');
            $table->time('heure_debut');
            $table->time('heure_fin');

            $table->string('statut', 20)->default('en_attente');
            $table->text('besoins_specifiques')->nullable();

            // Données calculées / enrichies par l'IA (phase ultérieure).
            $table->json('tags_ia')->nullable();
            $table->string('priorite', 20)->default('normale');
            $table->decimal('score_risque_no_show', 5, 2)->nullable();
            $table->text('explication_ia')->nullable();

            $table->text('notes_admin')->nullable();

            $table->dateTime('confirmee_le')->nullable();
            $table->dateTime('annulee_le')->nullable();
            $table->dateTime('check_in_le')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index utiles pour les filtres et le calcul de capacité.
            $table->index('statut');
            $table->index('date_reservation');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
