<?php
set_time_limit(0);
ini_set("memory_limit", "-1");
require_once('../config.php');
require_once('includes/budgetsubmitclass.php');

//prameshjha.jdsoftware.com/jdbox/services/budgetsubmit.php?data_city=mumbai&budgetjson='{"c_data":{"305":{"cid":"305","ncid":"10076456","cnm":"Car Hire","bval":"65.11210","bflg":"0","c_bgt":12972,"bm_bgt":0,"f_bgt":12972,"pin_data":{"400001":{"anm":"Fort","cnt":"350.97260","cnt_f":175.4863,"pos":{"1":{"inv_booked":"1.00000","bidder":"PXX22.XX22.120726083221.W3T7-18.59695-0.85000-0.85-0.85000,PXX22.XX22.120916225811.Y2P8-17.83941-0.15000-1-0.15000","bidvalue":65.1121,"budget":0,"inv_avail":0,"is_bidder":0}},"best_flg":4},"400002":{"anm":"Kalbadevi","cnt":"90.54400","cnt_f":45.272,"pos":{"1":{"inv_booked":"0.00000","bidder":"","bidvalue":65.1121,"budget":3685,"inv_avail":1,"is_bidder":0}},"best_flg":1}}},"4448":{"cid":"4448","ncid":"10348289","cnm":"Packers n Movers","bval":"215.98624","bflg":"1","c_bgt":1086,"bm_bgt":537.0224,"f_bgt":1086,"pin_data":{"400001":{"anm":"Fort","cnt":"8.62880","cnt_f":4.3144,"pos":{"1":{"inv_booked":"1.00000","bidder":"PXX22.XX22.111220171515.J5E6-83.57277-1.00000-1.00000-1.00000","bidvalue":215.98624,"budget":0,"inv_avail":0,"is_bidder":0}},"best_flg":4},"400002":{"anm":"Kalbadevi","cnt":"2.86920","cnt_f":1.4346,"pos":{"1":{"inv_booked":"0.20000","bidder":"PXX22.XX22.111209105058.L5B5-98.04937-0.20000-0.20000-0.20000","bidvalue":215.98624,"budget":310,"inv_avail":0.8,"is_bidder":0}},"best_flg":2}}}},"pos":{"1":25,"2":25,"3":0,"4":50,"5":0,"6":0,"7":0,"100":0},"tb_bgt":14058}'


if($_REQUEST)
{
	$params=$_REQUEST;
}
else
{
header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);
}

//echo json_encode($params);
//exit;


$budgetsubmitclassclassobj = new budgetsubmitclass($params);


if($params['action']=='submitbudget')
{
//echo json_encode($params);
//exit;
$result = $budgetsubmitclassclassobj->submitbudget();	
}
if($params['action']=='submitBudgetDataHidden')
{

$result = $budgetsubmitclassclassobj->submitBudgetDataHidden();	
}
//echo json_encode($params);
//exit;
if($params['action']=='getbudget')
{
$result = $budgetsubmitclassclassobj->getbudget();	
}

if($params['action']=='getActbudget')
{
	$result = $budgetsubmitclassclassobj->getActbudget();	
}

if($params['action']=='getbudgetCompletejson')
{
$result = $budgetsubmitclassclassobj->getbudgetCompletejson();	
}

if($params['action'] == 'updateActualBudget')
{
	$result = $budgetsubmitclassclassobj->updateActualBudget();
}


if($params['action'] == 'updateActualBudgetNEW')
{
	$result = $budgetsubmitclassclassobj->updateActualBudgetNEW();
}

//print_r($result);
$resultstr= json_encode($result);

print($resultstr);

?>
