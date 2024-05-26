<?php

namespace App\Policies;

use App\Repositories\AppUserRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\SiteDeveloperRepository;

use Illuminate\Auth\Access\HandlesAuthorization;

class UserCanDevelopPolicy
{

	use HandlesAuthorization;

	/**
     * @var AppUserRepository
     */
	protected $appuserRepo;
	
	/**
     * @var EmployeeRepository
     */
	protected $employeeRepo;

	/**
     * @var SiteDeveloperRepository
     */
	protected $sitedeveloperRepo;

    /**
     * Create a new controller instance.
     *
     * @param AppUserRepository $appuserRepo
     * @param EmployeeRepository $userRepo
     * @param SiteDeveloperRepository $sitedeveloperRepo
     * @return void
     */    
    public function __construct(
    	AppUserRepository $appuserRepo,
    	EmployeeRepository $employeeRepo,
    	SiteDeveloperRepository $sitedeveloperRepo
    ){
    	$this->appuserRepo = $appuserRepo;
    	$this->employeeRepo = $employeeRepo;
    	$this->sitedeveloperRepo = $sitedeveloperRepo;
    }

    /**
     * Determine whether the logged in user can view developer stuff
     *
     * @author sigmoswitch
     * @return bool
     */
    public function develop()
    {

        /**dd('inside the UserCanDevelopPolicy');*/
    	/**$auth_user_role = $this->appuserRepo->getAuthUserDetails()->role;*/
    	$auth_user_id = $this->appuserRepo->getAuthUserDetails()->user_id;
    	/**$auth_user_permissions = $this->appuserRepo->getAuthUserDetails()->permissions;*/
    	/**$auth_user_username = $this->appuserRepo->getAuthUserDetails()->username;*/

    	$sitedevelopers = $this->sitedeveloperRepo->getSiteDeveloperArray();

    	return array_key_exists($auth_user_id, $sitedevelopers);

    }      
}
