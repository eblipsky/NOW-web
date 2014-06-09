<?php


class EXDataController extends BaseController {
    
        public function __construct() {
            $this->data['content_title'] = "External Case Data";
        }
	public function index()
	{
            $this->data['types'] = CaseTypes::all();
            $this->data['drives'] = Drive::all();
            $this->data['vendors'] = Vendor::all();
            $this->data['cases'] = EXCase::all();             
            $this->data['canedit'] = isset($_SERVER['PHP_AUTH_USER']);
            return View::make('exdata/list',$this->data);            
	}
        
        public function createcase()
	{               
            $this->data['types'] = CaseTypes::all();
            $this->data['drives'] = Drive::all();
            $this->data['vendors'] = Vendor::all();            
            return View::make('exdata/createcase',$this->data);            
	}
        
        public function storecase() 
        {
            EXCase::store(Input::get("ghs_case"), 
                            Input::get("vendor_case"), 
                            Input::get("vendor"), 
                            Input::get("individuals"), 
                            Input::get("type"), 
                            Input::get("drive"));
            return Redirect::to('/exdata/case');            
        }

        public function editcase($id)
	{
            $this->data['types'] = CaseTypes::all();
            $this->data['drives'] = Drive::all();
            $this->data['vendors'] = Vendor::all();
            $this->data['case'] = new EXCase($id);
            return View::make('exdata/editcase',$this->data);            
	}
        
        public function updatecase($id) 
        {
            EXCase::update($id, 
                            Input::get("ghs_case"), 
                            Input::get("vendor_case"), 
                            Input::get("vendor"), 
                            Input::get("individuals"), 
                            Input::get("type"), 
                            Input::get("drive"));
            return Redirect::to('/exdata/case');            
        }
        
        public function createvendor()
	{                                    
            return View::make('exdata/createvendor',$this->data);            
	}
        
        public function storevendor() 
        {
            return Redirect::to('/exdata/case');            
        }
        
        public function createdrive()
	{                           
            $this->data['vendors'] = Vendor::all();            
            return View::make('exdata/createdrive',$this->data);            
	}
        
        public function storedrive() 
        {
            Drive::store(Input::get('vendor'), Input::get('serial'));             
            return Redirect::to('/exdata/case');            
        }
        
}
