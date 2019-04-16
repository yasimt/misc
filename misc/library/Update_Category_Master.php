<?php
session_start();
require_once("../common/Serverip.php");
require_once("../library/config.php");
include_once(APP_PATH."library/path.php");
global $dbarr;

class Update_Category_Master
{
	public function __construct($dbarr,$parentid)
	{
	
		$this -> conn_fnc  = new DB($dbarr['DB_FNC']);/* connection object to finance server*/
		
		$this -> conn_iro  = new DB($dbarr['DB_IRO']);/*connection object to de/cs server*/
		
		$this -> conn_local= new DB($dbarr['LOCAL']);/*connection object to tme database of decs server server*/
		
		$this -> conn_auto = new DB($dbarr['DB_AUTOSUGGEST']);/*connection object to autosuggest server*/
		
		//$this -> conn_amb = new DB($dbarr['DB_AMB']);/*connection object to tme database of decs server server*/
		
		$this -> parentid   = $parentid;/*initialize paretnid  with p from session */
//		$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid," constructor called for parentid ----> ".$this -> parentid);
		
	}
	function trim_value(&$value) 
	{ 
		$value = trim($value,'/'); 
	}


	function GetContractsCategory()
	{
		$sql_catlineage = "SELECT catidlineage,catidlineage_nonpaid FROM tbl_companymaster_extradetails where parentid='".$this -> parentid."'";
		$res_catlineage = $this -> conn_iro -> query_sql($sql_catlineage);
		if($res_catlineage && mysql_num_rows($res_catlineage))
		{
			$row_catlineage = mysql_fetch_assoc($res_catlineage);
			$catlinge = $row_catlineage['catidlineage'];
			
			if($row_catlineage['catidlineage_nonpaid'] != '')
			{
				$catlinge .= ",".$row_catlineage['catidlineage_nonpaid'];
			}
			
			$catlineage_arr = explode('/,/',$catlinge);
			array_walk($catlineage_arr, array($this,'trim_value'));
			$catlineage_arr = array_unique($catlineage_arr);
			return $catlineage_arr;
		}
		else
		{
			return array();
		}
	}
	
	function GetActiveStatus()
	{
		$sql_active = "SELECT freeze,mask FROM tbl_companymaster_extradetails where parentid='".$this -> parentid."'";
		$res_active = $this -> conn_iro -> query_sql($sql_active);
		if($res_active && mysql_num_rows($res_active))
		{
			$row_active = mysql_fetch_assoc($res_active);
			if(!$row_active['freeze'] && !$row_active['mask'])
			{
				$active_flag = 1;
			}
			else
			{
				$active_flag = 0;
			}
			
			return $active_flag;
		}
	}
	
