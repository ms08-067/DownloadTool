<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemoteJob extends Model
{
    protected $connection = 'sqlite';
    protected $table = 'jobs';
}
