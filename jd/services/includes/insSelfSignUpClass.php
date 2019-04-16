<?php
class insSelfSignUpClass extends DB
{
	var  $conn_default  = null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var  $configobj		= null;
	
	function __construct($params,$db)
	{
		
		
		
		if(trim($params['parentid']) != "")
		{
			$this->params['parentid']	 	 = $params['parentid'];
		}
		else
		{
			$result['error_code']   = 1;
			$result['status'] = 'parentid missing';
			echo json_encode($result); exit;
		}		
		
		if(trim($params['data_city']) != "" && $params['data_city'] != null)
		{
			$this->params['data_city']	 	 = addslashes($params['data_city']);
		}
		else
		{
			$result['error_code']   = 1;
			$result['status'] = 'data_city missing';
			echo json_encode($result); exit;
		}
		
		if(trim($params['campaigninfo']) != "" && $params['campaigninfo'] != null)
		{
			$campaigninfo_arr = json_decode(trim($params['campaigninfo']), true);
			$campaigninfo_arr = array_filter($campaigninfo_arr);
			
			if( is_array($campaigninfo_arr) && count($campaigninfo_arr)>0 )
			{
				$this->params['campaigninfo'] 	 = addslashes($params['campaigninfo']);
			}
			else
			{
				$result['error_code']   = 1;
				$result['status'] = 'campaigninfo missing';
				echo json_encode($result); exit;
			}
			
		}
		else
		{
			$result['error_code']   = 1;
			$result['status'] = 'campaigninfo missing';
			echo json_encode($result); exit;
		}
		
		
		$this->params['companyname']  	 = addslashes($params['companyname']);
		$this->params['campaigninfo'] 	 = addslashes($params['campaigninfo']);
		$this->params['payment_type']	 = addslashes($params['payment_type']);
		$this->params['source'] 	 	 = addslashes($params['source']);
		$this->params['trans_id']	     = $params['trans_id'];
		$this->params['payment_done_on'] = $params['payment_done_on'];
		$this->params['requested_date']  = $params['requested_date'];
		$this->params['payment_id'] 	 = $params['payment_id'];
		$this->params['paymode'] 	  	 = addslashes($params['paymode']);
		$this->params['bank_ref_no'] 	 = $params['bank_ref_no'];
		$this->params['amount_paid']  	 = $params['amount_paid'];
		$this->params['version'] 	 	 = $params['version'];
		$this->params['user_code'] 	 	 = addslashes($params['user_code']);
		$this->params['user_name'] 	 	 = addslashes($params['user_name']);
		
		$this->params['user_type'] 	 	 = addslashes($params['user_type']);
		
		$this->params['emi'] 	 		 = $params['emi'];
		
		$this->params['balance'] 	 	 = $params['balance'];
		$this->params['dealclosetype']   = $params['dealclosetype'];
		
		$this->params['module'] 	 	 = addslashes($params['module']);
		
		
		//emi - ecs/si
		$this->params['payment_info']  	 = addslashes(json_encode(array('selPGMode'=>$params['selPGMode'],'tax'=>$params['tax'],'emi'=>$params['emi'],'balance'=>$params['balance'])));
		
		if($params['amount_paid']>0)
		  $this->params['payment_done_flag'] = 1;
		else
		  $this->params['payment_done_flag'] = 0;
		  
		  $this->params['done_flag'] = 0;
		
		$this->db 			= $db;
		$this->conn_default = $this->db['remote']['idc']['master'];
		//$this->setCommonInfo();
		//echo '<pre>';print_r($this->db);die;
		
	}
	// Function to set Common Information
	/*function setCommonInfo()
	{
		$this->configobj = new configclass();		
		
	}*/
	
