<?php

require_once('../config.php');
require_once('includes/approval_update_class.php');
//CREATED BY PRANLIN
//http://pranlin.jdsoftware.com/jdbox/services/approvalUpdates.php?ucode=10041420&amount=2&act=fcv&data_city=mumbai


if ($_REQUEST) {
    foreach ($_REQUEST as $key => $value) {
        $params[$key] = $value;
    }
} else {
    header('Content-Type: application/json');
    $params = json_decode(file_get_contents('php://input'), true);
}

$budget_service_obj = new Approval_update_class($params);

switch ($params['act']) {
    case 'fcv':
        $budget_service_arr = $budget_service_obj->approveFcv();
        break;
    case 'refund':
        $budget_service_arr = $budget_service_obj->approveRefund();
        break;
    case 'fundtrn':
        $budget_service_arr = $budget_service_obj->approveFundTrn();
        break;

    default:
        $budget_service_arr = json_encode(array('error' => 99, 'msg' => 'unknown request'));
        break;
}

echo $budget_service_arr;
