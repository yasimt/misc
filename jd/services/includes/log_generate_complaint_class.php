<?php

class log_generate_complaint_class extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $dbConIdc    	= null;
	var  $params  	= null;
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
		
		if(!$this->params['action'])
		{
			$errorarray['errormsg']='action missing';
			echo json_encode($errorarray); exit;
		}
		if($this->params['action'] == 1)
		{
			if($this->params['parentid']) {
				$this->parentid = $this->params['parentid'];
			}else{
			    $errorarray['errormsg']='parentid missing';
				echo json_encode($errorarray); exit;
			}
			
			
			if($this->params['module']) {
				$this->module = $this->params['module'];
			}else{
				$errorarray['errormsg']='module missing';
				echo json_encode($errorarray); exit;
			}
		
			if($this->params['data_city']) {
				$this->data_city = $this->params['data_city'];
			}else{
				$errorarray['errormsg']='data_city missing';
				echo json_encode($errorarray); exit;
			}
			
			if(trim($this->params['limit']) != "")
			{
				$this->limit  = $this->params['limit']; //initialize limit
			}
			
		}	
			
		if($this->params['action'] == 2)
		{
			if($this->params['complaint_id']) {
				$this->complaint_id = $this->params['complaint_id'];
			}else{
			    $errorarray['errormsg']='complaint_id missing';
				echo json_encode($errorarray); exit;
			}
			
			if($this->params['data_city']) {
				$this->data_city = $this->params['data_city'];
			}else{
				$errorarray['errormsg']='data_city missing';
				echo json_encode($errorarray); exit;
			}
			
			if($this->params['module']) {
				$this->module = $this->params['module'];
			}else{
				$errorarray['errormsg']='module missing';
				echo json_encode($errorarray); exit;
			}
			
		}
		
		if($this->params['action'] == 3)
		{
			if($this->params['parentid']) {
				$this->parentid = $this->params['parentid'];
			}else{
			    $errorarray['errormsg']='parentid missing';
				echo json_encode($errorarray); exit;
			}
			if($this->params['module']) {
				$this->module = $this->params['module'];
			}else{
				$errorarray['errormsg']='module missing';
				echo json_encode($errorarray); exit;
			}
			
			if($this->params['uemptype']) {
				$this->uemptype = $this->params['uemptype'];
			}
		}
		
		if($this->params['action'] == 4)
		{
			if($this->params['module']) {
				$this->module = $this->params['module'];
			}else{
				$errorarray['errormsg']='module missing';
				echo json_encode($errorarray); exit;
			}
			
			if($this->params['uemptype']) {
				$this->uemptype = $this->params['uemptype'];
			}
		}
		
		if($this->params['action'] == 7)
		{
			
			if($this->params['parentid']) {
				$this->parentid = $this->params['parentid'];
			}else{
			    $errorarray['errormsg']='parentid missing';
				echo json_encode($errorarray); exit;
			}
			
			
			if($this->params['module']) {
				$this->module = $this->params['module'];
			}else{
				$errorarray['errormsg']='module missing';
				echo json_encode($errorarray); exit;
			}
		
			if($this->params['data_city']) {
				$this->data_city = $this->params['data_city'];
			}else{
				$errorarray['errormsg']='data_city missing';
				echo json_encode($errorarray); exit;
			}
			
			if($this->params['complaint_id']) {
				$this->complaint_id = $this->params['complaint_id'];
			}
			
		}
		
		
		if($this->params['action'] == 8)
		{
			
			if($this->params['parentid']) {
				$this->parentid = $this->params['parentid'];
			}else{
			    $errorarray['errormsg']='parentid missing';
				echo json_encode($errorarray); exit;
			}
			
			
			if($this->params['module']) {
				$this->module = $this->params['module'];
			}else{
				$errorarray['errormsg']='module missing';
				echo json_encode($errorarray); exit;
			}
		
			if($this->params['data_city']) {
				$this->data_city = $this->params['data_city'];
			}else{
				$errorarray['errormsg']='data_city missing';
				echo json_encode($errorarray); exit;
			}
			
			if($this->params['complaint_id']) {
				$this->complaint_id = $this->params['complaint_id'];
			}
			
			if($this->params['follow_up_time']) {
				$this->dept_id = $this->params['follow_up_time'];
			}
			
		/*	if($this->params['datesource']) {
				$this->datesource = $this->params['datesource'];
			}
			*/
			if($this->params['Complain_Types']) {
				$this->Complain_Types = $this->params['Complain_Types'];
			}
			
			if($this->params['status']) {
				$this->status = $this->params['status'];
			}
			
			if($this->params['description']) {
				$this->description = $this->params['description'];
			}
			
			if($this->params['caller_name']) {
				$this->caller_name = $this->params['caller_name'];
			}
			
			if($this->params['caller_num']) {
				$this->caller_num = $this->params['caller_num'];
			}
			
			if($this->params['Source_Types']) {
				$this->Source_Types = $this->params['Source_Types'];
			}
			
			if($this->params['subsource']) {
				$this->subsource = $this->params['subsource'];
			}
			
			if($this->params['contract_type']) {
				$this->contract_type = $this->params['contract_type'];
			}
			
			if($this->params['emp_code']) {
				$this->emp_code = $this->params['emp_code'];
			}
			
			if($this->params['emp_name']) {
				$this->emp_name = $this->params['emp_name'];
			}
			
			if($this->params['emp_type']) {
				$this->emp_type = $this->params['emp_type'];
			}
			
			if($this->params['reason_types']) {
				$this-> reason_types = $this->params['reason_types'];
			}
			
			if($this->params['email']) {
				$this-> email = $this->params['email'];
			}
			
			if($this->params['mobile']) {
				$this-> mobile = $this->params['mobile'];
			}
			
			if($this->params['call_back_time']) {
				$this-> call_back_time = $this->params['call_back_time'];
			}
			
			if($this->params['categories']) {
				$this-> categories = $this->params['categories'];
			}
			
			if($this->params['mail_val']) {
				$this-> mail_val = $this->params['mail_val'];
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
		
		$data_city 				= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$this->local_d_jds		= $db[strtolower($data_city)]['d_jds']['master'];
		$this->conn_iro			= $db[strtolower($data_city)]['iro']['master'];
		$this->messaging    	= $db[strtolower($data_city)]['messaging']['master'];
		
	}
	
	function get_all_complaints()
	{
		
		$sql_get_complaint_types = "select complaintype_id,complaintype_name  from tbl_complaintype";
		$res_get_complaint_types = parent::execQuery($sql_get_complaint_types, $this->local_d_jds);
		if(DEBUG_MODE)
		{
			echo '<br> sql :: '.$sql_get_complaint_types;
			echo '<br> res :: '.$res_get_complaint_types;
			echo '<br> num rows :: '.mysql_num_rows($res_get_complaint_types);
		}
		
		if($res_get_complaint_types && mysql_num_rows($res_get_complaint_types))
		{
			while($row_get_complaint_types = mysql_fetch_assoc($res_get_complaint_types))
			{
				$complaint_type_arr[$row_get_complaint_types['complaintype_id']] = $row_get_complaint_types['complaintype_name'];
			}
		}	
		
		$sql_count = "select count(1) as total_count from log_complain_main where parentid='".$this->parentid."'";
		$res_count = parent::execQuery($sql_count, $this->local_d_jds);
		if($res_count && mysql_num_rows($res_count))
		{
			$row_count = mysql_fetch_assoc($res_count);
		}
		
		if($this->limit)
		$limit = 'limit '.$this->limit;
		
		$sql = "select * from log_complain_main where parentid='".$this->parentid."' ORDER BY complain_registration_date DESC ".$limit;
		$res = parent::execQuery($sql, $this->local_d_jds);
		if(DEBUG_MODE)
		{
			echo '<br> sql :: '.$sql;
			echo '<br> res :: '.$res;
			echo '<br> num rows :: '.mysql_num_rows($res);
		}
		if($res && mysql_num_rows($res))
		{
			$i=0;
			while($row = mysql_fetch_assoc($res))
			{
				
				$complaint_arr[$i]['autoid'] = $row['autoid'];
				$complaint_arr[$i]['complain_type'] = $complaint_type_arr[$row['complain_type']];
				$complaint_arr[$i]['reason'] = $row['reason'];
				$complaint_arr[$i]['complain_registration_date'] = $row['complain_registration_date'];
				$complaint_arr[$i]['registeredby'] = $row['registeredby'];
				$complaint_arr[$i]['registeredby_name'] = $row['registeredby_name'];
				$complaint_arr[$i]['complain_resolved_date'] = $row['complain_resolved_date'];
				$complaint_arr[$i]['resolvedby'] = $row['resolvedby'];
				$complaint_arr[$i]['complain_source'] = $row['complain_source'];
				
				switch($row['resolutionflag'])
				{
					case '0':
					$complaint_arr[$i]['resolutionflag'] = 'Open';
					break;
					case '1':
					$complaint_arr[$i]['resolutionflag'] = 'Closed';
					break;
					case '2':
					$complaint_arr[$i]['resolutionflag'] = 'Follow Up';
					break;
					case '3':
					$complaint_arr[$i]['resolutionflag'] = 'Call Back';
					break;
					
				}
				switch($row['cs_feedback_rating'])
				{
					case 1:
					$complaint_arr[$i]['cs_feedback_rating'] = 'Poor';
					break;
					
					case 2:
					$complaint_arr[$i]['cs_feedback_rating'] = 'Average';
					break;
					
					case 3:
					$complaint_arr[$i]['cs_feedback_rating'] = 'good';
					break;
					
					case 4:
					$complaint_arr[$i]['cs_feedback_rating'] = 'very Good';
					break;
					
					case 5:
					$complaint_arr[$i]['cs_feedback_rating'] = 'Excellent';
					break;
					
					default:
					$complaint_arr[$i]['cs_feedback_rating'] = 'None';
					break;
					
					
				}
			$i++;	
			}
			
			 		$result_msg_arr['error']['code'] = 0;
					$result_msg_arr['error']['msg'] = "Data Found!";
					$result_msg_arr['data_count'] = $row_count['total_count'];
					$result_msg_arr['data'] = $complaint_arr;
					return $result_msg_arr;
		}
		else{
			 		$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "No Data Found!";
					return $result_msg_arr;
					
		}
		
	}
	
	function get_complaint_content()
	{
		
		$sql="select autoid, complain_registration_date,cs_feedback_rating, registeredby, registeredby_name, resolvedby, resolvedby_name, complain_source, complain_type, caller_name, caller_number, IF(resolutionflag = '1','Closed',IF(resolutionflag = '2','Follow Up',IF(resolutionflag = '3','Call Back','Open'))) AS Complaint_Status, complain_resolved_date, comp_category_name, comp_category_id, department_id   from log_complain_main where autoid='".$this->complaint_id."'";
		$res = parent::execQuery($sql, $this->local_d_jds);
		if(DEBUG_MODE)
		{
			echo '<br> sql :: '.$sql;
			echo '<br> res :: '.$res;
			echo '<br> num rows :: '.mysql_num_rows($res);
		}
		if($res && mysql_num_rows($res))
		{
			 $row = mysql_fetch_assoc($res);
			 switch($row['cs_feedback_rating'])
				{
					case 1:
					$row['cs_feedback_rating'] = 'Poor';
					break;
					
					case 2:
					$row['cs_feedback_rating'] = 'Average';
					break;
					
					case 3:
					$row['cs_feedback_rating'] = 'good';
					break;
					
					case 4:
					$row['cs_feedback_rating'] = 'very Good';
					break;
					
					case 5:
					$row['cs_feedback_rating'] = 'Excellent';
					break;
					
					default:
					$row['cs_feedback_rating'] = 'None';
					break;
					
					
				}
			 
			 
			 
			 $sql_type = "SELECT complaintype_name FROM tbl_complaintype WHERE  complaintype_id ='".$row['complain_type']."'";
			 $res_type = parent::execQuery($sql_type, $this->local_d_jds);
			 if(DEBUG_MODE)
				{
					
					echo '<br> sql :: '.$sql_type;
					echo '<br> res :: '.$res_type;
					echo '<br> num rows :: '.mysql_num_rows($res_type);
				}
				
			 if($res_type && mysql_num_rows($res_type))
			 {
				$row_type = mysql_fetch_assoc($res_type);
				$row['complaintype_name'] = $row_type['complaintype_name'];
			 }
		
			$sql_get_empname  = "SELECT empCode,CONCAT(empFname,' ',empLname) AS empName FROM emplogin where empCode in (SELECT DISTINCT updatedby FROM d_jds.log_complain_details WHERE complaintid='".$this->complaint_id."') ";
			$res_get_empname = parent::execQuery($sql_get_empname, $this->conn_iro);
			
			if(DEBUG_MODE)
				{
					echo '<br> sql :: '.$sql_get_empname;
					echo '<br> res :: '.$res_get_empname;
					echo '<br> num rows :: '.mysql_num_rows($res_get_empname);
				}
				
			if($res_get_empname && mysql_num_rows($res_get_empname))
			{
				 while($row_get_empname=mysql_fetch_assoc($res_get_empname))
				{
					$empcode_name[$row_get_empname['empCode']] = $row_get_empname['empName'];
				}
			}
			
			if(DEBUG_MODE)
			{
				echo '<pre> emp list :: ';
				print_r($empcode_name);
			}
			
			 $sql_followup="select * from log_complain_details where complaintid='".$this->complaint_id."'";
			 $res_followup = parent::execQuery($sql_followup, $this->local_d_jds);
				if(DEBUG_MODE)
				{
					echo '<br> sql :: '.$sql_followup;
					echo '<br> res :: '.$res_followup;
					echo '<br> num rows :: '.mysql_num_rows($res_followup);
				}
			  if($res_followup && mysql_num_rows($res_followup))
			   {
				  while($row_followup=mysql_fetch_assoc($res_followup))
					{
					  $row_followup['updatedby_name'] = $empcode_name[$row_followup['updatedby']];
					  $row['history'][] 			  = $row_followup;
					}
				}
				
				return $row;
		}	
	}
	
	function get_complain_sources()
	{
		$sql_source = "SELECT * FROM tbl_sourcetype WHERE active_flag=1";
		$res_source = parent::execQuery($sql_source, $this->local_d_jds);
		if(DEBUG_MODE)
				{
					echo '<br> sql :: '.$sql_source;
					echo '<br> res :: '.$res_source;
					echo '<br> num rows :: '.mysql_num_rows($res_source);
				}
		if($res_source && mysql_num_rows($res_source))
		{
			$i = 0;
			while($row_source = mysql_fetch_assoc($res_source))
			{
				$source_list_arr['main'][$i]['sid']   = $row_source['source_id'];
				$source_list_arr['main'][$i]['sname'] = $row_source['source_name'];
				$i++;
			}
			
			$sub_source_arr = array("Regular Issues","Sales Issues");
			foreach($sub_source_arr as $key=>$sub_source)
			{
				$source_list_arr['sub'][$key]['sname'] = $sub_source;
			}
			
			if(DEBUG_MODE)
			{
				echo '<pre>';
				print_r($source_list_arr);
			}
			
			return $source_list_arr;
		}
	}
	
	function getContractCategories(){
		
	   $sql_obtain="SELECT  concat(ifnull(catidlineage_nonpaid,''),',',ifnull(catidlineage,'')) as categories FROM tbl_companymaster_extradetails WHERE parentid='".$this->parentid."'";
	   $res_obtain = parent::execQuery($sql_obtain, $this->conn_iro);
	   if(DEBUG_MODE)
		{
			echo '<br> sql :: '.$sql_obtain;
			echo '<br> res :: '.$res_obtain;
			echo '<br> num rows :: '.mysql_num_rows($res_obtain);
		}
	   if($res_obtain && mysql_num_rows($res_obtain))
	   {
		   $row_obtain=mysql_fetch_assoc($res_obtain);
		   $categories = $row_obtain['categories'];
		   $categories=str_replace('/', '', $categories);
		   $categories=explode(',',$categories);
		   $categories=array_filter($categories);
		   $catids = implode("','",$categories);
		   $catids=ltrim($catids,',');
	   }
	   
	      if(DEBUG_MODE)
			{
				echo '<br> catids :: '.$catids;
				echo '<br> ';
			}
	   
	   $catarr=array();
	   if($catids!=''){
		   $getcatdetailsql="select catid,category_name from tbl_categorymaster_parentinfo where catid in ('".$catids."');";
		   $res_obtain = parent::execQuery($getcatdetailsql, $this->local_d_jds);
		   if(DEBUG_MODE)
			{
				echo '<br> sql :: '.$getcatdetailsql;
				echo '<br> res :: '.$res_obtain;
				echo '<br> num rows :: '.mysql_num_rows($res_obtain);
			}
		   if($res_obtain && mysql_num_rows($res_obtain))
		   {
				while($compdet=mysql_fetch_assoc($res_obtain))
				{
					$catarr[$compdet['catid']]=$compdet['category_name'];
				}
		   }
		}
		   if(DEBUG_MODE)
			{
				echo '<br> <pre>catids :: ';
				print_r($catarr);
			}
	   return $catarr;
	}

	function getEmailMobile($contact_person = '')
	{
		  $sql_details = "SELECT  email,mobile,contact_person FROM tbl_companymaster_generalinfo WHERE parentid='".$this->parentid."'";
		   $res_details = parent::execQuery($sql_details, $this->conn_iro);
		   if(DEBUG_MODE)
			{
				echo '<br> sql :: '.$sql_details;
				echo '<br> res :: '.$res_details;
				echo '<br> num rows :: '.mysql_num_rows($res_details);
			}
		   if($res_details && mysql_num_rows($res_details))
		   {
			   $row_details = mysql_fetch_assoc($res_details);
			   
			   if(DEBUG_MODE)
				{
					echo '<pre><br> email mobile rows :: ';
					print_r($row_details);
				}
			   
			   $email_arr = array();
			   if($row_details['email'])
			   {
				    $email_arr                 = explode(",",$row_details['email']);
					$email_arr					= array_values(array_filter($email_arr));
					$email_mobile_arr['email'] = $email_arr;
			   }
				
			    
			    $mobile_arr = array();			    
			   if($row_details['mobile'])
			   {
					$mobile_arr					= explode(",",$row_details['mobile']);
					$mobile_arr					= array_values(array_filter($mobile_arr));
					$email_mobile_arr['mobile'] = $mobile_arr;
				}
				
				$contact_person_arr = array();
				if($row_details['contact_person'])
				{
					$contact_person_arr				= explode(",",$row_details['contact_person']);
					$email_mobile_arr['contact_person'] = $contact_person_arr;
				}
				
				if($contact_person == "contact_person")
				{
					return $email_mobile_arr;
					die;
				}		
			   
			   if(DEBUG_MODE)
				{
					echo '<pre><br> email mobile arr :: ';
					print_r($email_mobile_arr);
				}
				//echo "evebg";
			   //print_r($email_mobile_arr);
			   return $email_mobile_arr;
		   }
	}
	function Getdepartment()
	{
		$sql_department = "SELECT id,department_name FROM db_iro.tbl_complain_department WHERE display_flag =1";
		
		$res_department = parent::execQuery($sql_department, $this->conn_iro);
		$i = 0;
		$department = array();
		while($row_department = mysql_fetch_assoc($res_department))
		{
			$department[$i] = $row_department;
			$i++;
		}
		if(DEBUG_MODE)
		{
			echo '<br> sql :: '.$sql_department;
			echo '<br> res :: '.$res_department;
			echo '<br> num rows :: '.mysql_num_rows($res_department);
		}
		
		
		
		return $department;
	}
	
	function Getreason()
	{
		$sql_reason = "select distinct(reason_names) from d_jds.tbl_reasons_complain where active_flag=1";
		
		$res_reason = parent::execQuery($sql_reason, $this->conn_iro);
		$i = 0;
		$reason = array();
		while($row_reason = mysql_fetch_assoc($res_reason))
		{
			$reason[$i] = $row_reason;
			$i++;
		}
		if(DEBUG_MODE)
		{
			echo '<br> sql :: '.$sql_reason;
			echo '<br> res :: '.$res_reason;
			echo '<br> num rows :: '.mysql_num_rows($res_reason);
		}
		
		
		return $reason;
	}
	
	function get_complain_types($cond = '')
	{
		//$this->complaint_history = $this->cmplain_history();
		//$complaint_list_arr['history'] = $this->complaint_history;
		
		switch(strtolower($this->module))
		{
			case 'cs':
			$sql_con = "";
			$sql_role = "SELECT  role_name FROM db_iro.tbl_role_master WHERE subrole_id = '85'";
			$res_role = parent::execQuery($sql_role, $this->conn_iro);
			if(DEBUG_MODE)
			{
				echo '<br> sql :: '.$sql_role;
				echo '<br> res :: '.$res_role;
				echo '<br> num rows :: '.mysql_num_rows($res_role);
			}
			if($res_role && mysql_num_rows($res_role)>0 && trim($this->uemptype))
			{
				
				while($row_role = mysql_fetch_assoc($res_role))
				{
					$role_fb_template[] = strtolower(trim($row_role['role_name']));
				}
				if(in_array(strtolower(trim($this->uemptype)),$role_fb_template))
				{
					$sql_con = " AND eligible_source&8=8 ";
				}
				else
				{
					$sql_con = " ";
				}
			}
			
			
			break;
			case 'tme':
					$sql_con = " AND eligible_source&4=4 ";
			break;
			case 'me':
					$sql_con = " AND eligible_source&4=4 ";
			break;
		}
		
		if(DEBUG_MODE)
		{
			echo '<br> sql cond :: '.$sql_con;
		}
		if($cond == 'all')
		{
			$sql = "SELECT complaintype_id,complaintype_name from tbl_complaintype where display_flag=1".$sql_con;
			$res = parent::execQuery($sql, $this->local_d_jds);
		}
		else
		{
			
			if(strtolower($this->module) == 'tme' || strtolower($this->module) == 'me' || in_array(strtolower($this->uemptype),$role_fb_template))
			{
				
				$sql = "SELECT complaintype_id,complaintype_name from tbl_complaintype where display_flag=1".$sql_con;
			}
			else
			{
			$sql = "SELECT a.complaintype_id,a.complaintype_name FROM tbl_complaintype_parent AS a
				JOIN tbl_complaintype AS b
				ON 
				a.complaintype_name = b.complaintype_name where b.display_flag=1 ".$sql_con." order by a.complaintype_id ";
			}
			$res = parent::execQuery($sql, $this->local_d_jds);
		}
		if(DEBUG_MODE)
		{
			print_r($this->local_d_jds);
			echo '<br> sql :: '.$sql;
			echo '<br> res :: '.$res;
			echo '<br> num rows :: '.mysql_num_rows($res);
		}
		
		if($res && mysql_num_rows($res))
		{
			$i = 0;
			while($row = mysql_fetch_assoc($res))
			{
				$complaint_list_arr[$i]['cid']   = $row['complaintype_id'];
				$complaint_list_arr[$i]['cname'] = addslashes(stripslashes($row['complaintype_name']));
				$i++;
			}
			//print_r($complaint_list_arr);
			if(DEBUG_MODE)
			{
				echo '<pre>';
				print_r($complaint_list_arr);
			}
			
			return $complaint_list_arr;
		}
	}
	
	function getComplaintFormInfo()
	{
		//echo 'sadvav';die;
		if(count($this->get_complain_sources()))
		$form_array['sources']= $this->get_complain_sources();
		
		if(count($this->get_complain_types('all')))
		$form_array['Complaintype']= $this->get_complain_types('all');
		
		if(count($this->getContractCategories()))
		$form_array['cats']= $this->getContractCategories();
		
		if(count($this->getEmailMobile()))
		$form_array['email_mob']= $this->getEmailMobile();
		
		if(count($this->Getdepartment()))
		$form_array['Getdepartment']= $this->Getdepartment();
		
		if(count($this->Getreason()))
		$form_array['Getreason']= $this->Getreason();
		
		if($this->complaint_id)
		$form_array['complaint_info']= $this->get_complaint_content();
		
		return $form_array;
	}
	
	function Log_Update_ComplaintDetails()
	{
		
			
			//contract categories - names and ids
			
			
			//tme and me names
			
			$mobileNo				= $this->params['mobile_number'];
			$Description			= $this->params['description'];
			$type_insert			= $this->params['complaint_type_name'];
			$this->Complain_Types	= $this->params['complaint_type_name'];
			$Sources				= $this->params['complaint_sources'];
			$autoid					= $this->params['autoid'];
			$this->Source_Types		= $this->params['complaint_sources'];
			$this->subsource		= $this->params['complaint_subsources'];
			$this->mobile_number	= $this->params['mobile_number'];
			$this->caller_num		= $this->params['caller_num'];
			$this->dept_id			= $this->get_department_id($this->params['dept_id']);
			$this->status			= $this->params['status'];
			$this->email			= $this->params['email_ids'];
			//$categories = $this->getContractCategories();
			$this->attachment 		= json_decode($this->params['attachment'],true);
			
			if(count($this->attachment) > 0)
			{
				
				$this->attachment_str = implode(",",$this->attachment);
			}
			else
			{
				$this->attachment_str = '';
			}		
			
			
			
			$this->cat_data = json_decode($this->params['cat_check'],true);
			//print_r($categories);
			//print_r($this->params);
			foreach($this->cat_data as $key => $value)
			{
				//foreach($value as $key_cat => $value_cat)
			//	{
				//	echo $value_cat;echo $key;
					//if($value_cat == 1)
					//{
						$cat_ids[] = $key;
						$cat_names[] = $value;
					//}
				//}	
			} 
			$catids = $this->params["key"];
			$catnames = $this->params["value"];
			//print_r($catids);
			//print_r($catnames);
			
				$reason_types = $this->params["reason_types"];
				if($reason_types!='' && $reason_types!='0'){
					$reason_types = $reason_types;
				}else{
					$reason_types='';
				}
			
			
			
			switch($this->status)
			{
				case 1:
				$action_flag = 32; // closed
				break;
				case 2:
				$action_flag = 33; // follow up 
				break;
				case 3:
				$action_flag = 34; // call back
				break;
				default:
				$action_flag = 31; // open
				break;
			}
			//echo $action_flag ;
			if($this->status == '2' && $this->data_city != 'REMOTE_CITIES' && $this->dept_id != '1' && $this->dept_id != '6' && $this->dept_id != '5')
			{
				$this->call_follow_up($this->data_city,$this->dept_id);
			}
			
			
			$contract_extradetails = "select companyname,original_creator from tbl_companymaster_extradetails where parentid='".$this->parentid."'";
			$res_extradetails	   = parent::execQuery($contract_extradetails, $this->conn_iro);
			if($res_extradetails && mysql_num_rows($res_extradetails))
			{
				$row_extradetails = mysql_fetch_assoc($res_extradetails);
			}
			
			//echo '<pre>';print_r($this->params);
		//	echo '<pre>';print_r($row_extradetails);
			
			$getcompname = $row_extradetails['companyname'];
			$getparentid = $this->parentid;
			
			
			//$this->Complain_Types = $this->get_complain_types($this->params['complaint_type_name']);
			
			$sql = "select complaintype_id from d_jds.tbl_complaintype where complaintype_name='".$this->params['complaint_type_name']."'";
			$res  = parent::execQuery($sql, $this->conn_iro);
			
		//	echo '<pre>';print_r($this->Complain_Types);
		//	die();
			$empcodes = $this->emp_code;
			
			
			
			
			if($row_extradetails['original_creator'])
			{
				$empcodes = $empcode.",','".$row_extradetails['original_creator'];
			}
					
			$sql_empdetails  = "SELECT empcode,CONCAT(empFName,' ',empLName) AS empName FROM emplogin WHERE empcode in ('".$empcodes."')";
			$res_empdetails  = parent::execQuery($sql_empdetails, $this->conn_iro);
			if($res_empdetails && mysql_num_rows($res_empdetails))
			{
				while($row_empdetails = mysql_fetch_assoc($res_empdetails))
				{
					$emp_detail[$row_empdetails['empcode']] =  $row_empdetails['empName'];
				}
				$this->company_name = $row_extradetails['companyname'];
				
				
				if($emp_detail[$this->emp_code])
				$updated_by_name = $emp_detail[$row_empdetails['empcode']];
				else
				$updated_by_name = $this->params['emp_name'];
				
				
				if($row_extradetails['original_creator'] && $emp_detail[$row_extradetails['original_creator']])
					$created_by_name = $emp_detail[$row_extradetails['original_creator']];
				else if ($row_extradetails['original_creator'])
					$created_by_name = $row_extradetails['original_creator'];
				
			}
			
			if($this->status == 1) 
			{
				$complain_resolved_date 	= date("Y-m-d H:i:s");
				$complain_resolved_by		= $this->emp_code;
				$complain_resolved_by_name	= $this->params['emp_name'];
				$updated_by_name = $this->params['emp_name'];
			}
			else
			{
				$updated_by_name = $this->params['emp_name'];
			}
			
			
			
			if($this->complaint_id)
			{
				//echo $this->status;
				//print_r($this->params);
				//print_r($this->params);
				$sql_ins_other = "Insert into log_complain_details(`complaintid`,`updated_date`,`updatedby`,`Description`,`caller_name`,`caller_number`,`dept_id`,`attachment`)values('".$this->complaint_id."',now(),'".$this->emp_code."','".addslashes(stripslashes($this->description))."','".addslashes(stripslashes(mysql_escape_string($this->caller_name)))."','".addslashes(stripslashes(mysql_escape_string($this->caller_num)))."','".$this->dept_id."','".$this->attachment_str."')";
				$res_ins_other = parent::execQuery($sql_ins_other, $this->local_d_jds);
				
				if($this->status == '1')
				{
					$resolution_flag_columns = " resolutionflag='".$this->status."', complain_resolved_date='".$complain_resolved_date."', resolvedby='".$complain_resolved_by."', resolvedby_name='".$complain_resolved_by_name."', department_id='".$this->dept_id."' ";
				}
				else if($this->status == '3')
				{
					$resolution_flag_columns = " resolutionflag='".$this->status."', registeredby='".$this->emp_code."',registeredby_name = '".$updated_by_name."',registeredby_roleid='".$this->emp_type."',callback_time='".$this->call_back_time."' ";
				}
				else
				{
					$resolution_flag_columns = " resolutionflag='".$this->status."', department_id='".$this->dept_id."',registeredby_roleid='".$this->emp_type."', registeredby='".$this->emp_code."',registeredby_name = '".$updated_by_name."',comp_category_id='".addslashes(stripslashes(mysql_escape_string($catids)))."' ,comp_category_name='".addslashes(stripslashes(mysql_escape_string($catnames)))."'";
				}
				if($this->Source_Types == 'JD Web - Complaint Through Website')
				$resolution_flag_columns .=",complain_type ='".$this->Complain_Types."'";
			
				$sql_update  = "update log_complain_main set ".$resolution_flag_columns." where autoid='".$this->complaint_id."'";
				$res_update  = parent::execQuery($sql_update, $this->local_d_jds);
				
				if($this->params['parentid']) {
				$this->parentid = $this->params['parentid'];
			}else{
			    $errorarray['errormsg']='parentid missing';
				echo json_encode($errorarray); exit;
			}
			
			
			if($this->params['module']) {
				$this->module = $this->params['module'];
			}else{
				$errorarray['errormsg']='module missing';
				echo json_encode($errorarray); exit;
			}
		
			
			
			$sendEmailtoclient	= $this->sendMail($this->email,$this->mobile_number,$this->company_name,$this->parentid,$this->status,$this->Complain_Types,$this->description,$this->complaint_id,$this->Source_Types);
			
			}
			else
			{
				
				$excludearr=array();
				$getcomplaintypesql="select complaintype_id from tbl_complaintype  where display_flag=1 and complaintype_name in ('Data Changes','Contract Verification','Data Validation - Other Project','Old Data');";
				$getcomplaintyperes = parent::execQuery($getcomplaintypesql, $this->local_d_jds);
				if($getcomplaintyperes && mysql_num_rows($getcomplaintyperes)){
					while($resrow = mysql_fetch_assoc($getcomplaintyperes))
						array_push($excludearr,$resrow['complaintype_id']);

				}
				if(!in_array($this->Complain_Types, $excludearr)){
				$checksql="select autoid from log_complain_main where complain_type='".$this->Complain_Types."' and (complain_resolved_date >= DATE_SUB(NOW(), INTERVAL 15 DAY)) and parentid='".$this->parentid."' and resolutionflag=1 order by complain_resolved_date desc limit 1";
					$checksqlres = parent::execQuery($checksql, $this->local_d_jds);
					if($checksqlres && mysql_num_rows($checksqlres)){
						$repeat_flag = 1;
						while($resrow = mysql_fetch_assoc($checksqlres))
							$repeat_id=$resrow['autoid'];

					}
				}
				
				$check = $this->Validate_data();
				if($check['status'] == '400')
				{
					return $check;
					die;
				}
				//die;
			//	echo '<pre>';print_r($this->params);		
				$sql= "Insert into log_complain_main(`parentid`,`company_Name`,`resolutionflag`,`complain_registration_date`,`registeredBy`,`complain_resolved_date`,`resolvedby`,`complain_type`,`complain_source`,`standard_complainID`,`sub_source`,`callback_time`,`caller_name`,`caller_number`,`comp_category_id`,`comp_category_name`,`contract_created_by`,`contract_created_by_name`,`registeredby_name`,`me_code`,`me_name`,`tme_code`,`tme_name`,`resolvedby_name`,`data_city`,`reason`,`department_id`,`registeredby_roleid`,`repeat_complain`,`repeat_complain_id`)values('".$this->parentid."','".addslashes(stripslashes($row_extradetails['companyname']))."','".$this->status."',now(),'".$this->emp_code."','".$complain_resolved_date."','".$complain_resolved_by."','".$this->Complain_Types."','".$this->Source_Types."','".$newstandardid."','".$this->subsource."','".$this->call_back_time."','".addslashes(stripslashes(mysql_escape_string($this->caller_name)))."','".addslashes(stripslashes(mysql_escape_string($this->caller_num)))."','".addslashes(stripslashes(mysql_escape_string($catids)))."','".addslashes(stripslashes(mysql_escape_string($catnames)))."','".addslashes(stripslashes(mysql_escape_string($row_extradetails['original_creator'])))."','".addslashes(stripslashes(mysql_escape_string($created_by_name)))."','".addslashes(stripslashes(mysql_escape_string($updated_by_name)))."','".addslashes(stripslashes(mysql_escape_string($me_code)))."','".addslashes(stripslashes(mysql_escape_string($me_name)))."','".addslashes(stripslashes(mysql_escape_string($tme_code)))."','".addslashes(stripslashes(mysql_escape_string($tme_name)))."','".addslashes(stripslashes(mysql_escape_string($complain_resolved_by_name)))."','".addslashes(stripslashes(mysql_escape_string($this->data_city)))."','".$this-> reason_types."','".$this->dept_id."','".$this->emp_type."','".$repeat_flag."','".$repeat_id."')";
				
				$res = parent::execQuery($sql, $this->local_d_jds);
				$resID = $this->mysql_insert_id;
				if($resID>=0)
				{
					
					$sql1="Insert into log_complain_details(`complaintid`,`updated_date`,`updatedby`,`Description`,`caller_name`,`caller_number`,`dept_id`,`attachment`)values('".$resID."',now(),'".$this->emp_code."','".addslashes(stripslashes($this->description))."','".addslashes(stripslashes(mysql_escape_string($this->caller_name)))."','".addslashes(stripslashes(mysql_escape_string($this->caller_num)))."','".$this->dept_id."','".$this->attachment_str."')";
					$res1 = parent::execQuery($sql1, $this->local_d_jds);
				}
				
			
			$sendEmailtoclient	= $this->sendMail($this->email,$this->mobile_number,$this->company_name,$this->parentid,$this->status,$this->Complain_Types,$this->description,$resID,$this->Source_Types);
			//$sendEmailtoclient	= sendMail($emailid,$mobileNo,$CompN,$getparentid,$Status_Flag,$type_insert,$newstandardid,$new_compl_autoid,$Sources);	
				
			}
			
			
			if($this->contract_type == 'lead')
			{	
				
				if($this->status == 0)
				{
					$lead_log_insert = "INSERT INTO d_jds.tbl_new_lead_log (parentid,tmecode,tmename,update_date,action_flag,insert_date) values('".$this->parentid."','".$this->emp_code."','".addslashes(stripslashes(mysql_escape_string($this->emp_name)))."',NOW(),'".$action_flag."',NOW())";
					
				}
				else
				{
				$lead_log_insert = "INSERT INTO d_jds.tbl_new_lead_log (parentid,tmecode,tmename,update_date,action_flag,insert_date) values('".$this->parentid."','".$this->emp_code."','".addslashes(stripslashes(mysql_escape_string($this->emp_name)))."',NOW(),'".$action_flag."',NOW())";
				}
				
				$lead_log_insert_res = parent::execQuery($lead_log_insert, $this->local_d_jds);
				
				$update_main_lead = "UPDATE tbl_new_lead SET tmecode = '".$this->emp_code."',tmename = '".$this->emp_name."',update_date = NOW(),action_flag = '".$action_flag."' WHERE parentid = '".$this->parentid."'";
				$lead_insert_res = parent::execQuery($update_main_lead, $this->local_d_jds);
			}
			
			
			$return_array['status'] = '200'; 
			$return_array['msg'] = 'Data submitted';
			
			return $return_array;
	}
	function Validate_data()
	{
		//echo $this->Complain_Types;
		$sql_comp = "SELECT * FROM tbl_complaintype WHERE complaintype_id = '".$this->Complain_Types."'";
		$res_comp = parent::execQuery($sql_comp, $this->local_d_jds);
		
		$sql_src = "SELECT * FROM tbl_sourcetype WHERE source_name = '".$this->Source_Types."'";
		$res_src = parent::execQuery($sql_src, $this->local_d_jds);
		
		if(mysql_num_rows($res_comp) > 0 && mysql_num_rows($res_src) > 0)
		{
			$check['status'] = '200'; 
			$check['msg'] = 'Data submitted';
		}
		else
		{
			$check['status'] = '400'; 
			$check['msg'] = 'Incorrect Data submitted';
		}
		
		return $check;
	}
	
	function sendMail($emailid,$mobileNo,$Company,$parent_id,$Status_Flag,$type_insert,$newstandardid,$autoid,$source)
	{
	
	$email 	 = $emailid;
	$source_text='';
	$complain_type_text='';
	
    	if(isset($autoid) && $autoid!=''){

    		$source_text=$type_insert;
			//$complain_type_text=$source;
			$sql_comp = "select complain_type from d_jds.log_complain_main where autoid='".$autoid."'";
			$res_comp = parent::execQuery($sql_comp, $this->local_d_jds);
			while($res_row=mysql_fetch_assoc($res_comp)){
				$type=$res_row['complain_type'];
			}
			
			
			$sql = "select complaintype_name from tbl_complaintype where complaintype_id='".$type."'";
			$res = parent::execQuery($sql, $this->local_d_jds);
			//$res=$conn_local->query_sql($sql);
			
			while($res_row=mysql_fetch_assoc($res)){
				$complain_type_text=$res_row['complaintype_name'];
			}
			
    	}
    	else{
    		$type=$type_insert;

			$sql = "select complaintype_name from tbl_complaintype where complaintype_id='".$type."'";
			$res = parent::execQuery($sql, $this->local_d_jds);
			//$res=$conn_local->query_sql($sql);
			
			while($res_row=mysql_fetch_assoc($res)){
				$complain_type_text=$res_row['complaintype_name'];
			}
    		//$source=$source;
    		$Sources_Array=array();
    		//$Sources_Array=array("Documents","Inbound Call","IRO","IT Report","JD Website","Mails","Other Forums","Other Projects","Outbound Call","Ticket","TME / ME Calls","Walk In", "Off-line contracts","Project-Changes","Projects- Duplicate anamoly","Complaint through website");//Array for sources
    		$selectSource = "select distinct(source_name) from d_jds.tbl_sourcetype where active_flag=1";
			$resSource = parent::execQuery($selectSource, $this->local_d_jds);
			
			if ($resSource && mysql_num_rows($resSource) > 0) {
				while($rowSource = mysql_fetch_assoc($resSource)){
					array_push($Sources_Array, $rowSource['source_name']);
				}
			}

    		for($i=0;$i<count($Sources_Array);$i++)
    		{
    			if($source==urlencode($Sources_Array[$i])){
    				$source_text=$Sources_Array[$i];
    			}
    		}
    	}
    	if(trim($_SERVER['SERVER_ADDR'])!='')
    	{
       	 $server_city = '';
       	 $server_indicators = explode(".", trim($_SERVER['SERVER_ADDR']));
        
	        if($_SERVER['SERVER_ADDR']=="192.168.17.217" ||$_SERVER['SERVER_ADDR']=="115.112.246.24" || $_SERVER['SERVER_ADDR']=="192.168.1.227")
	        {
	            //$server_city = 'REMOTE_CITIES';
	        }
	        elseif(is_array($server_indicators) && count($server_indicators)>3)
	        {
				define("MUMBAI_SPHINXIP", "172.29.0.227"); 
				define("DELHI_SPHINXIP", "172.29.8.227");
				define("KOLKATA_SPHINXIP", "172.29.16.227");
				define("BANGALORE_SPHINXIP", "172.29.26.227");
				define("CHENNAI_SPHINXIP", "172.29.32.227");
				define("PUNE_SPHINXIP", "172.29.40.227");
				define("HYDERABAD_SPHINXIP", "172.29.50.227");
				define("AHMEDABAD_SPHINXIP", "172.29.56.227");
				define("REMOTE_CITIES_SPHINXIP", "192.168.17.227");	
	            
	            switch($server_indicators[2])
	            {
	                case 0:
	                    $url = MUMBAI_SPHINXIP.":8001/company/getCards?type=extended&parentid=".$parent_id."&city=Mumbai";
	                break;
	                case 8:
	                    $url = DELHI_SPHINXIP.":8001/company/getCards?type=extended&parentid=".$parent_id."&city=Delhi";
	                break; 
	                case 16:
	                    $url = KOLKATA_SPHINXIP.":8001/company/getCards?type=extended&parentid=".$parent_id."&city=Kolkata";
	                break;
	                case 26:
	                    $url = BANGALORE_SPHINXIP.":8001/company/getCards?type=extended&parentid=".$parent_id."&city=Bangalore";
	                break;
	                case 32:
	                    $url = CHENNAI_SPHINXIP.":8001/company/getCards?type=extended&parentid=".$parent_id."&city=Chennai";
	                break;
	                case 40:
	                    $url = PUNE_SPHINXIP.":8001/company/getCards?type=extended&parentid=".$parent_id."&city=Pune";
	                break;
	                case 50:
	                    $url = HYDERABAD_SPHINXIP.":8001/company/getCards?type=extended&parentid=".$parent_id."&city=Hyderabad";
	                break;
	                case 56:
	                    $url  = AHMEDABAD_SPHINXIP.":8001/company/getCards?type=extended&parentid=".$parent_id."&city=Ahmedabad" ;
	                break; 
	                case 64:
	                    $url  = "http://pravinkucha.jdsoftware.com/iro_services/company/getCards?parentid=".$parent_id."&city=mumbai&type=extended" ;
	                break;            
	            }
	        }
    	}
    	
    	$ch = curl_init();        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$resultString = curl_exec($ch);
        curl_close($ch);
        $shorturlde=json_decode($resultString,true);
        $shorturl=$shorturlde['results']['exteneded']['shorturl'];
    	$editlink="http://jsdl.in/EL-$shorturl";
        $subject = "Your complaint registered with Justdial";
	
        if($Status_Flag==0)
        $status='Open';
        if($Status_Flag==2)
        $status='Follow Up';
        if($Status_Flag==3)
        $status='Call Back';
        if($Status_Flag==1)
        $status='Closed';
    	$time=date('d-m-y H:i');
		
		$cityforurl='';
		if(defined('REMOTE_CITY_MODULE'))
			$cityforurl='remote';
		else
			$cityforurl=DATA_CITY;
		$timestamp=rawurlencode(base64_encode(strtotime(date('Y-m-d H:i:s'))));
		
		
		
		$secret_key = "*(%@*&^@^%^%*(";

		// Create the initialization vector for added security.
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
		
		
		$autoid_en = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $secret_key, $autoid, MCRYPT_MODE_CBC, $iv);
		$autoid_en = rawurlencode((base64_encode($autoid_en)));
		$iv=rawurlencode((base64_encode($iv)));
		$autoid_en=$autoid_en;
		//$autoid_en=urlencode($autoid_en);
		$iv=str_replace('%2F', 'patch', $iv);
		$autoid_en=str_replace('%2F', 'patch', $autoid_en);
		$yeslink='';
		$nolink='';
		if($_SERVER["SERVER_ADDR"]==DEVLP_APP_IP){
			$yeslink="<a href='http://sumeetp.jdsoftware.com/new_website/CMPT-".$autoid_en."/".$cityforurl."/yes/".$timestamp."/".$iv."'  name='yeslink' id='yeslink'>Yes</a>";
	     	$nolink="<a href='http://sumeetp.jdsoftware.com/new_website/CMPT-".$autoid_en."/".$cityforurl."/no/".$timestamp."/".$iv."'  name='nolink' id='nolink'>No</a>";
		}
		else{
		 $yeslink="<a href='http://www.justdial.com/CMPT-".$autoid_en."/".$cityforurl."/yes/".$timestamp."/".$iv."'  name='yeslink' id='yeslink'>Yes</a>";
		$nolink="<a href='http://www.justdial.com/CMPT-".$autoid_en."/".$cityforurl."/no/".$timestamp."/".$iv."'  name='nolink' id='nolink'>No</a>";
		}
	
		

	  	$emailhtml.="<b>Complaint ID: </b>".$autoid."<br>";
	   	$emailhtml.="<b>Data & Time: </b>".date('d-m-y H:i')."<br>";
	   	$emailhtml.="<b>Type of Complaint: </b>".$complain_type_text."<br>";
	   	$emailhtml.="<b>Source: </b>".$source."<br>";
	   	$emailhtml.="<b>Status: </b>".$status."<br>";
	   	$emailhtml.="<b>Edit Link: </b>".$editlink."<br>";
	    if($status=='Closed'){
   			$emailhtml.="<br><br>";
			$emailhtml.="<style type='text/css'>"."\n";
			$emailhtml.="a:link {"."\n";
			$emailhtml.="text-decoration: none"."\n";
			$emailhtml.="color: #086AB7"."\n";
			$emailhtml.="}"."\n";
			$emailhtml.="a:visited {"."\n";
			$emailhtml.="text-decoration: none"."\n";
			$emailhtml.="}"."\n";
			$emailhtml.="a:hover {"."\n";
			$emailhtml.="text-decoration: none"."\n";
			$emailhtml.="}"."\n";
			$emailhtml.="a:active {"."\n";
			$emailhtml.="text-decoration: none"."\n";
			$emailhtml.="}"."\n";
			$emailhtml.=".style30 {font-size: 12}"."\n";
			$emailhtml.=".style31 {font-size: 10px}"."\n";
			$emailhtml.=".style32 {font-family: Verdana, Arial, Helvetica, sans-serif; color: #1274BF;}"."\n";
			$emailhtml.=".style34 {font-size: 10; }"."\n";
			$emailhtml.=".style36 "."\n";
			$emailhtml.=".style37 {color: #FB6C02}"."\n";
			$emailhtml.=".style38 {font-weight: bold}"."\n";
			$emailhtml.=".style39 {color: #FB6C02; font-weight: bold }"."\n";
			$emailhtml.=".style42 {"."\n";
			$emailhtml.="color: #1475C1;"."\n";
			$emailhtml.="font-family: Verdana, Arial, Helvetica, sans-serif;"."\n";
			$emailhtml.="font-size: 12px;"."\n";
			$emailhtml.="}"."\n";
			$emailhtml.=".style45 {color: #00CCFF}"."\n";
			$emailhtml.=".style46 {"."\n";
			$emailhtml.="color: #1374C1;"."\n";
			$emailhtml.="font-size: 10px;"."\n";
			$emailhtml.="}"."\n";
			$emailhtml.=".style48 {"."\n";
			$emailhtml.="color: #FB6C02"."\n";
			$emailhtml.="}"."\n";
			$emailhtml.=".style49 {font-size: 9px}"."\n";
			$emailhtml.=".style50 {"."\n";
			$emailhtml.="font-size: 12px"."\n";
			$emailhtml.="font-family: Verdana, Arial, Helvetica, sans-serif;"."\n";
			$emailhtml.="}"."\n";
			$emailhtml.=".style53 {}"."\n";
			$emailhtml.=".style56 {"."\n";
			$emailhtml.=""."\n";
			$emailhtml.=";"."\n";
			$emailhtml.="}"."\n";
			$emailhtml.=".style57 {"."\n";
			$emailhtml.=";"."\n";
			$emailhtml.=""."\n";
			$emailhtml.="}"."\n";
			$emailhtml.=".style58 {}"."\n";
			$emailhtml.=".style59 {color: #FF0000}"."\n";
			$emailhtml.="</style>"."\n";
			$emailhtml.="<table width='900' height='118' border='1' align='center' cellpadding='2' cellspacing='2' bordercolor='#1475C1'>"."\n";
			$emailhtml.="<tr>"."\n";
			$emailhtml.="<td><table width='100%' border='0' cellspacing='2' cellpadding='2'>"."\n";
			$emailhtml.="<tr>"."\n";
			$emailhtml.="<td><img src='http://images.jdmagicbox.com/email_banners/mumbai/Quiz/just-dial-logo-220X72.gif' width='175' height='61' />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>"."\n";
			$emailhtml.="</tr>"."\n";
			$emailhtml.="<tr>"."\n";
			$emailhtml.="<td><div align='center'><strong><span style='font-family: Verdana, Arial, Helvetica, sans-serif;font-size: 18px;color: #1475C1'>Feedback Survey by Just<span style='color: #FF6C01'>Dial</span></span><span  style='font-family: Verdana, Arial, Helvetica, sans-serif'><br />"."\n";
			$emailhtml.="<br />    "."\n";
			$emailhtml.="</span></strong><span  style='font-family: Verdana, Arial, Helvetica, sans-serif'><span style='color: #000000;font-size: 16px;'><strong>........ Help Us to Help You ........</strong></span></span><br />"."\n";
			$emailhtml.="</div></td>"."\n";
			$emailhtml.="</tr>"."\n";
			$emailhtml.="<tr>"."\n";
			$emailhtml.="<td height='12'>"."\n";
			$emailhtml.="<tr>"."\n";
			$emailhtml.="<td style='color: #1475C1;font-family: Verdana, Arial, Helvetica, sans-serif;font-size: 12px;'><h3>Are you happy with the solution provided by Customer Support Executive ?</h3></td>"."\n";
			$emailhtml.="<td><span style='color: #FF6C00'>"."\n";
			$emailhtml.="$yeslink&nbsp;&nbsp;$nolink"."\n";
			$emailhtml.="</td></tr>"."\n";
			$emailhtml.="<tr>"."\n";
			$emailhtml.="<td>&nbsp;</td>"."\n";
			$emailhtml.="<td>&nbsp;</td>"."\n";
			$emailhtml.="</tr></table>"."\n";
			$emailhtml.="</table>"."\n";
			$emailhtml.="<br><br>Warm Regards,<br>"."\n";
			$emailhtml.="Customer Support Unit<br>"."\n";
			$emailhtml.="8888888888 "."\n";
	   	}

	   	$html.="Complaint ID: ".$autoid."\n";
	   	$html.="Data & Time: ".date('d-m-y H:i')."\n";
	   	$html.="Type of Complaint: ".$complain_type_text."\n";
	   	$html.="Source: ".$source."\n";
	   	$html.="Status: ".$status."\n";
	   	$html.="Edit Link: ".$editlink."\n";
	   	
        $message =$emailhtml;
        $smstext=$html;
        $send_header .= 'MIME-Version: 1.0' . "\r\n";
        $send_header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $send_header.="from:noreply@justdial.com";
        
        
        if(strtolower($status) == 'closed'){
			
			$general_arr = $this->getEmailMobile("contact_person");
			
			$check_arr = array("Less Leads","No Leads","Irrelevant Leads","Out of Area Leads","No Conversion","Complaint regarding Masked number Lead","Complaint Against Incomplete Lead through Web / App / WAP","Complaint for position in no area search");
			
			$url_lead = '';
			if(empty($emailid))
			{
				
				if(count($general_arr['email']) > 0 && in_array($complain_type_text,$check_arr) && count($general_arr['contact_person']) > 0)
				{
					$url_lead = "http://192.168.20.101/10aug2016/client_lead.php?docid=".urlencode($shorturlde['results']['docid'])."&parentid=".urlencode($parent_id)."&name=".urlencode($general_arr['contact_person'][0])."&companyName=".urlencode($shorturlde['results']['exteneded']['compname'])."&to=".urlencode($general_arr['email'][0])."&city=".urlencode($this->data_city);
					
				}
				else if(count($general_arr['email']) > 0 && in_array($complain_type_text,$check_arr))
				{
					$url_lead = "http://192.168.20.101/10aug2016/client_lead.php?docid=".urlencode($shorturlde['results']['docid'])."&parentid=".urlencode($parent_id)."&name=&companyName=".urlencode($shorturlde['results']['exteneded']['compname'])."&to=".urlencode($general_arr['email'][0])."&city=".urlencode($this->data_city);
				}	
			}
			else
			{
				if(($general_arr['contact_person']) > 0)
				{
				$url_lead = "http://192.168.20.101/10aug2016/client_lead.php?docid=".urlencode($shorturlde['results']['docid'])."&parentid=".urlencode($parent_id)."&name=".urlencode($general_arr['contact_person'][0])."&companyName=".urlencode($shorturlde['results']['exteneded']['compname'])."&to=".urlencode($emailid)."&city=".urlencode($this->data_city);
				}
				else
				{
				$url_lead = "http://192.168.20.101/10aug2016/client_lead.php?docid=".urlencode($shorturlde['results']['docid'])."&parentid=".urlencode($parent_id)."&name=&companyName=".urlencode($shorturlde['results']['exteneded']['compname'])."&to=".urlencode($emailid)."&city=".urlencode($this->data_city);	
				}	
			}
			
			//$url_lead = "http://192.168.20.101/10aug2016//client_lead.php?docid=022PXX22.XX22.170427125056.K3U2&parentid=PXX22.XX22.170427125056.K3U2&name=vishal%20rana&companyName=ZXY Abhishek Store&to=vishal.rana@justdial.com&city=mumbai";
			if($url_lead != '')
			{
				$ch = curl_init();        
				curl_setopt($ch, CURLOPT_URL, $url_lead);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$resultString_lead = curl_exec($ch);
				
				if(DEBUG_MODE)
				{	
					echo '<pre>';
					print_r($resultString_lead);
				}
			}
		}
   		
	//echo $message;
	//print "<br>mobileNo : ".$mobileNo;
	
		
	if($email != '')
	{
		//mail($email,$subject,$message,$send_header);
		$insert_email = "INSERT INTO tbl_common_intimations
		(email_id,email_subject, email_text, source, sender_email,sender_name) 
		VALUES 
		('".addslashes($email)."','".addslashes($subject)."','".addslashes($message)."','Complain module','noreply@justdial.com','noreply@justdial.com')";
		$res_common_mail = parent::execQuery($insert_email, $this->messaging);
		
	}
    if(strlen($mobileNo)>=10){
		
        $sendSMSToClient = $this->sendSMS($mobileNo,$autoid,$Company,$parent_id,$Status_Flag,$type_insert,$newstandardid,$smstext);
    }
//print "<br>emailrracas : ".$email;exit;
	}
	
	function sendSMS($mobileNo,$autoid,$Company,$parent_id,$Status_Flag,$type_insert,$newstandardid,$smstext)
	{
    
	$smsmobile= $mobileNo;

	global $db;
	
	require_once('class_send_sms_email.php');
	
	$smsObj	 = new email_sms_send($db,$this->data_city); 
    
	
    if($smsmobile!='' && is_numeric($smsmobile)==true){
        
        $smsObj->sendSMS($smsmobile, $smstext , 'cs');
    }
    
	}
	
	function call_follow_up($server_city,$dept_ids)
	{
		$sql_dep = "select email_id,department_name from db_iro.tbl_complain_department where id='".$_REQUEST['follow_up_time']."'";
		$res_dep = parent::execQuery($sql_dep, $this->conn_iro);
		
		$row = mysql_fetch_assoc($res_dep);
		//$row = $conn_iro->fetchData($res_dep);
		
		$body = "";
		$body .= "Dear ".$row['department_name'].",<br><br>";	
		$body .="&nbsp;&nbsp;&nbsp;Please find below the complaint details which has been forwarded to you:<br><br>";
		$body .="Complaint Id	 - ".$autoid."<br>";
		$body .="Complaint Type  - ".$type_insert."<br>";
		$body .="Complaint Comment    - ".$Description."<br><br>";
		$body .="Request you to provide the solution at the earliest.<br><br>";
		$body .="Regards,<br>";
		$body .=ucwords($dependencies_arr['uname']);
		
		$sub = "Subject:- ".$autoid." (".$dependencies_arr['s_deptCity'].")- Escalation From CS";
		$curlurl 	= "http://".SSO_IP."/hrmodule/employee/fetch_employee_info/".$dependencies_arr['ucode'];
		$ch = curl_init();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$curlurl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_POST, TRUE);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$postarr);
		$resultString = curl_exec($ch);
		$emp_detail = json_decode($resultString,true);
	//$sender = "naresh.bhati@justdial.com";
	//	print_r($emp_detail['data']['email_id']);
		
		$insert_email = "INSERT INTO tbl_common_intimations
		(email_id,email_subject, email_text, source, sender_email,sender_name) 
		VALUES 
		('".addslashes($row['email_id'])."','".addslashes($sub)."','".addslashes($body)."','Complain module','".$emp_detail['data']['email_id']."','".$emp_detail['data']['email_id']."')";
	
		$res_common_mail = parent::execQuery($insert_email,$this->messaging);
		//$res_common_mail = $this->messaging->query_sql($insert_email);
		
	}
	
	
	function FetchSubComplaintType(){
	
	
		switch(strtolower($this->module))
		{
			case 'cs':
			$sql_con = "";
			$sql_role = "SELECT  role_name FROM db_iro.tbl_role_master WHERE subrole_id = '85'";
			$res_role = parent::execQuery($sql_role, $this->conn_iro);
			if(DEBUG_MODE)
			{
				echo '<br> sql :: '.$sql_role;
				echo '<br> res :: '.$res_role;
				echo '<br> num rows :: '.mysql_num_rows($res_role);
			}
			if($res_role && mysql_num_rows($res_role)>0 && trim($this->uemptype))
			{
				
				while($row_role = mysql_fetch_assoc($res_role))
				{
					$role_fb_template[] = strtolower(trim($row_role['role_name']));
				}
				if(in_array(strtolower(trim($this->uemptype)),$role_fb_template))
				{
					$sql_con = " AND eligible_source&8=8 ";
				}
				else
				{
					$sql_con = " ";
				}
			}
			
			
			break;
			case 'tme':
					$sql_con = " AND eligible_source&4=4 ";
			break;
			case 'me':
					$sql_con = " AND eligible_source&4=4 ";
			break;
		}
		
		if(DEBUG_MODE)
		{
			echo '<br> sql cond :: '.$sql_con;
		}
		
		$sql = "SELECT complaintype_id,complaintype_name FROM tbl_complaintype where display_flag =1 and sub_parentid='".$this->params['cid']."'
				 ".$sql_con." ";
		$res = parent::execQuery($sql, $this->local_d_jds);
		
		if(DEBUG_MODE)
		{
			print_r($this->local_d_jds);
			echo '<br> sql :: '.$sql;
			echo '<br> res :: '.$res;
			echo '<br> num rows :: '.mysql_num_rows($res);
		}
		
		if($res && mysql_num_rows($res))
		{
			$i = 0;
			while($row = mysql_fetch_assoc($res))
			{
				$complaint_list_arr[$i]['cid']   = $row['complaintype_id'];
				$complaint_list_arr[$i]['cname'] = $row['complaintype_name'];
				$i++;
			}
			
			if(DEBUG_MODE)
			{
				echo '<pre>';
				print_r($complaint_list_arr);
			}
			
			
		}
		else
		{
				$complaint_list_arr['msg']   		 = 'No Child found';
				$complaint_list_arr['error_msg'] = '400';
		}
		
		return $complaint_list_arr;
		
	}
	
	function get_department_id($dept_name)
	{
		$sql_dept = "SELECT id FROM db_iro.tbl_complain_department WHERE department_name='".$dept_name."' and display_flag =1 limit 1";
		$res_dept = parent::execQuery($sql_dept, $this->conn_iro);
		//$i = 0;
		
		$row_dept = mysql_fetch_assoc($res_dept);

		return $row_dept['id'];
		
	}
	
	function Fetchautoid_details()
	{
		//echo '<pre>';print_r($this->params);
		$sql_dept = "SELECT a.complain_source,CASE resolutionflag WHEN '1' THEN 'Closed' WHEN '0' THEN 'Open' WHEN '2' THEN 'Follow Up' WHEN '3' THEN 'Call Back' END AS resolutionflag,b.complaintype_name,b.complaintype_id 
		FROM 
		d_jds.log_complain_main AS a
		JOIN d_jds.tbl_complaintype AS b
		ON
		a.complain_type = b.complaintype_id
		WHERE autoid='".$this->params['autoid']."'";
		$res_dept = parent::execQuery($sql_dept, $this->conn_iro);
		$return_arr = array();
		if($res_dept && mysql_num_rows($res_dept))
		{
			$i=0;
			while($row_dept = mysql_fetch_assoc($res_dept))
			{
				$return_arr['complain_source'] 			= $row_dept['complain_source'];
				$return_arr['status'] 					= $row_dept['resolutionflag'];
				$return_arr['complaintype_name'] 		= $row_dept['complaintype_name'];
				$return_arr['complaintype_id'] 			= $row_dept['complaintype_id'];
			}
		}
		return $return_arr;
	}
	
	function cmplain_history($parentid)
	{
		$sql_history = "select count(1) as cnt from log_complain_main where parentid ='".$parentid."'";
		$res_history = parent::execQuery($sql_history, $this->local_d_jds);
		$row_history = mysql_fetch_assoc($res_history);
		return $row_history['cnt']; 
	}
}


?>
