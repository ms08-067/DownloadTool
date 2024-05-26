<?php

namespace App\Console\Commands;

use App\Repositories\JobRepository;
use Illuminate\Console\Command;

class JobMoveToDirectory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:movetodirectory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move all caseID files from unzip_folder to jobFolder';

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
        $this->jobRepository->move_to_share_directory_of_choice();
    }
}
