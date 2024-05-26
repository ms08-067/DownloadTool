<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\UserCanDevelopPolicy;
use App\Models\User;
use LdapRecord\Laravel\Middleware\WindowsAuthenticate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserCanDevelopPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('viewWebSocketsDashboard', function ($user = null) {
            /**dd($user);*/
            return in_array($user->username, [
                    'nickolas',
            ]);
        });

        Gate::guessPolicyNamesUsing(function ($modelClass) {
            // return policy class name...
            /**dd($modelClass);*/
        });
        
        //WindowsAuthenticate::rememberAuthenticatedUsers();
        //WindowsAuthenticate::logoutUnauthenticatedUsers();
        //WindowsAuthenticate::serverKey('PHP_AUTH_USER');
        //WindowsAuthenticate::bypassDomainVerification();
        //WindowsAuthenticate::guards(['default']);
        // WindowsAuthenticate::extractDomainUsing(function ($account) {
        //     [$username, $domain] = array_pad(
        //         array_reverse(explode('\\', $account)),
        //         2,
        //         null
        //     );

        //     return [$username, $domain];
        // });
    }
}
