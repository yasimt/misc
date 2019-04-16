<?php

class inventoryblockingserviceclass extends DB
{
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	
	
	var  $parentid		= null;
	var $liveversion,$newversion,$liveversionmodule,$newversionmodule,$para_astate;
	//var  $version		= null;
	
	var  $data_city	= null;
	var  $api_action = null; 	// area selection option 
	 
	var $InventoryManagementApiResult_existing= null;
	var $bidding_details_summary_category_list;
	var $bidding_details_summary_pincode_list;
	var $national_catid_array,$finalinv_array;
	var $payment_apportioning_budget_duration_arr ;

	var	$budgetMismatchfactorTBDCampid1;
	var	$budgetMismatchfactorTBDCampid2;
	var	$budgetMismatchfactorTBECampid1;
	var	$budgetMismatchfactorTBECampid2;
	var $budgetMismatchfactorTBSCampid1;
	var $budgetMismatchfactorTBSCampid2;
	var $budgetMismatchfactorTBSACampid1;
	var	$budgetMismatchfactorTBSACampid2;


	function __construct($params)
	{
		//$this->printerror(99,'stopped');	
		$this->params = $params;
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}else
		{
			//$errorarray['errormsg']='data_city missing';
			$this->printerror(10,'data_city missing');
			//echo json_encode($errorarray); exit;
		}

		if(trim($this->params['parentid']) != "" && $this->params['parentid'] != null)
		{
			$this->parentid  = $this->params['parentid']; //initialize datacity
		}else
		{
			//$errorarray['errormsg']='parentid missing';
			$this->printerror(9,'parentid missing');
			//echo json_encode($errorarray); exit;
		}

		

		if(trim($this->params['action']) != "" && $this->params['action'] != null)
		{
			$this->api_action  = $this->params['action']; //initialize datacity
		}else
		{
			//$errorarray['errormsg']='action missing';
			$this->printerror(8,'action missing');
		}

		
		if($this->api_action=='blockinventory')
		{
			if($this->params['liveversion']==null || $this->params['newversion']==null)
			{				
				//$errorarray['errormsg']='liveversion or newversion is missing';
				//$errorarray['errormsg']='liveversion or newversion is missing';
				$this->printerror(6,'liveversion or newversion is missing');
			}else
			{
				$this->liveversion 	= $this->params['liveversion'];
				$this->newversion	= $this->params['newversion'];
			}			
		}


		if(trim($this->params['username']) != "" && $this->params['usercode'] != null)
		{
			$this->username  = $this->params['username']; //initialize datacity
			$this->usercode  = $this->params['usercode']; //initialize datacity
			
		}else
		{
			//$errorarray['errormsg']='username or usercode is missing';
			$this->printerror(7,'username or usercode is missing');
			//echo json_encode($errorarray); exit;
		}
        if(trim($this->params['p_astate']) != "" && $this->params['p_astate'] != null)
		{
			$this->para_astate  = $this->params['p_astate'];
		}
		$this->setServers();
		$this->setmodule();

