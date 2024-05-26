<?php

namespace App\Console\Commands;

use App\Repositories\JobRepository;
use Illuminate\Console\Command;

class JobArchiveOldJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:archiveoldjobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive Old Jobs From //share/jobs folder to //share/archive folder';

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
        $this->jobRepository->archive_old_jobs();
    }
}
