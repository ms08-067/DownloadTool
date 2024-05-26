<?php

namespace App\Models;

use App\Jobs\UploadToServerGermany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Class TasksFiles
 * @package App\Model
 *
 * @author anhlx412@gmail.com
 */
class TasksFiles extends Model
{
    protected $connection = 'sqlite';
    protected $table = 'tasks_files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'task_id',
        'case_id',
        'local',
        'live',
        'state',
        'time',
        'file_name',
        'size',
        'file_path',
        'upload_id',
        'type',
        'order_upload',
        'j_key',
        'uuid',
        'is_exists',
        'changed_split',
        'error_number',
        'pid',
        'folder',
        'customer_id',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;

    
    protected $UPLOAD_STATE_QUEUE = 'queue';
    protected $UPLOAD_STATE_NEW = 'new';
    protected $UPLOAD_STATE_ERROR = 'error';
    protected $limit = 10;

    /**
     * Scan ready folder & insert into tasks_files table
     *
     * @param $task
     * @param $s3Br24Config
     * @param $log
     * @return mixed
     *
     * @author anhlx412@gmail.com
     */
    public function setDataTaskFileByScanReadyFolder($task, $s3Br24Config, $log)
    {
        writeLog($log, 'Task ID: ' . $task->id . ' --- Case ID: ' . $task->case_id);
        /**Insert tasks_files table*/
        $task->is_spliting = 1;
        $task->save();

        if (self::where('task_id', $task->id)->exists()) {
            self::where('task_id', $task->id)->delete();
            TasksReadyFolders::where('task_id', $task->id)->delete();
        }

        /**create all forder*/
        $directories = Storage::disk('jobfolder')->allDirectories($task->case_id . '/ready');
        $data = [];
        foreach ($directories as $d) {
            $filePath = str_replace($task->case_id . '/ready/', "", $d);

            $data[] = [
                'task_id' => $task->id,
                'case_id' => $task->case_id,
                'jobIdTitle' => $task->jobIdTitle,
                'local' => $s3Br24Config['job_folder'] . $d,
                'live' => '',
                'state' => 'new',
                'time' => Carbon::now()->toDateTimeString(),
                'file_name' => '',
                'size' => 0,
                'file_path' => $filePath,
                'customer_id' => $task->customer_id,
                'type' => 'folder'
            ];
        }

        /**scan files*/
        $files = Storage::disk('jobfolder')->allFiles($task->case_id . '/ready');
        $number = 0;
        foreach ($files as $file) {
            $extension = \File::extension($file);
            $forbidden_extensions = ar_file_work();
            if (!is_file_working($extension, $forbidden_extensions)) {
                continue;
            }

            $filePath = str_replace($task->case_id . '/ready/', "", $file);
            $data[] = [
                'task_id' => $task->id,
                'case_id' => $task->case_id,
                'jobIdTitle' => $task->jobIdTitle,
                'local' => $s3Br24Config['job_folder'] . $file,
                'live' => '',
                'state' => 'new',
                'time' => Carbon::now()->toDateTimeString(),
                'file_name' => basename($file),
                'size' => ceil(filesize($s3Br24Config['job_folder'] . $file) / 1024 / 1024),
                'file_path' => $filePath,
                'customer_id' => $task->customer_id,
                'type' => 'file'
            ];

            $number++;
            /** insert part with 500 record*/
            if ($number > 500) {
                $number = 0;
                self::insert($data);
                $data = [];
            }
        }

        if (!empty($data)) {
            self::insert($data);
        }

        $task->is_spliting = 2;
        $task->save();

        exec("chmod -R 777 " . $s3Br24Config['job_folder'] . $task->case_id);

        return $task;
    }

