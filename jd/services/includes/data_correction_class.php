<?php
class data_correction_class extends DB
{
	var  $conn_iro    	= null;
	var  $conn_jds   	= null;
	var  $conn_tme 		= null;
	var  $conn_idc    	= null;
	var  $conn_fnc    	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	
	
	function __construct($params)
	{	
		global $params;
 		$parentid 			= trim($params['parentid']);
		$module 			= trim($params['module']);
		$data_city 			= trim($params['data_city']); 	
		$rquest 			= trim($params['rquest']); 
		$data 				= trim($params['data']); 
		/*if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->send_die_message($message));
            die();
        }*/
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(trim($module)=='')
		{
			$message = "Module is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}		 
		//$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->rquest  	  	= $rquest;
		$this->data			= $data;	
		$this->setServers();		 
		
		$this->source_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');		
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;			
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_iro_slave	= $db[$conn_city]['iro']['slave'];

		$this->data_correction			= $db[$conn_city]['data_correction']['master'];
		$this->data_correction_slave	= $db[$conn_city]['data_correction']['slave'];
		$this->conn_idc    		= $db[$conn_city]['idc']['master'];	
		
		/*$this->conn_national	= $db['db_national'];	
		$this->conn_webedit		= $db['web_edit'];	*/	
		//$this->source_city = $conn_city; 		
		
	}	
	function data_correction() {
		$func = $this->rquest;
		if((int)method_exists($this,$func) > 0)
			return $this->$func();
		else {
			$message = "invalid function";
			return json_encode($this->send_die_message($message));			
		}
	}	
	public function update_allocation()
	{
		global $params;
		
		$entered_date	= date("Y-m-d", strtotime($params['entered_date']));
		$e= explode("-",$entered_date);
		
		$year	= $e[0];
		$month	= $e[1];
		
		$ARR_DC_TABLES = $this->get_tables($year,$month);
		
		
		 
		$sql_allocated = "
		UPDATE 
			".$ARR_DC_TABLES['ALLOCATED_LOGS']." 
		SET 
			status			=	'".$params['status']."',
			grouping_date	=	'".$params['grouping_date']."'
		WHERE 
			parentid='".$params['parentid']."' AND entered_date = '".$params['entered_date']."' AND module_type = '".$params['module']."'";
		$res_allocated = parent::execQuery($sql_allocated, $this->data_correction); 
				 
		$sql_dialer = "
		UPDATE 
			".$ARR_DC_TABLES['DC_FOR_DIALER']." 
		SET 
			 status			=	'".$params['status']."',
			grouping_date	=	'".$params['grouping_date']."' 
		WHERE 
			parentid='".$params['parentid']."' AND entered_date = '".$params['entered_date']."' AND module_type = '".$params['module']."'";
		$res_dialer = parent::execQuery($sql_dialer, $this->data_correction);
		
		$insert_log =	"INSERT INTO db_data_correction.tbl_mongo_api_log
                            SET
                                parentid            =	'".$params['parentid']."',
                                entered_date        =	'".$params['entered_date']."',
                                module_type			=	'".$params['module']."',                               
                                update_allocation	=	'".json_encode($params)."'                               
                            ON DUPLICATE KEY UPDATE
                                update_allocation	=	'".json_encode($params)."'";     
		$res_insert_log = parent::execQuery($insert_log, $this->data_correction);	
		
		$return_arr = array("erroCode"=>"0", "errorStatus"=>"data_inserted");
		return  $return_arr;
		
	}
	public function update_accuracy_status()
	{
		global $params;
		
		$entered_date	= date("Y-m-d", strtotime($params['entered_date']));
		$e= explode("-",$entered_date);
		
		$year	= $e[0];
		$month	= $e[1];
		
		$ARR_DC_TABLES = $this->get_tables($year,$month);
				 
		$sql_allocated = "
		UPDATE 
			".$ARR_DC_TABLES['ALLOCATED_LOGS']." 
		SET 
			action_flag			=	'".$params['action_flag']."'
		WHERE 
			parentid='".$params['parentid']."' AND entered_date = '".$params['entered_date']."' AND module_type = '".$params['module']."'";
		$res_allocated = parent::execQuery($sql_allocated, $this->data_correction); 
		
		 
		$sql_dialer = "
		UPDATE 
			".$ARR_DC_TABLES['DC_FOR_DIALER']." 
		SET 
			 action_flag			=	'".$params['action_flag']."' 
		WHERE 
			parentid='".$params['parentid']."' AND entered_date = '".$params['entered_date']."' AND module_type = '".$params['module']."'";
		$res_dialer = parent::execQuery($sql_dialer, $this->data_correction);
		
		$insert_log =	"INSERT INTO db_data_correction.tbl_mongo_api_log
                            SET
                                parentid            =	'".$params['parentid']."',
                                entered_date        =	'".$params['entered_date']."',
                                module_type			=	'".$params['module']."',                               
                                update_accuracy_status	=	'".json_encode($params)."'                               
                            ON DUPLICATE KEY UPDATE
                                update_accuracy_status	=	'".json_encode($params)."'";     
		$res_insert_log = parent::execQuery($insert_log, $this->data_correction);	
		
		$return_arr = array("erroCode"=>"0", "errorStatus"=>"data_updated");
		return  $return_arr;		
	}
	
	function get_tables($year,$month)
	{
		define("DB_DATA_CORRECTION","db_data_correction");
		define("TBL_MODULE_TYPES","tbl_module_types");
		define("TBL_FIELD_PRICING","tbl_field_pricing_new");
		define("TBL_ALLOCATED_LOGS","tbl_tme_dc_logs_allocated_");
		define("TBL_FIELDWISE_AUDIT","tbl_fieldwise_data_correction_");
		define("TBL_PAYOUT_LOG","tbl_data_correction_payout_log_");
		define("TBL_DC_FOR_DIALER","tbl_dc_for_dialer_");
		define("TBL_USER_CONTRIBUTION","tbl_user_contribution_");
		define("TBL_UNALLOCATED_LOGS","tbl_tme_dc_logs_unallocated_");


		//global $ARR_DC_TABLES;

		$ARR_DC_TABLES['MODULE_TYPES'] 		= DB_DATA_CORRECTION. "." .TBL_MODULE_TYPES;
		$ARR_DC_TABLES['FIELD_PRICING'] 	= DB_DATA_CORRECTION. "." .TBL_FIELD_PRICING;
		$ARR_DC_TABLES['ALLOCATED_LOGS'] 	= DB_DATA_CORRECTION. "." .TBL_ALLOCATED_LOGS.$month."_".$year;
		$ARR_DC_TABLES['FIELDWISE_AUDIT'] 	= DB_DATA_CORRECTION. "." .TBL_FIELDWISE_AUDIT.$month."_".$year;
		$ARR_DC_TABLES['PAYOUT_LOG'] 		= DB_DATA_CORRECTION. "." .TBL_PAYOUT_LOG.$month."_".$year;
		$ARR_DC_TABLES['DC_FOR_DIALER'] 	= DB_DATA_CORRECTION. "." .TBL_DC_FOR_DIALER.$month."_".$year;
		$ARR_DC_TABLES['USER_CONTRIBUTION']	= DB_DATA_CORRECTION. "." .TBL_USER_CONTRIBUTION.$month."_".$year; 		
		$ARR_DC_TABLES['UNALLOCATED_LOGS']	= DB_DATA_CORRECTION. "." .TBL_UNALLOCATED_LOGS.$month."_".$year; 		
		
		return $ARR_DC_TABLES;
	}
	
	public function populate_dc_table()
	{	 	 
		global $params;
		if($params['trace'] == 1)
		{	
			echo "Input Parameters : ";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		$priority_field = '';
		$priority_flag = '9';
		$compData				= 	$this->GetCompData($params['parentid']); 
		//print_r($compData);
		$str_orig_details = addslashes(http_build_query($compData['data'],'','|~@~|'));
		$user_details = $str_orig_details;
		
		$priority='9';
		
		$insert_dc_table = "INSERT INTO db_data_correction.tbl_tme_dc_logs_allocated_13_2016
				SET
				sphinx_id				=	'".$compData['data']['sphinx_id']."',
				parentid				=	'".$compData['data']['parentid']."',
				entered_date			=	'".$params['entered_date']."',
				companymaster_details	=	'".addslashes($companymaster_details)."',
				data_city				=	'".$compData['data']['data_city']."',
				module_type				=	'".$params['module']."',
				paid					=	'".$compData['data']['paid']."',
				priority				=	'".$priority."',
				freeze					=	'".$compData['data']['freeze']."',
				mask					=	'".$compData['data']['mask']."',
				user_id					=	'".$params['tme_code']."',
				user_details			=	'".addslashes($user_details)."',
				dialer_flag				=	'1',
				tme_disposition			=	'".$params['tme_disposition']."'";
		$res_insert = parent::execQuery($insert_dc_table, $this->data_correction);		
			 
		 
		$zone 			 					=	$this->get_zone($compData['data']['data_city']);
		$arr_dialer_params['sphinx_id'] 	=	$compData['data']['sphinx_id'];
		$arr_dialer_params['parentid'] 		=	$compData['data']['parentid'];
		$arr_dialer_params['mobile'] 		=	$compData['data']['mobile'];
		$arr_dialer_params['landline'] 		=	$compData['data']['landline'];
		$arr_dialer_params['stdcode'] 		=	$compData['data']['stdcode'];
		$arr_dialer_params['entered_date'] 	=	$params['entered_date'];
		$arr_dialer_params['data_city'] 	=	$params['data_city'];
		$arr_dialer_params['priority_field']=	$priority_field;
		$arr_dialer_params['zone'] 			=	$zone;
		$arr_dialer_params['module_type'] 	=	$params['module'];
		$arr_dialer_params['priority'] 		=	$priority_flag;
		$arr_dialer_params['user_id'] 		=	$params['tme_code'];
		$arr_dialer_params['company_name'] 	=	$compData['data']['companyname'];
		$arr_dialer_params['pincode'] 		=	$compData['data']['pincode'];
		$arr_dialer_params['paid'] 			=	$compData['data']['paid'];
	 
		$this->push_data_into_dialer($arr_dialer_params);
		echo "success";
		 	
	} 
	public function GetCompData($parentid)
	{
	
		$sql = "SELECT cmg.*, cme.*  FROM tbl_companymaster_generalinfo cmg JOIN tbl_companymaster_extradetails cme on cmg.parentid = cme.parentid WHERE cmg.parentid='".$parentid."' LIMIT 1";
		$res = parent::execQuery($sql, $this->conn_iro);
		if($res && mysql_num_rows($res)>0)
		{
			$row=mysql_fetch_assoc($res);
			//return $row;
		}
		$ret['numrows'] =  mysql_num_rows($res);
		$ret['data'] 	=  $row;
		return $ret;		
	}
	public function get_zone($city)
	{
		$sql = "SELECT zone FROM db_data_correction.tbl_zone_for_dialer WHERE city='".$city."' LIMIT 1";
		$res = parent::execQuery($sql, $this->conn_iro);
		$row=mysql_fetch_assoc($res);
		return $row['zone'];
	}		 
	public function push_data_into_dialer($arr_params)
	{
		$mobiles	= $this->get_clean_value($arr_params['mobile'], ",");
		$landlines	= $this->get_clean_value($arr_params['landline'], ",");

		$c = count($mobiles);
		for($l=0; $l<$c; $l++)
		{
			$arr_dnc_m1[$l] = $mobiles[$l];
		}

		if(count($landlines) > 0 )
		{
			if($arr_params['stdcode'][0] != '0')
			{
				$std_code	= '0'.$arr_params['stdcode'];
			}
			else
			{
				$std_code	= $arr_params['stdcode'];
			}
		}

		$d = count($landlines);
		for($m=0; $m<$d; $m++)
		{
			$arr_dnc_l1[$m] = $std_code.$landlines[$m];
		}

		// making mobile str for insert in tbl_dc_for_dialer
		$str_f = '';
		$str_v = '';
		for($a=0; $a<$c; $a++)
		{
			if($a <= 3)
			{
				$b = $a + 1;
				$str_f .= "mobile_".$b. ",";
				$str_v .= '"' . $arr_dnc_m1[$a] . '",';
			}
		}

		// making landline str for insert in tbl_dc_for_dialer
		for($a=0; $a<$d; $a++)
		{
			if($a <= 3)
			{
				$b = $a + 1;
				$str_f .= "landline_".$b. ",";
				$str_v .= '"' . $arr_dnc_l1[$a] . '",';
			}
		}

		$ins_f = trim($str_f,",");
		$ins_v = trim($str_v,",");

		if(!empty($ins_f))
		{
			$ins_f1 = ",".$ins_f;
		}
		if(!empty($ins_v))
		{
			$ins_v1 = ",".$ins_v;
		}
		/************ checking for dnc numbers and adding 055 for dialer ends ***********/

		$parentid_dialer = $arr_params['sphinx_id'] . "." . str_replace(" ","-",str_replace(":","-",$arr_params['entered_date']));

		//$tbl_allocated_logs = $this->arr_data_corr_conn['TABLES']['ALLOCATED_LOGS'];
		$tbl_allocated_logs 		= 'db_data_correction.tbl_tme_dc_logs_allocated_13_2016';
		$tbl_dialer 		= 'db_data_correction.tbl_dc_for_dialer_13_2016';

		// insert into tbl_dc_for_dialer
		$sql3 = 'INSERT IGNORE INTO '.$tbl_dialer.' (sphinx_id, parentid, entered_date, parentid_dialer, stdcode, dialer_flag, data_city, priority_field, zone, module_type, user_id, priority, pincode, company_name, paid '.$ins_f1.') VALUES ('.$arr_params['sphinx_id'].',"'.$arr_params['parentid'].'", "'.$arr_params['entered_date'].'", "'.$parentid_dialer.'", "'.$std_code.'", "1", "'.$arr_params['data_city'].'", "'.$arr_params['priority_field'].'", "'.$arr_params['zone'].'", "'.$arr_params['module_type'].'", "'.$arr_params['user_id'].'", "'.$arr_params['priority'].'", "'.$arr_params['pincode'].'", "'.$arr_params['company_name'].'", "'.$arr_params['paid'].'"  '.$ins_v1.'  )';
		//$res3 = parent::execQuery($sql3, $this->data_correction);

		/*	
		// update dialer flag in allocated logs so that we know this data has been given to dialer
		$sql_upt_df = 'UPDATE '.$tbl_allocated_logs.' SET dialer_flag=1 WHERE sphinx_id="'.$arr_params['sphinx_id'].'" AND entered_date="'.$arr_params['entered_date'].'" AND module_type="'.$arr_params['module_type'].'"';
		//$res_upt_df = $this->exec_query($this->daco_link, $this->arr_data_corr_conn['DB'], $sql_upt_df);
		$res3 = parent::execQuery($sql3, $this->data_correction);*/
	}
	public function get_count()
	{
		global $params;
		$return_array = Array();
		if($params['trace'] == 1)
		{
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Purpose : To get DC counts\n";
			echo "\n--------------------------------------------------------------------------------------\n";
			echo "Input Parameters : \n";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
	
		$entered_date	= date("Y-m-d", strtotime($params['start_date']));
		$e= explode("-",$entered_date);
		
		$year	= $e[0];
		$month	= $e[1];
		$params['start_date'] = $params['start_date'] ." 00:00:00";
		$params['end_date'] = $params['end_date'] ." 23:59:59";
		$ARR_DC_TABLES = $this->get_tables($year,$month);		 
		if(strtoupper($params['module']) == 'TME')
		{			
			$sql_pending = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details,audited_date, counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND dialer_flag='1' AND module_type='TME' AND (status='20'  OR action_flag	= 2  OR action_flag	= 3) AND paid!=1 AND ((priority_field LIKE '%pincode%' OR priority_field LIKE '%area%' OR priority_field LIKE '%companyname%' OR priority_field LIKE '%mobile%' OR priority_field LIKE '%landline%'  OR priority_field LIKE '%tollfree%') OR priority_field is NULL ) AND correct = '2'  AND (disposition = '' OR disposition IS NULL) ORDER BY entered_date DESC) a GROUP BY a.parentid,a.grouping_date ";
			
			if(!(isset($params['source']) && $params['source'] == 'auditm'))
				$pending_cnt  = $this->get_result($sql_pending,'','data_correction');

			$sql_audited = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details, audited_date,counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE audited_date >= '".$params['start_date']."' AND audited_date <= '".$params['end_date']."' AND dialer_flag='1' AND module_type='TME' AND paid!=1  AND auditor_id != '' AND ((priority_field LIKE '%pincode%' OR priority_field LIKE '%area%' OR priority_field  LIKE '%companyname%' OR priority_field LIKE '%mobile%' OR priority_field LIKE '%landline%'  OR priority_field LIKE '%tollfree%') OR priority_field is NULL ) AND correct != '2' AND (disposition != '' AND disposition IS NOT null)) a GROUP BY a.parentid,a.grouping_date";
						 
			$audited_cnt  = $this->get_result($this->get_query($sql_audited,$year,$month),'','data_correction');
			
			$sql_followup = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details, audited_date,counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND dialer_flag='1' AND module_type='TME' AND paid!=1  and ((priority_field LIKE '%pincode%' AND priority_field LIKE '%area%' OR priority_field  LIKE '%companyname%' OR priority_field LIKE '%mobile%' OR priority_field LIKE '%landline%'  OR priority_field LIKE '%tollfree%') OR priority_field is NULL ) AND  correct = '2' AND (disposition != '' AND disposition IS NOT null)) a GROUP BY a.parentid,a.grouping_date";
			if(!(isset($params['source']) && $params['source'] == 'auditm'))
				$followup_cnt  = $this->get_result($sql_followup,'','data_correction');
			
			$return_array['module']		=	$params['module'];	
			if(!(isset($params['source']) && $params['source'] == 'auditm'))
			{	
				$return_array['pending']	=	$pending_cnt;					
				$return_array['followup']	=	$followup_cnt;			
			}	
			$return_array['audited']	=	$audited_cnt;					
			
		}
		else if(strtoupper($params['module']) == 'TOP_LISTING_OTHERS')
		{
			$sql_pending = " SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details, audited_date,counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."'  AND entered_date <= '".$params['end_date']."' AND dialer_flag='1' AND module_type='TME' AND paid!=1 AND ((priority_field NOT LIKE '%pincode%' AND priority_field NOT LIKE '%area%' AND priority_field NOT LIKE '%companyname%' AND priority_field NOT LIKE '%mobile%' AND priority_field NOT LIKE '%landline%'  AND priority_field NOT LIKE '%tollfree%'  AND priority_field  LIKE '%top_five_listing%'  ) OR priority_field is  NULL ) AND correct = '2' AND (disposition = '' OR disposition IS NULL) ORDER BY entered_date DESC) a GROUP BY a.parentid ORDER BY entered_date";
			if(!(isset($params['source']) && $params['source'] == 'auditm'))
				$pending_cnt  = $this->get_result($sql_pending,'','data_correction');
			
			$sql_audited = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details, audited_date,counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE audited_date >= '".$params['start_date']."' AND audited_date <= '".$params['end_date']."' AND dialer_flag='1' AND module_type='TME' AND paid!=1  AND auditor_id != '' AND ((priority_field NOT LIKE '%pincode%' AND priority_field NOT LIKE '%area%' AND priority_field NOT LIKE '%companyname%' AND priority_field NOT LIKE '%mobile%' AND priority_field NOT LIKE '%landline%'  AND priority_field NOT LIKE '%tollfree%' AND priority_field  LIKE '%top_five_listing%') OR priority_field is NULL ) AND correct != '2' AND (disposition != '' AND disposition IS NOT null)  ORDER BY entered_date DESC) a GROUP BY a.parentid,entered_date";
			 
			 
			$audited_cnt  = $this->get_result($this->get_query($sql_audited,$year,$month,'','data_correction'));
						
			$sql_followup = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details,audited_date,audited_date, counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND dialer_flag='1'  AND module_type='TME'  AND paid!=1  AND ((priority_field NOT LIKE '%pincode%' AND priority_field NOT LIKE '%area%' AND priority_field NOT LIKE '%companyname%' AND priority_field NOT LIKE '%mobile%' AND priority_field NOT LIKE '%landline%'  AND priority_field NOT LIKE '%tollfree%'  AND priority_field  LIKE '%top_five_listing%'  ) OR priority_field is NULL ) AND correct = '2' AND (disposition != '' AND disposition IS NOT null) ORDER BY entered_date DESC) a GROUP BY a.parentid ORDER BY entered_date";			
			if(!(isset($params['source']) && $params['source'] == 'auditm'))			
				$followup_cnt  = $this->get_result($sql_followup,'','data_correction');
			
