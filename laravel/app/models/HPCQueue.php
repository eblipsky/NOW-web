<?php

class HPCQueue implements Countable {
    
    private $pipeline = "";
    private $name = "";
       
    function __construct($pipeline,$name) {
        
        $this->name = urldecode($name);
        if (is_string($pipeline)) 
            $this->pipeline = new HPCPipeline($pipeline);
        else
            $this->pipeline = $pipeline;    
        
//        Cache::rememberForever($pipeline.'_'.$name, function() use($this) {
//            
//            $this->name = urldecode($name);
//            if (is_string($pipeline)) 
//                $this->pipeline = new HPCPipeline($pipeline);
//            else
//                $this->pipeline = $pipeline;    
//            
//        
//        });        
                        
        //Cache::forever($this->pipeline->name.'_'.$name, $this);
    }
    
    public function all() {
        return $this->pipeline->queues();
    }
    
    public function pipeline() {
        return $this->pipeline;
    }
    
    public static function create($pipeline,$queue) {
        Cache::forget($pipeline.'_'.$queue);
        R::connection()->sadd($pipeline . "_queue", urldecode($queue));
        R::connection()->set($pipeline . "_queue_out_".urldecode($queue), 'None');
        R::connection()->set($pipeline . "_queue_err_".urldecode($queue), 'None');
        R::connection()->set($pipeline . "_queue_files_".urldecode($queue), 'MAX_CPU');
        R::connection()->set($pipeline . "_queue_valid_".urldecode($queue), 'check_default');
        
        if ($pipeline == "CommandTemplates") {
            $tc = new HPCTemplateCmd($pipeline.'_'.$queue);
            $tc->save("");            
        }
    }
    
    function update($active,$out,$err,$valid,$templatecmd,$cmd,$cmdtype,$files,$output,$desc) {
        Cache::forget($this->pipeline->name.'_'.$this->name);
        R::connection()->set($this->pipeline->name . "_queue_active_" . $this->name,$active);                
        R::connection()->set($this->pipeline->name . "_queue_out_" . $this->name,$out);            
        R::connection()->set($this->pipeline->name . "_queue_err_" . $this->name,$err);                
        R::connection()->set($this->pipeline->name . "_queue_valid_" . $this->name,$valid);              
        R::connection()->set($this->pipeline->name . "_queue_template_" . $this->name,$templatecmd);
        R::connection()->set($this->pipeline->name . "_queue_cmd_" . $this->name,$cmd);
        R::connection()->set($this->pipeline->name . "_queue_cmdtype_" . $this->name,$cmdtype);        
        R::connection()->set($this->pipeline->name . "_queue_files_" . $this->name,$files);       
        R::connection()->set($this->pipeline->name . "_queue_output_" . $this->name,$output);     
        R::connection()->set($this->pipeline->name . "_queue_desc_" . $this->name,$desc);
        
        if ($pipeline == "CommandTemplates") {
            $tc = new HPCTemplateCmd($pipeline.'_'.$queue);
            $tc->save($cmd);            
        }
    }
    
    public static function delete($pipeline,$queue) {
        
        Cache::forget($pipeline.'_'.$queue);
        R::connection()->srem($pipeline . "_queue", urldecode($queue));                
        
        //remove possible template command
        if ($pipeline == "CommandTemplates") {
            $tc = new HPCTemplateCmd($pipeline.'_'.$queue);
            $tc->delete();            
        }
        
    }

    public function count() {
        return R::connection()->llen($this->pipeline->name . '_queue_' . $this->name);
    }

    public function active_count() {
        $count = 0;
        foreach (HPCNode::all() as $node) {
            if ($node->stage() == $this->pipeline->name.'_queue_'.$this->name) {
                $count += count($node->files());
            }
        }
        return $count;
    }
    
    function files() {   
        return HPCFile::find($this->pipeline->name, $this->name);
    }
    
    public function clear() {
        
        $hasFiles = true;
        while($hasFiles)
        {
            $file = R::connection()->lpop($this->pipeline->name . '_queue_' . $this->name);
            if ($file) {
                //might not want to do this here
                //$f = new HPCFile($file);
                //$f->clear();
            } else {
                $hasFiles = false;
            } 
        }
        
    }

