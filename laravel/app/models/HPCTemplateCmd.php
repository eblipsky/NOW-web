<?php

class HPCTemplateCmd {
    
    //ToDo: I think this class isnt totally respected!!! 
    
    private $name = "";    
       
    function __construct($name) {
        $this->name = $name;        
    }
    
    static function all() {
        $cmds = array();
        
        $sorted = R::connection()->hkeys("tmplcmd");        
        asort($sorted);
                
        foreach ($sorted as $var) {
            $cmds[] = new HPCTemplateCmd($var);
        }
        return $cmds;
    }    
    
    function name() {
        return $this->name;
    }
    
    function cmd() {
        return R::connection()->hget("tmplcmd",$this->name);
    }           
    
    function save($val) {       
        return R::connection()->hset("tmplcmd",$this->name,$val);
    }                           
    
    function delete() {
        return R::connection()->hdel("tmplcmd", $this->name);
    }
    
}