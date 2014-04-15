<?php

class NodeController extends \BaseController {
        
        public function view() {                                       
            $this->data['pipelines'] = HPCPipeline::all();
            $this->data['nodes'] = HPCNode::all();
            return View::make('hpc/node_list',$this->data);
        }
        
        public function setPipeline($node) {
            $n = new HPCNode($node);
            $n->setPipeline(Input::get('pipeline'));            
        }
}