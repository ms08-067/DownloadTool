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
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;

use App\Models\TaskDownloadFile;
use App\Models\TaskDownload;
use App\Models\TaskDownloadView;
use App\Models\TaskUpload;
use App\Models\TaskUploadView;

class ManualUL_afterzip_movetojobfolderreadydirectory implements ShouldQueue
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
        $this->onQueue('manualul_afterzip_movetojobfolderreadydirectory');
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
        /** if a job is dispatched to this queue then it originally came from the ManualUL_afterzip_movetojobfolderreadydirectorychecks queue */
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
            
            /** reqeusted to move uploaded files to the Jobfolder/ready folder so they can check it */
            /** should have a notification that pops on their channel indicating that the case can be checked */
            /** later when it reaches s3 a notification that is has been uploaded to s3 */

            /** because the zip could be pretty big maybe it will take longer to upload to the shared folder and the scheduler will always be */
            /** if you can add a new column to keep track of whether it is being done */
            /** also if the number files do not match re-initiate the */
            /** since we are changing the logic.. instead of moveing the zip and uncompressing, and remove the zip afterwards we jsut unzip to the jobfolder ready folder check when its done. */

            $file = TaskUpload::where('state', '=', 'zipped')->where('state', '!=', 'notified')->where('move_to_jobfolder', 0)->orderBy('updated_at', 'asc')->get();
            /**dump($file);*/

            if(empty($file) || $file == null) {
                /** if it cannot find the row.. how do we handle it then? usually the db values should already be there. reaching here should never be the case.. if the job was dispatched */
                /** only if it is a timing issue?? */
                /** for testing we let it pop from the queue and log it */
                Loggy::write('default', json_encode([
                    'success' => false,
                    'description' => 'function ManualUL_afterzip_movetojobfolderreadydirectory handle() could not find row on TaskUpload table using query parameters',
                    'case_id' => $this->case_id,
                    'encrypted_case_id' => $this->encrypted_case_id,
                    'fstack' => $this->fstack,
                    'the_current_computer_ip_initiating' => $this->the_current_computer_ip_initiating,
                    'last_updated_by' => $this->last_updated_by
                ]));
                return;
            }            
            /**dump($file);*/


            $remembering_caseId_doing = null;
            foreach($file as $file_key => $upload_task_file_details){
                /**dump('$file_key    == '. $file_key);*/
                /**dump($upload_task_file_details);*/

                if($remembering_caseId_doing != $upload_task_file_details->case_id){
                    $remembering_caseId_doing = $upload_task_file_details->case_id;
                    /**dump('remembering_caseId_doing   '. $remembering_caseId_doing);*/

                    $all_case_files = TaskUpload::where('case_id', $upload_task_file_details->case_id)->get();
                    /**dd($all_case_files);*/

                    $all_files_ready_for_zip_upload_for_this_caseid = true;
                    foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                        /**dump($case_downloadfile_details->local);*/
                        if($case_downloadfile_details->state == 'zipped'){
                            /** let it through */
                        }else{
                            /** if one of the files in the case id is still downloading go to the next caseID if any to check if we can do that unzip first */
                            $all_files_ready_for_zip_upload_for_this_caseid = false;
                        }
                    }

                    if($all_files_ready_for_zip_upload_for_this_caseid){
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


            /**  */

            $move_to_jobfolder_tries = null;
            foreach($all_case_files as $case_file_key => $case_uploadfile_details){
                $file = TaskUpload::where('id', $case_uploadfile_details->id)->first();
                
                $move_to_jobfolder_tries = $file->move_to_jobfolder_tries;

                $file->move_to_jobfolder = 1;
                $file->save();
            }





            /** moving */
            $file->move_to_jobfolder = 1;
            $file->move_to_jobfolder_tries = $move_to_jobfolder_tries + 1;
            $file->save();

            /** replace with new retry count */
            $move_to_jobfolder_tries = $move_to_jobfolder_tries + 1;


            if ($move_to_jobfolder_tries == config('s3br24.s3_upload_time_allowed_before_retry')) {
                /** should we try to move again and see if that helps? */
                $message = array(
                    'title' => $file->case_id,
                    'content' => 'Move Uploaded Files to JobFolder ready Folder (error)',
                    'link' => null,
                    'to' => config('br24config.rc_notify_usernames')
                );

                $messenger_destination = env("MESSENGER_DESTINATION", "ROCKETCHAT");
                dump($messenger_destination);
                if($messenger_destination == 'BITRIX'){
                    BTRXsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                }else if($messenger_destination == 'ROCKETCHAT'){
                    //sendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                    RCsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                    /** ROCKETCHAT */
                }else{
                    /** this will not put the job back into the queue */
                    /** but if by some chance there was a job */
                    /** put it back in this queue to be checked again */
                    \App\Jobs\ManualUL_afterzip_movetojobfolderreadydirectory::dispatch($this->case_id, $this->encrypted_case_id, $this->fstack, $this->the_current_computer_ip_initiating, $this->last_updated_by)->delay(now()->addSeconds($queue_delay_seconds_manualul));                    
                    return;
                }
            }





            /** start the moving process */
            $path = storage_path()."/app".config('s3br24.job_folder').$remembering_caseId_doing."/ready";

            dump($path);
            /** need to be able to remove all the contents of the jobfolder/ready folder as well beforehand */

            $fs = new Filesystem();
            if(File::exists($path)) {
                /**dump($path . ' exists');*/
                /**dump('attempting deleting '. $path);*/
                $fs->cleanDirectory($path);
                $files1 = $fs->files($path);
                $fs->delete($files1);
                exec('rm -R '.$path."/");
            }

            if(File::exists($path)){
                try {
                    File::delete($path);
                    exec('rm -R '.$path);
                } catch (FileNotFoundException $e) {
                    dd($e);
                }
            }

            /** if the folder still has stuff inside then we cannot continue  */
            /** this could be because the file is open on another computer.. making the file busy [TODO]*/
            /** the directory needs to be clear of files otherwise the unzip to ready folder checks fails and nobody gets alerted */



            /**dd($path);*/
            if(!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true); /**make directory if not exists */
            }

            exec('mkdir -p ' . $path);


            /** check that the right amount of files exist in both locations this check should equal to 0 otherwise it makes problems */
            $number_of_files_in_directory = exec("find ".$path." -type f | wc -l");
            $number_of_folders_in_directory_min_depth = exec("find ".$path." -mindepth 1 -type d | wc -l");

            dump('number_of_files_in_directory '.$path . ' => ' . $number_of_files_in_directory);
            dump('number_of_folders_in_directory_min_depth '.$path . ' => ' . $number_of_folders_in_directory_min_depth);
            $very_specific_count = $number_of_files_in_directory + $number_of_folders_in_directory_min_depth;
            if($very_specific_count >= 1){
                /** we cannot continue because there are still folders or files in the ready directory */
                $file->move_to_jobfolder = 0;
                $file->save();

                \App\Jobs\ManualUL_afterzip_movetojobfolderreadydirectory::dispatch($this->case_id, $this->encrypted_case_id, $this->fstack, $this->the_current_computer_ip_initiating, $this->last_updated_by)->delay(now()->addSeconds($queue_delay_seconds_manualul));
                return;
            }else{
                /** we can continue */
            }


            /** we try to get the zip and cp it there extract it and remove the zip afterwards? */
            /** we change it so that it will unzip rather than move the file */

            // $cmd = 'cp -r '.$tempZipFolderZipFile.' '.$path;
            // //exec($cmd);
            // $pid = exec($cmd . " & echo $!;", $output);
            // /**dd(null);*/
            // $file->pid = $pid;
            // $file->save();

            /** store the pid so that you can cancel this one */

            /** at which point do we try to cancel it? only when they try to spam the upload button on upload */
            /** check that it is running then kill the process before uploading */
            //sleep(3);
            /** you may need to split it up into three different scheduled task */
            /** one for copying */
            /** one for unzipping */
            /** one for and one for checking or always checking in between the tasks to know when to do the next one */

            $unzip_log = storage_path()."/logs".config('s3br24.download_log') . 'unzip_log';
            exec("mkdir -p $unzip_log");
            /** dd(null); */
            $unzip_log = $unzip_log . '/' . $remembering_caseId_doing . '_ready_unzip_jobfolder.log';

            $tempZipFolderZipFile = storage_path()."/app".config('s3br24.temp_zip_folder').$remembering_caseId_doing."/ready.zip";

            /** use option -oO UTF8 to force character encoding on unzip filenames entirely */
            //$copied_zip_path = $path."/ready.zip";
            $cmd = "unzip -o " . $tempZipFolderZipFile . " -d " . $path . ' > '.$unzip_log;
            dump($cmd);
            //exec($cmd);
            $pid = exec($cmd . " & echo $!;", $output);
            dump($pid);

            $file->move_to_jobfolder = 2;
            $file->pid = $pid;
            $file->save();

            /** lastly if we reach here with everything we expect to find we can dispatch to the next queue */
            \App\Jobs\ManualUL_afterzip_movetojobfolderreadydirectory_send_message::dispatch($this->case_id, $this->encrypted_case_id, $this->fstack, $this->the_current_computer_ip_initiating, $this->last_updated_by)->delay(now()->addSeconds($queue_delay_seconds_manualul));
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
            'description' => 'ManualUL_afterzip_movetojobfolderreadydirectory failed()',
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
