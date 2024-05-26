<?php

namespace App\Http\Controllers;

use App\Repositories\GroupRepository;
use App\Repositories\TeamRepository;
use App\Repositories\RoleHasPermissionRepository;
use App\Repositories\PermissionRepository;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Helpers;
use Session;
use Validator;
use DateTime;

/**
 * Class AjaxRoutePermissionTabsController
 *
 * @author sigmoswitch
 * @package App\Http\Controller
 */
class AjaxRoutePermissionTabsController extends Controller
{
    /**
     * @var GroupRepository
     */
    protected $groupRepo;

    /**
     * @var TeamRepository
     */
    protected $teamRepo;

    /**
     * @var RoleHasPermissionRepository
     */
    protected $rolehaspermissionRepo;

    /**
     * @var PermissionRepository
     */
    protected $permissionRepo;

    /**
     * create a new controller instance.
     *
     * @param GroupRepository $grouprepo
     * @param TeamRepository $teamrepo
     * @param RoleHasPermissionRepository $rolehaspermissionRepo
     * @param PermissionRepository $permissionRepo
     */
    public function __construct(
    	TeamRepository $teamRepo,
    	GroupRepository $groupRepo,
    	RoleHasPermissionRepository $rolehaspermissionRepo,
    	PermissionRepository $permissionRepo
    ) {
    	$this->groupRepo = $groupRepo;
    	$this->teamRepo = $teamRepo;
    	$this->rolehaspermissionRepo = $rolehaspermissionRepo;
    	$this->permissionRepo = $permissionRepo;
    }

    /**
     * Send RoleRositionInfo Tab HTML via Ajax
     *
     * @author sigmoswitch
     * @return \Illuminate\View\View
     */
    public function ajaxRoleRositionInfo_HTML()
    {
    	/** columns to be the roles and the rows to be the permissions */
    	/** obviously the columns will be dynamic in which case this is going to be fine if we make the html what we need */
    	/** then let the javascript datatables plugin handle the columns also and checkboxes */

    	$groups = $this->groupRepo->getList();

    	$this->data = compact('groups');
    	$this->js = compact([]);

    	return $this->render('permission._roleposition');
    }

    /**
     * Send PermissionsInfo Tab HTML via Ajax
     *
     * @author sigmoswitch
     * @return \Illuminate\View\View
     */
    public function ajaxPermissionsInfo_HTML()
    {
    	//the columns are the roles?
    	// the rows are the permissions..

    	// and it needs to be dynamic.. 

    	$this->data = compact([]);
    	$this->js = compact([]);

    	return $this->render('permission._permissions');
    }

    /**
     * Get RoleRositionInfo Table via Ajax
     *
     * @author sigmoswitch
     * @return \Illuminate\View\View
     */
    public function getRoleRositionInfo_TABLE(Request $request)
    {
    	$locale = app()->getLocale();
    	$role_permission_list_view = DB::table('v_role_permission')->when($request->getting_columns, function($query) {
    		return $query->limit(1);
    	})->get()->toArray();

    	$groups = $this->groupRepo->getList();
    	$rolehaspermissions = $this->rolehaspermissionRepo->getAll();
    	$permissions = $this->permissionRepo->getAll();    	

    	$counting_number = 1;
    	foreach ($role_permission_list_view as &$role_permission) {
    		$role_permission->number = $counting_number;
    		$counting_number++;
    	}

    	return dataTables($role_permission_list_view)
    	->setTransformer(new \App\Transformers\RolePermissionListinfoTransformer(['locale' => $locale, 'company_position' => $groups, 'rolehaspermission' => $rolehaspermissions, 'company_permissions' => $permissions]))
    	->make(true); 
    }

    /**
     * Get PermissionsInfo Table via Ajax
     *
     * @author sigmoswitch
     * @return \Illuminate\View\View
     */
    public function getPermissionsInfo_TABLE()
    {
    	$locale = app()->getLocale();
    	$permissions_list_view = DB::table('v_permissions')->get()->toArray();
    	$counting_number = 1;
    	foreach ($permissions_list_view as &$permission) {
    		$permission->number = $counting_number;
    		$counting_number++;
    	}

    	return dataTables($permissions_list_view)
    	->setTransformer(new \App\Transformers\PermissionsListinfoTransformer(['locale' => $locale]))
    	->make(true); 
    }

}
