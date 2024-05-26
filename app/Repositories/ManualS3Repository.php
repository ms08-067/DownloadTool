<?php

namespace App\Repositories;

use App\Models\Task;
use App\Models\TaskDownload;
use App\Models\TaskDownloadFile;
use App\Models\TasksFiles;
use App\Models\TaskManualDownload;
use App\Models\TaskManualDownloadFile;
use Aws\Exception\MultipartUploadException;
use Aws\S3\MultipartUploader;
use Aws\S3\ObjectUploader;
use Carbon\Carbon;
use Loggy;
use Debugbar;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Aws\S3\S3Client;  
use Aws\Exception\AwsException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use App\Repositories\AppUserRepository;

/**
 * Class ManualS3Repository
 *
 * @author lexuananh@br24.com
 * @package App\Repositories
 */
class ManualS3Repository extends Repository
{
    public $taskDownloadFile;
    public $taskDownload;
    public $task;
    public $tasksFiles;
    public $taskManualDownload;
    public $taskManualDownloadFile;

    /**
     * @var AppUserRepository
     */
    protected $appuserRepo;

    /**
     * ManualS3Repository constructor.
     *
     * @param TaskDownloadFile $taskDownloadFile
     * @param TaskDownload $taskDownload
     * @param Task $task
     * @param TasksFiles $tasksFiles
     */
    public function __construct(
        TaskDownloadFile $taskDownloadFile, 
        TaskDownload $taskDownload, 
        Task $task, 
        TasksFiles $tasksFiles,
        TaskManualDownload $taskManualDownload,
        TaskManualDownloadFile $taskManualDownloadFile,
        AppUserRepository $appuserRepo
    )
    {
        $this->appuserRepo = $appuserRepo;
        //$this->taskDownloadFile = $taskDownloadFile;
        //$this->taskDownload = $taskDownload;
        $this->task = $task;
        $this->tasksFiles = $tasksFiles;
        $this->taskManualDownload = $taskManualDownload;        
        $this->taskManualDownloadFile = $taskManualDownloadFile;
    }

    public function scan($mdl_checkbox_example, $mdl_checkbox_new, $mdl_checkbox_ready, $last_updated_by, $caseID = null, $xlmFiles = [])
    {
        $s3Br24Config = config('s3br24');
        $s3 = Storage::disk('s3');
        //$xlmFiles = $s3->files($s3Br24Config['xml_dir']);
        $bucket = config('filesystems.disks.s3.bucket');

        //dump($s3Br24Config);
        //dump($s3);
        //dump($xlmFiles);
        //dd($bucket);
        if(empty($xlmFiles)){
            /** if the scheduler handles it for the manual downloads how to mark if the xml has been downloaded? on the manual task download table? they will all be kept there as new... */
            /** think about the time it needs to force redownload or if the job has been deleted after 30 days */

            /** most definitely comming from the scheduler */
            /** it is at this time we was to scan whether the manual dl job xml has been downloaded.. */

            /** only the xml jobs that have been created should be turned to downloaded */
            // $task = $this->taskManualDownload->where('state', 'new')->get()->toArray();
            // foreach($task as $generic_key => $details) {
            //     $this->taskManualDownload->where('case_id', $details['case_id'])->where('created_xml', 1)->update(['state' => 'downloaded']);
            // }
            /** it does not matter should stick to being state = new for everything */
        }else{

            Loggy::write('manual_download_freshdownload', 'scan() $caseID => ' . json_encode($caseID));
            Loggy::write('manual_download_freshdownload', 'scan() $xlmFiles => '.json_encode($xlmFiles));
            Loggy::write('manual_download_freshdownload', 'scan() $mdl_checkbox_example => '.json_encode($mdl_checkbox_example));
            Loggy::write('manual_download_freshdownload', 'scan() $mdl_checkbox_new => '.json_encode($mdl_checkbox_new));
            Loggy::write('manual_download_freshdownload', 'scan() $mdl_checkbox_ready => '.json_encode($mdl_checkbox_ready));

            $this->getInfoFromS3duplicateOntestBucket(
                $s3,
                $bucket,
                $xlmFiles,
                $s3Br24Config['xml_tmp'],
                $s3Br24Config['xml_not_zip'],
                $s3Br24Config['job_dir'],
                $s3Br24Config['manual_download_temp_folder'],
                $mdl_checkbox_example, 
                $mdl_checkbox_new, 
                $mdl_checkbox_ready,
                $last_updated_by
            );
            // $this->getInfoFromS3(
            //     $s3,
            //     $bucket,
            //     $xlmFiles,
            //     $s3Br24Config['xml_tmp'],
            //     $s3Br24Config['xml_not_zip'],
            //     $s3Br24Config['job_dir'],
            //     $s3Br24Config['download_temp_folder']
            // );
        }
    }

    public function scanAsia()
    {
        // $s3Br24Config = config('s3br24');
        // $s3 = Storage::disk('s3_asia');
        // $xlmFiles = $s3->files($s3Br24Config['xml_dir_asia']);

        // $bucket = config('filesystems.disks.s3_asia.bucket');

        // $this->getInfoFromS3(
        //     $s3,
        //     $bucket,
        //     $xlmFiles,
        //     $s3Br24Config['xml_tmp_asia'],
        //     $s3Br24Config['xml_not_zip_asia'],
        //     $s3Br24Config['job_dir'],
        //     $s3Br24Config['download_temp_folder']
        // );
    }

