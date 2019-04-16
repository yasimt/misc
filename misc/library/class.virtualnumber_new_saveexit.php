<?php
class Virtualnumber
{
    private $parentid, $paid_flag, $hidden_flag, $pincode_flag, $mappednumber_flag, $expired_flag, $freeze_flag, $businessEligibility, $landline, $mobile, $tollfree, $finalmappednumbers, $dndnumbers, $status , $update_techinfo_flag, $linkcontract_flag, $linkcontracts, $linkcontractsvalues, $latestcontractid, $rootcontractid, $virtualno, $stdcode,$techinfo_url,$curl_responce_flag, $techinfo_array, $change_flag, $quarantineEligibility, $all_expr_link,$mainrootcontractid ,$phonesearch_flag, $landline_display_str, $mobile_display_str , $landline_str, $mobile_str, $contact_arr, $eligible_linkcontract,$contacts_mode,$top_mappednumber_withoutstd,$invalid_virtualno;
    private $city_vn, $conn_iro, $conn_finance, $techinfo_conn, $log_path, $dndobj, $testing_numbers,$assign_newVno;
    public $changes_done, $direct_update, $reason, $process_flag,$affect_contract, $insertion_time;


    const TOTAL_REQUIRE_MAPPEDNO = 8; /* total mapped numbers tech info can store */
    const TOTAL_TECHINFO_MAPPENO = 8; /* total mapped numbers return form techinfo virtual for perticular virtual number*/
    const CURL_TIMEOUT = 5;
    
    function __construct($parentid, $dbarr, $city_vn, $dndobj=null)
	{
        if(trim($parentid) == '')
		{
			 die("parentid is blank");
		}
        if(!defined('APP_PATH'))
        {
            die("APP PATH not defined....");
        }
        $this->log_path			= APP_PATH . '/logs/virtualNoLogs/';
        $this->dndobj			= $dndobj;
        $this->city_vn          = $city_vn;
        $this->conn_iro         = new DB($dbarr['DB_IRO']);
        $this->conn_finance     = new DB($dbarr['FINANCE']);
        $this->conn_decs     	= new DB($dbarr['DB_DECS']);
        $this->conn_ecsbill     = new DB($dbarr['ECS_BILL']);
        $this->techinfo_conn    = null;
        $this->testing_numbers 	= array();
        $this->direct_update    = false;                        
        
        $this->initialized_testingnumbers();
        $this->initialized($parentid,$dbarr,$dndobj='null');
        $this->initializedtechinfo();
		$this->insertion_time = date("Y-m-d H:i:s");
    }

    function initialized_testingnumbers()
    {
        switch($this->city_vn)
        {
            case 'MUMBAI' :
                $this->testing_numbers  =    array(61426204, 61426700, 61427281, 61427799, 61428229, 61428767, 61429202, 61625001, 61625506, 61626504, 61627000, 61635000, 61635500, 61639499, 61636501, 61637000, 61637500, 61638000, 61639499, 66720400, 66824002, 67303500, 67307500, 67688400, 67689001, 67733000, 61638999, 61425994, 61626452);
            break;
            case 'DELHI' :
                $this->testing_numbers  =    array(66223710, 66217007, 66226216, 66226714, 66227229, 66227789, 66228217 , 66264015, 66264538, 66265016, 66265556, 66266032, 66266519, 66430021, 66433037, 66436035, 66440029, 66442006, 66261501, 66261801, 66262001);
            break;
            case 'KOLKATA' :
                $this->testing_numbers  =   array(66347880, 66348484, 66039890, 66341499, 66341719, 66342404, 66342999, 66343428, 66343939, 66346492, 66346984, 66347497, 66245000);
            break;
            case 'BANGALORE' :
                $this->testing_numbers  =   array(66379034, 66379623, 66366498, 66388899, 66389570, 66367599, 66507000, 66507556, 66508101, 66534004, 66534552, 66536002, 66536500, 66537000);
            break;
            case 'CHENNAI' :
                $this->testing_numbers  =   array(66077000, 66245000, 66245500, 66246000, 66246500, 66247000, 66247500, 66323500, 66324700, 66324200, 66323125, 66325697, 66368984, 66369448, 66369759, 66326154, 66599999, 66593999);
            break;
            case 'PUNE' :
                $this->testing_numbers  =   array(66285763, 66822512, 66239388, 66239720, 66491062, 66491341, 66491886, 67281000);
            break;
            case 'HYDERABAD' :
                $this->testing_numbers  =   array(66045997, 66046997, 66047797, 66049997, 67112997, 67115997, 67118997, 67119497, 67119997, 67120497, 67120997, 67239997, 66046497, 66048297, 66048797, 66049497);
            break;
            case 'AHMEDABAD' :
                $this->testing_numbers  =   array(66153494, 66152498, 66151500, 66152992, 66156638, 66087999);
            break;
        }    
    }

    function initialized($parentid)
    {
        $this->parentid                 =   $parentid;
        $this->changes_done             =   false;
        $this->paid_flag                = -1;
        $this->hidden_flag              = false;
        $this->phonesearch_flag         = false;
        $this->pincode_flag             = false;
        $this->mappednumber_flag        = false;
        $this->expired_flag             = false;
        $this->freeze_flag              = false;
        $this->businessEligibility      = true;
        $this->landline                 = array();
        $this->mobile                   = array();
        $this->tollfree                 = array();
        $this->finalmappednumbers       = array();
        $this->dndnumbers               = array();
        $this->status                   = '';
        $this->update_techinfo_flag     = false;
        $this->process_flag             = false;
        $this->virtualno                = 0;        
        $this->linkcontract_flag        = false;
        $this->rootcontractid           = '';
        $this->mainrootcontractid       = '';
        $this->linkcontracts            = array();
        $this->linkcontractsvalues      = array();
        $this->latestcontractid         = '';
        $this->stdcode                  = '';
        $this->affect_contract          =  false;
        $this->techinfo_array           = array();
        $this->change_flag              = 0;
        $this->quarantineEligibility    = true;
        $this->all_expr_link            = false;
        $this->landline_display_str     = '';
        $this->mobile_display_str       = '';
        $this->landline_str             = '';
        $this->mobile_str               = '';
        $this->tollfree                 = '';
        $this->contact_arr              = array();
        $this->contacts_mode            = array('landline','mobile','tollfree');
        $this->eligible_linkcontract = array();
        $this->top_mappednumber_withoutstd = array();
        $this->assign_newVno = false;
        $this->invalid_virtualno = array();
    }
    
	function initializedtechinfo()
    {
        if($this->direct_update)
        {
            $this->techinfo_conn    =   new DB(array(constant(strtoupper($this->city_vn) . '_TECH_DB_IP'), TECH_INFO_DB_USERID, TECH_INFO_DB_PASSWORD, TECH_INFO_DB_NAME));
            if(!$this->techinfo_conn)
            {
                echo "<br>Can't connect to Techinfo server";
                exit;
            }
        }
        else
        {
            if(LIVE_APP == 1)
            {
                $this->techinfo_url="http://".constant(strtoupper($this->city_vn ).'_TECH_API_URL')."/justdial/";
            }
            else
            {
                $this->techinfo_url="http://techinfo.jdsoftware.com/justdial/"; 
            }
        }
    }

    function get_allmember_vars()
    {
        return array('parentid'=>$this->parentid, 'change_done' => $this->changes_done, 'paid_flag'=> $this->paid_flag, 'hidden_flag'=>$this->hidden_flag, 'pincode_flag'=> $this->pincode_flag, 'mappednumber_flag' => $this->mappednumber_flag, 'expired_flag'=> $this->expired_flag, 'freeze_flag' => $this->freeze_flag, 'businessEligibility' => $this->businessEligibility, 'landline' => $this->landline, 'mobile' => $this->mobile, 'tollfree'=>$this->tollfree,  'finalmappednumbers' => $this->finalmappednumbers, 'dndnumbers' => $this->dndnumbers, 'status' => $this->status, 'update_techinfo_flag' => $this->update_techinfo_flag, 'process_flag'=> $this->process_flag, 'virtualno' => $this->virtualno, 'linkcontract_flag'=>$this->linkcontract_flag, 'rootcontractid' => $this->rootcontractid, 'mainrootcontractid'=>$this->mainrootcontractid, 'linkcontracts'=>$this->linkcontracts, 'linkcontractsvalues' =>        $this->linkcontractsvalues, 'latestcontractid' => $this->latestcontractid, 'stdcode'=>$this->stdcode, 'affect_contract'=>$this->affect_contract);
    }

    function process_update_virtual_number($parentid)
    {
        $this->direct_update    =   true;
        $this->process_flag= true;
        
        $this->initializedtechinfo();
        
        if($parentid!=$this->parentid)
        {
            $this->initialized($parentid);
        }
        $qry_insert_process_vrnmap = "INSERT INTO process_vrnmap SET parentid = '" . $this->parentid . "', process_starttime = '" . date('Y-m-d H:i:s') . "' ON DUPLICATE KEY UPDATE process_starttime = '" . date('Y-m-d H:i:s') . "'";
        $res_insert_process_vrnmap = $this->execute_query($qry_insert_process_vrnmap, $this->techinfo_conn);
        unset($qry_insert_process_vrnmap);
        $this->business_eligibility();
        if($this->businessEligibility)
        {            
            $this->check_expiry();
            if(!$this->expired_flag) /*stop process for more than six month expired contract */
            {
                $this->check_freeze();  /* check contract is freez or mask */
            }
        }
        $qry_updt_oldvalue_process_vrnmap ="UPDATE process_vrnmap SET oldvalue = '" . print_r($this->get_allmember_vars(), true) . "' WHERE parentid='".$this->parentid."'";
        //$res_updt_oldvalue_process_vrnmap = $this->techinfo_conn->query_sql($qry_updt_oldvalue_process_vrnmap);
        $res_updt_oldvalue_process_vrnmap =$this->execute_query($qry_updt_oldvalue_process_vrnmap,$this->techinfo_conn);
        unset($qry_updt_oldvalue_process_vrnmap);
        if($this->businessEligibility && !$this->expired_flag && !$this->freeze_flag)
        {
            if(intval($this->virtualno)==0)
            {
                $this->affect_contract= true;
            }
            $this->allocate();
        }
        else
        {
            $this->deallocate();
        }
        $qry_updt_newvalue_process_vrnmap = "UPDATE process_vrnmap SET newvalue =  '" . print_r($this->get_allmember_vars(), true) . "', process_endtime = '" . date('Y-m-d H:i:s') . "', done_flag = '" . $this->changes_done . "' WHERE parentid = '" . $this->parentid . "'";
        $res_updt_newvalue_process_vrnmap = $this->execute_query($qry_updt_newvalue_process_vrnmap, $this->techinfo_conn);
        unset($qry_updt_newvalue_process_vrnmap);
    }
    
