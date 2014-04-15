<?php

class HPCLogEntry {
    
    private $json = null;

    function __construct($json) {
        $this->json = $json;        
    }
    
    function stage() {
        return $this->json->stage;
    }

    function cmd() {
        
        $cmd = "";
        
        if (isset($this->json->cmd)) {
            $cmd = $this->json->cmd;
        } else {
            $cmd = "not captured";
        }
        
        $cmd = str_replace("-", "&#8209;", $cmd);
        
        return $cmd;       
        
    }
    
    function logurl() {
                       
        //ToDo: once this is mapped to couch we will have a log ID                
        //$url = "/hpc/log/".$this->json->logid;        
        $url = "/hpc/log/777074f7375c3b9fecff988e980032af";
        
        return $url;
        
    }
    
    function node() {
        return $this->json->node;
    }    
    
    function start() {
        return $this->json->start;
    }
    
    function end() {
        return $this->json->end;
    }
    
    function total() {
        return $this->json->total;
    }
}