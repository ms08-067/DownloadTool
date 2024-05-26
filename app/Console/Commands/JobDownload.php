<?php

namespace App\Console\Commands;

use App\Repositories\JobRepository;
use Illuminate\Console\Command;

class JobDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download job from s3 or asia ftp.';

    protected $jobRepository;

    /**
     * S3JobDownload constructor.
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
        $this->jobRepository->download();
    }
}
