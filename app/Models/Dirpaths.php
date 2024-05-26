<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dirpaths extends Model
{
    protected $connection = 'sqlite';    
    protected $table = 'dirpaths';
    public $timestamps = false;
}
