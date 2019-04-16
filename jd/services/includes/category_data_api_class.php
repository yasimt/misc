<?php
class categoryDetailsClass extends DB
{
	var $dataservers = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');

	function __construct($params)
	{
		$this->params = $params;
		$this->setServers();
	}

	function setServers()
	{
		global $db;

		$data_city = ((in_array(strtolower($this->params['city']), $this->dataservers)) ? strtolower($this->params['city']) : 'remote');
		$this->dbConDjds = $db[$data_city]['d_jds']['master'];
		$this->conn_iro = $db[$data_city]['iro']['master'];

		if (DEBUG_MODE) {
			echo '<br>City : '.$data_city;
			echo '<br>dbConDjds:';
			print_r($this->dbConDjds);
		}
	}

	function get_catgory_details($return, $city, $where, $orderby , $limit, $q_type)
	{
		if(DEBUG_MODE==1)
		{
			echo '<br>';
			echo '<br> Function call -->  get_catgory_details';
			echo '<br> city -->'.$city;
			echo '<br> where -->'.$where;
			echo '<br> orderby -->'.$orderby;
			echo '<br> limit -->'.$limit;
		}

		if($q_type == "parentinfo")
		{
			$table_name = "tbl_categorymaster_parentinfo";
		}
		else
		{
			$table_name = "tbl_categorymaster_generalinfo";
		}
		$array_city 	= array('MUMBAI','DELHI','BANGALORE','KOLKATA','CHENNAI','HYDERABAD','PUNE','AHMEDABAD');
		$cnames 		= '"'.str_replace(',', '","', $cnames).'"';
		if($return == "" || $return == null)
			$return = 'national_catid,category_name';
		else
		{
			if(strpos($return,'national_catid') !== false)
			{
				$return = $return;
			}
			else
				$return = 'national_catid'.",".$return;
		}
		$where_condition   		= json_decode($where,true);
		$where_array 			= array_keys($where_condition);
		$return_array			= explode("," ,$return);

		if($q_type == "parentinfo")
		{
			$field_array = array("national_catid","parent_national_catid","catid","parent_catid","category_name","child_count","parent_flag","parent_catid_lineage","parent_nationalcatid_lineage","parentlineage","category_depth","callcount","callcount_rolling","category_earning","category_contribution","callcnt_avg","parent_callcount","parent_callcnt_avg","parent_callcount_rolling","parent_filter_flag","parent_filter_callcnt_avg","parent_filter_callcnt_rolling","parent_category_earning","brand_parent_filter_flag","brand_parent_filter_callcnt_avg","brand_parent_filter_callcnt_rolling","mask_status","original_creator","original_date","updated_by","updated_on","backend_uptdate","callcount_3month","parent_filter_callcount_3month","multiparentage_id","auto_id","parent_reach_count","parent_reach_count_avg","parent_reach_count_rolling");
		}
		else
		{	
			$field_array	=	array("national_catid","catid","category_name","table_no","service_name","brand_name","total_results","search_type","category_type","category_source","business_flag","category_verticals","biddable_type","category_addon","paid_clients","nonpaid_clients","catlookup_flag","catlookup_script","mask_status","isdeleted","category_position","categoryname_display","prompt_categoryname_display","display_as_filter","associate_national_catid","parent_filter_flag","promt_ratings_flag","display_product_flag","display_flag","force_display","regionlist","g_popularity","avg_cpc","callcount","callcount_rolling","category_earning","category_contribution","callcnt_avg","mail_authorised","smsfeedback_flag","competitor_details_flag","other_flag","deduction_buffer_days","city_count","noduplicatecheck","syncount","category_weight","budgeting_parent_catid","budgeting_parent_catname","budgeting_parent_contribution","catname_search","catname_search_processed","catname_search_processed_ws","catname_search_ignore_processed","catname_search_ignore_processed_ws","movie_rating","movie_censor_rating","movie_release_date","original_creator","original_date","updated_by","updated_on","backend_uptdate","active_flag","event_end_date","user_rating_flag","category_scope","top_category_flag","partial_inventory","bd_caption_id","number_masking","category_guarantee","add_bform_flag","premium_flag","rating_productname","group_id","local_listing_flag","bestdealFlag","bd_msg_id","citylineage","citylineage_ws","sort_photos_tag","photo_tag","is_restricted","emergency_type","video_id","business_tag","brand_filter_flag","block_for_sale","lock_for_edit","new_logic","budget_type","total_hidden_results","movie_show_count","video_flag","cuisine_name","sub_category_type","entity_name","entity_flag","entity_id","c2l_strip_word","assoc_flag","brand_flag","premium_source","premium_comment","display_product_preference","show_button_count","new_release","universal_flow","universal_relevent_count","rest_price_range","popular_cat","top_category_slab","master_brand_name","auto_suggest_flag","filter_callcount_rolling","filter_callcount","misc_parent_flag","image_path","callcount_3month","filter_callcount_3month","hk_flag","sub_display_product_flag","feedback_count","layer_type","image_present","image_id","movie_ncatid","misc_cat_flag","source","area_flow_type","search_flow_type","group_master_id","ods_dname","template_id","hk_desc","bidvalue","bredcrum_parent","ods_ncatid","attribute_group","c2l_cat_map","c2l_script_title","display_product_abbr","category_ranking","bfc_bifurcation_flag","reach_count","reach_count_rolling","reach_count_avg","reach_count_3month","filter_reach_count","filter_reach_count_rolling","category_conversion_flag","priority_flag","category_description","ad_id","comp_lookup_scriptid","mapped_attribute","auth_flag","auth_gen_ncatid","miscellaneous_flag","cuisine_tag","social_interest_id","cat_mapped_attr","merge_flag","nm_flag","callcount_original","callcount_rolling_original","reach_count_original","reach_count_rolling_original","subscript","tag_along_flag","tag_national_catid","tag_catname_display","tag_ncid_position","category_adwords","featured_on_flag","fp_max_cnt");
		}

		$orderby_array = explode(',', $orderby);

		$orderby_final = "";
		$orderby_flag = 0 ;

		foreach ($orderby_array as $key)
		{
			$field_orderby = explode(" ", $key);
			if(in_array($field_orderby[0], $field_array) && (strtoupper($field_orderby[1]) == 'ASC' || strtoupper($field_orderby[1]) == 'DESC' ))
			{
				$orderby_final = $orderby_final.",".$key;
				$orderby_flag = 1;
			}
		}

		$orderby_final = trim($orderby_final, ",");

		if($orderby_flag == 1)
			$orderby_final = "ORDER BY ".$orderby_final;

		//echo $orderby_final;die;
		$field_array_bitwise	= array('misc_parent_flag','misc_cat_flag','miscellaneous_flag','category_type','category_verticals','promt_ratings_flag','display_product_flag','category_addon');

		$final_columns_array 	= array();
		$final_columns_array 	= array_intersect($field_array,$return_array);
		$final_columns 		 	= implode(",",$final_columns_array);
		$final_condition 		= "";
		$final_condition 		= "";

		//print_r($where_condition);die;

		foreach ($where_condition as $key => $value) 
		{
			if(in_array($key,$field_array))
			{	
				if(substr($value,0,1)=="!")
				{

					$value = ltrim($value , "!");
					$value = str_replace(",", "','", $value);

					if(in_array($key,$field_array_bitwise))
						$final_condition = $final_condition." ".$key."&".$value. "!=". $value." AND";
					else
					{	
						$pos = strpos($value, ",");
						if($pos === false)
							$final_condition = $final_condition." ".$key." != '". $value. "' AND";
						else	
							$final_condition = $final_condition." ".$key." not in ('". $value. "') AND";
					}	
				}
				else
				{	
					$value = str_replace(",", "','", $value);
					if(in_array($key,$field_array_bitwise))
						$final_condition = $final_condition." ".$key."&".$value. "=". $value." AND";
					else
					{
						$pos = strpos($value, ",");
						if($pos === false)
						{
							if($key == 'catname_search_processed' && $table_name == "tbl_categorymaster_generalinfo" )
							{
								$b1				= $this->getSingular(strtolower(trim($value)));
								$b1_ws 			= str_replace(' ','',$b1);

								$final_condition = $final_condition." (category_name ='". $value. "' or catname_search_processed_ws ='". $b1_ws. "' or catname_search_processed ='". $b1. "') AND";
							}
							elseif($key == "parent_catid_lineage" || $key == "parent_nationalcatid_lineage" || $key == "parentlineage" )
								$final_condition = $final_condition." ".$key." like '%/". $value."/%' AND";
							else
							{	
								$final_condition = $final_condition." ".$key." = '". $value. "' AND";
							}	
						}
						else	
							$final_condition = $final_condition." ".$key." in ('". $value. "') AND";
					}	
				}
			}		
		}
		$final_condition = 'Where'.trim($final_condition,'AND');

		if($final_condition == "Where")
			$final_condition = "";

		//echo $final_condition;die;

		if($limit > 0)
			$limit_condition = "LIMIT ".$limit;
		else
			$limit_condition = "";


		if($final_condition != "")
		{
			$sql = "select distinct ".$final_columns." from ".$table_name." ".$final_condition." ".$orderby_final." ".$limit_condition;

		}

		//echo $sql; die;
		$sql = "/*CATSQL */".$sql;
		$res 			= parent::execQuery($sql, $this->dbConDjds);
		if($res===false){
			$sql .= "(".__LINE__.")";			
			$sql_err_log =	"INSERT INTO tbl_catapi_err_sql SET 
						 failed_sql 	= '".addslashes(stripslashes($sql))."',
						 curl_params 	= '".json_encode($this->params)."',						
						 updatedOn		= NOW()";
			$res_err_log = parent::execQuery($sql_err_log,$this->conn_iro);
		} 
		//die;
		$numrows 		= mysql_num_rows($res);
		//print_r($numrows);die;
		if(DEBUG_MODE==1)
		{
			echo '<br>';
			echo '<pre>Server info ->';print_r($this->dbConDjds);
			echo '<br>sql ->'.$sql;
		}
		$i = 0;
		if($numrows>0)
		{
			while($row = mysql_fetch_assoc($res))
			{
				foreach($final_columns_array as $key)
				{
					$data[$i][$key]			= $row[$key];
				}
				$i++;
			}
		}
		//print_r($data);die;

		if(DEBUG_MODE==1)
		{
			echo '<br>Data set : ';
			print_r($data);
		}

		if($i>0)
		{
			$results_array['results'] = $data;
			$results_array['totalresults'] = $i;
			$results_array['errorcode'] = '0';
			$results_array['msg'] = '';
		}
		else
		{
			$data = array();
			$results_string = '';
			$results_array['results'] = $data;
			$results_array['errorcode'] = '2';
			$results_array['msg'] = 'Result not found';
		}
		$return_result = json_encode($results_array);
		unset($obj);
		return $return_result;
	}

	
	function get_catgory_details_scase($return, $city, $where, $orderby , $limit, $q_type , $scase)
	{
		if(DEBUG_MODE==1)
		{
			echo '<br>';
			echo '<br> Function call -->  get_catgory_details_scase';
			echo '<br> city -->'.$city;
			echo '<br> where -->'.$where;
			echo '<br> orderby -->'.$orderby;
			echo '<br> limit -->'.$limit;
			echo '<br> scase -->'.$scase;
		}

		if($q_type == "parentinfo")
		{
			$table_name = "tbl_categorymaster_parentinfo";
		}
		else
		{
			$table_name = "tbl_categorymaster_generalinfo";
		}

		$array_city 	= array('MUMBAI','DELHI','BANGALORE','KOLKATA','CHENNAI','HYDERABAD','PUNE','AHMEDABAD');
		$cnames 		= '"'.str_replace(',', '","', $cnames).'"';
		if($return == "" || $return == null)
			$return = 'national_catid,category_name';
		else
		{
			if(strpos($return,'national_catid') !== false)
			{
				$return = $return;
			}
			else
				$return = 'national_catid'.",".$return;
		}
		$where_condition   		= json_decode($where,true);
		if(!empty($where_condition))
			$where_array 			= array_keys($where_condition);
		$return_array			= explode("," ,$return);

		if($q_type == "parentinfo")
		{
			$field_array = array("national_catid","parent_national_catid","catid","parent_catid","category_name","child_count","parent_flag","parent_catid_lineage","parent_nationalcatid_lineage","parentlineage","category_depth","callcount","callcount_rolling","category_earning","category_contribution","callcnt_avg","parent_callcount","parent_callcnt_avg","parent_callcount_rolling","parent_filter_flag","parent_filter_callcnt_avg","parent_filter_callcnt_rolling","parent_category_earning","brand_parent_filter_flag","brand_parent_filter_callcnt_avg","brand_parent_filter_callcnt_rolling","mask_status","original_creator","original_date","updated_by","updated_on","backend_uptdate","callcount_3month","parent_filter_callcount_3month","multiparentage_id","auto_id","parent_reach_count","parent_reach_count_avg","parent_reach_count_rolling");
		}
		else
		{	
			$field_array	=	array("national_catid","catid","category_name","table_no","service_name","brand_name","total_results","search_type","category_type","category_source","business_flag","category_verticals","biddable_type","category_addon","paid_clients","nonpaid_clients","catlookup_flag","catlookup_script","mask_status","isdeleted","category_position","categoryname_display","prompt_categoryname_display","display_as_filter","associate_national_catid","parent_filter_flag","promt_ratings_flag","display_product_flag","display_flag","force_display","regionlist","g_popularity","avg_cpc","callcount","callcount_rolling","category_earning","category_contribution","callcnt_avg","mail_authorised","smsfeedback_flag","competitor_details_flag","other_flag","deduction_buffer_days","city_count","noduplicatecheck","syncount","category_weight","budgeting_parent_catid","budgeting_parent_catname","budgeting_parent_contribution","catname_search","catname_search_processed","catname_search_processed_ws","catname_search_ignore_processed","catname_search_ignore_processed_ws","movie_rating","movie_censor_rating","movie_release_date","original_creator","original_date","updated_by","updated_on","backend_uptdate","active_flag","event_end_date","user_rating_flag","category_scope","top_category_flag","partial_inventory","bd_caption_id","number_masking","category_guarantee","add_bform_flag","premium_flag","rating_productname","group_id","local_listing_flag","bestdealFlag","bd_msg_id","citylineage","citylineage_ws","sort_photos_tag","photo_tag","is_restricted","emergency_type","video_id","business_tag","brand_filter_flag","block_for_sale","lock_for_edit","new_logic","budget_type","total_hidden_results","movie_show_count","video_flag","cuisine_name","sub_category_type","entity_name","entity_flag","entity_id","c2l_strip_word","assoc_flag","brand_flag","premium_source","premium_comment","display_product_preference","show_button_count","new_release","universal_flow","universal_relevent_count","rest_price_range","popular_cat","top_category_slab","master_brand_name","auto_suggest_flag","filter_callcount_rolling","filter_callcount","misc_parent_flag","image_path","callcount_3month","filter_callcount_3month","hk_flag","sub_display_product_flag","feedback_count","layer_type","image_present","image_id","movie_ncatid","misc_cat_flag","source","area_flow_type","search_flow_type","group_master_id","ods_dname","template_id","hk_desc","bidvalue","bredcrum_parent","ods_ncatid","attribute_group","c2l_cat_map","c2l_script_title","display_product_abbr","category_ranking","bfc_bifurcation_flag","reach_count","reach_count_rolling","reach_count_avg","reach_count_3month","filter_reach_count","filter_reach_count_rolling","category_conversion_flag","priority_flag","category_description","ad_id","comp_lookup_scriptid","mapped_attribute","auth_flag","auth_gen_ncatid","miscellaneous_flag","cuisine_tag","social_interest_id","cat_mapped_attr","merge_flag","nm_flag","callcount_original","callcount_rolling_original","reach_count_original","reach_count_rolling_original","subscript","tag_along_flag","tag_national_catid","tag_catname_display","tag_ncid_position","category_adwords","featured_on_flag","fp_max_cnt");
		}

		
		//echo $orderby_final;die;
		$field_array_bitwise	= array('misc_parent_flag','misc_cat_flag','miscellaneous_flag','category_type','category_verticals','promt_ratings_flag','display_product_flag','category_addon');

		$final_columns_array 	= array();
		$final_columns_array 	= array_intersect($field_array,$return_array);
		$final_columns 		 	= implode(",",$final_columns_array);
		$final_condition 		= "";
		$final_condition 		= "";

		//print_r($where_condition);die;
		if(!empty($where_condition))
		{
			foreach ($where_condition as $key => $value) 
			{
				if(in_array($key,$field_array))
				{	
					if(substr($value,0,1)=="!")
					{

						$value = ltrim($value , "!");
						$value = str_replace(",", "','", $value);

						if(in_array($key,$field_array_bitwise))
							$final_condition = $final_condition." ".$key."&".$value. "!=". $value." AND";
						else
						{	
							$pos = strpos($value, ",");
							if($pos === false)
								$final_condition = $final_condition." ".$key." != '". $value. "' AND";
							else	
								$final_condition = $final_condition." ".$key." not in ('". $value. "') AND";
						}	
					}
					else
					{	
						$value = str_replace(",", "','", $value);
						if(in_array($key,$field_array_bitwise))
							$final_condition = $final_condition." ".$key."&".$value. "=". $value." AND";
						else
						{
							$pos = strpos($value, ",");
							if($pos === false)
							{
								if($key == 'category_name' && $table_name == "tbl_categorymaster_generalinfo" )
								{
									$b1				= $this->getSingular(strtolower(trim($value)));
									$b1_ws 			= str_replace(' ','',$b1);

									if($scase == 2)
									{
										$catname = $value;

										$catname_search				= trim(preg_replace('#\(.*\)#','',$this->sanitize($value)));
										$catname_search_processed	= $this->catFilter($this->getSingular($catname_search));
									}
									elseif($scase == 4)
									{
										$catname = $value;
									}

									else
									{
										$final_condition = $final_condition." (category_name ='". $value. "' or catname_search_processed_ws ='". $b1_ws. "' or catname_search_processed ='". $b1. "') AND";
									}
								}
								elseif($key == "movie_release_date" && $scase ==4 )
								{
									$final_condition = $final_condition." AND movie_release_date>=DATE_SUB('".$value."' ,INTERVAL 1 MONTH) AND  movie_release_date<=DATE_ADD('".$value."',INTERVAL 10 DAY) AND";
								}
								elseif($key == "parent_catid_lineage" || $key == "parent_nationalcatid_lineage" || $key == "parentlineage" )
									$final_condition = $final_condition." ".$key." like '%/". $value."/%' AND";
								elseif(($scase == 1 || $scase == 3)  && $key == "catid")
								{
									$final_condition = $final_condition." ".$key." = '". $value. "' AND";
									$catid = $value;
								}
								elseif($scase == 2 && $key == "catname_search_processed")
								{
									$catname_search_processed = $value;
								}
								else if($scase == 2 && $key == "national_catid")
								{
									$national_catid = $value;
								}
								else
								{	
									$final_condition = $final_condition." ".$key." = '". $value. "' AND";
								}	
							}
							else
							{
								if(($scase == 1 || $scase == 3)  && $key == "catid")
								{
									$catid = $value;
								}	
								$final_condition = $final_condition." ".$key." in ('". $value. "') AND";
							}
						}	
					}
				}		
			}
		}


		$final_condition = trim($final_condition,'AND');

		//echo $catid;die;

		$catid 			= str_replace("'", "", $catid);

		if($scase == 1)
		{
			$orderby_final 	= "ORDER BY field(catid,".$catid.")";
		}
		else
		{
			$orderby_array = explode(',', $orderby);

			$orderby_final = "";
			$orderby_flag = 0 ;

			foreach ($orderby_array as $key)
			{
				$field_orderby = explode(" ", $key);
				if(in_array($field_orderby[0], $field_array) && (strtoupper($field_orderby[1]) == 'ASC' || strtoupper($field_orderby[1]) == 'DESC' ))
				{
					$orderby_final = $orderby_final.",".$key;
					$orderby_flag = 1;
				}
			}

			$orderby_final = trim($orderby_final, ",");

			if($orderby_flag == 1)
				$orderby_final = "ORDER BY ".$orderby_final;
		}

		//echo $orderby_final;die;
		//echo $final_condition;die;

		if($limit > 0)
			$limit_condition = "LIMIT ".$limit;
		else
			$limit_condition = "";


		if($scase == 2)
		{

			$final_condition = 'AND'.trim($final_condition,'AND');

			if($final_condition == "AND")
				$final_condition = "";

			$catname_search_processed_array = explode(" ", strtolower(trim($catname_search_processed)));

			$ignore_words = array("value","a","about","an","are","as","at","be","by","com","de","en","for","from","how","i","in","is","it","la","of","on","or","that","the","this","to","was","what","when","where","who","will","with","und","www");

			foreach ($catname_search_processed_array as $key) 
			{
				if(strlen($key) > 2)
					$cnameNew[] = $key;
			}

			if($national_catid == null || $national_catid == '')
			{
				$national_catid = 0;
			}

			if($cnameNew != '')
			{
				$catname_new 	= implode(" +" , array_diff($cnameNew, $ignore_words));
				$catname_new	= $this->catFilter($this->getSingular($catname_new));
			}
			
			if($catname_new != "")
			{
				$sql = "SELECT catid, national_catid, category_name AS catname, biddable_type AS cat_type,  callcount, IF(category_type&64=64,1,0) AS block_for_contract, IF(category_type&16=16,1,0) AS exclusive, premium_flag, IF(catname_search_processed='".$catname_search_processed."' or national_catid = ".$national_catid.",1,0) AS exact_match, display_as_filter AS filter_flag, associate_national_catid AS fnid,search_type,business_flag,bfc_bifurcation_flag AS mrg,reach_count,IF(miscellaneous_flag&16=16,1,0) AS chain_out FROM tbl_categorymaster_generalinfo 
						WHERE MATCH(catname_search_processed) AGAINST('+".str_replace(" ", " +", $catname_new)."' IN BOOLEAN MODE) ".$final_condition." AND category_name NOT LIKE '%jdyp%' union 

					SELECT catid, national_catid, category_name AS catname, biddable_type AS cat_type,  callcount, IF(category_type&64=64,1,0) AS block_for_contract, IF(category_type&16=16,1,0) AS exclusive, premium_flag, IF(catname_search_processed='".$catname_search_processed."' or national_catid = ".$national_catid.",1,0) AS exact_match, display_as_filter AS filter_flag, associate_national_catid AS fnid,search_type,business_flag,bfc_bifurcation_flag AS mrg,reach_count,IF(miscellaneous_flag&16=16,1,0) AS chain_out FROM tbl_categorymaster_generalinfo 
					WHERE national_catid = ".$national_catid." ".$final_condition." AND category_name NOT LIKE '%jdyp%' ORDER BY exact_match DESC ,callcount DESC, catname ASC ".$limit_condition ;

			}
			else
			{
				$sql = "SELECT catid, national_catid, category_name AS catname, biddable_type AS cat_type,  callcount, IF(category_type&64=64,1,0) AS block_for_contract, IF(category_type&16=16,1,0) AS exclusive, premium_flag, IF(catname_search_processed='".$catname_search_processed."' or national_catid = ".$national_catid.",1,0) AS exact_match, display_as_filter AS filter_flag, associate_national_catid AS fnid,search_type,business_flag,bfc_bifurcation_flag AS mrg,reach_count,IF(miscellaneous_flag&16=16,1,0) AS chain_out FROM tbl_categorymaster_generalinfo 
					WHERE national_catid = ".$national_catid." ".$final_condition." AND category_name NOT LIKE '%jdyp%' ORDER BY exact_match DESC ,callcount DESC, catname ASC ".$limit_condition ;
			}
		}
		elseif($scase == 3)
		{
			$final_condition = 'Where'.trim($final_condition,'AND');

			if($final_condition == "Where")
				$final_condition = "";

			$sql = "SELECT parentlineage,GROUP_CONCAT(DISTINCT category_name SEPARATOR '|~|') AS catnamelist, GROUP_CONCAT(DISTINCT catid SEPARATOR '|~|') AS catidlist,TRIM(BOTH '/' from substring_index(parentlineage,'/',2)) AS parentage,TRIM(BOTH '/' from substring_index(parent_catid_lineage,'/',2)) AS parentcatid FROM tbl_categorymaster_parentinfo WHERE catid IN (".$catid.") GROUP BY parentage HAVING (parentage!='' AND parentage!='/' AND parentage !='B2B' AND parentage !='RECYCLE BIN (P)' AND parentage !='B2C Products' AND parentage !='B2C Services')";
		}
		elseif($scase == 4)
		{
			$ignore_words = array("value","a","about","an","are","as","at","be","by","com","de","en","for","from","how","i","in","is","it","la","of","on","or","that","the","this","to","was","what","when","where","who","will","with","und","www","3d","4d","5d","6d","7d","8d","9d");

			//print_r($ignore_words);die;

			$catnameArray = explode(" ", strtolower(trim($catname)));

			//print_r($catnameArray);die;

			$cond_3d = "";

			foreach ($catnameArray as $key) 
			{
				if(strlen($key) > 2)
					$cnameNew[] = $key;
				else
				{
					$cond_3d = $cond_3d. "AND category_name like '%".$key."%' ";
				}
			}

			//print_r($cnameNew);die;

			/*if(in_array("3d", $cnameNew))
			{
				$cond_3d = "AND category_name like '%3d%' ";
			}*/

			//print_r($catnameArray);die;

			$catname_new = implode(" +" , array_diff($cnameNew, $ignore_words));

			//echo $catname_new;die;

			$catname_search_processed	= $this->catFilter($this->getSingular($catname_new));

			$catname_search_processed = str_replace(" ", " +", $catname_search_processed);


			$sql = "select catid,category_name,national_catid ,movie_release_date from ".$table_name." where match(category_name) AGAINST('+".$catname_new."' in boolean mode) ".$cond_3d." and category_verticals &8=8 ".$final_condition." union all 
			select catid,category_name,national_catid ,movie_release_date from ".$table_name." where match(catname_search_processed) AGAINST('+".$catname_search_processed."' in boolean mode) ".$cond_3d." and category_verticals &8=8 ".$final_condition." order by length(category_name) asc ".$limit_condition;

			//echo $sql;die;
		}
		else
		{
			$final_condition = 'Where'.trim($final_condition,'AND');

			if($final_condition == "Where")
				$final_condition = "";

			if($final_condition != "")
				$sql = "select distinct ".$final_columns." from ".$table_name." ".$final_condition." ".$orderby_final." ".$limit_condition;

		}

		//echo $sql; die;

		if($scase == 2)
		{
			$final_columns_array = array("catid","national_catid","catname","cat_type","callcount","block_for_contract","exclusive","premium_flag","exact_match","filter_flag","fnid","search_type","business_flag","mrg","reach_count","chain_out");
		}
		if($scase == 3)
		{
			$final_columns_array = array("parentlineage","catnamelist","catidlist","parentage","parentcatid");
		}
		$sql = "/*CATSQL */".$sql;
		$res 			= parent::execQuery($sql, $this->dbConDjds);
		if($res===false){
			$sql .= "(".__LINE__.")";			
			$sql_err_log =	"INSERT INTO tbl_catapi_err_sql SET 
						 failed_sql 	= '".addslashes(stripslashes($sql))."',
						 curl_params 	= '".json_encode($this->params)."',						
						 updatedOn		= NOW()";
			$res_err_log = parent::execQuery($sql_err_log,$this->conn_iro);
		} 
		//die;
		$numrows 		= mysql_num_rows($res);
		//print_r($numrows);die;
		if(DEBUG_MODE==1)
		{
			echo '<br>';
			echo '<pre>Server info ->';print_r($this->dbConDjds);
			echo '<br>sql ->'.$sql;
		}
		$i = 0;
		if($numrows>0)
		{
			while($row = mysql_fetch_assoc($res))
			{
				foreach($final_columns_array as $key)
				{
					$data[$i][$key]			= $row[$key];
				}
				$i++;
			}
		}
		//print_r($data);die;

		if(DEBUG_MODE==1)
		{
			echo '<br>Data set : ';
			print_r($data);
		}

		if($i>0)
		{
			$results_array['results'] = $data;
			$results_array['totalresults'] = $i;
			$results_array['errorcode'] = '0';
			$results_array['msg'] = '';
		}
		else
		{
			$data = array();
			$results_string = '';
			$results_array['results'] = $data;
			$results_array['errorcode'] = '2';
			$results_array['msg'] = 'Result not found';
		}
		$return_result = json_encode($results_array);
		unset($obj);
		return $return_result;
	}

