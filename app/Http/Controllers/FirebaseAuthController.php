<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseAuthService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FirebaseAuthController extends Controller
{
    public function login(Request $request, FirebaseAuthService $firebaseAuthService)
    {
        
        $request->validate([
            'idToken' => 'required|string'
        ]);

        try {
            $firebaseUser = $firebaseAuthService->getUserFromToken($request->idToken);

            $user = User::updateOrCreate(
                ['firebase_uid' => $firebaseUser->uid],
                [
                    'name' => $firebaseUser->displayName ?? 'Unnamed',
                    'email' => $firebaseUser->email,
                ]
            );

            Auth::login($user);

            return response()->json([
                'message' => 'User authenticated',
                'user' => $user,
                'token' => $user->createToken('api-token')->plainTextToken,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Authentication failed: ' . $e->getMessage()], 401);
        }
    }
}

