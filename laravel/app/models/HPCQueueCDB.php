<?php

class HPCQueueCDB implements Countable {
    
    private $pipeline = "";
    private $queue = "";
    private $name = "";
       
    function __construct($pipeline,$name) {                        
        
        if (is_string($pipeline)) {            
            $this->pipeline = new HPCPipeline($pipeline);
        } else {            
            $this->pipeline = $pipeline;    
        }
        
        $this->getQueueByName($name);  
        
    }
    
    private function getQueueByName($name) {
       
        $name = urldecode($name);
        
        $client = new couchClient (Settings::COUCHDB_HOST,  Settings::COUCHDB_DB);
        
        $opts = array ( "include_docs" => False, "descending" => false, "key"=> $this->pipeline->id() );
        $queues = $client->setQueryParameters($opts)->getView('pipelines','queues')->rows;
                       
        if (count($queues)>0) {
            foreach ($queues as $queue) {
                if ($queue->value->name == $name) {                    
                    $this->queue = $queue->value;            
                    $this->name = $name;
                    return;
                }
            }
            
        }
        
    }
    
    private function getPipelineByName($name) {
       
        $client = new couchClient (Settings::COUCHDB_HOST,  Settings::COUCHDB_DB);
        
        $opts = array ( "include_docs" => False, "descending" => false, "key"=> $name );
        $pipelines = $client->setQueryParameters($opts)->getView('pipelines','all')->rows;
        
        if (count($pipelines)>0) {
            $this->pipeline = $pipelines[0]->value;            
        }
        
    }
    
    public function all() {
        return $this->pipeline->queues();
    }
    
    public function pipeline() {
        return $this->pipeline;
    }
    
    public static function create($pipeline,$queue) {
        
        $client = new couchClient (Settings::COUCHDB_HOST,  Settings::COUCHDB_DB);
        $p = new HPCPipeline($pipeline);
        
        $doc = new couchDocument($client);
        $doc->type = 'queue';
        $doc->pipeline = $p->id();
        $doc->name = $queue;
        $doc->active = False;
        $doc->nextq = 'None';
        $doc->errq = 'None';
        $doc->filecnt = 'MAX_CPU';
        $doc->validchk = 'check_default';
        $doc->pipeline = $id;                
        
//        if ($pipeline == "CommandTemplates") {
//            $tc = new HPCTemplateCmd($pipeline.'_'.$queue);
//            $tc->save("");            
//        }
    }
    
    function update($active,$out,$err,$valid,$templatecmd,$cmd,$cmdtype,$files,$desc) {
        
        $client = new couchClient (Settings::COUCHDB_HOST,  Settings::COUCHDB_DB);
        
        $doc = couchDocument::getInstance($client,$this->id());
                      
        $doc->active = ( isset($active) ? True : False);
        $doc->nextq = $out;
        $doc->errq = $err;
        $doc->validchk = $valid;
        $doc->tmplcmd = $templatecmd;
        $doc->cmd = $cmd;
        $doc->cmdtype = $cmdtype;        
        $doc->filecnt = $files;
        $doc->desc = $desc;
                
//        if ($pipeline == "CommandTemplates") {
//            $tc = new HPCTemplateCmd($pipeline.'_'.$queue);
//            $tc->save($cmd);            
//        }
        return $this->id();
    }
    
    public static function delete($pipeline,$queue) {
        
        $p = new HPCPipeline($pipeline);
        $q = new HPCQueue($p, $queue);
                        
        $client = new couchClient (Settings::COUCHDB_HOST,  Settings::COUCHDB_DB);
        $doc = $client->getDoc($q->id());        
        $client->deleteDoc($doc);  
            
    }
    
    public function id() {
        return $this->queue->_id;
    }

    public function count() {
        return R::connection()->llen($this->pipeline->name . '_queue_' . $this->name);
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
        if (isset($this->queue->desc)) {
            return $this->queue->desc;
        } else {
            return null;
        }
        
    }
    
    function length() {    
        return R::connection()->llen($this->pipeline->name . '_queue_' . $this->name);        
    }

    function active() {       
        if (isset($this->queue->active)) {
            return $this->queue->active;
        } else {
            return False;
        }        
    }

    function queue_out() {      
        return $this->queue->nextq;
    }

    function queue_err() {
        return $this->queue->errq;
    }

    function valid_check() {      
        return $this->queue->validchk;
    }
    
    function cmdtype() {        
        if (isset($this->queue->cmdtype)) {
            return $this->queue->cmdtype;
        } else {
            return null;
        }
        
    }

    function template_cmd() {        
        if (isset($this->queue->tmplcmd)) {
            return $this->queue->tmplcmd;
        } else {
            return null;
        }
        
    }
    
    function cmd() {      
        if (isset($this->queue->cmd)) {
            return $this->queue->cmd;
        } else {
            return null;
        }
        
    }
    
    function file_cnt() {  
        return $this->queue->filecnt;
    }

    function file_output() {  
        return $this->queue->fileoutput;
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