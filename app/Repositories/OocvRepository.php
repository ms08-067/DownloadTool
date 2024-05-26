<?php

namespace App\Repositories;

use IDCT\Networking\Ssh\Credentials;
use IDCT\Networking\Ssh\SftpClient;
use App\Models\Br24Done;
use App\Models\TasksFiles;
use Illuminate\Support\Facades\Mail;
use App\Models\Task;
use App\Models\OocvScan;
use App\Models\Assignments;
use App\Models\Dirpaths;

/**
 * Class OocvRepository
 *
 * @author lexuananh@br24.com
 * @package App\Repositories
 */
class OocvRepository extends Repository
{
    public $task;
    public $oocvScan;
    public $dirpaths;
    public $assignments;
    public $tasksFiles;
    public $br24Done;

    /**
     * OocvRepository constructor.
     *
     * @param Task $task
     * @param OocvScan $oocvScan
     * @param Dirpaths $dirpaths
     * @param Assignments $assignments
     * @param TasksFiles $tasksFiles
     * @param Br24Done $br24Done
     */
    public function __construct(Task $task, OocvScan $oocvScan, Dirpaths $dirpaths, Assignments $assignments, TasksFiles $tasksFiles, Br24Done $br24Done)
    {
        $this->task = $task;
        $this->oocvScan = $oocvScan;
        $this->dirpaths = $dirpaths;
        $this->assignments = $assignments;
        $this->tasksFiles = $tasksFiles;
        $this->br24Done = $br24Done;
    }

