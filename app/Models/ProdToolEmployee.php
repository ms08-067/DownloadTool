<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProdToolEmployee
 * @author tolawho
 * @package App\Models
 */
class ProdToolEmployee extends Model
{
	protected $connection = 'prodtool';
	protected $table = 'prodtool_1910.users';
	protected $hidden = ['password'];
	protected $guarded = ['id'];
	public $timestamps = false;

    /**
     * Get the group that owns the employee.
     * @author tolawho
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
    	return $this->belongsTo('App\Models\Group');
    }

    /**
     * Get the office that where the employee work at.
     * @author tolawho
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function office()
    {
    	return $this->belongsTo('App\Models\Office');
    }

    /**
     * Get all user from prodtool
     *
     * @author tolawho
     * @return array
     */
    public function getAll()
    {
    	return self::select('id', 'username', 'fullname', 'group_id', 'email')->whereNotIn('group_id', config('base.exclude.group'))->whereNotIn('id', config('base.exclude.user'))->get()->keyBy('id')->toArray();
    }

    /**
     * Get entirely all user from prodtool
     *
     * @author sigmoswitch
     * @return array
     */
    public function getEntirelyAll()
    {
    	return self::select('*')->whereNotIn('id', [472, 633])->get()->keyBy('id')->toArray();
    }

    /**
     * Get all valid employee not include $exclude member
     *
     * @author tolawho
     * @param array $exclude
     * @return mixed
     */
    public function getValidEmployees($exclude = [])
    {
    	$query = self::whereNotIn('group_id', config('base.exclude.group'))->whereNotIn('id', config('base.exclude.user'));
    	if ($exclude) {
    		$query->whereNotIn('id', $exclude);
    	}
    	return $query->get()->keyBy('id')->toArray();
    }

    /**
     * Get all employee reguardless of date or position or etc.
     *
     * @author sigmoswitch
     * @param string $userid
     * @return mixed
     */
    public function getAllEmployeesReguardlessOfGrouporUser($userid)
    {
    	return self::select('id', 'username', 'fullname', 'group_id', 'email', 'created')->where('id', $userid)->get()->keyBy('id')->toArray();
    }

    /**
     * Find an employee by username
     *
     * @author tolawho
     * @param string $username
     * @return mixed
     */
    public function getByUsername($username)
    {
    	return self::select('id', 'username', 'fullname', 'group_id', 'email')
    	->where('username', $username)->first();
    }

    /**
     * Scope a query to only include active users.
     *
     * @author tolawho
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
    	return $query->where('status', 1);
    }

    /**
     * Scope a query to only include inactive users.
     *
     * @author tolawho
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInActive($query)
    {
    	return $query->where('status', '0');
    }

    /**
     * Scope a query to only include staff.
     *
     * @author tolawho
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStaff($query)
    {
    	return $query->whereNotIn('group_id', config('base.exclude.group'))
    	->whereNotIn('id', config('base.exclude.user'));
    }
}
