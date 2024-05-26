<?php

namespace App\Http\Controllers\LaravelLogViewer;

use App\Http\Controllers\LaravelLogViewer\Level;
use App\Http\Controllers\LaravelLogViewer\Pattern;

/**
 * Class LaravelLogViewer
 * @package App\Http\Controllers\LaravelLogViewer
 */
class LaravelLogViewer
{
    /**
     * @var string file
     */
    private $file;

    /**
     * @var string folder
     */
    private $folder;

    /**
     * @var string storage_path
     */
    private $storage_path;

    /**
     * Why? Uh... Sorry
     */
    const MAX_FILE_SIZE = 52428800;

    /**
     * @var Level level
     */
    private $level;

    /**
     * @var Pattern pattern
     */
    private $pattern;

    /**
     * LaravelLogViewer constructor.
     */
    public function __construct()
    {
        $this->level = new Level();
        $this->pattern = new Pattern();
        $this->storage_path = function_exists('config') ? config('logviewer.storage_path', storage_path('logs')) : storage_path('logs');

    }

    /**
     * @param string $folder
     */
    public function setFolder($folder)
    {
        if (app('files')->exists($folder)) {
            $this->folder = $folder;
        }
        else if(is_array($this->storage_path)) {
            foreach ($this->storage_path as $value) {
                $logsPath = $value . '/' . $folder;
                if (app('files')->exists($logsPath)) {
                    $this->folder = $folder;
                    break;
                }
            }
        } else {
                $logsPath = $this->storage_path . '/' . $folder;
                if (app('files')->exists($logsPath)) {
                    $this->folder = $folder;
                }
        }
    }

    /**
     * @param string $file
     * @throws \Exception
     */
    public function setFile($file)
    {
        $file = $this->pathToLogFile($file);

        if (app('files')->exists($file)) {
            $this->file = $file;
        }
    }

    /**
     * @param string $file
     * @return string
     * @throws \Exception
     */
    public function pathToLogFile($file)
    {

        if (app('files')->exists($file)) { // try the absolute path
      
            return $file;
        }
        if (is_array($this->storage_path)) {
     
            foreach ($this->storage_path as $folder) {
                if (app('files')->exists($folder . '/' . $file)) { // try the absolute path
                    $file = $folder . '/' . $file;
                    break;
                }
            }
            return $file;
        }

        $logsPath = $this->storage_path;
        $logsPath .= ($this->folder) ? '/' . $this->folder : '';
        $file = $logsPath . '/' . $file;
        // check if requested file is really in the logs directory
        if (dirname($file) !== $logsPath) {
            throw new \Exception('No such log file: '.$file);
        }
        
        return $file;
    }

    /**
     * @return string
     */
    public function getFolderName()
    {
        return $this->folder;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        //return basename($this->file);
        return $this->file;
    }

    /**
     * @return array
     */
    public function all()
    {
        if (strpos($this->folder, $this->storage_path) !== false) {
            $path_to_use = str_replace($this->storage_path, "", $this->folder);
        }else{
            $path_to_use = '';
        }

        $log = array();
        if (!$this->file) {
            $log_file = (!$this->folder) ? $this->getFiles() : $this->getFolderFiles(false, $path_to_use);
            /**dd($log_file);*/
            if (!count($log_file)) {
                return [];
            }
            $this->file = $log_file[0];
        }

        $max_file_size = function_exists('config') ? config('logviewer.max_file_size', self::MAX_FILE_SIZE) : self::MAX_FILE_SIZE;
        if (app('files')->size($this->file) > $max_file_size) {
            return null;
        }

        if (!is_readable($this->file)) {
            return [[
                'context' => '',
                'level' => '',
                'date' => null,
                'text' => 'Log file "' . $this->file . '" not readable',
                'stack' => '',
            ]];
        }

        $file = app('files')->get($this->file);

        preg_match_all($this->pattern->getPattern('logs'), $file, $headings);

        if (!is_array($headings)) {
            return $log;
        }

        $log_data = preg_split($this->pattern->getPattern('logs'), $file);

        if ($log_data[0] < 1) {
            array_shift($log_data);
        }

        foreach ($headings as $h) {
            for ($i = 0, $j = count($h); $i < $j; $i++) {
                foreach ($this->level->all() as $level) {
                    if (strpos(strtolower($h[$i]), '.' . $level) || strpos(strtolower($h[$i]), $level . ':')) {

                        preg_match($this->pattern->getPattern('current_log', 0) . $level . $this->pattern->getPattern('current_log', 1), $h[$i], $current);
                        if (!isset($current[4])) {
                            continue;
                        }

                        $log[] = array(
                            'context' => $current[3],
                            'level' => $level,
                            'folder' => $this->folder,
                            'level_class' => $this->level->cssClass($level),
                            'level_img' => $this->level->img($level),
                            'date' => $current[1],
                            'text' => $current[4],
                            'in_file' => isset($current[5]) ? $current[5] : null,
                            'stack' => preg_replace("/^\n*/", '', $log_data[$i])
                        );
                    }
                }
            }
        }

        if (empty($log)) {

            $lines = explode(PHP_EOL, $file);
            $log = [];

            foreach ($lines as $key => $line) {
                $log[] = [
                    'context' => '',
                    'level' => '',
                    'folder' => '',
                    'level_class' => '',
                    'level_img' => '',
                    'date' => $key + 1,
                    'text' => $line,
                    'in_file' => null,
                    'stack' => '',
                ];
            }
        }

        return array_reverse($log);
    }

