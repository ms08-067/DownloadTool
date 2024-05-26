<?php

namespace App\Console\Commands;

use App\Repositories\OocvRepository;
use Illuminate\Console\Command;

class OocvFinish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oocv:finish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xóa job đã finsh trong thư mục tmp và chuyển job vào thư mục done trên Oocv. (Delete the finsh job in the tmp directory and move the job to the done directory on Oocv.)';

    protected $oocvRepository;

    /**
     * OocvFinish constructor.
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
//        $this->oocvRepository->oocvFinish();
    }
}
