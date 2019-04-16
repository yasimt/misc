
<?php

	require_once('../config.php');
	require_once('includes/downselldetailsClass.php');


	if ($_REQUEST) {
		foreach ($_REQUEST as $key => $value) {
			$params[$key] = $value;
		}
	} else {
		header('Content-Type: application/json');
		$params = json_decode(file_get_contents('php://input'), true);
	}
	$params['sso_ip']=SSO_MODULE_IP;
	$down_obj = new DownselldetailsClass($params);
	if($params['action']==='showdetails'):
		$budget_service_arr = $down_obj->ShowDownselNotData();
    elseif ($params['action']==='acceptreq'):
        $budget_service_arr = $down_obj->acceptDownsell();
    elseif ($params['action']==='rejectreq'):
        $budget_service_arr = $down_obj->RejectDiscount();
    elseif ($params['action']==='RejectbeforeApproval'):
        $budget_service_arr = $down_obj->RejectbeforeApproval();
	endif;

	print($budget_service_arr);
