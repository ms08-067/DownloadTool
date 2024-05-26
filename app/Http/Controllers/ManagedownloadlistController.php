<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

use App\Repositories\AppUserRepository;
use App\Repositories\GroupRepository;
use App\Repositories\TeamRepository;
use App\Repositories\HashtagRepository;

use Carbon\Carbon;
use App\Helpers;

use Session;
use Exception;
use Debugbar;
use Validator;
use Loggy;
use Cache;

//use Adldap;
use LdapRecord\Container as LdapContainer;
use LdapRecord\Connection as LdapConnection;
use LdapRecord\Models\ActiveDirectory\Entry as LdapModelAdEntry;
use LdapRecord\Models\ActiveDirectory\User as LdapModelAdUser;
use LdapRecord\Models\ActiveDirectory\Group as LdapModelAdGroup;
use LdapRecord\Models\ActiveDirectory\Computer as LdapModelAdComputer;
use LdapRecord\Models\ActiveDirectory\Contact as LdapModelAdContact;
use LdapRecord\Models\ActiveDirectory\Container as LdapModelAdContainer;
use LdapRecord\Models\ActiveDirectory\OrganizationalUnit as LdapModelAdOrganizationalUnit;
use LdapRecord\Models\ActiveDirectory\Printer as LdapModelAdPrinter;
use LdapRecord\Models\ActiveDirectory\ForeignSecurityPrincipal as LdapModelAdForeignSecurityPrincipal;

use Sinergi\BrowserDetector\Browser;

use App\Models\Task;
use App\Models\TaskDownload;
use App\Models\TaskDownloadFile;
use App\Models\TasksFiles;
use App\Models\TaskDownloadView;

use App\Models\TaskUpload;
use App\Models\TaskUploadFile;
use App\Models\TaskUploadView;

use App\Models\Hashtag;

/**
 * Class ManagedownloadlistController
 *
 * @author sigmoswitch
 * @package App\Http\Controllers
 */
class ManagedownloadlistController extends Controller
{
    public $taskUploadFile;
    public $TaskUploadView;
    public $taskUpload;
    public $taskDownloadFile;
    public $taskDownload;
    public $task;
    public $tasksFiles;
    public $hashtag;


    public $TaskDownloadView;
    
    /**
     * @var AppUserRepository
     */
    protected $appuserRepo;

    /**
     * @var GroupRepository
     */
    protected $groupRepo;

    /**
     * @var TeamRepository
     */
    protected $teamRepo;

    /**
     * @var HashtagRepository
     */
    protected $hashtagRepo;

    /**
     * S3Repository constructor.
     *
     * @param AppUserRepository $appuserRepo
     * @param TaskDownloadFile $taskDownloadFile
     * @param TaskDownload $taskDownload
     * @param TaskDownloadView $taskDownloadView 
     * @param TaskUpload $taskUpload
     * @param TaskUploadFile $taskUploadFile 
     * @param TaskUploadView $taskUploadView 
     * @param Task $task
     * @param TasksFiles $tasksFiles
     * @param Hashtag $hashtag
     */
    public function __construct(
        AppUserRepository $appuserRepo,
        HashtagRepository $hashtagRepo,
        TaskDownloadFile $taskDownloadFile, 
        TaskDownload $taskDownload,
        TaskDownloadView $taskDownloadView,
        TaskUpload $taskUpload,
        TaskUploadFile $taskUploadFile,
        TaskUploadView $taskUploadView,        
        Task $task, 
        TasksFiles $tasksFiles,
        Hashtag $hashtag
    )
    {
        $this->appuserRepo = $appuserRepo;
        $this->hashtagRepo = $hashtagRepo;

        $this->taskDownloadFile = $taskDownloadFile;
        $this->taskDownload = $taskDownload;
        $this->taskDownloadView = $taskDownloadView;

        $this->taskUploadFile = $taskUploadFile;
        $this->taskUpload = $taskUpload;
        $this->taskUploadView = $taskUploadView;

        $this->task = $task;
        $this->tasksFiles = $tasksFiles;

        $this->hashtag = $hashtag;
    }


