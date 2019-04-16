<?php
class category_page_class extends DB
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
		$parentid 		= trim($params['parentid']);
		$module 		= trim($params['module']);
		$data_city 		= trim($params['data_city']);
		$ucode 			= trim($params['ucode']);
		
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
		$this->ucode  	  	= $ucode;
		$this->mongo_flag	= 0;
		$this->mongo_tme	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->setServers();
		$this->categoryClass_obj = new categoryClass();
			
		$this->add_catlin_nonpaid_db = 0;
		if(($this->module == 'DE') || ($this->module == 'CS'))
		{
			$this->add_catlin_nonpaid_db = 1;
		}
		
		$this->temp_paid_cat_arr 	= array();
		$this->temp_nonpaid_cat_arr = array();
		
		$this->contract_existing_temp_cat_arr = array();
		$new_category_arr = array(); 
		if((is_array($params['old_category'])) && (count($params['old_category'])>0)){
			$new_category_arr = array_merge($params['old_category'],$this->getContractTempCatInfo());
			$this->contract_existing_temp_cat_arr = $this->getValidCategories($new_category_arr);
		}else{ 
			$this->contract_existing_temp_cat_arr = $this->getContractTempCatInfo();
		}
		
		if(count($this->contract_existing_temp_cat_arr) <=0)
		{
			$message = "Category Not Found.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		$this->timingflg = 0;
		
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
	}
	function getContractTempCatInfo()
	{
		$catlin_nonpaid_db = '';
		if($this->add_catlin_nonpaid_db == 1)
		{
			$catlin_nonpaid_db = 'db_iro.';
		}
		$temp_category_arr = array();
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
			$sqlTempCategory	=	"SELECT catIds as catidlineage,catidlineage_nonpaid FROM tbl_business_temp_data as A LEFT JOIN ".$catlin_nonpaid_db."tbl_companymaster_extradetails_shadow as B on A.contractid=B.parentid WHERE contractid = '" . $this->parentid . "'";
			$resTempCategory 	= parent::execQuery($sqlTempCategory, $this->conn_temp);
			$num_rows 			= parent::numRows($resTempCategory);
			if($num_rows > 0){
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
				$temp_catlin_arr 	= 	$this->getValidCategories($temp_catlin_arr);
				
				$temp_catlin_np_arr = array();
				$temp_catlin_np_arr = explode("/,/",trim($row_temp_category['catidlineage_nonpaid'],"/"));
				$temp_catlin_np_arr = array_filter($temp_catlin_np_arr);
				$temp_catlin_np_arr = 	$this->getValidCategories($temp_catlin_np_arr);
				
				$total_catlin_arr = array();
				$total_catlin_arr =  array_merge($temp_catlin_arr,$temp_catlin_np_arr);
				$total_catlin_arr = array_merge(array_filter($total_catlin_arr));
				$temp_category_arr = $this->getValidCategories($total_catlin_arr);
				
				$this->temp_paid_cat_arr = $temp_catlin_arr;
				$this->temp_nonpaid_cat_arr = $temp_catlin_np_arr;
			}
		}
		return $temp_category_arr; 
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
			$final_catids_arr = array_merge($final_catids_arr);
		}
		return $final_catids_arr;	
	}
	function getCategoryDetails($catids_arr,$natl_cat_flg =0)
	{
		$CatinfoArr = array();
		$catids_str = implode(",",$catids_arr);
		//$sqlCategoryDetails = "SELECT catid,category_name,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catids_str."')";
		//$resCategoryDetails 	= parent::execQuery($sqlCategoryDetails, $this->conn_catmaster);
		$cat_params = array();
		$cat_params['page'] 		= 'category_page_class';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'catid,category_name,national_catid';

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
				if($natl_cat_flg == 1){
					$CatinfoArr[]	= intval($row_catdetails['national_catid']);
				}else{
					$catid 			= intval($row_catdetails['catid']);
					$category_name	= trim($row_catdetails['category_name']);
					$national_catid	= intval($row_catdetails['national_catid']);
					$CatinfoArr[$catid]['catname'] = $category_name;
					$CatinfoArr[$catid]['national_catid'] = $national_catid;
				}
			}
		}
		return $CatinfoArr;
	}
	function getContractCategoryDetails()
	{
		$paid_nonpaid_temp_catdetails_arr = $this->getCategoriesInfo();
		if(count($paid_nonpaid_temp_catdetails_arr) > 0)
		{
			$authcatcnt = 0;
			foreach($paid_nonpaid_temp_catdetails_arr as $temp_catid => $temp_catval_arr)
			{
				if($temp_catval_arr['cmnt'] == 'Authorised')
				{
					
					$special_char_arr_space = array(' - ',' -','- ',' ( ','( ',' (',' ) ',') ',' )');
					$special_char_arr  = array('-','-','-','(','(','(',')',')',')');
					$athcnm = preg_replace('/\s+/',' ', $temp_catval_arr['cnm']);
					$athcnm = addslashes(stripslashes(str_ireplace($special_char_arr_space,$special_char_arr,$athcnm)));
					
					if($temp_catval_arr['lnkct'] && !stristr($temp_catval_arr['rlcnm'],"authorised"))
					{
						$athcid = $temp_catval_arr['lnkct'];
					}
					else
					{
						$athcid = $temp_catid;
					}
					$paid_nonpaid_temp_catdetails_arr[$temp_catid]['athcid'] = $athcid;
					$paid_nonpaid_temp_catdetails_arr[$temp_catid]['athcnm'] = $athcnm;
				}
				if(!isset($temp_catval_arr['show']))
				{
					$paid_nonpaid_temp_catdetails_arr[$temp_catid]['show'] = 1; // adding show key in result
					if($temp_catval_arr['athchk'] ==1)
					{
						$authcatcnt++;
					}
				}
				if(!isset($temp_catval_arr['athchk']))
				{
					$paid_nonpaid_temp_catdetails_arr[$temp_catid]['athchk'] = 0; // adding athchk key in result
				}
				if(isset($temp_catval_arr['lnkct']))
				{
					unset($paid_nonpaid_temp_catdetails_arr[$temp_catid]['lnkct']);
				}
				if(isset($temp_catval_arr['rlcnm']))
				{
					unset($paid_nonpaid_temp_catdetails_arr[$temp_catid]['rlcnm']);
				}
			}
			if(($this->module == 'DE') || ($this->module == 'CS'))
			{
				$result_msg_arr['movie_data'] = $this->get_movie_details($this->parentid);	
			}	
			
			$result_msg_arr['data'] = $paid_nonpaid_temp_catdetails_arr;
			$result_msg_arr['authcatcnt'] = $authcatcnt;
			$result_msg_arr['timingflg'] = $this->timingflg;
			$result_msg_arr['authltrflg'] = $this->getTempAuthorisationFlag();
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
			return $result_msg_arr;
		}
		else
		{
			$message = "Category Not Found.";
			echo json_encode($this->sendDieMessage($message));
			die;
		}
		return $paid_nonpaid_temp_catdetails_arr;
		
		
	}
	
	function get_movie_details($parentid, $conn_iro)
	{
		$today = date('Y-m-d');
		
		$sqlCategoryDetails = "SELECT GROUP_CONCAT(TIME_FORMAT(movie_timings,'%I:%i %p') SEPARATOR ', ') AS timing,movie_date,index_mv,category_name, catid FROM db_iro.tbl_movie_timings WHERE parentid='".$parentid."' AND movie_date >= '".$today." 00:00:00' AND movie_date <= '".$today." 23:59:59' GROUP BY index_mv,movie_date,category_name";
		$resCategoryDetails 	= parent::execQuery($sqlCategoryDetails, $this->conn_iro);
		$count = parent::numRows($resCategoryDetails);
		$i = 0;
		if($resCategoryDetails && parent::numRows($resCategoryDetails)>0)
		{
			$array_fetch_res = array();
			
			while($row_catdetails = parent::fetchData($resCategoryDetails))
			{
				$array_fetch_res['data'][$i] = $row_catdetails;
				$i++;
			}
			$array_fetch_res['error'] = 0;
			$array_fetch_res['count']=$count;
		}
		else
		{
			$array_fetch_res['data'][$i] = "";
			$array_fetch_res['error'] = 1;
		}		
		
		return $array_fetch_res; 
	}

	function getCategoriesInfo()
	{
		$categories_arr = array();
		$national_catids_arr = array();
		if(count($this->contract_existing_temp_cat_arr)>0)
		{	
			$catids_list_qry = implode(",",$this->contract_existing_temp_cat_arr);
			$ignore_movie_natcatid_arr = array();
			if($this->module == 'DE' || $this->module == 'CS')
			{
				$ignore_movie_natcatid_arr = $this->ignoreMovieTimingCatInfo($catids_list_qry);
			}
			/*$sqlCategoryInfo =	"SELECT category_name as catname,catid,category_addon, 
								CASE business_flag
								WHEN 2 THEN 'B2C'
								WHEN 1 THEN 'B2B'
								WHEN 3 THEN 'B2B,B2C' END  AS imparent, if(category_type&16 = 16,1,0) as exclusive,total_results as totcompdisplay, CASE search_type WHEN 0 THEN 'L' WHEN 1 THEN 'A' WHEN 2 THEN 'Z' WHEN 3 THEN 'SZ' WHEN 4 THEN 'NM' WHEN 5 THEN 'VNM' END  AS searchtype, mask_status as mask, category_scope as distr, national_catid,CASE budget_type WHEN '' THEN 'Normal'  WHEN 0 THEN 'Normal' WHEN 1 THEN 'Low' WHEN 2 THEN 'High' END AS budgettype,display_product_flag,auth_flag,auth_gen_ncatid,category_description,city_count,callcount,reach_count  FROM tbl_categorymaster_generalinfo WHERE catid IN (".$catids_list_qry.") ORDER BY category_name";*/
			//$resCategoryInfo = parent::execQuery($sqlCategoryInfo, $this->conn_catmaster);
							
			$cat_params = array();
			$cat_params['page'] 		= 'category_page_class';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'category_name,catid,category_addon,business_flag,category_type,total_results,search_type,mask_status,category_scope,national_catid,budget_type,display_product_flag,auth_flag,auth_gen_ncatid,category_description,city_count,callcount,reach_count';
			$cat_params['orderby'] 		= 'category_name ASC';

			$where_arr  	=	array();
			if($catids_list_qry!=''){
				$where_arr['catid']			= $catids_list_qry;
				$cat_params['where']		= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}
			
			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
			{
				$authorized_cat_arr = array();
				foreach($cat_res_arr['results'] as $key =>$row_catinfo)
				{
					$budget_type	=	$row_catinfo['budget_type'];
					//CASE budget_type WHEN '' THEN 'Normal'  WHEN 0 THEN 'Normal' WHEN 1 THEN 'Low' WHEN 2 THEN 'High' END AS budgettype
					$budgettype ='';
					switch ($budget_type) {
						case '0':
							$budgettype = 'Normal';							
							break;
						case '1':
							$budgettype = 'Low';							
							break;
						case '2':
							$budgettype = 'High';							
							break;
					}
					if($budget_type==''){
						$budgettype = 'Normal';							
					}
					/*CASE business_flag
								WHEN 2 THEN 'B2C'
								WHEN 1 THEN 'B2B'
								WHEN 3 THEN 'B2B,B2C' END  AS imparent*/
					$business_flag = $row_catinfo['business_flag'];
					switch ($business_flag) {
						case '2':
							$imparent = 'B2C';
							break;
						case '1':
							$imparent ='B2B';
							break;
						case '3':
							$imparent ='B2B,B2C';
							break;
					}
					//CASE search_type WHEN 0 THEN 'L' WHEN 1 THEN 'A' WHEN 2 THEN 'Z' WHEN 3 THEN 'SZ' WHEN 4 THEN 'NM' WHEN 5 THEN 'VNM' END  AS searchtype
					$search_type = $row_catinfo['search_type'];
					switch ($search_type) {
						case '0':
							$searchtype = 'L';
							break;
						case '1':
							$searchtype ='A';
							break;
						case '2':
							$searchtype ='Z';
							break;
						case '3':
							$searchtype ='SZ';
							break;
						case '4':
							$searchtype ='NM';
							break;
						case '5':
							$searchtype ='VNM';
							break;
					}

					$category_type = $row_catinfo['category_type'];
					$exclusive = 0;
					if(((int)$category_type&16) ==16){
						$exclusive = 1;
					}

					// catname imparent  searchtype mask distr budgettype
					$catid 			= intval($row_catinfo['catid']);
					$catname 		= trim($row_catinfo['category_name']);
					$national_catid = intval($row_catinfo['national_catid']);
					$budgettype 	= $budgettype;
					$totcompdisplay = trim($row_catinfo['totcompdisplay']);
					$exclusive 		= $exclusive;
					$distr			= trim($row_catinfo['category_scope']);
					$mask			= intval($row_catinfo['mask_status']);
					$imparent		= $imparent;
					$searchtype		= $searchtype;
					$category_addon	= trim($row_catinfo['category_addon']);
					$city_count	    = trim($row_catinfo['city_count']);
					$callcount	    = trim($row_catinfo['callcount']);
					$reach_count	= number_format((float)$row_catinfo['reach_count'], 2, '.', '');
					$display_product_flag = trim($row_catinfo['display_product_flag']);
					$category_description = trim($row_catinfo['category_description']);
					$auth_flag			= intval($row_catinfo['auth_flag']);
					$auth_gen_ncatid 	= intval($row_catinfo['auth_gen_ncatid']);
					$movie_flag     = 0;

					
					if(((int)$display_product_flag&1073741824)==1073741824) {
						$movie_flag = 1;
					}
					$this->timingflg = $movie_flag;
					if(in_array($catid,$this->temp_nonpaid_cat_arr)){
						$categories_arr[$catid]['paid'] = 'N'; // Paid Status
					}else{
						$categories_arr[$catid]['paid'] = 'Y'; // Paid Status
					}
					$categories_arr[$catid]['cnm'] = $catname;
					
					
					if($movie_flag==1)
					{						
						if($this->module == 'DE' || $this->module == 'CS')
						{
							if(in_array($national_catid,$ignore_movie_natcatid_arr))
							{
								$categories_arr[$catid]['cmnt'] = 'Hide Timings'; // Comment
							}
							else
							{
								$categories_arr[$catid]['cmnt'] = 'Add Timings'; // Comment
							}
						}
						else
						{
							$categories_arr[$catid]['cmnt'] = ''; // Comment
						}
					}
					elseif($auth_gen_ncatid > 0) // whether authorised
					{
						if($auth_flag == 1){
							$auth_mask_status = 0; // don't do anything as authorised category already exists 
						}else{
							$auth_mask_status = $this->getCatMaskStatus($catid,$auth_gen_ncatid);
						}
						if($auth_mask_status == 1){
							$categories_arr[$catid]['cmnt'] = '';
						}else{
							$categories_arr[$catid]['cmnt'] = 'Authorised';
						}
					}
					else
					{
						$categories_arr[$catid]['cmnt'] = ''; // Comment
					}
					
					$categories_arr[$catid]['msk'] 	= $mask; // Mask
					$categories_arr[$catid]['ccnt'] = $callcount; // Call Count
					$categories_arr[$catid]['rcnt'] = $reach_count; // Reach Count
					
					
					if(((int)$category_addon & 2) == 2)
						$categories_arr[$catid]['grnt'] = 1; // Guarantee Flag
					else
						$categories_arr[$catid]['grnt'] = 0; // Guarantee Flag
					 
					$categories_arr[$catid]['bdgtp'] = $budgettype; // Budget Type

					if($totcompdisplay)
						$categories_arr[$catid]['tcomp'] = $totcompdisplay; // Total Company Display
					else
						$categories_arr[$catid]['tcomp'] = 7; // Total Company Display

					if(strstr($imparent, 'B2B'))
						$categories_arr[$catid]['b2b'] = 1; // Business To Business Category
					else 
						$categories_arr[$catid]['b2b'] = 0; // Business To Business Category

					if(($exclusive == '1') || (($totcompdisplay == '1')))
						$categories_arr[$catid]['exlcv'] = 1; // Exclusive
					else 
						$categories_arr[$catid]['exlcv'] = 0; // Exclusive
						
					if(($distr == 1 || $distr == 2) && $city_count == '9')
						$categories_arr[$catid]['distr'] = 1; // National / State Lisiting Identifier
					else 
						$categories_arr[$catid]['distr'] = 0; // National / State Lisiting Identifier
					
					
					if(strtoupper($searchtype) == 'A' || strtoupper($searchtype) == 'NM' || strtoupper($searchtype) == 'VNM')
					{
						$categories_arr[$catid]['type'] = 'Area';
					}
					elseif(strtoupper($searchtype) == 'Z')
					{
						$categories_arr[$catid]['type'] = 'Zonal';  
					}
					elseif(strtoupper($searchtype) == 'SZ')
					{
						$categories_arr[$catid]['type'] = 'Superzone';  
					}
					else
					{
						$categories_arr[$catid]['type'] = 'All Area';  
					}
					
					if(($category_description !=null) && (!empty($category_description))){
						$categories_arr[$catid]['narr'] = $category_description;
					}else{
						$categories_arr[$catid]['narr'] = ''; 
					}
					
					/*--- Authorized Category Related Handling Starts----*/
					$categories_arr[$catid]['rlcnm'] = $catname;
					$catnm_new = '';
					if($auth_flag == 1)
					{
						$categories_arr[$catid]['athchk'] = 1; 
						$catnm_new = str_ireplace("(authorised)","",$catname);
						$catnm_new = str_ireplace("authorised","",$catnm_new);
						$catnm_new = str_ireplace("(authorized)","",$catnm_new);
						$catnm_new = str_ireplace("authorized","",$catnm_new);
						$categories_arr[$catid]['cnm'] = trim($catnm_new);
					}
					if($catnm_new)
					{
						$authorized_cat_arr[$catid] = $auth_gen_ncatid;
						$k = array_search($auth_gen_ncatid,$authorized_cat_arr);
					}
					else
					{
						$authorized_cat_arr[$catid] = $national_catid;
						$k = array_search($national_catid,$authorized_cat_arr);
					}					
					if($k != '' && $k != $catid) //found
					{
						$categories_arr[$catid]['show'] 	= 0; //dont show
						$categories_arr[$k]['athchk'] 		= 1; 
						$categories_arr[$catid]['athchk'] 	= 1; 
						$categories_arr[$k]['lnkct'] 		= $catid;
						$categories_arr[$catid]['lnkct'] 	= $k;
					}
					/*--- Authorized Category Related Handling Ends----*/
					
					
					
				}
			}
		}
		return $categories_arr;
	}
	function getCatMaskStatus($catid,$auth_gen_ncatid)
	{
		$msk_st = 0;
		//$sqlCatMaskStatus = "SELECT catid FROM tbl_categorymaster_generalinfo WHERE auth_gen_ncatid = '".$auth_gen_ncatid."' AND auth_flag = 1 AND mask_status = 1";
		//$resCatMaskStatus 	= parent::execQuery($sqlCatMaskStatus, $this->conn_catmaster);
		$cat_params = array();
		$cat_params['page'] 		= 'category_page_class';
		$cat_params['skip_log'] 		= '1';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'catid';

		$where_arr  	=	array();
		if($auth_gen_ncatid!=''){
			$where_arr['auth_gen_ncatid']	= $auth_gen_ncatid;
			$where_arr['auth_flag']			= '1';
			$where_arr['mask_status']		= '1';
			$cat_params['where']			= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}
		if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
		{
			//$row_gen_catid 	= parent::fetchData($resCatMaskStatus);

			$gen_catidval 	= $cat_res_arr['results']['0']['catid'];
			if(($catid >0) && ($catid != $gen_catidval) && (!in_array($gen_catidval,$this->temp_paid_cat_arr) && !in_array($gen_catidval,$this->temp_nonpaid_cat_arr)))
			{
				$msk_st = 1;
			}
		}
		return $msk_st;
	}
	function getTempAuthorisationFlag()
	{
		$authorisation_flag = '-1';
		$sqlAuthorisationFlag 	= "SELECT authorisation_flag FROM tbl_temp_flow_status WHERE parentid = '".$this->parentid."'";
		$resAuthorisationFlag 	= parent::execQuery($sqlAuthorisationFlag, $this->conn_temp);
		if($resAuthorisationFlag && parent::numRows($resAuthorisationFlag)>0)
		{
			$row_authorisation 	= parent::fetchData($resAuthorisationFlag);
			$authorisation_flag = $row_authorisation['authorisation_flag'];
		}
		return $authorisation_flag;
	}
	function ignoreMovieTimingCatInfo($catids_list)
	{
		$ignoreMovieCatArr = array();
		//$sqlMovieTaggedCategies = "SELECT national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN (".$catids_list.") AND display_product_flag&1073741824=1073741824";
		//$resMovieTaggedCategies 	= parent::execQuery($sqlMovieTaggedCategies, $this->conn_catmaster);
		$cat_params = array();
		$cat_params['page'] 		= 'category_page_class';
		$cat_params['skip_log'] 	= '1';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'national_catid';

		$where_arr  	=	array();
		if($catids_list!=''){
			$where_arr['catid']					= $catids_list;
			$where_arr['display_product_flag']	= '1073741824';			
			$cat_params['where']				= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}
		if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
		{
			$natcatids_arr = array();
			foreach($cat_res_arr['results'] as $key =>$row_movie_cat)
			{
				$natcatids_arr[] = trim($row_movie_cat['national_catid']);
			}
			if(count($natcatids_arr)>0)
			{
				$natcatids_list = "'".implode("','",$natcatids_arr)."'";
				$sqlIgnoreMovieCategories = "SELECT national_catid FROM tbl_ignore_movie_tagged_categories WHERE national_catid IN (".$natcatids_list.")";
				$resIgnoreMovieCategories = parent::execQuery($sqlIgnoreMovieCategories, $this->conn_local);
				if($resIgnoreMovieCategories && parent::numRows($resIgnoreMovieCategories)>0)
				{
					while($row_ignore_cat = parent::fetchData($resIgnoreMovieCategories))
					{
						$ignoreMovieCatArr[] = trim($row_ignore_cat['national_catid']);
					}
				}
			}
		}
		return $ignoreMovieCatArr;
	}
	private function sendDieMessage($msg)
	{
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
	function arrayProcess($requestedArr)
	{
		$processedArr = array();
		if(count($requestedArr)>0){
			$processedArr = array_merge(array_unique(array_filter($requestedArr)));
		}
		return $processedArr;
	}
	function slasher($arr)
	{
		if(is_array($arr)){
			foreach($arr as $key=>$value){
				$arr[$key] = addslashes(stripslashes($value));
			}
		}else{
			$arr = addslashes(stripslashes($arr));
		}
		return $arr;		
	}
}
?>
