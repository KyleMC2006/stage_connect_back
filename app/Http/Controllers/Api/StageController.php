<?php

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use App\Models\Etudiant;
use App\Models\Offre;
use App\Models\TuteurStage;
use App\Models\User; // Pour accéder au rôle de l'utilisateur authentifié
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Pour la gestion des fichiers

class StageController extends Controller
{
    

    /**

     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $stages = Stage::with(['etudiant.user', 'offre.entreprise.user', 'tuteurStage'])->get();


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
        $stage = Stage::with(['etudiant.user', 'offre.entreprise.user', 'tuteurStage'])->find($id);

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

            'tuteur_stage_id' => 'nullable|sometimes|exists:tuteur_stages,id',
            'date_fin' => 'sometimes|date|after_or_equal:date_debut',
            'statut' => 'sometimes|string|in:en_attente,en_cours,termine,suspendu', 
            'note_stage' => 'nullable|sometimes|numeric|min:0|max:20',
            'commentaire_note' => 'nullable|sometimes|string',
            'rapport_stage' => 'nullable|sometimes|file|mimes:pdf,doc,docx|max:10240', // Max 10MB
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
            } elseif ($user->role === 'tuteur_stage') { 
                if ($user->tuteurStage->id !== $stage->tuteur_stage_id) {
                    return response()->json(['message' => 'Non autorisé. Vous ne pouvez noter que les stages dont vous êtes le tuteur.'], 403);
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