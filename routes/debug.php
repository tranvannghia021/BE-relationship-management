<?php

use App\Repositories\AppointmentRepository;
use App\Repositories\Mongo\RelationshipRepository;
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

    $people=app(RelationshipRepository::class)->setCollection(1)->
    getUserLongTimeBySetting(2);
    $ids=\Illuminate\Support\Arr::pluck($people,'_id');
    $temp=[];
    foreach ($ids as $id){

        $temp[]=[
            '_id'=>(string)new \MongoDB\BSON\ObjectId($id),
             'is_notification'=>false
        ];
    }

    $appointments= app(RelationshipRepository::class)->setCollection(1)->update($temp);
    dd($appointments);
});

