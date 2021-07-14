<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use App\Mail\Verifymail;
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
     */

    public function register(Request $request){
        $this->validate($request, [
        'username' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required',
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
            if($user->save() ){
                Mail::to($user)->send(new Verifymail($user));
                // Mailjob::dispatch(new verify_mail($user),$user);
                 $code = 200;
                $output = [
                    'user' => $user,
                    'code' => $code,
                    'message' => 'Mail Send'
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

    public function login(Request $request){
         $this->validate($request, [
        'email' => 'required',
        'password' => 'required',
    ]);
          // $ur_token = $this->respondWithToken($authorised);
        $input = $request->only('email','password');
        if($authorised = Auth::attempt($input)){
            $token = $this->respondWithToken($authorised);
            $code = 200;
            $output = [
                'message' => 'User authorised',
                'token' => $token
            ];
            // $expiry = Auth::factory()->getTTL()*60;
            // return response('Login Success')->withCookie(new cookie('token',$authorised,$expiry));
        }
        else{
             $code = 401;
                  $output = [
                     'code' => $code,
                     'message' => 'User not authorised'
            ];
        }
        return response()->json($output,$code);
    }


    public function forgotpassword(Request $request){
        $this->validate($request,[
            'email' => 'required|email',
        ]);
        $email = $request->input('email');
        $get_id = User::where('email',$email)->first();
        if($get_id){
            $user = User::find($get_id->id);
            if($user->is_verify){
                // $response = $this->broker()->sendResetLink($email);
                // return $response == Password::RESET_LINK_SENT
                //            ? response()->json(true)
                //            : response()->json(false);
                 // Mail::to($user)->send(new Verifymail($user));
                Mail::to($user)->send(new ResetingMail($user));
                return response()->json(['message' => 'Mail send to reset password']);
            }
            return response()->json(['message' => 'Please verify your email id']);
        }
        return response()->json(['message' => 'Email id do not exist']);
    }


    public function resetpassword(Request $request){
        $this->validate($request,[
            'token' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password'
        ]);
        $checkpass = User::where('token',$token)->first();
        if($checkpass){
            $user = User::find($checkpass->id);
            $user->password = app('hash')->make($request->input('password'));
            $user->confirm_password = app('hash')->make($request->input('confirm_password'));
            $user->save();
            return response()->json(['message' => 'Password reset, please login again']);
        }
        return response()->json(['message' => 'Invalid token id']);
    }


    public function get_verified(String $token){
        //
        $check_e = User::where('token',$token)->first();
        if($check_e){
            $user = User::find($check_e->id);
            $user->is_verify = true;
            $user->save();
            return response()->json(['message' => 'Verification successful']);
        }
        return response()->json(['message' => 'Verification unsuccessful']);
    }
    //
}
