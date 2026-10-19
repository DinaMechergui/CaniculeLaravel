<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Création de la table diagnostics.
 *
 * Un diagnostic décrit les symptômes d'une panne / coupure signalée.
 * Les colonnes « IA » sont remplies plus tard par le modèle de prédiction.
 *
 * Doit s'exécuter APRÈS la migration create_signalements_table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnostics', function (Blueprint $table) {
            $table->id();

            // Un seul diagnostic par signalement (relation 1:1).
            $table->foreignId('signalement_id')
                ->unique()
                ->constrained('signalements')
                ->cascadeOnDelete();

            // ----- Symptômes déclarés -----
            $table->string('zone_touchee', 20);          // logement, immeuble, rue, quartier
            $table->boolean('disjoncteur_saute')->default(false);
            $table->boolean('baisse_tension_avant')->default(false);
            $table->boolean('odeur_brule')->default(false);
            $table->boolean('etincelles')->default(false);
            $table->boolean('recurrent')->default(false);
            $table->boolean('personnes_vulnerables')->default(false);
            $table->string('heure_panne', 5);             // format HH:MM (ex : 14:35)
            $table->decimal('temperature', 4, 1)->nullable(); // °C extérieurs

            // ----- Résultats du modèle IA (nullable tant que le modèle n'a pas tourné) -----
            $table->string('cause_predite', 30)->nullable();
            $table->decimal('confiance', 5, 4)->nullable(); // 0 à 1
            $table->string('niveau_danger', 10)->nullable(); // faible, moyen, eleve

            // ----- Vérité terrain saisie par l'administrateur (sert à ré-entraîner le modèle) -----
            $table->string('cause_reelle', 30)->nullable();
            $table->text('remarque')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnostics');
    }
};
