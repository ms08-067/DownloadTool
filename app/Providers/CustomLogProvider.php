<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CustomLogProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('customlog', function () {
            return new \App\Facades\CustomLog;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
