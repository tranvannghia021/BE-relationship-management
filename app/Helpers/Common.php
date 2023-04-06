<?php
namespace App\Helpers;
use App\Repositories\Mongo\MongoBaseRepository;
use App\Repositories\Mongo\RelationshipRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Pusher\Pusher;

class Common{
    /**
     * @param string $channel
     * @param string $event
     * @param array $payload
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Pusher\ApiErrorException
     * @throws \Pusher\PusherException
     */
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

    /**
     * @param array $payload
     * @return string
     */
    public static function encodeJWT(array $payload) :string{
        $payload['expire']=date("Y-m-d H:i:s",time()  + config('auth.key.expire'));
        return JWT::encode($payload, config('auth.key.jwt'),config('auth.key.alg'));
    }

    /**
     * @param string $jwt
     * @return mixed
     */
    public static function decodeJWT(string $jwt){
        $jwt=trim(trim($jwt,'Bearer'));
        return json_decode(json_encode(JWT::decode($jwt, new Key(config('auth.key.jwt'),config('auth.key.alg')))),true);
    }

    /**
     * @param string $jwt
     * @return mixed
     */
    public static function decodeJWTRefreshToken(string $jwt){
        $jwt=trim(trim($jwt,'Bearer'));
        return json_decode(json_encode(JWT::decode($jwt, new Key(config('auth.key.jwt_private'),config('auth.key.alg')))),true);
    }

    /**
     * @param array $payload
     * @param int|null $time
     * @return string
     */
    public static function encodeJWTRefreshToken(array $payload) :string{

        $payload['expire']=date("Y-m-d H:i:s",time() + config('auth.key.expire_refresh_token'));
        return JWT::encode($payload, config('auth.key.jwt_private'),config('auth.key.alg'));
    }
    /**
     * @param array $payload
     * @return string
     */
    public static function encodeSocialAuth(array $payload,int $time=null) :string{
        $payload['expire']=date("Y-m-d H:i:s",time() + $time ?? config('auth.social.expire'));
        return JWT::encode($payload, config('auth.social.key'),config('auth.key.alg'));
    }

    /**
     * @param string $jwt
     * @return mixed
     */
    public static function decodeSocialAuth(string $jwt){
        return json_decode(json_encode(JWT::decode($jwt, new Key(config('auth.social.key'),config('auth.key.alg')))),true);
    }

    /**
     * @param string $time
     * @return bool
     */
    public static function expireToken(string $time) :bool{
        return date("Y-m-d H:i:s",time()) > $time;
    }

    /**
     * @param $request
     * @return array|bool[]
     */
    public static function handleError($request){
       if(isset($request['error'])){
           return [
               'status'=>false,
               'error'=>@$request['error'],
               'error_description'=>@$request['error_description'],
           ];
       }
       return ['status'=>true];
    }

    public static function createCollection(int $id){
        if(!app(MongoBaseRepository::class)->collectionExist($id)){
            app(MongoBaseRepository::class)->createCollection($id);
        }
    }

}
