<?php

class HPCNode {
        
    private $name = "";
       
    function __construct($name) {
        $this->name = $name;             
    }
    
    public static function all() {
        $nodes = array();
        $node_names = R::connection()->smembers("nodes");
        asort($node_names);
        foreach ( $node_names as $node ) {
            $nodes[] = new HPCNode($node);
        }        
        return $nodes;
    }

    function name() {
        return $this->name;
    }
    
    function ver() {
        return R::connection()->hget($this->name,'ver');
    }
    
    function pipeline() {
        return R::connection()->hget($this->name,"pipeline");
    }
    function setPipeline($pipeline) {
        return R::connection()->hset($this->name,"pipeline",$pipeline);
    }
           
    function stage() {
        return R::connection()->hget($this->name,'stage'); 
    }    
    
    function ProcessingPipeline() {
        $both = $this->stage();
        $both = explode('_queue_', $both);
        return new HPCPipeline($both[0]); 
    }    
    
    function ProcessingQueue() {
        $both = $this->stage();
        if ( strpos($both, "_queue_") === false ) {
    	    return "";
        } else {
            $both = explode('_queue_', $both);
            return new HPCQueue($both[0], $both[1]);
	}
    }    
    
    function start_time() {
        return R::connection()->hget($this->name,'start');   
    }
    
    function files() {
        return R::connection()->lrange($this->name.'_files',0,-1);        
    }
    
    function file_info() {
        $info = array();
        foreach ($this->files() as $fq) {
            $info[] = new HPCFile($fq);
        }        
        return $info;
    }
    
    function cmds() {
        return R::connection()->get('cmd_'.$this->name);        
    }    
    
    
}
