<?php

class contractpaymentserviceclass extends DB
{
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	
	
	var  $parentid		= null;
	//var  $version		= null;
	
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	
	

	function __construct($params)
	{		
		$this->params = $params;

		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='data_city missing';
			echo json_encode($errorarray); exit;
		}

		if($params['action']=='updatepaymentdetailsdealclose')
		{
			if(trim($this->params['instrumentid']) != "")
			{
				$this->instrumentid  = $this->params['instrumentid']; //initialize paretnid
			}else
			{
				$errorarray['errormsg']='instrumentid missing';
				echo json_encode($errorarray); exit;
			}

		}

		if($params['action']=='updatepaymentdetailsapproval')
		{
			if(trim($this->params['instrumentid']) != "" && $this->params['instrumentid'] != null)
			{
				$this->instrumentid  = $this->params['instrumentid']; //initialize duration
			}else
			{
				$errorarray['errormsg']='instrumentid missing';
				echo json_encode($errorarray); exit;
			}

		}
		if($params['action']=='deleteinstrument')
		{
			if(trim($this->params['instrumentid']) != "" && $this->params['instrumentid'] != null)
			{
				$this->instrumentid  = $this->params['instrumentid']; //initialize duration
			}else
			{
				$errorarray['errormsg']='instrumentid missing';
				echo json_encode($errorarray); exit;
			}

		}

