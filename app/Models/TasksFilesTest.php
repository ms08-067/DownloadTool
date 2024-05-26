<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Class TasksFilesTest
 * @package App\Model
 *
 * @author anhlx412@gmail.com
 */
class TasksFilesTest extends Model
{
    protected $connection = 'sqlite';
    protected $table = 'tasks_files_test';

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
     * Get upload job by upload type and limit
     *
     * @param string $type
     * @return \Illuminate\Support\Collection
     *
     * @author anhlx412@gmail.com
     */
    public function getJobUploadByType($type = _SERVER_GERMANY) {
        $count = $this->getCountProcessInQueueByType($type);

        if ($count >= $this->limit) {
            $limit = 0;
        } else {
            $limit = $this->limit - $count;
        }

        $paths = \DB::table('prodtool_remote.tasks_files_test')
        ->select(
            'prodtool_remote.tasks_files_test.*',
            'prodtool_remote.tasks_ready_folders.folder',
            'prodtool_1910.customers.name',
            'prodtool_1910.customers.upload_type',
            'prodtool_1910.customers.upload_protocol',
            'prodtool_1910.customers.upload_username',
            'prodtool_1910.customers.upload_password',
            'prodtool_1910.customers.upload_host',
            'prodtool_1910.customers.upload_destination'
        )
        ->join('prodtool_1910.customers', 'prodtool_1910.customers.id', '=', 'prodtool_remote.tasks_files_test.customer_id')
        ->leftJoin('prodtool_remote.tasks_ready_folders', 'prodtool_remote.tasks_ready_folders.task_id', '=', 'prodtool_remote.tasks_files_test.task_id')
        ->where('prodtool_remote.tasks_files_test.state', $this->UPLOAD_STATE_NEW)
        ->where('prodtool_1910.customers.upload_type', $type)
        ->where('prodtool_remote.tasks_files_test.type', 'file')
        ->where('prodtool_remote.tasks_ready_folders.folder', '<>' , '')
        ->orderBy('prodtool_remote.tasks_files_test.order_upload', 'desc')
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
    public function getCountProcessInQueueByType($type = _SERVER_GERMANY) {
        $count = \DB::table('prodtool_remote.tasks_files_test')
        ->select(
            'prodtool_remote.tasks_files_test.id'
        )
        ->join('prodtool_1910.customers', 'prodtool_1910.customers.id', '=', 'prodtool_remote.tasks_files_test.customer_id')
        ->where('prodtool_remote.tasks_files_test.state', $this->UPLOAD_STATE_QUEUE)
        ->where('prodtool_1910.customers.upload_type', $type)
        ->count();

        return $count;
    }

    /**
     * @param $id
     * @param string $state
     * @param bool $exists
     * @return mixed
     *
     * @author anhlx412@gmail.com
     */
    public function updateStateById($id, $state = 'uploaded', $exists = true) {
        $data = [];

        if (!$exists) {
            $data = ['is_exists' => 0];
        }

        return self::where('id', $id)
        ->update(array_merge([
            'state' => $state,
            'time' => Carbon::now()->toDateTimeString()
        ], $data));
    }
}
