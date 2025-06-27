<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator; 
use App\Models\User;
use App\Models\Etudiant;
use App\Models\Etablissement;
use App\Models\Entreprise;
use App\Models\Filiere;
use App\Models\Ville;
use App\Models\Domaine;
use App\Models\FilAnnee; 

class UserController extends Controller
{

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
        $profileData = [];

        switch ($role) {
            case 'etudiant':
                
                Log::info('Après Auth::login(): Auth::check() = ' . (Auth::check() ? 'true' : 'false'));
                if (Auth::check()) {
                    Log::info('Après Auth::login(): Utilisateur authentifié (ID: ' . Auth::id() . ', Email: ' . Auth::user()->email . ')');
                } else {
                    Log::warning('Après Auth::login(): Utilisateur non authentifié malgré l\'appel à Auth::login().');
                }
                Log::info('Objet $user passé à Auth::login(): ', ['type' => get_class($user), 'id' => $user->id ?? 'null']);
                // --- FIN DES LOGS ---

                $validator = Validator::make($request->all(), [
                    'matricule' => ['required', 'string', 'max:255', Rule::unique('etudiants')->ignore($user->etudiant)],
                    'projets' => 'nullable|string',
                    'competences' => 'nullable|string',
                    'CV' => 'nullable|file|mimes:pdf|max:2048',
                    'parcours' => 'nullable|string',
                    'id_filiere' => 'required|exists:filieres,id',
                    'id_etablissement' => 'required|exists:etablissements,id',
                    'filannee_id' => 'required|exists:filannee,id', 
                ]);

                if ($validator->fails()) {
                    return response()->json($validator->errors(), 422);
                }
                $profileData = $validator->validated();

                // --- DÉBUT DE LA LOGIQUE D'UPLOAD DU CV ---
                if ($request->hasFile('CV')) {
                    $file = $request->file('CV');
                    
                    $fileName = $user->id . '_cv_' . time() . '.' . $file->getClientOriginalExtension();

                    
                    $path = $file->storeAs('cvs/' . $user->id, $fileName, 'public');

                    
                    $profileData['CV'] = Storage::url($path);

                    // Si l'étudiant a déjà un CV et que c'est une mise à jour, supprimez l'ancien fichier
                    if ($user->etudiant && $user->etudiant->CV && Storage::disk('public')->exists(str_replace('/storage/', '', $user->etudiant->CV))) {
                        Storage::disk('public')->delete(str_replace('/storage/', '', $user->etudiant->CV));
                    }
                } else {
                    // Si pas de nouveau CV est uploadé, et c'est une mise à jour, conserver l'ancien CV
                    if ($user->etudiant) {
                        $profileData['CV'] = $user->etudiant->CV;
                    } else {
                        // Pour une nouvelle création et pas de CV fourni, s'assurer que CV est null ou vide
                        $profileData['CV'] = null;
                    }
                }

                if ($user->etudiant) {
                    $user->etudiant->update($profileData);
                } else {
                    $user->etudiant()->create($profileData);
                }
                $profileExists = true;
                break;

            case 'etablissement':
                $validator = Validator::make($request->all(), [
                    'nom_etablissement' => ['required', 'string', 'max:191', Rule::unique('etablissements')->ignore($user->etablissement)],
                    'siteweb' => 'nullable|url',
                    'adresse' => 'required|string|max:191',
                    'ville_id' => 'required|exists:villes,id',
                    'numero_agrement' => ['required', 'string', 'max:191', Rule::unique('etablissements')->ignore($user->etablissement)],
                ]);

                if ($validator->fails()) {
                    return response()->json($validator->errors(), 422);
                }
                $profileData = $validator->validated();

                if ($user->etablissement) {
                    $user->etablissement->update($profileData);
                } else {
                    $user->etablissement()->create($profileData);
                }
                $profileExists = true;
                break;

            case 'entreprise':
                // Utilisez Validator::make comme ci-dessus
                $validator = Validator::make($request->all(), [
                    'nom_entreprise' => ['required', 'string', 'max:191', Rule::unique('entreprises')->ignore($user->entreprise)],
                    'email_entreprise' => ['required', 'string', 'email', 'max:191', Rule::unique('entreprises')->ignore($user->entreprise)],
                    'siteweb' => 'nullable|url',
                    'adresse' => 'required|string|max:191',
                    'id_domaine' => 'required|exists:domaines,id',
                    'RCCM' => ['required', 'string', 'max:191', Rule::unique('entreprises')->ignore($user->entreprise)],
                    'ville_id' => 'required|exists:villes,id',
                ]);

                if ($validator->fails()) {
                    return response()->json($validator->errors(), 422);
                }
                $profileData = $validator->validated();

                if ($user->entreprise) {
                    $user->entreprise->update($profileData);
                } else {
                    $user->entreprise()->create($profileData);
                }
                $profileExists = true;
                break;

            

            default:
                return response()->json(['message' => 'Rôle non valide.'], 400);
        }


