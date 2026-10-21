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
        Schema::create('bulletins_alerte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alerte_id')->constrained('alertes')->onDelete('cascade');
            $table->string('niveau', 20); // vert, jaune, orange, rouge
            $table->string('evolution', 30); // aggravation, stable, amelioration, levee
            $table->text('message');
            $table->text('conduite_a_tenir')->nullable();
            $table->integer('temperature_max')->nullable();
            $table->dateTime('date_publication');
            $table->timestamps();

            $table->index('niveau');
            $table->index('evolution');
            $table->index('date_publication');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulletins_alerte');
    }
};
