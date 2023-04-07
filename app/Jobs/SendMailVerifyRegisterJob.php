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

class SendMailVerifyRegisterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $users;
    /**
     * Create a new job instance.
     */
    public function __construct($users)
    {
//        $this->OnQueue("send_email");
        $this->users=$users;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Common::createCollection($this->users['id']);
            $subject='Verify account from '.config('app.name');

            $token=config('app.url').'/api/verify-email?token='.Common::encodeSocialAuth([
                    'type'=>'verify-email',
                    'id'=>$this->users['id'],
                    'email'=>$this->users['email']
                ],60*5);
            Mail::send('mails.verify-register',['token'=>$token],function  ($message) use ($subject){
                $message->from(config('mail.from.address'))->to($this->users['email'])
                    ->subject($subject);
            });
        }catch (\Exception $exception){
            throw $exception;
        }
    }
}
