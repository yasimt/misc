<?php 
/**
* 
*/
class dispositionInfo extends Controller{
	
	function __construct(){
		# code...
		 parent::__construct();
	}

	function insertDisposeData(){
		echo $this->view->locationInfo		=	$this->model->insertDisposeData();
	}
	function disposeFlow(){
		echo $this->view->locationInfo		=	$this->model->disposeFlow();
	}
	function tmeFeedback(){
		echo $this->view->locationInfo		=	$this->model->tmeFeedback();
	}
	function checkSlotForMe(){
		echo $this->view->locationInfo		=	$this->model->checkSlotForMe();
	}
	function create_otp(){
		echo $this->view->dispositionInfo		=	$this->model->create_otp();
	}
	function checkOTP_otp(){
		echo $this->view->dispositionInfo		=	$this->model->checkOTP_otp();
	}
	function insertAllMeDetails(){
		echo $this->view->dispositionInfo		=	$this->model->insertAllMeDetails();
	}
	function insertpincodeDetails(){
		echo $this->view->dispositionInfo		=	$this->model->insertpincodeDetails();
	}
}
?>
