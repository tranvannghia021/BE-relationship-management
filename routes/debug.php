<?php

use App\Jobs\SendPusherNotificationLongTimeJob;
use App\Jobs\SendPusherNotificationReadyTimeMeetJob;
use App\Repositories\AppointmentRepository;
use App\Repositories\Mongo\RelationshipRepository;
use App\Repositories\UserRepository;
use App\Services\NotificationService;
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

Route::get('notification-long-time',function (){
    $users=app(UserRepository::class)->getAll();

    if (!empty($users)){
        foreach ($users->toArray() as $user){
            $people=app(RelationshipRepository::class)->setCollection($user['id'])->
            getUserLongTimeBySettingTEST($user['settings']['user_long_time']);

            if(!empty($people)){
                $ids=[];
                foreach ($people as $item){
                    $item['_id']=(string)new \MongoDB\BSON\ObjectId($item['_id']);
                    $item['time_created_at']=\Illuminate\Support\Carbon::now()->toDateTimeString();
                    $ids[]=[
                        '_id'=>$item['_id'],
                        'is_notification'=>true
                    ];
                    app(NotificationService::class)->create([
                        'shop_id'=>$user['id'],
                        'link'=>$item['_id']."_people",
                        'type'=>'long_time',
                        'info'=>$item,
                        'title'=>"Have you had a friend for so long?",
                        'created_at'=> $item['time_created_at']
                    ]);

                    SendPusherNotificationLongTimeJob::dispatch($user['id'],$item)->onQueue('notification-long-time');
                }
//                app(RelationshipRepository::class)->setCollection($user['id'])->update($ids);
            }
        }
    }
    dd('ok');
});
Route::get('notification-ready-time',function (){
    $users=app(UserRepository::class)->getAll();
    if(!empty($users)){
        foreach ($users as $user){
            $appointments= app(AppointmentRepository::class)->getUserReadyTimeBySettingTEST($user['settings']['ready_time_appointment']);
            if(!empty($appointments)){
                foreach ($appointments as $appointment){
                    $appointment['time_created_at']=Carbon::now()->toDateTimeString();
                    app(NotificationService::class)->create([
                        'shop_id'=>$user['id'],
                        'link'=>$appointment['id']."_appointment",
                        'type'=>'ready_time',
                        'info'=>$appointment,
                        'title'=>$appointment['name'],
                        'created_at'=>$appointment['time_created_at']
                    ]);
                    SendPusherNotificationReadyTimeMeetJob::dispatch($user['id'],$appointment)->onQueue('notification-ready-time');
                }
//                app(AppointmentRepository::class)->whereInUpdateIsNotification(\Illuminate\Support\Arr::pluck($appointments,'id'));
            }
        }
    }
    dd('ok');
});

Route::get('test',function (){

});

