<?php

class validate_phonsearch_campaign_class extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $dbConIdc    	= null;
	var  $intermediate 	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	var  $version		= null;
	var  $sys_regfee_budget	= null;
	

	var  $catsearch	= null;
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	
	
	
	var	 $optvalset = array('ALL','ZONE','NAME','PIN','DIST');
	

	function __construct($params)
	{		
		$this->params = $params;		
		
		$errorarray['errorCode']= 1;
				
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
		
		if(trim($this->params['action']) != "" && $this->params['action'] != null)
		{
			$this->action  = $this->params['action']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='action missing';
			echo json_encode($errorarray); exit;
		}

		if(trim($this->params['usercode']) != "" && $this->params['usercode'] != null)
		{
			$this->usercode  = $this->params['usercode']; //initialize usercode
		}else
		{
			$errorarray['errormsg']='usercode missing';
			echo json_encode($errorarray); exit;
		}
		
		if(trim($this->params['trace']) != "" && $this->params['trace'] != null)
		{
			$this->trace  = $this->params['trace']; //initialize usercode
		}
		
		
		
		if(trim($this->params['genio_lite_daemon']) != "" && $this->params['genio_lite_daemon'] != null)
		{
			$this->genio_lite_daemon  = $this->params['genio_lite_daemon']; 
		}else
		{
			$this->genio_lite_daemon  =0;
		}

		$this->setServers();
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->db_budgeting   	= $db[$data_city]['db_budgeting']['master'];
		
		//print_r($this->fin);
		switch(strtolower(trim($this->module)))
		{
			case 'cs':		
				$this-> module_flag = 1;
			break;

			case 'tme':
				$this-> module_flag = 2;
			break;

			case 'me':
				$this-> module_flag = 3;
			break;
		}

	}

	function validatePincode()
	{		
		$errorarray['errorCode'] = 1;
		$errorarray['errormsg']  = 'pincodes missing';
		$sql="select pincodelist from tbl_contract_pincodelist where parentid='".$this->parentid."' and module=".$this->module_flag;
		$res_pin 	= parent::execQuery($sql, $this->db_budgeting);
		
		if($this->trace)
		{
			echo '<br>';
			echo 'sql :: '.$sql;
			echo '<br>rows :: '.mysql_num_rows($res_pin);
			echo '<br>';
		}
		
		if($res_pin && mysql_num_rows($res_pin) > 0)
		{
			while($row_pin = mysql_fetch_assoc($res_pin))
			{
				if(trim($row_pin['pincodelist']))
				{
					$pincode_arr = explode(",",trim($row_pin['pincodelist']));
					if(is_array($pincode_arr) && count($pincode_arr)>0)
					{
						$errorarray['errorCode'] = 0;
						$errorarray['errormsg']  = 'pincodes present';
						return $errorarray;
					}
					
				}
				
			}
		}
		
		$sql_lite = "select * from tbl_contract_pincodelist_lite where parentid='".$this->parentid."' and module=".$this->module_flag;
		$res_lite = parent::execQuery($sql_lite, $this->db_budgeting);
		
		if($this->trace)
		{
			echo '<br>';
			echo 'sql :: '.$sql_lite;
			echo '<br>rows :: '.mysql_num_rows($res_lite);
			echo '<br>';
		}
		
		if($res_lite && mysql_num_rows($res_lite) > 0)
		{
			$row_lite = mysql_fetch_assoc($res_lite);
			if(trim($row_lite['pincodelist']))
			{
				$sql="INSERT INTO tbl_contract_pincodelist set
					parentid='".$this->parentid."',
					module=".$this->module_flag.",
					pincodelist='".$row_lite['pincodelist']."',
					pincodejson='".$row_lite['pincodejson']."'
					ON DUPLICATE KEY UPDATE
					module=".$this->module_flag.",
					pincodelist='".$row_lite['pincodelist']."',
					pincodejson='".$row_lite['pincodejson']."'";
					
				$res = parent::execQuery($sql, $this->db_budgeting);
				
				if($this->trace)
				{
					echo '<br>';
					echo 'sql :: '.$sql;
					echo 'res :: '.$res;
					echo '<br>';
				}
				if($res)
				{
					$errorarray['errorCode'] = 0;
					$errorarray['errormsg']  = 'pincodes present';
					return $errorarray;
				}
						
			}
		
		}
		
		
			return $errorarray;
		

		
	}

	
	
	
}



?>
