<?php

namespace App\Console\Commands;

use App\Repositories\ManualJobRepository;
use Illuminate\Console\Command;

class ManualJobSendMessageMoveToDirectory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:manualdl_messageaftermovetodirectory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Message When all files with same case ID has been extracted';

    protected $manualJobRepository;

    /**
     * ManualJobSendMessageMoveToDirectory constructor.
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
        $this->manualJobRepository->send_message_when_case_is_fully_extracted_move_to_directory_of_choice();
    }
}
