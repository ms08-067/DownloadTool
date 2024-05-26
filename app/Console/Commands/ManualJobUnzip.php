<?php

namespace App\Console\Commands;

use App\Repositories\ManualJobRepository;
use Illuminate\Console\Command;

class ManualJobUnzip extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:manualdl_unzip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[Manual DL] Unzip actually the zips part of the caseID.';

    protected $manualJobRepository;

    /**
     * ManualJobUnzip constructor.
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
        $this->manualJobRepository->actually_unzip();
    }

    // function getDirContents($dir, &$results = array())
    // {
    //     $files = scandir($dir);

    //     foreach ($files as $key => $value) {
    //         $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
    //         if (!is_dir($path)) {
    //             $results[] = $path;
    //         } else if ($value != "." && $value != "..") {
    //             self::getDirContents($path, $results);
    //             $results[] = $path;
    //         }
    //     }
    //     return $results;
    // }
}
