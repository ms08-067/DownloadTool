<?php
/**
 *  @author anhlx412@gmail.com
 */

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UploadToServerGermany extends UploadToServer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $file;
    protected $repository;

    /**
     * Create a new job instance.
     *
     * UploadToServerOtto constructor.
     * @param $repository
     * @param $file
     */
    public function __construct($repository, $file)
    {
        $this->file = $file;
        $this->repository = $repository;
    }

    /**
     * Execute the job.
     *
     */
    public function handle()
    {
        $this->repository->s3Repository->uploadGermany($this->file);
    }

    /**
     * The job failed to process.
     *
     * @param \Exception $exception
     */
    public function failed(\Exception $exception)
    {
        $this->repository->s3Repository->uploadFail($this->file);
    }
}
