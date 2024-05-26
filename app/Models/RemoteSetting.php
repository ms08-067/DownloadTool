<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemoteSetting extends Model
{
    protected $connection = 'sqlite';
    protected $table = 'remote_settings';

    public static function getValueByCode($code)
    {
        $setting = RemoteSetting::where('code', $code)->first();
        return isset($setting->value) ? $setting->value : null;
    }
}
