<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\email_verify;

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
        $this->validate($request, [
        'username' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required'
    ]);
        $input = $request->only('username','email','password');
        try{
            $user = new User;
            $user->username = $input['username'];
            $user->email = $input['email'];
            $user->password = app('hash')->make($input['password']);
            $token = bin2hex(random_bytes(20));
            $user->token = $token; 
            if($user->save() ){
                Mailjob::dispatch(new verify_mail($user),$user);
                 $code = 200;
                $output = [
                    'user' => $user,
                    'code' => $code,
                    'message' => 'Mail Send'
                ];
            } else {
                 $code = 200;
                 $output = [
                    'code' => $code,
                    'message' => 'Registration Not Success'
                ];
            }
        } catch (Exception $e) {
                  $code = 500;
                  $output = [
                     'code' => $code,
                     'message' => 'Error'
            ];
        }

        return response()->json($output,$code);
    }
    public function get_verified(String $token){
        //
        $check_e = User::where('token',$token)->first();
        if($check_e){
            $user = User::find($check_e->id);
            $user->is_verify = true;
            $user->save();
            return response()->json(['message' => 'Verification success']);
        }
        return response()->json(['message' => 'Verification unsuccessful']);
    }
    //
}
