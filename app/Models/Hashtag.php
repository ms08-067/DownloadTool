<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Hashtag
 *
 * @author sigmoswitch
 * @package App\Models
 */
class Hashtag extends Model
{
    protected $guarded = ['id'];


    /**
     * Get all hashtags
     *
     * @author sigmoswitch
     * @return mixed
     */
    public function getAll()
    {
        return self::select('*')->get()->keyBy('id')->toArray();
    }
}
