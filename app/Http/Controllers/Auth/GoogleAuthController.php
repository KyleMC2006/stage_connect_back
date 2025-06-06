<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log; 

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     * This will be called by your frontend.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function redirectToGoogle()
    {

         return Socialite::driver('google')->stateless()->redirect();
       //  try {
            
            // This will return the URL to your frontend
         //   $redirectUrl = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();

        //    return response()->json([
         //       'redirect_url' => $redirectUrl
         //   ]);

        //} catch (\Exception $e) {
        //    Log::error('Google OAuth redirect error: ' . $e->getMessage());
        //    return response()->json(['error' => 'Could not redirect to Google: ' . $e->getMessage()], 500);
        //}
    }

    /**
     * Handle the callback from Google.
     * This is the URL Google will redirect to with the authorization code.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            
            $googleUser = Socialite::driver('google')->stateless()->user();

             //create the user in your database
            $user = User::updateOrCreate(
                [
                    'google_id' => $googleUser->id,
                ],
                [
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => null, // Or Hash::make(Str::random(16)),
                ]
            );

            // Log the user in and generate an API token
            Auth::login($user); // Log the user into Laravel's authentication system

            
            // Assuming you're using Laravel Sanctum:
            $token = $user->createToken('auth_token')->plainTextToken;

            
            // with a token 
             return response()->json([
                 'message' => 'Login successful',
                 'user' => $user,
                'token' => $token,
             ]);

        } catch (\Exception $e) {
            Log::error('Google OAuth callback error: ' . $e->getMessage());
            
            $frontendErrorUrl = env('FRONTEND_URL') . '/auth/error?message=' . urlencode('Google authentication failed: ' . $e->getMessage());
            return redirect($frontendErrorUrl);
            // return response()->json(['error' => 'Google authentication failed: ' . $e->getMessage()], 500);
        }
    }
}