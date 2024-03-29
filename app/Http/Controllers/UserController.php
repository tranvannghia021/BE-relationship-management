<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\LoginRequest;
use App\Http\Requests\Account\RegisterRequest;
use App\Http\Requests\Account\ReSendLinkRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Services\UserService;
use App\Traits\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UserController extends Controller
{
    use Response;
    protected $userService;
    public function __construct(UserService $userService)
    {
        $this->userService=$userService;
    }
    public function register(RegisterRequest $request){
        if($request->input('password') !== $request->input('confirm_password')){
            $message=[
                'confirm_password'=>[
                    'The confirm password confirmation does not match.'
                ]
            ];
            return $this->ApiResponse(null,$message,422);
        }
        return $this->userService->register($request);
    }

    public function login(LoginRequest $request){
        return $this->userService->login($request);
    }

    public function refreshToken(Request $request){
        return $this->userService->refreshToken($request);
    }
    public function users(Request $request){
        return $this->userService->getUsers($request);
    }

    public function forgot(Request $request){
        $type=$request->input('type');
       if(@$type === 'get_link'){

           return $this->userService->createLinkForgot($request);

       }
           return $this->userService->forgotPassword($request);
    }

    public function deleteUser(Request $request){
        return $this->userService->deleteUser($request);
    }

    public function reSendLinkVerifyEmail(ReSendLinkRequest $request){
        return $this->userService->reSendLinkVerifyEmail($request);
    }

    public function changePassword(ChangePasswordRequest $request){
        return $this->userService->changePassword($request);
    }

    public function updateUsers(Request $request){
        $request=$request->only([
            'name',
            'avatar',
            'email',
            'gender',
            'birthday',
            'phone',
            'address',
            'status',
            'userInfo',
            'settings',
        ]);

        if(!empty($request['name'])){
            $request['first_name']=$request['name'];
            unset($request['name']);
        }
        return $this->userService->updateUser($request);
    }
}
