<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Group;
use App\Models\Permission;
use App\Models\RoleHasPermission;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder24MAY2019 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

    	$roles = [
    		'Editor',
    		'Quality Controller',
    		'Super Quality Controller',
    		'Shift Leader',
    		'3D Modeler',
    		'Graphic Designer',
    		'Translator',
    		'Accountant',
    		'Human Resources Generalist',
    		'Software Developer',
    		'IT Administrator',
    		'Executive Assistant',
    		'Sales Executive',
    		'Content Marketing Specialist',
    		'Intern',
    		'Trainee',
    		'Training Manager',
    		'Production Manager',
    		'Chief Technology Officer',
    		'Chief Product Officer',
    		'Human Resources Manager',
    		'Chief Accountant',
    		'Director',
    		'General Director',
    		'Cleaner',
    		'Other'
    	];
    	
    	$permissions = [
    		// 'READ period_controls' => ['Editor', 'Quality Controller', 'Super Quality Controller', 'Shift Leader', '3D Modeler', 'Graphic Designer', 'Translator', 'Accountant', 'Human Resources Generalist', 'Software Developer', 'IT Administrator', 'Executive Assistant', 'Sales Executive', 'Content Marketing Specialist', 'Intern', 'Trainee', 'Training Manager', 'Production Manager', 'Chief Technology Officer', 'Chief Product Officer', 'Human Resources Manager', 'Chief Accountant', 'Director', 'General Director', 'Cleaner', 'Other'],
    		// 'READ avatar' => ['Editor', 'Quality Controller', 'Super Quality Controller', 'Shift Leader', '3D Modeler', 'Graphic Designer', 'Translator', 'Accountant', 'Human Resources Generalist', 'Software Developer', 'IT Administrator', 'Executive Assistant', 'Sales Executive', 'Content Marketing Specialist', 'Intern', 'Trainee', 'Training Manager', 'Production Manager', 'Chief Technology Officer', 'Chief Product Officer', 'Human Resources Manager', 'Chief Accountant', 'Director', 'General Director', 'Cleaner', 'Other'],
    		// 'WRITE avatar' => ['Editor', 'Quality Controller', 'Super Quality Controller', 'Shift Leader', '3D Modeler', 'Graphic Designer', 'Translator', 'Accountant', 'Human Resources Generalist', 'Software Developer', 'IT Administrator', 'Executive Assistant', 'Sales Executive', 'Content Marketing Specialist', 'Intern', 'Trainee', 'Training Manager', 'Production Manager', 'Chief Technology Officer', 'Chief Product Officer', 'Human Resources Manager', 'Chief Accountant', 'Director', 'General Director', 'Cleaner', 'Other'],
    		// 'READ home' => ['Editor', 'Quality Controller', 'Super Quality Controller', 'Shift Leader', '3D Modeler', 'Graphic Designer', 'Translator', 'Accountant', 'Human Resources Generalist', 'Software Developer', 'IT Administrator', 'Executive Assistant', 'Sales Executive', 'Content Marketing Specialist', 'Intern', 'Trainee', 'Training Manager', 'Production Manager', 'Chief Technology Officer', 'Chief Product Officer', 'Human Resources Manager', 'Chief Accountant', 'Director', 'General Director', 'Cleaner', 'Other'],
    		// 'WRITE home' => [],
    		// 'READ mydata' => ['Editor', 'Quality Controller', 'Super Quality Controller', 'Shift Leader', '3D Modeler', 'Graphic Designer', 'Translator', 'Accountant', 'Human Resources Generalist', 'Software Developer', 'IT Administrator', 'Executive Assistant', 'Sales Executive', 'Content Marketing Specialist', 'Intern', 'Trainee', 'Training Manager', 'Production Manager', 'Chief Technology Officer', 'Chief Product Officer', 'Human Resources Manager', 'Chief Accountant', 'Director', 'General Director', 'Cleaner', 'Other'],
    		// 'WRITE mydata' => [],
    		// 'READ download' => ['Editor', 'Quality Controller', 'Super Quality Controller', 'Shift Leader', '3D Modeler', 'Graphic Designer', 'Translator', 'Accountant', 'Human Resources Generalist', 'Software Developer', 'IT Administrator', 'Executive Assistant', 'Sales Executive', 'Content Marketing Specialist', 'Intern', 'Trainee', 'Training Manager', 'Production Manager', 'Chief Technology Officer', 'Chief Product Officer', 'Human Resources Manager', 'Chief Accountant', 'Director', 'General Director', 'Cleaner', 'Other'],
    		// 'READ organization' => ['Director', 'General Director', 'Chief Technology Officer', 'Chief Accountant', 'Accountant', 'Human Resources Manager', 'Human Resources Generalist', 'IT Administrator'],
    		// 'WRITE organization' => ['Chief Technology Officer', 'Human Resources Manager', 'Human Resources Generalist', 'IT Administrator'],
    		// 'READ export' => ['Director', 'General Director', 'Chief Technology Officer', 'Chief Accountant', 'Accountant', 'Human Resources Manager', 'Human Resources Generalist', 'IT Administrator'],
    		// 'WRITE export' => ['Chief Technology Officer', 'Human Resources Manager', 'Human Resources Generalist', 'IT Administrator'],
    		// 'READ attendance' => ['Director', 'General Director', 'Chief Technology Officer', 'Chief Accountant', 'Accountant', 'Human Resources Manager', 'Human Resources Generalist', 'IT Administrator', 'Shift Leader', 'Production Manager'],
    		// 'WRITE attendance' => ['Chief Technology Officer', 'Chief Accountant', 'Accountant', 'Human Resources Manager', 'Human Resources Generalist', 'IT Administrator', 'Shift Leader', 'Production Manager'],
    		// 'READ bonuses' => ['Director', 'General Director', 'Chief Technology Officer', 'Chief Accountant', 'Accountant', 'Human Resources Manager', 'Human Resources Generalist', 'IT Administrator'],
    		// 'WRITE bonuses' => ['Chief Technology Officer', 'Chief Accountant', 'Accountant', 'Human Resources Manager', 'Human Resources Generalist', 'IT Administrator'],
    		// 'READ accounting' => ['Director', 'General Director', 'Chief Technology Officer', 'Chief Accountant', 'Accountant', 'IT Administrator'],
    		// 'WRITE accounting' => ['Chief Technology Officer', 'Chief Accountant', 'Accountant', 'IT Administrator'],
    		// 'READ management' => ['Director', 'General Director', 'Chief Technology Officer', 'IT Administrator'],
    		// 'WRITE management' => ['Chief Technology Officer', 'IT Administrator'],
    		// 'READ others' => ['Director', 'General Director', 'Chief Technology Officer', 'Chief Accountant', 'Accountant', 'Human Resources Manager', 'Human Resources Generalist', 'IT Administrator'],
    		// 'WRITE others' => ['Chief Technology Officer', 'Chief Accountant', 'Accountant', 'Human Resources Manager', 'Human Resources Generalist', 'IT Administrator'],
    		// 'READ settings' => ['Director', 'General Director', 'Chief Technology Officer', 'Chief Accountant', 'Accountant', 'Human Resources Manager', 'Human Resources Generalist', 'IT Administrator'],
    		// 'WRITE settings' => ['Chief Technology Officer', 'Chief Accountant', 'Accountant', 'Human Resources Manager', 'Human Resources Generalist', 'IT Administrator'],
    		// 'READ ops' => ['Director', 'General Director', 'Chief Technology Officer', 'IT Administrator'],
    		// 'WRITE ops' => ['Chief Technology Officer', 'Chief Accountant', 'Accountant', 'IT Administrator'],
    		// 'READ permissions' => ['Director', 'General Director', 'Chief Technology Officer', 'IT Administrator'],
    		// 'WRITE permissions' => ['Chief Technology Officer', 'Chief Accountant', 'Accountant', 'IT Administrator'],
    		// 'READ employeelist' => ['Director', 'General Director', 'Chief Technology Officer', 'IT Administrator'],
    		// 'WRITE employeelist' => ['Chief Technology Officer', 'IT Administrator'],
    		'READ logs' => ['Director', 'General Director', 'Chief Technology Officer', 'Chief Accountant', 'Accountant', 'Human Resources Manager', 'Human Resources Generalist', 'IT Administrator', 'Software Developer'],
    		'WRITE logs' => [],
    		
    	];

    	//Permission::whereNotNull('id')->delete();
    	//RoleHasPermission::whereNotNull('id')->delete();

		//create roles
    	foreach ($roles as $role) {
    		//$rolesArray[$role] = Group::create(['name' => $role]);
    		$rolesArray[$role] = Group::select('*')->where(['name' => $role])->get()->toArray();
    	}

    	//create permissions
    	foreach ($permissions as $permission => $authorized_roles) {
            //create permission
    		$permissionInstance = Permission::create(['name' => $permission]);

    		//dd($permissionInstance);

            //authorize roles to those permissions
    		foreach ($authorized_roles as $role) {
    			$fk_role_id = $rolesArray[$role][0]['id'];
    			$fk_permission_id = $permissionInstance->id;
    			RoleHasPermission::updateorCreate(
    				[
    					'fk_permission_id' => $fk_permission_id, 
    					'fk_role_id' => $fk_role_id
    				],
    				[
    					'fk_permission_id' => $fk_permission_id, 
    					'fk_role_id' => $fk_role_id
    				]
    			);
    		}
    	}
    }
}