		if(trim($this->params['instrumentid']) != "")
		{
				$this->instrumentid  = $this->params['instrumentid']; //initialize paretnid
		}
		$this->setServers();
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');		
		$this->fin_master	= $db[$data_city]['fin']['master'];
		$this->fin_slave	= $db[$data_city]['fin']['master'];
		$this->fin_select	= $db[$data_city]['fin']['master']; // it will point to slave if there is no lag otherwise it will point to master		
		$this->conn_dcdash  = $db['dcdash'];
	}

		
	function updatepaymentdetailsdealclose()
	{
		$usersql='';

		$instrumentid 	= $this->params['instrumentid'];
		//$parentid	  	= $this->params['parentid']; 
		//$version	  	= $this->params['version']; 

		$payment_instrument_summary_sql = "select * from payment_instrument_summary where instrumentid='".$instrumentid."'";
		$pis_temp = parent::execQuery($payment_instrument_summary_sql, $this->fin_master);

		if(DEBUG_MODE)
		{
			echo '<pre><br><b>payment instrument summary sql</b>'.$payment_instrument_summary_sql;
			echo '<br><b>Result Set:</b>'.$pis_temp;
			echo '<br><b>Num Rows:</b>'.mysql_num_rows($pis_temp);
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		$pis_res = mysql_fetch_assoc($pis_temp);
		
		$payment_otherdetails_sql	= "select * from payment_otherdetails where parentid='".$pis_res['parentid']."' and version ='".$pis_res['version']."'";
		$pod_temp	= parent::execQuery($payment_otherdetails_sql, $this->fin_master);

		if(DEBUG_MODE)
		{
			echo '<br><b>payment_otherdetails sql</b>'.$payment_otherdetails_sql;
			echo '<br><b>Result Set:</b>'.$pod_temp;
			echo '<br><b>Num Rows:</b>'.mysql_num_rows($pod_temp);
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$pod_res = mysql_fetch_assoc($pod_temp);

		$payment_apportioning_sql	= "select * from payment_apportioning where parentid='".$pis_res['parentid']."' and version ='".$pis_res['version']."' AND budget!=balance";		
		$pa_res 	= parent::execQuery($payment_apportioning_sql, $this->fin_master);

		if(DEBUG_MODE)
		{
			echo '<br><b>payment_apportioning sql</b>'.$payment_apportioning_sql;
			echo '<br><b>Result Set:</b>'.$pa_res;
			echo '<br><b>Num Rows:</b>'.mysql_num_rows($pa_res);
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}

		//$pa_res = mysql_fetch_assoc($pa_temp);
		
		
		$payment_executives_details_sql	= "select * from payment_executives_details where instrumentid='".$instrumentid."'";
		$ped_temp	= parent::execQuery($payment_executives_details_sql, $this->fin_master);

		if(DEBUG_MODE)
		{
			echo '<br><b>payment_otherdetails sql</b>'.$payment_executives_details_sql;			
			echo '<br><b>Num Rows:</b>'.mysql_num_rows($ped_temp);
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		$ped_res = mysql_fetch_assoc($ped_temp);
		
		
		$mecode = trim($ped_res['mecode'])!=''?trim($ped_res['mecode']):trim($pod_res['mecode']);
		$meName = trim($ped_res['meName'])!=''?trim($ped_res['meName']):trim($pod_res['meName']);
		$tmecode = trim($ped_res['tmecode'])!=''?trim($ped_res['tmecode']):trim($pod_res['tmecode']);
		$tmeName = trim($ped_res['tmeName'])!=''?trim($ped_res['tmeName']):trim($pod_res['tmeName']); 
		
		$campaignidlist='';
		$dealclosebudget='';
		$campaignwisebudget='';
		$arrCampaign= array();
		$dealclose_date='';
		while($pa_array= mysql_fetch_assoc($pa_res))
		{
			array_push($arrCampaign,$pa_array['campaignId']);
			$campaignidlist		.= 	','.$pa_array['campaignId'];
			$dealclosebudget	= 	$dealclosebudget+floatval($pa_array['budget']);
			$campaignwisebudget	=	$campaignwisebudget.$pa_array['campaignId'].'-'.floatval($pa_array['budget']).',';
			$dealclose_date= $pa_array['entry_date'];
		}
		$campaignidlist = trim($campaignidlist,',');
		$campaignwisebudget= rtrim($campaignwisebudget,',');
		
		
		$payment_cheque_details_sql = "select * from payment_cheque_details where instrumentid='".$instrumentid."'";
		$pcd_temp = parent::execQuery($payment_cheque_details_sql, $this->fin_master);

		if(DEBUG_MODE)
		{
			echo '<pre><br><b>payment_cheque_details sql</b>'.$payment_cheque_details_sql;
			echo '<br><b>Result Set:</b>'.$pcd_temp;
			echo '<br><b>Num Rows:</b>'.mysql_num_rows($pcd_temp);
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		$pcd_res = mysql_fetch_assoc($pcd_temp);
		$campaignname = $this->getcampaignname($arrCampaign);
		
		
		if(DEBUG_MODE)
		{
			echo '<br><b>campaignname:</b>'.$campaignname;
			echo '<br><b>arrCampaign:</b>'; print_r($arrCampaign);
		}
		$sql = "insert into contract_payment_details set
				instrumentId='".$pis_res['instrumentId']."',
				parentid='".$pis_res['parentid']."',
				instrumentType='".addslashes(stripslashes($pis_res['instrumentType']))."',
				paymentType='".addslashes(stripslashes($pis_res['paymentType']))."',
				instrumentAmount='".$pis_res['instrumentAmount']."',
				campaignidlist 		='".$campaignidlist."',
				dealclosebudget 	='".$dealclosebudget."',
				campaignwisebudget	='".$campaignwisebudget."',				
				service_tax='".$pis_res['service_tax']."',
				tdsAmount='".$pis_res['tdsAmount']."',
				bannerregfee='".$pis_res['bannerregfee']."',
				disc_perc='".$pis_res['disc_perc']."',
				version='".$pis_res['version']."',
				app_version='".$pis_res['app_version']."',
				entry_doneby='".addslashes(stripslashes($pis_res['entry_doneby']))."',
				entry_date='".$pis_res['entry_date']."',
				dealclose_date='".$dealclose_date."',
				approvalStatus='".$pis_res['approvalStatus']."',
				entryModule='".$pis_res['entryModule']."',
				depositDate='".$pis_res['depositDate']."',
				clearanceDate='".$pis_res['clearanceDate']."',
				chargebackDate='".$pis_res['chargebackDate']."',
				chargeBackFlag='".$pis_res['chargeBackFlag']."',
				reconsilationDate='".$pis_res['reconsilationDate']."',
				data_city='".addslashes(stripslashes($pis_res['data_city']))."',
				againstBouncedInstrumentId='".$pis_res['againstBouncedInstrumentId']."',
				instrumentRecievedFlag='".$pis_res['instrumentRecievedFlag']."',
				pan_no='".$pis_res['pan_no']."',
				tan_no='".$pis_res['tan_no']."',
				origin_city='".addslashes(stripslashes($pis_res['origin_city']))."',
				deposit_location='".addslashes(stripslashes($pis_res['deposit_location']))."',
				salesMonth='".$pis_res['salesMonth']."',
				source='".addslashes(stripslashes($pis_res['source']))."',
				campaign_type='".$pis_res['campaign_type']."',
				multicity='".$pis_res['multicity']."',
				inst_contractForm='".$pis_res['inst_contractForm']."',
				sc_inst_contractForm='".$pis_res['sc_inst_contractForm']."',
				inst_ackForm='".$pis_res['inst_ackForm']."',
				sc_inst_ackForm='".$pis_res['sc_inst_ackForm']."',
				inst_addendum='".$pis_res['inst_addendum']."',
				sc_inst_addendum='".$pis_res['sc_inst_addendum']."',
				inst_ecsForm='".$pis_res['inst_ecsForm']."',
				sc_inst_ecsForm='".$pis_res['sc_inst_ecsForm']."',
				inst_nachForm='".$pis_res['inst_nachForm']."',
				sc_inst_nachForm='".$pis_res['sc_inst_nachForm']."',
				inst_siForm='".$pis_res['inst_siForm']."',
				sc_inst_siForm='".$pis_res['sc_inst_siForm']."',
				inst_vlcForm='".$pis_res['inst_vlcForm']."',
				sc_inst_vlcForm='".$pis_res['sc_inst_vlcForm']."',
				inst_sfLOA='".$pis_res['inst_sfLOA']."',
				is_restricted='".$pis_res['is_restricted']."',
				doc_edited_date='".$pis_res['doc_edited_date']."',
				doc_edited_by='".addslashes(stripslashes($pis_res['doc_edited_by']))."',
				offline_reason='".addslashes(stripslashes($pis_res['offline_reason']))."',
				exclusivelisting_tag='".$pis_res['exclusivelisting_tag']."',				
				companyname='".addslashes(stripslashes($pod_res['companyname']))."',
				skippay='".$pod_res['skippay']."',
				contractType='".addslashes(stripslashes($pod_res['contractType']))."',
				contractForm='".$pod_res['contractForm']."',
				listingType='".$pod_res['listingType']."',
				ecsflag='".$pod_res['ecsflag']."',
				ackForm='".$pod_res['ackForm']."',
				addendum='".$pod_res['addendum']."',
				siForm='".$pod_res['siForm']."',
				ecsForm='".$pod_res['ecsForm']."',
				billingForm='".$pod_res['billingForm']."',
				address_proof='".addslashes(stripslashes($pod_res['address_proof']))."',
				identity_proof='".addslashes(stripslashes($pod_res['identity_proof']))."',
				other_docs='".addslashes(stripslashes($pod_res['other_docs']))."',
				misc_docs='".addslashes(stripslashes($pod_res['misc_docs']))."',
				axisbankCheque='".$pod_res['axisbankCheque']."',
				rest_cat_docs='".$pod_res['rest_cat_docs']."',
				irocode='".$pod_res['irocode']."',
				tmecode ='".$tmecode."',
				tmeName ='".addslashes(stripslashes($tmeName))."',
				tme_allocid='".$pod_res['tme_allocid']."',
				mecode ='".$mecode."',
				meName ='".addslashes(stripslashes($meName))."',
				managercode='".$pod_res['managercode']."',
				peoncode='".$pod_res['peoncode']."',
				iroName ='".addslashes(stripslashes($pod_res['iroName']))."',
				managerName ='".addslashes(stripslashes($pod_res['managerName']))."',
				peonName ='".addslashes(stripslashes($pod_res['peonName']))."',
				owner='".addslashes(stripslashes($pod_res['owner']))."',
				claimant='".addslashes(stripslashes($pod_res['claimant']))."',
				webApprovalFlag='".$pod_res['webApprovalFlag']."',
				csApprovalFlag='".$pod_res['csApprovalFlag']."',
				csApprovalDoneBy='".addslashes(stripslashes($pod_res['csApprovalDoneBy']))."',
				csApprovalDoneDate='".$pod_res['csApprovalDoneDate']."',
				reseller_details='".addslashes(stripslashes($pod_res['reseller_details']))."',
				jdfos_flag='".$pod_res['jdfos_flag']."',
				cdata_city='".addslashes(stripslashes($pod_res['cdata_city']))."',
				company_addr='".addslashes(stripslashes($pod_res['company_addr']))."',
				invoice_businessname='".addslashes(stripslashes($pod_res['invoice_businessname']))."',
				invoice_cpersonname='".addslashes(stripslashes($pod_res['invoice_cpersonname']))."',
				invoice_cpersonnum='".addslashes(stripslashes($pod_res['invoice_cpersonnum']))."',
				contract_form_no='".addslashes(stripslashes($pod_res['contract_form_no']))."',				
				firttimeappr='".$pod_res['firttimeappr']."',
				ftadoneon='".$pod_res['ftadoneon']."',
				chequeNo='".$pcd_res['chequeNo']."',
				chequeDate='".$pcd_res['chequeDate']."',
				MICR='".$pcd_res['MICR']."',
				IFSC='".$pcd_res['IFSC']."',
				bankcity='".addslashes(stripslashes($pcd_res['bankcity']))."',
				bankBranch='".addslashes(stripslashes($pcd_res['bankBranch']))."',
				bankName='".addslashes(stripslashes($pcd_res['bankName']))."',
				location='".addslashes(stripslashes($pcd_res['location']))."',
				acType='".$pcd_res['acType']."',
				accountNo='".$pcd_res['accountNo']."',
				collectionDate='".$pcd_res['collectionDate']."',
				outStnCheque='".$pcd_res['outStnCheque']."',
				cheque_depositDate='".$pcd_res['depositDate']."',
				cheque_clearanceDate='".$pcd_res['clearanceDate']."',
				campaignname= '".$campaignname."'

				ON DUPLICATE KEY UPDATE
				
				parentid='".$pis_res['parentid']."',
				instrumentType='".addslashes(stripslashes($pis_res['instrumentType']))."',
				paymentType='".addslashes(stripslashes($pis_res['paymentType']))."',
				instrumentAmount='".$pis_res['instrumentAmount']."',
				campaignidlist 		='".$campaignidlist."',
				dealclosebudget 	='".$dealclosebudget."',
				campaignwisebudget	='".$campaignwisebudget."',				
				service_tax='".$pis_res['service_tax']."',
				tdsAmount='".$pis_res['tdsAmount']."',
				bannerregfee='".$pis_res['bannerregfee']."',
				disc_perc='".$pis_res['disc_perc']."',
				version='".$pis_res['version']."',
				app_version='".$pis_res['app_version']."',
				entry_doneby='".addslashes(stripslashes($pis_res['entry_doneby']))."',
				entry_date='".$pis_res['entry_date']."',
				dealclose_date='".$dealclose_date."',
				approvalStatus='".$pis_res['approvalStatus']."',
				entryModule='".$pis_res['entryModule']."',
				depositDate='".$pis_res['depositDate']."',
				clearanceDate='".$pis_res['clearanceDate']."',
				chargebackDate='".$pis_res['chargebackDate']."',
				chargeBackFlag='".$pis_res['chargeBackFlag']."',
				reconsilationDate='".$pis_res['reconsilationDate']."',
				data_city='".addslashes(stripslashes($pis_res['data_city']))."',
				againstBouncedInstrumentId='".$pis_res['againstBouncedInstrumentId']."',
				instrumentRecievedFlag='".$pis_res['instrumentRecievedFlag']."',
				pan_no='".$pis_res['pan_no']."',
				tan_no='".$pis_res['tan_no']."',
				origin_city='".addslashes(stripslashes($pis_res['origin_city']))."',
				deposit_location='".addslashes(stripslashes($pis_res['deposit_location']))."',
				salesMonth='".$pis_res['salesMonth']."',
				source='".addslashes(stripslashes($pis_res['source']))."',
				campaign_type='".$pis_res['campaign_type']."',
				multicity='".$pis_res['multicity']."',
				inst_contractForm='".$pis_res['inst_contractForm']."',
				sc_inst_contractForm='".$pis_res['sc_inst_contractForm']."',
				inst_ackForm='".$pis_res['inst_ackForm']."',
				sc_inst_ackForm='".$pis_res['sc_inst_ackForm']."',
				inst_addendum='".$pis_res['inst_addendum']."',
				sc_inst_addendum='".$pis_res['sc_inst_addendum']."',
				inst_ecsForm='".$pis_res['inst_ecsForm']."',
				sc_inst_ecsForm='".$pis_res['sc_inst_ecsForm']."',
				inst_nachForm='".$pis_res['inst_nachForm']."',
				sc_inst_nachForm='".$pis_res['sc_inst_nachForm']."',
				inst_siForm='".$pis_res['inst_siForm']."',
				sc_inst_siForm='".$pis_res['sc_inst_siForm']."',
				inst_vlcForm='".$pis_res['inst_vlcForm']."',
				sc_inst_vlcForm='".$pis_res['sc_inst_vlcForm']."',
				inst_sfLOA='".$pis_res['inst_sfLOA']."',
				is_restricted='".$pis_res['is_restricted']."',
				doc_edited_date='".$pis_res['doc_edited_date']."',
				doc_edited_by='".addslashes(stripslashes($pis_res['doc_edited_by']))."',
				offline_reason='".addslashes(stripslashes($pis_res['offline_reason']))."',
				exclusivelisting_tag='".$pis_res['exclusivelisting_tag']."',				
				companyname='".addslashes(stripslashes($pod_res['companyname']))."',
				skippay='".$pod_res['skippay']."',
				contractType='".addslashes(stripslashes($pod_res['contractType']))."',
				contractForm='".$pod_res['contractForm']."',
				listingType='".$pod_res['listingType']."',
				ecsflag='".$pod_res['ecsflag']."',
				ackForm='".$pod_res['ackForm']."',
				addendum='".$pod_res['addendum']."',
				siForm='".$pod_res['siForm']."',
				ecsForm='".$pod_res['ecsForm']."',
				billingForm='".$pod_res['billingForm']."',
				address_proof='".addslashes(stripslashes($pod_res['address_proof']))."',
				identity_proof='".addslashes(stripslashes($pod_res['identity_proof']))."',
				other_docs='".addslashes(stripslashes($pod_res['other_docs']))."',
				misc_docs='".addslashes(stripslashes($pod_res['misc_docs']))."',
				axisbankCheque='".$pod_res['axisbankCheque']."',
				rest_cat_docs='".$pod_res['rest_cat_docs']."',
				irocode='".$pod_res['irocode']."',
				tmecode ='".$tmecode."',
				tmeName ='".addslashes(stripslashes($tmeName))."',
				tme_allocid='".$pod_res['tme_allocid']."',
				mecode ='".$mecode."',
				meName ='".addslashes(stripslashes($meName))."',
				managercode='".$pod_res['managercode']."',
				peoncode='".$pod_res['peoncode']."',
				iroName ='".addslashes(stripslashes($pod_res['iroName']))."',
				managerName ='".addslashes(stripslashes($pod_res['managerName']))."',
				peonName ='".addslashes(stripslashes($pod_res['peonName']))."',
				owner='".addslashes(stripslashes($pod_res['owner']))."',
				claimant='".addslashes(stripslashes($pod_res['claimant']))."',
				webApprovalFlag='".$pod_res['webApprovalFlag']."',
				csApprovalFlag='".$pod_res['csApprovalFlag']."',
				csApprovalDoneBy='".addslashes(stripslashes($pod_res['csApprovalDoneBy']))."',
				csApprovalDoneDate='".$pod_res['csApprovalDoneDate']."',
				reseller_details='".addslashes(stripslashes($pod_res['reseller_details']))."',
				jdfos_flag='".$pod_res['jdfos_flag']."',
				cdata_city='".addslashes(stripslashes($pod_res['cdata_city']))."',
				company_addr='".addslashes(stripslashes($pod_res['company_addr']))."',
				invoice_businessname='".addslashes(stripslashes($pod_res['invoice_businessname']))."',
				invoice_cpersonname='".addslashes(stripslashes($pod_res['invoice_cpersonname']))."',
				invoice_cpersonnum='".addslashes(stripslashes($pod_res['invoice_cpersonnum']))."',
				contract_form_no='".addslashes(stripslashes($pod_res['contract_form_no']))."',				
				firttimeappr='".$pod_res['firttimeappr']."',
				ftadoneon='".$pod_res['ftadoneon']."',
				chequeNo='".$pcd_res['chequeNo']."',
				chequeDate='".$pcd_res['chequeDate']."',
				MICR='".$pcd_res['MICR']."',
				IFSC='".$pcd_res['IFSC']."',
				bankcity='".addslashes(stripslashes($pcd_res['bankcity']))."',
				bankBranch='".addslashes(stripslashes($pcd_res['bankBranch']))."',
				bankName='".addslashes(stripslashes($pcd_res['bankName']))."',
				location='".addslashes(stripslashes($pcd_res['location']))."',
				acType='".$pcd_res['acType']."',
				accountNo='".$pcd_res['accountNo']."',
				collectionDate='".$pcd_res['collectionDate']."',
				outStnCheque='".$pcd_res['outStnCheque']."',
				cheque_depositDate='".$pcd_res['depositDate']."',
				cheque_clearanceDate='".$pcd_res['clearanceDate']."',
				campaignname= '".$campaignname."'";
				//echo "<pre>".$sql;
				
				parent::execQuery($sql, $this->fin_master);

				if(DEBUG_MODE)
				{
					echo '<br><b>contract_payment_details sql</b>'.$sql;
					echo '<br><b>Error:</b>'.$this->mysql_error;
				}
				
				
				parent::execQuery($sql, $this->conn_dcdash);
				
				$resultarr['status']='successful';
				return $resultarr;
	}

	function approvalInstrumentChecking()
	{
		$contract_payment_details_sql="select parentid from contract_payment_details where instrumentid='".$this->instrumentid."'";
		//echo $pcd_sql;
		$contract_payment_details_res= parent::execQuery($contract_payment_details_sql, $this->fin_master);
		
		

		if(mysql_num_rows($contract_payment_details_res)==0)
		{
			$this->updatepaymentdetailsdealclose();// if instrument is called directly without dealclose then also it should be handled			
		}

		
			if(DEBUG_MODE)
			{
				echo '<pre><br><b>contract_payment_details_sql </b>'.$contract_payment_details_sql;
				echo '<pre><br><b>mysql_num_rows  </b>'.mysql_num_rows($contract_payment_details_res);
				echo '<br><b>Error:</b>'.$this->mysql_error;
				echo '<br><b>instrumentid:</b>'.$this->instrumentid;
			}
	}
	
	function updatepaymentdetailsapproval()
	{

		
		//$this->approvalInstrumentChecking();		
		$this->updatepaymentdetailsdealclose(); // since the columns are getting updated on approval so we update the table again $this->approvalInstrumentChecking();		
		

		$payment_instrument_summary_sql = "select approvalStatus from payment_instrument_summary where instrumentid='".$this->instrumentid."'";
		$pis_temp = parent::execQuery($payment_instrument_summary_sql, $this->fin_master);

		if(DEBUG_MODE)
		{
			echo '<pre><br><b>payment instrument summary sql</b>'.$payment_instrument_summary_sql;
			echo '<br><b>Result Set:</b>'.$pis_temp;
			echo '<br><b>Num Rows:</b>'.mysql_num_rows($pis_temp);
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		$pis_res = mysql_fetch_assoc($pis_temp);
		
		$pcd_sql="select finalApprovalDoneby,finalApprovalUCode,finalApprovalRemarks,finalApprovalFlag,finalApprovalDate from payment_clearance_details where instrumentid='".$this->instrumentid."'";
		
		$pcd_res= parent::execQuery($pcd_sql, $this->fin_master);
		if(mysql_num_rows($pcd_res))
		{
			$pcd_array= mysql_fetch_assoc($pcd_res);			
		 
			$sql="update contract_payment_details set
			approvalStatus='".$pis_res['approvalStatus']."',
			finalApprovalDoneby		='".addslashes(stripslashes($pcd_array['finalApprovalDoneby']))."',
			finalApprovalUCode		='".addslashes(stripslashes($pcd_array['finalApprovalUCode']))."',
			finalApprovalRemarks	='".addslashes(stripslashes($pcd_array['finalApprovalRemarks']))."',
			finalApprovalDate		='".$pcd_array['finalApprovalDate']."',
			finalApprovalFlag		='".$pcd_array['finalApprovalFlag']."'
			where 
			instrumentId			='".$this->instrumentid."'"	;							
			
			
			parent::execQuery($sql, $this->fin_master);
			if(DEBUG_MODE)
			{
				echo '<pre><br><b>payment_clearance_details sql</b>'.$pcd_sql;
				echo '<br><b>payment_clearance_details result array </b>'; print_r($pcd_array);
				echo '<br><b>Error:</b>sql- '.$sql;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
			parent::execQuery($sql, $this->conn_dcdash); 
		}
		
		$resultarr['status']='successful';		
		return $resultarr;
	}
	
	function deleteinstrument()
	{
		$cpd_sql="delete from contract_payment_details where instrumentid='".$this->instrumentid."'";		
		$pcd_res= parent::execQuery($cpd_sql, $this->fin_master);
		parent::execQuery($cpd_sql, $this->conn_dcdash);
		$resultarr['status']='successful';
		return $resultarr;
	}
	
	function getcampaignname($arrCampaign)
	{
		
		$campaign='';
		
			if (in_array("10",$arrCampaign))
			{
				$campaign = 'National Registration - Phone';    
			}
		   
			else if (in_array("2",$arrCampaign))
			{
				$campaign = 'Platinum/Diamond';    

			}
			else if (in_array("1",$arrCampaign))
			{
				$campaign = 'Package';
			}
			else if (in_array("4",$arrCampaign))
			{
				$campaign = 'SMS Leads';

			}
			else if (in_array("6",$arrCampaign))
			{
				$campaign = 'Powerlisting';

			}
			else if (in_array("13",$arrCampaign))
			{
				$campaign = 'Category Sponsorship';

			}
			else if (in_array("3",$arrCampaign))
			{
				$campaign = 'Web Search';

			}
			else if (in_array("8",$arrCampaign))
			{
				$campaign = 'Enhancements(Video/Logo/Catalog)';

			}
			else if (in_array("15",$arrCampaign))
			{
				$campaign = 'Category Text Banner';
			}
			else if (in_array("17",$arrCampaign))
			{
				$campaign = 'Hidden Contract';
			}
			else if (in_array("72",$arrCampaign))
			{
				$campaign = 'JD Omni';
			}
			else if (in_array("73",$arrCampaign))
			{
				$campaign = 'JD Omni';
			}
	
	return $campaign;
	}
}



?>
