<?php

namespace App\Jobs;

use App\Helpers\PusherHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MongoDB\BSON\ObjectId;

class SendPusherNotificationLongTimeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $people,$userId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId,$item)
    {
        $this->userId=$userId;
        $this->people=$item;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data=[
            '_id'=>(string) new ObjectId($this->people['_id']),
            'full_name'=>$this->people['full_name'],
            'avatar'=>$this->people['avatar'],
            'tag'=>$this->people['tag']
        ];
        PusherHelper::pusher($this->userId,$data,'notification_');
    }
}
