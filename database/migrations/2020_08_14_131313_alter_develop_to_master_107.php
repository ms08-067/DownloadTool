<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDevelopToMaster107 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->string('address');
            $table->timestamps();
        });
        Schema::create('employee_status_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('desc');
            $table->timestamps();
        });

        Schema::create('company_positions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->enum('active', ['1', '2'])->default('1')->comment('1: active 2: inactive');
            $table->string('last_updated_by')->nullable()->default(null);
            $table->nullableTimestamps();
        });
        Schema::create('company_teams', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->enum('active', ['1', '2'])->default('1')->comment('1: active 2: inactive');
            $table->string('last_updated_by')->nullable()->default(null);
            $table->nullableTimestamps();
        });


        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('last_updated_by')->nullable()->default(null);
            $table->timestamps();
        });
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('fk_permission_id');
            $table->unsignedInteger('fk_role_id');
            $table->timestamps();

            $table->foreign('fk_permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->foreign('fk_role_id')->references('id')->on('company_positions')->onDelete('cascade');
        });


        Schema::create('maritial_status_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('banks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('name_eng');
            $table->string('code');
            $table->timestamps();
        });
        Schema::create('bank_branches', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('fk_bank_id');
            $table->string('name');
            $table->string('name_eng');
            $table->string('address');
            $table->string('address_eng');
            $table->string('coordinates');
            $table->timestamps();

            $table->foreign('fk_bank_id')->references('id')->on('banks');
        });

        Schema::create('nationalities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('country');
            $table->integer('sort_order');
            $table->timestamps();
        });            
        Schema::create('employees', function (Blueprint $table) {

            $table->increments('id'); 
            $table->unsignedInteger('user_id')->unique(); 
            $table->string('fullname', 255)->charset('utf8')->default(null)->comment('pulled from production tool'); 
            $table->string('username', 50)->charset('utf8')->default(null)->comment('pulled from production tool'); 
            $table->unsignedInteger('fk_group_id')->default(3)->comment('pulled from production tool'); 
            $table->unsignedInteger('fk_team_id')->nullable()->comment('pulled from production tool user_teams'); 
            $table->unsignedInteger('fk_status_id')->default(1)->comment('pulled from production tool'); 
            $table->date('status_change_date')->nullable()->default(null); 
            $table->string('reason')->nullable()->comment('reason for termination resign or inactive'); 
            $table->unsignedTinyInteger('level')->default(1)->comment('pulled from production tool'); 
            $table->unsignedInteger('fk_office_id')->nullable()->default(null)->comment('1: NCH Office, 2: DC Office'); 
            $table->unsignedInteger('homeland_status')->nullable()->default(null)->comment('1: Viet, 2: Expat'); 
            $table->unsignedInteger('tax_resident_type_id')->nullable()->default(null)->comment('1: Tax Resident, 2: Tax Non-Resident'); 
            $table->string('bank_account', 255)->nullable()->default(null); 
            $table->unsignedInteger('fk_bank_id')->nullable()->default(null); 
            $table->unsignedInteger('fk_bank_branch_id')->nullable()->default(null); 
            $table->date('birthday')->nullable()->default(null); 
            $table->unsignedInteger('gender')->nullable()->default(null)->comment('1: Female, 2: Male'); 
            $table->unsignedInteger('fk_maritial_status_id')->nullable()->default(null)->comment('single/married/divorced/widowed: take from martial_status_type_table'); 
            $table->unsignedInteger('fk_nationality_id')->nullable()->default(null); 
            $table->string('email_address', 255)->unique()->nullable()->charset('utf8')->default(null); 
            $table->string('work_email_address', 255)->unique()->nullable()->charset('utf8')->default(null); 
            $table->string('contact_phone', 255)->nullable()->default(null); 
            $table->string('work_contact_phone', 255)->nullable()->default(null); 
            $table->string('skype', 255)->nullable()->default(null); 
            $table->string('temporary_address', 255)->nullable()->charset('utf8')->default(null); 
            $table->string('permanent_address', 255)->nullable()->charset('utf8')->default(null); 
            $table->string('avatar')->nullable()->default(null); 
            $table->string('tax_code', 255)->nullable()->default(null); 
            $table->string('social_insurance_number', 255)->nullable()->default(null); 
            $table->string('ice_name', 255)->nullable()->charset('utf8')->default(null); 
            $table->string('ice_phonenumber', 255)->nullable()->default(null); 
            $table->decimal('current_annual_leave', 3, 1)->default('0.0'); 
            $table->string('last_updated_by')->nullable()->default(null); 
            $table->timestamps();
            $table->softDeletes();
            $table->index('user_id');

            $table->foreign('fk_office_id')->references('id')->on('offices'); 
            $table->foreign('fk_status_id')->references('id')->on('employee_status_types'); 
            $table->foreign('fk_group_id')->references('id')->on('company_positions'); 
            $table->foreign('fk_team_id')->references('id')->on('company_teams'); 
            $table->foreign('fk_bank_id')->references('id')->on('banks'); 
            $table->foreign('fk_bank_branch_id')->references('id')->on('bank_branches'); 
            $table->foreign('fk_maritial_status_id')->references('id')->on('maritial_status_types'); 
            $table->foreign('fk_nationality_id')->references('id')->on('nationalities');
        });


        Schema::create('site_developers', function (Blueprint $table) {
            $table->unsignedInteger('fk_user_id')->unique();
            $table->string('last_updated_by')->nullable()->default(null);
            $table->timestamps();

            $table->foreign('fk_user_id')->references('user_id')->on('employees');
        });

        Artisan::call('db:seed', array('--class' => 'PermissionsSeeder'));
        Artisan::call('db:seed', array('--class' => 'PermissionsSeeder24MAY2019'));

        Artisan::call('db:seed', array('--class' => 'BankSeeder'));
        Artisan::call('db:seed', array('--class' => 'BranchSeeder'));
        Artisan::call('db:seed', array('--class' => 'MaritialStatusTypeSeeder'));
        Artisan::call('db:seed', array('--class' => 'NationalitySeeder'));
        Artisan::call('db:seed', array('--class' => 'OfficeSeeder'));



        $view = "
            CREATE VIEW `v_app_user` 
            AS
            SELECT 
                emp.user_id AS user_id,
                emp.fullname AS name,
                gps.name AS role,
                subsubquery.permissions_stringlist AS permissions
            FROM
                employees emp
                    LEFT OUTER JOIN
                company_positions gps ON emp.fk_group_id = gps.id
                    LEFT OUTER JOIN
                (SELECT 
                    cmp.id,
                    cmp.name,
                    role_has_permissionssubquery.Permissions AS permissions_stringlist
                FROM
                    company_positions AS cmp
                        LEFT OUTER JOIN 
                    (SELECT 
                        fk_role_id AS id,
                        cop.name AS company_position,
                        GROUP_CONCAT(per.name,';') AS Permissions
                    FROM
                        role_has_permissions AS rhp
                            LEFT OUTER JOIN 
                        permissions AS per ON rhp.fk_permission_id = per.id
                            LEFT OUTER JOIN 
                        company_positions AS cop ON rhp.fk_role_id = cop.id
                    GROUP BY fk_role_id
                    ) AS role_has_permissionssubquery ON cmp.id = role_has_permissionssubquery.id
                ) AS subsubquery ON gps.id = subsubquery.id
            ";
            DB::unprepared($view);

            $view = "
            CREATE VIEW `v_role_permission` 
            AS
            SELECT 
                pem.name AS permission,
                'Edit;Delete' AS actions,
                apusr.fullname AS last_updated_by,
                pem.updated_at AS last_updated
            FROM
                permissions pem
                    LEFT JOIN 
                employees apusr ON pem.last_updated_by = apusr.user_id;
            ";
            DB::unprepared($view);

}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::dropIfExists('tasks_downloads_files');
   }
}