        $user->load([
            'etudiant.etablissement',
            'etudiant.filiere',
            'etudiant.filannee.annee', 
            'etablissement.ville', 
            'entreprise.domaine', 
            'entreprise.ville' 
        ]);

        $profileResponseData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'photo' => $user->photo,
            'is_active' => $user->is_active,
            'firebase_uid' => $user->firebase_uid,
            'description' => $user->description,
            'couverture' => $user->couverture,
        ];

        // Ajouter les détails spécifiques au rôle
        if ($user->role === 'etudiant' && $user->etudiant) {
           
            $profileResponseData['name'] = $user->etudiant->nom_etudiant ?? $user->name; 

            $profileResponseData['role_details'] = [
                'matricule' => $user->etudiant->matricule,
                'projets' => $user->etudiant->projets,
                'competences' => $user->etudiant->competences,
                'CV' => $user->etudiant->CV ? Storage::url($user->etudiant->CV) : null,
                'parcours' => $user->etudiant->parcours,
                'id_filiere' => $user->etudiant->id_filiere,
                'id_etablissement' => $user->etudiant->id_etablissement,
                'filannee_id' => $user->etudiant->filannee_id, 
            ];
            
            if ($user->etudiant->filiere) {
                $profileResponseData['role_details']['filiere'] = $user->etudiant->filiere->toArray();
            }
            if ($user->etudiant->filannee && $user->etudiant->filannee->annee) {
                // Accéder au libellé de l'année via la relation filannee et annee
                $profileResponseData['role_details']['annee_libelle'] = $user->etudiant->filannee->annee->libannee; // <--- MODIFICATION ICI : AJOUT DU LIBELLÉ
            }


        } elseif ($user->role === 'etablissement' && $user->etablissement) {
            $profileResponseData['name'] = $user->etablissement->nom_etablissement;
            $profileResponseData['role_details'] = [
                'siteweb' => $user->etablissement->siteweb,
                'adresse' => $user->etablissement->adresse,
                'ville_id' => $user->etablissement->ville_id,
                'numero_agrement' => $user->etablissement->numero_agrement,
                'ville' => $user->etablissement->ville ? $user->etablissement->ville->toArray() : null, // Inclure la ville
            ];

        } elseif ($user->role === 'entreprise' && $user->entreprise) {
            $profileResponseData['name'] = $user->entreprise->nom_entreprise;
            $profileResponseData['role_details'] = [
                'email_entreprise' => $user->entreprise->email_entreprise,
                'siteweb' => $user->entreprise->siteweb,
                'adresse' => $user->entreprise->adresse,
                'RCCM' => $user->entreprise->RCCM,
                'ville_id' => $user->entreprise->ville_id,
                'id_domaine' => $user->entreprise->id_domaine,
                'domaine' => $user->entreprise->domaine ? $user->entreprise->domaine->toArray() : null, // Inclure le domaine
                'ville' => $user->entreprise->ville ? $user->entreprise->ville->toArray() : null, // Inclure la ville
            ];
        } elseif ($user->role === 'admin') {
            // Pas de détails de rôle spécifiques pour l'admin si non nécessaires
        } else {
            // Cette partie ne devrait normalement pas être atteinte si profileExists est géré correctement
            return response()->json(['message' => 'Profil non trouvé ou rôle non pris en charge.'], 404);
        }

        return response()->json($profileResponseData, 200);
    }


    public function setRole(Request $request)
    {
        $user = Auth::user();
        if (!is_null($user->role)) {
            return response()->json(['message' => 'Votre rôle est déjà défini. Vous ne pouvez pas le modifier via cette fonction.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'role' => 'required|in:etudiant,entreprise,etablissement',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $selectedRole = $request->input('role');

        // Mettre à jour le rôle de l'utilisateur
        $user->update(['role' => $selectedRole]);

        // Charger la relation correspondante pour s'assurer que l'objet est bien défini après l'assignation du rôle
        // Cela permet de ne pas avoir à vérifier $user->etudiant dans la réponse JSON directement.
        $user->load($selectedRole);

        return response()->json([
            'message' => 'Rôle ' . $selectedRole . ' assigné avec succès. Veuillez compléter votre profil.',
            'user' => $user, // Retourne l'objet user avec son nouveau rôle et la relation chargée
            'redirect_url' => env('FRONTEND_URL') . '/complete-profile/' . $selectedRole // Redirection côté frontend

        ], 200);
    }

    
    public function updatePhotos(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($user->photo) {
                Storage::delete($user->photo);
            }

            // Enregistrer la nouvelle photo
            $path = $request->file('photo')->store('profile_photos', 'public');
            $user->photo = $path;
            $user->save();

            return response()->json(['message' => 'Photo de profil mise à jour avec succès.', 'photo_url' => Storage::url($path)]);
        }

        return response()->json(['message' => 'Aucune photo fournie.'], 400);
    }

    public function updateCouverture(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'couverture' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->hasFile('couverture')) {
            // Supprimer l'ancienne image de couverture si elle existe
            if ($user->couverture) {
                Storage::delete($user->couverture);
            }

            // Enregistrer la nouvelle image de couverture
            $path = $request->file('couverture')->store('couverture', 'public');
            $user->couverture = $path;
            $user->save();

            return response()->json(['message' => 'Image de couverture mise à jour avec succès.', 'couverture_url' => Storage::url($path)]);
        }

        return response()->json(['message' => 'Aucune image fournie.'], 400);
    }

    public function updateDescription(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user->description = $request->input('description');
        $user->save();

        return response()->json(['message' => 'Description mise à jour avec succès.', 'description' => $user->description]);
    }

    // Méthode pour récupérer le profil d'un utilisateur
    public function getUserProfile()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        // Log pour véruier l"authentifictaion
    \Log::info('APP_URL from config: ' . config('app.url'));
    \Log::info('Default Filesystem Disk Root: ' . Storage::disk('public')->path(''));
    
        // Charger les relations basées sur le rôle de l'utilisateur
        switch ($user->role) {
            case 'etudiant':
                $user->load('etudiant.etablissement', 'etudiant.filiere', 'etudiant.filannee.annee'); // <--- MISE À JOUR ICI POUR L'ÉTUDIANT
                break;
            case 'etablissement':
                $user->load('etablissement.ville');
                break;
            case 'entreprise':
                $user->load('entreprise.domaine', 'entreprise.ville');
                break;
            default:
                // Gérer les rôles non définis si nécessaire
                return response()->json(['message' => 'Rôle utilisateur non reconnu.'], 400);
        }
         $defaultPhotoUrl = asset('profile_photos/default_user_photo.png');

        $profileData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'photo' => $user->photo ? Storage::url($user->photo) : $defaultPhotoUrl , // URL de la photo
            'is_active' => $user->is_active,
            'firebase_uid' => $user->firebase_uid,
            'description' => $user->description,
            'couverture' => $user->couverture ? Storage::url($user->couverture) : null, // URL de la couverture
        ];

        // Ajouter les détails spécifiques au rôle
        if ($user->role === 'etudiant' && $user->etudiant) {
            $profileData['name'] = $user->etudiant->nom_etudiant ?? $user->name;
            $profileData['role_details'] = [
                'etudiant_id' => $user->etudiant->id,
                'matricule' => $user->etudiant->matricule,
                'projets' => $user->etudiant->projets,
                'competences' => $user->etudiant->competences,
                'CV' => $user->etudiant->CV ? Storage::url($user->etudiant->CV) : null,
                'parcours' => $user->etudiant->parcours,
                'id_filiere' => $user->etudiant->id_filiere,
                'id_etablissement' => $user->etudiant->id_etablissement,
                'filannee_id' => $user->etudiant->filannee_id, // <--- AJOUTÉ ICI
            ];
            if ($user->etudiant->etablissement) {
                $profileData['role_details']['etablissement'] = $user->etudiant->etablissement->toArray();
            }
            if ($user->etudiant->filiere) {
                $profileData['role_details']['filiere'] = $user->etudiant->filiere->toArray();
            }
            if ($user->etudiant->filannee && $user->etudiant->filannee->annee) {
                $profileData['role_details']['annee_libelle'] = $user->etudiant->filannee->annee->libannee; // <--- AJOUTÉ ICI
                $profileData['role_details']['filiere_annee_complete'] = $user->etudiant->filiere->libfil . ' - ' . $user->etudiant->filannee->annee->libannee;
            }else {
        // Optionally, set them to null or empty string if not available
        $profileData['role_details']['annee_libelle'] = null;
        $profileData['role_details']['filiere_annee_complete'] = null;
    }

        } elseif ($user->role === 'etablissement' && $user->etablissement) {
            $profileData['name'] = $user->etablissement->nom_etablissement;
            $profileData['role_details'] = [
                'etablissement_id' => $user->etablissement->id,
                'siteweb' => $user->etablissement->siteweb,
                'adresse' => $user->etablissement->adresse,
                'ville_id' => $user->etablissement->ville_id,
                'numero_agrement' => $user->etablissement->numero_agrement,
                'ville' => $user->etablissement->ville ? $user->etablissement->ville->toArray() : null,
            ];

        } elseif ($user->role === 'entreprise' && $user->entreprise) {
            $profileData['name'] = $user->entreprise->nom_entreprise;
            $profileData['role_details'] = [
                'entreprise_id' => $user->entreprise->id,
                'email_entreprise' => $user->entreprise->email_entreprise,
                'siteweb' => $user->entreprise->siteweb,
                'adresse' => $user->entreprise->adresse,
                'RCCM' => $user->entreprise->RCCM,
                'ville_id' => $user->entreprise->ville_id,
                'id_domaine' => $user->entreprise->id_domaine,
                'domaine' => $user->entreprise->domaine ? $user->entreprise->domaine->toArray() : null,
                'ville' => $user->entreprise->ville ? $user->entreprise->ville->toArray() : null,
            ];
        }

        return response()->json($profileData, 200);
    }


    public function showUserProfile($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisaterur non trouvée'], 404);
        }

        switch ($user->role) {
            case 'etudiant':
                $user->load('etudiant.etablissement', 'etudiant.filiere', 'etudiant.filannee.annee');
                break;
            case 'etablissement':
                $user->load('etablissement.ville');
                break;
            case 'entreprise':
                $user->load('entreprise.domaine', 'entreprise.ville');
                break;
          
            default:
                
                break;
        }

        $defaultPhotoUrl = asset('profile_photos/default_user_photo.png');

        $profileData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email, 
            'role' => $user->role,
            'photo' => $user->photo ? Storage::url($user->photo) : $defaultPhotoUrl ,
            'description' => $user->description,
            'couverture' => $user->couverture ? Storage::url($user->couverture) : null,
            
        ];

        // Ajouter les détails spécifiques au rôle
        if ($user->role === 'etudiant' && $user->etudiant) {
            $profileData['name'] = $user->etudiant->nom_etudiant ?? $user->name;
            $profileData['role_details'] = [
                'projets' => $user->etudiant->projets,
                'competences' => $user->etudiant->competences,
                'CV' => $user->etudiant->CV ? Storage::url($user->etudiant->CV) : null, 
                'parcours' => $user->etudiant->parcours,
                'filiere' => $user->etudiant->filiere ? $user->etudiant->filiere->toArray() : null,
                'etablissement' => $user->etudiant->etablissement ? $user->etudiant->etablissement->toArray() : null,
                'filannee_id' => $user->etudiant->filannee_id,
                'annee_libelle' => ($user->etudiant->filannee && $user->etudiant->filannee->annee) ? $user->etudiant->filannee->annee->libannee : null,
                'filiere_annee_complete' => ($user->etudiant->filiere && $user->etudiant->filannee && $user->etudiant->filannee->annee) ? $user->etudiant->filiere->libfil . ' - ' . $user->etudiant->filannee->annee->libannee : null,
            ];

        } elseif ($user->role === 'etablissement' && $user->etablissement) {
            $profileData['name'] = $user->etablissement->nom_etablissement;
            $profileData['role_details'] = [
                'siteweb' => $user->etablissement->siteweb,
                'adresse' => $user->etablissement->adresse, 
                'ville' => $user->etablissement->ville ? $user->etablissement->ville->toArray() : null,
                
            ];

        } elseif ($user->role === 'entreprise' && $user->entreprise) {
            $profileData['name'] = $user->entreprise->nom_entreprise;
            $profileData['role_details'] = [
                'email_entreprise' => $user->entreprise->email_entreprise, 
                'siteweb' => $user->entreprise->siteweb,
                'adresse' => $user->entreprise->adresse, 
                'domaine' => $user->entreprise->domaine ? $user->entreprise->domaine->toArray() : null,
                'ville' => $user->entreprise->ville ? $user->entreprise->ville->toArray() : null,
            ];
        }

        return response()->json($profileData, 200);
    }
}