<?php
class dataCorrectionPayout
{
	var $tme_pricing_fields_values;
	var $tme_non_mendatory_fields;

	function __construct($dbarr)
	{

		$this->tme_pricing_fields_values = array("companyName" => "1", "address" => "1", "landmark" => "1", "contact_person" => "1", "email" => "1", "working_time" => "1");

		$this->tme_non_mendatory_fields = array("contact_person", "email", "working_time");
		$this -> conn_iro  	= new DB($dbarr['DB_IRO']);					
		$this -> conn_local = new DB($dbarr['LOCAL']);
		$this -> conn_local_slave = new DB($dbarr['LOCAL_SLAVE']);
		$this -> dbconn_idc	= new DB($dbarr['DB_ONLINE1']);
	}

	public function generateDataCorrectionPayout($from_date,$to_date,$city,$module_type='tme',$cron='',$paid=0)
	{
		
		if(!empty($from_date) && !empty($to_date) && !empty($city))
		{
			$auditCalc = $this->dataCorrectionAuditCalculation($from_date,$to_date,$city,$module_type,$cron,$paid);

			if($auditCalc == '1'){
				return true;
			}
			return false;
		}
		return false;
	}

	public function dataCorrectionAuditCalculation($from_date,$to_date,$city,$module_type,$cron,$paid)
	{
		if(!empty($from_date) && !empty($to_date) && !empty($city) && !empty($module_type))
		{
			if($cron == 'tmecron'){
				$payout_eligibility = 90;
				$records = array();
			}
			else
			{
				$payout_eligibility = 90;
			}

			$audit_status = "";
			if($module_type == 'tme')
			{
				if($paid != 1){
					$paid_where = " AND pl.is_paid != 1";//" AND dc_tme.paid != 1 ";
					$where_tme = " AND gi.paid != 1 ";
					$where_tme2 = " AND paid != 1 ";
					$where_tme3 = " AND is_paid != 1 ";
					$where_tme4 = " AND a.paid != 1 ";
				}else if($paid == 1){
					$paid_where = " AND pl.is_paid = 1";//" AND dc_tme.paid = 1 ";
					$where_tme = " AND gi.paid = 1 ";
					$where_tme2 = " AND paid = 1 ";
					$where_tme3 = " AND is_paid = 1 ";
					$where_tme4 = " AND a.paid = 1 ";
				}

				$audit_status =  " AND is_audited='1' ";
			}
			elseif ($module_type == 'tmepaid')
			{
				$paid_where = " AND pl.is_paid = 1";//" AND dc_tme.paid = 1 ";
				$where_tme = " AND gi.paid = 1 ";
				$where_tme2 = " AND b.paid = 1 ";
				$where_tme3 = " AND is_paid = 1 ";
				$audit_status =  " AND is_audited='0' ";
			}
			elseif ($module_type == 'cse')
			{
				$paid_where = " AND pl.is_paid = 1";//" AND dc_tme.paid = 1 ";
				$where_tme = " AND gi.paid = 1 ";
				$where_tme2 = " AND paid = 1 ";
				$audit_status =  " AND is_audited='1' ";
			}

			define("MAX_CAPPED_AMT",'1000');

			$insertSql = $selectSql = $from = $where = "";


			$where = "WHERE batch_start_date='".$from_date." 00:00:00' AND batch_end_date='".$to_date." 23:59:59' AND module_type='".$module_type."'";

			$deleteSql = "DELETE FROM data_correction_payout ".$where;
			$deleteQuery = $this -> conn_local->query_sql($deleteSql);

			$insertSql = "REPLACE INTO data_correction_payout (userid,audit_data_count,total_fixed_fields_audited,total_valid_fixed_fields,total_invalid_fixed_fields,per_of_fields_validated,valid_total_pay,invalid_total_pay,total_categories_audited,total_valid_categories,total_invalid_categories,per_of_categories_validated,category_penalty,category_pay,category_total_pay,total_landline_audited,total_valid_landline,total_invalid_landline,per_of_landline_validated,landline_penalty,landline_pay,landline_total_pay,total_mobile_audited,total_valid_mobile,total_invalid_mobile,per_of_mobile_validated,mobile_penalty,mobile_pay,mobile_total_pay,total_earned,final_payout,batch_start_date,batch_end_date,city,lastupdated,module_type) ";
			$selectSql = "SELECT pl.editedby_id AS userid, ";
			$where = " WHERE pl.entered_date >= '".$from_date." 00:00:00' AND pl.entered_date <= '".$to_date." 23:59:59' AND pl.editedby_id != '' ".$audit_status." AND module_type='".$module_type."' ".$paid_where." AND (pl.freeze = '0' AND pl.mask ='0') GROUP BY pl.editedby_id ORDER BY pl.editedby_id ";

			if($module_type=='tme' || $module_type=='tme90')
			{
				$from = " FROM tbl_data_correction_payout_log pl ";
				$selectSql .= "COUNT(1) as audit_data_count, ";
			}
			else if($module_type=='cse')
			{
				$from = " FROM tbl_data_correction_payout_log pl ";
				$selectSql .= "COUNT(1) AS audit_data_count, ";
			}
			
			$date = date('Y-m-d H:i:s');

			$selectSql .= "SUM(IF(valid_field_names != '',length(valid_field_names) - length(replace(valid_field_names,',',''))+1,0) + IF(invalid_field_names != '',length(invalid_field_names) - length(replace(invalid_field_names,',',''))+1,0)) AS total_fixed_fields_audited, SUM(IF(valid_field_names != '',length(valid_field_names) - length(replace(valid_field_names,',',''))+1,0)) AS total_valid_fixed_fields, SUM(IF(invalid_field_names != '',length(invalid_field_names) - length(replace(invalid_field_names,',',''))+1,0)) AS total_invalid_fixed_fields, ROUND((SUM(IF(valid_field_names != '',length(valid_field_names) - length(replace(valid_field_names,',',''))+1,0)) * 100) / SUM(IF(valid_field_names != '',length(valid_field_names) - length(replace(valid_field_names,',',''))+1,0) + IF(invalid_field_names != '',length(invalid_field_names) - length(replace(invalid_field_names,',',''))+1,0)),2) AS per_of_fields_validated, SUM(total_valid_field_values) AS valid_total_pay, SUM(total_invalid_field_values) AS invalid_total_pay, SUM(IF(valid_catnames != '',(length(replace(valid_catnames,'|~|',',')) - length(replace(replace(valid_catnames,'|~|',','),',','')))+1,0) + IF(invalid_catnames != '',(length(replace(invalid_catnames,'|~|',',')) - length(replace(replace(invalid_catnames,'|~|',','),',','')))+1,0)) AS total_categories_audited, SUM(IF(valid_catnames != '',(length(replace(valid_catnames,'|~|',',')) - length(replace(replace(valid_catnames,'|~|',','),',','')))+1,0)) AS total_valid_categories, SUM(IF(invalid_catnames != '',(length(replace(invalid_catnames,'|~|',',')) - length(replace(replace(invalid_catnames,'|~|',','),',','')))+1,0)) AS total_invalid_categories, ROUND((SUM(IF(valid_catnames != '',(length(replace(valid_catnames,'|~|',',')) - length(replace(replace(valid_catnames,'|~|',','),',','')))+1,0)) * 100) / SUM(IF(valid_catnames != '',(length(replace(valid_catnames,'|~|',',')) - length(replace(replace(valid_catnames,'|~|',','),',','')))+1,0) + IF(invalid_catnames != '',(length(replace(invalid_catnames,'|~|',',')) - length(replace(replace(invalid_catnames,'|~|',','),',','')))+1,0)),2) AS per_of_categories_validated,SUM(total_invalid_catvalues) AS category_penalty,SUM(total_valid_catvalues) AS category_pay,SUM(total_cat_pay) AS category_total_pay, SUM(IF(valid_landline != '',length(valid_landline) - length(replace(valid_landline,',',''))+1,0) + IF(invalid_landline != '',length(invalid_landline) - length(replace(invalid_landline,',',''))+1,0)) AS total_landline_audited, SUM(IF(valid_landline != '',length(valid_landline) - length(replace(valid_landline,',',''))+1,0)) AS total_valid_landline, SUM(IF(invalid_landline != '',length(invalid_landline) - length(replace(invalid_landline,',',''))+1,0)) AS total_invalid_landline, ROUND((SUM(IF(valid_landline != '',length(valid_landline) - length(replace(valid_landline,',',''))+1,0)) * 100) / SUM(IF(valid_landline != '',length(valid_landline) - length(replace(valid_landline,',',''))+1,0) + IF(invalid_landline != '',length(invalid_landline) - length(replace(invalid_landline,',',''))+1,0)),2) AS per_of_landline_validated,SUM(total_invalid_lanvalues) AS landline_penalty,SUM(total_valid_lanvalues) AS landline_pay,SUM(total_landline_pay) AS landline_total_pay, SUM(IF(valid_mobile != '',length(valid_mobile) - length(replace(valid_mobile,',',''))+1,0) + IF(invalid_mobnames != '',length(invalid_mobnames) - length(replace(invalid_mobnames,',',''))+1,0)) AS total_mobile_audited, SUM(IF(valid_mobile != '',length(valid_mobile) - length(replace(valid_mobile,',',''))+1,0)) AS total_valid_mobile, SUM(IF(invalid_mobnames != '',length(invalid_mobnames) - length(replace(invalid_mobnames,',',''))+1,0)) AS total_invalid_mobile, ROUND((SUM(IF(valid_mobile != '',length(valid_mobile) - length(replace(valid_mobile,',',''))+1,0)) * 100) / SUM(IF(valid_mobile != '',length(valid_mobile) - length(replace(valid_mobile,',',''))+1,0) + IF(invalid_mobnames != '',length(invalid_mobnames) - length(replace(invalid_mobnames,',',''))+1,0)),2) AS per_of_mobile_validated,SUM(total_invalid_mobvalues) AS mobile_penalty,SUM(total_valid_mobvalues) AS mobile_pay, SUM(total_mobile_pay) AS mobile_total_pay, ((SUM(total_valid_field_values) + SUM(total_cat_pay) + SUM(total_landline_pay) + SUM(total_mobile_pay)) - SUM(total_invalid_field_values)) AS total_earned, IF(((SUM(total_valid_field_values) + SUM(total_cat_pay) + SUM(total_landline_pay) + SUM(total_mobile_pay)) - SUM(total_invalid_field_values)) > ".MAX_CAPPED_AMT.",".MAX_CAPPED_AMT.",((SUM(total_valid_field_values) + SUM(total_cat_pay) + SUM(total_landline_pay) + SUM(total_mobile_pay)) - SUM(total_invalid_field_values))) AS final_payout, '".$from_date." 00:00:00', '".$to_date." 23:59:59', '".$city."', '".$date."', '".$module_type."' ";

			$selectSql .= $from .= $where;
			$insertSql .= $selectSql;
			
			$execQuery = $this -> conn_local->query_sql($insertSql);
			
			/*------------------ Added by Neelam @ 28/02/2013 : To update username in data_correction_payout from mktgEmpMaster or emplogin table---------------------*/
			
			$sql_username = "SELECT userid, module_type FROM data_correction_payout WHERE batch_start_date >= '".$from_date." 00:00:00' AND batch_end_date <= '".$to_date." 23:59:59' AND module_type='".$module_type."'";			
			$res_username = $this->conn_local_slave->query_sql($sql_username);
			if(mysql_num_rows($res_username) > 0)
			{
				while($row_username = mysql_fetch_assoc($res_username))
				{
					$userid_res 		= $row_username['userid'];
					$module_type_res 	= $row_username['module_type'];
					$user_name_res 		= $this->fetchUserName($userid_res, $module_type_res);
					
					$sql_updt_username = "UPDATE data_correction_payout SET username = '".$user_name_res."' WHERE batch_start_date >= '".$from_date." 00:00:00' AND batch_end_date <= '".$to_date." 23:59:59' AND module_type='".$module_type_res."' AND userid = '".$userid_res."'";
					$res_updt_username = $this->conn_local->query_sql($sql_updt_username);
				}
			}	
			/*------------------ END --------------------*/
			$ex_from_date = explode("-",$from_date);
			$year		  = $ex_from_date[0];
			$month		  = $ex_from_date[1];
			$tbl_name	  = '';
			
			if($paid != 1)
			{
				if($year == "2013" && $month <= 3)
				{
					$f_m_cond	= " ";
				}
				else
				{
					$f_m_cond	= "AND (freeze ='0' AND mask ='0') ";
				}
				$tbl_name = 'tbl_dc_for_dialer';
			}
			else
			{
				$f_m_cond	= "AND (freeze ='0' AND mask ='0') ";
				if($year == "2013" && $month <= 3)
					{ $tbl_name = 'tbl_data_correction_for_tme';}
				else
					{ $tbl_name = 'tbl_dc_for_dialer'; }
					
			}
			
			
			//$selectAllTmeList = "SELECT editedby_id,editedby_name FROM tbl_data_correction_payout_log pl join tbl_tme_dc_logs as dcl ON dcl.parentid = pl.parentid AND dcl.entered_date = pl.entered_date join db_iro.tbl_companymaster_generalinfo as gi on  pl.parentid=gi.parentid WHERE pl.entered_date >= '".$from_date." 00:00:00' AND pl.entered_date <= '".$to_date." 23:59:59' AND pl.editedby_id != '' AND dcl.module_type='".$module_type."' ".$where_tme." GROUP BY pl.editedby_id ORDER BY pl.editedby_id ";
			
			
			/*$selectAllTmeList = "SELECT createdby as editedby_id, count(1) as total_allocated FROM ".$tbl_name." WHERE entered_date >= '".$from_date."' AND entered_date <= '".$to_date."' AND allocated='1' AND (priority_field !='' OR priority_field IS NOT NULL) ".$where_tme2." ".$f_m_cond." GROUP BY createdby";*/
			
			/* Modified by Neelam @ 11/06/2013 - In paid payout total allocated is blank issue resolved*/
			$selectAllTmeList = "SELECT abc.userid as editedby_id, dialer.total_allocated FROM (SELECT userid from data_correction_payout WHERE batch_start_date >= '".$from_date." 00:00:00' AND batch_end_date <= '".$to_date." 23:59:59' AND module_type='".$module_type."') abc LEFT JOIN (SELECT createdby as editedby_id, count(1) as total_allocated FROM ".$tbl_name." WHERE entered_date >= '".$from_date."' AND entered_date <= '".$to_date."' AND allocated='1' AND (priority_field !='' OR priority_field IS NOT NULL) ".$where_tme2." ".$f_m_cond." GROUP BY createdby) AS dialer ON abc.userid = dialer.editedby_id ";
			
			$execAllTmeList = $this -> conn_local_slave->query_sql($selectAllTmeList);
			$where = "";

			while($fetchAllTme = mysql_fetch_assoc($execAllTmeList))
			{
				$TMEaccurancy 	= 0;
				$total_audited	= 0;
				$total_allocated = 0;
				$total_correct 	= 0;
				$total_records 	= 0;
				
				$editedby_name = '';
				$editedby_name = $this->fetchUserName($fetchAllTme['editedby_id'], $module_type);
						
				//$sql_correct = "SELECT COUNT(1) as cnt, a.correct FROM (SELECT parentid, entered_date, correct, paid, tme_id, module_type FROM tbl_tme_dc_logs WHERE entered_date >= '".$from_date." 00:00:00' AND entered_date <= '".$to_date." 23:59:59' AND allocated='1' AND module_type='".$module_type."' AND tme_id = '".$fetchAllTme['editedby_id']."' ".$where_tme2." GROUP BY parentid, date(entered_date)) a LEFT JOIN tbl_data_correction_for_tme b on a.parentid=b.parentid and a.entered_date = b.entered_date_display AND  a.tme_id = b.createdby  WHERE a.entered_date >= '".$from_date." 00:00:00' AND a.entered_date <= '".$to_date." 23:59:59' AND b.allocated='1' AND a.module_type='".$module_type."' AND a.tme_id = '".$fetchAllTme['editedby_id']."' ".$where_tme4." AND (b.freeze ='0' AND b.mask ='0') GROUP BY a.correct ";
				$sql_correct = "SELECT COUNT(1) as cnt, correct FROM ".$tbl_name."  as a WHERE entered_date >= '".$from_date." 00:00:00' AND entered_date <= '".$to_date." 23:59:59' AND allocated='1'  AND createdby = '".$fetchAllTme['editedby_id']."' ".$where_tme4."  AND (freeze ='0' AND mask ='0') GROUP BY correct";
				$res_correct = $this->conn_local_slave->query_sql($sql_correct);
				if(mysql_num_rows($res_correct) > 0)
				{
					while($row_correct = mysql_fetch_assoc($res_correct))
					{
						if($row_correct['correct'] == '1')
						{
							$total_correct = $row_correct['cnt']; #store correct records separately to insert.

						}
						//$total_records 	+= $row_correct['cnt']; // get total records contribution
					}
				}
				
				$sql_total_records = "SELECT count(1) as total_records FROM tbl_tme_dc_logs as a WHERE entered_date >= '".$from_date." 00:00:00' AND entered_date <= '".$to_date." 23:59:59' AND allocated='1' AND module_type='".$module_type."' AND tme_id = '".$fetchAllTme['editedby_id']."' ".$where_tme4 . " GROUP BY parentid, date(entered_date) ";				
				$res_total_records = $this->conn_local_slave->query_sql($sql_total_records);
				$row_total_records 	= mysql_num_rows($res_total_records);
				$total_records		= $row_total_records;
				
				/* Fetch total allocated count from tbl_dc_for_dialer Added by Neelam @ 01/03/2013*/
				$total_allocated = $fetchAllTme['total_allocated'];
				/* END */
				
				#get the audited records from payout table
				$sql_audited = "SELECT audit_data_count FROM data_correction_payout WHERE userid = '".$fetchAllTme['editedby_id']."' AND batch_start_date >= '".$from_date." 00:00:00' AND batch_end_date <= '".$to_date." 23:59:59' AND module_type='".$module_type."' ";
				$res_audited = $this->conn_local_slave->query_sql($sql_audited);

				if(mysql_num_rows($res_audited) > 0)
				{
					$row_audited 	= mysql_fetch_assoc($res_audited);
					$total_audited 	= $row_audited['audit_data_count'];						
					#total allocated records are sum of audited records and total pending records from dialer table
				}
				// Added by Neelam
				if(empty($total_allocated) )
					$total_allocated = 0;
				//	
				if($total_allocated < $total_audited)
					$total_allocated = $total_audited;

				if($total_records < $total_allocated)
					$total_records = $total_allocated;
				
				if($total_audited < 1)
				{
					$TMEaccurancy = 0;
				}
				else
				{
					$TMEaccurancy = round(($total_correct / $total_audited) * 100);
				}
				$reward_amt = 0;
				if($TMEaccurancy >= 90 && $module_type!="tme90" && $module_type!="cse" && $paid!=1)
				{
					$checkTotalTmeRecords = "SELECT parentid,entered_date FROM tbl_data_correction_for_tme WHERE allocated='1' AND correct = '2' AND createdby = '".$fetchAllTme['editedby_id']."' AND entered_date >= '".$from_date."' AND entered_date <= '".$to_date."' AND (freeze ='0' AND mask ='0')";
					$resultTotalTmeRecords = $this->conn_local_slave->query_sql($checkTotalTmeRecords);

					while($fetchTotalTmeRecords = mysql_fetch_assoc($resultTotalTmeRecords))
					{
						$valid_fields = 0;
						$valid_fields_value = 0;
						$valid_fields_count = 0;

						$valid_landline = 0;
						$valid_landline_values = 0;
						$valid_landline_count = 0;

						$valid_mobile = 0;
						$valid_mobile_values = 0;
						$valid_mobile_count = 0;

						$valid_categories = 0;
						$valid_categories_values = 0;
						$valid_category_count = 0;
						$tmeAllCatid = 0;

						$tmeAddedCatArr = '';
						$tmeRemovedCatArr = '';
						$tmeAllCatArr = '';
						$catCalResult ='';

						$select_tme_pricing_fields = "SELECT dc.companyname as companynameTME,dc.building_name as building_nameTME,dc.street as streetTME,dc.area as areaTME,dc.pincode as pincodeTME,dc.landmark as landmarkTME,dc.contact_person as contact_personTME,dc.email as emailTME,dc.working_time_start as working_time_startTME,dc.working_time_end as working_time_endTME,dc.landline as landlineTME,dc.mobile as mobileTME,gi.companyname,gi.building_name,gi.street,gi.area,gi.landline,gi.mobile,gi.pincode,gi.landmark,gi.contact_person,gi.email,dc.added_catids,dc.removed_catids,ge.working_time_start,ge.working_time_end,ge.original_date AS 'contract_date' FROM tbl_data_correction_for_tme dc left join db_iro.tbl_companymaster_generalinfo gi on dc.parentid = gi.parentid left join db_iro.tbl_companymaster_extradetails ge on dc.parentid = ge.parentid WHERE dc.parentid = '".$fetchTotalTmeRecords['parentid']."' AND entered_date = '".$fetchTotalTmeRecords['entered_date']."' AND (dc.freeze ='0' AND dc.mask = '0')";
						$result_tme_pricing_fields = $this->conn_local_slave->query_sql($select_tme_pricing_fields);
						$fetch_tme_pricing_fields = mysql_fetch_assoc($result_tme_pricing_fields);

						$tme_working_time_start = trim($fetch_tme_pricing_fields['working_time_startTME'], ',');
						$tme_working_time_end = trim($fetch_tme_pricing_fields['working_time_endTME'], ',');
						$compmaster_working_time_start = trim($fetch_tme_pricing_fields['working_time_start'], ',');
						$compmaster_working_time_end = trim($fetch_tme_pricing_fields['working_time_end'], ',');

						if(trim($fetch_tme_pricing_fields['companynameTME']) != trim($fetch_tme_pricing_fields['companyname'])){
							$reward_amt++;
							$valid_fields = 'companyName,';
							$valid_fields_value = '1,';
						}

						if(trim($fetch_tme_pricing_fields['building_nameTME']) != trim($fetch_tme_pricing_fields['building_name']) || trim($fetch_tme_pricing_fields['streetTME']) != trim($fetch_tme_pricing_fields['street']) || trim($fetch_tme_pricing_fields['pincodeTME']) != trim($fetch_tme_pricing_fields['pincode']) || trim($fetch_tme_pricing_fields['areaTME']) != trim($fetch_tme_pricing_fields['area'])){
							$reward_amt++;
							$valid_fields .= 'address,';
							$valid_fields_value .= '1,';
						}

						if(trim($fetch_tme_pricing_fields['landmarkTME']) != trim($fetch_tme_pricing_fields['landmark'])){
							$reward_amt++;
							$valid_fields .= 'landmark,';
							$valid_fields_value .= '1,';
						}

						if(trim($fetch_tme_pricing_fields['contact_personTME']) != trim($fetch_tme_pricing_fields['contact_person'])){
							$reward_amt++;
							$valid_fields .= 'contact_person,';
							$valid_fields_value .= '1,';
						}

						if(trim($fetch_tme_pricing_fields['emailTME']) != trim($fetch_tme_pricing_fields['email'])){
							$reward_amt++;
							$valid_fields .= 'email,';
							$valid_fields_value .= '1,';
						}

						if(($tme_working_time_start != $compmaster_working_time_start)||($tme_working_time_end != $compmaster_working_time_end)){
							$reward_amt++;
							$valid_fields .= 'working_time,';
							$valid_fields_value .= '1,';
						}

						if(trim($fetch_tme_pricing_fields['landlineTME']) != trim($fetch_tme_pricing_fields['landline'])){
							$landlineCal = $this->variableFieldPayCalculation('landline','2',$fetch_tme_pricing_fields['landlineTME'],$fetch_tme_pricing_fields['landlineTME'],'');
							$valid_landline = $fetch_tme_pricing_fields['landlineTME'];
							$valid_landline_values = $landlineCal['total_valid_fieldvalues'];

							$reward_amt += $valid_landline_values;
							$reward_lan += $valid_landline_values;

							//echo "valid_landline=>".$valid_landline.'<br>';
							//echo "valid_landline_values=>".$valid_landline_values.'<br>';
						}

						if(trim($fetch_tme_pricing_fields['mobileTME']) != trim($fetch_tme_pricing_fields['mobile'])){
							$mobileCal = $this->variableFieldPayCalculation('mobile','2',$fetch_tme_pricing_fields['mobileTME'],$fetch_tme_pricing_fields['mobileTME'],'');

							$valid_mobile = $fetch_tme_pricing_fields['mobileTME'];
							$valid_mobile_values = $mobileCal['total_valid_fieldvalues'];
							$reward_amt += $valid_mobile_values;
							$reward_mob += $valid_mobile_values;
							//echo 'valid_mobile=>'.$valid_mobile.'<br>';
							//echo 'valid_mobile_values=>'.$valid_mobile_values.'<br>';
						}

						if($fetch_tme_pricing_fields['added_catids'] != '' || $fetch_tme_pricing_fields['removed_catids'] !='' ){

							$tmeAddedCatArr = explode("|~|",$fetch_tme_pricing_fields['added_catids']);
							$tmeRemovedCatArr = explode("|~|",$fetch_tme_pricing_fields['removed_catids']);
							if(!empty($tmeAddedCatArr) && !empty($tmeRemovedCatArr))
								$tmeAllCatArr = array_values(array_filter(array_merge($tmeAddedCatArr,$tmeRemovedCatArr)));
							else if(!empty($tmeAddedCatArr) && empty($tmeRemovedCatArr))
								$tmeAllCatArr = array_values(array_filter($tmeAddedCatArr));
							else if(empty($tmeAddedCatArr) && !empty($tmeRemovedCatArr))
								$tmeAllCatArr = array_values(array_filter($tmeRemovedCatArr));

							$catCalResult = $this->variableFieldPayCalculation('category','5',$tmeAllCatArr,$tmeAllCatArr,'');
							$valid_categories_values = $catCalResult['total_valid_fieldvalues'];
							$reward_amt += $valid_categories_values;
							$reward_cat += $valid_categories_values;
						}

						if(is_array($tmeAllCatArr)){
							$tmeAllCatid = implode('|~|',$tmeAllCatArr);
						}else{
							$tmeAllCatid = '';
						}

						$valid_fields = trim($valid_fields,',');
						$valid_fields_value = trim($valid_fields_value,',');
						if($valid_fields_value != 0)
							$valid_fields_count = count(explode(',',$valid_fields_value));

						//echo "valid_landline1 =>".$valid_landline."valid_mobile1=>".$valid_mobile.'<br>';

						if($valid_landline != ''){
							$valid_landline_count = count(explode(',',$valid_landline)).'<br>';
						}

						if($valid_mobile != ''){
							$valid_mobile_count = count(explode(',',$valid_mobile)).'<br>';
						}

						if($tmeAllCatid != ''){
							$valid_category_count = count(explode('|~|',$tmeAllCatid)).'<br>';
						}
						
						$insert_tme90_records = "INSERT IGNORE INTO tbl_data_correction_payout_log (parentid,editedby_id,editedby_name,valid_field_names,valid_field_values,total_valid_field_values,valid_catnames,total_valid_catvalues,total_cat_pay,valid_landline,total_valid_lanvalues,total_landline_pay,valid_mobile,total_valid_mobvalues,total_mobile_pay,city,entered_date,batch_date_start,batch_date_end,is_audited,module_type,contract_date,is_accepted,to_be_paid)VALUES('".$fetchTotalTmeRecords['parentid']."','".$fetchAllTme['editedby_id']."','".$editedby_name."','".$valid_fields."','".$valid_fields_value."','".$valid_fields_count."','".$tmeAllCatid."','".$valid_category_count."','".$valid_categories_values."','".$valid_landline."','".$valid_landline_count."','".$valid_landline_values."','".$valid_mobile."','".$valid_mobile_count."','".$valid_mobile_values."','".$city."','".$fetchTotalTmeRecords['entered_date']."','".$from_date." 00:00:00','".$to_date." 23:59:59',0,'tme90','".$fetch_tme_pricing_fields['contract_date']."','1','1')";
						$result_tme90_records = $this->conn_local->query_sql($insert_tme90_records);
					}

					$select_TME_payout_amt = "SELECT final_payout FROM data_correction_payout WHERE batch_start_date = '".$from_date." 00:00:00' AND batch_end_date = '".$to_date." 23:59:59' AND userid = '".$fetchAllTme['editedby_id']."' AND module_type='".$module_type."'";
					$result_TME_payout_amt = $this->conn_local->query_sql($select_TME_payout_amt);
					$fetch_TME_payout_amt = mysql_fetch_assoc($result_TME_payout_amt);

					$total_grand_payout_total = $fetch_TME_payout_amt['final_payout'] + $reward_amt;
					$update_grand_payout_total = "UPDATE data_correction_payout SET reward_amt = '".$reward_amt."',reward_payout_total ='".$total_grand_payout_total."',data_accuracy = '".$TMEaccurancy."', total_contributed=".$total_records.", total_allocated=".$total_allocated.", total_correct_records='".$total_correct."' WHERE batch_start_date >= '".$from_date." 00:00:00' AND batch_end_date <= '".$to_date." 23:59:59' AND userid = '".$fetchAllTme['editedby_id']."' AND module_type='".$module_type."'";
					$result_grand_payout_total = $this->conn_local->query_sql($update_grand_payout_total);
				}
				else
				{
					$update_grand_payout_total = "UPDATE data_correction_payout SET reward_amt = 0,reward_payout_total = final_payout ,data_accuracy = '".$TMEaccurancy."', total_contributed=".$total_records.", total_allocated=".$total_allocated.", total_correct_records='".$total_correct."' WHERE batch_start_date >= '".$from_date." 00:00:00' AND batch_end_date <= '".$to_date." 23:59:59' AND userid = '".$fetchAllTme['editedby_id']."' AND module_type='".$module_type."'";
					$result_grand_payout_total = $this->conn_local->query_sql($update_grand_payout_total);
				}
			}
			return '1';
		}
		else
			return '0';
	}

