<?php

namespace App\Console\Commands;

use App\Repositories\AsiaRepository;
use Illuminate\Console\Command;

class AsiaScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asia:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan job from asia ftp.';
    protected $asiaRepository;

    /**
     * AsiaScan constructor.
     * @param AsiaRepository $asiaRepository
     */
    public function __construct(AsiaRepository $asiaRepository)
    {
        parent::__construct();

        $this->asiaRepository = $asiaRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->asiaRepository->scan();
    }
}
