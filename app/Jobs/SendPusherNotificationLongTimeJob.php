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
            'type'=>'long_time',
            'info'=>$this->people,
            'title'=>"Have you had a friend for so long?",
            'created_at'=> $this->people['time_created_at']
        ];
        PusherHelper::pusher($this->userId,$data,'notification_');
    }
}