		$this->budgetMismatchfactorTBDCampid1=1;
		$this->budgetMismatchfactorTBDCampid2=1;
		$this->budgetMismatchfactorTBECampid1=1;
		$this->budgetMismatchfactorTBECampid2=1;
		$this->budgetMismatchfactorTBSCampid1=1;
		$this->budgetMismatchfactorTBSCampid2=1;		
		$this->budgetMismatchfactorTBSACampid1=1;
		$this->budgetMismatchfactorTBSACampid2=1;
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
		$this->dbConbudget  	= $db[$data_city]['db_budgeting']['master'];	

	}

	function setmodule()
	{
		switch($this->liveversion%10)
		{
			case 1:
			$this->liveversionmodule='cs';
			break;
			case 2:
			$this->liveversionmodule='tme';
			break;
			case 3:
			$this->liveversionmodule='me';
			break;			
		}

		switch($this->newversion%10)
		{
			case 1:
			$this->newversionmodule='cs';
			break;
			case 2:
			$this->newversionmodule='tme';
			break;
			case 3:
			$this->newversionmodule='me';
			break;			
		}

		
		
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
	function checkingInventoryLost()
	{
		$status=null;
		
		$InvMgmtparams = array(); // we are making inventory management parameters list and we will pass that parameter to invMgmtClass , we nee to  remove some parameter and remove some parameter
        $astate=7;
		
		//$p_astate = (int)$this->para_astate;		
		//if($p_astate>0) $astate = $this->para_astate;
		
		$paramsInvMgmt['set']['version']	= $this->params['liveversion'];

		$paramsInvMgmt['set']['astatus'] 	= '3';  // 1-blocking 2-booking(LIVE) 3-checking
		$paramsInvMgmt['set']['astate']		= $astate;  // 1-dealclose 2-balance readjustment 3-financial approval 4-expiry 5-release 6-part payment 7-ecs 10-category/pin deletion LIve 11-category/pin deletion Shadow ,17 dependednt
		// I have to pass the same astate - 7 which srini is passing so that we get the same error othwise if we pass two different astate then we will get differnt resposnse from inventory management api
		// here we will check whether it is inventory loss or not , if there is no inventory loss then we will not process through this service 
        $paramsInvMgmt['set']['ecs_flag']	= '1';
		
		$InvMgmtparams= $this->prepareParamsforInvMgmt($paramsInvMgmt);
		
		// we are not creating the object to see whether inventory is lost or not
		//$InvMgmtparams['trace']	= 0;
		$invmgmtclassobj = new invMgmtClass($InvMgmtparams);
		$result = $invmgmtclassobj->manageInventory();
		unset($invmgmtclassobj);
		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b> Inventory Management Api Result</b>'; print_r($result);	 // uncomment this 		 
		}
		
		$this->InventoryManagementApiResult_existing = $result;
		if(is_array($result['results']['fail']) && count($result['results']['fail'])>0)
		{
			$status='fail';	
		}else
		{
			$status='pass';	
		}
		$this->tbl_invloss_renewbudget_api_log('checkingInventoryLost - invMgmtClass - OldInventory Details',$paramsInvMgmt,$result);
		
		return $status;
	}

	

	function tbl_invloss_renewbudget_api_log($api_name,$api_parameter,$api_result)
	{
		
		if(is_array($api_result))
		{
			$api_resultstring= stripslashes(json_encode($api_result));
		}else
		{
			$api_resultstring=$api_result;
		}
		
		$sql = "insert into tbl_invloss_renewbudget_api_log set
					parentid		='".$this->parentid."',
					oldversion		='".$this->liveversion."',
					newversion 		='".$this->newversion."',
					api_name ='".$api_name."',
					api_parameter ='".json_encode($api_parameter)."',
					api_result ='".$api_resultstring."'";
					
		parent::execQuery($sql,$this->dbConbudget);
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
		$resultstr= json_encode($result);
		print($resultstr);
		die;
	}

	function Insertinto_tbl_bidding_details_summary()
	{
		$pincode_list_str  = null;
		$category_list_str = null;

		// first we will check data is presnt in live if we do not get data then we will fetch from IDC
		$gi_sql = "select companyname,pincode,latitude,longitude,landline,mobile from tbl_companymaster_generalinfo where parentid='".$this->parentid."'";		
		$gi_res= parent::execQuery($gi_sql,$this->dbConIro);
		if(mysql_num_rows($gi_res)==0)
		{
			$gi_res=null;
			$gi_res= parent::execQuery($gi_sql,$this->Idc);
		}
		if(mysql_num_rows($gi_res)==0)
		{
			$this->printerror(2,'No data in tbl_companymaster_generalinfo, live as well as IDC');
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

		$catidvaltemparr= array();
		$pincodetemparr = array();
		$pincodetemparr1= array();
		$pincodetemparr2= array();
		
		// reading all pass category pincode 
		foreach($this->InventoryManagementApiResult_existing['results']['pass'] as $catidval=>$valueArr)
		{
			array_push($catidvaltemparr,$catidval);
			$pintemparr= array_keys($valueArr);
			array_push($pincodetemparr, $pintemparr);			
		}

		// reading all fail category pincode 
		foreach($this->InventoryManagementApiResult_existing['results']['fail'] as $catidval=>$valueArr)
		{
			array_push($catidvaltemparr,$catidval);
			$pintemparr= array_keys($valueArr);
			array_push($pincodetemparr, $pintemparr);			
		}
		
		$catidvaltemparr= array_unique($catidvaltemparr);
		$category_list_str = implode(',',$catidvaltemparr);
		foreach($pincodetemparr as $indx=>$arr)
		{
			foreach($arr as $arrkey=>$arrval)
			{
				array_push($pincodetemparr1,$arrval);
			}			
		}

		$pincodetemparr2= array_unique($pincodetemparr1);

		$pincode_list_str  = implode(',',$pincodetemparr2);
		
		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			/*
			//echo '<br>catidvalarr--'; print_r($catidvalarr);
			//echo '<br>$pincodetemparr--'; print_r($pincodetemparr);
			//echo '<br>$pincodetemparr1--'; print_r($pincodetemparr1);
			//echo '<br>$pincodetemparr2--'; print_r($pincodetemparr2);
			*/
			
			echo '<br>category_list_str--'; print_r($category_list_str);	// uncomment		
			echo '<br>pincode_list_str--'; print_r($pincode_list_str);  	// uncomment
		}
		

		$this->bidding_details_summary_category_list = $category_list_str;
		$this->bidding_details_summary_pincode_list  = $pincode_list_str;

		
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
						module			='".$this->newversionmodule."',
						contact_details	='".$contact_details_str."',
						category_list	='".$category_list_str."',
						dealclosed_flag	=0,
						pincode_list	='".$pincode_list_str."',
						updatedon			='".date('Y-m-d H:i:s')."',
						username			='".addslashes(stripcslashes($this->username))."',
						updatedby			='".addslashes(stripcslashes($this->usercode))."' 
						ON DUPLICATE KEY UPDATE
						sphinx_id		='".$id_gen_arr['sphinx_id']."',						
						docid			='".$id_gen_arr['docid']."',
						companyname		='".addslashes(stripcslashes($gi_arr['companyname']))."',
						data_city		='".$id_gen_arr['data_city']."',
						pincode			='".$gi_arr['pincode']."',
						latitude		='".$gi_arr['latitude']."',
						longitude		='".$gi_arr['longitude']."',
						module			='".$this->newversionmodule."',
						contact_details	='".$contact_details_str."',
						category_list	='".$category_list_str."',
						dealclosed_flag	=0,
						pincode_list	='".$pincode_list_str."',
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
						
						//// uncomment
					}
	}



	function prepareParamsforBudgetCalculation($temparray=null)
	{		
		$BdgtCallparams['data_city']	= $_REQUEST['data_city'];		
		$BdgtCallparams['parentid']		= $_REQUEST['parentid'];		
		$BdgtCallparams['tenure']		= 12;
		$BdgtCallparams['mode']		    = 4; // initialize mode 1-best positon 2-fixed position 3-package 4-renewal 5-exclusive
		$BdgtCallparams['option']	    = 1; // default 1, max 7

		//$BdgtCallparams['version']		= $_REQUEST['ver'];

		if(count($temparray))
		{
			// first we will unset the keys then we will set 
			if(is_array($temparray['unset'])  && count($temparray['unset']))
			{
				foreach($temparray['unset'] as $keytounset)
				{
					unset($BdgtCallparams[$keytounset]);
				}
			}

			if(is_array($temparray['set'])  && count($temparray['set']))
			{
				foreach($temparray['set'] as $key=>$value)
				{
					$BdgtCallparams[$key]=$value;
				}
			}
		}

		return $BdgtCallparams;
		
	}

	function getbudgetFactorForMismatch()
	{	

		$catidcond = ' and catid in ('. $this->bidding_details_summary_category_list.') ';
		$pincond   = ' and pincode in ('. $this->bidding_details_summary_pincode_list .') ';
		
		
		$PaymentApportioningDetailsarr = $this->getPaymentApportioningDetails($this->liveversion);
		$campaignid_1_budg=$campaignid_2_budg=0;

		$campaignid_1_budg_biddingtable=$campaignid_2_budg_biddingtable=0;
		$biddingtable_name='';
		
		foreach($PaymentApportioningDetailsarr as $campaignid=>$val)
		{
			if($campaignid==1)
			{
				$campaignid_1_budg=$val['budget'];
			}

			if($campaignid==2)
			{
				$campaignid_2_budg=$val['budget'];
			}
		}

		$live_data = $this->payment_apportioning_budget_duration_arr[$this->liveversion];
		$live_duration = $live_data['duration'];

		$sql="SELECT  sum(bidperday)*".$live_duration." as campbudg,campaignid from tbl_bidding_details where parentid ='".$this->parentid."' AND version ='" . $this->liveversion . "' ".$catidcond.$pincond." group by campaignid";

		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b>tbl_bidding_details Query:</b>'.$sql.'<br>';
		}
		$res 	= parent::execQuery($sql, $this->fin);
		$num	= mysql_num_rows($res);
		if($num)
		{
			while($resultarr= mysql_fetch_assoc($res))
			{	
				if(floatval($resultarr['campaignid'])==1 && $campaignid_1_budg)
				{
					if(floatval($resultarr['campbudg'])==0)
					{
						$this->printerror(21,'bidperday is 0 for campaignid 1 in tbl_bidding_details ');
					}else
					{					
						$this->budgetMismatchfactorTBDCampid1= floatval($campaignid_1_budg)/floatval($resultarr['campbudg']);
						$campaignid_1_budg_biddingtable=floatval($resultarr['campbudg']);
						$biddingtable_name.=' tbl_bidding_details';
					}
				}

				if(floatval($resultarr['campaignid'])==2 && $campaignid_2_budg)
				{
					if(floatval($resultarr['campbudg'])==0)
					{
						$this->printerror(22,'bidperday is 0 for campaignid 2 in tbl_bidding_details');
					}else
					{					
						$this->budgetMismatchfactorTBDCampid2= floatval($campaignid_2_budg)/floatval($resultarr['campbudg']);
						$campaignid_2_budg_biddingtable=floatval($resultarr['campbudg']);
						$biddingtable_name.=' tbl_bidding_details';
					}
				}
			}			
		}
		
		$sql="SELECT  sum(bidperday)*".$live_duration." as campbudg,campaignid from tbl_bidding_details_expired where parentid ='".$this->parentid."' AND version ='" . $this->liveversion . "' ".$catidcond.$pincond." group by campaignid";
		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b>tbl_bidding_details_expired Query:</b>'.$sql.'<br>';
		}
		$res 	= parent::execQuery($sql, $this->fin);
		$num	= mysql_num_rows($res);
		if($num)
		{
			while($resultarr= mysql_fetch_assoc($res))
			{	
				if(floatval($resultarr['campaignid'])==1 && $campaignid_1_budg)
				{
					if(floatval($resultarr['campbudg'])==0)
					{
						$this->printerror(21,'bidperday is 0 for campaignid 1 in tbl_bidding_details_expired');
					}else
					{					
						$this->budgetMismatchfactorTBECampid1= floatval($campaignid_1_budg)/floatval($resultarr['campbudg']);
						$campaignid_1_budg_biddingtable=floatval($resultarr['campbudg']);
						$biddingtable_name.=' tbl_bidding_details_expired';
					}
				}

				if(floatval($resultarr['campaignid'])==2 && $campaignid_2_budg)
				{
					if(floatval($resultarr['campbudg'])==0)
					{
						$this->printerror(22,'bidperday is 0 for campaignid 2 in tbl_bidding_details_expired');
					}else
					{					
						$this->budgetMismatchfactorTBECampid2= floatval($campaignid_2_budg)/floatval($resultarr['campbudg']);
						$campaignid_2_budg_biddingtable=floatval($resultarr['campbudg']);
						$biddingtable_name.=' tbl_bidding_details_expired';
					}
				}
			}			
		}
		

		$sql="SELECT campaignid,sum(if(sys_budget<=0,1,sys_budget)) as campbudg,campaignid from tbl_bidding_details_shadow where parentid ='".$this->parentid."' AND version ='" . $this->liveversion . "' ".$inserted_on_cond.$catidcond.$pincond." group by campaignid"; 
		$res 	= parent::execQuery($sql, $this->dbConbudget);
		$num	= mysql_num_rows($res);

		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b>tbl_bidding_details_shadow  Query:</b>'.$sql.'<br>';
		}
		
		if($num)
		{
			while($resultarr= mysql_fetch_assoc($res))
			{	
				if(floatval($resultarr['campaignid'])==1 && $campaignid_1_budg)
				{
					if(floatval($resultarr['campbudg'])==0)
					{
						$this->printerror(19,'sys_budget is 0 for campaignid 1 in tbl_bidding_details_shadow');
					}else
					{					
						$this->budgetMismatchfactorTBSCampid1= floatval($campaignid_1_budg)/floatval($resultarr['campbudg']);
						$campaignid_1_budg_biddingtable=floatval($resultarr['campbudg']);
						$biddingtable_name.=' tbl_bidding_details_shadow';
					}
				}

				if(floatval($resultarr['campaignid'])==2 && $campaignid_2_budg)
				{
					if(floatval($resultarr['campbudg'])==0)
					{
						$this->printerror(20,'sys_budget is 0 for campaignid 2 in tbl_bidding_details_shadow');
					}else
					{					
						$this->budgetMismatchfactorTBSCampid2=floatval($campaignid_2_budg)/floatval($resultarr['campbudg']);
						$campaignid_2_budg_biddingtable=floatval($resultarr['campbudg']);
						$biddingtable_name.=' tbl_bidding_details_shadow';
					}
				}
			}
		}

		

		/*	
		$latestinsertedon_sql= "select max(inserted_on) as inserted_on from tbl_bidding_details_shadow_archive where parentid ='".$this->parentid."' AND version ='" . $this->liveversion . "'";
		$latestinsertedon_res 	= parent::execQuery($latestinsertedon_sql, $this->dbConbudget);
		$latestinsertedon_arr= mysql_fetch_assoc($latestinsertedon_res);
		$inserted_on_cond='';
		if($latestinsertedon_arr['inserted_on']!='' && $latestinsertedon_arr['inserted_on']!='0000-00-00 00:00:00')
		{
			$inserted_on_cond = "and inserted_on='".$latestinsertedon_arr['inserted_on']."'";
		}*/
		//$sql="SELECT campaignid,sum(sys_budget) as campbudg from tbl_bidding_details_shadow_archive where parentid ='".$this->parentid."' AND version ='" . $this->liveversion . "' ".$inserted_on_cond.$catidcond.$pincond." and sys_budget>0 group by campaignid"; 

		$sql="select campaignid,sum(if(sys_budget<=0,1,sys_budget)) as campbudg,campaignid from
				(
					select * from db_budgeting.tbl_bidding_details_shadow_archive where parentid ='".$this->parentid."' AND version ='" . $this->liveversion . "' GROUP BY catid, pincode  
					union
					select * from db_budgeting.tbl_bidding_details_shadow_archive_historical where parentid ='".$this->parentid."' AND version ='" . $this->liveversion . "' GROUP BY catid, pincode  
					
				)a group by campaignid";

		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b>tbl_bidding_details_shadow_archive  Query:</b>'.$sql.'<br>';
		}
				
		$res 	= parent::execQuery($sql, $this->dbConbudget);
		$num	= mysql_num_rows($res);
		if($num)
		{
			while($resultarr= mysql_fetch_assoc($res))
			{	
				if(floatval($resultarr['campaignid'])==1 && $campaignid_1_budg)
				{
					if(floatval($resultarr['campbudg'])==0)
					{
						$this->printerror(17,'sys_budget is 0 for campaignid 1 in tbl_bidding_details_shadow_archive');
					}else
					{					
						$this->budgetMismatchfactorTBSACampid1= floatval($campaignid_1_budg)/floatval($resultarr['campbudg']);
						$campaignid_1_budg_biddingtable=floatval($resultarr['campbudg']);
						$biddingtable_name.=' tbl_bidding_details_shadow_archive';
					}
				}

				if(floatval($resultarr['campaignid'])==2 && $campaignid_2_budg)
				{
					if(floatval($resultarr['campbudg'])==0)
					{
						$this->printerror(18,'sys_budget is 0 for campaignid 2 in tbl_bidding_details_shadow_archive');
					}else
					{					
						$this->budgetMismatchfactorTBSACampid2=floatval($campaignid_2_budg)/floatval($resultarr['campbudg']);
						$campaignid_2_budg_biddingtable=floatval($resultarr['campbudg']);
						$biddingtable_name.=' tbl_bidding_details_shadow_archive';
					}
				}
			}			
		}
		
		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			
			echo '<br><b>campaignid_1_budg</b>'.$campaignid_1_budg;
			echo '<br><b>campaignid_2_budg</b>'.$campaignid_2_budg;
			
			echo '<br><b>budgetMismatchfactorTBDCampid1 - </b>'.$this->budgetMismatchfactorTBDCampid1;
			echo '<br><b>budgetMismatchfactorTBDCampid2 - </b>'.$this->budgetMismatchfactorTBDCampid2;
			echo '<br><b>budgetMismatchfactorTBECampid1 - </b>'.$this->budgetMismatchfactorTBECampid1;
			echo '<br><b>budgetMismatchfactorTBECampid2 - </b>'.$this->budgetMismatchfactorTBECampid2;
			echo '<br><b>budgetMismatchfactorTBSCampid1 - </b>'.$this->budgetMismatchfactorTBSCampid1;
			echo '<br><b>budgetMismatchfactorTBSCampid2 - </b>'.$this->budgetMismatchfactorTBSCampid2;
			echo '<br><b>budgetMismatchfactorTBSACampid1 - </b>'.$this->budgetMismatchfactorTBSACampid1;
			echo '<br><b>budgetMismatchfactorTBSACampid2 - </b>'.$this->budgetMismatchfactorTBSACampid2;			
			echo '<br><b>PaymentApportioningDetailsarr:</b>'; print_r($PaymentApportioningDetailsarr);
		}




		
		$sql="SELECT  * from tbl_bidding_details where parentid ='".$this->parentid."' AND version ='" . $this->liveversion . "' limit 1 "; // if data is not present in bidding dettails then only we have to stop 
		$res 	= parent::execQuery($sql, $this->fin);
		$num	= mysql_num_rows($res);
		if($num==0)
		{
			if($campaignid_1_budg!=0 && $campaignid_1_budg_biddingtable==0)
			{
				$this->printerror(23,'Entry of Campaignid 1 is present in payment_apportioning but its entry is missing on '.$biddingtable_name);
			}

			if($campaignid_2_budg=0 && $campaignid_2_budg_biddingtable==0)
			{
				$this->printerror(24,'Entry of Campaignid 2 is present in payment_apportioning but its entry is missing on '.$biddingtable_name);
			}
		}
	}

	// this function will check whether a category and pincode has multiple position then we will not process it 
	function checkMultiplepositionForCatPin($con,$tablename,$andcon='') 
	{		
			$multiplepositionforcategorypincode="select catid,pincode,count(distinct(position_flag)) as cnt from ".$tablename." where parentid ='".$this->parentid."' AND version ='" . $this->liveversion . "' ".$andcon." group by catid,pincode	having cnt>1";
			$res 	= parent::execQuery($multiplepositionforcategorypincode,$con);
			$num	= mysql_num_rows($res);
			if($num)
			{
				$msg='';
				while($result=mysql_fetch_assoc($res))
				{
					//$msg.=' # Catid-'.$result['catid'].'- pincode- '.$result['pincode'];
					if($tablename=='tbl_bidding_details_expired')
					{
						$selcatpindata=" Select parentid,docid,version,campaignid,catid,national_catid,pincode,position_flag,inventory,bidvalue,callcount,duration,sys_budget,actual_budget,bidperday,lcf,hcf,data_city,physical_pincode,latitude,longitude,updatedby,updatedon,backenduptdate,expiredon,remarks from tbl_bidding_details_expired where parentid ='".$this->parentid."' AND version ='" . $this->liveversion . "' AND catid=".$result['catid']." AND pincode=".$result['pincode']." and position_flag=100 ";
						//echo "<br>".$selcatpindata;
						$selcatpindatares = parent::execQuery($selcatpindata,$con);

						if(mysql_num_rows($selcatpindatares))
						{
							$selcatpindataarr=mysql_fetch_assoc($selcatpindatares);
							$insertsql= "Insert into tbl_bidd_det_exp_delete set
											parentid= '".$selcatpindataarr['parentid']."',
											docid= '".$selcatpindataarr['docid']."',
											version= '".$selcatpindataarr['version']."',
											campaignid= '".$selcatpindataarr['campaignid']."',
											catid= '".$selcatpindataarr['catid']."',
											national_catid= '".$selcatpindataarr['national_catid']."',
											pincode= '".$selcatpindataarr['pincode']."',
											position_flag= '".$selcatpindataarr['position_flag']."',
											inventory= '".$selcatpindataarr['inventory']."',
											bidvalue= '".$selcatpindataarr['bidvalue']."',
											callcount= '".$selcatpindataarr['callcount']."',
											duration= '".$selcatpindataarr['duration']."',
											sys_budget= '".$selcatpindataarr['sys_budget']."',
											actual_budget= '".$selcatpindataarr['actual_budget']."',
											bidperday= '".$selcatpindataarr['bidperday']."',
											lcf= '".$selcatpindataarr['lcf']."',
											hcf= '".$selcatpindataarr['hcf']."',
											data_city= '".$selcatpindataarr['data_city']."',
											physical_pincode= '".$selcatpindataarr['physical_pincode']."',
											latitude= '".$selcatpindataarr['latitude']."',
											longitude= '".$selcatpindataarr['longitude']."',
											updatedby= '".$selcatpindataarr['updatedby']."',
											updatedon= '".$selcatpindataarr['updatedon']."',
											backenduptdate= '".$selcatpindataarr['backenduptdate']."',
											expiredon= '".$selcatpindataarr['expiredon']."',
											remarks= '".$selcatpindataarr['remarks']." @ invlosss process'";
								//echo '<br>'.$insertsql;
							parent::execQuery($insertsql,$con);

							$deletepindata=" delete from tbl_bidding_details_expired where parentid ='".$this->parentid."' AND version ='" . $this->liveversion . "' AND catid=".$result['catid']." AND pincode=".$result['pincode']." and position_flag=100";

								//echo '<br>'.$deletepindata;
							parent::execQuery($deletepindata,$con);

							
						}
					}
				}
				
			}
			// again we are checking the same and if we are getting again same then we need to throw error
			$multiplepositionforcategorypincode="select catid,pincode,count(distinct(position_flag)) as cnt from ".$tablename." where parentid ='".$this->parentid."' AND version ='" . $this->liveversion . "' ".$andcon." group by catid,pincode	having cnt>1";
			$res 	= parent::execQuery($multiplepositionforcategorypincode,$con);
			$num	= mysql_num_rows($res);
			if($num)
			{
				$msg='';
				while($result=mysql_fetch_assoc($res))
				{
					$msg.=' # Catid-'.$result['catid'].'- pincode- '.$result['pincode'];
				}
				$this->printerror(16,'Same Category pincode has multiple position Table-'.$tablename.' Cat Pin Details '.$msg );
			}
	}

	function getOldBiddingdetails()
	{
		
		$catidcond = ' and catid in ('. $this->bidding_details_summary_category_list.') ';
		$pincond   = ' and pincode in ('. $this->bidding_details_summary_pincode_list .') ';
		$this->getbudgetFactorForMismatch();
		$live_data = $this->payment_apportioning_budget_duration_arr[$this->liveversion];
		$live_duration = $live_data['duration'];

		$sql="SELECT catid,callcount as cnt_f,pincode as pin,position_flag as pos,bidvalue, bidperday*if(position_flag=100,".$this->budgetMismatchfactorTBDCampid1.",".$this->budgetMismatchfactorTBDCampid2.")*".$live_duration." as budget,inventory from tbl_bidding_details where parentid ='".$this->parentid."' AND version ='" . $this->liveversion . "' ".$catidcond.$pincond." ORDER BY catid, pincode";
		$res 	= parent::execQuery($sql, $this->fin);
		$num	= mysql_num_rows($res);
		
		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b>DB  Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res;
			echo '<br><b>Num Rows:</b>'.$num;			
		}	
		
		if($num==0)
		{
			//if data is not present in bidding_details then do the additional lookup on expire also 
			
			$sql="SELECT catid,callcount as cnt_f,pincode as pin,position_flag as pos,bidvalue, bidperday*if(position_flag=100,".$this->budgetMismatchfactorTBECampid1.",".$this->budgetMismatchfactorTBECampid2.")*".$live_duration." as budget,inventory from tbl_bidding_details_expired where parentid ='".$this->parentid."' AND version ='" . $this->liveversion . "' ".$catidcond.$pincond." ORDER BY catid, pincode";
					$res 	= parent::execQuery($sql, $this->fin);

			$num	= mysql_num_rows($res);

			if(defined('TRACE_MODE') && TRACE_MODE!=0)
			{
				echo '<br><b>DB  Query:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res;
				echo '<br><b>Num Rows:</b>'.$num;			
			}
			
			$this->checkMultiplepositionForCatPin($this->fin,'tbl_bidding_details_expired','');
			 
			if ($num==0) {
			
				$sql="SELECT catid,callcount as cnt_f,pincode as pin,position_flag as pos,bidvalue, if(sys_budget<=0,1,sys_budget) * if(position_flag=100,".$this->budgetMismatchfactorTBSCampid1.",".$this->budgetMismatchfactorTBSCampid2.") AS budget,inventory from tbl_bidding_details_shadow where parentid ='".$this->parentid."' AND version ='" . $this->liveversion . "' ".$catidcond.$pincond." ORDER BY catid, pincode";
				$res 	= parent::execQuery($sql, $this->dbConbudget);
				$num	= mysql_num_rows($res);
				
				if(defined('TRACE_MODE') && TRACE_MODE!=0)
				{
					echo '<br><b>DB  Query:</b>'.$sql;
					echo '<br><b>Result Set:</b>'.$res;
					echo '<br><b>Num Rows:</b>'.$num;			
				}
				

				//$this->checkMultiplepositionForCatPin($this->dbConbudget,'tbl_bidding_details_shadow','');
				
				if ($num==0) {
					/*	
					$latestinsertedon_sql= "select max(inserted_on) as inserted_on from tbl_bidding_details_shadow_archive where parentid ='".$this->parentid."' AND version ='" . $this->liveversion . "'";
					$latestinsertedon_res 	= parent::execQuery($latestinsertedon_sql, $this->dbConbudget);
					$latestinsertedon_arr= mysql_fetch_assoc($latestinsertedon_res);
					$inserted_on_cond='';
					if($latestinsertedon_arr['inserted_on']!='' && $latestinsertedon_arr['inserted_on']!='0000-00-00 00:00:00')
					{
						$inserted_on_cond = "and inserted_on='".$latestinsertedon_arr['inserted_on']."'";
					}					
					*/					
										
					$sql="SELECT catid,callcount as cnt_f,pincode as pin,position_flag as pos,bidvalue, if(sys_budget<=0,1,sys_budget)*if(position_flag=100,".$this->budgetMismatchfactorTBSACampid1.",".$this->budgetMismatchfactorTBSACampid2.") AS budget,inventory from (

					select * from db_budgeting.tbl_bidding_details_shadow_archive where parentid ='".$this->parentid."' AND version ='" . $this->liveversion . "' GROUP BY catid, pincode 
					union
					select * from db_budgeting.tbl_bidding_details_shadow_archive_historical where parentid ='".$this->parentid."' AND version ='" . $this->liveversion . "' GROUP BY catid, pincode  
					
					)a
					group by catid,pincode ORDER BY catid, pincode"; // added group by condition since there may be multiple entry of same version 
					$res 	= parent::execQuery($sql, $this->dbConbudget);					
					
					$num	= mysql_num_rows($res);
					
					if(defined('TRACE_MODE') && TRACE_MODE!=0)
					{
						echo '<br><b>DB  Query:</b>'.$sql;
						echo '<br><b>Result Set:</b>'.$res;
						echo '<br><b>Num Rows:</b>'.$num;			
					}
					
					//$this->checkMultiplepositionForCatPin($this->dbConbudget,'tbl_bidding_details_shadow_archive',$inserted_on_cond,' group by parentid,version,campaignid,catid,pincode ');
				}
			}

		}		

		
		
		if($res && $num > 0)
		{
			while($row=mysql_fetch_assoc($res))
			{
				$catid 	 = $row['catid']; 
				$pincode = $row['pin']; 
				$positionval = $row['pos'];
				//{"400001":{"cnt_f":0.006,"pin":400001,"pos":3,"bidvalue":513.17432543,"budget":6.4386,"inv":1}								

				$inv_old[$catid][$pincode]['cnt_f']   	= $row['cnt_f'];
				$inv_old[$catid][$pincode]['pin']   	= $pincode;
				$inv_old[$catid][$pincode]['pos']   	= $row['pos'];
				$inv_old[$catid][$pincode]['bidvalue']  = $row['bidvalue'];
				$inv_old[$catid][$pincode]['budget'] 	= $row['budget'];
				$inv_old[$catid][$pincode]['inv'] 	 	= $row['inventory'];
				$inv_old[$catid][$pincode]['avlbl'] 	='un';
				//$budget[$catid] += $positionarr['budget'];
				//$cat_pincode_budget_sum+= $positionarr['budget'];
			}

			// we will check whethere this position is still available or not if it is available then we will not change it , if it is not available then only we will change it
			$existing_invenory_availablestatus =  $this->InventoryManagementApiResult_existing;

			// iterating pass value
			foreach($existing_invenory_availablestatus['results']['pass'] as $catidval=>$valueArr)
			{				
				foreach($valueArr as $pincode=>$pincodearr)
				{
					foreach($pincodearr as $positionkey=>$value)
					{
						$inv_old[$catidval][$pincode]['avlbl']='Y';
					}
				}				
			}
			// iterating fail value
			foreach($existing_invenory_availablestatus['results']['fail'] as $catidval=>$valueArr)
			{				
				foreach($valueArr as $pincode=>$pincodearr)
				{
					foreach($pincodearr as $positionkey=>$value)
					{
						$inv_old[$catidval][$pincode]['avlbl']='N';
					}
				}				
			}

			// now we will check if is there any element which have still availability or avlbl as un the stop
			if( in_array('un',$inv_old))
			{
				$this->printerror(15,'There are some old inventory catgory pincode which old postion was not known');
			}

			
		}else
		{
			$this->printerror(14,'Data not present in any of the bidding tables');
		}

		return $inv_old;
	}

	function getnewBiddingdetails($budgetDetailsAPIresult)
	{// This is to traverse the renew api and see the 

		foreach ($budgetDetailsAPIresult['result']['c_data'] as $catid =>$catidvalarr)
		{

			$this->national_catid_array[$catid]=$catidvalarr['ncid'];
			
			foreach ($catidvalarr['pin_data'] as $pincode =>$pincodevalarr)
			{				
				$renewal_pos = $pincodevalarr['best_flg'];
				//echo "<br>pincodevalarr renewal_pos=". $renewal_pos; print_r($pincodevalarr);

				$inv_new[$catid][$pincode]['cnt_f']   = $pincodevalarr['cnt_f'];
				$inv_new[$catid][$pincode]['pin']   = $pincode;
				$inv_new[$catid][$pincode]['pos']   = $renewal_pos;
				$inv_new[$catid][$pincode]['bidvalue']   = $pincodevalarr['pos'][$renewal_pos]['bidvalue'];
				$inv_new[$catid][$pincode]['budget'] = $pincodevalarr['pos'][$renewal_pos]['budget'];
				$inv_new[$catid][$pincode]['inv'] 	 = $pincodevalarr['pos'][$renewal_pos]['inv_avail'];
			}
		}
		return $inv_new;
	}

	function update_tbl_bidding_details_intermediate($inv_old,$inv_new)
	{
		$fp_budget=0;
		$package_budget=0;
		$this->finalinv_array = $inv_old;		

		$delsql= " Delete from tbl_bidding_details_intermediate where parentid ='".$this->parentid."' and version ='".$this->newversion."'";
		parent::execQuery($delsql, $this->dbConbudget);
		
		foreach($inv_old as $catid=>$catidarr)
		{
			$cat_budget = 0;
			foreach ($catidarr as $pincode=>$pincodearr)
			{
				 // changing all the position where inventory was lost 
				if($inv_old[$catid][$pincode]['avlbl']=='N') 				
				{
					$inv_old[$catid][$pincode]['pos']=$inv_new[$catid][$pincode]['pos'];
					$this->finalinv_array[$catid][$pincode]['pos']=$inv_new[$catid][$pincode]['pos']; // for loging purpose
				}
				unset($inv_old[$catid][$pincode]['avlbl']); // now unset because we dont have to dump it into intermediate table
				
				$cat_budget+= $inv_old[$catid][$pincode]['budget'];

				if($inv_old[$catid][$pincode]['pos']==100)
				{
					$package_budget+=$inv_old[$catid][$pincode]['budget'];
					
				}else
				{
					$fp_budget+=$inv_old[$catid][$pincode]['budget'];
				}
			}
			
			$sql = "insert into tbl_bidding_details_intermediate set
					parentid		='".$this->parentid."',
					catid			='".$catid."',
					national_catid	='".$this->national_catid_array[$catid]."',
					cat_budget		='".$cat_budget."',
					version			='".$this->newversion."',
					pincode_list 	='".json_encode($inv_old[$catid])."',							
					updatedby		='".$this->usercode."',
					updatedon=now()
					ON DUPLICATE KEY UPDATE							
					catid			='".$catid."',
					national_catid	='".$this->national_catid_array[$catid]."',
					cat_budget		='".$cat_budget."',
					pincode_list 	='".json_encode($inv_old[$catid])."',							
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
			asort($inv_old);
			asort($inv_new);
			echo '<br>inv_old'; print_r($inv_old);
			echo '<br>inv_new'; print_r($inv_new);
		}
		
		$this->returnbudgetarr['fp_budget']=$fp_budget;
		$this->returnbudgetarr['package_budget']=$package_budget;
		
		return $this->returnbudgetarr;
	}



	function catBudgetCheckwithPaymentApportioning($inv_old,$inv_new)
	{
		$fp_budget=0;
		$package_budget=0;		
		$PaymentApportioningDetailsarr = $this->getPaymentApportioningDetails($this->liveversion);

		$PaymentApportioningbudget=0;
		foreach($PaymentApportioningDetailsarr as $campaignid=>$val)
		{			
			$PaymentApportioningbudget+=$val['budget'];
		}
		
		foreach($inv_old as $catid=>$catidarr)
		{
			$cat_budget = 0;
			foreach ($catidarr as $pincode=>$pincodearr)
			{
				if($inv_old[$catid][$pincode]['avlbl']=='N') 				
				{
					$inv_old[$catid][$pincode]['pos']=$inv_new[$catid][$pincode]['pos'];					
				}
				

				if($inv_old[$catid][$pincode]['pos']==100)
				{
					$package_budget+=$inv_old[$catid][$pincode]['budget'];
					
				}else
				{
					$fp_budget+=$inv_old[$catid][$pincode]['budget'];
				}
				if(defined('TRACE_MODE') && TRACE_MODE!=0)
				{
					//echo '<br>catid--'.$catid.'-pincode-'.$pincode.'--pos--'.$inv_old[$catid][$pincode]['pos'].'--fp_budget '.$fp_budget.'---package_budget '.$package_budget;
			
				}
			}
		}

		$PaymentApportioningbudget = floatval(round($PaymentApportioningbudget));
		$fp_and_package_budget =floatval(round($package_budget)+round($fp_budget));

		$buddgetdiffernce=abs($PaymentApportioningbudget-$fp_and_package_budget);

		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b>PaymentApportioningDetailsarr</b> <br>'; print_r($PaymentApportioningDetailsarr); // uncomment
			echo '<br><b>PaymentApportioningbudget</b> '; print($PaymentApportioningbudget); // uncomment
			echo '<br><b>fp_budget</b> '; print($fp_budget); // uncomment
			echo '<br><b>package_budget</b> '; print($package_budget); // uncomment
			echo '<br><b>fp_and_package_budget</b> '; print($fp_and_package_budget);
			echo '<br><b>round PaymentApportioningbudget'; print(round($PaymentApportioningbudget));
			echo '<br><b>$buddgetdiffernce'; print(round($buddgetdiffernce));
			echo '<br><b>floatval($buddgetdiffernce)'.floatval($buddgetdiffernce);
		}

		
		
		if(floatval($buddgetdiffernce)>10) // upto 10Rs differnce is ok  
		{
			
			$this->printerror(11,'PaymentApportioning budget and Renew category Pincode budget is not same');
		}	
			
		
	}
	
	function tbl_invloss_renewbudget_log($inv_old,$inv_new)
	{
		$sql = "insert into tbl_invloss_renewbudget_log set
					parentid		='".$this->parentid."',
					oldversion		='".$this->liveversion."',
					newversion 		='".$this->newversion."',
					inv_old_details ='".json_encode($inv_old)."',							
					inv_new_details ='".json_encode($inv_new)."',							
					inv_final_details ='".json_encode($this->finalinv_array)."',							
					username		='".addslashes($this->username)."',
					usercode		='".$this->usercode."'";
		parent::execQuery($sql,$this->dbConbudget);
	}
	
	
	function callBudgetCalculationRenewAPI()
	{// This function is going to call actual budget calculculation api and based on the result
		$BdgtCallparams= array();
		$BdgtCallparams['set']['version']	 = $this->params['newversion'];
        $BdgtCallparams['set']['oldversion'] = $this->params['liveversion'];
		$BdgtCallparams = $this->prepareParamsforBudgetCalculation($BdgtCallparams);
		
		// Budget calculation object creation and calling
		$budgetDetailsClass_obj = new budgetDetailsClass($BdgtCallparams);
		$budgetDetailsAPIresult = $budgetDetailsClass_obj->getBudget();

		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b>budgetDetailsAPIresult</b> <br>'; print_r($budgetDetailsAPIresult); // uncomment
		}

		$this->tbl_invloss_renewbudget_api_log('budgetDetailsAPI ',$BdgtCallparams,$budgetDetailsAPIresult);
			
		$inv_new = $this->getnewBiddingdetails($budgetDetailsAPIresult);
		$inv_old =$this->getOldBiddingdetails();

		$this->tbl_invloss_renewbudget_api_log('getOldBiddingdetails ',' old inventory',$inv_old);
		$this->tbl_invloss_renewbudget_api_log('getnewBiddingdetails ',' new inventory',$inv_new);		
		
		//$inv_new = array();		//unset($inv_old);		
		if( (!is_array($inv_new) || count($inv_new)==0) || (!is_array($inv_old) || count($inv_old)==0))
		{
			$this->printerror(12,'Either old or new inventory is missing');
		}

		$this->categoryPincodeMatching($inv_old,$inv_new); // it will check the category and pincode must be present on both
		// updating into tbl_bidding_details_intermediate
		$this->catBudgetCheckwithPaymentApportioning($inv_old,$inv_new); // we will check the budget of category and previous version 
		
		$budget = $this->update_tbl_bidding_details_intermediate($inv_old,$inv_new);
		$this->tbl_invloss_renewbudget_log($inv_old,$inv_new); // loging the data 

		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b>budget</b> <br>'; print_r($budget); // uncomment
		}				
	}

	function categoryPincodeMatching($inv_old,$inv_new)
	{
		$inv_old_catlist=array();
		$inv_old_pinlist=array();

		$inv_new_catlist=array();
		$inv_new_pinlist=array();

		foreach($inv_old as $catid=>$catidarr)
		{			
			foreach ($catidarr as $pincode=>$pincodearr)
			{
				array_push($inv_old_pinlist,$pincode);
			}
			array_push($inv_old_catlist,$catid);
		}

		foreach($inv_new as $catid=>$catidarr)
		{			
			foreach ($catidarr as $pincode=>$pincodearr)
			{
				array_push($inv_new_pinlist,$pincode);
			}
			array_push($inv_new_catlist,$catid);
		}

		$inv_new_catlist = array_unique($inv_new_catlist);
		$inv_old_catlist = array_unique($inv_old_catlist);

		$inv_old_pinlist = array_unique($inv_old_pinlist);
		$inv_new_pinlist = array_unique($inv_new_pinlist);

		
		$catlist_diff= array_diff($inv_old_catlist,$inv_new_catlist);
		$pinlist_diff= array_diff($inv_old_pinlist,$inv_new_pinlist);

		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b>$inv_old</b> <br>'; print_r($inv_old); 
			echo '<br><b>$inv_new</b> <br>'; print_r($inv_new); 
			echo '<br><b>inv_old_pinlist</b> <br>'; print_r($inv_old_pinlist); 
			echo '<br><b>inv_old_catlist</b> <br>'; print_r($inv_old_catlist); 
			echo '<br><b>inv_new_pinlist</b> <br>'; print_r($inv_new_pinlist); 
			echo '<br><b>inv_new_catlist</b> <br>'; print_r($inv_new_catlist); 
			echo '<br><b>catlist_diff</b> <br>'; print_r($catlist_diff); 
			echo '<br><b>pinlist_diff</b> <br>'; print_r($pinlist_diff); 
		}

		if(count($pinlist_diff))
		{
			
			// we are in this block so there is some extra pincode in old array so need to check whether those pincode are active or not if any of that pincode is active then we will stop user otherewise we will allow 
			
			$mismatchpincodelist = implode(',',$pinlist_diff);
			
			$active_pincode_sql="select group_concat(distinct pincode) as pincodefound from d_jds.tbl_areamaster_consolidated_v3 where pincode in (".$mismatchpincodelist.") and type_flag=1 AND display_flag=1 AND broader_area_flag=0 AND de_display=1 ";
			$active_pincode_res = parent::execQuery($active_pincode_sql,$this->dbConDjds);			
			
			$active_pincode_array = mysql_fetch_assoc($active_pincode_res);
			
			if(defined('TRACE_MODE') && TRACE_MODE!=0)
			{
				echo '<br><b>active_pincode_sql</b> <br>'.$active_pincode_sql;
				echo '<br><b>pincodefound</b> <br>'.$active_pincode_array['pincodefound'];
			}
			
			if($active_pincode_array['pincodefound']!='')
			{
				$stringtolog = " inv_old_pinlist:-".implode(',',$inv_old_pinlist)." -- inv_new_pinlist:-".implode(',',$inv_new_pinlist);
				$this->tbl_invloss_renewbudget_api_log('Old Pincode is not matched with new pincode ','',$stringtolog);
				$this->printerror('13P',' Old Pincode is not matched with new pincode ');
			}
			else
			{
				$stringtolog = " inv_old_pinlist:-".implode(',',$inv_old_pinlist)." -- inv_new_pinlist:-".implode(',',$inv_new_pinlist);
				$stringtolog .=" \n\r non active pincodes (".$mismatchpincodelist.")  in old tables so allowed to poceed ";
				$this->tbl_invloss_renewbudget_api_log('non active pincodes in old tables so allowed to poceed','',$stringtolog);
				
				
			}
			
			
		}
		
		if(count($catlist_diff))
		{
			$stringtolog = "inv_old_catlist-".implode(',',$inv_old_catlist)."inv_new_catlist-".implode(',',$inv_new_catlist);
			$this->tbl_invloss_renewbudget_api_log(' Old Category not matched with new category ','',$stringtolog);
			$this->printerror('13C',' Old Category not matched with new category ');
			
		}
		

	}
	
	function get_payment_apportioning_budget_duration_data($version)
	{
		
		$payment_apportioning_sql = "select max(duration) as duration,sum(budget) as budget, version from payment_apportioning where parentid='".$this->parentid."' and version ='".$version."' and campaignid in (1,2)";
		

		$payment_apportioning_res = parent::execQuery($payment_apportioning_sql, $this->fin);
		$payment_apportioning_arr= mysql_fetch_assoc($payment_apportioning_res);

		$returnarr= array();
		$returnarr[$payment_apportioning_arr['version']]['duration'] =$payment_apportioning_arr['duration'];
		$returnarr[$payment_apportioning_arr['version']]['budget']	= $payment_apportioning_arr['budget'];
		
		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b>payment_apportioning_res</b> <br>'; print_r($payment_apportioning_res); // uncomment
			//print_r($returnarr);
		}
		
		return $returnarr;
	}

	function getPaymentApportioningDetails($version)
	{
		$payment_apportioning_arr= array();
		$returnarr= array();
		
		$payment_apportioning_sql = "select * from payment_apportioning where parentid='".$this->parentid."' and version ='".$version."' and campaignid in (1,2)";		

		$payment_apportioning_res = parent::execQuery($payment_apportioning_sql, $this->fin);

		while($payment_apportioning_arr= mysql_fetch_assoc($payment_apportioning_res))
		{
			$returnarr[$payment_apportioning_arr['campaignId']]=$payment_apportioning_arr;
		}

		
		
		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b>payment_apportioning_arr</b> <br>'; print_r($payment_apportioning_arr); // uncomment
			//print_r($returnarr);
		}
		
		return $returnarr;
	}

	function set_payment_apportioning_budget_duration_data($temparr,$stoponerror=0)
	{
		$this->payment_apportioning_budget_duration_arr = $temparr;
		//print_r($temparr);
		if($stoponerror==1)
		{
			foreach ($this->payment_apportioning_budget_duration_arr as $version=> $versionvalarr)
			{
				if($versionvalarr['duration']==0 || $versionvalarr['budget']==0)
				{
					$this->printerror(4,'Budget or duration is missing on payment_apportioning for version '.$version); // need to uncomment 			
				}
			}
		}
	}
	
	function callupdateActualBudget()
	{// This will update actual budget for the contract
		$actual_fp_budget =$this->returnbudgetarr['fp_budget'];
		$actual_package_budget= $this->returnbudgetarr['package_budget'];
		$actual_total_budget	= $actual_fp_budget + $actual_package_budget;
		$duration= $this->payment_apportioning_budget_duration_arr[$this->liveversion]['duration'];
		
		$sqlUpdtActualBudget = "INSERT INTO tbl_bidding_details_summary SET 
								parentid 				= '".$this->parentid."',
								version 				= '".$this->newversion."',
								sys_fp_budget			= '".$actual_fp_budget."',				
								sys_package_budget		= '".$actual_package_budget."',								
								sys_total_budget		= '".$actual_total_budget."',
								actual_fp_budget		= '".$actual_fp_budget."',				
								actual_package_budget	= '".$actual_package_budget."',								
								actual_total_budget		= '".$actual_total_budget."',
								duration				= '".$duration."',
								updatedon				= '".date('Y-m-d H:i:s')."',
								updatedby				= '".addslashes(stripcslashes($this->usercode))."'
								ON DUPLICATE KEY UPDATE
								sys_fp_budget			= '".$actual_fp_budget."',				
								sys_package_budget		= '".$actual_package_budget."',								
								sys_total_budget		= '".$actual_total_budget."',
								actual_fp_budget		= '".$actual_fp_budget."',				
								actual_package_budget	= '".$actual_package_budget."',
								actual_total_budget		= '".$actual_total_budget."',
								duration				= '".$duration."',
								updatedon				= '".date('Y-m-d H:i:s')."',
								updatedby				= '".addslashes(stripcslashes($this->usercode))."'";
		$resUpdtActualBudget = parent::execQuery($sqlUpdtActualBudget, $this->dbConbudget);
		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b>sqlUpdtActualBudget</b> <br>'; print_r($sqlUpdtActualBudget); // uncomment
		}

		
	}

	
	function renewbudgetsimulation()
	{ 
		/* This function will give the simulation of renew budget
		 * To call the renew budget we need to pass the category and pincode to the service 
		*/

		$this->Insertinto_tbl_bidding_details_summary();
		$this->callBudgetCalculationRenewAPI();
		$this->callupdateActualBudget();

		
	}

	function delaclosesimulation()
	{		
		$status=null;
		
		$InvMgmtparams = array(); // we are making inventory management parameters list and we will pass that parameter to invMgmtClass , we nee to  remove some parameter and remove some parameter
		
		$paramsInvMgmt['set']['version']	= $this->params['newversion'];

		$paramsInvMgmt['set']['astatus'] 	= '1';  // 1-blocking 2-booking(LIVE) 3-checking
		$paramsInvMgmt['set']['astate']		= '1';  // 1-dealclose 2-balance readjustment 3-financial approval 4-expiry 5-release 6-part payment 7-ecs 10-category/pin deletion LIve 11-category/pin deletion Shadow ,17 dependednt
		// I have to pass the same astate - 7 which srini is passing so that we get the same error othwise if we pass two different astate then we will get differnt resposnse from inventory management api
		// here we will check whether it is inventory loss or not , if there is no inventory loss then we will not process through this service 

		$InvMgmtparams= $this->prepareParamsforInvMgmt($paramsInvMgmt);
		
		// we are not creating the object to see whether inventory is lost or not
		//$InvMgmtparams['trace']	= 0;
		
		$invmgmtclassobj = new invMgmtClass($InvMgmtparams);
		$result = $invmgmtclassobj->manageInventory();
		if(is_array($result['results']['fail']) && count($result['results']['fail'])>0)
		{
			$status='fail';
			$this->printerror(5,'Fail in deal close inventory api',$result); // need to uncomment
		}else
		{
			$status='pass';	
		}

		if(defined('TRACE_MODE') && TRACE_MODE!=0)
		{
			echo '<br><b>paramsInvMgmt</b> <br>'; print_r($paramsInvMgmt); // uncomment
			echo '<br><b>result</b> <br>'; print_r($result); // uncomment
		}

		return $status;
		
		unset($invmgmtclassobj);
	}
	 
	function blockinventory()
	{

		/* This process has to suppose to give the same behaviour as of renewal and deal close so we will do the follwoing
		 * step 1 :- first check whether its inventory is lost or not by cheking invntory status if there is no loss of inventory then it will return from thereself 
		 * step 2 :- call the renew budget details and get the details
		 * step 3 :- keep the bidding details same and process the complete json again
		 * step 4 :- call the budget submit to populate into intermediate table
		 * step 5 :- call update actual budget api of inventory 
		 * step 6 :- call deal close api of inventory
		 * 		 
		*/
				
		$invsts= $this->checkingInventoryLost();
				
		if($invsts=='pass')
		{
			$this->printerror(55,'No Inventory Loss so can not use this api'); // need to uncomment 			
		}

		$payment_apportioning_old_version_data = $this->get_payment_apportioning_budget_duration_data($this->liveversion);
		 
		
		$this->set_payment_apportioning_budget_duration_data($payment_apportioning_old_version_data,1);
				
		// step 2 we have to call the renew budget details api so that we will get the serv
		
		$this->renewbudgetsimulation();

		$this->delaclosesimulation();
		
		$retultarr['error']['code'] = 0;
		$retultarr['error']['msg'] = 'successful';
		$retultarr['data']= $this->returnbudgetarr;
		$retultarr['data']['duration']= $this->payment_apportioning_budget_duration_arr[$this->liveversion]['duration'];

		$this->tbl_invloss_renewbudget_api_log('FinalResult - ',$this->params,$retultarr);
		
		return $retultarr;

		/*
		$payment_apportioning_sql	= "select * from payment_apportioning where parentid='".$pis_res['parentid']."' and version ='".$pis_res['version']."' AND budget!=balance";		
		$pa_res 	= parent::execQuery($payment_apportioning_sql, $this->fin_master);

		if(DEBUG_MODE)
		{
			echo '<br><b>payment_apportioning sql</b>'.$payment_apportioning_sql;
			echo '<br><b>Result Set:</b>'.$pa_res;
			echo '<br><b>Num Rows:</b>'.mysql_num_rows($pa_res);
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		*/
		
		//$this->printerror(1,'Testing');
	}

	
	
	
	
}



?>
