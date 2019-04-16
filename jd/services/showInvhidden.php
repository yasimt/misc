<?php
ini_set("memory_limit",-1);
require_once('../config.php');
require_once('includes/showInvhiddenClass.php');
//http://sunnyshende.jdsoftware.com/JDBOXNEW/services/showInv.php?parentid=NNPXX22.XX22.121231144841.T5S7&data_city=mumbai&trace=1
if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
	header('Content-type: application/json');
}

if(isset($_REQUEST['parentid']) && isset($_REQUEST['data_city']))
{
	$params['data_city']	= $_REQUEST['data_city'];
	$params['parentid']	= $_REQUEST['parentid'];
	$params['astatus']	= $_REQUEST['astatus'];  // 0-default 1-Shadow Inv 2-LIVE Inv
}
else
{
	$params	= json_decode(file_get_contents('php://input'),true);
}
//print_r($params);

if(DEBUG_MODE)
{
	echo '<pre>';
	print_r($params);
}
$showinvclassobj = new showInvClass($params);

$result = $showinvclassobj->showInventory();
//echo "<pre>"; print_r($result);
$resultstr= json_encode($result);

print($resultstr);
?>
