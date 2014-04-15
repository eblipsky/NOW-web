<?php

class HPCBatchController extends BaseController {

    function __construct() {
        parent::__construct();
        $this->data['content_title'] = "HPC Batch Manager";
    }
    
    public function index()
    {                                
        $this->data['batches'] = HPCBatch::all();
        return View::make('hpc/batch_list',$this->data);
    }

    public function create()
    {                                        
        return View::make('hpc/batch_create',$this->data);
    }    

    public function show($name)
    {   
        $batch = new HPCBatch($name);
        $this->data['batch'] = $batch; 
        return View::make('hpc/batch_show',$this->data);
    }   
    
    public function progress($name)
    {   
        $batch = new HPCBatch($name);
        $this->data['batch'] = $batch; 
        return View::make('hpc/batch_progress',$this->data);
    }   
    
    public function edit($name)
    {                           
        $batch = new HPCBatch($name);
        $this->data['batch'] = $batch;                
        $this->data['fqs'] = json_encode($batch->files(false));
        $this->data['used_pipelines'] = $batch->pipelines();
        foreach (HPCPipeline::all() as $p) {
            if (! in_array($p, $this->data['used_pipelines'])) {
                if ($p->name != "CommandTemplates") {
                    $avail[] = $p;
                }
            }
        }
        $this->data['available_pipelines'] = $avail;                                          
        
        return View::make('hpc/batch_edit',$this->data);
    }  

    public function start($name)
    {                     
        $batch = new HPCBatch($name);                
        $batch->start();                     
        //ToDo: return some state of success from start() 
    } 
    
    public function update($name)
    {                     
        $batch = new HPCBatch($name);        
        $batch->addFiles(Input::get('fqs'));        
        $batch->addPipelines(Input::get('queues'));        
              
        //return $this->edit($name);        
    }  
    
    public function store()
    {                                            
        $batch = HPCBatch::create(Input::get('batch_name'));          
        return Redirect::to('/hpc/batches/'.$batch->name().'/edit');        
    }    
    
}
