<?php

namespace App\Repositories;

use App\Models\TaskDownload;
use App\Models\TaskDownloadFile;
use App\Models\TaskUpload;
use App\Models\TaskUploadFile;
use App\Models\TaskUploadView;
use App\Models\Settings;
use App\Models\Task;
use App\Models\TasksFiles;
use App\Models\TasksReadyFolders;
use App\Models\TransferDataLog;
use App\Models\XmlFile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Debugbar;
use App\Models\TaskManualDownload;
use App\Models\TaskManualDownloadFile;

use App\Models\TaskManualUpload;
use App\Models\TaskManualUploadFile;
use App\Models\TaskManualUploadView;

/**
 * Class ManualJobRepository
 *
 * @author anhlx412@gmail.com
 * @package App\Repositories
 */
class ManualJobRepository extends Repository
{
    public $taskDownloadFile;
    public $taskDownload;
    public $taskUploadFile;
    public $TaskUploadView;
    public $taskUpload;
    public $settings;
    public $task;
    public $tasksFiles;
    public $tasksReadyFolders;
    protected $limit = 10;
    public $taskManualDownloadFile;
    public $taskManualDownload;
    public $taskManualUploadFile;
    public $taskManualUpload; 
    public $TaskManualUploadView;   

    /**
     * ManualJobRepository constructor.
     * @param TaskDownloadFile $taskDownloadFile
     * @param TaskDownload $taskDownload
     * @param TaskUpload $taskUpload
     * @param TaskUploadFile $taskUploadFile 
     * @param TaskUploadView $taskUploadView 
     * @param Settings $settings
     * @param Task $task
     * @param TasksFiles $tasksFiles
     * @param TasksReadyFolders $tasksReadyFolders
     * @param TaskManualDownloadFile $taskManualDownloadFile
     * @param TaskManualDownload $taskManualDownload
     */
    public function __construct(
        TaskDownloadFile $taskDownloadFile,
        TaskDownload $taskDownload,
        TaskUpload $taskUpload,
        TaskUploadFile $taskUploadFile,
        TaskUploadView $taskUploadView,
        Settings $settings,
        Task $task,
        TasksFiles $tasksFiles,
        TasksReadyFolders $tasksReadyFolders,
        TaskManualDownloadFile $taskManualDownloadFile,
        TaskManualDownload $taskManualDownload,
        TaskManualUploadFile $taskManualUploadFile,
        TaskManualUpload $taskManualUpload,
        TaskManualUploadView $taskManualUploadView

    ){
        // $this->taskDownloadFile = $taskDownloadFile;
        // $this->taskDownload = $taskDownload;

        // $this->taskUploadFile = $taskUploadFile;
        // $this->taskUpload = $taskUpload;
        // $this->taskUploadView = $taskUploadView;

        $this->settings = $settings;
        $this->task = $task;
        $this->tasksFiles = $tasksFiles;
        $this->tasksReadyFolders = $tasksReadyFolders;

        $this->taskManualDownloadFile = $taskManualDownloadFile;
        $this->taskManualDownload = $taskManualDownload;
        
        $this->taskManualUploadFile = $taskManualUploadFile;
        $this->taskManualUpload = $taskManualUpload;
        $this->taskManualUploadView = $taskManualUploadView;

    }

