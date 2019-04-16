<?php
class phone_search_check_class extends DB
{
	var  $conn_local   	= null;
	var  $conn_iro   	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	
	
	function __construct($params)
	{
		$parentid 		= trim($params['parentid']);
		$module 		= trim($params['module']);
		$data_city 		= trim($params['data_city']);
		
		if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->send_die_message($message));
            die();
        }
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(trim($module)=='')
		{
			$message = "Module is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		
		$this->setServers();
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$conn_city 			= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$this->conn_fin   			= $db[$conn_city]['fin']['master'];
		$this->conn_idc   			= $db[$conn_city]['idc']['master'];
		
	}
	function getPhoneSearchCampaignInfo()
	{
		$result_found = 0;
		$sqlFetchShadowData = "SELECT parentid FROM tbl_companymaster_finance_shadow WHERE parentid='".$this->parentid."' AND campaignid IN ('1','2','10')";
		$resFetchShadowData   = parent::execQuery($sqlFetchShadowData, $this->conn_fin);
		if($resFetchShadowData && mysql_num_rows($resFetchShadowData)>0)
		{
			$result_found = 1;
		}
		else
		{
			$sqlFetchMainData = "SELECT parentid FROM tbl_companymaster_finance WHERE parentid='".$this->parentid."' AND campaignid IN ('1','2')";
			$resFetchMainData   = parent::execQuery($sqlFetchMainData, $this->conn_fin);
			if($resFetchMainData && mysql_num_rows($resFetchMainData)>0)
			{
				$result_found = 1;
			}
			
			$sqlFetchNational = "SELECT parentid FROM db_national_listing.tbl_companymaster_finance_national WHERE parentid='".$this->parentid."' AND campaignid IN ('10')";
			//print_r($this->conn_idc);
			$resFetchNational   = parent::execQuery($sqlFetchNational, $this->conn_idc);
				if($resFetchNational && mysql_num_rows($resFetchNational)>0)
				{
					$result_found = 1;
				}	
		}
		
		if($result_found == 1)
		{
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
		}
		else
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Phone Search campaign not found";
		}
		return $result_msg_arr;
		
	}
	private function send_die_message($msg)
	{
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
}
?>
