<?php

class packagepincatupdtclass extends DB
{
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	
	
	var  $parentid,$companynamevalue		= null;
	var $liveversion,$newversion,$liveversionmodule,$newversionmodule,$para_astate;
	var $liveduration	= null;
	var $returnbudgetarr = array();
	var  $data_city	= null;
	var $module=null;
	var $version=null;
	var $sphinx_id=null;
	var $docid=null;
	var $bidding_details_summary_duration; 
	var $national_catid_array,$finalinv_array;
	

	function __construct($params)
	{
		//$this->printerror(99,'stopped');	
		$this->params = $params;
		if(isset($this->params['data_city']) && trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}else
		{
			//$errorarray['errormsg']='data_city missing';
			$this->printerror(12,'data_city missing');
			//echo json_encode($errorarray); exit;
		}

		if( isset($this->params['parentid']) && trim($this->params['parentid']) != "" && $this->params['parentid'] != null)
		{
			$this->parentid  = $this->params['parentid']; //initialize datacity
		}else
		{
			//$errorarray['errormsg']='parentid missing';
			$this->printerror(13,'parentid missing');
			//echo json_encode($errorarray); exit;
		}

		

		if(isset($this->params['action']) &&  trim($this->params['action']) != "" && $this->params['action'] != null)
		{
			$this->api_action  = strtolower($this->params['action']); //initialize action
		}else
		{
			//$errorarray['errormsg']='action missing';
			$this->printerror(14,'action missing');
		}
		
		if(isset($this->params['source']) &&  trim($this->params['source']) != "" && $this->params['source'] != null)
		{
			$this->api_source  = $this->params['source']; //initialize source
		}else
		{			
			$this->printerror(15,'source missing');
		}
		
		if(isset($this->params['module']) &&  trim($this->params['module']) != "" && $this->params['module'] != null)
		{
			$this->module  = $this->params['module']; //initialize module
		}
		
		if(isset($this->params['version']) &&  trim($this->params['version']) != "" && $this->params['version'] != null)
		{
			$this->version  = $this->params['version']; //initialize version 
		}
		
		
		if($this->api_action=='packbudgetcalbypin')
		{	
			if($this->version==null)
			{
				$this->printerror(16,'version missing');
			}
			
			if($this->module==null)
			{
				$this->printerror(17,'module missing');
			}
		}
		
		if($this->api_action=='apportion')
		{	
			if(isset($this->params['catlist']) && $this->params['catlist']!='')
			{
				$this->requestedcatlist 	= str_replace('/','', $this->params['catlist']);
			}else
			{
				$this->printerror(18,'catlist is not proper');
			}

			if(strlen($this->requestedcatlist)<2)
			{
				$this->printerror(19,'catlist is not proper');
			}
		}


		if(isset($this->params['username']) && trim($this->params['username']) != "" && isset($this->params['usercode']) && trim($this->params['usercode']) != "")
		{
			$this->username  = $this->params['username']; 
			$this->usercode  = $this->params['usercode']; 
			
		}else
		{
			//$errorarray['errormsg']='username or usercode is missing';
			$this->printerror(20,'username or usercode is missing');
			//echo json_encode($errorarray); exit;
		}

		$this->companyClass_obj = new companyClass();
        
		$this->setServers();
		
		
		$this->centraliselogging($this->params,'API Initialization',$this->api_source);
	}
		
	// Function to set DB connection objects
	function setServers()
	{
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->dbConDjds  		= $db[$data_city]['d_jds']['master'];
		$this->Idc   			= $db[$data_city]['idc']['master'];
		$this->dbConbudget  	= $db[$data_city]['db_budgeting']['master'];	
		$this->fin   			= $db[$data_city]['fin']['master'];	
		$this->dbConTme   		= $db[$data_city]['tme_jds']['master'];	
	}

	function prepareParamsforInvMgmt($temparray=null){
		
		$InvMgmtparams['data_city']	= $_REQUEST['data_city'];		
		$InvMgmtparams['parentid']	= $_REQUEST['parentid'];		
		$InvMgmtparams['module']	= $_REQUEST['module'];
		

		if(count($temparray))
		{
			// first we will unset the keys then we will set 
			if(is_array($temparray['unset'])  && count($temparray['unset']))
			{
				foreach($temparray['unset'] as $keytounset)
				{
					unset($InvMgmtparams[$keytounset]);
				}
			}

			if(is_array($temparray['set'])  && count($temparray['set']))
			{
				foreach($temparray['set'] as $key=>$value)
				{
					$InvMgmtparams[$key]=$value;
				}
			}
		}

		return $InvMgmtparams;		
	}

	
	function printerror($errorcode,$errormsg,$apiresult=null)
	{
		/*
		 * 1- No Inventory Loss So can not use this api
		 * 2- No data in tbl_companymaster_generalinfo live as well as IDC  
		*/	
		$result['error']['code'] = $errorcode;
		$result['error']['msg'] = $errormsg;
		if($apiresult!=null)
		{
			$result['error']['apiresult'] = $apiresult;
		}
		
		$this->centraliselogging($result,'Error found ',$this->api_source);
		
		$resultstr= json_encode($result);
		print($resultstr);
		die;
	}
	
