<?php
/*
Creation Date : 22nd Dec 2011
ALL GENIO COMMONS FUNCTIONS WILL BE PLACED HERE
*/

/*
Comments: This function returns sphinxid of the requested parentid
Variables : 1) $fun_sphinxid_parentid and 2) fun_sphinxid_parentid are defined as global in library\global_variables.php file
*/
require_once(APP_PATH."00_Payment_Rework/company_finance_class.php"); 

function getContractSphinxId($parentid) {


    GLOBAL $dbarr,$fun_sphinxid_parentid,$fun_var_sphinxid,$compmaster_obj;
	$sphinx_id = 0;
	//if (MAIN_CITY_MODULE == 'cs') {

		if ($fun_sphinxid_parentid == $parentid) return $fun_var_sphinxid;
		
		$conn_iro = new DB($dbarr['DB_IRO']);
		

		if(!isset($compmaster_obj)){
			$compmaster_obj = new companyMasterClass($conn_iro,"",$parentid);
		}
		$temparr		= array();
		$fieldstr		= '';
		$fieldstr 		= "sphinx_id";
		$tablename		= "tbl_companymaster_extradetails";
		$wherecond		= "parentid= '".$parentid."'";
		$temparr		= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

		$sphinxid_values= $temparr['data']['0'];
		$sphinxid_rows	= $temparr['numrows'];

		if ($temparr['numrows'] == 0) {

			$idgenerator_sphinxid_query = " SELECT sphinx_id FROM tbl_id_generator WHERE parentid = '" . $parentid . "' ";
			$sphinxid_res               = $conn_iro->query_sql($idgenerator_sphinxid_query);
		    $sphinxid_rows              = mysql_num_rows($sphinxid_res);
		    $sphinxid_values       		= mysql_fetch_assoc($sphinxid_res);
		}

		if ($sphinxid_rows) {
			$sphinx_id             = $sphinxid_values['sphinx_id'];
			$fun_sphinxid_parentid = $sphinx_id;
		}
	//}

    if (intval($sphinx_id) == 0) {

		echo '<h3>Sphinx Id not found for this parentid : ' . $parentid . ' </h3>';
		exit();

	}
	return $sphinx_id;

}

function get_company_data($sphinx_id) /* send second parameter ZERO when u need all the information and ONE when u need only genio variables i.e parentid, sphinzid, pincode, data_city */
{
	global $dbarr;
	$dataArr= array();

	$conn_iro= new DB($dbarr['DB_IRO']);
	$conn_national = new DB($dbarr['DB_NATIONAL']);
	$sql_generalinfo	= "SELECT pincode, parentid, data_city FROM tbl_companymaster_generalinfo_shadow WHERE sphinx_id='".$sphinx_id."'";
	$qry_generalinfo	= $conn_iro->query_sql($sql_generalinfo);
	if($qry_generalinfo)
	{
		if(mysql_num_rows($qry_generalinfo)>0) {
			$row_generalinfo=mysql_fetch_assoc($qry_generalinfo);

			$dataArr['sphinx_id']	= $sphinx_id;
			$dataArr['parentid']	= $row_generalinfo['parentid'];
			$dataArr['pincode']		= $row_generalinfo['pincode'];
			$dataArr['data_city']	= $row_generalinfo['data_city'];
		}
	}
	$dataArr['national']=0;
	
	
	$sql_national="SELECT count(parentid) AS counter FROM d_jds.tbl_national_listing_temp WHERE parentid='".$row_generalinfo['parentid']."'";	
	$qry_national=$conn_iro->query_sql($sql_national);
	
	/*$sql_national="select count(*) from tbl_companymaster_finance_national where parentid='".$row_generalinfo['parentid']."' and active_campaign<>0";	
	$qry_national=$conn_national->query_sql($sql_national);	*/
	
	
	
	
	if($qry_national)
	{
		$row_national= mysql_fetch_assoc($qry_national);
		if($row_national['counter']>0){
			$dataArr['national']=1;
		}
	}
	
	$sqlFlag = "select flags from tbl_companymaster_extradetails_shadow where parentid ='".$row_generalinfo['parentid']."'";
	$qryFlag = $conn_iro->query_sql($sqlFlag);
	$rowFlag = mysql_fetch_assoc($qryFlag);
	$flagCheck = $rowFlag['flags']&512;
	
	if($flagCheck == 512)
	{
		$dataArr['com'] = 1;
	}
	
	/*CHECKING PAID OR NONPAID */
	/*$sql_paid = "SELECT nonpaid FROM d_jds.tbl_temp_intermediate WHERE parentid = '".$row_generalinfo['parentid']."'";
	$qry_paid = $conn_iro->query_sql($sql_paid);
	if($qry_paid && mysql_num_rows($qry_paid)){
		$row_paid = mysql_fetch_assoc($qry_paid);
		$dataArr['nonpaid']  = $row_paid['nonpaid'];
	}*/
	/*CHECKING PAID OR NONPAID */
	
	$sql_paid = "SELECT nonpaid,c2c, hiddenCon, dotcom, exclusive,version,actmode FROM d_jds.tbl_temp_intermediate WHERE parentid = '".$row_generalinfo['parentid']."'";
	$qry_paid = $conn_iro->query_sql($sql_paid);
	if($qry_paid && mysql_num_rows($qry_paid)){
		$row_paid = mysql_fetch_assoc($qry_paid);
		$dataArr['nonpaid']  = $row_paid['nonpaid'];
		$dataArr['version']  = $row_paid['version'];
		$dataArr['c2c']  = $row_paid['c2c'];
		$dataArr['hiddenCon']  = $row_paid['hiddenCon'];
		$dataArr['com']  = (!isset($dataArr['com'])?$row_paid['dotcom']:$dataArr['com']);
		$dataArr['exclusive']  = $row_paid['exclusive'];
	}
	$paidexpiredarr= paidexpired($row_generalinfo['parentid']);
	
	$dataArr['paidexpired']= $paidexpiredarr['paidexpired'];
	
	if($row_paid['actmode'])
	$dataArr['pdg_contract']= pdgcontract($dataArr['parentid'], $dataArr['sphinx_id']);
	return $dataArr;
}

