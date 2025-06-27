<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CandidatureController;
use App\Http\Controllers\Api\OffreController;
use App\Http\Controllers\Api\EtudiantController;
use App\Http\Controllers\Api\EtablissementController;
use App\Http\Controllers\Api\EntrepriseController;
use App\Http\Controllers\Api\AnneeController;
use App\Http\Controllers\Api\FiliereController;
use App\Http\Controllers\Api\VilleController;
use App\Http\Controllers\Api\DomaineController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Auth\FirebaseGoogleAuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProfilCommuController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\StageController;
use App\Http\Controllers\Api\CommentaireController;
use App\Http\Controllers\Api\PartenariatController;



// JUSTE DES TESTS OUBLIEES !!!!

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test-cors', function() {
    return response()->json([
        'allowed_origins' => config('cors.allowed_origins'),
        'frontend_url' => env('FRONTEND_URL')
    ]);
});




 Route::post('/auth/google/firebase-callback', [FirebaseGoogleAuthController::class, 'handleFirebaseGoogleCallback']);


// FONCTION QUI RECUPERE LES INFOS DE N'IMPORTE QUI POUR (VOIR DETAILS PROFILS)
Route::get('/users/{id}/profile', [UserController::class, 'showUserProfile']);

 Route::post('/test1', function(){
    return('test');
});



// TOUJOURS DES TESTS

Route::get('/test-routes', function() {
    return response()->json(Route::getRoutes()->get());
});

    Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});



// LES INDEX SONT LA POUR RECUPERER LA LISTE DE TOUTES LES ENTITES PAR ROLE
// LES SHOW(ID) C4EST POUR RECUPEPR LES ELEMENTS D'UNE ENTITE SUIVANT SONID (PROBABLEMENT PEU UTILES)
    Route::get('/etudiants',[EtudiantController::class,'index']);
    Route::get('/etudiants/{id}',[EtudiantController::class,'show']);

    Route::get('/entreprises',[EntrepriseController::class,'index']);
    Route::get('/entreprises/{id}',[EntrepriseController::class,'show']);

    Route::get('/etablissements',[EtablissementController::class,'index']);
    Route::get('/etablissements/{id}',[EtablissementController::class,'show']);

        
    Route::get('/villes', [VilleController::class,'index']);
    Route::get('/domaines', [DomaineController::class, 'index']);
    Route::get('/filieres', [FiliereController::class, 'index']);
    Route::get('/annees', [AnneeController::class, 'index']);


    ///ROUTES PROTEGES
