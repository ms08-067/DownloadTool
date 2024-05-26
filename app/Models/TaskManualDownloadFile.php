<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskManualDownloadFile extends Model
{
    protected $connection = 'sqlite';    
    protected $table = 'tasks_manual_downloads_files';
}
