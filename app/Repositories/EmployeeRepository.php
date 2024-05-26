<?php

namespace App\Repositories;

use App\Models\Employee;
use App\Models\ProdToolEmployee;
use App\Models\ProdToolUserTeam;

use App\Models\Team;
use App\Models\Timesheet;
use App\Models\AttendanceStatus;

use App\Models\Group;/** Roles / Company Positions */
use App\Models\Permission; /** Permissions */

use App\Models\InitialisedEmployee;
use App\Events\NewEmployeeInitialisedEvent;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;



use Carbon\Carbon;
use App\Helpers;
use Exception;
use Session;
use Debugbar;
use Loggy;
use Cache;

/**
 * Class EmployeeRepository
 *
 * @author sigmoswitch
 * @package App\Repositories
 */
class EmployeeRepository extends Repository
{
    /**
     * @var Employee
     */
    protected $employee;
    
    /**
     * @var ProdToolEmployee
     */
    protected $prodtoolemployee;

    /**
     * @var ProdToolUserTeam
     */
    protected $prodtooluserTeam;   

	/**
     * @var Team
     */
	protected $company_team;

    /**
     * @var Timesheet
     */
    protected $timesheet;

    /**
     * @var AttendanceStatus
     */
    protected $attendancestatus;

    /**
     * @var Group
     */
    protected $company_position;

    /**
     * Create a new repository instance.
     * @param Employee $employee
     * @param ProdToolEmployee $prodtoolemployee
     * @param ProdToolUserTeam $prodtooluserTeam
     * @param Team $company_team
     */
    public function __construct(
    	Employee $employee,
    	ProdToolEmployee $prodtoolemployee,
    	ProdToolUserTeam $prodtooluserTeam,
    	Team $company_team,
    	Timesheet $timesheet,
    	AttendanceStatus $attendancestatus,
    	Group $company_position
    ){
    	$this->employee = $employee;
    	$this->prodtoolemployee = $prodtoolemployee;
    	$this->prodtooluserTeam = $prodtooluserTeam;
    	$this->company_team = $company_team;
    	$this->timesheet = $timesheet;
    	$this->attendancestatus = $attendancestatus;
    	$this->company_position = $company_position;
    }

    /**
     * Get all profile details of all employees
     *
     * @author sigmoswitch
     * @return mixed
     */
    public function getAll()
    {
    	return $this->employee->getAll();
    	/***/
    }

    /**
     * Get all profile details of all employees
     *
     * @author sigmoswitch
     * @return mixed
     */
    public function getAlluserid()
    {
    	return $this->employee->getAlluserid();
    	/***/
    }    

    /**
     * Get profile detail by id
     *
     * @author sigmoswitch
     * @param $id
     * @return mixed
     */
    public function findById($id)
    {
    	return $this->employee->where('user_id', $id)->first();
    	/***/
    }

    /**
     * Get profile detail by ldapusername
     *
     * @author sigmoswitch
     * @param $username
     * @return mixed
     */
    public function findByLdapUsername($username)
    {
    	return $this->employee->where('username', strtolower($username))->first();
    	/***/
    }

    /**
     * Update profile detail
     *
     * @author sigmoswitch
     * @param $input
     * @return mixed
     */
    public function update($input)
    {
    	/** If you want to observer to work you need to do it with model->save(); to capture the event.. */
    	/*$update_employee = Employee::where('user_id', $input['user_id'])->firstOrFail();*/
    	/*$update_employee->last_updated_by = Auth::user()->id;*/
    	/*$update_employee->save();*/
    	return $this->employee->where('user_id', $input['user_id'])->update($input);
    }

    /**
     * Update or create new record by where condition
     *
     * @author sigmoswitch
     * @param array $item
     * @param array $where
     * @return static
     */
    public function updateOrCreate($item, $where)
    {
    	$record = $this->employee->where($where)->first();
    	if (is_null($record)) {
    		return $this->store($item);
    	}
    	unset($item['created_at']);
    	return $record->update($item);
    }
    
