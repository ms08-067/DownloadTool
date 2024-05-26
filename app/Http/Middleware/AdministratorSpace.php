<?php

namespace App\Http\Middleware;

use Closure;
use App\Repositories\AppUserRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\SiteDeveloperRepository;

use Illuminate\Support\Facades\Crypt;

class AdministratorSpace
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
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /**$auth_user_role = $this->appuserRepo->getAuthUserDetails()->role;*/
        $auth_user_id_check = $this->appuserRepo->checkAuthUserDetails();
        if($auth_user_id_check == null){
            /** we want to pass parameters along with redirect so that can display the message about the error */
            return redirect('/')->with(Auth::logout())->with(['errors' => ['ldap_mismatch' => 'LDAP account not found']]);
        }
        $auth_user_id = $this->appuserRepo->getAuthUserDetails()->user_id;
        $auth_user_permissions = $this->appuserRepo->getAuthUserDetails()->permissions;
        /**$auth_user_username = $this->appuserRepo->getAuthUserDetails()->username;*/

        $route = $request->route()->getName();
        $section = val(explode('.', $route)[0], 'none');
        $auth_user_permissions = explode(';', $auth_user_permissions);
        
        $sitedevelopers = $this->sitedeveloperRepo->getSiteDeveloperArray();
        /**dump($section);*/
        /**dump($sitedevelopers);*/
        /**dd($auth_user_permissions);*/
        

        if (in_array("READ Logs", $auth_user_permissions) && array_key_exists(strtolower($auth_user_id), $sitedevelopers)) {
            return $next($request);
        }else if (in_array("READ ".$section, $auth_user_permissions) || ccc()) {
            return $next($request);
        }else{
            return redirect('/employee_profile/'.Crypt::encryptString($auth_user_id));
        }   
    }
}
