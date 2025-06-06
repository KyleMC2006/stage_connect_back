<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    
    public function profile()
    {
        $user = Auth::user();
        return response()->json($user);
    }

    
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'description' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|max:2048',
            'couverture' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('photo')) {
            $user->photo = $request->file('photo')->store('photos', 'public');
        }
        if ($request->hasFile('couverture')) {
            $user->couverture = $request->file('couverture')->store('couvertures', 'public');
        }

        if ($request->has('description')) {
            $user->description = $request->description;
        }

        $user->save();

        return response()->json($user);
    }
}
