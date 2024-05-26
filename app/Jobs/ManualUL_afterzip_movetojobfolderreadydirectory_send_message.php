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

class ManualUL_afterzip_movetojobfolderreadydirectory_send_message implements ShouldQueue
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
        $this->onQueue('manualul_afterzip_movetojobfolderreadydirectory_send_message');
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
        /** if a job is dispatched to this queue then it originally came from the ManualUL_afterzip_movetojobfolderreadydirectory_send_messagechecks queue */
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
            
            /** sometimes the amount of scheduled task keeps growing almost like there is a blockage of some sort. */
            /** and when a job gets notified if there are backlogs of tasks then it doesn't complete correctly */
            /** in fact it actually deletes the jobFolder contetns wasting time and effort */
            $file = TaskUpload::where('move_to_jobfolder', 2)->orderBy('updated_at', 'asc')->get();
            /**dump($file);*/

            if(empty($file) || $file == null) {
                /** if it cannot find the row.. how do we handle it then? usually the db values should already be there. reaching here should never be the case.. if the job was dispatched */
                /** only if it is a timing issue?? */
                /** for testing we let it pop from the queue and log it */
                Loggy::write('default', json_encode([
                    'success' => false,
                    'description' => 'function ManualUL_afterzip_movetojobfolderreadydirectory_send_message handle() could not find row on TaskUpload table using query parameters',
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

                    $all_files_ready_for_unzip_for_this_caseid = true;
                    foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                        /**dump($case_downloadfile_details->local);*/
                        if($case_downloadfile_details->state == 'zipped'){
                            /** let it through */
                        }else{
                            /** if one of the files in the case id is still downloading go to the next caseID if any to check if we can do that unzip first */
                            $all_files_ready_for_unzip_for_this_caseid = false;
                        }
                    }

                    if($all_files_ready_for_unzip_for_this_caseid){
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
            /**dd(null);*/

            /** we can check if the pid is still running if it is we can skip the next part or just let it run it gets check later and gets reset so it can be done again . */


            /** its all then a matter of sending ONE message to the responsible with the case number and the job location */
            /** maybe need to move everything -- later */
            $content = '';
            $case_id = '';
            $xml_title_contents = '';
            $xml_jobid_title = '';

            foreach($all_case_files as $case_file_key => $case_downloadfile_details){

                $file = TaskUploadView::where('id', $case_downloadfile_details->id)->first();
                /**dump('processing === ' . $file->local);*/
                /**dump($file->local);*/

                $JOBFOLDER_DIR_SHARELOCATION_STRING = env('JOBFOLDER_DIR_SHARELOCATION_STRING');

                //$content .= 'Zip '.$file->type.' extracted to ' . $folder . '';
                if($content == ''){
                    $content .= '[BR]------------------------------------------------------[BR]';
                    $content .= '[URL=file:\\\\'.str_replace("/", "\\", $JOBFOLDER_DIR_SHARELOCATION_STRING . $file->case_id).'\\ready'.']'. str_replace("/", "\\", $JOBFOLDER_DIR_SHARELOCATION_STRING . $file->case_id).'\\ready'.'[/URL]';
                    $content .= '[BR]------------------------------------------------------[BR] files are ready for checking';

                    $case_id = $file->case_id;
                    $xml_title_contents = $file->xml_title_contents;
                    $xml_jobid_title = $file->xml_jobid_title;
                }

                /**$file->state = 'notified';*/
                /**$file->save();*/
            }
            $content .= '';

            dump($content);

            /** by this point the files have already moved so we don't need to do the next check */
            /** */

            $result_of_folder_move = $this->check_move_status_caseID_folder_from_temp_upload_directory_to_jobfolder_ready_directory($case_id);

            dump('$result_of_folder_move');
            dump($result_of_folder_move);

            if($result_of_folder_move == 'still running find command'){
                /** the move is inconclusive */
                /** put back in the queue */
                \App\Jobs\ManualUL_afterzip_movetojobfolderreadydirectory_send_message::dispatch($this->case_id, $this->encrypted_case_id, $this->fstack, $this->the_current_computer_ip_initiating, $this->last_updated_by)->delay(now()->addSeconds($queue_delay_seconds_manualul));
                return;
            }



            if($result_of_folder_move == 'true'){
                /** we have to protect against the eventuallity that there is a power cut when this move is happening.. or if there is a network issue */
                $message = array(
                    'title' => $case_id,
                    'content' => $content,
                    'xml_title_contents' => $xml_title_contents,
                    'xml_jobid_title' => $xml_jobid_title,
                    'link' => null,
                    'to' => config('br24config.rc_notify_usernames')
                );

                $messenger_destination = env("MESSENGER_DESTINATION", "ROCKETCHAT");
                dump($messenger_destination);
                if($messenger_destination == 'BITRIX'){
                    BTRXsendUploadJobReadyforCheckingMessage('CREATE_JOB_ZIP_ERROR', $message);
                }else if($messenger_destination == 'ROCKETCHAT'){
                    //sendUploadJobReadyforCheckingMessage('CREATE_JOB_ZIP_ERROR', $message);
                    RCsendUploadJobReadyforCheckingMessage('CREATE_JOB_ZIP_ERROR', $message);
                    /** ROCKETCHAT */
                }else{
                    /** this will not put the job back into the queue */
                    /** but if by some chance there was a job */
                    /** put it back in this queue to be checked again */
                    \App\Jobs\ManualUL_afterzip_movetojobfolderreadydirectory_send_message::dispatch($this->case_id, $this->encrypted_case_id, $this->fstack, $this->the_current_computer_ip_initiating, $this->last_updated_by)->delay(now()->addSeconds($queue_delay_seconds_manualul));
                    return;
                }

                $file = TaskUpload::where('case_id', $remembering_caseId_doing)->first();
                /**dump('processing === ' . $file->local);*/
                /**dump($file->local);*/
                $file->state = 'notified/uploading to s3';
                $file->move_to_jobfolder = 3;
                $file->sending_to_s3 = 1;
                $file->save();

                /** add to the next queue */
                \App\Jobs\ManualUL_send_the_ready_zip_to_s3::dispatch($this->case_id, $this->encrypted_case_id, $this->fstack, $this->the_current_computer_ip_initiating, $this->last_updated_by)->delay(now()->addSeconds($queue_delay_seconds_manualul));
                return;
            }else{

                /** what do we do when it does not match here ? */
                /** we check if the pid is still running */
                /** if it is not we reset the move_to_jobfolder value to 0 so it can be done again */

                /** the contents are not the same.. so we have a problem so we allow the scheduled tasks to re do the function with the folder and file structure the same as the zip source */
                /** as some point we need to check if the move to the jobFolder pid is still alive.. if it isn't need to reset it to the unzipped state for all the rows with the same case_id */
                /** we update the state to still moving adjusting the updated_at column so the next time it tires the other caseId and not stick to repeating this caseID to keep things moving along rather than stuck */

                /** sometimes the best way to fix the problem is to clear the jobFolder directory as well */
                $file = TaskUpload::where('case_id', $remembering_caseId_doing)->first();

                $check_if_pid_still_running_cmd = "ps aux | awk '{print $1 }' | grep ". $file->pid;
                dump($check_if_pid_still_running_cmd);

                $check_if_pid_still_running = exec($check_if_pid_still_running_cmd);

                dump('$check_if_pid_still_running');
                dump($check_if_pid_still_running);

                dump("file[pid]");
                dump($file['pid']);

                if($check_if_pid_still_running == $file['pid']){
                    /** it is still running under the same pid.. lucky us, just let it keep going. */
                    /** throw an error so taht it can be reloaded back into the queue technically it is not an error */
                    Loggy::write('default', json_encode([
                        'success' => false,
                        'description' => 'check_if_pid_still_running == file pid',
                        'caseId' => $this->case_id,
                        'encryptedcaseId' => $this->encrypted_case_id,
                        'fStack' => $this->fstack,
                        'the_current_computer_ip_initiating' => $this->the_current_computer_ip_initiating,
                        'last_updated_by' => $this->last_updated_by
                    ]));

                    \App\Jobs\ManualUL_afterzip_movetojobfolderreadydirectory_send_message::dispatch($this->case_id, $this->encrypted_case_id, $this->fstack, $this->the_current_computer_ip_initiating, $this->last_updated_by)->delay(now()->addSeconds($queue_delay_seconds_manualul));
                    return;
                }else{
                    dump($remembering_caseId_doing . ' rsync pid: '.$file['pid'].' is no longer running destined for ready folder');
                    /** pid does not exists so we can reset it to the unzipped state to attempt to zip again */

                    /** lets try not to remove the files in the jobFolder at all */
                    // $jobFolder = storage_path()."/app".config('s3br24.job_folder');
                    // exec('rm -r ' . $jobFolder . $working_on_this_case_id . '/new');
                    // exec('rm -r ' . $jobFolder . $working_on_this_case_id . '/example');

                    $file->try = 0;
                    $file->state = 'retry_zip';
                    $file->move_to_jobfolder = 0;
                    $file->move_to_jobfolder_tries = 0;
                    $file->sending_to_s3 = 0;
                    $file->sending_to_s3_tries = 0;

                    $file->save();

                    /** send it back to the previous queue to be rezipped... hopefully that fixes the issue */
                    \App\Jobs\ManualUL_checkthenzip::dispatch($this->case_id, $this->encrypted_case_id, $this->fstack, $this->the_current_computer_ip_initiating, $this->last_updated_by)->delay(now()->addSeconds($queue_delay_seconds_manualul));
                    return;
                }
            }
        }
    }

    public function check_move_status_caseID_folder_from_temp_upload_directory_to_jobfolder_ready_directory($case_id)
    {
        /** start the moving process */
        $path = storage_path()."/app".config('s3br24.job_folder').$case_id."/ready";
        dump($path);
        if(!File::exists($path)) {
            File::makeDirectory($path, 0777, true, true); /**make directory if not exists */
        }
        exec('mkdir -p ' . $path);



        dump('checking move from tempUploadfolder to jobfolder ready for case_id => '.$case_id);

        /** it seems to keep bunching here we can probably use the grep command to see if one of the find commands is already running for this case in which case don't need to repeat it */
        $cmd1 = "ps aux | grep \"find\" | grep \"".$path."\" | grep \"type\" | grep \"wc\" | wc -l";
        dump($cmd1);
        $check_find_file_count_command_is_still_running_jobfolder_directory = exec($cmd1);
        
        $cmd2 = "ps aux | grep \"find\" | grep \"".$path."\" | grep \"mindepth\" | grep \"type\" | grep \"wc\" | wc -l";
        dump($cmd2);
        $check_find_directory_count_command_is_still_running_jobfolder_directory = exec($cmd2);

        dump('jobfolder_ready ' . $check_find_file_count_command_is_still_running_jobfolder_directory . "||" . $check_find_directory_count_command_is_still_running_jobfolder_directory);


        if($check_find_file_count_command_is_still_running_jobfolder_directory >= 2 || $check_find_directory_count_command_is_still_running_jobfolder_directory >= 2){
            return 'still running find command';
        }else{

            /** we try to get the zip and cp it there extract it and remove the zip afterwards? */
            $tempZipFolderZipFile = storage_path()."/app".config('s3br24.temp_zip_folder').$case_id."/ready.zip";

            /** check that the right amount of files exist in both locations */
            $number_of_files_in_directory = exec("find ".$path." -type f | wc -l");
            $number_of_folders_in_directory_min_depth = exec("find ".$path." -mindepth 1 -type d | wc -l");

            /**dump('try_to_download_again');*/
            /**dump($try_to_download_again);*/
            $zipArchive = new \ZipArchive();
            $tryOpeningZip = $zipArchive->open($tempZipFolderZipFile);

            /**dump('========================');*/

            /**dump('$count_inflated_files');*/
            /**dump($count_inflated_files);*/
            /**dd(null);*/

            /** if one encountered but it is not yet unzipped we have to wait longer */

            /** if the logs are not built consistently then the files never leave this loop and will eventually fail hard */
            $very_specific_count = $number_of_files_in_directory + $number_of_folders_in_directory_min_depth;

            dump('number_of_files_in_directory '.$path . ' => ' . $number_of_files_in_directory);
            dump('number_of_folders_in_directory_min_depth '.$path . ' => ' . $number_of_folders_in_directory_min_depth);
            dump('====');
            dump('$zipArchive->numFiles => ' .$zipArchive->numFiles);
            dump('$very_specific_count => ' .$very_specific_count);

            /** close at the end */
            $todays_date = Carbon::now()->format('Y-m-d');
            $yesterdays_date = Carbon::now()->subDays(1)->format('Y-m-d');
            $keeping_track_of_archive_string = false;
            $zipArchive_numFiles_Counted_from_unzip_test_log = 0;

            if(File::exists($unzip_log)){
                $fropen = fopen($unzip_log, 'r' );
                if ($fropen) {
                    while (($line = fgets($fropen)) !== false) {

                        if (strpos($line, 'Archive:') !== false) {
                            if($keeping_track_of_archive_string == true){
                                $keeping_track_of_archive_string = false;
                            }
                            $keeping_track_of_archive_string = true;
                        }

                        if($keeping_track_of_archive_string == true){
                            if (strpos($line, $todays_date) !== false || strpos($line, $yesterdays_date) !== false) {

                                /**dump($line);*/
                                $length_string_portion = substr($line, 0, 9);
                                $length_string_portion = (int)$length_string_portion;
                                /**dump($length_string_portion);*/

                                if($length_string_portion >= 1){
                                    $zipArchive_numFiles_Counted_from_unzip_test_log++;
                                }
                            }
                        }
                    }
                    fclose($fropen);
                } else {
                    /** error opening the log file. maybe still writing to */
                    dump('error opening unzip_log file' . $unzip_log);
                    return;
                }
            }            
            dump('zipArchive_numFiles_Counted_from_unzip_test_log '.$folder . ' => ' . $zipArchive_numFiles_Counted_from_unzip_test_log);

            if($very_specific_count == $zipArchive->numFiles || $number_of_files_in_directory == $zipArchive->numFiles || $number_of_files_in_directory == $zipArchive_numFiles_Counted_from_unzip_test_log){
                //$zipArchive->close();
                return 'true';
            }else{
                //$zipArchive->close();

                /** sometimes the zip doesn't have enough files in it.. how do tell the rest of the code that it needs to go back to the zipping queue */
                return 'false';
            }
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
            'description' => 'ManualUL_afterzip_movetojobfolderreadydirectory_send_message failed()',
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
