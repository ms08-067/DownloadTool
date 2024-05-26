<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Group
 *
 * @author tolawho
 * @package App\Models
 */
class Group extends Model
{

    protected $guarded = ['id'];
    /**protected $connection = 'prodtool';*/
    protected $table = 'company_positions';

    /**
     * Get all groups with exclude group
     *
     * @author tolawho
     * @return mixed
     */
    public function getAll()
    {
        return self::select('id', 'name')->whereNotIn('id', config('base.exclude.group'))->orderBy('name')->get()->keyBy('id')->toArray();
    }

    /**
     * Get all groups with exclude group
     *
     * @author tolawho
     * @return mixed
     */
    public function getAllActive()
    {
        return self::select('id', 'name')->where('active', 1)->whereNotIn('id', config('base.exclude.group'))->orderBy('name')->get()->keyBy('id')->toArray();
    }

    /**
     * Get all groups
     *
     * @author sigmoswitch
     * @return mixed
     */
    public function getEntirelyAll()
    {
        return self::select('id', 'name')->orderBy('name')->get()->keyBy('id')->toArray();
    }   

    /**
     * Get list PositionType
     *
     * @return mixed
     */
    public function getList()
    {
        return self::all()->keyBy('id');
    }

    /**
     * Get the employees for the group from the employee tool
     * @author tolawho
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function employees()
    {
        return $this->hasMany('App\Models\Employee');
    }

    /**
     * Scope a query to only include staff's group.
     *
     * @author tolawho
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStaff($query)
    {
        return $query->whereNotIn('id', config('base.exclude.group'));
    }
}
