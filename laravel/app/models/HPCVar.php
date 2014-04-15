<?php

class HPCVar {
    
    private $name = "";
    private $key = "";
       
    function __construct($key,$name) {
        $this->name = $name;
        $this->key = $key;
    }
    
    static function all($key) {
        $vars = array();        
        foreach (R::connection()->hkeys($key) as $var) {
            $vars[] = new HPCVar($key,$var);
        }
        return $vars;
    }
    
    function key() {
        return $this->key;
    }
    
    function name() {
        return $this->name;
    }
    
    function save($val) {
        return R::connection()->hset($this->key,$this->name,$val);
    }
    
    function val() {
        return R::connection()->hget($this->key,$this->name);
    }                              
    
    function delete() {
        return R::connection()->hdel($this->key, $this->name);
    }
    
}