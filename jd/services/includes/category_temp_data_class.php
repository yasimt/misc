<?php
class category_temp_data_class extends DB
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
		$this->module 	= trim($params['module']);
		$data_city 	= trim($params['data_city']);
		$catlist 	= trim($params['catlist']);
		$this->existlist  = trim($params['existlist']);
		$this->catlist 	= trim($params['catlist']);
		$this->ucode 	= trim($params['ucode']);
		$this->nocategory = trim($params['nocategory']);
		$this->checkomit  = trim($params['checkomit']);
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
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= $module;
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
		
		$this->temp_paid_cat_arr 	= array();
		$this->temp_nonpaid_cat_arr = array();
		$contract_existing_temp_cat_arr = $this->fetch_contract_temp_categories();
		$requested_category_arr = array();
		if($catlist)
		{
			$catlist_arr = explode("|P|",$catlist);
			if(count($catlist_arr)>0)
			{
				foreach($catlist_arr as $request_catid)
				{
					if(intval($request_catid)>0)
					{
						$requested_category_arr[] = $request_catid;
					}
				}
			}
		}
		if(count($requested_category_arr)>0)
		{
			$requested_category_arr = array_unique($requested_category_arr);
		}
		$contract_current_all_cat_arr = array_merge($contract_existing_temp_cat_arr,$requested_category_arr);
		if(count($contract_current_all_cat_arr) <=0 && !$this->checkomit)
		{
			$message = "Category Not Found.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		$this->requested_category_arr = $requested_category_arr;
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
			echo json_encode($this->send_die_message($message));
			die();
		}	
	}
	function fetch_contract_temp_categories()
	{
		$catlin_nonpaid_db = '';
		//if($this->existlist!='' && strtoupper($this->module)!='CS' && strtoupper($this->module)!='TME'){
		if($this->existlist!='' && strtoupper($this->module)!='CS'){
			
				$existlist=explode('|P|', $this->existlist);
				$cat_arr=explode('|P|', $this->catlist);
				
				$catids_from_temp='';
				if($this->mongo_flag == 1 || $this->mongo_tme == 1){
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->parentid;
					$mongo_inputs['data_city'] 	= $this->data_city;
					$mongo_inputs['module']		= $this->module;
					$mongo_inputs['table'] 		= "tbl_business_temp_data";
					$mongo_inputs['fields'] 	= "catIds";
					$data_res = $this->mongo_obj->getData($mongo_inputs);
					$catids_from_temp = $data_res['catIds'];
				}else{				
					$getex="select catIds from tbl_business_temp_data WHERE contractid='".$this->parentid."'";
					$res_ex   = parent::execQuery($getex, $this->conn_temp);
					if($res_ex && mysql_num_rows($res_ex)>0){
						$row_ex = mysql_fetch_assoc($res_ex);
						 $catids_from_temp=$row_ex['catIds'];
					}
				}
				
				// deleting from main
				$existlist = array_diff($existlist, $cat_arr); 
				$catids_from_temp=explode("|P|", $catids_from_temp);
				
				$catids_from_temp=array_diff($catids_from_temp, $existlist);
				$catids_from_temp=array_filter($catids_from_temp);
				$catids_from_temp=implode('|P|', $catids_from_temp);
				
				// deleting from nonpaid
				$catids_from_temp_np='';
				if($this->mongo_flag == 1 || $this->mongo_tme == 1){
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->parentid;
					$mongo_inputs['data_city'] 	= $this->data_city;
					$mongo_inputs['module']		= $this->module;
					$mongo_inputs['table'] 		= "tbl_companymaster_extradetails_shadow";
					$mongo_inputs['fields'] 	= "catidlineage_nonpaid";
					$data_res = $this->mongo_obj->getData($mongo_inputs);
					$catids_from_temp_np = $data_res['catidlineage_nonpaid'];
				}else{
					$getexnp="select catidlineage_nonpaid from tbl_companymaster_extradetails_shadow WHERE parentid='".$this->parentid."'";
					$res_exnp   = parent::execQuery($getexnp, $this->conn_temp);
					if($res_exnp && mysql_num_rows($res_exnp)>0){
						$row_ex = mysql_fetch_assoc($res_ex);
						 $catids_from_temp_np=$row_ex['catidlineage_nonpaid'];
					}
				}
				$catids_from_temp_np=explode("|P|", $catids_from_temp_np);
				
				$catids_from_temp_np=array_diff($catids_from_temp_np, $existlist);
				$catids_from_temp_np=array_filter($catids_from_temp_np);
				$catids_from_temp_np=implode('|P|', $catids_from_temp_np);
				
				if($this->mongo_flag == 1 || $this->mongo_tme == 1){
					
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->parentid;
					$mongo_inputs['data_city'] 	= $this->data_city;
					$mongo_inputs['module']		= $this->module;
					$mongo_data = array();
					
					$bustemp_tbl 		= "tbl_business_temp_data";
					$bustemp_upt = array();
					$bustemp_upt['catIds'] 					= $catids_from_temp;
					$mongo_data[$bustemp_tbl]['updatedata'] = $bustemp_upt;
					
					$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
					$extrdet_upt = array();
					$extrdet_upt['catidlineage_nonpaid'] 	= $catids_from_temp_np;
					$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
			
					$mongo_inputs['table_data'] 			= $mongo_data;
					$res_cat_del = $this->mongo_obj->updateData($mongo_inputs);
					
					/**/ 
					if(strtolower(trim($this->module)) == 'me' || strtolower(trim($this->module)) == 'jda')
					{
						$this->sendLogs($this->parentid,$mongo_data,$res_cat_del);
					}
					/**/
				}
				else
				{
					$cat_del = "UPDATE tbl_business_temp_data SET 
										catIds 			= '".$catids_from_temp."'
										WHERE contractid='".$this->parentid."'";
					
					$cat_del = $cat_del."/* TMEMONGOQRY */";			
					$res_cat_del   = parent::execQuery($cat_del, $this->conn_temp);
					
					$cat_del = "UPDATE tbl_companymaster_extradetails_shadow SET 
										catidlineage_nonpaid 			= '".$catids_from_temp_np."'
										WHERE parentid='".$this->parentid."'";
					
					$cat_del = $cat_del."/* TMEMONGOQRY */";
					$res_cat_del   = parent::execQuery($cat_del, $this->conn_temp);
				}
		}
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
				
				$temp_catlin_np_arr = array();
				$temp_catlin_np_arr = explode("/,/",trim($row_temp_category['catidlineage_nonpaid'],"/"));
				$temp_catlin_np_arr = array_filter($temp_catlin_np_arr);
				$temp_catlin_np_arr = 	$this->get_valid_categories($temp_catlin_np_arr);
				
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
	
	function sendLogs($parentid,$data,$response){
		
		/**/
		$post_data = array();
		$log_url = 'http://192.168.17.109/logs/logs.php';
		$post_data['ID']                = $parentid;
		$post_data['PUBLISH']           = 'ME';
		$post_data['ROUTE']             = 'MONGOUPDATE';
		$post_data['CRITICAL_FLAG'] 	= 1;
		$post_data['MESSAGE']       	= 'mongo update on shadow tables';
		$post_data['DATA']['url']       = 'category_temp_data_class.php';
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
		}
		return $final_catids_arr;	
	}
	function getCategoryDetails($catids_arr)
	{
		$CatinfoArr = array();
		$catids_str = implode("','",$catids_arr);
		//$sqlCategoryDetails = "SELECT catid,category_name,national_catid,category_scope FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catids_str."')";
		//$resCategoryDetails 	= parent::execQuery($sqlCategoryDetails, $this->conn_catmaster);
		$cat_params = array();
		$cat_params['page'] ='category_temp_data_class';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'catid,category_name,national_catid,category_scope';

		$where_arr  	=	array();			
		$where_arr['catid']			= implode(",",$catids_arr);
		$cat_params['where']		= json_encode($where_arr);
		if(count($catids_arr)>0){
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
				$national_catid	= trim($row_catdetails['national_catid']);
				$CatinfoArr[$catid]['catname'] = $category_name;
				$CatinfoArr[$catid]['national_catid'] = $national_catid;
				$CatinfoArr[$catid]['category_scope'] = trim($row_catdetails['category_scope']);
			}
		}
		return $CatinfoArr;
	}
	function getauthocat($all_temp_cat_details_arr)
	{
		
		$catautoid = array();
		if(count($all_temp_cat_details_arr)>0)
			{
				foreach($all_temp_cat_details_arr as $catid => $catinfo_arr)
				{
					if(stristr($catinfo_arr['catname'],'(Authorised)') || stristr($catinfo_arr['catname'],'(Authorized)'))
					{
						$catautoid[] = 	$catid;
					}
				}
			}
		$auth_nonauth_natcatids = implode(",",$catautoid);		
		$row_data = array();
		$catarray = array();
                if(count($catautoid) > 0)
                {
		/*$sql = "select auth_gen_ncatid from tbl_categorymaster_generalinfo where catid in (".$auth_nonauth_natcatids.")";
		$res 	= parent::execQuery($sql, $this->conn_catmaster);	
		$num_rows 			= parent::numRows($res);
		if($num_rows > 0 ){
			while($row = parent::fetchData($res))
			{
				$row_data[] = $row['auth_gen_ncatid'];
			}	
		}
		$auth_nonauth_natcatids_str = implode(",",$row_data);		*/
		
		$cat_params = array();
		$cat_params['page'] ='category_temp_data_class';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'auth_gen_ncatid';

		$where_arr  	=	array();			
		$where_arr['catid']			= implode(",",$catautoid);
		$cat_params['where']		= json_encode($where_arr);
		if(count($catautoid)>0){
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}
		foreach($cat_res_arr['results'] as $key =>$cat_arrn)
		{
			$catid_arr_auto[] = $cat_arrn['auth_gen_ncatid'];
		}
		//print_r($catid_arr_auto);
		
		//print_r($cat_res_arr['results']['0']['auth_gen_ncatid']);
		$auth_nonauth_natcatids_str = implode(",",$catid_arr_auto);
		
		//print_r($row_data);
				
			
			//echo $sql = "SELECT catid,category_name as catname,national_catid,CASE search_type WHEN 0 THEN 'L' WHEN 1 THEN 'A' WHEN 2 THEN 'Z' WHEN 3 THEN 'SZ' WHEN 4 THEN 'NM' WHEN 5 THEN 'VNM' END  AS searchtype,CASE business_flag WHEN 2 THEN 'B2C' WHEN 1 THEN 'B2B' WHEN 3 THEN 'B2B,B2C' END  AS imparent,if(category_type&128 = 128,1,0) as mfrs,category_scope as  distr,auth_gen_ncatid FROM tbl_categorymaster_generalinfo WHERE auth_gen_ncatid IN (".$auth_nonauth_natcatids_str.") AND biddable_type=1 AND mask_status=0 ";
		//	$res 	= parent::execQuery($sql, $this->conn_catmaster);
		//	$num_rows 			= parent::numRows($res);
			
			$cat_params_new = array();
			$cat_params_new['page'] ='category_temp_data_class';
			$cat_params_new['data_city'] 	= $this->data_city;
			$cat_params_new['return']		= "catid,category_name,national_catid,search_type,business_flag,category_type,category_scope,auth_gen_ncatid";
		

			$where_arr_new  	=	array();			
			$where_arr_new['auth_gen_ncatid']			= $auth_nonauth_natcatids_str;
			$cat_params_new['where']		= json_encode($where_arr_new);
			if(count($catautoid)>0){
				$cat_res_new	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params_new);
			}
			$cat_res_arr_new = array();
			if($cat_res_new !=''){
				$cat_res_arr_new =	json_decode($cat_res_new,TRUE);
			}
			
			//echo "<pre>";
			//print_r($cat_res_arr_new);
			
			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr_new['results'])>0)
			{
				foreach($cat_res_arr_new['results'] as $key =>$cat_arr)
				{
					if(!stristr($cat_arr['catname'],'(Authorised)') || stristr($cat_arr['catname'],'(Authorized)'))
					{	
						$catarray[$cat_arr['catid']]['catid'] = $cat_arr['catid'];
						$catarray[$cat_arr['catid']]['catname'] = $cat_arr['category_name'];
						$catarray[$cat_arr['catid']]['national_catid'] = $cat_arr['national_catid'];
						
						switch($cat_arr['search_type'])
						{
						case '0':
						$catarray[$cat_arr['catid']]['searchtype']= 'L';
						break;
						case '1':
						$catarray[$cat_arr['catid']]['searchtype']= 'A';
						break;
						case '2':
						$catarray[$cat_arr['catid']]['searchtype']= 'Z';
						break;
						case '3':
						$catarray[$cat_arr['catid']]['searchtype']= 'SZ';
						break;
						case '4':
						$catarray[$cat_arr['catid']]['searchtype']= 'NM';
						break;
						case '5':
						$catarray[$cat_arr['catid']]['searchtype']= 'VNM';
						break;
						}
						
						switch($cat_arr['business_flag'])
						{
						case '2':
						$catarray[$cat_arr['catid']]['imparent']= 'B2C';
						break;
						case '1':
						$catarray[$cat_arr['catid']]['imparent']= 'B2B';
						break;
						case '3':
						$catarray[$cat_arr['catid']]['imparent']= 'B2B,B2C';
						break;
						}
						
						//if(category_type&128 = 128,1,0)
						$category_type =	$cat_arr['category_type'];
						$category_type_flag 	  = 0;
						if(((int)$category_type & 128)==128){
							$category_type_flag = 1;
						}
						//category_scope as  distr
						
						$catarray[$cat_arr['catid']]['mfrs'] = $category_type_flag;
						$catarray[$cat_arr['catid']]['distr'] = $cat_arr['category_scope'];
						$catarray[$cat_arr['catid']]['auth_gen_ncatid'] = $cat_arr['auth_gen_ncatid'];
						
					}
				}
			}
		
			
			/*if($num_rows > 0 ){
				while($row = parent::fetchData($res))
				{
					if(!stristr($row['catname'],'(Authorised)') || stristr($row['catname'],'(Authorized)'))
					{
						$catarray[$row['catid']]['catname'] = $row['catname'];
						$catarray[$row['catid']]['national_catid'] = $row['national_catid'];
						$catarray[$row['catid']]['category_scope'] = $row['distr'];
					}
					//$row_data[] = $row['auth_gen_ncatid'];
				}	
			}*/
			
		}
		return $catarray;
		//echo "<pre>gggg";		
		//print_r($catarray);
			
	}	
	
	
	
	function populate_category_temp_data()
	{
		//Obtain already existing categories from db(tbl_business_temp_data).
		//$this->temp_paid_cat_arr;
		 
		//Requested categories which got selected now.
		//$this->requested_category_arr;
		 
		//Reset Authorization Flag If Any Authorized Category Selected
		if(count($this->requested_category_arr)>0)
		{
			$this->resetAuthorizationFlag($requested_cat_details_arr);
			 
			$all_temp_paid_categories_arr = array_merge($this->temp_paid_cat_arr,$this->requested_category_arr);
			$all_temp_paid_categories_arr = array_unique(array_filter($all_temp_paid_categories_arr));
			 
			$all_temp_cat_details_arr = $this->getCategoryDetails($all_temp_paid_categories_arr);
			if(strtolower($this->module) == "de")
			{
				$getauthocat = $this->getauthocat($all_temp_cat_details_arr);
				$all_temp_cat_details_arr = $all_temp_cat_details_arr + $getauthocat;
			}
			//print_r($all_temp_cat_details_arr);
			$catnames_arr = array();
			$catids_arr = array();
			$national_catids_arr = array();
			if(count($all_temp_cat_details_arr)>0)
			{
				foreach($all_temp_cat_details_arr as $catid => $catinfo_arr)
				{
					$catids_arr[] = $catid;
					$catnames_arr[] = $catinfo_arr['catname'];
					$national_catids_arr[] = $catinfo_arr['national_catid'];
				}
			}
			 
			$catnames_str = "|P|";
			$catnames_str .= implode("|P|",$catnames_arr);
			
			$catids_str = "|P|";
			$catids_str .= implode("|P|",$catids_arr);
			
			$national_catids_str = "|P|";
			$national_catids_str .= implode("|P|",$national_catids_arr);
			
			$catname_sel = str_ireplace("|P|","|~|",$catnames_str);
			 
			 
			$categories = $this->slasher($catnames_str);
			$catSelected = $this->slasher($catname_sel);
			 
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
			else
			{
				// Update tbl_business_temp_data
				$sqlUpdtBusinessTempData = "UPDATE tbl_business_temp_data SET 
										categories 		= '".$categories."',
										catIds 			= '".$catids_str."',
										nationalcatIds 	= '".$national_catids_str."',
										catSelected 	= '".$catSelected."'
										WHERE contractid='".$this->parentid."'";
				$sqlUpdtBusinessTempData = $sqlUpdtBusinessTempData."/* TMEMONGOQRY */";
				$resUpdtBusinessTempData   = parent::execQuery($sqlUpdtBusinessTempData, $this->conn_temp);
			}
		
			$sqlUpdtTempMulticity ="UPDATE tbl_business_temp_multicity SET nationalIds = '".$national_catids_str."' WHERE parentid='".$this->parentid."'";
			$resUpdtTempMulticity   = parent::execQuery($sqlUpdtTempMulticity, $this->conn_temp);
			
			$this->updateNationalId(); // updating national catid for both paid/nonpaid category/s in tbl_national_listing_temp
			 
		}
		// Fetching Category Info for sending catdetails
		
		if($this->checkomit)
		{
			$sqlUpdtBusinessShadowData = "UPDATE tbl_companymaster_extradetails_shadow SET nocategory='".$this->nocategory."'  WHERE parentid='".$this->parentid."'";
			$resUpdtBusinessShadowData = parent::execQuery($sqlUpdtBusinessShadowData, $this->conn_iro);
		}
		
		$paid_nonpaid_temp_categories = $this->fetch_contract_temp_categories();
		
		if(count($paid_nonpaid_temp_categories) > 0)
		{
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
			return $result_msg_arr;
		}
		else
		{
			$message = "Category Not Found.";
			echo json_encode($this->send_die_message($message));
			die;
		}
		return $paid_nonpaid_temp_catdetails_arr;
		
		
	}
	function resetAuthorizationFlag()
	{
		$requested_cat_details_arr = $this->getCategoryDetails($this->requested_category_arr);
		$authorized_exists_flag = 0;
		foreach($requested_cat_details_arr as $catinfo_arr)
		{
			if(stristr($catinfo_arr['catname'],'(Authorised)') || stristr($catinfo_arr['catname'],'(Authorized)'))
			{
				$authorized_exists_flag = 1;
				break;
			}
		}
		if($authorized_exists_flag)
		{			
			$sqlResetAuthorisationFlag   = "INSERT INTO tbl_temp_flow_status SET
											parentid ='".$this->parentid."',
											authorisation_flag='0',
											comp_cat_name_flag='1'
																			
											ON DUPLICATE KEY UPDATE
													
											authorisation_flag='0',
											comp_cat_name_flag='1'";
			$resResetAuthorisationFlag   = parent::execQuery($sqlResetAuthorisationFlag, $this->conn_temp);
		}
	}
	function updateNationalId()
	{
		$contract_temp_categories = $this->fetch_contract_temp_categories();
		
		$all_contract_temp_catdetails_arr = $this->getCategoryDetails($contract_temp_categories);
		
		 $alloweddotcom = 0;
		if(strtoupper($this->module) == 'CS' ){
			$sql_intermediate = "SELECT dotcom FROM tbl_temp_intermediate WHERE parentid = '".$this->parentid."' and dotcom>0";
            $res_intermediate   = parent::execQuery($sql_intermediate, $this->conn_temp);
            if($res_intermediate && mysql_num_rows($res_intermediate)>0)
            {
                $alloweddotcom = 1;
            } 
		}
			 
		$national_catids_arr = array();
		if(count($all_contract_temp_catdetails_arr)>0)
		{
			foreach($all_contract_temp_catdetails_arr as $catid => $catinfo_arr)
			{
				if($catinfo_arr['category_scope'] == '1' || $catinfo_arr['category_scope'] == '2' || $alloweddotcom)
				{
					$national_catids_arr[] = $catinfo_arr['national_catid'];
				}	
			}
		}
		$national_catids_str = "|P|";
		$national_catids_str .= implode("|P|",$national_catids_arr);
		$national_catids_str .="|P|";
		
		$TotalCategoryWeight = substr_count($national_catids_str, '|P|') - 1;
		
		$sqlUpdtNationalListing ="UPDATE tbl_national_listing_temp SET Category_nationalid = '".$national_catids_str."' , TotalCategoryWeight = '".$TotalCategoryWeight."' WHERE parentid='".$this->parentid."'";
		$resUpdtNationalListing   = parent::execQuery($sqlUpdtNationalListing, $this->conn_temp);
		
		$insert_debug_log = "INSERT INTO tbl_national_listing_temp_debug SET parentid='".$this->parentid."',page='services/category_temp_data_class.php',line_no= '385',query= '".addslashes($sqlUpdtNationalListing)."',date_time= '".date('Y-m-d H:i:s')."',ucode= '".$_SESSION['ucode']."',uname='".addslashes($_SESSION['uname'])."'";
		$res_insert_debug_log = parent::execQuery($insert_debug_log,$this->conn_temp);
				
	}
	private function send_die_message($msg)
	{
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
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
	
}



?>
