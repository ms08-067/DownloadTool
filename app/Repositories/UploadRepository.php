<?php

namespace App\Repositories;

use App\Models\Br24Done;
use IDCT\Networking\Ssh\Credentials;
use IDCT\Networking\Ssh\SftpClient;
use App\Models\TasksFiles;
use Illuminate\Support\Facades\Log;
use phpseclib\Net\SFTP;

/**
 * Class UploadRepository
 *
 * @author anhlx412@gmail.com
 * @package App\Repositories
 */
class UploadRepository extends Repository
{
    public $tasksFiles;
    public $s3Repository;
    public $br24Done;
    protected $servers = [
        _SERVER_GERMANY,
        _SERVER_ASIA,
        _SERVER_OTHERS
    ];

    /**
     * UploadRepository constructor.
     *
     * @param TasksFiles $tasksFiles
     * @param S3Repository $s3Repository
     * @param Br24Done $br24Done
     */
    public function __construct(TasksFiles $tasksFiles, S3Repository $s3Repository, Br24Done $br24Done)
    {
        $this->tasksFiles = $tasksFiles;
        $this->s3Repository = $s3Repository;
        $this->br24Done = $br24Done;
    }

    /**
     * Control server upload
     *
     * @param $server
     *
     * @author anhlx412@gmail.com
     */
    public function uploadServer($server = null)
    {
        if (!$server || empty($server)) {
            $server = $this->servers;
        } else {
            $server = [strtolower($server)];
        }

        foreach ($server as $s) {
            $paths = $this->tasksFiles->getJobUploadByType($s);

            if ($paths && !empty($paths)) {
                $class = '\App\Jobs\UploadToServer' . ucfirst($s);

                foreach ($paths as $path) {
                    try {
                        $this->tasksFiles->where('id', $path->id)->update(['state' => 'queue']);

                        $class::dispatch($this, $path)->onQueue($s);
                    } catch (\Exception $e) {
                        $this->tasksFiles->where('id', $path->id)->update(['state' => 'new']);
                    }
                }
            }
        }
    }

    /**
     * upload
     *
     * @param $file
     *
     * @author anhlx412@gmail.com
     */
    public function upload($file)
    {
        $case_id = $file->case_id;
        /** Log*/
        $log = makePathLog($file->id, '_upload', 'uploadjob/' . date('Y-m-d') . '/' . $case_id);
        makeLog($log, "File ID ---------->> ". $file->id);

        try {
            /** Check file in local*/
            if (file_exists($file->local)) { /** Exist*/
                makeLog($log, 'case id: ' . $case_id . ', file id: ' . $file->id . ', size:' . number_format($file->size) . 'MB.');

                $this->sFptUpload($file, $log);
                $this->s3Repository->uploadSuccess($file, 'waiting_move');

                makeLog($log, "------------ Finish success file. ------------");
            } else { /** File is deleted*/
                $str_deleted = $file->file_path . "\n";
                $this->s3Repository->uploadFileDelete($file, 'deleted', $str_deleted);
                $this->br24Done->updateOttoUpload($file->task_id, $file->local, 3);
                makeLog($log, "------------ Upload Fail - File delete. ------------");
            }
        } catch (\Exception $e) {
            $this->s3Repository->uploadFail($file);
            Log::error('Caught exception: ' . $e->getMessage());
            makeLog($log, "------------ Upload Fail - Exception. ------------");
            makeLog($log, $e->getMessage());
        }
    }

    /**
     * use sftp for upload
     *
     * @param $file
     * @param $log
     * @throws \Exception
     *
     * @author anhlx412@gmail.com
     */
    public function sFptUpload($file, $log)
    {
        list($sftp, $target_directory) = $this->sFptConnect($file, $log);

        sfptCreateFolderByPath($sftp, $target_directory);

        $target_file = $target_directory . '/' . basename($file->local);

        $localFile = $file->local;
        if (!file_exists($localFile)) {
            $localFile = str_replace("/data/webroot/jobfolder/","/old_data/webroot/jobfolder/otto/", $localFile);
        }

        $sftp->upload($localFile, $target_file);
        /**$sftp->put($target_file, $localFile, SFTP::SOURCE_LOCAL_FILE);*/

        makeLog($log, 'File upload: ' . $target_file);

        /**$sftp->close();*/
    }

    public function sFptConnect($file, $log)
    {
        $sftp = new SftpClient();
        $credentials = Credentials::withPassword($file->upload_username, $file->upload_password);
        $sftp->setCredentials($credentials);
        $sftp->connect($file->upload_host);

        $folder = dirname($file->file_path);
        $folder = str_replace("/home/itadmin/old_data/webroot/jobfolder/otto/{$file->case_id}/ready/", '', $folder);

        $uploadDestination = json_decode($file->upload_destination);
        $target_directory = $uploadDestination->remote_dir_temp . $folder;

        return [$sftp, $target_directory];
        /**
         * //       $sftp = new SFTP($file->upload_host);
         * //       if (!$sftp->login($file->upload_username, $file->upload_password)) {
         * //           exit('Login Failed');
         * //       }
         * //
         * //       $folder = dirname($file->file_path);
         * //       $folder = str_replace("/home/itadmin/old_data/webroot/jobfolder/otto/{$file->case_id}/ready/", '', $folder);
         * //
         * //       $uploadDestination = json_decode($file->upload_destination);
         * //       $target_directory = $uploadDestination->remote_dir_temp . $folder;
         * //
         * //       return [$sftp, $target_directory];
         */
    }

    public function createFolder($file, $log)
    {
        list($sftp, $target_directory) = $this->sFptConnect($file, $log);

        sfptCreateFolderByPath($sftp, $target_directory);

        $target_file = $target_directory . '/cumulus.log';

        if (!$sftp->fileExists($target_file)) {
            $folder = dirname($file->local);
            $cumulus = $folder . '/cumulus.log';

            if (!file_exists($cumulus)) {
                $cumulus = str_replace("/data/webroot/jobfolder/","/old_data/webroot/jobfolder/otto/", $cumulus);
            }

            $sftp->upload($cumulus, $target_file);
        }

        $sftp->close();
    }
}
