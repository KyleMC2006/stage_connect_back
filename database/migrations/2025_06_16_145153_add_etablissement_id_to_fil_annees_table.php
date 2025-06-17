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
        Schema::table('filannee', function (Blueprint $table) {
            // Add etablissement_id column
            $table->foreignId('etablissement_id')
                  ->nullable() // Decide if it can be nullable or must always be present
                  ->constrained('etablissements') // Assumes your establishments table is named 'etablissements'
                  ->onDelete('cascade'); // Or 'set null' if you prefer

            // If you want to add an index for faster lookups
            $table->index('etablissement_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filannee', function (Blueprint $table) {
            
            $table->dropForeign(['etablissement_id']);
            
            $table->dropColumn('etablissement_id');
        });
    }
};
