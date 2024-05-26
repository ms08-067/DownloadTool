<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SiteDeveloper
 *
 * @author sigmoswitch
 * @package App\Models
 */
class SiteDeveloper extends Model
{
	protected $table = 'site_developers';

    /**
     * Get all site developer usernames
     *
     * @author sigmoswitch
     * @return mixed
     */
    public function getAll()
    {
    	return self::select('*')->get()->toArray();
    }

    /**
     * Get all site developer usernames
     *
     * @author sigmoswitch
     * @return mixed
     */
    public function getSiteDeveloperArray()
    {
    	$array = self::select('fk_user_id')->get()->keyBy('fk_user_id')->toArray();
	   	/**$array[BSX.BSX.BSV]['fk_user_id'] = BSX.BSX.BSV;*/

    	return $array;
    }    
}
