<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
//use Adldap;
//use Adldap\Configuration\DomainConfiguration;
//use App\Providers\Br24AdldapProvider;
use Sinergi\BrowserDetector\Browser;
use Exception;
use Debugbar;
use Loggy;

trait AuthenticatesUsers
{
    use RedirectsUsers, ThrottlesLogins, DispatchesJobs;

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        $result = new Browser;
        $result = $result->getName();
        $locale = app()->getLocale();
        $app_name = Str::slug(env('APP_NAME', 'laravel'), '_').'_'.Str::slug(env('APP_ENV', 'dev'), '_').'_session';
        /**dd($locale);*/

        $this->data = compact('result');
        $this->js = compact('locale', 'app_name');

        return $this->render('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        /**If the class is using the ThrottlesLogins trait, we can automatically throttle*/
        /**the login attempts for this application. We'll key this by the username and*/
        /**the IP address of the client making these requests into this application.*/
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $this->ldapLogin_packagist($request);

        /**If the login attempt was unsuccessful we will increment the number of attempts*/
        /**to login and redirect the user back to the login form. Of course, when this*/
        /**user surpasses their maximum number of attempts they will get locked out.*/
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt($this->credentials($request), $request->filled('remember'));
        /***/
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
        /***/
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);
        Loggy::write('authentication', Auth::user()->username .' -> successfully logged in');
        return $this->authenticated($request, $this->guard()->user()) ?: redirect()->intended($this->redirectPath());
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        /**dd($request);*/
        /**dd($user);*/
        return true;
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        /**dd($request->only(['username']));*/
        /**$username = $request->only(['username']);*/
        /**Loggy::write('authentication', $username['username'] .' ---> ', [trans('auth.failed')]);*/
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
        /***/
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return $this->loggedOut($request) ?: redirect('/lang/'.app()->getLocale());
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        /**dd($request);*/
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        /** even logging out if the ldap server cannot be found then it errors badly */
        return Auth::guard();
        //
    }

    /**
     * Perform the ldap login.
     *
     * @return array
     */
    private function ldapLogin_packagist(Request $request)
    {
        $exists = DB::table('users')->where('username', $request['username'])->first();

        $credentials = [
            'samaccountname' => $request['username'],
            'password' => $request['password'],
            'fallback' => [
                'username' => $request['username'],
                'password' => $request['password'],
            ],
        ];

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            return $this->sendLoginResponse($request);
        }else{
            /** if the ldap server becomes unavailable and they enter their db stored password incorrectly it will get deleted and they will be blocked out */
            if($exists){
                DB::table('users')->where('username', $request['username'])->delete();
            }
        }
        
        // $exists = DB::table('users')->where('username', $request['username'])->first();

        // try {
        //     /** not found there so will need to use the normal way */
        //     /** it will fail anyway if the network to the ldap server is down they can just wait until available again */
        //     /** but if there is a way to redirect back to the login page when the ldap server cannot be found? */
        //     //Adldap::connect();

        //     /** can try to use the fallback way */
        //     /** this will always happen every log in attempt network or no network*/
        //     $swap_config = [
        //         'hosts'            => explode(' ', env('LDAP_HOSTS', 'corp-dc1.corp.acme.org corp-dc2.corp.acme.org')),
        //         'base_dn'          => env('LDAP_BASE_DN', 'dc=corp,dc=acme,dc=org'),
        //         'username'         => env('LDAP_USERNAME'),
        //         'password'         => env('LDAP_PASSWORD'),
        //         'schema'           => Adldap\Schemas\ActiveDirectory::class,
        //         'account_prefix'   => env('LDAP_ACCOUNT_PREFIX', ''),
        //         'account_suffix'   => env('LDAP_ACCOUNT_SUFFIX', ''),
        //         'port'             => env('LDAP_PORT', 389),
        //         'follow_referrals' => false,
        //         'use_ssl'          => env('LDAP_USE_SSL', false),
        //         'use_tls'          => env('LDAP_USE_TLS', false),
        //         'version'          => 3,
        //         'timeout'          => env('LDAP_TIMEOUT', 5),
        //     ];
        //     $config_revert = [
        //         'default' => [
        //             'hosts'            => explode(' ', env('LDAP_HOSTS', 'corp-dc1.corp.acme.org corp-dc2.corp.acme.org')),
        //             'base_dn'          => env('LDAP_BASE_DN', 'dc=corp,dc=acme,dc=org'),
        //             'username'         => env('LDAP_USERNAME'),
        //             'password'         => env('LDAP_PASSWORD'),
        //             'schema'           => Adldap\Schemas\ActiveDirectory::class,
        //             'account_prefix'   => env('LDAP_ACCOUNT_PREFIX', ''),
        //             'account_suffix'   => env('LDAP_ACCOUNT_SUFFIX', ''),
        //             'port'             => env('LDAP_PORT', 389),
        //             'follow_referrals' => false,
        //             'use_ssl'          => env('LDAP_USE_SSL', false),
        //             'use_tls'          => env('LDAP_USE_TLS', false),
        //             'version'          => 3,
        //             'timeout'          => env('LDAP_TIMEOUT', 5),
        //         ]
        //     ];
            
        //     /** use custom provider to bypass throwing cannot connect ldap server 500 error */
        //     $custom_provider = new Br24AdldapProvider($swap_config);
        //     Adldap::swap($custom_provider);
        //     $result_connectWhenOtherSituation = Adldap::connectWhenOtherSituation();

        //     if($result_connectWhenOtherSituation === "Can't contact LDAP server"){
        //         throw ValidationException::withMessages([
        //             $this->username() => [trans('auth.ldap_server_not_found', ['minutes' => "5"])],
        //         ]);
        //         return;
        //     }else{
        //         $revert = new Adldap\Adldap($config_revert);
        //         Adldap::swap($revert);
        //     }
        // } catch (BindException $e) {
        //     Debugbar::addException($e);
        //     $last_error = Adldap::getConnection()->getLastError();
        //     $error_msg = $last_error." Please try again";
        //     $username = $request->only(['username']);
        //     Loggy::write('authentication', $username['username'] .' --> ', [$error_msg]);
        //     throw ValidationException::withMessages([
        //         $this->username() => [$error_msg],
        //     ]);
        // }

        // if (Auth::attempt($request->only(['username', 'password']), $request->filled('remember'))) {
        //     /** this means that their password stored on the db is correctly input */
        //     /** we should probably get all the employees from the production server now if it hasn't been done yet */
        //     //$this->employeeRepo->dbTableLikenessCheck();
        //     return $this->sendLoginResponse($request);
        // }else{
        //     /** if they have changed their passwords we can remove their entry from the Users table and silently authenticate again provided the server is available */
        //     /** which causes the problem that when they change their password on ldap it is not reflected here need the ldap server present */
        //     if($exists){
        //         DB::table('users')->where('username', $request['username'])->delete();
        //     }
        // }
        /** if all fails */
    }
}