	function get_similar_business_category($city, $catid)
	{
		if(DEBUG_MODE==1)
		{
			echo '<br>';
			echo '<br> Function call -->  get_catgory_details';
			echo '<br> city -->'.$city;
			echo '<br> catid -->'.$catid;

		}

		
		$sql = "call sp_similar_business_category('".$catid."')";

		
		//echo $sql; die;
		$sql = "/*CATSQL */".$sql;
		$res 			= parent::execQuery($sql, $this->dbConDjds);
		if($res===false){
			$sql .= "(".__LINE__.")";			
			$sql_err_log =	"INSERT INTO tbl_catapi_err_sql SET 
						 failed_sql 	= '".addslashes(stripslashes($sql))."',
						 curl_params 	= '".json_encode($this->params)."',						
						 updatedOn		= NOW()";
			$res_err_log = parent::execQuery($sql_err_log,$this->conn_iro);
		} 
		//die;
		$numrows 		= mysql_num_rows($res);
		//print_r($numrows);die;
		if(DEBUG_MODE==1)
		{
			echo '<br>';
			echo '<pre>Server info ->';print_r($this->dbConDjds);
			echo '<br>sql ->'.$sql;
		}
		$i = 0;
		if($numrows>0)
		{
			while($row = mysql_fetch_assoc($res))
			{
				$data['catid']			= $row['bidcats'];		
				$i++;
			}
		}
		//print_r($data);die;

		if(DEBUG_MODE==1)
		{
			echo '<br>Data set : ';
			print_r($data);
		}

		if($i>0)
		{
			$results_array['results'] = $data;
			// /$results_array['totalresults'] = $i;
			$results_array['errorcode'] = '0';
			$results_array['msg'] = '';
		}
		else
		{
			$data = array();
			$results_string = '';
			$results_array['results'] = $data;
			$results_array['errorcode'] = '2';
			$results_array['msg'] = 'Result not found';
		}
		$return_result = json_encode($results_array);
		unset($obj);
		return $return_result;
	}


