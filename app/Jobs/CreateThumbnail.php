<?php

namespace App\Jobs;

use App\Models\Example;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateThumbnail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    public $imagePaths;

    public $photoshopExtensions;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($imagePaths, $photoshopExtensions)
    {
        $this->imagePaths = $imagePaths;
        $this->photoshopExtensions = explode(',', $photoshopExtensions);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->imagePaths as $path) {
            $dirname = dirname($path->destination);
            $command = "mkdir -p " . $dirname;
            exec($command);
            $command = "chmod -R 777 " . $dirname;
            exec($command);
            $size = 1*$path->size*1024*1024; //MB
            if (in_array($path->extension, $this->photoshopExtensions)) {
                if ($size >= 100) {
                    $command = "exiftool -Photoshop:PhotoshopThumbnail -b -resize '{$path->path}' > '{$path->destination}'";
                } else {
                    /**$command = "convert '{$path->path}' -flatten -thumbnail 100x70^ -gravity center -crop 100x70+0+0 '{$path->destination}' > /dev/null &";*/
                    $command = "convert -resize 100 '{$path->path}' '{$path->destination}'";
                }
            } else {
                /**$command = "convert '{$path->path}' -flatten -thumbnail 100x70^ -gravity center -crop 100x70+0+0 '{$path->destination}' > /dev/null &";*/
                $command = "convert -resize 100 '{$path->path}' '{$path->destination}'";
            }
            exec($command);
            if (strpos($command, '@ error') !== false) {
                $path->status = 'error';
            } else {
                $path->status = 'finish';
            }
            $path->save();
        }
    }
}
