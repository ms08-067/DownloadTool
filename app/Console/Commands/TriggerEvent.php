<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Events\NewAutoDLJobData;

use Carbon\Carbon;
use App\Helpers;
use Exception;
use Debugbar;
use Artisan;
use Loggy;

class TriggerEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'irregularly:trigger_event';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testing Web Socket event trigger';

    /**
     * Create new command instance.
     * @param DoorEntrySystemRepository $doorentrysystemRepo
     * @param TimeEntryRepository $timeentryRepo
     * @param EmployeeRepository $employeeRepo
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
        event(new NewAutoDLJobData(rand()));
    }
}
