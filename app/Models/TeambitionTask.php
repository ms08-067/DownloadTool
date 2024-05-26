<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeambitionTask extends Model
{
    protected $connection = 'teambition';
    protected $table = 'tasks';
    
    public function job_type()
    {
        return $this->hasOne('App\Model\TeambitionJobType', 'teambition_id', 'teambition_type_id');
    }
}
