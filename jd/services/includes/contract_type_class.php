<?php
class contract_type_class extends DB
{
	var  $conn_iro    	= null;	 
	var  $conn_fin    	= null;	
	var  $conn_national	= null;	
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $data_city		= null;
	
	
	function __construct($params)
	{	
		global $params;
		$data_city 			= trim($params['data_city']); 	
		$rquest 			= trim($params['rquest']); 
 		if(trim($data_city)=='') {
			$message = "Data City is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}			
		if(trim($rquest)=='') {
			$message = "Invalid request name.";
			echo json_encode($this->send_die_message($message));
			die();
		}		 
		$this->parentid  		= $params['parentid'];
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->rquest  	  	= $rquest;
		$this->setServers();		  	
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_fin    		= $db[$conn_city]['fin']['master'];
		$this->conn_national	= $db['db_national'];	 
	}	
	function fetch_contract() {
		$func = $this->rquest;
		if((int)method_exists($this,$func) > 0)
			return $this->$func();
		else {
			$message = "invalid function";
			return json_encode($this->send_die_message($message));			
		}
	}
	
	function get_contract_type()
	{
		global $params;
		$chk_flag		=	0;
		$chk_entry		=	0;
		$chk_n_flag		=	0;
		$chk_entry_n	=	0;
		$chk_flag		=	0;
		$chk_entry_exp	=	0;
		if($params['trace'] == 1)
		{
			echo "<pre>";
			echo "Input Parameters : ";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		if(trim($this->parentid)=='') {
            $message = "parentid blank.";
            echo json_encode($this->send_die_message($message));
            die();
        }
		
		$sql_contract_type="SELECT campaignid,exclusivelisting_tag,expired FROM tbl_companymaster_finance WHERE parentid='".$this->parentid."' and budget>0 and active_campaign=1";		
		$res_contract_type 	= parent::execQuery($sql_contract_type, $this->conn_fin);
		$contract_type=array();
		$contract_type_display	=	Array();
		$numRows = mysql_num_rows($res_contract_type);
		if($res_contract_type && $numRows>0){
			while($row_contract_type = mysql_fetch_assoc($res_contract_type)){
				$CampType = $row_contract_type['campaignid'];
				$ExclType = $row_contract_type['exclusivelisting_tag'];
				$ExpirType = $row_contract_type['expired'];	
			
				if($ExclType == 1){
					$contract_type[] = "Platinum(Exclusive)";
					$contract_type_display[]   = "<font color=green>Platinum(Exclusive)</font>";
				}		
				switch($CampType){
					case 1  : $contract_type[]		   	=	"Supreme";
							  $contract_type_display[] 	=	"<font color=green>Supreme</font>";
							  $paid_status  			=	"1";
							  break;
					case 2  : $contract_type[] 		   	=	"Platinum/Diamond/Gold";
							  $contract_type_display[] 	=	"<font color=green>Platinum/Diamond/Gold</font>";
							  $paid_status  			=	"1";
							  break;
					case 4  : $contract_type[] 		   	=	"Lead(SMS)";
							  $contract_type_display[] 	=	"<font color=green>Lead(SMS)</font>";
							  $paid_status  			=	"1";
							  break;
					case 5  : $contract_type[]		    =	"Competitors Banner";		
							  $contract_type_display[]	=	"<font color=green>Competitors Banner</font>";		
							  $paid_status  			=	"1";
							  break;
					case 12 : $contract_type[]			=	"Tenure";
							  $contract_type_display[]	=	"<font color=green>Tenure</font>";
							  $paid_status  			=	"1";
							  break;					
					case 13 : $contract_type[]			=	"Category Banner";
							  $contract_type_display[]	=	"<font color=green>Category Banner</font>";
							  $paid_status  			=	"1";
							  break;
					case 14 : $contract_type[]			=	"Category Filter Banner";
							  $contract_type_display[]	=	"<font color=green>Category Filter Banner</font>";
							  $paid_status  			=	"1";
							  break;
					case 15 : $contract_type[]			=	"Category Text Banner";
							  $contract_type_display[]	=	"<font color=green>Category Text Banner</font>";
							  $paid_status  			=	"1";
							  break;			
					case 17 : $contract_type[]			=	"Lead(HIDDEN)";
							  $contract_type_display[]	=	"<font color=green>Lead(HIDDEN)</font>";
							  $paid_status  			=	"1";
							  break;
					case 18 : $contract_type[]			=	"Lead(C2C)"; 
							  $contract_type_display[]	=	"<font color=green>Lead(C2C)</font>"; 
							  $paid_status  			=	"1";
							  break;
					case 21 : $contract_type[]			=	"National Listing PDG"; 
							  $contract_type_display[]	=	"<font color=green>National Listing PDG</font>"; 
							  $paid_status  			=	"1";
							  break;		  
					case 22 : $contract_type[]			=	"JDRR";
							  $contract_type_display[]	=	"<font color=green>JDRR</font>";
							  $paid_status  			=	"1";
							  break;
					case 23 : $contract_type[]			=	"Restaurants";
							  $contract_type_display[]	=	"<font color=green>Restaurants</font>";
							  $paid_status  			=	"1";
							  break;		  
					case 29 : $contract_type[]			=	"Shop Front"; 
							  $contract_type_display[]	=	"<font color=green>Shop Front</font>"; 
							  $paid_status  			=	"1";
							  break;
					case 72 : 
					case 73 : 
					case 74 : 
							  $contract_type[]			= 	"Jd Omni"; 
							  $contract_type_display[]	=	"<font color=green>Jd Omni</font>"; 
							  $paid_status  			=	"1";
							  break;		  
				}
			}
		}	
		if($numRows > 0){
			if($this->GetCorporateDealers == 1){
				$contract_type[]			=	"CorporateDealers";
				$contract_type_display[]	=	"<font color=green>CorporateDealers</font>";
				$paid_status  				=	"1";
			}			 
		}
		else{
			$chk_entry	=	1;	
		}	
 		
		$sql_national_type_1="SELECT * FROM db_national_listing.tbl_national_listing WHERE parentid='".$this->parentid."'";
		$res_national_type_1 = parent::execQuery($sql_national_type_1, $this->conn_national);
		if($res_national_type_1 && mysql_num_rows($res_national_type_1)>0)
		{
			$sql_national_type="SELECT campaignid,exclusivelisting_tag,expired,balance,active_campaign FROM tbl_companymaster_finance_national WHERE parentid='".$this->parentid."'";
			$res_national_type = parent::execQuery($sql_national_type, $this->conn_national);
			
			if($res_national_type && mysql_num_rows($res_national_type)>0)
			{
				while($row_national_type = mysql_fetch_assoc($res_national_type))
				{
					$national_type 	   = $row_national_type['campaignid'];
					if($national_type == 10 &&   $row_national_type['balance']>0)
					{
						$contract_type[] 			= 	"National Listing";
						$contract_type_display[] 	= 	"<font color=green>National Listing</font>";
						$paid_status  				=	"1";
						$chk_n_flag				 	=	1;
					}
					else if($national_type == 10 &&   $row_national_type['balance']<=0)
					{
						if($row_national_type['active_campaign'] > 0)
						{	
							$contract_type[] 			= 	"National Listing(Expired)";
							$contract_type_display[] 	= 	"<font color=green>National Listing(Expired)</font>";
							$paid_status  				=	"0";
							$chk_n_flag				 	=	1;
						}
					}
					else if($national_type == 21 &&   $row_national_type['balance']>0)
					{
						$contract_type[] 			= 	"National Listing(PDG)";
						$contract_type_display[] 	= 	"<font color=green>National Listing(PDG)</font>";
						$paid_status  				=	"1";
						$chk_n_flag				 	=	1;
					}
					// Temp removed as front end is not ready ref - Shital Patil / Husain Salik
					/*else if($national_type == 21 &&   $row_national_type['balance']<=0)
					{
						if($row_national_type['active_campaign'] > 0)
						{
							$contract_type[] 			= 	"National Listing(PDG)(Expired)";
							$contract_type_display[] 	= 	"<font color=green>National Listing(PDG)(Expired)</font>";
							$paid_status  				=	"0";
							$chk_n_flag				 	=	1;
						}
					}
					else
					{
						$contract_type[] 			= 	"National Listing(Non Paid)";
						$contract_type_display[] 	= 	"<font color=green>National Listing(Non Paid)</font>";
						$paid_status  				=	"0";
						$chk_n_flag				 	=	1;
						
						$chk_entry_n				=	1;
					}*/
				}
			}
			else
			{
				$chk_entry_n				=	1;
				$contract_type[] 			= 	"National Listing(Non Paid)";
					$contract_type_display[] 	= 	"<font color=green>National Listing(Non Paid)</font>";
					$paid_status  				=	"0";
					$chk_n_flag				 	=	1;
			}
		}
		if($national_type!='10' && $national_type!='21')
		{
			$sql_finc="SELECT parentid,IF(IFNULL(mask,0) = 0 AND IFNULL(freeze,0) = 0 AND MIN(expired)=0,1,2)AS paid_flag,IF(MAX(expired_on) < DATE_SUB(CURDATE(),INTERVAL 3 MONTH),'Expired','Active' ) AS exp,MAX(expired_on) FROM db_finance.tbl_companymaster_finance WHERE parentid = '".$this->parentid."'";
			$res_finc 	= 	parent::execQuery($sql_finc, $this->conn_fin);
			$row_finc	=	mysql_fetch_array($res_finc);
			if(($row_finc['paid_flag'] == 2 && $row_finc['exp'] == "Expired")){
				$contract_type[] 			 =	"Paid Expired (3 months & above)";
				$contract_type_display[] 	 =	"<font color=green>Paid Expired (3 months & above)</font>";
				$paid_status  				 =	"0";
				
				$chk_flag=1;
			}
			else{
				$chk_entry_exp	=	1;
			}	 
			
			$sql_finc1	=	"SELECT parentid,IF(IFNULL(mask,0) = 0 AND IFNULL(freeze,0) = 0 AND MIN(expired)=0,1,2)AS paid_flag,IF(MAX(expired_on) > DATE_SUB(CURDATE(),INTERVAL 3 MONTH),'Expired','Active' ) AS exp,MAX(expired_on) FROM db_finance.tbl_companymaster_finance WHERE parentid = '".$this->parentid."'";
			
			$res_finc1 	= 	parent::execQuery($sql_finc1, $this->conn_fin);			
			$row_finc1	=	mysql_fetch_array($res_finc1);			
			if($row_finc1['paid_flag'] == 2 && $row_finc1['exp'] == "Expired"){
				$contract_type[]			=	"Paid Expired (Less then 3 Month)";				
				$contract_type_display[]	=	"<font color=red> Paid Expired (Less then 3 Month)</font>";		
				$paid_status  				=	"1";			
			}		
		}
		if($chk_entry == 1 && $chk_entry_n == 1)
		{
			$select_approved = "SELECT parentid,approvalStatus FROM payment_instrument_summary WHERE parentid='".$this->parentid."' AND approvalStatus = 0";
			$res_approved 	= 	parent::execQuery($select_approved, $this->conn_fin);
			if($res_approved && mysql_num_rows($res_approved)>0){
				$contract_type[]			=	"Paid(Unapproved)";
				$contract_type_display[]	=	"<font color=green><b> Paid</b>(Unapproved)</font>";
				$paid_status  				=	"1";			
			}
			else
			{
				$contract_type[]		 	=	"Nonpaid";
				$contract_type_display[]	=	"<font color=green>Nonpaid</font>";
				$paid_status  				=	"0";			
			}
		}
		
		if(count($contract_type)==0)
		{
			$contract_type[]		 	=	"Nonpaid";
			$contract_type_display[]	=	"<font color=green>Nonpaid</font>";
			$paid_status  				=	"0";			
		}
		/*
		$sql_national_type="SELECT * FROM db_national_listing.tbl_national_listing WHERE parentid='".$this->parentid."' ";
		$res_national_type = parent::execQuery($sql_national_type, $this->conn_national);
		if($res_national_type && mysql_num_rows($res_national_type)>0)
		{
			$row_national_type = mysql_fetch_assoc($res_national_type);
			$contract_type[] 			= 	"National Listing";
			$contract_type_display[] 	= 	"<font color=green>National Listing</font>";
			$paid_status  				=	$row_national_type['paid'];
			$chk_n_flag				 	=	1;	
			
		}	
		if($chk_flag == 1 && $chk_n_flag == 1){
			$contract_type[] 			=	"National Listing(Expired)";
			$contract_type_display[] 	=	",<font color=green>National Listing(Expired)</font>";
			$paid_status  				=	"0";			
		}*/
		$contract_type 			 =	array_unique($contract_type);
		$contract_type_display 	 =	array_unique($contract_type_display);
		 
		$return_array['contract_type'] 			=	implode(",",$contract_type);
		$return_array['contract_type_display'] 	=	implode(",",$contract_type_display);
		$return_array['paid'] 					=	$paid_status;
		$output_final['result'] 				=   $return_array;
		$output_final['error']['message'] 		=  "success";
		if($params['trace'] == 1)
		{		
			echo "<pre>";print_r($output_final);		
		}	
		return $output_final;			
	}		

	function GetCorporateDealers()
	{
		$sql = "SELECT CorporateDealers FROM tbl_companymaster_extradetails WHERE parentid='".$this->parentid."' And data_city='".$this->data_city."'";
		$res = parent::execQuery($sql, $this->conn_iro);
		$corpdealer = "0";
		if($res && mysql_num_rows($res)>0)
		{
			$row = mysql_fetch_assoc($res);
			$corpdealer = $row['CorporateDealers'];			
		}	
		return $corpdealer;		
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
