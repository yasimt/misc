<?php

class historyclass
{
	function maintain_history($conn_local, $conn_iro, $userid, $pid, $generalinfo_columns_values, $extradetails_columns_values)
	{
		if(count($generalinfo_columns_values))
		{
			$qrystr = '';
			$field_names = '';
			foreach($generalinfo_columns_values as $key => $value)
			{
				$qrystr.= $key ."=". $value ."&";
				$field_names_generalinfo.= $key.","; 
			}
			foreach($extradetails_columns_values as $key => $value)
			{
				$qrystr.= $key ."=". $value ."&";
				$field_names_extradetails.= $key.","; 
			}
			$business_details_new = rtrim($qrystr, '&');
			$field_names_generalinfo = rtrim($field_names_generalinfo, ',');
			$field_names_extradetails = rtrim($field_names_extradetails, ',');
			
			$business_details_old_arr = $this -> select_old_generalinfo($conn_iro, $pid, $field_names_generalinfo);
			
			
			$compname = $business_details_old_arr[0];
			$paid = $business_details_old_arr[1];
			$business_details_old = $business_details_old_arr[2]."&";
			$business_details_old.= $this -> select_old_extradetails($conn_iro, $pid, $field_names_extradetails);
			
			$qry = "INSERT INTO tbl_contract_update_trail SET 
						parentid = '".$pid."',
						compname = '".$compname."',
						updated_by = '".$userid."',
						paidstatus = '".$paid."',
						business_details_old = '".$business_details_old."',
						business_details_new = '".$business_details_new."' ";
			$res = $conn_local -> query_sql($qry);
			return true;
		}
	}
	
	function select_old_generalinfo($conn, $pid, $field_names)
	{
		$qry = "SELECT companyname, paid, ". $field_names ." FROM tbl_companymaster_generalinfo WHERE parentid = '". $pid ."' ";
		$res = $conn -> query_sql($qry);
		$row = mysql_fetch_assoc($res);
		
		foreach($row as $key => $value)
		{
			if($key != 'companyname' && $key != 'paid')
				$bus_old_details.= $key ."=". $value ."&";
		}
		$ret_arr[0] = $row[companyname];
		$ret_arr[1] = $row[paid];
		$ret_arr[2] = rtrim($bus_old_details, '&');
		
		return $ret_arr;
	}

	function select_old_extradetails($conn, $pid, $field_names)
	{
		$qry = "SELECT ". $field_names ." FROM tbl_companymaster_extradetails WHERE parentid = '". $pid ."' ";
		$res = $conn -> query_sql($qry);
		$row = mysql_fetch_assoc($res);
		
		foreach($row as $key => $value)
		{
			$bus_old_details.= $key ."=". $value ."&";
		}
		return rtrim($bus_old_details, '&');
	}
}
?>