    /**
     * Create new expense record
     *
     * @author sigmoswitch
     * @param $input
     * @return static
     */
    public function store($input)
    {
    	return $this->employee->create($input);
    	/***/
    }

    /**
     * Delete all timesheet record by contract_id
     *
     * @author sigmoswitch
     * @param $userId
     * @return mixed
     */
    public function deleteAllByUserId($userId)
    {
    	return $this->employee->where('user_id', $userId)->withTrashed()->forceDelete();
    	/***/
    }

    /**
     * Get employee information reguardless of date period
     *
     * @author sigmoswitch
     * @param $date
     * @return mixed
     * @throws \Exception
     */
    public function getListForEmployeeWithoutPeriod($userid)
    {
    	//$this->dbTableLikenessCheck();
    	
    	$period = getPeriod();
    	$date = $period['date'];

    	$cacheKey = md5(sprintf(config('base.cache.key.employee'), date('Ym01', strtotime($date)), app()->getLocale()));
    	/**dd($cacheKey);*/
    	if (config('base.cache.enable') && Cache::has($cacheKey)) {
    		/**return cache($cacheKey);*/
    		/**dd(Cache::get($cacheKey));*/
    		return Cache::get($cacheKey);
    	}

    	/**Now all Employeess in the full list are accounted for.*/

    	$currencyUnit = config('base.currency_unit')[Session::get('appcurrency')];
    	$locale = app()->getLocale();


    	$employeesprofiledetails = $this->employee->getAllList($userid); /**get their profiledetails from salarytool*/

    	/**echo 'theinside';echo '<pre>';print_r($employeesprofiledetails);echo '</pre>';die();*/

    	/**$mappingTeam = $this->getMappingTeam();*/
    	/**echo '<pre>';print_r($mappingTeam);echo '</pre>';die();*/

    	/**$hasTeamIds = $mappingTeam['hasTeamIds'];*/
    	/**echo '<pre>';print_r($hasTeamIds);echo '</pre>';die();*/

    	/**mapping some information for employee*/
    	/**foreach ($employeesprofiledetails as $userId => &$employee) {$employee['belongTeam'] = val($mappingTeam['hasTeams'][$userId], 'na');}*/

    	/**echo '<pre>';print_r($employeesprofiledetails);echo '</pre>';die();*/

    	if (config('base.cache.enable')) {
    		/**cache([$cacheKey => $employeesprofiledetails], config('base.cache.time.medium'));*/ /** change to the Facade version 20JAN2020 */
    		Cache::put($cacheKey, $employeesprofiledetails, config('base.cache.time.medium'));
    		/**$cacheKeys = cache('cache_key');*/
    		$cacheKeys = Cache::get('cache_key');
    		$cacheKeys[] = $cacheKey;
    		/**dd($cacheKeys);*/
    		/**cache()->forever('cache_key', $cacheKeys);*/ /** change to the Facade version 20JAN2020 */
    		Cache::forever('cache_key', $cacheKeys);
    		/**dd(Cache::get('cache_key'));*/
    	}

    	return $employeesprofiledetails;
    	/***/
    }

    /**
     * check likeness
     *
     * @author sigmowwitch
     * @return mixed
     * @throws \Exception
     */
    public function dbTableLikenessCheck()
    {
    	$employees = $this->prodtoolemployee->getEntirelyAll();
    	$prodtool_userteamslist = $this->prodtooluserTeam->getEntirelyAll();
    	ksort($prodtool_userteamslist);
    	foreach ($employees as $userId => &$details){
    		$details['team_id'] = val($prodtool_userteamslist[$userId]['team_id'], null);
    	}

    	$employee_profiledetails_list = $this->employee->getAll();

    	$whichusersnewlycreated = [];

    	$differences_A = array_diff_key($employees, $employee_profiledetails_list);

    	try {
    		foreach ($differences_A as $userId => $employee_details_from_prodtool) {
    			$newemployee_init = $this->initialiseNewEmployee($userId, $employee_details_from_prodtool);
    			$whichusersnewlycreated[$userId] = 'created';
    		}
    	} catch(\Exception $e) {
    		Debugbar::addException($e);
    		throw $e;
    	}

    	return $whichusersnewlycreated;
    }


