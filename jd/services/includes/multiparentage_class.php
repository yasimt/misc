<?php
class multiparentage_class extends DB
{
	var  $conn_iro    	= null;
	var  $conn_jds   	= null;
	var  $conn_tme 		= null;
	var  $conn_idc    	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	
	
	function __construct($params)
	{	
		GLOBAL $params;
 		$parentid 			= trim($params['parentid']);
		$module 			= trim($params['module']);
		$ucode 				= trim($params['ucode']);
		$uname 				= trim($params['uname']);
		$data_city 			= trim($params['data_city']); 	
		$rquest 			= trim($params['rquest']);
		$catid_list			= trim($params['catid_list']);
 		if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->send_die_message($message));
            die();
        }
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(trim($module)=='')
		{
			$message = "Module is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}		
		if(trim($rquest)=='')
		{
			$message = "Invalid Request Name.";
			echo json_encode($this->send_die_message($message));
			die();
		}		 
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->ucode  	  	= $ucode;
		$this->uname  	  	= $uname;
		$this->rquest  	  	= $rquest;
		$this->catid_list	= $catid_list;
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->categoryClass_obj = new categoryClass();
		$this->setServers();
		
		$this->add_catlin_nonpaid_db = 0;
		if(($this->module == 'DE') || ($this->module == 'CS'))
		{
			$this->add_catlin_nonpaid_db = 1;
		}		 
		$this->remove_catidlist_arr = array();
		
		if(!empty($params['removed_catid']))
		{
			$this->remove_catidlist_arr = explode(',',$params['removed_catid']);			
			$this->remove_catidlist_arr = array_map('trim',$this->remove_catidlist_arr);			
			$this->remove_catidlist_arr = array_unique(array_filter($this->remove_catidlist_arr));
		}
		$this->removeCats 			= str_replace("," ,"|P|",$params['cat_for_moderation']);			
		$this->debug_mode		= 	0;
		if($params['trace']==1){
			$this->debug_mode	= 	1;
			print"<pre>";print_r($params);
		}
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		GLOBAL $db;
			
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->conn_tme  		= $db[$conn_city]['tme_jds']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];
		$this->conn_data  		= $db[$conn_city]['data_correction']['master'];
		
		if(($this->module =='DE') || ($this->module =='CS'))
		{
			$this->conn_temp	 	= $this->conn_local;
			$this->conn_catmaster 	= $this->conn_local;
		}
		elseif($this->module =='TME')
		{
			$this->conn_temp		= $this->conn_tme;
			$this->conn_catmaster 	= $this->conn_local;
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($conn_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}

		}
		elseif($this->module =='ME')
		{
			$this->conn_temp		= $this->conn_idc;
			$this->conn_catmaster 	= $this->conn_local;
			if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){	
				$this->mongo_flag = 1;
			}
		}
		else
		{
			$message = "Invalid Module.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		$this->liveParentageInfoArr = array();		
		$this->tempParentageInfoArr = array();
		$this->doneArr = array();
		$this->matchedCatidStr = '';
		$this->magic_counter = 0;
		$contract_details  		= $this->getContractsDetails();
		$this->edit_flag 		= $contract_details['edit_flag'];
		$this->exist_category 	= $contract_details['exist_category'];
	}	
	function multiparentage() {
		$func = $this->rquest;
		if((int)method_exists($this,$func) > 0)
			return $this->$func();
		else 
		{
			$message = "invalid function";
			return json_encode($this->send_die_message($message));			
		}
	}
	function check_multiparentange()
	{
		GLOBAL $params;
		$response_send = 0;
		$exclusion_chk_flg = $this->isExclusionTypeContract();
		if($exclusion_chk_flg == 1){
			return $output_final	=	$this->send_die_message("no popup");
		}
		$brand_bypass_check_flag = $this->contractByPassCheck(2);
		if($brand_bypass_check_flag == 1){
			return $output_final	=	$this->send_die_message("no popup");
		}
		$this->catid_list_array = explode(",",$this->catid_list);
		$this->temp_catids_arr = array_diff($this->catid_list_array,$this->remove_catidlist_arr);
		$this->temp_catids_arr = $this->get_valid_categories($this->temp_catids_arr);
		
		$this->allowmulticat_arr = array();
		if(($this->module == 'DE' || $this->module == 'CS') && (count($this->temp_catids_arr) >0 )){
			$this->allowmulticat_arr = $this->allowMultiparentCatChk($this->temp_catids_arr);
			if($this->debug_mode){
				echo "<br>Allow Multiparentage Catid : ".implode($this->allowmulticat_arr);
			}
			if(count($this->allowmulticat_arr)>0){
				$this->temp_catids_arr = array_diff($this->temp_catids_arr,$this->allowmulticat_arr);
				$this->temp_catids_arr = $this->get_valid_categories($this->temp_catids_arr);
			}
		}
		if(count($this->temp_catids_arr)<=0){
			return $output_final	=	$this->send_die_message("no popup");
		}
		$popup_type = $this->getPopupType();
		if($popup_type == "new")
		{
			if(count($this->tempParentageInfoArr)>1)
			{
				$distinct_cat_flag = $this->distinctCategoryCheck($this->tempParentageInfoArr); // function to check distinct category in parentage
				if($distinct_cat_flag ==1)
				{
					$response_send = 1;
					$output_array['popup_type'] 		= "new";
					$output_array['parentage_info'] 	= $this->tempParentageInfoArr;
					$output_final['data'] 				=  $output_array;
					$output_final['error']['code'] 		=  "0";
					$output_final['error']['message'] 	=  "show popup";
					$this->insertMultiparentageLog($popup_type);
					return $output_final;
				}
			}
		}
		else if($popup_type == "edit")
		{
			if(count($this->tempParentageInfoArr)>1){
				$liveParentageKeysArr = array_keys($this->liveParentageInfoArr);
				$cat_parent_info_arr = $this->getCategoryWiseParentInfoArr();
				$temp_cat_parentage_arr = array();
				if(count($this->temp_catids_arr)>0){
					foreach($this->temp_catids_arr as $tempCatid){
						$cat_parent_list_arr = $cat_parent_info_arr[$tempCatid];
						if(is_array($cat_parent_list_arr)){
							$temp_cat_parentage_arr= array_merge($temp_cat_parentage_arr,$cat_parent_list_arr);
						}
					}					
				}
				$temp_cat_parentage_arr = array_merge(array_unique(array_filter($temp_cat_parentage_arr)));
				
				$mainParentageArr = $liveParentageKeysArr;
				if(count($temp_cat_parentage_arr)>0){
					$this->doMagic($mainParentageArr,$cat_parent_info_arr);
				}
				if(count($this->doneArr)>0){
					$mismatched_parent_arr = array_diff($temp_cat_parentage_arr,$this->doneArr);
					$mismatched_parent_arr = array_merge(array_unique(array_filter($mismatched_parent_arr)));
				}
				if(count($mismatched_parent_arr)>0){
					foreach($mismatched_parent_arr as $mismatchedParentageName){
						$addedCatidsList 	.= $this->tempParentageInfoArr[$mismatchedParentageName]['catid']."|~|";
						$addedCatnamesList 	.= $this->tempParentageInfoArr[$mismatchedParentageName]['catname']."|~|";
					}
				}	
				$addedCatListArr = array();
				if($addedCatidsList){
					$addedCatListArr = explode("|~|",$addedCatidsList);
					$addedCatListArr = array_merge(array_filter(array_unique($addedCatListArr)));
				}
				if(count($addedCatListArr)>0){
					$catnameArr = $this->getCategoryDetails($addedCatListArr,1);
					$response_send = 1;
					$output_final['data']['popup_type'] 		= 	'edit';
					$output_final['data']['catid'] 				= 	implode("|~|",$addedCatListArr);
					$output_final['data']['catname']			= 	implode("|~|",$catnameArr); 
					$output_final['error']['code'] 				=  	"0";
					$output_final['error']['message'] 			=  	"show popup";
					$this->insertMultiparentageLog($popup_type);
					return $output_final;
				}
			}
		}	
		if($response_send !=1){
			return $output_final	=	$this->send_die_message("no popup");
		}
	}
	function doMagic($mainParentageArr,$cat_parent_info_arr)
	{
		if($this->debug_mode && $newcall !=1){
			echo "<br>cat parent info arr : ";print"<pre>";print_r($cat_parent_info_arr);
		}
		$mainParentageArr = array_diff($mainParentageArr,$this->doneArr);
		$mainParentageArr = array_merge(array_unique(array_filter($mainParentageArr)));
		if(count($mainParentageArr)>0){
			$newMainParentage = array();
			foreach($mainParentageArr as $mainParentage){
				$this->doneArr[] = $mainParentage;
				foreach($cat_parent_info_arr as $parentage_catid => $parentage_list){
					if(in_array($mainParentage,$parentage_list)){
						$newMainParentage = array_merge($newMainParentage,$parentage_list);
					}
				}
			}
			$newMainParentage = array_merge(array_unique(array_filter($newMainParentage)));
		}
		if($this->debug_mode){
			echo "<br>Done Array For Function Call : ".$this->magic_counter; print"<pre>";print_r($this->doneArr);
		}
		if(count($newMainParentage)>0){
			$this->magic_counter ++;
			$this->doMagic($newMainParentage,$cat_parent_info_arr);
		}
	}
	function getPopupType() // new / edit
	{
		$popup_val = '';
		$this->reset_flag 		= $this->getResetFlag();
		
		if(!empty($this->exist_category)){
			$existingCatArr 	  = explode(",",$this->exist_category);
		}
		if(count($existingCatArr)>0){
			$this->liveParentageInfoArr 	= $this->getParentageInfo($existingCatArr,'Live');
		}
		if(count($this->temp_catids_arr)>0){
			$this->tempParentageInfoArr 	= $this->getParentageInfo($this->temp_catids_arr,'Temp');
		}
		$tempLiveCommonParentArr = array();
		$tempLiveCommonParentArr = array_intersect(array_keys($this->tempParentageInfoArr),array_keys($this->liveParentageInfoArr));
		
		if((count($this->liveParentageInfoArr)>0) && ($this->reset_flag !=1) && (count($tempLiveCommonParentArr)>0)){
			$popup_val = 'edit';
		}else{
			$popup_val = 'new';
		}
		if($this->debug_mode)
		{
			echo '<hr><B>Category Reset Flag -></B>'.$this->reset_flag;
			echo '<br><B>Contract Existing Flag -></B>'.$this->edit_flag;
			echo '<br><B>Temp Catid-></B>'.implode(",",$this->temp_catids_arr);
			echo '<br><B>Live Catid-></B>'.$this->exist_category;
			echo '<br><B>Common Parentage-></B>'.implode(",",$tempLiveCommonParentArr);
			echo '<br><B>Popup Type-></B><B><font color=blue> '.strtoupper($popup_val).'</font></B>';
			echo '<br>';
		}
		return $popup_val;
	}
	
	function getParentageInfo($catids_arr,$flow_type)
	{
		$unallocated_parentcatids_arr = array("999999997","999999998");
		$parentDetailsArr = array();
		if(count($catids_arr)>0)
		{
			$catid_comma_separated = implode(",",$catids_arr); 
			$catids_str = implode(",",$catids_arr);
			//$sqlParentInfo = "SELECT parentlineage,GROUP_CONCAT(DISTINCT category_name SEPARATOR '|~|') AS catnamelist, GROUP_CONCAT(DISTINCT catid SEPARATOR '|~|') AS catidlist,TRIM(BOTH '/' from substring_index(parentlineage,'/',2)) AS parentage,TRIM(BOTH '/' from substring_index(parent_catid_lineage,'/',2)) AS parentcatid FROM tbl_categorymaster_parentinfo WHERE catid IN ('".$catids_str."') GROUP BY parentage HAVING (parentage!='' AND parentage!='/' AND parentage !='B2B' AND parentage !='RECYCLE BIN (P)' AND parentage !='B2C Products' AND parentage !='B2C Services')";
			//$resParentInfo = parent::execQuery($sqlParentInfo, $this->conn_catmaster);
			$cat_params = array();
			$cat_params['page'] 		= 'multiparentage_class';
			$cat_params['scase'] 		= '3';
			$cat_params['parentid'] 	= $this->parentid;
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'catid,category_name,parentlineage';

			$where_arr  	=	array();
			if($catids_str!=''){
				$where_arr['catid']		= $catids_str;
				$cat_params['where']	= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($this->debug_mode)
			{
				echo '<hr><B>'.$flow_type.' Parent Info Query -></B>'.$sqlParentInfo;
				echo '<br><B>Resource -></B>'.$resParentInfo;
				echo '<br><B>Rows Num-></B>'.mysql_num_rows($resParentInfo);
				echo '<br><B>Mysql Error-></B>'.mysql_error();
				echo '<br><B>'.$flow_type.' Category -></B>'.$catid_comma_separated;
				echo '<hr>';
			}			
			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
			{
				foreach($cat_res_arr['results'] as $key =>$row_parentinfo )
				{
					$catnamelist 	= trim($row_parentinfo['catnamelist']);
					$catidlist 		= trim($row_parentinfo['catidlist']);
					$parentage 		= trim($row_parentinfo['parentage']);
					$parentcatid 	= trim($row_parentinfo['parentcatid']);
					if($this->module =='DE'){
						$parentage = ucwords(strtolower($parentage));
					}
					
					if(!in_array($parentcatid,$unallocated_parentcatids_arr))
					{
						$parentDetailsArr[$parentage]['catname'] 	= $catnamelist;
						$parentDetailsArr[$parentage]['catid'] 		= $catidlist;
					}
				}
			}
			if($this->debug_mode)
			{
				echo "<b>Parentage Info for <font color=blue>".strtoupper($flow_type)."</font> category</b>";
				print"<pre>";print_r($parentDetailsArr);
			}
		}
		return $parentDetailsArr;
	}
	function distinctCategoryCheck($parentageArr)
	{
		$distinct_catflag = 1;
		$parentageKeysArr = array_keys($parentageArr);
		$allCatidList = '';
		if(count($parentageKeysArr)>0){				
			foreach($parentageKeysArr as $parentageName){
				$allCatidList 	.= $parentageArr[$parentageName]['catid']."|~|";
			}
		}
		$allCatidListArr = array();
		if($allCatidList){
			$allCatidListArr = explode("|~|",$allCatidList);
			$allCatidListArr = array_merge(array_filter(array_unique($allCatidListArr)));
		}
		foreach($parentageArr as $parentageKey => $parentageVal){
			$parantageCatidList = '';
			$parantageCatidList = $parentageVal['catid'];
			$parantageCatidListArr = array();
			$parantageCatidListArr = explode("|~|",$parantageCatidList);
			$parantageCatidListArr = array_merge(array_filter(array_unique($parantageCatidListArr)));
			$parentageDiffArr = array_diff($allCatidListArr,$parantageCatidListArr);
			if(count($parentageDiffArr)<=0){
				$distinct_catflag = 0;
			}
		}
		if($this->debug_mode){
			echo "<b> Distinct Category Flag : </b>".$distinct_catflag."<br><br>";
		}
		return $distinct_catflag;
	}
	function getCategoryWiseParentInfoArr()
	{
		$cat_parent_info_arr = array();
		if(count($this->temp_catids_arr) && count($this->tempParentageInfoArr)){
			foreach($this->temp_catids_arr as $tempCatId){
				foreach($this->tempParentageInfoArr as $pName => $catDetails){
					$catDetailsArr = array();
					$catDetailsArr = explode("|~|",$catDetails['catid']);
					$catDetailsArr = array_unique($catDetailsArr);
					if(in_array($tempCatId,$catDetailsArr)){
						$cat_parent_info_arr[$tempCatId][] = $pName;
					}
				}
			}
		}
		return $cat_parent_info_arr;
	}
	function insertIntoCCRMultiParent()
	{ 
		GLOBAL $params;
		if($params['trace'] == 1)
		{
			print_R($params);
		}
		$contract_existing_temp_cat_arr = $this->fetch_contract_temp_categories();
		$arr_old_cat 		= trim(str_replace("/", "", $this->exist_category), ",");
		$moderated_catids 	= trim(str_replace("/", "", $params['cat_for_moderation']), ",");
		$catid_selected 	= trim(str_replace("/", "", $params['catid_selected']), ",");
		
		$moderated_catids_array = array();
		$moderated_catids_array = explode(",",$moderated_catids); 
		
		$catid_selected_array = array();
		$catid_selected_array = explode(",",$catid_selected); 
		
		$this->moderated_catids_arr = array_diff($moderated_catids_array,$catid_selected_array);
		$this->moderated_catids_arr = $this->get_valid_categories($this->moderated_catids_arr);
		
		$selectedCatlistArr = array();
		if(!empty($params['catid_selected'])){ // New Popup
			$selectedCatlist = $params['catid_selected'];
			$selectedCatlistArr = explode(",",$selectedCatlist);
			$selectedCatlistArr = $this->get_valid_categories($selectedCatlistArr);
		}else{
			$ignored_catidlist_arr = array();
			$ignored_catidlist_arr = array_merge($this->moderated_catids_arr,$this->remove_catidlist_arr);
			$ignored_catidlist_arr = $this->get_valid_categories($ignored_catidlist_arr);
			$selectedCatlistArr	= array_diff($contract_existing_temp_cat_arr,$ignored_catidlist_arr);
			$selectedCatlistArr = $this->get_valid_categories($selectedCatlistArr);
		}

		if(count($selectedCatlistArr)>0)
		{
			$selected_catids_str 	= implode(",",$selectedCatlistArr);
			//$sqlSelectedCategories 	= "SELECT GROUP_CONCAT(catid) as catids, GROUP_CONCAT(CONCAT(parentlineage,category_name)) AS old_cat_parentlineage FROM tbl_categorymaster_parentinfo WHERE catid IN ('".$selected_catids_str."') ";
			//$resSelectedCategories 	= parent::execQuery($sqlSelectedCategories, $this->conn_catmaster);
			$cat_params = array();
			$cat_params['page'] 		= 'multiparentage_class';
			$cat_params['q_type'] 		='parentinfo';
			$cat_params['parentid'] 	= $this->parentid;
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'parentlineage,category_name,catid';

			$where_arr  	=	array();
			if($selected_catids_str!=''){
				$where_arr['catid']		= $selected_catids_str;
				$cat_params['where']	= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){
				//$data_selected_cat		= mysql_fetch_assoc($resSelectedCategories);
				$old_cat_parentlineage_arr  = array();
				foreach ($cat_res_arr['results'] as $key => $cat_arr) {
					$old_cat_parentlineage_arr[] = $cat_arr['parentlineage'].$cat_arr['category_name'];
				}
				$old_cat_parentlineage =	implode(",", $old_cat_parentlineage_arr);
				//$old_cat_parentlineage 	= $data_selected_cat['old_cat_parentlineage'];
			}
		}
		if(count($this->moderated_catids_arr)>0)
		{
			$moderated_catids_str = implode(",",$this->moderated_catids_arr);
			//$sqlModeratedCategories = "SELECT GROUP_CONCAT(catid SEPARATOR '|~|') as catids,GROUP_CONCAT(category_name SEPARATOR '|~|') as catnames, GROUP_CONCAT(CONCAT(parentlineage,category_name) SEPARATOR '|~|') AS new_cat_parentlineage FROM tbl_categorymaster_parentinfo WHERE catid IN ('".$moderated_catids_str."')";
			//$resModeratedCategories 	= parent::execQuery($sqlModeratedCategories, $this->conn_catmaster);
			$cat_params = array();
			$cat_params['page'] 		= 'multiparentage_class';
			$cat_params['q_type'] 		= 'parentinfo';
			$cat_params['parentid'] 	= $this->parentid;
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'catid,category_name,parentlineage';

			$where_arr  	=	array();
			if($moderated_catids_str!=''){
				$where_arr['catid']		= $moderated_catids_str;
				$cat_params['where']	= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}
			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0){
				//$data_moderated_cat		= mysql_fetch_assoc($resModeratedCategories);
				$new_cat_parentlineage_arr  = array();
				$cat_arr 					= array();
				$catname_arr 				= array();
				foreach ($cat_res_arr['results'] as $key => $cat_arr) {
					$new_cat_parentlineage_arr[] = $cat_arr['parentlineage'].$cat_arr['category_name'];
					$cat_arr[] 					 = $cat_arr['catid'];
					$catname_arr[] 				 = $cat_arr['category_name']; 
				}

				$catids 	=	implode("|~|", $cat_arr);
				$catnames 	=	implode("|~|", $catname_arr);

				$new_cat_parentlineage 	= implode("|~|",$new_cat_parentlineage_arr);
				$requested_catids 		= $catids;
				$requested_catnames 	= $catnames;
			}
		}
		
		if($this->removeCats!='' && $this->removeCats!=null){ //this table is used in dealclose, dnt remove this code
			$makeEntry = "INSERT INTO online_regis1.tbl_removed_categories
											SET
											parentid = '".$this->parentid."',
											catIds = '".$catid_selected."', 
											removed_categories = '".$this->removeCats."',
											ucode = '".$this->ucode."', 
											updatedOn= '".date('Y-m-d H:i:s')."',
											module = '".$this->module."'
											ON DUPLICATE KEY UPDATE
											catIds = '".$catid_selected."', 
											removed_categories = '".$this->removeCats."',
											ucode = '".$this->ucode."', 
											updatedOn= '".date('Y-m-d H:i:s')."',
											module = '".$this->module."' ";
			$resMEntry = parent::execQuery($makeEntry, $this->conn_idc);				
		}
		
		$new_param = array();
		$new_param['parentid'] 			 = $params['parentid'];
		$new_param['companyname'] 		 = $params['companyname'];
		$new_param['exist_category'] 	 = $old_cat_parentlineage;
		$new_param['requested_category'] = $new_cat_parentlineage;
		$new_param['requested_catids'] 	 = $requested_catids;
		$new_param['requested_catnames'] = $requested_catnames;
		$new_param['requested_by'] 		 = $params['ucode'];
		$new_param['requested_by_name']  = $params['uname'];
		$new_param['requested_date'] 	 = date("Y-m-d H:i:s");
		$new_param['module_type'] 		 = $params['module'];
		$new_param['data_city'] 		 = $params['data_city'];
		
		$time_stamp = date_create();
		$uniqueid 	= date_format($time_stamp, 'U');
		if($old_cat_parentlineage && $new_cat_parentlineage && $requested_catids){
			$arr_rc	 	= explode("|~|",$new_param['requested_category']);
			$arr_rcids	= explode("|~|",$new_param['requested_catids']);
			$arr_rcnms	= explode("|~|",$new_param['requested_catnames']);
			$count 		= count($arr_rcids);
			for($i=0; $i<$count; $i++)
			{
				$sql = "INSERT INTO tbl_ccr_multiparent_contract SET
					parentid				=	'".$new_param['parentid']."',
					companyname				=	'".addslashes($new_param['companyname'])."',
					exist_category 			=	'".addslashes($new_param['exist_category'])."',
					requested_category 		=	'".addslashes($arr_rc[$i])."',
					requested_catids		=	'".addslashes($arr_rcids[$i])."',
					category_name			=	'".addslashes($arr_rcnms[$i])."',
					requested_by 			=	'".$new_param['requested_by']."',
					loginName				=	'".addslashes($new_param['requested_by_name'])."',
					requested_date 			=	'".$new_param['requested_date']."',
					module					=	'".$new_param['module_type']."',
					data_city				=	'".$params['data_city']."',
					uniqueid				=	'".$uniqueid."'";
				
				 $res_sql 	= parent::execQuery($sql, $this->conn_local);			
			}
		}else{
			$res_sql = true;
		}
		if($res_sql)
		{
			$sqlUpdateOldData = "UPDATE tbl_ccr_multiparent_contract SET status = 3 WHERE parentid = '".$this->parentid."' AND uniqueid != '".$uniqueid."' AND status = 0";
			$resUpdateOldData = parent::execQuery($sqlUpdateOldData, $this->conn_local);
			if($resUpdateOldData)
			{
				 $sqlUpdTempFlow = "INSERT INTO tbl_temp_flow_status 
				 					SET 
				 						parentid = '".$this->parentid."',
				 						ccr_uniqueid = '".$uniqueid."'
				 					ON DUPLICATE KEY UPDATE 
				 					 	ccr_uniqueid = '".$uniqueid."' ";
				 $res_sql 	= parent::execQuery($sqlUpdTempFlow, $this->conn_temp);
			}
			return $this->populate_category_temp_data();			
		}
		else 
		{
			$output_final	=	$this->send_die_message("Insert failed");			
			return  $output_final;
		}
	}
	function populate_category_temp_data()
	{
		GLOBAL $params;
		
		$this->remove_catidlist_arr = array_merge($this->moderated_catids_arr,$this->remove_catidlist_arr);
		$this->remove_catidlist_arr = $this->get_valid_categories($this->remove_catidlist_arr);
		if(count($this->temp_paid_cat_arr) >0)
		{
			if(count($this->remove_catidlist_arr)>0)	
			{
				$final_temp_paid_cat_arr = array();
				$matched_paid_cat_arr = array();
				$matched_paid_cat_arr = array_intersect($this->temp_paid_cat_arr,$this->remove_catidlist_arr);
				if(count($matched_paid_cat_arr)>0)
				{
					$final_temp_paid_cat_arr = array_diff($this->temp_paid_cat_arr,$matched_paid_cat_arr);
				}
				else
				{
					$final_temp_paid_cat_arr = $this->temp_paid_cat_arr;
				}
			}
			else
			{
				$final_temp_paid_cat_arr = $this->temp_paid_cat_arr;
			}
		}
		if(count($final_temp_paid_cat_arr)>0)
		{
			$all_temp_paid_cat_arr = $this->getCategoryDetails($final_temp_paid_cat_arr);
			
			
			
			$catids_arr = array();
			$catnames_arr = array();
			$national_catids_arr = array();
			$i = 1;
			if(count($all_temp_paid_cat_arr)>0)
			{
				foreach($all_temp_paid_cat_arr as $catid => $catinfo_arr)
				{
					$catname 		= trim($catinfo_arr['catname']);
					$national_catid = trim($catinfo_arr['national_catid']);					
					$catids_arr[] 			= $catid;
					$catnames_arr[] 		= $catname;
					$national_catids_arr[] 	= $national_catid;
				}
				
			}
			$catnames_str = "|P|";
			$catnames_str .= implode("|P|",$catnames_arr);
			
			$catids_str = "|P|";
			$catids_str .= implode("|P|",$catids_arr);
			
			$national_catids_str = "|P|";
			$national_catids_str .= implode("|P|",$national_catids_arr);
			
			$catname_sel = str_ireplace("|P|","|~|",$catnames_str);
			 
			 
			$categories 	= $this->slasher($catnames_str);
			$catSelected 	= $this->slasher($catname_sel);
			 
			// Update tbl_business_temp_data
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_data = array();
				
				$bustemp_tbl 		= "tbl_business_temp_data";
				$bustemp_upt = array();
				$bustemp_upt['categories'] 				= $categories;
				$bustemp_upt['catIds'] 					= $catids_str;
				$bustemp_upt['nationalcatIds'] 			= $national_catids_str;
				$bustemp_upt['catSelected'] 			= $catSelected;
				$bustemp_upt['categories_list'] 		= '';
				$mongo_data[$bustemp_tbl]['updatedata'] = $bustemp_upt;
				
				$mongo_inputs['table_data'] 			= $mongo_data;
				$resUpdtBusinessTempData = $this->mongo_obj->updateData($mongo_inputs);

				/**/
				if(strtolower(trim($this->module)) == 'me' || strtolower(trim($this->module)) == 'jda')
				{
					$this->sendLogs($this->parentid,$mongo_data,$resUpdtBusinessTempData);
				}
				/**/
				
			}
			else{
				$sqlUpdtBusinessTempData = "UPDATE tbl_business_temp_data SET 
										categories 		= '".$categories."',
										catIds 			= '".$catids_str."',
										nationalcatIds 	= '".$national_catids_str."',
										catSelected 	= '".$catSelected."',
										categories_list	= ''
										WHERE contractid='".$this->parentid."'";
				$sqlUpdtBusinessTempData 	= $sqlUpdtBusinessTempData."/* TMEMONGOQRY */";
				$resUpdtBusinessTempData 	= parent::execQuery($sqlUpdtBusinessTempData, $this->conn_temp);
			}
			
			//Update tbl_national_listing_temp
			$TotalCategoryWeight = substr_count($national_catids_str, '|P|');
			
			$sqlUpdtNationalListing ="UPDATE tbl_national_listing_temp SET Category_nationalid = '".$national_catids_str."' , TotalCategoryWeight = '".$TotalCategoryWeight."' WHERE parentid='".$this->parentid."'";
			$resUpdtNationalListing   = parent::execQuery($sqlUpdtNationalListing, $this->conn_temp); 
			
			 
		}
		else
		{
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_data = array();
				
				$bustemp_tbl 		= "tbl_business_temp_data";
				$bustemp_upt = array();
				$bustemp_upt['categories'] 				= '';
				$bustemp_upt['catIds'] 					= '';
				$bustemp_upt['nationalcatIds'] 			= '';
				$bustemp_upt['catSelected'] 			= '';
				$bustemp_upt['categories_list'] 		= '';
				$mongo_data[$bustemp_tbl]['updatedata'] = $bustemp_upt;
				
				$mongo_inputs['table_data'] 			= $mongo_data;
				$resUpdtBusinessTempData = $this->mongo_obj->updateData($mongo_inputs);				
			}
			else
			{				
				$sqlUpdtBusinessTempData = "UPDATE tbl_business_temp_data SET categories ='', catIds='', nationalcatIds='', catSelected='', categories_list= '' WHERE contractid = '".$this->parentid."'";
				$sqlUpdtBusinessTempData = $sqlUpdtBusinessTempData."/* TMEMONGOQRY */";
				$resUpdtBusinessTempData = parent::execQuery($sqlUpdtBusinessTempData, $this->conn_temp);
			}
		}
		
		if(count($this->temp_nonpaid_cat_arr) >0)
		{
			if(count($this->remove_catidlist_arr)>0)	
			{
				$final_temp_nonpaid_cat_arr 	= 	array();
				$matched_nonpaid_cat_arr 		= 	array();				
				$matched_nonpaid_cat_arr 		= 	array_intersect($this->temp_nonpaid_cat_arr,$this->remove_catidlist_arr);
				
				if(count($matched_nonpaid_cat_arr)>0)
				{
					$final_temp_nonpaid_cat_arr = array_diff($this->temp_nonpaid_cat_arr,$matched_nonpaid_cat_arr);
				}
				else
				{
					$final_temp_nonpaid_cat_arr = $this->temp_nonpaid_cat_arr;
				}
			}
			else
			{
				$final_temp_nonpaid_cat_arr = $this->temp_nonpaid_cat_arr;
			}
		}
		if(count($final_temp_nonpaid_cat_arr)>0)
		{
			$all_temp_nonpaid_cat_arr = $this->getCategoryDetails($final_temp_nonpaid_cat_arr);
			
			$catids_nonpaid_arr = array();
			$national_catids_nonpaid_arr = array();
			$i = 1;
			if(count($all_temp_nonpaid_cat_arr)>0)
			{
				foreach($all_temp_nonpaid_cat_arr as $catid => $catinfo_arr)
				{
					$catname 		= trim($catinfo_arr['catname']);
					$national_catid = trim($catinfo_arr['national_catid']);
					
					$catids_nonpaid_arr[] 			= $catid;
					$national_catids_nonpaid_arr[] 	= $national_catid;
				}
			}
			
			$catidlineage_nonpaid 			= "/".implode('/,/',$catids_nonpaid_arr)."/";
			$national_catidlineage_nonpaid 	= "/".implode('/,/',$national_catids_nonpaid_arr)."/";
			
			$catlin_nonpaid_db = '';
			if($this->add_catlin_nonpaid_db == 1)
			{
				$catlin_nonpaid_db = 'db_iro.';
			}
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_data = array();
				
				$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
				$extrdet_upt = array();
				$extrdet_upt['catidlineage_nonpaid'] 			= $catidlineage_nonpaid;
				$extrdet_upt['national_catidlineage_nonpaid'] 	= $national_catidlineage_nonpaid;
				$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
				$mongo_inputs['table_data'] 			= $mongo_data;
				$resUpdateExtraShadow = $this->mongo_obj->updateData($mongo_inputs);
			}
			else
			{				
				$sqlUpdateExtraShadow = "UPDATE ".$catlin_nonpaid_db."tbl_companymaster_extradetails_shadow SET catidlineage_nonpaid = '".$catidlineage_nonpaid."', national_catidlineage_nonpaid = '".$national_catidlineage_nonpaid."' WHERE parentid = '".$this->parentid."'";
				$sqlUpdateExtraShadow = $sqlUpdateExtraShadow."/* TMEMONGOQRY */";
				$resUpdateExtraShadow 	= parent::execQuery($sqlUpdateExtraShadow, $this->conn_temp);
			}
		}
		else
		{
			$catlin_nonpaid_db = '';
			if($this->add_catlin_nonpaid_db == 1)
			{
				$catlin_nonpaid_db = 'db_iro.';
			}
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_data = array();
				
				$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
				$extrdet_upt = array();
				$extrdet_upt['catidlineage_nonpaid'] 			= '';
				$extrdet_upt['national_catidlineage_nonpaid'] 	= '';
				$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
				$mongo_inputs['table_data'] 			= $mongo_data;
				$resUpdateExtraShadow = $this->mongo_obj->updateData($mongo_inputs);
			}
			else
			{				
				$sqlUpdateExtraShadow = "UPDATE ".$catlin_nonpaid_db."tbl_companymaster_extradetails_shadow SET catidlineage_nonpaid = '', national_catidlineage_nonpaid = '' WHERE parentid = '".$this->parentid."'";
				$sqlUpdateExtraShadow = $sqlUpdateExtraShadow."/* TMEMONGOQRY */";
				$resUpdateExtraShadow   = parent::execQuery($sqlUpdateExtraShadow, $this->conn_temp);
			}
		}
		$result_msg_arr['error']['code'] = 0;
		$result_msg_arr['error']['message'] = "insert success";
		return $result_msg_arr;		
	}
	
	function sendLogs($parentid,$data,$response){
		
		/**/
		$post_data = array();
		$log_url = 'http://192.168.17.109/logs/logs.php';
		$post_data['ID']                = $parentid;
		$post_data['PUBLISH']           = 'ME';
		$post_data['ROUTE']             = 'MONGOUPDATE';
		$post_data['CRITICAL_FLAG'] 	= 1;
		$post_data['MESSAGE']       	= 'mongo update on shadow tables';
		$post_data['DATA']['url']       = 'multiparentage_class.php';
		$post_data['DATA_JSON']['paramssubmited'] = $data;
		$post_data['DATA_JSON']['response'] = $response;
		$post_data = http_build_query($post_data);
		/**/
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $log_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content  = curl_exec($ch);
		curl_close($ch);
		return $content;
	}
	
 	function fetch_contract_temp_categories()
	{
		GLOBAL $params;		
		$temp_category_arr = array();
		$catlin_nonpaid_db = '';
		if($this->add_catlin_nonpaid_db == 1)
		{
			$catlin_nonpaid_db = 'db_iro.';
		}
		
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['data_city'] 		= $this->data_city;
			$mongo_inputs['module']			= $this->module;
			$mongo_inputs['t1'] 			= "tbl_business_temp_data";
			$mongo_inputs['t2'] 			= "tbl_companymaster_extradetails_shadow";
			$mongo_inputs['t1_on'] 			= "contractid";
			$mongo_inputs['t2_on'] 			= "parentid";
			$mongo_inputs['t1_fld'] 		= "";
			$mongo_inputs['t2_fld'] 		= "catidlineage_nonpaid";
			$mongo_inputs['t1_mtch'] 		= json_encode(array("contractid"=>$this->parentid));
			$mongo_inputs['t2_mtch']		= "";
			$mongo_inputs['t1_alias'] 		= json_encode(array("catIds"=>"catidlineage"));
			$mongo_inputs['t2_alias'] 		= "";
			$mongo_join_data 	= $this->mongo_obj->joinTables($mongo_inputs);
			$row_temp_category 	= $mongo_join_data[0];
		}else{
			$sqlTempCategory	=	"SELECT catids as catidlineage,catidlineage_nonpaid FROM tbl_business_temp_data as A LEFT JOIN ".$catlin_nonpaid_db."tbl_companymaster_extradetails_shadow as B on A.contractid=B.parentid WHERE contractid = '" . $this->parentid . "'";
			$resTempCategory 	= parent::execQuery($sqlTempCategory, $this->conn_temp);
			$num_rows 			= parent::numRows($resTempCategory);
			if($num_rows > 0 ){
				$row_temp_category = parent::fetchData($resTempCategory);
			}
		}
		if(count($row_temp_category)>0)
		{
			
			if((isset($row_temp_category['catidlineage']) && $row_temp_category['catidlineage'] != '') || (isset($row_temp_category['catidlineage_nonpaid']) && $row_temp_category['catidlineage_nonpaid'] != ''))
			{
				$temp_catlin_arr 	= 	array();
				$temp_catlin_arr  	=   explode('|P|',$row_temp_category['catidlineage']);
				$temp_catlin_arr 	= 	array_filter($temp_catlin_arr);
				$temp_catlin_arr 	= 	$this->get_valid_categories($temp_catlin_arr);
				$temp_catlin_arr 	= 	array_diff($temp_catlin_arr,explode(",",$params['removed_catid']));
				
				$temp_catlin_np_arr = array();
				$temp_catlin_np_arr = explode("/,/",trim($row_temp_category['catidlineage_nonpaid'],"/"));
				$temp_catlin_np_arr = array_filter($temp_catlin_np_arr);
				$temp_catlin_np_arr 	= 	array_diff($temp_catlin_np_arr,explode(",",$params['removed_catid']));
				
				$total_catlin_arr = array();
				$total_catlin_arr =  array_merge($temp_catlin_arr,$temp_catlin_np_arr);
				$total_catlin_arr = array_merge(array_filter($total_catlin_arr));
				$temp_category_arr = $this->get_valid_categories($total_catlin_arr);
				
				$this->temp_paid_cat_arr = $temp_catlin_arr;
				$this->temp_nonpaid_cat_arr = $temp_catlin_np_arr;
			}
		}
 		return $temp_category_arr; 
	}
	function getCategoryDetails($catids_arr,$catnameflag = 0)
	{
		$catnameArr = array();
		$CatinfoArr = array();
		$catids_str = implode("','",$catids_arr);
		//$sqlCategoryDetails = "SELECT catid,category_name,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catids_str."')";
		//$resCategoryDetails 	= parent::execQuery($sqlCategoryDetails, $this->conn_catmaster);
		$cat_params = array();
		$cat_params['page'] 		='multiparentage_class';
		$cat_params['parentid'] 	= $this->parentid;
		$cat_params['data_city'] 	= $this->data_city;			
		$cat_params['return']		= 'catid,category_name,national_catid';

		$where_arr  	=	array();
		if(count($catids_arr)>0){
			$where_arr['catid']		= implode(",",$catids_arr);		
			$cat_params['where']	= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
		{
			foreach($cat_res_arr['results'] as $key=>$row_catdetails)
			{
				$catid 			= trim($row_catdetails['catid']);
				$category_name	= trim($row_catdetails['category_name']);
				$national_catid	= trim($row_catdetails['national_catid']);
				$CatinfoArr[$catid]['catname'] = $category_name;
				$CatinfoArr[$catid]['national_catid'] = $national_catid;
				$catnameArr[] = $category_name;
			}
		}
		if($catnameflag == 1){
			return $catnameArr;
		}else{
			return $CatinfoArr;
		}
	}
	function get_valid_categories($total_catlin_arr)
	{
		$final_catids_arr = array();
		if((!empty($total_catlin_arr)) && (count($total_catlin_arr) >0))
		{
			foreach($total_catlin_arr as $catid)
			{
				$final_catid = 0;
				$final_catid = preg_replace('/[^0-9]/', '', $catid);
				if(intval($final_catid)>0)
				{
					$final_catids_arr[]	= $final_catid;
				}
			}
			$final_catids_arr = array_filter($final_catids_arr);
			$final_catids_arr = array_unique($final_catids_arr);
			$final_catids_arr = array_merge($final_catids_arr);
		}
		return $final_catids_arr;	
	}
	function slasher($arr)
	{
		if(is_array($arr))
		{
			foreach($arr as $key=>$value)
			{
				$arr[$key] = addslashes(stripslashes($value));
			}
		}
		else
		{
			$arr = addslashes(stripslashes($arr));
		}
		return $arr;		
	}
	function getContractsDetails()
	{
		$contract_info_arr = array();
		$edit_flag 	= 0; // new	
		$live_category_arr = array();
		$sqlLiveCategory = "SELECT catidlineage,catidlineage_nonpaid FROM tbl_companymaster_extradetails WHERE parentid = '".$this->parentid."'";
		$resLiveCategory 	= parent::execQuery($sqlLiveCategory, $this->conn_iro);
		if($resLiveCategory && mysql_num_rows($resLiveCategory))
		{
			$edit_flag = 1;
			$row_live_category	=	mysql_fetch_assoc($resLiveCategory);
			if((isset($row_live_category['catidlineage']) && $row_live_category['catidlineage'] != '') || (isset($row_live_category['catidlineage_nonpaid']) && $row_live_category['catidlineage_nonpaid'] != ''))
			{
				$extra_catlin_arr 	= 	array();
				$extra_catlin_arr   =   explode("/,/",trim($row_live_category['catidlineage'],"/"));
				$extra_catlin_arr 	= 	array_filter($extra_catlin_arr);
				$extra_catlin_arr 	= $this->get_valid_categories($extra_catlin_arr);
				
				
				$extra_catlin_np_arr = array();
				$extra_catlin_np_arr = explode("/,/",trim($row_live_category['catidlineage_nonpaid'],"/"));
				$extra_catlin_np_arr = array_filter($extra_catlin_np_arr);
				$extra_catlin_np_arr = $this->get_valid_categories($extra_catlin_np_arr);
				
				$live_category_arr = array_merge($extra_catlin_arr,$extra_catlin_np_arr);
			}
		}
		if(count($live_category_arr)>0){
			$contract_info_arr['exist_category'] = implode(",",$live_category_arr);
		}else{
			$contract_info_arr['exist_category'] = '';
		}
		$contract_info_arr['edit_flag'] = $edit_flag;
		
		return $contract_info_arr;
	}	
	function getResetFlag()
	{
		$cat_reset_flag = 0;
		
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_temp_intermediate";
			$mongo_inputs['fields'] 	= "cat_reset_flag";
			$row_reset_flag = $this->mongo_obj->getData($mongo_inputs);
		}else{
			$sqlResetFlag = "SELECT cat_reset_flag FROM tbl_temp_intermediate WHERE parentid = '".$this->parentid."'";
			$resResetFlag  	= parent::execQuery($sqlResetFlag, $this->conn_temp);
			$row_reset_flag	= mysql_fetch_assoc($resResetFlag); 
		}
		//if($resResetFlag && mysql_num_rows($resResetFlag)>0)
		if(count($row_reset_flag)>0)
		{
			//$row_reset_flag	= mysql_fetch_assoc($resResetFlag); 
			$cat_reset_flag = intval($row_reset_flag['cat_reset_flag']);			
		}		
		return $cat_reset_flag;
	}
	function isExclusionTypeContract()
	{
		$exclusion_flag = 0;
		if(($this->module == 'DE') || ($this->module == 'CS')){
			$sqlExclusionTypeChk 	= "SELECT hiddenCon,dotcom FROM tbl_temp_intermediate WHERE parentid = '".$this->parentid."'";
			$resExclusionTypeChk  	= parent::execQuery($sqlExclusionTypeChk, $this->conn_temp);
			$row_exclusion_data 	= mysql_fetch_assoc($resExclusionTypeChk);
		}else{
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_temp_intermediate";
				$mongo_inputs['fields'] 	= "hiddenCon,dotcom";
				$row_exclusion_data 		= $this->mongo_obj->getData($mongo_inputs);
			}else{
				$sqlExclusionTypeChk 	= "SELECT hiddenCon FROM tbl_temp_intermediate WHERE parentid = '".$this->parentid."'";
				$resExclusionTypeChk  	= parent::execQuery($sqlExclusionTypeChk, $this->conn_temp);
				$row_exclusion_data 	= mysql_fetch_assoc($resExclusionTypeChk);
			}
		}
		//$resExclusionTypeChk  	= parent::execQuery($sqlExclusionTypeChk, $this->conn_temp);
		//if($resExclusionTypeChk && mysql_num_rows($resExclusionTypeChk)>0)
		if(count($row_exclusion_data)>0)
		{
			//$row_exclusion_data = mysql_fetch_assoc($resExclusionTypeChk);
			$hiddenCon 	= trim($row_exclusion_data['hiddenCon']);
			$dotcom 	= trim($row_exclusion_data['dotcom']);
			if(intval($hiddenCon) == 1){
				$exclusion_flag = 1;
			}else if(intval($dotcom) == 1){
				$exclusion_flag = 1;
			}
		}
		return $exclusion_flag;
	}
	function insertMultiparentageLog($popup_type)
	{
		$sqlInsertLog = "INSERT INTO d_jds.tbl_multiparentage_check_log 
						SET
						parentid 				= '".$this->parentid."',
						live_catids 			= '".$this->exist_category."',
						temp_catids 			= '".implode(",",$this->temp_catids_arr)."',
						remove_catids 			= '".implode(",",$this->remove_catidlist_arr)."',
						live_parentage_str 		= '".json_encode($this->liveParentageInfoArr)."',
						temp_parentage_str 		= '".json_encode($this->tempParentageInfoArr)."',
						cat_reset_flag 			= '".$this->reset_flag."',
						contract_existing_flag 	= '".$this->edit_flag."',
						popup_type 				= '".$popup_type."',
						ucode 					= '".$this->ucode."',
						module 					= '".$this->module."',
						insertdate 				= '".date("Y-m-d H:i:s")."'";
		$resInsertLog  	= parent::execQuery($sqlInsertLog, $this->conn_data);				
	}
	function contractByPassCheck($reasonid)
	{
		$bypasscheck_flag = 0;
		$sqlContractByPassChk = "SELECT parentid FROM tbl_contract_bypass_exclusion WHERE parentid = '".$this->parentid."' AND reasonid = '".$reasonid."'";
		$resContractByPassChk = parent::execQuery($sqlContractByPassChk, $this->conn_iro);
		if($resContractByPassChk && mysql_num_rows($resContractByPassChk)>0)
		{
			$bypasscheck_flag = 1;
		}
		return $bypasscheck_flag;
	}
	function allowMultiparentCatChk($catids_arr)
	{
		$allowmulticat_arr = array();
		$catids_str = implode("','",$catids_arr);
		//$sqlAllowMultiparentChk = "SELECT GROUP_CONCAT(catid) as catid FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catids_str."') AND category_type&2048=2048";
		//$resAllowMultiparentChk 	= parent::execQuery($sqlAllowMultiparentChk, $this->conn_catmaster);
		$cat_params = array();
		$cat_params['page'] 		= 'multiparentage_class';
		$cat_params['skip_log'] 	= '1';
		$cat_params['parentid'] 	= $this->parentid;
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'catid';

		$where_arr  	=	array();
		$where_arr['catid']				= implode(",",$catids_arr);
		$where_arr['category_type']		= "2048";	
		$cat_params['where']	= json_encode($where_arr);
		if(count($catids_arr)>0){
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0){
			//$row_allowmulti = mysql_fetch_assoc($resAllowMultiparentChk);
			foreach ($cat_res_arr['results'] as $key => $cat_arr) {
				$final_catid = trim($cat_arr['catid']);
				if($final_catid!=''){
					$catid_array[] = $final_catid;
				}
			}
			//$catid 			= trim($row_allowmulti['catid']);
			if(count($catid_array)>0){
				//$catid_array = explode(",",$catid);
				$catid_array = $this->get_valid_categories($catid_array);
				if(count($catid_array)>0){
					$allowmulticat_arr = $catid_array;
				}
			}
		}
		return $allowmulticat_arr;
	}
	private function send_die_message($msg)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['message'] = $msg;
		return $die_msg_arr;
	}		
}
?>
