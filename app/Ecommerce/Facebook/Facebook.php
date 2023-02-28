<?php
namespace App\Ecommerce\Facebook;
use App\Ecommerce\BaseApi;
use App\Helpers\Common;
use App\Repositories\UserRepository;

class Facebook extends BaseApi{
    protected $_baseApi,$_version;
    public function __construct()
    {
        $this->_baseApi=config('auth.social.facebook.base_api');
        $this->_version=config('auth.social.facebook.version');
        parent::__construct();
    }

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
    private function implodeScope(){
        return implode(',',config('auth.social.facebook.scope'));
    }
    private function encodeState(array $payload,string $type){
        $payload['type']=$type;
        return Common::encodeSocialAuth($payload);
    }

    private function decodeState(string $state){
        return Common::decodeSocialAuth($state);
    }
    public function authHandle(array $request){
        $code=$request['code'];
        $token=self::getToken($code);
        if(!$token['status']){
             self::pusher($request['state']['uuid'],[
                'status'=>false,
                'message'=>'Access denied!'
            ]);
            return;
        }
        if($request['state']['type'] == 'auth'){
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
                'first_name'=>$user['data']['first_name'],
                'last_name'=>$user['data']['last_name'],
                'email'=>$user['data']['email'],
                'email_verified_at'=>date('Y-m-d H:i:s'),
                'platform'=>'facebook',
                'status'=>true,
            ];
            if ($request['state']['type'] == 'new' && self::IsUserExist($request['state'])){
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
           self::pusher($request['state']['uuid'],$pusher);
        }
    }
    public function getToken(string $code){
        $url="$this->_baseApi/$this->_version/oauth/access_token?".http_build_query([
            'client_id'=>config('auth.social.facebook.client_id'),
                'redirect_uri'=>config('auth.social.facebook.redirect_uri'),
                'client_secret'=>config('auth.social.facebook.client_secret'),
                'code'=>$code,
            ]);
        return $this->getRequest($url);
    }

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

    private function pusher($id,$data,$prefix='auth_'){
        Common::pushSocket(
            config('services.pusher.channel'),
            config('services.pusher.event').$prefix.$id,
            $data);
    }
    private function IsUserExist($payload){
        $users= app(UserRepository::class)->findBy([
            'email'=>$payload['email'],
            'internal_id'=>$payload['internal_id'],
            'platform'=>$payload['platform'],
            'status'=>true

        ]);
        return !empty($users);
    }
}
