<?php
namespace App\Ecommerce\Google;
use App\Ecommerce\BaseApi;
use App\Helpers\Common;
use App\Helpers\PusherHelper;
use App\Helpers\UserHelper;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class Google extends BaseApi{
    protected $_baseApi,$_version;
    public function __construct()
    {
        $this->_baseApi=config('auth.social.google.base_api');
        $this->_version=config('auth.social.google.version');
        parent::__construct();
    }

    /**
     * @param array $payload
     * @param $type
     * @return string
     */
    public function generateUrl(array $payload=[],$type='auth'){
        return "https://accounts.google.com/o/oauth2/v2/auth?".http_build_query([
                'client_id'=>config('auth.social.google.client_id'),
                'redirect_uri'=>config('auth.social.google.redirect_uri'),
                'state'=>self::encodeState($payload,$type),
                'response_type'=>'code',
                'scope'=>self::implodeScope(),
            ]);
    }

    /**
     * @return string
     */
    private function implodeScope(){
        return implode(' ',config('auth.social.google.scope'));
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

    /**
     * @param array $request
     * @return void
     */
    public function authHandle(array $request){
        $state= $request['state'];
        $code=$request['code'];
        $token=self::getToken($code);
        if(!$token['status']){
            PusherHelper::pusher($request['state']['uuid'],[
                'status'=>false,
                'error'=>[
                    'type'=>'account_access_denied',
                    'message'=>'Access denied!',
                ]
            ]);
            return;
        }
        if($state['type'] == 'auth'){
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
                'email_verified_at'=>$user['data']['verified_email'] ? now() : null,
                'first_name'=>@$user['data']['name'] ?? $user['data']['given_name'],
                'last_name'=>'',
                'email'=>$user['data']['email'],
                'avatar'=>$user['data']['picture'],
                'password'=>Hash::make(123456789),
                'platform'=>'google',
                'status'=>true,
            ];
            if ($state['type'] == 'new' && UserHelper::IsUserExist($payload)){
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
            PusherHelper::pusher($request['state']['uuid'],[
                'status'=>true,
                'data'=>$pusher
            ]);
        }
    }

    /**
     * @param string $code
     * @return array
     */
    public function getToken(string $code){
        $url="https://oauth2.$this->_baseApi/token";
        $body = [
            'code' => $code,
            'client_id' => config('auth.social.google.client_id'),
            'client_secret' => config('auth.social.google.client_secret'),
            'redirect_uri' => config('auth.social.google.redirect_uri'),
            'grant_type' => 'authorization_code'
        ];
        return $this->postRequest($url,[
            'Content-Type'=>'application/json'
        ],$body);
    }

    /**
     * @param string $token
     * @return array
     */
    private function getProfile(string $token){
        $url="https://www.$this->_baseApi/oauth2/$this->_version/userinfo?alt=json&access_token=".$token;
        return $this->getRequest($url);

    }


}
