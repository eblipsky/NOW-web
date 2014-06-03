<?php

class HPCPipeline {
    
    //ToDo: make a pipeline type for the command templates pipeline and have all respect it!
    
    public $name = "";    
       
    function __construct($name) {
        $this->name = $name;        
    }
    
    public static function all() {        
        
        $pipelins = array();
        
        $sorted = R::connection()->smembers("pipeline");
        asort($sorted);
        foreach ($sorted as $name) {
            $pipelins[] = new HPCPipeline($name);
        }
        
        return $pipelins;
    } 
    
    public static function create($pipeline) {                                 
                       
        R::connection()->sadd("pipeline", $pipeline);
        
        //add default queues in
        R::connection()->sadd($pipeline . "_queue", "start");
        R::connection()->set($pipeline . "_queue_active_start", 'active');
        R::connection()->set($pipeline . "_queue_out_start", 'None');
        R::connection()->set($pipeline . "_queue_err_start", 'None');
        R::connection()->set($pipeline . "_queue_files_start", 'MAX_CPU');
        R::connection()->set($pipeline . "_queue_valid_start", 'check_default');

        R::connection()->sadd($pipeline . "_queue", "error");
        R::connection()->set($pipeline . "_queue_active_error", 'active');
        R::connection()->set($pipeline . "_queue_out_error", 'None');
        R::connection()->set($pipeline . "_queue_err_error", 'None');

        R::connection()->sadd($pipeline . "_queue", "done");
        R::connection()->set($pipeline . "_queue_active_done", 'active');
        R::connection()->set($pipeline . "_queue_out_done", 'None');
        R::connection()->set($pipeline . "_queue_err_done", 'None');

        R::connection()->sadd($pipeline . "_queue", "None");
    }
    
    public static function delete($pipeline) {
        R::connection()->srem("pipeline", $pipeline);
    }
    
    public function queues() {      
        $queues = array();
        $sorted = R::connection()->smembers($this->name . "_queue");
        asort($sorted);
        foreach ($sorted as $name) {            
            $queues[] = new HPCQueue($this,$name);                                  
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
        
        $files = array();
        $queues = $this->queues();
        foreach ($queues as $queue) {
            $files = array_merge( $files, $queue->files() );
        }               
        
        foreach ($fqs as $fq) {      
            
            $queued = false;
            $inprocess = false;
            
            $file = new HPCFile($fq);
            $queued = in_array($file, $files);
            
            $file = new HPCFile($fq);
            $inprocess = $file->inProcess();           
            
            if ( !$queued && !$inprocess ) {                
                R::connection()->rpush($this->name . '_queue_start', $fq);                
            }
        }    
        
    }
    
    //ToDo: this is poor! need to seperate out functions and get the html over into the view
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
