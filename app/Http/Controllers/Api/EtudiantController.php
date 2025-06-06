<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EtudiantController extends Controller
{
    // Liste tous les étudiants
    public function index()
    {
        $etudiants = Etudiant::with(['user', 'filiere', 'etablissement'])->get();
        return response()->json($etudiants);
    }

    // Affiche un étudiant
    public function show($id)
    {
        $etudiant = Etudiant::with(['user', 'filiere', 'etablissement'])->find($id);
        if (!$etudiant) {
            return response()->json(['message' => 'Étudiant non trouvé'], 404);
        }
        return response()->json($etudiant);
    }

    // Crée un profil étudiant (après login Firebase)
    public function store(Request $request)
    {
        $request->validate([
            'matricule' => 'required|string|unique:etudiants',
            'projets' => 'nullable|text',
            'competences' => 'nullable|text',
            'CV' => 'nullable|file',
            'parcours' => 'nullable|text',
            'id_filiere' => 'required|exists:filieres,id',
            'id_etablissement' => 'required|exists:etablissements,id',
        ]);

        $user = Auth::user();

        if (!$user || $user->role !== 'etudiant') {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // Upload CV 
        $cvPath = null;
        if ($request->hasFile('CV')) {
            $cvPath = $request->file('CV')->store('cvs', 'public');
        }

        $etudiant = Etudiant::create([
            'user_id' => $user->id,
            'matricule' => $request->matricule,
            'projets' => $request->projets,
            'competences' => $request->competences,
            'CV' => $cvPath,
            'parcours' => $request->parcours,
            'id_filiere' => $request->id_filiere,
            'id_etablissement' => $request->id_etablissement,
        ]);

        return response()->json($etudiant, 201);
    }

    // Modifier un profil étudiant
    public function update(Request $request, $id)
{
    $etudiant = Etudiant::find($id);
    if (!$etudiant) {
        return response()->json(['message' => 'Étudiant non trouvé'], 404);
    }

    $user = Auth::user();
    if (!$user || $user->id !== $etudiant->user_id) {
        return response()->json(['message' => 'Accès interdit'], 403);
    }

    $request->validate([
        'matricule' => 'sometimes|string|unique:etudiants,matricule,' . $id,
        'projets' => 'nullable|string',
        'competences' => 'nullable|string',
        'CV' => 'nullable|file',
        'parcours' => 'nullable|string',
        'id_filiere' => 'sometimes|exists:filieres,id',
        'id_etablissement' => 'sometimes|exists:etablissements,id',
    ]);

    if ($request->hasFile('CV')) {
        
        if ($etudiant->CV && \Storage::disk('public')->exists($etudiant->CV)) {
            \Storage::disk('public')->delete($etudiant->CV);
        }
        $cvPath = $request->file('CV')->store('cvs', 'public');
        $etudiant->CV = $cvPath;
    }

    $data = $request->except('CV');
    $data['CV'] = $etudiant->CV;

    $etudiant->update($data);

    return response()->json($etudiant);
}

    // Supprimer un étudiant
    public function destroy($id)
    {
        $etudiant = Etudiant::find($id);
        if (!$etudiant) {
            return response()->json(['message' => 'Étudiant non trouvé'], 404);
        }

        $user = Auth::user();
        if (!$user || $user->id !== $etudiant->user_id) {
            return response()->json(['message' => 'Accès interdit'], 403);
        }

        $etudiant->delete();
        return response()->json(['message' => 'Étudiant supprimé avec succès.']);
    }
}
