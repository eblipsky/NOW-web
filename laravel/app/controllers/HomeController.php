<?php

class HomeController extends BaseController {

	public function index()
	{            
            $this->data['content_title'] = "Home";
            return View::make('home',$this->data);
	}

}
