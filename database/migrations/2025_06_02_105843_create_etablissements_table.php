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
        Schema::create('etablissements', function (Blueprint $table){
            $table->id();
            $table->foreignId('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');
            $table->string('nom_etablissement');
            $table->string('siteweb')->nullable();
            $table->string('adresse')->nullable();
            $table->string('numero_agrement')->unique();
            $table->foreignId('ville_id')
            ->references('id')
            ->on('villes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etablissements');
    }
};
