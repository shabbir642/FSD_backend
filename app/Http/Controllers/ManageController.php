<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use App\Models\Task;
use App\Mail\Verifymail;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Cookie;

class ManageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('Admin:api',['except'=>['userlist','me','refresh','logout'] ]);
    }
    public function me(){
        return response()->json(Auth::User());
    }
    public function userlist(){
        return User :: all();
        // $users = User::paginate(4);
        // return view('paging')->with('users',$users);
    }
    public function filteruser($keyword)
    {
        // return response('here');
        if(!!$keyword){
            $user = User::where('username', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('email','LIKE','%'.$keyword.'%')->get();
          return response()->json($user);
        }
        return User :: all();
        
    }
    public function refresh(){
        return $this->respondWithToken($this->guard()->refresh());
    }
    public function logout()
    {
        Auth::logout();
        return response('User is Logged out');
    }
    public function makeadmin(Request $request){
            $our_user = $request->input('id');
            $our_user = User:: where('id',$our_user)->first();
            if(!$our_user) return response('User id do not esist');
            $our_user->admin = 1;
            $our_user->save();
            return response()->json(['message' => 'User is admin now']);
    }
    public function removeadmin(Request $request){
            $our_user = $request->input('id');
            $our_user = User:: where('id',$our_user)->first();
            if(!$our_user) return response('User id do not esist');
            $our_user->admin = NULL;
            $our_user->save();
            return response()->json(['message' => 'User is not an admin now']);
    }
    public function adduser(Request $request){
        $this->validate($request,[
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
            $user->confirm_password = app('hash')->make($input['password']);
            $token = bin2hex(random_bytes(20));
            $user->token = $token; 
            $current = $this->auth->guard()->user();
            $current = json_decode($current);
            $user->created_by = $current->username;
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
  public function countuser()
  {
    return User::count();
  }

    public function delete($id){
            $our_user = User:: where('id',$id)->first();
            if(!$our_user){
             return response('User Absent');
           } 
           // return response('here');
            Task::where('assignee',$our_user->id)->update(['assignee' => null]);
            $our_user->deleted_by = Auth::user()->id;
            $our_user->save();
            $our_user->delete();
            return response()->json(['message' => 'User deleted']);
    }
     public function guard(){
        return Auth::guard();
    }
}
