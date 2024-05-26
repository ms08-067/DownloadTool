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
use App\Events\NewAutoDLJobData;
use Debugbar;

use App\Models\TaskDownloadFile;
use App\Models\TaskDownload;
use App\Models\TaskDownloadView;
use App\Models\TaskUpload;
use App\Models\TaskUploadView;

class AutoDL_messageaftermovetodirectory implements ShouldQueue
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
        $this->onQueue('autodl_messageaftermovetodirectory');
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
        /** if a job is dispatched to this queue then it originally came from the AutoDL_messageaftermovetodirectorychecks queue */
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
                'caseId' => $caseId
            ]));
            throw new \Exception('no network connectivity or NAS/ RocketChat not connected/ mounts not mounted');
        }else{
            $queue_delay_seconds_autodl = DB::table('queue_delay_seconds_autodl')->first()->queue_delay_seconds;
            /** if it reaches here let us assume that it is already started sending via rsync */

            /** we wanted to send the files to the jobFolder as soon as the files have been unzipped and independently for each type chosen to manually download. */
            /** however the notification shoul only happen once all of the extracted zips have been moved there and checked for file consistency */

            /** since its one caseId with one type */
            /** eg example 10101010 when downloading new and example not ready */
            $all_case_files = TaskDownloadFile::where('case_id', $caseId)->where('state', '=', 'moving_to_jobFolder')->where('state', '!=', 'notified')->get();

            /**dump($all_case_files);*/
            /**dd(null);*/

            /** its all then a matter of sending ONE message to the responsible with the case number and the job location */
            /** maybe need to move everything -- later */
            $content = '';
            $attachment = '';
            $case_id = '';
            $xml_title_contents = '';
            $xml_jobid_title = '';
            $one_already_notified = false;
            foreach($all_case_files as $case_file_key => $case_downloadfile_details){

                $file = TaskDownloadFile::where('id', $case_downloadfile_details->id)->first();
                dump('processing === ' . $file->local);
                /**dump($file->local);*/

                if($file->state == 'notified'){
                    $one_already_notified = true;
                    break;
                }

                $JOBFOLDER_DIR_SHARELOCATION_STRING_ALTERNATE = env('JOBFOLDER_DIR_SHARELOCATION_STRING_ALTERNATE');
                $JOBFOLDER_DIR_SHARELOCATION_STRING_ALTERNATE = str_replace("[XxFOLDERNUMBERxX]", $file->case_id, $JOBFOLDER_DIR_SHARELOCATION_STRING_ALTERNATE);
                if($case_downloadfile_details->xml_tool_client == 1){
                    /**Germany*/
                    $JOBFOLDER_DIR_SHARELOCATION_STRING_ALTERNATE = str_replace("[XxFOLDERNAMExX]", "Germany", $JOBFOLDER_DIR_SHARELOCATION_STRING_ALTERNATE);
                }else if($case_downloadfile_details->xml_tool_client == 2){
                    /**Asia*/
                    $JOBFOLDER_DIR_SHARELOCATION_STRING_ALTERNATE = str_replace("[XxFOLDERNAMExX]", "Asia", $JOBFOLDER_DIR_SHARELOCATION_STRING_ALTERNATE);
                }else{
                    /**Default*/
                    $JOBFOLDER_DIR_SHARELOCATION_STRING_ALTERNATE = str_replace("[XxFOLDERNAMExX]", "Other", $JOBFOLDER_DIR_SHARELOCATION_STRING_ALTERNATE);
                }


                $JOBFOLDER_DIR_SHARELOCATION_STRING = env('JOBFOLDER_DIR_SHARELOCATION_STRING');
                $JOBFOLDER_DIR_SHARELOCATION_STRING_DECODED = env('JOBFOLDER_DIR_SHARELOCATION_STRING_DECODED');

                $JOBFOLDER_DIR_SHARELOCATION_STRING = str_replace("[XxFOLDERNUMBERxX]", $file->case_id, $JOBFOLDER_DIR_SHARELOCATION_STRING);
                $JOBFOLDER_DIR_SHARELOCATION_STRING_DECODED = str_replace("[XxFOLDERNUMBERxX]", $file->case_id, $JOBFOLDER_DIR_SHARELOCATION_STRING_DECODED);

                if($case_downloadfile_details->xml_tool_client == 1){
                    /**Germany*/
                    $JOBFOLDER_DIR_SHARELOCATION_STRING = str_replace("[XxFOLDERNAMExX]", "Germany", $JOBFOLDER_DIR_SHARELOCATION_STRING);
                    $JOBFOLDER_DIR_SHARELOCATION_STRING_DECODED = str_replace("[XxFOLDERNAMExX]", "Germany", $JOBFOLDER_DIR_SHARELOCATION_STRING_DECODED);
                }else if($case_downloadfile_details->xml_tool_client == 2){
                    /**Asia*/
                    $JOBFOLDER_DIR_SHARELOCATION_STRING = str_replace("[XxFOLDERNAMExX]", "Asia", $JOBFOLDER_DIR_SHARELOCATION_STRING);
                    $JOBFOLDER_DIR_SHARELOCATION_STRING_DECODED = str_replace("[XxFOLDERNAMExX]", "Asia", $JOBFOLDER_DIR_SHARELOCATION_STRING_DECODED);
                }else{
                    /**Default*/
                    $JOBFOLDER_DIR_SHARELOCATION_STRING = str_replace("[XxFOLDERNAMExX]", "Other", $JOBFOLDER_DIR_SHARELOCATION_STRING);
                    $JOBFOLDER_DIR_SHARELOCATION_STRING_DECODED = str_replace("[XxFOLDERNAMExX]", "Asia", $JOBFOLDER_DIR_SHARELOCATION_STRING_DECODED);
                }


                $formatUrl = str_replace("/", "\\", $JOBFOLDER_DIR_SHARELOCATION_STRING_ALTERNATE);
                $linkUrl = 'file:\\\\'.$formatUrl;

                /** So now we modify the message and handle the link as an attachment instead */
                /** changing the format of the bitrix api rest command */
                $attachment = $JOBFOLDER_DIR_SHARELOCATION_STRING . '||' . $JOBFOLDER_DIR_SHARELOCATION_STRING_DECODED . '||' . $linkUrl . '||' . $formatUrl;

                //$content .= 'Zip '.$file->type.' extracted to ' . $folder . '';

                if($content == ''){
                    //$blankImage = '[img width="0" height="0" alt="" title=""][/img]';
                    // $content .= '[BR]------------------------------------------------------[BR]';
                    // $content .= '[URL='.$JOBFOLDER_DIR_SHARELOCATION_STRING.']'. $JOBFOLDER_DIR_SHARELOCATION_STRING_DECODED.'[/URL]';
                    // $content .= '[BR]------------------------------------------------------[BR]';
                    // $content .= '[URL=https://www.bbcode.org/images/lubeck.jpg][img]https://www.bbcode.org/images/lubeck_small.jpg[/img][/URL]';
                    // $content .= '[BR]------------------------------------------------------[BR]';
                    // $content .= '<a href="'.$JOBFOLDER_DIR_SHARELOCATION_STRING.'" target="_blank">'.$JOBFOLDER_DIR_SHARELOCATION_STRING_DECODED.'</a>';
                    // $content .= '[BR]------------------------------------------------------[BR]';

                    // $content .= '[BR]------------------------------------------------------[BR]';
                    // $content .= '[URL=file:\\\\'.str_replace("/", "\\", $JOBFOLDER_DIR_SHARELOCATION_STRING_ALTERNATE . $file->case_id).']'. str_replace("/", "\\", $JOBFOLDER_DIR_SHARELOCATION_STRING_ALTERNATE . $file->case_id).'[/URL]';
                    // $content .= '[BR]------------------------------------------------------[BR]';


                    $case_id = $file->case_id;
                    $xml_title_contents = $file->xml_title_contents;
                    $xml_jobid_title = $file->xml_jobid_title;
                }

                /**$file->state = 'notified';*/
                /**$file->save();*/
            }
            $content .= '';

            dump('$one_already_notified');
            dump($one_already_notified);

            $result_of_folder_move = $this->physically_move_caseID_folder_from_unzip_directory_to_jobfolder_directory($case_id);

            dump('$result_of_folder_move');
            dump($result_of_folder_move);

            /** to force notifying of case Id*/
            $case_id_to_force_notify_manually = DB::table('bypass_filecountcheck_force_notify')->first();
            if($case_id_to_force_notify_manually){
                if($case_id == $case_id_to_force_notify_manually->case_id){
                    $result_of_folder_move = 'true';
                    $one_already_notified = false;
                    dump('bypassing filecountcheck for '.$case_id.'');
                }
            }

            if($result_of_folder_move === 'still running find command'){
                /** the move is inconclusive because it is still counting the amount of files and directories in both locations */
                /** put back into the queue */
                \App\Jobs\AutoDL_messageaftermovetodirectory::dispatch($caseId)->delay(now()->addSeconds($queue_delay_seconds_autodl));
                return;
            }


            if($result_of_folder_move == 'true'){
                /** we have to protect against the eventuallity that there is a power cut when this move is happening.. or if there is a network issue */
                $message = array(
                    'title' => $case_id,
                    'content' => $attachment,
                    'xml_title_contents' => $xml_title_contents,
                    'xml_jobid_title' => $xml_jobid_title,
                    'link' => null,
                    'to' => config('br24config.rc_notify_usernames')
                );
                dump('about to send rc message' . json_encode($message));
                /** this is where it goes wrong. */
                $messenger_destination = env("MESSENGER_DESTINATION", "ROCKETCHAT");
                dump("messenger_destination", $messenger_destination);
                if($messenger_destination == 'BITRIX'){
                    BTRXsendCreateJobMessage('', $message);
                }else if($messenger_destination == 'ROCKETCHAT'){
                    //sendCreateJobMessage('', $message);
                    RCsendCreateJobMessage('', $message);
                    /** ROCKETCHAT */
                }else{
                    /** this will not put the job back into the queue */
                    /** but if by some chance there was a job */
                    /** put it back in this queue to be checked again */
                    \App\Jobs\AutoDL_messageaftermovetodirectory::dispatch($caseId)->delay(now()->addSeconds($queue_delay_seconds_autodl));
                    return;
                }


                dump('rc/bitrix message sent!  cleanup about to be performmed');

                // foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                //     $file = TaskDownloadFile::where('id', $case_downloadfile_details->id)->first();
                //     /**dump('processing === ' . $file->local);*/
                //     /**dump($file->local);*/
                //     $file->state = 'notified';
                //     $file->save();
                // }


                /** since it is being handled by the worker it always seems to stop here.... but why? */

                /**at the end we can safely remove the case_id from the unzip_folder tree */
                $unzipFolder = storage_path()."/app".config('s3br24.unzip_folder').$case_id;
                dump('about to remove unzip folder for case_id => '. $case_id);
                if(File::exists($unzipFolder)){
                    dump($unzipFolder . ' exists');
                    exec("rm -R ".$unzipFolder, $output, $result);

                    dump('$output = ' .json_encode($output));
                    dump('$result = ' .json_encode($result));
                }



                /** perform clean up of all previous steps only at the end, that way when we need to reset to an earlier step whenever a step errors out we have the resources left intact */
                foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                    $file = TaskDownloadFile::where('id', $case_downloadfile_details->id)->first();

                    $s3Br24Config = config('s3br24');
                    $download_temp_folder = $s3Br24Config['download_temp_folder'];
                    $xml_file_dir = storage_path()."/app".$download_temp_folder . "xml/".$file->case_id .'.xml';
                    /**dump($xml_file_dir);*/
                    if(File::exists($xml_file_dir)){
                        /**dump($xml_file_dir . ' exists');*/
                        try {
                            File::delete($xml_file_dir);
                        } catch (FileNotFoundException $e) {
                            dd($e);
                        }
                    }


                    $unzipFolder = storage_path()."/app".config('s3br24.unzip_folder');
                    $newFolder = $unzipFolder . $file->case_id . "/new/";
                    $exampleFolder = $unzipFolder . $file->case_id . "/examples/";
                    $readyFolder = $unzipFolder . $file->case_id . "/ready/";

                    /**log for list files of zip*/
                    $unzip_log = storage_path()."/logs".config('s3br24.download_log') . 'unzip_log' ;
                    /**exec("mkdir -p $unzip_log");*/

                    if ($file->type == 'new') {
                        $unzip_log = $unzip_log . '/' . $file->case_id . '_new.log';
                        $folder = $newFolder;
                    } else if ($file->type == 'example') {
                        $unzip_log = $unzip_log . '/' . $file->case_id . '_example.log';
                        $folder = $exampleFolder;
                    } else if ($file->type == 'ready') {
                        $unzip_log = $unzip_log . '/' . $file->case_id . '_ready.log';
                        $folder = $readyFolder;
                    }
                    /**dump($unzip_log);*/
                    if(File::exists($unzip_log)){
                        /**dump($unzip_log . ' exists');*/
                        try {
                            File::delete($unzip_log);
                        } catch (FileNotFoundException $e) {
                            dd($e);
                        }
                    }

                    $dir = config('s3br24.download_temp_folder');
                    $dirZip = storage_path()."/app". $dir . 'job/' . $file->local;

                    /**dump('$dirZip');*/
                    /**dump($dirZip);*/
                    if(File::exists($dirZip)){
                        dump($dirZip . ' exists');
                        try {
                            File::delete($dirZip);
                        } catch (FileNotFoundException $e) {
                            dd($e);
                        }
                    }
                }


                /** we can add the case_id to the task_upload table now using the case_id just notified */
                try {
                    DB::beginTransaction();
                    $task_download = TaskDownload::where('case_id', $case_id)->first();
                    if($task_download){
                        $data = [
                            'case_id' => $case_id,
                            'state' => 'downloaded',
                            'try' => 0,
                            'time' => time(),
                            'initiator' => null,
                            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                        ];
                        TaskUpload::insert($data);
                    }

                    DB::commit();

                    foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                        $file = TaskDownloadFile::where('id', $case_downloadfile_details->id)->first();
                        $file->state = 'notified';
                        $file->save();
                        dump('set '.$case_downloadfile_details->type.' to notified');
                    }

                    //event(new NewAutoDLJobData('New Auto DL Job Id = '.$case_id));
                    //dump('NewAutoDLJobData event triggered');
                } catch(\Exception $e) {
                    Debugbar::addException($e);
                    throw $e;
                }
            }else{
                /** the contents are not the same.. so we have a problem so we allow the scheduled tasks to re do the function with the folder and file structure the same as the zip source */
                /** as some point we need to check if the move to the jobFolder pid is still alive.. if it isn't need to reset it to the unzipped state for all the rows with the same case_id */
                /** we update the state to still moving adjusting the updated_at column so the next time it tires the other caseId and not stick to repeating this caseID to keep things moving along rather than stuck */
                $last_key = $all_case_files->keys()->last();
                dump('$last_key');
                dump($last_key);

                foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                    $file = TaskDownloadFile::where('id', $case_downloadfile_details->id)->first();

                    dump($file);

                    $working_on_this_case_id = $file->case_id;
                    dump($working_on_this_case_id . ' still moving_to_jobFolder');
                    $file->state = 'moving_to_jobFolder';
                    $file->save();


                    /** sometimes the best way to fix the problem is to clear the jobFolder directory as well */
                    $check_if_pid_still_running_cmd = "ps aux | awk '{print $1 }' | grep ". $file->pid;
                    dump($check_if_pid_still_running_cmd);

                    $check_if_pid_still_running = exec($check_if_pid_still_running_cmd);

                    dump('$check_if_pid_still_running');
                    dump($check_if_pid_still_running);

                    dump("file[pid]");
                    dump($file['pid']);

                    if($check_if_pid_still_running == $file['pid']){

                        /** it is still running under the same pid.. lucky us, just let it keep going. */

                        /** throw an error so taht it can be reloaded back into the queue tecnically it is not an error */
                        Loggy::write('default', json_encode([
                            'success' => false,
                            'description' => 'check_if_pid_still_running == file pid',
                            'caseId' => $caseId,
                            //'caseIdtype' => $caseIdtype
                        ]));
                        //throw new \Exception('check_if_pid_still_running == file pid');

                        if($case_file_key == $last_key){
                            \App\Jobs\AutoDL_messageaftermovetodirectory::dispatch($caseId)->delay(now()->addSeconds($queue_delay_seconds_autodl));
                            return;
                        }
                    }else{
                        dump($working_on_this_case_id . ' rsync pid: '.$file['pid'].' is no longer running');
                        /** pid does not exists when the file check return false so we can reset it to the unzipped state to attempt to move again */
                        /** could also be that the command has finished ... */
                        /** if we trigger the unzip queue now it will never notify the job */

                        /** lets try not to remove the files in the jobFolder at all */
                        // $jobFolder = storage_path()."/app".config('s3br24.job_folder');
                        // exec('rm -r ' . $jobFolder . $working_on_this_case_id . '/new');
                        // exec('rm -r ' . $jobFolder . $working_on_this_case_id . '/example');

                        /** MAYBE THE SPEED IS TOO FAST? */

                        if($case_file_key == $last_key){
                            \App\Jobs\AutoDL_messageaftermovetodirectory::dispatch($caseId)->delay(now()->addSeconds($queue_delay_seconds_autodl));
                            return;
                        }


                        // $file->state = 'unzipped';
                        // $file->unzip = 2;
                        // $file->save();

                        // \App\Jobs\AutoDL_unzip::dispatch($file->case_id, $file->type)->delay(now()->addSeconds($queue_delay_seconds_autodl));
                        /** do not return mid iteration let it do for all items in the array */
                    }
                }
            }
        }
    }

    public function physically_move_caseID_folder_from_unzip_directory_to_jobfolder_directory($case_id)
    {
        /** now with the option to selectively choose which zips to download we have to refine the file count isolated to just what was chosen */
        /** what happens if there is a network issue */
        /** that interrupts the cifs mount */
        $all_case_files = TaskDownloadFile::where('case_id', $case_id)->where('state', '!=', 'notified')->get();

        /** up date the status here so that the updated_at changes so the scheduler can handle the next in the list */

        $xml_tool_client = null;
        foreach($all_case_files as $case_file_key => $case_downloadfile_details){
            $xml_tool_client = $case_downloadfile_details->xml_tool_client;
            $file = TaskDownloadFile::where('id', $case_downloadfile_details->id)->first();
            $file->state = 'moving_to_jobFolder';
            $file->save();
        }

        $jobFolder = storage_path()."/app".config('s3br24.job_folder');

        $jobFolderAsia = storage_path()."/app".config('s3br24.job_folder_asia');
        $jobFolderGermany = storage_path()."/app".config('s3br24.job_folder_germany');

        $unzipFolder = storage_path()."/app".config('s3br24.unzip_folder').$case_id;


        /** check that the amount of files and folders are exactly the same in the unzip and job folder before deleting the folder from the unzip folder */
        /** if it reaches here then it means it has completed the copy */
        /** if anyime there is a network issue or power cut then */



        dump('checking move from unzipfolder to jobfolder for case_id => '.$case_id);
        $inner_jobfolder = null;
        if($xml_tool_client == 1){
            $inner_jobfolder = $jobFolderGermany.$case_id;
        }else if($xml_tool_client == 2){
            $inner_jobfolder = $jobFolderAsia.$case_id;
        }else{
            $inner_jobfolder = $jobFolder.$case_id;
        }

        /** it seems to keep bunching here we can probably use the grep command to see if one of the find commands is already running for this case in which case don't need to repeat it */
        //$cmd1 = "ps aux | grep \"find\" | grep \"".$inner_jobfolder."\" | grep \"type\" | grep \"wc\" | wc -l";
        $cmd1 = "ps aux | grep \"".$inner_jobfolder."\" | grep \"type\" | grep \"wc\" | wc -l";
        dump($cmd1);
        exec($cmd1, $check_find_file_count_command_is_still_running_jobfolder_directory);
        
        //$cmd2 = "ps aux | grep \"find\" | grep \"".$inner_jobfolder."\" | grep \"mindepth\" | grep \"type\" | grep \"wc\" | wc -l";
        $cmd2 = "ps aux | grep \"".$inner_jobfolder."\" | grep \"mindepth\" | grep \"type\" | grep \"wc\" | wc -l";
        dump($cmd2);
        exec($cmd2, $check_find_directory_count_command_is_still_running_jobfolder_directory);

        dump('jobfolder ' . $check_find_file_count_command_is_still_running_jobfolder_directory[0] . "||" . $check_find_directory_count_command_is_still_running_jobfolder_directory[0]);


        $inner_unzipfolder = $unzipFolder;

        /** it seems to keep bunching here we can probably use the grep command to see if one of the find commands is already running for this case in which case don't need to repeat it */
        //$cmd3 = "ps aux | grep \"find\" | grep \"".$inner_unzipfolder."\" | grep \"type\" | grep \"wc\" | wc -l";
        $cmd3 = "ps aux | grep \"".$inner_unzipfolder."\" | grep \"type\" | grep \"wc\" | wc -l";
        dump($cmd3);
        exec($cmd3, $check_find_command_is_still_running_unzipfolder_directory);

        //$cmd4 = "ps aux | grep \"find\" | grep \"".$inner_unzipfolder."\" | grep \"mindepth\" | grep \"type\" | grep \"wc\" | wc -l";
        $cmd4 = "ps aux | grep \"".$inner_unzipfolder."\" | grep \"mindepth\" | grep \"type\" | grep \"wc\" | wc -l";
        dump($cmd4);
        exec($cmd4, $check_find_directory_count_command_is_still_running_unzipfolder_directory);

        dump('unzipfolder ' . $check_find_command_is_still_running_unzipfolder_directory[0] . "||" . $check_find_directory_count_command_is_still_running_unzipfolder_directory[0]);

        if($check_find_file_count_command_is_still_running_jobfolder_directory[0] >= 2 || $check_find_directory_count_command_is_still_running_jobfolder_directory[0] >= 2 || $check_find_command_is_still_running_unzipfolder_directory[0] >= 2 || $check_find_directory_count_command_is_still_running_unzipfolder_directory[0] >= 2){
            return 'still running find command';
        }else{


            $count_of_types_downloaded = count($all_case_files);

            $array_of_checks_per_type = [];
            /** if they select only part of the zips ... then this also needs to be split */
            foreach($all_case_files as $case_file_key => $case_downloadfile_details){
               
                /** the worker seems to encounter a problem if the find command cannot find the directory */
                /** so some checks to see if the directory exists or make sure that the type is also named correctly after the folder */

                if($case_downloadfile_details->type == 'example'){
                    $type = 'examples';
                }else{
                    $type = $case_downloadfile_details->type;
                }


                exec("find ".$inner_jobfolder."/".$type." -type f | wc -l", $number_of_files_in_jobfolder_directory);
                exec("find ".$inner_jobfolder."/".$type." -mindepth 1 -type d | wc -l", $number_of_folders_in_jobfolder_directory_min_depth);
                dump('number_of_files_in_jobfolder_directory '.$inner_jobfolder."/".$type. ' => ' . $number_of_files_in_jobfolder_directory[0]);
                dump('number_of_folders_in_jobfolder_directory_min_depth '.$inner_jobfolder."/".$type. ' => ' . $number_of_folders_in_jobfolder_directory_min_depth[0]);

                exec("find ".$inner_unzipfolder."/".$type." -type f | wc -l", $number_of_files_in_unzipfolder_directory);
                exec("find ".$inner_unzipfolder."/".$type." -mindepth 1 -type d | wc -l", $number_of_folders_in_unzipfolder_directory_min_depth);
                dump('number_of_files_in_unzipfolder_directory '.$inner_unzipfolder."/".$type. ' => ' . $number_of_files_in_unzipfolder_directory[0]);
                dump('number_of_folders_in_unzipfolder_directory_min_depth '.$inner_unzipfolder."/".$type. ' => ' . $number_of_folders_in_unzipfolder_directory_min_depth[0]);
                
                $counts_of_items_in_jobfolder = (int)$number_of_files_in_jobfolder_directory[0] + (int)$number_of_folders_in_jobfolder_directory_min_depth[0];
                $counts_of_items_in_unzipfolder = (int)$number_of_files_in_unzipfolder_directory[0] + (int)$number_of_folders_in_unzipfolder_directory_min_depth[0];
            
                $array_of_checks_per_type[$type]['counts_of_items_in_jobfolder'] = $counts_of_items_in_jobfolder;
                $array_of_checks_per_type[$type]['counts_of_items_in_unzipfolder'] = $counts_of_items_in_unzipfolder;
            }

            dump('$array_of_checks_per_type');
            dump($array_of_checks_per_type);

            $success_count_checks = 0;
            foreach($array_of_checks_per_type as $generic_type_key => $checks_per_type_details){
                if($checks_per_type_details['counts_of_items_in_jobfolder'] == $checks_per_type_details['counts_of_items_in_unzipfolder']){
                    $success_count_checks++;
                }
            }

            if($success_count_checks == $count_of_types_downloaded){
                return 'true';
            }else{
                
                /** managed to get to here meaning that the right conditions for checking the file counts but failed due to not the same so attempt finding Thumbs.db file and remove those */
                dump("executing find ".$inner_jobfolder." -type f -name Thumbs.db -delete command");
                exec("find ".$inner_jobfolder." -type f -name Thumbs.db -delete", $Thumbs_db_files_in_unzip_directory);
                dump('$Thumbs_db_files_in_unzip_directory'); /** having in double quotation marks actually tries to dump the variable need to use single quotes to output string with dollar sign */
                dump(json_encode($Thumbs_db_files_in_unzip_directory));
                
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
        $caseId = $this->case_id;
        /**dump($caseId);*/

        /** simply log and let the tool move it to the failed jobs queue for later */
        Loggy::write('default', json_encode([
            'success' => false,
            'description' => 'AutoDL_messageaftermovetodirectory failed()',
            'exception' => $e,
            'caseId' => $caseId
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