    /**
     * Get upload job by upload type and limit
     *
     * @param string $type
     * @return \Illuminate\Support\Collection
     *
     * @author anhlx412@gmail.com
     */
    public function getJobUploadByType($type = _SERVER_GERMANY)
    {
        $count = $this->getCountProcessInQueueByType($type);

        if ($count >= $this->limit) {
            $limit = 0;
        } else {
            $limit = $this->limit - $count;
        }

        $paths = \DB::table('prodtool_remote.tasks_files')
        ->select(
            'prodtool_remote.tasks_files.*',
            'prodtool_remote.tasks_ready_folders.folder',
            'prodtool_1910.customers.name',
            'prodtool_1910.customers.upload_type',
            'prodtool_1910.customers.upload_protocol',
            'prodtool_1910.customers.upload_username',
            'prodtool_1910.customers.upload_password',
            'prodtool_1910.customers.upload_host',
            'prodtool_1910.customers.upload_destination'
        )
        ->join('prodtool_1910.customers', 'prodtool_1910.customers.id', '=', 'prodtool_remote.tasks_files.customer_id')
        ->leftJoin('prodtool_remote.tasks_ready_folders', 'prodtool_remote.tasks_ready_folders.task_id', '=', 'prodtool_remote.tasks_files.task_id')
        ->where('prodtool_remote.tasks_files.state', $this->UPLOAD_STATE_NEW)
        ->where('prodtool_1910.customers.upload_type', $type)
        ->where('prodtool_remote.tasks_files.type', 'file')
        ->where('prodtool_remote.tasks_ready_folders.folder', '<>', '')
        ->orderBy('prodtool_remote.tasks_files.order_upload', 'desc')
        ->orderBy('prodtool_remote.tasks_files.id', 'asc')
        ->offset(0)
        ->limit($limit)
        ->get();

        return $paths;
    }

    /**
     * Count job in queue by upload type
     *
     * @param $type
     * @return int
     *
     * @author anhlx412@gmail.com
     */
    public function getCountProcessInQueueByType($type = _SERVER_GERMANY)
    {
        $count = \DB::table('prodtool_remote.tasks_files')
        ->select(
            'prodtool_remote.tasks_files.id'
        )
        ->join('prodtool_1910.customers', 'prodtool_1910.customers.id', '=', 'prodtool_remote.tasks_files.customer_id')
        ->where('prodtool_remote.tasks_files.state', $this->UPLOAD_STATE_QUEUE)
        ->where('prodtool_1910.customers.upload_type', $type)
        ->count();

        return $count;
    }

    /**
     * @param $id
     * @param string $state
     * @param bool $exists
     * @param int $is_move_final
     * @return mixed
     *
     * @author anhlx412@gmail.com
     */
    public function updateStateById($id, $state = 'uploaded', $exists = true, $is_move_final = 0)
    {
        $data = [];

        if (!$exists) {
            $data = ['is_exists' => 0];
        }

        if ($is_move_final != 0) {
            $data = array_merge($data, [
                'is_move_final' => $is_move_final
            ]);
        }

        return self::where('id', $id)
        ->update(array_merge([
            'state' => $state,
            'time' => Carbon::now()->toDateTimeString()
        ], $data));
    }

    public function oocvCheckFolderToMove()
    {
        $data = \DB::table('prodtool_remote.tasks_files as tf')
        ->selectRaw('
                    tf.id,
                    tf.task_id,
                    tf.case_id,
                    c.name,
                    c.upload_type,
                    c.upload_protocol,
                    c.upload_username,
                    c.upload_password,
                    c.upload_host,
                    c.upload_destination,
                    SUBSTRING_INDEX( tf.file_path, "/", 1 ) AS folder,
                    (select count(1) from prodtool_remote.tasks_files t where t.task_id = tf.task_id and (t.state = "waiting_move" OR t.state = "uploaded") and t.file_path like CONCAT("%",SUBSTRING_INDEX( tf.file_path, "/", 1 ),"%")) as total_wait_move,
                    (select count(1) from prodtool_remote.tasks_files t where t.task_id = tf.task_id and t.file_path like CONCAT("%",SUBSTRING_INDEX( tf.file_path, "/", 1 ),"%")) as total_file_in_folder
                    ')
        ->join('prodtool_1910.customers as c', 'c.id', '=', 'tf.customer_id')
        ->where('tf.state', 'waiting_move')
        ->groupBy('tf.task_id', DB::raw('SUBSTRING_INDEX( tf.file_path, "/", 1 )'))
        ->get();

        $result = [];
        foreach ($data as $d) {
            $result[$d->folder]['task_id'] = $d->task_id;
            $result[$d->folder]['case_id'] = $d->case_id;
            $result[$d->folder]['total_wait_move'] = $d->total_wait_move;
            $result[$d->folder]['total_file_in_folder'] = $d->total_file_in_folder;
            $result[$d->folder]['c_name'] = $d->name;
            $result[$d->folder]['upload_type'] = $d->upload_type;
            $result[$d->folder]['upload_protocol'] = $d->upload_protocol;
            $result[$d->folder]['upload_username'] = $d->upload_username;
            $result[$d->folder]['upload_password'] = $d->upload_password;
            $result[$d->folder]['upload_host'] = $d->upload_host;
            $result[$d->folder]['upload_destination'] = $d->upload_destination;
        }

        return $result;
    }
}
