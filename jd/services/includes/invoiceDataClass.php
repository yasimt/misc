<?php

/**
* 
*/

class invoiceData extends DB
{
	function __construct($params)
	{
		$parentid = trim($params['parentid']);
		$city 	  = trim($params['city']);
		$module   = trim($params['module']);
		$doctype  = trim($params['doctype']);
		$filetype = trim($params['filetype']);
		$last_record   = trim($params['last_record']);
	
		if($parentid =='')
		{
			$message = "Parentid is Blank";
			echo json_encode($this->dieMessage($message));
			die;
		}
		if($city =='')
		{
			$message = "City is Blank";
			echo json_encode($this->dieMessage($message));
			die;
		}
		if($module =='')
		{
			$message = "Module is Blank";
			echo json_encode($this->dieMessage($message));
			die;
		}
		if($doctype =='')
		{
			$message = "Doctype is Blank";
			echo json_encode($this->dieMessage($message));
			die;
		}
		if($filetype =='')
		{
			$this->filetype = 'pdf';
		}
		else
		{
			$this->filetype =$filetype;
		}
		
		$this->last_record 	= $last_record;	
		$this->parentid 	= $parentid;
		$this->city 		= strtolower($city);
		$this->module 		= strtolower($module);
		$this->doctype 		= $doctype;	
		$this->dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
		$this->setServers();

	}
	function setServers()
	{	
		global $db;
					
		
		$data_city 		= ((in_array($this->city, $this->dataservers)) ? $this->city : 'remote');
		
		$this->conn_fin   			= $db[$data_city]['fin']['master'];
		
		switch($this->module)
		{
			case 'cs':
			$this->conn_temp = $db[$data_city]['d_jds']['master'];
			$this->conn_main = $db[$data_city]['d_jds']['master'];
			$this->conn_finance_temp = $db[$data_city]['fin']['master'];
			$this->conn_finance = $db[$data_city]['fin']['master'];
			break;
			case 'tme':
			$this->conn_temp = $db[$data_city]['tme_jds']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			break;
			case 'me':
			$this->conn_temp = $db[$data_city]['idc']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			break;
			case 'jda':
			//$this->conn_temp = 
			break;
			default:
			$errorarray['errormsg']='module missing';
			echo json_encode($errorarray); exit;
			break;
		}

	}
	function getInvoiceData()
	{
		$ftp_server = $this->getFtpServer();
		$ftp_user_name ="linux";
		$ftp_user_pass	="Te@mJD";
		$conn_id = ftp_connect($ftp_server);
		$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

		$modPath =  $this->getModPath();
		//echo "here".$this->parentid;
		$raw_records_arr  = array();
		$final_record_arr = array();
		if($this->doctype == "invoice")
		{
			$sql_invoice_arch="select filename from db_finance.tbl_invoice_proposal_details_arch where parentid ='".$this->parentid."' and filetype='pdf' and lower(module_type)='".$this->module."'";
			
			//echo "<br>conn: <pre>".print_r($this->conn_fin);
			$res_invoice_arch 	= parent::execQuery($sql_invoice_arch, $this->conn_fin);
			//$res_invoice_arch = $this->conn_fin->query_sql($sql_invoice_arch);
			if(mysql_num_rows($res_invoice_arch) > 0)
			{
				$server_file= $modPath."/logs/invoice/" ;
				while($result_inv_arch = mysql_fetch_assoc($res_invoice_arch))
				{
					//array_push($raw_records_arr,$result_inv_arch['filename']);
					$flag_value=1;
					$file_1 = strchr($result_inv_arch['filename'],'invoice');			
					$file_2 = substr($file_1,7);			
					$file_3 = explode(".",$file_2);				
					$raw_records_arr[$result_inv_arch['filename']] = $file_3[0];	
				}
					$row_records_arr = array();
					$row_records_arr = 	$raw_records_arr;
				//echo "<pre>before sort";print_r($row_records_arr);
				rsort($raw_records_arr);
				//echo "<pre>AFTER sort";print_r($row_records_arr);
				if($this->last_record == 1 )
				{
					//$count = 1;
					//foreach ($raw_records_arr as $filename => $date) {
						//if($count == 1)
						//{
						    $filename 		 = array_search($raw_records_arr[0], $row_records_arr);
							$date			 =  $row_records_arr[$filename];
						    							
							$date_time =  new DateTime("@$date");
							$result = $date_time->format('Y-m-d H:i:s');
							$link =	 "FileNm=".$filename."&FilePath=".$server_file."&FtpServer=".$ftp_server;
							$final_record_arr[$result] = $link;
						//}
						//++$count;
					//}
				}
				else
				{
					foreach ($row_records_arr as $filename => $date) {
							$date_time =  new DateTime("@$date");
							$result = $date_time->format('Y-m-d H:i:s');
							$link =	 "FileNm=".$filename."&FilePath=".$server_file."&FtpServer=".$ftp_server;
							$final_record_arr[$result] = $link;
						}
				}
				//$final_arr = $this->arraySortByTime($raw_records_arr);
			}
	    }

	if($this->doctype == "invoice"){
		$whereCond = "(doc_type='".$this->doctype."' or doc_type='receipt')";
	}else{
		$whereCond = "doc_type='".$this->doctype."'";
	}
	if($this->last_record == 1)
	{
		$recordCondn .= " ORDER BY insert_date DESC LIMIT 1 ";
	}

	$sql_invoice="select * from db_finance.tbl_invoice_proposal_details where parentid ='".$this->parentid."' and module='".$this->module."' and ".$whereCond." ".$recordCondn." ";	
	$res_invoice = parent::execQuery($sql_invoice,$this->conn_fin); 
	 
		if(mysql_num_rows($res_invoice) > 0)
		{
			while($result_inv = mysql_fetch_assoc($res_invoice))
			{				
				$flag_value=2;
				if($this->filetype == 'pdf' ){
					$FileName = $result_inv['pdf_file_name'];	
				}else{
					$FileName = $result_inv['html_file_name'];	
				}	
				
				$file_1 = strchr(str_replace("_","",$result_inv['pdf_file_name']),$result_inv['doc_type']);				
				if($result_inv['doc_type'] == "invoice" || $result_inv['doc_type'] == "receipt"){
				   $file_2 = substr($file_1,7);
				}else{			
				  $file_2 = substr($file_1,8);
				}
				
				$file_3 = explode(".",$file_2);				
				$date = new DateTime("@$file_3[0]");
				$result = $date->format('Y-m-d H:i');
				
				$server_file= $modPath."".$result_inv ['download_path'] . $filetype."/";
				$str_filenm =$FileName;
				
				$link = "FileNm=".$str_filenm."&FilePath=".$server_file."&FtpServer=".$ftp_server."&filetype=".$this->filetype;
				$final_record_arr[$result] = $link;
				
			}
		}
		if(empty($final_record_arr)){
			$final_record_arr['errorCode']		=	1;
			$final_record_arr['errorStatus']	=	"DNF";
		}else{
			$final_record_arr['errorCode']		=	0;
			$final_record_arr['errorStatus']	=	"DF";
		}
		echo json_encode($final_record_arr);
		ftp_close($conn_id);
	}
	// function arraySortByTime($params)
	// {
	// 	if(count($params)>0)
	// 	{

