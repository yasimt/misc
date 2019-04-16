<?php
//populate_proposal_details_class.php
ob_start();
ini_set('max_execution_time', 0); 

class populate_proposal_details_class extends DB{
	var  $conn_local   	= null;
	var  $conn_iro   	= null;
	var  $params  		= null;
	var  $configobj		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	
	function __construct($params){
		$parentid 		= trim($params['parentid']);
		$version		= trim($params['version']);
		$module 		= trim($params['module']);
		$source 		= trim($params['source']);
		$data_city 		= trim($params['data_city']);
		$ucode 			= trim($params['ucode']);
		$client_mailid	= trim($params['client_mailid']);
		$campaign_ids	= trim($params['campaign_ids']);
		$campaign_names	= trim($params['campaign_names']);
		$pdf_file_name	= trim($params['pdf_file_name']);
		$finTempArr 	= $params['finTempArr'];
		$payment_type 	= trim($params['payment_type']);
		if(trim($parentid)==''){
            $message = "Parentid is blank.";
            echo json_encode($this->send_die_message($message));
            die();
        }
		if(trim($version)==''){
            $message = "Version is blank.";
            echo json_encode($this->send_die_message($message));
            die();
        }
        if(trim($data_city)==''){
			$message = "Data City is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(trim($module)==''){
			$message = "Module is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(trim($client_mailid)==''){
			$message = "Client Mail Id is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}	
		if(trim($ucode)==''){
			$message = "Empcode is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(empty($finTempArr) || count($finTempArr)<=0){
			$message = "Please Pass FinTempArr.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		
		$this->parentid  	= $parentid;		
		$this->version		= $version;
		$this->data_city 	= $data_city;		
		$this->module  	  	= strtoupper($module);
		$this->source  	  	= strtoupper($source);
		$this->ucode		= $ucode;
		$this->client_mailid	= trim($client_mailid);
		$this->campaign_ids		= trim($campaign_ids);
		$this->campaign_names	= trim($campaign_names);
		$this->params 			= $params;
		$this->finTempArr 		= $finTempArr;
		$this->payment_type 	= $payment_type;
		$this->pdf_file_name 	= $pdf_file_name;
		$this->mongo_flag 		= 0;
		$this->mongo_tme 		= 0;
		$this->setServers();
		$this->docid 			= $this->getDocid();
		$this->last_insert_date	= '';
		$this->category_count   = 0;
		$this->id 				= 0;
		//~ echo "<pre>params:--";print_r($params);
		//~ echo "<pre>finTempArr:--";print_r($this->finTempArr);
		$this->total_budget   = array();
		$this->total_duration = array();
		if(count($this->finTempArr)>0){
			foreach($this->finTempArr as $key=>$value){
				$total_budget[$value['actcamp']] = $value['budget'];
				$total_duration[$value['actcamp']] = $value['duration'];				
			}
		}
		$this->total_budget   = $total_budget;
		$this->total_duration = $total_duration;
		$this->companyClass_obj = new companyClass();
		//~ echo "<pre>this ->total_budget:---";print_r($this->total_budget);		
		//~ echo "<pre>this ->total_duration:---";print_r($this->total_duration);		
		//~ echo "<br>";die("dfgfdg");
	}	
	
	function setServers(){	
		GLOBAL $db;
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->conn_tme  		= $db[$conn_city]['tme_jds']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];
		$this->db_budgeting		= $db[$conn_city]['db_budgeting']['master'];		
		$this->setCommonInfo();
		if((strtoupper($this->module) =='DE') || (strtoupper($this->module) =='CS')){
			$this->conn_temp	 	= $this->conn_local;
			$this->conn_temp_fin 	= $this->conn_iro;
			$this->conn_catmaster 	= $this->conn_local;
		}elseif(strtoupper($this->module) =='TME'){
			$this->conn_temp		= $this->conn_tme;
			$this->conn_temp_fin 	= $this->conn_tme;
			$this->conn_catmaster 	= $this->conn_local;
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($conn_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}

		}elseif((strtoupper($this->module) =='ME') || (strtoupper($this->module) =='JDA')){
			$this->conn_temp		= $this->conn_idc;
			$this->conn_temp_fin 	= $this->conn_idc;
			$this->conn_catmaster 	= $this->conn_local;
			if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){	
				$this->mongo_flag = 1;
			}
		}else{
			$message = "Invalid Module.";
			echo json_encode($this->send_die_message($message));
			die();
		}		
	}
	function setCommonInfo(){
		$this->configobj = new configclass();		
	}
	function getDocid(){
		$docid = '';
		$sel_docid = "SELECT docid FROM tbl_id_generator WHERE parentid='".$this->parentid."'";
		$res_docid = parent::execQuery($sel_docid, $this->conn_iro);
		if($res_docid && parent::numRows($res_docid)>0){
			$row_docid= parent::fetchData($res_docid);
			if($row_docid[docid]!=''){
				$docid = $row_docid[docid];
			}
		}
		return $docid;
	}
	
