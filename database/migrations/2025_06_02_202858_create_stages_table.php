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
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etudiant_id')
                ->constrained('etudiants')
                ->onDelete('cascade');
            $table->foreignId('offre_id')
                ->constrained('offres')
                ->onDelete('cascade');
            $table->foreignId('tuteur_stage_id')
                ->nullable()
                ->constrained('tuteur_stages')
                ->onDelete('set null');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->enum('statut', ['en_cours', 'termine', 'annule', 'suspendu'])
                ->default('en_cours');
            $table->string('rapport_stage')->nullable(); // PDF
            $table->integer('note_stage')->nullable(); 
            $table->text('commentaire_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};
