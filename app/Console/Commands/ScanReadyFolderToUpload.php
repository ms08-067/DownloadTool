<?php

namespace App\Console\Commands;

use App\Repositories\JobRepository;
use Illuminate\Console\Command;

class ScanReadyFolderToUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan Ready Folder To Upload.';
    protected $jobRepository;

    /**
     * UploadScan constructor.
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
        $this->jobRepository->scanReadyFolderToUpload();
    }
}
