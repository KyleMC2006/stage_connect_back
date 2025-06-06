<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Models\Etudiant;
use App\Models\Offre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidatureController extends Controller
{
    // Liste toutes les candidatures de l'utilisateur connecté (étudiant)
    public function index()
    {
        $user = Auth::user();
        $etudiant = $user->etudiant;

        if (!$etudiant) {
            return response()->json(['message' => 'Accès réservé aux étudiants'], 403);
        }

        $candidatures = Candidature::with(['offre', 'offre.entreprise'])
            ->where('etudiant_id', $etudiant->id)
            ->get();

        return response()->json($candidatures);
    }

    // Affiche une candidature spécifique
    public function show($id)
    {
        $candidature = Candidature::with(['etudiant.user', 'offre.entreprise'])->find($id);

        if (!$candidature) {
            return response()->json(['message' => 'Candidature non trouvée'], 404);
        }

        return response()->json($candidature);
    }

    // Création d'une candidature (postuler à une offre)
    public function store(Request $request)
    {
        $request->validate([
            'offre_id' => 'required|exists:offres,id',
            'lettre_motivation' => 'nullable|string',
        ]);

        $user = Auth::user();
        $etudiant = $user->etudiant;

        if (!$etudiant) {
            return response()->json(['message' => 'Accès réservé aux étudiants'], 403);
        }

        
        $dejaPostule = Candidature::where('etudiant_id', $etudiant->id)
            ->where('offre_id', $request->offre_id)
            ->first();

        if ($dejaPostule) {
            return response()->json(['message' => 'Vous avez déjà postulé à cette offre'], 409);
        }

        $candidature = Candidature::create([
            'etudiant_id' => $etudiant->id,
            'offre_id' => $request->offre_id,
            'statut' => 'en attente',
            'date_postulat' => now(),
            'lettre_motivation' => $request->lettre_motivation,
        ]);

        return response()->json($candidature, 201);
    }

    // Mise à jour d'une candidature 
    public function update(Request $request, $id)
    {
        $candidature = Candidature::find($id);

        if (!$candidature) {
            return response()->json(['message' => 'Candidature non trouvée'], 404);
        }

        $user = Auth::user();
        if ($candidature->etudiant->user_id !== $user->id) {
            return response()->json(['message' => 'Accès interdit'], 403);
        }

        $request->validate([
            'lettre_motivation' => 'nullable|string',
        ]);

        $candidature->update([
            'lettre_motivation' => $request->lettre_motivation,
        ]);

        return response()->json($candidature);
    }

    // Suppression d'une candidature
    public function destroy($id)
    {
        $candidature = Candidature::find($id);

        if (!$candidature) {
            return response()->json(['message' => 'Candidature non trouvée'], 404);
        }

        $user = Auth::user();
        if ($candidature->etudiant->user_id !== $user->id) {
            return response()->json(['message' => 'Accès interdit'], 403);
        }

        $candidature->delete();
        return response()->json(['message' => 'Candidature supprimée avec succès']);
    }
}

