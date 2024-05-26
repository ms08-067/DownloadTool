<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskUpload extends Model
{
    protected $connection = 'sqlite';    
    protected $table = 'tasks_uploads';
}
