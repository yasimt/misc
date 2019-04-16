<?php
/*
 * chkMultiPaymtsEligibility.php
 * 
 * Copyright 2018 Raj Yadav <rajyadav@localhost.localdomain>
 */

//ini_set('display_errors', '1');

if($_REQUEST['module'] == 'tme')
{
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: origin, content-type, accept');
	header('Access-Control-Allow-Methods: *');
}

require_once('../config.php');
require_once('includes/class_ChkMultiPaymts_eligibility.php');

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
}

if($_REQUEST)
{
	$params=$_REQUEST;
}
else
{
header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);
}

//echo"\n <br>params". json_encode($params);
//die('inside jdbox');

$ChkMultiPaymtsObj = new class_ChkMultiPaymts_eligibility($params);

switch(strtolower($params['action']))
{
	case 'iseligible':
		$result = $ChkMultiPaymtsObj -> isEligible();	
	break;
	case 'iseligiblebaladj':
		$result = $ChkMultiPaymtsObj -> isEligibleBalAdj();	
	break;
	case 'resetexistingrequest':
		$result = $ChkMultiPaymtsObj -> resetExistingRequest();	
	break;
	default:
	$result['code'] = '400';
	$result['msg']  = 'Invalid Action';
	break;
}




$resultstr= json_encode($result);

print($resultstr);

?>
