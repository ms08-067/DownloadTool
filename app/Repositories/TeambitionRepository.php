<?php

namespace App\Repositories;

use App\Models\Task;
use App\Models\TeambitionSetting;
use App\Models\TeambitionTask;
use App\Models\TeambitionTaskFile;
use Illuminate\Support\Facades\File;

class TeambitionRepository extends Repository
{
    public $teambitionSetting;
    public $teambitionTask;
    public $teambitionTaskFile;
    public $task;

    public function __construct(TeambitionSetting $teambitionSetting, TeambitionTask $teambitionTask, TeambitionTaskFile $teambitionTaskFile, Task $task)
    {
        $this->teambitionSetting = $teambitionSetting;
        $this->teambitionTask = $teambitionTask;
        $this->teambitionTaskFile = $teambitionTaskFile;
        $this->task = $task;
    }

    private function getTeambitionSetting()
    {
        $settings = $this->teambitionSetting->select('type', 'value')->get();
        if (!empty($settings)) {
            $settings = $settings->mapWithKeys(function ($item) {
                return [$item['type'] => $item['value']];
            });
        }
        return $settings;
    }

    public function scan()
    {
        $teambition_settings = $this->getTeambitionSetting();
        if (empty($teambition_settings)) {
            return;
        }

        $base_api_url = $teambition_settings['teambition_base_api_url'];
        $access_token = $teambition_settings['teambition_api_access_token'];
        $teambition_organizations = $teambition_settings['teambition_organizations'];
        $teambition_organizations = json_decode($teambition_organizations, true);
        $teambition_downloaded_folder = $teambition_settings['teambition_downloaded_folder'];
        $teambition_executor_id = $teambition_settings['teambition_executor_id'];
        foreach ($teambition_organizations as $teambition_organization) {
            $organization_id = $teambition_organization['id'];
            $organization_name = $teambition_organization['name'];
            $organization_marking = $teambition_organization['marking'];
            $organization_customfields = $teambition_organization['customfields'];
            /**get my uncomplete tasks of organization*/
            $task_api_url = $base_api_url . "/organizations/$organization_id/tasks?access_token=$access_token&isDone=false";
            $tasks = $this->sendTeambitionRequest($task_api_url);
            if (!empty($tasks)) {
                /**filter by executor*/
                $new_task_ids = array();
                $new_tasks = array();
                $new_task_files = array();
                foreach ($tasks as $task) {
                    if (!isset($task['_executorId'])) {
                        continue;
                    }
                    if ($task['_executorId'] != $teambition_executor_id) {
                        continue;
                    }
                    /**job has no parent and has no attachments --> no create job*/
                    /**
                     * // if (!isset($task['parent']['_id']) && $task['badges']['attachmentsCount'] < 1) { 
                     * //     continue;
                     * // }
                     */
                    $new_task_ids[] = $task['_id'];
                    $new_tasks[$task['_id']] = $task;

                    $attachments_api_url = $base_api_url . "/tasks/{$task['_id']}/activities/attachments?access_token=$access_token";
                    $attachments = $this->sendTeambitionRequest($attachments_api_url);
                    if (!empty($attachments)) {
                        $path = "$teambition_downloaded_folder/jobs/{$task['_id']}/new";
                        if (!is_dir($path)) {
                            exec("mkdir -p $path");
                            exec("chmod -R 777 $path");
                        }
                        $log = "$teambition_downloaded_folder/logs/{$task['_id']}.txt";
                        foreach ($attachments as $attachment) {
                            if (isset($attachment['fileType'])) {
                                $this->downloadTeambitionFile($attachment['downloadUrl'], $path, $log);

                                $new_task_files[$task['_id']][] = array(
                                    'file_name' => $attachment['fileName'],
                                    'file_type' => strtolower($attachment['fileType']),
                                    'file_size' => $attachment['fileSize'],
                                    'teambition_work_id' => $attachment['_id'],
                                    'teambition_collection_id' => $attachment['_parentId']
                                );
                            }
                        }
                    } else {
                        /** if job has no attachments --> has no task_files --> set job downloaded*/
                        $new_tasks[$task['_id']]['teambition_status'] = 'downloaded';
                    }
                }
                if (!empty($new_tasks)) {
                    $teambition_task = $this->teambitionTask
                    ->where('customerFtp', $organization_name)
                    ->select('prodtool_case_id')
                    ->orderBy('prodtool_case_id', 'desc')
                    ->first();
                    $prodtool_max_case_id_1 = 0;
                    if (isset($teambition_task['prodtool_case_id'])) {
                        $prodtool_max_case_id_1 = str_replace($organization_marking, '', $teambition_task['prodtool_case_id']);
                        $prodtool_max_case_id_1 = 1 * $prodtool_max_case_id_1;
                    }
                    $prodtool_task = $this->task
                    ->where('customerFtp', $organization_name)
                    ->select('case_id')
                    ->orderBy('case_id', 'desc')
                    ->first();
                    $prodtool_max_case_id_2 = 0;
                    if (isset($prodtool_task['case_id'])) {
                        $prodtool_max_case_id_2 = str_replace($organization_marking, '', $prodtool_task['case_id']);
                        $prodtool_max_case_id_2 = 1 * $prodtool_max_case_id_2;
                    }

                    $prodtool_max_case_id = $prodtool_max_case_id_1 > $prodtool_max_case_id_2 ? $prodtool_max_case_id_1 : $prodtool_max_case_id_2;

                    $created_task_ids = $this->teambitionTask
                    ->whereIn('teambition_task_id', $new_task_ids)
                    ->select('teambition_task_id')
                    ->get();
                    if (!empty($created_task_ids)) {
                        $created_task_ids = $created_task_ids->pluck('teambition_task_id')->toArray();
                        $new_task_ids = array_diff($new_task_ids, $created_task_ids);
                    }
                    $data = array();
                    $created_at = date('Y-m-d H:i:s');
                    $timezone = new \DateTimeZone('Asia/Ho_Chi_Minh');
                    foreach ($new_task_ids as $new_task_id) {
                        $prodtool_max_case_id++;

                        $amount = null;
                        $dueDate = null;
                        $teambition_type_id = null;
                        $teambition_case_id = null;
                        foreach ($new_tasks[$new_task_id]['customfields'] as $customfield) {
                            if (isset($customfield['values'][0])) {
                                if ($customfield['_customfieldId'] == $organization_customfields['amount_id']) {
                                    $amount = $customfield['values'][0];
                                }
                                if ($customfield['_customfieldId'] == $organization_customfields['duedate_id']) {
                                    $dueDate = new \DateTime($customfield['values'][0]);
                                    $dueDate->setTimezone($timezone);
                                    $dueDate = $dueDate->format("Y-m-d H:i:s");
                                }
                                if ($customfield['_customfieldId'] == $organization_customfields['type_demand_id']) {
                                    $teambition_type_id = $customfield['values'][0];
                                }
                                if ($customfield['_customfieldId'] == $organization_customfields['case_id']) {
                                    $teambition_case_id = $customfield['values'][0];
                                }
                            }
                        }
                        $new_case_id = str_pad($prodtool_max_case_id, 6, '0', STR_PAD_LEFT);

                        $parent_id = null;
                        if (isset($new_tasks[$new_task_id]['parent']['_id'])) {
                            $teambition_parent_id = $new_tasks[$new_task_id]['parent']['_id'];

                            $parent_task = $this->teambitionTask->where('teambition_task_id', $teambition_parent_id)->select('id')->first();
                            if (isset($parent_task->id)) {
                                $parent_id = $parent_task->id;
                            }
                        }

                        $data[] = array(
                            'title' => $new_tasks[$new_task_id]['content'],
                            'prodtool_case_id' => "$organization_marking$new_case_id",
                            'teambition_organization_id' => $organization_id,
                            'teambition_project_id' => $new_tasks[$new_task_id]['_projectId'],
                            'teambition_task_id' => $new_task_id,
                            'status' => isset($new_tasks[$new_task_id]['teambition_status']) ? $new_tasks[$new_task_id]['teambition_status'] : 'new',
                            'jobInfo' => $new_tasks[$new_task_id]['note'],
                            'amount' => $amount,
                            'dueDate' => $dueDate,
                            'teambition_type_id' => $teambition_type_id,
                            'teambition_case_id' => $teambition_case_id,
                            'customerFtp' => $organization_name,
                            'parent_id' => $parent_id,
                            'created_at' => $created_at
                        );
                    }
                    $result = $this->teambitionTask->insert($data);
                    if ($result) {
                        $created_tasks = $this->teambitionTask
                        ->whereIn('teambition_task_id', $new_task_ids)
                        ->select('id', 'teambition_task_id')
                        ->get();
                        if (!empty($created_tasks)) {
                            $data_task_files = array();
                            foreach ($created_tasks as $created_task) {
                                if (isset($new_task_files[$created_task['teambition_task_id']])) {
                                    foreach ($new_task_files[$created_task['teambition_task_id']] as &$new_task_file) {
                                        $new_task_file['task_id'] = $created_task['id'];
                                        $new_task_file['status'] = 'new';
                                        $new_task_file['created_at'] = $created_at;
                                        $data_task_files[] = $new_task_file;
                                    }
                                }
                            }
                            if (!empty($data_task_files)) {
                                $this->teambitionTaskFile->insert($data_task_files);
                            }
                        }
                    }
                }
            }
        }
    }

