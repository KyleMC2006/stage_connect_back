<?php

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TuteurStage;
use App\Models\Entreprise; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TuteurStageController extends Controller
{
    /**
     
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est bien une entreprise
        if ($user->role !== 'entreprise') {
            return response()->json(['message' => 'Non autorisé. Seules les entreprises peuvent lister leurs tuteurs de stage.'], 403);
        }

        $entreprise = $user->entreprise;

        if (!$entreprise) {
            return response()->json(['message' => 'Profil entreprise non complet.'], 400);
        }

        $tuteurs = TuteurStage::where('entreprise_id', $entreprise->id)
                              ->get(); 

        return response()->json($tuteurs, 200);
    }

    /**
     * Store a newly created resource in storage.
     * Permet à une entreprise de créer un nouveau tuteur de stage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'entreprise') {
            return response()->json(['message' => 'Seules les entreprises peuvent créer des tuteurs de stage.'], 403);
        }

        $entreprise = $user->entreprise;

        if (!$entreprise) {
            return response()->json(['message' => 'Profil entreprise non complet. Veuillez compléter votre profil pour ajouter des tuteurs de stage.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'nom_tuteur' => 'required|string|max:191',
            'contact' => 'required|string|max:191', 
            'poste' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $tuteur = TuteurStage::create([
            'entreprise_id' => $entreprise->id,
            'nom_tuteur' => $request->nom_tuteur,
            'contact' => $request->contact,
            'poste' => $request->poste,
        ]);

        return response()->json(['message' => 'Tuteur de stage créé avec succès', 'tuteur' => $tuteur], 201);
    }

    /**
     * @param  int  $id L'ID du tuteur de stage.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $tuteur = TuteurStage::find($id);

        if (!$tuteur) {
            return response()->json(['message' => 'Tuteur de stage non trouvé'], 404);
        }

        $user = Auth::user();
        if ($user->role !== 'entreprise' || $tuteur->entreprise_id !== $user->entreprise->id) {
            return response()->json(['message' => 'Non autorisé. Vous n\'êtes pas autorisé à accéder à ce tuteur de stage.'], 403);
        }

        return response()->json($tuteur, 200);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id L'ID du tuteur de stage à mettre à jour.
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $tuteur = TuteurStage::find($id);

        if (!$tuteur) {
            return response()->json(['message' => 'Tuteur de stage non trouvé'], 404);
        }

        $user = Auth::user();
        
        if ($user->role !== 'entreprise' || $tuteur->entreprise_id !== $user->entreprise->id) {
            return response()->json(['message' => 'Non autorisé. Vous n\'êtes pas le propriétaire de ce tuteur de stage.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nom_tuteur' => 'sometimes|required|string|max:191',
            'contact' => 'sometimes|required|string|max:191',
            'poste' => 'sometimes|required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $tuteur->update($request->all());

        return response()->json(['message' => 'Tuteur de stage mis à jour avec succès', 'tuteur' => $tuteur], 200);
    }

    /**
     *
     * @param  int  $id L'ID du tuteur de stage à supprimer.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $tuteur = TuteurStage::find($id);

        if (!$tuteur) {
            return response()->json(['message' => 'Tuteur de stage non trouvé'], 404);
        }

        $user = Auth::user();
       
        if ($user->role !== 'entreprise' || $tuteur->entreprise_id !== $user->entreprise->id) {
            return response()->json(['message' => 'Non autorisé. Vous n\'êtes pas le propriétaire de ce tuteur de stage.'], 403);
        }

        $tuteur->delete();

        return response()->json(['message' => 'Tuteur de stage supprimé avec succès'], 200);
    }
}