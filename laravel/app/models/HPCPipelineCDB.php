<?php

class HPCPipelineCDB {
    
    //ToDo: make a pipeline type for the command templates pipeline and have all respect it!
    public $name = "";
    private $pipeline = "";        
    
    function __construct($name) {
        $this->getPipelineByName($name);
        $this->name = $this->pipeline->name;
    }
    
    private function getPipelineByName($name) {
       
        $client = new couchClient (Settings::COUCHDB_HOST,  Settings::COUCHDB_DB);
        
        $opts = array ( "include_docs" => False, "descending" => false, "key"=> $name );
        $pipelines = $client->setQueryParameters($opts)->getView('pipelines','all')->rows;
        
        if (count($pipelines)>0) {
            $this->pipeline = $pipelines[0]->value;            
        }
        
    }
    
    public static function all() {        
        
        $pipelines = array();                
        
        $client = new couchClient (Settings::COUCHDB_HOST,  Settings::COUCHDB_DB);
        
        //get pipelines
        $opts = array ( "include_docs" => False, "descending" => false );
        $pipes = $client->setQueryParameters($opts)->getView('pipelines','all');
        
        foreach( $pipes->rows as $pipeline ) {                
            $pipelines[] = new HPCPipeline($pipeline->value->name);
        }        
        
        return $pipelines;
    } 
    
    public static function create($pipeline) {                                 
                
        $client = new couchClient (Settings::COUCHDB_HOST,  Settings::COUCHDB_DB);
        
        $doc = new couchDocument($client);
        $doc->type = 'pipeline';
        $doc->name = $pipeline;
        $id = $doc->id();

        $doc = new couchDocument($client);
        $doc->type = 'queue';
        $doc->name = 'start';
        $doc->active = True;
        $doc->nextq = 'None';
        $doc->errq = 'None';
        $doc->filecnt = 'MAX_CPU';
        $doc->validchk = 'check_default';
        $doc->pipeline = $id;

        $doc = new couchDocument($client);
        $doc->type = 'queue';
        $doc->name = 'error';
        $doc->active = True;
        $doc->nextq = 'None';
        $doc->errq = 'None';
        $doc->pipeline = $id;

        $doc = new couchDocument($client);
        $doc->type = 'queue';
        $doc->name = 'done';
        $doc->active = True;
        $doc->nextq = 'None';
        $doc->errq = 'None';
        $doc->pipeline = $id;

        $doc = new couchDocument($client);
        $doc->type = 'queue';
        $doc->name = 'None';
        $doc->pipeline = $id;                       
        
    }
    
    public static function delete($pipeline) {                
        
        $p = new HPCPipeline($pipeline);                        
                
        //remove queues
        foreach ($p->queues() as $queue) {            
            $queue->delete($p->name, $queue->name());
        }
        
        $client = new couchClient (Settings::COUCHDB_HOST,  Settings::COUCHDB_DB);
        $doc = $client->getDoc($p->id());
        $client->deleteDoc($doc);                                
        
    }
    
    public function id() {
        return $this->pipeline->_id;
    }
    
    public function queues() {   
                        
        $client = new couchClient (Settings::COUCHDB_HOST,  Settings::COUCHDB_DB);
        
        $queues = array();
        $opts = array ( "include_docs" => False, "descending" => false, "key"=> $this->id() );
        $qs = $client->setQueryParameters($opts)->getView('pipelines','queues')->rows;
        foreach ($qs as $queue) {
            $queues[] = new HPCQueue($this,$queue->value->name);
        }
        return $queues;
        
    }
    
    //ToDo: setup vars like globals??? but might be to complex??
    public function vars() {                
        
        $vars['genome'] = R::connection()->hget($this->name,'ref_genome');
        $vars['vcf'] = R::connection()->hget($this->name,'ref_vcf');
        $vars['bed'] = R::connection()->hget($this->name,'ref_bed');
        $vars['bait'] = R::connection()->hget($this->name,'ref_bait');
        $vars['target'] = R::connection()->hget($this->name,'ref_target');
        
        return $vars;
    }    
    
    public function updateVars($genome,$vcf,$bed,$bait,$target) {
        R::connection()->hset($this->name,'ref_genome',$genome);
        R::connection()->hset($this->name,'ref_vcf',$vcf);
        R::connection()->hset($this->name,'ref_bed',$bed);
        R::connection()->hset($this->name,'ref_bait',$bait);
        R::connection()->hset($this->name,'ref_target',$target);
    }
    
    public function nodes() {
        $nodes = array();
        $node_names = R::connection()->smembers("nodes");
        asort($node_names);
        foreach ( $node_names as $node ) {
            if ($node == $this->name ) {
                $nodes[] = new HPCNode($node);
            }
        }  
        return $nodes();
    }
    
