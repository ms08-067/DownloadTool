<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskUploadView extends Model
{
    protected $connection = 'sqlite';    
    protected $table = 'v_upload_files';
}
