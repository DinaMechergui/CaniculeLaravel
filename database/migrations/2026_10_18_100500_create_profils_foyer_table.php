<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('profils_foyer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('nb_adultes')->default(1);
            $table->unsignedTinyInteger('nb_enfants')->default(0);
            $table->unsignedTinyInteger('nb_personnes_agees')->default(0);
            $table->boolean('a_animaux')->default(false);
            $table->string('type_logement', 20);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('profils_foyer'); }
};
