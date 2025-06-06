<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Offre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OffreController extends Controller
{
    
    public function index()
    {
        $offres = Offre::with(['entreprise', 'domaine'])->get();
        return response()->json($offres);
    }

    
    public function show($id)
    {
        $offre = Offre::with(['entreprise', 'domaine'])->find($id);
        if (!$offre) {
            return response()->json(['message' => 'Offre non trouvée'], 404);
        }
        return response()->json($offre);
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:191',
            'description' => 'required|string',
            'domaine_id' => 'required|exists:domaines,id',
            'adresse' => 'required|string',
            'date_expiration' => 'required|date|after_or_equal:today',
            'duree_en_semaines' => 'required|integer|min:1',
            'date_debut' => 'required|date|after_or_equal:today',
            'statut' => 'nullable|string|in:active,expiree',
        ]);

        $user = Auth::user();
        if (!$user || $user->role !== 'entreprise' || !$user->entreprise) {
            return response()->json(['message' => 'Non autorisé.'], 403);
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
            'statut' => $request->statut ?? 'active',
        ]);

        return response()->json($offre, 201);
    }

    
    public function update(Request $request, $id)
    {
        $offre = Offre::find($id);
        if (!$offre) {
            return response()->json(['message' => 'Offre non trouvée'], 404);
        }

        $user = Auth::user();
        if (!$user || !$user->entreprise || $user->entreprise->id !== $offre->entreprise_id) {
            return response()->json(['message' => 'Accès interdit.'], 403);
        }

        $request->validate([
            'titre' => 'sometimes|string|max:191',
            'description' => 'sometimes|string',
            'domaine_id' => 'sometimes|exists:domaines,id',
            'adresse' => 'sometimes|string',
            'date_expiration' => 'sometimes|date|after_or_equal:today',
            'duree_en_semaines' => 'sometimes|integer|min:1',
            'date_debut' => 'sometimes|date|after_or_equal:today',
            'statut' => 'sometimes|string|in:active,expiree',
        ]);

        $offre->update($request->all());

        return response()->json($offre);
    }

    // Supprimer une offre
    public function destroy($id)
    {
        $offre = Offre::find($id);
        if (!$offre) {
            return response()->json(['message' => 'Offre non trouvée'], 404);
        }

        $user = Auth::user();
        if (!$user || !$user->entreprise || $user->entreprise->id !== $offre->entreprise_id) {
            return response()->json(['message' => 'Accès interdit.'], 403);
        }

        $offre->delete();

        return response()->json(['message' => 'Offre supprimée avec succès.']);
    }
}
