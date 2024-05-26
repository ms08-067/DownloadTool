<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Storage;

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
use Artisan;

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

use File;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

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

use App\Models\TaskManualDownloadFile;
use App\Models\TaskManualDownload;
use App\Models\TaskManualDownloadView;
use App\Models\TaskManualUpload;
use App\Models\TaskManualUploadView;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

/**
 * Class ManagemanualdownloadlistController
 *
 * @author sigmoswitch
 * @package App\Http\Controllers
 */
class ManagemanualdownloadlistController extends Controller
{
    public $taskUploadFile;
    public $TaskUploadView;
    public $taskUpload;
    public $taskDownloadFile;
    public $taskDownload;
    public $task;
    public $tasksFiles;
    public $hashtag;

    public $taskManualDownloadFile;
    public $taskManualDownload;
    public $taskManualDownloadView;
    public $taskManualUpload;
    public $taskManualUploadView;


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
        // TaskDownloadFile $taskDownloadFile, 
        // TaskDownload $taskDownload,
        // TaskDownloadView $taskDownloadView,
        // TaskUpload $taskUpload,
        // TaskUploadFile $taskUploadFile,
        // TaskUploadView $taskUploadView,
        Task $task, 
        TasksFiles $tasksFiles,
        Hashtag $hashtag,
        TaskManualDownloadFile $taskManualDownloadFile, 
        TaskManualDownload $taskManualDownload,
        TaskManualDownloadView $taskManualDownloadView,
        TaskManualUpload $taskManualUpload,
        TaskManualUploadView $taskManualUploadView
    )
    {
        $this->appuserRepo = $appuserRepo;
        $this->hashtagRepo = $hashtagRepo;

        // $this->taskDownloadFile = $taskDownloadFile;
        // $this->taskDownload = $taskDownload;
        // $this->taskDownloadView = $taskDownloadView;

        // $this->taskUploadFile = $taskUploadFile;
        // $this->taskUpload = $taskUpload;
        // $this->taskUploadView = $taskUploadView;

        $this->task = $task;
        $this->tasksFiles = $tasksFiles;

        $this->hashtag = $hashtag;


        $this->taskManualDownloadFile = $taskManualDownloadFile;
        $this->taskManualDownload = $taskManualDownload;
        $this->taskManualDownloadView = $taskManualDownloadView;

        $this->taskManualUpload = $taskManualUpload;
        $this->taskManualUploadView = $taskManualUploadView;
    }


    /**
     * Show manual manage download list
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
        
        $tabHTML_manualdownloadlistInfo = route('AjaxManagemanualdownloadlistTabsController.ajaxmanualdownloadlistInfo_HTML');
        $getmanualdownloadlistinfo_db_table = route('AjaxManagemanualdownloadlistTabsController.getmanualdownloadlistInfo_TABLE');
        $URL_getmanualdownloadlistinfoExportExcel = '';/** route('export.AjaxExcelExportRoutingsController.getmanualdownloadlistReportinfoExportExcel') */
        
        $manage_add_manualdownloadlist = route('manualdownloadlist.add_manualdownloadlist');
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



        $change_assignees_for_job = route('manualdownloadlist.postModifyAssigneesforJob');
        $change_deliverydate_for_job = route('manualdownloadlist.postModifyDeliveryDateTimeforJob');
        $change_custom_color_for_job = route('manualdownloadlist.postModifyCustomColorforJob');
        $change_status_for_job = route('manualdownloadlist.postModifyStatusforJob');
        $change_custom_internal_note_for_job = route('manualdownloadlist.postModifyInternalNoteforJob');
        $change_custom_star_rating_note_for_job = route('manualdownloadlist.postModifyStarRatingNoteforJob');
        $change_star_rating_for_job = route('manualdownloadlist.postModifyStarRatingforJob');
        $reset_star_rating_for_job = route('manualdownloadlist.postResetStarRatingforJob');
        $change_tags_for_job = route('manualdownloadlist.postModifytagsforJob');
        $change_custom_output_expected_for_job = route('manualdownloadlist.postModifyOutputExpectedforJob');

        $job_statuses = config('base.job_statuses');

        $ajax_getAccountingPITMathOperatorSelectOptionList = $this->getAccountingPITMathOperatorSelectOptionList();
        /**dd($ajax_getAccountingPITMathOperatorSelectOptionList);*/

        $urlSyncReviewRequiredStatus = route('sync.previewrequiredstatus_manualdownload');
        $manual_download_scan_initiate = route('manualdownloadlist.manual_download_scan_initiate');
        $manual_download_actually_start_downloading = route('manualdownloadlist.manual_download_actually_start_downloading');
        $urlGetZipDetailsOfJob = route('manualdownloadlist.getZipDetailsOfCaseID');
        $urlGetAria2cDownloadProgressDetailsOfCaseID = route('manualdownloadlist.getAria2cDownloadProgressDetailsOfCaseID');

        /**dd($urlGetAria2cDownloadProgressDetailsOfCaseID);*/

        $currently_downloading_aria2c = DB::table('tasks_manual_downloads_files')->select('case_id', 'type')->whereNotIn('state', ['notified'])->get()->groupBy('case_id')
        ->map(function ($ts) {
            return $ts->keyBy('type');
        })->toArray();

        foreach($currently_downloading_aria2c as $index_case_id => $type_details){
            foreach($type_details as $type_key => $dl_type_details){
                $currently_downloading_aria2c[$index_case_id][$type_key]->encrypted_type = Crypt::encryptString($dl_type_details->type);
                $currently_downloading_aria2c[$index_case_id][$type_key]->encrypted_case_id = Crypt::encryptString($dl_type_details->case_id);
            }
        }
        /**dd($currently_downloading_aria2c);*/

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
        $this->js = compact('timesheet_period', 'locale', 'groups', 'teams', 'section', 'auth_user_permissions', 'tabHTML_manualdownloadlistInfo', 'getmanualdownloadlistinfo_db_table', 'manage_add_manualdownloadlist', 'URL_replace_shift_with_another', 'default_datepicker_date',  'URL_getmanualdownloadlistinfoExportExcel', 'minDatefordatefromfilter', 'maxDatefordatefromfilter', 'selectize_employee_list_formated_json', 'selectize_selected_employee_has_family_members_in_company_formated_json', 'selectize_hashtag_list_formated_json', 'selectize_selected_hashtags_formated_json', 'change_assignees_for_job', 'change_deliverydate_for_job', 'change_custom_color_for_job', 'change_status_for_job', 'change_custom_internal_note_for_job', 'change_custom_star_rating_note_for_job', 'change_star_rating_for_job', 'reset_star_rating_for_job', 'change_tags_for_job', 'job_statuses', 'ajax_getAccountingPITMathOperatorSelectOptionList', 'urlSyncReviewRequiredStatus', 'change_custom_output_expected_for_job', 'browser_detected', 'assignees', 'manual_download_scan_initiate', 'manual_download_actually_start_downloading', 'urlGetZipDetailsOfJob', 'urlGetAria2cDownloadProgressDetailsOfCaseID', 'currently_downloading_aria2c');

        if($isReloaded == true){
            return $this->js;
        }

        return $this->render('manage_manualdownloadlist.index');
    }

    /**
     * add downloadlist info
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postAddmanualdownloadlistInfo(Request $request)
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
    public function postEditmanualdownloadlistInfo($id, Request $request)
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
    public function getReplacemanualdownloadlistHTML($id)
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
    public function postDeletemanualdownloadlistInfo($id)
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
    public function postEnablemanualdownloadlistInfo($id)
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
    public function postDisablemanualdownloadlistInfo($id)
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
    public function postReplacemanualdownloadlistInfo($id, Request $request)
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

            $file = $this->taskManualDownload->where('case_id', $caseId)->first();

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

        $file = $this->taskManualDownloadView->where('case_id', $caseId)->first();

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

            $file = $this->taskManualDownload->where('case_id', $caseId)->first();

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

        $file = $this->taskManualDownloadView->where('case_id', $caseId)->first();

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

            $file = $this->taskManualDownload->where('case_id', $caseId)->first();

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

        $file = $this->taskManualDownloadView->where('case_id', $caseId)->first();

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

            $file = $this->taskManualDownload->where('case_id', $caseId)->first();

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

        $file = $this->taskManualDownloadView->where('case_id', $caseId)->first();

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
            $file = $this->taskManualDownload->where('case_id', $caseId)->first();
            if($file){
                $file->state = $new_status;
                $file->last_updated_by = $last_updated_by;
                $file->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                $file->save();
            }

            $file = $this->taskManualUpload->where('case_id', $caseId)->first();
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

        $file = $this->taskManualDownloadView->where('case_id', $caseId)->first();

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

            $file = $this->taskManualDownload->where('case_id', $caseId)->first();

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

        $file = $this->taskManualDownloadView->where('case_id', $caseId)->first();

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

            $file = $this->taskManualDownload->where('case_id', $caseId)->first();

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

        $file = $this->taskManualDownloadView->where('case_id', $caseId)->first();

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

            $file = $this->taskManualDownload->where('case_id', $caseId)->first();

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

        $file = $this->taskManualDownloadView->where('case_id', $caseId)->first();

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

            $file = $this->taskManualDownload->where('case_id', $caseId)->first();

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

        $file = $this->taskManualDownloadView->where('case_id', $caseId)->first();

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
            $file = $this->taskManualDownload->where('case_id', $whattheysent['case_id'])->first();
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
        
        $file = $this->taskManualDownloadView->where('case_id', $caseId)->first();

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

            $file = $this->taskManualDownload->where('case_id', $caseId)->first();

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

        $file = $this->taskManualDownloadView->where('case_id', $caseId)->first();

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
     * process the checking of the entered caseID client side
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function manual_download_scan_initiate(Request $request)
    {
        /** need to scan */
        $whattheysent = $request->all();
        /**dump($whattheysent);*/

        $check_if_caseid_already_downloaded_before_but_was_archived = DB::table('v_manual_download_files')->where('case_id', $whattheysent['case_id'])->whereIn('archived_case', ['2'])->get()->keyBy('case_id')->toArray();
        // if(isset($check_if_caseid_already_downloaded_before_but_was_archived[$whattheysent['case_id']])){
        //     dump($check_if_caseid_already_downloaded_before_but_was_archived[$whattheysent['case_id']]);
        // }else{
        //     dump($check_if_caseid_already_downloaded_before_but_was_archived);
        // }

        if(!empty($check_if_caseid_already_downloaded_before_but_was_archived)){
            /** it was downloaded a long time ago but archived (making it already part of the table entries) so do not show the re-download alert just lead into the zip scan automatically */
            /** we have to perfom some maintainance beforehand to allowe for it to happen */
            /** to avoid the unique column constraint configured on the database for the task manual download table.. */

            /** a few thing you noticed is that it still checks whether the row in the db task_manual_downloads_files is changed or not */
            /** we have to let the tool download everything and at the users decision */
            /** what is the easiest way to get the tool back to that state */

            /** we certainly need to do the xml scan again to see if there is something to download */
            /** its only possible to be done from the search input field */
            /** however you need to check the steps it will go through for the best experience */

            /** similar to the tool is met with a new case id */

            /** do we want to preserve the rows on the db for their details?  NO */
            try {
                DB::beginTransaction();
                DB::table('tasks_manual_downloads')->where('case_id', $whattheysent['case_id'])->delete();
                DB::table('tasks_manual_downloads_files')->where('case_id', $whattheysent['case_id'])->delete();
                DB::table('tasks_manual_uploads')->where('case_id', $whattheysent['case_id'])->delete();
                DB::commit();
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
        }


        $check_if_caseid_already_downloaded_before = DB::table('v_manual_download_files')->where('case_id', $whattheysent['case_id'])->whereIn('archived_case', ['1'])->get()->keyBy('case_id')->toArray();
        // if(isset($check_if_caseid_already_downloaded_before[$whattheysent['case_id']])){
        //     dump($check_if_caseid_already_downloaded_before[$whattheysent['case_id']]);
        // }else{
        //     dump($check_if_caseid_already_downloaded_before);
        // }

        if(!empty($check_if_caseid_already_downloaded_before)){

            /** show a link to download again only after 1 hour expired from last update date time .. */
            $updated_at_timestamp = Carbon::createFromFormat('Y-m-d H:i:s', $check_if_caseid_already_downloaded_before[$whattheysent['case_id']]->updated_at)->timestamp;
            /**dd($updated_at_timestamp);*/
            if (time() - $updated_at_timestamp > 2 * 60 * 60) { 
                /** which button will do that? */
                if($check_if_caseid_already_downloaded_before[$whattheysent['case_id']]->status_of_case == 'new'){
                    $download_status = 'recently started downloading.';
                }else{
                    $download_status = 'already downloaded.';
                }
                $redownload_URL = route('manualdownloadlist.postModifyForcefullyReManualdownloadforJob', ['id' => Crypt::encryptString($whattheysent['case_id'])]);
                $link_to_download_again = $download_status.' <br><br><a name="redownload_manualdownloadlist_X" href="'.$redownload_URL.'" class="btn-info btn-xs" target="_blank" style="font-size: 0.8vw"><i class="fa fa-download"></i>&nbsp;&nbsp;Re-Download</a>';
            }else{
                $link_to_download_again = "recently started downloading. Please Try again in a few minutes.";
            }

            return response()->json([
                'success' => false,
                'error_from_downloaded_check' => 'error_from_downloaded_check',
                'errors' => ["CaseID ".$whattheysent['case_id']." ".$link_to_download_again],
                'whattheysent' => $whattheysent,
                'caseId' => $whattheysent['case_id'],
            ]);
        }

        $validator = Validator::make($whattheysent, [
            "case_id" => [
                'required',
                'numeric',
                'regex:/^[1-9][0-9]*$/',
                'min:10000000',
                'max:2147483647',
                'digits:8'
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error_from_validator' => 'error_from_validator',
                'errors' => $validator->errors()->all(),
                'whattheysent' => $whattheysent
            ]);
        }

        $status = app('App\Repositories\ManualS3Repository')->trigger_s3_xml_scanV2($whattheysent['case_id'], false);

        /**dump('inside manual_download_scan_initiate AFTER');*/
        /**dd($status);*/

        if($status['success'] == true){
            return response()->json([
                'success' => true,
                'whattheysent' => $whattheysent,
                'caseId' => $whattheysent['case_id'],
            ]);
        }else{
            return response()->json([
                'success' => false,
                'whattheysent' => $whattheysent,
                'caseId' => $whattheysent['case_id'],
                'errors' => [$status['errors']]
            ]);
        }
    }

    /**
     * process the actual download of the entered caseID client side
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function manual_download_actually_start_downloading(Request $request)
    {
        /** need to download this is only triggered on success finding the correct case. */
        $whattheysent = $request->all();
        /**dd($whattheysent);*/
        Loggy::write('manual_download_freshdownload', 'manual_download_actually_start_downloading() $whattheysent => ' . json_encode($whattheysent));

        $status = app('App\Repositories\ManualS3Repository')->trigger_s3_xml_scanV2($whattheysent['case_id'], true, $whattheysent['mdl_checkbox_example'], $whattheysent['mdl_checkbox_new'], $whattheysent['mdl_checkbox_ready']);

        if($status['success'] == true){
            return response()->json([
                'success' => true,
                'whattheysent' => $whattheysent,
                'caseId' => $whattheysent['case_id'],
            ]);
        }else{
            return response()->json([
                'success' => false,
                'whattheysent' => $whattheysent,
                'caseId' => $whattheysent['case_id'],
                'errors' => [$status['errors']]
            ]);
        }
    }

    /**
     * Forcefully Re Manual Download the Job using caseID
     *
     * @param $caseId
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postModifyForcefullyReManualdownloadforJob($caseId = null, Request $request)
    {
        try {
            $caseId = Crypt::decryptString($caseId);
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]); /** aborting */
        }
        $whattheysent = $request->all();
        /**dd($whattheysent);*/

        Loggy::write('manual_download_redownload', 'job:manualdl_redownload mdl_checkbox_example' . $caseId . ' -> ' . $whattheysent["mdl_checkbox_example"]);
        Loggy::write('manual_download_redownload', 'job:manualdl_redownload mdl_checkbox_new' . $caseId . ' -> ' . $whattheysent["mdl_checkbox_new"]);
        Loggy::write('manual_download_redownload', 'job:manualdl_redownload mdl_checkbox_ready' . $caseId . ' -> ' . $whattheysent["mdl_checkbox_ready"]);

        Loggy::write('manual_download_redownload', 'job:manualdl_redownload array_of_job_zips_details' . $caseId . ' -> ' . json_encode($whattheysent["array_of_job_zips_details"]));
        

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

        if (Auth::check()) {
            // The user is logged in...
            Loggy::write('manual_download_redownload', Auth::user()->username .' authenticated to use the redownload command');
            $authenticated_user = Auth::user()->username;
            $last_updated_by = $this->appuserRepo->getAuthUserDetails()->user_id;
        }else{
            Loggy::write('manual_download_redownload', 'User From Console triggered the manual redownload command for caseID' . $caseId);
            $authenticated_user = 'User From Console';
            $last_updated_by = "";
        }

        /**dd($last_updated_by);*/
        try {
            DB::beginTransaction();

            $manualdownload = $this->taskManualDownload->where('case_id', $caseId)->first();
            if(!empty($manualdownload)){
                $manualdownload->state = 'new';
                $manualdownload->archived_case = '1';
                if($last_updated_by !== ""){
                    $manualdownload->last_updated_by = $last_updated_by;
                }
                $manualdownload->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                $manualdownload->save();
            }

            $this->taskManualUpload->where('case_id', $caseId)->delete();
            /** will this trigger just the download of the specific chosen folder */
            /** we need to make a decision whether to keep the other folders... when a re-download is triggered.. */
            /** the user is in control. */
            /** but we don't want to make the tool download */
            /** if it is downloaded already.. and they choose to re-download only the ready .. they should keep the new and example if its there .. */
            /** that means that the tool needs to remember what was downloaded before */
            /** if there is a row in the taskmanualdownloadfiles table then we know.. as long as the status is unchanged.. */
            /** but if they decide to delete from the shared folder .. the tool needs to check if the folder has some stuff there. */

            if($whattheysent["mdl_checkbox_example"] == 'true'){
                $examplezip = $this->taskManualDownloadFile->where('case_id', $caseId)->where('type', 'example')->first();
                if(!empty($examplezip)){
                    $examplezip->state = 'new';
                    $examplezip->url = $whattheysent["array_of_job_zips_details"]["example"]["uri"];
                    $examplezip->size = $whattheysent["array_of_job_zips_details"]["example"]["size"];
                    $examplezip->last_modified = $whattheysent["array_of_job_zips_details"]["example"]["lastModified"];
                    $examplezip->file_count = 0;
                    $examplezip->unzip = 0;
                    $examplezip->unzip_tries = 0;
                    $examplezip->unzip_checks = 0;
                    $examplezip->unzip_checks_tries = 0;
                    $examplezip->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                    $examplezip->save();

                    /**ADD IT STRAIGHT TO THE DOWNLOAD QUEUE */
                    \App\Jobs\ManualDL_download::dispatch($examplezip->case_id, $examplezip->type);
                }else{
                    /** need to create the row for this job and type if it does not exist already */
                    $dataZip = [
                        'case_id' => $caseId,
                        'live' => $whattheysent["array_of_job_zips_details"]["example"]["live"],
                        'state' => 'new',
                        'time' => time(),
                        'url' => $whattheysent["array_of_job_zips_details"]["example"]["uri"],
                        'local' => $whattheysent["array_of_job_zips_details"]["example"]["local"],
                        'size' => $whattheysent["array_of_job_zips_details"]["example"]["size"],
                        'last_modified' => $whattheysent["array_of_job_zips_details"]["example"]["lastModified"],
                        'type' => 'example',
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                    ];
                    $this->taskManualDownloadFile->insert($dataZip);
                    \App\Jobs\ManualDL_download::dispatch($caseId, 'example');
                }
            }else{
                $examplezip = [];
            }

            if($whattheysent["mdl_checkbox_new"] == 'true'){
                $newzip = $this->taskManualDownloadFile->where('case_id', $caseId)->where('type', 'new')->first();
                if(!empty($newzip)){
                    $newzip->state = 'new';
                    $newzip->url = $whattheysent["array_of_job_zips_details"]["new"]["uri"];
                    $newzip->size = $whattheysent["array_of_job_zips_details"]["new"]["size"];
                    $newzip->last_modified = $whattheysent["array_of_job_zips_details"]["new"]["lastModified"];
                    $newzip->file_count = 0;
                    $newzip->unzip = 0;
                    $newzip->unzip_tries = 0;
                    $newzip->unzip_checks = 0;
                    $newzip->unzip_checks_tries = 0;
                    $newzip->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                    $newzip->save();

                    /**ADD IT STRAIGHT TO THE DOWNLOAD QUEUE */
                    \App\Jobs\ManualDL_download::dispatch($newzip->case_id, $newzip->type);
                }else{
                    /** need to create the row for this job and type if it does not exist already */
                    $dataZip = [
                        'case_id' => $caseId,
                        'live' => $whattheysent["array_of_job_zips_details"]["new"]["live"],
                        'state' => 'new',
                        'time' => time(),
                        'url' => $whattheysent["array_of_job_zips_details"]["new"]["uri"],
                        'local' => $whattheysent["array_of_job_zips_details"]["new"]["local"],
                        'size' => $whattheysent["array_of_job_zips_details"]["new"]["size"],
                        'last_modified' => $whattheysent["array_of_job_zips_details"]["new"]["lastModified"],
                        'type' => 'new',
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                    ];
                    $this->taskManualDownloadFile->insert($dataZip);                    
                    \App\Jobs\ManualDL_download::dispatch($caseId, 'new');
                }
            }else{
                $newzip = [];
            }

            if($whattheysent["mdl_checkbox_ready"] == 'true'){
                $readyzip = $this->taskManualDownloadFile->where('case_id', $caseId)->where('type', 'ready')->first();
                if(!empty($readyzip)){
                    $readyzip->state = 'new';
                    $readyzip->url = $whattheysent["array_of_job_zips_details"]["ready"]["uri"];
                    $readyzip->size = $whattheysent["array_of_job_zips_details"]["ready"]["size"];
                    $readyzip->last_modified = $whattheysent["array_of_job_zips_details"]["ready"]["lastModified"];
                    $readyzip->file_count = 0;
                    $readyzip->unzip = 0;
                    $readyzip->unzip_tries = 0;
                    $readyzip->unzip_checks = 0;
                    $readyzip->unzip_checks_tries = 0;
                    $readyzip->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                    $readyzip->save();
                    
                    /**ADD IT STRAIGHT TO THE DOWNLOAD QUEUE */
                    \App\Jobs\ManualDL_download::dispatch($readyzip->case_id, $readyzip->type);
                }else{
                    /** need to create the row for this job and type if it does not exist already */
                    $dataZip = [
                        'case_id' => $caseId,
                        'live' => $whattheysent["array_of_job_zips_details"]["ready"]["live"],
                        'state' => 'new',
                        'time' => time(),
                        'url' => $whattheysent["array_of_job_zips_details"]["ready"]["uri"],
                        'local' => $whattheysent["array_of_job_zips_details"]["ready"]["local"],
                        'size' => $whattheysent["array_of_job_zips_details"]["ready"]["size"],
                        'last_modified' => $whattheysent["array_of_job_zips_details"]["ready"]["lastModified"],
                        'type' => 'ready',
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                    ];
                    $this->taskManualDownloadFile->insert($dataZip); 
                    \App\Jobs\ManualDL_download::dispatch($caseId, 'ready');
                }
            }else{
                $readyzip = [];
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Debugbar::addException($e);
            return response()->json([
                'success' => false,
                'error_from_catch' => 'error_from_catch',
                'errors' => $e,
                'caseId' => $caseId
            ]);
        }

        /** for some reason these are proper but they don't actually perform the commands.. NOW it is working.. was just a folder permission issue.. needed to set to www-data group and user www-data.. so it could actually delete */

        /** if write to db success then make sure previous attempts are cleared away especially the jobfolder...on the NAS */
        /**data_sdb_temp/job delete zips for previous attempt */
        $examplezipdir  = storage_path()."/app".config('s3br24.manual_download_temp_folder') . 'job';
        if(!empty($examplezip)){
            /** remove the file at the end */
            $path = $examplezipdir.'/'.$examplezip->local;
            if(File::exists($path)){
                try {
                    File::delete($path);
                    Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $path .' exists');
                    exec('rm '.$path);
                } catch (FileNotFoundException $e) {
                    dd($e);
                }
            }
        }

        /**data_sdb_temp/job delete zips for previous attempt */
        $newzipdir  = storage_path()."/app".config('s3br24.manual_download_temp_folder') . 'job';
        if(!empty($newzip)){
            /** remove the file at the end */
            $path = $newzipdir.'/'.$newzip->local;
            if(File::exists($path)){
                try {
                    File::delete($path);
                    Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $path .' exists');
                    exec('rm '.$path);
                } catch (FileNotFoundException $e) {
                    dd($e);
                }
            }
        }

        /**data_sdb_temp/job delete zips for previous attempt */
        $readyzipdir  = storage_path()."/app".config('s3br24.manual_download_temp_folder') . 'job';
        if(!empty($readyzip)){
            /** remove the file at the end */
            $path = $readyzipdir.'/'.$readyzip->local;
            if(File::exists($path)){
                try {
                    File::delete($path);
                    Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $path .' exists');
                    exec('rm '.$path);
                } catch (FileNotFoundException $e) {
                    dd($e);
                }
            }
        }


        /** need to remove any traces of the previous attempt */
        /** but they want to only re download the ones they choose.. so we need to only remove that */
        $jobFolder = storage_path()."/app".config('s3br24.manual_job_folder').$caseId.'/';
        
        if($whattheysent["mdl_checkbox_example"] == 'true'){
            if(File::exists($jobFolder.'examples/')) {
                Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $jobFolder.'examples/' .' exists -> ');
                $fs = new Filesystem();
                $fs->cleanDirectory($jobFolder.'examples/');
                $files1example = $fs->files($jobFolder.'examples/');
                $fs->delete($files1example);
                exec('rm -rf '.$jobFolder.'examples/');
            }
        }
        if($whattheysent["mdl_checkbox_new"] == 'true'){
            if(File::exists($jobFolder.'new/')) {
                Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $jobFolder.'new/' .' exists -> ');
                $fs = new Filesystem();
                $fs->cleanDirectory($jobFolder.'new/');
                $files1new = $fs->files($jobFolder.'new/');
                $fs->delete($files1new);
                exec('rm -rf '.$jobFolder.'new/');
            }            
        }
        if($whattheysent["mdl_checkbox_ready"] == 'true'){
            if(File::exists($jobFolder.'ready/')) {
                Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $jobFolder.'ready/' .' exists -> ');
                $fs = new Filesystem();
                $fs->cleanDirectory($jobFolder.'ready/');
                $files1ready = $fs->files($jobFolder.'ready/');
                $fs->delete($files1ready);
                exec('rm -rf '.$jobFolder.'ready/');
            }            
        }


        /** no matter which part is decided to download do we have to make sure to clear only unzipfolder and logs for the selection made otherwise IT WILL AFFECT the function when the scheduler takes control */
        $unzipFolder = storage_path()."/app".config('s3br24.manual_unzip_folder').$caseId.'/';
        if(File::exists($unzipFolder)) {
            Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $unzipFolder .' exists -> ');
            $fs = new Filesystem();
            $fs->cleanDirectory($unzipFolder);
            $files2 = $fs->files($unzipFolder);
            $fs->delete($files2);
            exec('rm -rf '.$unzipFolder);
        }




        /** IMPORTANT to also clear the logs. NB: logs are not contained in caseid separated folder!! the case id is part of the filename */
        $all_caseid_zips_local = DB::table('tasks_manual_downloads_files')->where("case_id", $caseId)->get()->groupBy('case_id')
        ->map(function ($ts) {
            return $ts->keyBy('type');
        })->toArray();
        /**dd($all_caseid_zips_local);*/


        $unzip_log = storage_path()."/logs".config('s3br24.manual_download_log') . 'unzip_log';
        if($whattheysent["mdl_checkbox_example"] == 'true'){
            /** remove the file at the end */
            $unziplog_example = $caseId.'_example.log';
            $path = $unzip_log.'/'.$unziplog_example;
            if(File::exists($path)){
                try {
                    File::delete($path);
                    Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $path .' exists');
                    exec('rm '.$path);
                } catch (FileNotFoundException $e) {
                    dd($e);
                }
            }

            $type = 'example';
            if(isset($all_caseid_zips_local[$caseId][$type])){
                $progress_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress/". explode(".", $all_caseid_zips_local[$caseId][$type]->local)[0].'_progressLog.log';
                if (File::exists($progress_log)) {
                    try {
                        File::delete($progress_log);
                        Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $progress_log .' exists');
                        exec('rm '.$progress_log);
                    } catch (FileNotFoundException $e) {
                        dd($e);
                    }
                }
            }

            /** unziptest log */
            $unziptest_progress_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress/". $caseId.'_example_unziptest_progress.log';
            if (File::exists($unziptest_progress_log)) {
                try {
                    File::delete($unziptest_progress_log);
                    Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $unziptest_progress_log .' exists');
                    exec('rm '.$unziptest_progress_log);
                } catch (FileNotFoundException $e) {
                    dd($e);
                }
            }

            /** unzip log */
            $unzip_progress_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress/". $caseId.'_example_unzip_progress.log';
            if (File::exists($unzip_progress_log)) {
                try {
                    File::delete($unzip_progress_log);
                    Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $unzip_progress_log .' exists');
                    exec('rm '.$unzip_progress_log);
                } catch (FileNotFoundException $e) {
                    dd($e);
                }
            }
        }

        if($whattheysent["mdl_checkbox_new"] == 'true'){
            $unziplog_new = $caseId.'_new.log';
            /** remove the file at the end */
            $path = $unzip_log.'/'.$unziplog_new;
            if(File::exists($path)){
                try {
                    File::delete($path);
                    Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $path .' exists');
                    exec('rm '.$path);
                } catch (FileNotFoundException $e) {
                    dd($e);
                }
            }

            $type = 'new';
            if(isset($all_caseid_zips_local[$caseId][$type])){
                $progress_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress/". explode(".", $all_caseid_zips_local[$caseId][$type]->local)[0].'_progressLog.log';
                if (File::exists($progress_log)) {
                    try {
                        File::delete($progress_log);
                        Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $progress_log .' exists');
                        exec('rm '.$progress_log);
                    } catch (FileNotFoundException $e) {
                        dd($e);
                    }
                }
            }

            /** unziptest log */
            $unziptest_progress_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress/". $caseId.'_new_unziptest_progress.log';
            if (File::exists($unziptest_progress_log)) {
                try {
                    File::delete($unziptest_progress_log);
                    Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $unziptest_progress_log .' exists');
                    exec('rm '.$unziptest_progress_log);
                } catch (FileNotFoundException $e) {
                    dd($e);
                }
            }

            /** unzip log */
            $unzip_progress_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress/". $caseId.'_new_unzip_progress.log';
            if (File::exists($unzip_progress_log)) {
                try {
                    File::delete($unzip_progress_log);
                    Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $unzip_progress_log .' exists');
                    exec('rm '.$unzip_progress_log);
                } catch (FileNotFoundException $e) {
                    dd($e);
                }
            }
        }

        if($whattheysent["mdl_checkbox_ready"] == 'true'){
            $unziplog_ready = $caseId.'_ready.log';
            /** remove the file at the end */
            $path = $unzip_log.'/'.$unziplog_ready;
            if(File::exists($path)){
                try {
                    File::delete($path);
                    Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $path .' exists');
                    exec('rm '.$path);
                } catch (FileNotFoundException $e) {
                    dd($e);
                }
            }

            $type = 'ready';
            if(isset($all_caseid_zips_local[$caseId][$type])){
                $progress_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress/". explode(".", $all_caseid_zips_local[$caseId][$type]->local)[0].'_progressLog.log';
                if (File::exists($progress_log)) {
                    try {
                        File::delete($progress_log);
                        Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $progress_log .' exists');
                        exec('rm '.$progress_log);
                    } catch (FileNotFoundException $e) {
                        dd($e);
                    }
                }
            }

            /** unziptest log */
            $unziptest_progress_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress/". $caseId.'_ready_unziptest_progress.log';
            if (File::exists($unziptest_progress_log)) {
                try {
                    File::delete($unziptest_progress_log);
                    Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $unziptest_progress_log .' exists');
                    exec('rm '.$unziptest_progress_log);
                } catch (FileNotFoundException $e) {
                    dd($e);
                }
            }

            /** unzip log */
            $unzip_progress_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress/". $caseId.'_ready_unzip_progress.log';
            if (File::exists($unzip_progress_log)) {
                try {
                    File::delete($unzip_progress_log);
                    Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $unzip_progress_log .' exists');
                    exec('rm '.$unzip_progress_log);
                } catch (FileNotFoundException $e) {
                    dd($e);
                }
            }

        }

        /** no matter which type is redownloaded need to remove the $case_id_rsync_progressLog.log file */
        $rsync_progress_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress/". $caseId.'_rsync_progressLog.log';
        if (File::exists($rsync_progress_log)) {
            try {
                File::delete($rsync_progress_log);
                Loggy::write('manual_download_redownload', $authenticated_user .' -> ' . $rsync_progress_log .' exists');
                exec('rm '.$rsync_progress_log);
            } catch (FileNotFoundException $e) {
                dd($e);
            }
        }

        Loggy::write('manual_download_redownload', 'job:manualdl_redownload ' . $caseId . ' -> finished -> selected items of job will be redownloaded via scheduler shortly');

        $file = $this->taskManualDownloadView->where('case_id', $caseId)->first();

        return response()->json([
            'success' => true,
            'caseId' => $caseId,
            'state' => $file->state,
            'last_updated_by_name' => $file->last_updated_by_name,  
            'updated_at' => Carbon::now()->format($last_updated_format)
        ]);
    }

    /**
     * Get Current Details of Zips on Amazon using case id number
     *
     * @param $caseId
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function getZipDetailsOfCaseID(Request $request, $caseId = null)
    {
        $whattheysent = $request->all();

        if($caseId == null){
            /**dump($whattheysent);*/
            /**dump('$caseID was null so probably comming from the search keynumber input validation already made');*/
            /** would like to do the validation stuff here too before but must actually get the details to supply the form */
            $caseId = $whattheysent["case_id"];
            /** it probably also matters which parts they wanted.. we need to check that all info is given back */
            /** actually after the checks they still haven't decided which parts they want to dl.. */
            /** not yet at least */
        }else{
            try {
                $caseId = Crypt::decryptString($caseId);
            } catch (DecryptException $e) {
                Debugbar::addException($e);
                return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]); /** aborting */
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
        }

        $all_caseid_zips_local = DB::table('tasks_manual_downloads_files')->where("case_id", $caseId)->get()->groupBy('case_id')
        ->map(function ($ts) {
            return $ts->keyBy('type');
        })->toArray();
        /**dd($all_caseid_zips_local);*/
        
        /**this still takes quite a long time per caseID */
        /**before we can actually see the popup with choices the user can make */
        /** there must be a faster way */
        /** perhaps need to use the AWS SDK is that faster? or is it the same?? */
        /** we should check if they request a zip is infact different use the amazon file signature to check for that change */
        /** at what stage is that better? */
        /** you can get the size of the zips too and alert if the zip on amazon has a different signature */
        $s3 = Storage::disk('s3');
        /**$client = $s3->getDriver()->getAdapter()->getClient();*/
        $expiry = "+7 days";

        $client = new S3Client([
            'version' => 'latest',
            'profile' => 'default',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            // by not defining the access id and access key here the aws sdk will try to look for it as an environment variable 
            // 'credentials' => array(
            //     'key' => env('AWS_ACCESS_KEY_ID'),
            //     'secret'  => env('AWS_SECRET_ACCESS_KEY')
            // )
        ]);

        $s3Br24Config = config('s3br24');
        $bucket = config('filesystems.disks.s3.bucket');

        $zipFolder = $s3Br24Config['job_dir'].$caseId."/zip/";
        $zipFiles = $s3->files($zipFolder);

        /** need to update the url just in case there is a new zip that replaces the other file therefore update the amazon signature */
        $array_of_job_zips_details = [];
        $array_of_job_zips_details["example"]["uri"] = null;
        $array_of_job_zips_details["example"]["size"] = null;
        $array_of_job_zips_details["example"]["lastModified"] = null;
        $array_of_job_zips_details["new"]["uri"] = null;
        $array_of_job_zips_details["new"]["size"] = null;
        $array_of_job_zips_details["new"]["lastModified"] = null;
        $array_of_job_zips_details["ready"]["uri"] = null;
        $array_of_job_zips_details["ready"]["size"] = null;
        $array_of_job_zips_details["ready"]["lastModified"] = null;

        if (!empty($zipFiles)) {
            foreach ($zipFiles as $zip) {
                if (strpos($zip, "example.zip") !== false || strpos($zip, "new.zip") !== false || strpos($zip, "ready.zip") !== false) {
                    try {
                        $command = $client->getCommand('GetObject', [
                            'Bucket' => $bucket,
                            'Key' => $zip
                        ]);

                        $request = $client->createPresignedRequest($command, $expiry);
                        $uri = (string)$request->getUri();

                        if (strpos($zip, "example.zip") !== false) {
                            $array_of_job_zips_details["example"]["live"] = $zip;
                            $array_of_job_zips_details["example"]["local"] = basename($zip);
                            $array_of_job_zips_details["example"]["uri"] = $uri;
                            $array_of_job_zips_details["example"]["size"] = $s3->size($zip);
                            $array_of_job_zips_details["example"]["lastModified"] = $s3->lastModified($zip);
                        }

                        if (strpos($zip, "new.zip") !== false) {
                            $array_of_job_zips_details["new"]["live"] = $zip;
                            $array_of_job_zips_details["new"]["local"] = basename($zip);
                            $array_of_job_zips_details["new"]["uri"] = $uri;
                            $array_of_job_zips_details["new"]["size"] = $s3->size($zip);
                            $array_of_job_zips_details["new"]["lastModified"] = $s3->lastModified($zip);
                        }

                        if (strpos($zip, "ready.zip") !== false) {
                            $array_of_job_zips_details["ready"]["live"] = $zip;
                            $array_of_job_zips_details["ready"]["local"] = basename($zip);
                            $array_of_job_zips_details["ready"]["uri"] = $uri;
                            $array_of_job_zips_details["ready"]["size"] = $s3->size($zip);
                            $array_of_job_zips_details["ready"]["lastModified"] = $s3->lastModified($zip);
                        }

                    } catch (\Exception $ex) {
                        Loggy::write('manual_download_redownload', json_encode($ex->getMessage()));
                    }
                }
            }
        }else{
            return response()->json([
                'success' => false,
                'caseId' => $caseId,
                'errors' => ['There are no zips.']
            ]);
        }

        $count_of_information_gathered = 0;
        foreach($array_of_job_zips_details as $generic_key => $zip_info_gathered){
            if($zip_info_gathered["uri"] !== null && $zip_info_gathered["size"] !== null && $zip_info_gathered["lastModified"] !== null){
                $count_of_information_gathered++;
            }
        }

        if($count_of_information_gathered !== count($zipFiles)){
            return response()->json([
                'success' => false,
                'caseId' => $caseId,
                'errors' => ['Not all info about zips collected. Please Try again in a moment']
            ]);
        }else{
            return response()->json([
                'success' => true,
                'caseId' => $caseId,
                'array_of_job_zips_details' => $array_of_job_zips_details,
                'all_caseid_zips_local' => $all_caseid_zips_local
            ]);
        }
    }

    /**
     * Get Aria2c Download Progress Details of Zips happening using case id number
     *
     * @param $caseId
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function getAria2cDownloadProgressDetailsOfCaseID($caseId = null, $type = null, Request $request)
    {
        /**$whattheysent = $request->all();*/
        /**dd($whattheysent);*/

        /** what is the idea behind this now? */
        /** this happens immediately after selecting which zips */
        /** does that mean that it has started downloading yet? no */
        /** still need to wait for the scheduler to actuate */
        /** so we have to get a list using the status */

        try {
            $caseId = Crypt::decryptString($caseId);
            $type = Crypt::decryptString($type);
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]); /** aborting */
        }

        $all_caseid_zips_local = DB::table('tasks_manual_downloads_files')->where("case_id", $caseId)->where("type", $type)->get()->groupBy('case_id')
        ->map(function ($ts) {
            return $ts->keyBy('type');
        })->toArray();
        /**dd($all_caseid_zips_local);*/

        /** if the case is new => hasn't started downloading yet */
        /** if the case is downloading => has STARTED downloading maybe */
        /** if the case is downloaded => has FINISHED downloading maybe */

        if($all_caseid_zips_local[$caseId][$type]->state == 'moving_to_jobFolder'){
            /** for the rsync step from unzip directory to jobfolder it will be in another log file to grab details from */
            $log_name = $caseId.'_rsync_progressLog.log';
            /** the only way is the look for the log file */
            /** if the file doesn't exist the you need to use the state to determine whether it has already been downloaded? */
            $progress_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress/". $log_name;
            
            
            if (File::isFile($progress_log)) {
                $file_as_array = array_filter(preg_split('/\n|\r\n?/', file_get_contents($progress_log)));
            }else{
                $file_as_array = [];
            }

            $file_as_array = array_reverse($file_as_array);
            /**dd($file_as_array);*/

            $latest_progress_details_array = [];
            /** 30,290,409,773 100%   75.83MB/s    0:06:20 (xfr#197, to-chk=0/206) */

            $finished = null;
            foreach($file_as_array as $line_number_key => $line_number_detail){

                if (strpos($line_number_detail, '%') !== false) {
                    if (strpos($line_number_detail, '(') !== false && strpos($line_number_detail, ')') !== false){
                        /** remove everything between the brackets */
                        $line_number_detail = explode("(", $line_number_detail)[0];
                    }

                    /** if its finished then there is no need to continue we can do some clean up? */
                    if (strpos($line_number_detail, '100%') !== false){
                        /**$latest_progress_details_array = explode(" ", $line_number_detail);*/
                        $finished = true;
                        break;
                    }else{
                        /** it is the latest details filtering out the useless lines */
                        $latest_progress_details_array = array_values(array_filter(explode(" ", $line_number_detail)));
                        $finished = false;
                        break;
                    }
                }
            }
        }else if($all_caseid_zips_local[$caseId][$type]->state == 'downloaded'){

            $log_name = '';
            /** we have two more logs to check the progress with */
            /** unzip test log */
            /** unzip actually log */

            /**log for list files of zip*/
            $unzip_log = storage_path()."/logs".config('s3br24.manual_download_log') . 'unzip_log';
            exec("mkdir -p $unzip_log");

            $progress_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress";
            exec("mkdir -p $progress_log");

            /** dd(null); */
            if ($type == 'new') {
                $progress_log = $progress_log . '/' . $caseId . '_new_unziptest_progress.log';
                $log_name = $caseId . '_new_unziptest_progress.log';
            } else if ($type == 'example') {
                $progress_log = $progress_log . '/' . $caseId . '_example_unziptest_progress.log';
                $log_name = $caseId . '_example_unziptest_progress.log';
            } else if ($type == 'ready') {
                $progress_log = $progress_log . '/' . $caseId . '_ready_unziptest_progress.log';
                $log_name = $caseId . '_ready_unziptest_progress.log';
            }else{
                dump();
            }

            /**dump($progress_log);*/


            if (File::isFile($progress_log)) {
                $file_as_array = array_filter(preg_split('/\n|\r\n?/', file_get_contents($progress_log)));
            }else{
                $file_as_array = [];
            }

            $file_as_array = array_reverse($file_as_array);
            /**dump($file_as_array);*/

            $latest_progress_details_array = [];
            /** 32.0 00:00:00 [0.00 /s] [===========================================>  ] 96% */
            /** file number currently unzipping, time elapsed, speed of unzip, progress bar, percentage*/

            /** you can use the other db value to do a how many out of total unzipped $all_caseid_zips_local[$caseId][$type]->file_count */
            $finished = null;
            $exploded_array = [];
            foreach($file_as_array as $line_number_key => $line_number_detail){
                if (strpos($line_number_detail, '%') !== false) {
                    
                    /** 40 characters long */
                    $progress_bar = explode("[", $line_number_detail);
                    foreach($progress_bar as $generic_key => $string_detail){
                        if (strpos($string_detail, '=') !== false){
                            /**dump($string_detail);*/
                            $inner_explode = explode("]", $string_detail)[0];
                            $progress_bar = "[".$inner_explode."]";
                            break;
                        }
                    }
                    /**dump($progress_bar);*/

                    $exploded_array = array_values(array_filter(explode(" ", str_replace("[ ", "[", str_replace(" /s", "/s", $line_number_detail)))));
                    /**dump($exploded_array);*/
                    /** if its finished then there is no need to continue we can do some clean up? */
                    $exploded_array[3] = $progress_bar;
                    $exploded_array[4] = $exploded_array[array_key_last($exploded_array)];
                    $exploded_array[5] = $all_caseid_zips_local[$caseId][$type]->file_count;
                    
                    /**dump($exploded_array);*/

                    if((int)str_replace("%", "", $exploded_array[4]) > 100){
                        $exploded_array[4] = '100%';
                    }

                    if ((float)$exploded_array[0] == (float)$exploded_array[5]){
                        $finished = true;
                        break;
                    }else{
                        $finished = false;
                        break;
                    }
                }
            }
            foreach($exploded_array as $generic_key => $string_detail){
                if($string_detail == ']'){
                    unset($exploded_array[$generic_key]);
                }
            }

            $latest_progress_details_array = array_values($exploded_array);
        }else if($all_caseid_zips_local[$caseId][$type]->state == 'unzipping'){
            
            $log_name = '';

            $progress_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress";

            /** dd(null); */
            if ($type == 'new') {
                $progress_log = $progress_log . '/' . $caseId . '_new_unzip_progress.log';
                $log_name = $caseId . '_new_unzip_progress.log';
            } else if ($type == 'example') {
                $progress_log = $progress_log . '/' . $caseId . '_example_unzip_progress.log';
                $log_name = $caseId . '_example_unzip_progress.log';
            } else if ($type == 'ready') {
                $progress_log = $progress_log . '/' . $caseId . '_ready_unzip_progress.log';
                $log_name = $caseId . '_ready_unzip_progress.log';
            }else{
                dump();
            }

            /**dump($progress_log);*/


            if (File::isFile($progress_log)) {
                $file_as_array = array_filter(preg_split('/\n|\r\n?/', file_get_contents($progress_log)));
            }else{
                $file_as_array = [];
            }

            $file_as_array = array_reverse($file_as_array);
            /**dd($file_as_array);*/

            $latest_progress_details_array = [];
            /** 30,290,409,773 100%   75.83MB/s    0:06:20 (xfr#197, to-chk=0/206) */

            $finished = null;
            $exploded_array = [];
            foreach($file_as_array as $line_number_key => $line_number_detail){
                if (strpos($line_number_detail, '%') !== false) {

                    /** 40 characters long */
                    $progress_bar = explode("[", $line_number_detail);
                    foreach($progress_bar as $generic_key => $string_detail){
                        if (strpos($string_detail, '=') !== false){
                            /**dump($string_detail);*/
                            $inner_explode = explode("]", $string_detail)[0];
                            $progress_bar = "[".$inner_explode."]";
                            break;
                        }
                    }

                    $exploded_array = array_values(array_filter(explode(" ", str_replace("[ ", "[", str_replace(" /s", "/s", $line_number_detail)))));
                    /** if its finished then there is no need to continue we can do some clean up? */
                    $exploded_array[3] = $progress_bar;
                    $exploded_array[4] = $exploded_array[array_key_last($exploded_array)];
                    $exploded_array[5] = $all_caseid_zips_local[$caseId][$type]->file_count;
                    /**dump($exploded_array);*/

                    if ((float)$exploded_array[0] == (float)$exploded_array[5]){
                        $finished = true;
                        break;
                    }else{
                        $finished = false;
                        break;
                    }
                }
            }

            foreach($exploded_array as $generic_key => $string_detail){
                if($string_detail == ']'){
                    unset($exploded_array[$generic_key]);
                }
            }

            $latest_progress_details_array = array_values($exploded_array);
        }else{
            $log_name = explode(".", $all_caseid_zips_local[$caseId][$type]->local)[0].'_progressLog.log';
            /** the only way is the look for the log file */
            $progress_log = storage_path()."/logs".config('s3br24.manual_download_log')."progress/". $log_name;

            /** you need to know at which stage the manual upload is at.. because this function is the aria2c step */
            /** have we decided that it will be the same log file for both stages? */
            /** check if the file exists */
            /** if it does not.. then you exit out and let the client know. */
            /** if it does exists get it into an array */
            
            if (File::isFile($progress_log)) {
                $file_as_array = array_filter(preg_split('/\n|\r\n?/', file_get_contents($progress_log)));
            }else{
                $file_as_array = [];
            }

            $file_as_array = array_reverse($file_as_array);
            /**dd($file_as_array);*/

            $latest_progress_details_array = [];
            /** string will look similar to this [3.8GiB/4.4GiB(85%) CN:8 DL:5.6MiB ETA:1m52s] */
            /** or this (OK):download completed.*/


            $finished = null;
            foreach($file_as_array as $line_number_key => $line_number_detail){

                /** if its finished then there is no need to continue we can do some clean up? */
                if (strpos($line_number_detail, '(OK):download completed.') !== false){
                    //$latest_progress_details_array = $line_number_detail;
                    $finished = true;
                    break;
                }

                if (strpos($line_number_detail, '%') !== false && strpos($line_number_detail, 'CN:') !== false && strpos($line_number_detail, 'DL:') !== false && strpos($line_number_detail, 'ETA:') !== false) {
                    /** it is the latest details filtering out the useless lines */
                    $line_number_detail = str_replace("[", "", str_replace("]", "", $line_number_detail));
                    $latest_progress_details_array = explode(" ", $line_number_detail);
                    $finished = false;
                    break;
                }
            }
        }

        /** we can even have this one retrigger the manual download if the unzip state == 3 (failed) */
        if($all_caseid_zips_local[$caseId][$type]->unzip == 3){
            $file = $this->taskManualDownloadFile->where("case_id", $caseId)->where("type", $type)->first();
            $file->state = 'new';
            $file->unzip = 0;
            $file->unzip_tries = 0;
            $file->unzip_checks = 0;
            $file->unzip_checks_tries = 0;
            $file->file_count = (int) 0;
            $file->save();
            
            // $file->state = 'downloaded';
            // $file->unzip = 1; /**unzip error*/
            // $file->unzip_tries = 0;
            // $file->unzip_checks = 1;
            // $file->unzip_checks_tries = 0;
            // $file->file_count = (int) 0;
            // $file->save();  

            /** and since the queue is handling it all we need to send it to the queue too*/
            \App\Jobs\ManualDL_download::dispatch($caseId, $type)->delay(now()->addSeconds(DB::table('queue_delay_seconds_manualdl')->first()->queue_delay_seconds));
        }

        /** should be the total size of all zip types being attempted to download */
        $all_caseid_zips_local_sizes = DB::table('tasks_manual_downloads_files')->where("case_id", $caseId)
        //->where("type", $type)
        ->whereNotIn("state", ["notified"])
        ->get()->groupBy('case_id')
        ->map(function ($ts) {
            return $ts->keyBy('id');
        })->toArray();
        /**dd($all_caseid_zips_local_sizes);*/

        if(isset($all_caseid_zips_local_sizes[$caseId])){
            $zip_sizes = array_column($all_caseid_zips_local_sizes[$caseId], 'size');
        }else{
            $zip_sizes = [];
        }
        /**dd($zip_sizes);*/

        return response()->json([
            'success' => true,
            'caseId' => $caseId,
            'type' => $type,
            'state' => $all_caseid_zips_local[$caseId][$type]->state,
            'progress_log' =>  $log_name,
            'check_progress_log_file_exists' => File::isFile($progress_log),
            'latest_progress_details_array' => $latest_progress_details_array,
            'finished' => $finished,
            'file_count' => $all_caseid_zips_local[$caseId][$type]->file_count,
            //'zip_size' => $all_caseid_zips_local[$caseId][$type]->size,
            'zip_size' => array_sum($zip_sizes),
            //'zip_sizes' => $zip_sizes
        ]);
    }

}
