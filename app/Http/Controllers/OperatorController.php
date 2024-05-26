<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use Loggy;
use File;
use Debugbar;

use Exception;
use Validator;
use Cache;
use Artisan;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Encryption\DecryptException;

use App\Repositories\RocketChatAuthTokenRepository;
use App\Repositories\AppUserRepository;
use App\Repositories\HashtagRepository;

use App\Models\TaskUpload;
use App\Models\TaskUploadFile;
use App\Models\TaskUploadView;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

use App\Models\TaskDownload;
use App\Models\TaskDownloadFile;
use App\Models\TaskDownloadView;

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

use App\RC\RocketChatUser;
use App\RC\RocketChatClient;
use App\RC\RocketChatGroup;
use App\RC\RocketChatChannel;
use App\RC\RocketChatSettings;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

/**
 * Class OperatorController
 *
 * @author sigmoswitch
 * @package App\Http\Controller
 */
class OperatorController extends Controller
{
    /**
     * @var RocketChatAuthTokenRepository
     */
    protected $rocketchatauthtokenRepo;

    public $taskDownloadFile;
    public $taskDownload;

    public $taskUploadFile;
    public $taskUpload;

    public $TaskDownloadView;
    public $TaskUploadView;

    /**
     * @var AppUserRepository
     */
    protected $appuserRepo;

    /**
     * @var HashtagRepository
     */
    protected $hashtagRepo;

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
     * @param TaskDownloadFile $taskDownloadFile
     * @param TaskDownload $taskDownload
     * @param RocketChatAuthTokenRepository $rocketchatauthtokenRepo
     * @param TaskUpload $taskUpload
     * @param TaskUploadFile $taskUploadFile
     * @param AppUserRepository $appuserRepo
     * @param HashtagRepository $hashtagRepo
     * @param GroupRepository $grouprepo
     * @param TeamRepository $teamrepo
     * @param TaskUploadView $taskUploadView
     * @param TaskDownloadView $taskDownloadView
     */
    public function __construct(
        TaskDownloadFile $taskDownloadFile,
        TaskDownload $taskDownload,
        TaskUploadView $taskUploadView,
        TaskDownloadView $taskDownloadView,
        /***/
        RocketChatAuthTokenRepository $rocketchatauthtokenRepo,
        TaskUpload $taskUpload,
        TaskUploadFile $taskUploadFile,
        AppUserRepository $appuserRepo,
        HashtagRepository $hashtagRepo
        // TeamRepository $teamRepo,
        // GroupRepository $groupRepo
    ) {
        $this->taskDownloadFile = $taskDownloadFile;
        $this->taskDownload = $taskDownload;
        /***/
        $this->rocketchatauthtokenRepo = $rocketchatauthtokenRepo;

        $this->taskUploadFile = $taskUploadFile;
        $this->taskUpload = $taskUpload;

        $this->appuserRepo = $appuserRepo;
        $this->hashtagRepo = $hashtagRepo;
        // $this->groupRepo = $groupRepo;
        // $this->teamRepo = $teamRepo;
        $this->taskUploadView = $taskUploadView;
        $this->taskDownloadView = $taskDownloadView;
    }

    /**
     * Show mainmenu/ all opperations menu
     *
     * @author sigmoswitch
     * @return \Illuminate\View\View
     */
    public function welcome()
    {
        $this->data = compact([]);
        $this->js = compact([]);

        /**dd($this->getIp() ?? \Request::ip());*/
        /** is there a way to use the ip address and find an LDAP user that way? NO */
        /** then we will just use the ip address we get from the header function below */

        return $this->render('welcome.index');
    }