	private function getSingular($str = '')
	{
		$s = array();
		$t = explode(' ',$str);
		$e = array('shoes'=>'shoe','shoe'=>'shoes',
					'glasses'=>'glass','glass'=>'glasses',
					'mattresses'=>'mattress','mattress'=>'mattresses',
					'watches'=>'watch','watch'=>'watches',
					'classes'=>'class','class'=>'classes');
		$r = array('ss'=>false,'os'=>'o','ies'=>'y','xes'=>'x','oes'=>'o','ies'=>'y','ves'=>'f','s'=>'');
		foreach($t as $v){
			if(strlen($v)>=4)
			{
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
		return implode(' ',$s);
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






function sanitize($str)
{
	$str = preg_replace('/[@&-.,_)(\s+]+/',' ',$str);
	$str = preg_replace('/\\\+/i',' ',$str);
	$str = preg_replace("/[^a-zA-Z0-9\s]+/",'',$str);
	$str = preg_replace('/\s\s+/',' ',$str);
	return trim(strtolower($str));
}
/*

function sanitize_new($str)
	{
		$str = str_replace("_G","",$str);
		$str = preg_replace('/[@&-,\/)(\s+]+/','',$str);
		$str = preg_replace("/[^a-zA-Z0-9\s]+/",'',$str);
		$str = preg_replace('/\\\+/i','',$str);
		$str = preg_replace('/\s\s+/',' ',$str);
		return trim($str);
	}
*/

}

?>