	function getNewVersion()
	{
		$sql="SELECT ifnull( MAX(version*1) +10 ,11) AS newversion FROM payment_apportioning WHERE parentid='".$this->parentid."' AND (version%10)='1'";
		$res= parent::execQuery($sql,$this->fin);
		$row = mysql_fetch_assoc($res);
		return $row['newversion'];
	}
	
	function getCatPinFromBiddingDetails()
	{
		$resultarr= array();
		
		// checking balance on companymaster_finance 		
		$sql= "select group_concat(campaignid) as campaignidlist ,count(1) as cnt,version,max(duration) as maxduration  from tbl_companymaster_finance where parentid='".$this->parentid."' and balance>0 and campaignid in (1,2) ";
		$res= parent::execQuery($sql,$this->fin);
		
		$arr = mysql_fetch_assoc($res);
		
		if(intval($arr['cnt'])==1)
		{
			$finversion = $arr['version'];
			
		}elseif(intval($arr['cnt'])==2)
		{
			$this->printerror(51,'Balance present on PDG campaign in tbl_companymaster_finance');
			
		}else
		{
			$this->printerror(52,'No balance in package campaign tbl_companymaster_finance');
		}
		
		#if(intval($arr['maxduration'])>365)
		#{
		#	$this->printerror(54,' duration is more than a year ');
		#}
		
		
		$sql= "select group_concat(distinct catid) as catidlist , group_concat(distinct pincode) as pincodelist  ,count(1) as cnt,version  from tbl_bidding_details  where parentid='".$this->parentid."'  and campaignid=1  ";
		$res= parent::execQuery($sql,$this->fin);
		
		$catpinlistarr = mysql_fetch_assoc($res);
		
		if(intval($catpinlistarr['cnt'])==0)
		{
			$this->printerror(53,'data not found on tbl_bidding_details');
			
		}else
		{
			$resultarr['catidlist']=$catpinlistarr['catidlist'];
			$resultarr['pincodelist']=$catpinlistarr['pincodelist'];
			$resultarr['tbl_companymaster_finance_version']=$finversion;
			$resultarr['tbl_bidding_details_version']=$catpinlistarr['version'];;
		}
		
		return $resultarr;		
	}
	
