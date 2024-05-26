<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskManualUploadView extends Model
{
    protected $connection = 'sqlite';    
    protected $table = 'v_manual_upload_files';
}
