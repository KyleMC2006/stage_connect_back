<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Models\Offre;
use App\Models\Stage; 
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon; 
use Illuminate\Support\Facades\Log; 

class CandidatureController extends Controller
{
    /**
     * Store a newly created resource in storage.
     * Permet à un étudiant de postuler à une offre.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        
        if ($user->role !== 'etudiant') {
            return response()->json(['message' => 'Seuls les étudiants peuvent postuler à une offre.'], 403);
        }

        
        $etudiant = $user->etudiant;

        if (!$etudiant) {
            return response()->json(['message' => 'Profil étudiant non complet. Veuillez compléter votre profil avant de postuler.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'offre_id' => 'required|exists:offres,id',
            'lettre_motivation' => 'required|string|min:50',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $offre = Offre::find($request->offre_id);

        
        if ($offre->statut !== 'active' || Carbon::now()->greaterThan($offre->date_expiration)) {
            return response()->json(['message' => 'Cette offre n\'est plus active ou a expiré.'], 400);
        }

        // Vérifier si l'étudiant a déjà postulé à cette offre
        $dejaPostuler = Candidature::where('etudiant_id', $etudiant->id)
                                          ->where('offre_id', $offre->id)
                                          ->first();
        if ($dejaPostuler) {
            return response()->json(['message' => 'Vous avez déjà postulé à cette offre.'], 409);
        }

        $candidature = Candidature::create([
            'etudiant_id' => $etudiant->id,
            'offre_id' => $request->offre_id,
            'statut' => 'en_attente', 
            'date_postulat' => Carbon::now(),
            'lettre_motivation' => $request->lettre_motivation,
        ]);

        
        Notification::create([
            'user_id' => $offre->entreprise->user->id,
            'type' => 'nouvelle_candidature',
            'message' => 'Une nouvelle candidature a été soumise pour votre offre : ' . $offre->titre,
            'donnees_sup' => ['candidature_id' => $candidature->id, 'offre_id' => $offre->id]
        ]);

        return response()->json(['message' => 'Candidature soumise avec succès', 'candidature' => $candidature], 201);
    }

    /**
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();
        $query = Candidature::query();

        switch ($user->role) {
            case 'etudiant':
                $query->where('etudiant_id', $user->etudiant->id);
                $query->with('offre.entreprise');
                break;
            case 'entreprise':
                $query->whereHas('offre', function ($q) use ($user) {
                    $q->where('entreprise_id', $user->entreprise->id);
                });
                $query->with('etudiant.user', 'offre');
                break;
            case 'etablissement':
                $query->whereHas('etudiant', function ($q) use ($user) {
                    $q->where('etablissement_id', $user->etablissement->id);
                });
                $query->with('etudiant.user', 'etudiant.etablissement', 'offre.entreprise');
                break;
            default:
                return response()->json(['message' => 'Accès non autorisé pour ce rôle.'], 403);
        }

        $candidatures = $query->get();

        return response()->json($candidatures, 200);
    }

    /**
     * Display the specified resource.
     * Affiche une candidature spécifique, avec autorisation.
     *
     * @param  int  $id L'ID de la candidature.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $candidature = Candidature::with('etudiant.user', 'offre.entreprise')->find($id);

        if (!$candidature) {
            return response()->json(['message' => 'Candidature non trouvée'], 404);
        }

        $user = Auth::user();
        $authorized = false;

        // Vérification d'autorisation
        if ($user->role === 'etudiant' && $candidature->etudiant->user_id === $user->id) {
            $authorized = true;
        } elseif ($user->role === 'entreprise' && $candidature->offre->entreprise->user_id === $user->id) {
            $authorized = true;
        } elseif ($user->role === 'etablissement' && $candidature->etudiant->etablissement->user_id === $user->id) {
            $authorized = true;
        }

        if (!$authorized) {
            return response()->json(['message' => 'Non autorisé à consulter cette candidature.'], 403);
        }

        return response()->json($candidature, 200);
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id L'ID de la candidature.
     * @return \Illuminate\Http\JsonResponse
     */
    public function accepterParEntreprise(Request $request, $id)
    {
        $candidature = Candidature::find($id);

        if (!$candidature) {
            return response()->json(['message' => 'Candidature non trouvée'], 404);
        }

        $user = Auth::user();
        if ($user->role !== 'entreprise' || $candidature->offre->entreprise->user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé à effectuer cette action.'], 403);
        }

        if ($candidature->statut !== 'en_attente') {
            return response()->json(['message' => 'La candidature n\'est pas dans le statut "en_attente_entreprise".'], 400);
        }

        $candidature->update([
            'statut' => 'acceptee',
            'date_acceptation_entreprise' => Carbon::now(),
        ]);

        Notification::create([
            'user_id' => $candidature->etudiant->user->id,
            'type' => 'candidature_acceptee_entreprise',
            'message' => 'Votre candidature pour l\'offre "' . $candidature->offre->titre . '" a été acceptée par l\'entreprise. Vous avez 48h pour confirmer.',
            'donnees_sup' => ['candidature_id' => $candidature->id]
        ]);

        return response()->json(['message' => 'Candidature acceptée par l\'entreprise, en attente de confirmation de l\'étudiant.', 'candidature' => $candidature], 200);
    }

