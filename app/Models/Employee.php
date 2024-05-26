<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Employee
 *
 * @author sigmoswitch
 * @package App\Models
 */
class Employee extends Model
{
	use SoftDeletes;

	protected $guarded = ['id'];
	protected $table = 'employees';

    /**protected $fillable = [''];*/

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
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
     * Get employee profile detail
     *
     * @author sigmoswitch
     * @return mixed
     */
	public function getAlluserid()
	{
		return self::select('user_id')->get()->keyBy('user_id')->toArray();
	}

    /**
     * Get employee profile detail
     *
     * @author sigmoswitch
     * @return mixed
     */
    public function getEntirelyAll()
    {
    	return self::select('user_id', 'created_at')->get()->keyBy('user_id')->toArray();
    }

    /**
     * Get employee profile detail reguardless of date or position or etc.
     *
     * @author sigmoswitch
     * @param string $userid
     * @return mixed
     */
    public function getAllList($userid)
    {
    	return self::select('*')->where('user_id', $userid)->get()->keyBy('user_id')->toArray();
    }

    /**
     * Get all valid employee not include $exclude member
     *
     * @author sigmoswitch
     * @param array $exclude
     * @return mixed
     */
    public function getValidEmployeesFromEmployeeProfileDetailsTable($exclude = [])
    {
    	$query = self::whereNotIn('fk_group_id', config('base.exclude.group'))->whereNotIn('user_id', config('base.exclude.user'));
    	if ($exclude) {
    		$query->whereNotIn('user_id', $exclude);
    	}
    	return $query->get()->keyBy('user_id')->toArray();
    }

    /**
     * Get ONE by multiple fields
     *
     * @author sigmoswitch
     * @param  string $field The field to search by
     * @param  mixed $value The field value
     * @return mixed
     */
    public function findByMultiple($field, $value, $field2, $value2)
    {
    	return self::where($field, $value)->where($field2, $value2)->first();
        /***/
    }

    /**
     * Get ONE by one fields
     *
     * @author sigmoswitch
     * @param  string $field The field to search by
     * @param  mixed $value The field value
     * @return mixed
     */
    public function findBy($field, $value)
    {
    	return self::where($field, $value)->first();
        /***/
    }

}
