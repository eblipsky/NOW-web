<?php

class SettingsController extends \BaseController {            
    
    public function index() {                                                          
        
        $this->data['settings'] = HPCSetting::all();                 
        $this->data['content_title'] = "Settings";

        return View::make('hpc/settings',$this->data); 
        
    }
       
    public function save() {

        HPCSetting::save(
            Input::get('LOGO'),
            Input::get('COMPANY'),
            Input::get('DIVISION'),
            Input::get('BASE_DIR'),
            Input::get('HAS_NODES'), 
            Input::get('EMAIL'), 
            Input::get('REDIS_HOST'), 
            Input::get('REDIS_PORT'), 
            Input::get('REDIS_DB'),
            Input::get('COUCHDB_HOST'), 
            Input::get('COUCHDB_DB') 
        );        
        
    }

}
