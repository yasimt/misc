<?php
class areaPincodeInfo extends Controller {
    function __construct() {
        parent::__construct();
    }
    
    function getAllArea() {
		echo $this->view->pincodeareaInfo	=	$this->model->getAllArea();
	}
	
	function setAreaPincodeInfo() {
		echo $this->view->pincodeareaInfo	=	$this->model->setAreaPincodeInfo();
	}
	
	function getAllPincodes($parentid,$data_city) {
		echo $this->view->pincodeareaInfo	=	$this->model->getAllPincodes($parentid,$data_city);
	}
}
