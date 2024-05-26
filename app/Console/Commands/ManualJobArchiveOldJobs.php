<?php

namespace App\Console\Commands;

use App\Repositories\ManualJobRepository;
use Illuminate\Console\Command;

class ManualJobArchiveOldJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:manualdl_archiveoldjobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[Manual DL] Delete Old Jobs From //share/manual 30 days old reflect on DB';

    protected $manualJobRepository;

    /**
     * ManualJobArchiveOldJobs constructor.
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
        $this->manualJobRepository->archive_old_jobs();
    }
}
