<?php
namespace App\Traits;
trait Response{
    /**
     * @param $data
     * @param $message
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function ApiResponse($data =[], $message = 'Success',$code = 200){
        return response()->json([
            'status'=>true,
            'message'=>$message,
            'data'=>$data,
        ],$code);
    }

    /**
     * @param $error
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function ApiResponseError($error,$code=400){
        return response()->json([
            'status'=>false,
            'error'=>$error,
            'message'=>'Error,Try again'
        ],$code);
    }
}
