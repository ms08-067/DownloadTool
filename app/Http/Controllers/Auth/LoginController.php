<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Repositories\AppUserRepository;
use App\Repositories\EmployeeRepository;
use LdapRecord\Laravel\Auth\ListensForLdapBindFailure;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;
    use ListensForLdapBindFailure {
        handleLdapBindError as baseHandleLdapBindError;
    }

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
     * @param Employee $employee
     * @    return void
     */    
    public function __construct(
        AppUserRepository $appuserRepo,
        EmployeeRepository $employeeRepo
    ){
        $this->appuserRepo = $appuserRepo;
        $this->employeeRepo = $employeeRepo;

        $this->middleware('guest')->except('logout');
        $this->listenForLdapBindFailure();
    }

    protected function handleLdapBindError($message, $code = null)
    {
        if ($code == '773') {
            /**The users password has expired. Redirect them.*/
            abort(redirect('/password-reset'));
        }

        $this->baseHandleLdapBindError($message, $code);
        // throw ValidationException::withMessages([
        //     'email' => "Whoops! LDAP server cannot be reached.",
        // ]);        
    }
}
