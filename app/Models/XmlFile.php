<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XmlFile extends Model
{
    protected $connection = 'sqlite';    
    protected $table = 'xmlfiles';
    public $timestamps = false;
}
