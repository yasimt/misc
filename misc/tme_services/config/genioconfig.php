<?php

GLOBAL $parseConf;

# PART ADDED BECAUSE THE SAME FILE IS USED FOR CONSTANT CONFIG OF OTHER MODULES
$serverAddr	=	$_SERVER['SERVER_ADDR'];
$serverPointer	=	explode('.',$serverAddr);
if($serverPointer[2]	==	'64') {
	$parseConf	=	parse_ini_file(dirname(__FILE__) . '/../public/files/developmentip.conf',1);
} else {
	$parseConf	=	parse_ini_file(dirname(__FILE__) . '/../public/files/productionip.conf',1);
}


$genioconfig = array();
$genioconfig['HRMODULE'] 			= "http://192.168.20.237/hrmodule";
$genioconfig['HREMPXHR'] 			= "http://192.168.20.237:8080/api/getEmployee_xhr.php";
if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
{
	$genioconfig['jdbox_url']['mumbai'] 	= "http://".$_SERVER['SERVER_NAME']."/jdbox/";
	$genioconfig['cs_url']['mumbai'] 		= "http://".$_SERVER['SERVER_NAME']."/csgenio/";
	$genioconfig['tme_url']['mumbai'] 		= "http://".$_SERVER['SERVER_NAME']."/tmegenio/";
	$genioconfig['iro_url']['mumbai'] 		= "http://172.29.0.227/";
	
	$genioconfig['GNO_URL'] 				= "http://".$_SERVER['SERVER_NAME']."/megenio/";
	$genioconfig['KNOWLEDGE_APICALL'] 		= "http://192.168.22.103:810/SSO/uimage/";
	
}else{
	
	// JDBOX API URL
	$genioconfig['jdbox_url']['mumbai'] 	= "http://172.29.0.237:977/";
	$genioconfig['jdbox_url']['delhi'] 		= "http://172.29.8.237:977/";
	$genioconfig['jdbox_url']['kolkata'] 	= "http://172.29.16.237:977/";
	$genioconfig['jdbox_url']['bangalore'] 	= "http://172.29.26.237:977/";
	$genioconfig['jdbox_url']['chennai'] 	= "http://172.29.32.237:977/";
	$genioconfig['jdbox_url']['pune']		= "http://172.29.40.237:977/";
	$genioconfig['jdbox_url']['hyderabad'] 	= "http://172.29.50.237:977/";
	$genioconfig['jdbox_url']['ahmedabad'] 	= "http://192.168.35.237:977/";
	$genioconfig['jdbox_url']['remote']		= "http://192.168.20.135:811/";
	
	// CS API URL
	$genioconfig['cs_url']['mumbai'] 		= "http://172.29.0.217:81/";
	$genioconfig['cs_url']['delhi'] 		= "http://172.29.8.237:81/";
	$genioconfig['cs_url']['kolkata'] 		= "http://172.29.16.217:81/";
	$genioconfig['cs_url']['bangalore'] 	= "http://172.29.26.217:81/";
	$genioconfig['cs_url']['chennai'] 		= "http://172.29.32.217:81/";
	$genioconfig['cs_url']['pune']			= "http://172.29.40.217:81/";
	$genioconfig['cs_url']['hyderabad'] 	= "http://172.29.50.217:81/";
	$genioconfig['cs_url']['ahmedabad'] 	= "http://192.168.35.217:81/";
	$genioconfig['cs_url']['remote']		= "http://192.168.17.217:81/";
	
	// TME API URL
	$genioconfig['tme_url']['mumbai'] 		= "http://172.29.0.237:97/";
	$genioconfig['tme_url']['delhi'] 		= "http://172.29.8.237:97/";
	$genioconfig['tme_url']['kolkata'] 		= "http://172.29.16.237:97/";
	$genioconfig['tme_url']['bangalore'] 	= "http://172.29.26.237:97/";
	$genioconfig['tme_url']['chennai'] 		= "http://172.29.32.237:97/";
	$genioconfig['tme_url']['pune']			= "http://172.29.40.237:97/";
	$genioconfig['tme_url']['hyderabad'] 	= "http://172.29.50.237:97/";
	$genioconfig['tme_url']['ahmedabad'] 	= "http://192.168.35.237:97/";
	$genioconfig['tme_url']['remote']		= "http://192.168.17.237:197/";
	
	// IRO API URL
	$genioconfig['iro_url']['mumbai'] 		= "http://172.29.0.227/";
	$genioconfig['iro_url']['delhi'] 		= "http://172.29.8.227/";
	$genioconfig['iro_url']['kolkata'] 		= "http://172.29.16.227/";
	$genioconfig['iro_url']['bangalore'] 	= "http://172.29.26.227/";
	$genioconfig['iro_url']['chennai'] 		= "http://172.29.32.227/";
	$genioconfig['iro_url']['pune']			= "http://172.29.40.227/";
	$genioconfig['iro_url']['hyderabad'] 	= "http://172.29.50.227/";
	$genioconfig['iro_url']['ahmedabad'] 	= "http://192.168.35.227/";
	$genioconfig['iro_url']['remote']		= "http://192.168.17.227/";
	
	
	// CONSTANT IN UPPER CASE IF ITS INDEPENDENT OF DATA CITY
	$genioconfig['GNO_URL'] 			= "http://192.168.20.17/";
	$genioconfig['KNOWLEDGE_APICALL'] 	= "http://192.168.20.237/uimage/";
	
	
	
	
	
	
	
	
	
	
}

?>