	public function getBatchWiseAllTMEs($from_date,$to_date,$city,$module_type)
	{
		if(!empty($from_date) && !empty($to_date) && !empty($city) && !empty($module_type))
		{
			$selectSql = $from = $where = "";
			$selectSql = "SELECT pl.editedby_id AS userid, ";
			$where = "WHERE pl.entered_date >= '".$from_date." 00:00:00' AND pl.entered_date <= '".$to_date." 23:59:59' AND pl.editedby_id != '' AND is_audited='1' AND module_type='".$module_type."' AND (pl.freeze = '0' AND pl.mask = '0') GROUP BY pl.editedby_id ORDER BY pl.editedby_id ";

			if($module_type=='tme')
			{
				$from = "FROM tbl_data_correction_payout_log pl left join mktgEmpMaster AS me on pl.editedby_id = me.mktempcode ";
				$selectSql .= "me.empName AS username, COUNT(1) as totalData ";
			}
			else if($module_type=='cse')
			{
				$from = "FROM tbl_data_correction_payout_log pl left join db_iro.emplogin AS me on pl.editedby_id = me.empCode ";
				$selectSql .= "CONCAT_WS(' ',me.empFName,me.empLName) AS username, COUNT(1) AS totalData ";
			}

			$selectSql .= $from .= $where;
			$execQuery = $this -> conn_local ->query_sql($selectSql);
			$data = $this->fetchAll($execQuery);

			if(!empty($data) && is_array($data) && count($data) > 0)
				return $data;
			else
				return '0';
		}
		else
			return '0';
	}

