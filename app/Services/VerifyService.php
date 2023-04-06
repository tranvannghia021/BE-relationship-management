<?php
namespace App\Services;
use App\Helpers\Common;
use App\Repositories\UserRepository;

class VerifyService{
    protected $userRepo;
    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo=$userRepo;
    }

    public function Verify($request){
        $this->userRepo->update($request['userInfo']['id'],[
            'email_verified_at'=>now()
        ]);
        return view('verify-done');
    }

    public function VerifyForgotPassword($request){
        $account=$this->userRepo->find($request['userInfo']['id']);
        unset($account['password']);
        Common::pushSocket(config('services.pusher.channel'),config('services.pusher.event').$account['email'],[
            "status"=>true,
            'data'=>$account
        ]);
    }
}
