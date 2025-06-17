<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ProfilCommu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\ProfilCommuController;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\InvalidToken;

class FirebaseGoogleAuthController extends Controller
{
    /**
*
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleFirebaseGoogleCallback(Request $request)
    {
        $idTokenString = $request->input('id_token');

        if (empty($idTokenString)) {
            Log::error('Firebase ID token non fourni dans la requête.');
            return response()->json([
                'status' => 'error',
                'message' => 'Token d\'authentification Firebase manquant.',
            ], 400); // Bad Request
        }
        
        try {
            $firebaseCredentialsPath = base_path(env('FIREBASE_CREDENTIALS_PATH'));

            $factory = (new Factory)->withServiceAccount($firebaseCredentialsPath);
            $auth = $factory->createAuth();

            $verifiedIdToken = $auth->verifyIdToken($idTokenString);


            $uid = $verifiedIdToken->claims()->get('sub');
            $email = $verifiedIdToken->claims()->get('email');
            $name = $verifiedIdToken->claims()->get('name');
            $picture = $verifiedIdToken->claims()->get('picture');

            Log::info('Firebase user verified: UID ' . $uid . ', Email: ' . $email . ', Name: ' . $name);

            if (empty($email)) {
                Log::error('E-mail de l\'utilisateur Firebase non disponible. Impossible de créer/mettre à jour l\'utilisateur.');
                return response()->json([
                    'status' => 'error',
                    'message' => 'E-mail Firebase non disponible. Authentification impossible.',
                    'code' => 'email_not_available'
                ], 400); // Bad Request
            }
            
            $user = User::updateOrCreate(
                [
                    'email' => $email,
                ],
                [
                    'name' => $name,
                    'google_id' => null,
                    'firebase_uid' => $uid,
                    'password' => null,
                    
                ]
            );

            if (!$user->profilCommu) {
                ProfilCommu::create([
                    'user_id' => $user->id,
                    'likes' => 0, // Initialize with 0 likes or a default value
                ]);
                Log::info('ProfilCommu created for user ID: ' . $user->id);
            } else {
                Log::info('ProfilCommu already exists for user ID: ' . $user->id);
            }

            $user->update(['en_ligne' => true]);
            Log::info('Statut en ligne mis à jour pour l\'utilisateur ID: ' . $user->id);

            Log::info('Utilisateur Laravel créé/mis à jour: ID ' . $user->id . ', Email: ' . $user->email . ', Rôle: ' . ($user->role ?? 'N/A'));

            Auth::login($user);
            
            Log::info('Après Auth::login(): Auth::check() = ' . (Auth::check() ? 'true' : 'false'));
            if (Auth::check()) {
                Log::info('Après Auth::login(): Utilisateur authentifié (ID: ' . Auth::id() . ', Email: ' . Auth::user()->email . ')');
            } else {
                Log::warning('Après Auth::login(): Utilisateur non authentifié malgré l\'appel à Auth::login().');
            }
            Log::info('Objet $user passé à Auth::login(): ', ['type' => get_class($user), 'id' => $user->id ?? 'null']);


            $token = $user->createToken('auth_token')->plainTextToken;
            Log::info('Sanctum token généré pour utilisateur ' . $user->id);

            $nextStepPath = '/profile'; 

            if (is_null($user->role)) {
                $nextStepPath = '/select-role';
            } else {
                $profileExists = false;
                switch ($user->role) {
                    case 'etudiant':
                        $profileExists = !is_null($user->etudiant);
                        break;
                    case 'etablissement':
                        $profileExists = !is_null($user->etablissement);
                        break;
                    case 'entreprise':
                        $profileExists = !is_null($user->entreprise);
                        break;
                    default:
                        Log::warning('Utilisateur ' . $user->id . ' a un rôle inconnu: ' . $user->role);
                        $profileExists = true;
                        break;
                }

                if (!$profileExists) {
                    $nextStepPath = '/complete-profile/' . $user->role;
                } else {
                    $nextStepPath =   $user->role .'-dashboard/';
                }
            }
            

            return response()->json([
                'status' => 'success',
                'message' => 'Authentification réussie.',
                'token' => $token,
                'user' => $user->toArray(), 
                'next_step' => $nextStepPath
            ], 200); // OK

        } catch (InvalidToken $e) {
            Log::error('Token Firebase ID invalide : ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Token d\'authentification Firebase invalide ou expiré.',
                'code' => 'invalid_token'
            ], 401); // Unauthorized
        } catch (\Exception $e) {
            Log::error('Échec de l\'authentification Google via Firebase : ' . $e->getMessage());
            return response()->json([
                'status' => 'Erreur',
                'message' => 'Échec de l\'authentification. Une erreur interne est survenue.',
                'code' => '500 serveur interne'
            ], 500); // Internal Server Error
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {

            $user->update(['en_ligne' => false]);
            Log::info('Statut en ligne mis à jour à false pour l\'utilisateur ID: ' . $user->id);

            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Déconnexion réussie.'], 200);
        }

        return response()->json(['message' => 'Aucun utilisateur authentifié à déconnecter.'], 401);
    }
}