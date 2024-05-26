<?php

namespace App\Console\Commands;

use App\Repositories\TeambitionRepository;
use Illuminate\Console\Command;

class TeambitionCheckDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teambition:check_download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check download job from teambition';
    protected $teambitionRepository;

    /**
     * Teambition constructor.
     * @param TeambitionRepository $teambitionRepository
     */
    public function __construct(TeambitionRepository $teambitionRepository)
    {
        parent::__construct();

        $this->teambitionRepository = $teambitionRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->teambitionRepository->checkDownload();
    }
}
