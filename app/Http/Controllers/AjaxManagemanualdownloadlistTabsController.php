<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

use App\Repositories\AppUserRepository;
use App\Repositories\GroupRepository;
use App\Repositories\TeamRepository;

use Carbon\Carbon;
use App\Helpers;
use Session;
use Validator;
use DateTime;
use Cache;

/**
 * Class AjaxManagemanualdownloadlistTabsController
 *
 * @author sigmoswitch
 * @package App\Http\Controller
 */
class AjaxManagemanualdownloadlistTabsController extends Controller
{
    /**
     * @var AppUserRepository
     */
    protected $appuserRepo;

    /**
     * @var GroupRepository
     */
    // protected $groupRepo;

    /**
     * @var TeamRepository
     */
    // protected $teamRepo;
   
    /**
     * create a new controller instance.
     *
     * @param AppUserRepository $appuserRepo
     * @param GroupRepository $grouprepo
     * @param TeamRepository $teamrepo
     */
    public function __construct(
        AppUserRepository $appuserRepo
        // TeamRepository $teamRepo,
        // GroupRepository $groupRepo
    ) {
        $this->appuserRepo = $appuserRepo;
        // $this->groupRepo = $groupRepo;
        // $this->teamRepo = $teamRepo;
    }

    /**
     * Send manualdownloadlistInfo Tab HTML via Ajax
     *
     * @author sigmoswitch
     * @return \Illuminate\View\View
     */
    public function ajaxmanualdownloadlistInfo_HTML()
    {
        $route = url()->current();
        $auth_user_permissions = $this->appuserRepo->getAuthUserDetails()->permissions;
        $auth_user_permissions = explode(';', $auth_user_permissions);

        $this->data = compact('route', 'auth_user_permissions');
        $this->js = compact([]);

        return $this->render('manage_manualdownloadlist._manualdownloadlist');
    }

