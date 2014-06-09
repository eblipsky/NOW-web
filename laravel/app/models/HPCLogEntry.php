<?php

class HPCLogEntry {
    
    private $couchLog = null;

    function __construct($couchLog) {        
        $this->couchLog = $couchLog;         
    }
    
    function stage() {
        return $this->couchLog->value->stage;
    }
    
    function cmdver() {
        
        if (isset($this->couchLog->value->cmdver)) {
            return $this->couchLog->value->cmdver;
        } else {
            return "";
        }
    } 

    function cmd() {
        
        $cmd = "";    
        
        if (isset($this->couchLog->value->cmd)) {
            $cmd = $this->couchLog->value->cmd;
        } else {
            $cmd = "not captured";
        }
        
        $cmd = str_replace("-", "&#8209;", $cmd);
        
        return $cmd;       
        
    }
    
    function logurl() {
                       
        //ToDo: no link if attaachment doesnt exist        
        $url = "/hpc/log/".$this->couchLog->id;
        return $url;
        
    }
    
    function node() {
        //return $this->json->node;
        return $this->couchLog->value->node;
    }    
    
    function start() {
        //return $this->json->start;
        return $this->couchLog->value->start;
    }
    
    function end() {
        //return $this->json->end;
        return $this->couchLog->value->end;
    }
    
    function total() {
        //return $this->json->total;
        return $this->couchLog->value->total;
    }
}