<?php

namespace App\Console\Commands;

use App\Repositories\JobRepository;
use Illuminate\Console\Command;

class JobUploadReady extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:upload_ready';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload Ready Zip to s3';

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
        $this->jobRepository->send_the_ready_zip_to_s3();
    }
}
