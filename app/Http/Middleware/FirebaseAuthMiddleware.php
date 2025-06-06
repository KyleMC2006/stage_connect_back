<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FirebaseAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        
        $idToken = $request->bearerToken();

        if (!$idToken) {
            return response()->json(['error' => 'Token manquant'], 401);
        }

        try {
            $verifiedIdToken = FirebaseService::auth()->verifyIdToken($idToken);
            $uid = $verifiedIdToken->claims()->get('sub');

            $user = User::where('firebase_uid', $uid)->first();

            if (!$user) {
                return response()->json(['error' => 'Utilisateur non trouvé'], 403);
            }

            Auth::login($user); 
            return $next($request);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Token invalide', 'details' => $e->getMessage()], 401);
        }
    }
}
