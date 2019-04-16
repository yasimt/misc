<?php 
 $city_ip_array = array("0"=>'MUMBAI',"8"=>'DELHI',"16"=>'KOLKATA',"26"=>'BANGALORE',"32"=>'CHENNAI',"40"=>'PUNE',"50"=>'HYDERABAD',"56"=>'AHMEDABAD',"17"=>'REMOTE_CITIES',"64"=>'MUMBAI');
if(trim($_SERVER['SERVER_ADDR'])!='')
{
	$server_indicators = explode(".", trim($_SERVER['SERVER_ADDR']));
        
	//if($_SERVER['SERVER_ADDR']=="192.168.1.217" ||$_SERVER['SERVER_ADDR']=="115.112.246.24" || $_SERVER['SERVER_ADDR']=="192.168.1.227")
	if($_SERVER['SERVER_ADDR']=="192.168.17.217" ||$_SERVER['SERVER_ADDR']=="103.20.126.63" || $_SERVER['SERVER_ADDR']=="192.168.17.227")
	{
		define("CSAPI_URL","REMOTE_CITIES");
	}
	elseif(is_array($server_indicators) && count($server_indicators)>3)
	{
		getDefindUrl($server_indicators[2]);
	}
}
elseif(!isset($_SERVER['HTTP_HOST']) && isset($_SERVER['argv'][1]))
{
	if(array_key_exists($_SERVER['argv'][1], $city_ip_array))
	{       
		$prod = 2; /* For cron */
		//$server_city = $city_ip_array[$_SERVER['argv'][1]];
		getDefindUrl($_SERVER['argv'][1]);
	}
}else{
	$rooofldrName = getRootFolder();
	define("CSAPI_URL",$rooofldrName);
	die("++Please provide proper server address or server argument!!!!\n");
}

function getDefindUrl($ipVal){
	 $city_ip_array = array("0"=>'MUMBAI',"8"=>'DELHI',"16"=>'KOLKATA',"26"=>'BANGALORE',"32"=>'CHENNAI',"40"=>'PUNE',"50"=>'HYDERABAD',"56"=>'AHMEDABAD',"17"=>'REMOTE_CITIES',"64"=>'MUMBAI');
	 /*switch($ipVal)
	{
		case 0:
			define("CSAPI_URL","MUMBAI");
		break;
		case 1:
			define("CSAPI_URL","REMOTE_CITIES");
		break;
		case 8:
			define("CSAPI_URL","DELHI");
		break; 
		case 16:
			define("CSAPI_URL","KOLKATA");
		break;
		case 26:
			define("CSAPI_URL","BANGALORE");
		break;
		case 32:
			define("CSAPI_URL","CHENNAI");
		break;
		case 40:
			define("CSAPI_URL","PUNE");
		break;
		case 50:
			define("CSAPI_URL","HYDERABAD");
		break;
		case 56:
			define("CSAPI_URL","AHMEDABAD");
		break;            
	}*/
	switch($ipVal)
	{
		case 0:
			define("CSAPI_URL","172.29.0.217:81");
		break;
		case 1:
			define("CSAPI_URL","192.168.17.217:81");
		break;
		case 8:
			define("CSAPI_URL","172.29.8.217:81");
		break; 
		case 16:
			define("CSAPI_URL","172.29.16.217:81");
		break;
		case 26:
			define("CSAPI_URL","172.29.26.217:81");
		break;
		case 32:
			define("CSAPI_URL","172.29.32.217:81");
		break;
		case 40:
			define("CSAPI_URL","172.29.40.217:81");
		break;
		case 50:
			define("CSAPI_URL","172.29.50.217:81");
		break;
		case 56:
			define("CSAPI_URL","172.29.56.237:81");
		break;
		case 35:
			define("CSAPI_URL","192.168.35.217:81");
		break;
		case 64:
			$rooofldrName = getRootFolder();
			define("CSAPI_URL",$rooofldrName);
		break;
	}
}

function getRootFolder(){
	$rootFolder ='';
	if($_SERVER['HTTP_HOST']!=''){
		$rootFolder = $_SERVER['HTTP_HOST'];
	}else{
		$getpostFixvalue = explode("htdocs/",$_SERVER['PWD']);
		$getpostFixvalue = array_merge(array_filter($getpostFixvalue));

		$rootFileArr =  explode("/",$getpostFixvalue[1]);
		$rootFileArr = array_merge(array_filter($rootFileArr));
	
		$rootFolder = $_SERVER['LOGNAME'].".jdsoftware.com/".$rootFileArr[0];
	}
	return $rootFolder;
}
?>