    /**
     * Initialise new employeeIds some rows in some tables
     *
     * @author sigmoswitch
     * @param $userId, $from, $key
     * @return mixed
     */
    public function initialiseNewEmployee($userId, $employee_details_from_prodtool)
    {
    	$created_date_reformat = Carbon::createFromFormat('Y-m-d H:i:s', $employee_details_from_prodtool['created']);
    	$reformatted_created_date = Carbon::parse($created_date_reformat)->format('Y-m-d');
    	$now = Carbon::now();
    	try {
    		$newemployeeprofiledetailsrow = new Employee;
    		$newemployeeprofiledetailsrow->user_id = $userId;
    		$newemployeeprofiledetailsrow->fullname = $employee_details_from_prodtool['fullname'];
    		$newemployeeprofiledetailsrow->username = strtolower($employee_details_from_prodtool['username']);

    		$transcribed_group_and_team_id = $this->PT_ET_GROUPID_TEAMID_transcribe($employee_details_from_prodtool['group_id'], $employee_details_from_prodtool['team_id'], $employee_details_from_prodtool['username']);
    		$newemployeeprofiledetailsrow->fk_group_id = $transcribed_group_and_team_id['fk_group_id'];
    		$newemployeeprofiledetailsrow->fk_team_id = $transcribed_group_and_team_id['fk_team_id'];

    		$transcribed_status_id = $this->PT_ET_STATUSID_transcribe($employee_details_from_prodtool['status']);
    		$newemployeeprofiledetailsrow->fk_status_id = $transcribed_status_id;

    		$transcribed_gender_id = $this->PT_ET_GENDERID_transcribe($employee_details_from_prodtool['gender']);
    		$newemployeeprofiledetailsrow->gender = $transcribed_gender_id;

    		/** acc empoloyee import test */
    		/**
			 * 	// $newemployeeprofiledetailsrow->reason = null;
			 * 	// $newemployeeprofiledetailsrow->level = $employee_details_from_prodtool['level'];
			 * 	// $newemployeeprofiledetailsrow->homeland_status = 1;
			 * 	// $newemployeeprofiledetailsrow->tax_resident_type_id = 1;
			 * 	// $newemployeeprofiledetailsrow->fk_office_id = $employee_details_from_prodtool['office_id'];
			 * 	// $newemployeeprofiledetailsrow->fk_bank_id = null;
			 * 	// $newemployeeprofiledetailsrow->fk_bank_branch_id = null;
			 * 	// $newemployeeprofiledetailsrow->birthday = null;
			 * 	// $newemployeeprofiledetailsrow->fk_maritial_status_id = null;
			 * 	// $newemployeeprofiledetailsrow->fk_nationality_id = null;
			 * 	// $newemployeeprofiledetailsrow->email_address = null;
			 * 	// $newemployeeprofiledetailsrow->work_email_address = null;
			 * 	// $newemployeeprofiledetailsrow->contact_phone = null;
			 * 	// //$newemployeeprofiledetailsrow->skype = $employee_details_from_prodtool['skype'];
			 * 	// $newemployeeprofiledetailsrow->permanent_address = null;
			 * 	// $newemployeeprofiledetailsrow->temporary_address = NULL;
			 * 	// $newemployeeprofiledetailsrow->avatar = NULL;
			 * 	// $newemployeeprofiledetailsrow->created_at = $reformatted_created_date;
			 * 	// $newemployeeprofiledetailsrow->updated_at = $reformatted_created_date;
			 * 	// $newemployeeprofiledetailsrow->deleted_at = NULL;
			 */

    		/** default uncomment when not testing import */
    		$newemployeeprofiledetailsrow->reason = null;
    		$newemployeeprofiledetailsrow->level = $employee_details_from_prodtool['level'];
    		$newemployeeprofiledetailsrow->homeland_status = 1;
    		$newemployeeprofiledetailsrow->tax_resident_type_id = 1;
    		$newemployeeprofiledetailsrow->fk_office_id = $employee_details_from_prodtool['office_id'];
    		$newemployeeprofiledetailsrow->fk_bank_id = 1;
    		$newemployeeprofiledetailsrow->fk_bank_branch_id = 10;
    		$newemployeeprofiledetailsrow->birthday = $employee_details_from_prodtool['birthday'];

    		$newemployeeprofiledetailsrow->fk_maritial_status_id = NULL;
    		$newemployeeprofiledetailsrow->fk_nationality_id = 250;
    		$newemployeeprofiledetailsrow->email_address = NULL;
    		$newemployeeprofiledetailsrow->work_email_address = NULL;
    		$newemployeeprofiledetailsrow->contact_phone = $employee_details_from_prodtool['phone'];
    		//$newemployeeprofiledetailsrow->skype = $employee_details_from_prodtool['skype'];
    		$newemployeeprofiledetailsrow->permanent_address = $employee_details_from_prodtool['address'];
    		$newemployeeprofiledetailsrow->temporary_address = NULL;
    		$newemployeeprofiledetailsrow->avatar = NULL;
    		$newemployeeprofiledetailsrow->created_at = $reformatted_created_date;
    		$newemployeeprofiledetailsrow->updated_at = $reformatted_created_date;
    		$newemployeeprofiledetailsrow->deleted_at = NULL;

    		$newemployeeprofiledetailsrow->save();
    		/**echo '$newemployeeprofiledetailsrow_created<br>';*/

    		/**$company_position = $this->company_position->findById($transcribed_group_and_team_id['fk_group_id']);*/
    		/**$newemployeeprofiledetailsrow->assignRole($company_position->name);*/

    		$newemployeeinitialised = new InitialisedEmployee;
    		$newemployeeinitialised->fk_user_id = $userId;
    		$newemployeeinitialised->created_at = $now;
    		$newemployeeinitialised->updated_at = $now;
    		$newemployeeinitialised->save();

    		//event(new NewEmployeeInitialisedEvent($userId));
    	} catch(\Exception $e) {
    		Debugbar::addException($e);
    		throw $e;
    	}
    }