	public function checkUserAlreadyExistsInPayout($userid,$from_date,$to_date,$city,$module_type)
	{
		if(!empty($userid) && !empty($from_date) && !empty($to_date) && !empty($city) && !empty($module_type))
		{
			$selectSql = "SELECT COUNT(1) AS cnt FROM data_correction_payout WHERE userid = '".$userid."' AND batch_start_date = '".$from_date."' AND batch_end_date = '".$to_date."' AND city = '".$city."' AND module_type='".$module_type."'";

			$execSql = $this->conn_local->query_sql($selectSql);
			$cntArr = $this->fetchSingal($execSql);
			if(!empty($cntArr) && is_array($cntArr) && $cntArr['cnt'] > 0)
				return true;
			else
				return false;
		}
		else
			return '0';
	}

	public function getUserAllocatedDataCount($userid,$from_date,$to_date,$city,$module_type)
	{
		if(!empty($userid) && !empty($from_date) && !empty($to_date) && !empty($city) && !empty($module_type))
		{
			$sourceTable = $addWhere = "";
			if($module_type=='tme')
			{
				$selectSql = "SELECT sum(total_per_data) AS totalData FROM tbl_data_correction_user_wise_counts WHERE user_id = '".$userid."' AND batch_start_date >= '".$from_date."' AND batch_end_date <= '".$to_date."' AND user_id != '' AND module_type='".$module_type."'";
				//$sourceTable = "tbl_data_correction";
			}
			else if($module_type=='cse')
			{
				$sourceTable = "tbl_data_correction_for_cse";
				$selectSql = "SELECT COUNT(1) AS totalData FROM ".$sourceTable." AS dc LEFT JOIN db_iro.tbl_companymaster_generalinfo gi on dc.parentid=gi.parentid WHERE dc.editedby = '".$userid."' AND entered_date >= '".$from_date."' AND entered_date <= '".$to_date."' AND dc.editedby != '' AND dc.allocated=1 AND gi.paid=1";
			}

			$execQuery = $this->conn_local_slave->query_sql($selectSql);
			$data = $this->fetchSingal($execQuery);
			if(!empty($data) && is_array($data) && $data['totalData'] > 0)
				return $data['totalData'];
			else
				return '0';

		}
		else
			return '0';
	}

