<?php

namespace App\Jobs;

use App\Models\RemoteQueueActionFile;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class HandleActionFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    public $imagePath;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(RemoteQueueActionFile $imagePath)
    {
        $this->imagePath = $imagePath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $dirname = dirname($this->imagePath->destination);
        makedir($dirname, 'mkdir -p');
        chmode($dirname, 'chmod -R 777');

        if ($this->imagePath->action == 'copy') {
            copy_move($this->imagePath->source, $dirname, 'cp -r');
        } elseif ($this->imagePath->action == 'move') {
            copy_move($this->imagePath->source, $dirname, 'mv');
        }

        $this->imagePath->status = 'finish';
        $this->imagePath->updated_at = date('Y-m-d H:i:s');
        $this->imagePath->save();
    }
}