	// 		foreach ($params as $filename => $filename) {
	// 			$file_1 = strchr($result_inv_arch['filename'],'invoice');			
	// 			$file_2 = substr($file_1,7);			
	// 			$file_3 = explode(".",$file_2);				
	// 			$date 	= $file_3[0];

	// 		}
	// 		$file_1 = strchr($result_inv_arch['filename'],'invoice');			
	// 		$file_2 = substr($file_1,7);			
	// 		$file_3 = explode(".",$file_2);				
	// 		$date = new DateTime("@$file_3[0]");
	// 		$result = $date->format('Y-m-d H:i') . "\n";
			
	// 	}
	// }

	function getFtpServer()
	{
		switch($this->city)
		{
		   case 'mumbai':
			 	if($this->module == 'cs' || $this->module == 'tme'){		
					$ftp_server="172.29.0.156";
				}else{
				  	$ftp_server="192.168.7.39";
				}
				break;
			case 'delhi':
				if($this->module == 'cs' || $this->module == 'tme'){				
					$ftp_server="172.29.8.156";
				 }else{
				    $ftp_server="192.168.7.39";
				 }
				break; 
			case 'kolkata': 
				if($this->module == 'cs' || $this->module == 'tme'){
					$ftp_server="172.29.16.156";
				}else{
				    $ftp_server="192.168.7.39";	
				}
				break;
			case 'bangalore':
				if($this->module == 'cs' || $this->module == 'tme'){
					$ftp_server="172.29.26.156";
				}else{
				    $ftp_server="192.168.7.39";
				}
				break;
			case 'chennai': 
			    if($this->module == 'cs' || $this->module == 'tme'){
					$ftp_server="172.29.32.156";
				}else{
				    $ftp_server="192.168.7.39";
				}
				break;
			case 'pune': 
			    if($this->module == 'cs' || $this->module == 'tme'){
					$ftp_server="172.29.40.156";
				}else{
				    $ftp_server="192.168.7.39";
				} 
				break;
			case 'hyderabad':
				if($this->module == 'cs' || $this->module == 'tme'){
					$ftp_server="172.29.50.156";
				}else{
					$ftp_server="192.168.7.39";
				}
				break;
			case 'ahmedabad': 
			    if($this->module == 'cs' || $this->module == 'tme'){
					$ftp_server="172.29.56.156";
				 }else{
					$ftp_server="192.168.7.39";
				}
				break;
			default : 
				$ftp_server="192.168.7.39";
		}
		return $ftp_server;
	}
	function getModPath()
	{
		if($this->module == "cs"){
			$modPath = "217/dataentry_pincodewise";
		}
		if($this->module == "tme"){
			$modPath = "237/tme_live_pincodewise";
		}
		if($this->module == "me"){
			$modPath = "237/me_live_remotecity";
		}
		if($this->module == "jda"){
			$modPath = "59/jda_port_80";
		}
		
		if($this->city == "remote")
		{
			if($this->module == "cs"){
			   $modPath = "227/dataentry_pincodewise";	
			}
			if($this->module == "tme"){
				$modPath = "17237/tme_live_pincodewise";
			}
			if($this->module == "me"){
				$modPath = "237/me_live_remotecity";
			}
		}
		return $modPath;
	}
	private function dieMessage($message)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $message;
		return $die_msg_arr;
	}
}


?>
