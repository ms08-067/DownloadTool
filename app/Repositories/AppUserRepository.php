<?php

namespace App\Repositories;

use App\Models\AppUser;

use App\Repositories\EmployeeRepository;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Helpers;
use Exception;
use Session;
use Debugbar;

/**
 * Class AppUserRepository
 *
 * @author sigmoswitch
 * @package App\Repositories
 */
class AppUserRepository extends Repository
{
    /**
     * @var AppUser
     */
    protected $appuser;

    /**
     * @var EmployeeRepository
     */
    protected $employeeRepo;

    /**
     * Create a new repository instance.
     * @param AppUser $appuser
     */
    public function __construct(
    	AppUser $appuser,
    	EmployeeRepository $employeeRepo
    ){
    	$this->appuser = $appuser;
    	$this->employeeRepo = $employeeRepo;
    }

    /**
     * Get all profile details of all employees
     *
     * @author sigmoswitch
     * @return mixed
     */
    public function getAll()
    {
    	return $this->appuser->getAll();
    	//
    }

    /** Get record by app user user_id
     *
     * @param $UserId
     * @return mixed
     */
    public function getByUserId($UserId)
    {
        return $this->appuser->getByUserId($UserId);
    }

    /**
     *
     */
    public function checkAuthUserDetails()
    {
        $auth_username = Auth::user()->username;
        /** have to find their details from the employee table and then get from the groups. */
        $auth_user = $this->employeeRepo->findByLdapUsername($auth_username);
        /** this part is important that their ldap username matches the username column */
        return $auth_user;
    } 

    /**
     *
     */
    public function getAuthUserDetails()
    {
        $auth_username = Auth::user()->username;
        /** have to find their details from the employee table and then get from the groups. */
        $auth_user = $this->employeeRepo->findByLdapUsername($auth_username);
        $auth_user = $this->getByUserId($auth_user->user_id);
        
        /**$auth_user_role = $auth_user->role;*/
        /**$auth_user_id = $auth_user->user_id;*/
        /**$auth_user_permissions = $auth_user->permissions;*/
        $auth_user->username = $auth_username;

        return $auth_user;
    }       
}
