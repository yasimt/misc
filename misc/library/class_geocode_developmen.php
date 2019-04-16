<?php
require_once(APP_PATH.'common/dbconnection/config.php');
require_once(APP_PATH.'common/dbconnection/db.class.php');

class Geocode
{
		var $conn_iro, $conn_local, $conn_idc, $conn_tme;
		var $new_flag, $module;
		var $tag_name, $tag_number;
		var $parentid, $lat, $lon, $latitude_primary, $ongitude_primary;

	public function __construct($dbarr, $pid, $module, $new_contract = '0', $POST = '', $IsTMENonpaid = '0')
	{
		$this -> parentid	= $pid;
		$this -> new_flag	= $new_contract;
		$this -> module		= $module;
		$this ->IsTMENonpaid = $IsTMENonpaid;
		if(!(empty($POST)))
		{
			$this -> tag_name 					= $POST['locationtype'];
			$this -> tag_number 				= $POST['geocodetagging'];
			$this -> lat						= $POST['latitude'];
			$this -> lon						= $POST['longitude'];
			$this -> latitude_primary			= $POST['latitude_primary'];
			$this -> longitude_primary			= $POST['longitude_primary'];
			$this -> locationtype_apprvd		= $POST['locationtype_apprvd'];
		}
		$this -> conn_iro  	  = new DB($dbarr['DB_IRO']);		/* connection object to de/cs server */
		$this -> conn_local	  = new DB($dbarr['LOCAL']);		/* connection object to d_jds */
		$this -> conn_tme	  = new DB($dbarr['DB_TME']);		/* connection object to tme_jds */
		if(strtolower($this -> module) == 'me' || strtolower($this -> module) == 'tme')
			$this -> conn_idc	  = new DB($dbarr['IDC']);			/* connection object to online_regis */
	}
	
