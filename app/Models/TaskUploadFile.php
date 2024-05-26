<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskUploadFile extends Model
{
    protected $connection = 'sqlite';    
    protected $table = 'tasks_uploads_files';
}