    /**
     * Get manualdownloadlistInfo Table via Ajax
     *
     * @author sigmoswitch
     * @return \Illuminate\View\View
     */
    public function getmanualdownloadlistInfo_TABLE(Request $request)
    {
        /** depending on where the multi select has decided to display those that are disabled or those that are enabled or both */
        $locale = app()->getLocale();

        $whattheysent = $request->all();

        /** get the birthday dates from the request */
        /** then convert them back to the date format on the db for searching make sure they are strings */
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

        //if (isset($whattheysent['report_date_value'])){
        //    $report_date_reformat = Carbon::createFromFormat($format, $whattheysent['report_date_value']);
        //    $whattheysent['report_date_value'] = Carbon::parse($report_date_reformat)->format('Y-m-d');
        //    $whattheysent['min_date'] = Carbon::parse($report_date_reformat)->firstOfMonth()->format('Y-m-d');
        //    $whattheysent['max_date'] = Carbon::parse($report_date_reformat)->lastOfMonth()->format('Y-m-d');
        //}else{
        //    /** set some defaults */
        //    /* Perhaps we can take from the cache system in this case */
        //    $periodKey = sprintf(config('base.cache.key.period'), auth()->id());
        //    if (!Cache::has($periodKey)) {
        //        Cache::put($periodKey, date('Y-m-01'), config('base.cache.time.short'));
        //    }
        //    /**dd(Cache::get($periodKey));*/
        //    $whattheysent['min_date'] = Carbon::parse(Cache::get($periodKey))->firstOfMonth()->format('Y-m-d');
        //    $whattheysent['max_date'] = Carbon::parse(Cache::get($periodKey))->lastOfMonth()->format('Y-m-d');
        //}
        
        $whattheysent['min_date'] = Carbon::now()->subMonths(2)->firstOfMonth()->format('Y-m-d');
        $whattheysent['max_date'] = Carbon::now()->addYears(1)->format('Y-m-d');

        /** to be able to search any job instructions for keyword without date restriction */
        $global_filter = strtolower(remove_vietnamese_accents($request->global_search_value));
        if(substr($global_filter, 0, 1) === '!' || substr($global_filter, 0, 1) === '#'){
            $whattheysent['min_date'] = null;
            $whattheysent['max_date'] = null;
        }

        $request->replace($whattheysent);

        /**dd($request->all());*/

        /** to get the file count from each of the case_id types example or new into this array */
        $downloadlist_list_file_count_array = DB::table('tasks_manual_downloads_files')->get()->keyBy('id')->toArray();


        $downloadlist_list_view = DB::table('v_manual_download_files')
        // ->when($request->min_birthday, function($query) use ($request){
        //  return $query->whereBetween('birthday', [$request->min_birthday, $request->max_birthday]);
        // })
        // ->where(function($query) use ($today){
        //  $query->whereRaw("DATE(calendar_date) = DATE('$today')")->whereIn('user_status', [1]);
        // })
        // ->when($request->byStatus_value, function($query) use ($request){
        //  return $query->whereIn('user_status', $request->byStatus_value);
        // })
        ->when($request->byJobStatus_value, function($query) use ($request){
            /**dd($request->byJobStatus_value);*/
            $strtolower = strtolower($request->byJobStatus_value);
            //dd($strtolower);
            if (strpos($request->byJobStatus_value, '|') !== false) {
                return $query->whereRaw("`status_of_case` REGEXP '^{$strtolower}$'");
            }else{
                return $query->whereRaw("`status_of_case` REGEXP '{$strtolower}'");
            }
        })
        // ->when($request->min_date, function($query) use ($request){
        //     return $query->whereRaw("DATE(expected_delivery_date_coalesce) >= DATE('$request->min_date') AND DATE(expected_delivery_date_coalesce) <= DATE('$request->max_date')");
        //     //return $query->whereRaw("DATE(expected_delivery_date_coalesce) >= DATE('$request->min_date')");
        // })
        // ->when($request->byEnabledDisabled_value, function($query) use ($request){
        //  return $query->whereIn('enabled_disabled', $request->byEnabledDisabled_value);
        // })
        // ->when($request->byCompanyPosition_value, function($query) use ($request){
        //  /** problem when its is an array because str_pos requires a string */
        //  if (strpos($request->byCompanyPosition_value, 'Production Manager') !== false){
        //      return $query->whereRaw("`position` REGEXP '^{$request->byCompanyPosition_value}$'");
        //  }           
        //  if (strpos($request->byCompanyPosition_value, 'Accountant') !== false){
        //      return $query->whereRaw("`position` REGEXP '^{$request->byCompanyPosition_value}$'");
        //  }
        //  if (strpos($request->byCompanyPosition_value, '|') !== false) {
        //      return $query->whereRaw("`position` REGEXP '^{$request->byCompanyPosition_value}$'");
        //  }else{
        //      return $query->whereRaw("`position` REGEXP '{$request->byCompanyPosition_value}'");
        //  }
        // })
        // ->when($request->byCompanyDepartment_value, function($query) use ($request){
        //  /**dd($request->byCompanyDepartment_value);*/
        //  if (strpos($request->byCompanyDepartment_value, 'IT') !== false){
        //      return $query->whereRaw("`team` REGEXP '^{$request->byCompanyDepartment_value}$'");
        //  }
        //  if (strpos($request->byCompanyDepartment_value, '|') !== false) {
        //      return $query->whereRaw("`team` REGEXP '^{$request->byCompanyDepartment_value}$'");
        //  }else{
        //      return $query->whereRaw("`team` REGEXP '{$request->byCompanyDepartment_value}'");
        //  }
        // })
        /**->when($request->byEditorLevel_value, function($query) use ($request){return $query->whereIn('editor_level', $request->byEditorLevel_value);})*/
        ->when($request->byAssignee_value, function($query) use ($request){
            /**dd($request->byAssignee_value);*/
            $strtolower = strtolower($request->byAssignee_value);
            //dd($strtolower);
            if (strpos($request->byAssignee_value, '|') !== false) {
                return $query->whereRaw("`assignees` REGEXP '^{$strtolower}$'");
            }else{
                return $query->whereRaw("`assignees` REGEXP '{$strtolower}'");
            }
        })
        ->when($request->global_search_value, function($query) use ($request){
            //$global_filter = strtolower(remove_vietnamese_accents($request->global_search_value));
            $global_filter = strtolower($request->global_search_value);
            if (is_numeric($global_filter)) {
                return $query->whereRaw("`case_id` LIKE '%{$global_filter}%'")->orWhereRaw("`xml_jobid_title` LIKE '%{$global_filter}%'");
            }else if(substr($global_filter, 0, 1) === '#'){
                $multi_word_hashtag_search = explode(' ', $global_filter);
                $search_string_post_format = str_replace('#', '', $global_filter);
                return $query->whereRaw("`custom_hashtag` LIKE '%{$search_string_post_format}%'");
            }else if(substr($global_filter, 0, 1) === '@'){
                $multi_word_hashtag_search = explode(' ', $global_filter);
                $search_string_post_format = str_replace('@', '', $global_filter);
                return $query->whereRaw("`assignees` LIKE '%{$search_string_post_format}%'");
            }else if(substr($global_filter, 0, 1) === '!'){
                $multi_word_jobinforproduction_search = array_values(array_filter(explode('!', $global_filter)));
                /**dd($multi_word_jobinforproduction_search);*/
                foreach ($multi_word_jobinforproduction_search as $key => $value) {
                    $search_string_post_format = trim(str_replace('!', '', $value));
                    /**dd($search_string_post_format);*/
                    $query->whereRaw("`xml_jobinfoproduction` LIKE '%{$search_string_post_format}%'");
                }
                return $query;
            }else{
                return $query->whereRaw("`xml_title_contents` LIKE '%{$global_filter}%'");
            }
        })
        ->when($request->start, function($query) use ($request){
            return $query->offset($request->start);
        })
        ->when($request->length, function($query) use ($request){
            return $query->limit($request->length);
        })
        // ->orderByRaw("
        //     CASE status_of_case
        //         WHEN 'in progress' THEN 1
        //         WHEN 'downloaded' THEN 2
        //         WHEN 'ready' THEN 3
        //         WHEN 'check' THEN 4
        //         WHEN 'feedback' THEN 5
        //         WHEN 'pause' THEN 6
        //         WHEN 'notified/uploading to s3' THEN 7
        //         WHEN 'uploaded to s3' THEN 8
        //         WHEN 'new' THEN 9
        //         WHEN 'downloading' THEN 10
        //         WHEN 'retry_zip' THEN 11
        //         WHEN 'zipped' THEN 12
        //     END
        //     ")
        //->orderBy('expected_delivery_date_coalesce', 'DESC')
        ->whereIn('archived_case', ['1'])
        ->get()->keyBy('case_id')->toArray();

        /**dd($downloadlist_list_view);*/



        /** want to see less old ones */
        $downloadlist_list_view_total = DB::table('v_manual_download_files')
        // ->when($request->min_birthday, function($query) use ($request){
        //  return $query->whereBetween('birthday', [$request->min_birthday, $request->max_birthday]);
        // })
        // ->where(function($query) use ($today){
        //  $query->whereRaw("DATE(calendar_date) = DATE('$today')")->whereIn('user_status', [1]);
        // })
        // ->when($request->byStatus_value, function($query) use ($request){
        //  return $query->whereIn('user_status', $request->byStatus_value);
        // })
        ->when($request->byJobStatus_value, function($query) use ($request){
            /**dd($request->byJobStatus_value);*/
            $strtolower = strtolower($request->byJobStatus_value);
            //dd($strtolower);
            if (strpos($request->byJobStatus_value, '|') !== false) {
                return $query->whereRaw("`status_of_case` REGEXP '^{$strtolower}$'");
            }else{
                return $query->whereRaw("`status_of_case` REGEXP '{$strtolower}'");
            }
        })
        // ->when($request->min_date, function($query) use ($request){
        //     return $query->whereRaw("DATE(expected_delivery_date_coalesce) >= DATE('$request->min_date') AND DATE(expected_delivery_date_coalesce) <= DATE('$request->max_date')");
        //     //return $query->whereRaw("DATE(expected_delivery_date_coalesce) >= DATE('$request->min_date')");
        // })
        // ->when($request->byEnabledDisabled_value, function($query) use ($request){
        //  return $query->whereIn('enabled_disabled', $request->byEnabledDisabled_value);
        // })
        // ->when($request->byCompanyPosition_value, function($query) use ($request){
        //  /** problem when its is an array because str_pos requires a string */
        //  if (strpos($request->byCompanyPosition_value, 'Production Manager') !== false){
        //      return $query->whereRaw("`position` REGEXP '^{$request->byCompanyPosition_value}$'");
        //  }           
        //  if (strpos($request->byCompanyPosition_value, 'Accountant') !== false){
        //      return $query->whereRaw("`position` REGEXP '^{$request->byCompanyPosition_value}$'");
        //  }
        //  if (strpos($request->byCompanyPosition_value, '|') !== false) {
        //      return $query->whereRaw("`position` REGEXP '^{$request->byCompanyPosition_value}$'");
        //  }else{
        //      return $query->whereRaw("`position` REGEXP '{$request->byCompanyPosition_value}'");
        //  }
        // })
        // ->when($request->byCompanyDepartment_value, function($query) use ($request){
        //  /**dd($request->byCompanyDepartment_value);*/
        //  if (strpos($request->byCompanyDepartment_value, 'IT') !== false){
        //      return $query->whereRaw("`team` REGEXP '^{$request->byCompanyDepartment_value}$'");
        //  }
        //  if (strpos($request->byCompanyDepartment_value, '|') !== false) {
        //      return $query->whereRaw("`team` REGEXP '^{$request->byCompanyDepartment_value}$'");
        //  }else{
        //      return $query->whereRaw("`team` REGEXP '{$request->byCompanyDepartment_value}'");
        //  }
        // })
        /**->when($request->byEditorLevel_value, function($query) use ($request){return $query->whereIn('editor_level', $request->byEditorLevel_value);})*/
        ->when($request->byAssignee_value, function($query) use ($request){
            /**dd($request->byAssignee_value);*/
            $strtolower = strtolower($request->byAssignee_value);
            //dd($strtolower);
            if (strpos($request->byAssignee_value, '|') !== false) {
                return $query->whereRaw("`assignees` REGEXP '^{$strtolower}$'");
            }else{
                return $query->whereRaw("`assignees` REGEXP '{$strtolower}'");
            }
        })
        ->when($request->global_search_value, function($query) use ($request){
            //$global_filter = strtolower(remove_vietnamese_accents($request->global_search_value));
            $global_filter = strtolower($request->global_search_value);
            if (is_numeric($global_filter)) {
                return $query->whereRaw("`case_id` LIKE '%{$global_filter}%'")->orWhereRaw("`xml_jobid_title` LIKE '%{$global_filter}%'");
            }else if(substr($global_filter, 0, 1) === '#'){
                $multi_word_hashtag_search = explode(' ', $global_filter);
                $search_string_post_format = str_replace('#', '', $global_filter);
                return $query->whereRaw("`custom_hashtag` LIKE '%{$search_string_post_format}%'");
            }else if(substr($global_filter, 0, 1) === '@'){
                $multi_word_hashtag_search = explode(' ', $global_filter);
                $search_string_post_format = str_replace('@', '', $global_filter);
                return $query->whereRaw("`assignees` LIKE '%{$search_string_post_format}%'");
            }else if(substr($global_filter, 0, 1) === '!'){
                $multi_word_jobinforproduction_search = array_values(array_filter(explode('!', $global_filter)));
                /**dd($multi_word_jobinforproduction_search);*/
                foreach ($multi_word_jobinforproduction_search as $key => $value) {
                    $search_string_post_format = trim(str_replace('!', '', $value));
                    /**dd($search_string_post_format);*/
                    $query->whereRaw("`xml_jobinfoproduction` LIKE '%{$search_string_post_format}%'");
                }
                return $query;
            }else{
                return $query->whereRaw("`xml_title_contents` LIKE '%{$global_filter}%'");
            }
        })
        // ->when($request->start, function($query) use ($request){
        //     return $query->offset($request->start);
        // })
        // ->when($request->length, function($query) use ($request){
        //     return $query->limit($request->length);
        // })
        // ->orderByRaw("
        //     CASE status_of_case
        //         WHEN 'in progress' THEN 1
        //         WHEN 'downloaded' THEN 2
        //         WHEN 'ready' THEN 3
        //         WHEN 'check' THEN 4
        //         WHEN 'feedback' THEN 5
        //         WHEN 'pause' THEN 6
        //         WHEN 'notified/uploading to s3' THEN 7
        //         WHEN 'uploaded to s3' THEN 8
        //         WHEN 'new' THEN 9
        //         WHEN 'downloading' THEN 10
        //         WHEN 'retry_zip' THEN 11
        //         WHEN 'zipped' THEN 12
        //     END
        //     ")
        //->orderBy('expected_delivery_date_coalesce', 'DESC')
        ->whereIn('archived_case', ['1'])
        ->get()->keyBy('case_id')->toArray();
        /**dd($downloadlist_list_view_total);*/




        $counting_number = 1;
        $LAST_UPDATED_SORTORDER = [];
        $CREATED_AT_UPDATED_SORTORDER = [];
        $EXPECTED_DELIVERY_DATE_COALESCE_SORTORDER = [];
        $EXPECTED_DELIVERY_DATE_COALESCE_SORTORDER_INVERSE = [];

        foreach ($downloadlist_list_view as $item => &$working_shift_detail) {
            $working_shift_detail->number = $counting_number;
            
            $working_shift_detail->expected_delivery_time_custom_grouping = 1;
            $working_shift_detail->expected_delivery_date_coalesce_sortorder = 1;

            /** set the default so the property is present. It will be overwritten when it is there if people try to view the manual download list before any files have been downloaded */
            $working_shift_detail->example_file_count = null;
            $working_shift_detail->new_file_count = null;  
            $working_shift_detail->ready_file_count = null;

            $counting_number++;
            $LAST_UPDATED_SORTORDER[$item] = $working_shift_detail->updated_at;
            $CREATED_AT_UPDATED_SORTORDER[$item] = $working_shift_detail->created_at;
            
            if($working_shift_detail->expected_delivery_date_coalesce == NULL){
                /** should we skip these? or just put in todays date */
                $carbon_now_formated_longform = Carbon::now()->format('Y-m-d H:i:s');
                $EXPECTED_DELIVERY_DATE_COALESCE_SORTORDER[$item] = $carbon_now_formated_longform;
                $EXPECTED_DELIVERY_DATE_COALESCE_SORTORDER_INVERSE[$item] = $carbon_now_formated_longform;
            }else{
                $EXPECTED_DELIVERY_DATE_COALESCE_SORTORDER[$item] = $working_shift_detail->expected_delivery_date_coalesce;
                $EXPECTED_DELIVERY_DATE_COALESCE_SORTORDER_INVERSE[$item] = $working_shift_detail->expected_delivery_date_coalesce;
            }
        }

        /**dump($LAST_UPDATED_SORTORDER);*/


        uasort($LAST_UPDATED_SORTORDER, "compareByTimeStampconvert");
        /**dd($LAST_UPDATED_SORTORDER);*/
        uasort($CREATED_AT_UPDATED_SORTORDER, "compareByTimeStampconvert");
        /**dd($CREATED_AT_UPDATED_SORTORDER);*/
        //uasort($EXPECTED_DELIVERY_DATE_COALESCE_SORTORDER, "compareByTimeStampconvert");
        /**dump($EXPECTED_DELIVERY_DATE_COALESCE_SORTORDER);*/
        //uasort($EXPECTED_DELIVERY_DATE_COALESCE_SORTORDER_INVERSE, "compareByTimeStampconvertInverse");
        /**dd($EXPECTED_DELIVERY_DATE_COALESCE_SORTORDER_INVERSE);*/

        $anumber = 1;
        foreach ($LAST_UPDATED_SORTORDER as $keys => $things){
            $downloadlist_list_view[$keys]->last_updated_sortorder = $anumber;
            $anumber++;
        }


        $anumber = 1;
        foreach ($CREATED_AT_UPDATED_SORTORDER as $keys => $things){
            $downloadlist_list_view[$keys]->created_at_sortorder = $anumber;
            $anumber++;
        }        
        /**dd($downloadlist_list_view);*/

        // $carbon_now_date = Carbon::now()->startOfDay();

        // $revrsed = array_reverse($EXPECTED_DELIVERY_DATE_COALESCE_SORTORDER);



        // $anumber = 1;
        // foreach ($EXPECTED_DELIVERY_DATE_COALESCE_SORTORDER as $keys => $things){

        //     $carbon_check_expected_delivery_date = Carbon::createFromFormat('Y-m-d H:i:s', $things);

        //     if($carbon_check_expected_delivery_date->greaterThanOrEqualTo($carbon_now_date)){
        //         /** put in the first (TOP) category */
        //         //$downloadlist_list_view[$keys]->expected_delivery_time_custom_grouping = 0;
        //     }else{
        //         /** put in the second (BOTTOM) category */
        //         $downloadlist_list_view[$keys]->expected_delivery_time_custom_grouping = 1;
        //         $downloadlist_list_view[$keys]->expected_delivery_date_coalesce_sortorder = $anumber;
        //     }

        //     $anumber++;
        // }



        // $anumber = 1;
        // foreach ($EXPECTED_DELIVERY_DATE_COALESCE_SORTORDER_INVERSE as $keys => $things){

        //     $carbon_check_expected_delivery_date = Carbon::createFromFormat('Y-m-d H:i:s', $things);

        //     if($carbon_check_expected_delivery_date->greaterThanOrEqualTo($carbon_now_date)){
        //         /** put in the first (TOP) category */
        //         $downloadlist_list_view[$keys]->expected_delivery_time_custom_grouping = 0;
        //         $downloadlist_list_view[$keys]->expected_delivery_date_coalesce_sortorder = $anumber;
        //         $anumber++;
        //     }else{
        //         /** put in the second (BOTTOM) category */
        //         //$downloadlist_list_view[$keys]->expected_delivery_time_custom_grouping = 1;
        //     }

        // }



        /** to map the amount of files in new or example folders to the case_id */
        foreach($downloadlist_list_file_count_array as $inner_download_task_id_key => $inner_download_task_id_detail){
            
            foreach($downloadlist_list_view as $case_id_key => $case_id_details){
                /**dump($case_id_key);*/
                
                if($inner_download_task_id_detail->case_id == $case_id_key){
                    /**dd($inner_download_task_id_detail);*/

                    if(!isset($downloadlist_list_view[$case_id_key]->example_file_count)){
                        $downloadlist_list_view[$case_id_key]->example_file_count = null;
                    }
                    if(!isset($downloadlist_list_view[$case_id_key]->new_file_count)){
                        $downloadlist_list_view[$case_id_key]->new_file_count = null;  
                    }
                    if(!isset($downloadlist_list_view[$case_id_key]->ready_file_count)){
                        $downloadlist_list_view[$case_id_key]->ready_file_count = null;
                    }

                    if($inner_download_task_id_detail->type == 'example'){
                        $downloadlist_list_view[$case_id_key]->example_file_count = $inner_download_task_id_detail->file_count;
                    }elseif($inner_download_task_id_detail->type == 'new'){
                        $downloadlist_list_view[$case_id_key]->new_file_count = $inner_download_task_id_detail->file_count;
                    }elseif($inner_download_task_id_detail->type == 'ready'){
                        $downloadlist_list_view[$case_id_key]->ready_file_count = $inner_download_task_id_detail->file_count;
                    }else{
                        dd('encountered type we did not expect');
                    }
                }else{
                    continue;
                }
            }
        }

        /**dd($downloadlist_list_view);*/
        /**echo '<pre>';print_r($downloadlist_list_view);echo '</pre>';die();*/


        if((int)$request->draw >= 0){

            $controller_variables = app('App\Http\Controllers\ManagemanualdownloadlistController')->index(true, 'manualdownloadlist');
            
            /**dump('AjaxManagedownloadlistTabsController');*/
            /**dd($controller_variables);*/

            /** to be able to bring these numbers to the page when it is refreshed There is surely a better way of doing this but this seems quicker than ajax. */

            // $controller_variables['total_working_days'] = $total_working_days;
            // $controller_variables['total_working_days_to_work'] = $total_working_days_to_work;
            // $controller_variables['total_working_time'] = $total_working_time;
            // $controller_variables['remaining_working_days'] = $remaining_working_days;
            // $controller_variables['total_overtime'] = $total_overtime;
            // $controller_variables['total_fines'] = $total_fines;
            // $controller_variables['total_min_working_hours'] = $total_min_working_hours;
            // $controller_variables['remaining_working_hours'] = $remaining_working_hours;
            // $controller_variables['total_min_working_hours_as_of_today'] = $total_min_working_hours_as_of_today;
            // $controller_variables['remaining_working_hours_as_of_today'] = $remaining_working_hours_as_of_today;
            // $controller_variables['total_working_time_elapsed'] = $total_working_time_elapsed;

            $extends_app = $controller_variables;
        }else{
            $extends_app = compact([]);
        }

        //$employee_details = DB::table('employees')->get()->keyBy('user_id')->toArray();

        // $auth_user_permissions = $this->appuserRepo->getAuthUserDetails()->permissions;
        // $auth_user_permissions = explode(';', $auth_user_permissions);

        /**$BASIC_SALARY = DB::table('v_manage_company_departments')->sum('active_employee_count');*/
        /**$WORKING_SALARY = DB::table('v_manage_company_departments')->sum('total_employee_count');*/

        
        $recordsTotal = count($downloadlist_list_view_total);
        $recordsFiltered = count($downloadlist_list_view_total);

        $status_grouping_sort_order = config('base.job_statuses');


        /** to have the jobs that are due soon at the uppper most top  e.g up to 7 days in the future should be enough */
        /** to have all the jobs that are already overdue? directly beneath the first group.. */
        /** to have all the jobs that are greate than current date + 7 days at the very bottom */

        /** we add to the grouping class */

        // TODAY - TODAY + 1 YEAR  == 

        // (ANYTHING OVER TODAY + 1 YEAR HIDDEN) // SET BY $request->max_date

        // YESTERDAY - YESTERDAY SUB 2 MONTHS

        // (ANYTHING OVER YESTERDAY SUB 2 MONTHS HIDDEN) // SET BY $request->min_date


        $currently_downloading_aria2c = DB::table('tasks_manual_downloads_files')->select('case_id', 'type')->whereNotIn('state', ['notified'])->get()->groupBy('case_id')
        ->map(function ($ts) {
            return $ts->keyBy('type');
        })->toArray();

        /**dd($currently_downloading_aria2c);*/

        return dataTables($downloadlist_list_view)
        ->setTransformer(new \App\Transformers\manualdownloadlistListinfoTransformer(['locale' => $locale, 'status_grouping_sort_order' => $status_grouping_sort_order, 'search_query_type_highlighting' => $global_filter, 'currently_downloading_aria2c' => $currently_downloading_aria2c]))
        ->with(['extends_app' => $extends_app])
        ->setTotalRecords($recordsTotal)
        ->setFilteredRecords($recordsFiltered)
        ->skipPaging()
        ->smart(false)
        ->make(true);
    }

