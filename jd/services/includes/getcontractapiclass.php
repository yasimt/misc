<?php

class getcontractapiclass extends DB
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
	var  $sys_regfee_budget	= null;
	

	var  $catsearch	= null;
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	
	
	
	var	 $optvalset = array('ALL','ZONE','NAME','PIN','DIST');
	

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

		if(trim($this->params['usercode']) != "" && $this->params['usercode'] != null)
		{
			$this->usercode  = $this->params['usercode']; //initialize usercode
		}else
		{
			$errorarray['errormsg']='usercode missing';
			echo json_encode($errorarray); exit;
		}

		$this->setServers();		
	}		
	
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
			
		$this->fin   			= $db[$data_city]['fin']['master'];		
		$this->db_budgeting   	= $db[$data_city]['db_budgeting']['master'];
		$this->dbConDjds  		= $db[$data_city]['d_jds']['master'];		
	}

	function updatetemptable()
	{
		$versionInitClass_obj = new versionInitClass($this->params);		
		$result = $versionInitClass_obj->setversion();
		$this->version=$result['version'];
		
		$pincodevaluse= $this->getshadowpincodelist();
		$this->params['pincodelist']= $pincodevaluse; // pincode list
		
		$pincodeselectionclassobj = new pincodeselectionclass($this->params);
		$result = $pincodeselectionclassobj->setPincode();

		$budgetinitclass_obj = new budgetinitclass($this->params);
		$result = $budgetinitclass_obj->initBudget();

		$this->update_tbl_bidding_details_intermediate();
		$returnarr['error']['code'] = 0;
		$returnarr['error']['msg'] = "Successful";
		return $returnarr;
	}

	function getshadowpincodelist()
	{		
		// we give the preference to tbl_bidding_details_shadow and if we do not get result then we will search inside tbl_bidding_details
		$pincodelist='';
		$sqlpincodelist = "select group_concat(distinct(pincode)) as pincodelist from tbl_bidding_details_shadow where parentid='".$this->parentid."'";		
		$res_pin 	= parent::execQuery($sqlpincodelist, $this->db_budgeting);
		$num_rows		= mysql_num_rows($res_pin);
		if($res_pin && $num_rows > 0)
		{			
			$row=mysql_fetch_assoc($res_pin);			
			$pincodelist=$row['pincodelist'];			
			
		}
		
		if($pincodelist=='')
		{
			$sqlpincodelist = "select group_concat(distinct(pincode)) as pincodelist from tbl_bidding_details where parentid='".$this->parentid."'";
			$res_pin 	= parent::execQuery($sqlpincodelist, $this->fin);
			$num_rows		= mysql_num_rows($res_pin);
			$row=mysql_fetch_assoc($res_pin);			
			$pincodelist=$row['pincodelist'];
		}

		if($pincodelist=='')
		{
			$sqlpincodelist = "select group_concat(distinct(pincode)) as pincodelist from tbl_bidding_details_expired where parentid='".$this->parentid."'";
			$res_pin 	= parent::execQuery($sqlpincodelist, $this->fin);
			$num_rows		= mysql_num_rows($res_pin);
			$row=mysql_fetch_assoc($res_pin);			
			$pincodelist=$row['pincodelist'];
		}

		if($pincodelist!='')
		{
			$sqlpincodelist = "select group_concat(distinct(pincode) ORDER BY pincode ) as pincodelist from tbl_areamaster_consolidated_v3 where pincode in (".$pincodelist.") AND type_flag=1 AND display_flag=1 AND broader_area_flag=0 AND de_display=1 ";
			$res_pin 	= parent::execQuery($sqlpincodelist, $this->dbConDjds);
			$num_rows		= mysql_num_rows($res_pin);
			$row=mysql_fetch_assoc($res_pin);			
			$pincodelist=$row['pincodelist'];
		}
		
		return $pincodelist;
	}

	function update_tbl_bidding_details_intermediate()
	{
		return ; // as per discussion with sunny we will not put data on getcontract into tbl_bidding_details_intermediate 
		
		// we give the preference to tbl_bidding_details_shadow and if we do not get result then we will search inside tbl_bidding_details
		// fetch the latest version based on time

		$resultarr= array();
		$national_catid_arr= array();
		$catid_budget_arr= array();
		
		$fetchlatestversion_sql = "select version,updatedon from tbl_bidding_details_shadow where parentid='".$this->parentid."' group by updatedon order by updatedon desc limit 1";
		$res_fetchlatestversion 	= parent::execQuery($fetchlatestversion_sql,$this->db_budgeting);
		$num_rows		= mysql_num_rows($res_fetchlatestversion);

		if($num_rows) // if data is present on tbl_bidding_details_shadow
		{
			$arr_fetchlatestversion = mysql_fetch_assoc($res_fetchlatestversion);
			$latestversion_val	= $arr_fetchlatestversion['version'];
			
			$tbl_bidding_details_shadow_sql = "select parentid,version,catid,national_catid,pincode,position_flag,inventory,bidvalue,callcount,sys_budget,actual_budget from tbl_bidding_details_shadow where parentid='".$this->parentid."' and version ='".$latestversion_val."' ";
			$tbl_bidding_details_shadow_res = parent::execQuery($tbl_bidding_details_shadow_sql,$this->db_budgeting);
						
			while($row = mysql_fetch_assoc($tbl_bidding_details_shadow_res))
			{				
				$national_catid_arr[$row['catid']]	=$row['national_catid'];
				$catid_budget_arr[$row['catid']]	=$catid_budget_arr[$row['catid']]+$row['actual_budget'];	
								
				$resultarr[$row['catid']][$row['pincode']] = array('cnt_f'=>$row['callcount'],'pin'=>$row['pincode'],'pos'=>$row['position_flag'],'bidvalue'=>$row['bidvalue'],'budget'=>$row['actual_budget'],'inv'=>$row['inventory']);
			}
		}else // there is no entry inside tbl_bidding_details_shadow so we will check from tbl_bidding_details
		{		
			$tbl_bidding_details_sql = "select parentid,version,catid,national_catid,pincode,position_flag,inventory,bidvalue,callcount,sys_budget,actual_budget from tbl_bidding_details where parentid='".$this->parentid."' ";
			$tbl_bidding_details_res = parent::execQuery($tbl_bidding_details_sql,$this->fin);
						
			while($row = mysql_fetch_assoc($tbl_bidding_details_res))
			{				
				$national_catid_arr[$row['catid']]=$row['national_catid'];				
				$resultarr[$row['catid']][$row['pincode']] = array('cnt_f'=>$row['callcount'],'pin'=>$row['pincode'],'pos'=>$row['position_flag'],'bidvalue'=>$row['bidvalue'],'budget'=>$row['actual_budget'],'inv'=>$row['inventory']);
			}	
		}


		//print_r($resultarr);
		
		if(count($resultarr))
		{
			$deletesql="DELETE from tbl_bidding_details_intermediate where parentid ='".$this->parentid."'";
			parent::execQuery($deletesql,$this->db_budgeting);
			
			foreach($resultarr as $catid=>$catidarr)
			{	
				$insertsql= "INSERT INTO tbl_bidding_details_intermediate SET
				parentid 		= '".$this->parentid."',
				version			= '".$this->version."',	
				catid			= '".$catid."',	
				national_catid	= '".$national_catid_arr[$catid]."',
				pincode_list	= '".json_encode($catidarr)."',
				cat_budget		= '".$catid_budget_arr[$catid]."',
				updatedby		= '".addslashes($this->usercode)."',
				updatedon		= '".date('Y-m-d H:i:s')."'
				
				ON DUPLICATE KEY UPDATE
				
				pincode_list	= '".json_encode($catidarr)."',
				cat_budget		= '".$catid_budget_arr[$catid]."',
				updatedby		= '".addslashes($this->usercode)."',
				updatedon		= '".date('Y-m-d H:i:s')."'";

				//echo "<br>".$insertsql;
				parent::execQuery($insertsql,$this->db_budgeting);
			}
		}

		
		
	}


}



?>
