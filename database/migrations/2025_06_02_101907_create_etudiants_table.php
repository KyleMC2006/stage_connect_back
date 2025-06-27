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
        Schema::create('etudiants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');
            $table->text('competences')->nullable();
            $table->text('parcours')->nullable();
            $table->string('CV')->nullable();
            $table->foreignId('id_etablissement')
            ->references('id')
            ->on('etablissements');
            $table->text('projets')->nullable();
            $table->foreignId('id_filiere')
            ->references('id')
            ->on('filieres');
            $table->foreignId('filannee_id')->constrained();
            $table->string('matricule')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etudiants');
    }
};
