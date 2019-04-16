<?php

class saveCompanyDataClass extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $dbConIdc    	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	

	var  $catsearch	= null;
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	var $omni_duration;	
	function __construct($params)
	{	
		$this->params = $params;
		/* Code for companymasterclass logic starts */
		if($this->params['is_remote'] == 'REMOTE')
		{
			$this->is_split = FALSE;	 // when split table goes live then make it TRUE		
		}
		else
		{
			$this->is_split = FALSE;			
		}
		$result_msg_arr=array();
		
		if(trim($this->params['action']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Action Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->action  = $this->params['action']; 

		if($this->action!='1' && $this->action!='8' && $this->action!='9'&& $this->action!='10' && $this->action!='11'){ 
			if(trim($this->params['parentid']) == "")
			{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Parentid Missing"; 
					echo json_encode($result_msg_arr);exit;
			}
			else
				$this->parentid  = $this->params['parentid']; 
			
			if(trim($this->params['version']) == "")
			{
				/*$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "version Missing";
				echo json_encode($result_msg_arr);exit;*/
			}
			else
				$this->version  = $this->params['version']; 

		}
		
		if( trim($this->params['genio_lite_daemon']) )
		{
			$this->genio_lite_daemon  = $this->params['genio_lite_daemon']; 
		}	
		
		if( trim($this->params['manage_campaign']) )
		{
			$this->manage_campaign  = $this->params['manage_campaign']; 
		}
							
		if(trim($this->params['module']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->module  = $this->params['module']; 

		
		
		if(trim($this->params['data_city']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Data City Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->data_city  = $this->params['data_city']; 
		if(trim($this->params['ucode']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "User Code Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->ucode  = $this->params['ucode']; 
		if(trim($this->params['uname']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "User Name Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->uname  = $this->params['uname'];  
		
		if( trim($this->params['source_uname']) )
		{
			$this->source_uname  = $this->params['source_uname']; 
		}
		
		if( trim($this->params['source_ucode']) )
		{
			$this->source_ucode  = $this->params['source_ucode']; 
		}
		

		$status=$this->setServers();
		if($status==-1)
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Missing";
			return $result_msg_arr;
		}
		
		$this->companyClass_obj  = new companyClass();			
		$urls=$this->getCurlURL();
		$this->cs_url	 = $urls['url'];
		$this->jdbox_url = $urls['jdbox_url'];
		$this->city_indicator	 = $urls['city_indicator'];
		
		
	} 
		
	
	function setServers()
	{	
		global $db;

		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$this->dbConIro = $db[$data_city]['iro']['master'];
		$this->conn_idc = $db[$data_city]['idc']['master'];
		$this->conn_djds= $db[$data_city]['d_jds']['master'];
		
		switch(strtolower($this->module))
		{
			case 'cs':
			$this->conn_temp = $db[$data_city]['d_jds']['master'];
			$this->conn_main = $db[$data_city]['d_jds']['master'];
			$this->conn_finance_temp = $db[$data_city]['fin']['master'];
			$this->conn_finance = $db[$data_city]['fin']['master'];
			$this->conn_to_idc = $db[$data_city]['fin']['master'];
			break;
			case 'tme':
		
			$this->conn_temp = $db[$data_city]['tme_jds']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			$this->conn_to_idc = $db[$data_city]['idc']['master'];

			break;
			case 'me':
			$this->conn_temp = $db[$data_city]['idc']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			$this->conn_to_idc = $db[$data_city]['idc']['master'];
			break;
			case 'jda':
			//$this->conn_temp = 
			break;
			default:
			return -1;
			break;
		}

	}
		function mysql_real_escape_custom($string=null){
			$escapedstring=addslashes($string);
			return $escapedstring;
		}
		function checkJson($string) {
		 json_decode($string);
		 return (json_last_error() == JSON_ERROR_NONE);
		}
		function saveCompanyDetails(){
			

			$comp_insert_arr 	= array();
			$comp_upd_arr 	 	= array();
			$misc_tag_flag		= 0;
			
			$tbl_arr=json_decode($this->params['tbl'],1);
			$count=0;
			foreach ($tbl_arr as $keyname => $tbl) {  

			if(!$this->checkJson($this->params['fields'])){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Not A valid Json";  
				return $result_msg_arr; 
			}
			$arr_to_upt=json_decode($this->params['fields'],1);

			$api_ip_arr = array('10033648','10000760','10015427','10015416','10065163');
			$this->live_comp = 0;
			if(in_array($this->ucode,$api_ip_arr)){
				$this->live_comp = 1;
			}
			if(in_array($this->source_ucode,$api_ip_arr)){
				$this->live_comp = 1;
			}

			//echo "<pre>";print_r($arr_to_upt);die;
			$social_mobile	=	$arr_to_upt['tbl_companymaster_generalinfo']['mobile'];
			$arr_to_upt=$arr_to_upt[$tbl];  
			
			if( $tbl == 'tbl_companymaster_generalinfo' )
				$existing_geocode_data = $this -> getExistingGeneralInfo($arr_to_upt['parentid']);
			
			if($arr_to_upt['parentid'])
			{
				$sql_sphinx = "SELECT parentid,sphinx_id,docid,data_city FROM tbl_id_generator WHERE parentid ='".$arr_to_upt['parentid']."'";
				$res_sphinx = parent::execQuery($sql_sphinx, $this->dbConIro);
				if($res_sphinx &&  mysql_num_rows($res_sphinx)>0)
				{
					$row_sphinx = mysql_fetch_assoc($res_sphinx);
					if($row_sphinx['sphinx_id']>0)
					{
						$arr_to_upt['sphinx_id'] = $row_sphinx['sphinx_id'];
					}
					else
					{
						$result_msg_arr['error']['code'] = 1;
						$result_msg_arr['error']['msg'] = "sphinx id not found in id generator";
						return $result_msg_arr;
					}
				}
			}
			
			$accepted_tbl=array('tbl_companymaster_generalinfo','tbl_companymaster_extradetails');
			if(!in_array($tbl, $accepted_tbl)){  

				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Please Pass Only Accepted Tables";
				return $result_msg_arr;
			}
			if(!in_array($tbl, $accepted_tbl)){

				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Please Pass Only Accepted Tables";
				return $result_msg_arr;
			}
				$db='db_iro';
				$getcolumns="SELECT `COLUMN_NAME` 	
					FROM `INFORMATION_SCHEMA`.`COLUMNS` 
					WHERE `TABLE_SCHEMA`='".$db."' 
					    AND `TABLE_NAME`='".$tbl."';";

				$res_temp = parent::execQuery($getcolumns, $this->dbConIro);
		 		$columns=array();
		 		if($res_temp && mysql_num_rows($res_temp)>0)
		 		{
		 			while($rowcolumn=mysql_fetch_assoc($res_temp)){
		 				$columns[]=$rowcolumn['COLUMN_NAME'];
		 			}

		 			
				}
				
				foreach ($arr_to_upt as $key => $value) {
					if(!in_array($key, $columns)) 
					{
						$result_msg_arr['error']['code'] = 1;
						$result_msg_arr['error']['msg'] = "Please Pass Only Valid Column In ".$tbl. " column name :: ".$key;
						$result_msg_arr['error']['passed_key'] = json_encode(array_keys($arr_to_upt));
						$result_msg_arr['error']['column_key'] = json_encode($columns);
						return $result_msg_arr;
					}
				}

				$parentid=$arr_to_upt['parentid'];
				$columns = implode(", ",array_keys($arr_to_upt));
				
				$sqlExtraDetailsInfo = "SELECT national_catidlineage_search,hotcategory FROM tbl_companymaster_extradetails WHERE parentid = '".$parentid."'";
				//$resExtraDetailsInfo =  parent::execQuery($sqlExtraDetailsInfo, $this->dbConIro);
				$comp_params = array();
				$comp_params['data_city'] 	= $this->data_city;
				$comp_params['table'] 		= 'extra_det_id';		
				$comp_params['parentid'] 	= $parentid;
				$comp_params['fields']		= 'national_catidlineage_search,hotcategory';
				$comp_params['action']		= 'fetchdata';
				$comp_params['page']		= 'saveCompanyDataClass';
				$comp_params['skip_log']	= 1;

				$comp_api_arr	= array();
				$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
				if($comp_api_res!=''){
					$comp_api_arr 	= json_decode($comp_api_res,TRUE);
				}

				//if($resExtraDetailsInfo && parent::numRows($resExtraDetailsInfo)>0){
				if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['extra_det_id']=='1')
					{
					$this-> existing_contract = 1;
					//$row_extradetails 	= parent::fetchData($resExtraDetailsInfo);
					$row_extradetails	=	$comp_api_arr['results']['data'][$parentid];

					$old_ncatsrch 		= trim($row_extradetails['national_catidlineage_search']);
					$old_hotcat		    = trim($row_extradetails['hotcategory']);
				}
				
				// to get data as data is processed and inserted so fetching the same.
				
				$sqlforvalues="select $columns from $tbl where parentid='".$parentid."'";

				$forvaluesres=parent::execQuery($sqlforvalues, $this->conn_idc);
				if($forvaluesres && mysql_num_rows($forvaluesres) && strtolower(trim($this->params['ucode'])) != "selfsignup")
				{
						
						$forvaluesrow=mysql_fetch_assoc($forvaluesres);
						$arr_to_upt_new=$forvaluesrow;
					
				}
				else{
					if(strtolower(trim($this->params['ucode'])) == "selfsignup" && count($arr_to_upt)>0 )
					{
						$arr_to_upt_new = $arr_to_upt; 
					}
					else
					{
						$subject='No entry in IDC  ';
						foreach($_SERVER as $key=>$val)
			 			$serverdetiail.="<br>".$key.'=>'.$val;
			 			$headers  = 'MIME-Version: 1.0' . "\r\n";
			 			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			 			$headers .= 'From: apache@justdial.com';
			 			mail ('rajakkal.ganesh@justdial.com' , $subject.$parentid,$subject);
			 			 	$result_msg_arr['error']['code'] = 1;
			 				$result_msg_arr['error']['msg'] = "No Entry In IDC!"; 
			 			 	return $result_msg_arr ; 	
				  }
				}

				$arr_insert_local_table = array();
				$arr_update_local_table = array();
				
				unset($arr_to_upt_new['misc_flag']);
				foreach ($arr_to_upt_new as $key => $value) {	
					
					if(($tbl == 'tbl_companymaster_generalinfo') )
					{
						if($key == 'companyname')
							$companyname = trim($value);
						
						if($key == 'mobile')
							$mobile = trim($value);	
						
						if($key == 'landline')
							$landline = trim($value);	
						
						if($key == 'area')
							$area = trim($value);	
							
						if($key == 'pincode')
							$pincode = trim($value);	
							
						if($key == 'latitude')
							$latitude = trim($value);
							
						if($key == 'longitude')
							$longitude = trim($value);
						
						if($key == 'contact_person')
							$contact_person = trim($value);
								
					}
					
					if( $tbl == 'tbl_companymaster_extradetails' && $this->genio_lite_daemon && ($key == 'freeze' ||  $key == 'mask') )
					{
						$value = 0;
					}
									
					$arr_insert_local_table[] = $key."='".addslashes(stripslashes($value))."'";
					$comp_insert_arr[$tbl][$key]	  =	$key."='".addslashes(stripslashes($value))."'";
					
					if($key != 'nationalid' && $key != 'sphinx_id'  && $key != 'parentid' && $key != 'createdby' && $key != 'createdtime' && $key != 'original_creator' && $key != 'original_date')
					{
						$arr_update_local_table[] = $key."='".addslashes(stripslashes($value))."'";
						$comp_upd_arr[$tbl][$key] 	  = $key."='".addslashes(stripslashes($value))."'";
					}
					
					if(($tbl == 'tbl_companymaster_extradetails') && ($key == 'companyname')){
						$compnameval = trim($value);
						$compinitial = substr($compnameval, 0, 3);
						if(strtolower($compinitial) == 'zxy' || strtolower($compinitial) == 'zqvd'){	
							$misckey 		= "misc_flag";
							$misc_flag_val 	= 1;
							if($this->live_comp ==1){								
								$misc_tag_flag = 1;
							}
							else{
							$arr_insert_local_table[] = $misckey."=misc_flag + if(misc_flag&".$misc_flag_val."=".$misc_flag_val.",0,".$misc_flag_val.")";
							$arr_update_local_table[] = $misckey."=misc_flag + if(misc_flag&".$misc_flag_val."=".$misc_flag_val.",0,".$misc_flag_val.")";
						}
					}
				}
				}
				
				if(!empty($arr_insert_local_table) && is_array($arr_insert_local_table) && count($arr_insert_local_table) > 0){
					$sql_str_local		=	implode(",",$arr_insert_local_table);
				}
				
				if(!empty($arr_update_local_table) && is_array($arr_update_local_table) && count($arr_update_local_table) > 0){
					$sql_str_updt_local	=	implode(",",$arr_update_local_table);
				}
			
				$query_insert_local	=	"INSERT INTO ".$tbl." SET ";
				
				$query_on_dup_local	=	" ON DUPLICATE KEY UPDATE ";

				$query_insert_local  .=	$sql_str_local.$query_on_dup_local.$sql_str_updt_local;
			
			 	$obj_log	= new contractLog($parentid, $this->module,  $this->ucode." (".$this->source_ucode.")" , $this->data_city); 
			 	//$sql = "INSERT INTO ".$tbl." ($columns) VALUES ('$values') on duplicate key update $for_on_duplicate";
			 	$count++;
			 					 
			 	if($this->genio_lite_daemon)
			 	 $res_ins[$count]['res_idc'] = parent::execQuery($query_insert_local, $this->conn_main);
		
			 	if($this->live_comp!=1){
					  $res_ins[$count]['res'] = parent::execQuery($query_insert_local, $this->dbConIro);
					  $res_ins[$count]['query_run'] = $query_insert_local;
			 		  $res_ins[$count]['table'] = $tbl;
				}			 	 
			 	
			 	unset($obj_log);
			 	
			 	if( $tbl == 'tbl_companymaster_generalinfo' )
				$new_geocode_data = $this -> getExistingGeneralInfo($parentid);
			 	
		 	}
		 	$comp_update_arr = array();
			$update_data	 = array();			
			//~ echo "<pre>insert arr ---";print_r($comp_insert_arr);
			//~ echo "<pre>update arr ---";print_r($comp_upd_arr);
			
			$gen_table_arr	 = array();
			$extra_table_arr = array();
			
			if( $existing_geocode_data['latitude'] != $new_geocode_data['latitude'] || $existing_geocode_data['longitude'] != $new_geocode_data['longitude'] || $existing_geocode_data['geocode_accuracy_level'] != $new_geocode_data['geocode_accuracy_level'] )
			{
				$insert_geocode_log = "INSERT INTO tbl_geocode_update_trail 
										SET
									parentid	=	'".$parentid."',
									companyname	=	'".addslashes($new_geocode_data['companyname'])."',
									data_city	=	'".$this->data_city."',
									latitude_old	=	'".$existing_geocode_data['latitude']."',
									longitude_old	=	'".$existing_geocode_data['longitude']."',
									latitude_new	=	'".$new_geocode_data['latitude']."',
									longitude_new	=	'".$new_geocode_data['longitude']."',
									geocode_accuracy_level_old	=	'".$existing_geocode_data['geocode_accuracy_level']."',
									geocode_accuracy_level_new	=	'".$new_geocode_data['geocode_accuracy_level']."',
									source	=	'geniolite',
									paid	=	'1',
									updated_by	=	'".$this->source_ucode."',
									update_time	=	now()";
				 $res_geocode_log =  parent::execQuery($insert_geocode_log, $this->conn_djds);						
		 	}
			
			$arr_insert_gen 	= array();
			$arr_update_gen 	= array();
			$arr_insert_extra 	= array();			
			$arr_update_extra 	= array();
			
			$arr_insert_gen 	= $comp_insert_arr['tbl_companymaster_generalinfo'];
			$arr_update_gen 	= $comp_upd_arr['tbl_companymaster_generalinfo'];
			$arr_insert_extra 	= $comp_insert_arr['tbl_companymaster_extradetails'];
			$arr_update_extra 	= $comp_upd_arr['tbl_companymaster_extradetails'];
			
			if(!empty($arr_insert_gen) && is_array($arr_insert_gen) && count($arr_insert_gen) > 0){
				foreach($arr_insert_gen as $index=>$gen_info_column){
					$gen_col_arr = array();
					
					if($gen_info_column!=''){
						$gen_col_arr =	explode("=",$gen_info_column);
					}
					$gen_col_name 	='';
					$gen_col_val 	='';				
					$gen_col_name 		=	$gen_col_arr['0'];
					$gen_col_val 		=	$gen_col_arr['1'];
					if($gen_col_name!=''){
						$gen_table_arr[$gen_col_name] = trim($gen_col_val,"''");
					}
				}
			}
			if(!empty($arr_update_gen) && is_array($arr_update_gen) && count($arr_update_gen) > 0){
				foreach($arr_update_gen as $index1=>$gen_upd_column){
					$gen_col_arr = array();
					if($gen_upd_column!=''){
						$gen_col_arr =	explode("=",$gen_upd_column);
					}
					$gen_col_name 	='';
					$gen_col_val 	='';				
					$gen_col_name 		=	$gen_col_arr['0'];
					$gen_col_val 		=	$gen_col_arr['1'];
					
					if(!array_key_exists($gen_col_name,$gen_table_arr)){
						$gen_table_arr[$gen_col_name] = trim($gen_col_val,"''");		
					}
				}
			}
			$bitwise_arr = array('type_flag','iro_type_flag','website_type_flag','businesstags','misc_flag','flags');			
			
			
			$arr_to_extra = array();
			$arr_to_extra = json_decode($this->params['fields'],1);
						
			if(!empty($arr_insert_extra) && is_array($arr_insert_extra) && count($arr_insert_extra) > 0){
				foreach($arr_insert_extra as $idx=>$exta_info_column){
					$extra_col_arr = array();
					
					if($exta_info_column!=''){
						$extra_col_arr =	explode("=",$exta_info_column);
					}
					$extra_col_name 	='';
					$extra_col_val 		='';				
					$extra_col_name 	=	$extra_col_arr['0'];
					$extra_col_val 		=	$extra_col_arr['1'];
					
					//$post_arr['type_flag']['set']; 
					
					if(in_array(strtolower($extra_col_name),$bitwise_arr)){
						if(is_array($arr_to_extra['tbl_companymaster_extradetails'][$extra_col_name])){
							if($arr_to_extra['tbl_companymaster_extradetails'][$extra_col_name]['set']!='' || $arr_to_extra['tbl_companymaster_extradetails'][$extra_col_name]['unset']!=''){
								//echo "<br>inside ".$extra_col_name;
								$extra_table_arr[$extra_col_name] = $arr_to_extra['tbl_companymaster_extradetails'][$extra_col_name];
							}
						}											
					}
					else if($extra_col_name!=''){
						$extra_table_arr[$extra_col_name] = trim($extra_col_val,"''");	
					}		
				}
			}
			if(!empty($arr_update_extra) && is_array($arr_update_extra) && count($arr_update_extra) > 0){
				foreach($arr_update_extra as $idx1=>$exta_upd_column){
					$extra_col_arr = array();
					if($exta_upd_column!=''){
						$extra_col_arr =	explode("=",$exta_upd_column);
					}
					$extra_col_name 	='';
					$extra_col_val 		='';				
					$extra_col_name 	=	$extra_col_arr['0'];
					$extra_col_val 		=	$extra_col_arr['1'];
					
					if(!array_key_exists($extra_col_name,$extra_table_arr)){
						if(in_array(strtolower($extra_col_name),$bitwise_arr)){							
							if(is_array($arr_to_extra['tbl_companymaster_extradetails'][$extra_col_name])){
								if($arr_to_extra['tbl_companymaster_extradetails'][$extra_col_name]['set']!='' || $arr_to_extra['tbl_companymaster_extradetails'][$extra_col_name]['unset']!=''){
									$extra_table_arr[$extra_col_name] = $arr_to_extra['tbl_companymaster_extradetails'][$extra_col_name];
								}
							}
						}
						else if($extra_col_name!=''){
							$extra_table_arr[$extra_col_name] = trim($extra_col_val,"''");
						}		
					}	
				}
			}			
			if($misc_tag_flag == 1){
				$extra_table_arr['misc_flag']['set'] = '1';
			}
			$update_data['gen_info_id'] 	= $gen_table_arr;
			$update_data['extra_det_id'] 	= $extra_table_arr;
			
			$comp_update_arr['usrid']		=$this->ucode;
			$comp_update_arr['usrnm'] 		=$this->uname;
			$comp_update_arr['data_city'] 	=$this->data_city;
			$comp_update_arr['parentid'] 	=$arr_to_extra['tbl_companymaster_extradetails']['parentid'];
			$comp_update_arr['rsrc'] 	 	=$this->params['module'];			
			
			$comp_update_arr['update_data'] = json_encode($update_data);
			$comp_update_arr['page'] 		= 'insertLiveClass';
			$comp_update_arr['action'] 		= 'updatedata';
			
			/* echo "<pre>final call ----";print_r($extra_table_arr);
			echo "<pre>final call ----";print_r($comp_update_arr); */
			
			$comp_upd_res 	= '';
			$comp_upd_arr 	= array();			
			if($this->live_comp == 1){
				$comp_upd_res	=	$this->companyClass_obj->getCompanyInfo($comp_update_arr);				
				
				if($comp_upd_res!=''){
					$comp_upd_arr = 	json_decode($comp_upd_res,1);
				}				
				$count_comp_api = 1;
				if($comp_upd_arr['errors']['code'] =='0' ){					
					$res_ins[$count_comp_api]['res'] = '1';
				}
				else{
					if($comp_upd_arr['errors']['msg']['0'] !=''){
						$error_msg_api =  $comp_upd_arr['errors']['msg']['0'];
					}
					else{
						$error_msg_api =  $comp_upd_arr['error']['msg']['0'];
					}
					$res_ins[$count_comp_api]['msg']	= $error_msg_api;									
					$res_ins[$count_comp_api]['error'] 	= '1';
				}
			}			
			
			
			if( is_array($res_ins) && count($res_ins) > 0 && $this->genio_lite_daemon )
			{
				
				$row_no_email_reason = $this -> fetchEmailNotPresentReason($parentid);
				
				if(count($row_no_email_reason) > 0)
				{
					$narration_params_arr = Array();
					$narration_params_arr['parentid']	 	=  $parentid;
					$narration_params_arr['data_city'] 		=  $this->data_city;
					$narration_params_arr['action'] 		=  2;
					$narration_params_arr['ucode']     		=  $this->source_ucode;
					$narration_params_arr['uname']     		=  $this->source_uname;
					$narration_params_arr['module']			=  'geniolite';
					
					$narration  = "\n"."Source : GenioLite "."\n"."Reason Given For No Email Id : ".$row_no_email_reason['reason']."\n"."Emp ID : ".$row_no_email_reason['updated_by']."\n"."Emp Name : ".$row_no_email_reason['updated_by_name'];
					$narration_params_arr['narration']		=  urlencode($narration);
					
					$narration_url=$this->cs_url."/api/fetch_update_narration.php";
					$res_narration_url = $this->curlCall($narration_url,$narration_params_arr,'post'); 
				}
				
				if($this->manage_campaign)
				{
					if($this-> existing_contract)
						$source_to_check = 'MANAGE_CAMPAIGN Edit';
					else
						$source_to_check = 'MANAGE_CAMPAIGN NEW';
				}
				else
				{
					if($this-> existing_contract)
						$source_to_check = 'ME Data GenioLite_Edit';
					else
						$source_to_check = 'ME Data GenioLite_New';
				}   
				
				$params_arr = Array();
				$params_arr['parentid']	 		=	$parentid;
				$params_arr['data_city'] 		=	$this->data_city;
				$params_arr['rquest'] 			= 'insertCompanySource';
				$params_arr['universal_source'] = $source_to_check;
				$params_arr['subsource'] 	 	= $source_to_check;
				$params_arr['datesource'] 	 	= date("Y-m-d H:i:s");
				$params_arr['paid'] 			= '1';
				$params_arr['ucode']     		=  $this->source_ucode;
				$params_arr['uname']     		=  $this->source_uname;
				
				//$curl_url	=	'http://imteyazraja.jdsoftware.com/jdbox/services/attributes_temp_to_main.php';	
				$curl_url_source =	$this->jdbox_url.'services/location_api.php';
				$curl_url_res	 = $this->curlCall($curl_url_source,$params_arr,'post');
				
				$mobile_arr	  = array();
				$landline_arr = array();
				
				if($mobile != '')
				$mobile_arr = explode(",",$mobile);
				
				if($landline != '')
				$landline_arr = explode(",",$landline);
				
				$contact_details = array_merge($mobile_arr, $landline_arr);
				$contact_details = array_unique($contact_details);
				$contact_details = implode(",",$contact_details);
				
				
				$sql_tmesearch = "INSERT INTO tbl_tmesearch SET
									parentid 		= '".$parentid."',
									contractid 		= '".$parentid."',
									compname 		= '".addslashes(stripslashes($companyname))."',
									paidstatus 		= '1',
									pincode			= '".$pincode."',
									freez 			= '0', 
									mask 			= '0',
									mainsource 		= '".addslashes(stripslashes($row_source['sCode']))."',
									subsource 		= '".addslashes(stripslashes($row_source['sName']))."',
									datesource 		= NOW(),
									data_source		= '".addslashes(stripslashes($this->module))."',
									datasource_date = NOW(),
									contact_details = '".$contact_details."',
									data_city		= '".addslashes($this->data_city)."',
									area			= '".$area."',
									contact_person	= '".addslashes(stripslashes($contact_person))."',
									latitude		= '".$latitude."',
									longitude		= '".$longitude."',
									landline		= '".$landline."',
									mobile			= '".$mobile."'

									ON DUPLICATE KEY UPDATE

									contractid 		= '".$parentid."',
									compname 		= '".addslashes(stripslashes($companyname))."',
									paidstatus 		= '1',
									pincode			= '".$pincode."',
									freez 			= '0', 
									mask 			= '0',
									mainsource 		= '".addslashes(stripslashes($row_source['sCode']))."',
									subsource 		= '".addslashes(stripslashes($row_source['sName']))."',
									datesource 		= NOW(),
									data_source		= '".addslashes(stripslashes($this->module))."',
									datasource_date = NOW(),
									contact_details = '".$contact_details."',
									data_city		= '".addslashes($this->data_city)."',
									area			= '".$area."',
									contact_person	= '".addslashes(stripslashes($contact_person))."',
									latitude		= '".$latitude."',
									longitude		= '".$longitude."',
									landline		= '".$landline."',
									mobile			= '".$mobile."'";
									
				 $res_insert = parent::execQuery($sql_tmesearch, $this->conn_djds);
					 
				

			}
			
			//echo "<pre>";print_r($arr_to_upt);
			$curl_url =	$this->jdbox_url.'services/add_update_social.php';
			$social_params_arr = array();
			$social_params_arr['parentid'] =	$parentid;
			if($arr_to_upt['city']!=''){
				$city = $arr_to_upt['city'];
			}
			else{
				$city = $this->data_city;
			}
			//echo "<b>".$this->data_city."</b>";
			$social_params_arr['city'] 					= 	$city;
			$social_params_arr['data_city'] 			= 	$this->data_city;
			$social_params_arr['companyname'] 			=	$arr_to_upt['companyname'];
			$social_params_arr['mobile'] 				=	$social_mobile;

			$this->curlCall($curl_url,json_encode($social_params_arr),'json');
			
			
			/*********************calling attributes api here starts*******************************/
				
			
			if(isset($this->params['genio_lite_daemon']) && $this->params['genio_lite_daemon']!= ''){
				$this->flow_module = 'genio_lite';
				
				$params_arr_att = array();
				$params_arr_att['parentid']   = $parentid;
				$params_arr_att['data_city']  = $this->data_city;		
				$params_arr_att['module']     = 'ME';
				$params_arr_att['source']     = $this->flow_module;
				$params_arr_att['ucode']      = $this->ucode;
				$params_arr_att['uname']      = $this->uname;
				$params_arr_att['action']     = 'add_remove_attr';
				$curl_url_att =	$this->jdbox_url.'services/attributes_dealclose.php';
				//$curl_url_att = 'http://imteyazraja.jdsoftware.com/jdbox/services/attributes_dealclose.php';				
				$tempRes_att = $this->curlCall($curl_url_att,$params_arr_att);
				
				
				$params_arr = Array();
				$params_arr['parentid']	 =	$parentid;
				$params_arr['data_city'] =	$this->data_city;
				$params_arr['action'] 	 = 'temp_to_main';
				$params_arr['module']    = $this->flow_module;
				$params_arr['ucode']     =  $this->ucode;
				$params_arr['isDealClose']     =  1;
				//$curl_url	=	'http://imteyazraja.jdsoftware.com/jdbox/services/attributes_temp_to_main.php';	
				$curl_url =	$this->jdbox_url.'services/attributes_temp_to_main.php';
				$tempRes = $this->curlCall($curl_url,json_encode($params_arr),'json');
				$resultAttr     = json_decode($tempRes,true);	
				
			}
				

			//echo "temp to mainn---<pre>";print_r($resultAttr);
			/*********************calling attributes api here ends*******************************/
			
			

			
		 	$err=0;
		 	
		 	foreach ($res_ins as $reskey => $resvalue) {
		 			
		 		if(! $resvalue['res'] ){ 
		 			
		 			$result_msg_arr['error']['code'] = 1;
 					$result_msg_arr['error']['msg'] = "Error Occured!"; 
 					$result_msg_arr['data']['query'] = $resvalue['query_run'];  
 					
 					$failq="insert into dealcloselive_failed_query(failed_query,insert_time) values('".$this->mysql_real_escape_custom($resvalue['query_run'])."','".date("Y-m-d H:i:s")."')";
 					$resinsfailq = parent::execQuery($failq, $this->conn_idc);
 					$err=1;
				 }
				 if($this->live_comp == 1 && $resvalue['error']== 1 ){
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Error Occured!"; 
					$result_msg_arr['data']['query'] = $resvalue['msg'];  
				 }
		 	}
		 	if($err==1)
		 	{
		 		return $result_msg_arr ; 	 
		 	}
		 	else{

				$this->call_webapi($parentid);
				require_once('category_sendinfo_class.php');
				$imgParams['parentid']                      = $parentid;
				$imgParams['data_city']                     = $this->data_city;
				$imgParams['national_catidlineage_search'] 	= $old_ncatsrch;
				$imgParams['hotcategory']                   = $old_hotcat;
				$obj 	  = new category_sendinfo_class($imgParams);
				$data_arr = $obj->sendCatInfo();
			 	$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success!";
			 	return $result_msg_arr ; 	 
		 	}	 
		}
		
		function fetchEmailNotPresentReason($parentid)
		{
			$sql_reason = "SELECT * FROM online_regis.tbl_email_reason WHERE parentid ='".$parentid."'";
			$res_reason = parent::execQuery($sql_reason, $this->conn_to_idc);
			if($res_reason && mysql_num_rows($res_reason))
			{
				return mysql_fetch_assoc($res_reason);
			}
		}
		
		function getExistingGeneralInfo($parentid)
		{
			$comp_params = array();
			$comp_params['data_city'] 	= $this->data_city;
			$comp_params['table'] 		= 'gen_info_id,extra_det_id';		
			$comp_params['parentid'] 	= $parentid;
			$comp_params['fields']		= 'parentid,companyname,landmark,latitude,longitude,geocode_accuracy_level,';
			$comp_params['action']		= 'fetchdata';
			$comp_params['page']		= 'saveCompanyDataClass';
			$comp_params['skip_log']	= 1;
	
			$comp_api_res  	= '';
			$comp_api_arr	= array();
			$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
			if($comp_api_res!=''){
				$comp_api_arr 	= json_decode($comp_api_res,TRUE);
			}
			
			if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['gen_info_id']=='1')
			{
				$row_edit_data 	= $comp_api_arr['results']['data'][$parentid];
							
				return $row_edit_data;
				
			}
		}
		
		function call_webapi($parentid){
			$url=$this->cs_url."/web_services/curl_serverside.php";

			$params['parentid']=$parentid;
			$params['insta_activate']=1; 
			$params['ucode']=$this->ucode." (".$this->source_ucode.")";
			$params['uname']=$this->uname." (".$this->source_uname.")";
			$params['validationcode']='DEALCLOSE_LIVE'; 
			$params['city_indicator']=$this->city_indicator;
			$params['data_city']=$this->data_city;
			$res=$this->curlCall($url,$params,'post'); 
		}
		function curlCall($url,$data=null,$method='get'){
			$ch = curl_init();        
	        curl_setopt($ch, CURLOPT_URL, $url);
	        //curl_setopt($ch, CURLOPT_URL, $param['url']);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	        curl_setopt($ch, CURLOPT_TIMEOUT, 180);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
	        if($method=='post'){
	        	
	        	curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	        }
	        else if($method=='json'){
	        	$body = json_encode($data);
	        	curl_setopt($ch, CURLOPT_POST, true);
	        	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		    }
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$resultString = curl_exec($ch);
	        curl_close($ch); 
			return $resultString;
	}
		function for_on_duplicate($arr_to_upt){
			$con = mysql_connect($this->dbConIro[0], $this->dbConIro[1], $this->dbConIro[2]) ;
			$result = array_map(function($key, $value){
				if($key!='sphinx_id')
			    return "$key='".mysql_real_escape_string($value)."'"; 
			}, array_keys($arr_to_upt), array_values($arr_to_upt));

			return $result; 
		}
	
	private function getCurlURL()
	{
		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
		{
			$url = "http://ganeshrj.jdsoftware.com/csgenio/";
			$jdbox_url 				= "http://ganeshrj.jdsoftware.com/jdbox/";
			$city_indicator 		= "main_city";
		}
		else
		{
			switch(strtoupper($this->data_city))
			{
				case 'MUMBAI' :
					$url 					= "http://".MUMBAI_CS_API."/";
					$jdbox_url 				= "http://".MUMBAI_JDBOX_API."/";
					$city_indicator 		= "main_city";
					break;

				case 'AHMEDABAD' :
					$url 					= "http://".AHMEDABAD_CS_API."/";
					$jdbox_url 				= "http://".AHMEDABAD_JDBOX_API."/";
					$city_indicator = "main_city";
					break;

				case 'BANGALORE' :
					$url 					= "http://".BANGALORE_CS_API."/";
					$jdbox_url 				= "http://".BANGALORE_JDBOX_API."/";
					$city_indicator 		= "main_city";
					break;

				case 'CHENNAI' :
					$url 					= "http://".CHENNAI_CS_API."/";
					$jdbox_url 				= "http://".CHENNAI_JDBOX_API."/";
					$city_indicator		    = "main_city";
					break;

				case 'DELHI' :
					$url 					= "http://".DELHI_CS_API."/";
					$jdbox_url 				= "http://".DELHI_JDBOX_API."/";
					$city_indicator 		= "main_city";
					break;

				case 'HYDERABAD' :
					$url 					= "http://".HYDERABAD_CS_API."/";
					$jdbox_url 				= "http://".HYDERABAD_JDBOX_API."/";
					$city_indicator 		= "main_city";
					break;

				case 'KOLKATA' :
					$url 					= "http://".KOLKATA_CS_API."/";
					$jdbox_url 				= "http://".KOLKATA_JDBOX_API."/";
					$city_indicator 		= "main_city";
					break;

				case 'PUNE' :
					$url 					= "http://".PUNE_CS_API."/";
					$jdbox_url 				= "http://".PUNE_JDBOX_API."/";
					$city_indicator 		= "main_city";
					break;

				default:
					$url 					= "http://".REMOTE_CITIES_CS_API."/";
					$jdbox_url 				= "http://".REMOTE_CITIES_JDBOX_API."/";
					$city_indicator 		= "remote_city";
					break;
			}	
			
		}
		$urlArr['url'] 					= $url;
		$urlArr['jdbox_url'] 			= $jdbox_url;
		$urlArr['city_indicator'] 		= $city_indicator;
		return $urlArr;
	}

		
	}
?>
