<?php

namespace App\Console\Commands;

use App\Repositories\ManualJobRepository;
use Illuminate\Console\Command;

class ManualJobMoveToDirectory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:manualdl_movetodirectory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move all caseID files from unzip_folder to jobFolder';

    protected $manualJobRepository;

    /**
     * ManualJobMoveToDirectory constructor.
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
        $this->manualJobRepository->move_to_share_directory_of_choice();
    }
}
