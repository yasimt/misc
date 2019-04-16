<?php
class messageBroadcast extends Controller {
    function __construct() {
        parent::__construct();
    }
    function messageDetails() {
		
		echo $this->model->getEmpMessageDetails();
	}
	function messageUpdates() {
		
		echo $this->model->getEmpMessageUpdates();
	}
}