	function storeBudgetDetails(){		
		$done 	 = 0; $sub_ins_new = '';
		$res_arr = array();		
													
		$dataArr		= $this->readExistingBudgetDetails();				
		$exist_result   = $this->readMappingTableEntry();		
		
		$difference_cnt = $dataArr['difference_cnt'];
		$countBudget 	= $dataArr['countBudget'];				
		$count_map 		= $exist_result['count'];
		//~ echo "<br>countBudget:---".$countBudget;
		//~ echo "<br>count_map:---".$count_map;
		//~ echo "<pre>dataArr:--";print_r($dataArr);		
		if(count($this->finTempArr)>0){
			$campaigns_selected   = ''; 
			$campaignNames 		= '';
			foreach($this->finTempArr as $key=>$value){				
				if($campaigns_selected =='')
					$campaigns_selected = $value['actcamp'];
				else
					$campaigns_selected .= ",".$value['actcamp'];
					
				if($campaignNames=='')
					$campaignNames = $value['campname'];
				else
					$campaignNames .= ",".$value['campname'];
			}
		}
		
		if($countBudget==0 && $count_map==0){ //NEW entry
			
			$insertDetails = "INSERT INTO tbl_proposal_budget_diffrence_log(parentid, docid, insert_date, data_city, version, client_email, user_code, module, mail_sent, campaigns_selected, campaignNames,source,mapping_result,pdf_file_name,payment_type,total_budget,total_duration) VALUES('".$this->parentid."', '".$this->docid."', '".date('Y-m-d H:i:s')."','".$this->data_city."' , '".$this->version."', '".$this->client_mailid."', '".$this->ucode."', '".$this->module."', '1', '".$campaigns_selected."', '".$campaignNames."', '".$this->source."','', '".$this->pdf_file_name."', '".$this->payment_type."', '".json_encode($this->total_budget)."', '".json_encode($this->total_duration)."')";
			$resDetails	   =  parent::execQuery($insertDetails, $this->db_budgeting);				
			$sub_ins_new   = $this->GetPlatDiamCategories();			
			if($sub_ins_new!='' && $resDetails){
				$ins_inter_sql="INSERT INTO tbl_proposal_budget_mapping_details(parentid,version,catid,national_catid,pincode_list,cat_budget,updatedby,updatedon,id)
								VALUES $sub_ins_new";
				$res_ins = parent::execQuery($ins_inter_sql, $this->db_budgeting);	
			}
			if($res_ins && $resDetails){			
				$returnArr['errorCode'] = 0;
				$returnArr['errorMsg']  = 'new entry made Successfully';
			}else{			
				$returnArr['errorCode'] = 1;
				$returnArr['errorMsg']  = 'new insert Query Failed';
			}			
		}else{
			$makeNewEntry 	= 0;		
			//~ echo "<pre>params:--";print_r($this->params);							
			if($difference_cnt>=0){ //if no difference in selected campaigns, check fr pincode_list								
				$exisiting_picode_list = $exist_result['result'];
				$this->GetPlatDiamCategories_new();			
				$new_pincode_list 	   = $this->newpincodeList;	
				//~ echo "<pre>new_pincode_list:---";print_r($new_pincode_list);
				//~ echo "<pre>exisiting_picode_list:---";print_r($exisiting_picode_list);
				
				if(count($new_pincode_list)>0){
					$new_pincode_list_keys = array_keys($new_pincode_list);
				}
				if(count($exisiting_picode_list)>0){
					$existing_pincode_keys = array_keys($exisiting_picode_list);
				}
				if((count($new_pincode_list_keys) >count($existing_pincode_keys)) || (count($existing_pincode_keys) >count($new_pincode_list_keys))){
					$makeNewEntry = 1;
				}
				//~ echo "<br>makeNewEntry:-1--".$makeNewEntry;
				if($this->payment_type!='' && $dataArr['data_log']['payment_type']!='' && strtolower($this->payment_type)!=strtolower($dataArr['data_log']['payment_type'])){
					$makeNewEntry = 1;
				}
				//~ echo "<br>makeNewEntry:--2-".$makeNewEntry;
				if($difference_cnt>0){ //change in campaign_ids & campaign_names
					$makeNewEntry = 1;
				}
									
				//~ echo "<br>makeNewEntry:--3-".$makeNewEntry;
				
				if($makeNewEntry==0){ //compare inner arrays content fr pincode selection change							
					foreach($new_pincode_list as $key=>$value){
						$pincodeList = '';
						$pincodeList 	= json_decode($value['pincode_list'],true);
						$oldPinCodeLst  = json_decode($exisiting_picode_list[$key]['pincode_list'],true);		
						//~ echo "<br>:-pincodeList--".count($pincodeList)."--oldPinCodeLst--".count($oldPinCodeLst);
						$final_diff = $this->diff_recursive($pincodeList,$oldPinCodeLst);
						$final_diff1 = $this->diff_recursive($oldPinCodeLst,$pincodeList);
						//~ echo "<pre>final_diff:--";print_r($final_diff);
						//~ echo "<pre>final_diff1:--";print_r($final_diff1);
						if((!empty($final_diff) && count($final_diff)>0) || (!empty($final_diff1) && count($final_diff1)>0) || (count($pincodeList)>count($oldPinCodeLst)) || (count($oldPinCodeLst)>count($pincodeList))){
							$makeNewEntry = 1;									
							break;
						}
					}					
				}
				//~ echo "<br>makeNewEntry:--4-".$makeNewEntry;//die("dfgdf");
				if($makeNewEntry==1){					
					$insertDetails = "INSERT INTO tbl_proposal_budget_diffrence_log(parentid, docid, insert_date, data_city, version, client_email, user_code, module, mail_sent, campaigns_selected, campaignNames,source,mapping_result,pdf_file_name,payment_type,total_budget,total_duration) VALUES('".$this->parentid."', '".$this->docid."', '".date('Y-m-d H:i:s')."','".$this->data_city."' , '".$this->version."', '".$this->client_mailid."', '".$this->ucode."', '".$this->module."', '1', '".$campaigns_selected."', '".$campaignNames."', '".$this->source."','', '".$this->pdf_file_name."', '".$this->payment_type."','".json_encode($this->total_budget)."', '".json_encode($this->total_duration)."')";
					$resDetails	   =  parent::execQuery($insertDetails, $this->db_budgeting);
						
					$sub_ins_new 	= $this->GetPlatDiamCategories();			
					if($sub_ins_new!='' && $resDetails){
						$ins_inter_sql="INSERT INTO tbl_proposal_budget_mapping_details(parentid,version,catid,national_catid,pincode_list,cat_budget,updatedby,updatedon,id)
										VALUES $sub_ins_new";
						$res_ins = parent::execQuery($ins_inter_sql, $this->db_budgeting);	
					}
					if($res_ins && $resDetails){			
						$returnArr['errorCode'] = 0;
						$returnArr['errorMsg']  = 'entry made as changes found';
					}else{			
						$returnArr['errorCode'] = 1;
						$returnArr['errorMsg']  = 'insert Query Failed(change case)';
					}
				}else{
					$returnArr['errorMsg']  = 'no insertion done';
					$returnArr['errorCode'] = 1;
				}
			}			
		}
		return $returnArr;
	}
		
