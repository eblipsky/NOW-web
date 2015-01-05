<?php

class BaseController extends Controller {

    protected $data = array();
    
    function __construct() {

        $logo = new HPCSetting("LOGO");
        $company = new HPCSetting("COMPANY");
        $division = new HPCSetting("DIVISION");

        $this->data['logo'] = $logo->value();
        $this->data['company'] = $company->value();
        $this->data['division'] = $division->value();

    }
    
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

}
