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
        Schema::create('candidatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etudiant_id')
                ->constrained('etudiants')
                ->onDelete('cascade');
            $table->foreignId('offre_id')
                ->constrained('offres')
                ->onDelete('cascade');
            $table->enum('statut', ['en_attente', 'acceptee', 'refusee','desistement','en_attente_confirmation_etudiant','confirmee_etudiant', 'en_attente_validation_etablissement', 'validee_etablissement','desistement_etudiant'])
                ->default('en_attente');
            $table->date('date_postulat');
            $table->text('lettre_motivation')->nullable();
            $table->unique(['etudiant_id', 'offre_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidatures');
    }
};