    public function startFiles($fqs) {     
        //ToDo: have this check for in-process files (HPCNode::files()) ... or not start while nodes are working?
        foreach ($fqs as $fq) {                   
            $found = false;
            foreach($this->queues() as $queue) {                
                foreach($queue->files() as $file) {
                    if ($fq == $file->name()) {
                        $found = true;
                    }
                }
            }
            
            if (!$found) {
                R::connection()->rpush($this->name . '_queue_start', $fq);
            }
        }        
    }
    
    //ToDo: this is poor! need to seperate out functions and get the html over into the view
    //      as we move this into couch have couch do the work for making the document to display
    public function getView() {
        return $this->visual($this->name);
    }        
    
    function get_stages($pipeline,&$output_js) {                                                                      
        
        $output="";        
        $sorted_queues = array();
                
        $queues = $this->queues();        
        $sorted_queues = array(new HPCQueue($this->name, 'error'),new HPCQueue($this->name, 'start'));
                        
        while(count($sorted_queues) < count($queues)) {
                        
            $nextq = new HPCQueue($this->name, $sorted_queues[count($sorted_queues)-1]->queue_out());          
            $errq = new HPCQueue($this->name, $sorted_queues[count($sorted_queues)-1]->queue_err());
            
            if ($nextq == NULL) break;
            if ($errq->name() != "error") $sorted_queues[] = $errq;
            $sorted_queues[] = $nextq;
            
        }       
        $sorted_queues[] = new HPCQueue($this->name, 'error');
        $sorted_queues = array_unique($sorted_queues);     
        
        $top = 0;        
        foreach ($sorted_queues as $queue) {
        
            $top_offset = 6;
            
            $errq = $queue->queue_err();
            
            $div_top = 5+($top*14)+$top_offset;
            $div_left = 40; //+($top*2);
            
            if ($queue->name() == "start") {
                $div_top = 15+$top_offset;
                $div_left = 5;    
            }
            elseif ($queue->name() == "error") {
                $div_top = 15+$top_offset;
                $div_left = 90;
            }
            elseif ($queue->name() == "done") {
                $div_top = 5+(($top+1)*14)+$top_offset;
                $div_left = 5;
            }
            elseif ($queue->name() == "None") {                
                $div_left = 90;
            }
            elseif ($queue->queue_err() != "done" && $queue->queue_err() != "error") {
                $div_left = 5;
            }
        
            $friendly_q = str_replace(".","",str_replace("->", "", $queue->name()));
            $output .= "<div title='".$queue->description()."' class='component window' style='position:absolute;top:".$div_top."em;left:".$div_left."em;' id='".$friendly_q."'>";
            
            $output .= "<table style='margin: 2px;width: 97%' class='hovertable'>";
            $output .= "<tr><th colspan='2'>".$queue->name()."</th></tr>";            
            $output .= "<tr><td>status</td><td>";
            if ($queue->active()) {
                $output .= "active";
            } else {
                $output .= "<div style='color: darkorange;'>inactive</div>";
            }
            $output .= "</td></tr>";
            $output .= "<tr><td>files</td><td>".$queue->file_cnt()."</td></tr>";
            $output .= "<tr><td>queued</td><td>".$queue->length()."</td></tr>";
            $output .= "</table>";
            
            $output .= "</div>";                    
            
            $nextq = str_replace(".","",str_replace("->", "",$queue->queue_out()));
            $errq =  str_replace(".","",str_replace("->", "", $queue->queue_err()));
            $output_js .= "jsPlumb.connect({source:'".$friendly_q."',target:'".$nextq."'}, connector_out);";
            $output_js .= "jsPlumb.connect({source:'".$friendly_q."',target:'".$errq."'}, connector_err);";
            
            $top++;
        }
        
        return $output;
    }
    
        
    function visual($pipeline) {        
            
        $output_stage = "";
        $output_js = "";  

        $output_js_start = "<script>jsPlumb.ready(function() {";
        $output_js_start .= "var connector_out = {connector:'Straight',paintStyle:{lineWidth:3,strokeStyle:'#00FF00'},hoverPaintStyle:{strokeStyle:'#dbe300'},endpoint:'Blank',anchor:'Continuous',overlays:[ ['Arrow', {location:0.5, width:15, length:12} ]]};";                           
        $output_js_start .= "var connector_err = {connector:'Straight',paintStyle:{lineWidth:3,strokeStyle:'#FF0000'},hoverPaintStyle:{strokeStyle:'#dbe300'},endpoint:'Blank',anchor:'Continuous',overlays:[ ['Arrow', {location:0.5, width:15, length:12} ]]};";                           
        $output_js_start .= "jsPlumb.draggable(jsPlumb.getSelector('.window'), { containment:'#body'});";
        $output_js_end = "});</script>";                      

        $output_stage .= $this->get_stages($pipeline,$output_js);                     

        $breaks = "";
        for ($i = 0; $i < count($this->queues())*9; $i++) {
            $breaks .= "<br/>";
        }
        
        
        return $output_js_start.$output_js.$output_js_end.$breaks.$output_stage;                   
        
    }
    
}