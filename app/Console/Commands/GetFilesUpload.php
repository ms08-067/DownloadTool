<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GetFilesUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get total files upload to s3';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tasks = Task::where('status', 4)->where('is_temp_stop', 0)->where('deliveryProduction', '>=', '2018-07-01 00:00:00')->where('deliveryProduction', '<=', '2018-07-31 23:59:59')->get();
        $s3 = Storage::disk('s3');

        $total = 0;
        $text = "case id \t\t delivery date \t\t total file";
        dump($text);
        Storage::append('file.log', $text);

        foreach ($tasks as $task) {
            $readyDir = 'br24/Jobs/' . $task->case_id . '/ready/';
            $xlmFiles = $s3->allFiles($readyDir);
            $number = 0;
            foreach ($xlmFiles as $file) {
                if (in_array(\File::extension($file), config('br24config.file_not_scan'))) {
                    continue;
                }

                $number++;
            }

            $total += $number;
            $text = $task->case_id . "\t\t" . $task->deliveryProduction . "\t\t" . $number;
            dump($text);
            Storage::append('file.log', $text);
        }

        dump('total: ' . $total);
        Storage::append('file.log', 'total: ' . $total);

    }
}
