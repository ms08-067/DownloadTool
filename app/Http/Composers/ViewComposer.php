<?php

namespace App\Http\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\EmployeeRepository;
use App\Repositories\AppUserRepository;
use App\Repositories\PermissionRepository;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Session;
use Carbon\Carbon;

/**
 * Class ViewComposer
 *
 * @author tolawho
 * @package App\Http\Composers
 */
class ViewComposer
{
    /**
     * @var EmployeeRepository
     */
    protected $employeeRepo;

    /**
     * @var AppUserRepository
     */
    protected $appuserRepo;

    /**
     * @var PermissionRepository
     */
    protected $permissionRepo;

    /**
     * Create new view composer instance.
     * @param EmployeeRepository $employeeRepo
     * @return void
     */
    public function __construct(
        EmployeeRepository $employeeRepo,
        AppUserRepository $appuserRepo,
        PermissionRepository $permissionRepo
    ){
        $this->employeeRepo = $employeeRepo;
        $this->appuserRepo = $appuserRepo;
        $this->permissionRepo = $permissionRepo;
    }

    /**
     * Bind data to the view.
     *
     * @author tolawho
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        if(Auth::user()){
            $auth_user = $this->employeeRepo->findByLdapUsername(Auth::user()->username);
            /**dd($auth_user);*/
        }

        if (in_array($view->getName(), ['layouts.simple', 'layouts.app', 'layouts.bridge', 'partials.header', 'partials.footer', 'layouts.app_manual', 'partials.header_manual'])) {
            $app_name = Str::slug(env('APP_NAME', 'laravel'), '_').'_'.Str::slug(env('APP_ENV', 'dev'), '_').'_session';
            $locale = app()->getLocale();
            $data = $view->getData();

            $page_title_name = request()->getRequestUri();
            $page_title_name = str_replace('/', ' - ', $page_title_name);
            $page_title_name = ucwords(str_replace('_', ' ', $page_title_name));
            $page_title_name = preg_replace('/\s-\s/', '', $page_title_name, 1);

            $route = request()->route()->getName();
            /** make sure all route names have three parts */
            //1: will be the header sub sections .... eg mydata, organization, attendance, bonuses, accounting. //used for checking against permissions also
            //2: will be the sub sub items in this sub section
            //3: will be the action
            $section = val(explode('.', $route)[0], 'none');
            $module = val(explode('.', $route)[1], 'none');
            $action = val(explode('.', $route)[2], 'index');

            $tpl = sprintf('%s.%s.%s', $section, $module, $action);
            $js = isset($data['js']) ? json_encode($data['js']) : json_encode([]);
            $enviro = env('APP_ENV');

            $appcurrency_key = 'appcurrency';
            if (Session::has($appcurrency_key) && in_array(Session::get($appcurrency_key), config('base.currency'))) {
                $currency = Session::get($appcurrency_key);
                $currencyUnit =  config('base.currency_unit')[Session::get($appcurrency_key)];
            }else{
                $currency = config('app.currency');
                $currencyUnit = config('app.currencyUnit');
                Session::put($appcurrency_key, $currency);
            }

            $auth_userId = null;
            $auth_fullname = null;
            $auth_encrypted_ids = [];
            $auth_user_permissions = [];
            $doesAuthUserHaveElevatedPrivileges = false;

            if(Auth::user()){
                /** Authorised by ldap now so must get their user_id and information from the employees table */
                /** get the encrypted user_id of authorised logged in user to the view */
                $auth_userId = $auth_user->user_id;
                $auth_fullname = $auth_user->fullname;/** since we don't have their user_id in this table any more what can we use then ? */
                $auth_encrypted_ids[$auth_userId]['user_id'] = Crypt::encryptString($auth_userId);

                $auth_user_permissions = $this->appuserRepo->getAuthUserDetails()->permissions;
                $auth_user_permissions = explode(';', $auth_user_permissions);
                if(sizeof($auth_user_permissions) <= 6){$doesAuthUserHaveElevatedPrivileges = false;}else{$doesAuthUserHaveElevatedPrivileges = true;}
            }

            if(env('APP_ENV') == 'dev'){
                $versioning = Carbon::now()->timestamp;
                // exec("cat /var/www/src/alpine/lastrev-parse 2>&1", $output, $return_var);
                // dump($output);
                // dump($return_var);
                // $versioning = trim(preg_replace('/[\t|\s{2,}]/', '', $output[0]));
                // dd($versioning);
            }else{
                /** because the command it run with user "www-data" (the user for nginx) and the new security feature from git does not allow the user www-data to perform the git command below */
                /** */
                exec("cat /var/www/src/alpine/lastrev-parse 2>&1", $output, $return_var);
                // dump($output);
                // dump($return_var);
                $versioning = trim(preg_replace('/[\t|\s{2,}]/', '', $output[0]));
                // dd($versioning);
            }
            
            $view->with(compact('js', 'tpl', 'enviro', 'section', 'module', 'action', 'page_title_name', 'locale', 'currency', 'currencyUnit', 'auth_encrypted_ids', 'auth_userId', 'auth_fullname', 'auth_user_permissions', 'doesAuthUserHaveElevatedPrivileges', 'versioning', 'app_name'));
        }
    }
}
