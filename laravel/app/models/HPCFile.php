<?php

class HPCFile {            
    
    private $fq;
       
    function __construct($fq) {
        $this->fq = $fq;
    }
    
    public static function all() {                        
        $files = array();
        $sorted = R::connection()->smembers("fq");
        asort($sorted);
        foreach( $sorted as $file) {
            $files[] = new HPCFile($file);
        }
        return $files;
    }
    
    public static function find($pipeline,$queue) {
        
        $files = array();
        $file_names = R::connection()->lrange($pipeline . '_queue_' . urldecode($queue), 0, -1);
        asort($file_names);
        foreach ( $file_names as $file ) {
            $files[] = new HPCFile($file);
        }        
        usort($files, array("Compare", "cmp_priority"));
        return $files;
    }
    
    public function inProcess() {
        
        foreach ( HPCNode::all() as $node) {
            foreach ( $node->files() as $file) {
                if ( $file == $this->name() ) {
                    return TRUE;
                }
            }
        }
        
        return FALSE;
    }
    
    private function load_vars() {
        $fh = fopen($this->path()."/".$this->name().".var",'r');
        while ($line = fgets($fh)) {
            $parts=  explode("=", $line);
            $this->var_store(trim($parts[0]),trim($parts[1]));
        }
        fclose($fh);
    }
    
    public function save() {
        if (! R::connection()->sismember("fq",  $this->name()) ) {
            //new file, add and load the vars from the .var file            
            R::connection()->sadd("fq",$this->name());
            $this->load_vars();
            return true;
        }
        return false;
    }
    
    public function vars() {
        return HPCVar::all("var_".$this->name());
    }    
    
    public function var_store($name,$val) {
        $var = new HPCVar($this->key, $name);
        $var->save($val);        
    }

    public function var_update($name,$val) {
        $var = new HPCVar($this->key,$name);
        return $var->save($val);            
    }

    public function var_destroy($name) {
        $var = new HPCVar($this->key,$name);
        return $var->delete();                        
    }    
            
    public function delete() {
        R::connection()->srem("fq",$this->name());
    }
    
    public function clear() {
        R::connection()->del('fq_time_' . $this->name);
    }

    public function fullname() {
        return $this->dir().$this->filename();
    }
    
    public function name() {
        return $this->fq;
    }
    
    public function filename() {
        return R::connection()->hget("finfo_".$this->fq,'filename');
        //return $this->info['filename'];
    }
    
    public function dir() {
        return R::connection()->hget("finfo_".$this->fq,'dir');
        //return $this->info['dirname'];
    }
    
    public function queue() {        
        return R::connection()->hget("finfo_".$this->fq,'queue');
    }    

    public function pipeline() {        
        return R::connection()->hget("finfo_".$this->fq,'pipeline');
    }  
    
    public function status() {        
        return R::connection()->hget("finfo_".$this->fq,'status');
    } 

    public function setPriority($priority) {        
        return R::connection()->hset("finfo_".$this->fq,'priority',$priority);
    } 
    public function priority() {        
        $priority = R::connection()->hget("finfo_".$this->fq,'priority');
        if (isset($priority)) {
            return $priority;
        } else {
            return 5;
        }
    } 
    
    public function node() {        
        return R::connection()->hget("finfo_".$this->fq,'node');
    }
    
    public function stages() {        
        $log = new HPCLog($this->name());
        return $log->log_entries();
    } 
    
}