	function genio_update_virtual_number($parentid)
    {
        $process_flag = false;
        $this->initializedtechinfo(); 
        $this->initialized($parentid);
        $this->business_eligibility();
        if($this->businessEligibility && !$this->expired_flag && !$this->freeze_flag)
		{
            $this->allocate();
        }
        else
        {
            if(intval($this->virtualno)==0)
            {
                $extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][process flag :".$process_flag."] [virtual number :".intval($this->virtualno)."]";
                $this->logmsgvirtualno("Contract is not eligible as well as it does not have virtual number",$this->log_path,'Approval process',$this->parentid,$extra_str);
            }
            else
            {
                $extra_str="[paid flag : ".$this->paid_flag."][reason : ".$this->reason."][process flag :".$process_flag."]";
                $this->logmsgvirtualno("Contract is not eligible.",$this->log_path,'Approval process',$this->parentid,$extra_str);
            }
        }
    }

    function business_eligibility()
    {
        $this->get_linked_contracts();
        $this->check_paid_contract();
        if($this->businessEligibility)
        {
            $this->check_expiry();
            if($this->businessEligibility)
            {
                $this->check_hiddencontract();
                if($this->businessEligibility)
                {
                    if($this->businessEligibility)
                    {
                        $this->check_pincode_flag();
                        if($this->businessEligibility)
                        {
                            $this->get_mappednumber();
                            if(!$this->businessEligibility)
                            {
                                $this->reason = 'mapped number not exist in the contract or in any link contracts';
                            }
                        }
                        else
                        {
                            $this->reason = 'pincode of other city';
                        }
                    }
                    else
                    {
                        $this->reason = 'not a phone search contract';
                    }
                }
                else
                {
                    $this->reason = 'hidden';
                }
            }
            else
            {
                $this->reason = 'expired';
            }
        }
        else
        {
            $this->reason = 'nonpaid';
        }
    }
    function check_paid($parentid)
    {
        $is_paid_contract=false;
        if(!$is_paid_contract)
        {
            $qry_paid_contract = "SELECT DISTINCT parentid FROM payment_instrument_summary WHERE parentid='".trim($parentid)."' and approvalStatus='1' LIMIT 1";
            //$res_paid_contract = $this->conn_finance->query_sql($qry_paid_contract);
            $res_paid_contract = $this->execute_query($qry_paid_contract,$this->conn_finance);
            if(!$res_paid_contract)
            {
                die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_paid_contract);
                return $is_paid_contract;                
            }
            else
            {            
                if(mysql_num_rows($res_paid_contract)>0)
                {
                    mysql_free_result($res_paid_contract);
                    $is_paid_contract = true;
                    return $is_paid_contract;
                }
                else
                {
                    mysql_free_result($res_paid_contract);
                    $paymentQry="SELECT a.billnumber FROM " . DB_ECS_BILLING . ".ecs_bill_details a JOIN " . DB_ECS_BILLING . ".ecs_bill_breakup b ON a.billnumber = b.billnumber
                            JOIN " . DB_ECS_BILLING . ".ecs_bill_clearance_details c ON b.billnumber = c.billnumber WHERE c.billResponseStatus in (1,3) AND c.billApportioningFlag=1 AND a.data_city = '" . $this->city_vn . "' AND a.parentid = '" . trim($parentid) . "' ORDER BY c.billResponseDate DESC LIMIT 1";
                    //$paymentRes= $this->conn_finance->query_sql($paymentQry);
                    $paymentRes = $this->execute_query($paymentQry,$this->conn_finance);
                    if(!$paymentRes)
                    {
                        die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $paymentQry);
                        return $is_paid_contract;
                    }
                    else
                    {
                        if(mysql_num_rows($paymentRes)>0)
                        {
                            mysql_free_result($paymentRes);
                            $is_paid_contract = true;
                            return $is_paid_contract;
                        }
                        else
                        {
                            mysql_free_result($paymentRes);
                            $paymentQry_SI="SELECT a.billnumber FROM " . DB_SI_BILLING . ".si_ecs_bill_details a JOIN " . DB_SI_BILLING . ".si_ecs_bill_breakup b ON a.billnumber = b.billnumber JOIN " . DB_SI_BILLING . ".si_ecs_bill_clearance_details c ON b.billnumber = c.billnumber WHERE c.billResponseStatus = 1 AND c.billApportioningFlag=1 AND a.data_city = '" . $this->city_vn . "' AND a.parentid='" . trim($parentid) . "' ORDER BY c.billResponseDate DESC LIMIT 1";
                            //$paymentRes_SI= $this->conn_finance->query_sql($paymentQry_SI);
                            $paymentRes_SI = $this->execute_query($paymentQry_SI,$this->conn_finance);
                            if(!$paymentRes_SI)
                            {
                                die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $paymentQry_SI);
                                return $is_paid_contract;
                            }
                            else
                            {
                                if(mysql_num_rows($paymentRes_SI)>0)
                                {
                                    mysql_free_result($paymentRes_SI);
                                    $is_paid_contract = true;
                                    return $is_paid_contract;
                                }         
                                else
                                {
                                    $fcv_fdv = false;
                                    mysql_free_result($paymentRes_SI);
                                    $check_Fund_transfer_contract="SELECT transactionId FROM payment_funds_transfer_master WHERE destinationParentid='".trim($parentid)."' AND transferDestFlag=1";
                                    //$res_check_Fund_transfer_contract = $this->conn_finance->query_sql($check_Fund_transfer_contract);
                                    $res_check_Fund_transfer_contract = $this->execute_query($check_Fund_transfer_contract,$this->conn_finance);
                                    if(!$res_check_Fund_transfer_contract)
                                    {
                                        die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $check_Fund_transfer_contract);
                                        //return false;
                                        return $is_paid_contract;
                                    }
                                    else
                                    {
                                        if(mysql_num_rows($res_check_Fund_transfer_contract)>0)
                                        {
                                            $fcv_fdv = true;
                                            mysql_free_result($res_check_Fund_transfer_contract);
                                            $is_paid_contract = true;
                                            return $is_paid_contract;
                                        }
                                        else
                                        {
                                            mysql_free_result($res_check_Fund_transfer_contract);
                                            $check_FCV_contract = "SELECT parentid FROM tbl_company_refer_fdv WHERE campaign='surplus' AND flag=1 AND parentid='".trim($parentid)."'";
                                            //$res_check_FCV_contract = $this->conn_finance->query_sql($check_FCV_contract);
                                            $res_check_FCV_contract = $this->execute_query($check_FCV_contract,$this->conn_finance);
                                            if(!$res_check_FCV_contract)
                                            {
                                                die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $check_FCV_contract);
                                                return $is_paid_contract;
                                            }
                                            else
                                            {
                                                if(mysql_num_rows($res_check_FCV_contract)>0)
                                                {
                                                    $fcv_fdv = true;
                                                    mysql_free_result($res_check_FCV_contract);
                                                    $is_paid_contract = true;;
                                                }
                                                else
                                                {
                                                    $fcv_fdv = false;
                                                    mysql_free_result($res_check_FCV_contract);
                                                    $is_paid_contract = false;
                                                }
                                            }
                                        }
                                        if($is_paid_contract)
                                        {
                                            //check balance of bid_details or expiry_flag = 1
                                            //none is true set 0
                                            $get_bid_detail="SELECT SUM(bid_chgthreshold+bid_chgadbudget) AS budget, parentid,MAX(expired) AS expired_flag FROM bid_details WHERE parentid ='".trim($parentid)."' GROUP BY parentid";
                                            //$res_get_bid_detail = $this->conn_finance->query_sql($get_bid_detail);
                                            $res_get_bid_detail = $this->execute_query($get_bid_detail,$this->conn_finance);
                                            if(!$res_get_bid_detail)
                                            {
                                                die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $get_bid_detail);
                                                $is_paid_contract= false;
                                                return $is_paid_contract;
                                            }
                                            else
                                            {
                                                if(mysql_num_rows($res_get_bid_detail)>0)
                                                {
                                                    $row_get_bid_detail = mysql_fetch_assoc($res_get_bid_detail);
                                                    if($row_get_bid_detail['budget']<=0 )
                                                    {
                                                        $is_paid_contract = false;
                                                        return $is_paid_contract; 
                                                    }
                                                }
                                                mysql_free_result($res_get_bid_detail);                                 
                                                return $is_paid_contract;
                                            }
                                        }
                                    }
                                }                  
                            }
                        }              
                    }
                }
            }
        }
        return $is_paid_contract;
    }
    function check_paid_contract()
    {
        $single_paidflag='';
        if($this->linkcontract_flag)
        {
            foreach($this->linkcontracts as $pids)
            {
                $single_paidflag = $this->check_paid($pids);
                if($this->parentid == $pids)
                {
                    if($single_paidflag)
                    {
                        $this->paid_flag = 1;
                    }
                    else
                    {
                        $this->paid_flag = 0;
                    }
                }
                if($single_paidflag)
                {
                    $this->linkcontractsvalues[$pids]['paid']= 1;
                    if(!$this->linkcontractsvalues[$pids]['paid'])
                    {
                        $this->linkcontractsvalues[$pids]['businessEligibility']= false;
                    }
                }

            }
        }
        else
        {
            $single_paidflag = $this->check_paid($this->parentid);
            if($single_paidflag)
            {
                $this->paid_flag = 1;
            }
            else
            {
                $this->paid_flag = 0;
            }
        }
        if(!$this->paid_flag)
        {
            $this->businessEligibility = false;
        }
    }

    function check_hidden($parentid)
    {
        $is_hidden_contract=false;
        if(!$is_hidden_contract)
        {
            $qry_hidden_contract = "SELECT parentid, 1 FROM bid_details WHERE parentid = '".$parentid."' AND contractid LIKE 'H%' LIMIT 1";
            //$res_hidden_contract = $this->conn_finance->query_sql($qry_hidden_contract);
            $res_hidden_contract = $this->execute_query($qry_hidden_contract,$this->conn_finance);
            if(!$res_hidden_contract)
            {
                        die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_hidden_contract);
                        //return false;         
                        return $is_hidden_contract;
            }
            else
            {
                if(mysql_num_rows($res_hidden_contract)>0)
                {
                    $is_hidden_contract = true;
                    return $is_hidden_contract;
                }
                mysql_free_result($res_hidden_contract);
            }
        }
        return $is_hidden_contract;
    }
    function check_hiddencontract()
    {
        $single_hiddenflag='';
        if($this->linkcontract_flag)
        {
            foreach($this->linkcontracts as $pids)
            {
                $single_hiddenflag = $this->check_hidden($pids);
                if($single_hiddenflag)
                {
                    $this->linkcontractsvalues[$pids]['hidden']= true;
                    if($this->parentid == $pids)
                    {
                        $this->hidden_flag = true;
                    }
                }
                if($this->linkcontractsvalues[$pids]['hidden'])
                {
                    $this->linkcontractsvalues[$pids]['businessEligibility'] = false;
                }
            }
        }
        else
        {
            $single_hiddenflag = $this->check_hidden($this->parentid);
            if($single_hiddenflag)
            {
                $this->hidden_flag = true;
            }
        }
        if($this->hidden_flag)
        {
            $this->businessEligibility = false;
        }
    }

    function check_weblisting($parentid)
    {
        $is_catspon_contract=false;
        $get_plat_package_budget = "SELECT sum(bid_perday) AS budgetperday  FROM tbl_clients_deduction_perday_master WHERE parentid='".$parentid."' and campaignid in (1,2)";
        //$res_get_plat_package_budget = $this->conn_finance->query_sql($get_plat_package_budget);
        $res_get_plat_package_budget = $this->execute_query($get_plat_package_budget,$this->conn_finance);
        if(!$res_get_plat_package_budget)
        {
            die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $get_plat_package_budget);
            return false; 
        }
        else
        {
            if(mysql_num_rows($res_get_plat_package_budget)<0)
            {
                $is_catspon_contract=true;
            }
        }
        return $is_catspon_contract;
    }
    
    function check_weblisting_contract()
    {
        $single_catsponflag='';
        /*here check that */
        if($this->linkcontract_flag)
        {
            foreach($this->linkcontracts as $pids)
            {
                $single_catsponflag = $this->check_weblisting($pids);
                if($single_catsponflag)
                {
                    $this->linkcontractsvalues[$pids]['nophonesearch']= true;
                    if($this->parentid == $pids)
                    {
                        $this->phonesearch_flag = true;
                    }
                }
                if($this->linkcontractsvalues[$pids]['nophonesearch'])
                {
                    $this->linkcontractsvalues[$pids]['businessEligibility'] = false;
                }
                
            }
        }
        else
        {
            $single_catsponflag = $this->check_weblisting($this->parentid);
            if($single_catsponflag)
            {
                $this->phonesearch_flag = true;
            }
        }
        if($this->phonesearch_flag)
        {
            $this->businessEligibility = false;
        }
    }

    function check_pincode($parentid)
    {
        $is_pincode_present = false;
        $qry_paincode_vn_contract = "SELECT pincode, virtualnumber FROM tbl_companymaster_generalinfo WHERE parentid = '".$parentid."'";
        //$res_paincode_vn_contract = $this->conn_iro->query_sql($qry_paincode_vn_contract);
        $res_paincode_vn_contract = $this->execute_query($qry_paincode_vn_contract,$this->conn_iro);
        if(!$res_paincode_vn_contract)
        {
            die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_paincode_vn_contract);
            return false;                 
        }
        else
        {
            if(mysql_num_rows($res_paincode_vn_contract)>0)
            {
                $row_paincode_vn_contract = mysql_fetch_array($res_paincode_vn_contract);
                $pincode = $row_paincode_vn_contract['pincode'];
                if(intval(trim($pincode))>0)
                {
                    $qry_check_pincode_incity = "SELECT display FROM ".DB_JDS_LIVE.".tbl_areas_count WHERE pin_code LIKE '%" . $pincode . "%' AND display > 0";
                    //$res_check_pincode_incity = $this->conn_iro->query_sql($qry_check_pincode_incity);
                    $res_check_pincode_incity = $this->execute_query($qry_check_pincode_incity,$this->conn_iro);
                    if($res_check_pincode_incity)
                    {
                        if(mysql_num_rows($res_check_pincode_incity) > 0)
                        {
                            $is_pincode_present = true;
                            return $is_pincode_present;
                        }
                        mysql_free_result($res_check_pincode_incity);
                    }
                    else
                    {
                        die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_check_pincode_incity);
                        return $is_pincode_present;                                     
                    }
                }
            }
            mysql_free_result($res_paincode_vn_contract);
        }
        return $is_pincode_present;
    }

    function check_pincode_flag()
    {
        $single_pincodeflag ='';
        if($this->linkcontract_flag)
        {
            foreach($this->linkcontracts as $pids)
            {
                $single_pincodeflag = $this->check_pincode($pids);
                if($single_pincodeflag)
                {
                    $this->linkcontractsvalues[$pids]['pincode']= true;
                    if($this->parentid == $pids)
                    {
                        $this->pincode_flag = true;
                    }
                }
                if(!$this->linkcontractsvalues[$pids]['pincode'])
                {
                    $this->linkcontractsvalues[$pids]['businessEligibility'] = false;
                }
            }
        }
        else
        {
            $single_pincodeflag = $this->check_pincode($this->parentid);
            if($single_pincodeflag)
            {
                $this->pincode_flag = true;
            }
        }
        if(!$this->pincode_flag)
        {
            $this->businessEligibility = false;
        } 
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

    function get_eligible_linkcontract()
    {
        $this->eligible_linkcontract = array();
        foreach($this->linkcontracts as $pids)
        {
            if($this->linkcontractsvalues[$pids]['businessEligibility'])
            {
                $this->eligible_linkcontract[] = $pids;
            }
        }
    }
    function get_single_mappednumber($parentid)
    {
        $qry_get_all_contact_number = "SELECT  stdcode, landline, mobile,virtualNumber,companyname,mobile_feedback,email_feedback FROM tbl_companymaster_generalinfo where parentid='".$parentid."'";
        //$res_get_all_contact_number = $this->conn_iro->query_sql($qry_get_all_contact_number);
        $res_get_all_contact_number = $this->execute_query($qry_get_all_contact_number,$this->conn_iro);
        if($res_get_all_contact_number && mysql_num_rows($res_get_all_contact_number)>0)
        {
            $row_get_all_contact_number = mysql_fetch_assoc($res_get_all_contact_number);
            $contract_stdcode= $row_get_all_contact_number['stdcode'];
            $this->contact_arr['landline'] = $row_get_all_contact_number['landline'];
            $this->contact_arr['mobile']    = $row_get_all_contact_number['mobile'];
            //echo "<br>".$this->contact_arr['tollfree'] = $row_get_all_contact_number['tollfree'];
            $this->virtualno            = $row_get_all_contact_number['virtualNumber'];
            $this->companyname          = $row_get_all_contact_number['companyname'];
            $this->mobile_feedback      = $row_get_all_contact_number['mobile_feedback'];
            $this->email_feedback       = $row_get_all_contact_number['email_feedback'];
            $this->stdcode              = $row_get_all_contact_number['stdcode'];
            $this->get_top_mappednumber_withoutstd();
            $this->get_top_mappednumber();
        }
        mysql_free_result($res_get_all_contact_number);
    }

    function get_additional_mapped_link($parentid)
    {
        $link_landline  = array();
        $link_mobile    = array();
        $link_tollfree  = array();
        $link_contact   = array();
        $qry_get_all_contact_number = "SELECT  stdcode, landline_display, mobile_display, tollfree FROM tbl_companymaster_generalinfo where parentid='".$parentid."'";
        //$res_get_all_contact_number = $this->conn_iro->query_sql($qry_get_all_contact_number);
        $res_get_all_contact_number = $this->execute_query($qry_get_all_contact_number,$this->conn_iro);
        if($res_get_all_contact_number && mysql_num_rows($res_get_all_contact_number)>0)
        {
            $row_get_all_contact_number = mysql_fetch_assoc($res_get_all_contact_number);
            $contract_stdcode= $row_get_all_contact_number['stdcode'];
            $link_landline  = $this->convert_contactnos_arr($row_get_all_contact_number['landline_display']);
            if(count($link_landline)>0)
            {
                $link_landline  = $this->concat_stdcode_zero($link_landline);
            }
            $link_mobile = $this->convert_contactnos_arr($row_get_all_contact_number['mobile_display']);
            if(count($link_mobile)>0)
            {
                $link_mobile  = $this->concat_stdcode_zero($link_mobile);
            }
            $link_tollfree = $this->convert_contactnos_arr($row_get_all_contact_number['tollfree']);
            $link_contact = array_merge($link_landline,$link_mobile,$link_tollfree); echo "line 766"; print_r($link_contact);
            $link_contact = array_filter($link_contact);
            $link_contact = array_merge($link_contact); echo "line 768---";print_r($link_contact);
            return $link_contact;
        }
        mysql_free_result($res_get_all_contact_number);
        return $link_contact;
    }

    function get_top_mappednumber_withoutstd()
    {
        foreach ($this->contacts_mode as $contact_mode)
        {
            switch($contact_mode)
            {
                case 'landline':$this->landline   =   $this->convert_contactnos_arr($this->contact_arr[$contact_mode]); 
                                break;
                case 'mobile'  :$this->mobile   =   $this->convert_contactnos_arr($this->contact_arr[$contact_mode]);
                                /*$this->mobile=$this->get_not_dnc_mobile($this->mobile);*//*remove this because allow DNC number as virtual mapeed number*/
                                break;
            }
        }
        $this->top_mappednumber_withoutstd= array_merge($this->landline,$this->mobile);
    }

    function get_mappednumber()
    {
        $get_next_mapped = array();
        $this->get_single_mappednumber($this->parentid);
        /*if($this->linkcontract_flag)
        {
            echo "</pre><br> line 770 first mapped number ".count($this->finalmappednumbers)."<pre>"; print_r($this->finalmappednumbers);
            if(count($this->finalmappednumbers)< self::TOTAL_REQUIRE_MAPPEDNO)
            {
                $this->get_eligible_linkcontract();
                echo "</pre>line 774 <br> eligible link contract--><pre>"; print_r($this->eligible_linkcontract);
                foreach($this->eligible_linkcontract as $linkparentid)
                {
                    if($this->parentid!=$linkparentid)
                    {
                        $get_next_mapped=$this->get_additional_mapped_link($linkparentid);
                        echo "<br>line 791"; print_r($get_next_mapped);
                        $this->finalmappednumbers = array_merge($this->finalmappednumbers,$get_next_mapped);
                        $this->finalmappednumbers = array_unique($this->finalmappednumbers);
                        if(count($this->finalmappednum) >= self::TOTAL_REQUIRE_MAPPEDNO)
                        {
                            $this->finalmappednumbers = array_slice($this->finalmappednumbers,0,self::TOTAL_REQUIRE_MAPPEDNO);
                            break;
                        }echo "line 798-->";print_r($this->finalmappednumbers);
                    }
                }
            }
        }*/
        if(count($this->finalmappednumbers)>0)
        {
            $this->mappednumber_flag = true;
        }
        if(!$this->mappednumber_flag)
        {
            $this->businessEligibility = false;
        }
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
    function get_top_mappednumber()
    {
        foreach ($this->contacts_mode as $contact_mode)
        {
            switch($contact_mode)
            {
                case 'landline':$this->landline   =   $this->convert_contactnos_arr($this->contact_arr[$contact_mode]); 
                                $landline_cnt = count($this->landline);
                                if($landline_cnt>0)
                                {
                                    $this->landline = $this->concat_stdcode_zero($this->landline);
                                }
                                break;
                case 'mobile'  :$this->mobile   =   $this->convert_contactnos_arr($this->contact_arr[$contact_mode]);
                                $mobile_cnt = count($this->mobile);
                                if($mobile_cnt>0)
                                {
                                    $this->mobile = $this->concat_stdcode_zero($this->mobile);
                                }
                                /*$this->mobile=$this->get_not_dnc_mobile($this->mobile);*//*remove this because allow DNC number as virtual mapeed number*/
                                break;
            }
        }
        
        /*$this->landline   =   $this->convert_contactnos_arr($landline_str);        
        $landline_cnt = count($this->landline);
        if($landline_cnt>0)
        {
            $this->landline = $this->concat_stdcode_zero($this->landline);
        }            

        $this->mobile   =   $this->convert_contactnos_arr($mobile_str);
        $mobile_cnt = count($this->mobile);
        if($mobile_cnt>0)
        {
            $this->mobile = $this->concat_stdcode_zero($this->mobile);
        }*/
        /*$this->mobile=$this->get_not_dnc_mobile($this->mobile);*//*remove this because allow DNC number as virtual mapeed number*/        
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
        $this->finalmappednumbers = $top_eight_array;
    }

    function is_expiry_contract($parentid)
    {
        $is_expiry_contract = false;
        if($parentid!='')
        {
            $qry_expire_date = "SELECT parentid, MIN(expired) AS exp_flag,MAX(DATE(expired_on))AS exp_date,max(end_date) as enddate FROM tbl_companymaster_campaigns GROUP BY parentid HAVING exp_flag=1 AND parentid='".$parentid."' and exp_date<='".date('Y-m-d', mktime(23, 59, 59, date('n')-6, date('j'), date('Y')))."' LIMIT 1";
            //$res_expire_date = $this->conn_finance->query_sql($qry_expire_date);
            $res_expire_date = $this->execute_query($qry_expire_date,$this->conn_finance);
            if(!$res_expire_date)
            {
                    die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_expire_date);
                    return $is_expiry_contract;
            }
            else
            {
                if(mysql_num_rows($res_expire_date)>0)
                {
                    $is_expiry_contract = true;
                }
                mysql_free_result($res_expire_date);
            }
        }
        return $is_expiry_contract;
    }

    function check_expiry()
    {
        $get_expired_flag='';
        if($this->linkcontract_flag)
        {
            $link_expr_contract_count=0;
            foreach($this->linkcontracts as $pids)
            {
                if(trim($pids)!='')
                {
                    $get_expired_flag = $this->is_expiry_contract(trim($pids));
                    if($get_expired_flag)
                    {
                        $link_expr_contract_count++;
                        $this->linkcontractsvalues[$pids]['expired']= true;
                    }
                    if($this->linkcontractsvalues[$pids]['expired'])
                    {
                        $this->linkcontractsvalues[$pids]['businessEligibility'] = false;
                    }
                }
            }
            if(count($this->linkcontracts)== $link_expr_contract_count)
            {
                $this->expired_flag = true;
            }
        }
        else
        {
            $get_expired_flag = $this->is_expiry_contract(trim($this->parentid));
            if($get_expired_flag)
            {
                $this->expired_flag = true;
            }
        }
    }
    
    function is_freeze_contract($parentid)
    {
        $is_freeze_contract = false;
        $this->is_freeze=false;
        $this->is_mask= false;
        if($parentid!='')
        {
            $qry_freez_flag = "SELECT parentid,freeze,mask FROM tbl_companymaster_extradetails WHERE parentid = '".$parentid."' AND (freeze = 1 OR mask=1)";
            //$res_free_flag = $this->conn_iro->query_sql($qry_freez_flag);
            $res_freez_flag = $this->execute_query($qry_freez_flag,$this->conn_iro);
            if(!$res_freez_flag)
            {
                    die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_freez_flag);
                    return $is_freeze_contract;                
            }
            else
            {
                if(mysql_num_rows($res_freez_flag)>0)
                {
                    $row_freez_flag = mysql_fetch_assoc($res_freez_flag);
                    if($row_freez_flag['freeze']==1)
                    {
                        $is_freeze_contract = true;
                        $this->is_freeze = true;
                    }
                    elseif($row_check_compfreeze['mask']==1)
                    {
                        $is_freeze_contract = true;
                        $this->is_mask = true;
                    }
                }
                mysql_free_result($res_freez_flag);
            }
        }
        return $is_freeze_contract;
    }

    function check_freeze()
    {
        $get_freezeflag='';
        if($this->linkcontract_flag)
        {
            $link_freez_contract_count = 0;
            foreach($this->linkcontracts as $pids)
            {
                $get_freezeflag = $this->is_freeze_contract($pids);
                if($get_freezeflag)
                {
                    if($this->is_freeze)
                    {
                        $run_time_freeze ='';
                        $run_time_freeze = $this->get_compfreeze_details($pids);
                        if($run_time_freeze)
                        {
                            $link_freez_contract_count++;
                            $this->linkcontractsvalues[$pids]['freeze']= true;
                        }
                        if($this->linkcontractsvalues[$pids]['freeze'])
                        {
                            $this->linkcontractsvalues[$pids]['businessEligibility'] = false;
                        }
                    }
                    else if($this->is_mask)
                    {
                        $run_time_mask= '';
                        $run_time_mask = $this->get_compmask_details($pids);
                        if($run_time_freeze)
                        {
                            $link_freez_contract_count++;
                            $this->linkcontractsvalues[$pids]['freeze']= true;
                        }
                        if($this->linkcontractsvalues[$pids]['freeze'])
                        {
                            $this->linkcontractsvalues[$pids]['businessEligibility'] = false;
                        }
                    }
                }
            }
            if(count($this->linkcontracts)== $link_freez_contract_count)
            {
                $this->freeze_flag = true;
            }
        }
        else
        {
            $get_freezeflag = $this->is_freeze_contract(trim($this->parentid));
            if($get_freezeflag)
            {
                if($this->is_freeze)
                {
                    $run_time_freeze= '';
                    $run_time_freeze = $this->get_compfreeze_details($this->parentid);
                    if($run_time_freeze)
                    {
                        $this->freeze_flag = true;
                    }
                }
                else if($this->is_mask)
                {
                    $run_time_mask = '';
                    $run_time_mask = $this->get_compmask_details($this->parentid);
                    if($run_time_freeze)
                    {
                        $this->freeze_flag = true;
                    }
                }
            }
        }
    }

    function update()
    {
        if($this->status=='A')
        {
            $this->allocate();
        }
        if($this->status == 'D')
        {
            $this->deallocarte();
        }
    }

    function allocate()
    {
        $this->techinfo_allocation();
        $this->genio_allocation();
    }
    
    function deallocate()
    {
        $this->techinfo_deallocation();
        $this->genio_deallocation();
    }

    function techinfo_allocation()
    {
        $this->get_techinfo_information(); 
        if($this->techInfo_array['BusinessId']!='')
        {
            if(trim($this->techInfo_array['BusinessId'])==trim($this->parentid))
            {
                $this->checkTechinfoNumber();
                if($this->change_flag==1)
                {
                    $this->status='A';
                    $this->update_techinfo();
                }
            }
            else
            {
				$extra_str="[techinfo businessid : ".$this->techInfo_array['BusinessId']."][given parentid :".$this->parentid."]";
                $this->logmsgvirtualno("Techinfo business id is not same as given parentid",$this->log_path,'Approval process',$this->parentid,$extra_str);
                if($this->linkcontract_flag)
                {
                    if(trim($this->techInfo_array['BusinessId'])== trim($this->mainrootcontractid) && trim($this->mainrootcontractid)!='')
                    {
						$extra_str="[techinfo businessid : ".$this->techInfo_array['BusinessId']."][main root parentid :".$this->mainrootcontractid."]";
						$this->logmsgvirtualno("Campare techinfo businessid with linkcontract main root parentid",$this->log_path,'Approval process',$this->parentid,$extra_str);
                        $this->checkTechinfoNumber();
                        if($this->change_flag==1)
                        {
                            $this->status='A';
                            $this->update_techinfo();
                        }
                    }
                    else
                    {
						$extra_str="[techinfo businessid : ".$this->techInfo_array['BusinessId']."][main root parentid :".$this->mainrootcontractid."]";
						$this->logmsgvirtualno("Techinfo businessid is not equle to  linkcontract main root parentid",$this->log_path,'Approval process',$this->parentid,$extra_str);
                        $this->setnextrootcontractid();
                        if(in_array(trim($this->techInfo_array['BusinessId']), $this->linkcontracts))
                        {
							$extra_str="[techinfo businessid : ".$this->techInfo_array['BusinessId']."][linkcontract array :".implode(",",$this->linkcontracts)."]";
							$this->logmsgvirtualno("Techinfo businessid is present in linkcontract array",$this->log_path,'Approval process',$this->parentid,$extra_str);
                            $this->checkTechinfoNumber(); 
                            if($this->change_flag==1)
                            {
                                $this->status='A';
                                $this->update_techinfo();
                            }
                        }
                        else
                        {
                            $this->invalid_virtualno[]= $this->virtualno;

							$extra_str="[techinfo businessid : ".$this->techInfo_array['BusinessId']."][linkcontract array :".implode(",",$this->linkcontracts)."][Invalid virtual number : ".implode(",",$this->invalid_virtualno)."]";
							$this->logmsgvirtualno("Techinfo businessid is not present in linkcontract array so check any other linkcontract have different virtual number",$this->log_path,'Approval process',$this->parentid,$extra_str);
                            
                            foreach($this->linkcontractsvalues as $parentdkey =>$parentvalue)
                            {  
                                if(trim($parentdkey)!=$this->parentid)
                                {
                                    if(trim($this->virtualno)!=trim($parentvalue['vno']) && trim($this->virtualno)!='' && trim($parentvalue['vno'])!='' && !in_array(trim($parentvalue['vno']),$this->invalid_virtualno))
                                    {
                                        $old_virtualno = $this->virtualno;
                                        $this->virtualno = $parentvalue['vno'];

                                        $extra_str="[Old virtual number : ".$old_virtualno."] [New virtual number :".$this->virtualno."][new virtual number taken from this parentid : ".$parentdkey."]";
							            $this->logmsgvirtualno("Get new virtual number from other link contract",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                        break;
                                    }
                                }
                            }
                            if(trim($old_virtualno)!=trim($this->virtualno) && trim($old_virtualno)=='')
                            {
                                $this->assign_newVno=true;
                                $get_arry_str_1 = array_map(create_function('$key, $value', 'return $key.":".$value[vno]." # ";'), array_keys($this->linkcontractsvalues), array_values($this->linkcontractsvalues));
                                $extra_str="[all link contract virtual number :".implode(",",$get_arry_str_1)."] [assign new virtual number flag :".$this->assign_newVno."]";
							    $this->logmsgvirtualno("All link contracts have same virtual number so assign new virtual number",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                $this->virtualno = '';
                                $this->status='A';
							    $this->update_techinfo();
                            }
                            elseif(trim($old_virtualno)!=trim($this->virtualno) && trim($old_virtualno)!='')
                            {
                                $get_arry_str_1 = array_map(create_function('$key, $value', 'return $key.":".$value[vno]." # ";'), array_keys($this->linkcontractsvalues), array_values($this->linkcontractsvalues));
                                $extra_str="[all link contract virtual number :".implode(",",$get_arry_str_1)."][Old virtual number : ".$old_virtualno."] [new virtual number : ".$this->virtualno."]";
							    $this->logmsgvirtualno("Other lick contract have different virtual number so check tht virtual number and assign to all link contract",$this->log_path,'Approval process',$this->parentid,$extra_str);

                                $this->status='A';
							    $this->update_techinfo();
                            }
						}
                    }
                }
                else
                {
                    /*this single contract and virtual number is assign to different parent so assign new virtual number*/
                    $this->invalid_virtualno[]= $this->virtualno;
                    $this->assign_newVno=true;
                    $extra_str="[techinfo businessid : ".$this->techInfo_array['BusinessId']."][given parentid :".$this->parentid."][link contract flag :".$this->linkcontract_flag."][assign new virtual number flag :".$this->assign_newVno."][Invalid virtual number : ".implode(",",$this->invalid_virtualno)."]";
					$this->logmsgvirtualno("Techinfo business id is not same as given parentid and given parentid is not a linkcontract so assign new virtual number",$this->log_path,'Approval process',$this->parentid,$extra_str);

					$this->virtualno = '';
                    $this->status='A';
					$this->update_techinfo();
                }
            }
        }
        else
        {
			$extra_str="[techinfo businessid : ".$this->techInfo_array['BusinessId']."][given parentid :".$this->parentid."]";
            $this->logmsgvirtualno("Techinfo business id is blank..",$this->log_path,'Approval process',$this->parentid,$extra_str);
            /*$this->status='A';
            $this->update_techinfo();*/
        }
        /*$this->status='A';
        $this->update_techinfo(); */
    }
    
    function genio_allocation()
    {    
        $this->update_compnaymaster_genralinfo(); 
        if($this->direct_update)
        {
            $this->update_companymaster_extradetails();
            $this->changes_done = true;
        }
        $this->update_company_master_search();
    }

    function techinfo_deallocation()
    {
        $this->get_techinfo_information(); 
        if($this->update_techinfo_flag)
        {
            $this->status='D';
            $this->update_techinfo();
        }
    }

    function genio_deallocation()
    {
        $this->virtualno='';
        if($this->linkcontract_flag)
        {
            if($this->linkcontractsvalues[$this->parentid]['freeze'] || $this->linkcontractsvalues[$this->parentid]['expired'])
            {
                if(trim($this->rootcontractid)!='')
                {
                    $this->virtualno = $this->linkcontractsvalues[$this->rootcontractid]['vno'];
                }                
            }
        }        
        $this->update_compnaymaster_genralinfo();
        if($this->direct_update)
        {
            $this->update_companymaster_extradetails();
            $this->changes_done = true;
        }
        $this->update_company_master_search();
    }

    function get_techinfo_information()
    {
        if($this->direct_update)
        {
            $this->update_techinfo_flag=false;
            if(intval($this->virtualno)>0)
            {
                $qry_get_businessid_techinfo = "SELECT vrnno, 1 FROM vrnmap WHERE vrnno='".trim($this->virtualno)."' AND vrnbusinessid = '".trim($this->parentid)."'";
                //$res_get_businessid_techinfo = $this->techinfo_conn->query_sql($qry_get_businessid_techinfo);
                $res_get_businessid_techinfo = $this->execute_query($qry_get_businessid_techinfo,$this->techinfo_conn);
                if(!$res_get_businessid_techinfo)
                {
                    die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_get_businessid_techinfo);
                    return false;
                }
                else
                {
                    if(mysql_num_rows($res_get_businessid_techinfo)>0)
                    {
                        $this->update_techinfo_flag=true;
                    }
                    else
                    {
                        $this->virtualno = 0;
                        if($this->linkcontract_flag)
                        {
                            $this->linkcontractsvalues[$this->parentid]['vno'] = 0;
                        }
                    }
                    mysql_free_result($res_get_businessid_techinfo);
                }            
            }
            if(!$this->update_techinfo_flag || intval($this->virtualno)==0)
            {
                $qry_get_businessid_techinfo    =   "SELECT vrnno, 1 FROM vrnmap WHERE vrnbusinessid = '".trim($this->parentid)."' AND VrnStatus ='A'";
                //$res_get_businessid_techinfo    =   $this->techinfo_conn->query_sql($qry_get_businessid_techinfo);
                $res_get_businessid_techinfo = $this->execute_query($qry_get_businessid_techinfo,$this->techinfo_conn);
                if(!$res_get_businessid_techinfo)
                {
                    die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_get_businessid_techinfo);
                    return false;                
                }
                else
                {
                    if(mysql_num_rows($res_get_businessid_techinfo)>0)
                    {
                        $row_get_businessid_techinfo    =  mysql_fetch_assoc($res_get_businessid_techinfo);
                        $this->update_techinfo_flag=true;
                        $this->virtualno = $row_get_businessid_techinfo['vrnno'];
                        if($this->linkcontract_flag)
                        {
                            $this->linkcontractsvalues[$this->parentid]['vno'] = $row_get_businessid_techinfo['vrnno'];
                        }
                    }
                    mysql_free_result($res_get_businessid_techinfo);
                }          
            }
            if($this->linkcontract_flag)
            {
                foreach($this->linkcontracts as $parentid)
                {
                    if($parentid!=$this->parentid)
                    {
                        if(intval($this->linkcontractsvalues[$parentid]['vno'])>0)
                        {
                            $qry_get_businessid_techinfo = "SELECT vrnno, 1 FROM vrnmap WHERE vrnno='".trim($this->linkcontractsvalues[$parentid]['vno'])."' AND vrnbusinessid = '".trim($parentid)."'";
                            //$res_get_businessid_techinfo = $this->techinfo_conn->query_sql($qry_get_businessid_techinfo);
                            $res_get_businessid_techinfo = $this->execute_query($qry_get_businessid_techinfo,$this->techinfo_conn);
                            if($res_get_businessid_techinfo && mysql_num_rows($res_get_businessid_techinfo)>0)
                            {
                                $this->linkcontractsvalues[$parentid]['tvno'] = $this->linkcontractsvalues[$parentid]['vno'];
                            }
                            mysql_free_result($res_get_businessid_techinfo);
                        }
                        if(intval($this->linkcontractsvalues[$parentid]['tvno'])==0)
                        {
                            $qry_get_businessid_techinfo    =   "SELECT vrnno, 1 FROM vrnmap WHERE vrnbusinessid = '".trim($parentid)."' AND VrnStatus ='A'";
                            //$res_get_businessid_techinfo    =   $this->techinfo_conn->query_sql($qry_get_businessid_techinfo);
                            $res_get_businessid_techinfo    = $this->execute_query($qry_get_businessid_techinfo,$this->techinfo_conn);
                            if($res_get_businessid_techinfo && mysql_num_rows($res_get_businessid_techinfo)>0)
                            {
                                $row_get_businessid_techinfo    =  mysql_fetch_assoc($res_get_businessid_techinfo);
                                $this->linkcontractsvalues[$parentid]['tvno'] = $row_get_businessid_techinfo['vrnno'];
                            }
                            mysql_free_result($res_get_businessid_techinfo);
                        }
                    }
                    else
                    {
                        $this->linkcontractsvalues[$this->parentid]['tvno'] = $this->virtualno;
                    }                
                }
            }            
        }
        else
        {
            if(intval($this->virtualno)!=0)
            {
                $this->url_counter=0;
                $curl_url = $this->techinfo_url."vrnsearch.php?VN=".trim($this->virtualno);
                $url_type='vnsearch';
                $curl_outout = $this->run_curl_url($curl_url,$url_type);
                if(!$this->curl_responce_flag)
                {
                    /*$logtbl_process_flag=1;
                    $logtbl_reason = "techinfo url not working";
                    $process_name = "check for given virtual number";
                    $this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);*/
                    $extra_str="[techinfo url not working][url :".$curl_url."]";
                    $this->logmsgvirtualno("check for given virtual number in techinfo",$this->log_path,'Approval process',$this->parentid,$extra_str);
                }
                else
                {
                    $this->techInfo_array = $this->techinfoArray($curl_outout); 
                }
            }
            else
            {
                $extra_str="[Virtual number :".$this->virtualno."]";
                $this->logmsgvirtualno("current virtual number is zero.",$this->log_path,'Approval process',$this->parentid,$extra_str);
                if($this->linkcontract_flag)
                {
                    foreach($this->linkcontracts as $pid)
                    {
                        if(trim($this->linkcontractsvalues[$pid]['vno'])!='' &&  intval(trim($this->linkcontractsvalues[$pid]['vno']))!=0)
                        {
                            $this->virtualno = trim($this->linkcontractsvalues[$pid]['vno']);
                            break;
                        }
                    }
                    $this->get_techinfo_information();
                }
                else
                {
                    //single contract and not having virtual number so assign new virrual number
                }
            }
        }
    }

    function setnextrootcontractid()
    {
        if(!$this->mappednumber_flag)
        {
            $this->rootcontractid = '';            
        }
        else
        {
            $found_rootid = false;
            $current_rootid = $this->rootcontractid; 
            $this->rootcontractid = '';
            /*foreach($this->eligible_linkcontract as $parentid)
            {
                if($found_rootid)
                {
                    $this->rootcontractid = $parentid; 
                    break;
                }
                if($parentid==$current_rootid)
                {
                    $found_rootid = true;
                }
            }*/
            foreach($this->linkcontracts as $parentid)
            {
                if($found_rootid)
                {
                    $this->rootcontractid = $parentid; 
                    break;
                }
                if($parentid==$current_rootid)
                {
                    $found_rootid = true;
                }
            }        
        }                
    }
    function update_techinfo()
    {
        if($this->direct_update)
        {
            if($this->status =='A')
            {
                $deallocate_vno = 0;
                $parentid = $this->parentid;            
                if($this->linkcontract_flag)
                {
                    $this->updatelinkednumbers();                        
                    if(trim($this->rootcontractid)!='' && trim($this->rootcontractid)!=$parentid)
                    {  
                        $parentid = $this->rootcontractid; /* assigning root parentid if contract is link contract */
                        if($this->virtualno!=$this->linkcontractsvalues[$this->rootcontractid]['vno'] && intval($this->linkcontractsvalues[$this->rootcontractid]['vno'])>0)
                        {                                
                            $deallocate_vno = $this->virtualno;
                            $this->virtualno = $this->linkcontractsvalues[$this->rootcontractid]['vno'];
                        }                                                    
                    }

                }
                if(intval($this->virtualno)>0)
                {
                    $qry_deallocate_tech_info = "UPDATE vrnmap SET VrnStatus='D', VrnBusinessId='', VrnModifiedTime=current_timestamp,  VrnModifiyByName='process' WHERE VrnNo != '" . trim($this->virtualno) . "' AND VrnBusinessId = '" . trim($parentid) . "';";
                    $res_deallocate_tech_info = $this->execute_query($qry_deallocate_tech_info, $this->techinfo_conn);  
                    if($this->linkcontract_flag)
                    {
                        if(trim($parentid)!=trim($this->mainrootcontractid) && intval(trim($this->mainrootcontractid)>0) && intval(trim($parentid))>0)
                        {
                            $updt_mainparent_techinfo = "Update vrnmap set VrnBusinessId = '".trim($this->mainrootcontractid)."' where VrnNo = '".trim($this->virtualno)."'";
                            //$res_updt_mainparent_techinfo = $this->techinfo_conn->query_sql($updt_mainparent_techinfo);
                            $res_updt_mainparent_techinfo = $this->execute_query($updt_mainparent_techinfo,$this->techinfo_conn);
                            $parentid = $this->mainrootcontractid;
                        }
                    }
                    $qry_update_techinfo = "UPDATE vrnmap SET VrnPhone1 = '" . trim($this->finalmappednumbers[0]) . "', VrnPhone2 = '" . trim($this->finalmappednumbers[1]) . "', VrnPhone3 = '". trim($this->finalmappednumbers[2]) . "', VrnPhone4 = '" . trim($this->finalmappednumbers[3]) . "', VrnPhone5='".trim($this->finalmappednumbers[4])."', VrnPhone6='".trim($this->finalmappednumbers[5])."', VrnPhone7='".trim($this->finalmappednumbers[6])."', VrnPhone8='".trim($this->finalmappednumbers[7])."',
                    VrnBusinessName=SUBSTR('".addslashes($this->companyname)."',1,50),VrnModifiedTime=current_timestamp, VrnModifiyByName='process', fb_mobile='".$this->mobile_feedback."',fb_email='".$this->email_feedback."' WHERE VrnNo ='".trim($this->virtualno)."' and VrnBusinessId ='".trim($parentid)."';";
                    //$res_update_techinfo = $this->techinfo_conn->query_sql($qry_update_techinfo);
                    $res_update_techinfo = $this->execute_query($qry_update_techinfo,$this->techinfo_conn);
                }
                else
                {            
                    $qry_get_virtualno="SELECT VrnNo, 1 FROM vrnmap WHERE length(ifnull(VrnBusinessId,'')) <= 0 ORDER BY VrnId LIMIT 1 FOR UPDATE";
                    $res_get_virtualno = $this->techinfo_conn->query_sql($qry_get_virtualno);
                    while($row = mysql_fetch_array($res_get_virtualno))
                    {
                        $this->virtualno = intval($row['VrnNo']);
                    }
                    if($this->virtualno != "")
                    {
                        $result10 = $this->techinfo_conn->query_sql("UPDATE vrnmap set VrnPhone1='".$this->finalmappednumbers[0]."', VrnPhone2='".$this->finalmappednumbers[1]."', VrnPhone3='".$this->finalmappednumbers[2]."', VrnPhone4='" . $this->finalmappednumbers[3] ."', VrnPhone5='".$this->finalmappednumbers[4]."', VrnPhone6='".$this->finalmappednumbers[5]."', VrnPhone7='".$this->finalmappednumbers[6]."', VrnPhone8='".$this->finalmappednumbers[7]."', VrnCreationTime=current_timestamp, VrnCreatedByName='process', VrnStatus='".$this->status."', VrnBusinessId='".trim($parentid)."', VrnBusinessName=SUBSTR('".addslashes($this->companyname)."',1,50), VrnModifiedTime=current_timestamp, VrnModifiyByName='process', fb_mobile='".$this->mobile_feedback."', fb_email='".$this->email_feedback."' where VrnNo='".$this->virtualno . "'");
                        
                        $count = mysql_affected_rows();
                        if($count > 0)
                        {
                            $this->techinfo_conn->query_sql("COMMIT") or die(mysql_error());
                            $qry_deallocate_tech_info = "UPDATE vrnmap SET VrnStatus='D', VrnBusinessId='', VrnModifiedTime=current_timestamp,  VrnModifiyByName='process' WHERE VrnNo != '" . trim($this->virtualno) . "' AND VrnBusinessId = '" . trim($parentid) . "';";
                            $res_deallocate_tech_info = $this->execute_query($qry_deallocate_tech_info, $this->techinfo_conn);
                        }
                        else 
                        {
                            $this->techinfo_conn->query_sql("ROLLBACK");
                            $logtbl_process_flag=0;
                            $logtbl_reason='Unable to allocate VN - '. $vrnno . ' hence it rollbacked';
                            $process_name='process run for dnc,hidden,blcok for virtual number';
                            $this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
                            //$strResult = "<Result><Code></Code><Text>Unable to allocate VN - " . $vrnno . "</Text></Result>";
                        }
                    }
                    else
                    {
                        $logtbl_process_flag=0;
                        $logtbl_reason='virtual number inventory over';
                        $process_name='process run for dnc,hidden,blcok for virtual number';
                        $this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);
                        /*insert into log*/
                    }                  
                }
                if(intval($deallocate_vno)>0)
                {
                    $allocated_vno = $this->virtualno;
                    $this->status = 'D';
                    $this->virtualno = $deallocate_vno;
                    $this->update_techinfo();
                    $this->virtualno = $allocated_vno;                    
                    $this->status = 'A';
                    unset($allocated_vno);
                }
                unset($deallocate_vno);
                unset($parentid);
            }
            else if($this->status=='D')
            {
                $deallocate_bid = $this->parentid;
                $deallocate_vno = $this->virtualno;
                $this->updatelinkednumbers();
                if(intval($deallocate_vno)>0)
                {
                    if($this->linkcontract_flag)
                    {
                        if(trim($this->rootcontractid)!='')
                        {
                            if($deallocate_bid==$this->rootcontractid && $this->linkcontractsvalues[$this->rootcontractid]['vno']==$deallocate_vno)
                                return;                        
                        }
                    }
                    $qry_deallocate_tech_info = "UPDATE vrnmap SET VrnStatus='D', VrnBusinessId='', VrnModifiedTime=current_timestamp,  VrnModifiyByName='process' WHERE VrnNo = '" . trim($deallocate_vno) . "' AND VrnBusinessId = '" . trim($deallocate_bid) . "';";
                    //$res_deallocate_tech_info = $this->techinfo_conn->query_sql($qry_deallocate_tech_info);
                    $res_deallocate_tech_info = $this->execute_query($qry_deallocate_tech_info,$this->techinfo_conn);
                    $logtbl_process_flag=1;
                    $logtbl_reason='virtual number' . $deallocate_vno . ' deallocated due to reason: ' . $this->reason ;
                    $process_name='process run for dnc,hidden,blcok for virtual number';
                    $temp_vno = $this->virtualno;
                    $temp_pid = $this->parentid;
                    $this->virtualno = $deallocate_vno;
                    $this->parentid = $deallocate_bid;
                    $this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);  
                    $this->virtualno = $temp_vno;
                    $this->parentid = $temp_pid;
                    unset($temp_vno);
                    unset($temp_pid);
                    unset($deallocate_bid);              
                    unset($deallocate_vno);              
                }
            }
        }
        else
        {
            if($this->status=='A')
            {
                $deallocate_vno = 0;
                $parentid = $this->parentid;       
                if($this->linkcontract_flag && $this->assign_newVno== false)
                {
                    $this->updatelinkednumbers();               
                    if(trim($this->rootcontractid)!='' && trim($this->rootcontractid)!=$parentid) /* true if child contract */
                    {
                        $parentid = $this->rootcontractid; /* assigning root parentid if contract is link contract */
                        if($this->virtualno!=$this->linkcontractsvalues[$this->rootcontractid]['vno'])
                        {                                
                            $deallocate_vno = $this->virtualno;
                            $this->virtualno = $this->linkcontractsvalues[$this->rootcontractid]['vno'];
                        }                                                   
                    }
                }
                if(intval($this->virtualno)>0)
                {
                    if($this->linkcontract_flag)
                    {
                        $key = array_search(trim($this->techInfo_array['BusinessId']), $this->linkcontracts);
                        $parentid = $this->linkcontracts[$key];
                    }
                    $curl_url = $this->techinfo_url."allocate.php?VN=".trim($this->virtualno)."&";
                    $url_type='allocation'; 
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
                    if(trim($fbemail_str)!='')
                    {
                        $fbemail=trim($fbemail_str);
                    }
                    $addition_info ="User=".trim($_SESSION['ucode'])."&BusinessId=".trim($parentid)."&Email=".trim($fbemail)."&Mobile=".$fb_mobile."&BusinessName=".trim($this->companyname);
                    $curl_url.= $curl_url.$landline_str.$addition_info; 
                    $curl_outout = $this->run_curl_url($curl_url,$url_type); 
                    if(!$this->curl_responce_flag)
                    {
                        /*$logtbl_process_flag=1;
                        $logtbl_reason = "techinfo url not working";
                        $process_name = "update new data for present virtual number";
                        $this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);*/
                        $extra_str="[techinfo url not working][url :".$curl_url."]";
                        $this->logmsgvirtualno("Allocate virtual number using techinfo",$this->log_path,'Approval process',$this->parentid,$extra_str);
                    }
                    else
                    {
                        $tectinfo_vrn = $this->readTechinfoXml($curl_outout);
                        $this->virtualno = $tectinfo_vrn; 
                    }
                }
                else
                {
                    $extra_str="[virtual no :".$this->virtualno."][not even link contract:".$this->linkcontract_flag." ][assign new virtual number flag :".$this->assign_newVno."]";
                    $this->logmsgvirtualno("allocate new virtual number contract not having virtual number",$this->log_path,'Approval process',$this->parentid,$extra_str);

                    $curl_url = $this->techinfo_url."allocate.php?";
                    $url_type='allocation';
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
                    if(trim($fbemail_str)!='')
                    {
                        $fbemail=trim($fbemail_str);
                    }
                    $addition_info ="User=".trim($_SESSION['ucode'])."&BusinessId=".trim($parentid)."&Email=".trim($fbemail)."&Mobile=".$fb_mobile."&BusinessName=".trim($this->companyname);
                    $curl_url = $curl_url.$landline_str.$addition_info; 
                    $curl_outout = $this->run_curl_url($curl_url,$url_type);
                    if(!$this->curl_responce_flag)
                    {
                        /*$logtbl_process_flag=1;
                        $logtbl_reason = "techinfo url not working";
                        $process_name = "allocate new virtual number contract not having virtual number";
                        $this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);*/
                        $extra_str="[techinfo url not working][url :".$curl_url."]";
                        $this->logmsgvirtualno("allocate new virtual number contract not having virtual number",$this->log_path,'Approval process',$this->parentid,$extra_str);
                    }
                    else
                    {
                        $tectinfo_vrn = $this->readTechinfoXml($curl_outout);
                        $this->virtualno = $tectinfo_vrn; 
                    }
                }
                if(intval($deallocate_vno)>0)
                {
                    $allocated_vno = $this->virtualno;
                    $this->status = 'D';
                    $this->virtualno = $deallocate_vno;
                    $this->update_techinfo();
                    $this->virtualno = $allocated_vno;                    
                    unset($allocated_vno);
                }
                unset($deallocate_vno);
                unset($parentid);
            }
            else if($this->status=='D')
            {
            }
        }
    }
    function updatelinkednumbers()
    {
        if($this->linkcontract_flag)
        {
            if(trim($this->rootcontractid)!='')
            {
                if($this->linkcontractsvalues[$this->rootcontractid]['vno']<=0)
                {
                    if($this->linkcontractsvalues[$this->rootcontractid]['tvno']>0)
                    {
                        $this->linkcontractsvalues[$this->rootcontractid]['vno'] = $this->linkcontractsvalues[$this->rootcontractid]['tvno'];
                    }
                    else
                    {
                        if($this->parentid==$this->rootcontractid)
                        {
                            if($this->virtualno>0)
                            {
                                $this->linkcontractsvalues[$this->rootcontractid]['vno'] = $this->virtualno;
                            } 
                            if($this->linkcontractsvalues[$this->rootcontractid]['vno']!=$this->linkcontractsvalues[$this->rootcontractid]['tvno'])
                            {
                                if($this->linkcontractsvalues[$this->rootcontractid]['tvno']>0)
                                {
                                    $this->linkcontractsvalues[$this->rootcontractid]['vno'] = $this->linkcontractsvalues[$this->rootcontractid]['tvno'];
                                }
                                elseif($this->virtualno>0)
                                {
                                    $this->linkcontractsvalues[$this->rootcontractid]['vno'] = $this->virtualno;
                                }                        
                            }                                                                   
                        }
                        else
                        {
                                $this->setnextrootcontractid();
                                $this->updatelinkednumbers();                              
                        }                                
                    }                        
                }                        
            }
            if(trim($this->rootcontractid)!='')
            {
                    if($this->linkcontractsvalues[$this->rootcontractid]['vno']!=$this->virtualno)
                    {
                        if(trim($this->reason)=='' && $this->virtualno > 0)
                        {
                            $this->reason = 'Different virtual number in links' ;
                        }                             
                    }
                    if($this->linkcontractsvalues[$this->rootcontractid]['vno']==0)
                    {
                        $this->setnextrootcontractid();
                        $this->updatelinkednumbers(); 
                    }                     
            }
        }    
    }
    function update_compnaymaster_genralinfo()
    {
        if(trim($this->virtualno)!='' && intval($this->virtualno)!=0)
        {
            $mappd_number=trim($this->finalmappednumbers[0]);
        }
        else
        {
            $mappd_number='';
        }
        if($this->linkcontract_flag)
        {
            $parent_str = implode("','",$this->linkcontracts);
        }
        else
        {
            $parent_str = trim($this->parentid);
        }
        if(trim($parent_str)!='')
        {
            $qry_update_tbl_companymaster="update tbl_companymaster_generalinfo set virtualnumber='".trim($this->virtualno)."', virtual_mapped_number='".$this->top_mappednumber_withoutstd[0]."' WHERE parentid in('".$parent_str."')"; 
            $res_update_tbl_companymaster = $this->conn_iro->query_sql($qry_update_tbl_companymaster,trim($this->parentid),true);

            $extra_str="[updated virtual number:".$this->virtualno."][updated virtual mapped number:".implode(",",$this->top_mappednumber_withoutstd)."][updated parentid:".$parent_str."][update Qry :".$qry_update_tbl_companymaster."][update qry result: ".$res_update_tbl_companymaster."]";
            $this->logmsgvirtualno("Update tbl companymaster ganeral info",$this->log_path,'Approval process',$this->parentid,$extra_str);
        }
    }

    function update_companymaster_extradetails()
    {
        $qry_update_general_extra="UPDATE tbl_companymaster_extradetails SET db_update = '" . date('Y-m-d') . "'  WHERE parentid='".trim($this->parentid)."'";
        $res_update_general_extra = $this->conn_iro->query_sql($qry_update_general_extra,trim($this->parentid),true);
    }

    function update_company_master_search()
    {
        $qry_sel_comp_srch="SELECT parentid, mobile_display, landline_display,tollfree_display,fax,virtualNumber,tollfree FROM tbl_companymaster_generalinfo WHERE parentid ='".$this->parentid."'";
        $res_sel_comp_srch = $this->conn_iro->query_sql($qry_sel_comp_srch);
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
                $update_tbl_compsrch = "UPDATE tbl_companymaster_search SET phone_search = '".$new_phone_search."' WHERE parentid = '".$this->parentid."'";
                $res_update_tbl_compsrch = $this->conn_iro->query_sql($update_tbl_compsrch,$this->parentid,true);
            }
        }
        unset($row_sel_comp_srch);
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
        $this->logmsgvirtualno("Insert into log Table if process is failed.",$this->log_path,'Approval process',$this->parentid,$extra_str);
    }

    function get_linked_contracts($parentid='')
    {
        $owned_parentid     =  false;
        $added              =  false;
        $sorted_linked_contracts = array();
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
                            $this->get_linked_contracts($row_get_linked_contract_flag['parentid']);
                        }
                        elseif($parentid!=$row_get_linked_contract_flag['scheme_parentid'])
                        {
                            $this->get_linked_contracts($row_get_linked_contract_flag['scheme_parentid']);
                        }
                    }            
                }
            }
            if(!$added)
            {
                if(count($this->linkcontracts)>0)
                {                
                    $update_sort_sql =  "SELECT parentid, scheme_parentid FROM tbl_company_refer WHERE parentid IN ('". implode("','", $this->linkcontracts) ."') OR scheme_parentid IN ('" . implode("','", $this->linkcontracts) . "') ORDER BY creationDt";
                    //$resupdate_sort_sql    =   $this->conn_decs->query_sql($update_sort_sql);
                    $resupdate_sort_sql = $this->execute_query($update_sort_sql,$this->conn_decs);
                    $this->latestcontractid = '';
                    if($resupdate_sort_sql && mysql_num_rows($resupdate_sort_sql)>0)
                    {
                        $count=1;
                        //$res_get_linked_contract_flag   =   $this->conn_decs->query_sql($qry_get_linked_contract_flag);
                        //$res_get_linked_contract_flag   = $this->execute_query($qry_get_linked_contract_flag,$this->conn_decs);
                        while($row_get_linked_contract_flag = mysql_fetch_array($resupdate_sort_sql))
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
                                $sorted_linked_contracts[] = $row_get_linked_contract_flag['parentid'];
                            }
                            if($this->linkcontractsvalues[$row_get_linked_contract_flag['scheme_parentid']]['sort']==0)
                            {
                                if($count==1)
                                {
                                    $this->rootcontractid = $row_get_linked_contract_flag['scheme_parentid'];
                                    $this->mainrootcontractid = $row_get_linked_contract_flag['scheme_parentid'];
                                }
                                $this->linkcontractsvalues[$row_get_linked_contract_flag['scheme_parentid']]['sort'] = $count++;
                                $this->latestcontractid = $row_get_linked_contract_flag['scheme_parentid'];
                                $sorted_linked_contracts[] = $row_get_linked_contract_flag['scheme_parentid'];
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
            mysql_free_result($res_get_linked_contract_flag);
            unset($vrno);
            unset($count);                                  
        }
        $get_arry_str_1 = '';
        $get_arry_str_1 = array_map(create_function('$key, $value', 'return $key.":".$value[0]." # ";'), array_keys($this->linkcontractsvalues), array_values($this->linkcontractsvalues));
       
        $extra_str="[Link contract flag : ".$this->linkcontract_flag."][ total link contract : ".count($this->linkcontracts)."][link contracts : ".implode(",",$this->linkcontracts)."][link contract value : ".implode(",",$get_arry_str_1)."][Main link contract : ".$this->rootcontractid."]";
        $this->logmsgvirtualno("Get link contract information.",$this->log_path,'Approval process',$this->parentid,$extra_str);
        unset($owned_parentid);
        unset($added);   
        unset($parentid); 
        unset($sorted_linked_contracts);        
    }

    function get_compfreeze_details($parentid='')
    {
        if(trim($parentid)!='')
        {
            $freez =false;
            $qry_check_compfreeze="SELECT parentid,freez,date_time FROM tbl_compfreez_details WHERE parentid='".$parentid."' ORDER BY date_time DESC LIMIT 1";
            //$res_check_compfreeze = $this->conn_decs->query_sql($qry_check_compfreeze);
            $res_check_compfreeze = $this->execute_query($qry_check_compfreeze,$this->conn_decs);
            if(!$res_check_compfreeze)
            {
                die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_check_compfreeze);
                return false;
            }
            else
            {
                if(mysql_num_rows($res_check_compfreeze)>0)
                {
                    $row_check_compfreeze = mysql_fetch_assoc($res_check_compfreeze);
                    if($row_check_compfreeze['freez']==1)
                    {
                        if(strtotime($row_check_compfreeze['date_time'])<=strtotime(date('Y-m-d', mktime(23, 59, 59, date('n')-6, date('j'), date('Y')))))
                        {
                            $freez = true;
                        }
                    }
                }
            mysql_free_result($res_check_compfreeze);
            }
            unset($row_check_compfreeze);
        }
        return $freez;
    }

    function get_compmask_details($parentid='')
    {
        if(trim($parentid)!='')
        {
            $mask = false;
            $qry_check_compmask="SELECT parentid,mask,date_time FROM tbl_compMask_details WHERE parentid='".$parentid."' ORDER BY date_time DESC LIMIT 1";
            //$res_check_compmask = $this->conn_decs->query_sql($qry_check_compmask);
            $res_check_compmask = $this->execute_query($qry_check_compmask,$this->conn_decs);
            if(!$res_check_compmask)
            {
                die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_check_compmask);
                return false;
            }
            else
            {
                if(mysql_num_rows($res_check_compmask)>0)
                {
                    $row_check_compmask = mysql_fetch_assoc($res_check_compmask);
                    if($row_check_compmask['mask']==1)
                    {
                        if(strtotime($res_check_compmask['date_time'])<=strtotime(date('Y-m-d', mktime(23, 59, 59, date('n')-6, date('j'), date('Y')))))
                        {
                            $mask = true;
                        }
                    }
                }
                mysql_free_result($res_check_compmask);
            }
            unset ($row_check_compmask);
        }
        return $mask;
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
    
    function get_stdcode()
    {
        $qry_get_stdcode="SELECT stdcode FROM d_jds.tbl_area_master WHERE pincode='".trim($this->pincode)."' AND area='".trim($this->area)."'";
        //$res_get_stdcode = $this->conn_decs->query_sql($qry_get_stdcode);
        $res_get_stdcode = $this->execute_query($qry_get_stdcode,$this->conn_decs);
        if($res_get_stdcode && mysql_num_rows($res_get_stdcode)>0)
        {
            $row_get_stdcode = mysql_fetch_assoc($res_get_stdcode);
            $new_stdcode = $row_get_stdcode['stdcode'];
        }
        return $new_stdcode;
    }

    function run_curl_url($curl_url,$url_type)
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
        $this->logmsgvirtualno("Techinfo url .",$this->log_path,'Approval process',$this->parentid,$extra_str);
        return $output;
    }

    function techinfoArray($curl_outout)
    {
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
            $this->logmsgvirtualno("Get all inforamtion from Techinfo URL successfully",$this->log_path,'Approval process',$this->parentid,$extra_str);
            return $this->techinfo_array;
        }
        else
        {
            /*log msg*/
            /*$logtbl_process_flag=1;
            $logtbl_reason = "techinfo xml not upload proper";
            $process_name = "check virtual number in normal dealclose process";
            $this->insert_into_log_tbl($logtbl_process_flag,$logtbl_reason,$process_name);*/

            $extra_str="[URL return code : ".$Code."][ URL return Text : ".$text."][insert into logtable]";
            $this->logmsgvirtualno("Get all inforamtion from Techinfo URL is failed",$this->log_path,'Approval process',$this->parentid,$extra_str);
            return $this->techinfo_array;
        }
    }

    function checkTechinfoNumber()
    {
        if(is_array($this->techInfo_array) && count($this->techInfo_array)>0)
        {
            if($this->linkcontract_flag)
            {
                /*if(trim($this->mainrootcontractid)==trim($this->techinfo_array['BusinessId']))
                {
                    $this->change_flag = $this->check_mapped_number($this->finalmappednumbers,$this->techInfo_array);
                }
                else if(trim($this->rootcontractid)==trim($this->techinfo_array['BusinessId']))
                {
                    $this->change_flag = $this->check_mapped_number($this->finalmappednumbers,$this->techInfo_array);
                }*/
                $this->change_flag = $this->check_mapped_number($this->finalmappednumbers,$this->techInfo_array);
                if($this->change_flag==0)
                {
                    if((trim($this->mobile_feedback)!=trim($this->techinfo_array['Mobile'])) || (trim($this->email_feedback)!=trim($this->techinfo_array['Email'])))
                    {
                        $this->change_flag=1;
                    }
                }
                $extra_str="[techinfo return array : ".implode(",",$this->techInfo_array)."][change flag :".$this->change_flag."]";
                $this->logmsgvirtualno("Check techinfo number for link contract.",$this->log_path,'Approval process',$this->parentid,$extra_str);
            }
            else
            {
                if(trim($this->parentid)==trim($this->techinfo_array['BusinessId']))
                {
                    $this->change_flag = $this->check_mapped_number($this->finalmappednumbers,$this->techInfo_array);
                }
                if($this->change_flag==0)
                {
                    if((trim($this->mobile_feedback)!=trim($this->techinfo_array['Mobile'])) || (trim($this->email_feedback)!=trim($this->techinfo_array['Email'])))
                    {
                        $this->change_flag=1;
                    }
                }
                $extra_str="[techinfo return array : ".implode(",",$this->techInfo_array)."][change flag :".$this->change_flag."]";
                $this->logmsgvirtualno("Check techinfo number for single contract.",$this->log_path,'Approval process',$this->parentid,$extra_str);
            }
        }
        else
        {
            /*techinfo array is null so log this....*/
            $extra_str="[techinfo return array : ".implode(",",$this->techInfo_array)."]";
            $this->logmsgvirtualno("Techinfo url return null array in check techinfo mapped number",$this->log_path,'Approval process',$this->parentid,$extra_str);
        }
        return $this->change_flag;
    }

    function check_mapped_number($genio_top_mapp,$techinfo_top_mapp)
    {
        $this->change_flag=0;
        for($j=0;$j<self::TOTAL_TECHINFO_MAPPENO;$j++)
        {
            if($genio_top_mapp[$j]!=trim($techinfo_top_mapp['Ph'.($j+1)]))
            {
                $change_flag=1;
                break;
            }
        }
        return $this->change_flag;
    }

    function readTechinfoXml($curl_outout)
    {
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
            if($Code==0 && strlen($Code)>0)
            {
                /*log msg here */
                $extra_str="[URL return code : ".$Code."][ URL return Text : ".$text."][Virrtual number : ".$vn."][already assign virtual number :".$this->virtualno."]";
                $this->logmsgvirtualno("Get all information from allocate virtual number Techinfo URL is successfully",$this->log_path,'Approval process',$this->parentid,$extra_str);
                return $vn;
            }
            else
            {
                /*log msg here*/
                $extra_str="[URL return code : ".$Code."][ URL return Text : ".$text."][Virrtual number : ".$vn."][already assign virtual number :".$this->virtualno."]";
                $this->logmsgvirtualno("Get all inforamtion from allocate virtual number Techinfo URL is failed",$this->log_path,'Approval process',$this->parentid,$extra_str);
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
                $this->logmsgvirtualno("Get all inforamtion from deallocate virtual number Techinfo URL is successfully",$this->log_path,'Approval process',$this->parentid,$extra_str);
                return $vn;
            }
            else
            {
                /*log msg here */
                $extra_str="[URL return code : ".$Code."][ URL return Text : ".$text."][Virrtual number : ".$vn."][already assign virtual number :".$this->virtualno."]";
                $this->logmsgvirtualno("Get all inforamtion from deallocate virtual number Techinfo URL is failed",$this->log_path,'Approval process',$this->parentid,$extra_str);
                return $vn;
            }
        }
    }

    function insert_into_quarantine_table()
    {
        $qry_insert_quarantine_tbl = "INSERT INTO tbl_quarantine_virtualnumber SET vno = '".(int)$this->virtualno."', businessid ='".trim($this->techinfo_array['BusinessId'])."', start_date ='".date('Y-m-d H:i:s')."', active_flag = 1";
        $res_insert_quarantine_tbl = $this->execute_query($qry_insert_quarantine_tbl,$this->conn_decs);
        unset($qry_updt_newvalue_process_vrnmap);
    }

    function check_all_linkcontract_expr()
    {
        foreach($this->linkcontracts as $pids)
        {
            $link_expr_contract_count=0;
            $qry_min_expr = "SELECT parentid, MIN(expired) AS exp_flag,MAX(DATE(expired_on))AS exp_date,max(end_date) as enddate FROM tbl_companymaster_campaigns GROUP BY parentid HAVING exp_flag=1 AND parentid='".$pids."' and exp_date<='".date('Y-m-d', mktime(23, 59, 59, date('n')-6, date('j'), date('Y')))."' LIMIT 1";
            $res_min_expr = $this->conn_finance->query_sql($qry_min_expr);
            if(!$res_min_expr)
            {
                    die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_min_expr);
                    return false;        
            }
            else
            {
                if(mysql_num_rows($res_min_expr)>0)
                {
                    $link_expr_contract_count++;
                }
            }
        }
        if(count($this->linkcontracts)== $link_expr_contract_count)
        {
            $this->all_expr_link = true;
        }
    }

    function update_quarantine_companymaster()
    {
        $qry_update_quarantine_companymaster = "UPDATE tbl_companymaster_generalinfo SET virtualnumber='', virtual_mapped_number='' WHERE parentid='".trim($this->parentid)."'";
        $res_update_quarantine_companymaster = $this->conn_iro->query_sql($qry_update_quarantine_companymaster,trim($this->parentid),true);
    }

    function quarantine_eligibility()
    {
        $qry_sel_quarantine_tbl = "select * from tbl_quarantine_virtualnumber where vno = '".(int)$this->virtualno."' and start_date!='' and end_date!='' and active_flag = 1";
        $res_sel_quarantine_tbl = $this->conn_decs->query_sql($qry_sel_quarantine_tbl);
        if(!$res_sel_quarantine_tbl)
        {
            die("\n<br/>Got mysql error(" . mysql_error() . ") while executing query: " . $qry_hidden_contract);
            return false;
        }
        else
        {
            if(mysql_num_rows($res_sel_quarantine_tbl)<=0)
            {
                $this->quarantineEligibility = false;
            }
        }
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
        if (isset($_SESSION['ucode']) || isset($_SESSION['mktgEmpCode'])) {
            $userID = isset($_SESSION['ucode']) ? $_SESSION['ucode'] : $_SESSION['mktgEmpCode']; //  Switches between TME_Live Session ID and DATAENTRY Session ID
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
/* Called on getContractData.php to delete entries whose dealclose is not done(contracts left incomplete after intermediate page) */
	function deleteIncomplete_VN_Reason($parentid)
	{
		$sql = " DELETE FROM tbl_block_VN_reason WHERE parentid = '".$parentid."' and IsDealClosed = -1 ";
		$res = $this->conn_decs->query_sql($sql);
		if($res)
			return true;
		else
			return false;
	}
/* Called from intermediate page - to Insert reason for blocking VN NOTE [block_flag will be set to 1 or 0 on Deal Close] */
	function InsertVN_reason($parentid, $reason, $block_flag, $userid)
	{
		if($parentid) {
			$sql =" INSERT INTO tbl_block_VN_reason
					SET
						parentid	= '".$parentid."',
						reason		= '".$reason."',
						userid		= '".$userid."',
						block_flag	= '".$block_flag."',
						IsDealClosed= -1,
						data_time	= '".$this->insertion_time."'	";

			$res = $this->conn_decs->query_sql($sql);
			if($res) 
				return true;
		}
		return false;
	}
/* Called from dealclose - if block for VN is changed it is updated in generalinfo and Isdealclosed is set to 1 */	
	function UPDATE_VN_Reason_Flag_Dealclose($parentid)
	{
		$block_VN_Reason	= $this->Sel_block_VN_Reason($parentid);
		$block_VN_GenInfo	= $this->Sel_GenInfo_VN_blockFlag($parentid);
		if($block_VN_Reason && $block_VN_GenInfo!='') {
			$block_VN_Reason = explode(',', $block_VN_Reason);
			$IsDealClosed	= $block_VN_Reason[0];
			$block_flag		= $block_VN_Reason[1];
			if($block_flag !== $block_VN_GenInfo) {
				$updQry = " Update tbl_companymaster_generalinfo SET blockforvirtual = '".$block_flag."' WHERE parentid = '".$parentid."' ";
				$res	=	$this->conn_iro->query_sql($updQry);
				unset($res);unset($updQry);
				$upd_VN_reason_Table = " UPDATE tbl_block_VN_reason SET IsDealClosed =1 WHERE parentid = '".$parentid."' ";
				$res_VN_reason_Table = $this->conn_decs->query_sql($upd_VN_reason_Table);
				unset($upd_VN_reason_Table);unset($res_VN_reason_Table);
			}
		}
	}
		
	function Sel_GenInfo_VN_blockFlag($parentid)
	{
		$sql = " SELECT blockforvirtual FROM tbl_companymaster_generalinfo WHERE parentid = '".$parentid."' ";
		$res = $this->conn_iro->query_sql($sql);
		if($res) {
			if(mysql_num_rows($res) >0) {
				$row = mysql_fetch_assoc($res);
				return $row['blockforvirtual'];
			}
		}
		return false;
	}

	function Sel_block_VN_Reason($parentid)
	{
		$sql = " SELECT IsDealClosed, block_flag FROM tbl_block_VN_reason WHERE parentid = '".$parentid."' and IsDealClosed =-1";
		$res = $this->conn_decs->query_sql($sql);
		if($res && mysql_num_rows($res) >0) {
			$row = mysql_fetch_assoc($res);
			return $row['IsDealClosed'].','.$row['block_flag'] ;
		}
		return false;
	}
}
?>
