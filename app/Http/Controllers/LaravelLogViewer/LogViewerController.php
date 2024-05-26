<?php

namespace App\Http\Controllers\LaravelLogViewer;

use Illuminate\Support\Facades\Crypt;

use App\Http\Controllers\LaravelLogViewer\LaravelLogViewer;

if (class_exists("\\Illuminate\\Routing\\Controller")) {    
    class BaseController extends \Illuminate\Routing\Controller {}  
} elseif (class_exists("Laravel\\Lumen\\Routing\\Controller")) {    
    class BaseController extends \Laravel\Lumen\Routing\Controller {}   
}

/**
 * Class LogViewerController
 * @package App\Http\Controllers\LaravelLogViewer
 */
class LogViewerController extends BaseController
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var LaravelLogViewer
     */
    private $log_viewer;

    /**
     * @var string
     */
    protected $view_log = 'laravel-log-viewer::log';

    /**
     * LogViewerController constructor.
     */
    public function __construct()
    {
        $this->log_viewer = new LaravelLogViewer();
        $this->request = app('request');
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function index()
    {
        $folderFiles = [];
        $folderFilesSizes = [];
        if ($this->request->input('f')) {
            $this->log_viewer->setFolder(str_replace(storage_path('logs'), "", Crypt::decrypt($this->request->input('f'))));
            $folderFiles = $this->log_viewer->getFolderFiles(true);
            $folderFilesSizes = $this->log_viewer->getFolderFilesSizes(true);
        }
        if ($this->request->input('l')) {
            $this->log_viewer->setFile(Crypt::decrypt($this->request->input('l')));
        }

        if ($early_return = $this->earlyReturn()) {
            return $early_return;
        }

        $foldersAndFiles_unflattened = $this->log_viewer->foldersAndFiles();
        $restructured_structure_array = array_flip(array_values(array_filter_custom(array_keys_multi($foldersAndFiles_unflattened), "if_element_is_numeric")));
        foreach($restructured_structure_array as $key => $value){
            $restructured_structure_array["folders"][$key] = [];
            $restructured_structure_array["folders_file_sizes"][$key] = [];
            unset($restructured_structure_array[$key]);
        }
        /**dump($restructured_structure_array);*/
        $foldersAndFiles_flattened = flatten($foldersAndFiles_unflattened);
        /**dump($foldersAndFiles_flattened);*/
        foreach($foldersAndFiles_flattened as $key => $value){
            $restruct_key = array_reverse(explode(".", $key));
            foreach($restruct_key as $useful_inner_key => $key_name){
                /** the array is reversed therefore the first element is always going to be a numerical value */
                if(is_numeric($key_name)){
                    
                    if(isset($restruct_key[$useful_inner_key + 1])){
                        /** its a log in a folder */
                        $first_folder_path_key_name_to_use = $restruct_key[$useful_inner_key + 1];
                        $restructured_structure_array["folders"][$first_folder_path_key_name_to_use][$key_name] = $value;
                        $restructured_structure_array["folders_file_sizes"][$first_folder_path_key_name_to_use][$key_name] = $this->log_viewer->bytesToHuman(app('files')->size($value));
                    }else{
                        /** its a file not in a folder */
                        $restructured_structure_array["files"][$key_name] = $value;
                        $restructured_structure_array["files_sizes"][$key_name] = $this->log_viewer->bytesToHuman(app('files')->size($value));
                    }

                }else{
                    dd('first key is not numerical ?? it should be. must mean the flatten function is not working properly');
                }
                break;
            }
        }
        /**dump($restructured_structure_array);*/

        $data = [
            'logs' => $this->log_viewer->all(),
            'folders' => $this->log_viewer->getFolders(),
            'current_folder' => $this->log_viewer->getFolderName(),
            'folder_files' => $folderFiles,
            'folder_files_sizes' => $folderFilesSizes,
            'files' => $this->log_viewer->getFiles(true),
            'files_sizes' => $this->log_viewer->getFilesSizes(true),
            'current_file' => $this->log_viewer->getFileName(),
            'standardFormat' => true,
            'structure' => $restructured_structure_array,
            'storage_path' => $this->log_viewer->getStoragePath(),
        ];

        $data["full_data"] = $data;
        /**dd($data);*/

        if ($this->request->wantsJson()) {
            return $data;
        }

        if (is_array($data['logs']) && count($data['logs']) > 0) {
            $firstLog = reset($data['logs']);
            if (!$firstLog['context'] && !$firstLog['level']) {
                $data['standardFormat'] = false;
            }
        }

        return app('view')->make($this->view_log, $data);
    }



    /**
     * @return bool|mixed
     * @throws \Exception
     */
    private function earlyReturn()
    {
        if ($this->request->input('f')) {
            $this->log_viewer->setFolder(Crypt::decrypt($this->request->input('f')));
        }

        if ($this->request->input('dl')) {
            return $this->download($this->pathFromInput('dl'));
        } elseif ($this->request->has('clean')) {
            app('files')->put($this->pathFromInput('clean'), '');
            return $this->redirect(url()->previous());
        } elseif ($this->request->has('del')) {
            app('files')->delete($this->pathFromInput('del'));

            $path_to_use = explode("/", $this->log_viewer->getFolderName());
            $path_to_use = "/".$path_to_use[array_key_last($path_to_use)];
            /**dump($path_to_use);*/
            $folderFiles = $this->log_viewer->getFolderFiles(false, $path_to_use);
            /**dump($folderFiles);*/
            if (!empty($folderFiles)) {
                $next_encrypted_log_file = $folderFiles[array_key_first($folderFiles)];
                $next_encrypted_log_file = Crypt::encrypt($folderFiles[array_key_first($folderFiles)]);
                $next_encrypted_log_file_folder = str_replace(storage_path('logs'), "", $this->log_viewer->getFolderName());
                $next_encrypted_log_file_folder = Crypt::encrypt(str_replace(storage_path('logs'), "", $this->log_viewer->getFolderName()));
                return $this->redirect($this->request->url()."?l=".$next_encrypted_log_file."&f=".$next_encrypted_log_file_folder);
            }else{
                return $this->redirect($this->request->url());
            }
        } elseif ($this->request->has('delall')) {
            if ($this->request->input('f')) {
                $path_to_use = explode("/", $this->log_viewer->getFolderName());
                $path_to_use = "/".$path_to_use[array_key_last($path_to_use)];
                $files = ($this->log_viewer->getFolderName()) ? $this->log_viewer->getFolderFiles(false, $path_to_use) : $this->log_viewer->getFiles(true);
                foreach ($files as $file) {
                    app('files')->delete($this->log_viewer->pathToLogFile($file));
                }
                return $this->redirect($this->request->url()."?f=".$this->request->input('f'));
            }else{
                $files = ($this->log_viewer->getFolderName()) ? $this->log_viewer->getFolderFiles(true) : $this->log_viewer->getFiles(true);
                foreach ($files as $file) {
                    app('files')->delete($this->log_viewer->pathToLogFile($file));
                }
                return $this->redirect($this->request->url());
            }
        }
        return false;
    }

    /**
     * @param string $input_string
     * @return string
     * @throws \Exception
     */
    private function pathFromInput($input_string)
    {
        return $this->log_viewer->pathToLogFile(Crypt::decrypt($this->request->input($input_string)));
    }

    /**
     * @param $to
     * @return mixed
     */
    private function redirect($to)
    {
        if (function_exists('redirect')) {
            return redirect($to);
        }

        return app('redirect')->to($to);
    }

    /**
     * @param string $data
     * @return mixed
     */
    private function download($data)
    {
        if (function_exists('response')) {
            return response()->download($data);
        }

        // For laravel 4.2
        return app('\Illuminate\Support\Facades\Response')->download($data);
    }
}