    public function __toString() {
        return $this->name();
    }

    public function name() {
        return $this->name;
    }

    function description() {    
        return R::connection()->get($this->pipeline->name . "_queue_desc_" . $this->name);        
    }
    
    function length() {    
        return R::connection()->llen($this->pipeline->name . '_queue_' . $this->name);        
    }

    function active() {   
        return R::connection()->get($this->pipeline->name . "_queue_active_" . $this->name);        
    }

    function queue_out() {      
        return R::connection()->get($this->pipeline->name . "_queue_out_" . $this->name);        
    }

    function queue_err() {
        return R::connection()->get($this->pipeline->name . "_queue_err_" . $this->name);        
    }

    function valid_check() {      
        return R::connection()->get($this->pipeline->name . "_queue_valid_" . $this->name);        
    }
    
    function cmdtype() {        
        return R::connection()->get($this->pipeline->name . "_queue_cmdtype_" . $this->name);        
    }

    function template_cmd() {      
        return R::connection()->get($this->pipeline->name . "_queue_template_" . $this->name);        
    }
    
    function cmd() {      
        return R::connection()->get($this->pipeline->name . "_queue_cmd_" . $this->name);        
    }
    
    function file_cnt() {  
        return R::connection()->get($this->pipeline->name . "_queue_files_" . $this->name);        
    }
    
    function file_output() {  
        return R::connection()->get($this->pipeline->name . "_queue_output_" . $this->name);        
    }
    
    function cmdParse() {
        
        $cmd = "";
        
        if ($this->cmdtype() == "template") {
            $c = new HPCTemplateCmd($this->template_cmd());        
            $cmd = $c->cmd();
        } else {
            $cmd = $this->cmd();
        }
                
        $output = array();
        exec("/usr/bin/python /var/www/html/laravel/process-util.py parsecmd ".$this->pipeline->name." '".$cmd."' 2>&1", $output);        
        
        $ret = "";
        foreach($output as $line) {
            $ret .= $line."<br/>";
        }
        return $ret;
        
    }
    
    public function moveFiles($fqs,$to) {
        return $this->load_queue_from_queue($this->pipeline->name, $fqs, $this->name, $to);        
    }
        
    private function load_queue_from_queue($pipeline, $fqs, $from, $to) {                                     
        
 //for some reason this only moves one file ... probably the intermixed commands are breaking the pipeline
//        $redis = R::connection('pipe');
//        $pipe = $redis->pipeline();
//        
//        foreach ($fqs as $fq) {
//            $qlen = R::connection()->llen($pipeline . '_queue_' . $from);
//            for ($x = 0; $x <= $qlen; $x++) {
//                $tmp = R::connection()->lpop($pipeline . '_queue_' . $from);                
//                if ($tmp != "") {                        
//                    if ($tmp == $fq)
//                        $pipe->rpush($pipeline . '_queue_' . $to, $tmp);     
//                    else
//                        $pipe->rpush($pipeline . '_queue_' . $from, $tmp);                                 
//                }
//            }
//        }          
//        
//        $pipe->execute();
        
        foreach ($fqs as $fq) {
            $qlen = R::connection()->llen($pipeline . '_queue_' . $from);
            for ($x = 0; $x <= $qlen; $x++) {
                $tmp = R::connection()->lpop($pipeline . '_queue_' . $from);                
                if ($tmp != "") {                        
                    if ($tmp == $fq)
                        R::connection()->rpush($pipeline . '_queue_' . $to, $tmp);                               
                    else
                        R::connection()->rpush($pipeline . '_queue_' . $from, $tmp);         
                }
            }
        }                             

    }

    private function load_queue($pipeline, $fqs) {
        if ($this->isConnected()) {
            $queuefiles = $this->get_queue_files($pipeline, 'start');
            foreach ($fqs as $fq) {
                if (!in_array($fq, $queuefiles)) {
                    $this->redis->rpush($pipeline . '_queue_start', $fq);
                }
            }
        }
    }
    
}