	function geocode_approval_processing_and_compgeocodes($postarr)
	{
		$update_flag = 1;
		$old_building_name 		= trim($postarr['old_building_name']);
		$current_building_name 	= trim($postarr['plot']);
		$old_city_name			= trim($postarr['old_city_name']);
		$current_city_name 		= trim($postarr['city']);
		$geocode_accuracy_level = trim($postarr['geocode_accuracy_level']);
		$old_latitude 			= trim($postarr['old_latitude']);
		$old_longitude		 	= trim($postarr['old_longitude']);
		if($geocode_accuracy_level == 1)
		{
			if(($old_building_name !='') && (strtolower($old_building_name) == strtolower($current_building_name)) && (strtolower($old_city_name) == strtolower($current_city_name)))
			{	
				$update_flag = 0;
			}
		}
		$this -> insert_tbl_compgeocodes_shadow($postarr['apprvd_tagging'], $postarr['latitude'], $postarr['longitude'], $postarr['geocodetagging'], $update_flag, $old_latitude,$old_longitude);
		
		//update_flag 1 indicates building name and city name  have not been changed. In this case we are not changing latitude, longitude and geocode_accuracy_level.
		
	}
	function insert_tbl_compgeocodes_shadow($apprvd_location, $latitude, $longitude, $geocode_accuracy_level, $update_flag, $old_latitude, $old_longitude)
	{
		$column_to_be_updated = "";
		$mapped = $_SESSION['ucode']." - ".$_SESSION['uname'];
		
		switch(strtolower($apprvd_location))
		{
			case 'building'	:	$latitude_bldg = $latitude;
								$longitude_bldg = $longitude;
								$column_to_be_updated = " latitude_bldg = '".$latitude_bldg."', longitude_bldg = '".$longitude_bldg."', "; 
								break;
								
			case 'landmark'	:	$latitude_landmark = $latitude;
								$longitude_landmark = $longitude;
								$column_to_be_updated = " latitude_bldg = 0, longitude_bldg = 0, latitude_landmark = '".$latitude_landmark."', longitude_landmark	= '".$longitude_landmark."', "; 
								break;
								
			case 'street'	:	$latitude_street = $latitude;
								$longitude_street = $longitude;
								$column_to_be_updated = " latitude_bldg = 0, longitude_bldg = 0, latitude_landmark = 0, longitude_landmark = 0, latitude_street = '".$latitude_street."', longitude_street = '".$longitude_street."', ";
								break;
								
			case 'pincode'	:	$latitude_pincode = $latitude;
								$longitude_pincode = $longitude;
								$column_to_be_updated = " latitude_bldg = 0, longitude_bldg = 0, latitude_landmark = 0, longitude_landmark = 0, latitude_street = 0, longitude_street = 0, latitude_pincode	= '".$latitude_pincode."',	longitude_pincode = '".$longitude_pincode."',";
								break;
								
			case 'area'		:	$latitude_area = $latitude;
								$longitude_area = $longitude;
								$column_to_be_updated = " latitude_bldg = 0, longitude_bldg = 0, latitude_landmark = 0, longitude_landmark = 0, latitude_street = 0, longitude_street = 0, latitude_pincode	= 0, longitude_pincode = 0, latitude_area = '".$latitude_area."', longitude_area = '".$longitude_area."',";
								break;
		}
		if($update_flag == 0)
		{
			$latitude = $old_latitude;
			$longitude = $old_longitude;
		}
			
		$sqlInsrtCompgeoCodes= "INSERT INTO tbl_compgeocodes_shadow SET
								parentid				= '".$this -> parentid."',
								".$column_to_be_updated."
								latitude_final			= '".$latitude."',
								longitude_final			= '".$longitude."',
								geocode_accuracy_level 	= '".$geocode_accuracy_level."',
								logdatetime				= NOW(),
								mappedby				= '".addslashes($mapped)."'
								  
								ON DUPLICATE KEY UPDATE
								
								".$column_to_be_updated."
								latitude_final			= '".$latitude."',
								longitude_final			= '".$longitude."',
								geocode_accuracy_level 	= '".$geocode_accuracy_level."',
								logdatetime				= NOW(),
								mappedby				= '".addslashes($mapped)."'";
		
		if(strtolower($this -> module) == 'cs')
			$resInsrtCompgeoCodes = $this -> conn_local -> query_sql($sqlInsrtCompgeoCodes);
		else if(strtolower($this -> module) == 'tme')
			$resInsrtCompgeoCodes = $this -> conn_tme -> query_sql($sqlInsrtCompgeoCodes);
		else if(strtolower($this -> module) == 'me')
			$resInsrtCompgeoCodes = $this -> conn_idc -> query_sql($sqlInsrtCompgeoCodes);		
			
	}
	function GetCompgeocodesShadow()
	{
		$qry_geocodes_shadow = "SELECT * FROM tbl_compgeocodes_shadow WHERE parentid = '".$this -> parentid."' ";
		if(strtolower($this -> module) == 'cs')
			$res_geocodes_shadow = $this -> conn_local -> query_sql($qry_geocodes_shadow);
		else if(strtolower($this -> module) == 'tme')
			$res_geocodes_shadow = $this -> conn_tme -> query_sql($qry_geocodes_shadow);
		else if(strtolower($this -> module) == 'me')
			$res_geocodes_shadow = $this -> conn_idc -> query_sql($qry_geocodes_shadow);

		$ReturnVal = mysql_fetch_assoc($res_geocodes_shadow);
		return $ReturnVal;
	}

