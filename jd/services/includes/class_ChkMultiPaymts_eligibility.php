<?php

class class_ChkMultiPaymts_eligibility extends DB
{	
	
	var  $dbConFin    	= null;	
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $parentid		= null;
	var  $data_city	= null;
	

	function __construct($params)
	{		
		$this->params = $params;		
		
		$parentid 		  = trim($params['parentid']);
		$data_city 		  = trim($params['data_city']);
		
		
		$errorarray['errorCode'] = 1;
		
		
		if(trim($this->params['action']) != "")
		{
			$this->action  = $this->params['action']; //initialize paretnid
		}else
		{
			$errorarray['msg']='action missing';
			echo json_encode($errorarray); exit;
		}
		
		if(trim($this->params['parentid']) != "")
		{
			$this->parentid  = $this->params['parentid']; //initialize paretnid
		}else
		{
			$errorarray['msg']='parentid missing';
			echo json_encode($errorarray); exit;
		}		
		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}else
		{
			$errorarray['msg']='data_city missing';
			echo json_encode($errorarray); exit;
		}
		
		if( trim(strtolower($this->params['action'])) == "resetexistingrequest" )
		{
			
			if(trim($this->params['version']) != "" && $this->params['version'] != null)
			{
				$this->version  = $this->params['version']; //initialize datacity
			}else
			{
				$errorarray['msg']='version missing';
				echo json_encode($errorarray); exit;
			}
			
		}
		
		if(trim($this->params['trace']) != "" && $this->params['trace'] != null)
		{
			$this->trace  = $this->params['trace']; //initialize usercode
		}
		
		
		$this->setServers();		
	}		
	
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		
		$this->fin   			= $db[$data_city]['fin']['master'];		
		//$this->dbConbudget      = $db[$data_city]['db_budgeting']['master'];

	}
	
	function isEligible()
	{	
		
		
		$sql_chk_Elg = " SELECT * 
						FROM tbl_payments_allowed_contracts_temp 
						WHERE parentid='".$this->parentid."'  
						AND mul_instrument_flag = 1";
		$res_chk_Elg =  parent::execQuery($sql_chk_Elg, $this->fin);
	   
	    if($this->trace)
	    {
			echo '<br>';
			echo 'sql :: '.$sql_chk_Elg;
			echo '<br>';
			echo '<br>'.$res_chk_Elg;
			echo '<br>';
			echo 'rows :: '.mysql_num_rows($res_chk_Elg);
			echo '<br>';
			echo '<br>';
	    }
	    
	    if( $res_chk_Elg && mysql_num_rows($res_chk_Elg)>0 )
	    {
			$isEligible_resp['errorCode'] = '0';
			$isEligible_resp['isEligible']= '1';
		}
		else
		{
			$isEligible_resp['errorCode'] = '0';
			$isEligible_resp['isEligible']= '0';
		}
		
		return $isEligible_resp;
	}
	
	function isEligibleBalAdj()
	{	
		
		
		$sql_chk_Elg = " SELECT * 
						FROM tbl_payments_allowed_contracts_temp 
						WHERE parentid='".$this->parentid."'  
						AND bal_adjustment_flag = 1 ";
		$res_chk_Elg =  parent::execQuery($sql_chk_Elg, $this->fin);
	   
	    if($this->trace)
	    {
			echo '<br>';
			echo 'sql :: '.$sql_chk_Elg;
			echo '<br>';
			echo '<br>'.$res_chk_Elg;
			echo '<br>';
			echo 'rows :: '.mysql_num_rows($res_chk_Elg);
			echo '<br>';
			echo '<br>';
	    }
	    
	    if( $res_chk_Elg && mysql_num_rows($res_chk_Elg)>0 )
	    {
			$isEligible_resp['errorCode'] = '0';
			$isEligible_resp['isEligible']= '1';
		}
		else
		{
			$isEligible_resp['errorCode'] = '0';
			$isEligible_resp['isEligible']= '0';
		}
		
		return $isEligible_resp;
	}
	
	
	function resetExistingRequest()
	{
		$sql_get_temp = " SELECT * 
							 FROM tbl_payments_allowed_contracts_temp
							 WHERE parentid='".$this->parentid."' ";
							 
		$res_get_temp =  parent::execQuery($sql_get_temp, $this->fin);
	   
	    if($this->trace)
	    {
			echo '<br>';
			echo 'sql :: '.$sql_get_temp;
			echo '<br>';
			echo '<br>'.$res_get_temp;
			echo '<br>';
			echo 'rows :: '.mysql_num_rows($res_get_temp);
			echo '<br>';
			echo '<br>';
	    }
	    
	    $campaign_wise_balance = array();
	    if( $res_get_temp && mysql_num_rows($res_get_temp)>0 )
	    {
			$insert_main = "INSERT INTO payments_allowed_contracts (parentid, bal_adjustment_flag, mul_instrument_flag, dealclose_flag, entry_date, ucode, uname, city, reason, requested_by, approved_by, VERSION, budget_option, festive_combo_bal)
			(SELECT parentid, bal_adjustment_flag, mul_instrument_flag, dealclose_flag, entry_date, ucode, uname, city, reason, requested_by, approved_by, ".$this->version.", budget_option, festive_combo_bal
			FROM tbl_payments_allowed_contracts_temp
			WHERE parentid='".$this->parentid."')";
			$res_main =  parent::execQuery($insert_main, $this->fin);
			if($this->trace)
			{
				echo '<br>';
				echo 'sql :: '.$insert_main;
				echo '<br>';
				echo '<br>'.$res_main;
				echo '<br>';
				echo '<br>';
			}
			if($res_main)
			{
				$del_temp = "DELETE FROM tbl_payments_allowed_contracts_temp
							 WHERE parentid='".$this->parentid."' ";
				$res_del_temp =  parent::execQuery($del_temp, $this->fin);
				
				if($this->trace)
				{
					echo '<br>';
					echo 'sql :: '.$del_temp;
					echo '<br>';
					echo '<br>'.$res_del_temp;
					echo '<br>';
					echo '<br>';
				}
				
				if($res_del_temp)
				{
					$resetExistingRequest_res['code'] = 200;
					$resetExistingRequest_res['msg']  = 'data inserted';
				}
				else
				{
					$resetExistingRequest_res['code'] = 500;
					$resetExistingRequest_res['msg']  = 'delete query failed';
				}
				
			}else
			{
				$resetExistingRequest_res['code'] = 500;
				$resetExistingRequest_res['msg']  = 'main table query failed';
			}
			

		}else
		{
			$resetExistingRequest_res['code'] = 404;
			$resetExistingRequest_res['msg']  = 'no request found';
		}
		
		   return $resetExistingRequest_res;
	}
	

}



?>
