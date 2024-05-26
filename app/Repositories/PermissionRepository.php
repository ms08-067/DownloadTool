<?php

namespace App\Repositories;

use App\Models\Permission;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Helpers;
use Exception;
use Session;
use Debugbar;

/**
 * Class PermissionRepository
 *
 * @author sigmoswitch
 * @package App\Repositories
 */
class PermissionRepository extends Repository
{
    /**
     * @var Permission
     */
    protected $permission;

    /**
     * Create a new repository instance.
     * @param Permission $appuser
     */
    public function __construct(
    	Permission $permission
    ){
    	$this->permission = $permission;
    }

    /**
     * Get all profile details of all employees
     *
     * @author sigmoswitch
     * @return mixed
     */
    public function getAll()
    {
    	return $this->permission->getAll();
    	//
    }

    /** Get permission by id
     *
     * @param $UserId
     * @return mixed
     */
    public function getById($id)
    {
        return $this->permission->getById($id);
    }
    
}