	public function getUserAuditedDataCounts($userid,$from_date,$to_date,$status='',$city,$module_type)
	{
		if(!empty($userid) && !empty($from_date) && !empty($to_date) && !empty($city) && !empty($module_type))
		{
			$auditedDataCount = 0;
			$where = "";
			$where = "WHERE editedby_id = '".$userid."' AND entered_date >= '".$from_date."' AND entered_date <= '".$to_date."' AND is_audited=1 AND module_type='".$module_type."'";
			if(!empty($status))
			{
				if($status=='accepted')
					$where .= "AND is_accepted='1'";
				else if($status=='rejected')
					$where .= "AND is_accepted='0' AND parentid NOT IN (SELECT DISTINCT parentid FROM tbl_data_correction_payout_log ".$where." AND is_accepted='1' AND (freeze = '0' AND mask = '0'))";
			}

			$sql = "SELECT count(DISTINCT parentid) as totalData FROM tbl_data_correction_payout_log ".$where;

				$execQuery = $this->conn_local->query_sql($sql);
				$data = $this->fetchSingal($execQuery);
				if(!empty($data) && is_array($data) && $data['totalData'] > 0)
					return $data['totalData'];
				else
					return '0';
			}
			else
				return '0';
		}

	public function getUserAuditDataFieldCounts($userid,$from_date,$to_date,$city,$module_type)
	{
		if(!empty($userid) && !empty($from_date) && !empty($to_date) && !empty($city) && !empty($module_type))
		{
			$assignedDataFieldCount = $dataArr = array();

			// Please enable this for TME calculations
			$where = $whereAdd = "";
			$selectField = "";

			// Original
			$where = "WHERE editedby_id = '".$userid."' AND is_audited = 1 AND module_type = '".$module_type."' ";

			if($module_type=='tme')
			{
				if($report_type=='d')
					$where .= "AND batch_date_start = '".$from_date."' AND batch_date_end = '".$to_date."' ";
				else if($report_type=='dt')
					$where .= "AND batch_date_start >= '".$from_date."' AND batch_date_end <= '".$to_date."' ";
			}
			else if($module_type=='cse')
				$where .= "AND entered_date >= '".$from_date."' AND entered_date <= '".$to_date."' ";

			if(!empty($this->tme_pricing_fields_values) && is_array($this->tme_pricing_fields_values) && count($this->tme_pricing_fields_values) > 0)
			{
				foreach($this->tme_pricing_fields_values AS $fieldName => $fieldValue)
				{
					$sql = "";
					$sql = "SELECT COUNT(DISTINCT parentid) AS '".$fieldName."' FROM tbl_data_correction_payout_log AS dcp ";
					$whereAdd = "AND MATCH(valid_field_names,invalid_field_names) AGAINST('".$fieldName."' in boolean mode) AND (freeze ='0' AND mask ='0') ";


					$sql .= $where.$whereAdd;

					$execQuery = $this->conn_local->query_sql($sql);
					$data = $this->fetchSingal($execQuery);
					if(!empty($data) && is_array($data) && count($data) > 0)
						$dataArr[$fieldName] = $data[$fieldName];
					else
						$dataArr[$fieldName] = '0';
				}
			}

			$totalEnteredFieldCounts = 0;

			if(!empty($dataArr) && is_array($dataArr) && count($dataArr) > 0)
			{
				foreach($dataArr AS $keyField => $keyValue)
				{
					$assignedDataFieldCount[$keyField] = $keyValue;
					$totalEnteredFieldCounts = $totalEnteredFieldCounts + $keyValue;

					if(array_key_exists($keyField,$this->tme_pricing_fields_values))
					{
						$assignedDataFieldCount[$keyField.'_fv'] = $this->tme_pricing_fields_values[$keyField];
						$assignedDataFieldCount[$keyField.'_tv'] = $keyValue * $this->tme_pricing_fields_values[$keyField];
					}
					else
					{
						$assignedDataFieldCount[$keyField.'_fv'] = '0';
						$assignedDataFieldCount[$keyField.'_tv'] = '0';
					}

					$total_field_pay = $total_field_pay + $assignedDataFieldCount[$keyField.'_tv'];
				}

				$assignedDataFieldCount['total_entered_field_counts'] = $totalEnteredFieldCounts;
				$assignedDataFieldCount['total_field_pay'] = $total_field_pay;
			}
			else
			{
				foreach($this->tme_pricing_fields_values AS $key => $value)
				{
					$assignedDataFieldCount[$key] = '0';

					$assignedDataFieldCount[$key.'_fv'] = $this->tme_pricing_fields_values[$key];
					$assignedDataFieldCount[$key.'_tv'] = '0';
					$total_field_pay = 0;
				}

				$assignedDataFieldCount['total_entered_field_counts'] = '0';
				$assignedDataFieldCount['total_field_pay'] = '0';
			}

			return $assignedDataFieldCount;

		}
		else
			return '0';
	}

