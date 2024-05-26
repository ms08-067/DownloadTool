<?php

namespace App\Handlers;

use App\Models\User as EloquentUser;
use Adldap\Models\User as LdapUser;
use App\Models\Employee;

class LdapUserIdAttributeHandler
{
    /**
     * @var Employee
     */
    protected $employee;

    /**
     * create a new controller instance.
     *
     * @param EmployeeRepository $employeerepo
     */
    public function __construct(
        Employee $employee
    ) {
        $this->employee = $employee;
    }
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
        $userID = null;
        $ldap_username = strtolower($ldapUser->samaccountname[0]);
        if($ldap_username){
            $this_person = $this->employee->findBy('username', $ldap_username);
            if($this_person){
                $userID = $this_person->user_id;
            }
        }
        $eloquentUser->fk_user_id = $userID;
    }
}