<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Offre;
use App\Models\Entreprise;
use App\Models\Etablissement; 
use App\Models\Candidature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OffreController extends Controller
{
    /**
     
     * @return \Illuminate\Http\JsonResponse
     *  @param  \Illuminate\Http\Request  $request
     */

    public function getStatistics()
    {
        $user = Auth::user();
        $totalOffres = 0;
        $activeOffres = 0;
        $expiredOffres = 0;
        $pendingOffres = 0; // Pour les entreprises, les offres 'en attente' de publication

        if ($user->role === 'entreprise' && $user->entreprise) {
            $entrepriseId = $user->entreprise->id;
            $totalOffres = Offre::where('entreprise_id', $entrepriseId)->count();
            $activeOffres = Offre::where('entreprise_id', $entrepriseId)
                                ->where('statut', 'active')
                                ->where('date_expiration', '>=', now())
                                ->count();
            $expiredOffres = Offre::where('entreprise_id', $entrepriseId)
                                ->where('date_expiration', '<', now())
                                ->count();


            return response()->json([
                'total_offres' => $totalOffres,
                'active_offres' => $activeOffres,
                'expired_offres' => $expiredOffres,
                'message' => 'Statistiques des offres pour votre entreprise.',
            ], 200);

        }
    }

    public function mesOffres()
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est bien une entreprise
        if ($user->role !== 'entreprise') {
            return response()->json(['message' => 'Non autorisé. Seules les entreprises peuvent voir leurs propres offres.'], 403);
        }

        $entreprise = $user->entreprise;

        if (!$entreprise) {
            return response()->json(['message' => 'Profil entreprise non complet.'], 400);
        }

        $today = Carbon::today();

        
        Offre::where('date_expiration', '<', $today)
             ->where('statut', '!=', 'expiree') 
             ->update(['statut' => 'expiree']);

        // Récupère toutes les offres de cette entreprise
        $offres = Offre::where('entreprise_id', $entreprise->id)
                       ->with('domaine')->get();

        return response()->json($offres, 200);
    }
 
    public function index(Request $request)
    {

        $user = Auth::user();
        $today = Carbon::today();

        
        Offre::where('date_expiration', '<', $today)
             ->where('statut', '!=', 'expiree') 
             ->update(['statut' => 'expiree']);
             
        $query = Offre::with('entreprise.user', 'domaine');

        // Si l'utilisateur est un étudiant, filtrer les offres en fonction des partenariats
        if ($user->role === 'etudiant' && $user->etudiant) {
            $etablissementId = $user->etudiant->etablissement_id;

            $offres = $query->where(function($q) use ($etablissementId) {
                $q->where('visibility', 'public')->where('statut','active')
                  ->orWhere(function ($subQ) use ($etablissementId) {
                      $subQ->where('visibility', 'partners_only')
                           ->whereHas('entreprise.partenariats', function ($partQ) use ($etablissementId) {
                               $partQ->where('etablissement_id', $etablissementId)
                                     ->where('statut', 'active');
                           });
                  });
            })->get();

        } elseif ($user->role === 'entreprise' && $user->entreprise) {
            
            $offres = $query->where('entreprise_id', $user->entreprise->id)->get();
        } else {

            return response()->json(['message' => 'Non autorisé à lister les offres ou profil non pertinent.'], 403);
        }

        return response()->json($offres, 200);
    }


    public function show(int $id)
    {
        
        // 1. Récupérer l'offre spécifique
        $offre = Offre::with('entreprise','domaine')->find($id);

        if (!$offre) {
            return response()->json(['message' => 'Offre non trouvée.'], 404);
        }

        // 2. Récupérer le nombre de candidatures pour cette offre SANS ajouter d'attribut
        
        $nombreCandidatures = Candidature::where('offre_id', $offre->id)->count();


        return response()->json([
            'offre' => $offre, // L'objet offre complet
            'nombre_candidatures' => $nombreCandidatures, // Le compte que vous avez récupéré
            'message' => 'Détails de l\'offre et nombre de candidatures récupérés avec succès.'
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'entreprise' || !$user->entreprise) {
            return response()->json(['message' => 'Seules les entreprises peuvent créer des offres.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:191',
            'description' => 'required|string',
            'domaine_id' => 'required|exists:domaines,id',
            'adresse' => 'required|string|max:191',
            'date_expiration' => 'required|date|after:today',
            'duree_en_semaines' => 'nullable|integer|min:1',
            'date_debut' => 'required|date|after_or_equal:today',
            'is_targeted' => 'boolean', 
            'visibility' => 'required_if:is_targeted,true|in:public,partners_only', 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $offre = Offre::create([
            'entreprise_id' => $user->entreprise->id,
            'titre' => $request->titre,
            'description' => $request->description,
            'domaine_id' => $request->domaine_id,
            'adresse' => $request->adresse,
            'date_expiration' => $request->date_expiration,
            'duree_en_semaines' => $request->duree_en_semaines,
            'date_debut' => $request->date_debut,
            'statut' => 'active',
            'is_targeted' => $request->is_targeted ?? false, 
            'visibility' => $request->is_targeted ? ($request->visibility ?? 'partners_only') : 'public', 
        ]);

        return response()->json(['message' => 'Offre créée avec succès.', 'data' => $offre], 201);
    }


    
    

    public function update(Request $request, Offre $offre)
    {
        $user = Auth::user();

        if ($user->role !== 'entreprise' ) {
            return response()->json(['message' => 'Non autorisé à modifier cette offre.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'statut' => 'sometimes|required|in:active,expiree',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $offre->update($request->all());

        return response()->json($offre, 200);
    }



    public function destroy(Offre $offre)
    {
        $user = Auth::user();

        if ($user->role !== 'entreprise' || !$user->entreprise || $offre->entreprise_id !== $user->entreprise->id) {
            return response()->json(['message' => 'Non autorisé à supprimer cette offre.'], 403);
        }

        $offre->delete();

        return response()->json(['message' => 'Offre supprimée avec succès.'], 204);
    }
       
}


