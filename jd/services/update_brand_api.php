<?
require_once('../config.php');
require_once('includes/update_brand_class.php');

if($_REQUEST['post_data'])
{
	foreach($_REQUEST as $key=>$value)
	{
		$params[$key] = $value;
	}
}
else
{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);}

$brandUpdateObj  	= new brandUpdateClass($params);
$brandUpdateRes 	= $brandUpdateObj->updateBrandName();
?>