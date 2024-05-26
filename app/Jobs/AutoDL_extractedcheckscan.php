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

class AutoDL_extractedcheckscan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 0; /** if tries = 0 retry indefinitely */

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
        $case_id,
        $type
    )
    {
        $this->onQueue('autodl_extractedcheckscan');
        $this->delay(1);

        $this->case_id = $case_id;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** if a job is dispatched to this queue then it originally came from the AutoDL_extractedcheckscanchecks queue */
        /** the zip needed to be downloaded again because it could not be unzipped... */

        $caseId = $this->case_id;
        $caseIdtype = $this->type;
        dump($caseId);
        dump($caseIdtype);

        /**for the job to fail there has to be an exception called.. */
        /**just throw an error when it fails to have the queue reload it to have it reprocess */
        /**throw new \Exception('Exception message');*/
        /**any return is considered a successful job!*/
        /**return;*/

        //$network_connectivity = $this->check_online();
        $network_connectivity = true;
        dump($network_connectivity);
        if(!$network_connectivity){
            /** throw an error and delay the job for a minute */
            Loggy::write('default', json_encode([
                'success' => false,
                'description' => 'no network connectivity or NAS/ RocketChat not connected/ mounts not mounted '. env("MESSENGER_DESTINATION", "ROCKETCHAT"),
                'caseId' => $caseId,
                'caseIdtype' => $caseIdtype
            ]));
            throw new \Exception('no network connectivity or NAS/ RocketChat not connected/ mounts not mounted');
        }else{
            $queue_delay_seconds_autodl = DB::table('queue_delay_seconds_autodl')->first()->queue_delay_seconds;

            $file = TaskDownloadFile::select('*')->where('state', 'unzipping')->where('unzip', 1)
            ->when($caseId, function($query) use ($caseId){
                return $query->where("case_id", $caseId);
            })
            ->when($caseIdtype, function($query) use ($caseIdtype){
                return $query->where("type", $caseIdtype);
            })->first();
            /** will always concentrate on the same zip ?*/

            if(empty($file) || $file == null) {
                /** if it cannot find the row.. how do we handle it then? usually the db values should already be there. reaching here should never be the case.. if the job was dispatched */
                /** only if it is a timing issue?? */
                /** for testing we let it pop fromt he queue and log it */
                Loggy::write('default', json_encode([
                    'success' => false,
                    'description' => 'function AutoDL_extractedcheckscan handle() could not find row on taskManualDownloadFile table using query parameters',
                    'caseId' => $caseId,
                    'caseIdtype' => $caseIdtype
                ]));
                return;
            }

            $s3Br24Config = config('s3br24');
            $download_temp_folder = $s3Br24Config['download_temp_folder'];
            $xml_file_dir = storage_path()."/app".$download_temp_folder . "xml/".$file->case_id .'.xml';

            /** the unzipping could have been finished or it is still running */
            /** */
            $job_folder = config('s3br24.job_folder');
            $dir = config('s3br24.download_temp_folder');

            /** final destination of the files */
            $jobDir = storage_path()."/app". $job_folder . 'job/' . $file->case_id . '/' . $file->type;
            dump('$jobDir');
            dump($jobDir);

            $dirZip = storage_path()."/app". $dir . 'job/' . $file->local;
            dump('$dirZip');
            dump($dirZip);

            $zipArchive = new \ZipArchive();
            $tryOpeningZip = $zipArchive->open($dirZip);

            $unzipFolder = storage_path()."/app".config('s3br24.unzip_folder');
            $newFolder = $unzipFolder . $file->case_id . "/new/";
            $exampleFolder = $unzipFolder . $file->case_id . "/examples/";
            $readyFolder = $unzipFolder . $file->case_id . "/ready/";

            /**log for list files of zip*/
            $unzip_log = storage_path()."/logs".config('s3br24.download_log') . 'unzip_log' ;

            if ($file->type == 'new') {
                $unzip_log = $unzip_log . '/' . $file->case_id . '_new.log';
                $folder = $newFolder;
            } else if ($file->type == 'example') {
                $unzip_log = $unzip_log . '/' . $file->case_id . '_example.log';
                $folder = $exampleFolder;
            } else if ($file->type == 'ready') {
                $unzip_log = $unzip_log . '/' . $file->case_id . '_ready.log';
                $folder = $readyFolder;
            } else{
                dump('check_extracted_files_with_zip_contents() encountered a value not expected');
            }

            $number_of_files_in_directory = exec("find ".$folder." -type f | wc -l");
            $number_of_folders_in_directory_min_depth = exec("find ".$folder." -mindepth 1 -type d | wc -l");

            dump("number_of_files_in_directory cmd => find ".$folder." -type f | wc -l");
            dump("number_of_folders_in_directory_min_depth cmd => find ".$folder." -mindepth 1 -type d | wc -l");
            dump('number_of_files_in_directory '.$folder . ' => ' . $number_of_files_in_directory);
            dump('number_of_folders_in_directory_min_depth '.$folder . ' => ' . $number_of_folders_in_directory_min_depth);

            dump('$zipArchive->numFiles => ' .$zipArchive->numFiles);

            /** if one encountered but it is not yet unzipped we have to wait longer */

            /** if the logs are not built consistently then the files never leave this loop and will eventually fail hard */
            $very_specific_count = $number_of_files_in_directory + $number_of_folders_in_directory_min_depth;
            /** NOTE: (dicovered 10FEB2022) thinking that $zipArchive->numFiles really only does count number of files and does not include the folder count anymore*/

            /** we may need to find a better way of counting directories and files in the zip. by using the zip test logfile */
            /** the problem is with $zipArchive->numFiles is that is counts some zip root folders as files. */

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

                                $length_string_portion = substr($line, 0, 9);
                                $length_string_portion = (int)$length_string_portion;

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
                $file->unzip = 2; /** unzip finished */
                $file->state = 'unzipped'; /** unzip finished */
                $file->file_count = (int) $number_of_files_in_directory;
                $file->save();

                /** while the thing is running on the next cron schedule we have another script that checks the task_download_files which are downloaded and are unzip == 1 and maybe another column processing */

                /** because the zip could be large the message gets sent before the unzipping process is done */
                /** should probably split this up into its own function with its own command that can also be added to cron job */
                /** make it a little fancier and do checks of the files extracted with the contents of the zip before deleteing the zip */
                /** when zipping is done send a message and clear up the xml and zip files */
                /** message should include the location of the extracted files. */
                /** this means that every one of the files gotten needs to be processed. */
                /** currently they are unzipped one by one. maybe doing the un zip for all that are ready to be downloaded for the same case */

                /** you have to think about the cron schedule */

                /** when it is fully checked need to go in to the unzip folder and iterate through all the folders and remove any offending characters from the folder names only */
                /** proably best to put it into a log file to interate through */

                /**log for list files of zip*/
                $unzip_folder_character_checks_log = storage_path()."/logs".config('s3br24.download_log') . 'unzip_log';
                exec("mkdir -p $unzip_folder_character_checks_log");

                $unzipFolder = storage_path()."/app".config('s3br24.unzip_folder');
                $newFolder = $unzipFolder . $file->case_id . "/new";
                $exampleFolder = $unzipFolder . $file->case_id . "/examples";
                $readyFolder = $unzipFolder . $file->case_id . "/ready";

                if ($file->type == 'new') {
                    $unzip_folder_character_checks_log = $unzip_folder_character_checks_log . '/' . $file->case_id . '_new_folder_name_checks.log';
                } else if ($file->type == 'example') {
                    $unzip_folder_character_checks_log = $unzip_folder_character_checks_log . '/' . $file->case_id . '_example_folder_name_checks.log';
                } else if ($file->type == 'ready') {
                    $unzip_folder_character_checks_log = $unzip_folder_character_checks_log . '/' . $file->case_id . '_ready_folder_name_checks.log';
                }else{
                    dump('check_extracted_files_with_zip_contents() encountered a value not expected');
                }

                /** test the zip that has been downloaded .... */
                if($file->type == 'example'){
                    $folder = $exampleFolder;
                    $cmd = 'find '.$folder.' -mindepth 1 -type d >> '.$unzip_folder_character_checks_log;
                }else if($file->type == 'new'){
                    $folder = $newFolder;
                    $cmd = 'find '.$folder.' -mindepth 1 -type d >> '.$unzip_folder_character_checks_log;
                }else if($file->type == 'ready'){
                    $folder = $readyFolder;
                    $cmd = 'find '.$folder.' -mindepth 1 -type d >> '.$unzip_folder_character_checks_log;
                }else{
                    dump('encountered a type we did not expect');
                    /** like a ready type we should just get out of the function */
                }

                dump($cmd);
                exec($cmd);

                /** hopefully the command finished writing to the log so that we can use the information contained inside */

                $fropen = fopen($unzip_folder_character_checks_log, 'r' );
                if ($fropen) {
                    while (($line = fgets($fropen)) !== false) {

                        /** check if the line contains a new line character that could affect the rsync to the jobFolder */
                        if(strpos($line, PHP_EOL) !== false ) {
                            $line = str_replace($folder.'/', '', $line);

                            $original_name_for_search = str_replace(' ', '\ ', $line);
                            $original_name_for_search = trim(preg_replace('/\n{2,}(?!.*\n{2})/s', ' ', $original_name_for_search));
                            $line = trim(preg_replace('/\s+/', ' ', $line));

                            if (strpos($line, '(') !== false || strpos($line, ')') !== false) {
                                //file line has some indication of CRC error therefore count it
                                $original_name_for_search = str_replace(')', '\)', $original_name_for_search);
                                $original_name_for_search = str_replace('(', '\(', $original_name_for_search);
                            }

                            $new_folder_name = $folder.'/"'.$line.'"';
                            $cmd = 'mv ' . $folder.'/'.$original_name_for_search .' '. ' ' . $new_folder_name;

                            dump($cmd);
                            exec($cmd);
                        }else{
                        }
                    }
                    fclose($fropen);
                } else {
                    /** error opening the log file. force download the zip again */
                    $try_to_download_again = true;
                }

                /** remove the file at the end */
                if(File::exists($unzip_folder_character_checks_log)){
                    try {
                        File::delete($unzip_folder_character_checks_log);
                    } catch (FileNotFoundException $e) {
                        dd($e);
                    }
                }


                $count_number_of_log_files_remain = 0;
                /** because the next step is important that only one rsync command is taking place because the rsync command is moving the whole root caseID folder to the jobfolder */
                /** THER CAN ONLY BE ONE */
                /** try to determine if the others are done checking and if so let this currently doing be the only trigger to place into the movetodirectory queue*/
                $unzip_folder_character_checks_log_new = storage_path()."/logs".config('s3br24.download_log') . 'unzip_log' . '/' . $file->case_id . '_new_folder_name_checks.log';
                if(File::exists($unzip_folder_character_checks_log_new)){
                    $count_number_of_log_files_remain++;
                }
                $unzip_folder_character_checks_log_example = storage_path()."/logs".config('s3br24.download_log') . 'unzip_log' . '/' . $file->case_id . '_example_folder_name_checks.log';
                if(File::exists($unzip_folder_character_checks_log_example)){
                    $count_number_of_log_files_remain++;
                }
                $unzip_folder_character_checks_log_ready = storage_path()."/logs".config('s3br24.download_log') . 'unzip_log' . '/' . $file->case_id . '_ready_folder_name_checks.log';
                if(File::exists($unzip_folder_character_checks_log_ready)){
                    $count_number_of_log_files_remain++;
                }

                dump('$count_number_of_log_files_remain');
                dump($count_number_of_log_files_remain);

                /** or and together check if there are any types in the same case that are behind */
                $all_case_files = TaskDownloadFile::select('*')
                ->when($caseId, function($query) use ($caseId){
                    return $query->where("case_id", $caseId);
                })
                ->whereNotIn('state', ['notified', 'unzipped', 'moving_to_jobFolder', 'moving_to_jobFolder.'])->get();

                $count_all_case_files_not_ready_to_move = count($all_case_files);
                dump('$count_all_case_files_not_ready_to_move');
                dump($count_all_case_files_not_ready_to_move);
                /** if you need to manually add it to the queue because the folder and number file checks always fails  just add it via tinker */
                /** using       \Queue::push(new App\Jobs\AutoDL_movetodirectory('11532718')); */
                if($count_number_of_log_files_remain == 0 && $count_all_case_files_not_ready_to_move == 0){
                    /**dispatch to the next step */

                    /** have to move the inside of the folder around so that the contents of the new zip are at the root of the folder and make sure the folder has the correct permissions for the users to read and write */

                    if($file->xml_tool_client == 2){
                        /** asia jobs only */
                        $cmd = 'rm -R ' . $exampleFolder;
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'rm -R ' . $readyFolder;
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'mv ' . $newFolder.'/new/ '. $unzipFolder.$file->case_id . "/orig_new/";
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'mv ' . $newFolder.'/example/ '. $unzipFolder.$file->case_id . "/orig_example/";
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'rm -R ' . $newFolder;
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'mv ' . $unzipFolder.$file->case_id .'/orig_new/ '. $unzipFolder . $file->case_id . "/1_new/";
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'mv ' . $unzipFolder.$file->case_id .'/orig_example/ '. $unzipFolder . $file->case_id . "/1_new/example/";
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'mkdir -p ' . $unzipFolder . $file->case_id . "/2_example/";
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'mkdir -p ' . $unzipFolder . $file->case_id . "/3_3D/";
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'mkdir -p ' . $unzipFolder . $file->case_id . "/4_render/";
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'mkdir -p ' . $unzipFolder . $file->case_id . "/6_check/";
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'chown 1026:2961179137 -R ' .$unzipFolder.$file->case_id.'/';
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'chmod 777 -R ' .$unzipFolder.$file->case_id.'/';
                        dump($cmd);
                        exec($cmd);
                    }else{
                        $cmd = 'rm -R ' . $exampleFolder;
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'mv ' . $newFolder.'/new/ '. $unzipFolder.$file->case_id . "/orig_new/";
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'mv ' . $newFolder.'/example/ '. $unzipFolder.$file->case_id . "/orig_example/";
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'rm -R ' . $newFolder;
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'mv ' . $unzipFolder.$file->case_id .'/orig_new/ '. $newFolder;
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'mv ' . $unzipFolder.$file->case_id .'/orig_example/ '. $unzipFolder . $file->case_id . "/example/";
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'chown 1026:2961179137 -R ' .$unzipFolder.$file->case_id.'/';
                        dump($cmd);
                        exec($cmd);

                        $cmd = 'chmod 777 -R ' .$unzipFolder.$file->case_id.'/';
                        dump($cmd);
                        exec($cmd);
                    }

                    /** files need to be moved to another */
                    \App\Jobs\AutoDL_movetodirectory::dispatch($file->case_id)->delay(now()->addSeconds($queue_delay_seconds_autodl));
                    return;
                }else{
                    /** we allow these to return and put all the responsibility in the tool to not mess up at this moment. since there is no way at the moment to reput back into the queue if it doesn't get placed in the movetodirectory queue */
                    return;
                }
            }else{
                /** we should probably wait until it is done */
                /** we should check if the process is still running? if it has stopped restart it? */
                /** if it keeps trying it will eventually need to be retried */

                /** probably could add another column that will count the number of times the check zip file number against extractionfolder files number */
                /** when it doesn't match so many times then it will return it to a state to redo the download ...*/

                /** to be able to give them some time to complete unzipping we add a retry count */
                /** once exhausted then reset the state to unzip again */
                $unzip_scan_folder_content_against_zip_contents_tries = $file->unzip_checks_tries;
                if ($unzip_scan_folder_content_against_zip_contents_tries >= config('s3br24.unzip_retry_count')) {
                    /** because reaching here means that the zip has been downloaded and files checked before unzip passed */
                    /** so we don't need to do those steps again we just need to unzip it */
                    $file->state = 'downloaded';
                    $file->unzip = 1; /**unzip error*/
                    $file->unzip_tries = 0;
                    $file->unzip_checks = 1;
                    $file->unzip_checks_tries = 0;
                    $file->save();

                    /** dispatch to the unzip step...*/
                    \App\Jobs\AutoDL_unzip::dispatch($file->case_id, $file->type);
                    return;
                }else{
                    /** increment the amount of times trying to scan the two locations for matching file amnounts */
                    $file->unzip_checks_tries = $unzip_scan_folder_content_against_zip_contents_tries + 1;
                    $file->save();
                    /** replace with new retry count */
                    /**$unzip_scan_folder_content_against_zip_contents_tries = $unzip_scan_folder_content_against_zip_contents_tries + 1;*/

                    /** throw an error instead of dispatching to the same queue which will retry after the retry_after value log it*/
                    Loggy::write('default', json_encode([
                        'success' => false,
                        'description' => 'function AutoDL_extractedcheckscan handle() $very_specific_count != $zipArchive->numFiles' .' |||| '. 'number_of_files_in_directory '.$folder . ' => ' . $number_of_files_in_directory . ' | ' . 'number_of_folders_in_directory_min_depth '.$folder . ' => ' . $number_of_folders_in_directory_min_depth . ' | ' .  '$zipArchive->numFiles => ' .$zipArchive->numFiles,
                        'caseId' => $caseId,
                        'caseIdtype' => $caseIdtype
                    ]));
                    \App\Jobs\AutoDL_extractedcheckscan::dispatch($file->case_id, $file->type)->delay(now()->addSeconds($queue_delay_seconds_autodl));
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
        $caseId = $this->case_id;
        $caseIdtype = $this->type;

        /** simply log and let the tool move it to the failed jobs queue for later */
        Loggy::write('default', json_encode([
            'success' => false,
            'description' => 'AutoDL_extractedcheckscan failed()',
            'exception' => $e,
            'caseId' => $caseId,
            'caseIdtype' => $caseIdtype
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

            if (strpos($response, 'ping: bad address') !== false || $response2 == false || strpos($response3, 'ping: bad address') !== false) {
                /** we are not online/ cannot access rocket chat/ cannot access NAS */
                return false;
            }else{

                /** it not just good enough to be able to ping the Network Attached Storage Device.. it has to be mounted */

                $jobFolderAsia = storage_path()."/app".config('s3br24.job_folder_asia');
                $jobFolderGermany = storage_path()."/app".config('s3br24.job_folder_germany');

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
            }
        }else{
            return false;
            /** we are disabling the queue from the db */
        }
        return true;
    }

}
