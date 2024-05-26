<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Loggy;
use File;

use App\Models\TaskManualDownloadFile;
use App\Models\TaskManualDownload;
use App\Models\TaskManualDownloadView;
use App\Models\TaskManualUpload;
use App\Models\TaskManualUploadView;

class ManualDL_movetodirectory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $tries = 0; /** if tries = 0 retry indefinitely */
    //public $backoff = 60; /** amount of seconds to hold off before retrying when job fails and puts back to queue */
    //public $timeout = 7200;
    //public $maxExceptions = 3;

    public $case_id;
    public $type;

    /**
     * The number of seconds after which the job's unique lock will be released when using ShouldBeUnique implementation
     *
     * @var int
     */
    public $uniqueFor = 3600;

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->case_id;
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     * In this example, the retry delay will be 1 second for the first retry, 5 seconds for the second retry, and 10 seconds for the third retry:
     * @return array
     */
    public function backoff()
    {
        return [60];
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        $case_id
    )
    {
        $this->onQueue('manualdl_movetodirectory');
        $this->delay(1);

        $this->case_id = $case_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        /** if a job is dispatched to this queue then it originally came from the ManualDL_movetodirectorychecks queue */
        /** the zip needed to be downloaded again because it could not be unzipped... */

        $caseId = $this->case_id;
        dump($caseId);

        /**for the job to fail there has to be an exception called.. */
        /**just throw an error when it fails to have the queue reload it to have it reprocess */
        /**throw new \Exception('Exception message');*/
        /**any return is considered a successful job!*/
        /**return;*/

        $network_connectivity = $this->check_online();
        //$network_connectivity = true;
        dump($network_connectivity);
        if(!$network_connectivity){
            /** throw an error and delay the job for a minute */
            Loggy::write('default', json_encode([
                'success' => false,
                'description' => 'no network connectivity or NAS/ RocketChat not connected/ mounts not mounted '. env("MESSENGER_DESTINATION", "ROCKETCHAT"),
                'caseId' => $caseId,
                //'caseIdtype' => $caseIdtype
            ]));
            throw new \Exception('no network connectivity or NAS/ RocketChat not connected/ mounts not mounted');
        }else{
            $queue_delay_seconds_manualdl = DB::table('queue_delay_seconds_manualdl')->first()->queue_delay_seconds;
            /** if it reaches here let us assume that the case is ready to be moved. */

            /** what happens if there is a network issue --> it will retry */
            /** that interrupts the cifs mount :- fstab will try to re mount and anyway if the mounts are not mounted none of the scheduled task will run and therefore needs human intervention */
            $all_case_files = TaskManualDownloadFile::select('*')
            ->when($caseId, function($query) use ($caseId){
                return $query->where("case_id", $caseId);
            })
            // ->when($caseIdtype, function($query) use ($caseIdtype){
            //     return $query->where("type", $caseIdtype);
            // })
            ->where('state', 'unzipped')->where('unzip', 2)
            ->where('state', '!=', 'notified')->get();

            /**dump($all_case_files);*/
            dump(empty($all_case_files));
            dump($all_case_files->count());
            if($all_case_files->count() != 0){
                $jobFolder = storage_path()."/app".config('s3br24.manual_job_folder');

                $jobFolderAsia = storage_path()."/app".config('s3br24.job_folder_asia');
                $jobFolderGermany = storage_path()."/app".config('s3br24.job_folder_germany');

                //dump($jobFolder);
                exec("mkdir -p " . $jobFolder);
                exec("mkdir -p " . $jobFolderAsia);
                exec("mkdir -p " . $jobFolderGermany);

                $unzipFolder = storage_path()."/app".config('s3br24.manual_unzip_folder').$caseId;

                //$DS_Store_files_in_unzip_directory = exec("find ".$unzipFolder." -type f -name .DS_Store");
                $DS_Store_files_in_unzip_directory = exec("find ".$unzipFolder." -type f -name .DS_Store -delete");
                $DS_Store_files_in_unzip_directory = exec("find ".$unzipFolder." -type f -name ._.DS_Store -delete");
                //$Thumbs_db_files_in_unzip_directory = exec("find ".$unzipFolder." -type f -name Thumbs.db");
                $Thumbs_db_files_in_unzip_directory = exec("find ".$unzipFolder." -type f -name Thumbs.db -delete");
                /** before moving we remove any of the useless files that maybe affect the file count later */
                /** .DS_Store */
                /** Thumbs.db */

                /** now we have an issue where there are special characters in the folder name that are preventing the transfer to the job folder */
                /** right before do the step we have to sanitize the folder names too */

                /** Should limit the total amount of concurrent rsync commands to 5 so that there limited overload on the system */
                $cmd = 'ps aux | grep rsync | wc -l';
                dump($cmd);
                $count_of_rsync_commands_running = exec($cmd);
                dump($count_of_rsync_commands_running);

                if($count_of_rsync_commands_running > 5){
                    Loggy::write('default', json_encode([
                        'success' => false,
                        'description' => '$count_of_rsync_commands_running > 5',
                        'caseId' => $caseId,
                        //'caseIdtype' => $caseIdtype
                    ]));

                    //throw new \Exception('$count_of_rsync_commands_running > 5');
                    /** put it back in the queue with a delay */
                    \App\Jobs\ManualDL_movetodirectory::dispatch($caseId)->delay(now()->addSeconds($queue_delay_seconds_manualdl));
                    return;
                }

                $progress_path = storage_path()."/logs".config('s3br24.manual_download_log')."progress";

                if (!File::isFile($progress_path)) {
                    $path = storage_path().'/logs'.config('s3br24.manual_download_log').'progress';
                    File::makeDirectory($path, 0777, true, true); /** make directory */
                }
                $rsync_progress_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress/". $caseId.'_rsync_progressLog.log';


                /** BUT WE WANT ALL MANUAL JOBS TO BE SENT TO THE OTHERS FOLDER DESTINATION SO DISREGUARD THE xml tool client column */
                //foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                //    if($case_downloadfile_details->xml_tool_client == 1){
                //        /**dump($unzipFolder);*/
                //        $cmd = 'rsync --ignore-existing -avr --stats --info=progress2 '.$unzipFolder. ' ' . $jobFolderGermany;
                //        dump($cmd);
                //    }else if($case_downloadfile_details->xml_tool_client == 2){
                //        /**dump($unzipFolder);*/
                //        $cmd = 'rsync --ignore-existing -avr --stats --info=progress2 '.$unzipFolder. ' ' . $jobFolderAsia;
                //        dump($cmd);
                //    }else{
                //        /**dump($unzipFolder);*/
                //        $cmd = 'rsync --ignore-existing -avr --stats --info=progress2 '.$unzipFolder. ' ' . $jobFolder;
                //        dump($cmd);
                //    }
                //}

                /**dump($unzipFolder);*/
                $cmd = 'rsync --ignore-existing -avr --stats --info=progress2 '.$unzipFolder. ' ' . $jobFolder;
                dump($cmd);

                /** LOGGING FILE FOR AUTODL IS NOT NECESSARY AND PROBABLY AFFECTS THE QUEUE SEQUENCE FOR SERIOULS SMALL ZIPS */
                //$pid = exec($cmd . " > /dev/null & echo $!;", $output);
                /** run in foreground but this gets the $pid after it finishes.. so why do we need the pid? */
                //exec("sh -c 'echo $$; exec " .$cmd. "'", $output, $result_code);
                exec("sh -c 'echo $$; exec " .$cmd. " > {$rsync_progress_log}'", $output, $result_code);
                //$pid = exec($cmd . " > {$rsync_progress_log} & echo $!;", $output);

                dump('$output');
                dump($output);

                $pid = $output[0];

                dump('$pid');
                dump($pid);

                dump('$result_code');
                dump($result_code);

                // get the last item key in collection object;
                $last_key = $all_case_files->keys()->last();
                dump('$last_key');
                dump($last_key);

                /** it seems to stall here.. and then gets retried .. */

                foreach($all_case_files as $case_file_key => $case_downloadfile_details){

                    dump($case_file_key);

                    $file = TaskManualDownloadFile::where('id', $case_downloadfile_details->id)->first();
                    $file->pid = $pid;
                    $file->state = 'moving_to_jobFolder';
                    $file->save();


                    /** we need to have sufficient time to allow for the rsync command to finish */
                    /** dispatch to the next step which is notify when all have been moved to the job folder.. */
                    if($case_file_key == $last_key){
                        /** to make sure that they all have had their state adjusted to the next state we only dispatch on the last item .. */
                        \App\Jobs\ManualDL_messageaftermovetodirectory::dispatch($caseId)->delay(now()->addSeconds($queue_delay_seconds_manualdl));
                        dump('job dispatched to ManualDL_messageaftermovetodirectory queue');
                    }
                }
            }else{
                /** the array was empty how can that be? */
                \App\Jobs\ManualDL_messageaftermovetodirectory::dispatch($caseId)->delay(now()->addSeconds($queue_delay_seconds_manualdl));
            }

        }
        /** for some reason sometimes it passes right through and the next queue is never queued up */
    }

    /**
     * handle failed job.
     *
     * @return void
     */
    public function failed(Exception $e)
    {
        /**dump($e);*/
        $caseId = $this->case_id;
        //$caseIdtype = $this->type;
        /**dump($caseId);*/
        /**dump($caseIdtype);*/

        /** simply log and let the tool move it to the failed jobs queue for later */
        Loggy::write('default', json_encode([
            'success' => false,
            'description' => 'ManualDL_movetodirectory failed()',
            'exception' => $e,
            'caseId' => $caseId,
            //'caseIdtype' => $caseIdtype
        ]));
    }    

    /**
     * handle check network status and device connectivity before processing job.
     *
     * @return boolean
     */
    public function check_online()
    {
        /** this whole functiont akes about 5 seconds.. is there a way to make it faster? */
        $scheduled_tasks_toggle_value = DB::table('scheduled_tasks_toggle')->first();
        /**dd($scheduled_tasks_toggle_value->active);*/

        if($scheduled_tasks_toggle_value->active == 1){

            $today = Carbon::now()->format('Y-m-d');

            $whoami = exec("whoami");
            $response = exec("/bin/ping -c 1 google.com");
            $messenger_destination = env("MESSENGER_DESTINATION", "ROCKETCHAT");
            dump($messenger_destination);
            if($messenger_destination == 'BITRIX'){
                $response2 = app('App\Http\Controllers\OperatorController')->test_bitrix_chat_server_online();
            }else if($messenger_destination == 'ROCKETCHAT'){
                $response2 = app('App\Http\Controllers\OperatorController')->test_rocket_chat_server_online();
                /** ROCKETCHAT */
            }else{
                return false;
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
            /**dd($response3);*/

            if (strpos($response, 'ping: bad address') !== false || $response2 == false || strpos($response3, 'ping: bad address') !== false) {
                if(env('APP_ENV') == 'prod'){
                }
                if(env('APP_ENV') == 'dev' || env('APP_ENV') == 'test'){
                }
                /** we are not online/ cannot access rocket chat/ cannot access NAS */
                return false;
            }else{

                /** it not just good enough to be able to ping the Network Attached Storage Device.. it has to be mounted */
                //$jobFolder = storage_path()."/app".config('s3br24.job_folder');

                $jobFolderAsia = storage_path()."/app".config('s3br24.job_folder_asia');
                $jobFolderGermany = storage_path()."/app".config('s3br24.job_folder_germany');

                //$archiveFolder = storage_path()."/app".config('s3br24.archive_folder');
                $manualjobFolder = storage_path()."/app".config('s3br24.manual_job_folder');

                //$response4 = exec("mountpoint ".$jobFolder);
                $response4a = exec("mountpoint ".$jobFolderAsia);
                $response4b = exec("mountpoint ".$jobFolderGermany);

                //$response5 = exec("mountpoint ".$archiveFolder);
                $response6 = exec("mountpoint ".$manualjobFolder);

                if(env('APP_ENV') == 'prod'){ 
                    if(strpos($response4a, 'is not a mountpoint') !== false || strpos($response4b, 'is not a mountpoint') !== false || strpos($response6, 'is not a mountpoint') !== false){
                        return false;
                        /** we are online BUT */
                        /** mount point is not mounted when it should be, thefore cannot continue */
                    }else{
                        /** pass through */
                    }
                }
                if(env('APP_ENV') == 'dev' || env('APP_ENV') == 'test'){
                    /**pass through*/
                }
            }
        }else{
            return false;
            /** we are disabling the queue from the db */
        }
        return true;
    }

}
