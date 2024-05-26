<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

/**
 * Class RolePermissionListinfoTransformer
 *
 * @author sigmoswitch
 * @package App\Transformers
 */
class RolePermissionListinfoTransformer extends TransformerAbstract
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
    	/**$locale = $this->fromRoutingsController['locale'];*/

    	$groups = $this->fromRoutingsController['company_position'];
    	$company_permissions = $this->fromRoutingsController['company_permissions'];
    	$rolehaspermissions = $this->fromRoutingsController['rolehaspermission'];


    	$permission_name = strtolower(str_replace(' ', '_', $resource->permission));
    	$permission_parts = explode(' ', $resource->permission);
    	$newstring_to_output = '';
    	if(strpos($permission_parts[0], 'READ') !== false) {
    		$newstring_to_output = '<span style="font-weight: 900; font-size:18px" class="text-navy" data-permission="'.$permission_name.'">READ</span>';
    	}
    	if(strpos($permission_parts[0], 'WRITE') !== false) {
    		$newstring_to_output = '<span style="font-weight: 900; font-size:18px" class="text-danger" data-permission="'.$permission_name.'">WRITE</span>';
    	}

    	$section = '<span style="font-weight: 900; font-size:18px">'.$permission_parts[1].'</span>';

    	$arraytobereturned = [
    		'permission' => (string) $newstring_to_output,
    		'section' => (string) $section,
    		//'numbering' => (int) $resource->number,
    		//'actions' => $resource->actions,
    		//'last_updated_by' => (string) $resource->last_updated_by,
    		//'last_updated' => (string) $resource->last_updated
    		//'space' => '<span class="unselectable"></span>'
    	];

    	foreach ($groups as $company_position){

    		$should_be_checked_or_not = null;
    		$should_be_disabled_or_not = null;
    		/** if the role has the permission then checkbox = checked */
    		foreach($rolehaspermissions as $positionhaspermission){
    			if ($positionhaspermission['fk_role_id'] == $company_position['id']){
    				if($company_permissions[$positionhaspermission['fk_permission_id']]['name'] == $resource->permission){
    					$should_be_checked_or_not = 'checked';
    					$haystack = ['READ avatar', 'WRITE avatar', 'READ home', 'READ mydata', 'READ period_controls', 'READ download'];
    					if (in_array($resource->permission, $haystack)) {
    						$should_be_disabled_or_not = 'disabled';
    					}
    					break;
    				}
    			}
    		}

    		$column_name = strtolower(str_replace(' ', '_', $company_position['name']));
    		$arraytobereturned[$column_name] = '<input class="route_permission_checkbox" type="checkbox" name="edit_'.$column_name.'_'.$permission_name.'" '.$should_be_checked_or_not.' '.$should_be_disabled_or_not.'>';
    	}

    	return $arraytobereturned;
    }
}





