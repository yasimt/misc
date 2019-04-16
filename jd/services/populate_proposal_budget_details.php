<?php
//populate_proposal_budget_details.php
require_once('../config.php');
require_once('../library/configclass.php');
require_once('includes/populate_proposal_details_class.php');
//ini_set('display_errors', '0');

if($_REQUEST['post_data']){
	header('Content-Type: application/json');
	foreach($_REQUEST as $key=>$value){
		$params[$key] = $value;
	}
}else{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}

$proposal_cls_obj	= new populate_proposal_details_class($params);
$res_arr		 	= $proposal_cls_obj->storeBudgetDetails();
echo json_encode($res_arr);
?>
