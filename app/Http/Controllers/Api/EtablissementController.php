<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EtablissementController extends Controller
{
    // GET /api/etablissements
    public function index()
    {
        $etablissements = Etablissement::paginate(15);
        return response()->json($etablissements, 200);
    }

    

    // GET /api/etablissements/{id}
    public function show($id)
    {
        $etablissements = Etablissement::find($id);

        if (!$etablissement) {
            return response()->json(['message' => 'Etablissement non trouvé'], 404);
        }

        return response()->json($etablissement, 200);
    }

    // PUT /api/etablissements/{id}
    public function update(Request $request, $id)
    {
        $etablissement = Etablissement::find($id);

        if (!$etablissement) {
            return response()->json(['message' => 'Etablissement non trouvé'], 404);
        }

        if ($etablissement->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nom_etablissement' => 'sometimes|string|max:255',
            'siteweb' => 'nullable|url',
            'adresse' => 'sometimes|string|max:255',
            'ville_id' => 'sometimes|exists:villes,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $etablissement->update($request->all());

        return response()->json(['message' => 'Etablissement mis à jour avec succès', 'etablissement' => $etablissement], 200);
    }

    // DELETE /api/etablissements/{id}
    public function destroy($id)
    {
        $etablissement = Etablissement::find($id);

        if (!$etablissement) {
            return response()->json(['message' => 'Etablissement non trouvé'], 404);
        }

        if ($etablissement->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $etablissement->delete();

        return response()->json(['message' => 'Etablissement supprimé avec succès'], 200);
    }
s
    public function gererFiliereAnnees(Request $request, $id)
    {
        $etablissement = Etablissement::find($id);

        if (!$etablissement) {
            return response()->json(['message' => 'Etablissement non trouvé'], 404);
        }

        // Vérifier que l'utilisateur authentifié est le propriétaire de cet établissement
        if ($etablissement->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé. Vous n\'êtes pas le propriétaire de cet établissement.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'filiere_id' => 'required|exists:filieres,id', 
            'annee_ids' => 'required|array',              
            'annee_ids.*' => 'exists:annees,id',          
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $filiereId = $request->filiere_id;
        $anneeIds = $request->annee_ids;

       
        $etablissement->ecolefil()->syncWithoutDetaching([$filiereId]);

        
        $filiere = Filiere::find($filiereId);
        
        $filiere->filannee()->sync($anneeIds);

        return response()->json([
            'message' => 'Filière et années associées à l\'établissement avec succès.',
            'filiere_id' => $filiereId,
            'annees_associees' => $filiere->filannee()->get()
        ], 200);
    }

    public function getFilieresAnnees($id)
    {
        $etablissement = Etablissement::find($id);

        if (!$etablissement) {
            return response()->json(['message' => 'Etablissement non trouvé'], 404);
        }

        // Vérifier que l'utilisateur authentifié est le propriétaire de cet établissement
        if ($etablissement->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé. Vous n\'êtes pas le propriétaire de cet établissement.'], 403);
        }

        // Charger les filières de cet établissement, et pour chaque filière, charger ses années
        $etablissementWithFilieres = Etablissement::with('ecolefil.filannee')
                                                ->where('id', $id)
                                                ->first();



        return response()->json([
            'message' => 'Filières de l\'établissement avec années récupérées avec succès.',
            'etablissement' => $etablissementWithFilieres
        ], 200);
    }

}
