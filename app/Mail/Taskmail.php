<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Task;
class Taskmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    // public $assignee, $assignor, $task;
    public function __construct($assignee,$assignor,$task)
    {
        $this->assignee = $assignee;
        $this->assignor = $assignor;
        $this->task = $task;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('task')
        ->with([
            'assignor' => $this->assignor['username'],
            'assignee' => $this->assignee['username'],
            'task' => $this->task
        ]);
    }
}
