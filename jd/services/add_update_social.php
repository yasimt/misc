<?
require_once('../config.php');
require_once('../functions.php');
require_once('../library/configclass.php');
require_once('includes/add_update_social_class.php');

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
	$params	= json_decode(file_get_contents('php://input'),true);

}

//echo "<pre>";print_r($params);
$social_obj = 	new socialClass($params);
$social_obj->addSocial();

?>