<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnneeController extends Controller
{
    public function index()
    {
        $annees = Annee::all(); // Récupère toutes les années
        return response()->json($annees, 200);
    }
    
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'etablissement') { 
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'libannee' => 'required|string|max:191|unique:annees,libannee',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $annee = Annee::create(['libannee' => $request->libannee]);
        return response()->json(['message' => 'Année créée avec succès', 'annee' => $annee], 201);
    }
}
