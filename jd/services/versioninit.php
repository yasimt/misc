<?php
require_once('../config.php');
require_once('includes/versioninitclass.php');


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

$versionInitClass_obj = new versionInitClass($params);

if($params['action']=='getversion')
{
$result = $versionInitClass_obj->getversion();
$resultstr= json_encode($result);
print($resultstr);
exit;	
}

$result = $versionInitClass_obj->setversion();

$resultstr= json_encode($result);

print($resultstr);

