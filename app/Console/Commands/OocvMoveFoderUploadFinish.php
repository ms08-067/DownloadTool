<?php

namespace App\Console\Commands;

use App\Repositories\OocvRepository;
use Illuminate\Console\Command;

class OocvMoveFoderUploadFinish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oocv:move-to-final';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move folder finish from temp to final folder.';
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
        $this->oocvRepository->oocvMoveToFinal();
    }
}
