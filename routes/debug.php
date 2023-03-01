<?php
use Illuminate\Support\Facades\Route;


Route::group(['prefix'=>''],function (){
    Route::get('check',function (){
        $mysql = \Illuminate\Support\Facades\DB::connection()->getPdo();
        $redis    = \Illuminate\Support\Facades\Redis::connection()->ping('ok');
        return [
            'redis'=>$redis,
            'mysql'=>$mysql,
        ];
    });
});
