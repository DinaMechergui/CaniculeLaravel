<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('etapes_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_urgence_id')->constrained('plans_urgence')->cascadeOnDelete();
            $table->string('titre', 150);
            $table->text('description')->nullable();
            $table->string('moment', 20);
            $table->unsignedInteger('ordre');
            $table->boolean('obligatoire')->default(false);
            $table->timestamps();
            $table->index(['plan_urgence_id', 'moment', 'ordre']);
        });
    }

    public function down(): void { Schema::dropIfExists('etapes_plan'); }
};
