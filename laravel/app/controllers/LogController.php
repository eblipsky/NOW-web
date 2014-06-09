<?php

class LogController extends \BaseController {            
    
    public function index($logid) {                                                          
        
        $client = new couchClient (Settings::COUCHDB_HOST,  Settings::COUCHDB_DB);                                    
        $log = $client->asArray()->getDoc($logid);                      

        $url = Settings::COUCHDB_HOST."/".Settings::COUCHDB_DB."/".$logid."/logfile";

        $str = nl2br(file_get_contents($url));
        //$str = str_replace("<br />", "<hr>", $str);                        
        $this->data['file'] = $str;                        

        return View::make('hpc/log',$this->data); 
        
    }
       
}