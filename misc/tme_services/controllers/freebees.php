<?php

class freebees extends Controller {
    function __construct() {
        parent::__construct();
    }
    
    function getEmpInfo() {
		echo $this->view->freebees	=	$this->model->getEmpInfo();
	}
	
	function insertfreebees(){
		echo $this->view->contractInfo  =   $this->model->insertfreebees();
	}
	
	function checkfreebees(){
		echo $this->view->contractInfo  =   $this->model->checkfreebees();
	}
	
	function getFreebeesInfo() {
		echo $this->view->freebees	=	$this->model->getFreebeesInfo();
	}
	
	function updateDetails(){
		echo $this->view->freebees	=	$this->model->updateDetails();
	}
	
	function resetfreebeesInfo(){
		echo $this->view->freebees	=	$this->model->resetfreebeesInfo();
	}
	
}

?>
