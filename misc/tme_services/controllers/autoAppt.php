<?php 
/**
* Controller created to handle Garb And non grab flow
* Created by Apoorv Agrawal
* Date : 14-06-2017
*/
class autoAppt extends Controller{	
	public function __construct(){
		# code...
		 parent::__construct();
	}
	public function get_view_exec_rec(){
		echo $this->view->autoAppt		=	$this->model->get_view_exec_rec();
	}
	public function get_click_alloc_fresh(){
		echo $this->view->autoAppt		=	$this->model->get_click_alloc_fresh();
	}
	public function get_click_continue_followUp(){
		echo $this->view->autoAppt		=	$this->model->get_click_continue_followUp();
	}
	public function get_fersh_alloc(){
		echo $this->view->autoAppt		=	$this->model->get_fersh_alloc();
	}
	public function insert_menu_link(){
		echo $this->view->autoAppt = $this->model->insert_menu_link();
	}
}
?>
