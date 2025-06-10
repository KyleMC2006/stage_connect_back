<?php

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use App\Models\Annee; // Ajouté pour les opérations de pivot
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FiliereController extends Controller
{
    /**

     * @return \Illuminate\Http\JsonResponse
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


    public function syncAnnees(Request $request, $id)
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

        $filiere->filannee()->sync($request->annee_ids);

        return response()->json(['message' => 'Années de la filière synchronisées avec succès.'], 200);
    }
}