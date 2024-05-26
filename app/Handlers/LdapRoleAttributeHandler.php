<?php

namespace App\Handlers;

use App\Models\User as EloquentUser;
use Adldap\Models\User as LdapUser;
use App\Models\Employee;
use App\Models\Team;
use App\Models\Group;

class LdapRoleAttributeHandler
{
    /**
     * @var Employee
     */
    protected $employee;

    /**
     * @var Team
     */
    protected $team;

    /**
     * @var Group
     */
    protected $group;

    /**
     * create a new controller instance.
     *
     * @param EmployeeRepository $employeerepo
     */
    public function __construct(
        Employee $employee,
        Team $team,
        Group $group
    ) {
        $this->employee = $employee;
        $this->team = $team;
        $this->group = $group;
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
        $userROLE = null;
        $ldap_username = strtolower($ldapUser->samaccountname[0]);
        if($ldap_username){
            $this_person = $this->employee->findBy('username', $ldap_username);
            if($this_person){
                $userROLE = $this_person->fk_group_id;
            }
        }
        $eloquentUser->fk_group_id = $userROLE;
    }
}