    /**
     * Show manage company departments Table
     *
     * @author sigmoswitch
     * @return \Illuminate\View\View
     */
    public function index($isReloaded = false, $varSection = null)
    {
        $route = request()->route()->getName();
        $section = val(explode('.', $route)[0], 'none');

        if($isReloaded == true){
            $section = $varSection;
        }

        $auth_user_permissions = $this->appuserRepo->getAuthUserDetails()->permissions;
        $auth_user_permissions = explode(';', $auth_user_permissions);
        

        //$groups = $this->groupRepo->getAll();
        $groups = [];
        //$teams = $this->teamRepo->getAll();
        $teams = [];
        
        $timesheet_period = getPeriod_ajax();
        /**dd($timesheet_period);*/
        
        $locale = app()->getLocale();       

        $periodKey = sprintf(config('base.cache.key.period'), auth()->id());
        if (!Cache::has($periodKey)) {
            Cache::put($periodKey, date('Y-m-01'), config('base.cache.time.short'));
        }
        $default_datepicker_date = date('Y-m-01', strtotime(Cache::get($periodKey)));



        switch ($locale) {
            case 'vi':
            $format = CASE_VI_DATE_FORMAT;
            break;
            case 'en':
            $format = CASE_EN_DATE_FORMAT;
            break;
            case 'de':
            $format = CASE_DE_DATE_FORMAT;
            break;
            default:
            $format = CASE_DEFAULT_DATE_FORMAT;
        }

        $minDatefordatefromfilter = Carbon::now()->firstOfMonth()->subMonth()->subYears(1)->format($format);
        $maxDatefordatefromfilter = Carbon::now()->addYears(1)->lastOfMonth()->format($format);
        /**dd($maxDatefordatefromfilter);*/
        
        $tabHTML_downloadlistInfo = route('AjaxManagedownloadlistTabsController.ajaxdownloadlistInfo_HTML');
        $getdownloadlistinfo_db_table = route('AjaxManagedownloadlistTabsController.getdownloadlistInfo_TABLE');
        $URL_getdownloadlistinfoExportExcel = '';/** route('export.AjaxExcelExportRoutingsController.getdownloadlistReportinfoExportExcel') */
        
        $manage_add_downloadlist = route('downloadlist.add_downloadlist');
        //$URL_replace_shift_with_another = route('working_shift.replace_shift_with_another_html');
        $URL_replace_shift_with_another = '';



        /** the idea of getting all the active from LDAP */


        $pastthreemonthsfromtoday_date = Carbon::now()->subMonth(3);
        /**dd($pastthreemonthsfromtoday_date);*/
        
        /** only get users in the 3D department */
        if(env("LDAP_ACCOUNT_SUFFIX") === "@AD.HANOI.BR24.VN"){
            /** new domain has not got OUs so search the long way */
            $CN_3D = LdapModelAdGroup::find('CN=3D,CN=Users,DC=ad,DC=hanoi,DC=br24,DC=vn');
            // Retrieve the members that belong to the above group.
            $users = $CN_3D->members()->get();
            /**dd($users);*/
        }elseif(env("LDAP_ACCOUNT_SUFFIX") === "@br24.int"){
            $ou = Adldap::search()->ous()->where('ou', '=', '3D')->first();
            /**dd($ou);*/
            $users = Adldap::search()->users()->setDn($ou->getDn())->get();
            $users = Adldap::search()->users()->get();
            /**dd($users);*/
        }else{
            $users = [];
        }
        /**echo '<pre>';print_r($users);echo '</pre>';die();*/
        /**dd($users);*/


        // $selectize_employee_list_formated_json = DB::table('v_upload_files')->where('created_at', '>=', $pastthreemonthsfromtoday_date)->get()->toArray();
        // dd($selectize_employee_list_formated_json);


        $selectize_employee_list_formated_json = [];

        $assignees = [];
        $anew_counter = 0;
        foreach($users as $generic_key => $ldap_user_details){
            /**dump($ldap_user_details->getName());*/

            $getNameValue = $ldap_user_details->getName();
            $getDisplayName = $ldap_user_details->getFirstAttribute('displayName');

            /**dd($ldap_user_details->getName());*/
            if(!in_array($getNameValue, ['Administrator', 'Guest', 'krbtgt', 'it', 'read-only', 'test'])){

                /**dd($ldap_user_details);*/
                /**dump($ldap_user_details->getFirstAttribute('displayName'));*/

                $selectize_employee_list_formated_json[$anew_counter] = new \StdClass;
                $selectize_employee_list_formated_json[$anew_counter]->username = $getNameValue;
                $selectize_employee_list_formated_json[$anew_counter]->fullname = $getDisplayName;
                $selectize_employee_list_formated_json[$anew_counter]->fullname_noaccents = remove_vietnamese_accents($getDisplayName);

                $assignees[$anew_counter] = $getNameValue;
                $anew_counter++;
            }
        }
        /**dd($selectize_employee_list_formated_json);*/
        $selectize_selected_employee_has_family_members_in_company_formated_json = [
            // 0 => [ 'case_id' => '10101010'],
            // 1 => [ 'case_id' => '11433664'],
            // 2 => [ 'case_id' => '11434021'],
        ];
        /**dd($selectize_selected_employee_has_family_members_in_company_formated_json);*/

        /**dd($assignees);*/

        $all_hashtags_available = DB::table('hashtags')->get()->keyBy('id')->toArray();
        /**dd($all_hashtags_available);*/

        $selectize_hashtag_list_formated_json = [];

        $anew_counter = 0;
        foreach($all_hashtags_available as $generic_key => $hashtag_detail){
            $selectize_hashtag_list_formated_json[$anew_counter] = new \StdClass;
            $selectize_hashtag_list_formated_json[$anew_counter]->name = '#'.$hashtag_detail->name;
            $anew_counter++;
        }

        $selectize_selected_hashtags_formated_json = [];



        $change_assignees_for_job = route('downloadlist.postModifyAssigneesforJob');
        $change_deliverydate_for_job = route('downloadlist.postModifyDeliveryDateTimeforJob');
        $change_custom_color_for_job = route('downloadlist.postModifyCustomColorforJob');
        $change_status_for_job = route('downloadlist.postModifyStatusforJob');
        $change_custom_internal_note_for_job = route('downloadlist.postModifyInternalNoteforJob');
        $change_custom_star_rating_note_for_job = route('downloadlist.postModifyStarRatingNoteforJob');
        $change_star_rating_for_job = route('downloadlist.postModifyStarRatingforJob');
        $reset_star_rating_for_job = route('downloadlist.postResetStarRatingforJob');
        $change_tags_for_job = route('downloadlist.postModifytagsforJob');
        $change_custom_output_expected_for_job = route('downloadlist.postModifyOutputExpectedforJob');

        $job_statuses = config('base.job_statuses');

        $ajax_getAccountingPITMathOperatorSelectOptionList = $this->getAccountingPITMathOperatorSelectOptionList();
        /**dd($ajax_getAccountingPITMathOperatorSelectOptionList);*/

        $urlSyncReviewRequiredStatus = route('sync.previewrequiredstatus');

        /** also need to support other browsers */
        /**https://addons.mozilla.org/en-US/firefox/addon/local-filesystem-links/*/
        /**syntax for the browsing localfile with firefox*/
        /**file://///192.168.1.3/jobs/11455743/*/

        /** need this chrom extension */
        /**https://chrome.google.com/webstore/detail/enable-local-file-links/nikfmfgobenbhmocjaaboihbeocackld?hl=en*/
        /**syntax for the browsing localfile with chrome*/
        /**file://192.168.1.3/jobs/11455743/*/
        $browser = new Browser;
        /**dd($browser->getName());*/
        if($browser->getName() === Browser::FIREFOX){
        }
        if($browser->getName() === Browser::CHROME){
        }

        $browser_detected = $browser->getName();

        $this->data = compact('timesheet_period');
        $this->js = compact('timesheet_period', 'locale', 'groups', 'teams', 'section', 'auth_user_permissions', 'tabHTML_downloadlistInfo', 'getdownloadlistinfo_db_table', 'manage_add_downloadlist', 'URL_replace_shift_with_another', 'default_datepicker_date',  'URL_getdownloadlistinfoExportExcel', 'minDatefordatefromfilter', 'maxDatefordatefromfilter', 'selectize_employee_list_formated_json', 'selectize_selected_employee_has_family_members_in_company_formated_json', 'selectize_hashtag_list_formated_json', 'selectize_selected_hashtags_formated_json', 'change_assignees_for_job', 'change_deliverydate_for_job', 'change_custom_color_for_job', 'change_status_for_job', 'change_custom_internal_note_for_job', 'change_custom_star_rating_note_for_job', 'change_star_rating_for_job', 'reset_star_rating_for_job', 'change_tags_for_job', 'job_statuses', 'ajax_getAccountingPITMathOperatorSelectOptionList', 'urlSyncReviewRequiredStatus', 'change_custom_output_expected_for_job', 'browser_detected', 'assignees');

        if($isReloaded == true){
            return $this->js;
        }

        return $this->render('manage_downloadlist.index');
    }

    /**
     * add downloadlist info
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postAdddownloadlistInfo(Request $request)
    {
        $whattheysent = $request->all();
        /**dd($whattheysent);*/
        $locale = app()->getLocale();

        switch ($locale) {
            case 'vi':
            $format = CASE_VI_DATE_FORMAT;
            break;
            case 'en':
            $format = CASE_EN_DATE_FORMAT;
            break;
            case 'de':
            $format = CASE_DE_DATE_FORMAT;
            break;
            default:
            $format = CASE_DEFAULT_DATE_FORMAT;
        }

        if (isset($whattheysent['calendar_date'])){
            $calendar_date_reformat = Carbon::createFromFormat($format, $whattheysent['calendar_date']);
            $whattheysent['calendar_date'] = Carbon::parse($calendar_date_reformat)->format('Y-m-d');
        }

        /**dd($whattheysent);*/
        $whattheysent['last_updated_by'] = $this->appuserRepo->getAuthUserDetails()->user_id;

        $request->replace($whattheysent);


        $messages = [
            'name.regex' => 'The name may only contain letters and no spaces.'
        ];

