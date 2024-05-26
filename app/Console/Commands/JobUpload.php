<?php

namespace App\Console\Commands;

use App\Models\RemoteMappingName;
use App\Repositories\UploadRepository;
use FtpClient\FtpClient;
use Illuminate\Console\Command;

class JobUpload extends Command
{
    public $uploadRepository;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:upload {server?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload Job to the customer\'s server.';

    /**
     * Create a new command instance.
     *
     * UploadJob constructor.
     * @param UploadRepository $uploadRepository
     */
    public function __construct(UploadRepository $uploadRepository)
    {
        parent::__construct();

        $this->uploadRepository = $uploadRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $server = $this->argument('server');
        $this->uploadRepository->uploadServer($server);
    }
}