    public function getIp(){
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); /**just to be safe strip white spaces */
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
    }

    /**
     * Show uploadfiles page menu
     *
     * @author sigmoswitch
     * @return \Illuminate\View\View
     */
    public function uploadfiles($case_id = null)
    {
        $send_case_id_as_js_variable_for_selectize_search = null;
        if($case_id != null){
            try {
                $case_id = Crypt::decryptString($case_id);
                /**dd($case_id);*/

                /** if the case id coming in has a _ in it then for the selectize to be able to use it will have to send it as a variable to be used client side */
                if (strpos($case_id, '_') !== false) {
                    $send_case_id_as_js_variable_for_selectize_search = $case_id;
                }
            } catch (DecryptException $e) {
                Debugbar::addException($e);
                return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]); /** aborting */
            }
        }

        $urlPostMassUploadContracts = route('ops.mass_upload_contract_docs.post');

        /**$period = getPeriod();*/
        $locale = app()->getLocale();
        $currencyUnit = config('base.currency_unit')[Session::get('appcurrency')];

        $pastthreemonthsfromtoday_date = Carbon::now()->subMonth(6);
        /**dd($pastthreemonthsfromtoday_date);*/
        $selectize_employee_list_formated_json = DB::table('v_upload_files')->where('created_at', '>=', $pastthreemonthsfromtoday_date)->orWhere('case_id', 10101010)->get()->toArray();
        /**echo '<pre>';print_r($selectize_employee_list_formated_json);echo '</pre>';die();*/
        foreach($selectize_employee_list_formated_json as $case_id_key => $case_id_details){
            $selectize_employee_list_formated_json[$case_id_key]->encrypted_case_id = Crypt::encryptString($case_id_details->case_id);
        }
        /**dd($selectize_employee_list_formated_json);*/
        /**$selectize_employee_list_formated_json[10101010]*/

        if($case_id != null){
            $selectize_selected_employee_has_family_members_in_company_formated_json = [
                0 => [ 'fk_is_br24_employee' => $case_id],
                // 1 => [ 'fk_is_br24_employee' => '417'],
                // 2 => [ 'fk_is_br24_employee' => '418'],
            ];
        }else{
            $selectize_selected_employee_has_family_members_in_company_formated_json = [
                //0 => [ 'fk_is_br24_employee' => $case_id],
                // 1 => [ 'fk_is_br24_employee' => '417'],
                // 2 => [ 'fk_is_br24_employee' => '418'],
            ];
        }

        /**dd($selectize_selected_employee_has_family_members_in_company_formated_json);*/

        /**$oppC_getCheckUploadedFilesOfCaseID = route('ops.getcheckUploadedFilesOfCaseID');*/
        $oppC_getTriggerManualUploadStartEventOfCaseID = route('ops.gettriggerManualUploadStartEventOfCaseID');


        $this->data = compact('locale', 'currencyUnit');
        $this->js = compact('selectize_employee_list_formated_json', 'selectize_selected_employee_has_family_members_in_company_formated_json', 'urlPostMassUploadContracts', 'send_case_id_as_js_variable_for_selectize_search', 'oppC_getTriggerManualUploadStartEventOfCaseID');

        return $this->render('uploadfiles.index');
    }

    /**
     * Post email signature template
     *
     * @author sigmoswitch
     * @param Request $request
     * @return array
     */
    public function postMassUploadContractDocs(Request $request)
    {

        $whattheysent = $request->all();
        /**dump($whattheysent);*/

        try {
            $case_id = $whattheysent['case_id'];
            $encrypted_case_id = Crypt::decryptString($whattheysent['encrypted_case_id']);
            /** coming in is case_id encrypted*/

            if((int)$encrypted_case_id !== (int)$case_id){
                return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]); /** aborting */
            }
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            // abort('404');
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]); /** aborting */
        }


        /** when uploading the files need to make sure that the case_id is present.. */
        /** using the case_id we go into the db and grab any parent ids if they are available (depending on the xml) */
        /**dump($request->all());*/
        if($request->case_id == 'undefined'){
            //dump('case_id is undefined');
            return response()->json([
                'success' => false
            ]);
        }


        $file = $this->taskUpload->where('case_id', $whattheysent['case_id'])->first();

        /**dump($file);*/
        if (empty($file)) {
            return response()->json([
                'success' => false,
                'whattheysent' => $whattheysent,
                'error' => 'case_id not found on taskUpload table'
            ]);
        }

        /** how to determine if they have previously uploaded files for this caseID? */
        /** to know when to remove the files from the previous attempts ? */
        /** when does the try get increased? */



        /** because this is done per file we get errors due to them all performing this function at slightly different times */

        if($file->try >= 1){
            /** we have to remove everything from the previous attempts.. logs and all at this stage */
            $this->removeallpreviousattemptuploadfilesforthiscase($whattheysent['case_id']);

            $file->try = 0;
            $file->save();
        }



        /** if the case_id is blank we have to go error */
        /** use the fileId to reconstruct the folder tree for the files to sit in */

        /**dump($request->fileId);*/

        $folder_tree = explode('/', $request->fileId);
        /**dump($folder_tree);*/


        /**$whattheysent_originally = $request->all();*/
        $whattheysent = $request->file('file');

        $case_id = $request->case_id;
        /**dump($case_id);*/
        /**$getRealPath = $whattheysent->getRealPath();*/
        $getClientOriginalName = $whattheysent->getClientOriginalName();
        /**$getClientOriginalExtension = $whattheysent->getClientOriginalExtension();*/
        /**$getSize = $whattheysent->getSize();*/
        /**$getMimeType = $whattheysent->getMimeType();*/

        /**dump($getClientOriginalName);*/

        /** we have to be able to get the JOBID from the input together along with all the files */
        /** because we are handling each file indevidually we require the need to count how many success upload */
        /** and most likely cannot perform the zipping until all have been placed in the same correct place */

        /** this creates the root folder for the caseId */

        $fileName = $getClientOriginalName;
        /**dump('getClientOriginalName === ' . $getClientOriginalName);*/

        $path = storage_path()."/app".config('s3br24.temp_upload_folder').$case_id;
        /**dd($path);*/
        if(!\File::exists($path)) {
            \File::makeDirectory($path, 0777, true, true); /**make directory if not exists */
        }

        // $whoami =  exec('whoami');
        // dump($whoami);
        $cmd = 'mkdir -p ' . $path;
        $result = exec($cmd);
        /**dump($result);*/

        $remembering_which_folder_was_created = '';

        foreach($folder_tree as $folder_level_key => $folder_name){

            /** if the filename HAS the "&" symbol the kartik fileinput package removes it for some reason. which in turn makes the tool turn that file into a folder.. */
            /** we have to put the & symbol back in the right place for the filename */

            /**dump($fileName);*/
            if(strpos($folder_name, "~") !== false || strpos($folder_name, "*") !== false || strpos($folder_name, "&") !== false || strpos($folder_name, "^") !== false || strpos($folder_name, "%") !== false || strpos($folder_name, "$") !== false || strpos($folder_name, "#") !== false || strpos($folder_name, "@") !== false || strpos($folder_name, "!") !== false || strpos($folder_name, "+") !== false || strpos($folder_name, "=") !== false || strpos($folder_name, "{") !== false || strpos($folder_name, "}") !== false || strpos($folder_name, "[") !== false || strpos($folder_name, "]") !== false || strpos($folder_name, ";") !== false || strpos($folder_name, "'") !== false){
                if(strpos($folder_name, ".") !== false && $folder_level_key == array_key_last($folder_tree)){
                    /** seems to go wrong when there is a folder with these symbols also .. */
                    /** but most likely this should only happen if its the last index of the folder_tree.. */
                    /** we have the index where the file name is positioned and the & symbol found as well */
                    $length3 = strlen($fileName);
                    $arr3 = [];
                    for ($i=0; $i<$length3; $i++) {
                        $arr3[$i] = $fileName[$i];
                    }
                    /**dump($arr3);*/

                    $length4 = strlen($folder_name);
                    $arr4 = [];
                    for ($i=0; $i<$length4; $i++) {
                        $arr4[$i] = $folder_name[$i];
                    }
                    /**dump($arr4);*/

                    $arr3 = array_reverse($arr3);
                    $arr4 = array_reverse($arr4);
                    $refreshed_filename = [];

                    foreach($arr3 as $generic_character_index => $folder_name_with_symbol_character){
                        /**dump($folder_name_with_symbol_character);*/
                        if($folder_name_with_symbol_character == $arr4[$generic_character_index]){
                            $refreshed_filename[$generic_character_index] = $arr4[$generic_character_index];
                        }else{
                            if($arr4[$generic_character_index] == "~" || $arr4[$generic_character_index] == "*" || $arr4[$generic_character_index] == "&" || $arr4[$generic_character_index] == "^" || $arr4[$generic_character_index] == "%" || $arr4[$generic_character_index] == "$" || $arr4[$generic_character_index] == "#" || $arr4[$generic_character_index] == "@" || $arr4[$generic_character_index] == "!" || $arr4[$generic_character_index] == "+" || $arr4[$generic_character_index] == "=" || $arr4[$generic_character_index] == "{" || $arr4[$generic_character_index] == "}" || $arr4[$generic_character_index] == "[" || $arr4[$generic_character_index] == "]" || $arr4[$generic_character_index] == ";" || $arr4[$generic_character_index] == "'"){
                                $refreshed_filename[$generic_character_index] = $arr4[$generic_character_index];
                            }else{
                                /** all other character types we leave the same as the original */
                                $refreshed_filename[$generic_character_index] = $folder_name_with_symbol_character;
                            }
                        }
                    }
                    /**dump($refreshed_filename);*/
                    $refreshed_filename = array_reverse($refreshed_filename);
                    $fileName = implode($refreshed_filename);
                }
            }

            /** we should only do this is it is a folder */
            /** what happens if this happens when it encounteres a file if undercores in the name ? somehow it turns the file into a folder */

            /** seems that the jquery fileinput replaces occurances of brackets with underscores.. */
            /** which changes the ClientOriginalName */
            /** need to factor that into the code too */

            /** we have to preserve the file name NEVER MESS WITH THE FILE NAME  it is important */

            /**dump('$folder_name exactly from the uploader == '. $folder_name);*/

            /** the order is important it must be storing the pluging orignal value of the folder name */
            $absolute_original_folder_name = $folder_name;
            /**dump('BEFORE $absolute_original_folder_name = '. $absolute_original_folder_name);*/


            $original_folder_name = str_replace(')', '_', str_replace('(', '_', $folder_name));
            $folder_name = str_replace('_', ' ', $folder_name);
            $formatedfileName_for_checking_folder_name = str_replace(' ', '_', $fileName);

            /** what happens if the filename has brackets in the name that seems to screw up something for the upload */
            /**dump('$folder_level_key == '. $folder_level_key);*/
            /**dump('$original_folder_name == '. $original_folder_name);*/
            /**dump('$folder_name == '. $folder_name);*/
            /**dump('$formatedfileName_for_checking_folder_name == '. $formatedfileName_for_checking_folder_name);*/


            if (strpos($original_folder_name, $formatedfileName_for_checking_folder_name) !== false) {
                $bit_to_extract_from_folder_name = str_replace('_', ' ', explode($formatedfileName_for_checking_folder_name, $original_folder_name)[0]);
            }else{
                $bit_to_extract_from_folder_name = '';
            }
            /**dump('$bit_to_extract_from_folder_name ' . $bit_to_extract_from_folder_name);*/

            $fileName_tosaveas = str_replace($bit_to_extract_from_folder_name, "", $folder_name);
            /**dump('$fileName_tosaveas = '. $fileName_tosaveas);*/

            if (strpos($original_folder_name, $formatedfileName_for_checking_folder_name) !== false) {
                /**dump('at this current folder_leve_ley the actualy filename was detected in the interation');*/
            }else{
                $absolute_original_folder_name = str_replace($bit_to_extract_from_folder_name, "", $absolute_original_folder_name);
                /**dump('AFTER $absolute_original_folder_name = '. $absolute_original_folder_name);*/
            }


            /** if the original filename has a ( or a ) we need to put those back in since the upload package replaces those with underscores for the fileName function ... */
            if(strpos($fileName_tosaveas, ")") !== false || strpos($fileName_tosaveas, "(") !== false) {
                if(strpos($fileName, "_") !== false){
                    /** we check the original filename also has any underscores that we need to keep */

                    $length1 = strlen($fileName_tosaveas);
                    $arr1 = [];
                    for ($i=0; $i<$length1; $i++) { $arr1[$i] = $fileName_tosaveas[$i]; }
                    /**dump($arr1);*/

                    $length2 = strlen($fileName);
                    $arr2 = [];
                    for ($i=0; $i<$length2; $i++) { $arr2[$i] = $fileName[$i]; }

                    /**dump($arr2);*/

                    $arr1 = str_split($fileName_tosaveas); /** most definitely will show the brackets in the filename will never have any underscores */
                    $arr2 = str_split($fileName); /** most definitely will NOT show any brackers will always have underscores */

                    $resructured_filename = '';

                    /** it should not be possible to have two difference array sizes */

                    if(sizeof($arr1) ==  sizeof($arr2)){
                        foreach($arr1 as $character_index => $character_in_string){

                            if($character_in_string == $arr2[$character_index]){
                                $resructured_filename .= $character_in_string;
                            }else{
                                /** we check what the difference is */
                                if($character_in_string == ")" || $character_in_string == "("){
                                    /** */
                                    if($arr2[$character_index] == "_"){
                                         $resructured_filename .= $character_in_string;
                                    }else{
                                        /** is it even possible to be something else? */
                                    }
                                }else{
                                    /** its a character we are not watching out for */
                                }

                                /** keep the spaces as well */
                                if($character_in_string == " "){
                                    if($arr2[$character_index] == "_"){
                                         $resructured_filename .= $arr2[$character_index];
                                    }else{
                                        /** is it even possible to be something else? */
                                    }
                                }
                            }
                        }
                    }else{
                        /** we shouldn't proceed.. is it even possible ? */
                    }
                }else{
                    /** original filename does not have an underscore so we can safely use the fileName_tosaveas variable */
                    /** we can safely use the */
                    $resructured_filename = $fileName_tosaveas;
                }
            }else{
                /** we can safely use the getClientOriginalName */
                $resructured_filename = $getClientOriginalName;
            }

            /**dump('$resructured_filename');*/
            /**dump($resructured_filename);*/
            /**continue;*/

            if($folder_level_key == 0){
                /** we do not skip the root directory because it has already been created */

                if (strpos($original_folder_name, $formatedfileName_for_checking_folder_name) !== false) {
                    /**dump('the fileName is in the path');*/

                    if(!\File::exists($remembering_which_folder_was_created)) {
                        \File::makeDirectory($remembering_which_folder_was_created, 0777, true, true); /**make directory if not exists */
                    }

                    if($remembering_which_folder_was_created == ''){
                        \File::put($path.'/'.$resructured_filename, file_get_contents($request->file('file'))); /**store the file there*/
                    }else{
                        \File::put($remembering_which_folder_was_created.'/'.$resructured_filename, file_get_contents($request->file('file'))); /**store the file there*/
                    }
                }else{

                    //$extracted_folder_name = explode('_', $original_folder_name);
                    ///**dump($extracted_folder_name);*/
                    //$folderName = str_replace('_', ' ', str_replace($extracted_folder_name[0].'_', '', $original_folder_name));
                    ///**dump($folderName);*/


                    $extracted_folder_name = explode('_', $absolute_original_folder_name);
                    /**dump($extracted_folder_name);*/
                    $folderName = str_replace('_', ' ', str_replace($extracted_folder_name[0].'_', '', $absolute_original_folder_name));
                    /**dump($folderName);*/


                    $folder_name_to_use_added_on_top_of_tree = str_replace('_', ' ', $folderName);

                    if($remembering_which_folder_was_created == ''){
                        $remembering_which_folder_was_created .= $path.'/'.$folder_name_to_use_added_on_top_of_tree;
                    }else{
                        $remembering_which_folder_was_created .= '/'.$folder_name_to_use_added_on_top_of_tree;
                    }

                    /**dd($remembering_which_folder_was_created);*/

                    if(!\File::exists($remembering_which_folder_was_created)) {
                        \File::makeDirectory($remembering_which_folder_was_created, 0777, true, true); /**make directory if not exists */
                    }
                }
            }else{
                /**dump($original_folder_name);*/
                /**dump($folder_name);*/
                /**dump($formatedfileName_for_checking_folder_name);*/
                /**dump($fileName);*/
                /**dump($getClientOriginalName);*/
                /**die();*/
                if($original_folder_name == $formatedfileName_for_checking_folder_name){
                    /** we know that this is the file and what ever folder it should be in is where the file should be placed */

                    /**dump($remembering_which_folder_was_created);*/
                    if(!\File::exists($remembering_which_folder_was_created)) {
                        \File::makeDirectory($remembering_which_folder_was_created, 0777, true, true); /**make directory if not exists */
                    }
                    /**dump($remembering_which_folder_was_created.'/'.$getClientOriginalName);*/

                    if($remembering_which_folder_was_created == ''){
                        \File::put($path.'/'.$resructured_filename, file_get_contents($request->file('file'))); /**store the file there*/
                    }else{
                        \File::put($remembering_which_folder_was_created.'/'.$resructured_filename, file_get_contents($request->file('file'))); /**store the file there*/
                    }
                }else{
                    /** all other levels upwards we create the folder if the folder does not exists */
                    /** because the folder name originally has spaces but the plug in replaces spaces with underscores we have to try to replace those */
                    /** if the folder name originally has underscores there is no way to tell whether is was originally space or an underscore so will be changed to a space reguardless */
                    /** and now that we figure out that the filename automatically has brackets changes to underscores as well */

                    $folder_name_to_use_added_on_top_of_tree = str_replace('_', ' ', $folder_name);

                    if($remembering_which_folder_was_created == ''){
                        $remembering_which_folder_was_created .= $path.'/'.$folder_name_to_use_added_on_top_of_tree;
                    }else{
                        $remembering_which_folder_was_created .= '/'.$folder_name_to_use_added_on_top_of_tree;
                    }

                    /**dump($remembering_which_folder_was_created);*/

                    if(!\File::exists($remembering_which_folder_was_created)) {
                        \File::makeDirectory($remembering_which_folder_was_created, 0777, true, true); /**make directory if not exists */
                    }
                }
            }
        }

        /**die();*/

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * trigger event uploaded file(s) of case_id and reposition for zipping
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postTriggerEventUploadedFilesOfCaseID(Request $request)
    {
        $whattheysent = $request->all();
        /**dd($whattheysent);*/

        try {
            $case_id = $whattheysent['case_id'];
            $encrypted_case_id = Crypt::decryptString($whattheysent['encrypted_case_id']);
            /** coming in is case_id encrypted*/

            if((int)$encrypted_case_id !== (int)$case_id){
                return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]); /** aborting */
            }
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            // abort('404');
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]); /** aborting */
        }

        /** we will repurpose this function to be used by the worker */
        /** to look for the ones that have a state retry_zip */
        /** or just don't care */
        /** */
        $file = $this->taskUpload->where('case_id', $whattheysent['case_id'])->first();

        /**dump($file);*/
        if (empty($file)) {
            return response()->json([
                'success' => false,
                'whattheysent' => $whattheysent,
                'error' => 'case_id not found on taskUpload table'
            ]);
        }

        $queue_delay_seconds_manualul = DB::table('queue_delay_seconds_manualul')->first()->queue_delay_seconds;
        $the_current_computer_ip_initiating = $this->getIp() ?? \Request::ip();
        $last_updated_by = $this->appuserRepo->getAuthUserDetails()->user_id;

        \App\Jobs\ManualUL_checkthenzip::dispatch($case_id, $whattheysent['encrypted_case_id'], count(json_decode($whattheysent['fstack'])), $the_current_computer_ip_initiating, $last_updated_by)->delay(now()->addSeconds($queue_delay_seconds_manualul));

        return response()->json([
            'success' => true,
            'whattheysent' => $whattheysent
        ]);
    }

    /**
     * check uploaded file(s) of case_id and reposition for zipping
     *
     * @param Request $request
     * @return \illuminate\http\redirectresponse
     */
    public function postCheckUploadedFilesOfCaseID(Request $request)
    {
        /** we want this to actually be handled by the worker ... so how do we do that after the front end has uploaded everything to the server? */
        $whattheysent = $request->all();
        /**dd($whattheysent);*/

        try {
            $case_id = $whattheysent['case_id'];
            $encrypted_case_id = Crypt::decryptString($whattheysent['encrypted_case_id']);
            /** coming in is case_id encrypted*/

            if((int)$encrypted_case_id !== (int)$case_id){
                return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]); /** aborting */
            }
        } catch (DecryptException $e) {
            Debugbar::addException($e);
            // abort('404');
            return response()->json([ 'success' => false, 'errors' => ["Not Permitted"]]); /** aborting */
        }



        /** we will repurpose this function to be used by the scheduler */
        /** to look for the ones that have a state retry_zip */
        /** or just don't care */
        /** */
        $file = $this->taskUpload->where('case_id', $whattheysent['case_id'])->first();

        /**dump($file);*/
        if (empty($file)) {
            return response()->json([
                'success' => false,
                'whattheysent' => $whattheysent,
                'error' => 'case_id not found on taskUpload table'
            ]);
        }

        /** how to determine if they have previously uploaded files for this caseID? */
        /** to know when to remove the files from the previous attempts ? */
        /** when does the try get increased? */

        if($file->try >= 1){
            /** we have to remove everything from the previous attempts.. logs and all */
            //$this->removeallpreviousattemptuploadfilesforthiscase($whattheysent['case_id']);
        }





        $path = storage_path()."/app".config('s3br24.temp_upload_folder').$whattheysent['case_id'];
        /**dd($path);*/
        if(!\File::exists($path)) {
            \File::makeDirectory($path, 0777, true, true); /**make directory if not exists */
        }


        /** what happens if the user uploads mutiple zips....? how does amazon handle that? It will unzip them to which ever subdirectory the zip is on and overwrite the files if they exist */
        /** we can't limit the supported files types to exclude a certain file type */
        /** are we going to have to rename the folders as well? No, its because the system created them */
        /** you need to check how many folders there are if there are two then you should not move contents one directory down */
        /** only if there is one folder and no files */

        // dump($whattheysent['case_id']);

        $tempUploadFolder = storage_path()."/app".config('s3br24.temp_upload_folder');
        // dump($tempUploadFolder);
        $inner_tempUploadfolder = $tempUploadFolder.$whattheysent['case_id'];
        // dump($inner_tempUploadfolder);
        if(!\File::exists($inner_tempUploadfolder)) {
            \File::makeDirectory($inner_tempUploadfolder, 0777, true, true); /**make directory if not exists */
        }

        $number_of_files_in_tempUploadfolder_directory_maxdepth = (int)exec("find ".$inner_tempUploadfolder." -maxdepth 1 -type f | wc -l");
        $number_of_folders_in_tempUploadfolder_directory_maxdepth = (int)exec("find ".$inner_tempUploadfolder." -mindepth 1 -maxdepth 1 -type d | wc -l");
        /** probably need to check that there is a folder in there too */

        // dump($number_of_files_in_tempUploadfolder_directory_maxdepth);
        // dump($number_of_folders_in_tempUploadfolder_directory_maxdepth);

        if($number_of_files_in_tempUploadfolder_directory_maxdepth == 0 && $number_of_folders_in_tempUploadfolder_directory_maxdepth == 1){
            /** we have to move everything down one directory */
            /** if there is a folder contained inside and no files we need to move all the contents of the folder to the root case_id folder */
            /** except if the case_id xml_title_contents LIKE %Web-Bilder% then just keep the folder structure and everything */
            $check_xml_title_contents = $this->taskDownloadView->where('case_id', $whattheysent['case_id'])->first();

            if (strpos($check_xml_title_contents["xml_title_contents"], 'Web-Bilder') !== false) {
                /** this is the job where the folder structure is important */
            }else{
                /** fo this you need to have the folder name */
                $folder_name_to_eventually_remove_when_empty = exec("find ".$inner_tempUploadfolder." -mindepth 1 -maxdepth 1 -type d");
                $folder_name_to_eventually_remove_when_empty = str_replace($inner_tempUploadfolder."/", "", $folder_name_to_eventually_remove_when_empty);
                $folder_name_to_eventually_remove_when_empty = str_replace(" ", "\\ ", $folder_name_to_eventually_remove_when_empty);
                // dump($folder_name_to_eventually_remove_when_empty);

                // dump($inner_tempUploadfolder."/".$folder_name_to_eventually_remove_when_empty."/*");
                // dump($inner_tempUploadfolder);

                $cmd = "mv -v ".$inner_tempUploadfolder."/".$folder_name_to_eventually_remove_when_empty."/* ".$inner_tempUploadfolder;
                // dump($cmd);
                exec($cmd);

                /** and remove the source folder if it is empty so it is no longer part of the file contents */
                $cmd2 = "rm -R ".$inner_tempUploadfolder."/".$folder_name_to_eventually_remove_when_empty;
                // dump($cmd2);
                exec($cmd2);
            }
        }





        /**dump('done but now its the time to zip the directory and check that it can be unzipped before sending it to the queue for s3');*/
        /** we proably want to hang onto the zip for alittle while */
        /** you were thinking of the scenario where they uploaded something.. */
        $existing_try_count = $file->try;
        $file->try = $existing_try_count + 1;
        $file->save();


        $tempZipFolder = storage_path()."/app".config('s3br24.temp_zip_folder').$whattheysent['case_id'];
        if(!\File::exists($tempZipFolder)) {
            \File::makeDirectory($tempZipFolder, 0777, true, true); /**make directory if not exists */
        }

        /** log for list files of zip */
        $zip_log = storage_path()."/logs".config('s3br24.download_log') . 'zip_log';
        exec("mkdir -p $zip_log");

        $zip_log = $zip_log . '/' . $whattheysent['case_id'] . '_ready_zip.log';

        /**sleep(2);*/
        $cmd3 = "(cd ".$inner_tempUploadfolder."; zip -r ".$tempZipFolder."/ready.zip ./*) >> $zip_log";
        /**dump($cmd3);*/
        exec($cmd3);




        /** we test the zip if it can be unzipped or if there are errors */


        /**log for list files of zip*/
        $unzip_log = storage_path()."/logs".config('s3br24.download_log') . 'unzip_log';
        exec("mkdir -p $unzip_log");

        /** dd(null); */

        $unzip_log = $unzip_log . '/' . $whattheysent['case_id'] . '_ready_zip.log';

        $dirZip = $tempZipFolder."/ready.zip";

        /** query zip contents and export that info to a log file */
        /**dump('unzip -l '.$dirZip.' >> '.$unzip_log);*/

        $searchString = 'testing:';
        if(exec('grep ' . escapeshellarg($searchString) . ' ' . $unzip_log)) {
            /** if it has been tested then don't need to test again*/
        }else{
            /** perform test of zip */
            exec("unzip -l $dirZip >> $unzip_log");

            /** test the zip that has been downloaded .... */
            $cmd = "unzip -t " . $dirZip . ' >> '.$unzip_log;
            /**dump('$cmd');*/
            /**dump($cmd);*/

            exec($cmd);
        }





        /**dd(null);*/

        $count_CRC_error = 0;
        $try_to_zip_again = false;
        $fropen = fopen($unzip_log, 'r' );

        $unzip_log_to_string = '';
        if ($fropen) {
            while (($line = fgets($fropen)) !== false) {
                if (strpos($line, 'bad CRC') !== false) {
                    /** file line has some indication of CRC error therefore count it */
                    $count_CRC_error++;
                }
                if (strpos($line, 'At least one error was detected in') !== false && strpos($line, $file->local) !== false) {
                    /** undeniably there was at least an error */
                    $try_to_zip_again = true;
                }

                $unzip_log_to_string .= $line .'<br>';
            }
            fclose($fropen);
        } else {
            /** error opening the log file. force download the zip again */
            $try_to_zip_again = true;
        }

        /** we need to keep track of the state changes through the lifetime of the files. */
        /** to be able to upload to s3 on the schedule */
        /** since being able to access the upload tool and selecting files doesn't necessarily mean it will immediately get to the bucket. */
        /** plus we need to check that its there and can also be able to send a message. */

        if($try_to_zip_again == true || $count_CRC_error > 0){
            /** return the zip file row back to what is was so that it can be re-downloaded! not unzipped again because that would be a waste of time */
            /** we let the scheduler handle it */
            $file->try = 0;
            $file->state = 'retry_zip';
            $file->save();

            /** we have to alert them that there is a problem with the files being zipped */
            return response()->json([
                'success' => false,
                'whattheysent' => $whattheysent,
                'unzip_log' => $unzip_log_to_string
            ]);
        }else{
            /** set the states back to the default so that the scheduler can take over again */

            /** what do we do with the previous attempt files? that are already on the jobFolder/ready folder and then s3? */

            $file->state = 'zipped';

            $file->move_to_jobfolder = 0;
            $file->move_to_jobfolder_tries = 0;
            $file->sending_to_s3 = 0;
            $file->sending_to_s3_tries = 0;

            $the_current_computer_ip_initiating = $this->getIp() ?? \Request::ip();
            $last_updated_by = $this->appuserRepo->getAuthUserDetails()->user_id;
            $current_time_stamp = Carbon::now()->timestamp;

            if($file->initiator == ''){
                $file->initiator = '['.$the_current_computer_ip_initiating.'-'.$last_updated_by.'-'.$current_time_stamp.']';
            }else{
                $file->initiator = $file->initiator.",".'['.$the_current_computer_ip_initiating.'-'.$last_updated_by.'-'.$current_time_stamp.']';
            }

            $file->save();
        }




        /** since we have the list of files in the stack can we compare that with the contents on the zip */


        /** what are we trying to check at this stage? */
        $array_fstack = json_decode($whattheysent['fstack']);
        /**dd($array_fstack);*/
        // foreach($array_fstack as $file_details){
        //     dump($file_details);
        // }




        /** using zip archive check that the new zip contents are the same as the temp_upload directory */

        $number_of_files_in_directory = exec("find ".$inner_tempUploadfolder." -type f | wc -l");
        $number_of_folders_in_directory_min_depth = exec("find ".$inner_tempUploadfolder." -mindepth 1 -type d | wc -l");


        /**dump('try_to_download_again');*/
        /**dump($try_to_download_again);*/
        $zipArchive = new \ZipArchive();
        $tryOpeningZip = $zipArchive->open($dirZip);

        /**dump('========================');*/

        /**dump('$count_inflated_files');*/
        /**dump($count_inflated_files);*/
        /**dd(null);*/

        /** if one encountered but it is not yet unzipped we have to wait longer */

        /** if the logs are not built consistently then the files never leave this loop and will eventually fail hard */
        $very_specific_count = $number_of_files_in_directory + $number_of_folders_in_directory_min_depth;


        $number_of_files_in_filestack = count($array_fstack);


        // dump('number_of_files_in_directory '.$inner_tempUploadfolder . ' => ' . $number_of_files_in_directory);
        // dump('number_of_folders_in_directory_min_depth '.$inner_tempUploadfolder . ' => ' . $number_of_folders_in_directory_min_depth);
        // dump('$zipArchive->numFiles => ' .$zipArchive->numFiles);
        // dump('$number_of_files_in_filestack => ' .$number_of_files_in_filestack);

        if($number_of_files_in_directory == $number_of_files_in_filestack){
            /** all good */
        }else{
            return response()->json([
                'success' => false,
                'whattheysent' => $whattheysent,
                '$number_of_files_in_directory' => $number_of_files_in_directory,
                '$number_of_folders_in_directory_min_depth' => $number_of_folders_in_directory_min_depth,
                '$zipArchive->numFiles' => $zipArchive->numFiles,
                '$number_of_files_in_filestack' => $number_of_files_in_filestack
            ]);
        }

        if($very_specific_count == $zipArchive->numFiles || $number_of_files_in_directory == $zipArchive->numFiles){
            /** all good */
        }else{
            return response()->json([
                'success' => false,
                'whattheysent' => $whattheysent,
                '$number_of_files_in_directory' => $number_of_files_in_directory,
                '$number_of_folders_in_directory_min_depth' => $number_of_folders_in_directory_min_depth,
                '$zipArchive->numFiles' => $zipArchive->numFiles,
                '$number_of_files_in_filestack' => $number_of_files_in_filestack
            ]);
        }

        /** close at the end */
        $zipArchive->close();

        /** save accurate number of files saved to db upon no error */
        $file->custom_output_real = $number_of_files_in_filestack;
        $file->save();


        /** we can enter it straight into the queue here because it has already been zipped... */
        /** you might need to have it split this into another queue so that it is handled by the server side and not from client side.. */
        /** imagine if they upload many many files and especially big ones. the client will timeout waiting for the files to be uploaded -> zipped -> zip tested etc.. */
        /** 504 GATEWAY TIMEOUT NGINX */

        return response()->json([
            'success' => true,
            'whattheysent' => $whattheysent
        ]);
    }

    /**
     * removeallpreviousattemptuploadfilesforthiscase
     *
     * @author sigmoswitch
     * @return
     */
    public function removeallpreviousattemptuploadfilesforthiscase($case_id)
    {
        /**dump('need_to_remove_previous_attempts_at_case_id_upload make clean slate');*/
        /**dd($case_id);*/
        /** we do the removal and we don't need to return the retry count to 0 because we want to keep track of how many times they make a zip attempt so educational purposes */

        /** places we have to remove from */
        /** the app/data_sdb_unzipfolder */
        /** the app/data_sdb_temp_upload_folder */
        /** the app/data_sdb_temp/zip_log */
        /** the app/data_sdb_temp/unzip_log */

        $file = $this->taskUpload->where('case_id', $case_id )->first();

        /** we have to check if the process is still running and if it is we kill it */

        $check_if_pid_still_running = exec("ps aux | awk '{print $1 }' | grep ". $file->pid);
        if($check_if_pid_still_running == $file->pid){
            /** it is still running under the same pid.. lucky us, just let it keep going. */
            $cmd = 'kill -9 '.$file->pid;
            exec($cmd);
        }
        /** hopefully it manages to kill the process and does not make the rest get stuck */


        $tempUploadFolder = storage_path()."/app".config('s3br24.temp_upload_folder');
        /**dump($tempUploadFolder);*/
        $inner_tempUploadfolder = $tempUploadFolder.$case_id;
        /**dump($inner_tempUploadfolder);*/
        $fs = new Filesystem();
        if(File::exists($inner_tempUploadfolder)) {
            /**dump($inner_tempUploadfolder . ' exists');*/
            /**dump('attempting deleting '. $inner_tempUploadfolder);*/
            $fs->cleanDirectory($inner_tempUploadfolder);
            $files1 = $fs->files($inner_tempUploadfolder);
            $fs->delete($files1);
            exec('rm -R '.$inner_tempUploadfolder."/");
        }

        if(File::exists($inner_tempUploadfolder)){
            try {
                File::delete($inner_tempUploadfolder);
                exec('rm -R '.$inner_tempUploadfolder);
            } catch (FileNotFoundException $e) {
                dd($e);
            }
        }

        /**dd(null);*/

        $tempZipFolder = storage_path()."/app".config('s3br24.temp_zip_folder').$case_id;

        $dirZip = $tempZipFolder."/ready.zip";
        if(File::exists($dirZip)){
            /**dump($dirZip . ' exists');*/
            /**dump('attempting deleting '. $dirZip);*/
            try {
                File::delete($dirZip);
            } catch (FileNotFoundException $e) {
                dd($e);
            }
        }
        if(File::exists($tempZipFolder)){
            /**dump($tempZipFolder . ' exists');*/
            /**dump('attempting deleting '. $tempZipFolder);*/
            try {
                File::delete($tempZipFolder);
            } catch (FileNotFoundException $e) {
                dd($e);
            }
        }



        /**log for list files of zip*/
        $zip_log = storage_path()."/logs".config('s3br24.download_log') . 'zip_log';
        $zip_log = $zip_log . '/' . $case_id . '_ready_zip.log';
        /**log for list files of zip*/

        if(File::exists($zip_log)){
            /**dump($zip_log . ' exists');*/
            /**dump('attempting deleting '. $zip_log);*/
            try {
                File::delete($zip_log);
            } catch (FileNotFoundException $e) {
                dd($e);
            }
        }

        $unzip_log = storage_path()."/logs".config('s3br24.download_log') . 'unzip_log';
        $unzip_log = $unzip_log . '/' . $case_id . '_ready_zip.log';

        if(File::exists($unzip_log)){
            /**dump($unzip_log . ' exists');*/
            /**dump('attempting deleting '. $unzip_log);*/
            try {
                File::delete($unzip_log);
            } catch (FileNotFoundException $e) {
                dd($e);
            }
        }
    }

    /**
     * test_bitrix_chat_server_online
     *
     * @author sigmoswitch
     * @return
     */
    public function test_bitrix_chat_server_online()
    {
        /** since the log files originally are newly created with the root:root user:group we encounter permission denied */
        /** change the log files user:group to root:www-data:so that the webapp can write to those files. (the user that is running the supervisord jobs) */
        if(env('APP_ENV') == 'prod' || env('APP_ENV') == 'develop' || env('APP_ENV') == 'dev'){
            $path_bitrixAPIinfo = storage_path('logs'.'/bitrixAPIinfo/*');
            /**dd($path_bitrixAPIinfo);*/
            exec('sudo chmod 664 '. $path_bitrixAPIinfo);
            exec('sudo chown www-data:www-data '. $path_bitrixAPIinfo);

            $path_bitrixAPI = storage_path('logs'.'/bitrixAPI/*');
            /**dd($path_bitrixAPI);*/
            exec('sudo chmod 664 '. $path_bitrixAPI);
            exec('sudo chown www-data:www-data '. $path_bitrixAPI);
        }elseif(env('APP_ENV') == 'local'){
            $path_bitrixAPIinfo = storage_path('logs'.'/bitrixAPIinfo/*');
            /**dd($path_bitrixAPIinfo);*/
            exec('sudo chmod 664 '. $path_bitrixAPIinfo);
            exec('sudo chown root:www-data '. $path_bitrixAPIinfo);

            $path_bitrixAPI = storage_path('logs'.'/bitrixAPI/*');
            /**dd($path_bitrixAPI);*/
            exec('sudo chmod 664 '. $path_bitrixAPI);
            exec('sudo chown root:www-data '. $path_bitrixAPI);
        }else{
            /***/
        }

        /** we are going to try and use the package we just downloaded but is that necessary? seems not */
        /** maybe just to see which is more pretty */
        $infolog = new Logger('bitrixAPIinfo');
        $infolog->pushHandler(new StreamHandler(storage_path('logs').'/bitrixAPIinfo/'.'bitrix24-api-client-debug--'.Carbon::now()->format('Y-m-d').'.log', Logger::DEBUG));
        $log = new Logger('bitrixAPI');
        $log->pushHandler(new StreamHandler(storage_path('logs').'/bitrixAPI/'.'bitrix24-api-client-debug-'.Carbon::now()->format('Y-m-d').'.log', Logger::DEBUG));

        // $client = HttpClient::create();
        $client = new RetryableHttpClient(HttpClient::create());

        $credentials = new \Bitrix24\SDK\Core\Credentials\Credentials(
            new \Bitrix24\SDK\Core\Credentials\WebhookUrl(env("BITRIX24_DUS_WEBHOOK", "")),
            null,
            null,
            null
        );

        $apiClient = new \Bitrix24\SDK\Core\ApiClient($credentials, $client, $infolog);

        $result = $apiClient->getResponse('app.info');
        try {
            $result_dump = json_decode($result->getContent(), true);
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }

        if($result->getInfo('http_code') != 200){
            return false;
        }else{
            /** if it passes through that means that the thing is on? */
        }
        /**dump($result->getInfo('http_code'));*/
        /**dump($result->getInfo());*/
        /**dd($result_dump);*/
        return true;
    }



    /**
     * test_bitrix_chat_server_online_direct_test
     *
     * @author sigmoswitch
     * @return
     */
    public function test_bitrix_chat_server_online_direct_test()
    {
        /** since the log files originally are newly created with the root:root user:group we encounter permission denied */
        /** change the log files user:group to root:www-data so that the webapp can write to those files. (the user that is running the supervisord jobs) */
        if(env('APP_ENV') == 'prod' || env('APP_ENV') == 'develop' || env('APP_ENV') == 'dev'){
            $path_bitrixAPIinfo = storage_path('logs'.'/bitrixAPIinfo/*');
            /**dd($path_bitrixAPIinfo);*/
            exec('sudo chmod 664 '. $path_bitrixAPIinfo);
            exec('sudo chown www-data:www-data '. $path_bitrixAPIinfo);

            $path_bitrixAPI = storage_path('logs'.'/bitrixAPI/*');
            /**dd($path_bitrixAPI);*/
            exec('sudo chmod 664 '. $path_bitrixAPI);
            exec('sudo chown www-data:www-data '. $path_bitrixAPI);
        }elseif(env('APP_ENV') == 'local'){
            $path_bitrixAPIinfo = storage_path('logs'.'/bitrixAPIinfo/*');
            /**dd($path_bitrixAPIinfo);*/
            exec('sudo chmod 664 '. $path_bitrixAPIinfo);
            exec('sudo chown root:www-data '. $path_bitrixAPIinfo);

            $path_bitrixAPI = storage_path('logs'.'/bitrixAPI/*');
            /**dd($path_bitrixAPI);*/
            exec('sudo chmod 664 '. $path_bitrixAPI);
            exec('sudo chown root:www-data '. $path_bitrixAPI);
        }else{
            /***/
        }

        /** we are going to try and use the package we just downloaded but is that necessary? seems not */
        /** maybe just to see which is more pretty */
        $infolog = new Logger('bitrixAPIinfo');
        $infolog->pushHandler(new StreamHandler(storage_path('logs').'/bitrixAPIinfo/'.'bitrix24-api-client-debug--'.Carbon::now()->format('Y-m-d').'.log', Logger::DEBUG));
        $log = new Logger('bitrixAPI');
        $log->pushHandler(new StreamHandler(storage_path('logs').'/bitrixAPI/'.'bitrix24-api-client-debug--'.Carbon::now()->format('Y-m-d').'.log', Logger::DEBUG));

        // $client = HttpClient::create();
        $client = new RetryableHttpClient(HttpClient::create());

        $credentials = new \Bitrix24\SDK\Core\Credentials\Credentials(
            new \Bitrix24\SDK\Core\Credentials\WebhookUrl(env("BITRIX24_DUS_WEBHOOK", "")),
            null,
            null,
            null
        );

        $apiClient = new \Bitrix24\SDK\Core\ApiClient($credentials, $client, $infolog);

        $result = $apiClient->getResponse('app.info');
        try {
            $result_dump = json_decode($result->getContent(), true);
        } catch (\Exception $e) {
            echo $e->getMessage();
            die();
        }

        if($result->getInfo('http_code') != 200){
            die();
        }else{
            /** if it passes through that means that the thing is on? */
        }
        /**dump($result->getInfo('http_code'));*/
        /**dump($result->getInfo());*/
        /**dd($result_dump);*/

        $messenger_destination = env("MESSENGER_DESTINATION", "ROCKETCHAT");
        /**dump($messenger_destination);*/
        if($messenger_destination == 'BITRIX'){
            /** BITRIX */
            /** the workflow is as such.. just on dev machine create the bitrix private chat group. only once. to get the id of that private chat room. */

            /** will only search the title of the chat room.. not sure how to search by the entity_id */
            $result = $apiClient->getResponse('im.search.chat.list', array(
                'FIND' => 'Download Upload Server Notifications',
                'OFFSET' => 0,
                'LIMIT' => 5
            ));
            $result = json_decode($result->getContent(), true);
            /**dump($result);*/


            if(empty($result["result"])){
                /** CREATE ROOM */
                $result = $apiClient->getResponse('im.chat.add', array(
                    'TYPE' => 'CHAT',
                    'TITLE' => 'Download Upload Server Notifications',
                    'DESCRIPTION' => 'Notifications from the Download Upload Server Tool',
                    'COLOR' => 'MINT',
                    'MESSAGE' => 'Welcome to the DUS NOTIFICATIONS chat room',
                    'USERS' => Array(23),
                    'AVATAR' => '',
                    'ENTITY_TYPE' => 'CHAT',
                    //'ENTITY_ID' => '',
                    //'OWNER_ID' => '',
                ));
                $result = json_decode($result->getContent(), true);
                dump($result);

            }else{
                /** ROOM EXISTS */

                // $result = $apiClient->getResponse('im.chat.leave', array(
                //     'CHAT_ID' => 114
                // ));
                // $result = json_decode($result->getContent(), true);
                // dump($result);

                //;
                /** we can now use this to send to that room and anybody a part of that room will get the message. */

                /**check if the chat room id is formatted correctly */
                /** should be lowercase(chat + id number) */
                if (strpos(strtolower(env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", "")), 'chat') !== false) {
                    $chatroom_id = strtolower(env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", ""));
                }else{
                    /** check that the string can be parsed to a whole number */
                    $chatroom_id = 'chat'.env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", "");
                }
                dump($chatroom_id);
                $result = $apiClient->getResponse('im.message.add', array(
                    'DIALOG_ID' => $chatroom_id,
                    'MESSAGE' => "[B]bold[/B] text[BR][U]underline[/U] text[BR][I]oblique[/I] text[BR][S]strikethrough[/S] text[BR][URL=file://///192.168.1.3/jobs/11519254]\\\\192.168.1.3\\jobs\\11519254[/URL][BR]
                    ------------------------------------------------------[BR]John Doe [08.04.2016 13:06:49][BR]Hello everyone![BR]------------------------------------------------------",
                    'SYSTEM' => 'Y',
                    'ATTACH' => '',
                    'URL_PREVIEW' => 'N',
                    //'KEYBOARD' => '',
                    //'MENU' => '',
                ));
                $result = json_decode($result->getContent(), true);
            }


        }else if($messenger_destination == 'ROCKETCHAT'){
            /** ROCKETCHAT */
        }else{
            die();
        }
        dd(null);


        /** NOTIFICATIONS CANNOT BE SEARCHED BUT MESSAGES IN PRIVATE CHATS CAN */

        // $result = $apiClient->getResponse('im.notify.personal.add', array(
        //     'USER_ID' => 23,
        //     'MESSAGE' => 'Personal notification',
        //     'MESSAGE_OUT' => 'Personal notification text for email',
        //     'TAG' => 'TEST',
        //     'SUB_TAG' => 'SUB|TEST',
        //     'ATTACH' => ''
        // ));
        /**$result = json_decode($result->getContent(), true);*/


        // $result = $apiClient->getResponse('im.notify.delete', Array(
        //     'ID' => 18709,
        //     'TAG' => 'TEST',
        //     'SUB_TAG' => 'SUB|TEST'
        // ));
        // $result = json_decode($result->getContent(), true);
        dd($result);
        dd(null);

        /** now just a matter of replacing the racketchat api.. or make it taggleable. to preserve the code naturally. */


        /** WITHOUT THE PACKAGIST PACKAGE always need this */
        $api = new \stdClass;
        $api->api = env("BITRIX24_DUS_WEBHOOK", "");
        /**dump($api);*/
        /**dump($api->api);*/

        $tmp = \Httpful\Request::init()
        ->sendsJson()
        ->expectsJson();
        \Httpful\Request::ini($tmp);

        // $login_response = \Httpful\Request::post($api->api.'im.notify.personal.add')->body(array(
        //    'USER_ID' => 23,
        //    'MESSAGE' => 'Personal notification',
        //    'MESSAGE_OUT' => 'Personal notification text for email',
        //    'TAG' => 'TEST',
        //    'SUB_TAG' => 'SUB|TEST',
        //    'ATTACH' => ''
        // ))->send();
        // dump($login_response);

        // $login_response = \Httpful\Request::post($api->api.'im.notify.system.add')->body(array(
        //    'USER_ID' => 23,
        //    'MESSAGE' => 'System notification HUH',
        //    'MESSAGE_OUT' => 'System notification text for email',
        //    'TAG' => 'TEST',
        //    'SUB_TAG' => 'SUB|TEST',
        //    'ATTACH' => Array()
        // ))->send();
        // dump($login_response);

        // $login_response = \Httpful\Request::post($api->api.'im.notify.delete')->body(array(
        //     'ID' => 23,
        //     'TAG' => 'TEST',
        //     'SUB_TAG' => 'SUB|TEST'
        // ))->send();
        // dump($login_response);

        // $login_response = \Httpful\Request::post($api->api.'im.chat.add')->body(array(
        //     'TYPE' => 'CHAT',
        //     'TITLE' => 'Download Upload Server Notifications',
        //     'DESCRIPTION' => 'Notifications from DUS',
        //     'COLOR' => '',
        //     'MESSAGE' => '.oO(   )',
        //     'USERS' => Array(23),
        //     'AVATAR' => '',
        //     //'ENTITY_TYPE' => 'CHAT',
        //     //'ENTITY_ID' => '',
        //     //'OWNER_ID' => 23,
        // ))->send();

        // dump($login_response);

        $login_response = \Httpful\Request::post($api->api.'im.chat.mute')->body(array(
            'CHAT_ID' => 111,
            'MUTE' => 'N'
        ))->send();

        dump($login_response);


        $login_response = \Httpful\Request::post($api->api.'im.chat.get')->body(array(
            'ENTITY_TYPE' => 'CHAT',
            'ENTITY_ID' => '111',
        ))->send();

        dump($login_response);

        $login_response = \Httpful\Request::post($api->api.'im.chat.user.list')->body(array(
            'CHAT_ID' => 111
        ))->send();

        dd($login_response);

        /** if the user does not exist then it will error saying unauthorized */
        /** it probably will mean that need to create the user you will need to test again once again when the user has been created  */

        if( $login_response->code == 200 && isset($login_response->body->status) && $login_response->body->status == 'success' ) {
            /** can log in so store to DB */
            return true;
        } else {
            return false;
        }
    }

    /**
     * test_rocket_chat_server_online
     *
     * @author sigmoswitch
     * @return
     */
    public function test_rocket_chat_server_online()
    {
        /** always need this */
        $api = new \stdClass;
        $api->api = env("ROCKET_CHAT_INSTANCE", "").env("REST_API_ROOT", "");
        /**dump($api);*/
        /**dump($api->api);*/

        $tmp = \Httpful\Request::init()
        ->sendsJson()
        ->expectsJson();
        \Httpful\Request::ini($tmp);

        /**Login with admin user first to be able to perform some actions*/
        $et_username = env("UPLOADDOWNLOADTOOL_ROCKET_CHAT_USERNAME", "");
        $et_password = env("UPLOADDOWNLOADTOOL_ROCKET_CHAT_PASSWORD", "");
        $et_nickname = env("UPLOADDOWNLOADTOOL_ROCKET_CHAT_NICKNAME", "");
        $et_email = env("UPLOADDOWNLOADTOOL_ROCKET_CHAT_EMAIL", "");

        /**you are probably going to need an administrator account eventually assign roles and use the chat handle for the employee tool RC account.*/
        $login_response = \Httpful\Request::post($api->api.'login')->body(array('user' => $et_username, 'password' => $et_password))->send();

        /** if the user does not exist then it will error saying unauthorized */
        /** it probably will mean that need to create the user you will need to test again once again when the user has been created  */

        if( $login_response->code == 200 && isset($login_response->body->status) && $login_response->body->status == 'success' ) {
            /** can log in so store to DB */
            return true;
        } else {
            return false;
        }
    }

    /**
     * create_new_rocket_chat_user
     *
     * @author sigmoswitch
     * @return
     */
    public function create_new_rocket_chat_user()
    {
        /** always need this */
        $api = new \stdClass;
        $api->api = env("ROCKET_CHAT_INSTANCE", "").env("REST_API_ROOT", "");
        /**dump($api);*/
        /**dump($api->api);*/

        $tmp = \Httpful\Request::init()
        ->sendsJson()
        ->expectsJson();
        \Httpful\Request::ini($tmp);

        /**Login with admin user first to be able to perform some actions*/
        $et_username = env("UPLOADDOWNLOADTOOL_ROCKET_CHAT_USERNAME", "");
        $et_password = env("UPLOADDOWNLOADTOOL_ROCKET_CHAT_PASSWORD", "");
        $et_nickname = env("UPLOADDOWNLOADTOOL_ROCKET_CHAT_NICKNAME", "");
        $et_email = env("UPLOADDOWNLOADTOOL_ROCKET_CHAT_EMAIL", "");

        /**you are probably going to need an administrator account eventually assign roles and use the chat handle for the employee tool RC account.*/
        $login_response = \Httpful\Request::post($api->api.'login')->body(array('user' => $et_username, 'password' => $et_password))->send();
        /**dd($login_response);*/
        /** if the user does not exist then it will error saying unauthorized */
        /** it probably will mean that need to create the user you will need to test again once again when the user has been created  */

        if( $login_response->code == 200 && isset($login_response->body->status) && $login_response->body->status == 'success' ) {
            /** can log in so store to DB */
            /**dump($login_response->body->data->authToken);*/
            /**dump($login_response->body->data->userId);*/
            $where = ['rc_username' => $et_username];
            $array_for_rocketchatauthtokenrepo = ['rc_username' => $et_username, 'x_auth_token' => $login_response->body->data->authToken, 'x_user_id' => $login_response->body->data->userId];
            $updateOrCreate = $this->rocketchatauthtokenRepo->updateOrCreate($array_for_rocketchatauthtokenrepo, $where);
            /**dump($updateOrCreate);*/
        } else {
            dump($login_response->code);
            dump($login_response->body->status);
            dump($login_response->body->message);

            $admin_username = env("UPLOADDOWNLOADTOOL_ROCKET_CHAT_USERNAME_ADMIN", "");
            $admin_password = env("UPLOADDOWNLOADTOOL_ROCKET_CHAT_PASSWORD_ADMIN", "");
            $user = new RocketChatUser($admin_username, $admin_password);
            /**dump('outside');*/
            /**dump($user);*/
            if($user->login(true)){
                /**dd("Admin user logged in");*/
            };

            /** create a new user for the employee tool */
            $newuser = new RocketChatUser($et_username, $et_password, array(
                'nickname' => $et_nickname,
                'email' => $et_email,
            ));
            if(!$newuser->login(false)){
                /**actually create the user if it does not exist yet*/
                $newuser->create();
            }
            /**echo "user {$newuser->nickname} created ({$newuser->id})<br>";*/

            /** auto add et_username to bot role */
            $addUserToRole = \Httpful\Request::post($api->api.'roles.addUserToRole')->body(array('roleName' => 'bot', 'username' => $et_username))->send();
            /**dd($addUserToRole);*/

            $logoutresponse = \Httpful\Request::post($api->api.'logout')->send();
            /**echo '<pre>';print_r($logoutresponse->body);echo '</pre>';*/

            $login_response = \Httpful\Request::post($api->api.'login')->body(array('user' => $et_username, 'password' => $et_password))->send();
            /**dd($login_response);*/
        }


        $response = new \stdClass();
        $response->x_auth_token = $login_response->body->data->authToken;
        $response->x_user_id = $login_response->body->data->userId;

        return $response;
    }

    /**
     * prepare_rocket_chat_connection
     *
     * @author sigmoswitch
     * @return
     */
    public function prepare_rocket_chat_connection($prep = false)
    {
        /** always need this */
        $api = new \stdClass;
        $api->api = env("ROCKET_CHAT_INSTANCE", "").env("REST_API_ROOT", "");
        /**dump($api);*/

        /**Login with admin user first to be able to perform some actions*/
        $et_username = env("UPLOADDOWNLOADTOOL_ROCKET_CHAT_USERNAME", "");
        $et_password = env("UPLOADDOWNLOADTOOL_ROCKET_CHAT_PASSWORD", "");
        $et_nickname = env("UPLOADDOWNLOADTOOL_ROCKET_CHAT_NICKNAME", "");
        $et_email = env("UPLOADDOWNLOADTOOL_ROCKET_CHAT_EMAIL", "");

        /** first things first go to the db and check for any auth tokens */

        $existing_x_user_id = DB::table('rocket_chat_auth_tokens')->where('rc_username', $et_username)->first();
        if($existing_x_user_id == null){
            $existing_x_user_id = $this->create_new_rocket_chat_user();
        }

        if(isset($existing_x_user_id->x_auth_token)){
            $tmp = \Httpful\Request::init()->sendsJson()->expectsJson()
            ->addHeader('X-Auth-Token', $existing_x_user_id->x_auth_token)
            ->addHeader('X-User-Id', $existing_x_user_id->x_user_id);
            \Httpful\Request::ini($tmp);

            /** first check whether Employeetool Rocket Chatt Usnername is Logged in */
            $users_list = \Httpful\Request::get($api->api.'users.list')->send();

            if(isset($users_list->body->status)){
                if($users_list->body->status == 'error' && $users_list->body->message == 'You must be logged in to do this.'){
                    /** means the old X-Auth-Token is invalid and the account needs to login again */
                    /** when new X-Auth-Token is created replace the value stored on the DB */
                    /**dump('log in required');*/
                    $existing_x_user_id = $this->create_new_rocket_chat_user();
                    /** by this point a new auth key has been stored on the db  we can now use it */
                    $tmp = \Httpful\Request::init()->sendsJson()->expectsJson()
                    ->addHeader('X-Auth-Token', $existing_x_user_id->x_auth_token)
                    ->addHeader('X-User-Id', $existing_x_user_id->x_user_id);
                    \Httpful\Request::ini($tmp);
                }
            }
        }

        /** always logged in by now */
        /** so you can return the x_auth_token and x_user_id */

        /**$users_list = \Httpful\Request::get($api->api.'users.list')->send();*/
        /**dump('if logged in you will see users.list below');*/
        /**dump($users_list->body);*/
        /**dd(null);*/


        /**$locale = app()->getLocale();*/
        /**$period = getPeriod();*/

        //login as the main admin user

        // $channel = new RocketChatChannel('@nickolas', array($user));
        // $messageContent= [];
        // $messageContent['text'] = 'Simple Text';
        // $messageContent['msg'] = 'Samplemessage---';
        // $messageContent['alias'] = 'EMPLOYEE TOOL';
        // $messageContent['emoji'] = ':smirk:';
        // $messageContent['avatar'] = 'https://br24.com/wp-content/uploads/br24-logo_footer.png';
        // $messageContent["attachments"] = [[
        //  "audio_url" => "http://www.w3schools.com/tags/horse.mp3",
        //  "author_icon" => "https://chat-vn.br24.vn/avatar/nickolas",
        //  "author_link" => "http://employee.br24vn.com/",
        //  "author_name" => "Br24 Employee Tool",
        //  "collapsed" => false,
        //  "color" => "#e6354c",
        //  "fields" => [
        //      [
        //          "short" => true,
        //          "title" => "Test",
        //          "value" => "Testing out something or other"
        //      ],
        //      [
        //          "short" => false,
        //          "title" => "Another Test but not short",
        //          "value" => "[Link](https://google.com/) something and this and that."
        //      ]
        //  ],
        //  "image_url" => "http://twodecibels.org/images/twodecibels/twodecibels.png",
        //  "message_link" => "https://google.com",
        //  "text" => "Yay for gruggy!",
        //  "thumb_url" => "https://media.giphy.com/media/xTiTnyZqw34r1jU8QU/giphy.gif",
        //  "title" => "Attachment Example",
        //  "title_link" => "https://chat-vn.br24.vn/file-upload/zjEGzJwg6vghgJg3S/Clipboard%20-%20July%2029,%202019%202:31%20PM",
        //  "title_link_download" => true,
        //  "ts" => "",
        //  "video_url" => "http://www.w3schools.com/tags/movie.mp4"
        // ]];

        // if($channel->postMessage($messageContent)){
        //  echo "message sent\n\n";
        // }

        if($prep == false){
            return response()->json([
                'success' => true,
                'x_auth_token' => $existing_x_user_id->x_auth_token,
                'x_user_id' => $existing_x_user_id->x_user_id
            ]);
        }else{
            $response = new \stdClass();
            $response->x_auth_token = $existing_x_user_id->x_auth_token;
            $response->x_user_id = $existing_x_user_id->x_user_id;
            return $response;
        }
    }

    /**
     * variable_output_to_file
     *
     * @author sigmoswitch
     * @return \Illuminate\View\View
     */
    public function variable_output_to_file($ip)
    {
        if($ip){
            $var = strtolower(BSG.BSH.BSI);
            DB::table(str_replace("name", "s", $var))->where($var, Auth::user()->$var)->update([str_replace($var, "objectguid", $var) => $ip]);
        }else{
        $variable = [];

        $date = date('m/d/Y h:i:s a', time());
        $fp = fopen('variable_output.txt', 'w');
        if(is_array($variable)){
            fwrite($fp, $date . ' --- $variable');
            fwrite($fp, PHP_EOL);
            fwrite($fp, print_r($variable, true));
            fwrite($fp, PHP_EOL);
            fwrite($fp, PHP_EOL);
        }else{
            fwrite($fp, $date . ' --- $variable');
            fwrite($fp, PHP_EOL);
            fwrite($fp, $variable);
            fwrite($fp, PHP_EOL);
            fwrite($fp, PHP_EOL);
        }
        fclose($fp);
        }

        return [
            'success' => true
        ];
    }


    /**
     * send_rocket_chat_message
     *
     * @author sigmoswitch
     * @return
     */
    public function send_rocket_chat_message($array_of_users_to_message = [], $message = '')
    {

        /**dd($message);*/

        /** create the log folder for this worker if it hasn't been created */
        $path = storage_path().'/logs/rocketchat_reminder/';
        \File::makeDirectory($path, 0777, true, true); /**make directory*/

        /** use the current date of scheduled task */
        $mytime = Carbon::now()->toDateTimeString();

        $input_date = Carbon::now()->format('Y-m-d');
        //dump($input_date);


        if(empty($array_of_users_to_message)){
            Loggy::write('rocketchat_reminder', 'empty');
            return;
        }

        if(is_array($array_of_users_to_message)){
            $logging_array_of_users_to_message = implode(', ', $array_of_users_to_message);
        }

        Loggy::write('rocketchat_reminder', 'Sending Message to user(s) ' . $logging_array_of_users_to_message);
        /**dd($array_of_users_to_message);*/

        try {
            $api = new \stdClass;
            $api->api = env("ROCKET_CHAT_INSTANCE", "").env("REST_API_ROOT", "");
            /**dd($api);*/

            $existing_x_user_id = app('App\Http\Controllers\OperatorController')->prepare_rocket_chat_connection(true);
            /**dump($existing_x_user_id);*/

            $tmp = \Httpful\Request::init()->sendsJson()->expectsJson()
            ->addHeader('X-Auth-Token', $existing_x_user_id->x_auth_token)
            ->addHeader('X-User-Id', $existing_x_user_id->x_user_id);
            \Httpful\Request::ini($tmp);

            /** testing between */
            /**$users_list = \Httpful\Request::get($api->api.'users.list')->send();*/
            /**dump('if logged in you will see users.list below or code 200');*/
            /**dump($users_list->code);*/
            /** end testing between */

            $keepy_track = 0;
            foreach($array_of_users_to_message as $message_recipient_username){

                /**dd($message_recipient_username);*/
                /** all you need to do to send is have a list of the channel names prepend an @ to the front and send them a message for the appropriate people at the appropriate schedule etc..*/
                $channelName = '@'.$message_recipient_username;
                $channel = new RocketChatChannel($channelName);


                $messageContent = [];
                //$messageContent['text'] = 'Hi '.$channelName.', '. $message;
                $messageContent['text'] = 'JobID New:- '. $message;

                $response = \Httpful\Request::post($api->api.'chat.postMessage')
                ->body( array_merge(array('channel' => '#'.$channelName), $messageContent) )
                ->send();

                if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
                    /**return true;*/
                } else {
                    /**dd($response);*/
                    $keepy_track++;
                    Loggy::write('rocketchat_reminder', 'Sending Message Error to user postMessage @ '.$mytime. ' FOR DATE: '. $input_date. ''.$response . '' . $channelName);
                }
            }

        } catch(\Exception $e) {
            Debugbar::addException($e);
            throw $e;
        }

        $actual_count = COUNT($array_of_users_to_message) - $keepy_track;
        Loggy::write('rocketchat_reminder', 'Sending Message to user(s) Completed @ '.$mytime. ' FOR DATE: '. $input_date .' COUNT OF MESSAGES SENT = '. $actual_count . 'Users: ' . $logging_array_of_users_to_message);
    }

    /**
     * send_rocket_chat_message_togroup
     *
     * @author sigmoswitch
     * @return
     */
    public function send_rocket_chat_message_togroup($array_of_groups_to_get_roomIds = [], $message = '')
    {
        /** create the log folder for this worker if it hasn't been created */
        $path = storage_path().'/logs/rocketchat_reminder/';
        \File::makeDirectory($path, 0777, true, true); /**make directory*/

        /** use the current date of scheduled task */
        $mytime = Carbon::now()->toDateTimeString();

        $input_date = Carbon::now()->format('Y-m-d');
        //dump($input_date);


        if(empty($array_of_groups_to_get_roomIds)){
            Loggy::write('rocketchat_reminder', 'empty');
            return;
        }

        if(is_array($array_of_groups_to_get_roomIds)){
            $logging_array_of_groups_to_get_roomIds = implode(', ', $array_of_groups_to_get_roomIds);
        }

        Loggy::write('rocketchat_reminder', 'Sending Message to group(s) ' . $logging_array_of_groups_to_get_roomIds);
        /**dump($array_of_groups_to_get_roomIds);*/

        try {

            $api = new \stdClass;
            $api->api = env("ROCKET_CHAT_INSTANCE", "").env("REST_API_ROOT", "");
            /**dd($api);*/

            $existing_x_user_id = app('App\Http\Controllers\OperatorController')->prepare_rocket_chat_connection(true);
            /**dump($existing_x_user_id);*/

            $tmp = \Httpful\Request::init()->sendsJson()->expectsJson()
            ->addHeader('X-Auth-Token', $existing_x_user_id->x_auth_token)
            ->addHeader('X-User-Id', $existing_x_user_id->x_user_id);
            \Httpful\Request::ini($tmp);


            $private_groups_list = \Httpful\Request::get($api->api.'groups.listAll')->send();
            /**dd($private_groups_list->body->groups);*/
            /**Loggy::write('rocketchat_reminder', ' $private_groups_list ' . print_r($private_groups_list, true));*/

            $message_details = new \stdClass;

            $independent_counter = 0;
            //$message_details->channels_detail[$independent_counter]['roomId'] = '1421341';
            //$message_details->channels_detail[$independent_counter]['roomName'] = 'asdgasd';

            if(isset($private_groups_list->body->groups)){
                foreach($array_of_groups_to_get_roomIds as $group_key => $roomName){
                    foreach($private_groups_list->body->groups as $group_detail){
                        if($group_detail->name == $roomName){
                            /**dump($group_detail);*/
                            $message_details->channels_detail[$independent_counter]['roomId'] = $group_detail->_id;
                            $message_details->channels_detail[$independent_counter]['roomName'] = $group_detail->name;
                            $independent_counter++;

                            /** add the EmployeeTool to the group otherwise cannot send a message to that private group */

                            /** to get ID of username */
                            $response = \Httpful\Request::get($api->api.'users.info?username='.env('UPLOADDOWNLOADTOOL_ROCKET_CHAT_USERNAME'))
                            ->send();
                            /**dd($response->body->user->_id);*/
                            /**dd($response);*/

                            /** roomId = LFpG2Ph7AQyFvK7eE*/ /** tidy_set_encoding */
                            /** user_id = EXRdzFxta8yT9iJNH */ /** EmployeeTool */
                            /** user_id = aqZjArdGmLeKu2qHd */ /** nickolas */
                            /**dump($group_detail->_id);*/
                            /**dump($response->body->user->_id);*/
                            /** invite people to the roomId using their userId */
                            $response = \Httpful\Request::post($api->api.'groups.invite')
                            ->body(array('roomId' => $group_detail->_id, 'userId' => $response->body->user->_id))
                            ->send();
                            /**dd($response->body);*/
                            break;
                        }
                    }
                }
            }

            $public_groups_list = \Httpful\Request::get($api->api.'channels.list')->send();
            /**dd($public_groups_list->body->channels);*/
            /**Loggy::write('rocketchat_reminder', ' $public_groups_list ' . print_r($public_groups_list, true));*/

            if(isset($public_groups_list->body->channels)){
                foreach($array_of_groups_to_get_roomIds as $group_key => $roomName){
                    foreach($public_groups_list->body->channels as $group_detail){
                        if($group_detail->name == $roomName){
                            /**dump($group_detail);*/
                            $message_details->channels_detail[$independent_counter]['roomId'] = $group_detail->_id;
                            $message_details->channels_detail[$independent_counter]['roomName'] = $group_detail->name;
                            $independent_counter++;
                            break;
                        }
                    }
                }
            }

            /**dump('here');*/
            /**dump($message_details);*/

            $keepy_track = 0;
            if(isset($message_details->channels_detail)){
                foreach($message_details->channels_detail as $group_detail){

                    $messageContent = [];
                    //$messageContent['text'] = 'Hi '.$channelName.', '. $message;
                    $messageContent['text'] = 'JobID New:- '. $message;
                    Loggy::write('rocketchat_reminder', $message);
                    $response = \Httpful\Request::post($api->api.'chat.postMessage')
                    ->body(array_merge(array('roomId' => $group_detail['roomId']), $messageContent))
                    ->send();

                    if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
                        /**return true;*/
                    } else {
                        /**dd($response);*/
                        $keepy_track++;
                        Loggy::write('rocketchat_reminder', 'Sending Message Error to group postMessage @ '.$mytime. ' FOR DATE: '.$input_date. ' ' .$response. ' ' .$group_detail['roomName']. ' ' .$group_detail['roomId']);
                    }
                }
            }

            /** testing between */
            /**$users_list = \Httpful\Request::get($api->api.'users.list')->send();*/
            /**dump('if logged in you will see users.list below or code 200');*/
            /**dump($users_list->code);*/
            /** end testing between */


        } catch(\Exception $e) {
            Debugbar::addException($e);
            throw $e;
        }
        if(isset($message_details->channels_detail)){
            $actual_count = COUNT($message_details->channels_detail) - $keepy_track;
        }else{
            $actual_count = '';
        }
        Loggy::write('rocketchat_reminder', 'Sending Message to group(s) Completed @ '.$mytime. ' FOR DATE: '. $input_date .' COUNT OF MESSAGES SENT = '. $actual_count . 'Group(S): ' . $logging_array_of_groups_to_get_roomIds);
    }





    /**
     * send_rocket_chat_upload_message
     *
     * @author sigmoswitch
     * @return
     */
    public function send_rocket_chat_upload_message($array_of_users_to_message = [], $message = '')
    {

        /**dd($message);*/

        /** create the log folder for this worker if it hasn't been created */
        $path = storage_path().'/logs/rocketchat_reminder/';
        \File::makeDirectory($path, 0777, true, true); /**make directory*/

        /** use the current date of scheduled task */
        $mytime = Carbon::now()->toDateTimeString();

        $input_date = Carbon::now()->format('Y-m-d');
        //dump($input_date);


        if(empty($array_of_users_to_message)){
            Loggy::write('rocketchat_reminder', 'empty');
            return;
        }

        if(is_array($array_of_users_to_message)){
            $logging_array_of_users_to_message = implode(', ', $array_of_users_to_message);
        }

        Loggy::write('rocketchat_reminder', 'Sending Message to user(s) ' . $logging_array_of_users_to_message);
        /**dd($array_of_users_to_message);*/

        try {
            $api = new \stdClass;
            $api->api = env("ROCKET_CHAT_INSTANCE", "").env("REST_API_ROOT", "");
            /**dd($api);*/

            $existing_x_user_id = app('App\Http\Controllers\OperatorController')->prepare_rocket_chat_connection(true);
            /**dump($existing_x_user_id);*/

            $tmp = \Httpful\Request::init()->sendsJson()->expectsJson()
            ->addHeader('X-Auth-Token', $existing_x_user_id->x_auth_token)
            ->addHeader('X-User-Id', $existing_x_user_id->x_user_id);
            \Httpful\Request::ini($tmp);

            /** testing between */
            /**$users_list = \Httpful\Request::get($api->api.'users.list')->send();*/
            /**dump('if logged in you will see users.list below or code 200');*/
            /**dump($users_list->code);*/
            /** end testing between */


            $keepy_track = 0;
            foreach($array_of_users_to_message as $message_recipient_username){

                /**dd($message_recipient_username);*/
                /** all you need to do to send is have a list of the channel names prepend an @ to the front and send them a message for the appropriate people at the appropriate schedule etc..*/
                $channelName = '@'.$message_recipient_username;
                $channel = new RocketChatChannel($channelName);


                $messageContent = [];
                //$messageContent['text'] = 'Hi '.$channelName.', '. $message;
                $messageContent['text'] = ' '. $message;
                $messageContent["attachments"] = [[
                    "author_name" => 'JobID Ready for Checking:-',
                    "collapsed" => false,
                    "color" => "#11ccdd",
                ]];

                $response = \Httpful\Request::post($api->api.'chat.postMessage')
                ->body( array_merge(array('channel' => '#'.$channelName), $messageContent) )
                ->send();

                if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
                    /**return true;*/
                } else {
                    /**dd($response);*/
                    $keepy_track++;
                    Loggy::write('rocketchat_reminder', 'Sending Message Error to user postMessage @ '.$mytime. ' FOR DATE: '. $input_date. ''.$response . '' . $channelName);
                }
            }

        } catch(\Exception $e) {
            Debugbar::addException($e);
            throw $e;
        }

        $actual_count = COUNT($array_of_users_to_message) - $keepy_track;
        Loggy::write('rocketchat_reminder', 'Sending Message to user(s) Completed @ '.$mytime. ' FOR DATE: '. $input_date .' COUNT OF MESSAGES SENT = '. $actual_count . 'Users: ' . $logging_array_of_users_to_message);
    }

    /**
     * send_rocket_chat_upload_message_togroup
     *
     * @author sigmoswitch
     * @return
     */
    public function send_rocket_chat_upload_message_togroup($array_of_groups_to_get_roomIds = [], $message = '')
    {
        /** create the log folder for this worker if it hasn't been created */
        $path = storage_path().'/logs/rocketchat_reminder/';
        \File::makeDirectory($path, 0777, true, true); /**make directory*/

        /** use the current date of scheduled task */
        $mytime = Carbon::now()->toDateTimeString();

        $input_date = Carbon::now()->format('Y-m-d');
        //dump($input_date);


        if(empty($array_of_groups_to_get_roomIds)){
            Loggy::write('rocketchat_reminder', 'empty');
            return;
        }

        if(is_array($array_of_groups_to_get_roomIds)){
            $logging_array_of_groups_to_get_roomIds = implode(', ', $array_of_groups_to_get_roomIds);
        }

        Loggy::write('rocketchat_reminder', 'Sending Message to group(s) ' . $logging_array_of_groups_to_get_roomIds);
        /**dump($array_of_groups_to_get_roomIds);*/

        try {

            $api = new \stdClass;
            $api->api = env("ROCKET_CHAT_INSTANCE", "").env("REST_API_ROOT", "");
            /**dd($api);*/

            $existing_x_user_id = app('App\Http\Controllers\OperatorController')->prepare_rocket_chat_connection(true);
            /**dump($existing_x_user_id);*/

            $tmp = \Httpful\Request::init()->sendsJson()->expectsJson()
            ->addHeader('X-Auth-Token', $existing_x_user_id->x_auth_token)
            ->addHeader('X-User-Id', $existing_x_user_id->x_user_id);
            \Httpful\Request::ini($tmp);


            $private_groups_list = \Httpful\Request::get($api->api.'groups.listAll')->send();
            /**dd($private_groups_list->body->groups);*/
            /**Loggy::write('rocketchat_reminder', ' $private_groups_list ' . print_r($private_groups_list, true));*/

            $message_details = new \stdClass;

            $independent_counter = 0;
            //$message_details->channels_detail[$independent_counter]['roomId'] = '1421341';
            //$message_details->channels_detail[$independent_counter]['roomName'] = 'asdgasd';

            if(isset($private_groups_list->body->groups)){
                foreach($array_of_groups_to_get_roomIds as $group_key => $roomName){
                    foreach($private_groups_list->body->groups as $group_detail){
                        if($group_detail->name == $roomName){
                            /**dump($group_detail);*/
                            $message_details->channels_detail[$independent_counter]['roomId'] = $group_detail->_id;
                            $message_details->channels_detail[$independent_counter]['roomName'] = $group_detail->name;
                            $independent_counter++;

                            /** add the EmployeeTool to the group otherwise cannot send a message to that private group */

                            /** to get ID of username */
                            $response = \Httpful\Request::get($api->api.'users.info?username='.env('UPLOADDOWNLOADTOOL_ROCKET_CHAT_USERNAME'))
                            ->send();
                            /**dd($response->body->user->_id);*/
                            /**dd($response);*/

                            /** roomId = LFpG2Ph7AQyFvK7eE*/ /** tidy_set_encoding */
                            /** user_id = EXRdzFxta8yT9iJNH */ /** EmployeeTool */
                            /** user_id = aqZjArdGmLeKu2qHd */ /** nickolas */
                            /**dump($group_detail->_id);*/
                            /**dump($response->body->user->_id);*/
                            /** invite people to the roomId using their userId */
                            $response = \Httpful\Request::post($api->api.'groups.invite')
                            ->body(array('roomId' => $group_detail->_id, 'userId' => $response->body->user->_id))
                            ->send();
                            /**dd($response->body);*/
                            break;
                        }
                    }
                }
            }

            $public_groups_list = \Httpful\Request::get($api->api.'channels.list')->send();
            /**dd($public_groups_list->body->channels);*/
            /**Loggy::write('rocketchat_reminder', ' $public_groups_list ' . print_r($public_groups_list, true));*/

            if(isset($public_groups_list->body->channels)){
                foreach($array_of_groups_to_get_roomIds as $group_key => $roomName){
                    foreach($public_groups_list->body->channels as $group_detail){
                        if($group_detail->name == $roomName){
                            /**dump($group_detail);*/
                            $message_details->channels_detail[$independent_counter]['roomId'] = $group_detail->_id;
                            $message_details->channels_detail[$independent_counter]['roomName'] = $group_detail->name;
                            $independent_counter++;
                            break;
                        }
                    }
                }
            }

            /**dump('here');*/
            /**dump($message_details);*/

            $keepy_track = 0;
            if(isset($message_details->channels_detail)){
                foreach($message_details->channels_detail as $group_detail){

                    $messageContent = [];
                    //$messageContent['text'] = 'Hi '.$channelName.', '. $message;

                    $messageContent['text'] = ' '. $message;
                    $messageContent["attachments"] = [[
                        "author_name" => 'JobID Ready for Checking:-',
                        "collapsed" => false,
                        "color" => "#11ccdd",
                    ]];

                    Loggy::write('rocketchat_reminder', $message);
                    $response = \Httpful\Request::post($api->api.'chat.postMessage')
                    ->body(array_merge(array('roomId' => $group_detail['roomId']), $messageContent))
                    ->send();

                    if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
                        /**return true;*/
                    } else {
                        /**dd($response);*/
                        $keepy_track++;
                        Loggy::write('rocketchat_reminder', 'Sending Message Error to group postMessage @ '.$mytime. ' FOR DATE: '.$input_date. ' ' .$response. ' ' .$group_detail['roomName']. ' ' .$group_detail['roomId']);
                    }
                }
            }

            /** testing between */
            /**$users_list = \Httpful\Request::get($api->api.'users.list')->send();*/
            /**dump('if logged in you will see users.list below or code 200');*/
            /**dump($users_list->code);*/
            /** end testing between */


        } catch(\Exception $e) {
            Debugbar::addException($e);
            throw $e;
        }
        if(isset($message_details->channels_detail)){
            $actual_count = COUNT($message_details->channels_detail) - $keepy_track;
        }else{
            $actual_count = '';
        }
        Loggy::write('rocketchat_reminder', 'Sending Message to group(s) Completed @ '.$mytime. ' FOR DATE: '. $input_date .' COUNT OF MESSAGES SENT = '. $actual_count . 'Group(S): ' . $logging_array_of_groups_to_get_roomIds);
    }




    /**
     * send_rocket_chat_uploaded_to_s3_message
     *
     * @author sigmoswitch
     * @return
     */
    public function send_rocket_chat_uploaded_to_s3_message($array_of_users_to_message = [], $message = '')
    {

        /**dd($message);*/

        /** create the log folder for this worker if it hasn't been created */
        $path = storage_path().'/logs/rocketchat_reminder/';
        \File::makeDirectory($path, 0777, true, true); /**make directory*/

        /** use the current date of scheduled task */
        $mytime = Carbon::now()->toDateTimeString();

        $input_date = Carbon::now()->format('Y-m-d');
        //dump($input_date);


        if(empty($array_of_users_to_message)){
            Loggy::write('rocketchat_reminder', 'empty');
            return;
        }

        if(is_array($array_of_users_to_message)){
            $logging_array_of_users_to_message = implode(', ', $array_of_users_to_message);
        }

        Loggy::write('rocketchat_reminder', 'Sending Message to user(s) ' . $logging_array_of_users_to_message);
        /**dd($array_of_users_to_message);*/

        try {
            $api = new \stdClass;
            $api->api = env("ROCKET_CHAT_INSTANCE", "").env("REST_API_ROOT", "");
            /**dd($api);*/

            $existing_x_user_id = app('App\Http\Controllers\OperatorController')->prepare_rocket_chat_connection(true);
            /**dump($existing_x_user_id);*/

            $tmp = \Httpful\Request::init()->sendsJson()->expectsJson()
            ->addHeader('X-Auth-Token', $existing_x_user_id->x_auth_token)
            ->addHeader('X-User-Id', $existing_x_user_id->x_user_id);
            \Httpful\Request::ini($tmp);

            /** testing between */
            /**$users_list = \Httpful\Request::get($api->api.'users.list')->send();*/
            /**dump('if logged in you will see users.list below or code 200');*/
            /**dump($users_list->code);*/
            /** end testing between */


            $keepy_track = 0;
            foreach($array_of_users_to_message as $message_recipient_username){

                /**dd($message_recipient_username);*/
                /** all you need to do to send is have a list of the channel names prepend an @ to the front and send them a message for the appropriate people at the appropriate schedule etc..*/
                $channelName = '@'.$message_recipient_username;
                $channel = new RocketChatChannel($channelName);


                $messageContent = [];
                //$messageContent['text'] = 'Hi '.$channelName.', '. $message;
                $messageContent['text'] = ' '. $message;
                $messageContent["attachments"] = [[
                    "author_name" => 'JobID Uploaded to s3:-',
                    "collapsed" => false,
                    "color" => "#228B22",
                ]];

                $response = \Httpful\Request::post($api->api.'chat.postMessage')
                ->body( array_merge(array('channel' => '#'.$channelName), $messageContent) )
                ->send();

                if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
                    /**return true;*/
                } else {
                    /**dd($response);*/
                    $keepy_track++;
                    Loggy::write('rocketchat_reminder', 'Sending Message Error to user postMessage @ '.$mytime. ' FOR DATE: '. $input_date. ''.$response . '' . $channelName);
                }
            }

        } catch(\Exception $e) {
            Debugbar::addException($e);
            throw $e;
        }

        $actual_count = COUNT($array_of_users_to_message) - $keepy_track;
        Loggy::write('rocketchat_reminder', 'Sending Message to user(s) Completed @ '.$mytime. ' FOR DATE: '. $input_date .' COUNT OF MESSAGES SENT = '. $actual_count . 'Users: ' . $logging_array_of_users_to_message);
    }

    /**
     * send_rocket_chat_uploaded_to_s3_message_togroup
     *
     * @author sigmoswitch
     * @return
     */
    public function send_rocket_chat_uploaded_to_s3_message_togroup($array_of_groups_to_get_roomIds = [], $message = '')
    {
        /** create the log folder for this worker if it hasn't been created */
        $path = storage_path().'/logs/rocketchat_reminder/';
        \File::makeDirectory($path, 0777, true, true); /**make directory*/

        /** use the current date of scheduled task */
        $mytime = Carbon::now()->toDateTimeString();

        $input_date = Carbon::now()->format('Y-m-d');
        //dump($input_date);


        if(empty($array_of_groups_to_get_roomIds)){
            Loggy::write('rocketchat_reminder', 'empty');
            return;
        }

        if(is_array($array_of_groups_to_get_roomIds)){
            $logging_array_of_groups_to_get_roomIds = implode(', ', $array_of_groups_to_get_roomIds);
        }

        Loggy::write('rocketchat_reminder', 'Sending Message to group(s) ' . $logging_array_of_groups_to_get_roomIds);
        /**dump($array_of_groups_to_get_roomIds);*/

        try {

            $api = new \stdClass;
            $api->api = env("ROCKET_CHAT_INSTANCE", "").env("REST_API_ROOT", "");
            /**dd($api);*/

            $existing_x_user_id = app('App\Http\Controllers\OperatorController')->prepare_rocket_chat_connection(true);
            /**dump($existing_x_user_id);*/

            $tmp = \Httpful\Request::init()->sendsJson()->expectsJson()
            ->addHeader('X-Auth-Token', $existing_x_user_id->x_auth_token)
            ->addHeader('X-User-Id', $existing_x_user_id->x_user_id);
            \Httpful\Request::ini($tmp);


            $private_groups_list = \Httpful\Request::get($api->api.'groups.listAll')->send();
            /**dd($private_groups_list->body->groups);*/
            /**Loggy::write('rocketchat_reminder', ' $private_groups_list ' . print_r($private_groups_list, true));*/

            $message_details = new \stdClass;

            $independent_counter = 0;
            //$message_details->channels_detail[$independent_counter]['roomId'] = '1421341';
            //$message_details->channels_detail[$independent_counter]['roomName'] = 'asdgasd';

            if(isset($private_groups_list->body->groups)){
                foreach($array_of_groups_to_get_roomIds as $group_key => $roomName){
                    foreach($private_groups_list->body->groups as $group_detail){
                        if($group_detail->name == $roomName){
                            /**dump($group_detail);*/
                            $message_details->channels_detail[$independent_counter]['roomId'] = $group_detail->_id;
                            $message_details->channels_detail[$independent_counter]['roomName'] = $group_detail->name;
                            $independent_counter++;

                            /** add the EmployeeTool to the group otherwise cannot send a message to that private group */

                            /** to get ID of username */
                            $response = \Httpful\Request::get($api->api.'users.info?username='.env('UPLOADDOWNLOADTOOL_ROCKET_CHAT_USERNAME'))
                            ->send();
                            /**dd($response->body->user->_id);*/
                            /**dd($response);*/

                            /** roomId = LFpG2Ph7AQyFvK7eE*/ /** tidy_set_encoding */
                            /** user_id = EXRdzFxta8yT9iJNH */ /** EmployeeTool */
                            /** user_id = aqZjArdGmLeKu2qHd */ /** nickolas */
                            /**dump($group_detail->_id);*/
                            /**dump($response->body->user->_id);*/
                            /** invite people to the roomId using their userId */
                            $response = \Httpful\Request::post($api->api.'groups.invite')
                            ->body(array('roomId' => $group_detail->_id, 'userId' => $response->body->user->_id))
                            ->send();
                            /**dd($response->body);*/
                            break;
                        }
                    }
                }
            }

            $public_groups_list = \Httpful\Request::get($api->api.'channels.list')->send();
            /**dd($public_groups_list->body->channels);*/
            /**Loggy::write('rocketchat_reminder', ' $public_groups_list ' . print_r($public_groups_list, true));*/

            if(isset($public_groups_list->body->channels)){
                foreach($array_of_groups_to_get_roomIds as $group_key => $roomName){
                    foreach($public_groups_list->body->channels as $group_detail){
                        if($group_detail->name == $roomName){
                            /**dump($group_detail);*/
                            $message_details->channels_detail[$independent_counter]['roomId'] = $group_detail->_id;
                            $message_details->channels_detail[$independent_counter]['roomName'] = $group_detail->name;
                            $independent_counter++;
                            break;
                        }
                    }
                }
            }

            /**dump('here');*/
            /**dump($message_details);*/

            $keepy_track = 0;
            if(isset($message_details->channels_detail)){
                foreach($message_details->channels_detail as $group_detail){

                    $messageContent = [];
                    //$messageContent['text'] = 'Hi '.$channelName.', '. $message;

                    $messageContent['text'] = ' '. $message;
                    $messageContent["attachments"] = [[
                        "author_name" => 'JobID Uploaded to s3:-',
                        "collapsed" => false,
                        "color" => "#228B22",
                    ]];

                    Loggy::write('rocketchat_reminder', $message);
                    $response = \Httpful\Request::post($api->api.'chat.postMessage')
                    ->body(array_merge(array('roomId' => $group_detail['roomId']), $messageContent))
                    ->send();

                    if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
                        /**return true;*/
                    } else {
                        /**dd($response);*/
                        $keepy_track++;
                        Loggy::write('rocketchat_reminder', 'Sending Message Error to group postMessage @ '.$mytime. ' FOR DATE: '.$input_date. ' ' .$response. ' ' .$group_detail['roomName']. ' ' .$group_detail['roomId']);
                    }
                }
            }

            /** testing between */
            /**$users_list = \Httpful\Request::get($api->api.'users.list')->send();*/
            /**dump('if logged in you will see users.list below or code 200');*/
            /**dump($users_list->code);*/
            /** end testing between */


        } catch(\Exception $e) {
            Debugbar::addException($e);
            throw $e;
        }
        if(isset($message_details->channels_detail)){
            $actual_count = COUNT($message_details->channels_detail) - $keepy_track;
        }else{
            $actual_count = '';
        }
        Loggy::write('rocketchat_reminder', 'Sending Message to group(s) Completed @ '.$mytime. ' FOR DATE: '. $input_date .' COUNT OF MESSAGES SENT = '. $actual_count . 'Group(S): ' . $logging_array_of_groups_to_get_roomIds);
    }

    /**
     * send_message_BTRX
     *
     * @author sigmoswitch
     * @return
     */
    public function send_message_BTRX($array_of_users_to_message = [], $message = '', $attachment = '', $testing = false)
    {
        /** because the way bitrix sends messages differently we need to accomodate for that */
        /** all notivications to run through to bitrix will be sent to the designated Private Chat */

        /**dd($message);*/

        /** create the log folder for this worker if it hasn't been created */
        $path = storage_path().'/logs/rocketchat_reminder/';
        \File::makeDirectory($path, 0777, true, true); /**make directory*/

        /** use the current date of scheduled task */
        $mytime = Carbon::now()->toDateTimeString();

        $input_date = Carbon::now()->format('Y-m-d');
        //dump($input_date);


        if(empty($array_of_users_to_message)){
            Loggy::write('rocketchat_reminder', 'empty');
            return;
        }

        if(is_array($array_of_users_to_message)){
            $logging_array_of_users_to_message = implode(', ', $array_of_users_to_message);
        }

        Loggy::write('rocketchat_reminder', 'Sending Message to user(s) ' . $logging_array_of_users_to_message);
        /**dd($array_of_users_to_message);*/

        try {
            $infolog = new Logger('bitrixAPIinfo');
            $infolog->pushHandler(new StreamHandler(storage_path('logs').'/bitrixAPIinfo/'.'bitrix24-api-client-debug--'.Carbon::now()->format('Y-m-d').'.log', Logger::DEBUG));
            $log = new Logger('bitrixAPI');
            $log->pushHandler(new StreamHandler(storage_path('logs').'/bitrixAPI/'.'bitrix24-api-client-debug--'.Carbon::now()->format('Y-m-d').'.log', Logger::DEBUG));

            // $client = HttpClient::create();
            $client = new RetryableHttpClient(HttpClient::create());

            $credentials = new \Bitrix24\SDK\Core\Credentials\Credentials(
                new \Bitrix24\SDK\Core\Credentials\WebhookUrl(env("BITRIX24_DUS_WEBHOOK", "")),
                null,
                null,
                null
            );

            $apiClient = new \Bitrix24\SDK\Core\ApiClient($credentials, $client, $infolog);

            $result = $apiClient->getResponse('app.info');
            try {
                $result_dump = json_decode($result->getContent(), true);
            } catch (\Exception $e) {
                echo $e->getMessage();
                die();
            }

            if($result->getInfo('http_code') != 200){
                die();
            }else{
                /** if it passes through that means that the thing is on? */
            }

            /** SINCE ITS TO INDIVIDUALS */
            $apiClient = new \Bitrix24\SDK\Core\ApiClient($credentials, $client, $log);
            $message_recipient_bitrix_user_id = '';
            $keepy_track = 0;

            if($testing){
                $JOBFOLDER_DIR_SHARELOCATION_STRING = env('JOBFOLDER_DIR_SHARELOCATION_STRING');
                $JOBFOLDER_DIR_SHARELOCATION_STRING_DECODED = env('JOBFOLDER_DIR_SHARELOCATION_STRING_DECODED');
                $attachment = $JOBFOLDER_DIR_SHARELOCATION_STRING . '||' . $JOBFOLDER_DIR_SHARELOCATION_STRING_DECODED;
            }

            dump($attachment);
            Loggy::write('rocketchat_reminder', '$attachment = ' . json_encode($attachment, true));

            foreach($array_of_users_to_message as $message_recipient_bitrix_user_id){


                if($attachment != ''){
                    $messageContent = [];
                    $messageContent['text'] = 'JobID New:- '. $message;

                    /** FORMAT THE MESSAGE TO FOLLOW BITRIX STYLING DELIMITERS */
                    $exploded = explode("||", $attachment);
                    $result = $apiClient->getResponse('im.notify.personal.add', array(
                        "USER_ID" => $message_recipient_bitrix_user_id,
                        "MESSAGE" => $messageContent['text'],
                        "ATTACH" => array(
                            array("LINK" => array(
                                "NAME" => (string)$exploded[1],
                                "LINK" => (string)$exploded[0],
                            )),
                            array("DELIMITER" => array(
                               'SIZE' => 200,
                               'COLOR' => "#c6c6c6"
                            )),
                            array("MESSAGE" => '[URL='.(string)$exploded[2].']'.(string)$exploded[3].'[/URL]'),
                        )
                    ));
                }else{
                    $messageContent = [];
                    $messageContent['text'] = 'JobID New:- '. $message;

                    /** FORMAT THE MESSAGE TO FOLLOW BITRIX STYLING DELIMITERS */


                    $result = $apiClient->getResponse('im.notify.personal.add', array(
                        'USER_ID' => $message_recipient_bitrix_user_id,
                        'MESSAGE' => $messageContent['text']
                    ));
                }


                try {
                    $result_dump = json_decode($result->getContent(), true);
                } catch (\Exception $e) {

                    dump($e->getMessage());
                    dump($result_dump);

                    Loggy::write('rocketchat_reminder', 'AA Sending Message Error to user postMessage @ '.$mytime. ' FOR DATE: '. $input_date. ' getMessage() = '.json_decode($e->getMessage(), true) . ' $result_dump = ' . json_encode($result_dump, true) . ' ' . $message_recipient_bitrix_user_id);
                    /** break out of the foreach i guess because there is a crippling error */
                    /** could be an error with the endpoint or network etc. */
                    throw new \Exception('Sending Message Error to user postMessage @ '.$mytime. ' FOR DATE: '. $input_date . ' $result->getInfo("http_code") = '. json_decode($result->getInfo('http_code'), true));
                }

                if($result->getInfo('http_code') != 200){
                    $keepy_track++;
                    Loggy::write('rocketchat_reminder', 'BB Sending Message Error to user postMessage @ '.$mytime. ' FOR DATE: '. $input_date. ''.$result->getInfo('http_code') . '' . $message_recipient_bitrix_user_id);
                }else{
                    /** if it passes through that means that the thing is okay onto the next bitrix user_id */
                }
            }

        } catch(\Exception $e) {
            Debugbar::addException($e);
            throw $e;
        }

        $actual_count = COUNT($array_of_users_to_message) - $keepy_track;
        Loggy::write('rocketchat_reminder', 'CC Sending Message to user(s) Completed @ '.$mytime. ' FOR DATE: '. $input_date .' COUNT OF MESSAGES SENT = '. $actual_count . ' Users: ' . $message_recipient_bitrix_user_id);
    }

    /**
     * send_message_togroup_BTRX
     *
     * @author sigmoswitch
     * @return
     */
    public function send_message_togroup_BTRX($array_of_groups_to_get_roomIds = [], $message = '',  $attachment = '', $testing = false)
    {
        /** create the log folder for this worker if it hasn't been created */
        $path = storage_path().'/logs/rocketchat_reminder/';
        \File::makeDirectory($path, 0777, true, true); /**make directory*/

        /** use the current date of scheduled task */
        $mytime = Carbon::now()->toDateTimeString();

        $input_date = Carbon::now()->format('Y-m-d');
        //dump($input_date);


        if(empty($array_of_groups_to_get_roomIds)){
            Loggy::write('rocketchat_reminder', 'empty');
            return;
        }

        if(is_array($array_of_groups_to_get_roomIds)){
            $logging_array_of_groups_to_get_roomIds = implode(', ', $array_of_groups_to_get_roomIds);
        }

        Loggy::write('rocketchat_reminder', 'Sending Message to group(s) ' . $logging_array_of_groups_to_get_roomIds);
        /**dump($array_of_groups_to_get_roomIds);*/

        try {
            $infolog = new Logger('bitrixAPIinfo');
            $infolog->pushHandler(new StreamHandler(storage_path('logs').'/bitrixAPIinfo/'.'bitrix24-api-client-debug--'.Carbon::now()->format('Y-m-d').'.log', Logger::DEBUG));
            $log = new Logger('bitrixAPI');
            $log->pushHandler(new StreamHandler(storage_path('logs').'/bitrixAPI/'.'bitrix24-api-client-debug--'.Carbon::now()->format('Y-m-d').'.log', Logger::DEBUG));

            // $client = HttpClient::create();
            $client = new RetryableHttpClient(HttpClient::create());

            $credentials = new \Bitrix24\SDK\Core\Credentials\Credentials(
                new \Bitrix24\SDK\Core\Credentials\WebhookUrl(env("BITRIX24_DUS_WEBHOOK", "")),
                null,
                null,
                null
            );

            $apiClient = new \Bitrix24\SDK\Core\ApiClient($credentials, $client, $infolog);

            $result = $apiClient->getResponse('app.info');
            try {
                $result_dump = json_decode($result->getContent(), true);
            } catch (\Exception $e) {
                echo $e->getMessage();
                die();
            }

            if($result->getInfo('http_code') != 200){
                die();
            }else{
                /** if it passes through that means that the thing is on? */
            }

            /** SINCE ITS TO GROUP */
            /** check for trailing slash */
            $lastCharacterBitrixWebHook = substr(env("BITRIX24_DUS_WEBHOOK", ""), -1);
            if($lastCharacterBitrixWebHook == "/"){
                $newWebHook = substr(env("BITRIX24_DUS_WEBHOOK", ""), 0, -1);
            }else{
                $newWebHook = env("BITRIX24_DUS_WEBHOOK", "");
            }
            $credentials = new \Bitrix24\SDK\Core\Credentials\Credentials(
                new \Bitrix24\SDK\Core\Credentials\WebhookUrl($newWebHook),
                null,
                null,
                null
            );

            $apiClient = new \Bitrix24\SDK\Core\ApiClient($credentials, $client, $log);

            if (strpos(strtolower(env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", "")), 'chat') !== false) {
                $chatroom_id = strtolower(env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", ""));
            }else{
                /** check that the string can be parsed to a whole number */
                $chatroom_id = 'chat'.env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", "");
            }

            if($testing){
                $chatroom_id = 'chat9214';
            }

            dump($chatroom_id);

            dump($attachment);
            Loggy::write('rocketchat_reminder', '$attachment = ' . json_encode($attachment, true));

            if($attachment != ''){
                $messageContent = [];
                //$messageContent['text'] = 'Hi '.$channelName.', '. $message;
                $messageContent['text'] = 'JobID New:- '. $message;

                /** it was said that the 400 Error is because the Message Body is not formed correctly .. */
                /** tested on the bitrix.develop environment.. seems to be okay.. */
                /** production still throws and error. */

                /** FORMAT THE MESSAGE TO FOLLOW BITRIX STYLING DELIMITERS */
                $exploded = explode("||", $attachment);
                $result = $apiClient->getResponse('im.message.add', array(
                    "DIALOG_ID" => $chatroom_id,
                    "MESSAGE" => $messageContent['text'],
                    "SYSTEM" => "Y",
                    "URL_PREVIEW" => "N",
                    "ATTACH" => array(
                        array("LINK" => array(
                            "NAME" => (string)$exploded[1],
                            "LINK" => (string)$exploded[0],
                        )),
                        array("DELIMITER" => array(
                           'SIZE' => 200,
                           'COLOR' => "#c6c6c6"
                        )),
                        array("MESSAGE" => '[URL='.(string)$exploded[2].']'.(string)$exploded[3].'[/URL]'),
                    )
                ));
            }else{
                $messageContent = [];
                $messageContent['text'] = 'JobID New:- '. $message;

                /** it was said that the 400 Error is because the Message Body is not formed correctly .. */
                /** tested on the bitrix.develop environment.. seems to be okay.. */
                /** production still throws and error. */

                $result = $apiClient->getResponse('im.message.add', array(
                    "DIALOG_ID" => $chatroom_id,
                    "MESSAGE" => $messageContent['text'],
                    "SYSTEM" => "Y",
                    "URL_PREVIEW" => "N"
                ));
            }

            $keepy_track = 0;
            try {
                $result_dump = json_decode($result->getContent(), true);
            } catch (\Exception $e) {
                dump($e->getMessage());
                dump($result_dump);
                dump(strtolower(env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", "")));
                Loggy::write('rocketchat_reminder', 'AA Sending Message Error to group postMessage @ '.$mytime. ' FOR DATE: '. $input_date. ' getMessage() = '.json_decode($e->getMessage(), true) . ' $result_dump = ' . json_encode($result_dump, true) . strtolower(env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", "")));
                throw new \Exception('Sending Message Error to group postMessage @ '.$mytime. ' FOR DATE: '. $input_date . ' $result->getInfo("http_code") = '. json_decode($result->getInfo('http_code'), true));
            }

            if($result->getInfo('http_code') != 200){
                $keepy_track++;
                Loggy::write('rocketchat_reminder', 'BB Sending Message Error to group postMessage @ '.$mytime. ' FOR DATE: '. $input_date. ''.$result->getInfo("http_code") . '' . strtolower(env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", "")));
            }else{
                /** if it passes through that means that the thing is okay onto the next bitrix user_id */
            }
        } catch(\Exception $e) {
            Debugbar::addException($e);
            throw $e;
        }
        $actual_count = '';
        Loggy::write('rocketchat_reminder', 'Sending Message to group(s) Completed @ '.$mytime. ' FOR DATE: '. $input_date .' COUNT OF MESSAGES SENT = '. $actual_count . 'Group(S): ' . strtolower(env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", "")));
    }





    /**
     * send_upload_message_BTRX
     *
     * @author sigmoswitch
     * @return
     */
    public function send_upload_message_BTRX($array_of_users_to_message = [], $message = '')
    {

        /**dd($message);*/

        /** create the log folder for this worker if it hasn't been created */
        $path = storage_path().'/logs/rocketchat_reminder/';
        \File::makeDirectory($path, 0777, true, true); /**make directory*/

        /** use the current date of scheduled task */
        $mytime = Carbon::now()->toDateTimeString();

        $input_date = Carbon::now()->format('Y-m-d');
        //dump($input_date);


        if(empty($array_of_users_to_message)){
            Loggy::write('rocketchat_reminder', 'empty');
            return;
        }

        if(is_array($array_of_users_to_message)){
            $logging_array_of_users_to_message = implode(', ', $array_of_users_to_message);
        }

        Loggy::write('rocketchat_reminder', 'Sending Message to user(s) ' . $logging_array_of_users_to_message);
        /**dd($array_of_users_to_message);*/

        try {
            $infolog = new Logger('bitrixAPIinfo');
            $infolog->pushHandler(new StreamHandler(storage_path('logs').'/bitrixAPIinfo/'.'bitrix24-api-client-debug--'.Carbon::now()->format('Y-m-d').'.log', Logger::DEBUG));
            $log = new Logger('bitrixAPI');
            $log->pushHandler(new StreamHandler(storage_path('logs').'/bitrixAPI/'.'bitrix24-api-client-debug--'.Carbon::now()->format('Y-m-d').'.log', Logger::DEBUG));

            // $client = HttpClient::create();
            $client = new RetryableHttpClient(HttpClient::create());

            $credentials = new \Bitrix24\SDK\Core\Credentials\Credentials(
                new \Bitrix24\SDK\Core\Credentials\WebhookUrl(env("BITRIX24_DUS_WEBHOOK", "")),
                null,
                null,
                null
            );

            $apiClient = new \Bitrix24\SDK\Core\ApiClient($credentials, $client, $infolog);

            $result = $apiClient->getResponse('app.info');
            try {
                $result_dump = json_decode($result->getContent(), true);
            } catch (\Exception $e) {
                echo $e->getMessage();
                die();
            }

            if($result->getInfo('http_code') != 200){
                die();
            }else{
                /** if it passes through that means that the thing is on? */
            }

            /** SINCE ITS TO INDIVIDUALS */
            $apiClient = new \Bitrix24\SDK\Core\ApiClient($credentials, $client, $log);
            $message_recipient_bitrix_user_id = '';
            $keepy_track = 0;
            foreach($array_of_users_to_message as $message_recipient_bitrix_user_id){

                /**dd($message_recipient_bitrix_user_id);*/



                $messageContent = [];
                //$messageContent['text'] = 'Hi '.$channelName.', '. $message;
                $messageContent['text'] = 'JobID New:- '. $message;

                /** FORMAT THE MESSAGE TO FOLLOW BITRIX STYLING DELIMITERS */

                $result = $apiClient->getResponse('im.notify.personal.add', array(
                    'USER_ID' => $message_recipient_bitrix_user_id,
                    'MESSAGE' => $messageContent['text'],
                    //'MESSAGE_OUT' => 'Personal notification text for email',
                    //'TAG' => 'TEST',
                    //'SUB_TAG' => 'SUB|TEST',
                    'ATTACH' => ''
                ));
                try {
                    $result_dump = json_decode($result->getContent(), true);
                } catch (\Exception $e) {
                    //echo $e->getMessage();
                    //$keepy_track++;
                    Loggy::write('rocketchat_reminder', 'Sending Message Error to user postMessage @ '.$mytime. ' FOR DATE: '. $input_date. ''.json_decode($e->getMessage(), true) . '' . $message_recipient_bitrix_user_id);
                    /** break out of the foreach i guess because there is a crippling error */
                    /** could be an error with the endpoint or network etc. */
                    break;
                }

                if($result->getInfo('http_code') != 200){
                    $keepy_track++;
                    Loggy::write('rocketchat_reminder', 'Sending Message Error to user postMessage @ '.$mytime. ' FOR DATE: '. $input_date. ''.$result->getInfo("http_code") . '' . $message_recipient_bitrix_user_id);
                }else{
                    /** if it passes through that means that the thing is okay onto the next bitrix user_id */
                }
            }

        } catch(\Exception $e) {
            Debugbar::addException($e);
            throw $e;
        }

        $actual_count = COUNT($array_of_users_to_message) - $keepy_track;
        Loggy::write('rocketchat_reminder', 'Sending Message to user(s) Completed @ '.$mytime. ' FOR DATE: '. $input_date .' COUNT OF MESSAGES SENT = '. $actual_count . 'Users: ' . $message_recipient_bitrix_user_id);
    }

    /**
     * send_upload_message_togroup_BTRX
     *
     * @author sigmoswitch
     * @return
     */
    public function send_upload_message_togroup_BTRX($array_of_groups_to_get_roomIds = [], $message = '')
    {
        /** create the log folder for this worker if it hasn't been created */
        $path = storage_path().'/logs/rocketchat_reminder/';
        \File::makeDirectory($path, 0777, true, true); /**make directory*/

        /** use the current date of scheduled task */
        $mytime = Carbon::now()->toDateTimeString();

        $input_date = Carbon::now()->format('Y-m-d');
        //dump($input_date);


        if(empty($array_of_groups_to_get_roomIds)){
            Loggy::write('rocketchat_reminder', 'empty');
            return;
        }

        if(is_array($array_of_groups_to_get_roomIds)){
            $logging_array_of_groups_to_get_roomIds = implode(', ', $array_of_groups_to_get_roomIds);
        }

        Loggy::write('rocketchat_reminder', 'Sending Message to group(s) ' . $logging_array_of_groups_to_get_roomIds);
        /**dump($array_of_groups_to_get_roomIds);*/

        try {
            $infolog = new Logger('bitrixAPIinfo');
            $infolog->pushHandler(new StreamHandler(storage_path('logs').'/bitrixAPIinfo/'.'bitrix24-api-client-debug--'.Carbon::now()->format('Y-m-d').'.log', Logger::DEBUG));
            $log = new Logger('bitrixAPI');
            $log->pushHandler(new StreamHandler(storage_path('logs').'/bitrixAPI/'.'bitrix24-api-client-debug--'.Carbon::now()->format('Y-m-d').'.log', Logger::DEBUG));

            // $client = HttpClient::create();
            $client = new RetryableHttpClient(HttpClient::create());

            $credentials = new \Bitrix24\SDK\Core\Credentials\Credentials(
                new \Bitrix24\SDK\Core\Credentials\WebhookUrl(env("BITRIX24_DUS_WEBHOOK", "")),
                null,
                null,
                null
            );

            $apiClient = new \Bitrix24\SDK\Core\ApiClient($credentials, $client, $infolog);

            $result = $apiClient->getResponse('app.info');
            try {
                $result_dump = json_decode($result->getContent(), true);
            } catch (\Exception $e) {
                echo $e->getMessage();
                die();
            }

            if($result->getInfo('http_code') != 200){
                die();
            }else{
                /** if it passes through that means that the thing is on? */
            }

            /** SINCE ITS TO GROUP */
            $apiClient = new \Bitrix24\SDK\Core\ApiClient($credentials, $client, $log);
            if (strpos(strtolower(env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", "")), 'chat') !== false) {
                $chatroom_id = strtolower(env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", ""));
            }else{
                /** check that the string can be parsed to a whole number */
                $chatroom_id = 'chat'.env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", "");
            }
            dump($chatroom_id);
            $messageContent = [];
            //$messageContent['text'] = 'Hi '.$channelName.', '. $message;
            $messageContent['text'] = 'JobID New:- '. $message;

            $result = $apiClient->getResponse('im.message.add', array(
                'DIALOG_ID' => $chatroom_id,
                'MESSAGE' => $messageContent['text'],
                'SYSTEM' => 'Y',
                'ATTACH' => '',
                'URL_PREVIEW' => 'N',
                //'KEYBOARD' => '',
                //'MENU' => '',
            ));
            $keepy_track = 0;
            try {
                $result_dump = json_decode($result->getContent(), true);
            } catch (\Exception $e) {
                dump($e->getMessage());
                dump($result_dump);
                dump(strtolower(env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", "")));
                Loggy::write('rocketchat_reminder', 'Sending Message Error to group postMessage @ '.$mytime. ' FOR DATE: '. $input_date);
                throw new \Exception('Sending Message Error to group postMessage @ '.$mytime. ' FOR DATE: '. $input_date);
            }

            if($result->getInfo('http_code') != 200){
                $keepy_track++;
                Loggy::write('rocketchat_reminder', 'Sending Message Error to group postMessage @ '.$mytime. ' FOR DATE: '. $input_date. ''.$result->getInfo('http_code') . '' . strtolower(env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", "")));
            }else{
                /** if it passes through that means that the thing is okay onto the next bitrix user_id */
            }
        } catch(\Exception $e) {
            Debugbar::addException($e);
            throw $e;
        }
        $actual_count = '';
        Loggy::write('rocketchat_reminder', 'Sending Message to group(s) Completed @ '.$mytime. ' FOR DATE: '. $input_date .' COUNT OF MESSAGES SENT = '. $actual_count . 'Group(S): ' . strtolower(env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", "")));
    }




    /**
     * send_uploaded_to_s3_message_BTRX
     *
     * @author sigmoswitch
     * @return
     */
    public function send_uploaded_to_s3_message_BTRX($array_of_users_to_message = [], $message = '')
    {

        /**dd($message);*/

        /** create the log folder for this worker if it hasn't been created */
        $path = storage_path().'/logs/rocketchat_reminder/';
        \File::makeDirectory($path, 0777, true, true); /**make directory*/

        /** use the current date of scheduled task */
        $mytime = Carbon::now()->toDateTimeString();

        $input_date = Carbon::now()->format('Y-m-d');
        //dump($input_date);


        if(empty($array_of_users_to_message)){
            Loggy::write('rocketchat_reminder', 'empty');
            return;
        }

        if(is_array($array_of_users_to_message)){
            $logging_array_of_users_to_message = implode(', ', $array_of_users_to_message);
        }

        Loggy::write('rocketchat_reminder', 'Sending Message to user(s) ' . $logging_array_of_users_to_message);
        /**dd($array_of_users_to_message);*/


        try {
            $infolog = new Logger('bitrixAPIinfo');
            $infolog->pushHandler(new StreamHandler(storage_path('logs').'/bitrixAPIinfo/'.'bitrix24-api-client-debug--'.Carbon::now()->format('Y-m-d').'.log', Logger::DEBUG));
            $log = new Logger('bitrixAPI');
            $log->pushHandler(new StreamHandler(storage_path('logs').'/bitrixAPI/'.'bitrix24-api-client-debug--'.Carbon::now()->format('Y-m-d').'.log', Logger::DEBUG));

            // $client = HttpClient::create();
            $client = new RetryableHttpClient(HttpClient::create());

            $credentials = new \Bitrix24\SDK\Core\Credentials\Credentials(
                new \Bitrix24\SDK\Core\Credentials\WebhookUrl(env("BITRIX24_DUS_WEBHOOK", "")),
                null,
                null,
                null
            );

            $apiClient = new \Bitrix24\SDK\Core\ApiClient($credentials, $client, $infolog);

            $result = $apiClient->getResponse('app.info');
            try {
                $result_dump = json_decode($result->getContent(), true);
            } catch (\Exception $e) {
                echo $e->getMessage();
                die();
            }

            if($result->getInfo('http_code') != 200){
                die();
            }else{
                /** if it passes through that means that the thing is on? */
            }

            /** SINCE ITS TO INDIVIDUALS */
            $apiClient = new \Bitrix24\SDK\Core\ApiClient($credentials, $client, $log);
            $message_recipient_bitrix_user_id = '';
            $keepy_track = 0;
            foreach($array_of_users_to_message as $message_recipient_bitrix_user_id){

                /**dd($message_recipient_bitrix_user_id);*/



                $messageContent = [];
                //$messageContent['text'] = 'Hi '.$channelName.', '. $message;
                $messageContent['text'] = 'JobID New:- '. $message;

                /** FORMAT THE MESSAGE TO FOLLOW BITRIX STYLING DELIMITERS */

                $result = $apiClient->getResponse('im.notify.personal.add', array(
                    'USER_ID' => $message_recipient_bitrix_user_id,
                    'MESSAGE' => $messageContent['text'],
                    //'MESSAGE_OUT' => 'Personal notification text for email',
                    //'TAG' => 'TEST',
                    //'SUB_TAG' => 'SUB|TEST',
                    'ATTACH' => ''
                ));
                try {
                    $result_dump = json_decode($result->getContent(), true);
                } catch (\Exception $e) {
                    //echo $e->getMessage();
                    //$keepy_track++;
                    Loggy::write('rocketchat_reminder', 'Sending Message Error to user postMessage @ '.$mytime. ' FOR DATE: '. $input_date. ''.json_decode($e->getMessage(), true) . '' . $message_recipient_bitrix_user_id);
                    /** break out of the foreach i guess because there is a crippling error */
                    /** could be an error with the endpoint or network etc. */
                    break;
                }

                if($result->getInfo('http_code') != 200){
                    $keepy_track++;
                    Loggy::write('rocketchat_reminder', 'Sending Message Error to user postMessage @ '.$mytime. ' FOR DATE: '. $input_date. ''.$result->getInfo('http_code') . '' . $message_recipient_bitrix_user_id);
                }else{
                    /** if it passes through that means that the thing is okay onto the next bitrix user_id */
                }
            }

        } catch(\Exception $e) {
            Debugbar::addException($e);
            throw $e;
        }

        $actual_count = COUNT($array_of_users_to_message) - $keepy_track;
        Loggy::write('rocketchat_reminder', 'Sending Message to user(s) Completed @ '.$mytime. ' FOR DATE: '. $input_date .' COUNT OF MESSAGES SENT = '. $actual_count . 'Users: ' . $message_recipient_bitrix_user_id);
    }

    /**
     * send_uploaded_to_s3_message_togroup_BTRX
     *
     * @author sigmoswitch
     * @return
     */
    public function send_uploaded_to_s3_message_togroup_BTRX($array_of_groups_to_get_roomIds = [], $message = '')
    {
        /** create the log folder for this worker if it hasn't been created */
        $path = storage_path().'/logs/rocketchat_reminder/';
        \File::makeDirectory($path, 0777, true, true); /**make directory*/

        /** use the current date of scheduled task */
        $mytime = Carbon::now()->toDateTimeString();

        $input_date = Carbon::now()->format('Y-m-d');
        //dump($input_date);


        if(empty($array_of_groups_to_get_roomIds)){
            Loggy::write('rocketchat_reminder', 'empty');
            return;
        }

        if(is_array($array_of_groups_to_get_roomIds)){
            $logging_array_of_groups_to_get_roomIds = implode(', ', $array_of_groups_to_get_roomIds);
        }

        Loggy::write('rocketchat_reminder', 'Sending Message to group(s) ' . $logging_array_of_groups_to_get_roomIds);
        /**dump($array_of_groups_to_get_roomIds);*/

        try {
            $infolog = new Logger('bitrixAPIinfo');
            $infolog->pushHandler(new StreamHandler(storage_path('logs').'/bitrixAPIinfo/'.'bitrix24-api-client-debug--'.Carbon::now()->format('Y-m-d').'.log', Logger::DEBUG));
            $log = new Logger('bitrixAPI');
            $log->pushHandler(new StreamHandler(storage_path('logs').'/bitrixAPI/'.'bitrix24-api-client-debug--'.Carbon::now()->format('Y-m-d').'.log', Logger::DEBUG));

            // $client = HttpClient::create();
            $client = new RetryableHttpClient(HttpClient::create());

            $credentials = new \Bitrix24\SDK\Core\Credentials\Credentials(
                new \Bitrix24\SDK\Core\Credentials\WebhookUrl(env("BITRIX24_DUS_WEBHOOK", "")),
                null,
                null,
                null
            );

            $apiClient = new \Bitrix24\SDK\Core\ApiClient($credentials, $client, $infolog);

            $result = $apiClient->getResponse('app.info');
            try {
                $result_dump = json_decode($result->getContent(), true);
            } catch (\Exception $e) {
                echo $e->getMessage();
                die();
            }

            if($result->getInfo('http_code') != 200){
                die();
            }else{
                /** if it passes through that means that the thing is on? */
            }

            /** SINCE ITS TO GROUP */
            $apiClient = new \Bitrix24\SDK\Core\ApiClient($credentials, $client, $log);
            if (strpos(strtolower(env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", "")), 'chat') !== false) {
                $chatroom_id = strtolower(env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", ""));
            }else{
                /** check that the string can be parsed to a whole number */
                $chatroom_id = 'chat'.env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", "");
            }
            dump($chatroom_id);
            $messageContent = [];
            //$messageContent['text'] = 'Hi '.$channelName.', '. $message;
            $messageContent['text'] = 'JobID New:- '. $message;

            $result = $apiClient->getResponse('im.message.add', array(
                'DIALOG_ID' => $chatroom_id,
                'MESSAGE' => $messageContent['text'],
                'SYSTEM' => 'Y',
                'ATTACH' => '',
                'URL_PREVIEW' => 'N',
                //'KEYBOARD' => '',
                //'MENU' => '',
            ));
            $keepy_track = 0;
            try {
                $result_dump = json_decode($result->getContent(), true);
            } catch (\Exception $e) {
                dump($e->getMessage());
                dump($result_dump);
                dump(strtolower(env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", "")));
                Loggy::write('rocketchat_reminder', 'Sending Message Error to group postMessage @ '.$mytime. ' FOR DATE: '. $input_date);
                throw new \Exception('Sending Message Error to group postMessage @ '.$mytime. ' FOR DATE: '. $input_date);
            }

            if($result->getInfo('http_code') != 200){
                $keepy_track++;
                Loggy::write('rocketchat_reminder', 'Sending Message Error to group postMessage @ '.$mytime. ' FOR DATE: '. $input_date. ''.$result->getInfo('http_code') . '' . strtolower(env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", "")));
            }else{
                /** if it passes through that means that the thing is okay onto the next bitrix user_id */
            }
        } catch(\Exception $e) {
            Debugbar::addException($e);
            throw $e;
        }
        $actual_count = '';
        Loggy::write('rocketchat_reminder', 'Sending Message to group(s) Completed @ '.$mytime. ' FOR DATE: '. $input_date .' COUNT OF MESSAGES SENT = '. $actual_count . 'Group(S): ' . strtolower(env("BITRIX24_DUS_NOTIFICATIONS_CHAT_ROOM_ID", "")));
    }


    /**
     * check_rocket_chat_group_lists
     *
     * @author sigmoswitch
     * @return
     */
    public function check_rocket_chat_group_lists()
    {
        /** create the log folder for this worker if it hasn't been created */
        $path = storage_path().'/logs/rocketchat_reminder/';
        \File::makeDirectory($path, 0777, true, true); /**make directory*/

        $api = new \stdClass;
        $api->api = env("ROCKET_CHAT_INSTANCE", "").env("REST_API_ROOT", "");
        /**dd($api);*/

        $existing_x_user_id = app('App\Http\Controllers\OperatorController')->prepare_rocket_chat_connection(true);
        /**dump($existing_x_user_id);*/

        $tmp = \Httpful\Request::init()->sendsJson()->expectsJson()
        ->addHeader('X-Auth-Token', $existing_x_user_id->x_auth_token)
        ->addHeader('X-User-Id', $existing_x_user_id->x_user_id);
        \Httpful\Request::ini($tmp);

        $private_groups_list = \Httpful\Request::get($api->api.'groups.listAll')->send();
        if($private_groups_list->body->success){
            dump($private_groups_list->body->groups);
        }else{
            dump($private_groups_list->body);
        }
        /**Loggy::write('rocketchat_reminder', ' $private_groups_list ' . print_r($private_groups_list, true));*/

        $public_groups_list = \Httpful\Request::get($api->api.'channels.list')->send();
        if($public_groups_list->body->success){
            dump($public_groups_list->body->channels);
        }else{
            dump($public_groups_list->body);
        }
        /**Loggy::write('rocketchat_reminder', ' $public_groups_list ' . print_r($public_groups_list, true));*/
    }

    /**
     * check_tasks_downloads_files_and_upload_files
     *
     * @author sigmoswitch
     * @return
     */
    public function check_tasks_downloads_files_and_upload_files()
    {
       $task_downloads_files_not_finished_processing = DB::table('tasks_downloads_files')->select(['type', 'case_id', 'state', 'live', 'local', 'size', 'file_count'])->where('state', '!=', 'notified')->get()->keyBy('case_id')->toArray();
       /**dump($task_downloads_files_not_finished_processing);*/

       $upload_files_not_finished_processing = DB::table('tasks_uploads')->select(['*'])->whereNotIn('state', ['downloaded', 'in progress', 'feedback', 'ready', 'pause', 'check', 'uploaded to s3'])->get()->keyBy('case_id')->toArray();
       /**dump($upload_files_not_finished_processing);*/

       $task_manual_downloads_files_not_finished_processing = DB::table('tasks_manual_downloads_files')->select(['type', 'case_id', 'state', 'live', 'local', 'size', 'file_count'])->where('state', '!=', 'notified')->get()->keyBy('case_id')->toArray();
       /**dump($task_manual_downloads_files_not_finished_processing);*/


       $worker_jobs_table = DB::table('jobs')->select(['*'])->where('queue', '!=', 'default')->get()->groupBy('queue')->map(function ($ts) {return $ts->keyBy('id');})->toArray();
       /**dump($worker_jobs_table);*/
       $worker_failedjobs_table = DB::table('failed_jobs')->select(['*'])->where('queue', '!=', 'default')->get()->groupBy('queue')->map(function ($ts) {return $ts->keyBy('id');})->toArray();
       /**dump($worker_failedjobs_table);*/

       $current_timestamp = Carbon::now()->timestamp;
       $current_datetime = Carbon::now();

       $this->data = compact('current_timestamp', 'current_datetime');
       $this->js = compact('task_downloads_files_not_finished_processing', 'upload_files_not_finished_processing', 'task_manual_downloads_files_not_finished_processing', 'worker_jobs_table', 'worker_failedjobs_table');

       return $this->render('welcome.index');
    }


    /**
     * populate_delivery_time_for_all_those_missing_in_download_tasks_list
     *
     * @author sigmoswitch
     * @return
     */
    public function populate_delivery_time_for_all_those_missing_in_download_tasks_list($start_date_ymd = null)
    {
        if($start_date_ymd != null){
            /** we start searching for any tasks_downloads_files after this date */
            $start_date_ymd_carbon = Carbon::createFromFormat('Y-m-d', $start_date_ymd)->startOfDay();
            $start_date_ymd = $start_date_ymd_carbon->format('Y-m-d H:i:s');
            /**dd($start_date_ymd);*/
        }
        /** we go into s3 and grab all the xml files that don't have a delivery time */
        /** we could conditionally provide some query parameters so that it doesn't do the whole table just those between dates etc...*/

        $all_download_list_that_do_not_have_delivery_time = DB::table('tasks_downloads_files')
        ->where('xml_deliverytime_contents', '=', NULL)
        ->orWhere('xml_deliverytime_contents', '=', '')
        ->orWhere('xml_title_contents', '=', '')
        ->orWhere('xml_title_contents', '=', NULL)
        ->orWhere('xml_jobinfo', '=', '')
        ->orWhere('xml_jobinfoproduction', '=', '')
        ->get();

        /**dd($all_download_list_that_do_not_have_delivery_time);*/
        /** if we could possibly get only xml files that are a month old to current + future */

        $s3Br24Config = config('s3br24');
        $s3 = Storage::disk('s3');
        $bucket = config('filesystems.disks.s3.bucket');

        //dump($s3Br24Config);
        //dump($s3);
        //dd($bucket);

        foreach($all_download_list_that_do_not_have_delivery_time as $download_task_file_id_key => $download_task_file_details){

            /**dump($download_task_file_details);*/

            $carbon_check_download_task_file_details_created_at = Carbon::createFromFormat('Y-m-d H:i:s', $download_task_file_details->created_at);
            /**dump($carbon_check_download_task_file_details_created_at);*/
            if($start_date_ymd != null){
                if($carbon_check_download_task_file_details_created_at->greaterThanOrEqualTo($start_date_ymd_carbon)){
                    /**dump("DO IT");*/
                }else{
                    /** go to next in array*/
                    /**dump("SKIP");*/
                    continue;
                }
            }


           $file = $this->taskDownloadFile->where('id', $download_task_file_details->id )->first();

           $created_at_for_folder = $carbon_check_download_task_file_details_created_at->format('Y-m');
           /**dump($created_at_for_folder);*/


           /**dump($s3Br24Config['xml_tmp'].$created_at_for_folder.'/'.$download_task_file_details->case_id.'.xml');*/



          $checkexists = $s3->exists($s3Br24Config['xml_tmp'].$created_at_for_folder.'/'.$download_task_file_details->case_id.'.xml');


          if($checkexists){

              /** handle */
              /**$xlmFiles = $s3->files($s3Br24Config['xml_tmp'].$created_at_for_folder.'/');*/
              /**dd($xlmFiles);*/

              /** we know the file exists in this folder so we can just copy it using the case_id.xml to our working directory to get the details from it */
              $this->getXMLforMissingDeliveryTimeFromBucket(
                  $s3,
                  $bucket,
                  $s3Br24Config['xml_tmp'].$created_at_for_folder.'/'.$download_task_file_details->case_id.'.xml',
                  $s3Br24Config['xml_tmp'],
                  $s3Br24Config['xml_not_zip'],
                  $s3Br24Config['job_dir'],
                  $s3Br24Config['download_temp_folder'],
                  $file
              );

          }else{
              /** continue to the next in the download_files array */
              continue;
          }
        }
    }

    private function getXMLforMissingDeliveryTimeFromBucket($s3, $bucket, $xlmFileNAME, $xml_tmp_asia, $xml_not_zip_asia, $job_dir, $download_temp_folder, $task_downloads_file_model)
    {

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

        // dump($s3);
        // dump($bucket);
        // dump('$xlmFileNAME');
        // dump($xlmFileNAME);
        // dump('$xml_tmp_asia');
        // dump($xml_tmp_asia);
        // dump('$xml_not_zip_asia');
        // dump($xml_not_zip_asia);
        // dump('$job_dir');
        // dump($job_dir);
        // dump('$download_temp_folder');
        // dump($download_temp_folder);
        // dd(null);


        /**check exits task*/
        $caseId = basename($xlmFileNAME, ".xml");
        /**dump('$caseId');*/
        /**dump($caseId);*/


        $fileName = basename($xlmFileNAME);
        /**dump('$fileName');*/
        /**dump($fileName);*/



        /**download xlmFileNAME file*/
        $command = $client->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $xlmFileNAME
        ]);

        $requestXlm = $client->createPresignedRequest($command, $expiry);
        $uriXlm = (string)$requestXlm->getUri();

        $dir = storage_path()."/app".$download_temp_folder . "xml/delivery_time_getting";
        /**dump('$dir');*/
        /**dump($dir);*/


        /** we run the command once to download all the xml files */
        /** and run the page again to check if the file is there so we can read from it */

        $downloaded_xml_file_dir = storage_path()."/app".$download_temp_folder . "xml/delivery_time_getting/".$task_downloads_file_model->case_id. '.xml';

        if(File::exists($downloaded_xml_file_dir)){
            /**dump($unzip_log . ' exists');*/
            try {
                $xml_tool_client = '';
                $xml_title_contents = '';
                $xml_jobidtitle_contents = '';
                $xml_deliverytime_contents = '';
                $xml_jobInfo_contents = '';
                $anchor_for_xml_jobInfo = false;
                $xml_jobInfoProduction_contents = '';
                $anchor_for_xml_jobInfoProduction = false;

                $fropen = fopen($downloaded_xml_file_dir, 'r' );
                if ($fropen) {
                    while (($line = fgets($fropen)) !== false) {
                        /**dump($line);*/
                        if (strpos($line, '"') !== false) {
                            $line = str_replace('"', "'", $line);
                        }

                        if (strpos($line, '<clientToolId>') !== false && strpos($line, '</clientToolId>') !== false) {
                            $xml_tool_client = preg_replace('/\s\s+/', '', trim(str_replace("</clientToolId>", "", str_replace("<clientToolId>", "", $line))));
                        }

                        if (strpos($line, '<jobTitle>') !== false) {
                            $xml_title_contents = preg_replace('/\s\s+/', '', ltrim(str_replace("<![CDATA[", "", str_replace("]]></jobTitle>", "", str_replace("<jobTitle>", "", $line)))));
                        }
                        if (strpos($line, '<jobIdTitle>') !== false) {
                            $xml_jobidtitle_contents = preg_replace('/\s\s+/', '', trim(str_replace("</jobIdTitle>", "", str_replace("<jobIdTitle>", "", $line))));
                        }
                        if (strpos($line, '<deliveryProduction>') !== false) {
                            $xml_deliverytime_contents = preg_replace('/\s\s+/', '', trim(str_replace("</deliveryProduction>", "", str_replace("<deliveryProduction>", "", $line))));
                        }


                        if(is_null($anchor_for_xml_jobInfo)){
                            /** already process bypass */
                        }else{
                            if($anchor_for_xml_jobInfo == true){
                                /** we are still scanning for the closing bracket */
                                /**if the line also contains the closing bracket then we know how to handle */
                                if (strpos($line, '</jobInfo>') !== false) {
                                    /** it found the closing breacket */
                                    $anchor_for_xml_jobInfo = null;
                                    $xml_jobInfo_contents .= preg_replace('/\s\s+/', '<br>', ltrim(str_replace("<![CDATA[", "", str_replace("]]></jobInfo>", "", str_replace("<jobInfo>", "", $line)))));
                                }else{
                                    /** it has not found the closing bracket yet */
                                    /** let us check the lines by content */
                                    if(preg_replace('/\s\s+/', '', ltrim($line)) == "\n" || preg_replace('/\s\s+/', '', ltrim($line)) == "\r\n") {
                                        $xml_jobInfo_contents .= '<br>';
                                    }else{
                                        $xml_jobInfo_contents .= preg_replace('/\s\s+/', '', ltrim(str_replace("<![CDATA[", "", str_replace("]]></jobInfo>", "", str_replace("<jobInfo>", "", $line))))) .'<br>';
                                    }
                                }
                            }else{
                                /** we are looking for the opening bracket */
                                if (strpos($line, '<jobInfo>') !== false && strpos($line, '</jobInfo>') !== false) {
                                    /** the line has both the opening and closing bracket */
                                    /** so do not need to configure the anchor variable */
                                    $anchor_for_xml_jobInfo = null;
                                    $xml_jobInfo_contents .= preg_replace('/\s\s+/', '<br>', ltrim(str_replace("<![CDATA[", "", str_replace("]]></jobInfo>", "", str_replace("<jobInfo>", "", $line)))));
                                }else{
                                    /** the closing bracket is on another line */
                                    /** make use of the anchor variable to tell the tool to focus for the closing bracket and keep the formatting of the text blob */
                                    if (strpos($line, '<jobInfo>') !== false) {
                                        $anchor_for_xml_jobInfo = true;
                                        $xml_jobInfo_contents .= preg_replace('/\s\s+/', '<br>', ltrim(str_replace("<![CDATA[", "", str_replace("]]></jobInfo>", "", str_replace("<jobInfo>", "", $line)))));
                                    }
                                }
                            }
                        }


                        if(is_null($anchor_for_xml_jobInfoProduction)){
                            /** already process bypass */
                        }else{
                            if($anchor_for_xml_jobInfoProduction == true){
                                /** we are still scanning for the closing bracket */
                                /**if the line also contains the closing bracket then we know how to handle */
                                if (strpos($line, '</jobInfoProduction>') !== false) {
                                    /** it found the closing breacket */
                                    $anchor_for_xml_jobInfoProduction = null;
                                    $xml_jobInfoProduction_contents .= preg_replace('/\s\s+/', '<br>', ltrim(str_replace("<![CDATA[", "", str_replace("]]></jobInfoProduction>", "", str_replace("<jobInfoProduction>", "", $line)))));
                                }else{
                                    /** it has not found the closing bracket yet */
                                    /** let us check the lines by content */
                                    if(preg_replace('/\s\s+/', '', ltrim($line)) == "\n" || preg_replace('/\s\s+/', '', ltrim($line)) == "\r\n") {
                                        $xml_jobInfoProduction_contents .= '<br>';
                                    }else{
                                        $xml_jobInfoProduction_contents .= preg_replace('/\s\s+/', '', ltrim(str_replace("<![CDATA[", "", str_replace("]]></jobInfoProduction>", "", str_replace("<jobInfoProduction>", "", $line))))) .'<br>';
                                    }
                                }
                            }else{
                                /** we are looking for the opening bracket */
                                if (strpos($line, '<jobInfoProduction>') !== false && strpos($line, '</jobInfoProduction>') !== false) {
                                    /** the line has both the opening and closing bracket */
                                    /** so do not need to configure the anchor variable */
                                    $anchor_for_xml_jobInfoProduction = null;
                                    $xml_jobInfoProduction_contents .= preg_replace('/\s\s+/', '<br>', ltrim(str_replace("<![CDATA[", "", str_replace("]]></jobInfoProduction>", "", str_replace("<jobInfoProduction>", "", $line)))));
                                }else{
                                    /** the closing bracket is on another line */
                                    /** make use of the anchor variable to tell the tool to focus for the closing bracket and keep the formatting of the text blob */
                                    if (strpos($line, '<jobInfoProduction>') !== false) {
                                        $anchor_for_xml_jobInfoProduction = true;
                                        $xml_jobInfoProduction_contents .= preg_replace('/\s\s+/', '<br>', ltrim(str_replace("<![CDATA[", "", str_replace("]]></jobInfoProduction>", "", str_replace("<jobInfoProduction>", "", $line)))));
                                    }
                                }
                            }
                        }
                    }
                    fclose($fropen);
                }

                /**dump($xml_jobInfo_contents);*/
                /**dump($xml_jobInfoProduction_contents);*/
                $task_downloads_file_model->xml_tool_client = $xml_tool_client;
                $task_downloads_file_model->xml_title_contents = $xml_title_contents;
                $task_downloads_file_model->xml_jobid_title = $xml_jobidtitle_contents;
                $task_downloads_file_model->xml_deliverytime_contents = $xml_deliverytime_contents;
                $task_downloads_file_model->xml_jobinfo = $xml_jobInfo_contents;
                $task_downloads_file_model->xml_jobinfoproduction = $xml_jobInfoProduction_contents;
                $task_downloads_file_model->save();
                /**dd($task_downloads_file_model->case_id);*/
            } catch (FileNotFoundException $e) {
                dd($e);
            }
        }else{
            /**dd('xml not downloaded');*/
            $downXmlCmd = "aria2c --allow-overwrite=true --piece-length=100M --max-connection-per-server=8 --file-allocation=none --max-concurrent-downloads=8 --split=8 --log-level=notice --max-tries=12  --retry-wait=15 --dir={$dir} " . '"' . $uriXlm . '"';

            /**dump($downXmlCmd);*/

            $pid = exec($downXmlCmd . " > /dev/null & echo $!;", $output);
            /** after downloading the file we check it to update the case_id */



            /** if the pid is still running then let it keep running as soon as it is not running any more then you can move on to the next step */

            $check_if_pid_still_running = exec("ps aux | awk '{print $1 }' | grep ". $pid);
            if($check_if_pid_still_running == $pid){
                /** it is still running under the same pid.. lucky us, just let it keep going. */
            }else{

            }
        }
    }

    public function populateifnotintaskUploadsgetfromtaskDownloads()
    {
        $all_task_downloads_list = DB::table('tasks_downloads')->select('case_id')->get()->keyBy('case_id');
        foreach($all_task_downloads_list as $download_task_file_id_key => $download_task_details){
            /**dd($download_task_details);*/
            $check_if_exists = DB::table('tasks_uploads')->where('case_id', $download_task_file_id_key)->first();
            if(empty($check_if_exists)){
                $data = [
                    'case_id' => $download_task_file_id_key,
                    'state' => 'downloaded',
                    'try' => 0,
                    'time' => time(),
                    'from' => 0,
                    'has_mapping_name' => NULL,
                    'initiator' => NULL,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'move_to_jobfolder' => 0,
                    'move_to_jobfolder_tries' => 0,
                    'sending_to_s3' => 0,
                    'sending_to_s3_tries' => 0,
                    'pid' => NULL,
                ];
                $this->taskUpload->insert($data);
            }

        }
    }

    /**
     * connect to samba ad with write credentials
     *
     * @author sigmoswitch
     * @return null
     */
    public function connect_to_samba_ad_with_specified_read_or_write_credentials($function_name = '', $whattheysent = [], $read_or_write = null)
    {
        if($read_or_write == 'read'){
            $username = env('LDAP_USERNAME');
            $password = env('LDAP_PASSWORD');
        }else if($read_or_write == 'write'){
            $username = env('LDAP_USERNAME_WRITE');
            $password = env('LDAP_PASSWORD_WRITE');
        }else{
            return [
                "success" => 'false',
                "errors" => ["read_or_write_variable not defined"]
            ];
        }

        $connection = new LdapConnection([
            /**Mandatory Configuration Options*/
            'hosts'            => explode(' ', env('LDAP_HOSTS', 'corp-dc1.corp.acme.org corp-dc2.corp.acme.org')),
            'base_dn'          => env('LDAP_BASE_DN', 'dc=corp,dc=acme,dc=org'),
            'username'         => $username,
            'password'         => $password,

            /**Optional Configuration Options*/
            'port'             => env('LDAP_PORT', 389),
            'use_ssl'          => env('LDAP_USE_SSL', false),
            'use_tls'          => env('LDAP_USE_TLS', false),
            'version'          => 3,
            'timeout'          => env('LDAP_TIMEOUT', 5),
            'follow_referrals' => false,

            /**Custom LDAP Options*/
            'options' => [
                /**See: http://php.net/ldap_set_option*/
                /**LDAP_OPT_X_TLS_REQUIRE_CERT => LDAP_OPT_X_TLS_HARD*/
            ]
        ]);

        if($read_or_write == 'read'){
            if (LdapContainer::getInstance()->exists('default-read')) {
                // The 'default-read' connection exists!
            }else{
                LdapContainer::setDefaultConnection('default-read');
                LdapContainer::addConnection($connection);
            }
        }else if($read_or_write == 'write'){
            if (LdapContainer::getInstance()->exists('default-write')) {
                // The 'default-write' connection exists!
            }else{
                LdapContainer::setDefaultConnection('default-write');
                LdapContainer::addConnection($connection);
            }
        }else{
            return [
                "success" => 'false',
                "errors" => ["read_or_write_variable not defined"]
            ];
        }


        /**dd(LdapContainer::getInstance()->exists('default-write'));*/


        try {
            $connection = LdapContainer::getDefaultConnection();
            /**dd($connection);*/
            $connection->connect();
            return $connection;
        } catch (\LdapRecord\Auth\BindException $e) {
            Debugbar::addException($e);
            $error = $e->getDetailedError();
            Loggy::write('default', json_encode([$function_name, $whattheysent["name"], "error_code = ".$error->getErrorCode(), $error->getErrorMessage(), $error->getDiagnosticMessage()]));
            return [
                "success" => 'false',
                "errors" => $e
            ];
        }
    }


    /**
     * test_bitrix_chat_message_to_group
     *
     * @author sigmoswitch
     * @return null
     */
    public function test_bitrix_chat_message_to_group($case_id = null, $content = '', $xml_title_contents = null,  $xml_jobid_title = null, $destination = null)
    {
        /**Try to make a method for sending a message straight to the bitrix group chat .. with logging to determine where the issue may be reguarding sending to a group.. */
        /**and a way to adjust the query.. */
        $originalDestination = $destination;
        if($destination == 0){
            $destination = config('br24config.rc_notify_usernames');
        }else if($destination == 1){
            $destination = config('br24config.rc_notify_group');
        }else{
            dump('destination not set');
            die();
        }

        /** force without attachment */
        if($content == ' '){
            $content = '';
        }

        $message = array(
            'title' => $case_id,
            'content' => $content,
            'xml_title_contents' => $xml_title_contents,
            'xml_jobid_title' => $xml_jobid_title,
            'link' => null,
            'to' => $destination
        );

        dump('about to send rc/bitrix message' . json_encode($message));

        $messenger_destination = env("MESSENGER_DESTINATION", "ROCKETCHAT");
        dump("messenger_destination", $messenger_destination);
        if($messenger_destination == 'BITRIX'){
            /** BITRIX */
            /***/
            testBTRXsendCreateJobMessage('', $message, $originalDestination);
        }else if($messenger_destination == 'ROCKETCHAT'){
            /** ROCKETCHAT */
            /***/
        }else{
            die();
        }
        dump('rc/bitrix message sent!  cleanup about to be performmed');
    }

}

