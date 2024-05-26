<?php

namespace App\Console\Commands;

use App\Models\OocvScan;
use App\Repositories\OocvRepository;
use Illuminate\Console\Command;

class OocvFixDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oocv:fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix download error';

    protected $oocvRepository;

    /**
     * OocvFixDownload constructor.
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
        $this->oocvRepository->oocvFixDownload();
    }
}
