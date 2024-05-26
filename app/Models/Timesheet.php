<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Timesheet
 *
 * @author tolawho
 * @package App\Models
 */
class Timesheet extends Model
{
    /**protected $fillable = ['key', 'fk_user_id', 'record_date', 'fk_attendance_status_id', 'updated_at', 'created_at'];*/
    protected $guarded = ['id'];
    protected $table = 'timesheets_data';

    /**
     * Get all timesheet by month
     *
     * @author tolawho
     * @param $date
     */
    public function getByMonth($date)
    {
        $firstDayOfMonth = date('Y-m-01', strtotime($date));
        $lastDayOfMonth = date('Y-m-t', strtotime($date));
        return self::select('id', 'fk_user_id', 'record_date', 'fk_attendance_status_id')
        ->whereBetween('record_date', [$firstDayOfMonth, $lastDayOfMonth])
        ->get()->groupBy('fk_user_id')
        ->map(function ($ts) {
            return $ts->keyBy('record_date');
        })
        ->toArray();
    }

    /**
     * Get all timesheet by date range
     *
     * @author tolawho
     * @param $date
     */
    public function getByRange($range_start, $range_end)
    {
        return self::select('id', 'fk_user_id', 'record_date', 'fk_attendance_status_id')
        ->whereRaw("DATE(record_date) >= DATE('$range_start') AND DATE(record_date) <= DATE('$range_end')")->get()->groupBy('fk_user_id')->map(function ($ts) {
            return $ts->keyBy('record_date');
        })->toArray();
    }

    /**
     * Get all timesheet by year
     *
     * @param $date
     */
    public function getByYear($date)
    {
        $firstDayOfYear = date('Y-01-01', strtotime($date));
        $lastDayOfYear = date('Y-12-31', strtotime($date));
        return self::select('id', 'fk_user_id', 'record_date', 'fk_attendance_status_id')
        ->whereBetween('record_date', [$firstDayOfYear, $lastDayOfYear])
        ->get()->groupBy('fk_user_id')
        ->map(function ($ts) {
            return $ts->keyBy('record_date');
        })
        ->toArray();
    }

    /**
     * Get all List timesheet by userid
     *
     * @author sigmoswitch
     * @param $userid
     */
    public function getAllList($userid)
    {
        return self::select('id', 'fk_user_id', 'record_date', 'fk_attendance_status_id')->where('fk_user_id', $userid)->get()->groupBy('fk_user_id')
        ->map(function ($ts) {
            return $ts->keyBy('record_date');
        })
        ->toArray();
    }

    /**
     * Get all by multiple fields
     *
     * @author sigmoswitch
     * @param  string $field The field to search by
     * @param  mixed $value The field value
     * @return mixed
     */
    public function findByMultiple($field, $value, $field2, $value2)
    {
    	return self::where($field, $value)->where($field2, $value2)->get()->groupBy('fk_user_id')->toArray();
    }

    /**
     * Get all by multiple fields
     *
     * @author sigmoswitch
     * @param  string $field The field to search by
     * @param  mixed $value The field value
     * @return mixed
     */
    public function findByMultipleFirst($field, $value, $field2, $value2)
    {
    	return self::where($field, $value)->where($field2, $value2)->first()->toArray();
    }

}
