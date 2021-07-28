<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use App\Models\Task;
use App\Mail\TaskMail;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Cookie;

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
            'deadline' => 'date_format:Y-m-d\TH:i'
        ]);
         $input = $request->only(['title','description','status','assignee','deadline']);
         $assigne_free = User:: where('id',$input['assignee'])->first();
         if(!$assigne_free){
            return response()->json(['message'=>'Assignee do not exist']);
         }
         $Assigne = User:: find($assigne_free->id);
         if($Assigne->status == 0){
            return response()->json(['message'=>'Assignee is not free']);
         }
         try{
            $newTask = new Task;
            $newTask->title = $input['title'];
            $newTask->status = 'Assigned';
            $newTask->assignor = Auth::user()->id;
            $newTask->assignee = $input['assignee'];
            $Assigne->status = 0;
            $newTask->description = $input['description'];
            if(array_key_exists('deadline',$input)){
            $newTask->deadline =  date('Y-m-d\TH:i', strtotime($input['deadline']));
            }
            $newTask->save();
            $Assignor = User::find($newTask->assignor);
            Mail::to($Assigne)->send(new Taskmail($Assigne,$Assignor,$newTask));
            return response()->json([
                     'Task' => $newTask->title,
                     'Assignor' => $Assignor->id,
                     'Assignee' => $Assigne->id,
                     'Deadline' => $newTask->deadline
                 ],201);
         }catch (Exception $e) {
                 return response('Error');
        }

    }
    public function listtask(){
        return Task :: all();
    }
    // public function tasklisting()
    // {
        
    // }
    public function updatetask(Request $request,$id)
    {
        $task = Task::find($id);
        if(!$task){
            return response()->json(['message' => 'Requested Task is not found'],400);
        }
        if($task->assignor != Auth::user()->id){
            return response()->json(['message'=>'Not authorized for said action'],400);
        }
        $this-validate($request,[
            'description' => 'string',
            'deadline' => 'date_format:Y-m-d\TH:i',
        ]);
        $input = $request->only(['title','description','deadline']);
        if(array_key_exists('title',$input)){
            $task->title = $input['title'];
        }
        if(array_key_exists('description',$input)){
            $task->description = $input['description'];
        }
        if(array_key_exists('deadline',$input)){
            $task->deadline = date('Y-m-d\TH:i', strtotime($input['deadline']));
        }
        $task->save();
        return response()->json(['message'=>'Task is being updated']);
    }
    public function viewtask($id)
    {
        $task = Task::find($id);
        if(!task){
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
        $task = Task::find($input['id']);
        if(!$task){
            return response()->json(['message' => 'Requested Task is not found'],404);
        }
        if($task->assignee != Auth::user()->id){
            return response()->json(['message' => 'Only assignee can change the status'],400);
        }
        $task->status = $input['status'];
        $task->save();
        if($input['status'] == 'Complete'){
            Auth::user()->status = 1;
        }
        return response()->json(['message' => 'Your task status has been updated'],200);
    }
    public function deletetask($id)
    {
        $task = Task::find($id);
        if(!task){
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
