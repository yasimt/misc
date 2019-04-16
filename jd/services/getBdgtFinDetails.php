<?php

//CREATED BY PRANLIN

//http://pranlin.jdsoftware.com/jdbox/services/getBdgtFinDetails.php?p_id=PXX22.XX22.170125140236.V9G1&vrsn=33&data_city=Mumbai&act=TBDIA
//http://pranlin.jdsoftware.com/jdbox/services/getBdgtFinDetails.php?p_id=PXX22.XX22.170125140236.V9G1&vrsn=33&data_city=Mumbai&act=PA
//http://pranlin.jdsoftware.com/jdbox/services/getBdgtFinDetails.php?p_id=PXX22.XX22.170125140236.V9G1&vrsn=23&data_city=Mumbai&act=VRSN
//http://pranlin.jdsoftware.com/jdbox/services/getBdgtFinDetails.php?p_id=PXX22.XX22.170203110500.G5C8&vrsn=23&data_city=Mumbai&act=CAMPID
//http://pranlin.jdsoftware.com/jdbox/services/getBdgtFinDetails.php?p_id=PXX22.XX22.170125140236.V9G1&vrsn=13&data_city=Mumbai&act=OLD
//http://pranlin.jdsoftware.com/jdbox/services/getBdgtFinDetails.php?campid=13&data_city=Mumbai&act=CAMP

require_once('../config.php');
require_once('includes/budget_fin_class.php');

header('Content-Type: application/json');
if ($_REQUEST) {
    foreach ($_REQUEST as $key => $value) {
        $params[$key] = $value;
    }
} else {
    $params = json_decode(file_get_contents('php://input'), true);
}

$budget_service_obj = new Budget_fin_class($params);

if($params['act']==='TBDIA'):
    $budget_service_arr = $budget_service_obj->getTBDIADetails();
    elseif ($params['act']==='PA'):
        $budget_service_arr = $budget_service_obj->getPADetails();
    elseif ($params['act']==='VRSN'):
        $budget_service_arr = $budget_service_obj->getLastVersion();
    elseif ($params['act']==='OLD'):
        $budget_service_arr = $budget_service_obj->getOldDataList();
    elseif ($params['act']==='CAMP'):
        $budget_service_arr = $budget_service_obj->getCampaignName();
    elseif ($params['act']==='CAMPID'):
        $budget_service_arr = $budget_service_obj->getCampaignIds();
endif;

print($budget_service_arr);
