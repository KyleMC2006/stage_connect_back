<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; // Importez la façade Storage

use App\Models\Etudiant;
use App\Models\Etablissement;
use App\Models\Entreprise;
use App\Models\Filiere;
use App\Models\Ville;
use App\Models\Domaine; 

class UserController extends Controller
{
    // ... (méthode setRole inchangée)

    /**
     * Complète le profil spécifique au rôle de l'utilisateur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeProfile(Request $request, string $role)
    {
        $user = Auth::user();

        if ($user->role !== $role) {
            return response()->json(['message' => 'Le rôle spécifié ne correspond pas au rôle de l\'utilisateur authentifié.'], 403);
        }

        $profileExists = false;
        switch ($role) {
            case 'etudiant':
                $profileExists = !is_null($user->etudiant);
                break;
            case 'etablissement':
                $profileExists = !is_null($user->etablissement);
                break;
            case 'entreprise':
                $profileExists = !is_null($user->entreprise);
                break;
        }

        if ($profileExists) {
            return response()->json(['message' => 'Ce profil est déjà complet.'], 409);
        }

        $rules = [];
        $profileData = ['user_id' => $user->id];

        switch ($role) {
            case 'etudiant':
                $rules = [
                    'matricule' => ['required', 'string', 'max:191', Rule::unique('etudiants', 'matricule')],
                    'projets' => ['nullable', 'string'],
                    'competences' => ['nullable', 'string'],
                    // MODIFICATION ICI POUR L'UPLOAD DE FICHIER
                    'CV' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:2048'], // max: 2048 KB = 2MB
                    'parcours' => ['nullable', 'string'],
                    'id_filiere' => ['required', 'exists:filieres,id'],
                    'id_etablissement' => ['required', 'exists:etablissements,id'],
                ];
                // Ne pas inclure 'CV' directement ici car ce n'est plus une simple chaîne
                $profileData = array_merge($profileData, $request->only([
                    'matricule', 'projets', 'competences', 'parcours', 'id_filiere', 'id_etablissement'
                ]));
                break;

            case 'etablissement':
                $rules = [
                    'nom_etablissement' => ['required', 'string', 'max:191'],
                    'siteweb' => ['nullable', 'url', 'max:191'],
                    'adresse' => ['required', 'string', 'max:191'],
                    'ville_id' => ['required', 'exists:villes,id'],
                    'numero_agrement' => ['required', 'string', 'max:191', Rule::unique('etablissements', 'numero_agrement')],
                ];
                $profileData = array_merge($profileData, $request->only([
                    'nom_etablissement', 'siteweb', 'adresse', 'ville_id', 'numero_agrement'
                ]));
                break;

            case 'entreprise':
                $rules = [
                    'nom_entreprise' => ['required', 'string', 'max:191'],
                    'email_entreprise' => ['required', 'email', 'max:191', Rule::unique('entreprises', 'email_entreprise')],
                    'siteweb' => ['nullable', 'url', 'max:191'],
                    'adresse' => ['required', 'string', 'max:191'],
                    'id_domaine' => ['required', 'exists:domaines,id'],
                    'RCCM' => ['required', 'string', 'max:191', Rule::unique('entreprises', 'RCCM')],
                    'ville_id' => ['required', 'exists:villes,id'],
                ];
                $profileData = array_merge($profileData, $request->only([
                    'nom_entreprise', 'email_entreprise', 'siteweb', 'adresse', 'id_domaine', 'RCCM', 'ville_id'
                ]));
                break;

            default:
                return response()->json(['message' => 'Rôle non valide pour la complétion de profil.'], 400);
        }

        $request->validate($rules);

        // LOGIQUE D'UPLOAD DU CV (pour étudiant seulement)
        if ($role === 'etudiant' && $request->hasFile('CV')) {
            $file = $request->file('CV');
            // Génère un nom de fichier unique pour éviter les conflits
            $fileName = $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            // Définit le chemin de stockage, par exemple 'cvs/user_id/nom_fichier.pdf'
            $path = $file->storeAs('cvs/' . $user->id, $fileName, 'public'); // Stocke dans storage/app/public/cvs/{user_id}/

            // Stocke le chemin d'accès public du fichier dans la base de données
            $profileData['CV'] = Storage::url($path); // Génère une URL publique (ex: /storage/cvs/1/mon_cv.pdf)
        }

        switch ($role) {
            case 'etudiant':
                Etudiant::create($profileData);
                break;
            case 'etablissement':
                Etablissement::create($profileData);
                break;
            case 'entreprise':
                Entreprise::create($profileData);
                break;
        }

        return response()->json([
            'message' => 'Profil complété avec succès.',
            'redirect_url' => env('FRONTEND_URL') . '/profil/' . $user->role,
            'user' => $user->load($user->role)
        ]);
    }

    // ... (méthode getProfile inchangée)
}