    /**
     * Creates a multidimensional array
     * of subdirectories and files
     *
     * @param null $path
     *
     * @return array
     */
    public function foldersAndFiles($path = null)
    {
        /**dump($path);*/
        $contents = array();
        $dir = $path ? $path : $this->storage_path;
        /**dump(scandir($dir));*/
        foreach(scandir($dir) as $node) {
            if ($node == '.' || $node == '..' || $node == '.gitignore'){
                continue;
            }else{
                $path = $dir . '/' . $node;
                /**dump($path);*/
                if(is_dir($path)){
                    $contents[$path] = $this->foldersAndFiles($path);
                }else{
                    $contents[] = $path;
                }
            }
        }
        /**dump($contents);*/
        return $contents;
    }

    /**
     * Returns an array of
     * all subdirectories of specified directory
     *
     * @param string $folder
     *
     * @return array
     */
    public function getFolders($folder = '')
    {
        $folders = [];
        $listObject = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->storage_path.'/'.$folder, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($listObject as $fileinfo) {
            if($fileinfo->isDir()) $folders[] = $fileinfo->getRealPath();
        }

        $folders = array_reverse($folders);
        sort($folders);
       
        return $folders;
        // $folders = glob($this->storage_path . '/*', GLOB_ONLYDIR);
        // if (is_array($this->storage_path)) {
        //     foreach ($this->storage_path as $value) {
        //         $folders = array_merge(
        //             $folders,
        //             glob($value . '/*', GLOB_ONLYDIR)
        //         );
        //     }
        // }

        // if (is_array($folders)) {
        //     foreach ($folders as $k => $folder) {
        //         $folders[$k] = basename($folder);
        //     }
        // }
        // dd($folders);
        // return array_values($folders);
    }

    /**
     * @param bool $basename
     * @return array
     */
    public function getFolderFiles($basename = false, $folder = '')
    {
        if($folder == ''){
            $folder = $this->folder;
        }
        return $this->getFiles($basename, $folder);
    }

    /**
     * @param bool $basename
     * @return array
     */
    public function getFolderFilesSizes($basename = false, $folder = '')
    {
        if($folder == ''){
            $folder = $this->folder;
        }        
        return $this->getFilesSizes($basename, $folder);
    }

    /**
     * @param bool $basename
     * @param string $folder
     * @return array
     */
    public function getFiles($basename = false, $folder = '')
    {
        // $files = [];
        // $pattern = function_exists('config') ? config('logviewer.pattern', '*.log') : '*.log';
        // $fullPath = $this->storage_path.'/'.$folder;

        // $listObject = new \RecursiveIteratorIterator(
        //     new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS),
        //     \RecursiveIteratorIterator::CHILD_FIRST
        // );

        // foreach ($listObject as $fileinfo) {
        //     if(!$fileinfo->isDir() && strtolower(pathinfo($fileinfo->getRealPath(), PATHINFO_EXTENSION)) == explode('.', $pattern)[1])
        //         $files[] = $basename ? basename($fileinfo->getRealPath()) : $fileinfo->getRealPath();
        // }
        // //dump($files);
        // return $files;

        // dump($basename);
        // dump($folder);

        $pattern = function_exists('config') ? config('logviewer.pattern', '*.log') : '*.log';
        $files = glob(
            $this->storage_path . '' . $folder . '/' . $pattern,
            preg_match($this->pattern->getPattern('files'), $pattern) ? GLOB_BRACE : 0
        );
        if (is_array($this->storage_path)) {
            foreach ($this->storage_path as $value) {
                $files = array_merge(
                  $files,
                  glob(
                      $value . '/' . $folder . '/' . $pattern,
                      preg_match($this->pattern->getPattern('files'), $pattern) ? GLOB_BRACE : 0
                  )
                );
            }
        }

        $files = array_reverse($files);
        $files = array_filter($files, 'is_file');
        if ($basename && is_array($files)) {
            foreach ($files as $k => $file) {
                $files[$k] = basename($file);
            }
        }
        // dump($files);
        return array_values($files);
    }