    /**
     * Download OOCV jobs.
     */
    public function oocvDownload()
    {
        $datetimeObj = date_create("now");
        date_sub($datetimeObj, date_interval_create_from_date_string('1 years'));
        $beforeTime1Year = $datetimeObj->format('Y-m-d H:i:s');

        /**update project_id from job title*/
        $projectsNew = $this->task->select('id', 'case_id', 'jobTitle', 'project_id')
        ->where('customer_id', 1547)
        ->where('project_id', '')
        ->where('deliveryProduction', '>=', $beforeTime1Year)
        ->get();
        foreach ($projectsNew as $p) {
            $pos = strpos($p['case_id'], '_');
            if ($pos !== FALSE) {
                $projectId = substr($p['case_id'], 0, $pos);
            } else {
                $projectId = $p['case_id'];
            }
            if (is_numeric($projectId)) {
                $this->task->where('id', $p['id'])->update(['project_id' => $projectId]);
            }
        }

        /**check number folder downloading*/
        $oocvDownloading = $this->oocvScan->where('state', 'downloading')->where('type', 'folder')->count();
        if ($oocvDownloading >= 5) {
            return;
        }

        /**download job*/
        $projects = $this->task->select('id', 'case_id', 'jobTitle', 'project_id')
        ->where('customer_id', 1547)
        ->whereRaw('case_id REGEXP ("^[0-9]+$")')
        ->where('project_id', '!=', '')
        ->where('redo', 0)
        ->where('is_training', 0)
        /**->where('is_temp_stop', 0)*/
        ->where('deliveryProduction', '>=', $beforeTime1Year)
        ->orderBy('isExpress', 'desc')
        ->orderBy('vip_job', 'desc')
        ->orderBy('deliveryProduction', 'asc')
        ->get()->keyBy('project_id')->toArray();

        $sftp = config('oocv');
        $remoteDir = $sftp['sftp']['remote_dir'];
        $connection = ssh2_connect($sftp['sftp']['host'], 22);
        ssh2_auth_password($connection, $sftp['sftp']['username'], $sftp['sftp']['password']);
        $connection_resource = ssh2_sftp($connection);
        $stream = intval($connection_resource); /**with php version > 5.6 need to include intval*/

        $dirs = opendir("ssh2.sftp://{$stream}{$remoteDir}");

        $pr = 1;
        while (false !== ($dir = readdir($dirs))) {
            if ($pr > 5) {
                break;
            }

            $projectId = substr($dir, 0, strpos($dir, '_'));
            if ($dir != "." && $dir != ".." && $dir != 'received' && in_array($projectId, array_keys($projects))) {
                $live = $remoteDir . $dir;
                if (strlen($projectId) > 5) {
                    $isTransferCompleted = false;
                    $current_dir = opendir("ssh2.sftp://$stream/$live");
                    while (false !== ($log = readdir($current_dir))) {
                        if (strpos($log, '-transfer-completed') !== false) {
                            $isTransferCompleted = true;
                            break;
                        }
                    }
                    if (!$isTransferCompleted) {
                        continue;
                    }
                }

                $folderIsset = $this->oocvScan->where('live', 'LIKE', '%' . $live . '%')->count();

                $folderDownloadedIsset = $this->oocvScan
                ->where('live', 'LIKE', '%' . $live . '%')
                ->where('type', 'folder')
                ->where('state', 'downloaded')
                ->count();

                $folderDownloadingIsset = $this->oocvScan
                ->where('live', 'LIKE', '%' . $live . '%')
                ->where('type', 'folder')
                ->count();


                if ($folderIsset == 0 || $folderDownloadedIsset == $folderDownloadingIsset) {
                    $try = 1;

                    if ($folderDownloadedIsset > 0) {
                        $try = $folderDownloadedIsset + 1;
                    }
                    $downloadDir = config('oocv.download_dir') . $dir;
                    $data = [
                        'live' => $live,
                        'local' => $downloadDir,
                        'size' => 0,
                        'name' => $dir,
                        'state' => 'downloading',
                        'case_id' => $projects[$projectId]['case_id'],
                        'task_id' => $projects[$projectId]['id'],
                        'project_id' => $projectId,
                        'type' => 'folder',
                        'try' => $try,
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s"),
                    ];

                    $this->oocvScan->insert($data);

                    createFolderByPath($downloadDir);
                    /**exec("mkdir $downloadDir");*/

                    /**download file*/
                    $files = opendir("ssh2.sftp://{$stream}{$remoteDir}" . $dir);
                    while (false !== ($file = readdir($files))) {
                        if (($file != ".") && ($file != "..")) {
                            $statinfo = ssh2_sftp_stat($connection_resource, "{$remoteDir}{$dir}/{$file}");

                            $jf = $projects[$projectId]['case_id'] . "/" . "new" . "/" . $dir;
                            $data = [
                                'live' => $remoteDir . $dir . "/" . $file,
                                'local' => $jf . "/" . $file,
                                'name' => $file,
                                'size' => $statinfo['size'],
                                'state' => 'downloading',
                                'case_id' => $projects[$projectId]['case_id'],
                                'task_id' => $projects[$projectId]['id'],
                                'project_id' => $projectId,
                                'type' => 'file',
                                'try' => $try,
                                'created_at' => date("Y-m-d H:i:s"),
                                'updated_at' => date("Y-m-d H:i:s"),
                            ];

                            $this->oocvScan->insert($data);
                            $log = $downloadDir . '/' . 'oocv.log';
                            $link = "sftp://{$sftp['sftp']['host']}:22/{$data['live']}";
                            $cmd = "aria2c --allow-overwrite=true --piece-length=100M --max-connection-per-server=8 --file-allocation=none --max-concurrent-downloads=8 --split=8 --log-level=notice --max-tries=12  --retry-wait=15 --dir={$downloadDir} --log={$log} --ftp-user={$sftp['sftp']['username']} --ftp-passwd={$sftp['sftp']['password']} {$link}";
                            exec($cmd . " > /dev/null &");
                        }
                    }
                    $pr++;
                } else if ($folderIsset == 1) {
                    $this->oocvScan->where('live', 'LIKE', '%' . $live . '%')->delete();
                }
            }

        }
    }

