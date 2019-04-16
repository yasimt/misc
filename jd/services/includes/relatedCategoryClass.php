<?php
class relatedCategoryClass extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $dbConIdc    	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	

	var  $catsearch	= null;
	var  $data_city	= null;
	var  $grp 		= null; 	//[MRK|RK|PK] category group default no group 
	var  $off 		= 0; 	//offset- starting of record default 0
	var  $num 		= 50; 	//number of record - default 50
	var  $odr 		= 'REL'; 	//[REL|ALP] REL- Relevance (default) , ALP- Alphabetical order of result 
	var	 $grpvalset = array('MRK','RK','PK');
	var	 $odrvalset = array('REL','ALP');

	function __construct($params)
	{		
		$this->params = $params;		
		$this->setServers();
		
		/* Code for companymasterclass logic starts */
		if($this->params['is_remote'] == 'REMOTE')
		{
			$this->is_split = FALSE;	 // when split table goes live then make it TRUE		
		}
		else
		{
			$this->is_split = FALSE;			
		}

		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}

		if(trim($this->params['str']) != "" && $this->params['str'] != null)
		{
			$this->str  = $this->params['str']; //initialize catsearch
		}else
		{
			$results_array['results'] = array();
			$results_array['error']['code'] = 1;
			$results_array['error']['msg'] = "Incorrect Parameters Passed";
			$return_string = json_encode($results_array);
			echo $return_string;
			die; 
		}

		
		if(trim($this->params['grp']) != "" && $this->params['grp'] != null)
		{
			
			$this->grp 		= strtoupper($this->params['grp']); //initialize group by default it will be null			
			if(!in_array($this->grp,$this->grpvalset))
			{echo json_encode('Please provide correct group value'); exit; }
		}

		if(trim($this->params['off']) != "" && $this->params['off'] != null)
		{
			$this->off  = intval($this->params['off']); //initialize offset
			if($this->off<0)
			{echo json_encode('Please provide positive offset value'); exit; }
		}

		if(trim($this->params['num']) != "" && $this->params['num'] != null)
		{
			$this->num  = intval($this->params['num']); //initialize number of records
			if($this->num<0)
			{echo json_encode('Please provide positive num value'); exit; }
		}

		if(trim($this->params['odr']) != "" && $this->params['odr'] != null)
		{
			$this->odr  = strtoupper($this->params['odr']); //initialize order of results			
			if(!in_array($this->odr,$this->odrvalset))
			{echo json_encode('Please provide correct sorting order value'); exit; }
		}
		$this->request_cid = 0;
		if(isset($this->params['cid']))
		{
			$this->request_cid  = trim($this->params['cid']);
		}
		$this->bfcignore = 0;
		if(isset($this->params['bfcignore']))
		{
			$this->bfcignore  = trim($this->params['bfcignore']);
		}
		$this->mrkonly = 0;
		if(isset($this->params['mrkonly']))
		{
			$this->mrkonly  = intval($this->params['mrkonly']);
		}
		if(trim($this->params['stp']) != "" && $this->params['stp'] != null)
		{
			$this->stp  = intval($this->params['stp']); //initialize variable to identify national listing
			if(trim($this->params['ntp']) != "" && $this->params['ntp'] != null)
			{
				$this->ntp  = intval($this->params['ntp']); //initialize variable to identify national listing type - zone,state or top
			}else
			{
				echo json_encode('Please provide correct national type - zone,state or top'); exit; 
			 }
		}
		
		$this->categoryClass_obj = new categoryClass();
		
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->dbConDjds  		= $db[$data_city]['d_jds']['master'];
		//$this->dbConDjds_slave	= $db[$data_city]['d_jds']['slave'];
		//$this->dbConIro_slave	= $db[$data_city]['iro']['slave'];
		$this->dbConIdc   		= $db[$data_city]['idc']['master'];		
	}
	
	function getCategory()
	{
		$cid_array = array();
		
		$cat_params = array();
		$where_arr  	=	array();
		######################### Most Relevant category ################################
		
		if($this->stp)
		{
			if($this->ntp != 2){
				//$multicity_condition = " AND category_scope = 1 AND city_count=9  ";
				$where_arr['category_scope'] = '1';
				$where_arr['city_count'] 	 = '9';
			}
			else if($this->ntp == 2){
				//$multicity_condition = " AND (category_scope = 1 OR category_scope = 2) AND city_count=9  ";
				$where_arr['category_scope'] = '1,2';
				$where_arr['city_count'] 	 = '9';
			} 
		}
		
		$orderby_clause = "ORDER BY exact_match desc";
		if($this->odr ==1)
			$orderby_clause .= " ,catname asc, callcount desc";
		else
			$orderby_clause .= " ,callcount desc, catname asc";
			
		$catname_search				= trim(preg_replace('#\(.*\)#','',$this->sanitize($this->str))); 
		$catname_search_wo_space	= preg_replace('/\s*/m','',$catname_search);
		$catname_search_processed	= $this->catFilter($this->getSingular($catname_search));
		$catname_search_ignore		= $this->catIgnore($this->sanitize($this->braces_content_removal($this->str)));
		$catname_search_processed_wo_space	= preg_replace('/\s*/m','',$catname_search_processed);
		$catname_search_ignore_processed	= $this->catFilter($this->cat_synonym_singular($catname_search_ignore,false));
		$catname_search_ignore_processed_wo_space =  preg_replace('/\s*/m','',$catname_search_ignore_processed);			
		
		$csip_all = "+".str_replace(" "," +", $catname_search_ignore_processed);
		$csp_all = $catname_search_processed;
				
		if(DEBUG_MODE)
		{
			echo '<br><b>catname_search:</b>'.$catname_search;
			echo '<br><b>catname_search_wo_space:</b>'.$catname_search_wo_space;
			echo '<br><b>catname_search_processed:</b>'.$catname_search_processed;
			echo '<br><b>catname_search_ignore:</b>'.$catname_search_ignore;
			echo '<br><b>catname_search_processed_wo_space:</b>'.$catname_search_processed_wo_space;
			echo '<br><b>catname_search_ignore_processed:</b>'.$catname_search_ignore_processed;
			echo '<br><b>catname_search_ignore_processed_wo_space:</b>'.$catname_search_ignore_processed_wo_space;
			echo '<br><b>csip_all:</b>'.$csip_all;
			echo '<br><b>csp_all:</b>'.$csp_all;
			print_r($this->dbConDjds);
		}
		
		$bfc_condn = '';
		if($this->bfcignore == 1){
			$where_arr['category_type'] 	 = '!64';
			//$bfc_condn = " AND category_type&64=64 !=1 ";
		}
		if($this->mrkonly == 1){
			$cat_params['limit'] = '400';
			//$limit_clause =" LIMIT 400 ";
		}
		$sql= "select catid, national_catid, category_name as catname, biddable_type as cat_type,  callcount, if(category_type&64=64,1,0) as block_for_contract, if(category_type&16=16,1,0) as exclusive, premium_flag, if(catname_search_processed='".$catname_search_processed."',1,0) as exact_match, display_as_filter as filter_flag, associate_national_catid as fnid,search_type,business_flag,bfc_bifurcation_flag as mrg,reach_count,if(miscellaneous_flag&16=16,1,0) as chain_out from tbl_categorymaster_generalinfo 
		where match(catname_search_processed) against('".$csp_all."' in BOOLEAN MODE) AND biddable_type=1 AND mask_status=0 AND bfc_bifurcation_flag NOT IN (4,5,6,7,8) and category_name not like '%jdyp%'
		".$bfc_condn.$multicity_condition.$orderby_clause.$limit_clause;

		//$res_catname 	= parent::execQuery($sql, $this->dbConDjds);
		//$num_rows		= mysql_num_rows($res_catname);
		
		$cat_params['page'] 		= 'relatedCategoryClass';
		$cat_params['scase'] 		= '2';
		$cat_params['parentid'] 	= $this->parentid;
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'catid,category_name,biddable_type';

		
		if($csp_all!=''){
			$where_arr['catname_search_processed']	= $csp_all;
			$where_arr['biddable_type']				= '1';
			$where_arr['mask_status']				= '0';
			$where_arr['bfc_bifurcation_flag']		= '!4,5,6,7,8';
			$cat_params['where']					= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		if(DEBUG_MODE)
		{
			echo '<br><b>MRK Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res_catname;
			echo '<br><b>Num Rows:</b>'.$num_rows;
			echo '<br><b>Error:</b>'.mysql_error();
			
		}
		if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results']) > 0)
		{
			$i=0;
			if(intval($this->request_cid)>0)
			{
				$catinfoArr = $this->getCategoryInfo();
				if(count($catinfoArr)>0)
				{
					$merge_flag = intval($catinfoArr['mrg']);
					$MRK_cat[0]['cid'] = $catinfoArr['cid'];
					$MRK_cat[0]['nid'] = $catinfoArr['nid'];
					$MRK_cat[0]['cnm'] = $catinfoArr['cnm'];
					$MRK_cat[0]['ctype'] = $catinfoArr['ctype'];
					$MRK_cat[0]['bfc'] = $catinfoArr['bfc'];
					$MRK_cat[0]['cst'] = $catinfoArr['cst'];
					$MRK_cat[0]['ccnt'] = $catinfoArr['ccnt'];
					$MRK_cat[0]['btype'] = $catinfoArr['btype'];
					$MRK_cat[0]['excl'] = $catinfoArr['excl'];
					$MRK_cat[0]['premium'] = $catinfoArr['premium'];
					$MRK_cat[0]['chain_out'] = $catinfoArr['chain_out'];
					$MRK_cat[0]['mrg'] = $merge_flag;
					$MRK_cat[0]['rcnt'] = number_format((float)$catinfoArr['rcnt'], 2, '.', '');
					if($merge_flag == 6){
						$MRK_cat[0]['mrgwith'] = $this->mergeDestCat($catinfoArr['cnm']);
					}
					$cid_array[] = $this->request_cid;
					$i++;
				}
			}
			
			
			foreach($cat_res_arr['results'] as $key=> $row)
			{
				if(DEBUG_MODE)
					print_r($row);

				if($row['exact_match']==1)
				{
					$exact_catid = $row['catid'];
					$filter_flag = $row['filter_flag'];
					$fnid = $row['fnid'];
				}			
				if(trim($row['catid']) != $this->request_cid)
				{	
					if(DEBUG_MODE)
						echo '<br>Non Exact Match :';
					
					if($row['search_type']==1)
						$cat_search_type = "A";
					elseif($row['search_type']==2)
						$cat_search_type = "Z";
					elseif($row['search_type']==3)
						$cat_search_type = "SZ";
					elseif($row['search_type']==4)
						$cat_search_type = "NM";
					elseif($row['search_type']==5)
						$cat_search_type = "VNM";
					else
						$cat_search_type = "L";
						
						
					$business_flag	= trim($row['business_flag']);
					if($business_flag == 1){
						$btype = "B2B";
					}else if($business_flag == 2){
						$btype = "B2C";
					}else if($business_flag == 3){
						$btype = "B2B,B2C";
					}else{
						$btype = "OTHER";
					}	
					$merge_flag = intval($row['mrg']);	
					$MRK_cat[$i]['cid'] = $row['catid'];
					$MRK_cat[$i]['nid'] = $row['national_catid'];
					$MRK_cat[$i]['cnm'] = $row['catname'];
					$MRK_cat[$i]['ctype'] = $row['cat_type'];
					$MRK_cat[$i]['bfc'] = $row['block_for_contract'];
					$MRK_cat[$i]['cst'] = $cat_search_type;
					$MRK_cat[$i]['ccnt'] = $row['callcount'];
					$MRK_cat[$i]['btype'] = $btype;
					$MRK_cat[$i]['excl'] = $row['exclusive'];
					$MRK_cat[$i]['premium'] = $row['premium_flag'];
					$MRK_cat[$i]['chain_out'] = $row['chain_out'];
					$MRK_cat[$i]['mrg'] = $merge_flag;
					$MRK_cat[$i]['rcnt'] = number_format((float)$row['reach_count'], 2, '.', '');
					if($merge_flag == 6){
						$MRK_cat[$i]['mrgwith'] = $this->mergeDestCat($row['catname']);
					}
					$i++;
					$cid_array[] = $row['catid'];
					
				}
			}
		}
		else
		{
			if(intval($this->request_cid)>0)
			{
				$catinfoArr = $this->getCategoryInfo();
				if(count($catinfoArr)>0)
				{
					$merge_flag = intval($catinfoArr['mrg']);
					$MRK_cat[0]['cid'] = $catinfoArr['cid'];
					$MRK_cat[0]['nid'] = $catinfoArr['nid'];
					$MRK_cat[0]['cnm'] = $catinfoArr['cnm']." (".$catinfoArr['cst'].")";
					$MRK_cat[0]['ctype'] = $catinfoArr['ctype'];
					$MRK_cat[0]['bfc'] = $catinfoArr['bfc'];
					$MRK_cat[0]['cst'] = $catinfoArr['cst'];
					$MRK_cat[0]['ccnt'] = $catinfoArr['ccnt'];
					$MRK_cat[0]['btype'] = $catinfoArr['btype'];
					$MRK_cat[0]['excl'] = $catinfoArr['excl'];
					$MRK_cat[0]['premium'] = $catinfoArr['premium'];
					$MRK_cat[0]['chain_out'] = $row['chain_out'];
					$MRK_cat[0]['mrg'] = $merge_flag;
					$MRK_cat[0]['rcnt'] = number_format((float)$catinfoArr['rcnt'], 2, '.', '');
					if($merge_flag == 6){
						$MRK_cat[0]['mrgwith'] = $this->mergeDestCat($catinfoArr['cnm']);
					}
					$cid_array[] = $this->request_cid;
				}
			}
		}
		/*
		######################################### Filter Keywords ############################################
		if($filter_flag==1)
		{
			$orderby_clause = "ORDER BY category_position ASC";
			
			$sql= "select catid, national_catid, category_name as catname, biddable_type as cat_type,  callcount, if(category_type&64=64,1,0) as block_for_contract, if(category_type&16=16,1,0) as exclusive, premium_flag, if(catname_search_ignore_processed='".$catname_search_ignore_processed."',1,0) as exact_match, display_as_filter as filter_flag, associate_national_catid as f_nid
			from tbl_categorymaster_generalinfo 
			where associate_national_catid =".$fnid." AND biddable_type=1 
			".$orderby_clause.$limit_clause;

			$res_catname 	= parent::execQuery($sql, $this->dbConDjds);
			$num_rows		= mysql_num_rows($res_catname);

			if(DEBUG_MODE)
			{
				echo '<br><b>Filter Query:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res_catname;
				echo '<br><b>Num Rows:</b>'.$num_rows;
				echo '<br><b>Error:</b>'.mysql_error();
				
			}
			
			if($res_catname && $num_rows > 0)
			{
				$i=0;
				while($row=mysql_fetch_assoc($res_catname))
				{
					//print_r($row);	
					$FK_cat[$i]['cid'] = $row['catid'];
					$FK_cat[$i]['nid'] = $row['national_catid'];
					$FK_cat[$i]['cnm'] = $row['catname'];
					$FK_cat[$i]['ctype'] = $row['cat_type'];
					$FK_cat[$i]['bfc'] = $row['block_for_contract'];
					$i++;
					$cid_array[] = $row['catid'];
					if($row['f_nid']==$row['national_catid'])
					{
						$head_filter = 1;
						$filter_flag = $row['filter_flag'];
						$head_nid = $row['national_catid'];
						$head_cid = $row['catid'];
					}
				}
			}
		}
		*/
		######################################### Related Keywords ############################################
		//http://sunnyshende.jdsoftware.com/web_services/web_services/RelatedKeywords.php?catname=chinese%20restaurants&city=mumbai&limit=30
		
		if($this->mrkonly !=1){
		
		$apiurl = APIDOMAIN."RelatedKeywords.php?catname=".$this->str."&city=".$this->data_city."&limit=999";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERPWD, $username.":".$password);
		curl_setopt($ch, CURLOPT_URL, $apiurl);
		//curl_setopt($ch, CURLOPT_USERPWD, "beta2:Kx0N3wY#@R");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close ($ch);

		$result = json_decode($result, true);
		if(DEBUG_MODE==1)
		{
			echo '<br>API URL:'.$apiurl;
			//print_r($result);
		}
		
		
		}
		if($result)
		{
			
			if($multicity_condition)
			{
				if(DEBUG_MODE==1)
				{
					echo '<br>before national listing filer ';
					print_r($result);
				}
				
				foreach ($result as $key=>$value)
				{
					$catid_list[] = $value['catid'];
				}
				
				if(count($catid_list))
				{
				   $catid_list    = array_unique($catid_list);
				   //$sql_validate  = "select catid,bfc_bifurcation_flag as mrg,if(miscellaneous_flag&16=16,1,0) as chain_out from tbl_categorymaster_generalinfo where catid in (".implode(',',$catid_list).") AND biddable_type=1 AND mask_status=0 AND bfc_bifurcation_flag NOT IN (4,5,6,7) and category_name not like '%jdyp%' ".$multicity_condition."";
				   //$res_validate  = parent::execQuery($sql_validate, $this->dbConDjds);
				    $cat_params = array();
				    $cat_params['page'] 		= 'relatedCategoryClass';					
					$cat_params['parentid'] 	= $this->parentid;
					$cat_params['data_city'] 	= $this->data_city;
					$cat_params['return']		= 'catid,category_name,biddable_type';

					$where_arr  = array();
					if($this->stp)
					{
						if($this->ntp != 2){
							//$multicity_condition = " AND category_scope = 1 AND city_count=9  ";
							$where_arr['category_scope'] = '1';
							$where_arr['city_count'] 	 = '9';
						}
						else if($this->ntp == 2){
							//$multicity_condition = " AND (category_scope = 1 OR category_scope = 2) AND city_count=9  ";
							$where_arr['category_scope'] = '1,2';
							$where_arr['city_count'] 	 = '9';
						} 
					}
					if(count($catid_list)!=''){
						$where_arr['catid']			= implode(',',$catid_list);
						$where_arr['biddable_type']	= '1';
						$where_arr['mask_status']	= '0';
						$where_arr['bfc_bifurcation_flag']		='!4,5,6,7,8';						
						
						$cat_params['where']	= json_encode($where_arr);
						$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
					}
					
					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}
				   
				   if(DEBUG_MODE==1)
					{
						echo '<br>national listing sql '.$sql_validate;
						echo '<br>national listing res '.$res_validate;
						echo '<br>national listing row '.mysql_num_rows($res_validate);
					}
					
				   if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
				   {
					   foreach($cat_res_arr['results'] as $key =>$row_validate)
					   {
						   $catid_data_tag[$row_validate['catid']]['mrg'] = $row_validate['bfc_bifurcation_flag'];
						   $national_tagged_catids[] = $row_validate['catid'];
					   }
					   
					   if(DEBUG_MODE==1)
						{
							echo '<br>found national listing ids ';
							print_r(explode(',',$row_validate['catids']));
						}
						
					   
					  // if($row_validate['catids'])
					   //$national_tagged_catids = explode(',',$row_validate['catids']);
					   
					   //$non_national_catids = array_diff($catid_list,$national_tagged_catids);
					   
				   }
				   
				  foreach ($result as $key=>$value)
				  {
						if(!in_array($value['catid'],$national_tagged_catids))
						{
							unset($result[$key]);
						}
				   }
				   
				   $result = array_values($result);
				   
				}
				
				if(DEBUG_MODE==1)
				{
					echo '<br>after national listing filer ';
					print_r($result);
				}
				
			}else {
					
					foreach ($result as $key=>$value)
					{
						$catid_list_tag[] = $value['catid'];
					}
					
				   //$sql_validate  = "select catid,bfc_bifurcation_flag as mrg,if(miscellaneous_flag&16=16,1,0) as chain_out from tbl_categorymaster_generalinfo where catid in (".implode(',',$catid_list_tag).") AND biddable_type=1 AND mask_status=0  AND bfc_bifurcation_flag NOT IN (4,5,6,7)";
				   //$res_validate  = parent::execQuery($sql_validate, $this->dbConDjds);
					$cat_params = array();
					$cat_params['page'] 		='relatedCategoryClass';
					$cat_params['data_city'] 	= $this->data_city;
					$cat_params['return']		= 'catid,bfc_bifurcation_flag,miscellaneous_flag';

					$where_arr  	=	array();			
					$where_arr['catid']			= implode(",",$catid_list_tag);
					$where_arr['biddable_type']	= '1';
					$where_arr['mask_status']	= '0';
					$where_arr['bfc_bifurcation_flag']	= '!4,5,6,7,8';
					
					$cat_params['where']		= json_encode($where_arr);
					if(count($catid_list_tag)>0){
						$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
					}
					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}
				   
				   if(DEBUG_MODE==1)
					{
						echo '<br>tag check sql '.$sql_validate;
						echo '<br>tag check res '.$res_validate;
						echo '<br>tag check row '.mysql_num_rows($res_validate);
					}
					
				   if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
				   {
					   foreach($cat_res_arr['results'] as $key =>$row_validate)
					   {
						   $miscellaneous_flag = $row_validate['miscellaneous_flag'];
						   if(((int)$miscellaneous_flag &16)==16 ){
							   $chain_out = 1;
						   }
						   else{
								$chain_out = 0;
						   }
						   
						   $catid_data_tag[$row_validate['catid']]['mrg'] = $row_validate['bfc_bifurcation_flag'];
						   $catid_data_tag[$row_validate['catid']]['chain_out'] = $chain_out;
						   $found_catids[] = $row_validate['catid'];
						   
					   }
					   
					   if(DEBUG_MODE==1)
						{
							echo '<br>tag catid ids ';
							print_r($catid_data_tag);
						}
				   }
				  
				  if(count($found_catids)>0) 
				  {
					  foreach ($result as $key=>$value)
					  {
							if(!in_array($value['catid'],$found_catids))
							{
								unset($result[$key]);
							}
					   }
					}
				   
				   
			}
			
			$i=0;
			foreach($result as $key=>$value)
			{
				//echo '<hr>';
				//print_r($value);
				if(is_array($value['filter_list']))
				{	
					$j=0;
					foreach($value['filter_list'] as $fkey=>$fdata)
					{
						$FK_cat[$j]['cid']   = $fdata['fcatid'];
						$FK_cat[$j]['nid']   = $fdata['fnational_catid'];
						$FK_cat[$j]['cnm']   = $fdata['fcatname'];
						$FK_cat[$j]['ctype'] = 1;
						$FK_cat[$j]['bfc']   = $fdata['bfc'];
						$FK_cat[$j]['chain_out']   = $fdata['chain_out'];
						$j++;
						$cid_array[] = $fdata['fcatid'];
					}
				}
				if(!(in_array($value['catid'], $cid_array)))
				{
					if($value['cst']==1)
						$cat_search_type = "A";
					elseif($value['cst']==2)
						$cat_search_type = "Z";
					elseif($value['cst']==3)
						$cat_search_type = "SZ";
					elseif($value['cst']==4)
						$cat_search_type = "NM";
					elseif($value['cst']==5)
						$cat_search_type = "VNM";
					else
						$cat_search_type = "L";
						
					$RK_cat[$i]['cid']  = (string)$value['catid'];
					$RK_cat[$i]['nid']  = $value['national_catid'];
					$RK_cat[$i]['cnm']  = $value['catname'];
					$RK_cat[$i]['ctype']= 1; 
					$RK_cat[$i]['bfc'] 	= $value['bfc'];
					$RK_cat[$i]['cst'] 	= $value['cst'];
					$RK_cat[$i]['ccnt'] = $value['ccnt'];
					$RK_cat[$i]['btype'] = $value['category_type'];
					$RK_cat[$i]['excl'] = $value['excl'];
					$RK_cat[$i]['premium'] = $value['premium_flag'];
					$RK_cat[$i]['chain_out'] = ($catid_data_tag[$value['catid']]['chain_out'] > 0) ? $catid_data_tag[$value['catid']]['chain_out'] : "0" ;
					$RK_cat[$i]['mrg'] = ($catid_data_tag[$value['catid']]['mrg'] > 0) ? $catid_data_tag[$value['catid']]['mrg'] : 0 ; // default value
					$i++;
					
					$cid_array[] = $value['catid'];
				}
			}
		}
		######################################### Popular Keywords ############################################
		if(($this->mrkonly !=1) && (intval($exact_catid)>0))
		{
			$sql= "select group_concat(bid_catid order by cnt desc) as pop_catids from tbl_related_categories where for_bid_catid=".$exact_catid;

			$res_catname 	= parent::execQuery($sql, $this->dbConDjds);
			$num_rows		= mysql_num_rows($res_catname);

			if(DEBUG_MODE)
			{
				echo '<br><b>Popular Keywords Query:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res_catname;
				echo '<br><b>Num Rows:</b>'.$num_rows;
				echo '<br><b>Error:</b>'.mysql_error();
				
			}
			if($res_catname && $num_rows > 0)
			{
				$row=mysql_fetch_assoc($res_catname);
				$pop_catids = $row['pop_catids'];
				if(DEBUG_MODE)
					echo '<br>Popular catids:'.$pop_catids;
				if($pop_catids)
				{
					$cat_params =  array();
					$where_arr  =  array();
					if($this->odr ==1){
						//$orderby_clause = "ORDER BY catname asc, callcount desc";
						$where_arr['orderby']  ="ORDER BY catname asc, callcount desc";
					}
					else{
						$cat_params['scase']   = '1';
						//$orderby_clause = "ORDER BY field(catid,".$pop_catids.")";
					}
					
					//~ $sql= "select catid, national_catid, category_name as catname, biddable_type as cat_type,  callcount, if(category_type&64=64,1,0) as block_for_contract, if(category_type&16=16,1,0) as exclusive, premium_flag, search_type, business_flag,bfc_bifurcation_flag as mrg
					//~ from tbl_categorymaster_generalinfo 
					//~ where catid in (".$pop_catids.") AND biddable_type=1 AND mask_status=0  AND bfc_bifurcation_flag NOT IN (4,5,6,7)
					//~ ".$multicity_condition.$orderby_clause.$limit_clause;

					//$res_catname 	= parent::execQuery($sql, $this->dbConDjds);
					//$num_rows		= mysql_num_rows($res_catname);
					
					$cat_params['page'] 		= 'relatedCategoryClass';					
					$cat_params['data_city'] 	= $this->data_city;
					$cat_params['return']		= 'catid,national_catid,category_name,biddable_type,callcount,category_type,premium_flag,search_type,business_flag,bfc_bifurcation_flag';
					if($this->mrkonly == 1){
						$cat_params['limit'] = '400';						
					}
					if($this->stp)
					{
						if($this->ntp != 2){
							//$multicity_condition = " AND category_scope = 1 AND city_count=9  ";
							$where_arr['category_scope'] = '1';
							$where_arr['city_count'] 	 = '9';
						}
						else if($this->ntp == 2){
							//$multicity_condition = " AND (category_scope = 1 OR category_scope = 2) AND city_count=9  ";
							$where_arr['category_scope'] = '1,2';
							$where_arr['city_count'] 	 = '9';
						} 
					}
					
					if($pop_catids!=''){
						$where_arr['catid']				= $pop_catids;
						$where_arr['biddable_type']		= '1';
						$where_arr['mask_status']		= '0';
						$where_arr['bfc_bifurcation_flag']		= '!4,5,6,7,8';
						$cat_params['where']	= json_encode($where_arr);
						$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
					}
					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}

					if(DEBUG_MODE)
					{
						echo '<br><b>MRK Query:</b>'.$sql;
						echo '<br><b>Result Set:</b>'.$res_catname;
						echo '<br><b>Num Rows:</b>'.$num_rows;
						echo '<br><b>Error:</b>'.mysql_error();
						
					}
					if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results']) > 0)
					{
						$i=0;
						foreach($cat_res_arr['results'] as $key =>$row)
						{
							//print_r($row);
							$category_type =	$row['category_type'];
							
							$block_for_contract = 0;
							if(((int)$category_type & 64) == 64){
								$block_for_contract = 1;
							}
							$exclusive = 0;
							if(((int)$category_type & 64) == 64){
								$exclusive = 1;
							}
								
							if(!(in_array($row['catid'], $cid_array)))
							{
								if($row['search_type']==1)
									$cat_search_type = "A";
								elseif($row['search_type']==2)
									$cat_search_type = "Z";
								elseif($row['search_type']==3)
									$cat_search_type = "SZ";
								elseif($row['search_type']==4)
									$cat_search_type = "NM";
								elseif($row['search_type']==5)
									$cat_search_type = "VNM";
								else
									$cat_search_type = "L";
									
								$business_flag	= trim($row['business_flag']);
								if($business_flag == 1){
									$btype = "B2B";
								}else if($business_flag == 2){
									$btype = "B2C";
								}else if($business_flag == 3){
									$btype = "B2B,B2C";
								}else{
									$btype = "OTHER";
								}	
								$merge_flag = intval($row['bfc_bifurcation_flag']);	
								$PK_cat[$i]['cid'] = $row['catid'];
								$PK_cat[$i]['nid'] = $row['national_catid'];
								$PK_cat[$i]['cnm'] = $row['category_name'];
								$PK_cat[$i]['ctype'] = $row['biddable_type'];
								$PK_cat[$i]['bfc'] = $block_for_contract;
								$PK_cat[$i]['cst'] = $cat_search_type;
								$PK_cat[$i]['ccnt'] = $row['callcount'];
								$PK_cat[$i]['btype'] = $btype;
								$PK_cat[$i]['excl'] = $exclusive;
								$PK_cat[$i]['premium'] = $row['premium_flag'];
								$PK_cat[$i]['mrg'] = $merge_flag;
								if($merge_flag == 6){
									$PK_cat[$i]['mrgwith'] = $this->mergeDestCat($row['category_name']);
								}
								$i++;
								$cid_array[] = $row['catid'];
							}

						}
					}
				}
			}
		}
		
		

		$rescategories = array();

		// filter category addition 
		$FLCarray[0]['cid'] = 304085;
		$FLCarray[0]['cnm'] = 'Restaurants';
		$FLCarray[0]['ctp'] = 1;
		$FLCarray[0]['pcnt'] = 0;
		$FLCarray[0]['cnt'] = 0;
		$FLCarray[0]['bfc'] = 0;
		$FLCarray[0]['ect'] = 0;            
	
		$FLCarray[1]['cid'] = 12586;
		$FLCarray[1]['cnm'] = 'Home Delivery Restaurants';
		$FLCarray[1]['ctp'] = 1;
		$FLCarray[1]['pcnt'] = 0;
		$FLCarray[1]['cnt'] = 0;
		$FLCarray[1]['bfc'] = 0;
		$FLCarray[1]['ect'] = 0;
		

		if($this->grp!=null)
		{
			
			switch($this->grp)
			{
				case 'MRK':
				$rescategories['MRK']=$categories;
				break;
				case 'RK':
				$rescategories['RK']=$categories;
				break;
				case 'PK':
				$rescategories['PK']=$categories;
				break;
			}
		
		}else
		{
			$rescategories['FLC']= $FK_cat;
			$rescategories['MRK']= $MRK_cat;
			$rescategories['RK'] = $RK_cat;
			$rescategories['PK'] = $PK_cat;
			
		}
		
		$return_array['results'] = $rescategories;
		$return_array['error']['code'] = "0";
		$return_array['error']['msg'] = "";
		return $return_array;
	}
	
	function getSingular($str='')
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
	function applyIgnore($str)
	{
		$ig_strt = array('/^\bthe\b/i','/^\bdr\.\s/i','/^\bdr\b/i','/^\bprof\.\s/i','/^\bprof\b/i','/^\band\b/i','/^\bbe\b/i');
		$ig_last = array('/\bpvt\.\s/i','/\bltd\.\s/i','/\bpvt\b/i','/\bltd\b/i','/\bprivate\b/i','/\blimited\b/i');
		$s = $str;
		$s = preg_replace($ig_strt,'',$s);
		$s = preg_replace($ig_last,'',trim($s));
		$s = preg_replace('/[\s+]+/',' ',trim($s));
		return (strlen($s)<=1) ? $str : $s;
	}
	function sanitize($str)
	{
		$str = preg_replace('/[@&-.,_)(\s+]+/',' ',$str);
		$str = preg_replace('/\\\+/i',' ',$str);
		$str = preg_replace("/[^a-zA-Z0-9\s]+/",'',$str);
		$str = preg_replace('/\s\s+/',' ',$str);
		return trim(strtolower($str));
	}
	
	
	function braces_content_removal($str,$i=0)
	{
		//echo '<hr>';
		//echo '<br>str->'.$str;
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
				//echo '<br>End----->'.
				$end = strpos($str,')');
			}
			if(!$eflag)
			{
				//echo '<br>new end->'.
				$end =$start;
				//$end = strlen($str);
			}
			if(!$sflag)
			{
				//echo '<br>new start->'.
				$start = $end;
			}

			if($end < $start)
			{
				//echo '<br>new start(end < start)->'.
				$start = 0;
			}

			//echo '<br>Start->'.$start;
			//echo '<br>End->'.$end;
			//echo '<br>output->'.
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

	function catFilter($categoryname)
		{
		 $filter = '/\b(about|after|all|also|an|and|another|any|are|as|at|be|because|been|before|being|between|both|but|by|came|can|come|could|did|do|does|each|else|for|from|get|got|has|had|he|have|her|here|him|himself|his|how|if|in|into|is|it|its|like|many|me|might|more|most|much|must|my|never|now|of|on|only|or|other|our|out|over|said|same|see|should|since|so|some|still|such|take|than|that|their|them|then|there|these|they|this|those|through|to|too|under|use|upto|until|very|want|was|way|we|well|were|what|when|which|while|who|will|with|would|you|your|the)\b/i';
		
		 $categoryname = trim(preg_replace($filter,'',$categoryname));
		 if(!preg_match('/\b(up)\b$/i',$categoryname))
		 {
			$categoryname = str_ireplace(' up ',' ',$categoryname);
		 }
		 return preg_replace('/[\s\s+]+/',' ',$categoryname);
		}

	function cat_synonym_singular($str, $synonym = false)
	{
		$str_split = explode(' ', $str);
		$str_singluar = '';
		foreach($str_split as $st)
		{
			$singular = (strlen(trim($st)) >= 4) ? $this->getSingular($st) : $st;
			
			if($synonym == true)
			{
				$singular_syn = $this->get_catsyn($singular);
				$singular = ($singular_syn != '') ? $singular_syn : $singular;
				$singular = $this->cat_synonym_singular($singular);
			}
			
			$str_singluar[] = $singular;
		}
		return implode(' ', $str_singluar);
	}
	
	function catIgnore($category_search)
	{
		$str = $category_search;
		$ignore = array('dealer','dealers','retailer','retailers','vendor','vendors','&','and','on','shop','shops','doctor','doctors');

		foreach($ignore as $w)
		{
			$str = preg_replace('/\b'.$w.'\b/i','',$str);
		}
		//$str = preg_replace('/\brestaurant[s]*\b$/i','',$str);
		return strlen(trim($str))<1 ? $category_search : trim(preg_replace('/[\s\s+]+/',' ',$str));
	}
	function getCategoryInfo()
	{
		$catInfoArr = array();
		$cat_params = array();
		$where_arr  = array();
		
		$bfc_condn = '';
		if($this->bfcignore == 1){
			//$bfc_condn = " AND category_type&64=64 !=1 ";
			$where_arr['category_type']	= "!64";
		}
		
		//$sqlCategoryInfo = "SELECT catid,national_catid,category_name,biddable_type,if(category_type&64=64,1,0) as block_for_contract, if(category_type&16=16,1,0) as exclusive, premium_flag,  callcount,search_type,business_flag,bfc_bifurcation_flag as mrg,reach_count,if(miscellaneous_flag&16=16,1,0) as chain_out FROM tbl_categorymaster_generalinfo WHERE catid = '".$this->request_cid."' ".$bfc_condn." AND mask_status=0 AND bfc_bifurcation_flag NOT IN (4,5,6,7)";
		//$resCategoryInfo 	= parent::execQuery($sqlCategoryInfo, $this->dbConDjds);
		
		$cat_params['data_city'] 	= $this->data_city;			
		$cat_params['return']		= 'catid,national_catid,category_name,biddable_type,category_type,premium_flag,callcount,search_type,business_flag,bfc_bifurcation_flag,reach_count,miscellaneous_flag';
		
		$where_arr['catid']			= str_replace("','", ",",$this->request_cid);
		$where_arr['mask_status']	= "0";
		$where_arr['bfc_bifurcation_flag']	= "!4,5,6,7,8";			
		$cat_params['where']		= json_encode($where_arr);

		$cat_res_arr = array();
		if($this->request_cid!=''){
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
				
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}
		if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
		{
			$row_catinfo = $cat_res_arr['results']['0'];
			
			if($row_catinfo['search_type']==1)
				$cat_search_type = "A";
			elseif($row_catinfo['search_type']==2)
				$cat_search_type = "Z";
			elseif($row_catinfo['search_type']==3)
				$cat_search_type = "SZ";
			elseif($row_catinfo['search_type']==4)
				$cat_search_type = "NM";
			elseif($row_catinfo['search_type']==5)
				$cat_search_type = "VNM";
			else
				$cat_search_type = "L";
				
			$business_flag	= trim($row_catinfo['business_flag']);
			if($business_flag == 1){
				$btype = "B2B";
			}else if($business_flag == 2){
				$btype = "B2C";
			}else if($business_flag == 3){
				$btype = "B2B,B2C";
			}else{
				$btype = "OTHER";
			}
			$category_type =	trim($row_catinfo['category_type']);
			if(((int)$category_type&64)==64){
				$block_for_contract = 1;
			}
			else{
				$block_for_contract = 0;	
			}

			if(((int)$category_type&16)==16){
				$exclusive = 1;
			}
			else{
				$exclusive = 0;	
			}
			$miscellaneous_flag =	trim($row_catinfo['miscellaneous_flag']);
			if(((int)$category_type&16)==16){
				$chain_out = 1;
			}
			else{
				$chain_out = 0;	
			}
			$mrg =trim($row_catinfo['bfc_bifurcation_flag']);

			$catInfoArr['cid'] 		= trim($row_catinfo['catid']);
			$catInfoArr['nid'] 		= trim($row_catinfo['national_catid']);
			$catInfoArr['cnm'] 		= trim($row_catinfo['category_name']);
			$catInfoArr['ctype'] 	= trim($row_catinfo['biddable_type']);
			$catInfoArr['bfc'] 		= $block_for_contract;
			$catInfoArr['cst'] 		= $cat_search_type;
			$catInfoArr['ccnt'] 	= trim($row_catinfo['callcount']);
			$catInfoArr['btype'] 	= $btype;
			$catInfoArr['excl'] 	= $exclusive;
			$catInfoArr['premium'] 	= trim($row_catinfo['premium_flag']);
			$catInfoArr['mrg'] 		= $mrg;
			$catInfoArr['chain_out'] = $chain_out;
			$catInfoArr['rcnt'] 	= number_format((float)$row_catinfo['reach_count'], 2, '.', '');
		}
		return $catInfoArr;
	}
	function mergeDestCat($catsrc)
	{
		$catname_des = '';
		$sqlDestCategory = "SELECT catname_des FROM tbl_category_merging_final WHERE  catname_src = '".addslashes(stripslashes($catsrc))."' LIMIT 1";
		$resDestCategory = parent::execQuery($sqlDestCategory, $this->dbConDjds);
		if($resDestCategory && parent::numRows($resDestCategory)>0)
		{
			$row_des_cat = parent::fetchData($resDestCategory);
			$catname_des = trim($row_des_cat['catname_des']);
		}
		return $catname_des;
	}
}




?>
