<?php

use App\Repositories\UserRepository;
use Illuminate\Support\Carbon;
use \Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

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
Route::get('/debug-sentry', function () {
    throw new Exception('My first Sentry error!');
});
Route::put('trigger',function (){
    $validate =\Illuminate\Support\Facades\Validator::make(\request()->all(),[
        'email'=>'required'
    ]);
    if($validate->fails()){
        return $validate->errors()->messages();
    }
    $email =\request()->input('email');
    $res=app(\App\Repositories\UserRepository::class)->updateBy([
        'email'=>$email,
        'platform'=>config('auth.platform_app')
    ],
        [
        'email_verified_at'=>now()
        ]);

    return ['status'=>true,'data'=>$res];
});

Route::patch('migrate',function (){
    $request =\request();


    $path= $request->input('path');
   if(empty($path)){
       \Illuminate\Support\Facades\Artisan::call('migrate:refresh');

   }else{
       \Illuminate\Support\Facades\Artisan::call('migrate --path=/app/database/migrations/'.$path);
   }
       return ['status'=>true];

});

Route::get('name-migrate',function (){
    $files=scandir('../database/migrations/');
    return $files;
});

Route::put('drop/table',function (){
    $request =\request();
    $validate = \Illuminate\Support\Facades\Validator::make($request->all(),[
        'name'=>'required'
    ]);
    if($validate->fails()){
        return $validate->errors()->messages();
    }
    $table =$request->input('name');
    Schema::dropIfExists($table);
    return ['status'=>true];
});
Route::get('env',function (){
    $fileName=\request()->input('name','app');
    dd(config($fileName));
});
Route::get('run-add',function (){
    try {
        Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->jsonb('settings')->after('status')->default(json_encode([
                'user_long_time'=>7,
                'ready_time_appointment'=>1
            ]));
        });
        Schema::table('appointment', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->enum('status',['coming','done','cancel'])->default('coming');
        });
        return true;
    }catch (\Exception $exception){
        return $exception->getMessage();
    }
});

Route::get('test',function (){
    try {
        $users=app(UserRepository::class)->getAllUserLongTime();
        $currenDate= \Carbon\Carbon::now()->toDateTimeString();
        foreach ($users as $user){
            if($user['user_long_time'] < $currenDate){
                echo "asdasd";
            }
        }
        dd($users);
    }catch (\Exception $exception){
        dd($exception->getMessage());
    }
});

