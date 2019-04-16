<?php

class Budget_service_class extends DB {

    var $params         = null;
    var $dataservers    = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

    function __construct($params) {
        if($params['act']==='add'):
            $this->company_nm = trim(addslashes(stripcslashes($params['company'])));
            $this->module     = trim($params['module']);
            $this->actual_amt = trim($params['a_amt']);
            $this->propos_amt = trim($params['p_amt']);
            $this->cust_nm    = trim(addslashes(stripcslashes($params['c_name'])));
            $this->cust_id    = trim($params['c_id']);
            $this->rqst_type  = trim($params['typ']);
            $this->ecs        = trim($params['ecs']);
            $this->setupfree        = trim($params['setupfree']);
            $this->setUpFeeVal        = trim($params['setUpFeeVal']);
            $this->downsel_type    = trim($params['downsel_type']);
            $this->ptenure        = trim($params['ptenure']);
            elseif ($params['act']==='check'):
                $this->source   = $params['module'];
            elseif ($params['act']==='addfcv'):
                $this->company_nm   = addslashes(stripcslashes($params['company']));
                $this->module       = $params['module'];
                $this->cust_id      = $params['c_id'];
                $this->cust_nm      = $params['c_name'];
                $this->fcv_amt      = $params['amt'];
                $this->reason       = $params['reason'];
            elseif ($params['act']==='checkfcv'):
                $this->source   = $params['module'];
            elseif ($params['act']==='dsview'):
                $this->status     = $params['status'];
                $this->custid     = $params['custid'];
                $this->source     = $params['module'];
            elseif ($params['act']==='dspaginate'):
                // function -> downsellPaginate
                $this->status     = $params['status'];
                $this->custid     = $params['custid'];
                $this->source     = $params['module'];
                $this->pageShow   = $params['pageShow'];
                $this->pageLimit  = $params['limit'];
            elseif ($params['act']==='updsapi'):
                $this->updateId = $params['upid'];
            elseif ($params['act']==='refund'):
                $this->company_nm = trim(addslashes(stripcslashes($params['company'])));
                $this->module     = trim($params['module']);
                $this->amtandst   = trim($params['amtandst']);
                $this->amtnost    = trim($params['amtnost']);
                $this->cust_nm    = trim(addslashes(stripcslashes($params['c_name'])));
                $this->cust_id    = trim($params['c_id']);
                $this->st         = trim($params['st']);
                $this->jdpool     = trim($params['jdpool']);
                $this->orig_1     = trim($params['orig_1']);
                $this->xfer_1     = trim($params['xfer_1']);
                $this->enddate_1  = trim($params['enddate_1']);
                $this->enddate_0  = trim($params['enddate_0']);
                $this->comments   = trim($params['comments']);
                $this->exsreson   = trim($params['exsreson']);
                $this->reason     = trim(addslashes(stripcslashes($params['reason'])));
            elseif ($params['act']==='logs'):
                $this->source   = trim($params['source']);
                $this->api      = trim($params['api']);
                $this->param    = trim($params['param']);
                $this->response = trim($params['response']);
            
        endif;

        $this->parentid  	= trim($params['p_id']);
        $this->version    	= trim($params['vrsn']);
        $this->data_city  	= trim($params['data_city']);
        $this->sso_ip     	= trim($params['sso_ip']);
		$this->rowid      	= trim($params['rowid']);
        $this->setServers();
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
     * Function to set DB connection objects
     */
    function setServers() {
        global $db;
        $conn_city = ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
        $this->conn_iro     = $db[$conn_city]['iro']['master'];             // 67.213 - db_iro
        $this->conn_tme_jds = $db[$conn_city]['tme_jds']['master'];         // 67.213 - tme_jds
        $this->conn_local   = $db[$conn_city]['d_jds']['master'];           // 67.213 - d_jds
        $this->conn_idc     = $db[$conn_city]['idc']['master'];             // 6.52 online_regis_xx
        $this->conn_fnc     = $db[$conn_city]['fin']['master'];             // 67.215 db_finance
        $this->conn_budget  = $db[$conn_city]['db_budgeting']['master'];    // 67.215 db_budget
        //~ if(strtolower($this->module)	==	'me'){
			//~ $this->conn_idc     = $db[$conn_city]['idc']['master'];
		//~ }else{
			//~ $this->conn_idc     = $db[$conn_city]['tme_jds']['master'];
		//~ }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
     * to fetch the actual employee city
     */
    function getEmpCity($empid) {
        $url="http://".$this->sso_ip."/hrmodule/employee/fetch_employee_info/$empid";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resultString = curl_exec($ch);
        $data=  json_decode($resultString,TRUE);
        curl_close($ch);
        return $data['data']['city'];
    }
    
    function sendNotification($sendNotArr) {
		$query				=	"SELECT ucode FROM online_regis.budget_approval_users WHERE active_flag=1 AND approval_flag=1 AND myjd=1"; 
        $stmt				=	parent::execQuery($query,$this->conn_idc);
        $row    			=	parent::numRows($stmt); 
        $user_access		=	array();
        if($row	>	0){
			while ($row_temp = parent::fetchData($stmt)) {
				$user_access[]	=	$row_temp['ucode'];
			}
		}
		$resultArr=	array();
		$resultArr['users'] = $user_access;
		foreach($user_access as $key=>$val){
			$curlParams 					=  array();
			$authToken  					=  "ZedA76A%'>j0~'z]&w7bR64{s";
			$parentid						=	$sendNotArr['parentid'];			
			$not_id 						=	intval(10);
			$not_msg						=  "<a href='http://accounts.justdial.com/hr_lite/#/ShowDownselNotData/".$sendNotArr['parentid']."/".$sendNotArr['version']."'>Downsell Request for : ".ucfirst($sendNotArr['company_nm']).".Kindly Approve/reject.</a>";
			$stripnot_msg 					=	strip_tags($not_msg);
			$curlParams['url'] 				=  "http://192.168.20.237:8080/api/sendNotification.php?auth_token=".urlencode($authToken)."&not_id=".$not_id."&empcode=".$val."&not_msg=".urlencode($stripnot_msg)."&txtmsg=".urlencode($not_msg);
			$curlParams['formate'] 			=  'basic';
			$curlParams['method'] 			=  'POST';
			$params['txtmsg']				=	$not_msg;
			$curlParams['postData'] 		=  json_encode($params);
			$curlParams['headerJson'] 		=  'json';
			$singleCheck					=  $this->curlCall($curlParams);	
			$resultArr[$val] 				=  json_decode($singleCheck,1);
		}
		return json_encode($resultArr);
    }
    
    function curlCall($param) {
        $retVal = '';
        $method = ((isset($param['method'])) && ($param['method'] != "")) ? strtolower($param['method']) : "get";
        $formate = ((isset($param['formate'])) && ($param['formate'] != "")) ? strtolower($param['formate']) : "array";

        # Init Curl Call #
        $ch = curl_init();
        # Set Options #
        curl_setopt($ch, CURLOPT_URL, $param['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param['postData']);
        }
        
        if(isset($param['headerJson']) && $param['headerJson'] != '')  {
			if($param['headerJson']	==	'json') {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
					'Content-Type: application/json',                                                                                
					'Content-Length: ' . strlen($param['postData']))                                                                       
				); 
			} else if($param['headerJson']	==	'array') {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-type: multipart/form-data'
				));
			} else if($param['headerJson']	==	'withHead') {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-type: multipart/form-data',
					'HR-API-AUTH-TOKEN:'.$param['authToken']
				));
			}
		}
		// echo '--retVCal=params==<pre>';print_r($param);
        $retVal = curl_exec($ch);
        // echo '--retVCal===<pre>';print_r($retVal);
        curl_close($ch);
        unset($method);
        if ($formate == "array") {
            return json_decode($retVal, TRUE);
        } else {
            return $retVal;
        }
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
    * function to add new downsell request
    */
   function request() {
        $now        = date('Y-m-d H:i:s');
        $status     = 0;
        $empCity    = $this->getEmpCity($this->cust_id);
        if ($this->cust_id === '' || $this->cust_nm === '' || $this->actual_amt === '' || $this->module === '' || $this->company_nm === '' || $this->parentid==='' || $this->version==='' || $this->rqst_type==='' || $this->ecs==='' || $this->downsel_type == ''):
            $result = json_encode(array('error' => 1, 'msg' => 'All parameters are required'));
        else:
            try {
                if($this->rqst_type!='Proposal'):
                    $dealclose=1;
                    else:
                    $dealclose=0;
                endif;
                $setUpFeeVal	=	$this->setUpFeeVal;
                if($setUpFeeVal!='' && $setUpFeeVal!= null){
					$this->actual_amt	=	($this->actual_amt-$setUpFeeVal);
					$this->propos_amt	=	($this->propos_amt-$setUpFeeVal);
					$disc_per			=	number_format((($this->actual_amt-$this->propos_amt)/$this->actual_amt)*100,4);
					$this->actual_amt	=	($this->actual_amt+$setUpFeeVal);
					$this->propos_amt	=	($this->propos_amt+$setUpFeeVal);
				}else{
					$disc_per	=	number_format((($this->actual_amt-$this->propos_amt)/$this->actual_amt)*100,4);
				}
                if (parent::execQuery("INSERT INTO online_regis.downsell_trn (company_nm, source, cust_id, cust_name, actual_amt, proposed_amt, status, created_at, parentid, version, request_type, data_city, dealclose_flag, ecs_flag, employee_city,setUpfree,setUpfreeVal,downsel_type,proposed_tenure) VALUES ('$this->company_nm', '$this->module', '$this->cust_id', '$this->cust_nm', '$this->actual_amt', '$this->propos_amt', '$status', '$now', '$this->parentid', '$this->version', '$this->rqst_type', '$this->data_city','$dealclose', '$this->ecs', '$empCity','$this->setupfree','$this->setUpFeeVal','$this->downsel_type','$this->ptenure')",$this->conn_idc)):
					$sendNotArr					=	array();
					$sendNotArr['parentid']		=	$this->parentid;
					$sendNotArr['version']		=	$this->version;
					$sendNotArr['status']		=	$status;
					$sendNotArr['cust_id']		=	$this->cust_id;
					$sendNotArr['cust_nm']		=	$this->cust_nm;
					$sendNotArr['actual_amt']	=	$this->actual_amt;
					$sendNotArr['propos_amt']	=	$this->propos_amt;
					$sendNotArr['company_nm']	=	$this->company_nm;
                    $notifyflag					=	json_decode($this->sendNotification($sendNotArr),1);
                    $result = json_encode(array('error' => 0, 'msg' => 'Success','notification' => $notifyflag));
                    $this->dumpData();
                    if($this->module=='ME'):
                    $this->updateCampaignId($this->parentid,$this->version,$this->conn_idc,$disc_per);
					else:
                        if($this->module=='TME'): 
                            $this->updateCampaignIdTme($this->parentid,$this->version,$disc_per,$this->conn_tme_jds,$this->conn_idc);
                        endif;
                    endif;
                else:
                    $result = json_encode(array('error' => 3, 'msg' => 'Sever error: unable to insert new record.'));
                endif;
            } catch (Exception $exc) {
                $result = json_encode(array('error' => 2, 'msg' => $exc->getTraceAsString()));
            }
        endif;
        return $result;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
    * function to add campaign id's to the downsell request
    */
    function updateCampaignId($parentid,$version,$connection,$disc_per){
        $query="UPDATE online_regis.downsell_trn SET campaignid = (SELECT group_concat(campaignid SEPARATOR '-') as campaignids from tbl_companymaster_finance_temp where parentid='$parentid' and version='$version' and recalculate_flag=1) where parentid='$parentid' and version='$version'";
        parent::execQuery($query,$connection);
        $query="SELECT budget,campaignid,original_budget,duration from tbl_companymaster_finance_temp where parentid='$parentid' and version='$version' AND campaignid NOT IN (56) AND recalculate_flag = 1 AND budget > 0";
        $stmt	=	parent::execQuery($query,$connection);
        $row    =	parent::numRows($stmt);
        $downsel_budget	=	array();
        $downsel_tenure	=	array();
        if($row	>	0){
			while ($row_temp = parent::fetchData($stmt)) {
				$campaign_id      = $row_temp['campaignid'];
				$sys_budget       = $row_temp['budget'];
				$original_budget  = $row_temp['original_budget'];
				$disc_budget      = (1-($disc_per/100))*$original_budget;
				$downsel_budget['budget'][$campaign_id]	=	$disc_budget;
				$downsel_tenure['tenure'][$campaign_id]	=	$row_temp['duration'];
				if($original_budget > $disc_budget && $disc_budget!= 0 ){
					 $ratio= ($original_budget/$disc_budget);
					 $org_bud_str = '';
					 $dwnsBudget= $disc_budget;
					 if($ratio>1){
						$downsel_budget['original_budget'][$campaign_id] = $disc_budget;
					 }
				}
			}
			$downsel_budget		=	json_encode($downsel_budget);
			$downsel_tenure		=	json_encode($downsel_tenure);
			$updateDownsel		=	"UPDATE online_regis.downsell_trn SET downsel_campaignBud='$downsel_budget',downsel_tenure='$downsel_tenure' where parentid='$parentid' AND version='$version'";
			$upd_con			=	parent::execQuery($updateDownsel,$connection);
		}
        //~ $this->updateCampaignIdBUdget($parentid,$version,$connection,$disc_per);
    }
    
    function upDTBudgetTME(){
	   $parentid	=	$this->parentid;
	   $version	=	$this->version;
	   $row_id		=	$this->rowid;
       $query="SELECT budget,campaignid,original_budget from tbl_companymaster_finance_temp where parentid='$parentid' and version='$version' AND campaignid NOT IN (56) AND recalculate_flag = 1 AND budget > 0"; 
        $stmt	=	parent::execQuery($query,$this->conn_tme_jds);
        $row    =	parent::numRows($stmt); 
        $fin_campaignBid	=	array();
        if($row	>	0){
			while ($row_temp = parent::fetchData($stmt)) {
				$fin_campaignBid[$row_temp['campaignid']]	=	$row_temp['budget'];
			}
			$fin_campaignBid		=	json_encode($fin_campaignBid);
			$updateDownsel		=	"UPDATE online_regis.downsell_trn SET finance_campaignBud='$fin_campaignBid' WHERE id='$row_id'";
			$upd_con			=	parent::execQuery($updateDownsel,$this->conn_idc);
		}
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
    * function to add campaign id's to the downsell request from tme_jds DB
    */
    function updateCampaignIdTme($parentid,$version,$disc_per,$conntmejds,$connidc){
        $select="SELECT group_concat(campaignid SEPARATOR '-') as campaignids from tme_jds.tbl_companymaster_finance_temp where parentid='$parentid' and version='$version' and recalculate_flag=1";
        $selRows=parent::execQuery($select, $conntmejds);
        while($row_stat = parent::fetchData($selRows)){
            $campids=$row_stat['campaignids'];
        }
        $query="UPDATE online_regis.downsell_trn SET campaignid ='$campids' where parentid='$parentid' and version='$version'";
        parent::execQuery($query,$connidc);
        $query="SELECT budget,campaignid,original_budget,duration from tbl_companymaster_finance_temp where parentid='$parentid' and version='$version' AND campaignid NOT IN (56) AND recalculate_flag = 1 AND budget > 0";
        $stmt	=	parent::execQuery($query,$conntmejds);
        $row    =	parent::numRows($stmt);
        $downsel_budget	=	array();
        if($row	>	0){
			while ($row_temp = parent::fetchData($stmt)) {
				$campaign_id      = $row_temp['campaignid'];
				$sys_budget       = $row_temp['budget'];
				$original_budget  = $row_temp['original_budget'];
				$disc_budget      = (1-($disc_per/100))*$original_budget;
				$downsel_budget['budget'][$campaign_id]	=	$disc_budget;
				$downsel_tenure['tenure'][$campaign_id]	=	$row_temp['duration'];
				if($original_budget > $disc_budget && $disc_budget!= 0){
					 $ratio= ($original_budget/$disc_budget);
					 $org_bud_str = '';
					 $dwnsBudget= $disc_budget;
					 if($ratio>1){
						$downsel_budget['original_budget'][$campaign_id] = $disc_budget;
					 }
				}
			}
			$downsel_budget		=	json_encode($downsel_budget);
			$downsel_tenure		=	json_encode($downsel_tenure);
			$updateDownsel		=	"UPDATE online_regis.downsell_trn SET downsel_campaignBud='$downsel_budget',downsel_tenure='$downsel_tenure' where parentid='$parentid' AND version='$version'";
			$upd_con			=	parent::execQuery($updateDownsel,$connidc);
		}
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
    * function to check id the down sell request is valid or already existing
    * @return json
    */
    function chkRequest() {
        if ($this->source === '' || $this->parentid==='' || $this->version===''):
            $result = json_encode(array('error' => 1, 'msg' => 'All parameters are required'));
        else:
            try {
                $stmt   = parent::execQuery("SELECT id,ecs_flag,status,request_type,cust_id,cust_name,updated_by FROM online_regis.downsell_trn WHERE source='$this->source' AND parentid='$this->parentid' AND version='$this->version' AND delete_flag!=1 AND status!=2 AND module!='Geniolite'", $this->conn_idc);
                $row    = parent::numRows($stmt);
                if($row >0):
						while ($row_stat = parent::fetchData($stmt)) {
							 $id     	  = $row_stat['id'];
							 $flag   	  = $row_stat['ecs_flag'];
							 $status 	  = $row_stat['status'];
							 $type   	  = $row_stat['request_type'];
							 $cust_id     = $row_stat['cust_id'];
							 $cust_name	  = $row_stat['cust_name'];
							 $updated_by  = $row_stat['updated_by'];
						}
						$result = json_encode(array('error' => 4, 'msg' => 'Request already exist', 'ecs'=>$flag, 'status'=>$status, 'type'=>$type));
                    else:
                        $result = json_encode(array('error' => 0, 'msg' => 'It is a new request'));
                endif;
            } catch (Exception $exc) {
                $result = json_encode(array('error' => 2, 'msg' => $exc->getTraceAsString()));
            }
        endif;
        return $result;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
    * function to update the deal close flag on "Deale Close"
    * @return json
    */
    function updtDealClose() {
        if ($this->parentid==='' || $this->version===''):
            $result = json_encode(array('error' => 1, 'msg' => 'All parameters are required'));
        else:
            try {
                if(parent::execQuery("UPDATE online_regis.downsell_trn SET dealclose_flag='2' WHERE parentid='$this->parentid' AND version='$this->version' AND delete_flag!=1", $this->conn_idc)):
                    $result = json_encode(array('error' => 0, 'msg' => 'Success'));
                    else:
                        $result = json_encode(array('error' => 3, 'msg' => 'Oops.. Something went wrong'));
                endif;
            } catch (Exception $exc) {
                $result = json_encode(array('error' => 2, 'msg' => $exc->getTraceAsString()));
            }
        endif;
        return $result;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
    * to dump data from "tbl_bidding_details_intermediate" and "tbl_bidding_details_summary" to their duplicate tables-
    * on downdell request
    * @return json
    */
    function dumpData() {
        if($this->parentid==='' || $this->version===''):
            $result = json_encode(array('error' => 1, 'msg' => 'All parameters are required'));
        else:
            try {
                if(parent::execQuery("INSERT INTO db_budgeting.tbl_bidding_details_intermediate_archive_approvalmodule (parentid,version,catid,national_catid,pincode_list,cat_budget,updatedby,updatedon,backenduptdate)"
                        . "SELECT parentid,version,catid,national_catid,pincode_list,cat_budget,updatedby,updatedon,backenduptdate FROM db_budgeting.tbl_bidding_details_intermediate_approvalmodule WHERE parentid='$this->parentid' and version='$this->version'", $this->conn_budget)){
                    $delereInterAprovalModule=  parent::execQuery("DELETE FROM db_budgeting.tbl_bidding_details_intermediate_approvalmodule WHERE parentid='$this->parentid' and version='$this->version'", $this->conn_budget);
                }

                if (parent::execQuery("INSERT INTO db_budgeting.tbl_bidding_details_intermediate_approvalmodule (parentid,version,catid,national_catid,pincode_list,cat_budget,updatedby,updatedon,backenduptdate) "
                        . "SELECT parentid,version,catid,national_catid,pincode_list,cat_budget,updatedby,updatedon,backenduptdate FROM db_budgeting.tbl_bidding_details_intermediate WHERE parentid='$this->parentid' and version='$this->version'",$this->conn_budget)):
                    $result = json_encode(array('error' => 0, 'msg' => 'Success'));
                else:
                    $result = json_encode(array('error' => 3, 'msg' => 'Sever error: unable to insert new record.'));
                endif;

                if (parent::execQuery("INSERT INTO db_budgeting.tbl_bidding_details_summary_approvalmodule (sphinx_id,parentid,docid,companyname,data_city,pincode,latitude,longitude,version,module,contact_details,category_list,pincode_list,duration,sys_fp_budget,sys_package_budget,sys_regfee_budget,sys_total_budget,actual_fp_budget,actual_package_budget,actual_regfee_budget,actual_total_budget,appfee,areazonal_count,allarea_count,option_selected,mode_selected,bestbudgetflag,dealclosed_flag,dealclosed_on,dealclosed_by,dealclosed_uname,updatedby,username,updatedon,backenduptdate) "
                        . "SELECT sphinx_id,parentid,docid,companyname,data_city,pincode,latitude,longitude,version,module,contact_details,category_list,pincode_list,duration,sys_fp_budget,sys_package_budget,sys_regfee_budget,sys_total_budget,actual_fp_budget,actual_package_budget,actual_regfee_budget,actual_total_budget,appfee,areazonal_count,allarea_count,option_selected,mode_selected,bestbudgetflag,dealclosed_flag,dealclosed_on,dealclosed_by,dealclosed_uname,updatedby,username,updatedon,backenduptdate FROM db_budgeting.tbl_bidding_details_summary bds WHERE parentid='$this->parentid' and version='$this->version'"
                        . " ON DUPLICATE KEY UPDATE sphinx_id=bds.sphinx_id, parentid=bds.parentid, docid=bds.docid, companyname=bds.companyname, data_city=bds.data_city, pincode=bds.pincode, latitude=bds.latitude, longitude=bds.longitude, version=bds.version, module=bds.module, contact_details=bds.contact_details, category_list=bds.category_list, pincode_list=bds.pincode_list, duration=bds.duration,  allarea_count=bds.allarea_count, option_selected=bds.option_selected, dealclosed_flag=bds.dealclosed_flag, dealclosed_by=bds.dealclosed_by, updatedby=bds.updatedby, updatedon=bds.updatedon,"
                        . " sys_fp_budget=bds.sys_fp_budget, sys_package_budget=bds.sys_package_budget, sys_regfee_budget=bds.sys_regfee_budget, sys_total_budget=bds.sys_total_budget, actual_fp_budget=bds.actual_fp_budget, actual_package_budget=bds.actual_package_budget, actual_regfee_budget=bds.actual_regfee_budget, actual_total_budget=bds.actual_total_budget, appfee=bds.appfee, areazonal_count=bds.areazonal_count, mode_selected=bds.mode_selected, bestbudgetflag=bds.bestbudgetflag, dealclosed_on=bds.dealclosed_on, dealclosed_uname=bds.dealclosed_uname, username=bds.username, backenduptdate=bds.backenduptdate",$this->conn_budget)):
                    $result = json_encode(array('error' => 0, 'msg' => 'Success'));
                else:
                    $result = json_encode(array('error' => 3, 'msg' => 'Sever error: unable to insert new record.'));
                endif;
            } catch (Exception $exc) { $result = json_encode(array('error' => 2, 'msg' => $exc->getTraceAsString())); }
        endif;
        return $result;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
    * function to check the down sell request status pending or not
    * @return json
    */
    function chkPending() {
        if ($this->parentid==='' || $this->version===''):
            $result = json_encode(array('error' => 1, 'msg' => 'All parameters are required'));
        else:
            try {
                $stmt   = parent::execQuery("SELECT status,delete_flag,request_type,dealclose_flag,module FROM online_regis.downsell_trn WHERE parentid='$this->parentid' AND version='$this->version' AND delete_flag!=1", $this->conn_idc);
                $row    = parent::numRows($stmt);
                if($row >0):
                    while ($row_stat = parent::fetchData($stmt)) {
                         $status = $row_stat['status'];
                         $flag   = $row_stat['delete_flag'];
                         $rqsttyp= $row_stat['request_type'];
						 $dealclose_flag	= $row_stat['dealclose_flag'];
						  $module			= $row_stat['module'];
                    }
                    if($flag==1){
                         $result = json_encode(array('error' => 3, 'msg' => 'It is a deleted request', 'type'=>$rqsttyp, 'status'=>$status));
                    }else if($dealclose_flag    ==  2 && $status == 1){
                         $result = json_encode(array('error' => 3, 'msg' => 'It is a Dealclosed Request', 'type'=>$rqsttyp, 'status'=>$status));
                    }else if(strtolower($module)	==	'geniolite' && $status == 1){
						$result = json_encode(array('error' => 5, 'msg' => 'Allow as it is from geniolite', 'type'=>$rqsttyp, 'status'=>$status));
					}else if($status==2){
                         $result = json_encode(array('error' => 3, 'msg' => 'It is a rejected request', 'type'=>$rqsttyp, 'status'=>$status));
                    }else if($status==0){
                         $result = json_encode(array('error' => 4, 'msg' => 'Pending request', 'type'=>$rqsttyp, 'status'=>$status));
                    }else if($dealclose_flag    !=  2 && $status==1 && strtolower($module)	!=	'geniolite'){
                        $result = json_encode(array('error' => 0, 'msg' => 'It is an approved request', 'type'=>$rqsttyp, 'status'=>$status));
                    }

                endif;
                if($row ==0):
                    $result = json_encode(array('error' => 3, 'msg' => 'No record found'));
                endif;
            } catch (Exception $exc) {
                $result = json_encode(array('error' => 2, 'msg' => $exc->getTraceAsString()));
            }
        endif;
        return $result;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    
    function chkgenericPending() {
        if ($this->parentid===''):
            $result = json_encode(array('error' => 1, 'msg' => 'All parameters are required'));
        else:
            try {
                $stmt   = parent::execQuery("SELECT status,delete_flag,request_type,dealclose_flag,module,cust_id,cust_name FROM online_regis.downsell_trn WHERE parentid='$this->parentid' AND  status IN('0','-1') AND delete_flag!=1 order by created_at desc LIMIT 1", $this->conn_idc);
                $row    = parent::numRows($stmt);
                if($row >0):
                    $data = parent::fetchData($stmt);
                    if($data['module'] == '')
						$module = $data['source'];
					else
						$module = $data['module'];
                    $result = json_encode(array('error' => 1, 'msg' => 'Downsell Pending request found', 'data'=>$data, 'message'=>'Downsell Request raised by '.$data['cust_id'].'('.$data['cust_name'].') is Pending from '.$module.'. You are not allowed to edit the contract!.'));
                endif;
                if($row ==0):
                    $result = json_encode(array('error' => 0, 'msg' => 'No record found'));
                endif;
            } catch (Exception $exc) {
                $result = json_encode(array('error' => 2, 'msg' => $exc->getTraceAsString()));
            }
        endif;
        return $result;
    }
    
    /**
    * function insert values to 'fcv_trn' table
    * @return json
    */
    function fcvRequest() {
        $now        = date('Y-m-d H:i:s');
        $status     = 0;
        if ($this->cust_id === '' || $this->cust_nm === '' || $this->fcv_amt === '' || $this->reason === '' || $this->module === '' || $this->company_nm === '' || $this->parentid==='' ):
            $result = json_encode(array('error' => 1, 'msg' => 'All parameters are required'));
        else:
            try {
                if (parent::execQuery("INSERT INTO online_regis.fcv_trn (company_nm, source, cust_id, cust_name, fcv_amt, reason, status, created_at, parentid, data_city) VALUES ('$this->company_nm', '$this->module', '$this->cust_id', '$this->cust_nm', '$this->fcv_amt', '$this->reason', '$status', '$now', '$this->parentid', '$this->data_city')",$this->conn_idc)):
                    $result = json_encode(array('error' => 0, 'msg' => 'Success'));
                else:
                    $result = json_encode(array('error' => 3, 'msg' => 'Sever error: unable to insert new record.'));
                endif;
            } catch (Exception $exc) {
                $result = json_encode(array('error' => 2, 'msg' => $exc->getTraceAsString()));
            }
        endif;
        return $result;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
    * function to check id the FCV request is valid or already existing
    * @return json
    */
    function chkfcvRequest() {
        if ($this->source === '' || $this->parentid===''):
            $result = json_encode(array('error' => 1, 'msg' => 'All parameters are required'));
        else:
            try {
                $stmt   = parent::execQuery("SELECT id FROM online_regis.fcv_trn WHERE source='$this->source' AND parentid='$this->parentid' AND delete_flag!=1", $this->conn_idc);
                $row    = parent::numRows($stmt);
                if($row >0):
                    $result = json_encode(array('error' => 4, 'msg' => 'Request already exist'));
                    else:
                        $result = json_encode(array('error' => 0, 'msg' => 'It is a new request'));
                endif;
            } catch (Exception $exc) {
                $result = json_encode(array('error' => 2, 'msg' => $exc->getTraceAsString()));
            }
        endif;
        return $result;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
     * to fetch all the entries to downsell_trn
     * @return JSON
     */
    function downsellView() {
		if(strtolower($this->source) == 'tme'){
			$source = "source IN ('tme','me')";
		}else{
			$source = "source IN ('$this->source')";
		}
		if(strtolower($this->source) == 'tme' && ($this->status == 0 || $this->status == '0')){
			$status = "status IN('0','-1')";
		}else{
			$status = "status='$this->status'";
		}
        if($this->status==='' || $this->custid==='' || $this->source===''):
            $result = json_encode(array('error' => 1, 'msg' => 'Status and User ID are mandatory'));
        else:
            try {
                if($this->status==3):
                    $stmt=  parent::execQuery("SELECT id,company_nm as compname, parentid as contractid,source,cust_id,cust_name,actual_amt,"
                            . "proposed_amt,status,version,updated_by,request_type,data_city,created_at,updated_at,ecs_flag,dealclose_flag,module FROM online_regis.downsell_trn WHERE delete_flag='0' AND cust_id='$this->custid' AND ".$source." ORDER BY id DESC LIMIT 1000", $this->conn_idc);
                else:
                    $stmt=  parent::execQuery("SELECT id,company_nm as compname, parentid as contractid,source,cust_id,cust_name,actual_amt,"
                            . "proposed_amt,status,version,updated_by,request_type,data_city,created_at,updated_at,ecs_flag,dealclose_flag,module FROM online_regis.downsell_trn WHERE ".$status." AND delete_flag='0' AND cust_id='$this->custid' AND ".$source." ORDER BY id DESC LIMIT 1000", $this->conn_idc);
                endif;
                $row    = parent::numRows($stmt);
                if($row >0):
                    $i=0;
                    while ($rows = parent::fetchData($stmt)) {
                        $data[$i]=$rows;
                        $i++;
                    }
                    $result = json_encode(array('data'=>$data, 'error' => 0, 'msg' => 'success'));
                    else:
                        $result = json_encode(array('error' => 1, 'msg' => 'Unknown status'));
                endif;
            } catch (Exception $exc) {
                $result = json_encode(array('error' => 1, 'msg' => $exc->getTraceAsString()));
            }
        endif;
        return $result;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Created by - Vivek(10033590) Queries fetched from downsellView().
     * to fetch paginated entries to downsell_trn and is used in genio_lite for Discount Report under menu links
     * @return JSON
     */
    function downsellPaginate() {
        /*
         *    status = 0 ----> Pending  
         *    status = 1 ----> Approved
         *    status = 2 ----> Rejected
         *    status = 3 ----> All  
         *    custid - --> empcode 
         *    source ----> mocdule (me/tme)
         *    pageLimit ----> No. of results per page
        */
        if($this->status==='' || $this->custid==='' || $this->source==='' || $this->pageShow==='' || $this->pageLimit==0 || $this->pageLimit===''):
            $result = json_encode(array('error' => 1,'status' => $this->status, 'msg' => 'Status/User ID/Pagination values  are mandatory'));
        else:
            try {
                if($this->status==3):
                    $stmtCnt=  parent::execQuery("SELECT COUNT(1) as cnt FROM online_regis.downsell_trn WHERE delete_flag='0' AND cust_id='$this->custid' AND source='$this->source' ORDER BY id DESC", $this->conn_idc);
                    $stmt=  parent::execQuery("SELECT id,company_nm as compname, parentid as contractid,source,cust_id,cust_name,actual_amt,"
                            . "proposed_amt,status,version,updated_by,request_type,data_city,created_at,updated_at,ecs_flag,dealclose_flag,module,master_transaction_id FROM online_regis.downsell_trn WHERE delete_flag='0' AND cust_id='$this->custid' AND source='$this->source' ORDER BY id DESC LIMIT "
                            . $this->pageShow*$this->pageLimit.','.$this->pageLimit, $this->conn_idc);
                else:
                    $stmtCnt=  parent::execQuery("SELECT COUNT(1) as cnt FROM online_regis.downsell_trn WHERE status='$this->status' AND delete_flag='0' AND cust_id='$this->custid' AND source='$this->source' ORDER BY id DESC", $this->conn_idc);
                    $stmt=  parent::execQuery("SELECT id,company_nm as compname, parentid as contractid,source,cust_id,cust_name,actual_amt,"
                            . "proposed_amt,status,version,updated_by,request_type,data_city,created_at,updated_at,ecs_flag,dealclose_flag,module,master_transaction_id FROM online_regis.downsell_trn WHERE status='$this->status' AND delete_flag='0' AND cust_id='$this->custid' AND source='$this->source' ORDER BY id DESC LIMIT "
                            . $this->pageShow*$this->pageLimit.','.$this->pageLimit, $this->conn_idc);
                endif;
                $row    = parent::numRows($stmt);
                $dataCount = parent::fetchData($stmtCnt);
                if($row >0):
                    $i=0;
                    while ($rows = parent::fetchData($stmt)) {
                        $data[$i]=$rows;
                        $i++;
                    }
                    $result = json_encode(array('data'=>$data,'counttot' => $dataCount['cnt'], 'error' => 0, 'msg' => 'success'));
                    else:
                        $result = json_encode(array('error' => 1, 'msg' => 'Unknown status'));
                endif;
            } catch (Exception $exc) {
                $result = json_encode(array('error' => 1, 'msg' => $exc->getTraceAsString()));
            }
        endif;
        return $result;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * To update cancel the User Request
     * @return JSON
     */
    function cancelDsRqst() {
        if($this->updateId===''):
            $result = json_encode(array('error' => 1, 'msg' => 'If no id, No update..'));
        else:
            try {
                if($stmt   = parent::execQuery("UPDATE online_regis.downsell_trn SET delete_flag=1 WHERE id='$this->updateId' ", $this->conn_idc)):
                    $result = json_encode(array('error' => 0, 'msg' => 'Success'));
                else:
                    $result = json_encode(array('error' => 1, 'msg' => 'Oops, something went wrong.'));
                endif;
            } catch (Exception $exc) {
                $result = json_encode(array('error' => 1, 'msg' => $exc->getTraceAsString()));
            }

        endif;
        return $result;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
     * Function for refund request
     * @return JSON
     */
    function refundRqst() {
        $now        = date('Y-m-d H:i:s');
        $status     = 0;
        if ($this->reason==='' || $this->company_nm==='' || $this->module==='' || $this->amtandst==='' || $this->amtnost===''
            || $this->cust_nm==='' || $this->cust_id==='' || $this->data_city==='' || $this->parentid==='' || $this->comments===''):
            $result = json_encode(array('error' => 1, 'msg' => 'All parameters are required'));
        else:
            try {
                if (parent::execQuery("INSERT INTO online_regis.refund_trn (company_nm,source,cust_id,cust_name,refund_amt,grossamt,serv_tax,excessreason,comments,reason,"
                        . "status,parentid,data_city,created_at,jdpool,orig_1,xfer_1,enddate_0,enddate_1) "
                        . "VALUES ('$this->company_nm','$this->module','$this->cust_id','$this->cust_nm','$this->amtandst','$this->amtnost','$this->st',"
                        . "'$this->exsreson','$this->comments','$this->reason','$status','$this->parentid','$this->data_city','$now','$this->jdpool',"
                        . "'$this->orig_1','$this->xfer_1','$this->enddate_0','$this->enddate_1')",$this->conn_idc)):
                    $result = json_encode(array('error' => 0, 'msg' => 'Success'));
                else:
                    $result = json_encode(array('error' => 3, 'msg' => 'Sever error: unable to insert new record.'));
                endif;
            } catch (Exception $exc) {
                $result = json_encode(array('error' => 2, 'msg' => $exc->getTraceAsString()));
            }
        endif;
        return $result;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
     * function to create logs
     * @return JSON
     */
    function approvalLogs() {
        $paramArr=json_decode($this->param, TRUE);
        if($paramArr['p_id']!=''){
            $parentid=$paramArr['p_id'];
        }else if($paramArr['parentid']!=''){
            $parentid=$paramArr['parentid'];
        }else{
            $parentid='';
        }
        $now    = date('Y-m-d H:i:s');
        try {
            if(parent::execQuery("INSERT INTO online_regis.approval_logs (source,api,parentid,param,response,datetime) "
                    . "VALUES ('$this->source','$this->api','$parentid','$this->param','$this->response','$now')",$this->conn_idc)):
                $result = json_encode(array('error' => 0, 'msg' => 'Success'));
            else:
                $result = json_encode(array('error' => 1, 'msg' => 'Sever error: unable to insert new record.'));
            endif;
        } catch (Exception $exc) {
            $result = json_encode(array('error' => 2, 'msg' => $exc->getTraceAsString()));
        }
        return $result;
    }

}
