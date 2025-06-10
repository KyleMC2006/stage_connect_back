<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\InvalidToken;

class FirebaseGoogleAuthController extends Controller
{
    /**
     * Handles the callback from the frontend with the Firebase ID Token.
     * Verifies the token and manages user authentication and provides JSON response.
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
            $factory = (new Factory)->withServiceAccount(storage_path('app/firebase_credentials.json'));
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

            Log::info('Utilisateur Laravel créé/mis à jour: ID ' . $user->id . ', Email: ' . $user->email . ', Rôle: ' . ($user->role ?? 'N/A'));

            Auth::login($user);

            $token = $user->createToken('auth_token')->plainTextToken;
            Log::info('Sanctum token généré pour utilisateur ' . $user->id);

            $nextStepPath = '/profile'; // Default path if all is good

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
                    $nextStepPath = '/profil/' . $user->role;
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Authentification réussie.',
                'token' => $token,
                'user' => $user->toArray(), // Convert user model to array for JSON
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
}