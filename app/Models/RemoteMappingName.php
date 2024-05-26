<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemoteMappingName extends Model
{
    protected $connection = 'sqlite';
    protected $table = 'mapping_names';
}