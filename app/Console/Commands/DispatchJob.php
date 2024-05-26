<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DispatchJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:dispatch {job} {caseid} {folder_type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch job';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $class = '\\App\\Jobs\\' . $this->argument('job');
        $class::dispatch($this->argument('caseid'), $this->argument('folder_type'));
        /**dispatch(new $class($this->argument('caseid'), $this->argument('folder_type')));*/
    }
}
