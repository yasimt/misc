<?php
include_once(APP_PATH."library/define_virtualnumbers.php");
include_once(APP_PATH."common/virtual_api.php");
include_once(APP_PATH."library/common_api.php");
include_once(APP_PATH."business/class.restaurant.php");
include_once(APP_PATH."library/DialableNumberclass.php");
include_once(APP_PATH."business/vn_common_functions.php");
class Virtualnumber
{
	const TOTAL_REQUIRE_MAPPEDNO = 8; /* total mapped numbers tech info can store */
    const TOTAL_TECHINFO_MAPPENO = 8; /* total mapped numbers return form techinfo virtual for perticular virtual number*/
    const CURL_TIMEOUT = 10;
    private $parentid,$city_vn, $conn_iro, $conn_finance, $techinfo_conn,$log_path, $dndobj,$usercode,$area,$block_virtual_flag,$pri_number,$processName;
    private $pincode_flag;
    function __construct($parentid, $dbarr, $city_vn, $usercode,$processName,$dndobj=null)
	{
		if(trim($parentid) == '')
		{
			 die("parentid is blank");
		}
        if(!defined('APP_PATH'))
        {
            die("APP PATH not defined....");
        }
        if(trim($usercode)=='')
        {
            die("user code is blank");
        }
        if(trim($city_vn)=='')
        {
            die("City is blank....");
        }
        $this->log_path			= APP_PATH . 'logs/virtualNoLogs';
        $this->dndobj			= $dndobj;
        //echo $this->city_vn          = trim($city_vn);
        $this->processName		= $processName;
        
        $this->conn_iro         = new DB($dbarr['DB_IRO']);
        $this->conn_finance     = new DB($dbarr['FINANCE']);
        $this->conn_decs     	= new DB($dbarr['DB_DECS']);
        $this->conn_ecsbill     = new DB($dbarr['ECS_BILL']);
        $this->conn_idc         = new DB($dbarr['IDC']);
        
        $this->city_vn          = $this->get_data_city($parentid,$this->conn_iro,trim($city_vn));
        
        $this->techinfo_conn    = null;
        $this->usercode               = $usercode;
		$this->other_city_api = 0;      

        $this->kol_city_arr 	= array('HOOGHLY','HOWRAH','NORTH 24 PARGANAS','SOUTH 24 PARGANAS');
		$this->other_rem = array('POLLACHI');
        
        if(in_array(strtoupper($this->city_vn),$this->kol_city_arr))
        {
		$this->other_city_api = 1;	
        $this->city_vn = 'KOLKATA';
		}     
        if(in_array(strtoupper($this->city_vn),$this->other_rem))
        {
		$this->other_city_api = 1;	
        $this->city_vn = 'COIMBATORE';
		}
        $this->main_city_arr = array();
		$this->remote_city_arr = array();
		$this->cityStdCode_arr =  array();
		$this->reliance_main_city = array();
		$this->reliance_remote_city = array();
		$this->reliance_Ozonetel = array();
		
		
		if((in_array(strtoupper($this->city_vn),$this->reliance_main_city)) || (in_array(strtoupper($this->city_vn),$this->reliance_remote_city)))
		{
			if(APP_LIVE == 1)
			{
				if(defined(strtoupper($this->city_vn).'_RELIANCE_API_URL'))
				{
					if(in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
					{
					$port = '80';
					$socket = fSockOpen(constant(strtoupper($this->city_vn).'_RELIANCE_API_URL'), $port, $errorNo, $errorStr,3);	
					}
					else
					{
					$port = '8100';
					$socket = fSockOpen(constant(strtoupper($this->city_vn).'_RELIANCE_API_URL'), $port, $errorNo, $errorStr,3);
					}
					if ($socket)
					{
						$this->RcomConnectFlag = true;
					}
					else
					{
						$this->RcomConnectFlag = false;
					}
				}
				else
				{
					$this->RcomConnectFlag = true;
				}
			}
			else
			{
				$this->RcomConnectFlag = true;
			}
		}
		else
		{
			$port = '80';
			$socket = fSockOpen("172.29.66.229", $port, $errorNo, $errorStr,3);
			if ($socket)
			{
				$this->RcomConnectFlag = true;
			}
			else
			{
				$this->RcomConnectFlag = false;
			}
			
		}
		$this->initialized($parentid,$dbarr,$dndobj='null');
        $this->initializedtechinfo();
        $this->initializedJdfosurl();
        $this->insertion_time = date("Y-m-d H:i:s");
        $this->obj_rest		=	new restaurant($parentid);
		$this->compmaster_obj	= new companyMasterClass($this->conn_iro,"",$parentid);
		$this->pre_virtualnumber = $this->vn_check($parentid);
		$this->curlobj = new CurlClass();
	}
	
	function __destruct()
	{
		$this->post_virtualnumber = $this->vn_check($this->parentid);
		if($this->pre_virtualnumber != $this->post_virtualnumber)	
		{
			$curl_url_il = "http://".JDBOX_SERVICES_API."/instant_live.php";
			
			$param_array['parentid'] 		=	$this->parentid;
			$param_array['data_city'] 		=	$this->city_vn;
			$param_array['module'] 			=	'cs';
			$param_array['ucode'] 			=	$this->usercode;
			
			
			
			$instant_url =  $curl_url_il."?".http_build_query($param_array);
			$insert_instant = "INSERT INTO online_regis1.tbl_instant_live 
						SET 
						parentid	=	'".$this->parentid."',
						data_city	=	'".$this->city_vn."',
						url			=	'".$instant_url."',
						source		=	'cs vn',
						ucode		=	'".$this->usercode."',
						entry_date	=	now()";
			$res_qry = $this->execute_query($insert_instant,$this->conn_idc);
			
		}
		
	}
	
	function get_data_city($parentid,$conn_iro,$city_vn)
	{
		$sql_res="select data_city from tbl_id_generator WHERE parentid = '".$parentid."'";
		
		$res = $this->execute_query($sql_res,$conn_iro);
		
		$rows = mysql_fetch_assoc($res);
		
		if($res && mysql_num_rows($res)>0)
		{
			
			return $rows['data_city'];
		}
		else
		{
			return $city_vn;
		}		
	}	
	
	function vn_check($parentid){
		$temparr	= array();
		$fieldstr	= "pincode, virtualnumber";
		$where 		= "parentid = '".$this->parentid."'";
		$res_initialvalue_contract	= $this->compmaster_obj->getRow($fieldstr,"tbl_companymaster_generalinfo",$where);
		
		return $res_initialvalue_contract['data'][0]['virtualnumber'];
		//echo "<pre>";
		//print_r($res_initialvalue_contract['data'][0]['virtualnumber']);
	}


	function initialized($parentid)
    {
        $this->parentid                 =   $parentid;
        $this->paid_flag                = -1;
        $this->pri_number               = 0;
        $this->jdfos_url ='';
        $this->virtualno                = 0;
        $this->geninfo_vno              = 0;
        $this->vnEligibleCompaign		= false;
        $this->JDFOS_flag				= false;
        $this->jdfos_pid_chk_flag		= false;
        $this->pincode_flag             = false;
        $this->pincode					= 0;
        $this->pincode_flag             = false;
        $this->linkcontract_flag        = false;
        $this->linkcontracts            = array();
        $this->linkcontractsvalues      = array();
        $this->rootcontractid           = '';
        $this->mainrootcontractid       = '';
        $this->mappednumber_flag		= false;
        $this->blockVn_flag				= false;
        $this->block_vn_cat_flag		= false;
        $this->opt_in_category_flag		= false;
        $this->ineligible_cat_process	= '';
        $this->contact_arr              = array();
        $this->contractperson_arr		= array();
        $this->contacts_mode            = array('landline_display','mobile_display','tollfree','landline','mobile');
        $this->stdcode                  = '';
        $this->area                     = '';
        $this->top_mappednumber_withoutstd = array();
        $this->landline                 = array();
        $this->mobile                   = array();
        $this->tollfree                 = array();
        $this->finalmappednumbers       = array();
        $this->techinfo_array           = array();
        $this->remoteVnDbArr			= array();
		$this->quarantineFlag			= false;
		$this->quarantineFlag_expire    = false;
		$this->approval_flag 			= true;
		$this->pri_number               = 0;
		$this->techinfoPidStatus		= bindec(000);
		$this->obj_rest		=	new restaurant($parentid);
		$this->DialableNumberClass = new DialableNumberClass($this->compmaster_obj);
		$this->VnVerticalArr = array('32');
		$this->shopfront_flag = false;
		$this->block_tag_proceed_flag = false;
		$this->block_untag_proceed_flag = false;
		$this->vnNotAssignreason = '';
		$this->reason ='';
		$this->country_code= '91';
		$this->CountryStdCode ='';
		$this->oldexpired = false;
		$this->currentexpired = false;
		$this->expired_on_date = false;	
		if(in_array(strtoupper($this->city_vn),$this->main_city_arr))
		{
			$this->vn_length = '8';
		}else{
			$this->vn_length = '7';
		}
	}
	
	function initializedtechinfo()
    {
		if(APP_LIVE == 1)
		{
			//for live 
			//if(VN_TECHINFO_SERVER !='')
			//{
				if(in_array(strtoupper($this->city_vn),$this->main_city_arr))
				{
					if(in_array(strtoupper($this->city_vn),$this->reliance_main_city) && in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel)){ 
						$this->techinfo_url="http://".constant(strtoupper($this->city_vn ).'_RELIANCE_API_URL')."/JD_CALITEAPI/api.php?";
					}else if(in_array(strtoupper($this->city_vn),$this->reliance_main_city) && !in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel)) 
					{
						$this->techinfo_url="http://".constant(strtoupper($this->city_vn ).'_RELIANCE_API_URL')."/TECDRestService/";
					}
					else{
						$this->techinfo_url="http://".VN_TECHINFO_SERVER.":81/justdial/";
						$extra_str="Servere IP : ".VN_TECHINFO_SERVER;
						$this->logmsgvirtualno("Server IP has been read from text file",$this->log_path,$this->processName,$this->parentid,$extra_str);
					}
				}
				else if(in_array(strtoupper($this->city_vn),$this->remote_city_arr))
				{
					if(in_array(strtoupper($this->city_vn),$this->reliance_remote_city) && in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel)){ 
						$this->rcom_remote_url="http://".constant(strtoupper($this->city_vn ).'_RELIANCE_API_URL')."/JD_CALITEAPI/api.php?";
					}else if(in_array(strtoupper($this->city_vn),$this->reliance_remote_city) && !in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel)){ 
					$this->rcom_remote_url="http://".constant(strtoupper($this->city_vn ).'_RELIANCE_API_URL')."/TECDRestService/";
					}
					else{
						$this->rcom_remote_url = "";
					}
					$this->techinfo_url ="";	
				}
				else if(!in_array(strtoupper($this->city_vn),$this->remote_city_arr) && !in_array(strtoupper($this->city_vn),$this->main_city_arr))
				{
					$this->techinfo_url="http://172.29.66.229/justdial/";
				}
			/*}
			else
			{
				if(in_array(strtoupper($this->city_vn),$this->main_city_arr))
				{
					if(in_array(strtoupper($this->city_vn),$this->reliance_main_city)){ 
						$this->techinfo_url="http://".constant(strtoupper($this->city_vn ).'_RELIANCE_API_URL')."/TECDRestService/";
					}else{
						$this->techinfo_url="http://".constant(strtoupper($this->city_vn ).'_TECH_API_URL')."/justdial/";
						$extra_str="Default Server IP";
						$this->logmsgvirtualno("Server IP has been considered by default.",$this->log_path,$this->processName,$this->parentid,$extra_str);
					}
				}
				else if(in_array(strtoupper($this->city_vn),$this->remote_city_arr))
				{
					if(in_array(strtoupper($this->city_vn),$this->reliance_remote_city)){ 
						$this->rcom_remote_url="http://".constant(strtoupper($this->city_vn ).'_RELIANCE_API_URL')."/TECDRestService/";
					}else{
						$this->rcom_remote_url = "";
					}
					$this->techinfo_url ="";	
				}
			}*/
		}
		else
		{
			// for development
			if(in_array(strtoupper($this->city_vn),$this->main_city_arr))
			{
				if(in_array(strtoupper($this->city_vn),$this->reliance_main_city) && in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel)){ 
						$this->techinfo_url="http://".constant(strtoupper($this->city_vn ).'_RELIANCE_API_URL')."/JD_CALITEAPI/api.php?";
				}else if(in_array(strtoupper($this->city_vn),$this->reliance_main_city) && !in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel)) 
				{
						$this->techinfo_url="http://".constant(strtoupper($this->city_vn ).'_RELIANCE_API_URL')."/TECDRestService/";
				}else{
					$this->techinfo_url="http://172.29.66.229/justdial/";
				}
			}
			else if(in_array(strtoupper($this->city_vn),$this->remote_city_arr))
			{
				if(in_array(strtoupper($this->city_vn),$this->reliance_remote_city) && in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel)){ 
					$this->rcom_remote_url="http://".constant(strtoupper($this->city_vn ).'_RELIANCE_API_URL')."/JD_CALITEAPI/api.php?";
				}else if(in_array(strtoupper($this->city_vn),$this->reliance_remote_city) && !in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel)){ 
				$this->rcom_remote_url="http://".constant(strtoupper($this->city_vn ).'_RELIANCE_API_URL')."/TECDRestService/";
				}else{
					$this->rcom_remote_url = "";
				}
				$this->techinfo_url ="http://172.29.66.229/justdial/";	
			}
			else if(!in_array(strtoupper($this->city_vn),$this->remote_city_arr) && !in_array(strtoupper($this->city_vn),$this->main_city_arr))
				{
					$this->techinfo_url="http://172.29.66.229/justdial/";
				}
		}
	}
	
	function initializedJdfosurl()
	{
		if(APP_LIVE == 1)
		{
			$this->jdfos_url = JDFOS_LIVE_URL;
		}
		else
		{
			$this->jdfos_url = JDFOS_LIVE_DEV;
		}
	}
	
	function getCountryStdCode(){
		if(in_array(strtoupper($this->city_vn),array_keys($this->cityStdCode_arr))){
			$this->CountryStdCode = $this->country_code.$this->cityStdCode_arr[strtoupper($this->city_vn)];
		}
	}
	
	function genio_update_virtual_number($parentid,$verticalFlag='',$expired_display_flag='')
    {
		if(!$this->RcomConnectFlag)
		{
			$logtbl_process_flag=1;
			$logtbl_reason = "Not able to connect with Reliance Url";
			$process_name = "Inside Genio Update VN Function";
			$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
			return;
		}
		
		if(strtolower($this->city_vn) == 'coimbatore')
		{
			/*$this->logmsgvirtualno("Virtual Number Process skipped In coimbatore City ",$this->log_path,$this->processName,$this->parentid,$extra_str);
			$logtbl_process_flag=1;
			$logtbl_reason = "Virtual Number Process is stopped In coimbatore City";
			$process_name = "Inside Genio Update VN Function";
			$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
			return;*/
		}
        $process_flag = false;
        $this->initialized($parentid);
        $this->initializedtechinfo(); 
        $this->initializedJdfosurl();
        $this->getCountryStdCode();
        $this->getinitailvalue();
        $this->getLinkedContracts();
        $this->verticalFlag = 0;
        
        if($this->linkcontract_flag)
        {
            $this->sort_link_contract();
        }
        $this->paid_flag = $this->checkPaid();
        
        $verticalFlag = $this->checkOtherVertical();
		
		$this->checkJdfos();					/* checking jdfos condition  */
		
		$this->checkShopfrontFlag();			/* checking jdshopfront condition*/
		
		$this->currentExpiryStatus();
		
		
		if($this->JDFOS_flag == true || $this->shopfront_flag == true)  /*either of the flag is set that set the vertical flag*/
		{
			$verticalFlag = 1;
		}
       
        $this->verticalFlag = $verticalFlag;
       // $this->opt_in_category_flag = $this->is_opt_in_category_exists();
       $this->blockVn_flag = $this->getBlockforVn();
	   $this->quarantineFlag = $this->checkQuarantineVn();
		if($this->opt_in_category_flag == true)
		{
			$this->ineligible_cat_process = "Opt In Category Found";
			$current_expiry_flag = $this->currentExpiryStatus();
			if($current_expiry_flag)
			{
				$this->reason = 'opt_in_category';
				$extra_str="[virtual number :".intval($this->virtualno)."]";
				$this->logmsgvirtualno("Virtual Number Process skipped : Opt In Category Found ",$this->log_path,$this->processName,$this->parentid,$extra_str);
				$this->quarantine_ineligible_cat_exists('OPT_IN_CATEGORY');
				$this->insert_narration($this->reason);
				return 'called';
			}
		}
		$this->freeze = $this->is_freeze_contract();
		if($this->freeze == true)
		{	
			$this->remoteVnDbArr = $this->vnSearch();	
			
				if(count($this->remoteVnDbArr)>0)
				{
					if(trim($this->remoteVnDbArr['BusinessId'])==trim($this->parentid) && $this->remoteVnDbArr['BusinessId']!='')
					{	
						if($this->virtualno > 0)
						{
							
							$this->cron_dealloc_virtualno($this->parentid);
							$this->reason = 'freeze contract';
							$extra_str="[virtual number :".intval($this->virtualno)."]";
							$this->logmsgvirtualno("Virtual Number Process skipped : freeze contract ",$this->log_path,$this->processName,$this->parentid,$extra_str);
							$this->insert_narration($this->reason);
							
						}
					}
					else{
						$extra_str="[database contractid : ".$this->remoteVnDbArr['BusinessId']."][given parentid :".$this->parentid."]";
						$this->logmsgvirtualno("Database contractid id is not same as given parentid",$this->log_path,$this->processName,$this->parentid,$extra_str);
						$this->updateCompanymaster();
					}
				}
				
				return 'called'; 			
		}
				
      //  $this->block_vn_cat_flag = $this->chk_block_for_vn_category();
        if($this->block_vn_cat_flag == true)
        {
			$this->ineligible_cat_process = "Block For VN Category Found";
			$this->reason = 'block_for_vn_category';
			$extra_str="[virtual number :".intval($this->virtualno)."]";
			$this->logmsgvirtualno("Virtual Number Process skipped : Block For Virtual Number Category Found ",$this->log_path,$this->processName,$this->parentid,$extra_str);
			$this->quarantine_ineligible_cat_exists('BLOCK_FOR_VN');
			//return 'called';
			if(in_array(strtoupper($this->city_vn),$this->reliance_main_city)){
				if(intval($this->virtualno)>0)
				{
					$allocate = 1;
					$success = 1;
				}
				else
				{
					$this->reason = 'block_for_vn_category';
					$success=1;
					$allocate = 0;
					$this->insert_narration($this->reason);
					
				}
				return json_encode(array("status"=>intval($success),"virtual number"=>intval($this->virtualno),"allocate"=>intval($allocate),"reason"=>$this->reason,"main reason"=>$this->vnNotAssignreason));
			}else{
				if(intval($this->virtualno)>0)
				{
					return 'called';
				}elseif(trim($this->reason)!=''){
					return 'called';
				}else{
					return 'failed';
				}
			}
		}
        /*if($jdfocFlag!='' && $jdfocFlag==1)
        {
			$this->JDFOS_flag = $jdfocFlag;
		}*/
		
		
	/*	if($this->blockVn_flag == 1)
		{
			$this->quarantine_ineligible_cat_exists('BLOCK_FOR_VN');
		}
		*/
		
		
	//	$this->allowed_contract = $this->allocContracts();
		/*if(intval($this->allowed_contract) == 1) 
		{
			
			$extra_str = "forceful vn allocation contracts";
			$this->logmsgvirtualno("Call counts contracts",$this->log_path,$this->processName,$this->parentid,$extra_str);
			if(intval($this->virtualno)>0)
			{
				return 'called';
			}
		}*/
		
		$extra_str="[virtual number :".intval($this->virtualno)."] [Vertical flag : ".$verticalFlag."][JDFOS flag :".intval($this->JDFOS_flag)."][Paid flag :".intval($this->paid_flag)."][expired flag:".intval($this->currentexpired)."][callcount contract:".intval($this->allowed_contract)."]";
		$this->logmsgvirtualno("Check For VN Eligible Flag",$this->log_path,$this->processName,$this->parentid,$extra_str);
		
		
		//$this->virtualno = 0;
        //if($this->paid_flag ==1 || $jdfocFlag ==1 || $this->JDFOS_flag)
        if(((($this->paid_flag ==1 || $verticalFlag ==1 || intval($this->JDFOS_flag)==1) && intval($this->currentexpired)==0) || intval($this->allowed_contract) == 1))
        { 
			$this->vnEligibleCompaign = $this->checkVnEligibleCampaign();
			$extra_str = "[eligible flag :".intval($this->vnEligibleCompaign)."]";
			$this->logmsgvirtualno("checking manual_overide flag,balance & expired flag",$this->log_path,$this->processName,$this->parentid,$extra_str);
			//if($this->vnEligibleCompaign || $jdfocFlag ==1)
			if($this->vnEligibleCompaign || $verticalFlag ==1 || intval($this->JDFOS_flag)==1 || intval($this->allowed_contract) ==1)
			{
				$this->get_vn();
				
				if($this->other_city_api != 1)
				{
					//$this->pincode_flag = $this->checkPincodeFlag();
					$this->pincode_flag = 1;
				}
				else
				{
					$this->pincode_flag = 1;
				}
				if($this->pincode_flag)
				{
					if(intval($this->virtualno)>0)
					{
						$extra_str="[virtual number :".intval($this->virtualno)."]";
						$this->logmsgvirtualno("Virtual number is present",$this->log_path,$this->processName,$this->parentid,$extra_str);
						
						$this->mappednumber_flag = $this->getMappednumber();
						if($this->mappednumber_flag)
						{
							$this->quarantineFlag = $this->checkQuarantineVn();
							if($this->quarantineFlag)
							{
								/*$extra_str="[virtual number :".intval($this->virtualno)."][Quarantine Flag : ".$this->quarantineFlag."]";
								$this->logmsgvirtualno("Contract Virtual numebr is quarantine, still update mapped number in Techinfo server",$this->log_path,$this->processName,$this->parentid,$extra_str);
								$success_vn = $this->updateMapping();
								$this->updateCompanymaster();
								if($this->JDFOS_flag)
								{
									$returnFlag = $this->updateJdfosDb();
								}*/
								$extra_str="[virtual number :".intval($this->virtualno)."][Quarantine Flag : ".$this->quarantineFlag."]";
								$this->logmsgvirtualno("Contract Virtual numebr is quarantine",$this->log_path,$this->processName,$this->parentid,$extra_str);
								
								$this->blockVn_flag = $this->getBlockforVn();
								
								$extra_str="[virtual number :".intval($this->virtualno)."][Block for virtualnumber Flag : ".$this->blockVn_flag."]";
								$this->logmsgvirtualno("Check Block for virtual number",$this->log_path,$this->processName,$this->parentid,$extra_str);
								
								if(intval($this->blockVn_flag)==0){
									$rvQrFg = $this->removeQuarantine($this->virtualno);
									if(!$rvQrFg)
									{
										$message = "<br>ParentId = " . $parentid . "<br>Process Name = ".$this->processName." <br>Reason = " . $reason . "<br>virtual number = " . $this->virtualno . "<br>City = ". $this->city_vn;
										$subject = $this->city_vn." - Quarantine virtual number not remove";
										$this->sendMail($subject, $message);
									}else{
										$this->quarantineFlag = false;
										$extra_str="[virtual number :".intval($this->virtualno)."][Quarantine Flag : ".$this->quarantineFlag."][Function return value :".$rvQrFg."]";
										$this->logmsgvirtualno("Contract Virtual numebr is quarantine, but all condition satify,so remove from quarantine Table",$this->log_path,$this->processName,$this->parentid,$extra_str);
									}
									$this->quarantine_ineligible_cat_exists('Quarantine_FOR_VN');									
									$success_vn = $this->updateMapping();
									$this->updateCompanymaster($this->virtualno);
									if($this->JDFOS_flag)
									{
										$returnFlag = $this->updateJdfosDb($this->virtualno);
									}
								}else{
									$extra_str="[virtual number :".intval($this->virtualno)."][Quarantine Flag : ".$this->quarantineFlag."]";
									$this->logmsgvirtualno("Contract Virtual number is quarantine, still update mapped number in Techinfo server",$this->log_path,$this->processName,$this->parentid,$extra_str);
									$success_vn = $this->updateMapping();
									$this->updateCompanymaster();
									$this->quarantine_ineligible_cat_exists('BLOCK_FOR_VN');
									if($this->JDFOS_flag)
									{
										$returnFlag = $this->updateJdfosDb();
									}
								}
							}
							else
							{
								$this->quarantineFlag_expire = $this->checkQuarantineVn_Expire();
								$this->approval_flag = $this->chk_eligible_campaign_balance();
								if(($this->quarantineFlag_expire && $this->approval_flag) || intval($this->allowed_contract)==1)
								{
									$extra_str="[virtual number :".intval($this->virtualno)."][Quarantine Flag : ".$this->quarantineFlag."]";
									$this->logmsgvirtualno("Expire Quarantine with hide flag 1 and having balance in campaign 1,2 and 23 ",$this->log_path,$this->processName,$this->parentid,$extra_str);
									$success_vn = $this->updateMapping();
									$this->updateCompanymaster($this->virtualno);
									$this->updateQuaranHideFlag();
									//$this->JDFOS_flag=1;
									if($this->JDFOS_flag)
									{
										$returnFlag = $this->updateJdfosDb($this->virtualno);
									}
								}
								else if(($this->quarantineFlag_expire) && (!$this->approval_flag) && ($expired_display_flag != 1))
								{
									$extra_str="[virtual number :".intval($this->virtualno)."][Quarantine Flag : ".$this->quarantineFlag."]";
									$this->logmsgvirtualno("Expire Quarantine with hide flag 1, But Not having balance in campaign 1,2 and 23",$this->log_path,$this->processName,$this->parentid,$extra_str);
									$success_vn = $this->updateMapping();
									$this->updateCompanymaster();
									if($this->JDFOS_flag)
									{
										$returnFlag = $this->updateJdfosDb();
									}
								}
								else
								{
									$extra_str="[virtual number :".intval($this->virtualno)."][Quarantine Flag : ".$this->quarantineFlag."]";
									$this->logmsgvirtualno("Contract Virtual numebr is not quarantine, still update mapped number On VN server",$this->log_path,$this->processName,$this->parentid,$extra_str);
									$success_vn = $this->updateMapping();
									$this->updateCompanymaster($this->virtualno);
									//$this->JDFOS_flag=1;
									if($this->JDFOS_flag)
									{
										$returnFlag = $this->updateJdfosDb($this->virtualno);
									}
									if($expired_display_flag == 1)
									{
										$this->updateQuaranHideFlag();
									}
								}
							}
								
						}
						else
						{
							$this->reason = 'mapped number not exist in the contract or in any link contracts';
							$this->vnNotAssignreason = 'mapped_number';
							$this->quarantineFlag = $this->checkQuarantineVn();
							$extra_str="[virtual number :".intval($this->virtualno)."][Quarantine Flag : ".$this->quarantineFlag."]";
							$this->logmsgvirtualno("Check quarantine flag",$this->log_path,$this->processName,$this->parentid,$extra_str);
							if(!$this->quarantineFlag && intval($this->virtualno)>0)
							{
								$this->QuarantineNo($this->reason);
							}
							$this->insert_narration($this->reason);
							$this->updateCompanymaster();
							if($this->JDFOS_flag)
							{
								$returnFlag = $this->updateJdfosDb();
							}
						}
					}
					else
					{
						$this->mappednumber_flag = $this->getMappednumber();
						$this->blockVn_flag = $this->getBlockforVn();
						if($this->mappednumber_flag && intval($this->blockVn_flag)==0)
						{
							$oldExpired = $this->Oldexpired();
							if(!$oldExpired || intval($this->allowed_contract) == 1){
								$success_vn = $this->freshAllocation();
								if(intval($success_vn)>0)
								{
									$this->updateCompanymaster($this->virtualno);
									if($this->JDFOS_flag)
									{
										$returnFlag = $this->updateJdfosDb($this->virtualno);
									}
								}
							}else{//$this->shopfront_flag&&$verticalFlag
								$this->reason = 'six months old expired';
								$this->six_month_flg=1;
								$extra_str="[contract is more thn 6 month expired, so vn is not assign for this contract][virtual number :".intval($this->virtualno)."]";
								$this->logmsgvirtualno("Check contract is 6 or more thn 6 month expired contract",$this->log_path,$this->processName,$this->parentid,$extra_str);
								$this->insert_narration($this->reason);
								$this->updateCompanymaster();
								if($this->JDFOS_flag)
								{
									$returnFlag = $this->updateJdfosDb();
								}
							}
						}
						elseif($this->mappednumber_flag && intval($this->blockVn_flag)==1)
						{
							$this->reason = 'mapped number  exist in the contract but contract is block for virtual number';
							$this->vnNotAssignreason = 'block_for_vn';
							$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."][pincode : ".$this->pincode."][block for Vn flag : ".intval($this->blockVn_flag)."]";
							$this->logmsgvirtualno("Contract is not eligible as well as it does not have virtual number",$this->log_path,$this->processName,$this->parentid,$extra_str);
							$this->insert_narration($this->reason);
							$this->quarantine_ineligible_cat_exists('BLOCK_FOR_VN');
						}
						else
						{
							$this->reason = 'mapped number not exist in the contract or in any link contracts';
							$this->vnNotAssignreason = 'mapped_number';
							$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."][pincode : ".$this->pincode."]";
							$this->logmsgvirtualno("Contract is not eligible as well as it does not have virtual number",$this->log_path,$this->processName,$this->parentid,$extra_str);
							$this->insert_narration($this->reason);
						}
					}
				}
				else
				{
					$this->quarantineFlag = $this->checkQuarantineVn();
					$this->reason = 'pincode out of city';
					$this->vnNotAssignreason = 'pincode';
					$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."][pincode : ".$this->pincode."][Quarantine Flag : ".$this->quarantineFlag."]";
					$this->logmsgvirtualno("Contract is not eligible as well as it does not have virtual number",$this->log_path,$this->processName,$this->parentid,$extra_str);
					$this->insert_narration($this->reason);
					if(!$this->quarantineFlag && intval($this->virtualno)>0)
					{
						$this->QuarantineNo($this->reason);
					}
					$this->updateCompanymaster();
					if($this->JDFOS_flag)
					{
						$returnFlag = $this->updateJdfosDb();
					}
				}
			}
			else
			{
				if(intval($this->virtualno)>0)
				{
					$this->reason = 'not a virtual number eligible campaign';
					$this->vnNotAssignreason = 'non_eligible_campaign';
					$this->deallocate_vn();
					$this->quarantine_ineligible_cat_exists('quarantine_FOR_VN');
					$this->insert_narration($this->reason);
				}
				else
				{
					if($this->jdfos_pid_chk_flag == true)
					{
						$this->updateJdfosDb();
					}
					$this->reason = 'not a virtual number eligible campaign';
					$this->vnNotAssignreason = 'non_eligible_campaign';
					$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."]";
					$this->logmsgvirtualno("Contract is not eligible as well as it does not have virtual number",$this->log_path,$this->processName,$this->parentid,$extra_str);
					$this->insert_narration($this->reason);
				}
			}
		}
		else
		{
			if(intval($this->virtualno)>0)
			{
				if(intval($this->currentexpired) == 1 && !$this->quarantineFlag)
				{
					
					$this->reason = "Paid expired into Quarantine";
					
					if($this->expired_on_date)
					$this->deallocate_vn();
					else
					$this->QuarantineNo($this->reason);
					
					$this->getMappednumber();
					$this->updateMapping();
					//$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."][passing jdfos flag:".$jdfocFlag."][obj jdfos flag :".$this->JDFOS_flag."]";
					$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."][expired flag :".$this->currentexpired."]";
					$this->logmsgvirtualno("Quarantine VN",$this->log_path,$this->processName,$this->parentid,$extra_str);
					
						
				}
				else if(intval($this->quarantineFlag) == 1)
				{
					$checkVal = $this->checkCalling();
					if($checkVal)
					{
						$this->cron_dealloc_virtualno($this->parentid);
						$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."][passing vertical flag:".$verticalFlag."][obj jdfos flag :".$this->JDFOS_flag."][expired flag :".$this->currentexpired."]";
						$this->logmsgvirtualno("Deallocate VN",$this->log_path,$this->processName,$this->parentid,$extra_str);
					}
					else
					{	
						$this->quarantine_ineligible_cat_exists('quarantine_FOR_VN');
						$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."][passing vertical flag:".$verticalFlag."][obj jdfos flag :".$this->JDFOS_flag."][expired flag :".$this->currentexpired."]";
						$this->logmsgvirtualno("Inside Quarantine ",$this->log_path,$this->processName,$this->parentid,$extra_str);
					}
				}
				else
				{	
				$this->deallocate_vn();
				//$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."][passing jdfos flag:".$jdfocFlag."][obj jdfos flag :".$this->JDFOS_flag."]";
				$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."][passing vertical flag:".$verticalFlag."][obj jdfos flag :".$this->JDFOS_flag."][expired flag :".$this->currentexpired."]";
				$this->logmsgvirtualno("Deallocate VN",$this->log_path,$this->processName,$this->parentid,$extra_str);
				}
				$this->updateCompanymaster();
			}
			else
			{
				if($this->jdfos_pid_chk_flag == true)
				{
						$this->updateJdfosDb();
				}
				$this->reason = 'Contract is not eligible for virtual number reason - nonpaid contract';
				$this->vnNotAssignreason = 'nonpaid';
				$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."]";
				$this->logmsgvirtualno("Contract is not eligible as well as it does not have virtual number",$this->log_path,$this->processName,$this->parentid,$extra_str);
				$this->insert_narration($this->reason);
			}
		}
		if(in_array(strtoupper($this->city_vn),$this->reliance_main_city)){
			if(intval($this->virtualno)>0)
			{
				//return 'called';
				$allocate = 1;
				$success = 1;
			}
			else
			{
				//return 'failed';
				if(in_array($this->vnNotAssignreason,array('mapped_number','block_for_vn','pincode','non_eligible_campaign','nonpaid'))){
					$success=1;
				}else{
					$success=0;
				}
				$allocate = 0;
				
			}
			return json_encode(array("status"=>intval($success),"virtual number"=>intval($this->virtualno),"allocate"=>intval($allocate),"reason"=>$this->reason,"main reason"=>$this->vnNotAssignreason));
		}else{
			if(intval($this->virtualno)>0)
			{
				return 'called';
			}elseif(trim($this->reason)!=''){
				return 'called';
			}else{
				return 'failed';
			}
		}
	}
	
	function checkCalling(){
		$sql = "SELECT DISTINCT a.businessid FROM d_jds.tbl_unused_quarantine_vn_15days AS a
					JOIN
					d_jds.tbl_quarantine_virtualnumber AS b
					ON 
					a.businessid = b.businessid
					WHERE  
					b.active_flag = 1 AND b.reason = 'Paid expired into Quarantine' AND  b.businessid='".$this->parentid."'";
			$res = $this->execute_query($sql,$this->conn_decs);
		$extra_str="[query : ".$sql."][res : ".$res."]";	
		$this->logmsgvirtualno("checkCalling function",$this->log_path,$this->processName,$this->parentid,$extra_str);				
			if($res && mysql_num_rows($res)>0)
			{
				return true;
			}
			else
			{
				return false;
			}	
	}	
	function allocContracts()
	{
		$this->allowed_contract = false;
		$qry = "SELECT parentid FROM tbl_vn_callcount where parentid='".$this->parentid."'";
		$res_qry = $this->execute_query($qry,$this->conn_decs);
		if($res_qry && mysql_num_rows($res_qry)>0)
		{
			/*$this->get_vn();
			$this->mappednumber_flag = $this->getMappednumber();
			//$this->blockVn_flag = $this->getBlockforVn();
			if(intval($this->quarantineFlag) == 1 && intval($this->blockVn_flag) == 0 && intval($this->block_vn_cat_flag) == 0 && intval($this->opt_in_category_flag) == 0) 
			{
				$rvQrFg = $this->removeQuarantine($this->virtualno);
				if(!$rvQrFg)
				{
					$message = "<br>ParentId = " . $parentid . "<br>Process Name = ".$this->processName." <br>Reason = " . $reason . "<br>virtual number = " . $this->virtualno . "<br>City = ". $this->city_vn;
					$subject = $this->city_vn." - Quarantine virtual number not remove";
					$this->sendMail($subject, $message);
				}else{
					$this->quarantineFlag = false;
					$extra_str="[virtual number :".intval($this->virtualno)."][Quarantine Flag : ".$this->quarantineFlag."][Function return value :".$rvQrFg."]";
					$this->logmsgvirtualno("Contract Virtual numebr is quarantine, but all condition satify,so remove from quarantine Table",$this->log_path,$this->processName,$this->parentid,$extra_str);
				}
				
			}
			
			if($this->mappednumber_flag && intval($this->blockVn_flag)==0 && intval($this->virtualno)<=0)
			{
				$success_vn = $this->freshAllocation();	
			}
			else
			{
				$success_vn = $this->updateMapping();
			}
			if(intval($success_vn)>0)
			{
				$this->updateCompanymaster($this->virtualno);
				if($this->JDFOS_flag)
				{
					$returnFlag = $this->updateJdfosDb($this->virtualno);
				}
			}
			$this->allowed_contract = true;
			
		}
		else
		{
			
			$this->allowed_contract = false;
		}
		*/
			$this->allowed_contract = false;
		}
		return $this->allowed_contract;
	}
	
	function get_vn()
	{
			$businessid=$this->parentid;
			$this->techInfoPaidStatus();
			$this->status='A'; 
			if(in_array(strtoupper($this->city_vn),$this->reliance_main_city))
			{
				if(in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
				{
					
					$curl_url = $this->techinfo_url."action=businessRef";
					$postarr = $this->makeRelianceUrl($businessid); 
					$curl_output = $this->runCurlUrl($curl_url,'POST',$postarr);
				}
				else if(!in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
				{
					$curl_url = $this->techinfo_url."businessreference/".$this->parentid;
					$postarr = $this->makeRelianceUrl($businessid); 
					$curl_output = $this->runCurlUrl($curl_url,'GET',$postarr);
				}
				//$postarr = $this->makeRelianceUrl($businessid); 
				//$curl_output = $this->runCurlUrl($curl_url,'GET',$postarr);
				
					if(!$this->curl_response_flag)
					{
						$logtbl_process_flag=1;
						$logtbl_reason = "url not working";
						$process_name = "business ref api not working";
						$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
						$extra_str="[url not working][API URL :".$curl_url."][API OUTPUT :".$curl_output."]";
						$this->logmsgvirtualno("business ref api not working",$this->log_path,$this->processName,$this->parentid,$extra_str);
					}
					else
					{
						if(in_array(strtoupper($this->city_vn),$this->reliance_main_city)){
							$techinfo_vrn = $this->readRelianceJson($curl_output,2);
						}else{
							$techinfo_vrn = $this->readTechinfoXml($curl_output);
						}
						$this->virtualno = $techinfo_vrn;
					//	echo '<pre>get vn ====';print_r($this->virtualno);die();
						$extra_str="[API : ".$curl_url."][POST Arr : ".json_encode($postarr)."][API OUTPUT :".$curl_output."][vn : ".$techinfo_vrn."][Virtual Number :".$this->virtualno."]";
						$this->logmsgvirtualno("Vn from API",$this->log_path,$this->processName,$this->parentid,$extra_str);
					}
			}
			else if(in_array(strtoupper($this->city_vn),$this->reliance_remote_city))
			{
				if(in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
				{
					
					$curl_url =  $this->rcom_remote_url."action=businessRef";
					$postarr = $this->makeRelianceUrl($businessid); 
					$curl_output = $this->runCurlUrl($curl_url,'POST',$postarr);
				}
				else if(!in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
				{
					$curl_url =  $this->rcom_remote_url."businessreference/".$this->parentid;
					$postarr = $this->makeRelianceUrl($businessid); 
					$curl_output = $this->runCurlUrl($curl_url,'GET',$postarr);
				}
				
					if(!$this->curl_response_flag)
					{
						$logtbl_process_flag=1;
						$logtbl_reason = "url not working";
						$process_name = "business ref api not working";
						$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
						$extra_str="[url not working][API URL :".$curl_url."][API OUTPUT :".$curl_output."]";
						$this->logmsgvirtualno("business ref api not working",$this->log_path,$this->processName,$this->parentid,$extra_str);
					}
					else
					{
						if(in_array(strtoupper($this->city_vn),$this->reliance_remote_city)){
							$techinfo_vrn = $this->readRelianceJson($curl_output,2);
						}else{
							$techinfo_vrn = $this->readTechinfoXml($curl_output);
						}
						$this->virtualno = $techinfo_vrn;
					//	echo '<pre>get vn ====';print_r($this->virtualno);die();
						$extra_str="[API : ".$curl_url."][POST Arr : ".json_encode($postarr)."][API OUTPUT :".$curl_output."][vn : ".$techinfo_vrn."][Virtual Number :".$this->virtualno."]";
						$this->logmsgvirtualno("Vn from API",$this->log_path,$this->processName,$this->parentid,$extra_str);
					}
			}	
		return $curl_output;		
	}
	
	function genio_block_for_vn_category($parentid)
	{
		$this->initialized($parentid);
        $this->initializedtechinfo(); 
        $this->initializedJdfosurl();
        $this->getinitailvalue();
        $this->paid_flag = $this->checkPaid();
        $this->checkJdfos();
		if($this->JDFOS_flag == true){
			$verticalFlag = 1;
		}else{
			$verticalFlag = $this->checkOtherVertical();
		}
		if(($this->paid_flag ==1 || $verticalFlag ==1 || $this->JDFOS_flag) && (intval($this->geninfo_vno)<=0))
		{
			$this->block_untag_proceed_flag = true;
		}
		if(intval($this->virtualno)>0)
		{
			$this->block_tag_proceed_flag = true;
		}
	}
	function insert_narration($reason)
	{
		$empname = $this->emp_details($this->usercode);
		$reason .=" (System updated narration) ";
		$reason .= "\n".date("l jS M, Y H:i:s")."\n";
		$reason .="\n -".$empname."\n";
		//echo $reason; 
		$sql_upt_narr = "INSERT INTO d_jds.tbl_paid_narration (contractid,narration,creationDt,createdBy,parentid,data_city) VALUES ('".$this->parentid."','".addslashes($reason)."','".date('Y-m-d H:i:s')."','".$this->usercode ."','".$this->parentid."','".$this->city_vn."')";
		$res_upt_narr = $this->execute_query($sql_upt_narr,$this->conn_decs);
		
	}
	
	function emp_details($empcode)
	{
		$curlurl 	= "http://".SSO_IP."/hrmodule/employee/fetch_employee_info/".$empcode;
		$ch = curl_init();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$curlurl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_POST, TRUE);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$postarr);
		$resultString = curl_exec($ch);
		$emp_detail = json_decode($resultString,true);
		return $emp_detail['data']['empname'];
	}
	
	function chk_eligible_campaign_balance()
	{
		$expired_approval_flag = true;
		$sqlContractBalance = "SELECT parentid FROM tbl_companymaster_finance WHERE parentid = '".$this->parentid."' AND campaignid NOT IN (22, 17, 86, 87, 88, 84, 83, 82, 75, 74, 73, 72 ) and (balance > 0 or (balance<=0 and expired=0 and manual_override=1)) ";
		$resContractBalance = $this->execute_query($sqlContractBalance,$this->conn_finance);
		if($resContractBalance && mysql_num_rows($resContractBalance)<=0)
		{
			$expired_approval_flag = false;
		}
		else
		{
			$sqlContractBalance_national = "SELECT parentid FROM db_national_listing.tbl_companymaster_finance_national WHERE parentid = '".$this->parentid."' AND campaignid IN(10) AND balance>0";
			$resContractBalance_national = $this->execute_query($sqlContractBalance_national,$this->conn_idc);
			if($resContractBalance_national && mysql_num_rows($resContractBalance_national)<=0)
			{
				$expired_approval_flag = false;
			}
			
		}
		return $expired_approval_flag;
	}
	
	function Oldexpired(){
		$totalexpiredCampaign = 0;
		$sql1="SELECT DATE_FORMAT(subdate(now(),interval 6 month),'%Y-%m-%d')  AS dt";
		$res1=$this->execute_query($sql1,$this->conn_finance);
		$row1=mysql_fetch_array($res1);
		$month=$row1['dt'];
		if($this->linkcontract_flag)
        {
            $pid_list = implode("','",$this->linkcontracts); 
        }
        else
        {
            $pid_list = $this->parentid;
        }
        $qrygetAllActiveCampiagn = "SELECT count(campaignid) as linkcount FROM tbl_companymaster_finance WHERE parentid in ('".$pid_list."') AND campaignid NOT IN (22, 17, 86, 87, 88, 84, 83, 82, 75, 74, 73, 72 )";
		$resgetAllActiveCampiagn = $this->execute_query($qrygetAllActiveCampiagn,$this->conn_finance);
		if($resgetAllActiveCampiagn){
			$rowgetAllActiveCampiagn = mysql_fetch_assoc($resgetAllActiveCampiagn);
			$totalActiveCamapign = $rowgetAllActiveCampiagn['linkcount'];
		}else{
			$totalActiveCamapign = 0;
		}
		
		 $qrygetAllActiveCampiagn_national = "SELECT expired,date(expired_on) as expired_on FROM db_national_listing.tbl_companymaster_finance_national WHERE parentid in ('".$pid_list."') AND campaignid in(10)";
		 $resgetAllActiveCampiagn_national = $this->execute_query($qrygetAllActiveCampiagn_national,$this->conn_idc);
		 if($resgetAllActiveCampiagn_national){
			$totalActiveCamapign += mysql_num_rows($resgetAllActiveCampiagn_national);
			$rowgetAllActiveCampiagn_national = mysql_fetch_assoc($resgetAllActiveCampiagn_national);
			
			if($rowgetAllActiveCampiagn_national['expired'])
				$last_expired_link_child_national = $rowgetAllActiveCampiagn_national['expired_on'];
		 }
		
		$extra_str="[contract having total Campaign : ".intval($totalActiveCamapign)."]";
		$this->logmsgvirtualno("Total Campaign",$this->log_path,$this->processName,$this->parentid,$extra_str);
		
		$qry_exp_link_con_child="SELECT parentid,expired,date(expired_on) as expired_on FROM tbl_companymaster_finance WHERE parentid in ('".$pid_list."') AND campaignid NOT IN (22, 17, 86, 87, 88, 84, 83, 82, 75, 74, 73, 72 ) AND expired=1 ORDER BY expired_on DESC";
		$res_exp_link_con_child = $this->execute_query($qry_exp_link_con_child,$this->conn_finance);
		if($res_exp_link_con_child){
			$totalexpiredCampaign = mysql_num_rows($res_exp_link_con_child);
			
			
			if($totalexpiredCampaign>0){
				$row_exp_link_con_child = mysql_fetch_assoc($res_exp_link_con_child);
				$last_expired_link_child = $row_exp_link_con_child['expired_on'];
			}
			
			
		}
			if($rowgetAllActiveCampiagn_national['expired'])
				$totalexpiredCampaign += mysql_num_rows($resgetAllActiveCampiagn_national);
			
			
			if( $last_expired_link_child_national && strtotime($last_expired_link_child_national) > strtotime($last_expired_link_child))
				$last_expired_link_child = $last_expired_link_child_national;
		
			$extra_str="[Expired Campaign : ".intval($totalexpiredCampaign )."]";
			$this->logmsgvirtualno("Total Expired Campaign",$this->log_path,$this->processName,$this->parentid,$extra_str);
			
			if((intval($totalexpiredCampaign)==intval($totalActiveCamapign)) && $totalActiveCamapign>0 &&  $totalexpiredCampaign >0){
				if(strtotime(trim($last_expired_link_child))<= strtotime($month) && trim($last_expired_link_child)!='' && trim($last_expired_link_child)!='0000-00-00 00:00:00'){
					$this->oldexpired = true;
				}
			}
		$extra_str="[Total Expired campaign : ".intval($totalexpiredCampaign)."][Total campaign :".intval($totalActiveCamapign)."][latest expired Date :".$last_expired_link_child."][rows :".json_encode($row_exp_link_con_child)."]";
		$this->logmsgvirtualno("To Get Expired Flag ",$this->log_path,$this->processName,$this->parentid,$extra_str);
		
		return $this->oldexpired;
	}
	function currentExpiryStatus(){
		$expired_campaign_count = 0;
		if($this->linkcontract_flag)
        {
            $pid_list = implode("','",$this->linkcontracts); 
        }
        else
        {
            $pid_list = $this->parentid;
        }
		$sqlAllActiveCampaign = "SELECT count(campaignid) as linkcount FROM tbl_companymaster_finance WHERE parentid in ('".$pid_list."') AND campaignid NOT IN (22, 17, 86, 87, 88, 84, 83, 82, 75, 74, 73, 72 )";
		$resAllActiveCampaign = $this->execute_query($sqlAllActiveCampaign,$this->conn_finance);
		if($resAllActiveCampaign){
			$row_all_active_campaign = mysql_fetch_assoc($resAllActiveCampaign);
			$active_campaign_count 	 = $row_all_active_campaign['linkcount'];
		}else{
			$active_campaign_count = 0;
		}
		
		$date_expired_on = '0000-00-00';
		
		 $qrygetAllActiveCampiagn_national = "SELECT balance,expired,date(expired_on) as expired_on,manual_override FROM db_national_listing.tbl_companymaster_finance_national WHERE parentid in ('".$pid_list."') AND campaignid in(10)";
		 $resgetAllActiveCampiagn_national = $this->execute_query($qrygetAllActiveCampiagn_national,$this->conn_idc);
		 if($resgetAllActiveCampiagn_national){
			$active_campaign_count += mysql_num_rows($resgetAllActiveCampiagn_national);
			$rowgetAllActiveCampiagn_national = mysql_fetch_assoc($resgetAllActiveCampiagn_national);
			
			if($rowgetAllActiveCampiagn_national['balance'] <= 0 && $rowgetAllActiveCampiagn_national['expired'] == 1 && $rowgetAllActiveCampiagn_national['manual_override'] == 0)
			{
				$date_expired_on = $rowgetAllActiveCampiagn_national['expired_on'];
			}
				
		 } 
		 
		
		$extra_str="[contract having total Campaign : ".intval($active_campaign_count)."]";
		$this->logmsgvirtualno("Total Campaign ",$this->log_path,$this->processName,$this->parentid,$extra_str);
		
		
		
		$qry_exp_link_con_child="SELECT parentid,expired,date(expired_on) as expired_on FROM tbl_companymaster_finance WHERE parentid in ('".$pid_list."') AND campaignid NOT IN (22, 17, 86, 87, 88, 84, 83, 82, 75, 74, 73, 72 ) AND balance<=0 AND expired=1";
		$res_exp_link_con_child = $this->execute_query($qry_exp_link_con_child,$this->conn_finance);
		if($res_exp_link_con_child){
			$expired_campaign_count = mysql_num_rows($res_exp_link_con_child);
			while($rowgetAllActiveCampiagn = mysql_fetch_assoc($res_exp_link_con_child))
			{
				if(strtotime($date_expired_on) < strtotime($rowgetAllActiveCampiagn['expired_on']))
				$date_expired_on = $rowgetAllActiveCampiagn['expired_on'];
			}	
			
		}
		
		
		if($rowgetAllActiveCampiagn_national['balance'] <= 0 && $rowgetAllActiveCampiagn_national['expired'])
			$expired_campaign_count += 1;
		
		$extra_str="[Expired Campaign : ".intval($expired_campaign_count )."]";
		$this->logmsgvirtualno("Total Expired Campaign",$this->log_path,$this->processName,$this->parentid,$extra_str);
		
		if(((intval($expired_campaign_count)==intval($active_campaign_count)) && $active_campaign_count>0 &&  $expired_campaign_count >0) || ($active_campaign_count <= 0 && $expired_campaign_count <= 0)){
			
			$this->currentexpired = true;
			
			$expired_date = strtotime($date_expired_on);
			$now = time();
			$difference = $now - $expired_date;
			$days = floor($difference / (60*60*24) );
			if($days > "30")
			{
				$this->expired_on_date = true;	
			}
			
		}
		
		
		$extra_str="[Total campaign :".intval($active_campaign_count)."][Total Expired campaign : ".intval($expired_campaign_count)."]";
		$this->logmsgvirtualno("get expired flag",$this->log_path,$this->processName,$this->parentid,$extra_str);
		
		return $this->currentexpired;
	}
	function updateQuaranHideFlag()
	{
		/*if($this->linkcontract_flag)
        {
            $hide_pid_list = implode("','",$this->linkcontracts); 
        }
        else
        {
            $hide_pid_list = $this->parentid;
        }*/
        $hide_pid_list = $this->parentid;
		$sqlUpdateHideFlag = "UPDATE d_jds.tbl_quarantine_virtualnumber SET active_flag = 0, hide_flag =0, hidden_by='".$this->usercode."', end_date= NOW(), update_date = NOW() WHERE businessid IN ('".$hide_pid_list."')";
		$resUpdateHideFlag = $this->execute_query($sqlUpdateHideFlag,$this->conn_decs);
		if($resUpdateHideFlag)
		{
			$extra_str="[sqlUpdateHideFlag :".$sqlUpdateHideFlag."][resUpdateHideFlag : ".$resUpdateHideFlag."]";
			$this->logmsgvirtualno("Updating Hide Flag",$this->log_path,$this->processName,$this->parentid,$extra_str);
		}
	}
	function deallocate_vn()
	{
		$this->quarantineFlag = $this->checkQuarantineVn();
		if(in_array(strtoupper($this->city_vn),$this->main_city_arr))
		{
			if(!$this->linkcontract_flag && !$this->quarantineFlag)
			{
				if(in_array(strtoupper($this->city_vn),$this->reliance_main_city)){
					if(in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
					{
						$curl_url = $this->techinfo_url."action=vmDelete&vmnNumber=".$this->CountryStdCode.trim($this->virtualno);
					}
					else if(!in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
					{
						$curl_url = $this->techinfo_url."actualnumber/virtualnumber/".$this->CountryStdCode.trim($this->virtualno)."/user/Jack";
					}
					$curl_res = $this->runCurlUrl($curl_url,'DELETE');
				}else{
					$curl_url = $this->techinfo_url."deallocate.php?VN=".trim($this->virtualno)."&User=".$this->usercode;
					$curl_res = $this->runTechInfoCurlUrl($curl_url);
				}
				
				if(!$this->curl_response_flag)
				{
					$extra_str="[Techinfo url not working for deallocation][url :".$curl_url."]";
					$this->logmsgvirtualno("Deallocating virtual number in techinfo",$this->log_path,$this->processName,$this->parentid,$extra_str);
				}
				else
				{
					//$this->reason = 'Virtual Number Deallocated';
					$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."]";
					$this->logmsgvirtualno("Ineligible Contract Found.",$this->log_path,$this->processName,$this->parentid,$extra_str);
				}
			}
			$insertarr['tbl_companymaster_generalinfo']	= array("virtualNumber" => '',"virtual_mapped_number" => '',"pri_number" => '',"parentid"=>$this->parentid);
			$this->compmaster_obj->UpdateRow($insertarr);
			$this->updateCompanyMasterSearch();
			$this->DialableNumberClass->upDateDialableNumber($this->parentid,$this->conn_iro);
			if($this->jdfos_pid_chk_flag == true)
			{
				$this->updateJdfosDb();
			}
		}
		else if(in_array(strtoupper($this->city_vn),$this->remote_city_arr))
		{
			if(!$this->linkcontract_flag && !$this->quarantineFlag)
			{
				if(in_array(strtoupper($this->city_vn),$this->reliance_remote_city)){
					if(in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
					{
						$curl_url = $this->rcom_remote_url."action=vmDelete&vmnNumber=".$this->CountryStdCode.trim($this->virtualno);
					}
					else if(!in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
					{
						$curl_url = $this->rcom_remote_url."actualnumber/virtualnumber/".$this->CountryStdCode.trim($this->virtualno)."/user/Jack";
					}
					$curl_res = $this->runCurlUrl($curl_url,'DELETE');
				}else{
					$curl_url = $this->techinfo_url."deallocate.php?VN=".trim($this->virtualno)."&User=".$this->usercode;
					$curl_res = $this->runTechInfoCurlUrl($curl_url);
				}
			}
			$insertarr	= array();
			$insertarr['tbl_companymaster_generalinfo']	= array("virtualNumber" => '',"virtual_mapped_number" => '',"pri_number" => '',"parentid"=>$this->parentid);
			$this->compmaster_obj->UpdateRow($insertarr);
			
			$this->updateCompanyMasterSearch();
			$this->DialableNumberClass->upDateDialableNumber($this->parentid,$this->conn_iro);

			//$sqlDeleteMapdetails = "DELETE FROM mapdetails WHERE LContract = '".$this->parentid."'";
			//$resDeleteMapDetails = $this->execute_query($sqlDeleteMapdetails,$this->mapdetails_conn);

			$this->reason = 'Virtual Number Deallocated';
			$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."]";
			$this->logmsgvirtualno("Ineligible Contract Found.",$this->log_path,$this->processName,$this->parentid,$extra_str);
			if($this->jdfos_pid_chk_flag == true)
			{
				$this->updateJdfosDb();
			}
		}
		else if(!in_array(strtoupper($this->city_vn),$this->remote_city_arr) && !in_array(strtoupper($this->city_vn),$this->main_city_arr))
		{
			if(!$this->linkcontract_flag && !$this->quarantineFlag)
			{
				
					$curl_url = $this->techinfo_url."deallocate.php?VN=".trim($this->virtualno)."&User=".$this->usercode;
					$curl_res = $this->runTechInfoCurlUrl($curl_url);
			}
			$insertarr	= array();
			$insertarr['tbl_companymaster_generalinfo']	= array("virtualNumber" => '',"virtual_mapped_number" => '',"pri_number" => '',"parentid"=>$this->parentid);
			$this->compmaster_obj->UpdateRow($insertarr);
			
			$this->updateCompanyMasterSearch();
			$this->DialableNumberClass->upDateDialableNumber($this->parentid,$this->conn_iro);

			//$sqlDeleteMapdetails = "DELETE FROM mapdetails WHERE LContract = '".$this->parentid."'";
			//$resDeleteMapDetails = $this->execute_query($sqlDeleteMapdetails,$this->mapdetails_conn);

			$this->reason = 'Virtual Number Deallocated';
			$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."]";
			$this->logmsgvirtualno("Ineligible Contract Found.",$this->log_path,$this->processName,$this->parentid,$extra_str);
			if($this->jdfos_pid_chk_flag == true)
			{
				$this->updateJdfosDb();
			}
		}
		
	}
	
	function getinitailvalue()
    {
		$temparr	= array();
		$fieldstr	= "pincode, virtualnumber";
		$where 		= "parentid = '".$this->parentid."'";
		$res_initialvalue_contract	= $this->compmaster_obj->getRow($fieldstr,"tbl_companymaster_generalinfo",$where);

        if(!$res_initialvalue_contract)
        {
            die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_initialvalue_contract);
            return false;
        }
        else
        {
            if($res_initialvalue_contract['numrows']>0)
            {
                $res_initialvalue_contract =  $res_initialvalue_contract['data']['0'];
                $this->virtualno =$res_initialvalue_contract['virtualnumber'];
                $this->geninfo_vno=$res_initialvalue_contract['virtualnumber'];
                $this->pincode = $res_initialvalue_contract['pincode'];
                if(intval($this->virtualno)<=0)
                {
					$this->checkQuarantine();
				}
            }
        }
    }
	
	function getLinkedContracts($parentid='')
	{
		$owned_parentid     =  false;
        $added              =  false;
        $sorted_linked_contracts = $this->linkcontracts;
        if(trim($parentid)=='')
        {
            $parentid = $this->parentid;
            $owned_parentid = true;
        }
        $qry_get_linked_contract_flag   =   "SELECT parentid, scheme_parentid FROM tbl_company_refer WHERE parentid = '".$parentid."' OR scheme_parentid = '" . $parentid . "' ORDER BY creationDt";
        //$res_get_linked_contract_flag   =   $this->conn_decs->query_sql($qry_get_linked_contract_flag);
        //$res_get_linked_contract_flag   =   $this->execute_query($qry_get_linked_contract_flag,$this->conn_decs);
      
            if(mysql_num_rows($res_get_linked_contract_flag)>0)
            {
                if($owned_parentid && !$this->linkcontract_flag)
                {
                    $this->linkcontract_flag = false;  //changing link contract logic
                }
                while($row_get_linked_contract_flag = mysql_fetch_array($res_get_linked_contract_flag))
                {
					$added = false;
					if(!in_array($row_get_linked_contract_flag['parentid'], $this->linkcontracts))
                    {
						$this->linkcontracts[] = $row_get_linked_contract_flag['parentid'];
						
						$res_getvirtualnumber = array();
						$field		= "virtualnumber";
						$tablename 	= "tbl_companymaster_generalinfo";
						$where		= "parentid='".$row_get_linked_contract_flag['parentid']."'";									
						$res_getvirtualnumber = $this->compmaster_obj->getRow_WDC($field,$tablename,$where);

                        $vrno = 0;

						if($res_getvirtualnumber['numrows']>0)
						{
							$row_getvirtualnumber = $res_getvirtualnumber['data']['0'];
							$vrno = intval($row_getvirtualnumber['virtualnumber']);
						}
                        
                        $this->linkcontractsvalues[$row_get_linked_contract_flag['parentid']] = array('vno'=>$vrno, 'tvno'=>0, 'paid'=>-1, 'expired'=>false, 'freeze'=>true, 'sort'=>0, 'hidden'=>false, 'pincode'=>false, 'mappednumber'=>false, 'businessEligibility'=>true, 'nophonesearch'=>false);
                        $added = true;
					}
					if(!in_array($row_get_linked_contract_flag['scheme_parentid'], $this->linkcontracts))
                    {
                        $this->linkcontracts[] = $row_get_linked_contract_flag['scheme_parentid'];
                        
                        $res_getvirtualnumber = array();
						$field		= "virtualnumber";
						$tablename 	= "tbl_companymaster_generalinfo";
						$where		= "parentid='".$row_get_linked_contract_flag['scheme_parentid']."'";									
						$res_getvirtualnumber = $this->compmaster_obj->getRow_WDC($field,$tablename,$where);
						
                        $vrno = 0;

						if($res_getvirtualnumber['numrows']>0)
						{
							$row_getvirtualnumber = $res_getvirtualnumber['data']['0'];
							$vrno = intval($row_getvirtualnumber['virtualnumber']);   
						}

                        $this->linkcontractsvalues[$row_get_linked_contract_flag['scheme_parentid']] = array('vno'=>$vrno,  'tvno'=>0, 'paid'=>-1, 'expired'=>false, 'freeze'=>false, 'sort'=>0, 'hidden'=>false, 'pincode'=>false, 'mappednumber'=>false, 'businessEligibility'=>true, 'nophonesearch'=>false);
                        $added = true;
                    }
                    if($added)
                    {   
                        if($parentid!=$row_get_linked_contract_flag['parentid'])
                        {
                            $this->getLinkedContracts($row_get_linked_contract_flag['parentid']);
                        }
                        elseif($parentid!=$row_get_linked_contract_flag['scheme_parentid'])
                        {
                            $this->getLinkedContracts($row_get_linked_contract_flag['scheme_parentid']);
                        }
                    } 
				}
				mysql_free_result($res_get_linked_contract_flag);
				unset($row_get_linked_contract_flag);
			}
		$get_arry_str_1 = '';
        $get_arry_str_1 = array_map(create_function('$key, $value', 'return $key.":".$value[0]." # ";'), array_keys($this->linkcontractsvalues), array_values($this->linkcontractsvalues));
       
        $extra_str="[Link contract flag : ".$this->linkcontract_flag."][ total link contract : ".count($this->linkcontracts)."][link contracts : ".implode(",",$this->linkcontracts)."][link contract value : ".implode(",",$get_arry_str_1)."][Main link contract : ".$this->rootcontractid."]";
        $this->logmsgvirtualno("Get link contract information.",$this->log_path,$this->processName,$this->parentid,$extra_str);
        unset($owned_parentid);
        unset($added);   
        unset($parentid); 
        unset($sorted_linked_contracts);  
	}
	
	function is_freeze_contract()
	{
		$sql_extra = "SELECT freeze, closedown_flag FROM db_iro.tbl_companymaster_extradetails where parentid = '".$this->parentid."'";
		$res_extra = $this->execute_query($sql_extra,$this->conn_decs);
		$row_extra = mysql_fetch_array($res_extra);
		$freeze_flag    = $row_extra['freeze'];
		$closedown_flag = $row_extra['closedown_flag'];
		
		
		$sql = "SELECT reason FROM d_jds.tbl_compfreez_details where parentid = '".$this->parentid."' ORDER BY autoid DESC LIMIT 1";
		$res = $this->execute_query($sql,$this->conn_decs);
		$row = mysql_fetch_array($res); 
		//$freeze_flag = $row['freez'];
		$freeze_reason = $row['reason'];
		$fz_reason = explode('-',$freeze_reason); 
	//	echo "<prE>";
		$freeze_arr = array();
		//$freeze_flag = 1;
		if($freeze_flag == 1 || $closedown_flag == 1)
		{	
			$freeze_arr = array('duplicate contract','outer city pincode', 'business owner decision','business shut down','invalid/junk/test','duplicate','closed down above 6 months');
			if(in_array(strtolower(trim($fz_reason['0']," ")),$freeze_arr))
			{
								
				$extra_str = "permanent freeze contract";
				$this->logmsgvirtualno("inside is_freeze_contract -".$freeze_reason,$this->log_path,$this->processName,$this->parentid,$extra_str);
				return true;
			}
			else if ($closedown_flag == 1)
			{
				$extra_str = "contract tagged for closed down";
				$this->logmsgvirtualno("inside close down check -".$closedown_flag,$this->log_path,$this->processName,$this->parentid,$extra_str);
				return true;
			}
			else
			{
				return false;
			}
		}
		else{
			return false;
		}
		
	}
	
	function sort_link_contract()
    {
        $sorted_linked_contracts =  array();
        if(count($this->linkcontracts)>0)
        {                
            $update_sort_sql =  "SELECT parentid, scheme_parentid FROM tbl_company_refer WHERE parentid IN ('". implode("','", $this->linkcontracts) ."') OR scheme_parentid IN ('" . implode("','", $this->linkcontracts) . "') ORDER BY creationDt";
            //$resupdate_sort_sql    =   $this->conn_decs->query_sql($update_sort_sql);
            //$resupdate_sort_sql = $this->execute_query($update_sort_sql,$this->conn_decs);
            $this->latestcontractid = '';
            if($resupdate_sort_sql && mysql_num_rows($resupdate_sort_sql)>0 )
            {
                $count=1;
                //$res_get_linked_contract_flag   =   $this->conn_decs->query_sql($qry_get_linked_contract_flag);
                //$res_get_linked_contract_flag   = $this->execute_query($qry_get_linked_contract_flag,$this->conn_decs);
                while($row_get_linked_contract_flag = mysql_fetch_array($resupdate_sort_sql))
                {
                    if(in_array($row_get_linked_contract_flag['parentid'],$this->linkcontracts))
                    {
                        if($this->linkcontractsvalues[$row_get_linked_contract_flag['parentid']]['sort']==0)
                        {
                            if($count==1)
                            {
                                $this->rootcontractid = $row_get_linked_contract_flag['parentid'];
                                $this->mainrootcontractid = $row_get_linked_contract_flag['parentid'];
                            }
                            $this->linkcontractsvalues[$row_get_linked_contract_flag['parentid']]['sort'] = $count++;
                            $this->latestcontractid = $row_get_linked_contract_flag['parentid'];
                            if(!in_array($row_get_linked_contract_flag['parentid'],$sorted_linked_contracts))
                            {
                                $sorted_linked_contracts[] = $row_get_linked_contract_flag['parentid'];
                            }
                        }
                    }
                    if(in_array($row_get_linked_contract_flag['scheme_parentid'],$this->linkcontracts))
                    {
                        if($this->linkcontractsvalues[$row_get_linked_contract_flag['scheme_parentid']]['sort']==0)
                        {
                            if($count==1)
                            {
                                $this->rootcontractid = $row_get_linked_contract_flag['scheme_parentid'];
                                $this->mainrootcontractid = $row_get_linked_contract_flag['scheme_parentid'];
                            }
                            $this->linkcontractsvalues[$row_get_linked_contract_flag['scheme_parentid']]['sort'] = $count++;
                            $this->latestcontractid = $row_get_linked_contract_flag['scheme_parentid'];
                            if(!in_array($row_get_linked_contract_flag['scheme_parentid'],$sorted_linked_contracts))
                            {
                                $sorted_linked_contracts[] = $row_get_linked_contract_flag['scheme_parentid'];
                            }
                        }
                    }
                }
                if(count($sorted_linked_contracts)>0)
                {
                    $this->linkcontracts = $sorted_linked_contracts;
                }
            }
            if(!$this->process_flag)
            {
                $this->latestcontractid = $this->parentid;
            }
            else
            {
                $qry_latestcontractid = "SELECT parentid FROM tbl_contract_update_trail WHERE parentid IN  ('" . implode("','", $this->linkcontracts) . "') ORDER BY update_time DESC LIMIT 1";
                //$res_latestcontractid = $this->conn_decs->query_sql($qry_latestcontractid);
                $res_latestcontractid   = $this->execute_query($qry_latestcontractid,$this->conn_decs);
                if($res_latestcontractid && mysql_num_rows($res_latestcontractid)>0)
                {
                    $row_latestcontractid = mysql_fetch_assoc($res_latestcontractid);
                    $this->latestcontractid = $row_latestcontractid['parentid'];
                }
                mysql_free_result($res_latestcontractid);
            }
        }
    }
	
	function execute_query($qry, $srv_obj)
    {
        if(trim($qry)!='' && is_object($srv_obj))
        {
            $query_result = $srv_obj->query_sql($qry);
            if(!$query_result)
            {
                die("\n<br/>Got mysql error(" . mysql_error($srv_obj->con) . ") while executing query: " . $qry);
                return false;  
            }
        }
        return $query_result;
    }
	
	function checkPaid($pid='')
	{
		if(trim($pid)=='' || intval(trim($pid))<=0)
		{
			$pid = trim($this->parentid);
		}
		
		$is_paid_contract=false;
        if(!$is_paid_contract)
        {
            $qry_paid_contract = "SELECT parentid FROM tbl_companymaster_finance WHERE parentid = '".$pid."' AND campaignid NOT IN (22, 17, 86, 87, 88, 84, 83, 82, 75, 74, 73, 72 ) LIMIT 1";
            $res_paid_contract = $this->execute_query($qry_paid_contract,$this->conn_finance);
            
            $qry_paid_contract_national = "SELECT parentid FROM db_national_listing.tbl_companymaster_finance_national WHERE parentid = '".$pid."' AND campaignid IN(10) LIMIT 1";
            $res_paid_contract_national = $this->execute_query($qry_paid_contract_national,$this->conn_idc);
            
            if(!$res_paid_contract || !$res_paid_contract_national)
            {
                die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_paid_contract);
            }
            else
            {            
                if(mysql_num_rows($res_paid_contract)>0 || mysql_num_rows($res_paid_contract_national)>0)
                {
                    $is_paid_contract = true;
                }
                mysql_free_result($res_paid_contract);
                mysql_free_result($res_paid_contract_national);
            }
        }
        return $is_paid_contract;
	}

	function checkVnEligibleCampaign($pid='')
	{
		if(trim($pid)=='' || intval(trim($pid))<=0)
		{
			$pid = trim($this->parentid);
		}
		$is_phonesearch_contract=false;
		$get_plat_package_budget = "SELECT balance,campaignid,expired FROM tbl_companymaster_finance WHERE parentid='".$pid."' AND campaignid NOT IN (22, 17, 86, 87, 88, 84, 83, 82, 75, 74, 73, 72 ) and (balance > 0 or (balance<=0 and expired=0 and manual_override=1)) order by campaignid ";
        $res_get_plat_package_budget = $this->execute_query($get_plat_package_budget,$this->conn_finance);
        
        $get_plat_package_budget_national = "SELECT balance,campaignid,expired FROM db_national_listing.tbl_companymaster_finance_national WHERE parentid='".$pid."' AND campaignid in (10) and (balance > 0 or (balance<=0 and expired=0 and manual_override=1)) order by campaignid ";
        $res_plat_package_budget_national = $this->execute_query($get_plat_package_budget_national,$this->conn_idc);
        
        if(!$res_get_plat_package_budget ||  !$res_plat_package_budget_national)
        {
            die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $get_plat_package_budget);
            return false; 
        }
        else
        {
			if( ($res_get_plat_package_budget && mysql_num_rows($res_get_plat_package_budget)>0) || ($res_plat_package_budget_national && mysql_num_rows($res_plat_package_budget_national)>0) )
			{
				$is_phonesearch_contract=true;
				/*while($row_get_plat_package_budget =  mysql_fetch_assoc($res_get_plat_package_budget))
				{
					if($row_get_plat_package_budget['campaignid']==23)
					{
						$this->checkJdfos();
						if(intval($this->JDFOS_flag)==1)
						{
							$is_phonesearch_contract=true;
						}
						//$this->JDFOS_flag = true;
					}
					else
					{
						$this->checkJdfos();
						if(intval($this->JDFOS_flag)==1)
						{
							$is_phonesearch_contract=true;
						}
					}
				}*/
			}
		}
		return $is_phonesearch_contract;
	}

	function checkPincodeFlag()
	{
		$is_pincode_present = false;
		if(intval(trim($this->pincode))>0)
		{
			$qry_check_pincode_incity = "SELECT display_flag  FROM ".DB_JDS_LIVE.".tbl_area_master WHERE pincode= '" . $this->pincode . "' and data_city = '".$this->city_vn ."' AND display_flag  = 1  LIMIT 1";
			//$res_check_pincode_incity = $this->conn_iro->query_sql($qry_check_pincode_incity);
			$res_check_pincode_incity = $this->execute_query($qry_check_pincode_incity,$this->conn_iro);
			if($res_check_pincode_incity)
			{
				if(mysql_num_rows($res_check_pincode_incity) > 0)
				{
					$is_pincode_present = true;
				}
				mysql_free_result($res_check_pincode_incity);
			}
			else
			{
				die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_check_pincode_incity);
			}
		}
		return $is_pincode_present;
	}
	
	function cron_dealloc_virtualno($cron_parentid)
    {
		
		$this->getinitailvalue();	
		
		$sql_area ="SELECT stdcode,data_city FROM d_jds.tbl_city_master WHERE ct_name = (SELECT DISTINCT data_city FROM d_jds.tbl_areamaster_consolidated_v3 WHERE pincode='".$this->pincode."' AND display_flag=1  LIMIT 1) and display_flag=1";
		
		$res_area = $this->execute_query($sql_area,$this->conn_iro);
		$row_area = mysql_fetch_array($res_area);
		$std =  ltrim($row_area['stdcode'],'0');
		$virtualNo = $this->virtualno;
				
		 
		/*if(strtoupper($this->city_vn) == 'CHANDIGARH')
		{
			$std = '172';
		}  
		if(strtoupper($this->city_vn) == 'COIMBATORE')
		{
			$std = '422';
		}
		if(strtoupper($this->city_vn) == 'INDORE')
		{
			$std = '731';
		}  
		*/
		$countrycode = 91;
		
		if(in_array(strtoupper($this->city_vn),$this->reliance_main_city)){
			if(in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
			{
				$curl_url = $this->techinfo_url."action=vmDelete&vmnNumber=".$countrycode.$std.trim($this->virtualno);
			}
			else if(!in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
			{
			$curl_url = $this->techinfo_url."actualnumber/virtualnumber/".$countrycode.$std.$virtualNo."/user/Jack";
			}
		}
		else if(in_array(strtoupper($this->city_vn),$this->reliance_remote_city)) {
		
			if(in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
			{
				$curl_url = $this->rcom_remote_url."action=vmDelete&vmnNumber=".$countrycode.$std.trim($this->virtualno);
			}
			else if(!in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
			{
				$curl_url = $this->rcom_remote_url."actualnumber/virtualnumber/".$countrycode.$std.$virtualNo."/user/Jack";	
			}
		
		}	
		else if(!in_array(strtoupper($this->city_vn),$this->remote_city_arr) && !in_array(strtoupper($this->city_vn),$this->main_city_arr))
		{
			$curl_url = $this->techinfo_url."deallocate.php?VN=".trim($this->virtualno)."&User=".$this->usercode;
			$curl_res = $this->runTechInfoCurlUrl($curl_url);
			
			
			if($this->curl_response_flag)
			{
				$insertarr	= array();
				$insertarr['tbl_companymaster_generalinfo']	= array("virtualNumber" => '',"virtual_mapped_number" => '',"pri_number" => '',"parentid"=>$this->parentid);
				$this->compmaster_obj->UpdateRow($insertarr);
				
				$this->updateCompanyMasterSearch();
				$this->DialableNumberClass->upDateDialableNumber($this->parentid,$this->conn_iro);
	
				//$sqlDeleteMapdetails = "DELETE FROM mapdetails WHERE LContract = '".$this->parentid."'";
				//$resDeleteMapDetails = $this->execute_query($sqlDeleteMapdetails,$this->mapdetails_conn);
	
				$this->reason = 'Virtual Number Deallocated';
				$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."]";
				$this->logmsgvirtualno("Ineligible Contract Found.",$this->log_path,$this->processName,$this->parentid,$extra_str);
				if($this->jdfos_pid_chk_flag == true)
				{
					$this->updateJdfosDb();
				}
				//if($this->quarantineFlag)
				//{
				$qry_update_quarantine_tbl = "UPDATE d_jds.tbl_quarantine_virtualnumber set active_flag = 0 ,end_date='".date('Y-m-d H:i:s')."',update_date='".date('Y-m-d H:i:s')."' where businessid ='".trim($this->parentid)."' and active_flag = 1";
				$res_update_quarantine_tbl = $this->execute_query($qry_update_quarantine_tbl,$this->conn_decs);	
				$extra_str="[Virtual number :".$this->virtualno."][qry : ".$qry_update_quarantine_tbl."] [qry result : ".$res_update_quarantine_tbl."]";
				$this->logmsgvirtualno("Deactivate quarantine flag - ".$this->reason."",$this->log_path,$this->processName,$this->parentid,$extra_str);
				
				
				//	rmv_quaran_blockforvncat($this->parentid,$this->virtualno,"Virtual Number Deallocated",$this->log_path,$this->processName,$this->usercode,$this->conn_decs);
				//}
			}
			
			return 'called';
		}	
		
		//$curl_res = $this->runCurlUrl($curl_url,'DELETE');
        
			/*if(!$this->curl_response_flag)
			{
				$extra_str="[Reliance url not working for deallocation][url :".$curl_url."]";
				$this->logmsgvirtualno("Deallocating virtual number in reliance",$this->log_path,$this->processName,$this->parentid,$extra_str);
				return 'failed';
			}
			else
			{
				$relianceOutput = json_decode($curl_res,true);
				
				if($relianceOutput['status'] == '200')
				{
					$extra_str="[Deallocating virtual number]";	
					$this->logmsgvirtualno("Deallocate VN",$this->log_path,$this->processName,$this->parentid,$extra_str);
					
					$insertarr['tbl_companymaster_generalinfo']	= array("virtualNumber" => '',"dialable_virtualnumber" => '',"virtual_mapped_number" => '',"pri_number" => '',"parentid"=>$this->parentid);
					
					$this->compmaster_obj->UpdateRow($insertarr);
					$this->updateCompanyMasterSearch($this->parentid);
					$this->reason = 'deallocation';
					$this->insert_narration($this->reason);
					return 'called';
				}
				else
				{
					$extra_str="[Deallocating virtual number]";	
					$this->logmsgvirtualno("error while Deallocate VN",$this->log_path,$this->processName,$this->parentid,$extra_str);
					$this->reason = 'error while Deallocation';
					$this->insert_narration($this->reason);
					return 'failed';
				}
			}*/
    }
	
	function getMappednumber()
	{
		$get_next_mapped = array();
		$isMappedNopresent = false;
        $this->getSingleMappednumber($this->parentid);
        if($this->linkcontract_flag)
        {
            if(count($this->finalmappednumbers)< self::TOTAL_REQUIRE_MAPPEDNO)
            {
                $get_next_mapped			= $this->getEligibleLinkcontract();
                $this->finalmappednumbers	= array_merge($this->finalmappednumbers,$get_next_mapped);
                $this->finalmappednumbers	= array_unique($this->finalmappednumbers);
                $this->finalmappednumbers	= array_merge($this->finalmappednumbers);
                if(count($this->finalmappednumbers) >= self::TOTAL_REQUIRE_MAPPEDNO)
				{
					$this->finalmappednumbers = array_slice($this->finalmappednumbers,0,self::TOTAL_REQUIRE_MAPPEDNO);
				}
			}
		}
		$extra_str="[all eligible mapped no.(without stdcode) :".implode(",",$this->top_mappednumber_withoutstd)."][[all eligible mapped no.(with stdcode):".implode(",",$this->finalmappednumbers)."]";
        $this->logmsgvirtualno("All mapped number ",$this->log_path,$this->processName,$this->parentid,$extra_str);
		if(count($this->finalmappednumbers)>0)
		{
			$isMappedNopresent = true;
		}
		return $isMappedNopresent;
	}
	
	function getSingleMappednumber($pid)
	{

		$temparr	= array();
		$contactpArr = array();
		$fieldstr	= "stdcode, landline_display, mobile_display, tollfree, landline, mobile,virtualNumber,companyname,mobile_feedback,email_feedback,pincode,area,contact_person_display";
		$where 		= "parentid='".$pid."'";
		$res_get_all_contact_number	= $this->compmaster_obj->getRow($fieldstr,"tbl_companymaster_generalinfo",$where);

        if($res_get_all_contact_number['numrows']>0)
        {
            $row_get_all_contact_number = $res_get_all_contact_number['data']['0'];
            $contract_stdcode= $row_get_all_contact_number['stdcode'];
            $this->contact_arr['landline_display'] = $row_get_all_contact_number['landline_display'];
            $this->contact_arr['mobile_display']   = $row_get_all_contact_number['mobile_display'];
            $this->contact_arr['tollfree']         = $row_get_all_contact_number['tollfree'];
            $this->contact_arr['landline']         = $row_get_all_contact_number['landline'];
            $this->contact_arr['mobile']           = $row_get_all_contact_number['mobile'];
            $this->contact_arr['mobile_feedback'] = $row_get_all_contact_number['mobile_feedback'];
            //$this->virtualno            = $row_get_all_contact_number['virtualNumber'];
            $this->companyname          = stripslashes(stripslashes($row_get_all_contact_number['companyname']));
            $this->mobile_feedback      = $row_get_all_contact_number['mobile_feedback'];
            $this->email_feedback       = $row_get_all_contact_number['email_feedback'];
            $this->stdcode              = $row_get_all_contact_number['stdcode'];
            $this->pincode              = $row_get_all_contact_number['pincode'];
            $this->area                 = $row_get_all_contact_number['area'];
            if($row_get_all_contact_number['contact_person_display']!=''){
				$contactpArr = explode(",",$row_get_all_contact_number['contact_person_display']);
				$contactpArr = array_merge(array_filter($contactpArr));
				$this->contractperson_arr = $contactpArr;
			}
            if(trim($this->stdcode)=='' || intval(trim($this->stdcode))=='0')
            {
                $this->stdcode = $this->getStdcode();
            }
            $this->getTopMappednumberWithoutstd();
            if(in_array(strtoupper($this->city_vn),$this->reliance_main_city)){
				$this->getTopMappednumber(1); 
			}else{
				$this->getTopMappednumber(1); 
			}
        }
	}
	
	function getStdcode()
    {
        $qry_get_stdcode="SELECT stdcode FROM d_jds.tbl_area_master WHERE pincode='".trim($this->pincode)."' AND area='".trim($this->area)."' AND display_flag=1 AND deleted=0 LIMIT 1";
        $res_get_stdcode = $this->execute_query($qry_get_stdcode,$this->conn_decs);
        if($res_get_stdcode && mysql_num_rows($res_get_stdcode)>0)
        {
            $row_get_stdcode = mysql_fetch_assoc($res_get_stdcode);
            $new_stdcode = $row_get_stdcode['stdcode'];
        }
        return $new_stdcode;
    }
    
    function getTopMappednumberWithoutstd()
    {
		if($this->shopfront_flag==1){
			$this->contacts_mode            = array('mobile_feedback','mobile_display','landline_display','tollfree','landline','mobile');
			$this->uniqueMobile();
		}
		foreach ($this->contacts_mode as $contact_mode)
		{
			switch($contact_mode)
			{
				case 'landline_display':$this->landline   =   $this->convert_contactnos_arr($this->contact_arr[$contact_mode]); 
								break;
				case 'mobile_display'  :$this->mobile   =   $this->convert_contactnos_arr($this->contact_arr[$contact_mode]);
								/*$this->mobile=$this->get_not_dnc_mobile($this->mobile);*//*remove this because allow DNC number as virtual mapeed number*/
								break;
				case 'tollfree'  :$this->tollfree   =   $this->convert_contactnos_arr($this->contact_arr[$contact_mode]);
								/*$this->mobile=$this->get_not_dnc_mobile($this->mobile);*//*remove this because allow DNC number as virtual mapeed number*/
								break;
				case 'mobile_feedback': $this->fbmobile   =   $this->convert_contactnos_arr($this->contact_arr[$contact_mode]);
								break;
			}
		}
		if($this->shopfront_flag==1){
			if(count($this->fbmobile)>0){
				$this->top_mappednumber_withoutstd= array_merge($this->fbmobile,$this->mobile,$this->landline,$this->tollfree);
			}else{
				$this->top_mappednumber_withoutstd= array_merge($this->landline,$this->mobile,$this->tollfree);
			}
		}else{
			$this->top_mappednumber_withoutstd= array_merge($this->landline,$this->mobile,$this->tollfree);
		} 
	}
	
	function uniqueMobile(){
		$mbArr = $this->convert_contactnos_arr($this->contact_arr['mobile_display']);
		$fbmbArr =  $this->convert_contactnos_arr($this->contact_arr['mobile_feedback']);
		if(count($fbmbArr)>0){
			foreach($fbmbArr as $fbkey => $fbval){
				if(in_array($fbval,$mbArr)){
					$key='';
					$key = array_search($fbval, $mbArr);
					unset($mbArr[$key]);
				}
			}
			$mbArr = array_merge(array_filter($mbArr));
		}
		/*if(count($mbArr)>0){
			$this->contact_arr['mobile_display'] = implode(",",$mbArr);
		}*/
		$this->contact_arr['mobile_display'] = implode(",",$mbArr);
	}
	
	function convert_contactnos_arr($str)
    {
        $new_contact_arr = array();
        if(trim($str)!='')
        {
            $contact_array = explode(",",trim(trim($str),","));
            $contact_array = array_filter($contact_array);
            $contact_array = array_merge($contact_array);
            foreach($contact_array as $string)
            {
                $number = preg_replace("/[^ 0-9 ]/", '', $string);
                $exp_contact_arr=explode(" ",trim($number,' '));
                $exp_contact_arr=array_filter($exp_contact_arr);
                $exp_contact_arr=array_merge($exp_contact_arr);    
                foreach($exp_contact_arr as $num)
                {
                    if(strlen(trim($num))>=6)
                    {
                        $new_contact_arr[]=trim($num);
                        break;
                    }
                }
            }
            $new_contact_arr = array_unique($new_contact_arr);
            $new_contact_arr = array_merge(array_filter($new_contact_arr));
        }
        return $new_contact_arr;
    }
    
    function checkQuarantine()
    {
		/*if($this->linkcontract_flag)
        {
            $quarantine_parentid = $this->mainrootcontractid;
        }
        else
        {
            $quarantine_parentid = $this->parentid;
        }*/
        $quarantine_parentid = $this->parentid;
        $qry_sel_quarantine_tbl = "SELECT vno,businessid,active_flag FROM tbl_quarantine_virtualnumber WHERE businessid = '".trim($quarantine_parentid)."' AND active_flag = 1 AND  end_date = '0000-00-00 00:00:00' AND vno!=0 ORDER BY update_date DESC LIMIT 1"; 
        $res_sel_quarantine_tbl = $this->conn_decs->query_sql($qry_sel_quarantine_tbl);
        if(!$res_sel_quarantine_tbl)
        {
            die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_sel_quarantine_tbl);
            return false;
        }
        else
        {
            if(mysql_num_rows($res_sel_quarantine_tbl)<=0)
            {
                $this->quarantineEligibility = false;
            }
            elseif(mysql_num_rows($res_sel_quarantine_tbl)>0)
            {
                $row_sel_quarantine_tbl = mysql_fetch_assoc($res_sel_quarantine_tbl);
                if(trim($this->virtualno)=='' || intval($this->virtualno)<=0)
                {
                    $this->virtualno = $row_sel_quarantine_tbl['vno'];
                    $get_arry_str_1 ='';
                    $get_arry_str_1 = array_map(create_function('$key, $value', 'return $key.":".$value." # ";'), array_keys($row_sel_quarantine_tbl), array_values($row_sel_quarantine_tbl)); 
                    $extra_str="[This contract is present in quaratine table.][Virtual number (after checking quarantine table):".$this->virtualno."][all information : ".implode("#",$get_arry_str_1)."]";
                    $this->logmsgvirtualno("Quarantine table details",$this->log_path,$this->processName,$this->parentid,$extra_str);
                }
            }
        }
	}
	
	function getTopMappednumber($stdflag=0)
	{
		$fbmobile_cnt = 0;
		$req_fbmobile_no = array();
		if($this->shopfront_flag==1){
			$this->contacts_mode            = array('mobile_feedback','landline_display','mobile_display','tollfree','landline','mobile');
			//$this->uniqueMobile();
		}
		foreach ($this->contacts_mode as $contact_mode)
		{
			switch($contact_mode)
			{
				case 'landline_display':
								$this->landline=  $this->convert_contactnos_arr($this->contact_arr[$contact_mode]); 
								$landline_cnt = count($this->landline);
								if($landline_cnt>0 && intval($stdflag)==1)
								{
									$this->landline = $this->concat_stdcode_zero($this->landline);
								}
								break;
				case 'mobile_display'  :
								$this->mobile =$this->convert_contactnos_arr($this->contact_arr[$contact_mode]);
								$mobile_cnt = count($this->mobile);
								if($mobile_cnt>0 && intval($stdflag)==1)
								{
									$this->mobile = $this->concat_stdcode_zero($this->mobile);
								}
								/*$this->mobile=$this->get_not_dnc_mobile($this->mobile);*//*remove this because allow DNC number as virtual mapeed number*/
								break;
				case 'tollfree' :
								$this->tollfree   =   $this->convert_contactnos_arr($this->contact_arr[$contact_mode]);
								$tollfree_cnt = count($this->tollfree);
								break;
				case 'mobile_feedback': 
								$this->fbmobile   =   $this->convert_contactnos_arr($this->contact_arr[$contact_mode]);
								$fbmobile_cnt = count($this->fbmobile);
								if($fbmobile_cnt>0 && intval($stdflag)==1)
								{
									$this->fbmobile = $this->concat_stdcode_zero($this->fbmobile);
								}
								break;
			}
		}
		
		$req_landline_cnt = 4;
        $req_mobile_cnt = 4;
        if($landline_cnt<$req_landline_cnt || ($mobile_cnt+$fbmobile_cnt)<$req_mobile_cnt)
        {
            if(($mobile_cnt+$fbmobile_cnt)>=$req_mobile_cnt && $landline_cnt<$req_landline_cnt)
            {
                $req_mobile_cnt += ($req_landline_cnt - $landline_cnt);
                $req_landline_cnt = $landline_cnt;
                if(($req_landline_cnt+$req_mobile_cnt)>self::TOTAL_REQUIRE_MAPPEDNO)
                {
                    $req_mobile_cnt = self::TOTAL_REQUIRE_MAPPEDNO - $req_landline_cnt;

                }
                if($req_mobile_cnt>($mobile_cnt+$fbmobile_cnt))
                {
                    $req_mobile_cnt=$mobile_cnt;
                }
            }
            elseif(($mobile_cnt+$fbmobile_cnt)<$req_mobile_cnt && $landline_cnt>=$req_landline_cnt)
            {
                $req_landline_cnt += ($req_mobile_cnt - ($mobile_cnt+$fbmobile_cnt));
                $req_mobile_cnt = ($mobile_cnt+$fbmobile_cnt);
                if(($req_landline_cnt+$req_mobile_cnt)>self::TOTAL_REQUIRE_MAPPEDNO)
                {
                    $req_landline_cnt = self::TOTAL_REQUIRE_MAPPEDNO - $req_mobile_cnt;

                }
                if($req_landline_cnt>$landline_cnt)
                {
                    $req_landline_cnt=$landline_cnt;
                }
            }
            else
            {
                $req_landline_cnt = $landline_cnt;
                $req_mobile_cnt = ($mobile_cnt+$fbmobile_cnt);
            }
        }
        if(is_array($this->landline))
        {
            $req_lanline_no = array_slice($this->landline,0,$req_landline_cnt);
        }
        else
        {
            $req_lanline_no = array();
        }
        if(is_array($this->mobile))
        {
			if($this->shopfront_flag==1){
				$req_fbmobile_no = array_slice($this->fbmobile,0,$req_mobile_cnt);
				$otherNonfbMbcout = $req_mobile_cnt - count($req_fbmobile_no);
				$req_nonfbmobile_no = array_slice($this->mobile,0,$otherNonfbMbcout);
				$req_mobile_no = array_merge($req_fbmobile_no,$req_nonfbmobile_no);
			}else{
				$req_mobile_no = array_slice($this->mobile,0,$req_mobile_cnt);
			}
        }
        else
        {
            $req_mobile_no = array();
        }
        if($this->shopfront_flag==1){
			if(count($this->fbmobile)>0){
				$top_eight_array = array_merge($req_mobile_no,$req_lanline_no); 
			}else{
				$top_eight_array = array_merge($req_lanline_no,$req_mobile_no);
			}
		}else{
			$top_eight_array = array_merge($req_lanline_no,$req_mobile_no); 
		}       
        
        $without_space_top_eight_array = array();
        foreach($top_eight_array as $number)
        {
            $number = explode(" ", trim($number));
            $without_space_top_eight_array[] = $number[0];
        }        
        $top_eight_array = $without_space_top_eight_array;
        if(count($top_eight_array)<= self::TOTAL_REQUIRE_MAPPEDNO && $tollfree_cnt >0)
        {
            $new_get_top_eight_contcts = array();
            $req_tollfree_num = self :: TOTAL_REQUIRE_MAPPEDNO - count($top_eight_array);
            $req_tollfree_no = array_slice($this->tollfree,0,$req_tollfree_num);
            $new_get_top_eight_contcts = array_merge($top_eight_array,$req_tollfree_no);
            $top_eight_array = $new_get_top_eight_contcts;
        }
        
        $this->finalmappednumbers = $top_eight_array;
	}
	
	function concat_stdcode_zero($contacts)
    {
        $new_contacts = array();
        if(!is_array($contacts))
        {
            $contacts = array($contacts);
        }
        foreach($contacts as $contact)
        {
            if(trim($contact)!='')
            {
                if(strlen($contact)<10)
                {
					if((in_array(strtoupper($this->city_vn),$this->reliance_main_city)) || (in_array(strtoupper($this->city_vn),$this->reliance_remote_city))){
						$contact = $this->country_code.ltrim($this->stdcode,'0').ltrim($contact, '0');
					}else{
						$contact = $this->stdcode.ltrim($contact, '0');
					}
                }else{
					if((in_array(strtoupper($this->city_vn),$this->reliance_main_city)) || (in_array(strtoupper($this->city_vn),$this->reliance_remote_city))){
						$contact = $this->country_code.ltrim($contact, '0');
					}
				}
                if(trim(trim(trim($contact), '0'))!='')
                {
					if((!in_array(strtoupper($this->city_vn),$this->reliance_main_city)) && (!in_array(strtoupper($this->city_vn),$this->reliance_remote_city))){
						$contact = '0'.ltrim($contact, '0');
					}
                }
                else
                {
					if((!in_array(strtoupper($this->city_vn),$this->reliance_main_city)) && (!in_array(strtoupper($this->city_vn),$this->reliance_remote_city))){
						$contact = trim(trim(trim($contact), '0'));
					}
                }
                $new_contacts[] = $contact;
            }
        }
        return $new_contacts;
    }
    
    function getEligibleLinkcontract()
    {
		$additionalMappedNo = array();
		$additionalMappedNoWithoutStd = array();

		$temparr	= array();
		$fieldstr	= "parentid,landline_display,mobile_display,tollfree,pincode,area,stdcode";
		$linkcontracts_pid_str = implode("','",$this->linkcontracts);
		$where 		= "parentid IN ('".$linkcontracts_pid_str."') and parentid !='".$this->parentid."' and virtualnumber>0";
		$ResGetLinkInfo	= $this->compmaster_obj->getRow($fieldstr,"tbl_companymaster_generalinfo",$where);


		if(!$ResGetLinkInfo)
		{
			die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $QryGetLinkInfo);
		}
		else
		{
			if($ResGetLinkInfo['numrows']>0)
			{
				foreach($ResGetLinkInfo['data'] as $RowGetLinkInfo)
				{
					$landlinestr		= '';
					$mobilestr 			= '';
					$tollfreestr 		='';
					$addLandlineArr 	= array();
					$addLandlineArrstd 	= array();
					$addMobileArr 		= array();
					$addMobileArrstd 	= array();
					$addTollfreeArr 	= array();
					$landlinestr 		= $RowGetLinkInfo['landline_display'];
					if($landlinestr!='')
					{
						$addLandlineArr 	= $this->convert_contactnos_arr($landlinestr);
						$addLandlineArrstd 	= $this->concat_stdcode_zero($addLandlineArr);
					}
					$mobilestr 		= $RowGetLinkInfo['mobile_display'];
					if(!$mobilestr)
					{
						$addMobileArr		= $this->convert_contactnos_arr($mobilestr);
						$addMobileArrstd	= $this->concat_stdcode_zero($addMobileArr);
					}
					$tollfreestr	= $RowGetLinkInfo['tollfree'];
					if(!$tollfreestr)
					{
						$addTollfreeArr = $this->convert_contactnos_arr($tollfreestr);
					}
					$additionalMappedNoWithoutStd	= array_merge($additionalMappedNo,$addLandlineArr,$addMobileArr,$addTollfreeArr);
					$additionalMappedNo				= array_merge($additionalMappedNo,$addLandlineArrstd,$addMobileArrstd,$addTollfreeArr);
				}
				if(count($additionalMappedNoWithoutStd)>0)
				{
					$additionalMappedNoWithoutStd 		= array_filter($additionalMappedNoWithoutStd);
					$additionalMappedNoWithoutStd 		= array_unique($additionalMappedNoWithoutStd);
					$additionalMappedNoWithoutStd 		= array_merge($additionalMappedNoWithoutStd);
					$this->top_mappednumber_withoutstd	= array_merge($this->top_mappednumber_withoutstd,$additionalMappedNoWithoutStd);
					$additionalMappedNo					= array_filter($additionalMappedNo);
					$additionalMappedNo					= array_unique($additionalMappedNo);
					$additionalMappedNo					= array_merge($additionalMappedNo);
				}
				//mysql_free_result($ResGetLinkInfo);
				unset($RowGetLinkInfo);
			}
		}
		return $additionalMappedNo;
	}
	
	function freshAllocation()
	{	
		$businessid = '';
		$this->status='A';  
		if($this->linkcontract_flag)
        {
			$businessid = $this->mainrootcontractid;
		}
		else
		{
			$businessid = $this->parentid;
		}
		if(in_array(strtoupper($this->city_vn),$this->main_city_arr))
		{
			$this->techInfoPaidStatus();
			if(in_array(strtoupper($this->city_vn),$this->reliance_main_city)){
				//$curl_url = $this->techinfo_url."mapping";
				if(in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
				{
					$curl_url = $this->techinfo_url."action=allocate";
				}
				else if(!in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
				{
					$curl_url = $this->techinfo_url."singletxn/allocate";
				}
				$postarr = $this->makeRelianceUrl($businessid); 
				$curl_output = $this->runCurlUrl($curl_url,'POST',$postarr);
			}else{
				$curl_url = $this->techinfo_url."allocate.php?";
				$urlEnd = $this->makeTechinfoUrl($businessid);
				$curl_url = $curl_url.$urlEnd;
				$curl_output = $this->runTechInfoCurlUrl($curl_url);
			}
			//$curl_output = '{"error":"The request has succeeded","status":200,"data":[{"virtual Number":"912230071817"}]}';
			if(!$this->curl_response_flag)
			{
				$logtbl_process_flag=1;
				$logtbl_reason = "Rcom url not working";
				$process_name = "Allocation API not working";
				$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
				$extra_str="[techinfo url not working][url :".$curl_url."]";
				$this->logmsgvirtualno("allocate new virtual number contract not having virtual number",$this->log_path,$this->processName,$this->parentid,$extra_str);
			}
			else
			{
				if(in_array(strtoupper($this->city_vn),$this->reliance_main_city)){
					if(in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
					{
						$techinfo_vrn = $this->readRelianceJson($curl_output,1);
					}
					else if(!in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
					{
						$techinfo_vrn = $this->readRelianceJson($curl_output);
					}
				}else{
					$techinfo_vrn = $this->readTechinfoXml($curl_output);
				}
				$this->virtualno = $techinfo_vrn;
			}
		}
		else if(in_array(strtoupper($this->city_vn),$this->remote_city_arr))
		{
			$this->techInfoPaidStatus();
			if(in_array(strtoupper($this->city_vn),$this->reliance_remote_city))
			{
				if(in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
				{
					$curl_url = $this->rcom_remote_url."action=allocate";
				}
				else if(!in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
				{
					$curl_url = $this->rcom_remote_url."singletxn/allocate";
				}
				$postarr = $this->makeRelianceUrl($businessid); 
				$curl_output = $this->runCurlUrl($curl_url,'POST',$postarr);
			}
			else
			{
				$curl_url = $this->techinfo_url."allocate.php?";
				$urlEnd = $this->makeTechinfoUrl($businessid);
				$curl_url = $curl_url.$urlEnd;
				$curl_output = $this->runTechInfoCurlUrl($curl_url);
			}
				if(!$this->curl_response_flag)
				{
					$logtbl_process_flag=1;
					$logtbl_reason = "rcom remote city url not working";
					$process_name = "allocate new virtual number contract not having virtual number";
					$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
					$extra_str="[rcom remote city url not working][url :".$curl_url."]";
					$this->logmsgvirtualno("allocate new virtual number contract not having virtual number",$this->log_path,$this->processName,$this->parentid,$extra_str);
				}
				else
				{
				if(in_array(strtoupper($this->city_vn),$this->reliance_main_city)){
					if(in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
						{
							$remote_city_vrn = $this->readRelianceJson($curl_output,1);
						}
					else if(!in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
						{
							$remote_city_vrn = $this->readRelianceJson($curl_output);
						}
					}else
						{
							$remote_city_vrn = $this->readTechinfoXml($curl_output);
						}
					$this->virtualno = $remote_city_vrn;	
				
				}			
		}
		else if(!in_array(strtoupper($this->city_vn),$this->remote_city_arr) && !in_array(strtoupper($this->city_vn),$this->main_city_arr))
		{
			$this->techInfoPaidStatus();
			
				$curl_url = $this->techinfo_url."allocate.php?";
				$urlEnd = $this->makeTechinfoUrl($businessid);
				$curl_url = $curl_url.$urlEnd;
				$curl_output = $this->runTechInfoCurlUrl($curl_url);
				if(!$this->curl_response_flag)
				{
					$logtbl_process_flag=1;
					$logtbl_reason = "rcom remote city url not working";
					$process_name = "allocate new virtual number contract not having virtual number";
					$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
					$extra_str="[rcom remote city url not working][url :".$curl_url."]";
					$this->logmsgvirtualno("allocate new virtual number contract not having virtual number",$this->log_path,$this->processName,$this->parentid,$extra_str);
				}
				
				else
				{
				
					$remote_city_vrn = $this->readTechinfoXml($curl_output);
					$this->virtualno = $remote_city_vrn;	
				
				}	
		}
		if($this->virtualno)
		{
			$qry="SELECT campaignid FROM tbl_companymaster_finance WHERE parentid='".$this->parentid."'";
			$result=$this->execute_query($qry,$this->conn_finance);
			$campaignid=array();
			$i=0;
				while($row = mysql_fetch_row($result)) 
				{
					$campaignid[$i]=$row[0];
					$i++;
				} 
			//	 echo"<pre>";print_r($campaignid);die();
			$campaignNamearray=array();
				for($i=0;$i<sizeof($campaignid);$i++)
				{
					if($campaignid[$i] == 1 || $campaignid[$i] == 2 || $campaignid[$i] == 23 || $campaignid[$i] == 29)
					{
						$qry="SELECT campaignName FROM payment_campaign_master WHERE campaignId='".$campaignid[$i]."'";
						$result=$this->execute_query($qry,$this->conn_finance);
						$row = mysql_fetch_row($result);
						$campaignNamearray[$i] =$row[0];
					}
				}
			//  		echo"<pre>";print_r($campaignNamearray);
			$campaignName=implode(",",$campaignNamearray);	    
			// 		echo $campaignid;
			$qry1= "INSERT INTO d_jds.tbl_virtual_allocation SET contractid='".$this->parentid."',entry_date=NOW(), virtual_number='".$this->virtualno."',contract_type='".$campaignName."'";
			$result2 = $this->execute_query($qry1,$this->conn_iro);
		}
		
		return $this->virtualno;
	}

	function updateJdfosDb($jdfosVno=0)
	{
		$jdfocUpdateflag = false;
		$extra_str="[virtual number : ".$jdfosVno."]";
		$this->logmsgvirtualno("Log virtual number in jdfos update function",$this->log_path,$this->processName,$this->parentid,$extra_str);
		$arr_save_rest_dealclose		=	$this->obj_rest->fetch_rest_dealclose(0);
		
		$jdfos_json = json_decode($arr_save_rest_dealclose,true); 
		if($jdfos_json['results']['errorCode']==0){
			$jdfocUpdateflag = true;
		}
		$extra_str="[array return :".$jdfos_json['results']['errorMsg']."][code:".$jdfos_json['results']['errorCode']."]";
		$this->logmsgvirtualno("JDFOS url response",$this->log_path,$this->processName,$this->parentid,$extra_str);
		return $jdfocUpdateflag;
	}
	
	function updateMapping()
	{
		if(in_array(strtoupper($this->city_vn),$this->main_city_arr))
		{
			$this->techInfoPaidStatus();
			$this->techInfo_array = $this->vnSearch();
			if(count($this->techInfo_array)>0)
			{
				if(trim($this->techInfo_array['BusinessId'])==trim($this->parentid))
				{
					$this->status='A';
					if(in_array(strtoupper($this->city_vn),$this->reliance_main_city)){
						//$curl_url = $this->techinfo_url."mapping/9122".trim($this->virtualno);
						//$curl_url = $this->techinfo_url."mapping/".$this->CountryStdCode.trim($this->virtualno);
						if(in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
						{
							$curl_url = $this->techinfo_url."action=modify&vmn=".$this->CountryStdCode.trim($this->virtualno);
						}
						else if(!in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
						{
							$curl_url = $this->techinfo_url."singletxn/modify/".$this->CountryStdCode.trim($this->virtualno);
						}
						$postarr = $this->makeRelianceUrl(trim($this->parentid));
						$curl_output = $this->runCurlUrl($curl_url,'PUT',$postarr);
					}else{
						$curl_url = $this->techinfo_url."allocate.php?VN=".trim($this->virtualno)."&";
						$urlEnd = $this->makeTechinfoUrl(trim($this->parentid));
						$curl_url = $curl_url.$urlEnd;
						$curl_output = $this->runTechInfoCurlUrl($curl_url,$url_type); 
					}
					if(!$this->curl_response_flag)
					{
						$logtbl_process_flag=1;
						$logtbl_reason = "techinfo url not working";
						$process_name = "update new data for present virtual number";
						$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
						$extra_str="[techinfo url not working][API URL :".$curl_url."][API OUTPUT :".$curl_output."]";
						$this->logmsgvirtualno("Allocate virtual number using techinfo",$this->log_path,$this->processName,$this->parentid,$extra_str);
					}
					else
					{
						if(in_array(strtoupper($this->city_vn),$this->reliance_main_city)){
							$techinfo_vrn = $this->readRelianceJson($curl_output,1);
						}else{
							$techinfo_vrn = $this->readTechinfoXml($curl_output);
						}
						$this->virtualno = $techinfo_vrn;
						$extra_str="[API : ".$curl_url."][POST Arr : ".json_encode($postarr)."][API OUTPUT :".$curl_output."][vn : ".$techinfo_vrn."][Virtual Number :".$this->virtualno."]";
						$this->logmsgvirtualno("Update actual number IN VN API",$this->log_path,$this->processName,$this->parentid,$extra_str);
					}
				}
				else
				{
					$postarr = array();
					$extra_str="[techinfo businessid : ".$this->techInfo_array['BusinessId']."][given parentid :".$this->parentid."]";
					$this->logmsgvirtualno("Techinfo business id is not same as given parentid",$this->log_path,$this->processName,$this->parentid,$extra_str);
					if($this->linkcontract_flag)
					{
						if(in_array(trim($this->techInfo_array['BusinessId']),$this->linkcontracts))
						{
							$extra_str="[techinfo businessid : ".$this->techInfo_array['BusinessId']."][linkcontract array :".implode(",",$this->linkcontracts)."]";
							$this->logmsgvirtualno("Techinfo businessid is present in linkcontract array",$this->log_path,$this->processName,$this->parentid,$extra_str);
							$this->status='A';
							
							if(in_array(strtoupper($this->city_vn),$this->reliance_main_city)){
								//$curl_url = $this->techinfo_url."mapping/9122".trim($this->virtualno);
								//$curl_url = $this->techinfo_url."mapping/".$this->CountryStdCode.trim($this->virtualno);
								$curl_url = $this->techinfo_url."singletxn/modify/".$this->CountryStdCode.trim($this->virtualno);
								$postarr = $this->makeRelianceUrl(trim($this->techInfo_array['BusinessId']));
								$curl_output = $this->runCurlUrl($curl_url,'PUT',$postarr);
							}else{
								$curl_url = $this->techinfo_url."allocate.php?VN=".trim($this->virtualno)."&";
								$urlEnd = $this->makeTechinfoUrl(trim($this->techInfo_array['BusinessId']));
								$curl_url = $curl_url.$urlEnd;
								$curl_output = $this->runTechInfoCurlUrl($curl_url,$url_type);
							}
							if(!$this->curl_response_flag)
							{
								$logtbl_process_flag=1;
								$logtbl_reason = "techinfo url not working";
								$process_name = "update new data for present virtual number";
								$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
								$extra_str="[techinfo url not working][url :".$curl_url."]";
								$this->logmsgvirtualno("Allocate virtual number using techinfo",$this->log_path,$this->processName,$this->parentid,$extra_str);
							}
							else
							{
								if(in_array(strtoupper($this->city_vn),$this->reliance_main_city)){
									$techinfo_vrn = $this->readRelianceJson($curl_output,1);
								}else{
									$techinfo_vrn = $this->readTechinfoXml($curl_output);
								}
								$this->virtualno = $techinfo_vrn;
								$extra_str="[API OUTPUT :".$curl_url."][vn : ".$techinfo_vrn."][Virtual Number :".$this->virtualno."]";
								$this->logmsgvirtualno("Update actual number IN VN API",$this->log_path,$this->processName,$this->parentid,$extra_str);
							}
						}
						else
						{
							$message = "<br>ParentId = " . $this->parentid . "<br>Process Name = ".$this->processName."<br>Reason = Virtual number is differ in companymaster generalinfo and techinfo server.<br>Virtual number : " . $this->virtualno . "<br>City = ". $this->city_vn."<br>Techinfo parentid : ".trim($this->techInfo_array['BusinessId'])."<br> Link contract :Yes";
							$subject = $this->city_vn ." - Virtual number mismatch (".$this->virtualno.")";
							$this->sendMail($subject, $message);
							
							$extra_str="Mismatch VN Contract.[Mismatch Vn : ".$this->virtualno."][Vn assign parentid :".trim($this->techInfo_array['BusinessId'])." ]";
							$this->logmsgvirtualno("Remove Duplicate Vn",$this->log_path,$this->processName,$this->parentid,$extra_str);
							
							$this->updateCompanymaster();
							if($this->JDFOS_flag)
							{
								$returnFlag = $this->updateJdfosDb();
							}
						}
					}
					else
					{
						$message = "<br>ParentId = " . $this->parentid . "<br>Process Name = ".$this->processName."<br>Reason = Virtual number is differ in companymaster generalinfo and techinfo server.<br>Virtual number : " . $this->virtualno . "<br>City = ". $this->city_vn."<br>Techinfo parentid : ".trim($this->techInfo_array['BusinessId'])."<br> Link contract :No";
						$subject = $this->city_vn ." - Virtual number mismatch (".$this->virtualno.")";
						$this->sendMail($subject, $message);
						
						$extra_str="Mismatch VN Contract.[Mismatch Vn : ".$this->virtualno."][Vn assign parentid :".trim($this->techInfo_array['BusinessId'])." ]";
						$this->logmsgvirtualno("Remove Duplicate Vn",$this->log_path,$this->processName,$this->parentid,$extra_str);
						
						$this->updateCompanymaster();
						if($this->JDFOS_flag)
						{
							$returnFlag = $this->updateJdfosDb();
						}
					}
				}
			}
		}
		else if(in_array(strtoupper($this->city_vn),$this->remote_city_arr))
		{
			$this->techInfoPaidStatus();
			$this->remoteVnDbArr = $this->vnSearch();
			if(count($this->remoteVnDbArr)>0)
			{
				if(trim($this->remoteVnDbArr['BusinessId'])==trim($this->parentid) && $this->remoteVnDbArr['BusinessId']!='')
				{
					$this->status='A';
					if(in_array(strtoupper($this->city_vn),$this->reliance_remote_city))
					{
						
						if(in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
						{
							$curl_url = $this->rcom_remote_url."action=modify&vmn=".$this->CountryStdCode.trim($this->virtualno);
						}
						else if(!in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
						{
							$curl_url = $this->rcom_remote_url."singletxn/modify/".$this->CountryStdCode.trim($this->virtualno);
						}
						$postarr = $this->makeRelianceUrl(trim($this->parentid));
						$curl_output = $this->runCurlUrl($curl_url,'PUT',$postarr);
						if(!$this->curl_response_flag)
						{
							$logtbl_process_flag=1;
							$logtbl_reason = "rcom remote city url not working";
							$process_name = "update new data for present virtual number";
							$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
							$extra_str="[rcom remote city url not working][API URL : ".$curl_url."][API OUTPUT :".$curl_output."]";
							$this->logmsgvirtualno("Allocate virtual number using rcom remote city",$this->log_path,$this->processName,$this->parentid,$extra_str);
						}
						else
						{
							$remote_city_vrn = $this->readRelianceJson($curl_output,1);
							$this->virtualno = $remote_city_vrn;
							$extra_str="[API : ".$curl_url."][POST Arr : ".json_encode($postarr)."][API OUTPUT :".$curl_output."][vn : ".$remote_city_vrn."][Virtual Number :".$this->virtualno."]";
							$this->logmsgvirtualno("Update actual number IN VN API",$this->log_path,$this->processName,$this->parentid,$extra_str);
						}
					}
					else
					{
							$curl_url = $this->techinfo_url."allocate.php?VN=".trim($this->virtualno)."&";
							$urlEnd = $this->makeTechinfoUrl(trim($this->techInfo_array['BusinessId']));
							echo $curl_url = $curl_url.$urlEnd;
							$curl_output = $this->runTechInfoCurlUrl($curl_url,$url_type);
								
							if(!$this->curl_response_flag)
							{
								$logtbl_process_flag=1;
								$logtbl_reason = "techinfo url not working";
								$process_name = "update new data for present virtual number";
								$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
								$extra_str="[techinfo url not working][url :".$curl_url."]";
								$this->logmsgvirtualno("Allocate virtual number using techinfo",$this->log_path,$this->processName,$this->parentid,$extra_str);
							}
							else
							{
							$techinfo_vrn = $this->readTechinfoXml($curl_output);
							$this->virtualno = $techinfo_vrn;
							$extra_str="[API OUTPUT :".$curl_url."][vn : ".$techinfo_vrn."][Virtual Number :".$this->virtualno."]";
							$this->logmsgvirtualno("Update actual number IN VN API",$this->log_path,$this->processName,$this->parentid,$extra_str);
							}
						
					}
				}
				else
				{
					$extra_str="[database contractid : ".$this->remoteVnDbArr['contractid']."][given parentid :".$this->parentid."]";
					$this->logmsgvirtualno("Database contractid id is not same as given parentid",$this->log_path,$this->processName,$this->parentid,$extra_str);
					if($this->linkcontract_flag)
					{
						if(in_array(trim($this->remoteVnDbArr['contractid']),$this->linkcontracts))
						{
							$extra_str="[remote city contractid : ".$this->remoteVnDbArr['contractid']."][linkcontract array :".implode(",",$this->linkcontracts)."]";
							$this->logmsgvirtualno("remote city contractid is present in linkcontract array",$this->log_path,$this->processName,$this->parentid,$extra_str);
							$this->status='A';
							if(in_array(strtoupper($this->city_vn),$this->reliance_remote_city))
							{
								$curl_url = $this->rcom_remote_url."singletxn/modify/".$this->CountryStdCode.trim($this->virtualno);
								$postarr = $this->makeRelianceUrl(trim($this->remoteVnDbArr['contractid']));
								$curl_output = $this->runCurlUrl($curl_url,'PUT',$postarr);
								
								if(!$this->curl_response_flag)
								{
									$logtbl_process_flag=1;
									$logtbl_reason = "rcom remote city url not working";
									$process_name = "update new data for present virtual number";
									$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
									$extra_str="[rcom remote city url not working][url :".$curl_url."]";
									$this->logmsgvirtualno("Allocate virtual number using rcom remote city",$this->log_path,$this->processName,$this->parentid,$extra_str);
								}
								else
								{
									$remote_city_vrn = $this->readRelianceJson($curl_output,1);
									$this->virtualno = $remote_city_vrn;
									$extra_str="[API URL :".$curl_url."][API OUTPUT :".$curl_output."][vn : ".$remote_city_vrn."][Virtual Number :".$this->virtualno."]";
									$this->logmsgvirtualno("Update actual number IN VN API",$this->log_path,$this->processName,$this->parentid,$extra_str);
								}
							}
							else
							{
								$DbVN = $this->remoteAllocateVirtualDb($this->virtualno);
								$this->virtualno = $DbVN;
							}
						}
						else
						{
							$message = "<br>ParentId = " . $this->parentid . "<br>Process Name = ".$this->processName."<br>Reason = Virtual number is differ in companymaster generalinfo and RCOM/Mapping Master Table.<br>Virtual number : " . $this->virtualno . "<br>City = ". $this->city_vn."<br>RCOM/Mapping Master Parentid : ".trim($this->remoteVnDbArr['contractid'])."<br> Link contract :Yes";
							$subject = $this->city_vn ." - Virtual number mismatch (".$this->virtualno.")";
							$this->sendMail($subject, $message);
							$extra_str="Mismatch VN Contract.[Mismatch Vn : ".$this->virtualno."][Vn assign parentid :".trim($this->remoteVnDbArr['contractid'])." ]";
							$this->logmsgvirtualno("Remove Duplicate Vn",$this->log_path,$this->processName,$this->parentid,$extra_str);
							
							$this->updateCompanymaster();
							if($this->JDFOS_flag)
							{
								$returnFlag = $this->updateJdfosDb();
							}
						}
					}
					else
					{
						$message = "<br>ParentId = " . $this->parentid . "<br>Process Name = ".$this->processName."<br>Reason = Virtual number is differ in companymaster generalinfo and RCOM/Mapping Master Table.<br>Virtual number : " . $this->virtualno . "<br>City = ". $this->city_vn."<br>RCOM/Mapping Master Parentid : ".trim($this->remoteVnDbArr['contractid'])."<br> Link contract :No";
						$subject = $this->city_vn ." - Virtual number mismatch (".$this->virtualno.")";
						$this->sendMail($subject, $message);
						$extra_str="Mismatch VN Contract.[Mismatch Vn : ".$this->virtualno."][Vn assign parentid :".trim($this->remoteVnDbArr['contractid'])." ]";
						$this->logmsgvirtualno("Remove Duplicate Vn",$this->log_path,$this->processName,$this->parentid,$extra_str);
						
						$this->updateCompanymaster();
						if($this->JDFOS_flag)
						{
							$returnFlag = $this->updateJdfosDb();
						}
					}
				}
			}
		}
		else if(!in_array(strtoupper($this->city_vn),$this->remote_city_arr) && !in_array(strtoupper($this->city_vn),$this->main_city_arr))
		{
			$this->techInfoPaidStatus();
			
			$this->remoteVnDbArr = $this->vnSearch();
			//print_r($this->remoteVnDbArr);
			if(count($this->remoteVnDbArr)>0)
			{
				if(trim($this->remoteVnDbArr['BusinessId'])==trim($this->parentid) && $this->remoteVnDbArr['BusinessId']!='')
				{
					$this->status='A';
					$curl_url = $this->techinfo_url."allocate.php?VN=".trim($this->virtualno)."&";
					$urlEnd = $this->makeTechinfoUrl(trim($this->techInfo_array['BusinessId']));
					$curl_url = $curl_url.$urlEnd;
					$curl_output = $this->runTechInfoCurlUrl($curl_url,$url_type);
						
					if(!$this->curl_response_flag)
					{
						$logtbl_process_flag=1;
						$logtbl_reason = "techinfo url not working";
						$process_name = "update new data for present virtual number";
						$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
						$extra_str="[techinfo url not working][url :".$curl_url."]";
						$this->logmsgvirtualno("Allocate virtual number using techinfo",$this->log_path,$this->processName,$this->parentid,$extra_str);
					}
					else
					{
					$techinfo_vrn = $this->readTechinfoXml($curl_output);
					$this->virtualno = $techinfo_vrn;
					$extra_str="[API OUTPUT :".$curl_url."][vn : ".$techinfo_vrn."][Virtual Number :".$this->virtualno."]";
					$this->logmsgvirtualno("Update actual number IN VN API",$this->log_path,$this->processName,$this->parentid,$extra_str);
					}
				
			
				}
				else
				{
					$extra_str="[database contractid : ".$this->remoteVnDbArr['contractid']."][given parentid :".$this->parentid."]";
					$this->logmsgvirtualno("Database contractid id is not same as given parentid",$this->log_path,$this->processName,$this->parentid,$extra_str);
					if($this->linkcontract_flag)
					{
						if(in_array(trim($this->remoteVnDbArr['contractid']),$this->linkcontracts))
						{
							$extra_str="[remote city contractid : ".$this->remoteVnDbArr['contractid']."][linkcontract array :".implode(",",$this->linkcontracts)."]";
							$this->logmsgvirtualno("remote city contractid is present in linkcontract array",$this->log_path,$this->processName,$this->parentid,$extra_str);
							$this->status='A';
							if(in_array(strtoupper($this->city_vn),$this->reliance_remote_city))
							{
								$curl_url = $this->rcom_remote_url."singletxn/modify/".$this->CountryStdCode.trim($this->virtualno);
								$postarr = $this->makeRelianceUrl(trim($this->remoteVnDbArr['contractid']));
								$curl_output = $this->runCurlUrl($curl_url,'PUT',$postarr);
								
								if(!$this->curl_response_flag)
								{
									$logtbl_process_flag=1;
									$logtbl_reason = "rcom remote city url not working";
									$process_name = "update new data for present virtual number";
									$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
									$extra_str="[rcom remote city url not working][url :".$curl_url."]";
									$this->logmsgvirtualno("Allocate virtual number using rcom remote city",$this->log_path,$this->processName,$this->parentid,$extra_str);
								}
								else
								{
									$remote_city_vrn = $this->readRelianceJson($curl_output,1);
									$this->virtualno = $remote_city_vrn;
									$extra_str="[API URL :".$curl_url."][API OUTPUT :".$curl_output."][vn : ".$remote_city_vrn."][Virtual Number :".$this->virtualno."]";
									$this->logmsgvirtualno("Update actual number IN VN API",$this->log_path,$this->processName,$this->parentid,$extra_str);
								}
							}
							else
							{
								$DbVN = $this->remoteAllocateVirtualDb($this->virtualno);
								$this->virtualno = $DbVN;
							}
						}
						else
						{
							$message = "<br>ParentId = " . $this->parentid . "<br>Process Name = ".$this->processName."<br>Reason = Virtual number is differ in companymaster generalinfo and RCOM/Mapping Master Table.<br>Virtual number : " . $this->virtualno . "<br>City = ". $this->city_vn."<br>RCOM/Mapping Master Parentid : ".trim($this->remoteVnDbArr['contractid'])."<br> Link contract :Yes";
							$subject = $this->city_vn ." - Virtual number mismatch (".$this->virtualno.")";
							$this->sendMail($subject, $message);
							$extra_str="Mismatch VN Contract.[Mismatch Vn : ".$this->virtualno."][Vn assign parentid :".trim($this->remoteVnDbArr['contractid'])." ]";
							$this->logmsgvirtualno("Remove Duplicate Vn",$this->log_path,$this->processName,$this->parentid,$extra_str);
							
							$this->updateCompanymaster();
							if($this->JDFOS_flag)
							{
								$returnFlag = $this->updateJdfosDb();
							}
						}
					}
					else
					{
						$message = "<br>ParentId = " . $this->parentid . "<br>Process Name = ".$this->processName."<br>Reason = Virtual number is differ in companymaster generalinfo and RCOM/Mapping Master Table.<br>Virtual number : " . $this->virtualno . "<br>City = ". $this->city_vn."<br>RCOM/Mapping Master Parentid : ".trim($this->remoteVnDbArr['contractid'])."<br> Link contract :No";
						$subject = $this->city_vn ." - Virtual number mismatch (".$this->virtualno.")";
						$this->sendMail($subject, $message);
						$extra_str="Mismatch VN Contract.[Mismatch Vn : ".$this->virtualno."][Vn assign parentid :".trim($this->remoteVnDbArr['contractid'])." ]";
						$this->logmsgvirtualno("Remove Duplicate Vn",$this->log_path,$this->processName,$this->parentid,$extra_str);
						
						$this->updateCompanymaster();
						if($this->JDFOS_flag)
						{
							$returnFlag = $this->updateJdfosDb();
						}
					}
				}
			}
		}
		
		
		return $this->virtualno;
	}
	
	function makeRelianceUrl($pid)
	{
		$curlUrlEnd='';
		$i=1;
		$landline_str='';
		$agentlistArr = array();
		$agent = array();
		foreach($this->finalmappednumbers as $contactno)
		{
			$agent = array();
			if($i>8)
			{
				break;
			}
			else
			{
				//$agent['agentOrder'] = '"'.$i.'"';
				$agent['agentOrder'] = "$i";
				$agent['name'] = ($this->contractperson_arr[0]!=''?$this->contractperson_arr[0]:'');
				$agent['phone'] = trim($contactno);
				$agent['userName'] = ($this->contractperson_arr[0]!=''?$this->contractperson_arr[0]:'');
				$agentlistArr[] = $agent; 
			}
			$i++;
		}
		if(trim($this->mobile_feedback)!='')
		{
			$fb_mobile=trim($this->mobile_feedback);
		}
		if(trim($this->email_feedback)!='')
		{
			$fbemail=trim($this->email_feedback);
		}
		if($this->shopfront_flag==1){//DialType
			$dialtype = "0";
		}else{
			$dialtype = "1";
		}

		$curlPostarr = array();
		$curlPostarr['user'] = trim($this->usercode);
		$curlPostarr['circle'] = strtoupper($this->city_vn);
		$curlPostarr['contractType'] = trim($this->techinfoPidStatus);
		//$curlPostarr['businessName'] = trim($this->companyname);
		$curlPostarr['businessId'] = trim($pid);
		$curlPostarr['contextCode'] = '1';
		$curlPostarr['dialType'] = $dialtype;
		$curlPostarr['fb_email'] = trim($fbemail);
		$curlPostarr['fb_mobile'] = trim($fb_mobile);
		$curlPostarr['agentList'] = $agentlistArr;
		$curlPostarr['contract'] = "new";
		
		return $curlPostarr;
	}
	
	function makeTechinfoUrl($pid)
	{
		$curlUrlEnd='';
		$i=1;
		$landline_str='';
		foreach($this->finalmappednumbers as $contactno)
		{
			if($i>8)
			{
				break;
			}
			else
			{
				$landline_str .="Ph".($i)."=".trim($contactno);
				$landline_str.="&";
			}
			$i++;
		}
		if(trim($this->mobile_feedback)!='')
		{
			$fb_mobile=trim($this->mobile_feedback);
		}
		if(trim($this->email_feedback)!='')
		{
			$fbemail=trim($this->email_feedback);
		}
		if($this->shopfront_flag==1){//DialType
			$dialtype = "0";
		}else{
			$dialtype = "1";
		}
		$addition_info ="User=".trim($this->usercode)."&BusinessId=".trim($pid)."&Email=".urlencode(trim($fbemail))."&Mobile=".$fb_mobile."&Contract=".trim($this->techinfoPidStatus)."&DialType=".intval($dialtype)."&BusinessName=".urlencode(trim($this->companyname))."&data_city=".urlencode($this->city_vn)."&VN=".$this->virtualno;
		$curlUrlEnd = $landline_str.$addition_info;
		
		return $curlUrlEnd;
	}
	
	function runCurlUrl($curl_url,$method,$postarr=array())
    {
		$output = '';
        $this->curl_response_flag = false;
        if(!is_object($this->curlobj)){
			$this->curlobj = new CurlClass();
		}
        $this->curlobj->setOpt(CURLOPT_CONNECTTIMEOUT,3);
        $this->curlobj->setOpt(CURLOPT_TIMEOUT,10);
        if(strtoupper(trim($method))=='POST'){ 
			$output = $this->curlobj->post($curl_url,$postarr,1);
		}elseif(strtoupper(trim($method))=='PUT'){
			$output = $this->curlobj->put($curl_url,$postarr,1);
		}elseif(strtoupper(trim($method))=='DELETE'){
			$output = $this->curlobj->delete($curl_url,$postarr);
		}else{
			$output = $this->curlobj->get($curl_url);
		}
		/*$fields = json_encode($postarr);if($method=='POST')echo "url=".$curl_url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$curl_url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('developer_id:justdial','Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, self::CURL_TIMEOUT );
        //curl_setopt( $ch, CURLOPT_TIMEOUT, self::CURL_TIMEOUT );
        curl_setopt($ch,CURLOPT_POST, TRUE);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields);
        $output =curl_exec($ch);*/
        if($output!=false)
        {
            $this->curl_response_flag = true;            
        }
        else
        {
			
            if(curl_errno($ch)==28)
            {
                return -28;
            }
        }
       
       $extra_str="[Url run :".$curl_url."][method  :".$method."][post array :".json_encode($postarr)."][curl response flag : ".$this->curl_response_flag."][curl return :".$output."]";
		$string_code = substr($extra_str,strpos($extra_str,'curl return',0));
		$this->code =  strstr($string_code,'error":"12017');
        $this->logmsgvirtualno("Curl url .",$this->log_path,$this->processName,$this->parentid,$extra_str);
        return $output;
    }
    
    function runTechInfoCurlUrl($curl_url )
    {
        $this->curl_response_flag = false;
        $ch = curl_init();
        $ans = curl_setopt($ch, CURLOPT_URL,$curl_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, self::CURL_TIMEOUT );
        curl_setopt( $ch, CURLOPT_TIMEOUT, self::CURL_TIMEOUT );
        $output =curl_exec($ch);
        if($output!=false)
        {
            $this->curl_response_flag = true;            
        }
        else
        {
            if(curl_errno($ch)==28)
            {
                return -28;
            }
        }
        $extra_str="[Url run :".$curl_url."][url type :".$url_type."][curl response flag : ".$this->curl_response_flag."][outpout return :".$output."]";
        $this->logmsgvirtualno("Curl url .",$this->log_path,$this->processName,$this->parentid,$extra_str);
        return $output;
    }
    
    function runPostCurlUrl($url,$params)
    {
		$this->curl_postresponse_flag = false;//echo "<pre>";print_r($params);die();
		foreach($params as $results) 
		{ 
			foreach($results as $field)
			{
				foreach($field as $field_key)
				{
					foreach($field_key as $key => $value)
					{
						$params_string .= $key.'='.$value.'&'; 
					}
				}
			}
		}
		$params_string =rtrim($params_string, '&');
		$ch_int = curl_init();
		curl_setopt($ch_int,CURLOPT_URL, $url);
		curl_setopt($ch_int,CURLOPT_POST, count($params_string));
		curl_setopt($ch_int,CURLOPT_POSTFIELDS, $params_string);
		curl_setopt($ch_int,CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch_int);
		curl_close($ch_int);
		if($result!=false)
        {
            $this->curl_postresponse_flag = true;            
        }
        else
        {
            if(curl_errno($ch_int)==28)
            {
                return -28;
            }
        }
		
		$extra_str="[Url run :".$url."][param string : ".$params_string."][curl response flag : ".$this->curl_postresponse_flag."][outpout return :".$result."]";
        $this->logmsgvirtualno("JDFOS update url.",$this->log_path,$this->processName,$this->parentid,$extra_str);
		return $result;
	}
	
	function readRelianceJson($curl_output,$modify_flag=0){
		//{"error":"The request has succeeded","status":200,"data":[{"virtual Number":"912230071817"}]}
		$modifiedNUumber = '';
		$curl_output = trim($curl_output);
		$relianceOutput = json_decode($curl_output,true);
		$countsubstr = 0;
		if($this->status=='A'){ 
			if(count($relianceOutput)>0){ 
				if($relianceOutput['status']=='200'){ 
					if(intval($modify_flag)==1){ 
						//$modifiedNUumber = explode(":",$relianceOutput['data']['0']);
						$modifiedNUumber = isset($relianceOutput['data']['0']['virtual Number']) ? $relianceOutput['data']['0']['virtual Number'] : $relianceOutput['data']['virtual Number'];
						//die;
						//$countsubstr = strlen($this->CountryStdCode);
						if($countsubstr>0){
							//$vn = substr($modifiedNUumber[1],$countsubstr);
							$vn = substr($modifiedNUumber,$countsubstr);
						}else{
							//$vn = substr($modifiedNUumber[1],(-$this->vn_length));
							$vn = substr($modifiedNUumber,(-$this->vn_length));
						}
					}else if(intval($modify_flag)==2){
						//print_r($relianceOutput);die();
						$modifiedNUumber = isset($relianceOutput['data']['0']['VitualNumber']) ? $relianceOutput['data']['0']['VitualNumber'] : $relianceOutput['data']['VitualNumber'];
						
						if($countsubstr>0){
							//$vn = substr($modifiedNUumber[1],$countsubstr);
							$vn = substr($modifiedNUumber,$countsubstr);
						}else{
							//$vn = substr($modifiedNUumber[1],(-$this->vn_length));
							$vn = substr($modifiedNUumber,(-$this->vn_length));
						}
						//echo '<pre>vn====';print_r($vn);die();
					}else{
						//$countsubstr = strlen($this->CountryStdCode);
						if($countsubstr>0){
							$vn = substr($relianceOutput['data']['0']['virtual Number'],$countsubstr);
						}else{
							$vn = substr($relianceOutput['data']['0']['virtual Number'],(-$this->vn_length));
						}
					}
					$extra_str="[API output: ".$curl_output."][Virtual number : ".$vn."][already assign virtual number :".$this->virtualno."]";
					$this->logmsgvirtualno("Get API output, VN API is successfully",$this->log_path,$this->processName,$this->parentid,$extra_str);
					return $vn;
				}else if($relianceOutput['status']=='404'){
				   $extra_str="[API output: ".$curl_output."]";
					$this->logmsgvirtualno("This BusinessId is not linked with any VirtualNumber",$this->log_path,$this->processName,$this->parentid,$extra_str);
				} 
				else{
					$logtbl_process_flag=1;
					$logtbl_reason = "status 400";
					$process_name = "api error Virtual number";
					$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
				}
			}
		}elseif($this->status=='D'){ 
		}else{
		}
	}
	
    function readTechinfoXml($curl_output)
    {
		$curl_output = trim($curl_output);
        $xmlDoc = new DOMDocument();
        $xmlDoc->loadXML($curl_output);
        $Result = $xmlDoc->getElementsByTagName( "Result" );
        if($this->status=='A')
        {
            foreach($Result as $obj)
            {
                $Code = $obj->getElementsByTagName("Code");
                $Code = $Code->item(0)->nodeValue;
                $text = $obj->getElementsByTagName("Text");
                $text = $text->item(0)->nodeValue; 
                $vn   = $obj->getElementsByTagName("VN");
                $vn   = $vn->item(0)->nodeValue; 
            }
			if($Code==5 && strlen($Code)>0)
			{
				$parentId =$this->parentid;
				$message = "<br>ParentId = " . $parentId . "<br>URL = " . $this->techinfo_url . "<br>City = " .$this->city_vn;
				$subject = $this->city_vn."-".$text;
				$this->sendMail($subject, $message);
				$logtbl_process_flag=1;
				$logtbl_reason = "virtualnumber inventory full main city.UrlCode".$Code." text".$text;
				$process_name = "allocate virtual number";
				$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
			}
            if($Code==0 && strlen($Code)>0)
            {
                /*log msg here */
                $extra_str="[URL return code : ".$Code."][ URL return Text : ".$text."][Virrtual number : ".$vn."][already assign virtual number :".$this->virtualno."]";
                $this->logmsgvirtualno("Get all information from allocate virtual number Techinfo URL is successfully",$this->log_path,$this->processName,$this->parentid,$extra_str);
                return $vn;
            }
            else
            {
                /*log msg here*/
                $extra_str="[URL return code : ".$Code."][ URL return Text : ".$text."][Virrtual number : ".$vn."][already assign virtual number :".$this->virtualno."]";
                $this->logmsgvirtualno("Get all inforamtion from allocate virtual number Techinfo URL is failed",$this->log_path,$this->processName,$this->parentid,$extra_str);
                return $this->virtualno;
            }
        }
        elseif($this->status=='D')
        {
            foreach($Result as $obj)
            {
                $Code = $obj->getElementsByTagName("Code");
                $Code = $Code->item(0)->nodeValue;
                $text = $obj->getElementsByTagName("Text");
                $text = $text->item(0)->nodeValue; 
            }
            if($Code==0 && strlen($Code)>0)
            {
                /*log msg here */
                $extra_str="[URL return code : ".$Code."][ URL return Text : ".$text."][Virrtual number : ".$vn."][already assign virtual number :".$this->virtualno."]";
                $this->logmsgvirtualno("Get all inforamtion from deallocate virtual number Techinfo URL is successfully",$this->log_path,$this->processName,$this->parentid,$extra_str);
                return $vn;
            }
            else
            {
                /*log msg here */
                $extra_str="[URL return code : ".$Code."][ URL return Text : ".$text."][Virrtual number : ".$vn."][already assign virtual number :".$this->virtualno."]";
                $this->logmsgvirtualno("Get all inforamtion from deallocate virtual number Techinfo URL is failed",$this->log_path,$this->processName,$this->parentid,$extra_str);
                return $vn;
            }
        }
    }
	
	function vnSearch()
	{
		if(in_array(strtoupper($this->city_vn),$this->main_city_arr))
		{
			$curl_output ='';
			
			if(in_array(strtoupper($this->city_vn),$this->reliance_main_city)){
				//$curl_url = $this->techinfo_url."virtualnumbersearch/9122".trim($this->virtualno);
				if(in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
				{
				$curl_url = $this->techinfo_url."action=vmSearch&vmnNumber=".$this->CountryStdCode.trim($this->virtualno);
				}
				else if(!in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
				{
				$curl_url = $this->techinfo_url."virtualnumbersearch/".$this->CountryStdCode.trim($this->virtualno);	
				}
				$curl_output = $this->runCurlUrl($curl_url,'GET');
			}else{
				$curl_url = $this->techinfo_url."vrnsearch.php?VN=".trim($this->virtualno);
				$curl_output = $this->runTechInfoCurlUrl($curl_url);
			}
			if(!$this->curl_response_flag)
			{
				$logtbl_process_flag=1;
				$logtbl_reason = "techinfo url not working";
				$process_name = "check for given virtual number";
				$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
				$extra_str="[techinfo url not working][url :".$curl_url."]";
				$this->logmsgvirtualno("check for given virtual number in techinfo",$this->log_path,$this->processName,$this->parentid,$extra_str);
			}
			else
			{
				if(in_array(strtoupper($this->city_vn),$this->reliance_main_city)){
					$this->techInfo_array = $this->relianceArray($curl_output); 
				}else{
					$this->techInfo_array = $this->techinfoArray($curl_output); 
				}
				
			}
			return $this->techInfo_array;
		}
		else if(in_array(strtoupper($this->city_vn),$this->remote_city_arr))
		{
			$virtualArr = array();
			$curl_output ='';
			
			if(in_array(strtoupper($this->city_vn),$this->reliance_remote_city))
			{
				if(in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
				{
					$curl_url = $this->rcom_remote_url."action=vmSearch&vmnNumber=".$this->CountryStdCode.trim($this->virtualno);
				}
				elseif(!in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
				{
					$curl_url = $this->rcom_remote_url."virtualnumbersearch/".$this->CountryStdCode.trim($this->virtualno);
				}
				$curl_output = $this->runCurlUrl($curl_url,'GET');
				
				if(!$this->curl_response_flag)
				{
					$logtbl_process_flag=1;
					$logtbl_reason = "rcom remote city url not working";
					$process_name = "check for given virtual number";
					$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
					$extra_str="[rcom remote city url not working][url :".$curl_url."]";
					$this->logmsgvirtualno("check for given virtual number in rcom api",$this->log_path,$this->processName,$this->parentid,$extra_str);
				}
				else
				{
					$virtualArr = $this->relianceArray($curl_output);
					$virtualArr['contractid'] = trim($virtualArr['BusinessId']);
				}	
			}
			else
			{
				echo $curl_url = $this->techinfo_url."vrnsearch.php?VN=".trim($this->virtualno);
				$curl_output = $this->runTechInfoCurlUrl($curl_url);
				$this->techInfo_array = $this->techinfoArray($curl_output); 
				return $this->techInfo_array;	
			}
			return $virtualArr;
		}
		else if(!in_array(strtoupper($this->city_vn),$this->remote_city_arr) && !in_array(strtoupper($this->city_vn),$this->main_city_arr))
		{
			$virtualArr = array();
			$curl_output ='';
				$curl_url = $this->techinfo_url."vrnsearch.php?VN=".trim($this->virtualno);
				$curl_output = $this->runTechInfoCurlUrl($curl_url);
				$this->techInfo_array = $this->techinfoArray($curl_output); 
				return $this->techInfo_array;	
		
			return $virtualArr;
		}
		
	}
	
	function relianceArray($curl_output){
		$vnSearchArr = array();
		$curl_output = trim($curl_output);
		$vnSearchArr = json_decode($curl_output,true);
		if(count($vnSearchArr)>0){
			if($vnSearchArr['status']==200){
				$this->techinfo_array['Code']= $vnSearchArr['status'];
				$this->techinfo_array['Text']= $vnSearchArr['error'];
				$this->techinfo_array['VN']= $vnSearchArr['data'][0]['VN'];
				$this->techinfo_array['Status']= $vnSearchArr['data'][0]['Status'];
				$this->techinfo_array['BusinessId']= $vnSearchArr['data'][0]['BusinessId'];
				$this->techinfo_array['Ph1']= $vnSearchArr['data'][0]['Ph1'];
				$this->techinfo_array['Ph2']= $vnSearchArr['data'][0]['Ph2'];
				$this->techinfo_array['Ph3']= $vnSearchArr['data'][0]['Ph3'];
				$this->techinfo_array['Ph4']= $vnSearchArr['data'][0]['Ph4'];
				$this->techinfo_array['Ph5']= $vnSearchArr['data'][0]['Ph5'];
				$this->techinfo_array['Ph6']= $vnSearchArr['data'][0]['Ph6'];
				$this->techinfo_array['Ph7']= $vnSearchArr['data'][0]['Ph7'];
				$this->techinfo_array['Ph8']= $vnSearchArr['data'][0]['Ph8'];
				$this->techinfo_array['Mobile']= $vnSearchArr['data'][0]['FeedbackMobile'];
				$this->techinfo_array['Email']= $vnSearchArr['data'][0]['FeedbackEmail'];
				$this->techinfo_array['Dialtype']= $vnSearchArr['data'][0]['DialType'];
				
				$get_arry_str='';
            	$get_arry_str = array_map(create_function('$key, $value', 'return $key.":".$value." # ";'), array_keys($this->techinfo_array), array_values($this->techinfo_array));

				$extra_str="[API OUTPUT : ".$curl_output."][Return Reliance array :".implode($get_arry_str)."]";
				$this->logmsgvirtualno("Get all inforamtion from Reliance API successfully",$this->log_path,$this->processName,$this->parentid,$extra_str);
				return $this->techinfo_array;
			}else{
				$logtbl_process_flag=1;
				$logtbl_reason = "Relinace API output not proper";
				$process_name = "check virtual number";
				$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);

				$extra_str="[API OUTPUT : ".$curl_output."][insert into logtable]";
				$this->logmsgvirtualno("Get all inforamtion from Reliance API is failed",$this->log_path,$this->processName,$this->parentid,$extra_str);
				return $this->techinfo_array;
			}
		}else{
			$logtbl_process_flag=1;
			$logtbl_reason = "Relinace API output not proper";
			$process_name = "check virtual number";
			$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);

			$extra_str="[API OUTPUT : ".$curl_output."][Reliance API Failed]";
			$this->logmsgvirtualno("Get all inforamtion from Reliance API is failed",$this->log_path,$this->processName,$this->parentid,$extra_str);
			return $this->techinfo_array;
		}
	}
	
	function techinfoArray($curl_output)
    {
		$curl_output = trim($curl_output);
        $curl_output = str_replace('&nbsp;', '&#160;', $curl_output);
        $curl_output = str_replace('Bussiness Id', 'BusinessId', $curl_output);
        $xmlDoc = new DOMDocument();
        $xmlDoc->loadXML($curl_output);
        $Result = $xmlDoc->getElementsByTagName( "Result" );
        foreach($Result as $obj)
        {
            $Code = $obj->getElementsByTagName("Code");
            $Code = $Code->item(0)->nodeValue;
            $text = $obj->getElementsByTagName("Text");
            $text = $text->item(0)->nodeValue; 
            $vn   = $obj->getElementsByTagName("VN");
            $vn   = $vn->item(0)->nodeValue;
            $vn_arry = explode(" ",$vn);
            $Status = $obj->getElementsByTagName("Status");
            $Status = $Status->item(0)->nodeValue;
            $Status_arry = explode(" ",$Status);
            $BusinessId = $obj->getElementsByTagName("BusinessId");
            $BusinessId = $BusinessId->item(0)->nodeValue;
            $BusinessId_arry = explode(" ",$BusinessId);
            $Ph1 = $obj->getElementsByTagName("Ph1");
            $Ph1 = $Ph1->item(0)->nodeValue;
            $Ph1_arry = explode(" ",$Ph1);
            $Ph2 = $obj->getElementsByTagName("Ph2");
            $Ph2 = $Ph2->item(0)->nodeValue;
            $Ph2_arry = explode(" ",$Ph2);
            $Ph3 = $obj->getElementsByTagName("Ph3");
            $Ph3 = $Ph3->item(0)->nodeValue;
            $Ph3_arry = explode(" ",$Ph3);
            $Ph4 = $obj->getElementsByTagName("Ph4");
            $Ph4 = $Ph4->item(0)->nodeValue;
            $Ph4_arry = explode(" ",$Ph4);
            $Ph5 = $obj->getElementsByTagName("Ph5");
            $Ph5 = $Ph5->item(0)->nodeValue;
            $Ph5_arry = explode(" ",$Ph5);
            $Ph6 = $obj->getElementsByTagName("Ph6");
            $Ph6 = $Ph6->item(0)->nodeValue;
            $Ph6_arry = explode(" ",$Ph6);
            $Ph7 = $obj->getElementsByTagName("Ph7");
            $Ph7 = $Ph7->item(0)->nodeValue;
            $Ph7_arry = explode(" ",$Ph7);
            $Ph8 = $obj->getElementsByTagName("Ph8");
            $Ph8 = $Ph8->item(0)->nodeValue;
            $Ph8_arry = explode(" ",$Ph8);
            $Mobile = $obj->getElementsByTagName("Mobile");
            $Mobile = $Mobile->item(0)->nodeValue;
            $Mobile_arry = explode(" ",$Mobile);
            $Email = $obj->getElementsByTagName("Email");
            $Email = $Email->item(0)->nodeValue;
            $Email_arry = explode(" ",$Email);
            $dialtype = $obj->getElementsByTagName("DialType");
            $dialtype = $dialtype->item(0)->nodeValue;
            $dialtype_arry = explode(" ",$dialtype);
        }
        if($Code==='0')
        {
            $this->techinfo_array['Code']= $Code;
            $this->techinfo_array['Text']= $text;
            $this->techinfo_array['VN']= $vn_arry[1];
            $this->techinfo_array['Status']= $Status_arry[1];
            $this->techinfo_array['BusinessId']= $BusinessId_arry[1];
            $this->techinfo_array['Ph1']= $Ph1_arry[1];
            $this->techinfo_array['Ph2']= $Ph2_arry[1];
            $this->techinfo_array['Ph3']= $Ph3_arry[1];
            $this->techinfo_array['Ph4']= $Ph4_arry[1];
            $this->techinfo_array['Ph5']= $Ph5_arry[1];
            $this->techinfo_array['Ph6']= $Ph6_arry[1];
            $this->techinfo_array['Ph7']= $Ph7_arry[1];
            $this->techinfo_array['Ph8']= $Ph8_arry[1];
            $this->techinfo_array['Mobile']= $Mobile_arry[2];
            $this->techinfo_array['Email']= $Email_arry[2];
			$this->techinfo_array['Dialtype']= $dialtype_arry[2];
            /*log msg*/
            $get_arry_str='';
            
            $get_arry_str = array_map(create_function('$key, $value', 'return $key.":".$value." # ";'), array_keys($this->techinfo_array), array_values($this->techinfo_array));

            $extra_str="[URL return code : ".$Code."][ URL return Text : ".$text."][Return Teachinfo array :".implode($get_arry_str)."]";
            $this->logmsgvirtualno("Get all inforamtion from Techinfo URL successfully",$this->log_path,$this->processName,$this->parentid,$extra_str);
            return $this->techinfo_array;
        }
        else
        {
            /*log msg*/
            $logtbl_process_flag=1;
            $logtbl_reason = "techinfo xml not upload proper";
            $process_name = "check virtual number in normal dealclose process";
            $this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);

            $extra_str="[URL return code : ".$Code."][ URL return Text : ".$text."][insert into logtable]";
            $this->logmsgvirtualno("Get all inforamtion from Techinfo URL is failed",$this->log_path,$this->processName,$this->parentid,$extra_str);
            return $this->techinfo_array;
        }
    }
    
    function checkQuarantineVn()
    {
		$checkQuarantineFlag = false;
		/*if($this->linkcontract_flag)
        {
            $quarantine_parentid = $this->mainrootcontractid;
        }
        else
        {
            $quarantine_parentid = $this->parentid;
        }*/
        $quarantine_parentid = $this->parentid;
        if(intval($this->virtualno)>0)
		{
			$condition = " AND vno='".intval($this->virtualno)."'";
		}
		else
		{
			$condition = "";
		}
		$qryCheckQuarantineFlag = "SELECT businessid,vno FROM tbl_quarantine_virtualnumber WHERE businessid = '".trim($quarantine_parentid)."' ".$condition." AND active_flag = 1 AND  hide_flag = 0 AND end_date = '0000-00-00 00:00:00' AND vno!=0 ORDER BY update_date DESC LIMIT 1";
		$resCheckQuarantineFlag = $this->execute_query($qryCheckQuarantineFlag,$this->conn_decs);
		if(!$resCheckQuarantineFlag)
		{
			die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_get_linked_contract_flag);
            return false; 
		}
		else
		{
			if($resCheckQuarantineFlag>0)
			{
				$extra_str="[Qry:".$qryCheckQuarantineFlag."][ qry result:".$resCheckQuarantineFlag."]";
				$this->logmsgvirtualno("Check quarantine",$this->log_path,$this->processName,$this->parentid,$extra_str);
				$rowCheckQuarantineFlag = mysql_fetch_assoc($resCheckQuarantineFlag);
				if(intval($rowCheckQuarantineFlag['vno'])>0)
				{
					if(trim($rowCheckQuarantineFlag['vno'])==$this->virtualno)
					{
						$checkQuarantineFlag = true;
					}
				}
			}
		}
		return $checkQuarantineFlag;
	}
	function checkQuarantineVn_Expire()
    {
		$checkQuarantineFlag = false;
		/*if($this->linkcontract_flag)
        {
            $quarantine_parentid = implode("','",$this->linkcontracts); 
        }
        else
        {
            $quarantine_parentid = $this->parentid;
        }*/
        $quarantine_parentid = $this->parentid;
        if(intval($this->virtualno)>0)
		{
			$condition = " AND vno='".intval($this->virtualno)."'";
		}
		else
		{
			$condition = "";
		}
		$qryCheckQuarantineFlag = "SELECT businessid,vno FROM tbl_quarantine_virtualnumber WHERE businessid IN ('".trim($quarantine_parentid)."') ".$condition." AND active_flag = 1 AND  hide_flag = 1 AND end_date = '0000-00-00 00:00:00' AND vno!=0 ORDER BY update_date DESC LIMIT 1";
		$resCheckQuarantineFlag = $this->execute_query($qryCheckQuarantineFlag,$this->conn_decs);
		if(!$resCheckQuarantineFlag)
		{
			die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_get_linked_contract_flag);
            return false; 
		}
		else
		{
			if($resCheckQuarantineFlag>0)
			{
				$extra_str="[Qry:".$qryCheckQuarantineFlag."][ qry result:".$resCheckQuarantineFlag."]";
				$this->logmsgvirtualno("Check quarantine For expired",$this->log_path,$this->processName,$this->parentid,$extra_str);
				$rowCheckQuarantineFlag = mysql_fetch_assoc($resCheckQuarantineFlag);
				if(intval($rowCheckQuarantineFlag['vno'])>0)
				{
					if(trim($rowCheckQuarantineFlag['vno'])==$this->virtualno)
					{
						$checkQuarantineFlag = true;
					}
				}
			}
		}
		return $checkQuarantineFlag;
	}
	
	function QuarantineNo($reason)
	{
		/*if($this->linkcontract_flag)
        {
            $businessid = $this->mainrootcontractid;
        }
        else
        {
            $businessid = $this->parentid;
        }*/
        $businessid = $this->parentid;
        $qry_insert_quarantine_tbl = "INSERT INTO tbl_quarantine_virtualnumber SET vno = '".(int)$this->virtualno."', businessid ='".trim($businessid)."', start_date ='".date('Y-m-d H:i:s')."', active_flag = 1, reason ='".$reason."', update_date = '".date('Y-m-d H:i:s')."'";
        $res_insert_quarantine_tbl = $this->execute_query($qry_insert_quarantine_tbl,$this->conn_decs);
        
        $extra_str="[qry : ".$qry_insert_quarantine_tbl."][ qry result:".$res_insert_quarantine_tbl."][virtual number :".$this->virtualno."]";
        $this->logmsgvirtualno("insert into quarantine table",$this->log_path,$this->processName,$this->parentid,$extra_str);
	}
    
    function removeQuarantine($vno)
    {
		$removeSuccess = false;
		if(intval($vno)>0)
		{
			$qry_update_quarantine_tbl = "update tbl_quarantine_virtualnumber set active_flag=0 ,end_date='".date('Y-m-d H:i:s')."',update_date='".date('Y-m-d H:i:s')."' where vno = '".$vno."' and businessid ='".trim($this->parentid)."'";
			$res_update_quarantine_tbl = $this->execute_query($qry_update_quarantine_tbl,$this->conn_decs);
			if($res_update_quarantine_tbl)
			{
				$removeSuccess = true;
			}

			$extra_str="[Virtual number :".$vno."][qry : ".$qry_update_quarantine_tbl."] [qry result : ".$res_update_quarantine_tbl."]";
			$this->logmsgvirtualno("Deactivate quarantine flag.",$this->log_path,$this->processName,$this->parentid,$extra_str);
		}
		return $removeSuccess;
	}
	
    function updateCompanymaster($genioVn='',$flag_vn='')
    {
		$insertarr	= array();
		$upVn = '';
		if(intval($genioVn)>0)
		{
			$this->getPriNumber();
			$upVn = "virtualNumber = '".$genioVn."', virtual_mapped_number = '".$this->top_mappednumber_withoutstd[0]."', pri_number='".$this->pri_number."'";
			$insertarr['tbl_companymaster_generalinfo']['virtualNumber'] = $genioVn;
			$insertarr['tbl_companymaster_generalinfo']['virtual_mapped_number'] = $this->top_mappednumber_withoutstd[0];
			$insertarr['tbl_companymaster_generalinfo']['pri_number'] = $this->pri_number;
			$insertarr['tbl_companymaster_generalinfo']['parentid']		= $this->parentid;
			
		}
		else
		{
			$upVn = "virtualNumber = '', virtual_mapped_number = '' , pri_number = '' ";
			$insertarr['tbl_companymaster_generalinfo']['virtualNumber'] = '';
			$insertarr['tbl_companymaster_generalinfo']['virtual_mapped_number'] = '';
			$insertarr['tbl_companymaster_generalinfo']['pri_number'] = '';
			$insertarr['tbl_companymaster_generalinfo']['parentid']		= $this->parentid;
			
		}
		if(trim($upVn)!='')
		{
			$res_vn  = $this->compmaster_obj->UpdateRow($insertarr);
			$qryUpdateCompanymasterGen = "update tbl_companymaster_generalinfo set ".$upVn." where parentid in( '".$this->parentid."')";
			//$resUpdateCompanymasterGen = $this->execute_query($qryUpdateCompanymasterGen,$this->conn_iro);
			$extra_str="[updated virtual number:".$genioVn."][updated virtual mapped number:".implode(",",$this->top_mappednumber_withoutstd)."][updated parentid:".$this->parentid."][insertarr :".implode(",",$insertarr['tbl_companymaster_generalinfo'])."]";
			$this->logmsgvirtualno("Company Master UpdateRow Function Called : ",$this->log_path,$this->processName,$this->parentid,$extra_str);
			$this->updateCompanyMasterSearch();
			
			if($flag_vn == 1)
			{
				$this->compmaster_obj	= new companyMasterClass($this->conn_iro,"",$parentid);
				$this->DialableNumberClass = new DialableNumberClass($this->compmaster_obj);
			}
			
			
			$this->DialableNumberClass->upDateDialableNumber($this->parentid,$this->conn_iro);
			if($this->jdfos_pid_chk_flag == true)
			{
				$this->updateJdfosDb();
			}
		}
		
		return true;
	}
	
	function getPriNumber()
	{
		$qry_get_pri_number = "SELECT pri_no,start_number,end_number FROM tbl_virtual_number_range WHERE  city='".$this->city_vn."' AND '".$this->virtualno."' BETWEEN start_number AND end_number";
        $res_get_pri_number = $this->execute_query($qry_get_pri_number,$this->conn_decs);
        if($res_get_pri_number && mysql_num_rows($res_get_pri_number)>0)
        {
            $row_get_pri_number = mysql_fetch_array($res_get_pri_number);
            $this->pri_number = $row_get_pri_number['pri_no'];
        }
        $extra_str="[qry : ".$qry_get_pri_number."][ qry result:".$res_get_pri_number."][virtual number :".$this->virtualno."][PIR number : ".$this->pri_number."]";
        $this->logmsgvirtualno("Check/get PRI number from virtual number table",$this->log_path,$this->processName,$this->parentid,$extra_str);
	}
	
    function updateCompanyMasterSearch($pid='')
    {
		if($pid=='')
		{
			$pid = $this->parentid;
		}

		$temparr	= array();
		$fieldstr	= "parentid, mobile_display, landline_display,tollfree_display,fax,virtualNumber,tollfree";
		$where 		= "parentid ='".$pid."'";
		$res_sel_comp_srch	= $this->compmaster_obj->getRow($fieldstr,"tbl_companymaster_generalinfo",$where);

        if($res_sel_comp_srch['numrows']>0)
        {
            foreach($res_sel_comp_srch['data'] as $row_sel_comp_srch)
            {
                $phone_searchArr= array();
                if(trim($row_sel_comp_srch['mobile_display'])!='')
                    $phone_searchArr    =   array_merge($phone_searchArr, explode(",", trim($row_sel_comp_srch['mobile_display'])));
                if(trim($row_sel_comp_srch['landline_display'])!='')
                    $phone_searchArr    =   array_merge($phone_searchArr,explode(",",trim($row_sel_comp_srch['landline_display'])));
                if(trim($row_sel_comp_srch['tollfree_display'])!='')
                    $phone_searchArr    =   array_merge($phone_searchArr,explode(",",trim($row_sel_comp_srch['tollfree_display'])));
                if(trim($row_sel_comp_srch['fax'])!='')
                    $phone_searchArr    =   array_merge($phone_searchArr,explode(",",trim($row_sel_comp_srch['fax'])));
                if(trim($row_sel_comp_srch['virtualNumber'])!='')
                    $phone_searchArr    =   array_merge($phone_searchArr,explode(",",trim($row_sel_comp_srch['virtualNumber'])));
                if(trim($row_sel_comp_srch['tollfree'])!='')
                    $phone_searchArr= array_merge($phone_searchArr,explode(",",trim($row_sel_comp_srch['tollfree'])));

                $phone_searchArr = array_filter($phone_searchArr);
                $new_phone_search = implode(",",$phone_searchArr);
                
                $insertarr = array();
                $insertarr['tbl_companymaster_search']	= array("phone_search" => $new_phone_search);
                $whrCond = "parentid = '".$this->parentid."'";
				$this->compmaster_obj->UpdateFields($insertarr,$whrCond);
			
                /*$update_tbl_compsrch = "UPDATE tbl_companymaster_search SET phone_search = '".$new_phone_search."' WHERE parentid = '".$pid."'";
                $res_update_tbl_compsrch = $this->conn_iro->query_sql($update_tbl_compsrch,$this->parentid,true);*/
            }
        }
        unset($row_sel_comp_srch);
    }
    
    function remoteAllocateVirtualDb($vn='')
    {
		if($vn=='' || $vn==0)
		{
			$virtualNo = 0;

			$joinfiedsname	= "mm.virtualNo, mm.mappedNo, mm.contractid, mm.free_flag, mm.approved_flag, mm.city";
			$jointablesname = DB_JDS_LIVE.".tbl_virtual_num_mapping_master mm LEFT JOIN tbl_companymaster_generalinfo cm";
			$joincondon		= " ON (mm.virtualNo=cm.virtualNumber)";
			$wherecond		= "(cm.virtualnumber='' OR cm.virtualnumber IS NULL) AND (mm.contractid='' OR mm.contractid IS NULL) AND free_flag=0 AND (mm.mappedNo='' or mm.mappedNo='0' or mm.mappedNo is null) AND mm.approved_flag=0 AND mm.virtualNo!=0 AND mm.city = '".$this->city_vn."'";
			$limit			= "1 FOR UPDATE";
			$res_get_vn_from_db = $this->compmaster_obj->joinRow($joinfiedsname ,$jointablesname,$joincondon,$wherecond ,'',$limit);

			if($res_get_vn_from_db['numrows']>0)
			{
				$row_vno=$res_get_vn_from_db['data']['0'];
				$virtualNo = $row_vno['virtualNo'];
				$sql_update_vr_mst = "UPDATE " . DB_JDS_LIVE . ".tbl_virtual_num_mapping_master SET mappedNo = '".$this->top_mappednumber_withoutstd[0]."', contractid = '". $this->parentid. "', free_flag = 1, approved_flag = 0, VrnPaidContract = '".$this->techinfoPidStatus."' WHERE virtualNo = '".$virtualNo."' AND city = '".$this->city_vn."'";
				$res_update_vr_mst = $this->execute_query($sql_update_vr_mst,$this->conn_decs);
				
				$extra_str="[virtual number :".$virtualNo."][update query:".$sql_update_vr_mst."][update qry result:".$res_update_vr_mst."]";
				$this->logmsgvirtualno("assign new virtual number in remotecity ",$this->log_path,$this->processName,$this->parentid,$extra_str);
				
				$count = mysql_affected_rows();
				if($count > 0)
				{
					mysql_query("COMMIT") or die(mysql_error());
				}
				else 
				{
					mysql_query("ROLLBACK");
				}
			}
			else
			{
				if(mysql_num_rows($res_get_vn_from_db)==0)
				{
					$extra_str="[virtual number count:".mysql_num_rows($res_get_vn_from_db)."]";
					$this->logmsgvirtualno("Virtual number invetory is full..",$this->log_path,$this->processName,$this->parentid,$extra_str);
					
					$message = "<br>ParentId = " . $this->parentid . "<br>Process Name = ".$this->processName."<br>Reason = Virtual number invetory is full <br>City = ". $this->city_vn;
					$subject = $this->city_vn ." - Virtual number inventory is full";
					$this->sendMail($subject, $message);
					$logtbl_process_flag=1;
					$logtbl_reason = "virtualnumber inventory full in remote city";
					$process_name = "allocate virtual number in remote city";
					$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
				}
			}
		}
		else
		{
			$sql_update_vr_mst = "UPDATE " . DB_JDS_LIVE . ".tbl_virtual_num_mapping_master SET mappedNo = '".$this->top_mappednumber_withoutstd[0]."', approved_flag = 0, VrnPaidContract = '".$this->techinfoPidStatus."' WHERE virtualNo = '".$vn."' and city = '".$this->city_vn."'";
			$res_update_vr_mst = $this->execute_query($sql_update_vr_mst,$this->conn_decs);
			$extra_str="[virtual number :".$vn."][update query:".$sql_update_vr_mst."][update qry result:".$res_update_vr_mst."]";
			$this->logmsgvirtualno("update mapped number",$this->log_path,$this->processName,$this->parentid,$extra_str);
			$virtualNo =$vn;
		}
		return $virtualNo;
	}
	
	function updateMapDetails($virtualNo)
	{
		$pid_str = '';
		$current_date = date("Y-m-d");						   // 2012-12-24
		$current_time = date("H:i:s");
		if($this->linkcontract_flag)
        {
			$pid_str = implode("','",$this->linkcontracts);
		}
		else
		{
			$pid_str = $this->parentid;
		}
		$sql_get_matched_vn = "SELECT SVCode,Contract from mapdetails where Contract in ( '".$pid_str."')";
		$res_get_matched_vn = $this->execute_query($sql_get_matched_vn,$this->mapdetails_conn);
		if($res_get_matched_vn)
		{
			if(mysql_num_rows($res_get_matched_vn) == 0)
			{
				$sqlInsertQry = "INSERT into mapdetails SET
								LContract  = '".$this->parentid."',
								Contract   = '".$this->parentid."',
								SVCode     = '".$virtualNo."',
								Phone      = '".$this->top_mappednumber_withoutstd[0]."',
								Email      = '".$this->email_feedback."',
								Mobile     = '".$this->mobile_feedback."',
								VrnPaidContract = '".$this->techinfoPidStatus."',
								UpdateDate = '".$current_date."',
								UpdateTime = '".$current_time."'

								ON DUPLICATE KEY UPDATE
								SVCode     = '".$virtualNo."',
								Phone      = '".$this->top_mappednumber_withoutstd[0]."',
								VrnPaidContract = '".$this->techinfoPidStatus."',
								Email      = '".$this->email_feedback."',
								Mobile     = '".$this->mobile_feedback."',
								UpdateDate = '".$current_date."',
								UpdateTime = '".$current_time."'";
				$resInsert = $this->execute_query($sqlInsertQry,$this->mapdetails_conn);
			}
			else
			{
				$row_get_matched_vn = mysql_fetch_assoc($res_get_matched_vn);
				$sqlInsertQry = "UPDATE  mapdetails SET
								Phone      = '".$this->top_mappednumber_withoutstd[0]."',
								Email      = '".$this->email_feedback."',
								Mobile     = '".$this->mobile_feedback."',
								VrnPaidContract = '".$this->techinfoPidStatus."',
								UpdateDate = '".$current_date."',
								UpdateTime = '".$current_time."'
								where
								Contract   = '".$row_get_matched_vn['Contract']."'";
				$resInsert = $this->execute_query($sqlInsertQry,$this->mapdetails_conn);
			}
			$sqlInsertLogQry = "INSERT into mapdetails_log SET
							Contract   = '".$this->parentid."',
							SVCode     = '".$virtualNo."',
							Phone      = '".$this->top_mappednumber_withoutstd[0]."',
							Email      = '".$email_feedback."',
							Mobile     = '".$this->mobile_feedback."',
							UpdateDate = '".$current_date."',
							UpdateTime = '".$current_time."'";
			$resInsertLog = $this->execute_query($sqlInsertLogQry,$this->mapdetails_conn);
		}
	}
	
	function blockForVirtualnumber($parentid,$reason,$block_flag,$jdfos=0)
	{
		$this->quarantineFlag = false;
		$this->initialized($parentid);
		$this->getinitailvalue();
		$this->getLinkedContracts();
		$this->quarantineFlag = $this->checkQuarantineVn();
		
		$extra_str="[block for VN parentids : ".$parentid."][reason:".$reason."][block flag :".$block_flag."][Quarantine Flag : ".intval($this->quarantineFlag)."][JDFOS flag:".intval($jdfos)."]";
        $this->logmsgvirtualno("forcefully Block/Unblock for virtual number process start",$this->log_path,'Block for virtual number',$this->parentid,$extra_str);
            
		if($this->linkcontract_flag)
        {
            $this->sort_link_contract();
            $parenid_list = implode("','",$this->linkcontracts);
            foreach ($this->linkcontracts as $pid)
            {
                $insrt_reason = $this->InsertVN_reason($pid, $reason, $block_flag);
                $extra_str="[block parentids : ".$parenid_list."][reason:".$reason."][block flag :".$block_flag."][insert successfulty in table:".$insrt_reason."]";
                $this->logmsgvirtualno("forcefuly Block for virtual number",$this->log_path,'Block for virtual number',$this->parentid,$extra_str); 
            }
        }
        else
        {
            $parenid_list = $this->parentid;
            $insrt_reason = $this->InsertVN_reason($this->parentid, $reason, $block_flag);
            $extra_str="[block parentids : ".$parenid_list."][reason:".$reason."][block flag :".$block_flag."][insert successfulty in table:".$insrt_reason."]";
            $this->logmsgvirtualno("forcefuly Block for virtual number",$this->log_path,'Block for virtual number',$this->parentid,$extra_str); 
        }
        
        if($block_flag==0)
        {
			//$this->updateBlockUnblockGenio($parenid_list,$block_flag);
			$this->updateBlockUnblockGenio($this->parentid,$block_flag);
			if($this->quarantineFlag)
			{
				$rvQrFg = $this->removeQuarantine($this->virtualno);
				if(!$rvQrFg)
				{
					$message = "<br>ParentId = " . $parentid . "<br>Process Name = Block/unblock virtual number <br>Reason = " . $reason . "<br>virtual number = " . $this->virtualno . "<br>City = ". $this->city_vn;
					$subject = $this->city_vn." - Quarantine virtual number not remove";
					$this->sendMail($subject, $message);
				}
				$insarr['tbl_companymaster_generalinfo'] = array("virtualNumber" =>intval($this->virtualno),"parentid" => $this->parentid);
				$this->compmaster_obj->UpdateRow($insarr);

				$extra_str="[updated virtual number:".$this->virtualno."][Update query Arr :".json_encode($insarr)."]";
				$this->logmsgvirtualno("Update tbl companymaster general info",$this->log_path,$this->processName,$this->parentid,$extra_str);
			}
			
			$this->genio_update_virtual_number($parentid,'','');
			$this->insert_narration($reason);
            if($this->linkcontract_flag && false)
            {
				$this->updateCompLinkVn($this->linkcontracts);
			}
		}
		elseif($block_flag==1)
		{
			//$this->updateBlockUnblockGenio($parenid_list,$block_flag);
			$this->updateBlockUnblockGenio($this->parentid,$block_flag);
			if(!$this->quarantineFlag && intval($this->virtualno)>0)
			{
				$this->QuarantineNo('block for virtual number');
			}

			$insertarr	= array();
			$insertarr['tbl_companymaster_generalinfo']	= array("virtualNumber" => '',"virtual_mapped_number" => '',"pri_number" => '');
			$whereCond	= "parentid in ( '".$parenid_list."')";
			//$this->compmaster_obj->UpdateFields($insertarr,$whereCond);
			
			$update_cm_general = "UPDATE tbl_companymaster_generalinfo SET virtualNumber='',virtual_mapped_number='',pri_number='' WHERE parentid IN ('".$parenid_list."')";
			$res_update_cm_general	=	$this->execute_query($update_cm_general,$this->conn_iro);

			$extra_str="[updated virtual number:".$this->virtualno."][update Qry :".$QryBlockvnComp."][update qry result: ".$ResBlockvnComp."]";
			$this->logmsgvirtualno("Update block virtual number in tbl companymaster ganeral info",$this->log_path,$this->processName,$this->parentid,$extra_str);
			$this->updateCompanyMasterSearch();
			$this->initializedJdfosurl();
			$this->checkJdfos();
			if($this->JDFOS_flag)
			{
				$this->updateJdfosDb();
			}
			$this->DialableNumberClass->upDateDialableNumber($this->parentid,$this->conn_iro);
			/*if($this->linkcontract_flag)
			{
				foreach($this->linkcontracts as $key =>$val)
				{
					$this->updateCompanyMasterSearch($val);
				}
			}
			else
			{
				$this->updateCompanyMasterSearch();
			}*/
		}
		else if($block_flag == 2)
		{
			$reason = "Update block virtual number in tbl companymaster ganeral info";
			$this->updateBlockUnblockGenio($this->parentid,$block_flag);
			$extra_str="[updated virtual number:".$this->virtualno."][update both Vn & actual Number]";
			$this->logmsgvirtualno("Update block virtual number in tbl companymaster ganeral info",$this->log_path,$this->processName,$this->parentid,$extra_str);
			$this->insert_narration($reason);
		}
	}
	
	function InsertVN_reason($parenid_list,$reason, $block_flag)
	{
		if($parenid_list) {
			$sql =" INSERT INTO tbl_block_VN_reason
					SET
						parentid	= '".$parenid_list."',
						reason		= '".addslashes($reason)."',
						userid		= '".$this->usercode."',
						block_flag	= '".$block_flag."',
						IsDealClosed= -1,
						data_time	= '".$this->insertion_time."'	";

			$res = $this->execute_query($sql,$this->conn_decs);
			
			$extra_str="[Qry :".$sql."][Qry. result:".$res."]";
			$this->logmsgvirtualno("Insert block for virtual number reason in Table",$this->log_path,$this->processName,$this->parentid,$extra_str);
			if($res) 
				return true;
		}
		return false;
	}
	
	function updateBlockUnblockGenio($pidlist,$flag)
	{
		$insert_field['tbl_companymaster_generalinfo'] = array("blockforvirtual"=>$flag);
		$whereCond	= "parentid IN ('".$pidlist."')";
		//$this->compmaster_obj->UpdateFields($insert_field,$whereCond);
		$update_cm_general = "UPDATE tbl_companymaster_generalinfo SET blockforvirtual='".$flag."' WHERE parentid IN ('".$pidlist."')";
		$res_update_cm_general	=	$this->execute_query($update_cm_general,$this->conn_iro);
		
		$sql_update_cm_extra = "UPDATE tbl_companymaster_extradetails SET db_update = '".date('Y-m-d H:i:s')."' WHERE parentid IN ('".$pidlist."')";
		$res_update_cm_extra = $this->execute_query($sql_update_cm_extra,$this->conn_iro);
		
		$extra_str="[Qry :".$update_cm_general."][Qry. result:".$res_update_cm_general."]";
		$this->logmsgvirtualno("Update companymaster block for virtual number",$this->log_path,$this->processName,$this->parentid,$extra_str);
	}
	
	function updateCompLinkVn($linkpid=array())
	{
		if(is_array($linkpid) && count($linkpid)>0)
		{
			if(intval($this->virtualno)>0)
			{
				$this->getPriNumber();
			}
			foreach($linkpid as $key => $value)
			{
				if(trim($value)!=$this->parentid)
				{
					$temparr	= array();
					$fieldstr	= "parentid,landline_display,mobile_display,tollfree";
					$where 		= "parentid='".$value."'";
					$resGetLinkMap	= $this->compmaster_obj->getRow($fieldstr,"tbl_companymaster_generalinfo",$where);

					if($resGetLinkMap['numrows']>0)
					{
						$tempLandlineArr	= array();
						$tempMobileArr 		= array();
						$tempTollfreeArr	= array();
						$tempContactArr		= array();
						$tempMappedNo = '';
						$rowGetLinkMap = $resGetLinkMap['data']['0'];
						if($rowGetLinkMap['landline_display']!='')
						{
							$tempLandlineArr = explode(",",$rowGetLinkMap['landline_display']);
							$tempLandlineArr = array_merge(array_filter($tempLandlineArr));
						}
						if($rowGetLinkMap['mobile_display']!='')
						{
							$tempMobileArr = explode(",",$rowGetLinkMap['mobile_display']);
							$tempMobileArr = array_merge(array_filter($tempMobileArr));
						}
						if($rowGetLinkMap['tollfree']!='')
						{
							$tempTollfreeArr = explode(",",$rowGetLinkMap['tollfree']);
							$tempTollfreeArr = array_merge(array_filter($tempTollfreeArr));
						}
						$tempContactArr = array_merge($tempLandlineArr,$tempMobileArr,$tempTollfreeArr);
						if(count($tempContactArr)>0)
						{
							$tempMappedNo = $tempContactArr[0];
						}
						if(intval($this->virtualno)>0 && intval($tempMappedNo)>0)
						{
							$insertarr	= array();
							$insertarr['tbl_companymaster_generalinfo']	= array("virtualNumber" => $this->virtualno,"virtual_mapped_number" => $tempMappedNo,"pri_number" => $this->pri_number,"parentid"=>$this->parentid);
							$this->compmaster_obj->UpdateRow($insertarr);

							$extra_str="[updated virtual number:".$this->virtualno."][update Qry :".$QryTempUpdateComp."][update qry result: ".$ResTempUpdateComp."]";
							$this->logmsgvirtualno("Update all link parentid virtual number",$this->log_path,$this->processName,$this->parentid,$extra_str);

							$this->updateCompanyMasterSearch($value);
						}
					}
					mysql_free_result($resGetLinkMap);
					unset($rowGetLinkMap);
				}
			}
		}
	}
	
	function getChangedDatacityVn()
	{
	}
	
	function techInfoPaidStatus()
	{
		$jdPaid = 0;
		$jdFos = 0;
		$shopfront = 0;
		if($this->linkcontract_flag)
        {
			$parenid_list = implode("','",$this->linkcontracts);
		}
		else
		{
			$parenid_list = $this->parentid;
		}
		$qryCheckPhoneSearcgStatus = "select parentid,campaignid from tbl_companymaster_finance where parentid in ('".$parenid_list."') AND campaignid NOT IN (22, 17, 86, 87, 88, 84, 83, 82, 75, 74, 73, 72 ) and (balance > 0 or (balance<=0 and expired=0 and manual_override=1)) ";
		$resCheckPhoneSearcgStatus =	$this->execute_query($qryCheckPhoneSearcgStatus,$this->conn_finance);
		
		$qryCheckPhoneSearcgStatus_national = "select parentid,campaignid from db_national_listing.tbl_companymaster_finance_national where parentid in ('".$parenid_list."') and campaignid in (10) and balance>0";
		$resCheckPhoneSearcgStatus_national =	$this->execute_query($qryCheckPhoneSearcgStatus_national,$this->conn_idc);
		
		if($resCheckPhoneSearcgStatus  || $resCheckPhoneSearcgStatus_national)
		{
			if(mysql_num_rows($resCheckPhoneSearcgStatus)>0  || mysql_num_rows($resCheckPhoneSearcgStatus_national)>0)
			{
				$jdPaid = 1;
			}
		}
		$qryCheckJDFOSstatus = "select parentid,campaignid,balance from tbl_companymaster_finance where parentid in ('".$parenid_list."') and campaignid in (23)";
		$resCheckJDFOSstatus =	$this->execute_query($qryCheckJDFOSstatus,$this->conn_finance);
		if($resCheckJDFOSstatus)
		{
			if(mysql_num_rows($resCheckJDFOSstatus)>0)
			{
				$rowCheckJDFOSstatus = mysql_fetch_assoc($resCheckJDFOSstatus);
				if($rowCheckJDFOSstatus['balance']>0){
					$jdFos = 1;
				}else{
					if(intval($this->JDFOS_flag)==1)
					{
						$jdFos = 1;
					}
				}
			}
			else
			{
				if(intval($this->JDFOS_flag)==1)
				{
					$jdFos = 1;
				}
			}
		}
		
		if($this->shopfront_flag){
			$shopfront = 1;
		}
		$this->techinfoPidStatus = bindec($shopfront.$jdFos.$jdPaid);
		
		return $this->techinfoPidStatus;
	}
	
	function checkBlockflag($pid)
	{
		$flag = false;
		$temparr	= array();
		$fieldstr	= "blockforvirtual";
		$where 		= "parentid='".$pid."' and virtualNumber>0";
		$resGetVnflag	= $this->compmaster_obj->getRow($fieldstr,"tbl_companymaster_generalinfo",$where);

		if($resGetVnflag['numrows']>0)
		{
			$flag = true;
		}
		else
		{
			$temparr	= array();
			$fieldstr	= "blockforvirtual";
			$where 		= "parentid='".$pid."' and virtualNumber>0";
			$resGetBlockflag	= $this->compmaster_obj->getRow($fieldstr,"tbl_companymaster_generalinfo",$where);

			if($resGetBlockflag['numrows'])
			{
				$flag = true;
			}
		}
		return $flag;
	}
	
	function processCheckJdfos(){
		$jdfosprocessflag = false;
		$this->checkJdfos();
		if($this->JDFOS_flag){
			$jdfosprocessflag = true;
		}
		return $jdfosprocessflag;
	}
	
	function checkJdfos()
	{
		$sql_res="select docid from tbl_id_generator WHERE parentid = '".$this->parentid."'";
		$sql_res = $this->execute_query($sql_res,$this->conn_iro);
		$rows = mysql_fetch_row($sql_res);
		if($rows[0])
		{
		$this->docid = trim($rows[0]);
		$extra_str = "[docid = ".$this->docid."]";
		$this->logmsgvirtualno("calling restaurant api from docid from id_generator table",$this->log_path,$this->processName,$this->parentid,$extra_str);
		}
		else
		{
		$this->docid = $this->GetMajorCity($this->city_vn);
		$extra_str = "[docid = ".$this->docid."]";
		$this->logmsgvirtualno("calling restaurant api from docid from function ",$this->log_path,$this->processName,$this->parentid,$extra_str);
		}
		//print_r($this->docid);
		$jdfocCurlurl =  $this->jdfos_url."restDet/".$this->docid;
		$jdfocCurlurl_output ='';
		$jdfocCurlurl_output = $this->runCurlUrl($jdfocCurlurl,'GET',0);
		if(!$this->curl_response_flag)
		{
			$message = "<br>ParentId = " . $this->parentid . "<br>Process Name = ".$this->processName."<br>Reason = JDFOS fetch url not wokring<br>Checking contract is JDFOS or not<br>City = ". $this->city_vn."<br>Virtual NO : ".$jdfosVno."<br>Url : ".$jdfocCurlurl;
			$subject = $this->city_vn ." - JDFOS fetch url not working";
			$this->sendMail($subject, $message);
		}
		else
		{
			$jdfos_json = array();
			$jdfos_json = json_decode($jdfocCurlurl_output,true);
			$commission_arr = explode(",",$jdfos_json['results']['gen_info']['0']['commission_percent']);
			foreach ($commission_arr as $key => $value)
			{		
				if($value > 0)
					{
						$jdcommission = 1;
						break;
					}
			}		
			
			$extra_str="[jdfos curl parentid : ".$jdfos_json['results']['gen_info']['0']['parentid']."][JDFOS curl url jdfos condition : isOnline =".$jdfos_json['results']['gen_info']['0']['isonline'].",isRegistered = ".$jdfos_json['results']['gen_info']['0']['isRegistered'].", isActive = ".$jdfos_json['results']['gen_info']['0']['isActive'].", jdcommission_percent = ".$jdfos_json['results']['gen_info']['0']['commission_percent']."]";
			$this->logmsgvirtualno("JDFOS url output",$this->log_path,$this->processName,$this->parentid,$extra_str);
			if(strtoupper(trim($jdfos_json['results']['gen_info']['0']['parentid'])) == strtoupper($this->parentid) && $jdfos_json['results']['gen_info']['0']['isonline']==1 && $jdfos_json['results']['gen_info']['0']['isRegistered']==1 && ($jdfos_json['results']['gen_info']['0']['isActive']==1 || $jdfos_json['results']['gen_info']['0']['isActive']==2 ) && ($jdcommission == 1))	
			{
				if(intval($this->JDFOS_flag)==0){
					$this->JDFOS_flag = true;
					$extra_str="[updated virtual number:".$this->virtualno."][ contract present on JDFOS server][jdfos flag:".$this->JDFOS_flag."]";
					$this->logmsgvirtualno("contract not contain JDFOS campaign in finance so checking on JDFOS server",$this->log_path,$this->processName,$this->parentid,$extra_str);
				}
			}
			else if(strtoupper(trim($jdfos_json['results']['gen_info']['0']['parentid'])) == strtoupper($this->parentid))
			{
				$this->jdfos_pid_chk_flag = true;
				$extra_str="[updated virtual number:".$this->virtualno."][Contract Exists On JDFOS Server without jdfos condition][jdfos_pid_chk_flag:".$this->jdfos_pid_chk_flag."]";
				$this->logmsgvirtualno("Contract Exists On JDFOS Server without jdfos condition",$this->log_path,$this->processName,$this->parentid,$extra_str);
			}
			else
			{
				$this->JDFOS_flag = false;
				$extra_str="[updated virtual number:".$this->virtualno."][ contract not present on JDFOS server][jdfos flag:".$this->JDFOS_flag."]";
				$this->logmsgvirtualno("contract not contain JDFOS campaign in finance so checking on JDFOS server",$this->log_path,$this->processName,$this->parentid,$extra_str);
			}
		}
	}
	function GetMajorCity($data_city)
		{
			$sql_stdcd="SELECT stdcode FROM  d_jds.city_master WHERE ct_name='".$data_city."'";
			$res_std = $this->execute_query($sql_stdcd,$this->conn_iro);
			
			if($res_std && mysql_num_rows($res_std))
			{
				$row_stdcd= mysql_fetch_assoc($res_std);
			}
			$strstdcd=$row_stdcd['stdcode'];
		
			$sql_datacity="SELECT city_name FROM db_iro.tbl_major_cities WHERE city_name='".$data_city."'";
			$res = $this->execute_query($sql_datacity,$this->conn_iro);
			//$res=$this->iro->query_sql($sql_datacity);
			if($res && mysql_num_rows($res))		
			{
				$row_c=mysql_fetch_assoc($res);
			}
			if($row_c != ''){
					$docid= $strstdcd.$this->parentid;
					//echo'if='.$docid;
					return $docid; 
				}else{
					$docid="9999".$this->parentid;
				   // echo'else='.$docid;
				    return $docid;
				}
				
		}
		
		function checkOtherVertical(){
		$mandateVerticalArr = array();
		$otherverticalFlag = 0;
		
		
			$qryCheckActiveMandate = "SELECT parentid,vertical_flag FROM db_ecs.ecs_mandate WHERE parentid = '".$this->parentid."' AND deactiveflag=0 AND ecs_stop_flag=0 AND activeFlag = 1 AND vertical_flag>0 AND mandate_type='Vertical'";
			$resCheckActiveMandate = $this->execute_query($qryCheckActiveMandate,$this->conn_finance);
			if($resCheckActiveMandate && mysql_num_rows($resCheckActiveMandate)>0){
				while($rowCheckActiveMandate= mysql_fetch_assoc($resCheckActiveMandate)){
					$mandateVerticalArr[] = $rowCheckActiveMandate['vertical_flag'];
				}
			}else{
				mysql_free_result($resCheckActiveMandate);
				$qryCheckActiveMandate = "SELECT parentid,vertical_flag FROM db_si.si_mandate WHERE parentid = '".$this->parentid."' AND deactiveflag=0 AND ecs_stop_flag=0 AND activeFlag = 1 AND vertical_flag>0 AND mandate_type='Vertical'";
				$resCheckActiveMandate = $this->execute_query($qryCheckActiveMandate,$this->conn_finance);
				if($resCheckActiveMandate && mysql_num_rows($resCheckActiveMandate)>0){
					while($rowCheckActiveMandate= mysql_fetch_assoc($resCheckActiveMandate)){
						$mandateVerticalArr[] = $rowCheckActiveMandate['vertical_flag'];
					}
				}
			}
			if(count($mandateVerticalArr)>0){
				foreach($mandateVerticalArr as $verticalVal){
					if(in_array($verticalVal,$this->VnVerticalArr)){
						$otherverticalFlag = 1;
						break;
					}
				}
			}
		
		return $otherverticalFlag;
	}
	
	function checkShopfrontFlag(){
		$this->shopfront_flag = false;
		/*$this->check_flag = 0;
		if(APP_LIVE == 1)
		{
			$params_sf_url = VERTICAL_API."sf_info.php?docid=".$this->docid."&type_flag=32&formate=basic";
		}
		else
		{
			   $params_sf_url = "http://rohnyshende.jdsoftware.com/web_services/web_services/web_services/sf_info.php?docid=".$this->docid."&type_flag=32&formate=basic";
		}
		
		$postarr = $this->makeRelianceUrl($businessid);
		$response = $this->runCurlUrl($params_sf_url,'POST',$postarr);
		$responsearr = json_decode($response,true);
		$responsedocid= $responsearr['results']['compdetails']['docid'];
		if(APP_LIVE == 1)
		{
			$sf_info_url     =  "http://192.168.20.105:1080/services/opt/sf_ApiResponse.php?docid=".$this->docid."&parentid=".$this->parentid."&data_city=".$this->city_vn;
		}
		else
		{
			$sf_info_url ="http://rohnyshende.jdsoftware.com/QUICK_SERVICES/services/opt/sf_ApiResponse.php?docid=".$this->docid."&parentid=".$this->parentid."&data_city=".$this->city_vn."&development=1";
		}
				
	  $postarr = $this->makeRelianceUrl($businessid);		
	  $response_info = $this->runCurlUrl($sf_info_url,'POST',$postarr);
	  
	  $response_info_arr = json_decode($response_info,true);
	  
		 if(($response_info_arr['sf_deactive_flag']==0 && $response_info_arr['sf_opted_Flag']==1) || ($responsedocid!=null && strlen($responsedocid)>3))
		 {
			 $this->check_flag=1;
		 }
		if($this->check_flag == 1)
		{
		$extra_str ="response_deacitve_flag=".$response_info_arr['sf_deactive_flag']." response_sf_opt_flag=".$response_info_arr['sf_opted_Flag']."  docid=".$responsedocid." Response with of all api flag=".$this->check_flag;
		$this->logmsgvirtualno("Shopfront Tagging ",$this->log_path,$this->processName,$this->parentid,$extra_str);
		
		}
		else
		{
		$extra_str ="response_deacitve_flag=".$response_info_arr['sf_deactive_flag']." response_sf_opt_flag=".$response_info_arr['sf_opted_Flag']."  docid=".$responsedocid." Response with of all api flag=".$this->check_flag;
		$this->logmsgvirtualno("Shopfront Tagging ",$this->log_path,$this->processName,$this->parentid,$extra_str);		
		}*/
		$qryCheckShopfront = "SELECT * FROM db_iro.tbl_companymaster_extradetails WHERE parentid = '".$this->parentid."' AND website_type_flag &32=32 and businesstags&2=2" ;
		$resCheckShopfront = $this->execute_query($qryCheckShopfront,$this->conn_iro);
		$row_num = mysql_num_rows($resCheckShopfront);
		//echo'<pre>';print_r($row_num);
		$jdcommision_query = "SELECT DISTINCT parentid FROM tbl_categorywise_commission WHERE parentid = '".$this->parentid."' ORDER BY entry_date DESC";
		$resjdcommision_query = $this->execute_query($jdcommision_query,$this->conn_finance);
		$row_num_commission = mysql_num_rows($resjdcommision_query);
		
		if(($resCheckShopfront && ($row_num>0)) && ($resjdcommision_query && ($row_num_commission>0)) /*&& $this->check_flag*/){
				$this->shopfront_flag  = true;	
		}
		$extra_str="[First Qry run  : ".$qryCheckShopfront."][Frist Qry result : ".$resCheckShopfront." ][Frist num of rows : ".$row_num."][Second Qry run  : ".$jdcommision_query."][Second Qry result : ".$resjdcommision_query." ][Second Qry num of rows : ".$row_num_commission."][shop_front_flag : ".$this->shopfront_flag."]";
		$this->logmsgvirtualno("shop_front_flag",$this->log_path,$this->processName,$this->parentid,$extra_str);	
	}
	
	function quarantine_ineligible_cat_exists($action)
	{
		$quarantine_pid_arr = array();
		if($this->linkcontract_flag && count($this->linkcontracts)>0){
			$quarantine_pid_arr = $this->linkcontracts;
		}else{
			$quarantine_pid_arr[] = $this->parentid;
		}
		foreach($quarantine_pid_arr as $deallocate_pid)
		{
			$block_vn_quaran = false;
			
			
			if(intval($this->virtualno) >0 && ($this->blockVn_flag==1 || $this->quarantineFlag==1 || intval($this->opt_in_category_flag) == 1 || intval($this->block_vn_cat_flag) == 1))
			{	
			
				$block_vn_quaran = chk_quaran_ineligible_cat_exists($deallocate_pid,$this->virtualno,$this->conn_decs);
			    
				$insert_quarantine_flag = 1;
				
				
				if($block_vn_quaran)
				{
					$quarantine_dt 		= get_quaran_date_ineligible_cat_exists($deallocate_pid,$this->virtualno,$this->conn_decs);
					$quarantine_dt_new 	= date_create($quarantine_dt['start_date']);
					$quarantine_reason 	= $quarantine_dt['reason'];

					$current_date = date("Y-m-d");
					$cur_date_new = date_create($current_date);

					$date_difference = date_diff($quarantine_dt_new,$cur_date_new);
					$date_diff_new 	 = $date_difference->format("%a");
					
				//	echo intval($date_diff_new);
					//$this->currentexpired = true;
					
					if(( (intval($date_diff_new)>30 && ($quarantine_reason == 'Paid expired into Quarantine' || $quarantine_reason == 'block for virtual number')) || (intval($date_diff_new)>180)) && ((intval($this->paid_flag) != 1 && $this->verticalFlag != 1 && $this->JDFOS_flag== false) || $quarantine_reason == 'block for virtual number' || intval($this->currentexpired) == 1 ))
					{
						
						
						$insert_quarantine_flag = 0;
						rmv_quaran_blockforvncat($deallocate_pid,$this->virtualno,$this->ineligible_cat_process,$this->log_path,$this->processName,$this->usercode,$this->conn_decs);
						
						if(in_array(strtoupper($this->city_vn),$this->main_city_arr))
						{
							
							if(in_array(strtoupper($this->city_vn),$this->reliance_main_city)){
								if(in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
								{
									$curl_url = $this->techinfo_url."action=vmDelete&vmnNumber=".$this->CountryStdCode.trim($this->virtualno);
								}
								elseif(!in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
								{
									$curl_url = $this->techinfo_url."actualnumber/virtualnumber/".$this->CountryStdCode.trim($this->virtualno)."/user/Jack";	
								}
								
								$curl_res = $this->runCurlUrl($curl_url,'DELETE');
							}else{
								$curl_url = $this->techinfo_url."deallocate.php?VN=".trim($this->virtualno)."&User=".$this->usercode;
								$curl_res = $this->runTechInfoCurlUrl($curl_url);
							}
							
							if(!$this->curl_response_flag)
							{
								$extra_str="[Techinfo url not working for deallocation][url :".$curl_url."]";
								$this->logmsgvirtualno("Deallocating virtual number in techinfo",$this->log_path,$this->processName,$deallocate_pid,$extra_str);
							}
							else
							{
								$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."]";
								$this->logmsgvirtualno("Ineligible Contract Found.",$this->log_path,$this->processName,$deallocate_pid,$extra_str);
							}
						}
						else if(in_array(strtoupper($this->city_vn),$this->remote_city_arr))
						{
							if(in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
								{
									$curl_url = $this->rcom_remote_url."action=vmDelete&vmnNumber=".$this->CountryStdCode.trim($this->virtualno);
								}
								elseif(!in_array(strtoupper($this->city_vn),$this->reliance_Ozonetel))
								{
									$curl_url = $this->rcom_remote_url."actualnumber/virtualnumber/".$this->CountryStdCode.trim($this->virtualno)."/user/Jack";	
								}
							$curl_res = $this->runCurlUrl($curl_url,'DELETE');
							$sqlDeallocateVNRemote = "UPDATE d_jds.tbl_virtual_num_mapping_master SET mappedNo = null, contractid = '', curr_time = '0000-00-00 00:00:00', free_flag = 0, approved_flag = 0, VrnPaidContract = null WHERE virtualNo = '".$this->virtualno."' AND city = '".$this->city_vn."'";
							$resDeallocateVNRemote = $this->execute_query($sqlDeallocateVNRemote,$this->conn_decs);
						}
						else if(!in_array(strtoupper($this->city_vn),$this->remote_city_arr) && !in_array(strtoupper($this->city_vn),$this->main_city_arr))
						{
							$curl_url = $this->techinfo_url."deallocate.php?VN=".trim($this->virtualno)."&User=".$this->usercode;
							$curl_res = $this->runTechInfoCurlUrl($curl_url);
							if(!$this->curl_response_flag)
							{
								$extra_str="[Techinfo url not working for deallocation][url :".$curl_url."]";
								$this->logmsgvirtualno("Deallocating virtual number in techinfo",$this->log_path,$this->processName,$deallocate_pid,$extra_str);
							}
							else
							{
								$extra_str="[paid flag : ".$this->paid_flag."][expired flag : ".intval($this->currentexpired)."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."]";
								$this->logmsgvirtualno("Ineligible Contract Found.",$this->log_path,$this->processName,$deallocate_pid,$extra_str);
							}
						}
					}
				}
				
				if(!$block_vn_quaran && ($insert_quarantine_flag == 1))
				{
					insert_quaran_ineligible_cat_exists($deallocate_pid,$this->virtualno,$this->ineligible_cat_process,$this->log_path,$this->processName,$this->usercode,$this->conn_decs);
				}		
			}
			$insertarr_geninfo	= array();
			$insertarr_geninfo['tbl_companymaster_generalinfo']	= array("virtualNumber" => '',"virtual_mapped_number" => '',"pri_number" => '',"parentid"=>$deallocate_pid);
			$this->compmaster_obj->UpdateRow($insertarr_geninfo);
			
			$insertarr_extra["tbl_companymaster_extradetails"]	= array("db_update" => date('Y-m-d H:i:s'));
			$wherecond		= "parentid = '".$deallocate_pid."'";
			$this->compmaster_obj->UpdateFields($insertarr_extra,$wherecond);
			
			updtcompsrch_ineligible_cat_exists($deallocate_pid,$this->conn_iro);
			$this->DialableNumberClass->upDateDialableNumber($deallocate_pid,$this->conn_iro);
			updtJdfos_ineligible_cat_exists($this->jdfos_url,$deallocate_pid,$this->ineligible_cat_process,$this->log_path,$this->processName,$this->usercode);
		}	
	}
	
	function chk_block_for_vn_category()
	{
		$block_vn_flag = 0;
		
		$temparr	= array();
		$fieldstr	= "catidlineage,catidlineage_nonpaid";
		$where 		= "parentid = '".$this->parentid."'";
		$temparr	= $this->compmaster_obj->getRow($fieldstr,"tbl_companymaster_extradetails",$where);
		
		if($temparr['numrows']>0)
		{
			$row_category_info	= $temparr['data'][0];
			if((isset($row_category_info['catidlineage']) && $row_category_info['catidlineage'] != '') || (isset($row_category_info['catidlineage_nonpaid']) && $row_category_info['catidlineage_nonpaid'] != ''))
			{
                $extra_catlin_arr 	= 	array();
				$extra_catlin_arr   =   explode("/,/",trim($row_category_info['catidlineage'],"/"));
				$extra_catlin_arr 	= 	array_filter($extra_catlin_arr);
				
				$extra_catlin_np_arr = array();
				$extra_catlin_np_arr = explode("/,/",trim($row_category_info['catidlineage_nonpaid'],"/"));
				$extra_catlin_np_arr = array_filter($extra_catlin_np_arr);
				
				$total_catlin_arr = array();
				$total_catlin_arr =  array_merge($extra_catlin_arr,$extra_catlin_np_arr);
				$total_catlin_arr =  array_filter($total_catlin_arr);
				$total_catlin_arr =  array_unique($total_catlin_arr);
				
				$final_catlin_arr = array();
				if(count($total_catlin_arr)>0)
				{
					foreach($total_catlin_arr as $catidval)
					{
						$catidval = trim($catidval,',');
						$catidval = trim($catidval,'/');
						$final_catlin_arr[] = $catidval;
					}
				}
				$final_catlin_arr = array_filter($final_catlin_arr);
				$final_catlin_arr = array_unique($final_catlin_arr);
				
				$catid_lineage_str = implode("','",$final_catlin_arr);
				$sqlBlockVNCategory	=	"select count(catid) as cnt from d_jds.tbl_categorymaster_generalinfo WHERE category_type&4=4 AND catid IN ('".$catid_lineage_str."') GROUP BY catid";
				$resBlockVNCategory	=	$this->conn_decs->query_sql($sqlBlockVNCategory);
				$row_block_vn_cat	=	mysql_fetch_assoc($resBlockVNCategory);
				if($row_block_vn_cat['cnt'] > 0){
					$block_vn_flag = 1;
				}
				$extra_str="[Qry run : ".$sqlBlockVNCategory."][Qry result : ".$resBlockVNCategory."][block_vn_flag : ".$block_vn_flag."]";
				$this->logmsgvirtualno("inside chk_block_for_vn_category function",$this->log_path,$this->processName,$this->parentid,$extra_str);	
			}
		}
		return $block_vn_flag; 
	}
	function is_opt_in_category_exists()
	{
		$opt_in_cat_flag = 0;
		
		$temparr	= array();
		$fieldstr	= "catidlineage,catidlineage_nonpaid";
		$where 		= "parentid = '".$this->parentid."'";
		$temparr	= $this->compmaster_obj->getRow($fieldstr,"tbl_companymaster_extradetails",$where);
		
		if($temparr['numrows']>0)
		{
			$row_category_info	= $temparr['data'][0];
			if((isset($row_category_info['catidlineage']) && $row_category_info['catidlineage'] != '') || (isset($row_category_info['catidlineage_nonpaid']) && $row_category_info['catidlineage_nonpaid'] != ''))
			{
                $extra_catlin_arr 	= 	array();
				$extra_catlin_arr   =   explode("/,/",trim($row_category_info['catidlineage'],"/"));
				$extra_catlin_arr 	= 	array_filter($extra_catlin_arr);
				
				$extra_catlin_np_arr = array();
				$extra_catlin_np_arr = explode("/,/",trim($row_category_info['catidlineage_nonpaid'],"/"));
				$extra_catlin_np_arr = array_filter($extra_catlin_np_arr);
				
				$total_catlin_arr = array();
				$total_catlin_arr =  array_merge($extra_catlin_arr,$extra_catlin_np_arr);
				$total_catlin_arr =  array_filter($total_catlin_arr);
				$total_catlin_arr =  array_unique($total_catlin_arr);
				
				$final_catlin_arr = array();
				if(count($total_catlin_arr)>0)
				{
					foreach($total_catlin_arr as $catidval)
					{
						$catidval = trim($catidval,',');
						$catidval = trim($catidval,'/');
						$final_catlin_arr[] = $catidval;
					}
				}
				$final_catlin_arr = array_filter($final_catlin_arr);
				$final_catlin_arr = array_unique($final_catlin_arr);
				
				$catid_lineage_str = implode("','",$final_catlin_arr);
				
				$sqlOptInCategory	=	"select count(catid) as cnt from d_jds.tbl_categorymaster_generalinfo WHERE number_masking&8=8 AND catid IN ('".$catid_lineage_str."') GROUP BY catid";
				$resOptInCategory	=	$this->conn_decs->query_sql($sqlOptInCategory);
				$row_opt_in_cat	=	mysql_fetch_assoc($resOptInCategory);
				if($row_opt_in_cat['cnt'] > 0){
					$opt_in_cat_flag = 1;
				}
				$extra_str="[Qry run : ".$sqlOptInCategory."][Qry result : ".$resOptInCategory."][opt_in_cat_flag : ".$opt_in_cat_flag."]";
				$this->logmsgvirtualno("inside is_opt_in_category_exists function",$this->log_path,$this->processName,$this->parentid,$extra_str);	
			}
		}
		return $opt_in_cat_flag; 
	}
	function BlockMappedNumber($number)
	{
		$this->initialized($this->parentid);
		$this->getinitailvalue();
		$extra_str="[virtual number :".intval($this->virtualno)."][block number : ".$number."]";
		$this->logmsgvirtualno("Get virtual number in block number process",$this->log_path,$this->processName,$this->parentid,$extra_str);
		$this->getLinkedContracts();
        if($this->linkcontract_flag)
        {
            $this->sort_link_contract();
        }
        $this->vnEligibleCompaign = $this->checkVnEligibleCampaign();
		if(intval($this->virtualno)>0)
		{
			$this->mappednumber_flag = $this->getMappednumber();
			if($this->mappednumber_flag)
			{//$this->finalmappednumbers
				//print "<pre> this->finalmappednumbers : ";print_r($this->finalmappednumbers); echo "VN==>".$this->virtualno."-Blcok No=".$number; 
				/*for($i=0;$i<COUNT($this->finalmappednumbers);$i++)
				{
					echo "<br>".
					
					if(in_array($number,$this->finalmappednumbers))
					{
						unset($number,$this->finalmappednumbers);
					}
				}*/
				
				if(in_array($number,$this->finalmappednumbers))
				{
					$key = array_search($number,$this->finalmappednumbers);
					unset($this->finalmappednumbers[$key]);
					$this->finalmappednumbers =array_merge(array_filter($this->finalmappednumbers));
				}//print "<pre> this->finalmappednumbers : ";print_r($this->finalmappednumbers);
				if(COUNT($this->finalmappednumbers)>0)
				{
					$this->finalmappednumbers = array_filter($this->finalmappednumbers);
				}
				//print "<pre> this->finalmappednumbers : ";print_r($this->finalmappednumbers); echo "VN==>".$this->virtualno; exit;
				if(COUNT($this->finalmappednumbers)>0)
				{
					$extra_str="[virtual number :".intval($this->virtualno)."][block number : ".$number."][mapped numbers: ".implode(",",$this->finalmappednumbers)."]";
					$this->logmsgvirtualno("After block particular number contract still have mapped number in contract so update mapped number in VN tabel or server",$this->log_path,$this->processName,$this->parentid,$extra_str);
					$success_vn = $this->updateMapping();
					$this->updateCompanymaster($this->virtualno);
					$this->updateCompanyMasterSearch();
					$this->DialableNumberClass->upDateDialableNumber($this->parentid,$this->conn_iro);
				}
				else
				{
					$extra_str="[reason: after remove block number, not single number is present which is use as mapped number ][block number : ".$number."][virtual no :".$this->virtualno."]";
					$this->logmsgvirtualno("Contract is not eligible.",$this->log_path,$this->processName,$this->parentid,$extra_str);
					
					$this->quarantineFlag = $this->checkQuarantineVn();
					
					$extra_str="[virtual number not in quarantine table][block number : ".$number."][virtual no :".$this->virtualno."]";
					$this->logmsgvirtualno("Checking quarantine.",$this->log_path,$this->processName,$this->parentid,$extra_str);
					
					if(!$this->quarantineFlag && intval($this->virtualno)>0)
					{
						$reason = "after remove block number, not single number is present which is use as mapped number";
						$this->QuarantineNo($reason);
					}
					$this->updateCompanymaster();
					$this->updateCompanyMasterSearch();
					$this->DialableNumberClass->upDateDialableNumber($this->parentid,$this->conn_iro);
					if($this->JDFOS_flag)
					{
						$returnFlag = $this->updateJdfosDb();
					}
				}
			}
		}
		else
		{
			$extra_str="[virtual number :".intval($this->virtualno)."][block number : ".$number."]";
			$this->logmsgvirtualno("Get virtual number in block number process, virtual number is not present in contract",$this->log_path,$this->processName,$this->parentid,$extra_str);
		}
	}
	
	function getBlockforVn($pid='')
	{
		$flag = false;
		if(trim($pid)!='')
		{
			$qPid  = $pid;
		}
		else
		{
			$qPid  = $this->parentid;
		}
		$temparr	= array();
		$fieldstr	= "blockforvirtual";
		$where 		= "parentid ='".$qPid."' and blockforvirtual=1";
		$resGetBlockVn	= $this->compmaster_obj->getRow($fieldstr,"tbl_companymaster_generalinfo",$where);

		if($resGetBlockVn['numrows'])
		{
			$flag = true;
		}
		return $flag;
	}
	
	function sendMail($subject, $msg)
	{
		if(APP_LIVE == 1)
		{
			$tech_emailids = array('shakirshaikh@justdial.com','chandrashekhar.koralli@justdial.com','processissues@justdial.com');
			//$tech_emailids = array('yogitatandel@justdial.com');
			$headers = "From: noreply@justdial.com\r\n";
			$headers .= "Content-type: text/html\r\n"; 
			if(!is_array($emails))
			{
				$emails = array(trim($emails));
			}
			foreach($tech_emailids as $value)
			{
				//mail($value, $subject, $msg, $headers);
			}
		}
	}
    
    function insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name)
    {
        if($this->direct_update)
        {
			$qry_delete_tbltechinfofailed_process = "delete from tbl_techinfo_failed_process_log where parentid ='".trim($this->parentid)."' and process_flag=1";
			$res_delete_tbltechinfofailed_process = $this->techinfo_conn->query_sql($qry_delete_tbltechinfofailed_process);
            
            $qry_insert_tbltechinfofailed_process = "INSERT INTO tbl_techinfo_failed_process_log SET vno=" . (int)$this->virtualno . ", parentid ='".trim($this->parentid)."',process_flag='".$logtbl_process_flag."',update_date='".date('Y-m-d H:i:s')."', reason='".$logtbl_reason."',processname='".$process_name."'";
            $res_insert_tbltechinfofailed_process = $this->techinfo_conn->query_sql($qry_insert_tbltechinfofailed_process);        
        }
        else
        {
			$qry_delete_tbltechinfofailed_process = "delete from tbl_techinfo_failed_process_log where parentid ='".trim($this->parentid)."' and process_flag=1";
			$res_delete_tbltechinfofailed_process = $this->conn_decs->query_sql($qry_delete_tbltechinfofailed_process);
			
            $qry_insert_tbltechinfofailed_process = "INSERT INTO tbl_techinfo_failed_process_log SET vno=" . (int)$this->virtualno . ", parentid ='".trim($this->parentid)."',process_flag='".$logtbl_process_flag."',update_date='".date('Y-m-d H:i:s')."', reason='".$logtbl_reason."',processname='".$process_name."'";
            $res_insert_tbltechinfofailed_process = $this->conn_decs->query_sql($qry_insert_tbltechinfofailed_process);
        }
        $extra_str="[Qry run : ".$qry_insert_tbltechinfofailed_process."][Qry result : ".$res_insert_tbltechinfofailed_process."][Process flag : ".$logtbl_process_flag."][log table reason : ".$logtbl_reason."][Process name : ".$process_name."]";
        $this->logmsgvirtualno("Insert into log Table if process is failed.",$this->log_path,$this->processName,$this->parentid,$extra_str);
		$parentId =$this->parentid;
		$message = "<br>ParentId = " . $parentId . "<br>Process Name = ".$process_name."<br>Reason = " . $logtbl_reason . "<br>URL = " . $this->techinfo_url . "<br>City = ". $this->city_vn;
		$subject = "RCOM ALERT: ".$process_name." ".date('d-m-Y H:i:s')." ". $this->city_vn;
		$this->sendMail($subject, $message);
    }
    
    function logmsgvirtualno($sMsg, $sNamePrefix,$process,$contractid,$extra_str='')
    {
		if(!defined('REMOTE_CITY_MODULE') || $this->usercode == '10026632' || $this->usercode == '009882')
		{
		$baseDir = $this->log_path;
		$year = date("Y");   
		$month = date("m");
		$day = date("d");
		$diryr = $baseDir. "/". $year;
		$dirmnth = $diryr. "/". $month;
		$dirday = $dirmnth. "/". $day;
		if(trim($this->log_path)!=''){
			$directodyArr = explode("/",$this->log_path);
			$directodyArr = array_merge(array_filter($directodyArr));
			$filePath = '';
			foreach($directodyArr as $key => $directoryName){
				if($filePath!=''){
					$filePath .= "/".$directoryName;
				}else{
					$filePath = APP_PATH.$directoryName;
				}
				if(!is_dir($filePath)){
					//mkdir($filePath, 0755);
					mkdir($filePath, 0777);
				}
			}
		}
		if(!is_dir($diryr))
		{
			 //mkdir($baseDir."/". $year, 0755);						 
			 mkdir($baseDir."/". $year, 0777);						 
		}

		if(!is_dir($dirmnth))
		{
			 //mkdir($diryr."/". $month, 0755);						 
			 mkdir($diryr."/". $month, 0777);						 
		}
		if(!is_dir($dirday))
		{
			 //mkdir($dirmnth."/". $day, 0755);						 
			 mkdir($dirmnth."/". $day, 0777);						 
		}
		$basefile = basename('logs/virtualNoLogs/');
		$FinalPath["result"]["path"] = explode(",",$dirday);
        $log_msg='';
        
        $sNamePrefix = $FinalPath["result"]["path"][0]."/".$contractid.'.html';
        // fetch directory for the file
        $pathToLog = dirname($sNamePrefix); 
        if (!file_exists($pathToLog)) {
            //mkdir($pathToLog, 0755, true);
            mkdir($pathToLog, 0777, true);
        }
        /*$file_n=$sNamePrefix.$contractid.".txt"; */
       // $file_n=$sNamePrefix.$contractid.".html";
		$file_n=$sNamePrefix;
        // Set this to whatever location the log file should reside at.
        $logFile = fopen($file_n, 'a+');

        // Change this to point to the User ID variable in session.
        if (isset($this->usercode) || isset($_SESSION['mktgEmpCode'])) {
            $userID = isset($this->usercode) ? $this->usercode : $_SESSION['mktgEmpCode']; //  Switches between TME_Live Session ID and DATAENTRY Session ID
        } else {
            $userID = 'unknown'; // stands for "default"  or "unknown"
        }
        /*$log_msg.=  "Parentid:-".$contractid."\n [$sMsg] \n ".$extra_str." [user id: $userID] [Action: $process] [Date : ".date('Y-m-d H:i:s')."]";*/
        $pageName 		= wordwrap($_SERVER['PHP_SELF'],22,"\n",true);
        $log_msg.= "<table border=0 cellpadding='0' cellspacing='0' width='100%'>
                        <tr valign='top'>
                            <td style='width:10%; border:1px solid #669966'>Date :".date('Y-m-d H:i:s')."</td>
                            <td style='width:10%; border:1px solid #669966'>File name:".$pageName."</td>
                            <td style='width:30%; border:1px solid #669966'>Message:".$sMsg."</td>
                            <td style='width:30%; border:1px solid #669966'>Extra Message: ".$extra_str."</td>
                            <td style='width:10%; border:1px solid #669966'>User Id :".$userID."</td>
                            <td style='width:10%; border:1px solid #669966'>Action :".$process."</td>
                        </tr>
                    </table>";
        fwrite($logFile, $log_msg);
        fclose($logFile);
		}
    }
}
?>
