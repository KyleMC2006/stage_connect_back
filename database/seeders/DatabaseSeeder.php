<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Etudiant;
use App\Models\Etablissement;
use App\Models\Filiere;
use App\Models\Entreprise;
use App\Models\Domaine;
use App\Models\Ville;
use App\Models\Offre;
use App\Models\Candidature;
use App\Models\Stage;
use App\Models\TuteurStage;
use App\Models\Message;
use App\Models\Commentaire;
use App\Models\ProfilCommu;
use App\Models\Annee;
use App\Models\EcoleFil;
use App\Models\FilAnnee;
use App\Models\Notification;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {   $secteurs = [
            'Informatique',
            'Finance',
            'Santé',
            'Education',
            'BTP',
            'Transport',
            'Agroalimentaire',
            'Télécommunications',
            'Immobilier',
            'Tourisme',
        ];
        foreach($secteurs as $nom){
            Domaine::create(['libdomaine' => $nom]);
        }
        $filieres = [
            'Informatique de GEstion',
            'GEstion Finance Comptable',
            'Santé',
            'Génie civile',
            'BTP',
            'Transport et Logistique',
            'Agroalimentaire',
            'Télécommunications',
            'Immobilier',
            'Tourisme',
        ];
        foreach($filieres as $nom){
            Filiere::create(['libfil' => $nom]);
        }
        
        $villes = [
            'Cotonou',
            'Parakou',
            'Godomey',
            'Abomey-Calavi',
            'Malanville',
            'Accra',
            'Bohicon',
            'Lokossa',
            'Porto-Novo',
            'Ouidah',
        ];
        foreach($villes as $nom){
            Ville::create(['nom_ville' => $nom]);
        }

        $annees = [
            'Première année',
            'Deuxième année', 
            'Troisième année',
            'Quatrième année', 
            'Cinquième année', 
            'Master 1', 
            'Master 2',
        ];
        foreach($annees as $nom) {
            Annee::create(['libannee' => $nom]);
        }

        User::factory(15)->create();
        User::all()->each(function($user){
            if($user->role === 'etudiant'){
                Etudiant::factory()->create(['user_id' => $user->id]);
            } elseif ($user->role === 'entreprise'){
                Entreprise::factory()->create(['user_id' => $user->id]);
            } else if ($user->role === 'etablissement'){
                Etablissement::factory()->create(['user_id' => $user->id]);
            }
        });

        $etablissements = Etablissement::all();
        $filieres = Filiere::all();
        
        foreach($etablissements as $etablissement) {
            // Etablissent - Filiere
            $filieresAleatoires = $filieres->random(rand(3, 6));
            foreach($filieresAleatoires as $filiere) {
                EcoleFil::create([
                    'id_etablissement' => $etablissement->id,
                    'id_filiere' => $filiere->id
                ]);
            }
        }

        $annees = Annee::all();
        foreach($filieres as $filiere) {
            // Chaque filière a 3-5 années
            $anneesAleatoires = $annees->random(rand(3, 5));
            foreach($anneesAleatoires as $annee) {
                FilAnnee::firstOrCreate([
                    'id_fil' => $filiere->id,
                    'id_annee' => $annee->id
                ]);
            }
        }
        
        
        
        Offre::factory()->count(10)->create();

        $etudiants = Etudiant::all();
        $offres = Offre::all();
        
        foreach($etudiants as $etudiant) {
            // Etudiant-Offre
            $offresAleatoires = $offres->random(rand(1, 4));
            foreach($offresAleatoires as $offre) {
                Candidature::factory()->create([
                    'etudiant_id' => $etudiant->id,
                    'offre_id' => $offre->id
                ]);
            }
        }
        
        //  Entreprise-tuteurs
        $entreprises = Entreprise::all();
        foreach($entreprises as $entreprise) {
            TuteurStage::factory(rand(1, 3))->create([
                'entreprise_id' => $entreprise->id
            ]);
        }
        
        $candidaturesAcceptees = Candidature::where('statut', 'acceptee')->get();
        
        foreach($candidaturesAcceptees->take(10) as $candidature) {
            Stage::factory()->create([
                'etudiant_id' => $candidature->etudiant_id,
                'offre_id' => $candidature->offre_id,
                'tuteur_stage_id' => TuteurStage::where('entreprise_id', $candidature->offre->entreprise_id)
                    ->inRandomOrder()->first()?->id
            ]);
        }

        $users = User::inRandomOrder()->take(10)->get();
        foreach($users as $user) {
            $profil = ProfilCommu::factory()->create(['user_id' => $user->id]);
            
            
            Commentaire::factory(rand(1, 5))->create([
                'profil_commus_id' => $profil->id
            ]);
        }

        
        Notification::factory()->count(3)->create();
               
        

    
        Message::factory(30)->create();
        

        
    }
}
