<?
function fetch_associated_verticals($parentid,$conn_iro)
{
	$verticals_list_arr = array();
	$sqlFetchAssociatedVerticals = "SELECT DISTINCT vertical_name FROM tbl_vertical_bform_details WHERE (parentid = '".$parentid."' OR parent_pid = '".$parentid."') AND vertical_name!=''";
	$resFetchAssociatedVerticals = $conn_iro->query_sql($sqlFetchAssociatedVerticals);
	if($resFetchAssociatedVerticals && mysql_num_rows($resFetchAssociatedVerticals))
	{
		while($row_vertical_name = mysql_fetch_assoc($resFetchAssociatedVerticals))
		{
			$verticals_list_arr[] = $row_vertical_name['vertical_name'];
		}
	}
	return $verticals_list_arr;
}
function fetchUpdateDetails($parentid,$vertical_name,$conn_iro)
{
	$updtDetailsArr = array();
	$sqlFetchUpdateDetails = "SELECT id,ucode,uname,insertdate FROM tbl_vertical_bform_details WHERE (parentid = '".$parentid."' OR parent_pid = '".$parentid."') AND vertical_name = '".$vertical_name."'";
	$resFetchUpdateDetails = $conn_iro->query_sql($sqlFetchUpdateDetails);
	if($resFetchUpdateDetails && mysql_num_rows($resFetchUpdateDetails))
	{
		while($row_update_details = mysql_fetch_assoc($resFetchUpdateDetails))
		{
			$id  	= trim($row_update_details['id']);
			$ucode  = trim($row_update_details['ucode']);
			$uname  = trim($row_update_details['uname']);
			$updatedby = $uname."(".$ucode.")";
			$insertdate = trim($row_update_details['insertdate']);
			$updtDetailsArr[$id] = $updatedby."|".$insertdate;
		}
	}
	return $updtDetailsArr;
}
function getCompanyDetails($parentid,$compmaster_obj)
{
	$compDetailsArr = array();
	$temparr		= array();
	$fieldstr 		= "companyname";
	$tablename		= "tbl_companymaster_generalinfo";
	$wherecond		= "parentid = '".$parentid."'";
	$compmaster_obj->set_datacity($parentid);
	$temparr		= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
	if($temparr['numrows']>0)
	{
		$row_company_details = $temparr['data']['0'];
		$compDetailsArr['companyname'] = ucwords(strtolower($row_company_details['companyname']));
	}
	return $compDetailsArr;
}
function verticalKeyDetails($conn_iro)
{
	$keyDetailsArr = array();
	$sqlVerticalKeyDetails = "SELECT key_name,key_value FROM tbl_vertical_key_details";
	$resVerticalKeyDetails = $conn_iro->query_sql($sqlVerticalKeyDetails);
	if($resVerticalKeyDetails && mysql_num_rows($resVerticalKeyDetails)>0)
	{
		while($row_key_details = mysql_fetch_assoc($resVerticalKeyDetails))
		{
			$key_name  = trim($row_key_details['key_name']);
			$key_value = trim($row_key_details['key_value']);
			$keyDetailsArr[$key_name] = $key_value;
		}
	}
	return $keyDetailsArr;
}
function hideKey($vertical_name)
{
	$hide_key_arr = array('pickup_area_code');
	if(strtolower($vertical_name) == 'pathology')
	{
		$hide_key_arr = array('qualification','entity_workplace','specialization','department','booking_policy','booking_policy_home','cancel_policy','cancel_policy_home','percentage_booking','changeover_slot','min_time_rsvn','min_time_rsvn_home','min_time_rsvn_cancel','min_time_rsvn_cancel_home','max_booking_limit','loc_parentid','loc_docid','ref_parentid','age_restriction','gender_spec','service_mode','popular_flag','pickup_area_code');
	} 	
	return $hide_key_arr;
}
?>
