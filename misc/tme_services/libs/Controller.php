<?php

class Controller {

	function __construct() {
		//echo 'Main controller<br />';
		$this->view = new View();
	}
	
	public function loadModel($name) 
	{	
		$path = 'models/'.$this->rep_ascii($name).'_model.php';
		
		if (file_exists($path)) 
		{
			require 'models/'.$this->rep_ascii($name).'_model.php';
            
			$modelName = ucwords($this->rep_ascii($name)) . '_Model';
			//echo $modelName; die;
			$this->model = new $modelName();
		}		
	}
	
	public function rep_ascii($char) {
		return preg_replace('/[[:^print:]]/', '', $char);
	}
}
