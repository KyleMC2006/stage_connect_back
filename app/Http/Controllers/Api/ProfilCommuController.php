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
    
    /**

     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Charge les ProfilCommu
        $profilCommus = ProfilCommu::with([
            'user' => function ($query) {
                
                $query->with(['etudiant', 'etablissement', 'entreprise']);
            },
            'commentaire.user' 
        ])->get();

      
        
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


    public function show($id)
    {
        $profilCommu = ProfilCommu::find($id);

        if (!$profilCommu) {
            return response()->json(['message' => 'Utilisaterur non trouvée'], 404);
        }
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


    public function ajoutLike($id)
    {
        $profilCommu = ProfilCommu::find($id);

        if (!$profilCommu) {
            return response()->json(['message' => 'Post communautaire non trouvé.'], 404);
        }

        $userId = Auth::id(); // L'ID de l'utilisateur authentifié

        // Vérifier si l'utilisateur est authentifié
        if (!$userId) {
            return response()->json(['message' => 'Authentification requise pour aimer un post.'], 401);
        }

        // Vérifier si l'utilisateur a déjà liké ce post en utilisant la méthode sur le modèle
        if ($profilCommu->isLikedByUser($userId)) {
            return response()->json(['message' => 'Vous avez déjà liké ce post.'], 409); // 409 Conflict
        }

        // Ajouter une entrée dans la table pivot
        // La méthode `attach()` ajoute une relation.
        // Puisque la clé primaire composée dans la migration assure l'unicité, pas besoin de vérification supplémentaire ici,
        // mais nous l'avons fait avant pour un message d'erreur clair.
        $profilCommu->likers()->attach($userId);

        // Incrémente le compteur 'likes' sur le modèle ProfilCommu
        $profilCommu->increment('likes');

        return response()->json(['message' => 'Like ajouté avec succès.', 'data' => $profilCommu], 200);
    }

    public function retirerLike($id)
    {
        $profilCommu = ProfilCommu::find($id);

        if (!$profilCommu) {
            return response()->json(['message' => 'Post communautaire non trouvé.'], 404);
        }

        $userId = Auth::id(); // L'ID de l'utilisateur authentifié

        // Vérifier si l'utilisateur est authentifié
        if (!$userId) {
            return response()->json(['message' => 'Authentification requise pour retirer un like.'], 401);
        }

        // Vérifier si l'utilisateur a effectivement liké ce post (si l'entrée existe dans la pivot)
        if (!$profilCommu->isLikedByUser($userId)) {
            return response()->json(['message' => 'Vous n\'avez pas liké ce post, vous ne pouvez pas retirer le like.'], 400); // 400 Bad Request
        }

        // Retirer l'entrée de la table pivot
        // La méthode `detach()` supprime la relation. Elle retourne le nombre de lignes supprimées.
        $detachedCount = $profilCommu->likers()->detach($userId);

        // Décrémente le compteur 'likes' seulement si une ligne a été effectivement retirée et qu'il est supérieur à zéro.
        // La condition `if ($detachedCount)` est redondante si `isLikedByUser` est vrai, mais sécuritaire.
        if ($profilCommu->likes > 0) {
            $profilCommu->decrement('likes');
        }

        return response()->json(['message' => 'Like retiré avec succès.', 'data' => $profilCommu], 200);
    }
}