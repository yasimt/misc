<?php
class profile_strength_class extends DB
{
	var  $conn_iro    	= null;
	var  $conn_jds   	= null;
	var  $conn_tme 		= null;
	var  $conn_idc    	= null;
	var  $conn_fnc    	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	var  $ucode			= null;
	
	
	function __construct($params)
	{	
		global $params;
 		$parentid 			= trim($params['parentid']);
		$module 			= trim($params['module']);
		$data_city 			= trim($params['data_city']); 	
		$rquest 			= trim($params['rquest']); 
		$ucode 		= trim($params['ucode']); 
		/*if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }*/
		/*
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}*/
		/*
		if(trim($module)=='')
		{
			$message = "Module is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}*/		 
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->rquest  	  	= $rquest;
		$this->ucode  	  	= $ucode;
		$this->call_time  	= date("Y-m-d H:i:s");
		$this->setServers();
		$this->categoryClass_obj = new categoryClass();
				 		 
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;			
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_iro_slave	= $db[$conn_city]['iro']['slave'];
		$this->conn_fnc    		= $db[$conn_city]['fin']['master'];	
		$this->conn_fnc_slave   = $db[$conn_city]['fin']['slave'];	
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];	
	}	
	function profile_strength() {
		$func = $this->rquest;
		if((int)method_exists($this,$func) > 0)
			return $this->$func();
		else 
		{
			$message = "Invalid Function";
			return json_encode($this->sendDieMessage($message));			
		}
	}
	function calculate_profile_strength()
	{
	 	global $params;
		if($params['trace'] == 1)
		{	
			echo "Input Parameters : ";
			echo "\n\nStart Time ".date('Y-m-d H:i:s')."\n\n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		if(trim($this->parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
		
        if(trim($this->data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		 
		$compdata = $this->GetCompdetails();
		$categories = explode(",",trim(str_replace("/","",$compdata['catidlineage']),","));
		$catid_list = trim(str_replace("/","",$compdata['catidlineage']),",");
		if(!empty($params['catidlist']))
			$catid_list = $params['catidlist'];
			
		$params['category'] = 'generic';
		//$sql_cat = "SELECT category_name FROM tbl_categorymaster_generalinfo WHERE catid IN (".$catid_list.")";
		//$res_cat = parent::execQuery($sql_cat, $this->conn_iro);

		$cat_params = array();
		$cat_params['page']= 'profile_strength_class';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'category_name';

		$where_arr  	=	array();
		if($catid_list!=''){
			$where_arr['catid']			= $catid_list;
			$cat_params['where']		= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
		{
			foreach($cat_res_arr['results'] as $key=>$row_cat)
			{
				$cat_names_arr[] = addslashes($row_cat['category_name']);
			}
			if(count($cat_names_arr)>0)
			{
				$cat_names = implode("','",$cat_names_arr);
				$sql_cat_type ="SELECT a.master_category FROM online_regis1.tbl_profile_category_mapping a JOIN online_regis1.tbl_profile_master_categories b on a.master_category=b.category WHERE child_category in ('".$cat_names."') and b.active_flag=1 ORDER BY priority LIMIT 1";
				$res_cat_type = parent::execQuery($sql_cat_type, $this->conn_idc);
				if(mysql_num_rows($res_cat_type)>0)
				{
					$row_cat_type = mysql_fetch_assoc($res_cat_type);
					$params['category'] = $row_cat_type['master_category'];	
				}
			}
		}		 
		$docid_arr = $this->GetIdGenerator();
		$compdata['docid'] = $docid_arr['docid'];
		
		$contact_details = explode(",",trim(($compdata['mobile'].",".$compdata['landline'].",".$compdata['tollfree']),","));
		$contact_details = array_filter($contact_details);

		$mobile_landline = 0;
		if(count($contact_details)>0)
		{
			$mobile_landline = count($contact_details);
			if(count($contact_details)>1)
				$mobile_landline = 2;				
		}
		
		$attributes_arr = array_filter(explode("-###",trim($compdata['attributes'],",")));
		
		$sql_photo = "SELECT * FROM db_iro.tbl_video_photo_review_count_final WHERE parentid='".$this->parentid."' LIMIt 1";
		$res_photo = parent::execQuery($sql_photo, $this->conn_iro);
		$row_photo = mysql_fetch_assoc($res_photo);
		
		$score_arr['companyname']	=	(!empty($compdata['companyname']) ? '1' : '0');
		$score_arr['pincode']	=	(!empty($compdata['pincode']) ? '1' : '0');
		$score_arr['city']	=	(!empty($compdata['city']) ? '1' : '0');
		$score_arr['state']	=	(!empty($compdata['state']) ? '1' : '0');
		
		$score_arr['paid']	=	$compdata['paid'];
		$score_arr['mobile_landline']	=	$mobile_landline;
		$score_arr['year_of_establishment']	=	(!empty($compdata['year_establishment']) ? '1' : '0');
		$score_arr['mode_of_payment']	=	(!empty($compdata['payment_type']) ? '1' : '0');
		$score_arr['tagline']	=	(!empty($compdata['tag_line']) ? '1' : '0');
		$score_arr['turnover']	=	(!empty($compdata['turnover']) ? '1' : '0');
		$score_arr['no_of_employees']	=	(!empty($compdata['no_employee']) ? '1' : '0');
		$score_arr['testimonials']	=	(!empty($compdata['testimonial']) ? '1' : '0');
		$score_arr['awards_and_recognitions']	=	(!empty($compdata['award']) ? '1' : '0');
				
		$curl_menu = "http://".GNO_URL."/presentation/dashboard_services/dashboard/checkMenuStatus?contract_id=".$compdata['docid'];
		
		$score_arr['menu'] = '0';
		$param_menu_arr = array();		 		
		$resp_menu = $this->get_curl_data($curl_menu,$param_menu_arr);
		$data_menu = json_decode($resp_menu,true);
		$score_arr['menu']	=	$data_menu['result']['menu_available'];
		
		$score_arr['aadhar']	=	'0';
		$score_arr['pancard']	=	'0';
		$score_arr['license']	=	'0';
		$score_arr['passport']	=	'0';
		
		$sql_kyc = "SELECT * FROM tbl_kyc_details WHERE parentid='".$this->parentid."' LIMIT 1";
		$res_kyc = parent::execQuery($sql_kyc, $this->conn_idc);
		if(mysql_num_rows($res_kyc)>0)
		{
			$row_kyc = mysql_fetch_assoc($res_kyc);
			if(!empty($row_kyc['aadharNum']))
				$score_arr['aadhar']	=	'1';
			if(!empty($row_kyc['panCardNum']))	
				$score_arr['pancard']	=	'1';
			if(!empty($row_kyc['licenseNum']))	
				$score_arr['license']	=	'1';
			if(!empty($row_kyc['passportNum']))
				$score_arr['passport']	=	'1';
			
		}
		$curl_comp_api = "http://".WEB_SERVICES_API."/web_services/CompanyDetails.php?docid=".$compdata['docid'];
		$param_comp_arr = array();		 		
		$resp_comp = $this->get_curl_data($curl_comp_api,$param_comp_arr);
		$data_comp = json_decode($resp_comp,true);		 
		$score_arr['deals_and_offers']	=	(!empty($data_comp[$compdata['docid']]['offer_d']) ? '1' : '0');
		$score_arr['profile_picture']	=	(!empty($data_comp[$compdata['docid']]['profile_pic']) ? '1' : '0');
				
		$score_arr['website']	=	(!empty($compdata['website']) ? '1' : '0');
		$score_arr['email']	=	(!empty($compdata['email']) ? '1' : '0');
		$score_arr['building_name']	=	(!empty($compdata['building_name']) ? '1' : '0');
		$score_arr['area']	=	(!empty($compdata['area']) ? '1' : '0');
		$score_arr['street']	=	(!empty($compdata['street']) ? '1' : '0');
		$score_arr['landmark']	=	(!empty($compdata['landmark']) ? '1' : '0');
		$score_arr['geocode_accuracy_level']	=	(($compdata['geocode_accuracy_level']=='1') ? '1' : '0');
		$score_arr['categories']	=	count(array_filter($categories));
		$score_arr['proof_of_establishment']	=	(!empty($compdata['proof_establishment']) ? '1' : '0');
		$score_arr['profile_picture']	=	(($row_photo['photo_count']>0) ? '1' : '0');
		$score_arr['quality_images']	=	(($row_photo['photo_count']>0) ? '1' : '0');
		$score_arr['attributes']	=	count($attributes_arr);
		$score_arr['ratings']	=	$row_photo['review_count'];
		if(!empty($compdata['social_media_url']))
		{
			$social_media_url_arr = explode("|~|",$compdata['social_media_url']);
			$score_arr['facebook']	=	(!empty($social_media_url_arr['0']) ? '1' : '0');
			$score_arr['twitter']	=	(!empty($social_media_url_arr['1']) ? '1' : '0');
			$score_arr['google']	=	(!empty($social_media_url_arr['2']) ? '1' : '0');
			$score_arr['other']	=	(!empty($social_media_url_arr['3']) ? '1' : '0');
			$score_arr['youtube']	=	(!empty($social_media_url_arr['4']) ? '1' : '0');
			$score_arr['instagram']	=	(!empty($social_media_url_arr['5']) ? '1' : '0');			
		}
		$sql_attributes = "SELECT 
						SUM( IF(LOWER(attribute_name)='registration no',1,0)) registration_number, 
						SUM(IF(LOWER(attribute_name)='years of experience',1,0)) years_of_experience, 
						SUM(IF(LOWER(attribute_name)='price for two',1,0)) price_for_two, 
						SUM(IF(LOWER(sub_group_name)='services',1,0)) services, 
						SUM(IF(LOWER(sub_group_name)='consultation fee',1,0)) consultation_fee, 
						SUM(IF(LOWER(sub_group_name)='type',1,0)) establishment_type, 
						SUM(IF(LOWER(sub_group_name)='cuisines',1,0)) cuisine
						FROM db_iro.tbl_companymaster_attributes WHERE parentid='".$this->parentid."'";
		$res_attributes = parent::execQuery($sql_attributes, $this->conn_iro);
		if(mysql_num_rows($res_attributes) > 0)
		{		 
			$row_attributes = mysql_fetch_assoc($res_attributes);			
			
			$score_arr['registration_number'] = (($row_attributes['registration_number']>0) ? '1' : '0');
			$score_arr['years_of_experience'] = (($row_attributes['years_of_experience']>0) ? '1' : '0');
			$score_arr['price_for_two'] = (($row_attributes['price_for_two']>0) ? '1' : '0');
			$score_arr['services'] = (($row_attributes['services']>0) ? '1' : '0');
			$score_arr['consultation_fee'] = (($row_attributes['consultation_fee']>0) ? '1' : '0');
			$score_arr['establishment_type'] = (($row_attributes['establishment_type']>0) ? '1' : '0');
			$score_arr['cuisine'] = (($row_attributes['cuisine']>0) ? '1' : '0');			
		}		
		$sql = "SELECT * FROM online_regis1.tbl_profile_parameters WHERE category='".$params['category']."'";
		$res = parent::execQuery($sql, $this->conn_idc);
		$numRows = mysql_num_rows($res);
		
		if($numRows > 0)
		{		 
			while($row = mysql_fetch_assoc($res))
			{
				if($row['type_flag']=='fix_value')
				{
					$score  += $score_arr[$row['field']]*$row['score']; 					
					$score_cnt[$row['field']]  = $score_arr[$row['field']]*$row['score']; 					
				}	
				else if($row['type_flag']=='range_value')
				{
					$range_arr = explode(",",$row['remarks']);
					foreach($range_arr as $key=>$val)
					{
						$val_arr = explode("=",$val);
						$range_arr = explode("-",$val_arr['0']);
						if($score_arr[$row['field']] >= $range_arr['0'] &&  $score_arr[$row['field']] <= $range_arr['1'])
						{
							$score  += $val_arr[1]; 	
							$score_cnt[$row['field']] = $val_arr[1];
						}
					}					 
				}
				$profile_rules[$row['field']] = $row;
			}
		}	
		 
		$output_final['result']['parentid']  = $this->parentid;
		$output_final['result']['docid']  = $compdata['docid'];
		$output_final['result']['companyname']  = $compdata['companyname'];
		$output_final['result']['category']  = $params['category'];
		$output_final['result']['data_city'] = $this->data_city;
		$output_final['result']['profile_strength'] = $score;
		$output_final['result']['profile_strength_calulated'] = $score_cnt;
		$output_final['result']['profile_strength_rules'] = $profile_rules;
		$output_final['error']['code'] = 0;
		$output_final['error']['message'] = 'success';
				
		if($params['trace'] == 1)
		{	
			
			echo "Out put : ";
			print_r($output_final);
			
			echo "\n\nStart Time ".date('Y-m-d H:i:s')."\n\n";

			echo "\n--------------------------------------------------------------------------------------\n";
		}				
		return $output_final;
	}
	public function master_category()
	{
		global $params;
		if($params['trace'] == 1)
		{	
			echo "Input Parameters : ";
			echo "\n\nStart Time ".date('Y-m-d H:i:s')."\n\n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		if(strtolower($params['type']) == 'insert')
		{
			if(empty($params['category']))
			{
				$output = "Category Blank";
			}
			else
			{
				$exist_flag=0;
				$sql = "SELECT * FROM online_regis1.tbl_profile_master_categories";
				$res = parent::execQuery($sql, $this->conn_idc);
				$insert_flag = '0';
				if(mysql_num_rows($res)>0)
				{
					$exist_flag = 0;
					while($row=mysql_fetch_assoc($res))
					{
						if(strtolower($row['category']) == strtolower($params['category']))
						{
							$exist_flag=1;
							break;	
						}
					}
					if($exist_flag == 1)
					{
						$output	=	"Category Already Exist";
						$output_final['errorCode'] = 1;
						$insert_flag = '0';
					}
					else
						$insert_flag = '1';
				} 
				else				
					$insert_flag = '1';
				
				if($insert_flag == '1')
				{
					$priority = mysql_num_rows($res) + 1;
					$insert = "INSERT INTO online_regis1.tbl_profile_master_categories 
						SET 
							category	=	'".addslashes($params['category'])."',
							active_flag	=	'1',
							priority	=	'".$priority."'";
							
					$res_insert = parent::execQuery($insert, $this->conn_idc);
					if($res_insert)
						$output = "Master category Inserted successfully ";
					else
						$output = "Master category Insertion failed ";					
				}				
			}
		}
		else if(strtolower($params['type']) == 'update')
		{
						
		}
		else
		{
			$output = "Invalid type ";		
		}
		$output_final['result'] = $output;
		if($params['trace'] == 1)
		{	
			echo "Out put : ";
			print_r($output_final);
			echo "\n\nStart Time ".date('Y-m-d H:i:s')."\n\n";

			echo "\n--------------------------------------------------------------------------------------\n";
		}				
		return $output_final;
	}
	
	public function get_score_details()
	{
		global $params;
		if($params['trace'] == 1)
		{	
			echo "Input Parameters : ";
			echo "\n\nStart Time ".date('Y-m-d H:i:s')."\n\n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}		
		$sql_fields = "SELECT * FROM online_regis1.tbl_profile_fields WHERE active_flag=1";
		$res_fields = parent::execQuery($sql_fields, $this->conn_idc);
		if(mysql_num_rows($res_fields)>0)
		{
			while($row_fields=mysql_fetch_assoc($res_fields))
			{
				$score_arr[$row_fields['field']]['category'] = $params['category'];
				$score_arr[$row_fields['field']]['parent_field'] = $row_fields['parent_field'];
				$score_arr[$row_fields['field']]['field'] = $row_fields['field'];
				$score_arr[$row_fields['field']]['score'] = '0';
				$score_arr[$row_fields['field']]['remarks'] = '0';
				$score_arr[$row_fields['field']]['max_score'] = '0';
				$score_arr[$row_fields['field']]['type_flag'] = 'fix_value';
				 
			}
		}	
		
		$sql = "SELECT * FROM online_regis1.tbl_profile_parameters WHERE category='".$params['category']."'";
		$res = parent::execQuery($sql, $this->conn_idc);
		if(mysql_num_rows($res)>0)
		{
			while($row=mysql_fetch_assoc($res))
			{				
				$score_arr[$row['field']]['category'] = $row['category'];
				$score_arr[$row['field']]['parent_field'] = $row['parent_field'];
				$score_arr[$row['field']]['field'] = $row['field'];
				$score_arr[$row['field']]['score'] = $row['score'];
				$score_arr[$row['field']]['remarks'] = $row['remarks'];
				$score_arr[$row['field']]['max_score'] = $row['max_score'];
				$score_arr[$row['field']]['type_flag'] = $row['type_flag'];
			}
		}
		$output_final['result']	= 	$score_arr;
		if($params['trace'] == 1)
		{	
			echo "Out put : ";
			print_r($output_final);
			echo "\n\nStart Time ".date('Y-m-d H:i:s')."\n\n";

			echo "\n--------------------------------------------------------------------------------------\n";
		}				
		return $output_final;

	}
	public function GetCompdetails()
	{		
		$sql = "SELECT *,IFNULL(SUBSTRING_INDEX(averageRating,'~',-1),0) AS rating_jd FROM tbl_companymaster_generalinfo a JOIN tbl_companymaster_extradetails b on a.parentid=b.parentid WHERE a.parentid ='".$this->parentid."'";
		$res = parent::execQuery($sql, $this->conn_iro);
		
		if($res && parent::numRows($res)>0)
		{
			$row = parent::fetchData($res);
			return $row;
		}
	}
	public function GetIdGenerator()
	{
		$sqlDocidInfo = "SELECT ((url_cityid*100000000) + sphinx_id) AS panindia_sphinxid,data_city,CONCAT('|#|',data_city,'|#|') AS listed_cities,CONCAT(url_cityid,shorturl) AS short_url,stdcode,stdcode as data_city_stdcode,sphinx_id,docid FROM db_iro.tbl_id_generator WHERE parentid ='".$this->parentid."'";
		$resDocidInfo = parent::execQuery($sqlDocidInfo, $this->conn_iro);
		if($resDocidInfo && parent::numRows($resDocidInfo)>0)
		{
			$row_docid = parent::fetchData($resDocidInfo);
			return $row_docid;
		}		
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
	private function get_paidstatus($parentid,$data_city)
	{
		$res_arr = array();
		$sql_contract_type = "SELECT parentid FROM tbl_companymaster_finance WHERE parentid='".$parentid."' and balance>0 LIMIT 1";
		$res_contract_type = parent::execQuery($sql_contract_type,$this->conn_fnc);
		
		$paid_status = 0;
		if(parent::numRows($res_contract_type)>0){
			$paid_status = 1;
		}
		else{
			$sql_national_type = "SELECT parentid FROM tbl_companymaster_finance_national WHERE parentid='".$parentid."' AND  campaignid = 10 AND balance>0 LIMIT 1";
			$res_national_type = parent::execQuery($sql_national_type,$this->conn_national);
			if(parent::numRows($res_national_type)>0){
				$paid_status = 1;
			}
		}
		$res_arr['result']['paid'] 	= $paid_status;
		return $res_arr;
	}		
	 
	private function sendDieMessage($msg)
	{
		$die_msg_arr['result'] = array();
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['message'] = $msg;
		return $die_msg_arr;
	}		
}
?>
