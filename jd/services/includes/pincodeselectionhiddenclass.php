<?php

class pincodeselectionclass extends DB
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
	var  $ucode		= null;
	

	var  $catsearch	= null;
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	
	
	
	//var	 $optvalset = array('ALL','ZONE','NAME','PIN','DIST');
	

	function __construct($params)
	{		
		$this->params = $params;		
		//print_r($params);		
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
		
		
		$this->setServers();
		$this->setModuleVersion();
		
		//echo json_encode('const'); exit;
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->dbConbudget  	= $db[$data_city]['db_budgeting']['master'];
		
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->dbContme			= $db[$data_city]['tme_jds']['master'];
		$this->dbConDjds		= $db[$data_city]['d_jds']['master'];
		$this->dbConIro			= $db[$data_city]['iro']['master'];
		$this->dbConIdc   		= $db[$data_city]['idc']['master'];		
		if(strtoupper($this->module) == 'ME'){
			if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
		}
	}	

	function setModuleVersion()
	{
		if(strtoupper($this->module) == 'CS') $this->ModuleVersion = 1;
		else if(strtoupper($this->module) == 'TME')   $this->ModuleVersion = 2;
		else if(strtoupper($this->module) == 'ME')    $this->ModuleVersion = 3;
		
	}
	
	function getPincode()
	{
		$pincodelist=array();
		$pincodelist['pincodelist']='';
		
		$sql="select pincodelist from tbl_contract_pincodelist_hidden where parentid='".$this->parentid."' and module=".$this->ModuleVersion;
		$res_pin 	= parent::execQuery($sql, $this->dbConDjds);
		$num_rows		= mysql_num_rows($res_pin);
		
		if($res_pin && $num_rows > 0)
		{
			while($row=mysql_fetch_assoc($res_pin))
			{
				$pincodelist['pincodelist']=$row['pincodelist'];
			}
		}
		
		
		$pincodelist['is_datacity_pincode'] = $this-> getPhysicalPincode();
		$pincodelist['physical_pincode'] = $this->physical_pincode;
		
		return $pincodelist;
	}
	
	function getPhysicalPincode()
	{
		
			$pincodesql  ="SELECT pincode from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
			$res_pincode = parent::execQuery($pincodesql, $this->dbConIro);
			
			if($res_pincode && mysql_num_rows($res_pincode)>0)
			{
				$row_pincode = mysql_fetch_assoc($res_pincode);
			}
			
		if(count($row_pincode)>0)
		{			
			if($row_pincode['pincode'])
			{
				$this->physical_pincode = $row_pincode['pincode'];
				$sql_chk_datacity="SELECT * FROM tbl_areamaster_consolidated_v3  where data_city='".$this->data_city."' AND  pincode='".$row_pincode['pincode']."'  AND display_flag=1  group by pincode";
				$res_area 	= parent::execQuery($sql_chk_datacity, $this->dbConDjds);
				
				if($res_area && mysql_num_rows($res_area)>0)
				return 1;
				else
				return 0;
				
			}else{
				die('Physical pincode is missing');
			}
			
		}else{
			die('data not available in shadow table');
		}
			
	}

	function setPincode()
	{

		$pincodelist_arr = explode(',',$this->params['pincodelist']);
		$is_mypincode_datacity = $this-> getPhysicalPincode();
		foreach($pincodelist_arr as $key => $pincode)
		{
			if(trim($pincode) == trim($this->physical_pincode) && !$is_mypincode_datacity)
			{
				unset($pincodelist_arr[$key]);
				
			}
			
		}
		
		$pincodelist_arr = array_unique($pincodelist_arr);
		$pincodelist_arr = array_filter($pincodelist_arr);

		$pincodelist_str = implode(',',$pincodelist_arr);
		
		$sql="INSERT INTO tbl_contract_pincodelist_hidden set
				parentid='".$this->parentid."',
				module=".$this->ModuleVersion.",
				pincodelist='".$pincodelist_str."',
				catlist='".$this->params['catlist']."'
				ON DUPLICATE KEY UPDATE
				module=".$this->ModuleVersion.",
				pincodelist='".$pincodelist_str."',
				catlist='".$this->params['catlist']."'";
				
		parent::execQuery($sql, $this->dbConDjds);
		

		$returnarr['status']='sucessful';
		return $returnarr;
	}
	
}



?>