	function getPinZoneMap($pincodelist=null){
		$data_city = $this->data_city;
		if($this->data_city == 'remote'){

			$row_data_city = array();
			$cat_params = array();
			$cat_params['data_city']	= $this->data_city;
			$cat_params['table'] 		= 'gen_info_id';
			$cat_params['module'] 		= $this->module;
			$cat_params['parentid'] 	= $this->parentid;
			$cat_params['action'] 		= 'fetchdata';
			$cat_params['fields']		= 'data_city';
			$cat_params['page']			= 'populate_proposal_details_class';

			$resTempCategory			= 	array();
			$resTempCategory			=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);
			
			if(!empty($resTempCategory) && $resTempCategory['errors']['code']==0){
				$row_data_city = $resTempCategory['results']['data'][$this->parentid];
				$data_city = $row_data_city['data_city'];
			}

			/*$sql_datacity="SELECT data_city FROM db_iro.tbl_companymaster_generalinfo WHERE parentid ='".$this->parentid."'";
			$res_data_city = parent::execQuery($sql_datacity, $this->conn_local);
			$row_data_city = mysql_fetch_assoc($res_data_city);
			$data_city = $row_data_city['data_city'];*/
		}
		$returnarr= array();
		$pincode_cond ='';
		if($pincodelist){
			$pincode_cond = " pincode in (".$pincode.") and ";
		}		
		$sql="SELECT distinct pincode, zoneid FROM tbl_area_master WHERE ".$pincode_cond."  data_city='".$data_city."' AND display_flag>0 AND type_flag=1 AND deleted='0'";
		$res = parent::execQuery($sql, $this->conn_local);
		if($res && mysql_num_rows($res)){
			while($row = mysql_fetch_assoc($res)){
				$returnarr[$row['pincode']]= $row['zoneid'];
			}			
		}
		return $returnarr;
	}
	
	function GetPlatDiamCategories(){		
		$PinZoneMap_arr = $this->getPinZoneMap();				
		$sub_arr		= array();
		$ins_str 		= '';
		$sub_ins_new	= '';
		$pincode_count  = '';		
		$sql = "select * from tbl_bidding_details_intermediate where parentid='".$this -> parentid."' and version='".$this->version."' ";
		$res = parent::execQuery($sql, $this->db_budgeting);
		if(mysql_num_rows($res)==0){
			$sql = "select * from tbl_bidding_details_intermediate_archive where parentid='".$this -> parentid."' and version='".$this->version."' ";
			$res = parent::execQuery($sql, $this->db_budgeting);
		}		
		$possible_positionarray = array(0,1,2,3,4,5,6,7,100);
		if($this->campaignid==1){
			$possible_positionarray	= array(100);
		}
		if($this->campaignid==2){
			$possible_positionarray	= array(0,1,2,3,4,5,6,7);
		}
		
		if(mysql_num_rows($res)){
			$newpincodeList = array();	
			$this->newpincodeList = '';		
			$i=0;
			$id = $this->getId();			
			while($row= mysql_fetch_assoc($res)){						
				$ins_str='(';			
				$catid = $row['catid'];				
				$pincode_list = $row['pincode_list'];						
				$newpincodeList[$i]['pincode_list']  = $pincode_list;				
				$ins_str.="'".$row['parentid']."',";
				$ins_str.="'".$row['version']."',";
				$ins_str.="'".$catid."',";
				$ins_str.="'".$row['national_catid']."',";
				$ins_str.="'".$pincode_list."',";
				$ins_str.="'".$row['cat_budget']."',"; 
				$ins_str.="'".$this->ucode."',"; 
				$ins_str.="'".date("Y-m-d H:i:s")."',"; 				
				$ins_str.="'".$id."')"; 				
				array_push($sub_arr, $ins_str);
				$i++;
			}			
			$pincode_count 		  = $i;
			$this->pincode_count  = $pincode_count;			
			$sub_ins_new		  = implode(",",$sub_arr);  	
		}
		return $sub_ins_new;
	}
	
	function GetPlatDiamCategories_new(){
		$PinZoneMap_arr = $this->getPinZoneMap();				
		$sub_arr		= array();
		$ins_str 		= '';
		$sub_ins_new	= '';
		$pincode_count  = '';		
		$sql = "select * from tbl_bidding_details_intermediate where parentid='".$this -> parentid."' and version='".$this->version."' ";
		$res = parent::execQuery($sql, $this->db_budgeting);
		if(mysql_num_rows($res)==0){
			$sql = "select * from tbl_bidding_details_intermediate_archive where parentid='".$this -> parentid."' and version='".$this->version."' ";
			$res = parent::execQuery($sql, $this->db_budgeting);
		}		
		$possible_positionarray = array(0,1,2,3,4,5,6,7,100);
		if($this->campaignid==1){
			$possible_positionarray	= array(100);
		}
		if($this->campaignid==2){
			$possible_positionarray	= array(0,1,2,3,4,5,6,7);
		}
		
		if(mysql_num_rows($res)){
			$newpincodeList = array();	
			$this->newpincodeList = '';		
			$i=0;					
			while($row= mysql_fetch_assoc($res)){						
				$ins_str='(';			
				$catid = $row['catid'];				
				$pincode_list = $row['pincode_list'];						
				$newpincodeList[$i]['pincode_list']  = $pincode_list;				
				$ins_str.="'".$row['parentid']."',";
				$ins_str.="'".$row['version']."',";
				$ins_str.="'".$catid."',";
				$ins_str.="'".$row['national_catid']."',";
				$ins_str.="'".$pincode_list."',";
				$ins_str.="'".$row['cat_budget']."',"; 
				$ins_str.="'".$this->ucode."',"; 
				$ins_str.="'".date("Y-m-d H:i:s")."',"; 				
				$ins_str.="'".$id."')"; 				
				array_push($sub_arr, $ins_str);
				$i++;
			}
			$this->newpincodeList = $newpincodeList;				
			$sub_ins_new		  = implode(",",$sub_arr);  	
		}
		return $sub_ins_new;
	}
	
	function diff_recursive($array1, $array2) {
		$difference=array();
		foreach($array1 as $key => $value) {
			if(is_array($value) && isset($array2[$key])){ // it's an array and both have the key
				$new_diff = $this->diff_recursive($value, $array2[$key]);
				if( !empty($new_diff) )
					$difference[$key] = $new_diff;
			} else if(is_string($value) && !in_array($value, $array2)) { // the value is a string and it's not in array B
				$difference[$key] = $value . " is missing from the second array";
			} else if(!is_numeric($key) && !array_key_exists($key, $array2)) { // the key is not numberic and is missing from array B
				$difference[$key] = "Missing from the second array";
			}
		}
		return $difference;
	}

	function readExistingBudgetDetails(){
		$difference_cnt = 0;
		$total_budget_new_arr = array(); $total_duration_arr = array();
		$dataArr		= array();
		$Query 			= "SELECT * FROM tbl_proposal_budget_diffrence_log WHERE parentid='".$this->parentid."' AND version='".$this->version."' ORDER BY insert_date DESC LIMIT 1";
		$resQuery 		= parent::execQuery($Query, $this->db_budgeting);	
		$countBudget 	= mysql_num_rows($resQuery);
		if($resQuery && mysql_num_rows($resQuery)>0){
			$rowQuery = mysql_fetch_assoc($resQuery);					
			if(count($this->finTempArr)>0){
				$this->campaigns_selected   = ''; 
				$this->campaignNames 		= '';
				foreach($this->finTempArr as $key=>$value){				
					if($this->campaigns_selected =='')
						$this->campaigns_selected = $value['actcamp'];
					else
						$this->campaigns_selected .= ",".$value['actcamp'];
						
					if($this->campaignNames=='')
						$this->campaignNames = $value['campname'];
					else
						$this->campaignNames .= ",".$value['campname'];
				}
			}
			$exisiting_campaignids  = $rowQuery['campaigns_selected'];
			//~ echo "<br>exisiting_campaignids:---".$exisiting_campaignids;
			$exisiting_campaignames = $rowQuery['campaignNames'];
			$this->last_insert_date = $rowQuery['insert_date'];
			$this->category_count 	= $rowQuery['category_count'];
			$this->id 				= $rowQuery['id'];
			$total_budget_existing  = $rowQuery['total_budget'];
			//~ echo "<br>total_budget_existing:--from table:--".$total_budget_existing;
			$total_duration_existing  = $rowQuery['total_duration'];
			//~ echo "<br>total_budget_existing:--436:---".$total_budget_existing;
			//~ echo "<br>total_budget_existing:--436:---".$total_budget_existing;
			if($total_duration_existing!='' && $total_duration_existing!=null){
				$total_duration_existing_arr = json_decode($total_duration_existing,1);
				//~ echo "<pre>total_duration_existing_arr:--";print_r($total_duration_existing_arr);echo "<br>";
			}
			if($total_budget_existing!='' && $total_budget_existing!=null){
				$total_budget_existing_arr = json_decode($total_budget_existing,1);
				//~ echo "<pre>after json_decode:---";print_r($total_budget_existing_arr);echo "<br>break";
			}			
			//~ echo "<pre>this-445-->total_budget:---";print_r($this->total_budget);echo "<br>breaking;;;";
			//~ echo "<pre>this-445-->total_duration:---";print_r($this->total_duration);echo "<br>breaking;;;";
			$total_budget_new_arr = $this->total_budget;
			$total_duration_arr   = $this->total_duration;
			
			//~ echo "<br>total_duration_arr_new:--";print_r($total_duration_arr);echo "<br>count -----------".count($total_duration_arr)."<br>;";
			//~ echo "<br>total_duration_existing_arr:--";print_r($total_duration_existing_arr);echo "<br>";
			//~ echo "<br>total_budget_new_arr:--";print_r($total_budget_new_arr);echo "<br>";
			//~ echo "<br>total_budget_existing_arr:-before unique-";print_r($total_budget_existing_arr);echo "<br>";
			
			
			$total_budget_existing_arr   = array_filter($total_budget_existing_arr);
			//~ echo "<br>total_budget_existing_arr:-after unique-";print_r($total_budget_existing_arr);echo "<br>";
			$total_duration_existing_arr = array_filter($total_duration_existing_arr);
			$exisiting_campaignids_arr   = array_unique(array_filter(explode(",",$exisiting_campaignids)));
			$exisiting_campaignames_arr  = array_unique(array_filter(explode(",",$exisiting_campaignames)));
			$new_campaignids_arr         = array_unique(array_filter(explode(",",$this->campaigns_selected)));
			$new_campaignames_arr        = array_unique(array_filter(explode(",",$this->campaignNames)));								
			$hourdiff1 = round((strtotime(date('Y-m-d H:i:s')) - strtotime($rowQuery['insert_date']) )/3600, 1);			
			if(trim($hourdiff1) >= 1){				
				$difference_cnt = $difference_cnt+1;
			}
			
			if((count($total_budget_existing_arr)>count($total_budget_new_arr)) || (count($total_budget_new_arr)>count($total_budget_existing_arr))){
				$difference_cnt = $difference_cnt+1;
				//~ echo "<br>000";
			}
			if((count($total_duration_existing_arr)>count($total_duration_arr)) || (count($total_duration_arr)>count($total_duration_existing_arr))){
				$difference_cnt = $difference_cnt+1;
				//~ echo "<br>9999";
			}
			if(count($total_budget_new_arr)>0){
				$totBudgetNew = 0;
				foreach($total_budget_new_arr as $key=>$budget){
					//~ echo "<br>key:---".$key."--budget--new----".$budget;
					$totBudgetNew = $totBudgetNew+$budget;					
				}
			}
			//~ echo "<pre>total_budget_existing_arr bfore loop:---";print_r($total_budget_existing_arr);
			if(count($total_budget_existing_arr)>0){
				$totBudgetOld = 0;
				foreach($total_budget_existing_arr as $key=>$budget){
					//~ echo "<br>key:---".$key."--budget--old----".$budget;
					$totBudgetOld = $totBudgetOld+$budget;					
				}
			}
			//~ echo "<br>totBudgetNew:---".$totBudgetNew;
			//~ echo "<br>totBudgetOld:---".$totBudgetOld;
			//die;
			if(count($total_duration_arr)>0){
				$totalDurationNew = 0;
				foreach($total_duration_arr as $key=>$duration){
					//~ echo "<br>key:---".$key."--duration--old----".$duration;
					$totalDurationNew = $totalDurationNew+$duration;					
				}
			}
			if(count($total_duration_existing_arr)>0){
				$totalDurationOld = 0;
				foreach($total_duration_existing_arr as $key=>$duration){
					//~ echo "<br>key:---".$key."--duration--new----".$duration;
					$totalDurationOld = $totalDurationOld+$duration;					
				}
			}			
			
			//~ echo "<br>totalDurationNew:---".$totalDurationNew;
			//~ echo "<br>totalDurationOld:---".$totalDurationOld;
			if(($totBudgetNew>$totBudgetOld) || ($totBudgetOld>$totBudgetNew)){
				$difference_cnt = $difference_cnt+1;
				//~ echo "<br>888";
			}
			if(($totalDurationNew>$totalDurationOld) || ($totalDurationOld>$totalDurationNew)){
				$difference_cnt = $difference_cnt+1;
				//~ echo "<br>777";
			}
			if(count($new_campaignids_arr)>0 || count($exisiting_campaignids_arr)>0){						
				$result_id = array_merge(array_diff($new_campaignids_arr, $exisiting_campaignids_arr), array_diff($exisiting_campaignids_arr, $new_campaignids_arr));		
			}			
			
			if(count($exisiting_campaignames_arr)>0 || count($new_campaignames_arr)>0){						
				$result_names = array_merge(array_diff($new_campaignames_arr, $exisiting_campaignames_arr), array_diff($exisiting_campaignames_arr, $new_campaignames_arr));						
			}
			if($this->payment_type!='' && strtolower($this->payment_type)!=strtolower($rowQuery['payment_type'])){
				//~ echo "<br>111";
				$difference_cnt = $difference_cnt+1;
			}
			if(count($result_names)>0){
				//~ echo "<br>222";
				$difference_cnt = $difference_cnt+1;
			}
			if(count($result_id)>0){
				//~ echo "<br>333";
				$difference_cnt = $difference_cnt+1;
			}
		}
					
		//~ echo "<br>difference_cnt:--".$difference_cnt;
		//~ die("dfg");
		$dataArr['difference_cnt'] = $difference_cnt;
		$dataArr['data_log'] 	   = $rowQuery;
		$dataArr['countBudget']    = $countBudget;
		//~ echo "<pre>dataArr:--";print_r($dataArr);
		return $dataArr;
	}
	
	function readMappingTableEntry(){
		$result_arr = array();
		$data_array = array();		
		$dateCondition = ''; $LimitCond= '';
		if($this->last_insert_date!=''){
			$dateCondition = " AND updatedon='".$this->last_insert_date."' AND updatedon<=NOW()";
		}
		if($this->category_count!=0){
			$LimitCond = " LIMIT  ".$this->category_count." ";
		}
		if($this->id!=''){
			$idCond = " AND  id='".$this->id."'";
		}		
		$readEnrty  = "SELECT pincode_list FROM tbl_proposal_budget_mapping_details WHERE parentid='".$this->parentid."' AND version = '".$this->version."' ".$idCond."  ORDER BY updatedon desc ".$LimitCond."";		
		//~ echo "<pre>readEnrty:---".$readEnrty;
		$resEntry = parent::execQuery($readEnrty, $this->db_budgeting);
		$count    = mysql_num_rows($resEntry);		
		if($resEntry && mysql_num_rows($resEntry)>0){
			while($rowResult = mysql_fetch_assoc($resEntry)){
				$result_arr[] = $rowResult;
			}
		}
		$data_array['count']  = $count;
		$data_array['result'] = $result_arr;
		return $data_array;
	}
	
	function getId(){
		$id= '';
		$getId = "SELECT id , parentid FROM tbl_proposal_budget_diffrence_log WHERE parentid='".$this->parentid."' AND version='".$this->version."' ORDER BY insert_date DESC LIMIT 1";
		$resId = parent::execQuery($getId, $this->db_budgeting);
		$count = mysql_num_rows($resId);	
		$rowId = mysql_fetch_array($resId);
		$id    = $rowId['id'];
		return $id;
	}
	
	function curlCall($curl_url,$data){	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curl_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$content  = curl_exec($ch);
		curl_close($ch);
		return $content;
	}
	
	private function getURLInfo($data_city){
		return $urldetails	= $this->configobj->get_url($data_city);		
	}
	
	private function send_die_message($msg){
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
	
	function addslashesArray($resultArray){
		foreach($resultArray AS $key=>$value)
		{
			$resultArray[$key] = addslashes(stripslashes(trim($value)));
		}		
		return $resultArray;
	}
}
?>