    /**
     * @return string
     */
    public function getStoragePath()
    {
        return $this->storage_path;
    }

    /**
     * @param $path
     *
     * @return void
     */
    public function setStoragePath($path)
    {
        $this->storage_path = $path;
    }

    public static function directoryTreeStructure($full_data)
    {
        /**dump($full_data);*/
        // dump($full_data['current_folder']);
        // dump($full_data['current_file']);
        // dump($full_data['structure']['files']);


        /** we start from the top and work our way down */
        foreach ($full_data['folders'] as $full_data_folders_key => $full_data_folders_value) {
            

            foreach ($full_data['structure']['folders'] as $structure_folders_key => $structure_folders_log_files) {
                /**dd($full_data_folders_value);*/
                /**dd($structure_folders_key);*/

                if($full_data_folders_value == $structure_folders_key){
                    if(is_dir($full_data_folders_value)){
                        
                        $exploded = explode( "/", $full_data_folders_value);
                        $folder_display_name = last($exploded);
                        /**dd($folder_display_name);*/

                        echo '<div class="list-group-item">';
                        echo '<a href="?f='. \Illuminate\Support\Facades\Crypt::encrypt($full_data_folders_value).'"><span class="fa fa-folder"></span>&nbsp;'.str_replace($full_data['storage_path'], "", $full_data_folders_value).'</a>';


                        if (strpos($full_data['current_folder'], $full_data['storage_path']) !== false) {
                            /** it already has the storage path in it */
                            $current_folder = $full_data['current_folder'];
                        }else{
                            /** storage path missing */
                            $current_folder = $full_data['storage_path'].$full_data['current_folder'];
                        }
                        /**dump($current_folder);*/
                        /**dump($full_data_folders_value);*/

                        if($current_folder == $full_data_folders_value){

                            echo '<div class="list-group folder">';
                            $reversed_structure_folders_log_files = array_reverse($structure_folders_log_files);
                            /**dd($reversed_structure_folders_log_files);*/
                            foreach($reversed_structure_folders_log_files as $generic_log_key => $log_file_details){

                                /** FOR FILES IN FOLDER NEED TO ADD THE STORAGE PATH */
                                if (strpos($full_data['current_file'], $full_data['storage_path']) !== false) {
                                    /** it already has the storage path in it */
                                    $current_file = $full_data['current_file'];
                                }else{
                                    /** storage path missing */
                                    $current_file = $full_data_folders_value.'/'.$full_data['current_file'];
                                }

                                if($current_file == $log_file_details){
                                    $active_class = 'llv-active';
                                }else{
                                    $active_class = '';
                                }

                                $exploded = explode( "/", $log_file_details);
                                $file_name_display = last($exploded);
                                $folder = str_replace($full_data['storage_path'], "", rtrim(str_replace($file_name_display, "", $log_file_details), "/"));
                                $file = $log_file_details;

                                /**dump($exploded);*/
                                /**dump($file_name_display);*/
                                /**dump($folder);*/
                                /**dd($file);*/

                                if(isset($full_data['structure']['folders_file_sizes'][$structure_folders_key][$generic_log_key])){
                                    $file_size_indicator = '<div style="float: right">'.$full_data['structure']['folders_file_sizes'][$structure_folders_key][$generic_log_key].'</div>';
                                }else{
                                    $file_size_indicator = '<div style="float: right">&nbsp;</div>';
                                }


                                if(strlen($file_name_display) >= 39){
                                    $side_bar_text_enable = 'side-bar-text';
                                }else{
                                    $side_bar_text_enable = '';
                                }

                                echo '<a title="'.$file_name_display.'" href="?l='.\Illuminate\Support\Facades\Crypt::encrypt($file).'&f='.\Illuminate\Support\Facades\Crypt::encrypt($folder).'" class="list-group-item '.$active_class.' list-group-item-logname"><div style="float: left; width: 100%;"><div class="side-bar-text-container"><span style="line-height: 1.7;" class="'.$side_bar_text_enable.'">'.$file_name_display.'</span></div></div>'.$file_size_indicator.'</a>';
                                
                            }
                            echo '</div>';
                        }
                        echo '</div>';

                    }else{
                        /**its a log file without a folder handled in next foreach */
                    }
                }
            }


        }

        foreach ($full_data['files'] as $full_data_files_key => $full_data_files_value) {
            foreach ($full_data['structure']['files'] as $structure_files_key => $structure_files_value) {

                /**dump($structure_files_value);*/
                if($full_data['storage_path'].'/'.$full_data_files_value == $structure_files_value){
                    if(is_dir($structure_files_key)){
                        /***/
                    }else{


                        /** FOR FILES NEED TO REMOVE THE STORAGE PATH */
                        if (strpos($full_data['current_file'], $full_data['storage_path']) !== false) {
                            /** it already has the storage path in it */
                            $current_file = str_replace($full_data['storage_path']."/", "", $full_data['current_file']);
                        }else{
                            /** storage path missing */
                            /** remove any trailing forward slash if any */
                            $current_file = str_replace("/", "", $full_data['current_file']);
                        }

                        /**its a log file */
                        if($current_file == $full_data_files_value){
                            $active_class = 'llv-active';
                        }else{
                            $active_class = '';
                        }

                        if(isset($full_data['files_sizes'][$full_data_files_key])){
                            $file_size_indicator = '<div style="float: right">'.$full_data['files_sizes'][$full_data_files_key].'</div>';
                        }else{
                            $file_size_indicator = '';
                        }
                        echo '<a title="'.$full_data_files_value.'" href="?l='.\Illuminate\Support\Facades\Crypt::encrypt($full_data_files_value).'" class="list-group-item '.$active_class.' log-no-folder"><div style="float: left; width: 100%;"><div class="side-bar-text-container"><span style="line-height: 1.7;">'.$full_data_files_value.'</span></div></div>'.$file_size_indicator.'</a>';
                    }
                }
            }
        }
        return;
    }

