<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppUser extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $protected = ['id'];
    protected $table = 'v_app_user';

    /**
     * Get employee profile detail
     *
     * @author sigmoswitch
     * @return mixed
     */
    public function getAll()
    {
        return self::select('*')->get()->keyBy('user_id')->toArray();
    }

    /**
     * Get app user by employee user_id
     *
     * @param $userId
     * @return mixed
     */
    public function getByUserId($userId)
    {
        return self::where('user_id', $userId)->get()->first();
    }    
}
