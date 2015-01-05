<?php

class FileManagerController extends \BaseController {            
    
    public function index() {                                                          
        
        $this->data['files'] = HPCFileManager::all();                 
        $this->data['content_title'] = "File Manager";
        
        return View::make('hpc/file_manager',$this->data); 
        
    }
       
}