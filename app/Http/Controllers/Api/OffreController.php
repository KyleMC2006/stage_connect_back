<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Offre;
use App\Models\Entreprise;
use App\Models\Etablissement; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class OffreController extends Controller
{
   

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

        // Récupère toutes les offres de cette entreprise
        $offres = Offre::where('entreprise_id', $entreprise->id)
                       ->with('domaine', 'candidatures') 
                       ->paginate(15);

        return response()->json($offres, 200);
    }
 
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Offre::active()->with('entreprise.user', 'domaine');

        // Si l'utilisateur est un étudiant, filtrer les offres en fonction des partenariats
        if ($user->role === 'etudiant' && $user->etudiant) {
            $etablissementId = $user->etudiant->etablissement_id;

            $offres = $query->where(function($q) use ($etablissementId) {
                $q->where('visibility', 'public')
                  ->orWhere(function ($subQ) use ($etablissementId) {
                      $subQ->where('visibility', 'partners_only')
                           ->whereHas('entreprise.partenariats', function ($partQ) use ($etablissementId) {
                               $partQ->where('etablissement_id', $etablissementId)
                                     ->where('statut', 'actif');
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
            'statut' => 'required|in:active,inactive',
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
            'statut' => $request->statut,
            'is_targeted' => $request->is_targeted ?? false, 
            'visibility' => $request->is_targeted ? ($request->visibility_scope ?? 'partners_only') : 'public', 
        ]);

        return response()->json(['message' => 'Offre créée avec succès.', 'data' => $offre], 201);
    }

    /**
     * Display the specified resource.
     * L'accès à une offre spécifique doit aussi respecter la visibilité.
     */
    public function show(Offre $offre)
    {
        $user = Auth::user();

        
        if ($user->role === 'entreprise' && $user->entreprise && $offre->entreprise_id === $user->entreprise->id) {
            $offre->load('entreprise.user', 'domaine');
            return response()->json($offre, 200);
        }

        
        if ($user->role === 'etudiant' && $user->etudiant) {
            $etablissementId = $user->etudiant->etablissement_id;

            // Vérifier si l'offre est publique
            if ($offre->visibility === 'public') {
                $offre->load('entreprise.user', 'domaine');
                return response()->json($offre, 200);
            }

            // Vérifier si l'offre est ciblée aux partenaires et si l'établissement de l'étudiant est partenaire actif
            if ($offre->visibility === 'partners_only') {
                $isPartner = $offre->entreprise->partenariats()
                                    ->where('etablissement_id', $etablissementId)
                                    ->where('statut', 'actif')
                                    ->exists();
                if ($isPartner) {
                    $offre->load('entreprise.user', 'domaine');
                    return response()->json($offre, 200);
                }
            }
        }

        
        return response()->json(['message' => 'Offre non trouvée ou non autorisée.'], 404);
    }

    public function update(Request $request, Offre $offre)
    {
        $user = Auth::user();

        if ($user->role !== 'entreprise' || !$user->entreprise || $offre->entreprise_id !== $user->entreprise->id) {
            return response()->json(['message' => 'Non autorisé à modifier cette offre.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'titre' => 'sometimes|required|string|max:191',
            'description' => 'sometimes|required|string',
            'domaine_id' => 'sometimes|required|exists:domaines,id',
            'adresse' => 'sometimes|required|string|max:191',
            'date_expiration' => 'sometimes|required|date|after:today',
            'duree_en_semaines' => 'nullable|integer|min:1',
            'date_debut' => 'sometimes|required|date|after_or_equal:today',
            'statut' => 'sometimes|required|in:active,inactive',
            'is_targeted' => 'sometimes|boolean',
            'visibility' => 'required_if:is_targeted,true|in:public,partners_only',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        
        $data = $request->all();
        if ($request->has('is_targeted')) {
            $data['visibility'] = $request->is_targeted ? ($request->visibility ?? 'partners_only') : 'public';
        }

        $offre->update($data);

        return response()->json(['message' => 'Offre mise à jour avec succès.', 'data' => $offre], 200);
    }

    /**
     * Remove the specified resource from storage.
     * Seules les entreprises propriétaires peuvent supprimer leurs offres.
     */
    public function destroy(Offre $offre)
    {
        $user = Auth::user();

        if ($user->role !== 'entreprise' || !$user->entreprise || $offre->entreprise_id !== $user->entreprise->id) {
            return response()->json(['message' => 'Non autorisé à supprimer cette offre.'], 403);
        }

        $offre->delete();

        return response()->json(['message' => 'Offre supprimée avec succès.'], 204);
    }
       return response()->json(['message' => 'Offre supprimée avec succès'], 200);
}
