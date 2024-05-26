<?php

namespace App\Console\Commands;

use App\Repositories\JobRepository;
use Illuminate\Console\Command;

class JobFinish extends Command
{
    public $jobRepository;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:finish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check finish job.';

    /**
     * Create a new command instance.
     *
     * UploadJob constructor.
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
        $this->jobRepository->finishJob();
    }
}