	function Insertinto_tbl_bidding_details_summary()
	{
		$pincode_list_str  = null;
		$category_list_str = null;

		// first we will check data is presnt in live if we do not get data then we will fetch from IDC
		$gi_arr = array();
		$cat_params = array();
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['table'] 		= 'gen_info_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= $this->parentid;
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['page'] 		= 'packagepincatupdtclass';
		$cat_params['fields']		= 'companyname,pincode,latitude,longitude,landline,mobile';

		$res_gen_info1		= 	array();
		$res_gen_info1		=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

		if(!empty($res_gen_info1) && $res_gen_info1['errors']['code']==0){

			if(count($cat_api_res['results']['data']) == 0){
				$this->printerror(2,'No data in tbl_companymaster_generalinfo');
			}else{
				
				$gi_arr 		=	$cat_api_res['results']['data'][$this->parentid];

				$id_gen_sql = "select * from db_iro.tbl_id_generator where parentid='".$this->parentid."'";
				$id_gen_res= parent::execQuery($id_gen_sql,$this->dbConIro);
				
				if(mysql_num_rows($id_gen_res)==0)
				{	$this->printerror(3,'No data in tbl_id_generator ');	}

				if(mysql_num_rows($id_gen_res))
				{	$id_gen_arr= mysql_fetch_assoc($id_gen_res);	}	

				$contact_details = $gi_arr['landline'].",".$gi_arr['mobile'];
				$contact_details_array = explode(',',$contact_details);
				$contact_details_array = array_filter($contact_details_array);

				$contact_details_str='';

				if(count($contact_details_array))
				{	$contact_details_str = implode(',',$contact_details_array);	}
				
				$catpinlist = $this->getCatPinFromBiddingDetails();
				
				if( intval($catpinlist['tbl_companymaster_finance_version'])!=intval($catpinlist['tbl_bidding_details_version']))
				{
					$this->printerror(5,'version of tbl_companymaster_finance is : '.$catpinlist['tbl_companymaster_finance_version']. ' , and version of tbl_bidding_details is '.$catpinlist['tbl_bidding_details_version'].' so can not process ');
				}else
				{
					$this->liveversion = intval($catpinlist['tbl_bidding_details_version']);
				}
				
				if(defined('TRACE_MODE') && TRACE_MODE!=0)
				{			
					echo '<br>category_list_str--'.$catpinlist['catidlist'];
					echo '<br>pincode_list_str--'.$catpinlist['pincodelist'];
				}
				
				$this->pureflexiChecking();
				
				$category_list_str =  $catpinlist['catidlist'];
				$pincode_list_str  = $catpinlist['pincodelist'];
				
				$this->categoryChangeChecking($category_list_str,$this->requestedcatlist);
				
				$pincodejson = ' concat(\'{"a_a_p":"\',pincode_list,\'","n_a_a_p":"\',pincode_list,\'"}\')'  ;		

				$this->newversion = $this->liveversion;
				
				$this->companynamevalue = addslashes(stripcslashes($gi_arr['companyname']));

				
				$update_sql= " INSERT INTO tbl_bidding_details_summary set
								sphinx_id		='".$id_gen_arr['sphinx_id']."',
								parentid		='".$id_gen_arr['parentid']."',
								docid			='".$id_gen_arr['docid']."',
								companyname		='".addslashes(stripcslashes($gi_arr['companyname']))."',
								data_city		='".$id_gen_arr['data_city']."',
								pincode			='".$gi_arr['pincode']."',
								latitude		='".$gi_arr['latitude']."',
								longitude		='".$gi_arr['longitude']."',
								version			='".$this->newversion."',
								module			='CS',
								contact_details	='".$contact_details_str."',
								category_list	='".$this->requestedcatlist."',
								dealclosed_flag	=0,
								pincode_list	='".$pincode_list_str."',
								pincodejson     =".$pincodejson.",
								updatedon		='".date('Y-m-d H:i:s')."',
								username		='".addslashes(stripcslashes($this->username))."',
								updatedby		='".addslashes(stripcslashes($this->usercode))."' 
								ON DUPLICATE KEY UPDATE
								sphinx_id		='".$id_gen_arr['sphinx_id']."',						
								docid			='".$id_gen_arr['docid']."',
								companyname		='".addslashes(stripcslashes($gi_arr['companyname']))."',
								data_city		='".$id_gen_arr['data_city']."',
								pincode			='".$gi_arr['pincode']."',
								latitude		='".$gi_arr['latitude']."',
								longitude		='".$gi_arr['longitude']."',
								module			='CS',
								contact_details	='".$contact_details_str."',
								category_list	='".$this->requestedcatlist."',
								dealclosed_flag	=0,
								pincode_list	='".$pincode_list_str."',
								pincodejson     =".$pincodejson.",
								updatedon			='".date('Y-m-d H:i:s')."',
								username			='".addslashes(stripcslashes($this->username))."',
								updatedby			='".addslashes(stripcslashes($this->usercode))."'
								";

				parent::execQuery($update_sql, $this->dbConbudget);

				if(defined('TRACE_MODE') && TRACE_MODE!=0)
				{
					echo '<br><b>DB Query:</b>'.$update_sql;
					echo '<br><b>dbConbudget:</b>'; print_r($this->dbConbudget);
					echo '<br><b>Error:</b>'.$this->mysql_error;
				}
			}
		}


		/*$gi_sql = "select companyname,pincode,latitude,longitude,landline,mobile from tbl_companymaster_generalinfo where parentid='".$this->parentid."'";		
		$gi_res= parent::execQuery($gi_sql,$this->dbConIro);
		
		if(mysql_num_rows($gi_res)==0)
		{
			$this->printerror(2,'No data in tbl_companymaster_generalinfo');
		}		
		
		$id_gen_sql = "select * from db_iro.tbl_id_generator where parentid='".$this->parentid."'";
		$id_gen_res= parent::execQuery($id_gen_sql,$this->dbConIro);
		if(mysql_num_rows($id_gen_res)==0)
		{
			$this->printerror(3,'No data in tbl_id_generator ');
		}
		

		if(mysql_num_rows($gi_res))
		{
			$gi_arr= mysql_fetch_assoc($gi_res);			
		}	

		
		if(mysql_num_rows($id_gen_res))
		{
			$id_gen_arr= mysql_fetch_assoc($id_gen_res);			
		}	

		$contact_details = $gi_arr['landline'].",".$gi_arr['mobile'];
		$contact_details_array = explode(',',$contact_details);
		$contact_details_array = array_filter($contact_details_array);

		$contact_details_str='';
		if(count($contact_details_array))
		{
			$contact_details_str = implode(',',$contact_details_array);
		}


		$catpinlist = $this->getCatPinFromBiddingDetails();
		
		if( intval($catpinlist['tbl_companymaster_finance_version'])!=intval($catpinlist['tbl_bidding_details_version']))
		{
			$this->printerror(5,'version of tbl_companymaster_finance is : '.$catpinlist['tbl_companymaster_finance_version']. ' , and version of tbl_bidding_details is '.$catpinlist['tbl_bidding_details_version'].' so can not process ');
		}else
		{
			$this->liveversion = intval($catpinlist['tbl_bidding_details_version']);
		}
		
		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{			
			echo '<br>category_list_str--'.$catpinlist['catidlist'];
			echo '<br>pincode_list_str--'.$catpinlist['pincodelist'];
		}
		
		$this->pureflexiChecking();
		
		
		$category_list_str =  $catpinlist['catidlist'];
		$pincode_list_str  = $catpinlist['pincodelist'];
		
		$this->categoryChangeChecking($category_list_str,$this->requestedcatlist);
		
		$pincodejson = ' concat(\'{"a_a_p":"\',pincode_list,\'","n_a_a_p":"\',pincode_list,\'"}\')'  ;		
		
		$this->newversion = $this->liveversion;
		
		$this->companynamevalue = addslashes(stripcslashes($gi_arr['companyname']));

		
		$update_sql= " INSERT INTO tbl_bidding_details_summary set
						sphinx_id		='".$id_gen_arr['sphinx_id']."',
						parentid		='".$id_gen_arr['parentid']."',
						docid			='".$id_gen_arr['docid']."',
						companyname		='".addslashes(stripcslashes($gi_arr['companyname']))."',
						data_city		='".$id_gen_arr['data_city']."',
						pincode			='".$gi_arr['pincode']."',
						latitude		='".$gi_arr['latitude']."',
						longitude		='".$gi_arr['longitude']."',
						version			='".$this->newversion."',
						module			='CS',
						contact_details	='".$contact_details_str."',
						category_list	='".$this->requestedcatlist."',
						dealclosed_flag	=0,
						pincode_list	='".$pincode_list_str."',
						pincodejson     =".$pincodejson.",
						updatedon		='".date('Y-m-d H:i:s')."',
						username		='".addslashes(stripcslashes($this->username))."',
						updatedby		='".addslashes(stripcslashes($this->usercode))."' 
						ON DUPLICATE KEY UPDATE
						sphinx_id		='".$id_gen_arr['sphinx_id']."',						
						docid			='".$id_gen_arr['docid']."',
						companyname		='".addslashes(stripcslashes($gi_arr['companyname']))."',
						data_city		='".$id_gen_arr['data_city']."',
						pincode			='".$gi_arr['pincode']."',
						latitude		='".$gi_arr['latitude']."',
						longitude		='".$gi_arr['longitude']."',
						module			='CS',
						contact_details	='".$contact_details_str."',
						category_list	='".$this->requestedcatlist."',
						dealclosed_flag	=0,
						pincode_list	='".$pincode_list_str."',
						pincodejson     =".$pincodejson.",
						updatedon			='".date('Y-m-d H:i:s')."',
						username			='".addslashes(stripcslashes($this->username))."',
						updatedby			='".addslashes(stripcslashes($this->usercode))."'
						";

		parent::execQuery($update_sql, $this->dbConbudget);

		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b>DB Query:</b>'.$update_sql;
			echo '<br><b>dbConbudget:</b>'; print_r($this->dbConbudget);
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}*/
	}

