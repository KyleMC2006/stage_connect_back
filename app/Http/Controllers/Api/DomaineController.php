<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Domaine;
use Illuminate\Http\Request;

class DomaineController extends Controller
{
    /**

     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $domaines = Domaine::all(); 
        return response()->json($domaines, 200);
    }

  
}