	public function getUserValidInvalidDataFieldCounts($userid,$from_date,$to_date,$city,$module_type)
	{
		if(!empty($userid) && !empty($from_date) && !empty($to_date) && !empty($city) && !empty($module_type))
		{
			if(!empty($this->tme_pricing_fields_values) && is_array($this->tme_pricing_fields_values) && count($this->tme_pricing_fields_values) > 0)
			{
				$dataArr = array();
				// FOR TMEs / CSEs
				foreach($this->tme_pricing_fields_values AS $fieldName => $fieldValue)
				{
					$where = "";

					$where = "WHERE dc.editedby_id = '".$userid."' AND dc.parentid = cv.parentid AND dc.entered_date >= '".$from_date."' AND dc.entered_date <= '".$to_date."' AND dc.is_accepted = '1' AND dc.is_audited = '1' AND dc.module_type='".$module_type."' AND (dc.freeze ='0' AND dc.mask ='0') ";

					$sql = "SELECT ";

					// Original
					if($fieldName == 'address')
					{
						$sql = "SELECT COUNT(DISTINCT(parentid))  AS '".$fieldName."' FROM (SELECT cv.parentid, COUNT(cv.parentid) AS fieldMatch  FROM tbl_data_correction_payout_log AS dc, tbl_data_correction_contract_validation AS cv WHERE dc.editedby_id = '".$userid."' AND dc.parentid = cv.parentid AND dc.entered_date >= '".$from_date."' AND dc.entered_date <= '".$to_date."' AND dc.is_accepted = '1' AND dc.is_audited = '1' AND (cv.field_name = 'building_name' OR  cv.field_name = 'area' OR cv.field_name = 'pincode') AND cv.validate_status = '1' AND dc.module_type='".$module_type."' GROUP BY cv.parentid HAVING fieldMatch=3) addr";
					}
					else if($fieldName == 'email')
					{
						$sql .= " COUNT(DISTINCT(cv.parentid)) AS '".$fieldName."'";

						$where .= " AND (cv.field_name = 'email_1' OR cv.field_name = 'email_2' OR cv.field_name = 'email_3') AND cv.validate_status = '1' ";
					}
					else if($fieldName == 'working_time')
					{
						$sql .= " COUNT(DISTINCT(cv.parentid)) AS '".$fieldName."'";

						$where .= " AND cv.field_name = 'working_time' AND cv.validate_status = '1' ";

					}
					else
					{
						$sql .= " COUNT(DISTINCT(cv.parentid)) AS '".$fieldName."'";

						$where .= " AND cv.field_name = '$fieldName' AND cv.validate_status = '1' ";

					}

					if($fieldName != 'address')
						$sql .= " FROM tbl_data_correction_payout_log AS dc, tbl_data_correction_contract_validation AS cv ".$where;

					$resultTME = $this->conn_local->query_sql($sql);

					$dataTME = $this->fetchAssoc($resultTME);

					if(!empty($dataTME) && is_array($dataTME) && count($dataTME) > 0)
					{
						foreach($dataTME AS $keyTme => $keyArrTme)
						{
							foreach($keyArrTme AS $keyFieldTme => $keyValueTme)
							{
								$dataArr[$keyFieldTme] = $dataArr[$keyFieldTme] + $keyValueTme;
							}
						}
					}
				}
				//
			}


			$totalValidFieldCounts = 0;
			if(!empty($dataArr) && is_array($dataArr) && count($dataArr) > 0)
			{
				foreach($dataArr AS $keyField => $keyValue)
				{
					$auditedDataFieldCount[$keyField] = $keyValue;

					$totalValidFieldCounts = $totalValidFieldCounts + $keyValue;

					//echo substr($keyField,13)."<pre>";
					if(array_key_exists($keyField,$this->tme_pricing_fields_values))
					{
						$auditedDataFieldCount[$keyField.'_fv'] = $this->tme_pricing_fields_values[$keyField];
						$auditedDataFieldCount[$keyField.'_tv'] = $keyValue * $this->tme_pricing_fields_values[$keyField];
					}
					else
					{
						$auditedDataFieldCount[$keyField.'_fv'] = '0';
						$auditedDataFieldCount[$keyField.'_tv'] = '0';
					}

					$total_field_pay = $total_field_pay + $auditedDataFieldCount[$keyField.'_tv'];
				}

				$auditedDataFieldCount['total_valid_field_counts'] = $totalValidFieldCounts;
				$auditedDataFieldCount['total_field_pay'] = $total_field_pay;
			}
			else
			{
				foreach($this->tme_pricing_fields_values AS $key => $value)
				{
					$auditedDataFieldCount[$key] = '0';

					$auditedDataFieldCount[$key.'_fv'] = $this->tme_pricing_fields_values[$key];
					$auditedDataFieldCount[$key.'_tv'] = '0';
					$total_field_pay = '0';
				}

				$auditedDataFieldCount['total_valid_field_counts'] = '0';
				$auditedDataFieldCount['total_field_pay'] = '0';
			}

			return $auditedDataFieldCount;
		}
		else
			return '0';
	}

	public function getVariableFieldsTotalValue($userid,$from_date,$to_date,$city,$module_type)
	{
		if(!empty($userid) && !empty($from_date) && !empty($to_date) && !empty($city) && !empty($module_type))
		{
			//Added on 30-03-2012
			$sql = "SELECT SUM(total_cat_pay) AS total_category_pay, SUM(total_landline_pay) AS total_tele_pay, SUM(total_mobile_pay) AS total_mobile_pay FROM (SELECT DISTINCT parentid,total_cat_pay,total_landline_pay,total_mobile_pay,editedby_id FROM tbl_data_correction_payout_log AS dcp WHERE dcp.editedby_id = '".$userid."' AND entered_date >= '".$from_date."' AND entered_date <= '".$to_date."' AND dcp.is_audited=1 AND dcp.module_type='".$module_type."' AND (dcp.freeze ='0' AND dcp.mask ='0')) AS a GROUP BY editedby_id";

			$resultSql = $this->conn_local->query_sql($sql);

			$data = $this->fetchSingal($resultSql);

			if(!empty($data) && is_array($data) && count($data) > 0)
				return $data;
			else
				return '0';
		}
		else
			return '0';
	}


	public function getDatacorrectionPayoutDetails($from_date,$to_date,$city,$userid='',$module_type)
	{
		if(!empty($from_date) && !empty($to_date) && !empty($city) && !empty($module_type))
		{
			$where = "";
			$where = "WHERE batch_start_date='".$from_date." 00:00:00' AND batch_end_date='".$to_date." 23:59:59' AND city='".$city."' AND module_type='".$module_type."'";

			// do not show records where allocation is less than 20% of total contributed.
			/*
			if($module_type == 'tme')
			{
				$where .= " AND total_allocated >= ROUND(0.20*total_contributed) ";
			}
			*/

			if(!empty($userid))
				$where .= " AND userid='".$userid."'";

			$sql = "SELECT userid, username, total_contributed, total_allocated, audit_data_count, total_correct_records, total_fixed_fields_audited, total_valid_fixed_fields, total_invalid_fixed_fields, per_of_fields_validated, valid_total_pay, invalid_total_pay, total_categories_audited, total_valid_categories ,total_invalid_categories, per_of_categories_validated, category_penalty, category_pay, category_total_pay, total_landline_audited, total_valid_landline, total_invalid_landline, per_of_landline_validated, landline_penalty, landline_pay, landline_total_pay, total_mobile_audited, total_valid_mobile, total_invalid_mobile, per_of_mobile_validated, mobile_penalty, mobile_pay, mobile_total_pay, total_earned, data_accuracy, reward_amt, final_payout, reward_payout_total as grand_payout FROM data_correction_payout ".$where." ORDER BY data_accuracy DESC";			
			$execQuery = $this->conn_local->query_sql($sql);
			$data = $this->fetchAll($execQuery);

			$unwanted_data = array();
			$filter_data = array();
			$i = 0;
			foreach($data as $heading => $val)
			{
				if($data[$heading]['total_earned'] == 0 && $data[$heading]['data_accuracy'] == 100) {
					$unwanted_data[$i] = $heading;
				}
				else{
					//$filter_data[$i] = $data[$i];
				}
					$i++;
				}

			if(count($unwanted_data) > 0 )
			{
				foreach($unwanted_data as $key => $headval)
				{
					unset($data[$headval]);
				}
			}
			$filter_data1 = array_values(array_filter($data));


			if(!empty($filter_data1) && is_array($filter_data1) && count($filter_data1) > 0)
				return $filter_data1;
			else
				return '0';
		}
		else
			return '0';
	}


