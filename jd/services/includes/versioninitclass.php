<?php
/**
 * Filename : catdetailsclass.php
 * Date		: 19/08/2013
 * Author	: pramesh
 
 * */
class versionInitClass extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $Idc	    	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	var  $usercode		= null;
	

	var  $module	= null;
	var  $data_city	= null;
	var  $ModuleVersion=null;
	
	
	//minpinbdgt - minimum category pincode budget for that catid and pincode for b2c category only 
	 	
	
	
	

	function __construct($params)
	{		
		$this->params = $params;				

		if(trim($this->params['parentid']) != "")
		{
			$this->parentid  = $this->params['parentid']; //initialize paretnid
		}else
		{
			{echo json_encode('Please provide parentid'); exit; }
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
		
		$this->usercode = $this->params['usercode'];
		
		//mongo
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		
		$this->setServers();
		//echo json_encode('const'); exit;
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->dbConDjds  		= $db[$data_city]['d_jds']['master'];
		$this->Idc   			= $db[$data_city]['idc']['master'];
		$this->fin   			= $db[$data_city]['fin']['master'];
		$this->tme_jds   		= $db[$data_city]['tme_jds']['master'];
		$this->dbConbudget  	= $db[$data_city]['db_budgeting']['master'];


		
		//echo "<pre>"; print_r($this->Idc);
		
		switch($this->module)
		{
			case 'cs':
			$this->tempconn = $this->fin;
			break;
			
			case 'tme':
			$this->tempconn = $this->tme_jds;
			if((in_array($this->usercode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($data_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}

			break;

			case 'me':
			$this->tempconn = $this->Idc;
			if((in_array($this->usercode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
			break;
		}	
	}


	function setversion()
	{
		$version = $this->fetchVersion($this->parentid,0);
		
		$version = $this->incrementVersion($this->parentid , $version);
		if($this->params['page'] == 'bform'){
			$result['version'] = $version;
			return $result;
		}

		$tbl_temp_intermediate_update ="INSERT INTO tbl_temp_intermediate set										
										parentid='".$this->parentid."',
										version ='".$version."'
										ON DUPLICATE KEY UPDATE
										version ='".$version."' ";
		switch($this->module)
		{
			case 'tme':
			//$tbl_temp_intermediate_update = $tbl_temp_intermediate_update."/* TMEMONGOQRY */";			
			//parent::execQuery($tbl_temp_intermediate_update, $this->tme_jds);			
			break;

			case 'cs':
			parent::execQuery($tbl_temp_intermediate_update,$this->dbConDjds);			
			break;
			
			case 'me':			
			//parent::execQuery($tbl_temp_intermediate_update, $this->Idc);			
			break;
		}
		
		//mongo query
		if($this->mongo_flag == 1 || $this->mongo_tme == 1)
		{
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_data = array();
			$intermd_tbl = "tbl_temp_intermediate";
			$intermd_upt = array();
			$intermd_upt['version'] = $version;
			$mongo_data[$intermd_tbl]['updatedata'] = $intermd_upt;
			
			$mongo_inputs['table_data'] = $mongo_data;
			$this->mongo_obj->updateData($mongo_inputs);
		}
		
		$result['version'] = $version;
		return $result;
	}

	function getModuleVersion()
	{
		if(strtoupper($this->module) == 'CS') $version = 1;
		else if(strtoupper($this->module) == 'TME')   $version = 2;
		else if(strtoupper($this->module) == 'ME')    $version = 3;
		$this->ModuleVersion = $version;
		return $version;
	}
	

	function fetchVersion($stop_die=0)
	{
		$module_version = $this->getModuleVersion();

		$qry = "SELECT version FROM payment_version WHERE parentid = '" . $this->parentid . "' AND module = '" . $module_version . "' ";

		$res = parent::execQuery($qry,$this->fin);

		if(mysql_num_rows($res))
		{
			$row = mysql_fetch_array($res);
			$version = $row['version'];
			if(trim($version)==''){
				$version = $this->getModuleVersion();
			}
		}
		else
		{
			$version = $this->getModuleVersion();
		}
				
		if($this->versionSanity($version))
			return $version;
		else {
			if ($stop_die==0) die("Error! Please contact S/W team- [payment_app]");
		}
	}

	function versionSanity($version)
	{
		switch($version%10)
		{
			case 1:
				$sane = strtolower($this->module) == 'cs'  ? true : false;
				break;
			case 2:
				$sane = strtolower($this->module) == 'tme' ? true : false;
				break;
			case 3:
				$sane = strtolower($this->module) == 'me'  ? true : false;
				break;
			default:
				$sane = false;
		}
		return $sane;
	}

	function incrementVersion($parentid, $version)       //$version is current version. It has to be incremented by 10
	{
		$module_version         = $this->getModuleVersion();
		$incremented_version    = $version+10;

		$checkVersion           = "SELECT version FROM payment_apportioning WHERE parentid='".$this->parentid."'  AND version='".$incremented_version."' LIMIT 1";
		$resCheckVersion        = parent::execQuery($checkVersion,$this->fin);
		$numCheckVersion        = mysql_num_rows($resCheckVersion);

		if($numCheckVersion)
		{
			$sqlFetchMaxVersion = "SELECT MAX(version*1) AS max_version FROM payment_apportioning WHERE parentid='".$this->parentid."' AND (version%10)='".$module_version."'";
			$resFetchMaxVersion     = parent::execQuery($sqlFetchMaxVersion,$this->fin);
			$rowFetchMaxVersion     = mysql_fetch_assoc($resFetchMaxVersion);
			$newIncrementedVersion  = $rowFetchMaxVersion['max_version']+10;
		}
		else{
			$newIncrementedVersion  = $incremented_version;
		}		
		
		return ($newIncrementedVersion);
	}

	function getversion()
	{
		$summary_version_sql 	="select version from tbl_temp_intermediate where parentid='".$this->parentid."'";
		//$summary_version_rs 	=  parent::execQuery($summary_version_sql, $this->intermediate);

		switch($this->module)
		{
			case 'tme':		
			if($this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_temp_intermediate";
				$mongo_inputs['fields'] 	= "version";
				$summary_version_arr = $this->mongo_obj->getData($mongo_inputs);
			}else{			
				$summary_version_rs= parent::execQuery($summary_version_sql, $this->tme_jds);
				$summary_version_arr = mysql_fetch_assoc($summary_version_rs);			
			}
			break;

			case 'cs':
			$summary_version_rs= parent::execQuery($summary_version_sql,$this->dbConDjds);
			$summary_version_arr = mysql_fetch_assoc($summary_version_rs);			
			break;
			
			case 'me':
			if($this->mongo_flag == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_temp_intermediate";
				$mongo_inputs['fields'] 	= "version";
				$summary_version_arr = $this->mongo_obj->getData($mongo_inputs);
			}else{			
				$summary_version_rs = parent::execQuery($summary_version_sql, $this->Idc);
				$summary_version_arr = mysql_fetch_assoc($summary_version_rs);			
			}
			break;
		}
		

		$result['version'] = $summary_version_arr['version'];
		return $result;
	}
	
}



?>
