<?php

namespace App\Console\Commands;

use App\Jobs\SendPusherNotificationLongTimeJob;
use App\Jobs\SendPusherNotificationReadyTimeMeetJob;
use App\Repositories\AppointmentRepository;
use App\Repositories\Mongo\NotificationRepository;
use App\Repositories\Mongo\RelationshipRepository;
use App\Repositories\UserRepository;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ReadyTimeAppointmentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointment:ready-time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $users=app(UserRepository::class)->getAllUserReadyTime();
           if(!empty($users)){
               foreach ($users as $user){
                  $appointments= app(AppointmentRepository::class)->getUserReadyTimeBySetting($user['ready_time_appointment']);
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
                       app(AppointmentRepository::class)->whereInUpdateIsNotification(\Illuminate\Support\Arr::pluck($appointments,'id'));
                   }
               }
           }
        }catch (\Exception $exception){
            throw $exception;
        }

    }
}
