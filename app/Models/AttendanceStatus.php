<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AttendanceStatus
 *
 * @author sigmoswitch
 * @package App\Models
 */
class AttendanceStatus extends Model
{
	protected $guarded = ['id'];

    /**
     * Get all timesheet record types
     * @author sigmoswitch
     * @return array
     */
    public function getAll()
    {
    	return self::select('id', 'name')->get()->keyBy('id')->toArray();
    }

    /**
     * Get all timesheet record types every column
     * @author sigmoswitch
     * @return array
     */
    public function getEntirelyAll()
    {
    	return self::select('*')->orderBy('status_grouping', 'asc')->orderBy('payment_percentage', 'asc')->orderBy('name', 'asc')->get()->keyBy('id')->toArray();
    }


    /**
     * Get all timesheet record types
     * @author sigmoswitch
     * @return array
     */
    public function getAllwithValue()
    {
    	return self::select('id', 'name', 'payment_percentage', 'desc_vn', 'desc_en', 'color')->orderBy('status_grouping', 'asc')->orderBy('payment_percentage', 'asc')->orderBy('name', 'asc')->get()->toArray();
    }

    /**
     * Get all timesheet record types
     * @author sigmoswitch
     * @return array
     */
    public function getAllwithValueKeyById()
    {
    	return self::select('id', 'name', 'payment_percentage', 'desc_vn', 'desc_en', 'color')->orderBy('status_grouping', 'asc')->orderBy('payment_percentage', 'asc')->orderBy('name', 'asc')->get()->keyBy('id')->toArray();
    }

    /**
     * Get timesheet record id of blank
     * @author sigmoswitch
     * @return array
     */
    public function getIdofBlank()
    {
    	return self::select('id', 'name')->where('name', '&nbsp;')->get()->keyBy('id')->toArray();
    }

    /**
     * Get timesheet record id of blank
     * @author sigmoswitch
     * @return array
     */
    public function getIdof_f()
    {
    	return self::select('id', 'name')->where('name', 'f7')->get()->keyBy('id')->toArray();
    }

}
