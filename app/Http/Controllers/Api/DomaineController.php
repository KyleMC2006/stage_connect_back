<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Domaine;

class DomaineController extends Controller
{
    public function index()
    {
        return response()->json(Domaine::all());
    }
}
