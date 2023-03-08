<?php
namespace App\Ecommerce\Github;
use App\Ecommerce\BaseApi;
use App\Helpers\Common;
use App\Helpers\PusherHelper;
use App\Helpers\UserHelper;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class Github extends BaseApi {
    protected $_baseApi,$_version,$_host;
    public function __construct()
    {
        $this->_baseApi=config('auth.social.github.base_api');
        $this->_version=config('auth.social.github.version');
        $this->_host=config('auth.social.github.host');
        parent::__construct();
    }

    /**
     * @param array $payload
     * @param $type
     * @return string
     */
    public function generateUrl(array $payload=[],$type='auth'){
        return "$this->_host/login/oauth/authorize?".http_build_query(
                [
                    "client_id"=>config('auth.social.github.client_id'),
                    'redirect_uri'=>config('auth.social.github.redirect_uri'),
                    'scope'=>self::implodeScope(),
                    'state'=>self::encodeState($payload,$type)
                ]
            );

    }

    /**
     * @return string
     */
    private function implodeScope(){
        return implode(' ',config('auth.social.github.scope'));
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
        $code=$request['code'];
        $token=self::getToken($code);
        if(!$token['status']){
            PusherHelper::pusher($request['state']['uuid'],[
                'status'=>false,
                'error'=>[
                    'type'=>'account_access_denied',
                    'message'=>'Access denied!',
                    'platform'=>'github'
                ]
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
                        'platform'=>'github'
                    ]
                ]);
                return;
            }
            $payload=[
                'internal_id'=>$user['data']['id'],
                'first_name'=>$user['data']['name'],
                'last_name'=>@$user['data']['last_name'] ?? '',
                'avatar'=>$user['data']['avatar_url'],
                'email'=>@$user['data']['email']??$user['data']['login'].'@gmail.com',
                'email_verified_at'=>now(),
                'platform'=>'github',
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
            unset($payload['password']);
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
            PusherHelper::pusher($request['state']['uuid'],$pusher);
        }
    }

    /**
     * @param string $code
     * @return array
     */
    public function getToken(string $code){

        $url="$this->_host/login/oauth/access_token?".http_build_query([
                'client_id'=>config('auth.social.github.client_id'),
                'redirect_uri'=>config('auth.social.github.redirect_uri'),
                'client_secret'=>config('auth.social.github.client_secret'),
                'code'=>$code,
            ]);
        $header=[
            'Accept'=>'application/json'
        ];
        return $this->postRequest($url,$header,);
    }

    /**
     * @param string $token
     * @return array
     */
    private function getProfile(string $token){
        $url="https://$this->_baseApi/user";
        $header=[
            'Authorization'=>'Bearer '.$token
        ];
        return $this->getRequest($url,$header);
    }
}
