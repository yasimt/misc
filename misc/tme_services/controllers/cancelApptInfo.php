<?php
class cancelApptInfo extends Controller {
    function __construct() {
        parent::__construct();
    }
    function cancel_appt() {
		echo $this->view->cancelApptInfo	=	$this->model->cancel_appt();
	}
	

}