	function Upt_Cat_Master($new_cat_arr = array(),$new_active_flag,$contract_type)
	{
		$old_cat_arr = $this -> GetContractsCategory();
		$old_active_flag  = $this -> GetActiveStatus();
		$categories_count_to_upgrade = array();
		$categories_count_to_degrade = array();
		$array_diff_new_and_old = array();
		$array_diff_new_and_old = array_diff($new_cat_arr,$old_cat_arr);
		if(count($new_cat_arr) || ($new_active_flag!=$old_active_flag))
		{ 
			if(count($new_cat_arr) && ($new_active_flag!=$old_active_flag))
			{
				if($new_active_flag)
				{ 
					$categories_count_to_degrade = array_diff($old_cat_arr,$new_cat_arr);
					$old_new_categories_arr = array_merge($new_cat_arr,$old_cat_arr);
					$categories_count_to_upgrade = array_diff($old_new_categories_arr,$categories_count_to_degrade);				
					
					$categories_count_to_degrade = array_unique($categories_count_to_degrade);
					$categories_count_to_degrade = array_filter($categories_count_to_degrade);
				
					$categories_count_to_upgrade = array_unique($categories_count_to_upgrade);
					$categories_count_to_upgrade = array_filter($categories_count_to_upgrade);
				
				}
				elseif(count($old_cat_arr))
				{ 
					$categories_count_to_degrade = $old_cat_arr;
					$categories_count_to_degrade = array_filter($categories_count_to_degrade);
				}
				
			}
			elseif(count($new_cat_arr) && (count($new_cat_arr)!=count($old_cat_arr) || count($array_diff_new_and_old)>0 ) && ($new_active_flag == $old_active_flag) && $new_active_flag)
			{
				$categories_count_to_degrade = array_diff($old_cat_arr,$new_cat_arr);
				$categories_count_to_upgrade = array_diff($new_cat_arr,$old_cat_arr);
				
				$categories_count_to_degrade = array_unique($categories_count_to_degrade);
				$categories_count_to_degrade = array_filter($categories_count_to_degrade);
				
				$categories_count_to_upgrade = array_unique($categories_count_to_upgrade);
				$categories_count_to_upgrade = array_filter($categories_count_to_upgrade);
			}

			$new_categories_to_upgrade = array();
			$new_categories_to_degrade = array();
			$logpath = APP_PATH."logs/Update_company_count/";
						
			if((count($categories_count_to_degrade)>0) || (count($categories_count_to_upgrade)>0))
			{
				$new_categories_to_upgrade = $categories_count_to_upgrade;
				$new_categories_to_degrade = $categories_count_to_degrade;
				$this->update_company_count_log($logpath,$contract_type,$new_categories_to_upgrade,$new_categories_to_degrade,$this -> parentid);
			}
			if(count($categories_count_to_degrade)>0)
			{
				$categories_count_to_degrade = array_unique($categories_count_to_degrade);
				$categories_count_to_degrade = array_filter($categories_count_to_degrade);
				$categories_to_degrade 		 = implode(',',$categories_count_to_degrade);
				if($contract_type == 1)
				{
					$sql_degrade = "UPDATE tbl_categorymaster_generalinfo SET paid_clients=if(paid_clients>0,paid_clients-1,0) WHERE catid IN (".$categories_to_degrade.")";
				}
				if($contract_type == 0)
				{
					$sql_degrade = "UPDATE tbl_categorymaster_generalinfo SET nonpaid_clients=if(nonpaid_clients>0,nonpaid_clients-1,0) WHERE catid IN (".$categories_to_degrade.")";
				}
				$res_degrade = $this -> conn_local -> query_sql($sql_degrade);
			}
			if(count($categories_count_to_upgrade)>0)
			{
				$categories_count_to_upgrade = array_unique($categories_count_to_upgrade);
				$categories_count_to_upgrade = array_filter($categories_count_to_upgrade);
				$categories_to_upgrade 		 = implode(',',$categories_count_to_upgrade);
				if($contract_type == 1) //paid
				{
					$sql_upgrade = "UPDATE tbl_categorymaster_generalinfo SET paid_clients=if(paid_clients>0,paid_clients+1,1) WHERE catid IN (".$categories_to_upgrade.")";
				}
				else if($contract_type == 0)
				{
					$sql_upgrade = "UPDATE tbl_categorymaster_generalinfo SET nonpaid_clients=if(nonpaid_clients>0,nonpaid_clients+1,1) WHERE catid IN (".$categories_to_upgrade.")";
				}
				$res_upgrade = $this -> conn_local -> query_sql($sql_upgrade);
			}
			
			
			$tot_catids_arr = array_merge($old_cat_arr,$new_cat_arr);
			if(count($tot_catids_arr)>0)
			{
				$tot_catids_arr = array_unique($tot_catids_arr);
				$tot_catids_arr = array_filter($tot_catids_arr);
				$tot_catids 	= implode(',',$tot_catids_arr);
				$sql_sel ="SELECT catid,nonpaid_clients, paid_clients FROM tbl_categorymaster_generalinfo WHERE catid IN (".$tot_catids.")";
				$res_sel = $this -> conn_local -> query_sql($sql_sel);
				if($res_sel && mysql_num_rows($res_sel))
				{
					$final_catid_arr = array();
					while($row_sel = mysql_fetch_assoc($res_sel))
					{
						$final_catid_arr[$row_sel['catid']]['company_count'] = $row_sel['nonpaid_clients'] + $row_sel['paid_clients'];
					}
				}
				
				if(count($final_catid_arr)>0)
				{
					foreach($final_catid_arr as $catid=>$value)
					{
						if($value['company_count']>0)
						{
							$display_flag = 1;
						}
						else
						{
							$display_flag = 0;
						}
						$old_display_flag = 0;
						$sql_old_disply_flg = "SELECT display_flag FROM tbl_catlineage_search WHERE catid='".$catid."' GROUP BY catid";
						$res_old_disply_flg = $this -> conn_local -> query_sql($sql_old_disply_flg);
						if($res_old_disply_flg && mysql_num_rows($res_old_disply_flg)>0)
						{
							$row_old_disply_flg = mysql_fetch_assoc($res_old_disply_flg);
							$old_display_flag = $row_old_disply_flg['display_flag'];
						}
						
						if($old_display_flag != $display_flag)
						{
							$this->category_display_flag_log($logpath,$catid,$this -> parentid,$old_display_flag,$display_flag);
						}
						$sql_upt_catlineage = "UPDATE tbl_catlineage_search set display_flag=".$display_flag." where catid='".$catid."'";
						$res_upt_catlineage = $this -> conn_local -> query_sql($sql_upt_catlineage);
						
						
						$sql_upt_catauto = "UPDATE tbl_autosuggest_category set display_flag=".$display_flag." where catid='".$catid."'";
						$res_upt_catauto = $this -> conn_auto -> query_sql($sql_upt_catauto);
					}
				}
			}
			
		}
	}
	 
