<?php
require_once('../config.php');

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
}

require_once('includes/log_generate_complaint_class.php');
require_once('includes/company_details_class.php');
//http://prameshjha.jdsoftware.com/jdbox/services/regfee.php?campaignid=1&contactno=8800900009&data_city=mumbai


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
	echo '<pre>request data :: ';
print_r($params);
}
//echo json_encode($params); exit;

		if(trim($params['parentid']) == "")
		{
			$errorarray['errormsg']='parentid missing';
			echo json_encode($errorarray); exit;
		}		
		
		if(trim($params['data_city']) == "" && $params['data_city'] == null)
		{
			$errorarray['errormsg']='data_city missing';
			echo json_encode($errorarray); exit;
		}
		
		if(trim($params['action']) == "" && $params['action'] == null)
		{
			$errorarray['errormsg']='action missing';
			echo json_encode($errorarray); exit;
		}
			
		if(trim($params['module']) == "" && $params['module'] == null)
		{
			$errorarray['errormsg']='module missing';
			echo json_encode($errorarray); exit;
		}
		

if($params['action'] == 1 || $params['action'] == 2  || $params['action'] == 11) 
{
	
	if($params['action'] == 11)
	{
		$params['action'] = 1;
		$revert_action = 1;
	}
	$log_generate_complaint = new log_generate_complaint_class($params);
	
	if($revert_action)
	{
			$params['action'] = 11;
	}
	
	if($params['action'] == 1 || $params['action'] == 11)
	{
		
		$result = $log_generate_complaint -> get_all_complaints();
		$result_arr['all_complaints'] = $result;
		
	}

	if($params['action'] == 2)
	{
		
		$result = $log_generate_complaint -> get_complaint_content();
	}
}



if($params['action'] == 3 || $params['action'] == 4 || $params['action'] == 11)
{
	require_once('../functions.php');
	require_once('includes/company_details_class.php');
	if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
	{
		$params['cs_url'] = get_cs_application_url($params['data_city']);
	}
	else
	{
		$params['cs_url'] = "http://rajkumaryadav.jdsoftware.com/csgenio/";
	}
		
	
	if($params['action'] == 3 || $params['action'] == 11) {

		$company_details_obj = new company_details_class($params);
		
		$result = $company_details_obj -> getPaymentDetails();
		$result_arr['payment_details'] = $result['payment_details'];
		
		$result_arr['instrument_details'] = $company_details_obj -> getInstrumentDetails();
		
		$result['instrument_details'] = $result_arr['instrument_details'];
		//array_push($result,$result_arr['instrument_details']);
		
	}
	
	if($params['action'] == 4 || $params['action'] == 11)  {
		$curl_url   = $params['cs_url']."api/fetch_update_narration.php?parentid=".$params['parentid']."&action=1&data_city=".$params['data_city']."";
		$result = curl_call_get($curl_url);
		$result_arr['narration_details'] = $result;
	}
	
}

function curl_call_get($curl_url)
{	
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$curl_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
	curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );

	$resstr = curl_exec($ch);
	curl_close($ch);
	return $resstr;
}
	

if($params['action'] == 11)
$result = $result_arr;

//echo "<pre>"; print_r($result);
$resultstr= json_encode($result);

print($resultstr);

