<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemoteQueueActionFile extends Model
{
    protected $connection = 'sqlite';
    protected $table = 'queue_action_files';

    public function task()
    {
        return $this->belongsTo(Task::class, 'prodtool_task_id', 'id');
    }
}
