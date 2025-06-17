<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Commentaire; 
use App\Models\ProfilCommu; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentaireController extends Controller
{
    /**
     
     * @return \Illuminate\Http\JsonResponse
     *  @param  \Illuminate\Http\Request  $request
     */

    public function index(ProfilCommu $profilCommu)
    {

        $commentaires = $profilCommu->commentaire()->with('user')->orderBy('created_at', 'asc')->get();

        return response()->json($commentaires, 200);
    }


    public function store(Request $request, ProfilCommu $profilCommu)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|max:191', 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $commentaire = $profilCommu->commentaire()->create([
            'user_id' => Auth::id(), 
            'comment' => $request->comment,
            // 'profil_commus_id' est automatiquement défini par la relation
        ]);

        $commentaire->load('user');

        return response()->json(['message' => 'Commentaire ajouté avec succès', 'data' => $commentaire], 201);
    }


    public function update(Request $request, Commentaire $commentaire)
    {
        
        if ($commentaire->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé à modifier ce commentaire.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'comment' => 'sometimes|required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $commentaire->update($request->only('comment'));

        return response()->json(['message' => 'Commentaire mis à jour avec succès', 'data' => $commentaire], 200);
    }

    public function destroy(Commentaire $commentaire)
    {
        $user = Auth::user();


        $commentaire->load('profilCommu'); 

        if ($commentaire->user_id !== $user->id &&
            $commentaire->profilCommu->user_id !== $user->id) // Si l'utilisateur n'est ni l'auteur du comm, ni l'auteur du post
        {
            
            return response()->json(['message' => 'Non autorisé à supprimer ce commentaire.'], 403);
        }

        $commentaire->delete();

        return response()->json(['message' => 'Commentaire supprimé avec succès'], 204);
    }
}