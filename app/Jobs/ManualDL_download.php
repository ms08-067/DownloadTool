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

class ManualDL_download implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $tries = 10; /** if tries = 0 retry indefinitely */
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
        $case_id,
        $type
    )
    {
        $this->onQueue('manualdl_download');
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
        /** if a job is dispatched to this queue then it originally came from the ManualDL_unzipchecks queue */
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

        $network_connectivity = $this->check_online();
        //$network_connectivity = true;
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
            $queue_delay_seconds_manualdl = DB::table('queue_delay_seconds_manualdl')->first()->queue_delay_seconds;
            
            $br24Config = config('br24config');
            
            /** because we now use a more fine grain logging if a download take longer than a day the download check will no look in the right log file */
            /** perhaps better to use week number but it can happen also as the week number changes or even month number changes its just un avoiable */
            /** and sometimes it is downloaded and aria2c reports that the file size is different .. whats going on there? */

            //$log = storage_path()."/logs".config('s3br24.manual_download_log') . date('Y_m_d') . '_downloadLog.log';
            $dir  = storage_path()."/app".config('s3br24.manual_download_temp_folder') . 'job';
            $progress_path = storage_path()."/logs".config('s3br24.manual_download_log')."progress";

            if (!File::isFile($progress_path)) {
                $path = storage_path().'/logs'.config('s3br24.manual_download_log').'progress';
                File::makeDirectory($path, 0777, true, true); /** make directory */
            }

            // if (!File::isFile($log)) {
            //     $path = storage_path().'/logs'.config('s3br24.manual_download_log');
            //     File::makeDirectory($path, 0777, true, true); /** make directory */
            //     File::put($log, '');
            // }

            if (!File::isFile($dir)) {
                $path = storage_path().'/app'.config('s3br24.manual_download_temp_folder')."job";
                File::makeDirectory($path, 0777, true, true); /** make directory */
            }

            $count = TaskManualDownloadFile::select('*')->where('state', 'downloading')->count();
            /**dump($count);*/
            if ($count >= 10) {
                $limit = 0;
            } else {
                $limit = 10 - $count;
            }

            $filesDownload = TaskManualDownloadFile::where('state', 'new')
            ->when($caseId, function($query) use ($caseId){
                return $query->where("case_id", $caseId);
            })
            ->when($caseIdtype, function($query) use ($caseIdtype){
                return $query->where("type", $caseIdtype);
            })->offset(0)->limit($limit)->get();
            /**dump($filesDownload);*/

            /** now we have to separate the download logs per type as well.. :/ */


            foreach ($filesDownload as $file) {
                /**remove any line from the log file which has clues about previous download attempts */

                $aria2c_download_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress/". explode(".", $file['local'])[0].'_aria2c_downloadLog.log';


                if(File::exists($aria2c_download_log)){
                    $searchString = "Download complete: " . $dir . "/" . $file['local'];
                    $replaceString = "Download complete_but_redownloading: " . $dir . "/" . $file['local'];
                    $string_replace_cmd = "sed -i 's%".$searchString."%".$replaceString."%g' " . $aria2c_download_log;
                    dump($string_replace_cmd);
                    exec($string_replace_cmd);
                }

                if ($file['from'] == $br24Config['from_s3']) {
                    $cmd="aria2c --allow-overwrite=true --piece-length=100M --max-connection-per-server=8 --file-allocation=none --max-concurrent-downloads=8 --split=8 --log-level=notice --max-tries=12  --retry-wait=15 --console-log-level=notice --download-result=full --human-readable=true --log={$aria2c_download_log}  --dir={$dir} " . '"' . $file['url'] . '"';
                    /**--console-log-level=<LEVEL>    Set log level to output to console.  LEVEL is either debug, info, notice, warn or error. Default: notice */
                    /**--download-result=<OPT> Set <OPT> to default, full, hide. Default: default */
                } else {
                    //$cmd="aria2c --ftp-user=" . $ftp_user_name_zip . " --ftp-passwd=" . $ftp_user_pass_zip . " --allow-overwrite=true --piece-length=100M --max-connection-per-server=8 --file-allocation=none --max-concurrent-downloads=8 --split=8 --log-level=notice --max-tries=12  --retry-wait=15 --log={$log}  --dir={$dir} " . '"' . $file['url'] . '"';
                    dump('from asia-ftp');
                }
                dump($cmd);


                /** has to be per file */
                $progress_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress/". explode(".", $file['local'])[0].'_progressLog.log';

                //$pid = exec($cmd . " > /dev/null & echo $!;", $output);
                $pid = exec($cmd . " > {$progress_log} &", $output);
                $file->pid = $pid;
                $file->state = 'downloading';
                $file->save();
            }



            /** what happens if it get cut in the middle due to electricty cut or network restart? */
            /** will the system wait until it has completed before or will it kill prematurely */
            /** proabably a good place to do a time check */
            /** its will hopefully write to the log when it gets aborted but a power cut */

            /**check download complete*/
            $filesDownloading = TaskManualDownloadFile::select('*')->where('state', 'downloading')
            ->when($caseId, function($query) use ($caseId){
                return $query->where("case_id", $caseId);
            })
            ->when($caseIdtype, function($query) use ($caseIdtype){
                return $query->where("type", $caseIdtype);
            })->get();
            /**dd($filesDownloading);*/

            dump('$filesDownloading');
            dump($filesDownloading);
            if(empty($filesDownloading) || $filesDownloading == null) {
                /** if it cannot find the row.. how do we handle it then? usually the db values should already be there. reaching here should never be the case.. if the job was dispatched */
                /** only if it is a timing issue?? */
                /** for testing we let it pop fromt he queue and log it */
                /** we need to throw an error so that it can be handled again on another retry */
                throw new \Exception('cannot find the row where it is downloading');
            }

            $date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . "-30 minutes"));

            foreach ($filesDownloading as $f) {
                $searchString = "Download complete: " . $dir . "/" . $f['local'];
                /**dd(exec('grep ' . escapeshellarg($searchString) . ' ' . $log));*/
                $aria2c_download_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress/". explode(".", $f['local'])[0].'_aria2c_downloadLog.log';

                if(File::exists($aria2c_download_log)){
                    if(exec('grep ' . escapeshellarg($searchString) . ' ' . $aria2c_download_log)) {
                        $f->state = 'downloaded';
                        $f->save();

                        dump('dispatching to ManualDL_unzipchecks ' .$f->case_id . ' ' .$f->type);
                        /** here we can safely dispatch to the unzipCheck queue */
                        \App\Jobs\ManualDL_unzipchecks::dispatch($f->case_id, $f->type);
                    } else {

                        if ($f['updated_at'] <= $date) {
                            $searchStringAborted = "Download aborted. URI=" . $f['url'];
                            if(exec('grep ' . escapeshellarg($searchStringAborted) . ' ' . $aria2c_download_log)) {
                                $f->state = 'new';
                                $f->save();

                                \App\Jobs\ManualDL_download::dispatch($f->case_id, $f->type)->delay(now()->addSeconds($queue_delay_seconds_manualdl));
                            }else{
                                $carbon_now = Carbon::now();
                                $retry_after_3hours = Carbon::createFromFormat('Y-m-d H:i:s', $f['updated_at'])->addHours(3);
                                if ($f['state'] == 'downloading' && $carbon_now->greaterThan($retry_after_3hours)) {
                                    /** the download still hasn't finished downloading after three hours since the download was started */
                                    /** can we still check if the pid is still running? */
                                    $check_if_pid_still_running = exec("ps aux | awk '{print $1 }' | grep ". $f['pid']);
                                    if($check_if_pid_still_running == $f['pid']){
                                        /** it is still running under the same pid.. lucky us, just let it keep going. */
                                        /** still need to put in back into the download queue */
                                        \App\Jobs\ManualDL_download::dispatch($f->case_id, $f->type)->delay(now()->addSeconds($queue_delay_seconds_manualdl));
                                    }else{
                                        /** pid does not exists so we can reset it to the new state to be redownloaded */
                                        $f->state = 'new';
                                        $f->save();
                                        \App\Jobs\ManualDL_download::dispatch($f->case_id, $f->type)->delay(now()->addSeconds($queue_delay_seconds_manualdl));
                                    }
                                }
                            }
                        }
                        /** we dispatch again to the queue */
                        \App\Jobs\ManualDL_download::dispatch($f->case_id, $f->type)->delay(now()->addSeconds($queue_delay_seconds_manualdl));
                    }
                }else{
                    dump('$aria2c_download_log does not exist');
                    /** if the file does not exist then the scheduler has just started? need to check again in a minute? */
                    \App\Jobs\ManualDL_download::dispatch($f->case_id, $f->type)->delay(now()->addSeconds($queue_delay_seconds_manualdl));
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
            'description' => 'ManualDL_download failed()',
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
