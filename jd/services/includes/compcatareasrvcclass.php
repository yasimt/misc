<?php

class compcatareasrvcclass extends DB
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
	var  $catsearch		= null;
	var  $data_city		= null;
	var  $campaignid 	= null;
	

	function __construct($params)
	{		
		$this->params = $params;		
		
		if(trim($this->params['parentid']) != "")
		{
			$this->parentid  = $this->params['parentid']; //initialize paretnid
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

		
		$this->setServers();
		//echo json_encode('const'); exit;
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->Iro    		= $db[$data_city]['iro']['master'];		
		$this->fin   			= $db[$data_city]['fin']['master'];
		

	}	

	
	function updateprimarytag()
	{
		
		if(trim($this->params['catidlist']))
		{		
			$catidlist = trim($this->params['catidlist']);
		}
		else
		{
			$returnarr['error']['code'] = 1;
			$returnarr['error']['msg'] = "catidlist missing";
			return $returnarr;
		}
		
				
		if(strlen($catidlist))
		{
			$sql_package = "update tbl_package_search set primary_tag=1 where parentid='".$this->parentid."' and catid in (".$catidlist.")";
			parent::execQuery($sql_package,$this->Iro);
			
			if (DEBUG_MODE)
			{
				echo '<br><b> Query:</b>' . $sql_package;
				echo '<br><b>Error:</b>' . $this->mysql_error;
			}
				
			$sql_nonpaid= "update tbl_nonpaid_search set primary_tag=1 where parentid='".$this->parentid."' and catid in (".$catidlist.")";
			parent::execQuery($sql_nonpaid,$this->Iro);
			
			if (DEBUG_MODE)
			{
				echo '<br><b> Query:</b>' . $sql_nonpaid;
				echo '<br><b>Error:</b>' . $this->mysql_error;
			}
		
		
		$message="updating primary_tag for catid - ".$catidlist;
		$this->log($this->parentid,$message,0);
		

		$returnarr['status']='successful';
		return $returnarr;
			
		}
		
	}
	
	
	
	function log($parentid,$message,$campaignid=null)
	{
		
		$this->log_tbl =" tbl_compcatarea_regen_log ";
		
		$sql="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$campaignid."','".addslashes(stripslashes($message))."')";	
		parent::execQuery($sql,$this->fin);
			
		if (DEBUG_MODE)
		{
			echo '<br><b> Query:</b>' . $sql;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}
		
		

	}
}



?>
