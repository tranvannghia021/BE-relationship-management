<?php
namespace App\Traits;
trait Response{
    public function ApiResponse($data =[], $message = 'Success',$code = 200){
        return response()->json([
            'status'=>true,
            'message'=>$message,
            'data'=>$data,
        ],$code);
    }
    public function ApiResponseError($error,$code=400){
        return response()->json([
            'status'=>false,
            'error'=>$error,
            'message'=>'Error,Try again'
        ],$code);
    }
}
