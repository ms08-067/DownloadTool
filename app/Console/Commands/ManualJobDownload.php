<?php

namespace App\Console\Commands;

use App\Repositories\ManualJobRepository;
use Illuminate\Console\Command;

class ManualJobDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:manualdl_download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[Manual DL] Download job from s3 or asia ftp.';

    protected $manualJobRepository;

    /**
     * ManualJobDownload constructor.
     * @param ManualJobRepository $manualJobRepository
     */
    public function __construct(ManualJobRepository $manualJobRepository)
    {
        parent::__construct();

        $this->manualJobRepository = $manualJobRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->manualJobRepository->download();
    }
}
