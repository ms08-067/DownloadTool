<?php

namespace App\Console\Commands;

use App\Repositories\JobRepository;
use Illuminate\Console\Command;

class JobBackupDatabaseFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:backupdatabasefile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make Backup Database File';

    protected $jobRepository;

    /**
     * S3JobUnzip constructor.
     *
     * @param JobRepository $jobRepository
     */
    public function __construct(JobRepository $jobRepository)
    {
        parent::__construct();

        $this->jobRepository = $jobRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->jobRepository->make_database_file_backup();
    }
}
