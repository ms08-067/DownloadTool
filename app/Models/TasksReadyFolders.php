<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

/**
 * Class TasksReadyFolders
 * @package App\Model
 *
 * @author anhlx412@gmail.com
 */
class TasksReadyFolders extends Model
{
    protected $connection = 'sqlite';
    protected $table = 'tasks_ready_folders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'task_id',
        'folder'
    ];

    public $timestamps = true;

    /**
     * Scan folder from S3
     * Save goal upload
     *
     * @param $task
     * @param $s3Br24Config
     * @param $log
     *
     * @author anhlx412@gmail.com
     */
    public function scanFolderS3ToUpload($log) {
        $s3 = Storage::disk('s3');
        $s3Bucket = config('filesystems.disks.s3.bucket');

        $this->scan($s3, $s3Bucket, $log);
    }

    public function scanFolderAsiaS3ToUpload($log) {
        // $s3 = Storage::disk('s3_asia');
        // $s3Bucket = config('filesystems.disks.s3_asia.bucket');

        // $this->scan($s3, $s3Bucket, $log);
    }

    public function saveDataToDb($task, $log) {
        $folderExist = self::select('*')->where('task_id', $task->id)->latest()->first();

        if ($folderExist == null) {
            writeLog($log, 'Table tasks_ready_folders haven\'t task_id.');

            $finalRedoFolder = 'ready';

            self::create([
                'task_id' => $task->id,
                'folder' => $finalRedoFolder
            ]);

            TasksFiles::where('task_id', $task->id)->update(['folder' => $finalRedoFolder]);
        } else {
            writeLog($log, 'Table tasks_ready_folders have task_id.');

            if ((date("i") % 5) == 0) {
                TasksFiles::where('task_id', $task->id)
                ->where('folder', '')
                ->update(['folder' => $folderExist->folder]);
            }
        }
    }

    protected function scan($s3, $s3Bucket, $log) {
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


        /**Create folder empty*/
        $folders = TasksFiles::where('type', 'folder')
        ->where('state', 'new')
        ->where('folder', '<>', '')
        ->select('id', 'case_id', 'jobIdTitle', 'folder', 'file_path')
        ->get();

        foreach ($folders as $folder) {
            $case_id = $folder->case_id;
            $arCases = explode("_", $case_id);
            if (isset($arCases[1])) {
                writeLog($log, "remove _* character in {$case_id}");
                $case_id = $arCases[0];
            }

            $filePathOriginal = rollbackOriginalName($folder);

            if ($folder->jobIdTitle && !empty($folder->jobIdTitle)) {
                $case_id = $folder->jobIdTitle;
            }

            $result = $client->putObject(array(
                'Bucket' => $s3Bucket,
                'Key' => "br24/Jobs/" . $case_id . "/" . $folder->folder . "/" . $filePathOriginal . "/"
            ));

            if (isset($result['ObjectURL'])) {
                writeLog($log, "create success " . "br24/Jobs/" . $case_id . "/" . $folder->folder . "/" . $filePathOriginal . "/");
                $location = $result['ObjectURL'];

                TasksFiles::where('id', $folder->id)
                ->update([
                    'state' => 'uploaded',
                    'live' => $location
                ]);
            } else {
                writeLog($log, "create fail " . "br24/Jobs/" . $case_id . "/" . $folder->folder . "/" . $filePathOriginal . "/");
            }
        }
    }
}