    private function getInfoFromS3duplicateOntestBucket($s3, $bucket, $xlmFiles, $xml_tmp_asia, $xml_not_zip_asia, $job_dir, $download_temp_folder, $mdl_checkbox_example, $mdl_checkbox_new, $mdl_checkbox_ready, $last_updated_by)
    {
        /**$client = $s3->getDriver()->getAdapter()->getClient();*/
        $expiry = "+7 days";

        $client = new S3Client([
            'version' => 'latest',
            'profile' => 'default',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            // by not defining the access id and access key here the aws sdk will try to look for it as an environment variable 
            // 'credentials' => array(
            //     'key' => env('AWS_ACCESS_KEY_ID'),
            //     'secret'  => env('AWS_SECRET_ACCESS_KEY')
            // )
        ]);

        $downloadXmlLog = config('s3br24.manual_download_log') . date('Y_m_d') . '_downloadXmlLog.log';
        $downloadJobErrorLog = config('s3br24.manual_download_log') . date('Y_m_d') . '_downloadJobErrorLog.log';

        if (!File::isFile(storage_path().'/logs'.$downloadJobErrorLog)) {
            $path = storage_path().'/logs'.config('s3br24.manual_download_log');
            File::makeDirectory($path, 0777, true, true); /** make directory */
            File::put(storage_path().'/logs'.$downloadJobErrorLog, '');
        }

        if (!File::isFile(storage_path().'/logs'.$downloadXmlLog)) {
            $path = storage_path().'/app'.config('s3br24.manual_download_temp_folder');
            File::makeDirectory($path, 0777, true, true); /** make directory */
            File::put(storage_path().'/logs'.$downloadXmlLog, '');

            $downloadXmlLog = storage_path().'/logs'.$downloadXmlLog;
        }else{
            $downloadXmlLog = storage_path().'/logs'.$downloadXmlLog;
        }

        /** test write a line to one of the log files*/
        /**\App\Facades\CustomLog::error('', $downloadJobErrorLog);*/
        /** test write a line to another log file*/
        /**\App\Facades\CustomLog::error('', $downloadXmlLog);*/

        // dump($s3);
        // dump($bucket);
        // dump('$xlmFiles');
        // dump($xlmFiles);
        // dump('$xml_tmp_asia');
        // dump($xml_tmp_asia);
        // dump('$xml_not_zip_asia');
        // dump($xml_not_zip_asia);
        // dump('$job_dir');
        // dump($job_dir);
        // dump('$download_temp_folder');
        // dd($download_temp_folder);
        
        $caseId = null;
        /**dump('========================= foreach Start ==========================');*/
        foreach ($xlmFiles as $xlm) {
            /**dump($xlm);*/
            Loggy::write('manual_download_freshdownload', 'getInfoFromS3duplicateOntestBucket() $xlm => '.json_encode($xlm));

            /**check exits task*/
            $caseId = basename($xlm, ".xml");
            /**dump('$caseId');*/
            /**dump($caseId);*/

            //$task = $this->task->where('case_id', $caseId)->orWhere('jobIdTitle', $caseId)->get()->first();
            $task = $this->taskManualDownload->where('case_id', $caseId)->get()->first();

            Loggy::write('manual_download_freshdownload', 'getInfoFromS3duplicateOntestBucket() $task => '.json_encode($task));

            $fileName = basename($xlm);
            /**dump('$fileName');*/
            /**dump($fileName);*/

            if ($task) {

                /** make sure we don't move anything when manually downloading */
                /** just mark row on taskManualDownload as downloaded (xml)*/
                /** so that the scheduler can begin downloading */
                
                /**move to xml tmp*/
                // $xmlTmp = $xml_tmp_asia . date('Y-m') . '/' . $fileName;
                // if ($s3->exists($xmlTmp)) {
                //     $s3->delete($xmlTmp);
                // }
                // $s3->move($xlm, $xmlTmp);

                //$this->taskManualDownload->where('case_id', $caseId)->update(['state' => 'downloaded']);
            } else {

                $taskD = $this->taskManualDownload->where('case_id', $caseId)->get()->first();
                if ($taskD) {
                    continue;
                }

                $modifyDate = $s3->lastModified($xlm);
                /**dump('$modifyDate');*/
                /**dump($modifyDate);*/


                /**move xml (that has no zip (not tracked by this task)) to folder only after older than one day */
                // if (time() - $modifyDate > 24 * 60 * 60) {
                //     // $fileMoveNotZip = $xml_not_zip_asia . $fileName;
                //     // /**dump('$fileMoveNotZip');*/
                //     // /**dump($fileMoveNotZip);*/
                //     // if ($s3->exists($fileMoveNotZip)) {
                //     //     $s3->delete($fileMoveNotZip);
                //     // }
                //     // $s3->move($xlm, $fileMoveNotZip);
                //     continue;
                // }





                /** by getting here we should check if the new folder has contents to determine if there should be a new.zip */
                /** by getting here we should check if the example folder has contents to determine if there should be a example.zip */

                $exampleFolder = $job_dir . $caseId . "/example/";
                $exampleFolderFiles = $s3->files($exampleFolder);

                $newFolder = $job_dir . $caseId . "/new/";
                $newFolderFiles = $s3->files($newFolder);

                $readyFolder = $job_dir . $caseId . "/ready/";
                $readyFolderFiles = $s3->files($readyFolder);

                $case_should_have_new_zip = false;
                $case_should_have_example_zip = false;
                $case_should_have_ready_zip = false;
                if(count($newFolderFiles) > 0){$case_should_have_new_zip = true;}
                if(count($exampleFolderFiles) > 0){$case_should_have_example_zip = true;}
                if(count($readyFolderFiles) > 0){$case_should_have_ready_zip = true;}



                /**scan zip file*/
                $zipFolder = $job_dir . $caseId . "/zip/";
                $zipFiles = $s3->files($zipFolder);

                Loggy::write('manual_download_freshdownload', 'getInfoFromS3duplicateOntestBucket() $zipFiles => '.json_encode($zipFiles));
                /**dump('$zipFolder');*/
                /**dump($zipFolder);*/

                /**dump('$zipFiles');*/
                /**dump($zipFiles);*/


                /** we check if the zips are already compressed by the german tool by comparing whether the caseId folders have contents and whether the zips exist */
                /** if there are files in the folders but not enough zips this could probably mean that the zipping preccess has not yet finished?  */
                /** can we still continue to go forwards or do we */

                $case_has_new_zip = false;
                $case_has_example_zip = false;
                $case_has_ready_zip = false;
                if (!empty($zipFiles)) {
                    /** it has some zip files */
                    /** check if */
                    foreach ($zipFiles as $zip) {
                        if (strpos($zip, "new.zip") > 0) {$case_has_new_zip = true;}
                        if (strpos($zip, "example.zip") > 0) {$case_has_example_zip = true;}
                        if (strpos($zip, "ready.zip") > 0) {$case_has_ready_zip = true;}
                    }
                }else{
                    /***/
                    /***/
                }

                /** you need to modify this as the workflow has changed now. after implementing the manual download */

                if($case_should_have_new_zip && !$case_has_new_zip){
                    /** there is no new zip but there should be */
                    /** do some more waiting */
                    /** we skip this xml for a little bit */
                    if (time() - $modifyDate > 2 * 60 * 60) { /** if more than 2 hour, it still has not zip file --> notify */
                        $logContent = '[MANUALDOWNLOAD] XML is OK but new zip folder is EMPTY';
                        $searchString = "$caseId - $logContent";
                        $r = exec('grep ' . escapeshellarg($searchString) . ' ' . storage_path().'/logs'.$downloadJobErrorLog);

                        if (!($r && !empty($r))) {
                            \App\Facades\CustomLog::error($searchString, $downloadJobErrorLog);

                            $message = array(
                                'title' => $caseId,
                                'content' => $logContent,
                                'link' => null,
                                'to' => config('br24config.notify_user_id')
                            );
                            $messenger_destination = env("MESSENGER_DESTINATION", "ROCKETCHAT");
                            /**dump($messenger_destination);*/
                            if($messenger_destination == 'BITRIX'){
                                BTRXsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                            }else if($messenger_destination == 'ROCKETCHAT'){
                                //sendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                                RCsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                                /** ROCKETCHAT */
                            }else{
                                /** do not handle */
                            }
                        }
                    }
                    continue;
                }
                if($case_should_have_example_zip && !$case_has_example_zip){
                    /** there is no example zip but there should be */



                    /** we skip this xml for a little bit */
                    if (time() - $modifyDate > 2 * 60 * 60) { /** if more than 2 hour, it still has not zip file --> notify */
                        $logContent = '[MANUALDOWNLOAD] XML is OK but example zip folder is EMPTY';

                        $customer_number = $this->get_customer_number_from_xml($caseId, false);
                        if($customer_number == '132558' || $customer_number == '200070' || $customer_number == '200219'){
                            /** something happened and now the workflow has changed and its going to place the example folder in the new zip */
                            /** I don't think you want to make it a general rule for every customer or just one specific customer, since it only affects the roof tile company and their auto generated jobs. */
                            /** when it has reached a certain point and the example zip is still found to not be generated we do an additional check */
                            /** we check if the new zip has the example folder ... */
                            /** we download the new zip, extract it and then check the contents .. */
                            /** we are forced to limit this special check that the example folder is in new zip to the customer that does the floor tiles .. otherwise a 4GB example folder or 4GB new folder will be downloaded twice. */
                            /** where would we download it to? */
                            /** perhaps we could generate the zip here and upload it to s3 bucket. ha! */
                            
                            $this->initiate_special_example_zip_not_present_function($caseId);

                            /** hoping that when it finishes that the zip can be there */
                            /** to avoid continuing to the next xml and immediately download */
                            /** would be nice to be able to just trigger the zipper from here.. it is what it is.. */

                            $logContent .= ' [trying to check and treat special case for customer number = ' .$customer_number .']';
                        }

                        $searchString = "$caseId - $logContent";
                        $r = exec('grep ' . escapeshellarg($searchString) . ' ' . storage_path().'/logs'.$downloadJobErrorLog);

                        if (!($r && !empty($r))) {
                            \App\Facades\CustomLog::error($searchString, $downloadJobErrorLog);

                            $message = array(
                                'title' => $caseId,
                                'content' => $logContent,
                                'link' => null,
                                'to' => config('br24config.notify_user_id')
                            );
                            $messenger_destination = env("MESSENGER_DESTINATION", "ROCKETCHAT");
                            /**dump($messenger_destination);*/
                            if($messenger_destination == 'BITRIX'){
                                BTRXsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                            }else if($messenger_destination == 'ROCKETCHAT'){
                                //sendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                                RCsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                                /** ROCKETCHAT */
                            }else{
                                /** do not handle */
                            }
                        }
                    }
                    continue;
                }

                if($case_should_have_ready_zip && !$case_has_ready_zip){
                    /** there is no ready zip but there should be */
                    /** we really don't handle if there is a ready zip just as long as the example and new zips if needed for the caseID has been thoroughly processed already so exists on the tasks_downloads_files table notified */
                    /** which will fail anyways earlier on in the script */
                    //continue;
                }



                /** what sort of information do we need to store on the db and at which stage do we need to go into the db to check if it has been stored previously */
                /** and for what reason would that be useful to know that */
                /** what do we do with that information and how to alert that something has changed... */


                if (!empty($zipFiles)) {
                    $isNew = false;
                    $hasNewInDB = false;


                    foreach ($zipFiles as $zip) {

                        /** every zip file found with the same caseID */

                        $taskFileE = $this->taskManualDownloadFile->where('live', $zip)->get()->first();
                        //$taskFileE = DB::table('tasks_downloads_files')->where('live', $zip)->get()->first();
                        /**dump($taskFileE);*/

                        if ($taskFileE) {
                            dump('===================================== stopping due to xml file already on the db ==============================');
                            /**dd($taskFileE);*/

                            if (strpos($zip, "new.zip") > 0) {
                                $hasNewInDB = true;
                            }
                            continue;
                        }


                        if ((strpos($zip, "example.zip") !== false && $mdl_checkbox_example == 'true') || (strpos($zip, "new.zip") !== false && $mdl_checkbox_new == 'true') || (strpos($zip, "ready.zip") !== false && $mdl_checkbox_ready == 'true')) {

                            try {
                                $type = 'example';

                                if (strpos($zip, "new.zip") > 0) {
                                    $type = 'new';
                                    $isNew = true;

                                    $this->taskManualDownload->where('case_id', $caseId)->delete();
                                    //DB::table('tasks_downloads')->where('case_id', $caseId)->delete();

                                    /**insert db*/
                                    $data = [
                                        'case_id' => $caseId,
                                        'state' => 'new',
                                        'try' => 0,
                                        'time' => time(),
                                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                        'created_xml' => '2',
                                        'last_updated_by' => $last_updated_by
                                    ];

                                    $this->taskManualDownload->insert($data);
                                    //DB::table('tasks_downloads')->insert($data);

                                    /**download xlm file*/
                                    $command = $client->getCommand('GetObject', [
                                        'Bucket' => $bucket,
                                        'Key' => $xlm
                                    ]);

                                    $requestXlm = $client->createPresignedRequest($command, $expiry);
                                    $uriXlm = (string)$requestXlm->getUri();

                                    /**dump('$uriXlm');*/
                                    /**dump($uriXlm);*/

                                    /**dump('$downloadXmlLog');*/
                                    /**dump($downloadXmlLog);*/


                                    $dir = storage_path()."/app".$download_temp_folder . "xml";


                                    if (!File::isFile(storage_path().'/app'.$downloadXmlLog."xml")) {
                                        $path = storage_path().'/app'.config('s3br24.manual_download_temp_folder')."xml";
                                        File::makeDirectory($path, 0777, true, true); /** make directory */
                                    }

                                    /**dump('$dir');*/
                                    /**dump($dir);*/

                                    $downXmlCmd = "aria2c --allow-overwrite=true --piece-length=100M --max-connection-per-server=8 --file-allocation=none --max-concurrent-downloads=8 --split=8 --log-level=notice --max-tries=12  --retry-wait=15 --log={$downloadXmlLog}  --dir={$dir} " . '"' . $uriXlm . '"';
                                    /**--console-log-level=<LEVEL>    Set log level to output to console.  LEVEL is either debug, info, notice, warn or error. Default: notice */
                                    /**--download-result=<OPT> Set <OPT> to default, full, hide. Default: default */

                                    /**dump($downXmlCmd);*/

                                    exec($downXmlCmd . " > /dev/null &");


                                    /**dump('====================== downloaded xml file because found "_new.zip" ============================ to ==== '. $dir);*/
                                }


                                if (strpos($zip, "ready.zip") > 0) {
                                    $type = 'ready';
                                    $isNew = false;
                                }

                                /**dd(null);*/

                                $command = $client->getCommand('GetObject', [
                                    'Bucket' => $bucket,
                                    'Key' => $zip
                                ]);

                                $request = $client->createPresignedRequest($command, $expiry);
                                $uri = (string)$request->getUri();

                                $this->taskManualDownloadFile->where('case_id', $caseId)->where('local', basename($zip))->delete();
                                //DB::table('tasks_downloads_files')->where('case_id', $caseId)->where('local', basename($zip))->delete();

                                /** so reguardless whether the xml is already downloaded.. we can grab the information from the xml version on amazon and store it already on the database table. */
                                $other_details = $this->get_customer_number_from_xml($caseId, true);

                                $dataZip = [
                                    'case_id' => $caseId,
                                    'live' => $zip,
                                    'state' => 'new',
                                    'time' => time(),
                                    'url' => $uri,
                                    'local' => basename($zip),
                                    'size' => $s3->size($zip),
                                    'last_modified' => $s3->lastModified($zip),
                                    'type' => $type,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'xml_title_contents' => $other_details['xml_title_contents'],
                                    'xml_jobinfoproduction' => $other_details['xml_jobinfoproduction'],
                                    'xml_deliverytime_contents' => $other_details['xml_deliverytime_contents']
                                ];

                                $this->taskManualDownloadFile->insert($dataZip);
                                //DB::table('tasks_downloads_files')->insert($dataZip);


                                /** but putting in the data of the zip on the next command it will be downloaded */
                                /** we already saved the xml file now lets use the case Id to download the zip files to the appropriate customer ID. */
                            } catch (\Exception $ex) {
                                \App\Facades\CustomLog::error($ex->getMessage(), $downloadJobErrorLog);
                            }
                        }
                    }
                }
            }
        }

        return [
            'success' => true,
            'caseId' => $caseId
        ];
    }


