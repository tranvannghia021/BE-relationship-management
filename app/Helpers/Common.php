<?php
namespace App\Helpers;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Common{
    public static function encodeJWT(array $payload) :string{
        $payload['expire']=date("Y-m-d H:i:s",time() + config('auth.key.expire'));
        return JWT::encode($payload, config('auth.key.jwt'),config('auth.key.alg'));
    }

    public static function decodeJWT(string $jwt){
        $jwt=trim(trim($jwt,'Bearer'));
        return json_decode(json_encode(JWT::decode($jwt, new Key(config('auth.key.jwt'),config('auth.key.alg')))),true);
    }

    public static function expireToken(string $time) :bool{
        return date("Y-m-d H:i:s",time()) > $time;
    }
}
