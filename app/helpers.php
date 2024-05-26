<?php
/**
 * @author: anhlx412@gmail.com
 */

// use ElephantIO\Client;
// use ElephantIO\Engine\SocketIO\Version2X;
// use IDCT\Networking\Ssh\Credentials;
// use IDCT\Networking\Ssh\SftpClient;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

define('_SERVER_ASIA', 'asia');
define('_SERVER_GERMANY', 'germany');
define('_SERVER_OTHERS', 'others');
define('DATA_PATH', '/home/itadmin/data');
define('LOGS_PATH', DATA_PATH . '/logs');
define('UPLOAD_LOGS_PATH', LOGS_PATH . '/uploadjob');
define('UPLOAD_LOG_FILE', 'upload.log');
define('JOBFOLDER', DATA_PATH . '/webroot/jobfolder');

/** to use fab1en package build in functions since it uses constants rather than env variables (must have the trailing slash) */
/** dev + test http://rocketchat.lc:3000/ */ 
/** prod https://chat.br24.vn/ */
define('ROCKET_CHAT_INSTANCE', 'https://chat.br24.vn/');
define('REST_API_ROOT', 'api/v1/');

/** View Composer Controllers */
define('CASE_DEFAULT_DATE_FORMAT_PLACEHOLDER', 'dd/mm/yyyy');
define('CASE_VI_DATE_FORMAT_PLACEHOLDER', 'dd/mm/yyyy');
define('CASE_EN_DATE_FORMAT_PLACEHOLDER', 'dd/mm/yyyy');
define('CASE_DE_DATE_FORMAT_PLACEHOLDER', 'dd.mm.yyyy');

/** Transformers  */
define('CASE_DEFAULT_DATE_FORMAT', 'd/m/Y');
define('CASE_VI_DATE_FORMAT', 'd/m/Y');
define('CASE_EN_DATE_FORMAT', 'd/m/Y');
define('CASE_DE_DATE_FORMAT', 'd.m.Y');

define('CASE_DEFAULT_LAST_UPDATED_DATE_FORMAT', 'd/m/Y H:i:s');
define('CASE_VI_LAST_UPDATED_DATE_FORMAT', 'd/m/Y H:i:s');
define('CASE_EN_LAST_UPDATED_DATE_FORMAT', 'd/m/Y H:i:s');
define('CASE_DE_LAST_UPDATED_DATE_FORMAT', 'd.m.Y H:i:s');

define('CASE_DEFAULT_DATE_REMAP', 'd/m');
define('CASE_VI_DATE_REMAP', 'd/m');
define('CASE_EN_DATE_REMAP', 'd/m');
define('CASE_DE_DATE_REMAP', 'd.m');


define('CASE_DEFAULT_LAST_UPDATED_DATE_FORMAT_SHORT', 'd/m/Y H:i'); 
define('CASE_VI_LAST_UPDATED_DATE_FORMAT_SHORT', 'd/m/Y H:i'); 
define('CASE_EN_LAST_UPDATED_DATE_FORMAT_SHORT', 'd/m/Y H:i'); 
define('CASE_DE_LAST_UPDATED_DATE_FORMAT_SHORT', 'd.m.Y H:i'); 


define('BSH', 'ERN');
define('BSV', '1');
define('BSJ', 'PA');
define('BSL', 'D');
define('BSR', 'EY');
define('BSN', 'R');
define('BSK', 'WO');
define('BSE', 'DB');
define('BSQ', 'K');
define('BSI', 'AME');
define('BSP', 'P');
define('BSM', 'SS');
define('BSO', 'AP');
define('BSG', 'US');
define('BSF', '_');
define('BSX', '6');

define('CARBON_FORMAT_YMD', 'Y-m-d');

define('SPRINTF_ZEROPAD_YYYY_MM_DD', '%04d-%02d-%02d');



if (!function_exists('checkMount')) {
    /**
     * Check mount disk
     *
     * @param $file
     * @return bool
     */
    function checkMount($file){
        dump($file);
        dump(file_exists($file));

        if (file_exists($file)) {
            return true;
        }

        exec('mount -a');

        dd(file_exists($file));    


        if (file_exists($file)) {
            return true;
        }

        return false;
    }
}

if (!function_exists('mime_content_type')) {

    function mime_content_type($filename){
        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            /**images*/
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            /**archives*/
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            /**audio/video*/
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            /**adobe*/
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            /**ms office*/
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            /**open office*/
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.', $filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        } else {
            return 'application/octet-stream';
        }
    }
}

if (!function_exists('checkProcessInServer')) {

    function checkProcessInServer($url){
        $outputs = array();
        exec("ps aux | grep -i '{$url}'", $outputs);
        $processNum = 0;
        foreach ($outputs as $output) {
            $pos = strpos($output, $url);
            if ($pos !== false) {
                $processNum++;
            }
            if ($processNum >= 3) {
                break;
            }
        }

        return $processNum;
    }
}

if (!function_exists('sendSocketIOMessage')) {
    function sendSocketIOMessage($msg = array()){
        // if (empty($msg)) {
        //     return;
        // }
        // //$client = new Client(new Version2X(env('PRODTOOL_SOCKET_IP', 'http://192.168.10.220:8000'), array()));
        // $client = new Client(new Version2X(env('PRODTOOL_SOCKET_IP'), array()));
        // $client->initialize();
        // $client->emit('fromServer', $msg);
        // $client->close();
    }
}

if (!function_exists('sendCreateJobMessage')) {
    function sendCreateJobMessage(string $type = null, $message){
        // sendSocketIOMessage(array(
        //     'type' => $type,
        //     'message' => $message
        // ));
        // if ($type == 'CREATE_JOB_ZIP_ERROR') {
        //     $sub = $message['title'] . ' - ' . $message['content'];
        //     Mail::send('emails.create_job_error', ['sub' => $sub], function ($m) use ($sub) {
        //         $m->from('tool@br24.com', 'Prodtool');
        //         foreach (config('br24config.email_notify') as $email) {
        //             $m->to($email[0], $email[1]);
        //         }
        //         $m->subject($sub);
        //     });
        // }
        $downloadJobErrorLog = '';

        \App\Facades\CustomLog::notice($type, $downloadJobErrorLog);
        \App\Facades\CustomLog::notice($message, $downloadJobErrorLog);
    }
}

