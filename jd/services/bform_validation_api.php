<?
require_once('../config.php');
require_once('includes/bform_validation_class.php');

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
  
$bform_validation_class_obj  		= new bform_validation_class($params);
//echo "<pre>";print_r($bform_validation_class_obj);
$bform_validation_class_err_arr 	= $bform_validation_class_obj->checkFields();
$bform_validation_class_err_str 	= json_encode($bform_validation_class_err_arr);

print($bform_validation_class_err_str);
?>
