<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    //
    protected function respondWithToken($token,$user)
    {
        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() + 60
        ], 200);
    }
}
