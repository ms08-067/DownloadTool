<?php

namespace App\Ldap\Rules;

use LdapRecord\Laravel\Auth\Rule;
use LdapRecord\Models\ActiveDirectory\Group;

class OnlyAdministrators extends Rule
{
    /**
     * Check if the rule passes validation.
     *
     * @return bool
     */
    public function isValid()
    {
        $administrators = Group::find('cn=Administrators,dc=local,dc=com');

        return $this->user->groups()->recursive()->exists($administrators);
    }
}
