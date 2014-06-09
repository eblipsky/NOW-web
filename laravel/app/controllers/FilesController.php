<?php

class FilesController extends \BaseController {            
    
    public function newfiles() {                                                   
        
        $files = array();
//        if ($handle = opendir('/fs-research01/tromp/data/')) {
//            while (false !== ($entry = readdir($handle))) {
//                if ($entry != "." && $entry != "..") {
//                    $files[] = $entry;
//                }
//            }
//            closedir($handle);
//        }

        $this->data['newfiles'] = $files;
        return View::make('hpc/newfiles',$this->data);
    }
     
    public function allfiles() {
        
        $files = HPCFile::all();
        $this->data['files'] = $files;
        
        return View::make('hpc/all_files',$this->data);
        
    }
    
    public function file($fq) {
        
        $file = new HPCFile($fq);
        $this->data['fq'] = $file;
        
        return View::make('hpc/file_info',$this->data);
        
    }
    
    public function setPriority() {     
                
        $fqs = Input::get('fqs');
        $priority = Input::get('priority');                
        
        foreach ($fqs as $key) {
            $fq = new HPCFile($key);
            $fq->setPriority($priority);           
        }
        
        return "";
    }
}