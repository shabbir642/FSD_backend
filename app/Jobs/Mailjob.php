<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class Mailjob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $mailtype;
    protected $user;
    public function __construct(Mailable $mailtype, User $user)
    {
        //
        $this->user = $user;
        $this->mailtype = $mailtype;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        Mail::to($this->user)->send($this->mailtype);
    }
}
