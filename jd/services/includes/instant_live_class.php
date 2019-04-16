<?php
class instant_live_class extends DB
{
	var  $conn_iro    	= null;
	var  $conn_jds   	= null;
	var  $conn_tme 		= null;
	var  $conn_idc    	= null;
	var  $conn_fnc    	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	var  $ucode			= null;
	
	
	function __construct($params)
	{	
		global $params;
 		$parentid 			= trim($params['parentid']);
		$module 			= trim($params['module']);
		$data_city 			= trim($params['data_city']); 	
		$rquest 			= trim($params['rquest']); 
		$ucode 		= trim($params['ucode']); 
		if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($module)=='')
		{
			$message = "Module is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}		 
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->rquest  	  	= $rquest;
		$this->ucode  	  	= $ucode;
		$this->instrumentid = "";
		if(isset($params['instrumentid'])){
			$this->instrumentid = trim($params['instrumentid']);
		}
		$this->requested_ver = 0;
		if(intval($params['version']) >0 ){
			$this->requested_ver = intval($params['version']);
		}
		$this->skiponline = 0;
		if(isset($params['skiponline'])){
			$this->skiponline = $params['skiponline'];
		}
		$this->call_time  	= date("Y-m-d H:i:s");
		$this->setServers();		 
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;			
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_iro_slave	= $db[$conn_city]['iro']['slave'];
		$this->conn_fnc    		= $db[$conn_city]['fin']['master'];	
		$this->conn_fnc_slave   = $db[$conn_city]['fin']['slave'];	
		
		$this->data_correction    		= $db[$conn_city]['data_correction']['master'];	
		$this->data_correction_slave  	= $db[$conn_city]['data_correction']['slave'];	

		
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];	
		$this->web_conn   		= $db['website']['master']; // pointing to 12.35
		$this->conn_national	= $db['db_national'];	
		$this->conn_webedit		= $db['web_edit']; // pointing to 6.75
		
	}	
	function instant_live() {
		$func = $this->rquest;
		if((int)method_exists($this,$func) > 0)
			return $this->$func();
		else {
			$message = "Invalid Function";
			return json_encode($this->sendDieMessage($message));			
		}
	}
	function InstantUpdation()
	{
	 	global $params;
		if($params['trace'] == 1)
		{	
			echo "Input Parameters : ";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		$this->insertLog("start_time",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,date('Y-m-d H:i:s'));	
		
		
		
		/* Fetching data from db_iro.tbl_id_generator table - 171 server */
		
		$row_idgen			=	$this->GetIdGenerator();
		$row_idgen 			= 	$this->addslashesArray($row_idgen);
		$panindia_sphinxid 	=	$row_idgen['panindia_sphinxid'];
		$data_city 			=	$row_idgen['data_city'];			
		$std_code 			=	$row_idgen['stdcode'];
		$short_url 			=	$row_idgen['short_url'];
		$data_city_stdcode 	=	$row_idgen['data_city_stdcode'];
		$sphinx_id 			=	$row_idgen['sphinx_id'];
	
		/* datasrc logic */
		$main_city_array = array('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad');  // live
		if(!in_array(strtolower($data_city),$main_city_array)){
			$datasrc = "Remotecity";
		}
		else{
			$datasrc = $data_city;
		}	
		
		/* Fetching data from db_national_listing.tbl_national_listing table - 17.233 server */	
		$row_national 	= 	$this->GetNationallist();
		$row_national 	= 	$this->addslashesArray($row_national);
		if($row_national != ""){
			$nationallisting_flag = 1;
			$listed_cities = $row_national['category_city']  .  $data_city ."|#|";;
		}
		else{
			$nationallisting_flag = 0;
			$listed_cities= $row_idgen['listed_cities'];
		}
		
		$curtime=date('H:m:s'); 
		$online_payment = 0;
			/* Online Payment Check - 161 server */
		if(!empty($this->instrumentid)){
			$sqlOnlinePayment = "SELECT parentid,instrumentid,version FROM payment_instrument_summary WHERE parentid = '".$this->parentid."' AND instrumentid = '".$this->instrumentid."' AND ((instrumentType IN ('offlinecreditcard','creditcard','neft','payu')) OR (entry_doneby = 'userselfwebsignup'))";
			$resOnlinePayment = parent::execQuery($sqlOnlinePayment, $this->conn_fnc);
			if($resOnlinePayment && parent::numRows($resOnlinePayment)>0){
				$row_online_payment = parent::fetchData($resOnlinePayment);
				$version = intval($row_online_payment['version']);
				if($version >0){
					$sqlPDGBudget = "SELECT parentid FROM payment_apportioning WHERE parentid = '".$this->parentid."' AND version = '".$version."' AND campaignId IN (1,2) AND budget>0";
					$resPDGBudget = parent::execQuery($sqlPDGBudget, $this->conn_fnc);
					if($resPDGBudget && parent::numRows($resPDGBudget)>0){
						$online_payment = 1;
					}
				}
			}
			// offline payment - fresh contract / paid expired to paid - based on start date - after discussion with S & T
			$today = date("Y-m-d")." 00:00:00";
			if($online_payment !=1){
				$sqlOfflinePayment = "SELECT parentid FROM tbl_companymaster_finance WHERE parentid = '".$this->parentid."' AND campaignId IN (1,2) AND balance>0 AND start_date >= '".$today."'";
				$resOfflinePayment = parent::execQuery($sqlOfflinePayment, $this->conn_fnc);
				if($resOfflinePayment && parent::numRows($resOfflinePayment)>0){
					$online_payment = 1;
				}
			}
		}else if($this->requested_ver > 0){
			$sqlOnlinePayment = "SELECT parentid,instrumentid,version FROM payment_instrument_summary WHERE parentid = '".$this->parentid."' AND version = '".$this->requested_ver."' AND entry_doneby = 'userselfwebsignup'";
			$resOnlinePayment = parent::execQuery($sqlOnlinePayment, $this->conn_fnc);
			if($resOnlinePayment && parent::numRows($resOnlinePayment)>0){
				$row_online_payment = parent::fetchData($resOnlinePayment);
				$version = intval($row_online_payment['version']);
				if($version >0){
					$sqlPDGBudget = "SELECT parentid FROM payment_apportioning WHERE parentid = '".$this->parentid."' AND version = '".$version."' AND campaignId IN (1,2) AND budget>0";
					$resPDGBudget = parent::execQuery($sqlPDGBudget, $this->conn_fnc);
					if($resPDGBudget && parent::numRows($resPDGBudget)>0){
						$online_payment = 1;
					}
				}
			}
		}
		else
		{
			$sql_ddg_instant1="SELECT national_catid,pincode,position_flag, lcf, hcf,updatedby, NOW() AS updateddate,backenduptdate,catid FROM db_iro.tbl_fp_search WHERE parentid='".$this->parentid."' and activeflag > 0 AND pincode<>'999999'";
			$res_ddg_instant1 = parent::execQuery($sql_ddg_instant1, $this->conn_iro);
			if(mysql_num_rows($res_ddg_instant1) >0)
				$online_payment = 0;
		}
		
		if($this->skiponline == 1){
			$online_payment = 1;
		}
		$instant_block = 0;
		if(!empty($this->instrumentid) && $online_payment != 1)
		{
			echo json_encode($this->sendDieMessage('Instrumentid Without Online Payment'));
			die();
		}
		
		if(($this->requested_ver > 0) && ($online_payment != 1))
		{
			echo json_encode($this->sendDieMessage('Version Not From Self Signup Flow'));
			die();
		}

		elseif(($curtime > '01:00:00' AND $curtime < '05:00:00') || $instant_block==1)
		{
		
			$sql_check_exists = "SELECT * FROM online_regis1.tbl_instant_live WHERE parentid='".$params['parentid']."' AND process_flag=0";
			$res_check_exists	=	parent::execQuery($sql_check_exists, $this->conn_idc);
			if($res_check_exists && mysql_num_rows($res_check_exists)==0)
			{
				$params_arr = array_map('trim',$params);			 
				foreach ($params_arr as $key => $value) {						
				 $params_string .= $key . '=' . urlencode($value) . '&';
				}
							   
				$params_string = rtrim($params_string, '&');			   
				$main_url = "http://".$_SERVER['HTTP_HOST']."/services/instant_live.php";
				$final_url = $main_url."?".$params_string;
				
				
				$datacity_upper = strtoupper(trim($params['data_city']));
				$maincities_array = array("MUMBAI","DELHI","KOLKATA","BANGALORE","CHENNAI","PUNE","HYDERABAD","AHMEDABAD");
				if(in_array($datacity_upper,$maincities_array)){
					$datacitystr = $params['data_city'];
				}else{
					$datacitystr = "Remote";
				}
				
				$insert_fail = "INSERT INTO online_regis1.tbl_instant_live 
				SET 
				parentid	=	'".$params['parentid']."',
				source		=	'".$params['module']."',
				ucode		=	'".$params['ucode']."',
				url			=	'".$final_url."',
				data_city	=	'".$datacitystr."',
				entry_date	=	now()";
				
				$res_insert_fail	=	parent::execQuery($insert_fail, $this->conn_idc);
			}
	 		//~ $output_final['data'] 				=  'Contract inserted successfully';
			//~ $output_final['error']['code'] 		=  "0";
			//~ $output_final['error']['message'] 	=  "success";
			
			$output_final['data'] 				=  'Contract inserted UN-successfully';
			$output_final['error']['code'] 		=  "1";
			$output_final['error']['message'] 	=  "Error";			 
			if($params['trace'] == 1)
			{	
				echo "Out put : ";
				print_r($output_final);
				echo "\n--------------------------------------------------------------------------------------\n";
			}	
			return $output_final;	
		}
		else	 		
		{
			GLOBAL $db;
			$this->conn_split			= $db['web_split'];
			$this->conn_split_2			= $db['web_split_standby'];
			$this->conn_split_3			= $db['web_split_standby_2'];
		
			$success_flag = 1;
			/* Fetching data from db_iro.iro_cards table - 171 server */
			$row_irocard = $this->GetIroCard();
			$row_irocard = $this->addslashesArray($row_irocard);
			
			$docid					= 	$row_irocard['docid'];
			$smallcard  			= 	$row_irocard['small_card'];
			$mediumcard 			= 	$row_irocard['medium_card'];
			$extendedcard 			= 	$row_irocard['extended_card'];
			$featured 				= 	$row_irocard['featured'];			
			$national_b2b_flag  	= 	$row_irocard['national_b2b_flag'];			
			$jdrrtag				= 	$row_irocard['jdrr_tag'];			
			$typeflag				= 	$row_irocard['type_flag'];
			$subtypeflag			= 	$row_irocard['sub_type_flag'];
			$irotypeflag			= 	$row_irocard['iro_type_flag'];
			$websitetypeflag		= 	$row_irocard['website_type_flag'];
			$businessflag			=	intval($row_irocard['businesstags']);
			$popularflag			=	$row_irocard['popular_flag'];
			$fb_prefered_language 	=	$row_irocard['fb_prefered_language'];
			$attributes 			=	$row_irocard['attributes'];
			
			$mediumcard_data		=	explode("|$$$|",$mediumcard);
			$b2b_flag				=	$mediumcard_data[61];
				
			
			/*$row_compsyn 			= 	$this->GetCompsyn();
			$row_compsyn 			= 	$this->addslashesArray($row_compsyn);
			
			$synname 				= 	$row_compsyn['synname'];
			$synid					= 	$row_compsyn['synid'];
			$syn_status				=	$row_compsyn['display_flag'];*/
			
			
			
			$row_compExtra			=	$this->GetCompExtradetails();
			$row_compExtra 			= 	$this->addslashesArray($row_compExtra);
			
			$main_compname 			=	$row_compExtra['main_compname'];
			$hidden_flag			=	$row_compExtra['hidden_flag'];
			$pointer_flag  			=	$row_compExtra['pointer_flag'];
			$rating_jd	   			=	$row_compExtra['rating_jd'];
			$random_compsrch_precalc=	$row_compExtra['random_compsrch_precalc'];
			$update_time   			=	$row_compExtra['update_time'];
			$active_flag   			=	$row_compExtra['active_flag'];
			$photos_count  			=	$row_compExtra['photos_count'];
			$data_src 	   			=	$row_compExtra['data_src'];	
			$noduplicatecheck 		=	$row_compExtra['noduplicatecheck'];
			$average_rating 		=	$row_compExtra['average_rating'];
			$area_sensitivity 		=	$row_compExtra['area_sensitivity'];
			$closedown_flag			=	$row_compExtra['closedown_flag'];
			$block_for_sale 		=	$row_compExtra['block_for_sale'];
			$type_flag				=	$row_compExtra['type_flag'];
			$sub_type_flag			=	$row_compExtra['sub_type_flag'];
			$star_jd				=	$row_compExtra['star_jd'];
			$iro_type_flag			=	$row_compExtra['iro_type_flag'];
			$website_type_flag 		=	$row_compExtra['website_type_flag'];
			$hotcategory			=	$row_compExtra['hotcategory'];
			$pricerange				=	$row_compExtra['price_range'];
			$dupgroupid				=	$row_compExtra['dup_groupid'];
			$lowranking				=	$row_compExtra['low_ranking'];
			$masterparentid			=	$row_compExtra['master_parentid'];
			$typeflagactions 		=	$row_compExtra['type_flag_actions'];
			$console_cat_callcnt 	=	$row_compExtra['console_cat_callcnt'];
			$restrict_display 		=	$row_compExtra['restrict_display'];		
			$guarantee 				=	$row_compExtra['guarantee'];
			$catidlineage 			=	$row_compExtra['catidlineage'];
			$mid					=	$row_compExtra['mid'];
			$muser					=	$row_compExtra['muser'];
			$catidlineage 			=	ltrim($catidlineage,"/");
			$catidlineage 			=	rtrim($catidlineage,"/");
			$catidlineage 			=	str_replace("/","",$catidlineage);			
			$catidlineage_array 	=	explode(",",$catidlineage);
			
			foreach($catidlineage_array as $key=>$val){
				if(!empty($val ))	
					$catidlineage_str .= ",".$val;
			}
			$catidlineage = trim($catidlineage_str,",");
			
			if($masterparentid != ""){
				$masterflag = 1;
			}else{
				$masterflag = 0;
			}
			/* Additional Check to Block */
			$skip_process = 0;
			if((intval($hidden_flag) == 1) || (empty($main_compname))){
				$skip_process = 1;
				echo json_encode($this->sendDieMessage('Skip Process - Companyname is blank or hidden flag is 1'));
				die();
			}
			$ignore_words_arr = array(" closed ","(accor)","(sodexo)","(see ");
			$compnameval = trim(strtolower($main_compname));
			foreach($ignore_words_arr as $ignore_word){
				if (stripos($compnameval, $ignore_word) !== false) {
					$skip_process = 1;
					break;
				}
			}
			if($skip_process == 1){
				echo json_encode($this->sendDieMessage('Skip Process - Additional Company Info Check'));
				die();
			}
			
			$row_compgeninfo		= 	$this->GetCompGeneralinfo();
			$row_compgeninfo 		= 	$this->addslashesArray($row_compgeninfo);
			
			$stdcode 	 			=	$row_compgeninfo['stdcode'];
			$pincode	 			=	$row_compgeninfo['pincode'];
			$areaname	 			=	$row_compgeninfo['areaname'];
			$areaname_ws 			=	$row_compgeninfo['areaname_ws'];
			$landmark    			=	$row_compgeninfo['landmark'];
			$city		 			=	$row_compgeninfo['city'];
			$latitude	 			=	$row_compgeninfo['latitude'];
			$longitude	 			=	$row_compgeninfo['longitude'];
			$address_search			=	$this->sanitize($row_compgeninfo['address_search']);
			$contactperson_search	=	$row_compgeninfo['contactperson_search'];
			$phone_search			=	$row_compgeninfo['phone_search'];
			$email_search			= 	$row_compgeninfo['email_search'];
			$tollfree				=	$row_compgeninfo['tollfree'];
			$v_number				=	$row_compgeninfo['v_number'];
			$website				=	$row_compgeninfo['website'];
			$blockforvirtual 		=	$row_compgeninfo['blockforvirtual'];			
			$company_callcnt 		=	$row_compgeninfo['company_callcnt'];
			$duplicate_check_phonenos=	$row_compgeninfo['duplicate_check_phonenos'];
			$geocode_accuracy_level	=	$row_compgeninfo['geocode_accuracy_level'];
			$state					=	$row_compgeninfo['state'];
			$updatedBy  			=	$row_compgeninfo['updatedby'];
			$updatedOn  			=	$row_compgeninfo['updatedon'];
			
			$misc_data 				= 	$this->getMiscData();
			//$paid					=	$row_compgeninfo['paid'];
			
			$paid_status_arr		=	$this->get_paidstatus($this->parentid,$this->data_city);
			$paid					=	$paid_status_arr['result']['paid'];      
			
			$row_pslab 				=	$this->GetPslab(); 
			$percentage_slab 		=	$row_compgeninfo['percentage_slab'];			
			
			$row_blockver 			=	$this->GetBlockVer();
			$block_verified 		=	$row_blockver['block_verified']; 
						
			if( ($row_compgeninfo['dialable_landline'] != '') && ($row_compgeninfo['dialable_landline'] != 0)){
				$dialable_landline		=	$row_compgeninfo['dialable_landline'];							
			}
			if(($row_compgeninfo['dialable_mobile'] != '') && ($row_compgeninfo['dialable_mobile'] != 0)){
					$dialable_mobile	= $row_compgeninfo['dialable_mobile'];
			}							
			if(($row_compgeninfo['dialable_virtualnumber'] != '') && ($row_compgeninfo['dialable_virtualnumber'] != 0)){
				$dialable_virtualnumber	= $row_compgeninfo['dialable_virtualnumber'];
			}
			
			$dialable_phonenos		=	$dialable_landline ."," . $dialable_mobile ."," . $dialable_virtualnumber .",". $tollfree ;
			$dialable_phonenos		=	explode(",",$dialable_phonenos);
			$dialable_all_clean 	=	array_filter($dialable_phonenos);
			$dialable_phonenos		=	implode(",",$dialable_all_clean);
			
			$sql_vlc="SELECT  ifnull(MAX(paid_flag),0) AS paid_flag, MAX(CASE WHEN paid_flag>0 THEN 1 ELSE 0 END) AS vlc_flag,MAX(bid_perday) AS bid_perday FROM(SELECT parentid,campaignid,budget,company_deduction_amt AS avg_amt,company_deduction_amt AS iro_bid_value,0 AS tenure,0 AS contract_dayleft,expired,bid_perday,IF(campaignid=22 AND end_date>CURRENT_DATE,1,0) AS jdrr_budget,IF(balance>0,balance,0) AS contract_amt, data_city,CASE WHEN campaignid=2 AND IF(balance>0,balance,0) > 0 AND expired=0 THEN 6 WHEN campaignid IN (1,3) AND IF(balance>0,balance,0)>0 AND expired=0 THEN 5 WHEN campaignid NOT IN (1,2,3) AND IF(balance>0,balance,0)>0 AND expired=0 THEN 3 END AS paid_flag FROM db_finance.tbl_companymaster_finance WHERE campaignid <> 0 AND parentid='".$this->parentid."')x1 GROUP BY parentid";
			$result_vlc = parent::execQuery($sql_vlc, $this->conn_fnc);
			$row_vlc=mysql_fetch_assoc($result_vlc);			
			
			if($row_vlc['paid_flag'] !=''){
				$paid_flag = $row_vlc['paid_flag'];
			}else{
				$paid_flag =0;
			}	
			if($row_vlc['vlc_flag'] != ''){
				$vlc_flag = $row_vlc['vlc_flag'];
			}else{
				$vlc_flag = 0;				
			}
			if($row_vlc['bid_perday'] != ''){
				$bid_perday = $row_vlc['bid_perday'];
			}else{
				$bid_perday = 0;
			}
			
			$sql_finfo	=	"SELECT contractId, SUBSTRING_INDEX(GROUP_CONCAT(add_infotxt ORDER BY lockDateTime DESC SEPARATOR '$##~~##$'),'$##~~##$',1) AS add_infotxt, data_city FROM d_jds.tbl_comp_addInfo WHERE add_infotxt !='' AND contractId='".$this->parentid."' GROUP BY contractId";
			
			$rs_finfo 	=	parent::execQuery($sql_finfo, $this->conn_iro);
			$row_finfo	=	mysql_fetch_assoc($rs_finfo);
			$f4info  	=	$row_finfo['add_infotxt'];
			
			$sql_btag="SELECT  IF(campaignid=5,'R',IF(campaignid=13,'E','')) AS banner_tag  FROM db_finance.tbl_companymaster_finance WHERE parentid ='".$this->parentid."' AND campaignid <> 0";
			$rs_btag 	=	parent::execQuery($sql_btag, $this->conn_fnc_slave);
			$row_btag	=	mysql_fetch_assoc($rs_btag);
			$bannertag  = $row_btag['banner_tag'];
			
			$sql_roling	=	"select IFNULL(final_callcnt,0) as final_callcnt from d_jds.tbl_comp_callcnt_rolling where parentid ='".$this->parentid."'";
			$res_roling 	=	parent::execQuery($sql_roling, $this->conn_iro);
			$row_roling		=	mysql_fetch_assoc($res_roling);
					
			if (!$row_roling ){
				$final_callcnt= 0;
			}else{
				$final_callcnt = $row_roling['final_callcnt'];
			}
			
			
		 
			$chk_com_data_url = "http://192.168.20.102:9001/web_services/CompanyDetails.php?docid=".$docid."&json=1&mod=g";
			$curl_com_data = $this->CurlFn($chk_com_data_url);	
			$curl_com_data_arr = json_decode($curl_com_data,true);
			if($curl_com_data_arr[$docid])
				$Contract_Flag = 2;
			else
				 $Contract_Flag = 1;
				 
			$sql_update_arch_d = "DELETE FROM webporting_instant_act.tbl_wp1_compsrch_new_architecture_instant WHERE parentid = '".$this->parentid."'";
			$res_update_arch_d 	= parent::execQuery($sql_update_arch_d, $this->conn_iro);				
			
			$sql_sp_catcall = "call webporting_instant_act.sp_instant_webporting('".$this->parentid."','".$Contract_Flag."')";
			$res_sp_catcall	= parent::execQuery($sql_sp_catcall, $this->conn_iro);
			$row_sp_catcall	= mysql_fetch_assoc($res_sp_catcall);
			
			if($row_sp_catcall['response'] != 'Success'){
				$success_flag = 0;
				$this->insertLog("sp_instant_webporting",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"webporting_instant_act.sp_instant_webporting call failed ==>Query==>".$sql_sp_catcall ."==>Result==>". $res_sp_catcall);
			}else{
				$this->insertLog("sp_instant_webporting",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"webporting_instant_act.sp_instant_webporting call sucessfully==>Query==>".$sql_sp_catcall ."==>Result==>". $res_sp_catcall);
			}
			
			$active_contract = 0;
			$data_found = 0;
			$sql_cnt_comp = "SELECT compname,docid,synid,address_search,movie_tag,active_flag FROM webporting_instant_act.tbl_wp1_compsrch_new_architecture_instant WHERE parentid = '".$this->parentid."' AND done_flag = 1";
			$res_cnt_comp 	=	parent::execQuery($sql_cnt_comp, $this->conn_iro);
			
			
			if($res_cnt_comp && mysql_num_rows($res_cnt_comp) > 0)		
			{
				$data_found = 1;
				while($row_comp_data 	= 	mysql_fetch_assoc($res_cnt_comp))
				{
					$active_contract								= intval($row_comp_data['active_flag']);
					$irocard_movie 									= intval($row_comp_data['movie_tag']); // movie_tag will be 1 in all rows for a parentid confirmed by Ashwin
					$synid											= $row_comp_data['synid'];
					$compname										= trim($row_comp_data['compname']);
					$compname 										= str_ireplace('(customer care)','Customer Care',$compname);
					$compname 										= str_ireplace('(prepaid)','Prepaid',$compname);
					$compname 										= str_ireplace('(postpaid)','Postpaid',$compname);
					$compname 										= str_ireplace('(booking office)','Booking Office',$compname);
					$areaname 										= $this->sanitize($areaname,1);
					$compname_search_witoutbracket 					= $this->braces_content_removal($this->sanitize($compname,1));
					
					if(trim($row_comp_data['docid']) != trim($row_comp_data['synid']))
					{
						$compname_area									= $this->braces_content_removal($compname).(($areaname)?' ('.$areaname.')':'');
						$compname_search 								= $this->braces_content_removal($this->sanitize($compname,1));
					}
					else
					{
						$compname_area									= $compname.(($areaname)?' ('.$areaname.')':'');
						$compname_search 								= $this->braces_content_removal($this->sanitize($compname));
					}
					$compname_search_wo_space						= preg_replace('/\s*/m','',$compname_search);
					$compname_search_ignore							= $this->applyIgnore($compname_search_witoutbracket);
					$compname_area_search							= $this->companyWithArea($compname_search,$areaname);
					$compname_area_search_wo_space					= preg_replace('/\s*/m','',$compname_area_search);	
					$compname_area_search_ignore    				= $this->companyWithArea($compname_search_ignore,$areaname);
					
					$compname_search_processed 						= $this->concatsingle($this->getSingular($compname_search));
					$compname_search_processed_wo_space 			= preg_replace('/\s*/m','',$compname_search_processed);
					$compname_search_processed_ignore 				= $this->concatsingle($this->applyIgnore($this->getSingular($compname_search_witoutbracket)));
					$compname_search_processed_ignore_wo_space 		= preg_replace('/\s*/m','',$compname_search_processed_ignore);
					
					$compname_area_search_processed 				= $this->concatsingle($this->getSingular($compname_area_search));
					$compname_area_search_processed_wo_space 		= preg_replace('/\s*/m','',$compname_area_search_processed);
					$compname_area_search_processed_ignore 			= $this->concatsingle($this->companyWithArea($compname_search_processed_ignore,$areaname));
					$compname_area_search_processed_ignore_wo_space =  preg_replace('/\s*/m','',$compname_area_search_processed_ignore);
					$address_search									= $this->braces_content_removal($this->sanitize($row_comp_data['address_search'],1));
					
					$Sql_update_compsearch = "UPDATE webporting_instant_act.tbl_wp1_compsrch_new_architecture_instant set compname_search= '".addslashes(stripslashes($compname_search))."', compname_search_wo_space= '".addslashes(stripslashes($compname_search_wo_space))."',compname_search_ignore= '".addslashes(stripslashes($compname_search_ignore))."', compname_area= '".addslashes(stripslashes($compname_area))."',compname_area_search= '".addslashes(stripslashes($compname_area_search))."',compname_area_search_wo_space= '".addslashes(stripslashes($compname_area_search_wo_space))."',compname_area_search_ignore= '".addslashes(stripslashes($compname_area_search_ignore))."',compname_search_processed= '".addslashes(stripslashes($compname_search_processed))."',compname_search_processed_wo_space= '".addslashes(stripslashes($compname_search_processed_wo_space))."',compname_search_processed_ignore= '".addslashes(stripslashes($compname_search_processed_ignore))."',compname_search_processed_ignore_wo_space= '".addslashes(stripslashes($compname_search_processed_ignore_wo_space))."',compname_area_search_processed= '".addslashes(stripslashes($compname_area_search_processed))."',compname_area_search_processed_wo_space = '".addslashes(stripslashes($compname_area_search_processed_wo_space))."',compname_area_search_processed_ignore = '".addslashes(stripslashes($compname_area_search_processed_ignore))."',compname_area_search_processed_ignore_wo_space = '".addslashes(stripslashes($compname_area_search_processed_ignore_wo_space))."', paidstatus ='".$paid."', address_search = '".addslashes(stripslashes($address_search))."'  WHERE parentid = '".$this->parentid."'  AND synid = '".$synid."' AND done_flag = 1";
					$res_update_compsearch 	=	parent::execQuery($Sql_update_compsearch, $this->conn_iro);	
				}
			}
			if(!$res_update_compsearch){
				$success_flag = 0;
				$this->insertLog("tbl_wp1_compsrch_new_architecture_instant",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"tbl_wp1_compsrch_new_architecture_instant updation failed==>Query==>".$Sql_update_compsearch."==>Result==>".$res_update_compsearch);
			}else{
				$this->insertLog("tbl_wp1_compsrch_new_architecture_instant",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"tbl_wp1_compsrch_new_architecture_instant updated sucessfully");
			}
			
			/*Iro cards insert start here*/
			if($data_found == 1){ // after discussion with Shital & Ashwin Removing Active Contract condition
				$insert_into_irocard_instant="INSERT INTO db_company.tbl_iro_cards_instant SET
						panindia_sphinxid				= '".$panindia_sphinxid."',
						sphinx_id						= '".$sphinx_id."',						
						parentid						= '".$this->parentid."',
						small_card						= '".$smallcard."',
						medium_card						= '".$mediumcard."',
						extended_card					= '".$extendedcard."',
						update_time						= '".date("Y-m-d H:i:s")."',
						data_city						= '".$data_city."',
						docid							= '".$docid."',
						featured						= '".$featured."',
						b2b_flag	 					= '".$b2b_flag."',
						national_b2b_flag	 			= '".$national_b2b_flag."',
						jdrr_tag						= '".$jdrrtag."',
						f4info							= '".$f4info."',
						short_url						= '".$short_url."',
						blockforvirtual					= '".$blockforvirtual."',
						VLC_flag						= '".$vlc_flag."',
						type_flag						= '".$typeflag."',
						sub_type_flag					= '".$subtypeflag."',
						low_ranking						= '".$lowranking."',
						iro_type_flag					= '".$irotypeflag."',
						website_type_flag				= '".$websitetypeflag."',
						block_verified					= '".$block_verified."',
						random_compsrch_precalc			= '".$random_compsrch_precalc."',
						business_flag					= '".$businessflag."',
						master_parentid					= '".$masterparentid."',
						master_flag						= '".$masterflag."',
						banner_tag						= '".$bannertag."',
						movie_tag						= '',
						popular_flag					= '".$popularflag."',
						type_flag_actions				= '".$typeflagactions."',
						fb_prefered_language			= '".$fb_prefered_language."',
						attributes						= '".$attributes."',
						guarantee						= '".$guarantee."',
						restrict_display				= '".$restrict_display."',
						misc_data						= '".addslashes($misc_data)."',
						mid 							= '".$mid."',
						muser 							= '".$muser."'
											
						ON DUPLICATE KEY UPDATE
						
						panindia_sphinxid				= '".$panindia_sphinxid."',
						sphinx_id						= ".$sphinx_id.",						
						parentid						= '".$this->parentid."',
						small_card						= '".$smallcard."',
						medium_card						= '".$mediumcard."',
						extended_card					= '".$extendedcard."',
						update_time						= '".date("Y-m-d H:i:s")."',
						data_city						= '".$data_city."',						
						featured						= '".$featured."',
						b2b_flag	 					= '".$b2b_flag."',
						national_b2b_flag	 			= '".$national_b2b_flag."',
						jdrr_tag						= '".$jdrrtag."',
						f4info							= '".$f4info."',
						short_url						= '".$short_url."',
						blockforvirtual					= '".$blockforvirtual."',
						VLC_flag						= '".$vlc_flag."',
						type_flag						= '".$typeflag."',
						sub_type_flag					= '".$subtypeflag."',
						low_ranking						= '".$lowranking."',
						iro_type_flag					= '".$irotypeflag."',
						website_type_flag				= '".$websitetypeflag."',
						block_verified					= '".$block_verified."',
						random_compsrch_precalc			= '".$random_compsrch_precalc."',
						business_flag					= '".$businessflag."',
						master_parentid					= '".$masterparentid."',
						master_flag						= '".$masterflag."',
						banner_tag						= '".$bannertag."',
						movie_tag						= '',
						popular_flag					= '".$popularflag."',
						type_flag_actions				= '".$typeflagactions."',
						fb_prefered_language			= '".$fb_prefered_language."',
						attributes						= '".$attributes."',
						guarantee						= '".$guarantee."',
						restrict_display				= '".$restrict_display."',
						misc_data						= '".addslashes($misc_data)."',
						mid 							= '".$mid."',
						muser 							= '".$muser."'";
				$result_consolidat_instant 	=	parent::execQuery($insert_into_irocard_instant, $this->conn_webedit);
				$result_consolidat_instant1 	=	parent::execQuery($insert_into_irocard_instant, $this->conn_split_3);
			}
			
			if(!$result_consolidat_instant){
				$success_flag = 0;
				$this->insertLog("tbl_iro_cards",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"tbl_iro_cards insertion failed==>Query==>".$insert_into_irocard_instant."==>Result==>".$result_consolidat_instant);
			}else{
				$this->insertLog("tbl_iro_cards",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"tbl_iro_cards inserted sucessfully");
			}
			
			if($irocard_movie == 1){
					
					$insert_into_irocard_movie="INSERT INTO webporting_crawl_movies.tbl_iro_cards_movies SET
						panindia_sphinxid				= '".$panindia_sphinxid."',
						sphinx_id						= '".$sphinx_id."',						
						parentid						= '".$this->parentid."',
						small_card						= '".$smallcard."',
						medium_card						= '".$mediumcard."',
						extended_card					= '".$extendedcard."',
						update_time						= '".date("Y-m-d H:i:s")."',
						data_city						= '".$data_city."',
						docid							= '".$docid."',
						featured						= '".$featured."',
						national_b2b_flag	 			= '".$national_b2b_flag."',
						jdrr_tag						= '".$jdrrtag."',
						f4info							= '".$f4info."',
						short_url						= '".$short_url."',
						blockforvirtual					= '".$blockforvirtual."',
						VLC_flag						= '".$vlc_flag."',
						type_flag						= '".$typeflag."',
						sub_type_flag					= '".$subtypeflag."',
						low_ranking						= '".$lowranking."',
						iro_type_flag					= '".$irotypeflag."',
						website_type_flag				= '".$websitetypeflag."',
						block_verified					= '".$block_verified."',
						random_compsrch_precalc			= '".$random_compsrch_precalc."',
						business_flag					= '".$businessflag."',
						master_parentid					= '".$masterparentid."',
						master_flag						= '".$masterflag."',
						banner_tag						= '".$bannertag."',
						movie_tag						= '',
						popular_flag					= '".$popularflag."',
						type_flag_actions				= '".$typeflagactions."',
						fb_prefered_language			= '".$fb_prefered_language."',
						guarantee						= '".$guarantee."',
						restrict_display				= '".$restrict_display."'
											
						ON DUPLICATE KEY UPDATE
						
						panindia_sphinxid				= '".$panindia_sphinxid."',
						sphinx_id						= ".$sphinx_id.",						
						parentid						= '".$this->parentid."',
						small_card						= '".$smallcard."',
						medium_card						= '".$mediumcard."',
						extended_card					= '".$extendedcard."',
						update_time						= '".date("Y-m-d H:i:s")."',
						data_city						= '".$data_city."',						
						featured						= '".$featured."',
						national_b2b_flag	 			= '".$national_b2b_flag."',
						jdrr_tag						= '".$jdrrtag."',
						f4info							= '".$f4info."',
						short_url						= '".$short_url."',
						blockforvirtual					= '".$blockforvirtual."',
						VLC_flag						= '".$vlc_flag."',
						type_flag						= '".$typeflag."',
						sub_type_flag					= '".$subtypeflag."',
						low_ranking						= '".$lowranking."',
						iro_type_flag					= '".$irotypeflag."',
						website_type_flag				= '".$websitetypeflag."',
						block_verified					= '".$block_verified."',
						random_compsrch_precalc			= '".$random_compsrch_precalc."',
						business_flag					= '".$businessflag."',
						master_parentid					= '".$masterparentid."',
						master_flag						= '".$masterflag."',
						banner_tag						= '".$bannertag."',
						movie_tag						= '',
						popular_flag					= '".$popularflag."',
						type_flag_actions				= '".$typeflagactions."',
						fb_prefered_language			= '".$fb_prefered_language."',
						guarantee						= '".$guarantee."',
						restrict_display				= '".$restrict_display."'";
					$result_irocard_movie 	=	parent::execQuery($insert_into_irocard_movie, $this->web_conn);
					$result_irocard_movie1 	=	parent::execQuery($insert_into_irocard_movie, $this->conn_split_3);
				}
			
		 
			$sql_select_compsearch = "SELECT panindia_sphinxid,docid,parentid,data_city,main_compname,compname,compname_search,compname_search_wo_space,compname_search_ignore,compname_area,compname_area_search,compname_area_search_wo_space,compname_area_search_ignore,compname_search_processed,compname_search_processed_wo_space,compname_search_processed_ignore,compname_search_processed_ignore_wo_space,compname_area_search_processed,compname_area_search_processed_wo_space,compname_area_search_processed_ignore,compname_area_search_processed_ignore_wo_space,pincode,areaname,areaname_ws,landmark,city,latitude,longitude,paidstatus,address_search,contactperson_search,phone_search,dialable_phonenos,othercity_number,email_search,tollfree,v_number,website,stdcode,data_city_stdcode,blockforvirtual,featured,national_b2b_flag,business_flag,average_rating,pointer_flag,rating_jd,bidperday,company_callcnt,company_callcnt_rolling,random_compsrch_precalc,update_time,active_flag,process_done_flag,synid,short_url,photos_count,data_src,noduplicatecheck,syn_status,area_sensitivity,duplicate_check_phonenos,geocode_accuracy_level,closedown_flag,block_for_sale,VLC_flag,type_flag,sub_type_flag,search_city,star_jd,iro_type_flag,website_type_flag,movie_tag,block_verified,hotcategory,price_range,master_parentid,master_flag,dup_groupid,banner_tag,contacts_present,autosuggest_display,original_date,popular_flag,console_cat_callcnt,trending_flag,average_rating_30,average_rating_90,rating_jd_30,star_jd_30,rating_jd_90,star_jd_90,instant_active_flag,misc_flag FROM webporting_instant_act.tbl_wp1_compsrch_new_architecture_instant WHERE parentid = '".$this->parentid."'  AND done_flag=1";			
			$res_select_compsearch 	=	parent::execQuery($sql_select_compsearch, $this->conn_iro);				
			
			if($res_select_compsearch && mysql_num_rows($res_select_compsearch) > 0)		
			{
									
				while($row_select_compsearch = mysql_fetch_assoc($res_select_compsearch))
				{
					$row_select_compsearch 	 = $this->addslashesArray($row_select_compsearch);					
					$sql_insert_compConsol_instant = "INSERT INTO db_company.tbl_company_consolidated_instant SET						
								 
					 panindia_sphinxid 								=	'".$row_select_compsearch['panindia_sphinxid']."',
					 docid		 									=	'".$docid."',
					 parentid										=	'".$row_select_compsearch['parentid']."',
					 data_city										=	'".$row_select_compsearch['data_city']."',
					 listed_cities									=	'".$listed_cities."',
					 nationallisting_flag 							=	'".$nationallisting_flag."',
					 main_compname									=	'".$row_select_compsearch['main_compname']."',
					 compname										=	'".$row_select_compsearch['compname']."',
					 compname_search								=	'".$row_select_compsearch['compname_search']."',
					 compname_search_wo_space 						=   '".$row_select_compsearch['compname_search_wo_space']."',
					 compname_search_ignore	 						=   '".$row_select_compsearch['compname_search_ignore']."',
					 compname_area									=	'".$row_select_compsearch['compname_area']."',
					 compname_area_search							=	'".$row_select_compsearch['compname_area_search']."',
					 compname_area_search_wo_space 					=	'".$row_select_compsearch['compname_area_search_wo_space']."',
					 compname_area_search_ignore	  				=	'".$row_select_compsearch['compname_area_search_ignore']."',
					 compname_search_processed	  					=	'".$row_select_compsearch['compname_search_processed']."',
					 compname_search_processed_wo_space				=	'".$row_select_compsearch['compname_search_processed_wo_space']."',
					 compname_search_processed_ignore 				= 	'".$row_select_compsearch['compname_search_processed_ignore']."',
					 compname_search_processed_ignore_wo_space 		= 	'".$row_select_compsearch['compname_search_processed_ignore_wo_space']."',
					 compname_area_search_processed 				= 	'".$row_select_compsearch['compname_area_search_processed']."',
					 compname_area_search_processed_wo_space 		= 	'".$row_select_compsearch['compname_area_search_processed_wo_space']."',
					 compname_area_search_processed_ignore 			= 	'".$row_select_compsearch['compname_area_search_processed_ignore']."',
					 compname_area_search_processed_ignore_wo_space	= 	'".$row_select_compsearch['compname_area_search_processed_ignore_wo_space']."',
					 pincode  										= 	'".$row_select_compsearch['pincode']."',
					 areaname 										= 	'".$row_select_compsearch['areaname']."',
					 areaname_ws 									= 	'".$row_select_compsearch['areaname_ws']."',
					 landmark 										= 	'".$row_select_compsearch['landmark']."',
					 city 											= 	'".$row_select_compsearch['city']."',
					 latitude 										= 	'".$row_select_compsearch['latitude']."',
					 longitude 										= 	'".$row_select_compsearch['longitude']."',
					 paidstatus 									= 	'".$row_select_compsearch['paidstatus']."',
					 address_search 								= 	'".$row_select_compsearch['address_search']."',
					 contactperson_search 							= 	'".$row_select_compsearch['contactperson_search']."',
					 phone_search 									= 	'".$row_select_compsearch['phone_search']."',
					 dialable_phonenos 								= 	'".$row_select_compsearch['dialable_phonenos']."',
					 email_search 									= 	'".$row_select_compsearch['email_search']."',
					 tollfree 										= 	'".$row_select_compsearch['tollfree']."',
					 v_number 										= 	'".$row_select_compsearch['v_number']."',
					 website 										= 	'".$row_select_compsearch['website']."',
					 stdcode 										= 	'".$row_select_compsearch['stdcode']."',
					 data_city_stdcode 								= 	'".$row_select_compsearch['data_city_stdcode']."',
					 blockforvirtual 								= 	'".$row_select_compsearch['blockforvirtual']."',
					 featured 										= 	'".$row_select_compsearch['featured']."',
					 national_b2b_flag 								= 	'".$row_select_compsearch['national_b2b_flag']."',
					 business_flag 									= 	'".$row_select_compsearch['business_flag']."',
					 pointer_flag 									= 	'".$row_select_compsearch['pointer_flag']."',
					 rating_jd 										= 	'".$row_select_compsearch['rating_jd']."',
					 bidperday 										= 	'".$row_select_compsearch['bidperday']."',
					 company_callcnt 								= 	'".$company_callcnt."',
					 company_callcnt_rolling 						= 	'".$row_select_compsearch['company_callcnt_rolling']."',
					 random_compsrch_precalc 						= 	'".$row_select_compsearch['random_compsrch_precalc']."',
					 update_time 									= 	'".$row_select_compsearch['update_time']."',
					 active_flag 									= 	'".$row_select_compsearch['active_flag']."',
					 group_flag 									= 	'',
					 group_callcount 								= 	'',
					 multiple 										= 	'',
					 process_done_flag 								= 	'".$row_select_compsearch['process_done_flag']."',
					 synid 											= 	'".$row_select_compsearch['synid']."',
					 short_url 										= 	'".$row_select_compsearch['short_url']."',
					 photos_count 									= 	'".$row_select_compsearch['photos_count']."',
					 data_src 										= 	'".$row_select_compsearch['data_src']."',
					 noduplicatecheck 								= 	'".$row_select_compsearch['noduplicatecheck']."',
					 average_rating 								= 	'".$row_select_compsearch['average_rating']."',
					 syn_status 									= 	'".$row_select_compsearch['syn_status']."',
					 percentage_slab 								= 	'".$percentage_slab."',
					 area_sensitivity 								= 	'".$row_select_compsearch['area_sensitivity']."',
					 duplicate_check_phonenos 						= 	'".$row_select_compsearch['duplicate_check_phonenos']."',
					 geocode_accuracy_level						 	= 	'".$row_select_compsearch['geocode_accuracy_level']."',
					 closedown_flag 								= 	'".$row_select_compsearch['closedown_flag']."',
					 block_for_sale 								= 	'".$row_select_compsearch['block_for_sale']."',
					 VLC_flag 										= 	'".$row_select_compsearch['VLC_flag']."',
					 type_flag 										= 	'".$row_select_compsearch['type_flag']."',
					 sub_type_flag 									= 	'".$row_select_compsearch['sub_type_flag']."',
					 search_city 									= 	'".$row_select_compsearch['search_city']."',
					 star_jd 										= 	'".$row_select_compsearch['star_jd']."',
					 iro_type_flag 									= 	'".$row_select_compsearch['iro_type_flag']."',
					 website_type_flag 								= 	'".$row_select_compsearch['website_type_flag']."',
					 block_verified 								= 	'".$row_select_compsearch['block_verified']."',
					 hotcategory 									= 	'".$row_select_compsearch['hotcategory']."',
					 master_parentid 								= 	'".$row_select_compsearch['master_parentid']."',
					 master_flag 									= 	'".$row_select_compsearch['master_flag']."',
					 dup_groupid 									= 	'".$row_select_compsearch['dup_groupid']."',
					 banner_tag 									= 	'".$row_select_compsearch['banner_tag']."',
					 contacts_present 								= 	'".$row_select_compsearch['contacts_present']."',
					 autosuggest_display 							= 	'".$row_select_compsearch['autosuggest_display']."',
					 original_date 									= 	'".$row_select_compsearch['original_date']."',
					 movie_tag 										= 	'".$row_select_compsearch['movie_tag']."',
					 popular_flag 									= 	'".$row_select_compsearch['popular_flag']."',
					 profile_photo_path 							= 	'".$row_select_compsearch['profile_photo_path']."',
					 instant_active_flag 							= 	'".$row_select_compsearch['instant_active_flag']."',
					 misc_flag 										= 	'".$row_select_compsearch['misc_flag']."',
					 mid 											= 	'".$mid."',
					 muser 											= 	'".$muser."'
					 
					 ON DUPLICATE KEY UPDATE
					 
					 panindia_sphinxid 								=	'".$row_select_compsearch['panindia_sphinxid']."',						 
					 parentid										=	'".$row_select_compsearch['parentid']."',
					 data_city										=	'".$row_select_compsearch['data_city']."',
					 listed_cities									=	'".$listed_cities."',
					 nationallisting_flag							=	'".$nationallisting_flag."',
					 main_compname									=	'".$row_select_compsearch['main_compname']."',
					 compname										=	'".$row_select_compsearch['compname']."',
					 compname_search								= 	'".$row_select_compsearch['compname_search']."',
					 compname_search_wo_space 						= 	'".$row_select_compsearch['compname_search_wo_space']."',
					 compname_search_ignore	 						= 	'".$row_select_compsearch['compname_search_ignore']."',
					 compname_area									= 	'".$row_select_compsearch['compname_area']."',
					 compname_area_search							= 	'".$row_select_compsearch['compname_area_search']."',
					 compname_area_search_wo_space 					= 	'".$row_select_compsearch['compname_area_search_wo_space']."',
					 compname_area_search_ignore	  				= 	'".$row_select_compsearch['compname_area_search_ignore']."',
					 compname_search_processed	  					= 	'".$row_select_compsearch['compname_search_processed']."',
					 compname_search_processed_wo_space				=	'".$row_select_compsearch['compname_search_processed_wo_space']."',
					 compname_search_processed_ignore 				= 	'".$row_select_compsearch['compname_search_processed_ignore']."',
					 compname_search_processed_ignore_wo_space 		= 	'".$row_select_compsearch['compname_search_processed_ignore_wo_space']."',
					 compname_area_search_processed 				= 	'".$row_select_compsearch['compname_area_search_processed']."',
					 compname_area_search_processed_wo_space 		= 	'".$row_select_compsearch['compname_area_search_processed_wo_space']."',
					 compname_area_search_processed_ignore 			= 	'".$row_select_compsearch['compname_area_search_processed_ignore']."',
					 compname_area_search_processed_ignore_wo_space = 	'".$row_select_compsearch['compname_area_search_processed_ignore_wo_space']."',
					 pincode  										= 	'".$row_select_compsearch['pincode']."',
					 areaname 										= 	'".$row_select_compsearch['areaname']."',
					 areaname_ws 									= 	'".$row_select_compsearch['areaname_ws']."',
					 landmark 										= 	'".$row_select_compsearch['landmark']."',
					 city 											= 	'".$row_select_compsearch['city']."',
					 latitude 										= 	'".$row_select_compsearch['latitude']."',
					 longitude 										= 	'".$row_select_compsearch['longitude']."',
					 paidstatus 									= 	'".$row_select_compsearch['paidstatus']."',
					 address_search 								= 	'".$row_select_compsearch['address_search']."',
					 contactperson_search 							= 	'".$row_select_compsearch['contactperson_search']."',
					 phone_search 									= 	'".$row_select_compsearch['phone_search']."',
					 dialable_phonenos 								= 	'".$row_select_compsearch['dialable_phonenos']."',
					 email_search 									= 	'".$row_select_compsearch['email_search']."',
					 tollfree 										= 	'".$row_select_compsearch['tollfree']."',
					 v_number 										= 	'".$row_select_compsearch['v_number']."',
					 website 										= 	'".$row_select_compsearch['website']."',
					 stdcode 										= 	'".$row_select_compsearch['stdcode']."',
					 data_city_stdcode 								= 	'".$row_select_compsearch['data_city_stdcode']."',
					 blockforvirtual 								= 	'".$row_select_compsearch['blockforvirtual']."',
					 featured 										= 	'".$row_select_compsearch['featured']."',
					 national_b2b_flag 								= 	'".$row_select_compsearch['national_b2b_flag']."',
					 business_flag 									= 	'".$row_select_compsearch['business_flag']."',
					 pointer_flag 									= 	'".$row_select_compsearch['pointer_flag']."',
					 rating_jd 										= 	'".$row_select_compsearch['rating_jd']."',
					 bidperday 										= 	'".$row_select_compsearch['bidperday']."',
					 company_callcnt 								= 	'".$company_callcnt."',
					 company_callcnt_rolling 						= 	'".$row_select_compsearch['company_callcnt_rolling']."',
					 random_compsrch_precalc 						= 	'".$row_select_compsearch['random_compsrch_precalc']."',
					 update_time 									= 	'".$row_select_compsearch['update_time']."',
					 active_flag 									= 	'".$row_select_compsearch['active_flag']."',
					 group_flag 									= 	'',
					 group_callcount 								= 	'',
					 multiple 										= 	'',
					 process_done_flag 								= 	'".$row_select_compsearch['process_done_flag']."',						
					 short_url 										= 	'".$row_select_compsearch['short_url']."',
					 photos_count 									= 	'".$row_select_compsearch['photos_count']."',
					 data_src 										= 	'".$row_select_compsearch['data_src']."',
					 noduplicatecheck 								= 	'".$row_select_compsearch['noduplicatecheck']."',
					 average_rating 								= 	'".$row_select_compsearch['average_rating']."',
					 syn_status 									= 	'".$row_select_compsearch['syn_status']."',
					 percentage_slab 								= 	'".$percentage_slab."',
					 area_sensitivity 								= 	'".$row_select_compsearch['area_sensitivity']."',
					 duplicate_check_phonenos 						= 	'".$row_select_compsearch['duplicate_check_phonenos']."',
					 geocode_accuracy_level 						= 	'".$row_select_compsearch['geocode_accuracy_level']."',
					 closedown_flag 								= 	'".$row_select_compsearch['closedown_flag']."',
					 block_for_sale 								= 	'".$row_select_compsearch['block_for_sale']."',
					 VLC_flag 										= 	'".$row_select_compsearch['VLC_flag']."',
					 type_flag 										= 	'".$row_select_compsearch['type_flag']."',
					 sub_type_flag 									= 	'".$row_select_compsearch['sub_type_flag']."',
					 search_city 									= 	'".$row_select_compsearch['search_city']."',
					 star_jd 										= 	'".$row_select_compsearch['star_jd']."',
					 iro_type_flag 									= 	'".$row_select_compsearch['iro_type_flag']."',
					 website_type_flag 								= 	'".$row_select_compsearch['website_type_flag']."',
					 block_verified 								= 	'".$row_select_compsearch['block_verified']."',
					 hotcategory 									= 	'".$row_select_compsearch['hotcategory']."',
					 master_parentid 								= 	'".$row_select_compsearch['master_parentid']."',
					 master_flag 									= 	'".$row_select_compsearch['master_flag']."',
					 dup_groupid 									= 	'".$row_select_compsearch['dup_groupid']."',
					 banner_tag 									= 	'".$row_select_compsearch['banner_tag']."',
					 contacts_present 								= 	'".$row_select_compsearch['contacts_present']."',
					 autosuggest_display 							= 	'".$row_select_compsearch['autosuggest_display']."',
					 original_date 									= 	'".$row_select_compsearch['original_date']."',
					 movie_tag 										= 	'".$row_select_compsearch['movie_tag']."',
					 popular_flag 									= 	'".$row_select_compsearch['popular_flag']."',
					 profile_photo_path						 		= 	'".$row_select_compsearch['profile_photo_path']."',
					 instant_active_flag 							= 	'".$row_select_compsearch['instant_active_flag']."',
					 misc_flag 										= 	'".$row_select_compsearch['misc_flag']."',
					 mid 											= 	'".$mid."',
					 muser 											= 	'".$muser."'";		
					$res_insert_compConsol_instant 	=	parent::execQuery($sql_insert_compConsol_instant, $this->conn_webedit);
					$res_insert_compConsol_instant_1 	=	parent::execQuery($sql_insert_compConsol_instant, $this->conn_split_3);
				}
				 
				if(!$res_insert_compConsol_instant){
					$success_flag = 0;
					$this->insertLog("tbl_company_consolidated",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"db_company.tbl_company_consolidated insertion failed ==>Query==>".$sql_insert_compConsol_instant."==>Result==>".$res_insert_compConsol_instant);
				}else{
					$this->insertLog("tbl_company_consolidated",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"insert in to db_company.tbl_company_consolidated inserted sucessfully");
				}
			 
					
					 
				$fp_pkg_np_srch = 0;
				$paid_update_done = 0;
				if($online_payment){
					
					
					
					$this->insertLog("online_payment_check",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"online_payment_check data found ==>sqlOnlinePayment ==>".$sqlOnlinePayment ."--sqlPDGBudget ==>".$sqlPDGBudget."==>resOnlinePayment==>". $resOnlinePayment."--resPDGBudget".$resPDGBudget);
					
					$sql_del_wp2_web = "DELETE FROM webporting_instant_act.tbl_wp2_compcatarea_web_instant WHERE parentid = '".$this->parentid."'";
					$res_del_wp2_web =	parent::execQuery($sql_del_wp2_web, $this->conn_iro);
				
					$sql_del_wp2_ddg = "DELETE FROM webporting_instant_act.tbl_wp2_compcatarea_web_ddg_instant WHERE parentid = '".$this->parentid."'";
					$res_del_wp2_ddg = parent::execQuery($sql_del_wp2_ddg, $this->conn_iro);
					
					
					$sql_sp_catcall = "call webporting_instant_act.sp_online_payment('".$this->parentid."')";
					$res_sp_catcall	= parent::execQuery($sql_sp_catcall, $this->conn_iro);
					$row_sp_catcall	= mysql_fetch_assoc($res_sp_catcall);
				
					if($row_sp_catcall['response'] != 'Success'){
						$success_flag = 0;
						$this->insertLog("sp_online_payment",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"webporting_instant_act.sp_online_payment call failed ==>Query==>".$sql_sp_catcall ."==>Result==>". $res_sp_catcall);
					}else{
						$this->insertLog("sp_online_payment",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"webporting_instant_act.sp_online_payment call sucessfully==>Query==>".$sql_sp_catcall ."==>Result==>". $res_sp_catcall);
					}
					$sql_delete_split = "DELETE FROM tbl_wp2_compcatarea_web_instant_inc WHERE docid='".$docid."'";
					 
				    //$res_delete_split  	= parent::execQuery($sql_delete_split, $this->conn_split);
				    //$res_delete_split1  = parent::execQuery($sql_delete_split, $this->conn_split_2);
				    $res_delete_split2  = parent::execQuery($sql_delete_split, $this->conn_split_3);
					
					
					
					$sql_wp2_web = "SELECT * FROM webporting_instant_act.tbl_wp2_compcatarea_web_instant WHERE docid='".$docid."'";
					$res_wp2_web =	parent::execQuery($sql_wp2_web, $this->conn_iro);
					$split_values='';
					if($res_wp2_web && mysql_num_rows($res_wp2_web) > 0)		
					{	
						$fp_pkg_np_srch = 1;
						while($row_wp2_web = mysql_fetch_assoc($res_wp2_web))
						{
							$row_wp2_web 	 = $this->addslashesArray($row_wp2_web);
							
							$split_values .= "('".$row_wp2_web['panindia_sphinxid']."','".$row_wp2_web['docid']."','".$row_wp2_web['national_catid']."','".$row_wp2_web['stdcode']."','".$row_wp2_web['parentid']."','".$row_wp2_web['pincode']."','".$row_wp2_web['areaname']."','".$row_wp2_web['areaname_ws']."','".$row_wp2_web['city']."','".$row_wp2_web['data_city']."','".$row_wp2_web['state']."','".$row_wp2_web['fulladdress']."','".$row_wp2_web['latitude']."','".$row_wp2_web['longitude']."','".$row_wp2_web['phonenos']."','".$row_wp2_web['email']."','".$row_wp2_web['active_flag']."','".$row_wp2_web['paid']."','".$row_wp2_web['contribution']."','".$row_wp2_web['perdaycontribution']."','".$company_callcnt."','".$row_wp2_web['company_callcnt_rolling']."','".$row_wp2_web['rating_priority']."','".$row_wp2_web['rating_compcat']."','".$row_wp2_web['rating_establishments']."','".$row_wp2_web['rating_jd']."','".$row_wp2_web['rating_web']."','".$row_wp2_web['weight']."','".$row_wp2_web['radiusofsignificance']."','".$row_wp2_web['table_no']."','".$row_wp2_web['photos_count']."','".$row_wp2_web['featured']."','".$row_wp2_web['national_b2b_flag']."','".$row_wp2_web['updatedby']."','".$row_wp2_web['updateddate']."','".$row_wp2_web['backenduptdate']."','".$row_wp2_web['random_compsrch_precalc']."','".$row_wp2_web['star_jd']."','".$row_wp2_web['price_range']."','".$row_wp2_web['type_flag']."','".$row_wp2_web['dup_groupid']."','".$row_wp2_web['sf_guarantee']."','".$row_wp2_web['console_cat_callcnt']."','".$row_wp2_web['trending_flag']."','".$row_wp2_web['rating_jd_30']."','".$row_wp2_web['star_jd_30']."','".$row_wp2_web['rating_jd_90']."','".$row_wp2_web['star_jd_90']."','".$row_wp2_web['attribute_search']."','".$row_wp2_web['reach_count']."','".$row_wp2_web['reach_count_rolling']."','".$row_wp2_web['unique_user_rolling']."','".$row_wp2_web['bidperday']."','".$row_wp2_web['contract_bidperday']."','".$row_wp2_web['cat_contribution']."','".$row_wp2_web['physical_pincode']."'),";
							//   
							/*
							$sql_inst_wp2_web="INSERT INTO db_split.tbl_wp2_compcatarea_web_instant_inc SET 
								
								panindia_sphinxid 		= '".$row_wp2_web['panindia_sphinxid']."',
								docid			  		= '".$row_wp2_web['docid']."',							
								national_catid	  		= '".$row_wp2_web['national_catid']."',							
								stdcode			  		= '".$row_wp2_web['stdcode']."',							
								parentid		  		= '".$row_wp2_web['parentid']."',							
								pincode			  		= '".$row_wp2_web['pincode']."',							
								areaname		  		= '".$row_wp2_web['areaname']."',							
								areaname_ws		  		= '".$row_wp2_web['areaname_ws']."',							
								city			  		= '".$row_wp2_web['city']."',							
								data_city		  		= '".$row_wp2_web['data_city']."',							
								state			  		= '".$row_wp2_web['state']."',							
								fulladdress		  		= '".$row_wp2_web['fulladdress']."',
								latitude		  		= '".$row_wp2_web['latitude']."',
								longitude		  		= '".$row_wp2_web['longitude']."',
								phonenos		  		= '".$row_wp2_web['phonenos']."',
								email			  		= '".$row_wp2_web['email']."',
								active_flag		  		= '".$row_wp2_web['active_flag']."',							
								paid			  		= '".$row_wp2_web['paid']."',							
								contribution	  		= '".$row_wp2_web['contribution']."',
								perdaycontribution		= '".$row_wp2_web['perdaycontribution']."',
								company_callcnt	  		= '".$company_callcnt."',							
								company_callcnt_rolling = '".$row_wp2_web['company_callcnt_rolling']."',							
								rating_priority	  		= '".$row_wp2_web['rating_priority']."',							
								rating_compcat	  		= '".$row_wp2_web['rating_compcat']."',							
								rating_establishments 	= '".$row_wp2_web['rating_establishments']."',
								rating_jd		  		= '".$row_wp2_web['rating_jd']."',							
								rating_web		  		= '".$row_wp2_web['rating_web']."',
								weight			  		= '".$row_wp2_web['weight']."',
								radiusofsignificance 	= '".$row_wp2_web['radiusofsignificance']."',
								table_no		  		= '".$row_wp2_web['table_no']."',
								photos_count	  		= '".$row_wp2_web['photos_count']."',
								featured		  		= '".$row_wp2_web['featured']."',							
								national_b2b_flag 		= '".$row_wp2_web['national_b2b_flag']."',							
								updatedby		  		= '".$row_wp2_web['updatedby']."',
								updateddate		  		= '".$row_wp2_web['updateddate']."',
								backenduptdate	  		= '".$row_wp2_web['backenduptdate']."',
								random_compsrch_precalc = '".$row_wp2_web['random_compsrch_precalc']."',							
								star_jd			  		= '".$row_wp2_web['star_jd']."',
								price_range		  		= '".$row_wp2_web['price_range']."',
								type_flag		  		= '".$row_wp2_web['type_flag']."',
								dup_groupid		  		= '".$row_wp2_web['dup_groupid']."',
								sf_guarantee	  		= '".$row_wp2_web['sf_guarantee']."',
								console_cat_callcnt 	= '".$row_wp2_web['console_cat_callcnt']."',
								trending_flag			= '".$row_wp2_web['trending_flag']."',
								rating_jd_30			= '".$row_wp2_web['rating_jd_30']."',
								star_jd_30				= '".$row_wp2_web['star_jd_30']."',
								rating_jd_90			= '".$row_wp2_web['rating_jd_90']."',
								star_jd_90				= '".$row_wp2_web['star_jd_90']."',
								attribute_search    	= '".$row_wp2_web['attribute_search']."',
								reach_count				= '".$row_wp2_web['reach_count']."',
								reach_count_rolling		= '".$row_wp2_web['reach_count_rolling']."',
								unique_user_rolling		= '".$row_wp2_web['unique_user_rolling']."',
								bidperday				= '".$row_wp2_web['bidperday']."',
								contract_bidperday		= '".$row_wp2_web['contract_bidperday']."',
								cat_contribution		= '".$row_wp2_web['cat_contribution']."',
								physical_pincode		= '".$row_wp2_web['physical_pincode']."'
								
								ON DUPLICATE KEY UPDATE 
								
								panindia_sphinxid 		= '".$row_wp2_web['panindia_sphinxid']."',
								national_catid	  		= '".$row_wp2_web['national_catid']."',							
								stdcode			  		= '".$row_wp2_web['stdcode']."',							
								parentid		  		= '".$row_wp2_web['parentid']."',							
								pincode			  		= '".$row_wp2_web['pincode']."',							
								areaname		  		= '".$row_wp2_web['areaname']."',							
								areaname_ws		  		= '".$row_wp2_web['areaname_ws']."',							
								city			  		= '".$row_wp2_web['city']."',							
								data_city		  		= '".$row_wp2_web['data_city']."',							
								state			  		= '".$row_wp2_web['state']."',							
								fulladdress		  		= '".$row_wp2_web['fulladdress']."',
								latitude		  		= '".$row_wp2_web['latitude']."',
								longitude		  		= '".$row_wp2_web['longitude']."',
								phonenos		  		= '".$row_wp2_web['phonenos']."',
								email			  		= '".$row_wp2_web['email']."',
								active_flag		  		= '".$row_wp2_web['active_flag']."',							
								paid			  		= '".$row_wp2_web['paid']."',							
								contribution	  		= '".$row_wp2_web['contribution']."',
								perdaycontribution		= '".$row_wp2_web['perdaycontribution']."',
								company_callcnt	  		= '".$company_callcnt."',							
								company_callcnt_rolling = '".$row_wp2_web['company_callcnt_rolling']."',							
								rating_priority	  		= '".$row_wp2_web['rating_priority']."',							
								rating_compcat	  		= '".$row_wp2_web['rating_compcat']."',							
								rating_establishments 	= '".$row_wp2_web['rating_establishments']."',
								rating_jd		  		= '".$row_wp2_web['rating_jd']."',							
								rating_web		  		= '".$row_wp2_web['rating_web']."',
								weight			  		= '".$row_wp2_web['weight']."',
								radiusofsignificance 	= '".$row_wp2_web['radiusofsignificance']."',
								table_no		  		= '".$row_wp2_web['table_no']."',
								photos_count	  		= '".$row_wp2_web['photos_count']."',
								featured		  		= '".$row_wp2_web['featured']."',							
								national_b2b_flag 		= '".$row_wp2_web['national_b2b_flag']."',							
								updatedby		  		= '".$row_wp2_web['updatedby']."',
								updateddate		  		= '".$row_wp2_web['updateddate']."',
								backenduptdate	  		= '".$row_wp2_web['backenduptdate']."',
								random_compsrch_precalc = '".$row_wp2_web['random_compsrch_precalc']."',							
								star_jd			  		= '".$row_wp2_web['star_jd']."',
								price_range		  		= '".$row_wp2_web['price_range']."',
								type_flag		  		= '".$row_wp2_web['type_flag']."',
								dup_groupid		  		= '".$row_wp2_web['dup_groupid']."',
								sf_guarantee	  		= '".$row_wp2_web['sf_guarantee']."',
								console_cat_callcnt 	= '".$row_wp2_web['console_cat_callcnt']."',
								trending_flag			= '".$row_wp2_web['trending_flag']."',
								rating_jd_30			= '".$row_wp2_web['rating_jd_30']."',
								star_jd_30				= '".$row_wp2_web['star_jd_30']."',
								rating_jd_90			= '".$row_wp2_web['rating_jd_90']."',
								star_jd_90				= '".$row_wp2_web['star_jd_90']."',
								attribute_search    	= '".$row_wp2_web['attribute_search']."',
								reach_count				= '".$row_wp2_web['reach_count']."',
								reach_count_rolling		= '".$row_wp2_web['reach_count_rolling']."',
								unique_user_rolling		= '".$row_wp2_web['unique_user_rolling']."',
								bidperday				= '".$row_wp2_web['bidperday']."',
								contract_bidperday		= '".$row_wp2_web['contract_bidperday']."',
								cat_contribution		= '".$row_wp2_web['cat_contribution']."',
								physical_pincode		= '".$row_wp2_web['physical_pincode']."'" ;
								
							$res_inst_wp2_web  	= parent::execQuery($sql_inst_wp2_web, $this->conn_split);
							$res_inst_wp2_web2  = parent::execQuery($sql_inst_wp2_web, $this->conn_split_2);
							   $res_inst_wp2_web3  = parent::execQuery($sql_inst_wp2_web, $this->conn_split_3);
							
							*/
						}
						$sql_inst_wp2_web="INSERT INTO  db_split.tbl_wp2_compcatarea_web_instant_inc (panindia_sphinxid,docid,national_catid,stdcode,parentid,pincode,areaname,areaname_ws,city,data_city,state,fulladdress,latitude,longitude,phonenos,email,active_flag,paid,contribution,perdaycontribution,company_callcnt,company_callcnt_rolling,rating_priority,rating_compcat,rating_establishments,rating_jd,rating_web,weight,radiusofsignificance,table_no,photos_count,featured,national_b2b_flag,updatedby,updateddate,backenduptdate,random_compsrch_precalc,star_jd,price_range,type_flag,dup_groupid,sf_guarantee,console_cat_callcnt,trending_flag,rating_jd_30,star_jd_30,rating_jd_90,star_jd_90,attribute_search,reach_count,reach_count_rolling,unique_user_rolling,bidperday,contract_bidperday,cat_contribution,physical_pincode) VALUES ".trim($split_values,",");
						 
						//$res_inst_wp2_web  	= parent::execQuery($sql_inst_wp2_web, $this->conn_split);
						//$res_inst_wp2_web2  = parent::execQuery($sql_inst_wp2_web, $this->conn_split_2);
						$res_inst_wp2_web3  = parent::execQuery($sql_inst_wp2_web, $this->conn_split_3);
						 
						$this->insertLog("tbl_wp2_compcatarea_web_instant_inc",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"insert in to db_split.tbl_wp2_compcatarea_web_instant_inc sucessfully");
					}
					else{
						$this->insertLog("tbl_wp2_compcatarea_web_instant_inc",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"No data found in webporting_instant_act.tbl_wp2_compcatarea_web_instant");
					}
					$paidstatus_flag_updt = 0;
					
					$delete_inst_wp2_ddg="DELETE FROM tbl_wp2_compcatarea_web_ddg_instant_inc WHERE docid='".$docid."'";
					
					//$res_delete_inst_wp2_ddg  =	parent::execQuery($delete_inst_wp2_ddg, $this->conn_split);
					//$res_delete_inst_wp2_ddg1  =	parent::execQuery($delete_inst_wp2_ddg, $this->conn_split_2);					
					$res_delete_inst_wp2_ddg2  =	parent::execQuery($delete_inst_wp2_ddg, $this->conn_split_3);
					
					$sql_wp2_ddg = "select * from webporting_instant_act.tbl_wp2_compcatarea_web_ddg_instant where docid='".$docid."'";
					$res_wp2_ddg =	parent::execQuery($sql_wp2_ddg, $this->conn_iro);
					$wp2_ddg_values='';
					if($res_wp2_ddg && mysql_num_rows($res_wp2_ddg) > 0)		
					{
						$fp_pkg_np_srch = 1;
						$paidstatus_flag_updt = 1; 
						while($row_wp2_web = mysql_fetch_assoc($res_wp2_ddg))
						{
							$row_wp2_web 	 = $this->addslashesArray($row_wp2_web);
							//print_r($row_wp2_web);
						 
							$wp2_ddg_values .= "('".$row_wp2_web['docid']."','".$row_wp2_web['national_catid']."','".$row_wp2_web['parentid']."','".$row_wp2_web['pincode']."','".$row_wp2_web['position_flag']."','".$row_wp2_web['lcf']."','".$row_wp2_web['hcf']."','".$row_wp2_web['physical_pincode']."','".$row_wp2_web['data_city']."','".addslashes($row_wp2_web['fulladdress'])."','".$row_wp2_web['latitude']."','".$row_wp2_web['longitude']."','".$row_wp2_web['phonenos']."','".$row_wp2_web['active_flag']."','".$row_wp2_web['paid']."','".$row_wp2_web['table_no']."','".$row_wp2_web['bidperday']."','".$row_wp2_web['contract_bidperday']."','".$row_wp2_web['cat_contribution']."','".$row_wp2_web['updatedby']."','".$row_wp2_web['updateddate']."','".$row_wp2_web['backenduptdate']."','".$row_wp2_web['dup_groupid']."'),";
							/*$sql_inst_wp2_ddg="INSERT INTO db_split.tbl_wp2_compcatarea_web_ddg_instant_inc SET 
									docid 				= '".$row_wp2_ddg['docid']."',
									national_catid   	= '".$row_wp2_ddg['national_catid']."',
									parentid		 	= '".$row_wp2_ddg['parentid']."',
									pincode			 	= '".$row_wp2_ddg['pincode']."',
									position_flag	 	= '".$row_wp2_ddg['position_flag']."',
									lcf				 	= '".$row_wp2_ddg['lcf']."',
									hcf				 	= '".$row_wp2_ddg['hcf']."',
									physical_pincode 	= '".$row_wp2_ddg['physical_pincode']."',
									data_city		 	= '".$row_wp2_ddg['data_city']."',
									fulladdress 	 	= '".$row_wp2_ddg['fulladdress']."',
									latitude		 	= '".$row_wp2_ddg['latitude']."',
									longitude		 	= '".$row_wp2_ddg['longitude']."',
									phonenos		 	= '".$row_wp2_ddg['phonenos']."',
									active_flag		 	= '".$row_wp2_ddg['active_flag']."',
									paid			 	= '".$row_wp2_ddg['paid']."',
									table_no		 	= '".$row_wp2_ddg['table_no']."',
									bidperday		 	= '".$row_wp2_ddg['bidperday']."',
									contract_bidperday	= '".$row_wp2_ddg['contract_bidperday']."',
									cat_contribution	= '".$row_wp2_ddg['cat_contribution']."',
									updatedby		 	= '".$row_wp2_ddg['updatedby']."',
									updateddate		 	= '".$row_wp2_ddg['updateddate']."',
									backenduptdate	 	= '".$row_wp2_ddg['backenduptdate']."',
									dup_groupid		 	= '".$row_wp2_ddg['dup_groupid']."'
									
									ON DUPLICATE KEY UPDATE
									
									national_catid   	= '".$row_wp2_ddg['national_catid']."',
									parentid		 	= '".$row_wp2_ddg['parentid']."',
									pincode			 	= '".$row_wp2_ddg['pincode']."',
									position_flag	 	= '".$row_wp2_ddg['position_flag']."',
									lcf				 	= '".$row_wp2_ddg['lcf']."',
									hcf				 	= '".$row_wp2_ddg['hcf']."',
									physical_pincode 	= '".$row_wp2_ddg['physical_pincode']."',
									data_city		 	= '".$row_wp2_ddg['data_city']."',
									fulladdress 	 	= '".$row_wp2_ddg['fulladdress']."',
									latitude		 	= '".$row_wp2_ddg['latitude']."',
									longitude		 	= '".$row_wp2_ddg['longitude']."',
									phonenos		 	= '".$row_wp2_ddg['phonenos']."',
									active_flag		 	= '".$row_wp2_ddg['active_flag']."',
									paid			 	= '".$row_wp2_ddg['paid']."',
									table_no		 	= '".$row_wp2_ddg['table_no']."',
									bidperday		 	= '".$row_wp2_ddg['bidperday']."',
									contract_bidperday	= '".$row_wp2_ddg['contract_bidperday']."',
									cat_contribution	= '".$row_wp2_ddg['cat_contribution']."',
									updatedby		 	= '".$row_wp2_ddg['updatedby']."',
									updateddate		 	= '".$row_wp2_ddg['updateddate']."',
									backenduptdate	 	= '".$row_wp2_ddg['backenduptdate']."',
									dup_groupid		 	= '".$row_wp2_ddg['dup_groupid']."'" ;
									
								$res_inst_wp2_ddg  	=	parent::execQuery($sql_inst_wp2_ddg, $this->conn_split);
								$res_inst_wp2_ddg2  =	parent::execQuery($sql_inst_wp2_ddg, $this->conn_split_2);					
								$res_inst_wp2_ddg3  =	parent::execQuery($sql_inst_wp2_ddg, $this->conn_split_3);	*/				
						}
						
							 
						$sql_inst_wp2_ddg="INSERT INTO db_split.tbl_wp2_compcatarea_web_ddg_instant_inc (docid ,national_catid,parentid,pincode,position_flag,lcf,hcf,physical_pincode,data_city,fulladdress,latitude,longitude,phonenos,active_flag,paid,table_no,bidperday,contract_bidperday,cat_contribution,updatedby,updateddate,backenduptdate,dup_groupid) 
						VALUES ".trim($wp2_ddg_values,",");
						
						//$res_inst_wp2_ddg  	=	parent::execQuery($sql_inst_wp2_ddg, $this->conn_split);
						//$res_inst_wp2_ddg2  =	parent::execQuery($sql_inst_wp2_ddg, $this->conn_split_2);
						$res_inst_wp2_ddg3  =	 parent::execQuery($sql_inst_wp2_ddg, $this->conn_split_3);
						
						
						$this->insertLog("tbl_wp2_compcatarea_web_ddg_instant_inc",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"insert in to db_split.tbl_wp2_compcatarea_web_ddg_instant_inc sucessfully");	
					}
					else{
						$this->insertLog("tbl_wp2_compcatarea_web_ddg_instant_inc",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"No data found in webporting_instant_act.tbl_wp2_compcatarea_web_ddg_instant");	
					}
					
					if($fp_pkg_np_srch == 1){
						if($paidstatus_flag_updt == 1){
							$paid_update_done = 1;
							$sqlPaidStIroCards = "UPDATE db_company.tbl_iro_cards_instant SET paidstatus = 6 WHERE docid = '".$docid."'";
							$resPaidStIroCards = parent::execQuery($sqlPaidStIroCards, $this->conn_webedit);
							$resPaidStIroCards1 = parent::execQuery($sqlPaidStIroCards, $this->conn_split_3);
							$sqlPaidStConsolidate = "UPDATE db_company.tbl_company_consolidated_instant SET paidstatus = 6 WHERE docid = '".$docid."'";
							$resPaidStConsolidate = parent::execQuery($sqlPaidStConsolidate, $this->conn_webedit);
							$resPaidStConsolidate1 = parent::execQuery($sqlPaidStConsolidate, $this->conn_split_3);
						}
						$sql_sp_instantPort = "call db_split.sp_instant_porting('".$docid."')";
						//$res_sp_instantPort	= parent::execQuery($sql_sp_instantPort, $this->conn_split);
						
						$sql_sp_instantPort_2 = "call db_split.sp_instant_porting('".$docid."')";
						//$res_sp_instantPort_2 = parent::execQuery($sql_sp_instantPort_2, $this->conn_split_2);
						$res_sp_instantPort = parent::execQuery($sql_sp_instantPort_2, $this->conn_split_3);
						
											
						$row_sp_instantPort	= mysql_fetch_assoc($res_sp_instantPort);
						
						if($row_sp_instantPort['response'] != 'Success'){
							$success_flag = 0;
							$this->insertLog("sp_instant_porting",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"db_split.sp_instant_porting sp call failed==>Query==>".$sql_sp_instantPort."==>Result==>".$res_sp_instantPort);
						}else{
							$this->insertLog("sp_instant_porting",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"db_split.sp_instant_porting sp call sucessfully==>Query==>".$sql_sp_instantPort."==>Result==>".$res_sp_instantPort);
						}
						
					}else{
						$this->insertLog("sp_instant_porting",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"db_split.sp_instant_porting not called , fp_pkg_np_srch : ".$fp_pkg_np_srch);
					}
				}else{
					$this->insertLog("online_payment_check",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"online_payment_check data not found ==>sqlOnlinePayment ==>".$sqlOnlinePayment ."--sqlPDGBudget ==>".$sqlPDGBudget."==>resOnlinePayment==>". $resOnlinePayment."--resPDGBudget".$resPDGBudget);
				}
				if($paid_update_done !=1){
					$sqlPaidStatus = "SELECT group_concat(campaignid) as campaignids FROM tbl_companymaster_finance WHERE parentid = '".$this->parentid."' AND balance>0 AND expired=0";
					$resPaidStatus = parent::execQuery($sqlPaidStatus, $this->conn_fnc_slave);
					if($resPaidStatus && mysql_num_rows($resPaidStatus)>0){
						$row_paid_status = mysql_fetch_assoc($resPaidStatus);
						$campaignids = trim($row_paid_status['campaignids']);
						$campaignids_arr = array();
						$campaignids_arr = explode(",",$campaignids);
						$campaignids_arr = array_filter(array_unique($campaignids_arr));
						if(count($campaignids_arr)>0){
							$paid_flag_val = 0;
							if(in_array("2",$campaignids_arr)){
								$paid_flag_val = 6;
							}else if((in_array("1",$campaignids_arr)) || (in_array("3",$campaignids_arr))){
								$paid_flag_val = 5;
							}else{
								$paid_flag_val = 3;
							}
							$sqlPaidStIroCards = "UPDATE db_company.tbl_iro_cards_instant SET paidstatus = '".$paid_flag_val."' WHERE docid = '".$docid."'";
							$resPaidStIroCards = parent::execQuery($sqlPaidStIroCards, $this->conn_webedit);
							$resPaidStIroCards1 = parent::execQuery($sqlPaidStIroCards, $this->conn_split_3);
							$sqlPaidStConsolidate = "UPDATE db_company.tbl_company_consolidated_instant SET paidstatus = '".$paid_flag_val."' WHERE docid = '".$docid."'";
							$resPaidStConsolidate = parent::execQuery($sqlPaidStConsolidate, $this->conn_webedit);
							$resPaidStConsolidate1 = parent::execQuery($sqlPaidStConsolidate, $this->conn_split_3);
						}
					}
				}
				
				/*Data for poster ,trailer category  starts here*/
				
				if(isset($params['movie_insta']) && $params['movie_insta'] == '1')
				{
					/*Data for tbl_compcatarea_web_ddg start here*/
					
					$sql_delete_webddg = "delete from webporting_crawl_movies.tbl_compcatarea_web_ddg_100 where docid ='".$docid."'";
					$res_del_rol = parent::execQuery($sql_delete_webddg, $this->web_conn);
					$res_del_rol1 = parent::execQuery($sql_delete_webddg, $this->conn_split_3);
					
					$sql_roling="select IFNULL(final_callcnt,0) as final_callcnt from d_jds.tbl_comp_callcnt_rolling where parentid ='".$this->parentid."'";
					$res_roling = parent::execQuery($sql_roling, $this->conn_iro);
					$row_roling=mysql_fetch_assoc($res_roling);
								
					if (!$row_roling ){
						$final_callcnt= 0;
					}else{
						$final_callcnt = $row_roling['final_callcnt'];
					}
					$sql_paid="SELECT IFNULL(paid_flag,0) AS paid_flag,IFNULL(bid_perday,0) AS perdaycontribution,IF(paid_flag IN (6,5),5,0) AS weight FROM (SELECT  MAX(paid_flag) AS paid_flag, MAX(CASE WHEN paid_flag>0 THEN 1 ELSE 0 END) AS vlc_flag,MAX(bid_perday) AS bid_perday FROM (SELECT parentid,campaignid,budget,company_deduction_amt AS avg_amt,company_deduction_amt AS iro_bid_value,0 AS tenure,0 AS contract_dayleft,expired,bid_perday,IF(campaignid=22 AND end_date>CURRENT_DATE,1,0) AS jdrr_budget,IF(balance>0,balance,0) AS contract_amt, data_city,CASE WHEN campaignid=2 AND IF(balance>0,balance,0) > 0 AND expired=0 THEN 6 WHEN campaignid IN (1,3) AND IF(balance>0,balance,0)>0 AND expired=0 THEN 5 WHEN campaignid NOT IN (1,2,3) AND IF(balance>0,balance,0)>0 AND expired=0 THEN 3 END AS paid_flag FROM db_finance.tbl_companymaster_finance WHERE campaignid <> 0 AND parentid='".$this->parentid."')x1)a"; 
					$result_paid = parent::execQuery($sql_paid, $this->conn_fnc);
					$row_paid=mysql_fetch_assoc($result_paid);		
					
					$paid_flag_ddg = $row_paid['paid_flag'];
					$perdaycontribution= $row_paid['perdaycontribution'];
					$weight = $row_paid['weight'];
					
				  
					$sql_ddg_instant="SELECT national_catid,pincode,position_flag, lcf, hcf,updatedby, NOW() AS updateddate,backenduptdate,catid FROM db_iro.tbl_fp_search WHERE parentid='".$this->parentid."' and activeflag>0 AND pincode<>'999999'";
					$res = parent::execQuery($sql_ddg_instant, $this->conn_iro);
										
					$res_stub = parent::execQuery($sql_ddg_instant, $this->conn_iro);

					
					while($getrow=mysql_fetch_assoc($res))
					{
						$insert_into_webddg="INSERT INTO webporting_crawl_movies.tbl_compcatarea_web_ddg_100 SET
							panindia_sphinxid	= '".$panindia_sphinxid."',
							sphinx_id			= ".$sphinx_id.",
							docid				= '".$docid."',
							national_catid		= ".$getrow['national_catid'].",
							stdcode				= '".$stdcode."',
							parentid			= '".$this->parentid."',
							pincode				= '".$getrow['pincode']."',													
							position_flag		= '".$getrow['position_flag']."',
							lcf					= '".$getrow['lcf']."',
							hcf					= '".$getrow['hcf']."',
							areaname			= '".$areaname."',
							areaname_ws			= '".$areaname_ws."',
							physical_pincode	= '".$pincode."',
							city				= '".$city."', 		
							data_city			= '".$data_city."',
							state				= '".$state."',
							fulladdress			= '".$address_search."',
							latitude			= '".$latitude."',
							longitude			= '".$longitude."',
							phonenos			= '".$phone_search."',
							email				= '".$email_search."',
							active_flag			= '".$active_flag."',
							paid				= '".$paid_flag_ddg."',
							perdaycontribution	= '".$perdaycontribution."',
							company_callcnt		= '".$company_callcnt."',
							rating_jd			= '".$rating_jd."',
							weight				= '".$weight."',						
							photos_count		= '".$photos_count."',
							featured			= '".$featured."',
							national_b2b_flag	= '".$national_b2b_flag."',
							updatedby			= '".$getrow['updatedby']."',
							updateddate			= '".$getrow['updateddate']."',
							backenduptdate		= '".$getrow['backenduptdate']."', 
							random_compsrch_precalc	= '".$random_compsrch_precalc."',
							type_flag				= '".$type_flag."',
							star_jd				= '".$star_jd."',
							dup_groupid			= '".$dupgroupid."',
							data_src			= '".$data_src."',
							company_callcnt_rolling = '".$final_callcnt."'
						
							ON DUPLICATE KEY UPDATE
							
							panindia_sphinxid	= '".$panindia_sphinxid."',
							sphinx_id			= ".$sphinx_id.",										
							stdcode				= '".$stdcode."',
							parentid			= '".$this->parentid."',
							pincode				= '".$getrow['pincode']."',	
							position_flag		= '".$getrow['position_flag']."',
							lcf					= '".$getrow['lcf']."',
							hcf					= '".$getrow['hcf']."',
							areaname			= '".$areaname."',
							areaname_ws			= '".$areaname_ws."',
							physical_pincode	= '".$pincode."',
							city				= '".$city."', 		
							data_city			= '".$data_city."',
							state				= '".$state."',
							fulladdress			= '".$address_search."',
							latitude			= '".$latitude."',
							longitude			= '".$longitude."',
							phonenos			= '".$phone_search."',
							email				= '".$email_search."',
							active_flag			= '".$active_flag."',
							paid				= '".$paid_flag_ddg."',
							perdaycontribution	= '".$perdaycontribution."',
							company_callcnt		= '".$company_callcnt."',
							rating_jd			= '".$rating_jd."',
							weight				= '".$weight."',						
							photos_count		= '".$photos_count."',
							featured			= '".$featured."',
							national_b2b_flag	= '".$national_b2b_flag."',
							updatedby			= '".$getrow['updatedby']."',
							updateddate			= '".$getrow['updateddate']."',
							backenduptdate		= '".$getrow['backenduptdate']."', 
							random_compsrch_precalc	= '".$random_compsrch_precalc."',
							type_flag				= '".$type_flag."',
							star_jd				= '".$star_jd."',
							dup_groupid			= '".$dupgroupid."',
							data_src			= '".$data_src."',
							company_callcnt_rolling = '".$final_callcnt."'							
							";
						 	 
						$result_webddg = parent::execQuery($insert_into_webddg, $this->web_conn);
						$result_webddg1 = parent::execQuery($insert_into_webddg, $this->conn_split_3);
					
					}	 
					 
					$sql_ddg_instant_999="SELECT (SELECT national_catid FROM d_jds.tbl_categorymaster_generalinfo WHERE catid = bid_catid) AS national_catid,a.pincode,a.partial_ddg_ratio,a.partial_ddg_ratio_cum,a.position_flag,IF(a.partial_ddg_ratio_cum=0,1,a.partial_ddg_ratio_cum) - IF(a.partial_ddg_ratio=0,1,a.partial_ddg_ratio) AS lcf, IF(a.partial_ddg_ratio_cum=0,1,a.partial_ddg_ratio_cum) AS hcf,'Dbteam' AS updatedby, NOW() AS updateddate,NOW() AS backenduptdate,a.shopfront_guarantee FROM db_iro.tbl_compcatarea_ddg_999999 a  WHERE  a.parentid='".$this->parentid."'  and a.activeflag>0";
					$res_999 = parent::execQuery($sql_ddg_instant_999, $this->conn_iro);
					
					while($getrow_999=mysql_fetch_assoc($res_999))
					{
						$insert_into_webddg_999="INSERT INTO webporting_crawl_movies.tbl_compcatarea_web_ddg_100 SET
							panindia_sphinxid	= '".$panindia_sphinxid."',
							sphinx_id			= ".$sphinx_id.",
							docid				= '".$docid."',
							national_catid		= ".$getrow_999['national_catid'].",
							stdcode				= '".$stdcode."',
							parentid			= '".$this->parentid."',
							pincode				= '".$getrow_999['pincode']."',	
							partial_ddg_ratio	= '".$getrow_999['partial_ddg_ratio']."',
							partial_ddg_ratio_cum = '".$getrow_999['partial_ddg_ratio_cum']."',
							position_flag		= '".$getrow_999['position_flag']."',
							lcf					= '".$getrow_999['lcf']."',
							hcf					= '".$getrow_999['hcf']."',
							areaname			= '".$areaname."',
							areaname_ws			= '".$areaname_ws."',
							physical_pincode	= '".$pincode."',
							city				= '".$city."', 		
							data_city			= '".$data_city."',
							state				= '".$state."',
							fulladdress			= '".$address_search."',
							latitude			= '".$latitude."',
							longitude			= '".$longitude."',
							phonenos			= '".$phone_search."',
							email				= '".$email_search."',
							active_flag			= '".$active_flag."',
							paid				= '".$paid_flag_ddg."',
							perdaycontribution	= '".$perdaycontribution."',
							company_callcnt		= '".$company_callcnt."',
							rating_jd			= '".$rating_jd."',
							weight				= '".$weight."',						
							photos_count		= '".$photos_count."',
							featured			= '".$featured."',
							national_b2b_flag	= '".$national_b2b_flag."',
							updatedby			= '".$getrow_999['updatedby']."',
							updateddate			= '".$getrow_999['updateddate']."',
							backenduptdate		= '".$getrow_999['backenduptdate']."', 
							random_compsrch_precalc	= '".$random_compsrch_precalc."',
							type_flag				= '".$type_flag."',
							star_jd				= '".$star_jd."',
							dup_groupid			= '".$dupgroupid."',
							data_src			= '".$data_src."',
							company_callcnt_rolling = '".$final_callcnt."'
							
							ON DUPLICATE KEY UPDATE
							
							panindia_sphinxid	= '".$panindia_sphinxid."',
							sphinx_id			= ".$sphinx_id.",										
							stdcode				= '".$stdcode."',
							parentid			= '".$this->parentid."',
							pincode				= '".$getrow_999['pincode']."',	
							partial_ddg_ratio	= '".$getrow_999['partial_ddg_ratio']."',
							partial_ddg_ratio_cum = '".$getrow_999['partial_ddg_ratio_cum']."',
							position_flag		= '".$getrow_999['position_flag']."',
							lcf					= '".$getrow_999['lcf']."',
							hcf					= '".$getrow_999['hcf']."',
							areaname			= '".$areaname."',
							areaname_ws			= '".$areaname_ws."',
							physical_pincode	= '".$pincode."',
							city				= '".$city."', 		
							data_city			= '".$data_city."',
							state				= '".$state."',
							fulladdress			= '".$address_search."',
							latitude			= '".$latitude."',
							longitude			= '".$longitude."',
							phonenos			= '".$phone_search."',
							email				= '".$email_search."',
							active_flag			= '".$active_flag."',
							paid				= '".$paid_flag_ddg."',
							perdaycontribution	= '".$perdaycontribution."',
							company_callcnt		= '".$company_callcnt."',
							rating_jd			= '".$rating_jd."',
							weight				= '".$weight."',						
							photos_count		= '".$photos_count."',
							featured			= '".$featured."',
							national_b2b_flag	= '".$national_b2b_flag."',
							updatedby			= '".$getrow_999['updatedby']."',
							updateddate			= '".$getrow_999['updateddate']."',
							backenduptdate		= '".$getrow_999['backenduptdate']."', 
							random_compsrch_precalc	= '".$random_compsrch_precalc."',
							type_flag				= '".$type_flag."',
							star_jd				= '".$star_jd."',
							dup_groupid			= '".$dupgroupid."',
							data_src			= '".$data_src."',
							company_callcnt_rolling = '".$final_callcnt."'		
							";
						
						$result_webddg_999 = parent::execQuery($insert_into_webddg_999, $this->web_conn);
						$result_webddg_9991 = parent::execQuery($insert_into_webddg_999, $this->conn_split_3);
					}			
						
					/*Data for tbl_compcatarea_web_ddg End  here*/
					
					/*Data for tbl_compcatarea_web Start here**/
					
					$sql_delete_webddg = "delete from webporting_crawl_movies.tbl_compcatarea_web_100 where docid ='".$docid."'";
					$res_del = parent::execQuery($sql_delete_webddg, $this->web_conn);
					$res_del1 = parent::execQuery($sql_delete_webddg, $this->conn_split_3);

					
					$sql_delete_online = "delete from webporting_crawl.tbl_compcat_mapping_online_movies where parentid ='".$this->parentid."'";
					
					$res_del_online = parent::execQuery($sql_delete_online, $this->conn_iro_slave);
					
					$sql_web1="SELECT parentId,national_catid, activeflag,search_contribution,updatedby,updatedon,backenduptdate,data_city,catid FROM db_iro.tbl_package_search where parentid='".$this->parentid."'";
					
					$result_webinst1 = parent::execQuery($sql_web1, $this->conn_iro);

					
					if($result_webinst1 && mysql_num_rows($result_webinst1))
					{
						while($getrow_web1=mysql_fetch_assoc($result_webinst1))
						{
							$sql_inst_web1="INSERT IGNORE INTO webporting_crawl_movies.tbl_compcatarea_web_100 (parentid,docid,national_catid,active_flag,contribution,updatedby,updateddate,backenduptdate,data_city) values('".$getrow_web1['parentId']."','".$docid."','".$getrow_web1['national_catid']."','".$getrow_web1['activeflag']."','".$getrow_web1['search_contribution']."','".$getrow_web1['updatedby']."','".$getrow_web1['updatedon']."','".$getrow_web1['backenduptdate']."','".$getrow_web1['data_city']."')";
							$result_webinst11 = parent::execQuery($sql_inst_web1, $this->web_conn);
							$result_webinst111 = parent::execQuery($sql_inst_web1, $this->conn_split_3);

							$sql_map1="INSERT IGNORE INTO webporting_crawl.tbl_compcat_mapping_online_movies(panindia_sphinxid,parentid, catid, docid, paidstatus,data_city, national_catid, featured, national_b2b_flag, autosuggest_city,type_flag,movie_tag) values('".$panindia_sphinxid."','".$this->parentid."','".$getrow_web1['catid']."','".$docid."','".$paid_flag_ddg."','".$getrow_web1['data_city']."','".$getrow_web1['national_catid']."','".$featured."',0,'".$getrow_web1['data_city']."','".$type_flag."',1)";
							$result_map1 = parent::execQuery($sql_map1, $this->conn_iro_slave);							
						}
					 }				
					
							
					$sql_web3="SELECT parentId,national_catid, activeflag,updatedby,updatedon,backenduptdate,data_city,catid FROM db_iro.tbl_nonpaid_search where parentid ='".$this->parentid."'";
					$result_webinst3 = parent::execQuery($sql_web3, $this->conn_iro);
					
					if($result_webinst3 && mysql_num_rows($result_webinst3))
					{
						while($getrow_web3=mysql_fetch_assoc($result_webinst3))
						{
							$sql_inst_web3="INSERT IGNORE INTO webporting_crawl_movies.tbl_compcatarea_web_100 (parentid,docid,national_catid,active_flag,updatedby,updateddate,backenduptdate,data_city) values('".$getrow_web3['parentId']."','".$docid."','".$getrow_web3['national_catid']."','".$getrow_web3['activeflag']."','".$getrow_web3['updatedby']."','".$getrow_web3['updatedon']."','".$getrow_web3['backenduptdate']."','".$getrow_web3['data_city']."')";
							
							$result_webinst33 = parent::execQuery($sql_inst_web3, $this->web_conn);
							$result_webinst333 = parent::execQuery($sql_inst_web3, $this->conn_split_3);
							 
							$sql_map3="INSERT IGNORE INTO webporting_crawl.tbl_compcat_mapping_online_movies(panindia_sphinxid,parentid, catid, docid, paidstatus,data_city, national_catid, featured, national_b2b_flag, autosuggest_city,type_flag,movie_tag) values('".$panindia_sphinxid."','".$this->parentid."','".$getrow_web3['catid']."','".$docid."','".$paid_flag_ddg."','".$getrow_web3['data_city']."','".$getrow_web3['national_catid']."','".$featured."',0,'".$getrow_web3['data_city']."','".$type_flag."',1)";
							
							$result_map3 = parent::execQuery($sql_map3, $this->conn_iro_slave);							
						}
					}
		
					while($getrow=mysql_fetch_assoc($res_stub))
					{
						$sql_ddg_stub="insert ignore into webporting_crawl_movies.tbl_compcatarea_web_100(docid,parentid,national_catid,data_city,active_flag,updatedby, panindia_sphinxid,perdaycontribution) values ('".$docid."','".$this->parentid."', '".$getrow['national_catid']."','".$data_city."',1,'ddg stubbed entry','".$panindia_sphinxid."','".$perdaycontribution."')"; 
						
						$result_ddg_stub = parent::execQuery($sql_ddg_stub, $this->web_conn);
						$result_ddg_stub1 = parent::execQuery($sql_ddg_stub, $this->conn_split_3);
						
						$sql_map4="INSERT IGNORE INTO webporting_crawl.tbl_compcat_mapping_online_movies(panindia_sphinxid,parentid, catid, docid, paidstatus,data_city, national_catid, featured, national_b2b_flag, autosuggest_city,type_flag,movie_tag) values('".$panindia_sphinxid."','".$this->parentid."','".$getrow['catid']."','".$docid."','".$paid_flag_ddg."','".$getrow['data_city']."','".$getrow['national_catid']."','".$featured."',0,'".$getrow['data_city']."','".$type_flag."',1)";
						
						$result_map4 = parent::execQuery($sql_map4, $this->conn_iro_slave);						
					 }
					
				
					$sql_web_updt="UPDATE webporting_crawl_movies.tbl_compcatarea_web_100 a SET a.sphinx_id=".$sphinx_id.", a.panindia_sphinxid='".$panindia_sphinxid."', a.docid='".$docid."', a.stdcode = '".$stdcode."', a.pincode='".$pincode."', a.physical_pincode='".$pincode."', a.areaname='".$areaname."', a.areaname_ws='".$areaname_ws."', a.city='".$city."', a.data_city='".$data_city."', a.state='".$state."', a.fulladdress='".$address_search."', a.latitude='".$latitude."', a.longitude='".$longitude."', a.phonenos='".$phone_search."', a.email='".$email_search."', a.company_callcnt='".$company_callcnt."', a.photos_count='".$photos_count."', a.random_compsrch_precalc='".$random_compsrch_precalc."', a.rating_jd='".$active_flag."', a.star_jd='".$star_jd."', a.updateddate=NOW(), a.backenduptdate=NOW(), a.featured='".$featured."', a.national_b2b_flag='".$national_b2b_flag."', a.company_callcnt_rolling='".$final_callcnt."', a.paid = '".$paid_flag_ddg."', a.weight = '".$weight."', a.VLC_flag = '".$vlc_flag."', a.type_flag = '".$type_flag."',a.price_range='".$pricerange."',a.dup_groupid='".$dupgroupid."',a.data_src='".$data_src."',a.console_cat_cnt = '".$console_cat_callcnt."' WHERE a.parentid='".$this->parentid."'";
									
					$result_webinst = parent::execQuery($sql_web_updt, $this->web_conn);
					$result_webinst1 = parent::execQuery($sql_web_updt, $this->conn_split_3);
					
					$sql_web_updt1="update webporting_crawl_movies.tbl_compcatarea_web_100 set paid = ".$paid_flag_ddg.", weight = '".$weight."', VLC_flag = '".$vlc_flag."' where parentid='".$this->parentid."'";
					$result_webupdt1 = parent::execQuery($sql_web_updt1, $this->web_conn);
					$result_webupdt11 = parent::execQuery($sql_web_updt1, $this->conn_split_3);
					
		
					$sql_trunc= "DELETE FROM webporting_crawl.tbl_wp3_category_specification_instant WHERE parentid='".$this->parentid."'";
					$rs_trunc = parent::execQuery($sql_trunc, $this->conn_iro_slave);
					if(trim($catidlineage) !='')
					{
						$sql_insert_cat="INSERT IGNORE INTO webporting_crawl.tbl_wp3_category_specification_instant(parentid,catid, specification_id, specification_name, specification_details, catname, national_catid, data_src) SELECT '".$this->parentid."',a.catid, a.specification_id, a.specification_name, a.specification_details, b.category_name, b.national_catid,'".$datasrc."' as data_src FROM d_jds.tbl_category_specification AS a JOIN d_jds.tbl_categorymaster_generalinfo b ON a.catid=b.catid where a.catid in(".$catidlineage.")"; 
						$rs_instcat = parent::execQuery($sql_insert_cat, $this->conn_iro_slave);				
					}
					$sql_sel_cat="select catid, specification_id, specification_name, specification_details, catname, national_catid, data_src  FROM webporting_crawl.tbl_wp3_category_specification_instant where parentid ='".$this->parentid."'"; 
					$res_sel_cat = parent::execQuery($sql_sel_cat, $this->conn_iro_slave);
					$values_arr = array();
					if($res_sel_cat && mysql_num_rows($res_sel_cat))
					{						
						$mv_cnt = 1;
						while($row_sel_cat=mysql_fetch_assoc($res_sel_cat))
						{
							$values_arr[] = " (".$row_sel_cat['national_catid']." , '".$row_sel_cat['catid']."', '".$row_sel_cat['specification_id']."','".addslashes($row_sel_cat['specification_name'])."','".addslashes($row_sel_cat['specification_details'])."', '".addslashes($row_sel_cat['catname'])."','".$datasrc."')";			
							if($mv_cnt % 500 == 0)
							{
								$values = implode(",",$values_arr);
								
								$sql_inst_cat= "INSERT IGNORE INTO webporting_crawl_movies.tbl_category_specification_consolidated (national_catid, catid, specification_id, specification_name, specification_details, catname, data_src) VALUES ".$values;
								$result_inst_cat = parent::execQuery($sql_inst_cat, $this->web_conn);
								$result_inst_cat1 = parent::execQuery($sql_inst_cat, $this->conn_split_3);
								unset($values_arr);																
							}
							$mv_cnt++;							
						}						
						if(count($values_arr)>0)
						{
							$values = implode(",",$values_arr);
							$sql_inst_cat= "INSERT IGNORE INTO webporting_crawl_movies.tbl_category_specification_consolidated (national_catid, catid, specification_id, specification_name, specification_details, catname, data_src) VALUES ".$values;
							$result_inst_cat = parent::execQuery($sql_inst_cat, $this->web_conn);
							$result_inst_cat1 = parent::execQuery($sql_inst_cat, $this->conn_split_3);
							unset($values_arr);
						}	
					}
				}
				/*Data for poster ,trailer category end here*/
				//To Delete details of a DOCID:
 
				$Rdis_Del="http://192.168.20.102:9001/web_services/company_info_redis.php?docid=".$docid."&case=DELETE&source=insta";
				$Curl_del = $this->CurlFn($Rdis_Del);	
				$Curl_del_op = json_decode($Curl_del,true);
				if($Curl_del_op['error']['msg'] != 'Inserted Into Redis Sucessful'){
					$success_flag = 0;
					$this->insertLog("REDIS_url_delete",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"REDIS url for data deletion call failed ==>Query==>".$Rdis_Del."==>Result==>".$Curl_del);		
				}else{
					$this->insertLog("REDIS_url_delete",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"REDIS url called and data deleted sucessfully==>Query==>".$Rdis_Del."==>Result==>".$Curl_del);
				}
				
				//To insert details of a DOCID into redis:
				$Rdis_Insrt="http://192.168.20.102:9001/web_services/company_info_redis.php?docid=".$docid."&case=DETAILS&source=webporting&from=insta";
				$Curl_Insert = $this->CurlFn($Rdis_Insrt);
				$Curl_insert_op = json_decode($Curl_Insert,true);
				
				if($Curl_insert_op['error']['msg'] != 'Inserted Into Redis Sucessful'){
					$success_flag = 0;
					$this->insertLog("REDIS_url_insert",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"REDIS url for data insertion call failed ==>Query==>".$Rdis_Insrt."==>Result==>".$Curl_Insert);		
				}else{
					$this->insertLog("REDIS_url_insert",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,"REDIS url called and data inserted sucessfully==>Query==>".$Rdis_Insrt."==>Result==>".$Curl_Insert);
				}
				

				$sql_insert_complog ="INSERT IGNORE INTO webporting_process_log.tbl_contract_status_log_instant(docid,update_time,status,panindia_sphinxid,short_url) values('".$docid."',now(),1,'".$panindia_sphinxid."','".$short_url."')";
				$res_insert_complog 	=	parent::execQuery($sql_insert_complog, $this->conn_webedit);				

				$sql_update_log		=	"UPDATE webporting_process_log.tbl_instant_status_log SET status =1";
				$res_update_log 	=	parent::execQuery($sql_update_log, $this->conn_webedit);				

				/* commented after confirmation from Ashwin sawai
				$sql_Fetch_Data 	=	"SELECT docid FROM processing.tbl_instant_activation_url WHERE docid = '".$docid."'";
				$res_Fetch_Data 	=	parent::execQuery($sql_Fetch_Data, $this->conn_webedit);				
				if($res_Fetch_Data && mysql_num_rows($res_Fetch_Data)>0)
				{
					$sql_Update_PendingData = "UPDATE processing.tbl_instant_activation_url SET process_flag ='5' WHERE docid = '".$docid."'";
					$res_Update_PendingData = parent::execQuery($sql_Update_PendingData, $this->conn_webedit);				
				}*/
				$sql_update_arch = "DELETE FROM webporting_instant_act.tbl_wp1_compsrch_new_architecture_instant WHERE parentid = '".$this->parentid."'";
				//$res_update_arch 	=	parent::execQuery($sql_update_arch, $this->conn_iro);
				
				
				
				$sql_del_wp2_web1 = "DELETE FROM webporting_instant_act.tbl_wp2_compcatarea_web_instant WHERE parentid = '".$this->parentid."'";
				//$res_del_wp2_web1 =	parent::execQuery($sql_del_wp2_web1, $this->conn_iro);
			
				$sql_del_wp2_ddg1 = "DELETE FROM webporting_instant_act.tbl_wp2_compcatarea_web_ddg_instant WHERE parentid = '".$this->parentid."'";
				//$res_del_wp2_ddg1 = parent::execQuery($sql_del_wp2_ddg1, $this->conn_iro); 

				if($success_flag == 0)
				{
				
					/*$sql_check_exists = "SELECT * FROM online_regis1.tbl_instant_live WHERE parentid='".$params['parentid']."' AND process_flag=0";
					$res_check_exists	=	parent::execQuery($sql_check_exists, $this->conn_idc);
					if($res_check_exists && mysql_num_rows($res_check_exists)==0)
					{
						$params_arr = array_map('trim',$params);			 
						foreach ($params_arr as $key => $value) {						
						 $params_string .= $key . '=' . urlencode($value) . '&';
						}
									   
						$params_string = rtrim($params_string, '&');			   
						$main_url = "http://".$_SERVER['HTTP_HOST']."/services/instant_live.php";
						$final_url = $main_url."?".$params_string;
						
						$datacity_upper = strtoupper(trim($params['data_city']));
						$maincities_array = array("MUMBAI","DELHI","KOLKATA","BANGALORE","CHENNAI","PUNE","HYDERABAD","AHMEDABAD");
						if(in_array($datacity_upper,$maincities_array)){
							$datacitystr = $params['data_city'];
						}else{
							$datacitystr = "Remote";
						}
						
						$insert_fail = "INSERT INTO online_regis1.tbl_instant_live 
						SET 
						parentid	=	'".$params['parentid']."',
						source		=	'".$params['module']."',
						ucode		=	'".$params['ucode']."',
						url			=	'".$final_url."',
						data_city	=	'".$datacitystr."',
						entry_date	=	now()";
						
						$res_insert_fail	=	parent::execQuery($insert_fail, $this->conn_idc);
					}*/
					$output_final['data'] 				=  'Contract inserted UN-successfully';
					$output_final['error']['code'] 		=  "1";
					$output_final['error']['message'] 	=  "Error";
					
				}
				else
				{
					$output_final['data'] 				=  'Contract inserted successfully';
					$output_final['error']['code'] 		=  "0";
					$output_final['error']['message'] 	=  "success";		
				}
				
				if($params['trace'] == 1)
				{	
					echo "<pre>";
					echo "Out put : ";
					print_r($output_final);
					echo "\n--------------------------------------------------------------------------------------\n";
				}
				$this->insertLog("end_time",date('H:i:s'),date('Y-m-d'),__LINE__,$this->parentid,$this->module,date('Y-m-d H:i:s'));	
				return $output_final;				
			}	
		}  
 					
	} 
	public function SendToInstantUpdation()
	{
		global $params;
		$output_final = array();
		if($params['trace'] == 1)
		{	
			echo "Input Parameters : ";
			print_r($params);
			echo "\n--------------------------------------------------------------------------------------\n";
		}
		$prev_day = date('Y-m-d ', strtotime(' -1 day'));
		$entry_date = $prev_day."00:00:00";
		$sql_check_exists = "SELECT * FROM online_regis1.tbl_instant_live WHERE parentid='".$params['parentid']."' AND process_flag=0 AND entry_date>='".$entry_date."' AND counter < 3";
		$res_check_exists	=	parent::execQuery($sql_check_exists, $this->conn_idc);
		if($res_check_exists && mysql_num_rows($res_check_exists)==0)
		{
			$curl_url_il = "http://".$_SERVER['HTTP_HOST']."/services/instant_live.php";
			
			$param_array['parentid'] 		=	$params['parentid'];
			$param_array['data_city'] 		=	$params['data_city'];
			$param_array['module'] 			=	$params['module'];	
			if(empty($param_array['module']))
				$param_array['module'] 			=	$params['source'];		
			$param_array['ucode'] 			=	$params['ucode'];	
			if(isset($params['movie_insta']) && $params['movie_insta'] == 1)
				$param_array['movie_insta'] 			=	$params['movie_insta'];
			
			$instant_url =  $curl_url_il."?".http_build_query($param_array);
			$datacity_upper = strtoupper(trim($param_array['data_city']));
			$maincities_array = array("MUMBAI","DELHI","KOLKATA","BANGALORE","CHENNAI","PUNE","HYDERABAD","AHMEDABAD");
			if(in_array($datacity_upper,$maincities_array)){
				$datacitystr = $param_array['data_city'];
			}else{
				$datacitystr = "Remote";
			}
			$insert_instant = "INSERT INTO online_regis1.tbl_instant_live 
			SET 
			parentid	=	'".$param_array['parentid']."',
			data_city	=	'".$datacitystr."',
			url			=	'".$instant_url."',
			source		=	'".$params['module']."',
			ucode		=	'".$params['ucode']."',
			entry_date	=	now()";
			$res_instant =  parent::execQuery($insert_instant, $this->conn_idc);
	 
			$output_final['data'] 				=  "sent to instant updation";
			$output_final['error']['code'] 		=  "0";
			$output_final['error']['message'] 	=  "success"; 
		}
		else
		{
			$output_final['data'] 				=  "request already sent to instant updation";
			$output_final['error']['code'] 		=  "0";
			$output_final['error']['message'] 	=  "success"; 
		}
		if($params['trace'] == 1)
		{	
			echo "\n\n\n";
			echo "Out put : ";
			print_r($output_final);
			echo "\n--------------------------------------------------------------------------------------\n";			 
		} 
		return $output_final;
	}
	public function GetIdGenerator()
	{
		$sqlDocidInfo = "SELECT ((url_cityid*100000000) + sphinx_id) AS panindia_sphinxid,data_city,CONCAT('|#|',data_city,'|#|') AS listed_cities,CONCAT(url_cityid,shorturl) AS short_url,stdcode,stdcode as data_city_stdcode,sphinx_id,docid FROM db_iro.tbl_id_generator WHERE parentid ='".$this->parentid."'";
		$resDocidInfo = parent::execQuery($sqlDocidInfo, $this->conn_iro);
		if($resDocidInfo && parent::numRows($resDocidInfo)>0)
		{
			$row_docid = parent::fetchData($resDocidInfo);
			return $row_docid;
		}		
	}
	public function GetNationallist()
	{
		$sqlNationalListing = "SELECT category_city FROM tbl_national_listing WHERE expired=0 AND parentid='".$this->parentid."'";
		$resNationalListing = parent::execQuery($sqlNationalListing, $this->conn_national);
		if($resNationalListing && parent::numRows($resNationalListing)>0)
		{
			$row_national = parent::fetchData($resNationalListing);
			return $row_national;
		}		
	}
	public function GetMajorCity($data_city)
	{
		$sqlMajorCity = "SELECT city_name FROM tbl_major_cities WHERE city_name='".$data_city."'";
		$resMajorCity = parent::execQuery($sqlMajorCity, $this->conn_iro);
		if($resMajorCity && parent::numRows($resMajorCity)>0)		
		{
			$row_majorcity = parent::fetchData($resMajorCity);
			return $row_majorcity; 
		}
	}
	public function GetIroCard()
	{
		$sqlFetchIroCards = "SELECT sphinx_id,parentid,contractid,small_card,medium_card,extended_card,update_time,data_city,docid,featured,national_b2b_flag,jdrr_tag,tag_line,type_flag,sub_type_flag,
		iro_type_flag,website_type_flag,businesstags,cc_status,popular_flag,fb_prefered_language,attributes FROM iro_cards where parentid ='".$this->parentid."'";
		$resFetchIroCards = parent::execQuery($sqlFetchIroCards, $this->conn_iro);
		if($resFetchIroCards && parent::numRows($resFetchIroCards)>0)
		{
			$row_iro_cards = parent::fetchData($resFetchIroCards);
			return $row_iro_cards;
		}		
	}
	private function getMiscData()
	{
		$misc_data = '';
		$sqlMiscData = "SELECT misc_data FROM tbl_omni_iro_data WHERE parentid = '".$this->parentid."'";
		$resMiscData = parent::execQuery($sqlMiscData, $this->conn_iro);
		
		if($resMiscData && parent::numRows($resMiscData)>0){
			$row_misc_data = parent::fetchData($resMiscData);
			$misc_data = trim($row_misc_data['misc_data']);
		}
		return $misc_data;
	}
	
	public function GetCompsyn()
	{
		$sqlCompanySyn="SELECT synname,synid,display_flag FROM d_jds.tbl_compsyn WHERE (display_flag=1 OR display_flag IS NULL) AND parentid ='".$this->parentid."'";
		$resCompanySyn = parent::execQuery($sqlCompanySyn, $this->conn_iro);
		if($resCompanySyn && parent::numRows($resCompanySyn)>0)
		{
			$row_comp_syn = parent::fetchData($resCompanySyn);
			return $row_comp_syn;
		}
	}
		
	public function GetCompExtradetails()
	{		
		$sqlExtraDetails = "SELECT parentid,companyname AS main_compname,business_assoc_flags AS business_flag,map_pointer_flags AS pointer_flag,IFNULL(SUBSTRING_INDEX(averageRating,'~',-1),0) AS rating_jd,RAND() AS random_compsrch_precalc,NOW() AS update_time,flgactive AS active_flag,num_of_photos AS photos_count,data_city AS data_src,noduplicatecheck AS noduplicatecheck,averageRating AS average_rating,area_sensitivity AS area_sensitivity,closedown_flag,block_for_sale,type_flag,sub_type_flag,IFNULL(SUBSTRING_INDEX(averageRating,'~',1),0) AS star_jd,iro_type_flag AS iro_type_flag,website_type_flag,hotcategory,ifnull(price_range,0) as price_range,dup_groupid,low_ranking,master_parentid,
		type_flag_actions,console_cat_callcnt,catidlineage,guarantee,restrict_display,hidden_flag,mid,muser FROM tbl_companymaster_extradetails WHERE parentid ='".$this->parentid."'";
		$resExtraDetails = parent::execQuery($sqlExtraDetails, $this->conn_iro);
		if($resExtraDetails && parent::numRows($resExtraDetails)>0)
		{
			$row_extradetails = parent::fetchData($resExtraDetails);
			return $row_extradetails;
		}
	}
	public function GetCompGeneralinfo()
	{	
		$sqlGeneralInfo = "select IF(hide_address='1','',pincode) AS pincode,IF(hide_address='1','',area) AS areaname,IF(hide_address='1','',REPLACE(area,' ','')) AS areaname_ws,IF(hide_address='1','',landmark) AS landmark,city,latitude,longitude,full_address as address_search,contact_person_display as contactperson_search,TRIM( BOTH ',' FROM CONCAT(IFNULL(landline,''),',',IFNULL(mobile,''))) AS phone_search,email_display as email_search,tollfree,virtualNumber as v_number,website,stdcode,ifnull(blockforvirtual,0) as blockforvirtual,IFNULL(company_callcnt,0) as company_callcnt,TRIM( BOTH ',' FROM CONCAT(IFNULL(landline_display,''),',',IFNULL(mobile_display,''))) AS duplicate_check_phonenos, geocode_accuracy_level, state,dialable_landline,dialable_mobile,dialable_virtualnumber,'Dbteam' AS updatedby, NOW() AS updatedon, paid FROM tbl_companymaster_generalinfo where parentid ='".$this->parentid."'";
		
		$resGeneralInfo = parent::execQuery($sqlGeneralInfo, $this->conn_iro);
		if($resGeneralInfo && parent::numRows($resGeneralInfo)>0)
		{
			$row_generalinfo = parent::fetchData($resGeneralInfo);
			return $row_generalinfo;
		}		
	}
	public function GetPslab()
	{
		$sql_pslab="select ifnull(percentage_slab,0) as percentage_slab from db_iro.tbl_company_percentage_slab where parentid = '".$this->parentid."'";
		$res = parent::execQuery($sql_pslab, $this->conn_iro);
		if($res && mysql_num_rows($res)>0)
		{
			$row=mysql_fetch_assoc($res);
			return $row;
		}		
	}
	
	public function GetBlockVer()
	{
		$sql_blockver="select count(1) as block_verified  from webporting_crawl.tbl_block_verified_contracts where parentid = '".$this->parentid."'";
		
		$res = parent::execQuery($sql_blockver, $this->conn_iro_slave);
		if($res && mysql_num_rows($res)>0)
		{
			$row=mysql_fetch_assoc($res);
			return $row;
		}
	}
	public function companyWithArea($c,$a)
	{
		$a = trim($a);
		$c = @preg_replace(array('/\bE\b/i','/\bW\b$/i'),array('East','West'),$c);
		if(!empty($a) && !preg_match("/\b$a/i",$c))
		{
			$c = $c.' '.$a;
		}
		return $c;
	}
	public function getSingular($str='')
	{
		$s = array();
		$t = explode(' ',$str);
		$e = array('shoes'=>'shoe','glasses'=>'glass','mattresses'=>'mattress','mattress'=>'mattress','joes'=>'joes','watches'=>'watch','access'=>'access','joss','sunglasses'=>'sunglass','status'=>'status');
		$r = array('ss'=>'ss','os'=>'o','ies'=>'y','xes'=>'x','oes'=>'o','ies'=>'y','ves'=>'f','s'=>'');
		foreach($t as $v){
			if(strlen($v)>=4){
				$f = false;
				foreach(array_keys($r) as $k){
					if(substr($v,(strlen($k)*-1))!=$k){
						continue;
					}
					else{
						$f = true;
						if(array_key_exists($v,$e))
							$s[] = $e[$v];
						else
							$s[] = substr($v,0,strlen($v)-strlen($k)).$r[$k];

						break;
					}
				}
				if(!$f){
					$s[] = $v;
				}
			}
			else{
				$s[] = $v;
			}
		}
		return (!empty($s)) ? implode(' ',$s) : $str;
	}
	public function applyIgnore($str)
	{
		$ig_strt = array('/^\bthe\b/i','/^\bdr\.\s/i','/^\bdr\b/i','/^\bprof\.\s/i','/^\bprof\b/i','/^\band\b/i','/^\bbe\b/i');
		$ig_last = array('/\bpvt\.\s/i','/\bltd\.\s/i','/\bpvt\b/i','/\bltd\b/i','/\bprivate\b/i','/\blimited\b/i','/\brestaurants$\b/i','/\brestaurant\b$/i',
			  '/\bhotel\b$/i','/\bhotels\b$/i');
		$s = $str;
		$s = preg_replace($ig_strt,'',$s);
		$s = preg_replace($ig_last,'',trim($s));
		$s = preg_replace('/[\s+]+/',' ',trim($s));
		return (strlen($s)<=1) ? $str : $s;
	}
	public function sanitize($str,$case='')
	{
		$str = preg_replace("/[@&\-\.,_]+/",' ',$str);
		if($case)
			$str = preg_replace("/[^a-zA-Z0-9\s\(\)]+/",'',$str);
		else
			$str = preg_replace("/[^a-zA-Z0-9\s]+/",'',$str);

		$str = preg_replace('/\\\+/i','',$str);
		$str = preg_replace('/\s\s+/',' ',$str);
		return trim(strtolower($str));
	}
	public function braces_content_removal($str,$i=0)
	{
		$sflag =$eflag = false;
		$start=$end=0;
		if(stristr($str,'(') || stristr($str,')'))
		{
			if(preg_match('/\(/',$str))
			{
				$sflag = true;
				//echo '<br>Start----->'.
				$start = strpos($str,'(');
			}
			
			if(preg_match('/\)/',$str))
			{
				$eflag = true;
				$end = strpos($str,')');
			}
			if(!$eflag)
			{
				$end =$start;
			}
			if(!$sflag)
			{
				$start = $end;
			}

			if($end < $start)
			{
				$start = 0;
			}
			$str = substr_replace($str, '', $start, ($end-$start)+1);
			$str = $this->braces_content_removal($str,++$i);
			return trim($str);
		}
		else
		{
			$str = preg_replace('/\s\s+/',' ',trim($str));
			return trim($str);
		}
	}
	public function concatsingle($str)
	{
		$new = '';
		$tmp = explode(' ',$str);
		for($i=0;$i<count($tmp);$i++)
		{
			if(strlen($tmp[$i])==1)
				$new .=$tmp[$i];
			else 
				$new .=' '.$tmp[$i].' ';
		}
		$new = trim(preg_replace('[\s\s+]',' ',$new));
		return $new;
	}
	public function insertLog($column,$time, $date, $lineno, $parentid, $module ,$message)
	{		 
		if($column == 'start_time' OR $column == 'end_time' OR $column == 'webporting_url' OR $column == 'webporting_status')
			$string	=	$message;
		else	
			$string	=	"[  DateTime :- ".$date ." ". $time." Line No :- ".$lineno."--".$message."]\n";
		
		$insert = "INSERT INTO d_jds.tbl_instant_live_log SET
					parentid	=	'".$parentid."',
					call_time	=	'".$this->call_time."',
					module		=	'".$this->module."',
					ucode		=	'".$this->ucode."',
					".$column."	= 	'".addslashes($string)."'
					 ON DUPLICATE KEY UPDATE
					".$column."= '".addslashes($string)."'"; 
		 $res 	=	parent::execQuery($insert, $this->data_correction);
	}
	public function CurlFn($Urlstr)
	{
		$ch = curl_init();			
		$ch = curl_init($Urlstr);	
		curl_setopt($ch, CURLOPT_URL,$Urlstr);				
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$resultString = curl_exec($ch);					
		curl_close($ch);				
		return $resultString;		
	}
	private function get_paidstatus($parentid,$data_city)
	{
		$res_arr = array();
		$sql_contract_type = "SELECT parentid FROM tbl_companymaster_finance WHERE parentid='".$parentid."' and balance>0 LIMIT 1";
		$res_contract_type = parent::execQuery($sql_contract_type,$this->conn_fnc);
		
		$paid_status = 0;
		if(parent::numRows($res_contract_type)>0){
			$paid_status = 1;
		}
		else{
			$sql_national_type = "SELECT parentid FROM tbl_companymaster_finance_national WHERE parentid='".$parentid."' AND  campaignid = 10 AND balance>0 LIMIT 1";
			$res_national_type = parent::execQuery($sql_national_type,$this->conn_national);
			if(parent::numRows($res_national_type)>0){
				$paid_status = 1;
			}
		}
		$res_arr['result']['paid'] 	= $paid_status;
		return $res_arr;
	}		
	function addslashesArray($resultArray)
	{
		foreach($resultArray AS $key=>$value)
		{
			$resultArray[$key] = addslashes(stripslashes(trim($value)));
		}
		
		return $resultArray;
	}
	private function sendDieMessage($msg)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['message'] = $msg;
		return $die_msg_arr;
	}		
}
?>
