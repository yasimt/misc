<?php
class ecsMandateClass extends DB
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
	

	var  $catsearch	= null;
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	var $omni_duration;	
	function __construct($params)
	{		
		$this->params = $params;		
		

		
		/* Code for companymasterclass logic starts */
		if($this->params['is_remote'] == 'REMOTE')
		{
			$this->is_split = FALSE;	 // when split table goes live then make it TRUE		
		}
		else
		{
			$this->is_split = FALSE;			
		}
		$result_msg_arr=array();
		
		if(trim($this->params['action']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Action Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->action  = $this->params['action']; 

		if($this->action!='1' && $this->action!='8' && $this->action!='9'&& $this->action!='10' && $this->action!='11' && $this->action!='12' && $this->action!='13'){ 
			if(trim($this->params['parentid']) == "")
			{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Parentid Missing"; 
					echo json_encode($result_msg_arr);exit;
			}
			else
				$this->parentid  = $this->params['parentid']; 

			if(trim($this->params['version']) == "")
			{
				/*$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "version Missing";
				echo json_encode($result_msg_arr);exit;*/
			}
			else
				$this->version  = $this->params['version']; 

		}
		if($this->action=='3'){
			if(trim($this->params['acc_num']) == "")
			{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Account Number Missing";
				echo json_encode($result_msg_arr);exit;
			}
			else
				$this->acc_num  = $this->params['acc_num'];

			if(trim($this->params['acc_hld_name']) == "")
			{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Account Holder Name Missing";
				echo json_encode($result_msg_arr);exit;
			}
			else
				$this->acc_hld_name  = $this->params['acc_hld_name'];

			if(trim($this->params['acc_type']) == "")
			{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Account Type is Missing";
				echo json_encode($result_msg_arr);exit;
			}
			else
				$this->acc_type  = $this->params['acc_type'];

			


		}
		if($this->action=='3' || $this->action=='1' ){

			if(trim($this->params['ifsc_code']) == "")
			{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "IFSC Code Missing";
				echo json_encode($result_msg_arr);exit;
			}
			else
				$this->ifsc_code  = $this->params['ifsc_code']; 

		}
		if(trim($this->params['module']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->module  = $this->params['module']; 

		
		
		if(trim($this->params['data_city']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Data City Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->data_city  = $this->params['data_city']; 

		if(trim($this->params['req_text']) != "")
		{
			$this->req_text  = $this->params['req_text']; 
		}
		if(trim($this->params['req_bank_text']) != "")
		{
			$this->req_bank_text  = $this->params['req_bank_text']; 
		}
		if(trim($this->params['req_city_text']) != "")
		{
			$this->req_city_text  = $this->params['req_city_text']; 
		}
		if(trim($this->params['req_branch_text']) != "")
		{
			$this->req_branch_text  = $this->params['req_branch_text']; 
		}

		$status=$this->setServers();
		if($status==-1)
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Missing";
			return $result_msg_arr;
		}
		$this->bank_name=$this->params['bank_name'];
		$this->branch_location=$this->params['branch_location'];
		$this->bank_branch=$this->params['bank_branch'];
		$this->micr_code=$this->params['micr_code'];

		$this->omni_duration =1520;
		
		//echo json_encode('const'); exit;
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;

		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		
		switch(strtolower($this->module))
		{
			case 'cs':
			$this->conn_temp = $db[$data_city]['d_jds']['master'];
			$this->conn_main = $db[$data_city]['d_jds']['master'];
			$this->conn_finance_temp = $db[$data_city]['fin']['master'];
			$this->conn_finance = $db[$data_city]['fin']['master'];
			$this->conn_to_idc = $db[$data_city]['fin']['master'];
			break;
			case 'tme':
		
			$this->conn_temp = $db[$data_city]['tme_jds']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			$this->conn_to_idc = $db[$data_city]['idc']['master'];

			break;
			case 'me':
			$this->conn_temp = $db[$data_city]['idc']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			$this->conn_to_idc = $db[$data_city]['idc']['master'];
			break;
			case 'jda':
			//$this->conn_temp = 
			break;
			default:
			return -1;
			break;
		}

	}
	function getBankDetails()
	{
			//$sql= "select bank,branch,bank_address,location from tbl_ecs_bankdetails where ifsc_code='".$this->ifsc_code."'";
			
			$sql= "select * from online_regis.tbl_ifsc_code where ifsc='".$this->ifsc_code."'";
			$res = parent::execQuery($sql, $this->conn_to_idc);

			//$res = parent::execQuery($sql, $this->conn_finance);
			$bankdetails=array();
			if($res && mysql_num_rows($res) )
			{
					while($row_bank_details = mysql_fetch_assoc($res)){
						$bankdetails['bank_name']=$row_bank_details['BANK'];
						$bankdetails['bank_branch']=$row_bank_details['BRANCH'];
						$bankdetails['bank_address']=$row_bank_details['ADDRESS'];
						$bankdetails['branch_location']=$row_bank_details['CITY'];
						$bankdetails['branch_micr']=$row_bank_details['MICR'];
						$bankdetails['branch_ifsc']=$row_bank_details['IFSC'];
					}
					$result_msg_arr['error']['code'] = 0;
					$result_msg_arr['error']['msg'] = 'Details Found';
					$result_msg_arr['error']['result'] = $bankdetails;
					return $result_msg_arr;

			}else
			{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Bank Details Not Found!";
					echo json_encode($result_msg_arr);exit;
			}

	}
	function getBankDetailsmicr()
	{
			//$sql= "select bank,branch,bank_address,location from tbl_ecs_bankdetails where ifsc_code='".$this->ifsc_code."'";
			
			$sql= "select * from online_regis.tbl_ifsc_code where MICR='".$this->micr_code."'";
			$res = parent::execQuery($sql, $this->conn_to_idc);

			//$res = parent::execQuery($sql, $this->conn_finance);
			$bankdetails=array();
			if($res && mysql_num_rows($res) )
			{
					while($row_bank_details = mysql_fetch_assoc($res)){
						$bankdetails['bank_name']=$row_bank_details['BANK'];
						$bankdetails['bank_branch']=$row_bank_details['BRANCH'];
						$bankdetails['bank_address']=$row_bank_details['ADDRESS'];
						$bankdetails['branch_location']=$row_bank_details['CITY'];
						$bankdetails['branch_ifsc']=$row_bank_details['IFSC'];
						$bankdetails['branch_micr']=$row_bank_details['MICR'];
					}
					$result_msg_arr['error']['code'] = 0;
					$result_msg_arr['error']['msg'] = 'Details Found';
					$result_msg_arr['error']['result'] = $bankdetails;
					return $result_msg_arr;

			}else
			{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Bank Details Not Found!";
					echo json_encode($result_msg_arr);exit;
			}

	}
	function bankNameAutoSuggest()
	{
			$sql= "select distinct bank as bank from online_regis.tbl_ifsc_code where bank like '%".$this->req_text."%' limit 10";
			$res = parent::execQuery($sql, $this->conn_to_idc);
			$bank_name=array();
			if($res && mysql_num_rows($res) )
			{
					while($row_bank_details = mysql_fetch_assoc($res)){
						
						$bank_name[]=$row_bank_details['bank'];
					}
					$result_msg_arr['error']['code'] = 0;
					$result_msg_arr['error']['msg'] = 'Details Found';
					$result_msg_arr['error']['result'] = $bank_name;
					return $result_msg_arr;

			}else
			{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Bank Details Not Found!";
					echo json_encode($result_msg_arr);exit;
			}

	}
	function bankCityAutoSuggest()
	{
			$sql= "select distinct city as city from online_regis.tbl_ifsc_code where city like '%".$this->req_text."%' and bank='".$this->req_bank_text."' limit 10";
			$res = parent::execQuery($sql, $this->conn_to_idc);
			$bank_name=array();
			if($res && mysql_num_rows($res) )
			{
					while($row_bank_details = mysql_fetch_assoc($res)){
						
						$bank_name[]=$row_bank_details['city'];
					}
					$result_msg_arr['error']['code'] = 0;
					$result_msg_arr['error']['msg'] = 'Details Found';
					$result_msg_arr['error']['result'] = $bank_name;
					return $result_msg_arr;

			}else
			{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Bank Details Not Found!";
					echo json_encode($result_msg_arr);exit;
			}

	}
	function bankBranchAutoSuggest()
	{
			$sql= "select distinct branch as branch from online_regis.tbl_ifsc_code where branch like '%".$this->req_text."%' and bank='".$this->req_bank_text."' and city='".$this->req_city_text."' limit 10";
			$res = parent::execQuery($sql, $this->conn_to_idc);
			$bank_name=array();
			if($res && mysql_num_rows($res) )
			{
					while($row_bank_details = mysql_fetch_assoc($res)){
						
						$bank_name[]=$row_bank_details['branch'];
					}
					$result_msg_arr['error']['code'] = 0;
					$result_msg_arr['error']['msg'] = 'Details Found';
					$result_msg_arr['error']['result'] = $bank_name;
					return $result_msg_arr;

			}else
			{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Bank Details Not Found!";
					echo json_encode($result_msg_arr);exit;
			}

	}
	function bankIfsc(){
		$sql= "select ifsc from online_regis.tbl_ifsc_code where branch like '%".$this->req_text."%' and bank='".$this->req_bank_text."' and city='".$this->req_city_text."' and branch='".$this->req_branch_text."'";
		$res = parent::execQuery($sql, $this->conn_to_idc);
		$bank_name=array();
		if($res && mysql_num_rows($res) )
		{
				while($row_bank_details = mysql_fetch_assoc($res)){
					
					$bank_name=$row_bank_details['ifsc'];
				}
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = 'Details Found';
				$result_msg_arr['error']['result'] = $bank_name;
				return $result_msg_arr;

		}else
		{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Bank Details Not Found!";
				echo json_encode($result_msg_arr);exit;
		}
	}
	function bankMICR(){
		$sql= "select MICR from online_regis.tbl_ifsc_code where branch like '%".$this->req_text."%' and bank='".$this->req_bank_text."' and city='".$this->req_city_text."' and branch='".$this->req_branch_text."'";
		$res = parent::execQuery($sql, $this->conn_to_idc);
		$bank_name=array();
		if($res && mysql_num_rows($res) )
		{
				while($row_bank_details = mysql_fetch_assoc($res)){
					
					$bank_micr=$row_bank_details['MICR'];
				}
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = 'Details Found';
				$result_msg_arr['error']['result'] = $bank_micr;
				return $result_msg_arr;

		}else
		{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Bank Details Not Found!";
				echo json_encode($result_msg_arr);exit;
		}
	}
	function mysql_real_escape_custom($string){
		
		$con = mysql_connect($this->conn_finance[0], $this->conn_finance[1], $this->conn_finance[2]) ;
		if(!$con){
			return $string;
		}
		$escapedstring=mysql_real_escape_string($string);
		return $escapedstring;

	}
	function saveMandateDetails(){
		/*$getBankDetails=$this->getBankDetails();
		
		if($getBankDetails['error']['code']==1){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Bank Details Not Found!";
			echo json_encode($result_msg_arr);exit;

		}
		else{

			$getBankDetails=$getBankDetails['error']['result'];

		}
*/
	 	$sql_save_mandate = "INSERT INTO tbl_omni_ecs_details_temp set
						parentid='".$this->parentid."',
						acName='".$this->mysql_real_escape_custom($this->acc_hld_name)."',
						acNo='".$this->acc_num."',
						bankname='".$this->mysql_real_escape_custom($this->bank_name)."',
						city='".$this->mysql_real_escape_custom($this->branch_location)."',
						actType='".$this->acc_type."',
						ifs='".$this->ifsc_code."',
						branch='".$this->mysql_real_escape_custom($this->bank_branch)."',
						MICR='".$this->micr_code."'
						ON DUPLICATE KEY UPDATE
						parentid='".$this->parentid."',
						acName='".$this->mysql_real_escape_custom($this->acc_hld_name)."',
						acNo='".$this->acc_num."',
						bankname='".$this->bank_name."',
						city='".$this->mysql_real_escape_custom($this->branch_location)."',
						ifs='".$this->ifsc_code."',
						actType='".$this->acc_type."',
						branch='".$this->mysql_real_escape_custom($this->bank_branch)."',
						MICR='".$this->micr_code."'";
		$res_disc = parent::execQuery($sql_save_mandate, $this->conn_temp);	
		if($res_disc)
		{
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
			echo json_encode($result_msg_arr);exit;
		}
		else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Failure";
			echo json_encode($result_msg_arr);exit;
		}

	}
	function getMandateDetails(){
		$sql= "select * from tbl_omni_ecs_details_temp where parentid='".$this->parentid."'";
		$res = parent::execQuery($sql, $this->conn_temp);
		$bankdetails=array();
		if($res && mysql_num_rows($res) )
		{
				while($row_bank_details = mysql_fetch_assoc($res)){
					$bankdetails['bank_name']=$row_bank_details['bankname'];
					$bankdetails['bank_branch']=$row_bank_details['branch'];
					$bankdetails['branch_location']=$row_bank_details['city'];
					$bankdetails['account_name']=$row_bank_details['acName'];
					$bankdetails['account_number']=$row_bank_details['acNo'];
					$bankdetails['account_type']=$row_bank_details['actType'];
					$bankdetails['ifsc_code']=$row_bank_details['ifs'];
					$bankdetails['micr_code']=$row_bank_details['MICR'];

				}
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = 'Details Found';
				$result_msg_arr['error']['result'] = $bankdetails;
				return $result_msg_arr;

		}else
		{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Account Details Not Found!";
				echo json_encode($result_msg_arr);exit;
		}
	}
	function TempToMainIdc(){
		$sql= "select * from tbl_omni_ecs_details_temp where parentid='".$this->parentid."'";
		$res = parent::execQuery($sql, $this->conn_temp);
		$bankdetails=array();
		if($res && mysql_num_rows($res) )
		{
				while($row_bank_details = mysql_fetch_assoc($res)){
					$bankdetails['bank_name']=$row_bank_details['bankname'];
					$bankdetails['bank_branch']=$row_bank_details['branch'];
					$bankdetails['branch_location']=$row_bank_details['city'];
					$bankdetails['account_name']=$row_bank_details['acName'];
					$bankdetails['account_number']=$row_bank_details['acNo'];
					$bankdetails['account_type']=$row_bank_details['actType'];
					$bankdetails['ifsc_code']=$row_bank_details['ifs'];

					 $sql_save_mandate = "INSERT INTO tbl_omni_ecs_details set
						parentid='".$this->parentid."',
						acName='".$row_bank_details['acName']."',
						acNo='".$row_bank_details['acNo']."',
						bankname='".$row_bank_details['bankname']."',
						city='".$row_bank_details['city']."',
						actType='".$row_bank_details['actType']."',
						ifs='".$row_bank_details['ifs']."',
						branch='".$row_bank_details['branch']."',
						MICR='".$row_bank_details['MICR']."'
						ON DUPLICATE KEY UPDATE
						acName='".$row_bank_details['acName']."',
						acNo='".$row_bank_details['acNo']."',
						bankname='".$row_bank_details['bankname']."',
						city='".$row_bank_details['city']."',
						actType='".$row_bank_details['actType']."',
						ifs='".$row_bank_details['ifs']."',
						branch='".$row_bank_details['branch']."',
						MICR='".$row_bank_details['MICR']."'";
						
				$res_disc = parent::execQuery($sql_save_mandate, $this->conn_to_idc);
					/*if($res_disc){
						$sqldel="delete from tbl_omni_ecs_details_temp where parentid='".$this->parentid."'";
						$sql_disc_del = parent::execQuery($sqldel, $this->conn_temp);
					}*/
				}
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = 'Saved Successfully!';
				return $result_msg_arr;

		}else
		{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "No Details Found In Temp!";
				echo json_encode($result_msg_arr);exit;
		}

	}
	function TempToMain(){
		$sql= "select * from tbl_omni_ecs_details where parentid='".$this->parentid."' ";
		$res = parent::execQuery($sql, $this->conn_to_idc);
		$bankdetails=array();
		if($res && mysql_num_rows($res) )
		{
				while($row_bank_details = mysql_fetch_assoc($res)){
					$bankdetails['bank_name']=$row_bank_details['bankname'];
					$bankdetails['bank_branch']=$row_bank_details['branch'];
					$bankdetails['branch_location']=$row_bank_details['city'];
					$bankdetails['account_name']=$row_bank_details['acName'];
					$bankdetails['account_number']=$row_bank_details['acNo'];
					$bankdetails['account_type']=$row_bank_details['actType'];
					$bankdetails['ifsc_code']=$row_bank_details['ifs'];

					$sql_save_mandate = "INSERT INTO tbl_omni_ecs_details set
						parentid='".$this->parentid."',
						acName='".$row_bank_details['acName']."',
						acNo='".$row_bank_details['acNo']."',
						bankname='".$row_bank_details['bankname']."',
						city='".$row_bank_details['city']."',
						actType='".$row_bank_details['actType']."',
						ifs='".$row_bank_details['ifs']."',
						branch='".$row_bank_details['branch']."'
						ON DUPLICATE KEY UPDATE
						acName='".$row_bank_details['acName']."',
						acNo='".$row_bank_details['acNo']."',
						bankname='".$row_bank_details['bankname']."',
						city='".$row_bank_details['city']."',
						actType='".$row_bank_details['actType']."',
						ifs='".$row_bank_details['ifs']."',
						branch='".$row_bank_details['branch']."'";
				$res_disc = parent::execQuery($sql_save_mandate, $this->conn_finance);
				}
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = 'Saved Successfully!';
				return $result_msg_arr;

		}else
		{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "No Details Found In IDC!";
				echo json_encode($result_msg_arr);exit;
		}

	}
	function MainToTemp(){
		$sql= "select * from tbl_omni_ecs_details where parentid='".$this->parentid."'";
		$res = parent::execQuery($sql, $this->conn_to_idc);
		$bankdetails=array();
		if($res && mysql_num_rows($res) )
		{
				while($row_bank_details = mysql_fetch_assoc($res)){
					$bankdetails['bank_name']=$row_bank_details['bankname'];
					$bankdetails['bank_branch']=$row_bank_details['branch'];
					$bankdetails['branch_location']=$row_bank_details['city'];
					$bankdetails['account_name']=$row_bank_details['acName'];
					$bankdetails['account_number']=$row_bank_details['acNo'];
					$bankdetails['account_type']=$row_bank_details['actType'];
					$bankdetails['ifsc_code']=$row_bank_details['ifs'];

					$sql_save_mandate = "INSERT INTO tbl_omni_ecs_details_temp set
						parentid='".$this->parentid."',
						acName='".$row_bank_details['acName']."',
						acNo='".$row_bank_details['acNo']."',
						bankname='".$row_bank_details['bankname']."',
						city='".$row_bank_details['city']."',
						actType='".$row_bank_details['actType']."',
						ifs='".$row_bank_details['ifs']."',
						branch='".$row_bank_details['branch']."',
						MICR='".$row_bank_details['MICR']."'
						ON DUPLICATE KEY UPDATE
						acName='".$row_bank_details['acName']."',
						acNo='".$row_bank_details['acNo']."',
						bankname='".$row_bank_details['bankname']."',
						city='".$row_bank_details['city']."',
						actType='".$row_bank_details['actType']."',
						ifs='".$row_bank_details['ifs']."',
						branch='".$row_bank_details['branch']."',
						MICR='".$row_bank_details['MICR']."'";
				$res_disc = parent::execQuery($sql_save_mandate, $this->conn_temp);
				}
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = 'Inserted Successfully!';
				return $result_msg_arr;

		}else
		{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "No Details Found In Main Tables";
				echo json_encode($result_msg_arr);exit;
		}

	}
	function getMandateDetailsCS(){
		$sql= "select * from tbl_omni_ecs_details where parentid='".$this->parentid."'";
		$res = parent::execQuery($sql, $this->conn_finance);
		$bankdetails=array();
		if($res && mysql_num_rows($res) )
		{
				while($row_bank_details = mysql_fetch_assoc($res)){
					$bankdetails['bank_name']=$row_bank_details['bankname'];
					$bankdetails['bank_branch']=$row_bank_details['branch'];
					$bankdetails['branch_location']=$row_bank_details['city'];
					$bankdetails['account_name']=$row_bank_details['acName'];
					$bankdetails['account_number']=$row_bank_details['acNo'];
					$bankdetails['account_type']=$row_bank_details['actType'];
					$bankdetails['ifsc_code']=$row_bank_details['ifs'];
					$bankdetails['micr_code']=$row_bank_details['MICR'];

				}
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = 'Details Found';
				$result_msg_arr['error']['result'] = $bankdetails;
				echo json_encode($result_msg_arr);exit;

		}else
		{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Account Details Not Found!";
				echo json_encode($result_msg_arr);exit;
		}
	}
	function curlCall($url,$data=null,$method='get'){
		global $genio_variables;
		global $dbarr;

			$ch = curl_init();        
			
	        curl_setopt($ch, CURLOPT_URL, $url);
	        //curl_setopt($ch, CURLOPT_URL, $param['url']);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	        curl_setopt($ch, CURLOPT_TIMEOUT, 180);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
	        if($method=='post'){
	        	
	        	curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

	        }
	        else if($method=='json'){
	        	$body = json_encode($data);
	        	curl_setopt($ch, CURLOPT_POST, true);
	        	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			/*	$fp = fopen('php://temp/maxmemory:256000', 'w');
				if (!$fp) 
				{
				    die('could not open temp memory data');
				}
				fwrite($fp, $body);
				fseek($fp, 0); 
				curl_setopt($ch, CURLOPT_PUT, 1);
				curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
				curl_setopt($ch, CURLOPT_INFILE, $fp); // file pointer
				curl_setopt($ch, CURLOPT_INFILESIZE, strlen($body)); */
				
				/*	curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
					'Content-Type: application/json',                                                                                
					'Content-Length: ' . strlen($body))                                                                       
				); */

	        }
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$resultString = curl_exec($ch);
	        curl_close($ch); 
			return $resultString;
	}
}
?>
