<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Annee;

class AnneeController extends Controller
{
    /**
     
     * @return \Illuminate\Http\JsonResponse
     *  @param  \Illuminate\Http\Request  $request
     */
    public function index()
    {
        $annees = Annee::all(); // Récupère toutes les années
        return response()->json($annees, 200);
    }
    
    
}