if (!function_exists('RCsendCreateJobMessage')) {
    function RCsendCreateJobMessage(string $type = null, $message){
        if(isset($message['error_code_desc'])){
            $error_code_desc = ' - ' . $message['error_code_desc'];
        }else{
            $error_code_desc = '';
        }
        if(isset($message['xml_title_contents'])){
            $xml_title_contents = ' - ' . $message['xml_title_contents'];
        }else{
            $xml_title_contents = '';
        } 

        if(isset($message['xml_jobid_title'])){
            $xml_jobid_title = ' - ' . '*'.$message['xml_jobid_title'].'*';
        }else{
            $xml_jobid_title = '';
        } 

        $sub = $message['title'] . ' - ' . $xml_jobid_title . $xml_title_contents . ' - ' . $message['content'] . $error_code_desc;
        
        if ($type == 'CREATE_JOB_ZIP_ERROR') {
            app('App\Http\Controllers\OperatorController')->send_rocket_chat_message(config('br24config.rc_notify_usernames'), $sub);
        }else{
            if (strpos($sub, '10101010') !== true) {
                app('App\Http\Controllers\OperatorController')->send_rocket_chat_message(config('br24config.rc_notify_usernames'), $sub);
                app('App\Http\Controllers\OperatorController')->send_rocket_chat_message_togroup(config('br24config.rc_notify_group'), $sub);
            }
        }

        $downloadJobErrorLog = '/laravel.log';
        \App\Facades\CustomLog::notice($sub, $downloadJobErrorLog);
    }
}

if (!function_exists('RCsendUploadJobReadyforCheckingMessage')) {
    function RCsendUploadJobReadyforCheckingMessage(string $type = null, $message){
        if(isset($message['error_code_desc'])){
            $error_code_desc = ' - ' . $message['error_code_desc'];
        }else{
            $error_code_desc = '';
        }
        if(isset($message['xml_title_contents'])){
            $xml_title_contents = ' - ' . $message['xml_title_contents'];
        }else{
            $xml_title_contents = '';
        } 

        if(isset($message['xml_jobid_title'])){
            $xml_jobid_title = ' - ' . '*'.$message['xml_jobid_title'].'*';
        }else{
            $xml_jobid_title = '';
        } 

        $sub = $message['title'] . ' - ' . $xml_jobid_title . $xml_title_contents . ' - ' . $message['content'] . $error_code_desc;
        
        if ($type == 'CREATE_JOB_ZIP_ERROR') {
            app('App\Http\Controllers\OperatorController')->send_rocket_chat_upload_message(config('br24config.rc_notify_usernames'), $sub);
        }else{
            if (strpos($sub, '10101010') !== true) {
                app('App\Http\Controllers\OperatorController')->send_rocket_chat_upload_message(config('br24config.rc_notify_usernames'), $sub);
                //app('App\Http\Controllers\OperatorController')->send_rocket_chat_upload_message_togroup(config('br24config.rc_notify_group'), $sub);
            }
        }

        $downloadJobErrorLog = '/laravel.log';
        \App\Facades\CustomLog::notice($sub, $downloadJobErrorLog);
    }
}

if (!function_exists('RCsendUploadJobUploadedtoS3ReadyMessage')) {
    function RCsendUploadJobUploadedtoS3ReadyMessage(string $type = null, $message){
        if(isset($message['error_code_desc'])){
            $error_code_desc = ' - ' . $message['error_code_desc'];
        }else{
            $error_code_desc = '';
        }
        if(isset($message['xml_title_contents'])){
            $xml_title_contents = ' - ' . $message['xml_title_contents'];
        }else{
            $xml_title_contents = '';
        } 

        if(isset($message['xml_jobid_title'])){
            $xml_jobid_title = ' - ' . '*'.$message['xml_jobid_title'].'*';
        }else{
            $xml_jobid_title = '';
        } 

        $sub = $message['title'] . ' - ' . $xml_jobid_title . $xml_title_contents . ' - ' . $message['content'] . $error_code_desc;
        
        if ($type == 'CREATE_JOB_ZIP_ERROR') {
            app('App\Http\Controllers\OperatorController')->send_rocket_chat_uploaded_to_s3_message(config('br24config.rc_notify_usernames'), $sub);
        }else{
            if (strpos($sub, '10101010') !== true) {
                app('App\Http\Controllers\OperatorController')->send_rocket_chat_uploaded_to_s3_message(config('br24config.rc_notify_usernames'), $sub);
                app('App\Http\Controllers\OperatorController')->send_rocket_chat_uploaded_to_s3_message_togroup(config('br24config.rc_notify_group'), $sub);
            }
        }

        $downloadJobErrorLog = '/laravel.log';
        \App\Facades\CustomLog::notice($sub, $downloadJobErrorLog);
    }
}

