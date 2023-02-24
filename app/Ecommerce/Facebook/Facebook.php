<?php
namespace App\Ecommerce\Facebook;
use App\Ecommerce\BaseApi;
use App\Helpers\Common;

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
        $payload= self::decodeState($request['state']);
        $code=$request['code'];
        $token=self::getToken($code);
        if($payload->type == 'auth'){
            //todo
            $user=self::getProfile($token->access_token);
            dd($user);
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
            'access_token'=>$token
            ]);
        return $this->getRequest($url);
    }
}
