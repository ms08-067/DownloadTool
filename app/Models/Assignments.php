<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignments extends Model
{
    protected $connection = 'sqlite';    
    protected $table = 'assignments';
    public $timestamps = false;
}