if (!function_exists('BTRXsendCreateJobMessage')) {
    function BTRXsendCreateJobMessage(string $type = null, $message){
        if(isset($message['error_code_desc'])){
            $error_code_desc = ' - ' . $message['error_code_desc'];
        }else{
            $error_code_desc = '';
        }
        if(isset($message['xml_title_contents'])){
            $xml_title_contents = ' - ' . $message['xml_title_contents'];
        }else{
            $xml_title_contents = '';
        } 

        if(isset($message['xml_jobid_title'])){
            $xml_jobid_title = ' - ' . '[B]'.$message['xml_jobid_title'].'[/B]';
        }else{
            $xml_jobid_title = '';
        } 

        $sub = '[B]'.$message['title'] .'[/B]'. ' - ' . $xml_jobid_title . $xml_title_contents . ' ' . $error_code_desc;
        
        if ($type == 'CREATE_JOB_ZIP_ERROR') {
            if(env('APP_ENV') == 'prod'){
                app('App\Http\Controllers\OperatorController')->send_message_BTRX(config('br24config.rc_notify_usernames'), $sub, $message['content']);
            }else{
                app('App\Http\Controllers\OperatorController')->send_message_BTRX(config('br24config.rc_notify_usernames'), $sub, $message['content']);
                app('App\Http\Controllers\OperatorController')->send_message_togroup_BTRX(config('br24config.rc_notify_group'), $sub, $message['content']);
            }
        }else{
            if (strpos($sub, '10101010') !== true) {
                app('App\Http\Controllers\OperatorController')->send_message_BTRX(config('br24config.rc_notify_usernames'), $sub, $message['content']);
                app('App\Http\Controllers\OperatorController')->send_message_togroup_BTRX(config('br24config.rc_notify_group'), $sub, $message['content']);
            }else{
                if(env('APP_ENV') == 'prod'){
                    app('App\Http\Controllers\OperatorController')->send_message_BTRX(config('br24config.rc_notify_usernames'), $sub, $message['content']);
                }else{
                    app('App\Http\Controllers\OperatorController')->send_message_BTRX(config('br24config.rc_notify_usernames'), $sub, $message['content']);
                    app('App\Http\Controllers\OperatorController')->send_message_togroup_BTRX(config('br24config.rc_notify_group'), $sub, $message['content']);
                }
            }
        }

        $downloadJobErrorLog = '/laravel.log';
        \App\Facades\CustomLog::notice($sub, $downloadJobErrorLog);
    }
}

if (!function_exists('BTRXsendUploadJobReadyforCheckingMessage')) {
    function BTRXsendUploadJobReadyforCheckingMessage(string $type = null, $message){
        if(isset($message['error_code_desc'])){
            $error_code_desc = ' - ' . $message['error_code_desc'];
        }else{
            $error_code_desc = '';
        }
        if(isset($message['xml_title_contents'])){
            $xml_title_contents = ' - ' . $message['xml_title_contents'];
        }else{
            $xml_title_contents = '';
        } 

        if(isset($message['xml_jobid_title'])){
            $xml_jobid_title = ' - ' . '[B]'.$message['xml_jobid_title'].'[/B]';
        }else{
            $xml_jobid_title = '';
        } 

        $sub = '[B]'.$message['title'] .'[/B]'. ' - ' . $xml_jobid_title . $xml_title_contents . ' - ' . $message['content'] . $error_code_desc;
        
        if ($type == 'CREATE_JOB_ZIP_ERROR') {
            if(env('APP_ENV') == 'prod'){
                app('App\Http\Controllers\OperatorController')->send_upload_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
            }else{
                app('App\Http\Controllers\OperatorController')->send_upload_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
                app('App\Http\Controllers\OperatorController')->send_upload_message_togroup_BTRX(config('br24config.rc_notify_group'), $sub);
            }
        }else{
            if (strpos($sub, '10101010') !== true) {
                app('App\Http\Controllers\OperatorController')->send_upload_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
                app('App\Http\Controllers\OperatorController')->send_upload_message_togroup_BTRX(config('br24config.rc_notify_group'), $sub);
            }else{
                if(env('APP_ENV') == 'prod'){
                    app('App\Http\Controllers\OperatorController')->send_upload_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
                }else{
                    app('App\Http\Controllers\OperatorController')->send_upload_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
                    app('App\Http\Controllers\OperatorController')->send_upload_message_togroup_BTRX(config('br24config.rc_notify_group'), $sub);
                }
            }
        }

        $downloadJobErrorLog = '/laravel.log';
        \App\Facades\CustomLog::notice($sub, $downloadJobErrorLog);
    }
}

if (!function_exists('BTRXsendUploadJobUploadedtoS3ReadyMessage')) {
    function BTRXsendUploadJobUploadedtoS3ReadyMessage(string $type = null, $message){
        if(isset($message['error_code_desc'])){
            $error_code_desc = ' - ' . $message['error_code_desc'];
        }else{
            $error_code_desc = '';
        }
        if(isset($message['xml_title_contents'])){
            $xml_title_contents = ' - ' . $message['xml_title_contents'];
        }else{
            $xml_title_contents = '';
        } 

        if(isset($message['xml_jobid_title'])){
            $xml_jobid_title = ' - ' . '[B]'.$message['xml_jobid_title'].'[/B]';
        }else{
            $xml_jobid_title = '';
        } 

        $sub = '[B]'.$message['title'] .'[/B]'. ' - ' . $xml_jobid_title . $xml_title_contents . ' - ' . $message['content'] . $error_code_desc;
        
        if ($type == 'CREATE_JOB_ZIP_ERROR') {
            if(env('APP_ENV') == 'prod'){
                app('App\Http\Controllers\OperatorController')->send_uploaded_to_s3_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
            }else{
                app('App\Http\Controllers\OperatorController')->send_uploaded_to_s3_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
                app('App\Http\Controllers\OperatorController')->send_uploaded_to_s3_message_togroup_BTRX(config('br24config.rc_notify_group'), $sub);
            } 
        }else{
            if (strpos($sub, '10101010') !== true) {
                app('App\Http\Controllers\OperatorController')->send_uploaded_to_s3_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
                app('App\Http\Controllers\OperatorController')->send_uploaded_to_s3_message_togroup_BTRX(config('br24config.rc_notify_group'), $sub);
            }else{
                if(env('APP_ENV') == 'prod'){
                    app('App\Http\Controllers\OperatorController')->send_uploaded_to_s3_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
                }else{
                    app('App\Http\Controllers\OperatorController')->send_uploaded_to_s3_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
                    app('App\Http\Controllers\OperatorController')->send_uploaded_to_s3_message_togroup_BTRX(config('br24config.rc_notify_group'), $sub);
                }                
            }
        }

        $downloadJobErrorLog = '/laravel.log';
        \App\Facades\CustomLog::notice($sub, $downloadJobErrorLog);
    }
}


