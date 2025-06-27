<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use App\Models\Etudiant;
use App\Models\Offre;
use App\Models\TuteurStage;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Pour la gestion des fichiers
use Carbon\Carbon; 

class StageController extends Controller
{
    

    /**

     * @return \Illuminate\Http\JsonResponse
     *  @param  \Illuminate\Http\Request  $request
     */
    public function index()
    {
        $today = Carbon::today();

        
        Stage::where('date_fin', '<', $today)
             ->where('statut', '!=', 'termine') 
             ->update(['statut' => 'termine']);

        Stage::where('date_debut', '<', $today)
            ->where('statut','en_attente') 
            ->update(['statut' => 'en_cours']);

        
        $stages = Stage::with(['etudiant.user','etudiant.filiere','etudiant.filannee', 'offre.entreprise.user'])->get();


        return response()->json($stages, 200);
    }

    /**
     * Store a newly created stage in storage.
     * POST /api/stages
     *
     * La création d'un stage est généralement déclenchée après l'acceptation d'une candidature.
     * Cela serait géré par l'entreprise ou un administrateur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
  
    public function show($id)
    {
        $stage = Stage::with(['etudiant.user','etudiant.filiere','etudiant.filannee', 'offre.entreprise.user']);->find($id);

        if (!$stage) {
            return response()->json(['message' => 'Stage non trouvé'], 404);
        }

        return response()->json($stage, 200);
    }


    public function update(Request $request, $id)
    {
        $stage = Stage::find($id);

        if (!$stage) {
            return response()->json(['message' => 'Stage non trouvé'], 404);
        }

        $user = Auth::user();

        $validator = Validator::make($request->all(), [

            'statut' => 'sometimes|string|in:en_attente,en_cours,termine,suspendu', 
            'note_stage' => 'nullable|sometimes|numeric|min:0|max:20',
            'commentaire_note' => 'nullable|sometimes|string',
            'rapport_stage' => 'nullable|sometimes|file|mimes:pdf,doc,docx|max:10240', 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        if ($request->has('statut') && $request->statut === 'suspendu') {
            if ($user->role === 'entreprise') {
              
                if ($user->entreprise->id !== $stage->offre->entreprise_id) {
                    return response()->json(['message' => 'Non autorisé. Vous ne pouvez suspendre que les stages liés à vos offres.'], 403);
                }
            } 
        }


        if ($request->has('note_stage') || $request->has('commentaire_note')) {
            if ($user->role === 'entreprise') {
                 if ($user->entreprise->id !== $stage->offre->entreprise_id) {
                    return response()->json(['message' => 'Non autorisé. Vous ne pouvez noter que les stages liés à vos offres.'], 403);
                }
            } 
        }
     
        if ($request->hasFile('rapport_stage')) {
           
            if ($user->role === 'etudiant' && $user->etudiant->id === $stage->etudiant_id) {
                
                if ($stage->statut !== 'termine') {
                    return response()->json(['message' => 'Le rapport de stage ne peut être uploadé que si le stage est terminé.'], 400); // Bad Request
                }

                $path = $request->file('rapport_stage')->store('rapports_stages', 'public');
                $stage->rapport_stage = $path; 
            } else {
                return response()->json(['message' => 'Non autorisé à uploader le rapport de stage.'], 403);
            }
        }

        $stage->update($request->except('rapport_stage')); 

        return response()->json(['message' => 'Stage mis à jour avec succès', 'stage' => $stage], 200);
    }

    public function destroy($id)
    {
        $stage = Stage::find($id);

        if (!$stage) {
            return response()->json(['message' => 'Stage non trouvé'], 404);
        }

        $user = Auth::user();
        if ($user->role === 'entreprise') {
            if ($user->entreprise->id !== $stage->offre->entreprise_id) {
                return response()->json(['message' => 'Non autorisé. Vous ne pouvez supprimer que les stages liés à vos offres.'], 403);
            }
        }

    
        if ($stage->rapport_stage && Storage::disk('public')->exists($stage->rapport_stage)) {
            Storage::disk('public')->delete($stage->rapport_stage);
        }

        $stage->delete();

        return response()->json(['message' => 'Stage supprimé avec succès'], 204); 
    }

    public function downloadRapport($id)
    {
        $stage = Stage::find($id);

        if (!$stage) {
            return response()->json(['message' => 'Stage non trouvé'], 404);
        }

        if (!$stage->rapport_stage) {
            return response()->json(['message' => 'Aucun rapport de stage disponible pour ce stage.'], 404);
        }

        
        $user = Auth::user();

        if ($user->role === 'etablissement') {
           
            if ($user->etablissement->id !== $stage->etudiant->etablissement_id) {
                return response()->json(['message' => 'Non autorisé. Vous n\'êtes pas l\'établissement de cet étudiant.'], 403);
            }
        } 
        
        if (!Storage::disk('public')->exists($stage->rapport_stage)) {
            return response()->json(['message' => 'Fichier de rapport non trouvé sur le serveur.'], 404);
        }

        return Storage::disk('public')->download($stage->rapport_stage);
    }
}