<?php

namespace App\Jobs;
use App\Jobs\Job;
use App\Mail\Verifymail;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
class VerifyJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $user;
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->user)->send(new Verifymail($this->user));
    }
}
