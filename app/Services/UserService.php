<?php
namespace App\Services;
use App\Helpers\Common;
use App\Helpers\PusherHelper;
use App\Jobs\SendMailVerifyForgotPasswordJob;
use App\Jobs\SendMailVerifyRegisterJob;
use App\Repositories\Mongo\MongoBaseRepository;
use App\Repositories\UserRepository;
use App\Traits\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Validation\Validator;

class UserService{
    use Response;
    protected $userRepo;
    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo=$userRepo;
    }

    public function register($request){
        try {

            $isCheckAccountExist=$this->userRepo->findBy([
                'email'=>$request->input('email'),
            ],['id']);
            if(!empty($isCheckAccountExist)){
                return $this->ApiResponseError('The account exists');
            }
            $account=$this->userRepo->create([
                'first_name'=>$request->input('name'),
                'email'=>$request->input('email'),
                'password'=>Hash::make($request->input('password')),
                'phone'=>@$request->input('phone_number'),
                'platform'=>config('auth.platform_app'),
                'status'=>true
            ]);
            if(empty($account)){
                return $this->ApiResponseError('Errors,Register is failed,Please try again');
            }
            SendMailVerifyRegisterJob::dispatch($account)->onConnection('redis')->onQueue("send_email");
            return $this->ApiResponse([],'Success,Please verify email',201);
        }catch (\Exception $exception){
            return $this->ApiResponseError('Errors');
        }
    }

    public function login($request){
        $account=$this->userRepo->findBy([
            'email'=>$request->input('email'),
            'platform'=>config('auth.platform_app'),
            'status'=>true,
        ]);

        if(!empty($account) && Hash::check($request->input('password'),$account['password'])){
            if (is_null($account['email_verified_at'])){
                return $this->ApiResponseError("Please,Verify email and login again");
            }
            unset($account['password']);
            $data=$this->createTokenAndInfo($account);
            return $this->ApiResponse($data,"Login Success");
        }
        return $this->ApiResponseError("Email and password does not match");
    }

    private function createTokenAndInfo($account,$hasRefresh = true){
        $data=[
            'userInfo'=>$account,
            'jwt'=>[
                'token'=>Common::encodeJWT([
                    'id'=>$account['id'],
                    'email'=>$account['email'],
                    'platform'=>$account['platform']
                ]),
                'token_type'=>'Bearer',
                'time_expire'=>date("Y-m-d H:i:s",time() + config('auth.key.expire')),
            ]
        ];
        if($hasRefresh){
            $data['jwt']['refresh_token']=Common::encodeJWTRefreshToken([
                'id'=>$account['id'],
                'email'=>$account['email'],
                'platform'=>$account['platform'],
                'type'=>'token_refresh'
            ]);
            $data['jwt']['time_expire_refresh_token']=date("Y-m-d H:i:s",time() + config('auth.key.expire_refresh_token'));
        }
        return $data;
    }

    public function refreshToken($request){
        $account=$request->input('userInfo');
        $data=$this->createTokenAndInfo($account,false);
        return $this->ApiResponse($data,"Refresh token success");
    }

    public function getUsers($request){
        $userIndo=$request->input('userInfo');
        $account=$this->userRepo->findBy([
            'id'=>$userIndo['id'],
            'status'=>true
        ]);
        if (empty($account)){
            return $this->ApiResponseError("User not found");
        }
        unset($account['password']);
        return $this->ApiResponse($account,"User Information");
    }

    public function forgotPassword($request){
        $validator=\Illuminate\Support\Facades\Validator::make($request->all(),[
            'id'=>'required',
            'email'=>'required|email',
            'old_password'=>'required|min:8',
            'new_password'=>'required|min:8'
        ]);

        if ($validator->fails()) {
            return $this->ApiResponseError($validator->messages());
        }
        $account =$this->userRepo->find($request->input('id'));
        if(empty($account)){
            return $this->ApiResponseError("User Not found");
        }
        if(Hash::check($request->input('old_password'),$account['password'])){
            $account->update([
                'password'=>Hash::make($request->input('new_password'))
            ]);
            return $this->ApiResponse(null,"Update password success");
        }
        return $this->ApiResponseError("Password old incorrect");

    }
    public function createLinkForgot($request){
        $validator=\Illuminate\Support\Facades\Validator::make($request->all(),[
            'email'=>'required|email'
        ]);
        if ($validator->fails()) {
            return $this->ApiResponseError($validator->messages());
        }

        $account=$this->userRepo->findBy([
            'email'=>$request->input('email'),
            'status'=>true,
            'platform'=>config('auth.platform_app')
        ]);
        if(empty($account)){
            return $this->ApiResponseError("User not found");
        }
        SendMailVerifyForgotPasswordJob::dispatch($account)->onConnection('redis')->onQueue('send_link_forgot_pass');
        return $this->ApiResponse(null,"Send link verify in your email");

    }

    public function deleteUser($request){
        $account=$this->userRepo->find($request->input('userInfo.id'));
        if(empty($account)){
            return $this->ApiResponseError("User not found");
        }
        $account->delete();
        return $this->ApiResponse(null,"Delete user success");
    }

    public function reSendLinkVerifyEmail($request){
        $account=$this->userRepo->findBy([
            'email'=>$request->input('email'),
            'status'=>true,
            'platform'=>config('auth.platform_app')
        ]);
        if(empty($account)){
            return $this->ApiResponseError("User not found");
        }
        if($request->input('type') === 'register'){

            SendMailVerifyRegisterJob::dispatch($account)->onConnection('redis')->onQueue("send_email");
        }else{
            SendMailVerifyForgotPasswordJob::dispatch($account)->onConnection('redis')->onQueue("send_link_forgot_pass");
        }
        return $this->ApiResponse(null,"sended link verify success");
    }

    public function changePassword($request){
        $account=$this->userRepo->find($request->input('userInfo.id'));
        if(empty($account)){
            return $this->ApiResponseError("User not found");
        }
        if(Hash::check($request->input('old_password'),$account['password'])){
            $account->update([
                'password'=>$request->input('new_password')
            ]);
            return $this->ApiResponse(null,"Update password success");
        }
        return $this->ApiResponseError("Password does not match");
    }
}
