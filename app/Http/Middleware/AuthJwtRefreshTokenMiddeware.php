<?php

namespace App\Http\Middleware;

use App\Helpers\Common;
use App\Repositories\UserRepository;
use Closure;
use Illuminate\Http\Request;
use Mockery\Exception;

class AuthJwtRefreshTokenMiddeware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token=$request->input('Authorization');
        if(empty($token)){
            return \response()->json([
                'status'=>false,
                'message'=>'Token refresh is required'
            ],401);
        }
        try {
            $payload=Common::decodeJWTRefreshToken($token);
            $isExpire=Common::expireToken($payload['expire']);
            if($isExpire){
                return \response()->json([
                    'status'=>false,
                    'message'=>'Token refresh is expire'
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
                'message'=>'Token refresh is invalid'
            ],401);
        }
        return $next($request);
    }
}
