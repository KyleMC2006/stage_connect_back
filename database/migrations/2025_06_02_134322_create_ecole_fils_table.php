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
        Schema::create('ecole_fils', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_filiere')
            ->references('id')
            ->on('filieres');
            $table->foreignId('id_etablissement')
            ->references('id')
            ->on('etablissements');
            $table->unique(['id_filiere','id_etablissement']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecole_fils');
    }
};
