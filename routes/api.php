<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\FireBaseAuthController;
use App\Http\Controllers\Api\CandidatureController;
use App\Http\Controllers\Api\OffreController;
use App\Http\Controllers\Api\EtudiantController;
use App\Http\Controllers\Api\EtablissementController;
use App\Http\Controllers\Api\EntrepriseController;
use App\Http\Controllers\Api\FiliereController;
use App\Http\Controllers\Api\DomaineController;

use App\Http\Controllers\Api\TuteurStageController;

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\GoogleAuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




 // Create this controller

Route::prefix('auth')->group(function () {
    Route::get('/google', [GoogleAuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
});


Route::get('/firebase-login', [FirebaseAuthController::class, 'login']);

Route::get('/test-routes', function() {
    return response()->json(Route::getRoutes()->get());
});

    Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});



   


    Route::get('/etudiants',[EtudiantController::class,'index']);
    Route::get('/etudiants/{id}',[EtudiantController::class,'show']);

    Route::get('/entreprises',[EntrepriseController::class,'index']);
    Route::get('/entreprises/{id}',[EntrepriseController::class,'show']);

    Route::get('/etablissements',[EtablissementController::class,'index']);
    Route::get('/etablissements/{id}',[EtablissementController::class,'show']);


Route::middleware('auth:sanctum')->group(function () {

    

    // Étape 2 Login
    Route::post('/select-role', [FirebaseAuthController::class, 'selectRole']);

    // Étape 3 Login
    Route::post('/complete-profile', [FirebaseAuthController::class, 'completeProfile']);

    // Étape 4 Login
    Route::post('/logout', [FirebaseAuthController::class, 'logout']);
    

    Route::get('/profile', [UserController::class, 'profile']);
    Route::post('/profile', [UserController::class, 'updateProfile']);

    

    //Etudiant

    Route::post('/etudiants', [EtudiantController::class, 'store']);
    Route::put('/etudiants/{id}', [EtudiantController::class, 'update']);
    Route::delete('/etudiants/{id}', [EtudiantController::class, 'destroy']);
    
    Route::post('/candidatures', [CandidatureController::class, 'store']);
    Route::get('/candidatures',[CandidatureController::class,'index']);
    Route::get('/candidatures/{id}',[CandidatureController::class,'show']);
    Route::put('/candidatures/{id}',[CandidatureController::class,'update']);
    Route::delete('/candidatures/{id}',[CandidatureController::class,'destroy']);

    
    
    //Entreprise
    
    Route::post('/entreprises',[EntrepriseController::class,'store']);
    Route::put('/entreprises/{id}',[EntrepriseController::class,'update']);
    Route::delete('/entreprises/{id}',[EntrepriseController::class,'destroy']);


        //ListeOffre
    Route::get('/offres', [OffreController::class,'index']);
    Route::post('/offres',[OffreController::class,'store']);
    Route::get('/offres/{id}', [OffreController::class,'show']);
    Route::put('/offres/{id}', [OffreController::class,'update']);
    Route::delete('/offres/{id}', [OffreController::class,'destroy']);
    
    

    //Etablissements

    Route::post('/etablissements',[EtablissementController::class,'store']);
    Route::put('/etablissements/{id}',[EtablissementController::class,'update']);
    Route::delete('/etablissements/{id}',[EtablissementController::class,'destroy']);
    Route::post('/etablissements/{id}/filieres', function (Request $request, $id) {
        $etablissement = Etablissement::findOrFail($id);

        if ($etablissement->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'filieres' => 'required|array',
            'filieres.*' => 'exists:filieres,id',
        ]);

        $etablissement->ecolefil()->sync($validated['filieres']);

        return response()->json(['message' => 'Filières mises à jour']);
    });


    Route::get('/filieres', [FiliereController::class, 'index']);
    Route::get('/filieres/{id}', [FiliereController::class, 'show']);
    Route::post('/filieres', [FiliereController::class, 'store']);
    Route::put('/filieres/{id}', [FiliereController::class, 'update']);
    Route::delete('/filieres/{id}', [FiliereController::class, 'destroy']);



    
   

    //Tuteur id du tuteur
    Route::get('/tuteur', [TuteurStageController::class, 'index']);
    Route::get('/tuteur/{id}', [TuteurStageController::class, 'show']);
    Route::post('/tuteur', [TuteurStageController::class, 'store']);
    Route::put('/tuteur/{id}', [TuteurStageController::class, 'update']);
    Route::delete('/tuteur/{id}', [TuteurStageController::class, 'destroy']);

 
    



    
    Route::get('/villes', [FiliereController::class,'index']);
    Route::get('/domaines', [DomaineController::class, 'index']);

});


