<?php

namespace App\Console\Commands;

use App\Repositories\OocvRepository;
use Illuminate\Console\Command;

class OocvDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oocv:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Oocv Download.';

    protected $oocvRepository;

    /**
     * OocvDownload constructor.
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
        $this->oocvRepository->oocvDownload();
    }
}