			$return_array['module']		=	$params['module'];		
			if(!(isset($params['source']) && $params['source'] == 'auditm'))	
			{
				$return_array['pending']	=	$pending_cnt;					
				$return_array['followup']	=	$followup_cnt;
			}	
			$return_array['audited']	=	$audited_cnt;					
			
		
		}
		else if(strtoupper($params['module']) == 'TMEMASK')
		{	 
			$sql_pending = " SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details, audited_date,counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='TMEMASK' AND paid!=1  AND correct = '2' AND (disposition = '' OR disposition IS NULL) ORDER BY entered_date DESC) a GROUP BY a.parentid ORDER BY entered_date";
			if(!(isset($params['source']) && $params['source'] == 'auditm'))
				$pending_cnt  = $this->get_result($sql_pending,'','data_correction');
						 
			$sql_audited_active = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id/*,user_details,auditor_details*/, audited_date,counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='TMEMASK' AND paid!=1 AND user_details !='' AND auditor_id != '' AND correct != '2' AND (disposition != '' AND disposition IS NOT null) AND disposition NOT IN ('Not Interested','Duplicate(Mask)','Invalid data', 'Other City Data','5 Attempts Exceeded') AND auditor_id != '' ORDER BY entered_date DESC) a GROUP BY a.parentid,a.entered_date ";
			
			$audited_active_cnt  = $this->get_result($this->get_query($sql_audited_active,$year,$month),'','data_correction');
			
			$sql_followup = " SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details,audited_date, counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='TMEMASK' AND paid!=1  AND correct = '2' AND (disposition != '' AND disposition IS NOT null) ORDER BY entered_date DESC) a GROUP BY a.parentid,a.entered_date ORDER BY entered_date";	
			if(!(isset($params['source']) && $params['source'] == 'auditm'))						
				$followup_cnt  = $this->get_result($sql_followup,'','data_correction');
			 
			$sql_audited_mask = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details, audited_date,counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='TMEMASK' AND paid!=1  AND auditor_id != '' AND correct != '2' AND (disposition != '' AND disposition IS NOT null) AND disposition IN ('Not Interested','Duplicate(Mask)','Invalid data', 'Other City Data','5 Attempts Exceeded') AND auditor_id != '' ORDER BY entered_date DESC) a GROUP BY a.parentid,a.entered_date";
			$audited_mask_cnt  = $this->get_result($this->get_query($sql_audited_mask,$year,$month),'','data_correction'); 
			
			$return_array['module']			=	$params['module'];	
			if(!(isset($params['source']) && $params['source'] == 'auditm'))			
			{
				$return_array['pending']		=	$pending_cnt;					
				$return_array['followup']		=	$followup_cnt;	
			}	
			$return_array['audited_active']	=	$audited_active_cnt;					
			
			$return_array['audited_mask']	=	$audited_mask_cnt;
			 
			 	
		} 
		else if(strtoupper($params['module']) == 'TME_NEWBUS' || strtoupper($params['module']) == 'TME_SAVENP' || strtoupper($params['module']) == 'BADWORD' || strtoupper($params['module']) == 'TOP_LISTING'  || strtoupper($params['module']) == 'JF_DUPLICATE_DATA' || strtoupper($params['module']) == 'CLICKANDEARN' || strtoupper($params['module']) == 'JDA_CF' || /*strtoupper($params['module']) == 'UNIVERSAL_RULE' || strtoupper($params['module']) == 'JDA_ME_DATA_AUDIT' || */ strtoupper($params['module']) == 'FIVE_PLUS_MOBILE' || strtoupper($params['module']) == 'OVERWRITTEN_DATA')
		{	
			$sql_pending = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details,audited_date, counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='".$params['module']."' AND   paid!=1  AND correct = '2' AND (disposition = '' OR disposition IS NULL) ORDER BY entered_date DESC) a GROUP BY a.parentid ORDER BY entered_date ";
			if(!(isset($params['source']) && $params['source'] == 'auditm'))		 
				$pending_cnt  = $this->get_result($sql_pending,'','data_correction');
			
		 	$sql_audited_active = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id/*,user_details,auditor_details*/, audited_date,counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='".$params['module']."' AND paid!=1  AND auditor_id != '' AND correct != '2' AND (disposition != '' AND disposition IS NOT null) AND disposition NOT IN ('Not Interested','Duplicate(Mask)','Invalid data', 'Other City Data','5 Attempts Exceeded') AND auditor_id != '' ORDER BY entered_date DESC) a GROUP BY a.parentid ";
			$audited_active_cnt  = $this->get_result($this->get_query($sql_audited_active,$year,$month),'','data_correction'); 
			
			//echo "\n\n\n".
			$sql_followup = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details, audited_date,counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='".$params['module']."' /*AND dialer_flag='1' */ AND paid!=1  AND correct = '2' AND (disposition != '' AND disposition IS NOT null) ORDER BY entered_date DESC) a GROUP BY a.parentid ORDER BY entered_date "; 
			if(!(isset($params['source']) && $params['source'] == 'auditm'))				
				$followup_cnt  = $this->get_result($sql_followup,'','data_correction'); 		
			 
			$sql_audited_mask = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details, audited_date,counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='".$params['module']."' AND paid!=1  AND auditor_id != '' AND correct != '2' AND (disposition != '' AND disposition IS NOT null) AND disposition IN ('Not Interested','Duplicate(Mask)','Invalid data', 'Other City Data','5 Attempts Exceeded') AND auditor_id != '' ORDER BY entered_date DESC) a GROUP BY a.parentid";
			
			$audited_mask_cnt  = $this->get_result($this->get_query($sql_audited_mask,$year,$month),'','data_correction'); 
			
			$return_array['module']			=	$params['module'];			
			if(!(isset($params['source']) && $params['source'] == 'auditm'))			
			{
				$return_array['pending']		=	$pending_cnt;					
				$return_array['followup']		=	$followup_cnt;
			}	
			$return_array['audited_active']	=	$audited_active_cnt;					
			$return_array['audited_mask']	=	$audited_mask_cnt;
		}
		else if(strtoupper($params['module']) == 'JDA_ME_DATA_AUDIT')		
		{	
			$sql_pending = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details,audited_date, counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='".$params['module']."' AND   paid!=1  AND correct = '2' AND (disposition = '' OR disposition IS NULL) AND (priority_field  LIKE '%catidlineage%' OR priority_field  ='' OR priority_field LIKE '%companyname%' OR priority_field LIKE '%mobile%' OR priority_field   LIKE '%landline%' OR priority_field  LIKE '%tollfree%' OR priority_field   LIKE '%pincode%' OR priority_field  LIKE '%area%') ORDER BY entered_date DESC) a GROUP BY a.parentid ORDER BY entered_date ";
			if(!(isset($params['source']) && $params['source'] == 'auditm'))		 
				$pending_cnt  = $this->get_result($sql_pending,'','data_correction');
			
		 	$sql_audited_active = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id/*,user_details,auditor_details*/, audited_date,counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='".$params['module']."' AND paid!=1  AND auditor_id != '' AND correct != '2' AND (disposition != '' AND disposition IS NOT null) AND disposition NOT IN ('Not Interested','Duplicate(Mask)','Invalid data', 'Other City Data','5 Attempts Exceeded') AND auditor_id != '' AND (priority_field  LIKE '%catidlineage%' OR priority_field  ='' OR priority_field LIKE '%companyname%' OR priority_field LIKE '%mobile%' OR priority_field   LIKE '%landline%' OR priority_field  LIKE '%tollfree%' OR priority_field   LIKE '%pincode%' OR priority_field  LIKE '%area%') ORDER BY entered_date DESC) a GROUP BY a.parentid ";
			$audited_active_cnt  = $this->get_result($this->get_query($sql_audited_active,$year,$month),'','data_correction'); 
			
			//echo "\n\n\n".
			$sql_followup = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details, audited_date,counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='".$params['module']."' /*AND dialer_flag='1' */ AND paid!=1  AND correct = '2' AND (disposition != '' AND disposition IS NOT null) AND (priority_field  LIKE '%catidlineage%' OR priority_field  ='' OR priority_field LIKE '%companyname%' OR priority_field LIKE '%mobile%' OR priority_field   LIKE '%landline%' OR priority_field  LIKE '%tollfree%' OR priority_field   LIKE '%pincode%' OR priority_field  LIKE '%area%') ORDER BY entered_date DESC) a GROUP BY a.parentid ORDER BY entered_date "; 
			if(!(isset($params['source']) && $params['source'] == 'auditm'))				
				$followup_cnt  = $this->get_result($sql_followup,'','data_correction'); 		
			 
			$sql_audited_mask = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details, audited_date,counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='".$params['module']."' AND paid!=1  AND auditor_id != '' AND correct != '2' AND (disposition != '' AND disposition IS NOT null) AND disposition IN ('Not Interested','Duplicate(Mask)','Invalid data', 'Other City Data','5 Attempts Exceeded') AND auditor_id != '' AND (priority_field  LIKE '%catidlineage%' OR priority_field  ='' OR priority_field LIKE '%companyname%' OR priority_field LIKE '%mobile%' OR priority_field   LIKE '%landline%' OR priority_field  LIKE '%tollfree%' OR priority_field   LIKE '%pincode%' OR priority_field  LIKE '%area%') ORDER BY entered_date DESC) a GROUP BY a.parentid";
			
			$audited_mask_cnt  = $this->get_result($this->get_query($sql_audited_mask,$year,$month),'','data_correction'); 
			
			$return_array['module']			=	$params['module'];			
			if(!(isset($params['source']) && $params['source'] == 'auditm'))			
			{
				$return_array['pending']		=	$pending_cnt;					
				$return_array['followup']		=	$followup_cnt;
			}	
			$return_array['audited_active']	=	$audited_active_cnt;					
			$return_array['audited_mask']	=	$audited_mask_cnt;
		}		
		else if(strtoupper($params['module']) == 'UNIVERSAL_RULE')
		{
			//$sub_module_type = Array('COMPANY_LENGTH','REPEATED_WORDS');
			$sub_module_type = Array('COMPANY_LENGTH','REPEATED_WORDS','COMPANY_PREFIX','SINGLE_WORD_COMPANY'/*,'MULTIPARENTAGE','HOMEKEY_CHARACTER','WITHIN_BRACKET','SAVE_EXIT_UNIVERSAL','BACKEND_DATA'*/);
			//$sub_module_type = Array('SINGLE_WORD_COMPANY');

			foreach($sub_module_type AS $sub_module)
			{
				$sql_pending = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details,audited_date, counter,data_city,status,action_flag,grouping_date,priority_field,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='".$params['module']."' AND paid!=1  AND correct = '2' AND (disposition = '' OR disposition IS NULL) AND priority_field = '".$sub_module."' ORDER BY entered_date DESC) a GROUP BY a.parentid ORDER BY entered_date ";
				if(!(isset($params['source']) && $params['source'] == 'auditm'))		 
					$pending_cnt  = $this->get_result($sql_pending,'','data_correction');
				
				 
				//$sql_audited_active = " SELECT * FROM ( SELECT x1.*, cs.subsource, cs.datesource FROM ( SELECT * FROM ( SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details,audited_date,counter,data_city,status,action_flag,priority_field,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE audited_date >= '".$params['start_date']."' AND audited_date <= '".$params['end_date']."'  AND dialer_flag='1'  AND module_type='".$params['module']."'  AND paid!=1  AND auditor_id != ''  ORDER BY sphinx_id, entered_date DESC) as dc GROUP BY sphinx_id ) as x1  LEFT JOIN d_jds.tbl_company_source cs ON x1.parentid = cs.parentid  WHERE  correct != '2'  AND (disposition != '' AND disposition IS NOT null) AND disposition NOT IN ('Not Interested','Duplicate(Mask)','Invalid data', 'Other City Data','5 Attempts Exceeded')   AND priority_field = '".$sub_module."' AND auditor_id != ''  ORDER BY datesource ASC) pq GROUP BY parentid ";
				
 				$sql_audited_active = " SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id/*,user_details,auditor_details*/, audited_date,counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='".$params['module']."' AND paid!=1  AND auditor_id != '' AND user_details !='' AND correct != '2' AND (disposition != '' AND disposition IS NOT null) AND disposition NOT IN ('Not Interested','Duplicate(Mask)','Invalid data', 'Other City Data','5 Attempts Exceeded') AND priority_field = '".$sub_module."' ORDER BY entered_date DESC) a GROUP BY a.parentid,  a.entered_date  ";
				
				$audited_active_cnt  = $this->get_result($this->get_query($sql_audited_active,$year,$month),'','data_correction'); 
				
				 
				$sql_followup = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details, audited_date,counter,data_city,status,action_flag,grouping_date,priority_field,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='".$params['module']."' AND paid!=1  AND correct = '2' AND (disposition != '' AND disposition IS NOT null)  AND priority_field = '".$sub_module."' ORDER BY entered_date DESC) a GROUP BY a.parentid ORDER BY entered_date ";
				if(!(isset($params['source']) && $params['source'] == 'auditm'))				
					$followup_cnt  = $this->get_result($sql_followup,'','data_correction'); 
			
				$sql_audited_mask = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details, audited_date,counter,data_city,status,action_flag,grouping_date,priority_field,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='".$params['module']."' AND paid!=1  AND auditor_id != '' AND correct != '2' AND (disposition != '' AND disposition IS NOT null) AND disposition IN ('Not Interested','Duplicate(Mask)','Invalid data', 'Other City Data','5 Attempts Exceeded') AND auditor_id != ''  AND priority_field = '".$sub_module."' ORDER BY entered_date DESC) a GROUP BY a.parentid,a.entered_date ";
				
				$audited_mask_cnt  = $this->get_result($this->get_query($sql_audited_mask,$year,$month),'','data_correction'); 
				
				$return_array['module']			=	$params['module'];			
				if(!(isset($params['source']) && $params['source'] == 'auditm'))			
				{
					$return_array[$sub_module]['pending']		=	$pending_cnt;					
					$return_array[$sub_module]['followup']		=	$followup_cnt;
				}	
				$return_array[$sub_module]['audited_active']	=	$audited_active_cnt;					
				$return_array[$sub_module]['audited_mask']	=	$audited_mask_cnt;
			}	
		}
		else if(strtoupper($params['module']) == 'TME_FBK')
		{
			$feedback_disposition_arr = Array('12','7','98');
			 

			foreach($feedback_disposition_arr AS $tme_disposition)
			{
				switch($tme_disposition)
				{
					case '12' : $sub_module ='company_closed';break;
					case '7'  : $sub_module ='wrong_number';break;
					case '98' : $sub_module ='not_in_business';break;
				}
				$sql_pending = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details,audited_date, counter,data_city,status,action_flag,grouping_date,priority_field,tme_disposition,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='".$params['module']."'  AND paid!=1  AND correct = '2' AND (disposition = '' OR disposition IS NULL) AND tme_disposition = '".$tme_disposition."' ORDER BY entered_date DESC) a GROUP BY a.parentid ORDER BY entered_date ";
				
				if(!(isset($params['source']) && $params['source'] == 'auditm'))		 
					$pending_cnt  = $this->get_result($sql_pending,'','data_correction');
				
			 	//$sql_audited_active = " SELECT * FROM ( SELECT x1.*, cs.subsource, cs.datesource FROM ( SELECT * FROM ( SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details,audited_date,counter,data_city,status,action_flag,priority_field,tme_disposition,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE audited_date >= '".$params['start_date']."' AND audited_date <= '".$params['end_date']."'     AND module_type='".$params['module']."'  AND paid!=1  AND auditor_id != ''  ORDER BY sphinx_id, entered_date DESC) as dc GROUP BY sphinx_id ) as x1  LEFT JOIN d_jds.tbl_company_source cs ON x1.parentid = cs.parentid  WHERE  correct != '2'  AND (disposition != '' AND disposition IS NOT null) AND disposition NOT IN ('Not Interested','Duplicate(Mask)','Invalid data', 'Other City Data','5 Attempts Exceeded')   AND tme_disposition = '".$tme_disposition."' AND auditor_id != ''  ORDER BY datesource ASC) pq GROUP BY parentid ";
			 	$sql_audited_active = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id/*,user_details,auditor_details*/, audited_date,counter,data_city,status,action_flag,grouping_date,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='".$params['module']."' AND paid!=1  AND auditor_id != '' AND correct != '2' AND (disposition != '' AND disposition IS NOT null) AND disposition NOT IN ('Not Interested','Duplicate(Mask)','Invalid data', 'Other City Data','5 Attempts Exceeded') AND auditor_id != '' AND tme_disposition = '".$tme_disposition."' ORDER BY entered_date DESC) a GROUP BY a.parentid";
				
				$audited_active_cnt  = $this->get_result($this->get_query($sql_audited_active,$year,$month),'','data_correction'); 
				
				 
				$sql_followup = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details, audited_date,counter,data_city,status,action_flag,grouping_date,priority_field,tme_disposition,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE entered_date >= '".$params['start_date']."' AND entered_date <= '".$params['end_date']."' AND module_type='".$params['module']."' AND paid!=1  AND correct = '2' AND (disposition != '' AND disposition IS NOT null)  AND tme_disposition = '".$tme_disposition."' ORDER BY entered_date DESC) a GROUP BY a.parentid ORDER BY entered_date ";
				if(!(isset($params['source']) && $params['source'] == 'auditm'))				
					$followup_cnt  = $this->get_result($sql_followup,'','data_correction'); 
			
				$sql_audited_mask = "SELECT * FROM (SELECT sphinx_id, parentid, company_name, entered_date, paid, user_id, disposition, correct, comments, auditor_id,user_details,auditor_details, audited_date,counter,data_city,status,action_flag,grouping_date,priority_field,tme_disposition,zone FROM ".$ARR_DC_TABLES['ALLOCATED_LOGS']." WHERE audited_date >= '".$params['start_date']."' AND audited_date <= '".$params['end_date']."' AND module_type='".$params['module']."' AND paid!=1  AND auditor_id != '' AND correct != '2' AND (disposition != '' AND disposition IS NOT null) AND disposition IN ('Not Interested','Duplicate(Mask)','Invalid data', 'Other City Data','5 Attempts Exceeded') AND auditor_id != ''  AND tme_disposition = '".$tme_disposition."' ORDER BY entered_date DESC) a GROUP BY a.parentid";
				
				$audited_mask_cnt  = $this->get_result($this->get_query($sql_audited_mask,$year,$month),'','data_correction'); 
				
				$return_array['module']			=	$params['module'];			
				if(!(isset($params['source']) && $params['source'] == 'auditm'))			
				{
					$return_array[$sub_module]['pending']		=	$pending_cnt;					
					$return_array[$sub_module]['followup']		=	$followup_cnt;
				}	
				$return_array[$sub_module]['audited_active']	=	$audited_active_cnt;					
				$return_array[$sub_module]['audited_mask']	=	$audited_mask_cnt;
			}			
		}
		else if(strtoupper($params['module']) == 'F9_COMP_NOT_FOUND')
		{
			$status_arr = Array('D','T','C','R');
			foreach($status_arr As $key=>$value)	
			{
				switch($value)
				{
					case 'D' : $sub_module ='done';break;
					case 'T' : $sub_module ='tried';break;
					case 'C' : $sub_module ='tagged';break;
					case 'R' : $sub_module ='repeated';break;					
				}
				$sql ="SELECT * FROM db_iro.srchNFnd WHERE reason ='Company Not Found' and status='".$value."'  and curdttm>='".$params['start_date']."' AND curdttm<='".$params['end_date']."' GROUP BY searchStr,areastr,status";
				$data  = $this->get_result($sql); 
				$return_array[$sub_module]  = $data;				
			}
		}	
		else if(strtoupper($params['module']) == 'F9_CATEGORY_NOT_FOUND')
		{
			$status_arr = Array('6','7','8');
			foreach($status_arr As $key=>$value)	
			{
				switch($value)
				{
					case '6' : $sub_module ='done';break;
					case '7' : $sub_module ='tried';break;
					case '8' : $sub_module ='tagged';break;					
				}
				$sql ="SELECT id,created_as,requested_by,city_name,iro_status,date_approved, count(*) as cnt, is_popular, iro_comments,dept FROM d_jds.tbl_category_creation_request where iro_status ='".$value."' AND date_approved>='".$params['start_date']."' AND date_approved<='".$params['end_date']."' GROUP BY created_as,iro_status";
				 
				$data  = $this->get_result($sql); 
				$return_array[$sub_module]  = $data;	
			
			}		 
		}		
		else if(strtoupper($params['module']) == 'F9_IRO_DC')		
		{
			$status_arr = Array('N','Y','C','T','E');
			foreach($status_arr As $key=>$value)	
			{
				switch($value)
				{
					case 'N' : $sub_module ='pending';break;
					case 'Y' : $sub_module ='done';break;
					case 'C' : $sub_module ='tagged';break;
					case 'T' : $sub_module ='tried';break;					
					case 'E' : $sub_module ='escalated_to_cs';break;					
				}
				$contract_status = Array('1'=>'paid','0'=>'nonpaid');
				foreach($contract_status as $key=>$paid)
				{
					$sql ="SELECT * from db_iro.repChngData where contractcode<>'' and done_flag ='".$value."' and updatedate>='".$params['start_date']."' AND updatedate<='".$params['end_date']."' AND paid='".$key."' GROUP BY contractcode,done_flag";
					$data  = $this->get_result($sql); 
					$return_array[$sub_module][$paid]  = $data;	
				}
			}
		}
		else if(strtoupper($params['module']) == 'F9_IRO_DC_FK_F12')		
		{
			$status_arr = Array('Y','C','T','E');
			//$status_arr = Array('Y');
			foreach($status_arr As $key=>$value)	
			{
				switch($value)
				{
					case 'Y' : $sub_module ='done';break;
					case 'C' : $sub_module ='tagged';break;
					case 'T' : $sub_module ='tried';break;					
					case 'E' : $sub_module ='escalated_to_cs';break;					
				}
				$contract_status = Array('1'=>'paid','0'=>'nonpaid');
				foreach($contract_status as $key=>$paid)
				{
					$comments_arr = Array('auto entry(f12 contract deactivation)','auto entry(f12 contract deactivation - closed down)','auto entry(f12 contract deactivation - not in business)','auto entry(f12 contract deactivation - remove listing)','auto entry(f12 contract deactivation - invalid number)');

					foreach($comments_arr AS $kk=>$comment)
					{
						/*$sql ="SELECT * from db_iro.repChngData where contractcode<>'' and done_flag ='".$value."' and updatedate>='".$params['start_date']."' AND updatedate<='".$params['end_date']."' AND paid='".$key."' AND comments='".$comment."' GROUP BY contractcode,done_flag";
						*/
						$sql	=	"SELECT autoid,contractcode,'".$params['start_date']."' as from_date,'".$params['end_date']."' as end_date,GROUP_CONCAT(iroCode ORDER BY done_date DESC) AS iroCode,business,phone,a.area,a.city,state_name,comments as iro_comment,comment2 as dbe_comment,GROUP_CONCAT(updatedate ORDER BY updatedate DESC) AS updatedate,SUM(updatecount) AS updatecnt,GROUP_CONCAT(done_flag ORDER BY updatedate DESC) AS done_flag,GROUP_CONCAT(done_by_cse ORDER BY updatedate DESC) AS done_by_cse,tagged_count,a.paid,starCompany,a.virtualNumber,GROUP_CONCAT(reason ORDER BY updatedate DESC) AS reason,custtype,businesstags,a.company_callcnt,invalid_detail,IF(invalid_detail != '' && flag&1=1,1,0) AS cli,IF(reason='Company closed down',1,0) AS compclosed,dialer_status FROM db_iro.repChngData a join db_iro.tbl_companymaster_generalinfo b on a.contractcode=b.parentid WHERE updatedate   >= '".$params['start_date']."' and updatedate <= '".$params['end_date']."' AND a.paid='".$key."' AND done_flag ='".$value."' AND tagged_count <='4' AND comments='".$comment."'  GROUP BY contractcode,done_flag   ORDER BY cli DESC, compclosed DESC,updatecnt DESC,company_callcnt DESC ";
						$data  = $this->get_result($sql); 
						$return_array[$sub_module][$paid][$comment]  = $data;
					}
					 
					$reason_arr = Array('personal number','fake listing');	
					$reason_arr = Array('personal number');	
					foreach($reason_arr AS $k=>$reason)
					{
						/*echo "\n\n\n".$sql ="SELECT * from db_iro.repChngData where contractcode<>'' and done_flag ='".$value."' and updatedate>='".$params['start_date']."' AND updatedate<='".$params['end_date']."' AND paid='".$key."' AND reason='".$reason."' GROUP BY contractcode,done_flag";
						$data  = $this->get_result($sql); 
						$return_array[$sub_module][$paid][$reason]  = $data;
						*/
						
						//echo "\n\n".
						$sql	=	"SELECT autoid,contractcode,'".$params['start_date']."' as from_date,'".$params['end_date']."' as end_date,GROUP_CONCAT(iroCode ORDER BY done_date DESC) AS iroCode,business,phone,a.area,a.city,state_name,comments as iro_comment,comment2 as dbe_comment,GROUP_CONCAT(updatedate ORDER BY updatedate DESC) AS updatedate,SUM(updatecount) AS updatecnt,GROUP_CONCAT(done_flag ORDER BY updatedate DESC) AS done_flag,GROUP_CONCAT(done_by_cse ORDER BY updatedate DESC) AS done_by_cse,tagged_count,a.paid,starCompany,a.virtualNumber,GROUP_CONCAT(reason ORDER BY updatedate DESC) AS reason,custtype,businesstags,a.company_callcnt,invalid_detail,IF(invalid_detail != '' && flag&1=1,1,0) AS cli,IF(reason='Company closed down',1,0) AS compclosed,dialer_status FROM db_iro.repChngData a join db_iro.tbl_companymaster_generalinfo b on a.contractcode=b.parentid WHERE updatedate   >= '".$params['start_date']."' and updatedate <= '".$params['end_date']."' AND a.paid='".$key."' AND done_flag ='".$value."' AND tagged_count <='4' AND reason='".$reason."' GROUP BY contractcode,done_flag   ORDER BY cli DESC, compclosed DESC,updatecnt DESC,company_callcnt DESC ";
						$data  = $this->get_result($sql); 
						$return_array[$sub_module][$paid][$reason]  = $data;
					}	
				}
			}
		}
		else if(strtoupper($params['module']) == 'BRANDNAME')		
		{
			$status_arr = Array('0','2');
			foreach($status_arr As $key=>$value)	
			{
				switch($value)
				{
					case '0' : $sub_module ='approved';break;
					case '2' : $sub_module ='rejected';break;
					
				}
				$contract_status = Array('1'=>'paid','0'=>'nonpaid');
				foreach($contract_status as $key=>$paid)
				{
					$sql ="SELECT * FROM db_iro.tbl_company_brandname_audit WHERE done_flag  in ('0','2') AND creationdate>='".$params['start_date']."' AND creationdate<='".$params['end_date']."' AND paid_status='".$key."' ";
					$data  = $this->get_result($sql); 
					$return_array[$sub_module][$paid]  = $data;	
				}
			}
		}		
		else if(strtoupper($params['module']) == 'PREMIUM_AUDIT')		
		{
			$status_arr = Array('1','2','3','4');
			foreach($status_arr As $key=>$value)	
			{
				switch($value)
				{
					case '1' : $sub_module ='approved';break;
					case '2' : $sub_module ='Rejected';break;
					case '3' : $sub_module ='escalate_to_cs';break;
					case '4' : $sub_module ='follow_up';break;
					
				}
				$contract_status = Array('1'=>'paid','0'=>'nonpaid');
				foreach($contract_status as $key=>$paid)
				{
					if($key == '1')
						$pd= " AND c.paid = 1 ";
					else if($key == '0')	
						$pd= " AND (c.paid = 0 OR c.paid is null) ";
					$sql ="select a.companyname,a.parentid,a.username,a.updatetime,a.Dept,a.City,a.paid_status,group_concat(catids) FROM d_jds.tbl_premium_categories_audit a JOIN tbl_companymaster_extradetails b JOIN tbl_companymaster_generalinfo c ON a.parentid=b.parentid AND a.parentid=c.parentid WHERE a.paid_status='".$key."' AND a.approval_status ='".$value."'   ".$pd." AND updatetime>='".$params['start_date']."' AND updatetime<='".$params['end_date']."' GROUP BY a.parentid";
					 
					$data  = $this->get_result($sql); 
					$return_array[$sub_module][$paid]  = $data;	
				}
			}
		}
		else if(strtoupper($params['module']) == 'CSESCALATIONS')		
		{
			$status_arr = Array('1','2','3');
			foreach($status_arr As $key=>$value)	
			{
				switch($value)
				{
					case '1' : $sub_module ='done';break;
					case '2' : $sub_module ='pending';break;
 					case '3' : $sub_module ='follow_up';break;					
				}				
				$sql ="SELECT *FROM db_iro.tbl_escalate_to_DB WHERE  updatedate>='".$params['start_date']."' AND updatedate<='".$params['end_date']."' AND STATUS = '".$value."'";				
				 
				$data  = $this->get_result($sql); 
				$return_array[$sub_module]  = $data;	
				 
			}
		}
		else if(strtoupper($params['module']) == 'SINGLEFORM')		
		{
			$sql_mod = "SELECT DISTINCT module_type FROM d_jds.tbl_single_bform_audit_data WHERE module_type!='TEST'";
			$res_mod = parent::execQuery($sql_mod, $this->conn_iro_slave);	
			$numRows = mysql_num_rows($res_mod);
			if($res_mod && $numRows>0)
			{
				while($row_mod = mysql_fetch_assoc($res_mod))
				{
					$module_array[] = $row_mod['module_type'];
				}
			}	
			
			$sql_mod_disp = "SELECT * FROM online_regis1.tbl_single_bform_disposition";
			$res_mod_disp = parent::execQuery($sql_mod_disp, $this->conn_idc);	
			$numRows_disp = mysql_num_rows($res_mod_disp);
			if($res_mod_disp && $numRows_disp>0)
			{
				while($row_mod_disp = mysql_fetch_assoc($res_mod_disp))
				{
					$module_disp_name_array[] = strtolower($row_mod_disp['module_type']);
					$module_dispositon_array[strtolower($row_mod_disp['module_type'])][$row_mod_disp['disposition_val']] = $row_mod_disp['disposition_name'];	
				}
			}
			$module_disp_name_array = array_unique($module_disp_name_array);
			/*print_r($module_disp_name_array);
			print_r($module_dispositon_array);
			print_r($module_array);*/
			foreach($module_array AS $key=>$module)
			{
				if(in_array(strtolower($module),$module_disp_name_array))
				{
					//echo "\n\n\n".$module;
					
					$status_arr = $module_dispositon_array[strtolower($module)];
					//print_r($status_arr);
					foreach($status_arr as $kk=>$vval)
					{
						//echo "\n\n".
						$sql_data ="SELECT * FROM d_jds.tbl_single_bform_audit_data WHERE module_type='".$module."' AND entered_date>='".$params['start_date']."' AND entered_date<='".$params['end_date']."' AND status_flag = '".$kk."'";				
						$data = Array();
						$data  = $this->get_result($sql_data); 
						$return_array[$module][$vval]  = $data;	
					}
				}
				else
				{
					$status_arr = Array('0','1','2','3','4','5','6','7','8');
					
					foreach($status_arr As $key=>$value)	
					{
						switch($value)
						{
							case '0' : $sub_module ='pending';break;
							case '1' : $sub_module ='followup';break;
							case '2' : $sub_module ='incorrect';break;
							case '3' : $sub_module ='correct';break;
							case '4' : $sub_module ='phone_correct_listing_incorrect';break;					
							case '5' : $sub_module ='invalid_freeze';break;					
							case '6' : $sub_module ='done';break;					
							case '7' : $sub_module ='tried_not_found';break;					
							case '9' : $sub_module ='duplicate';break;					
						}				
						$sql_data ="SELECT *FROM d_jds.tbl_single_bform_audit_data WHERE module_type='".$module."' AND entered_date>='".$params['start_date']."' AND entered_date<='".$params['end_date']."' AND status_flag = '".$value."'";				
						$data = Array(); 
						$data  = $this->get_result($sql_data); 
						$return_array[$module][$sub_module]  = $data;						 
					}
				}
			}
			 
		}
		else if(strtoupper($params['module']) == 'REWORKDATA')		
		{
			$module_array  =Array('F9_DC','TME','TMEMASK','TME_NEWBUS','TME_SAVENP');
			foreach($module_array AS $key=>$module)
			{ 
				$sql_data ="SELECT DISTINCT source,dialer_disposition,disposition ,count(1) as count FROM d_jds.tbl_dialer_rework_data WHERE module_type='".$module."' AND entered_date>='".$params['start_date']."' AND entered_date<='".$params['end_date']."' GROUP BY disposition, dialer_disposition";
				$res_mod = parent::execQuery($sql_data, $this->conn_iro_slave);
				if($res_mod && mysql_num_rows($res_mod) > 0)
				{ 
					while($row_mod = mysql_fetch_assoc($res_mod))
					{
						if(empty($row_mod['disposition']))
							$return_array[$module][$row_mod['dialer_disposition']]['pending'] = $row_mod['count'] ;
						else	
							$return_array[$module][$row_mod['dialer_disposition']][$row_mod['disposition']] = $row_mod['count'] ;
					}
				}
				 
			}
		}
		else if(strtoupper($params['module']) == 'ASSOCIATE_MAPPING')		
		{
			$status_arr = Array('contract mapped','contract unmapped');
			foreach($status_arr As $key=>$value)	
			{
				$sql ="SELECT * FROM db_iro.tbl_mappedcontract_logs WHERE  update_date>='".$params['start_date']."' AND update_date<='".$params['end_date']."' AND status = '".$value."'";				
				 
				$data  = $this->get_result($sql); 
				$return_array[$value]  = $data;	
			}
			 
		}
		else if(strtoupper($params['module']) == 'CSHELPDESK')		
		{	 
			$param_cs = Array();
			$param_cs['start_date'] = $params['start_date'];
			$param_cs['end_date'] 	= $params['end_date'];
			$param_cs['data'] 		= $params['data'];
			$param_cs['source'] 	= 'jdbox';
			
			$url = "http://cshelpdesk.justdial.com/cshelpdesk_services/cshelpdesk/get_audit_data";
			
			$ch 		= curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST      ,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS ,$param_cs);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$res = curl_exec($ch);		
			curl_close($ch);
			$return_array = json_decode($res,true);
		}
		else if(strtoupper($params['module']) == 'SP_DATACRR')		
		{
			/*$vertical_array = Array('17179869184','16384','34359738368','536870912','8388608','1024','1','2048','524288','512','8192','1073741824','137438953472','65536','2','274877906944','2097152','262144','8','4','8589934592','128','4194304','134217728','64','16','268435456','33554432','16777216','68719476736','67108864','32768','4294967296','2147483648','1099511627776','2199023255552','32','33','256','1048576','131072','549755813888','4096');
			
			foreach($vertical_array As $key=>$vertical)	
			{
				 
				switch($vertical)
				{
					case '17179869184' 	 :	$sub_module_vertical ='5 Star Hotels';break;
					case '16384' 	 	 :	$sub_module_vertical ='AC Service';break;
					case '34359738368' 	 :	$sub_module_vertical ='b2bmarketplace';break;
					case '536870912' 	 :	$sub_module_vertical ='Banquet Halls';break;
					case '8388608' 	 :	$sub_module_vertical ='Book Service';break;
					case '1024' 	 :	$sub_module_vertical ='Bus Booking';break;
					case '1' 	 :	$sub_module_vertical ='Table Reservation';break;
					case '2048' 	 :	$sub_module_vertical ='Cab Booking';break;
					case '524288' 	 :	$sub_module_vertical ='Cake Service';break;
					case '512' 	 :	$sub_module_vertical ='Car Service';break;
					case '8192' 	 :	$sub_module_vertical ='Courier Service';break;
					case '1073741824' 	 :	$sub_module_vertical ='Dairy Service';break;
					case '137438953472' 	 :	$sub_module_vertical ='Deals and Offers';break;
					case '65536' 	 :	$sub_module_vertical ='Diagnostic Labs';break;
					case '2' 	 :	$sub_module_vertical ='Doctors Appointment';break;
					case '274877906944' 	 :	$sub_module_vertical ='Events';break;
					case '2097152' 	 :	$sub_module_vertical ='Flight Booking';break;
					case '262144' 	 :	$sub_module_vertical ='Flower Service';break;
					case '8' 	 :	$sub_module_vertical ='JDFOS	';break;
					case '4' 	 :	$sub_module_vertical ='Gas Booking Service	';break;
					case '8589934592' 	 :	$sub_module_vertical ='GenieTag	';break;
					case '128' 	 :	$sub_module_vertical ='Grocery Delivery';break;
					case '4194304' 	 :	$sub_module_vertical ='Hotel Booking';break;
					case '134217728' 	 :	$sub_module_vertical ='Insurance';break;
					case '64' 	 :	$sub_module_vertical ='Laundry Service';break;
					case '16' 	 :	$sub_module_vertical ='Liquor Service';break;
					case '268435456' 	 :	$sub_module_vertical ='Loan';break;
					case '33554432' 	 :	$sub_module_vertical ='Mineral Water';break;
					case '16777216' 	 :	$sub_module_vertical ='Movies';break;
					case '68719476736' 	 :	$sub_module_vertical ='Non Branded Market Place';break;
					case '67108864' 	 :	$sub_module_vertical ='Pathology Service';break;
					case '32768' 	 :	$sub_module_vertical ='Pharmacy Delivery';break;
					case '4294967296' 	 :	$sub_module_vertical ='Plumber';break;
					case '2147483648' 	 :	$sub_module_vertical ='Recharge';break;
					case '1099511627776' 	 :	$sub_module_vertical ='Repair';break;
					case '2199023255552' 	 :	$sub_module_vertical ='Service';break;
					case '32' 	 :	$sub_module_vertical ='Shop Front';break;
					case '33' 	 :	$sub_module_vertical ='Shop Front Quotes';break;
					case '256' 	 :	$sub_module_vertical ='Spa &amp; Salons';break;
					case '1048576' 	 :	$sub_module_vertical ='Sweet Service';break;
					case '131072' 	 :	$sub_module_vertical ='Test Drive';break;
					case '549755813888' 	 :	$sub_module_vertical ='Train Booking';break;
					case '4096' 	 :	$sub_module_vertical ='Water Purifier Service';break;					
				}*/
				//$dept_arr = Array('Content Team','Data Base Team','Worked By Sp');
				$dept_arr = Array('Data Base Team');
				foreach($dept_arr As $k=>$dept)	
				{
					 
					$disposition_arr = Array('247','250');
					foreach($disposition_arr As $k=>$disposition)	
					{
						switch($disposition)
						{
							case '247' : $sub_module_disposition = 'Vendor Cancelled'; break;
							case '250' : $sub_module_disposition = 'Vendor Not Responding'; break;
						}
						$paid_status_arr = array('0','1');
						foreach($paid_status_arr As $p=>$p_status)	
						{	
							switch($p_status)
							{
								case '0' : $sub_module_paidstatus = 'nonpaid'; break;
								case '1' : $sub_module_paidstatus = 'paid'; break;								
							}
							$status_arr = Array('0','1','3','4');
							foreach($status_arr As $kk=>$status)	
							{
								switch($status)
								{
									case '0' : $sub_module_status = 'pending'; break;
									case '1' : $sub_module_status = 'done'; break;
									//case '2' : $sub_module_status = 'All'; break;
									case '3' : $sub_module_status = 'tried'; break;
									case '4' : $sub_module_status = 'NochangeRequire'; break;
								}
								
								$sql = "SELECT  doc_id,prod_id,updated_on,companyname,sub_type_flag,parentid,entrydate,vertical,orderid,dispid,dispname,update_status,data_city,paid_status,narration,vertical_type_value,verticalname,reason_code, disposedby,reason_name, action_flag, contract_type,curent_updated_on,updatedby from db_iro.tbl_allverticals_disposition WHERE entrydate>='".$params['start_date']."' AND entrydate<='".$params['end_date']."' /*AND vertical_type_value = '".$vertical."'*/ AND dispid = '".$disposition."' AND paid_status ='".$p_status."' AND update_status = '".$status."' ORDER BY entrydate DESC";
								$data  = $this->get_result($sql); 
								//$return_array[$sub_module_vertical][$dept][$sub_module_disposition][$sub_module_paidstatus][$sub_module_status]  = $data;	
								$return_array[$dept][$sub_module_disposition][$sub_module_paidstatus][$sub_module_status]  = $data;	
							} 
						}	
					}						 
				} 	
			//}	
		}	
		else if(strtoupper($params['module']) == 'F9_DWOCN')		
		{
			$data = Array();
			$start_date = $params['start_date'];
			$end_date 	= $params['end_date'];
			$paidcondition =array("paid"=>'1',"non_paid"=>'0');
			$paidcondition =array("non_paid"=>'0');

			foreach ($paidcondition as $attr=>$pc)
			{
				$condition =
				array("high_priority_".$attr=>array("AND starCompany=1"),
				"5_counts_and_above_".$attr => array ("having updatecnt > 4"),
				"4_counts_".$attr => array("having updatecnt > 3 and updatecnt < 5"), 
				"3_counts_".$attr => array("having updatecnt > 2 and updatecnt < 4"),
				"2_counts_".$attr => array("having updatecnt > 1 and updatecnt < 3"), 
				"1_counts_".$attr => array("having updatecnt > 0 and updatecnt < 2"));
				foreach($condition AS $key=>$val)
				{
					foreach($val AS $val1)
					{
						
						if(in_array($val1,$condition['high_priority_'.$attr]))
						{ 
							$wocontnumfollow = "Select count(*) as cnt from (SELECT autoid,GROUP_CONCAT(iroCode order by updatedate desc) as iroCode,business,phone,area,city,comments,GROUP_CONCAT(updatedate order by updatedate desc) as updatedate,sum(updatecount) as updatecnt,contractcode,
							GROUP_CONCAT(done_flag order by updatedate desc) as done_flag,paid,
							starCompany,virtualNumber,reason,custtype,businesstags,company_callcnt,
							invalid_detail,IF(invalid_detail != '' && flag&1=1,1,0) AS cli,
							IF(reason='Company closed down',1,0) AS compclosed from db_iro.repChngData
							where updatedate BETWEEN '$start_date' AND '$end_date' and 
							reason ='Data Without Contact Number' and done_flag='C' and paid='$pc' ".$val1."
							group by contractcode,done_flag) as a";
							
						}
						else
						{
							$wocontnumfollow = "Select count(*) as cnt from (SELECT autoid,GROUP_CONCAT(iroCode order by updatedate desc) as iroCode,business,phone,area,city,comments,GROUP_CONCAT(updatedate order by updatedate desc) as updatedate,sum(updatecount) as updatecnt,contractcode,
							GROUP_CONCAT(done_flag order by updatedate desc) as done_flag,paid,
							starCompany,virtualNumber,reason,custtype,businesstags,company_callcnt,
							invalid_detail,IF(invalid_detail != '' && flag&1=1,1,0) AS cli,
							IF(reason='Company closed down',1,0) AS compclosed from db_iro.repChngData
							where updatedate BETWEEN '$start_date' AND '$end_date' and 
							reason ='Data Without Contact Number' and done_flag='C' and paid='$pc'
							group by contractcode,done_flag ".$val1.") as a"; 
					
						}
						$exec_wocontnumfollow=parent::execQuery($wocontnumfollow,$this->conn_iro_slave);	
	 					$rowocontnumfollowcnt= mysql_num_rows($exec_wocontnumfollow);
						$rowocontnumfollow   = mysql_fetch_assoc($exec_wocontnumfollow);
						$wocontnumfollow=$rowocontnumfollow['cnt'];
						
						$data['data_without_contact_number_'.$attr]['follow'][$key] = $wocontnumfollow;
						$data['data_without_contact_number_'.$attr]['follow']['data_without_contact_number_'.$attr.''] += $data['data_without_contact_number_'.$attr]['follow'][$key];
						
						if(in_array($val1,$condition['high_priority_'.$attr]))
						{ 
							$wocontnumpend = "Select count(*) as cnt from(SELECT autoid,GROUP_CONCAT(iroCode order by updatedate desc) as iroCode,business,phone,area,city,comments,GROUP_CONCAT(updatedate order
							by updatedate desc) as updatedate,sum(updatecount) as updatecnt,
							contractcode,GROUP_CONCAT(done_flag order by updatedate desc) as 
							done_flag,paid,starCompany,virtualNumber,reason,custtype,businesstags,
							company_callcnt,invalid_detail,IF(invalid_detail != '' && flag&1=1,1,0)
							AS cli,IF(reason='Company closed down',1,0) AS compclosed from db_iro.repChngData 
							where updatedate BETWEEN '$start_date' AND '$end_date' and 
							reason ='Data Without Contact Number' and done_flag='N' and paid='$pc' ".$val1." group by 
							contractcode,done_flag) as a";
						}
					
						else
						{
							$wocontnumpend = "Select count(*) as cnt from(SELECT autoid,GROUP_CONCAT(iroCode order by updatedate desc) as iroCode,business,phone,area,city,comments,GROUP_CONCAT(updatedate order
							by updatedate desc) as updatedate,sum(updatecount) as updatecnt,
							contractcode,GROUP_CONCAT(done_flag order by updatedate desc) as 
							done_flag,paid,starCompany,virtualNumber,reason,custtype,businesstags,
							company_callcnt,invalid_detail,IF(invalid_detail != '' && flag&1=1,1,0)
							AS cli,IF(reason='Company closed down',1,0) AS compclosed from db_iro.repChngData 
							where updatedate BETWEEN '$start_date' AND '$end_date' and 
							reason ='Data Without Contact Number' and done_flag='N' and paid='$pc' group by 
							contractcode,done_flag ".$val1." ) as a";
						}
												
						$exec_wocontnumpend=parent::execQuery($wocontnumpend,$this->conn_iro_slave);	
						$rowocontnumpendcnt= mysql_num_rows($exec_wocontnumpend);
						$rowocontnumpend   = mysql_fetch_assoc($exec_wocontnumpend);
						$wocontnumpend = $rowocontnumpend['cnt'];
						$data['data_without_contact_number_'.$attr]['pending'][$key] = $wocontnumpend;
						
						$data['data_without_contact_number_'.$attr]['pending']['data_without_contact_number_'.$attr.''] += $data['data_without_contact_number_'.$attr]['pending'][$key];
							
						if(in_array($val1,$condition['high_priority_'.$attr]))
						{
							$wocontnumrecd = "Select count(*) as cnt from (SELECT autoid,GROUP_CONCAT(iroCode order by updatedate desc) as iroCode,business,phone,area,city,comments,GROUP_CONCAT(updatedate order by updatedate desc) as updatedate,sum(updatecount) as updatecnt,contractcode,			  GROUP_CONCAT(done_flag order by updatedate desc) as done_flag,paid,starCompany,
							virtualNumber,reason,custtype,businesstags,company_callcnt,invalid_detail,
							IF(invalid_detail != '' && flag&1=1,1,0) AS cli,IF(reason='Company closed down',1,0)
							AS compclosed from db_iro.repChngData where updatedate BETWEEN '$start_date' AND '$end_date'
							and reason ='Data Without Contact Number' and paid='$pc' ".$val1." group by contractcode,done_flag) as a";						
						}
						else
						{
							$wocontnumrecd = "Select count(*) as cnt from (SELECT autoid,GROUP_CONCAT(iroCode order by updatedate desc) as iroCode,business,phone,area,city,comments,GROUP_CONCAT(updatedate order by updatedate desc) as updatedate,sum(updatecount) as updatecnt,contractcode,GROUP_CONCAT(done_flag order by updatedate desc) as done_flag,paid,starCompany,virtualNumber,reason,custtype,businesstags,company_callcnt,invalid_detail,IF(invalid_detail != '' && flag&1=1,1,0) AS cli,IF(reason='Company closed down',1,0) AS compclosed from db_iro.repChngData where updatedate BETWEEN '$start_date' AND '$end_date' and reason ='Data Without Contact Number' and paid='$pc' group by contractcode,done_flag ".$val1." ) as a";
					   
						}
						$exec_wocontnumrecd=parent::execQuery($wocontnumrecd,$this->conn_iro_slave);	
						$rowocontnumrecdcnt= mysql_num_rows($exec_wocontnumrecd);
						$rowocontnumrecd   = mysql_fetch_assoc($exec_wocontnumrecd);
						$wocontnumrecd = $rowocontnumrecd['cnt'];
						$data['data_without_contact_number_'.$attr]['received'][$key]=$wocontnumrecd;
						 
						 
						$data['data_without_contact_number_'.$attr]['received']['data_without_contact_number_'.$attr.''] += 
						$data['data_without_contact_number_'.$attr]['received'][$key];
						 
											
						$data['data_without_contact_number_'.$attr]['done'][$key] = $data['data_without_contact_number_'.$attr]['received'][$key] - ($data['data_without_contact_number_'.$attr]['follow'][$key] 
						+ $data['data_without_contact_number_'.$attr]['Pending'][$key]);
						
						
						$data['data_without_contact_number_'.$attr]['done']['data_without_contact_number_'.$attr.''] += 
						$data['data_without_contact_number_'.$attr]['done'][$key];
					}
				}
			}
			$return_array	=	$data;
		}
		else if(strtoupper($params['module']) == 'VN_DC')
		{
			$data = Array();
			$start_date = $params['start_date'];
			$end_date 	= $params['end_date'];
			$paidcondition =array("paid"=>'1',"non_paid"=>'0');
			$paidcondition =array("non_paid"=>'0');
			foreach ($paidcondition as $attr=>$pc)
			{	
			 	$condition = array("high_priority_".$attr=>array("AND starCompany=1"),
				"5_counts_and_above_".$attr => array ("having updatecnt > 4"),
				"4_counts_".$attr => array("having updatecnt > 3 and updatecnt < 5"), 
				"3_counts_".$attr => array("having updatecnt > 2 and updatecnt < 4"),
				"2_counts_".$attr => array("having updatecnt > 1 and updatecnt < 3"), 
				"1_counts_".$attr => array("having updatecnt > 0 and updatecnt < 2"));		
					
				foreach($condition AS $key=>$val)
				{ 
					foreach($val AS $val1)
					{
						if(in_array($val1,$condn['high_priority_'.$attr]))
						{ 
							$follow =  "SELECT COUNT(*) AS cnt FROM (SELECT autoid,GROUP_CONCAT(iroCode ORDER	BY updatedate DESC) AS iroCode,business,phone,AREA,city,comments, 
							GROUP_CONCAT(updatedate ORDER BY 
							updatedate DESC) AS updatedate,SUM(updatecount) AS updatecnt,contractcode,
							GROUP_CONCAT(done_flag ORDER BY updatedate DESC) AS done_flag,paid,starCompany,
							virtualNumber,reason,custtype,businesstags,company_callcnt,invalid_detail,
							IF(invalid_detail != '' && flag&1=1,1,0) AS cli,IF(reason='Company closed down',1,0)
							AS compclosed FROM db_iro.repChngData WHERE updatedate BETWEEN '$start_date' AND
							'$end_date' AND reason ='VN Data Correction' AND done_flag='C' AND paid='$pc' ".$val1.
							" GROUP BY contractcode,done_flag )AS a"; 
							
						}
						else
						{
							
							$follow = "SELECT COUNT(*) AS cnt FROM (SELECT autoid,GROUP_CONCAT(iroCode ORDER BY	
							updatedate DESC)
							AS iroCode,business,phone,AREA,city,comments, GROUP_CONCAT(updatedate ORDER BY 
							updatedate DESC) AS updatedate,SUM(updatecount) AS updatecnt,contractcode,
							GROUP_CONCAT(done_flag ORDER BY updatedate DESC) AS done_flag,paid,starCompany,
							virtualNumber,reason,custtype,businesstags,company_callcnt,invalid_detail,
							IF(invalid_detail != '' && flag&1=1,1,0) AS cli,IF(reason='Company closed down',1,0)
							AS compclosed FROM db_iro.repChngData WHERE updatedate BETWEEN '$start_date' AND
							'$end_date' AND reason ='VN Data Correction' AND done_flag='C' AND paid='$pc' 
							 GROUP BY contractcode,done_flag  ".$val1.") AS a";
					
						}
						$exec_follow=parent::execQuery($follow,$this->conn_iro_slave);
						$rowfollowcnt = mysql_num_rows($exec_follow);
						$rowfollow    = mysql_fetch_assoc($exec_follow);
						 
					
						$data['VN_data_correction_'.$attr]['follow'][$key] = $rowfollow['cnt'];
						
						$data['VN_data_correction_'.$attr]['follow']['VN_data_correction_'.$attr] += $data['VN_data_correction_'.$attr]['follow'][$key];
					
						if(in_array($val1,$condn['high_priority_'.$attr]))
						{ 
							$pend = "Select count(*) as cnt from (SELECT autoid,GROUP_CONCAT(iroCode order by updatedate desc) as iroCode,
							business,phone,area,city,comments,GROUP_CONCAT(updatedate order by updatedate
							desc) as updatedate,sum(updatecount) as updatecnt,contractcode,
							GROUP_CONCAT(done_flag order by updatedate desc) as done_flag,paid,starCompany,
							virtualNumber,reason,custtype,businesstags,company_callcnt,invalid_detail,
							IF(invalid_detail != '' && flag&1=1,1,0) AS cli,IF(reason='Company closed down',1,0)
							AS compclosed from db_iro.repChngData where updatedate BETWEEN '$start_date' AND '$end_date'
							and reason ='VN Data Correction' and done_flag='N' and paid='$pc' ". $val1 ." group by 
							contractcode,done_flag) as a";
						}
						else
						{
							$pend = "Select count(*) as cnt from (SELECT autoid,GROUP_CONCAT(iroCode order by updatedate desc) as iroCode,
							business,phone,area,city,comments,GROUP_CONCAT(updatedate order by updatedate
							desc) as updatedate,sum(updatecount) as updatecnt,contractcode,
							GROUP_CONCAT(done_flag order by updatedate desc) as done_flag,paid,starCompany,
							virtualNumber,reason,custtype,businesstags,company_callcnt,invalid_detail,
							IF(invalid_detail != '' && flag&1=1,1,0) AS cli,IF(reason='Company closed down',1,0)
							AS compclosed from db_iro.repChngData where updatedate BETWEEN '$start_date' AND '$end_date'
							and reason ='VN Data Correction' and done_flag='N' and paid='$pc' group by 
							contractcode,done_flag " . $val1. " ) as a";
						}
						$exec_pend=parent::execQuery($pend,$this->conn_iro_slave);
						$rowpendcnt = mysql_num_rows($exec_pend);
						$rowpend    = mysql_fetch_assoc($exec_pend);

						
						$data['VN_data_correction_'.$attr]['pending'][$key] = $rowpend['cnt'];
						$data['VN_data_correction_'.$attr]['pending']['VN_data_correction_'.$attr] += $data['VN_data_correction_'.$attr]['pending'][$key];
				
						if(in_array($val1,$condn['high_priority_'.$attr]))
						{ 
							$recd = "Select count(*) as cnt from (SELECT autoid,GROUP_CONCAT(iroCode order by updatedate desc) as iroCode,business,phone,
							area,city,comments,GROUP_CONCAT(updatedate order by updatedate desc) as updatedate,
							sum(updatecount) as updatecnt,contractcode,GROUP_CONCAT(done_flag order by updatedate desc) as done_flag,paid,starCompany,virtualNumber,reason,custtype,businesstags,company_callcnt,
							invalid_detail,IF(invalid_detail != '' && flag&1=1,1,0) AS cli,
							IF(reason='Company closed down',1,0) AS compclosed from db_iro.repChngData where
							updatedate BETWEEN '$start_date' AND '$end_date' and reason ='VN Data Correction' and
							paid='$pc' ". $val1 ." group by contractcode,done_flag ) as a";
						}
						else
						{
							$recd = "Select count(*) as cnt from (SELECT autoid,GROUP_CONCAT(iroCode order by updatedate desc) as iroCode,business,phone,
							area,city,comments,GROUP_CONCAT(updatedate order by updatedate desc) as updatedate,
							sum(updatecount) as updatecnt,contractcode,GROUP_CONCAT(done_flag order by updatedate desc) as done_flag,paid,starCompany,virtualNumber,reason,custtype,businesstags,company_callcnt,
							invalid_detail,IF(invalid_detail != '' && flag&1=1,1,0) AS cli,
							IF(reason='Company closed down',1,0) AS compclosed from db_iro.repChngData where
							updatedate BETWEEN '$start_date' AND '$end_date' and reason ='VN Data Correction' and
							paid='$pc' group by contractcode,done_flag " .$val1. ") as a";
					   
						}
						$exec_recd	=	parent::execQuery($recd,$this->conn_iro_slave);
						$rowrecdcnt = mysql_num_rows($exec_recd);
						$rowrecd    = mysql_fetch_assoc($exec_recd);
						 
						$data['VN_data_correction_'.$attr]['received'][$key] = $rowrecd['cnt'];
						 
						 
						$data['VN_data_correction_'.$attr]['received']['VN_data_correction_'.$attr] += $data['VN_data_correction_'.$attr]['received'][$key];
									
						$data['VN_data_correction_'.$attr]['done'][$key] = $data['VN_data_correction_'.$attr]['received'][$key] - ($data['VN_data_correction_'.$attr]['follow'][$key] 
						+ $data['VN_data_correction_'.$attr]['pending'][$key]);
						
						$data['VN_data_correction_'.$attr]['done']['VN_data_correction_'.$attr] += $data['VN_data_correction_'.$attr]['done'][$key];
					}
				}
			}
			$return_array = $data;
		}
		 
		$output['result'] 				=  $return_array;
		$output['error']['message'] 	=  "success";		
		if($params['trace'] == 1){
			//echo "\n".$sql;
			echo "\n\n";print_r($output);
		}		
		return ($output);
	}
	function get_query($sql,$year,$month)
	{
		$month_1 = str_replace("-","_",date('m-Y',mktime(0, 0, 0, $month,date('d'),$year)));
		$month_2 = str_replace("-","_",date('m-Y',mktime(0, 0, 0, $month-1,date('d'),$year)));
		$month_3 = str_replace("-","_",date('m-Y',mktime(0, 0, 0, $month-2,date('d'),$year)));
		  
		$sql1 = str_replace($month_1,$month_2,$sql);
		$sql2 = str_replace($month_1,$month_3,$sql);
		$sql .=  " UNION ".  $sql1 . " UNION  " . $sql2 ;
	 
		return $sql;
	}
	public function get_result($sql,$data='',$db='')
	{
		/*echo "\n\n".$db;
		/*echo $sql;
		echo "\n\n";exit;*/
		global $params;
		if(0)//$params['trace'] == 1)
		{
			echo "\n\n".$sql;
			echo "\n-------------------------------------------------------------------------------------\n";
		}	
		
		if($this->data=='1')
		{
			$return_array = Array();
			if($db == 'data_correction')
				$res = parent::execQuery($sql, $this->data_correction_slave);	
			else
				$res = parent::execQuery($sql, $this->conn_iro_slave);	
			$numRows = mysql_num_rows($res);
			if($res && $numRows>0)
			{
				while($row = mysql_fetch_assoc($res))
				{
					$return_array[] =$row;
				}
			}
			return $return_array;
		}
		else
		{
			$numRows	=	'0';
			$city_arr	=	array('mumbai','delhi','kolkata','bangalore','pune','chennai','hyderabad','ahmedabad');
			if($db == 'data_correction')
			{
				
				if(in_array($this->source_city,$city_arr))
					$zone_cond	=	" AND xx.zone='".$this->source_city."'";
				else
					$zone_cond	=	"";
				//if($this->source_city =='remote')
				{
					//echo "\n\n".
					$sql = "SELECT DISTINCT xx.zone,count(1) AS cnt FROM ( ".$sql ." ) xx WHERE xx.zone<>'' ".$zone_cond." GROUP BY xx.zone";
				}
				 
				$res = parent::execQuery($sql, $this->data_correction_slave);
				
				while($row = mysql_fetch_assoc($res))
				{
					$rerun_arr[$row['zone']] = $row['cnt'];
					
				}
				if(!in_array($this->source_city,$city_arr))
				{
					foreach($city_arr as $key => $city)
					{
						if(!isset($rerun_arr[$city]))
							$rerun_arr[$city] = "0";
					}
				}	
				//print_r($rerun_arr);			exit;	
				return $rerun_arr;
				
			}	
			else
			{	
				//echo "\n".$sql;exit;
				$res = parent::execQuery($sql, $this->conn_iro_slave);		
			 	return mysql_num_rows($res);	
			}	
			//$res = parent::execQuery($sql, $this->conn_iro_slave);	
			
			
		}
	}
	
	public function get_clean_value($field_value, $separator="")
	{
		if(!empty($separator))
		{
			$clean_value	= array();
			$clean_value 	= array_values(array_filter(explode($separator,$field_value)));
		}
		else
		{
			$clean_value	= "";
			$clean_value 	= trim($field_value);
		}
		return $clean_value;
	}
	public function CurlFn($Urlstr)
	{
		$ch = curl_init();			
		$ch = curl_init($Urlstr);	
		curl_setopt($ch, CURLOPT_URL,$Urlstr);				
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$resultString = curl_exec($ch);					
		curl_close($ch);				
		return $resultString;		
	}
	private function send_die_message($msg)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['message'] = $msg;
		return $die_msg_arr;
	}		
}
?>
