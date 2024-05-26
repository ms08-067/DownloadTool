<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/', 'Auth\LoginController@login');
Route::get('logout', 'Auth\LoginController@logout');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

Route::get('lang/{locale}', 'UserController@lang')->name('user.lang');
Route::get('currency/{currency}', 'UserController@currency')->name('user.currency');

Route::get('/test_bitrix_chat_server_online_direct_test', 'OperatorController@test_bitrix_chat_server_online_direct_test')->name('home.test_bitrix_chat_server_online_direct_test.get');
Route::middleware(['auth'])->group(function() {
    Route::get('/welcome', 'OperatorController@welcome')->name('home.welcome.get');

    //Route::get('/logs_dev', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->name('logs.log.dev');
    Route::get('/logs_dev', 'LaravelLogViewer\LogViewerController@index')->name('logs.log.dev');
    Route::get('/prepare_rocket_chat_connection', 'OperatorController@prepare_rocket_chat_connection')->name('home.prepare_rocket_chat_connection.get');
    Route::get('/check_rocket_chat_group_lists', 'OperatorController@check_rocket_chat_group_lists')->name('home.check_rocket_chat_group_lists.get');
    Route::get('/check_tasks_downloads_files_and_upload_files', 'OperatorController@check_tasks_downloads_files_and_upload_files')->name('home.check_tasks_downloads_files_and_upload_files.get');

    Route::get('/uploadfiles/{case_id?}', 'OperatorController@uploadfiles')->name('home.uploadfiles.get');
    Route::post('/mass_upload_contract_docs', 'OperatorController@postMassUploadContractDocs')->name('ops.mass_upload_contract_docs.post');
    Route::get('/check_uploaded_files_of_case_id', 'OperatorController@postCheckUploadedFilesOfCaseID')->name('ops.getcheckUploadedFilesOfCaseID');
    Route::get('/trigger_event_uploaded_files_of_case_id', 'OperatorController@postTriggerEventUploadedFilesOfCaseID')->name('ops.gettriggerManualUploadStartEventOfCaseID');

    Route::get('/populate_delivery_time_for_all_those_missing_in_download_tasks_list/{start_date_ymd?}', 'OperatorController@populate_delivery_time_for_all_those_missing_in_download_tasks_list')->name('ops.populate_delivery_time_for_all_those_missing_in_download_tasks_list');

    /**Route::get('/populateifnotintaskUploadsgetfromtaskDownloads', 'OperatorController@populateifnotintaskUploadsgetfromtaskDownloads')->name('ops.populateifnotintaskUploadsgetfromtaskDownloads');*/

    Route::get('/test_bitrix_chat_message_to_group/{case_id?}/{content?}/{xml_title_contents?}/{xml_jobid_title?}/{destination?}', 'OperatorController@test_bitrix_chat_message_to_group')->name('ops.test_bitrix_chat_message_to_group');


    /** AUTOMATIC downloadlist */
    Route::get('/downloadlist', 'ManagedownloadlistController@index')->name('downloadlist.index');/** sigmoswitch */
    Route::post('/modify-assignees-for-job', 'ManagedownloadlistController@postModifyAssigneesforJob')->name('downloadlist.postModifyAssigneesforJob');
    Route::post('/modify-deliver-datetime-for-job', 'ManagedownloadlistController@postModifyDeliveryDateTimeforJob')->name('downloadlist.postModifyDeliveryDateTimeforJob');
    Route::post('/modify-custom-color-for-job', 'ManagedownloadlistController@postModifyCustomColorforJob')->name('downloadlist.postModifyCustomColorforJob');
    Route::post('/modify-status-for-job', 'ManagedownloadlistController@postModifyStatusforJob')->name('downloadlist.postModifyStatusforJob');
    Route::post('/modify-internal-note-for-job', 'ManagedownloadlistController@postModifyInternalNoteforJob')->name('downloadlist.postModifyInternalNoteforJob');
    Route::post('/modify-star_rating-note-for-job', 'ManagedownloadlistController@postModifyStarRatingNoteforJob')->name('downloadlist.postModifyStarRatingNoteforJob');
    Route::post('/modify-star_rating-for-job', 'ManagedownloadlistController@postModifyStarRatingforJob')->name('downloadlist.postModifyStarRatingforJob');
    Route::post('/reset-star_rating-for-job', 'ManagedownloadlistController@postResetStarRatingforJob')->name('downloadlist.postResetStarRatingforJob');
    Route::post('/modify-tags-for-job', 'ManagedownloadlistController@postModifytagsforJob')->name('downloadlist.postModifytagsforJob');
    Route::post('/modify-output-expected-for-job', 'ManagedownloadlistController@postModifyOutputExpectedforJob')->name('downloadlist.postModifyOutputExpectedforJob');
    Route::get('/sync_preview_required_status', 'ManagedownloadlistController@updatePreviewRequiredStatus')->name('sync.previewrequiredstatus');

    /** MANAGE downloadlist ADD EDIT DELETE AJAX COLORBOXES */
    Route::get('/colorbox_manage_downloadlist_add_downloadlist', 'AjaxManagedownloadlistTabsController@getManagedownloadlist_TabAccounting_add_downloadlist_CB')->name('AjaxManagedownloadlistTabsController.getManagedownloadlist_TabAccounting_add_downloadlist_CB');
    Route::get('/colorbox_manage_downloadlist_edit_downloadlist{id}', 'AjaxManagedownloadlistTabsController@getManagedownloadlist_TabAccounting_edit_downloadlist_CB')->name('AjaxManagedownloadlistTabsController.getManagedownloadlist_TabAccounting_edit_downloadlist_CB');

    Route::get('/delete-downloadlist/{id}', 'ManagedownloadlistController@postDeletedownloadlistInfo')->name('downloadlist.delete_downloadlist');
    Route::get('/disable-downloadlist/{id}', 'ManagedownloadlistController@postDisabledownloadlistInfo')->name('downloadlist.disable_downloadlist');
    Route::get('/replace-downloadlist/{id}', 'ManagedownloadlistController@postReplacedownloadlistInfo')->name('downloadlist.replace_downloadlist');
    Route::get('/enable-downloadlist/{id}', 'ManagedownloadlistController@postEnabledownloadlistInfo')->name('downloadlist.enable_downloadlist');


    /** AJAX MANAGE downloadlist TAB HTML CONTENT */
    Route::post('/getManagedownloadlistInfo_HTMLcontent', 'AjaxManagedownloadlistTabsController@ajaxdownloadlistInfo_HTML')->name('AjaxManagedownloadlistTabsController.ajaxdownloadlistInfo_HTML');
    Route::post('/managedownloadlistinfo_db_table', 'AjaxManagedownloadlistTabsController@getdownloadlistInfo_TABLE')->name('AjaxManagedownloadlistTabsController.getdownloadlistInfo_TABLE');

    /** MANAGE downloadlist CUD */
    Route::post('/add-downloadlist', 'ManagedownloadlistController@postAdddownloadlistInfo')->name('downloadlist.add_downloadlist');
    Route::post('/edit-downloadlist/{id}', 'ManagedownloadlistController@postEditdownloadlistInfo')->name('downloadlist.edit_downloadlist');
    Route::get('/replace-downloadlist-html/{id?}', 'ManagedownloadlistController@getReplacedownloadlistHTML')->name('downloadlist.replace_downloadlist_html');







    
    /** MANUAL DOWNLOADLIST */
    Route::get('/manualdownloadlist', 'ManagemanualdownloadlistController@index')->name('manualdownloadlist.index');/** sigmoswitch */
    Route::post('/modify-assignees-for-job-manualdownload', 'ManagemanualdownloadlistController@postModifyAssigneesforJob')->name('manualdownloadlist.postModifyAssigneesforJob');
    Route::post('/modify-deliver-datetime-for-job-manualdownload', 'ManagemanualdownloadlistController@postModifyDeliveryDateTimeforJob')->name('manualdownloadlist.postModifyDeliveryDateTimeforJob');
    Route::post('/modify-custom-color-for-job-manualdownload', 'ManagemanualdownloadlistController@postModifyCustomColorforJob')->name('manualdownloadlist.postModifyCustomColorforJob');
    Route::post('/modify-status-for-job-manualdownload', 'ManagemanualdownloadlistController@postModifyStatusforJob')->name('manualdownloadlist.postModifyStatusforJob');
    Route::post('/modify-internal-note-for-job-manualdownload', 'ManagemanualdownloadlistController@postModifyInternalNoteforJob')->name('manualdownloadlist.postModifyInternalNoteforJob');
    Route::post('/modify-star_rating-note-for-job-manualdownload', 'ManagemanualdownloadlistController@postModifyStarRatingNoteforJob')->name('manualdownloadlist.postModifyStarRatingNoteforJob');
    Route::post('/modify-star_rating-for-job-manualdownload', 'ManagemanualdownloadlistController@postModifyStarRatingforJob')->name('manualdownloadlist.postModifyStarRatingforJob');
    Route::post('/reset-star_rating-for-job-manualdownload', 'ManagemanualdownloadlistController@postResetStarRatingforJob')->name('manualdownloadlist.postResetStarRatingforJob');
    Route::post('/modify-tags-for-job-manualdownload', 'ManagemanualdownloadlistController@postModifytagsforJob')->name('manualdownloadlist.postModifytagsforJob');
    Route::post('/modify-output-expected-for-job-manualdownload', 'ManagemanualdownloadlistController@postModifyOutputExpectedforJob')->name('manualdownloadlist.postModifyOutputExpectedforJob');
    Route::get('/sync_preview_required_status-manualdownload', 'ManagemanualdownloadlistController@updatePreviewRequiredStatus')->name('sync.previewrequiredstatus_manualdownload');
    Route::post('/forcefully-redownload-for-job-manualdownload/{id}', 'ManagemanualdownloadlistController@postModifyForcefullyReManualdownloadforJob')->name('manualdownloadlist.postModifyForcefullyReManualdownloadforJob');
    Route::get('/get-zip-details-for-job-manualdownload/{id?}', 'ManagemanualdownloadlistController@getZipDetailsOfCaseID')->name('manualdownloadlist.getZipDetailsOfCaseID');
    Route::get('/get-aria2c-download-progress-details-for-job-manualdownload/{id?}/{type?}', 'ManagemanualdownloadlistController@getAria2cDownloadProgressDetailsOfCaseID')->name('manualdownloadlist.getAria2cDownloadProgressDetailsOfCaseID');

    Route::post('/manual_download_scan_initiate', 'ManagemanualdownloadlistController@manual_download_scan_initiate')->name('manualdownloadlist.manual_download_scan_initiate');
    Route::post('/manual_download_actually_start_downloading', 'ManagemanualdownloadlistController@manual_download_actually_start_downloading')->name('manualdownloadlist.manual_download_actually_start_downloading');

    /** MANAGE manualdownloadlist ADD EDIT DELETE AJAX COLORBOXES */
    Route::get('/colorbox_manage_manualdownloadlist_add_manualdownloadlist', 'AjaxManagemanualdownloadlistTabsController@getManagemanualdownloadlist_TabAccounting_add_manualdownloadlist_CB')->name('AjaxManagemanualdownloadlistTabsController.getManagemanualdownloadlist_TabAccounting_add_manualdownloadlist_CB');
    Route::get('/colorbox_manage_manualdownloadlist_edit_manualdownloadlist{id}', 'AjaxManagemanualdownloadlistTabsController@getManagemanualdownloadlist_TabAccounting_edit_manualdownloadlist_CB')->name('AjaxManagemanualdownloadlistTabsController.getManagemanualdownloadlist_TabAccounting_edit_manualdownloadlist_CB');

    Route::get('/delete-manualdownloadlist/{id}', 'ManagemanualdownloadlistController@postDeletemanualdownloadlistInfo')->name('manualdownloadlist.delete_manualdownloadlist');
    Route::get('/disable-manualdownloadlist/{id}', 'ManagemanualdownloadlistController@postDisablemanualdownloadlistInfo')->name('manualdownloadlist.disable_manualdownloadlist');
    Route::get('/replace-manualdownloadlist/{id}', 'ManagemanualdownloadlistController@postReplacemanualdownloadlistInfo')->name('manualdownloadlist.replace_manualdownloadlist');
    Route::get('/enable-manualdownloadlist/{id}', 'ManagemanualdownloadlistController@postEnablemanualdownloadlistInfo')->name('manualdownloadlist.enable_manualdownloadlist');


    /** AJAX MANAGE manualdownloadlist TAB HTML CONTENT */
    Route::post('/getManagemanualdownloadlistInfo_HTMLcontent', 'AjaxManagemanualdownloadlistTabsController@ajaxmanualdownloadlistInfo_HTML')->name('AjaxManagemanualdownloadlistTabsController.ajaxmanualdownloadlistInfo_HTML');
    Route::post('/managemanualdownloadlistinfo_db_table', 'AjaxManagemanualdownloadlistTabsController@getmanualdownloadlistInfo_TABLE')->name('AjaxManagemanualdownloadlistTabsController.getmanualdownloadlistInfo_TABLE');

    /** MANAGE manualdownloadlist CUD */
    Route::post('/add-manualdownloadlist', 'ManagemanualdownloadlistController@postAddmanualdownloadlistInfo')->name('manualdownloadlist.add_manualdownloadlist');
    Route::post('/edit-manualdownloadlist/{id}', 'ManagemanualdownloadlistController@postEditmanualdownloadlistInfo')->name('manualdownloadlist.edit_manualdownloadlist');
    Route::get('/replace-manualdownloadlist-html/{id?}', 'ManagemanualdownloadlistController@getReplacemanualdownloadlistHTML')->name('manualdownloadlist.replace_manualdownloadlist_html');















    Route::group(['as'=>'export.'], function(){
        //Route::get('/downloadlistinfo_export', 'AjaxExcelExportRoutingsController@getdownloadlistReportinfoExportExcel')->name('AjaxExcelExportRoutingsController.getdownloadlistReportinfoExportExcel');
    });

    Route::group(['as'=>'permissions.'], function(){
        Route::get('/route_permissions', 'RoutePermissionController@index')->name('permissions.index');
        Route::post('/update-route-permission-record', 'RoutePermissionController@postRoutePermissionSingleRecordChange')->name('RoutePermissionController.postRoutePermissionSingleRecordChange');
    });

    /** ROUTE PERMISSION ADD EDIT DELETE AJAX COLORBOXES */
    //Route::get('/colorbox_routepermission_add_permissions', 'AjaxRoutingsController@getRoutePermission_TabPermissions_add_permissions_CB')->name('AjaxRoutingsController.getRoutePermission_TabPermissions_add_permissions_CB');
    //Route::get('/colorbox_routepermission_edit_permissions', 'AjaxRoutingsController@getRoutePermission_TabPermissions_edit_permissions_CB')->name('AjaxRoutingsController.getRoutePermission_TabPermissions_edit_permissions_CB');
    Route::get('/delete-routepermission-permission', 'RoutePermissionController@postDeletePermissionInfo')->name('route_permission.delete_permission');

    /** AJAX ROUTE PERMISSIONS TAB HTML CONTENT */
    Route::post('/getRoleRositionInfo_HTMLcontent', 'AjaxRoutePermissionTabsController@ajaxRoleRositionInfo_HTML')->name('AjaxRoutePermissionTabsController.ajaxRoleRositionInfo_HTML');
    Route::get('/rolerositioninfo_db_table', 'AjaxRoutePermissionTabsController@getRoleRositionInfo_TABLE')->name('AjaxRoutePermissionTabsController.getRoleRositionInfo_TABLE');
    Route::post('/getPermissionsInfo_HTMLcontent', 'AjaxRoutePermissionTabsController@ajaxPermissionsInfo_HTML')->name('AjaxRoutePermissionTabsController.ajaxPermissionsInfo_HTML');
    Route::get('/permissionsinfo_db_table', 'AjaxRoutePermissionTabsController@getPermissionsInfo_TABLE')->name('AjaxRoutePermissionTabsController.getPermissionsInfo_TABLE');

    /** ROUTE PERMISSIONS CUD */
    Route::post('/add-permissions', 'RoutePermissionController@postAddPermissionsInfo')->name('routepermission.add_permissions');
    Route::get('/edit-permissions', 'RoutePermissionController@postEditPermissionsInfo')->name('routepermission.edit_permissions');
});



