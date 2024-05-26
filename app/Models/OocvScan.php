<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OocvScan extends Model
{
    protected $connection = 'sqlite';    
    protected $table = 'oocv_scan';
}
