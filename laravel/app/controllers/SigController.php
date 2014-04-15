<?php

class SigController extends \BaseController {
        
    public function __construct() {
        $this->data['content_title'] = "SIG Parse";
    }


    public function get() {                                                 
            return View::make('sig',$this->data);
        }
        
        public function post() { 
            $this->data['sig'] = Input::get('sig');
            $this->data['result'] = Sig::parse(Input::get('sig'));
            return View::make('sig',$this->data);
        }
             
}