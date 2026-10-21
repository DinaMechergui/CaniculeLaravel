<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
       public function up(): void
   {
       Schema::create('conseils', function (Blueprint $table) {
           $table->id();
           $table->foreignId('categorie_conseil_id')
                 ->constrained('categorie_conseils')
                 ->restrictOnDelete();
           $table->string('titre', 150);
           $table->text('contenu');
           $table->timestamps();
       });
   }

   public function down(): void
   {
       Schema::dropIfExists('conseils');
   }
};