    /**
     * Scan & Download OOCV jobs again.
     */
    public function oocvFixDownload()
    {
        $sftp = config('oocv');
        $date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . "-30 minutes"));
        $oocvScan = $this->oocvScan->where('updated_at', '<=', $date)->where('state', 'downloading')->where('type', 'file')->get();
        foreach ($oocvScan as $oocv) {
            $dir = basename(dirname($oocv['live']));
            $downloadDir = config('oocv.download_dir') . $dir;
            $log = $downloadDir . '/' . 'oocv.log';
            $link = "sftp://{$sftp['sftp']['host']}:22/{$oocv['live']}";
            $cmd = "aria2c --allow-overwrite=true --piece-length=100M --max-connection-per-server=8 --file-allocation=none --max-concurrent-downloads=8 --split=8 --log-level=notice --max-tries=12  --retry-wait=15 --dir={$downloadDir} --log={$log} --ftp-user={$sftp['sftp']['username']} --ftp-passwd={$sftp['sftp']['password']} {$link}";

            /**check file is complete downloaded*/
            $searchString = "Download complete: " . $downloadDir . "/" . $oocv['name'];
            $r = exec('grep ' . escapeshellarg($searchString) . ' ' . $log);
            if (!(!empty($r) && $r != false)) {
                /**check server process that execs on file*/
                $processNum = checkProcessInServer($oocv['live']);
                if ($processNum < 3) {
                    exec($cmd . " > /dev/null &");
                    $oocv->state = 'downloading';
                    $oocv->updated_at = date("Y-m-d H:i:s");
                    $oocv->save();
                }
            }
        }
    }

    /**
     * Update state OOCV jobs.
     */
    public function oocvUpdate()
    {
        $case_ids = array();
        $downloadedFiles = array();
        $oocvFolder = $this->oocvScan
        ->where('state', 'downloading')
        ->where('type', 'folder')->get();
        foreach ($oocvFolder as $o) {
            $oocvFiles = $this->oocvScan
            ->where('state', 'downloading')
            ->where('type', 'file')
            ->where('live', 'like', $o['live'] . '%')
            ->get();
            $filesStateCount = $oocvFiles->count();
            /**read file log update file downloaded*/
            $logFile = $o['local'] . '/' . 'oocv.log';
            foreach ($oocvFiles as $f) {
                if ($f->state == 'downloading') {
                    $searchString = "Download complete: " . $o['local'] . "/" . $f['name'];
                    if (exec('grep ' . escapeshellarg($searchString) . ' ' . $logFile)) {
                        /**check server process that execs on file*/
                        $processNum = checkProcessInServer($f['live']);
                        if ($processNum < 3) {
                            $f->state = 'downloaded';
                            $f->save();
                        }
                    }
                }
            }

            $oocvFilesCountAll = $this->oocvScan
            ->where('type', 'file')
            ->where('live', 'like', $o['live'] . '%')
            ->get()->count();

            /**update state folder and cp files*/
            $oocvFilesD = $this->oocvScan->where('state', 'downloaded')->where('type', 'file')->where('live', 'like', $o['live'] . '%')->get();
            $filesStateCountD = $oocvFilesD->count();

            if ($filesStateCountD > 0 && $filesStateCount == 0 && $oocvFilesCountAll == $filesStateCountD) {
                $this->updateJobOocv($o['id']);
                $o->state = 'moving';
                $o->save();

                /**move file to job folder*/
                $dirJob = config('oocv.jobfolder_dir') . $o['case_id'];

                $jf = $dirJob . "/new" . "/" . $o['name'];
                if (createFolderByPath($jf)) {
                    $cmd = "cp -R " . $o['local'] . "/* " . $jf;
                    exec($cmd . " > /dev/null &");
                }

                $downloadedFiles[$o['case_id']][$o['live']] = $oocvFilesD;
                if (!in_array($o['case_id'], $case_ids)) {
                    $case_ids[] = $o['case_id'];
                }
            }
        }

        /**
         * Move when downloaded
         * https://tasks.br24.vn/issues/1446
         *
         * @anhlx412@gmail.com
         */
        $oocvFolderMove = $this->oocvScan->where('state', 'moving')->where('type', 'folder')->get();
        foreach ($oocvFolderMove as $o) {
            if ($this->oocvMoveToReceived($o['live'])) {
                $o->state = 'downloaded';
                $o->save();

                /**read file log update file downloaded*/
                $logFile = $o['local'] . '/' . 'oocv.log';
                exec('rm -rf ' . $logFile);
            }
        }

        if (!empty($downloadedFiles)) {
            $case_ids = implode(', ', $case_ids);
            $sub = "Otto Job has new folders $case_ids";
            Mail::send('emails.oocv', ['oocvFilesDownloaded' => $downloadedFiles, 'sub' => $sub], function ($m) use ($downloadedFiles, $sub) {
                $m->from('tool@br24.com', 'Prodtool');
                foreach (config('oocv.email_notify') as $email) {
                    $m->to($email[0], $email[1]);
                }
                $m->subject($sub);
            });
        }
    }

    /**
     * Update info OOCV jobs.
     *
     * @param $id
     */
    protected function updateJobOocv($id)
    {
        $oocvScan = $this->oocvScan->find($id);
        $path = "jobfolder/{$oocvScan->case_id}/new";
        $dirpath = $this->dirpaths->where('path_folder', $path)->first();

        $path_folder = $path . '/' . $oocvScan->name;
        $existedDirpath = $this->dirpaths->where('path_folder', $path_folder)->where('task_id', $dirpath->task_id)->first();
        if (!isset($existedDirpath->id)) {
            /**update TreeNested dirpaths*/
            $left = $dirpath->rght;
            $right = $dirpath->rght + 1;
            /**update dirpath*/
            $this->dirpaths->where('task_id', $dirpath->task_id)->where('lft', '>=', $left)->increment('lft', 2);
            $this->dirpaths->where('task_id', $dirpath->task_id)->where('rght', '>=', $left)->increment('rght', 2);
            $dirpathNew = new Dirpaths();
            $dirpathNew->path_folder = $path_folder;
            $dirpathNew->parent_id = $dirpath->id;
            $dirpathNew->lft = $left;
            $dirpathNew->rght = $right;
            $dirpathNew->level = $dirpath->level + 1;
            $dirpathNew->has_pictures = 1;
            $dirpathNew->is_example = 0;
            $dirpathNew->is_assigned = 0;
            $dirpathNew->created = date("Y-m-d H:i:s");
            $dirpathNew->task_id = $dirpath->task_id;
            $dirpathNew->save();

            $dirpath_id = $dirpathNew->id;
        } else {
            $dirpath_id = $existedDirpath->id;
        }

        /**update assiment*/
        $exclude = array('DB', 'db', 'ini', 'xml', 'DS_Store', 'txt', 'zip', 'rar', 'bat', 'cos', 'BridgeSort', 'exe', 'bat', 'log');
        $oocvFiles = $this->oocvScan->where('state', 'downloaded')->where('type', 'file')->where('live', 'like', $oocvScan->live . '%')->get();
        foreach ($oocvFiles as $file) {
            $path_info = pathinfo(basename($file['local']));
            if (!in_array($path_info['extension'], $exclude)) {
                $existedAssigment = $this->assignments->where('file_name', $file->name)->where('dirpath_id', $dirpath_id)->first();
                if (!isset($existedAssigment->id)) {
                    $row = [
                        'task_id' => $dirpath->task_id,
                        'case_id' => $oocvScan->case_id,
                        'dirpath_id' => $dirpath_id,
                        'file_name' => $file->name,
                        'actived' => 1,
                        'is_assigned' => 0,
                        'created' => date("Y-m-d H:i:s")
                    ];

                    $this->assignments->insert($row);
                }

                /**#840 -> http://management.br24.vn/issues/840*/
                if ($file->type == 'file') {
                    $dirJob = config('oocv.jobfolder_dir') . $oocvScan->case_id;
                    if (!file_exists($dirJob)) {
                        mkdir($dirJob, 0777);
                        mkdir($dirJob . "/new", 0777);
                        mkdir($dirJob . "/examples", 0777);
                    }

                    $filename = pathinfo($file->name, PATHINFO_FILENAME);
                    $extention = pathinfo($file->name, PATHINFO_EXTENSION);
                    $filename = explode('_', strtoupper($filename));
                    if (in_array('DSM', $filename) || $extention == 'html') {
                        $oocvTempFolderLocal = $oocvScan->local . '/' . $file->name;
                        $jobfolderExamplePath = config('oocv.jobfolder_dir') . str_replace("/new/", "/examples/", $file->local);

                        if (createFolderByPath(dirname($jobfolderExamplePath))) {
                            $cmd = "cp -r " . $oocvTempFolderLocal . " " . $jobfolderExamplePath;
                            exec($cmd);
                        }
                    }
                }
            }
        }

        /**update task*/
        $assi = $this->assignments->where('task_id', $dirpath->task_id)->get()->count();
        $this->task->where('id', $dirpath->task_id)->update(['status' => 1, 'sub_status' => 1, 'sent_to_darlim' => 0, 'total_file' => $assi, 'is_move' => 0]);
    }

    /**
     * Move job finish to received folder.
     *
     * @param string $sfptPath
     * @return bool
     * @throws \Exception
     */
    public function oocvMoveToReceived($sfptPath = '')
    {
        if ($sfptPath == '') {
            return false;
        }

        $sftp = config('oocv');
        $remoteDir = $sftp['sftp']['remote_dir'];
        $remoteReceived = $sftp['sftp']['remote_dir_received'];

        $dir = basename($sfptPath);
        $live = $remoteDir . $dir;
        $received = $remoteReceived . $dir;

        $sftp = sfptConnect($sftp['sftp']['username'], $sftp['sftp']['password'], $sftp['sftp']['host']);

        if (!$sftp->fileExists($live)) {
            return true;
        }

        try {
            if ($sftp->fileExists($received)) {
                $files = $sftp->getFileList($received);

                if ($files && !empty($files)) {
                    foreach ($files as $file) {
                        if (in_array($file, array(".", ".."))) {
                            continue;
                        }

                        $sftp->remove($received . '/' . $file);
                    }
                }

                $sftp->removeDirectory($received);
            }

            $sftp->rename($live, $received);
            $sftp->close();

            return true;
        } catch (\Exception $e) {
            $sftp->close();
            return false;
        }
    }

    /**
     * Move otto folder upload from tmp to final
     */
    public function oocvMoveToFinal()
    {
        $data = $this->tasksFiles->oocvCheckFolderToMove();

        $folderFinish = [];
        foreach ($data as $folder => $info) {
            if ($info["total_wait_move"] == $info["total_file_in_folder"]) {
                $folderFinish[$folder] = $info;
            }
        }

        if (!empty($folderFinish)) {
            foreach ($folderFinish as $folder => $info) {
                /**Log*/
                $log = makePathLog($info['case_id'], '_move_final', 'uploadjob/' . date('Y-m-d') . '/' . $info['case_id']);

                $sftp = sfptConnect($info['upload_username'], $info['upload_password'], $info['upload_host']);

                $temp = json_decode($info['upload_destination']);
                $remoteFolderTemp = $temp->remote_dir_temp . $folder;
                $remoteFolderFinal = $temp->remote_dir_final . $folder;

                makeLog($log, "--------------------------------------------------");
                makeLog($log, "From folder ---------->> " . $remoteFolderTemp);
                makeLog($log, "To folder ---------->> " . $remoteFolderFinal);
                try {
                    if ($sftp->fileExists($remoteFolderFinal)) {
                        $files = $sftp->getFileList($remoteFolderFinal);
                        if ($files && !empty($files)) {
                            foreach ($files as $file) {
                                if (in_array($file, array(".", ".."))) {
                                    continue;
                                }

                                $sftp->remove($remoteFolderFinal . '/' . $file);
                            }
                        }
                        $sftp->removeDirectory($remoteFolderFinal);
                    }
                    $sftp->rename($remoteFolderTemp, $remoteFolderFinal);

                    $this->tasksFiles
                    ->where('file_path', 'like', '%' . $folder . '%')
                    ->where('task_id', $info['task_id'])
                    ->update(['state' => 'uploaded']);

                    $this->br24Done
                    ->where('note', 'like', '%' . $folder . '%')
                    ->where('task_id', $info['task_id'])
                    ->update(['is_otto_sub_task_upload' => 2]);

                    makeLog($log, "Move folder success.");
                } catch (\Exception $e) {
                    if ($e->getMessage() == 'Unable to rename remote file!') {
                        if ($sftp->fileExists($remoteFolderFinal)) {
                            $files = $sftp->getFileList($remoteFolderFinal);

                            if ($files && !empty($files)) {
                                foreach ($files as $file) {
                                    if (in_array($file, array(".", ".."))) {
                                        continue;
                                    }

                                    $sftp->remove($remoteFolderFinal . '/' . $file);
                                }
                            }

                            $sftp->removeDirectory($remoteFolderFinal);
                        }
                    }

                    makeLog($log, "------------ Move Fail - Exception. ------------");
                    makeLog($log, $e->getMessage());
                }

                $sftp->close();
            }
        }
    }
}
