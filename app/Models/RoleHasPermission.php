<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleHasPermission extends Model
{
	protected $guarded = ['id'];
	
    /**
     * Get all roles which has permissions
     *
     * @author sigmoswitch
     * @return mixed
     */
    public function getAll()
    {
        return self::select('fk_permission_id', 'fk_role_id')->orderBy('fk_role_id')->get()->toArray();
    }
}
