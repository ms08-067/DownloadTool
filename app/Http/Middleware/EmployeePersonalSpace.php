<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Repositories\AppUserRepository;
use App\Repositories\EmployeeRepository;
use Debugbar;

/**
* Class EmployeePersonalSpace
*
* @author sigmoswitch
* @package App\Imports
*/
class EmployeePersonalSpace
{
    /**
     * @var AppUserRepository
     */
    protected $appuserRepo;

    /**
     * @var EmployeeRepository
     */
    protected $employeeRepo;

    /**
     * Create a new controller instance.
     *
     * @param AppUserRepository $appuserRepo
     * @param EmployeeRepository $userRepo
     * @return void
     */    
    public function __construct(
    	AppUserRepository $appuserRepo,
    	EmployeeRepository $employeeRepo
    ){
    	$this->appuserRepo = $appuserRepo;
    	$this->employeeRepo = $employeeRepo;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
    	/**$auth_user_role = $this->appuserRepo->getAuthUserDetails()->role;*/
    	$auth_user_id = $this->appuserRepo->getAuthUserDetails()->user_id;

    	$uri = $request->path();
    	$route = (string)$request->route('user');

    	try {
    		$route = Crypt::decryptString($route);
    		$route = (int)$route;
    	} catch (DecryptException $e) {
    		Debugbar::addException($e);
    		abort(404, 'Page Not Found');
    	}

    	if( $auth_user_id !== $route ){
    		if (strpos($uri, 'employee_email_signature') !== false) {
    			return $next($request);
    		}else{
    			abort(403, 'Access denied');
    		}
    	}
    	return $next($request);
    }
}
