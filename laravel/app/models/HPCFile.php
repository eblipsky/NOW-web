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
        //ToDo: this will return the files current queue
        return R::connection()->hget("finfo_".$this->fq,'queue');
    }    

    public function pipeline() {
        //ToDo: this will return the files current queue
        return R::connection()->hget("finfo_".$this->fq,'pipeline');
    }  
    
    public function status() {
        //ToDo: this will return the files current queue
        return R::connection()->hget("finfo_".$this->fq,'status');
    } 
    
    public function node() {
        //ToDo: this will return the node if its being processed
        return R::connection()->hget("finfo_".$this->fq,'node');
    }
    
    public function stages() {
        //todo:fix naming, should be log, not stages
        $log = new HPCLog($this->name());
        return $log->log_entries();

    } 
    
}