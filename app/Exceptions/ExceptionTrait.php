<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


trait ExceptionTrait
{
    /**
     * @param $request
     * @param $e
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function apiException($request,$e){


        if($this->isModel($e)){

            return $this->modelResponse();
        }
        if($this->isHttp($e)){


            return $this->httpResponse();
        }
        if($this->isToken($e)){

            return $this->tokenResponse();
        }
        if($this->isMethod($e)){

            return $this->methodResponse();
        }
    }

    /**
     * @param $e
     * @return bool
     */
    public function isModel($e){

        return $e instanceof ModelNotFoundException;
    }

    /**
     * @param $e
     * @return bool
     */
    public function isHttp($e){

        return $e instanceof NotFoundHttpException;
    }

    /**
     * @param $e
     * @return bool
     */
    public function isToken($e){

        return $e instanceof AuthenticationException;
    }

    /**
     * @param $e
     * @return bool
     */
    public function isMethod($e){

        return $e instanceof MethodNotAllowedHttpException;
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function modelResponse(){

        return response()->json([
            'status'=>false,
            'message'=>'Model not found!'
        ],Response::HTTP_NOT_FOUND);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function httpResponse(){

        return response()->json([
            'status'=>false,
            'message'=>'Incorect route!'
        ],Response::HTTP_NOT_FOUND);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function tokenResponse(){

        return response()->json([
            'status'=>false,
            'message'=>'Unauthorized!'
        ],Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function methodResponse(){

        return response()->json([
            'status'=>false,
            'message'=> 'The GET method is not supported for this route!'
        ],Response::HTTP_METHOD_NOT_ALLOWED);
    }

}