	function processData()
	{
		if(trim($this->params['source']) == 'genio')
		{
			$sqlinsRecords = " INSERT INTO online_regis1.tbl_online_dealclose_contracts 		 (parentid,data_city,companyname,campaigninfo,payment_type,source,trans_id,requested_date,payment_done_on,payment_id,paymode,bank_ref_no,amount_paid,done_flag,payment_done_flag,payment_info,version,dealCloseType,user_code,user_name,user_type,processed_date,remarks,module)
			 VALUES ('".$this->params['parentid']."','".$this->params['data_city']."','".$this->params['companyname']."', '".$this->params['campaigninfo']."','".$this->params['payment_type']."','".$this->params['source']."','".$this->params['trans_id']."','".$this->params['requested_date']."','".$this->params['payment_done_on']."','".$this->params['payment_id']."','".$this->params['paymode']."','".$this->params['bank_ref_no']."','".$this->params['amount_paid']."','1','".$this->params['payment_done_flag']."','".$this->params['payment_info']."','".$this->params['version']."','".$this->params['dealclosetype']."','".$this->params['user_code']."','".$this->params['user_name']."','".$this->params['user_type']."','".date('Y-m-d H:i:s')."','geniocontract','".$this->params['module']."')
								 ON DUPLICATE KEY UPDATE  
								 companyname  	  = '".$this->params['companyname']."',
								 campaigninfo	  = '".$this->params['campaigninfo']."',
								 payment_type	  = '".$this->params['payment_type']."',
								 source		 	  = '".$this->params['source']."',
								 requested_date   = '".$this->params['requested_date']."',
								 payment_done_on  = '".$this->params['payment_done_on']."',
								 payment_id  	  = '".$this->params['payment_id']."',
								 paymode  		  = '".$this->params['paymode']."',
								 bank_ref_no  	  = '".$this->params['bank_ref_no']."',
								 amount_paid  	  = '".$this->params['amount_paid']."',
								 done_flag  	  = '1',
								 payment_done_flag= '".$this->params['payment_done_flag']."',
								 payment_info	  = '".$this->params['payment_info']."',
								 version	      = '".$this->params['version']."',
								 dealCloseType    = '".$this->params['dealclosetype']."',
								 user_code	      = '".$this->params['user_code']."',
								 user_name	      = '".$this->params['user_name']."',
								 user_type	      = '".$this->params['user_type']."',
								 processed_date   = '".date('Y-m-d H:i:s')."',
								 remarks	      = 'geniocontract',
								 module	     	  = '".$this->params['module']."'";
		}
		else
		{
			$sql_checkIfExist = "SELECT * FROM online_regis1.tbl_selfsignup_contracts WHERE parentid = '".$this->params['parentid']."' AND data_city = '".$this->params['data_city']."' AND trans_id = '".$this->params['trans_id']."'";
			$res_checkIfExist = parent::execQuery($sql_checkIfExist, $this->conn_default);
			
			if( mysql_num_rows($res_checkIfExist) <= 0 )
			{
				$sqlinsRecords = " INSERT INTO online_regis1.tbl_selfsignup_contracts 		 (parentid,data_city,companyname,campaigninfo,payment_type,source,trans_id,requested_date,payment_done_on,payment_id,paymode,bank_ref_no,amount_paid,done_flag,payment_done_flag,payment_info,version,dealCloseType,user_code,user_name,module)
				 VALUES ('".$this->params['parentid']."','".$this->params['data_city']."','".$this->params['companyname']."', '".$this->params['campaigninfo']."','".$this->params['payment_type']."','".$this->params['source']."','".$this->params['trans_id']."','".$this->params['requested_date']."','".$this->params['payment_done_on']."','".$this->params['payment_id']."','".$this->params['paymode']."','".$this->params['bank_ref_no']."','".$this->params['amount_paid']."','".$this->params['done_flag']."','".$this->params['payment_done_flag']."','".$this->params['payment_info']."','".$this->params['version']."','".$this->params['dealclosetype']."','".$this->params['user_code']."','".$this->params['user_name']."','".$this->params['module']."')
									 ON DUPLICATE KEY UPDATE  
									 companyname  	  = '".$this->params['companyname']."',
									 campaigninfo	  = '".$this->params['campaigninfo']."',
									 payment_type	  = '".$this->params['payment_type']."',
									 source		 	  = '".$this->params['source']."',
									 requested_date   = '".$this->params['requested_date']."',
									 payment_done_on  = '".$this->params['payment_done_on']."',
									 payment_id  	  = '".$this->params['payment_id']."',
									 paymode  		  = '".$this->params['paymode']."',
									 bank_ref_no  	  = '".$this->params['bank_ref_no']."',
									 amount_paid  	  = '".$this->params['amount_paid']."',
									 done_flag  	  = '".$this->params['done_flag']."',
									 payment_done_flag= '".$this->params['payment_done_flag']."',
									 payment_info	  = '".$this->params['payment_info']."',
									 version	      = '".$this->params['version']."',
									 dealCloseType    = '".$this->params['dealclosetype']."',
									 user_code	      = '".$this->params['user_code']."',
									 user_name	      = '".$this->params['user_name']."',
									 module	     	  = '".$this->params['module']."'";
									 
			 }
			 else if( $res_checkIfExist && mysql_num_rows($res_checkIfExist) > 0 )
			 {
				 $result['error_code'] = 1 ;
				 $result['status']	   = 'Deal Close Request Already Received !';
				 return $result;
			 }
		}
		$resinsRecords = parent::execQuery($sqlinsRecords, $this->conn_default);
		
		if($resinsRecords)
		{
			 $result['error_code'] = 0;
			 $result['status']	   = 'success';
			 return $result;
		}
		else
		{
			$result['error_code'] = 1;
			$result['status']	  = 'unsuccess';
			return $result;
		}
	}
}