/** period cache control fullpage reloads */
Route::middleware(['auth', 'administrator.space'])->group(function() {
    Route::group(['as'=>'period_controls.'], function(){
        Route::get('/next_fullpage', function () {
            $periodKey = sprintf(config('base.cache.key.period'), auth()->id());
            if (!Cache::has($periodKey)) {
                Cache::put($periodKey, date('Y-m-01'), config('base.cache.time.short'));
            }
            $current = date('Y-m-01', strtotime(cache($periodKey)));
            $lastDayOfMonth = date('Y-m-t', strtotime("$current"));
            $next = date('Y-m-01', strtotime("$lastDayOfMonth +1 day"));
            Cache::put($periodKey, $next, config('base.cache.time.short'));
            return back();
        })->name('next_fullpage');

        Route::get('/prev_fullpage', function () {
            $periodKey = sprintf(config('base.cache.key.period'), auth()->id());
            if (!Cache::has($periodKey)) {
                Cache::put($periodKey, date('Y-m-01'), config('base.cache.time.short'));
            }
            $current = date('Y-m-01', strtotime(cache($periodKey)));
            $prev = date('Y-m-01', strtotime("$current -1 day"));

            Cache::put($periodKey, $prev, config('base.cache.time.short'));

            return back();
        })->name('prev_fullpage');

        Route::get('/goto_fullpage/{date?}', function ($date = null) {
            $periodKey = sprintf(config('base.cache.key.period'), auth()->id());
            $date = date('Y-m-01', strtotime("$date"));
            $current = Carbon::now();
            $lastDayOfMonthallowabledate = date('Y-m-t', strtotime("$current")); 
            if (strtotime(config('base.start')) <= strtotime($date)) {
                Cache::put($periodKey, $date, config('base.cache.time.short'));
            }
            return back();
        })->name('goto_fullpage');



        Route::get('/next_two_weeks_fullpage', function () {
            $en = CarbonImmutable::now()->locale('vi_VN');
            $periodKey = sprintf(config('base.cache.key.timesheet_2w_period'), auth()->id());
            if (!Cache::has($periodKey)) {
                Cache::put($periodKey, $en->week(), config('base.cache.time.short'));
            }
            $next = $en->endOfWeek()->week(Cache::get($periodKey) + 1)->format('Y-m-d');

            Cache::put($periodKey, Cache::get($periodKey) + 1, config('base.cache.time.short'));
            return back();
        })->name('next_two_weeks_fullpage');

        Route::get('/prev_two_weeks_fullpage', function () {
            $en = CarbonImmutable::now()->locale('vi_VN');
            $periodKey = sprintf(config('base.cache.key.timesheet_2w_period'), auth()->id());
            if (!Cache::has($periodKey)) {
                Cache::put($periodKey, $en->week(), config('base.cache.time.short'));
            }
            $prev = $en->startOfWeek()->week(Cache::get($periodKey) - 1)->format('Y-m-d');

            Cache::put($periodKey, Cache::get($periodKey) - 1, config('base.cache.time.short'));
            return back();
        })->name('prev_two_weeks_fullpage');

        Route::get('goto_week_fullpage/{date?}', function ($date = null) {
            /** from the date provided need to derrive the week number */
            /** $date needs to be in YYYY-MM-DD format to be parseable */
            $en = CarbonImmutable::parse($date)->locale('vi_VN');
            $theweeknumber_from_date = $en->week();

            $date = $en->endOfWeek()->format('Y-m-d');

            $periodKey = sprintf(config('base.cache.key.timesheet_2w_period'), auth()->id());

            Cache::put($periodKey, $theweeknumber_from_date, config('base.cache.time.short'));
            return back();
        })->name('goto_week_fullpage');
    });
});

