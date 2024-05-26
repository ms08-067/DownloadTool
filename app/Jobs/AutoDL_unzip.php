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

class AutoDL_unzip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $tries = 10; /** if tries = 0 retry indefinitely */

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
        $this->onQueue('autodl_unzip');
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
        /** if a job is dispatched to this queue then it originally came from the AutoDL_unzipchecks queue */
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

            /** actualy unzipping after checking downloaded files */
            $file = TaskDownloadFile::select('*')->where('state', 'downloaded')->where('unzip', 1)->where('unzip_checks', 1)
            ->when($caseId, function($query) use ($caseId){
                return $query->where("case_id", $caseId);
            })
            ->when($caseIdtype, function($query) use ($caseIdtype){
                return $query->where("type", $caseIdtype);
            })->orderBy('updated_at', 'asc')->get();

            dump($file);
            if(empty($file) || $file == null) {
                /** if it cannot find the row.. how do we handle it then? usually the db values should already be there. reaching here should never be the case.. if the job was dispatched */
                /** only if it is a timing issue?? */
                /** for testing we let it pop fromt he queue and log it */
                Loggy::write('default', json_encode([
                    'success' => false,
                    'description' => 'function AutoDL_unzip handle() could not find row on taskDownloadFile table using query parameters',
                    'caseId' => $caseId,
                    'caseIdtype' => $caseIdtype
                ]));
                return;
            }


            /** want to do just for this type and case .. don't need to wait for the rest in the case .. */
            $all_case_files = TaskDownloadFile::select('*')
            ->when($caseId, function($query) use ($caseId){
                return $query->where("case_id", $caseId);
            })
            ->when($caseIdtype, function($query) use ($caseIdtype){
                return $query->where("type", $caseIdtype);
            })->where('state', '!=', 'notified')->get();


            /**dump($all_case_files);*/
            dump('actually_unzip');



            $remove_all_unzip_logs_from_this_case_id = null;
            foreach($all_case_files as $case_file_key => $case_downloadfile_details){

                $file = TaskDownloadFile::select('*')->where('id', $case_downloadfile_details->id)->first();
                dump('processing === ' . $file->local);
                dump($file->local);
                /** get existing retry count */
                $unzip_tries = $file->unzip_tries;


                if ($unzip_tries >= config('s3br24.unzip_retry_count')) {
                    $file->unzip = 3; /**unzip error*/
                    $file->save();

                    Loggy::write('default', json_encode([
                        'success' => false,
                        'description' => '$unzip_tries > config("s3br24.unzip_retry_count")',
                        'caseId' => $caseId,
                        'caseIdtype' => $caseIdtype
                    ]));

                    /** it fails but it will hold up the rest of the case files.. so how do overcome this hurdle? */

                    $file->unzip = 0;
                    $file->unzip_checks = 0;
                    $file->state = 'new';
                    $file->save();
                    /** lets try to send it back for full download again */
                    \App\Jobs\AutoDL_download::dispatch($file->case_id, $file->type)->delay(now()->addSeconds($queue_delay_seconds_autodl));
                    return;
                }

                /** unziping */
                $file->unzip = 1;
                $file->unzip_tries = $unzip_tries + 1;
                $file->save();

                /** replace with new retry count */
                $unzip_tries = $unzip_tries + 1;

                $dir = config('s3br24.download_temp_folder');

                /** final destination of the files */
                $jobDir = storage_path()."/app". $dir . 'job/' . $file->case_id . '_' . $file->type;
                dump('$jobDir');
                dump($jobDir);

                $dirZip = storage_path()."/app". $dir . 'job/' . $file->local;
                dump('$dirZip');
                dump($dirZip);

                /**dd(null);*/

                /**check zip file*/
                $zipArchive = new \ZipArchive();
                $tryOpeningZip = $zipArchive->open($dirZip);

                $unzipFolder = storage_path()."/app".config('s3br24.unzip_folder');
                /**dump($unzipFolder);*/
                $newFolder = $unzipFolder . $file->case_id . "/new/";
                $exampleFolder = $unzipFolder . $file->case_id . "/examples/";
                $readyFolder = $unzipFolder . $file->case_id . "/ready/";
                exec("mkdir -p " . $newFolder);
                exec("mkdir -p " . $exampleFolder);
                exec("mkdir -p " . $readyFolder);

                dump('$zipArchive->numFiles');
                dump($zipArchive->numFiles);

                if ($zipArchive->numFiles <= 0) { /** zip empty */
                    $file->unzip = 2; /** unzip complete */
                    $file->save();

                    /** when there are no files in the zip file here we can safely dispatch to the next queue  */
                    /** or even just set the state to notified is that better? */
                    /** what is the next queue to put it into after actually unzipping? extracted_check scan */
                    \App\Jobs\AutoDL_extractedcheckscan::dispatch($file->case_id, $file->type);
                    return;
                }
                $zipArchive_numFiles = $zipArchive->numFiles + 1;


                /**log for list files of zip*/
                $unzip_log = storage_path()."/logs".config('s3br24.download_log') . 'unzip_log';
                exec("mkdir -p $unzip_log");

                $progress_log = storage_path()."/logs".config('s3br24.download_log')."progress";
                exec("mkdir -p $progress_log");

                /** dd(null); */
                if ($file->type == 'new') {
                    $unzip_log = $unzip_log . '/' . $file->case_id . '_new.log';
                    $progress_log = $progress_log . '/' . $file->case_id . '_new_unzip_progress.log';
                } else if ($file->type == 'example') {
                    $unzip_log = $unzip_log . '/' . $file->case_id . '_example.log';
                    $progress_log = $progress_log . '/' . $file->case_id . '_example_unzip_progress.log';
                } else if ($file->type == 'ready') {
                    $unzip_log = $unzip_log . '/' . $file->case_id . '_ready.log';
                    $progress_log = $progress_log . '/' . $file->case_id . '_ready_unzip_progress.log';
                }else{
                    dump();
                }

                /** at this stage we need to check the unzip test log that was created for the zip for any indication that there were errors in which case need to trigger the download again.. and keep trying until we can be sure that the file is absolutely corrupt and not just due to network issues ? */
                /** since we are only doing one file per command */
                /** if the check of the log for this particular zip download has any error then attempt to download again .. and exit out reverting the row on taskdownloadfiles back to zip column 0  */
                /** until the next cron job */
                /** */

                if($file->state == 'unzipped'){
                    /** a zip from the same case coming back to be re-unzipped because not enough files in extracted directory if one of the other zips in the case is unzipped coming back here will not find the log for the unzipped file so errors make it go to the next file in the case*/
                    Loggy::write('default', json_encode([
                        'success' => false,
                        'description' => '($file->state == "unzipped")',
                        'caseId' => $caseId,
                        'caseIdtype' => $caseIdtype
                    ]));
                    /** try to redownload? is that the better way? */

                    /** THIS HAS MOST PROBABLY COME FROM THE MESSAGE AFTER MOVE TO DIRETORY QUEUE */
                    /**LET IS PASS THROUGHT THE REST OF THIS FUNCTION*/
                    /** to BE unzipped again */
                    $found_end_of_zip_test = '';
                }else{
                    $count_CRC_error = 0;
                    $try_to_download_again = false;
                    $found_end_of_zip_test = false;

                    if (File::isFile($unzip_log)) {
                        $file_as_array = array_filter(preg_split('/\n|\r\n?/', file_get_contents($unzip_log)));
                    }else{
                        $file_as_array = [];
                    }

                    foreach($file_as_array as $generic_index => $line){
                        if (strpos($line, 'bad CRC') !== false) {
                            /** file line has some indication of CRC error therefore count it */
                            $count_CRC_error++;
                        }
                        if (strpos($line, 'At least one error was detected in') !== false && strpos($line, $file->local) !== false) {
                            /** undeniably there was at least an error */
                            $try_to_download_again = true;
                            $found_end_of_zip_test = true;
                        }
                        if (strpos($line, 'No errors detected in compressed data of') !== false && strpos($line, $file->local) !== false) {
                            $found_end_of_zip_test = true;
                        }
                        if (strpos($line, 'At least one warning-error was detected in') !== false && strpos($line, $file->local) !== false) {
                            $found_end_of_zip_test = true;
                        }
                    }

                    dump('$count_CRC_error');
                    dump($count_CRC_error);
                    dump('$try_to_download_again');
                    dump($try_to_download_again);
                    dump('$found_end_of_zip_test');
                    dump($found_end_of_zip_test);
                }

                /** just a note that this will actually always run true because the zip test does not clear the job from the time it did the test. so it will find the test results */

                if($found_end_of_zip_test == false){
                    /** put it back in this queue to be checked again */
                    \App\Jobs\AutoDL_unzip::dispatch($file->case_id, $file->type)->delay(now()->addSeconds($queue_delay_seconds_autodl));
                    return;
                }

                /** keeping in mind the atempt amounts */
                /** when it error more than the specified amount of times then turn unzip to unsolveable unzip type. */

                if($try_to_download_again == true || $count_CRC_error > 0){
                    /** return the zip file row back to what is was so that it can be re-downloaded! not unzipped again because that would be a waste of time */
                    $file->unzip = 0;
                    $file->state = 'new';
                    $file->save();

                    /** if you do the redownload step MUST remove the row from the log which says it was previously downloaded successfully for the same CASE ID and local file name  */
                    $log = storage_path()."/logs".config('s3br24.download_log') . date('Y_m_d') . '_downloadLog.log';
                    $dir  = storage_path()."/app".config('s3br24.download_temp_folder') . 'job';
                    $searchString = "Download complete: " . $dir . "/" . $file->local;
                    $replaceString = "Download complete_but_retrying ".$unzip_tries.": " . $dir . "/" . $file->local;

                    $cmd = "sed -i 's%".$searchString."%".$replaceString."%g' " . $log;

                    exec($cmd);

                    /** need to remove the specific zip log file otherwise it cycles and never changes */
                    //exec("rm -R " . $unzip_log);

                    $remove_all_unzip_logs_from_this_case_id = true;

                    $message = array(
                        'title' => $file->case_id,
                        'content' => 'XML is OK but Zip downloaded has CRC ERROR (Cannot unzip). Will try to download again see if that helps.',
                        'link' => null,
                        'to' => config('br24config.rc_notify_usernames')
                    );

                    $messenger_destination = env("MESSENGER_DESTINATION", "ROCKETCHAT");
                    dump($messenger_destination);
                    if($messenger_destination == 'BITRIX'){

                        BTRXsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                    }else if($messenger_destination == 'ROCKETCHAT'){

                        RCsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                        /** ROCKETCHAT */
                    }else{
                        /** this will not put the job back into the queue */
                        /** but if by some chance there was a job */
                        /** put it back in this queue to be checked again */
                        \App\Jobs\AutoDL_download::dispatch($file->case_id, $file->type)->delay(now()->addSeconds($queue_delay_seconds_autodl));
                        return;
                    }
                    /** i still want the function to check CRC or errors for the rest of the zips of this caseID */

                    \App\Jobs\AutoDL_download::dispatch($file->case_id, $file->type)->delay(now()->addSeconds($queue_delay_seconds_autodl));
                    /** we dont return here so the final foreach can happen */
                }else{
                    if(File::exists($unzip_log)){
                        $file->unzip_checks = 1;
                        $file->state = 'unzipping';
                        $file->save();

                        dump($unzip_log . ' exist after exec');

                        /**dump('starting to unzip it officially');*/

                        /** there is no way to actually check if the unzip is done.. only on the next queue step which handles that */

                        /** all good... actually unzip it now to the correct folder */
                        /** want to be able to adjust this slightly if we can unzip it to another directory not the final jobFolder. */
                        /** have the system check if the unzips are good. */
                        /** and then move it to the job folder. */
                        /** so that way the jobfolder is never put there unless its ready to be notified. */

                        $searchString = 'inflating';
                        if(exec('grep ' . escapeshellarg($searchString) . ' ' . $unzip_log)) {
                            /** already inflating so do not trigger the unzip again. */

                            /** but keep a track of how many times .. because need to put it back in if it fails */
                        }else{
                            /** use option -oUU to skip UNICODE character check entirely */
                            /** use option -oO UTF8 to force character encoding on unzip filenames entirely */
                            /** to use pv to output progress bar you also need the amount of files in the archive and use in the -s option */
                            /** | pv -l -s 33 -pabtWf -i 0.5 >> unzip_log.log 2>> output-unzip_pv_progress.log */
                            if($file->type == 'example'){
                                $cmd = "unzip -o " . $dirZip . " -d " . $exampleFolder . ' >> '.$unzip_log;
                                //$cmd = "unzip -o " . $dirZip . " -d " . $exampleFolder . ' | pv -l -s '.$zipArchive_numFiles.' -pabtWf -i 0.5 >> '.$unzip_log . ' 2>> ' . $progress_log;
                                $folder = $exampleFolder;
                            }else if($file->type == 'new'){
                                $cmd = "unzip -o " . $dirZip . " -d " . $newFolder . ' >> '.$unzip_log;
                                //$cmd = "unzip -o " . $dirZip . " -d " . $newFolder . ' | pv -l -s '.$zipArchive_numFiles.' -pabtWf -i 0.5 >> '.$unzip_log . ' 2>> ' . $progress_log;
                                $folder = $newFolder;
                            }else if($file->type == 'ready'){
                                $cmd = "unzip -o " . $dirZip . " -d " . $readyFolder . ' >> '.$unzip_log;
                                //$cmd = "unzip -o " . $dirZip . " -d " . $readyFolder . ' | pv -l -s '.$zipArchive_numFiles.' -pabtWf -i 0.5 >> '.$unzip_log . ' 2>> ' . $progress_log;
                                $folder = $readyFolder;
                            }else{
                                dump('actually_unzip() encountered a type we did not expect');
                            }
                            dump($cmd);
                            exec($cmd);
                        }

                        /** here we should probably record the pid? */
                        /** but we don't so probably a TODO somewhere down the line */

                        /** because it hangs here */
                        /** when it is a big zip it take a bit of time ...*/


                        /** or if you could let it run in the background? */
                        /** when it gets killed it will hopefully be restarted by the init system */

                        /** we return here! we do not need to reach/ have the final foreach to happen */
                        \App\Jobs\AutoDL_extractedcheckscan::dispatch($file->case_id, $file->type);
                        return;
                    }else{
                        dump($unzip_log . ' does not exist ?? ');
                        /** wait for it */
                        \App\Jobs\AutoDL_unzip::dispatch($file->case_id, $file->type)->delay(now()->addSeconds($queue_delay_seconds_autodl));
                        return;
                    }
                }
            }


            /** at any point any of the files have error with the zip we need to halt the unzipping because then the zips are not all there at the same time for the checking function which also has to be modified */
            /** remove any unzip_logs associated with the caseID they will be generated again when the zip has been redownloaded on the next pass */
            if($remove_all_unzip_logs_from_this_case_id){
                foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                    /**log for list files of zip*/
                    $unzip_log = storage_path()."/logs".config('s3br24.download_log') . 'unzip_log';
                    exec("mkdir -p $unzip_log");

                    $progress_log = storage_path()."/logs".config('s3br24.download_log')."progress";
                    exec("mkdir -p $progress_log");

                    /** dd(null); */
                    if ($file->type == 'new') {
                        $unzip_log = $unzip_log . '/' . $file->case_id . '_new.log';
                        //$progress_log = $progress_log . '/' . $file->case_id . '_new_unziptest_progress.log';
                    } else if ($file->type == 'example') {
                        $unzip_log = $unzip_log . '/' . $file->case_id . '_example.log';
                        //$progress_log = $progress_log . '/' . $file->case_id . '_example_unziptest_progress.log';
                    } else if ($file->type == 'ready') {
                        $unzip_log = $unzip_log . '/' . $file->case_id . '_ready.log';
                        //$progress_log = $progress_log . '/' . $file->case_id . '_ready_unziptest_progress.log';
                    }else{
                        dump('actually_unzip() encountered a type we did not expect');
                    }
                    exec("rm -R " . $unzip_log);
                    //exec("rm -R " . $progress_log);
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
        $caseId = $this->case_id;
        $caseIdtype = $this->type;
        /**dump($caseId);*/
        /**dump($caseIdtype);*/

        /** simply log and let the tool move it to the failed jobs queue for later */
        Loggy::write('default', json_encode([
            'success' => false,
            'description' => 'AutoDL_unzip failed()',
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
