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

use App\Models\TaskDownloadFile;
use App\Models\TaskDownload;
use App\Models\TaskDownloadView;
use App\Models\TaskUpload;
use App\Models\TaskUploadView;

class ManualUL_check_the_progress_of_ready_zip_to_s3 implements ShouldQueue
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
        $this->onQueue('manualul_check_the_progress_of_ready_zip_to_s3');
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
        /** if a job is dispatched to this queue then it originally came from the ManualUL_check_the_progress_of_ready_zip_to_s3checks queue */
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
            $file = TaskUpload::where('sending_to_s3', 2)->where('state', '=', 'checking upload s3')->orderBy('updated_at', 'asc')->get();
            /**dump($file);*/

            if(empty($file) || $file == null) {
                /** if it cannot find the row.. how do we handle it then? usually the db values should already be there. reaching here should never be the case.. if the job was dispatched */
                /** only if it is a timing issue?? */
                /** for testing we let it pop from the queue and log it */
                Loggy::write('default', json_encode([
                    'success' => false,
                    'description' => 'function ManualUL_check_the_progress_of_ready_zip_to_s3 handle() could not find row on TaskUpload table using query parameters',
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
                        
                        if($case_downloadfile_details->sending_to_s3 == 2){
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

            /** check progress of upload ready.zip to s3 */

            /** on another trip we check if the pid is still running */
            /** if it is not we check if there are files on the s3? */
            /***/




            /** check if pid is still running */
            $file = TaskUpload::where('case_id', $remembering_caseId_doing)->first();



            $check_if_pid_still_running = exec("ps aux | awk '{print $1 }' | grep ". $file->pid);
            if($check_if_pid_still_running == $file->pid){
                /** it is still running under the same pid.. lucky us, just let it keep going. */


                if ($file->sending_to_s3_tries > 3600) {
                    $file->pid = NULL;
                    $file->sending_to_s3_tries = 0;
                    $file->sending_to_s3 = 1;
                    $file->save();
                    return;
                }


                $file->sending_to_s3_tries = $file->sending_to_s3_tries + 1;
                $file->save();

                /** return it to the same queue with a short delay */
                \App\Jobs\ManualUL_check_the_progress_of_ready_zip_to_s3::dispatch($this->case_id, $this->encrypted_case_id, $this->fstack, $this->the_current_computer_ip_initiating, $this->last_updated_by)->delay(now()->addSeconds($queue_delay_seconds_manualul));
                return;
            }else{
                dump($remembering_caseId_doing . ' aws cp s3 pid: '.$file->pid.' is no longer running destined for s3 ready folder');
                /** pid does not exists so we check if the right amount of files have been send to s3 */



                //$remembering_caseId_doing = 10101010;

                $bucket = config('filesystems.disks.s3.bucket');
                $alternate_method_s3path = 'br24/Jobs/'.$remembering_caseId_doing.'/ready/';

                /** create a log file to store the contents of the s3 bucket in human readable form to check whether all the files from the zip have been uploaded and extracted */




                $s3_unzip_log = storage_path()."/logs".config('s3br24.download_log') . 's3_unzip_log';
                exec("mkdir -p $s3_unzip_log");
                /** dd(null); */
                $s3_unzip_log = $s3_unzip_log . '/' . $remembering_caseId_doing . '_s3_ready_unzip_readyfolder.log';


                $cmd1 = 'aws s3 ls --profile default s3://'.$bucket.'/'.$alternate_method_s3path.' --recursive --human-readable > '.$s3_unzip_log;
                dump($cmd1);
                exec($cmd1);
                

                /** open the log file and go through eachline using the current date to see which files have been uploaded to s3 today */
                /** then check against the contents of the zip and compare */

                /** get form the temp upload directory */
                $tempupload_path = storage_path()."/app".config('s3br24.temp_upload_folder').$remembering_caseId_doing;




                $local_temp_upload_find_files_log = storage_path()."/logs".config('s3br24.download_log') . 's3_unzip_log';
                /**exec("mkdir -p $s3_unzip_log");*/
                /** dd(null); */
                $local_temp_upload_find_files_log = $local_temp_upload_find_files_log . '/' . $remembering_caseId_doing . '_s3_ready_unzip_readyfolder_compare_with.log';


                $cmd2 = "find ".$tempupload_path. " -type f > ".$local_temp_upload_find_files_log;
                dump($cmd2);
                exec($cmd2);
                
                
                // exec("find ".$inner_tempUploadfolder." -mindepth 1 -maxdepth 1 -type d | wc -l");

                // $cmd = 'tree ';

                $todays_date = Carbon::now()->format('Y-m-d');
                $yesterdays_date = Carbon::now()->subDays(1)->format('Y-m-d');
                /** what happens if it is a big zip  os even small zips that are uploaded just before midnight .. */
                /** the date check comparision might not always run */

                $array_of_items_on_s3 = [];
                $count_of_files_uplaod_to_s3_today = 0;
                $fropen = fopen($s3_unzip_log, 'r' );
                if ($fropen) {
                    while (($line = fgets($fropen)) !== false) {
                        if (strpos($line, $todays_date) !== false || strpos($line, $yesterdays_date) !== false) {
                                                
                            /**dump($line);*/
                            $file_from_root = explode($alternate_method_s3path, $line)[1];
                            /**dump($file_from_root);*/

                            if(str_replace(" ", "", $file_from_root) != ""){
                                $count_of_files_uplaod_to_s3_today++;
                                $array_of_items_on_s3[$count_of_files_uplaod_to_s3_today] = $file_from_root;
                            }
                        }
                    }
                    fclose($fropen);
                } else {
                    /** error opening the log file. maybe still writing to */
                    dump('error opening s3_unzip_log file');
                    return;
                }
                /**dump($array_of_items_on_s3);*/

                $array_of_items_on_local = [];
                $count_of_files_on_local_today = 0;
                $fropen = fopen($local_temp_upload_find_files_log, 'r' );
                if ($fropen) {
                    while (($line = fgets($fropen)) !== false) {
                        $file_from_root = str_replace($tempupload_path."/", "", $line);
                        /**dump($file_from_root);*/
                        if(str_replace(" ", "", $file_from_root) != ""){
                            $count_of_files_on_local_today++;
                            $array_of_items_on_local[$count_of_files_on_local_today] = $file_from_root;
                        }
                    }
                    fclose($fropen);
                } else {
                    /** error opening the log file. maybe still writing to */
                    dump('error opening local_temp_upload_find_files_log file');
                    return;
                }

                /**dump($array_of_items_on_local);*/
                $array_of_items_on_s3 = array_filter($array_of_items_on_s3);
                $array_of_items_on_local = array_filter($array_of_items_on_local);

                dump(print_r(json_encode($array_of_items_on_s3), true));
                dump(print_r(json_encode($array_of_items_on_local), true));

                $difference = array_diff($array_of_items_on_local, $array_of_items_on_s3);
                /**dump($difference);*/

                dump(print_r(json_encode($difference), true));
                dump(empty($difference));
                /**dd(null);*/

                /** what happens if there are not enough .. but some so we wait ... for the iterration and once all are found on s3 we can set the final status */
                /** then we are ready to do the clean up of the uploads */

                /** to force notifying of case Id bypass s3 file checks */
                $case_id_to_force_notify_manually = DB::table('bypass_filecountcheck_force_notify_s3')->first();
                if($case_id_to_force_notify_manually){
                    if($remembering_caseId_doing == $case_id_to_force_notify_manually->case_id){
                        $difference = [];
                        dump('bypassing s3 filecountcheck for '.$remembering_caseId_doing.'');
                    }
                }


                if(!empty($difference)){

                    /** if not then we return the file values back to 1 so that it can be triggered again */
                    /** need to wait for the s3 unzipper to finish */
                    /** we can increment the amount of times checking */
                    /** if it larger than a certain amount of times we return the thing to beginning */

                    $file = TaskUpload::where('case_id', $remembering_caseId_doing)->first();

                    if ($file->sending_to_s3_tries > config('s3br24.s3_upload_time_allowed_before_retry')) {
                        $file->pid = NULL;
                        $file->sending_to_s3_tries = 0;
                        $file->sending_to_s3 = 1;
                        $file->save();

                        \App\Jobs\ManualUL_check_the_progress_of_ready_zip_to_s3::dispatch($this->case_id, $this->encrypted_case_id, $this->fstack, $this->the_current_computer_ip_initiating, $this->last_updated_by)->delay(now()->addSeconds($queue_delay_seconds_manualul));
                        return;
                    }else{
                        /** this will move the case_id to the back so that the next inline can be processed asap */
                        $file->sending_to_s3_tries = $file->sending_to_s3_tries + 1;
                        $file->save();

                        \App\Jobs\ManualUL_check_the_progress_of_ready_zip_to_s3::dispatch($this->case_id, $this->encrypted_case_id, $this->fstack, $this->the_current_computer_ip_initiating, $this->last_updated_by)->delay(now()->addSeconds($queue_delay_seconds_manualul));
                        return;
                    }
                }else{

                    /** send a message to the channel stating that the thing is ready uploaded */

                    /** its all then a matter of sending ONE message to the responsible with the case number and the job location */
                    /** maybe need to move everything -- later */
                    $content = '';
                    $case_id = '';
                    $xml_title_contents = '';
                    $xml_jobid_title = '';

                    $file = TaskUploadView::where('case_id', $remembering_caseId_doing)->first();
                    /**dump('processing === ' . $file->local);*/
                    /**dump($file->local);*/

                    $JOBFOLDER_DIR_SHARELOCATION_STRING = env('JOBFOLDER_DIR_SHARELOCATION_STRING');

                    //$content .= 'Zip '.$file->type.' extracted to ' . $folder . '';
                    if($content == ''){
                        $content .= '[BR]------------------------------------------------------[BR]';
                        $content .= '[URL=file:\\\\'.str_replace("/", "\\", $JOBFOLDER_DIR_SHARELOCATION_STRING . $file->case_id).'\\ready'.']'. str_replace("/", "\\", $JOBFOLDER_DIR_SHARELOCATION_STRING . $file->case_id).'\\ready'.'[/URL]';
                        $content .= '[BR]------------------------------------------------------[BR] UPLOADED to AWS S3';

                        $case_id = $file->case_id;
                        $xml_title_contents = $file->xml_title_contents;
                        $xml_jobid_title = $file->xml_jobid_title;
                    }

                    /**$file->state = 'notified';*/
                    /**$file->save();*/

                    $content .= '';
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
                        BTRXsendUploadJobUploadedtoS3ReadyMessage('CREATE_JOB_ZIP_ERROR', $message);
                    }else if($messenger_destination == 'ROCKETCHAT'){
                        //sendUploadJobUploadedtoS3ReadyMessage('CREATE_JOB_ZIP_ERROR', $message);
                        RCsendUploadJobUploadedtoS3ReadyMessage('CREATE_JOB_ZIP_ERROR', $message);
                        /** ROCKETCHAT */
                    }else{
                        /** this will not put the job back into the queue */
                        /** but if by some chance there was a job */
                        /** put it back in this queue to be checked again */
                        \App\Jobs\ManualUL_check_the_progress_of_ready_zip_to_s3::dispatch($this->case_id, $this->encrypted_case_id, $this->fstack, $this->the_current_computer_ip_initiating, $this->last_updated_by)->delay(now()->addSeconds($queue_delay_seconds_manualul));
                        return;
                    }

                    $file = TaskUpload::where('case_id', $remembering_caseId_doing)->first();

                    $file->state = 'uploaded to s3';
                    $file->sending_to_s3 = 3;
                    $file->save();

                    dump('uploaded to s3 success now performing clean up '.$remembering_caseId_doing.'');

                    /** we need to do some cleaning up and remove files off of the download upload server */
                    /** so that when we encounter problems the important logs are still there and un-cluttered */


                    /** BECAUSE THE USER IS www-data and the folders and files you are trying to remove are belonging to www-data you do not need SUDO priveledges.*/

                    /** places we need to remove from temporary upload folder + files*/
                    // var/www/src/alpine/storage/app/data_sdb_temp_upload/case_id and everything inside it 
                    //exec("rm -R var/www/src/alpine/storage/app/data_sdb_temp_upload/".$remembering_caseId_doing);
                    //$path = storage_path()."/app".config('s3br24.temp_upload_folder').$remembering_caseId_doing;
                    //$cmd = "rm -R ".$path;
                    //exec($cmd);

                    /** temporary upload folder zip log */
                    // var/www/src/alpine/storage/logs/data_sdb/zip_log/case_id_ready_zip.log
                    //exec("rm /var/www/src/alpine/storage/logs/data_sdb/zip_log/".$remembering_caseId_doing."_ready_zip.log");
                    $zip_log = storage_path()."/logs".config('s3br24.download_log') . 'zip_log';
                    $zip_log = $zip_log . '/' . $remembering_caseId_doing . '_ready_zip.log';
                    $cmd1 = "rm ".$zip_log;
                    dump('cmd1 '.$cmd1.'');
                    exec($cmd1);


                    /** temporary upload folder unzip to jobfolder/ready folder log */
                    // var/www/src/alpine/storage/logs/data_sdb/unzip_log/case_id_ready_unzip_jobfolder.log
                    //exec("rm /var/www/src/alpine/storage/logs/data_sdb/unzip_log/".$remembering_caseId_doing."_ready_unzip_jobfolder.log");
                    $unzip_log = storage_path()."/logs".config('s3br24.download_log') . 'unzip_log';
                    $unzip_log = $unzip_log . '/' . $remembering_caseId_doing . '_ready_zip.log';
                    $cmd2 = "rm ".$unzip_log;
                    dump('cmd2 '.$cmd2.'');
                    exec($cmd2);

                    /** temporary upload folder unzip to jobfolder/ready folder log */
                    // var/www/src/alpine/storage/logs/data_sdb/unzip_log/case_id_ready_unzip_jobfolder.log
                    //exec("rm /var/www/src/alpine/storage/logs/data_sdb/unzip_log/".$remembering_caseId_doing."_ready_unzip_jobfolder.log");
                    $unzip_jobfolder_log = storage_path()."/logs".config('s3br24.download_log') . 'unzip_log';
                    $unzip_jobfolder_log = $unzip_jobfolder_log . '/' . $remembering_caseId_doing . '_ready_unzip_jobfolder.log';
                    $cmd3 = "rm ".$unzip_jobfolder_log;
                    dump('cmd3 '.$cmd3.'');
                    exec($cmd3);

                    /** temporary ready zip folder + files*/
                    // var/www/src/alpine/storage/app/data_sdb_temp_zip/case_id
                    //exec("rm -R /var/www/src/alpine/storage/app/data_sdb_temp_zip/".$remembering_caseId_doing);
                    $tempZipFolder = storage_path()."/app".config('s3br24.temp_zip_folder').$remembering_caseId_doing;
                    $cmd4 = "rm -R ".$tempZipFolder;
                    dump('cmd4 '.$cmd4.'');
                    exec($cmd4);
                    
                    // var/www/src/alpine/storage/logs/data_sdb/s3_unzip_log/case_id_s3_ready_unzip_readyfolder.log
                    //exec("rm /var/www/src/alpine/storage/logs/data_sdb/s3_unzip_log/".$remembering_caseId_doing."_s3_ready_unzip_readyfolder.log");
                    $s3_unzip_log = storage_path()."/logs".config('s3br24.download_log') . 's3_unzip_log';
                    $s3_unzip_log = $s3_unzip_log . '/' . $remembering_caseId_doing . '_s3_ready_unzip_readyfolder.log';
                    $cmd5 = "rm ".$s3_unzip_log;
                    dump('cmd5 '.$cmd5.'');
                    exec($cmd5);

                    // var/www/src/alpine/storage/logs/data_sdb/s3_unzip_log/case_id_s3_ready_unzip_readyfolder_compare_with.log
                    //exec("rm /var/www/src/alpine/storage/logs/data_sdb/s3_unzip_log/".$remembering_caseId_doing."_s3_ready_unzip_readyfolder_compare_with.log");
                    $local_temp_upload_find_files_log = storage_path()."/logs".config('s3br24.download_log') . 's3_unzip_log';
                    $local_temp_upload_find_files_log = $local_temp_upload_find_files_log . '/' . $remembering_caseId_doing . '_s3_ready_unzip_readyfolder_compare_with.log';
                    $cmd6 = "rm ".$local_temp_upload_find_files_log;
                    dump('cmd6 '.$cmd6.'');
                    exec($cmd6);
                }
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
            'description' => 'ManualUL_check_the_progress_of_ready_zip_to_s3 failed()',
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
