<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Console\Commands\CreateThumbnail;
use App\Console\Commands\GetFilesUpload;
use App\Console\Commands\JobFinish;
use App\Console\Commands\JobUpload;
use App\Console\Commands\OocvDownload;
use App\Console\Commands\OocvFixDownload;
use App\Console\Commands\OocvMoveFoderUploadFinish;
use App\Console\Commands\OocvUpdate;
use App\Console\Commands\S3JobAsiaScan;
use App\Console\Commands\S3JobScan;
use App\Console\Commands\JobCreate;
use App\Console\Commands\JobDownload;
use App\Console\Commands\JobReDownload;
use App\Console\Commands\JobReDownloadErrorZip;
use App\Console\Commands\JobReDownloadLongUnZipManual;
use App\Console\Commands\JobUnzip;
use App\Console\Commands\JobUnzipChecks;
use App\Console\Commands\JobExtractedCheckScan;
use App\Console\Commands\JobMoveToDirectory;
use App\Console\Commands\JobSendMessageMoveToDirectory;

use App\Console\Commands\JobAfterZipMoveToReadyDirectory;
use App\Console\Commands\JobAfterZipMoveToReadyDirectorySendMessage;

use App\Console\Commands\JobUploadReady;
use App\Console\Commands\JobUploadReadyCheck;

//use App\Console\Commands\JobArchiveOldJobs;
use App\Console\Commands\JobBackupDatabaseFile;

use App\Console\Commands\ScanReadyFolderToUpload;
use App\Console\Commands\TeambitionCheckDownload;
use App\Console\Commands\TeambitionCreate;
use App\Console\Commands\TeambitionScan;
use App\Console\Commands\AsiaScan;
use App\Console\Commands\HandleActionFile;

use App\Console\Commands\Alookintosqlite;

use App\Console\Commands\ManualS3JobScan;
//use App\Console\Commands\ManualJobArchiveOldJobs;
use App\Console\Commands\ManualJobDownload;
use App\Console\Commands\ManualJobExtractedCheckScan;
use App\Console\Commands\ManualJobMoveToDirectory;
use App\Console\Commands\ManualJobSendMessageMoveToDirectory;
use App\Console\Commands\ManualJobUnzip;
use App\Console\Commands\ManualJobUnzipChecks;

use App\Console\Commands\TriggerEvent;

use App\Console\Commands\DispatchJob;

use App\Console\Commands\S3JobInitiateSpecialExampleZipNotPresentFunction;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Loggy;
use App\Console\Commands\Inspire;

class Kernel extends ConsoleKernel
{

    /*
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Inspire::class,
        Commands\S3JobScan::class,
        Commands\JobDownload::class,
        Commands\JobReDownload::class,
        Commands\JobReDownloadErrorZip::class,
        Commands\JobReDownloadLongUnZipManual::class,

        Commands\JobUnzipChecks::class,
        Commands\JobUnzip::class,
        Commands\JobExtractedCheckScan::class,
        Commands\JobMoveToDirectory::class,
        Commands\JobSendMessageMoveToDirectory::class,

        Commands\JobAfterZipMoveToReadyDirectory::class,
        Commands\JobAfterZipMoveToReadyDirectorySendMessage::class,

        Commands\JobBackupDatabaseFile::class,

        Commands\JobUploadReady::class,
        Commands\JobUploadReadyCheck::class,

        Commands\JobCreate::class,
        Commands\CreateThumbnail::class,

        Commands\S3JobInitiateSpecialExampleZipNotPresentFunction::class,

        /**server 220*/
        Commands\GetFilesUpload::class,
        Commands\ScanReadyFolderToUpload::class,
        Commands\JobUpload::class,
        Commands\JobFinish::class,

        Commands\HandleActionFile::class,

        Commands\ManualS3JobScan::class,
        Commands\ManualJobDownload::class,
        Commands\ManualJobExtractedCheckScan::class,
        Commands\ManualJobMoveToDirectory::class,
        Commands\ManualJobSendMessageMoveToDirectory::class,
        Commands\ManualJobUnzip::class,
        Commands\ManualJobUnzipChecks::class,

