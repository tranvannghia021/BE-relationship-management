<?php
use \Illuminate\Support\Facades\Route;

Route::group(['prefix'=>''],function (){
    Route::get('check',function (){
        $postgres = \Illuminate\Support\Facades\DB::connection()->getPdo();
        $mongodb = \Illuminate\Support\Facades\DB::connection('mongodb')->getMongoClient()->listDatabases();
        $redis    = \Illuminate\Support\Facades\Redis::connection()->ping('ok');
        return [
            'redis'=>$redis,
            'postgres'=>$postgres,
            'mongodb'=>$mongodb
        ];
    });
});
Route::get('php', function () {
    phpinfo();
});