	function InsertCompgeocodes($getGeocodesArr)
	{
		if(strtolower($this -> module) == 'tme')
		{
			global $dbarr;
			$this -> conn_idc	  = new DB($dbarr['IDC']);			/* connection object to online_regis */
		}
		$qry_geocodes_main = "INSERT INTO tbl_compgeocodes 
			SET
			parentid			= '".$getGeocodesArr[parentid]."',
			latitude_landmark	= '".$getGeocodesArr[latitude_landmark]."',
			longitude_landmark	= '".$getGeocodesArr[longitude_landmark]."',
			latitude_area		= '".$getGeocodesArr[latitude_area]."',
			longitude_area		= '".$getGeocodesArr[longitude_area]."',
			latitude_street		= '".$getGeocodesArr[latitude_street]."',
			longitude_street	= '".$getGeocodesArr[longitude_street]."',
			latitude_bldg		= '".$getGeocodesArr[latitude_bldg]."',
			longitude_bldg		= '".$getGeocodesArr[longitude_bldg]."',
			latitude_pincode	= '".$getGeocodesArr[latitude_pincode]."',
			longitude_pincode	= '".$getGeocodesArr[longitude_pincode]."',
			latitude_final		= '".$getGeocodesArr[latitude_final]."',
			longitude_final		= '".$getGeocodesArr[longitude_final]."',
			logdatetime			= '".$getGeocodesArr[logdatetime]."',
			mappedby			= '".addslashes($getGeocodesArr[mappedby])."',
			geocode_accuracy_level = '".$getGeocodesArr[geocode_accuracy_level]."'
			  
			ON DUPLICATE KEY UPDATE
			
			latitude_landmark	= '".$getGeocodesArr[latitude_landmark]."',
			longitude_landmark	= '".$getGeocodesArr[longitude_landmark]."',
			latitude_area		= '".$getGeocodesArr[latitude_area]."',
			longitude_area		= '".$getGeocodesArr[longitude_area]."',
			latitude_street		= '".$getGeocodesArr[latitude_street]."',
			longitude_street	= '".$getGeocodesArr[longitude_street]."',
			latitude_bldg		= '".$getGeocodesArr[latitude_bldg]."',
			longitude_bldg		= '".$getGeocodesArr[longitude_bldg]."',
			latitude_pincode	= '".$getGeocodesArr[latitude_pincode]."',
			longitude_pincode	= '".$getGeocodesArr[longitude_pincode]."',
			latitude_final		= '".$getGeocodesArr[latitude_final]."',
			longitude_final		= '".$getGeocodesArr[longitude_final]."',
			logdatetime			= '".$getGeocodesArr[logdatetime]."',
			mappedby			= '".addslashes($getGeocodesArr[mappedby])."',
			geocode_accuracy_level = '".$getGeocodesArr[geocode_accuracy_level]."'";
			
			if(strtolower($this -> module) == 'cs' || $this -> IsTMENonpaid =='1')
				$res_geocodes_main = $this -> conn_local -> query_sql($qry_geocodes_main);
			else if(strtolower($this -> module) == 'tme')
				$res_geocodes_main = $this -> conn_idc -> query_sql($qry_geocodes_main);
			else if(strtolower($this -> module) == 'me')
				$res_geocodes_main = $this -> conn_idc -> query_sql($qry_geocodes_main);
				
				//echo "<pre>".$qry_geocodes_main;
				if($res_geocodes_main)
					return true;
				else
					return false;
	}
	
	function InsertUnapprovedGeocodes($oldDetails,$newDetails,$old_tagging,$new_tagging,$source) {
		// before insertion we remove all pending data ie approval_flag = 0
		$sqlDelPendingRecord = "DELETE FROM geocode_approval_request_data where parentid = '".$this -> parentid."' AND approval_flag = 0";
		
		$sqlInsrtUnapprovedData = 	"INSERT INTO geocode_approval_request_data SET 
									parentid			=	'".$this -> parentid."',
									companyname			= 	'".addslashes($newDetails['companyname'])."',
									username			=	'".addslashes($_SESSION['uname'])."',
									userid				=	'".$_SESSION['ucode']."',
									approval_flag		=	'0',
									entrydate			=	NOW(),
									state_old			=   '".addslashes($oldDetails['state'])."',
									city_old			=	'".addslashes($oldDetails['city'])."',
									building_old		=	'".addslashes($oldDetails['building_name'])."',
									street_old			=	'".addslashes($oldDetails['street'])."',
									landmark_old		=	'".addslashes($oldDetails['landmark'])."',
									area_old			=	'".addslashes($oldDetails['area'])."',
									pincode_old			=	'".$oldDetails['pincode']."',
									latitude_old		=	'".$oldDetails['latitude']."',
									longitude_old		=	'".$oldDetails['longitude']."',
									tagging_old 		=	'".addslashes($old_tagging)."',
									state_new			=   '".addslashes($newDetails['state'])."',
									city_new			=	'".addslashes($newDetails['city'])."',
									building_new		=	'".addslashes($newDetails['building_name'])."',
									street_new			=	'".addslashes($newDetails['street'])."',
									landmark_new		=	'".addslashes($newDetails['landmark'])."',
									area_new			=	'".addslashes($newDetails['area'])."',
									pincode_new			=	'".$newDetails['pincode']."',
									latitude_new		=	'".$newDetails['latitude']."',
									longitude_new		=	'".$newDetails['longitude']."',
									tagging_new			=	'".addslashes($new_tagging)."',
									source				= 	'".$source."'";
			
		if(strtolower($this -> module) == 'cs' || $this -> IsTMENonpaid == '1')
		{
			$resDelPendingRecord = $this -> conn_iro -> query_sql($sqlDelPendingRecord);
			$resInsrtUnapprovedData = $this -> conn_iro -> query_sql($sqlInsrtUnapprovedData);
		}
		else /* for tme and me */
		{
			$resDelPendingRecord = $this -> conn_idc -> query_sql($sqlDelPendingRecord);
			$resInsrtUnapprovedData = $this -> conn_idc -> query_sql($sqlInsrtUnapprovedData);					
		}

	}
	function set_locationtype($acc_level)
	{
		if(is_numeric($acc_level))
		{
			switch($acc_level)
			{
				case '1' : $level = 'building'; break;
				case '2' : $level = 'landmark'; break;
				case '3' : $level = 'street'; 	break;
				case '4' : $level = 'pincode';	break;
				case '6' : $level = 'area';	 	break;
			}
		}
		else
		{
			switch($acc_level)
			{
				case 'building' : $level = '1'; break;
				case 'landmark' : $level = '2'; break;
				case 'street' 	: $level = '3';	break;
				case 'pincode' 	: $level = '4';	break;
				case 'area' 	: $level = '6';	break;
			}
		}
		return $level;
	}
	
