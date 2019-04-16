<?php
class misc_budget_update_class extends DB
{
	var  $conn_iro    	= null;
	var  $conn_jds   	= null;
	var  $conn_tme 		= null;
	var  $conn_fnc    	= null;
	var  $conn_idc    	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'kolkata', 'bangalore', 'chennai', 'pune', 'hyderabad', 'ahmedabad');

	var  $action		= null;
	var  $data_city		= null;
	var  $teaminfo_arr	= array();
	var  $all_cities_arr= array();
	
	
	function __construct($params)
	{
		$action 			= trim($params['action']);
		$data_city 			= trim($params['data_city']);
		if(trim($action)=='')
        {
            $message = "Action is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if(trim($data_city)=='')
        {
            $message = "Data City is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        $this->action  		= $action;
		$this->data_city 	= $data_city;
		$this->setServers();
		$this->all_cities_arr = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];
		$this->conn_budget   	= $db[$conn_city]['db_budgeting']['master'];
		

	}
	function getBannerBudgetInfo()
	{
		$resultArr = array();
		if(in_array(strtoupper($this->data_city), array('MUMBAI','KOLKATA','BANGALORE','CHENNAI','PUNE','HYDERABAD','AHMEDABAD', 'DELHI','JAIPUR','CHANDIGARH','COIMBATORE'))){
            $datacity_str =  $this->data_city;
        }else{
            $datacity_str =  'Remote';
        }
		$sqlBannerBudget = "SELECT city,banner_fees,banner_fees_ecs,banner_standalone FROM bannercharge WHERE city = '".$datacity_str."'";
		$resBannerBudget = parent::execQuery($sqlBannerBudget, $this->conn_local);
		if($resBannerBudget && parent::numRows($resBannerBudget)>0)
		{
			$row_banner_bdgt 	= parent::fetchData($resBannerBudget);
			$banner_fees 		= trim($row_banner_bdgt['banner_fees']);
			$banner_fees		= round($banner_fees * 12);
			$banner_fees_ecs 	= trim($row_banner_bdgt['banner_fees_ecs']);
			$banner_fees_ecs	= round($banner_fees_ecs * 12);
		}
		
		if($banner_fees){
			$resultArr['bnrfees'] 		= $banner_fees;
			$resultArr['bnrfees_ecs'] 	= $banner_fees_ecs;
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function updateBannerBudget($params)
	{
		global $db;
		$query_str = '';
		$param_str 		= json_encode($params);
		$time_stamp 	= date_create();
		$uniqueid 		= date_format($time_stamp, 'U');
		
		$requested_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$resultArr = array();
		
		if(count($params['bannerbdgt'])>0)
		{
			$banner_fees		= $params['bannerbdgt']['bfee'];
			$banner_fees		= $banner_fees / 12;
			$banner_fees		= number_format((float)$banner_fees, 2, '.', '');
			$banner_fees_ecs	= $params['bannerbdgt']['bfee_ecs'];
			$banner_fees_ecs	= $banner_fees_ecs / 12;
			$banner_fees_ecs	= number_format((float)$banner_fees_ecs, 2, '.', '');
			$bnrlogstr	 	 	= json_encode($params['bannerbdgt']['L']);
			
			if(in_array(strtoupper($this->data_city), array('MUMBAI','KOLKATA','BANGALORE','CHENNAI','PUNE','HYDERABAD','AHMEDABAD', 'DELHI','JAIPUR','CHANDIGARH','COIMBATORE'))){
				$datacity_str =  $this->data_city;
			}else{
				$datacity_str =  'Remote';
			}
				
			$query_str = "UPDATE bannercharge SET banner_fees = '".$banner_fees."', banner_standalone = '".$banner_fees."', banner_fees_ecs = '".$banner_fees_ecs."' WHERE city = '".$datacity_str."'";
			$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
			$sqlUpdtBCampaignReqLocal = $query_str;
			$resUpdtBCampaignReqLocal = parent::execQuery($sqlUpdtBCampaignReqLocal, $requested_city_local); // updating on requested city first - Local
			
			$requested_city_idc 	 = $db[$requested_city]['idc']['master'];
			$sqlUpdtBCampaignReqIDC = $query_str;
			$resUpdtBCampaignReqIDC = parent::execQuery($sqlUpdtBCampaignReqIDC, $requested_city_idc); // updating on requested city first - IDC
			
			if($resUpdtBCampaignReqLocal && $resUpdtBCampaignReqIDC)
			{
				$resultArr['errorcode'] = 0;
				unset($this->all_cities_arr[$requested_city]);
				$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
									campaign_name 	= 'Banner',
									city_name 		= '".$this->data_city."',
									ucode 			= '".$params['ucode']."',
									uname 			= '".addslashes($params['uname'])."',
									insertdate 		= '".date("Y-m-d H:i:s")."',
									ip_address		= '".$params['ipaddr']."',
									query_str		= '".addslashes($query_str)."',
									param_str		= '".$param_str."',
									comment			= 'Banner Budget Update',
									uniqueid		= '".$uniqueid."',
									log_str			= '".$bnrlogstr."'";
				$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
				$city_update_arr = array();
				try{
					$i = 0;
					$j = 0;
					foreach($this->all_cities_arr as $cityvalue)
					{
						$i ++;
						$conn_city_local	= array();
						$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
						$sqlUpdtBCampaignRestLocal 	= $query_str;
						$resUpdtBCampaignRestLocal 	= parent::execQuery($sqlUpdtBCampaignRestLocal, $conn_city_local);
						
						$conn_city_idc	= array();
						$conn_city_idc  	= $db[$cityvalue]['idc']['master'];
						$sqlUpdtBCampaignRestIDC 	= $query_str;
						$resUpdtBCampaignRestIDC 	= parent::execQuery($sqlUpdtBCampaignRestIDC, $conn_city_idc);
						if($resUpdtBCampaignRestLocal && $resUpdtBCampaignRestIDC)
						{
							$j ++;
							$city_update_arr[] = $cityvalue;
							$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
												  campaign_name 	= 'Banner',
												  city_name 		= '".$this->data_city."',
												  ucode 			= '".$params['ucode']."',
												  uname 			= '".addslashes($params['uname'])."',
												  insertdate 		= '".date("Y-m-d H:i:s")."',
												  ip_address		= '".$params['ipaddr']."',
												  query_str			= '".addslashes($query_str)."',
												  param_str			= '".$param_str."',
												  comment			= 'Banner Budget Update'";
							$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
						}
					}
					$city_update_str = '';
					if(count($city_update_arr)>0){
						$city_update_str = implode(",",$city_update_arr);
					}
					$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
					$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
					if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
						$this->sendErrorMsg('Banner Budget Update',$query_str);
					}
				}
				catch(Exception $e) {
					$city_update_str = implode(",",$city_update_arr);
					$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
					$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
					$this->sendErrorMsg('Banner Budget Update',$query_str);
				}
			}
			
		}
		return $resultArr;
	}
	
	function getJdrrBudgetInfo()
	{
		$resultArr = array();
		if(in_array(strtoupper($this->data_city), array('MUMBAI','DELHI','KOLKATA','BANGALORE','CHENNAI','PUNE','HYDERABAD','AHMEDABAD'))){
            $datacity_str =  $this->data_city;
        }else{
            $datacity_str =  'Remote';
        }
		$sqlJdrrBudget = "SELECT city,upfront_payment,monthlyPayment,advance_amt,additional_amt FROM online_regis_mumbai.tbl_jdrr_pricing WHERE city = '".$datacity_str."'";
		$resJdrrBudget = parent::execQuery($sqlJdrrBudget, $this->conn_idc);
		if($resJdrrBudget && parent::numRows($resJdrrBudget)>0)
		{
			$row_jdrr_bdgt 	= parent::fetchData($resJdrrBudget);
			$upfront_payment 	= trim($row_jdrr_bdgt['upfront_payment']); // Yearly Budget
			$upfront_payment	= round($upfront_payment);
		}
		
		if($upfront_payment){
			$resultArr['upfront'] 		= $upfront_payment;
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	
	function updateJdrrBudget($params)
	{
		global $db;
		$query_str = '';
		$param_str 		= json_encode($params);
		$time_stamp 	= date_create();
		$uniqueid 		= date_format($time_stamp, 'U');
		
		$requested_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$resultArr = array();
		
		if(count($params['jdrrbdgt'])>0)
		{
			$upfront_payment = $params['jdrrbdgt']['upfront'];
			$upfront_payment = intval($upfront_payment);
			$jdrrlogstr	 	 = json_encode($params['jdrrbdgt']['L']);
			
			if(in_array(strtoupper($this->data_city), array('MUMBAI','DELHI','KOLKATA','BANGALORE','CHENNAI','PUNE','HYDERABAD','AHMEDABAD'))){
				$datacity_str =  $this->data_city;
			}else{
				$datacity_str =  'Remote';
			}
			$query_str = "UPDATE online_regis_mumbai.tbl_jdrr_pricing SET upfront_payment = '".$upfront_payment."' WHERE city = '".$datacity_str."'";
			
			$requested_city_idc 	 = $db[$requested_city]['idc']['master'];
			$sqlUpdtJdrrCampaign = $query_str;
			$resUpdtJdrrCampaign = parent::execQuery($sqlUpdtJdrrCampaign, $requested_city_idc); // updating on requested city first - IDC
			
			if($resUpdtJdrrCampaign)
			{
				$resultArr['errorcode'] = 0;
				$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
									campaign_name 	= 'JDRR',
									city_name 		= '".$this->data_city."',
									ucode 			= '".$params['ucode']."',
									uname 			= '".addslashes($params['uname'])."',
									insertdate 		= '".date("Y-m-d H:i:s")."',
									ip_address		= '".$params['ipaddr']."',
									query_str		= '".addslashes($query_str)."',
									param_str		= '".$param_str."',
									comment			= 'JDRR Budget Update',
									uniqueid		= '".$uniqueid."',
									log_str			= '".$jdrrlogstr."',
									city_update_str = 'mumbai,delhi,pune,bangalore,ahmedabad,hyderabad,chennai,kolkata,remote'";
				$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);			
			}
		}
		return $resultArr;
	}
	function getNationalListingBudget()
	{
		$resultArr = array();
		$natbdgt_found = 0;
		$sqlNationalBudget = "SELECT minbudget_national FROM tbl_business_uploadrates WHERE city = '".$this->data_city."'";
		$resNationalBudget = parent::execQuery($sqlNationalBudget, $this->conn_local);
		if($resNationalBudget && parent::numRows($resNationalBudget)>0){
			$natbdgt_found = 1;
			$row_national_bdgt 	= parent::fetchData($resNationalBudget);
			$minbudget_national = intval($row_national_bdgt['minbudget_national']);
		}
		if($natbdgt_found){
			$resultArr['natbdgt'] 	= $minbudget_national;
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	
	function updateNationalBudget($params)
	{
		global $db;
		$query_str = '';
		$param_str 		= json_encode($params);
		$time_stamp 	= date_create();
		$uniqueid 		= date_format($time_stamp, 'U');
		
		$requested_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$resultArr = array();
		
		if(count($params['natbdgt'])>0)
		{
			$minbudget_national	= intval($params['natbdgt']['budget']);
			$natlogstr	 	 	= json_encode($params['natbdgt']['L']);
				
			$query_str = "UPDATE tbl_business_uploadrates SET minbudget_national = '".$minbudget_national."' WHERE city = '".$this->data_city."'";
			$requested_city_local 	= $db[$requested_city]['d_jds']['master'];
			$sqlUpdtNationalBudget 	= $query_str;
			$resUpdtNationalBudget  = parent::execQuery($sqlUpdtNationalBudget, $requested_city_local); // updating on requested city first - Local
						
			if($resUpdtNationalBudget)
			{
				$resultArr['errorcode'] = 0;
				unset($this->all_cities_arr[$requested_city]);
				$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
									campaign_name 	= 'National Listing',
									city_name 		= '".$this->data_city."',
									ucode 			= '".$params['ucode']."',
									uname 			= '".addslashes($params['uname'])."',
									insertdate 		= '".date("Y-m-d H:i:s")."',
									ip_address		= '".$params['ipaddr']."',
									query_str		= '".addslashes($query_str)."',
									param_str		= '".$param_str."',
									comment			= 'National Listing Budget Update',
									uniqueid		= '".$uniqueid."',
									log_str			= '".$natlogstr."'";
				$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
				$city_update_arr = array();
				try{
					$i = 0;
					$j = 0;
					foreach($this->all_cities_arr as $cityvalue)
					{
						$i ++;
						$conn_city_local	= array();
						$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
						$sqlUpdtNationalBudgetRest 	= $query_str;
						$resUpdtNationalBudgetRest 	= parent::execQuery($sqlUpdtNationalBudgetRest, $conn_city_local);
						
						if($resUpdtNationalBudgetRest)
						{
							$j ++;
							$city_update_arr[] = $cityvalue;
							$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
												  campaign_name 	= 'National Listing',
												  city_name 		= '".$this->data_city."',
												  ucode 			= '".$params['ucode']."',
												  uname 			= '".addslashes($params['uname'])."',
												  insertdate 		= '".date("Y-m-d H:i:s")."',
												  ip_address		= '".$params['ipaddr']."',
												  query_str			= '".addslashes($query_str)."',
												  param_str			= '".$param_str."',
												  comment			= 'National Listing Budget Update'";
							$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
						}
					}
					$city_update_str = '';
					if(count($city_update_arr)>0){
						$city_update_str = implode(",",$city_update_arr);
					}
					$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
					$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
					if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
						$this->sendErrorMsg('National Listing Budget Update',$query_str);
					}
				}
				catch(Exception $e) {
					$city_update_str = implode(",",$city_update_arr);
					$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
					$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
					$this->sendErrorMsg('National Listing Budget Update',$query_str);
				}
			}
		}
		return $resultArr;
	}
	function getPositionAvailInfo()
	{
		$resultArr = array();
		$sqlPositionInfo = "SELECT position_flag,active_flag FROM tbl_fixedposition_factor WHERE position_flag IN (4,5,6,7) ORDER by position_flag ASC";
		$resPositionInfo = parent::execQuery($sqlPositionInfo, $this->conn_budget);
		if($resPositionInfo && parent::numRows($resPositionInfo)>0)
		{
			while($row_position_info 	= parent::fetchData($resPositionInfo))
			{
				$position_flag 	= intval($row_position_info['position_flag']);
				$active_flag 	= intval($row_position_info['active_flag']);
				$resultArr['data'][$position_flag] = $active_flag;
			}
		}
		if(count($resultArr)>0){
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function updatePositionAvail($params)
	{
		global $db;
		$param_str 		= json_encode($params);
		$time_stamp 	= date_create();
		$uniqueid 		= date_format($time_stamp, 'U');
		
		$requested_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$resultArr = array();
		
		if(count($params['posinfo'])>0)
		{
			$poslogstr	 	 		= json_encode($params['posinfo']['L']);
			$requested_city_conn 	= $db[$requested_city]['db_budgeting']['master'];
			
			if(count($params['posinfo']['posavail'])>0){
				$i = 0;
				$j = 0;
				$query_arr = array();
				foreach($params['posinfo']['posavail'] as $position_flag => $active_flag){
					$i ++;
					$sqlUpdtPosAvailability = "UPDATE tbl_fixedposition_factor SET active_flag = '".$active_flag."' WHERE position_flag = '".$position_flag."'";
					$resUpdtPosAvailability = parent::execQuery($sqlUpdtPosAvailability, $requested_city_conn);
					if($resUpdtPosAvailability){
						$query_arr[$position_flag] = $sqlUpdtPosAvailability;
						$j ++;
						$resultArr['errorcode'] = 0;
					}else{
						$this->sendErrorMsg('Position Availability Update',$sqlUpdtPosAvailability);
						$resultArr['errorcode'] = 1;
						break;
					}
				}
				$query_str = json_encode($query_arr);
				$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
									campaign_name 	= 'Position Availability',
									city_name 		= '".$this->data_city."',
									ucode 			= '".$params['ucode']."',
									uname 			= '".addslashes($params['uname'])."',
									insertdate 		= '".date("Y-m-d H:i:s")."',
									ip_address		= '".$params['ipaddr']."',
									query_str		= '".addslashes($query_str)."',
									param_str		= '".$param_str."',
									comment			= 'Position Availability Update',
									uniqueid		= '".$uniqueid."',
									log_str			= '".$poslogstr."',
									city_update_str = '".$this->data_city."'";
				$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
				
				if(($i <= 0) || ($j <=0) || ($i !=$j)){
					$this->sendErrorMsg('Position Availability Update',$query_str);
				}
				
			}else{
				$resultArr['errorcode'] = 1;
			}
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function arrayProcess($requestedArr)
	{
		$processedArr = array();
		if(count($requestedArr)>0){
			$processedArr = array_merge(array_unique(array_filter($requestedArr)));
		}
		return $processedArr;
	}
	private function sendErrorMsg($action,$query){
		$email_text	= '';
		$email_text .= '<br>Action : '.$action;
		$email_text .= '<br>Query : '.$query;
		$email_text .= '<br>';
		
		$link_sms	= mysql_connect('172.29.0.33','decs_app','s@myD#@mnl@sy');
		mysql_select_db('sms_email_sending', $link_sms);
		
		// insert into Tushar's table to automatically send sms from his table
		echo $sql_sms = "INSERT INTO tbl_common_intimations (email_id, email_subject, email_text, source) VALUES ('imteyaz.raja@justdial.com','Error In GENIO Campaign Budget Update', '".addslashes($email_text)."','cs')";
		$res_sms = mysql_query($sql_sms, $link_sms);
	}
	private function sendDieMessage($msg)
	{
		$die_msg_arr['errorcode'] = 1;
		$die_msg_arr['errormsg'] = $msg;
		return $die_msg_arr;
	}
}
?>
