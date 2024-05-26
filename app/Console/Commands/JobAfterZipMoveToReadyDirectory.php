<?php

namespace App\Console\Commands;

use App\Repositories\JobRepository;
use Illuminate\Console\Command;

class JobAfterZipMoveToReadyDirectory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:afterzip_movetojobfolderreadydirectory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move all files uploaded for caseID from temp_upload directory to jobFolder ready folder';

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
        $this->jobRepository->after_zip_move_from_temp_upload_directory_to_jobfolder();
    }
}
