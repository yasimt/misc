<?php
class configclass{
	
public function get_url($data_city)
{
	if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
	{
		$url 					= "http://imteyazraja.jdsoftware.com/csgenio/";
		$KNOWLEDGE_APICALL 		= "http://192.168.22.103:810/SSO/uimage/";
		$jdbox_url 				= "http://imteyazraja.jdsoftware.com/jdbox/";
		$tme_url 				= "http://imteyazraja.jdsoftware.com/tmegenio/";
		$city_indicator 		= "main_city";
		$iro_url 				= "http://guruprasadk.jdsoftware.com/iro0605";
		$HRMODULE 				= "http://192.168.20.237/hrmodule";
		$rest_url				= "http://abhinandanladage.jdsoftware.com/restaurantapis/restaurant";
		$social_new_url 		= "http://labs.justdial.com/php/graphapi/pen/AddNode.php";
		$omni_url 				= "http://pratikjain.jdsoftware.com/jdbox/services/global_company_api.php";
		$company_data_url 		= "http://172.29.86.26:4000/api/comp";
		$node_fin_api_url  		= "http://172.29.64.51:7799/";
	}
	else
	{
		switch(strtoupper($data_city))
		{
			case 'MUMBAI' :
				$url 					= "http://172.29.0.217:81/";
				$jdbox_url 				= "http://172.29.0.217:811/";
				$tme_url 				= "http://172.29.0.237:97/";
				$iro_url 				= "http://172.29.0.227";	
				$node_fin_api_url  		= "http://172.29.0.217:3005/";			
				$city_indicator 		= "main_city";
				break;

			case 'AHMEDABAD' :
				if(defined('AHMEDABAD_LCL_TO_IDC_MIGRATION') && AHMEDABAD_LCL_TO_IDC_MIGRATION==1 )
				{
					$url 					= "http://192.168.35.217:81/";
					$jdbox_url 				= "http://192.168.35.217:811/";
					$tme_url 				= "http://192.168.35.237:97/";
					$iro_url 				= "http://192.168.35.227";
					$node_fin_api_url  		= "http://192.168.35.217:3005/";			
					
				}else
				{
					$url 					= "http://172.29.56.217:81/";
					$jdbox_url 				= "http://172.29.56.217:811/";
					$tme_url 				= "http://172.29.56.237:97/";
					$iro_url 				= "http://172.29.56.227";
					$node_fin_api_url  		= "http://172.29.56.217:3005/";			
					
				}
				
				$city_indicator = "main_city";
				break;

			case 'BANGALORE' :
				$url 					= "http://172.29.26.217:81/";
				$jdbox_url 				= "http://172.29.26.217:811/";
				$tme_url 				= "http://172.29.26.237:97/";
				$iro_url 				= "http://172.29.26.227";
				$node_fin_api_url  		= "http://172.29.26.217:3005/";			
				$city_indicator 		= "main_city";
				break;

			case 'CHENNAI' :
				$url 					= "http://172.29.32.217:81/";
				$jdbox_url 				= "http://172.29.32.217:811/";
				$tme_url 				= "http://172.29.32.237:97/";
				$iro_url 				= "http://172.29.32.227";
				$node_fin_api_url  		= "http://172.29.32.217:3005/";			
				$city_indicator		    = "main_city";
				break;

			case 'DELHI' :
				$url 					= "http://172.29.8.217:81/";
				$jdbox_url 				= "http://172.29.8.217:811/";
				$tme_url 				= "http://172.29.8.237:97/";
				$iro_url 				= "http://172.29.8.227";
				$node_fin_api_url  		= "http://172.29.8.217:3005/";			
				$city_indicator 		= "main_city";
				break;

			case 'HYDERABAD' :
				$url 					= "http://172.29.50.217:81/";
				$jdbox_url 				= "http://172.29.50.217:811/";
				$tme_url 				= "http://172.29.50.237:97/";
				$iro_url 				= "http://172.29.50.227";
				$node_fin_api_url  		= "http://172.29.50.217:3005/";			
				$city_indicator 		= "main_city";
				break;

			case 'KOLKATA' :
				$url 					= "http://172.29.16.217:81/";
				$jdbox_url 				= "http://172.29.16.217:811/";
				$tme_url 				= "http://172.29.16.237:97/";
				$iro_url 				= "http://172.29.16.227";
				$node_fin_api_url  		= "http://172.29.16.217:3005/";			
				$city_indicator 		= "main_city";
				break;

			case 'PUNE' :
				$url 					= "http://172.29.40.217:81/";
				$jdbox_url 				= "http://172.29.40.217:811/";
				$tme_url 				= "http://172.29.40.237:97/";
				$iro_url 				= "http://172.29.40.227";
				$node_fin_api_url  		= "http://172.29.40.217:3005/";			
				$city_indicator 		= "main_city";
				break;

			default:
				$url 					= "http://192.168.17.217:81/";
				$jdbox_url 				= "http://192.168.20.135:811/";
				$tme_url 				= "http://192.168.17.237:197/";
				$iro_url 				= "http://192.168.17.227";
				$node_fin_api_url  		= "http://192.168.17.217:3005/";			
				$city_indicator 		= "remote_city";
				
				break;
		}	
		//$KNOWLEDGE_APICALL 				= "http://192.168.20.237/uimage/";	
		$rest_url =	"http://192.168.20.109/restaurantapis/restaurant";
		$social_new_url = "http://g.justdial.com/php/pen/AddNode.php";		
		$omni_url		= "https://www.jdomni.com/marketplace/php/micrositeDataForUpdate.ns";
		$company_data_url		= "http://192.168.20.133/api/comp";
	}	
	$KNOWLEDGE_APICALL 				= "http://192.168.20.237/uimage/"; //uncomment  it later
	$urlArr['url'] 					= $url;
	$urlArr['KNOWLEDGE_APICALL'] 	= $KNOWLEDGE_APICALL;
	$urlArr['jdbox_url'] 			= $jdbox_url;
	$urlArr['tme_url'] 				= $tme_url;
	$urlArr['iro_url'] 				= $iro_url;
	$urlArr['jdbox_service_url'] 	= $jdbox_url.'services/';	
	$urlArr['city_indicator'] 		= $city_indicator;
	$urlArr['HRMODULE'] 			= "http://192.168.20.237/hrmodule";
	$urlArr['rest_url'] 			= $rest_url;
	$urlArr['social_new_url'] 		= $social_new_url;
	$urlArr['omni_url'] 			= $omni_url;
	$urlArr['company_data_url'] 	= $company_data_url;
	$urlArr['node_fin_api_url'] 	= $node_fin_api_url;
	return $urlArr;
}

}
?>
