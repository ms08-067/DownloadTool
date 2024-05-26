<?php

namespace App\Console\Commands;

use App\Repositories\ManualS3Repository;
use Illuminate\Console\Command;

class ManualS3JobScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 's3:manualdl_scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[Manual DL] Scan s3 folder job to download.';
    protected $manuals3Repository;

    /**
     * ManualS3JobScan constructor.
     *
     * @param ManualS3Repository $manuals3Repository
     */
    public function __construct(ManualS3Repository $manuals3Repository)
    {
        parent::__construct();

        $this->manuals3Repository = $manuals3Repository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->manuals3Repository->scan();
    }
}
