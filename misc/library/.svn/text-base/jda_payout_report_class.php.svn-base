<?php
class dataCorrectionPayout
	{
		var $tme_pricing_fields_values;		
		var $tme_non_mendatory_fields;
		
		function __construct($dbarr)
		{
			$this->tme_pricing_fields_values = array("companyName" => "1", "address" => "1", "landmark" => "1", "contact_person" => "1", "email" => "1", "working_time" => "1");		
			$this->tme_non_mendatory_fields = array("contact_person", "email", "working_time");
			$this->conn_iro   = new DB($dbarr['DB_IRO']);
			$this->conn_local = new DB($dbarr['LOCAL']);
		}

		public function generateDataCorrectionPayout($from_date,$to_date,$city,$module_type='tme',$cron='')
		{
			if(!empty($from_date) && !empty($to_date) && !empty($city))
			{
				$auditCalc = $this->dataCorrectionAuditCalculation($from_date,$to_date,$city,$module_type,$cron);
				
				if($auditCalc == '1'){
					return true;
				}
				return false;
			}
			return false;
		}
		
		public function dataCorrectionAuditCalculation($from_date,$to_date,$city,$module_type,$cron)
		{
			if(!empty($from_date) && !empty($to_date) && !empty($city) && !empty($module_type))
			{
				$audit_status = "";				
				if($module_type == 'jda')
				{
					$audit_status =  " AND is_audited='1' ";
				}
				
				$insertSql = $selectSql = $from = $where = "";
							
				$insertSql = "REPLACE INTO data_correction_payout (userid,username,audit_data_count,total_fixed_fields_audited,total_valid_fixed_fields,total_invalid_fixed_fields,per_of_fields_validated,valid_total_pay,invalid_total_pay,total_categories_audited,total_valid_categories,total_invalid_categories,per_of_categories_validated,category_penalty,category_pay,category_total_pay,total_landline_audited,total_valid_landline,total_invalid_landline,per_of_landline_validated,landline_penalty,landline_pay,landline_total_pay,total_mobile_audited,total_valid_mobile,total_invalid_mobile,per_of_mobile_validated,mobile_penalty,mobile_pay,mobile_total_pay,total_earned,batch_start_date,batch_end_date,city,lastupdated,module_type) ";
				
				$where = " WHERE date(pl.entered_date) >= '".$from_date."' AND date(pl.entered_date) <= '".$to_date."' AND pl.editedby_id != '' ".$audit_status." AND module_type='".$module_type."' GROUP BY pl.editedby_id ORDER BY pl.editedby_id ";

				if($module_type=='jda')
				{
					$from = "FROM tbl_data_correction_payout_log pl join mktgEmpMaster AS me on pl.editedby_id = me.mktempcode join tme_jds.tbl_companymaster_generalinfo_shadow as dc_tme ON dc_tme.parentid = pl.parentid";					
					define(MAX_CAPPED_AMT,0); 
				}				
				
				$selectSql .="SELECT pl.editedby_id AS userid, me.empName AS username, COUNT(1) as audit_data_count, SUM(IF(valid_field_names != '',length(valid_field_names) - length(replace(valid_field_names,',',''))+1,0) + IF(invalid_field_names != '',length(invalid_field_names) - length(replace(invalid_field_names,',',''))+1,0)) AS total_fixed_fields_audited, SUM(IF(valid_field_names != '',length(valid_field_names) - length(replace(valid_field_names,',',''))+1,0)) AS total_valid_fixed_fields, SUM(IF(invalid_field_names != '',length(invalid_field_names) - length(replace(invalid_field_names,',',''))+1,0)) AS total_invalid_fixed_fields, ROUND((SUM(IF(valid_field_names != '',length(valid_field_names) - length(replace(valid_field_names,',',''))+1,0)) * 100) / SUM(IF(valid_field_names != '',length(valid_field_names) - length(replace(valid_field_names,',',''))+1,0) + IF(invalid_field_names != '',length(invalid_field_names) - length(replace(invalid_field_names,',',''))+1,0)),2) AS per_of_fields_validated, SUM(total_valid_field_values) AS valid_total_pay, SUM(total_invalid_field_values) AS invalid_total_pay, SUM(IF(valid_catnames != '',(length(replace(valid_catnames,'|~|',',')) - length(replace(replace(valid_catnames,'|~|',','),',','')))+1,0) + IF(invalid_catnames != '',(length(replace(invalid_catnames,'|~|',',')) - length(replace(replace(invalid_catnames,'|~|',','),',','')))+1,0)) AS total_categories_audited, SUM(IF(valid_catnames != '',(length(replace(valid_catnames,'|~|',',')) - length(replace(replace(valid_catnames,'|~|',','),',','')))+1,0)) AS total_valid_categories, SUM(IF(invalid_catnames != '',(length(replace(invalid_catnames,'|~|',',')) - length(replace(replace(invalid_catnames,'|~|',','),',','')))+1,0)) AS total_invalid_categories, ROUND((SUM(IF(valid_catnames != '',(length(replace(valid_catnames,'|~|',',')) - length(replace(replace(valid_catnames,'|~|',','),',','')))+1,0)) * 100) / SUM(IF(valid_catnames != '',(length(replace(valid_catnames,'|~|',',')) - length(replace(replace(valid_catnames,'|~|',','),',','')))+1,0) + IF(invalid_catnames != '',(length(replace(invalid_catnames,'|~|',',')) - length(replace(replace(invalid_catnames,'|~|',','),',','')))+1,0)),2) AS per_of_categories_validated,SUM(total_invalid_catvalues) AS category_penalty,SUM(total_valid_catvalues) AS category_pay,SUM(total_cat_pay) AS category_total_pay, SUM(IF(valid_landline != '',length(valid_landline) - length(replace(valid_landline,',',''))+1,0) + IF(invalid_landline != '',length(invalid_landline) - length(replace(invalid_landline,',',''))+1,0)) AS total_landline_audited, SUM(IF(valid_landline != '',length(valid_landline) - length(replace(valid_landline,',',''))+1,0)) AS total_valid_landline, SUM(IF(invalid_landline != '',length(invalid_landline) - length(replace(invalid_landline,',',''))+1,0)) AS total_invalid_landline, ROUND((SUM(IF(valid_landline != '',length(valid_landline) - length(replace(valid_landline,',',''))+1,0)) * 100) / SUM(IF(valid_landline != '',length(valid_landline) - length(replace(valid_landline,',',''))+1,0) + IF(invalid_landline != '',length(invalid_landline) - length(replace(invalid_landline,',',''))+1,0)),2) AS per_of_landline_validated,SUM(total_invalid_lanvalues) AS landline_penalty,SUM(total_valid_lanvalues) AS landline_pay,SUM(total_landline_pay) AS landline_total_pay, SUM(IF(valid_mobile != '',length(valid_mobile) - length(replace(valid_mobile,',',''))+1,0) + IF(invalid_mobnames != '',length(invalid_mobnames) - length(replace(invalid_mobnames,',',''))+1,0)) AS total_mobile_audited, SUM(IF(valid_mobile != '',length(valid_mobile) - length(replace(valid_mobile,',',''))+1,0)) AS total_valid_mobile, SUM(IF(invalid_mobnames != '',length(invalid_mobnames) - length(replace(invalid_mobnames,',',''))+1,0)) AS total_invalid_mobile, ROUND((SUM(IF(valid_mobile != '',length(valid_mobile) - length(replace(valid_mobile,',',''))+1,0)) * 100) / SUM(IF(valid_mobile != '',length(valid_mobile) - length(replace(valid_mobile,',',''))+1,0) + IF(invalid_mobnames != '',length(invalid_mobnames) - length(replace(invalid_mobnames,',',''))+1,0)),2) AS per_of_mobile_validated,SUM(total_invalid_mobvalues) AS mobile_penalty,SUM(total_valid_mobvalues) AS mobile_pay, SUM(total_mobile_pay) AS mobile_total_pay, ((SUM(total_valid_field_values) + SUM(total_cat_pay) + SUM(total_landline_pay) + SUM(total_mobile_pay)) - SUM(total_invalid_field_values)) AS total_earned, '".$from_date." 00:00:00', '".$to_date." 23:59:59', '".$city."', NOW(), '".$module_type."' ";	

				$selectSql .= $from .= $where;
				$insertSql .= $selectSql;
				$execQuery 	= $this->conn_local->query_sql($insertSql);
				return '1';
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
				
				if(!empty($userid))
					$where .= " AND userid='".$userid."'";
					
				$sql = "SELECT * FROM data_correction_payout ".$where." ORDER BY final_payout DESC";
				$execQuery = $this->conn_local->query_sql($sql);
				$data = $this->fetchAll($execQuery);
				
				if(!empty($data) && is_array($data) && count($data) > 0)
					return $data;
				else
					return '0';
			}
			else
				return '0';
		}
		
		
		public function datacorrectionPayoutHtml($from_date,$to_date,$city,$module_type)
		{
			if(!empty($from_date) && !empty($to_date) && !empty($city) && !empty($module_type))
			{
				$datacorrectionPayoutArr = $this->getDatacorrectionPayoutDetails($from_date,$to_date,$city,'',$module_type);
				
				if(!empty($datacorrectionPayoutArr) && is_array($datacorrectionPayoutArr) && count($datacorrectionPayoutArr) > 0)
				{
					if($module_type=='tme')
						$payoutHeading = "TME Data Correction Payout List";
					else if($module_type=='cse')
						$payoutHeading = "CSE Data Correction Payout List";
					else if($module_type=='jda')
						$payoutHeading = "JDA Payout List";
						
					$dataCnt = count($datacorrectionPayoutArr);
					$output = "";
					$output .= "<table name='tbldatacorrectionPayout' align='center' width='100%' border='0'>";
					if($module_type=='tme'){
						$output .= "<tr><td colspan='34' align='center' class='tableTH'>$payoutHeading</td><td align='center'><input type='submit' name='crbtn' value='Make Live' onClick='return submt();'></td></tr>";
					}else{
						$output .= "<tr><td colspan='35' align='center' class='tableTH'>$payoutHeading</td><td align='center'></td></tr>";
					}
					$output .= "<tr>";
					$output .= "<th width='5%' align='left' class='tableTH'>User Id</th>";
					$output .= "<th width='10%' align='left' class='tableTH'>User Name</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Total Audited</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Total Fixed Fields Audited</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Total Valid Fixed Fields</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Total Invalid Fixed Fields</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Fixed Fields Audit %</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Total Valid Data Pay</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Total Penalty</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Total Categories Audited</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Total Valid Categories</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Total Invalid Categories</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Category Audit %</th>";
					$output .= "<th width='10%' align='right' class='tableTH'>Category Penalty</th>";
					$output .= "<th width='10%' align='right' class='tableTH'>Category Pay</th>";
					$output .= "<th width='10%' align='right' class='tableTH'>Total Category Pay</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Total Landline Audited</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Total Valid Landline</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Total Invalid Landline</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Landline Audit %</th>";
					$output .= "<th width='10%' align='right' class='tableTH'>Landline Penalty</th>";
					$output .= "<th width='10%' align='right' class='tableTH'>Landline Pay</th>";
					$output .= "<th width='10%' align='right' class='tableTH'>Total Landline Pay</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Total Mobile Audited</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Total Valid Mobile</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Total Invalid Mobile</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Mobile Audit %</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Mobile Penalty</th>";
					$output .= "<th width='10%' align='right' class='tableTH'>Mobile Pay</th>";
					$output .= "<th width='10%' align='right' class='tableTH'>Total Mobile Pay</th>";
					$output .= "<th width='5%' align='right' class='tableTH'>Final payout</th>";
					$output .= "</tr>";
					//$output .= "</table>";
					//$output .= "<div style='overflow-y:scroll;height:265px;'>";
					//$output .= "<table width='100%' border='0' cellspacing='0' cellpadding='1'>";
					$dataCountArr = array();
					for($r=0;$r<$dataCnt;$r++)
					{
						$userid = $datacorrectionPayoutArr[$r]['userid'];
						$username = (!empty($datacorrectionPayoutArr[$r]['username'])) ? $datacorrectionPayoutArr[$r]['username'] : "-";
						$total_audited = $datacorrectionPayoutArr[$r]['audit_data_count'];
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
						
						$output .= "<tr>";
						$output .= "<td align='left' width='5%' class='tableTD'>$userid</td>";
						$output .= "<td align='left' width='10%' class='tableTD'>$username</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$total_audited</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$total_fixed_audited</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$total_fixed_valid</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$total_fixed_invalid</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$per_of_fields_validated</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$total_valid_pay</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$total_penalty</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$total_cat_audited</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$total_cat_valid</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$total_cat_invalid</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$per_cat_validated</td>";
						$output .= "<td align='right' width='10%' class='tableTD'>$category_penalty</td>";
						$output .= "<td align='right' width='10%' class='tableTD'>$category_pay</td>";
						$output .= "<td align='right' width='10%' class='tableTD'>$total_category_pay</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$total_lan_audited</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$total_lan_valid</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$total_lan_invalid</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$per_lan_validated</td>";
						$output .= "<td align='right' width='10%' class='tableTD'>$landline_penalty</td>";
						$output .= "<td align='right' width='10%' class='tableTD'>$landline_pay</td>";
						$output .= "<td align='right' width='10%' class='tableTD'>$total_tele_pay</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$total_mob_audited</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$total_mob_valid</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$total_mob_invalid</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$per_mob_validated</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$mobile_penalty</td>";
						$output .= "<td align='right' width='10%' class='tableTD'>$mobile_pay</td>";
						$output .= "<td align='right' width='10%' class='tableTD'>$total_mobile_pay</td>";
						$output .= "<td align='right' width='5%' class='tableTD'>$total_earned</td>";
						$output .= "</tr>";
					}
					$output .= "</table>";
					//$output .= "</div>";
					
					return $output;
				}
				else
					return '0';
			}
			else
				return '0';
		}
			
		public function fetchAll($query_result)
		{
			if(!empty($query_result))
			{
				$records = array();
				while ($data = mysql_fetch_array($query_result))
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
	}
?>