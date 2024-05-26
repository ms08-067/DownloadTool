<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskManualDownloadView extends Model
{
    protected $connection = 'sqlite';    
    protected $table = 'v_manual_download_files';
}