    /**
     * Transcribe Production Tool Group ID to Employee Tool Group ID
     *
     * @author sigmoswitch
     * @param $pt_group_id
     * @return var
     */
    public function PT_ET_GROUPID_TEAMID_transcribe($pt_group_id, $pt_team_id, $username)
    {
    	$data = [];
    	$data['fk_group_id'] = null;
    	$data['fk_team_id'] = null;

    	/******************************************* PRODUCTION_TOOL GROUP ID */
    	/** 1   Manager      */
    	/** 2   QC           */
    	/** 3   Editor       */
    	/** 4   Sales        */
    	/** 5   Marketing    */
    	/** 6   Admin        */
    	/** 7   SQC          */
    	/** 8   Shift Leader */
    	/** 9   WP           */
    	/** 10  PM           */
    	/** 11  Accounting   */
    	/** 12  IT           */
    	/** 13  HR           */
    	/** 14  Other        */

    	/*******************************************  EMPLOYEE_TOOL COMPANY_POSITIONS_ID */
    	/** 1 Editor                         */
    	/** 2 Quality Controller             */
    	/** 3 Super Quality Controller       */
    	/** 4 Shift Leader                   */
    	/** 5 3D Modeler                     */
    	/** 6 Graphic Designer               */
    	/** 7 Translator                     */
    	/** 8 Accountant                     */
    	/** 9 Human Resources Generalist     */
    	/** 10 Software Developer            */
    	/** 11 IT Administrator              */
    	/** 12 Executive Assistant           */
    	/** 13 Sales Executive               */
    	/** 14 Content Marketing Specialist  */
    	/** 15 Intern                        */
    	/** 16 Trainee                       */
    	/** 17 Training Manager              */
    	/** 18 Production Manager            */
    	/** 19 Chief Technology Officer      */
    	/** 20 Chief Product Officer         */
    	/** 21 Human Resources Manager       */
    	/** 22 Chief Accountant              */
    	/** 23 Director                      */
    	/** 24 General Director              */
    	/** 25 Cleaner                       */
    	/** 26 Other                         */

    	if($pt_group_id == 1){
    		$data['fk_group_id'] = 26;
    		if($username == 'markus'){
    			$data['fk_group_id'] = 24;
    		}
    		if($username == 'marcus'){
    			$data['fk_group_id'] = 23;
    		}
    	}
    	if($pt_group_id == 2){
    		$data['fk_group_id'] = 2;
    		$data['fk_team_id'] = 13;
    	}
    	if($pt_group_id == 3){
    		$data['fk_group_id'] = 1;
    	}
    	if($pt_group_id == 4){
    		$data['fk_group_id'] = 13;
    		$data['fk_team_id'] = 4;
    	}
    	if($pt_group_id == 5){
    		$data['fk_group_id'] = 14;
    	}
    	if($pt_group_id == 6){
    		$data['fk_group_id'] = 26;
    	}
    	if($pt_group_id == 7){
    		$data['fk_group_id'] = 3;
    	}
    	if($pt_group_id == 8){
    		$data['fk_group_id'] = 4;
    	}
    	if($pt_group_id == 9){
    		$data['fk_group_id'] = 7;
    		$data['fk_team_id'] = 12;
    	}
    	if($pt_group_id == 10){
    		$data['fk_group_id'] = 18;
    	}
    	if($pt_group_id == 11){
    		$data['fk_group_id'] = 8;
    		if($username == 'hungv'){
    			$data['fk_group_id'] = 22;
    		}
    	}
    	if($pt_group_id == 12){
    		$data['fk_group_id'] = 10;
    		if($username == 'nickolas'){
    			$data['fk_group_id'] = 11;
    		}
    		if($username == 'thomas'){
    			$data['fk_group_id'] = 19;
    		}
    		$data['fk_team_id'] = 2;
    	}
    	if($pt_group_id == 13){
    		$data['fk_group_id'] = 21;
    	}
    	if($pt_group_id == 14){
    		$data['fk_group_id'] = 26;
    	}
    	/******************************************* PRODUCTION_TOOL TEAM_ID */
    	/** 1   Priv           */ //Dept. Privalia
		/** 2   Color          */ //Dept. Colorwork
		/** 3   Clipping       */ //Dept. Clipping
		/** 4   3D             */ //Dept. 3D
		/** 5   InDesign       */ //Dept. InDesign
		/** 6   Training       */ //Dept. Training

		/******************************************* EMPLOYEE_TOOL COMPANY_TEAMS_ID */
		/** 1	Management         */
		/** 2	IT                 */
		/** 3	Marketing          */
		/** 4	Sales and Service  */
		/** 5	Accounting         */
		/** 6	Human Resources    */
		/** 7	Privalia           */
		/** 8	Colorwork          */
		/** 9	Clipping           */
		/** 10	3D                 */
		/** 11	InDesign           */
		/** 12	Translation        */
		/** 13	Quality Control    */
		/** 14	Work Preparation   */
		/** 15	Training           */
		/** 16	Other              */

		if($pt_team_id == 1){
			$data['fk_team_id'] = 7;
		}
		if($pt_team_id == 2){
			$data['fk_team_id'] = 8;
		}
		if($pt_team_id == 3){
			$data['fk_team_id'] = 9;
		}
		if($pt_team_id == 4){
			$data['fk_team_id'] = 10;
			$data['fk_group_id'] = 5;
		}
		if($pt_team_id == 5){
			$data['fk_team_id'] = 11;
		}
		if($pt_team_id == 6){
			$data['fk_team_id'] = 15;
		}
		/** hand older team_ids */
		if($pt_team_id == 7){
			$data['fk_team_id'] = 16;
		}
		if($pt_team_id == 8){
			$data['fk_team_id'] = 16;
		}
		if($pt_team_id == 9){
			$data['fk_team_id'] = 16;
		}

		/** make sure they all have some sort of team or grouping  to insert into the database */
		if($data['fk_team_id'] == null){$data['fk_team_id'] = 16;}
		if($data['fk_group_id'] == null){$data['fk_group_id'] = 26;}

		return $data;
	}