	public function datacorrectionPayoutHtml($from_date,$to_date,$city,$module_type,$ret_arr=false)
	{
		if(!empty($from_date) && !empty($to_date) && !empty($city) && !empty($module_type))
		{
			$datacorrectionPayoutArr 	= $this->getDatacorrectionPayoutDetails($from_date,$to_date,$city,'',$module_type);
			$arr_data_correction_payout = $datacorrectionPayoutArr;

			if(!empty($datacorrectionPayoutArr) && is_array($datacorrectionPayoutArr) && count($datacorrectionPayoutArr) > 0)
			{
				if($module_type=='tme')
					$payoutHeading = "TME Data Correction Payout List";
				else if($module_type=='cse')
					$payoutHeading = "CSE Data Correction Payout List";
				else
					$payoutHeading = "TME 90% Data Correction Payout List";

				$dataCnt = count($datacorrectionPayoutArr);
				$output = "";
				$output .= "<table name='tbldatacorrectionPayout' align='center' width='100%' border='0'>";
				if($module_type=='tme'){
					$output .= "<tr><td colspan='34' align='center' class='tableTH'>$payoutHeading</td><td align='center'><input type='submit' name='crbtn' value='Make Live' onClick='return submt();'></td></tr>";
				}else{
					$output .= "<tr><td colspan='35' align='center' class='tableTH'>$payoutHeading</td><td align='center'></td></tr>";
				}
				$output .= "<tr>";
				$output .= "<th align='left' class='tableTH'>User Id</th>";
				$output .= "<th align='left' class='tableTH'>User Name</th>";
				$output .= "<th align='right' class='tableTH'>Total Contributed</th>";
				$output .= "<th align='right' class='tableTH'>20% of Total Contributed</th>";
				$output .= "<th align='right' class='tableTH'>Total Allocated</th>";
				$output .= "<th align='right' class='tableTH'>Total Audited</th>";
				$output .= "<th align='right' class='tableTH'>Total Correct Records</th>";
				$output .= "<th align='right' class='tableTH'>Total Fixed Fields Audited</th>";
				$output .= "<th align='right' class='tableTH'>Total Valid Fixed Fields</th>";
				$output .= "<th align='right' class='tableTH'>Total Invalid Fixed Fields</th>";
				$output .= "<th align='right' class='tableTH'>Fixed Fields Audit %</th>";
				$output .= "<th align='right' class='tableTH'>Total Fixed Fields Pay</th>";
				$output .= "<th align='right' class='tableTH'>Total Fixed Fields Penalty</th>";
				$output .= "<th align='right' class='tableTH'>Total Categories Audited</th>";
				$output .= "<th align='right' class='tableTH'>Total Valid Categories</th>";
				$output .= "<th align='right' class='tableTH'>Total Invalid Categories</th>";
				$output .= "<th align='right' class='tableTH'>Category Audit %</th>";
				$output .= "<th align='right' class='tableTH'>Category Penalty</th>";
				$output .= "<th align='right' class='tableTH'>Category Pay</th>";
				$output .= "<th align='right' class='tableTH'>Total Category Pay</th>";
				$output .= "<th align='right' class='tableTH'>Total Landline Audited</th>";
				$output .= "<th align='right' class='tableTH'>Total Valid Landline</th>";
				$output .= "<th align='right' class='tableTH'>Total Invalid Landline</th>";
				$output .= "<th align='right' class='tableTH'>Landline Audit %</th>";
				$output .= "<th align='right' class='tableTH'>Landline Penalty</th>";
				$output .= "<th align='right' class='tableTH'>Landline Pay</th>";
				$output .= "<th align='right' class='tableTH'>Total Landline Pay</th>";
				$output .= "<th align='right' class='tableTH'>Total Mobile Audited</th>";
				$output .= "<th align='right' class='tableTH'>Total Valid Mobile</th>";
				$output .= "<th align='right' class='tableTH'>Total Invalid Mobile</th>";
				$output .= "<th align='right' class='tableTH'>Mobile Audit %</th>";
				$output .= "<th align='right' class='tableTH'>Mobile Penalty</th>";
				$output .= "<th align='right' class='tableTH'>Mobile Pay</th>";
				$output .= "<th align='right' class='tableTH'>Total Mobile Pay</th>";
				$output .= "<th align='right' class='tableTH'>Total Earned</th>";
				$output .= "<th align='right' class='tableTH'>Accuracy</th>";
				$output .= "<th align='right' class='tableTH'>Reward</th>";
				$output .= "<th align='right' class='tableTH'>Final Payout</th>";
				$output .= "<th align='right' class='tableTH'>Grand payout</th>";
				$output .= "<th align='right' class='tableTH'>TME City</th>";
				$output .= "</tr>";
				//$output .= "</table>";
				//$output .= "<div style='overflow-y:scroll;height:265px;'>";
				//$output .= "<table width='100%' border='0' cellspacing='0' cellpadding='1'>";
				$dataCountArr = array();
				for($r=0;$r<$dataCnt;$r++)
				{
					$userid = $datacorrectionPayoutArr[$r]['userid'];
					$username = (!empty($datacorrectionPayoutArr[$r]['username'])) ? $datacorrectionPayoutArr[$r]['username'] : "-";
					$total_contributed = $datacorrectionPayoutArr[$r]['total_contributed'];
					$total_allocated = $datacorrectionPayoutArr[$r]['total_allocated'];
					$total_audited = $datacorrectionPayoutArr[$r]['audit_data_count'];
					$total_correct_records = $datacorrectionPayoutArr[$r]['total_correct_records'];
					$total_fixed_audited = $datacorrectionPayoutArr[$r]['total_fixed_fields_audited'];
					$total_fixed_valid = $datacorrectionPayoutArr[$r]['total_valid_fixed_fields'];
					$total_fixed_invalid = $datacorrectionPayoutArr[$r]['total_invalid_fixed_fields'];
					$per_of_fields_validated = $datacorrectionPayoutArr[$r]['per_of_fields_validated'];
					$total_valid_pay = $datacorrectionPayoutArr[$r]['valid_total_pay'];
					$total_penalty = $datacorrectionPayoutArr[$r]['invalid_total_pay'];
					$total_cat_audited = $datacorrectionPayoutArr[$r]['total_categories_audited'];
					$total_cat_valid = $datacorrectionPayoutArr[$r]['total_valid_categories'];
					$total_cat_invalid = $datacorrectionPayoutArr[$r]['total_invalid_categories'];
					$per_cat_validated = $datacorrectionPayoutArr[$r]['per_of_categories_validated'];
					$category_penalty = $datacorrectionPayoutArr[$r]['category_penalty'];
					$category_pay = $datacorrectionPayoutArr[$r]['category_pay'];
					$total_category_pay = $datacorrectionPayoutArr[$r]['category_total_pay'];
					$total_lan_audited = $datacorrectionPayoutArr[$r]['total_landline_audited'];
					$total_lan_valid = $datacorrectionPayoutArr[$r]['total_valid_landline'];
					$total_lan_invalid = $datacorrectionPayoutArr[$r]['total_invalid_landline'];
					$per_lan_validated = $datacorrectionPayoutArr[$r]['per_of_landline_validated'];
					$landline_penalty = $datacorrectionPayoutArr[$r]['landline_penalty'];
					$landline_pay = $datacorrectionPayoutArr[$r]['landline_pay'];
					$total_tele_pay = $datacorrectionPayoutArr[$r]['landline_total_pay'];
					$total_mob_audited = $datacorrectionPayoutArr[$r]['total_mobile_audited'];
					$total_mob_valid = $datacorrectionPayoutArr[$r]['total_valid_mobile'];
					$total_mob_invalid = $datacorrectionPayoutArr[$r]['total_invalid_mobile'];
					$per_mob_validated = $datacorrectionPayoutArr[$r]['per_of_mobile_validated'];
					$mobile_penalty = $datacorrectionPayoutArr[$r]['mobile_penalty'];
					$mobile_pay = $datacorrectionPayoutArr[$r]['mobile_pay'];
					$total_mobile_pay = $datacorrectionPayoutArr[$r]['mobile_total_pay'];
					$total_earned = $datacorrectionPayoutArr[$r]['total_earned'];
					$data_accuracy = $datacorrectionPayoutArr[$r]['data_accuracy'];
					$reward_amt = $datacorrectionPayoutArr[$r]['reward_amt'];
					$reward_payout_total = $datacorrectionPayoutArr[$r]['grand_payout'];
					$final_payout = $datacorrectionPayoutArr[$r]['final_payout'];
					$tme_city		= $this->fetchTmeCity($userid);

					$per_20 = round(0.20 * $total_contributed);
					$arr_data_correction_payout[$r]['20_per_count'] = $per_20;

					// as requested by Hiren
					if($data_accuracy == "0")
					{
						$reward_payout_total = 0;
						$arr_data_correction_payout[$r]['grand_payout'] =  $reward_payout_total;
					}
					elseif($total_audited < $per_20 && $data_accuracy >= "90")
					{
						$reward_payout_total = $final_payout;
						$arr_data_correction_payout[$r]['grand_payout'] =  $reward_payout_total;
					}
					$arr_data_correction_payout[$r]['tme_city'] = $tme_city;
					$output .= "<tr>";
					$output .= "<td align='left' class='tableTD'>$userid</td>";
					$output .= "<td align='left'  class='tableTD'>$username</td>";
					$output .= "<td align='right' class='tableTD'>$total_contributed</td>";
					$output .= "<td align='right' class='tableTD'>$per_20</td>";
					$output .= "<td align='right' class='tableTD'>$total_allocated</td>";
					$output .= "<td align='right' class='tableTD'>$total_audited</td>";
					$output .= "<td align='right' class='tableTD'>$total_correct_records</td>";
					$output .= "<td align='right' class='tableTD'>$total_fixed_audited</td>";
					$output .= "<td align='right' class='tableTD'>$total_fixed_valid</td>";
					$output .= "<td align='right' class='tableTD'>$total_fixed_invalid</td>";
					$output .= "<td align='right' class='tableTD'>$per_of_fields_validated</td>";
					$output .= "<td align='right' class='tableTD'>$total_valid_pay</td>";
					$output .= "<td align='right' class='tableTD'>$total_penalty</td>";
					$output .= "<td align='right' class='tableTD'>$total_cat_audited</td>";
					$output .= "<td align='right' class='tableTD'>$total_cat_valid</td>";
					$output .= "<td align='right' class='tableTD'>$total_cat_invalid</td>";
					$output .= "<td align='right' class='tableTD'>$per_cat_validated</td>";
					$output .= "<td align='right' class='tableTD'>$category_penalty</td>";
					$output .= "<td align='right' class='tableTD'>$category_pay</td>";
					$output .= "<td align='right' class='tableTD'>$total_category_pay</td>";
					$output .= "<td align='right' class='tableTD'>$total_lan_audited</td>";
					$output .= "<td align='right' class='tableTD'>$total_lan_valid</td>";
					$output .= "<td align='right' class='tableTD'>$total_lan_invalid</td>";
					$output .= "<td align='right' class='tableTD'>$per_lan_validated</td>";
					$output .= "<td align='right' class='tableTD'>$landline_penalty</td>";
					$output .= "<td align='right' class='tableTD'>$landline_pay</td>";
					$output .= "<td align='right' class='tableTD'>$total_tele_pay</td>";
					$output .= "<td align='right' class='tableTD'>$total_mob_audited</td>";
					$output .= "<td align='right' class='tableTD'>$total_mob_valid</td>";
					$output .= "<td align='right' class='tableTD'>$total_mob_invalid</td>";
					$output .= "<td align='right' class='tableTD'>$per_mob_validated</td>";
					$output .= "<td align='right' class='tableTD'>$mobile_penalty</td>";
					$output .= "<td align='right' class='tableTD'>$mobile_pay</td>";
					$output .= "<td align='right' class='tableTD'>$total_mobile_pay</td>";
					$output .= "<td align='right' class='tableTD'>$total_earned</td>";
					$output .= "<td align='right' class='tableTD'>$data_accuracy%</td>";
					$output .= "<td align='right' class='tableTD'>$reward_amt</td>";
					$output .= "<td align='right' class='tableTD'>$final_payout</td>";
					$output .= "<td align='right' class='tableTD'>$reward_payout_total</td>";
					$output .= "<td align='right' class='tableTD'>$tme_city</td>";
					$output .= "</tr>";
				}
				$output .= "</table>";
				//$output .= "</div>";

				if($ret_arr == true)
					return array($output,$arr_data_correction_payout);
				else
					return $output;
			}
			else
				return '0';
		}
		else
			return '0';
	}

