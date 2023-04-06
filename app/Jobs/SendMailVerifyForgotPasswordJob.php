<?php

namespace App\Jobs;

use App\Helpers\Common;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMailVerifyForgotPasswordJob implements ShouldQueue
{
    protected $users;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($users)
    {
        $this->users=$users;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $subject='Verify account forgot password from '.config('app.name');

        $token=config('app.url').'/api/verify-forgot-password?token='.Common::encodeSocialAuth([
                'type'=>'verify-forgot-password',
                'id'=>$this->users['id'],
                'email'=>$this->users['email']
            ],60*5);
        Mail::send('mails.verify-register',['token'=>$token],function  ($message) use ($subject){
            $message->from(config('mail.from.address'))->to($this->users['email'])
                ->subject($subject);
        });
    }
}
