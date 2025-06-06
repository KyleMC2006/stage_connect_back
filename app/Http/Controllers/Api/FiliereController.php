<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FiliereController extends Controller
{
    // Lister toutes les filières
    public function index()
    {
        return response()->json(Filiere::all());
    }

    // Afficher une filière
    public function show($id)
    {
        $filiere = Filiere::find($id);
        if (!$filiere) {
            return response()->json(['message' => 'Filière non trouvée'], 404);
        }

        return response()->json($filiere);
    }

    // Créer une nouvelle filière
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'etablissement') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $request->validate([
            'libfil' => 'required|string|unique:filieres,libfil',
        ]);

        $filiere = Filiere::create([
            'libfil' => $request->libfil,
        ]);

        return response()->json($filiere, 201);
    }

    // Modifier une filière
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'etablissement') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $filiere = Filiere::find($id);
        if (!$filiere) {
            return response()->json(['message' => 'Filière non trouvée'], 404);
        }

        $request->validate([
            'libfil' => 'required|string|unique:filieres,libfil,' . $id,
        ]);

        $filiere->update([
            'libfil' => $request->libfil,
        ]);

        return response()->json($filiere);
    }

    // Supprimer une filière
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'etablissement') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $filiere = Filiere::find($id);
        if (!$filiere) {
            return response()->json(['message' => 'Filière non trouvée'], 404);
        }

        $filiere->delete();
        return response()->json(['message' => 'Filière supprimée avec succès']);
    }
}