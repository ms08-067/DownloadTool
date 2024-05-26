<?php

namespace App\Console\Commands;

use App\Models\RemoteQueueActionFile;
use App\Models\RemoteSetting;
use Illuminate\Console\Command;
use App\Models\RemoteJob;
use DB;

class HandleActionFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'handle:action_file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle action file';

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
        $imagePaths = RemoteQueueActionFile::with(['task'])->where('status', 'new')->get();

        $now = date('Y-m-d H:i:s');
        $normals = [];
        foreach ($imagePaths as $path) {
            if (empty($path->task)) {
                $path->status = 'error';
                $path->updated_at = $now;
                $path->save();

                continue;
            }
            $path->status = 'queue';
            $path->updated_at = $now;
            $path->save();
            
            /**
             * // if ($path->task->isExpress == 1) {
             * //     $dirname = dirname($path->destination);
             * //     makedir($dirname, 'mkdir -p');
             * //     chmode($dirname, 'chmod -R 777');
             * //     if ($path->action == 'copy') {
             * //         copy_move($path->source, $dirname, 'cp -r', '> /dev/null &');
             * //     } elseif ($path->action == 'move') {
             * //         copy_move($path->source, $dirname, 'mv', '> /dev/null &');
             * //     }
             * //     $path->status = 'finish';
             * //     $path->save();
             * //     continue;
             * // } 
             */

            if ($path->task->vip_job == 1 || $path->task->isExpress == 1) {
                \App\Jobs\HandleActionFile::dispatch($path)->onQueue('handle_action_vip_file');
            } else {
                $normals[] = $path;
            }
        }
        foreach($normals as $path) {
            \App\Jobs\HandleActionFile::dispatch($path)->onQueue('handle_action_file');
        }
    }
}
