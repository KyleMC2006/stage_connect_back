<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ville;

class VilleController extends Controller
{
    public function index(){
        return response()->json(Ville::all());
    }
}
