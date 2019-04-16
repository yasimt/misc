<?php

require_once('../config.php');
require_once('includes/compcatareasrvcclass.php');
//prameshjha.jdsoftware.com/jdbox/services/compcatareasrvc.php?parentid=P1211643358T4N5A5&data_city=mumbai&action=updateprimarytag&catidlist=14353,569

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);	
	header('Content-Type: application/json');
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

if(DEBUG_MODE)
{
	echo '<pre><br><b>params:</b>'; print_r($params);	
}
					
$compcatobj = new compcatareasrvcclass($params);

if($params['action']=='updateprimarytag')
{
$result = $compcatobj->updateprimarytag();	
}


$resultstr= json_encode($result);

print($resultstr);

