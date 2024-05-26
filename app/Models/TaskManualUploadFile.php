<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskManualUploadFile extends Model
{
    protected $connection = 'sqlite';    
    protected $table = 'tasks_manual_uploads_files';
}