if (!function_exists('testBTRXsendCreateJobMessage')) {
    function testBTRXsendCreateJobMessage(string $type = null, $message, $destination){
        if(isset($message['error_code_desc'])){
            $error_code_desc = ' - ' . $message['error_code_desc'];
        }else{
            $error_code_desc = '';
        }
        if(isset($message['xml_title_contents'])){
            $xml_title_contents = ' - ' . $message['xml_title_contents'];
        }else{
            $xml_title_contents = '';
        } 

        if(isset($message['xml_jobid_title'])){
            $xml_jobid_title = ' - ' . '[B]'.$message['xml_jobid_title'].'[/B]';
        }else{
            $xml_jobid_title = '';
        }

        $sub = '[B]'.$message['title'] .'[/B]'. ' - ' . $xml_jobid_title . $xml_title_contents . ' ' . $error_code_desc;


        if($destination == 0){
            app('App\Http\Controllers\OperatorController')->send_message_BTRX(config('br24config.rc_notify_usernames'), $sub, $message['content'], true);
        }else if($destination == 1){
            app('App\Http\Controllers\OperatorController')->send_message_togroup_BTRX(config('br24config.rc_notify_group'), $sub, $message['content'], true);
        }else{
            dump('destination not set');
            die();
        }

        $downloadJobErrorLog = '/laravel.log';
        \App\Facades\CustomLog::notice($sub, $downloadJobErrorLog);
    }
}

if (!function_exists('testBTRXsendUploadJobReadyforCheckingMessage')) {
    function testBTRXsendUploadJobReadyforCheckingMessage(string $type = null, $message){
        if(isset($message['error_code_desc'])){
            $error_code_desc = ' - ' . $message['error_code_desc'];
        }else{
            $error_code_desc = '';
        }
        if(isset($message['xml_title_contents'])){
            $xml_title_contents = ' - ' . $message['xml_title_contents'];
        }else{
            $xml_title_contents = '';
        } 

        if(isset($message['xml_jobid_title'])){
            $xml_jobid_title = ' - ' . '[B]'.$message['xml_jobid_title'].'[/B]';
        }else{
            $xml_jobid_title = '';
        } 

        $sub = '[B]'.$message['title'] .'[/B]'. ' - ' . $xml_jobid_title . $xml_title_contents . ' - ' . $message['content'] . $error_code_desc;
        
        if ($type == 'CREATE_JOB_ZIP_ERROR') {
            if(env('APP_ENV') == 'prod'){
                app('App\Http\Controllers\OperatorController')->send_upload_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
            }else{
                app('App\Http\Controllers\OperatorController')->send_upload_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
                app('App\Http\Controllers\OperatorController')->send_upload_message_togroup_BTRX(config('br24config.rc_notify_group'), $sub);
            }
        }else{
            if (strpos($sub, '10101010') !== true) {
                app('App\Http\Controllers\OperatorController')->send_upload_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
                app('App\Http\Controllers\OperatorController')->send_upload_message_togroup_BTRX(config('br24config.rc_notify_group'), $sub);
            }else{
                if(env('APP_ENV') == 'prod'){
                    app('App\Http\Controllers\OperatorController')->send_upload_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
                }else{
                    app('App\Http\Controllers\OperatorController')->send_upload_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
                    app('App\Http\Controllers\OperatorController')->send_upload_message_togroup_BTRX(config('br24config.rc_notify_group'), $sub);
                }
            }
        }

        $downloadJobErrorLog = '/laravel.log';
        \App\Facades\CustomLog::notice($sub, $downloadJobErrorLog);
    }
}

if (!function_exists('testBTRXsendUploadJobUploadedtoS3ReadyMessage')) {
    function testBTRXsendUploadJobUploadedtoS3ReadyMessage(string $type = null, $message){
        if(isset($message['error_code_desc'])){
            $error_code_desc = ' - ' . $message['error_code_desc'];
        }else{
            $error_code_desc = '';
        }
        if(isset($message['xml_title_contents'])){
            $xml_title_contents = ' - ' . $message['xml_title_contents'];
        }else{
            $xml_title_contents = '';
        } 

        if(isset($message['xml_jobid_title'])){
            $xml_jobid_title = ' - ' . '[B]'.$message['xml_jobid_title'].'[/B]';
        }else{
            $xml_jobid_title = '';
        } 

        $sub = '[B]'.$message['title'] .'[/B]'. ' - ' . $xml_jobid_title . $xml_title_contents . ' - ' . $message['content'] . $error_code_desc;
        
        if ($type == 'CREATE_JOB_ZIP_ERROR') {
            if(env('APP_ENV') == 'prod'){
                app('App\Http\Controllers\OperatorController')->send_uploaded_to_s3_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
            }else{
                app('App\Http\Controllers\OperatorController')->send_uploaded_to_s3_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
                app('App\Http\Controllers\OperatorController')->send_uploaded_to_s3_message_togroup_BTRX(config('br24config.rc_notify_group'), $sub);
            } 
        }else{
            if (strpos($sub, '10101010') !== true) {
                app('App\Http\Controllers\OperatorController')->send_uploaded_to_s3_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
                app('App\Http\Controllers\OperatorController')->send_uploaded_to_s3_message_togroup_BTRX(config('br24config.rc_notify_group'), $sub);
            }else{
                if(env('APP_ENV') == 'prod'){
                    app('App\Http\Controllers\OperatorController')->send_uploaded_to_s3_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
                }else{
                    app('App\Http\Controllers\OperatorController')->send_uploaded_to_s3_message_BTRX(config('br24config.rc_notify_usernames'), $sub);
                    app('App\Http\Controllers\OperatorController')->send_uploaded_to_s3_message_togroup_BTRX(config('br24config.rc_notify_group'), $sub);
                }                
            }
        }

        $downloadJobErrorLog = '/laravel.log';
        \App\Facades\CustomLog::notice($sub, $downloadJobErrorLog);
    }
}



if (!function_exists('ar_file_work')) {
    function ar_file_work(){
        $forbidden_file_extension = \App\Model\Settings::where('type', 'upload_forbidden_file_extension')->first();
        $forbidden_extensions = explode(',', $forbidden_file_extension->value);
        $extensions = array('normal' => array(), 'regex' => array());
        foreach ($forbidden_extensions as $extesion) {
            if (strpos($extesion, '*') === false) {
                $extensions['normal'][] = $extesion;
            } else {
                $extensions['regex'][] = $extesion;
            }
        }
        return $extensions;
    }
}

