<?php
class geocode_class extends DB
{
	var  $conn_iro    	= null;	 
	var  $conn_iro_slave= null;	 
	var  $conn_fin    	= null;	
	var  $conn_national	= null;	
	var  $conn_data_correction	= null;	
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $data_city		= null;
	var  $ucode		= null;
	
	
	function __construct($params)
	{	
		global $params;
		$data_city 			= trim($params['data_city']); 	
		$rquest 			= trim($params['rquest']); 
		if(trim($rquest)=='') {
			$message = "Invalid request name.";
			echo json_encode($this->send_die_message($message));
			die();
		}	
	 		
		
		$this->data_city 	= $data_city;
		$this->rquest  	  	= $rquest;
		
		//mongo
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		
		$this->setServers();		  	
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
		
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');	
		$this->conn_idc    		= $db[$conn_city]['idc']['master'];
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];		
		$this->conn_iro_slave	= $db[$conn_city]['iro']['slave'];		
		$this->conn				= $db[$conn_city]['d_jds']['master'];		
		$this->conn_data_correction	= $db[$conn_city]['data_correction']['master'];		

		if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){
			$this->mongo_flag = 1;
		}
	}	
	function fetch_details() {
		$func = $this->rquest;
		if((int)method_exists($this,$func) > 0)
			return $this->$func();
		else {
			$message = "invalid function";
			return $this->send_die_message($message);			
		}
	}	
	function get_geocode_accuracy()
	{
		global $params;
		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get Geocode Accuracy\n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		 
		// get the lat / long of new area / pincode
		$sql_geocode	= "SELECT latitude_final, longitude_final FROM tbl_area_master WHERE area='".$params['area']."' AND city='".$params['city']."' AND display_flag=1 AND type_flag=1";
		$res_geocode = parent::execQuery($sql_geocode, $this->conn); 
				
		if($res_geocode && mysql_num_rows($res_geocode) > 0)
		{
			$row_geocode	= mysql_fetch_assoc($res_geocode);

			$param_api_gal = Array();
			$param_api_gal['building_name'] = $params['building_name'];
			$param_api_gal['landmark'] 		= $params['landmark'];
			$param_api_gal['street'] 		= $params['street'];
			$param_api_gal['area'] 			= $params['area'];
			$param_api_gal['city'] 			= $params['city'];
			$param_api_gal['pincode'] 		= $params['pincode'];
			$param_api_gal['latitude'] 		= $row_geocode['latitude_final'];
			$param_api_gal['longitude'] 	= $row_geocode['longitude_final'];
			$param_api_gal['module'] 		= 'cs';
			$param_api_gal['rquest'] 		= 'getGeocodeAccuracy';
			$param_api_gal['parentid'] 		= $params['parentid'];

			$configclassobj	=	new configclass();
			$urldetails		=	$configclassobj->get_url(urldecode($params['data_city']));
			$app_url	=	$urldetails['url'];
			
			$curl_url = $app_url."api_services/api_geocode_accuracy_new.php";

			$ch 		= curl_init();
			curl_setopt($ch, CURLOPT_URL, $curl_url);
			curl_setopt($ch, CURLOPT_POST,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$param_api_gal);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$resmsg = curl_exec($ch);
			curl_close($ch);

			$decode_curl = json_decode(stripslashes($resmsg), true);
			
			if($decode_curl['status'] == 'Pass')
			{
				$post_arr['geocode_accuracy_level'] = $decode_curl['data']['geocode_accuracy_level'];
				$post_arr['latitude'] 				= $decode_curl['data']['latitude'];
				$post_arr['longitude'] 				= $decode_curl['data']['longitude'];
				$post_arr['map_pointer_flags'] 		= $decode_curl['data']['map_pointer_flags'];
				$post_arr['flags'] 					= $decode_curl['data']['flags'];	
				$sent_to_moderation					= $decode_curl['data']['sent_to_moderation'];

				// do not call sent to moderation for new contracts
				if($sent_to_moderation == "yes") 
				{
					$arr_old_add		= array();
					$arr_new_add		= array();

					$select1 = "select building_name,state,city,landmark,street,pincode,latitude,longitude,geocode_accuracy_level FROM tbl_companymaster_generalinfo where parentid='".$params['parentid']."'";	
					$res_slect1 = parent::execQuery($select1, $this->conn_iro); 
					if($res_slect1 && mysql_num_rows($res_slect1))
					{
						$row_1 = mysql_fetch_assoc($res_slect1);
						$arr_old_add['state'] 			= $row_1['state'];
						$arr_old_add['city']			= $row_1['city'];
						$arr_old_add['building_name']	= $row_1['building_name'];
						$arr_old_add['landmark']		= $row_1['landmark'];
						$arr_old_add['street']			= $row_1['street'];
						$arr_old_add['area']			= $row_1['area'];
						$arr_old_add['pincode']			= $row_1['pincode'];
						$arr_old_add['latitude']			= $row_1['latitude'];
						$arr_old_add['longitude']			= $row_1['longitude'];
						$arr_old_add['geocode_accuracy_level'] = $row_1['geocode_accuracy'];
					}
					
					$arr_new_add['state'] 			= $params['state'];
					$arr_new_add['city'] 			= $params['city'];
					$arr_new_add['building_name']	= $params['building_name'];
					$arr_new_add['landmark']		= $params['landmark'];
					$arr_new_add['street']			= $params['street'];
					$arr_new_add['area']			= $params['area'];
					$arr_new_add['pincode']			= $params['pincode'];
					$arr_new_add['latitude']		= $params['latitude'];
					$arr_new_add['longitude']		= $params['longitude'];
					$arr_new_add['geocode_accuracy_level']		= $params['geocode_accuracy_level'];
					$param_api_gal2 = Array();
					$param_api_gal2['new_address']		=	json_encode($arr_new_add);
					$param_api_gal2['old_address']		=	json_encode($arr_old_add);
					$param_api_gal2['uname'] 			= $from_user;
					$param_api_gal2['ucode'] 			= $from_user;
					$param_api_gal2['temp_latitude'] 	= $params['latitude'];
					$param_api_gal2['temp_longitude'] 	= $params['longitude'];
					$param_api_gal2['temp_tagging'] 	= $params['geocode_accuracy_level'];
					$param_api_gal2['original_tagging'] = $arr_old_add['geocode_accuracy_level'];
					$param_api_gal2['parentid'] 	 	= $params['parentid'];
					$param_api_gal2['module'] 			= 'cs';
					$param_api_gal2['rquest'] 			= 'insertGeocodeModeration';

					$curl_url = $app_url."/api_services/api_geocode_accuracy_new.php";
					$var_get = http_build_query($param_api_gal2);
					

					$ch 		= curl_init();
					curl_setopt($ch, CURLOPT_URL, $curl_url);
					curl_setopt($ch, CURLOPT_POST,1);
					curl_setopt($ch, CURLOPT_POSTFIELDS ,$param_api_gal2);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					$resdata = curl_exec($ch);
					curl_close($ch);
				}
			}
		} 
		$output 				=   json_decode($resmsg,true);
		if($params['trace'] == 1){
			echo "<br><br>";print_r($output);
		}		
		return ($output);
	}
	function send_die_message($msg)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['message'] = $msg;
		return $die_msg_arr;
	}
}
?>