	function update_company_count_log($logpath,$contact_type,$new_categories_to_upgrade,$new_categories_to_degrade,$parentid)
	{
		$all_catids_arr = array_merge($new_categories_to_upgrade,$new_categories_to_degrade);
		$all_catids_arr = array_filter($all_catids_arr);
		$all_catids_up_de = implode(',',$all_catids_arr);
		
		if($all_catids_up_de != '')
		{
			//Getting Old company count
			$sql_company_cnt = "SELECT catid,category_name,nonpaid_clients, paid_clients FROM tbl_categorymaster_generalinfo WHERE catid IN (".$all_catids_up_de.")";
			$res_company_cnt = $this -> conn_local -> query_sql($sql_company_cnt);
			$insert_string = '';
			if($res_company_cnt && mysql_num_rows($res_company_cnt))
			{
				$catid_arr_upgrade = array();
				$catid_arr_degrade = array();
				while($row_company_cnt = mysql_fetch_assoc($res_company_cnt))
				{
					$insert_string .="<tr align='center'>";
					$insert_string .= "<td>".$parentid."</td><td>".$row_company_cnt['catid']."</td>";
					
					if(in_array($row_company_cnt['catid'],$new_categories_to_upgrade))
					{
						if($contact_type == 1)
						{
							$insert_string .= "<td>-</td><td>-</td><td>".$row_company_cnt['paid_clients']."</td><td>".(($row_company_cnt['paid_clients'] > 0)? ($row_company_cnt['paid_clients']- 1) : 0)."</td>";
						}
						else if($contract_type == 0)
						{
							$insert_string .= "<td>".$row_company_cnt['nonpaid_clients']."</td><td>".(($row_company_cnt['nonpaid_clients'] > 0)? ($row_company_cnt['nonpaid_clients']- 1) : 0)."</td><td>-</td><td>-</td>";
						}
					}
					
					if(in_array($row_company_cnt['catid'],$new_categories_to_degrade))
					{	
						if($contact_type == 1)
						{
							$insert_string .= "<td>-</td><td>-</td><td>".$row_company_cnt['paid_clients']."</td><td>".(($row_company_cnt['paid_clients'] > 0)? ($row_company_cnt['paid_clients']- 1) : 0)."</td>";					
						}
						else if($contract_type == 0)
						{
							$insert_string .= "<td>".$row_company_cnt['nonpaid_clients']."</td><td>".(($row_company_cnt['nonpaid_clients'] > 0)? ($row_company_cnt['nonpaid_clients']- 1) : 0)."</td><td>-</td><td>-</td>";
						}
						$insert_string .="<td>".date('d/m/Y  H:i:s')."</td>";						
					}
					$insert_string .="</tr>";	
				}
			}
		}
		
		
		$log_msg=''; 
		// fetch directory for the file
		$pathToLog = dirname($logpath); 
		if (!file_exists($pathToLog)) {
			mkdir($pathToLog, 777, true);
		}
		$file_n=$logpath.date("d_m_Y").".html"; 
		$logFile = fopen($file_n, 'a');

		$log_msg.= "<table border=1 cellpadding='0' cellspacing='0' width='100%'>
							<tr valign='top'>
								<td  colspan='5' border:1px solid #669966' align='center'><b>UPGRADE / DEGRADE Company Count</b> </td>
							</tr>
							<tr valign='top'>
								<td border:1px solid #669966' align='center'><b>ParentId</b> </td>
								<td border:1px solid #669966' align='center'><b>Catid</b> </td>
								<td border:1px solid #669966' align='center'><b>Old_Nonpaid_count</b> </td>
								<td border:1px solid #669966' align='center'><b>New_Nonpaid_count</b> </td>
								<td border:1px solid #669966' align='center'><b>old_paid_count</b> </td>
								<td border:1px solid #669966' align='center'><b>New_paid_count</b> </td>
								<td border:1px solid #669966' align='center'><b>Updated Date</b> </td>
							</tr>";
		
		$log_msg .= $insert_string ;
		$log_msg.= "</table>";						
		
		fwrite($logFile, $log_msg);
		fclose($logFile);
	}
	
