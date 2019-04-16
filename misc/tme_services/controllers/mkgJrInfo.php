<?php 
/**
* Controller created to handle Garb And non grab flow
* Created by Apoorv Agrawal
* Date : 15-03-2016
*/
class mkgJrInfo extends Controller{
	
	function __construct(){
		# code...
		 parent::__construct();
	}

	function visiting_card(){
		echo $this->view->mkgJrInfo		=	$this->model->visiting_card();
	}
	function updateVistingCard(){
		echo $this->view->mkgJrInfo		=	$this->model->updateVistingCard();
	}
	/* Function to get timings based on grab and non grab flow */
	public function get_me(){
		echo $this->view->mkgJrInfo 	=	$this->model->get_me();
	}
	/* Function added to get melogin for AllMe Data */
	public function allMelogin(){
		echo $this->view->mkgJrInfo 	=	$this->model->allMelogin();
	}
	public function addAlternateAddress(){
		echo $this->view->mkgJrInfo 	=	$this->model->addAlternateAddress();
	}
	public function get_state_id($state_name){
		echo $this->view->mkgJrInfo 	=	$this->model->get_state_id($state_name);
	}
	public function get_city_id($ct_name){
		echo $this->view->mkgJrInfo 	=	$this->model->get_city_id($ct_name);
	}
	public function ecsTransfer(){
		echo $this->view->mkgJrInfo 	=	$this->model->ecsTransfer();
	}
	public function iroAppTransfer(){
		echo $this->view->mkgJrInfo 	=	$this->model->iroAppTransfer();
	}
	public function iroAppSaveExit(){
		echo $this->view->mkgJrInfo 	=	$this->model->iroAppSaveExit();
	}
	public function proceedCompany(){
		echo $this->view->mkgJrInfo 	=	$this->model->proceedCompany();
	}
	public function meisabsent(){
		echo $this->view->mkgJrInfo 	=	$this->model->meisabsent();
	}
	public function getAreaXHR(){
		
		echo $this->view->mkgJrInfo 	=	$this->model->getAreaXHR();
	}
}
?>