    /**
     * Get Manage manualdownloadlist Add manualdownloadlist ColorBox
     *
     * @author sigmoswitch
     * @return \Illuminate\View\View
     */
    public function getManagemanualdownloadlist_TabAccounting_add_manualdownloadlist_CB()
    {
        $selectize_employee_list_formated_json = DB::table('v_employees_basic')->where('user_status', 1)->get()->toArray();
        /**dd($selectize_employee_list_formated_json);*/

        $selectize_selected_employee_formated_json = [];
        /**dd($selectize_selected_employee_formated_json);*/

        $this->data = compact([]);
        $this->js = compact('selectize_employee_list_formated_json', 'selectize_selected_employee_formated_json');

        return $this->render('manage_manualdownloadlist.details.add_manualdownloadlist');
    }

    /**
     * Get Manage manualdownloadlist Edit manualdownloadlist ColorBox
     *
     * @author sigmoswitch
     * @return \Illuminate\View\View
     */
    public function getManagemanualdownloadlist_TabAccounting_edit_manualdownloadlist_CB($id)
    {
        try {
            $IdENCRYPTED = $id;
            $id = Crypt::decryptString($id);
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            abort('404');
        }

        $advance_payment_row = $this->advancepaymentRepo->findById($id)->toArray();
        /**dd($advance_payment_row);*/

        $selectize_employee_list_formated_json = DB::table('v_employees_basic')->get()->toArray();
        /**dd($selectize_employee_list_formated_json);*/

        $recipients_array = explode(',', $advance_payment_row['fk_user_id']);
        /**dump($recipients_array);*/

        $selectize_selected_employee_formated_json = DB::table('employees')->whereIn('user_id', $recipients_array)->get()->toArray();
        /**dd($selectize_selected_employee_formated_json);*/


        $manage_edit_downloadlist = route('downloadlist.edit_downloadlist', ['id' => $IdENCRYPTED]);


        $this->data = compact('advance_payment_row', 'id', 'manage_edit_downloadlist', 'IdENCRYPTED');
        $this->js = compact('manage_edit_downloadlist', 'selectize_employee_list_formated_json', 'selectize_selected_employee_formated_json');

        return $this->render('manage_manualdownloadlist.details.edit_manualdownloadlist');
        /***/
    }

}
