<?php

class deal_close_details_class extends DB
{	
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;	
	var  $intermediate 	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	var  $version		= null;
	var  $data_city	= null;

	function __construct($params)
	{		
		$this->params = $params;		
		
		$parentid 		= trim($params['parentid']);
		$module 		= trim($params['module']);
		$data_city 		= trim($params['data_city']);
		$ucode 			= trim($params['ucode']);
		$uname 			= trim($params['uname']);
		
				
		if(trim($this->params['parentid']) != "")
		{
			$this->parentid  = $this->params['parentid']; //initialize paretnid
		}else
		{
			$errorarray['errormsg']='parentid missing';
			echo json_encode($errorarray); exit;
		}

		if(trim($this->params['module']) != "" && $this->params['module'] != null)
		{
			$this->module  = strtolower($this->params['module']); //initialize module
		}else
		{
			$errorarray['errormsg']='module missing';
			echo json_encode($errorarray); exit;
		}		
		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='data_city missing';
			echo json_encode($errorarray); exit;
		}

		if(trim($this->params['usercode']) != "" && $this->params['usercode'] != null)
		{
			$this->usercode  = $this->params['usercode']; //initialize usercode
		}
		
		if(trim($this->params['trace']) != "" && $this->params['trace'] != null)
		{
			$this->trace  = $this->params['trace']; //initialize usercode
		}
		
		if( trim($this->params['action']) == "fetchBudgetDetails" )
		{
			if( trim($this->params['companyname']) )
			{
				$this -> companyname = $this->params['companyname'];
			}
			
			if( trim($this->params['catid']) )
			{
				$this -> catid = $this->params['catid'];
			}
			
		}
		

		$this->setServers();		
	}		
	
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->fin   			= $db[$data_city]['fin']['master'];		
		
		if(strtoupper($this->module) == 'ME')
		{
			$this->conn_idc   	= $db[$data_city]['idc']['master'];
			$this->conn_local  	= $db[$data_city]['d_jds']['master'];
			$this->conn_temp 	= $this->conn_idc;
		}
		else if(strtoupper($this->module) == 'TME')
		{
			$this->conn_tme  	= $db[$data_city]['tme_jds']['master'];
			$this->conn_temp     = $this->conn_tme;
		}
		else
		{
			$this->conn_local  	= $db[$data_city]['d_jds']['master'];
			$this->conn_temp     = $this->conn_local;
		}
		
	    $this->configobj = new configclass();	
		$this->urldetails	= $this->configobj->get_url($data_city);
			
	}

	
	function fetchDealClosedetails()
	{
		
		
		$sql_fetch="SELECT a.parentid, companyname AS compname, b.entry_date, b.version, tmecode, mecode 
					FROM payment_otherdetails a JOIN payment_apportioning b 
					ON a.parentid =b.parentid 
					AND a.version=b.version 
					WHERE
					a.parentid ='".$this->parentid."'
					AND (a.version MOD 10) in (2, 3)
					GROUP BY a.parentid, a.version	
					ORDER BY b.entry_date 
					DESC  LIMIT 1";
		$res_fetch = parent::execQuery($sql_fetch, $this->fin);
		
		if($res_fetch && mysql_num_rows($res_fetch))
		{
			while( $row_fetch = mysql_fetch_assoc($res_fetch) )
			{
				$parentid_data[$row_fetch['parentid']]['date']    = $row_fetch['entry_date'];
				$parentid_data[$row_fetch['parentid']]['version'] = $row_fetch['version'];
				$parentid_data[$row_fetch['parentid']]['me_details']['empcode'] = $row_fetch['mecode'];
				
				if($row_fetch['mecode'])
				$empDetails = $this->fetEmpDetails($row_fetch['mecode']);
				
				
				$parentid_data[$row_fetch['parentid']]['me_details']['empname']   = $empDetails['data'][0]['empname'];
				$parentid_data[$row_fetch['parentid']]['me_details']['executive'] = $empDetails['data'][0]['empname'].'|'.$row_fetch['mecode'];
				
				$parentid_data[$row_fetch['parentid']]['me_details']['Title']  = $empDetails['data'][0]['section'];
				$parentid_data[$row_fetch['parentid']]['me_details']['status'] = ($empDetails['data'][0]['resign_flag'] == 0 && $empDetails['data'][0]['delete_flag'] == 1) ? 'Not Active' : 'Active';
				$parentid_data[$row_fetch['parentid']]['me_details']['mobile'] = $empDetails['data'][0]['mobile_num'];
				
			
			}
			$curl_url = $this->urldetails['jdbox_url']."services/fetchFinData.php";
			$post_data['parentid']  = $this->parentid;
			$post_data['data_city'] = $this->data_city;
			$post_data['action']    = 'fetchBalGaAmt';
			
			$parentid_data[$this->parentid]['finance_info'] = $this->curl_call($curl_url,$post_data);
			
			$parentid_data['error_code'] = 0;
			return $parentid_data;
		}
		else
		{
			$parentid_data['error_code'] = 1;
			$parentid_data['error_msg'] = 'No data Found';
			
			return $parentid_data;
		}	
		
	}
	
	function fetEmpDetails($empcode)
	{
			$post_data['empcode'] =  $empcode;
			$curl_url = SSO_MODULE_IP.":8080/api/fetch_employee_info.php?auth_token=TUFEasRsasqhasjhsasqNlaccuafasnsasTewqeqU";		
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$curl_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch,CURLOPT_POST, TRUE);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
			$company_data = json_decode(curl_exec($ch) , true) ;
			curl_close($ch);
			return $company_data;
	}

	function curl_call($curl_url,$post_data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$curl_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_POST, TRUE);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
		$res_data = json_decode(curl_exec($ch) , true) ;
		curl_close($ch);
		return $res_data;
	}
}



?>
