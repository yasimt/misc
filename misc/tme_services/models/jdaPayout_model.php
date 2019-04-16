<?php

class JdaPayout_Model extends Model {
	public function __construct() {
		parent::__construct();
		GLOBAL $parseConf;
		$this->mongo_obj = new MongoClass();
		$this->mongo_city = ($parseConf['servicefinder']['remotecity'] == 1) ? $_SESSION['remote_city'] : $_SESSION['s_deptCity'];
	}
	public function jdaPayoutProcess()
	{
		//$conn_local, $conn_iro, $parentid, $editedby_id, $entered_date
		// tme_jds shadow tables
		$dbObjLocal	=	new DB($this->db['db_local']);
		$dbObjIro	=	new DB($this->db['db_iro']);
		$dbObjJda	=	new DB($this->db['db_jda_website']);
		
		header('Content-Type: application/json');
		$params			=	array_merge($_GET,$_POST);
		$paramsGET		=	json_decode(file_get_contents('php://input'),true);
		$retArr			=	array();
		//$parentid 		=	$params['parentid'];
		$parentid 		=	$paramsGET['parentid'];
		$editedby_id 	=   $paramsGET['editedby_id'];
		$entered_date 	= 	$paramsGET['entered_date'];
		$sql_tme_shadow = "SELECT cmg.companyname,cmg.building_name,cmg.street,cmg.landmark,cmg.pincode,cmg.area,cmg.subarea,cmg.contact_person,cmg.landline,cmg.mobile,cmg.email,cmg.website,cmg.state,cmg.city,cmg.paid,cme.working_time_start,cme.working_time_end,cme.catidlineage,cme.original_date AS 'contract_date',cme.updatedBy,cme.updatedOn,cmg.paid,cme.catidlineage FROM tme_jds.tbl_companymaster_generalinfo_shadow cmg join tme_jds.tbl_companymaster_extradetails_shadow cme on cmg.parentid = cme.parentid WHERE cmg.parentid='".$parentid."' AND cme.updatedOn >='".$entered_date."'  LIMIT 1";
		$res_tme_shadow 	= $dbObjLocal->query($sql_tme_shadow);

		$numPage	=	$dbObjLocal->numRows($res_tme_shadow);
		// if details are found in tme jds table then only do the payout.
		if($numPage > 0 )
		{
			// make connection to jda 7.35 to fetch one previous state of jda entered.
			//global $dbarr;
			//$conn_jda 		= new DB($dbarr['JDA_WEBSITE']);
			
			
			// get the field pricing
			$sql 	= 	"SELECT * FROM db_audit.tbl_field_pricing WHERE module_type='jda'";
			$res	= 	$dbObjLocal->query($sql);
			$row	=	$dbObjLocal->fetchData($res);
			
			$arr_field_pricing	= array();
			$date1 = new DateTime($entered_date);
			$fromD =  $date1->format('Y-m-d');
			
			if($fromD >= $row['effective_date'])
			{
				foreach($row as $key => $val)
				{
					if($key != 'effective_date')
					{
						$str = $key;
						$str1 = str_replace('_reward','',$key);
						$str2 = str_replace('_penalty','',$str1);

						$key1	= $str2. '_reward';
						$key2	= $str2. '_penalty';
						
						if(isset($row[$key1]) && count($row[$key1]) > 0) {
							$arr_field_pricing[$str2]['reward'] = $row[$key1];
						}
						
						if(isset($row[$key2]) && count($row[$key2]) > 0) {
							$arr_field_pricing[$str2]['penalty'] = $row[$key2];
						}
					}
				}
			}
			else
			{
				// 0-fieldname, 1-reward, 2-max cap, 3-penalty
				// this should be used uptil 31-Oct 2012 only
				$arr_field_pricing["address"]['reward'] 	= 1;
				$arr_field_pricing["address"]['penalty'] 	= 5;
				$arr_field_pricing["landmark"]['reward'] 	= 1;
				$arr_field_pricing["landmark"]['penalty'] 	= 0;
				$arr_field_pricing["contact_person"]['reward'] 	= 1;
				$arr_field_pricing["contact_person"]['penalty'] = 5;
				$arr_field_pricing["landline"]['reward'] 	= 1;
				$arr_field_pricing["landline"]['penalty'] 	= 5;
				$arr_field_pricing["landline"]['max_cap'] 	= 0;
				$arr_field_pricing["mobile"]['reward'] 		= 1;
				$arr_field_pricing["mobile"]['penalty'] 	= 5;
				$arr_field_pricing["mobile"]['max_cap'] 	= 0;
				$arr_field_pricing["website"]['reward'] 	= 2;
				$arr_field_pricing["website"]['penalty'] 	= 5;
				$arr_field_pricing["email"]['reward'] 		= 2;
				$arr_field_pricing["email"]['penalty'] 		= 5;
				$arr_field_pricing["working_time"]['reward'] 	= 1;
				$arr_field_pricing["working_time"]['penalty']	= 0;
				$arr_field_pricing["category"]['reward'] 		= 1;
				$arr_field_pricing["category"]['penalty']		= 0;
			}
			
			/******************* CHECK if its an contract which is already in our system and edited by JDA START *****************/
			// it is NOT an edited contract by default
			
			$sql 	= "SELECT edited_contract_payout FROM db_audit.tbl_configurations WHERE module_type='jda'";
			$res 	= $dbObjLocal->query($sql);
			$row	=	$dbObjLocal->fetchData($res);
			$row_edited	= array();
			//echo "<br> ====== edited_contract_payout == " . $row['edited_contract_payout'] . " ====== <br>" ; 
			if($row['edited_contract_payout'] == "on")
			{
				$EDITED_LIVE = '0';
				$sql_edited  = "SELECT cmg.companyname, cmg.building_name, cmg.street, cmg.landmark, cmg.pincode, cmg.area, cmg.subarea, cmg.contact_person, cmg.landline, cmg.mobile, cmg.email, cmg.website, cmg.state, cmg.city, cmg.paid, cme.working_time_start, cme.working_time_end, cme.catidlineage, cme.original_date AS 'contract_date', cme.updatedBy, cme.updatedOn, cmg.paid, cme.catidlineage, cmg.sphinx_id, cmg.parentid FROM tbl_companymaster_generalinfo_live_shadow cmg JOIN tbl_companymaster_extradetails_live_shadow cme on cmg.parentid = cme.parentid WHERE cmg.parentid='".$parentid."' LIMIT 1";
				$res_edited	 	= 	$dbObjJda->query($sql_edited);	
				$numPageJda		=	$dbObjJda->numRows($res_edited);			
				if($numPageJda > 0 )
				{
					$EDITED_LIVE	= '1'; // yes, it is edited contract
					$row_edited		= $dbObjJda->fetchData($res_edited);					
					//echo "<br> ====== EDITED_LIVE === " . $EDITED_LIVE . " ====== <br>" ; 
				}
			}
			/******************* CHECK if its an contract which is already in our system and edited by JDA END *******************/
			//print_r($arr_field_pricing);
			
			// main tables
			$sql_companymaster_info = "SELECT cmg.companyname, cmg.building_name, cmg.street, cmg.landmark, cmg.pincode, cmg.area, cmg.subarea, cmg.contact_person, cmg.landline, cmg.mobile, cmg.email, cmg.website, cmg.state, cmg.city, cmg.paid, cme.working_time_start, cme.working_time_end, cme.catidlineage, cme.original_date AS 'contract_date', cme.updatedBy, cme.updatedOn, cmg.paid, cme.catidlineage, cmg.sphinx_id, cmg.parentid FROM db_iro.tbl_companymaster_generalinfo cmg JOIN db_iro.tbl_companymaster_extradetails cme on cmg.parentid = cme.parentid WHERE cmg.parentid='".$parentid."' LIMIT 1";
			$result_companymaster_info 	= $dbObjIro->query($sql_companymaster_info);
			$arr_jda_details 	= $dbObjIro->fetchData($result_companymaster_info);

			$arr_field_pricingCnt 	= count($arr_field_pricing);
			$building_name_status	= '';
			$area_status 			= '';
			$pincode_status 		= '';
			$street_status 			= '';

			if(!empty($arr_jda_details['paid'])){
				$paid = $arr_jda_details['paid'];
			}else{
				$paid = 0;
			}

			// fetching the tme shadow details
			$arr_tme_details 			= $dbObjLocal->fetchData($res_tme_shadow);
			$arr_payout_log_details 	= array();
			$arr_valid_field_names 		= array();
			$arr_valid_field_values 	= array();
			$arr_invalid_field_names 	= array();
			$arr_invalid_field_values 	= array();
			
			foreach($arr_field_pricing as $key =>$arr_values)
			{
				$field_name 	= $key;
				
				/************************** address validation starts here ***************************/
				if($field_name == 'address')
				{
					$row_edited_field_building = '';
					if(isset($row_edited['building_name'])){
						$row_edited_field_building = $row_edited['building_name'];
					}
					$row_edited_field_area = '';
					if(isset($row_edited['area'])){
						$row_edited_field_area = $row_edited['area'];
					}
					$row_edited_field_pincode = '';
					if(isset($row_edited['pincode'])){
						$row_edited_field_pincode = $row_edited['pincode'];
					}
					$row_edited_field_street = '';
					if(isset($row_edited['street'])){
						$row_edited_field_street= $row_edited['street'];
					}
					$building_name_status = $this->compare_single_value_fields($field_name, $arr_jda_details['building_name'], $arr_tme_details['building_name'],$row_edited_field_building ,$arr_field_pricing);
					
					$area_status 	= $this->compare_single_value_fields($field_name, $arr_jda_details['area'], $arr_tme_details['area'], $row_edited_field_area,$arr_field_pricing);
					
					$pincode_status = $this->compare_single_value_fields($field_name, $arr_jda_details['pincode'], $arr_tme_details['pincode'], $row_edited_field_pincode,$arr_field_pricing);
					
					$street_status 	= $this->compare_single_value_fields($field_name,$arr_jda_details['street'], $arr_tme_details['street'], $row_edited_field_street,$arr_field_pricing);
					
					
					if((!empty($building_name_status) && $building_name_status == 'Valid') || (!empty($area_status) && $area_status == 'Valid') || (!empty($pincode_status) && $pincode_status == 'Valid') || ( !empty($street_status) && $street_status == 'Valid'))
					{
						$arr_valid_field_names[] 	= $field_name;
						$arr_valid_field_values[] 	= $arr_field_pricing[$field_name]['reward'];
					}
					elseif($building_name_status == 'Invalid' && $area_status == 'Invalid' && $pincode_status == 'Invalid' && $street_status == 'Invalid')
					{
						$arr_invalid_field_names[] 	= $field_name;
						$arr_invalid_field_values[] = $arr_field_pricing[$field_name]['penalty'];
					}
				}
				else if($field_name=='contact_person')
				{
					$row_edited_field_contact_person = '';
					if(isset($row_edited['contact_person'])){
						$row_edited_field_contact_person= $row_edited['contact_person'];
					}
					$arr_return = $this->compare_comma_separated_fields($field_name, $arr_jda_details['contact_person'], $arr_tme_details['contact_person'], $row_edited_field_contact_person,$arr_field_pricing);
					if(is_array($arr_return) && is_array($arr_return[0]))
					{
						foreach($arr_return[0] as $k => $val)
						{
							array_push($arr_valid_field_names,$arr_return[0][$k]);
						}
					}
					if(is_array($arr_return) && is_array($arr_return[1]))
					{
						foreach($arr_return[1] as $k => $val)
						{
							array_push($arr_valid_field_values,$arr_return[1][$k]);
						}
					}
					if(is_array($arr_return) && is_array($arr_return[2]))
					{
						foreach($arr_return[2] as $k => $val)
						{
							array_push($arr_invalid_field_names,$arr_return[2][$k]);
						}
					}
					if(is_array($arr_return) && is_array($arr_return[3]))
					{
						foreach($arr_return[3] as $k => $val)
						{
							array_push($arr_invalid_field_values,$arr_return[3][$k]);
						}
					}
				}
				else if($field_name=='landmark' || $field_name=='website')
				{
					$row_edited_field = '';
					if(isset($row_edited[$field_name])){
						$row_edited_field = $row_edited[$field_name];
					}
					$arr_return = $this->compare_single_value_fields($field_name,$arr_jda_details[$field_name], $arr_tme_details[$field_name],$row_edited_field ,$arr_field_pricing);
					$arr_return = $this->compare_single_value_fields($field_name,$arr_jda_details[$field_name], $arr_tme_details[$field_name],$row_edited_field ,$arr_field_pricing);
					if(is_array($arr_return) && is_array($arr_return[0]) && array_key_exists('0',$arr_return[0]))
					{
						array_push($arr_valid_field_names,$arr_return[0][0]);
					}
					if(is_array($arr_return) && is_array($arr_return[1]) && array_key_exists('0',$arr_return[0]))
					{
						array_push($arr_valid_field_values,$arr_return[1][0]);
					}
					if(is_array($arr_return) && is_array($arr_return[2]) && array_key_exists('0',$arr_return[0]))
					{
						array_push($arr_invalid_field_names,$arr_return[2][0]);
					}
					if(is_array($arr_return) && is_array($arr_return[3]) && array_key_exists('0',$arr_return[0]))
					{
						array_push($arr_invalid_field_values,$arr_return[3][0]);
					}
					
				}
				else if($field_name=='email')
				{
					$row_edited_field_email = '';
					if(isset($row_edited['email'])){
						$row_edited_field_email= $row_edited['email'];
					}
					$arr_return = $this->compare_comma_separated_fields($field_name, $arr_jda_details['email'], $arr_tme_details['email'], $row_edited_field_email,$arr_field_pricing);
					if(is_array($arr_return) && is_array($arr_return[0]))
					{
						foreach($arr_return[0] as $k => $val)
						{
							array_push($arr_valid_field_names,$arr_return[0][$k]);
						}
					}
					if(is_array($arr_return) && is_array($arr_return[1]))
					{
						foreach($arr_return[1] as $k => $val)
						{
							array_push($arr_valid_field_values,$arr_return[1][$k]);
						}
					}
					if(is_array($arr_return) && is_array($arr_return[2]))
					{
						foreach($arr_return[2] as $k => $val)
						{
							array_push($arr_invalid_field_names,$arr_return[2][$k]);
						}
					}
					if(is_array($arr_return) && is_array($arr_return[3]))
					{
						foreach($arr_return[3] as $k => $val)
						{
							array_push($arr_invalid_field_values,$arr_return[3][$k]);
						}
					}
				}
				else if( ($field_name=='landline') || ($field_name=='mobile') )
				{
					$arr_valid_contact_nos = $arr_invalid_contact_nos = array();
					$landJdaDet	=	'';
					$landTmeDet	=	'';
					$landRowTmeDet	=	'';
					if(isset($arr_jda_details[$field_name])) {
						$landJdaDet	=	$arr_jda_details[$field_name];
					}
					
					if(isset($arr_tme_details[$field_name])) {
						$landTmeDet	=	$arr_tme_details[$field_name];
					}
					
					if(isset($row_edited[$field_name])) {
						$landTmeDet	=	$row_edited[$field_name];
					}
					$arr_return = $this->compare_comma_separated_fields($field_name, $landJdaDet, $landTmeDet, $landTmeDet,$arr_field_pricing);
					
					if(is_array($arr_return) && is_array($arr_return[0]))
					{
						foreach($arr_return[0] as $k => $v)
						{
							array_push($arr_valid_field_names,$arr_return[0][$k]);
						}
					}
					
					if(is_array($arr_return) && is_array($arr_return[2]))
					{
						foreach($arr_return[2] as $k => $v)
						{
							array_push($arr_invalid_field_names,$arr_return[2][$k]);
						}
					}
					
					if(is_array($arr_return) && is_array($arr_return[4]))
					{
						foreach($arr_return[4] as $k => $v)
						{
							array_push($arr_invalid_contact_nos,$arr_return[4][$k]);
						}
					}
					if(is_array($arr_return) && is_array($arr_return[5]))
					{
						foreach($arr_return[5] as $k => $v)
						{
							array_push($arr_valid_contact_nos,$arr_return[5][$k]);
						}
					}
					
					if($field_name=='landline') {
						$maxcap = '';
						if(isset($arr_field_pricing[$field_name]['max_cap'])){
							$maxcap = $arr_field_pricing[$field_name]['max_cap'];
						}
						$arr_landline_payout 	= $this->variableFieldPayCalculation($field_name,$maxcap,$arr_field_pricing[$field_name]['penalty'],$arr_jda_details[$field_name],$arr_valid_contact_nos,$arr_invalid_contact_nos, $arr_field_pricing[$field_name]['reward']);

						$str_valid_landline 	= implode(",",$arr_valid_contact_nos);
						$str_invalid_landline 	= implode(",",$arr_invalid_contact_nos);
					} else if($field_name=='mobile') {
						$maxcap = '';
						if(isset($arr_field_pricing[$field_name]['max_cap'])){
							$maxcap = $arr_field_pricing[$field_name]['max_cap'];
						}
						$arr_mobile_payout 	= $this->variableFieldPayCalculation($field_name,$maxcap,$arr_field_pricing[$field_name]['penalty'],$arr_jda_details[$field_name],$arr_valid_contact_nos,$arr_invalid_contact_nos,$arr_field_pricing[$field_name]['reward']);

						$str_valid_mobile 	= implode(",",$arr_valid_contact_nos);
						$str_invalid_mobile = implode(",",$arr_invalid_contact_nos);
					}
				} else if($field_name == 'working_time') {
					$jda_wstart		= str_replace(".",":",trim($arr_jda_details['working_time_start'],','));
					$jda_wend 		= str_replace(".",":",trim($arr_jda_details['working_time_end'],','));
					
					$tme_wstart		= str_replace(".",":",trim($arr_tme_details['working_time_start'],','));
					$tme_wend 		= str_replace(".",":",trim($arr_tme_details['working_time_end'],','));
					
					$arr_wrong_entries = array("00:00","23:59","--:--");
					
					//Working time format 1: Closed-00:00,00:00-00:00,00:00-00:00,00:00-00:00,00:00-00:00,00:00-00:00,00:00-00:00	
					//Working time format 2: 07.00,07.00,07.00,07.00,07.00,07.00,07.00
					$jda_start['start1'] = array();
					$jda_start['start2'] = array();
					if(preg_match("/-/",$jda_wstart))
					{
						$arr_jda_start_comma	= explode(",",$jda_wstart);
						foreach($arr_jda_start_comma as $a => $b)
						{
							$arr_jda_start	= explode("-",$b);
							$jda_start['start1'][]	= $arr_jda_start[0];
							if(!in_array($arr_jda_start[1],$arr_wrong_entries))
							{
								$jda_start['start2'][]	= $arr_jda_start[1];
							}
						}
						$im_jda_start1	=	implode(",",$jda_start['start1']);
						$im_jda_start2	=	implode(",",array_values(array_filter($jda_start['start2'])));
					}
					else
					{
						$im_jda_start1 	= $jda_wstart;
						$im_jda_start2	= "";
					}
					
					$jda_end['end1'] = array();
					$jda_end['end2'] = array();
					if(preg_match("/-/",$jda_wend))
					{
						$arr_jda_end_comma	= explode(",",$jda_wend);
						foreach($arr_jda_end_comma as $a => $b)
						{
							$arr_jda_end	= explode("-",$b);
							$jda_end['end1'][]	= $arr_jda_end[0];
							if(!in_array($arr_jda_end[1],$arr_wrong_entries))
							{
								$jda_end['end2'][]	= $arr_jda_end[1];
							}
						}
						$im_jda_end1	=	implode(",",$jda_end['end1']);
						$im_jda_end2	=	implode(",",array_values(array_filter($jda_end['end2'])));
					}
					else
					{
						$im_jda_end1 	= $jda_wend;
						$im_jda_end2	= "";
					}
					
					$tme_start['start1'] = array();
					$tme_start['start2'] = array();
					if(preg_match("/-/",$tme_wstart))
					{
						$arr_tme_start_comma	= explode(",",$tme_wstart);
						foreach($arr_tme_start_comma as $a => $b)
						{
							$arr_tme_start	= explode("-",$b);
							$tme_start['start1'][]	= $arr_tme_start[0];
							if(!in_array($arr_tme_start[1],$arr_wrong_entries))
							{
								$tme_start['start2'][]	= $arr_tme_start[1];
							}
						}
						$im_tme_start1	= implode(",",$tme_start['start1']);
						$im_tme_start2	= implode(",",array_values(array_filter($tme_start['start2'])));
					}
					else
					{
						$im_tme_start1 	= $tme_wstart;
						$im_tme_start2	= "";
					}
					
					$tme_end['end1'] = array();
					$tme_end['end2'] = array();
					if(preg_match("/-/",$tme_wend))
					{
						$arr_tme_end_comma	= explode(",",$tme_wend);
						foreach($arr_tme_end_comma as $a => $b)
						{
							$arr_tme_end	= explode("-",$b);
							$tme_end['end1'][]	= $arr_tme_end[0];
							if(!in_array($arr_tme_end[1],$arr_wrong_entries))
							{
								$tme_end['end2'][]	= $arr_tme_end[1];
							}
						}
						$im_tme_end1	= implode(",",$tme_end['end1']);
						$im_tme_end2	= implode(",",array_values(array_filter($tme_end['end2'])));
					}
					else
					{
						$im_tme_end1 	= $tme_wend;
						$im_tme_end2	= "";
					}
					
					$patt_s = "/00:00,00:00,00:00,00:00/";
					$patt_e = "/23:59,23:59,23:59,23:59/";

					$show_jda_wt = false;
					$show_tme_wt = false;
					if(preg_match($patt_s,$im_jda_start1) && preg_match($patt_e,$im_jda_end1) )
					{
						$show_jda_wt = true;
					}
					elseif(count($jda_start['start1']) > 5 && count($jda_end['end1']) > 5 &&  !preg_match($patt_s,$im_jda_start1) && !preg_match($patt_e,$im_jda_end1))
					{
						$show_jda_wt = true;
					}

					if(preg_match($patt_s,$im_tme_start1) && preg_match($patt_e,$im_tme_end1) )
					{
						$show_tme_wt = true;
					}
					elseif(count($tme_start['start1']) > 5 && count($tme_end['end1']) > 5 &&  !preg_match($patt_s,$im_tme_start1) && !preg_match($patt_s,$im_tme_end1) )
					{
						$show_tme_wt = true;
					}

					if($show_tme_wt === true && $show_jda_wt === true)
					{
						if(!empty($im_jda_start1) && !empty($im_tme_start1) && ($im_jda_start1 != $im_tme_start1 || $im_jda_end1 != $im_tme_end1))
						{
							$arr_invalid_field_names[] 	= $field_name;
							$arr_invalid_field_values[] = $arr_field_pricing[$field_name]['penalty'];
						}
						else
						{
							$arr_valid_field_names[] 	= $field_name;
							$arr_valid_field_values[] 	= $arr_field_pricing[$field_name]['reward'];
						}
					}
					else
					{
						$arr_valid_field_names[] 	= $field_name;
						$arr_valid_field_values[] 	= $arr_field_pricing[$field_name]['reward'];
					}
				}
				else if($field_name == 'category')
				{
					if(MONGOUSER==1)
					{
						$mongo_inputs = array();
						$mongo_inputs['parentid'] 	= $parentid;
						$mongo_inputs['data_city'] 	= SERVER_CITY;
						$mongo_inputs['module']		= 'tme';
						$mongo_inputs['table'] 		= "tbl_business_temp_data";
						$mongo_inputs['fields'] 	= "catIds";
						$row_temp_cat = $this->mongo_obj->getData($mongo_inputs);
					}
					else
					{
						$sql_temp_cat	="SELECT catIds FROM tme_jds.tbl_business_temp_data WHERE contractid='".$parentid."'";
						$res_temp_cat 	= $dbObjLocal->query($sql_temp_cat);
						$row_temp_cat 	= $dbObjLocal->fetchData($res_temp_cat);
					}
					$jda_cats 		= array_values(array_filter(explode(",",str_replace("/","",$arr_jda_details['catidlineage']))));
					$tme_cats 		= array_values(explode("|P|",$row_temp_cat['catIds']));
					$jda_categories 			= array_intersect($jda_cats, $tme_cats);
					$jda_invalid_categories 	= array_diff($jda_cats, $tme_cats);
					
					$total_cat_pay 		= 0;
					$total_cat_pay_valid 		= 0;
					$total_cat_pay_invalid 		= 0;
					$str_jda_valid_catids 	= "";
					$str_jda_invalid_catids	= "";

					if(!empty($jda_categories) && is_array($jda_categories)){
						$jda_categories_count 	= count($jda_categories);
						$total_cat_pay_valid 	= $jda_categories_count * $arr_field_pricing["category"]['reward'];
						$str_jda_valid_catids 	= implode("|~|",$jda_categories);
					}


					if(!empty($jda_invalid_categories) && is_array($jda_invalid_categories)){
						$jda_categories_count_invalid 	= count($jda_invalid_categories);
						$total_cat_pay_invalid 			= $jda_categories_count_invalid * $arr_field_pricing["category"]['penalty'];
						$str_jda_invalid_catids 		= implode("|~|",$jda_invalid_categories);
					}

					$total_cat_pay										= $total_cat_pay_valid - $total_cat_pay_invalid;
					$arr_payout_log_details['total_valid_catvalues'] 	= $total_cat_pay_valid;
					$arr_payout_log_details['total_cat_pay'] 			= $total_cat_pay;
					$arr_payout_log_details['valid_catnames'] 			= $str_jda_valid_catids;
					$arr_payout_log_details['invalid_catnames'] 		= $str_jda_invalid_catids;
				}
			}
			
			if(count($arr_invalid_field_names) > 0)
			{
				$correct_flg_main = '0';
			}
			else
			{
				$correct_flg_main = '1';
			}

			if(count($jda_invalid_categories) > 0 )
			{
				$correct_flg_cat = '0';
			}
			else
			{
				$correct_flg_cat = '1';
			}

			if($correct_flg_cat == '1' && $correct_flg_main == '1' )
			{
				$correct_flg = '1';
			}
			else
			{
				$correct_flg = '0';
			}

			if(is_array($arr_valid_field_names) && count($arr_valid_field_names))
			{
				$arr_valid_field_names1	= array_values(array_filter($arr_valid_field_names));
				$arr_payout_log_details['valid_field_names'] = implode(",",$arr_valid_field_names1);
			}
			else
				$arr_payout_log_details['valid_field_names'] = "";


			if(is_array($arr_invalid_field_names) && count($arr_invalid_field_names))
			{
				$arr_invalid_field_names1	= array_values(array_filter($arr_invalid_field_names));
				$arr_payout_log_details['invalid_field_names'] = implode(",",$arr_invalid_field_names1);
			}
			else
				$arr_payout_log_details['invalid_field_names'] = "";


			$total_valid_field_value = $total_invalid_field_value = $total_payout = 0;
			if(!empty($arr_valid_field_values) && is_array($arr_valid_field_values) && count($arr_valid_field_values) > 0)
			{
				$arr_valid_field_values1	= array_values(array_filter($arr_valid_field_values,array($this, 'check_values')));
				$arr_payout_log_details['valid_field_values'] = implode(",",$arr_valid_field_values1);
				$total_valid_field_value = array_sum($arr_valid_field_values);
			}
			else
			{
				$arr_payout_log_details['valid_field_values'] = "";
				$total_valid_field_value = "0.00";
			}

			if(!empty($arr_invalid_field_values) && is_array($arr_invalid_field_values) && count($arr_invalid_field_values) > 0)
			{
				$arr_invalid_field_values1	= array_values(array_filter($arr_invalid_field_values,array($this, 'check_values')));
				$arr_payout_log_details['invalid_field_values'] = implode(",",$arr_invalid_field_values1);
				$total_invalid_field_value = array_sum($arr_invalid_field_values);
			}
			else
			{
				$arr_payout_log_details['invalid_field_values'] = "";
				$total_invalid_field_value = "0.00";
			}

			$date1 = new DateTime($entered_date);
			$date2 =  $date1->format('Y-m-d');

			$editedby_id = (!empty($editedby_id)) ? $editedby_id : $arr_jda_details['updatedBy'];
			$sql1 	= 'SELECT empName FROM d_jds.mktgEmpMaster WHERE mktEmpCode = "'.$editedby_id.'" LIMIT 1';
			$res1 	= $dbObjLocal->query($sql1);
			$row1	= $dbObjLocal->fetchData($res1);

			$arr_payout_log_details['parentid'] 		= $parentid;
			$arr_payout_log_details['editedby_id'] 		= $editedby_id;			// ideally jda id
			$arr_payout_log_details['editedby_name'] 	= $row1['empName']; 	// ideally jda name
			
			// tme id
			$arr_payout_log_details['auditedby_id'] 	= $_SESSION['ucode'];
			
			// tme name
			$arr_payout_log_details['auditedby_name'] 	= $_SESSION['uname'];
			
			$arr_payout_log_details['batch_date_start'] = $date2. " 00:00:00";
			$arr_payout_log_details['batch_date_end'] 	= $date2. " 23:59:59";
			
			$arr_payout_log_details['total_valid_field_values'] 	= $total_valid_field_value;
			$arr_payout_log_details['total_invalid_field_values'] 	= $total_invalid_field_value;
			
			$total_payout	= $arr_payout_log_details['total_valid_field_values'] - $arr_payout_log_details['total_invalid_field_values'];
			$arr_payout_log_details['total_payout'] 	= $total_payout;

			$arr_payout_log_details['total_valid_lanvalues'] 	= ltrim(rtrim($arr_landline_payout['total_valid_fieldvalues'],","),",");
			$arr_payout_log_details['total_invalid_lanvalues'] 	= ltrim(rtrim($arr_landline_payout['total_invalid_fieldvalues'],","),",");
			
			$arr_payout_log_details['total_landline_pay'] 		= $arr_landline_payout['total_field_pay'];

			$arr_payout_log_details['valid_landline'] 			= ltrim(rtrim($str_valid_landline,","),",");
			$arr_payout_log_details['invalid_landline'] 		= ltrim(rtrim($str_invalid_landline,","),",");

			$arr_payout_log_details['total_valid_mobvalues'] 	= $arr_mobile_payout['total_valid_fieldvalues'];
			$arr_payout_log_details['total_invalid_mobvalues']	= $arr_mobile_payout['total_invalid_fieldvalues'];
			$arr_payout_log_details['total_mobile_pay'] 		= $arr_mobile_payout['total_field_pay'];

			$arr_payout_log_details['valid_mobile'] 			= ltrim(rtrim($str_valid_mobile,","),",");
			$arr_payout_log_details['invalid_mobnames'] 		= ltrim(rtrim($str_invalid_mobile,","),",");

			$arr_payout_log_details['city'] 					= $arr_tme_details['city'];
			$arr_payout_log_details['entered_date'] 			= $entered_date;
			$arr_payout_log_details['audited_date'] 			= date('Y-m-d H:i:s');
			$arr_payout_log_details['is_accepted'] 				= '1';
			$arr_payout_log_details['is_audited'] 				= '1';
			$arr_payout_log_details['to_be_paid'] 				= '1';
			$arr_payout_log_details['module_type'] 				= 'jda';
			$arr_payout_log_details['contract_date'] 			= $arr_jda_details['contract_date'];
			$arr_payout_log_details['edited_contract']			= $EDITED_LIVE;
			
			$this->maintain_log(http_build_query($arr_payout_log_details));

			$payoutResult = $this->dataCorrectionPayoutLog($arr_payout_log_details,$paid);
			$payoutResult = $this->tmeDcPayoutLog($arr_jda_details,$arr_tme_details,$arr_payout_log_details,$paid,$correct_flg, $parentid, $row_edited);
		}
	}
	public function tmeDcPayoutLog($arr_jda_details,$arr_tme_details,$arr_payout_log_details,$paid,$correct, $parentid, $row_edited)
	{
		$dbObjLocal	=	new DB($this->db['db_local']);
		global $EDITED_LIVE;
		$str_row_edited = "";
		
		if(!empty($arr_payout_log_details) && is_array($arr_payout_log_details) && count($arr_payout_log_details) > 0)
		{
			if($EDITED_LIVE == "1")
			{
				//$str_row_edited = json_encode($row_edited);
				//$str_row_edited = str_replace('"','\'',$str_row_edited);
				$str_row_edited = addslashes(http_build_query($row_edited,'','|~@~|'));
				
			}
			
			//$str_jda_details = json_encode($arr_jda_details);
			//$str_jda_details = str_replace('"','\'',$str_jda_details);
			
			$str_jda_details = addslashes(http_build_query($arr_jda_details,'','|~@~|'));

			//$str_tme_details 		= json_encode($arr_tme_details);
			//$str_auditor_details 	= str_replace('"','\'',$str_tme_details);
			
			$str_auditor_details = addslashes(http_build_query($arr_tme_details,'','|~@~|'));

			$nowdate = date('Y-m-d H:i:s');
			$sql = 'REPLACE INTO tbl_tme_dc_logs(sphinx_id, parentid, companymaster_details, tme_details, auditor_details, entered_date, tme_id, auditor_id, done_date, audit_status, valid_fields, invalid_fields, module_type, valid_catnames, invalid_catnames, correct, allocated, paid, edited_contract) VALUES ("'.$arr_jda_details['sphinx_id'].'","'.$parentid.'","'.$str_row_edited.'","'.$str_jda_details.'","'.$str_auditor_details.'","'.$arr_payout_log_details['entered_date'].'","'.$arr_payout_log_details['editedby_id'].'","'.$arr_payout_log_details['auditedby_id'].'","'.$arr_payout_log_details['audited_date'].'","1","'.$arr_payout_log_details['valid_field_names'].'","'.$arr_payout_log_details['invalid_field_names'].'","jda","'.$arr_payout_log_details['valid_catnames'].'","'.$arr_payout_log_details['invalid_catnames'].'","'.$correct.'","1","'.$paid.'", "'.$EDITED_LIVE.'")';
			
			$res = $dbObjLocal->query($sql);
			
			$this->maintain_log($sql);
			if($res)
				return '1';
			else
				return '0';
		}
		else
			return '0';
	}
	public function dataCorrectionPayoutLog($arr_payout_log_details,$paid='0')
	{
		$dbObjLocal	=	new DB($this->db['db_local']);
		if(!empty($arr_payout_log_details) && is_array($arr_payout_log_details) && count($arr_payout_log_details) > 0)
		{
			$sql 	= "REPLACE INTO tbl_data_correction_payout_log ";
			$arr_fn = $arr_fv = array();
			$im_fn 	= $im_fv = $on_duplicate_key_update = "";
			foreach($arr_payout_log_details AS $field => $fieldVal)
			{
				$arr_fn[] 	= $field;
				$arr_fv[] 	= "'".$fieldVal."'";
				//$on_duplicate_key_update .= $field." = '".$fieldVal."',";
			}
			$im_fn 	= implode(",",$arr_fn);
			$im_fv 	= implode(",",$arr_fv);

			$sql	.= "(".$im_fn.",is_paid) VALUES (".$im_fv.",".$paid.")";
			$res 	= $dbObjLocal->query($sql);
			$this->maintain_log($sql);
			
			if($res)
				return '1';
			else
				return '0';
		}
		else
			return '0';
	}
	public function variableFieldPayCalculation($field_name,$field_max_val,$penalty,$arr_user_edited_vals,$arr_valid_vals,$arr_invalid_vals, $reward)
	{
		$arr_user_edited_vals = explode(',',$arr_user_edited_vals);

		if(!empty($field_name) && !empty($arr_user_edited_vals)) //&& $field_max_val > 0
		{
			$validValues = $invalidValues = $totalValidValue = $totalInvalidValue = $totalPay = 0;
			$calResultArr = array();

			if(!empty($arr_valid_vals) && is_array($arr_valid_vals) && count($arr_valid_vals) > 0)
			{
				/*$validPer = round((count($arr_valid_vals) / count($arr_user_edited_vals)) * 100);
				if($validPer=='100')
				{
					if(count($arr_valid_vals) > $field_max_val)
						$totalValidValue = number_format($field_max_val, 2, '.', '');
					else
						$totalValidValue = number_format(count($arr_valid_vals), 2, '.', '');
				}
				else
					$totalValidValue = number_format(($field_max_val * $validPer) / 100, 2, '.', '');
				*/
				$totalValidValue = count($arr_valid_vals) * $reward;
			}
			else
			{
				//$validPer = 0;
				$totalValidValue = 0;
			}

			if(!empty($arr_invalid_vals) && is_array($arr_invalid_vals) && count($arr_invalid_vals) > 0)
			{
				/*
				$invalidPer = round((count($arr_invalid_vals) / count($arr_user_edited_vals)) * 100);

				if($invalidPer=='100')
				{
					if(count($arr_invalid_vals) > $penalty)
						$totalInvalidValue = number_format($penalty, 2, '.', '');
					else
						$totalInvalidValue = number_format(count($arr_invalid_vals), 2, '.', '');
				}
				else
					$totalInvalidValue = number_format(($penalty * $invalidPer) / 100, 2, '.', '');
				*/
				$totalInvalidValue = count($arr_invalid_vals) * $penalty;
			}
			else
			{
				//$invalidPer 		= 0;
				$totalInvalidValue 	= 0;
			}

			$totalPay = number_format(($totalValidValue - $totalInvalidValue),2,'.','');

			$calResultArr['total_valid_fieldvalues'] 	= $totalValidValue;
			$calResultArr['total_invalid_fieldvalues'] 	= $totalInvalidValue;
			$calResultArr['total_field_pay'] 			= $totalPay;
		}
		else
		{
			$calResultArr['total_valid_fieldvalues'] 	= '0';
			$calResultArr['total_invalid_fieldvalues'] 	= '0';
			$calResultArr['total_field_pay'] 			= '0';
		}
		return $calResultArr;
	}
	public function maintain_log($sqlstr)
	{
		if(defined('APP_PATH')) {
			$sNamePrefix= APP_PATH . "logs/log_error/jdapayout_querylog_".date("Y-m-d").".txt";
		} else {
			$sNamePrefix= $_SERVER["DOCUMENT_ROOT"]."logs/log_error/jdapayout_querylog_".date("Y-m-d").".txt";
		}
		$pathToLog 		= dirname($sNamePrefix);
		if (!file_exists($pathToLog))
		{
			mkdir($pathToLog, 0777, true);
		}
		$fp		= fopen($sNamePrefix, 'a');
		$date	= date('H:i:s');
		$time 	= date('Y-m-d');
		$string			= "[DATE# ".$date." ".$time."] [QUERY:".$sqlstr."]\n\n\n";
		fwrite($fp,$string);
		fclose($fp);
	}
	public function compare_single_value_fields($field_name, $jda_val, $tme_val, $orig_val='',$arr_field_pricing)
	{
		global $EDITED_LIVE;
		$arr_invalid_field_names_local	= array();
		$arr_invalid_field_values_local	= array();
		$arr_valid_field_names_local	= array();
		$arr_valid_field_values_local	= array();
		$status = '';
		
		// remove all spaces
		$tme_val1 	= trim(strtolower(preg_replace('/\s+/','',$tme_val)));
		$jda_val1	= trim(strtolower(preg_replace('/\s+/','',$jda_val)));		
		
		//keep only alphabets and numbers in a string; remove all other chars.
		$tme_val2	= preg_replace("/[^A-Za-z0-9]/", '',$tme_val1);
		$jda_val2	= preg_replace("/[^A-Za-z0-9]/", '',$jda_val1);
		
		
		if($EDITED_LIVE	== '1')
		{
			// remove all spaces 			
			$orig_val1	= trim(strtolower(preg_replace('/\s+/','',$orig_val)));
			
			//keep only alphabets and numbers in a string; remove all other chars.
			$orig_val2	= preg_replace("/[^A-Za-z0-9]/", '',$orig_val1);
		}
		
		//both are not empty and both the values are same then give reward for fresh contract
		if(!empty($jda_val2) && !empty($tme_val2) &&  ($jda_val2 == $tme_val2))
		{
			if($field_name == "address")
			{
				$status = 'Valid';
			}
			else
			{
				$arr_valid_field_names_local[] 	= $field_name;
				$arr_valid_field_values_local[]	= $arr_field_pricing[$field_name]['reward'];
			}
			
			// check if original values is also same then its a no change for edited contract.
			if($EDITED_LIVE	== '1')
			{
				if($orig_val2 == $tme_val2)
				{
					if($field_name == "address")
					{
						$status = '';
					}
				}
				else
				{
					if($field_name == "address")
					{
						$status = 'Valid';
					}
					else
					{
						$arr_valid_field_names_local[]	= $field_name;
						$arr_valid_field_values_local[] = $arr_field_pricing[$field_name]['reward'];
					}
				}
			}
		}
		else if(empty($jda_val2) && !empty($tme_val2)){
			//no reward or penalty as TME entered value for fresh contract
			
			// compare original values with tme values.
			// If (tme = original) and jda removed the value then penalize jda
			// if (tme != original) then do not penalize or reward b'coz jda value is empty
			if($EDITED_LIVE	== '1')
			{
				if($orig_val2 == $tme_val2)
				{
					if($field_name == "address")
					{
						$status = 'Invalid';
					}
					else
					{
						$arr_invalid_field_names_local[] 	= $field_name;
						$arr_invalid_field_values_local[] 	= $arr_field_pricing[$field_name]['penalty'];
					}
				}
				else
				{
					if($field_name == "address")
					{
						$status = '';
					}
				}
			}
		}
		//bn both the values are not same
		else if($jda_val2 != $tme_val2)
		{
			if($field_name == "address")
			{
				$status = 'Invalid';
			}
			else
			{
				$arr_invalid_field_names_local[] 	= $field_name;
				$arr_invalid_field_values_local[]	= $arr_field_pricing[$field_name]['penalty'];
			}

			// if (tme != original) then penalize the jda
			// if (tme == original) then penalize the jda
			/*
			if($EDITED_LIVE	== '1')
			{
				if($field_name == "address")
				{
					$status = 'Invalid';
				}
				else
				{
					$arr_invalid_field_names[] 	= $field_name;
					$arr_invalid_field_values[] 	= $arr_field_pricing[$field_name]['penalty'];
				}
			}
			*/
		}
		
		if($field_name == "address")
		{
			return $status;
		}
		else
		{
			return array($arr_valid_field_names_local, $arr_valid_field_values_local, $arr_invalid_field_names_local, $arr_invalid_field_values_local);
		}
	}	
	public function compare_comma_separated_fields($field_name, $jda_val, $tme_val, $orig_val='',$arr_field_pricing)
	{
		global $EDITED_LIVE;
		$arr_invalid_field_names_local 	= array();
		$arr_invalid_field_values_local = array();
		$arr_valid_field_names_local 	= array();
		$arr_valid_field_values_local 	= array();
		$arr_invalid_contact_nos 		= array();	// will be used for only mobile and landline
		$arr_valid_contact_nos 			= array();	// will be used for only mobile and landline
		
		$orig_val1 	= array();
		$jda_val1 	= array();
		$tme_val1 	= array();
		
		$orig_val2	= array();
		$jda_val2	= array();
		$tme_val2	= array();
		
		if($EDITED_LIVE	== '1')
		{
			$orig_val1 	= array_values(array_filter(explode(',',$orig_val)));			
		}
		$jda_val1 	= array_values(array_filter(explode(',',$jda_val)));
		$tme_val1 	= array_values(array_filter(explode(',',$tme_val)));
		
		
		if($field_name == "contact_person")
		{
			foreach($jda_val1 as $k=>$v)
			{
				if(preg_match("/\(/",$v))
				{
					$arr_v	= explode("(",$v);
				}
				else
				{
					$arr_v[0] = $v;
				}
				
				$v	= strtolower($arr_v[0]);
				$v1 = str_replace("mr","",$v);
				$v2 = str_replace("mrs","",$v1);
				$v3 = str_replace("dr","",$v2);
				$v4 = str_replace("ms","",$v3);
				$v5 = preg_replace('/\s+/','',$v4);
				$jda_val2[$k] = trim($v5);
			}
			
			foreach($tme_val1 as $k=>$v)
			{
				if(preg_match("/\(/",$v))
				{
					$arr_v	= explode("(",$v);
				}
				else
				{
					$arr_v[0] = $v;
				}
				
				$v	= strtolower($arr_v[0]);
				$v1 = str_replace("mr","",$v);
				$v2 = str_replace("mrs","",$v1);
				$v3 = str_replace("dr","",$v2);
				$v4 = str_replace("ms","",$v3);
				$v5 = preg_replace('/\s+/','',$v4);
				$tme_val2[$k] = trim($v5);
			}
			
			if($EDITED_LIVE	== '1')
			{
				foreach($orig_val1 as $k=>$v)
				{
					if(preg_match("/\(/",$v))
					{
						$arr_v	= explode("(",$v);
					}
					else
					{
						$arr_v[0] = $v;
					}
					
					$v	= strtolower($arr_v[0]);
					$v1 = str_replace("mr","",$v);
					$v2 = str_replace("mrs","",$v1);
					$v3 = str_replace("dr","",$v2);
					$v4 = str_replace("ms","",$v3);
					$v5 = preg_replace('/\s+/','',$v4);
					$orig_val2[$k] = trim($v5);
				}
			}
		}
		else
		{
			if($EDITED_LIVE	== '1')
			{
				$orig_val2 	= $orig_val1;
			}
			$jda_val2 	= $jda_val1;
			$tme_val2 	= $tme_val1;
		}
		
		$jda_cnt	= count($jda_val2);		
		if($jda_cnt > 0)
		{
			$cntl	= 0;
			for($cntl=0; $cntl<$jda_cnt; $cntl++)
			{
				$cnt2 = $cntl+1;
				if(!empty($jda_val2[$cntl]))
				{
					$keythis = array_search($jda_val2[$cntl],$tme_val2);
					if($keythis === FALSE)
					{
						if($field_name == 'landline' || $field_name=='mobile')
						{
							$arr_invalid_contact_nos[] 		= $jda_val2[$cntl];	// will be used for only mobile and landline
						}
						$arr_invalid_field_names_local[] 	= $field_name.'_'.$cnt2;
						$arr_invalid_field_values_local[] 	= $arr_field_pricing[$field_name]['penalty'];

					}
					else
					{
						// if edited contract
						if($EDITED_LIVE	== '1')
						{
							// check jda value == original value 
							$keythis2 = array_search($jda_val2[$cntl],$orig_val2);
							
							// if no, then give reward to jda
							if($keythis2 === FALSE)
							{
								if($field_name == 'landline' || $field_name=='mobile')
								{
									$arr_valid_contact_nos[] 		= $jda_val2[$cntl];	// will be used for only mobile and landline
								}
								$arr_valid_field_names_local[] 	= $field_name.'_'.$cnt2;
								$arr_valid_field_values_local[] = $arr_field_pricing[$field_name]['reward'];
							}
							else
							{
								// if yes, no reward as this value already exists in original table
							}
						}
						else
						{
							// if jda value exists in tme val then give reward to jda							
							if($field_name == 'landline' || $field_name=='mobile')
							{
								$arr_valid_contact_nos[] = $jda_val2[$cntl];	// will be used for only mobile and landline								
							}
							$arr_valid_field_names_local[]	= $field_name.'_'.$cnt2;
							$arr_valid_field_values_local[] = $arr_field_pricing[$field_name]['reward'];
						}
					}
				}
			}
			
			//if edited contract
			if($EDITED_LIVE	== '1')
			{
				$arr_intersect 	= array();
				
				// get common values from tme and original.
				$arr_intersect 	= array_values(array_intersect($tme_val2,$orig_val2));
				$cnt_in			= count($arr_intersect);
				if($cnt_in > 0)
				{
					$q=1;
					for($p=0; $p<=$cnt_in; $p++)
					{
						// if tme enters a value which is same as original value
						// but jda has deleted that value then give penalty to jda
						if(!in_array($arr_intersect[$p],$jda_val2))
						{
							if($field_name == 'landline' || $field_name=='mobile')
							{
								$arr_invalid_contact_nos[] 		= $arr_intersect[$p];	// will be used for only mobile and landline
							}
							$arr_invalid_field_names_local[] 	= $field_name .'_'.$q;
							$arr_invalid_field_values_local[] 	= $arr_field_pricing[$field_name]['penalty'];
						}
						$q++;
					}
				}
			}
			
			if($field_name == 'landline' || $field_name=='mobile')
			{
				return array($arr_valid_field_names_local, $arr_valid_field_values_local, $arr_invalid_field_names_local, $arr_invalid_field_values_local, $arr_invalid_contact_nos, $arr_valid_contact_nos);
			}
			else
			{
				return array($arr_valid_field_names_local, $arr_valid_field_values_local, $arr_invalid_field_names_local, $arr_invalid_field_values_local);
			}
		}
	}
	
	public function check_values($val)
	{
		if($val > 0)
		{
			return true;
		}
		elseif($val == "0")
		{
			return true;
		}
		else
		{
			return false;
		}
	}
		
}
