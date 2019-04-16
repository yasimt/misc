<?php
class process extends Controller {
    function __construct() {
        parent::__construct();
    }

    function incentiveProcess() {
		echo $this->view->tmeinfo	=	$this->model->incentiveProcess();
	}
    function processDataNew() {
		echo $this->view->tmeinfo	=	$this->model->processDataNew();
	}
    function processDataNewClash() {
		echo $this->view->tmeinfo	=	$this->model->processDataNewClash();
	}
    function processDataNewRules() {
		echo $this->view->tmeinfo	=	$this->model->processDataNewRules();
	}
    function processLateReturn() {
		echo $this->view->tmeinfo	=	$this->model->processLateReturn();
	}
    function setCalcFlag() {
		echo $this->view->tmeinfo	=	$this->model->setCalcFlag();
	}
    function updateTable() {
		echo $this->view->tmeinfo	=	$this->model->setCalcFlag();
	}
}
