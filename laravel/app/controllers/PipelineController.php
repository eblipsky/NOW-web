<?php

class PipelineController extends \BaseController {


	public function create()
	{   
         
            //ToDo: add validation to the submit side with messages!!
            if ( Input::get('pipeline') != "" ) {
                return HPCPipeline::create(Input::get('pipeline'));            
            }       
                                                 
	}

	public function delete($pipeline)
	{
            return HPCPipeline::delete($pipeline);         
	}
        
        public function pipeline_vars($pipeline) {
            
            $p = new HPCPipeline($pipeline);                       
            $this->data['vars'] = $p->vars();
            return View::make('hpc/pipeline_vars',$this->data);
        }
        
        public function updateVars($pipeline) {
            
            $p = new HPCPipeline($pipeline);         
            $p->updateVars(Input::get('ref_genome'), Input::get('ref_vcf'), Input::get('ref_bed'), Input::get('ref_bait'), Input::get('ref_target'));
        }
        
        public function view($pipeline) {                           
            
            $p = new HPCPipeline($pipeline);
                        
            $this->data['content_title'] = "Flow View ".$pipeline." Pipeline";
            $this->data['view'] = $p->getView();
            return View::make('hpc/viewer',$this->data);
        }
        
        public function startFiles() {    
            
            $p = new HPCPipeline(Input::get('pipeline'));                        
            return $p->startFiles(Input::get('fqs'));
        }
         
        public function clear($pipeline) {                        
            
            $p = new HPCPipeline($pipeline);            
            foreach($p->queues() as $queue) {              
                $queue->clear();
            }                    
        }
}