    /**
     * L'entreprise refuse une candidature.
     * Statut de : `en_attente_entreprise` vers `refusee_entreprise`.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id L'ID de la candidature.
     * @return \Illuminate\Http\JsonResponse
     */
    public function refuserParEntreprise(Request $request, $id)
    {
        $candidature = Candidature::find($id);

        if (!$candidature) {
            return response()->json(['message' => 'Candidature non trouvée'], 404);
        }

        $user = Auth::user();
        if ($user->role !== 'entreprise' || $candidature->offre->entreprise->user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé à effectuer cette action.'], 403);
        }

        if ($candidature->statut !== 'en_attente') {
            return response()->json(['message' => 'La candidature n\'est pas dans le statut "en_attente_entreprise".'], 400);
        }

        $candidature->update([
            'statut' => 'refusee',
        ]);

        Notification::create([
            'user_id' => $candidature->etudiant->user->id,
            'type' => 'candidature_refusee_entreprise',
            'message' => 'Votre candidature pour l\'offre "' . $candidature->offre->titre . '" a été refusée par l\'entreprise.',
            'donnees_sup' => ['candidature_id' => $candidature->id]
        ]);

        return response()->json(['message' => 'Candidature refusée par l\'entreprise.', 'candidature' => $candidature], 200);
    }