        $validator = Validator::make($whattheysent, [
            "calendar_date" => 'required|date',
            "advance_amount" => [
                'required',
                'numeric',
                'regex:/^[1-9][0-9]*$/',
                'min:10000',
                'max:2147483647'
            ],
            "reason" => '',
            "payment_period_description" => '',
            "recipients" => 'required',

        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error_from_validator' => 'error_from_validator',
                'errors' => $validator->errors()->all(),
                'whattheysent' => $whattheysent
            ]);
        }


        $array_for_downloadlistrepo = [];

        foreach ($whattheysent as $key => $value){
            if($key == "recipients"){ $array_for_downloadlistrepo["fk_user_id"] = $value; }
            if($key == "calendar_date"){ $array_for_downloadlistrepo["calendar_date"] = $value; }
            if($key == "advance_amount"){ $array_for_downloadlistrepo["advance_amount"] = $value; }
            if($key == "reason"){ $array_for_downloadlistrepo["advance_reason"] = $value; }
            if($key == "payment_period_description"){ $array_for_downloadlistrepo["advance_period"] = $value; }

            if($key == "last_updated_by"){ $array_for_downloadlistrepo["last_updated_by"] = $value; }
        }
        /**dump(null);*/
        /**dd($array_for_downloadlistrepo);*/

        try {
            DB::beginTransaction();

            $where = ['id' => 'i wiin'];
            $this->advancepaymentRepo->updateOrCreate($array_for_downloadlistrepo, $where);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Debugbar::addException($e);
            return response()->json([
                'success' => false,
                'error_from_catch' => 'error_from_catch',
                'errors' => $e,
                'whattheysent' => $whattheysent,
                'array_for_downloadlistrepo' => $array_for_downloadlistrepo
            ]);
        }

        return response()->json([
            'success' => true,
            'whattheysent' => $whattheysent,
            'array_for_downloadlistrepo' => $array_for_downloadlistrepo,
        ]);
    }

    /**
     * edit downloadlist info
     *
     * @param posteditdocumentinfo $request
     * @return \illuminate\http\redirectresponse
     */
    public function postEditdownloadlistInfo($id, Request $request)
    {
        try {
            $id = Crypt::decryptString($id);
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]); /** aborting */
        }

        $whattheysent = $request->all();
        /**dd($whattheysent);*/
        $locale = app()->getLocale();

        switch ($locale) {
            case 'vi':
            $format = CASE_VI_DATE_FORMAT;
            break;
            case 'en':
            $format = CASE_EN_DATE_FORMAT;
            break;
            case 'de':
            $format = CASE_DE_DATE_FORMAT;
            break;
            default:
            $format = CASE_DEFAULT_DATE_FORMAT;
        }

        if (isset($whattheysent['calendar_date'])){
            $calendar_date_reformat = Carbon::createFromFormat($format, $whattheysent['calendar_date']);
            $whattheysent['calendar_date'] = Carbon::parse($calendar_date_reformat)->format('Y-m-d');
        }

        $whattheysent['last_updated_by'] = $this->appuserRepo->getAuthUserDetails()->user_id;

        $request->replace($whattheysent);
        /**dd($request->all());*/

        $messages = [
            'name.regex' => 'The name may only contain letters and no spaces.'
        ];

        $validator = Validator::make($whattheysent, [
            "calendar_date" => 'required|date',
            "advance_amount" => [
                'required',
                'numeric',
                'regex:/^[1-9][0-9]*$/',
                'min:10000',
                'max:2147483647'
            ],
            "reason" => '',
            "payment_period_description" => '',
            "recipients" => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error_from_validator' => 'error_from_validator',
                'errors' => $validator->errors()->all(),
                'whattheysent' => $whattheysent
            ]);
        }


        $array_for_downloadlistrepo = [];

        foreach ($whattheysent as $key => $value){
            if($key == "recipients"){ $array_for_downloadlistrepo["fk_user_id"] = $value; }
            if($key == "calendar_date"){ $array_for_downloadlistrepo["calendar_date"] = $value; }
            if($key == "advance_amount"){ $array_for_downloadlistrepo["advance_amount"] = $value; }
            if($key == "reason"){ $array_for_downloadlistrepo["advance_reason"] = $value; }
            if($key == "payment_period_description"){ $array_for_downloadlistrepo["advance_period"] = $value; }

            if($key == "last_updated_by"){ $array_for_downloadlistrepo["last_updated_by"] = $value; }
        }
        /**dump(null);*/
        /**dd($array_for_downloadlistrepo);*/

        /**return response()->json(['success' => false,'whattheysent' => $whattheysent,'array_for_workingshiftrepo' => $array_for_workingshiftrepo]);*/

        try {
            DB::beginTransaction();

            $where = ['id' => $id];
            $this->advancepaymentRepo->updateOrCreate($array_for_downloadlistrepo, $where);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Debugbar::addException($e);
            return response()->json([
                'success' => false,
                'error_from_catch' => 'error_from_catch',
                'errors' => $e,
                'whattheysent' => $whattheysent,
                'array_for_downloadlistrepo' => $array_for_downloadlistrepo
            ]);
        }

        return response()->json([
            'success' => true,
            'whattheysent' => $whattheysent,
            'array_for_downloadlistrepo' => $array_for_downloadlistrepo
        ]);
    }

    /**
     * replace downloadlist with another html info
     *
     * @param posteditdocumentinfo $request
     * @return \illuminate\http\redirectresponse
     */
    public function getReplacedownloadlistHTML($id)
    {
        try {
            $id = Crypt::decryptString($id);
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]); /** aborting */
        }

        /**dump($id);*/

        /** want to remove the one clicked on from the selectize list and also those that are disabled already */

        /** we need all the working shift types */
        $selectize_working_shifts_formated_json = DB::table('working_shifts')->select('id', 'name', 'symbol')->where('name', '<>', 'None')->where('disabled', '=', '1')->whereNotIn('id', [$id])->get()->toArray();
        /**dd($selectize_working_shifts_formated_json);*/

        //$selectize_selected_employee_default_shifts_formated_json = DB::table('default_shifts')->where('fk_user_id', $userId)->get()->toArray();
        /**dd($selectize_selected_employee_default_shifts_formated_json);*/
        /** to get the shifts assigned for the employee */

        $today = Carbon::now()->format('Y-m-d');

        $this->data = compact('today');
        $this->js = compact('selectize_working_shifts_formated_json');

        return $this->render('manage_working_shifts.replace_working_shift');
    }


    /**
     * delete downloadlist info
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postDeletedownloadlistInfo($id)
    {
        try {
            $id = Crypt::decryptString($id);
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]); /** aborting */
        }

        /**$locale = app()->getLocale();*/
        /**
         *  // switch ($locale) {
         *  //  case 'vi':
         *  //  $format = CASE_VI_DATE_FORMAT;
         *  //  break;
         *  //  case 'en':
         *  //  $format = CASE_EN_DATE_FORMAT;
         *  //  break;
         *  //  case 'de':
         *  //  $format = CASE_DE_DATE_FORMAT;
         *  //  break;
         *  //  default:
         *  //  $format = CASE_DEFAULT_DATE_FORMAT;
         *  // }
         */

        try {
            DB::beginTransaction();

            if ($this->advancepaymentRepo->delete($id)) {
                session()->flash('message', trans('message.attendance_status.deleted.successful'));
            }

            DB::commit();
            /**cleanCache();*/
        } catch (\Exception $e) {
            DB::rollBack();
            Debugbar::addException($e);
            $error_report = report($e);
            return response()->json([
                'success' => false,
                'error_from_catch' => 'error_from_catch',
                'errors' => $e,
                'error_report' => $error_report
            ]);
        }

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * enable downloadlist info
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postEnabledownloadlistInfo($id)
    {
        try {
            $id = Crypt::decryptString($id);
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]); /** aborting */
        }

        /**$locale = app()->getLocale();*/
        /**
         *  // switch ($locale) {
         *  //  case 'vi':
         *  //  $format = CASE_VI_DATE_FORMAT;
         *  //  break;
         *  //  case 'en':
         *  //  $format = CASE_EN_DATE_FORMAT;
         *  //  break;
         *  //  case 'de':
         *  //  $format = CASE_DE_DATE_FORMAT;
         *  //  break;
         *  //  default:
         *  //  $format = CASE_DEFAULT_DATE_FORMAT;
         *  // }
         */

        // return response()->json([
        //  'success' => false,
        //  'id' => $id,
        //  'disable' => 'disable',
        //  'process_penalties_accept_reject_table_sync' => false
        // ]);

        try {
            DB::beginTransaction();

            $array_for_workingshiftrepo['disabled'] = 1;
            $where = ['id' => $id];

            if ($this->customeventscheduleRepo->updateOrCreate($array_for_workingshiftrepo, $where)) {
                session()->flash('message', trans('message.attendance_status.deleted.successful'));
            }

            DB::commit();
            /**cleanCache();*/
        } catch (\Exception $e) {
            DB::rollBack();
            Debugbar::addException($e);
            $error_report = report($e);
            return response()->json([
                'success' => false,
                'error_from_catch' => 'error_from_catch',
                'errors' => $e,
                'error_report' => $error_report
            ]);
        }

        return response()->json([
            'success' => true,
            'process_penalties_accept_reject_table_sync' => true
        ]);
    }

    /**
     * disable downloadlist info
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postDisabledownloadlistInfo($id)
    {
        try {
            $id = Crypt::decryptString($id);
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]); /** aborting */
        }

        /**$locale = app()->getLocale();*/
        /**
         *  // switch ($locale) {
         *  //  case 'vi':
         *  //  $format = CASE_VI_DATE_FORMAT;
         *  //  break;
         *  //  case 'en':
         *  //  $format = CASE_EN_DATE_FORMAT;
         *  //  break;
         *  //  case 'de':
         *  //  $format = CASE_DE_DATE_FORMAT;
         *  //  break;
         *  //  default:
         *  //  $format = CASE_DEFAULT_DATE_FORMAT;
         *  // }
         */

        // return response()->json([
        //  'success' => false,
        //  'id' => $id,
        //  'disable' => 'disable',
        //  'process_penalties_accept_reject_table_sync' => false
        // ]);

        try {
            DB::beginTransaction();

            $array_for_workingshiftrepo['disabled'] = 2;
            $where = ['id' => $id];

            if ($this->customeventscheduleRepo->updateOrCreate($array_for_workingshiftrepo, $where)) {
                session()->flash('message', trans('message.attendance_status.deleted.successful'));
            }

            DB::commit();
            /**cleanCache();*/
        } catch (\Exception $e) {
            DB::rollBack();
            Debugbar::addException($e);
            $error_report = report($e);
            return response()->json([
                'success' => false,
                'error_from_catch' => 'error_from_catch',
                'errors' => $e,
                'error_report' => $error_report
            ]);
        }

        return response()->json([
            'success' => true,
            'process_penalties_accept_reject_table_sync' => true
        ]);
    }


    /**
     * replace downloadlist info
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postReplacedownloadlistInfo($id, Request $request)
    {
        try {
            $id = Crypt::decryptString($id);
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]); /** aborting */
        }

        /**dd($request->all());*/
        $whattheysent = $request->all();

        /**$locale = app()->getLocale();*/
        /**
         *  // switch ($locale) {
         *  //  case 'vi':
         *  //  $format = CASE_VI_DATE_FORMAT;
         *  //  break;
         *  //  case 'en':
         *  //  $format = CASE_EN_DATE_FORMAT;
         *  //  break;
         *  //  case 'de':
         *  //  $format = CASE_DE_DATE_FORMAT;
         *  //  break;
         *  //  default:
         *  //  $format = CASE_DEFAULT_DATE_FORMAT;
         *  // }
         */


        $new_workingshift_id = implode($whattheysent['replace_with_working_shift']);
        /**dd($new_workingshift_id);*/

        /** where do i have to update this working shift Replace in assigned_shift and default_shift */

        $first_pass = DB::table('default_shifts')->where('fk_working_shift_id', $id)->get()->toArray();
        $second_pass = DB::table('default_shifts')->where('fk_swap_shift_id', $id)->get()->toArray();
        
        /** we will just leave the assigned_shifts table alone as it is otherwise many records will be changed */
        //$third_pass = DB::table('assigned_shifts')->where('fk_shift_id', $id)->get()->toArray();

        /**dump($first_pass);*/
        /**dump($second_pass);*/
        /**dd($third_pass);*/

        try {
            DB::beginTransaction();

            foreach($first_pass as $record_detail){
                DB::table('default_shifts')->where('id', $record_detail->id)->update([
                    'fk_working_shift_id' => $new_workingshift_id
                ]);
            }

            foreach($second_pass as $record_detail){
                DB::table('default_shifts')->where('id', $record_detail->id)->update([
                    'fk_swap_shift_id' => $new_workingshift_id
                ]);
            }

            // foreach($third_pass as $record_detail){
            //  DB::table('assigned_shifts')->where('id', $record_detail->id)->update([
            //      'fk_shift_id' => $new_workingshift_id
            //  ]);
            // }

            DB::commit();

            /** if all goes well and at this point we will turn the working shift to disabled */
            $array_for_workingshiftrepo['disabled'] = 2;
            $where = ['id' => $id];

            if ($this->customeventscheduleRepo->updateOrCreate($array_for_workingshiftrepo, $where)) {
                session()->flash('message', trans('message.attendance_status.deleted.successful'));
            }

            Loggy::write('replaced_downloadlist', Auth::user()->username .' -> replaced ' . $id . ' with ' . $new_workingshift_id);

        } catch (\Exception $e) {
            DB::rollBack();
            Debugbar::addException($e);
            $error_report = report($e);
            return response()->json([
                'success' => false,
                'error_from_catch' => 'error_from_catch',
                'errors' => $e,
                'error_report' => $error_report
            ]);
        }

        return response()->json([
            'success' => true,
            'process_penalties_accept_reject_table_sync' => true
        ]);
    }

    /**
     * modify assignees of job
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postModifyAssigneesforJob(Request $request)
    {
        $whattheysent = $request->all();
        /**dd($whattheysent);*/

        $locale = app()->getLocale();

        switch ($locale) {
            case 'en':
            $format = CASE_EN_DATE_FORMAT;
            $last_updated_format = CASE_EN_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'de':
            $format = CASE_DE_DATE_FORMAT;
            $last_updated_format = CASE_DE_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'vi':
            $format = CASE_VI_DATE_FORMAT;
            $last_updated_format = CASE_VI_LAST_UPDATED_DATE_FORMAT;
            break;
            default:
            $format = CASE_DEFAULT_DATE_FORMAT;
            $last_updated_format = CASE_DEFAULT_LAST_UPDATED_DATE_FORMAT;
        }

        try {
            $encrypted_case_id = Crypt::decryptString($whattheysent['encrypted_case_id']);
            
            if($whattheysent['case_id'] != (int)$encrypted_case_id ){
                return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
            }

        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
        }

        $last_updated_by = $this->appuserRepo->getAuthUserDetails()->user_id;

        $caseId = $encrypted_case_id;
        if(isset($whattheysent['assignees'])){
            $assignees = implode(" ", $whattheysent['assignees']);
        }else{
            $assignees = null;
        }
        try {
            DB::beginTransaction();

            $file = $this->taskDownload->where('case_id', $caseId)->first();

            $file->assignees = $assignees;
            $file->last_updated_by = $last_updated_by;
            $file->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $file->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Debugbar::addException($e);
            return response()->json([
                'success' => false,
                'error_from_catch' => 'error_from_catch',
                'errors' => $e,
                'whattheysent' => $whattheysent,
                'caseId' => $caseId
            ]);
        }

        $file = $this->taskDownloadView->where('case_id', $caseId)->first();

        return response()->json([
            'success' => true,
            'whattheysent' => $whattheysent,
            'caseId' => $caseId,
            'assignees' => $assignees,
            'last_updated_by_name' => $file->last_updated_by_name,
            'updated_at' => Carbon::now()->format($last_updated_format)
        ]);
    }

    /**
     * modify tags of job
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postModifytagsforJob(Request $request)
    {
        $whattheysent = $request->all();
        /**dd($whattheysent);*/

        $locale = app()->getLocale();

        switch ($locale) {
            case 'en':
            $format = CASE_EN_DATE_FORMAT;
            $last_updated_format = CASE_EN_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'de':
            $format = CASE_DE_DATE_FORMAT;
            $last_updated_format = CASE_DE_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'vi':
            $format = CASE_VI_DATE_FORMAT;
            $last_updated_format = CASE_VI_LAST_UPDATED_DATE_FORMAT;
            break;
            default:
            $format = CASE_DEFAULT_DATE_FORMAT;
            $last_updated_format = CASE_DEFAULT_LAST_UPDATED_DATE_FORMAT;
        }

        try {
            $encrypted_case_id = Crypt::decryptString($whattheysent['encrypted_case_id']);
            
            if($whattheysent['case_id'] != (int)$encrypted_case_id ){
                return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
            }
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
        }

        $last_updated_by = $this->appuserRepo->getAuthUserDetails()->user_id;


        $whattheysent['last_updated_by'] = $this->appuserRepo->getAuthUserDetails()->user_id;

        $request->replace($whattheysent);

        /**dd($whattheysent);*/


        $caseId = $encrypted_case_id;


        if(isset($whattheysent['hashtag'])){

            /** first we check if the hash tag already exists */
            try {
                DB::beginTransaction();
                foreach ($whattheysent['hashtag'] as $key => $value){

                    $hashtag_record = $this->hashtag->where('name', $value)->first();     
                    $array_for_hashtagrepo = [];
                    if($hashtag_record == null){
                        $array_for_hashtagrepo["name"] = $value;
                        $array_for_hashtagrepo["last_updated_by"] = $last_updated_by;

                        $where = ['id' => 'i wiin'];
                        $this->hashtag->updateOrCreate($where, $array_for_hashtagrepo);
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Debugbar::addException($e);
                return response()->json([
                    'success' => false,
                    'error_from_catch' => 'error_from_catch',
                    'errors' => $e,
                    'whattheysent' => $whattheysent,
                    'caseId' => $caseId
                ]);
            }

            $custom_hashtag = implode(" ", $whattheysent['hashtag']);
        }else{
            $custom_hashtag = null;
        }
        try {
            DB::beginTransaction();

            $file = $this->taskDownload->where('case_id', $caseId)->first();

            $file->custom_hashtag = $custom_hashtag;
            $file->last_updated_by = $last_updated_by;
            $file->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $file->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Debugbar::addException($e);
            return response()->json([
                'success' => false,
                'error_from_catch' => 'error_from_catch',
                'errors' => $e,
                'whattheysent' => $whattheysent,
                'caseId' => $caseId
            ]);
        }

        $file = $this->taskDownloadView->where('case_id', $caseId)->first();

        $all_hashtags_available = DB::table('hashtags')->get()->keyBy('id')->toArray();
        /**dd($all_hashtags_available);*/

        $selectize_hashtag_list_formated_json = [];

        $anew_counter = 0;
        foreach($all_hashtags_available as $generic_key => $hashtag_detail){
            $selectize_hashtag_list_formated_json[$anew_counter] = new \StdClass;
            $selectize_hashtag_list_formated_json[$anew_counter]->name = '#'.$hashtag_detail->name;
            $anew_counter++;
        }


        return response()->json([
            'success' => true,
            'whattheysent' => $whattheysent,
            'caseId' => $caseId,
            'last_updated_by_name' => $file->last_updated_by_name,
            'updated_at' => Carbon::now()->format($last_updated_format),
            'selectize_hashtag_list_formated_json' => $selectize_hashtag_list_formated_json
        ]);
    }


    /**
     * modify delivery datetime of job
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postModifyDeliveryDateTimeforJob(Request $request)
    {
        $whattheysent = $request->all();
        /**dd($whattheysent);*/
        $locale = app()->getLocale();

        switch ($locale) {
            case 'en':
            $format = CASE_EN_DATE_FORMAT;
            $last_updated_format = CASE_EN_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'de':
            $format = CASE_DE_DATE_FORMAT;
            $last_updated_format = CASE_DE_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'vi':
            $format = CASE_VI_DATE_FORMAT;
            $last_updated_format = CASE_VI_LAST_UPDATED_DATE_FORMAT;
            break;
            default:
            $format = CASE_DEFAULT_DATE_FORMAT;
            $last_updated_format = CASE_DEFAULT_LAST_UPDATED_DATE_FORMAT;
        }

        try {
            $encrypted_case_id = Crypt::decryptString($whattheysent['encrypted_case_id']);
            
            if($whattheysent['case_id'] != (int)$encrypted_case_id ){
                return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
            }
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
        }

        $caseId = $encrypted_case_id;
        $last_updated_by = $this->appuserRepo->getAuthUserDetails()->user_id;
        if(isset($whattheysent['new_delivery_datetime'])){
            $new_delivery_datetime = Carbon::createFromFormat('Y-m-d H:i', $whattheysent['new_delivery_datetime'])->format('Y-m-d H:i:s');
        }else{
            $new_delivery_datetime = null;
        }
        try {
            DB::beginTransaction();

            $file = $this->taskDownload->where('case_id', $caseId)->first();

            $file->custom_delivery_time = $new_delivery_datetime;
            $file->last_updated_by = $last_updated_by;
            $file->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $file->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Debugbar::addException($e);
            return response()->json([
                'success' => false,
                'error_from_catch' => 'error_from_catch',
                'errors' => $e,
                'whattheysent' => $whattheysent,
                'caseId' => $caseId
            ]);
        }

        $file = $this->taskDownloadView->where('case_id', $caseId)->first();

        return response()->json([
            'success' => true,
            'whattheysent' => $whattheysent,
            'caseId' => $caseId,
            'new_deliver_datetime' => $new_delivery_datetime,
            'last_updated_by_name' => $file->last_updated_by_name,
            'updated_at' => Carbon::now()->format($last_updated_format)
        ]);
    }


    /**
     * modify custom color of job
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postModifyCustomColorforJob(Request $request)
    {
        $whattheysent = $request->all();
        /**dd($whattheysent);*/
        $locale = app()->getLocale();

        switch ($locale) {
            case 'en':
            $format = CASE_EN_DATE_FORMAT;
            $last_updated_format = CASE_EN_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'de':
            $format = CASE_DE_DATE_FORMAT;
            $last_updated_format = CASE_DE_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'vi':
            $format = CASE_VI_DATE_FORMAT;
            $last_updated_format = CASE_VI_LAST_UPDATED_DATE_FORMAT;
            break;
            default:
            $format = CASE_DEFAULT_DATE_FORMAT;
            $last_updated_format = CASE_DEFAULT_LAST_UPDATED_DATE_FORMAT;
        }

        try {
            $encrypted_case_id = Crypt::decryptString($whattheysent['encrypted_case_id']);
            
            if($whattheysent['case_id'] != (int)$encrypted_case_id ){
                return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
            }

        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
        }

        $caseId = $encrypted_case_id;
        $last_updated_by = $this->appuserRepo->getAuthUserDetails()->user_id;
        if(isset($whattheysent['new_custom_color'])){
            $new_custom_color = str_replace("#", "", $whattheysent['new_custom_color']);

            if($new_custom_color == 'FFFFFF'){
                $new_custom_color = null;    
            }
        }else{
            $new_custom_color = null;
        }
        try {
            DB::beginTransaction();

            $file = $this->taskDownload->where('case_id', $caseId)->first();

            $file->custom_color = $new_custom_color;
            $file->last_updated_by = $last_updated_by;
            $file->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $file->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Debugbar::addException($e);
            return response()->json([
                'success' => false,
                'error_from_catch' => 'error_from_catch',
                'errors' => $e,
                'whattheysent' => $whattheysent,
                'caseId' => $caseId
            ]);
        }

        $file = $this->taskDownloadView->where('case_id', $caseId)->first();

        return response()->json([
            'success' => true,
            'whattheysent' => $whattheysent,
            'caseId' => $caseId,
            'custom_color' => $file->custom_color,
            'last_updated_by_name' => $file->last_updated_by_name,
            'updated_at' => Carbon::now()->format($last_updated_format)
        ]);
    }

    /**
     * modify status of job
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postModifyStatusforJob(Request $request)
    {
        $whattheysent = $request->all();
        /**dd($whattheysent);*/

        $locale = app()->getLocale();

        switch ($locale) {
            case 'en':
            $format = CASE_EN_DATE_FORMAT;
            $last_updated_format = CASE_EN_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'de':
            $format = CASE_DE_DATE_FORMAT;
            $last_updated_format = CASE_DE_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'vi':
            $format = CASE_VI_DATE_FORMAT;
            $last_updated_format = CASE_VI_LAST_UPDATED_DATE_FORMAT;
            break;
            default:
            $format = CASE_DEFAULT_DATE_FORMAT;
            $last_updated_format = CASE_DEFAULT_LAST_UPDATED_DATE_FORMAT;
        }

        try {
            $encrypted_case_id = Crypt::decryptString($whattheysent['encrypted_case_id']);
            
            if($whattheysent['case_id'] != (int)$encrypted_case_id ){
                return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
            }

        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
        }

        $caseId = $encrypted_case_id;
        $last_updated_by = $this->appuserRepo->getAuthUserDetails()->user_id;

        $job_statuses = config('base.job_statuses');
        /**dd($job_statuses);*/

        if(isset($whattheysent['new_status_id'])){
            $new_status = strtolower($job_statuses[$whattheysent['new_status_id']]['name']);
        }
        try {
            DB::beginTransaction();

            /** we need to update two tables why? */
            $file = $this->taskDownload->where('case_id', $caseId)->first();
            if($file){
                $file->state = $new_status;
                $file->last_updated_by = $last_updated_by;
                $file->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                $file->save();
            }

            $file = $this->taskUpload->where('case_id', $caseId)->first();
            if($file){
                $file->state = $new_status;
                $file->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                $file->save();
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Debugbar::addException($e);
            return response()->json([
                'success' => false,
                'error_from_catch' => 'error_from_catch',
                'errors' => $e,
                'whattheysent' => $whattheysent,
                'caseId' => $caseId
            ]);
        }

        $file = $this->taskDownloadView->where('case_id', $caseId)->first();

        return response()->json([
            'success' => true,
            'whattheysent' => $whattheysent,
            'caseId' => $caseId,
            'status' => $file->status_of_case,
            'last_updated_by_name' => $file->last_updated_by_name,  
            'updated_at' => Carbon::now()->format($last_updated_format)
        ]);
    }


    /**
     * modify custom internal note of job
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postModifyInternalNoteforJob(Request $request)
    {
        $whattheysent = $request->all();
        /**dd($whattheysent);*/
        $locale = app()->getLocale();

        switch ($locale) {
            case 'en':
            $format = CASE_EN_DATE_FORMAT;
            $last_updated_format = CASE_EN_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'de':
            $format = CASE_DE_DATE_FORMAT;
            $last_updated_format = CASE_DE_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'vi':
            $format = CASE_VI_DATE_FORMAT;
            $last_updated_format = CASE_VI_LAST_UPDATED_DATE_FORMAT;
            break;
            default:
            $format = CASE_DEFAULT_DATE_FORMAT;
            $last_updated_format = CASE_DEFAULT_LAST_UPDATED_DATE_FORMAT;
        }

        try {
            $encrypted_case_id = Crypt::decryptString($whattheysent['encrypted_case_id']);
            
            if($whattheysent['case_id'] != (int)$encrypted_case_id ){
                return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
            }
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
        }

        $caseId = $encrypted_case_id;
        $last_updated_by = $this->appuserRepo->getAuthUserDetails()->user_id;
        if(isset($whattheysent['new_custom_internal_note'])){
            if(str_replace(" ", "", $whattheysent['new_custom_internal_note']) != ""){
                $new_custom_internal_note = $whattheysent['new_custom_internal_note'];
            }else{
                $new_custom_internal_note = null;    
            }
        }else{
            $new_custom_internal_note = null;
        }
        try {
            DB::beginTransaction();

            $file = $this->taskDownload->where('case_id', $caseId)->first();

            $file->custom_internal_notes = $new_custom_internal_note;
            $file->last_updated_by = $last_updated_by;
            $file->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $file->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Debugbar::addException($e);
            return response()->json([
                'success' => false,
                'error_from_catch' => 'error_from_catch',
                'errors' => $e,
                'whattheysent' => $whattheysent,
                'caseId' => $caseId
            ]);
        }

        $file = $this->taskDownloadView->where('case_id', $caseId)->first();

        return response()->json([
            'success' => true,
            'whattheysent' => $whattheysent,
            'caseId' => $caseId,
            'internal_note' => $file->custom_internal_notes,
            'last_updated_by_name' => $file->last_updated_by_name,  
            'updated_at' => Carbon::now()->format($last_updated_format)
        ]);
    }

    /**
     * modify star rating note of job
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postModifyStarRatingNoteforJob(Request $request)
    {
        $whattheysent = $request->all();
        /**dd($whattheysent);*/
        $locale = app()->getLocale();

        switch ($locale) {
            case 'en':
            $format = CASE_EN_DATE_FORMAT;
            $last_updated_format = CASE_EN_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'de':
            $format = CASE_DE_DATE_FORMAT;
            $last_updated_format = CASE_DE_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'vi':
            $format = CASE_VI_DATE_FORMAT;
            $last_updated_format = CASE_VI_LAST_UPDATED_DATE_FORMAT;
            break;
            default:
            $format = CASE_DEFAULT_DATE_FORMAT;
            $last_updated_format = CASE_DEFAULT_LAST_UPDATED_DATE_FORMAT;
        }

        try {
            $encrypted_case_id = Crypt::decryptString($whattheysent['encrypted_case_id']);
            
            if($whattheysent['case_id'] != (int)$encrypted_case_id ){
                return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
            }
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
        }

        $caseId = $encrypted_case_id;
        $last_updated_by = $this->appuserRepo->getAuthUserDetails()->user_id;
        if(isset($whattheysent['new_star_rating_custom_note'])){
            if(str_replace(" ", "", $whattheysent['new_star_rating_custom_note']) != ""){
                $new_star_rating_custom_note = $whattheysent['new_star_rating_custom_note'];
            }else{
                $new_star_rating_custom_note = null;    
            }
        }else{
            $new_star_rating_custom_note = null;
        }
        try {
            DB::beginTransaction();

            $file = $this->taskDownload->where('case_id', $caseId)->first();

            $file->custom_job_star_rating_comment = $new_star_rating_custom_note;
            $file->last_updated_by = $last_updated_by;
            $file->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $file->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Debugbar::addException($e);
            return response()->json([
                'success' => false,
                'error_from_catch' => 'error_from_catch',
                'errors' => $e,
                'whattheysent' => $whattheysent,
                'caseId' => $caseId
            ]);
        }

        $file = $this->taskDownloadView->where('case_id', $caseId)->first();

        return response()->json([
            'success' => true,
            'whattheysent' => $whattheysent,
            'caseId' => $caseId,
            'star_rating_comment' => $file->custom_job_star_rating_comment,
            'star_rating' => $file->custom_job_star_rating,
            'last_updated_by_name' => $file->last_updated_by_name,            
            'updated_at' => Carbon::now()->format($last_updated_format)
        ]);
    }

    /**
     * modify star rating of job
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postModifyStarRatingforJob(Request $request)
    {
        $whattheysent = $request->all();
        /**dd($whattheysent);*/
        $locale = app()->getLocale();

        switch ($locale) {
            case 'en':
            $format = CASE_EN_DATE_FORMAT;
            $last_updated_format = CASE_EN_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'de':
            $format = CASE_DE_DATE_FORMAT;
            $last_updated_format = CASE_DE_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'vi':
            $format = CASE_VI_DATE_FORMAT;
            $last_updated_format = CASE_VI_LAST_UPDATED_DATE_FORMAT;
            break;
            default:
            $format = CASE_DEFAULT_DATE_FORMAT;
            $last_updated_format = CASE_DEFAULT_LAST_UPDATED_DATE_FORMAT;
        }

        try {
            $encrypted_case_id = Crypt::decryptString($whattheysent['encrypted_case_id']);
            
            if($whattheysent['case_id'] != (int)$encrypted_case_id ){
                return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
            }
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
        }

        $caseId = $encrypted_case_id;
        $last_updated_by = $this->appuserRepo->getAuthUserDetails()->user_id;
        if(isset($whattheysent['new_star_rating_comment'])){
            if(str_replace(" ", "", $whattheysent['new_star_rating_comment']) != ""){
                $new_star_rating_comment = $whattheysent['new_star_rating_comment'];
            }else{
                $new_star_rating_comment = null;
                /** want to keep the existing */
            }
        }else{
            $new_star_rating_comment = null;
        }

        if(isset($whattheysent['new_star_rating'])){
            if(str_replace(" ", "", $whattheysent['new_star_rating']) != ""){
                $new_star_rating = $whattheysent['new_star_rating'];
            }else{
                $new_star_rating = null;    
            }
        }else{
            $new_star_rating = null;
        }

        try {
            DB::beginTransaction();

            $file = $this->taskDownload->where('case_id', $caseId)->first();

            if($new_star_rating_comment != null){
                $file->custom_job_star_rating_comment = $new_star_rating_comment;
            }
            $file->custom_job_star_rating = $new_star_rating;
            $file->last_updated_by = $last_updated_by;
            $file->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $file->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Debugbar::addException($e);
            return response()->json([
                'success' => false,
                'error_from_catch' => 'error_from_catch',
                'errors' => $e,
                'whattheysent' => $whattheysent,
                'caseId' => $caseId
            ]);
        }

        /** to correctly display the notes and tooltip icon when there are comments get the latest from the database and feed it back to the page */

        $file = $this->taskDownloadView->where('case_id', $caseId)->first();

        return response()->json([
            'success' => true,
            'whattheysent' => $whattheysent,
            'caseId' => $caseId,
            'star_rating_comment' => $file->custom_job_star_rating_comment,
            'star_rating' => $file->custom_job_star_rating,
            'last_updated_by_name' => $file->last_updated_by_name,
            'updated_at' => Carbon::now()->format($last_updated_format)
        ]);
    }


    /**
     * reset star rating of job
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postResetStarRatingforJob(Request $request)
    {
        $whattheysent = $request->all();
        /**dd($whattheysent);*/
        $locale = app()->getLocale();

        switch ($locale) {
            case 'en':
            $format = CASE_EN_DATE_FORMAT;
            $last_updated_format = CASE_EN_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'de':
            $format = CASE_DE_DATE_FORMAT;
            $last_updated_format = CASE_DE_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'vi':
            $format = CASE_VI_DATE_FORMAT;
            $last_updated_format = CASE_VI_LAST_UPDATED_DATE_FORMAT;
            break;
            default:
            $format = CASE_DEFAULT_DATE_FORMAT;
            $last_updated_format = CASE_DEFAULT_LAST_UPDATED_DATE_FORMAT;
        }

        try {
            $encrypted_case_id = Crypt::decryptString($whattheysent['encrypted_case_id']);
            
            if($whattheysent['case_id'] != (int)$encrypted_case_id ){
                return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
            }
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
        }

        $caseId = $encrypted_case_id;
        $last_updated_by = $this->appuserRepo->getAuthUserDetails()->user_id;
        if(isset($whattheysent['new_star_rating_comment'])){
            if(str_replace(" ", "", $whattheysent['new_star_rating_comment']) != ""){
                $new_star_rating_comment = $whattheysent['new_star_rating_comment'];
            }else{
                $new_star_rating_comment = null;    
            }
        }else{
            $new_star_rating_comment = null;
        }

        if(isset($whattheysent['new_star_rating'])){
            if(str_replace(" ", "", $whattheysent['new_star_rating']) != ""){
                $new_star_rating = $whattheysent['new_star_rating'];
            }else{
                $new_star_rating = null;    
            }
        }else{
            $new_star_rating = null;
        }

        try {
            DB::beginTransaction();

            $file = $this->taskDownload->where('case_id', $caseId)->first();

            $file->custom_job_star_rating_comment = $new_star_rating_comment;
            $file->custom_job_star_rating = $new_star_rating;
            $file->last_updated_by = $last_updated_by;
            $file->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $file->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Debugbar::addException($e);
            return response()->json([
                'success' => false,
                'error_from_catch' => 'error_from_catch',
                'errors' => $e,
                'whattheysent' => $whattheysent,
                'caseId' => $caseId
            ]);
        }

        $file = $this->taskDownloadView->where('case_id', $caseId)->first();

        return response()->json([
            'success' => true,
            'whattheysent' => $whattheysent,
            'caseId' => $caseId,
            'star_rating_comment' => $file->custom_job_star_rating_comment,
            'star_rating' => $file->custom_job_star_rating,
            'last_updated_by_name' => $file->last_updated_by_name,
            'updated_at' => Carbon::now()->format($last_updated_format)
        ]);
    }



    /**
     * Get Accounting PIT Math Operators Select Option List
     *
     * @author sigmoswitch
     */
    public function getAccountingPITMathOperatorSelectOptionList() //ManageAccountingPITConfigController SELECT OPTION LIST
    {
        /**$whattheysent = $request->all();*/
        

        $job_statuses = config('base.job_statuses');
        /**dd($job_statuses);*/

        $select_option = [];
        $counter = 0;
        foreach ($job_statuses as $key => $recordtype){
            /**dd($recordtype);*/

            if($recordtype['show_to_them'] == true){
                $select_option[$counter]['name'] = $recordtype['name'];
                //$select_option[$counter]['symbol'] = $recordtype['symbol'];
                $select_option[$counter]['id'] = $recordtype['id'];

                $counter++;
            }
        }

        /**dd($select_option);*/

        return response()->json($select_option);
    }


    /**
     *
     *
     */
    public function updatePreviewRequiredStatus(Request $request)
    {
        $whattheysent = $request->all();
        /**dd($whattheysent);*/

        /** should we check for the encrypted case_id so that any funny business is cut out */
        /**dd($whattheysent);*/
        $locale = app()->getLocale();

        switch ($locale) {
            case 'en':
            $format = CASE_EN_DATE_FORMAT;
            $last_updated_format = CASE_EN_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'de':
            $format = CASE_DE_DATE_FORMAT;
            $last_updated_format = CASE_DE_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'vi':
            $format = CASE_VI_DATE_FORMAT;
            $last_updated_format = CASE_VI_LAST_UPDATED_DATE_FORMAT;
            break;
            default:
            $format = CASE_DEFAULT_DATE_FORMAT;
            $last_updated_format = CASE_DEFAULT_LAST_UPDATED_DATE_FORMAT;
        }

        try {
            $encrypted_case_id = Crypt::decryptString($whattheysent['encrypted_case_id']);
            
            if($whattheysent['case_id'] != (int)$encrypted_case_id ){
                return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
            }
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
        }

        $caseId = $encrypted_case_id;
        $last_updated_by = $this->appuserRepo->getAuthUserDetails()->user_id;

        if((int)$whattheysent['status'] == 1){
            $response2 = app('App\Http\Controllers\OperatorController')->test_rocket_chat_server_online();
            /**dd($response2);*/
        }

        try {
            DB::beginTransaction();

            /** we mark the thing as archived in the db */
            $file = $this->taskDownload->where('case_id', $whattheysent['case_id'])->first();
            /**dd($file->getOriginal());*/
            $file->preview_req = (int)$whattheysent['status'];
            $file->last_updated_by = $last_updated_by;
            $file->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Debugbar::addException($e);
            return response()->json([
                'success' => false,
                'caseId' => $caseId,
                'error_from_catch' => 'error_from_catch',
                'errors' => $e,
                'whattheysent' => $whattheysent
            ]);
        }
        
        $file = $this->taskDownloadView->where('case_id', $caseId)->first();

        /**dump($file->getOriginal());*/
        /**dump($file->assignees);*/
        $assginees_to_send_message_to = explode(" ", $file->assignees);
        /**dd($assginees_to_send_message_to);*/
        /** need to send a message to the assignees after the preview required flag is properly set on the db */
        /** what happens if the rocketchat server is down? */
        /** we should probably check before doing anything */

        if((int)$whattheysent['status'] == 1){
            $message = $caseId . ' requires a preview file.';
            app('App\Http\Controllers\OperatorController')->send_rocket_chat_message($assginees_to_send_message_to, $message);
        }

        return response()->json([
            'success' => true,
            'whattheysent' => $whattheysent,
            'caseId' => $caseId,
            'preview_req' => $file->preview_req,
            'last_updated_by_name' => $file->last_updated_by_name,
            'updated_at' => Carbon::now()->format($last_updated_format)
        ]);

    }

    /**
     * modify custom output expected (files) of job
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postModifyOutputExpectedforJob(Request $request)
    {
        $whattheysent = $request->all();
        /**dd($whattheysent);*/
        $locale = app()->getLocale();

        switch ($locale) {
            case 'en':
            $format = CASE_EN_DATE_FORMAT;
            $last_updated_format = CASE_EN_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'de':
            $format = CASE_DE_DATE_FORMAT;
            $last_updated_format = CASE_DE_LAST_UPDATED_DATE_FORMAT;
            break;
            case 'vi':
            $format = CASE_VI_DATE_FORMAT;
            $last_updated_format = CASE_VI_LAST_UPDATED_DATE_FORMAT;
            break;
            default:
            $format = CASE_DEFAULT_DATE_FORMAT;
            $last_updated_format = CASE_DEFAULT_LAST_UPDATED_DATE_FORMAT;
        }

        try {
            $encrypted_case_id = Crypt::decryptString($whattheysent['encrypted_case_id']);
            
            if($whattheysent['case_id'] != (int)$encrypted_case_id ){
                return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
            }
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]);
        }

        $caseId = $encrypted_case_id;
        $last_updated_by = $this->appuserRepo->getAuthUserDetails()->user_id;
        if(isset($whattheysent['new_custom_output_expected']) && is_numeric($whattheysent['new_custom_output_expected'])){
            if($whattheysent['new_custom_output_expected'] >= 1){
                /** we need to check if it is a number */
                $new_custom_output_expected = $whattheysent['new_custom_output_expected'];
            }else{
                $new_custom_output_expected = null;    
            }
        }else{
            return response()->json([ 'success' => false, 'errors' => ["Value is not a number"]]);
            $new_custom_output_expected = null;
        }


        try {
            DB::beginTransaction();

            $file = $this->taskDownload->where('case_id', $caseId)->first();

            $file->custom_output_expected = $new_custom_output_expected;
            $file->last_updated_by = $last_updated_by;
            $file->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $file->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Debugbar::addException($e);
            return response()->json([
                'success' => false,
                'error_from_catch' => 'error_from_catch',
                'errors' => $e,
                'whattheysent' => $whattheysent,
                'caseId' => $caseId
            ]);
        }

        $file = $this->taskDownloadView->where('case_id', $caseId)->first();

        return response()->json([
            'success' => true,
            'whattheysent' => $whattheysent,
            'caseId' => $caseId,
            'internal_note' => $file->custom_internal_notes,
            'last_updated_by_name' => $file->last_updated_by_name,  
            'updated_at' => Carbon::now()->format($last_updated_format)
        ]);
    }
}