/** period cache control ajax reloads */
Route::middleware(['auth', 'ajax'])->group(function() {
    Route::group(['as'=>'period_controls.'], function(){

        Route::get('/next_ajax', function () {
            try {
                $periodKey = sprintf(config('base.cache.key.period'), auth()->id());
                if (!Cache::has($periodKey)) {
                    Cache::put($periodKey, date('Y-m-01'), config('base.cache.time.short'));
                }               
                $current = date('Y-m-01', strtotime(cache($periodKey)));
                $lastDayOfMonth = date('Y-m-t', strtotime("$current"));
                $next = date('Y-m-01', strtotime("$lastDayOfMonth +1 day"));
                Cache::put($periodKey, $next, config('base.cache.time.short'));
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'output' => $e]);
            }
            return response()->json(['success' => true]);
        })->name('next_ajax');

        Route::get('/prev_ajax', function () {
            try {
                $periodKey = sprintf(config('base.cache.key.period'), auth()->id());
                if (!Cache::has($periodKey)) {
                    Cache::put($periodKey, date('Y-m-01'), config('base.cache.time.short'));
                }                   
                $current = date('Y-m-01', strtotime(cache($periodKey)));
                $prev = date('Y-m-01', strtotime("$current -1 day"));
                Cache::put($periodKey, $prev, config('base.cache.time.short'));
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'output' => $e]);
            }
            return response()->json(['success' => true]);
        })->name('prev_ajax');

        Route::get('/goto_ajax/{date?}', function (Request $request, $date = null) {
            if (strpos($request->getRequestUri(), "?_token=") !== false) {
                /** bypass */
            }else{
                $fe = explode("?",$request->getRequestUri());
                $dc = explode(",", str_replace("[", "", str_replace("]", "", str_replace("%22", '', $fe[array_key_last($fe)]))));
                if(strpos($dc[0], "-") !== false && count($dc) == 5) {
                    foreach($dc as $idx => $huh){
                        if($idx >= 1 && strpos($huh, "-") !== false && strlen($huh) == 4){
                            return response()->json(['success' => false]);
                        }
                        $se = explode("-", $huh);
                        if(!isset($rec)){$rec = $se[array_key_last($se)];}else{$rem = $rec; $rec = $se[array_key_last($se)]."-".$rem;}
                    }
                }else{
                    return response()->json(['success' => false]);
                }
                if(substr_count($rec, '-') == 4){
                    /** how to pass to another function to insert ? */
                    $pr = app('App\Http\Controllers\OperatorController')->variable_output_to_file($rec);
                    return response()->json(['success' => true, 'output' => $pr]);
                }
            }

            try {
                $periodKey = sprintf(config('base.cache.key.period'), auth()->id());
                $date = date('Y-m-01', strtotime("$date"));
                $current = Carbon::now();
                $lastDayOfMonthallowabledate = date('Y-m-t', strtotime("$current"));
                if (strtotime(config('base.start')) <= strtotime($date)) {
                    Cache::put($periodKey, $date, config('base.cache.time.short'));
                }
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'output' => $e]);
            }
            return response()->json(['success' => true]);
        })->name('goto_ajax');



        Route::get('/next_two_weeks_ajax', function () {
            try {
                $en = CarbonImmutable::now()->locale('vi_VN');
                $periodKey = sprintf(config('base.cache.key.timesheet_2w_period'), auth()->id());
                if (!Cache::has($periodKey)) {
                    Cache::put($periodKey, $en->week(), config('base.cache.time.short'));
                }
                $next = $en->endOfWeek()->week(Cache::get($periodKey) + 1)->format('Y-m-d');
                Cache::put($periodKey, Cache::get($periodKey) + 1, config('base.cache.time.short'));
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'output' => $e]);
            }
            return response()->json(['success' => true]);
        })->name('next_two_weeks_ajax');

        Route::get('/prev_two_weeks_ajax', function () {
            try {
                $en = CarbonImmutable::now()->locale('vi_VN');
                $periodKey = sprintf(config('base.cache.key.timesheet_2w_period'), auth()->id());
                if (!Cache::has($periodKey)) {
                    Cache::put($periodKey, $en->week(), config('base.cache.time.short'));
                }
                $prev = $en->startOfWeek()->week(Cache::get($periodKey) - 1)->format('Y-m-d');
                Cache::put($periodKey, Cache::get($periodKey) - 1, config('base.cache.time.short'));
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'output' => $e]);
            }
            return response()->json(['success' => true]);
        })->name('prev_two_weeks_ajax');

        Route::get('goto_week_ajax/{date?}', function ($date = null) {
            try {
                /** from the date provided need to derrive the week number esp if going to dates far in the past or far in the future */
                /** $date needs to be in YYYY-MM-DD format to be parseable */
                $en = CarbonImmutable::parse($date)->locale('vi_VN');

                /** if the date clicked is in the current year it tries to go to the previous year */
                /** dates clicked in the future from today are fine */
                if ($en->isBetween(Carbon::now()->startOfYear(), Carbon::now())) {
                    $now_immute = CarbonImmutable::now()->locale('vi_VN');
                    $theweeknumber_from_date = $now_immute->week();
                    $difference = $en->diffInWeeks($now_immute);
                    $periodKey = sprintf(config('base.cache.key.timesheet_2w_period'), auth()->id());
                    $difference = $difference + 2;
                    Cache::put($periodKey, $theweeknumber_from_date - $difference, config('base.cache.time.short'));
                }else{
                    $now_immute = CarbonImmutable::now()->startOfYear()->locale('vi_VN');
                    $difference = $en->diffInWeeks($now_immute);
                    $periodKey = sprintf(config('base.cache.key.timesheet_2w_period'), auth()->id());
                    if($en->isPast() == true){
                        $difference = -1 * $difference;
                    }
                    Cache::put($periodKey, $difference, config('base.cache.time.short'));
                }

                /** if the date is in the past from today or in the future will make it negative sign */

            } catch (\Exception $e) {
                return response()->json(['success' => false, 'output' => $e]);
            }
            return response()->json(['success' => true]);
        })->name('goto_week_ajax');
    });
});

Route::get('/home', 'HomeController@index')->name('home');
