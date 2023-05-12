<?php

namespace App\Jobs;

use App\Services\AppointmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrtherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data,$function;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data,$function)
    {
        $this->data=$data;
        $this->function=$function;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app(AppointmentService::class)->{$this->function}($this->data['user_id'],$this->data['relationship_ids']);
    }
}
