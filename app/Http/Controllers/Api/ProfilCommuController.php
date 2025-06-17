<?php



namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProfilCommu; // Assurez-vous d'importer le modèle ProfilCommu
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfilCommuController extends Controller
{
    /**

     * @return \Illuminate\Http\JsonResponse
     *  @param  \Illuminate\Http\Request  $request
     */
    public function __construct()
    {
        // Les utilisateurs non authentifiés peuvent voir les posts, mais pas les créer, liker ou supprimer.
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    /**

     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        
        $profilCommus = ProfilCommu::with(['user', 'commentaire.user'])
                                  ->orderBy('created_at', 'desc')
                                  ->get();

        return response()->json($profilCommus, 200);
    }

    public function store(Request $request)
    {
        
       

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $profilCommu = ProfilCommu::create([
            'user_id' => Auth::id(), 
            'likes' => 0, // Initialise les likes à 0 pour un nouveau post
        ]);

        return response()->json(['message' => 'Post communautaire créé avec succès', 'data' => $profilCommu], 201);
    }


    public function show(ProfilCommu $profilCommu)
    {
        // Charge l'utilisateur qui a posté et les commentaires avec leurs utilisateurs associés.
        $profilCommu->load(['user', 'commentaire.user']);
        return response()->json($profilCommu, 200);
    }





    public function destroy(ProfilCommu $profilCommu)
    {
        // Autorisation: Seul le propriétaire du post (ou un administrateur) peut le supprimer.
        if ($profilCommu->user_id !== Auth::id()) {
            // Optionnel: ajouter `|| Auth::user()->isAdmin()` si vous avez une méthode pour vérifier le rôle admin.
            return response()->json(['message' => 'Non autorisé à supprimer ce post.'], 403);
        }

        $profilCommu->delete();

        return response()->json(['message' => 'Post communautaire supprimé avec succès'], 204);
    }


    public function ajoutLike(ProfilCommu $profilCommu)
    {
        
        // Idée a inclure : table pivots likes
        $profilCommu->increment('likes'); // Incrémente le compteur 'likes'


        return response()->json(['message' => 'Like ajouté avec succès', 'data' => $profilCommu], 200);
    }

    public function retirerLike(ProfilCommu $profilCommu)
    {
        // Décrémente le compteur 'likes'.
        // Assurez-vous que le compteur ne descend pas en dessous de zéro.
        if ($profilCommu->likes > 0) {
            $profilCommu->decrement('likes');
        } else {
            return response()->json(['message' => 'Le nombre de likes est déjà à zéro.'], 400);
        }

        return response()->json(['message' => 'Like retiré avec succès', 'data' => $profilCommu], 200);
    }
}