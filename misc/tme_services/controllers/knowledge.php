<?php
class Knowledge extends Controller {

    function __construct() {
        parent::__construct();
        
    } 
   
    
	function getalldata() {	
		
     echo	$this->view->file = $this->model->getalldata();
	
	}
	function getalldata_count() {
		
     echo	$this->view->file = $this->model->getalldata_count();
	
	}
	function fetchall_autosuggest_tmegenio() {
		
     echo	$this->view->file = $this->model->fetchall_autosuggest_megenio();
	
	}
	function getalldata_mandatory() {
		
     echo	$this->view->file = $this->model->getalldata_mandatory();
	
	}
	function getalldata_mandatory_popup() {
		
     echo	$this->view->file = $this->model->getalldata_mandatory_popup();
	
	}
	function teamtype() {
		
     echo	$this->view->file = $this->model->teamtype();
	
	}
	
}
	

?>