	public function getUserValidInvalidDataFieldCountsNew($userid,$from_date,$to_date,$city,$module_type)
	{
		if(!empty($userid) && !empty($from_date) && !empty($to_date) && !empty($city) && !empty($module_type))
		{
			if(!empty($this->tme_pricing_fields_values) && is_array($this->tme_pricing_fields_values) && count($this->tme_pricing_fields_values) > 0)
			{
				$dataArr = array();
				// FOR TMEs / CSEs
				foreach($this->tme_pricing_fields_values AS $fieldName => $fieldValue)
				{
					$validwhere = $invalidwhere = "";

					$validwhere = "WHERE dc.editedby_id = '".$userid."' AND dc.entered_date >= '".$from_date."' AND dc.entered_date <= '".$to_date."' AND dc.is_accepted = '1' AND dc.is_audited = '1' AND dc.module_type='".$module_type."' AND (dc.freeze ='0' AND dc.mask ='0') ";

					$invalidwhere = $validwhere;

					$validsql = "SELECT ";
					$invalidsql = "SELECT ";


					$validsql .= " COUNT(DISTINCT(dc.parentid)) AS '".$fieldName."'";

					$invalidsql .= " COUNT(DISTINCT(dc.parentid)) AS '".$fieldName."'";

					$validwhere .= " AND MATCH(dc.valid_field_names) AGAINST('".$fieldName."' IN BOOLEAN MODE) ";
					$invalidwhere .= " AND MATCH(dc.invalid_field_names) AGAINST('".$fieldName."' IN BOOLEAN MODE) ";

					$validsql .= " FROM tbl_data_correction_payout_log AS dc ".$validwhere;
					$invalidsql .= " FROM tbl_data_correction_payout_log AS dc ".$invalidwhere;
					$validresultTME = $this->conn_local->query_sql($validsql);
					$invalidresultTME = $this->conn_local->query_sql($invalidsql);

					$validdataTME = $this->fetchAssoc($validresultTME);
					$invaliddataTME = $this->fetchAssoc($invalidresultTME);

					if(!empty($validdataTME) && is_array($validdataTME) && count($validdataTME) > 0)
					{
						foreach($validdataTME AS $keyTme => $keyArrTme)
						{
							foreach($keyArrTme AS $keyFieldTme => $keyValueTme)
							{
								$dataArr[$keyFieldTme] = $dataArr[$keyFieldTme] + $keyValueTme;
							}
						}
					}

					if(!empty($invaliddataTME) && is_array($invaliddataTME) && count($invaliddataTME) > 0)
					{
						foreach($invaliddataTME AS $keyTme => $keyArrTme)
						{
							foreach($keyArrTme AS $keyFieldTme => $keyValueTme)
							{
								$dataArr[$keyFieldTme] = $dataArr[$keyFieldTme] + $keyValueTme;
							}
						}
					}
				}
				//
			}


			$totalValidFieldCounts = 0;
			if(!empty($dataArr) && is_array($dataArr) && count($dataArr) > 0)
			{
				foreach($dataArr AS $keyField => $keyValue)
				{
					$auditedDataFieldCount[$keyField] = $keyValue;

					$totalValidFieldCounts = $totalValidFieldCounts + $keyValue;

					//echo substr($keyField,13)."<pre>";
					if(array_key_exists($keyField,$this->tme_pricing_fields_values))
					{
						$auditedDataFieldCount[$keyField.'_fv'] = $this->tme_pricing_fields_values[$keyField];
						$auditedDataFieldCount[$keyField.'_tv'] = $keyValue * $this->tme_pricing_fields_values[$keyField];
					}
					else
					{
						$auditedDataFieldCount[$keyField.'_fv'] = '0';
						$auditedDataFieldCount[$keyField.'_tv'] = '0';
					}

					$total_field_pay = $total_field_pay + $auditedDataFieldCount[$keyField.'_tv'];
				}

				$auditedDataFieldCount['total_valid_field_counts'] = $totalValidFieldCounts;
				$auditedDataFieldCount['total_field_pay'] = $total_field_pay;
			}
			else
			{
				foreach($this->tme_pricing_fields_values AS $key => $value)
				{
					$auditedDataFieldCount[$key] = '0';

					$auditedDataFieldCount[$key.'_fv'] = $this->tme_pricing_fields_values[$key];
					$auditedDataFieldCount[$key.'_tv'] = '0';
					$total_field_pay = '0';
				}

				$auditedDataFieldCount['total_valid_field_counts'] = '0';
				$auditedDataFieldCount['total_field_pay'] = '0';
			}

			return $auditedDataFieldCount;
		}
		else
			return '0';
	}



	public function makeTmeDataLive($from_date,$to_date){

		$from_date = $from_date.' 00:00:00';
		$to_date = $to_date.' 23:59:59';
		
		$date = date('Y-m-d H:i:s');
		
		if($from_date != '' && $to_date != ''){
			$update_tbl_data_live = "INSERT INTO tbl_tme_dc_make_data_live (batch_start_date,batch_end_date,insert_date,insert_by) VALUES('".$from_date."','".$to_date."','".$date."','".$_SESSION['ucode']."')";
			$execQuery = $this->conn_local->query_sql($update_tbl_data_live);
		}
	}


	public function variableFieldPayCalculation($fieldName,$fieldMaxVal,$tmeEditedValArr,$validValArr,$invalidValArr)
	{
		if(!empty($fieldName) && $fieldMaxVal > 0 && !empty($tmeEditedValArr))
		{
			$validValues = $invalidValues = $totalValidValue = $totalInvalidValue = $totalPay = 0;
			$calResultArr = array();

			if(!empty($validValArr) && count($validValArr))
			{
				$validPer = round((count($validValArr) / count($tmeEditedValArr)) * 100);
				if($validPer=='100')
				{
					if(count($validValArr) > $fieldMaxVal)
						$totalValidValue = number_format($fieldMaxVal, 2, '.', '');
					else
						$totalValidValue = number_format(count($validValArr), 2, '.', '');
				}
			else
				$totalValidValue = number_format(($fieldMaxVal * $validPer) / 100, 2, '.', '');
			}
			else
			{
				$validPer = 0;
				$totalValidValue = 0;
			}

			if(!empty($invalidValArr) && is_array($invalidValArr) && count($invalidValArr))
			{
				$invalidPer = round((count($invalidValArr) / count($tmeEditedValArr)) * 100);
				if($invalidPer=='100')
				{
					if(count($invalidValArr) > $fieldMaxVal)
					$totalInvalidValue = number_format($fieldMaxVal, 2, '.', '');
				else
					$totalInvalidValue = number_format(count($invalidValArr), 2, '.', '');
			}
			else
				$totalInvalidValue = number_format(($fieldMaxVal * $invalidPer) / 100, 2, '.', '');
			}
			else
			{
				$invalidPer = 0;
				$totalInvalidValue = 0;
			}

			$totalPay = number_format(($totalValidValue - $totalInvalidValue),2,'.','');

			$calResultArr['total_valid_fieldvalues'] = $totalValidValue;
			$calResultArr['total_invalid_fieldvalues'] = $totalInvalidValue;
			$calResultArr['total_field_pay'] = $totalPay;
		}
		else
		{
			$calResultArr['total_valid_fieldvalues'] = '0';
			$calResultArr['total_invalid_fieldvalues'] = '0';
			$calResultArr['total_field_pay'] = '0';
		}
		return $calResultArr;
	}

