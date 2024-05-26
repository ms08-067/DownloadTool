<?php

namespace App\Console\Commands;

use App\Models\RemoteQueueThumbnail;
use App\Models\RemoteSetting;
use Illuminate\Console\Command;
use App\Models\RemoteJob;

class CreateThumbnail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:thumbnail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create thumbnail';

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
        $total_other_jobs = RemoteJob::where('queue', '<>', 'default')->count();
        if ($total_other_jobs > 0) {
            return;
        }

        $settings = RemoteSetting::whereIn('code', array('queue_thumbnail_amount', 'queue_thumbnail_size', 'queue_thumbnail_photoshop'))
        ->select('code', 'value')->get()->toArray();
        if (empty($settings)) {
            return;
        }
        $maxAmount = 10;
        $maxSize = 1000;/**MB*/
        $photoshopExtensions = array();
        foreach ($settings as $setting) {
            if ($setting['code'] == 'queue_thumbnail_amount') {
                $maxAmount = $setting['value'];
            }
            if ($setting['code'] == 'queue_thumbnail_size') {
                $maxSize = $setting['value'];
            }
            if ($setting['code'] == 'queue_thumbnail_photoshop') {
                $photoshopExtensions = $setting['value'];
            }
        }
        $total_queue = RemoteQueueThumbnail::where('status', 'queue')->count();
        if ($total_queue >= $maxAmount) {
            return;
        }

        $maxSize = $maxSize * 1024 * 1024; /**Byte*/
        $imagePaths = RemoteQueueThumbnail::where('status', 'new')->get();

        $queueImages = array();
        $index = 0;
        $totalSize = 0;
        foreach ($imagePaths as $path) {
            $index ++;
            $totalSize += 1*$path->size;
            if ($index > $maxAmount) {
                \App\Jobs\CreateThumbnail::dispatch($queueImages, $photoshopExtensions);
                $queueImages = array();
                $index = 0;
                $totalSize = 0;
            }
            if ($totalSize > $maxSize) {
                if (count($queueImages) == 0) {
                    $path->status = 'queue';
                    $path->save();
                    $queueImages[] = $path;
                }
                \App\Jobs\CreateThumbnail::dispatch($queueImages, $photoshopExtensions);
                $queueImages = array();
                $index = 0;
                $totalSize = 0;
            }
            $path->status = 'queue';
            $path->save();
            $queueImages[] = $path;
        }
        if (count($queueImages) > 0) {
            \App\Jobs\CreateThumbnail::dispatch($queueImages, $photoshopExtensions);
        }
    }
}
