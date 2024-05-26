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
use Illuminate\Support\Facades\Storage;

use App\Models\TaskDownloadFile;
use App\Models\TaskDownload;
use App\Models\TaskDownloadView;
use App\Models\TaskUpload;
use App\Models\TaskUploadView;

class ManualUL_send_the_ready_zip_to_s3 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $tries = 10; /** if tries = 0 retry indefinitely */
    //public $backoff = 60; /** amount of seconds to hold off before retrying when job fails and puts back to queue */
    //public $timeout = 7200;
    //public $maxExceptions = 3;

    public $case_id;
    public $encrypted_case_id;
    public $fstack;
    public $the_current_computer_ip_initiating;
    public $last_updated_by;

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
        $case_id,
        $encrypted_case_id,
        $fstack,
        $the_current_computer_ip_initiating,
        $last_updated_by
    )
    {
        $this->onQueue('manualul_send_the_ready_zip_to_s3');
        $this->delay(1);

        $this->case_id = $case_id;
        $this->encrypted_case_id = $encrypted_case_id;
        $this->fstack = $fstack;
        $this->the_current_computer_ip_initiating = $the_current_computer_ip_initiating;
        $this->last_updated_by = $last_updated_by;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        /** if a job is dispatched to this queue then it originally came from the ManualUL_send_the_ready_zip_to_s3checks queue */
        /** the zip needed to be downloaded again because it could not be unzipped... */
        dump($this->case_id);
        dump($this->encrypted_case_id);
        dump($this->fstack);
        dump($this->the_current_computer_ip_initiating);
        dump($this->last_updated_by);


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
                'caseId' => $this->case_id,
                'encryptedcaseId' => $this->encrypted_case_id,
                'fStack' => $this->fstack,
                'the_current_computer_ip_initiating' => $this->the_current_computer_ip_initiating,
                'last_updated_by' => $this->last_updated_by
            ]));
            throw new \Exception('no network connectivity or NAS/ RocketChat not connected/ mounts not mounted');
        }else{
            $queue_delay_seconds_manualul = DB::table('queue_delay_seconds_manualul')->first()->queue_delay_seconds;
            
            /** we want this to actually be handled by the worker ... so how do we do that after the front end has uploaded everything to the server? */
            /** sometimes the amount of scheduled task keeps growing almost like there is a blockage of some sort. */
            /** and when a job gets notified if there are backlogs of tasks then it doesn't complete correctly */
            /** in fact it actually deletes the jobFolder contetns wasting time and effort */
            $file = TaskUpload::where('sending_to_s3', 1)->orderBy('updated_at', 'asc')->get();
            /**dd($file);*/

            if(empty($file) || $file == null) {
                /** if it cannot find the row.. how do we handle it then? usually the db values should already be there. reaching here should never be the case.. if the job was dispatched */
                /** only if it is a timing issue?? */
                /** for testing we let it pop from the queue and log it */
                Loggy::write('default', json_encode([
                    'success' => false,
                    'description' => 'function ManualUL_send_the_ready_zip_to_s3 handle() could not find row on TaskUpload table using query parameters',
                    'case_id' => $this->case_id,
                    'encrypted_case_id' => $this->encrypted_case_id,
                    'fstack' => $this->fstack,
                    'the_current_computer_ip_initiating' => $this->the_current_computer_ip_initiating,
                    'last_updated_by' => $this->last_updated_by
                ]));
                return;
            } 
            /**dd($file);*/


            $remembering_caseId_doing = null;
            $need_to_make_all_notified = false;
            foreach($file as $file_key => $download_task_file_details){
                /**dump('$file_key    == '. $file_key);*/
                /**dump($download_task_file_details);*/

                if($remembering_caseId_doing != $download_task_file_details->case_id){
                    $remembering_caseId_doing = $download_task_file_details->case_id;
                    /**dump('remembering_caseId_doing   '. $remembering_caseId_doing);*/

                    $all_case_files = TaskUpload::where('case_id', $download_task_file_details->case_id)->get();
                    /**dd($all_case_files);*/

                    $all_files_ready_for_sending_to_s3_for_this_caseid = true;
                    foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                        /**dump($case_downloadfile_details->local);*/
                        
                        if($case_downloadfile_details->sending_to_s3 == 1){
                            /** let it through */
                        }else{
                            /** if one of the files in the case id is still downloading go to the next caseID if any to check if we can do that unzip first */
                            $all_files_ready_for_sending_to_s3_for_this_caseid = false;
                        }

                    }

                    if($all_files_ready_for_sending_to_s3_for_this_caseid){
                        /**dump('should be fine to unzip now for caseID  ' . $remembering_caseId_doing);*/
                        break;
                    }else{
                        /** when it doesn't have another caseId to go to then we must be able to exit properly  */
                        $remembering_caseId_doing = null;
                        $all_case_files = null;
                    }
                }
            }
            
            if(is_null($remembering_caseId_doing)){
                return;
            }

            dump($remembering_caseId_doing);
            /**dump($all_case_files);*/
            /**dump(null);*/

            /** send it to s3 */

            //filename to store
            $s3path = 'br24/Jobs/'.$remembering_caseId_doing.'/ready/ready.zip';

            dump($s3path);

            //Upload File to s3
            $s3Br24Config = config('s3br24');
            $s3 = Storage::disk('s3');
            $bucket = config('filesystems.disks.s3.bucket');

            $local_file = storage_path()."/app".config('s3br24.temp_zip_folder').$remembering_caseId_doing. '/ready.zip';
            /**dump($local_file);*/

            //$s3->put($s3path, file_get_contents($local_file));
            /** enable uplaod fo large files using file stream */
            //$s3->put($s3path, fopen($local_file, 'r+'));
            /** since this probably is working it always gets reset we should probably use the pid method and aws cli to make this work and we can check if it still running if it is let it run if it is not check if the s3 butcker has the file etc */
            /** so that if there are multiple uploads it can handle at least 5 at a time maybe and not have to wait? */

            /** running the command as user=www-data seems to not be able to get the aws credentials .. even with the environment variables set */
            /** */

            // alternate menthod to store to get pid for checking ? 
            $alternate_method_s3path = 'br24/Jobs/'.$remembering_caseId_doing.'/ready/ready.zip';
            $cmd = 'aws s3 cp --profile default '.$local_file.' s3://'.$bucket.'/'.$alternate_method_s3path;
            dump($cmd);
            $pid = exec($cmd . " > /dev/null & echo $!;", $output);
            dump('aws pid=' .$pid);
            /** can we store the pid to the taskUpload table */

            $file = TaskUpload::where('case_id', $remembering_caseId_doing)->first();
            $file->pid = $pid;
            $file->state = 'checking upload s3';
            $file->sending_to_s3 = 2;
            $file->save();

            /** send it to the next queue */
            \App\Jobs\ManualUL_check_the_progress_of_ready_zip_to_s3::dispatch($this->case_id, $this->encrypted_case_id, $this->fstack, $this->the_current_computer_ip_initiating, $this->last_updated_by)->delay(now()->addSeconds($queue_delay_seconds_manualul));
        }
    }

    /**
     * handle failed job.
     *
     * @return void
     */
    public function failed(Exception $e)
    {
        /**dump($e);*/
        /** simply log and let the tool move it to the failed jobs queue for later */
        Loggy::write('default', json_encode([
            'success' => false,
            'description' => 'ManualUL_send_the_ready_zip_to_s3 failed()',
            'exception' => $e,
            'caseId' => $this->case_id,
            'encryptedcaseId' => $this->encrypted_case_id,
            'fStack' => $this->fstack,
            'the_current_computer_ip_initiating' => $this->the_current_computer_ip_initiating,
            'last_updated_by' => $this->last_updated_by
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
