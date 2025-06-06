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
        Schema::create('entreprises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');
            $table->string('nom_entreprise');
            $table->string('email_entreprise');
            $table->string('siteweb')->nullable();
            $table->string('adresse')->nullable();
            $table->string('RCCM')->unique();
            $table->foreignId('id_domaine')
            ->references('id')
            ->on('domaines');
            $table->foreignId('ville_id')
            ->references('id')
            ->on('villes')
            ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entreprises');
    }
};
