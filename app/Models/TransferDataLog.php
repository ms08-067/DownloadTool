<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferDataLog extends Model
{
    protected $connection = 'sqlite';    
    protected $table = 'transfer_data_logs';
    public $timestamps = false;

    public static function writeLog($case_id, $action, $total_file, $total_data)
    {
        $created = date('Y-m-d H:i:s');
        return TransferDataLog::insert(compact('case_id', 'action', 'total_file', 'total_data', 'created'));
    }
}
