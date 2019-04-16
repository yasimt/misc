<?php

class campaignadditionclass extends DB
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
	var  $docid			= null;	
	var  $sys_regfee_budget	= null;
	var  $defaultcatsponbudget=4;
	var  $defaultcompbannerbudget=4;

	var  $catsearch	= null;
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	
	
	
	var	 $optvalset = array('ALL','ZONE','NAME','PIN','DIST');
	

	function __construct($params)
	{	
		$this->isAuthorise();	
		$this->params = $params;		
		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='data_city missing';
			echo json_encode($errorarray); exit;
		}
		$this->categoryClass_obj = new categoryClass();
		$this->setServers();		
		
	}
	
	
	function gettbl_id_generator($parentid)
	{
		
		$sql = "select * from tbl_id_generator where parentid='".$parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIro);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
		
		if(mysql_num_rows($result))
		{
			$resarry= mysql_fetch_assoc($result);			
		}
		return $resarry;
	}
	function isAuthorise()
	{
		if($_SERVER['REMOTE_ADDR']!='172.29.87.117')
		{
			$unauterr= array_merge($_SERVER,$_SESSION);			
			$unauterrstr = "<pre><br>" . print_r($unauterr, true) . "<br></pre>";			
			mail('prameshjha@justdial.com','Unauthorise access of jdbox/proc.php',$unauterrstr);
			$this->printerror(404," You are not Authorised to access this URL");
		}
		
	}	
	
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->dbConDjds  		= $db[$data_city]['d_jds']['master'];
		//$this->dbConDjds_slave	= $db[$data_city]['d_jds']['slave'];
		//$this->dbConIro_slave	= $db[$data_city]['iro']['slave'];
		$this->dbConIdc   		= $db[$data_city]['idc']['master'];
		$this->finance   		= $db[$data_city]['fin']['master'];
		//$this->tme_jds   		= $db[$data_city]['tme_jds']['master'];
		$this->db_budgeting   	= $db[$data_city]['db_budgeting']['master'];
		
		//echo '<pre>';print_r($db[$data_city]['fin']['master']);
	}

	function getCustomdDataCity($datacity_custcom)
	{
		$datacity_custcom 		= ((in_array(strtolower($datacity_custcom), $this->dataservers)) ? strtolower($datacity_custcom) : 'remote');
		return $datacity_custcom;
	}
	function printerror($errorcode,$errormsg,$apiresult=null)
	{
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
  function mysql_real_escape_custom($string){
		
		$con = mysql_connect($this->dbConIdc[0], $this->dbConIdc[1], $this->dbConIdc[2]) ;
		if(!$con){
			return $string;
		}
		$escapedstring=mysql_real_escape_string($string);
		return $escapedstring;

	}
	
	function updatedoneflagstore($pid,$dcity,$dflag)
	{
		$sql= "update test.tbl_storecreation set done_flag='".$dflag."' where parentid='".$pid."' and data_city='".$dcity."'";
		parent::execQuery($sql, $this->dbConIdc);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;			
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
	}
	
	function updateversionstore($pid,$dcity)
	{
		
		$CustomdDataCity = $this->getCustomdDataCity($dcity);
		global $db;
		$this->finance_cust   		= $db[$CustomdDataCity]['fin']['master'];
		
		if(DEBUG_MODE)
		{
			echo '<br><b>dcity:</b>'.$dcity;
			echo '<br><b>CustomdDataCity</b>'.$CustomdDataCity;			
			echo '<br><b>$this->finance_cust:</b>'; print_r($this->finance_cust);
			echo '<br><b>$this->dbConIdc</b>'; print_r($this->dbConIdc);
		}
		
		$sql= "select version from payment_instrument_summary where parentid='".$pid."' and approvalStatus=1 order by entry_date desc limit 1";
		$payment_instrument_summary_res = parent::execQuery($sql, $this->finance_cust);		
		
		if(mysql_num_rows($payment_instrument_summary_res))
		{
			$payment_instrument_summary_arr = mysql_fetch_assoc($payment_instrument_summary_res);
			$version = $payment_instrument_summary_arr['version'];
		}

		
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;			
			echo '<br><b>version</b>'.$version;			
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		$sql= "update test.tbl_storecreation set version=".$version." where parentid='".$pid."' and data_city='".$dcity."'";
		parent::execQuery($sql, $this->dbConIdc);
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		return $version;
		
	}
	function omniStoreCreationAPIcall($con_parentid,$con_data_city,$con_version)
	{
		$configclassobj= new configclass();
		$urldetails= $configclassobj->get_url(urldecode($con_data_city));
		
		
		$Inparray['parentid']	=	$con_parentid;
		$Inparray['version']	=	$con_version;
		$Inparray['data_city']	=	urldecode($con_data_city);			
		$Inparray['action']		=	6; 
		$Inparray['usercode']	=	'BACKEND'; 
		$Inparray['module']		=	'cs';
				
		$curlobj = new CurlClass();
		
		//$curlurl=$urldetails['jdbox_service_url'].'getOmniDetails.php';
		$curlurl='http://172.29.26.217:1010/services/getOmniDetails.php';
		$curlobj->setOpt(CURLOPT_CONNECTTIMEOUT, 30);
		$curlobj->setOpt(CURLOPT_TIMEOUT, 900);
		$output = $curlobj->post($curlurl,$Inparray,1);
		$output_arr= json_decode($output,true);
		
		if(DEBUG_MODE)
		{
			echo "<br>curlurl".$curlurl;print_r($Inparray);
			echo '<br>output_arr'; print_r($output_arr);
		}
		
		$sql= "update test.tbl_storecreation set api_response='".$this->mysql_real_escape_custom($output)."' where parentid='".$con_parentid."' and data_city='".$con_data_city."'";
		parent::execQuery($sql, $this->dbConIdc);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
	}
	function omnistorecreation()
	{
			
		$sql = "select * from test.tbl_storecreation where done_flag=0 ";
		$restbl_storecreation = parent::execQuery($sql, $this->dbConIdc);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		if(mysql_num_rows($restbl_storecreation))
		{
			while($row=mysql_fetch_assoc($restbl_storecreation))
			{
				
				$con_parentid = $row['parentid'];				   
				$con_data_city = $row['data_city'];
				$con_version = intval($row['version']);
				 
				if(DEBUG_MODE)
				{
					echo '<br><b>row</b>'; print_r($row);
					echo '<br><b>con_parentid:</b>'.$con_parentid;
					echo '<br><b>con_data_city:</b>'.$con_data_city;
				}
				
				 
				 
				 $this->updatedoneflagstore($con_parentid,$con_data_city,9);
				 if($con_version==0)
				 {
					$con_version = $this->updateversionstore($con_parentid,$con_data_city);
				 }
				 $this->omniStoreCreationAPIcall($con_parentid,$con_data_city,$con_version);
				 $this->updatedoneflagstore($con_parentid,$con_data_city,1);
				 
			}
		}		
		
	}
	
	
	function updatedoneflagbcam($pid,$ver,$dflag)
	{
		
		$sql= "update backend_campaign_addition_master set done_flag='".$dflag."' where parentid='".$pid."' and version='".$ver."'";
		parent::execQuery($sql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;			
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
	}

	function updatedoneflagbbam($pid,$ver,$dflag)
	{
		
		$sql= "update backend_banner_addition_master set done_flag='".$dflag."' where parentid='".$pid."' and version='".$ver."'";
		parent::execQuery($sql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;			
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
	}
	
	
	function updateprocessstatbcam($dataarr)
	{
		$sql= "update backend_campaign_addition_master set 
				process_status='".$dataarr['process_status']."' ,
				process_message='".addslashes(stripslashes($dataarr['process_message']))."', 
				error_code='".$dataarr['error_code']."', 
				error_message='".addslashes(stripslashes($dataarr['error_message']))."' 
				where
				parentid='".$dataarr['parentid']."' and version='".$dataarr['version']."'";
		parent::execQuery($sql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;			
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
	}
	
	
	
	function getPaymentApportioningDetails($parentid,$version)
	{
		$retunArray= array();
		$sql="Select * from payment_apportioning where parentid='".$parentid."' and version=".$version." ";
		$restulset= parent::execQuery($sql,$this->finance);
		
		if(mysql_num_rows($restulset))
		{
			while($row=mysql_fetch_assoc($restulset))
			{
				$retunArray	[$row['campaignId']] =$row;
			}				
		}
		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql; 
			echo '<br><b> mysql_num_rows </b>'.mysql_num_rows($restulset);
			echo '<br><b>retunArray</b>'; print_r($retunArray);
		}
		return $retunArray;
	}
	
	function gettbl_companymaster_financeDetails($parentid,$version)
	{
		$retunArray= array();
		$sql="Select * from tbl_companymaster_finance where parentid='".$parentid."' and version=".$version." ";
		$restulset= parent::execQuery($sql,$this->finance);
		
		if(mysql_num_rows($restulset))
		{
			while($row=mysql_fetch_assoc($restulset))
			{
				$retunArray	[$row['campaignid']] =$row;
			}
		}
		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql; 
			echo '<br><b> mysql_num_rows </b>'.mysql_num_rows($restulset);
			echo '<br><b>retunArray</b>'; print_r($retunArray);
		}
		return $retunArray;
	}
	
	
	function gettbl_companymaster_finance_ShadowDetails($parentid,$version)
	{
		$retunArray= array();
		$sql="Select * from tbl_companymaster_finance_shadow where parentid='".$parentid."' and version=".$version." ";
		$restulset= parent::execQuery($sql,$this->finance);
		
		if(mysql_num_rows($restulset))
		{
			while($row=mysql_fetch_assoc($restulset))
			{
				$retunArray	[$row['campaignid']] =$row;
			}
		}
		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql; 
			echo '<br><b> mysql_num_rows </b>'.mysql_num_rows($restulset);
			echo '<br><b>retunArray</b>'; print_r($retunArray);
		}
		return $retunArray;
	}

	
	
	function updatebackend_campaign_addition_master_archive($parentid,$version)
	{
		$sql= "insert into backend_campaign_addition_master_archive (parentid,version,campaignidlist,insert_time,process_starttime,process_endtime,process_status,process_message,error_message,error_code,done_flag)
		select parentid,version,campaignidlist,insert_time,process_starttime,process_endtime,process_status,process_message,error_message,error_code,done_flag
		from backend_campaign_addition_master
		where parentid='".$parentid."' and version=".$version." ";
		parent::execQuery($sql,$this->finance);		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
	}
	
	function getfcvcode($data_city){
		$sCode=null;
		for($i = 0; $i < 3; $i++){
			$aChars = array('A', 'B', 'C', 'D', 'E','F','G','H', 'I', 'J', 'K', 'L','M','N','P', 'Q', 'R', 'S', 'T','U','V','W', 'X', 'Y', 'Z');
			$iTotal = count($aChars) - 1;
			$iIndex = rand(0, $iTotal);
			$sCode .= $aChars[$iIndex];
			$sCode .= chr(rand(49, 57));
		}

		$cCode1 = time().$sCode;
		$cCode  = "Admin.".$data_city.".".$cCode1;
		return     $cCode;
	}
	
	function updateCompanymasterFinance($parentid,$version,$data)
	{
		
		$companymasterfinanceDetailsarr = $this->gettbl_companymaster_financeDetails($parentid,$version);
		
		
		foreach ($companymasterfinanceDetailsarr as $companymasterfinancearrcampaignid=>$companymasterfinancearrcampaignidarr)
		{
			$finarr = $companymasterfinancearrcampaignidarr;
			break;
		}
		
		if(DEBUG_MODE)
		{
			echo '<br><b>finarr</b>'; print_r($finarr);
		}
		
		// we got finance table details in array $finarr , now we have to populate the array for new campaigns
		
		
		$campaignidlistarr = json_decode($data['campaignidlist'],true);		
		foreach($campaignidlistarr as $campaignid=>$campaignidarr)
		{
			
			// need to ask discount term budget and balance
			$bid_perday		= ($campaignidarr['bdgt']/$finarr['duration']);
			$daily_threshold = ($campaignidarr['bdgt']/$finarr['duration']);
			$campaignidarr['bal'] = $bid_perday*$data['remainingdays'];
			$sql ="INSERT INTO tbl_companymaster_finance SET
					sphinx_id		='".$finarr['sphinx_id']."',
					parentid		='".$parentid."',
					campaignid		='".$campaignid."',
					regionid		='".$finarr['regionid']."',
					nationalid		='".$finarr['nationalid']."',
					companyname		='".addslashes(stripslashes($finarr['companyname']))."',
					pincode			='".$finarr['pincode']."',
					version			='".$version."',
					duration 		=".$finarr['duration'].",
					budget			='".$campaignidarr['bdgt']."',
					discount_budget ='".$campaignidarr['bdgt']."',
					balance			='".$campaignidarr['bal']."',
					discount_balance ='".$campaignidarr['bal']."',
					bid_perday		= ".$bid_perday.",
					campaign_value	= ".($bid_perday*365).",
					discount_bid_per_day=".$bid_perday.",
					daily_threshold = ".$daily_threshold.",
					weekly_threshold = ".($daily_threshold*7).", 
					monthly_threshold = ".($daily_threshold*30).",
					active_flag 	=1,
					active_campaign =1,
					start_date 		= '".date('Y-m-d H:i:s')."',
					end_date		= DATE_ADD('".date('Y-m-d H:i:s')."', INTERVAL(balance/bid_perday) DAY) 
					
					ON DUPLICATE KEY UPDATE
					
					regionid		='".$finarr['regionid']."',
					nationalid		='".$finarr['nationalid']."',
					companyname		='".addslashes(stripslashes($finarr['companyname']))."',
					pincode			='".$finarr['pincode']."',
					version			='".$version."',
					duration 		=".$finarr['duration'].",
					budget			='".$campaignidarr['bdgt']."',
					discount_budget ='".$campaignidarr['bdgt']."',
					balance			=(balance+ ".$campaignidarr['bal']."),
					discount_balance = ( discount_balance + ".$campaignidarr['bal']."),
					bid_perday		= ".$bid_perday.",
					campaign_value	= ".($bid_perday*365).",
					discount_bid_per_day=(discount_budget/duration),
					daily_threshold = ".$daily_threshold.",
					weekly_threshold = ".($daily_threshold*7).", 
					monthly_threshold = ".($daily_threshold*30).",
					active_flag 	=1,
					active_campaign =1,
					start_date		='".date('Y-m-d H:i:s')."',
					end_date		= DATE_ADD('".date('Y-m-d H:i:s')."', INTERVAL(balance/bid_perday) DAY) " ;
			parent::execQuery($sql,$this->finance);		
			if(DEBUG_MODE)
			{
				echo '<br><b>sql</b>'.$sql;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}			
		}		
	}
	
	
	function updateCompanymasterFinanceShadow($parentid,$version,$data)
	{
		
		$financeShadowDetailsarr = $this->gettbl_companymaster_finance_ShadowDetails($parentid,$version);
		
		
		foreach ($financeShadowDetailsarr as $financeShadowDetailsarrcampaignid=>$financeShadowDetailsarrcampaignidarr)
		{
			$finshdarr = $financeShadowDetailsarrcampaignidarr;
			break;
		}
		
		if(DEBUG_MODE)
		{
			echo '<br><b>finarr</b>'; print_r($finshdarr);
		}
		
		// we got finance table details in array $finshdarr , now we have to populate the array for new campaigns
		
		
		$campaignidlistarr = json_decode($data['campaignidlist'],true);		
		foreach($campaignidlistarr as $campaignid=>$campaignidarr)
		{
			
			$bid_perday		= ($campaignidarr['bdgt']/$finshdarr['duration']);
			$daily_threshold = ($campaignidarr['bdgt']/$finshdarr['duration']);
			
			
			$sql ="INSERT INTO tbl_companymaster_finance_shadow SET
					sphinx_id		='".$finshdarr['sphinx_id']."',
					campaignid		='".$campaignid."',
					version			='".$version."',
					parentid		='".$parentid."',
					regionid		='".$finshdarr['regionid']."',
					nationalid		='".$finshdarr['nationalid']."',
					companyname		='".addslashes(stripslashes($finshdarr['companyname']))."',
					pincode			='".$finshdarr['pincode']."',
					bid_day_sel		='".$finshdarr['bid_day_sel']."',
					bid_timing		='".$finshdarr['bid_timing']."',
					duration 		=".$finshdarr['duration'].",
					budget			='".$campaignidarr['bdgt']."',
					balance			='0',
					campaign_value	= ".$campaignidarr['bdgt'].",					
					daily_threshold = ".$daily_threshold.",
					data_city		='".addslashes(stripslashes($finshdarr['data_city']))."',
					original_creator		='".$finshdarr['original_creator']."',
					original_date		='".addslashes(stripslashes($finshdarr['original_date']))."',
					updatedBy		='".addslashes(stripslashes($finshdarr['updatedBy']))."',
					updatedOn		='".$finshdarr['updatedOn']."'
										
					ON DUPLICATE KEY UPDATE
										
					parentid		='".$parentid."',
					regionid		='".$finshdarr['regionid']."',
					nationalid		='".$finshdarr['nationalid']."',
					companyname		='".addslashes(stripslashes($finshdarr['companyname']))."',
					pincode			='".$finshdarr['pincode']."',
					bid_day_sel		='".$finshdarr['bid_day_sel']."',
					bid_timing		='".$finshdarr['bid_timing']."',					
					duration 		=".$finshdarr['duration'].",
					budget			='".$campaignidarr['bdgt']."',
					balance			='0',
					campaign_value	= ".$campaignidarr['bdgt'].",					
					daily_threshold = ".$daily_threshold.",
					data_city		='".addslashes(stripslashes($finshdarr['data_city']))."',
					original_creator		='".$finshdarr['original_creator']."',
					original_date		='".addslashes(stripslashes($finshdarr['original_date']))."',
					updatedBy		='".addslashes(stripslashes($finshdarr['updatedBy']))."',
					updatedOn		='".$finshdarr['updatedOn']."' " ;
			parent::execQuery($sql,$this->finance);		
			if(DEBUG_MODE)
			{
				echo '<br><b>sql</b>'.$sql;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
		}
		
	}
	
	function ApplyFCV($parentid,$version,$fcvAmt,$data)
	{  // This function will create fcv and update fcv table 
		
		// backend_campaign_addition_fcv_details is table which tell us we can not process the same contract version twice
		$sql= "insert into backend_campaign_addition_fcv_details (parentid,version,fcvamount,processed_flag,updatedon,isdeleted) values
				('".$parentid."','".$version."','".$fcvAmt."',1,'".date('Y-m-d H:i:s')."',0)";
		parent::execQuery($sql,$this->finance);
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		
		if($fcvAmt>0)
		{
			$id_generatorarray = $this->gettbl_id_generator($parentid);
			$cCode = $this->getfcvcode($id_generatorarray['data_city']);
				
			$sql= " INSERT INTO payment_fdvfcv_master 
					(transactionid,parentid,amount,doneby,doneon,donebyip,transactiontype,reason,approvedby,comments,transactiondone) VALUES
					('".$cCode."','".$parentid."','".$fcvAmt."','Backend',NOW(),'Backend','GEN FCV','Festive combo offer','Divyesh Jain','Auto FCV added through backend process',1)";
							 
			parent::execQuery($sql,$this->finance);					
			if(DEBUG_MODE)
			{
				echo '<br><b>sql</b>'.$sql;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
				
			$sql= " INSERT INTO payment_campaign_fdvfcv (transactionid,parentid,transferAmount,campaignId) VALUES ('".$cCode."','".$parentid."','".$fcvAmt."','0')";
			parent::execQuery($sql,$this->finance);
			if(DEBUG_MODE)
			{
				echo '<br><b>sql</b>'.$sql;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
		}
		
	}
	
	function deleteECSTablesEntries($parentid,$version)
	{
		$sql="delete from db_ecs_billing.payment_ecs_apportioning where parentid='".$parentid."' and version='".$version."' ";
		$res=parent::execQuery($sql,$this->finance);		
		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;		
		}
		
		$sql="delete from db_si_billing.si_payment_ecs_apportioning where parentid='".$parentid."' and version='".$version."' ";
		$res=parent::execQuery($sql,$this->finance);		
		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;		
		}
		
	}
	
	function deleteDependantTableEntry($parentid,$version,$depcampaigidarr)
	{
		if(!is_array(array_filter($depcampaigidarr)))
		return;
		
		$sql="delete from dependant_campaign_details where parentid='".$parentid."' and version='".$version."' and dep_campaignid in (".implode(',',$depcampaigidarr).") ";
		$res=parent::execQuery($sql,$this->finance);		
		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;		
		}
		
		$sql="delete from dependant_campaign_details_appr where parentid='".$parentid."' and version='".$version."' and dep_campaignid in (".implode(',',$depcampaigidarr).") ";
		$res=parent::execQuery($sql,$this->finance);		
		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;		
		}
		
	}	
	
	function insertIntobackend_campaign_addition_log($parentid,$version,$oldtabledatavalue,$newtabledatavalue)
	{
		
		$sql= " Insert Into backend_campaign_addition_log set 
				parentid		='".$parentid."',
				version			='".$version."',
				table_data_old	='".$this->mysql_real_escape_custom(json_encode($oldtabledatavalue))."',
				table_data_new	='".$this->mysql_real_escape_custom(json_encode($newtabledatavalue))."',
				insertedon		='".date('Y-m-d H:i:s')."',
				insertedby  ='backend'";
		parent::execQuery($sql,$this->finance);
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
	}
	
	function datacapture($parentid,$version)
	{// this function will return the data from all tables which are going to be update
		
		$Resultarry=array();
		$sql="select * from tbl_companymaster_finance where parentid='".$parentid."' ";
		$res=parent::execQuery($sql,$this->finance);		
		if($res && mysql_num_rows($res)>0){
			$tabledata=array();
			while($row=mysql_fetch_assoc($res)){
				 $tabledata[] = $row;
			}			
			$Resultarry['tbl_companymaster_finance']=($tabledata);
		}
		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
			echo '<br><b>tabledata</b>'; if(isset($tabledata) && count($tabledata))print_r($tabledata);
		}

		$sql="select * from tbl_companymaster_finance_shadow where parentid='".$parentid."' and version='".$version."' ";
		$res=parent::execQuery($sql,$this->finance);		
		if($res && mysql_num_rows($res)>0){
			$tabledata=array();
			while($row=mysql_fetch_assoc($res)){
				 $tabledata[] = $row;
			}			
			$Resultarry['tbl_companymaster_finance_shadow']=($tabledata);
		}
				
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
			echo '<br><b>tabledata</b>'; if(isset($tabledata) && count($tabledata))print_r($tabledata);
		}

		$sql="select * from payment_apportioning where parentid='".$parentid."' and version='".$version."' ";
		$res=parent::execQuery($sql,$this->finance);
		
		if($res && mysql_num_rows($res)>0){
			$tabledata=array();	
			while($row=mysql_fetch_assoc($res)){
				 $tabledata[] = $row;
			}			
			$Resultarry['payment_apportioning']=($tabledata);
		}
		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
			echo '<br><b>tabledata</b>'; if(isset($tabledata) && count($tabledata))print_r($tabledata);
		}

		$sql="select * from campaign_dealclose_budget where parentid='".$parentid."' and version='".$version."' ";
		$res=parent::execQuery($sql,$this->finance);
		
		if($res && mysql_num_rows($res)>0){	
			$tabledata=array();
			while($row=mysql_fetch_assoc($res)){
				 $tabledata[] = $row;
			}			
			$Resultarry['campaign_dealclose_budget']=($tabledata);
		}
		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
			echo '<br><b>tabledata</b>'; if(isset($tabledata) && count($tabledata))print_r($tabledata);
		}


		$sql="select * from contract_dealclose_budget where parentid='".$parentid."' and version='".$version."' ";
		$res=parent::execQuery($sql,$this->finance);		
		if($res && mysql_num_rows($res)>0){
			$tabledata=array();
			while($row=mysql_fetch_assoc($res)){
				 $tabledata[] = $row;
			}
			$Resultarry['contract_dealclose_budget']=($tabledata);
		}
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
			echo '<br><b>tabledata</b>'; if(isset($tabledata) && count($tabledata))print_r($tabledata);
		}		
		
		
		$sql="select * from payment_fdvfcv_master where parentid='".$parentid."' ";
		$res=parent::execQuery($sql,$this->finance);		
		if($res && mysql_num_rows($res)>0){	
			$tabledata=array();
			while($row=mysql_fetch_assoc($res)){
				 $tabledata[] = $row;
			}			
			$Resultarry['payment_fdvfcv_master']=($tabledata);
		}		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
			echo '<br><b>tabledata</b>'; if(isset($tabledata) && count($tabledata))print_r($tabledata);
		}
		
		$sql="select * from payment_campaign_fdvfcv where parentid='".$parentid."' ";
		$res=parent::execQuery($sql,$this->finance);		
		if($res && mysql_num_rows($res)>0){	
			$tabledata=array();
			while($row=mysql_fetch_assoc($res)){
				 $tabledata[] = $row;
			}			
			$Resultarry['payment_campaign_fdvfcv']=($tabledata);
		}		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
			echo '<br><b>tabledata</b>'; if(isset($tabledata) && count($tabledata))print_r($tabledata);
		}
										
		
		$sql="select * from db_ecs_billing.payment_ecs_apportioning where parentid='".$parentid."' and version='".$version."' ";
		$res=parent::execQuery($sql,$this->finance);		
		if($res && mysql_num_rows($res)>0){	
			$tabledata=array();
			while($row=mysql_fetch_assoc($res)){
				 $tabledata[] = $row;
			}			
			$Resultarry['payment_ecs_apportioning']=($tabledata);
		}		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
			echo '<br><b>tabledata</b>'; if(isset($tabledata) && count($tabledata))print_r($tabledata);
		}
		
		$sql="select * from db_si_billing.si_payment_ecs_apportioning where parentid='".$parentid."' and version='".$version."' ";
		$res=parent::execQuery($sql,$this->finance);		
		if($res && mysql_num_rows($res)>0){	
			$tabledata=array();
			while($row=mysql_fetch_assoc($res)){
				 $tabledata[] = $row;
			}
			$Resultarry['si_payment_ecs_apportioning']=($tabledata);
		}
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
			echo '<br><b>tabledata</b>'; if(isset($tabledata) && count($tabledata))print_r($tabledata);
		}
		
		
		
		
		$sql="select * from dependant_campaign_details where parentid='".$parentid."' and version='".$version."' ";
		$res=parent::execQuery($sql,$this->finance);		
		if($res && mysql_num_rows($res)>0){	
			$tabledata=array();
			while($row=mysql_fetch_assoc($res)){
				 $tabledata[] = $row;
			}
			$Resultarry['dependant_campaign_details']=($tabledata);
		}
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
			echo '<br><b>tabledata</b>'; if(isset($tabledata) && count($tabledata))print_r($tabledata);
		}
		
		$sql="select * from dependant_campaign_details_appr where parentid='".$parentid."' and version='".$version."' ";
		$res=parent::execQuery($sql,$this->finance);		
		if($res && mysql_num_rows($res)>0){	
			$tabledata=array();
			while($row=mysql_fetch_assoc($res)){
				 $tabledata[] = $row;
			}
			$Resultarry['dependant_campaign_details_appr']=($tabledata);
		}
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
			echo '<br><b>tabledata</b>'; if(isset($tabledata) && count($tabledata))print_r($tabledata);
		}
		
		return $Resultarry;
	
		
	}
	
	function getRemainingdays($parentid,$version)
	{
		$remainingdays = 365;
		
		$sql="select max(ROUND(balance/bid_perday)) as remainingdays from tbl_companymaster_finance where parentid = '".$parentid."' and version= ".$version." and campaignid in (1,2) and balance>0 ";
		$res = parent::execQuery($sql,$this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		if(($res) && mysql_num_rows($res))
		{
			$row=mysql_fetch_assoc($res);
			$remainingdays = $row['remainingdays'];
		}
		
		return $remainingdays;
					
	}
	
	function explode_bidder($bidder_strng,$actual=0)
	{
		if(trim($bidder_strng)!='')
		{
			$temparr	= explode(",",$bidder_strng);
			foreach($temparr as $temp)
			{
				$bidder_arr	= explode("-",$temp);
				if($actual == 1)
				{
					$return_arr[$bidder_arr['0']]	= $bidder_arr['1']."~".$bidder_arr['2'];
				}else
				{
					$return_arr[$bidder_arr['0']]	= $bidder_arr['1'];
				}			
			}
		}
		return $return_arr;
	}
	
	function getCatsponAvail($parentid,$data_city,$catidarr)
	{

		$available	= array();
		if(count($catidarr))
		{
			$catidstr	= implode(",",$catidarr);
			if(trim($catidstr)!='')
			{
				$sql	= "SELECT catid,cat_sponbanner_bidder,cat_sponbanner_inventory FROM tbl_cat_banner_bid WHERE catid IN (".$catidstr.") AND data_city='".$data_city."'";
				$res	= parent::execQuery($sql,$this->finance);
				if($res && mysql_num_rows($res))
				{
					while($row = mysql_fetch_assoc($res))
					{
						if(trim($row['cat_sponbanner_bidder'])!='')
						{
							$bidder_arr	= $this->explode_bidder($row['cat_sponbanner_bidder']);
							$get_bidder	= array_keys($bidder_arr);
							if(in_array($parentid,$get_bidder))
							{
								$available[$row['catid']] = (1 - ($row['cat_sponbanner_inventory'] - $bidder_arr[$parentid]));
							}else
							{
								$available[$row['catid']] = (1 - $row['cat_sponbanner_inventory']);
							}
						}else
						{
							$available[$row['catid']] = 1;
						}
					}
				}
			}

			$catid_frm_avail = array_keys($available);
			$diffarr		 = array_diff($catidarr,$catid_frm_avail);

			foreach($diffarr as $cat){
			$available[$cat]	= 1;
			}
		}
		return $available;
	}
		
	function insertIntoCatsponCompbanner($parentid,$version,$approvalstatus)
	{
		//$this->dbConIro $this->dbConDjds $this->dbConIdc 
		//$conn_local,$bannerObj,$catid_arr,$parentid,$finrsarray
		// active pending
		
		$sql="select replace(catidlineage,'/','') as catidlineage from tbl_companymaster_extradetails where parentid='".$parentid."' ";
		
		if($approvalstatus=='active')
		{
			$res = parent::execQuery($sql,$this->dbConIro);
			
		}elseif($approvalstatus=='pending')
		{
			$res = parent::execQuery($sql,$this->dbConIdc);
		}
		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>approvalstatus</b>'.$approvalstatus;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		if(mysql_num_rows($res))
		{
			$row= mysql_fetch_assoc($res);
			$catarr_str = $row['catidlineage'];
			if(strlen($catarr_str))
			{
				$catid_arr = explode(',',$catarr_str);				
				$catid_arr = array_filter($catid_arr);				
				if(count($catid_arr) > 0)
				{			
					//$sql	= "SELECT catid,category_name,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN($catarr_str)";
					//$res_cat	=  parent::execQuery($sql,$this->dbConDjds);
					$cat_params = array();
					$cat_params['page'] =	'campaignadditionclass';
					$cat_params['data_city'] 	= $this->data_city;										
					$cat_params['return']		= 'catid,national_catid,category_name';	

					$where_arr  	=	array();			
					$where_arr['catid']			= $catarr_str;		
					$cat_params['where']		= json_encode($where_arr);
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);

					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}
					
					if(count($cat_res_arr['results'])>0 && $cat_res_arr['errorcode']=='0')
					{												
						foreach($cat_res_arr['results'] as $key=>$row_cat)
						{
							$catdetails_arr[$row_cat['catid']]['catname']	= $row_cat['category_name'];
							$catdetails_arr[$row_cat['catid']]['nat_cat']	= $row_cat['national_catid'];
						}
					}
					
					if(DEBUG_MODE)
					{
						echo '<br><b>sql</b>'.$sql;
						echo '<br><b>Error:</b>'.$this->mysql_error;
						echo '<br><b>catdetails_arr</b>';print_r($catdetails_arr);						
					}
				}
			}
		}
		
		$pymntAptngarr 		= 	$this->getPaymentApportioningDetails($parentid,$version);
		$id_generatorarray 	= 	$this->gettbl_id_generator($parentid);
		$data_city			=	$id_generatorarray['data_city'];
		
		
		if(isset($pymntAptngarr[13]['budget']))
		{
			$catspon_budget	=	$pymntAptngarr[5]['budget'];
		}else
		{
			$catspon_budget	=	$this->defaultcatsponbudget;
		}
		
		if(isset($pymntAptngarr[5]['budget']))
		{
			$compbanner_budget	=	$pymntAptngarr[5]['budget'];
		}else
		{
			$compbanner_budget	=	$this->defaultcompbannerbudget;
		}
	
	
		if(count($catdetails_arr) > 0)
		{
			
			$insert_arr = array();
			$i			= 0; 
			$count_cat	= count($catdetails_arr);
			$catids		= array_keys($catdetails_arr);
			$inv_arr	= $this->getCatsponAvail($parentid,$data_city,$catids);
			
			if(DEBUG_MODE)
			{	
				echo '<br><b>data_city:</b>'.$data_city;
				echo '<br><b>$pymntAptngarr:</b>'; print_r($pymntAptngarr);
				echo '<br><b>$catids:</b>'; print_r($catids);
				echo '<br><b>inv_arr:</b>'; print_r($inv_arr);
			}
			
			foreach($catdetails_arr as $catid => $catname)
			{
					$sql = "INSERT INTO tbl_comp_banner set
					parentid='".$parentid."',
					catid='".$catid."',
					campaign_type=4,						
					cat_name='".addslashes($catname['catname'])."',						
					banner_camp=2,
					national_catid='".$catname['nat_cat']."',
					update_date= '".date('Y-m-d H:i:s')."',
					tenure = 365,						
					budget=".$compbanner_budget.",					
					variable_budget	= ".($compbanner_budget/$count_cat).",
					campaign_name='cat_banner',
					iscalculated=1,
					inventory=0						
					ON DUPLICATE KEY UPDATE
					cat_name='".addslashes($catname['catname'])."',						
					banner_camp=2,
					national_catid='".$catname['nat_cat']."',
					update_date='".date('Y-m-d H:i:s')."',
					tenure = 365,						
					budget=".$compbanner_budget.",					
					variable_budget	= ".($compbanner_budget/$count_cat).",		
					campaign_name='cat_banner',
					iscalculated=1,
					inventory=0 ";

					if($approvalstatus=='active')
					{
						parent::execQuery($sql,$this->dbConDjds);
						
					}elseif($approvalstatus=='pending')
					{
						parent::execQuery($sql,$this->dbConIdc);
					}
					
					if(DEBUG_MODE)
					{
						echo '<br><b>sql</b>'.$sql;
						echo '<br><b>Error:</b>'.$this->mysql_error;						
					}
				
										
					if($inv_arr[$catid] > 0)
					{
						$sql = "INSERT INTO tbl_catspon set
						parentid='".$parentid."',
						catid = '".$catid."',
						campaign_type  	= 1,
						cat_name = '".addslashes($catname['catname'])."',
						national_catid	= ".$catname['nat_cat'].",
						iscalculated 	= 1,
						banner_camp  	= 2,
						tenure 		 	= 365,
						budget=".$catspon_budget.",
						variable_budget	= ".($catspon_budget/$count_cat).",
						update_date= '".date('Y-m-d H:i:s')."',
						campaign_name  	= 'catspon'
												
						ON DUPLICATE KEY UPDATE
						cat_name = '".addslashes($catname['catname'])."',
						national_catid	= ".$catname['nat_cat'].",
						iscalculated 	= 1,
						banner_camp  	= 2,
						tenure 		 	= 365,
						budget=".$catspon_budget.",
						variable_budget	= ".($catspon_budget/$count_cat).",
						update_date= '".date('Y-m-d H:i:s')."',
						campaign_name  	= 'catspon'";
					
						if($approvalstatus=='active')
						{
							parent::execQuery($sql,$this->dbConDjds);
							
						}elseif($approvalstatus=='pending')
						{
							parent::execQuery($sql,$this->dbConIdc);
						}
						
						if(DEBUG_MODE)
						{
							echo '<br><b>sql</b>'.$sql;
							echo '<br><b>Error:</b>'.$this->mysql_error;						
						}
					}
				
			}// foreach end 
		
		}//if end
	}
	
	function insertIntoTblBannerApproval($parentid)
	{
		$sql ="insert ignore into db_finance.tbl_banner_approval (parentid,campaignid,version,companyname,entry_date,fin_approveddate,data_city,approval_status) 
				(select  parentid,campaignid,version,companyname,start_date as entry_date,start_date as fin_approveddate,data_city, 0 as approval_status from  db_finance.tbl_companymaster_finance where parentid='".$parentid."' and campaignid in (5,13) and balance>0 );";		
		parent::execQuery($sql,$this->finance);

		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;						
		}		

		$ucode='backend';
		$sql ="insert ignore into db_finance.banner_status_report_log(parentid,companyname,data_city,version,campaignid,usercode)(select  parentid,companyname,data_city,version,campaignid,'$ucode' from  db_finance.tbl_companymaster_finance where parentid='".$parentid."' and campaignid in (5,13) and balance>0 );";		
		parent::execQuery($sql,$this->finance);
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;						
		}		
		
	}
	
	function insertIntoBannerTables($data)
	{// this function will insert into banner tables as well as banner uploadd module table based on status of its approval status 
		
		$version 	= $data['version'];
		$parentid 	= $data['parentid'];
		if(isset($data['version_status']))
		{
			$version_approval_status = $data['version_status'];
		}else
		{		
			$VersionApprovalStatus 		= $this->checkApprovalStatusOfVersion($parentid,$version);
			$version_approval_status 	= $VersionApprovalStatus['version_status'];
		}
		
		if($version_approval_status=='active')
		{ // we need to inset into main tables
			
			$this->insertIntoCatsponCompbanner($parentid,$version,$version_approval_status);
			$this->insertIntoTblBannerApproval($parentid);
			
		}elseif($version_approval_status=='pending')
		{ // we need to inset into deal closed tables
			
			$this->insertIntoCatsponCompbanner($parentid,$version,$version_approval_status);			
		}
		
		
	}
	
	
	function InsertionIntoPaymentTables($data)
	{
		$retunArray = array();		
				
		$version 	= $data['version'];
		$parentid 	= $data['parentid'];
		$version_approval_status = $data['version_status'];
		$totalFCVamount=0;
		$totalcampaignbudget=0;
		
		if(DEBUG_MODE)
		{
			echo '<br><b> InsertionIntoPaymentTables $data:</b>'; print_r($data);
		}
		
		
		if($version_approval_status=='active')
		{
			$data['remainingdays']= $this->getRemainingdays($parentid,$version);
		}


		$campaignidlistarr = json_decode($data['campaignidlist'],true);
		$discampaignid = array_keys($campaignidlistarr);		
		$PaymentApportioningarr = $this->getPaymentApportioningDetails($parentid,$version);
		
		$entry_date = $start_date =$source = $disruption_flag =  null;
		$duration = 365;
		
		
		foreach ($PaymentApportioningarr as $PaymentApportioningarrcampaignid=>$PaymentApportioningarrcampaignarr)
		{
			$entry_date = $PaymentApportioningarrcampaignarr['entry_date'];
			$start_date = $PaymentApportioningarrcampaignarr['start_date'];
			$source		= $PaymentApportioningarrcampaignarr['source'];
			if($PaymentApportioningarrcampaignarr['duration']){ $duration = min($duration,$PaymentApportioningarrcampaignarr['duration']); } 			
			
			if($PaymentApportioningarrcampaignarr['disruption_flag']==0)
			{
				$disruption_flag = $PaymentApportioningarrcampaignarr['disruption_flag'];
				
			}elseif(($PaymentApportioningarrcampaignarr['disruption_flag']>0))
			{
				$disruption_flag = min($disruption_flag,$PaymentApportioningarrcampaignarr['disruption_flag']); 	
			}
			
		}
		
		
		$oldtabledatavalu=$this->datacapture($parentid,$version);
		
		// insertion on payment_apportioning 
		foreach($campaignidlistarr as $campaignid=>$campaignidarr)
		{
			
			if($version_approval_status=='active')
			{
				$campaignidarr['bal'] = ($campaignidarr['bdgt']/$duration)*$data['remainingdays'];
				$app_duration		=$data['remainingdays'];
				$tot_app_duration	=$data['remainingdays'];
				$app_amount			=$campaignidarr['bal'];
				$tot_app_amount		=$campaignidarr['bal'];
				$start_dateval		="'".$start_date."'";
				$totalFCVamount = $totalFCVamount+ $campaignidarr['bal'];
					
			}elseif($version_approval_status=='pending')
			{
				$app_duration		=0;
				$tot_app_duration	=0;
				$app_amount			=0;
				$tot_app_amount		=0;
				$start_dateval		= 'null';
				$totalcampaignbudget=$totalcampaignbudget+$campaignidarr['bdgt'];
			}
			
			$sql ="INSERT INTO payment_apportioning SET
					parentid   	= '".$parentid."',
					campaignId	= '".$campaignid."',
					version		= '".$version."',
					budget		= '".$campaignidarr['bdgt']."',
					balance		= 0 ,
					duration	= '".$duration."',
					entry_date	= '".$entry_date."',
					start_date	= $start_dateval,
					app_duration= '".$app_duration."',
					app_amount	= '".$app_amount."',
					tot_app_duration = '".$tot_app_duration."',
					tot_app_amount	 = '".$tot_app_amount."',
					source		= '".$source."',
					isdeleted	=	'0',
					disruption_flag= '".$disruption_flag."'					
					ON DUPLICATE KEY UPDATE
					budget		= '".$campaignidarr['bdgt']."',
					balance		= 0 ,
					duration	= '".$duration."',
					entry_date	= '".$entry_date."',
					start_date	=  $start_dateval,
					app_duration= '".$app_duration."',
					app_amount	= '".$app_amount."',					
					tot_app_duration = '".$tot_app_duration."',
					tot_app_amount	 = '".$tot_app_amount."',
					source		= '".$source."',	
					isdeleted	=	'0',
					disruption_flag= '".$disruption_flag."'	";
					
					parent::execQuery($sql,$this->finance);
					
					if(DEBUG_MODE)
					{
						echo '<br><b>sql</b>'.$sql;
						echo '<br><b>Error:</b>'.$this->mysql_error;
					}
					
					
										
		}
		
		
		if($version_approval_status=='active')
		{
			// insertion into campaign_dealclose_budget
			foreach($campaignidlistarr as $campaignid=>$campaignidarr)
			{
				$sql = "INSERT INTO campaign_dealclose_budget SET
						parentid		 = '".$parentid."',
						version		     = '".$version."',
						campaignid		 = '".$campaignid."',
						system_budget    = '".$campaignidarr['bdgt']."',
						net_budget       = '0.00',
						premium_budget   = '0.00',
						discount_budget  = '".$campaignidarr['bdgt']."',
						contract_amount  = '".$campaignidarr['bdgt']."',
						accrual_amount   = '0.00',
						service_amount   = '".$campaignidarr['bdgt']."',
						entry_date       = '".$entry_date."'";
						
				parent::execQuery($sql,$this->finance);
				
				if(DEBUG_MODE)
				{
					echo '<br><b>sql</b>'.$sql;
					echo '<br><b>Error:</b>'.$this->mysql_error;
				}					
			}
			
			// insertion into contract_dealclose_budget	
			$sql = "UPDATE contract_dealclose_budget SET discount_budget = (discount_budget+".$totalFCVamount."),
					system_budget	= net_budget + premium_budget  + discount_budget,
					contract_amount = net_budget + premium_budget  + discount_budget,
					service_amount	= net_budget + premium_budget  + discount_budget
					WHERE parentid	= '".$parentid."' AND version = '".$version."'";
			parent::execQuery($sql,$this->finance);
			
			if(DEBUG_MODE)
			{
				echo '<br><b>sql</b>'.$sql;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
			// Apply fcv and put into fcv table			
			$this->ApplyFCV($parentid,$version,$totalFCVamount,$data);
			
			$this->updateCompanymasterFinance($parentid,$version,$data);
			
		}elseif($version_approval_status=='pending')
		{
			
						// insertion into campaign_dealclose_budget
			foreach($campaignidlistarr as $campaignid=>$campaignidarr)
			{
				$sql = "INSERT INTO campaign_dealclose_budget SET
						parentid		 = '".$parentid."',
						version		     = '".$version."',
						campaignid		 = '".$campaignid."',
						system_budget    = '".$campaignidarr['bdgt']."',
						net_budget       = '".$campaignidarr['bdgt']."',
						premium_budget   = '0',
						discount_budget  = '0',
						contract_amount  = '".$campaignidarr['bdgt']."',
						accrual_amount   = '".$campaignidarr['bdgt']."',
						service_amount   = '".$campaignidarr['bdgt']."',
						entry_date       = '".$entry_date."'";
						
				parent::execQuery($sql,$this->finance);
				
				if(DEBUG_MODE)
				{
					echo '<br><b>sql</b>'.$sql;
					echo '<br><b>Error:</b>'.$this->mysql_error;
				}					
			}
			
			// insertion into contract_dealclose_budget	
			$sql = "UPDATE contract_dealclose_budget SET					
					system_budget	= system_budget + ".$totalcampaignbudget.",
					net_budget      = net_budget	+ ".$totalcampaignbudget.",
					contract_amount = contract_amount + ".$totalcampaignbudget.",
					accrual_amount  = accrual_amount + ".$totalcampaignbudget.",
					service_amount	= service_amount + ".$totalcampaignbudget."
					WHERE parentid	= '".$parentid."' AND version = '".$version."'";
			parent::execQuery($sql,$this->finance);
			
			if(DEBUG_MODE)
				{
					echo '<br><b>sql</b>'.$sql;
					echo '<br><b>Error:</b>'.$this->mysql_error;
				}					
				
			$this->updateCompanymasterFinanceShadow($parentid,$version,$data);
			$this->ApplyFCV($parentid,$version,0,$data);
		}
		
		$this->deleteECSTablesEntries($parentid,$version);
		$this->deleteDependantTableEntry($parentid,$version,$discampaignid);
		
		
		$newtabledatavalu=$this->datacapture($parentid,$version);		
		$this->insertIntobackend_campaign_addition_log($parentid,$version,$oldtabledatavalu,$newtabledatavalu);
		
		$retunArray['process_message'] ='Contract got processed sucessfully';
		$retunArray['process_status'] = 'pass';
		$retunArray['status'] = 'pass';
		return $retunArray;
	}
	
	
	function checkApprovalStatusOfVersion($parentid,$version)
	{ 	// we need to check the current status of that version . We can process only if the versio is either approved and current active version or pending for approval
		$retunArray= array();		
		$versionstatus = null;
		
		$sql="select parentid from tbl_companymaster_finance where parentid = '".$parentid."' and version= ".$version." and campaignid in (1,2) and balance>0 ";
		$res = parent::execQuery($sql,$this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		if(($res) && mysql_num_rows($res))
		{
			$retunArray['process_message'] =' version '.$version.' present on tbl_companymaster_finance table ';
			$retunArray['process_status'] = 'pass';
			$retunArray['version_status'] = 'active';
			$retunArray['status'] = 'pass';
			
		}else
		{
			
			$sql="select parentid from tbl_companymaster_finance_shadow where parentid = '".$parentid."' and version= ".$version." and campaignid in (1,2) ";
			$res = parent::execQuery($sql,$this->finance);
			
			if(DEBUG_MODE)
			{
				echo '<br><b>sql</b>'.$sql;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
			if(($res) && mysql_num_rows($res))
			{
				$retunArray['process_message'] =' version '.$version.' present on tbl_companymaster_finance_shadow table ';
				$retunArray['process_status'] = 'pass';
				$retunArray['version_status'] = 'pending';
				$retunArray['status'] = 'pass';
				
			}else
			{
				$retunArray['process_message'] =' version '.$version.' neither present on tbl_companymaster_finance nor on tbl_companymaster_finance_shadow ';
				$retunArray['process_status'] = 'fail';
				$retunArray['version_status'] = 'invalid';
				$retunArray['status'] = 'fail';
			}
		}		
		
		
		return $retunArray;
		
	}
	
	
	function checkepaymentapportioningEntry($parentid,$campaignlist,$version)
	{ 	// We will check campaignid already existing or not , if campaingid is already existing then can't process 
						
		$retunArray= array();
		
		
		$sql="Select group_concat('{campaignid = ',campaignid,' budget = ',budget,' balance = ',balance,'}' separator '#') as campbudgetbal,count(1) as countval from payment_apportioning where parentid='".$parentid."' and version='".$version."'  and campaignid in (".implode(',',$campaignlist).") ";
		
		$restulset= parent::execQuery($sql,$this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;		
		}

		if(mysql_num_rows($restulset))
		{
			$row=mysql_fetch_assoc($restulset);
			
			if(DEBUG_MODE)
			{			
				echo '<br><b> row </b>'; print_r($row);
			
			}
			
			if($row['countval']>0)
			{				
				$retunArray['process_message'] = 'Entries already present on payment_apportioning forparentid '.$parentid.' and version '.$version. ' : ' .$row['campbudgetbal'];
				$retunArray['process_status'] = 'fail';
				$retunArray['status'] = 'fail';
				
			}else
			{
				
				// check there should be only on entry_date for that version 
				
				$sql="select group_concat('{campaignid = ',campaignid,' entry_date= ',entry_date,'}' separator '#') as campentrydate,TIMESTAMPDIFF(Second, min(entry_date),max(entry_date)) as seconddif,count(distinct entry_date) as countval from payment_apportioning where parentid='".$parentid."' and version='".$version."'";
						
				$restulset= parent::execQuery($sql,$this->finance);
				$row=mysql_fetch_assoc($restulset);
				
				if(DEBUG_MODE)
				{
					echo '<br><b>sql</b>'.$sql;
					echo '<br><b>Error:</b>'.$this->mysql_error;
					echo '<br><b> row </b>'; print_r($row);
				}
				
				if($row['countval']==0)
				{				
					$retunArray['process_message'] = 'Entry not present on payment_apportioning for parentid '.$parentid.' and version '.$version.''; 
					$retunArray['process_status'] = 'fail';
					$retunArray['status'] = 'fail';
					
				}elseif($row['countval']>1)
				{	
					if($row['seconddif']<60) // if time difference is less than 60 sec / 1 min than we will conside it as ok 
					{
						$retunArray['process_status'] = 'pass';
						$retunArray['status'] = 'pass';	
						
					}else
					{
						$retunArray['process_message'] = 'Multiple entry_date present on payment_apportioning for that version : ' .$row['campentrydate'];
						$retunArray['process_status'] = 'fail';
						$retunArray['status'] = 'fail';
					}
					
				}elseif($row['countval']==1)
				{
					$retunArray['process_status'] = 'pass';
					$retunArray['status'] = 'pass';	
				}
			}
		}
		
		
		if(DEBUG_MODE)
		{								
			echo '<br><b>retunArray</b>'; print_r($retunArray);
		}
		
		return $retunArray;
				
	}
	
	
	function checkbackend_campaign_addition_fcv_detailsEntry($parentid,$version)
	{
		// If there is entry present on backend_campaign_addition_fcv_details then its already processed so it can not be processed again 
		
		
		$retunArray= array();
		
		$sql="select * from backend_campaign_addition_fcv_details where parentid='".$parentid."' and version='".$version."' and processed_flag=1 and isdeleted=0";
		$restulset= parent::execQuery($sql,$this->finance);
		
		if(mysql_num_rows($restulset))
		{	
			$retunArray['process_message'] ='Entry present on backend_campaign_addition_fcv_details for parentid = '.$parentid.' and version '.$version;
			$retunArray['process_status'] = 'fail';
			$retunArray['status'] = 'fail';

		}else
		{
			$retunArray['process_status'] = 'pass';
			$retunArray['status'] = 'pass';
		}
		
		
		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql; 
			echo '<br><b>Error:</b>'.$this->mysql_error;
			echo '<br><b> mysql_num_rows </b>'.mysql_num_rows($restulset);
			echo '<br><b>retunArray</b>'; print_r($retunArray);
		}
		return $retunArray;
		
		
	}
	
	function checkbalanceeligibilityforCampaignAddition($parentid,$campaignlist)
	{ 	// We will check eligibility of campaign addition whether we can add or not campaignid 
		// if we get any campaign with balance in companymaster_finance then we will not process such contract 	
		
		
		$retunArray= array();
		
		$sql="Select group_concat('{campaignid = ',campaignid,' budget = ',budget,' balance = ',balance,'}' separator '#') as campbudgetbal,count(1) as countval from tbl_companymaster_finance where parentid='".$parentid."' and campaignid in (".implode(',',$campaignlist).") and balance>0 ";
		$restulset= parent::execQuery($sql,$this->finance);
		
		if(mysql_num_rows($restulset))
		{
			$row=mysql_fetch_assoc($restulset);
			
			if($row['countval']>0)
			{				
				$retunArray['process_message'] =$row['campbudgetbal'];
				$retunArray['process_status'] = 'fail';
				$retunArray['status'] = 'fail';
				
			}else
			{
				$retunArray['process_status'] = 'pass';
				$retunArray['status'] = 'pass';
			}
		}
		
		
		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql</b>'.$sql; 
			echo '<br><b>Error:</b>'.$this->mysql_error;
			echo '<br><b> row </b>'; print_r($row);
			echo '<br><b>retunArray</b>'; print_r($retunArray);
		}
		return $retunArray;
				
	}
	
	function getError($error)
	{
		$returnarry= array();
		
				
		switch($error)
        {
            case 'balancepresentonfinancetable' :
                $returnarry['error_code'] 	= 11;
                $returnarry['error_message']= "Balance present on finance table";
            break;
            
            case 'paymentapportioningdataissue' :
                $returnarry['error_code'] 	= 21;
                $returnarry['error_message']= "Data issue present on payment_apportioning ";
            break;
           
            case 'fcvalreadygivenbyprocess' :
                $returnarry['error_code'] 	= 31;
                $returnarry['error_message']= "fcv already given by process";
             break;
             
             case 'versionapprovalstatus' :
                $returnarry['error_code'] 	= 51;
                $returnarry['error_message']= "invalid version as per finance main and shadow table ";
             break;
                
             case'sucessful':
              $returnarry['error_code'] 	= 0;
               $returnarry['error_message']= "No Error ";
            break;
           
           
        }
        return $returnarry;
        
	}
	
	
	function campaignprocessing($dataarry)
	{
		$retunArray= array();
		$version = $dataarry['version'];
		$parentid = $dataarry['parentid'];
		$paramsaarry['version'] 	= $dataarry['version'];
		$paramsaarry['parentid'] 	= $dataarry['parentid'];
			
		$campaignidlistarr = json_decode($dataarry['campaignidlist'],true);
		$discampaignid = array_keys($campaignidlistarr);
		
		if(DEBUG_MODE)
		{
		
			echo '<br><b>campaignidlistarr:</b>'; print_r($campaignidlistarr);
			echo '<br><b>discampaignid:</b>'; print_r($discampaignid);
		}
		
		//echo '$discampaignid';print_r($discampaignid);
		
		
		$eligres = $this->checkbalanceeligibilityforCampaignAddition($parentid,$discampaignid);
		
		if($eligres['status']=='fail')
		{
			
			$paramsaarry['process_status'] = $eligres['process_status'];
			$paramsaarry['process_message'] = $eligres['process_message'];			
			$errorarry = $this->getError('balancepresentonfinancetable');			
			$paramsaarry['error_code'] 		= $errorarry['error_code'];
			$paramsaarry['error_message'] = $errorarry['error_message'];			
			$this->updateprocessstatbcam($paramsaarry);
			
		}elseif($eligres['status']=='pass')
		{	
			$eligres=null;
			$eligres = $this->checkepaymentapportioningEntry($parentid,$discampaignid,$version);
			
			if($eligres['status']=='fail')
			{				
				$paramsaarry['process_status'] = $eligres['process_status'];
				$paramsaarry['process_message'] = $eligres['process_message'];			
				$errorarry = $this->getError('paymentapportioningdataissue');			
				$paramsaarry['error_code'] 		= $errorarry['error_code'];
				$paramsaarry['error_message'] = $errorarry['error_message'];				
				$this->updateprocessstatbcam($paramsaarry);
				
			}elseif($eligres['status']=='pass')
			{
				
				
				$eligres=null;
				$eligres = $this->checkbackend_campaign_addition_fcv_detailsEntry($parentid,$version);
				if($eligres['status']=='fail')
				{				
					$paramsaarry['process_status'] = $eligres['process_status'];
					$paramsaarry['process_message'] = $eligres['process_message'];			
					$errorarry = $this->getError('fcvalreadygivenbyprocess');			
					$paramsaarry['error_code'] 		= $errorarry['error_code'];
					$paramsaarry['error_message'] = $errorarry['error_message'];				
					$this->updateprocessstatbcam($paramsaarry);
					
				}elseif($eligres['status']=='pass')
				{
					$VersionApprovalStatus=null;
					$VersionApprovalStatus = $this->checkApprovalStatusOfVersion($parentid,$version);
					$version_status = $VersionApprovalStatus['version_status'];
					
					if($VersionApprovalStatus['status']=='fail')
					{
						$paramsaarry['process_status'] = $VersionApprovalStatus['process_status'];
						$paramsaarry['process_message'] = $VersionApprovalStatus['process_message'];			
						$errorarry = $this->getError('versionapprovalstatus');			
						$paramsaarry['error_code'] 		= $errorarry['error_code'];
						$paramsaarry['error_message'] = $errorarry['error_message'];				
						$this->updateprocessstatbcam($paramsaarry);
						
					}elseif($VersionApprovalStatus['status']=='pass')
					{					
						// now we don't have issue on finance as well as apportioning so we will make intry into apportioning table 
						$eligres=null;
						$dataarry['version_status']=$version_status;												
						$eligres = $this->InsertionIntoPaymentTables($dataarry);
						$eligres = $this->insertIntoBannerTables($dataarry);						
						$paramsaarry['process_status'] = $eligres['process_status'];
						$paramsaarry['process_message'] = $eligres['process_message'];			
						$errorarry = $this->getError('sucessful');
						$paramsaarry['error_code'] 		= $errorarry['error_code'];
						$paramsaarry['error_message'] = $errorarry['error_message'];
						$this->updateprocessstatbcam($paramsaarry);
					}
				}				
			}		
		}
		
	}
	
	function displayresultset($tablename,$result)
	{
		echo "<pre> <h3> ".$tablename." </h3>";
		
		if(mysql_num_rows($result)>0)
		{
			echo "<table border='1px'>";
				$count=0;
				while($row=mysql_fetch_assoc($result))
				{
					$column = array_keys($row);
					if($count==0)
					{
						echo "<tr>";
						for($i=0; $i<sizeof($column); $i++)
						{
							echo "<th>".$column[$i]."</th>";
						}
						echo "</tr>";
						$count++;
					}
					echo "<tr>";
					for($i=0; $i<sizeof($column); $i++)
					{
						echo "<td >".$row[$column[$i]]."</td>";
					}
					echo "</tr>";
				}
				echo "</table>";
		}
		
	}
	
	function todaysprocessedddata()
	{
		$sql = "select parentid,if(fcvamount=0,'Unapproved','Approved') as Approvalstatus from backend_campaign_addition_fcv_details where updatedon>'2017-10-15 00:36:44'";
		$result = parent::execQuery($sql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		$this->displayresultset(' backend_campaign_addition_fcv_details ',$result);
		
		
	}
	
	function addbanners()
	{
		$sql = "select * from backend_banner_addition_master where done_flag=0 ";
		$res  = parent::execQuery($sql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		if(mysql_num_rows($res))
		{
			while($row=mysql_fetch_assoc($res))
			{
				
				$contract_parentid = $row['parentid'];
				$contract_version = $row['version'];
				 
				if(DEBUG_MODE)
				{
					echo '<br><b>row</b>'; print_r($row);
					echo '<br><b>contract_parentid:</b>'.$contract_parentid;					
					echo '<br><b>contract_version:</b>'.$contract_version;
				}
				 
				$this->updatedoneflagbbam($contract_parentid,$contract_version,9);				
				$this->insertIntoBannerTables($row);
				$this->updatedoneflagbbam($contract_parentid,$contract_version,1);				
			}
			
			$result['message']='Contracts got processed';
		}else
		{
			$result['message']='No contract found to process';
		}
		return $result;
	}
	
	function addcampaigns()
	{
	
	
		$sql = "select * from backend_campaign_addition_master where done_flag=0 ";
		$resbkndcmpgnaddnmaster  = parent::execQuery($sql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		if(mysql_num_rows($resbkndcmpgnaddnmaster))
		{
			while($row=mysql_fetch_assoc($resbkndcmpgnaddnmaster))
			{
				
				$contract_parentid = $row['parentid'];
				$contract_version = $row['version'];
				 
				if(DEBUG_MODE)
				{
					echo '<br><b>row</b>'; print_r($row);
					echo '<br><b>contract_parentid:</b>'.$contract_parentid;					
					echo '<br><b>contract_version:</b>'.$contract_version;
				}
				 
				 $this->updatedoneflagbcam($contract_parentid,$contract_version,99);
				 $this->campaignprocessing($row);
				 $this->updatedoneflagbcam($contract_parentid,$contract_version,1);
				 $this->updatebackend_campaign_addition_master_archive($contract_parentid,$contract_version);				 
			}
			
			$result['message']='Contracts got processed';
		}else
		{
			$result['message']='No contract found to process';
		}
			 
		return $result;
		/*
		 
		 Tables to be used
1)payment_apportioning
    => entry of new campaigns with respective budgets where parentid,version,entry_date,source,isdeleted,disruption_flag will remain same as main parentid
    => Duration as needed and balance will be zero.

2)campaign_dealclose_budget
    => entry of new campaigns with respective budgets where parentid,version,entry_date will remain same as main parentid.
    => system_budget,discount_budget,contract_amount,accrual_amount,service_amount will be same as respective budgets.

3)contract_dealclose_budget
    => update summation of new campaigns budget in discount_budget for respective parentid and version
    => and need to run following query to update other columns
        system_budget      = net_budget + premium_budget  + discount_budget
        contract_amount = net_budget + premium_budget  + discount_budget
        service_amount   = net_budget + premium_budget  + discount_budget

4)If contract is approved
    => entry of new campaigns with respective budgets where budget,balance,discount_budget,discount_balance will be new budgets
        and bid_perday, bid_perday,discount_bid_per_day, daily_threshold will be budget/duration,
        weekly_threshold = bid_perday*7, monthly_threshold = bid_perday*30,
        campaign_value = bid_perday*365,
        active_flag=1, active_campaign =1,
        start_date will same for which new entries are made,
        end_date has to be set as per service,      
        and version will be the version for which new entries are made.
        remaining values will be same as other campaigns of that contract.

5)If not approved
    =>  Make entry for new campaigns with respective campaignid,campaign_value,budget,duration,version
         and remaining values will be same as other campaigns of that contract.

		 
		 */
		
	}
	
	
	
	function viewdata()
	{		
		$sql = "select * from tbl_backendprocess_parentid where done_flag=0";
		$res= parent::execQuery($sql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b> mysql_num_rows :</b>'.mysql_num_rows($res);
			echo '<br><b>Error:</b>'.$this->mysql_error;
			
		}
		
		
	}
	
	function process()
	{		

		
		$sql = "";
		parent::execQuery($sql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
	}
	
}



?>