	function category_display_flag_log($logpath,$catid,$parentid,$old_display_flag,$display_flag)
	{
		
		$log_msg=''; 
		// fetch directory for the file
		$pathToLog = dirname($logpath); 
		if (!file_exists($pathToLog)) {
			mkdir($pathToLog, 777, true);
		}
		$file_n=$logpath."display_flag_".date("d_m_Y").".html"; 
		
		$logFile = fopen($file_n, 'a');

		$log_msg.= "<table border=1 cellpadding='0' cellspacing='0' width='100%'>
							<tr valign='top'>
								<td   border:1px solid #669966' align='center'><b>ParentId</b> </td>
								<td   border:1px solid #669966' align='center'><b>Catid</b> </td>
								<td   border:1px solid #669966' align='center'><b>Old</b> </td>
								<td   border:1px solid #669966' align='center'><b>New</b> </td>
								<td   border:1px solid #669966' align='center'><b>Updated Date</b> </td>
							</tr>";
							
		$log_msg .= "<tr><td>".$parentid."</td><td align='center'>".$catid."</td><td align='center'>".$old_display_flag."</td><td align='center'>".$display_flag."</td><td align='right'>".date("d/m/Y  H:i:s")."</td>";
		
		$log_msg.= "</tr></table>";						
		
		fwrite($logFile, $log_msg);
		fclose($logFile);
	}
}

/* This updation is integrated in compcatarea regen so there is no need to call this -- Pramesh/Raj
 * This process is commented where it is gettting called 
$parentid = $_GET['parentid']; 
$new_category_arr = array();
$new_category_arr = explode('|',$_GET['new_cat_array']);
$active_flag = $_GET['active_flag'];
$contract_type = $_GET['contract_type'];

$upt_cat_master = new Update_Category_Master($dbarr,$parentid);
$upt_cat_master->Upt_Cat_Master($new_category_arr,$active_flag,$contract_type);
*/
?>
