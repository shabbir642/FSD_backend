<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use App\Jobs\VerifyJob;
use App\Mail\Verifymail;
use App\Mail\ResetingMail;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Cookie;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    //  */
    // public function __construct()
    // {
    //    $this->middleware('cors:api');
    // }

    public function register(Request $request){
        $this->validate($request, [
            'username' => 'required',
            'email' => 'required|email|unique:users',
            'password' => [
                'required',
                'string',
            'min:6',             // must be at least 10 characters in length
            'regex:/[a-z]/',      // must contain at least one lowercase letter
            'regex:/[A-Z]/',      // must contain at least one uppercase letter
            'regex:/[0-9]/',      // must contain at least one digit
            'regex:/[@$!%*#?&]/', // must contain a special character
        ],
        'confirm_password' => 'required|same:password'
    ]);

        $input = $request->only('username','email','password','confirm_password');

        try{
            $user = new User;
            $user->username = $input['username'];

            $user->email = $input['email'];
            $user->password = app('hash')->make($input['password']);
            $user->confirm_password = app('hash')->make($input['confirm_password']);

            $token = bin2hex(random_bytes(20));
            $user->token = $token; 
                        // return response('what');

            $user->save();

            $this->dispatch(new VerifyJob($user,$user));
            return response()->json(['message' => 'Please verify your email by clicking on link send on your email'],200);
        } catch (Exception $e) {
          return response()->json(['message' => 'Error'],404);  
      }
  }

  public function login(Request $request){
     $this->validate($request, [
        'email' => 'required',
        'password' => 'required',
    ]);

   $input = $request->only('email','password');
   $user = User::where('email',$input['email'])->first();

   if(!$user){
    return response()->json(['message' => 'Email id do not exist. Please Sign up']);
    }
        // return response('here');
   if(!$user->is_verify){
       return response()->json(['message' => 'Please verify your email first']);
    }

   if($token = Auth::attempt($input)){
       return $this->respondWithToken($token,$user);
    }
     else{
         return response()->json(['message' => 'Unsuccessful'],500);
     }
    }
public function forgotpassword(Request $request){
    $this->validate($request,[
        'email' => 'required|email',
    ]);
    $email = $request->input('email');
    $get_id = User::where('email',$email)->first();
    if($get_id){
        $user = $get_id;
        if($user->is_verify){
            Mail::to($user)->send(new ResetingMail($user->token));
            return response()->json(['message' => 'Mail send to reset password'],200);
        }
        return response()->json(['message' => 'Please verify your email id'],401);
    }
    return response()->json(['message' => 'Email id do not exist'],404);
}
public function resetpassword(Request $request){
    $this->validate($request,[
        'password' => 'required',
        'confirm_password' => 'required|same:password'
    ]);
    $checkpass = User::where('token',$request->token)->first();
    if($checkpass){
        $user = $checkpass;
        $user->password = app('hash')->make($request->input('password'));
        $user->confirm_password = app('hash')->make($request->input('confirm_password'));
        $user->save();
        return response()->json(['message' => 'Password reset, please login again'],200);
    }
    return response()->json(['message' => 'Email id do not exist, please sign up'],404);
}


public function get_verified(String $token){
        //
    $check_e = User::where('token',$token)->first();
    if($check_e){
        $user = $check_e;
        $user->is_verify = true;
        $user->save();
        return response()->json(['message' => 'Verification successful']);
    }
    return response()->json(['message' => 'Verification unsuccessful']);
}
    //
}
