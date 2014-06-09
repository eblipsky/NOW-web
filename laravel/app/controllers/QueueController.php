<?php

class QueueController extends \BaseController {        
    
	public function queues($pipeline)
	{
            $p = new HPCPipeline($pipeline);                     
            $queues = $p->queues();
            $this->data['queues'] = $queues;
            return View::make('hpc/queue_list',$this->data);
	}

	public function queue($pipeline,$queue)
	{		
            $p = new HPCPipeline($pipeline); 
            $q = new HPCQueue($pipeline, $queue);       
                       
            $this->data['queue'] = $q;
            $this->data['queues'] = $p->queues();
            $this->data['cmds'] = HPCTemplateCmd::all();
            $this->data['files'] = HPCFile::find($pipeline,$queue);
            return View::make('hpc/queue_edit',$this->data);
            
	}

        public function movefiles() {            
            $qFrom = new HPCQueue(Input::get('pipeline'), Input::get('from'));            
            return $qFrom->moveFiles(Input::get('fqs'), Input::get('to'));                        
        }

        public function create($pipeline)
	{                    
            return HPCQueue::create($pipeline, Input::get('queue'));   
	}

	public function delete($pipeline,$queue)
	{            
            return HPCQueue::delete($pipeline, $queue);    
	}
        
        public function update($pipeline,$queue)
	{
            $q = new HPCQueue($pipeline, $queue); 
                     
            $q->update(Input::get('queue_active'), 
                        Input::get('queue_out'), 
                        Input::get('queue_err'), 
                        Input::get('valid_check'), 
                        Input::get('queue_template'),
                        trim(Input::get('version_cmd')),
                        trim(Input::get('queue_cmd')),
                        Input::get('queue_cmdtype'), 
                        Input::get('queue_files_cnt'),
                        Input::get('queue_output'),
                        Input::get('queue_desc'));
            
	}

}