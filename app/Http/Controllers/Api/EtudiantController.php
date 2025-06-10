<?php

namespace App\Http\Controllers\Api;

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
        $etudiants = Etudiant::paginate(15);
        return response()->json($etudiants);
    }

    // Affiche un étudiant
    public function show($id)
    {
        $etudiant = Etudiant::find($id);
        if (!$etudiant) {
            return response()->json(['message' => 'Étudiant non trouvé'], 404);
        }
        return response()->json($etudiant);
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
        'projets' => 'nullable|string',
        'competences' => 'nullable|string',
        'CV' => 'nullable|file|mimes:pdf|max:2048',
        'parcours' => 'nullable|string',
        'id_filiere' => 'sometimes|exists:filieres,id',
        'id_etablissement' => 'sometimes|exists:etablissements,id',
        'filannee_id' => 'sometimes|exists:filannee,id',
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
