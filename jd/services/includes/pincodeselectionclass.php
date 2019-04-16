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
		
		//mongo
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		
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
		$this->dbConDjds_slave	= $db[$data_city]['d_jds']['slave'];
		//$this->dbConIro_slave	= $db[$data_city]['iro']['slave'];
		$this->dbConIdc   		= $db[$data_city]['idc']['master'];		
		if(strtoupper($this->module) == 'ME'){
			if((in_array($this->params['ucode'], json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
		}
		
		if(strtoupper($this->module) == 'TME'){
			if((in_array($this->params['ucode'], json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($data_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
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
		
		$sql="select pincodelist from tbl_contract_pincodelist where parentid='".$this->parentid."' and module=".$this->ModuleVersion;
		$res_pin 	= parent::execQuery($sql, $this->dbConbudget);
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
		switch(strtoupper($this->module))
		{
			case 'TME':
			if($this->mongo_tme == 1)
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "pincode";
				$row_pincode = $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$pincodesql  ="SELECT pincode from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
				$res_pincode = parent::execQuery($pincodesql, $this->dbContme);
				if($res_pincode && mysql_num_rows($res_pincode)>0)
				{
					$row_pincode = mysql_fetch_assoc($res_pincode);
				}
			}
			break;
			case 'CS':
			$pincodesql  ="SELECT pincode from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
			$res_pincode = parent::execQuery($pincodesql, $this->dbConIro);
			if($res_pincode && mysql_num_rows($res_pincode)>0)
			{
				$row_pincode = mysql_fetch_assoc($res_pincode);
			}
			break;
			case 'ME':
			if($this->mongo_flag == 1)
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "pincode";
				$row_pincode = $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$pincodesql  ="SELECT pincode from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
				$res_pincode = parent::execQuery($pincodesql, $this->dbConIdc);
				if($res_pincode && mysql_num_rows($res_pincode)>0)
				{
					$row_pincode = mysql_fetch_assoc($res_pincode);
				}
			}
			break;
			default:
			die('Invalid module');
			break;
		}
		
		if(count($row_pincode)>0)
		{			
			if($row_pincode['pincode'])
			{
				$this->physical_pincode = $row_pincode['pincode'];
				$sql_chk_datacity="SELECT * FROM tbl_areamaster_consolidated_v3  where data_city='".$this->data_city."' AND  pincode='".$row_pincode['pincode']."'  AND display_flag=1  group by pincode";
				$res_area 	= parent::execQuery($sql_chk_datacity, $this->dbConDjds_slave);
				
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
		
		if(count($pincodelist_arr) <= 0)
		{
			$reason = (!$is_mypincode_datacity) ? 'Bform Pincode('.$this->physical_pincode.') does not belongs to '.$this->data_city.'' : 'Pincodes Not Found !';
			$returnarr['error_code']   = '1';
			$returnarr['status'] = 'Error - '.$reason.'';
			return $returnarr;
		}
		
		$pincodelist_arr = array_unique($pincodelist_arr);
		$pincodelist_arr = array_filter($pincodelist_arr);

		$pincodelist_str = implode(',',$pincodelist_arr);
		
		if($this->params['pincodejson'] =='' || $this->params['pincodejson'] =='null' || !isset($this->params['pincodejson']))
		{
			$pincodejson= ' concat(\'{"a_a_p":"\',pincodelist,\'","n_a_a_p":"\',pincodelist,\'"}\')'  ;		
		}else
		{
			$pincodejson= "'".$this->params['pincodejson']."'";
		}
		
		
		$sql="INSERT INTO tbl_contract_pincodelist set
				parentid='".$this->parentid."',
				module=".$this->ModuleVersion.",
				pincodelist='".$pincodelist_str."',
				pincodejson=".$pincodejson."
				ON DUPLICATE KEY UPDATE
				module=".$this->ModuleVersion.",
				pincodelist='".$pincodelist_str."',
				pincodejson=".$pincodejson."";
				
		$res_pincodelist = parent::execQuery($sql, $this->dbConbudget);
		
		
		if( strtolower(trim($this->params['sub_module'])) == 'geniolite' ) 
		{
			$sql_lite="INSERT INTO tbl_contract_pincodelist_lite set
					parentid='".$this->parentid."',
					module=".$this->ModuleVersion.",
					pincodelist='".$pincodelist_str."',
					pincodejson=".$pincodejson."
					ON DUPLICATE KEY UPDATE
					module=".$this->ModuleVersion.",
					pincodelist='".$pincodelist_str."',
					pincodejson=".$pincodejson."";
					
		 $res_pincodelist_lite = parent::execQuery($sql_lite, $this->dbConbudget);
		}
		
		
		if( $res_pincodelist ||  $res_pincodelist_lite )
		{
		
			$returnarr['error_code']   = '0';
			$returnarr['status']='sucessful';
		}
		else
		{
			$returnarr['error_code']   = '1';
			$returnarr['status']='Failed';
		}
		return $returnarr;
	}
	
	
	function setlisttojson()
	{		
		$cond= ' concat(\'{"a_a_p":"\',pincodelist,\'","n_a_a_p":"\',pincodelist,\'"}\') ' ;		
	
		$sql= "update tbl_contract_pincodelist set pincodejson=$cond where parentid='".$this->parentid."' and module=".$this->ModuleVersion." and (pincodejson='' or pincodejson is null) ";
		
		$res_pin 	= parent::execQuery($sql, $this->dbConbudget);

		$returnarr['status']='sucessful';
		return $returnarr;
	}

	function findPdgActive(){
		$check_pck 	=	"SELECT campaignid,budget FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND recalculate_flag=1 and campaignid in(1,2)";
		$check_res	=	parent::execQuery($check_pck, $this->dbContme);	
		if($check_res && mysql_num_rows($check_res)>0){
			while($fin_res = mysql_fetch_assoc($check_res)) {
				$fin_arr[$fin_res['campaignid']]['campaignid'] 	= $fin_res['campaignid'];  
				$fin_arr[$fin_res['campaignid']]['budget'] 		= $fin_res['budget'];  
			}
			if(($fin_arr['1']['budget'] > 0 && $fin_arr['2']['budget'] == 0) || (count($fin_arr) == 1 && $fin_arr['1']['budget'] > 0)){
				$pin_qry	=	"SELECT pincode_list,pincodejson FROM tbl_bidding_details_summary WHERE parentid='".$this->parentid."' AND version = '".$this->params['version']."'";
				$resbud 	= 	parent::execQuery($pin_qry, $this->dbConbudget);
				if($resbud && mysql_num_rows($resbud)>0){
					while($pin_res = mysql_fetch_assoc($resbud)) {
						$this->params['pincodelist'] = $pin_res['pincode_list'];
						$this->params['pincodejson'] = $pin_res['pincodejson'];
						$this->setPincode();
					}
				}
			}
		}
	}
	
	
	
}



?>
