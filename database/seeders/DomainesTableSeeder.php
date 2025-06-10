<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DomainesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $domaines = [
            "Technologies de l'Information et de la Communication (TIC)",
            "Santé et Pharmaceutique",
            "Éducation et Formation",
            "Finance et Assurances",
            "Agriculture et Agro-industrie",
            "Commerce et Distribution",
            "Tourisme et Hôtellerie",
            "Bâtiment et Travaux Publics (BTP)",
            "Transport et Logistique",
            "Énergie et Environnement",
            "Mines et Carrières",
            "Médias et Communication",
            "Artisanat et Production locale",
            "Services aux entreprises",
            "Conseil et Audit",
            "Industrie manufacturière",
            "Télécommunications",
            "Banque",
            "Microfinance",
            "Organisations Non Gouvernementales (ONG)",
            "Administration Publique",
            "Recherche et Développement",
            "Design et Création",
            "Événementiel et Loisirs",
            "Sécurité et Défense",
            "Immobilier",
            "Automobile",
            "Textile et Habillement",
            "Informatique / Services Numériques",
            "Alimentation et Boissons",
            "Mode et Beauté"
        ];

        // Vide la table 'domaines' avant d'insérer, utile en développement pour éviter les doublons.
        // ATTENTION : NE PAS UTILISER en production si vous voulez conserver des données existantes.
        DB::table('domaines')->truncate();

        // Prépare les données pour l'insertion
        $dataToInsert = [];
        foreach ($domaines as $domaineNom) {
            $dataToInsert[] = [
                'libdomaine' => $domaineNom,
                'created_at' => now(), // Ajoute les timestamps
                'updated_at' => now(), // Ajoute les timestamps
            ];
        }

        // Insère toutes les données en une seule fois
        DB::table('domaines')->insert($dataToInsert);
    }
}

//php artisan db:seed --class=DomainesTableSeeder