<?php
/**
 * Filename : catdetailsclass.php
 * Date		: 19/08/2013
 * Author	: pramesh
 
 * */
class nationallistingclass extends DB
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
	

	var  $module	= null;
	var  $data_city	= null;
	
	
	
	//minpinbdgt - minimum category pincode budget for that catid and pincode for b2c category only 
	 	
	
	
	

	function __construct($params)
	{		
		$this->params = $params;				
		
		$errorarray['errorCode'] = "1";
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
		
		if(trim($this->params['action']) == 'InsertGenioLite' && trim($this->params['data_city']) != "" && strtolower(trim($this->params['data_city'])) == 'remote' )
		{
			
			$errorarray['errorMsg']  = "Data not inserted";
			
			echo json_encode($errorarray); exit;
		}
		
		if(trim($this->params['action']) == 'calcupdatedata')
		{
			if(trim($this->params['budget']) != "" && $this->params['budget'] != null)
			{
				$this->budget  = $this->params['budget']; //initialize datacity
			}else
			{
				$errorarray['errormsg']='budget missing';
				echo json_encode($errorarray); exit;
			}
			
			if(trim($this->params['tenure']) != "" && $this->params['tenure'] != null)
			{
				$this->tenure  = $this->params['tenure']; //initialize datacity
			}else
			{
				$errorarray['errormsg']='tenure missing';
				echo json_encode($errorarray); exit;
			}
			
			if(trim($this->params['recalculate_flag']) != "" && $this->params['recalculate_flag'] != null)
			{
				$this->recalculate_flag  = $this->params['recalculate_flag']; //initialize datacity
			}
			else
			{
				$errorarray['errormsg']='recalculate_flag missing';
				echo json_encode($errorarray); exit;
			}

			if(trim($this->params['usercode']) != "")
			{
				$this->usercode  = $this->params['usercode']; //initialize paretnid
			}

		}
		
		if(trim($this->params['action']) == 'fetchtempdata')
		{
			
			if(trim($this->params['version']) != "" && $this->params['version'] != null)
			{
				$this->version  = $this->params['version']; //initialize datacity
			}
			
		}
		
		if(trim($this->params['action']) == 'InsertSelectedCites')
		{
			
			if(trim($this->params['selected_cities']) != "" && $this->params['selected_cities'] != null)
			{
				$this->selected_cities  = $this->params['selected_cities']; //initialize selected cities
			}
			else
			{
				$errorarray['code']    ='404';
				$errorarray['errormsg']='selected_cties missing';
				echo json_encode($errorarray); exit;
			}
			
		}
		
		
		if(trim($this->params['trace']) != "")
		{
			$this->trace  = $this->params['trace']; //initialize paretnid
		}
		
		//mongo
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		
		$this->setServers();
		$this->categoryClass_obj = new categoryClass();
		
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
		$this->tme_jds   		= $db[$data_city]['tme_jds']['master'];		
		$this->fin_con   		= $db[$data_city]['fin']['master'];
		
		switch(strtolower($this->module))
		{
			case 'cs':
			$this->tempconn  = $this->dbConDjds;
			$this->local_obj = $this->dbConDjds;
			break;
			
			case 'tme':
			$this->tempconn  = $this->tme_jds;
			$this->local_obj = $this->dbConDjds;
			if((in_array($this->usercode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($data_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}

			break;

			case 'me':
			$this->tempconn  = $this->Idc;
			$this->local_obj = $this->Idc;
			if((in_array($this->usercode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
			break;
		}
	
	}


	

	function isnationallisting()
	{
		$result['nationallisting'] = 0;		
		
		$sql_national="SELECT * FROM tbl_national_listing_temp WHERE parentid='" . $this->parentid . "'";
		
		$qry_national 	= parent::execQuery($sql_national,$this->tempconn);
		if($qry_national && mysql_num_rows($qry_national))
		{
			$row_national= mysql_fetch_assoc($qry_national);
			if(count($row_national)){
				$result['nationallisting'] = 1;
				$result['nationallisting_type'] = $row_national['state_zone'];
				$result['Category_nationalid']	= str_replace('|P|',',',trim($row_national['Category_nationalid'],'|P|'));
				$result['eligible_flag'] = 1;
			}
		}
		
		if(strtolower($this->module) == 'cs')
		{
			
			$sqlFlag = "select dotcom from tbl_temp_intermediate where parentid ='".$this->parentid."'";
			$qryFlag = parent::execQuery($sqlFlag,$this->tempconn);
			
			if($qryFlag && mysql_num_rows($qryFlag)>0)
			{
				$row_Flag = mysql_fetch_assoc($qryFlag);
				
				if($row_Flag['dotcom'] == 1)
				$result['com'] = 1;
				else
				$result['com'] = 0;
			}
			else
			{
				$result['com'] = 0;	
			}
			
			
			$sqlFlag1 = "select * from tbl_companymaster_finance_temp where parentid ='".$this->parentid."' and campaignid =17";
			$qryFlag1 = parent::execQuery($sqlFlag1,$this->fin_con);
			
			if($qryFlag1 && mysql_num_rows($qryFlag1)>0)
			{
				$result['hidden'] = 1;
			}
			else
			{
				$result['hidden'] = 0;	
			}
			
			
		}
		
		
		if($result['Category_nationalid']) {
		$excl = "SELECT category_name, min(category_scope) as distr, count(national_catid) FROM tbl_categorymaster_generalinfo WHERE national_catid in (" . $result['Category_nationalid'] . ") and isdeleted = 0 AND mask_status=0 AND (category_scope = 1 or category_scope = 2) GROUP BY category_name";
        
        //$excl_national 	= parent::execQuery($excl,$this->local_obj);
    		$cat_params = array();
			$cat_params['page'] 		= 'nationallistingclass';
			$cat_params['parentid'] 	= $this->parentid;
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'category_name,category_scope';

			$where_arr  	=	array();
			if($result['Category_nationalid']!=''){
				$where_arr['national_catid']	= $result['Category_nationalid'];
				$where_arr['isdeleted']			= '0';
				$where_arr['mask_status']		= '0';
				$where_arr['category_scope']	= '1,2';
				$cat_params['where']			= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
			{
				/*$distr_arr  =array();
				foreach($cat_res_arr['results'] as $key =>$row_excl)
				{
					$distr_arr[] = $row_excl['category_scope'];
				}
				// in_arr (1,2) & not_in (0)
				if($min_distr != '1' && $min_distr != '2')
				{
					$result['eligible_flag'] = 0;	
				}*/

				
				
			}
			else
			{
				$result['eligible_flag'] = 0;
			}
		}
		else
		{
			$result['eligible_flag'] = 0;
		}
		
		
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_business_temp_data";
				$mongo_inputs['fields'] 	= "catIds,nationalcatIds";
				$row_temp_data = $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$sql_temp_data = "SELECT catIds,nationalcatIds FROM tbl_business_temp_data WHERE contractid='" . $this->parentid . "'";
				$res_temp_data = parent::execQuery($sql_temp_data,$this->tempconn);
				if($this->trace)
				{
					echo "<br> sql :: ".$sql_temp_data;
					echo "<br> res :: ".$res_temp_data;
					echo "<br> rows:: ".mysql_num_rows($res_temp_data);
				}
				if($res_temp_data && mysql_num_rows($res_temp_data)>0){
					$row_temp_data = mysql_fetch_assoc($res_temp_data);
				}
			}
			
			
		if($row_temp_data['nationalcatIds']) {	
		//$excl = "SELECT category_name, min(category_scope) as distr, count(national_catid) FROM tbl_categorymaster_generalinfo WHERE national_catid in (" . str_replace('|P|',',',trim($row_temp_data['nationalcatIds'],'|P|')). ") and isdeleted = 0 AND mask_status=0 AND (category_scope = 1 or category_scope = 2) GROUP BY category_name";
		
		$national_catid	=	str_replace('|P|',',',trim($row_temp_data['nationalcatIds'],'|P|'));
        //$excl_national 	= parent::execQuery($excl,$this->local_obj);
			$cat_params['page'] 		= 'nationallistingclass';
			$cat_params['parentid'] 	= $this->parentid;
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'category_scope,category_name';

			$where_arr  	=	array();
			if($national_catid!=''){
				$where_arr['national_catid'] 	= $national_catid;
				$where_arr['isdeleted'] 		= '0';
				$where_arr['mask_status'] 		= '0';
				$where_arr['category_scope'] 	= '1,2';
				$cat_params['where']			= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}
			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			{
				//~ if($row_excl['distr'] != '1' && $row_excl['distr'] != '2')
				//~ {
					//~ $result['eligible_flag'] = 0;
					//~ $result['Nonpaid_cat']   = 1;	
				//~ }				
			}
			else
			{
				$result['eligible_flag'] = 0;
				$result['Nonpaid_cat']   = 1;
			}
		}
			
		return $result;
	
	}
	
	function mini_balance()
	{
		
		$sql_min_national = "SELECT minbudget_national FROM tbl_business_uploadrates WHERE city = '" . $this->data_city . "'";
		$res_min_national = parent::execQuery($sql_min_national,$this->dbConDjds);
		
		if($res_min_national && mysql_num_rows($res_min_national)>0)
		{
			$row_min_national = mysql_fetch_assoc($res_min_national);	
		}
		else
		{
			$row_min_national['minbudget_national'] = 0;
		}
		
		return $row_min_national['minbudget_national'];
	}

	function getNationalListingTempData()
	{
		
		$sql_national_temp = "SELECT * FROM tbl_national_listing_temp WHERE parentid='" . $this->parentid . "'";
		$res_national_temp = parent::execQuery($sql_national_temp,$this->tempconn);
		if($this->trace)
		{
			echo "<br> sql :: ".$sql_national_temp;
			echo "<br> res :: ".$res_national_temp;
			echo "<br> rows:: ".mysql_num_rows($res_national_temp);
		}
		
		
		if($res_national_temp && mysql_num_rows($res_national_temp)>0)
		{
			$row_national_temp = mysql_fetch_assoc($res_national_temp);
			
			if($this->trace)
			{
				echo "<pre> ";
				print_r($row_national_temp);
			}
			$city_array = explode(',',trim(str_replace('|#|', ',',$row_national_temp['Category_city']),','));
			
			$sql_finance_temp = "SELECT * FROM tbl_companymaster_finance_temp WHERE parentid='" . $this->parentid . "' AND campaignid = '10' AND sphinx_id!=''";
			
			if(strtolower($this->module) == 'cs')
			$res_finance_temp  = parent::execQuery($sql_finance_temp, $this->fin_con);
			else
			$res_finance_temp  = parent::execQuery($sql_finance_temp,$this->tempconn);
			if($this->trace)
			{
				echo "<br> sql :: ".$sql_finance_temp;
				echo "<br> res :: ".$res_finance_temp;
				echo "<br> rows:: ".mysql_num_rows($res_finance_temp);
			}
			if($res_finance_temp && mysql_num_rows($res_finance_temp)>0) {
				$row_finance_temp = mysql_fetch_assoc($res_finance_temp);
			}
			if($this->trace)
			{
				echo "<pre> ";
				print_r($row_finance_temp);
			}
			
			switch($row_national_temp['state_zone'])
			{
				case 1:
				$listing_type = "Zone Wise Listing"; 
				break;
				case 2:
				$listing_type = "State Wise Listing";
				break;
				case 3:
				$listing_type = "Top City Listing";
				break;
				default:
				$listing_type = "State City Listing";  //default state listing
				break;
			}
			
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_business_temp_data";
				$mongo_inputs['fields'] 	= "catIds";
				$row_temp_data = $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$sql_temp_data = "SELECT catIds FROM tbl_business_temp_data WHERE contractid='" . $this->parentid . "'";
				$res_temp_data = parent::execQuery($sql_temp_data,$this->tempconn);
				if($this->trace)
				{
					echo "<br> sql :: ".$sql_temp_data;
					echo "<br> res :: ".$res_temp_data;
					echo "<br> rows:: ".mysql_num_rows($res_temp_data);
				}
				if($res_temp_data && mysql_num_rows($res_temp_data)>0){
					$row_temp_data = mysql_fetch_assoc($res_temp_data);
				}
			}
			
			if(count($row_temp_data)>0)
			{	
				$catid_id_listOther   = trim($row_temp_data['catIds'],'|P|');
				$catidArrExisting 	  = explode('|P|',$catid_id_listOther);
				$catidArrExisting 	  = array_filter($catidArrExisting);
				if($this->trace)
				{
					echo "<pre> categories :: ";
					print_r($row_temp_data);
					print_r($catidArrExisting);
					echo count($catidArrExisting);
				}
			}			
			
			$sql_finance_national_main = "SELECT balance, expired, bid_perday, campaign_value, DATEDIFF(CURDATE(),DATE(expired_on)) AS diff_days FROM db_national_listing.tbl_companymaster_finance_national WHERE parentid='" . $this->parentid . "' AND campaignid = 10 ";
			$res_finance_national_main = parent::execQuery($sql_finance_national_main,$this->Idc);
			if($res_finance_national_main && mysql_num_rows($res_finance_national_main)>0)
			{
				$row_finance_national_main = mysql_fetch_assoc($res_finance_national_main);
			}
			//print_r($row_finance_national_main);
			//print_r($row_finance_temp['recalculate_flag']);
			
			if(count($catidArrExisting)<= 0)
			{
				$data['error']   	   = -2;
				$data['error_message']   = 'Paid Categories Not Selected';
			}else{
				$data['error']   = 0;
			}
			
			$data['type']    = $listing_type;
			$data['budget']  = $row_finance_temp['budget'];
			$data['tenure']  = $row_finance_temp['duration']?$row_finance_temp['duration']:'365';
			$data['calc']    = $row_finance_temp['recalculate_flag'];
			$data['city']    = $city_array;
			$data['Category_nationalid']	 = $row_national_temp['Category_nationalid'];
			$data['miniAmount'] = $this->mini_balance();
			
			
			if(trim($row_national_temp['Category_nationalid'],'|P|'))
			{
				//$sql_cat = "select category_name from tbl_categorymaster_generalinfo where national_catid in (".str_replace('|P|',',',trim($row_national_temp['Category_nationalid'],'|P|')).")";
				//$res_cat = parent::execQuery($sql_cat, $this->local_obj);
				$natcatids = str_replace('|P|',',',trim($row_national_temp['Category_nationalid'],'|P|'));
				$cat_params = array();
				$cat_params['page']= 'nationallistingclass';
				$cat_params['parentid'] 	= $this->parentid;
				$cat_params['data_city'] 	= $this->data_city;
				$cat_params['return']		= 'category_name';

				$where_arr  	=	array();
				if($natcatids!=''){
					$where_arr['national_catid'] 	= $natcatids;
					$cat_params['where']			= json_encode($where_arr);
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
				}
				$cat_res_arr = array();
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}
				if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0) {
					
					foreach($cat_res_arr['results'] as $key =>$row_cat)
					{
						$Cat_name[] = $row_cat['category_name'];
					}
					$data['Category_name'] = $Cat_name;
				}
			}
			
			if(count($row_finance_national_main)>0)
			{
				$data['existing'] 		  = 1;
				$data['existing_balance'] = $row_finance_national_main['balance'];
				$data['existing_min_bud'] = 12000;
				
				$state_change_arr = $this->isStateAdded($data);
				if($this->trace)
				{
					echo "<br><pre> state arr :: ";
					print_r($state_change_arr);
				}
				
				if((($row_finance_national_main['expired'] && $row_finance_national_main['diff_days']<= 45) || $row_finance_national_main['balance']>0) && !$state_change_arr['state_change'])
					$data['last_budget']	  = max($row_finance_national_main['campaign_value'],8000);
				else 
					$data['last_budget']	  = 0;
				
			//	$data['budget'] 		  = $row_finance_national_main['balance'];
			//	$data['tenure'] 		  = round($row_finance_national_main['balance']/$row_finance_national_main['bid_perday']);
				
			}
		//	if(strtolower($this->module) == 'tme')
		//	{
				$sql_national_pt = "SELECT payment_type FROM db_finance.tbl_payment_type WHERE parentid='" . $this->parentid . "' and VERSION='".$this->version."'";
				$res_national_pt = parent::execQuery($sql_national_pt,$this->fin_con);
				if($this->trace)
				{
				echo "<br> sql :: ".$sql_national_pt;
				echo "<br> res :: ".$res_national_pt;
				echo "<br> rows:: ".mysql_num_rows($res_national_pt);
				}
			
				if($res_national_pt && mysql_num_rows($res_national_pt)>0)
				{
					$row_national_pt = mysql_fetch_assoc($res_national_pt);
					$data['payment_type'] = explode(',',$row_national_pt['payment_type']);
			
				}
		//	}
				
				
				if(count($state_change_arr))
				{
					$data['state_change'] = $state_change_arr['state_change'];
					$data['new_contract'] = $state_change_arr['new_contract'];
				}
				
				
			return $data;
		}else {
			$data ['error'] = '-1';
			$data ['message'] = 'no data';
		}
		
		return $data;
		
	}
	
	function NationalListingSanityCheck()
	{
		$sql_finance_temp = "SELECT * FROM tbl_companymaster_finance_temp WHERE parentid='" . $this->parentid . "' AND campaignid = '10' AND sphinx_id!=''";	
		if(strtolower($this->module) == 'cs')
		$res_finance_temp  = parent::execQuery($sql_finance_temp, $this->fin_con);
		else
		$res_finance_temp  = parent::execQuery($sql_finance_temp,$this->tempconn);
		if($this->trace)
		{
			echo "<br> sql :: ".$sql_finance_temp;
			echo "<br> res :: ".$res_finance_temp;
			echo "<br> rows:: ".mysql_num_rows($res_finance_temp);
		}
		if($res_finance_temp && mysql_num_rows($res_finance_temp)>0) {
			$row_finance_temp = mysql_fetch_assoc($res_finance_temp);
			$res_arr['errorCode'] = "0";
			$res_arr['errorMsg']  = "National listing campaign needs not to be calculated";
			//echo ' <br>budget arra <pre>';print_r($row_finance_temp);
			if($row_finance_temp['budget']>0 )
			{
				$national_listing_status = $this->isStateAdded();
				//echo ' <br>national arra <pre>';print_r($national_listing_status);
				if(($national_listing_status['state_change'] || $national_listing_status['new_contract']) &&  !$row_finance_temp['recalculate_flag'])
				{
					$res_arr['errorCode'] = "1";
					$res_arr['errorMsg']  = "National listing campaign needs to be calculated";
				}
			}
			     
			$nationaldata = $this->isnationallisting(); 
			//print_r($nationaldata);
			//$nationaldata['eligible_flag'] = 0;
			if($nationaldata['eligible_flag'] == 0 && $nationaldata['com'] != 1)
			{
				$res_arr['errorCode'] = "1";
				if($nationaldata['Nonpaid_cat'] == 1)
				$res_arr['errorMsg']  = "Contract should have atleast one paid national listing Tag category";
				else
				$res_arr['errorMsg']  = "Contract should have atleast one national listing Tag category";
			}

			return $res_arr;
		}
	}
	
	function isStateAdded($national_listing_temp_data= array())
	{
		
		if(count($national_listing_temp_data) <=0 )
		$national_listing_temp_data = $this->getNationalListingTempData();
		
		 if($national_listing_temp_data['city'])
		 {
			 //echo ;
			$national_budget_arr = array();$main_state_arr = array();$temp_state_arr = array();$diff_state_arr = array();$national_budget_arr['state_change'] = 0;$national_budget_arr['new_contract'] = 1;
			$sql_count = "SELECT count(DISTINCT state_name) as state_count,GROUP_CONCAT(DISTINCT state_name) as state_names FROM city_master WHERE ct_name IN ('".implode("','",$national_listing_temp_data['city'])."') AND multicity_display = '1' AND display_flag=1 ";
			 $res_count = parent::execQuery($sql_count,$this->local_obj);
			 if($res_count && mysql_num_rows($res_count))
			 {
				 
				 
				 $row_count = mysql_fetch_assoc($res_count);
				 
				 $sql_main_national = "SELECT * FROM db_national_listing.tbl_national_listing WHERE parentid='" . $this->parentid . "'";
				 $res_main_national = parent::execQuery($sql_main_national,$this->Idc);
				 if($res_main_national && mysql_num_rows($res_main_national))
				 {
					 $national_budget_arr['new_contract'] = 0;
					 $row_main_national = mysql_fetch_assoc($res_main_national);
					 $main_city_array	= explode('|#|',trim($row_main_national['Category_city'],'|#|'));
					 
					 if(count($main_city_array)>0)
					 {
						 $sql_count_main = "SELECT GROUP_CONCAT(DISTINCT state_name) as state_names FROM city_master WHERE ct_name IN ('".implode("','",$main_city_array)."') AND multicity_display = '1' AND display_flag=1 ";
						 $res_count_main = parent::execQuery($sql_count_main,$this->local_obj);
						 if($res_count_main && mysql_num_rows($res_count_main))
						 {
							 $row_count_main = mysql_fetch_assoc($res_count_main);
							 $main_state_arr = explode(',',$row_count_main['state_names']);
						 }
					 }
					 
					 $temp_state_arr = explode(',',$row_count['state_names']);
					 
					 $diff_state_arr = array_diff($temp_state_arr,$main_state_arr);
					 /*echo 'main <pre>';
					 print_r($main_state_arr);
					 echo 'temp <pre>';
					 print_r($temp_state_arr);
					 echo 'diff <pre>';
					 print_r($diff_state_arr);*/
					 
					 if($row_count['state_count']>0 && (count($temp_state_arr) > count($main_state_arr) ))
					 {
							
						 //if(count($diff_state_arr)>0)
						 //{
							 $national_budget_arr['state_change'] = 1;
						// }
						 
					 }
					
				 }
				return $national_budget_arr;
			 }
			 
		 }
	}
	
	function getNationalListingMinBudget($min_monthly_cost,$max_monthly_cost,$min_upfront_cost,$max_upfront_cost,$state_monthly_cost,$state_upfront_cost)
	{
		 $national_listing_temp_data = $this->getNationalListingTempData();
		 if(count($national_listing_temp_data)>0)
		 {
			 //echo ;
			$national_increment_factor = 1.5;
			$moreThan_year_cost = $max_upfront_cost * $national_increment_factor;
			if(!$national_listing_temp_data['existing'])
			{
				/*$min_upfront_cost 	 		= 0.67*$min_upfront_cost;
				$state_upfront_cost	 		= 0.67*$state_upfront_cost;
				$max_upfront_cost			= 0.8*$max_upfront_cost;
				$national_increment_factor  = 1.71641791;*/
			}
			
			if($national_listing_temp_data['city'])
			{
				$sql_count = "SELECT countryzone_id, COUNT(DISTINCT state_name) AS state_count
							  FROM city_master
							  WHERE ct_name IN ('".implode("','",$national_listing_temp_data['city'])."') 
							  AND multicity_display = '1' AND display_flag=1
							  GROUP BY countryzone_id 
						   	  ORDER BY state_count";
				 $res_count = parent::execQuery($sql_count,$this->local_obj);
				// echo '<br><br>'.$sql_count.'<br>';
				 //echo ' res num :: '.mysql_num_rows($res_count);
				 
				 if($res_count && mysql_num_rows($res_count))
				 {
					$life_time_minimum_budget      = 25000;
					$min_life_time_monthly_cost    = round($life_time_minimum_budget/12);
					$life_time_maximum_budget      = 100000;
					$max_life_time_monthly_cost    = round($life_time_maximum_budget/12);
					$life_time_state_upfront_cost  = $life_time_minimum_budget*0.5;
					$life_time_state_monthly_cost  = ($life_time_state_upfront_cost/12);
					 
					$life_time_maximum_zone_budget 	= 50000;
					$life_time_maximum_zone_month_cost = round($life_time_maximum_zone_budget/12);
				    
				    while( $row_count = mysql_fetch_assoc($res_count))
				    {
						//echo '<pre>';
						//print_r($row_count);
						$row_count['state_count'] = ($this->IsUnionTerritory($national_listing_temp_data['city'], $row_count['countryzone_id'])) ? ( $row_count['state_count'] - 1 ) : $row_count['state_count'];
						
						if(!$reduce_count_for_first_zone)
						{
							$row_count['state_count'] = $row_count['state_count'] - 1;
						}
						if( $row_count['state_count'] > 0 )
						{
							//$row_count['state_count'] = 6;
							$national_budget_arr[$row_count['countryzone_id']]['monthly_budget'] = $row_count['state_count'] * $state_monthly_cost;
						    $national_budget_arr[$row_count['countryzone_id']]['upfront_budget'] = $row_count['state_count'] * $state_upfront_cost;
						    
						    $national_budget_arr[$row_count['countryzone_id']]['lifetime']['monthly_budget'] = $row_count['state_count'] * $life_time_state_monthly_cost;
						    $national_budget_arr[$row_count['countryzone_id']]['lifetime']['upfront_budget'] = $row_count['state_count'] * $life_time_state_upfront_cost;
						    
						}
						//print_r($national_budget_arr);
						if(!$reduce_count_for_first_zone)
						{
							
								$national_budget_arr[$row_count['countryzone_id']]['monthly_budget'] = $national_budget_arr[$row_count['countryzone_id']]['monthly_budget'] + $min_monthly_cost;
								$national_budget_arr[$row_count['countryzone_id']]['upfront_budget'] = $national_budget_arr[$row_count['countryzone_id']]['upfront_budget'] + $min_upfront_cost;
						    
								$national_budget_arr[$row_count['countryzone_id']]['lifetime']['monthly_budget'] = min( ( $national_budget_arr[$row_count['countryzone_id']]['lifetime']['monthly_budget'] + $min_life_time_monthly_cost ) , $life_time_maximum_zone_month_cost );
								$national_budget_arr[$row_count['countryzone_id']]['lifetime']['upfront_budget'] = min( ( $national_budget_arr[$row_count['countryzone_id']]['lifetime']['upfront_budget'] + $life_time_minimum_budget ) , $life_time_maximum_zone_budget );
								
						}else {
							
								$national_budget_arr[$row_count['countryzone_id']]['lifetime']['monthly_budget'] = min( $national_budget_arr[$row_count['countryzone_id']]['lifetime']['monthly_budget'] , $life_time_maximum_zone_month_cost );
								$national_budget_arr[$row_count['countryzone_id']]['lifetime']['upfront_budget'] = min( $national_budget_arr[$row_count['countryzone_id']]['lifetime']['upfront_budget'] , $life_time_maximum_zone_budget );
								
						}
						
						$reduce_count_for_first_zone = 1;
						
					}
					
					//print_r($national_budget_arr);
					
					if(count($national_budget_arr) > 0)
					{
							foreach($national_budget_arr as $czid => $czid_val)
							{
								$total_monthly_budget += $czid_val['monthly_budget'];
								$total_upfront_budget += $czid_val['upfront_budget'];
								
								
								$total_monthly_lifetime_budget += $czid_val['lifetime']['monthly_budget'];
								$total_upfront_lifetime_budget += $czid_val['lifetime']['upfront_budget'];
								
							}
						
						    if($national_budget_arr['upfront_budget'] > $max_upfront_cost)
						    {
								$national_budget_arr['monthly_budget'] = $max_monthly_cost;
								$national_budget_arr['upfront_budget'] = $max_upfront_cost;
								
								$national_budget_arr['lifetime']['monthly_budget'] = $max_life_time_monthly_cost;
								$national_budget_arr['lifetime']['upfront_budget'] = $life_time_maximum_budget;
							 }
								  
							 $national_budget_arr['monthly_budget'] = ($total_monthly_budget > $max_monthly_cost) ? $max_monthly_cost : $total_monthly_budget;
							 $national_budget_arr['upfront_budget'] = ($total_upfront_budget > $max_upfront_cost) ? $max_upfront_cost : $total_upfront_budget;
							 
							 $national_budget_arr['lifetime']['monthly_budget'] = ($total_monthly_lifetime_budget > $max_life_time_monthly_cost ) ? $max_life_time_monthly_cost :$total_monthly_lifetime_budget;
							 $national_budget_arr['lifetime']['upfront_budget'] = ($total_upfront_lifetime_budget > $life_time_maximum_budget ) ? $life_time_maximum_budget : $total_upfront_lifetime_budget;
					}
					//echo '<br> return national budget<pre>';
						//print_r($national_budget_arr);
						
						  /*if($national_listing_temp_data['existing'])
						  {
								$national_budget_arr['upfront_budget'] 	 		= ($national_listing_temp_data['last_budget'])>0 ? MIN(($national_listing_temp_data['last_budget']*1.5),$national_budget_arr['upfront_budget']) : $national_budget_arr['upfront_budget'];
								$national_increment_factor = ($national_budget_arr['upfront_budget']< $max_upfront_cost) ? (($max_upfront_cost*$national_increment_factor)/$national_budget_arr['upfront_budget']) : $national_increment_factor;
						   }
					/*while( $row_count = mysql_fetch_assoc($res_count))
					{
						 if(count($row_count) > 0 )
						 {
							 
							 //$min_monthly_cost $min_upfront_cost
							 
							 if($row_count['state_count']> 1)
							 {
								 $row_count['state_count'] = ($this->IsUnionTerritory($national_listing_temp_data['city'], $row_count['countryzone_id'])) ? ( $row_count['state_count'] - 1 ) : $row_count['state_count'];
								 
								 $national_budget_arr['monthly_budget'] = ((($row_count['state_count']-1) * $state_monthly_cost) + $min_monthly_cost );
								 $national_budget_arr['upfront_budget'] = ((($row_count['state_count']-1) * $state_upfront_cost) + $min_upfront_cost );
								 
								 $national_budget_arr['lifetime']['monthly_budget'] = ((($row_count['state_count']-1) * $life_time_state_monthly_cost) + $min_life_time_monthly_cost );
								 $national_budget_arr['lifetime']['upfront_budget'] = ((($row_count['state_count']-1) * $life_time_state_upfront_cost) + $life_time_minimum_budget );
								 
								 
								 if($national_budget_arr['upfront_budget']>$max_upfront_cost)
								 {
									$national_budget_arr['monthly_budget'] = $max_monthly_cost;
									$national_budget_arr['upfront_budget'] = $max_upfront_cost;
									
									$national_budget_arr['lifetime']['monthly_budget'] = $max_life_time_monthly_cost;
									$national_budget_arr['lifetime']['upfront_budget'] = $life_time_maximum_budget;
								  }
							
								 
							 }else
							 {
								 $national_budget_arr['monthly_budget'] = $min_monthly_cost;
								 $national_budget_arr['upfront_budget'] = $min_upfront_cost;
								 
								 $national_budget_arr['lifetime']['monthly_budget'] = $min_life_time_monthly_cost;
								 $national_budget_arr['lifetime']['upfront_budget'] = $life_time_minimum_budget;
							 }
							 
							 if($national_listing_temp_data['existing'])
							  {
									$national_budget_arr['upfront_budget'] 	 		= ($national_listing_temp_data['last_budget'])>0 ? MIN(($national_listing_temp_data['last_budget']*1.5),$national_budget_arr['upfront_budget']) : $national_budget_arr['upfront_budget'];
									$national_increment_factor = ($national_budget_arr['upfront_budget']< $max_upfront_cost) ? (($max_upfront_cost*$national_increment_factor)/$national_budget_arr['upfront_budget']) : $national_increment_factor;
							   }
							   
							 
							 /*if(($national_increment_factor * $national_budget_arr['upfront_budget'])>$moreThan_year_cost)
							 {
								 $national_increment_factor = $moreThan_year_cost/$national_budget_arr['upfront_budget'];
							 }
							 
							 $national_budget_arr['increment_factor'] = $national_increment_factor;
							 //$state_change_arr = $this -> isStateAdded();
							 //print_r($state_change_arr);
							 //$national_budget_arr['state_change'] = $state_change_arr['state_change'];
							 
						 }
				   }*/
				   
						if(($national_increment_factor * $national_budget_arr['upfront_budget'])>$moreThan_year_cost)
						{
								 $national_increment_factor = $moreThan_year_cost/$national_budget_arr['upfront_budget'];
						}

						$national_budget_arr['increment_factor'] = $national_increment_factor;
						 
						return $national_budget_arr;
				 }
			}
			 
		 }
	}
	
	function IsUnionTerritory($city_array, $countryzone_id)
	{
		if(count($city_array)>0 && $countryzone_id == '4')
		{
			 $sql_count = "SELECT DISTINCT state_name FROM city_master WHERE ct_name IN ('".implode("','",$city_array)."') AND multicity_display = '1' AND display_flag=1 ";
			 $res_count = parent::execQuery($sql_count,$this->local_obj);
			 if($res_count && mysql_num_rows($res_count))
			 {
				 while($row_count = mysql_fetch_assoc($res_count))
				 {
					 $state_name_arr[] = strtolower(trim($row_count['state_name']));
				 }
				 
				 if(in_array('chandigarh',$state_name_arr) && in_array('punjab',$state_name_arr))
				 {
					 return true;
					 
				 }else
				 {
					 return false;
				 }
				 
			 }
			 
			 
		}
	}
	
	function get_valid_categories($total_catlin_arr)
	{
		$final_catids_arr = array();
		if((!empty($total_catlin_arr)) && (count($total_catlin_arr) >0))
		{
			foreach($total_catlin_arr as $catid)
			{
				$final_catid = 0;
				$final_catid = preg_replace('/[^0-9]/', '', $catid);
				if(intval($final_catid)>0)
				{
					$final_catids_arr[]	= $final_catid;
				}
			}
			$final_catids_arr = array_filter($final_catids_arr);
			$final_catids_arr = array_unique($final_catids_arr);
		}
		return $final_catids_arr;	
	}
	
	function getCategoryDetails($catids_arr)
	{
		$CatinfoArr = array();
		$catids_str = implode("','",$catids_arr);
		//$sqlCategoryDetails = "SELECT catid,category_name,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catids_str."')";
		//$resCategoryDetails 	= parent::execQuery($sqlCategoryDetails, $this->local_obj);
		$cat_params = array();
		$cat_params['page']= 'nationallistingclass';
		$cat_params['parentid'] 	= $this->parentid;
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'catid,category_name,national_catid';

		$where_arr  	=	array();
		if(count($catids_arr)>0){
			$where_arr['catid']			= implode(",",$catids_arr);
			$cat_params['where']		= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}
		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
		{
			foreach($cat_res_arr['results'] as $key =>$row_catdetails)
			{
				$catid 			= trim($row_catdetails['catid']);
				$category_name	= trim($row_catdetails['category_name']);
				$national_catid	= trim($row_catdetails['national_catid']);
				$CatinfoArr[$catid]['catname'] = $category_name;
				$CatinfoArr[$catid]['national_catid'] = $national_catid;
			}
		}
		return $CatinfoArr;
	}
	
	function Calculate_Update_Budget()
	{
		$row = $this->getNationalListingTempData();
		
		
		if($row['error'] != '-1')
		{
			$city_array = array_values($row['city']);
		}
		
		
		
		if(strtolower($this->module) == 'cs')
		{
			$catlin_nonpaid_db = 'db_iro.';
		}
		$temp_category_arr = array();
		
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['data_city'] 		= $this->data_city;
			$mongo_inputs['module']			= $this->module;
			$mongo_inputs['t1'] 			= "tbl_business_temp_data";
			$mongo_inputs['t2'] 			= "tbl_companymaster_extradetails_shadow";
			$mongo_inputs['t1_on'] 			= "contractid";
			$mongo_inputs['t2_on'] 			= "parentid";
			$mongo_inputs['t1_fld'] 		= "";
			$mongo_inputs['t2_fld'] 		= "catidlineage_nonpaid";
			$mongo_inputs['t1_mtch'] 		= json_encode(array("contractid"=>$this->parentid));
			$mongo_inputs['t2_mtch']		= "";
			$mongo_inputs['t1_alias'] 		= json_encode(array("catIds"=>"catidlineage"));
			$mongo_inputs['t2_alias'] 		= "";
			$mongo_join_data 	= $this->mongo_obj->joinTables($mongo_inputs);
			$row_temp_category 	= $mongo_join_data[0];
		}
		else{
		
			$sqlTempCategory	=	"SELECT catIds as catidlineage,catidlineage_nonpaid FROM tbl_business_temp_data as A LEFT JOIN ".$catlin_nonpaid_db."tbl_companymaster_extradetails_shadow as B on A.contractid=B.parentid WHERE contractid = '" . $this->parentid . "'";
			$resTempCategory 	= parent::execQuery($sqlTempCategory, $this->tempconn);
			$num_rows 			= parent::numRows($resTempCategory);
			if($num_rows > 0 ){
				$row_temp_category = parent::fetchData($resTempCategory);
			}
		}

		if(count($row_temp_category)>0)
		{
			if((isset($row_temp_category['catidlineage']) && $row_temp_category['catidlineage'] != '') || (isset($row_temp_category['catidlineage_nonpaid']) && $row_temp_category['catidlineage_nonpaid'] != ''))
			{
				$temp_catlin_arr 	= 	array();
				$temp_catlin_arr  	=   explode('|P|',$row_temp_category['catidlineage']);
				$temp_catlin_arr 	= 	array_filter($temp_catlin_arr);
				$temp_catlin_arr 	= 	$this->get_valid_categories($temp_catlin_arr);
				
				$temp_catlin_np_arr = array();
				$temp_catlin_np_arr = explode("/,/",trim($row_temp_category['catidlineage_nonpaid'],"/"));
				$temp_catlin_np_arr = array_filter($temp_catlin_np_arr);
				$temp_catlin_np_arr = 	$this->get_valid_categories($temp_catlin_np_arr);
				
				$total_catlin_arr = array();
				$total_catlin_arr =  array_merge($temp_catlin_arr,$temp_catlin_np_arr);
				$total_catlin_arr = array_merge(array_filter($total_catlin_arr));
				$contract_temp_categories = $this->get_valid_categories($total_catlin_arr);
				
				$all_contract_temp_catdetails_arr = $this->getCategoryDetails($contract_temp_categories);
		
				$national_catids_arr = array();
				if(count($all_contract_temp_catdetails_arr)>0)
				{
					foreach($all_contract_temp_catdetails_arr as $catid => $catinfo_arr)
					{
						$national_catids_arr[] = $catinfo_arr['national_catid'];
					}
				}
				//echo '<pre>ava';
				//print_r($national_catids_arr);
				//$excl = "SELECT national_catid,category_name, min(category_scope) as distr, count(national_catid) FROM tbl_categorymaster_generalinfo WHERE national_catid in (" . implode(",",$national_catids_arr) . ") and isdeleted = 0 AND mask_status=0 AND (category_scope = 1 or category_scope = 2) GROUP BY category_name";
        
				//$excl_national 	= parent::execQuery($excl,$this->local_obj);
				$cat_params = array();
				$cat_params['page']= 'nationallistingclass';
				$cat_params['parentid'] 	= $this->parentid;
				$cat_params['data_city'] 	= $this->data_city;
				$cat_params['return']		= 'national_catid,category_name,category_scope';

				$where_arr  	=	array();
				if(count($national_catids_arr)>0){
					$where_arr['national_catid']	= implode(",",$national_catids_arr);
					$where_arr['isdeleted']		= '0';
					$where_arr['mask_status']	= '0';
					$where_arr['category_scope']	= '1,2';
					$cat_params['where']		= json_encode($where_arr);
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
				}
				$cat_res_arr = array();
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}
			
				if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
				{
					foreach($cat_res_arr['results'] as $key =>$cat_arr)
					{
						$category_scope =	$cat_arr['category_scope'];
						if($category_scope ==1 || $category_scope ==2){
							$national_catids_arr1[]  = 	$cat_arr['national_catid'];
						}
					}
				}
				
				$national_catids_arr = $national_catids_arr1;
				//echo '<pre>ava';
				//print_r($national_catids_arr);
				
				$national_catids_str = "|P|";
				$national_catids_str .= implode("|P|",$national_catids_arr);
				$national_catids_str .="|P|";
				
				$TotalCategoryWeight = substr_count($national_catids_str, '|P|') - 1;
			
			}
			
			
		}
		
		
		if(count($city_array)>0)
		{
			
			$sql_city_count	   = "SELECT sum(totalcnt) as total_count FROM tbl_city_master WHERE ct_name IN ('".implode("','",$city_array)."') AND DE_display=1 AND display_flag=1";
			$res_city_count	   = parent::execQuery($sql_city_count,$this->dbConDjds);
			if($res_city_count && mysql_num_rows($res_city_count)>0)
			{
					//echo '<br> rows :: '.mysql_num_rows($res_city_count);
					$row_city_count = mysql_fetch_assoc($res_city_count);
					//echo '<pre>';print_r($row_city_count);
					$this->setsphinxid();
					$this->setversion();
					$updateFieldArr['budget']=$this->budget;
					$updateFieldArr['original_budget']=$this->budget;
					$updateFieldArr['duration']=$this->tenure;
					$updateFieldArr['version']= $this->version;

					$updateFieldArr['recalculate_flag'] = $this->recalculate_flag;


						$sql_payment_type = "SELECT * FROM tbl_payment_type WHERE parentid ='".$this->parentid."' AND  VERSION='".$this->version."'";
						$res_payment_type = parent::execQuery($sql_payment_type,$this->fin_con);
						if($res_payment_type && mysql_num_rows($res_payment_type))
						{
							$row_payment_type = mysql_fetch_assoc($res_payment_type);
							if( $row_payment_type['payment_type'] && ( stristr($row_payment_type['payment_type'],'nl_1yr_discount') || stristr($row_payment_type['payment_type'],'nl_2_yrs') ) )
							{
								if(stristr($row_payment_type['payment_type'],'nl_1yr_discount'))
								{
									$multiplier 	= 100/85;
								}elseif(stristr($row_payment_type['payment_type'],'nl_2_yrs')){
									$one_yr_value = $this->budget / 1.5;
									$multiplier  	= ($one_yr_value*2)/$this->budget;
								}
								$campaign_multiplier_temp_insert = "INSERT INTO campaign_multiplier_temp SET
                                                            parentid   = '".$this->parentid."',
                                                            version  = '".$this->version."',
                                                            campaignid  = 10,
                                                            actual_budget  = '".$this->budget."',
                                                            multiplier    = '" .$multiplier. "',
                                                            usercode    = '" .$this->usercode. "',
                                                            insert_date    = '" .date('Y-m-d H:i:s') . "'
                                                            ON DUPLICATE KEY UPDATE
                                                            multiplier    = '" .$multiplier. "',
                                                            actual_budget  = '".$this->budget."',
                                                            usercode    = '" .$this->usercode. "',
                                                            insert_date    = '" .date('Y-m-d H:i:s'). "'";
                $res_temp_insert =  parent::execQuery($campaign_multiplier_temp_insert, $this->tempconn);

							}else {
								$delete_campaign_multiplier = "DELETE FROM campaign_multiplier_temp WHERE parentid   = '".$this->parentid."' AND 	version  = '".$this->version."' AND campaignid  = 10 ";
								$res_delete_campaign_multiplier =  parent::execQuery($delete_campaign_multiplier, $this->tempconn);
							}
						}

					$res_data = $this->financeInsertUpdateTemp($campaignid = 10,$updateFieldArr);
					
					$sql = "UPDATE tbl_national_listing_temp SET totalcityweight='".$row_city_count['total_count']."',dailyContribution='".round(( $this->budget/(($this->tenure > 1095 ) ? 365 : $this->tenure ) ),4)."',ContractTenure = '".$this->tenure."',Category_nationalid = '".$national_catids_str."' , TotalCategoryWeight = '".$TotalCategoryWeight."'  WHERE parentid = '".$this->parentid."'";
					$res = parent::execQuery($sql,$this->tempconn);
					
					$data['error']         = 0;
					$data['finance_update']= $res_data;
					$data['data_update']   = $res;
					
					
					$insert_debug_log = "INSERT INTO tbl_national_listing_temp_debug SET parentid='".$this->parentid."',page='services/nationallistingclass.php',line_no= '184',query= '".addslashes($sql)."',date_time= '".date('Y-m-d H:i:s')."',ucode= '".$_SESSION['ucode']."',uname='".$_SESSION['uname']."'";
					$res_insert_debug_log = parent::execQuery($insert_debug_log,$this->tempconn);
					
				
			}else{
					$data ['error'] = '-1';
					$data ['message'] = 'no data';
			}
		}else{
				$data ['error'] = '-1';
				$data ['message'] = 'no data';
		}
		
		return $data;
	 }
	 
	function setversion()
	{
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_temp_intermediate";
			$mongo_inputs['fields'] 	= "version";
			$summary_version_arr = $this->mongo_obj->getData($mongo_inputs);
		}
		else{
			$summary_version_sql 	="select version from tbl_temp_intermediate where parentid='".$this->parentid."'";
			$summary_version_rs 	=  parent::execQuery($summary_version_sql, $this->tempconn);
			$summary_version_arr 	= mysql_fetch_assoc($summary_version_rs);
		}
		$this->version = $summary_version_arr['version'];
	}
	 
	function setsphinxid()
	{
		$sql= "select sphinx_id,docid from tbl_id_generator where parentid='".$this->parentid."'";
		$res = parent::execQuery($sql, $this->dbConIro);

		if($res && mysql_num_rows($res) )
		{
			$row= mysql_fetch_assoc($res);
			$this->sphinx_id = $row['sphinx_id'];
			$this->docid = $row['docid'];
		}else
		{
			echo "sphinx_id not found in tbl_id_generator";
			exit;
		}
	}
	
	 function financeInsertUpdateTemp($campaignid,$camp_data) {

        if ($campaignid>0 && is_array($camp_data)) {

            $insert_str = '';
            foreach($camp_data as $column_key => $column_value) {

                $temp_str    = $column_key ."='".$column_value . "'";
                $insert_str .= (($insert_str=='') ? $temp_str : ','.$temp_str) ;
            }

			$compmaster_fin_temp_insert = "INSERT INTO tbl_companymaster_finance_temp SET
                                            ". $insert_str.",
                                            sphinx_id   = '".$this->sphinx_id."',
                                            campaignid  = '".$campaignid."',
                                            parentid    = '" . $this->parentid . "'
                                            ON DUPLICATE KEY UPDATE
                                            " . $insert_str . "";//exit;
			//echo $compmaster_fin_temp_insert;
			if(strtolower($this->module) == 'cs')
			$res_compmaster_fin_temp_insert  = parent::execQuery($compmaster_fin_temp_insert, $this->fin_con);
			else
            $res_compmaster_fin_temp_insert  = parent::execQuery($compmaster_fin_temp_insert, $this->tempconn);
            
            
			if($this->trace)
			{
				echo "<br> sql :: ".$compmaster_fin_temp_insert;
				echo "<br> res :: ".$res_compmaster_fin_temp_insert;
				echo "<br> error :: ".mysql_error();
			}

        }
        return $res_compmaster_fin_temp_insert;

    }

	function removeLocalforNational(){
		
		$sql = "UPDATE tbl_national_listing_temp  SET category_city = TRIM(REPLACE(LOWER(category_city),'|#|".strtolower($this->data_city)."',''))  WHERE parentid='".$this->parentid."'"; 
		
		$res  = parent::execQuery($sql, $this->tempconn);
		
		$row = 	$this->getNationalListingTempData();
		
		return $row;
		
	}
	
	
	function GetNationalMinBudget(){
		
		$sql="select * from tbl_business_uploadrates where city='".$this->data_city."' limit 1";
		$res 	= parent::execQuery($sql, $this->dbConDjds);
		$num_rows		= mysql_num_rows($res);
		
		$return_array = array();
		
		if(DEBUG_MODE)
		{
			echo '<br><b>Biz Upload rates Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res;
			echo '<br><b>Num Rows:</b>'.$num_rows;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		if($res && $num_rows > 0)
		{		
			
			while($row=mysql_fetch_assoc($res))
			{
				$minimumbudget_national		= ($row['minimumbudget_national']);
				$maxbudget_national			= ($row['maxbudget_national']);
				$statebudget_national		= ($row['statebudget_national']);
				$minupfrontbudget_national	= ($row['minupfrontbudget_national']);
				$maxupfrontbudget_national	= ($row['maxupfrontbudget_national']);
				$stateupfrontbudget_national= ($row['stateupfrontbudget_national']);
				$minbudget_national        = ($row['minbudget_national']);
			}
		
		
		$national_min_budget_arr  =  $this->getNationalListingMinBudget($min_monthly_cost=($minimumbudget_national/12),$max_monthly_cost=($maxbudget_national/12),$minupfrontbudget_national,$maxupfrontbudget_national,$state_monthly_cost=($statebudget_national/12),$stateupfrontbudget_national);
		//echo '<pre>dsc==';print_r($national_min_budget_arr);	
			
			$return_array['minbudget_national'] 	 = $minbudget_national; 
			$return_array['monthly_national_budget'] = $national_min_budget_arr['monthly_budget']; 
			$return_array['upfront_national_budget'] = $national_min_budget_arr['upfront_budget']; 
			$return_array['state_change'] 			 = $national_min_budget_arr['state_change']; 
			$return_array['increment_factor'] 		 = $national_min_budget_arr['increment_factor']; 
			$return_array['lifetime_monthly_national_budget'] = $national_min_budget_arr['lifetime']['monthly_budget']; 
			$return_array['lifetime_upfront_national_budget'] = $national_min_budget_arr['lifetime']['upfront_budget']; 
			$return_array['minupfrontbudget_national'] = intval($minupfrontbudget_national); 
			$return_array['maxupfrontbudget_national'] = intval($maxupfrontbudget_national); 
			$return_array['stateupfrontbudget_national']  = intval($stateupfrontbudget_national);
		
		
		}
		
		$result['result'] = $return_array;
		$result['error']['code'] = 0;
		$result['error']['msg']  = "";
		return($result);	
	}
	
	
	
		
	public function National_temp_data(){
		
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_business_temp_data";
				$mongo_inputs['fields'] 	= "catIds,nationalcatIds,categories";
				$row_temp_data = $this->mongo_obj->getData($mongo_inputs);
				
		}
		$result['eligible_flag'] = 1;
		$result['total_cat'] = explode(",",str_replace('|P|',',',trim($row_temp_data['categories'],'|P|'))); 
		if($row_temp_data['nationalcatIds']) {	
		$excl = "SELECT category_name, min(category_scope) as distr, count(national_catid) FROM tbl_categorymaster_generalinfo WHERE national_catid in (" . str_replace('|P|',',',trim($row_temp_data['nationalcatIds'],'|P|')). ") and isdeleted = 0 AND mask_status=0 AND (category_scope = 1 or category_scope = 2) GROUP BY category_name";
		
        //$excl_national 	= parent::execQuery($excl,$this->local_obj);
        	$natcatids =	str_replace('|P|',',',trim($row_temp_data['nationalcatIds'],'|P|'));
			$cat_params['page']= 'nationallistingclass';
			$cat_params['parentid'] 	= $this->parentid;
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'category_scope,category_name';

			$where_arr  	=	array();
			if($natcatids!=''){
				$where_arr['national_catid'] 	= $natcatids;
				$where_arr['isdeleted'] 		= '0';
				$where_arr['mask_status'] 		= '0';
				$where_arr['category_scope'] 	= '1,2';
				$cat_params['where']			= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			{
				foreach($cat_res_arr['results'] as $key =>$cat_arr)
				{
					if($cat_arr['category_scope'] != '1' && $cat_arr['category_scope'] != '2')
					{
						$result['eligible_flag'] = 0;
						
					}
					else
					{
						$result['national_tag_cat'][] = $cat_arr['category_name'];
					}	
				}
			}
			else
			{
				$result['eligible_flag'] = 0;
			}
		}
		
		return $result;
	}	
	
	public function InsertGenioLite(){
		//echo 'dbdsb';
		$res_arr = array();
		$cityList = "|#|";//.$this->data_city."|#|";
		$city_arr = explode(',',$this->params['citystr']);
		
		if(count($city_arr) > 0 && $this->params['citystr']!='' && $this->params['citystr']!=null){
			foreach($city_arr as $key=>$value)		
			{		
				$cityList .= $value . "|#|";
			}
		}
		
		$sql_city_count	   = "SELECT sum(totalcnt) as total_count FROM tbl_city_master WHERE ct_name IN ('".implode("','",$city_arr)."') AND DE_display=1 AND display_flag=1";
		$res_city_count	   = parent::execQuery($sql_city_count,$this->dbConDjds);
		if($res_city_count && mysql_num_rows($res_city_count)>0)
		{
			//echo '<br> rows :: '.mysql_num_rows($res_city_count);
			$row_city_count = mysql_fetch_assoc($res_city_count);
		}
		#######################################################################################
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['data_city'] 		= $this->data_city;
			$mongo_inputs['module']			= $this->module;
			$mongo_inputs['t1'] 			= "tbl_business_temp_data";
			$mongo_inputs['t2'] 			= "tbl_companymaster_extradetails_shadow";
			$mongo_inputs['t1_on'] 			= "contractid";
			$mongo_inputs['t2_on'] 			= "parentid";
			$mongo_inputs['t1_fld'] 		= "";
			$mongo_inputs['t2_fld'] 		= "catidlineage_nonpaid";
			$mongo_inputs['t1_mtch'] 		= json_encode(array("contractid"=>$this->parentid));
			$mongo_inputs['t2_mtch']		= "";
			$mongo_inputs['t1_alias'] 		= json_encode(array("catIds"=>"catidlineage"));
			$mongo_inputs['t2_alias'] 		= "";
			$mongo_join_data 	= $this->mongo_obj->joinTables($mongo_inputs);
			$row_temp_category 	= $mongo_join_data[0];
		}
		

		if(count($row_temp_category)>0)
		{
			if((isset($row_temp_category['catidlineage']) && $row_temp_category['catidlineage'] != '') || (isset($row_temp_category['catidlineage_nonpaid']) && $row_temp_category['catidlineage_nonpaid'] != ''))
			{
				$temp_catlin_arr 	= 	array();
				$temp_catlin_arr  	=   explode('|P|',$row_temp_category['catidlineage']);
				$temp_catlin_arr 	= 	array_filter($temp_catlin_arr);
				$temp_catlin_arr 	= 	$this->get_valid_categories($temp_catlin_arr);
				
				$temp_catlin_np_arr = array();
				$temp_catlin_np_arr = explode("/,/",trim($row_temp_category['catidlineage_nonpaid'],"/"));
				$temp_catlin_np_arr = array_filter($temp_catlin_np_arr);
				$temp_catlin_np_arr = 	$this->get_valid_categories($temp_catlin_np_arr);
				
				$total_catlin_arr = array();
				$total_catlin_arr =  array_merge($temp_catlin_arr,$temp_catlin_np_arr);
				$total_catlin_arr = array_merge(array_filter($total_catlin_arr));
				$contract_temp_categories = $this->get_valid_categories($total_catlin_arr);
				
				$all_contract_temp_catdetails_arr = $this->getCategoryDetails($contract_temp_categories);
		
				$national_catids_arr = array();
				if(count($all_contract_temp_catdetails_arr)>0)
				{
					foreach($all_contract_temp_catdetails_arr as $catid => $catinfo_arr)
					{
						$national_catids_arr[] = $catinfo_arr['national_catid'];
					}
				}
				//echo '<pre>ava';
				//print_r($national_catids_arr);
				//$excl = "SELECT national_catid,category_name, min(category_scope) as distr, count(national_catid) FROM tbl_categorymaster_generalinfo WHERE national_catid in (" . implode(",",$national_catids_arr) . ") and isdeleted = 0 AND mask_status=0 AND (category_scope = 1 or category_scope = 2) GROUP BY category_name";
        
				//$excl_national 	= parent::execQuery($excl,$this->local_obj);
				$cat_params = array();
				$cat_params['page']= 'nationallistingclass';
				$cat_params['parentid'] 	= $this->parentid;
				$cat_params['data_city'] 	= $this->data_city;
				$cat_params['return']		= 'national_catid,category_name,category_scope';

				$where_arr  	=	array();
				if(count($national_catids_arr)>0){
					$where_arr['national_catid']	= implode(",",$national_catids_arr);
					$where_arr['isdeleted']		= '0';
					$where_arr['mask_status']	= '0';
					$where_arr['category_scope']= '1,2';
					$cat_params['where']		= json_encode($where_arr);
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
				}
				$cat_res_arr = array();
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}
			
				if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
				{
					$cat_scope_arr = array();
					foreach($cat_res_arr['results'] as $key =>$row_excl)
					{
						$category_scope	=	$row_excl['category_scope'];
						if($category_scope==1 || $category_scope == 2){
							$national_catids_arr1[]  = 	$row_excl['national_catid'];
						}
					}
					
				}
				
				$national_catids_arr = $national_catids_arr1;
				//echo '<pre>ava';
				//print_r($national_catids_arr);
				
				$national_catids_str = "|P|";
				$national_catids_str .= implode("|P|",$national_catids_arr);
				$national_catids_str .="|P|";
				
				$TotalCategoryWeight = substr_count($national_catids_str, '|P|') - 1;
			
			}
			
			
		}
		
		#######################################################################################
		if($this->params['type'] == 'zone') {
			$statezone = 1;
		}else if($this->params['type'] == 'state'){
			$statezone = 2;
		}else if($this->params['type'] == 'city'){
			$statezone = 3;
		}
		
		$shortUrlSql = "SELECT sphinx_id, parentid, CONCAT(url_cityid, shorturl) AS url FROM tbl_id_generator WHERE shorturl IS NOT NULL AND parentid='".$this->parentid."'";
		$reszone  = parent::execQuery($shortUrlSql, $this->dbConIro);
		$shortUrlRow = mysql_fetch_assoc($reszone);
		
		//dailyContribution='".round(($this->budget/$this->tenure),4)."',,Category_nationalid = '".$national_catids_str."' , TotalCategoryWeight = '".$TotalCategoryWeight."'
		
		
		$sqlIns = "INSERT INTO tbl_national_listing_temp SET
							parentid 			= '".$this->parentid."',					   
							Category_city 		= '".$cityList."',										
							contractCity 		= '".$this->data_city."',
							latitude			= '".$this->params['latitude']."',
							longitude			= '".$this->params['longitude']."',
							lastupdate			= '".date('Y-m-d H:i:s')."',
							state_zone			= '".$statezone."',
							totalcityweight		= '".$row_city_count['total_count']."',
							ContractTenure		= '".$this->params['tenure']."',
							Category_nationalid	= '".$national_catids_str."',
							TotalCategoryWeight	= '".$TotalCategoryWeight."',
							dailyContribution	= '".round(($this->params['budget']/ (($this->params['tenure'] > 1095 ) ? 365 : $this->params['tenure'] ) ),4)."',
							short_url		 	= '".$shortUrlRow['url']."'
						ON DUPLICATE KEY UPDATE
							Category_city 		= '".$cityList."',										
							contractCity 		= '".$this->data_city."',
							latitude			= '".$this->params['latitude']."',
							longitude			= '".$this->params['longitude']."',
							lastupdate			= '".date('Y-m-d H:i:s')."',
							state_zone			= '".$statezone."',
							totalcityweight		= '".$row_city_count['total_count']."',
							ContractTenure		= '".$this->params['tenure']."',
							Category_nationalid	= '".$national_catids_str."',
							TotalCategoryWeight	= '".$TotalCategoryWeight."',
							dailyContribution	= '".round(($this->params['budget']/ (($this->params['tenure'] > 1095 ) ? 365 : $this->params['tenure'] ) ),4)."',
							short_url		 	= '".$shortUrlRow['url']."'";
		$resIns = parent::execQuery($sqlIns, $this->tempconn);
		$insert_debug_log = "INSERT INTO tbl_national_listing_temp_debug SET parentid='".$this->parentid."',page='jd_box/services/includes/bformmulticity_class.php',line_no= '468',query= '".addslashes(stripslashes($sqlIns))."',date_time= '".date('Y-m-d H:i:s')."',ucode= '".$_SESSION['ucode']."',uname='".$_SESSION['uname']."'";
		$res_insert_debug_log =  parent::execQuery($insert_debug_log, $this->tempconn);
		
	//	$national_listing_arr = $this -> national_list_obj -> isStateAdded();
		
	/*	if($national_listing_arr['state_change'] || $national_listing_arr['new_contract'])
		{
			$sql_finance_temp = "UPDATE tbl_companymaster_finance_temp SET recalculate_flag  = 0 WHERE parentid='".$this->parentid."' AND campaignid='10'";
			$re_finance_temp  = parent::execQuery($sql_finance_temp, $this->tempconn);
		}
		*/
		
		if($resIns) {
			$res_arr['errorCode'] = "0";
			$res_arr['errorMsg']  = "Data inserted";
		}else {
			$res_arr['errorCode'] = "1";
			$res_arr['errorMsg']  = "Data not inserted";
		}
		return $res_arr;
		
	}
	
	
	public function CheckNationListing2(){
		
		$res_arr = array();
		
			$res_arr = $this->National_temp_data();
			$res_temp_data = $this->getNationalListingTempData();
			$cites = array_map('strtolower', $res_temp_data['city']);
			$res_arr['selected_cities'] = $cites;
			
			if( strtolower(trim($this->params['camp_name'])) == 'national_banner' )
			{
				$resp_data = $this -> FetchSelectedCites();
				if($resp_data['code'] == '200')
				{
					
					$cites = array_map('strtolower', $resp_data['data']);
					$res_arr['selected_cities'] = $cites;
					$res_arr['camp_name']	='national_banner';
					
				}
			}
		
			
		
		
		$sql="SELECT DISTINCT(ct_name) as city,city_id,state_id,state_name,countryzone FROM city_master WHERE /*ct_name != '".$this->data_city."' AND */multicity_display = '1' AND display_flag=1  ORDER BY state_name,ct_name";
		if($this->module == 'tme')
			$reszone  = parent::execQuery($sql, $this->dbConDjds);
		else
			$reszone  = parent::execQuery($sql, $this->tempconn);
		if(mysql_num_rows($reszone) > 0){
			$i =0;
			while($row = mysql_fetch_assoc($reszone)) {
			
				if(!$state_last_index[strtolower($row['state_name'])]['index'])
				{
					$state_last_index[strtolower($row['state_name'])]['index'] = 0;
				}
				
				
				if(!in_array(strtolower($row['countryzone']),$res_arr['Zone']['zone_stat']['selected_zone']) && in_array(strtolower($row['city']),$cites))
				{
					$res_arr['Zone']['zone_stat']['selected_zone'][] =  strtolower($row['countryzone']);
				}
				switch(strtolower($row['countryzone']))
				{
					
				case  'south zone':
					
					if(!in_array(($row['state_id']),$res_arr['Zone']['data'][strtolower($row['countryzone'])]['zone stat']['selected_state']) && in_array(strtolower($row['city']),$cites))
					{
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['zone stat']['selected_state'][] 	= ($row['state_id']);
					
					}
					
					if(strtolower($row['countryzone']) == 'south zone')
					{
					
					if(in_array(strtolower($row['city']),$cites))
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['stat']['select_city'][] 	= strtolower($row['city']);
					
					
					//if(in_array(strtolower($row['city']),$cites))
					if(!in_array($row['state_id'],$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['state_id']))
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['state_id'] 	= strtolower($row['state_id']);
					
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['data'][$row['city_id']] 	= strtolower($row['city']);
					
					}
					
					/*$res_arr['Zone']['data']['data']['data'][$row['state_name']]['city_name'] 	= strtolower($row['city']);
					$res_arr['Zone']['data']['data']['data'][$row['state_name']]['city_id'] 	= strtolower($row['city_id']);	/*$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_id']	= strtolower($row['state_id']);
					}
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_name']	= strtolower($row['state_name']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['selected']	= in_array(strtolower($row['city']),$cites) ? 1 : 0;*/
				case  'north zone':
					if(!in_array(($row['state_id']),$res_arr['Zone']['data'][strtolower($row['countryzone'])]['zone stat']['selected_state']) && in_array(strtolower($row['city']),$cites))
					{
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['zone stat']['selected_state'][] 	= ($row['state_id']);
					
					}
					if(strtolower($row['countryzone']) == 'north zone')
					{
					
					if(in_array(strtolower($row['city']),$cites))
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['stat']['select_city'][] 	= strtolower($row['city']);
					/*else
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['stat']['select_city'] 	= '';
					*/
					//if(in_array(strtolower($row['city']),$cites))
					if(!in_array($row['state_id'],$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['state_id']))
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['state_id']	= strtolower($row['state_id']);
					
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['data'][$row['city_id']]	= strtolower($row['city']);
					
					}
					/*$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city_id'] 	= strtolower($row['city_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_id']	= strtolower($row['state_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_name']	= strtolower($row['state_name']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['selected']	= in_array(strtolower($row['city']),$cites) ? 1 : 0;*/
				case  'west zone':
				//echo 'vdvsdb';
				if(!in_array(($row['state_id']),$res_arr['Zone']['data'][strtolower($row['countryzone'])]['zone stat']['selected_state']) && in_array(strtolower($row['city']),$cites))
					{
					//echo 'cscs=='.$row['state_id'];die;
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['zone stat']['selected_state'][] 	= ($row['state_id']);
					
					}
					if(strtolower($row['countryzone']) == 'west zone')
					{
					
					if(in_array(strtolower($row['city']),$cites))
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['stat']['select_city'][] 	= strtolower($row['city']);
					/*else
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['stat']['select_city'] 	= '';*/
					
					//if(in_array(strtolower($row['city']),$cites))
					if(!in_array($row['state_id'],$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['state_id']))
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['state_id']	= strtolower($row['state_id']);
					
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['data'][$row['city_id']] 	= strtolower($row['city']);
					
					}
				/*if(!in_array(strtolower($row['state_name']),$res_arr['Zone']['data'][strtolower($row['countryzone'])]['selected_state']) && in_array(strtolower($row['city']),$cites))
					{
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['selected_state'][] 	= strtolower($row['state_name']);
					//$res_arr['Zone']['data'][strtolower($row['countryzone'])]['selected_state'][] 	= strtolower($row['state_id']);
					}
					if(!in_array(strtolower($row['city']),$res_arr['Zone']['data']['data']['selected_city']) && in_array(strtolower($row['city']),$cites))
					{
						$res_arr['Zone']['data']['data']['selected_city'][] 	= strtolower($row['city']);
					}
					$res_arr['Zone']['data']['data']['data'][$row['state_name']]['city_name'] 	= strtolower($row['city']);
					$res_arr['Zone']['data']['data']['data'][$row['state_name']]['city_id'] 	= strtolower($row['city_id']);	
				
			//		$res_arr['Zone']['zone_stat']['selected_zone'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city'] 		= strtolower($row['city']);
					/*$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city_id'] 	= strtolower($row['city_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_id']	= strtolower($row['state_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_name']	= strtolower($row['state_name']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['selected']	= in_array(strtolower($row['city']),$cites) ? 1 : 0;*/
				case  'east zone':
				
				if(!in_array(($row['state_id']),$res_arr['Zone']['data'][strtolower($row['countryzone'])]['zone stat']['selected_state']) && in_array(strtolower($row['city']),$cites))
					{
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['zone stat']['selected_state'][] 	= ($row['state_id']);
					
					}
					if(strtolower($row['countryzone']) == 'east zone')
					{
					
					if(in_array(strtolower($row['city']),$cites))
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['stat']['select_city'][] 	= strtolower($row['city']);
					/*else
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['stat']['select_city'] 	= '';*/
					
					//if(in_array(strtolower($row['city']),$cites))
					if(!in_array($row['state_id'],$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['state_id']))
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['state_id'] 	= strtolower($row['state_id']);
					
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['data'][$row['city_id']]	= strtolower($row['city']);
					
					}
			//		$res_arr['Zone']['zone_stat']['selected_zone'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city'] 		= strtolower($row['city']);
					/*$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city_id'] 	= strtolower($row['city_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_id']	= strtolower($row['state_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_name']	= strtolower($row['state_name']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['selected']	= in_array(strtolower($row['city']),$cites) ? 1 : 0;*/
				case  'central zone':
					if(!in_array(($row['state_id']),$res_arr['Zone']['data'][strtolower($row['countryzone'])]['zone stat']['selected_state']) && in_array(strtolower($row['city']),$cites))
					{
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['zone stat']['selected_state'][] 	= ($row['state_id']);
					
					}
					if(strtolower($row['countryzone']) == 'central zone')
					{
					
					if(in_array(strtolower($row['city']),$cites))
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['stat']['select_city'][] 	= strtolower($row['city']);
					/*else
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['stat']['select_city'] 	= '';*/
					
					//if(in_array(strtolower($row['city']),$cites))
					if(!in_array($row['state_id'],$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['state_id']))
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['state_id'] 	= strtolower($row['state_id']);
					
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['data'][$row['city_id']]	= strtolower($row['city']);
					
					}
					/*$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city_id'] 	= strtolower($row['city_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_id']	= strtolower($row['state_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_name']	= strtolower($row['state_name']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['selected']	= in_array(strtolower($row['city']),$cites) ? 1 : 0;*/
				case  'north east zone':
					if(!in_array(($row['state_id']),$res_arr['Zone']['data'][strtolower($row['countryzone'])]['zone stat']['selected_state']) && in_array(strtolower($row['city']),$cites))
					{
					//echo 'cscs=='.$row['state_id'];die;
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['zone stat']['selected_state'][] 	= ($row['state_id']);
					
					}
					if(strtolower($row['countryzone']) == 'north east zone')
					{
					
					if(in_array(strtolower($row['city']),$cites))
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['stat']['select_city'][] 	= strtolower($row['city']);
					/*else
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['stat']['select_city'] 	= '';*/
					
					//if(in_array(strtolower($row['city']),$cites))
					if(!in_array($row['state_id'],$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['state_id']))
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['state_id']	= strtolower($row['state_id']);
					
					$res_arr['Zone']['data'][strtolower($row['countryzone'])]['data'][strtolower($row['state_name'])]['data'][$row['city_id']] 	= strtolower($row['city']);
					
					}
			//		$res_arr['Zone']['zone_stat']['selected_zone'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city'] 		= strtolower($row['city']);
					/*$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city_id'] 	= strtolower($row['city_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_id']	= strtolower($row['state_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_name']	= strtolower($row['state_name']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['selected']	= in_array(strtolower($row['city']),$cites) ? 1 : 0;		*/
				}
				
				$state_last_index[strtolower($row['state_name'])]['index'] = $state_last_index[strtolower($row['state_name'])]['index'] + 1;
				
			}
			
		$sql_min_national = "SELECT minupfrontbudget_national,maxupfrontbudget_national,stateupfrontbudget_national FROM tbl_business_uploadrates WHERE city = '" . $this->data_city . "'";
		$res_min_national = parent::execQuery($sql_min_national,$this->dbConDjds);
		//echo mysql_num_rows($res_min_national);
	
	
		if($res_min_national && mysql_num_rows($res_min_national)>0)
		{
			$row_arr = mysql_fetch_assoc($res_min_national);
			$res_arr['minupfrontbudget_national']	= intval($row_arr['minupfrontbudget_national']);
			$res_arr['maxupfrontbudget_national']	= intval($row_arr['maxupfrontbudget_national']);
			$res_arr['stateupfrontbudget_national']	= intval($row_arr['stateupfrontbudget_national']);		
			
			$res_arr['1']['banner_maxupfrontbudget_national']	= intval('48000');
			$res_arr['1']['banner_maxecsbudget_national']	= intval('57600');
			$res_arr['1']['banner_stateupfrontbudget_national']	= intval('16000');
			$res_arr['1']['banner_state_ecsbudget_national']	= intval('19200');
			$res_arr['1']['banner_zone_upfrontbudget_national']	= intval('24000');
			$res_arr['1']['banner_zone_ecsbudget_national']	= intval('28800');
			
			$res_arr['3']['banner_maxupfrontbudget_national']	= intval('90000');
			$res_arr['3']['banner_maxecsbudget_national']	= intval('108000');
			$res_arr['3']['banner_stateupfrontbudget_national']	= intval('30000');
			$res_arr['3']['banner_state_ecsbudget_national']	= intval('36000');
			$res_arr['3']['banner_zone_upfrontbudget_national']	= intval('45000');
			$res_arr['3']['banner_zone_ecsbudget_national']	= intval('54000');
			
			$exisiting_vfl = 0;
			
			$sql_existing_vfl = "select * from db_national_listing.tbl_companymaster_finance_national where parentid='".$this->parentid."' and campaignid = 10 and duration > 3600";
			
			$res_existing_vfl = parent::execQuery($sql_existing_vfl, $this->Idc);
			if($res_existing_vfl && mysql_num_rows($res_existing_vfl))
			{
				$exisiting_vfl = 1;
				$row_arr_vfl = mysql_fetch_assoc($res_existing_vfl);
				$budget_max_vfl = intval('100000');
				if(intval($row_arr_vfl['budget']) > intval('100000'))
				{
					$budget_vfl = intval($row_arr_vfl['budget']);
					$budget_max_vfl = intval($row_arr_vfl['budget']);
				}
				else
				{
					if(intval($row_arr_vfl['budget']) > intval('25000'))
					{
						$budget_vfl = intval($row_arr_vfl['budget']);
						
					}
					else
					{
						$budget_vfl = intval('25000');
						
					}		
				}	
			}
			
					
			$sql_existing_paid = "select * from tbl_companymaster_finance where parentid='".$this->parentid."' and campaignid in (1,2)";
			
			$res_existing_paid = parent::execQuery($sql_existing_paid, $this->fin_con);
			
			$exisiting_paid = 0;
			
			if($res_existing_paid && mysql_num_rows($res_existing_paid))
			{
				
				while($row_arr = mysql_fetch_assoc($res_existing_paid))
				{
					
					$balance = $row_arr['balance'];
					$manual_override = $row_arr['manual_override'];
					$expired  = $row_arr['expired'];
						
				
					if(($balance >0 ) || (($balance <=0) && ($manual_override == 1) && ($expired == 0)))
					{
						$exisiting_paid = 1;
						break;
					}
				}	
			}
			
			$sql_existing_paid_national = "select * from db_national_listing.tbl_companymaster_finance_national where parentid='".$this->parentid."' and campaignid in (10) and duration = 365";
			
			$res_existing_paid_national = parent::execQuery($sql_existing_paid_national, $this->Idc);
			
			$exisiting_paid = 0;
			
			if($res_existing_paid_national && mysql_num_rows($res_existing_paid_national))
			{
				
				while($row_arr = mysql_fetch_assoc($res_existing_paid_national))
				{
					$balance = $row_arr['balance'];
					$manual_override = $row_arr['manual_override'];
					$expired  = $row_arr['expired'];
					$budget_max_Nl = intval('100000');
					
					if(($balance >0 ) || (($balance <=0) && ($manual_override == 1) && ($expired == 0)))
					{
						$exisiting_Nl = 1;
						$balance_NL = $row_arr['budget'] * 2 > intval('100000') ? $row_arr['budget'] * 2 : intval('100000');
						if($balance_NL > $budget_max_Nl)
						{
							$budget_max_Nl = $balance_NL;
						}
						break;
					}
					
				}	
			}
			
			$res_arr['is_national_listing'] = 0;	
			$res_arr['is_vfl_national'] = 0;	
			if($exisiting_vfl == 1)
			{
				$res_arr['is_vfl_national'] = 1;	
				//echo "ccccccc==".$budget_vfl;
				$res_arr['lifetime_maxupfrontbudget_national']	= intval($budget_max_vfl);
				$res_arr['lifetime_minupfrontbudget_national']	= intval('25000'); 
				$res_arr['lifetime_previous_upfrontbudget_national']	= intval($budget_vfl);
				$res_arr['lifetime_stateupfrontbudget_national']	= intval('12500');
				$res_arr['lifetime_zone_maxupfrontbudget_national']	= intval('50000');
				//echo "vdbvsb";
			}
			else if($exisiting_paid == 1)
			{
				$res_arr['lifetime_maxupfrontbudget_national']		= intval('100000');
				$res_arr['lifetime_minupfrontbudget_national']		= intval('25000'); 
				$res_arr['lifetime_previous_upfrontbudget_national']	= 0;
				$res_arr['lifetime_stateupfrontbudget_national']	= intval('12500');
				$res_arr['lifetime_zone_maxupfrontbudget_national']	= intval('50000');
			}
			else if($exisiting_Nl == 1)
			{
				$res_arr['is_national_listing'] = 1;	
				$res_arr['lifetime_maxupfrontbudget_national']		= intval($budget_max_Nl);
				$res_arr['lifetime_minupfrontbudget_national']	= intval('25000'); 
				$res_arr['lifetime_previous_upfrontbudget_national']		= intval($balance_NL);
				$res_arr['lifetime_stateupfrontbudget_national']	= intval('12500');
				$res_arr['lifetime_zone_maxupfrontbudget_national']	=  intval('50000');
			}	
			else		
			{
				$res_arr['lifetime_maxupfrontbudget_national']	= intval('100000');
				$res_arr['lifetime_minupfrontbudget_national']	= intval('25000');
				$res_arr['lifetime_previous_upfrontbudget_national']	= 0;
				$res_arr['lifetime_stateupfrontbudget_national']	= intval('12500');
				$res_arr['lifetime_zone_maxupfrontbudget_national']	= intval('50000');
			}
			
				$res_arr['lifetime']['banner_maxupfrontbudget_national']	= intval('150000');
				$res_arr['lifetime']['banner_maxecsbudget_national']	= intval('180000');
				$res_arr['lifetime']['banner_stateupfrontbudget_national']	= intval('50000');
				$res_arr['lifetime']['banner_state_ecsbudget_national']	= intval('60000');
				$res_arr['lifetime']['banner_zone_upfrontbudget_national']	= intval('75000');
				$res_arr['lifetime']['banner_zone_ecsbudget_national']	= intval('90000');
				
			
		}
		else
		{
			$res_arr['minupfrontbudget_national'] = 0;
			$res_arr['maxupfrontbudget_national'] = 0;
			$res_arr['stateupfrontbudget_national'] = 0;
			$res_arr['lifetime_maxupfrontbudget_national']	= 0;
			$res_arr['lifetime_minupfrontbudget_national']	= 0;
			$res_arr['lifetime_stateupfrontbudget_national']	= 0;
		}
			
			
			//echo '<pre>';print_r($res_arr);
			$res_arr['errorCode'] = "0";
			$res_arr['errorMsg']  = "Data found";
		}else {
			$res_arr['errorCode'] = "1";
			$res_arr['errorMsg']  = "Data Not found";
		}
		//echo '<pre>';
		//print_r($res_arr);
		return $res_arr;
	}	
	
	function InsertSelectedCites()
	{
		$sql_upt = "UPDATE banner_payment_rotation_temp SET
						   selectedCities 	 = '".$this->selected_cities."',
						   bannerType		 	 = '1'
						   WHERE
						   parentid 			 = '".$this->parentid."' AND
						   active_flag = 1  ";								 
		$res_upt = parent::execQuery($sql_upt, $this->tempconn);
		if($this->trace)
		{
			echo "<br> sql :: ".$sql_upt;
			echo "<br> res :: ".$res_upt;
		}
		if($res_upt)
		{
			$res_arr['code'] = "200";
			$res_arr['Msg']  = "Data updated";
		}else
		{
			$res_arr['code'] = "503";
			$res_arr['Msg']  = "Data not updated";
		}
		
		return $res_arr;
	}
	
	function FetchSelectedCites()
	{
		$sql_sel = "SELECT  bannerType, selectedCities FROM banner_payment_rotation_temp WHERE  parentid = '".$this->parentid."' AND active_flag = 1";								 
		$res_sel = parent::execQuery($sql_sel, $this->tempconn);
		if($this->trace)
		{
			echo "<br> sql :: ".$sql_sel;
			echo "<br> res :: ".$res_sel;
			echo "<br> rows:: ".mysql_num_rows($res_sel);
		}
		$selectedCitiesArr = array();
		if($res_sel && mysql_num_rows($res_sel))
		{
			$row_sel = mysql_fetch_assoc($res_sel);
			
			if($row_sel['bannerType'] == 1)
			{
				
				$selectedCitiesArr = json_decode($row_sel['selectedCities'], true);
				if(count($selectedCitiesArr)>0)
				{
					$res_arr['code']  = "200";
					$res_arr['Msg']   = "Data Found";
					$res_arr['data']  = $selectedCitiesArr;
				}
				else
				{
					$res_arr['code'] = "503";
					$res_arr['Msg']  = "Invalid Data";
				}
			}
			else
			{
				$res_arr['code'] = "503";
				$res_arr['Msg']  = "Non National Listing Campaign";
			}
		}else
		{
			$res_arr['code']  = "400";
			$res_arr['Msg']   = "Data Not Found";
		}
		return $res_arr;
	}
	
	function resetNationalBannerData()
	{
		$sql_upt = "UPDATE banner_payment_rotation_temp SET
						   selectedCities 	 = '',
						   bannerType		 = '0'
						   WHERE
						   parentid 			 = '".$this->parentid."' AND
						   active_flag = 1";								 
		$res_upt = parent::execQuery($sql_upt, $this->tempconn);
		if($this->trace)
		{
			echo "<br> sql :: ".$sql_upt;
			echo "<br> res :: ".$res_upt; 
		}
		if($res_upt)
		{
			$res_arr['code'] = "200";
			$res_arr['Msg']  = "Data updated";
		}else
		{
			$res_arr['code'] = "503";
			$res_arr['Msg']  = "Data not updated";
		}
		
		return $res_arr;
	}
	
	
	
	
}


?>
