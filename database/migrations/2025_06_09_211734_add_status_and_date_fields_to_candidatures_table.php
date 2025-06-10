<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            // Mettre à jour le statut existant pour accepter les nouvelles valeurs ou ajouter une nouvelle colonne
            // Si 'statut' est déjà string, vous pouvez simplement ajouter les valeurs via une Enum ou une validation
            // $table->string('statut')->default('en_attente_entreprise')->change(); // Si vous voulez une valeur par défaut mise à jour

            $table->timestamp('date_acceptation_entreprise')->nullable()->after('lettre_motivation');
            $table->timestamp('date_confirmation_etudiant')->nullable()->after('date_acceptation_entreprise');
            $table->timestamp('date_validation_etablissement')->nullable()->after('date_confirmation_etudiant');
            $table->string('justificatif_desistement')->nullable()->after('date_validation_etablissement');
        });
    }

    public function down(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->dropColumn(['date_acceptation_entreprise', 'date_confirmation_etudiant', 'date_validation_etablissement', 'justificatif_desistement']);
        });
    }
};