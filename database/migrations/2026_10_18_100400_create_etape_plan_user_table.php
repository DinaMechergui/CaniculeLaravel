<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('etape_plan_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('etape_plan_id')->constrained('etapes_plan')->cascadeOnDelete();
            $table->timestamp('fait_le');
            $table->unique(['user_id', 'etape_plan_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('etape_plan_user'); }
};