	/* THIS FUNCTION RETURNS GEOCODES ACCORDING TO PINCODE WHICH WILL BE STORED IN GENERALINFO_SHADOW */

	function select_contract_geocodes($genralInfoArr)
	{
		$geo_arr = array();
		$got_area_master = false;
		$qry = "SELECT latitude,longitude,geocode_accuracy_level,area,pincode FROM tbl_companymaster_generalinfo WHERE  parentid= '".$genralInfoArr[parentid]."'";
		$res = $this -> conn_iro -> query_sql($qry);
		if($res)
		{
			$resultarr = mysql_fetch_assoc($res);
		}
		if($resultarr && ($genralInfoArr[area]==$resultarr[area] && $genralInfoArr[pincode]==$resultarr[pincode] )) // contract found in local and same area and pincode
		{
			 // if local table has more accurate then IDC so we will restrict this entry to write into local
			if(intval($resultarr[geocode_accuracy_level]) <= intval($genralInfoArr[geocode_accuracy_level]))
			{
				$geo_arr[latitude]=$resultarr[latitude];
				$geo_arr[longitude]=$resultarr[longitude];
				$geo_arr[geocode_accuracy_level]=$resultarr[geocode_accuracy_level];				
				
				$geocodes_idc_localsql = "INSERT INTO geocodes_idc_local SET
				parentid='".$genralInfoArr[parentid]."',
				latitude_idc ='".$genralInfoArr[latitude]."',
				longitude_idc ='".$genralInfoArr[longitude]."',
				accuracy_level_idc ='".$genralInfoArr[geocode_accuracy_level]."',
				latitude_local ='".$resultarr[latitude]."',
				longitude_local ='".$resultarr[longitude]."',
				accuracy_level_local ='".$resultarr[geocode_accuracy_level]."',
				entry_source= 'approval',
				approval_flag =0 ";
				$res = $this -> conn_iro -> query_sql($geocodes_idc_localsql);
			}
			else // more accurate enrty of IDC data
			{
				$geo_arr[latitude]=$genralInfoArr[latitude];
				$geo_arr[longitude]=$genralInfoArr[longitude];
				$geo_arr[geocode_accuracy_level]=$genralInfoArr[geocode_accuracy_level];
			}
			
		}
		else // fresh enrty  because no entry found in local or area pincode changed
		{
			$geo_arr[latitude]=$genralInfoArr[latitude];
			$geo_arr[longitude]=$genralInfoArr[longitude];
			$geo_arr[geocode_accuracy_level]=$genralInfoArr[geocode_accuracy_level];
		}
		
		
		return $geo_arr;
	}
	function ISNewContract()
	{
		$new_contract_flag = 0;
		$isNewContract = "SELECT parentid FROM tbl_companymaster_generalinfo WHERE parentid = '".$this -> parentid."'";
		$resNewContract = $this -> conn_iro -> query_sql($isNewContract);
		if($resNewContract && mysql_num_rows($resNewContract)<=0)
		{
			$new_contract_flag = 1;
		}
		return $new_contract_flag;
	}
		