    private function getInfoFromS3($s3, $bucket, $xlmFiles, $xml_tmp_asia, $xml_not_zip_asia, $job_dir, $download_temp_folder)
    {
        die();
        /**$client = $s3->getDriver()->getAdapter()->getClient();*/
        $expiry = "+7 days";
        $client = new S3Client([
            'version' => 'latest',
            'profile' => 'default',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            // by not defining the access id and access key here the aws sdk will try to look for it as an environment variable 
            // 'credentials' => array(
            //     'key' => env('AWS_ACCESS_KEY_ID'),
            //     'secret'  => env('AWS_SECRET_ACCESS_KEY')
            // )
        ]);
        $downloadXmlLog = config('s3br24.manual_download_log') . date('Y_m_d') . '_downloadXmlLog.txt';

        $downloadJobErrorLog = config('s3br24.manual_download_log') . date('Y_m_d') . '_downloadJobErrorLog.txt';
        if (!File::isFile($downloadJobErrorLog)) {
            File::put($downloadJobErrorLog, '');
        }
        Log::useFiles($downloadJobErrorLog, 'error');

        foreach ($xlmFiles as $xlm) {
            /**check exits task*/
            $caseId = basename($xlm, ".xml");
            $task = $this->task->where('case_id', $caseId)->orWhere('jobIdTitle', $caseId)->get()->first();
            $fileName = basename($xlm);

            if ($task) {
                /**move to xml tmp*/
                $xmlTmp = $xml_tmp_asia . date('Y-m') . '/' . $fileName;

                if ($s3->exists($xmlTmp)) {
                    $s3->delete($xmlTmp);
                }

                $s3->move($xlm, $xmlTmp);

                $this->taskManualDownload->where('case_id', $caseId)->update(['state' => 'downloaded']);
            } else {
                $taskD = $this->taskManualDownload->where('case_id', $caseId)->get()->first();
                if ($taskD) {
                    continue;
                }

                $modifyDate = $s3->lastModified($xlm);
                /**move to folder*/
                if (time() - $modifyDate > 24 * 60 * 60) {
                    $fileMoveNotZip = $xml_not_zip_asia . $fileName;
                    if ($s3->exists($fileMoveNotZip)) {
                        $s3->delete($fileMoveNotZip);
                    }

                    $s3->move($xlm, $fileMoveNotZip);
                    continue;
                }

                /**scan zip file*/
                $zipFolder = $job_dir . $caseId . "/zip/";
                $zipFiles = $s3->files($zipFolder);

                if (!empty($zipFiles)) {
                    $isNew = false;
                    $hasNewInDB = false;
                    foreach ($zipFiles as $zip) {
                        $taskFileE = $this->taskManualDownloadFile->where('live', $zip)->get()->first();
                        if ($taskFileE) {
                            if (strpos($zip, "new.zip") > 0) {
                                $hasNewInDB = true;
                            }
                            continue;
                        }

                        if (strpos($zip, "example.zip") > 0 || strpos($zip, "new.zip") > 0) {
                            try {
                                $type = 'example';
                                if (strpos($zip, "new.zip") > 0) {
                                    $type = 'new';
                                    $isNew = true;

                                    $this->taskManualDownload->where('case_id', $caseId)->delete();

                                    /**insert db*/
                                    $data = [
                                        'case_id' => $caseId,
                                        'state' => 'new',
                                        'try' => 0,
                                        'time' => time()
                                    ];

                                    $this->taskManualDownload->insert($data);

                                    /**download xlm file*/
                                    $command = $client->getCommand('GetObject', [
                                        'Bucket' => $bucket,
                                        'Key' => $xlm
                                    ]);

                                    $requestXlm = $client->createPresignedRequest($command, $expiry);
                                    $uriXlm = (string)$requestXlm->getUri();

                                    $dir = $download_temp_folder . "xml";
                                    $downXmlCmd = "aria2c --allow-overwrite=true --piece-length=100M --max-connection-per-server=8 --file-allocation=none --max-concurrent-downloads=8 --split=8 --log-level=notice --max-tries=12  --retry-wait=15 --log={$downloadXmlLog}  --dir={$dir} " . '"' . $uriXlm . '"';
                                    exec($downXmlCmd . " > /dev/null &");
                                }

                                $command = $client->getCommand('GetObject', [
                                    'Bucket' => $bucket,
                                    'Key' => $zip
                                ]);

                                $request = $client->createPresignedRequest($command, $expiry);
                                $uri = (string)$request->getUri();

                                $this->taskManualDownloadFile->where('case_id', $caseId)->where('local', basename($zip))->delete();

                                $dataZip = [
                                    'case_id' => $caseId,
                                    'live' => $zip,
                                    'state' => 'new',
                                    'time' => time(),
                                    'url' => $uri,
                                    'local' => basename($zip),
                                    'size' => $s3->size($zip),
                                    'last_modified' => $s3->lastModified($zip),
                                    'type' => $type
                                ];

                                $this->taskManualDownloadFile->insert($dataZip);
                            } catch (\Exception $ex) {
                                Log::error($ex->getMessage());
                            }
                        } else {
                            /**compare timestamp of xml & zip --> notify if xml time > zip time about 1h*/
                            $zipModifyDate = $s3->lastModified($zip);
                            $subTime = ($zipModifyDate - $modifyDate) / 3600;
                            if ($subTime >= 1) {
                                $logContent = 'XML is OK but Zip is ERROR (xml time > zip time about 1h)';
                                $searchString = "$caseId - $logContent";
                                $r = exec('grep ' . escapeshellarg($searchString) . ' ' . $downloadJobErrorLog);

                                if (!($r && !empty($r))) {
                                    Log::error($searchString);

                                    $message = array(
                                        'title' => $caseId,
                                        'content' => $logContent,
                                        'link' => null,
                                        'to' => config('br24config.notify_user_id')
                                    );
                                    sendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                                }
                            }
                        }
                    }

                    if (!$isNew) {
                        if (time() - $modifyDate > 1 * 60 * 60) { /**if more than 1 hour, it has not new.zip file --> notify*/
                            $logContent = 'XML is OK but new.zip file is ERROR';
                            $searchString = "$caseId - $logContent";
                            $r = exec('grep ' . escapeshellarg($searchString) . ' ' . $downloadJobErrorLog);

                            if (!($r && !empty($r)) && !$hasNewInDB) {
                                Log::error($searchString);

                                $message = array(
                                    'title' => $caseId,
                                    'content' => $logContent,
                                    'link' => null,
                                    'to' => config('br24config.notify_user_id')
                                );
                                sendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                            }
                        }
                    }
                } else {
                    if (time() - $modifyDate > 2 * 60 * 60) { /**if more than 2 hour, it has not zip file --> notify*/
                        $newFiles = $s3->files($job_dir . $caseId . '/new/');
                        if (!empty($newFiles)) {
                            $logContent = 'XML is OK but zip folder is EMPTY';
                            $searchString = "$caseId - $logContent";
                            $r = exec('grep ' . escapeshellarg($searchString) . ' ' . $downloadJobErrorLog);

                            if (!($r && !empty($r))) {
                                Log::error($searchString);

                                $message = array(
                                    'title' => $caseId,
                                    'content' => $logContent,
                                    'link' => null,
                                    'to' => config('br24config.notify_user_id')
                                );
                                sendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                            }
                        }
                    }
                }
            }
        }
    }