	public function get_tmes_with_data_accuracy($from_date, $to_date, $module_type)
	{
		if($module_type == 'tme')
		{
			$where_tme = " AND paid != 1 ";
		}
		$tme = array();
		
		echo $selectAllTmeList = "SELECT tme_id as editedby_id FROM (SELECT DISTINCT tme_id FROM tbl_tme_dc_logs WHERE entered_date >= '".$from_date." 00:00:00' AND entered_date <= '".$to_date." 23:59:59' AND allocated='1' AND module_type='tme' AND paid != 1 GROUP BY sphinx_id, date(entered_date)) t ";
		$execAllTmeList = $this->conn_local_slave->query_sql($selectAllTmeList);
		$where = "";

		while($fetchAllTme = mysql_fetch_assoc($execAllTmeList))
		{
			$TMEaccurancy 		= 0;
			$total_audited		= 0;
			$total_allocated 	= 0;

			#total_contribution count from logs data b'coz in  _for_tme table parentids get deleted				
			#$sql_correct = "SELECT COUNT(1) as cnt, correct FROM (SELECT parentid, entered_date, correct FROM tbl_tme_dc_logs WHERE entered_date >= '".$from_date." 00:00:00' AND entered_date <= '".$to_date." 23:59:59' AND allocated='1' AND module_type='tme' AND tme_id = '".$fetchAllTme['editedby_id']."' AND paid != 1 GROUP BY parentid, date(entered_date)) t GROUP BY correct";
			//$sql_correct = "SELECT COUNT(1) as cnt, a.correct FROM (SELECT parentid, entered_date, correct, paid, tme_id, module_type FROM tbl_tme_dc_logs WHERE entered_date >= '".$from_date." 00:00:00' AND entered_date <= '".$to_date." 23:59:59' AND allocated='1' AND module_type='".$module_type."' AND tme_id = '".$fetchAllTme['editedby_id']."' ".$where_tme." GROUP BY parentid, date(entered_date)) a LEFT JOIN tbl_data_correction_for_tme b on a.parentid=b.parentid and a.entered_date = b.entered_date_display AND  a.tme_id = b.createdby  WHERE a.entered_date >= '".$from_date." 00:00:00' AND a.entered_date <= '".$to_date." 23:59:59' AND b.allocated='1' AND a.module_type='".$module_type."' AND a.tme_id = '".$fetchAllTme['editedby_id']."' AND a.paid !=1 AND (b.freeze ='0' AND b.mask ='0') GROUP BY a.correct ";

			$sql_correct = "SELECT COUNT(1) as cnt, correct FROM tbl_dc_for_dialer  as a WHERE entered_date >= '".$from_date." 00:00:00' AND entered_date <= '".$to_date." 23:59:59' AND allocated='1'  AND createdby = '".$fetchAllTme['editedby_id']."' AND paid !=1 AND (freeze ='0' AND mask ='0') GROUP BY correct";			
			$res_correct = $this->conn_local_slave->query_sql($sql_correct);

			if(mysql_num_rows($res_correct) > 0)
			{
				$total_correct 	= 0;
				while($row_correct = mysql_fetch_assoc($res_correct))
				{
					if($row_correct['correct'] == '1' || $row_correct['correct'] == 1)
					{
						$total_correct = $row_correct['cnt']; #store correct records separately to insert.
					}
				}
			}
			
			#get the pending records from dialer table.
			$sql_pending = "SELECT COUNT(1) AS total_pending FROM tbl_dc_for_dialer WHERE allocated='1' AND createdby = '".$fetchAllTme['editedby_id']."' AND entered_date >= '".$from_date."' AND entered_date <= '".$to_date."' AND correct='2'";
			$res_pending = $this->conn_local_slave->query_sql($sql_pending);

			if(mysql_num_rows($res_pending) > 0)
			{
				$row_pending 	= mysql_fetch_assoc($res_pending);
				$total_pending 	= $row_pending['total_pending'];
			}

			#get the audited records from payout table
			//$sql_audited = "SELECT audit_data_count FROM data_correction_payout WHERE userid = '".$fetchAllTme['editedby_id']."' AND batch_start_date >= '".$from_date." 00:00:00' AND batch_end_date <= '".$to_date." 23:59:59'";
			$sql_audited = "SELECT count(1) as audit_data_count from tbl_data_correction_payout_log where editedby_id='".$fetchAllTme['editedby_id']."' and batch_date_start >= '".$from_date." 00:00:00' AND batch_date_end <= '".$to_date." 23:59:59' GROUP BY editedby_id ";
			$res_audited = $this->conn_local->query_sql($sql_audited);

			if(mysql_num_rows($res_audited) > 0)
			{
				$row_audited 	= mysql_fetch_assoc($res_audited);
				$total_audited 	= $row_audited['audit_data_count'];
				$total_allocated = $total_audited + $total_pending;
				#total allocated records are sum of audited records and total pending records from dialer table

			}

			echo "<br><br>TME  === " .$fetchAllTme['editedby_id'];
			echo "<br><br>total_audited === " .$total_audited;
			echo "<br><br>total_allocated === " .$total_allocated;
			echo "<br><br>total_correct === " .$total_correct;

			//total audited should match upto total allocated.
			if(!empty($total_audited) && !empty($total_allocated) && $total_audited >= $total_allocated)
			{
				// confirmed with Deodatt on 07-August-2012
				$TMEaccurancy = round(($total_correct / $total_audited) * 100);
				
				echo "<br><br>TMEaccurancy === " . $TMEaccurancy;					
				if($TMEaccurancy >= 90)
				{
					$tme[] = $fetchAllTme['editedby_id'];
				}
			}
			echo "<br><hr>";
		}
		if (count($tme) > 0 )
			return $tme;
		else
			return false;

	}

	public function fetchAll($query_result)
	{
		if(!empty($query_result))
		{
			$records = array();
			while ($data = mysql_fetch_assoc($query_result))
				array_push($records, $data);
			return $records;
		}
		else
		{
			return null;
		}
	}


	public function fetchSingal($query_result)
	{

		if(!empty($query_result))
		{
			$records = array();
			$records = mysql_fetch_array($query_result);
			return $records;
		}
		else
		{
			return null;
		}
	}


	public function fetchRow($query_result)
	{
		if(!empty($query_result))
		{
			$result = mysql_fetch_row($query_result);

			return $result;
		}
		else
		{
			return null;
		}
	}

	public function fetchObject($query_result)
	{
		if(!empty($query_result))
		{
			$records = array();
			while ($data = mysql_fetch_object($query_result))
				array_push($records, $data);
			return $records;
		}
	}

	public function fetchAssoc($query_result)
	{
		if(!empty($query_result))
		{
			$records = array();
			while ($data = mysql_fetch_assoc($query_result))
				array_push($records, $data);
			return $records;
		}
	}

	public function numRows($query_result)
	{
		if(!empty($query_result))
		{
			$num_records = "";
			$num_records = mysql_num_rows($query_result);
			return $num_records;
		}
		else
		{
			return null;
		}
	}

	public function fetchAssocConcat($query_result)
	{
		if(!empty($query_result))
		{
			$records = array();
			while ($data = mysql_fetch_array($query_result))
				array_push($records, $data[0]);
			return $records;
		}
	}
	
	public function fetchTmeCity($tmeid)
	{
		$tme_city = '';
		if(!empty($tmeid))
		{
			$sqlfetch = "SELECT city from login_details.tbl_loginDetails WHERE mktEmpCode ='".$tmeid."'";
			$resfetch =	$this -> dbconn_idc->query_sql($sqlfetch);
			$row		 = mysql_fetch_assoc($resfetch);
			$tme_city 	 = $row['city'];
		}
		return $tme_city;
	}
	
	/* Added by Neelam @28/02/2013 */
	public function fetchUserName($userid, $module_type)
	{
		$username = '';
		if(!empty($module_type) && ($module_type == 'tme' || $module_type == 'tme90'))
		{
			if(!empty($userid))
			{
				$sql = "SELECT empName AS username FROM mktgEmpMaster WHERE mktempcode = '".$userid."'";
				$res = $this -> conn_local->query_sql($sql);
				if(mysql_num_rows($res) < 1) // if userid not found in mktgEmpMaster then fetch it from emplogin table
				{
					$sql = "SELECT CONCAT_WS(' ',empFName,empLName) AS username FROM db_iro.emplogin WHERE empCode = '".$userid."'";
					$res = $this -> conn_local->query_sql($sql);
				}
				$row = mysql_fetch_assoc($res);
				$username = $row['username'];
			}
		}
		else if(!empty($module_type) && $module_type=='cse')
		{
			if(!empty($userid))
			{
				$sql = "SELECT CONCAT_WS(' ',empFName,empLName) AS username FROM db_iro.emplogin WHERE empCode = '".$userid."'";
				$res = $this -> conn_local->query_sql($sql);
				$row = mysql_fetch_assoc($res);
				$username = $row['username'];
			}
		}
		return $username;
	}
}

function make_logs($str)
{
	$fn	= APP_PATH."/logs/log_error/tme_process_data_correction_".date("Y-m-d").".html";
	$fp = fopen($fn,'a+') or die("can't open file");
	fwrite($fp, $str);
	fclose($fp);
}
?>
