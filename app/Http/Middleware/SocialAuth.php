<?php

namespace App\Http\Middleware;

use App\Helpers\Common;
use Closure;
use Illuminate\Http\Request;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;

class SocialAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token=$request->input('state');
        try {
            $payload=Common::decodeSocialAuth($token);
            $isExpire=Common::expireToken($payload['expire']);
            if($isExpire){
                return \response()->json([
                    'status'=>false,
                    'message'=>'State is expire'
                ],401);
            }
            $request['state']=(array)$payload;
        }catch (Exception $exception){
            return \response()->json([
                'status'=>false,
                'message'=>'State is invalid'
            ],401);
        }
        return $next($request);
    }
}
