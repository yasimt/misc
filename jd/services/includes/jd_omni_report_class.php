<?php

class jd_omni_report_class extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $dbConIdc    	= null;
	var  $params 	 	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	var  $catsearch		= null;
	var  $data_city		= null;
	var  $campaignid 	= null;
	

	function __construct($params)
	{		
		$this->params = $params;	
		
		if($this->params['action'] == 1)
		{
			
			if($this->params['from_date']) {
				$temp      	   = explode("-", $this->params['from_date']);
				$from_date 	   = date("Y-m-d H:i:s", mktime(0, 0, 10, $temp[1], $temp[2], $temp[0]));
				$this->frmdate = $from_date;
			}else{
			    $errorarray['errormsg']='from date missing';
				echo json_encode($errorarray); exit;
			}
			
			if($this->params['to_date']) {
				$temp	       = explode("-", $this->params['to_date']);
				$end_date	   = date("Y-m-d H:i:s", mktime(23, 59, 50, $temp[1], $temp[2], $temp[0]));
				$this->to_date = $end_date;
			}else{
			    $errorarray['errormsg']='End date missing';
				echo json_encode($errorarray); exit;
			}
			
			if($this->params['data_city']) {
				$this->data_city = $this->params['data_city'];
			}
			
			
			if($this->params['data_type']) {
				$this->data_type = $this->params['data_type'];
			}
						
			if($this->params['parentid']) {
				$this->parentid = $this->params['parentid'];
			}
			
			if($this->params['companyname']) {
				$this->companyname = $this->params['companyname'];
			}
			
			if($this->params['cheque_no']) {
				$this->cheque_no = $this->params['cheque_no'];
			}
			
			if($this->params['status']) {
				$this->status = $this->params['status'];
			}
			
			if($this->params['omni_disp_status']) {
				$this->omni_disp_status = $this->params['omni_disp_status'];
			}
			
		}		
		
		if($this->params['action'] == 2)
		{
			if($this->params['parentid']) {
				$this->parentid = $this->params['parentid'];
			}else{
			    $errorarray['errormsg']='parentid missing';
				echo json_encode($errorarray); exit;
			}
			
			
			if($this->params['version']) {
				$this->version = $this->params['version'];
			}else{
			    $errorarray['errormsg']='version missing';
				echo json_encode($errorarray); exit;
			}
			
			if($this->params['usercode']) {
				$this->usercode = $this->params['usercode'];
			}else{
			    $errorarray['errormsg']='usercode missing';
				echo json_encode($errorarray); exit;
			}
			
			
			if($this->params['data_city']) {
				$this->data_city = $this->params['data_city'];
			}
						
		}
		
		if($this->params['action'] == 3)
		{
			if($this->params['data_city']) {
				$this->data_city = $this->params['data_city'];
			}
		}
		
		if($this->params['action'] == 4)
		{
			if($this->params['parentid']) {
				$this->parentid = $this->params['parentid'];
			}else{
			    $errorarray['errormsg']='parentid missing';
				echo json_encode($errorarray); exit;
			}
			
			
			if($this->params['version']) {
				$this->version = $this->params['version'];
			}else{
			    $errorarray['errormsg']='version missing';
				echo json_encode($errorarray); exit;
			}
			
			if($this->params['omni_disposition_value']) {
				$this->omni_disposition_value = $this->params['omni_disposition_value'];
			}else{
			    $errorarray['errormsg']='omni_disposition_value missing';
				echo json_encode($errorarray); exit;
			}
			
			
			if($this->params['usercode']) {
				$this->usercode = $this->params['usercode'];
			}else{
			    $errorarray['errormsg']='usercode missing';
				echo json_encode($errorarray); exit;
			}
			
			
			if($this->params['data_city']) {
				$this->data_city = $this->params['data_city'];
			}else{
			    $errorarray['errormsg']='data_city missing';
				echo json_encode($errorarray); exit;
			}
						
		}
		
		
		$this->setServers();
		//echo json_encode('const'); exit;
		
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
					
		if(DEBUG_MODE)
		{
			echo '<pre>db array :: ';
			print_r($db);
		}
		
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		//$this->web_conn   		= $db['website']['master'];
		$this->fin_con   		= $db[strtolower($data_city)]['fin']['slave'];
		$this->idc_con			= $db[strtolower($data_city)]['idc']['master'];
		$this->local_con		= $db[strtolower($data_city)]['iro']['master'];
		
	}
	
		
	function GenerateReport()
	{
		if($this->status == 1)//financial approval pending
		{
			$search_cond="a.entry_date>='$this->frmdate' AND a.entry_date<='$this->to_date'";
			$orderCondition = "ORDER BY a.entry_date";
		}
		
		if($this->status == 2)//all approved but non-verified
		{
			$search_cond	="finalapprovaldate>='$this->frmdate' AND finalapprovaldate<='$this->to_date'";
			$orderCondition = "ORDER BY finalapprovaldate";
			$cs_app_cond 	= "AND a.csApprovalFlag=0";
		}
		
		if($this->status == 3)//all verified approved data
		{
			$search_cond="finalapprovaldate>='$this->frmdate' AND finalapprovaldate<='$this->to_date'";
			$orderCondition = "ORDER BY finalapprovaldate";
			$cs_app_cond 	= "AND a.csApprovalFlag=1";
		}
		if($this->status == 4)//all approved data
		{
			$search_cond="finalapprovaldate>='$this->frmdate' AND finalapprovaldate<='$this->to_date'";
			$orderCondition = "ORDER BY finalapprovaldate";
			$cs_app_cond 	= " ";
		}
		
		if($this->cheque_no)
		{
			$extra_table = "  join payment_cheque_details d on a.instrumentid=d.instrumentid";
			$cheque_cond = " AND d.chequeNo = '".trim($this->cheque_no)."'";
		}
		
		if($this->companyname)
		{
			$search_cond .= "AND companyName like '".$this->companyname."%'";
		}
		if($this->parentid)
		{
			$search_cond .= "AND a.parentid = '".trim($this->parentid)."'";
		}
	
		
		if($this->data_city)
		{
			$city_condition=" AND a.data_city in ('".$this->data_city."')";
		}
		
		if($this->status == 1)
		{
					$sql_contract_count="select  group_concat(distinct a.parentid) as parentids
										from payment_instrument_summary a join payment_clearance_details b
										on a.instrumentid=b.instrumentid join payment_otherdetails c 
										on a.parentid=c.parentid ".$extra_table." and a.version=c.version 
										JOIN payment_apportioning e ON a.parentid=e.parentid AND a.version=e.version 
										WHERE $search_cond ".$cheque_cond." AND ((a.version%10) in (2,3) OR a.campaign_type = 2) and b.finalapprovalflag=0 and approvalstatus=0 AND e.campaignid IN (72,73)
										$city_condition ";
					$res_contract_count = parent::execQuery($sql_contract_count, $this->fin_con);
		}
		
		if(in_array($this->status,array(2,3,4)))
		{
					$sql_contract_count = "  select group_concat(DISTINCT a.parentid) as parentids
											 from payment_otherdetails a 
											 join 
											 (select parentid,data_city AS datacity,
											 SUBSTRING_INDEX(GROUP_CONCAT(VERSION ORDER BY finalapprovaldate DESC SEPARATOR '#'),'#',1) AS VERSION,
											 SUBSTRING_INDEX(GROUP_CONCAT(entry_date ORDER BY finalapprovaldate DESC SEPARATOR '#'),'#',1) AS entry_date,
											 SUBSTRING_INDEX(GROUP_CONCAT(campaign_type ORDER BY finalapprovaldate DESC SEPARATOR '#'),'#',1) AS campaign_type,
											 SUBSTRING_INDEX(GROUP_CONCAT(finalapprovaldate ORDER BY finalapprovaldate DESC SEPARATOR '#'),'#',1) AS finalapprovaldate
											 from payment_instrument_summary a join payment_clearance_details b 
											 on a.instrumentid=b.instrumentid ".$extra_table."
											 where b.finalapprovalflag=1 ".$cheque_cond." AND a.paymenttype='fresh' $city_condition
											 group by parentid) b JOIN payment_apportioning c 
											 on a.parentid=b.parentid and a.version=b.version AND b.parentid=c.parentid AND b.version=c.version
											 where $search_cond AND ((b.version%10) in (2,3) OR b.campaign_type = 2) ".$cs_app_cond." AND c.campaignid IN (72,73) AND c.budget>0";
					$res_contract_count = parent::execQuery($sql_contract_count, $this->fin_con);
		}			
					if(DEBUG_MODE)
					{
						echo '<br> sql:: '.$sql_contract_count;
						echo '<br> res:: '.$res_contract_count;
						echo '<br> num rows:: '.mysql_num_rows($res_contract_count);
					}
					
					if($res_contract_count && mysql_num_rows($res_contract_count))
					{
						$row_contract_count = mysql_fetch_assoc($res_contract_count);
						if(DEBUG_MODE)
						{
							echo 'parentid list <pre> '.$row_contract_count['parentids'];
							print_r(explode(',',$row_contract_count['parentids']));
						}
						
						if(count(explode(',',$row_contract_count['parentids']))>0)
						{
							if($this->omni_disp_status)
							$omni_disp_status_cond = " AND omni_disposition_value IN ('".str_replace(",","','",$this->omni_disp_status)."') "; 
							
							$parentid_list = str_replace(",","','",$row_contract_count['parentids']);
							$sql_omni_details = "SELECT * FROM online_regis1.tbl_omni_details_consolidated  WHERE parentid IN ('".$parentid_list."') AND data_city='".$this->data_city."' ".$omni_disp_status_cond." ";
							$res_omni_details = parent::execQuery($sql_omni_details, $this->idc_con);
							if(DEBUG_MODE)
							{
								echo '<br> sql:: '.$sql_omni_details;
								echo '<br> res:: '.$res_omni_details;
								echo '<br> num rows:: '.mysql_num_rows($res_omni_details);
							}
							if($res_omni_details && mysql_num_rows($res_omni_details)>0)
							{
								while($row_omni_details =  mysql_fetch_assoc($res_omni_details))
								{
									$omni_details[$row_omni_details['parentid']]['contact_person'] = $row_omni_details['contact_person'];
									$omni_details[$row_omni_details['parentid']]['storeid'] = $row_omni_details['storeid'];
									$omni_details[$row_omni_details['parentid']]['supplier_id'] = $row_omni_details['supplier_id'];
									
									
									$omni_details[$row_omni_details['parentid']]['website_request'] = (strtolower(trim($row_omni_details['own_cust_website'])) == 'no')?$row_omni_details['website_requests']:null;
									
									$omni_details[$row_omni_details['parentid']]['domain_name'] = $row_omni_details['website'];
									$omni_details[$row_omni_details['parentid']]['domain_ip']   = $row_omni_details['website_arecord'];
									$omni_details[$row_omni_details['parentid']]['book_by_jd']  = (strtolower(trim($row_omni_details['own_cust_website'])) == 'no')?'YES':'NO';
									$omni_details[$row_omni_details['parentid']]['omni_type']  = ucfirst($row_omni_details['omni_type'])?ucfirst($row_omni_details['omni_type']):null;
									$omni_details[$row_omni_details['parentid']]['welcome_mail_sent'] = ($row_omni_details['welcome_date'] != '0000-00-00 00:00:00')?'YES':'NO';
									$omni_details[$row_omni_details['parentid']]['welcome_mail_date'] = $row_omni_details['welcome_date'];
									if($row_omni_details['storeid'] && $row_omni_details['supplier_id'] && $row_omni_details['website'] && $row_omni_details['website_arecord'] && ($row_omni_details['welcome_date'] != '0000-00-00 00:00:00'))
									{
										  $omni_details[$row_omni_details['parentid']]['omni_live'] = 1;
									}else
									{
										  $omni_details[$row_omni_details['parentid']]['omni_live'] = 0;
									}
									
									
									$omni_details[$row_omni_details['parentid']]['Dispositionid']   = $row_omni_details['omni_disposition_value'];
								}
							}
							
						 }
						
					 }
				
				if($this->status == 1)
				{
					$sql="select  a.parentid,companyname,a.entry_date,data_city ,a.version
										from payment_instrument_summary a join payment_clearance_details b
										on a.instrumentid=b.instrumentid join payment_otherdetails c 
										on a.parentid=c.parentid ".$extra_table." and a.version=c.version 
										JOIN payment_apportioning e ON a.parentid=e.parentid AND a.version=e.version 
										WHERE $search_cond ".$cheque_cond." AND ((a.version%10) in (2,3) OR a.campaign_type = 2) and b.finalapprovalflag=0 and approvalstatus=0 AND e.campaignid IN (72,73)
										GROUP BY e.parentid,e.version
										$city_condition $orderCondition";
					$res = parent::execQuery($sql, $this->fin_con);
				}			 
				if(in_array($this->status,array(2,3,4)))
				{
					$sql = " select a.parentid, a.companyname ,b.entry_date,finalapprovaldate,datacity,a.version,a.*
							 from payment_otherdetails a 
							 join 
							 (select parentid,data_city AS datacity,
							 SUBSTRING_INDEX(GROUP_CONCAT(VERSION ORDER BY finalapprovaldate DESC SEPARATOR '#'),'#',1) AS VERSION,
                             SUBSTRING_INDEX(GROUP_CONCAT(entry_date ORDER BY finalapprovaldate DESC SEPARATOR '#'),'#',1) AS entry_date,
                             SUBSTRING_INDEX(GROUP_CONCAT(campaign_type ORDER BY finalapprovaldate DESC SEPARATOR '#'),'#',1) AS campaign_type,
							 SUBSTRING_INDEX(GROUP_CONCAT(finalapprovaldate ORDER BY finalapprovaldate DESC SEPARATOR '#'),'#',1) AS finalapprovaldate
							 from payment_instrument_summary a join payment_clearance_details b 
							 on a.instrumentid=b.instrumentid ".$extra_table."
							 where b.finalapprovalflag=1 ".$cheque_cond." AND a.paymenttype='fresh' $city_condition
							 group by parentid) b JOIN payment_apportioning c 
							 on a.parentid=b.parentid and a.version=b.version AND b.parentid=c.parentid AND b.version=c.version
							 where $search_cond AND ((b.version%10) in (2,3) OR b.campaign_type = 2) ".$cs_app_cond." AND c.campaignid IN (72,73) AND c.budget>0
							 GROUP BY c.parentid";	
					$res = parent::execQuery($sql, $this->fin_con);
				}
					if(DEBUG_MODE)
					{
						echo '<br> sql:: '.$sql;
						echo '<br> res:: '.$res;
						echo '<br> num rows:: '.mysql_num_rows($res);
					}
					
					if($res && mysql_num_rows($res))
					{
						$i=0;
						while($row = mysql_fetch_assoc($res))
						{
							if($this->data_type == 1 || ($this->data_type == 3 && $omni_details[$row['parentid']]['omni_live']) || ($this->data_type == 2 && !$omni_details[$row['parentid']]['omni_live']))
							{
								if(!$this->omni_disp_status || ($this->omni_disp_status>0 && count($omni_details[$row['parentid']])))
								{
									$omini_approved_contracts[$i]['parentid'] 			= $row['parentid'];
									$omini_approved_contracts[$i]['companyname']		= $row['companyname'];
									$omini_approved_contracts[$i]['entry_date'] 		= $row['entry_date'];
									$omini_approved_contracts[$i]['approval_date']	    = $row['finalapprovaldate'];
									$omini_approved_contracts[$i]['version']		    = $row['version'];
									$omini_approved_contracts[$i]['omni_details']  		= $omni_details[$row['parentid']];
									$omini_approved_contracts[$i]['Dispositionid'][$row['parentid']] = $omni_details[$row['parentid']]['Dispositionid'];
									$i++;
								}
							}
						}
						
						if(DEBUG_MODE)
						{
							echo 'returning data list <pre> ';
							print_r($omini_approved_contracts);
						}
						
						if(count($omini_approved_contracts)>0)
						 { return $omini_approved_contracts; }
						else{
							$errorarray['errormsg']='No Live data in selected date range';
							$errorarray['errorcode']='-1';
							return $errorarray;
						}
						
					}else{
							$errorarray['errormsg']='No data in selected options';
							$errorarray['errorcode']='-1';
							return $errorarray;
					}
		 
			
	}
	
	
	function UpdateVersion()
	{
		 $sql="Update payment_otherdetails set csApprovalFlag='1',csApprovalDoneBy='".$this->usercode."',csApprovalDoneDate=now() where parentid='".$this->parentid."' and version='".$this->version."'";
		 $res1 = parent::execQuery($sql, $this->fin_con);
		 if(DEBUG_MODE)
					{
						echo '<br> sql:: '.$sql;
						echo '<br> res:: '.$res;
					}
		 
		 $sql="UPDATE tbl_companymaster_extradetails SET flgApproval=3,flgactive=1 WHERE parentid='".$this->parentid."'";
		 $res2 = parent::execQuery($sql, $this->local_con);
		 
		 if(DEBUG_MODE)
					{
						echo '<br> sql:: '.$sql;
						echo '<br> res:: '.$res;
					}
		if($res1 && $res2)
		{
			$errorarray['errormsg']='updated sucessfully';
			$errorarray['errorcode']='1';
			
		}else{
			$errorarray['errormsg']='not updated';
			$errorarray['errorcode']='-1';
		}
				return $errorarray;
	}
	
	function getOmniDespositions()
	{
		$sql_desposition = "SELECT disposition_main_id,disposition_sub_id,disposition FROM d_jds.Jd_omni_disposition";
		$res_desposition = parent::execQuery($sql_desposition, $this->local_con);
		 if(DEBUG_MODE)
					{
						echo '<br> sql:: '.$sql_desposition;
						echo '<br> res:: '.$res_desposition;
						echo '<br> mysql rows:: '.mysql_num_rows($res_desposition);
					}
		if($res_desposition && mysql_num_rows($res_desposition))
		{
			while($row_desposition = mysql_fetch_assoc($res_desposition))
			{
				  $desposition_arr[$row_desposition['disposition_main_id']][$row_desposition['disposition_sub_id']] = $row_desposition['disposition'];
			}
			
			return $desposition_arr;
		}
	}
	
	function updateOmniDesposition()
	{
		$sql_update_omni_idc  = "UPDATE online_regis1.tbl_omni_details_consolidated  
								 SET omni_disposition_value='".$this->omni_disposition_value."',omni_disposition_date='".date("Y-m-d H:i:s")."'
								 WHERE parentid='".$this->parentid."' AND data_city='".$this->data_city."'";
		$res_update_omni_idc  = parent::execQuery($sql_update_omni_idc, $this->idc_con);
		if(DEBUG_MODE)
		{
			echo '<br> sql:: '.$sql_update_omni_idc;
			echo '<br> res:: '.$res_update_omni_idc;
		}
		
		$sql_update_omni_fnc  = "UPDATE payment_otherdetails 
								SET omni_disposition_status='".$this->omni_disposition_value."'
								WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
		$res_update_omni_fnc = parent::execQuery($sql_update_omni_fnc, $this->fin_con);
		if(DEBUG_MODE)
		{
			echo '<br> sql:: '.$sql_update_omni_fnc;
			echo '<br> res:: '.$res_update_omni_fnc;
		}
		
		$sql_insert_log  	= "INSERT INTO d_jds.omni_disposition_log(parentid,disposition,disposition_on,version,updatedby) VALUES ('".$this->parentid."','".$this->omni_disposition_value."','".date("Y-m-d H:i:s")."','".$this->version."','".$this->usercode."')";
		$res_insert_log		= parent::execQuery($sql_insert_log, $this->local_con);
		if(DEBUG_MODE)
		{
			echo '<br> sql:: '.$sql_insert_log;
			echo '<br> res:: '.$res_insert_log;
		}
		
		if($res_update_omni_idc && $res_update_omni_fnc && $res_insert_log)
		{
			$errorarray['errormsg']='updated sucessfully';
			$errorarray['errorcode']='1';
			
		}else{
			$errorarray['errormsg']='not updated';
			$errorarray['errorcode']='-1';
		}
		return $errorarray;
		
	}

}


?>
