<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskDownloadFile extends Model
{
    protected $connection = 'sqlite';    
    protected $table = 'tasks_downloads_files';
}
