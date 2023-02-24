<?php
namespace App\Helpers;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Pusher\Pusher;

class Common{

   public static function pushSocket(string $channel, string $event, array $payload)
    {
        $pusher = new Pusher(
            config('services.pusher.key'),
            config('services.pusher.secret'),
            config('services.pusher.app_id'),
            config('services.pusher.options')
        );
        $pusher->trigger($channel, $event, $payload);
    }

    public static function encodeJWT(array $payload) :string{
        $payload['expire']=date("Y-m-d H:i:s",time() + config('auth.key.expire'));
        return JWT::encode($payload, config('auth.key.jwt'),config('auth.key.alg'));
    }

    public static function decodeJWT(string $jwt){
        $jwt=trim(trim($jwt,'Bearer'));
        return json_decode(json_encode(JWT::decode($jwt, new Key(config('auth.key.jwt'),config('auth.key.alg')))),true);
    }

    public static function encodeSocialAuth(array $payload) :string{
        $payload['expire']=date("Y-m-d H:i:s",time() + config('auth.social.expire'));
        return JWT::encode($payload, config('auth.social.key'),config('auth.key.alg'));
    }

    public static function decodeSocialAuth(string $jwt){
        return json_decode(json_encode(JWT::decode($jwt, new Key(config('auth.social.key'),config('auth.key.alg')))),true);
    }

    public static function expireToken(string $time) :bool{
        return date("Y-m-d H:i:s",time()) > $time;
    }
}