Route::middleware('auth:sanctum')->group(function (){

    // --- NOUVEAU : Route de déconnexion ---
    Route::post('/logout', [UserController::class, 'logout']);

    Route::post('/user/set-role', [UserController::class, 'setRole']);


    // RECUPERER PHOTO , COUVERTURE ET DESC D'UN USER AVEC LES INFOS DE SATABLE DE ROLE SPECIFIQUE
    Route::get('/user/getProfile', [UserController::class, 'getUserProfile']);

   //COMPLETE PROFIL
    Route::post('/user/complete-profile/{role}', [UserController::class, 'completeProfile']);

 
//MODIFIER PHOTO COUVERTURE ET DESC 

    Route::post('/photo-update', [UserController::class, 'updatePhotos']);
    Route::post('/couverture-update', [UserController::class, 'updateCouverture']);
    Route::post('/description-update', [UserController::class, 'updateDescription']);




   
    //Update infos etudiant
    Route::put('/etudiants/{id}', [EtudiantController::class, 'update']);
    Route::delete('/etudiants/{id}', [EtudiantController::class, 'destroy']);

    //Update infos entreprise
    Route::put('/entreprises/{id}',[EntrepriseController::class,'update']);
    Route::delete('/entreprises/{id}',[EntrepriseController::class,'destroy']);


    //Update infos etablissements
    Route::put('/etablissements/{id}',[EtablissementController::class,'update']);
    Route::delete('/etablissements/{id}',[EtablissementController::class,'destroy']);

    

  


    // Récupérer les filières d'un établissement avec leurs années
    Route::get('/etablissements/{id}/indexFiliereAnnees', [EtablissementController::class, 'getFilieresAnnees']);

    // Gérer l'association d'une filière et de ses années pour un établissement
    Route::post('/etablissements/{id}/gererFilereAnnees', [EtablissementController::class, 'gererFiliereAnnees']);

//SYNCHRONISER FILIERE
    Route::post('/filieres/{id}/annees/attach', [FiliereController::class, 'attachAnnees']);
    Route::post('/filieres/{id}/sync-annees', [FiliereController::class, 'syncAnnees']);

    Route::get('/fil-annees/filter-by-filiere/{filiereId}/etablissement/{etablissementId}', [FiliereController::class, 'filterByFiliereAndEtablissement']);

 //Créer
    Route::post('/filieres', [FiliereController::class, 'store']);
    
    //Annne
    Route::post('/annees', [AnneeController::class, 'store']); 
    
     //  LES CANDIDATURES TCHIAA
    Route::post('/candidatures', [CandidatureController::class, 'store']); 
    Route::get('/candidatures', [CandidatureController::class, 'index']); 
    Route::get('/candidatures/{id}', [CandidatureController::class, 'show']); 

    
    
        
    Route::post('/candidatures/{id}/accepter-entreprise', [CandidatureController::class, 'accepterParEntreprise']);
    Route::post('/candidatures/{id}/refuser-entreprise', [CandidatureController::class, 'refuserParEntreprise']);
    Route::post('/candidatures/{id}/confirmer-etudiant', [CandidatureController::class, 'confirmerParEtudiant']);
    Route::post('/candidatures/{id}/desister-etudiant', [CandidatureController::class, 'desisterParEtudiant']);
    Route::get('/candidatures/statistics', [CandidatureController::class, 'getStatistics']);

    Route::delete('/candidatures/{id}', [CandidatureController::class, 'destroy']); 
   

//Offres

    Route::get('/offres', [OffreController::class, 'index']); // offres actives
    Route::get('/offres/{id}', [OffreController::class, 'show']);

    Route::post('/offres', [OffreController::class, 'store']); 
    Route::put('/offres/{id}', [OffreController::class, 'update']); 
    Route::delete('/offres/{id}', [OffreController::class, 'destroy']); 
    Route::get('/mes-offres', [OffreController::class, 'mesOffres']); // Pour les entreprises
    
    Route::get('/get-offer-statistics', [OffreController::class, 'getStatistics']);


        //  NOTIFICATIONS 
    Route::get('/notifications', [NotificationController::class, 'index']); 
    Route::get('/notifications/{id}', [NotificationController::class, 'show']); 
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']); 
    
Route::get('/notif-all', [NotificationController::class, 'all'])->withoutMiddleware('auth:sanctum'); 


    
    Route::get('/filieres/{id}', [FiliereController::class, 'show']);
    Route::put('/filieres/{id}', [FiliereController::class, 'update']);
    Route::delete('/filieres/{id}', [FiliereController::class, 'destroy']);



    


    //stages

     
        Route::get('/stages', [StageController::class, 'index']);
        Route::get('/stages/{id}', [StageController::class, 'show']);
        Route::put('/stages/{id}', [StageController::class, 'update']);
        Route::delete('/stages/{id}', [StageController::class, 'destroy']);
        Route::get('/stages/{id}/rapport/download', [StageController::class, 'downloadRapport']);

//messages 

    Route::prefix('messages')->group(function () {
        //  /api/messages (liste des conversations)
        Route::get('/', [MessageController::class, 'index']);

        //  /api/messages (envoyer un nouveau message)
        Route::post('/', [MessageController::class, 'store']);

        // GET /api/messages/conversation/{id} (récupérer les messages d'une conversation)
        // Le paramètre {id} sera l'ID de l'autre utilisateur dans la conversation
        Route::get('/conversation/{id}', [MessageController::class, 'getConversation']);

        // PUT /api/messages/{id}/mark-as-read (marquer un message spécifique comme lu)
        // Le paramètre {id} sera l'ID du message à marquer comme lu
        Route::put('/{id}/mark-as-read', [MessageController::class, 'markAsRead']);


        // DELETE /api/messages/{id} (supprimer un message spécifique)
        Route::delete('/{id}', [MessageController::class, 'destroy']);
        

});


//likes et ProfilsCommus


    Route::prefix('profils-commu')->group(function () {
        
         // GET /api/profils-commu (liste des posts communautaires)
        Route::get('/', [ProfilCommuController::class, 'index'])->withoutMiddleware('auth:sanctum');



        // GET /api/profils-commu/{id} (afficher un post spécifique)
        Route::get('/{id}', [ProfilCommuController::class, 'show'])->withoutMiddleware('auth:sanctum');


        // POST /api/profils-commu/{id}/like (pour ajouter un like)
        Route::post('/{id}/like', [ProfilCommuController::class, 'ajoutLike']);

        // POST /api/profils-commu/{id}/unlike (pour retirer un like)
        Route::post('/{id}/unlike', [ProfilCommuController::class, 'retirerLike']);
    

    //Commentaires

    // Routes pour les Commentaires sous les Profils Commu
        Route::prefix('{id}/commentaires')->group(function () {
            // GET /api/profils-commu/{id}/commentaires (liste les commentaires pour un post)
            Route::get('/', [CommentaireController::class, 'index'])->withoutMiddleware('auth:sanctum');

            // POST /api/profils-commu/{id}/commentaires (ajoute un commentaire à un post)
            Route::post('/', [CommentaireController::class, 'store']);
        });

    });
 

    Route::prefix('commentaires')->group(function () {
        // PUT /api/commentaires/{id} (met à jour un commentaire)
        Route::put('/{id}', [CommentaireController::class, 'update']);


        // DELETE /api/commentaires/{id} (supprime un commentaire)
        Route::delete('/{id}', [CommentaireController::class, 'destroy']);
    });

    //Partenariats

    Route::prefix('partenariats')->group(function () {
        Route::get('/', [PartenariatController::class, 'index']); // Liste des partenariats
        Route::post('/', [PartenariatController::class, 'store']); // Envoyer une demande de partenariat 
        Route::get('/{id}', [PartenariatController::class, 'show']); // Voir les détails d'un partenariat spécifique
        Route::put('/{id}', [PartenariatController::class, 'update']); // Mettre à jour un partenariat (accepter/refuser par l'Entreprise)
        Route::delete('/{id}', [PartenariatController::class, 'destroy']); // Supprimer un partenariat
    });


});


