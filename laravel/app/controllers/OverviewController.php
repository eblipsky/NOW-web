<?php

class OverviewController extends \BaseController {            

    function __construct() {
        $this->data['content_title'] = "HPC Overview";
    }
    
    public function index() {        
        $this->data['pipelines'] = HPCPipeline::all();        
        //foreach ($this->data['pipelines'] as $pipeline) {
        //    
        //}
        return View::make('hpc/overview',$this->data);
    }

    public function overview($pipeline) {
                
        $files = array();                                       
        $done_counts = array();
        $errored = array();
        $nodes = HPCNode::all();
        $queues = HPCPipeline::flow($pipeline);
        $p = new HPCPipeline($pipeline);
        
        foreach ($p->queues() as $queue) {
            if ($queue->name() == "error") {
                foreach($queue->files() as $file) { 
                    $key = explode('.', $file->name());
                    $key = $key[0];                
                    $files[] = array('name' => $file->name(), 'base' => $key, 'queue' => 'error', 'inprocess' => false);                
                    $errored[$key] = true;
                }
                break;
            }
        }

        foreach($queues as $queue) {
            $qname = $queue->name();
            foreach($queue->files() as $file) { 
                $key = explode('.', $file->name());
                $key = $key[0];                                                
                $files[] = array('name' => $file->name(), 'base' => $key, 'queue' => $qname, 'inprocess' => false);                
                if ($qname == "done") {
                    if (isset($done_counts[$key])) {
                        $done_counts[$key] += 1;
                    } else {
                        $done_counts[$key] = 1;
                    }
                }
            }            
        }        
        foreach ($nodes as $node) {      
            if ($node->pipeline() == $pipeline) {
                foreach ($node->files() as $fq) {                        
                    $file = new HPCFile($fq);
                    $key = explode('.', $file->name());
                    $key = $key[0];                                        
                    $files[] = array('name' => $file->name(), 'base' => $key, 'queue' => str_replace($pipeline.'_queue_','',$node->stage()), 'inprocess' => true);
                }
            }
        }        
                
        $cnts = array();
        $x = array();
        foreach ($files as $file) {             
            $x[$file['base']][] = $file;            
        }                        
        asort($x);
        
        $percents = array();
        foreach ($x as $key => $value) {              
            if ( isset($done_counts[$key])) {
                $avg = round(($done_counts[$key] / count($value))*100,0);
                $percents[$key] = $avg;
            } else {
                $percents[$key] = 0;
            }            
        }      
  
        //need to do an inprocess column and add the error queue
        
        $this->data['files'] = $x;        
        $this->data['percents'] = $percents;
        $this->data['errored'] = $errored;
        $this->data['queues'] = $queues;
        return View::make('hpc/overview_details',$this->data);
    }
    
    public function overview2($pipeline) {                
                        
        $queues = HPCPipeline::flow($pipeline);                
        $files = array(); 
        $p = new HPCPipeline($pipeline);        
        foreach ($p->queues() as $queue) {            
            $files = array_merge( $files, $queue->files() );
        }
        $nodes = HPCNode::all();
        foreach ($nodes as $node) {      
            if ($node->pipeline() == $pipeline) {
                foreach ($node->files() as $fq) {
                    $files[] = new HPCFile($fq);
                }                
            }
        }                
                
        $percents = array();
        $cnts = array();
        $x = array();
        foreach ($files as $file) {                      
            $key = explode('.', $file->name());
            $key = $key[0];                                    
            if (isset($percents[$key])) {
                $percents[$key] += $this->percent_complete($queues, $file);
                $cnts[$key] += 1;
                $x[$key] .= ",".$file->queue();
            } else {
                $percents[$key] = $this->percent_complete($queues, $file);
                $cnts[$key] = 1;
                $x[$key] = $file->queue();
            }                
        }                             
        
        ksort($files);
        
        foreach ($percents as $key => $value) {
            $percents[$key] = round((($value / 100) / $cnts[$key]) * 100, 0);
        }
                        
        $this->data['files'] = $files;
        $this->data['percents'] = $percents;
        $this->data['queues'] = $queues;
        $this->data['cnts'] = $cnts;
        $this->data['x'] = $x;
        return View::make('hpc/overview_details',$this->data);
    }
    
    function percent_complete($queues, $file) {
                          
        $file_progress = array_search($file->queue(), $queues)+2;                                        
        return round(($file_progress / count($queues) ) * 100,0);        
        
    }
    
}
