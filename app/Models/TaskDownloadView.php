<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskDownloadView extends Model
{
    protected $connection = 'sqlite';    
    protected $table = 'v_download_files';
}
