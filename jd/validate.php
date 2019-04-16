<?php
require_once("config.php");
require_once("functions.php");

$conn = new DB();

$type 		= 	trim($_REQUEST['type']);
$compname	=	trim($_REQUEST['compname']);
$tele		=	trim($_REQUEST['tele']);
$tollfree	=	trim($_REQUEST['tollfree']);
$mobile		=	trim($_REQUEST['mobile']);
$source		=	trim($_REQUEST['source']);
$pincode	=	trim($_REQUEST['pincode']);
$catarr		=	trim($_REQUEST['arr_cat']);

$sel_city	=	'';

$msg		=	array();

global $db;

$dataservers 	=	array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

if(trim($_REQUEST['data_city']))
	$sel_city		=	trim($_REQUEST['data_city']); // selected city on bform
else	
	$sel_city		=	trim($_REQUEST['sel_city']); // selected city on bform
	
if(!empty($sel_city))
{
	$city_name 		= $sel_city;	
	$data_city 		= ((in_array(strtolower($city_name), $dataservers)) ? strtolower($city_name) : 'remote');
	if($data_city == 'remote')
	{
		$conn_djds_remote 	= 	$db['remote']['d_jds']['master'];
		if(is_numeric($sel_city))
		{
			$city_name		= 	getCityName($sel_city, $conn_djds_remote);
		}
		$datacity			=	getDatacity($city_name);		
		$data_city 			= ((in_array(strtolower($datacity), $dataservers)) ? strtolower($datacity) : 'remote');
	}
}
else
{
	$city_name 		= $_REQUEST['city'] ? trim($_REQUEST['city']) : 'remote';
				
	$data_city 		= ((in_array(strtolower($city_name), $dataservers)) ? strtolower($city_name) : 'remote');
}

$conn_djds  		= $db[$data_city]['d_jds']['master'];
$conn_djds_slave  	= $db[$data_city]['d_jds']['slave'];
$conn_idc   		= $db[$data_city]['idc']['master'];
$conn_iro    		= $db[$data_city]['iro']['master'];
$conn_iro_slave     = $db[$data_city]['iro']['slave'];
//$conn_tmejds 		= $db[$data_city]['tme_jds']['master'];
//$conn_fin    		= $db[$data_city]['fin']['master'];
//$conn_reseller	= $db['reseller']['master'];

switch($type)
{
	case 'compname' :
		$ret1 	= checkCategory($compname, $conn_djds);
		if($ret1 == '')
		{
			$ret2 	= checkCatsynonym($compname, $conn_djds);
			$msg[]	= $ret2;
		}
		else
		{
			$msg[]	= $ret1;
		}
		if(trim($_REQUEST['skip_brand'])!=='1')
		{
			$msg[] 	= checkBrandname($compname, $conn_iro);
		}
		if(strtolower($source) !='web') // Added by shital patil - 23-01-2014 for bad word handling
		{		
			$msg[] 	= checkBadWords($compname, $conn_iro,$_REQUEST['data_city']);
		}
		if($compname!=''){
			$msg[] 	= checkNonUTFChar($compname);
		}
		
		
	break;
	
	case 'tele' :
		//$msg[] 	= checkBlockednum($tele, $conn_idc);
		$msg[] 	= checkVitualNumber($tele,$data_city,$conn_djds);
	break;
	
	case 'mobile' :
		//$msg[] 	= checkBlockednum($mobile, $conn_idc);
	break;
	
	case 'category' :
		$msg[]	= checkPremimumCategory($catarr, $conn_djds);
	break;
	
	case 'business' :
		if($tele != '' || $mobile != '' || $tollfree != '')
		{
			if($tele != '')
			{
				$tele_arr  = explode('|', $tele);
				
				foreach ($tele_arr as $key => $televal)
				{
					$numbers[] = $televal;
				}				
			}
			
			if($mobile != '')
			{
				$mobile_arr  = explode('|', $mobile);
				
				foreach ($mobile_arr as $key => $mobileval)
				{
					$numbers[] = $mobileval;
				}
			}
			
			if($tollfree != '')
			{
				$tollfree_arr  = explode('|', $tollfree);
				
				foreach ($tollfree_arr as $key => $tollfreeval)
				{
					$numbers[] = $tollfreeval;
				}
			}
			$numbers = implode(',', $numbers);
			//$msg[] 	 = checkDuplicate($numbers, $compname, $pincode, $city_name, $source);
			//$msg[] 	=  checkCompanyArea($compname, $city_name, $conn_djds);
			//$msg[]	=  checkCompanyCity($compname, $city_name, $conn_djds);
		}
		
	break;	
	
	default:
	break;
}

//if(!empty($msg1) || !empty($msg2) || !empty($msg3) || !empty($msg4)) echo $msg1 . "\r\n"  . $msg2 . "\r\n" . $msg3 . "\r\n" . $msg4;
$msg_clean = array_filter($msg);
if(count($msg_clean) > 0)
{
	$im_msg = implode("\r\n",$msg_clean);
	echo $im_msg;
}
exit;
?>