    /**
     * Transcribe Production Tool Status ID to Employee Tool Group ID
     *
     * @author sigmoswitch
     * @param $pt_status_id
     * @return var
     */    
    public function PT_ET_STATUSID_transcribe($pt_status_id)
    {
    	/** 1 Active      */
    	/** 2 Inactive    */
    	/** 3 Resigned    */
    	/** 4 Terminated  */

    	if($pt_status_id == 0){
    		return 2;
    	}elseif($pt_status_id == 1){
    		return 1;
    	}
    }

    /**
     * Transcribe Production Tool Group ID to Employee Tool Group ID
     *
     * @author sigmoswitch
     * @param $pt_gender_id
     * @return var
     */    
    public function PT_ET_GENDERID_transcribe($pt_gender_id)
    {
    	if($pt_gender_id == '0'){ /** Female */
    		return 2; /** employee tool 2 = female */
    	}elseif($pt_gender_id == '1'){ /** Male */
    		return 1; /** employee tool 1 = male */
    	}else{
    		return null;
    	}
    }

    /**
     * Get list timesheet of employee by period viewing month
     *
     * @author tolawho / sigmoswitch
     * @param $date
     * @return array
     */
    public function getTimesheet($date)
    {
    	$timesheetsbymonth = $this->timesheet->getByMonth($date);
    	$timesheetrecordtype = $this->attendancestatus->getAllwithValueKeyById();
    	/**dump($timesheetrecordtype);*/
    	$id_of_blank = $this->attendancestatus->getIdofBlank();
    	/**dd($id_of_blank);*/

    	$data = [];
    	foreach ($timesheetsbymonth as $userId => $timesheet) {
    		$temporaryTimesheet = [];
    		foreach ($timesheet as &$timesheetrecorditem) {
    			$timestamp = strtotime($timesheetrecorditem['record_date']);
    			$key = date('j', $timestamp); 
    			foreach($timesheetrecordtype as $idtorecord){
    				/**echo 'idtorecord=  '.$idtorecord['id'].'<br>';*/
    				/**echo 'timesheetrecorditem=  '.$timesheetrecorditem['fk_attendance_status_id'].'<br>';*/
    				if($timesheetrecorditem['fk_attendance_status_id'] == null){
    					$indexoftimesheetrecord = $id_of_blank[array_key_first($id_of_blank)]['id'];
    					$timesheetrecorditem['record'] = NULL;
    					/**$caltotal = NULL;*/
    					break;
    				}
    				if((int)$timesheetrecorditem['fk_attendance_status_id'] == (int)$idtorecord['id']){
    					/**$caltotal = $idtorecord['name'];*/
    					$indexoftimesheetrecord = $idtorecord['id'];
    					$timesheetrecorditem['indexofrecord'] = $indexoftimesheetrecord;
    					$timesheetrecorditem['record'] = $idtorecord['name'];
    					break;
    				}else{
    					$indexoftimesheetrecord = $id_of_blank[array_key_first($id_of_blank)]['id'];
    					$timesheetrecorditem['record'] = NULL;
    					/**$caltotal = NULL;*/
    				}
    			}
    			/**dd($timesheetrecorditem);*/
    			$timesheetrecorditem['total'] = val($timesheetrecordtype[$indexoftimesheetrecord]['payment_percentage'], 0);
    			$timesheetrecorditem['day'] = date('D', $timestamp);
    			$temporaryTimesheet[$key] = $timesheetrecorditem;
    		}
    		$data[$userId]['working'] = collect($temporaryTimesheet)->sum('total');
    		$data[$userId]['overtime'] = 0;
    		$data[$userId]['timesheet'] = $temporaryTimesheet;
    	}
    	unset($timesheetsbymonth);
    	return $data;
    }


