<?php 
/**
* 
*/
class JdominiInfo extends Controller{
	
	function __construct(){
		# code...
		 parent::__construct();
	}

	function showTimingSlots(){
		echo $this->view->JdominiInfo		=	$this->model->showTimingSlots();
	}
	function showJDOMINIAppts(){
		echo $this->view->JdominiInfo		=	$this->model->showJDOMINIAppts();
	}
	function showGroupByJDOMINIData(){
		echo $this->view->JdominiInfo		=	$this->model->showGroupByJDOMINIData();
	}
	function getDetailsJDOmini(){
		echo $this->view->JdominiInfo		=	$this->model->getDetailsJDOmini();
	}
	function TmeCodeWiseData(){
		echo $this->view->JdominiInfo		=	$this->model->TmeCodeWiseData();
	}
	function crearteTable(){
		echo $this->view->JdominiInfo		=	$this->model->crearteTable();
	}
	function getTodayDate(){
		echo $this->view->JdominiInfo		=	$this->model->getTodayDate();
	}
	//~ function tmeFeedback(){
		//~ echo $this->view->locationInfo		=	$this->model->tmeFeedback();
	//~ }
	//~ function activateME(){
		//~ echo $this->view->locationInfo		=	$this->model->activateME();
	//~ }
}
?>
