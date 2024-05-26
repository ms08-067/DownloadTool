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

class AutoDL_unzipchecks implements ShouldQueue
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
        $this->onQueue('autodl_unzipchecks');
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

            /** get the first downloaded file detail */
            /** find a caseID that has all zips downloaded and treat the case as a whole */

            $file = TaskDownloadFile::select('*')
            ->when($caseId, function($query) use ($caseId){
                return $query->where("case_id", $caseId);
            })
            ->when($caseIdtype, function($query) use ($caseIdtype){
                return $query->where("type", $caseIdtype);
            })
            ->where('state', 'downloaded')->where('unzip', 0)->first();

            if(empty($file) || $file == null) {
                /** if it cannot find the row.. how do we handle it then? usually the db values should already be there. reaching here should never be the case.. if the job was dispatched */
                /** only if it is a timing issue?? */
                /** for testing we let it pop fromt he queue and log it */
                Loggy::write('default', json_encode([
                    'success' => false,
                    'description' => 'function AutoDL_unzipchecks handle() could not find row on taskDownloadFile table using query parameters',
                    'caseId' => $caseId,
                    'caseIdtype' => $caseIdtype,
                    'query1' => 'state = downloaded',
                    'query2' => 'unzip = 0'
                ]));
                return;
            }

            /** get existing retry count */
            $unzip_tries = $file->unzip_tries;

            if($file->xml_title_contents == ''){
                /**<jobTitle><![CDATA[CGI Raumerstellung + Staging (br24_sarraj) Matze]]></jobTitle>*/
                /** get the jobTitle from the xml and store it with the zip to be used with the RC message */
                $s3Br24Config = config('s3br24');
                $download_temp_folder = $s3Br24Config['download_temp_folder'];

                $downloaded_xml_file_dir = storage_path()."/app".$download_temp_folder . "xml/".$file->case_id. '.xml';

                if(File::exists($downloaded_xml_file_dir)){
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

            if ($unzip_tries > config('s3br24.unzip_retry_count')) {
                $file->unzip = 3; /** unzip error */
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

            /**check zip file can be opened and extracted */
            $zipArchive = new \ZipArchive();
            $tryOpeningZip = $zipArchive->open($dirZip);

            dump('$tryOpeningZip');
            dump($tryOpeningZip);

            if ($tryOpeningZip !== TRUE) {
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

                if ($unzip_tries == config('s3br24.unzip_retry_count')) {
                    /** should we try to download again and see if that helps? */
                    $message = array(
                        'title' => $file->case_id,
                        'content' => 'XML is OK but Zip is ERROR (Cannot open to unzip)',
                        'error_code_desc' => $error_code_desc,
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
                }

                Loggy::write('default', json_encode([
                    'success' => false,
                    'description' => 'XML is OK but Zip is ERROR (Cannot open to unzip)',
                    'caseId' => $caseId,
                    'caseIdtype' => $caseIdtype,
                    'message' => $message
                ]));

                /** remove from this queue and add to the download queue after saving the data to let that happen */
                \App\Jobs\AutoDL_download::dispatch($file->case_id, $file->type)->delay(now()->addSeconds($queue_delay_seconds_autodl));
                return;
            }

            if ($zipArchive->numFiles <= 0) { /** zip empty */
                $file->unzip = 2; /** unzip complete */
                $file->save();

                /** when there are no files in the zip file here we can safely dispatch to the next queue  */
                /** or even just set the state to notified is that better? */
                \App\Jobs\AutoDL_unzip::dispatch($file->case_id, $file->type);
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
                $progress_log = $progress_log . '/' . $file->case_id . '_new_unziptest_progress.log';
            } else if ($file->type == 'example') {
                $unzip_log = $unzip_log . '/' . $file->case_id . '_example.log';
                $progress_log = $progress_log . '/' . $file->case_id . '_example_unziptest_progress.log';
            } else if ($file->type == 'ready') {
                $unzip_log = $unzip_log . '/' . $file->case_id . '_ready.log';
                $progress_log = $progress_log . '/' . $file->case_id . '_ready_unziptest_progress.log';
            }else{
                dump('unZipChecks() encountered a type we did not expect');
            }

            dump('$unzip_log');
            dump($unzip_log);

            dump('File::exists($unzip_log)');
            dump(File::exists($unzip_log));

            $searchString = 'testing:';
            if(File::exists($unzip_log)){
                dump($unzip_log . ' exists');
                try {
                    if(exec('grep ' . escapeshellarg($searchString) . ' ' . $unzip_log)) {
                        /** if it has been tested then don't need to test again*/
                    }else{
                        /** perform test of zip */
                        /** -l option lists the items in the zip */
                        /** so then why do you need to do this ? */
                        exec("unzip -l $dirZip >> $unzip_log");

                        /** test the zip that has been downloaded .... */
                        if($file->type == 'example'){
                            $cmd = "unzip -t " . $dirZip . ' >> '.$unzip_log;
                            //$cmd = "unzip -t " . $dirZip .' | pv -l -s '.$zipArchive_numFiles.' -pabtWf -i 0.5 >> '.$unzip_log . ' 2>> ' . $progress_log;
                            //$folder = $exampleFolder;
                        }else if($file->type == 'new'){
                            $cmd = "unzip -t " . $dirZip . ' >> '.$unzip_log;
                            //$cmd = "unzip -t " . $dirZip .' | pv -l -s '.$zipArchive_numFiles.' -pabtWf -i 0.5 >> '.$unzip_log . ' 2>> ' . $progress_log;
                            //$folder = $newFolder;
                        }else if($file->type == 'ready'){
                            $cmd = "unzip -t " . $dirZip . ' >> '.$unzip_log;
                            //$cmd = "unzip -t " . $dirZip .' | pv -l -s '.$zipArchive_numFiles.' -pabtWf -i 0.5 >> '.$unzip_log . ' 2>> ' . $progress_log;
                            //$folder = $readyFolder;
                        }else{
                            dump('unZipChecks() encountered a type we did not expect');
                            /** like a ready type we should just get out of the function */
                        }
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
                if($file->type == 'example'){
                    $cmd = "unzip -t " . $dirZip . ' >> '.$unzip_log;
                    //$cmd = "unzip -t " . $dirZip .' | pv -l -s '.$zipArchive_numFiles.' -pabtWf -i 0.5 >> '.$unzip_log . ' 2>> ' . $progress_log;
                    //$folder = $exampleFolder;
                }else if($file->type == 'new'){
                    $cmd = "unzip -t " . $dirZip . ' >> '.$unzip_log;
                    //$cmd = "unzip -t " . $dirZip .' | pv -l -s '.$zipArchive_numFiles.' -pabtWf -i 0.5 >> '.$unzip_log . ' 2>> ' . $progress_log;
                    //$folder = $newFolder;
                }else if($file->type == 'ready'){
                    $cmd = "unzip -t " . $dirZip . ' >> '.$unzip_log;
                    //$cmd = "unzip -t " . $dirZip .' | pv -l -s '.$zipArchive_numFiles.' -pabtWf -i 0.5 >> '.$unzip_log . ' 2>> ' . $progress_log;
                    //$folder = $readyFolder;
                }else{
                    dump('unZipChecks() encountered a type we did not expect');
                    /** like a ready type we should just get out of the function */
                }
                dump('$cmd');
                dump($cmd);
                exec($cmd);
            }

            /** probably when the file is opened it cannot be written to by the zip test */
            /** you need to switch to the other file_get_contents method of reading from the log to be as least disruptive as possible */


            if(File::exists($unzip_log)){
                dump($unzip_log . ' exist after exec');
                /** we need to separate out the rest of this function to another queue? */

                /** at this stage we need to check the unzip test log that was created for the zip for any indication that there were errors in which case need to trigger the download again.. and keep trying until we can be sure that the file is absolutely corrupt and not just due to network issues ? */
                /** since we are only doing one file per command */
                /** if the check of the log for this particular zip download has any error then attempt to download again .. and exit out reverting the row on taskdownloadfiles back to zip column 0  */
                /** until the next cron job */
                /** */

                if($file->state == 'unzipped' || $file->state == 'unzipping'){
                    /** a zip from the same case coming back to be re-unzipped because not enough files in extracted directory if one of the other zips in the case is unzipped coming back here will not find the log for the unzipped file so errors make it go to the next file in the case*/
                    Loggy::write('default', json_encode([
                        'success' => false,
                        'description' => '($file->state == "unzipped" || $file->state == "unzipping"',
                        'caseId' => $caseId,
                        'caseIdtype' => $caseIdtype
                    ]));
                    return;
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

                if($found_end_of_zip_test == false){
                    /** put it back in this queue to be checked again */
                    \App\Jobs\AutoDL_unzipchecks::dispatch($file->case_id, $file->type)->delay(now()->addSeconds($queue_delay_seconds_autodl));
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
                    exec("rm -R " . $unzip_log);

                    if ($unzip_tries == config('s3br24.unzip_retry_count')) {
                        $message = array(
                            'title' => $file->case_id,
                            'content' => 'XML is OK but Zip downloaded has CRC ERROR (Cannot unzip). Have tried downloading already '. $unzip_tries . ' times.',
                            'link' => null,
                            'to' => config('br24config.rc_notify_usernames')
                        );

                        $messenger_destination = env("MESSENGER_DESTINATION", "ROCKETCHAT");
                        dump($messenger_destination);
                        if($messenger_destination == 'BITRIX'){
                            BTRXsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                        }else if($messenger_destination == 'ROCKETCHAT'){

                            RCsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);

                        }else{
                            /** this will not put the job back into the queue */
                            /** but if by some chance there was a job */
                            /** put it back in this queue to be checked again */
                            \App\Jobs\AutoDL_download::dispatch($file->case_id, $file->type)->delay(now()->addSeconds($queue_delay_seconds_autodl));
                            return;
                        }
                    }
                    /** i still want the function to check CRC or errors for the rest of the zips of this caseID */

                    /** here we allow the queue to remove the job from the queue since the file needs to be downloaded again. */
                    /** but this time using the queue way */
                    \App\Jobs\AutoDL_download::dispatch($file->case_id, $file->type)->delay(now()->addSeconds($queue_delay_seconds_autodl));
                    return;
                }else{
                    $file->unzip = 1;
                    $file->unzip_tries = $unzip_tries + 1;
                    $file->unzip_checks = 1;
                    $file->save();

                    /** here we can safely dispatch to the unzipCheck queue */
                    \App\Jobs\AutoDL_unzip::dispatch($file->case_id, $file->type);
                    return;
                }

            }else{
                dump($unzip_log . ' does not exist after exec');
                /** put it back in this queue to be checked again */
                \App\Jobs\AutoDL_unzipchecks::dispatch($file->case_id, $file->type)->delay(now()->addSeconds($queue_delay_seconds_autodl));
                return;
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
            'description' => 'AutoDL_unzipchecks failed()',
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
                if(env('APP_ENV') == 'prod'){
                }
                if(env('APP_ENV') == 'dev' || env('APP_ENV') == 'test'){
                }
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
