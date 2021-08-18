<?php

namespace App\Jobs;
use App\Jobs\Job;
use App\Models\User;
use App\Models\Task;
use App\Mail\Dailymail;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
class Dailytask extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $user;
    protected $task;
    public function __construct($user, $task)
    {
        $this->user = $user;
        $this->task = $task;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->user)->send(new Dailymail($this->user, $this->task));
    }
}
