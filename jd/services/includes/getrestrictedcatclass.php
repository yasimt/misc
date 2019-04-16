<?php
/**
 * Filename : getrestrictedcatclass.php
 * Date		: 2018-09-04
 * Author	: Apoorv
 * Purpose	: get Restricted Category
 
 * */
class getrestrictedCatClass extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $Idc	    	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	var  $usercode		= null;
	

	var  $module	= null;
	var  $data_city	= null;
	var  $ModuleVersion=null;
	function __construct($params)
	{		
		$this->params = $params;				

		if(trim($this->params['parentid']) != "")
		{
			$this->parentid  = $this->params['parentid']; //initialize paretnid
		}else
		{
			{echo json_encode('Please provide parentid'); exit; }
		}
		
		if(trim($this->params['module']) != "" && $this->params['module'] != null)
		{
			$this->module  = strtolower($this->params['module']); //initialize module
		}else
		{
			$errorarray['errormsg']='module missing';
			echo json_encode($errorarray); exit;
		}
		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='data_city missing';
			echo json_encode($errorarray); exit;
		}
		
		
		//mongo
		
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		
		$this->setServers();
		$this->categoryClass_obj = new categoryClass();
		//echo json_encode('const'); exit;
	}
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->dbConDjds  		= $db[$data_city]['d_jds']['master'];
		$this->Idc   			= $db[$data_city]['idc']['master'];
		$this->fin   			= $db[$data_city]['fin']['master'];
		$this->tme_jds   		= $db[$data_city]['tme_jds']['master'];
		$this->dbConbudget  	= $db[$data_city]['db_budgeting']['master'];


		
		//echo "<pre>"; print_r($this->Idc);
		
		switch($this->module)
		{
			case 'cs':
			$this->tempconn = $this->fin;
			break;
			
			case 'tme':
			$this->tempconn = $this->tme_jds;
			$this->mongo_tme = 1;
			

			break;

			case 'me':
			$this->tempconn = $this->Idc;
			
			
			
			break;
		}	
	}
	
	function getCategoryList(){
		$temp_catlin_arr 	= 	array();
		
		$tempData = array();
		$mongo_inputs = array();
		$mongo_inputs['parentid'] 	= $this->parentid;
		$mongo_inputs['data_city'] 	= $this->data_city;
		$mongo_inputs['module']		= $this->module;
		$mongo_inputs['table'] 		= "tbl_business_temp_data";
		$mongo_inputs['fields'] 	= "catIds,parentid";
		$tempData = $this->mongo_obj->getData($mongo_inputs);
		
		if(count($tempData)>0){
			$temp_catlin_arr  	=   explode('|P|',$tempData['catIds']);
			$temp_catlin_arr 	= 	array_filter($temp_catlin_arr);
			$temp_catlin_arr 	= 	$this->getValidCategories($temp_catlin_arr);
			
		}
		
		return $temp_catlin_arr;
	}
	
	function getRestrictedCats(){
		$respArr = array();
		$catinfoArr = $this->getCategoryList();
		$restrictedCatIdstr = "";
		$restrictedCatNamestr = "";
		$restric_cat_arr = array();		
		$restricted_cat_exists = false;
		
		$_24_hrs_catid_str = "";
		$_24_hrs_catName_str = "";
		$_24_hrs_arr = array();		
		$is_24_hrs = false;
		
		$respArr['error']['code'] = 0;
		$respArr['error']['status'] = 'Resticted Category Not Found';
		$respArr['error']['status_24_hrs'] = '24 hrs Category Not Found';
		$respArr['result']['isRestricted'] = '0';
		$respArr['result']['restricted'] = $restric_cat_arr;
		$respArr['result']['is_24_hrs'] = '0';
		$respArr['result']['24_hrs'] = $_24_hrs_arr;
		
		if( !empty($catinfoArr) ){
			
			$catids_str = implode("','",$catinfoArr);
			//$sql_restricted_catids = "SELECT national_catid,catid,category_name,table_no,service_name,brand_name,total_results,search_type,category_type,category_source,business_flag,category_verticals,biddable_type,category_addon,paid_clients,nonpaid_clients,catlookup_flag,catlookup_script,mask_status,isdeleted,category_position,categoryname_display,prompt_categoryname_display,display_as_filter,associate_national_catid,parent_filter_flag,promt_ratings_flag,display_product_flag,display_flag,force_display,regionlist,g_popularity,avg_cpc,callcount,callcount_rolling,category_earning,category_contribution,callcnt_avg,mail_authorised,smsfeedback_flag,competitor_details_flag,other_flag,deduction_buffer_days,city_count,noduplicatecheck,syncount,category_weight,budgeting_parent_catid,budgeting_parent_catname,budgeting_parent_contribution,catname_search,catname_search_processed,catname_search_processed_ws,catname_search_ignore_processed,catname_search_ignore_processed_ws,movie_rating,movie_censor_rating,movie_release_date,original_creator,original_date,updated_by,updated_on,backend_uptdate,active_flag,event_end_date,user_rating_flag,category_scope,top_category_flag,partial_inventory,bd_caption_id,number_masking,category_guarantee,add_bform_flag,premium_flag,rating_productname,group_id,local_listing_flag,bestdealFlag,bd_msg_id,citylineage,citylineage_ws,sort_photos_tag,photo_tag,is_restricted,emergency_type,video_id,business_tag,brand_filter_flag,block_for_sale,lock_for_edit,new_logic,budget_type,total_hidden_results,movie_show_count,video_flag,cuisine_name,sub_category_type,entity_name,entity_flag,entity_id,c2l_strip_word,assoc_flag,brand_flag,premium_source,premium_comment,display_product_preference,show_button_count,new_release,universal_flow,universal_relevent_count,rest_price_range,popular_cat,top_category_slab,master_brand_name,auto_suggest_flag,filter_callcount_rolling,filter_callcount,misc_parent_flag,image_path,callcount_3month,filter_callcount_3month,hk_flag,sub_display_product_flag,feedback_count,layer_type,image_present,image_id,movie_ncatid,misc_cat_flag,source,area_flow_type,search_flow_type,group_master_id,ods_dname,template_id,hk_desc,bidvalue,bredcrum_parent,ods_ncatid,attribute_group,c2l_cat_map,c2l_script_title,display_product_abbr,category_ranking,bfc_bifurcation_flag,reach_count,reach_count_rolling,reach_count_avg,reach_count_3month,filter_reach_count,filter_reach_count_rolling,category_conversion_flag,priority_flag,category_description,ad_id,comp_lookup_scriptid,mapped_attribute,auth_flag,auth_gen_ncatid,miscellaneous_flag,cuisine_tag,social_interest_id,cat_mapped_attr,merge_flag,nm_flag,callcount_original,callcount_rolling_original,reach_count_original,reach_count_rolling_original,subscript,tag_along_flag,tag_national_catid,tag_catname_display,tag_ncid_position,category_adwords,featured_on_flag from tbl_categorymaster_generalinfo where catid IN ('".$catids_str."')";
			
			//$res_sql_restricted_catids 	= parent::execQuery($sql_restricted_catids, $this->dbConDjds);
			$cat_params = array();
			$cat_params['page']= 'getrestrictedcatclass';
			$cat_params['data_city'] 	= $this->data_city;										
			$cat_params['return']		= 'national_catid,catid,category_name,table_no,service_name,brand_name,total_results,search_type,category_type,category_source,business_flag,category_verticals,biddable_type,category_addon,paid_clients,nonpaid_clients,catlookup_flag,catlookup_script,mask_status,isdeleted,category_position,categoryname_display,prompt_categoryname_display,display_as_filter,associate_national_catid,parent_filter_flag,promt_ratings_flag,display_product_flag,display_flag,force_display,regionlist,g_popularity,avg_cpc,callcount,callcount_rolling,category_earning,category_contribution,callcnt_avg,mail_authorised,smsfeedback_flag,competitor_details_flag,other_flag,deduction_buffer_days,city_count,noduplicatecheck,syncount,category_weight,budgeting_parent_catid,budgeting_parent_catname,budgeting_parent_contribution,catname_search,catname_search_processed,catname_search_processed_ws,catname_search_ignore_processed,catname_search_ignore_processed_ws,movie_rating,movie_censor_rating,movie_release_date,original_creator,original_date,updated_by,updated_on,backend_uptdate,active_flag,event_end_date,user_rating_flag,category_scope,top_category_flag,partial_inventory,bd_caption_id,number_masking,category_guarantee,add_bform_flag,premium_flag,rating_productname,group_id,local_listing_flag,bestdealFlag,bd_msg_id,citylineage,citylineage_ws,sort_photos_tag,photo_tag,is_restricted,emergency_type,video_id,business_tag,brand_filter_flag,block_for_sale,lock_for_edit,new_logic,budget_type,total_hidden_results,movie_show_count,video_flag,cuisine_name,sub_category_type,entity_name,entity_flag,entity_id,c2l_strip_word,assoc_flag,brand_flag,premium_source,premium_comment,display_product_preference,show_button_count,new_release,universal_flow,universal_relevent_count,rest_price_range,popular_cat,top_category_slab,master_brand_name,auto_suggest_flag,filter_callcount_rolling,filter_callcount,misc_parent_flag,image_path,callcount_3month,filter_callcount_3month,hk_flag,sub_display_product_flag,feedback_count,layer_type,image_present,image_id,movie_ncatid,misc_cat_flag,source,area_flow_type,search_flow_type,group_master_id,ods_dname,template_id,hk_desc,bidvalue,bredcrum_parent,ods_ncatid,attribute_group,c2l_cat_map,c2l_script_title,display_product_abbr,category_ranking,bfc_bifurcation_flag,reach_count,reach_count_rolling,reach_count_avg,reach_count_3month,filter_reach_count,filter_reach_count_rolling,category_conversion_flag,priority_flag,category_description,ad_id,comp_lookup_scriptid,mapped_attribute,auth_flag,auth_gen_ncatid,miscellaneous_flag,cuisine_tag,social_interest_id,cat_mapped_attr,merge_flag,nm_flag,callcount_original,callcount_rolling_original,reach_count_original,reach_count_rolling_original,subscript,tag_along_flag,tag_national_catid,tag_catname_display,tag_ncid_position,category_adwords,featured_on_flag';	

			$where_arr  	=	array();			
			$where_arr['catid']			= implode(",",$catinfoArr);	
			$cat_params['where']		= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);

			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}
			if(count($cat_res_arr['results'])>0 && $cat_res_arr['errorcode']=='0'){
				
				foreach($cat_res_arr['results'] as $key =>$row_national_catids){
					if( $row_national_catids['is_restricted'] == 1 ){
						$restrictedCatIdstr .= $row_national_catids['catid']."||";
						$restrictedCatNamestr .= $row_national_catids['category_name']."||";
						$new_arr = array("catid"=>$row_national_catids['catid'],'catname'=>$row_national_catids['category_name']);
						$restric_cat_arr[] = $new_arr;
						$restricted_cat_exists = true;
					}
					
					if(((int)$row_national_catids['misc_cat_flag'] & 256) == 256) // 24 hours tagged category
					{
						$is_24_hrs			 = true;
						$_24_hrs_cat_id_arr .= $row_national_catids['catid']."||";
						$_24_hrs_catName_str .= $row_national_catids['category_name']."||";;
						$new_arr_24_hrs = array("catid"=>$row_national_catids['catid'],'catname'=>$row_national_catids['category_name']);
						$_24_hrs_arr[] = $new_arr_24_hrs;
					}
				}
				$restrictedCatIdstr = trim($restrictedCatIdstr,"||");
				$restrictedCatNamestr = trim($restrictedCatNamestr,"||");
				$respArr['error']['code'] = 0;
				if($restricted_cat_exists){
					$respArr['result']['isRestricted'] = '1';
					$respArr['error']['status'] = 'Resticted Category Found';
				}
				$respArr['result']['restricted'] = $restric_cat_arr;
				if($is_24_hrs){
					$respArr['result']['is_24_hrs'] = '1';
					$respArr['error']['status_24_hrs'] = '24 hrs Category Found';
				}
				$respArr['result']['24_hrs'] = $_24_hrs_arr;
			}
		}else{
			$respArr['error']['code'] = 1;
			$respArr['error']['status'] = 'Category Not Found';
		}
		//~ echo "<pre>";print_r($respArr);
		//~ die;
		return $respArr;  
	}
	
	function getValidCategories($total_catlin_arr){
		$final_catids_arr = array();
		if( (!empty($total_catlin_arr)) && (count($total_catlin_arr) > 0) ){
			foreach($total_catlin_arr as $catid){
				$final_catid = 0;
				$final_catid = preg_replace('/[^0-9]/', '', $catid);
				if(intval($final_catid)>0)
				{
					$final_catids_arr[]	= $final_catid;
				}
			}
			$final_catids_arr = array_merge(array_unique(array_filter($final_catids_arr)));
		}
		return $final_catids_arr;	
	}
}
