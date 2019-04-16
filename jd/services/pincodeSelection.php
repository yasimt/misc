<?php
require_once('../config.php');
require_once('includes/pincodeselectionclass.php');
////sunnyshende.jdsoftware.com/jdbox/services/areaDetails.php?data_city=mumbai&latitude=&longitude=&rds=5&opt=DIST

if($_REQUEST)
{
	$params=$_REQUEST;
}
else
{
header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);
}



$pincodeselectionclassobj = new pincodeselectionclass($params);

if($params['action']=='set')
{
$result = $pincodeselectionclassobj->setPincode();	
}

if($params['action']=='get')
{
$result = $pincodeselectionclassobj->getPincode();	
}

if($params['action']=='setlisttojson')
{
$result = $pincodeselectionclassobj->setlisttojson();	
}
if($params['action']=='pdg')
{
$result = $pincodeselectionclassobj->findPdgActive();
}

$resultstr= json_encode($result);

print($resultstr);

