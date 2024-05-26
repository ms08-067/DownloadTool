<?php

namespace App\Console\Commands;

use App\Repositories\ManualJobRepository;
use Illuminate\Console\Command;

class ManualJobExtractedCheckScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:manualdl_extractedcheckscan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Extract files with zip contents to determine if extracted fully before alerting PIC/deleting ZIP';

    protected $manualJobRepository;

    /**
     * ManualJobExtractedCheckScan constructor.
     *
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
        $this->manualJobRepository->check_extracted_files_with_zip_contents();
    }
}