	function select_generalinfo_main()
	{
		$returnValue = '';
		$old = " SELECT building_name, landmark, street, area, pincode, city, state,latitude, longitude,geocode_accuracy_level FROM tbl_companymaster_generalinfo WHERE parentid = '".$this -> parentid."' ";
		if(strtolower($this -> module) == 'cs')
			$res_old = $this -> conn_iro -> query_sql($old);
		else if(strtolower($this -> module) == 'tme')
			$res_old = $this -> conn_idc -> query_sql($old);
			
		if(mysql_num_rows($res_old)) {
			$row_old = mysql_fetch_assoc($res_old);
			if(!empty($row_old))
				$returnValue = $row_old;
		}
		return $returnValue;
	}
	
	function update_general_info_accuracy_level($accuracy_level, $table)
	{
		$tableName = $table == 0 ? '_shadow' : '' ;
		$qry = "UPDATE tbl_companymaster_generalinfo".$tableName." SET geocode_accuracy_level = '".$accuracy_level."' WHERE parentid = '".$this -> parentid."' ";
		if(strtolower($this -> module) == 'cs')
			$res = $this -> conn_iro -> query_sql($qry);
		else if(strtolower($this -> module) == 'tme') 
		{
			if($table ==0)
				$res = $this -> conn_tme -> query_sql($qry);
			else
				$res = $this -> conn_idc -> query_sql($qry);
		}
	}
	
	function check_unapproved_record()  //if geocode and tagging to be sent for approval is the same as that approved
	{
		$unapproved_main = $this -> select_level('unapproved','main_table','1');
		$unapproved_temp = $this -> select_level('unapproved','','');
		
		if(!is_null($unapproved_main) && !is_null($unapproved_temp))
		{
			$unapproved_main_array = explode('##', $unapproved_main);
			$unapproved_temp_array = explode('##', $unapproved_temp);
			if($unapproved_temp_array[temp_tagging] == $unapproved_main_array[original_tagging] && $unapproved_temp_array[temp_latitude] == $unapproved_main_array[approved_latitude] && $unapproved_temp_array[temp_longitude] == $unapproved_main_array[approved_longitude])
				return true;
		}
		return false;
	}
	function get_companymaster_shadow_info(){
		$geocodeShadowInfo = array();
		$sqlGeocodeShadowInfo = "SELECT companyname,building_name, landmark, street, area, pincode, city, state, latitude, longitude, geocode_accuracy_level FROM tbl_companymaster_generalinfo_shadow WHERE parentid = '".$this -> parentid."'";
		$resGeocodeShadowInfo = $this -> conn_iro -> query_sql($sqlGeocodeShadowInfo);
		if($resGeocodeShadowInfo && mysql_num_rows($resGeocodeShadowInfo)>0)
		{
			$row_geocode_shadow = mysql_fetch_assoc($resGeocodeShadowInfo);
			$geocodeShadowInfo['companyname'] = $row_geocode_shadow['companyname'];
			$geocodeShadowInfo['building_name'] = $row_geocode_shadow['building_name'];
			$geocodeShadowInfo['landmark'] = $row_geocode_shadow['landmark'];
			$geocodeShadowInfo['street'] = $row_geocode_shadow['street'];
			$geocodeShadowInfo['area'] = $row_geocode_shadow['area'];
			$geocodeShadowInfo['pincode'] = $row_geocode_shadow['pincode'];
			$geocodeShadowInfo['city'] = $row_geocode_shadow['city'];
			$geocodeShadowInfo['state'] = $row_geocode_shadow['state'];
			$geocodeShadowInfo['latitude'] = $row_geocode_shadow['latitude'];
			$geocodeShadowInfo['longitude'] = $row_geocode_shadow['longitude'];
			$geocodeShadowInfo['geocode_accuracy_level'] = $row_geocode_shadow['geocode_accuracy_level'];
		}
		return $geocodeShadowInfo;
	}
}
?>