	function getpayment_apportioningDetails($ver=null)
	{
		$returnarr = array();
		$sqlcond= null;
		if($ver != null)
		{
			$sqlcond = ' and version= '.$ver;
		}
		
		$sql = " SELECT campaignId as campaignid,version,budget,duration,entry_date,start_date,app_duration,app_amount FROM payment_apportioning where parentid='".$this->parentid."' ".$sqlcond;
		$res= parent::execQuery($sql,$this->fin);
		
		if(mysql_num_rows($res)>0)
		{
			while($arr= mysql_fetch_assoc($res))
			{
				$returnarr[$arr['version']][$arr['campaignid']] = $arr;
			}
		}
		
		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b>getpayment_apportioningDetails <br> returnarr </b>'; print_r($returnarr);
		}
						
		return $returnarr;
	}		

	function prepareParamsforBudgetCalculation($temparray=null)
	{		
		$paymentapportioningarr = $this->getpayment_apportioningDetails($this->liveversion);
		
		if(isset($paymentapportioningarr[$this->liveversion][1]) && isset($paymentapportioningarr[$this->liveversion][1]['budget']) && intval($paymentapportioningarr[$this->liveversion][1]['budget'])>0 )
		{
			$budget = $paymentapportioningarr[$this->liveversion][1]['budget'];
			$this->liveduration = $paymentapportioningarr[$this->liveversion][1]['duration'];
		}
		else
		{
			$this->printerror(3,'Package budget not present on payment_apportioning for vesrion '.$this->liveversion);
		}		
		
		
		
		 
		
		if($this->liveduration==365)
		{
			$tenure=12;
			
		}elseif($this->liveduration==730)
		{
			$tenure=24;;
			
		}else
		{
			if($this->liveduration%365==0)
			{
				$tenure = $this->liveduration/365*12;// number of month from exact 
				
			}else
			{
				$tenure = $this->liveduration / 30;
			}
			
		}
		
		$BdgtCallparams['data_city']	= $this->data_city;		
		$BdgtCallparams['version']      = $this->newversion;
		$BdgtCallparams['parentid']		= $this->parentid;
		$BdgtCallparams['tenure']		= $tenure;
		$BdgtCallparams['mode']		    = 3; // initialize mode 1-best positon 2-fixed position 3-package 4-renewal 5-exclusive
		$BdgtCallparams['option']	    = 1; // default 1, max 7
		$BdgtCallparams['module']	    = 'CS' ;
		$BdgtCallparams['custompackage'] = 1;
		$BdgtCallparams['packagebgt_yrly'] = $budget; // package budget 
		
		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b> prepareParamsforBudgetCalculation <br> BdgtCallparams </b>'; print_r($BdgtCallparams);
		}
		
		return $BdgtCallparams;
	}
	
	function prepareParamsforBudgetDetailsObj()
	{
		$sql = " select * from tbl_bidding_details_summary where parentid ='".$this->parentid."' and version ='".$this->version."'";
		$res = parent::execQuery($sql, $this->dbConbudget);
		
		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>mysql_num_rows </b>'.mysql_num_rows($res);
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
					
		if(mysql_num_rows($res)==0)
		{
			$this->printerror(31,'Data not present on tbl_bidding_details_summary for parentid : '.$this->parentid.' , version :'.$this->version);
		}else
		{
			$row= mysql_fetch_assoc($res);
		}
		
		// need to calcullate month from duration 
		
		if( $row['duration']==null)
		{
			$this->printerror(32,'duration not present on tbl_bidding_details_summary for parentid : '.$this->parentid.' , version :'.$this->version);
		}
		
		if(trim($row['pincodebudgetjson'])=='')
		{
			$this->printerror(35,'pincodebudgetjson not set on tbl_bidding_details_summary for parentid : '.$this->parentid.' , version :'.$this->version);
		}
		
		$this->bidding_details_summary_duration = intval($row['duration']);
		
		if($this->bidding_details_summary_duration==365)
		{
			$tenure=12;
			
		}elseif($this->bidding_details_summary_duration==730)
		{
			$tenure=24;;
			
		}else
		{
			if($this->bidding_details_summary_duration%365==0)
			{
				$tenure = $this->bidding_details_summary_duration/365*12;// number of month from exact 
				
			}else
			{
				$tenure = $this->bidding_details_summary_duration / 30;
			}
			
		}
		
		
		$BdgtCallparams['data_city']	= $this->data_city;		
		$BdgtCallparams['version']      = $this->version;
		$BdgtCallparams['parentid']		= $this->parentid;
		$BdgtCallparams['tenure']		= $tenure;
		$BdgtCallparams['mode']		    = 3; // initialize mode 1-best positon 2-fixed position 3-package 4-renewal 5-exclusive
		$BdgtCallparams['option']	    = 1; // default 1, max 7
		$BdgtCallparams['module']	    = $this->module ;
		
		// package special params for pincode budget
		$BdgtCallparams['pinbgt'] = 1;
		$BdgtCallparams['pinview'] = 0;
		
		return $BdgtCallparams;
	}
	
	function update_tbl_bidding_details_intermediate($inv_new,$versionval)
	{
		$fp_budget=0;
		$package_budget=0;
		$this->finalinv_array = $inv_new;		

		$delsql= " Delete from tbl_bidding_details_intermediate where parentid ='".$this->parentid."' and version ='".$versionval."'";
		parent::execQuery($delsql, $this->dbConbudget);
		
		foreach($inv_new as $catid=>$catidarr)
		{
			$cat_budget = 0;
			foreach ($catidarr as $pincode=>$pincodearr)
			{
				$cat_budget+= $inv_new[$catid][$pincode]['budget'];

				if($inv_new[$catid][$pincode]['pos']==100)
				{
					$package_budget+=$inv_new[$catid][$pincode]['budget'];
					
				}else
				{
					$fp_budget+=$inv_new[$catid][$pincode]['budget'];
				}
			}
			
			$sql = "insert into tbl_bidding_details_intermediate set
					parentid		='".$this->parentid."',
					catid			='".$catid."',
					national_catid	='".$this->national_catid_array[$catid]."',
					cat_budget		='".$cat_budget."',
					version			='".$versionval."',
					pincode_list 	='".json_encode($inv_new[$catid])."',							
					updatedby		='".$this->usercode."',
					updatedon=now()
					ON DUPLICATE KEY UPDATE							
					catid			='".$catid."',
					national_catid	='".$this->national_catid_array[$catid]."',
					cat_budget		='".$cat_budget."',
					pincode_list 	='".json_encode($inv_new[$catid])."',							
					updatedby		='".$this->usercode."',
					updatedon=now()	";
					parent::execQuery($sql, $this->dbConbudget);

					
			if(defined('TRACE_MODE') && TRACE_MODE!=0)
			{
				
				echo '<br><b>tbl_bidding_details_intermediate DB  Query:</b>'.$sql;				
				echo '<br><b>Error:</b>'.$this->mysql_error;				
			}
			
		}

		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{			
			asort($inv_new);
			echo '<br>inv_new'; print_r($inv_new);
		}
	
		
		$this->returnbudgetarr['fp_budget']=$fp_budget;
		$this->returnbudgetarr['package_budget']=$package_budget;
		
		return $this->returnbudgetarr;
	}

	function getcatpinarrayforbudgetsubmit($budgetDetailsAPIresult)
	{// This is to traverse the budget details api response and make data which will get updated on bidding details intermediate 

		foreach ($budgetDetailsAPIresult['result']['c_data'] as $catid =>$catidvalarr)
		{
			$this->national_catid_array[$catid]=$catidvalarr['ncid'];
			
			foreach ($catidvalarr['pin_data'] as $pincode =>$pincodevalarr)
			{
				
				if(defined('TRACE_MODE') && TRACE_MODE!=0)
				{
					echo '<br>pincodevalarr'; print_r($pincodevalarr);
				}
		
				$inv_new[$catid][$pincode]['cnt_f'] = $pincodevalarr['cnt_f'];
				$inv_new[$catid][$pincode]['pin']   = $pincode;
				$inv_new[$catid][$pincode]['pos']   = 100;
				$inv_new[$catid][$pincode]['bidvalue'] = $pincodevalarr['pos'][100]['bidvalue'];
				$inv_new[$catid][$pincode]['budget'] = $pincodevalarr['flexi_bgt'];
				$inv_new[$catid][$pincode]['inv'] 	 = $pincodevalarr['pos'][100]['inv_avail'];
			}
		}
		return $inv_new;
	}
	
	function budgetCalculationAndBudgetSubmit()
	{
		$BdgtCallparams = $this->prepareParamsforBudgetCalculation();
		
		$budgetDetailsClass_obj = new budgetDetailsClass($BdgtCallparams);
		$budgetDetailsAPIresult = $budgetDetailsClass_obj->getBudget();
		
		$inv_new = $this->getcatpinarrayforbudgetsubmit($budgetDetailsAPIresult);		
		$budget = $this->update_tbl_bidding_details_intermediate($inv_new,$this->newversion);
	}

	function budgetcalculationsimulation()
	{
		/* This function will give the simulation of renew budget
		 * To call the renew budget we need to pass the category and pincode to the service 
		*/
		
		$this->Insertinto_tbl_bidding_details_summary();
		$this->budgetCalculationAndBudgetSubmit();
		$this->callupdateActualBudget($this->newversion,$this->liveduration);
		//$this->callselfSignupAPI();
		/*if(in_array($this->parentid,array('PXX11.XX11.180215123251.E4V1','PXX11.XX11.180215122516.T6Q3','PXX22.XX22.110906165241.S2Y2')))
		{*/
			$this->callInvMgmt();
			
		/*}else
		{
			$this->callselfSignupAPI();
		}*/
	}
	
	function callInvMgmt()
	{
		$curlobj = new CurlClass();
	
		$configclassobj = new configclass();
		$urldetails		= $configclassobj->get_url(urldecode($this->data_city));

		$inMgmtParamsarr['data_city']    = $this->data_city;
		$inMgmtParamsarr['parentid']     = $this->parentid;
		$inMgmtParamsarr['astatus']      = 1;
		$inMgmtParamsarr['astate']       = 1;
		$inMgmtParamsarr['version']      = $this->liveversion;
		
		$curlurl = $urldetails['jdbox_service_url'].'invMgmt.php';
		$curlobj->setOpt(CURLOPT_CONNECTTIMEOUT, 30);
		$curlobj->setOpt(CURLOPT_TIMEOUT, 900);
		$output  = $curlobj->get($curlurl,$inMgmtParamsarr);
		$output_arr= json_decode($output,true);
		
		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo "<br>curlurl".$curlurl;print_r($inMgmtParamsarr);
			echo '<br>output_arr -- '; print_r($output_arr);
			echo '<br>output_arr-- ';
		}
		
		$this->centraliselogging($inMgmtParamsarr,'invMgmt.php call ',$this->api_source,$curlurl,$output);
		
		 if( is_array($output_arr) && count($output_arr['results']['fail']) <= 0 && $output_arr['error']['code'] == 0 )
		 {
			$inMgmtParamsarr_live['data_city']    = $this->data_city;
			$inMgmtParamsarr_live['parentid']     = $this->parentid;
			$inMgmtParamsarr_live['astatus']      = 2;
			$inMgmtParamsarr_live['astate']       = 3;
			$inMgmtParamsarr_live['version']      = $this->liveversion;
			
			$curlurl=$urldetails['jdbox_service_url'].'invMgmt.php';
			$curlobj->setOpt(CURLOPT_CONNECTTIMEOUT, 30);
			$curlobj->setOpt(CURLOPT_TIMEOUT, 900);
			$output_live    = $curlobj->get($curlurl,$inMgmtParamsarr_live);
			$output_live_arr= json_decode($output_live,true);
			if(defined('TRACE_MODE') && TRACE_MODE!=0)
			{
				echo "<br>curlurl".$curlurl;print_r($inMgmtParamsarr_live);
				echo '<br>output_arr -- '; print_r($output_live_arr);
				echo '<br>output_arr-- ';
			}
			$this->centraliselogging($inMgmtParamsarr_live,'invMgmt.php call ',$this->api_source,$curlurl,$output_live);
			
		 }
	}
	
	function callselfSignupAPI()
	{
		$selfSignupParamsarr = $this->prepareParamsforselfSignupAPI();
		
		$curlobj = new CurlClass();
	
		$configclassobj= new configclass();
		$urldetails= $configclassobj->get_url(urldecode($this->data_city));

		$curlurl=$urldetails['jdbox_service_url'].'insSelfSignUp.php';
		
		$curlobj->setOpt(CURLOPT_CONNECTTIMEOUT, 30);
		$curlobj->setOpt(CURLOPT_TIMEOUT, 900);
		#$output = $curlobj->post($curlurl,$selfSignupParamsarr);
		#$output = $curlobj->post($curlurl,json_encode($selfSignupParamsarr),1);
		$output = $curlobj->get($curlurl,$selfSignupParamsarr);
		$output_arr= json_decode($output,true);
		
		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo "<br>curlurl".$curlurl;print_r($selfSignupParamsarr);
			echo '<br>output_arr -- '; print_r($output_arr);
			echo '<br>output_arr-- ';
		}
		
		$this->centraliselogging($selfSignupParamsarr,'insSelfSignUp.php call ',$this->api_source,$curlurl,$output);
		
		return $output_arr;
	}
	
	function prepareParamsforselfSignupAPI()
	{
		$selfSignupAPIParams= array();
		$selfSignupAPIParams['payment_type'] 	= 1;
		$selfSignupAPIParams['dealclosetype'] 	= 3;
		$selfSignupAPIParams['amount_paid'] 	= 0;
		$selfSignupAPIParams['balance']			= 0;
		$selfSignupAPIParams['source']			= 'web_edit';
		$selfSignupAPIParams['payment_done_flag']= 0 ;

		$selfSignupAPIParams['parentid']	= $this->parentid;
		$selfSignupAPIParams['companyname']	= $this->companynamevalue ;
		$selfSignupAPIParams['data_city']	= $this->data_city;
		
		$campaigninfoarray = array();
		$campaigninfoarray['budget'] 	= $this->returnbudgetarr['package_budget'] ;
		$campaigninfoarray['duration'] 	= $this->liveduration ;
		$campaigninfoarray['combo'] 	='Web Edit' ;
		$campaigninfoarray['actcamp']	= 1;		
		$campaigninfo_arra_withkey= array('99'=>$campaigninfoarray);		
		$selfSignupAPIParams['campaigninfo'] = json_encode($campaigninfo_arra_withkey);

		$selfSignupAPIParams['requested_date'] = date('Y-m-d H:i:s');
		$selfSignupAPIParams['payment_done_on'] = date('Y-m-d H:i:s');
		
		$trans_idValue = $this->gettrans_id();
		
		$selfSignupAPIParams['trans_id'] = $trans_idValue;
		$selfSignupAPIParams['version']= $this->newversion;
		
		$selfSignupAPIParams['user_code']	= $this->usercode;
		$selfSignupAPIParams['user_name']	= $this->username;
		
		return $selfSignupAPIParams;
	}
	
	function gettrans_id()
	{
        $dataArr['length']=15;
        $dataArr['prefix'] = '';
        
		$length  =  empty($dataArr['length']) ? 6 : $dataArr['length'];
		$prefix  =  empty($dataArr['prefix']) ? '' : $dataArr['prefix'];
                        
        //generate a random id encrypt it and store it in $rnd_id 
		$rnd_id = crypt(uniqid(rand(),1)); 

		//to remove any slashes that might have come 
		$rnd_id = strip_tags(stripslashes($rnd_id)); 

		//Removing any . or / and reversing the string 
		$rnd_id = str_replace(".","",$rnd_id); 
		$rnd_id = strrev(str_replace("/","",$rnd_id)); 

		//finally take the first required length characters from the $rnd_id 
		$rnd_id = $prefix.substr($rnd_id,0,$length); 
                
        return $rnd_id;
	}
	
	function callupdateActualBudget($versionval,$durationval)
	{// This will update actual budget for the contract
		$actual_fp_budget =$this->returnbudgetarr['fp_budget'];
		$actual_package_budget= $this->returnbudgetarr['package_budget'];
		$actual_total_budget	= $actual_fp_budget + $actual_package_budget;
		
		
		$sqlUpdtActualBudget = "INSERT INTO tbl_bidding_details_summary SET 
								parentid 				= '".$this->parentid."',
								version 				= '".$versionval."',
								sys_fp_budget			= '".$actual_fp_budget."',				
								sys_package_budget		= '".$actual_package_budget."',								
								sys_total_budget		= '".$actual_total_budget."',
								actual_fp_budget		= '".$actual_fp_budget."',				
								actual_package_budget	= '".$actual_package_budget."',								
								actual_total_budget		= '".$actual_total_budget."',
								duration				= '".$durationval."',
								dealclosed_flag			=  0,
								updatedon				= '".date('Y-m-d H:i:s')."',
								username				= '".addslashes(stripcslashes($this->username))."',
								updatedby				= '".addslashes(stripcslashes($this->usercode))."'
								ON DUPLICATE KEY UPDATE
								sys_fp_budget			= '".$actual_fp_budget."',				
								sys_package_budget		= '".$actual_package_budget."',								
								sys_total_budget		= '".$actual_total_budget."',
								actual_fp_budget		= '".$actual_fp_budget."',				
								actual_package_budget	= '".$actual_package_budget."',
								actual_total_budget		= '".$actual_total_budget."',
								duration				= '".$durationval."',
								dealclosed_flag			=  0,
								updatedon				= '".date('Y-m-d H:i:s')."',
								username				= '".addslashes(stripcslashes($this->username))."',
								updatedby				= '".addslashes(stripcslashes($this->usercode))."'";
		$resUpdtActualBudget = parent::execQuery($sqlUpdtActualBudget, $this->dbConbudget);
		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b>sqlUpdtActualBudget</b> <br>'; print_r($sqlUpdtActualBudget); // uncomment
		}
	}
	 
	function apportion()
	{
		
		
		$this->budgetcalculationsimulation();
		
		$retultarr['error']['code'] = 0;
		$retultarr['error']['msg'] = 'successful';		
		
		
		return $retultarr;
	}
	
	function categoryChangeChecking($biddingdetailscategory,$requestedcategory)
	{
		$biddingdetailscategoryarray	= explode(',',$biddingdetailscategory);
		$requestedcategoryarray			= explode(',',$requestedcategory);
					
		
		if( count(array_diff($biddingdetailscategoryarray,$requestedcategoryarray))==0 && count(array_diff($requestedcategoryarray,$biddingdetailscategoryarray))==0  )
		{
			$this->printerror(22,'There is no any category change, so can not proceed');
		}
	}
	
	function pureflexiChecking()
	{		
		$sql = "select parentid from tbl_payment_type_dealclosed where parentid='".$this -> parentid."' AND VERSION='".$this->liveversion."' AND ( payment_type like '%flexi_selected_user%' ) limit 1";
		$res = parent::execQuery($sql, $this->fin);
		
		if(mysql_num_rows($res)>0)
		{
			$this->printerror(55,'This API can not be used for this contract,it is a flexi contract (highest to lowest) or life time contract ');
		}
	}
	
	function packbudgetCalculationbypinAndBudgetSubmit()
	{
		$BdgtCallparams = $this->prepareParamsforBudgetDetailsObj();
		
		$budgetDetailsClass_obj = new budgetDetailsClass($BdgtCallparams);
		$budgetDetailsAPIresult = $budgetDetailsClass_obj->getBudget();
		
		$inv_new = $this->getcatpinarrayforbudgetsubmit($budgetDetailsAPIresult);		
		$budget = $this->update_tbl_bidding_details_intermediate($inv_new,$this->version);
	}
	
	function packbudgetcalbypin()
	{ 
		// This function is for wraper of budget calcutaion and submission of pincode wise budget 
		$this->setsphinxid();
		$this->packbudgetCalculationbypinAndBudgetSubmit();
		
		$this->callupdateActualBudget($this->version,$this->bidding_details_summary_duration);
		$this->callupdatefinanceTemp();
		
		$retultarr['error']['code'] = 0;
		$retultarr['error']['msg'] = 'successful';		
		
		
		return $retultarr;
	}
	
	function setsphinxid()
	{
		$sql= "select sphinx_id from tbl_id_generator where parentid='".$this->parentid."'";
		$res = parent::execQuery($sql, $this->dbConIro);

		if($res && mysql_num_rows($res))
		{
			$row= mysql_fetch_assoc($res);
			$this->sphinx_id = $row['sphinx_id'];			
		}else
		{
			$this->printerror(34,'sphinx_id not found ');
		}
	}
	
	function callupdatefinanceTemp()
	{
		$package_budget_value = $this->returnbudgetarr['package_budget'];
		
		$this->financeInsertUpdateTemp(1,array("budget"=>$package_budget_value,"original_budget"=>$package_budget_value,"original_actual_budget"=>$package_budget_value,"duration"=>$this->bidding_details_summary_duration,"recalculate_flag"=>1,"version" =>$this->version));
		
		// setting fp budget 0
		$this->financeInsertUpdateTemp(2,array("budget"=>0,"original_budget"=>0,"original_actual_budget"=>0,"duration"=>0,"recalculate_flag"=>1,"version" =>$this->version));
	}
	
	function financeInsertUpdateTemp($campaignid,$camp_data) 
	{

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
			if(strtolower($this->module) == 'tme'){
				parent::execQuery($compmaster_fin_temp_insert, $this->dbConTme);
			}else{
				parent::execQuery($compmaster_fin_temp_insert, $this->Idc);
			}
		}
        return 0;
    }
	
	function centraliselogging($dataarray,$message,$source,$apiurl=null,$apiurlresponse=null)
	{		
		$post_data = array();
		
		$log_url = 'http://192.168.17.109/logs/logs.php';
		$post_data['ID']                = $this -> parentid;
		$post_data['PUBLISH']           = 'PACKCATPINUPDATE';
		$post_data['ROUTE']             = $source;
		$post_data['CRITICAL_FLAG'] 	= 1;
		$post_data['MESSAGE']       	= $message;
		$post_data['DATA']['url']       = $apiurl;
		$post_data['DATA_JSON']['DataArray'] = json_encode($dataarray);
		$post_data['DATA_JSON']['response'] = $apiurlresponse;
		$post_data['DATA']['user'] = 	$this->usercode;
		$post_data['DATA']['source'] = 	$source;
		
		$post_data = http_build_query($post_data);
				
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $log_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content  = curl_exec($ch);
		curl_close($ch);
		
		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br>centraliselogging<br><b>post_data</b> <br>'; print_r($post_data); // uncomment
			echo '<br> content <br>'; print($content); // uncomment
		}		
	}
	
}



?>