function paidexpired($parentid){
	GLOBAL $dbarr;
	
	$conn_finance = new DB($dbarr['FINANCE']);
	
	$sql_paid = "SELECT * FROM tbl_companymaster_finance 
				 WHERE parentid = '".$parentid."' 
				 AND active_flag =1 
				 AND active_campaign=1 
				 AND (expired=0 OR expired_on BETWEEN now() - INTERVAL 3 MONTH AND now())
				 AND balance > 0";
	$qry_paid = $conn_finance->query_sql($sql_paid);
	$row_paid = mysql_fetch_assoc($qry_paid); //10-9-2015 11:55 vinay has given query spark to kaustav

	
	$sql_db = "SELECT parentid,IF(IFNULL(mask,0) = 0 
			   AND IFNULL(freeze,0) = 0 AND SUM(IFNULL(balance,0)) > 0 
			   AND MIN(expired)=0,1,2)AS paid_flag,IF(MAX(expired_on) < DATE_SUB(CURDATE(),INTERVAL 3 MONTH),'Expired','Active' ) AS exp,MAX(expired_on) 
			   FROM ".DB_FINANCE.".tbl_companymaster_finance where parentid='".$parentid."'";
	$qry_db = $conn_finance->query_sql($sql_db);
	$row_db = mysql_fetch_assoc($qry_db);
	
	if($row_paid){
		$data['paid'] = 1;
		$data['nonpaid'] = 0;
		if(isset($row_db['paid_flag']) && isset($row_db['exp'])){
			if($row_db['paid_flag'] == 2 && $row_db['exp'] == "Expired"){
				$data['paidexpired'] = 1;
			}else{
				$data['paidexpired'] = 0;
			}
		}
	}
	else{
		$data['paid'] = 0;
		if(isset($row_db['paid_flag']) && isset($row_db['exp'])){
			if($row_db['paid_flag'] == 2 && $row_db['exp'] == "Expired"){
				$data['paidexpired'] = 1;
			}else{
				$data['paidexpired'] = 0;
			}
		}
		$data['nonpaid'] = 1;
	}
	return $data;
}

function pdgcontract($parentid, $sphinx_id){
	GLOBAL $dbarr;
	$pdg_contract=0;
	
	$financeObj	=	new company_master_finance($dbarr,$parentid,$sphinx_id);
	$balanceval =   array();
	$balancevalshadow = array();
	$version 	= 	fetchVersion($parentid);
	$balancevalshadow =	$financeObj->getFinanceMainData(0,$version);
	$balancevals = $financeObj->getFinanceMainData();
	//echo "<pre>";print_r($balancevals);
	$pdgarr=array('2');
	foreach($balancevals as $balanceval){
		//if($balanceval['active_campaign']=='1' && $balanceval['budget'] > 0 && in_array($balanceval['campaignid'],$pdgarr))
		// Only check expired condition for PDG contract for non paid flow
		if($balanceval['expired'] != 1 && in_array($balanceval['campaignid'],$pdgarr)){

			$pdg_contract=1;
		}
	}
	
	return $pdg_contract;
}



