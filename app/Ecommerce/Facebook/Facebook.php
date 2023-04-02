<?php
namespace App\Ecommerce\Facebook;
use App\Ecommerce\BaseApi;
use App\Helpers\Common;
use App\Helpers\PusherHelper;
use App\Helpers\UserHelper;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class Facebook extends BaseApi{
    protected $_baseApi,$_version;
    public function __construct()
    {
        $this->_baseApi=config('auth.social.facebook.base_api');
        $this->_version=config('auth.social.facebook.version');
        parent::__construct();
    }

    /**
     * @param array $payload
     * @param $type
     * @return string
     */
    public function generateUrl(array $payload=[],$type='auth'){
       return "https://www.facebook.com/v15.0/dialog/oauth?".http_build_query(
               [
                   "client_id"=>config('auth.social.facebook.client_id'),
                   'redirect_uri'=>config('auth.social.facebook.redirect_uri'),
                   'response_type'=>'code',
                   'display'=>'popup',
                   'scope'=>self::implodeScope(),
                   'state'=>self::encodeState($payload,$type)
               ]
           );

    }

    /**
     * @return string
     */
    private function implodeScope(){
        return implode(',',config('auth.social.facebook.scope'));
    }

    /**
     * @param array $payload
     * @param string $type
     * @return string
     */
    private function encodeState(array $payload,string $type){
        $payload['type']=$type;
        return Common::encodeSocialAuth($payload);
    }

    /**
     * @param string $state
     * @return mixed
     */
    private function decodeState(string $state){
        return Common::decodeSocialAuth($state);
    }

    /***
     * @param array $request
     * @return void
     */
    public function authHandle(array $request){
        $code=$request['code'];
        $token=self::getToken($code);
        if(!$token['status']){
            PusherHelper::pusher($request['state']['uuid'],[
                'status'=>false,
                'message'=>'Access denied!'
            ]);
            return;
        }
        if($request['state']['type'] == 'auth'){
            $user=self::getProfile($token['data']['access_token']);
           if(!$user['status']){
               PusherHelper::pusher($request['state']['uuid'],[
                   'status'=>false,
                   'error'=>[
                       'type'=>'account_access_denied',
                       'message'=>'Access denied!',
                   ]
               ]);
               return;
           }
            $payload=[
                'internal_id'=>$user['data']['id'],
                'first_name'=>$user['data']['first_name'],
                'last_name'=>$user['data']['last_name'],
                'email'=>$user['data']['email'],
//                'email_verified_at'=>now(),
                'platform'=>'facebook',
                'avatar'=>@$user['data']['picture']['data']['url'],
                'password'=>Hash::make(123456789),
                'status'=>true,
            ];
            if ($request['state']['type'] == 'new' && UserHelper::IsUserExist($request['state'])){
                PusherHelper::pusher($request['state']['uuid'],[
                    'status'=>false,
                    'error'=>[
                        'type'=>'account_already_exist',
                        'message'=>'Account already exists',
                    ]
                ]);
                return;
            }

            $result=app(UserRepository::class)->updateOrInsert([
                'internal_id'=>$payload['internal_id'],
                'email'=>$payload['email'],
                'platform'=>$payload['platform'],
            ],$payload);
            $payload['id']=$result['id'];
            unset($payload['password']);
            $pusher=[
                'userInfo'=>$payload,
                'token'=>[
                    'type'=>'Bearer',
                    'access_token'=>Common::encodeJWT([
                        'id'=>$result['id'],
                        'email'=>$result['email'],
                        'internal_id'=>$result['internal_id']
                    ]),
                    'time_expire'=>config('auth.key.expire')
                ]
            ];
            PusherHelper::pusher($request['state']['uuid'],$pusher);
        }
    }

    /**
     * @param string $code
     * @return array
     */
    public function getToken(string $code){
        $url="$this->_baseApi/$this->_version/oauth/access_token?".http_build_query([
            'client_id'=>config('auth.social.facebook.client_id'),
                'redirect_uri'=>config('auth.social.facebook.redirect_uri'),
                'client_secret'=>config('auth.social.facebook.client_secret'),
                'code'=>$code,
            ]);
        return $this->getRequest($url);
    }

    /**
     * @param string $token
     * @return array
     */
    private function getProfile(string $token){
        $url="$this->_baseApi/$this->_version/me?".http_build_query([
            'access_token'=>$token,
             'fields'=>implode(',',[
                 'id',
                'name',
                'first_name',
                'last_name',
                'email',
                'birthday',
                'gender',
                'hometown',
                'location',
                'picture'
             ])
            ]);
        return $this->getRequest($url);
    }


}
