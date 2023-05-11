<?php

namespace App\Console\Commands;

use App\Jobs\SendPusherNotificationLongTimeJob;
use App\Repositories\Mongo\RelationshipRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class UserLongTimeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:long-time';

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
            $users=app(UserRepository::class)->getAllUserLongTime();
            if (!empty($users)){
                foreach ($users as $user){
                    $people=app(RelationshipRepository::class)->setCollection($user['id'])->
                    getUserLongTimeBySetting($users['user_long_time']);
                    if(!empty($people)){
                        $ids=[];
                        foreach ($people as $item){
                            $ids[]=[
                                '_id'=>(string)new \MongoDB\BSON\ObjectId($item['_id']),
                                'is_notification'=>true
                            ];
                            SendPusherNotificationLongTimeJob::dispatch($user['id'],$item)->onQueue('notification-long-time');
                        }
                        app(RelationshipRepository::class)->setCollection($user['id'])->update($ids);
                    }
                }
            }
        }catch (\Exception $exception){
            throw $exception;
        }
    }
}
