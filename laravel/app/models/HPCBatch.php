<?php

class HPCBatch {
        
    private $name = "";
       
    function __construct($name) {
        $this->name = $name;           
    }
        
    public static function all() {
        $batches = array();
        $batch_names = R::connection()->smembers("batches");
        asort($batch_names);
        foreach ( $batch_names as $batch ) {
            $batches[] = new HPCBatch($batch);
        }        
        return $batches;
    }
    
    public static function create($name) {
        if (! is_null($name)) {
            R::connection()->sadd("batches",$name);
            return new HPCBatch($name);
        }           
    }
    
    function start() {
        // get first pipeline and put files in that start queue
        // client will handle from there
        $pipelines = $this->pipelines();
        
        if ( count($pipelines) > 0 ) {
            $pipline = $pipelines[0];
            $pipline->startFiles($this->files(false));
        }
        
    }
    
    function name() {
        return $this->name;
    }

    function percent_complete() {
        
        // need total queue count
        $queues = Array();
        foreach ($this->pipelines() as $pipeline) {
            
            $queues[] = new HPCQueue($pipeline, "start");
            $checking = true;
            
            while ($checking) {                
                $last_queue = end($queues);                
                if ($last_queue->name() == "done") {
                   $checking = false;
                } else {                    
                   $queues[] = new HPCQueue($pipeline, $last_queue->queue_out());                      
                }               
            }
            
        }         
        
        // for each file get queue number
        $file_progress = 0;
        $file_count = count($this->files());
        foreach ($this->files() as $file) {
            $file_queue = new HPCQueue($file->pipeline(), $file->queue());
            $queue_number = array_search($file_queue, $queues)+1;            
            $file_progress += $queue_number;            
        }                
        
        if ($file_count == 0 || count($queues) == 0 || $file_progress == $file_count) {
            return 0;
        } else {
            return round(($file_progress / (count($queues) * $file_count)) * 100,0);
            return $file_progress.'='.count($queues).'='.$file_count;
        }
    }
    
    function stage() {
        return "None";
    }
    
    function status() {
        $status = R::connection()->get('batch_'.$this->name.'_status');
        if (is_null($status)){
            return "New";
        } else {
            return $staus;
        }
    }
    
    function addFiles($fqs) {
        
        //Todo: make sure we dont add the same file twice!!!
        R::connection()->del('batch_'.$this->name.'_files');
        foreach ($fqs as $fq) {                                             
            R::connection()->rpush('batch_'.$this->name.'_files', $fq);            
        }            
        
    }
    
    function addPipelines($pipes) {
                
        //Todo: make sure we dont add the same file twice!!!
        R::connection()->del('batch_'.$this->name.'_pipelines');
        foreach ($pipes as $p) {                                             
            R::connection()->rpush('batch_'.$this->name.'_pipelines', $p);            
        }        
    }
    
    function remove($file) {
        return R::connection()->lrem('batch_'.$this->name.'_files', 1, $file->name());
    }
    
    function pipelines($object=true) {
        $pipes = array();
        $avail = R::connection()->lrange('batch_'.$this->name.'_pipelines',0,-1);
        // dont sort this list asort($avail);
        foreach( $avail as $p) {
            if ($p != "CommandTemplates") {   
                if ($object) {
                    $pipes[] = new HPCPipeline($p);
                } else {
                    $pipes[] = $p;
                }
            }
        }        
        return $pipes;
    }
    
    function files($object=true) {        
        $files = array();
        $fqs = R::connection()->lrange('batch_'.$this->name.'_files',0,-1);        
        asort($fqs);
        foreach( $fqs as $fq) {
            if ($object) {
                $files[] = new HPCFile($fq);
            } else {
                $files[] = $fq;
            }
               
        }        
        return $files;        
    }
    
    
    
}