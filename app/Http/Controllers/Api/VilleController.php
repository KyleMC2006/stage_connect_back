<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ville;

class VilleController extends Controller
{
    /**
     
     * @return \Illuminate\Http\JsonResponse
     * @param  \Illuminate\Http\Request  $request
     */
    public function index()
    {
        $villes = Ville::all();
        return response()->json($villes, 200);
    }
}
