<?php
class jdomni_budget_update_class extends DB
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
		$conn_city 			= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$this->conn_iro    	= $db[$conn_city]['iro']['master'];
		$this->conn_local  	= $db[$conn_city]['d_jds']['master'];
		$this->conn_tme		= $db[$conn_city]['tme_jds']['master'];
		$this->conn_idc   	= $db[$conn_city]['idc']['master'];
		
		

	}
	function getJdomni1Status(){
		$resultArr = array();
		$campaignid =  '734'; // OMNI 1
		$sqlJdomniStatus = "SELECT display_upfront,display_ecs FROM tbl_finance_omni_flow_display_new_new WHERE campaignid = '".$campaignid."'";
		$resJdomniStatus = parent::execQuery($sqlJdomniStatus, $this->conn_tme);
		if($resJdomniStatus && parent::numRows($resJdomniStatus)>0){
			$row_omni1_status 	= parent::fetchData($resJdomniStatus);
			$display_upfront	= intval($row_omni1_status['display_upfront']);
			$display_ecs		= intval($row_omni1_status['display_ecs']);
			$upfrontstatus		= ($display_upfront == 1) ? 1 : 0;
			$ecsstatus			= ($display_ecs == 1) ? 1 : 0;
			$resultArr['upfrontstatus'] = $upfrontstatus;
			$resultArr['ecsstatus']		= $ecsstatus;
		}
		if(count($resultArr)>0){
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function updateJdomni1Status($params){
		global $db;
		$query_str = '';
		$param_str 		= json_encode($params);
		$time_stamp 	= date_create();
		$uniqueid 		= date_format($time_stamp, 'U');
		
		$requested_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$resultArr = array();
		
		if(count($params['omni1statusdata'])>0)
		{
			$display_upfront	= intval($params['omni1statusdata']['upfrontstatus']);
			$display_ecs		= intval($params['omni1statusdata']['ecsstatus']);
			$omni1logstr	 	= json_encode($params['omni1statusdata']['L']);
			
			$campaignid =  '734'; // OMNI 1
			
				
			$query_str = "UPDATE tbl_finance_omni_flow_display_new_new SET display_upfront = '".$display_upfront."', display_ecs = '".$display_ecs."' WHERE campaignid = '".$campaignid."'";
			$requested_city_local 	 = $db[$requested_city]['tme_jds']['master'];
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
									campaign_name 	= 'OMNI 1',
									city_name 		= '".$this->data_city."',
									ucode 			= '".$params['ucode']."',
									uname 			= '".addslashes($params['uname'])."',
									insertdate 		= '".date("Y-m-d H:i:s")."',
									ip_address		= '".$params['ipaddr']."',
									query_str		= '".addslashes($query_str)."',
									param_str		= '".$param_str."',
									comment			= 'OMNI 1 Upfront / ECS Status Update',
									uniqueid		= '".$uniqueid."',
									log_str			= '".$omni1logstr."'";
				$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
				$city_update_arr = array();
				try{
					$i = 0;
					$j = 0;
					foreach($this->all_cities_arr as $cityvalue)
					{
						$i ++;
						$conn_city_local	= array();
						$conn_city_local  	= $db[$cityvalue]['tme_jds']['master'];
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
												  campaign_name 	= 'OMNI 1',
												  city_name 		= '".$this->data_city."',
												  ucode 			= '".$params['ucode']."',
												  uname 			= '".addslashes($params['uname'])."',
												  insertdate 		= '".date("Y-m-d H:i:s")."',
												  ip_address		= '".$params['ipaddr']."',
												  query_str			= '".addslashes($query_str)."',
												  param_str			= '".$param_str."',
												  comment			= 'OMNI 1 Upfront / ECS Status Update'";
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
						$this->sendErrorMsg('OMNI 1 Upfront / ECS Status Update',$query_str);
					}
				}
				catch(Exception $e) {
					$city_update_str = implode(",",$city_update_arr);
					$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
					$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
					$this->sendErrorMsg('OMNI 1 Upfront / ECS Status Update',$query_str);
				}
			}
			
		}
		return $resultArr;
		
	}
	function arrayProcess($requestedArr){
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
		$sql_sms = "INSERT INTO tbl_common_intimations (email_id, email_subject, email_text, source) VALUES ('imteyaz.raja@justdial.com','Error In GENIO Campaign Budget Update', '".addslashes($email_text)."','cs')";
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
