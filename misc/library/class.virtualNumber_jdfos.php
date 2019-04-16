<?php
include_once(APP_PATH."library/define_virtualnumbers.php");
include_once(APP_PATH."common/virtual_api.php");
include_once(APP_PATH."library/common_api.php");
include_once(APP_PATH."business/class.restaurant.php");
class Virtualnumber
{
	const TOTAL_REQUIRE_MAPPEDNO = 8; /* total mapped numbers tech info can store */
    const TOTAL_TECHINFO_MAPPENO = 8; /* total mapped numbers return form techinfo virtual for perticular virtual number*/
    const CURL_TIMEOUT = 5;
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
        $this->log_path			= APP_PATH . 'logs/virtualNoLogs/';
        $this->dndobj			= $dndobj;
        $this->city_vn          = $city_vn;
        $this->processName		= $processName;
        
        $this->conn_iro         = new DB($dbarr['DB_IRO']);
        $this->conn_finance     = new DB($dbarr['FINANCE']);
        $this->conn_decs     	= new DB($dbarr['DB_DECS']);
        $this->conn_ecsbill     = new DB($dbarr['ECS_BILL']);
        $this->techinfo_conn    = null;
        $this->usercode               = $usercode;
        $this->main_city_arr = array('MUMBAI','KOLKATA','BANGALORE','CHENNAI','PUNE','HYDERABAD','AHMEDABAD', 'DELHI');
		$this->remote_city_arr = array('JAIPUR','CHANDIGARH','COIMBATORE');
		if(in_array(strtoupper($this->city_vn),$this->remote_city_arr))
		{
			if(APP_LIVE == 1)
			{
				$port = '3306';
				$socket = fSockOpen(constant(strtoupper($this->city_vn).'_MAP_DETAILS_DB_IP'), $port, $errorNo, $errorStr, 3);
			}
			else
			{
				$port = '3306';
				$socket = fSockOpen('192.168.6.96', $port, $errorNo, $errorStr, 3);
			}
		
			if ($socket)
            {
				$this->updateMapdetailconnect = true;
			}
			else
			{
				$this->updateMapdetailconnect = false;
			}
		}
		$this->initialized($parentid,$dbarr,$dndobj='null');
        $this->initializedtechinfo();
        $this->initializedJdfosurl();
        $this->insertion_time = date("Y-m-d H:i:s");
        $this->obj_rest		=	new restaurant($parentid);
	}
	
	function initialized($parentid)
    {
        $this->parentid                 =   $parentid;
        $this->paid_flag                = -1;
        $this->pri_number               = 0;
        $this->jdfos_url ='';
        $this->virtualno                = 0;
        $this->vnEligibleCompaign		= false;
        $this->JDFOS_flag				= false;
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
        $this->contact_arr              = array();
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
		$this->techinfoPidStatus		= bindec(00);
		$this->obj_rest		=	new restaurant($parentid);
	}
	
	function initializedtechinfo()
    {
		if(APP_LIVE == 1)
		{
			//for live 
			if(VN_TECHINFO_SERVER !='')
			{
				if(in_array(strtoupper($this->city_vn),$this->main_city_arr))
				{
					if(strtoupper($this->city_vn)=='DELHI')
					{
						$var = '';
						$port = '81';
						//$socket = fSockOpen(DELHI_STANDBY_TECH_DB_IP, $port, $errorNo, $errorStr, 3);
						$socket = fSockOpen(DELHI_TECH_DB_IP, $port, $errorNo, $errorStr, 3);
						if($socket)
						{
							//$var = DELHI_STANDBY_TECH_DB_IP;
							$var= DELHI_TECH_DB_IP;
						}
						else
						{
							//$socket = fSockOpen(DELHI_TECH_DB_IP, $port, $errorNo, $errorStr, 3);
							$socket = fSockOpen(DELHI_STANDBY_TECH_DB_IP, $port, $errorNo, $errorStr, 3);
							if ($socket)
							{
								//$var= DELHI_TECH_DB_IP;
								$var = DELHI_STANDBY_TECH_DB_IP;
							}
						}
						if($var)
						{
							define("DELHI_TECHINFO_SERVER",$var);
							$this->techinfo_url="http://".DELHI_TECHINFO_SERVER.":81/justdial/";
							$extra_str="Servere IP : ".DELHI_TECHINFO_SERVER;
							$this->logmsgvirtualno("Server IP has been read from text file",$this->log_path,$this->processName,$this->parentid,$extra_str);
						}
						else
						{
							$this->techinfo_url="http://".DELHI_TECH_API_URL."/justdial/";
							$extra_str="Servere IP : ".DELHI_TECH_API_URL;
							$this->logmsgvirtualno("Server IP has been read from text file",$this->log_path,$this->processName,$this->parentid,$extra_str);
						}
						
					}
					else
					{
						$this->techinfo_url="http://".VN_TECHINFO_SERVER.":81/justdial/";
						$extra_str="Servere IP : ".VN_TECHINFO_SERVER;
						$this->logmsgvirtualno("Server IP has been read from text file",$this->log_path,$this->processName,$this->parentid,$extra_str);
					}

				}
				else if(in_array(strtoupper($this->city_vn),$this->remote_city_arr))
				{
					$this->techinfo_url ="";	
				}

			}
			else
			{
				if(in_array(strtoupper($this->city_vn),$this->main_city_arr))
				{
					$this->techinfo_url="http://".constant(strtoupper($this->city_vn ).'_TECH_API_URL')."/justdial/";
					$extra_str="Default Server IP";
					$this->logmsgvirtualno("Server IP has been considered by default.",$this->log_path,$this->processName,$this->parentid,$extra_str);
				}
				else if(in_array(strtoupper($this->city_vn),$this->remote_city_arr))
				{
					$this->techinfo_url ="";	
				}
			}
		}
		else
		{
			// for development
			if(in_array(strtoupper($this->city_vn),$this->main_city_arr))
			{
				$this->techinfo_url="http://techinfo.jdsoftware.com/justdial/"; 
			}
			else if(in_array(strtoupper($this->city_vn),$this->remote_city_arr))
			{
				$this->techinfo_url ="";	
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
	
	function genio_update_virtual_number($parentid,$jdfocFlag='',$expired_display_flag='')
    {
        $process_flag = false;
        $this->initialized($parentid);
        $this->initializedtechinfo(); 
        $this->initializedJdfosurl();
        $this->getinitailvalue();
        $this->getLinkedContracts();
        if($this->linkcontract_flag)
        {
            $this->sort_link_contract();
        }
        $this->paid_flag = $this->checkPaid();
        
        if($jdfocFlag!='' && $jdfocFlag==1)
        {
			$this->JDFOS_flag = $jdfocFlag;
		}
        
        if($this->paid_flag ==1 || $jdfocFlag ==1 || $this->JDFOS_flag)
        { 
			$this->vnEligibleCompaign = $this->checkVnEligibleCampaign();
			if($this->vnEligibleCompaign || $jdfocFlag ==1)
			{
				$this->pincode_flag = $this->checkPincodeFlag();
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
										$extra_str="[virtual number :".intval($this->virtualno)."][Quarantine Flag : ".$this->quarantineFlag."][Function return value :".$rvQrFg."]";
										$this->logmsgvirtualno("Contract Virtual numebr is quarantine, but all condition satify,so remove from quarantine Table",$this->log_path,$this->processName,$this->parentid,$extra_str);
									}
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
								if($this->quarantineFlag_expire && $this->approval_flag)
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
									$this->logmsgvirtualno("Contract Virtual numebr is not quarantine, still update mapped number in Techinfo server",$this->log_path,$this->processName,$this->parentid,$extra_str);
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
							$this->quarantineFlag = $this->checkQuarantineVn();
							if(!$this->quarantineFlag)
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
						$this->mappednumber_flag = $this->getMappednumber();
						$this->blockVn_flag = $this->getBlockforVn();
						if($this->mappednumber_flag && intval($this->blockVn_flag)==0)
						{
							$success_vn = $this->freshAllocation();
							if(intval($success_vn)>0)
							{
								$this->updateCompanymaster($this->virtualno);
								if($this->JDFOS_flag)
								{
									$returnFlag = $this->updateJdfosDb($this->virtualno);
								}
								//$this->updateCompanymaster();
							}
						}
						elseif($this->mappednumber_flag && intval($this->blockVn_flag)==1)
						{
							$this->reason = 'mapped number  exist in the contract but contract is block for virtual number';
							$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."][pincode : ".$this->pincode."][block for Vn flag : ".intval($this->blockVn_flag)."]";
							$this->logmsgvirtualno("Contract is not eligible as well as it does not have virtual number",$this->log_path,$this->processName,$this->parentid,$extra_str);

						}
						else
						{
							$this->reason = 'mapped number not exist in the contract or in any link contracts';
							$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."][pincode : ".$this->pincode."]";
							$this->logmsgvirtualno("Contract is not eligible as well as it does not have virtual number",$this->log_path,$this->processName,$this->parentid,$extra_str);
						}
					}
				}
				else
				{
					$this->quarantineFlag = $this->checkQuarantineVn();
					$this->reason = 'pincode out of city';
					$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."][pincode : ".$this->pincode."][Quarantine Flag : ".$this->quarantineFlag."]";
					$this->logmsgvirtualno("Contract is not eligible as well as it does not have virtual number",$this->log_path,$this->processName,$this->parentid,$extra_str);
					if(!$this->quarantineFlag)
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
					$this->deallocate_vn();
				}
				else
				{
					$this->reason = 'not a virtual number eligible campaign';
					$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."]";
					$this->logmsgvirtualno("Contract is not eligible as well as it does not have virtual number",$this->log_path,$this->processName,$this->parentid,$extra_str);
				}
			}
		}
		else
		{
			if(intval($this->virtualno)>0)
			{
				$this->deallocate_vn();
			}
			else
			{
				$this->reason = 'nonpaid';
				$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."]";
				$this->logmsgvirtualno("Contract is not eligible as well as it does not have virtual number",$this->log_path,$this->processName,$this->parentid,$extra_str);
			}
		}
		if(intval($this->virtualno)>0)
		{
			return 'called';
		}
		else
		{
			return 'failed';
		}
	}
	function chk_eligible_campaign_balance()
	{
		$expired_approval_flag = true;
		$sqlContractBalance = "SELECT parentid FROM tbl_companymaster_finance WHERE parentid = '".$this->parentid."' AND campaignid IN(1,2,23) AND balance>0";
		$resContractBalance = $this->execute_query($sqlContractBalance,$this->conn_finance);
		if($resContractBalance && mysql_num_rows($resContractBalance)<=0)
		{
			$expired_approval_flag = false;
		}
		return $expired_approval_flag;
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
				$curl_url = $this->techinfo_url."deallocate.php?VN=".trim($this->virtualno)."&User=".$this->usercode;
				$curl_res = $this->runCurlUrl($curl_url);
				if(!$this->curl_responce_flag)
				{
					$extra_str="[Techinfo url not working for deallocation][url :".$curl_url."]";
					$this->logmsgvirtualno("Deallocating virtual number in techinfo",$this->log_path,$this->processName,$this->parentid,$extra_str);
				}
				else
				{
					$this->reason = 'Virtual Number Deallocated';
					$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."]";
					$this->logmsgvirtualno("Ineligible Contract Found.",$this->log_path,$this->processName,$this->parentid,$extra_str);
				}
			}
			$updtCompMaster_VN = "UPDATE tbl_companymaster_generalinfo  SET virtualNumber = '',virtual_mapped_number = '',pri_number = '' WHERE parentid = '".$this->parentid."'";
			$resCompMaster_VN = $this->execute_query($updtCompMaster_VN,$this->conn_iro);
		}
		else if(in_array(strtoupper($this->city_vn),$this->remote_city_arr))
		{
			if(!$this->linkcontract_flag && !$this->quarantineFlag)
			{
				$sqlDeallocateVNRemote = "UPDATE d_jds.tbl_virtual_num_mapping_master SET mappedNo = null, contractid = '', curr_time = '0000-00-00 00:00:00', free_flag = 0, approved_flag = 0, VrnPaidContract = null WHERE virtualNo = '".$this->virtualno."' AND city = '".$this->city_vn."'";
				$resDeallocateVNRemote = $this->execute_query($sqlDeallocateVNRemote,$this->conn_decs);
			}
			$updtCompMaster_VN = "UPDATE tbl_companymaster_generalinfo  SET virtualNumber = '',virtual_mapped_number = '',pri_number = '' WHERE parentid = '".$this->parentid."'";
			$resCompMaster_VN = $this->execute_query($updtCompMaster_VN,$this->conn_iro);
			
			$sqlDeleteMapdetails = "DELETE FROM mapdetails WHERE LContract = '".$this->parentid."'";
			$resDeleteMapDetails = $this->execute_query($sqlDeleteMapdetails,$this->mapdetails_conn);
			
			$this->reason = 'Virtual Number Deallocated';
			$extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][virtual number :".intval($this->virtualno)."]";
			$this->logmsgvirtualno("Ineligible Contract Found.",$this->log_path,$this->processName,$this->parentid,$extra_str);
		}
	}
	
	function getinitailvalue()
    {
        $qry_initialvalue_contract = "SELECT pincode, virtualnumber FROM tbl_companymaster_generalinfo WHERE parentid = '".$this->parentid."'";
        $res_initialvalue_contract = $this->execute_query($qry_initialvalue_contract,$this->conn_iro);
        if(!$res_initialvalue_contract)
        {
            die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_initialvalue_contract);
            return false;
        }
        else
        {
            if(mysql_num_rows($res_initialvalue_contract)>0)
            {
                $res_initialvalue_contract =  mysql_fetch_assoc($res_initialvalue_contract);
                $this->virtualno =$res_initialvalue_contract['virtualnumber'];
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
        $res_get_linked_contract_flag   =   $this->execute_query($qry_get_linked_contract_flag,$this->conn_decs);
        if(!$res_get_linked_contract_flag)
        {
            die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_get_linked_contract_flag);
            return false;        
        }
        else
        {
            if(mysql_num_rows($res_get_linked_contract_flag)>0)
            {
                if($owned_parentid && !$this->linkcontract_flag)
                {
                    $this->linkcontract_flag = true;
                }
                while($row_get_linked_contract_flag = mysql_fetch_array($res_get_linked_contract_flag))
                {
					$added = false;
					if(!in_array($row_get_linked_contract_flag['parentid'], $this->linkcontracts))
                    {
						$this->linkcontracts[] = $row_get_linked_contract_flag['parentid'];
						$qry_getvirtualnumber = "SELECT virtualnumber FROM tbl_companymaster_generalinfo WHERE parentid = '".$row_get_linked_contract_flag['parentid']."'";
                        //$res_getvirtualnumber = $this->conn_iro->query_sql($qry_getvirtualnumber);
                        $res_getvirtualnumber = $this->execute_query($qry_getvirtualnumber,$this->conn_iro);
                        $vrno = 0;
                        if(!$res_getvirtualnumber)
                        {
                            die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_getvirtualnumber);
                            return false;                      
                        }
                        else
                        {
                            if(mysql_num_rows($res_getvirtualnumber) > 0)
                            {
                                $row_getvirtualnumber = mysql_fetch_array($res_getvirtualnumber);
                                $vrno = intval($row_getvirtualnumber['virtualnumber']);
                            }
                            mysql_free_result($res_getvirtualnumber);
                        }
                        $this->linkcontractsvalues[$row_get_linked_contract_flag['parentid']] = array('vno'=>$vrno, 'tvno'=>0, 'paid'=>-1, 'expired'=>false, 'freeze'=>true, 'sort'=>0, 'hidden'=>false, 'pincode'=>false, 'mappednumber'=>false, 'businessEligibility'=>true, 'nophonesearch'=>false);
                        $added = true;
					}
					if(!in_array($row_get_linked_contract_flag['scheme_parentid'], $this->linkcontracts))
                    {
                        $this->linkcontracts[] = $row_get_linked_contract_flag['scheme_parentid'];
                        $qry_getvirtualnumber = "SELECT virtualnumber FROM tbl_companymaster_generalinfo WHERE parentid = '".$row_get_linked_contract_flag['scheme_parentid']."'";
                        //$res_getvirtualnumber = $this->conn_iro->query_sql($qry_getvirtualnumber);
                        $res_getvirtualnumber = $this->execute_query($qry_getvirtualnumber,$this->conn_iro);
                        $vrno = 0;
                        if(!$res_getvirtualnumber)
                        {
                            die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_getvirtualnumber);
                            return false;                  
                        }
                        else
                        {
                            if(mysql_num_rows($res_getvirtualnumber) > 0)
                            {
                                $row_getvirtualnumber = mysql_fetch_array($res_getvirtualnumber);
                                $vrno = intval($row_getvirtualnumber['virtualnumber']);   
                            }
                            mysql_free_result($res_getvirtualnumber);
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
	
	function sort_link_contract()
    {
        $sorted_linked_contracts =  array();
        if(count($this->linkcontracts)>0)
        {                
            $update_sort_sql =  "SELECT parentid, scheme_parentid FROM tbl_company_refer WHERE parentid IN ('". implode("','", $this->linkcontracts) ."') OR scheme_parentid IN ('" . implode("','", $this->linkcontracts) . "') ORDER BY creationDt";
            //$resupdate_sort_sql    =   $this->conn_decs->query_sql($update_sort_sql);
            $resupdate_sort_sql = $this->execute_query($update_sort_sql,$this->conn_decs);
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
                die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry);
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
            $qry_paid_contract = "SELECT parentid FROM tbl_companymaster_finance WHERE parentid = '".$pid."' LIMIT 1";
            $res_paid_contract = $this->execute_query($qry_paid_contract,$this->conn_finance);
            if(!$res_paid_contract)
            {
                die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_paid_contract);
            }
            else
            {            
                if(mysql_num_rows($res_paid_contract)>0)
                {
                    $is_paid_contract = true;
                }else{
					$this->checkJdfos();
				}
                mysql_free_result($res_paid_contract);
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
		$get_plat_package_budget = "SELECT balance,campaignid,expired FROM tbl_companymaster_finance WHERE parentid='".$pid."' AND campaignid in (1,2,16,23) order by campaignid ";
        
        $res_get_plat_package_budget = $this->execute_query($get_plat_package_budget,$this->conn_finance);
        
        if(!$res_get_plat_package_budget)
        {
            die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $get_plat_package_budget);
            return false; 
        }
        else
        {
			if($res_get_plat_package_budget && mysql_num_rows($res_get_plat_package_budget)>0)
			{
				$is_phonesearch_contract=true;
				while($row_get_plat_package_budget =  mysql_fetch_assoc($res_get_plat_package_budget))
				{
					if($row_get_plat_package_budget['campaignid']==23)
					{
						$this->JDFOS_flag = true;
					}
					else
					{
						$this->checkJdfos();
						if(intval($this->JDFOS_flag)==1)
						{
							$is_phonesearch_contract=true;
						}
					}
				}
			}
			else
			{
				$this->checkJdfos();
				if(intval($this->JDFOS_flag)==1)
				{
					$is_phonesearch_contract=true;
				}
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
		$qry_get_all_contact_number = "SELECT  stdcode, landline_display, mobile_display, tollfree, landline, mobile,virtualNumber,companyname,mobile_feedback,email_feedback,pincode,area FROM tbl_companymaster_generalinfo WHERE parentid='".$pid."'";
        $res_get_all_contact_number = $this->execute_query($qry_get_all_contact_number,$this->conn_iro);
        if($res_get_all_contact_number && mysql_num_rows($res_get_all_contact_number)>0)
        {
            $row_get_all_contact_number = mysql_fetch_assoc($res_get_all_contact_number);
            $contract_stdcode= $row_get_all_contact_number['stdcode'];
            $this->contact_arr['landline_display'] = $row_get_all_contact_number['landline_display'];
            $this->contact_arr['mobile_display']   = $row_get_all_contact_number['mobile_display'];
            $this->contact_arr['tollfree']         = $row_get_all_contact_number['tollfree'];
            $this->contact_arr['landline']         = $row_get_all_contact_number['landline'];
            $this->contact_arr['mobile']           = $row_get_all_contact_number['mobile'];
            //$this->virtualno            = $row_get_all_contact_number['virtualNumber'];
            $this->companyname          = $row_get_all_contact_number['companyname'];
            $this->mobile_feedback      = $row_get_all_contact_number['mobile_feedback'];
            $this->email_feedback       = $row_get_all_contact_number['email_feedback'];
            $this->stdcode              = $row_get_all_contact_number['stdcode'];
            $this->pincode              = $row_get_all_contact_number['pincode'];
            $this->area                 = $row_get_all_contact_number['area'];
            if(trim($this->stdcode)=='' || intval(trim($this->stdcode))=='0')
            {
                $this->stdcode = $this->getStdcode();
            }
            $this->getTopMappednumberWithoutstd();
            $this->getTopMappednumber();
        }
        mysql_free_result($res_get_all_contact_number);
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
			}
		}
		$this->top_mappednumber_withoutstd= array_merge($this->landline,$this->mobile,$this->tollfree);
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
	
	function getTopMappednumber()
	{
		foreach ($this->contacts_mode as $contact_mode)
		{
			switch($contact_mode)
			{
				case 'landline_display':
								$this->landline=  $this->convert_contactnos_arr($this->contact_arr[$contact_mode]); 
								$landline_cnt = count($this->landline);
								if($landline_cnt>0)
								{
									$this->landline = $this->concat_stdcode_zero($this->landline);
								}
								break;
				case 'mobile_display'  :
								$this->mobile =$this->convert_contactnos_arr($this->contact_arr[$contact_mode]);
								$mobile_cnt = count($this->mobile);
								if($mobile_cnt>0)
								{
									$this->mobile = $this->concat_stdcode_zero($this->mobile);
								}
								/*$this->mobile=$this->get_not_dnc_mobile($this->mobile);*//*remove this because allow DNC number as virtual mapeed number*/
								break;
				case 'tollfree' :
								$this->tollfree   =   $this->convert_contactnos_arr($this->contact_arr[$contact_mode]);
								$tollfree_cnt = count($this->tollfree);
								break;
			}
		}
		$req_landline_cnt = 4;
        $req_mobile_cnt = 4;
        if($landline_cnt<$req_landline_cnt || $mobile_cnt<$req_mobile_cnt)
        {
            if($mobile_cnt>=$req_mobile_cnt && $landline_cnt<$req_landline_cnt)
            {
                $req_mobile_cnt += ($req_landline_cnt - $landline_cnt);
                $req_landline_cnt = $landline_cnt;
                if(($req_landline_cnt+$req_mobile_cnt)>self::TOTAL_REQUIRE_MAPPEDNO)
                {
                    $req_mobile_cnt = self::TOTAL_REQUIRE_MAPPEDNO - $req_landline_cnt;

                }
                if($req_mobile_cnt>$mobile_cnt)
                {
                    $req_mobile_cnt=$mobile_cnt;
                }
            }
            elseif($mobile_cnt<$req_mobile_cnt && $landline_cnt>=$req_landline_cnt)
            {
                $req_landline_cnt += ($req_mobile_cnt - $mobile_cnt);
                $req_mobile_cnt = $mobile_cnt;
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
                $req_mobile_cnt = $mobile_cnt;
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
            $req_mobile_no = array_slice($this->mobile,0,$req_mobile_cnt);
        }
        else
        {
            $req_mobile_no = array();
        }
        $top_eight_array = array_merge($req_lanline_no,$req_mobile_no);        
        
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
                    $contact = $this->stdcode.ltrim($contact, '0');
                }
                if(trim(trim(trim($contact), '0'))!='')
                {
                    $contact = '0'.ltrim($contact, '0');
                }
                else
                {
                    $contact = trim(trim(trim($contact), '0'));
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
		$QryGetLinkInfo = "SELECT parentid,landline_display,mobile_display,tollfree,pincode,area,stdcode FROM tbl_companymaster_generalinfo WHERE parentid IN ('".implode("','",$this->linkcontracts)."') and parentid !='".$this->parentid."' and virtualnumber>0";
		$ResGetLinkInfo = $this->execute_query($QryGetLinkInfo,$this->conn_iro);
		if(!$ResGetLinkInfo)
		{
			die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $QryGetLinkInfo);
		}
		else
		{
			if(mysql_num_rows($ResGetLinkInfo)>0)
			{
				while($RowGetLinkInfo = mysql_fetch_assoc($ResGetLinkInfo))
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
				mysql_free_result($ResGetLinkInfo);
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
			$curl_url = $this->techinfo_url."allocate.php?";
			$urlEnd = $this->makeTechinfoUrl($businessid);
			$curl_url = $curl_url.$urlEnd;
			$curl_outout = $this->runCurlUrl($curl_url);
			if(!$this->curl_responce_flag)
			{
				$logtbl_process_flag=1;
				$logtbl_reason = "techinfo url not working";
				$process_name = "allocate new virtual number contract not having virtual number";
				$this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
				$extra_str="[techinfo url not working][url :".$curl_url."]";
				$this->logmsgvirtualno("allocate new virtual number contract not having virtual number",$this->log_path,$this->processName,$this->parentid,$extra_str);
			}
			else
			{
				$tectinfo_vrn = $this->readTechinfoXml($curl_outout);
				$this->virtualno = $tectinfo_vrn;
			}
		}
		else if(in_array(strtoupper($this->city_vn),$this->remote_city_arr))
		{
			$this->techInfoPaidStatus();
			$this->virtualno = $this->remoteAllocateVirtualDb();
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
		$this->logmsgvirtualno("DFOC url responce",$this->log_path,$this->processName,$this->parentid,$extra_str);
		/*$jdfocCurlurl =  $this->jdfos_url."restDet/".$this->parentid;
		$jdfocCurlurl_output = $this->runCurlUrl($jdfocCurlurl);
		if(!$this->curl_responce_flag)
		{
			$message = "<br>ParentId = " . $this->parentid . "<br>Process Name = ".$this->processName."<br>Reason = JDFOS fetch url not wokring<br>Virtual number : " . $this->virtualno . "<br>City = ". $this->city_vn."<br>Virtual NO : ".$jdfosVno."<br>Url : ".$jdfocCurlurl;
			$subject = $this->city_vn ." - JDFOS fetch url not working";
			$this->sendMail($subject, $message);
		}
		else
		{
			$jdfos_json = json_decode($jdfocCurlurl_output,true);
			if($jdfos_json['results']['gen_info']['0']['parentid'] == $this->parentid)
			{
				$jdfos_json['results']['gen_info'][0]['virtualNumber'] = $jdfosVno;
				if(count($jdfos_json)>0)
				{
					$jdfocUpdateCurl = $this->jdfos_url."insRestDet/".$this->parentid;
					$jdfocUpdateCurl_ouput = $this->runPostCurlUrl($jdfocUpdateCurl,$jdfos_json);
					if(!$this->curl_postresponce_flag)
					{
						$message = "<br>ParentId = " . $this->parentid . "<br>Process Name = ".$this->processName."<br>Reason = JDFOS update url not wokring<br>Virtual number : " . $this->virtualno . "<br>City = ". $this->city_vn."<br>Virtual NO : ".$jdfosVno."<br>Url :".$jdfocUpdateCurl;
						$subject = $this->city_vn ." - JDFOS fetch url not working";
						$this->sendMail($subject, $message);
					}
					else
					{
						$out = json_decode($jdfocUpdateCurl_ouput,true);
						if($out['results']['errorCode']==0)
						{
							$jdfocUpdateflag = true;
							$extra_str="[Responce Code :".$out['results']['errorCode']."][virtual numebr :".$jdfosVno."][url update flag :".$jdfocUpdateflag."]";
							$this->logmsgvirtualno("JDFOC url responce",$this->log_path,$this->processName,$this->parentid,$extra_str);
						}
						else
						{
							$jdfocUpdateflag = false;
							$extra_str="[Responce Code :".$out['results']['errorCode']."][virtual number : ".$jdfosVno."][url update flag :".$jdfocUpdateflag."]";
							$this->logmsgvirtualno("JDFOC url responce",$this->log_path,$this->processName,$this->parentid,$extra_str);
						}
					}
				}
			}
			else
			{
				$extra_str="json array is blank";
				$this->logmsgvirtualno("parentid not present in restaurant server",$this->log_path,$this->processName,$this->parentid,$extra_str);
			}
		}*/
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
					$curl_url = $this->techinfo_url."allocate.php?VN=".trim($this->virtualno)."&";
					$urlEnd = $this->makeTechinfoUrl(trim($this->parentid));
					$curl_url = $curl_url.$urlEnd;
					$curl_outout = $this->runCurlUrl($curl_url,$url_type); 
					if(!$this->curl_responce_flag)
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
						$tectinfo_vrn = $this->readTechinfoXml($curl_outout);
						$this->virtualno = $tectinfo_vrn; 
					}
				}
				else
				{
					$extra_str="[techinfo businessid : ".$this->techInfo_array['BusinessId']."][given parentid :".$this->parentid."]";
					$this->logmsgvirtualno("Techinfo business id is not same as given parentid",$this->log_path,$this->processName,$this->parentid,$extra_str);
					if($this->linkcontract_flag)
					{
						if(in_array(trim($this->techInfo_array['BusinessId']),$this->linkcontracts))
						{
							$extra_str="[techinfo businessid : ".$this->techInfo_array['BusinessId']."][linkcontract array :".implode(",",$this->linkcontracts)."]";
							$this->logmsgvirtualno("Techinfo businessid is present in linkcontract array",$this->log_path,$this->processName,$this->parentid,$extra_str);
							$this->status='A';
							$curl_url = $this->techinfo_url."allocate.php?VN=".trim($this->virtualno)."&";
							$urlEnd = $this->makeTechinfoUrl(trim($this->techInfo_array['BusinessId']));
							$curl_url = $curl_url.$urlEnd;
							$curl_outout = $this->runCurlUrl($curl_url,$url_type); 
							if(!$this->curl_responce_flag)
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
								$tectinfo_vrn = $this->readTechinfoXml($curl_outout);
								$this->virtualno = $tectinfo_vrn; 
							}
						}
						else
						{
							$message = "<br>ParentId = " . $this->parentid . "<br>Process Name = ".$this->processName."<br>Reason = Virtual number is differ in companymaster generalinfo and techinfo server.<br>Virtual number : " . $this->virtualno . "<br>City = ". $this->city_vn."<br>Techinfo parentid : ".trim($this->techInfo_array['BusinessId'])."<br> Link contract :Yes";
							$subject = $this->city_vn ." - Virtual number mismatch (".$this->virtualno.")";
							$this->sendMail($subject, $message);
						}
					}
					else
					{
						$message = "<br>ParentId = " . $this->parentid . "<br>Process Name = ".$this->processName."<br>Reason = Virtual number is differ in companymaster generalinfo and techinfo server.<br>Virtual number : " . $this->virtualno . "<br>City = ". $this->city_vn."<br>Techinfo parentid : ".trim($this->techInfo_array['BusinessId'])."<br> Link contract :No";
						$subject = $this->city_vn ." - Virtual number mismatch (".$this->virtualno.")";
						$this->sendMail($subject, $message);
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
				if(trim($this->remoteVnDbArr['contractid'])==trim($this->parentid) && $this->remoteVnDbArr['contractid']!='')
				{
					$this->status='A';
					$DbVN = $this->remoteAllocateVirtualDb($this->virtualno);
					$this->virtualno = $DbVN;
				}
				else
				{
					$extra_str="[database contractid : ".$this->remoteVnDbArr['contractid']."][given parentid :".$this->parentid."]";
					$this->logmsgvirtualno("Database contractid id is not same as given parentid",$this->log_path,$this->processName,$this->parentid,$extra_str);
					if($this->linkcontract_flag)
					{
						if(in_array(trim($this->remoteVnDbArr['contractid']),$this->linkcontracts))
						{
							$extra_str="[database contractid : ".$this->remoteVnDbArr['contractid']."][linkcontract array :".implode(",",$this->linkcontracts)."]";
							$this->logmsgvirtualno("Database contractid is present in linkcontract array",$this->log_path,$this->processName,$this->parentid,$extra_str);
							$this->status='A';
							$DbVN = $this->remoteAllocateVirtualDb($this->virtualno);
							$this->virtualno = $DbVN;
						}
						else
						{
							$message = "<br>ParentId = " . $this->parentid . "<br>Process Name = ".$this->processName."<br>Reason = Virtual number is differ in companymaster generalinfo and virtual number mapping master table.<br>Virtual number : " . $this->virtualno . "<br>City = ". $this->city_vn."<br>Techinfo parentid : ".trim($this->remoteVnDbArr['contractid'])."<br> Link contract :Yes";
							$subject = $this->city_vn ." - Virtual number mismatch (".$this->virtualno.")";
							$this->sendMail($subject, $message);
						}
					}
					else
					{
						$message = "<br>ParentId = " . $this->parentid . "<br>Process Name = ".$this->processName."<br>Reason = Virtual number is differ in companymaster generalinfo and virtual number mapping master table.<br>Virtual number : " . $this->virtualno . "<br>City = ". $this->city_vn."<br>Techinfo parentid : ".trim($this->remoteVnDbArr['contractid'])."<br> Link contract :Yes";
						$subject = $this->city_vn ." - Virtual number mismatch (".$this->virtualno.")";
						$this->sendMail($subject, $message);
					}
				}
			}
		}
		return $this->virtualno;
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
		$addition_info ="User=".trim($this->usercode)."&BusinessId=".trim($pid)."&Email=".trim($fbemail)."&Mobile=".$fb_mobile."&Contract=".trim($this->techinfoPidStatus)."&BusinessName=".urlencode(trim($this->companyname));
		$curlUrlEnd = $landline_str.$addition_info;
		
		return $curlUrlEnd;
	}
	
	function runCurlUrl($curl_url )
    {
        $this->curl_responce_flag = false;
        $ch = curl_init();
        $ans = curl_setopt($ch, CURLOPT_URL,$curl_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, self::CURL_TIMEOUT );
        curl_setopt( $ch, CURLOPT_TIMEOUT, self::CURL_TIMEOUT );
        $output =curl_exec($ch);
        if($output!=false)
        {
            $this->curl_responce_flag = true;            
        }
        else
        {
            if(curl_errno($ch)==28)
            {
                return -28;
            }
        }
        $extra_str="[Url run :".$curl_url."][url type :".$url_type."][curl response flag : ".$this->curl_responce_flag."][outpout return :".$output."]";
        $this->logmsgvirtualno("Techinfo url .",$this->log_path,$this->processName,$this->parentid,$extra_str);
        return $output;
    }
    
    function runPostCurlUrl($url,$params)
    {
		$this->curl_postresponce_flag = false;//echo "<pre>";print_r($params);die();
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
            $this->curl_postresponce_flag = true;            
        }
        else
        {
            if(curl_errno($ch_int)==28)
            {
                return -28;
            }
        }
		
		$extra_str="[Url run :".$url."][param string : ".$params_string."][curl response flag : ".$this->curl_postresponce_flag."][outpout return :".$result."]";
        $this->logmsgvirtualno("JDFOS update url.",$this->log_path,$this->processName,$this->parentid,$extra_str);
		return $result;
	}
    
    function readTechinfoXml($curl_outout)
    {
		$curl_outout = trim($curl_outout);
        $xmlDoc = new DOMDocument();
        $xmlDoc->loadXML($curl_outout);
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
			$curl_url = $this->techinfo_url."vrnsearch.php?VN=".trim($this->virtualno);
			$curl_outout = $this->runCurlUrl($curl_url);
			if(!$this->curl_responce_flag)
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
				$this->techInfo_array = $this->techinfoArray($curl_outout); 
			}
			return $this->techInfo_array;
		}
		else if(in_array(strtoupper($this->city_vn),$this->remote_city_arr))
		{
			$virtualArr = array();
			$sql_get_vn_from_db = "SELECT * FROM " . DB_JDS_LIVE . ".tbl_virtual_num_mapping_master WHERE virtualNo='".$this->virtualno."' and city='".$this->city_vn."'";
			$res_get_vn_from_db = $this->execute_query($sql_get_vn_from_db,$this->conn_decs);
			if($res_get_vn_from_db && mysql_num_rows($res_get_vn_from_db)>0)
			{		
					$row_vno = mysql_fetch_assoc($res_get_vn_from_db);

					$virtualArr['virtualNo']     = $row_vno['virtualNo'];
					$virtualArr['contractid']    = $row_vno['contractid'];
					$virtualArr['mappedNo']      = $row_vno['mappedNo'];
					$virtualArr['free_flag']     = $row_vno['free_flag'];
					$virtualArr['approved_flag'] = $row_vno['approved_flag'];
			}
			return $virtualArr;
		}
	}
	
	function techinfoArray($curl_outout)
    {
		$curl_outout = trim($curl_outout);
        $curl_outout = str_replace('&nbsp;', '&#160;', $curl_outout);
        $curl_outout = str_replace('Bussiness Id', 'BusinessId', $curl_outout);
        $xmlDoc = new DOMDocument();
        $xmlDoc->loadXML($curl_outout);
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
            $this->techinfo_array['Mobile']= $Mobile_arry[1];
            $this->techinfo_array['Email']= $Email_arry[1];

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
		$qryCheckQuarantineFlag = "SELECT * FROM tbl_quarantine_virtualnumber WHERE businessid = '".trim($quarantine_parentid)."' ".$condition." AND active_flag = 1 AND  hide_flag = 0 AND end_date = '0000-00-00 00:00:00' AND vno!=0 ORDER BY update_date DESC LIMIT 1";
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
		$qryCheckQuarantineFlag = "SELECT * FROM tbl_quarantine_virtualnumber WHERE businessid IN ('".trim($quarantine_parentid)."') ".$condition." AND active_flag = 1 AND  hide_flag = 1 AND end_date = '0000-00-00 00:00:00' AND vno!=0 ORDER BY update_date DESC LIMIT 1";
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
	
    function updateCompanymaster($genioVn='')
    {
		$upVn = '';
		if(intval($genioVn)>0)
		{
			$this->getPriNumber();
			$upVn = "virtualnumber = '".$genioVn."', virtual_mapped_number = '".$this->top_mappednumber_withoutstd[0]."', pri_number='".$this->pri_number."'";
		}
		else
		{
			$upVn = "virtualnumber = '', virtual_mapped_number = '' , pri_number = '' ";
		}
		if(trim($upVn)!='')
		{
			$qryUpdateCompanymasterGen = "update tbl_companymaster_generalinfo set ".$upVn." where parentid in( '".$this->parentid."')";
			$resUpdateCompanymasterGen = $this->execute_query($qryUpdateCompanymasterGen,$this->conn_iro);
			$extra_str="[updated virtual number:".$genioVn."][updated virtual mapped number:".implode(",",$this->top_mappednumber_withoutstd)."][updated parentid:".$parent_str."][update Qry :".$qryUpdateCompanymasterGen."][update qry result: ".$resUpdateCompanymasterGen."]";
			$this->logmsgvirtualno("Update tbl companymaster ganeral info",$this->log_path,$this->processName,$this->parentid,$extra_str);
			$this->updateCompanyMasterSearch();
		}
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
        $qry_sel_comp_srch="SELECT parentid, mobile_display, landline_display,tollfree_display,fax,virtualNumber,tollfree FROM tbl_companymaster_generalinfo WHERE parentid ='".$pid."'";
        $res_sel_comp_srch = $this->execute_query($qry_sel_comp_srch,$this->conn_iro);
        if($res_sel_comp_srch && mysql_num_rows($res_sel_comp_srch)>0)
        {
            while($row_sel_comp_srch = mysql_fetch_assoc($res_sel_comp_srch))
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
                $update_tbl_compsrch = "UPDATE tbl_companymaster_search SET phone_search = '".$new_phone_search."' WHERE parentid = '".$pid."'";
                $res_update_tbl_compsrch = $this->conn_iro->query_sql($update_tbl_compsrch,$this->parentid,true);
            }
        }
        unset($row_sel_comp_srch);
    }
    
    function remoteAllocateVirtualDb($vn='')
    {
		if($vn=='' || $vn==0)
		{
			$virtualNo = 0;
			$sql_get_vn_from_db = "SELECT mm.virtualNo, mm.mappedNo, mm.contractid, mm.free_flag, mm.approved_flag, mm.city FROM " . DB_JDS_LIVE . ".tbl_virtual_num_mapping_master mm LEFT JOIN " . DB_IRO . ".tbl_companymaster_generalinfo cm ON (mm.virtualNo=cm.virtualNumber) WHERE (cm.virtualnumber='' OR cm.virtualnumber IS NULL) AND (mm.contractid='' OR mm.contractid IS NULL) AND free_flag=0 AND (mm.mappedNo='' or mm.mappedNo='0' or mm.mappedNo is null) AND mm.approved_flag=0 AND mm.virtualNo!=0 AND mm.city = '".$this->city_vn."' LIMIT 1 FOR UPDATE";    
			$res_get_vn_from_db = $this->execute_query($sql_get_vn_from_db,$this->conn_decs);
			if($res_get_vn_from_db && mysql_num_rows($res_get_vn_from_db)>0)
			{
				$row_vno=mysql_fetch_assoc($res_get_vn_from_db);
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
		if(intval($virtualNo) && false)
		{
			if($this->updateMapdetailconnect)
			{
				if(APP_LIVE == 1)
				{
					$remote_city = strtoupper(trim($this->city_vn));
					if(trim($remote_city) == 'JAIPUR' || trim($remote_city) == 'CHANDIGARH' || trim($remote_city) == 'COIMBATORE')
					{
						$this->mapdetails_conn    =   new DB(array(constant(strtoupper($this->city_vn).'_MAP_DETAILS_DB_IP'), TECH_INFO_DB_USERID, TECH_INFO_DB_PASSWORD, 'paypercall'));
						$this->updateMapDetails($virtualNo);
					}
				}
				else
				{
					$remote_city = strtoupper(trim($this->city_vn));
					if((trim($remote_city) == 'JAIPUR' || trim($remote_city) == 'CHANDIGARH' || trim($remote_city) == 'COIMBATORE') && defined("REMOTE_CITY_MODULE"))
					{
						$this->mapdetails_conn    =   new DB(array('192.168.6.96', 'decs_app', 's@myD#@mnl@sy', 'paypercall'));
						$this->updateMapDetails($virtualNo);
					}
				}
			}
			else
			{
				$logtbl_process_flag =1;
				$logtbl_reason = $remote_city." city mapped detail server is down";
				$qry_insert_tbltechinfofailed_process = "INSERT INTO tbl_techinfo_failed_process_log SET vno=" . (int)$virtualNo . ", parentid ='".trim($this->parentid)."',process_flag='".$logtbl_process_flag."',update_date='".date('Y-m-d H:i:s')."', reason='".$logtbl_reason."',processname='".$this->processName."'";
				$res_insert_tbltechinfofailed_process = $this->execute_query($qry_insert_tbltechinfofailed_process,$this->conn_decs);
				$message = "<br>ParentId = " . $this->parentid . "<br>Process Name = ".$this->processName."<br>Reason = ".constant(strtoupper($this->city_vn).'_MAP_DETAILS_DB_IP')." server down <br>City = ". $this->city_vn;
				$subject =  $remote_city ." city Mapped detail server down : server (".constant(strtoupper($this->city_vn).'_MAP_DETAILS_DB_IP').")";
				$this->sendMail($subject, $message);
			}
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
				
				$updateVn = "update tbl_companymaster_generalinfo  set virtualNumber = '".intval($this->virtualno)."' where parentid = '".$parentid."'";
				$resupdateVn = $this->execute_query($updateVn,$this->conn_iro);
				$extra_str="[updated virtual number:".$this->virtualno."][update Qry :".$updateVn."][update qry result: ".$resupdateVn."]";
				$this->logmsgvirtualno("Update tbl companymaster general info",$this->log_path,$this->processName,$this->parentid,$extra_str);
			}
			if($jdfos==1)
			{
				$this->genio_update_virtual_number($parentid,1,'');
			}
			else{
				$this->genio_update_virtual_number($parentid,'','');
			}
            if($this->linkcontract_flag && false)
            {
				$this->updateCompLinkVn($this->linkcontracts);
			}
		}
		elseif($block_flag==1)
		{
			//$this->updateBlockUnblockGenio($parenid_list,$block_flag);
			$this->updateBlockUnblockGenio($this->parentid,$block_flag);
			if(!$this->quarantineFlag)
			{
				$this->QuarantineNo('block for virtual number');
			}
			$QryBlockvnComp = "update tbl_companymaster_generalinfo  set virtualNumber = '',virtual_mapped_number = '',pri_number = '' where parentid in ( '".$parenid_list."')";
			$ResBlockvnComp = $this->execute_query($QryBlockvnComp,$this->conn_iro);
			
			$extra_str="[updated virtual number:".$this->virtualno."][update Qry :".$QryBlockvnComp."][update qry result: ".$ResBlockvnComp."]";
			$this->logmsgvirtualno("Update block virtual number in tbl companymaster ganeral info",$this->log_path,$this->processName,$this->parentid,$extra_str);
			$this->updateCompanyMasterSearch();
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
		$update_cm_general = "UPDATE tbl_companymaster_generalinfo SET blockforvirtual='".$flag."' WHERE parentid IN ('".$pidlist."')";
		$res_update_cm_general	=	$this->execute_query($update_cm_general,$this->conn_iro);
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
					$qryGetLinkMap = "select parentid,landline_display,mobile_display,tollfree from tbl_companymaster_generalinfo where parentid = '".$value."'";
					$resGetLinkMap = $this->execute_query($qryGetLinkMap,$this->conn_iro);
					if($resGetLinkMap && mysql_num_rows($resGetLinkMap)>0)
					{
						$tempLandlineArr	= array();
						$tempMobileArr 		= array();
						$tempTollfreeArr	= array();
						$tempContactArr		= array();
						$tempMappedNo = '';
						$rowGetLinkMap = mysql_fetch_assoc($resGetLinkMap);
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
							$QryTempUpdateComp = "UPDATE tbl_companymaster_generalinfo SET virtualNumber = '".$this->virtualno."',virtual_mapped_number = '".$tempMappedNo."', pri_number = '".$this->pri_number."' WHERE parentid ='".$value."'";
							$ResTempUpdateComp = $this->execute_query($QryTempUpdateComp,$this->conn_iro);
							
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
		if($this->linkcontract_flag)
        {
			$parenid_list = implode("','",$this->linkcontracts);
		}
		else
		{
			$parenid_list = $this->parentid;
		}
		$qryCheckPhoneSearcgStatus = "select parentid,campaignid from tbl_companymaster_finance where parentid in ('".$parenid_list."') and campaignid in (1,2,16) and balance>0";
		$resCheckPhoneSearcgStatus =	$this->execute_query($qryCheckPhoneSearcgStatus,$this->conn_finance);
		if($resCheckPhoneSearcgStatus)
		{
			if(mysql_num_rows($resCheckPhoneSearcgStatus)>0)
			{
				$jdPaid = 1;
			}
		}
		$qryCheckJDFOSstatus = "select parentid,campaignid from tbl_companymaster_finance where parentid in ('".$parenid_list."') and campaignid in (23)";
		$resCheckJDFOSstatus =	$this->execute_query($qryCheckJDFOSstatus,$this->conn_finance);
		if($resCheckJDFOSstatus)
		{
			if(mysql_num_rows($resCheckJDFOSstatus)>0)
			{
				$jdFos = 1;
			}
			else
			{
				if(intval($this->JDFOS_flag)==1)
				{
					$jdFos = 1;
				}
			}
		}
		$this->techinfoPidStatus = bindec($jdFos.$jdPaid);
		
		return $this->techinfoPidStatus;
	}
	
	function checkBlockflag($pid)
	{
		$flag = false;
		$qryGetVnflag = "select blockforvirtual from tbl_companymaster_generalinfo where parentid ='".$pid."' and virtualNumber>0";
		$resGetVnflag =	$this->execute_query($qryGetVnflag,$this->conn_iro);
		if($resGetVnflag && mysql_num_rows($resGetVnflag)>0)
		{
			$flag = true;
		}
		else
		{
			$qryGetBlockflag ="select blockforvirtual from tbl_companymaster_generalinfo where parentid ='".$pid."' and blockforvirtual=1";
			$resGetBlockflag =	$this->execute_query($qryGetBlockflag,$this->conn_iro);
			if($resGetBlockflag && mysql_num_rows($resGetBlockflag))
			{
				$flag = true;
			}
		}
		return $flag;
	}
	
	function checkJdfos()
	{
		$jdfocCurlurl =  $this->jdfos_url."restDet/".$this->parentid;
		$jdfocCurlurl_output = $this->runCurlUrl($jdfocCurlurl);
		if(!$this->curl_responce_flag)
		{
			$message = "<br>ParentId = " . $this->parentid . "<br>Process Name = ".$this->processName."<br>Reason = JDFOS fetch url not wokring<br>Checking contract is JDFOS or not<br>City = ". $this->city_vn."<br>Virtual NO : ".$jdfosVno."<br>Url : ".$jdfocCurlurl;
			$subject = $this->city_vn ." - JDFOS fetch url not working";
			$this->sendMail($subject, $message);
		}
		else
		{
			$jdfos_json = json_decode($jdfocCurlurl_output,true);
			if($jdfos_json['results']['gen_info']['0']['parentid'] == $this->parentid)
			{
				if(intval($this->JDFOS_flag)==0){
					$this->JDFOS_flag = true;
					$extra_str="[updated virtual number:".$this->virtualno."][ contract present on JDFOS server][jdfos flag:".$this->JDFOS_flag."]";
					$this->logmsgvirtualno("contract not contain JDFOS campaign in finance so checking on JDFOS server",$this->log_path,$this->processName,$this->parentid,$extra_str);
				}
			}
			else
			{
				$extra_str="[updated virtual number:".$this->virtualno."][ contract present on JDFOS server][jdfos flag:".$this->JDFOS_flag."]";
				$this->logmsgvirtualno("contract not contain JDFOS campaign in finance so checking on JDFOS server",$this->log_path,$this->processName,$this->parentid,$extra_str);
			}
		}
	}
	function BlockMappedNumber($number)
	{
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
				}
				else
				{
					$extra_str="[reason: after remove block number, not single number is present which is use as mapped number ][block number : ".$number."][virtual no :".$this->virtualno."]";
					$this->logmsgvirtualno("Contract is not eligible.",$this->log_path,$this->processName,$this->parentid,$extra_str);
					
					$this->quarantineFlag = $this->checkQuarantineVn();
					
					$extra_str="[virtual number not in quarantine table][block number : ".$number."][virtual no :".$this->virtualno."]";
					$this->logmsgvirtualno("Checking quarantine.",$this->log_path,$this->processName,$this->parentid,$extra_str);
					
					if(!$this->quarantineFlag)
					{
						$reason = "after remove block number, not single number is present which is use as mapped number";
						$this->QuarantineNo($reason);
					}
					$this->updateCompanymaster();
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
		
		$qryGetBlockVn = "select blockforvirtual from tbl_companymaster_generalinfo where parentid ='".$qPid."' and blockforvirtual=1";
		$resGetBlockVn =	$this->execute_query($qryGetBlockVn,$this->conn_iro);
		if($resGetBlockVn && mysql_num_rows($resGetBlockVn))
		{
			$flag = true;
		}
		return $flag;
	}
	
	function sendMail($subject, $msg)
	{
		//$tech_emailids = array('shakirshaikh@justdial.com','yogitatandel@justdial.com','sankalpnavghare@justdial.com','imteyaz.raja@justdial.com');
		$tech_emailids = array('yogitatandel@justdial.com');
		$headers = "From: noreply@justdial.com\r\n";
		$headers .= "Content-type: text/html\r\n"; 
		if(!is_array($emails))
		{
			$emails = array(trim($emails));
		}
		foreach($tech_emailids as $value)
		{
			mail($value, $subject, $msg, $headers);
		}
	}
    
    function insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name)
    {
        if($this->direct_update)
        {
            $qry_insert_tbltechinfofailed_process = "INSERT INTO tbl_techinfo_failed_process_log SET vno=" . (int)$this->virtualno . ", parentid ='".trim($this->parentid)."',process_flag='".$logtbl_process_flag."',update_date='".date('Y-m-d H:i:s')."', reason='".$logtbl_reason."',processname='".$process_name."'";
            $res_insert_tbltechinfofailed_process = $this->techinfo_conn->query_sql($qry_insert_tbltechinfofailed_process);        
        }
        else
        {
            $qry_insert_tbltechinfofailed_process = "INSERT INTO tbl_techinfo_failed_process_log SET vno=" . (int)$this->virtualno . ", parentid ='".trim($this->parentid)."',process_flag='".$logtbl_process_flag."',update_date='".date('Y-m-d H:i:s')."', reason='".$logtbl_reason."',processname='".$process_name."'";
            $res_insert_tbltechinfofailed_process = $this->conn_decs->query_sql($qry_insert_tbltechinfofailed_process);
        }
        $extra_str="[Qry run : ".$qry_insert_tbltechinfofailed_process."][Qry result : ".$res_insert_tbltechinfofailed_process."][Process flag : ".$logtbl_process_flag."][log table reason : ".$logtbl_reason."][Process name : ".$process_name."]";
        $this->logmsgvirtualno("Insert into log Table if process is failed.",$this->log_path,$this->processName,$this->parentid,$extra_str);
		$parentId =$this->parentid;
		$message = "<br>ParentId = " . $parentId . "<br>Process Name = ".$process_name."<br>Reason = " . $logtbl_reason . "<br>URL = " . $this->techinfo_url . "<br>City = ". $this->city_vn;
		$subject = "Techinfo Server Error";
		$this->sendMail($subject, $message);
    }
    
    function logmsgvirtualno($sMsg, $sNamePrefix,$process,$contractid,$extra_str='')
    {
        $log_msg='';
        // fetch directory for the file
        $pathToLog = dirname($sNamePrefix); 
        if (!file_exists($pathToLog)) {
            mkdir($pathToLog, 0755, true);
        }
        /*$file_n=$sNamePrefix.$contractid.".txt"; */
        $file_n=$sNamePrefix.$contractid.".html";
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
?>
