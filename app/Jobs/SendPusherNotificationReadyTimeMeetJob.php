<?php

namespace App\Jobs;

use App\Helpers\PusherHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class SendPusherNotificationReadyTimeMeetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $userId,$appointment;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId,$appointment)
    {
        $this->userId=$userId;
        $this->appointment=$appointment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $data=[
            'type'=>'ready_time',
            'info'=>[],
            'title'=>$this->appointment['name'],
            'created_at'=>$this->appointment['time_created_at']
        ];
        PusherHelper::pusher($this->userId,$data,'notification_');
    }
}
