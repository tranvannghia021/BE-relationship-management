<?php

namespace App\Http\Middleware;

use App\Helpers\Common;
use App\Repositories\UserRepository;
use Closure;
use Illuminate\Http\Request;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;

class VerifyLinkService
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token=$request->input('token');
        try {
            $payload=Common::decodeSocialAuth($token);
            $isExpire=Common::expireToken($payload['expire']);
            if($isExpire){
                return \response()->json([
                    'status'=>false,
                    'message'=>'Link is expired'
                ],401);
            }
            $users=app(UserRepository::class)->find($payload['id']);
            if(empty($users)){
                return \response()->json([
                    'status'=>false,
                    'message'=>'User not found'
                ],401);
            }
            $request['userInfo']=$users;
        }catch (Exception $exception){
            return \response()->json([
                'status'=>false,
                'message'=>'Link is invalid'
            ],401);
        }
        return $next($request);
    }
}
