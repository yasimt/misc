<?php
class location_class extends DB
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
		$this->categoryClass_obj = new categoryClass();
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
	
	function get_state()
	{
		global $params;
		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get State List\n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		 
		if(!empty($params['stateid']))
			$state_id 	= 	$params['stateid'];
		if(!empty($params['statenm']))
			$state_nm 	= 	$params['statenm'];
		if(!empty($params['country_id']))
			$country_id 	= 	$params['country_id'];

		if(!empty($state_id))
			$where_arr[] = " state_id  = '". $state_id ."'";
		else if(!empty($state_nm))
			$where_arr[] = " st_name = '". $state_nm ."'";

		if(!empty($country_id))
		{
			if(!empty($where))
				$where_arr[] = " country_id = '". $country_id ."'";
			else
				$where_arr[] = " country_id = '". $country_id ."'";
		}
		if(!empty($params['search']))
			$where_arr[] = " st_name LIKE '%". $params['search'] ."%'";
		
		if(count($where_arr)>0)
			$where = " AND ".implode(" AND ",$where_arr);
		if(!empty($params['limit']))
			$limit  = " LIMIT ".$params['limit'];	
		$sql = "SELECT st_name as state_name,state_id,short_code FROM state_master WHERE delete_flag= 0 " .$where . " ORDER BY length(st_name) ".$limit;
		$res = parent::execQuery($sql, $this->conn_idc); 
		$numRows = mysql_num_rows($res);
		if($numRows > 0){		 
			while($result = mysql_fetch_assoc($res))
			{
				$return_array[] = $result;				 
			}			
		}
		$output['numRows'] 				=   $numRows; 
		$output['result'] 				=   $return_array;
		$output['error']['message'] 		=  "success";		
		if($params['trace'] == 1){
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}		
		return ($output);
	}	
	public function get_city()
	{
		global $params;
		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get area/street/landmark details from pincode/city/data_city/parent_area\n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		if(!empty($params['city']))
			$where_arr[] = "  ct_name = '".$params['city']."'";		
		if(!empty($params['city_id']))
			$where_arr[] = "  city_id = '".$params['city_id']."'";		
		if(!empty($params['data_city']))
			$where_arr[] = "  data_city = '".$params['data_city']."'";
		if(!empty($params['state_id']))
			$where_arr[] = "  state_id = '".$params['state_id']."'";
		if(!empty($params['state_name']))
			$where_arr[] = "  state_name = '".$params['state_name']."'";
			
		if(!empty($params['type']) || $params['type'] == 0)
		{
			switch($params['type'])
			{
				case '0' : $where_arr[] = "  type_flag = ".$params['type']; break;// main city		
				case '1' : $where_arr[] = "  type_flag = ".$params['type']; break;// routed city		
				case '2' : $where_arr[] = "  type_flag = ".$params['type']; break;		// synonym city		
				case '3' : $where_arr[] = "  type_flag = 0 AND allow_data = 1 "; break;		// Data city
				case '4' : $where_arr[] = "  DE_display = 1 ";	 break;		// DE cities
				case '5' : $where_arr[] = "  multicity_display = 1 "; break;// multicities
				case '6' : $where_arr[] = "  iro_display = 1 "; break;		// IRO coties
				case '7' : $where_arr[] = "  web_display = 1 "; break;		// WEB coties
				
			} 
		}	
		if(!empty($params['search']))
			$where_arr[] = "  ct_name like  '%".$params['search']."%'";		
		
		if(count($where_arr)>0)
			$where = " AND ".implode(" AND ",$where_arr);
			
		if(!empty($params['limit']))
			$limit  = " LIMIT ".$params['limit'];	
			
		$sql = "SELECT city_id,ct_name,main_city as route_city,default_area as  area_route,state_id,state_name,country_id,country_name,stdcode,multicity_display,allow_data,iro_display,web_display,DE_display,display_flag,data_city,zones,type_flag,countryzone,countryzone_id,latitude_city,longitude_city,mapped_cityname,mapped_cityid,dialer_mapped_cityname,iro_map_city,de_remotezone,de_tourist,top_city_flag,data_src,sGroup FROM tbl_city_master WHERE display_flag = 1 ".$where." ORDER BY length(ct_name) ".$limit;
		$res = parent::execQuery($sql, $this->conn_idc); 		
		$numRows = mysql_num_rows($res);
		if($numRows > 0){
			while($result = mysql_fetch_assoc($res)){				
				$return_array[] 		= $result;
			}
		}
		$output['numRows'] 				=   $numRows;
		$output['result'] 				=   $return_array;
		$output['error']['message'] 	=  "success";			 
		if($params['trace'] == 1){
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}	
		return ($output);

	}	
	public function get_data_city()
	{
		global $params;
		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get area/street/landmark details from pincode/city/data_city/parent_area\n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		if(!empty($params['city']))
		{
			$sql = "SELECT ct_name as city,data_city,display_flag,type_flag FROM tbl_city_master WHERE display_flag = 1 AND  ct_name = '".$params['city']."' ORDER BY length(ct_name) LIMIT 1 ";
			$res = parent::execQuery($sql, $this->conn_idc); 		
			$numRows = mysql_num_rows($res);
			if($numRows > 0)
			{
				$result = mysql_fetch_assoc($res);
				$return_array[] = $result;
			}
			$message 	=  "success";
		}
		else
		{
			$numRows = '0';
			$return_array = Array();
			$message 	=  "Invalid param";
		}
		$output['numRows'] 				=   $numRows;
		$output['result'] 				=   $return_array;
		$output['error']['message'] 	=   $message;			 
		if($params['trace'] == 1){
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}	
		return ($output);
	}		
	public function get_pincode()
	{
		global $params;
		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get Pincode details from cityname\n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		} 
		if(!empty($params['city']))
			$where_arr[] = "  city = '".$params['city']."'"; 		
		if(!empty($params['data_city']))
			$where_arr[] = "  data_city = '".$params['data_city']."'"; 
		if(!empty($params['area']))
			$where_arr[] = "  area = '".$params['area']."'";		
		if(!empty($params['zoneid']))
			$where_arr[] = "  zoneid = '".$params['zoneid']."'"; 
		if(!empty($params['pincode']))
		{
			if($params['autosuggest'])
				$where_arr[] = "  pincode LIKE '".addslashes($params['pincode'])."%'";
			else
				$where_arr[] = "  pincode = '".$params['pincode']."'";			
		}
			
		if(count($where_arr)>0)
			$where = " AND ".implode(" AND ",$where_arr);
		if($params['autosuggest'])
			$where .= " GROUP BY pincode ";
			
		$limit='';
		if(!empty($params['limit']))
			$limit  = " LIMIT ".$params['limit'];	
			
		$sql = "SELECT DISTINCT pincode,stdcode,city ,state,data_city,zoneid,latitude_pincode,longitude_pincode,callcnt_perday  FROM tbl_areamaster_consolidated_v3 WHERE display_flag= 1 " .$where . " ORDER BY pincode ".$limit;
		$res = parent::execQuery($sql, $this->conn_idc); 
		$numRows = mysql_num_rows($res);
		if($numRows > 0){
			while($result = mysql_fetch_assoc($res)){				
				$return_array['pincode'][$result['pincode']]['pincode'] 	= $result['pincode'];				 
				$return_array['pincode'][$result['pincode']]['stdcode'] 	= $result['stdcode'];				 
				$return_array['pincode'][$result['pincode']]['city'] 		= $result['city'];				 
				$return_array['pincode'][$result['pincode']]['state'] 		= $result['state'];				 
				$return_array['pincode'][$result['pincode']]['data_city'] 	= $result['data_city'];				 
				$return_array['pincode'][$result['pincode']]['zoneid'] 		= $result['zoneid'];				 
				$return_array['pincode'][$result['pincode']]['latitude'] 	= $result['latitude_pincode'];				 
				$return_array['pincode'][$result['pincode']]['longitude'] 	= $result['longitude_pincode'];				 
				$return_array['pincode'][$result['pincode']]['callcnt_perday'] 	= $result['callcnt_perday'];				 
				
			}
		}
		$output['numRows'] 				=   $numRows;
		$output['result'] 				=   $return_array;
		$output['error']['message'] 	=  "success";			 
		if($params['trace'] == 1){
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}	
		return ($output);
	}	 
	public function get_area()
	{
		global $params;
		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get area/street/landmark details from pincode/city/data_city/parent_area\n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		$search	= str_replace(" ","",$this->sanitize(trim(preg_replace('/(\([^)]*\))/','',urldecode(trim($params['search']))))));
		if(!empty($params['pincode']))
			$where_arr[] = "  pincode = '".$params['pincode']."'";
		if(!empty($params['city']))
			$where_arr[] = "  city = '".$params['city']."'";
		if(!empty($params['data_city']))
			$where_arr[] = "  data_city = '".$params['data_city']."'";	
		if(isset($params['autosuggest']) && $params['autosuggest']==1)
		{	
			if(!empty($params['search']))
				//$where_arr[] = "  areaname like  '".$params['search']."%'";
				$where_arr[] = "  areaname_search_processed_ws like  '".$search."%'";
		}
		else
		{
			if(!empty($params['search']))
				$where_arr[] = "  areaname like  '%".$params['search']."%'";
		}	
		if(!empty($params['parent_area']) && !empty($params['type']) && $params['type']!=1 )
			$where_arr[] = "  parent_area =  '".$params['parent_area']."'";		
		if(!empty($params['type']))
			$where_arr[] = " type_flag =  '".$params['type']."'";
			
		if(count($where_arr)>0)
			$where = " AND ".implode(" AND ",$where_arr);
		
		if(isset($params['autosuggest']) && $params['autosuggest']==1)
		{
			$where .= " GROUP BY areaname "; 
		}
		$limit = 0;
		if(!empty($params))
			$limit = $params['limit'];
		
		$ho_gpo_arr = array(' HO',' H.O.',' H O',' H. O.',' GPO',' G P O',' G.P.O.',' G. P. O.');
		
		$sql = "SELECT DISTINCT areaname ,main_area,areaname_display,pincode,stdcode,parent_area ,entity_area,city,data_city,state,country,country_id,zoneid,type_flag,latitude_area,longitude_area,latitude_pincode,longitude_pincode,latitude_final,longitude_final,latitude_median,longitude_median,de_display,LENGTH(areaname) AS area_len FROM tbl_areamaster_consolidated_v3 WHERE  display_flag= 1   " .$where . " ORDER BY LENGTH(areaname)";
		$res = parent::execQuery($sql, $this->conn_idc); 
		$numRows = mysql_num_rows($res);
		if($numRows > 0){
			$x=0;
			$y=0;
			$z=0;
			$p=0;
			while($result = mysql_fetch_assoc($res))
			{
				$skip_ho_gpo = 0;
				foreach($ho_gpo_arr AS $key=>$val)
				{
					if(strpos(strtoupper($result['areaname']),$val))
					{
						$skip_ho_gpo = 1;break;
					}
				}
				if($skip_ho_gpo == 0 || strtolower($params['live_area']) == strtolower($result['areaname']))
				{
					if($result['type_flag'] == 1 && $result['de_display'] == 1){
						if($limit>0 && $x < $limit)
							$return_array['areaname'][$x] = $result;				 					
						else if($limit==0)
							$return_array['areaname'][$x] = $result;				 					
						$x++;	 
					}	
					else if($result['type_flag'] == 2  && $result['de_display'] == 1){
						if($limit>0 && $y < $limit)
							$return_array['landmark'][$y]=$result;				 					
						else if($limit==0)
							$return_array['landmark'][$y]=$result;
						$y++;
					}	
					else if($result['type_flag'] == 3  && $result['de_display'] == 1){
						if($limit>0 && $z < $limit)
							$return_array['street'][$z] = $result;
						else if($limit==0)
							$return_array['street'][$z] = $result;
						$z++;	
					}
					else if($result['type_flag'] == 4  && $result['de_display'] == 0){
						if($limit>0 && $p < $limit)
							$return_array['synonym'][$p] = $result;	
						else if($limit==0) 		
							$return_array['synonym'][$p] = $result;	
						$p++;	
					}
				}
				
			}
		}
		
		$output['numRows']  			=   $numRows;
		$output['result']  				=   $return_array;
		$output['error']['message'] 	=  "success";			 
		if($params['trace'] == 1){
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}	
		return ($output);
	}
	public function get_building()
	{
		global $params;
		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get Building names from pincode";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		} 
		 
		if(!empty($params['pincode']))
			$where_arr[] = " pincode = '".$params['pincode']."' ";
		if(!empty($params['city']))
			$where_arr[] = " city = '".$params['city']."' ";	
		if(!empty($params['search']))
			$where_arr[] = " building_name like  '%".$params['search']."%' ";
		
		if(count($where_arr)>0)
			$where = " WHERE ".implode(" AND ",$where_arr);
		
		if(!empty($params['limit']))
			$limit  = " LIMIT ".$params['limit'];
		$sql = "SELECT * FROM online_regis1.tbl_building_master " .$where . "ORDER BY Building_name ".$limit;
		$res = parent::execQuery($sql, $this->conn_idc);
		
		$numRows = mysql_num_rows($res);
		if($numRows > 0){
			$x=0;
			while($result = mysql_fetch_assoc($res)){
				$return_array[$x]['building_name'] 	=	ucwords($result['building_name']);				 
				$return_array[$x]['area'] 			=	ucwords($result['area']);				 
				$return_array[$x]['pincode'] 		=	$result['pincode'];				 
				$return_array[$x]['city'] 			=	ucwords($result['city']);				 
				$return_array[$x]['data_city'] 		=	ucwords($result['data_city']);				 
				$return_array[$x]['latitude'] 		=	$result['latitude'];
				$return_array[$x]['longitude'] 		=	$result['longitude'];

				$x++;
			}
		}
		$output['numRows'] 				=   $numRows;
		$output['result'] 				=   $return_array;
		$output['error']['message'] 	=  "success";			 
		if($params['trace'] == 1){
			echo "<br>".$sql."<br>";
			print_r($output);
		}	
		return ($output);
	}
	function new_area_pincode_request()
	{
		global $params;
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : New area/pincode request ";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		} 
		$check_exist_area = "SELECT * FROM online_regis1.tbl_areamaster_consolidated_v3 WHERE areaname='".$params['area']."' AND pincode = '".$params['pincode']."' AND data_city = '".$params['data_city']."' AND display_flag=1  LIMIT 1";		
	
		$res_check_exist_area = parent::execQuery($check_exist_area, $this->conn_idc);
		if($res_check_exist_area && mysql_num_rows($res_check_exist_area)>0)
		{
			while($row_area = mysql_fetch_assoc($res_check_exist_area))
			{
				if($row_area['type_flag'] == 1 && $row_area['de_display'] == 1)
					$msg[] = "Exist as an Area";
				else if($row_area['type_flag'] == 1 && $row_area['de_display'] == 0)	
					$msg[] = "Exist as an East/West Area";
				else if($row_area['type_flag'] == 4)
					$msg[] = "Exist as Synonym";
			}	
		}
  		
		$check_exist_street_landmark = "SELECT * FROM online_regis1.tbl_areamaster_consolidated_v3 WHERE  entity_area = '".$params['area']."' AND pincode = '".$params['pincode']."' AND data_city = '".$params['data_city']."' AND display_flag=1 LIMIT 1";								
		$res_check_exist_street_landmark = parent::execQuery($check_exist_street_landmark, $this->conn_idc);
		if($res_check_exist_street_landmark && mysql_num_rows($res_check_exist_street_landmark)>0)
		{
			$row_street_landmark = mysql_fetch_assoc($res_check_exist_street_landmark);
			if($row_street_landmark['type_flag'] == 2)
			{
				$msg[] =  "Exist as Landmark";
			}
			if($row_street_landmark['type_flag'] == 3)
			{
				$msg[] =  "Exist as Street";
			}
			if($row_street_landmark['type_flag'] == 4 && $row_street_landmark['de_display']==0)
			{
				$msg[] =  "Exist as Synonym";
			}
				
		}
		if(empty($msg))
		{
			if($params['type'] == 'area') 	$type_flag = '1';
			if($params['type'] == 'landmark') $type_flag = '2';
			if($params['type'] == 'street') 	$type_flag = '3';
			
			
			$sel_state = "SELECT DISTINCT ct_name,data_city,state_name FROM tbl_city_master WHERE ct_name='".$params['city']."' AND display_flag= 1 LIMIT 1";
			$res_state = parent::execQuery($sel_state, $this->conn_idc);
			if($res_state && mysql_num_rows($res_state)>0)
			{
				$row_state = mysql_fetch_assoc($res_state);
				$state_name =	$row_state['state_name'];
				$data_city =	$row_state['data_city'];
			}
			if(empty($data_city))
				$data_city = $params['data_city'];
				
			require_once('../library/configclass.php');
			$configclassobj= new configclass();
			$urldetails		=	$configclassobj->get_url(urldecode($data_city));
			$jdbox_ip_url	=	$urldetails['jdbox_service_url'].'contract_type.php';
			
			$param_status = Array();
			$param_status['parentid'] 	=	$params['parentid'];
			$param_status['rquest']		=	'get_contract_type';
			$param_status['data_city']	=	$data_city;
			
			$resp = $this->get_curl_data($jdbox_ip_url,$param_status);
			$ret = json_decode($resp,true);	
			$insert = "
			INSERT IGNORE INTO online_regis1.tbl_new_area_pincode_request
			SET
				parentid 		=	'".$params['parentid']."',
				areaname		=	'".$params['area']."',
				pincode 		=	'".$params['pincode']."',
				city 			=	'".$params['city']."',
				state 			=	'".$state_name."',
				stdcode 		=	'".$params['stdcode']."',
				data_city 		=	'".$data_city."',
				type_flag		=	'".$type_flag."',
				requestby_code	=	'".$params['ucode']."',
				requestby_name	=	'".$params['uname']."',
				module			=	'".$params['module']."',
				paid			=	'".$ret['result']['paid']."',
				request_date	=	now()";
			$res_insert = parent::execQuery($insert, $this->conn_idc);
			if($res_insert)
				$msg[] = "Request Sent Successfully";
		 
		}
		$output['result'] 				=   $msg;
		$output['error']['message'] 	=  "success"; 
		if($params['trace'] == 1){
			echo "<br>".$sql."<br>";
			echo "<br>".$insert."<br>";
			print_r($output);
		}
		return ($output);
	
	}
	function get_stdcode()
	{
		global $params;
		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get stdcode from pincode/city\n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		 
		if(!empty($params['pincode']))
			$pincode 	= 	$params['pincode'];
		if(!empty($params['city']))
			$city 	= 	$params['city'];
		if(!empty($params['data_city']))
			$data_city 	= 	$params['data_city'];
			
		if(!empty($params['type']))
			$type 	= 	$params['type'];	

		if(!empty($pincode))
			$where_arr[] = " pincode  = '". $pincode ."'";
		else if(!empty($city))
			$where_arr[] = " cityname = '". $city ."'";

		 
		if(count($where_arr)>0)
			$where = " AND ".implode(" AND ",$where_arr);
		if(!empty($params['limit']))
			$limit  = " LIMIT ".$params['limit'];	
		if($type == 'pincode')	
			$sql = "SELECT DISTINCT pincode,stdcode FROM tbl_areamaster_consolidated_v3 WHERE display_flag = 1 " .$where . "   ".$limit;
		else if($type == 'city')
			$sql = "SELECT DISTINCT ct_name as city,stdcode FROM tbl_city_master WHERE display_flag = 1 " .$where . "   ".$limit;
		$res = parent::execQuery($sql, $this->conn_idc); 
		$numRows = mysql_num_rows($res);
		if($numRows > 0){		 
			while($result = mysql_fetch_assoc($res))
			{
				$return_array[] = $result;				 
			}			
		}
		$output['numRows'] 				=   $numRows; 
		$output['result'] 				=   $return_array;
		$output['error']['message'] 		=  "success";		
		if($params['trace'] == 1){
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}		
		return ($output);
	}
	function get_docid()
	{
		global $params;
		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get docid \n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}		 
		
		$sql = "SELECT a.*,b.companyname,b.mobile,b.area,b.pincode FROM tbl_id_generator a LEFT JOIN tbl_companymaster_generalinfo b ON a.parentid=b.parentid WHERE a.parentid= '" .$params['parentid'] ."'";
		$res = parent::execQuery($sql, $this->conn_iro); 
		$numRows = mysql_num_rows($res);
		if($numRows > 0){		 
			while($result = mysql_fetch_assoc($res))
			{
				$return_array[] = $result;	
				if(empty($return_array['0']['companyname']) && $params['identifier'] == 'pdata')	
				{
					if($this->mongo_flag == 1){
						$mongo_inputs = array();
						$mongo_inputs['parentid'] 	= $params['parentid'];
						$mongo_inputs['data_city'] 	= $this->data_city;
						$mongo_inputs['module']		= 'ME';
						$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
						$mongo_inputs['fields'] 	= "companyname,mobile";
						$rowIDC = $this->mongo_obj->getData($mongo_inputs);
					}
					else
					{
						$sqlIDC = "SELECT companyname,mobile FROM tbl_companymaster_generalinfo_shadow WHERE parentid= '" .$params['parentid'] ."'";
						$resIDC = parent::execQuery($sqlIDC, $this->conn_idc); 
						$rowIDC = mysql_fetch_assoc($resIDC);
						
					}
					$return_array['0']['companyname'] = $rowIDC['companyname'];
					$return_array['0']['mobile'] = $rowIDC['mobile'];
				}
			}			
		}
		$output['numRows'] 				=   $numRows; 
		$output['result'] 				=   $return_array;
		$output['error']['message'] 		=  "success";		
		if($params['trace'] == 1){
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}		
		return ($output);
	}
	public function get_geocode()
	{
		global $params;
		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get geocode from pincode/city\n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		 
		if(!empty($params['pincode']))
			$where_arr[] = "  pincode = '".$params['pincode']."'";
		if(!empty($params['city']) && $params['type'] == 'pincode')
			$where_arr[] = "  city = '".$params['city']."'";
		if(!empty($params['city']) && $params['type'] == 'city')
			$where_arr[] = "  cityname = '".$params['city']."'";	
		//if(!empty($params['data_city']))
			//$where_arr[] = "  data_city = '".$params['data_city']."'";	
		if(!empty($params['area']))
			$where_arr[] = "  areaname '".$params['area']."'";
		if(!empty($params['type']))
			$type = $params['type'];
			
		if(count($where_arr)>0)
			$where = " AND ".implode(" AND ",$where_arr);
		
		$limit = 0;
		if(!empty($params))
			$limit = $params['limit'];
		if($type == 'city')
		{
			$sql = "SELECT DISTINCT ct_name as city,latitude_city as latitude,longitude_city as longitude FROM tbl_city_master WHERE display_flag = 1 " .$where . " LIMIT 1";
		}	
		else if($type == 'pincode')	
		{	
			$sql = "SELECT DISTINCT pincode,latitude_pincode as latitude,longitude_pincode as longitude FROM tbl_areamaster_consolidated_v3 WHERE display_flag = 1 " .$where . " LIMIT 1  ";
		}
		
		$res = parent::execQuery($sql, $this->conn_idc); 
		$numRows = mysql_num_rows($res);
		if($numRows > 0){
			 
			$result = mysql_fetch_assoc($res);
		}		
		
		$output['numRows']  			=   $numRows;
		$output['result']  				=   $result;
		$output['error']['message'] 	=  "success";			 
		if($params['trace'] == 1){
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}	
		return ($output);
	}
	function get_source()
	{
		global $params;
		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get source \n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}		 
		if(!empty($params['limit']))
			$limit  = " LIMIT ".$params['limit'];	
		$sql = "SELECT DISTINCT scode,sname from d_jds.source WHERE sname like '%".$params['search']."%' ".$limit;
		$res = parent::execQuery($sql, $this->conn_iro); 
		$numRows = mysql_num_rows($res);
		if($numRows > 0){		 
			while($result = mysql_fetch_assoc($res))
			{
				$return_array[] = $result;	
				 
			}			
		}
		$output['numRows'] 				=   $numRows; 
		$output['result'] 				=   $return_array;
		$output['error']['message'] 		=  "success";		
		if($params['trace'] == 1){
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}		
		return ($output);
	}
	
	public function insertCompanySource()
	{
		global $params;
		
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get source \n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		
		$response_arr	=	array();
		$response_arr['errorCode'] = 1;
		
		$mandtory_field_arr	=	array('parentid','universal_source','datesource','data_city','ucode');
		$blank_field_flag = 0;
		foreach($mandtory_field_arr as $key => $val)
		{
			if(empty($params[$val]))
			{
				$blank_field_flag = 1;
				$response_arr['msg'] = $val." is blank";
				break;
			}
		}
		if($blank_field_flag == 1)
			return $response_arr;
		
		if(!DateTime::createFromFormat('Y-m-d H:i:s', $params['datesource']) !== false)	
		{
			$response_arr['msg'] = "datesource should be Y-m-d H:i:s format";
			return $response_arr;
		}
		
		$sql_get_sourcecode		=	"SELECT source_id FROM online_regis1.tbl_source_master WHERE source_name='".addslashes($params['universal_source'])."' LIMIT 1";
		$res_get_sourcecode		=	parent::execQuery($sql_get_sourcecode, $this->conn_idc);		 
		if(parent::numRows($res_get_sourcecode)>0)
		{
			$row_get_sourcecode = parent::fetchData($res_get_sourcecode);
			$sql_insert_source = "INSERT IGNORE INTO tbl_companysource_consolidated
										SET parentid 		= '".$params['parentid']."', 
										mainsource_name		= '".addslashes($params['universal_source'])."',
										mainsource_code 	= '".$row_get_sourcecode['source_id']."',
										subsource			= '".addslashes($params['subsource'])."',
										datesource			= '".$params['datesource']."',
										paid				= '".$params['paid']."',
										data_city			= '".addslashes($params['data_city'])."',
										updatedby			= '".$params['ucode']."',
										updater_name		= '".addslashes($params['uname'])."'";
			$res_insert_source		=	parent::execQuery($sql_insert_source, $this->conn);	
			if($res_insert_source)
			{
				$response_arr['errorCode'] = 0;
				$response_arr['msg'] =  "Company Source Details Inserted Successfully.";
			}
			else
				$response_arr['msg'] =  "Company Source Details Insertion Failed !";
			return $response_arr;
		}
		else
		{
			$response_arr['msg'] = "This Source is Not Found in Database.";
			return $response_arr;
		}
	}
	
	function badword_check()
	{
		global $params;		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To check badword \n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		if(!empty($params['parentid']))
			$parentid_cond .= " AND parentid != '".$params['parentid']."'";
		if(!empty($params['module']) && strtoupper($params['module'])=="DE")
			$module_cond .= " AND allow_module_flag != '1'";	
		
				
		$cmpname_bw	= $this->sanitize(trim(preg_replace('/(\([^)]*\))/','',urldecode(trim($params['companyname'])))));
		
		$return_array = array();
		$return_array['allow_flag'] = '0';
		$return_array['type'] 		= 'no badword';
		
		$all_cond	=	$parentid_cond.$module_cond;
		
		$sql = "SELECT * FROM online_regis1.CallerWord WHERE INSTR(' ".addslashes(trim(urldecode($params['companyname'])))." ', CONCAT(' ', word1, ' ') ) > 0 AND word1 != '' and act_flag=0 ".$all_cond."  ORDER BY category_word='Slang Word' DESC,category_word='Legal' DESC,category_word='Confine Word' DESC,category_word='Brand Restricted' DESC,category_word='Inapt' DESC LIMIT 1";
		$res = parent::execQuery($sql, $this->conn_idc); 
		$numRows = mysql_num_rows($res);
		if($numRows > 0)
		{
			$row= mysql_fetch_assoc($res);
			if(strtolower($row['category_word'])=="confine word" || strtolower($row['category_word'])=='slang word' || strtolower($row['category_word'])=='inapt' || strtolower($row['category_word'])=='legal' || strtolower($row['category_word'])=='brand restricted')
			{
				$return_array['type'] 				= $row['category_word'];				
				$return_array['badword_name'] 		= $row['Word1'];				
				if(strtolower($row['category_word'])=='confine word' || strtolower($row['category_word'])=='brand restricted')
					$return_array['allow_flag'] = '1';
				else if(strtolower($row['category_word'])=='slang word' || strtolower($row['category_word'])=='legal')
				{
					$return_array['allow_flag'] = '2';										
				}
				else if(strtolower($row['category_word'])=='inapt')
					$return_array['allow_flag'] = '1';						
			}
		}
		
		$city_arr				=	Array('MUMBAI','DELHI','KOLKATA','BANGALORE','CHENNAI','PUNE','HYDERABAD','AHMEDABAD');

		$main_city_array	=	array();
		$main_city_array['MUMBAI']['did_no']	=	'022-67283440';
		$main_city_array['MUMBAI']['email']		=	'mumbaidata@justdial.com';
		
		$main_city_array['DELHI']['did_no']		=	'0120-6658551';
		$main_city_array['DELHI']['email']		=	'delhidata@justdial.com';
		
		$main_city_array['KOLKATA']['did_no']	=	'033-66154599';
		$main_city_array['KOLKATA']['email']	=	'kolkatadata@justdial.com';
		
		$main_city_array['BANGALORE']['did_no']	=	'080-66377009';
		$main_city_array['BANGALORE']['email']	=	'bangaloredata@justdial.com';
		
		$main_city_array['CHENNAI']['did_no']	=	'044-66324002';
		$main_city_array['CHENNAI']['email']	=	'chennaidata@justdial.com';
		
		$main_city_array['PUNE']['did_no']		=	'020-66275060';
		$main_city_array['PUNE']['email']		=	'punedata@justdial.com';
		
		$main_city_array['HYDERABAD']['did_no']	=	'040-66048905';
		$main_city_array['HYDERABAD']['email']	=	'hyderabaddata@justdial.com';
		
		$main_city_array['AHMEDABAD']['did_no']	=	'079-66102030';
		$main_city_array['AHMEDABAD']['email']	=	'ahmedabaddata@justdial.com';
		
		$zone	=	'';
		if(!in_array(strtoupper($this->data_city),$city_arr))	
		{
			$sql_get_zone	=	"SELECT mapped_cityname as zone FROM d_jds.tbl_city_master WHERE data_city='".addslashes($this->data_city)."' LIMIT 1";
			$res_get_zone 	= parent::execQuery($sql_get_zone, $this->conn); 
			$numrows_zone = mysql_num_rows($res_get_zone);
			if($numrows_zone > 0)
			{
				$rows_get_zone= mysql_fetch_assoc($res_get_zone);
				$zone	=	$rows_get_zone['zone'];
			}
		}
		else
			$zone	=	$this->data_city;
			
		if($return_array['allow_flag'] == '2')
			$return_array['msg'] 				=   "The term '".$return_array['badword_name']."' in company name is tagged as ".$return_array['type']." & restricted for entry.\nPlease Contact Database Department for further detail.\n".$main_city_array[strtoupper($zone)]['email']." /Please call on ".$main_city_array[strtoupper($zone)]['did_no']."";
		elseif($return_array['allow_flag'] == '1')
			$return_array['msg'] 				=   "The term '".$return_array['badword_name']."' in company name is tagged as  ".$return_array['type'].".\nData will be masked and reviewed by DB team.";	
							  					  
		$output['numRows'] 				=   $numRows; 
		$output['result'] 				=   $return_array;
		$output['error']['message'] 	=   "success";		
		if($params['trace'] == 1){
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}		
		return ($output);
	}
	function get_generic_category()
	{
		global $params;		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get last disposition \n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}	
		//$sql = "SELECT national_catid,catid,category_name,service_name,brand_name,auth_flag,auth_gen_ncatid FROM d_jds.tbl_categorymaster_generalinfo WHERE catid IN (".trim($params['catids'],",").")";
		
		//$res = parent::execQuery($sql, $this->conn_iro); 
		$cat_params = array();
		$cat_params['page'] ='location_class';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'national_catid,catid,category_name,service_name,brand_name,auth_flag,auth_gen_ncatid';
		//$cat_params['catid']		= trim($params['catids'],",");

		$where_arr  	=	array();			
		$where_arr['catid']			=trim($params['catids'],",");		
		$cat_params['where']		= json_encode($where_arr);

		$cat_res_arr		= array();
		if($where_arr['catid']!=''){
			$cat_api_res_str	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		if($cat_api_res_str!=''){
			$cat_res_arr =	json_decode($cat_api_res_str,TRUE);
		}

		//$numRows = mysql_num_rows($res);
		$return_array = array();
		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results']) > 0)
		{
			$i=1;
			foreach($cat_res_arr['results'] as $key =>$cat_arr)
			{
				if($cat_arr['auth_flag'] == 1)	
				{
					$authorised_national_catids[] = $cat_arr['auth_gen_ncatid']; 					
				}
				if(!empty($cat_arr['brand_name']))	
				{
					$brand_category_arr[] =  $cat_arr['service_name'];
				}
				$category_arr[$i]['catid'] 			=	$cat_arr['catid'];
				$category_arr[$i]['category_name']	=	$cat_arr['category_name'];
				$i++;
			}
		}
		if(count($authorised_national_catids)>0)
		{
			//$sql_gen_cat = "SELECT national_catid,catid,category_name,service_name,brand_name,auth_flag,auth_gen_ncatid FROM d_jds.tbl_categorymaster_generalinfo WHERE national_catid IN (".implode(",",$authorised_national_catids).")";
			
			$cat_params = array();
			$cat_params['page'] ='location_class';
			$cat_params['data_city'] 	= $this->data_city;					
			$cat_params['return']		= 'catid,national_catid,category_name,service_name,brand_name,auth_flag,auth_gen_ncatid';

			$where_arr  	=	array();			
			$where_arr['national_catid'] = implode(",",$authorised_national_catids);		
			$cat_params['where']		 = json_encode($where_arr);

			if(count($authorised_national_catids)>0){
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
				$cat_res_arr = array();
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}
			}
			//$res_gen_cat = parent::execQuery($sql_gen_cat, $this->conn_iro); 
			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results']) > 0)
			{
				$i=1;
				foreach($cat_res_arr['results'] as $key =>$cat_arr)
				{
					$arr_generic_cat_auth[$i]['catid']			=	$cat_arr['catid'];
					$arr_generic_cat_auth[$i]['category_name']	=	$cat_arr['category_name'];
					$i++;
				}
			}
		}
		$final_cat_array = $arr_generic_cat_auth;
		
		$output['result'] 				=   $final_cat_array;
		$output['error']['message'] 	=   "success";		
		if($params['trace'] == 1){
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}		 	
		return ($output);
	}
	function get_budget()
	{		 
		global $params;
		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get budget \n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}		 
		
		$param_arr['parentid'] 		=	$params['parentid'];  
		$param_arr['competinfo'] 	=	'1';  
		$param_arr['area'] 			=	$params['area'];  
		$param_arr['catid'] 		=	$params['catid'];  
		$param_arr['catnm'] 		=	$params['catnm'];  
		$param_arr['city'] 			=	$params['data_city'];  
		//http://172.29.0.217:81/api/companydetails.php?parentid=PXX22.XX22.000063949321.J5S9&competinfo=1&area=Malad+West&catid=305&city=Mumbai&catnm=Car+Hire
		switch(strtolower($this->data_city))
		{	
			case 'mumbai' 		: $url = "http://".MUMBAI_CS_API;break;
			case 'delhi' 		: $url = "http://".DELHI_CS_API;break;
			case 'kolkata' 		: $url = "http://".KOLKATA_CS_API;break;
			case 'bangalore' 	: $url = "http://".BANGALORE_CS_API;break;
			case 'chennai' 		: $url = "http://".CHENNAI_CS_API;break;
			case 'pune' 		: $url = "http://".PUNE_CS_API;break;
			case 'hyderabad' 	: $url = "http://".HYDERABAD_CS_API;break;
			case 'ahmedabad' 	: $url = "http://".AHMEDABAD_CS_API;break;
			default 			: $url = "http://".REMOTE_CITIES_CS_API;break;					
		}
		$curl_url = $url . "/api/companydetails.php?".http_build_query($param_arr);			
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curl_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);		 
		curl_close($ch);
		
		$data_arr = json_decode($data,true); 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			print_R($data_arr['finance']['data']);
			echo "\n--------------------------------------------------------------------------------------\n";
		}	
		
		foreach($data_arr['finance']['data'] AS $key=>$val){ 
			if($val['expired'] == 0 && $val['balance']>0)
				$campaign_arr[] = $key;		
		}
	 	$pdg = 0;
		foreach($campaign_arr AS $k=>$v)
		{
			if($v==2){
				$pdg = 1;break;
			}
		} 
		if($pdg == 0){		
			$message = "Non PDG Contract";
			$non_pdg = 0;
		}
		else{
			$message = "PDG Contract";
			$non_pdg = 1;
		}	
		$sql_bur = "SELECT city,top_minbudget_package FROM d_jds.tbl_business_uploadrates WHERE city='".$params['city']."'";
		$res_bur = parent::execQuery($sql_bur, $this->conn_iro_slave);
		if($res_bur && mysql_num_rows($res_bur)>0)
		{
			$row_bur = mysql_fetch_assoc($res_bur);
		}
		$return_array['parentid']		=	$params['parentid'];
		$return_array['city']			=	$params['city'];
		$return_array['data_city']		=	$params['data_city'];
		$return_array['campaignid']		=	$data_arr['finance']['data']['1']['campaignid'];
		$return_array['budget']			=	$data_arr['finance']['data']['1']['budget'];
		$return_array['monthly_budget']	=	($data_arr['finance']['data']['1']['budget'])/12;
		$return_array['duration']		=	$data_arr['finance']['data']['1']['duration'];
		$return_array['version']		=	$data_arr['finance']['data']['1']['version'];
		$return_array['balance']		=	$data_arr['finance']['data']['1']['balance'];
		$return_array['multiplier']		=	$data_arr['finance']['data']['1']['multiplier'];
		$return_array['top_minbudget_package']		=	$row_bur['top_minbudget_package'];
		$return_array['payment']		=	$data_arr['payment'];
		$return_array['competinfo']		=	$data_arr['competinfo'];
	 
		$output['numRows'] 				=   $data_arr['finance']['data_avl']; 
		$output['result'] 				=   $return_array;
		$output['error']['message'] 	=   $message;		
		$output['error']['pdg_status'] 	=   $non_pdg;		
		
		if($params['trace'] == 1){
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}		
		return ($output);		
	}
	function top_five_listing()
	{
		global $params;
		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To top five listing \n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}	
		if(isset($params['listing_type']) && $params['listing_type'] == '2')	
		{	
			$companyname_search_or	= $this->sanitize(trim(preg_replace('/(\([^)]*\))/','',$params['companyname'])));
			$companyname_search = implode('+',explode(' ',$companyname_search_or));
			$word_count = str_word_count($companyname_search_or);
			if($word_count<=3)
			{	
				$sql = "SELECT *,(score/new_count)*100 AS percent_match FROM
                    (SELECT *,LENGTH(companyname_search)-LENGTH(REPLACE(companyname_search,' ',''))+1 AS word_count,LENGTH('".$companyname_search_or."')-LENGTH(REPLACE('".$companyname_search_or."',' ',''))+1  as new_count,
                    MATCH(companyname_search) AGAINST('".$companyname_search."' IN BOOLEAN MODE) AS score
                    FROM db_iro.tbl_top_five_percent_consolidated
                    WHERE MATCH companyname_search AGAINST('+".$companyname_search."' IN BOOLEAN MODE)
                    ORDER BY score)a WHERE  (score/new_count)*100>=100 AND word_count=new_count ORDER BY percent_match DESC";
			}
			else
			{
		 		$sql = "SELECT *,(score/new_count)*100 AS percent_match FROM
                    (SELECT *,LENGTH(companyname_search)-LENGTH(REPLACE(companyname_search,' ',''))+1 AS word_count,LENGTH('".$companyname_search_or."')-LENGTH(REPLACE('".$companyname_search_or."',' ',''))+1  as new_count,
                    MATCH(companyname_search) AGAINST('".$companyname_search."' IN BOOLEAN MODE) AS score
                    FROM db_iro.tbl_top_five_percent_consolidated
                    WHERE MATCH companyname_search AGAINST('+".$companyname_search."'  IN BOOLEAN MODE)
                    ORDER BY score)a WHERE  (score/new_count)*100>=80   ORDER BY percent_match desc ";	
			}	
		}	
		else
		{
			$source_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');	
			if($source_city == 'remote')
				$slab = '2';
			else	
				$slab = '10';
			$sql = "SELECT * from db_iro.tbl_top_five_percent_consolidated WHERE parentid = '".$params['parentid']."' and slab<= ".$slab;
		}		
		$res = parent::execQuery($sql, $this->conn_iro_slave); 
		$numRows = mysql_num_rows($res);
		$return_array['top_five_listing'] = '0';
		if($numRows > 0){		 
			$return_array['top_five_listing'] = '1';			
		}	
		
	
		if($numRows==0)
		{
			$sql_debug = str_replace("and (score/new_count)*100>=100 and slab<=2 and word_count=new_count order by percent_match desc"," ",$sql);
			$sql_debug = str_replace("AND (score/new_count)*100>=80 and slab<=2 order by percent_match desc"," ",$sql_debug);
			$res_debug = parent::execQuery($sql_debug, $this->conn_iro_slave);			
			$numRows = mysql_num_rows($res_debug);
			$res = $res_debug;
		}
		if($numRows>0)
		{
			$display_str = '<table border="1"  style="font:12px verdana;" width=100%>';
			$display_str .= "<tr align=center><td>";
			$display_str .= "<td>Parentid<td>";
			$display_str .= "<td>Companyname<td>";
			$display_str .= "<td>Companyname Search <td>";
			$display_str .= "<td>slab<td>";
			$display_str .= "<td>Data Source<td>";
			$display_str .= "<td>Word Count<td>";
			$display_str .= "<td>New Count<td>";
			$display_str .= "<td>Score<td>";
			$display_str .= "<td>Percent Match<td><tr>";
			$x=1;
			while($row_debug = mysql_fetch_assoc($res))
			{
				$display_str .= "<tr align=center><td>";
				$display_str .= "<td>".$row_debug['parentid']."<td>";
				$display_str .= "<td>".$row_debug['companyname']."<td>";
				$display_str .= "<td>".$row_debug['companyname_search']."<td>";
				$display_str .= "<td>".$row_debug['slab']."<td>";
				$display_str .= "<td>".$row_debug['data_src']."<td>";
				$display_str .= "<td>".$row_debug['word_count']."<td>";
				$display_str .= "<td>".$row_debug['new_count']."<td>";
				$display_str .= "<td>".$row_debug['score']."<td>";
				$display_str .= "<td>".$row_debug['percent_match']."<td><tr>";

				$match_arr[$x]['parentid']		=	$row_debug['parentid'];	
				$match_arr[$x]['companyname']	=	$row_debug['companyname'];	
				$match_arr[$x]['slab']			=	$row_debug['slab'];	
				$match_arr[$x]['percent_match']	=	$row_debug['percent_match'];	
				$match_arr[$x]['data_src']		=	$row_debug['data_src'];	
				$x++;
			}
			$display_str .= '</table>'; 
		}
			
		if($params['trace'] == 1)
		{
			echo "<hr>word_count : ".$word_count = str_word_count($companyname_search_or);
			echo "<hr><br>".$sql;
			echo "<br>";			
			echo "<hr>Output ";			
			echo "<hr>";
			
			echo $display_str;
			echo "<hr>";			
			echo "<br><br>";print_r($match_arr);
			echo "<br><br>";print_r($output);
			echo "<hr>";
		}
		$output['numRows'] 				=   $numRows; 
		$output['result'] 				=   $return_array;
		$output['result']['match_data'] =   $match_arr;
		$output['error']['message'] 	=  "success";	
		 		
		return ($output);
	}
	function get_disposition()
	{
		global $params;		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get last disposition \n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}	
		$sql = "SELECT contractcode as parentid,allocationType as disposition,allocationTime as disposition_time FROM d_jds.tblContractAllocation WHERE contractcode = '".$params['parentid']."' ORDER BY allocationTime DESC LIMIT 1";
		$res = parent::execQuery($sql, $this->conn_iro); 
		$numRows = mysql_num_rows($res);
		$return_array = array();
		if($numRows > 0)
		{
			$row= mysql_fetch_assoc($res);
			$sql_ds = "SELECT disposition_name FROM d_jds.tbl_disposition_info WHERE disposition_value='".$row['disposition']."'";
			$res_ds = parent::execQuery($sql_ds, $this->conn_iro); 
			$row_ds = mysql_fetch_assoc($res_ds);
			$row['disposition_name'] = $row_ds['disposition_name'];
			$return_array['parentid'] 			=	$params['parentid'];
			$return_array['disposition'] 		=	$row['disposition'];
			$return_array['disposition_name']	=	$row_ds['disposition_name'];
			$return_array['disposition_time']	=	$row['disposition_time'];  
		}
		else
		{
			$return_array['parentid'] 			=	$params['parentid'];
			$return_array['disposition'] 		=	"";
			$return_array['disposition_name']	=	"";
			$return_array['disposition_time']	=	"";            
		}	
		$output['numRows'] 				=   $numRows; 
		$output['result'] 				=   $return_array;
		$output['error']['message'] 	=   "success";		
		if($params['trace'] == 1){
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}		
		return ($output);
	}
	function check_virtual_number()
	{
		global $params;		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To check virtual number \n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		$phone_numbers = explode(",",$params['phone']);
		foreach($phone_numbers as $key=>$number)
		{
			if(intval($number)>0)
			{
				$sql = "SELECT * FROM d_jds.tbl_virtual_number_range WHERE city = '". $params['data_city']."'  AND ". $number ." BETWEEN start_number and end_number"; 	
				$res = parent::execQuery($sql, $this->conn_iro); 
				$numRows  = mysql_num_rows($res);
				$ret_array[$number] = 0;	
				if($numRows >0)
				{
					$ret_array[$number] = 1;	
				}
			}
		}
		$output['result']['virtual_number'] =   $ret_array;
		$output['error']['message'] 		=   "success";		
		if($params['trace'] == 1){
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}		 	
		return ($output);
	}
	function map_pointer_flag()
	{
		global $params;		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get map pointer flag \n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		$ret_array['parentid']	= $params['parentid'];
		$ret_array['latitude']	= $params['latitude'];
		$ret_array['longitude']	= $params['longitude'];
		$ret_array['geocode_accuracy_level']	= $params['geocode_accuracy_level'];		
		
		$sql = "SELECT a.parentid,b.map_pointer_flags,b.flags,a.geocode_accuracy_level FROM db_iro.tbl_companymaster_generalinfo a JOIN db_iro.tbl_companymaster_extradetails  b on a.parentid=b.parentid WHERE a.parentid='".$params['parentid']."'"; 
		$res = parent::execQuery($sql, $this->conn_iro); 
		$numRows  = mysql_num_rows($res);
		if($numRows >0)
		{
			$row =  mysql_fetch_assoc($res);
			$map_pointer_flags_old = $row['map_pointer_flags'];
			$flags_old 			   = $row['flags'];
		}
			
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
		if($params['geocode_accuracy_level'] == 1  || $params['geocode_accuracy_level'] == 2 )
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
		}
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
		}
		$ret_array['map_pointer_flags']		= $map_pointer_flags;
		$ret_array['flags'] 				= $flags_value;
					
		$output['result'] =   $ret_array;
		$output['error']['message'] 		=   "success";		
		if($params['trace'] == 1){
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}		 	
		return $output;
	}
	function check_doctor_contract()
	{
		global $params;		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get Doctor companyname \n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		$cmpname	=	$params['companyname'];	
		
		if(stripos(strtolower($cmpname),'dr ') == '0')
		{
			$cat_arr	=	explode(",",trim(trim($params['catid']," "),","));
			$cat_str = implode("','",$cat_arr);
			$dr_cat 		= 0 ;	
			if(!empty($cat_str))
			{	
				$sql =	"SELECT parentLineage FROM d_jds.tbl_categorymaster_parentinfo WHERE catid IN ('".$cat_str."') AND (parentLineage LIKE '%/Doctors & Specialists (P)/%' OR parentLineage LIKE '%/B2cs doctors/%')";
				$res = parent::execQuery($sql, $this->conn_iro); 
				if($res && mysql_num_rows($res) > 0)
				{
					$cmpname = preg_replace('/Dr /i','Dr. ',$cmpname,1);
					$dr_cat  =  1;
				}
			}
		}
		$output['result']['dr_contract_flag'] 	=   $dr_cat;
		$output['result']['companyname_old'] 	=   $params['companyname'];
		$output['result']['companyname_new'] 	=   $cmpname;		
		$output['error']['message'] 		=   "success";		
		if($params['trace'] == 1){
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}	
		return $output;
	}
	
	function dump_rework_data()
	{
		global $params;		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : dump_disposition_rework_data \n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		if(isset($params['module_type']) && strtolower($params['module_type']) == 'notification_reports')
		{
			if(empty($params['parentid']) || empty($params['data_city']))
			{
				$output['error_code']		=	'1';	
				$output['error']			=	'Parentid ,Module Type,Data City can not be empty' ;	
				$output['success_flag']		=	'0';
				
				return $output;
			}
			else
			{
				$sql_check_exist = "SELECT * FROM d_jds.tbl_single_bform_audit_data WHERE parentid='".$params['parentid']."' AND module_type		=	'NOTIFICATION_REPORTS' AND DATE(entered_date)=DATE('".$params['entered_date']."') AND status_flag=0";
				$res_check_exist = parent::execQuery($sql_check_exist, $this->conn_iro); 	
				if(mysql_num_rows($res_check_exist)==0)
				{
					$sql_dump_data = "INSERT INTO d_jds.tbl_single_bform_audit_data SET
						parentid		=	'".$params['parentid']."',
						module_type		=	'NOTIFICATION_REPORTS',
						data_city		=	'".$params['data_city']."',
						entered_date	=	'".$params['entered_date']."'";
					$res_dump_data = parent::execQuery($sql_dump_data, $this->conn_iro); 	
				}
				else
				{
					$output['error_code']		=	'1';	
					$output['error']			=	'Data Already pushed' ;	
					$output['success_flag']		=	'0';
					
					return $output;				
				}	
			}			
		}
		else
		{
			if(empty($params['parentid']) || empty($params['module_type']) || empty($params['source']) || empty($params['disposition']) || empty($params['data_city']))
			{
				$output['error_code']		=	'1';	
				$output['error']			=	'Parentid ,Module Type,Source,Disposition and Data City can not be  empty' ;	
				$output['success_flag']		=	'0';
				
				return $output;
			}
			
			$sql_dump_data	=	"INSERT INTO d_jds.tbl_dialer_rework_data SET 
									parentid				= '".$params['parentid']."',
									module_type				= '".$params['module_type']."',
									source					= '".$params['source']."',
									sourceid				= '".$params['ucode']."',
									data_city				= '".$params['data_city']."',
									entered_date			= NOW(),
									dialer_disposition		= '".$params['disposition']."'";
			$res_dump_data = parent::execQuery($sql_dump_data, $this->conn_iro); 
	
		}
		if($res_dump_data)
		{
			$output['error_code']		=	'0';	
			$output['success_flag']		=	'1';
		}
		else
		{
			$output['error_code']		=	'1';
			$output['success_flag']		=	'0';
		}
		return $output;
	}
	function freelisting_source_count()
	{
		global $params,$conn_iro;		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get freelisting source phone number wise wise data count \n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		global $params;
		$arr_numbers = explode(",",$params['phone']);
		$url 	=	$this->get_jdbox_url()."duplicate_check.php";
		$param_curl = Array();
		$param_curl['data_city']	=	$params['data_city'];
		$param_curl['module']		=	'jdbox';
		$param_curl['phone']		=	$params['phone'];
		$param_curl['companyname']	=	'xxxxxx';
		
		$data =  json_decode($this->get_curl_data($url,$param_curl),true);
		foreach($data['phone_search'] AS $key=>$val)
		{
			if(strtolower($val['data_city']) == strtolower($params['data_city']) && $val['display_flag'] == 1)
			{
				$parentid_arr[] = $val['parentid'];
				$parentid_phone_search[$val['parentid']] = $val;
			}
		}
		$count = Array();
		$input_phone_arr = Array();
		$input_phone_arr = explode(",",$params['phone']);		
		foreach($input_phone_arr as $key=>$number)
		{
			$count[$number] = 0; 								
		}
		foreach($parentid_phone_search AS $parentid=>$data_arr )
		{
			$phone = trim($parentid_phone_search[$parentid]['phone']);
			$live_phone_arr = Array();
			$live_phone_arr	 = explode(",",$phone);
			
			foreach($input_phone_arr as $key=>$number)
			{
				if(in_array($number,$live_phone_arr))
				{
					$count[$number]++; 
				}					
			}		
		}
		/*
		if(count($parentid_arr)>0)
		{
			$parentid_list	=	implode("','" ,$parentid_arr);
			$sql = "SELECT DISTINCT parentid,mainsource,subsource FROM d_jds.tbl_company_source WHERE parentid IN ('".$parentid_list."') AND subsource IN ('joinfree','Ad program')   GROUP BY parentid ORDER BY datesource";
			$res = parent::execQuery($sql, $this->conn_iro); 
			if($res && mysql_num_rows($res)>0)
			{
				while($row = mysql_fetch_assoc($res))
				{
					$phone = trim($parentid_phone_search[$row['parentid']]['phone']);
					$live_phone_arr = Array();
					$live_phone_arr	 = explode(",",$phone);
					
					foreach($input_phone_arr as $key=>$number)
					{
						if(in_array($number,$live_phone_arr))
						{
							$count[$number]++; 
						}					
					}	
				}
			}
		}*/
		$output['numRows'] 				=   count($count);
		$output['result'] 				=   $count;
		$output['error']['message'] 	=   "success";		
		if($params['trace'] == 1){
			echo "<hr><br>";print_r($output);
			echo "<hr>";
		}		
		return ($output);		
	}
	function check_repeated_words()
	{
		global $params;		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To check repeated words \n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		
		$sent_universal_moderation_flag =0;
		$send_to_moderation_rw = 0;
		$send_to_moderation_comp = 0;
		$send_to_moderation_cmp_prefix_word = 0;
		$send_to_moderation_cmp_single_word_compnay = 0;
		$send_to_moderation_cmp_homekey_character = 0;
		$send_to_moderation_within_bracket_word = 0;
		
	 	$compname 		= urldecode(trim($params['str']));
	 	$user_data_arr	= json_decode($params['user_data'],true);
		
		if(strtolower($params['field']) == 'companyname')
		{
			$arr_str = explode(" ",$compname);
			$arr_str = array_filter($arr_str);
			if(count($arr_str)>2)
			{
				foreach($arr_str as $key=>$str)
				{
					$cnt = 0;
					foreach($arr_str as $kk=>$str_data)
					{
						if($str == $str_data)
						{
							$cnt++; 
						}	
						if($cnt>2)
						{
							$send_to_moderation_rw = 1;
							break;
						}						
					}
				}
			}			
		}		
		if($send_to_moderation_rw == 1)
		{
			$select = "SELECT * FROM db_data_correction.tbl_reapeated_words_company WHERE companyname='".addslashes($compname)."' AND parentid='".$params['parentid']."' AND data_city='".addslashes($params['data_city'])."' and type='repeated_words'";
			$res_select = parent::execQuery($select, $this->conn_data_correction); 
			if($res_select && mysql_num_rows($res_select)>0)
			{
				$send_to_moderation_rw = 0; 
			}
		}
		if($send_to_moderation_rw == 1)
		{	
			$compData['parentid']		=	$params['parentid'];
			$compData['ucode']			=	$params['ucode'];
			$compData['data_city']		=	$params['data_city'];
			$compData['priority_field']	=	'REPEATED_WORDS';
			
			
			$insert = "INSERT INTO db_data_correction.tbl_reapeated_words_company 
						SET 
						companyname='".addslashes($compname)."',
						parentid='".$params['parentid']."' ,
						data_city='".addslashes($params['data_city'])."',
						type='repeated_words',
						entry_date=now()";
			$res_insert = parent::execQuery($insert, $this->conn_data_correction); 
			if($res_insert)
			{
				$ret = $this->data_correction_api($compData,'UNIVERSAL_RULE',$user_data_arr);		
				$sent_universal_moderation_flag = 1;
			}
		}
		if($sent_universal_moderation_flag == 0 && strlen($compname)>70)
		{
			$send_to_moderation_comp = 1;
		}
		if($send_to_moderation_comp == 1)
		{
			$select = "SELECT * FROM db_data_correction.tbl_reapeated_words_company WHERE companyname='".addslashes($compname)."' AND parentid='".$params['parentid']."' AND data_city='".addslashes($params['data_city'])."' and type='companyname length > 70'";
			$res_select = parent::execQuery($select, $this->conn_data_correction); 
			if($res_select && mysql_num_rows($res_select)>0)
			{
				$send_to_moderation_comp = 0; 
			}
		}
		if($send_to_moderation_comp == 1)
		{	
			$compData['parentid']		=	$params['parentid'];
			$compData['ucode']			=	$params['ucode'];
			$compData['data_city']		=	$params['data_city'];
			$compData['priority_field']	=	'COMPANY_LENGTH';
			
			$insert = "INSERT INTO db_data_correction.tbl_reapeated_words_company 
					SET 
						companyname='".addslashes($compname)."',
						parentid='".$params['parentid']."' ,
						data_city='".$params['data_city']."',
						type='companyname length > 70',
						entry_date=now()";
			$res_insert = parent::execQuery($insert, $this->conn_data_correction); 
			if($res_insert)
			{
				$ret = $this->data_correction_api($compData,'UNIVERSAL_RULE',$user_data_arr);		
				$sent_universal_moderation_flag = 1;
			}
		}
		
		//check companyname prefix restrict word 
		if($sent_universal_moderation_flag == 0)
		{
			$compname_arr	=	array_values(array_filter(explode(' ',trim($compname))));
			
			$singular_first_word	=	$this->getSingular(trim($compname_arr[0]));
			$singular_compname	=	$this->getSingular(trim($compname));
			
			
			$all_word_permutation = " ".addslashes($compname)." ".addslashes($singular_compname)." ";
			
			/*$sql_check_prefix	=	"SELECT COUNT(1) AS cnt FROM online_regis1.tbl_cmp_prefix_restrict_word WHERE  word IN ('".addslashes($compname_arr[0])."','".addslashes($compname)."','".addslashes($singular_first_word)."','".addslashes($singular_compname)."') AND word_type='prefix' AND active_flag=1";
			$res_check_prefix 	= 	parent::execQuery($sql_check_prefix, $this->conn_idc); 
			$rows_check_prefix 	=   parent::fetchData($res_check_prefix); */
			
			$sql_check_prefix	=	"SELECT *FROM online_regis1.tbl_cmp_prefix_restrict_word  WHERE INSTR(' ".$all_word_permutation." ', CONCAT(' ', word, ' ') ) > 0 AND word != '' AND active_flag=1 ";
			$res_check_prefix 	= 	parent::execQuery($sql_check_prefix, $this->conn_idc); 
			
			$cmp_prefix_flag	=	0;
			while($rows_check_prefix 	=   parent::fetchData($res_check_prefix))
			{
				if(stripos($singular_compname,$rows_check_prefix['word']) ===0 || stripos($compname,$rows_check_prefix['word']) ===0)
				{
					$cmp_prefix_flag = 1;
					break;
				}	
			}
			if($cmp_prefix_flag==1)
			{
	
				$compData['parentid']		=	$params['parentid'];
				$compData['ucode']			=	$params['ucode'];
				$compData['data_city']		=	$params['data_city'];
				$compData['priority_field']	=	'COMPANY_PREFIX';	
				
				$send_to_moderation_cmp_prefix_word	=	1;
				$ret = $this->data_correction_api($compData,'UNIVERSAL_RULE',$user_data_arr);		
				$sent_universal_moderation_flag = 1;
			}
			
		}
		//check companyname single word 
		if($sent_universal_moderation_flag == 0)
		{
			$compname_arr	=	array_values(array_filter(explode(' ',trim($compname))));
			if(count($compname_arr) == 1)
			{
				$ret = $this->checkBrandname($compname_arr['0']);
				$compdata	=	$this->company_data($params['parentid']);
				
				$sql_check_callcnt	=	" SELECT a.companyname,a.company_callcnt FROM db_iro.tbl_companymaster_generalinfo a JOIN  db_iro.tbl_companymaster_extradetails b on a.parentid=b.parentid WHERE a.companyname='".addslashes($compname)."' and b.freeze=0 and b.mask=0 and company_callcnt>50 ORDER BY company_callcnt DESC  LIMIT 1";
				$res_check_callcnt = parent::execQuery($sql_check_callcnt, $this->conn_iro); 
				$numsrows_check_callcnt = parent::numRows($res_check_callcnt);
				
				if($ret != 'Companyname is matching with brandname' && $numsrows_check_callcnt == 0 /*&& $compdata['mask'] == 0 && $compdata['freeze'] == 0*/)
				{	
					$compData['parentid']		=	$params['parentid'];
					$compData['ucode']			=	$params['ucode'];
					$compData['data_city']		=	$params['data_city'];
					$compData['priority_field']	=	'SINGLE_WORD_COMPANY';	
					
					$send_to_moderation_cmp_single_word_compnay	=	1;
					$ret = $this->data_correction_api($compData,'UNIVERSAL_RULE',$user_data_arr);		
					$sent_universal_moderation_flag = 1;
				}
			}
		}
	
		//check home key character in cmpname 
		if($sent_universal_moderation_flag == 0)
		{
			if(!empty($compname))
			{
				$sql_get_homekey	=	"SELECT word FROM online_regis1.tbl_cmp_prefix_restrict_word WHERE  word_type='home_key' AND active_flag=1";
				$res_get_homekey = parent::execQuery($sql_get_homekey, $this->conn_idc); 
				
				$homekey_character_arr	=	array();
				if(parent::numRows($res_get_homekey)>0)
				{
					while($rows_get_homekey = parent::fetchData($res_get_homekey))
					{
						$homekey_character_arr[]	=	$rows_get_homekey['word'];
					}
				}
				
				//$homekey_character_arr	=	array('asd','asdfasdfasdf','asdf','asdfg','asdfghjkl','asfg','asfghj','ghhj','ghh','ghhhd','ghhghj','afggs','fgfh','fjh','fg');
				
				foreach($homekey_character_arr as $key_homekey =>$val_homekey)
				{
					if(stripos($compname, $val_homekey) !== false)
					{
						$send_to_moderation_cmp_homekey_character	=	1;
	
						$compData['parentid']		=	$params['parentid'];
						$compData['ucode']			=	$params['ucode'];
						$compData['data_city']		=	$params['data_city'];
						$compData['priority_field']	=	'HOMEKEY_CHARACTER';	
						
						$ret = $this->data_correction_api($compData,'UNIVERSAL_RULE',$user_data_arr);		
						$sent_universal_moderation_flag = 1;
						
						break;
					}
				}
			}
			
		}
		
		//check within bracket word 
		if($sent_universal_moderation_flag == 0)
		{
			//capturing within bracket words
			preg_match('#\((.*?)\)#', $compname, $match);
			
			//replacing multiple spaces by single space
			$within_bracket_word = preg_replace('!\s+!', ' ', trim($match[1]));
			if(!empty($compname) && !empty($within_bracket_word))
			{
				$allow_word_arr	=	array('head office','regional office','corporate office','admin office','customer care','registered office','workshop','warehouse','factory outlet','retail store','retail office','branch office','sales office','airport office','phone banking','closed down','showroom','booking office','under renovation','opening shortly','temporary closed down','not in business','reservation office','india','pt');
				
				
				if(!in_array(strtolower($within_bracket_word),$allow_word_arr))
				{
					$send_to_moderation_within_bracket_word	=	1;

					$compData['parentid']		=	$params['parentid'];
					$compData['ucode']			=	$params['ucode'];
					$compData['data_city']		=	$params['data_city'];
					$compData['priority_field']	=	'WITHIN_BRACKET';	
					
					$ret = $this->data_correction_api($compData,'UNIVERSAL_RULE',$user_data_arr);		
					$sent_universal_moderation_flag = 1;	
				}
			}
			
		}
		
		$output_arr['send_to_moderation_repeated_words'] 			=   $send_to_moderation_rw;
		$output_arr['send_to_moderation_companyname'] 	 			=   $send_to_moderation_comp;
		$output_arr['send_to_moderation_cmp_prefix_word'] 			=   $send_to_moderation_cmp_prefix_word;
		$output_arr['send_to_moderation_cmp_single_word_compnay'] 	=   $send_to_moderation_cmp_single_word_compnay;
		$output_arr['send_to_moderation_cmp_homekey_character'] 	=   $send_to_moderation_cmp_homekey_character;
		$output_arr['send_to_moderation_within_bracket_word'] 	=   $send_to_moderation_within_bracket_word;
		
		$output['numRows'] 				=   count($count);
		$output['result']				= 	$output_arr;
		$output['error']['message'] 	=   "success";		
		if($params['trace'] == 1){
			echo "<hr><br>";print_r($output);
			echo "<hr>";
		}
		return ($output);
	}
	function checkBrandname($companyname)
	{
		$err_msg 	= '';
		if(!empty($companyname))
		{
			$business_name 	= trim($companyname);
			
			$companystr = strtolower($business_name); 
			$companystr = preg_replace("/[^A-Za-z0-9\s]/", " ", $companystr);
			
			$sql_brand	= "SELECT GROUP_CONCAT(brand_name separator '|~|') as brand_name, GROUP_CONCAT(source separator '|~|') as source FROM tbl_brand_names WHERE MATCH(brand_name) AGAINST('".$companystr."' IN BOOLEAN MODE) LIMIT 1";			
			$res_brand = parent::execQuery($sql_brand, $this->conn_iro); 
			$num_rows	= mysql_num_rows($res_brand);
			if($res_brand && $num_rows > 0)
			{
				$row = mysql_fetch_assoc($res_brand);
				$brand_name = trim($row['brand_name']);
				$brand_name = strtolower($brand_name);
				$source 	= trim($row['source']);
				$brand_name_arr = explode("|~|",$brand_name);
				$source_arr = explode("|~|",$source);
				$matched_brand = '';
				$matched_source = ''; 
				if(count($brand_name_arr)>0){
					foreach($brand_name_arr as $key => $value){
						if(strpos($companystr, $value) !== false) {
							$matched_brand = $value;
							$matched_source = $source_arr[$key];
							break;
						}
					}
				}
				if($matched_brand){
					$err_msg = 'Companyname is matching with brandname';
					$arr_errors['error'][] = $err_msg;
				}
			}
		}
		return $err_msg;
	}

	function check_jd_employee_number()
	{
		global $params;
		$response_arr	=	array();
		if(empty($params['mobile']))
		{
			$response_arr['errorCode']	=	'1';
			$response_arr['msg']	=	"Mobile number can not be blank";
			return $response_arr;
		}	
		
		$mobile_arr	=	array_unique(array_filter(explode(',',trim($params['mobile']))));		
		if(is_array($mobile_arr) && count($mobile_arr)>0)
		{
			$records_arr	=	array();
			$i=0;
			foreach($mobile_arr as $key_mobile	=> $val_mobile)
			{
				$mobile_sso_url	=	"http://".SSO_IP."/hrmodule/employee/fetchMobileEmployee/".$val_mobile;
				
				$ch 		= curl_init();
				curl_setopt($ch, CURLOPT_URL, $mobile_sso_url);
				curl_setopt($ch, CURLOPT_POST      ,1);
				curl_setopt($ch, CURLOPT_POSTFIELDS ,$param);
				curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				$resmsg = curl_exec($ch);
				
				$res_mobiel_response	=	json_decode($resmsg,true);
				if(!empty($res_mobiel_response['data']['empcode'])) 
				{
					$records_arr[$i]['empcode']	= $res_mobiel_response['data']['empcode'];
					$records_arr[$i]['mobile']	= $val_mobile;
					$i++;
				}
			}
			$response_arr['errorCode']	=	'0';
		}
		$response_arr['data']				=	$records_arr;
		$response_arr['numRows']			=	count($records_arr);
		return $response_arr;
	}

	function check_jd_employee()
	{
		global $params;
		
		$usercode	=	$params['usercode'];
		$response_arr	=	array();
		if(empty($usercode))
		{
			$response_arr['errorCode']	=	'1';
			$response_arr['msg']	=	"usercode can not be blank";
			return $response_arr;
		}	
	
		if(!empty($usercode))
		{
				$records_arr	=	array();
				$mobile_sso_url	=	"http://".SSO_IP."/hrmodule/employee/fetch_employee_info/".$usercode;
				
				$ch 		= curl_init();
				curl_setopt($ch, CURLOPT_URL, $mobile_sso_url);
				curl_setopt($ch, CURLOPT_POST      ,1);
				curl_setopt($ch, CURLOPT_POSTFIELDS ,$param);
				curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				$resmsg = curl_exec($ch);
				
				$res_mobiel_response	=	json_decode($resmsg,true);
				$i=0;
				if(!empty($res_mobiel_response['data']['empcode'])) 
				{
					$records_arr[$i]['empcode']	= $res_mobiel_response['data']['empcode'];
					$i++;
				}
			$response_arr['errorCode']	=	'0';
		}
		$response_arr['data']				=	$records_arr;
		$response_arr['numRows']			=	count($records_arr);
		return $response_arr;
	}

	function data_correction_api($compData,$modtype,$user_data_arr=array())
	{
		$param_dc = Array();
		$param_dc['parentid']		=	$compData['parentid'];
		$param_dc['mod_type']		=	$modtype;
		$param_dc['userid']			=	$compData['ucode'];
		$param_dc['edited_date']	=	date('Y-m-d H:i:s');
		$param_dc['data_city']		=	$compData['data_city'];
		
		$compData['jdbox_flag']				=	"1";
		if(!empty($compData['source']))
			$param_dc['source']			=	$compData['source'];
		
		if(strtoupper($modtype) == 'OVERWRITTEN_DATA')
			$param_dc['user_data']		=	json_encode($compData);
		elseif(strtoupper($modtype) == 'UNIVERSAL_RULE' )	
		{
			$user_data_arr['priority_field']	=	$compData['priority_field'];		
			$user_data_arr['jdbox_flag']		=	1;		
			$param_dc['user_data']	=	json_encode($user_data_arr);
		}
		
		switch(strtoupper($compData['data_city']))
		{
			case 'MUMBAI' 		:	$url = "http://172.29.0.237:97/";	break;
			case 'DELHI' 	 	:	$url = "http://172.29.8.237:97/";	break;
			case 'KOLKATA' 		:	$url = "http://172.29.16.237:97/";	break;
			case 'BANGALORE' 	:	$url = "http://172.29.26.237:97/";	break;
			case 'CHENNAI' 		:	$url = "http://172.29.32.237:97/";	break;		
			case 'PUNE' 		:	$url = "http://172.29.40.237:97/";	break;
			case 'HYDERABAD' 	:	$url = "http://172.29.50.237:97/";	break;		
			case 'AHMEDABAD' 	:	$url = "http://192.168.35.237:97/";	break;			
			default: 				$url = "http://192.168.17.237:197/";	break;
		}
		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']))
		{				
			$url = "http://nareshbhati.jdsoftware.com/tmegenio/";
		}				
		$curl_url = $url ."api_dc/datacorrection_api.php";		
	
		$ch 		= curl_init();
		curl_setopt($ch, CURLOPT_URL, $curl_url);
		curl_setopt($ch, CURLOPT_POST      ,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS ,$param_dc);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$resmsg = curl_exec($ch);		
		curl_close($ch);
		return $resmsg;
	}
	
	
	function get_misc_flag()
	{
		global $params;		 
		$sql_get_misc_flg	=	"SELECT IF(misc_flag&2=2,'2',IF(misc_flag&4=4,'4',IF(misc_flag&8=8,'8',''))) AS misc_flag_bit,misc_flag,BIN(misc_flag) AS bin_misc_flag FROM tbl_companymaster_extradetails WHERE parentid='".$params['parentid']."'";
		$res_get_misc_flg = parent::execQuery($sql_get_misc_flg, $this->conn_iro); 
		if($res_get_misc_flg && mysql_num_rows($res_get_misc_flg)>0)
		{
			$rows_get_misc_flg = mysql_fetch_assoc($res_get_misc_flg);
		}
		
		if($params['trace'] == 1){
			echo "<br>".$sql_get_misc_flg;
			echo "<br><br>";print_r($rows_get_misc_flg);
		}	
		$misc_flg_diff 	=	$params['shadow_misc_flag']-$rows_get_misc_flg['misc_flag_bit'];
		$misc_flg_new		=	$rows_get_misc_flg['misc_flag']+	$misc_flg_diff;
		
		$response_arr	=	array();
		$response_arr['misc_flag']	=	$misc_flg_new;
		$response_arr['misc_flag_live']	=	$rows_get_misc_flg['misc_flag'];
		
		return $response_arr;
	}
	
	function company_autosuggest(){
		global $params;	
		
		if(!empty($params['limit']))
			$limit_cond = " LIMIT 0,".$params['limit']."";
		else
			$limit_cond	=	" LIMIT 0,10";	
		
		$search_term_arr	=	explode('(',trim($params['searchterm']));
		if(count($search_term_arr)>1)
		{
			$text_arr 			= array_reverse(explode('(',trim($params['searchterm'])));
			$text_cmp_type		=	str_replace(')','',$text_arr[0]);
			unset($text_arr[0]);
			$valid_search_term =implode('(',array_reverse($text_arr));			
		}
		else
			$valid_search_term	=	trim($params['searchterm']);
		
		$final_arr =array();
		$records_arr	=	array();
		$sql_get_cmpname	=	"SELECT DISTINCT(cmpname) FROM  online_regis1.tbl_company_standard where active_flag=1 and cmp_type_flag=0 AND  cmpname LIKE '%".addslashes(trim($valid_search_term))."%' ORDER BY cmpname ".$limit_cond."";
		
		$res_get_cmpname = parent::execQuery($sql_get_cmpname, $this->conn_idc); 
		
		$response_arr	=	array();
		$numRows	=	parent::numRows($res_get_cmpname);
		
		//$response_arr['numRows']	=	$numRows;
		if($numRows>0)
		{
			$records_arr	=	array();
			while($rows_get_cmpname= parent::fetchData($res_get_cmpname))
			{
				$records_arr[]	=	$rows_get_cmpname;
			}
		}
		
		if(!empty($text_cmp_type))
		{
			$sql_get_cmpname_type	=	"SELECT DISTINCT(cmpname) FROM  online_regis1.tbl_company_standard where active_flag=1 and cmp_type_flag=1 AND  cmpname LIKE '%".addslashes(trim($text_cmp_type))."%' ORDER BY cmpname";
			$res_get_cmpname_type = parent::execQuery($sql_get_cmpname_type, $this->conn_idc); 
			
			$cmp_type_arr	=	array();
			if(parent::numRows($res_get_cmpname_type)>0)
			{
				$i=0;
				while($rows_get_cmpname_type=parent::fetchData($res_get_cmpname_type)){
					$cmp_type_arr[]	=	$rows_get_cmpname_type['cmpname'];
					$i++;
				}
			}
			
			$final_arr =array();
			if(count($cmp_type_arr)>0)
			{
				$i	=0;
				foreach($cmp_type_arr as $key => $val)
				{
					$final_arr[$i]['cmpname']	=	trim(urldecode($valid_search_term))." (".$val.")" ;
					if(is_array($records_arr) && count($records_arr)>0)
					{
						foreach($records_arr as $key2=>$val2)
						{
							$final_arr[$i]['cmpname']	=	$val2['cmpname']. " (".$val.")";		
							$i++;
						}
					}
					else
						$i++;	
				}	
			}
		}
		else
			$final_arr	=	$records_arr;
		
		$response_arr['cmpdata']= $final_arr;	
		$response_arr['numRows']= count($final_arr);	
		return $response_arr;
	}	
	
	
	function photo_option()
	{
		global $params;		
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get/update photo option \n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		if(trim($params['parentid'])=='') 
		{
			$message = "parentid blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(trim($params['data_city'])=='') 
		{
			$message = "data city blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}		
		if(trim($params['type'])=='') 
		{
			$message = "type blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		$output_arr = array();
		$output_arr['parentid'] =  	$params['parentid'];
		if(strtolower($params['type']) == 'get')
		{						
			//http://shitalpatil.jdsoftware.com/jdbox/services/location_api.php?rquest=photo_option&trace=1&data_city=mumbai&type=get&parentid=PXX22.XX22.171102113727.J9S8
			$sql	=	"SELECT IF(misc_flag&2=2,'2',IF(misc_flag&4=4,'4',IF(misc_flag&8=8,'8',''))) AS misc_flag_bit,misc_flag,BIN(misc_flag) AS bin_misc_flag FROM db_iro.tbl_companymaster_extradetails WHERE parentid='".$params['parentid']."'";
			$res = parent::execQuery($sql, $this->conn_iro); 
			if($res && mysql_num_rows($res)>0)
			{
				$row = mysql_fetch_assoc($res);				
				$output_arr['option'] = "";		
				$output_arr['remark'] = "";		
				if($row['misc_flag_bit'] == '2')
					$output_arr['option'] = "Take my content live";
				else if($row['misc_flag_bit'] == '4')
					$output_arr['option'] = "Take my content live & send me for review";
				else if($row['misc_flag_bit'] == '8')
					$output_arr['option'] = "Take my content live after my review";	
				else
				{	
					$output_arr['option'] = "Take my content live & send me for review";		
					$output_arr['remark'] = "Photo option not set. This is default value";		
				}
			}
			else
			{
				$message = "Invalid Parentid.";
				echo json_encode($this->send_die_message($message));
				die();
			}
		}
		else if(strtolower($params['type']) == 'update')
		{		
			if(trim($params['option'])=='') 
			{
				$message = "user option blank.";
				echo json_encode($this->send_die_message($message));
				die();
			}			
			//http://shitalpatil.jdsoftware.com/jdbox/services/location_api.php?rquest=photo_option&trace=1&data_city=mumbai&type=update&parentid=PXX22.XX22.171102113727.J9S8&option=take my content live
			$option = strtolower(urldecode($params['option']));
			if($option == 'take my content live')
				$misc_flag = "2";
			else if(($option) == 'take my content live & send me for review')
				$misc_flag = "4";
			else if(($option) == 'take my content live after my review')
				$misc_flag = "8";	
			else
			{
				$message = "invalid photo option.";
				echo json_encode($this->send_die_message($message));
				die();
			}
			
			$params['shadow_misc_flag']	= $misc_flag;
			$data = $this->get_misc_flag();	
		 
			if($data['misc_flag'] != $data['misc_flag_live'])
			{
				$compdata	=	$this->company_data($params['parentid']);
				$post_arr = Array();
				$post_arr['parentid']	=	$compdata['parentid'];
				$post_arr['sphinx_id']	=	$compdata['sphinx_id'];
				$post_arr['data_city']	=	$compdata['data_city'];
				$post_arr['companyname']	=	$compdata['companyname'];
				$post_arr['ucode']	=	'Owner Content Preference';
				$post_arr['uname']	=	'Owner Content Preference';
				$post_arr['source']	=	'Owner Content Preference';
				$post_arr['misc_flag']	=	$misc_flag;
				
				$sql_source = "SELECT scode FROM d_jds.source WHERE UPPER(TRIM(sname))='Owner Content Preference'";
				$res_source = parent::execQuery($sql_source, $this->conn_iro); 
				$row_source	= mysql_fetch_assoc($res_source);
				
				$post_arr['mainsource']	= $row_source['scode'];
				$post_arr['subsource']	= 'Owner Content Preference';
				$post_arr['datesource']	= date("Y-m-d H:i:s");
				$post_arr['validationcode'] = 'JOINFREE';
				
				$jdbox_url = str_replace('services/','',$this->get_jdbox_url());
				$jdbox_url = $jdbox_url."insert_api.php";
				
				$data_jdbox =  json_decode($this->get_curl_data($jdbox_url,$post_arr),true);		
				
				$output_arr['remark']	=	'update successful';
				$insert_log = "INSERT INTO d_jds.tbl_photo_video_option_log SET 
							parentid	=	'".$params['parentid']."',
							data_city	=	'".$params['data_city']."',
							user_option	=	'".$option."',
							misc_flag	=	'".$data['misc_flag']."',
							remark		=	'".$output_arr['remark']."',
							insert_date	=	now()"; 
				$res_insert_log = parent::execQuery($insert_log, $this->conn_iro); 				
			}	 
		}
		else 
		{
			$message = "Invalid Type";
			echo json_encode($this->send_die_message($message));
			die();
		}
		$output['result']				= 	$output_arr;
		$output['error']['message'] 	=   "success";		
		if($params['trace'] == 1){
			echo "<hr><br>";print_r($output);
			echo "<hr>";
		}		
		return ($output);
	}	
	function duplicate_check_api()
	{
		global $params;
		if($params['trace'] == 1)
		{
			echo "<prE>";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : duplicate check for reseller data\n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		
		$param_data = Array();
		$data_url = $this->get_jdbox_url()."/mongoWrapper.php";
		$param_data['action'] =	'getalldata';
		$param_data['post_data'] =	'1';
		$param_data['parentid'] =	$params['parentid'];
		$param_data['data_city'] =	$params['data_city'];
		$param_data['module'] 	  =	'ME';
		
		$data_res =	json_decode($this->get_curl_data($data_url,$param_data),true);
		$cmp_data_arr =  $data_res['tbl_companymaster_generalinfo_shadow'];
	 
		$rflag 			= ((in_array(strtolower($this->data_city), $this->dataservers)) ? 0 : 1);

		
		$curl_url		=	$this->get_jdbox_url()."duplicate_check.php";
		$param_arr	=	array();
		$param_arr['data_city']			=	$this->data_city;
		$param_arr['module']			=	'CS';
		$param_arr['companyname']		=	$cmp_data_arr['companyname'];
		$param_arr['pincode']			=	$cmp_data_arr['pincode'];
		$param_arr['phone']				=	trim(($cmp_data_arr['mobile'].','.$cmp_data_arr['landline']),',');
		$param_arr['area']				=	$cmp_data_arr['area'];
		$param_arr['landmark']			=	$cmp_data_arr['landmark'];
		$param_arr['street']			=	$cmp_data_arr['street'];
		$param_arr['building']			=	$cmp_data_arr['building_name'];
		$param_arr['rflag']				=	$rflag;
		$param_arr['n']					=	'1';
		
	    $output_arr =  json_decode($this->get_curl_data($curl_url,$param_arr),true);	
		if($params['trace'] == 1)
		{
				echo "<pre>";print_r($output_arr);
		}
	   
	   $perfect_match_flag	=	0;
	   $probable_match_flag	=	0;
	   
	   $response_arr =	array();
	   if(is_array($output_arr['perfect_match']) && count($output_arr['perfect_match'])>0)
	   {
			foreach($output_arr['perfect_match'] as $key_perfect_match => $val_perfect_match)
			{
				if($val_perfect_match['parentid'] == $params['parentid'] || $val_perfect_match['display_flag'] == 0)
				{
					unset($output_arr['perfect_match'][$key_perfect_match]);					 
				}
			}
			if(count($output_arr['perfect_match'])>0)
				$perfect_match_flag	=	1;
	   }
		
		if($perfect_match_flag	==	1)
			$probable_match_flag = 0;
		elseif(is_array($output_arr['probable_match']) && count($output_arr['probable_match'])>0)
		{
			foreach($output_arr['probable_match'] as $key_probable_match => $val_probable_match)
			{
				if($val_probable_match['parentid'] == $params['parentid']  || $val_probable_match['display_flag'] == 0)
				{
					unset($output_arr['probable_match'][$key_probable_match]);
				}
			}
			if(count($output_arr['probable_match'])>0)
				$probable_match_flag	=	1;
		}
		
		$response_arr['parentid']				=	$params['parentid'];
		$response_arr['data_city']				=	$this->data_city;
		$response_arr['perfect_match_flag']		=	$perfect_match_flag;
		$response_arr['probable_match_flag']	=	$probable_match_flag;
		
		
		$output['result']				= 	$response_arr;
		$output['error']['message'] 	=   "success";		
		
		if($params['trace'] == 1){
			echo "<hr><br>";print_r($output);
			echo "<hr>";
		}
	    return $output;   
	}
	function push_reseller_data()
	{
		global $params;
		if($params['trace'] == 1)
		{
			echo "<prE>";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To push reseller data for audit\n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		if(empty($params['parentid']) || empty($params['module_type']) || empty($params['data_city'])  || empty($params['entered_date']) || empty($params['userid']))
		{
			$output_arr  = 'parentid/module_type/data_city/entered_date/userid BLANK';			
		}
		else 
		{
			$check_exists = "SELECT * FROM d_jds.tbl_single_bform_audit_data WHERE parentid='".$params['parentid']."' AND status_flag=0 and module_type='".$params['module_type']."' and entered_date	='".$params['entered_date']."' AND  userid='".$params['userid']."'";
			$res_check_exists = parent::execQuery($check_exists, $this->conn_iro); 
			if($res_check_exists && mysql_num_rows($res_check_exists)==0)
			{
				$compdata	=	$this->company_data($params['parentid']);
				$dialer_data = Array();				
				$phone = explode(",",$compdata['landline']);
				$mobile = explode(",",$compdata['mobile']);
				
				 
				$phone_str="";
				$mobile_str="";			
							
				$stdcode = $compdata['stdcode'];
				if(count($phone)>0)
				{
					foreach($phone as $key=>$val)
					{
						$val = preg_replace('/[^A-Za-z0-9\-]/', '',$val);
						
						if(!empty($val))
							$phone_str .= $stdcode.$val.",";		
					}
				}
				if(count($mobile)>0)
				{
					foreach($mobile as $key=>$val)
					{
						$val = preg_replace('/[^A-Za-z0-9\-]/', '',$val);
						$val = ltrim($val,"0");
						if(!empty($val))
							$mobile_str .= $val.",";
						
					}
				}	
				$phone_str 	= trim($phone_str,",");
				$mobile_str = trim($mobile_str,",");
				
				$landline_arr 	= explode(",",$phone_str);
				$mobile_arr 	= explode(",",$mobile_str);
				
				foreach($mobile_arr AS $key=>$val)
				{
					if(!empty($val))
						$mobile_arr[$key]	= "0".ltrim($val,"0");
				}				
				foreach($landline_arr AS $key=>$val)
				{
					if(!empty($val))
						$landline_arr[$key]	= "0".ltrim($val,"0");
				}
				$contact_details = trim(($phone_str.",".$mobile_str),",");
				if(!empty($contact_details)){				 
					$dialer_data['landline_1']		=	$landline_arr['0'];
					$dialer_data['landline_2']		=	$landline_arr['1'];
					$dialer_data['landline_3']		=	$landline_arr['2'];
					$dialer_data['landline_4']		=	$landline_arr['3'];
					$dialer_data['landline_5']		=	$landline_arr['4'];
					$dialer_data['mobile_1']		=	$mobile_arr['0'];
					$dialer_data['mobile_2']		=	$mobile_arr['1'];
					$dialer_data['mobile_3']		=	$mobile_arr['2'];
					$dialer_data['mobile_4']		=	$mobile_arr['3'];
					$dialer_data['mobile_5']		=	$mobile_arr['4'];
				}  
				
			 
				$insert = "INSERT INTO d_jds.tbl_single_bform_audit_data SET
					parentid		=	'".$params['parentid']."',
					module_type		=	'".strtoupper($params['module_type'])."',
					data_city		=	'".$params['data_city']."',
					entered_date	=	'".$params['entered_date']."',
					userid			=	'".$params['userid']."',
					updatedby		=	'".$params['userid']."',
					updater_name	=	'".addslashes($params['uname'])."',
					updated_date	=	NOW(),
					landline_1		=	'".$dialer_data['landline_1']."',
					landline_2		=	'".$dialer_data['landline_2']."',
					landline_3		=	'".$dialer_data['landline_3']."',
					landline_4		=	'".$dialer_data['landline_4']."',
					landline_5		=	'".$dialer_data['landline_5']."',
					mobile_1		=	'".$dialer_data['mobile_1']."',
					mobile_2		=	'".$dialer_data['mobile_2']."',
					mobile_3		=	'".$dialer_data['mobile_3']."',
					mobile_4		=	'".$dialer_data['mobile_4']."',
					mobile_5		=	'".$dialer_data['mobile_5']."',
					source			=	'".$params['source']."'";
				
				$res_insert = parent::execQuery($insert, $this->conn_iro); 
				$output_arr = 'DATA INSERTED SUCCESSFULLY';
			}
			else
			{
				$output_arr = 'DATA ALREADY EXISTS';
			}
		}	
		$output['numRows'] 				=   count($count);
		$output['result']				= 	$output_arr;
		$output['error']['message'] 	=   "success";		
		if($params['trace'] == 1){
			echo "<hr><br>";print_r($output);
			echo "<hr>";
		}		
		return ($output);	
	}
	
	function get_cmp_data()
	{
		global $params;
		$sql_get_cmp_data	=	"SELECT * FROM tbl_companymaster_generalinfo a JOIN tbl_companymaster_extradetails  b ON a.parentid=b.parentid WHERE a.parentid='".$params['parentid']."' LIMIT 1";
		
		
		$res_get_cmp_data = parent::execQuery($sql_get_cmp_data, $this->conn_iro); 
		$numRows = mysql_num_rows($res_get_cmp_data);
		
		$rows	=	array();
		if($numRows > 0)
			$rows = mysql_fetch_assoc($res_get_cmp_data);
		
		return $rows;	 
			
	}
	
	function company_data($parentid)
	{
		$sql_get_cmp_data	=	"SELECT * FROM tbl_companymaster_generalinfo a JOIN tbl_companymaster_extradetails  b ON a.parentid=b.parentid WHERE a.parentid='".$parentid."' LIMIT 1";
		
		$res_get_cmp_data = parent::execQuery($sql_get_cmp_data, $this->conn_iro); 
		$numRows = mysql_num_rows($res_get_cmp_data);
		
		$rows	=	array();
		if($numRows > 0)
			$rows = mysql_fetch_assoc($res_get_cmp_data);
		
		return $rows;	 
			
	}
	function jda_cf_data()
	{
		global $params;
		if($params['trace'] == 1)
		{
			echo "<prE>";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To push JDA data for audit\n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		$param_dc = array();
		$param_dc['parentid']	=	$params['parentid'];
		$param_dc['ucode']		=	$params['ucode'];
		$param_dc['data_city']	=	urlencode($params['data_city']);
		$ret_dc = $this->data_correction_api($param_dc,'JDA_CF');		
		 
		if($params['trace'] == 1)
		{
			echo "<hr>"; 
			print_r($ret_dc);
			echo "<hr>"; 
		}
		return $ret_dc; 	
	}
	
	function update_chain_outlet_shadow_tbl()
	{
		global $params;
		
		$response_arr	=	array();
		
		$response_arr['error']	=	1;
		if(empty($params['parentid']) || empty($params['companyname']) || empty($params['data_city'])|| empty($params['empcode']))
		{
			$response_arr['msg']	=	'Parentid,Companyname,Data City and Empcode can not be keep empty';
			return $response_arr;
		}
		
		if(isset($params['landline']))
		{
			$landline 		= $params['landline'];
			$landline_cond	=	"landline = '".$landline."',";
		}
		if(isset($params['mobile']))
		{	
			$mobile = $params['mobile'];	 
			$mobile_cond	=	"mobile = '".$mobile."',";
		}
		if(isset($params['tollfree']))
		{	
			$tollfree = $params['tollfree'];	 	
			$tollfree_cond	=	"tollfree = '".$tollfree."',";
		}
		
		$sql_update_shadow_tbl	=	"INSERT INTO tbl_chain_outlet_tagged_contract_shadow SET
										parentid = '".$params['parentid']."',
										companyname = '".addslashes($params['companyname'])."',
										data_city = '".$params['data_city']."',
										".$landline_cond."
										".$mobile_cond."
										".$tollfree_cond."
										updatedby='".$params['empcode']."',
										updater_name='".$params['empname']."',
										updated_date =NOW()
										ON DUPLICATE KEY UPDATE
										companyname = '".addslashes($params['companyname'])."',
										".$landline_cond."
										".$mobile_cond."
										".$tollfree_cond."
										updatedby='".$params['empcode']."',
										updater_name='".$params['empname']."',
										updated_date =NOW()";	
			$res_update_shadow_tbl 	= parent::execQuery($sql_update_shadow_tbl, $this->conn); 
			if($res_update_shadow_tbl)
			{
				$response_arr['error']	=	0;
				$response_arr['msg']	=	'Successfully updated';
			}
			else
				$response_arr['msg']	=	'There was some issue while updating shadow table';
			
			if($params['trace'] == 1)
			{
				echo "<pre>";print_r($params);
			}
			return $response_arr;
	}
	
	function get_chain_outlet_shadow_data()
	{
		global $params;
		
		$response_arr	=	array();
		
		$response_arr['error']	=	1;
		if(empty($params['parentid']) || empty($params['data_city']))
		{
			$response_arr['msg']	=	'Parentid and Data City can not be keep empty';
			return $response_arr;
		}
	
		$sql_get_data					= "SELECT landline,mobile,tollfree FROM tbl_chain_outlet_tagged_contract_shadow WHERE parentid='".$params['parentid']."'";
		$res_get_data 					= parent::execQuery($sql_get_data, $this->conn); 
		$numrows_zone 					= parent::numRows($res_get_data); 
		$response_arr['num_rows']		= $numrows_zone;	
		$response_arr['data'] ='';
		if($numrows_zone > 0)
		{
			$rows_get_zone= parent::fetchData($res_get_zone);
			$response_arr['error']	=	0;
			$response_arr['data']	=	$rows_get_zone;
		}
		
		if($params['trace'] == 1)
		{
			echo "<pre>";print_r($params);
		}
		return $response_arr;
	}
	
	function populate_chain_outlet_main_tbl()
	{
		global $params;
		$response_arr	=	array();
		
		$response_arr['error']	=	1;
		if(empty($params['parentid']) || empty($params['data_city']) || empty($params['ucode']))
		{
			$response_arr['msg']	=	'Parentid,Data City,Empcode can not be keep empty';
			return $response_arr;
		}
		
		if(!empty($params['uname']))
		{
			$uname		 = $params['uname'];
			$uname_cond	 = "'".$uname."',";
		}
		
		$response_arr	=	array();
		$change_flag = 0;
		$get_old_data		=	"SELECT landline,mobile,tollfree FROM tbl_chain_outlet_tagged_contract WHERE parentid='".$params['parentid']."' LIMIT 1";
		$res_old_data 		= 	parent::execQuery($get_old_data, $this->conn); 
		$num_old_data 		=	parent::numRows($res_old_data); 
		
		$row_old_data	=	array();
		if($num_old_data>0)
		{
			$row_old_data	=	parent::fetchData($res_old_data); 
		}
		
		$get_user_data		=	"SELECT landline,mobile,tollfree FROM tbl_chain_outlet_tagged_contract_shadow WHERE parentid='".$params['parentid']."' LIMIT 1";
		$res_user_data 		= 	parent::execQuery($get_user_data, $this->conn); 
		$num_user_data 		=	parent::numRows($res_user_data); 
		
		$row_user_data	= array();
		if($num_user_data>0)
		{
			$row_user_data	=	parent::fetchData($res_user_data);
		}
		$diff_arr1=array_diff($row_old_data,$row_user_data);
		$diff_arr2=array_diff($row_user_data,$row_old_data);
		
		$key_diff1	=	array_keys($diff_arr1);
		$key_diff2	=	array_keys($diff_arr2);
		$key_arr	=	array_unique(array_merge($key_diff2,$key_diff1));
		if((is_array($diff_arr1) && count($diff_arr1)>0 ) || (is_array($diff_arr2) && count($diff_arr2)>0))
		{
			$change_flag =1 ;
		}
		$sql_populate_main_tbl	=	"REPLACE INTO tbl_chain_outlet_tagged_contract
					(parentid,companyname,data_city,landline,mobile,tollfree,updatedby,updater_name,updated_date)
					SELECT parentid,companyname,data_city,landline,mobile,tollfree,'".$params['ucode']."', ".$uname_cond." NOW() FROM tbl_chain_outlet_tagged_contract_shadow WHERE parentid='".$params['parentid']."'";
		$res_populate_main_tbl 	= parent::execQuery($sql_populate_main_tbl, $this->conn); 
		
		if($res_populate_main_tbl)
		{
			$response_arr['error']	=	0;
			$response_arr['msg']	=	'Successfully updated';
			if($change_flag == 1)
			{
				$sql_update_log	=	"INSERT INTO tbl_chain_outlet_contract_log SET 
										parentid='".$params['parentid']."',
										data_city='".$params['data_city']."',
										changed_field='".implode(',',$key_arr)."',
										landline_old='".$row_old_data['landline']."',
										landline_new='".$row_user_data['landline']."',
										mobile_old='".$row_old_data['mobile']."',
										mobile_new='".$row_user_data['mobile']."',
										tollfree_old='".$row_old_data['tollfree']."',
										tollfree_new='".$row_user_data['tollfree']."',
										updatedby='".$params['ucode']."',
										updater_name='".$params['uname']."',
										updated_date=NOW()";
				$res_update_log 	= parent::execQuery($sql_update_log, $this->conn); 						
				if(!$res_update_log)
				{
					$response_arr['error']	=	0;
					$response_arr['msg']	=	'log not maintaing. Kindly contact with software team';
				}
			}
		}
		else
			$response_arr['msg']	=	'There was some issue while updating shadow table';
		
		if($params['trace'] == 1)
		{
			echo "<pre>";print_r($params);
		}
		return $response_arr;
	}
	
	function chain_outlet_main_to_shadow()
	{
		global $params;
		
		$response_arr	=	array();
		
		$response_arr['error']	=	1;
		if(empty($params['parentid']) || empty($params['data_city']))
		{
			$response_arr['msg']	=	'Parentid,Data City can not be keep empty';
			return $response_arr;
		}
		$sql_get_cnt			=	"SELECT COUNT(1) AS cnt FROM tbl_chain_outlet_tagged_contract WHERE parentid='".$params['parentid']."'";
		$res_get_cnt 			=   parent::execQuery($sql_get_cnt, $this->conn); 
		
		$rows_get_cnt 			=   parent::fetchData($res_get_cnt); 
		
		
		if($rows_get_cnt['cnt'] == '0')
			$sql_populate_main_tbl	=	"UPDATE  tbl_chain_outlet_tagged_contract_shadow SET 
										landline ='',
										mobile ='',
										tollfree =''
										WHERE parentid='".$params['parentid']."';";
		else
			$sql_populate_main_tbl	=	"REPLACE INTO tbl_chain_outlet_tagged_contract_shadow
					(parentid,companyname,data_city,landline,mobile,tollfree,updatedby,updater_name,updated_date)
					SELECT parentid,companyname,data_city,landline,mobile,tollfree,updatedby,updater_name,updated_date FROM tbl_chain_outlet_tagged_contract WHERE parentid='".$params['parentid']."'";
		
		$res_populate_main_tbl 	= parent::execQuery($sql_populate_main_tbl, $this->conn); 
		
		if($res_populate_main_tbl)
		{
			$response_arr['error']	=	0;
			$response_arr['msg']	=	'Successfully updated';
		}
		else
			$response_arr['msg']	=	'There was some issue while updating shadow table';
		
		if($params['trace'] == 1)
		{
			echo "<pre>";print_r($params);
		}
		return $response_arr;
	}
	function overwritten_check()
	{
		global $params;
		
		/*
		1. Company Name Change 80% or more than 80% + Area/Pincode Changed
		2. Company Name Change 80% or more than 80% + All Phone (including Mobile) Numbers Changed
		3. Area/Pincode Changed + All Phone (including Mobile0 Numbers Changed
		*/
		
		if($params['trace'] == 1)
		{
			echo "<prE>";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To check overwirrten cases\n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		
		$user_data		=  json_decode($params['user_data'],true);
		$compDataLive 	= 	$this->company_data($params['parentid']);

		if(count($compDataLive)>0)
		{
			$user_data_field_arr	=	array_keys($user_data);
			foreach($compDataLive as $field_live=>$val_live)
			{
				if(!in_array($field_live,$user_data_field_arr))
					$user_data[$field_live]	= $val_live;
			}
			
			/*$comp_live_arr		=	array_filter(explode(" ",stripslashes(preg_replace("/[^ \w]+/", "", $this->remove_special_character($compDataLive['companyname'])))));
			$comp_shadow_arr	=	array_filter(explode(" ",stripslashes(preg_replace("/[^ \w]+/", "", $this->remove_special_character($params['companyname'])))));
			
			$weight_match_arr	=   array_intersect(array_map('strtolower', $comp_live_arr),array_map('strtolower', $comp_shadow_arr)); 
			$comp_match   		= 	ROUND(count($weight_match_arr)*2/(count($comp_shadow_arr) + count($comp_live_arr))*100,2);*/					
			
			$OldStr	=	preg_replace("/[^ \w]+/", "", $this->remove_special_character(strtolower($compDataLive['companyname'])));
			$NewStr	=	preg_replace("/[^ \w]+/", "", $this->remove_special_character(strtolower($params['companyname'])));
			$comp_match 		=	similar_text($OldStr, $NewStr, $perc);
			$comp_match			=	ceil($perc);
			
			
			
			// check area pincode changes
			$area_change	=	1;
			$pincode_change	=	1;
			$areapincode_change = 0;
			if(empty($compDataLive['area']) || (!empty($compDataLive['area']) && strtolower(trim($compDataLive['area'])) == strtolower(trim($params['area']))))
				$area_change	=	0;
			
			if(empty($compDataLive['pincode']) || (!empty($compDataLive['pincode']) && strtolower(trim($compDataLive['pincode'])) == strtolower(trim($params['pincode']))))
				$pincode_change	=	0;
				
			if($pincode_change == 1 || $area_change == 1) 
				$areapincode_change	=	1;
			
			$landline_live_arr 		= 	array_filter(explode(",",$compDataLive['landline']));
			$landline_shadow_arr 	=  	array_filter(explode(",",$params['landline']));
			$landline_intersect	 	= 	array_intersect($landline_live_arr,$landline_shadow_arr); 
			
			$mobile_live_arr 		= array_filter(explode(",",$compDataLive['mobile']));
			$mobile_shadow_arr 		= array_filter(explode(",",$params['mobile']));
			$mobile_intersect		= array_intersect($mobile_live_arr,$mobile_shadow_arr);
		
			//No need to check tollfree number as told by sufyan
			/*$tollfree_live_arr 	  	= 	array_filter(explode(",",$compDataLive['tollfree']));
			$tollfree_shadow_arr  	= 	array_filter(explode(",",$params['tollfree']));			
			$tollfree_intersect	 	= 	array_intersect($tollfree_live_arr,$tollfree_shadow_arr);*/
			
			
			
			// check area pincode changes
			$landline_change 	= 1;
			$mobile_change 		= 1;
			$tollfree_change 	= 1;
			$contact_change 	= 1;
			
			$contact_live_arr		=	array_merge($landline_live_arr,$mobile_live_arr);
			$contact_shadow_arr		=	array_merge($landline_shadow_arr,$mobile_shadow_arr);
			$contact_intersect		= 	array_intersect($contact_live_arr,$contact_shadow_arr);
			
			if(count($contact_live_arr) == 0 || count($contact_intersect) > 0)
				$contact_change	=	0;
			
			
			/*if(count($landline_live_arr) == 0 || count($landline_intersect) > 0) 
				$landline_change	=	0;
			if(count($mobile_live_arr) == 0 || count($mobile_intersect) > 0) 
				$mobile_change	=	0;
			if(count($tollfree_live_arr) == 0 || count($tollfree_intersect) > 0) 
				$tollfree_change	=	0;		
				
			if($landline_change == 1 || $mobile_change ==1 || $tollfree_change ==1)
				$contact_change	=	1;*/
			
			if(($comp_match < 80  && $areapincode_change == 1) || ($comp_match < 80  && $contact_change == 1) || ($areapincode_change == 1  && $contact_change == 1))
			{
				$compData_OR = Array();
				$compData_OR['parentid']	=	$params['parentid'];
				$compData_OR['ucode']		=	$params['ucode'];
				$compData_OR['data_city']	=	$params['data_city'];
				$ret = $this->data_correction_api($user_data,'OVERWRITTEN_DATA');		
				$remark = 'sent to moderation';
			
			}
			else
			{
				$remark = 'not sent to moderation';
			}
		}
		else
		{			
			$remark = 'New data - not sent to moderation';
		}
		$output['numRows'] 				=   '1';
		$output['result']				= 	$remark;
		$output['error']['message'] 	=   "success";		
		if($params['trace'] == 1){
			echo "<hr><br>";print_r($output);
			echo "<hr>";
		}
		return ($output);
	}
	function get_extention()
	{
		global $params;
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get extn list \n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
			 
		}	
		$sql = "SELECT a.parentid,a.data_city,a.phone_1,a.extn_1,a.phone_2,a.extn_2,a.phone_3,a.extn_3,a.phone_4,a.extn_4,a.phone_5,a.extn_5,a.phone_6,a.extn_6,a.phone_7,a.extn_7,a.phone_8,a.extn_8,a.phone_9,a.extn_9,a.phone_10,a.extn_10,		a.phone_11,a.extn_11,a.phone_12,a.extn_12,a.phone_13,a.extn_13,a.phone_14,a.extn_14,a.phone_15,a.extn_15,a.phone_16,a.extn_16,a.phone_17,a.extn_17,a.phone_18,a.extn_18,a.phone_19,a.extn_19,a.phone_20,a.extn_20,a.phone_21,a.extn_21,a.phone_22,a.extn_22,a.phone_23,a.extn_23,a.phone_24,a.extn_24,a.phone_25,a.extn_25,a.phone_26,a.extn_26,a.phone_27,a.extn_27,a.phone_28,a.extn_28,a.phone_29,a.extn_29,a.phone_30,a.extn_30,b.data_source FROM d_jds.tbl_doctor_data_dialer a JOIN d_jds.tbl_tmesearch b on a.parentid=b.parentid  WHERE a.parentid='".$params['parentid']."' LIMIT 1";
		

		
		$res = parent::execQuery($sql, $this->conn_iro); 
		$numRows = mysql_num_rows($res);
		
		$rows	=	array();
		if($numRows > 0)
			$rows = mysql_fetch_assoc($res);
		else 
			$rows = array('No data found');
		
		$output['numRows'] 				=   $numRows; 
		$output['result'] 				=   $rows;
		$output['error']['message'] 		=  "success";		
		if($params['trace'] == 1){
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}			
		return $output;			
	}
	
	function push_jda_me_data()
	{
		global $params;
		
		if($params['trace'] == 1)
		{
			echo "<prE>";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To push_jda_me_data\n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		
		$user_data_arr		=	json_decode($params['user_data'],true);			
		//$liveData_arr		=	$this->company_data($params['parentid']);
		$liveData_arr		=	json_decode($params['old_data'],true);;
		
		
		/*$valid_filed_array = array('companyname','state','building_name','area','city','street','landmark','stdcode','mobile','landline','email','pincode','contact_person','contact_person','payment_type','working_time_start','working_time_end','year_establishment','fb_prefered_language','website','tollfree','fax','fbMedia','othercity_number','catidlineage');*/
		
		
		$priority_field	=	'';
		if(is_array($liveData_arr) && count($liveData_arr)>0)
		{
			/*$arr_diff	=	array();
			foreach($liveData_arr AS $field=>$value) 
			{
				if(trim(strtolower($user_data_arr[$field])) != trim(strtolower($value)) && in_array($field,$valid_filed_array))
					$arr_diff[$field] = $value;
			}*/
			//$arr_invalid_field_names 	= array_keys($arr_diff);
			
			$arr_invalid_field_names	=	$this->get_changes_fields($liveData_arr,$user_data_arr);
			$priority_field		= implode(",",$arr_invalid_field_names);
		}	

		if(count($liveData_arr)>0)
			$new_flag	=	0;
		else	
			$new_flag	=	1;
		
		/*$sql_check_emp		=	"SELECT emptype FROM login_details.tbl_loginDetails WHERE mktEmpCode='".$user_data_arr['ucode']."' LIMIT 1";
		$res_log_update 	=  parent::execQuery($sql_check_emp, $this->conn_idc);
		$rows_log_update 	=  parent::fetchData($res_log_update);
		
		
		if($rows_log_update['emptype']	==	'13')
			$user_data_arr['source']	=	'JDA';*/
			
			
		//api to check employee source
		$secret_key	=	"TUFEaRsasqqNldsaxvzsdsdaUYcdeKmnR";
		$secret_key_encoded	=	urlencode($secret_key);
		$url	=	"http://192.168.20.237:8080/api/getEmployee_xhr.php?auth_token=".$secret_key_encoded."";
		$param	=	array();
		$param['empcode']		=	$user_data_arr['ucode'];
		$param['textSearch']	=	'4';
		$resp = $this->get_curl_data($url,$param);
		$empData_arr =  json_decode($resp,true);	
		
		if(is_array($empData_arr) && count($empData_arr['data'][0])>0)
		{
			if(strtoupper($empData_arr['data'][0]['type_of_employee'])=='ME' || strtoupper($empData_arr['data'][0]['type_of_employee'])=='JDA')
			{
				$user_data_arr['source']	=	$empData_arr['data'][0]['type_of_employee'];
				$user_data_arr['uname']		=	$empData_arr['data'][0]['empname'];
			}
			else
			{
				$response_arr['errorCode']	=	0;
				$response_arr['msg']	=	"Employee Not Found in JDA/ME";
				return $response_arr;
			}
			
		}
		else
		{
			$response_arr['errorCode']	=	0;
			$response_arr['msg']	=	"Employee Not Found";
			return $response_arr;
		}
		
		$str_old_details 	= addslashes(http_build_query($liveData_arr,'','|~@~|'));
		$str_user_details 	= addslashes(http_build_query($user_data_arr,'','|~@~|'));
		
		$sql_log_update	=	"REPLACE INTO online_regis1.tbl_jda_me_data SET 
								parentid			= '".$user_data_arr['parentid']."',
								data_city			=	'".$user_data_arr['data_city']."',
								entered_date		=	NOW(),
								paid				=	'".$user_data_arr['paid']."',
								source				=	'".$user_data_arr['source']."',
								priority_field		=	'".$priority_field."',
								new_contract_flag	=	'".$new_flag."',
								old_data			=	'".$str_old_details ."',
								user_data			=	'".$str_user_details."',
								updatedby		=	'".$user_data_arr['ucode']."',
								updater_name	=	'".addslashes($user_data_arr['uname'])."'";
		$res_log_update 	= parent::execQuery($sql_log_update, $this->conn_idc);					
		if($res_log_update)
		{
			$response_arr['errorCode']	=	0;
			$response_arr['msg']	=	"Data inserted successfully.";
		}
		else
		{
			$response_arr['errorCode']	=	1;
			$response_arr['msg']	=	"There was some issue while inserting data";
		}
		return $response_arr;
	}
	public function get_location()
	{
		global $params;
		 
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get area/street/landmark details from pincode/city/data_city/parent_area\n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		//$skip_keywords = Array('above','adj','adjacent','adjacent','adjacent to','adjacento','adjacentto','adjent','adjoing','adjoining','b/h','back','back side','backside','behind','below','beside','besides','bh','bhnd','close to','closeto','in front','infront','near','nearby','next','next to','nr','nxt','nxt to','opposite','opp','opping','oppoing','opposite','opps','oppsite','out side','outside','infront of','infrontof','in front of');
		$skip_keywords = Array('above','adjacent to','adjoining','behind','backside','below','besides','close to','in front of','inside','near','nearby','next to','opposite','outside');
					
		//get more relevant search keyword
		$relevant_search_arr=array();
		$i=0;
		foreach($skip_keywords as $key_keyword	=>$val_keyword)
		{
			if(stripos($val_keyword,trim($params['search'])) === 0)
			{
				$relevant_search_arr[$i]['areaname_display'] =ucfirst($val_keyword);
				$relevant_search_arr[$i]['entity_area_display'] =ucfirst($val_keyword);
				
				$relevant_search_arr[$i]['entity_area'] ='';
				$relevant_search_arr[$i]['parent_area'] ='';
				$i++;
			}
		}
		
		array_multisort(array_map('strlen', $skip_keywords), $skip_keywords);
		$skip_keywords = array_reverse($skip_keywords);
		$search_str = $params['search'];
		for($i = 0; $i < count($skip_keywords); $i++)
		{
			$search_str = str_replace($skip_keywords[$i]." ",'',strtolower(urldecode($search_str)));
		}
		$search_str	= str_replace(" ","",$this->sanitize(trim(preg_replace('/(\([^)]*\))/','',urldecode(trim($search_str))))));
		$search_str_arr = explode(" ",$search_str);
		$search_in_arr  = explode(" ",$params['search']);
		$search_str_arr = array_map('strtolower', $search_str_arr);
		$search_in_arr = array_map('strtolower', $search_in_arr);
		$search_out_arr = array_diff($search_in_arr,$search_str_arr);
		$search_out_str='';
		
		$search_out_str =  implode(" ",$search_out_arr);
		
		
		//added by naresh bhati
		foreach($skip_keywords as $key_keyword	=>$val_keyword)
		{
			
			if(stripos(trim($search_out_str),$val_keyword) === 0)
			{
				$search_out_str2 = substr($search_out_str,stripos(trim($search_out_str),$val_keyword),strlen($val_keyword));
				break;
			}
			else
				$search_out_str2 = '';
			
		}
		$search_out_str	=	$search_out_str2;
		
		if(!empty($params['pincode']))
		{
			$where_arr[] = "  pincode = '".$params['pincode']."'";
			$where_arr_comp[] = "  pincode = '".$params['pincode']."'";
		}	
		if(!empty($params['city']))
		{
			$where_arr[] = "  city = '".$params['city']."'";
			$where_arr_comp[] = "  city = '".$params['city']."'";
		}	
		if(!empty($params['data_city']))
		{
			$where_arr[] = "  data_city = '".$params['data_city']."'";	
			$where_arr_comp[] = "  data_city = '".$params['data_city']."'";	
		}	
		if(!empty($params['search']))
		{
			$where_arr[] = "  areaname_search_processed_ws like  '%".$search_str."%'";
			$where_arr_comp[] = "  companyname_search_WS like  '%".$search_str."%'";
		}	
		if(!empty($params['parent_area']) && !empty($params['type']) && $params['type']!=1 )
		{
			$where_arr[] = "  parent_area =  '".$params['parent_area']."'";		
			$where_arr_comp[] = "  area =  '".$params['parent_area']."'";					
		}	
		if(!empty($params['type']))
			$where_arr[] = " type_flag =  '".$params['type']."'";
			
		if(count($where_arr)>0)
			$where = "  ".implode(" AND ",$where_arr);		
		if(count($where_arr_comp)>0)
			$where_comp = " geocode_accuracy_level=1 AND ".implode(" AND ",$where_arr_comp);
		$limit = 0;
		if(!empty($params))
			$limit = $params['limit'];
	 
		//$sql = "SELECT DISTINCT areaname ,main_area,areaname_display,pincode,stdcode,parent_area ,entity_area,city,data_city,state,country,country_id,zoneid,type_flag,latitude_area,longitude_area,latitude_pincode,longitude_pincode,latitude_final,longitude_final,latitude_median,longitude_median,de_display,LENGTH(areaname) AS area_len FROM tbl_areamaster_consolidated_v3 WHERE  display_flag= 1   " .$where . " ORDER BY LENGTH(areaname)";
		$sql = "SELECT areaname ,main_area,concat('".ucfirst($search_out_str)." ',areaname_display) as areaname_display,concat('".ucfirst($search_out_str)." ',entity_area) as entity_area_display,pincode,stdcode,parent_area ,entity_area,city,data_city,state,country,country_id,zoneid,type_flag,latitude_area,longitude_area,latitude_pincode,longitude_pincode,latitude_final,longitude_final,latitude_median,longitude_median,de_display, geocode_accuracy_level,landmark_type,area_len FROM (
		SELECT 
		DISTINCT '' as parentid,areaname ,main_area,areaname_display,pincode,stdcode,parent_area ,entity_area,city,data_city,state,country,country_id,zoneid,type_flag,latitude_area,longitude_area,latitude_pincode,longitude_pincode,latitude_final,longitude_final,latitude_median,longitude_median,de_display,'0' as geocode_accuracy_level, 'areamaster' as landmark_type,LENGTH(areaname) AS area_len FROM  d_jds.tbl_areamaster_consolidated_v3 WHERE " .$where . " and display_flag=1 		
		UNION		
		SELECT DISTINCT parentid,concat(companyname,'-',area) as areaname,concat(companyname,'-',area) as main_area,concat(companyname,'-',area) as areaname_display,pincode,std_code as stdcode,area as parent_area,companyname as entity_area,city,data_city,state,'India' as country,'98' as country_id,zoneid,'2' as type_flag,latitude as latitude_area,longitude as longitude_area, latitude_pincode,longitude_pincode,latitude as latitude_final,longitude as longitude_final,'' AS latitude_median,'' AS longitude_median,'1' as de_display,geocode_accuracy_level,'companymaster' as landmark_type,LENGTH(companyname) AS area_len FROM  d_jds.tbl_company_landmark_autosuggest WHERE " .$where_comp . " ) aa GROUP BY aa.areaname,aa.pincode ORDER BY aa.landmark_type,aa.area_len ";
		
		$res = parent::execQuery($sql, $this->conn_iro); 
		
		$numRows = mysql_num_rows($res);
		if($numRows > 0){
			$x=0;
			$y=0;
			$z=0;
			$p=0;
			$ho_gpo_arr = array(' HO',' H.O.',' H O',' H. O.',' GPO',' G P O',' G.P.O.',' G. P. O.');
			while($result = mysql_fetch_assoc($res))
			{
				$skip_ho_gpo = 0;
				foreach($ho_gpo_arr AS $key=>$val)
				{
					if(strpos(strtoupper($result['areaname']),$val))
					{
						$skip_ho_gpo = 1;break;
					}
				}
				if($skip_ho_gpo == 0 || strtolower($params['live_area']) == strtolower($result['areaname']))
				{
				
					$result['areaname_display']		=	trim($result['areaname_display']);
					$result['entity_area_display']	=	trim($result['entity_area_display']);
					if($result['type_flag'] == 1 && $result['de_display'] == 1){
						if($limit>0 && $x < $limit)
							$return_array['areaname'][$x] = $result;				 					
						else if($limit==0)
							$return_array['areaname'][$x] = $result;				 					
						$x++;	 
					}	
					else if($result['type_flag'] == 2  && $result['de_display'] == 1){
						if($limit>0 && $y < $limit)
							$return_array['landmark'][$y]=$result;				 					
						else if($limit==0)
							$return_array['landmark'][$y]=$result;
						$y++;
					}	
					else if($result['type_flag'] == 3  && $result['de_display'] == 1){
						if($limit>0 && $z < $limit)
							$return_array['street'][$z] = $result;
						else if($limit==0)
							$return_array['street'][$z] = $result;
						$z++;	
					}
					else if($result['type_flag'] == 4  && $result['de_display'] == 0){
						if($limit>0 && $p < $limit)
							$return_array['synonym'][$p] = $result;	
						else if($limit==0) 		
							$return_array['synonym'][$p] = $result;	
						$p++;	
					}
				}	
			}
		}		
		if($params['type'] == 2)
		{
			if(is_array($return_array['landmark']) && count($return_array['landmark'])>0)
				$landmark_array['landmark']	=	array_merge($relevant_search_arr,$return_array['landmark']);
			else
				$landmark_array['landmark']	=	$relevant_search_arr;	
			$return_array				=	$landmark_array;
		}
		$output['numRows']  			=   $numRows;
		$output['result']  				=   $return_array;
		$output['error']['message'] 	=  "success";			 
		if($params['trace'] == 1){
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}
		return ($output);
	}
	public function pincode_porting()
	{
		global $params;		 
		if($params['trace'] == 1)
		{
			echo "<hr>";
			echo "Purpose : To port pincode from one city to other \n";
			echo "<hr>";
			echo "Input Parameters : \n";
			print_r($params);
			echo "<hr>";
		}
		$check_exist = "SELECT * FROM online_regis1.tbl_pincode_poritng_request WHERE pincode='".$params['pincode']."' AND done_flag=0";
		$res_exist = parent::execQuery($check_exist, $this->conn_idc); 
		if(mysql_num_rows($res_exist) > 0)
		{
			$numRows = 1;
			$message = "success";	
			$return_str = 'request already sent';
		}
		else if(empty($params['pincode']) || empty($params['city_new']) || empty($params['data_city_new']) || empty($params['zoneid_new']) || empty($params['stdcode_new']) || empty($params['userid']))
		{
			$numRows = 1;
			$message = "error";	
			$return_str = 'pincode/city_new/data_city_new/zoneid_new/stdcode_new/userid missing';
		}
		else
		{
			$get_area = "SELECT * FROM online_regis1.tbl_areamaster_consolidated_v3 WHERE pincode ='".$params['pincode']."' AND display_flag=1 LIMIT 1";
			$res_area = parent::execQuery($get_area, $this->conn_idc); 
			if(mysql_num_rows($res_area) > 0)
			{
				while($row_area = mysql_fetch_assoc($res_area))
				{
					$params['data_city_old'] =  $row_area['data_city'];
					$params['city_old'] =  $row_area['city'];
					$params['city_id_old'] =  $row_area['city_id'];
					$params['state_id_old'] =  $row_area['state_id'];
					$params['state_name_old'] =  $row_area['state'];
					$params['zoneid_old'] =  $row_area['zoneid'];
					$params['stdcode_old'] =  $row_area['stdcode'];
				}
			}	
			$get_city = "SELECT * FROM online_regis1.tbl_city_master WHERE ct_name   ='".$params['city_new']."'";
			$res_city = parent::execQuery($get_city, $this->conn_idc); 
			if(mysql_num_rows($res_city) > 0)
			{
				while($row_city = mysql_fetch_assoc($res_city))
				{	 
					$params['city_id_new'] = $row_city['city_id'];
					$params['state_id_new'] = $row_city['state_id'];
					$params['state_name_new'] = $row_city['state_name'];					 
				}
			}	
			
			$insert_request = "INSERT INTO online_regis1.tbl_pincode_poritng_request
								SET 
								pincode 		=	'".$params['pincode']."',
								city_old 		=	'".$params['city_old']."',
								city_new 		=	'".$params['city_new']."',
								data_city_old	=	'".$params['data_city_old']."',
								data_city_new	=	'".$params['data_city_new']."',
								stdcode_old		=	'".$params['stdcode_old']."',
								stdcode_new 	=	'".$params['stdcode_new']."',
								zoneid_old 		=	'".$params['zoneid_old']."',
								zoneid_new 		=	'".$params['zoneid_new']."',
								city_id_old 	=	'".$params['city_id_old']."',
								city_id_new 	=	'".$params['city_id_new']."',
								state_id_old 	=	'".$params['state_id_old']."',
								state_id_new 	=	'".$params['state_id_new']."',
								state_name_old 	=	'".$params['state_name_old']."',
								state_name_new 	=	'".$params['state_name_new']."',
								request_by 		=	'".$params['userid']."',
								request_date 	=	now(),
								request_type	=	'porting'";
								
			$res_insert_request = parent::execQuery($insert_request, $this->conn_idc); 
			if($res_insert_request)
			{
				$numRows = 1;
				$message = "success";	
				$return_str = 'request successful';
			}	
			else
			{
				$numRows = 0;
				$message = "failed";
				$return_str = 'request failed';
			}	
		}
		$output['numRows']  			=   $numRows;
		$output['result']  				=   $return_str;
		$output['error']['message'] 	=  	$message;		 
		if($params['trace'] == 1){
			echo "<hr>".$insert_request;
			echo "<hr><br>";print_r($output);
		}
		return ($output);		
	}
	
	public function check_removed_category()
	{
		global $params;		
		$user_data_arr		=	json_decode($params['user_data'],true);
		$user_catidlineage	=	str_replace("/","",trim($params['user_catidlineage'],"/"));
		$old_catidlineage	=	str_replace("/","",trim($params['old_catidlineage'],"/"));
		
		
		$user_catidlineage_arr	=	array_filter(explode(",",$user_catidlineage));
		$old_catidlineage_arr	=	array_filter(explode(",",$old_catidlineage));
		
		$removed_catidlineage_arr	=array();
		$removed_catidlineage_arr 	=	array_diff($old_catidlineage_arr,$user_catidlineage_arr);
		$response_arr	=	array();
		if(count($removed_catidlineage_arr)>0)
		{
			$removed_catidlineage_str	=	"'".implode("','",$removed_catidlineage_arr)."'";	
			//$sql_check_premium	=	"SELECT catid,category_name FROM tbl_categorymaster_generalinfo WHERE catid IN (".$removed_catidlineage_str.") AND category_type&16384=16384";
			//$res_check_premium 		= 	parent::execQuery($sql_check_premium, $this->conn);

			$cat_params = array();
			$cat_params['page'] ='location_class';
			$cat_params['skip_log'] 	='1';
			$cat_params['data_city'] 	= $this->data_city;		
			$cat_params['return']		= 'catid,category_name';	

			$where_arr  	=	array();
			$where_arr['category_type']	= "16384";
			$where_arr['catid']			= implode(",",$removed_catidlineage_arr);		
			$cat_params['where']		= json_encode($where_arr);			

			$cat_res_arr = array();
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);			
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}
			
			$invalid_catid_arr	=	array();
			$invalid_category_arr	=	array();
			if($cat_res_arr['errorcode'] == '0' && count($cat_res_arr['results'])>0)
			{
				foreach($cat_res_arr['results'] as $key=>$cat_arr)
				{
					$invalid_catid_arr[]	=	$cat_arr['catid'];
					$invalid_category_arr[]	=	$cat_arr['category_name'];
				}
			} 
			
			/*$sql_check_approved	=	"SELECT catid,category_name
						FROM tbl_premium_categories_audit a
						join tbl_categorymaster_generalinfo b
						on catids = catid
						where parentid='".$user_data_arr['parentid']."' and  catids IN (".$removed_catidlineage_str.") AND  approval_status = 1 ";*/
			//$res_check_approved 		= 	parent::execQuery($sql_check_approved, $this->conn); 
			$sql_check_approved	=	"SELECT  catids FROM tbl_premium_categories_audit WHERE parentid='".$user_data_arr['parentid']."' and  catids IN (".$removed_catidlineage_str.") AND  approval_status = 1 ";
			$res_check_approved = parent::execQuery($sql_check_approved, $this->conn );
			$final_catid_arr =array();
			if(parent::numRows($res_check_approved)>0){
				while ($rows_check_approved = parent::fetchData($res_check_approved)) {
					$catid =	trim($rows_check_approved['catids']);
					$final_catid_arr[]= $catid;
				}
			}

			if(count($final_catid_arr)>0)
			{
				$cat_params = array();
				$cat_params['page'] ='location_class';
				$cat_params['data_city'] 	= $this->data_city;
				$cat_params['return']		= 'catid,category_name';			

				$where_arr  	=	array();			
				$where_arr['catid']			= implode(",",$final_catid_arr);				
				$cat_params['where']		= json_encode($where_arr);
				
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);				
				$cat_res_arr = array();
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}

				if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){
					foreach($cat_res_arr['results'] as $key =>$rows_check_approved)
					{
					$invalid_catid_arr[]	=	$rows_check_approved['catid'];
					$invalid_category_arr[]	=	$rows_check_approved['category_name'];
					}
				}
			}
			
			if(count($invalid_catid_arr)>0)
			{
				$email_subject = "Approved premium/Authorized Category Removed - ".$user_data_arr['parentid'];
				$email_id 		= "dbescalations@justdial.com";	
				$email_id_bcc 	="naresh.bhati@justdial.com,shitalpatil@justdial.com";
				$email_id_cc	=	"";
				$email_text =	"Hi Team,<br/><br/>
								Please check below Approved premium/Authorized category removed by user.<br/><br/>
								<b>Details :</b> 
								<br/>
								Company Name : ".$user_data_arr['companyname']."
								<br/>
								Parent Id : ".$user_data_arr['parentid']."
								<br/>
								Category Removed : ".implode(',',array_unique($invalid_category_arr))."
								<br/>
								User Name : ".$user_data_arr['uname']."                        
								<br/>
								User Code : ".$user_data_arr['ucode']."
								<br/>
								Data City : ".$user_data_arr['data_city']."
								<br/>
								Date : ".$user_data_arr['updatedOn']."
								<br/>
								Module : ".strtoupper($user_data_arr['source'])."
								<br/><br/>
								Thanks,";         
								
				$params_SE = Array();
				$params_SE['city_name'] 	 	= $user_data_arr['data_city'];
				$params_SE['email_id'] 	 		= $email_id;
				$params_SE['email_subject']  	= $email_subject;
				$params_SE['email_text']  		= $email_text;
				$params_SE['email_id_cc'] 		= $email_id_cc;
				$params_SE['email_id_bcc'] 		= $email_id_bcc;
				$params_SE['source'] 	 		= 'CS';
				$params_SE['mod'] 		 		= 'common_panindia';		
				
				$result_api  = $this->callSMSEmailAPI($params_SE);
				
				$response_arr['errorcode']		=	1;
				$response_arr['msg']			=	"Authorized/Top Premium Category Found";
				$response_arr['email_text']		=	$email_text;
			}
			else
			{
				$response_arr['errorcode']	=	0;
				$response_arr['msg']		=	"Valid Category Found";
			}
		}
		else
		{
			$response_arr['errorcode']	=	0;
			$response_arr['msg']	  	=	"Valid Category Found";
		}
		return $response_arr;
	}
	
	function get_jdbox_url()
	{
		require_once('../library/configclass.php');
		$configclassobj= new configclass();
		$urldetails		=	$configclassobj->get_url(urldecode($this->data_city));
		return	$urldetails['jdbox_service_url'];
	}
	
 	function send_die_message($msg)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['message'] = $msg;
		return $die_msg_arr;
	}
	function sanitize($str)
	{
		$str = preg_replace('/[@&-.,_)(\s+]+/',' ',$str);
		$str = preg_replace("/[^a-zA-Z0-9\s]+/",'',$str);
		$str = preg_replace('/\\\+/i','',$str);
		$str = preg_replace('/\s\s+/',' ',$str);
		return trim($str);
	}	
	function get_curl_data($url,$param=array())
	{
		$ch 		= curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST      ,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS ,$param);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$resmsg = curl_exec($ch);
		curl_close($ch);
		return $resmsg;	
	}
	
	private function get_changes_fields($old_values,$new_values)
	{
		$arr_diff	=array();
		$valid_filed_array = array('companyname','state','building_name','area','city','street','landmark','stdcode','mobile','landline','email','pincode','working_time_start','working_time_end','year_establishment','website','tollfree','fax','fbMedia','othercity_number','catidlineage');
		foreach($old_values AS $field=>$value) 
		{
			if(trim(trim(stripslashes(strtolower($new_values[$field]))),',') != trim(trim(stripslashes(strtolower($value))),',') && in_array($field,$valid_filed_array))
			{	
				if($field	==	'payment_type')
				{
					if(trim(trim(strtolower($new_values[$field])),'~') != trim(trim(strtolower($value)),'~'))
						$arr_diff[$field] = $value;
				}
				elseif($field	==	'fb_prefered_language')
				{
					if($new_values[$field] != $value)
					{	
						if(!((trim($new_values[$field]) =='' && $value=='0') || ($new_values[$field] =='0' && trim($value)=='')))
							$arr_diff[$field] = $value;
					}
				}
				elseif($field	==	'catidlineage')
				{
					$arr_return_values 			= $this->get_category_comparision($value, $new_values[$field]);
					if(is_array($arr_return_values) && count($arr_return_values)>0)
					{
						if(!empty($arr_return_values['invalid_catids']))
						{
							$invalid_cat_arr	=	array_filter(explode('|~|',$arr_return_values['invalid_catids']));
							if(is_array($invalid_cat_arr) && count($invalid_cat_arr)>0)
							{
								$arr_diff[$field] = $value;
							}
						}
					}
				}
				elseif($field	==	'working_time_start' || $field	==	'working_time_end')
				{
					$working_time_old_arr	= array_filter(explode(',',trim(trim($value,'-'),',')));	
					$working_time_new_arr	= array_filter(explode(',',trim(trim($new_values[$field],'-'),',')));
					
					$working_tm_index=0;
					foreach($working_time_old_arr as $key_working_time_old_arr => $val_working_time_old_arr)
					{
						$working_invalid_flag	=	0;
						if(trim($val_working_time_old_arr,'-') != trim($working_time_new_arr[$working_tm_index],'-'))
						{
							$working_invalid_flag	=	1;
							break;
						}
						$working_tm_index++;
					}
					if($working_invalid_flag	==	1)
						$arr_diff[$field] = $value;
				}
				elseif($field	==	'mobile' || $field	==	'landline' || $field	==	'tollfree')
				{
					$contact_details_old_arr	= array_filter(explode(',',$value));	
					$contact_details_new_arr	= array_filter(explode(',',$new_values[$field]));	
					
					$contact_details_arr =array();
					$contact_details_arr1	=	array_diff($contact_details_old_arr,$contact_details_new_arr);
					$contact_details_arr2	=	array_diff($contact_details_new_arr,$contact_details_old_arr);
					$contact_details_arr	=	array_merge($contact_details_arr1,$contact_details_arr2);
					
					if(count($contact_details_arr)>0)
						$arr_diff[$field] = $value;
				}
				else
					$arr_diff[$field] = $value;
			}
		}
		
		$arr_invalid_field_names = array_keys($arr_diff);
		return $arr_invalid_field_names;
	}
	
	private function get_category_comparision($orig_catidlineage, $user_catidlineage)
	{
		$orig_cats		= array();
		$user_cats1					= $user_catidlineage;
		
		$user_cats 					= array_values(array_filter(explode(",",str_replace("/","",$user_cats1))));
		$orig_cats 					= array_values(array_filter(explode(",",str_replace("/","",$orig_catidlineage))));
		$orig_categories 			= array_intersect($orig_cats, $user_cats);	// merged catids
		
		$orig_invalid_categories1 	= array_diff($orig_cats, $user_cats);
		$orig_invalid_categories2 	= array_diff($user_cats, $orig_cats);
		$orig_invalid_categories	= array_merge($orig_invalid_categories1,$orig_invalid_categories2);
		
		$arr_removed_catids			= $orig_invalid_categories;
		$arr_added_catids			= array_diff($user_cats, $orig_cats);	// cat in tme catids but not in orig catids

		$added_catids				= implode("|~|",$arr_added_catids);
		$removed_catids				= implode("|~|",$arr_removed_catids);

		$str_orig_valid_catids 		= "";
		$str_orig_invalid_catids	= "";

		if(!empty($orig_categories) && is_array($orig_categories)){
			$str_orig_valid_catids 	= implode("|~|",$orig_categories);
		}

		if(!empty($orig_invalid_categories) && is_array($orig_invalid_categories))
		{
			$str_orig_invalid_catids 	= implode("|~|",$orig_invalid_categories);
		}
		return array("valid_catids"=>$str_orig_valid_catids, "invalid_catids"=>$str_orig_invalid_catids,"added_catids"=>$added_catids,"removed_catids"=>$removed_catids,"clean_orig_cats"=>$orig_cats, "clean_user_cats"=>$user_cats);
	}
	public function get_nearby_city()
	{
		global $params;
		if($params['trace'] == 1)
		{
			echo "<hr>";
			echo "Purpose : To get near by cities \n";
			echo "<hr>";
			echo "Input Parameters : \n";
			print_r($params);
			echo "<hr>";
		}
		
		$response_arr		=	array();
		
		$sql_latlong	=	"SELECT cityname,data_city,latitude_city,longitude_city FROM tbl_city_master WHERE cityname='".addslashes($params['city'])."' AND display_flag=1 LIMIT 1";
		$res_latlong = parent::execQuery($sql_latlong, $this->conn); 
		if(parent::numRows($res_latlong)>0)
		{
			$rows_get_city = parent::fetchData($res_latlong); 			
			$sql_getinfo	=	"SELECT cityname,data_city,latitude_city,longitude_city,type_flag, ROUND(( 6371 * acos ( cos ( radians('".$rows_get_city['latitude_city']."') ) * cos( radians( latitude_city ) ) * cos( radians( longitude_city ) - radians('".$rows_get_city['longitude_city']."') ) + sin ( radians('".$rows_get_city['latitude_city']."') ) * sin( radians( latitude_city ) ) ) ),2) AS `distance` FROM tbl_city_master WHERE display_flag=1 AND type_flag in (0,1) and cityname!='sveda' AND cityname !='".addslashes($params['city'])."'  ORDER BY distance ASC LIMIT 100";
			$res_getinfo = parent::execQuery($sql_getinfo, $this->conn); 
			$numRows = parent::numRows($res_getinfo);
			if(parent::numRows($res_getinfo)>0)
			{
				$records_arr	=	array();
				while($rows_getinfo	=	parent::fetchData($res_getinfo))
				{
					$records_arr[]	=	$rows_getinfo;
				}
			}
			$output['numRows'] 				=   $numRows; 
			$output['result'] 				=   $records_arr;
			$output['error']['message'] 		=  "success";	
		}
		else
		{
			$output['numRows'] 				=   parent::numRows($res_latlong);
			$output['result'] 				=   $records_arr;
			$output['error']['message'] 	=  "city not exists";	
		}
		
		if($params['trace'] == 1)
		{
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}		
		return $output;	
	}
	public function get_review_rating_callcnt()
	{
		global $params;
		if($params['trace'] == 1)
		{
			echo "<hr>";
			echo "Purpose : To get review rating callcnt \n";
			echo "<hr>";
			echo "Input Parameters : \n";
			print_r($params);
			echo "<hr>";
		}
		
		$sql = "select review_count,photo_count,parentid from tbl_video_photo_review_count_final where parentid ='".$params['parentid']."' GROUP BY parentid";
		$res = parent::execQuery($sql,$this->conn_iro); 
		if($res && mysql_num_rows($res) > 0)
		{	
			while($row = mysql_fetch_assoc($res))
			{
				//$review_count	=	$row['review_count'];
				$photo_count	=	$row['photo_count'] ;				
			}
		}
		$compdata	=	$this->company_data($params['parentid']);
		$company_callcnt = $compdata['company_callcnt'];
		
		/*if(empty($review_count))
		{
			$review_count	=	 '0';			
		}*/
		if(empty($photo_count))
		{
			$photo_count	=	 '0';			
		}
		if(empty($compdata['company_callcnt']))
		{
			$company_callcnt	=	 '0';			
		}
		  
		$curl = "http://192.168.20.101/10aug2016/review_rating.php?case=multiple_ratings&docid=".$compdata['docid'];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curl);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);		 
		curl_close($ch);
		
		$data_arr = json_decode($data,true); 
		
		$review_count = $data_arr['Results'][$compdata['docid']]['review'];	
		$rating_count = $data_arr['Results'][$compdata['docid']]['rating'];		

		
		  
		$review_rating_callcnt_arr['parentid'] 	= $params['parentid'];
		$review_rating_callcnt_arr['data_city'] = $params['data_city'];
		$review_rating_callcnt_arr['call_count'] = $company_callcnt;
		$review_rating_callcnt_arr['review_count'] = $review_count;
		$review_rating_callcnt_arr['rating_count'] = $rating_count;
		$review_rating_callcnt_arr['photo_count'] = $photo_count;
		
		$output['result']					=	$review_rating_callcnt_arr;
		$output['error']['code'] 			=  "0";	
		$output['error']['message'] 		=  "success";	
		if($params['trace'] == 1)
		{
			echo "<br>".$sql;
			echo "<br><br>";print_r($output);
		}	
		return $output;
	}
	
	private function callSMSEmailAPI($params)
	{
		$curl_url = SMS_EMAIL_LB_IP."/insert.php";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curl_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response  = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
	
	private function remove_special_character($str)
	{
		$string    =    str_replace('-',' ',$str);
		$a	= preg_replace('/[^A-Za-z0-9" "\-]/', '', $string);
		return $a;
	}
	
	private function getSingular($str='')
	{
		$s = array();
		$t = explode(' ',$str);
		$e = array('shoes'=>'shoe','glasses'=>'glass','mattresses'=>'mattress','mattress'=>'mattress','watches'=>'watch','access'=>'access');
		$r = array('ss'=>'ss','os'=>'o','ies'=>'y','xes'=>'x','oes'=>'o','ies'=>'y','ves'=>'f','s'=>'');
		foreach($t as $v){
			if(strlen($v)>=4){
				$f = false;
				foreach(array_keys($r) as $k){
					if(substr($v,(strlen($k)*-1))!=$k){
						continue;
					}
					else{
						$f = true;
						if(array_key_exists($v,$e))
							$s[] = $e[$v];
						else
							$s[] = substr($v,0,strlen($v)-strlen($k)).$r[$k];

						break;
					}
				}
				if(!$f){
					$s[] = $v;
				}
			}
			else{
				$s[] = $v;
			}
		}
		return (!empty($s)) ? implode(' ',$s) : $str;
	}
		
}
?>
