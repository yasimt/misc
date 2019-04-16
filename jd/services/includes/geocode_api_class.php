<?php
/**
 * Filename : geocode_api_class.php
 * Date		: 01/04/2019
 * Author	: shital patil
 * Purpose	: This file is used get geocode details
 * */
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
		$parentid 			= trim($params['parentid']); 
		if(trim($rquest)=='') {
			$message = "Invalid request name.";
			echo json_encode($this->send_die_message($message));
			die();
		}	
	 	$this->companyClass_obj  = new companyClass();	
		
		$this->parentid 	= $parentid;
		$this->data_city 	= $data_city;
		$this->rquest  	  	= $rquest;
		$urls = $this->getCurlURL($this->data_city);
		$this->jdbox_url = $urls['jdbox_url'];	 
		$this->jdbox_service_url = $urls['jdbox_service_url'];	 
		
		$this->setServers();	
		define("BUILDING_LEVEL", 1);
		define("LANDMARK_LEVEL", 2);
		define("STREET_LEVEL", 3);
		define("AREA_LEVEL", 6);
		define("PINCODE_LEVEL", 4);
		define("CITY_LEVEL", 7);

		define("TYPE_FLAG_AREA", 1);
		define("TYPE_FLAG_LANDMARK", 2);
		define("TYPE_FLAG_STREET", 3);	  	
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
	function get_geocode()
	{
		global $params;
		if($params['trace'] == 1)
		{
			echo "<hr><prE>";
			echo "<b>Purpose : To get Geocode Accuracy</b>";
			echo "<hr>";
			echo "<b>Input Parameters : </b><hr>";
			print_r($params);
			echo "<hr>";
		}
		$arr_new = array();
		$geocode_arr =Array();
		$arr_new['building_name']= $this->get_clean_variable($params['building_name']);
		$arr_new['landmark']	= $this->get_clean_variable($params['landmark']);
		$arr_new['street']		= $this->get_clean_variable($params['street']);
		$arr_new['area']		= $this->get_clean_variable($params['area']);
		$arr_new['pincode']		= $this->get_clean_variable($params['pincode']);
		$arr_new['city']		= $this->get_clean_variable($params['city']);
		$arr_new['latitude']	= $this->get_clean_variable($params['latitude']);
		$arr_new['longitude']	= $this->get_clean_variable($params['longitude']);
		$arr_new['gal']			= $this->get_clean_variable($params['gal']);
		$arr_new['geocode_accuracy_level']			= $this->get_clean_variable($params['geocode_accuracy_level']);
		
		if(isset($params['test']) && $params['test'] =='1')
		{
			$arr_old = Array();	
			$arr_old['building_name']= $this->get_clean_variable($params['building_name_old']);
			$arr_old['landmark']	= $this->get_clean_variable($params['landmark_old']);
			$arr_old['street']		= $this->get_clean_variable($params['street_old']);
			$arr_old['area']		= $this->get_clean_variable($params['area_old']);
			$arr_old['pincode']		= $this->get_clean_variable($params['pincode_old']);
			$arr_old['city']		= $this->get_clean_variable($params['city_old']);
			$arr_old['latitude']	= $this->get_clean_variable($params['latitude_old']);
			$arr_old['longitude']	= $this->get_clean_variable($params['longitude_old']);
			$arr_old['geocode_accuracy_level']	= $this->get_clean_variable($params['geocode_accuracy_level_old']);
		}
		else
		{
			$arr_old_data = Array();	
			$arr_old_data = $this->get_compdata();
			$arr_old = Array();	
			$arr_old['building_name']= $this->get_clean_variable($arr_old_data['building_name']);
			$arr_old['landmark']	= $this->get_clean_variable($arr_old_data['landmark']);
			$arr_old['street']		= $this->get_clean_variable($arr_old_data['street']);
			$arr_old['area']		= $this->get_clean_variable($arr_old_data['area']);
			$arr_old['pincode']		= $this->get_clean_variable($arr_old_data['pincode']);
			$arr_old['city']		= $this->get_clean_variable($arr_old_data['city']);
			$arr_old['latitude']	= $this->get_clean_variable($arr_old_data['latitude']);
			$arr_old['longitude']	= $this->get_clean_variable($arr_old_data['longitude']);
			$arr_old['geocode_accuracy_level']	= $this->get_clean_variable($arr_old_data['geocode_accuracy_level']);
		}
		if($params['trace'] == 1)
		{
			echo "<hr>";
			echo "<b>New Input param Array</b>";
			echo "<hr>";
			print_r($arr_new);
			echo "<b><hr>Live / Old data </b>";
			echo "<hr>";
			print_r($arr_old);			
			echo "<hr>";
		} 
		
		if(isset($params['map_geocode']) && $params['map_geocode'] == 1)
		{
			if(isset($arr_new['pincode']) && $arr_new['pincode']!='')
			{
				$pincode_geocode = $this->get_pincode_geocode($arr_new['pincode']);
				
				$distance 	=  $this->distance($pincode_geocode['latitude'], $pincode_geocode['longitude'], $arr_new['latitude'],  $arr_new['longitude'], 'K');
				$distance 	=  round($distance, 1);
				if($distance <= $pincode_geocode['new_redius'])
				{
					$geocode_arr['geocode_accuracy_level']	=	'1';
					$geocode_arr['latitude'] 	=	$arr_new['latitude'];
					$geocode_arr['longitude'] 	=	$arr_new['longitude'];
				}
				else
				{
					$geocode_arr = $this->getBestGeocode($arr_old, $arr_new);				
				}
			}
		}
		else if(count($arr_old)==0)
		{
		
			$geocode_arr = $this->getBestGeocode($arr_old, $arr_new);
		}
		else
		{
			$arr_old_datacity = $this->get_data_city($arr_old['city']);
			$arr_new_datacity = $this->get_data_city($arr_new['city']); 
			$arr_old['data_city']	= $arr_old_datacity;
			$arr_new['data_city']	= $arr_new_datacity;
			
			if(strtolower($arr_old_datacity) != strtolower($arr_new_datacity))
			{
				$geocode_arr = $this->getBestGeocode($arr_old, $arr_new);				
			}
			else
			{	
				if(strtolower($arr_new['building_name']) == strtolower($arr_old['building_name']) && strtolower($arr_new['landmark']) == strtolower($arr_old['landmark']) && strtolower($arr_new['street']) == strtolower($arr_old['street']) && strtolower($arr_new['area']) == strtolower($arr_old['area']) && strtolower($arr_new['pincode']) == strtolower($arr_old['pincode']) && strtolower($arr_new['city']) == strtolower($arr_old['city']) && strtolower($arr_new['latitude']) == strtolower($arr_old['latitude']) && strtolower($arr_new['longitude']) == strtolower($arr_old['longitude']) && strtolower($arr_new['geocode_accuracy_level']) == strtolower($arr_old['geocode_accuracy_level']))
				{
					$geocode_arr['geocode_accuracy_level'] = $arr_old['geocode_accuracy_level'];
					$geocode_arr['latitude']	=	$arr_old['latitude'];
					$geocode_arr['longitude']	=	$arr_old['longitude'];
					$geocode_arr['type'] 	   	=	'No change '.__LINE__;
					$geocode_arr['send_for_moderation'] 	   	=	'no';
					$geocode_arr['degrade_geocode'] 	   	=	'no';
				}
				else
				{					
					$geocode_arr = $this->check_geocode($arr_old, $arr_new);				 
				}	
			}
		}
		$map_pointer_flag_arr = $this->get_map_pointer_flag($arr_old['map_pointer_flags'],$arr_old['flags']);
		$geocode_arr['map_pointer_flag']	=	$map_pointer_flag_arr['map_pointer_flags'];
		$geocode_arr['flags'] 				=	$map_pointer_flag_arr['flags'];
		
		$geocode_return_arr['data']['parentid']		=	$params['parentid'];
		$geocode_return_arr['data']['data_city']	=	$params['data_city'];
		$geocode_return_arr['data']['geocode_accuracy_level']	=	$geocode_arr['geocode_accuracy_level'];
		$geocode_return_arr['data']['latitude']		=	$geocode_arr['latitude'];
		$geocode_return_arr['data']['longitude']	=	$geocode_arr['longitude'];
		$geocode_return_arr['data']['map_pointer_flag']	=	$geocode_arr['map_pointer_flag'];
		$geocode_return_arr['data']['flags']	=	$geocode_arr['flags'];
		
		$geocode_return_arr['data']['sent_to_moderation']		=	$geocode_arr['send_for_moderation'];
		$geocode_return_arr['data']['degrade_geocode']		=	$geocode_arr['degrade_geocode'];
		$geocode_return_arr['data']['type']		=	$geocode_arr['type'];
		$geocode_return_arr['error']['code']		=	'0';
		$geocode_return_arr['error']['message']		=	'success';
		if($params['trace'] == 1)
		{
			echo "<hr>";				 
			echo "<b>Output : </b><hr>";
			print_r($geocode_return_arr);
			echo "<hr>";
		}
		$this->log_api("get_geocode",$params,$geocode_return_arr,$arr_old,$arr_new);
		
		return ($geocode_return_arr);
			
	}	
	
	public function getBestGeocode($arr_old,$arr_new)
	{
		global $params;
		$best_geocode_arr = Array();
		$best_gecode_flag	=	'0';
		
		$val_changed = '0';
		$clean_building_arr 	=	$this->get_clean_values($arr_new['building_name']);
		//print_r($clean_building_arr);		
		
		$clean_landmark_arr 	=	$this->get_clean_values($arr_new['landmark']);
		//print_r($clean_landmark_arr);
		
		$clean_street_arr 	=	$this->get_clean_values($arr_new['street']);
		//print_r($clean_street_arr);
		 
		
		$clean_building 	=	str_replace(","," ",$clean_building_arr['value']);		
		$clean_landmark 	=	str_replace(","," ",$clean_landmark_arr['value']);		
		$clean_street 		=	str_replace(","," ",$clean_street_arr['value']);		
		
		$building_change_flag 	=	$clean_building_arr['changed'];		
		$landmark_change_flag 	=	$clean_landmark_arr['changed'];		
		$street_change_flag 		=	$clean_street_arr['changed'];	
		
		//echo $this->get_ignore_words();
		
		if(!empty($arr_new['area']))
		{
			$where_area = " AND parent_area='".addslashes($arr_new['area'])."'";
			$where_area_comp = " AND area='".addslashes($arr_new['area'])."'";
		}
		else
		{
			$where_area = '';
		}
		$ignore_words_arr = explode("|",$this->get_ignore_words());
		if(!empty($arr_new['building_name']))
		{ 
			$sql_building_check = "SELECT main_area,entity_area,latitude_final as latitude, longitude_final as longitude,'1' as gal,'buidling_master' as type FROM tbl_building_master_sphinx WHERE INSTR(' ".addslashes(trim(urldecode($arr_new['building_name'])))." ', CONCAT(' ', entity_area, ' ') ) > 0 AND entity_area != '' AND type_flag NOT IN (1,2,3,4) AND display_flag=1 AND pincode='".$arr_new['pincode']."' /*AND parent_area='".$arr_new['area']."'*/ 
			UNION 
			SELECT concat(companyname,'-',area) as main_area ,companyname as entity_area, latitude, longitude,'1' AS gal,'company_landmark_data' as type FROM tbl_company_landmark_autosuggest WHERE geocode_accuracy_level in (1,2) and INSTR(' ".addslashes(trim(urldecode($arr_new['building_name'])))." ', CONCAT(' ', companyname, ' ') ) > 0 AND area='".$arr_new['area']."'  AND pincode='".$arr_new['pincode']."'";
			
			if($params['trace']==1)
				echo "<br>".$sql_building_check;
			$res_building_check = parent::execQuery($sql_building_check, $this->conn); 
			if(mysql_num_rows($res_building_check) > 0)
			{
				$best_geocode_arr_temp =Array();
				$entity_area_arr = Array();
				$y=0;
				$x=0;
				while($row_building = mysql_fetch_assoc($res_building_check))
				{
					$y= strtolower($row_building['entity_area']);
					$best_geocode_arr_all[$y]['entity_area'] = strtolower($row_building['entity_area']);
					$best_geocode_arr_all[$y]['geocode_accuracy_level'] = '1';
					$best_geocode_arr_all[$y]['latitude']	=	$row_building['latitude'];
					$best_geocode_arr_all[$y]['longitude']	=	$row_building['longitude'];
					$best_geocode_arr_all[$y]['type']		=	'building_master_match_geocode '.__LINE__;
					foreach($ignore_words_arr AS $key=>$ig_word)
					{
						$entity_area  = strtolower($ig_word)." ".strtolower($row_building['entity_area']);
						if(strpos(strtolower($arr_new['building_name']), $entity_area ) !== false) 
						{
							$x= strtolower($row_building['entity_area']);
							$best_geocode_arr_landmark[$x]['entity_area'] = strtolower($row_building['entity_area']);
							$best_geocode_arr_landmark[$x]['geocode_accuracy_level'] = '2';
							$best_geocode_arr_landmark[$x]['latitude']	=	$row_building['latitude'];
							$best_geocode_arr_landmark[$x]['longitude']	=	$row_building['longitude'];
							$best_geocode_arr_landmark[$x]['type']		=	'building_master_match_geocode '.__LINE__;
							//$best_gecode_flag	=	'1';	
							$x++;
							unset($best_geocode_arr_all[strtolower($row_building['entity_area'])]);
						}
					} 
				}				 
			}
			if($params['trace']==1)
			{
				echo "<hr>best_geocode_arr_all ".count($best_geocode_arr_all);
				print_r($best_geocode_arr_all);
				
				echo "<hr>best_geocode_arr_landmark ".count($best_geocode_arr_landmark);
				print_r($best_geocode_arr_landmark);
				echo "<hr>";
			}
			
			
			if(count($best_geocode_arr_all)> count($best_geocode_arr_landmark) || (count($best_geocode_arr_all) == count($best_geocode_arr_landmark)))
			{
				//echo "<br>".__LINE__."<br>";
				if(count($best_geocode_arr_all)>0)
				{
					foreach($best_geocode_arr_all AS $key=>$geo_arr)
					{
						$best_geocode_arr = $geo_arr;
					}
				}	
			}
			else if(count($best_geocode_arr_all)<count($best_geocode_arr_landmark))
			{
				//echo "<br>".__LINE__."<br>";
				foreach($best_geocode_arr_landmark AS $key=>$geo_arr)
				{
					$best_geocode_arr = $geo_arr;
				}	
				//$best_gecode_flag= '1';	
			}
					
			if(count($best_geocode_arr)>0)
				$best_gecode_flag= '1';
		}
		if($best_gecode_flag == '0' && !empty($arr_new['building_name']))
		{
			$sql_landmark = "SELECT main_area,entity_area, latitude_final as latitude, longitude_final as longitude FROM tbl_areamaster_consolidated_v3 WHERE type_flag='2' AND de_display=1  AND display_flag=1 AND INSTR(' ".addslashes(trim(urldecode($arr_new['building_name'])))." ', CONCAT(' ', entity_area, ' ') ) > 0 AND pincode='".$arr_new['pincode']."' AND parent_area='".addslashes($arr_new['area'])."' ";
			$res_landmark = parent::execQuery($sql_landmark, $this->conn); 
			if($params['trace']==1)
				echo "<br>".$sql_landmark;
			if($res_landmark && mysql_num_rows($res_landmark) > 0)
			{
				$row_landmark = mysql_fetch_assoc($res_landmark);
				$best_geocode_arr['geocode_accuracy_level'] = LANDMARK_LEVEL;
				$best_geocode_arr['latitude']	=	$row_landmark['latitude'];
				$best_geocode_arr['longitude']	=	$row_landmark['longitude'];
				$best_geocode_arr['type'] 	   	=	'areamaster_landmark_match_geocode '.__LINE__;
				$best_gecode_flag	=	'1';	
			}
		}
		
		if($best_gecode_flag == '0' && !empty($arr_new['landmark']))
		{
			$sql_landmark = "SELECT main_area,entity_area, latitude_final as latitude, longitude_final as longitude FROM tbl_areamaster_consolidated_v3 WHERE type_flag='2' AND de_display=1  AND display_flag=1 AND entity_area ='".addslashes($clean_landmark)."' AND pincode='".$arr_new['pincode']."' AND parent_area='".addslashes($arr_new['area'])."' UNION 
			SELECT concat(companyname,'-',area) as main_area ,companyname as entity_area, latitude, longitude  FROM tbl_company_landmark_autosuggest WHERE geocode_accuracy_level in (1,2) and INSTR(' ".addslashes(trim(urldecode($clean_landmark)))." ', CONCAT(' ', companyname, ' ') ) > 0 AND area='".$arr_new['area']."' AND pincode='".$arr_new['pincode']."'";
			$res_landmark = parent::execQuery($sql_landmark, $this->conn); 
			if($params['trace']==1)
				echo "<br>".$sql_landmark;
			if($res_landmark && mysql_num_rows($res_landmark) > 0)
			{
				$row_landmark = mysql_fetch_assoc($res_landmark);
				$best_geocode_arr['geocode_accuracy_level'] = LANDMARK_LEVEL;
				$best_geocode_arr['latitude']	=	$row_landmark['latitude'];
				$best_geocode_arr['longitude']	=	$row_landmark['longitude'];
				$best_geocode_arr['type'] 	   	=	'areamaster_landmark_match_geocode '.__LINE__;
				$best_gecode_flag	=	'1';	
			}
		}
		
		if($best_gecode_flag == '0')
		{
			$sql_street = "SELECT main_area, latitude_final as latitude, longitude_final as longitude FROM tbl_areamaster_consolidated_v3 WHERE type_flag='3' AND de_display=1  AND display_flag=1 AND entity_area ='".addslashes($clean_street)."' AND pincode='".$arr_new['pincode']."' AND parent_area='".addslashes($arr_new['area'])."' ";
			$res_street = parent::execQuery($sql_street, $this->conn); 

			if($res_street && mysql_num_rows($res_street) > 0)
			{
				$row_street = mysql_fetch_assoc($res_street);
				$best_geocode_arr['geocode_accuracy_level'] = STREET_LEVEL;
				$best_geocode_arr['latitude']	=	$row_street['latitude'];
				$best_geocode_arr['longitude']	=	$row_street['longitude'];
				$best_geocode_arr['type'] 	   	=	'areamaster_street_match_geocode '.__LINE__;
				$best_gecode_flag	=	'1';	
			}
		}
		
		if($best_gecode_flag == '0')
		{
			$sql_area = "SELECT main_area, latitude_final as latitude, longitude_final as longitude FROM tbl_areamaster_consolidated_v3 WHERE type_flag='1' AND de_display=1  AND pincode='".$arr_new['pincode']."' AND areaname='".$arr_new['area']."' AND display_flag=1";
			$res_area = parent::execQuery($sql_area, $this->conn); 

			if($res_area && mysql_num_rows($res_area) > 0)
			{
				$row_area = mysql_fetch_assoc($res_area);

				$best_geocode_arr['geocode_accuracy_level'] = AREA_LEVEL;
				$best_geocode_arr['latitude']	=	$row_area['latitude'];
				$best_geocode_arr['longitude']	=	$row_area['longitude'];
				$best_geocode_arr['type'] 	   	=	'areamaster_area_match_geocode '.__LINE__;
				$best_gecode_flag	=	'1';	
			}
		}
		
		if($best_gecode_flag == '0')
		{
			$sql_pincode = "SELECT main_area, latitude_final as latitude, longitude_final as longitude FROM tbl_areamaster_consolidated_v3 WHERE type_flag='1' AND de_display=1  AND display_flag=1 AND pincode='".$arr_new['pincode']."'  LIMIT 1";
			$res_pincode = parent::execQuery($sql_pincode, $this->conn); 

			if($res_pincode && mysql_num_rows($res_pincode) > 0)
			{
				$row_pincode = mysql_fetch_assoc($res_pincode);

				$best_geocode_arr['geocode_accuracy_level'] = PINCODE_LEVEL;
				$best_geocode_arr['latitude']	=	$row_pincode['latitude'];
				$best_geocode_arr['longitude']	=	$row_pincode['longitude'];
				$best_geocode_arr['type'] 	   	=	'areamaster_pincode_match_geocode '.__LINE__;
				$best_gecode_flag	=	'1';	
			}
		}
		if($best_gecode_flag == '0')
		{
			$sql_city = "SELECT latitude_city AS latitude,longitude_city AS longitude FROM tbl_city_master WHERE ct_name='".addslashes($arr_new['city'])."' AND display_flag = 1 LIMIT 1";
			$res_city = parent::execQuery($sql_city, $this->conn); 
		
	 		$res_city = parent::execQuery($sql_city, $this->conn); 

			if($res_city && mysql_num_rows($res_city) > 0)
			{
				$row_city = mysql_fetch_assoc($res_city);

				$best_geocode_arr['geocode_accuracy_level'] = CITY_LEVEL;
				$best_geocode_arr['latitude']	=	$row_city['latitude'];
				$best_geocode_arr['longitude']	=	$row_city['longitude'];
				$best_geocode_arr['type'] 	   	=	'city_match_geocode '.__LINE__;
				$best_gecode_flag	=	'1';	
			}
		}
		return $best_geocode_arr;		 
	}
	public function check_geocode($arr_old, $arr_new)
	{
		global $params;
		$building_change	= (strtoupper($arr_old['building_name'])!=strtoupper($arr_new['building_name']) ? '1':'0');
		$landmark_change	= (strtoupper($arr_old['landmark'])!=strtoupper($arr_new['landmark']) ? '1':'0');
		$street_change	= (strtoupper($arr_old['street'])!=strtoupper($arr_new['street']) ? '1':'0');
		$area_change	= (strtoupper($arr_old['area'])!=strtoupper($arr_new['area']) ? '1':'0');
		$pincode_change	= (strtoupper($arr_old['pincode'])!=strtoupper($arr_new['pincode']) ? '1':'0');
		$city_change	= (strtoupper($arr_old['city'])!=strtoupper($arr_new['city']) ? '1':'0');
		$data_city_change	= (strtoupper($arr_old['data_city'])!=strtoupper($arr_new['data_city']) ? '1':'0');
	 
		if($params['trace'] == '1')
		{
			echo "<b><font color=red><hr>Change Flag </b><hr>";
			
			echo "<br>building	=>	".$building_change;
			echo "<br>landmark	=>	".$landmark_change;
			echo "<br>street	=>	".$street_change;
			echo "<br>area	=>	".$area_change;
			echo "<br>pincode	=>	".$pincode_change;
			echo "<br>city	=>	".$city_change;
			echo "<br>data_city	=>	".$data_city_change;
			
			echo "</font><hr>";
			echo "<hr>";
		}
		
		$within_radius = $this->get_pincode_radius_check($arr_new['pincode'],$arr_new['latitude'],$arr_new['longitude']);
		$geocode_check_arr = Array();
		if($within_radius == '0')
		{
			$geocode_check_arr = $this->getBestGeocode($arr_old, $arr_new);	
			$geocode_check_arr['send_for_moderation'] 	=	'yes';
			$geocode_check_arr['degrade_geocode'] 	=	'yes';
		}
		else
		{
			$geocode_check_arr = $this->getBestGeocode($arr_old, $arr_new);	
			switch($arr_old['geocode_accuracy_level'])
			{
				case BUILDING_LEVEL:
							
						//1. If only building name changed by user then don’t drop the geocode. If updated building is available in building master then update that geocode, else retain the geocode and send the same for moderation. (Remark: Building level – building change)	
						//if($building_change == '1'  && $landmark_change=='0' && $street_change=='0' && $area_change=='0' && $pincode_change=='0' && $city_change=='0' && $data_city_change=='0' )	
						
						
						$building_geocode = 'no';
						if($building_change == '1')	
						{
							if($geocode_check_arr['geocode_accuracy_level'] == '1')
							{
								$degrade_geocode = 'no';
								$send_for_moderation = 'no';
								$building_geocode = 'yes';
							}
							else if($geocode_check_arr['geocode_accuracy_level'] == '2')
							{
								$degrade_geocode = 'yes';
								$send_for_moderation = 'yes';
							}
							else
							{
								$degrade_geocode = 'no';
								$send_for_moderation = 'yes';
							}
							
						}
						if($landmark_change=='1')	
						{
							if($degrade_geocode != 'yes')
								$degrade_geocode = 'no';
							if($send_for_moderation != 'yes')	
								$send_for_moderation = 'no';
						}
						if($street_change=='1')	
						{
							if($degrade_geocode != 'yes')
								$degrade_geocode = 'no';
							if($send_for_moderation != 'yes')	
								$send_for_moderation = 'no';
						}
						if($area_change=='1')	
						{
							if($degrade_geocode != 'yes')
								$degrade_geocode = 'no';
							if($send_for_moderation != 'yes')	
								$send_for_moderation = 'yes';
						}
						if($pincode_change=='1')	
						{
							if($within_radius == '1')
							{
								if($degrade_geocode != 'yes')
									$degrade_geocode = 'no';
								if($send_for_moderation != 'yes')	
									$send_for_moderation = 'yes';
							}
							else
							{
								$degrade_geocode = 'yes';
								$send_for_moderation = 'yes';		
							}
						}
						if($city_change=='1')	
						{
							if($within_radius == '1')
							{
								if($degrade_geocode != 'yes')
									$degrade_geocode = 'no';
								if($send_for_moderation != 'yes')	
									$send_for_moderation = 'no';
							}
							else
							{
								$degrade_geocode = 'yes';
								$send_for_moderation = 'yes';
							}						 
						}
						if($degrade_geocode == 'yes' || $building_geocode == 'yes')
						{
							$geocode_check_arr['geocode_accuracy_level']	=	$geocode_check_arr['geocode_accuracy_level'];;
							$geocode_check_arr['latitude'] 		=	$geocode_check_arr['latitude'];
							$geocode_check_arr['longitude'] 	=	$geocode_check_arr['longitude'];
							
						}
						else
						{
							$geocode_check_arr['geocode_accuracy_level']	=	$arr_old['geocode_accuracy_level'];;
							$geocode_check_arr['latitude'] 		=	$arr_old['latitude'];
							$geocode_check_arr['longitude'] 	=	$arr_old['longitude'];
						
						}
						$geocode_check_arr['send_for_moderation'] 	=	$send_for_moderation;
						$geocode_check_arr['degrade_geocode'] 	=	$degrade_geocode;
						
				break;

				case LANDMARK_LEVEL:
				
					$building_geocode = 'no';
					if($building_change == '1')	
					{
						if($geocode_check_arr['geocode_accuracy_level'] == '1' || $geocode_check_arr['geocode_accuracy_level'] == '2')
						{
							$degrade_geocode = 'no';
							$send_for_moderation = 'no';
							$building_geocode = 'yes';
						}
						else
						{
							$degrade_geocode = 'no';
							$send_for_moderation = 'no';
						}
						
					}
					if($landmark_change=='1')	
					{
						if($geocode_check_arr['geocode_accuracy_level'] == '1' || $geocode_check_arr['geocode_accuracy_level'] == '2')
						{
							$degrade_geocode = 'no';
							$send_for_moderation = 'yes';
							$building_geocode = 'yes';
						}
						else
						{
							$degrade_geocode = 'no';
							$send_for_moderation = 'yes';
						}
					
						/*if($degrade_geocode != 'yes')
							$degrade_geocode = 'no';
						if($send_for_moderation != 'yes')	
							$send_for_moderation = 'no';
						*/	
					}
					if($street_change=='1')	
					{
						if($degrade_geocode != 'yes')
							$degrade_geocode = 'no';
						if($send_for_moderation != 'yes')	
							$send_for_moderation = 'no';
					}
					if($area_change=='1')	
					{
						if($degrade_geocode != 'yes')
							$degrade_geocode = 'no';
						if($send_for_moderation != 'yes')	
							$send_for_moderation = 'yes';
					}
					if($pincode_change=='1')	
					{	
						if($within_radius == '1')
						{
							if($degrade_geocode != 'yes')
								$degrade_geocode = 'no';
							if($send_for_moderation != 'yes')	
								$send_for_moderation = 'no';
						}
						else
						{
							$degrade_geocode = 'yes';
							$send_for_moderation = 'yes';		
						}
					}
					if($city_change=='1')	
					{
						if($within_radius == '1')
						{
							if($degrade_geocode != 'yes')
								$degrade_geocode = 'no';
							if($send_for_moderation != 'yes')	
								$send_for_moderation = 'no';
						}
						else
						{
							$degrade_geocode = 'yes';
							$send_for_moderation = 'yes';
						}						 
					}
					if($degrade_geocode == 'yes' || $building_geocode == 'yes')
					{
						$geocode_check_arr['geocode_accuracy_level']	=	$geocode_check_arr['geocode_accuracy_level'];;
						$geocode_check_arr['latitude'] 		=	$geocode_check_arr['latitude'];
						$geocode_check_arr['longitude'] 	=	$geocode_check_arr['longitude'];
						
					}
					else
					{
						$geocode_check_arr['geocode_accuracy_level']	=	$arr_old['geocode_accuracy_level'];;
						$geocode_check_arr['latitude'] 		=	$arr_old['latitude'];
						$geocode_check_arr['longitude'] 	=	$arr_old['longitude'];
					
					}
					$geocode_check_arr['send_for_moderation'] 	=	$send_for_moderation;
					$geocode_check_arr['degrade_geocode'] 	=	$degrade_geocode;
					
			}
		}
		return $geocode_check_arr;
	}
	public function get_pincode_geocode($pincode)
	{
		$sql =	"SELECT latitude, longitude, new_radius FROM geocode_pincode_master WHERE pincode='".$pincode."' ";
		$res = parent::execQuery($sql, $this->conn); 
		$row = array();
		if($res && mysql_num_rows($res) > 0)
		{
			$row 		= mysql_fetch_assoc($res);			 	
		}
		return $row;
	}	
	public function get_pincode_radius_check($pincode,$latitude_new,$longitude_new)
	{
		global $params;
		$within_radius = '0';
		$sql =	"SELECT latitude, longitude, new_radius FROM geocode_pincode_master WHERE pincode='".$pincode."' ";
		$res = parent::execQuery($sql, $this->conn); 
		$row = array();
		if($res && mysql_num_rows($res) > 0)
		{
			$pincode_geocode 		= mysql_fetch_assoc($res);	
			$distance 	=  $this->distance($pincode_geocode['latitude'], $pincode_geocode['longitude'], $latitude_new,  $longitude_new, 'K');
			$distance 	=  round($distance, 1);
			if($params['trace'] == '1')
			{
				echo "<hr><b><font color=red>distance => ".$distance;
				echo "<hr></b></font>";
			}
			if($distance <= $pincode_geocode['new_radius'])
			{
				$within_radius = '1';
			}		 	
		}
		return $within_radius;
		
	}
	public function get_map_pointer_flag($map_pointer_flags_old,$flags_old)
	{
		 
		if(empty($map_pointer_flags_old))
		{
			$map_pointer_flags = 0;
		}
		else
		{
			$map_pointer_flags = $map_pointer_flags_old;
		}
		if(empty($flags_old))
		{
			$flags_value = 0;
		}
		else
		{
			$flags_value = $flags_old;
		}

		// as discussed with rohit tandon: if building level and landmark level contracts then set to 2. if it is already 2 then do not do anything.
		if(1)
		{
			$map_bit_value 		= $map_pointer_flags&2;
			$flags_bit_value 	= $flags_value&2;

			if($map_bit_value == 2)
			{
				
			}
			else
			{
				$map_pointer_flags = $map_pointer_flags + 2;
			}

			if($flags_bit_value == 2)
			{
			}
			else
			{
				$flags_value = $flags_value + 2;
			}
		}/*
		else
		{
			if(($map_pointer_flags & 2) == 2)
			{
				$map_pointer_flags = ($map_pointer_flags ^ 2) ;  // have to UNSET the 2nd bit
			}
			if(($flags_value & 2) == 2)
			{
				$flags_value = ($flags_value ^ 2);
			}
		}*/
	 
		$output_arr['map_pointer_flags']	= $map_pointer_flags;
		$output_arr['flags'] 				= $flags_value;
		
		return $output_arr;		
	}
	public function get_city_latlong($arr_param)
	{		 
		$sql_latlong = "SELECT latitude_city,longitude_city FROM tbl_city_master WHERE ct_name='".addslashes($arr_param['city'])."' LIMIT 1";
		$res_latlong = parent::execQuery($sql_latlong, $this->conn); 
		
		$row_latlong = mysql_fetch_assoc($res_latlong);
		$arr_return_latlong['latitude'] = $row_latlong['latitude_city'];
		$arr_return_latlong['longitude'] = $row_latlong['longitude_city'];
		return $arr_return_latlong;
	}
	public function get_clean_values($val)
	{
		$pattern_pipe_sep_iw = "/".str_replace("/","\/",$this->get_ignore_words())."/i";
		$clean_val 	 		 = trim(preg_replace($pattern_pipe_sep_iw, "", $val));
		
		if($clean_val != $val)
		{
			$ret_arr['value'] = $clean_val;
			$ret_arr['changed'] = '1';
		}
		else
		{
			$ret_arr['value'] = $clean_val;
			$ret_arr['changed'] = '0';
		}
		return $ret_arr;
	}
	public function get_ignore_words()
	{
		$sql = "SELECT GROUP_CONCAT(ignore_words) AS ignore_words FROM  (SELECT * FROM tbl_ignore_words_for_geocode  ORDER BY LENGTH(ignore_words) DESC) as a";
		$res = parent::execQuery($sql, $this->conn); 

		if(mysql_num_rows($res) > 0)
		{
			$row = mysql_fetch_assoc($res);
			$ignore_words = str_replace(",", "|", strtoupper($row['ignore_words']));
			return $ignore_words;
		}
	}
	public function distance($lat1, $lon1, $lat2, $lon2, $unit)
	{
		$theta = $lon1 - $lon2;
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);
		if ($unit == "K")
			return ($miles * 1.609344);
		else if ($unit == "N")
			return ($miles * 0.8684);
		else
			return $miles;
	}
	function get_data_city($city)
	{
		$data_city = "";
		$sql = "SELECT * FROM tbl_city_master WHERE ct_name='".$city."' LIMIT 1";
		$res = parent::execQuery($sql, $this->conn); 

		if($res && mysql_num_rows($res) > 0)
		{
			$row = mysql_fetch_assoc($res);
			$data_city = $row['data_city'];
		}
		return $data_city;	
	}
	function get_compdata()
	{
		$comp_params = array();
		$comp_params['data_city'] 	= $this->data_city;
		$comp_params['table'] 		= 'gen_info_id,extra_det_id';		
		$comp_params['parentid'] 	= $this->parentid;
		$comp_params['fields']		= 'parentid,companyname,a.building_name,landmark, street, area, pincode, geocode_accuracy_level, city, latitude, longitude, map_pointer_flags, flags';
		$comp_params['action']		= 'fetchdata';
		$comp_params['page']		= 'geocode_api_class';
		$comp_params['skip_log']	= 1;

		$comp_api_res  	= '';
		$comp_api_arr	= array();
		$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
		if($comp_api_res!=''){
			$comp_api_arr 	= json_decode($comp_api_res,TRUE);
		}
		$return_arr = $comp_api_arr['results']['data'][$this->parentid];
		return $return_arr;
	}
	public function log_api($function,$params,$geocode_return_arr,$arr_old=Array(),$arr_new=Array())
	{
		if(!(isset($params['test']) && $params['test']== '1'))
		{
			$curl_url = $this->jdbox_service_url."geocode_api.php";
			$var_get = http_build_query($params);
			
			$arr_old_for_log = Array();
			$arr_old_for_log['building_name_old']= $arr_old['building_name'];
			$arr_old_for_log['landmark_old']	= $arr_old['landmark'];
			$arr_old_for_log['street_old']		= $arr_old['street'];
			$arr_old_for_log['area_old']		= $arr_old['area'];
			$arr_old_for_log['pincode_old']		= $arr_old['pincode'];
			$arr_old_for_log['city_old']		= $arr_old['city'];
			$arr_old_for_log['latitude_old']	= $arr_old['latitude'];
			$arr_old_for_log['longitude_old']	= $arr_old['longitude'];
			$arr_old_for_log['geocode_accuracy_level_old']	= $arr_old['geocode_accuracy_level'];
			
			$var_get_for_test = http_build_query(array_merge($params,$arr_old_for_log));
			$insert_log = "INSERT INTO tbl_geocode_api_details 
								SET
							parentid	=	'".$this->parentid."',
							data_city	=	'".$this->data_city."',
							latitude_old	=	'".$arr_old['latitude']."',
							longitude_old	=	'".$arr_old['longitude']."',
							latitude_new	=	'".$geocode_return_arr['data']['latitude']."',
							longitude_new	=	'".$geocode_return_arr['data']['longitude']."',
							geocode_accuracy_level_old	=	'".$arr_old['geocode_accuracy_level']."',
							geocode_accuracy_level_new	=	'".$geocode_return_arr['data']['geocode_accuracy_level']."',
							send_for_moderation	=	'".$geocode_return_arr['data']['sent_to_moderation']."',
							degrade_geocode	=	'".$geocode_return_arr['data']['degrade_geocode']."',
							call_time	=	now(),
							old_data	=	'".json_encode($arr_old)."',
							new_data	=	'".json_encode($arr_new)."',
							url			=	'".$curl_url."?".$var_get."',
							url_for_test=	'".$curl_url."?".$var_get_for_test."&test=1',
							output		=	'".json_encode($geocode_return_arr)."',
							source		=	'".$params['source']."'";
			$res_insert_log = parent::execQuery($insert_log, $this->conn); 
			 
		}
	}
	function getCurlURL()
	{
		$configclassobj	=	new configclass();
		$urldetails		=	$configclassobj->get_url(urldecode($this->data_city));
		return $urldetails;
	}
	public function get_clean_variable($var)
	{
		$var1 = ucwords(strtolower(trim(urldecode($var))));
		if(empty($var1))
		{
			$var1 = ucwords(strtolower(trim($var)));
		}
		return $var1;
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
