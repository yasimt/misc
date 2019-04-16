<?php
class contract_category_class extends DB
{
	var  $conn_iro    	= null;
	var  $conn_jds   	= null;
	var  $conn_tme 	= null;
	var  $conn_fnc    	= null;
	var  $conn_idc    	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	
	
	function __construct($params)
	{	
		
		$parentid 	= trim($params['parentid']);
		$module 	= trim($params['module']);
		$data_city 	= trim($params['data_city']);
		$jdrr_live 	= trim($params['jdrr_live']);
		$ucode 		= trim($params['ucode']);
		$bypass 		= trim($params['bypass']);
		
		
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
		$this->jdrr_live 	= $jdrr_live;
		$this->bypass 		= $bypass;
		$this->module  	  	= strtoupper($module);
		$this->ucode 		= $ucode;
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->setServers();
		$this->categoryClass_obj = new categoryClass();
		$this->add_catlin_nonpaid_db = 0;
		if(($this->module == 'DE') || ($this->module == 'CS'))
		{
			$this->add_catlin_nonpaid_db = 1;
		}
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->conn_tme  		= $db[$conn_city]['tme_jds']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];
		
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
			echo json_encode($this->sendDieMessage($message));
			die();
		}	
		//~ if($this->bypass == 1)	
			//~ $this->mongo_flag = 1;
	}
	function contractCategoryInfo()
	{
		$catlin_nonpaid_db = '';
		if($this->add_catlin_nonpaid_db == 1)
		{
			$catlin_nonpaid_db = 'db_iro.';
		}
		$contract_catlin_arr = array();
		$edit_flag = 0;
		$result_found_flag = 0;
		$sqlLiveCategory = "SELECT catidlineage,catidlineage_nonpaid FROM tbl_companymaster_extradetails WHERE parentid = '".$this->parentid."'";
		$resLiveCategory 	= parent::execQuery($sqlLiveCategory, $this->conn_iro);
		$extra_catlin_arr 	= 	array();
		$extra_catlin_np_arr = array();
		if($resLiveCategory && parent::numRows($resLiveCategory))
		{
			$edit_flag = 1;
			$row_live_category	=	parent::fetchData($resLiveCategory);
			if((isset($row_live_category['catidlineage']) && $row_live_category['catidlineage'] != '') || (isset($row_live_category['catidlineage_nonpaid']) && $row_live_category['catidlineage_nonpaid'] != ''))
			{
				$extra_catlin_arr   =   explode("/,/",trim($row_live_category['catidlineage'],"/"));
				$extra_catlin_arr 	= 	array_filter($extra_catlin_arr);
				$extra_catlin_arr 	= $this->getValidCategories($extra_catlin_arr);
				if(count($extra_catlin_arr) >0)
				{
					$result_found_flag = 1;
					$contract_catlin_arr['LIVE']['PAID'] = $this->getCategoryDetails($extra_catlin_arr);
				}
				$extra_catlin_np_arr = explode("/,/",trim($row_live_category['catidlineage_nonpaid'],"/"));
				$extra_catlin_np_arr = array_filter($extra_catlin_np_arr);
				$extra_catlin_np_arr = $this->getValidCategories($extra_catlin_np_arr);
				
				if(count($extra_catlin_np_arr) >0)
				{
					$result_found_flag = 1;
					$contract_catlin_arr['LIVE']['NONPAID'] = $this->getCategoryDetails($extra_catlin_np_arr);
				}
			}
		}
		$jdrr_restrict = 0;
		$jdrr_catinfo  ='';
		if($this->jdrr_live == 1){
			$live_all_catids_arr = array();
			$live_all_catids_arr = array_merge($extra_catlin_arr,$extra_catlin_np_arr);
			$live_all_catids_arr = $this->getValidCategories($live_all_catids_arr);
			if(count($live_all_catids_arr)>0){
				$catadd_info_arr = $this->catAdditionalInfo($live_all_catids_arr);
				if($catadd_info_arr['jdrrrest'] == 1){
					$jdrr_restrict = 1;
					$jdrr_catinfo = $catadd_info_arr['cat_info'];
				}
			}
			$result_msg_arr['jdrrrest'] 	= $jdrr_restrict;
			$result_msg_arr['jdrr_catinfo']	=	$jdrr_catinfo;
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
			return $result_msg_arr;
		}
		
		$cinemahallflg = 0;
		$cattype_found = 0;
		$cattype_info_arr = array();
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
			$paid_temp_catfound = 0;
			$nonbiddablecatflag = 0;
			$contract_catlin_arr['TEMP']['BLOCK'] = '';
			if((isset($row_temp_category['catidlineage']) && $row_temp_category['catidlineage'] != '') || (isset($row_temp_category['catidlineage_nonpaid']) && $row_temp_category['catidlineage_nonpaid'] != ''))
			{
				$temp_catlin_arr 	= 	array();
				$temp_catlin_arr   =   explode('|P|',$row_temp_category['catidlineage']);
				$temp_catlin_arr 	= 	array_filter($temp_catlin_arr);
				$temp_catlin_arr 	= $this->getValidCategories($temp_catlin_arr);
				if(count($temp_catlin_arr) >0)
				{
					$result_found_flag = 1;
					$paid_temp_catfound = 1;
					$contract_catlin_arr['TEMP']['PAID'] = $this->getCategoryDetails($temp_catlin_arr);
				}
				
				$temp_catlin_np_arr = array();
				$jdrr_catinfo 	=	'';
				$temp_catlin_np_arr = explode("/,/",trim($row_temp_category['catidlineage_nonpaid'],"/"));
				$temp_catlin_np_arr = array_filter($temp_catlin_np_arr);
				$temp_catlin_np_arr = $this->getValidCategories($temp_catlin_np_arr);
				
				if(count($temp_catlin_np_arr) >0)
				{
					$result_found_flag = 1;
					$contract_catlin_arr['TEMP']['NONPAID'] = $this->getCategoryDetails($temp_catlin_np_arr);
				}
				if(count($temp_catlin_arr)>0)
				{
					$nonbiddablecatarr = $this->getNonbiddableCategory($temp_catlin_arr);
					if(count($nonbiddablecatarr)>0)
					{
						$row_biddablecat = 1;
						$contract_catlin_arr['TEMP']['BLOCK'] = implode(",",$nonbiddablecatarr);
					}
				}
				$all_temp_catids_arr = array();
				$all_temp_catids_arr = array_merge($temp_catlin_arr,$temp_catlin_np_arr);
				$all_temp_catids_arr = $this->getValidCategories($all_temp_catids_arr);
				if(count($all_temp_catids_arr)>0)
				{
					$cattypeinfo_arr = $this->getCategoryTypeInfo($all_temp_catids_arr);
					if(isset($cattypeinfo_arr['cattype']) && isset($cattypeinfo_arr['template_id']))
					{
						$cattype_found = 1;
						$cattype_info_arr['cattype'] = $cattypeinfo_arr['cattype'];
						$cattype_info_arr['template_id'] = $cattypeinfo_arr['template_id'];
					}
					$cinemahallflg = $this->cinemaHallCatIfo($all_temp_catids_arr);
					$catadd_info_arr = $this->catAdditionalInfo($all_temp_catids_arr);
					if($catadd_info_arr['jdrrrest'] == 1){
						$jdrr_restrict = 1;
						$jdrr_catinfo = $catadd_info_arr['cat_info'];
					}
				}
			}
			if($paid_temp_catfound !=1)
			{
				$contract_catlin_arr['tmperror']['code'] = 1; 
				$contract_catlin_arr['tmperror']['msg'] = "Paid Category Not Found"; 
			}
			else if($row_biddablecat == 1)
			{
				$contract_catlin_arr['tmperror']['code'] = 2; 
				$contract_catlin_arr['tmperror']['msg'] = "Block Categor Exists"; 
			}
			else
			{
				$contract_catlin_arr['tmperror']['code'] = 0; 
				$contract_catlin_arr['tmperror']['msg'] = "Success"; 
			}
		}
		else
		{
			$contract_catlin_arr['TEMP']['BLOCK'] = '';
			$contract_catlin_arr['tmperror']['code'] = 1; 
			$contract_catlin_arr['tmperror']['msg'] = "Paid Category Not Found"; 
		}
		if($result_found_flag == 1)
		{
			if($cattype_found !=1)
			{
				$cattype_info_arr['cattype'] = '';
				$cattype_info_arr['template_id'] = '';
			}
			
			$result_msg_arr['data'] 		= $contract_catlin_arr;
			$result_msg_arr['info'] 		= $cattype_info_arr;
			$result_msg_arr['cnmhal'] 		= $cinemahallflg;
			$result_msg_arr['edit_flag'] 	= $edit_flag;
			$result_msg_arr['jdrrrest'] 	= $jdrr_restrict;
			$result_msg_arr['jdrr_catinfo'] = $jdrr_catinfo;
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
			return $result_msg_arr;
		}
		else
		{
			$die_msg_arr['data'] = $contract_catlin_arr;
			$die_msg_arr['error']['code'] = 1;
			$die_msg_arr['error']['msg'] = "Category Not Found.";
			return $die_msg_arr;
		}
	}
	function getValidCategories($total_catlin_arr)
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
		}
		return $final_catids_arr;	
	}
	function getCategoryDetails($catids_arr)
	{
		$CatinfoArr = array();
		$catids_str = implode(",",$catids_arr);
		//$sqlCategoryDetails = "SELECT catid,category_name FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catids_str."')";
		//$resCategoryDetails 	= parent::execQuery($sqlCategoryDetails, $this->conn_catmaster);
		$cat_params = array();
		$cat_params['page'] 		= 'contract_category_class';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'catid,category_name';

		$where_arr  	=	array();
		if($catids_str!=''){
			$where_arr['catid']			= $catids_str;
			$cat_params['where']		= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
		{
			foreach($cat_res_arr['results'] as $key => $row_catdetails)
			{
				$catid 			= trim($row_catdetails['catid']);
				$category_name	= trim($row_catdetails['category_name']);
				$CatinfoArr[$catid] = $category_name;
			}
		}
		return $CatinfoArr;
	}
	function getCategoryTypeInfo($catids_arr)
	{
		$CatinfoArr = array();
		$product_type_cat = 0;
		$template_id_arr = array();
		$catids_str = implode(",",$catids_arr);
		//$sqlCategoryDetails = "SELECT catid,category_name,template_id FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catids_str."') AND template_id>0";
		//$resCategoryDetails 	= parent::execQuery($sqlCategoryDetails, $this->conn_catmaster);
		$cat_params = array();
		$cat_params['page'] 		= 'contract_category_class';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'catid,category_name,template_id';

		$where_arr  	=	array();
		if($catids_str!=''){
			$where_arr['catid']			= $catids_str;
			$cat_params['where']		= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
		{
			foreach($cat_res_arr['results'] as $key =>$row_catdetails)
			{
				$catid 			= trim($row_catdetails['catid']);
				$category_name	= trim($row_catdetails['category_name']);
				$template_id	= trim($row_catdetails['template_id']);
				if(intval($template_id) == 13){
					$product_type_cat = 1;
				}
				$template_id_arr[] = $template_id;
			}
			$template_id_arr = array_unique($template_id_arr);
			if($product_type_cat == 1){
				$CatinfoArr['cattype'] = 'Product';
			}else{
				$CatinfoArr['cattype'] = 'Service';
			}
			$CatinfoArr['template_id'] = implode(",",$template_id_arr);
		}
		
		return $CatinfoArr;
	}
	function getNonbiddableCategory($catids_arr)
	{
		$nonbiddablecatarr = array();
		$catids_str = implode(",",$catids_arr);
		//$sqlNonbiddableCategory = "SELECT catid,category_name FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catids_str."') AND biddable_type = 0";
		//$resNonbiddableCategory = parent::execQuery($sqlNonbiddableCategory, $this->conn_local);
		$cat_params = array();
		$cat_params['page'] 		= 'contract_category_class';
		$cat_params['skip_log'] 	= '1';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'catid,category_name';

		$where_arr  	=	array();
		if($catids_str!=''){
			$where_arr['catid']				= $catids_str;
			$where_arr['biddable_type']		= '0';
			$cat_params['where']		= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
		{
			foreach($cat_res_arr['results'] as $key =>$row_biddablecat)
			{
				$catid 			= trim($row_biddablecat['catid']);
				$category_name	= trim($row_biddablecat['category_name']);
				$nonbiddablecatarr[] = $category_name;
			}
		}
		return $nonbiddablecatarr;
	}
	function cinemaHallCatIfo($catids_arr)
	{
		$cinemahall_flag = 0;
		$catids_str = implode(",",$catids_arr);
		$cinemahall_natcatid_arr = array();
		//$sqlCinemaHallCatinfo = "SELECT group_concat(distinct national_catid separator '|~|') as cinemahall_natcatid FROM tbl_categorymaster_generalinfo  WHERE category_name  in ('Cinema Halls','Multiplex Cinema Halls','Miniplex Cinema Halls','4D Cinema Halls','5D Cinema Halls','6D Cinema Halls')";
		//$resCinemaHallCatinfo = parent::execQuery($sqlCinemaHallCatinfo, $this->conn_local);
		$cat_params = array();
		$cat_params['page'] 		= 'contract_category_class';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'national_catid';

		$where_arr  	=	array();		
		$where_arr['category_name']	= 'Cinema Halls,Multiplex Cinema Halls,Miniplex Cinema Halls,4D Cinema Halls,5D Cinema Halls,6D Cinema Halls';
		$cat_params['where']		= json_encode($where_arr);
		$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
		{
			//$row_cinemahall_cat = parent::fetchData($resCinemaHallCatinfo);
			$cinemahall_natcatid_arr  =array();
			foreach ($cat_res_arr['results'] as $key => $cat_arr) {
				$cinemahall_natcatid = $cat_arr['national_catid'];
				if($cinemahall_natcatid!=''){
					$cinemahall_natcatid_arr[] = $cinemahall_natcatid;
				}
			}

			//$cinemahall_natcatid = trim($row_cinemahall_cat['cinemahall_natcatid']);
			//$cinemahall_natcatid_arr = explode("|~|",$cinemahall_natcatid);
			if(count($cinemahall_natcatid_arr)>0){
				$nat_catids_str = implode(",",$cinemahall_natcatid_arr);
				//$sqlCinemaHallFlag = "SELECT catid FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catids_str."') AND national_catid IN ('".$nat_catids_str."') LIMIT 1";
				//$resCinemaHallFlag = parent::execQuery($sqlCinemaHallFlag, $this->conn_local);
				$cat_params = array();
				$cat_params['page'] 		= 'contract_category_class';
				$cat_params['skip_log'] 	= '1';
				$cat_params['data_city'] 	= $this->data_city;
				$cat_params['return']		= 'catid';
				$cat_params['limit']		= '1';

				$where_arr  	=	array();
				if($catids_str!=''){
					$where_arr['catid']				= $catids_str;
					$where_arr['national_catid']	= $nat_catids_str;
					$cat_params['where']		= json_encode($where_arr);
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
				}
				$cat_res_arr = array();
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}
				if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){
					$cinemahall_flag = 1;
				}
			}
		}
		return $cinemahall_flag;
	}
	function catAdditionalInfo($catids_arr)
	{
		$resultArr = array();
		$catids_str = implode(",",$catids_arr);
		//$sqlCatAdditionalInfo = "SELECT GROUP_CONCAT(catid,'|~|',category_name SEPARATOR '|*|') AS cat_info, count(1) as cnt FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catids_str."') AND misc_cat_flag&64=64 HAVING cnt>0 LIMIT 1";
		//$resCatAdditionalInfo = parent::execQuery($sqlCatAdditionalInfo, $this->conn_local);
		$cat_params = array();
		$cat_params['page'] 		= 'contract_category_class';
		$cat_params['skip_log'] 	= '1';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'catid,category_name';

		$where_arr  	=	array();
		if($catids_str!=''){
			$where_arr['catid']				= $catids_str;
			$where_arr['misc_cat_flag']		= '64';
			$cat_params['where']			= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}
		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){
			//$rowCatInfo	=	parent::fetchData($resCatAdditionalInfo);
			//302|~|Travel Agents|*|305|~|Car Hire|*|4485|~|ENT Doctors
			$cat_info_arr = array();
			foreach ($cat_res_arr['results'] as $key => $cat_arr) {
				$cat_info_arr[] = $cat_arr['catid']."|~|".$cat_arr['category_name'];
			}
			$cat_info_str	=	implode("|*|", $cat_info_arr);
			if(($cat_info_str !=null) && !empty($cat_info_str))
			{
				$resultArr['jdrrrest'] = 1;
				$resultArr['cat_info']		=	$cat_info_str;
			}
		}
		return $resultArr;
	}
	private function sendDieMessage($msg)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
}
?>
