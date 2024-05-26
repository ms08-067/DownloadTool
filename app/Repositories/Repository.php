<?php

namespace App\Repositories;

class Repository
{
    public function checkSpecialCharacters($root_dir = '', $dir = '', $special_characters = array(), &$result = array())
    {
        $converting_path = str_replace($special_characters, '+++++', $dir);
        $last_character = substr($converting_path, strlen($converting_path) - 1);
        if (in_array($last_character, array(' ', '.'))) {
            $converting_path .= '+++++';
        }
        
        $hasConverted = ($dir != $converting_path);
        if ($hasConverted) {
            if (!isset($result[$converting_path])) {
                $result[$converting_path] = [
                    'original' => $dir,
                    'type' => 'folder',
                    'reason' => 'special_character'
                ];
            }
            exec("sudo mv " . $root_dir . $dir . " " . $root_dir . $converting_path);
        }
        $cdir = scandir($root_dir . $dir);
        $root_dir = $root_dir . $dir . DIRECTORY_SEPARATOR;
        foreach ($cdir as $key => $path) {
            if (!in_array($path,array(".",".."))) {
                $converting_path = str_replace($special_characters, '+++++', $path);
                if (is_dir($root_dir . $path)) {
                    $last_character = substr($converting_path, strlen($converting_path) - 1);
                    if (in_array($last_character, array(' ', '.'))) {
                        $converting_path .= '+++++';
                    }
                }
                
                $hasConverted = ($path != $converting_path);
                if (is_dir($root_dir . $path)) {
                    if ($hasConverted) {
                        if (!isset($result[$converting_path])) {
                            $result[$converting_path] = [
                                'original' => $path,
                                'type' => 'folder',
                                'reason' => 'special_character'
                            ];
                        }
                        exec("sudo mv '" . $root_dir . $path . "' '" . $root_dir . $converting_path . "'");
                        $path = $converting_path;
                    }
                    $this->checkSpecialCharacters($root_dir, $path, $special_characters, $result);
                } else {
                    if ($hasConverted) {
                        if (!isset($result[$converting_path])) {
                            $result[$converting_path] = [
                                'original' => $path,
                                'type' => 'file',
                                'reason' => 'special_character'
                            ];
                            exec("sudo mv '" . $root_dir . $path . "' '" . $root_dir . $converting_path . "'");
                        }
                    }
                }
            }
        }
    }
    
    public function checkMaxPathLength($root_dir = '', $dir = '', $max_path_length = 200, &$error_paths = array())
    {
        $cdir = scandir($root_dir . $dir);
        if ($cdir === FALSE || count($cdir) <= 2) {
            $path_length = mb_strlen($root_dir . $dir);
            if ($path_length > $max_path_length) {
                $error_paths[] = $root_dir . $dir;
            }
        } else {
            $root_dir = $root_dir . $dir . DIRECTORY_SEPARATOR;
            foreach ($cdir as $path) {
                if (!in_array($path,array(".",".."))) {
                    if (is_dir($root_dir . $path)) {
                        $this->checkMaxPathLength($root_dir, $path, $max_path_length, $error_paths);
                    }
                }
            }
        }
    }
    
    public function convertErrorPathLength($root_path = '', $error_paths = array(), $special_characters = array(), &$result = array())
    {
        foreach ($error_paths as $path) {
            $path = str_replace($root_path, '', $path); //remove /home/itadmin/data/webroot/{case_id}/new/
            
            $parent_dir = '';
            $arr = explode(DIRECTORY_SEPARATOR, $path);
            foreach ($arr as $old_dir) {
                if (mb_strlen($old_dir) > 25) {
                    $new_dir = substr($old_dir, 0, 25);
                    if (!empty($special_characters)) {
                        $new_dir = str_replace($special_characters, '+++++', $new_dir);
                    }
                    $new_dir .= '+++++';
                    if ($new_dir == $old_dir) {
                        $parent_dir .= $old_dir . DIRECTORY_SEPARATOR; 
                        continue;
                    }
                    
                    if (is_dir($root_path . $parent_dir . $old_dir)) {
                        if (is_dir($root_path . $parent_dir . $new_dir)) { //same parent & existed dir
                            $i = 1;
                            while(isset($result[$new_dir])) {
                                $i ++;
                                $new_dir = $new_dir . '_' . $i;
                            }
                        }
                        $result[$new_dir] = [
                            'original' => $old_dir,
                            'type' => 'folder',
                            'reason' => 'max_path_length'
                        ];
                        exec("sudo mv '" . $root_path . $parent_dir . $old_dir . "' '" . $root_path . $parent_dir . $new_dir . "'");
                    }
                    $old_dir = $new_dir;
                }
                $parent_dir .= $old_dir . DIRECTORY_SEPARATOR; 
            }
        }
    }
}
