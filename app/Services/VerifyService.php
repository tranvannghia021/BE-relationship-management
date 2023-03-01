<?php
namespace App\Services;
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
}