    public function checkDownload()
    {
        $teambition_settings = $this->getTeambitionSetting();
        if (empty($teambition_settings)) {
            return;
        }
        $teambition_downloaded_folder = $teambition_settings['teambition_downloaded_folder'];

        $new_tasks = $this->teambitionTask->where('status', 'new')->get();
        foreach ($new_tasks as $task) {
            $log = "$teambition_downloaded_folder/logs/{$task->teambition_task_id}.txt";
            $download_path = "$teambition_downloaded_folder/jobs/{$task->teambition_task_id}";

            $new_task_files = $this->teambitionTaskFile
            ->where('task_id', $task->id)
            ->where('status', 'new')
            ->get();
            if (empty($new_task_files->toArray())) {
                $task->status = 'downloaded';
                $task->save();
                continue;
            }
            foreach ($new_task_files as $task_file) {
                $searchString = "Download complete: $download_path/new/{$task_file->file_name}";
                if(exec('grep ' . escapeshellarg($searchString) . ' ' . $log)) {
                    $task_file->status = 'downloaded';
                    $task_file->save();
                }
            }
        }
    }

    public function create()
    {
        $teambition_settings = $this->getTeambitionSetting();
        if (empty($teambition_settings)) {
            return;
        }
        $base_api_url = $teambition_settings['teambition_base_api_url'];
        $access_token = $teambition_settings['teambition_api_access_token'];

        $teambition_organizations = $teambition_settings['teambition_organizations'];
        $teambition_organizations = json_decode($teambition_organizations, true);
        $teambition_organizations = collect($teambition_organizations)->keyBy('id');

        $teambition_downloaded_folder = $teambition_settings['teambition_downloaded_folder'];
        if (!is_dir("$teambition_downloaded_folder/xml")) {
            exec("mkdir -p $teambition_downloaded_folder/xml");
        }

        $jobFolder = config('s3br24.job_folder');
        /**get all tasks what is downloaded all files*/
        $downloaded_tasks = $this->teambitionTask->with(['job_type'])->where('status', 'downloaded')->get();
        foreach ($downloaded_tasks as $task) {
            $tempNewFolder = "$teambition_downloaded_folder/jobs/{$task->teambition_task_id}/new";
            $newFolder = $jobFolder . $task->prodtool_case_id . "/new/";
            if (!is_dir($newFolder)) {
                exec("mkdir -p $newFolder");
            } else {
                exec("rm -r $newFolder");
                exec("mkdir -p $newFolder");
            }
            exec("chmod -R 777 $jobFolder". $task->prodtool_case_id );
            /**copy file to jobfolder*/
            $downloaded_task_files = $this->teambitionTaskFile
            ->where('task_id', $task->id)
            ->where('status', 'downloaded')
            ->get();
            $task_file_ids = array();
            $updated_at = date('Y-m-d H:i:s');
            foreach ($downloaded_task_files as $task_file) {
                if ($task_file->file_type == 'rar') {
                    /**check rar file*/
                    $check_rar_log = "$tempNewFolder/check_rar.log";
                    $dirRar = "$tempNewFolder/{$task_file->file_name}";
                    exec("unrar t '$dirRar' > $check_rar_log");
                    $searchString = 'All OK';
                    if(exec('grep ' . escapeshellarg($searchString) . ' ' . $check_rar_log)) {
                        exec("unrar x '$dirRar' $newFolder");
                    } else {
                        $task_file->status = 'unrar_error';
                        $task_file->save();
                    }
                    exec("rm $check_rar_log");
                } elseif ($task_file->file_type == 'zip') {
                    /**check zip file*/
                    $dirZip = "$tempNewFolder/{$task_file->file_name}";
                    $zipArchive = new \ZipArchive();
                    $tryOpeningZip = $zipArchive->open($dirZip);
                    if ($tryOpeningZip !== TRUE) {
                        $task_file->status = 'unzip_error';
                        $task_file->save();
                    }
                    exec("unzip -O cp936 -o '$dirZip' -d $newFolder");
                } else {
                    exec("cp '$tempNewFolder/{$task_file->file_name}' $newFolder/");
                }
                $task_file_ids[] = $task_file->id;

                /**send message activity to teambition task*/
                $content = "[Downloaded Image] {$task_file->file_name}";
                $content = urlencode($content);
                $send_activity_api_url = $base_api_url . "/tasks/{$task->teambition_task_id}/activities?access_token=$access_token&content=$content";
                $this->sendTeambitionRequest($send_activity_api_url, 'POST');
            }
            $result = $this->teambitionTaskFile->whereIn('id', $task_file_ids)
            ->update(array(
                'status' => 'copied',
                'updated_at' => $updated_at
            ));
            /** if job has no attachments --> has no task_files --> allow to create job*/
            $count_task_files = $this->teambitionTaskFile
            ->where('task_id', $task->id)
            ->count();
            if ($result || empty($count_task_files)) {
                $jobTitle = $task->title;
                /**
                 * //if (!empty($task->teambition_case_id)) {
                 * //    $jobTitle = $task->teambition_case_id . '_' . $jobTitle;
                 * //} 
                 */

                $dueDate = $task->dueDate;
                if (empty($dueDate)) {
                    $dueDate = date("d.m.Y H:i");
                } else {
                    $dueDate = date("Y-m-d H:i:s", strtotime($dueDate));
                    $dueDate = date_create($dueDate);
                    date_sub($dueDate, date_interval_create_from_date_string('2 hours')); 
                    $dueDate = $dueDate->format('d.m.Y H:i');
                }
                
                $jobInfo = strip_tags($task->jobInfo);

                if (isset($task->parent_id)) {
                    $parent_task = $this->teambitionTask->where('id', $task->parent_id)->select('title', 'prodtool_case_id')->first();
                    if (isset($parent_task->title)) {
                        $jobInfo = 'Parent ID: ' . $parent_task->prodtool_case_id . "\n" . $jobInfo;
                        $jobInfo = 'Parent Name: ' . $parent_task->title . "\n" . $jobInfo;
                    }
                }

                $job_type = $task->job_type;
                if (!empty($job_type->vn_name)) {
                    $jobInfo = 'Job type VN: ' . $job_type->vn_name . "\n" . $jobInfo;
                }
                if (!empty($job_type->teambition_name)) {
                    $jobInfo = 'Job type JC: ' . $job_type->teambition_name . "\n" . $jobInfo;
                }
                /**create xml*/
                $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
                $xml .= "<job>";
                $xml .= "<customerFtp>{$task->customerFtp}</customerFtp>";
                $xml .= "<jobId>{$task->prodtool_case_id}</jobId>";
                $xml .= "<jobTitle><![CDATA[{$jobTitle}]]></jobTitle>";
                /** $xml .= "<jobType>{$taskJobType}</jobType>";*/
                $xml .= "<amount>{$task->amount}</amount>";
                $xml .= "<isExpress>0</isExpress>";
                $xml .= "<deliveryProduction>{$dueDate}</deliveryProduction>";
                $xml .= "<jobInfo><![CDATA[{$jobInfo}]]></jobInfo>";
                $xml .= "<jobInfoProduction><![CDATA[{$jobInfo}]]></jobInfoProduction>";
                $xml .= '<services><service><![CDATA[Data_Format - jpg.]]></service></services>';
                $xml .= "</job>";
                File::put("$teambition_downloaded_folder/xml/{$task->prodtool_case_id}.xml", $xml);
                exec("cp $teambition_downloaded_folder/xml/{$task->prodtool_case_id}.xml " . config('s3br24.temp_xml'));
                $this->teambitionTask->where('id', $task->id)
                ->update(array(
                    'status' => 'copied',
                    'updated_at' => $updated_at
                ));

                $teambition_customfield_id = $teambition_organizations[$task['teambition_organization_id']]["customfields"]["case_id"];
                $update_task_api_url = $base_api_url . "/tasks/{$task['teambition_task_id']}/customfields?access_token=$access_token";
                $api_data = "_customfieldId={$teambition_customfield_id}&values[]={$task['prodtool_case_id']}";
                $this->sendTeambitionRequest($update_task_api_url, 'PUT', $api_data);
            }
        }
    }

    private function sendTeambitionRequest($url, $method = 'GET', $data = null)
    {
        $curl = curl_init();

        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
            ),
        );
        if ($method == 'POST' || $method == 'PUT') {
            $curl_options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/x-www-form-urlencoded';
        }

        curl_setopt_array($curl, $curl_options);
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
            return null;
        } else {
            $response = json_decode($response, true);
            if (isset($response['result'])) {
                return $response['result'];
            }
            return $response;
        }
    }

    private function downloadTeambitionFile($downloadUrl, $path = null, $log = null)
    {
        $cmd="aria2c --allow-overwrite=true --piece-length=100M --max-connection-per-server=8 --file-allocation=none --max-concurrent-downloads=8 --split=8 --log-level=notice --max-tries=12  --retry-wait=15 --log={$log}  --dir={$path} " . '"' . $downloadUrl . '"';
        exec($cmd . " > /dev/null &");
    }
}
