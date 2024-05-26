<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskDownload extends Model
{
    protected $connection = 'sqlite';    
    protected $table = 'tasks_downloads';
}