        Commands\Alookintosqlite::class,
        Commands\TriggerEvent::class,

        Commands\DispatchJob::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('clean:directories')->daily();

        $scheduled_tasks_toggle_value = DB::table('scheduled_tasks_toggle')->first();

        if($scheduled_tasks_toggle_value->active == 1){

            $today = Carbon::now()->format('Y-m-d');

            $response = exec("ping -c 1 google.com");

            $messenger_destination = env("MESSENGER_DESTINATION", "ROCKETCHAT");
            if($messenger_destination == 'BITRIX'){

                $response2 = app('App\Http\Controllers\OperatorController')->test_bitrix_chat_server_online();
            }else if($messenger_destination == 'ROCKETCHAT'){

                $response2 = app('App\Http\Controllers\OperatorController')->test_rocket_chat_server_online();
            }else{

                $response2 = false;
            }

            if(env('JOBFOLDER_DIR_SHARELOCATION_STRING') != ''){

                $JOBFOLDER_DIR_SHARELOCATION_STRING = explode("/", env('JOBFOLDER_DIR_SHARELOCATION_STRING'));

                foreach($JOBFOLDER_DIR_SHARELOCATION_STRING as $share_server_ip_details){
                    if(strpos($share_server_ip_details, '.') !== false){
                        if(strpos($share_server_ip_details, ':') !== false){
                            $share_server_ip_details = explode(":", $share_server_ip_details)[0];
                             $response3 = exec("/bin/ping -c 1 ".rtrim(str_replace("https://", "", str_replace("http://", "", $share_server_ip_details)), "/"));
                        }else{
                             $response3 = exec("/bin/ping -c 1 ".rtrim(str_replace("https://", "", str_replace("http://", "", $share_server_ip_details)), "/"));
                        }
                        break;
                    }
                }
            }else{
                $response3 = 'ping: bad address env("JOBFOLDER_DIR_SHARELOCATION_STRING") not set';
            }

            $jobFolderAsia = storage_path()."/app".config('s3br24.job_folder_asia');
            $jobFolderGermany = storage_path()."/app".config('s3br24.job_folder_germany');

            $manualjobFolder = storage_path()."/app".config('s3br24.manual_job_folder');

            $response4a = exec("mountpoint ".$jobFolderAsia);
            $response4b = exec("mountpoint ".$jobFolderGermany);
            $response6 = exec("mountpoint ".$manualjobFolder);


            Loggy::write('default', "APP_ENV: ".env('APP_ENV'));

            if (strpos($response, 'ping: bad address') !== false || $response2 == false || strpos($response3, 'ping: bad address') !== false) {

                Loggy::write('default', "response: ".$response);
                Loggy::write('default', "response2: ".dump($response2));
                Loggy::write('default', "response3: ".$response3);

                echo "No internet \n";
            }else{
                if(env('APP_ENV') == 'prod'){
                    if(strpos($response4a, 'is not a mountpoint') !== false || strpos($response4b, 'is not a mountpoint') !== false || strpos($response6, 'is not a mountpoint') !== false){

                        Loggy::write('default', "response4a: ".$response4a);
                        Loggy::write('default', "response4b: ".$response4b);
                        Loggy::write('default', "response6: ".$response6);

                    }else{
                        $schedule->command('s3:scan')->everyMinute()->runInBackground()->appendOutputTo(storage_path('logs/console_joblogs/commands-'.$today.'.log'));
                        $schedule->command('job:backupdatabasefile')->dailyAt('05:20')->runInBackground()->appendOutputTo(storage_path('logs/console_joblogs/commands-'.$today.'.log'));
                    }
                }
                if(env('APP_ENV') == 'local'){
                    $schedule->command('s3:scan')->everyMinute()->runInBackground()->appendOutputTo(storage_path('logs/console_joblogs/commands-'.$today.'.log'));
                    $schedule->command('job:backupdatabasefile')->dailyAt('05:20')->runInBackground()->appendOutputTo(storage_path('logs/console_joblogs/commands-'.$today.'.log'));
                }
            }
        }
    }


    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
