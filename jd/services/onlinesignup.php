<?php
set_time_limit(0);
require_once('../config.php');
require_once('../library/configclass.php');
require_once('../library/class.Curl.php');

require_once('includes/onlinesignup_class.php');
require_once('includes/class_send_sms_email.php');



if($_REQUEST["trace"] == 1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
	header('Content-type: application/json');
}

if(isset($_REQUEST['action']))
{
	$params=$_REQUEST;
}
else
{
	$params	= json_decode(file_get_contents('php://input'),true);
}

if(DEBUG_MODE)
{
	echo '<pre>';
	print_r($params);
}


$Data_city_allowed_action_array = array('signupinitiationmobile');

$params['action']= strtolower(trim($params['action']));
if(trim($params['action'])==null)
{
	$result['error']['code'] = 1;
	$result['error']['msg'] = "action missing --";
	$resultstr= json_encode($result);
	print($resultstr);
	die;
}


# City validaton 

if( (!isset($params['data_city']) || trim($params['data_city'])==null) && in_array($params['action'],$Data_city_allowed_action_array) )
{
	if(!isset($params['city']) || trim($params['city'])==null )
	{
		$result['error']['code'] = 1;
		$result['error']['msg'] = "city missing ";
		$resultstr= json_encode($result);
		print($resultstr);
		die;
	}
	
	
	$data_city_of_city = onlinesignupclass::getDataCityFromCity($params['city']);
	
	if($data_city_of_city==null)
	{
		$result['error']['code'] = 1;
		$result['error']['msg'] = "Please pass valid city ";
		$resultstr= json_encode($result);
		print($resultstr);
		die;
	}else
	{
		$params['data_city']= $data_city_of_city;
	}
}


if(trim($params['data_city'])==null)
{
	$result['error']['code'] = 1;
	$result['error']['msg'] = "data_city missing ";
	$resultstr= json_encode($result);
	print($resultstr);
	die;
}


$obj = new onlinesignupclass($params);
$obj->verifyKey();

if($params['action']=='signupinitiation')
{
	$result = $obj->signupinitiation();

}elseif($params['action']=='signupinitiationmobile')
{
	$result = $obj->signupinitiationmobile();

}elseif($params['action']=='geniodealclosependingstatus')
{
	$result = $obj->genioDealClosePendingStatus();

}elseif($params['action']=='geniodealclose')
{
	$result = $obj->geniodealclose();

}elseif($params['action']=='autodealclose')
{
	$obj->autodealclose();
}


$resultstr= json_encode($result);
echo $resultstr;

?>
