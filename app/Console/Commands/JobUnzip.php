<?php

namespace App\Console\Commands;

use App\Repositories\JobRepository;
use Illuminate\Console\Command;

class JobUnzip extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:unzip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unzip actually the zips part of the caseID.';

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
        $this->jobRepository->actually_unzip();
    }

    // function getDirContents($dir, &$results = array())
    // {
    //     $files = scandir($dir);

    //     foreach ($files as $key => $value) {
    //         $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
    //         if (!is_dir($path)) {
    //             $results[] = $path;
    //         } else if ($value != "." && $value != "..") {
    //             self::getDirContents($path, $results);
    //             $results[] = $path;
    //         }
    //     }
    //     return $results;
    // }
}
