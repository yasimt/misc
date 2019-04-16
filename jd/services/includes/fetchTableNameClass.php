<?php

class fetchTableNameClass extends DB
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
		
		
		$errorarray['error_code'] = 1;
		
		
		if(trim($this->params['action']) != "")
		{
			$this->action  = $this->params['action']; //initialize paretnid
		}else
		{
			$errorarray['errormsg']='action missing';
			echo json_encode($errorarray); exit;
		}
		
		if(trim($this->params['parentid']) != "")
		{
			$this->parentid  = trim($this->params['parentid']); //initialize paretnid
		}else
		{
			$errorarray['errormsg']='parentid missing';
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
		
		
		if(trim($this->params['trace']) != "" && $this->params['trace'] != null)
		{
			$this->trace  = $this->params['trace']; //initialize usercode
		}
			
	}		
	
	function fetchBiddingTableName( )
	{
		if($this->parentid)
		{
			$last_char = substr($this->parentid, -1);
			
			$response['error_code'] = 0;
			 
			if(is_numeric($last_char))
			{
				$response['bidding_table'] =  'tbl_bidding_details_'.$last_char;
			}
			else
			{
				$response['bidding_table'] =  'tbl_bidding_details_0';
			}
		}
		else
		{
			$response['error_code'] = 1;
			$response['errormsg']   ='parentid missing';
		}
		
		return $response;
	}

}



?>
