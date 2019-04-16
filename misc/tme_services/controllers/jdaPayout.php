<?php

class jdaPayout extends Controller {
    function __construct() {
        parent::__construct();
    }

    function jdaPayoutProcess() {
		echo $this->view->jdaPayoutProcess	=	$this->model->jdaPayoutProcess();
	}
}
