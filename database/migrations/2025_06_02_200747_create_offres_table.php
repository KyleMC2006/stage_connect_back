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
        Schema::create('offres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')
                ->constrained('entreprises')
                ->onDelete('cascade');
            $table->string('titre');
            $table->text('description');
            $table->foreignId('domaine_id')
                ->constrained('domaines')
                ->onDelete('cascade');
            $table->string('adresse');
            $table->date('date_expiration');
            $table->integer('duree_en_semaines');
            $table->date('date_debut');
            $table->enum('statut', ['active', 'expiree'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offres');
    }
};
