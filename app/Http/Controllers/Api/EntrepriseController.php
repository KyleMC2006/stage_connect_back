<?php

namespace App\Http\Controllers\API;

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
        $entreprises = Entreprise::with('user', 'domaine', 'ville')->get();
        return response()->json($entreprises, 200);
    }

    // Crée une nouvelle entreprise (lié à l'utilisateur connecté)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom_entreprise' => 'required|string|max:255',
            'email_entreprise' => 'required|email|unique:entreprises,email_entreprise',
            'siteweb' => 'nullable|url',
            'adresse' => 'required|string',
            'RCCM' => 'required|string',
            'id_domaine' => 'required|exists:domaines,id',
            'ville_id' => 'required|exists:villes,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $entreprise = Entreprise::create([
            'user_id' => Auth::id(),
            'nom_entreprise' => $request->nom_entreprise,
            'email_entreprise' => $request->email_entreprise,
            'siteweb' => $request->siteweb,
            'adresse' => $request->adresse,
            'RCCM' => $request->RCCM,
            'id_domaine' => $request->id_domaine,
            'ville_id' => $request->ville_id,
        ]);

        return response()->json(['message' => 'Entreprise créée avec succès', 'entreprise' => $entreprise], 201);
    }

    // Affiche les détails d'une entreprise
    public function show($id)
    {
        $entreprise = Entreprise::with('user', 'domaine', 'ville')->find($id);

        if (!$entreprise) {
            return response()->json(['message' => 'Entreprise non trouvée'], 404);
        }

        return response()->json($entreprise, 200);
    }

    // Met à jour une entreprise existante
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
            'RCCM' => 'sometimes|string',
            'id_domaine' => 'sometimes|exists:domaines,id',
            'ville_id' => 'sometimes|exists:villes,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $entreprise->update($request->all());

        return response()->json(['message' => 'Entreprise mise à jour avec succès', 'entreprise' => $entreprise], 200);
    }

    // Supprime une entreprise (optionnel selon ton app)
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