    public function upload($s3, $bucket, $file) 
    {
        /**Log*/
        $log = makePathLog($file->id, '_upload', 'uploadjob/' . date('Y-m-d') . '/' . $file->case_id);
        makeLog($log, "File ID ---------->> ". $file->id);

        try {
            /** Create S3 client*/
            /**$client = $s3->getDriver()->getAdapter()->getClient();*/
            $client = new S3Client([
                'version' => 'latest',
                'profile' => 'default',
                'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
                // by not defining the access id and access key here the aws sdk will try to look for it as an environment variable 
                // 'credentials' => array(
                //     'key' => env('AWS_ACCESS_KEY_ID'),
                //     'secret'  => env('AWS_SECRET_ACCESS_KEY')
                // )
            ]);
            /** Check folder*/
            if ($file->folder == '') {
                makeLog($log, 'Folder empty.');
                return;
            }

            /** Check job*/
            $case_id = $file->case_id;
            $arCases = explode("_", $case_id);
            if (isset($arCases[1])) {
                $case_id = $arCases[0];
            }

            makeLog($log, 'case id: ' . $case_id . ', file id: ' . $file->id . ', size:' . number_format($file->size) . 'MB.');

            $filePathOriginal = rollbackOriginalName($file);

            if ($file->jobIdTitle && !empty($file->jobIdTitle)) {
                $case_id = $file->jobIdTitle;
            }

            /** Link folder from S3 to Upload*/
            /**$j_key = "br24/xml/test/" . $case_id . "/" . $file->folder . "/" . $filePathOriginal;*/
            $j_key = "br24/Jobs/" . $case_id . "/" . $file->folder . "/" . $filePathOriginal;
            makeLog($log, 'key: ' . $j_key);

            /** Check file in local*/
            if (file_exists($file->local)) { /**Exist*/
                if ($file->type == 'file' && $file->size == 0) {
                    $uploader = new ObjectUploader($client, $bucket, $j_key, fopen($file->local, 'rb'));
                } else {
                    $uploader = new MultipartUploader($client, $file->local, [
                        'bucket' => $bucket,
                        'key' => $j_key,
                    ]);
                }

                $i = 0;
                $success = false;
                $result = null;
                do {
                    $i++;
                    if ($i > 15) {
                        break;
                    }

                    try {
                        $result = $uploader->upload();

                        if (!empty($result['ObjectURL'])) {
                            makeLog($log, 'obj url:' . $result['ObjectURL']);

                            $this->uploadSuccess($file);
                            $success = true;
                        }
                    } catch (MultipartUploadException $e) {
                        if ($i == 1) {
                            makeLog($log, serialize($e->getMessage()));
                        }

                        new MultipartUploader($client, $file->local, [
                            'state' => $e->getState(),
                        ]);
                    }
                } while (!isset($result));

                /**log when have 5 times error*/
                if (!$success) {
                    makeLog($log, 'Upload unsuccess, file_id : ' . $file->id);
                    $this->uploadFail($file);
                }
            } else { /**File is deleted*/
                $str_deleted = $file->file_path . "\n";
                $this->uploadFileDelete($file, 'deleted', $str_deleted);
                makeLog($log, 'Upload unsuccess, file delete: ' . $file->id);
            }

            makeLog($log, "------------ Finish upload file. ------------");
        } catch (\Exception $e) {
            $this->uploadFail($file);
            Log::error('Caught exception: ' . $e->getMessage());
            makeLog($log, 'Upload fail - Exception.');
            makeLog($log, $e->getMessage());
        }
    }

    /**
     * Upload to S3 - Use queue
     * See more: Jobs/UploadToServerGermany
     *
     * @param $file
     *
     * @author anhlx412@gmail.com
     */
    public function uploadGermany($file)
    {
        $bucket = 'br24storage';
        $s3 = Storage::disk('s3');
        $this->upload($s3, $bucket, $file);
    }

    /**
     * Upload to S3 - Use queue
     * See more: Jobs/UploadToServerAsia
     *
     * @param $file
     *
     * @author anhlx412@gmail.com
     */
    public function uploadAsia($file)
    {
        $bucket = 'br24-asia';
        $s3 = Storage::disk('s3_asia');
        $this->upload($s3, $bucket, $file);
    }

    /**
     * @param $file
     * @param string $state
     *
     * @author anhlx412@gmail.com
     */
    public function uploadFail($file, $state = 'error')
    {
        $this->tasksFiles->updateStateById($file->id, $state);
        $this->task->updateProcess($file->task_id, false, []);
    }

    /**
     * @param $file
     * @param string $state
     * @param int $is_move_final
     *
     * @author anhlx412@gmail.com
     */
    public function uploadSuccess($file, $state = 'uploaded', $is_move_final = 0)
    {
        $this->tasksFiles->updateStateById($file->id, $state, true, $is_move_final);
        $this->task->updateProcess($file->task_id, true, []);
    }

    /**
     * @param $file
     * @param string $state
     * @param string $str_deleted
     */
    public function uploadFileDelete($file, $state = 'deleted', $str_deleted = '')
    {
        $this->tasksFiles->updateStateById($file->id, $state, false);
        $this->task->updateProcess($file->task_id, true, ['str_deleted' => $str_deleted]);
    }


