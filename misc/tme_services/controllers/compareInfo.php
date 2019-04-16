<?php
class compareInfo extends Controller {
    function __construct() {
        parent::__construct();
    }
    
    function compareBform($contractid) {
		echo $this->view->compareInfo	=	$this->model->compareBform($contractid);
	}
	
    function compareLocation($contractid) {
		echo $this->view->compareInfo	=	$this->model->compareLocation($contractid);
	}
	
	function insertLogBformDC() {
		echo $this->view->conpareInfo	=	$this->model->insertLogBformDC();
	}
	function compareMisc($parentid) {
		echo $this->view->conpareInfo	=	$this->model->compareMisc($parentid);
	}
	function getPreferedLanguage() {
		echo $this->view->conpareInfo	=	$this->model->getPreferedLanguage();
	}
}
