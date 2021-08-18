<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use App\Models\Task;
use App\Models\Notify;
use App\Events\EventNotification;
use App\Jobs\TaskJob;
use App\Mail\TaskMail;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Datetime;
class TaskController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function createtask(Request $request)
    {
         $this->validate($request,[
            'title' => 'required',
            'description' => 'string',
            'assignee' => 'required',
            'deadline' => 'required|date'
        ]);

         $input = $request->only(['title','description','assignee','deadline']);

         $assigne_free = User:: where('id',$input['assignee'])->first();
         if(!$assigne_free){
            return response()->json(['message'=>'Assignee do not exist']);
         }
         try{
            $newTask = new Task;
            $newTask->title = $input['title'];
            $newTask->status = 'Assigned';

            $newTask->assignor = Auth::user()->id;
            $newTask->assignee = $input['assignee'];
            // $Assigne->status = 0;
            $newTask->description = $input['description'];
            if(array_key_exists('deadline',$input)){
                $date = new DateTime($input['deadline']);
                $newTask->deadline = $date;
            }
            $newTask->save();
            $Assigne = User::find($input['assignee']);
            $Assignor = User::find($newTask->assignor);
            $this->dispatch(new TaskJob($Assigne,$Assignor,$newTask));

            $data = new Notify;
            $data->message = 'Task'.$newTask->title. 'has been created';
            $data->title = 'Task Created';
            $data->user = $newTask->assignee;
            $data->channel = 'my-channel';
            $data->event = 'createtask';
            event(new EventNotification($data));

            return response()->json([
                     'Task' => $newTask->title,
                     'Assignor' => $Assignor->id,
                     'Assignee' => $Assigne->id,
                     'Deadline' => $newTask->deadline
                 ],200);
         }catch (Exception $e) {
                 return response('Error');
        }

    }

    public function listtask(){
        if(Auth::user()->admin){
         $tasks = DB::table('tasks')->orderBy('deadline')->get();
        }
        else{
            $tasks = Task::where('assignee',Auth::user()->id)->orderBy('deadline')->get();
        }
        return response()->json($tasks);
    }
    public function counttask(){
        return Task::count();
    }
    public function taskfortoday($today)
    {
        $task = DB::table('tasks')->where('assignee',Auth::user()->id)->whereDate('deadline', $today)->get();
        return response()->json($task);
    }
    public function filtertask(Request $request)
    {
        $user = User::where('id',Auth::user()->id)->first();

        if(!$user->admin){

             $task = Task::where(function ($q) use ($request){
                $q->where('assignee',Auth::user()->id)
                    ->orWhere('assignor',Auth::user()->id);
             }); 
        } else{
            if($request->has('assignee')){
                $task = Task::where('assignee',$request->assignee);
            }
            else{
                $task = Task::orderBy('deadline');
            }
       }
        if($request->has('title')){
          $task = $task->where('title',$request->title);
        }
        if($request->has('status')){
            $task = $task->where('status',$request->status);
        }
        if($request->has('from') and $request->has('to')){
            $task = $task->whereBetween('deadline',[$request->from, $request->to]);
        } else {
            if($request->has('from')){
                $task = $task->where('deadline','>=',$request->from);
            }
            if($request->has('to')){
                $task = $task->whereDate('deadline','<=',$request->to);
            }
        }
        if($request->has('keyword')){
            $task = $task->where(function ($q) use ($request){
                 $q->where('title', 'LIKE', '%'.$request->keyword.'%')
                    ->orWhere('description','LIKE','%'.$request->keyword.'%');
                });
        }

        $task = $task->get();
        return response()->json($task);
    }

    public function mytask(Request $request)
    {
        // return response('here');

        $id = $request->input('id');
        $user = User::where('id',$id)->first();
        if(!$user){
            return response('User do not exist');
        }
        // return response('here');
        if($user->id != Auth::user()->id){
            return response('Not Authorised');
        }
        if(Auth::user()->admin){
            $task = DB::table('tasks')->orderBy('deadline')->get();
        } else {
        $task = Task::where('assignee',$user->id)->orWhere('assignor',$user->id)->get();
        }
        return response()->json($task);
    }
    public function tasksbyme(Request $request)
    {
        $user = User::find($request->input('id'));
        if(!$user){
            return respone()->json(['message' => 'user not found'],404);
        }
        if($user != Auth::user()){
            return response('Not Authorised');
        }
        $task = DB::table('tasks')->where('assignor',$user->id)->get();
        echo($task);
        return response('Welcome');
        // ->json($task);
    }

    public function taskstome(Request $request)
    {
        $user = User::find($request->input('id'));
        if(!$user){
            return response()->json(['message' => 'user not found'],404);
        }
        if($user != Auth::user()){
            return response('Not Authorised');
        }
        $task = DB::table('tasks')->where('assignee',$user->id)->get();
        // echo($user);
        return response()->json($task);
    }

    public function updatetask(Request $request)
    {
         $this->validate($request,[
            'description' => 'string',
            'deadline' => 'date'
        ]);
        $id = $request->only('id');
        $task = Task::where('id',$id)->first();
        // echo($task->id);
        if(!$task){
            return response()->json(['message' => 'Requested Task is not found'],400);
        }
        if($task->assignor != Auth::user()->id){
            return response()->json(['message'=>'Not authorized for said action'],400);
        }
       
        $input = $request->only(['title','description','deadline']);
        if(array_key_exists('title',$input)){
            $task->title = $input['title'];
        }
        if(array_key_exists('description',$input)){
            $task->description = $input['description'];
        }
        if(array_key_exists('deadline',$input)){
            $task->deadline = new DateTime($input['deadline']);
        }
        $task->save();
        return response()->json($task);
    }
    public function viewtask(Request $request,$id)
    {
        
        $task = $request->input('id');
        // echo($task);
        // $task = Task::where('id',$id)->first();
        // Task::find($id);
        if(!$task){
            return response()->json(['message' => 'Task do not exist'],404);
        }
        if(Auth::user()->id != $task->assignee && Auth::user()->id != $task->assignor){
            return response()->json(['message' => 'Only assignor and assignee can view'],404);
        }
        return response()->json([
            'Task' => $task->title,
            'Description' => $task->description,
            'Assignor' => $task->assignor,
            'Deadline' => $task->deadline 
        ]);
    }
    public function updatestatus(Request $request){
        $this->validate($request,[
            'status' => 'required'
        ]);
        
        $input = $request->only(['id','status']);
        $task = Task::where('id',$input['id'])->first();
        if(!$task){
            return response()->json(['message' => 'Requested Task is not found'],404);
        }
        if($task->assignee != Auth::user()->id){
            return response()->json(['message' => 'Only assignee can change the status'],400);
        }
        // return response('here');
        $task->status = $input['status'];
        $task->save();

            $data = new Notify;
            $data->message = 'Status of Task'.$task->title. 'has been updated';
            $data->title = 'Status Update';
            $data->user = $task->assignee;
            $data->channel = 'my-channel';
            $data->event = 'statusupdate';
            event(new EventNotification($data));
            // return response('here');
        return response()->json(['message' => 'Your task status has been updated'],200);
    }
    public function deletetask($id)
    {
        // return response('here');
        $task = Task::where('id',$id)->first();

        if(!$task){
            return response()->json(['message' => 'Task do not exist'],404);
        }
        if($task->assignor != Auth::user()->id){
            return response()->json(['message' => 'Only assignor can delete task'],400);
        }
        $task->delete();
        return response()->json(['message' => 'Task deleted'],200);
    }
    public function guard(){
        return Auth::guard();
    }
}
