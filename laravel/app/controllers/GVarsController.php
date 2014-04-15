<?php

class GVarsController extends \BaseController {
        
    private $key = "gvars";
    
    public function index() {                                                   
        $this->data['vars'] = HPCVar::all($this->key);
        return View::make('hpc/global_vars',$this->data);
    }

    public function store() {
        $var = new HPCVar($this->key, Input::get('name'));
        $var->save(Input::get('val'));        
    }

    public function update($name) {
        $var = new HPCVar($this->key,$name);
        return $var->save(Input::get('val'));            
    }

    public function destroy($name) {
        $var = new HPCVar($this->key,$name);
        return $var->delete();                        
    }        
}