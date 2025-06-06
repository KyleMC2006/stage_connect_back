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
        Schema::create('tuteur_stages', function (Blueprint $table) {
            $table->id();
            $table->string('nom_tuteur');
            $table->string('contact'); 
            $table->string('poste')->nullable();
            $table->foreignId('entreprise_id')
                ->constrained('entreprises')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tuteur_stages');
    }
};
