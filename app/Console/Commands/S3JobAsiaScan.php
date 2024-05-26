<?php

namespace App\Console\Commands;

use App\Repositories\S3Repository;
use Illuminate\Console\Command;

class S3JobAsiaScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 's3:scanAsia';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan s3 folder job asia to download.';
    protected $s3Repository;

    /**
     * S3JobScan constructor.
     *
     * @param S3Repository $s3Repository
     */
    public function __construct(S3Repository $s3Repository)
    {
        parent::__construct();

        $this->s3Repository = $s3Repository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->s3Repository->scanAsia();
    }
}
