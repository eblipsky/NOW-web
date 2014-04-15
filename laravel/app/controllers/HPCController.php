<?php

class HPCController extends BaseController {

    function __construct() {
        parent::__construct();
        $this->data['content_title'] = "HPC Pipeline";
    }
    
    public function monitor()
    {                        
        $this->data['current_time'] = Carbon\Carbon::now();
        $this->data['pipelines'] = HPCPipeline::all();
        return View::make('hpc/monitor',$this->data);
    }

//    public function editor()
//    {                        
//        $this->data['pipelines'] = HPCPipeline::all();
//        return View::make('hpc/editor',$this->data);
//    }   
    
    public function allfiles() {
        $this->data['fqs'] = HPCFile::all();
        return View::make('hpc/file_add',$this->data);
    }  
    
}
