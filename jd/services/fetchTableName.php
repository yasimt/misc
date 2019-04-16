<?php
/*
 * fetchTableName.php 
 * 
 * //This api will return bidding details table name
 * 
 * Copyright 2018 Raj Yadav <rajyadav@localhost.localdomain>
 */

ini_set('display_errors', '1');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: origin, content-type, accept');
header('Access-Control-Allow-Methods: *');

require_once('../config.php');
require_once('includes/fetchTableNameClass.php');

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

$fetchTableNameObj = new fetchTableNameClass($params);

if($params['action']=='fetchBiddingTableName')
{
	$result = $fetchTableNameObj -> fetchBiddingTableName();	
}
else
{
	$result['error_code'] = 1;
	$result['errormsg']   = 'Invalid Action Passed !';
}

$resultstr= json_encode($result);

print($resultstr);

?>
