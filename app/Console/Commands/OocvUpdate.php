<?php

namespace App\Console\Commands;

use App\Repositories\OocvRepository;
use Illuminate\Console\Command;

class OocvUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oocv:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update when file downloaded';

    protected $oocvRepository;

    /**
     * OocvUpdate constructor.
     * @param OocvRepository $oocvRepository
     */
    public function __construct(OocvRepository $oocvRepository)
    {
        parent::__construct();

        $this->oocvRepository = $oocvRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->oocvRepository->oocvUpdate();
    }
}

