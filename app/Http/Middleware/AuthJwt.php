<?php

namespace App\Http\Middleware;

use App\Helpers\Common;
use App\Repositories\UserRepository;
use Closure;
use Illuminate\Http\Request;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;
use const Grpc\STATUS_OK;

class AuthJwt
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token=$request->header('Authorization');
        if(empty($token)){
            return \response()->json([
                'status'=>false,
                'message'=>'Token is required'
            ],401);
        }
        try {
            $payload=Common::decodeJWT($token);
            $isExpire=Common::expireToken($payload['expire']);
            if($isExpire){
                return \response()->json([
                    'status'=>false,
                    'message'=>'Token is expire'
                ],401);
            }
            $user=app(UserRepository::class)->find($payload['id']);
            if(empty($user)){
                return \response()->json([
                    'status'=>false,
                    'message'=>'User not found'
                ],401);
            }
            unset($user['password']);
            $request['userInfo']=$user;
        }catch (\Exception $exception){
            return \response()->json([
                'status'=>false,
                'message'=>'Token is invalid'
            ],401);
        }
        return $next($request);
    }
}
