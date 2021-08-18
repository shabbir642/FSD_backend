<?php

namespace App\Jobs;
use App\Mail\TaskMail;
use App\Jobs\Job;
use App\Models\User;
use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class TaskJob extends Job implements ShouldQueue
{
    use SerializesModels;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $to;
    protected $from;
    protected $data;
    public function __construct($to,$from,$data)
    {
        $this->to = $to;
        $this->from = $from;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->to)->send(new Taskmail( $this->to,$this->from,$this->data));
    }
}
