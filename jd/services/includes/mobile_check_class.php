<?php
class mobile_check_class extends DB
{
	var  $conn_iro    	= null;	 
	var  $conn_idc    	= null;	
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $mobile		= null;
	var  $module		= null;
	var  $data_city		= null;
	
	
	function __construct($params)
	{	
		global $params;
		$module 			= trim($params['module']);
		$data_city 			= trim($params['data_city']); 	
		$rquest 			= trim($params['rquest']); 
 		if(trim($data_city)=='') {
			$message = "Data City is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(trim($module)=='') {
			$message = "Module is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}		
		if(trim($rquest)=='') {
			$message = "invalid request name.";
			echo json_encode($this->send_die_message($message));
			die();
		}		 
		$this->mobile  		= $params['mobile'];
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->rquest  	  	= $rquest;
		$this->setServers();
		$this->companyClass_obj  = new companyClass();		  	
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_idc    		= $db[$conn_city]['idc']['master'];
		$this->connCorp    		= $db['corporate'];		

	}	
	function fetch_mobile() {
		$func = $this->rquest;
		if((int)method_exists($this,$func) > 0)
			return $this->$func();
		else {
			$message = "invalid function";
			return json_encode($this->send_die_message($message));			
		}
	}
	
	function mobile_employee_check()
	{
		global $params;
		if($params['trace'] == 1)
		{	
			echo "<pre>";
			echo "Input Parameters : ";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		if(trim($this->mobile)=='') {
            $message = "mobile number(s) blank.";
            echo json_encode($this->send_die_message($message));
            die();
        }
		$arr_numbers = explode(",",$params['mobile']);
		$arr_numbers_final = Array();
		$arr_numbers1 = Array();
		$escape_numbers_arr = Array();
		if(isset($params['parentid']))
		{
			$exist_numbers = Array();
			$sql_edit = "SELECT parentid,mobile FROM db_iro.tbl_companymaster_generalinfo WHERE parentid = '".$params['parentid']."' AND data_city = '".$this->data_city."' LIMIT 1";
			//$res_edit 	= parent::execQuery($sql_edit, $this->conn_iro);
			//$num_rows = mysql_num_rows($res_edit);
			$comp_params = array();
			$comp_params['data_city'] 	= $this->data_city;
			$comp_params['table'] 		= 'gen_info_id';		
			$comp_params['parentid'] 	= $params['parentid'];
			$comp_params['fields']		= 'parentid,mobile';
			$comp_params['action']		= 'fetchdata';
			$comp_params['page']		= 'mobile_check_class';
			$comp_params['skip_log']	= 1;
			

			$comp_api_arr	= array();
			$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
			if($comp_api_res!=''){
				$comp_api_arr 	= json_decode($comp_api_res,TRUE);
			}
			/* echo "<pre>";print_r($comp_params);
			echo "<pre>";print_r($comp_api_arr); */
			if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['gen_info_id']=='1')
			{
				$row_edit 	= $comp_api_arr['results']['data'][$params['parentid']];				
				// if($num_rows >0) 
				// {
				//$row_edit = mysql_fetch_assoc($res_edit);
				$exist_numbers = explode(",",$row_edit['mobile']);				
			}			
			$arr_numbers_final = array_diff($arr_numbers,$exist_numbers); 
		}
		else
		{
			$arr_numbers_final = $arr_numbers;
		}
		/*$sql_escape = "SELECT mobile FROM online_regis1.tbl_escape_mobile  WHERE allow_flag=1 AND data_city = '".$this->data_city."'";
		$res_escape	= parent::execQuery($sql_escape, $this->conn_idc);		
		if($res_escape && mysql_num_rows($res_escape)>0){
			while($row_escape = mysql_fetch_assoc($res_escape)){
				$escape_numbers_arr[] = $row_escape['mobile'];					
			}
		}
		foreach($arr_numbers_final AS $key=>$mob){
			if(!in_array($mob,$escape_numbers_arr)){	
				$arr_numbers1[] = $mob;
			}
		}		
		$arr_numbers_final = $arr_numbers1;*/
		if(count($arr_numbers_final)>0)
		{
			foreach($arr_numbers_final AS $key=>$value) {
				$parentid_arr = array();
				//$curl_url ="http://accounts.justdial.com/hrmodule/employee/fetchMobileEmployee/".$value;
				$curl_url ="http://".SSO_IP."/hrmodule/employee/fetchMobileEmployee/".$value;
				$ch 		= curl_init();
				curl_setopt($ch, CURLOPT_URL, $curl_url);
				curl_setopt($ch, CURLOPT_POST,1);
				curl_setopt($ch, CURLOPT_POSTFIELDS,$param_api_gal);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				$resmsg = curl_exec($ch);
				curl_close($ch);
				
				$ret_array =  json_decode($resmsg,true);
				
				if(!empty($ret_array['data']['empcode'])) 
				{
					$sql = "SELECT * FROM online_regis1.tbl_mobile_contract_allow WHERE mobile='".$value."'";
					$res 	= parent::execQuery($sql, $this->conn_idc);
					
					if(mysql_num_rows($res)>0)
					{
						$row = mysql_fetch_assoc($res);
						if($row['allow_flag'] == 1){
							$msg[$value]['allow'] = "1";							
						}
						else{
							$msg[$value]['allow'] = "0";
						}						
					}
					else{
						$msg[$value]['allow'] = "0";						
					}					
				}
				else {
					$msg[$value]['allow'] = "1";
				}
				$param_arr = array();
				$param_arr['search'] 	= 	$value;
				$param_arr['dcity'] 	= 	$this->data_city;
				$param_arr['scity'] 	= 	$this->data_city;
				$param_arr['type'] 		=	'5';
				$param_arr['mod'] 		=	'jdbox';
				$param_arr['limit'] 	=	'500';

				$search_arr = json_decode($this->get_data($param_arr),true); 
				$phone_search_arr = Array();
				if(!empty($search_arr) && count($search_arr['results']['data'])>0)
				{
					foreach($search_arr['results']['data'] AS $key=>$val)
					{
						if($val['display_flag'] == 1)
						{	
							$row_arr['parentid']		=	$val['parentid']; 
							$row_arr['companyname'] 	=	$val['compname']; 
							$row_arr['area'] 			=	$val['areaname']; 
							$row_arr['display_flag'] 	=	$val['display_flag']; 
							$phone_search_arr[] 		= 	$row_arr;				
						}	
					}
				}	
				$num_rows = 0;
				if(count($phone_search_arr)>0)
				{
					foreach($phone_search_arr AS $key=>$val_arr)
					{						 
						$sql_audited = "SELECT * FROM d_jds.tbl_mobile_restrict_contract WHERE parentid_origional='".$params['parentid']."' AND mobile='".$value."' AND valid_flag=1";
						$res_sql_audited 	= parent::execQuery($sql_audited, $this->conn_iro);
						if(mysql_num_rows($res_sql_audited)=='0')
						{
							$selCorp = "SELECT * FROM db_jd_deductioninfo.SearchFeedBack_Corporate_Parentids WHERE parentid='".$params['parentid']."'";			
							$resCorp = parent::execQuery($selCorp, $this->connCorp);	
							if(mysql_num_rows($resCorp)==0 || (isset($params['parentid']) && $params['parentid'] == ''))
							{
								if(strtoupper($params['module']) == 'TME' || strtoupper($params['module']) == 'ME')
								{
									$sqlExist = "SELECT DISTINCT parentid_origional,mobile FROM d_jds.tbl_mobile_restrict_contract WHERE parentid_origional='".$params['parentid']."' AND mobile='".$value."' AND valid_flag=0 AND module = '".$params['module']."'";
									$resExist 	= parent::execQuery($sqlExist, $this->conn_iro);
									
									if($resExist && mysql_num_rows($resExist)==0)
									{	
										$num_rows++;
										$parentid_arr[$val_arr['parentid']]['companyname'] = $val_arr['companyname'];
										$parentid_arr[$val_arr['parentid']]['area'] = $val_arr['area'];
									}	
								}	
								else
								{
									
									$num_rows++;
									$parentid_arr[$val_arr['parentid']]['companyname'] = $val_arr['companyname'];
									$parentid_arr[$val_arr['parentid']]['area'] = $val_arr['area'];
								}
							}
						}						 
					}				
				}				 
				$msg[$value]['company_count'] = $num_rows ;
				$msg[$value]['parentid'] = $parentid_arr;
				$msg['contactnumber'] = $this->get_phone_number($this->data_city,$params['paid_flag']);
			}	
			$output_final['data'] 	=  $msg;
			$output_final['error']['code'] 		=  "0";
			$output_final['error']['message'] 	=  "success";			 
			if($params['trace'] == 1)
			{	
				echo "<pre>";
				echo "Out put : ";
				print_r($output_final);
				echo "\n--------------------------------------------------------------------------------------\n";
			}
			return $output_final;
		}			 		
	}
	function get_data($param_arr)
	{
		switch($this->data_city)
		{	
			case 'mumbai' 		: $url = "http://".MUMBAI_IRO_IP;break;
			case 'delhi' 		: $url = "http://".DELHI_IRO_IP;break;
			case 'kolkata' 		: $url = "http://".KOLKATA_IRO_IP;break;
			case 'bangalore' 	: $url = "http://".BANGALORE_IRO_IP;break;
			case 'chennai' 		: $url = "http://".CHENNAI_IRO_IP;break;
			case 'pune' 		: $url = "http://".PUNE_IRO_IP;break;
			case 'hyderabad' 	: $url = "http://".HYDERABAD_IRO_IP;break;
			case 'ahmedabad' 	: $url = "http://".AHMEDABAD_IRO_IP;break;
			default 			: $url = "http://".REMOTE_CITIES_IRO_IP;break;					
		}
		$curl_url = $url . "/mvc/services/autosuggest/autosuggest_class/phone_search?".http_build_query($param_arr);			
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curl_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);		 
		curl_close($ch);
		return $data;	
	}
	function insertMobileRequest()
	{
		global $params;
		$current_date = date("Y-m-d H:i:s");
		$output_final = Array();
		if(count($params['mobile']) > 0)
		{
			foreach($params['mobile'] as $key=>$value)
			{
				foreach($value AS $kk=>$val)	
				{
					$matched_details_arr = explode('|~~|',$val);
					foreach($matched_details_arr AS $k=>$v)
					{
						$details_arr = explode("|@|",$v);
						$insert = "INSERT INTO d_jds.tbl_mobile_restrict_contract
							SET
						parentid_origional 		=	'".$params['parentid_origional']."',
						companyname_origional	=	'".addslashes($params['companyname_origional'])."',
						area_origional			=	'".addslashes($params['area_origional'])."',
						parentid_matched		=	'".$details_arr['0']."',
						companyname_matched		=	'".addslashes($details_arr['1'])."',
						area_matched			=	'".addslashes($details_arr['2'])."',
						mobile					=	'".$key."',			
						ucode					=	'".$params['ucode']."',
						uname					=	'".addslashes($params['uname'])."',
						module					=	'".$params['module']."',
						request_date			=	'".$current_date."',
						data_city				=	'".$params['data_city']."'";
						$res_insert 	= parent::execQuery($insert, $this->conn_iro);
						if($res_insert)
						{
							$output_final['data'] 	=  "data insertion successful";
							$output_final['error']['code'] 		=  "0";
							$output_final['error']['message'] 	=  "success";			 
						}
						else
						{
							$output_final['data'] 	=  "data insertion failed";
							$output_final['error']['code'] 		=  "1";
							$output_final['error']['message'] 	=  "failed";			 
						}		
						
					}
				}	
			}
		}	
		return $output_final;		
	} 	 
	function get_phone_number($data_city,$paid_flag)
	{
		$sql = "SELECT mapped_cityname FROM d_jds.tbl_city_master WHERE ct_name='".$data_city."' ";
		$res = parent::execQuery($sql, $this->conn_iro);
		if($res && mysql_num_rows($res)>0){
			$row = mysql_fetch_assoc($res);
			switch(strtolower($row['mapped_cityname']))
			{
				case 'mumbai' 		: $paid_no = "022-61607080";	$nonpaid_no = "022-67726917"; break;
				case 'delhi'  		: $paid_no = "011-61607080";	$nonpaid_no = "0120-6658536"; break;
				case 'kolkata' 		: $paid_no = "033-61607080";	$nonpaid_no = "033-66154544"; break;
				case 'bangalore' 	: $paid_no = "080-61607080";	$nonpaid_no = "080-39804105"; break;
				case 'chennai' 		: $paid_no = "044-61607080";	$nonpaid_no = "044-66324144"; break;
				case 'pune' 		: $paid_no = "020-61607080";	$nonpaid_no = "020-66275155"; break;
				case 'hyderabad' 	: $paid_no = "040-61607080";	$nonpaid_no = "040-66306869"; break;
				case 'ahmedabad' 	: $paid_no = "079-61607080";	$nonpaid_no = "079-66102023"; break;
 			}
		}
		if($paid_flag == 'paid')
			return $paid_no;
		else if($paid_flag == 'nonpaid')
			return $nonpaid_no;	
		else 
			return $nonpaid_no;		 			
	}
	private function send_die_message($msg)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['message'] = $msg;
		return $die_msg_arr;
	}		
}
?>
