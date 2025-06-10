<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EntrepriseController extends Controller
{
    // Affiche la liste des entreprises
    public function index()
    {
        $entreprises = Entreprise::paginate(15);
        return response()->json($entreprises, 200);
    }

    
    // Affiche les détails d'une entreprise
    public function show($id)
    {
        $entreprise = Entreprise::find($id);

        if (!$entreprise) {
            return response()->json(['message' => 'Entreprise non trouvée'], 404);
        }

        return response()->json($entreprise, 200);
    }

    
    public function update(Request $request, $id)
    {
        $entreprise = Entreprise::find($id);

        if (!$entreprise) {
            return response()->json(['message' => 'Entreprise non trouvée'], 404);
        }

        if ($entreprise->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nom_entreprise' => 'sometimes|string|max:191',
            'email_entreprise' => 'sometimes|email|unique:entreprises,email_entreprise,' . $id,
            'siteweb' => 'nullable|url',
            'adresse' => 'sometimes|string',
            'id_domaine' => 'sometimes|exists:domaines,id',
            'ville_id' => 'sometimes|exists:villes,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $entreprise->update($request->all());

        return response()->json(['message' => 'Entreprise mise à jour avec succès', 'entreprise' => $entreprise], 200);
    }

    
    public function destroy($id)
    {
        $entreprise = Entreprise::find($id);

        if (!$entreprise) {
            return response()->json(['message' => 'Entreprise non trouvée'], 404);
        }

        if ($entreprise->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $entreprise->delete();

        return response()->json(['message' => 'Entreprise supprimée avec succès'], 200);
    }
}
