<?php
namespace App\Ecommerce\Google;
use App\Ecommerce\BaseApi;
use App\Helpers\Common;
use App\Repositories\UserRepository;

class Google extends BaseApi{
    protected $_baseApi,$_version;
    public function __construct()
    {
        $this->_baseApi=config('auth.social.google.base_api');
        $this->_version=config('auth.social.google.version');
        parent::__construct();
    }

    public function generateUrl(array $payload=[],$type='auth'){
        return "https://accounts.google.com/o/oauth2/v2/auth?".http_build_query([
                'client_id'=>config('auth.social.google.client_id'),
                'redirect_uri'=>config('auth.social.google.redirect_uri'),
                'state'=>self::encodeState($payload,$type),
                'response_type'=>'code',
                'scope'=>self::implodeScope(),
            ]);
    }
    private function implodeScope(){
        return implode(' ',config('auth.social.google.scope'));
    }
    private function encodeState(array $payload,string $type){
        $payload['type']=$type;
        return Common::encodeSocialAuth($payload);
    }

    private function decodeState(string $state){
        return Common::decodeSocialAuth($state);
    }
    public function authHandle(array $request){
        $state= $request['state'];
        $code=$request['code'];
        $token=self::getToken($code);
        if(!$token['status']){
            self::pusher($request['state']['uuid'],[
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
                self::pusher($request['state']['uuid'],[
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
                'email_verified_at'=>$user['data']['verified_email'] ? date('Y-m-d H:i:s',time()) : null,
                'first_name'=>@$user['data']['name'] ?? $user['data']['given_name'],
                'last_name'=>'',
                'email'=>$user['data']['email'],
                'avatar'=>$user['data']['picture'],
                'platform'=>'google',
                'status'=>true,
            ];
            if ($state['type'] == 'new' && self::IsUserExist($payload)){
                self::pusher($request['state']['uuid'],[
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
            self::pusher($request['state']['uuid'],[
                'status'=>true,
                'data'=>$pusher
            ]);
        }
    }
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

    private function getProfile(string $token){
        $url="https://www.$this->_baseApi/oauth2/$this->_version/userinfo?alt=json&access_token=".$token;

        return $this->getRequest($url);
    }
    private function pusher($id,$data,$prefix='auth_'){
        Common::pushSocket(
            config('services.pusher.channel'),
            config('services.pusher.event').$prefix.$id,
            $data);
    }
    private function IsUserExist($payload){
       $users= app(UserRepository::class)->findBy([
            'email'=>$payload['email'],
            'platform'=>$payload['platform'],
            'internal_id'=>$payload['internal_id'],
           'status'=>true
        ]);
      return !empty($users);
    }
}
