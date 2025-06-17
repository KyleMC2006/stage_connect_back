<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use App\Models\Annee; // Ajouté pour les opérations de pivot
use App\Models\Etablissement;
use App\Models\FilAnnee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FiliereController extends Controller
{
    /**

     * @return \Illuminate\Http\JsonResponse
     *  @param  \Illuminate\Http\Request  $request
     */
    public function index()
    {
        
        $filieres = Filiere::with('filannee')->get();
        return response()->json($filieres, 200);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'etablissement') {
            return response()->json(['message' => 'Non autorisé. Seuls les établissements peuvent créer des filières.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'libfil' => 'required|string|max:191|unique:filieres,libfil',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $filiere = Filiere::create([
            'libfil' => $request->libfil,
        ]);

        return response()->json(['message' => 'Filière créée avec succès', 'filiere' => $filiere], 201);
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $filiere = Filiere::with('filannee')->find($id);

        if (!$filiere) {
            return response()->json(['message' => 'Filière non trouvée'], 404);
        }

        return response()->json($filiere, 200);
    }



    public function attachAnnees(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user || ($user->role !== 'etablissement')) { 
            return response()->json(['message' => 'Non autorisé. Seuls les établissements  peuvent gérer les années des filières.'], 403);
        }

        $filiere = Filiere::find($id);

        if (!$filiere) {
            return response()->json(['message' => 'Filière non trouvée'], 404);
        }

        $validator = Validator::make($request->all(), [
            'annee_ids' => 'required|array',
            'annee_ids.*' => 'exists:annees,id', 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $filiere->filannee()->syncWithoutDetaching($request->annee_ids);

        $attachedAnnees = $filiere->filannee()->whereIn('id_annee', $request->annee_ids)->get();

        return response()->json([
            'message' => 'Année(s) attachée(s) avec succès à la filière.',
            'annees_attachees' => $attachedAnnees
        ], 200);
    }


    public function detachAnnees(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || ($user->role !== 'etablissement' && $user->role !== 'admin')) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $filiere = Filiere::find($id);

        if (!$filiere) {
            return response()->json(['message' => 'Filière non trouvée'], 404);
        }

        $validator = Validator::make($request->all(), [
            'annee_ids' => 'required|array',
            'annee_ids.*' => 'exists:annees,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $filiere->filannee()->detach($request->annee_ids);

        return response()->json(['message' => 'Année(s) détachée(s) avec succès de la filière.'], 200);
    }


    public function syncAnnees(Request $request, $filiereId)
    {
        $user = Auth::user();

     
        if (!$user || $user->role !== 'etablissement' || !$user->etablissement) {
            return response()->json(['message' => 'Non autorisé. Seuls les établissements authentifiés peuvent synchroniser les années des filières.'], 403);
        }

        $filiere = Filiere::find($filiereId);

        if (!$filiere) {
            return response()->json(['message' => 'Filière non trouvée.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'annee_ids' => 'required|array',
            'annee_ids.*' => 'exists:annees,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $anneeIds = $request->annee_ids;
        $etablissementId = $user->etablissement->id; // Récupère l'ID de l'établissement de l'utilisateur connecté

        $syncData = [];
        foreach ($anneeIds as $anneeId) {
            $syncData[$anneeId] = [
                'etablissement_id' => $etablissementId,
                // Ajoutez d'autres attributs de la table pivot si nécessaire
            ];
        }

        // Synchronise les années. Cela va détacher les années non présentes
        // et attacher/mettre à jour les années présentes avec l'etablissement_id.
        $filiere->filannee()->sync($syncData);

        // Récupérer les années associées avec l'attribut pivot pour la réponse
        $attachedAnnees = $filiere->filannee()->withPivot('etablissement_id')->get();

        return response()->json([
            'message' => 'Années de la filière synchronisées avec succès.',
            'filiere_id' => $filiere->id,
            'annees_synchronisees' => $attachedAnnees
        ], 200);
    }


    public function getByFiliere($filiereId)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'etablissement') {
            return response()->json(['message' => 'Non autorisé. Seuls les établissements peuvent consulter ces associations.'], 403);
        }

        $filiere = Filiere::find($filiereId);

        if (!$filiere) {
            return response()->json(['message' => 'Filière non trouvée.'], 404);
        }

        $filAnnees = FilAnnee::where('filiere_id', $filiereId)
                               ->with('filiere', 'annee')
                               ->get();

        if ($filAnnees->isEmpty()) {
            return response()->json(['message' => 'Aucune année associée à cette filière n\'a été trouvée via FilAnnee.'], 200);
        }

        return response()->json([
            'message' => 'Années associées à la filière récupérées avec succès.',
            'filiere' => $filiere->nom_filiere,
            'data' => $filAnnees
        ], 200);
    }


    public function filterByFiliereAndEtablissement($filiereId, $etablissementId)
    {
        // Autorisation : Permettez à tout utilisateur authentifié (y compris les étudiants) de consulter.
        // Si vous souhaitez rendre cette consultation publique (sans connexion), supprimez cette vérification.
        if (!Auth::check()) {
            return response()->json(['message' => 'Authentification requise pour consulter les offres.'], 401);
        }

        // 1. Vérifiez si la filière existe
        $filiere = Filiere::find($filiereId);
        if (!$filiere) {
            return response()->json(['message' => 'Filière non trouvée.'], 404);
        }

        // 2. Vérifiez si l'établissement existe
        $etablissement = Etablissement::find($etablissementId);
        if (!$etablissement) {
            return response()->json(['message' => 'Établissement non trouvé.'], 404);
        }

        // 3. Récupérez les associations FilAnnee filtrées par filière et établissement
        // Eager load 'filiere', 'annee' et 'etablissement' pour avoir les détails complets
        $filAnnees = FilAnnee::where('id_fil', $filiereId)
                             ->where('etablissement_id', $etablissementId)
                             ->with('filiere', 'annee', 'etablissement')
                             ->get();

        if ($filAnnees->isEmpty()) {
            return response()->json([
                'message' => 'Aucune offre trouvée pour cette filière et cet établissement.',
                'filiere' => $filiere->nom_filiere,
                'etablissement' => $etablissement->nom_etablissement
            ], 200);
        }

        return response()->json([
            'message' => 'Offres de filière-année récupérées avec succès.',
            'filiere' => $filiere->nom_filiere,
            'etablissement' => $etablissement->nom_etablissement,
            'data' => $filAnnees
        ], 200);
    }
}
    
