<?php

require_once('../config.php');
require_once('includes/budget_cal_class.php');
//CREATED BY PRANLIN
//http://pranlin.jdsoftware.com/jdbox/services/budgetCal.php?act=allCity
//http://pranlin.jdsoftware.com/jdbox/services/budgetCal.php?act=remote
//http://pranlin.jdsoftware.com/jdbox/services/budgetCal.php?act=add
//http://pranlin.jdsoftware.com/jdbox/services/budgetCal.php?catid=305&data_city=Mumbai&act=bid
header('Content-Type: application/json');

if ($_REQUEST) {
    foreach ($_REQUEST as $key => $value) {
        $params[$key] = $value;
    }
} else {
    header('Content-Type: application/json');
    $params = json_decode(file_get_contents('php://input'), true);
}

$budget_service_obj = new Budget_cal_class($params);

switch ($params['act']) {
    case 'allCity':
        $budget_service_arr = $budget_service_obj->fetchAllCity();
        break;
    case 'remote':
        $budget_service_arr = $budget_service_obj->fetchRemoteCity();
        break;
    case 'add':
        $budget_service_arr = $budget_service_obj->addCalculationRqst();
        break;
    case 'bid':
        $budget_service_arr = $budget_service_obj->checkBiddable();
        break;

    default:
        $budget_service_arr = json_encode(array('error' => 99, 'msg' => 'unknown request'));
        break;
}

print($budget_service_arr);