    public function reDownloadWhenServerRestart()
    {
        $date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . "-30 minutes"));
        /**dump($date);*/
        $filesDownloading = $this->taskManualDownloadFile
        ->where('state', 'downloaded')
        ->whereNotIn('unzip', [0])
        //->where('updated_at', '<=', $date)
        ->get();

        /**dd($filesDownloading);*/

        foreach ($filesDownloading as $file_part_of_case) {

            /** probably want to do the whole case again */
            $all_case_files = $this->taskManualDownloadFile->where('case_id', $file_part_of_case->case_id)->get();
            foreach($all_case_files as $case_file_key => $case_downloadfile_details){

                $file = $this->taskManualDownloadFile->where('id', $case_downloadfile_details->id)->first();

                $file->state = 'new';
                $file->unzip = 0;
                $file->unzip_tries = 0;
                $file->unzip_checks = 0;
                $file->unzip_checks_tries = 0;
                $file->updated_at = date("Y-m-d H:i:s");
                $file->save();
                // $processNum = checkProcessInServer($file['url']);
                // if ($processNum < 3) {
                // }
            }
        }
    }


    public function reDownloadErrorzip()
    {
        $date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . "-30 minutes"));
        /**dump($date);*/
        $filesDownloading = $this->taskManualDownloadFile
        //->whereIn('state', ['unzipping', 'unzipped'])
        ->whereIn('unzip', [3])
        //->where('updated_at', '<=', $date)
        ->get();

        /**dd($filesDownloading);*/

        foreach ($filesDownloading as $file_part_of_case) {

            /** probably want to do the whole case again */
            $all_case_files = $this->taskManualDownloadFile->where('case_id', $file_part_of_case->case_id)->get();
            foreach($all_case_files as $case_file_key => $case_downloadfile_details){

                $file = $this->taskManualDownloadFile->where('id', $case_downloadfile_details->id)->first();
                
                $file->state = 'new';
                $file->unzip = 0;
                $file->unzip_tries = 0;
                $file->unzip_checks = 0;
                $file->unzip_checks_tries = 0;
                $file->updated_at = date("Y-m-d H:i:s");
                $file->save();
                // $processNum = checkProcessInServer($file['url']);
                // if ($processNum < 3) {
                // }
            }
        }
    }


    public function reDownloadLongUnzipManual()
    {
        $date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . "-30 minutes"));
        /**dump($date);*/
        $filesDownloading = $this->taskManualDownloadFile
        ->whereIn('state', ['unzipping', 'unzipped'])
        //->whereIn('unzip', [3])
        //->where('updated_at', '<=', $date)
        ->get();

        /**dd($filesDownloading);*/

        foreach ($filesDownloading as $file_part_of_case) {

            /** probably want to do the whole case again */
            $all_case_files = $this->taskManualDownloadFile->where('case_id', $file_part_of_case->case_id)->get();
            foreach($all_case_files as $case_file_key => $case_downloadfile_details){

                $file = $this->taskManualDownloadFile->where('id', $case_downloadfile_details->id)->first();
                
                $file->state = 'new';
                $file->unzip = 0;
                $file->unzip_tries = 0;
                $file->unzip_checks = 0;
                $file->unzip_checks_tries = 0;
                $file->updated_at = date("Y-m-d H:i:s");
                $file->save();
                // $processNum = checkProcessInServer($file['url']);
                // if ($processNum < 3) {
                // }
            }
        }
    }

    /**
     * Download job to local server.
     */
    public function download()
    {
        // $ftp = config('asiaftp');
        // $ftp_user_name_zip = $ftp['ftp']['zip']['username'];
        // $ftp_user_pass_zip = $ftp['ftp']['zip']['password'];

        $br24Config = config('br24config');
        
        /** because we now use a more fine grain logging if a download take longer than a day the download check will no look in the right log file */
        /** perhaps better to use week number but it can happen also as the week number changes or even month number changes its just un avoiable */
        /** and sometimes it is downloaded and aria2c reports that the file size is different .. whats going on there? */

        $log = storage_path()."/logs".config('s3br24.manual_download_log') . date('Y_m_d') . '_downloadLog.log';
        $dir  = storage_path()."/app".config('s3br24.manual_download_temp_folder') . 'job';
        $progress_path = storage_path()."/logs".config('s3br24.manual_download_log')."progress";

        if (!File::isFile($progress_path)) {
            $path = storage_path().'/logs'.config('s3br24.manual_download_log').'progress';
            File::makeDirectory($path, 0777, true, true); /** make directory */
        }

        if (!File::isFile($log)) {
            $path = storage_path().'/logs'.config('s3br24.manual_download_log');
            File::makeDirectory($path, 0777, true, true); /** make directory */
            File::put($log, '');
        }

        if (!File::isFile($dir)) {
            $path = storage_path().'/app'.config('s3br24.manual_download_temp_folder')."job";
            File::makeDirectory($path, 0777, true, true); /** make directory */
        }

        $count = $this->taskManualDownloadFile->where('state', 'downloading')->count();
        /**dump($count);*/
        if ($count >= 10) {
            $limit = 0;
        } else {
            $limit = $this->limit - $count;
        }

        $filesDownload = $this->taskManualDownloadFile->where('state', 'new')->offset(0)->limit($limit)->get();
        /**dump($filesDownload);*/
        foreach ($filesDownload as $file) {
            if ($file['from'] == $br24Config['from_s3']) {
                $cmd="aria2c --allow-overwrite=true --piece-length=100M --max-connection-per-server=8 --file-allocation=none --max-concurrent-downloads=8 --split=8 --log-level=notice --max-tries=12  --retry-wait=15 --console-log-level=notice --download-result=full --human-readable=true --log={$log}  --dir={$dir} " . '"' . $file['url'] . '"';
                /**--console-log-level=<LEVEL>    Set log level to output to console.  LEVEL is either debug, info, notice, warn or error. Default: notice */
                /**--download-result=<OPT> Set <OPT> to default, full, hide. Default: default */
            } else {
                //$cmd="aria2c --ftp-user=" . $ftp_user_name_zip . " --ftp-passwd=" . $ftp_user_pass_zip . " --allow-overwrite=true --piece-length=100M --max-connection-per-server=8 --file-allocation=none --max-concurrent-downloads=8 --split=8 --log-level=notice --max-tries=12  --retry-wait=15 --log={$log}  --dir={$dir} " . '"' . $file['url'] . '"';
                dump('from asia-ftp');
            }
            /**dump($cmd);*/

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
        $filesDownloading = $this->taskManualDownloadFile->where('state', 'downloading')->get();
        /**dd($filesDownloading);*/
        $date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . "-30 minutes"));

        foreach ($filesDownloading as $f) {
            $searchString = "Download complete: " . $dir . "/" . $f['local'];
            /**dd(exec('grep ' . escapeshellarg($searchString) . ' ' . $log));*/

            if(exec('grep ' . escapeshellarg($searchString) . ' ' . $log)) {
                $f->state = 'downloaded';
                $f->save();

                dump('dispatching to ManualDL_unzipchecks ' .$f->case_id . ' ' .$f->type);
                /** here we can safely dispatch to the unzipCheck queue */
                \App\Jobs\ManualDL_unzipchecks::dispatch($f->case_id, $f->type);

            } else {
                if ($f['updated_at'] <= $date) {
                    $searchStringAborted = "Download aborted. URI=" . $f['url'];
                    if(exec('grep ' . escapeshellarg($searchStringAborted) . ' ' . $log)) {
                        $f->state = 'new';
                        $f->save();
                    }else{
                        $carbon_now = Carbon::now();
                        $retry_after_3hours = Carbon::createFromFormat('Y-m-d H:i:s', $f['updated_at'])->addHours(3);
                        if ($f['state'] == 'downloading' && $carbon_now->greaterThan($retry_after_3hours)) {
                            /** the download still hasn't finished downloading after three hours since the download was started */
                            /** can we still check if the pid is still running? */
                            $check_if_pid_still_running = exec("ps aux | awk '{print $1 }' | grep ". $f['pid']);
                            if($check_if_pid_still_running == $f['pid']){
                                /** it is still running under the same pid.. lucky us, just let it keep going. */
                            }else{
                                /** pid does not exists so we can reset it to the new state to be redownloaded */
                                $f->state = 'new';
                                $f->save();
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Unzip job & copy job from 215 to 220
     */
    public function unZipChecks()
    {
        // $fileMount = "/home/itadmin/data/webroot/ismount";
        // if (!checkMount($fileMount)) {
        //     return;
        // }
        /** run as many times as there are downloaded rows */
        /** get the first downloaded file detail */

        /** find a caseID that has all zips downloaded and treat the case as a whole */

        $file = $this->taskManualDownloadFile->where('state', 'downloaded')->where('unzip', 0)->first();

        /**dump($file);*/
        if (empty($file)) {
            return;
        }
        /**dd($file);*/

        /**dump('processing === ' . $file->local);*/
        /**dump($file->local);*/
        /** get existing retry count */
        $unzip_tries = $file->unzip_tries;
        /**dd($unzip_tries);*/


        if($file->xml_title_contents == ''){
            /**<jobTitle><![CDATA[CGI Raumerstellung + Staging (br24_sarraj) Matze]]></jobTitle>*/
            /** get the jobTitle from the xml and store it with the zip to be used with the RC message */
            $s3Br24Config = config('s3br24');
            $download_temp_folder = $s3Br24Config['manual_download_temp_folder'];

            $downloaded_xml_file_dir = storage_path()."/app".$download_temp_folder . "xml/".$file->case_id. '.xml';

            if(File::exists($downloaded_xml_file_dir)){
                /**dump($unzip_log . ' exists');*/
                try {
                    $xml_tool_client = '';
                    $xml_title_contents = '';
                    $xml_jobidtitle_contents = '';
                    $xml_deliverytime_contents = '';
                    $xml_jobInfo_contents = '';
                    $anchor_for_xml_jobInfo = false;
                    $xml_jobInfoProduction_contents = '';
                    $anchor_for_xml_jobInfoProduction = false;

                    $fropen = fopen($downloaded_xml_file_dir, 'r' );
                    if ($fropen) {
                        while (($line = fgets($fropen)) !== false) {
                            if (strpos($line, '"') !== false) {
                                $line = str_replace('"', "'", $line);
                            }

                            if (strpos($line, '<clientToolId>') !== false && strpos($line, '</clientToolId>') !== false) {
                                $xml_tool_client = preg_replace('/\s\s+/', '', trim(str_replace("</clientToolId>", "", str_replace("<clientToolId>", "", $line))));
                            }

                            if (strpos($line, '<jobTitle>') !== false) {
                                $xml_title_contents = preg_replace('/\s\s+/', '', ltrim(str_replace("<![CDATA[", "", str_replace("]]></jobTitle>", "", str_replace("<jobTitle>", "", $line)))));
                            }
                            if (strpos($line, '<jobIdTitle>') !== false) {
                                $xml_jobidtitle_contents = preg_replace('/\s\s+/', '', trim(str_replace("</jobIdTitle>", "", str_replace("<jobIdTitle>", "", $line))));
                            }
                            if (strpos($line, '<deliveryProduction>') !== false) {
                                $xml_deliverytime_contents = preg_replace('/\s\s+/', '', trim(str_replace("</deliveryProduction>", "", str_replace("<deliveryProduction>", "", $line))));
                            }     

                            if(is_null($anchor_for_xml_jobInfo)){
                                /** already process bypass */
                            }else{
                                if($anchor_for_xml_jobInfo == true){
                                    /** we are still scanning for the closing bracket */
                                    /**if the line also contains the closing bracket then we know how to handle */
                                    if (strpos($line, '</jobInfo>') !== false) {
                                        /** it found the closing breacket */
                                        $anchor_for_xml_jobInfo = null;
                                        $xml_jobInfo_contents .= preg_replace('/\s\s+/', '<br>', ltrim(str_replace("<![CDATA[", "", str_replace("]]></jobInfo>", "", str_replace("<jobInfo>", "", $line)))));
                                    }else{
                                        /** it has not found the closing bracket yet */
                                        /** let us check the lines by content */
                                        if(preg_replace('/\s\s+/', '', ltrim($line)) == "\n" || preg_replace('/\s\s+/', '', ltrim($line)) == "\r\n") {
                                            $xml_jobInfo_contents .= '<br>';
                                        }else{
                                            $xml_jobInfo_contents .= preg_replace('/\s\s+/', '', ltrim(str_replace("<![CDATA[", "", str_replace("]]></jobInfo>", "", str_replace("<jobInfo>", "", $line))))) . '<br>';
                                        }
                                    }
                                }else{
                                    /** we are looking for the opening bracket */
                                    if (strpos($line, '<jobInfo>') !== false && strpos($line, '</jobInfo>') !== false) {
                                        /** the line has both the opening and closing bracket */
                                        /** so do not need to configure the anchor variable */
                                        $anchor_for_xml_jobInfo = null;
                                        $xml_jobInfo_contents .= preg_replace('/\s\s+/', '<br>', ltrim(str_replace("<![CDATA[", "", str_replace("]]></jobInfo>", "", str_replace("<jobInfo>", "", $line)))));
                                    }else{
                                        /** the closing bracket is on another line */
                                        /** make use of the anchor variable to tell the tool to focus for the closing bracket and keep the formatting of the text blob */
                                        if (strpos($line, '<jobInfo>') !== false) {
                                            $anchor_for_xml_jobInfo = true;
                                            $xml_jobInfo_contents .= preg_replace('/\s\s+/', '<br>', ltrim(str_replace("<![CDATA[", "", str_replace("]]></jobInfo>", "", str_replace("<jobInfo>", "", $line)))));
                                        }
                                    }
                                }
                            }


                            if(is_null($anchor_for_xml_jobInfoProduction)){
                                /** already process bypass */
                            }else{
                                if($anchor_for_xml_jobInfoProduction == true){
                                    /** we are still scanning for the closing bracket */
                                    /**if the line also contains the closing bracket then we know how to handle */
                                    if (strpos($line, '</jobInfoProduction>') !== false) {
                                        /** it found the closing breacket */
                                        $anchor_for_xml_jobInfoProduction = null;
                                        $xml_jobInfoProduction_contents .= preg_replace('/\s\s+/', '<br>', ltrim(str_replace("<![CDATA[", "", str_replace("]]></jobInfoProduction>", "", str_replace("<jobInfoProduction>", "", $line)))));
                                    }else{
                                        /** it has not found the closing bracket yet */
                                        /** let us check the lines by content */
                                        if(preg_replace('/\s\s+/', '', ltrim($line)) == "\n" || preg_replace('/\s\s+/', '', ltrim($line)) == "\r\n") {
                                            $xml_jobInfoProduction_contents .= '<br>';
                                        }else{
                                            $xml_jobInfoProduction_contents .= preg_replace('/\s\s+/', '', ltrim(str_replace("<![CDATA[", "", str_replace("]]></jobInfoProduction>", "", str_replace("<jobInfoProduction>", "", $line))))) . '<br>';
                                        }
                                    }
                                }else{
                                    /** we are looking for the opening bracket */
                                    if (strpos($line, '<jobInfoProduction>') !== false && strpos($line, '</jobInfoProduction>') !== false) {
                                        /** the line has both the opening and closing bracket */
                                        /** so do not need to configure the anchor variable */
                                        $anchor_for_xml_jobInfoProduction = null;
                                        $xml_jobInfoProduction_contents .= preg_replace('/\s\s+/', '<br>', ltrim(str_replace("<![CDATA[", "", str_replace("]]></jobInfoProduction>", "", str_replace("<jobInfoProduction>", "", $line)))));
                                    }else{
                                        /** the closing bracket is on another line */
                                        /** make use of the anchor variable to tell the tool to focus for the closing bracket and keep the formatting of the text blob */
                                        if (strpos($line, '<jobInfoProduction>') !== false) {
                                            $anchor_for_xml_jobInfoProduction = true;
                                            $xml_jobInfoProduction_contents .= preg_replace('/\s\s+/', '<br>', ltrim(str_replace("<![CDATA[", "", str_replace("]]></jobInfoProduction>", "", str_replace("<jobInfoProduction>", "", $line)))));
                                        }
                                    }
                                }
                            }
                        }
                        fclose($fropen);
                    }

                    $file->xml_tool_client = $xml_tool_client;
                    $file->xml_title_contents = $xml_title_contents;
                    $file->xml_jobid_title = $xml_jobidtitle_contents;
                    $file->xml_deliverytime_contents = $xml_deliverytime_contents;

                    $file->xml_jobinfo = $xml_jobInfo_contents;
                    $file->xml_jobinfoproduction = $xml_jobInfoProduction_contents;

                    $file->save();
                } catch (FileNotFoundException $e) {
                    dd($e);
                }
            }
        }




        if ($unzip_tries > config('s3br24.manual_unzip_retry_count')) {
            $file->unzip = 3; /**unzip error*/
            $file->save();
            return;
        }

        /** unziping */
        $file->unzip = 1;
        $file->unzip_tries = $unzip_tries + 1;
        $file->save();

        /** replace with new retry count */
        $unzip_tries = $unzip_tries + 1;




        $dir = config('s3br24.manual_download_temp_folder');

        /** final destination of the files */
        $jobDir = storage_path()."/app". $dir . 'job/' . $file->case_id . '_' . $file->type;
        /**dump('$jobDir');*/
        /**dump($jobDir);*/



        $dirZip = storage_path()."/app". $dir . 'job/' . $file->local;

        /**dump('$dirZip');*/
        /**dump($dirZip);*/

        /**dd(null);*/

        /**check zip file*/
        $zipArchive = new \ZipArchive();
        $tryOpeningZip = $zipArchive->open($dirZip);

        /**dump('$tryOpeningZip');*/
        /**dump($tryOpeningZip);*/

        if ($tryOpeningZip !== TRUE) {
            //$file->unzip = 3; /**unzip error*/
            //$file->save();
            $file->unzip = 0;
            $file->state = 'new';
            $file->save();

            switch (intval($tryOpeningZip))
            {
                case 0:
                $error_code_desc = 'No error';

                case 1:
                $error_code_desc = 'Multi-disk zip archives not supported';

                case 2:
                $error_code_desc = 'Renaming temporary file failed';

                case 3:
                $error_code_desc = 'Closing zip archive failed';

                case 4:
                $error_code_desc = 'Seek error';

                case 5:
                $error_code_desc = 'Read error';

                case 6:
                $error_code_desc = 'Write error';

                case 7:
                $error_code_desc = 'CRC error';

                case 8:
                $error_code_desc = 'Containing zip archive was closed';

                case 9:
                $error_code_desc = 'No such file';

                case 10:
                $error_code_desc = 'File already exists';

                case 11:
                $error_code_desc = 'Can\'t open file';

                case 12:
                $error_code_desc = 'Failure to create temporary file';

                case 13:
                $error_code_desc = 'Zlib error';

                case 14:
                $error_code_desc = 'Malloc failure';

                case 15:
                $error_code_desc = 'Entry has been changed';

                case 16:
                $error_code_desc = 'Compression method not supported';

                case 17:
                $error_code_desc = 'Premature EOF';

                case 18:
                $error_code_desc = 'Invalid argument';

                case 19:
                $error_code_desc = 'Not a zip archive';

                case 20:
                $error_code_desc = 'Internal error';

                case 21:
                $error_code_desc = 'Zip archive inconsistent';

                case 22:
                $error_code_desc = 'Can\'t remove file';

                case 23:
                $error_code_desc = 'Entry has been deleted';

                default:
                $error_code_desc = 'An unknown error has occurred('.intval($tryOpeningZip).')';
            }

            if ($unzip_tries == config('s3br24.manual_unzip_retry_count')) {
                /** should we try to download again and see if that helps? */
                $message = array(
                    'title' => $file->case_id,
                    'content' => 'XML is OK but Zip is ERROR (Cannot open to unzip)',
                    'error_code_desc' => $error_code_desc,
                    'link' => null,
                    'to' => config('br24config.rc_notify_usernames')
                );

                    //sendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                RCsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
            }
            return;
        }


        /** don't create the folder for the job on the unzipcheck step */
        // $jobFolder = storage_path()."/app".config('s3br24.manual_job_folder');
        // dump($jobFolder);

        // $newFolder = $jobFolder . $file->case_id . "/new/";
        // $exampleFolder = $jobFolder . $file->case_id . "/examples/";
        // exec("mkdir -p " . $newFolder);
        // exec("mkdir -p " . $exampleFolder);

        if ($zipArchive->numFiles <= 0) { /** zip empty */
            $file->unzip = 2; /** unzip complete */
            $file->save();
            return;
        }


        /**dd('checkpoint');*/


        /**log for list files of zip*/
        $unzip_log = storage_path()."/logs".config('s3br24.manual_download_log') . 'unzip_log';
        exec("mkdir -p $unzip_log");

        /** dd(null); */

        if ($file->type == 'new') {
            $unzip_log = $unzip_log . '/' . $file->case_id . '_new.log';
        } else if ($file->type == 'example') {
            $unzip_log = $unzip_log . '/' . $file->case_id . '_example.log';
        } else if ($file->type == 'ready') {
            $unzip_log = $unzip_log . '/' . $file->case_id . '_ready.log';
        }else{
            dump('unZipChecks() encountered a type we did not expect');
        }

        /** query zip contents and export that info to a log file */
        /**dump('unzip -l '.$dirZip.' >> '.$unzip_log);*/

        $searchString = 'testing:';
        if(exec('grep ' . escapeshellarg($searchString) . ' ' . $unzip_log)) {
            /** if it has been tested then don't need to test again*/
        }else{
            /** perform test of zip */
            exec("unzip -l $dirZip >> $unzip_log");

            /** test the zip that has been downloaded .... */
            if($file->type == 'example'){
                $cmd = "unzip -t " . $dirZip . ' >> '.$unzip_log;
                    //$folder = $exampleFolder;
            }else if($file->type == 'new'){
                $cmd = "unzip -t " . $dirZip . ' >> '.$unzip_log;
                    //$folder = $newFolder;
            }else if($file->type == 'ready'){
                $cmd = "unzip -t " . $dirZip . ' >> '.$unzip_log;
                    //$folder = $newFolder;
            }else{
                dump('unZipChecks() encountered a type we did not expect');
                /** like a ready type we should just get out of the function */
            }

            /**dump('$cmd');*/
            /**dump($cmd);*/

            exec($cmd);
        }


        /** at this stage we need to check the unzip test log that was created for the zip for any indication that there were errors in which case need to trigger the download again.. and keep trying until we can be sure that the file is absolutely corrupt and not just due to network issues ? */
        /** since we are only doing one file per command */
        /** if the check of the log for this particular zip download has any error then attempt to download again .. and exit out reverting the row on taskdownloadfiles back to zip column 0  */
        /** until the next cron job */
        /** */
        if($file->state == 'unzipped' || $file->state == 'unzipping'){
            /** a zip from the same case coming back to be re-unzipped because not enough files in extracted directory if one of the other zips in the case is unzipped coming back here will not find the log for the unzipped file so errors make it go to the next file in the case*/
            return;
        }else{
            $count_CRC_error = 0;
            $try_to_download_again = false;
            $fropen = fopen($unzip_log, 'r' );
            if ($fropen) {
                while (($line = fgets($fropen)) !== false) {
                    if (strpos($line, 'bad CRC') !== false) {
                        /** file line has some indication of CRC error therefore count it */
                        $count_CRC_error++;
                    }
                    if (strpos($line, 'At least one error was detected in') !== false && strpos($line, $file->local) !== false) {
                        /** undeniably there was at least an error */
                        $try_to_download_again = true;
                    }
                }
                fclose($fropen);
            } else {
                /** error opening the log file. force download the zip again */
                $try_to_download_again = true;
            }
            // dump('$count_CRC_error');
            // dump($count_CRC_error);
            // dump('$try_to_download_again');
            // dd($try_to_download_again);
        }


        /** keeping in mind the atempt amounts */
        /** when it error more than the specified amount of times then turn unzip to unsolveable unzip type. */



        if($try_to_download_again == true || $count_CRC_error > 0){
            /** return the zip file row back to what is was so that it can be re-downloaded! not unzipped again because that would be a waste of time */
            $file->unzip = 0;
            $file->state = 'new';
            $file->save();

            /** if you do the redownload step MUST remove the row from the log which says it was previously downloaded successfully for the same CASE ID and local file name  */
            $log = storage_path()."/logs".config('s3br24.manual_download_log') . date('Y_m_d') . '_downloadLog.log';
            $dir  = storage_path()."/app".config('s3br24.manual_download_temp_folder') . 'job';
            $searchString = "Download complete: " . $dir . "/" . $file->local;
            $replaceString = "Download complete_but_retrying ".$unzip_tries.": " . $dir . "/" . $file->local;

            $cmd = "sed -i 's%".$searchString."%".$replaceString."%g' " . $log;

            exec($cmd);

            /** need to remove the specific zip log file otherwise it cycles and never changes */
            exec("rm -R " . $unzip_log);

            if ($unzip_tries == config('s3br24.manual_unzip_retry_count')) {
                $message = array(
                    'title' => $file->case_id,
                    'content' => 'XML is OK but Zip downloaded has CRC ERROR (Cannot unzip). Have tried downloading already '. $unzip_tries . ' times.',
                    'link' => null,
                    'to' => config('br24config.rc_notify_usernames')
                );

                    //sendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                RCsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
            }
            /** i still want the function to check CRC or errors for the rest of the zips of this caseID */
        }else{
            $file->unzip_checks = 1;
            $file->save();
        }
    }


    /**
     * actually_unzip
     */
    public function actually_unzip()
    {
        /** actualy unzipping after checking downloaded files */
        $file = $this->taskManualDownloadFile->where('state', 'downloaded')->where('unzip', 1)->where('unzip_checks', 1)->orderBy('updated_at', 'asc')->get();



        /**dump($file);*/
        if (empty($file)) {
            return;
        }
        /**dd($file);*/


        $remembering_caseId_doing = null;
        foreach($file as $file_key => $download_task_file_details){
            /**dump('$file_key    == '. $file_key);*/
            /**dump($download_task_file_details);*/

            if($remembering_caseId_doing != $download_task_file_details->case_id){
                $remembering_caseId_doing = $download_task_file_details->case_id;
                /**dump('remembering_caseId_doing   '. $remembering_caseId_doing);*/

                /** get all casefiles that do not have the state notified... */
                $all_case_files = $this->taskManualDownloadFile->where('case_id', $download_task_file_details->case_id)->where('state', '!=', 'notified')->get();
                /**dd($all_case_files);*/


                /** now that we have the ability to selectively choose whether to download just a few zips rather than all */
                /** we need to be able to check if the one selected e.g state = new is by itself.. */
                /** if all of the other zips are state notified.. means that it was only requested so in which case we should */
                /** only for the manual download section */



                $all_files_ready_for_unzip_for_this_caseid = true;
                foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                    /**dump($case_downloadfile_details->local);*/
                    if($case_downloadfile_details->unzip_checks != 1){
                        $all_files_ready_for_unzip_for_this_caseid = false;
                        /** if one of the files in the case id is still downloading go to the next caseID if any to check if we can do that unzip first */

                        /** it probably gets stuck here.  we have to check if the other cases are notified.. meaning for the re */
                    }elseif($case_downloadfile_details->state == 'downloading'){
                        $all_files_ready_for_unzip_for_this_caseid = false;
                    }
                }




                if($all_files_ready_for_unzip_for_this_caseid){
                    /**dump('should be fine to unzip now for caseID  ' . $remembering_caseId_doing);*/
                    break;
                }else{
                    /** when it doesn't have another caseId to go to then we must be able to exit properly  */

                    /** this case is not ready so push to back of the list of downloaded */
                    /** so it can process the ones that are ready */
                    foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                        $file = $this->taskManualDownloadFile->where('id', $case_downloadfile_details->id)->first();
                        /** updated the updated_at */
                        $file->updated_at = Carbon::now()->format('Y-m-d H:i:s'); /**unzip error*/
                        $file->save();
                    }

                    $remembering_caseId_doing = null;
                    $all_case_files = null;
                }
            }
        }
        

        if(is_null($remembering_caseId_doing)){
            return;
        }

        /**dump($remembering_caseId_doing);*/
        /**dump($all_case_files);*/
        /**dd('actually_unzip');*/

        $remove_all_unzip_logs_from_this_case_id = null;
        foreach($all_case_files as $case_file_key => $case_downloadfile_details){

            $file = $this->taskManualDownloadFile->where('id', $case_downloadfile_details->id)->first();
            /**dump('processing === ' . $file->local);*/
            /**dump($file->local);*/
            /** get existing retry count */
            $unzip_tries = $file->unzip_tries;
            /**dd($unzip_tries);*/


            if ($unzip_tries >= config('s3br24.manual_unzip_retry_count')) {
                $file->unzip = 3; /**unzip error*/
                $file->save();
                return;
            }

            /** unziping */
            $file->unzip = 1;
            $file->unzip_tries = $unzip_tries + 1;
            $file->save();

            /** replace with new retry count */
            $unzip_tries = $unzip_tries + 1;




            $dir = config('s3br24.manual_download_temp_folder');

            /** final destination of the files */
            $jobDir = storage_path()."/app". $dir . 'job/' . $file->case_id . '_' . $file->type;
            /**dump('$jobDir');*/
            /**dump($jobDir);*/



            $dirZip = storage_path()."/app". $dir . 'job/' . $file->local;

            /**dump('$dirZip');*/
            /**dump($dirZip);*/

            /**dd(null);*/

            /**check zip file*/
            $zipArchive = new \ZipArchive();
            $tryOpeningZip = $zipArchive->open($dirZip);

            /**dump('$tryOpeningZip');*/
            /**dump($tryOpeningZip);*/

            if ($tryOpeningZip !== TRUE) {
                $file->unzip = 3; /**unzip error*/
                $file->save();

                switch (intval($tryOpeningZip))
                {
                    case 0:
                    $error_code_desc = 'No error';

                    case 1:
                    $error_code_desc = 'Multi-disk zip archives not supported';

                    case 2:
                    $error_code_desc = 'Renaming temporary file failed';

                    case 3:
                    $error_code_desc = 'Closing zip archive failed';

                    case 4:
                    $error_code_desc = 'Seek error';

                    case 5:
                    $error_code_desc = 'Read error';

                    case 6:
                    $error_code_desc = 'Write error';

                    case 7:
                    $error_code_desc = 'CRC error';

                    case 8:
                    $error_code_desc = 'Containing zip archive was closed';

                    case 9:
                    $error_code_desc = 'No such file';

                    case 10:
                    $error_code_desc = 'File already exists';

                    case 11:
                    $error_code_desc = 'Can\'t open file';

                    case 12:
                    $error_code_desc = 'Failure to create temporary file';

                    case 13:
                    $error_code_desc = 'Zlib error';

                    case 14:
                    $error_code_desc = 'Malloc failure';

                    case 15:
                    $error_code_desc = 'Entry has been changed';

                    case 16:
                    $error_code_desc = 'Compression method not supported';

                    case 17:
                    $error_code_desc = 'Premature EOF';

                    case 18:
                    $error_code_desc = 'Invalid argument';

                    case 19:
                    $error_code_desc = 'Not a zip archive';

                    case 20:
                    $error_code_desc = 'Internal error';

                    case 21:
                    $error_code_desc = 'Zip archive inconsistent';

                    case 22:
                    $error_code_desc = 'Can\'t remove file';

                    case 23:
                    $error_code_desc = 'Entry has been deleted';

                    default:
                    $error_code_desc = 'An unknown error has occurred('.intval($tryOpeningZip).')';
                }

                $message = array(
                    'title' => $file->case_id,
                    'content' => 'XML is OK but Zip is ERROR (Cannot unzip)',
                    'error_code_desc' => $error_code_desc,
                    'link' => null,
                    'to' => config('br24config.rc_notify_usernames')
                );

                //sendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                RCsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                return;
            }




            /**$jobFolder = storage_path()."/app".config('s3br24.manual_job_folder');*/
            /**dump($jobFolder);*/
            // $newFolder = $jobFolder . $file->case_id . "/new/";
            // $exampleFolder = $jobFolder . $file->case_id . "/examples/";
            // exec("mkdir -p " . $newFolder);
            // exec("mkdir -p " . $exampleFolder);

            $unzipFolder = storage_path()."/app".config('s3br24.manual_unzip_folder');
            /**dump($unzipFolder);*/
            $newFolder = $unzipFolder . $file->case_id . "/new/";
            $exampleFolder = $unzipFolder . $file->case_id . "/examples/";
            $readyFolder = $unzipFolder . $file->case_id . "/ready/";
            exec("mkdir -p " . $newFolder);
            exec("mkdir -p " . $exampleFolder);
            exec("mkdir -p " . $readyFolder);

            if ($zipArchive->numFiles <= 0) { /** zip empty */
                $file->unzip = 2; /** unzip complete */
                $file->save();
                return;
            }



            /**log for list files of zip*/
            $unzip_log = storage_path()."/logs".config('s3br24.manual_download_log') . 'unzip_log';
            exec("mkdir -p $unzip_log");

            /** dd(null); */

            if ($file->type == 'new') {
                $unzip_log = $unzip_log . '/' . $file->case_id . '_new.log';
            } else if ($file->type == 'example') {
                $unzip_log = $unzip_log . '/' . $file->case_id . '_example.log';
            } else if ($file->type == 'ready') {
                $unzip_log = $unzip_log . '/' . $file->case_id . '_ready.log';
            }else{
                dump();
            }

            /** query zip contents and export that info to a log file */
            // dump('unzip -l '.$dirZip.' >> '.$unzip_log);
            // exec("unzip -l $dirZip >> $unzip_log");

            // /** test the zip that has been downloaded .... */
            // if($file->type == 'example'){
            //     $cmd = "unzip -t " . $dirZip . ' >> '.$unzip_log;
            //     $folder = $exampleFolder;
            // }else if($file->type == 'new'){
            //     $cmd = "unzip -t " . $dirZip . ' >> '.$unzip_log;
            //     $folder = $newFolder;
            // }else{
            //     dump('encountered a type we did not expect');
            //     /** like a ready type we should just get out of the function */
            // }

            // dump('$cmd');
            // dump($cmd);
            // exec($cmd);


            /** at this stage we need to check the unzip test log that was created for the zip for any indication that there were errors in which case need to trigger the download again.. and keep trying until we can be sure that the file is absolutely corrupt and not just due to network issues ? */
            /** since we are only doing one file per command */
            /** if the check of the log for this particular zip download has any error then attempt to download again .. and exit out reverting the row on taskdownloadfiles back to zip column 0  */
            /** until the next cron job */
            /** */

            if($file->state == 'unzipped'){
                /** a zip from the same case coming back to be re-unzipped because not enough files in extracted directory if one of the other zips in the case is unzipped coming back here will not find the log for the unzipped file so errors make it go to the next file in the case*/
                return;
            }else{
                $count_CRC_error = 0;
                $try_to_download_again = false;
                $fropen = fopen($unzip_log, 'r' );
                if ($fropen) {
                    while (($line = fgets($fropen)) !== false) {
                        if (strpos($line, 'bad CRC') !== false) {
                            /** file line has some indication of CRC error therefore count it */
                            $count_CRC_error++;
                        }
                        if (strpos($line, 'At least one error was detected in') !== false && strpos($line, $file->local) !== false) {
                            /** undeniably there was at least an error */
                            $try_to_download_again = true;
                        }
                    }
                    fclose($fropen);
                } else {
                    /** error opening the log file. force download the zip again */
                    $try_to_download_again = true;
                } 
                // dump('$count_CRC_error');
                // dump($count_CRC_error);
                // dump('$try_to_download_again');
                // dd($try_to_download_again);
            }



            /** keeping in mind the atempt amounts */
            /** when it error more than the specified amount of times then turn unzip to unsolveable unzip type. */

            if($try_to_download_again == true || $count_CRC_error > 0){
                /** return the zip file row back to what is was so that it can be re-downloaded! not unzipped again because that would be a waste of time */
                $file->unzip = 0;
                $file->state = 'new';
                $file->save();

                /** if you do the redownload step MUST remove the row from the log which says it was previously downloaded successfully for the same CASE ID and local file name  */
                $log = storage_path()."/logs".config('s3br24.manual_download_log') . date('Y_m_d') . '_downloadLog.log';
                $dir  = storage_path()."/app".config('s3br24.manual_download_temp_folder') . 'job';
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

                //sendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                RCsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                /** i still want the function to check CRC or errors for the rest of the zips of this caseID */
            }else{
                $file->unzip_checks = 1;
                $file->state = 'unzipping';
                $file->save();

                /**dump('starting to unzip it officially');*/
                $searchString = 'inflating';
                if(exec('grep ' . escapeshellarg($searchString) . ' ' . $unzip_log)) {
                    /** already inflating */
                }else{

                    /** all good... actually unzip it now to the correct folder */
                    /** want to be able to adjust this slightly if we can unzip it to another directory not the final jobFolder. */
                    /** have the system check if the unzips are good. */
                    /** and then move it to the job folder. */
                    /** so that way the jobfolder is never put there unless its ready to be notified. */
                    /** use option -oO UTF8 to force character encoding on unzip filenames entirely */
                    if($file->type == 'example'){
                        $cmd = "unzip -o " . $dirZip . " -d " . $exampleFolder . ' >> '.$unzip_log;
                        $folder = $exampleFolder;
                    }else if($file->type == 'new'){
                        $cmd = "unzip -o " . $dirZip . " -d " . $newFolder . ' >> '.$unzip_log;
                        $folder = $newFolder;
                    }else if($file->type == 'ready'){
                        $cmd = "unzip -o " . $dirZip . " -d " . $readyFolder . ' >> '.$unzip_log;
                        $folder = $readyFolder;
                    }else{
                        dump('actually_unzip() encountered a type we did not expect');
                    }
                    dump($cmd);
                    exec($cmd);
                }
            }

        }


        /** at any point any of the files have error with the zip we need to halt the unzipping because then the zips are not all there at the same time for the checking function which also has to be modified */
        /** remove any unzip_logs associated with the caseID they will be generated again when the zip has been redownloaded on the next pass */
        if($remove_all_unzip_logs_from_this_case_id){
            foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                /**log for list files of zip*/
                $unzip_log = storage_path()."/logs".config('s3br24.manual_download_log') . 'unzip_log';
                exec("mkdir -p $unzip_log");

                /** dd(null); */
                if ($file->type == 'new') {
                    $unzip_log = $unzip_log . '/' . $file->case_id . '_new.log';
                } else if ($file->type == 'example') {
                    $unzip_log = $unzip_log . '/' . $file->case_id . '_example.log';
                } else if ($file->type == 'ready') {
                    $unzip_log = $unzip_log . '/' . $file->case_id . '_ready.log';
                }else{
                    dump('actually_unzip() encountered a type we did not expect');
                }
                exec("rm -R " . $unzip_log);
            }
        }



        /**dd('======================== stopping here first ======================');*/


        /** i want to know why they would need to have these functions done to the case file zips. */

        // $now = date('Y-m-d H:i:s');
        // /** check max length of path */
        // $window_max_path_length = \App\Model\RemoteSetting::getValueByCode('window_max_path_length');
        // $window_special_characters = \App\Model\RemoteSetting::getValueByCode('window_special_characters');

        // if (!empty($window_max_path_length)) {
        //     $error_paths = array();

        //     $this->checkMaxPathLength($dir . 'job/', $file->case_id . '_' . $file->type, $window_max_path_length, $error_paths);

        //     $converted_result = array();

        //     $this->convertErrorPathLength($dir . 'job/' . $file->case_id . '_' . $file->type . DIRECTORY_SEPARATOR, $error_paths, $window_special_characters, $converted_result);

        //     if (!empty($converted_result)) {
        //         $mapping_names_data = [];
        //         foreach ($converted_result as $key => $item) {
        //             $mapping_names_data[] = [
        //                 'original' => $item['original'],
        //                 'replacement' => $key,
        //                 'type' => $item['type'],
        //                 'case_id' => $file->case_id,
        //                 'created_at' => $now
        //             ];
        //         }
        //         \App\Model\RemoteMappingName::insert($mapping_names_data);
        //         $file->has_mapping_name = 1;
        //         $file->save();
        //     }
        // }

        // /**check special characters in file, folder name*/
        // if (!empty($window_special_characters)) {

        //     $window_special_characters = explode(' ', $window_special_characters);
        //     $converted_result = array();

        //     $this->checkSpecialCharacters($dir . 'job/', $file->case_id . '_' . $file->type, $window_special_characters, $converted_result);

        //     if (!empty($converted_result)) {
        //         $mapping_names_data = [];
        //         foreach ($converted_result as $key => $item) {
        //             $mapping_names_data[] = [
        //                 'original' => $item['original'],
        //                 'replacement' => $key,
        //                 'type' => $item['type'],
        //                 'case_id' => $file->case_id,
        //                 'created_at' => $now
        //             ];
        //         }
        //         \App\Model\RemoteMappingName::insert($mapping_names_data);
        //         $file->has_mapping_name = 1;
        //         $file->save();
        //     }
        // }



        /**copy file*/
        // if ($file->type == 'example') {
        //     $cmd = "cp -R " . $jobDir . "/* " . $exampleFolder;
        //     exec($cmd);
        // }

        // if ($file->type == 'new') {
        //     $cmd = "cp -R " . $jobDir . "/* " . $newFolder;
        //     exec($cmd);

        //     $scanDir = $file->case_id . "/new/";
        //     $exDir = $file->case_id . "/examples/";
        //     $directories = Storage::disk('jobfolder')->allDirectories($scanDir);

        //     $dirMoved = [];
        //     foreach ($directories as $d) {
        //         $baseName = basename($d);

        //         if (strripos($baseName, 'example') !== false) {
        //             $isParentMoved = false;
        //             foreach ($dirMoved as $m) {
        //                 if (strripos($d, $m) !== false) {
        //                     $isParentMoved = true;
        //                 }
        //             }

        //             if ($isParentMoved == false) {
        //                 $realDir = str_replace($scanDir, '', $d);
        //                 $basePath = dirname($realDir);
        //                 Storage::disk('jobfolder')->makeDirectory($exDir . $basePath);
        //                 $path = $jobFolder . $d;
        //                 $exampleFolder = $jobFolder . $exDir . $basePath;
        //                 exec("mv '$path' '$exampleFolder'");
        //                 $dirMoved[] = $d;
        //             }
        //         }
        //     }

        //     /**remove folder after copy 215 to 220*/
        //     exec("rm -r $jobDir");
        //     exec("rm -r $dirZip");
        // }
    }

    /**
     * check_extracted_files_with_zip_contents
     */
    public function check_extracted_files_with_zip_contents()
    {
        $file = $this->taskManualDownloadFile->where('state', 'unzipping')->where('unzip', 1)->first();
        /** will always concentrate on the same zip */

        /**dump($file);*/
        if (empty($file)) {
            return;
        }
        /**dd($file);*/

        $s3Br24Config = config('s3br24');
        $download_temp_folder = $s3Br24Config['manual_download_temp_folder'];
        $xml_file_dir = storage_path()."/app".$download_temp_folder . "xml/".$file->case_id .'.xml';


        /** the unzipping could have been finished or it is still running */
        /** */
        $dir = config('s3br24.manual_download_temp_folder');

        /** final destination of the files */
        $jobDir = storage_path()."/app". $dir . 'job/' . $file->case_id . '_' . $file->type;
        /**dump('$jobDir');*/
        /**dump($jobDir);*/

        $dirZip = storage_path()."/app". $dir . 'job/' . $file->local;
        /**dump('$dirZip');*/
        /**dump($dirZip);*/

        /**dd(null);*/
        $zipArchive = new \ZipArchive();
        $tryOpeningZip = $zipArchive->open($dirZip);
        /**dd($zipArchive);*/


        /**$jobFolder = storage_path()."/app".config('s3br24.manual_job_folder');*/
        /**dump($jobFolder);*/

        /**$newFolder = $jobFolder . $file->case_id . "/new/";*/
        /**$exampleFolder = $jobFolder . $file->case_id . "/examples/";*/
        /**exec("mkdir -p " . $newFolder);*/
        /**exec("mkdir -p " . $exampleFolder);*/

        $unzipFolder = storage_path()."/app".config('s3br24.manual_unzip_folder');
        $newFolder = $unzipFolder . $file->case_id . "/new/";
        $exampleFolder = $unzipFolder . $file->case_id . "/examples/";
        $readyFolder = $unzipFolder . $file->case_id . "/ready/";
        /**exec("mkdir -p " . $newFolder);*/
        /**exec("mkdir -p " . $exampleFolder);*/
        /**exec("mkdir -p " . $readyFolder);*/

        /**log for list files of zip*/
        $unzip_log = storage_path()."/logs".config('s3br24.manual_download_log') . 'unzip_log' ;
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
        } else{
            dump('check_extracted_files_with_zip_contents() encountered a value not expected');
        }

        /** dd(null); */
        // $count_CRC_error = 0;
        // $count_inflated_files = 0;
        // $try_to_download_again = false;
        // $successfully = false;

        // if(File::exists($unzip_log)){
        //     $fropen = fopen($unzip_log, 'r' );
        //     if ($fropen) {
        //         while (($line = fgets($fropen)) !== false) {
        //             if (strpos($line, 'bad CRC') !== false) {
        //                 /** file line has some indication of CRC error therefore count it */
        //                 $count_CRC_error ++;
        //             }
        //             if (strpos($line, 'inflating') !== false || strpos($line, 'creating') !== false) {
        //                 /** file line has some indication of CRC error therefore count it */
        //                 $count_inflated_files ++;
        //             }
        //             if (strpos($line, 'At least one error was detected in') !== false && strpos($line, $file->local) !== false) {
        //                 /** undeniably there was at least an error */
        //                 $try_to_download_again = true;
        //             }
        //         }
        //         fclose($fropen);
        //     } else {
        //         /** error opening the log file. force download the zip again */
        //         $try_to_download_again = true;
        //     }
        // }


        $number_of_files_in_directory = exec("find ".$folder." -type f | wc -l");
        $number_of_folders_in_directory_min_depth = exec("find ".$folder." -mindepth 1 -type d | wc -l");

        dump('number_of_files_in_directory '.$folder . ' => ' . $number_of_files_in_directory);
        dump('number_of_folders_in_directory_min_depth '.$folder . ' => ' . $number_of_folders_in_directory_min_depth);

        /**dump('try_to_download_again');*/
        /**dump($try_to_download_again);*/

        dump('$zipArchive->numFiles => ' .$zipArchive->numFiles);

        /**dump('========================');*/

        /**dump('$count_inflated_files');*/
        /**dump($count_inflated_files);*/
        /**dd(null);*/

        /** if one encountered but it is not yet unzipped we have to wait longer */

        /** if the logs are not built consistently then the files never leave this loop and will eventually fail hard */
        $very_specific_count = $number_of_files_in_directory + $number_of_folders_in_directory_min_depth;

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

            // $message = array(
            //     'title' => $file->case_id,
            //     'content' => 'Zip '.$file->type.' extracted to ' . $folder,
            //     'link' => null,
            //     'to' => config('br24config.rc_notify_usernames')
            // );
            // RCsendCreateJobMessage('', $message);

            /** clear up the zips that have been extracted but because the files can be large or not it take time so this cannot work because the file is busy */
            /**gc_collect_cycles();*/

            // unlink($unzip_log);
            // unlink($xml_file_dir);
            // unlink($dirZip);

            /**dump($unzip_log);*/
            // if(File::exists($unzip_log)){
            //     /**dump($unzip_log . ' exists');*/
            //     try {
            //         File::delete($unzip_log);
            //     } catch (FileNotFoundException $e) {
            //         dd($e);
            //     }
            // }

            /**dump($xml_file_dir);*/
            // if(File::exists($xml_file_dir)){
            //     /**dump($xml_file_dir . ' exists');*/
            //     try {
            //         File::delete($xml_file_dir);
            //     } catch (FileNotFoundException $e) {
            //         dd($e);
            //     }
            // }

            // dump($dirZip);
            // if(File::exists($dirZip)){
            //     dump($dirZip . ' exists');
            //     try {
            //         File::delete($dirZip);
            //     } catch (FileNotFoundException $e) {
            //         dd($e);
            //     }
            // }


            /** when it is fully checked need to go in to the unzip folder and iterate through all the folders and remove any offending characters from the folder names only */
            /** proably best to put it into a log file to interate through */
            
            /**log for list files of zip*/
            $unzip_folder_character_checks_log = storage_path()."/logs".config('s3br24.manual_download_log') . 'unzip_log';
            exec("mkdir -p $unzip_folder_character_checks_log");

            /**dump($unzip_folder_character_checks_log);*/
            
            $unzipFolder = storage_path()."/app".config('s3br24.manual_unzip_folder');
            $newFolder = $unzipFolder . $file->case_id . "/new";
            $exampleFolder = $unzipFolder . $file->case_id . "/examples";
            $readyFolder = $unzipFolder . $file->case_id . "/ready";
            /**dd(null);*/

            /**dump($newFolder);*/
            /**dump($exampleFolder);*/

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

            /**dump($cmd);*/
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
                        /**dump($line);*/

                        if (strpos($line, '(') !== false || strpos($line, ')') !== false) {
                            //file line has some indication of CRC error therefore count it
                            /**$line = str_replace(')', '', $line);*/
                            /**$line = str_replace('(', '', $line);*/
                            $original_name_for_search = str_replace(')', '\)', $original_name_for_search);
                            $original_name_for_search = str_replace('(', '\(', $original_name_for_search);
                        }

                        $new_folder_name = $folder.'/"'.$line.'"';
                        $cmd = 'mv ' . $folder.'/'.$original_name_for_search .' '. ' ' . $new_folder_name;

                        /**dump($cmd);*/
                        exec($cmd);
                    }else{
                        /**dump('doesnt have');*/
                    }
                }
                fclose($fropen);
            } else {
                /** error opening the log file. force download the zip again */
                $try_to_download_again = true;
            }

            /**dd(null);*/


            /** remove the file at the end */
            if(File::exists($unzip_folder_character_checks_log)){
                /**dump($unzip_log . ' exists');*/
                try {
                    File::delete($unzip_folder_character_checks_log);
                } catch (FileNotFoundException $e) {
                    dd($e);
                }
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
            if ($unzip_scan_folder_content_against_zip_contents_tries >= config('s3br24.manual_unzip_retry_count')) {
                /** because reaching here means that the zip has been downloaded and files checked before unzip passed */
                /** so we don't need to do those steps again we just need to unzip it */
                $file->state = 'downloaded';
                $file->unzip = 1; /**unzip error*/
                $file->unzip_tries = 0;
                $file->unzip_checks = 1;
                $file->unzip_checks_tries = 0;
                $file->save();
            }else{
                /** increment the amount of times trying to scan the two locations for matching file amnounts */
                $file->unzip_checks_tries = $unzip_scan_folder_content_against_zip_contents_tries + 1;
                $file->save();

                /** replace with new retry count */
                /**$unzip_scan_folder_content_against_zip_contents_tries = $unzip_scan_folder_content_against_zip_contents_tries + 1;*/
            }
        }
    }




    /**
     * move_to_share_directory_of_choice
     */
    public function move_to_share_directory_of_choice()
    {
        $file = $this->taskManualDownloadFile->where('state', 'unzipped')->where('unzip', 2)->get();
        /**dump($file);*/

        if (empty($file)) {
            return;
        }
        /**dd($file);*/

        $remembering_caseId_doing = null;
        $need_to_make_all_unzipped_again = false;
        foreach($file as $file_key => $download_task_file_details){
            /**dump('$file_key    == '. $file_key);*/
            /**dump($download_task_file_details);*/

            if($remembering_caseId_doing != $download_task_file_details->case_id){
                $remembering_caseId_doing = $download_task_file_details->case_id;
                /**dump('remembering_caseId_doing   '. $remembering_caseId_doing);*/

                $all_case_files = $this->taskManualDownloadFile->where('case_id', $download_task_file_details->case_id)->where('state', '!=', 'notified')->get();
                /**dd($all_case_files);*/

                $all_files_ready_for_unzip_for_this_caseid = true;
                foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                    /**dump($case_downloadfile_details->local);*/
                    if($case_downloadfile_details->state == 'unzipped'){
                        /** let it through */
                    }elseif($case_downloadfile_details->state == 'notified'){
                        /***/
                        $need_to_make_all_unzipped_again = true;
                        break;
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


        if($need_to_make_all_unzipped_again){
            foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                $file = $this->taskManualDownloadFile->where('id', $case_downloadfile_details->id)->first();
                $file->state = 'unzipped';
                $file->save();
            }
            return;
        }

        if(is_null($remembering_caseId_doing)){
            return;
        }

        /**dump($remembering_caseId_doing);*/
        /**dump($all_case_files);*/

        if($remembering_caseId_doing != null){
            $result_of_folder_move = $this->start_physically_move_caseID_folder_from_unzip_directory_to_jobfolder_directory_using_rsync($remembering_caseId_doing);
        }
    }



    public function start_physically_move_caseID_folder_from_unzip_directory_to_jobfolder_directory_using_rsync($case_id)
    {
        /** what happens if there is a network issue */
        /** that interrupts the cifs mount :- fstab will try to re mount and anyway if the mounts are not mounted none of the scheduled task will run and therefore needs human intervention */
        $all_case_files = $this->taskManualDownloadFile->where('case_id', $case_id)->where('state', '!=', 'notified')->get();

        $jobFolder = storage_path()."/app".config('s3br24.manual_job_folder');
        /**dump($jobFolder);*/
        exec("mkdir -p " . $jobFolder);

        $unzipFolder = storage_path()."/app".config('s3br24.manual_unzip_folder').$case_id;

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
        $count_of_rsync_commands_running = exec($cmd);

        if($count_of_rsync_commands_running > 5){
            return;
        }

        $progress_path = storage_path()."/logs".config('s3br24.manual_download_log')."progress";

        if (!File::isFile($progress_path)) {
            $path = storage_path().'/logs'.config('s3br24.manual_download_log').'progress';
            File::makeDirectory($path, 0777, true, true); /** make directory */
        }
        $rsync_progress_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress/". $case_id.'_rsync_progressLog.log';

        /**dump($unzipFolder);*/
        $cmd = 'rsync --ignore-existing -ar --info=progress2 '.$unzipFolder. ' ' . $jobFolder;
        //$pid = exec($cmd . " > /dev/null & echo $!;", $output);
        $pid = exec($cmd . " > {$rsync_progress_log} &", $output);

        foreach($all_case_files as $case_file_key => $case_downloadfile_details){
            $file = $this->taskManualDownloadFile->where('id', $case_downloadfile_details->id)->first();
            $file->pid = $pid;
            $file->state = 'moving_to_jobFolder';
            $file->save();
        }
    }




    /**
     * send_message_when_case_is_fully_extracted_move_to_directory_of_choice
     */
    public function send_message_when_case_is_fully_extracted_move_to_directory_of_choice()
    {
        /** sometimes the amount of scheduled task keeps growing almost like there is a blockage of some sort. */
        /** and when a job gets notified if there are backlogs of tasks then it doesn't complete correctly */
        /** in fact it actually deletes the jobFolder contetns wasting time and effort */
        $file = $this->taskManualDownloadFile->where('state', 'moving_to_jobFolder')->where('unzip', 2)->orderBy('updated_at', 'asc')->get();
        /**dump($file);*/

        if (empty($file)) {
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

                $all_case_files = $this->taskManualDownloadFile->where('case_id', $download_task_file_details->case_id)->where('state', '!=', 'notified')->get();
                /**dd($all_case_files);*/

                $all_files_ready_for_unzip_for_this_caseid = true;
                foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                    /**dump($case_downloadfile_details->local);*/
                    if($case_downloadfile_details->state == 'moving_to_jobFolder'){
                        /** let it through */
                    }elseif($case_downloadfile_details->state == 'notified'){
                        /***/
                        $need_to_make_all_notified = true;
                        break;
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
        

        if($need_to_make_all_notified){
            foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                $file = $this->taskManualDownloadFile->where('id', $case_downloadfile_details->id)->first();
                $file->state = 'notified';
                $file->save();
            }
            return;
        }

        if(is_null($remembering_caseId_doing)){
            return;
        }

        dump($remembering_caseId_doing);
        /**dump($all_case_files);*/
        /**dd(null);*/

        /** its all then a matter of sending ONE message to the responsible with the case number and the job location */
        /** maybe need to move everything -- later */
        $content = '';
        $case_id = '';
        $xml_title_contents = '';
        $xml_jobid_title = '';
        $one_already_notified = false;
        foreach($all_case_files as $case_file_key => $case_downloadfile_details){

            $file = $this->taskManualDownloadFile->where('id', $case_downloadfile_details->id)->first();
            /**dump('processing === ' . $file->local);*/
            /**dump($file->local);*/

            if($file->state == 'notified'){
                $one_already_notified = true;
                break;
            }

            $MANUAL_JOBFOLDER_DIR_SHARELOCATION_STRING = env('MANUAL_JOBFOLDER_DIR_SHARELOCATION_STRING');

            //$content .= 'Zip '.$file->type.' extracted to ' . $folder . '';
            if($content == ''){
                $content .= '```';
                $content .= str_replace("/", "\\", $MANUAL_JOBFOLDER_DIR_SHARELOCATION_STRING . $file->case_id);
                $content .= '```';

                $case_id = $file->case_id;
                $xml_title_contents = $file->xml_title_contents;
                $xml_jobid_title = $file->xml_jobid_title;
            }

            /**$file->state = 'notified';*/
            /**$file->save();*/
        }
        $content .= '';


        $result_of_folder_move = $this->physically_move_caseID_folder_from_unzip_directory_to_jobfolder_directory($case_id);

        /** to force notifying of case Id*/
        $case_id_to_force_notify_manually = DB::table('bypass_manualdl_filecountcheck_force_notify')->first();
        if($case_id_to_force_notify_manually){
            if($case_id == $case_id_to_force_notify_manually->case_id){
                $result_of_folder_move = true;
                dump('bypassing filecountcheck for '.$case_id.'');
            }
        }

        if(!$one_already_notified && $result_of_folder_move){
            /** we have to protect against the eventuallity that there is a power cut when this move is happening.. or if there is a network issue */
            $message = array(
                'title' => $case_id,
                'content' => $content,
                'xml_title_contents' => $xml_title_contents,
                'xml_jobid_title' => $xml_jobid_title,
                'link' => null,
                'to' => config('br24config.rc_notify_usernames')
            );
            RCsendCreateJobMessage('', $message);

            foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                $file = $this->taskManualDownloadFile->where('id', $case_downloadfile_details->id)->first();
                /**dump('processing === ' . $file->local);*/
                /**dump($file->local);*/
                $file->state = 'notified';
                $file->save();
            }

            /**at the end we can safely remove the case_id from the unzip_folder tree */
            $unzipFolder = storage_path()."/app".config('s3br24.manual_unzip_folder').$case_id;
            dump('removing unzip folder for case_id => '. $case_id);
            exec("rm -R ".$unzipFolder);

            /** perform clean up of all previous steps only at the end, that way when we need to reset to an earlier step whenever a step errors out we have the resources left intact */
            foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                $file = $this->taskManualDownloadFile->where('id', $case_downloadfile_details->id)->first();

                $s3Br24Config = config('s3br24');
                $download_temp_folder = $s3Br24Config['manual_download_temp_folder'];
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


                $unzipFolder = storage_path()."/app".config('s3br24.manual_unzip_folder');
                $newFolder = $unzipFolder . $file->case_id . "/new/";
                $exampleFolder = $unzipFolder . $file->case_id . "/examples/";
                $readyFolder = $unzipFolder . $file->case_id . "/ready/";

                /**log for list files of zip*/
                $unzip_log = storage_path()."/logs".config('s3br24.manual_download_log') . 'unzip_log' ;
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

                $dir = config('s3br24.manual_download_temp_folder');
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
                $task_download = $this->taskManualDownload->where('case_id', $case_id)->first();
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
                    $this->taskManualUpload->insert($data);
                }

                DB::commit();
            } catch(\Exception $e) {
                Debugbar::addException($e);
                throw $e;
            }
        }else{
            /** the contents are not the same.. so we have a problem so we allow the scheduled tasks to re do the function with the folder and file structure the same as the zip source */
            /** as some point we need to check if the move to the jobFolder pid is still alive.. if it isn't need to reset it to the unzipped state for all the rows with the same case_id */
            /** we update the state to still moving adjusting the updated_at column so the next time it tires the other caseId and not stick to repeating this caseID to keep things moving along rather than stuck */

            foreach($all_case_files as $case_file_key => $case_downloadfile_details){
                $file = $this->taskManualDownloadFile->where('id', $case_downloadfile_details->id)->first();
                $working_on_this_case_id = $file->case_id;
                dump($working_on_this_case_id . ' still moving_to_jobFolder');
                $file->state = 'moving_to_jobFolder';
                $file->save();


                /** sometimes the best way to fix the problem is to clear the jobFolder directory as well */

                $check_if_pid_still_running = exec("ps aux | awk '{print $1 }' | grep ". $file->pid);
                if($check_if_pid_still_running == $file->pid){
                    /** it is still running under the same pid.. lucky us, just let it keep going. */
                }else{
                    dump($working_on_this_case_id . ' rsync pid: '.$file->pid.' is no longer running');
                    /** pid does not exists so we can reset it to the unzipped state to attempt to move again */

                    /** lets try not to remove the files in the jobFolder at all */
                        // $jobFolder = storage_path()."/app".config('s3br24.manual_job_folder');
                        // exec('rm -r ' . $jobFolder . $working_on_this_case_id . '/new');
                        // exec('rm -r ' . $jobFolder . $working_on_this_case_id . '/example');

                    $file->state = 'unzipped';
                    $file->save();
                }

            }
        }

    }

    public function physically_move_caseID_folder_from_unzip_directory_to_jobfolder_directory($case_id)
    {
        /** now with the option to selectively choose which zips to download we have to refine the file count isolated to just what was chosen */
        /** what happens if there is a network issue */
        /** that interrupts the cifs mount */
        $all_case_files = $this->taskManualDownloadFile->where('case_id', $case_id)->where('state', '!=', 'notified')->get();

        /** up date the status here so that the updated_at changes so the scheduler can handle the next in the list */
        foreach($all_case_files as $case_file_key => $case_downloadfile_details){
            $file = $this->taskManualDownloadFile->where('id', $case_downloadfile_details->id)->first();
            $file->state = 'moving_to_jobFolder.';
            $file->save();
            $file->state = 'moving_to_jobFolder';
            $file->save();
        }

        $jobFolder = storage_path()."/app".config('s3br24.manual_job_folder');

        $unzipFolder = storage_path()."/app".config('s3br24.manual_unzip_folder').$case_id;


        /** check that the amount of files and folders are exactly the same in the unzip and job folder before deleting the folder from the unzip folder */
        /** if it reaches here then it means it has completed the copy */
        /** if anyime there is a network issue or power cut then */



        dump('checking move from unzipfolder to jobfolder for case_id => '.$case_id);
        $inner_jobfolder = $jobFolder.$case_id;

        /** it seems to keep bunching here we can probably use the grep command to see if one of the find commands is already running for this case in which case don't need to repeat it */
        $cmd = "ps aux | grep \"find\" | grep \"".$inner_jobfolder."\" | grep \"type\" | grep \"wc\" | wc -l";
        dump($cmd);
        $check_find_file_count_command_is_still_running_jobfolder_directory = exec($cmd);
        
        $cmd = "ps aux | grep \"find\" | grep \"".$inner_jobfolder."\" | grep \"mindepth\" | grep \"type\" | grep \"wc\" | wc -l";
        dump($cmd);
        $check_find_directory_count_command_is_still_running_jobfolder_directory = exec($cmd);

        dump('jobfolder ' . $check_find_file_count_command_is_still_running_jobfolder_directory . "||" . $check_find_directory_count_command_is_still_running_jobfolder_directory);


        $inner_unzipfolder = $unzipFolder;

        /** it seems to keep bunching here we can probably use the grep command to see if one of the find commands is already running for this case in which case don't need to repeat it */
        $cmd = "ps aux | grep \"find\" | grep \"".$inner_unzipfolder."\" | grep \"type\" | grep \"wc\" | wc -l";
        dump($cmd);
        $check_find_command_is_still_running_unzipfolder_directory = exec($cmd);

        $cmd = "ps aux | grep \"find\" | grep \"".$inner_unzipfolder."\" | grep \"mindepth\" | grep \"type\" | grep \"wc\" | wc -l";
        dump($cmd);
        $check_find_directory_count_command_is_still_running_unzipfolder_directory = exec($cmd);

        dump('unzipfolder ' . $check_find_command_is_still_running_unzipfolder_directory . "||" . $check_find_directory_count_command_is_still_running_unzipfolder_directory);

        if($check_find_file_count_command_is_still_running_jobfolder_directory >= 2 || $check_find_directory_count_command_is_still_running_jobfolder_directory >= 2 || $check_find_command_is_still_running_unzipfolder_directory >= 2 || $check_find_directory_count_command_is_still_running_unzipfolder_directory >= 2){
            return false;
        }else{


            $count_of_types_downloaded = count($all_case_files);

            $array_of_checks_per_type = [];
            /** if they select only part of the zips ... then this also needs to be split */
            foreach($all_case_files as $case_file_key => $case_downloadfile_details){
               

                $number_of_files_in_jobfolder_directory = exec("find ".$inner_jobfolder."/". $case_downloadfile_details->type." -type f | wc -l");
                $number_of_folders_in_jobfolder_directory_min_depth = exec("find ".$inner_jobfolder."/". $case_downloadfile_details->type." -mindepth 1 -type d | wc -l");
                dump('number_of_files_in_jobfolder_directory '.$inner_jobfolder."/". $case_downloadfile_details->type. ' => ' . $number_of_files_in_jobfolder_directory);
                dump('number_of_folders_in_jobfolder_directory_min_depth '.$inner_jobfolder."/". $case_downloadfile_details->type. ' => ' . $number_of_folders_in_jobfolder_directory_min_depth);

                $number_of_files_in_unzipfolder_directory = exec("find ".$inner_unzipfolder."/". $case_downloadfile_details->type." -type f | wc -l");
                $number_of_folders_in_unzipfolder_directory_min_depth = exec("find ".$inner_unzipfolder."/". $case_downloadfile_details->type." -mindepth 1 -type d | wc -l");
                dump('number_of_files_in_unzipfolder_directory '.$inner_unzipfolder."/". $case_downloadfile_details->type. ' => ' . $number_of_files_in_unzipfolder_directory);
                dump('number_of_folders_in_unzipfolder_directory_min_depth '.$inner_unzipfolder."/". $case_downloadfile_details->type. ' => ' . $number_of_folders_in_unzipfolder_directory_min_depth);
                
                $counts_of_items_in_jobfolder = (int)$number_of_files_in_jobfolder_directory + (int)$number_of_folders_in_jobfolder_directory_min_depth;
                $counts_of_items_in_unzipfolder = (int)$number_of_files_in_unzipfolder_directory + (int)$number_of_folders_in_unzipfolder_directory_min_depth;
            
                $array_of_checks_per_type[$case_downloadfile_details->type]['counts_of_items_in_jobfolder'] = $counts_of_items_in_jobfolder;
                $array_of_checks_per_type[$case_downloadfile_details->type]['counts_of_items_in_unzipfolder'] = $counts_of_items_in_unzipfolder;
            }

            dump($array_of_checks_per_type);

            $success_count_checks = 0;
            foreach($array_of_checks_per_type as $generic_type_key => $checks_per_type_details){
                if($checks_per_type_details['counts_of_items_in_jobfolder'] == $checks_per_type_details['counts_of_items_in_unzipfolder']){
                    $success_count_checks++;
                }
            }

            if($success_count_checks == $count_of_types_downloaded){
                return true;
            }else{
                
                /** managed to get to here meaning that the right conditions for checking the file counts but failed due to not the same so attempt finding Thumbs.db file and remove those */
                $Thumbs_db_files_in_unzip_directory = exec("find ".$inner_jobfolder." -type f -name Thumbs.db -delete");

                return false;
            }


        }
    }










    /**
     * after_zip_move_from_temp_upload_directory_to_jobfolder
     */
    public function after_zip_move_from_temp_upload_directory_to_jobfolder()
    {
        /** reqeusted to move uploaded files to the Jobfolder/ready folder so they can check it */
        /** should have a notification that pops on their channel indicating that the case can be checked */
        /** later when it reaches s3 a notification that is has been uploaded to s3 */

        /** because the zip could be pretty big maybe it will take longer to upload to the shared folder and the scheduler will always be */
        /** if you can add a new column to keep track of whether it is being done */
        /** also if the number files do not match re-initiate the */
        /** since we are changing the logic.. instead of moveing the zip and uncompressing, and remove the zip afterwards we jsut unzip to the jobfolder ready folder check when its done. */

        $file = $this->taskManualUpload->where('state', '=', 'zipped')->where('state', '!=', 'notified')->where('move_to_jobfolder', 0)->orderBy('updated_at', 'asc')->get();
        /**dump($file);*/

        if (empty($file)) {
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

                $all_case_files = $this->taskManualUpload->where('case_id', $upload_task_file_details->case_id)->get();
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
            $file = $this->taskManualUpload->where('id', $case_uploadfile_details->id)->first();
            
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


        if ($move_to_jobfolder_tries == config('s3br24.manual_s3_upload_time_allowed_before_retry')) {
            /** should we try to move again and see if that helps? */
            $message = array(
                'title' => $file->case_id,
                'content' => 'Move Uploaded Files to JobFolder ready Folder (error)',
                'link' => null,
                'to' => config('br24config.rc_notify_usernames')
            );

            RCsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
        }





        /** start the moving process */
        $path = storage_path()."/app".config('s3br24.manual_job_folder').$remembering_caseId_doing."/ready";

        /**dump($path);*/
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

        $unzip_log = storage_path()."/logs".config('s3br24.manual_download_log') . 'unzip_log';
        exec("mkdir -p $unzip_log");
        /** dd(null); */
        $unzip_log = $unzip_log . '/' . $remembering_caseId_doing . '_ready_unzip_jobfolder.log';

        $tempZipFolderZipFile = storage_path()."/app".config('s3br24.manual_temp_zip_folder').$remembering_caseId_doing."/ready.zip";
        /** use option -oO UTF8 to force character encoding on unzip filenames entirely */
        //$copied_zip_path = $path."/ready.zip";
        $cmd = "unzip -o " . $tempZipFolderZipFile . " -d " . $path . ' > '.$unzip_log;
        //exec($cmd);
        $pid = exec($cmd . " & echo $!;", $output);

        $file->move_to_jobfolder = 2;
        $file->pid = $pid;
        $file->save();
    }


    /**
     * send_message_when_uploaded_case_is_fully_extracted_move_to_jobfolder_ready_directory
     */
    public function send_message_when_uploaded_case_is_fully_extracted_move_to_jobfolder_ready_directory()
    {
        /** sometimes the amount of scheduled task keeps growing almost like there is a blockage of some sort. */
        /** and when a job gets notified if there are backlogs of tasks then it doesn't complete correctly */
        /** in fact it actually deletes the jobFolder contetns wasting time and effort */
        $file = $this->taskManualUpload->where('move_to_jobfolder', 2)->orderBy('updated_at', 'asc')->get();
        /**dump($file);*/

        if (empty($file)) {
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

                $all_case_files = $this->taskManualUpload->where('case_id', $download_task_file_details->case_id)->get();
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

        /**dump($remembering_caseId_doing);*/
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

            $file = $this->taskManualUploadView->where('id', $case_downloadfile_details->id)->first();
            /**dump('processing === ' . $file->local);*/
            /**dump($file->local);*/

            $MANUAL_JOBFOLDER_DIR_SHARELOCATION_STRING = env('MANUAL_JOBFOLDER_DIR_SHARELOCATION_STRING');

            //$content .= 'Zip '.$file->type.' extracted to ' . $folder . '';
            if($content == ''){
                $content .= '```';
                $content .= str_replace("/", "\\", $MANUAL_JOBFOLDER_DIR_SHARELOCATION_STRING . $file->case_id).'\\ready';
                $content .= '```';

                $case_id = $file->case_id;
                $xml_title_contents = $file->xml_title_contents;
                $xml_jobid_title = $file->xml_jobid_title;
            }

            /**$file->state = 'notified';*/
            /**$file->save();*/
        }
        $content .= '';

        /**dd($content);*/

        /** by this point the files have already moved so we don't need to do the next check */
        /** */

        $result_of_folder_move = $this->check_move_status_caseID_folder_from_temp_upload_directory_to_jobfolder_ready_directory($case_id);


        if($result_of_folder_move){
            /** we have to protect against the eventuallity that there is a power cut when this move is happening.. or if there is a network issue */
            $message = array(
                'title' => $case_id,
                'content' => $content,
                'xml_title_contents' => $xml_title_contents,
                'xml_jobid_title' => $xml_jobid_title,
                'link' => null,
                'to' => config('br24config.rc_notify_usernames')
            );
            RCsendUploadJobReadyforCheckingMessage('', $message);


            $file = $this->taskManualUpload->where('case_id', $remembering_caseId_doing)->first();
            /**dump('processing === ' . $file->local);*/
            /**dump($file->local);*/
            $file->state = 'notified/uploading to s3';
            $file->move_to_jobfolder = 3;
            $file->sending_to_s3 = 1;
            $file->save();
        }else{

            /** what do we do when it does not match here ? */
            /** we check if the pid is still running */
            /** if it is not we reset the move_to_jobfolder value to 0 so it can be done again */



            /** the contents are not the same.. so we have a problem so we allow the scheduled tasks to re do the function with the folder and file structure the same as the zip source */
            /** as some point we need to check if the move to the jobFolder pid is still alive.. if it isn't need to reset it to the unzipped state for all the rows with the same case_id */
            /** we update the state to still moving adjusting the updated_at column so the next time it tires the other caseId and not stick to repeating this caseID to keep things moving along rather than stuck */

            /** sometimes the best way to fix the problem is to clear the jobFolder directory as well */
            $file = $this->taskManualUpload->where('case_id', $remembering_caseId_doing)->first();

            $check_if_pid_still_running = exec("ps aux | awk '{print $1 }' | grep ". $file->pid);
            if($check_if_pid_still_running == $file->pid){
                /** it is still running under the same pid.. lucky us, just let it keep going. */
            }else{
                dump($remembering_caseId_doing . ' rsync pid: '.$file->pid.' is no longer running destined for ready folder');
                /** pid does not exists so we can reset it to the unzipped state to attempt to move again */

                /** lets try not to remove the files in the jobFolder at all */
                // $jobFolder = storage_path()."/app".config('s3br24.manual_job_folder');
                // exec('rm -r ' . $jobFolder . $working_on_this_case_id . '/new');
                // exec('rm -r ' . $jobFolder . $working_on_this_case_id . '/example');

                $file->move_to_jobfolder = 0;
                $file->save();
            }

        }
    }


    public function check_move_status_caseID_folder_from_temp_upload_directory_to_jobfolder_ready_directory($case_id)
    {
        /** start the moving process */
        $path = storage_path()."/app".config('s3br24.manual_job_folder').$case_id."/ready";
        /**dd($path);*/
        if(!File::exists($path)) {
            File::makeDirectory($path, 0777, true, true); /**make directory if not exists */
        }
        exec('mkdir -p ' . $path);



        dump('checking move from tempUploadfolder to jobfolder ready for case_id => '.$case_id);

        /** it seems to keep bunching here we can probably use the grep command to see if one of the find commands is already running for this case in which case don't need to repeat it */
        $cmd = "ps aux | grep \"find\" | grep \"".$path."\" | grep \"type\" | grep \"wc\" | wc -l";
        dump($cmd);
        $check_find_file_count_command_is_still_running_jobfolder_directory = exec($cmd);
        
        $cmd = "ps aux | grep \"find\" | grep \"".$path."\" | grep \"mindepth\" | grep \"type\" | grep \"wc\" | wc -l";
        dump($cmd);
        $check_find_directory_count_command_is_still_running_jobfolder_directory = exec($cmd);

        dump('jobfolder_ready ' . $check_find_file_count_command_is_still_running_jobfolder_directory . "||" . $check_find_directory_count_command_is_still_running_jobfolder_directory);


        if($check_find_file_count_command_is_still_running_jobfolder_directory >= 2 || $check_find_directory_count_command_is_still_running_jobfolder_directory >= 2){
            return false;
        }else{

            /** we try to get the zip and cp it there extract it and remove the zip afterwards? */
            $tempZipFolderZipFile = storage_path()."/app".config('s3br24.manual_temp_zip_folder').$case_id."/ready.zip";

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
                return true;
            }else{
                return false;
            }
        }
    }





    /**
     * send_the_ready_zip_to_s3
     */
    public function send_the_ready_zip_to_s3()
    {
        /** sometimes the amount of scheduled task keeps growing almost like there is a blockage of some sort. */
        /** and when a job gets notified if there are backlogs of tasks then it doesn't complete correctly */
        /** in fact it actually deletes the jobFolder contetns wasting time and effort */
        $file = $this->taskManualUpload->where('sending_to_s3', 1)->orderBy('updated_at', 'asc')->get();
        /**dd($file);*/

        if (empty($file)) {
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

                $all_case_files = $this->taskManualUpload->where('case_id', $download_task_file_details->case_id)->get();
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

        /**dump($remembering_caseId_doing);*/
        /**dump($all_case_files);*/
        /**dump(null);*/

        /** send it to s3 */

        //filename to store
        $s3path = 'br24/Jobs/'.$remembering_caseId_doing.'/ready/ready.zip';

        /**dump($s3path);*/

        //Upload File to s3
        $s3Br24Config = config('s3br24');
        $s3 = Storage::disk('s3');
        $bucket = config('filesystems.disks.s3.bucket');

        $local_file = storage_path()."/app".config('s3br24.manual_temp_zip_folder').$remembering_caseId_doing. '/ready.zip';
        /**dump($local_file);*/

        //$s3->put($s3path, file_get_contents($local_file));
        /** enable uplaod fo large files using file stream */
        //$s3->put($s3path, fopen($local_file, 'r+'));
        /** since this probably is working it always gets reset we should probably use the pid method and aws cli to make this work and we can check if it still running if it is let it run if it is not check if the s3 butcker has the file etc */
        /** so that if there are multiple uploads it can handle at least 5 at a time maybe and not have to wait? */

        // alternate menthod to store to get pid for checking ? 
        $alternate_method_s3path = 'br24/Jobs/'.$remembering_caseId_doing.'/ready/ready.zip';
        $cmd = 'aws s3 cp --profile default '.$local_file.' s3://'.$bucket.'/'.$alternate_method_s3path;
        $pid = exec($cmd . " > /dev/null & echo $!;", $output);
        dump('aws pid=' .$pid);
        /** can we store the pid to the taskUpload table */

        $file = $this->taskManualUpload->where('case_id', $remembering_caseId_doing)->first();
        $file->pid = $pid;
        $file->state = 'checking upload s3';
        $file->sending_to_s3 = 2;
        $file->save();
    }



    /**
     * check_the_progress_of_ready_zip_to_s3
     */
    public function check_the_progress_of_ready_zip_to_s3()
    {
        


        /** sometimes the amount of scheduled task keeps growing almost like there is a blockage of some sort. */
        /** and when a job gets notified if there are backlogs of tasks then it doesn't complete correctly */
        /** in fact it actually deletes the jobFolder contetns wasting time and effort */
        $file = $this->taskManualUpload->where('sending_to_s3', 2)->where('state', '=', 'checking upload s3')->orderBy('updated_at', 'asc')->get();
        /**dump($file);*/

        if (empty($file)) {
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

                $all_case_files = $this->taskManualUpload->where('case_id', $download_task_file_details->case_id)->get();
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
        $file = $this->taskManualUpload->where('case_id', $remembering_caseId_doing)->first();



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
            return;
        }else{
            dump($remembering_caseId_doing . ' aws cp s3 pid: '.$file->pid.' is no longer running destined for s3 ready folder');
            /** pid does not exists so we check if the right amount of files have been send to s3 */



            //$remembering_caseId_doing = 10101010;

            $bucket = config('filesystems.disks.s3.bucket');
            $alternate_method_s3path = 'br24/Jobs/'.$remembering_caseId_doing.'/ready/';

            /** create a log file to store the contents of the s3 bucket in human readable form to check whether all the files from the zip have been uploaded and extracted */




            $s3_unzip_log = storage_path()."/logs".config('s3br24.manual_download_log') . 's3_unzip_log';
            exec("mkdir -p $s3_unzip_log");
            /** dd(null); */
            $s3_unzip_log = $s3_unzip_log . '/' . $remembering_caseId_doing . '_s3_ready_unzip_readyfolder.log';


            $cmd = 'aws s3 ls --profile default s3://'.$bucket.'/'.$alternate_method_s3path.' --recursive --human-readable > '.$s3_unzip_log;
            /**dump($cmd);*/
            exec($cmd);
            

            /** open the log file and go through eachline using the current date to see which files have been uploaded to s3 today */
            /** then check against the contents of the zip and compare */

            /** get form the temp upload directory */
            $tempupload_path = storage_path()."/app".config('s3br24.manual_temp_upload_folder').$remembering_caseId_doing;




            $local_temp_upload_find_files_log = storage_path()."/logs".config('s3br24.manual_download_log') . 's3_unzip_log';
            /**exec("mkdir -p $s3_unzip_log");*/
            /** dd(null); */
            $local_temp_upload_find_files_log = $local_temp_upload_find_files_log . '/' . $remembering_caseId_doing . '_s3_ready_unzip_readyfolder_compare_with.log';


            $cmd = "find ".$tempupload_path. " -type f > ".$local_temp_upload_find_files_log;
            /**dump($cmd);*/
            exec($cmd);
            
            
            // exec("find ".$inner_tempUploadfolder." -mindepth 1 -maxdepth 1 -type d | wc -l");

            // $cmd = 'tree ';

            $todays_date = Carbon::now()->format('Y-m-d');
            $yesterdays_date = Carbon::now()->format('Y-m-d');
            
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
            $case_id_to_force_notify_manually = DB::table('bypass_manualdl_filecountcheck_force_notify_s3')->first();
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

                $file = $this->taskManualUpload->where('case_id', $remembering_caseId_doing)->first();

                if ($file->sending_to_s3_tries > config('s3br24.manual_s3_upload_time_allowed_before_retry')) {
                    $file->pid = NULL;
                    $file->sending_to_s3_tries = 0;
                    $file->sending_to_s3 = 1;
                    $file->save();
                    return;
                }else{
                    /** this will move the case_id to the back so that the next inline can be processed asap */
                    $file->sending_to_s3_tries = $file->sending_to_s3_tries + 1;
                    $file->save();
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

                $file = $this->taskManualUploadView->where('case_id', $remembering_caseId_doing)->first();
                /**dump('processing === ' . $file->local);*/
                /**dump($file->local);*/

                $MANUAL_JOBFOLDER_DIR_SHARELOCATION_STRING = env('MANUAL_JOBFOLDER_DIR_SHARELOCATION_STRING');

                //$content .= 'Zip '.$file->type.' extracted to ' . $folder . '';
                if($content == ''){
                    $content .= '```';
                    $content .= str_replace("/", "\\", $MANUAL_JOBFOLDER_DIR_SHARELOCATION_STRING . $file->case_id).'\\ready';
                    $content .= '```';

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
                RCsendUploadJobUploadedtoS3ReadyMessage('', $message);


                $file = $this->taskManualUpload->where('case_id', $remembering_caseId_doing)->first();

                $file->state = 'uploaded to s3';
                $file->sending_to_s3 = 3;
                $file->save();



                /** we need to do some cleaning up and remove files off of the download upload server */
                /** so that when we encounter problems the important logs are still there and un-cluttered */


                /** places we need to remove from temporary upload folder + files*/
                // var/www/src/alpine/storage/app/data_sdb_temp_upload/case_id and everything inside it 
                //exec("sudo rm -R var/www/src/alpine/storage/app/data_sdb_temp_upload/".$remembering_caseId_doing);
                //$path = storage_path()."/app".config('s3br24.manual_temp_upload_folder').$remembering_caseId_doing;
                //$cmd = "sudo rm -R ".$path;
                //exec($cmd);

                /** temporary upload folder zip log */
                // var/www/src/alpine/storage/logs/data_sdb/zip_log/case_id_ready_zip.log
                //exec("sudo rm /var/www/src/alpine/storage/logs/data_sdb/zip_log/".$remembering_caseId_doing."_ready_zip.log");
                $zip_log = storage_path()."/logs".config('s3br24.manual_download_log') . 'zip_log';
                $zip_log = $zip_log . '/' . $remembering_caseId_doing . '_ready_zip.log';
                $cmd = "sudo rm ".$zip_log;
                exec($cmd);


                /** temporary upload folder unzip to jobfolder/ready folder log */
                // var/www/src/alpine/storage/logs/data_sdb/unzip_log/case_id_ready_unzip_jobfolder.log
                //exec("sudo rm /var/www/src/alpine/storage/logs/data_sdb/unzip_log/".$remembering_caseId_doing."_ready_unzip_jobfolder.log");
                $unzip_log = storage_path()."/logs".config('s3br24.manual_download_log') . 'unzip_log';
                $unzip_log = $unzip_log . '/' . $remembering_caseId_doing . '_ready_zip.log';
                $cmd = "sudo rm ".$unzip_log;
                exec($cmd);

                /** temporary upload folder unzip to jobfolder/ready folder log */
                // var/www/src/alpine/storage/logs/data_sdb/unzip_log/case_id_ready_unzip_jobfolder.log
                //exec("sudo rm /var/www/src/alpine/storage/logs/data_sdb/unzip_log/".$remembering_caseId_doing."_ready_unzip_jobfolder.log");
                $unzip_jobfolder_log = storage_path()."/logs".config('s3br24.manual_download_log') . 'unzip_log';
                $unzip_jobfolder_log = $unzip_jobfolder_log . '/' . $remembering_caseId_doing . '_ready_unzip_jobfolder.log';
                $cmd = "sudo rm ".$unzip_jobfolder_log;
                exec($cmd);

                /** temporary ready zip folder + files*/
                // var/www/src/alpine/storage/app/data_sdb_temp_zip/case_id
                //exec("sudo rm -R /var/www/src/alpine/storage/app/data_sdb_temp_zip/".$remembering_caseId_doing);
                $tempZipFolder = storage_path()."/app".config('s3br24.manual_temp_zip_folder').$remembering_caseId_doing;
                $cmd = "sudo rm -R ".$tempZipFolder;
                exec($cmd);
                
                // var/www/src/alpine/storage/logs/data_sdb/s3_unzip_log/case_id_s3_ready_unzip_readyfolder.log
                //exec("sudo rm /var/www/src/alpine/storage/logs/data_sdb/s3_unzip_log/".$remembering_caseId_doing."_s3_ready_unzip_readyfolder.log");
                $s3_unzip_log = storage_path()."/logs".config('s3br24.manual_download_log') . 's3_unzip_log';
                $s3_unzip_log = $s3_unzip_log . '/' . $remembering_caseId_doing . '_s3_ready_unzip_readyfolder.log';
                $cmd = "sudo rm ".$s3_unzip_log;
                exec($cmd);

                // var/www/src/alpine/storage/logs/data_sdb/s3_unzip_log/case_id_s3_ready_unzip_readyfolder_compare_with.log
                //exec("sudo rm /var/www/src/alpine/storage/logs/data_sdb/s3_unzip_log/".$remembering_caseId_doing."_s3_ready_unzip_readyfolder_compare_with.log");
                $local_temp_upload_find_files_log = storage_path()."/logs".config('s3br24.manual_download_log') . 's3_unzip_log';
                $local_temp_upload_find_files_log = $local_temp_upload_find_files_log . '/' . $remembering_caseId_doing . '_s3_ready_unzip_readyfolder_compare_with.log';
                $cmd = "sudo rm ".$local_temp_upload_find_files_log;
                exec($cmd);
            }
        }
    }





    /**
     * archive_old_jobs
     */
    public function archive_old_jobs()
    {

        $dir  = storage_path()."/app".config('s3br24.manual_archive_folder');

        if (!File::isFile($dir)) {
            $archivepath = storage_path().'/app'.config('s3br24.manual_archive_folder');
            File::makeDirectory($archivepath, 0777, true, true); /** make directory */
        }

        /**dd('archive old jobs');*/

        /** we find all of the old jobs ids and see if they have a folder in the job folder .. */
        /** we then go through all of them and attempt to mv them from the jobfolder to the archive folder. */
        /** for manaul downloaded jobs after 30 days from last downloaded deleted it from the shared folder entirely */
        /** do we need to clear it from the database too? */
        /** what happens when they */

        $min_date = Carbon::now()->subMonths(1)->format('Y-m-d');
        /**dump($min_date);*/

        $manualdownloadlist_list_view = DB::table('v_manual_download_files')
        //->whereRaw("`status_of_case` REGEXP '{notified}'")
        ->whereNotIn('archived_case', [2])
        ->when($min_date, function($query) use ($min_date){
            return $query->whereRaw("DATE(vuf_updated_at) <= DATE('$min_date')");
        })
        ->orderByRaw("
            CASE status_of_case
                WHEN 'in progress' THEN 1
                WHEN 'downloaded' THEN 2
                WHEN 'ready' THEN 3
                WHEN 'check' THEN 4
                WHEN 'feedback' THEN 5
                WHEN 'pause' THEN 6
                WHEN 'notified/uploading to s3' THEN 7
                WHEN 'uploaded to s3' THEN 8
                WHEN 'new' THEN 9
                WHEN 'downloading' THEN 10
                WHEN 'retry_zip' THEN 11
                WHEN 'zipped' THEN 12
            END
            ")
        ->orderBy('vuf_updated_at', 'DESC')
        ->get()->keyBy('case_id')->toArray();

        /**dd($manualdownloadlist_list_view);*/

        //$manualdownloadlist_list_view[10101010] = new \stdClass;

        /**dd($manualdownloadlist_list_view);*/

        foreach($manualdownloadlist_list_view as $case_id_key => $download_details){
            /**dump($case_id_key);*/
            /**dump($download_details);*/
            /** we check if the folder exists in the jobfolder directory */
            $jobpath = storage_path().'/app'.config('s3br24.manual_job_folder').$case_id_key;

            if (File::exists($jobpath)) {
                dump('folder exists ' . $case_id_key);
                //$cmd = 'mv '.$jobpath.'/* '.$archivepath.$case_id_key;
                $cmd = 'rsync --ignore-existing --remove-source-files -ar '.$jobpath. ' ' . $archivepath;
                dump($cmd);
                exec($cmd);

                /** check if the jobdirectory folder is empty before deleting */
                $number_of_files_in_jobs_directory = exec("find ".$jobpath." -type f | wc -l");
                dump('$number_of_files_in_jobs_directory '.$case_id_key.' = ' . $number_of_files_in_jobs_directory);

                //$number_of_files_in_archive_directory = exec("find ".$archivepath." -type f | wc -l");
                //dump('$number_of_files_in_archive_directory '.$case_id_key.' = ' . $number_of_files_in_archive_directory);

                if($number_of_files_in_jobs_directory == 0){
                    $cmd = 'rm -R '.$jobpath;
                    exec($cmd);

                    /** we mark the thing as archived in the db */
                    $file = $this->taskManualDownload->where('id', $download_details->id)->first();
                    /**dd($file);*/
                    $file->archived_case = 2;
                    $file->save();
                }
            }
        }
    }


    /**
     * make_database_file_backup
     */
    public function make_database_file_backup()
    {
        $file = database_path().'/database.sqlite';
        $dir = storage_path()."/app".config('s3br24.manual_archive_folder').'DB_BACKUP';

        if (!File::isFile($dir)) {
            $path = storage_path().'/app'.config('s3br24.manual_archive_folder').'DB_BACKUP';
            File::makeDirectory($path, 0777, true, true); /** make directory */
        }

        $today = Carbon::now()->format('Y-m-d');
        $week_ago = Carbon::now()->subDays(7)->format('Y-m-d');
        /**dd($file);*/
        /**dd('make_database_file_backup');*/
        /** daily at a certain time we copy the database file and store it on the archive folder by itself */
        $cmd = 'cp -r '.$file.' '.$dir;
        exec($cmd);

        $cmd = 'mv '.$dir.'/database.sqlite '.$dir.'/database_'.$today.'.sqlite';
        exec($cmd);

        $old_backup = $dir.'/database_'.$week_ago.'.sqlite';
        if (File::isFile($old_backup)) {
            $cmd = 'rm '.$dir.'/database_'.$week_ago.'.sqlite';
            exec($cmd);
        }
    }



    /**
     * Update database
     */
    public function create()
    {
        $td = $this->taskManualDownload->where('state', 'new')->get();
        foreach ($td as $t) {
            $caseId = $t->case_id;

            $totalFolderJobUnzip = $this->taskManualDownloadFile
            ->where('case_id', $caseId)
            ->where('state', 'downloaded')
            ->where('unzip', 2)
            ->count();

            $totalFolderJob = $this->taskManualDownloadFile
            ->where('case_id', $caseId)
            ->count();

            if ($totalFolderJobUnzip != $totalFolderJob) {
                continue;
            }

            $files = $this->taskManualDownloadFile
            ->where('case_id', $caseId)
            ->where('state', 'downloaded')
            ->where('unzip', 2)
            ->get();

            $isUnzip = true;
            $isNew = false;
            $has_mapping_name = 0;
            foreach ($files as $f) {
                if ($f->unzip < 2) {
                    $isUnzip = false;
                }
                if ($f->type == 'new') {
                    $isNew = true;
                }
                if ($f->has_mapping_name == 1) {
                    $has_mapping_name = 1;
                }
            }

            if (($isNew == true) && ($isUnzip == true)) {
                $dir = config('s3br24.manual_download_temp_folder');
                $xmlDir = $dir . 'xml/' . $caseId . ".xml";

                if (!file_exists($xmlDir)) {
                    $handle = fopen($xmlDir,"w");
                    fwrite($handle,"");
                    fclose($handle);
                }

                $cmd = "cp " . $xmlDir . " " . config('s3br24.manual_temp_xml');
                exec($cmd);

                $t->state = 'downloaded';
                $t->save();
            }
            $t->has_mapping_name = $has_mapping_name;
            $t->save();
        }
    }

    /**
     * Scan ready folder and save data into tasks_files table
     *
     * @author anhlx412@gmail.com
     */
    public function scanReadyFolderToUpload()
    {
        $s3Br24Config = config('s3br24');
        /** Create log & write log*/
        createDir(UPLOAD_LOGS_PATH);
        $log = UPLOAD_LOGS_PATH . '/scan-ready-folder-to-upload.log';
        writeLog($log, '------------------------------------------------------------');

        /** Get tasks finish*/
        $tasks = $this->task->where('status', 4)->where('is_spliting', 0)->where('is_upload', 0)->where('is_training', 0)->get();
        foreach ($tasks as $task) {
            /**
             * Scan ready folder & insert to tasks files table
             */
            $task = $this->tasksFiles->setDataTaskFileByScanReadyFolder($task, $s3Br24Config, $log);
            /**
             * Scan from S3 to update folder upload in tasks_files table
             * Update into tasks_ready_folders table and tasks_files table
             */
            $this->tasksReadyFolders->saveDataToDb($task, $log);
            $this->tasksReadyFolders->scanFolderS3ToUpload($log);
            $this->tasksReadyFolders->scanFolderAsiaS3ToUpload($log);

            writeLog($log, '----------------------------END--------------------------------');
        }

        $this->tasksFiles->where('state', 'error')->update(['state' => 'new']);
    }

    /**
     * Run cron job for check finish job
     * Save to uploadProcess.json for calculator percentage upload
     *
     * @author anhlx412@gmail.com
     */
    public function finishJob()
    {
        $jobs = $this->task->calculatorStateUpload();

        $jobUploadSuccess = [];
        $jobInfos = [];

        foreach ($jobs as $job) {
            if ($job->total_file > 0 && $job->total_file == $job->uploaded_file) {
                $jobUploadSuccess[] = $job->id;
            }

            $percentage = ( $job->total_file > 0 ) ? round($job->uploaded_file/$job->total_file*100 ) : 0;
            $jobInfos[$job->id]['percent'] = $percentage;
            $jobInfos[$job->id]['task_id'] = $job->id;
            $jobInfos[$job->id]['case_id'] = $job->case_id;
            $jobInfos[$job->id]['total'] = $job->total_file;
            $jobInfos[$job->id]['uploaded'] = $job->uploaded_file;
            $jobInfos[$job->id]['deleted'] = $job->deleted_file;
            $jobInfos[$job->id]['list_file_deleted'] = '';
        }

        if (!empty($jobUploadSuccess)) {
            $this->task->whereIn('id', $jobUploadSuccess)
            ->update([
                'is_upload' => 2,
                'upload_time' => time()
            ]);
        }

        /**Gui trang thai den trang Job overview thong qua file json. Se duoc cap nhat 3s 1 lan.*/
        shell_exec("chmod -R 777 /home/itadmin/www/br24.new/app/webroot/uploadProcess.json");
        $fp = fopen('/home/itadmin/www/br24.new/app/webroot/uploadProcess.json', 'w');
        fwrite($fp, json_encode($jobInfos));
        fclose($fp);
    }

    public function unZipFolderTemp()
    {
        $br24Config = config('br24config');
        $FOLDER_ZIP_NEW_TMP = $br24Config['FOLDER_ZIP_NEW_TMP'];
        $FOLDER_XMLFILE_TMP = $br24Config['FOLDER_XMLFILE_TMP'];
        $FOLDER_XMLFILE = $br24Config['FOLDER_XMLFILE'];
        $LIM_JOB_CREATE = $br24Config['LIM_JOB_CREATE'];
        $JOBFOLDER = $br24Config['JOBFOLDER'];
        $XML_FTP_VN = $br24Config['XML_FTP_VN'];
        if (!is_dir($FOLDER_ZIP_NEW_TMP)) {
            exec("mkdir -p $FOLDER_ZIP_NEW_TMP && chmod -R 777 $FOLDER_ZIP_NEW_TMP");
        }

        echo date('Y-m-d H:i:s') . "---> Bat dau kiem tra tao job.\n";

        $scandir = scandir($FOLDER_ZIP_NEW_TMP);
        if (!empty($scandir) && sizeof($scandir) > 2) {
            $zipArchive = new \ZipArchive();
            $count_job = 0;
            foreach ($scandir as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                };
                $pathinfo = pathinfo($file);
                if (!isset($pathinfo['extension']) ) {
                    continue;
                }
                if ($pathinfo['extension'] != 'zip') {
                    continue;
                }
                if ($count_job >= $LIM_JOB_CREATE) {
                    break;
                }
                $zipname = explode('_', $pathinfo['filename']);
                if (sizeof($zipname) != 3) {
                    unlink($FOLDER_ZIP_NEW_TMP . $file);
                    echo date('Y-m-d H:i:s') . "---> Cau truc file: $file sai dinh dang. Tool se tu dong xoa file nay va chuyen sang file tiep theo.\n";

                    sendSocketIOMessage('CREATE_JOB_ZIP_ERROR', "$file - create by handle");
                    continue;
                } else {
                    $ar_accept_folder = array('new', 'example');
                    if (!in_array($zipname[2], $ar_accept_folder)) {
                        unlink($FOLDER_ZIP_NEW_TMP . $file);
                        echo date('Y-m-d H:i:s') . "---> Cau truc file: $file sai dinh dang. Tool se tu dong xoa file nay va chuyen sang file tiep theo.\n";

                        sendSocketIOMessage('CREATE_JOB_ZIP_ERROR', "$file - create by handle");
                        continue;
                    }
                }

                $tryOpeningZip = $zipArchive->open($FOLDER_ZIP_NEW_TMP . $file);
                if ($tryOpeningZip !== TRUE) {
                    $check_zip_log = $FOLDER_ZIP_NEW_TMP . "$file.log";
                    exec("unzip -t ". $FOLDER_ZIP_NEW_TMP . $file ." > $check_zip_log");
                    $searchString = 'End-of-central-directory signature not found.  Either this file is not';
                    if(exec('grep ' . escapeshellarg($searchString) . ' ' . $check_zip_log)) {
                        exec("rm $check_zip_log");

                        echo date('Y-m-d H:i:s') . "---> File:  $file chua toan ven ....\n";
                        echo date('Y-m-d H:i:s') . "---> Errors:  $tryOpeningZip\n";
                        continue;
                    }
                    exec("rm $check_zip_log");
                }
                $zipArchive->close();

                $case_id = $zipname[1];
                $folder_type = $zipname[2];

                XmlFile::where('jobId', $case_id)->delete();

                $task = Task::where('case_id', $case_id)->select(
                    'id',
                    DB::raw("(select count(1) from staff_jobs where task_id = tasks.id limit 1) as count_staffjob")
                )->first();

                $job_folder = $JOBFOLDER . DIRECTORY_SEPARATOR . $case_id . DIRECTORY_SEPARATOR;
                if (!is_dir($job_folder)) {
                    exec("mkdir -p $job_folder");
                }

                if (isset($task->id)) { /**exists job*/
                    if (1*$task->count_staffjob > 0) {
                        echo date('Y-m-d H:i:s') . "---> Them folder vao job da ton tai -> $case_id \n";

                        if ($folder_type == 'example') {
                            echo date('Y-m-d H:i:s') . "---> Tao Example. \n";

                            $example_folder = $job_folder . 'examples' . DIRECTORY_SEPARATOR;
                            if (!is_dir($example_folder)) {
                                exec("mkdir -p $example_folder");
                            }

                            if (file_exists($FOLDER_ZIP_NEW_TMP . $file)) {
                                $total_data = filesize($example_folder . $file);
                                $this->move_copy_file('move_unzip', $FOLDER_ZIP_NEW_TMP, $example_folder, $file);

                                TransferDataLog::writeLog($case_id, 'unzip', 1, $total_data);
                            }
                            exec("chmod -R 777 $job_folder");
                            continue;
                        } else {
                            $new_folder = $job_folder . 'new' . DIRECTORY_SEPARATOR;
                            if (!is_dir($new_folder)) {
                                exec("mkdir -p $new_folder");
                            }
                            $total_data = filesize($new_folder . $file);
                            $this->move_copy_file('move_unzip', $FOLDER_ZIP_NEW_TMP, $new_folder, $file);
                            TransferDataLog::writeLog($case_id, 'unzip', 1, $total_data);

                            echo date('Y-m-d H:i:s') . '---> Giai nen thanh cong. Bat dau sap xep lai...' . "\n";
                            $this->sortNewJob($new_folder, $case_id);
                            echo date('Y-m-d H:i:s') . '--->' . " Sap xep lai thanh cong . copy file xml ve thu muc xmlfiles ... \n";
                            if (is_file($FOLDER_XMLFILE_TMP . DIRECTORY_SEPARATOR . $case_id . '.xml') && file_exists($FOLDER_XMLFILE_TMP . DIRECTORY_SEPARATOR . $case_id . '.xml')) {
                                $this->move_copy_file('copy', $FOLDER_XMLFILE_TMP . DIRECTORY_SEPARATOR, $FOLDER_XMLFILE . DIRECTORY_SEPARATOR, $case_id . '.xml');
                                $this->move_copy_file('move', $FOLDER_XMLFILE_TMP . DIRECTORY_SEPARATOR, $XML_FTP_VN . DIRECTORY_SEPARATOR, $case_id . '.xml');
                            }

                            exec("chmod -R 777 $new_folder");

                        }
                    }
                }
            }
        }
    }

    private function sortNewJob($path = null, $case_id = null)
    {
        $br24Config = config('br24config');
        $JOBFOLDER = $br24Config['JOBFOLDER'];
        $path_cut = $case_id . DIRECTORY_SEPARATOR . 'new' . DIRECTORY_SEPARATOR;
        $new_path = explode($path_cut, $path);
        if (isset($new_path[1]) && $new_path[1] == '') {
            $dir_path = $JOBFOLDER . DIRECTORY_SEPARATOR . $case_id . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR;
        } else {
            $dir_path = $JOBFOLDER . $case_id . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . $new_path[1] . DIRECTORY_SEPARATOR;
        }

        $cdir = scandir($path);
        $forbidden_extensions = ar_file_work();
        foreach ($cdir as $value) {
            if ($value == '.' || $value == '..') {
                continue;
            };
            if (is_dir($path . $value)) {
                if (strpos($value, 'MACOSX') !== false) {
                    exec('rm -r ' . $path . $value);
                    continue;
                } else if (strpos(strtolower($value), 'example') !== false) {
                    exec("mkdir -p $dir_path && chmod -R 777 $dir_path");
                    $delete[] = $path . $value;
                }
                $this->sortNewJob($path . $value . DIRECTORY_SEPARATOR, $case_id);
            } else {
                $pathinfo = pathinfo($value);
                if (strpos("INSTRUCTIONS", $value) !== false) {
                    if(!is_file_working($pathinfo['extension'], $forbidden_extensions)){
                        exec('rm ' . $path . $value);
                        continue;
                    }
                }
                /** use option -oO UTF8 to force character encoding on unzip filenames entirely */
                if ($pathinfo['extension'] == 'zip') {
                    $cmd = "mkdir -p " . $path . $pathinfo['filename'];
                    $cmd .= " && " . "mv " . $path. $value . " " . $path . $pathinfo['filename'] . DIRECTORY_SEPARATOR. $value;
                    $cmd .= " && unzip -o " . $path . $pathinfo['filename'] . DIRECTORY_SEPARATOR. $value . " -d " . $path . $pathinfo['filename'];
                    exec($cmd);
                    $this->sortNewJob($path . $pathinfo['filename'] . DIRECTORY_SEPARATOR, $case_id);
                }
                /**move file di*/
                if ((strpos(strtolower($path . $value), 'example') !== false)) {
                    $cmd = "mv " . $path. $value . " " . $dir_path. $value;
                    exec($cmd);
                    $delete[] = $path;
                }
            }
        }
        if (!empty($delete)) {
            foreach ($delete as $fileDel) {
                if (file_exists($fileDel)) {
                    exec("rm -r $fileDel");
                }
            }
        }
    }

    private function move_copy_file($type = null, $current_path = null , $new_path = null , $file = null)
    {
        /** use option -oO UTF8 to force character encoding on unzip filenames entirely */
        if ($type == 'move_unzip') {
            $cmd = "mv " . $current_path . $file . " " . $new_path . $file;
            $cmd .= " && " . "unzip -o " . $new_path . $file . " -d " . $new_path;
            exec($cmd);
        } elseif ($type == 'move') {
            $cmd = "mv " . $current_path . $file . " " . $new_path . $file;
            exec($cmd);
        } else {
            $cmd = "cp " . $current_path . $file . " " . $new_path . $file;
            exec($cmd);
        }
    }
}
