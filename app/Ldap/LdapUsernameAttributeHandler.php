<?php

namespace App\Ldap;

use App\Models\User as EloquentUser;
use Adldap\Models\User as LdapUser;

class LdapUsernameAttributeHandler
{
    /**
     * Synchronizes ldap attributes to the specified model.
     *
     * @param LdapUser     $ldapUser
     * @param EloquentUser $eloquentUser
     *
     * @return void
     */
    public function handle(LdapUser $ldapUser, EloquentUser $eloquentUser)
    {
        $ldap_username = strtolower($ldapUser->samaccountname[0]);
        $eloquentUser->username = $ldap_username;
    }
}