<?php

namespace App\Console\Commands;

use App\Repositories\AppointmentRepository;
use Illuminate\Console\Command;

class AutoUpdateStatusAppointmentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointment:auto-update-status';

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
        app(AppointmentRepository::class)->getAllAutoUpdateStatus();
    }
}