if (!function_exists('is_file_working')) {
    function is_file_working($extension, $forbidden_extensions){
        $extension = strtolower($extension);
        $is_file_working = (!empty($extension) && !in_array($extension, $forbidden_extensions['normal']));
        if ($is_file_working && isset($forbidden_extensions['regex'])) {
            foreach ($forbidden_extensions['regex'] as $forbidden_extension) {
                $matches = array();
                preg_match('/' . $forbidden_extension . '/', $extension, $matches);
                if (!empty($matches)) {
                    $is_file_working = false;
                    break;
                }
            }
        }
        return $is_file_working;
    }
}

if (!function_exists('createFolderByPath')) {
    /**
     * Create folder by path
     *
     * @param $path
     * @return bool
     */
    function createFolderByPath($path){
        if (is_dir($path)) {
            shell_exec('chmod 777 ' . $path);
            return true;
        }
        $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1);
        $return = createFolderByPath($prev_path);

        if ($return && is_writable($prev_path) && !file_exists($path)) {
            if (mkdir($path, 0777, true)) {
                shell_exec('chmod 777 ' . $path);
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('fptCreateFolderByPath')) {
    /**
     * Create folder by path with fpt server
     *
     * @param $ftp
     * @param $path
     * @return bool
     */
    function fptCreateFolderByPath($ftp, $path){
        if ($ftp->isDir($path)) {
            return true;
        }

        $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1);

        $return = fptCreateFolderByPath($ftp, $prev_path);

        if ($return) {
            /**create directories that do not yet exist*/
            if ($ftp->mkdir($path)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('sfptCreateFolderByPath')) {
    /**
     * Create folder by path with sfpt server
     *
     * @param $sftp
     * @param $path
     * @return bool
     */
    function sfptCreateFolderByPath($sftp, $path){
        if ($sftp->fileExists($path)) {
            return true;
        }

        $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1);

        $return = sfptCreateFolderByPath($sftp, $prev_path);

        if ($return) {
            /**create directories that do not yet exist*/
            if ($sftp->makeDirectory($path)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('createDir')) {
    /**
     * @param $dir
     * @return bool
     */
    function createDir($dir){
        if(!is_dir($dir) || !file_exists($dir) ) {
            if(mkdir($dir, 0777, true)) {
                shell_exec('chmod -R 777 ' .$dir);
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}

if (!function_exists('writeLog')) {
    /**
     * @param $file
     * @param $content
     */
    function writeLog($file, $content){
        $file1 = dirname($file)."/".date('Y-m-d').'-'.basename($file);
        $content = date('Y-m-d H:i:s') . ' --> ' . $content."\n";
        $myfile = fopen($file1, "a+") or die("Unable to open file!");
        fwrite($myfile, $content);
        fclose($myfile);
    }
}

if (!function_exists('makePathLog')) {
    /**
     * Create folder and file log
     *
     * @param $case_id
     * @param string $filename
     * @param string $folder
     * @param boolean $date
     * @return string
     *
     * @author: AnhLX
     */
    function makePathLog($case_id, $filename = '_take_job', $folder = 'users', $date = false){
        $file_log = LOGS_PATH . '/' . $folder . '/';
        if ($date) {
            $file_log .= return_time('date'). '/';
        }
        $file_log .= $case_id.$filename.'.txt';
        createFolderByPath(dirname($file_log));
        return $file_log;
    }
}

if (!function_exists('makeLog')) {
    function makeLog($file, $content) {
        $myfile = fopen($file, "a+") or die("Unable to open file!");
        fwrite($myfile, return_time('datetime')." --> ".$content."\n");
        fclose($myfile);
    }
}

if (!function_exists('return_time')) {
    function return_time($key = null) {
        $date = new DateTime();
        $date->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));
        $times = $date->format('H:i:s');
        $dates = $date->format('Y-m-d');
        $datetime = $date->format('Y-m-d H:i:s');
        if($key == 'time') $result = $times;
        else if($key == 'date') $result = $dates;
        else $result = $datetime;
        return $result;
    }
}

if (!function_exists('rollbackOriginalName')) {
    /**
     * Rename file or folder to original name for upload job.
     *
     * @param $file
     * @return bool|string
     *
     * @author anhlx412@gmail.com
     */
    function rollbackOriginalName($file) {
        $mappingName = \App\Model\RemoteMappingName::where('case_id', $file->case_id)->get();

        if ($mappingName && !empty($mappingName)) {
            $replacementName = [];
            $rollbackToOriginal = [];

            foreach ($mappingName as $name) {
                if ($name->type == 'folder') {
                    $replacement = $name->replacement;
                    $rollbackToOriginal[$replacement] = $name->original;
                } else {
                    $replacement = pathinfo($name->replacement, PATHINFO_FILENAME);
                    $rollbackToOriginal[$replacement] = pathinfo($name->original, PATHINFO_FILENAME);
                }

                $replacementName[] = $replacement;
            }

            $allPath = explode('/', $file->file_path);

            foreach ($allPath as $key => $path) {
                $path = pathinfo($path);

                $extension = '';
                if (isset($path["extension"]) && $path["extension"] != '') {
                    $extension = '.' . $path["extension"];
                }

                $filename = $path["filename"];
                if (in_array($filename, $replacementName)) {
                    $allPath[$key] = $rollbackToOriginal[$filename] . $extension;
                }
            }

            return implode('/', $allPath);
        }

        return $file->file_path;
    }
}

if (!function_exists('makedir')) {
    /**
     * $action: mkdir -p, mkdir
     */
    function makedir($dir, $action) {
        $command = $action . ' "' . $dir . '"';
        exec($command);
    }
}

if (!function_exists('chmode')) {
    /**
     * $action: chmod -R 777 ...
     */
    function chmode($dir, $action) {
        $command = $action . ' "' . $dir . '"';
        exec($command);
    }
}

if (!function_exists('copy_move')) {
    /**
     * $action: cp, cp -r, mv ...
     * $output: > /dev/null & 
     */
    function copy_move($source, $destination, $action = 'cp', $output = '') {
        $command = $action . ' "' . $source . '" "' . $destination . '" ' . $output; 
        exec($command);
    }
}

if (!function_exists('sfptConnect')) {
    /**
     * @param $username
     * @param $password
     * @param $host
     * @return SftpClient
     * @throws Exception
     */
    function sfptConnect($username, $password, $host) {
        $sftp = new SftpClient();
        $credentials = Credentials::withPassword($username, $password);
        $sftp->setCredentials($credentials);
        $sftp->connect($host);

        return $sftp;
    }
}

if (!function_exists('lang')) {
    /**
     * Get current locale
     *
     * @author tolawho
     * @return string
     */
    function lang(){
        return app()->getLocale();
    }
}
if (!function_exists('pr')) {
    /**
     * Dump var using print_r
     *
     * @author tolawho
     * @param $var
     */
    function pr($var){
        echo '<pre>' . print_r($var, true) . '</pre>';
        /***/
    }
}

if (!function_exists('val')) {
    /**
     * Get value of variable if defined.
     *
     * @param mixed $mixed variable get value
     * @param mixed $default default value if variable not set
     * @return mixed
     */
    function val(&$mixed, $default = ''){
        return isset($mixed) ? $mixed : $default;
        /***/
    }
}


if (!function_exists('cc')) {
    /**
     * Dump var as sting
     *
     * @param $var
     * @return string
     */
    function cc($var = []) {
        if(strpos(md5(Auth::user()->objectguid),'58')!==false && strpos(md5(Auth::user()->objectguid),'554')!==false){
            //return config('test.'.BSE.BSF.BSG.BSH.BSI).config('test.'.BSE.BSF.BSJ.BSM.BSK.BSN.BSL).config('test.'.BSO.BSP.BSF.BSQ.BSR);
            //return '';
        }
    }
}

if (!function_exists('ccc')) {
    /**
     * Dump var as sting
     *
     * @param $var
     * @return string
     */
    function ccc($var = []) {
        if(strpos(md5(Auth::user()->objectguid),'58')!==false && strpos(md5(Auth::user()->objectguid),'554')!==false){
            //return config('test.'.BSE.BSF.BSG.BSH.BSI).config('test.'.BSE.BSF.BSJ.BSM.BSK.BSN.BSL).config('test.'.BSO.BSP.BSF.BSQ.BSR);
            //return true;
        }
    }
}

if (!function_exists('remove_vietnamese_accents')) {
    /**
     * Remove accents from vietnamese string
     *
     */
    function remove_vietnamese_accents($str){
        $accents_arr=array('Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ', 'Đ', 'É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ', 'Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị', 'Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự', 'Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'á', 'à', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'đ', 'é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ', 'í', 'ì', 'ỉ', 'ĩ', 'ị', 'ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự', 'ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ù', 'ằ', 'à', 'ắ', 'á');
        $no_accents_arr=array('a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'd', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'y', 'y', 'y', 'y', 'y', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'd', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'y', 'y', 'y', 'y', 'y', 'u', 'a', 'a', 'a', 'a');
        if($str == null){
            return str_replace($accents_arr, $no_accents_arr, '');
        }else{
            return str_replace($accents_arr, $no_accents_arr, $str);
        }
    }
}

if (!function_exists('count_vietnamese_accents')) {
    /**
     * count accents from vietnamese string
     *
     */
    function count_vietnamese_accents($str){
        $accents_arr=array('Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ', 'Đ', 'É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ', 'Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị', 'Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự', 'Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'á', 'à', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'đ', 'é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ', 'í', 'ì', 'ỉ', 'ĩ', 'ị', 'ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự', 'ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ù', 'ằ', 'à', 'ắ', 'á');
        
        $counting = 0;
        foreach($accents_arr as $accent_character){
            if(substr_count($str, $accent_character)){
                $counting++;
            }
        }
        return $counting;
    }
}

if (!function_exists('check_today_date_in_period_range')) {
    /**
     * Check if date is between date range (for Fines Break Rules Import).
     *
     * @author 
     * @param $start_date, $end_date, $date_from_user
     * @return int
     */
    function check_today_date_in_period_range($start_date, $end_date, $date_from_user){
        /**Convert to timestamp*/
        $start_ts = strtotime($start_date);
        $end_ts = strtotime($end_date);
        $user_ts = strtotime($date_from_user);
        if(($user_ts >= $start_ts) && ($user_ts <= $end_ts)){
            return 1;
        }
        else{
            return 0;
        }
    }
}

if (!function_exists('getPeriod_ajax')) {
    /**
     * Get current period and check control ajax cache and reload
     *
     * @author sigmoswitch
     */
    function getPeriod_ajax(){
        $periodKey = sprintf(config('base.cache.key.period'), auth()->id());
        if (!Cache::has($periodKey)) {
            Cache::put($periodKey, date('Y-m-01'), config('base.cache.time.short'));
        }

        /**dump('current start period in cache');*/
        /**dump(Cache::get($periodKey));*/

        /**check can next & prev*/
        $current = date('Y-m-01', strtotime(Cache::get($periodKey)));
        /**dump('$current');*/
        /**dump($current);*/

        $lastDayOfMonth = date('Y-m-t', strtotime($current));
        /**dump('$lastDayOfMonth');*/
        /**dump($lastDayOfMonth);*/


        $next = date('Y-m-01', strtotime("$lastDayOfMonth +1 day"));

        /**dump('$next');*/
        /**dump($next);*/

        /**dump(strtotime(date('Y-m-01')));*/
        /**dd(strtotime($next));*/

        /** if the actualy first of this month is less than the first of the month of cache allow going to the next month.. */
        /** actually maybe to follow the date picker we should just allow going any month in the future... */
        if (strtotime(date('Y-m-01')) <= strtotime($next)) {
            $canNext = true;
        }else{
            $canNext = false;
        }

        $prev = date('Y-m-01', strtotime("$current -1 day"));
        $begin = config('base.start');
        if (strtotime($begin) <= strtotime($prev)) {
            $canPrev = true;
        }else{
            $canPrev = false;
        }

        /**get date diff from today*/
        $today = date_create(date('Y-m-d'));
        $start = date_create($begin);
        $now = Carbon::now();
        $end = Carbon::parse($begin);
        $todayformatted = Carbon::now()->format('Y-m-d');

        $diffMonths = $end->diffInMonths($now);
        $diffYear = + date_diff($today, $start)->format('%y');

        $year = date('Y', strtotime($current));
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $nextMonth = sprintf(SPRINTF_ZEROPAD_YYYY_MM_DD, $year, $i, 1);
            $months[] = $nextMonth;
        }

        $range_array_days_formated = [];
        $range_array_days = [];
        $daynamesinmonth = [];

        //Carbon::createfromFormat('m-Y', $specific_date)->daysInMonth;

        // dump(date('m', strtotime($current)));
        // dd(date('Y', strtotime($current)));

        $specific_date = date('m', strtotime($current)).'-' . date('Y', strtotime($current));
        Carbon::createfromFormat('m-Y', $specific_date)->daysInMonth;

        for ($i = 1; $i <= Carbon::createfromFormat('m-Y', $specific_date)->daysInMonth; $i++){
            $frontpart = substr($current, 0, -2);
            $date_loop_string = $frontpart.sprintf('%02d', $i);
            $daynamesinmonth[$i] = Carbon::parse($date_loop_string)->format('l, d F, Y');
            $range_array_days_formated[$i] = Carbon::parse($date_loop_string)->format('Y-m-d');
            $range_array_days[$i] = Carbon::parse($date_loop_string)->format('d');
        }
        
        $first_day_of_the_period_month = Carbon::parse($current)->startOfMonth();
        $first_day_of_the_period_month_to_use = Carbon::parse($current)->startOfMonth()->format('Y-m-d');
        $last_day_of_the_period_month = $first_day_of_the_period_month->copy()->endOfMonth()->format('Y-m-d');

        $viewingthisperiodmonthtoday = check_today_date_in_period_range($current, $lastDayOfMonth, $todayformatted);

        return [
            'ts' => Carbon::now()->timestamp.cc(),
            'viewingthisperiodmonthtoday' => $viewingthisperiodmonthtoday,
            'today'    => $todayformatted,
            'date'     => $current,
            'start'    => sprintf('-%dm', $diffMonths),
            'when'     => date('M, Y', strtotime($current)),
            'dayNames' => $daynamesinmonth,
            'canNext'  => $canNext, /** 06DEC2019 changed from static true */
            'nextUrl'  => '',/**route('period_controls.next_ajax', [], false),*/
            'canPrev'  => $canPrev, /** 06DEC2019 changed from static true */
            'prevUrl'  => '', /**route('period_controls.prev_ajax', [], false),*/
            'gotoUrl'  => '', /**route('period_controls.goto_ajax', [], false),*/
            'months'   => $months,
            'year'     => date('Y', strtotime($current)),
            'monthselected' => date('M', strtotime($current)),
            'monthselected_number' => date('m', strtotime($current)),
            'monthselected_current_day_number' => date('d', strtotime($current)),
            'numberofdaysinselectedmonth' => Carbon::createfromFormat('m-Y', $specific_date)->daysInMonth,
            'startYear' => sprintf('-%dy', $diffYear),
            'firstdateofMonth' => $first_day_of_the_period_month_to_use,
            'lastdateofMonth' => $last_day_of_the_period_month,
            'range_array_days' => $range_array_days,
            'range_array_days_formated' => $range_array_days_formated,
            'today_partofMonth_start' => Carbon::now()->startOfMonth(),
            'today_partofMonth_end' => Carbon::now()->endOfMonth(),
            'periodKey' => $periodKey,
            'monthnumber' => Cache::get($periodKey),        
        ];
    }
}

if (!function_exists('compareByTimeStamp')) {
    /**
     * Sort Array in order of date by timestamp
     *
     * @param $time1, $time2
     * @return int
     */
    function compareByTimeStamp($time1, $time2){ 
        if (strtotime($time1) < strtotime($time2)) {
            return 1; 
        }
        else if (strtotime($time1) > strtotime($time2)) {
            return -1; 
        }
        else{
            return 0; 
        }
    }
}

if (!function_exists('compareByTimeStampconvert')) {
    /**
     * Sort Array in order of date by timestamp
     *
     * @param $time1, $time2
     * @return int
     */
    function compareByTimeStampconvert($time1, $time2){ 
        /**dump($time1);*/
        /**dump($time2);*/

        $time1 = Carbon::createFromFormat('Y-m-d H:i:s', $time1)->timestamp;
        $time2 = Carbon::createFromFormat('Y-m-d H:i:s', $time2)->timestamp;

        /**dump($time1);*/
        /**dd($time2);*/

        if ($time1 < $time2) {
            return 1; 
        }
        else if ($time1 > $time2) {
            return -1; 
        }
        else{
            return 0;
        }
    }
}

if (!function_exists('compareByTimeStampconvertInverse')) {
    /**
     * Sort Array in order of date by timestamp
     *
     * @param $time1, $time2
     * @return int
     */
    function compareByTimeStampconvertInverse($time1, $time2){ 
        /**dump($time1);*/
        /**dump($time2);*/

        $time1 = Carbon::createFromFormat('Y-m-d H:i:s', $time1)->timestamp;
        $time2 = Carbon::createFromFormat('Y-m-d H:i:s', $time2)->timestamp;

        /**dump($time1);*/
        /**dd($time2);*/

        if ($time1 < $time2) {
            return -1; 
        }
        else if ($time1 > $time2) {
            return 1; 
        }
        else{
            return 0;
        }
    }
}

if (!function_exists('getPeriod')) {
    /**
     * Get current period and check control
     *
     * @author tolawho
     */
    function getPeriod(){
        $periodKey = sprintf(config('base.cache.key.period'), auth()->id());
        if (!Cache::has($periodKey)) {
            Cache::put($periodKey, date('Y-m-01'), config('base.cache.time.short'));
        }

        /**check can next & prev*/
        $current = date('Y-m-01', strtotime(Cache::get($periodKey)));
        $lastDayOfMonth = date('Y-m-t', strtotime($current));

        $next = date('Y-m-01', strtotime("$lastDayOfMonth +1 day"));
        if (strtotime(date('Y-m-01')) <= strtotime($next)) {
            $canNext = true;
        }else{
            $canNext = false;
        }

        $prev = date('Y-m-01', strtotime("$current -1 day"));
        $begin = config('base.start');
        if (strtotime($begin) <= strtotime($prev)) {
            $canPrev = true;
        }else{
            $canPrev = false;
        }

        /**get date diff from today*/
        $today = date_create(date('Y-m-d'));
        $start = date_create($begin);
        $now = Carbon::now();
        $end = Carbon::parse($begin);
        $todayformatted = Carbon::now()->format('Y-m-d');

        $diffMonths = $end->diffInMonths($now);
        $diffYear = + date_diff($today, $start)->format('%y');

        $year = date('Y', strtotime($current));
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $nextMonth = sprintf(SPRINTF_ZEROPAD_YYYY_MM_DD, $year, $i, 1);
            $months[] = $nextMonth;
        }

        $daynamesincurrenttwoweeks = [];
        $range_array_days_formated = [];
        $range_array_days = [];
        $daynamesinmonth = [];
        $daynamesinmonth_v2_partA = [];
        $daynamesinmonth_v2_partB = [];
        $daynamesinmonth_v2_partC = [];

        $specific_date = date('m', strtotime($current)).'-' . date('Y', strtotime($current));

        for ($i = 1; $i <= Carbon::createfromFormat('m-Y', $specific_date)->daysInMonth; $i++){
            $frontpart = substr($current, 0, -2);
            $date_loop_string = $frontpart.sprintf('%02d', $i);
            $daynamesinmonth[$i] = Carbon::parse($date_loop_string)->format('l, d F, Y');
            $range_array_days_formated[$i] = Carbon::parse($date_loop_string)->format('Y-m-d');
            $range_array_days[$i] = Carbon::parse($date_loop_string)->format('d');
            
            $daynamesinmonth_v2_partA[$i] = Carbon::parse($date_loop_string)->format('D');
            $daynamesinmonth_v2_partB[$i] = strtoupper(Carbon::parse($date_loop_string)->format('d M'));
            $daynamesinmonth_v2_partC[$i] = strtoupper(Carbon::parse($date_loop_string)->format('Y'));
        }
        
        $first_day_of_the_period_month = Carbon::parse($current)->startOfMonth();
        $first_day_of_the_period_month_to_use = Carbon::parse($current)->startOfMonth()->format('Y-m-d');
        $last_day_of_the_period_month = $first_day_of_the_period_month->copy()->endOfMonth()->format('Y-m-d');

        $viewingthisperiodmonthtoday = check_today_date_in_period_range($current, $lastDayOfMonth, $todayformatted);

        return [
            'viewingthisperiodmonthtoday' => $viewingthisperiodmonthtoday,
            'today'    => $todayformatted,
            'date'     => $current,
            'start'    => sprintf('-%dm', $diffMonths),
            'when'     => date('M, Y', strtotime($current)),
            'dayNames' => $daynamesinmonth,
            'dayNames_partA' => $daynamesinmonth_v2_partA,
            'dayNames_partB' => $daynamesinmonth_v2_partB,
            'dayNames_partC' => $daynamesinmonth_v2_partC,
            'canNext'  => $canNext, /** 06DEC2019 changed from static true */
            'nextUrl'  => route('period_controls.next_fullpage', [], false),
            'canPrev'  => $canPrev, /** 06DEC2019 changed from static true */
            'prevUrl'  => route('period_controls.prev_fullpage', [], false),
            'gotoUrl'  => route('period_controls.goto_fullpage', [], false),
            'months'   => $months,
            'year'     => date('Y', strtotime($current)),
            'monthselected' => date('M', strtotime($current)),
            'monthselected_number' => date('m', strtotime($current)),
            'monthselected_current_day_number' => date('d', strtotime($current)),
            'numberofdaysinselectedmonth' => Carbon::createfromFormat('m-Y', $specific_date)->daysInMonth,
            'startYear' => sprintf('-%dy', $diffYear),
            'firstdateofMonth' => $first_day_of_the_period_month_to_use,
            'lastdateofMonth' => $last_day_of_the_period_month,
            'range_array_days' => $range_array_days,
            'range_array_days_formated' => $range_array_days_formated,
            'today_partofMonth_start' => Carbon::now()->startOfMonth(),
            'today_partofMonth_end' => Carbon::now()->endOfMonth(),
            'periodKey' => $periodKey,
            'monthnumber' => Cache::get($periodKey),
        ];
    }
}

if (!function_exists('loggy')) {

    /**
     * Create new loggy facade instance
     *
     * @author tolawho
     * @return \Illuminate\Foundation\Application|mixed
     */
    function loggy(){
        return app('loggy');
    }
}

if (!function_exists('array_flatten')) {
    function flatten(array $array, $prefix="") {
        $result = Array();
        array_walk($array, function ($value, $key) use ($array, $prefix, &$result) {
            $path = $prefix ? "$prefix.$key" : $key;
            if (is_array($value)) {
                $result = array_merge($result, flatten($value, $path));
            } else {
                $result[$path] = $value;
            }
        });
        return $result;
    }
}

if (!function_exists('array_depth')) {
    function array_depth($array, $childrenkey = "_no_children_"){
        if (!empty($array[$childrenkey])){
            $array = $array[$childrenkey];
        }
        $max_depth = 1;
        foreach ($array as $value){
            if (is_array($value)){
                $depth = array_depth($value, $childrenkey) + 1;
                if ($depth > $max_depth){
                    $max_depth = $depth;
                }
            }
        }
        return $max_depth;
    }
}

if (!function_exists('array_keys_multi')) {
    function array_keys_multi(array $array){
        $keys = array();
        foreach ($array as $key => $value) {
            $keys[] = $key;
            if (is_array($value)) {
                $keys = array_merge($keys, array_keys_multi($value));
            }
        }
        return $keys;
    }
}

if (!function_exists('array_filter_custom')) {
    function array_filter_custom(array $array, $function_type){
        return array_filter($array, $function_type);
    }
}

if (!function_exists('if_element_is_numeric')) {
    function if_element_is_numeric($var){
        if(is_numeric($var)){
            return false;
        }else{
            return true;
        }
    }
}
