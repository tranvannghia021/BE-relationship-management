<?php
namespace App\Ecommerce\Google;
use App\Ecommerce\BaseApi;
use App\Helpers\Common;

class Google extends BaseApi{
    protected $_baseApi,$_version;
    public function __construct()
    {
        $this->_baseApi=config('auth.social.google.base_api');
        $this->_version=config('auth.social.google.version');
        parent::__construct();
    }

    public function generateUrl(array $payload=[],$type='auth'){
        return "https://accounts.google.com/o/oauth2/v2/auth?".
            "client_id=".config('auth.social.google.client_id').
            "&redirect_uri=".config('auth.social.google.redirect_uri').
            "&state=".self::encodeState($payload,$type).
            "&scope=".self::implodeScope().
            "response_type=code";
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
        $payload= self::decodeState($request['state']);
        $code=$request['code'];
        $token=self::getToken($code);
        if($payload->type == 'auth'){
            //todo
            $user=self::getProfile($token->access_token);
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
        $url="https://people.$this->_baseApi/$this->_version/people/me?".http_build_query([
                'personFields'=>'names,emailAddresses,phoneNumbers,addresses,birthdays,organizations,urls',
            ]);
        $headers=[
            'Authorization'=>'Bearer '.$token
        ];
        return $this->getRequest($url,$headers);
    }
}
