<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Partenariat;
use App\Models\Entreprise;
use App\Models\Etablissement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PartenariatController extends Controller
{
   

    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'entreprise' && $user->entreprise) {
            $partenariats = Partenariat::where('entreprise_id', $user->entreprise->id)
                                        ->with(['etablissement.user']) // Charge l'établissement et son utilisateur
                                        ->get();
        } elseif ($user->role === 'etablissement' && $user->etablissement) {
            $partenariats = Partenariat::where('etablissement_id', $user->etablissement->id)
                                        ->with(['entreprise.user']) // Charge l'entreprise et son utilisateur
                                        ->get();
        } else {
            return response()->json(['message' => 'Profil utilisateur non trouvé ou rôle non autorisé'], 403);
        }

        return response()->json($partenariats, 200);
    }


    public function store(Request $request)
    {
        $user = Auth::user();

   
        if ($user->role !== 'etablissement' || !$user->etablissement) {
             return response()->json(['message' => 'Seuls les établissements peuvent demander un partenariat'], 403);
        }

        $validator = Validator::make($request->all(), [
            'entreprise_id' => 'required|exists:entreprises,id', // ID de l'entreprise ciblée
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut', // Correction: après ou égale

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Vérifier si un partenariat  existe déjà
        $dejaPartenariat = Partenariat::where('entreprise_id', $request->entreprise_id)
                                            ->where('etablissement_id', $user->etablissement->id)
                                            ->whereIn('statut', ['en_attente', 'actif'])
                                            ->first();

        if ($dejaPartenariat) {
            return response()->json(['message' => 'Une demande de partenariat similaire est déjà en cours ou active.'], 409); // Conflict
        }

        $partenariat = Partenariat::create([
            'entreprise_id' => $request->entreprise_id,
            'etablissement_id' => $user->etablissement->id, 
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'statut' => 'en_attente', 
           
        ]);

        //  Envoyer une notification à l'entreprise ciblée
        
        Notification::create([
            'user_id' => $partenariat->entreprise->user_id,
            'type' => 'nouvelle_demande_partenariat',
            'message' => 'Vous avez une nouvelle demande de partenariat de ' . $user->etablissement->nom_etablissement,
            'donnees_sup' => ['partenariat_id' => $partenariat->id]
        ]);

        $partenariat->load(['entreprise.user', 'etablissement.user']); // Charge les relations pour la réponse
        return response()->json(['message' => 'Demande de partenariat envoyée avec succès', 'data' => $partenariat], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Partenariat $partenariat)
    {
        $user = Auth::user();

        // Seules les entreprises et les établissements impliqués dans ce partenariat peuvent le voir
        if (($user->role === 'entreprise' && $user->entreprise->id !== $partenariat->entreprise_id) ||
            ($user->role === 'etablissement' && $user->etablissement->id !== $partenariat->etablissement_id)) {
            return response()->json(['message' => 'Non autorisé à voir ce partenariat.'], 403);
        }

        $partenariat->load(['entreprise.user', 'etablissement.user']); 
        return response()->json($partenariat, 200);
    }

    /**
     * Update the specified resource in storage.
     * Permet à une Entreprise de modifier le statut d'un partenariat (accepter/refuser).
     */
    public function update(Request $request, Partenariat $partenariat)
    {
        $user = Auth::user();

       
        if ($user->role !== 'entreprise' || !$user->entreprise) {
            return response()->json(['message' => 'Seules les entreprises peuvent accepter ou refuser un partenariat.'], 403);
        }

        // L'entreprise authentifiée doit être celle impliquée dans le partenariat
        if ($user->entreprise->id !== $partenariat->entreprise_id) {
            return response()->json(['message' => 'Non autorisé à modifier ce partenariat.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'statut' => 'required|in:en_attente,actif,termine,suspendu',
            'date_debut' => 'sometimes|required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut', // Correction: après ou égale
            'type_partenariat' => 'sometimes|required|in:Stages,Recrutement,R&D,Mécénat',
            
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $partenariat->update($request->all());

        // Envoyer une notification à l'établissement si le statut change
        if ($request->has('statut') && $request->statut !== $partenariat->getOriginal('statut')) {
            Notification::create([
                'user_id' => $partenariat->etablissement->user_id,
                'type' => 'statut_partenariat_mis_a_jour',
                'message' => 'Le statut de votre partenariat avec ' . $user->entreprise->nom_entreprise . ' est maintenant ' . $request->statut,
                'donnees_sup' => ['partenariat_id' => $partenariat->id, 'nouveau_statut' => $request->statut]
            ]);
        }

        $partenariat->load(['entreprise.user', 'etablissement.user']); // Charger les relations pour la réponse
        return response()->json(['message' => 'Partenariat mis à jour avec succès', 'data' => $partenariat], 200);
    }


    public function destroy(Partenariat $partenariat)
    {
        $user = Auth::user();

        
        if (($user->role === 'entreprise' && $user->entreprise->id !== $partenariat->entreprise_id) ||
            ($user->role === 'etablissement' && $user->etablissement->id !== $partenariat->etablissement_id)) {
            return response()->json(['message' => 'Non autorisé à supprimer ce partenariat.'], 403);
        }

        $partenariat->delete();

        return response()->json(['message' => 'Partenariat supprimé avec succès'], 204);
    }
}