    /**
     * L'étudiant confirme son choix pour une candidature acceptée par l'entreprise.
     * Statut de : `en_attente_confirmation_etudiant` vers `en_attente_validation_etablissement`.
     * Lance le délai de 48h pour la validation de l'établissement.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id L'ID de la candidature.
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmerParEtudiant(Request $request, $id)
    {
        $candidature = Candidature::find($id);

        if (!$candidature) {
            return response()->json(['message' => 'Candidature non trouvée'], 404);
        }

        $user = Auth::user();
        if ($user->role !== 'etudiant' || $candidature->etudiant->user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé à effectuer cette action.'], 403);
        }

        if ($candidature->statut !== 'acceptee') {
            return response()->json(['message' => 'La candidature n\'est pas dans le statut "acceptee".'], 400);
        }

        
        $Autresconfirmations = Candidature::where('etudiant_id', $candidature->etudiant_id)
                                        ->whereIn('statut', ['confirmee_etudiant', 'en_attente_validation_etablissement', 'validee_etablissement'])
                                        ->where('id', '!=', $candidature->id)
                                        ->exists();
        if ($Autresconfirmations) {
            return response()->json(['message' => 'Vous avez déjà confirmé une autre candidature ou en avez une en attente de validation.'], 400);
        }


        $candidature->update([
            'statut' => 'en_attente_validation_etablissement',
            'date_confirmation_etudiant' => Carbon::now(),
        ]);


        Notification::create([
            'user_id' => $candidature->offre->entreprise->user->id, 
            'type' => 'candidature_confirmee_etudiant',
            'message' => 'L\'étudiant ' . $user->name . ' a confirmé sa candidature pour votre offre "' . $candidature->offre->titre . '".',
            'donnees_sup' => ['candidature_id' => $candidature->id]
        ]);
        Notification::create([
            'user_id' => $candidature->etudiant->etablissement->user->id, // Notifier l'établissement
            'type' => 'candidature_a_valider',
            'message' => 'Une candidature de votre étudiant ' . $user->name . ' est en attente de votre validation.',
            'donnees_sup' => ['candidature_id' => $candidature->id]
        ]);

        return response()->json(['message' => 'Candidature confirmée par l\'étudiant, en attente de validation par l\'établissement.', 'candidature' => $candidature], 200);
    }

    /**
     * L'étudiant se désiste d'une candidature.
     * Statut de : tout statut (sauf terminé ou refusé) vers `desistement_etudiant`.
     * 
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id L'ID de la candidature.
     * @return \Illuminate\Http\JsonResponse
     */
    public function desisterParEtudiant(Request $request, $id)
    {
        $candidature = Candidature::find($id);

        if (!$candidature) {
            return response()->json(['message' => 'Candidature non trouvée'], 404);
        }

        $user = Auth::user();
        if ($user->role !== 'etudiant' || $candidature->etudiant->user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé à effectuer cette action.'], 403);
        }

        
        if (in_array($candidature->statut, ['validee_etablissement', 'refusee', 'desistement_etudiant'])) {
            return response()->json(['message' => 'Cette candidature ne peut plus être désistée manuellement ou est déjà désistée/refusée.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'justificatif_desistement' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

    
        $candidature->update([
            'statut' => 'desistement_etudiant',
            'justificatif_desistement' => $request->justificatif_desistement,
        ]);

        Notification::create([
            'user_id' => $candidature->offre->entreprise->user->id, // Notifier l'entreprise
            'type' => 'candidature_desistement_etudiant',
            'message' => 'L\'étudiant ' . $user->name . ' s\'est désisté de sa candidature pour votre offre "' . $candidature->offre->titre . '".',
            'donnees_sup' => ['candidature_id' => $candidature->id]
        ]);
        Notification::create([
            'user_id' => $candidature->etudiant->etablissement->user->id, // Notifier l'établissement
            'type' => 'candidature_desistement_etudiant',
            'message' => 'Votre étudiant ' . $user->name . ' s\'est désisté d\'une candidature.',
            'donnees_sup' => ['candidature_id' => $candidature->id]
        ]);

        return response()->json(['message' => 'Candidature désistée avec succès. Justificatif enregistré.', 'candidature' => $candidature], 200);
    }

    /**
     * L'établissement valide (ou refuse) le choix d'un étudiant.
     * Statut de : `en_attente_validation_etablissement` vers `validee_etablissement` (ou `'refusee_etablissement'` si refus).
     * Si validation réussie, création du `Stage`.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id L'ID de la candidature.
     * @return \Illuminate\Http\JsonResponse
     */
    public function validerParEtablissement(Request $request, $id)
    {
        $candidature = Candidature::find($id);

        if (!$candidature) {
            return response()->json(['message' => 'Candidature non trouvée'], 404);
        }

        $user = Auth::user();
        // Vérifier que l'utilisateur est un établissement et que c'est bien l'établissement de l'étudiant concerné
        if ($user->role !== 'etablissement' || $candidature->etudiant->etablissement->user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé à effectuer cette action.'], 403);
        }

       

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:valider,refuser', // L'établissement peut valider ou refuser
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->action === 'valider') {

             if ($candidature->statut !== 'en_attente_validation_etablissement') {
                return response()->json(['message' => 'La candidature doit être en statut "en_attente_validation_etablissement" pour être validée.'], 400);
            }
            $candidature->update([
                'statut' => 'validee_etablissement',
                'date_validation_etablissement' => Carbon::now(),
            ]);

            // CRÉATION DU STAGE
            $stage = Stage::create([
                'etudiant_id' => $candidature->etudiant_id,
                'offre_id' => $candidature->offre_id,
                'statut' => 'en_attente',
                'date_debut' => $candidature->offre->date_debut, 
                'date_fin' => Carbon::parse($candidature->offre->date_debut)->addWeeks($candidature->offre->duree_en_semaines),
                
            ]);

            Notification::create([
                'user_id' => $candidature->etudiant->user->id,
                'type' => 'stage_valide_etablissement',
                'message' => 'Votre stage pour l\'offre "' . $candidature->offre->titre . '" a été validé par votre établissement !',
                'donnees_sup' => ['candidature_id' => $candidature->id, 'stage_id' => $stage->id]
            ]);
            Notification::create([
                'user_id' => $candidature->offre->entreprise->user->id,
                'type' => 'candidature_validee_etablissement',
                'message' => 'La candidature de ' . $candidature->etudiant->user->name . ' pour votre offre "' . $candidature->offre->titre . '" a été validée par son établissement. Un stage a été créé.',
                'donnees_sup' => ['candidature_id' => $candidature->id, 'stage_id' => $stage->id]
            ]);

            return response()->json(['message' => 'Candidature validée par l\'établissement. Un stage a été créé.', 'candidature' => $candidature, 'stage' => $stage], 200);

        } elseif ($request->action === 'refuser') {

            if ($candidature->statut !== 'desistement_etudiant') {
                return response()->json(['message' => 'La candidature doit être en statut  "desistement_etudiant" pour être refusée par l\'établissement.'], 400);
            }


            $candidature->update([
                'statut' => 'refusee_etablissement', 
                'date_validation_etablissement' => Carbon::now(),
            ]);

            Notification::create([
                'user_id' => $candidature->etudiant->user->id,
                'type' => 'candidature_refusee_etablissement',
                'message' => 'Votre établissement a refusé de valider votre candidature pour l\'offre "' . $candidature->offre->titre . '". La candidature est annulée.',
                'donnees_sup' => ['candidature_id' => $candidature->id]
            ]);
            Notification::create([
                'user_id' => $candidature->offre->entreprise->user->id,
                'type' => 'candidature_refusee_etablissement',
                'message' => 'L\'établissement de l\'étudiant ' . $candidature->etudiant->user->name . ' a refusé de valider la candidature pour votre offre "' . $candidature->offre->titre . '".',
                'donnees_sup' => ['candidature_id' => $candidature->id]
            ]);

            return response()->json(['message' => 'Candidature refusée par l\'établissement.', 'candidature' => $candidature], 200);
        }
    }


    
    public function destroy($id)
    {
        $candidature = Candidature::find($id);

        if (!$candidature) {
            return response()->json(['message' => 'Candidature non trouvée'], 404);
        }

        $user = Auth::user();
        $authorized = false;

        // Seul l'étudiant peut supprimer sa candidature si elle est encore "en attente entreprise"
        if ($user->role === 'etudiant' && $candidature->etudiant->user_id === $user->id && $candidature->statut === 'en_attente_entreprise') {
            $authorized = true;
        }
        


        if (!$authorized) {
            return response()->json(['message' => 'Non autorisé à supprimer cette candidature ou son statut ne le permet pas.'], 403);
        }

        

        $candidature->delete(); // Considérer l'utilisation de soft deletes ici

        return response()->json(['message' => 'Candidature supprimée avec succès'], 200);
    }
    

    public function getStatistics()
    {
        $user = Auth::user();

        
        if ($user->role !== 'etudiant' || !$user->etudiant) {
            return response()->json(['message' => 'Accès refusé. Seuls les étudiants peuvent obtenir ces statistiques d\'offres.'], 403);
        }

        $etudiantId = $user->etudiant->id;
        $response_data = [];

        // Base de la requête pour les candidatures de l'étudiant
        $baseCandidatureQuery = Candidature::where('etudiant_id', $etudiantId);

        $response_data['offres_par_statut_candidature'] = [
            'total_offres_postulees' => $baseCandidatureQuery->count(),
            'en_attente' => (clone $baseCandidatureQuery)->where('statut', 'en_attente')->count(),
            'acceptees' => (clone $baseCandidatureQuery)->where('statut', 'acceptee')->count(),
            'refusees' => (clone $baseCandidatureQuery)->where('statut', 'refusee')->count(),
         ];

        $response_data['message'] = 'Statistiques des offres basées sur vos candidatures.';

        return response()->json($response_data, 200);
    }
   
}