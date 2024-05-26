<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Class Permission
 *
 * @author sigmoswitch
 * @package App\Models
 */
class Permission extends Model
{
	protected $guarded = ['id'];

    /**
     * Get all permissions details
     *
     * @author sigmoswitch
     * @return mixed
     */
    public function getAll()
    {
        return self::select('*')->get()->keyBy('id')->toArray();
    }

    /**
     * Get a permission using id
     *
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return self::where('id', $id)->get()->first();
    }  	
}
