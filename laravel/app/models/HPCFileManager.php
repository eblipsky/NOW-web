<?php

class HPCFileManager {

    // ToDo: need to integrate the file object for files that are already imported or inprocess
    
    public static function all() {

        $handle = opendir(Settings::DATA_DIR);
        $files = array();
            
        if ($handle) 
        {
            while (false !== ($entry = readdir($handle))) 
            {
                if ($entry != "." && $entry != "..") 
                {
                    $files[] = $entry;
                }
            }
            closedir($handle);
        }
        return $files;
    }

}
