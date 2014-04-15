<?php

class FileVarsController extends \BaseController {
        
    private $key = "gvars";
    
    public function index() {                                                   
        //$this->data['vars'] = HPCVar::all($this->key);
        //return View::make('hpc/global_vars',$this->data);
    }

    public function store($fq) {
        $var = new HPCVar("var_".$fq, Input::get('name'));
        $var->save(Input::get('val'));        
    }

    public function update($fq,$name) {
        $var = new HPCVar("var_".$fq,$name);
        return $var->save(Input::get('val'));            
    }

    public function destroy($fq,$name) {
        $var = new HPCVar("var_".$fq,$name);
        return $var->delete();                        
    }        
}