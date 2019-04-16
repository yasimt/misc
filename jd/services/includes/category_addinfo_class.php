<?php
class category_addinfo_class extends DB
{
	var  $conn_iro    	= null;
	var  $conn_jds   	= null;
	var  $conn_tme 	= null;
	var  $conn_fnc    	= null;
	var  $conn_idc    	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	
	
	function __construct($params)
	{
		$parentid 		= trim($params['parentid']);
		$module 		= trim($params['module']);
		$data_city 		= trim($params['data_city']);
		$ucode 			= trim($params['ucode']);
		$action 		= trim($params['action']);
		
		if(($parentid=='') && ($action == 'ccrhistory'))
        {
            $message = "Parentid is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
		if(($ucode=='') && ($action == 'ccrhistory'))
        {
            $message = "ucode is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($module)=='')
		{
			$message = "Module is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->ucode 		= $ucode;
		
		//mongo
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->companyClass_obj = new companyClass();
		
		$this->setServers();
		
	}
	function setServers()
	{	
		global $db;
		$conn_city 				= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];
		
		if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){
			$this->mongo_flag = 1;
		}
	}
	function getCCRHistoryInfo($params){
		$filterby 	= trim($params['filterby']);
		$pgno 		= trim($params['pgno']);
		$limit 		= 25;
		if(empty($pgno)) $pgno = 1;
		$ps = (($pgno-1)*$limit);
		
		if($filterby == 'ucode'){
			$condition = " AND requested_by = '".$this->ucode."'";
		}else{
			$condition = " AND parentid = '".$this->parentid."'";
		}
		
		$resultArr = array();
		$sqlCCRHistory = "SELECT catetory_name, parentId, date_requested, requested_by, loginName, dept, created_as, catid, date_approved FROM d_jds.tbl_category_creation_request WHERE request_type = 1 AND ccr_flag = 1 AND parentid!='' ".$condition." AND dept = '".$this->module."' GROUP BY catetory_name,requested_by ORDER BY date_requested DESC LIMIT $ps, $limit";
		$resCCRHistory = parent::execQuery($sqlCCRHistory, $this->conn_local);
		if($resCCRHistory && parent::numRows($resCCRHistory)>0)
		{
			$i = 0;
			while($row_ccr_history 		= parent::fetchData($resCCRHistory))
			{
				
				$catname			= ucwords(strtolower($row_ccr_history['catetory_name']));
				$ccrpid 			= trim($row_ccr_history['parentId']);
				$compname 			= $this->getCompanyName($ccrpid);
				$compname 			= $compname ? $compname : '-';
				$requested_by 		= trim($row_ccr_history['requested_by']);
				$loginName 			= trim($row_ccr_history['loginName']);
				$loginName 			= str_ireplace("-CSGENIO","",$loginName);
				$loginName 			= ucwords(strtolower($loginName));
				$requested_user 	= $loginName." [".$requested_by."]";
				$requested_date 	= trim($row_ccr_history['date_requested']);
				$requested_catid 	= intval($row_ccr_history['catid']);
				$extra_cmnt = '';
				if(intval($requested_catid)>0)
				{
					$premium_status = '';
					$extra_cmnt = $this->getCatRemarks($ccrpid,$requested_catid);
					if(strtoupper($extra_cmnt) == 'SEND CATEGORY FOR PREMIUM CATEGORY MODERATION')
					{
						$premium_status = $this->getPremiumStatus($ccrpid,$requested_catid);
						switch($premium_status)
						{
							case '0':
								$premium_status_val = 'Pending';
								break;
							case '1':
								$premium_status_val = 'Approved';
								break;
							case '2':
								$premium_status_val = 'Rejected';
								break;
							case '3':
								$premium_status_val = 'Escalate To CS';
								break;
							case '4':
								$premium_status_val = 'Follow Up';
								break;
						}
						$extra_cmnt = "Sent To PCMM - Status : <b>".$premium_status_val."</b>";
					}
					if(strtoupper($extra_cmnt) == 'PROCESS RUN SUCCESSFULLY')
					{
						$extra_cmnt = "Category Added In Contract.";
					}
				}
				$resultArr['data'][$i]['catname']	= $catname;
				$resultArr['data'][$i]['ccrpid']  	= $ccrpid;
				$resultArr['data'][$i]['compname']  = $compname;
				$resultArr['data'][$i]['reqby']  	= $requested_by;
				$resultArr['data'][$i]['requser']  	= $requested_user;
				$resultArr['data'][$i]['reqdate']  	= $requested_date;
				$resultArr['data'][$i]['catdet']	= $this->getCCRStatus($catname,$requested_by,$extra_cmnt);
				$i++;
			}
		}
		if(count($resultArr)>0){
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getCompanyName($ccrpid)
	{
		$compname = '';
		if(strtoupper(trim($this->parentid)) == strtoupper(trim($ccrpid)))
		{
			if($this->mongo_flag == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $ccrpid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "companyname";
				$row_parent_compname = $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$sqlParentCompName = "SELECT companyname FROM tbl_companymaster_generalinfo_shadow WHERE parentid = '".$ccrpid."'";
				$resParentCompName = parent::execQuery($sqlParentCompName, $this->conn_idc);
				if($resParentCompName && parent::numRows($resParentCompName)>0)
				{
					$row_parent_compname 	= parent::fetchData($resParentCompName);
				}
			}
			$parent_compname = $row_parent_compname['companyname'];
			return $parent_compname;
		}
		else
		{
			$row_company = array();
			$cat_params = array();
			$cat_params['data_city']	= $this->data_city;
			$cat_params['table'] 		= 'gen_info_id';
			$cat_params['module'] 		= $this->module;
			$cat_params['parentid'] 	= $ccrpid;
			$cat_params['action'] 		= 'fetchdata';
			$cat_params['fields']		= 'companyname';
			$cat_params['page']			= 'category_addinfo_class';

			$resTempCategory			= 	array();
			$resTempCategory			=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

			if(!empty($resTempCategory) && $resTempCategory['errors']['code']==0){
				$row_company = $resTempCategory['results']['data'][$ccrpid];
				$compname 		= $row_company['companyname'];
			}


			/*$sqlCompanyName = "SELECT companyname FROM tbl_companymaster_generalinfo WHERE parentid = '".$ccrpid."'";
			$resCompanyName = parent::execQuery($sqlCompanyName, $this->conn_idc);
			if($resCompanyName && parent::numRows($resCompanyName)>0)
			{
				$row_company 	= parent::fetchData($resCompanyName);
				$compname 		= $row_company['companyname'];
			}*/
			return $compname;
		}
	}
	function getCatRemarks($ccrpid,$catid)
	{
		$remarks = '-';
		$sqlCatRemarks = "SELECT comments FROM tbl_ccr_process_log WHERE parentid = '".$ccrpid."' AND catid ='".$catid."' ORDER BY done_date DESC LIMIT 1";
		$resCatRemarks = parent::execQuery($sqlCatRemarks, $this->conn_local);
		if($resCatRemarks && parent::numRows($resCatRemarks)>0)
		{
			$row_remarks 	= parent::fetchData($resCatRemarks);
			$remarks 		= trim($row_remarks['comments']);
		}
		return $remarks;
	}
	function getPremiumStatus($ccrpid,$catid)
	{
		$premium_status = '';
		$sqlPremiumStatus = "SELECT approval_status FROM tbl_premium_categories_audit WHERE parentid= '".$ccrpid."' AND catids = '".$catid."'";
		$resPremiumStatus = parent::execQuery($sqlPremiumStatus, $this->conn_local);
		if($resPremiumStatus && parent::numRows($resPremiumStatus)>0)
		{
			$row_premium_status	= parent::fetchData($resPremiumStatus);
			$premium_status 	= intval($row_premium_status['approval_status']);
		}
		return $premium_status;
	}
	function getCCRStatus($catname,$reqby,$extra_cmnt)
	{
		$resultArr	= array();
		$sqlCCRStatus = "SELECT catetory_name,created_as,status,date_approved,comments FROM d_jds.tbl_category_creation_request_stats WHERE catetory_name= '".addslashes(stripslashes($catname))."' AND requested_by = '".$reqby."'ORDER BY update_date DESC LIMIT 1";
		$resCCRStatus = parent::execQuery($sqlCCRStatus, $this->conn_local);
		if($resCCRStatus && parent::numRows($resCCRStatus)>0)
		{
			$row_ccr_status	= parent::fetchData($resCCRStatus);
			switch($row_ccr_status['status'])
			{
				case '1':
					$status_val = 'Pending';
					break;
				case '2':
					$status_val = 'Approved';
					break;
				case '3':
					$status_val = 'Denied';
					break;
				case '4':
					$status_val = 'Completed';
					break;
				case '5':
					$status_val = 'Cancelled';
					break;
				case '6':
					$status_val = 'IRO Mistake';
					break;
				case '7':
					$status_val = 'Data Does Not Exist';
					break;
				case '8':
					$status_val = 'Data Added';
					break;
				case '9':
					$status_val = 'Created As Synonym';
					break;
			}
			$catname 	= trim($row_ccr_status['created_as']) 		? trim($row_ccr_status['created_as']) 		: trim($row_ccr_status['catetory_name']);
			$aprdate 	= trim($row_ccr_status['date_approved']) 	? trim($row_ccr_status['date_approved']) 	: '';
			$aprremarks = trim($row_ccr_status['comments']) 		? trim($row_ccr_status['comments']) 		: '';
			
			$resultArr['Status'] 	= $status_val;
			if(!empty($aprdate)){
				$resultArr['Appr. Date'] 	= $aprdate;
			}
			if(!empty($aprremarks)){
				$resultArr['Appr. Remarks'] 	= $aprremarks;
			}
			if(!empty($extra_cmnt)){
				$resultArr['Comment']	= $extra_cmnt;
			}
		}
		return $resultArr;
		
	}
	private function sendDieMessage($msg)
	{
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
}
?>
