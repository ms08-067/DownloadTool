<?php

namespace App\Repositories;

use App\Models\Task;
use App\Models\TaskDownload;
use App\Models\TaskDownloadFile;
use App\Models\TasksFiles;
use Aws\Exception\MultipartUploadException;
use Aws\S3\MultipartUploader;
use Aws\S3\ObjectUploader;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

/**
 * Class S3Repository
 *
 * @author lexuananh@br24.com
 * @package App\Repositories
 */
class S3Repository extends Repository
{
    public $taskDownloadFile;
    public $taskDownload;
    public $task;
    public $tasksFiles;

    /**
     * S3Repository constructor.
     *
     * @param TaskDownloadFile $taskDownloadFile
     * @param TaskDownload $taskDownload
     * @param Task $task
     * @param TasksFiles $tasksFiles
     */
    public function __construct(TaskDownloadFile $taskDownloadFile, TaskDownload $taskDownload, Task $task, TasksFiles $tasksFiles)
    {
        $this->taskDownloadFile = $taskDownloadFile;
        $this->taskDownload = $taskDownload;
        $this->task = $task;
        $this->tasksFiles = $tasksFiles;
    }

    public function scan()
    {
        $s3Br24Config = config('s3br24');
        $s3 = Storage::disk('s3');
        $xlmFiles = $s3->files($s3Br24Config['xml_dir']);

        $bucket = config('filesystems.disks.s3.bucket');

        $this->getInfoFromS3duplicateOntestBucket(
            $s3,
            $bucket,
            $xlmFiles,
            $s3Br24Config['xml_tmp'],
            $s3Br24Config['xml_not_zip'],
            $s3Br24Config['job_dir'],
            $s3Br24Config['download_temp_folder']
        );
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

    private function getInfoFromS3duplicateOntestBucket($s3, $bucket, $xlmFiles, $xml_tmp_asia, $xml_not_zip_asia, $job_dir, $download_temp_folder)
    {
        $expiry = "+7 days";

        $client = new S3Client([
            'version' => 'latest',
            'profile' => 'default',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1')
        ]);

        $downloadXmlLog = config('s3br24.download_log') . date('Y_m_d') . '_downloadXmlLog.log';
        $downloadJobErrorLog = config('s3br24.download_log') . date('Y_m_d') . '_downloadJobErrorLog.log';

        if (!File::isFile(storage_path().'/logs'.$downloadJobErrorLog)) {
            $path = storage_path().'/logs'.config('s3br24.download_log');
            File::makeDirectory($path, 0777, true, true); /** make directory */
            File::put(storage_path().'/logs'.$downloadJobErrorLog, '');
        }

        if (!File::isFile(storage_path().'/logs'.$downloadXmlLog)) {
            $path = storage_path().'/app'.config('s3br24.download_temp_folder');
            File::makeDirectory($path, 0777, true, true); /** make directory */
            File::put(storage_path().'/logs'.$downloadXmlLog, '');

            $downloadXmlLog = storage_path().'/logs'.$downloadXmlLog;
        }else{
            $downloadXmlLog = storage_path().'/logs'.$downloadXmlLog;
        }

        foreach ($xlmFiles as $xlm) {

            /**check exits task*/
            $caseId = basename($xlm, ".xml");

            $task = $this->taskDownload->where('case_id', $caseId)->get()->first();

            $fileName = basename($xlm);

            if ($task) {

                /**move to xml tmp*/
                $xmlTmp = $xml_tmp_asia . date('Y-m') . '/' . $fileName;

                if ($s3->exists($xmlTmp)) {
                    $s3->delete($xmlTmp);
                }

                $s3->move($xlm, $xmlTmp);

                $this->taskDownload->where('case_id', $caseId)->update(['state' => 'downloaded']);
            } else {

                $modifyDate = $s3->lastModified($xlm);

                /**move xml (that has no zip (not tracked by this task)) to folder only after older than one day */
                if (time() - $modifyDate > 24 * 60 * 60) {
                    $fileMoveNotZip = $xml_not_zip_asia . $fileName;

                    if ($s3->exists($fileMoveNotZip)) {
                        $s3->delete($fileMoveNotZip);
                    }

                    $s3->move($xlm, $fileMoveNotZip);
                    continue;
                }

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
                }


                if($case_should_have_new_zip && !$case_has_new_zip){
                    /** there is no new zip but there should be */
                    /** do some more waiting */
                    /** we skip this xml for a little bit */
                    if (time() - $modifyDate > 2 * 60 * 60) { /** if more than 2 hour, it still has not zip file --> notify */
                        $logContent = 'XML is OK but new zip folder is EMPTY';
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
                            dump($messenger_destination);
                            if($messenger_destination == 'BITRIX'){

                                BTRXsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                            }else if($messenger_destination == 'ROCKETCHAT'){

                                RCsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                            }
                        }
                    }
                    continue;
                }

                $bypass_all_example_zips_since_they_are_included_with_the_new_files = true;
                if(!$bypass_all_example_zips_since_they_are_included_with_the_new_files){
                    if($case_should_have_example_zip && !$case_has_example_zip){
                        /** there is no example zip but there should be */

                        /** but now since the example files are bundled together with the new files and never generates the example zip */
                        /** all this can be rechecked and modified to fit the new workflow */

                        /** we skip this xml for a little bit */
                        if (time() - $modifyDate > 2 * 60 * 60) { /** if more than 2 hour, it still has not zip file --> notify */
                            $logContent = 'XML is OK but example zip folder is EMPTY';

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

                                $logContent .= ' [trying to check and treat special case for customer number = ' .$customer_number .' [AUTO DL]]';
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
                                dump($messenger_destination);

                                if($messenger_destination == 'BITRIX'){

                                    BTRXsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                                }else if($messenger_destination == 'ROCKETCHAT'){

                                    RCsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);

                                }
                            }
                        }
                        continue;
                    }
                }else{
                    /** skip all example zip files */
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

                        $taskFileE = $this->taskDownloadFile->where('live', $zip)->get()->first();

                        if ($taskFileE) {
                            dump('===================================== stopping due to xml file already on the db ==============================');

                            if (strpos($zip, "new.zip") > 0) {
                                $hasNewInDB = true;
                            }
                            continue;
                        }

                        /** should we still download the example zip if it exists? */
                        /** 20OCT2022 REMOVE EXAMPLE ZIPS ENTIRELY */
                        /**if (strpos($zip, "example.zip") > 0 || strpos($zip, "new.zip") > 0) {$type = 'example';}*/
                        if (strpos($zip, "new.zip") > 0) {
                            try {
                                if (strpos($zip, "new.zip") > 0) {
                                    $type = 'new';
                                    $isNew = true;

                                    $this->taskDownload->where('case_id', $caseId)->delete();

                                    /**insert db*/
                                    $data = [
                                        'case_id' => $caseId,
                                        'state' => 'new',
                                        'try' => 0,
                                        'time' => time(),
                                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                                    ];

                                    $this->taskDownload->insert($data);

                                    /**download xlm file*/
                                    $command = $client->getCommand('GetObject', [
                                        'Bucket' => $bucket,
                                        'Key' => $xlm
                                    ]);

                                    $requestXlm = $client->createPresignedRequest($command, $expiry);
                                    $uriXlm = (string)$requestXlm->getUri();

                                    $dir = storage_path()."/app".$download_temp_folder . "xml";


                                    if (!File::isFile(storage_path().'/app'.$downloadXmlLog."xml")) {
                                        $path = storage_path().'/app'.config('s3br24.download_temp_folder')."xml";
                                        File::makeDirectory($path, 0777, true, true); /** make directory */
                                    }

                                    $downXmlCmd = "aria2c --allow-overwrite=true --piece-length=100M --max-connection-per-server=8 --file-allocation=none --max-concurrent-downloads=8 --split=8 --log-level=notice --max-tries=12  --retry-wait=15 --log={$downloadXmlLog}  --dir={$dir} " . '"' . $uriXlm . '"';

                                    exec($downXmlCmd . " > /dev/null &");
                                }

                                $command = $client->getCommand('GetObject', [
                                    'Bucket' => $bucket,
                                    'Key' => $zip
                                ]);

                                $request = $client->createPresignedRequest($command, $expiry);
                                $uri = (string)$request->getUri();

                                $this->taskDownloadFile->where('case_id', $caseId)->where('local', basename($zip))->delete();

                                $dataZip = [
                                    'case_id' => $caseId,
                                    'live' => $zip,
                                    'state' => 'new',
                                    'time' => time(),
                                    'url' => $uri,
                                    'local' => basename($zip),
                                    'size' => $s3->size($zip),
                                    'type' => $type,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                                ];

                                $this->taskDownloadFile->insert($dataZip);


                                /** but putting in the data of the zip on the next command it will be downloaded */
                                /** we already saved the xml file now lets use the case Id to download the zip files to the appropriate customer ID. */

                                /** to be handled by the queue system we add the zip details to it */
                                \App\Jobs\AutoDL_download::dispatch($caseId, $type)->delay(now()->addSeconds(DB::table('queue_delay_seconds_autodl')->first()->queue_delay_seconds));
                            } catch (\Exception $ex) {
                                \App\Facades\CustomLog::error($ex->getMessage(), $downloadJobErrorLog);
                            }
                        } else {
                            /**compare timestamp of xml & zip --> notify if xml time > zip time about 1h*/
                            $zipModifyDate = $s3->lastModified($zip);
                            $subTime = ($zipModifyDate - $modifyDate) / 3600;
                            if ($subTime >= 1) {
                                $logContent = 'XML is OK but Zip is ERROR (xml time > zip time about 1h)';
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
                                    dump($messenger_destination);
                                    if($messenger_destination == 'BITRIX'){

                                        BTRXsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);

                                    }else if($messenger_destination == 'ROCKETCHAT'){

                                        RCsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                                    }
                                }
                            }

                        }
                    }

                    if (!$isNew) {
                        if (time() - $modifyDate > 1 * 60 * 60) { /** if more than 1 hour, it has not new.zip file --> notify */

                            $logContent = 'XML is OK but new.zip file is ERROR';
                            $searchString = "$caseId - $logContent";
                            $r = exec('grep ' . escapeshellarg($searchString) . ' ' . storage_path().'/logs'.$downloadJobErrorLog);

                            if (!($r && !empty($r)) && !$hasNewInDB) {
                                \App\Facades\CustomLog::error($searchString, $downloadJobErrorLog);

                                $message = array(
                                    'title' => $caseId,
                                    'content' => $logContent,
                                    'link' => null,
                                    'to' => config('br24config.notify_user_id')
                                );
                                $messenger_destination = env("MESSENGER_DESTINATION", "ROCKETCHAT");
                                dump($messenger_destination);
                                if($messenger_destination == 'BITRIX'){

                                    BTRXsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                                }else if($messenger_destination == 'ROCKETCHAT'){

                                    RCsendCreateJobMessage('CREATE_JOB_ZIP_ERROR', $message);
                                }
                            }
                        }
                    }
                }
            }
        }
    }


    private function getInfoFromS3($s3, $bucket, $xlmFiles, $xml_tmp_asia, $xml_not_zip_asia, $job_dir, $download_temp_folder)
    {
        die();
        $expiry = "+7 days";
        $client = new S3Client([
            'version' => 'latest',
            'profile' => 'default',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1')
        ]);
        $downloadXmlLog = config('s3br24.download_log') . date('Y_m_d') . '_downloadXmlLog.txt';

        $downloadJobErrorLog = config('s3br24.download_log') . date('Y_m_d') . '_downloadJobErrorLog.txt';
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

                $this->taskDownload->where('case_id', $caseId)->update(['state' => 'downloaded']);
            } else {
                $taskD = $this->taskDownload->where('case_id', $caseId)->get()->first();
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
                        $taskFileE = $this->taskDownloadFile->where('live', $zip)->get()->first();
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

                                    $this->taskDownload->where('case_id', $caseId)->delete();

                                    /**insert db*/
                                    $data = [
                                        'case_id' => $caseId,
                                        'state' => 'new',
                                        'try' => 0,
                                        'time' => time()
                                    ];

                                    $this->taskDownload->insert($data);

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

                                $this->taskDownloadFile->where('case_id', $caseId)->where('local', basename($zip))->delete();

                                $dataZip = [
                                    'case_id' => $caseId,
                                    'live' => $zip,
                                    'state' => 'new',
                                    'time' => time(),
                                    'url' => $uri,
                                    'local' => basename($zip),
                                    'size' => $s3->size($zip),
                                    'type' => $type
                                ];

                                $this->taskDownloadFile->insert($dataZip);
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
                        if (time() - $modifyDate > 1 * 60 * 60) {
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
            $client = new S3Client([
                'version' => 'latest',
                'profile' => 'default',
                'region' => env('AWS_DEFAULT_REGION', 'us-east-1')
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
        $expiry = "+7 days";
        $client = new S3Client([
            'version' => 'latest',
            'profile' => 'default',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1')
        ]);

        $s3Br24Config = config('s3br24');
        $bucket = config('filesystems.disks.s3.bucket');

        /** by this point the xml should have been downloaded or created hopefully */
        $xml_dir = storage_path()."/app".$s3Br24Config['download_temp_folder']."xml";
        if (File::isFile($xml_dir.'/'.$caseID.'.xml')) {
            $file_as_array = array_filter(preg_split('/\n|\r\n?/', file_get_contents($xml_dir.'/'.$caseID.'.xml')));
        }else{
            /** if the xml is not there .... we can try the scan way ... */

            $s3 = Storage::disk('s3');

            $expiry = "+7 days";
            $client = new S3Client([
                'version' => 'latest',
                'profile' => 'default',
                'region' => env('AWS_DEFAULT_REGION', 'us-east-1')
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
        foreach($file_as_array as $generic_index => $line){
            if (strpos($line, '<customerId>') !== false && strpos($line, '</customerId>') !== false) {
                $customer_number = str_replace("<customerId>", "", str_replace("</customerId>", "", str_replace(" ", "", $line)));
                break;
            }
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
        $expiry = "+7 days";
        $client = new S3Client([
            'version' => 'latest',
            'profile' => 'default',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1')
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
        $log = storage_path()."/logs".config('s3br24.download_log') . date('Y_m_d') . '_downloadLog_check_newzip_contains_example.log';
        $dir = storage_path()."/app".config('s3br24.download_temp_folder') . 'job_check_newzip_contains_example/'.$caseID;
        $zipdir  = $dir.'/zip';

        if (!File::isFile($log)) {
            $path = storage_path().'/logs'.config('s3br24.download_log');
            File::makeDirectory($path, 0777, true, true); /** make directory */
            File::put($log, '');
        }
        if (!File::isFile($zipdir)) {
            $path = storage_path().'/app'.config('s3br24.download_temp_folder')."job_check_newzip_contains_example/".$caseID."/zip";
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
            $pid = exec($cmd . " > /dev/null & echo $!;", $output);

            /**dump($pid);*/
            sleep(10);
            /** if we are lucky its small enough to be handled straight away */
        }

        $backup_of_jobexamplefolder_contents = $jobexamplefolder_contents;

        /** can we check if the file exists after 10 seconds? */
        /**dump('check if zip is there');*/

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

                /** which ever which case.. it needs to uplaod the example zip*/
                $this->download_example_folder_contents_next_zip_and_next_upload_zip_to_job_s3_zip_key($caseID, $dir, $backup_of_jobexamplefolder_contents, $client, $expiry, $bucket, $newzip_name, $zipdir);

            }
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

                $client->waitUntil('ObjectExists', array(
                    'Bucket' => $bucket,
                    'Key'    => 'br24/Jobs/'.$caseID.'/zip/'.$example_zip_filename_to_save_as
                ));
            }
        }
    }
}
