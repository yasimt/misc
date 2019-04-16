<?php

class procclass extends DB
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
	var  $version		= null;
	var  $sys_regfee_budget	= null;
	

	var  $catsearch	= null;
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	
	
	
	#var	 $optvalset = array('ALL','ZONE','NAME','PIN','DIST');
	

	function __construct($params)
	{	
		//$this->isAuthorise();	
		$this->params = $params;		
		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='data_city missing';
			echo json_encode($errorarray); exit;
		}
		
		
		if(isset($this->params['instid']) && trim($this->params['instid']) != "" && $this->params['instid'] != null)
		{
			$this->setServers();
			$this->initializeInstrumentDetails();
			
		}else
		{
			
			if(trim($this->params['parentid']) != "")
			{
				$this->parentid  = $this->params['parentid']; //initialize paretnid
			}else
			{
				$errorarray['errormsg']='parentid missing';
				echo json_encode($errorarray); exit;
			}
			

			if(trim($this->params['version']) != "")
			{
				$this->version  = $this->params['version']; //initialize paretnid
			}else
			{
				$errorarray['errormsg']='version missing';
				echo json_encode($errorarray); exit;
			}
			
			$this->setServers();
			
		}
		
		 $this->setdocid();
		
	}
	
	
	function setdocid()
	{
		
		$sql = "select * from tbl_id_generator where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIro);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
		
		if(mysql_num_rows($result))
		{
			$resarry= mysql_fetch_assoc($result);
			$this->docid=$resarry['docid'];
		}
		
	}
	function isAuthorise()
	{
		if(strtolower($this->params['action'])=='upcatlingfrmbid')
		{
			return 1;
		}
		
		if( !in_array($_SERVER['REMOTE_ADDR'], array('172.29.87.117','172.29.87.127','172.29.87.12') ) )
		{
			$unauterr= array_merge($_SERVER,$_SESSION,$this->params);			
			$unauterrstr = "<pre><br>" . print_r($unauterr, true) . "<br></pre>";			
			mail('prameshjha@justdial.com','Unauthorise access of jdbox/proc.php',$unauterrstr);
			$this->printerror(404," You are not Authorised to access this URL");
		}
		
	}
	function initializeInstrumentDetails()
	{
		
		$sqlpayment_instrument_summary = "select * from payment_instrument_summary where instrumentId='".$this->params['instid']."' ";
		$respayment_instrument_summary = parent::execQuery($sqlpayment_instrument_summary, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sqlpayment_instrument_summary;
			echo '<br><b>biddingDetailsCount:</b>'; print_r($respayment_instrument_summary);
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		if(mysql_num_rows($respayment_instrument_summary))
		{
			while($row=mysql_fetch_assoc($respayment_instrument_summary))
			{
				$this->parentid = $this->params['parentid'] = $row['parentid'];
				$this->version  = $this->params['version']  = $row['version'];
			}
		}else
		{
			$this->printerror(101,"No details found in payment_instrument_summary for instrumentId='".$this->params['instid']."'");
		}
		
		if(DEBUG_MODE)
		{
			
			echo '<br><b>$this->params</b>'; print_r($this->params);
			
		}
		
	}	
	
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		//$this->dbConDjds  		= $db[$data_city]['d_jds']['master'];
		//$this->dbConDjds_slave	= $db[$data_city]['d_jds']['slave'];
		//$this->dbConIro_slave	= $db[$data_city]['iro']['slave'];
		$this->dbConIdc   		= $db[$data_city]['idc']['master'];
		$this->finance   		= $db[$data_city]['fin']['master'];
		//$this->tme_jds   		= $db[$data_city]['tme_jds']['master'];
		$this->db_budgeting   	= $db[$data_city]['db_budgeting']['master'];
		$this->conn_dcdash 		= $db['dcdash'];


		//echo '<pre>';print_r($db[$data_city]['fin']['master']);
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

	function biddingDetailsCount()
	{
		$result= array();
		$sqltbl_bidding_details = "select campaignid,count(campaignid) as cnt  from tbl_bidding_details where parentid='".$this->parentid."' and version ='".$this->version."' group by campaignid";
		$ressqltbl_bidding_details = parent::execQuery($sqltbl_bidding_details, $this->finance);

		if(mysql_num_rows($ressqltbl_bidding_details))
		{
			while($row=mysql_fetch_assoc($ressqltbl_bidding_details))
			{
				$result[$row['campaignid']]=$row['cnt'];
			}
		}
		return $result;
		
	}

	function financeVersionChecking()
	{
		$sqlfinvercheck = "select * from tbl_companymaster_finance where parentid='".$this->parentid."' and version ='".$this->version."'";
		$resfinvercheck = parent::execQuery($sqlfinvercheck, $this->finance);

		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sqlfinvercheck;			
			echo '<br><b>Num Rows:</b>'.mysql_num_rows($resfinvercheck);

			
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
			
		if(mysql_num_rows($resfinvercheck)==0)
		{
			$this->printerror(11,"No data in tbl_companymaster_finance for parentid='".$this->parentid."' and version ='".$this->version."'");			
		}
	}
	
	
	function getpayment_apportioningDetails()
	{
		$result= array();
		$sqlpayment_apportioning = "select campaignid,budget,balance from payment_apportioning where parentid='".$this->parentid."' and version ='".$this->version."'";
		$respayment_apportioning = parent::execQuery($sqlpayment_apportioning, $this->finance);

		if(mysql_num_rows($respayment_apportioning))
		{
			while($row=mysql_fetch_assoc($respayment_apportioning))
			{
				$result[$row['campaignid']]['budget']=$row['budget'];
				$result[$row['campaignid']]['balance']=$row['balance'];
			}
		}
		return $result;
		
	}

	function bidDetExpToBidDet()
	{	
		$this->financeVersionChecking();
		
		$sqltbl_bidding_details = "insert ignore into tbl_bidding_details (parentid,docid,version,campaignid,catid,national_catid,pincode,position_flag,inventory,bidvalue,callcount,duration,sys_budget,actual_budget,bidperday,lcf,hcf,data_city,physical_pincode,latitude,longitude,updatedby,updatedon)
		select parentid,docid,version,campaignid,catid,national_catid,pincode,position_flag,inventory,bidvalue,callcount,duration,sys_budget,actual_budget,bidperday,lcf,hcf,data_city,physical_pincode,latitude,longitude,updatedby,updatedon from tbl_bidding_details_expired where parentid='".$this->parentid."' and version ='".$this->version."'";
		$restbl_bidding_details = parent::execQuery($sqltbl_bidding_details, $this->finance);

		$countarr= $this->biddingDetailsCount();
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sqltbl_bidding_details;
			echo '<br><b>biddingDetailsCount:</b>'; print_r($countarr);
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}

		
		if($restbl_bidding_details)
		{
			$array['error_code']=0;
			$array['CampCatcount']=$countarr;
			$array['message']='Sucess';
		}
		else
		{
			$array['error_code']=0;
			$array['message']='Unsucess';
		}
		return $array;
	}

	function bidDetArcvToBidDet()
	{	
		$this->financeVersionChecking();
		
		$sqltbl_bidding_details = "insert ignore into tbl_bidding_details (parentid,docid,version,campaignid,catid,national_catid,pincode,position_flag,inventory,bidvalue,callcount,duration,sys_budget,actual_budget,bidperday,lcf,hcf,data_city,physical_pincode,latitude,longitude,updatedby,updatedon)
		select parentid,docid,version,campaignid,catid,national_catid,pincode,position_flag,inventory,bidvalue,callcount,duration,sys_budget,actual_budget,bidperday,lcf,hcf,data_city,physical_pincode,latitude,longitude,updatedby,updatedon from tbl_bidding_details_archive where parentid='".$this->parentid."' and version ='".$this->version."'";
		$restbl_bidding_details = parent::execQuery($sqltbl_bidding_details, $this->finance);


		$countarr= $this->biddingDetailsCount();
		$array['CampCatcount']=$countarr;
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sqltbl_bidding_details;
			echo '<br><b>biddingDetailsCount:</b>'; print_r($countarr);
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}

		
		if($restbl_bidding_details)
		{
			$array['error_code']=0;
			$array['CampCatcount']=$countarr;			
			$array['message']='Sucess';
			
		}
		else
		{
			$array['error_code']=0;
			$array['message']='Unsucess';
		}
		return $array;
	}
	
	function updtidcbantolcl()
	{
		$sql= "select parentid,budget,update_date,cat_name,catid,tenure,start_date,end_date,bid_per_day,variable_budget,campaign_name,campaign_type,1 as iscalculated,inventory,national_catid,parentname,banner_camp,selectedCities from tbl_catspon where parentid='".$this->parentid."' ";
		
		$res = parent::execQuery($sql, $this->dbConIdc);

		if(DEBUG_MODE)
		{
			echo '<br><b>sql:</b>'.$sql;
			echo '<br><b>mysql_num_rows:</b>'.mysql_num_rows($res);
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}

		if(mysql_num_rows($res))
		{
			$insertvalues='';
			while($row=mysql_fetch_assoc($res))
			{				
				$insertvalues .= "('".$row['parentid']."','".$row['budget']."','".$row['update_date']."','".$row['cat_name']."','".$row['catid']."','".$row['tenure']."','".$row['start_date']."','".$row['end_date']."','".$row['bid_per_day']."','".$row['variable_budget']."','".$row['campaign_name']."','".$row['campaign_type']."','".$row['iscalculated']."','".$row['inventory']."','".$row['national_catid']."','".$row['parentname']."','".$row['banner_camp']."','".$row['selectedCities']."'),";
			}
			
			$insertvalues = trim($insertvalues,',');
			
			$sql= "insert ignore into d_jds.tbl_catspon ( parentid,budget,update_date,cat_name,catid,tenure,start_date,end_date,bid_per_day,variable_budget,campaign_name,campaign_type,iscalculated,inventory,national_catid,parentname,banner_camp,selectedCities ) values  ".$insertvalues;
			
			parent::execQuery($sql, $this->dbConIro);
						
			if(DEBUG_MODE)
			{
				echo '<br><b>sql:</b>'.$sql;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
		}
		
		
		$sql= "select parentid,budget,update_date,cat_name,catid,tenure,start_date,end_date,bid_per_day,variable_budget,campaign_name,campaign_type,1 as iscalculated,inventory,national_catid,parentname,banner_camp,selectedCities from tbl_comp_banner where parentid='".$this->parentid."' ";
		
		$res = parent::execQuery($sql, $this->dbConIdc);

		if(DEBUG_MODE)
		{
			echo '<br><b>sql:</b>'.$sql;
			echo '<br><b>mysql_num_rows:</b>'.mysql_num_rows($res);
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}

		if(mysql_num_rows($res))
		{
			$insertvalues='';
			while($row=mysql_fetch_assoc($res))
			{				
				$insertvalues .= "('".$row['parentid']."','".$row['budget']."','".$row['update_date']."','".$row['cat_name']."','".$row['catid']."','".$row['tenure']."','".$row['start_date']."','".$row['end_date']."','".$row['bid_per_day']."','".$row['variable_budget']."','".$row['campaign_name']."','".$row['campaign_type']."','".$row['iscalculated']."','".$row['inventory']."','".$row['national_catid']."','".$row['parentname']."','".$row['banner_camp']."','".$row['selectedCities']."'),";
			}
			
			$insertvalues = trim($insertvalues,',');
			
			$sql= "insert ignore into d_jds.tbl_comp_banner ( parentid,budget,update_date,cat_name,catid,tenure,start_date,end_date,bid_per_day,variable_budget,campaign_name,campaign_type,iscalculated,inventory,national_catid,parentname,banner_camp,selectedCities ) values  ".$insertvalues;
			
			parent::execQuery($sql, $this->dbConIro);
						
			if(DEBUG_MODE)
			{
				echo '<br><b>sql:</b>'.$sql;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
		}
		
		$array['error_code']=0;
		$array['message']='Done';
		
		return $array;
	
	
	}
	

	function tbl_bidding_details_expired_packagecreationfrompdg()
	{	
		$payment_apportioning = $this->getpayment_apportioningDetails();
		$validpackageflag=0;
		foreach($payment_apportioning as $campid=>$campidarr)		
		{
			if ($campid==1)
			{
				$validpackageflag=1;	
			}
			
		}
		
		if($validpackageflag==0)
		{
			$array['error_code']=1;
			$array['message']='Version '.$this->version.' does not have package campaign';
			return $array;
		}
		
		$sqltbl_bidding_details_expired = "insert ignore into tbl_bidding_details_expired 							(parentid,docid,version,campaignid,catid,national_catid,pincode,position_flag,inventory,bidvalue,callcount,duration,sys_budget,actual_budget,bidperday, lcf,hcf,data_city,physical_pincode,latitude,longitude,updatedby,updatedon,backenduptdate,expiredon,remarks) 
		select parentid,docid,version,1 as campaignid,catid,national_catid,physical_pincode as pincode,100 as position_flag,0 as inventory,bidvalue,callcount,duration,sys_budget,actual_budget,bidperday,0 as lcf,0 as hcf,data_city,physical_pincode,latitude,longitude,updatedby,updatedon,backenduptdate,expiredon,'backend package creation' as remarks From tbl_bidding_details_expired where parentid='".$this->parentid."' and campaignid=2  and version ='".$this->version."' group by catid";		
		
		$restbl_bidding_details_expired = parent::execQuery($sqltbl_bidding_details_expired, $this->finance);		
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$restbl_bidding_details_expired;			
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}

		
		$array['error_code']=0;
		$array['message']='sucess';
		
		return $array;
	}
	
	function tbl_bidding_details_packagecreationfrompdg()
	{	
		$payment_apportioning = $this->getpayment_apportioningDetails();
		$validpackageflag=0;
		foreach($payment_apportioning as $campid=>$campidarr)		
		{
			if ($campid==1)
			{
				$validpackageflag=1;	
			}
			
		}
		
		if($validpackageflag==0)
		{
			$array['error_code']=1;
			$array['message']='Version '.$this->version.' does not have package campaign';
			return $array;
		}
		
		$sqltbl_bidding_details_expired = "insert ignore into tbl_bidding_details 							(parentid,docid,version,campaignid,catid,national_catid,pincode,position_flag,inventory,bidvalue,callcount,duration,sys_budget,actual_budget,bidperday, lcf,hcf,data_city,physical_pincode,latitude,longitude,updatedby,updatedon,backenduptdate) 
		select parentid,docid,version,1 as campaignid,catid,national_catid,physical_pincode as pincode,100 as position_flag,0 as inventory,bidvalue,callcount,duration,sys_budget,actual_budget,bidperday,0 as lcf,0 as hcf,data_city,physical_pincode,latitude,longitude,updatedby,updatedon,backenduptdate From tbl_bidding_details where parentid='".$this->parentid."' and campaignid=2  and version ='".$this->version."' group by catid";		
		
		$restbl_bidding_details_expired = parent::execQuery($sqltbl_bidding_details_expired, $this->finance);		
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$restbl_bidding_details_expired;			
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}

		
		$array['error_code']=0;
		$array['message']='sucess';
		
		return $array;
	}	

	function tbdetotblbded()
	{	
		
		$sel= "select * from tbl_bidding_details_expired where parentid='".$this->parentid."' and campaignid=2  and version ='".$this->version."'";
		$res_sel = parent::execQuery($sel, $this->finance);
		
		if(mysql_num_rows($res_sel))
		{
			$array['error_code']=1;
			$array['message']='Version '.$this->version.' have PDG campaign so cant proceed';
			return $array;
		}
		
		$sqltbl_bidding_details_expired = "insert ignore into tbl_bidd_det_exp_delete (parentid,docid,version,campaignid,catid,national_catid,pincode,position_flag,inventory,bidvalue,callcount,duration,sys_budget,actual_budget,bidperday, lcf,hcf,data_city,physical_pincode,latitude,longitude,updatedby,updatedon,backenduptdate,expiredon,remarks) 
		select parentid,docid,version,campaignid,catid,national_catid,pincode,position_flag,inventory,bidvalue,callcount,duration,sys_budget,actual_budget,bidperday,lcf,hcf,data_city,physical_pincode,latitude,longitude,updatedby,updatedon,backenduptdate,expiredon,remarks From tbl_bidding_details_expired where parentid='".$this->parentid."' and version ='".$this->version."' ";		
		
		$restbl_bidding_details_expired = parent::execQuery($sqltbl_bidding_details_expired, $this->finance);		
		
		
		$sqltbl_bidding_details_expired1 = "Delete From tbl_bidding_details_expired where parentid='".$this->parentid."' and version ='".$this->version."' and campaignid=1";		
		$restbl_bidding_details_expired = parent::execQuery($sqltbl_bidding_details_expired1, $this->finance);		
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sqltbl_bidding_details_expired;			
			echo '<br><b>DB Query:</b>'.$sqltbl_bidding_details_expired1;			
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		
		
		$array['error_code']=0;
		$array['message']='sucess';
		
		return $array;
	}
	
	
	function redealclose()
	{			
		
		$sel= "insert ignore into tbl_bidding_details_intermediate (parentid,version,catid,national_catid,pincode_list,cat_budget,updatedby,updatedon,backenduptdate)
		(select parentid,version,catid,national_catid,pincode_list,cat_budget,updatedby,updatedon,backenduptdate from tbl_bidding_details_intermediate_archive where parentid ='".$this->parentid."'  and version ='".$this->version."')";
		$res_sel = parent::execQuery($sel, $this->db_budgeting);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sel;			
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		$sel= "update tbl_bidding_details_summary set dealclosed_flag =0 where parentid='".$this->parentid."' and version ='".$this->version."' ";
		$res_sel = parent::execQuery($sel, $this->db_budgeting);
			
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sel;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		//$result = $this->dealcloseinvapicall();
		
		if(isset($result['results']['fail']) && is_array($result['results']['fail']) && count($result['results']['fail'])>0)
        {
			$array['error_code']=1;
			$array['message']='fail';
			$array['error_details']= $result;
		}else
		{
			$array['error_code']=0;
			$array['message']='sucess';
		}
		
		
		
		return $array;
	}
	
	
	function dealcloseinvapicall()
	{
	
		$curlobj = new CurlClass();
	
		$Inparray['parentid']	=	$this->parentid;
		$Inparray['version']	=	$this->version;
		$Inparray['data_city']	=	urldecode($this->data_city);			
		$Inparray['astatus']	=	1; 
		$Inparray['astate']		=	1;
		
		
		$configclassobj= new configclass();
		$urldetails= $configclassobj->get_url(urldecode($this->data_city));

		$curlurl=$urldetails['jdbox_service_url'].'invMgmt.php';			
		
		$curlobj->setOpt(CURLOPT_CONNECTTIMEOUT, 30);
		$curlobj->setOpt(CURLOPT_TIMEOUT, 900);
		$output = $curlobj->post($curlurl,$Inparray,1);
		$output_arr= json_decode($output,true);
		
		if(DEBUG_MODE)
		{
			echo "<br>curlurl".$curlurl;print_r($Inparray);
			echo '<br>output_arr'; print_r($output_arr);
		}
		
		return $output_arr;

	}
	
	
	
	function getFinanceData($campaignid)
	{
		$resultarr= array();
		
		$sql = "select * from tbl_companymaster_finance where parentid='".$this->parentid."' and campaignid in (".$campaignid.") ";
		$res = parent::execQuery($sql, $this->finance);		
		
		if(mysql_num_rows($res) >0 )
		{
			while($row= mysql_fetch_assoc($res))
			{
				$resultarr[$row['campaignid']]=$row;
			}
		}
		
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Num Rows:</b>'.mysql_num_rows($res);			
			echo '<br><b>resultarr:</b>'; print_r($resultarr);
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		return $resultarr;
			
	}
	
	
	function upcatlingfrmbid()
	{	
		
		$financeData = $this->getFinanceData('1,2');
		
		if( (isset($financeData['1']) && $financeData['1']['balance']>0 ) || (isset($financeData['2']) && $financeData['2']['balance']>0 )  )
		{		
			$sel= "select concat('/',group_concat(distinct catid separator '/,/'),'/') as catidlineage,count(distinct catid) as categorycount  from tbl_bidding_details where parentid='".$this->parentid."'";
			$res_sel = parent::execQuery($sel, $this->finance);
			
			if(DEBUG_MODE)
			{
				echo '<br><b>DB Query:</b>'.$sel;			
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
			if(mysql_num_rows($res_sel))
			{
				$row=mysql_fetch_assoc($res_sel)	;		
				if($row['categorycount']>0)
				{
					$catidlineage_nonpaid_sql='';
					if(isset($this->params['catidlineage_nonpaid']))
					{
						$catidlineage_nonpaid_sql=" ,catidlineage_nonpaid ='".$this->params['catidlineage_nonpaid']."'";
					}
					$sel= "update tbl_companymaster_extradetails set catidlineage ='".$row['catidlineage']."' ".$catidlineage_nonpaid_sql." where parentid='".$this->parentid."' ";
					
					$res_sel = parent::execQuery($sel, $this->dbConIro);
				}
			}
			
				
			if(DEBUG_MODE)
			{
				echo '<br><b>DB Query:</b>'.$sel;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
			$array['error_code']=0;
			$array['message']='sucess';
			
			$this->call_webapi();
		
		}else
		{
			$array['error_code']=1;
			$array['message']='not an active pdg or package';
		}
		
		return $array;
	}
	
	function call_webapi()
	{		
		$configclassobj= new configclass();		
		
		$urldetails= $configclassobj->get_url(urldecode($this->data_city));		
		$curlurl=$urldetails['url']."/web_services/curl_serverside.php";
		
		$curl_url	= $urldetails['url']."/web_services/curl_serverside.php?city_indicator=".$urldetails['city_indicator']."&data_city=".urlencode($this->data_city)."&parentid=".$this->parentid."&ucode=backend&validationcode=DEVLPRBKND&uname=backend&insta_activate=1";
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curl_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = curl_exec($ch);
		curl_close($ch);
		
		if(DEBUG_MODE)
		{
			echo "<br>curl_url".$curl_url;
			echo '<br>output'; print_r($output);
		}	
		return;
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
	
	function genview()
	{				
		$sql = "select * from tbl_companymaster_generalinfo  where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIro);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$this->displayresultset(' tbl_companymaster_generalinfo ',$result);
		
		$sql = "select * from tbl_companymaster_extradetails  where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIro);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$this->displayresultset(' tbl_companymaster_extradetails ',$result);
		
		$sql = "select * from tbl_companymaster_search where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIro);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$this->displayresultset(' tbl_companymaster_search ',$result);
		
		
		
		
		$sql = "select * from d_jds.tbl_company_consolidate  where parentid='".$this->parentid."' order by updatedOn";
		$result = parent::execQuery($sql, $this->dbConIro);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$this->displayresultset(' tbl_company_consolidate ',$result);
		
		

		$sql = "select *,group_concat(distinct catid) as catidlist,group_concat(distinct pincode) as pincodelist from tbl_fp_search where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIro);
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$this->displayresultset(' tbl_fp_search ',$result);

		$sql = "select *,group_concat(distinct catid) as catidlist,group_concat(distinct pincode) as pincodelist from tbl_package_search where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIro);
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$this->displayresultset(' tbl_package_search ',$result);
				

		$sql = "select *,group_concat(distinct catid) as catidlist,group_concat(distinct pincode) as pincodelist from tbl_nonpaid_search  where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIro);
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$this->displayresultset(' tbl_nonpaid_search ',$result);


		$sql = "select * from d_jds.tbl_catspon  where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIro);
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$this->displayresultset(' tbl_catspon ',$result);

		$sql = "select * from d_jds.tbl_comp_banner  where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIro);
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$this->displayresultset(' tbl_comp_banner ',$result);
		

		$sql = "select * from web_api_log where parentid='".$this->parentid."' order by id desc limit 10";
		$result = parent::execQuery($sql, $this->dbConIro);
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$this->displayresultset(' web_api_log ',$result);
		
		if(LIVE_APP)
		{
			$sql = "select * from db_iro.web_api_log  where parentid='".$this->parentid."' order by id desc limit 10";
			$result = parent::execQuery($sql, $this->conn_dcdash);		
			if(DEBUG_MODE)
			{
				echo '<br><b>DB Query:</b>'.$sql;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}		
			$this->displayresultset('17.103 db_iro web_api_log ',$result);
		}
		
		$sql = "select * from tbl_compcatarea_regen_log where parentid='".$this->parentid."' order by id limit 100";
		$result = parent::execQuery($sql, $this->finance);
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$this->displayresultset(' tbl_compcatarea_regen_log ',$result);
		
		$sql = "select * from catspon_banner_rotation where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->finance);
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$this->displayresultset(' catspon_banner_rotation ',$result);
		
				
	}
	
	function finview()
	{				
		$payment_apportioningsql = "select * from payment_apportioning where parentid='".$this->parentid."'  order by entry_date,version,campaignid ";
		$result = parent::execQuery($payment_apportioningsql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$payment_apportioningsql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
				
		$this->displayresultset(' payment_apportioning ',$result);
		
		
		$sql = "select sphinx_id,parentid,campaignid,campaign_value,budget,balance,bid_perday,net_budget,net_balance,delta_amount,extra_bidperday,start_date,end_date,duration,version,total_app_duration,total_app_amount,active_flag,active_campaign,searchcriteria,manual_override,data_city,cronexedate,multiplier from tbl_companymaster_finance where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
		
		$this->displayresultset(' tbl_companymaster_finance ',$result);
		
		
		$sql = "select sphinx_id,parentid,campaignid,campaign_value,budget,balance,bid_perday,net_budget,net_balance,delta_amount,start_date,end_date,duration,version,total_app_duration,total_app_amount,active_flag,active_campaign,searchcriteria,manual_override,data_city,cronexedate,multiplier from db_national_listing.tbl_companymaster_finance_national where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIdc);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
		
		$this->displayresultset(' tbl_companymaster_finance_national ',$result);
		
		
		
		$sql = "select parentid,campaignid,campaign_value,budget,duration,version,balance,data_city,original_creator,original_date,updatedBy,updatedOn,smartlisting_flag,exclusivelisting_tag,daily_threshold,company_deduction_amt,bid_type,searchcriteria,jdprime from tbl_companymaster_finance_shadow where parentid='".$this->parentid."' order by updatedon ";
		$result = parent::execQuery($sql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		$this->displayresultset(' tbl_companymaster_finance_shadow ',$result);
		

		$sql = "select parentid,instrumentType,paymentType,instrumentAmount,instrumentId,service_tax,tdsAmount,version,app_version,entry_doneby,entry_date,approvalStatus,entryModule,depositDate,clearanceDate,instrumentRecievedFlag,pan_no,tan_no from payment_instrument_summary where parentid='".$this->parentid."' order by entry_date ";
		$result = parent::execQuery($sql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		echo "<pre> <h3> payment_instrument_summary </h3>";
		$instrumentIdarray= array();
		
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
					
					array_push($instrumentIdarray,$row['instrumentId']);
				}
				
           echo "</table>";
		}
					
		
		if(count($instrumentIdarray))
		{
			$instrumentIdlist = "'".implode("','",$instrumentIdarray)."'";
			
			$sql = "select instrumentId,accountsRecievedFlag,accountsRecievedDate,bankSentFlag,bankSentDate,bankClearanceFlag,bankClearanceDate,accountsClearanceFlag,accountsClearanceDate,finalApprovalFlag,finalApprovalDate,finalApprovalDoneby,finalApprovalUCode from payment_clearance_details where instrumentId in (".$instrumentIdlist.")  order by finalApprovalDate ";
			$result = parent::execQuery($sql, $this->finance);
			
			if(DEBUG_MODE)
			{
				echo '<br><b>DB Query:</b>'.$sql;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}		
			
			$this->displayresultset(' payment_clearance_details ',$result);		
		}
		
		$sql = "select * from db_payment.genio_online_transactions where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIdc);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		$this->displayresultset(' genio_online_transactions ',$result);
		
		
		$sql = "select * from payment_snapshot where parentid='".$this->parentid."'  order by approval_date";
		$result = parent::execQuery($sql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		$this->displayresultset(' payment_snapshot ',$result);
		
					
		
		$sql = "select *,group_concat(distinct catid) as catidlist,group_concat(distinct pincode) as pincodelist from tbl_bidding_details where parentid='".$this->parentid."' group by campaignid,version";
		$result = parent::execQuery($sql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		$this->displayresultset(' tbl_bidding_details ',$result);
		
		
		
		$sql = "select *,group_concat(distinct catid) as catidlist,group_concat(distinct pincode) as pincodelist from tbl_bidding_details_expired where parentid='".$this->parentid."' group by campaignid,version";
		$result = parent::execQuery($sql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		$this->displayresultset(' tbl_bidding_details_expired ',$result);
		
		
		
		$sql = "select * from tbl_payment_type_dealclosed where parentid='".$this->parentid."'  order by inserted_time ";
		$result = parent::execQuery($sql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		$this->displayresultset(' tbl_payment_type_dealclosed ',$result);
		
		
		$sql = "select * from payment_dealclose_details where parentid='".$this->parentid."' ";		
		$result = parent::execQuery($sql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		$this->displayresultset(' payment_dealclose_details ',$result);
		

		$sql = "select * from campaign_multiplier where parentid='".$this->parentid."' ";		
		$result = parent::execQuery($sql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		$this->displayresultset(' campaign_multiplier ',$result);
		

		$sql = "select * from dependant_campaign_details where parentid='".$this->parentid."' ";		
		$result = parent::execQuery($sql, $this->finance);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		$this->displayresultset(' dependant_campaign_details ',$result);
						
		
		$sql = "select parentid,data_city,pincode,version,module,contact_details,category_list,pincode_list,sys_fp_budget,sys_package_budget,sys_regfee_budget,sys_total_budget,actual_fp_budget,actual_package_budget,actual_regfee_budget,actual_total_budget,dealclosed_flag,dealclosed_on,dealclosed_by,dealclosed_uname,updatedby,username,updatedon from tbl_bidding_details_summary where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->db_budgeting);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
				
		$this->displayresultset(' tbl_bidding_details_summary ',$result);
		
		
		$this->downselldetails();
		
		$this->geniolitedetails();
		
		$sql = "select * from tbl_carryfrwdversion_bd_populate where parentid='".$this->parentid."' ";		
		$result = parent::execQuery($sql, $this->db_budgeting);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		$this->displayresultset(' tbl_carryfrwdversion_bd_populate ',$result);
		
	
		
	}
	function getcatdet()
	{
		$sql = "select catid,category_name from d_jds.tbl_categorymaster_generalinfo where catid in ('".$this->catlist."') ";
		$result = parent::execQuery($sql, $this->dbConIro);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$this->displayresultset(' tbl_companymaster_search ',$result);
	}
	
	function omniview()
	{
		
		
		
		$sql = "select * from online_regis1.tbl_omni_details_consolidated where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIdc);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		
		$this->displayresultset(' online_regis1.tbl_omni_details_consolidated ',$result);
		
		$sql = "select * from online_regis1.omni_manual_process where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIdc);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
		
		$this->displayresultset(' online_regis1.omni_manual_process ',$result);
		
		

		$sql = "select * from omni_api_calls_log where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIdc);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
		
		$this->displayresultset(' online_regis_x omni_api_calls_log ',$result);
		
		
		
		$sql = "select * from tbl_omni_website_details where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIdc);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
		$this->displayresultset(' online_regis_x tbl_omni_website_details ',$result);
		
		
		$sql = "select * from tbl_omni_website_details_log  where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIdc);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
		$this->displayresultset(' online_regis_x tbl_omni_website_details_log ',$result);
		
		
		$sql = "select * from online_regis1.omni_website_creation_failure  where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIdc);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
		$this->displayresultset(' online_regis1 omni_website_creation_failure ',$result);
		
		
		
		$sql = "select * from tbl_omni_email_details where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIdc);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
		$this->displayresultset(' online_regis_x tbl_omni_email_details ',$result);


		
		$sql = "select * from online_regis1.omni_override_api_calls where parentid='".$this->parentid."' or docid='".$this->docid."'";
		$result = parent::execQuery($sql, $this->dbConIdc);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
		$this->displayresultset(' online_regis1 omni_override_api_calls ',$result);
		
		
		$sql = "select * from online_regis1.tbl_omni_website_audit_details where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIdc);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
		$this->displayresultset(' online_regis1 tbl_omni_website_audit_details ',$result);
		
		
		
		$sql = "select * from online_regis.tbl_domain_api_omni_team_log where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIdc);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
		$this->displayresultset(' online_regis tbl_domain_api_omni_team_log ',$result);
		

		$sql = "select * from tbl_omni_mapping  where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIro);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
		$this->displayresultset(' db_iro tbl_omni_mapping ',$result);
		
		
		
		$sql = "select * from tbl_omnidomain_storeid_details  where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIro);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
		
		$this->displayresultset('  db_iro tbl_omnidomain_storeid_details ',$result);
		
		$sql = "select * from tbl_omni_domain_booking_log  where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIro);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
		
		$this->displayresultset('  db_iro tbl_omni_domain_booking_log ',$result);
		
		
		$sql = "select * from online_regis1.tbl_template_mapping_api  where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIdc);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
		$this->displayresultset('17.233 online_regis1 tbl_template_mapping_api ',$result);
		
		if(LIVE_APP)
		{
			$sql = "select * from online_regis1.tbl_template_mapping_api  where parentid='".$this->parentid."' ";
			$result = parent::execQuery($sql, $this->conn_dcdash);		
			if(DEBUG_MODE)
			{
				echo '<br><b>DB Query:</b>'.$sql;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}		
			$this->displayresultset('17.103 online_regis1 tbl_template_mapping_api ',$result);
		}
		
		$sql = "select * from online_regis1.domain_mapping_failure_rerun where docid='".$this->docid."'";
		$result = parent::execQuery($sql, $this->dbConIdc);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}		
		$this->displayresultset(' online_regis1 domain_mapping_failure_rerun ',$result);
		

		echo "<br><br><br>";
		
		$this->finview();
		
	}
	
	
	function downselldetails()
	{
		$sql = "select * from online_regis.downsell_trn where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIdc);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$this->displayresultset(' online_regis downsell_trn ',$result);
		
		$sql = "select * from upsell_downsell_details where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->finance);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$this->displayresultset(' upsell_downsell_details ',$result);
		
		
	}
	
	function geniolitedetails()
	{
		$sql = "select * from online_regis1.tbl_selfsignup_contracts where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIdc);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$this->displayresultset(' online_regis1 tbl_selfsignup_contracts ',$result);
		
		
		$sql = "select * from tbl_company_cart where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->dbConIdc);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$this->displayresultset('online_regis  tbl_company_cart ',$result);
		
		
	}
	
	function budgetview()
	{
		
		$this->finview();
		
		$sql = "select * from tbl_bidding_details_summary where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->db_budgeting);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}				
		$this->displayresultset(' tbl_bidding_details_summary ',$result);
		
		$sql = "select * from tbl_bidding_details_budgetjson where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->db_budgeting);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}				
		$this->displayresultset(' tbl_bidding_details_budgetjson ',$result);

		$sql = "select * from tbl_bidding_details_intermediate where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->db_budgeting);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}				
		$this->displayresultset(' tbl_bidding_details_intermediate ',$result);

		$sql = "select * from tbl_bidding_details_intermediate_archive where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->db_budgeting);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}				
		$this->displayresultset(' tbl_bidding_details_intermediate_archive ',$result);

		$sql = "select * from tbl_bidding_details_shadow where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->db_budgeting);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}				
		$this->displayresultset(' tbl_bidding_details_shadow ',$result);

		$sql = "select * from tbl_bidding_details_shadow_archive where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->db_budgeting);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}				
		$this->displayresultset(' tbl_bidding_details_shadow_archive ',$result);
		
		$sql = "select * from tbl_bidding_details_shadow_archive_approved where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->db_budgeting);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}				
		$this->displayresultset(' tbl_bidding_details_shadow_archive_approved ',$result);
		
		
		$sql = "select * from tbl_bidding_details_shadow_archive_historical where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->db_budgeting);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}				
		$this->displayresultset(' tbl_bidding_details_shadow_archive_historical ',$result);		
		
		$sql = "select * from tbl_cs_inventory_release_log where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->finance);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}				
		$this->displayresultset(' tbl_cs_inventory_release_log ',$result);
		
		
		$sql = "select * from tbl_expiredContract_inventory_release_log where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->finance);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}				
		$this->displayresultset(' tbl_expiredContract_inventory_release_log ',$result);
		
		
		$sql = "select * from tbl_invloss_renewbudget_log where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->db_budgeting);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}				
		$this->displayresultset(' tbl_invloss_renewbudget_log ',$result);


		$sql = "select * from tbl_invloss_renewbudget_api_log where parentid='".$this->parentid."' ";
		$result = parent::execQuery($sql, $this->db_budgeting);		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}				
		$this->displayresultset(' tbl_invloss_renewbudget_api_log ',$result);
		
		
	}
	
}



?>
