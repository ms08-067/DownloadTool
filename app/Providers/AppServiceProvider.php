<?php

namespace App\Providers;

use App\Models\TasksFiles;
use App\Repositories\S3Repository;
use App\Repositories\UploadRepository;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

use Laravel\Sanctum\Sanctum;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobProcessed;

use Exception;
use Debugbar;
use Carbon\Carbon;
use URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Sanctum::ignoreMigrations();
        
        /** Check environment for service provider*/
        if (config('app.debug') && in_array($this->app->environment(), ['dev', 'test'])) {
            array_map([$this->app, 'register'], config('app.devProviders'));
        }

        $this->registerModelFactoriesFrom(database_path('factories'));
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if(env('APP_ENV') == 'prod'){
            if(strpos(URL::current(), 'http://dus.hanoi.br24.vn') !== false && isset($_SERVER["HTTP_USER_AGENT"]) && strpos($_SERVER["HTTP_USER_AGENT"], 'Firefox') !== false) {
                URL::forceScheme('https');
            }else{
                if (strpos(env('APP_URL'), 'https://') !== false) {
                    URL::forceScheme('https');
                }else{
                    URL::forceScheme('http');
                }
            }
        }else{
            if(strpos(URL::current(), 'http://dus-develop.hanoi.br24.vn') !== false && isset($_SERVER["HTTP_USER_AGENT"]) && strpos($_SERVER["HTTP_USER_AGENT"], 'Firefox') !== false) {
                URL::forceScheme('https');
            }else{
                if (strpos(env('APP_URL'), 'https://') !== false) {
                    URL::forceScheme('https');
                }else{
                    URL::forceScheme('http');
                }
            }
        }

        if (DB::Connection() instanceof \Illuminate\Database\SQLiteConnection) {
            DB::connection()->getPdo()->sqliteCreateFunction('REGEXP', function ($pattern, $value) {
                mb_regex_encoding('UTF-8');
                return (false !== mb_ereg($pattern, $value)) ? 1 : 0;
            });
        }

        /**to do additional things (logging or incremental statistics for a dashboard) before processing a job*/
        Queue::before(function (JobProcessing $event) {
            /** $event->connectionName*/
            /** $event->job*/
            /** $event->job->payload()*/
        });
        /**to do additional things (logging or incremental statistics for a dashboard) after processing a job*/
        Queue::after(function (JobProcessed $event) {
            /** $event->connectionName*/
            /** $event->job*/
            /** $event->job->payload()*/
            $payload = $event->job->payload();
            if(isset($payload['data']["command"]) && strpos($payload['data']["command"], 'LaravelWebSockets') !== false) {
                /** don't log these types of worker jobs to the done_jobs table */
            }else{
                DB::beginTransaction();
                try {
                    DB::table('done_jobs')->insert([
                        'displayName' => json_encode($payload['data']),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Debugbar::addException($e);
                    return false;
                }
            }
        });

        /**if you want to be alerted when a job has failed can send an email from here*/
        Queue::failing(function (JobFailed $event) {
            /** $event->connectionName*/
            /** $event->job*/
            /** $event->exception*/
        });

        /**Using the looping method on the Queue facade, you may specify callbacks that execute before the worker attempts to fetch a job from a queue. */
        /**For example, you might register a Closure to rollback any transactions that were left open by a previously failed job:*/
        Queue::looping(function () {
            // while (DB::transactionLevel() > 0) {
            //     DB::rollBack();
            // }
        });
    }

    /**
     * Register factories.
     *
     * @param  string  $path
     * @return void
     */
    protected function registerModelFactoriesFrom($path)
    {
        /**$this->app->make(ModelFactory::class)->load($path);*/
        /***/
    }
}
