<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Repositories\UserRepository;
 use Session;

/**
 * Class UserController
 *
 * @author tolawho/sigmoswitch
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
	
 	/**
    * @var UserRepository
    */
    protected $userRepo;

    /**
     * UserRepository constructor.
     *
     * @param UserRepository $userRepo
     */
    public function __construct(
        UserRepository $userRepo
    ) {
        $this->userRepo = $userRepo;
    }

    /**
     * Show change password form
     *
     * @return \Illuminate\View\View
     */
    public function changePwd()
    {
        return $this->render('auth.passwords.change');
    }

    /**
     * Change password
     *
     * @param ChangePasswordRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postChangePwd(ChangePasswordRequest $request)
    {
        $input = $request->all();
        $currentPassword = $input['current_password'];
        if (!\Hash::check($currentPassword, auth()->user()->password)) {
            validator(
                [],
                ['current_password' => 'required'],
                ['current_password.required' => 'Current password not match.']
            )->validate();
        }

        // do save new password
        if ($this->userRepo->update(['id' => auth()->id(), 'password' => bcrypt($input['password'])])) {
            $request->session()->flash('message', trans('Change password successful.'));
            /**cleanCache();*/
            /**clear employee cache when data changed*/
        }

        return back();
    }

    /**
     * Set locale for user
     *
     * @author tolawho
     * @param string $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function lang($locale)
    {
        $referer = request()->headers->get('referer');
        if (str_contains($referer, '/logout')) { 
            return redirect('/')->with(Auth::logout())->with(['errors' => ['ldap_mismatch' => '419 error']]);
        }

        if (in_array($locale, config('base.locales'))) {
            session()->put('locale', $locale);
        }
        return back();
    }
    /**
     * Set currency for user
     *
     * @author sigmoswitch
     * @param string $currency
     * @return \Illuminate\Http\RedirectResponse
     */
    public function currency($currency)
    {
        if (in_array($currency, config('base.currency'))) {
            session()->put('appcurrency', $currency);
        }
        return back();
    }
}
