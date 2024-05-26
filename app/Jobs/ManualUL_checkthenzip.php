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

class ManualUL_checkthenzip implements ShouldQueue
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
        $this->onQueue('manualul_checkthenzip');
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
        /** if a job is dispatched to this queue then it originally came from the ManualUL_checkthenzipchecks queue */
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

        //$network_connectivity = $this->check_online();
        $network_connectivity = true;
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

            /** we will repurpose this function to be used by the scheduler */
            /** to look for the ones that have a state retry_zip */
            /** or just don't care */
            /** */
            $file = TaskUpload::where('case_id', $this->case_id)->first();

            /**dd($file);*/
            if (empty($file)) {
                throw new \Exception('case_id not found on tasks_uploads table');
            }

            /** how to determine if they have previously uploaded files for this caseID? */
            /** to know when to remove the files from the previous attempts ? */
            /** when does the try get increased? */

            if($file->try >= 1){
                /** we have to remove everything from the previous attempts.. logs and all */
                //$this->removeallpreviousattemptuploadfilesforthiscase($this->case_id);
            }





            $path = storage_path()."/app".config('s3br24.temp_upload_folder').$this->case_id;
            /**dd($path);*/
            if(!\File::exists($path)) {
                \File::makeDirectory($path, 0777, true, true); /**make directory if not exists */
            }


            /** what happens if the user uploads mutiple zips....? how does amazon handle that? It will unzip them to which ever subdirectory the zip is on and overwrite the files if they exist */
            /** we can't limit the supported files types to exclude a certain file type */
            /** are we going to have to rename the folders as well? No, its because the system created them */
            /** you need to check how many folders there are if there are two then you should not move contents one directory down */
            /** only if there is one folder and no files */

            // dump($this->case_id);

            $tempUploadFolder = storage_path()."/app".config('s3br24.temp_upload_folder');
            dump($tempUploadFolder);
            $inner_tempUploadfolder = $tempUploadFolder.$this->case_id;
            dump($inner_tempUploadfolder);
            if(!\File::exists($inner_tempUploadfolder)) {
                \File::makeDirectory($inner_tempUploadfolder, 0777, true, true); /**make directory if not exists */
            }

            $number_of_files_in_tempUploadfolder_directory_maxdepth = (int)exec("find ".$inner_tempUploadfolder." -maxdepth 1 -type f | wc -l");
            $number_of_folders_in_tempUploadfolder_directory_maxdepth = (int)exec("find ".$inner_tempUploadfolder." -mindepth 1 -maxdepth 1 -type d | wc -l");
            /** probably need to check that there is a folder in there too */

            dump($number_of_files_in_tempUploadfolder_directory_maxdepth);
            dump($number_of_folders_in_tempUploadfolder_directory_maxdepth);

            if($number_of_files_in_tempUploadfolder_directory_maxdepth == 0 && $number_of_folders_in_tempUploadfolder_directory_maxdepth == 1){
                /** we have to move everything down one directory */
                /** if there is a folder contained inside and no files we need to move all the contents of the folder to the root case_id folder */
                /** except if the case_id xml_title_contents LIKE %Web-Bilder% then just keep the folder structure and everything */
                $check_xml_title_contents = TaskDownloadView::where('case_id', $this->case_id)->first();

                if (strpos($check_xml_title_contents["xml_title_contents"], 'Web-Bilder') !== false) {
                    /** this is the job where the folder structure is important */
                }else{
                    /** fo this you need to have the folder name */
                    $folder_name_to_eventually_remove_when_empty = exec("find ".$inner_tempUploadfolder." -mindepth 1 -maxdepth 1 -type d");
                    $folder_name_to_eventually_remove_when_empty = str_replace($inner_tempUploadfolder."/", "", $folder_name_to_eventually_remove_when_empty);
                    $folder_name_to_eventually_remove_when_empty = str_replace(" ", "\\ ", $folder_name_to_eventually_remove_when_empty);
                    dump($folder_name_to_eventually_remove_when_empty);

                    dump($inner_tempUploadfolder."/".$folder_name_to_eventually_remove_when_empty."/*");
                    dump($inner_tempUploadfolder);

                    $cmd = "mv -v ".$inner_tempUploadfolder."/".$folder_name_to_eventually_remove_when_empty."/* ".$inner_tempUploadfolder;
                    dump($cmd);
                    exec($cmd);
                    
                    /** and remove the source folder if it is empty so it is no longer part of the file contents */
                    $cmd2 = "rm -R ".$inner_tempUploadfolder."/".$folder_name_to_eventually_remove_when_empty;
                    dump($cmd2);
                    exec($cmd2);
                }
            }





            /**dump('done but now its the time to zip the directory and check that it can be unzipped before sending it to the queue for s3');*/
            /** we proably want to hang onto the zip for alittle while */
            /** you were thinking of the scenario where they uploaded something.. */
            $existing_try_count = $file->try;
            $file->try = $existing_try_count + 1;
            $file->save();


            $tempZipFolder = storage_path()."/app".config('s3br24.temp_zip_folder').$this->case_id;
            if(!\File::exists($tempZipFolder)) {
                \File::makeDirectory($tempZipFolder, 0777, true, true); /**make directory if not exists */
            }

            /** log for list files of zip */
            $zip_log = storage_path()."/logs".config('s3br24.download_log') . 'zip_log';
            exec("mkdir -p $zip_log");

            $zip_log = $zip_log . '/' . $this->case_id . '_ready_zip.log';

            /**sleep(2);*/
            $cmd3 = "(cd ".$inner_tempUploadfolder."; zip -r ".$tempZipFolder."/ready.zip ./*) >> $zip_log";
            dump($cmd3);
            exec($cmd3);


            /** for some reason after it finishes zipping it somehow errors.. */

            /** we test the zip if it can be unzipped or if there are errors */
            /**log for list files of zip*/
            $unzip_log = storage_path()."/logs".config('s3br24.download_log') . 'unzip_log';
            exec("mkdir -p $unzip_log");

            /** dd(null); */

            $unzip_log = $unzip_log . '/' . $this->case_id . '_ready_zip.log';
            dump('$unzip_log');
            dump($unzip_log);

            $dirZip = $tempZipFolder."/ready.zip";

            /** query zip contents and export that info to a log file */
            dump('unzip -l '.$dirZip.' >> '.$unzip_log);



            $searchString = 'testing:';
            if(File::exists($unzip_log)){
                dump($unzip_log . ' exists');
                try {
                    if(exec('grep ' . escapeshellarg($searchString) . ' ' . $unzip_log)) {
                        /** if it has been tested then don't need to test again*/
                    }else{
                        /** perform test of zip */
                        exec("unzip -l $dirZip >> $unzip_log");

                        /** test the zip that has been downloaded .... */
                        $cmd = "unzip -t " . $dirZip . ' >> '.$unzip_log;
                        dump('$cmd');
                        dump($cmd);

                        exec($cmd);
                    }                
                } catch (FileNotFoundException $e) {
                    dd($e);
                }
            }else{
                dump($unzip_log . ' does not exist');
                /** perform test of zip */
                exec("unzip -l $dirZip >> $unzip_log");

                /** test the zip that has been downloaded .... */
                $cmd = "unzip -t " . $dirZip . ' >> '.$unzip_log;
                dump('$cmd');
                dump($cmd);

                exec($cmd);
            }




            // $searchString = 'testing:';
            // if(exec('grep ' . escapeshellarg($searchString) . ' ' . $unzip_log)) {
            //     /** if it has been tested then don't need to test again*/
            // }else{
            //     /** perform test of zip */
            //     exec("unzip -l $dirZip >> $unzip_log");

            //     /** test the zip that has been downloaded .... */
            //     $cmd = "unzip -t " . $dirZip . ' >> '.$unzip_log;
            //     dump('$cmd');
            //     dump($cmd);

            //     exec($cmd);
            // }





            /**dd(null);*/

            $count_CRC_error = 0;
            $try_to_zip_again = false;
            $fropen = fopen($unzip_log, 'r' );

            $unzip_log_to_string = '';
            if ($fropen) {
                while (($line = fgets($fropen)) !== false) {
                    if (strpos($line, 'bad CRC') !== false) {
                        /** file line has some indication of CRC error therefore count it */
                        $count_CRC_error++;
                    }
                    if (strpos($line, 'At least one error was detected in') !== false && strpos($line, $file->local) !== false) {
                        /** undeniably there was at least an error */
                        $try_to_zip_again = true;
                    }

                    $unzip_log_to_string .= $line .'<br>';
                }
                fclose($fropen);
            } else {
                /** error opening the log file. force download the zip again */
                $try_to_zip_again = true;
            }

            /** we need to keep track of the state changes through the lifetime of the files. */
            /** to be able to upload to s3 on the schedule */
            /** since being able to access the upload tool and selecting files doesn't necessarily mean it will immediately get to the bucket. */
            /** plus we need to check that its there and can also be able to send a message. */

            if($try_to_zip_again == true || $count_CRC_error > 0){
                /** return the zip file row back to what is was so that it can be re-downloaded! not unzipped again because that would be a waste of time */
                /** we let the scheduler handle it */
                $file->try = 0;
                $file->state = 'retry_zip';
                $file->save();

                /** we have to alert them that there is a problem with the files being zipped */
                /** since it is the worker handling it we just put it back to be zipped again */
                \App\Jobs\ManualUL_checkthenzip::dispatch($this->case_id, $this->encrypted_case_id, $this->fstack, $this->the_current_computer_ip_initiating, $this->last_updated_by)->delay(now()->addSeconds($queue_delay_seconds_manualul));
                return;
            }else{
                /** set the states back to the default so that the scheduler can take over again */

                /** what do we do with the previous attempt files? that are already on the jobFolder/ready folder and then s3? */
                
                $file->state = 'zipped';
                
                $file->move_to_jobfolder = 0;
                $file->move_to_jobfolder_tries = 0;
                $file->sending_to_s3 = 0;
                $file->sending_to_s3_tries = 0;

                $current_time_stamp = Carbon::now()->timestamp;

                if($file->initiator == ''){
                    $file->initiator = '['.$this->the_current_computer_ip_initiating.'-'.$this->last_updated_by.'-'.$current_time_stamp.']';
                }else{
                    $file->initiator = $file->initiator.",".'['.$this->the_current_computer_ip_initiating.'-'.$this->last_updated_by.'-'.$current_time_stamp.']';
                }

                $file->save();
            }




            /** since we have the list of files in the stack can we compare that with the contents on the zip */


            /** using zip archive check that the new zip contents are the same as the temp_upload directory */

            $number_of_files_in_directory = exec("find ".$inner_tempUploadfolder." -type f | wc -l");
            $number_of_folders_in_directory_min_depth = exec("find ".$inner_tempUploadfolder." -mindepth 1 -type d | wc -l");


            $zipArchive = new \ZipArchive();
            $tryOpeningZip = $zipArchive->open($dirZip);

            /**dd(null);*/

            /** if one encountered but it is not yet unzipped we have to wait longer */

            /** if the logs are not built consistently then the files never leave this loop and will eventually fail hard */
            $very_specific_count = $number_of_files_in_directory + $number_of_folders_in_directory_min_depth;


            $number_of_files_in_filestack = $this->fstack;


            dump('number_of_files_in_directory '.$inner_tempUploadfolder . ' => ' . $number_of_files_in_directory);
            dump('number_of_folders_in_directory_min_depth '.$inner_tempUploadfolder . ' => ' . $number_of_folders_in_directory_min_depth);
            dump('$zipArchive->numFiles => ' .$zipArchive->numFiles);
            dump('$number_of_files_in_filestack => ' .$number_of_files_in_filestack);

            if($number_of_files_in_directory == $number_of_files_in_filestack){
                /** all good */
            }else{
                /** it should never come to be because the event is only triggered if all the files are successfully uploaded by the plugin */
                /** cannot explain but this happened so how do you recover from that? as it keeps getting put back */
                Loggy::write('default', json_encode([
                    'success' => false,
                    '$number_of_files_in_directory' => $number_of_files_in_directory,
                    '$number_of_folders_in_directory_min_depth' => $number_of_folders_in_directory_min_depth,
                    '$zipArchive->numFiles' => $zipArchive->numFiles,
                    '$number_of_files_in_filestack' => $number_of_files_in_filestack,
                    'description' => 'ManualUL_checkthenzip $number_of_files_in_directory != $number_of_files_in_filestack',
                ]));
                \App\Jobs\ManualUL_checkthenzip::dispatch($this->case_id, $this->encrypted_case_id, $this->fstack, $this->the_current_computer_ip_initiating, $this->last_updated_by)->delay(now()->addSeconds($queue_delay_seconds_manualul));
                return;
            }

            if($very_specific_count == $zipArchive->numFiles || $number_of_files_in_directory == $zipArchive->numFiles){
                /** all good */
            }else{
                /** it should never come to be because the event is only triggered if all the files are successfully uploaded by the plugin */
                Loggy::write('default', json_encode([
                    'success' => false,
                    '$number_of_files_in_directory' => $number_of_files_in_directory,
                    '$number_of_folders_in_directory_min_depth' => $number_of_folders_in_directory_min_depth,
                    '$zipArchive->numFiles' => $zipArchive->numFiles,
                    '$number_of_files_in_filestack' => $number_of_files_in_filestack,
                    'description' => 'ManualUL_checkthenzip $very_specific_count != $zipArchive->numFiles',
                ]));
                \App\Jobs\ManualUL_checkthenzip::dispatch($this->case_id, $this->encrypted_case_id, $this->fstack, $this->the_current_computer_ip_initiating, $this->last_updated_by)->delay(now()->addSeconds($queue_delay_seconds_manualul));
                return;                
            }

            /** close at the end */
            $zipArchive->close();

            /** save accurate number of files saved to db upon no error */
            $file->custom_output_real = $number_of_files_in_filestack;
            $file->save();        

            /** lastly if we reach here with everything we expect to find we can dispatch to the next queue */
            \App\Jobs\ManualUL_afterzip_movetojobfolderreadydirectory::dispatch($this->case_id, $this->encrypted_case_id, $this->fstack, $this->the_current_computer_ip_initiating, $this->last_updated_by)->delay(now()->addSeconds($queue_delay_seconds_manualul));
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
            'description' => 'ManualUL_checkthenzip failed()',
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
