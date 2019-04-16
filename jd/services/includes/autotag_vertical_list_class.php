<?php
class autotag_vertical_list_class extends DB
{
	var $conn_idc    = null;
	var $params  		= null;
	var $vertical_data_arr = array();
	
	function __construct($params)
	{
		$action 		= trim($params['action']);		
		if(trim($action)==''){
            $message = "Action is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
		$this->action  		= $action;		
		$this->setServers();
	}
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
		$conn_city 		= 'remote'; // no need of data_city as idc connection needed here
		$this->conn_idc = $db[$conn_city]['idc']['master'];
		$this->vertical_data_arr = $this->getVerticalDetails();
	}
	function getVerticalDetails()
	{
		$vertical_data = array();
		$sqlAllVerticals = "SELECT vertical_name,vertical_abbr,display_product_flag,type_flag_value FROM ".DB_ONLINE1.".tbl_auto_tagging_process_verticals WHERE active_flag =1";
		$resAllVerticals 	= parent::execQuery($sqlAllVerticals, $this->conn_idc);
		if($resAllVerticals && mysql_num_rows($resAllVerticals)>0)
		{
			while($row_all_vertilcals = mysql_fetch_assoc($resAllVerticals))
			{
				$vertical_name 			= trim($row_all_vertilcals['vertical_name']);
				$vertical_abbr 			= trim($row_all_vertilcals['vertical_abbr']);
				$display_product_flag 	= trim($row_all_vertilcals['display_product_flag']);
				$type_flag 				= trim($row_all_vertilcals['type_flag_value']);
				$vertical_data[$vertical_abbr]['type_flag'] = $type_flag;
				$vertical_data[$vertical_abbr]['vname'] 	= $vertical_name;
				$vertical_data[$vertical_abbr]['vabbr'] 	= strtolower($vertical_abbr);
			}
		}
		return $vertical_data;
	}
	function getVerticalList()
	{
		$result_msg_arr['data'] 		 = $this->vertical_data_arr;		
		$result_msg_arr['error']['code'] = 0;
		$result_msg_arr['error']['msg']  = "Success";
		return $result_msg_arr;
	}
	function getVerticalRules($vertical_abbr)
	{
		/*---- Contract Level Untag Rules ---*/
		$untag_rules_arr = array();
		$sqlFetchUntagRules = "SELECT untag_rules FROM ".DB_ONLINE1.".tbl_contract_level_untag_rules_original WHERE untag_rules !=''";
		$resFetchUntagRules 	= parent::execQuery($sqlFetchUntagRules, $this->conn_idc);
		if($resFetchUntagRules && mysql_num_rows($resFetchUntagRules))
		{
			$row_untag_rules = mysql_fetch_assoc($resFetchUntagRules);
			$untag_rules_str = trim($row_untag_rules['untag_rules']);
			$untag_rules_arr = json_decode($untag_rules_str,true);
		}
		$untag_rules_info_arr = array();
		if(count($untag_rules_arr)>0)
		{
			$sqlExistingUntagRules = "SELECT untag_rules FROM ".DB_ONLINE1.".tbl_contract_level_untag_rules WHERE untag_rules !=''";
			$resExistingUntagRules 	= parent::execQuery($sqlExistingUntagRules, $this->conn_idc);
			if($resExistingUntagRules && mysql_num_rows($resExistingUntagRules))
			{
				$row_exist_untag_rules = mysql_fetch_assoc($resExistingUntagRules);
				$exist_untag_rules_str = trim($row_exist_untag_rules['untag_rules']);
				$exist_untag_rules_arr = json_decode($exist_untag_rules_str,true);
			}
			foreach($untag_rules_arr as $untag_rule)
			{
				if(in_array($untag_rule,$exist_untag_rules_arr))
				{
					$untag_rules_info_arr[$untag_rule] = 'on';
				}
				else
				{
					$untag_rules_info_arr[$untag_rule] = 'off';
				}
			}
		}
		
		/*---- Ignore Keyword ---*/
		
		$ignore_keyword_arr = array();
		$sqlVerticalIgnoreKeyword = "SELECT ignore_keyword FROM ".DB_ONLINE1.".tbl_vertical_ignore_keyword_original WHERE vertical_abbr = '".$vertical_abbr."' AND ignore_keyword!=''";
		$resVerticalIgnoreKeyword 	= parent::execQuery($sqlVerticalIgnoreKeyword, $this->conn_idc);
		if($resVerticalIgnoreKeyword && mysql_num_rows($resVerticalIgnoreKeyword))
		{
			$row_ignore_keyword = mysql_fetch_assoc($resVerticalIgnoreKeyword);
			$ignore_keyword_str = trim($row_ignore_keyword['ignore_keyword']);
			$ignore_keyword_arr = json_decode($ignore_keyword_str,true);
		}
		
		$ignore_keyword_info_arr = array();
		if(count($ignore_keyword_arr)>0)
		{
			$sqlExistingIgnoreKeyword = "SELECT ignore_keyword FROM ".DB_ONLINE1.".tbl_vertical_ignore_keyword WHERE vertical_abbr = '".$vertical_abbr."' AND ignore_keyword!=''";
			$resExistingIgnoreKeyword 	= parent::execQuery($sqlExistingIgnoreKeyword, $this->conn_idc);
			if($resExistingIgnoreKeyword && mysql_num_rows($resExistingIgnoreKeyword))
			{
				$row_exist_ignore_keyword = mysql_fetch_assoc($resExistingIgnoreKeyword);
				$exist_ignore_keyword_str = trim($row_exist_ignore_keyword['ignore_keyword']);
				$exist_ignore_keyword_arr = json_decode($exist_ignore_keyword_str,true);
			}
			foreach($ignore_keyword_arr as $ignore_keyword)
			{
				if(in_array($ignore_keyword,$exist_ignore_keyword_arr))
				{
					$ignore_keyword_info_arr[$ignore_keyword] = 'on';
				}
				else
				{
					$ignore_keyword_info_arr[$ignore_keyword] = 'off';
				}
			}
		}
		
		/*---- Auto Tagging Rules ---*/
		
		
		$contract_type_arr = array();
		$mand_conditions_arr = array();
		$sqlVerticalTaggingRules = "SELECT contract_type,mand_conditions FROM ".DB_ONLINE1.".tbl_auto_tagging_vertical_rules_original WHERE vertical_abbr = '".$vertical_abbr."'";
		$resVerticalTaggingRules 	= parent::execQuery($sqlVerticalTaggingRules, $this->conn_idc);
		if($resVerticalTaggingRules && mysql_num_rows($resVerticalTaggingRules))
		{
			$row_vertical_tagging_rules = mysql_fetch_assoc($resVerticalTaggingRules);
			$contract_type_str = trim($row_vertical_tagging_rules['contract_type']);
			$contract_type_arr = json_decode($contract_type_str,true);
			
			$mand_conditions_str = trim($row_vertical_tagging_rules['mand_conditions']);
			$mand_conditions_arr = json_decode($mand_conditions_str,true);
		}
		
		$sqlExistingTaggingRules = "SELECT contract_type,mand_conditions FROM ".DB_ONLINE1.".tbl_auto_tagging_vertical_rules WHERE vertical_abbr = '".$vertical_abbr."'";
		$resExistingTaggingRules = parent::execQuery($sqlExistingTaggingRules, $this->conn_idc);
		if($resExistingTaggingRules && mysql_num_rows($resExistingTaggingRules))
		{
			$row_exist_tagging_rules = mysql_fetch_assoc($resExistingTaggingRules);
			$exist_contract_type_str = trim($row_exist_tagging_rules['contract_type']);
			$exist_contract_type_arr = json_decode($exist_contract_type_str,true);
			
			$exist_mand_cond_str = trim($row_exist_tagging_rules['mand_conditions']);
			$exist_mand_cond_arr = json_decode($exist_mand_cond_str,true);
		}
		
		/*----- Contract Type */
		$contract_type_info_arr = array();
		if(count($contract_type_arr)>0)
		{
			
			foreach($contract_type_arr as $contract_type)
			{
				if(in_array($contract_type,$exist_contract_type_arr))
				{
					$contract_type_info_arr[$contract_type] = 'on';
				}
				else
				{
					$contract_type_info_arr[$contract_type] = 'off';
				}
			}
			
		}
		
		/*--- Mandatory Conditions */
		$mand_conditions_info_arr = array();
		if(count($mand_conditions_arr)>0)
		{
			foreach($mand_conditions_arr as $mand_conditions)
			{
				if($mand_conditions == 'CONTACTNO')
				{
					$contactno_arr = array("MOBILE OR LANDLINE","MOBILE","LANDLINE","MOBILE AND LANDLINE");
					foreach($contactno_arr as $contactno)
					{
						if(in_array($contactno,$exist_mand_cond_arr))
						{
							$contact_matched = 1;
							$mand_conditions_info_arr['contact'][$contactno] = 'on';
						}
						else
						{
							$mand_conditions_info_arr['contact'][$contactno] = 'off';
						}
					}
					if($contact_matched == 1)
					{
						$mand_conditions_info_arr['contact']['NONE'] = 'off';
					}
					else
					{
						$mand_conditions_info_arr['contact']['NONE'] = 'on';
					}
				}
				if($mand_conditions == 'HOP')
				{
					if(in_array($mand_conditions,$exist_mand_cond_arr))
					{
						$mand_conditions_info_arr['hop']['sel'] = 'on';
					}
					else
					{
						$mand_conditions_info_arr['hop']['sel'] = 'off';
					}
					
					
					
					$doc_default_val_arr = $this->getVerticalDefaultValue($vertical_abbr);
					$doc_default_hop_val = $doc_default_val_arr['HOP'];
					if($doc_default_hop_val)
					{
						$doc_default_hop_arr = json_decode($doc_default_hop_val,true);
						foreach($doc_default_hop_arr as $key => $value)
						{
							$tmng_arr 	= explode("-",$value);
							$from_tmng 	= $tmng_arr[0];
							$to_tmng 	= $tmng_arr[1];
							if($from_tmng == '00:00'){
								$from_tmng = "Closed";
							}
							if($to_tmng == '00:00'){
								$to_tmng = "Closed";
							}
							$mand_conditions_info_arr['hop']['daytm'][strtolower($key)]['from'] 	= $from_tmng;
							$mand_conditions_info_arr['hop']['daytm'][strtolower($key)]['to'] 	= $to_tmng;
						}
					}
					$mand_conditions_info_arr['hop']['days'] = array("mon","tue","wed","thu","fri","sat","sun");
					$mand_conditions_info_arr['hop']['time'] = $this->createTime('05:00','23:30');
				}
			}
		}
		
		$result_msg_arr['untag'] 			= $untag_rules_info_arr;
		$result_msg_arr['ignore'] 			= $ignore_keyword_info_arr;
		$result_msg_arr['contype'] 			= $contract_type_info_arr;
		$result_msg_arr['mandcondn'] 		= $mand_conditions_info_arr;
		$result_msg_arr['error']['code'] 	= 0;
		$result_msg_arr['error']['msg'] 	= "Success";
		return $result_msg_arr;
	}
	function getVerticalDefaultValue($vertical_abbr)
	{
		$vertical_default_val_arr = array();
		$sqlVerticalDefaultValue = "SELECT default_key,default_value FROM ".DB_ONLINE1.".tbl_vertical_default_value WHERE vertical_abbr = '".$vertical_abbr."'";
		$resVerticalDefaultValue = parent::execQuery($sqlVerticalDefaultValue, $this->conn_idc);
		if($resVerticalDefaultValue && mysql_num_rows($resVerticalDefaultValue)>0)
		{
			while($row_vertical_default_val = mysql_fetch_assoc($resVerticalDefaultValue))
			{
				$default_key 	= trim($row_vertical_default_val['default_key']);
				$default_value 	= trim($row_vertical_default_val['default_value']);
				$vertical_default_val_arr[$default_key] = $default_value;
			}
		}
		return $vertical_default_val_arr;
	}
	function updateDrRules($params){
		
		$drdata			= $params['drdata'];
		$ucode			= $params['ucode'];
		$uname			= $params['uname'];
		$ip_address		= $params['ip_address'];
		$vertical_abbr	= $params['vabbr'];
		$vertical_name	= $this->vertical_data_arr[$vertical_abbr]['vname'];
		
		$untagstr = trim($drdata['untag']);
		$untagarr = explode("|",$untagstr);
		$untagarr = $this->arrayProcess($untagarr);
		$untag_rules = json_encode($untagarr);
		
		$sqlUpdateUntagRules = "UPDATE ".DB_ONLINE1.".tbl_contract_level_untag_rules SET untag_rules = '".addslashes(stripcslashes($untag_rules))."'";
		$resUpdateUntagRules = parent::execQuery($sqlUpdateUntagRules, $this->conn_idc);
		
		if($resUpdateUntagRules){
			$sqlContractUntagRulesLog = "INSERT INTO ".DB_ONLINE1.".tbl_contract_level_untag_rules_log (untag_rules,ucode,uname,ip_address,insertdate) VALUES ('".addslashes(stripslashes($untag_rules))."','".$ucode."','".$uname."','".$ip_address."','".date("Y-m-d H:i:s")."')";
			$resContractUntagRulesLog = parent::execQuery($sqlContractUntagRulesLog, $this->conn_idc);
		}
		
		$ignorestr 		= trim($drdata['ignore']);
		$ignorearr 		= explode("|",$ignorestr);
		$ignorearr 		= $this->arrayProcess($ignorearr);
		$ignore_keyword = json_encode($ignorearr);
				
		$sqlInsrtDocIgnoreKeywords = "INSERT INTO ".DB_ONLINE1.".tbl_vertical_ignore_keyword SET
									  vertical_abbr  = '".$vertical_abbr."',
									  vertical_name  = '".$vertical_name."',
									  ignore_keyword = '".addslashes(stripslashes($ignore_keyword))."'
									
									  ON DUPLICATE KEY UPDATE
									  
									  vertical_name  = '".$vertical_name."',
									  ignore_keyword = '".addslashes(stripslashes($ignore_keyword))."'";
		$resInsrtDocIgnoreKeywords = parent::execQuery($sqlInsrtDocIgnoreKeywords, $this->conn_idc);
		if($resInsrtDocIgnoreKeywords){
			$sqlDocIgnoreKeywordsLog = "INSERT INTO ".DB_ONLINE1.".tbl_vertical_ignore_keyword_log (vertical_name,vertical_abbr,ignore_keyword,ucode,uname,ip_address,insertdate) VALUES ('".$vertical_name."','".$vertical_abbr."','".$ignore_keyword."','".$ucode."','".$uname."','".$ip_address."','".date("Y-m-d H:i:s")."')";
			$resDocIgnoreKeywordsLog = parent::execQuery($sqlDocIgnoreKeywordsLog, $this->conn_idc);
		}
		
		$mandcondarr = array();
		$manddataarr = array_map('trim',$drdata['mand']);
		if(count($manddataarr)>0){
			$mandkeysarr = array_keys($manddataarr);
			$mandkeysarr = array_map('strtolower', $mandkeysarr);
			if(in_array('contact',$mandkeysarr)){
				$mandcondarr[] = strtoupper($manddataarr['contact']);
			}
			if(in_array('hop',$mandkeysarr)){
				$mandcondarr[] = 'HOP';
			}
		}
		$mand_conditions = json_encode($mandcondarr);
		
		$contypestr 	= trim($drdata['contype']);
		$contypearr 	= explode("|",$contypestr);
		$contypearr 	= $this->arrayProcess($contypearr);
		$contract_type 	= json_encode($contypearr);
		
		$sqlDocTaggingRules = 	"INSERT INTO ".DB_ONLINE1.".tbl_auto_tagging_vertical_rules SET 
								 vertical_abbr  	= '".$vertical_abbr."',
								 vertical_name  	= '".$vertical_name."',
								 mand_conditions 	= '".addslashes(stripcslashes($mand_conditions))."',
								 contract_type		= '".addslashes(stripcslashes($contract_type))."'
									
								 ON DUPLICATE KEY UPDATE
								 vertical_name  	= '".$vertical_name."',
								 mand_conditions 	= '".addslashes(stripcslashes($mand_conditions))."',
								 contract_type		= '".addslashes(stripcslashes($contract_type))."'";
		$resDocTaggingRules = parent::execQuery($sqlDocTaggingRules, $this->conn_idc);
		
		if($resDocTaggingRules){
			$sqlDocTaggingRulesLog = "INSERT INTO ".DB_ONLINE1.".tbl_auto_tagging_vertical_rules_log SET 
									   vertical_abbr  	= '".$vertical_abbr."',
									   vertical_name  	= '".$vertical_name."',
									   mand_conditions 	= '".$mand_conditions."',
									   contract_type 	= '".$contract_type."',
									   ucode 			= '".$ucode."',
									   uname 			= '".$uname."',
									   ip_address 		= '".$ip_address."',
									   insertdate 		= '".date("Y-m-d H:i:s")."'";
			$resDocTaggingRulesLog = parent::execQuery($sqlDocTaggingRulesLog, $this->conn_idc);
		}
		
		
		$doc_hop_arr = array();
		$days_arr = array("mon","tue","wed","thu","fri","sat","sun");
		foreach($days_arr as $day_val){
			$hopval = trim($drdata['hop'.$day_val]);
			if($hopval == 'Closed-Closed'){
				$hopval = '00:00-00:00';
			}
			$doc_hop_arr[strtoupper($day_val)] = $hopval;
		}
		if(count($doc_hop_arr)>0){
			$doc_hop_default_val = json_encode($doc_hop_arr);
			$sqlUpdateDocHopDefaultVal = "UPDATE ".DB_ONLINE1.".tbl_vertical_default_value SET default_value = '".addslashes(stripcslashes($doc_hop_default_val))."' WHERE vertical_abbr = '".$vertical_abbr."' AND default_key = 'HOP'";
			$resUpdateDocHopDefaultVal = parent::execQuery($sqlUpdateDocHopDefaultVal, $this->conn_idc);
			if($resUpdateDocHopDefaultVal){
				$sqlDocHopDefaultValLog = "INSERT INTO ".DB_ONLINE1.".tbl_vertical_default_value_log (vertical_name,vertical_abbr,default_key,default_value,ucode,uname,ip_address,insertdate) VALUES ('".$vertical_name."','".$vertical_abbr."','HOP','".addslashes(stripcslashes($doc_hop_default_val))."','".$ucode."','".$uname."','".$ip_address."','".date("Y-m-d H:i:s")."')";
				$resDocHopDefaultValLog = parent::execQuery($sqlDocHopDefaultValLog, $this->conn_idc);
			}
		}
		
		$result_msg_arr['error']['code'] 	= 0;
		$result_msg_arr['error']['msg'] 	= "Success";
		return $result_msg_arr;
		
	}
	private function createTime($start,$end) {		
		$tStart = strtotime($start);
		$tEnd = strtotime($end);
		$tNow = $tStart;
		$timeArrShow	=	array();
		while($tNow <= $tEnd){
			$timeArrShow[]	=	date("H:i",$tNow);
			$tNow = strtotime('+30 minutes',$tNow);
		}
		return $timeArrShow;
	}
	private function arrayProcess($arr){
		$returnArr = array();
		if(count($arr)>0){
			$returnArr = array_merge(array_filter(array_unique($arr)));
		}
		return $returnArr;
	}
	private function sendDieMessage($msg){
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
}
?>
