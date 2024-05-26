<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Br24Done extends Model
{
    protected $connection = 'sqlite';    
    protected $table = 'br24dones';
    public $timestamps = false;

    /**
     * @param $taskId
     * @param string $readyPath
     * @param int $status: 2 = uploaded; 3 = deleted
     * @return mixed
     *
     * @author anhlx412@gmail.com
     */
    public function updateOttoUpload($taskId, $readyPath = '', $status = 2) {
        return self::where('task_id', $taskId)
        ->where('ready_path', $readyPath)
        ->update([
            'is_otto_sub_task_upload' => $status
        ]);
    }
}
