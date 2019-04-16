<?php

class complaints_against_staff_class extends DB
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
		
		if($this->params['action'] == 1)
		{
			if($this->params['data_type']) {
				$this->data_type = $this->params['data_type'];
			}else{
			    $errorarray['errormsg']='data_type missing';
				echo json_encode($errorarray); exit;
			}
			
			if($this->params['status']) {
				$this->status = $this->params['status'];
			}else{
				$this->status = 0;
			}
			
			
			if($this->params['from_date']) {
				$this->frmdate = $this->params['from_date'];
			}else{
			    $errorarray['errormsg']='from date missing';
				echo json_encode($errorarray); exit;
			}
			
			if($this->params['to_date']) {
				$this->to_date = $this->params['to_date'];
			}else{
			    $errorarray['errormsg']='End date missing';
				echo json_encode($errorarray); exit;
			}
			
			if($this->params['data_city']) {
				$this->data_city = $this->params['data_city'];
			}
			
			if($this->params['docid']) {
				$this->docid = $this->params['docid'];
			}
			
			if($this->params['companyname']) {
				$this->companyname = $this->params['companyname'];
			}
			if($this->params['other_city']) {
				$this->other_city = $this->params['other_city'];
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
			
			
			$this->status = $this->params['status'];
			
			if($this->params['employee_comment']) {
				$this->employee_comment = urldecode($this->params['employee_comment']);
			}else{
			    $errorarray['errormsg']='employee_comment missing';
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
		$this->web_conn   		= $db['website']['master'];
		//echo '<pre>';print_r($db[strtolower($this->data_city)]);exit;
		$this->d_jds   			= $db[strtolower($this->data_city)]['d_jds']['master'];
		
	}
	
		
	
		
	function GenerateReport()
	{
		
		if(strtolower($this->data_type) == "reported")
		$sql_check_column = "date(reported_date)";
		else {
		$sql_check_column = "date(resolved_date)";
		$this->status = 1;
		}
		if(strtolower(trim($this->data_city)) == 'remote')
		{
			$sql_cities=" SELECT ct_name FROM d_jds.tbl_city_master WHERE display_flag=1 AND DE_display=1 AND type_flag IN (0,1) AND mapped_cityname='".$this->other_city."' AND data_city!='".$this->other_city."'";
		
			$res_cities = parent::execQuery($sql_cities, $this->d_jds);
			if($res_cities && mysql_num_rows($res_cities))
			{
				$cites_other = array();
				while($row_cities= mysql_fetch_assoc($res_cities))
				{
						$cites_other[] = $row_cities['ct_name'];
				}
			} 
		}
	//	echo '<pre>';print_r($cites_other);die;
		if($this->docid)
		{
			$sql_con .= "AND docid = '".$this->docid."'";
		}
		
		if($this->companyname)
		{
			$sql_con .= "AND companyname like '".$this->companyname."%'";
		}
	//	echo $this->other_city;die;
		
		if(strtolower(trim($this->data_city)) != 'remote')
		{
			$sql_con .= "AND data_city='".$this->data_city."'";
		}
		else if(strtolower(trim($this->data_city)) == 'remote')
		{
			$sql_con .= "AND data_city IN ('".implode("','",$cites_other)."') and data_city NOT IN ('Jaipur','Chandigarh','Coimbatore')";
		}
		
		
		
		$sql= "SELECT * FROM tbl_complaint_against_staff WHERE ".$sql_check_column." BETWEEN '".$this->frmdate."' AND '".$this->to_date."' ".$sql_con." AND status='".$this->status."'";
		$res = parent::execQuery($sql, $this->web_conn);
		
		if(DEBUG_MODE)
		{
			echo '<br>sql  :: '.$sql;
			echo '<br>res  :: '.$res;
			echo '<br>rows  :: '.mysql_num_rows($res);
		}
		
		if($res && mysql_num_rows($res) )
		{
			$complaint_against_staff = array();
			$complaint_against_staff['data_count'] = mysql_num_rows($res);
			$j=0;
			while($row= mysql_fetch_assoc($res))
			{
				switch($row['deptid'])
				{
					case '1':
					$deptname = 'TME/ME';
					break;
					case '2':
					$deptname = 'Customer Support';
					break;
					case '3':
					$deptname = 'Voice';
					break;
					default:
					$deptname = 'Others';
					break;
				}
				$complaint_against_staff['data'][$j]['sn'] 		= ++$i;
				$complaint_against_staff['data'][$j]['cid'] 	= $row['id'];
				$complaint_against_staff['data'][$j]['dn'] 		=$deptname;
				$complaint_against_staff['data'][$j]['en'] 		=$row['employee_name'];
				$complaint_against_staff['data'][$j]['cts'] 	=$row['comments'];
				$complaint_against_staff['data'][$j]['ects'] 	=$row['employee_comment'];
				$complaint_against_staff['data'][$j]['dcid']  	=$row['docid'];
				$complaint_against_staff['data'][$j]['cn']    	=$row['companyname'];
				$complaint_against_staff['data'][$j]['st']    	=$row['status'];
				$complaint_against_staff['data'][$j]['rp_dt'] 	=$row['reported_date'];
				$complaint_against_staff['data'][$j]['rs_dt'] 	=$row['resolved_date'];
				$complaint_against_staff['data'][$j]['dct']   	=$row['data_city'];
				$j++;
				
			}
			if(DEBUG_MODE)
			{
				echo '<pre>';
				print_r($complaint_against_staff);
				echo '</pre>';
			}
				
			return $complaint_against_staff;
		}
		else
		{
			$errorarray['errormsg']='No data in requested query ';
				return $errorarray;
		}
			
	}
	
	function updateComplaints()
	{
		$sql = "UPDATE tbl_complaint_against_staff SET status='".$this->status."', resolved_date='".date('Y-m-d H:i:s')."', employee_comment='".addslashes($this->employee_comment)."' WHERE id='".$this->complaint_id."'";
		$res = parent::execQuery($sql, $this->web_conn);
		if(DEBUG_MODE)
		{
			echo '<br>sql  :: '.$sql;
			echo '<br>res  :: '.$res;
			
		}
		if($res)
		{
			$errorarray['errormsg']='successful ';
				return $errorarray;
		}else{
			$errorarray['errormsg']='unsuccessful ';
				return $errorarray;
		}
	}
		

	function readValidationCodeFromTable()
	{
		$validationcode=null;
		$sql= "select validationcode from mobilemail_verification_code where parentid='".$this->parentid."'";	
		$res = parent::execQuery($sql, $this->conn_temp);
		if(DEBUG_MODE)
		{
			echo '<br>sql  :: '.$sql;
			echo '<br>res  :: '.$res;
			echo '<br>rows  :: '.mysql_num_rows($res);
		}
		if($res && mysql_num_rows($res) )
		{
			$row= mysql_fetch_assoc($res);
			$validationcode= $row['validationcode'];
		}
		
		return $validationcode;	
	}
	
	
	
	
	

}



?>
