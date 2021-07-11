<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    public function register(Request $request){
        // dd($request);
        $this->validate($request, [
        'username' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required'
    ]);
        // dd('user');
        $input = $request->only('username','email','password','token');
        // dd($input);
        try{
            $user = new User;
            $user->name = $input['username'];
            $user->email = $input['email'];
            $password = $input['password'];
            $user->password = app('hash')->make($password);
            $user->token = $input['token'];

            if($user->save() ){
                // 'code' => 200;
                $output = [
                    'user' => $user,
                    // 'code' => $code,
                    'message' => 'Registration Success'
                ];
            } else {
                // 'code' = 500;
                 $output = [
                    // 'code' => $code,
                    'message' => 'Registration Not Success'
                ];
            }
        } catch (Exception $e) {
            // 'code' = $code;
            $output = [
                'code' => 500,
                'message' => 'Error'
            ];
        }

        return response()->json($output);
    }
    //
}
