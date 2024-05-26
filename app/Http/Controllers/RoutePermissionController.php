<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Repositories\AppUserRepository;
use App\Repositories\GroupRepository;
use App\Repositories\TeamRepository;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Route;

use App\Repositories\RoleHasPermissionRepository;
use App\Repositories\PermissionRepository;

use Carbon\Carbon;
use App\Helpers;

use Session;
use Exception;
use Debugbar;
use Validator;

/**
 * Class RoutePermissionController
 *
 * @author sigmoswitch
 * @package App\Http\Controller
 */
class RoutePermissionController extends Controller
{
    /**
     * @var AppUserRepository
     */
    protected $appuserRepo;

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
     * AjaxExample6Controller constructor.
     *
     * @param GroupRepository $groupRepo
     * @param TeamRepository $teamRepo
     * @param AppUserRepository $appuserRepo
     * @param RoleHasPermission $rolehaspermission
     * @param RoleHasPermissionRepository $rolehaspermissionRepo
     */
    public function __construct(
    	GroupRepository $groupRepo,
    	TeamRepository $teamRepo,
    	AppUserRepository $appuserRepo,
    	RoleHasPermissionRepository $rolehaspermissionRepo,
    	PermissionRepository $permissionRepo
    ) {
    	$this->teamRepo = $teamRepo;
    	$this->groupRepo = $groupRepo;
    	$this->appuserRepo = $appuserRepo;
    	$this->rolehaspermissionRepo = $rolehaspermissionRepo;
    	$this->permissionRepo = $permissionRepo;
    }

    /**
	 * Show Route Permissions Matrix Table
	 *
     * @author sigmoswitch
     * @return \Illuminate\View\View
	 */
    public function index()
    {
    	$locale = app()->getLocale();
    	$period = getPeriod();
    	
    	$sections = config('base.sections');
    	/**$route = url()->current();*/
    	$auth_user_permissions = $this->appuserRepo->getAuthUserDetails()->permissions;
    	$auth_user_permissions = explode(';', $auth_user_permissions);

    	$company_positions = $this->groupRepo->getAll();
    	$company_permissions = $this->permissionRepo->getAll();

    	/** for security purposes we serve routes to retrieve forms via ajax for all the tabs */
    	$tabHTML_RoleRositionInfo = route('AjaxRoutePermissionTabsController.ajaxRoleRositionInfo_HTML');
    	$tabHTML_PermissionsInfo = route('AjaxRoutePermissionTabsController.ajaxPermissionsInfo_HTML');

    	$routepermission_getRoleRositioninfo_db_table = route('AjaxRoutePermissionTabsController.getRoleRositionInfo_TABLE');
    	$routepermission_getPermissionsinfo_db_table = route('AjaxRoutePermissionTabsController.getPermissionsInfo_TABLE');

    	$urlpostRoutePermissionSingleRecordChange = route('permissions.RoutePermissionController.postRoutePermissionSingleRecordChange');

    	$this->data = compact('period');
    	$this->js = compact('period', 'locale', 'sections', 'tabHTML_RoleRositionInfo', 'tabHTML_PermissionsInfo', 'routepermission_getRoleRositioninfo_db_table', 'routepermission_getPermissionsinfo_db_table', 'urlpostRoutePermissionSingleRecordChange', 'auth_user_permissions', 'company_positions', 'company_permissions');

    	return $this->render('permission.index');
    }

    /**
     * edit employee memeber timesheet record
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postRoutePermissionSingleRecordChange(Request $request)
    {
    	$whattheysent = $request->all();
    	$rolehaspermissionRepo = $request->only(['fk_role_id', 'fk_permission_id']); /**get these keys only*/

    	if($whattheysent['activate_or_deativate'] == 0){
    		$where = ['fk_role_id' => $whattheysent['fk_role_id'], 'fk_permission_id' => $whattheysent['fk_permission_id']];
    		if($this->rolehaspermissionRepo->deleteWhere($where)){
    			/**session()->flash('message', trans('message.delete.role.permission.routing.successful'));*/
    			return response()->json([
    				'success' => true,
    				'message' =>'deletedWhere',
    				'whattheysent' => $whattheysent
    			]);
    		}
    	}elseif($whattheysent['activate_or_deativate'] == 1){
    		$where = ['fk_role_id' => $whattheysent['fk_role_id'], 'fk_permission_id' => $whattheysent['fk_permission_id']];
    		if ($this->rolehaspermissionRepo->updateOrCreate($rolehaspermissionRepo, $where)) {
    			/**session()->flash('message', trans('message.change.role.permission.routing.successful'));*/
    			return response()->json([
    				'success' => true,
    				'message' =>'updateOrCreate',
    				'whattheysent' => $whattheysent
    			]);
    		}
    	}

    	return response()->json([
    		'success' => false,
    		'message' =>'inside',
    		'whattheysent' => $whattheysent,
    		'rolehaspermissionRepo' => $rolehaspermissionRepo
    	]);
    }

    /**
	 * Show Route Permissions dump
	 *
     * @author sigmoswitch
     * @return \Illuminate\View\View
	 */
    public function routes_dump()
    {
    	$routes = collect(\Route::getRoutes())->map(function ($route) { 
    		$route_to_keep = $route->getActionName(); 
    		if (strpos($route_to_keep, 'App\Http\Controllers') !== false) {
    			$route_to_keep = str_replace("App\Http\Controllers\\","", $route_to_keep);
    			return $route->URI().'';
    		}
    	})->reject(function ($route) {
    		return empty($route);
    	});
    	$routes = $routes->unique()->all();
    	dd($routes);
	   	/**$this->data = compact([]);*/
    	/**$this->js = compact([]);*/
    }


    /**
     * add permissions info
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postAddPermissionsInfo(Request $request)
    {
    	$whattheysent = $request->all();

    	return response()->json([
    		'success' => false,
    		'whattheysent' => $whattheysent
    	]);
    }

    /**
     * edit permissions info
     *
     * @param posteditdocumentinfo $request
     * @return \illuminate\http\redirectresponse
     */
    public function postEditPermissionsInfo(Request $request)
    {
    	$whattheysent = $request->all();

    	return response()->json([
    		'success' => false,
    		'whattheysent' => $whattheysent
    	]);
    }

    /**
     * delete permissions info
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postDeletePermissionsInfo()
    {
    	return response()->json([
    		'success' => false,
    		/**'whattheysent' => $whattheysent*/
    	]);
    }
}