    /**
     * Get list timesheet of employee by period viewing month
     *
     * @author sigmoswitch
     * @param $range_start, $range_end
     * @return array
     */
    public function getTimesheetWithDateRange($range_start, $range_end)
    {
    	$timesheetsbyrange = $this->timesheet->getByRange($range_start, $range_end);
    	/**dd($timesheetsbyrange);*/
    	/** here at this stage need to check if all the days are present for each of the user_ids.. */
    	/** if the date is not there. need to add it with a blank array .. it will be good enough. */
    	/** for instance if there are no data at all */
    	/** the data array returned is blank which cannot be sadly */
    	$timesheet_period = getTimesheetPeriod_ajax();

    	foreach ($timesheetsbyrange as $userId => $timesheet) {
    		foreach ($timesheet_period['range_array_days_formated'] as $dateinperiod){
    			if(!array_key_exists($dateinperiod, $timesheetsbyrange[$userId])){
    				$timesheetsbyrange[$userId][$dateinperiod]['record_date'] = $dateinperiod;
    			}
    		}
    	}
    	
    	/**echo '<pre>';print_r($timesheetsbyrange);echo '</pre>';die();*/

    	$timesheetrecordtype = $this->attendancestatus->getAllwithValue();
    	/**dd($timesheetrecordtype);*/
    	$data = [];
    	/** if the date does not exist still need to make a key for that date */
    	/** otherwise the column will error */
    	foreach ($timesheetsbyrange as $userId => $timesheet) {

    		$temporaryTimesheet = [];

    		foreach ($timesheet as &$timesheetrecorditem) {


    			$timestamp = strtotime($timesheetrecorditem['record_date']);
    			$key = date('j', $timestamp); /** day of the month without leading zeros */

    			if(isset($timesheetrecorditem['fk_attendance_status_id'])){
    				foreach($timesheetrecordtype as $idtorecord){

    					if($timesheetrecorditem['fk_attendance_status_id'] == $idtorecord['id']){
    						$caltotal = $idtorecord['name'];
    						$indexoftimesheetrecord = $idtorecord['id'];
    						$indexoftimesheetrecord = $indexoftimesheetrecord -1;
    						$timesheetrecorditem['indexofrecord'] = $indexoftimesheetrecord;
    						$timesheetrecorditem['record'] = $idtorecord['name'];
    						break;
    					}else{
    						$indexoftimesheetrecord = 0;
    						$timesheetrecorditem['record'] = NULL;
    						$caltotal = NULL;
    					}

    				}
    			}else{
    				$indexoftimesheetrecord = 0;
    				$timesheetrecorditem['record'] = NULL;
    				$caltotal = NULL;
    			}


    			$timesheetrecorditem['total'] = val($timesheetrecordtype[$indexoftimesheetrecord]['payment_percentage'], 0);
    			$timesheetrecorditem['day'] = date('D', $timestamp);
    			$temporaryTimesheet[$key] = $timesheetrecorditem;
    		}
    		$data[$userId]['working'] = collect($temporaryTimesheet)->sum('total');
    		$data[$userId]['overtime'] = 0;
    		$data[$userId]['timesheet'] = $temporaryTimesheet;
    	}

    	unset($timesheetsbyrange);

    	/**echo '<pre>';print_r($data);echo '</pre>';die();*/

    	return $data;
    }    
}
