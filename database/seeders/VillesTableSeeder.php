<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VillesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $villes = [
            'Cotonou',
            'Abomey-Calavi',
            'Ouidah',
            'Allada',
            'Toffo',
            'Zè',
            'Porto-Novo',
            'Sèmè-Kpodji',
            'Adjarra',
            'Aguégués',
            'Akpro-Missérété',
            'Avrankou',
            'Bonou',
            'Dangbo',
            'Ekpè',
            'Ifangni',
            'Missérété',
            'Ouèssè',
            'Kétou',
            'Pobè',
            'Adja-Ouèrè',
            'Ifangni',
            'Sakété',
            'Djougou',
            'Bassila',
            'Copargo',
            'Ouaké',
            'Parakou',
            'N\'Dali',
            'Pèrèrè',
            'Sinendé',
            'Tchaourou',
            'Bembèrèkè',
            'Kalalé',
            'Nikki',
            'Natitingou',
            'Tanguiéta',
            'Banikoara',
            'Kandi',
            'Gogounou',
            'Karimama',
            'Malanville',
            'Ségbana',
            'Dogbo',
            'Aplahoué',
            'Djakotomey',
            'Klhouékanmey',
            'Lalo',
            'Toviklin',
            'Lokossa',
            'Athiémé',
            'Bopa',
            'Comé',
            'Grand-Popo',
            'Houéyogbé',
            'Abomey',
            'Bohicon',
            'Covè',
            'Djidja',
            'Ouinhi',
            'Za-Kpota',
            'Zogbodomey',
            'Save',
            'Savalou',
            'Dassa-Zoumè',
            'Glazoué',
            'Ouèssè',
            'Bantè',
            'Boukoumbé',
            'Cobly',
            'Kérou',
            'Kouandé',
            'Pehonko',
            'Toukountouna',
            'Agbangnizoun',
            'Banamè',
            'Zagnanado',
            'Come',
            'Kpomasse',
            'Tori-Bossito'
        ];

        // Vide la table 'villes' avant d'insérer, utile en développement pour éviter les doublons.
        // ATTENTION : NE PAS UTILISER en production si vous voulez conserver des données existantes.
        DB::table('villes')->truncate();

        // Prépare les données pour l'insertion (chaque nom doit être un tableau associatif)
        $dataToInsert = [];
        foreach ($villes as $villeNom) {
            $dataToInsert[] = [
                'nom_ville' => $villeNom,
                'created_at' => now(), // Ajoute les timestamps
                'updated_at' => now(), // Ajoute les timestamps
            ];
        }

        // Insère toutes les données en une seule fois
        DB::table('villes')->insert($dataToInsert);
    }
}

//php artisan db:seed --class=VillesTableSeeder