function getcampcount(){

	global $dbarr;
	$conn_finance= new DB($dbarr['FINANCE']);

	$camp_count = 0;

	$sel_camp_cnt = "SELECT max(campaignid) AS maxcount FROM " . PAYMENT_CAMPAIGN_MASTER . "";
	$res_camp_cnt = $conn_finance->query_sql($sel_camp_cnt);
	if($res_camp_cnt && mysql_num_rows($res_camp_cnt)>0){
		$row_camp_cnt = mysql_fetch_array($res_camp_cnt);

		$camp_count = $row_camp_cnt['maxcount'];
	}

	return $camp_count;
}


function remove_non_utf8($text='') {
	$final_str = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $text);
	return $final_str;
}

/* function to update sphinx_id in the tables where missing */
function updateSphinxID($parentid, $dbarr, $module = ''){
	
		$module = (trim($module)!= '') ? strtolower($module) : strtolower(APP_MODULE);
		
	/* Creating Connection STARTS */
		if($module == 'cs'){
			$conn_company    = new DB($dbarr['DB_IRO']);
			$conn_server_iro = $conn_company;
			$conn_temp	     = $conn_company;
			
		} else if($module    == 'tme'){
			$conn_company  	 = new DB($dbarr['IDC']);
			$conn_server_iro = new DB($dbarr['DB_IRO']);
			$conn_temp 		 = new DB($dbarr['DB_TME']);
			
		}else if($module     == 'me'){
			$conn_company  	 = new DB($dbarr['IDC']);
			$conn_server_iro = new DB($dbarr['DB_IRO']);
			$conn_temp 		 = $conn_company;
		}
		
		$conn_national = new DB($dbarr['DB_NATIONAL']);
		$conn_finance	= new DB($dbarr['DB_FNC']);	
	/* Creating Connection ENDS */
		
	/* SPHINXID GENERATION STARTS */
		$sphinxGenSql	= "INSERT IGNORE INTO tbl_id_generator SET parentid = '".$parentid."'";
		$conn_server_iro->query_sql($sphinxGenSql);
		
		$sphinxGetSql	= "SELECT sphinx_id FROM tbl_id_generator WHERE parentid = '".$parentid."'";
		$sphinxGetQry	= $conn_server_iro->query_sql($sphinxGetSql);
			
		$sphinxGetRow= mysql_fetch_assoc($sphinxGetQry); 
		$sphinxid 		= $sphinxGetRow['sphinx_id'];
	/* SPHINXID GENERATION ENDS */
		
	/* SPHINX ID UPDATION STARTED */
	/* UPDATING COMPANYMASTER TABLES WITH SPHINX_ID */	
		
		
		$extraShadowSql	= "UPDATE tbl_companymaster_extradetails_shadow SET sphinx_id = '".$sphinxid."' WHERE parentid = '".$parentid."'";
		$conn_temp->query_sql($extraShadowSql);
		
		
		
		$generalShadowSql	= "UPDATE tbl_companymaster_generalinfo_shadow SET sphinx_id = '".$sphinxid."' WHERE parentid = '".$parentid."'";
		$conn_temp->query_sql($generalShadowSql);

		
		
		$finTempSql			= "UPDATE tbl_companymaster_finance_temp SET sphinx_id = '".$sphinxid."' WHERE parentid = '".$parentid."'";
		if($module == 'cs')
			$conn_finance->query_sql($finTempSql);
		else
			$conn_temp->query_sql($finTempSql);
		
		$finMetaTempSql		= "UPDATE tbl_companymaster_finance_meta_temp SET sphinx_id = '".$sphinxid."' WHERE parentid = '".$parentid."'";
		if($module == 'cs')
			$conn_finance->query_sql($finMetaTempSql);
		else
			$conn_temp->query_sql($finMetaTempSql);

		

	/* SPHINX ID UPDATION ENDS */
	}


	function get_active_version($parentid,$version,$instrumentid=''){
	
		global $dbarr;
		$conn_finance= new DB($dbarr['FINANCE']);		
		
		if(trim($instrumentid)!=''){
			$get_inst_details = "SELECT a.parentid,a.version,b.entry_date as entry_date,b.isdeleted FROM payment_instrument_summary a JOIN payment_apportioning b  ON (a.parentid=b.parentid and a.version=b.version) WHERE a.instrumentid = '" . $instrumentid . "' AND disruption_flag!=23 LIMIT 1";
			
		} else {
			$get_inst_details = "SELECT parentid,version,entry_date as entry_date,isdeleted FROM payment_apportioning WHERE parentid='" . $parentid . "' AND version=" . $version . " AND disruption_flag!=23 LIMIT 1";
		}		

		$res_inst_details = $conn_finance->query_sql($get_inst_details);

		if($res_inst_details && mysql_num_rows($res_inst_details)>0){
		
			$row_inst_details = mysql_fetch_array($res_inst_details);
			$ret_version = $row_inst_details['version'];
			$entry_date  = $row_inst_details['entry_date'];
			$parentid    = $parentid!='' ? $parentid : $row_inst_details['parentid'];
			$org_version = $row_inst_details['version'];

			$isdeleted  = $row_inst_details['isdeleted'];

			if($isdeleted==1){//JDRR contracts amount to apportioned in JDRR only
				return $ret_version;
			}
		}	
		
		$is_deleted_where = 'isdeleted IN (0,2)';
		if ($isdeleted>2) $is_deleted_where = "isdeleted='".$isdeleted."'";		


		if($entry_date!=''){
			
			$get_next_version = "SELECT parentid,version,entry_date FROM payment_apportioning WHERE parentid='" . $parentid . "' AND entry_date>='" . $entry_date . "' AND tot_app_amount>0 AND ".$is_deleted_where." AND (floor(budget)-floor(balance)) > 0 AND budget > 0 GROUP BY version ORDER BY entry_date DESC LIMIT 1";
			$res_next_version = $conn_finance->query_sql($get_next_version);

			if($res_next_version && mysql_num_rows($res_next_version)>0){
				$row_next_version = mysql_fetch_array($res_next_version);
				
				$ret_version = $row_next_version['version'];
			}
		
		}		

		
        if ($ret_version==$version) {//Disruption cases handling
		
            $disruption_sql = "SELECT * from payment_disruption_version WHERE parentid= '".$parentid."' AND old_version='".$ret_version."' ORDER BY entry_date DESC LIMIT 1 ";
            $disruption_res = $conn_finance->query_sql($disruption_sql);
            if(mysql_num_rows($disruption_res)) {
                $disruption_row = mysql_fetch_assoc($disruption_res);
                $ret_version = 	 $disruption_row['new_version'];
            }
        }

		
		if($ret_version!='' && trim($instrumentid)!='' && ($org_version!=$ret_version)){//update app_version only if version is not same
			$updt_app_version = "UPDATE payment_instrument_summary SET app_version = '" . $ret_version . "' WHERE instrumentid='" . $instrumentid . "'";
			$res_app_version = $conn_finance->query_sql($updt_app_version);
		}

		if($ret_version=='') $ret_version = $version;
		
		return $ret_version;
	}

	function encrypt_card_number($cardnum) {
	
		if($cardnum != ''){		
			$len = strlen($cardnum)-5;
			$str = '';
			for($i=0;$i<strlen($cardnum);$i++){
				if($i < 4 || $i > $len){
					$str .= $cardnum[$i];
				}else{
					$str .= 'X';
				}
			}
			return $str;
		}		
	}
	
	function payment_webstatus($para_arr,$flag){
		global $dbarr;
		$conn_finance= new DB($dbarr['FINANCE']);	
	
		$parentid     = $para_arr['parentid'];
		$instrumentid = $para_arr['instrumentid'];
		$source       = $para_arr['source'];

		switch($flag){
			case '1' :
				$ins_qry = "INSERT INTO payment_webstatus SET
							parentid = '".$parentid."',
							instrumentid = '".$instrumentid."',
							source = '".$source."',
							entry_date = NOW()";
				$res_ins_qry = $conn_finance->query_sql($ins_qry);			
			break;
			
			case '2' :
				$updt_qry = "UPDATE payment_webstatus SET
							payment_flag = 1
							WHERE instrumentid = '".$instrumentid."'";
				$res_updt_qry = $conn_finance->query_sql($updt_qry);					
			break;
			
			case '3' :
				$updt_qry = "UPDATE payment_webstatus SET
							process_flag = 1,
							processed_by = '".$source."',
							process_date = NOW()
							WHERE instrumentid = '".$instrumentid."'";
				$res_updt_qry = $conn_finance->query_sql($updt_qry);					
			break;			
		}
	}	
	
	function updt_tmesearch($parentid,$version){
		global $dbarr;
		$conn_local   = new DB($dbarr['LOCAL']);
		$conn_finance = new DB($dbarr['FINANCE']);
		
		$sel_ecs = "SELECT ecsflag FROM payment_otherdetails WHERE parentid='".$parentid."' AND version='".$version."' AND ecsflag>0";
		$res_ecs = $conn_finance->query_sql($sel_ecs);	
		
		if($res_ecs && mysql_num_rows($res_ecs)>0){
			$row_ecs = mysql_fetch_array($res_ecs);
			$ecs_flag = $row_ecs['ecsflag'];
			
			$upt_tmes = "UPDATE d_jds.tbl_tmesearch SET ecs_flag='".$ecs_flag."' WHERE parentid='".$parentid."'";
			$res_tmes = $conn_local->query_sql($upt_tmes);	
		}
		
	
	}
?>
