<?php

namespace App\Providers;

use App\Models\TaskDownload;
use App\Observers\taskDownloadsObserver;
use App\Models\TaskUpload;
use App\Observers\taskUploadsObserver;

use App\Models\TaskManualDownload;
use App\Observers\taskManualDownloadsObserver;
use App\Models\TaskManualUpload;
use App\Observers\taskManualUploadsObserver;


use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        TaskDownload::observe(taskDownloadsObserver::class);
        TaskUpload::observe(taskUploadsObserver::class);
        
        TaskManualDownload::observe(taskManualDownloadsObserver::class);
        TaskManualUpload::observe(taskManualUploadsObserver::class);

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
