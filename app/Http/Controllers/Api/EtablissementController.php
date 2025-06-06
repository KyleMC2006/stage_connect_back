<?php

namespace App\Http\Controllers\API;

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
        $etablissements = Etablissement::with('user', 'ville', 'ecolefil')->get();
        return response()->json($etablissements, 200);
    }

    // POST /api/etablissements
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom_etablissement' => 'required|string|max:191',
            'siteweb' => 'nullable|url',
            'adresse' => 'required|string|max:191',
            'ville_id' => 'required|exists:villes,id',
            'numero_agrement' => 'required|string|unique:etablissements',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        

        $etablissement = Etablissement::create([
            'user_id' => Auth::id(),
            'nom_etablissement' => $request->nom_etablissement,
            'siteweb' => $request->siteweb,
            'adresse' => $request->adresse,
            'ville_id' => $request->ville_id,
            'numero_agrement' => $request->numero_agrement
        ]);

        return response()->json(['message' => 'Etablissement créé avec succès', 'etablissement' => $etablissement], 201);
    }

    // GET /api/etablissements/{id}
    public function show($id)
    {
        $etablissement = Etablissement::with('user', 'ville', 'ecolefil')->find($id);

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
}
