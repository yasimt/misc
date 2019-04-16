<?php

//CREATED BY PRANLIN

//DOWN SELL
//pranlin.jdsoftware.com/jdbox/services/getBudgetService.php?module=ME&a_amt=100&p_amt=90&c_name=Sushama&c_id=111&p_id=A000.XXX.BBKGFM222XX&vrsn=23&company=BBC NETWORK&typ=Proposal&act=add&data_city=mumbai
//pranlin.jdsoftware.com/jdbox/services/getBudgetService.php?module=TME&p_id=A000.XXX.AAKGFM123XX&vrsn=13&act=check&data_city=mumbai
//pranlin.jdsoftware.com/jdbox/services/getBudgetService.php?p_id=A000.XXX.AAKGFM123XX&vrsn=13&act=dumpa&data_city=mumbai
//pranlin.jdsoftware.com/jdbox/services/getBudgetService.php?p_id=PXX22.XX22.161107180209.T3K3&vrsn=13&act=status&data_city=mumbai
//pranlin.jdsoftware.com/jdbox/services/getBudgetService.php?p_id=PXX22.XX22.161107180209.T3K3&vrsn=13&act=upDC&data_city=mumbai

//FCV
//pranlin.jdsoftware.com/jdbox/services/getBudgetService.php?module=ME&amt=100&c_name=Sushama&c_id=111&p_id=A000.XXX.BBKGFM222XX&company=BBC NETWORK&reason=Inventory return&act=addfcv&data_city=mumbai
//pranlin.jdsoftware.com/jdbox/services/getBudgetService.php?module=TME&p_id=A000.XXX.AAKGFM123XX&act=checkfcv&data_city=mumbai

//View
//pranlin.jdsoftware.com/jdbox/services/getBudgetService.php?status=1&custid=10041420&act=dsview&data_city=mumbai

//Cancel rqst
//pranlin.jdsoftware.com/jdbox/services/getBudgetService.php?upid=1&act=updsapi&data_city=mumbai

//Refund Request
//pranlin.jdsoftware.com/jdbox/services/getBudgetService.php?module=ME&c_name=Pranlin&c_id=10041420&p_id=PXX22.XX22.110906165241.S2Y2&company=ABC TECH&amtandst=100&amtnost=90&st=10&jdpool=1&orig_1=2&xfer_1=3&enddate_1=4&enddate_0=5&reason=ECS Stop request but delayed in retention process&exsreson=Test exs&comments=Test cmnts&act=refund&data_city=mumbai

require_once('../config.php');
require_once('includes/budget_service_class.php');


if ($_REQUEST) {
    foreach ($_REQUEST as $key => $value) {
        $params[$key] = $value;
    }
} else {
    header('Content-Type: application/json');
    $params = json_decode(file_get_contents('php://input'), true);
}

$params['sso_ip']=SSO_MODULE_IP;
$budget_service_obj = new Budget_service_class($params);

	if($params['act']==='add'):
		$budget_service_arr = $budget_service_obj->request();
    elseif ($params['act']==='check'):
        $budget_service_arr = $budget_service_obj->chkRequest();
    elseif ($params['act']==='upDC'):
        $budget_service_arr = $budget_service_obj->updtDealClose();
    elseif ($params['act']==='dumpa'):
        $budget_service_arr = $budget_service_obj->dumpData();
    elseif ($params['act']==='status'):
        $budget_service_arr = $budget_service_obj->chkPending();
    elseif ($params['act']==='addfcv'):
        $budget_service_arr = $budget_service_obj->fcvRequest();
    elseif ($params['act']==='checkfcv'):
        $budget_service_arr = $budget_service_obj->chkfcvRequest();
    elseif ($params['act']==='dsview'):
        $budget_service_arr = $budget_service_obj->downsellView();
    elseif ($params['act']==='dspaginate'):
        $budget_service_arr = $budget_service_obj->downsellPaginate();
    elseif ($params['act']==='updsapi'):
        $budget_service_arr = $budget_service_obj->cancelDsRqst();
    elseif ($params['act']==='refund'):
        $budget_service_arr = $budget_service_obj->refundRqst();
    elseif ($params['act']==='logs'):
        $budget_service_arr = $budget_service_obj->approvalLogs();
    elseif ($params['act']===	'upTmedown'):
		$budget_service_arr = $budget_service_obj->upDTBudgetTME();
	elseif ($params['act'] === 'chk'):
		$budget_service_arr = $budget_service_obj->chkgenericPending();
		
endif;

print($budget_service_arr);
