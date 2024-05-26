<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $connection = 'sqlite';
    protected $table = 'tasks';
    public $timestamps = false;

    /**
     * Update process
     *
     * @param $id
     * @param bool $update
     * @param array $delete
     * @return mixed
     *
     * @author anhlx412@gmail.com
     */
    public function updateProcess($id, $update = true, $delete = []) {
        $data = ['pic_processed' => \DB::raw('pic_processed + 1')];

        if ($update) {
            $data = array_merge($data, ['pic_uploaded' => \DB::raw('pic_uploaded + 1')]);
        }

        if (!empty($delete)) {
            $str_deleted = $delete['str_deleted'];
            $data = array_merge($data, ['list_deleted' => \DB::raw('CONCAT ( IFNULL(tasks.list_deleted, "") , "'. $str_deleted .'") ')]);
        }

        return self::where('id', $id)
        ->update($data);
    }

    /**
     * Calculator state job.
     *
     * @return \Illuminate\Support\Collection
     *
     * @author anhlx412@gmail.com
     */
    public function calculatorStateUpload() {
        $jobs = \DB::table('prodtool_1910.tasks as Task')
        ->join('prodtool_remote.tasks_files as TaskFile', 'TaskFile.task_id', '=', 'Task.id')
        ->whereRaw('Task.status = 4 AND Task.is_upload = 0 AND TaskFile.type = "file"')
        ->selectRaw(
            'Task.id,
            Task.case_id,
            COUNT( TaskFile.id ) AS total_file,
            ( SELECT count( id ) FROM prodtool_remote.tasks_files WHERE type = "file" AND state = "uploaded" AND task_id = `Task`.`id` ) AS uploaded_file,
            ( SELECT count( id ) FROM prodtool_remote.tasks_files WHERE type = "file" AND state = "error" AND task_id = `Task`.`id` ) AS error_file,
            ( SELECT count( id ) FROM prodtool_remote.tasks_files WHERE type = "file" AND state = "deleted" AND task_id = `Task`.`id` ) AS deleted_file')
        ->groupBy('Task.id')
        ->get();

        return $jobs;
    }
}
