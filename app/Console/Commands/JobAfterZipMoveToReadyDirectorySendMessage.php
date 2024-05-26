<?php

namespace App\Console\Commands;

use App\Repositories\JobRepository;
use Illuminate\Console\Command;

class JobAfterZipMoveToReadyDirectorySendMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:afterzip_movetojobfolderreadydirectory_send_message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Message after Move all files uploaded for caseID from temp_upload directory to jobFolder ready folder';

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
        $this->jobRepository->send_message_when_uploaded_case_is_fully_extracted_move_to_jobfolder_ready_directory();
    }
}
