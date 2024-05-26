<?php

namespace App\Repositories;

use App\Models\Task;
use App\Models\TaskDownload;
use App\Models\TaskDownloadFile;
use Illuminate\Support\Facades\Storage;

/**
 * Class AsiaRepository
 *
 * @author lexuananh@br24.com
 * @package App\Repositories
 */
class AsiaRepository extends Repository
{
    public $taskDownloadFile;
    public $taskDownload;
    public $task;

    /**
     * AsiaRepository constructor.
     *
     * @param TaskDownloadFile $taskDownloadFile
     * @param TaskDownload $taskDownload
     * @param Task $task
     */
    public function __construct(TaskDownloadFile $taskDownloadFile, TaskDownload $taskDownload, Task $task)
    {
        $this->taskDownloadFile = $taskDownloadFile;
        $this->taskDownload = $taskDownload;
        $this->task = $task;
    }

    public function scan() {
        $s3Br24Config = config('s3br24');
        $br24Config = config('br24config');
        /***/
        $ftp = config('asiaftp');
        $ftp_server = $ftp['ftp']['host'];
        $ftp_remote_dir = $ftp['ftp']['remote_dir'];
        /**xml*/
        $ftp_user_name_xml = $ftp['ftp']['xml']['username'];
        $ftp_user_pass_xml = $ftp['ftp']['xml']['password'];
        $ftp_xml_tmp = $ftp['ftp']['xml']['tmp'];
        $ftp_xml_not_zip = $ftp['ftp']['xml']['not_zip'];
        /**jobs*/
        $ftp_user_name_zip = $ftp['ftp']['zip']['username'];
        $ftp_user_pass_zip = $ftp['ftp']['zip']['password'];

        $downloadXmlLog = config('s3br24.download_log') . date('Y_m') . '_downloadXmlLog.txt';
        /**
         * Check xml from a ftp server
         */
        $conn_id = ftp_connect($ftp_server);
        /**login xml*/
        $login_result_xml = ftp_login($conn_id, $ftp_user_name_xml, $ftp_user_pass_xml);

        /** check connection*/
        if ((!$conn_id) || (!$login_result_xml)) {
            die("FTP connection has failed !");
        }
        $contents = ftp_nlist($conn_id, $ftp_remote_dir);
        $xmls = [];
        foreach ($contents as $content) {
            $res = ftp_size($conn_id, $content);
            if ("$res" != "-1") {
                $modifyDate = ftp_mdtm($conn_id, $ftp_remote_dir . $content);
                if ($modifyDate != -1) {
                    $caseId = basename($content, ".xml");
                    $originalXmlPath = $ftp_remote_dir . $content;
                    $xmlTmp = $ftp_xml_tmp . $content;
                    $xmlNotZip = $ftp_xml_not_zip . $content;

                    $task = $this->task->where('case_id', $caseId)->get()->first();

                    if ($task) {
                        /**download xlm file*/
                        $dir = $s3Br24Config['download_temp_folder'] . "xml";
                        $xmlUrl = "ftp://" . $ftp_server . "/" . $originalXmlPath;
                        $downXmlCmd = "aria2c --ftp-user=" . $ftp_user_name_xml . " --ftp-passwd=" . $ftp_user_pass_xml . " --allow-overwrite=true --piece-length=100M --max-connection-per-server=8 --file-allocation=none --max-concurrent-downloads=8 --split=8 --log-level=notice --max-tries=12  --retry-wait=15 --log={$downloadXmlLog}  --dir={$dir} " . '"' . $xmlUrl . '"';
                        exec($downXmlCmd);

                        $xmlDir = $dir . '/' . $caseId . ".xml";
                        $cmd = "cp " . $xmlDir . " " . config('s3br24.temp_xml');
                        exec($cmd);

                        /**move to xml tmp*/
                        ftp_rename($conn_id, $originalXmlPath, $xmlTmp);
                        $this->taskDownload->where('case_id', $caseId)->update(['state' => 'downloaded']);
                    } else {
                        $taskD = $this->taskDownload->where('case_id', $caseId)->get()->first();

                        if ($taskD) {
                            continue;
                        }

                        /**move to folder*/
                        if (time() - $modifyDate > 24 * 60 * 60) {
                            ftp_rename($conn_id, $originalXmlPath, $xmlNotZip);
                            continue;
                        }

                        $xmls[] = [
                            'modifyDate' => $modifyDate,
                            'name' => $caseId,
                            'fullName' => $content
                        ];
                    }
                }
            }
        }
        ftp_close($conn_id);

        /**
         * Down zip from new ftp
         */
        $conn_id = ftp_connect($ftp_server);
        /**login*/
        $login_result_zip = ftp_login($conn_id, $ftp_user_name_zip, $ftp_user_pass_zip);

        /** check connection*/
        if ((!$conn_id) || (!$login_result_zip)) {
            die("FTP connection has failed !");
        }

        foreach ($xmls as $xml) {
            $caseId = $xml['name'];
            $xmlFile = $xml['fullName'];
            $originalXmlPath = $ftp_remote_dir . $xmlFile;

            /**scan zip file*/
            $zipFolder = $ftp_remote_dir . $caseId . "/zip/";
            $zipFiles = ftp_nlist($conn_id, $zipFolder);

            if (!empty($zipFiles)) {
                $isNew = false;
                foreach ($zipFiles as $zip) {
                    $zip = $zipFolder . $zip;
                    $taskFileE = $this->taskDownloadFile->where('live', $zip)->get()->first();
                    if ($taskFileE) {
                        continue;
                    }

                    if (strpos($zip, "example.zip") > 0 || strpos($zip, "new.zip") > 0) {
                        $type = 'example';
                        if (strpos($zip, "new.zip") > 0) {
                            $type = 'new';
                            $isNew = true;
                        }

                        $dataZip = [
                            'case_id' => $caseId,
                            'live' => $zip,
                            'state' => 'new',
                            'time' => time(),
                            'url' => "ftp://" . $ftp_server . "/" . $zip,
                            'local' => basename($zip),
                            'size' => ftp_size($conn_id, $zip),
                            'type' => $type,
                            'from' => $br24Config['from_asia_ftp']
                        ];

                        $this->taskDownloadFile->insert($dataZip);
                    }
                }

                if ($isNew) {
                    /**insert db*/
                    $data = [
                        'case_id' => $caseId,
                        'state' => 'new',
                        'try' => 0,
                        'time' => time(),
                        'from' => $br24Config['from_asia_ftp']
                    ];

                    $this->taskDownload->insert($data);

                    /**download xlm file*/
                    $dir = $s3Br24Config['download_temp_folder'] . "xml";
                    $xmlUrl = "ftp://" . $ftp_server . "/" . $originalXmlPath;
                    $downXmlCmd = "aria2c --ftp-user=" . $ftp_user_name_xml . " --ftp-passwd=" . $ftp_user_pass_xml . " --allow-overwrite=true --piece-length=100M --max-connection-per-server=8 --file-allocation=none --max-concurrent-downloads=8 --split=8 --log-level=notice --max-tries=12  --retry-wait=15 --log={$downloadXmlLog}  --dir={$dir} " . '"' . $xmlUrl . '"';
                    exec($downXmlCmd . " > /dev/null &");
                }
            }
        }

        ftp_close($conn_id);
    }
}