    /**
     * trigger S3 bucket XML Scan via AWS SDK PHP
     *
     * @param $caseID
     * @return array
     * @author sigmoswitch
     */
    public function trigger_s3_xml_scanV2($caseID = null, $actually_download = false, $mdl_checkbox_example = null, $mdl_checkbox_new = null, $mdl_checkbox_ready = null)
    {
        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $caseID => ' . json_encode($caseID));
        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $actually_download => ' . json_encode($actually_download));
        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $mdl_checkbox_example => '.json_encode($mdl_checkbox_example));
        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $mdl_checkbox_new => '.json_encode($mdl_checkbox_new));
        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $mdl_checkbox_ready => '.json_encode($mdl_checkbox_ready));

        if($actually_download){
            if($mdl_checkbox_example == null || $mdl_checkbox_new == null || $mdl_checkbox_ready == null){
                /** this combination should not be possible it is a mistake */
                return [
                    'success' => false,
                    'errors' => 'choose at least 1 folder'
                ];
            }
        }

        $last_updated_by = $this->appuserRepo->getAuthUserDetails()->user_id;

        $s3 = Storage::disk('s3');
        /**$client = $s3->getDriver()->getAdapter()->getClient();*/
        $expiry = "+7 days";

        /**dump(env('AWS_ACCESS_KEY_ID'));*/
        /**dd(env('AWS_SECRET_ACCESS_KEY'));*/

        $client = new S3Client([
            'version' => 'latest',
            'profile' => 'default',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            // by not defining the access id and access key here the aws sdk will try to look for it as an environment variable 
            // 'credentials' => array(
            //     'key' => env('AWS_ACCESS_KEY_ID'),
            //     'secret'  => env('AWS_SECRET_ACCESS_KEY')
            // )
        ]);
        /**dd($s3);*/

        $s3Br24Config = config('s3br24');
        $bucket = config('filesystems.disks.s3.bucket');

        /** search directly for the job first and every attempt, since it also depends that the folder is there when an xml is found, acts like another check */
        $jobfolder_results = $client->getPaginator('ListObjectsV2', [
            'Bucket' => $bucket,
            'Prefix' => 'br24/Jobs/'.$caseID,
        ]);
        $jobfolder_contents = [];
        foreach ($jobfolder_results->search("Contents[]") as $key => $item_details) {
            /**dump($item_details);*/
            $jobfolder_contents[$key] = $item_details['Key'];
        }
        /**dd($jobfolder_contents);*/
        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() empty($jobfolder_contents) => '.json_encode(empty($jobfolder_contents)));

        if(empty($jobfolder_contents)){
            return [
                'success' => false,
                'errors' => 'JobFolder Cannot Be Found'
            ];
        }else{
            /** start scanning for any xml file */
            $results = $client->getPaginator('ListObjectsV2', [
                'Bucket' => $bucket,
                'Prefix' => $s3Br24Config['xml_dir'],
            ]);

            $scanned_xml = [];
            foreach ($results->search("Contents[?contains(Key, '".$caseID.".xml')]") as $key => $item_details) {
                $scanned_xml[$caseID.'_'.$key] = $item_details['Key'];
            }
            
            /**dump($scanned_xml);*/
            Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $scanned_xml => '.json_encode($scanned_xml));
            //testing other route
            //$scanned_xml = [];

            if(!empty($scanned_xml)){
                /** xml found */

                /** grab contents of xml.txt */
                $command = $client->getCommand('GetObject', [
                    'Bucket' => $bucket,
                    'Key' => $scanned_xml[array_key_last($scanned_xml)]
                ]);

                $request = $client->createPresignedRequest($command, $expiry);
                $uri = (string)$request->getUri();

                /** use the info from the text file to construct the xml */
                /** you still need to find out if you could do away with just putting the values straight into the db.. */
                /** NO! it is not going to run like the automatic downloads.. it references the xml in the xml folder to do some functions.. ... and you cannot be bothered to have to bypass it */
                $file_as_array = preg_split('/\n|\r\n?/', file_get_contents($uri));

                /**dd($file_as_array);*/
                Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $file_as_array => '.json_encode($file_as_array));

                if($actually_download){
                    /** if we arre actually downloading use the */
                    $xml_tool_client = '';
                    $xml_title_contents = '';
                    $xml_jobidtitle_contents = '';
                    $xml_deliverytime_contents = '';
                    $xml_jobInfo_contents = '';
                    $anchor_for_xml_jobInfo = false;
                    $xml_jobInfoProduction_contents = '';
                    $anchor_for_xml_jobInfoProduction = false;

                    foreach($file_as_array as $generic_index => $line){
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

                    /**insert db for the scheduler to handle */
                    try {
                        DB::beginTransaction();
                        $data = [
                            'case_id' => $caseID,
                            'state' => 'new',
                            'try' => 0,
                            'time' => time(),
                            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                            'created_xml' => '2',
                            'last_updated_by' => $last_updated_by
                        ];

                        $this->taskManualDownload->insert($data);

                        $downloadJobErrorLog = config('s3br24.manual_download_log') . date('Y_m_d') . '_downloadJobErrorLog.log';
                        $modifyDate = time();
                        /** need the uri for all the zips minus the ready zip .. just example and new if any.. */
                        /**scan zip file*/
                        $zipFolder = $s3Br24Config['job_dir'].$caseID."/zip/";
                        $zipFiles = $s3->files($zipFolder);
                        /**dump($zipFiles);*/

                        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $zipFiles => '.json_encode($zipFiles));


                        if (!empty($zipFiles)) {
                            $isNew = false;
                            $hasNewInDB = false;


                            foreach ($zipFiles as $zip) {
                                /**dump($zip);*/
                                Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $zip => '.json_encode($zip));
                                /** every zip file found with the same caseID */

                                $taskFileE = $this->taskManualDownloadFile->where('live', $zip)->get()->first();
                                //$taskFileE = DB::table('tasks_downloads_files')->where('live', $zip)->get()->first();
                                /**dump($taskFileE);*/

                                if ($taskFileE) {
                                    dump('===================================== stopping due to xml file already on the db ==============================');
                                    /**dd($taskFileE);*/

                                    if (strpos($zip, "new.zip") > 0) {
                                        $hasNewInDB = true;
                                    }
                                    continue;
                                }

                                /** we get every zip as long as its example new and ready */
                                if ((strpos($zip, "example.zip") !== false && $mdl_checkbox_example == 'true') || (strpos($zip, "new.zip") !== false && $mdl_checkbox_new == 'true') || (strpos($zip, "ready.zip") !== false && $mdl_checkbox_ready == 'true')) {

                                    try {
                                        $type = 'example';

                                        if (strpos($zip, "new.zip") !== false) {
                                            $type = 'new';
                                            $isNew = true;
                                        }

                                        if (strpos($zip, "ready.zip") !== false) {
                                            $type = 'ready';
                                            $isNew = false;
                                        }

                                        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $type => '.json_encode($type));


                                        $command = $client->getCommand('GetObject', [
                                            'Bucket' => $bucket,
                                            'Key' => $zip
                                        ]);

                                        $request = $client->createPresignedRequest($command, $expiry);
                                        $uri = (string)$request->getUri();
                                        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $uri => '.json_encode($uri));

                                        $size = $s3->size($zip);
                                        $lastModified = $s3->lastModified($zip);
                                        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $size => '.json_encode($size));
                                        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $lastModified => '.json_encode($lastModified));

                                        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $xml_tool_client => '.json_encode($xml_tool_client));
                                        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $xml_title_contents => '.json_encode($xml_title_contents));
                                        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $xml_jobInfoProduction_contents => '.json_encode($xml_jobInfoProduction_contents));
                                        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $xml_deliverytime_contents => '.json_encode($xml_deliverytime_contents));



                                        $this->taskManualDownloadFile->where('case_id', $caseID)->where('local', basename($zip))->delete();

                                        $dataZip = [
                                            'case_id' => $caseID,
                                            'live' => $zip,
                                            'state' => 'new',
                                            'time' => time(),
                                            'url' => $uri,
                                            'local' => basename($zip),
                                            'size' => $size,
                                            'last_modified' => $lastModified,
                                            'type' => $type,
                                            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                            'xml_tool_client' => $xml_tool_client,
                                            'xml_title_contents' => $xml_title_contents,
                                            'xml_jobinfoproduction' => $xml_jobInfoProduction_contents,
                                            'xml_deliverytime_contents' => $xml_deliverytime_contents
                                        ];

                                        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $dataZip => '.json_encode($dataZip));

                                        $this->taskManualDownloadFile->insert($dataZip);
                                        /** but putting in the data of the zip on the next command it will be downloaded */
                                        /** we already saved the xml file now lets use the case Id to download the zip files to the appropriate customer ID. */
                                        \App\Jobs\ManualDL_download::dispatch($caseID, $type)->delay(now()->addSeconds(DB::table('queue_delay_seconds_manualdl')->first()->queue_delay_seconds));
                                    } catch (\Exception $ex) {
                                        \App\Facades\CustomLog::error($ex->getMessage(), $downloadJobErrorLog);
                                    }
                                }
                            }
                        }
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Debugbar::addException($e);
                        return [
                            'success' => false,
                            'error_from_catch' => 'error_from_catch',
                            'errors' => $e,
                            'caseID' => $caseID,
                        ];
                    }
                }

                /** we will use the information from the xml then. */
                /** and store to the db immediately */
                
                return [
                    'success' => true,
                    'caseID' => $caseID,
                    'scan_xml' => 'found_xml'
                ];
            }else{
                /** xml not found */
                /** use the jobfolder contents to try and enter the details staight into the database to downloading without an xml? */
                $found_info_text_file = false;
                foreach($jobfolder_contents as $generic_key => $jobfolder_content_details){
                    if($jobfolder_content_details == 'br24/Jobs/'.$caseID.'/new/info.txt'){
                        $found_info_text_file = true;
                        break;
                    }
                }
                
                Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $found_info_text_file => '.json_encode($found_info_text_file));

                if($found_info_text_file){
                    /** grab contents of info.txt */
                    $command = $client->getCommand('GetObject', [
                        'Bucket' => $bucket,
                        'Key' => 'br24/Jobs/'.$caseID.'/new/info.txt'
                    ]);

                    $request = $client->createPresignedRequest($command, $expiry);
                    $uri = (string)$request->getUri();

                    /** use the info from the text file to construct the xml */
                    /** you still need to find out if you could do away with just putting the values straight into the db.. */
                    /** NO! it is not going to run like the automatic downloads.. it references the xml in the xml folder to do some functions.. ... and you cannot be bothered to have to bypass it */
                    $file_as_array = preg_split('/\n|\r\n?/', file_get_contents($uri));
                }else{
                    /** we have found the job folder without xml but there is not info.txt to grab information from.. how to do then ? */
                    /** we still create the xml but with bogus information.. or example text.. the xml just needs to have the caseID and be present. and a default deliver date timestamp.. could be current timestamp.. doesn't matter */
                    $file_as_array = [];
                }
                /**dump($file_as_array);*/

                if($actually_download){
                    /** if we arre actually downloading use the */
                    /** set defaults incase info.txt file does not exists. still need the xml to be generated so the rest of the code works like the auto downloader*/
                    $customer_number = '';
                    $order_name = '';
                    $additional_information_for_production = '';
                    $number_of_images = '';
                    $delivery_datetime_db_formatted = Carbon::now()->timestamp;
                    $order_datetime_db_formatted = Carbon::now()->timestamp;

                    /**remember when doing this section of the text file */
                    $staring_to_do_additional_information_for_production = false;

                    foreach($file_as_array as $generic_index => $line){
                        if($generic_index == 0){
                            $customer_number = $line;
                        }
                        if (strpos($line, 'Order name: ') !== false) {
                            $order_name .= preg_replace('/\s\s+/', ' ', str_replace("Order name: ", "", $line));
                        }
                        if (strpos($line, 'Additional information for production:') !== false) {
                            $staring_to_do_additional_information_for_production = true;
                        }
                        if (strpos($line, 'Images: ') !== false) {
                            $staring_to_do_additional_information_for_production = false;
                            $number_of_images = str_replace("Images: ", "", $line);
                        }

                        if($staring_to_do_additional_information_for_production){
                            $additional_information_for_production .= $line.'<br>';
                        }

                        /** as long as they don't change their date formats for the xml then this will work.*/
                        if (strpos($line, 'Delivery time : ') !== false) {
                            $delivery_date_reformat = Carbon::createFromFormat('d.m.Y H:i', str_replace("Delivery time : ", "", $line));
                            $delivery_datetime_db_formatted = Carbon::parse($delivery_date_reformat)->timestamp;
                        }
                        if (strpos($line, 'Date of order: ') !== false) {
                            $order_date_reformat = Carbon::createFromFormat('d.m.Y H:i', str_replace("Date of order: ", "", $line));
                            $order_datetime_db_formatted = Carbon::parse($order_date_reformat)->timestamp;
                        }            
                    }

                    //dump($customer_number);
                    //dump($order_name); //xml_title_contents
                    //dump($additional_information_for_production); //xml_jobinfoproduction
                    //dump($number_of_images); //
                    //dump($delivery_datetime_db_formatted); //xml_deliverytime_contents
                    //dump($order_datetime_db_formatted); //NONE
                    /**dd($uri);*/


                    /**create_the_xml using the manual_dl_job_xml_template. is better for the download upload tool and your sanity */
                    $path = storage_path('app/templates/job_xml/manual_dl_job_xml_template.xml');
                    $thisnow = file_get_contents($path);

                    $thisnow = str_replace("<customerId></customerId>", "<customerId>".$customer_number."</customerId>", $thisnow);
                    $thisnow = str_replace("<jobId></jobId>", "<jobId>".$caseID."</jobId>", $thisnow);
                    $thisnow = str_replace("<jobTitle><![CDATA[]]></jobTitle>", "<jobTitle><![CDATA[".$order_name."]]></jobTitle>", $thisnow);
                    $thisnow = str_replace("<deliveryProduction><![CDATA[]]></deliveryProduction>", "<deliveryProduction><![CDATA[".$delivery_datetime_db_formatted."]]></deliveryProduction>", $thisnow);
                    $thisnow = str_replace("<jobInfoProduction><![CDATA[]]></jobInfoProduction>", "<jobInfoProduction><![CDATA[".$additional_information_for_production."]]></jobInfoProduction>", $thisnow);

                    Storage::put($s3Br24Config['manual_download_temp_folder'].'xml/'.$caseID.'.xml', $thisnow); /**store the modified template file where it needs to be */
                    /** once the file has be saved enter some details into the db to be taken care of by the scheduler */
                    /**dd($thisnow);*/

                    /**insert db for the scheduler to handle */
                    try {
                        DB::beginTransaction();
                        $data = [
                            'case_id' => $caseID,
                            'state' => 'new',
                            'try' => 0,
                            'time' => time(),
                            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                            'created_xml' => '1',
                            'last_updated_by' => $last_updated_by
                        ];

                        $this->taskManualDownload->insert($data);

                        $downloadJobErrorLog = config('s3br24.manual_download_log') . date('Y_m_d') . '_downloadJobErrorLog.log';
                        $modifyDate = time();
                        /** need the uri for all the zips minus the ready zip .. just example and new if any.. */
                        /**scan zip file*/
                        $zipFolder = $s3Br24Config['job_dir'].$caseID."/zip/";
                        $zipFiles = $s3->files($zipFolder);
                        /**dump($zipFiles);*/

                        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $zipFiles => '.json_encode($zipFiles));


                        if (!empty($zipFiles)) {
                            $isNew = false;
                            $hasNewInDB = false;


                            foreach ($zipFiles as $zip) {
                                /**dump($zip);*/
                                Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $zip => '.json_encode($zip));
                                /** every zip file found with the same caseID */

                                $taskFileE = $this->taskManualDownloadFile->where('live', $zip)->get()->first();
                                //$taskFileE = DB::table('tasks_downloads_files')->where('live', $zip)->get()->first();
                                /**dump($taskFileE);*/

                                if ($taskFileE) {
                                    dump('===================================== stopping due to xml file already on the db ==============================');
                                    /**dd($taskFileE);*/

                                    if (strpos($zip, "new.zip") > 0) {
                                        $hasNewInDB = true;
                                    }
                                    continue;
                                }

                                /** we get every zip as long as its example new and ready */
                                if ((strpos($zip, "example.zip") !== false && $mdl_checkbox_example == 'true') || (strpos($zip, "new.zip") !== false && $mdl_checkbox_new == 'true') || (strpos($zip, "ready.zip") !== false && $mdl_checkbox_ready == 'true')) {

                                    try {
                                        $type = 'example';

                                        if (strpos($zip, "new.zip") !== false) {
                                            $type = 'new';
                                            $isNew = true;
                                        }

                                        if (strpos($zip, "ready.zip") !== false) {
                                            $type = 'ready';
                                            $isNew = false;
                                        }

                                        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $type => '.json_encode($type));


                                        $command = $client->getCommand('GetObject', [
                                            'Bucket' => $bucket,
                                            'Key' => $zip
                                        ]);

                                        $request = $client->createPresignedRequest($command, $expiry);
                                        $uri = (string)$request->getUri();

                                        $this->taskManualDownloadFile->where('case_id', $caseID)->where('local', basename($zip))->delete();

                                        $dataZip = [
                                            'case_id' => $caseID,
                                            'live' => $zip,
                                            'state' => 'new',
                                            'time' => time(),
                                            'url' => $uri,
                                            'local' => basename($zip),
                                            'size' => $s3->size($zip),
                                            'last_modified' => $s3->lastModified($zip),
                                            'type' => $type,
                                            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                            'xml_title_contents' => $order_name,
                                            'xml_jobinfoproduction' => $additional_information_for_production,
                                            'xml_deliverytime_contents' => $delivery_datetime_db_formatted
                                        ];

                                        Loggy::write('manual_download_freshdownload', 'trigger_s3_xml_scanV2() $dataZip => '.json_encode($dataZip));

                                        $this->taskManualDownloadFile->insert($dataZip);
                                        /** but putting in the data of the zip on the next command it will be downloaded */
                                        /** we already saved the xml file now lets use the case Id to download the zip files to the appropriate customer ID. */
                                        \App\Jobs\ManualDL_download::dispatch($caseID, $type)->delay(now()->addSeconds(DB::table('queue_delay_seconds_manualdl')->first()->queue_delay_seconds));
                                    } catch (\Exception $ex) {
                                        \App\Facades\CustomLog::error($ex->getMessage(), $downloadJobErrorLog);
                                    }
                                }
                            }
                        }
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Debugbar::addException($e);
                        return [
                            'success' => false,
                            'error_from_catch' => 'error_from_catch',
                            'errors' => $e,
                            'caseID' => $caseID,
                        ];
                    }
                }

                return [
                    'success' => true,
                    'caseID' => $caseID,
                    'scan_xml' => 'not_found_xml',
                    'found_info_text_file' => empty($file_as_array)
                ];
            }
        }
    }

    /**
     * trigger S3 bucket XML Scan via CLI
     *
     * @param $caseID
     * @return array
     * @author sigmoswitch
     */
    public function CLI_trigger_s3_xml_scan($caseID = null)
    {
        dd(null);
        $s3Br24Config = config('s3br24');
        $bucket = config('filesystems.disks.s3.bucket');

        $s3_manual_download_scan_xml_log_dir = storage_path()."/logs".config('s3br24.manual_download_log') . 's3_manual_download_scan_xml_log';
        exec("mkdir -p $s3_manual_download_scan_xml_log_dir");
        /** dd(null); */
        $s3_manual_download_scan_xml_log = $s3_manual_download_scan_xml_log_dir . '/' . $caseID . '_s3_manual_download_scan_xml.log';

        /** if log file exists overwrite with the new contents of the command */
        $cmd = 'aws s3 ls --profile default s3://'.$bucket.'/'.$s3Br24Config['xml_dir'].' --recursive --human-readable | grep '.$caseID.'.xml';
        /**dump($cmd);*/

        $scan_shell_script = $s3_manual_download_scan_xml_log_dir.'/'.$caseID.'_scan.sh';

        if(File::exists($scan_shell_script)){

            /** need to check if the thing is running still */
            /** if it is still running.. we should probably not try to do it again. */

            $check_if_a_previous_scan_is_running = $this->check_s3_xml_scan($caseID);

            if($check_if_a_previous_scan_is_running == 1){
                /** it is not running for this caseID so we should allow to clear the contents of the shell script for the new attempt */
                try {
                    File::delete($scan_shell_script);
                    exec('rm -R '.$scan_shell_script);
                } catch (FileNotFoundException $e) {
                    dd($e);
                }
            }else{
                /** we could give more details perhaps to the front end */
                return false;
            }
        }

        File::put($scan_shell_script, '#!/bin/bash'.PHP_EOL.'whoami'.PHP_EOL.$cmd);
        //exec('chmod +x '.$scan_shell_script);

        /** trigger the shell script and log the results to the log file in the background */
        $shell_execution_cmd = $scan_shell_script .' 2>&1 | tee '.$s3_manual_download_scan_xml_log.' 2>/dev/null >/dev/null &';
        /**dump($shell_execution_cmd);*/
        exec($shell_execution_cmd, $shell_exec_output, $shell_exec_return);
        /**dump('started running script');*/
        /**if there are any errors return false*/
        dump($shell_exec_output);
        dd($shell_exec_return);

        return true;
    }

    /**
     * Check S3 bucket XML Scan Progress if completed
     *
     * @param $caseID
     * @return array
     * @author sigmoswitch
     */
    public function CLI_check_s3_xml_scan($caseID = null)
    {
        dd(null);
        dump('check_s3_xml_scan');

        $s3Br24Config = config('s3br24');
        $bucket = config('filesystems.disks.s3.bucket');

        $cmd = 'aws s3 ls --profile default s3://'.$bucket.'/'.$s3Br24Config['xml_dir'].' --recursive --human-readable | grep '.$caseID.'.xml';
        $check_scan_xml_done = 'ps aux | grep "'.$cmd.'" | wc -l';
        dump($check_scan_xml_done);

        exec($check_scan_xml_done, $exec_output, $exec_return);
        dump(json_encode($exec_output));
        dd('$exec_return = '. $exec_return);
        /**dump('$value_returned == '. $value_returned);*/

        if($exec_return != 0){
            return false;
        }else{
            if($exec_output > 2){
                return false;
            }else{

                return true;
            }
        }
    }

    /**
     * Scan S3 bucket for evidence of xml for caseID
     *
     * @param $caseID
     * @return array
     * @author sigmoswitch
     */
    public function CLI_scan_finished_perform_the_next_step($bucket = null, $prefix = null, $caseID = null)
    {
        dd(null);
        $array = explode("\n", file_get_contents('file.txt'));
        //        $todays_date = Carbon::now()->format('Y-m-d');
        //
        //        $array_of_items_on_s3 = [];
        //        $count_of_files_uplaod_to_s3_today = 0;
        //        $fropen = fopen($s3_manual_download_scan_xml_log, 'r' );
        //        if ($fropen) {
        //            while (($line = fgets($fropen)) !== false) {
        //                if (strpos($line, $todays_date) !== false) {
        //
        //                    /**dump($line);*/
        //                    $file_from_root = explode($alternate_method_s3path, $line)[1];
        //                    /**dump($file_from_root);*/
        //
        //                    if(str_replace(" ", "", $file_from_root) != ""){
        //                        $count_of_files_uplaod_to_s3_today++;
        //                        $array_of_items_on_s3[$count_of_files_uplaod_to_s3_today] = $file_from_root;
        //                    }
        //                }
        //            }
        //            fclose($fropen);
        //        } else {
        //            /** error opening the log file. maybe still writing to */
        //            dump('error opening s3_manual_download_scan_xml_log file');
        //            return;
        //        }
    }

    /**
     * get customer number from xml
     *
     * @param $caseID
     * @return array
     * @author sigmoswitch
     */
    public function get_customer_number_from_xml($caseID = null, $get_other_details_from_xml = false)
    {
        /** specific roof tile customer logic for checking example folder contained within new zip*/
        $s3 = Storage::disk('s3');
        /**$client = $s3->getDriver()->getAdapter()->getClient();*/
        $expiry = "+7 days";
        $client = new S3Client([
            'version' => 'latest',
            'profile' => 'default',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            // by not defining the access id and access key here the aws sdk will try to look for it as an environment variable 
            // 'credentials' => array(
            //     'key' => env('AWS_ACCESS_KEY_ID'),
            //     'secret'  => env('AWS_SECRET_ACCESS_KEY')
            // )
        ]);

        $s3Br24Config = config('s3br24');
        $bucket = config('filesystems.disks.s3.bucket');

        /** by this point the xml should have been downloaded or created hopefully */
        $xml_dir = storage_path()."/app".$s3Br24Config['manual_download_temp_folder']."xml";
        if (File::isFile($xml_dir.'/'.$caseID.'.xml')) {
            $file_as_array = array_filter(preg_split('/\n|\r\n?/', file_get_contents($xml_dir.'/'.$caseID.'.xml')));
        }else{
            $s3 = Storage::disk('s3');
            /**$client = $s3->getDriver()->getAdapter()->getClient();*/
            $expiry = "+7 days";
            $client = new S3Client([
                'version' => 'latest',
                'profile' => 'default',
                'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
                // by not defining the access id and access key here the aws sdk will try to look for it as an environment variable 
                // 'credentials' => array(
                //     'key' => env('AWS_ACCESS_KEY_ID'),
                //     'secret'  => env('AWS_SECRET_ACCESS_KEY')
                // )
            ]);

            $s3Br24Config = config('s3br24');
            $bucket = config('filesystems.disks.s3.bucket');

            $results = $client->getPaginator('ListObjectsV2', [
                'Bucket' => $bucket,
                'Prefix' => $s3Br24Config['xml_dir'],
            ]);

            $scanned_xml = [];
            foreach ($results->search("Contents[?contains(Key, '".$caseID.".xml')]") as $key => $item_details) {
                $scanned_xml[$caseID.'_'.$key] = $item_details['Key'];
            }
            /**dump($scanned_xml);*/

            if(!empty($scanned_xml)){
                /** xml found */
                // $xlmFiles = [
                //     0 => $scanned_xml[array_key_last($scanned_xml)]
                // ];
                // dump($xlmFiles);

                /** how do we get the company number from the xml */
                /** grab contents of info.txt */
                $command = $client->getCommand('GetObject', [
                    'Bucket' => $bucket,
                    'Key' => $scanned_xml[array_key_last($scanned_xml)]
                ]);

                $request = $client->createPresignedRequest($command, $expiry);
                $uri = (string)$request->getUri();

                $file_as_array = preg_split('/\n|\r\n?/', file_get_contents($uri));
            }else{
                $file_as_array = [];
            }
        }
        //dump($file_as_array);
        $customer_number = null;

        $xml_tool_client = '';
        $xml_title_contents = '';
        $xml_jobidtitle_contents = '';
        $xml_deliverytime_contents = '';
        $xml_jobInfo_contents = '';
        $anchor_for_xml_jobInfo = false;
        $xml_jobInfoProduction_contents = '';
        $anchor_for_xml_jobInfoProduction = false;

        foreach($file_as_array as $generic_index => $line){
            if (strpos($line, '<customerId>') !== false && strpos($line, '</customerId>') !== false) {
                $customer_number = str_replace("<customerId>", "", str_replace("</customerId>", "", str_replace(" ", "", $line)));

                if(!$get_other_details_from_xml){
                    break;
                }
            }

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

        if($get_other_details_from_xml){
            return [
                'xml_tool_client' => $xml_tool_client,
                'xml_title_contents' => $xml_title_contents,
                'xml_jobidtitle_contents' => $xml_jobidtitle_contents,
                'xml_deliverytime_contents' => $xml_deliverytime_contents,
                'xml_jobInfo_contents' => $xml_jobInfo_contents,
                'xml_jobInfoProduction_contents' => $xml_jobInfoProduction_contents
            ];
        }

        /**dd('$customer_number = ' .$customer_number);*/
        return $customer_number;
    }

    /**
     * Check S3 bucket XML Scan Progress if completed
     *
     * @param $caseID
     * @return array
     * @author sigmoswitch
     */
    public function initiate_special_example_zip_not_present_function($caseID = null)
    {
        /** specific roof tile customer logic for checking example folder contained within new zip*/
        $s3 = Storage::disk('s3');
        /**$client = $s3->getDriver()->getAdapter()->getClient();*/
        $expiry = "+7 days";

        $client = new S3Client([
            'version' => 'latest',
            'profile' => 'default',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            // by not defining the access id and access key here the aws sdk will try to look for it as an environment variable 
            // 'credentials' => array(
            //     'key' => env('AWS_ACCESS_KEY_ID'),
            //     'secret'  => env('AWS_SECRET_ACCESS_KEY')
            // )
        ]);

        $s3Br24Config = config('s3br24');
        $bucket = config('filesystems.disks.s3.bucket');
        
        $jobexamplefolder_results = $client->getPaginator('ListObjectsV2', [
            'Bucket' => $bucket,
            'Prefix' => 'br24/Jobs/'.$caseID.'/example',
        ]);
        $jobexamplefolder_contents = [];
        foreach ($jobexamplefolder_results->search("Contents[]") as $key => $item_details) {
            $jobexamplefolder_contents[$key] = $item_details['Key'];
        }
        /**dump($jobexamplefolder_contents);*/

        /** you already know how many files should be in the examples folder. and their details including the folder structure.. */
        /** now just download the new zip and compare its folder structure.. to see if the example folder truly contrains a copy of the example folder */
        $jobzipfolder_results = $client->getPaginator('ListObjectsV2', [
            'Bucket' => $bucket,
            'Prefix' => 'br24/Jobs/'.$caseID.'/zip',
        ]);
        $jobzipfolder_contents = [];
        foreach ($jobzipfolder_results->search("Contents[?contains(Key, 'new.zip')]") as $key => $item_details) {
            $jobzipfolder_contents[$key] = $item_details['Key'];
        }
        /**dump($jobzipfolder_contents);*/
        /** we have the key.. so download it and put somewhere to process */

        $newzip_name = explode('/', $jobzipfolder_contents[array_key_last($jobzipfolder_contents)]);
        /**dd($newzip_name);*/
        $log = storage_path()."/logs".config('s3br24.manual_download_log') . date('Y_m_d') . '_downloadLog_check_newzip_contains_example.log';
        $dir = storage_path()."/app".config('s3br24.manual_download_temp_folder') . 'job_check_newzip_contains_example/'.$caseID;
        $zipdir  = $dir.'/zip';

        if (!File::isFile($log)) {
            $path = storage_path().'/logs'.config('s3br24.manual_download_log');
            File::makeDirectory($path, 0777, true, true); /** make directory */
            File::put($log, '');
        }
        if (!File::isFile($zipdir)) {
            $path = storage_path().'/app'.config('s3br24.manual_download_temp_folder')."job_check_newzip_contains_example/".$caseID."/zip";
            File::makeDirectory($path, 0777, true, true); /** make directory */
        }


        if (!File::isFile($zipdir.'/'.$newzip_name[array_key_last($newzip_name)])){
            $command = $client->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key' => $jobzipfolder_contents[array_key_last($jobzipfolder_contents)]
            ]);

            $request = $client->createPresignedRequest($command, $expiry);
            $uri = (string)$request->getUri();

            $cmd="aria2c --allow-overwrite=true --piece-length=100M --max-connection-per-server=8 --file-allocation=none --max-concurrent-downloads=8 --split=8 --log-level=notice --max-tries=12  --retry-wait=15 --log={$log}  --dir={$zipdir} " . '"' . $uri . '"';
            /**--console-log-level=<LEVEL>    Set log level to output to console.  LEVEL is either debug, info, notice, warn or error. Default: notice */
            /**--download-result=<OPT> Set <OPT> to default, full, hide. Default: default */
            $pid = exec($cmd . " > /dev/null & echo $!;", $output);

            /**dump($pid);*/
            sleep(10);
            /** if we are lucky its small enough to be handled straight away */
        }

        $backup_of_jobexamplefolder_contents = $jobexamplefolder_contents;

        /** can we check if the file exists after 10 seconds? */
        /**dump('check if zip is there');*/
        /**dump(File::isFile($zipdir.'/'.$newzip_name[array_key_last($newzip_name)]));*/

        if (File::isFile($zipdir.'/'.$newzip_name[array_key_last($newzip_name)])){

            /** can we try to open it? using the zipper explorer */
            $zipArchive = new \ZipArchive();
            $tryOpeningZip = $zipArchive->open($zipdir.'/'.$newzip_name[array_key_last($newzip_name)]);
            if ($tryOpeningZip == TRUE) {
                /** now see if the zip contains the example folder */
                /** by process of elimination */
                for( $i = 0; $i < $zipArchive->numFiles; $i++ ){ 
                    $stat = $zipArchive->statIndex( $i ); 
                    /**dump($stat['name']);*/
                    foreach($jobexamplefolder_contents as $generic_this_key => $details_of_example_file){
                        if($details_of_example_file == 'br24/Jobs/'.$caseID.'/'.$stat['name']){
                            unset($jobexamplefolder_contents[$generic_this_key]);
                            break;
                        }
                    }
                }

                /**dump('afterwards');*/
                /**dump($jobexamplefolder_contents);*/

                /** which ever which case.. it needs to uplaod the example zip*/
                $this->download_example_folder_contents_next_zip_and_next_upload_zip_to_job_s3_zip_key($caseID, $dir, $backup_of_jobexamplefolder_contents, $client, $expiry, $bucket, $newzip_name, $zipdir);

                //if(empty($jobexamplefolder_contents)){
                //    dump('the example folder is part of the new zip completely');
                //    /** check beforehand if anything has been previously downloaded to the specified download to location */
                //    /** and check if everything has been downloaded locally */
                //    $this->download_example_folder_contents_next_zip_and_next_upload_zip_to_job_s3_zip_key($caseId, $dir);
                //    dd(null);
                //}else{
                //    /** you can see how many percent of the example folder is in the new zip.. if it is 0% we can create the zip for the example folder and upload to s3.. which would fix the auto dl issue */
                //    /** if it is partial included in the new zip. how can that be? we have to error out and alert. or just create the example zip.. who cares. */
                //    if(count($jobexamplefolder_contents) == count($backup_of_jobexamplefolder_contents)){
                //        /** example 100% not part of the new zip.*/
                //        dump('example 100% not part of the new zip.');
                //    }elseif (count($jobexamplefolder_contents) < count($backup_of_jobexamplefolder_contents) && count($jobexamplefolder_contents) <= 0){
                //        dump('example is partially part of the new zip');
                //    }
                //}
            }else{
                /**dump('could not open zip');*/
                /***/
            }
        }else{
            /**dump('file does not exist');*/
        }
    }    

