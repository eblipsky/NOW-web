<?php

class TemplateCmdController extends \BaseController {       
    
    public function index() {                                                   
        $this->data['cmds'] = HPCTemplateCmd::all();
        return View::make('hpc/template_cmd',$this->data);
    }

    public function store() {
        $var = new HPCTemplateCmd(Input::get('name'));
        $var->save(Input::get('cmd'));        
    }

    public function update($name) {
        $var = new HPCTemplateCmd($name);
        return $var->save(Input::get('cmd'));            
    }

    public function destroy($name) {
        $var = new HPCTemplateCmd($name);
        return $var->delete();                        
    }        
}