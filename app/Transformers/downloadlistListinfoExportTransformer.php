<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

/**
 * Class downloadlistListinfoExportTransformer
 *
 * @author sigmoswitch
 * @package App\Transformers
 */
class downloadlistListinfoExportTransformer extends TransformerAbstract
{
    /**
     * Create a new transformer instance.
     *
     * @param $params
     */
    public function __construct($arrayfromroutings = [])
    {
    	$this->fromRoutingsController = $arrayfromroutings;
    }

    /**
     * @author sigmoswitch
     * @param $resource
     * @return array
     */
    public function transform($resource)
    {
    	$locale = $this->fromRoutingsController['locale'];


    	dd($resource);

    	switch ($locale) {
    		case 'en':
    		$format = CASE_EN_DATE_FORMAT;
    		$last_updated_format = CASE_EN_LAST_UPDATED_DATE_FORMAT;
    		break;
    		case 'de':
    		$format = CASE_DE_DATE_FORMAT;
    		$last_updated_format = CASE_DE_LAST_UPDATED_DATE_FORMAT;
    		break;
    		case 'vi':
    		$format = CASE_VI_DATE_FORMAT;
    		$last_updated_format = CASE_VI_LAST_UPDATED_DATE_FORMAT;
    		break;
    		default:
    		$format = CASE_DEFAULT_DATE_FORMAT;
    		$last_updated_format = CASE_DEFAULT_LAST_UPDATED_DATE_FORMAT;
    	}

    	if(isset($resource->calendar_date)){
    		$calendar_date = Carbon::createFromFormat('Y-m-d', $resource->calendar_date);
    		$calendar_date = Carbon::parse($calendar_date)->format($format);
    	}else{
    		$calendar_date = '';
    	}

    	if(isset($resource->last_updated)){
    		$last_updated = Carbon::createFromFormat('Y-m-d H:i:s', $resource->last_updated);
    		$last_updated = Carbon::parse($last_updated)->format($last_updated_format);
    	}else{
    		$last_updated = '';
    	}


    	return [
    		'numbering' => $resource->number,
    		'user_id' => $resource->fk_user_id,
    		'fullname' =>   $resource->fullname,
    		'groupname' =>  $resource->position,
    		'teamnames' =>  $resource->team,
    		"calendar_date" => $calendar_date,
    		'request_number' => str_pad($resource->id, 4, "0", STR_PAD_LEFT),
    		"advance_amount" => $resource->advance_amount,
    		"advance_reason" => $resource->advance_reason,
    		"advance_period" => $resource->advance_period,
    		'last_updated_by' => $resource->last_updated_by,
    		'last_updated' => $last_updated
    	];    	
    }
}
