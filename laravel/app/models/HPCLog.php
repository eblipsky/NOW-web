<?php

class HPCLog {
    
    private $fq = "";
    private $json = null;

    function __construct($fq) {
        $this->fq = $fq;        
    }
    
    private function format_time($str) {
        if (starts_with($str, "!!")) {
            return $str;
        } else {
            $parts = explode(":", $str);
            return sprintf("%02d:%02d:%02d",$parts[0],$parts[1],$parts[2]);
        }
    }
    
    function log_entries() {                               
        
        $client = new couchClient (Settings::COUCHDB_HOST,  Settings::COUCHDB_DB);
        $opts = array ( "include_docs" => False, "descending" => false, "startkey"=> array($this->fq), "endkey"=> array($this->fq, array()) );
        $logs = $client->setQueryParameters($opts)->getView('logs','all')->rows;                           
        
        if (count($logs) == 0) {
            return "";
        }
        
        $all = array();
        foreach ( $logs as $log) {
            $all[] = new HPCLogEntry($log);
        }
        
        return $all;
        
//        $log = R::connection()->get('fq_time_'.$this->fq);        
//        $this->json = $log;
//        
//        $entries = json_decode($this->json);       
//        
//        if ($entries == '') {
//            return "";
//        }
//        
//        $logs = array();
//        foreach ( $entries as $j) {
//            $logs[] = new HPCLogEntry($j);
//        }
//        
//        return $logs;        
        
    }
    
    

    
}