<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Services\Taskers\JobUserTaskerService;
use Log;

class JobUsersQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queuejob:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get users from queue and collect then in the github api';

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
     * @return mixed
     */
    public function handle()
    {
        $task = new JobUserTaskerService();
        $task->execute();

        Log::info("Executing queuejob:users");
    }
}