    /**
     * @param bool $basename
     * @param string $folder
     * @return array
     */
    public function getFilesSizes($basename = false, $folder = '')
    {
        /**dump($basename);*/
        /**dump($folder);*/
        // $files = [];
        // $pattern = function_exists('config') ? config('logviewer.pattern', '*.log') : '*.log';
        // $fullPath = $this->storage_path.$folder;
        // /**dump($fullPath);*/

        // $listObject = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        // /**dump($listObject);*/
        // foreach ($listObject as $fileinfo) {
        //     if(!$fileinfo->isDir() && strtolower(pathinfo($fileinfo->getRealPath(), PATHINFO_EXTENSION)) == explode('.', $pattern)[1])
        //         $files[] = $basename ? basename($fileinfo->getRealPath()) : $fileinfo->getRealPath();
        // }
        // /**dump($files);*/

        // foreach($files as $generic_key => $file_details){
        //     $files[$generic_key] = $this->bytesToHuman(app('files')->size($fullPath."/".$file_details));
        // }
        // dump($files);
        // return $files;



        $pattern = function_exists('config') ? config('logviewer.pattern', '*.log') : '*.log';
        $files = glob(
            $this->storage_path . '/' . $folder . '/' . $pattern,
            preg_match($this->pattern->getPattern('files'), $pattern) ? GLOB_BRACE : 0
        );
        if (is_array($this->storage_path)) {
            foreach ($this->storage_path as $value) {
                $files = array_merge(
                  $files,
                  glob(
                      $value . '/' . $folder . '/' . $pattern,
                      preg_match($this->pattern->getPattern('files'), $pattern) ? GLOB_BRACE : 0
                  )
                );
            }
        }

        $files = array_reverse($files);
        $files = array_filter($files, 'is_file');
        if ($basename && is_array($files)) {
            foreach ($files as $k => $file) {
                $files[$k] = $this->bytesToHuman(app('files')->size($file));
            }
        }
        /**dd($files);*/
        return array_values($files);
    }    

    public function bytesToHuman($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        for ($i = 0; $bytes > 1024; $i++) $bytes /= 1024;
        return round($bytes, 2) . ' ' . $units[$i];
    }


}