    /**
     * download example folder contents next zip and next upload zip to job s3 zip key
     *
     * @param $caseID
     * @return array
     * @author sigmoswitch
     */
    public function download_example_folder_contents_next_zip_and_next_upload_zip_to_job_s3_zip_key($caseID = null, $dir = null, $backup_of_jobexamplefolder_contents = null, $client = null, $expiry = null, $bucket = null, $newzip_name = null, $zipdir = null)
    {

        $bucket_prefix = 'br24/Jobs/'.$caseID.'/example';
        $download_to_location = $dir.'/example';
        /**dump($bucket_prefix);*/
        /**dump($download_to_location);*/

        /**dump('File::exists($download_to_location)');*/
        /**dump(File::exists($download_to_location));*/

        if(File::exists($download_to_location)) {
            exec('tree -f '.$download_to_location, $output);
            /**dump($output);*/

            foreach($output as $generic_this_key => $local_file_details){
                $stricktly_path_name = explode(" ", $local_file_details);
                /**dd($stricktly_path_name);*/
                $restructured = str_replace($dir, "br24/Jobs/11465865", $stricktly_path_name[array_key_last($stricktly_path_name)]);

                $check_currently_doing_folder = explode("/", $restructured);

                if($check_currently_doing_folder[array_key_last($check_currently_doing_folder)] == 'example'){
                    $restructured .= "/";
                }
                /**dump($restructured);*/

                foreach($backup_of_jobexamplefolder_contents as $generic_index_key => $checking_against){
                    if($checking_against == $restructured){
                        unset($backup_of_jobexamplefolder_contents[$generic_index_key]);
                        break;
                    }
                }
            }
        }
        /**dump('after local and s3 comparison');*/
        /**dump($backup_of_jobexamplefolder_contents);*/

        if(!empty($backup_of_jobexamplefolder_contents)){
            $command = $client->downloadBucket($download_to_location, $bucket, $bucket_prefix);
            /**dump($command);*/
            sleep(10);
            /** if we are lucky it is small enough to be handled straight away */
        }



        /** create the zip with the contents */
        $example_zip_filename_to_save_as = str_replace('new', 'example', $newzip_name[array_key_last($newzip_name)]);
        $cmd3 = "(cd ".$download_to_location."; zip -FSr ".$zipdir."/".$example_zip_filename_to_save_as." ./*)";
        /**dump($cmd3);*/
        exec($cmd3, $zip_output);

        /**dump('$zip_output');*/
        /**dump($zip_output);*/

        sleep(10);

        /** if we are lucky it is small enough to be handled straight away */

        /**dump('check zip exists');*/
        /**dump(File::isFile($zipdir."/".$example_zip_filename_to_save_as));*/

        /** and is read able */

        if (File::isFile($zipdir."/".$example_zip_filename_to_save_as)){

            $zipArchive = new \ZipArchive();
            $tryOpeningZip = $zipArchive->open($zipdir."/".$example_zip_filename_to_save_as);
            if ($tryOpeningZip == TRUE) {

                $zipArchive->close();

                /**dump('uploading to s3');*/
                $result = $client->putObject(array(
                    'Bucket'     => $bucket,
                    'Key'        => 'br24/Jobs/'.$caseID.'/zip/'.$example_zip_filename_to_save_as,
                    'SourceFile' => $zipdir."/".$example_zip_filename_to_save_as
                ));

                            // We can poll the object until it is accessible
                $client->waitUntil('ObjectExists', array(
                    'Bucket' => $bucket,
                    'Key'    => 'br24/Jobs/'.$caseID.'/zip/'.$example_zip_filename_to_save_as
                ));
            }
        }
    }


}
