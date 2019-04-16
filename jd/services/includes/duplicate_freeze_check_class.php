<?php 

class duplicate_freeze_check_class extends DB
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
	
	
	function __construct($params)
	{	
		
		global $params;

 		$src_parentid 			= trim($params['src_parentid']);
 		$dist_parentid 			= trim($params['dist_parentid']);
		$module 				= trim($params['module']);
		$data_city 				= trim($params['data_city']); 	
		$check_array 			= trim($params['check_array']); 	
		$merging_process        = trim($params['merging_process']); 	
		$cron			        = trim($params['cron']); 	
		$empcode					= trim($params['ucode']);
 		/*if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->send_die_message($message));
            die();
        }*/
        

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


		//print_r(json_decode($check_array,true));
		$this->params 			= $params;
		$this->src_parentid  	= $src_parentid;
		$this->dist_parentid  	= $dist_parentid;
		$this->trace  			= $params['trace'];
		$this->data_city 		= $data_city;
		$this->module  	  		= strtoupper($module);
		$this->merging_process	= $merging_process;
		$this->cron				= $cron;
		$this->empcode			= $empcode;
		$this->check_array  	= json_decode($check_array,true);
		$this->setServers();
		$this->companyClass_obj = new companyClass();
		$this->categoryClass_obj = new categoryClass();		 
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		
		global $db;			
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_iro_slave	= $db[$conn_city]['iro']['slave'];
		$this->conn_fnc    		= $db[$conn_city]['fin']['master'];		
		$this->local    		= $db[$conn_city]['LOCAL']['master'];		
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
	}
	
	function duplicate_freeze_check()
	{
		
		$row_data_to_update = array();
		$difference_Arr = array();
		$cat_params = array();
		$cat_params['data_city']	= $this->data_city;
		$cat_params['table'] 		= 'gen_info_id,extra_det_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= $this->src_parentid;
		$cat_params['page'] 		= 'duplicate_freeze_check_class';
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['fields']		= 'parentid,landline,dialable_landline,landline_feedback,mobile,website,dialable_mobile,mobile_feedback,fax,tollfree,email,
							email_feedback,landline_display,dialable_landline_display,mobile_display,dialable_mobile_display,email_display,
							landline_addinfo,mobile_addinfo,tollfree_addinfo,othercity_number,catidlineage,catidlineage_nonpaid';

		$resTempCategory			= 	array();
		$resTempCategory			=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);
		

		if(!empty($resTempCategory) && $resTempCategory['errors']['code']==0){

			$dest_data_arr 		= $this->getDestinationData();
			$attributes_data	= $this->getAttributes();
			$source_attr_data   = $attributes_data['source_data'];
			$diffAttr     		= $attributes_data['diffAttr'];
			
			$row_data_to_update = $resTempCategory['results']['data'][$this->src_parentid];
			
			if(count($dest_data_arr)>0)
			{
				$field_array = array('catidlineage','catidlineage_nonpaid','landline','dialable_landline','landline_feedback','mobile','dialable_mobile',' mobile_feedback','fax','tollfree','email','email_feedback','landline_display','dialable_landline_display','mobile_display','dialable_mobile_display','email_display','landline_addinfo','mobile_addinfo','tollfree_addinfo','othercity_number','website', 'attributes');
				
				$field_mapped_array = array('catidlineage'=>'Categories','catidlineage_nonpaid'=>'Non-paid Categories','landline'=>'LandLine Numbers','dialable_landline'=>'Dialable Landlines','landline_feedback'=>'Landline Feedback Numbers','mobile'=>'Mobile Numbers','dialable_mobile'=>'Dialable Mobile Numbers',' mobile_feedback'=>'Mobile Feedback Numbers','fax'=>'Fax','tollfree'=>'Toll Free Numbers','email'=>'Email Ids','email_feedback'=>'Email Feedback','landline_display'=>'Landline Display Numbers','dialable_landline_display'=>'Dialable Landline Display Numbers','mobile_display'=>'Mobile Display Numbers','dialable_mobile_display'=>'Dialable Mobile Display Numbers','email_display'=>'Email Display','landline_addinfo'=>'Landline Addinfo','mobile_addinfo'=>'Mobile Addinfo','tollfree_addinfo'=>'Tollfree Addinfo','othercity_number'=>'Other City Numbers','website' => 'Website', 'attributes'=>'Attributes');
				
				foreach ($field_array as $field_name){					
					if($field_name!='attributes'){
						$new_data_arr = '';
						$new_data_arr = $this -> returnColumnUpdate($field_name,$row_data_to_update[$field_name],$dest_data_arr[$this->dist_parentid][$field_name]);
						
						if(count($new_data_arr)>0)
							{	$difference_Arr[$field_mapped_array[$field_name]] = $new_data_arr;	}
					}else{
						//~ echo "<br>field_name:-23-".$field_name;
						//~ echo "<pre>source_attr_data:--";print_r($source_attr_data);
						if(count($source_attr_data>0)){	
							$difference_Arr[$field_mapped_array[$field_name]] = $source_attr_data;	
							$difference_Arr['attr_diff'] 					  = $diffAttr;
						}
					}
					
					if(count($new_data_arr)>0 && $field_name!='attributes')
					{	$difference_Arr[$field_mapped_array[$field_name]] = $new_data_arr;	}
				}
				
				if(count($difference_Arr))
				{	return $difference_Arr;	}
			}
		}


		/*$sql_source_data_to_update = " SELECT  a.parentid,landline,dialable_landline,landline_feedback,mobile,dialable_mobile, mobile_feedback, fax, tollfree, email,
							email_feedback,landline_display, dialable_landline_display, mobile_display, dialable_mobile_display, email_display,
							landline_addinfo, mobile_addinfo, tollfree_addinfo, a.othercity_number, b.catidlineage, b.catidlineage_nonpaid
							FROM tbl_companymaster_generalinfo a JOIN tbl_companymaster_extradetails b
							ON a.parentid = b.parentid
							WHERE a.parentid = '".$this->src_parentid."' ";
		$res_source_data_to_update = parent::execQuery($sql_source_data_to_update, $this->conn_iro);
		if($res_source_data_to_update && mysql_num_rows($res_source_data_to_update))
		{
			
			$dest_data_arr 		= $this->getDestinationData();
			$row_data_to_update = mysql_fetch_assoc($res_source_data_to_update);
			
			if(count($dest_data_arr)>0)
			{
				$field_array = array('catidlineage','catidlineage_nonpaid','landline','dialable_landline','landline_feedback','mobile','dialable_mobile',' mobile_feedback','fax','tollfree','email','email_feedback','landline_display','dialable_landline_display','mobile_display','dialable_mobile_display','email_display','landline_addinfo','mobile_addinfo','tollfree_addinfo','othercity_number');
				
				$field_mapped_array = array('catidlineage'=>'Categories','catidlineage_nonpaid'=>'Non-paid Categories','landline'=>'LandLine Numbers','dialable_landline'=>'Dialable Landlines','landline_feedback'=>'Landline Feedback Numbers','mobile'=>'Mobile Numbers','dialable_mobile'=>'Dialable Mobile Numbers',' mobile_feedback'=>'Mobile Feedback Numbers','fax'=>'Fax','tollfree'=>'Toll Free Numbers','email'=>'Email Ids','email_feedback'=>'Email Feedback','landline_display'=>'Landline Display Numbers','dialable_landline_display'=>'Dialable Landline Display Numbers','mobile_display'=>'Mobile Display Numbers','dialable_mobile_display'=>'Dialable Mobile Display Numbers','email_display'=>'Email Display','landline_addinfo'=>'Landline Addinfo','mobile_addinfo'=>'Mobile Addinfo','tollfree_addinfo'=>'Tollfree Addinfo','othercity_number'=>'Other City Numbers');
				
				foreach ($field_array as $field_name)
				{
					$new_data_arr = '';
					$new_data_arr = $this -> returnColumnUpdate($field_name,$row_data_to_update[$field_name],$dest_data_arr[$this->dist_parentid][$field_name]);
					
					if(count($new_data_arr)>0)
					$difference_Arr[$field_mapped_array[$field_name]] = $new_data_arr;
				}
				
				if(count($difference_Arr))
				return $difference_Arr;

			}
		}*/
	}
	
	function getAttributes(){
		$source_data = array();
		$dest_data	 = array();
		$get_source_data = "SELECT attribute_name, attribute_id, attribute_value FROM tbl_companymaster_attributes WHERE parentid='".$this->src_parentid."'";		
		$res_source_data = parent::execQuery($get_source_data, $this->conn_iro);
		$attribute_id 	  = array();
		if($res_source_data && mysql_num_rows($res_source_data)>0){
			while($row_source_data = mysql_fetch_assoc($res_source_data)){
				$source_data[] = $row_source_data;
				if(!in_array($row_source_data['attribute_id'],$attribute_id)){
					array_push($attribute_id,$row_source_data['attribute_id']);
				}
			}
		}
		//~ echo "<pre>attribute_id:--";print_r($attribute_id);
		$source_data_values = array_values($source_data);		
		$diff_data          = array();
		$get_dest_data	 = "SELECT attribute_name, attribute_id, attribute_value FROM tbl_companymaster_attributes WHERE parentid='".$this->dist_parentid."' AND attribute_id IN ('".implode("','", $attribute_id)."')";		
		//echo $get_dest_data;
		$destinationAttr = array();
		$res_dest_data   = parent::execQuery($get_dest_data, $this->conn_iro);
		if($res_dest_data && mysql_num_rows($res_dest_data)>0){			
			while($row_dest_data = mysql_fetch_assoc($res_dest_data)){			
				$dest_data[] = 	$row_dest_data;
				if(!in_array($row_dest_data['attribute_id'],$destinationAttr)){
					array_push($destinationAttr,$row_dest_data['attribute_id']);
				}
			}
		}		
		
		foreach($source_data as $key=>$value){			
			if(in_array($value[attribute_id],$destinationAttr)){			
				unset($source_data[$key]);
			}
		}
		
		$source_data = array_merge($source_data);		
		$diffAttr = array_diff($attribute_id, $destinationAttr);
		$diffAttr = array_merge(array_filter(array_unique($diffAttr)));		
		$data= array();
		$data['source_data'] = $source_data;
		$data['diffAttr'	] = $diffAttr;
		return $data;
	}
	
	function getDestinationData()
	{

		$row_dest_data_to_update2 = array();
		$difference_Arr = array();
		$cat_params = array();
		$cat_params['data_city']	= $this->data_city;
		$cat_params['table'] 		= 'gen_info_id,extra_det_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= trim($this->dist_parentid);
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['page'] 		= 'duplicate_freeze_check_class';
		$cat_params['fields']		= 'parentid,companyname,sphinx_id,landline,dialable_landline,latitude,longitude,geocode_accuracy_level,landline_feedback,mobile,dialable_mobile,mobile_feedback,fax,tollfree,email,email_feedback,landline_display,dialable_landline_display,mobile_display,dialable_mobile_display,email_display,landline_addinfo,mobile_addinfo,tollfree_addinfo,othercity_number,catidlineage,catidlineage_nonpaid,data_city,payment_type,website,social_media_url,working_time_start,working_time_end,tag_catid,tag_catname';

		$resTempCategory			= 	array();
		$resTempCategory			=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

		if(!empty($resTempCategory) && $resTempCategory['errors']['code']==0){

			$row_dest_data_to_update2 = $resTempCategory['results']['data'][$this->dist_parentid];

			$dest_data_arr[$row_dest_data_to_update2['parentid']]['landline'] 		   = $row_dest_data_to_update2['landline'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['dialable_landline'] = $row_dest_data_to_update2['dialable_landline'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['landline_feedback'] = $row_dest_data_to_update2['landline_feedback'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['geocode_accuracy_level'] = $row_dest_data_to_update2['geocode_accuracy_level'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['mobile'] 		   = $row_dest_data_to_update2['mobile'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['latitude'] 		   = $row_dest_data_to_update2['latitude'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['longitude'] 		   = $row_dest_data_to_update2['longitude'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['mobile'] 		   = $row_dest_data_to_update2['mobile'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['dialable_mobile']   = $row_dest_data_to_update2['dialable_mobile'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['mobile_feedback']   = $row_dest_data_to_update2['mobile_feedback'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['fax'] 			   = $row_dest_data_to_update2['fax'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['tollfree']          = $row_dest_data_to_update2['tollfree'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['email']             = $row_dest_data_to_update2['email'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['email_feedback']    = $row_dest_data_to_update2['email_feedback'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['landline_display']  = $row_dest_data_to_update2['landline_display'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['mobile_display']    = $row_dest_data_to_update2['mobile_display'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['email_display']     = $row_dest_data_to_update2['email_display'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['landline_addinfo']  = $row_dest_data_to_update2['landline_addinfo'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['mobile_addinfo']    = $row_dest_data_to_update2['mobile_addinfo'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['tollfree_addinfo']  = $row_dest_data_to_update2['tollfree_addinfo'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['othercity_number']  = $row_dest_data_to_update2['othercity_number'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['data_city']  	   = $row_dest_data_to_update2['data_city'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['catidlineage']  	   = $row_dest_data_to_update2['catidlineage'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['companyname']  	   = $row_dest_data_to_update2['companyname'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['sphinx_id']  	   = $row_dest_data_to_update2['sphinx_id'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['catidlineage_nonpaid']  	   = $row_dest_data_to_update2['catidlineage_nonpaid'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['dialable_landline_display'] = $row_dest_data_to_update2['dialable_landline_display'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['dialable_mobile_display']   = $row_dest_data_to_update2['dialable_mobile_display'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['payment_type']   	= $row_dest_data_to_update2['payment_type'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['website']   	= $row_dest_data_to_update2['website'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['social_media_url']   	= $row_dest_data_to_update2['social_media_url'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['working_time_start']   	= $row_dest_data_to_update2['working_time_start'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['working_time_end']   	= $row_dest_data_to_update2['working_time_end'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['tag_catid']   		= $row_dest_data_to_update2['tag_catid'];
			$dest_data_arr[$row_dest_data_to_update2['parentid']]['tag_catname']	    = $row_dest_data_to_update2['tag_catname'];
			
			return $dest_data_arr;
		}

		
	 	/*$sql_dest_data_to_update2 = " SELECT  a.parentid,a.companyname,a.sphinx_id,landline,dialable_landline,latitude,longitude,geocode_accuracy_level,landline_feedback,mobile,dialable_mobile,
											mobile_feedback, fax, tollfree, email, email_feedback,landline_display, dialable_landline_display, mobile_display, dialable_mobile_display, email_display,landline_addinfo, mobile_addinfo, tollfree_addinfo, a.othercity_number, b.catidlineage, b.catidlineage_nonpaid, a.data_city
											FROM tbl_companymaster_generalinfo a JOIN tbl_companymaster_extradetails b
											ON a.parentid = b.parentid
											WHERE a.parentid IN ('".trim($this->dist_parentid)."')";
			
		$res_dest_data_to_update2 = parent::execQuery($sql_dest_data_to_update2, $this->conn_iro);
		if($res_dest_data_to_update2 && mysql_num_rows($res_dest_data_to_update2))
		{
			while($row_dest_data_to_update2 = mysql_fetch_assoc($res_dest_data_to_update2))
			{
				
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['landline'] 		   = $row_dest_data_to_update2['landline'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['dialable_landline'] = $row_dest_data_to_update2['dialable_landline'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['landline_feedback'] = $row_dest_data_to_update2['landline_feedback'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['geocode_accuracy_level'] = $row_dest_data_to_update2['geocode_accuracy_level'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['mobile'] 		   = $row_dest_data_to_update2['mobile'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['latitude'] 		   = $row_dest_data_to_update2['latitude'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['longitude'] 		   = $row_dest_data_to_update2['longitude'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['mobile'] 		   = $row_dest_data_to_update2['mobile'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['dialable_mobile']   = $row_dest_data_to_update2['dialable_mobile'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['mobile_feedback']   = $row_dest_data_to_update2['mobile_feedback'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['fax'] 			   = $row_dest_data_to_update2['fax'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['tollfree']          = $row_dest_data_to_update2['tollfree'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['email']             = $row_dest_data_to_update2['email'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['email_feedback']    = $row_dest_data_to_update2['email_feedback'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['landline_display']  = $row_dest_data_to_update2['landline_display'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['mobile_display']    = $row_dest_data_to_update2['mobile_display'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['email_display']     = $row_dest_data_to_update2['email_display'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['landline_addinfo']  = $row_dest_data_to_update2['landline_addinfo'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['mobile_addinfo']    = $row_dest_data_to_update2['mobile_addinfo'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['tollfree_addinfo']  = $row_dest_data_to_update2['tollfree_addinfo'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['othercity_number']  = $row_dest_data_to_update2['othercity_number'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['data_city']  	   = $row_dest_data_to_update2['data_city'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['catidlineage']  	   = $row_dest_data_to_update2['catidlineage'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['companyname']  	   = $row_dest_data_to_update2['companyname'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['sphinx_id']  	   = $row_dest_data_to_update2['sphinx_id'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['catidlineage_nonpaid']  	   = $row_dest_data_to_update2['catidlineage_nonpaid'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['dialable_landline_display'] = $row_dest_data_to_update2['dialable_landline_display'];
				$dest_data_arr[$row_dest_data_to_update2['parentid']]['dialable_mobile_display']   = $row_dest_data_to_update2['dialable_mobile_display'];

			}
			return $dest_data_arr;

		}*/
	}
	
		
	function returnColumnUpdate($key,$source_data,$dest_data)
	{
		if($source_data)
		{
			//echo $key;
			if(stristr($key,'_addinfo'))
			{
				//echo '<pre><br>';
				//echo '<br>' .$key.' :source:'.$source_data;
				//echo '<br>';
				//echo '<br>coming in hereee1111';
				$source_main_data_arr = explode('|~|',$source_data);
				foreach($source_main_data_arr as $source_main_data_addinfo)
				{
					$source_main_data_addinfo_arr = explode('|^|',$source_main_data_addinfo);
					if($source_main_data_addinfo_arr[0])
					{
						$source_mobile_addinfo_arr[$source_main_data_addinfo_arr[0]] = $source_main_data_addinfo_arr[1];
					}
				}
				//print_r($source_mobile_addinfo_arr);
				//echo '<pre><br>';
				//echo '<br>' .$key.':dest:'.$dest_data;
				//echo '<br>';
				$dest_mobile_addinfo_arr = array();
				$dest_main_data_arr = array();

				$dest_main_data_arr = explode('|~|',$dest_data);
				foreach($dest_main_data_arr as  $dest_main_data_addinfo)
				{
					$dest_main_data_addinfo_arr = explode('|^|',$dest_main_data_addinfo);
					if($dest_main_data_addinfo_arr[0])
					{
						$dest_mobile_addinfo_arr[] = $dest_main_data_addinfo_arr[0];
					}
				}


				$count_addinfo_diff_arr = array_diff(array_keys($source_mobile_addinfo_arr),$dest_mobile_addinfo_arr);
				$count_addinfo_diff_arr = array_filter($count_addinfo_diff_arr);
				$count_addinfo_diff_arr = array_unique($count_addinfo_diff_arr);
				//echo '<br> diff <pre>';
				//print_r($count_addinfo_diff_arr);
				//echo '<br> end diff <pre>';
				if(count($count_addinfo_diff_arr)>0)
				{
					foreach($count_addinfo_diff_arr as $diff_mobile)
					{
						if($diff_mobile)
						$new_dest_addinfo_arr [] = $diff_mobile.'|^|'.$source_mobile_addinfo_arr[$diff_mobile];
					}
					
					return $new_dest_addinfo_arr;

					//print_r($new_dest_addinfo_arr);

					/*if(count($new_dest_addinfo_arr) && $dest_data)
					{
						$dest_data = $dest_data."|~|".implode("|~|",$new_dest_addinfo_arr);
					}else if (count($new_dest_addinfo_arr)){
						$dest_data = implode("|~|",$new_dest_addinfo_arr);
					}

					if($dest_data)
					{
						$update_column 	   = $key."= '".$dest_data."',";
						return trim($update_column);
					}*/

				}


				//echo '<br><br> final dest data :: '.$dest_data;

				//echo '<br>';
				//print_r($dest_mobile_addinfo_arr);
				
			}
			else if (stristr($key,'catidlineage'))
			{
				 //echo '<br>coming in hereee1122222<pre>';
				// print_r($source_data);
				// echo '<br>';
				// print_r($dest_data);
				 $source_catidlineage_arr = array();$source_nonpaid_catidlineage_arr = array();
				 $dest_catidlineage_arr   = array();$dest_nonpaid_catidlineage_arr   = array();
				 $dest_catidlineage_final_arr   = array();$dest_nonpaid_catidlineage_final_arr   = array();
				 $diff_catidlineage_arr   = array();$diff_nonpaid_catidlineage_arr   = array();

				 if($source_data){
					$source_catidlineage_arr = explode("/,/",trim($source_data,'/'));
					$source_catidlineage_arr = array_filter($source_catidlineage_arr);
					$source_catidlineage_arr = array_unique($source_catidlineage_arr);
				 }
				 
				 if(count($source_catidlineage_arr))
				 {
					 
					$dest_catidlineage_arr = explode("/,/",trim($dest_data,'/'));
					$dest_catidlineage_arr = array_filter($dest_catidlineage_arr);
					$dest_catidlineage_arr = array_unique($dest_catidlineage_arr);

					$diff_catidlineage_arr = array_diff($source_catidlineage_arr,$dest_catidlineage_arr);
					if(count($diff_catidlineage_arr))
					{
						//$sql_catids 	= "SELECT catid,national_catid,category_name FROM d_jds.tbl_categorymaster_generalinfo WHERE catid IN ('".implode("','",$diff_catidlineage_arr)."')";
						//$res_catids		=	parent::execQuery($sql_catids, $this->conn_iro);

						$cat_params = array();
						$cat_params['page'] ='duplicate_freeze_check_class';
						$cat_params['data_city'] 	= $this->data_city;
						$cat_params['return']		= 'catid,national_catid,category_name';				

						$where_arr  	=	array();
						$where_arr['catid']		= implode(",",$diff_catidlineage_arr);
						$cat_params['where']	= json_encode($where_arr);
						if(count($diff_catidlineage_arr)>0){
							$cat_res 		=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
						}
						$cat_res_arr 	= array();
						if($cat_res!=''){
							$cat_res_arr = json_decode($cat_res,TRUE);
						}
						if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
						{
							foreach($cat_res_arr['results'] as $key=>$data_arr)
							{
								$arr_national_catids[$data_arr['catid']] = $data_arr['category_name'];
							}
						}
					}

					//$category_data_arr[$key] = $diff_catidlineage_arr;
					
					return $arr_national_catids;
					//if(count($diff_catidlineage_arr)>0  && !checkPaidStatus($dest_parentid))
					//{
						//$dest_catidlineage_final_arr = array_merge($dest_catidlineage_arr,$diff_catidlineage_arr);

					//}else if(count($diff_catidlineage_arr)>0)
					//{
						//$source_nonpaid_catidlineage_arr = array_merge($source_nonpaid_catidlineage_arr,$diff_catidlineage_arr);
						//$source_nonpaid_catidlineage_arr = array_filter($source_nonpaid_catidlineage_arr);
						//$source_nonpaid_catidlineage_arr = array_unique($source_nonpaid_catidlineage_arr);

					//}

					//if(!count($dest_catidlineage_final_arr) &&  count($dest_catidlineage_arr)>0)
					//{
						//$dest_catidlineage_final_arr = $dest_catidlineage_arr;
					//}

				 }
				  
		  
			}else {
					if($key == 'latitude' || $key == 'longitude')
					{
					//echo $key;
					//print_r($source_data);
					return $source_data;
					
					}//echo '<br>coming in hereee1123333';
					$source_data_arr = explode(',',$source_data);
					$source_data_arr = array_filter($source_data_arr);
					$source_data_arr = array_unique($source_data_arr);

					$dest_data_arr	 = explode(',',$dest_data);
					$dest_data_arr	 = array_filter($dest_data_arr);
					$dest_data_arr	 = array_unique($dest_data_arr);
					//echo '<br>'.$key.'<br><pre>';
					//echo '<br> <pre> source data arr :: ';print_r($source_data_arr);
					//echo '<br> <pre> dest data arr :: ';print_r($dest_data_arr);

					$diff_data_arr   = array_diff($source_data_arr,$dest_data_arr);
					//echo '<br> <pre> data to update :: ';print_r($diff_data_arr);
					$diff_data_arr	 = array_unique($diff_data_arr);
					$diff_data_arr	 = array_filter($diff_data_arr);
					$diff_data_arr	 = array_unique($diff_data_arr);
					if(count($diff_data_arr)>0)
					{
						return $diff_data_arr;

					}
		    }
				
		  

		}
	}
	
	
	function duplicate_freeze_submit()
	{
		//echo 'dasbad';
		$new_data_arr = '';
		
		if(count($this->check_array) > 0)
		{
			
			require_once('../library/configclass.php');
			$configclassobj= new configclass();			
			$this->urldetails	=	$configclassobj->get_url(urldecode($this->data_city));
			 
			$row_data_to_update = $this->check_array;	
			$dest_data_arr 		= $this->getDestinationData();
			
			$obj_log        = new contractLog($this->dist_parentid, 'Duplicate_Merge_Cron', 'De-Duplication Cron' , $dest_data_arr[$this->dist_parentid]['data_city']);
			
			
			$field_array = array('catidlineage','catidlineage_nonpaid','landline','dialable_landline','latitude','longitude','geocode_accuracy_level','landline_feedback','mobile','dialable_mobile',' mobile_feedback','fax','tollfree','email','email_feedback','landline_display','dialable_landline_display','mobile_display','dialable_mobile_display','email_display','landline_addinfo','mobile_addinfo','tollfree_addinfo','othercity_number','payment_type','working_time_start','working_time_end','website','social_media_url','attributes');
						
			$field_mapped_array = array('catidlineage'=>'Categories','catidlineage_nonpaid'=>'Non-paid Categories','landline'=>'LandLine Numbers','latitude'=>'Latitude','longitude'=>'Longitude','geocode_accuracy_level'=>'Geo Code','dialable_landline'=>'Dialable Landlines','landline_feedback'=>'Landline Feedback Numbers','mobile'=>'Mobile Numbers','dialable_mobile'=>'Dialable Mobile Numbers',' mobile_feedback'=>'Mobile Feedback Numbers','fax'=>'Fax','tollfree'=>'Toll Free Numbers','email'=>'Email Ids','email_feedback'=>'Email Feedback','landline_display'=>'Landline Display Numbers','dialable_landline_display'=>'Dialable Landline Display Numbers','mobile_display'=>'Mobile Display Numbers','dialable_mobile_display'=>'Dialable Mobile Display Numbers','email_display'=>'Email Display','landline_addinfo'=>'Landline Addinfo','mobile_addinfo'=>'Mobile Addinfo','tollfree_addinfo'=>'Tollfree Addinfo','othercity_number'=>'Other City Numbers','payment_type'=> 'Payment Type','working_time_start'=> 'Working Time End','working_time_end'=> 'Working Time End','website'=> 'Website','social_media_url'=> 'Social Media Url','attributes'=>'Attributes');
			
			foreach($field_mapped_array as $key => $value)
			{
				if(count($row_data_to_update[$value])>0)
				{
					$row_data_to_update[$key] = $row_data_to_update[$value];
					unset($row_data_to_update[$value]);
				}
			}
			//print_r($row_data_to_update);die;
			
			foreach ($field_mapped_array as $field_name => $field_value)
			{
						
				if($field_name == 'catidlineage' || $field_name == 'catidlineage_nonpaid')
				{
					//echo 'vasb='.$dest_data_arr[$this->dist_parentid];
					$response_data = $this->callinsertapi($this->src_parentid,$field_name,$row_data_to_update,$dest_data_arr[$this->dist_parentid],$this->dist_parentid,$this->data_city,$field_value);
					
					if($response_data['code'] == '-1' && !$this->cron)
					return $response_data;
					
					
				}else if(stristr($field_name,'_addinfo'))
				{
					
					$column_to_update_extra .=  $this->returnColumndiff($row_data_to_update['parentid'],$field_name,$row_data_to_update[$field_name],$dest_data_arr[$this->dist_parentid][$field_name],$case);

				}
				else if(stristr($field_name,'payment_type'))
				{
					
					$column_to_update_extra .=  $this->returnColumndiff($row_data_to_update['parentid'],$field_name,$row_data_to_update[$field_name],$dest_data_arr[$this->dist_parentid][$field_name],$case);

				}
				else if(stristr($field_name,'social_media_url'))
				{
					
					$column_to_update_extra .=  $this->returnColumndiff($row_data_to_update['parentid'],$field_name,$row_data_to_update[$field_name],$dest_data_arr[$this->dist_parentid][$field_name],$case);

				}
				else if($field_name == 'working_time_start' || $field_name == 'working_time_end')
				{
					
					$column_to_update_extra .=  $this->returnColumndiff($row_data_to_update['parentid'],$field_name,$row_data_to_update[$field_name],$dest_data_arr[$this->dist_parentid][$field_name],$case);

				}
				else if( in_array(strtolower(trim($field_name)),array('latitude','longitude','geocode_accuracy_level')) )
				{
					if($this->trace) 
					{
						echo ' sour ac lev :: '.$row_data_to_update['geocode_accuracy_level'] .' dest ac lev :: '. $dest_data_arr[$this->dist_parentid]['geocode_accuracy_level'];
					}
					
					if( $row_data_to_update['geocode_accuracy_level']>0 && ($row_data_to_update['geocode_accuracy_level'] < $dest_data_arr[$this->dist_parentid]['geocode_accuracy_level'] ||  $dest_data_arr[$this->dist_parentid]['geocode_accuracy_level'] < 1) )
					{
						$column_to_update .=  $this->returnColumndiff($row_data_to_update['parentid'],$field_name,$row_data_to_update[$field_name],$dest_data_arr[$this->dist_parentid][$field_name],$case);
					}

				}else if(strtolower($field_name)=='attributes'){								
					$attributeCheck = $this->attributeUpdate($row_data_to_update[$field_name]);								
				}else {								
					$column_to_update .=  $this->returnColumndiff($row_data_to_update['parentid'],$field_name,$row_data_to_update[$field_name],$dest_data_arr[$this->dist_parentid][$field_name],$case);
					//echo $field_name;die;
				}
				
		}
			//print_r($column_to_update);die;
			
			
			
			if($column_to_update || $column_to_update_extra || $attributeCheck['error']['code']==0)
			{
				
				/* generating narration - start */
				if($this->cron)
				{
					$cs_url	    	=	$this->urldetails['url'];
					$narration_rul  = $cs_url."api/fetch_update_narration.php";
					
					//source
					$narration_params_source['parentid']    = $row_data_to_update['parentid'];
					$narration_params_source['narration']   = '<br></br>Contract ('.$row_data_to_update['parentid'].') is Freezed against its active contract ('.$this->dist_parentid.') via de duplication cron';
					$narration_params_source['ucode']       = 'De-Duplication Cron';
					$narration_params_source['uname']       = 'De-Duplication Cron';
					$narration_params_source['module']      = 'Duplication';
					$narration_params_source['data_city']   = $this->data_city;
					$narration_params_source['action']	    = 2;
					
					if($this->trace) 
					{
						echo '<br><br>'.$narration_rul;
						print_r($narration_params_source);
					}
					$ch 		= curl_init();
					curl_setopt($ch, CURLOPT_URL, $narration_rul);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,2);
					curl_setopt($ch,CURLOPT_POST, TRUE);
					curl_setopt($ch,CURLOPT_POSTFIELDS,$narration_params_source);
					$narration_res_source	= curl_exec($ch);
					if($this->trace) 
					{
						echo '<br><br>'.$narration_res_source;
					}
					
					
					//dest
					$narration_params_dest['parentid']    = $this->dist_parentid;
					$narration_params_dest['narration']   = '<br></br>Data copied from ('.$row_data_to_update['parentid'].') via de duplication Cron.';
					$narration_params_dest['ucode']       = 'De-Duplication Cron';
					$narration_params_dest['uname']       = 'De-Duplication Cron';
					$narration_params_dest['module']      = 'Duplication';
					$narration_params_dest['data_city']   = $dest_data_arr[$this->dist_parentid]['data_city'];
					$narration_params_dest['action']	  = 2;
					
					if($this->trace) 
					{
						echo '<br><br>'.$narration_rul;
						print_r($narration_params_dest);
					}
					$ch 		= curl_init();
					curl_setopt($ch, CURLOPT_URL, $narration_rul);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,2);
					curl_setopt($ch,CURLOPT_POST, TRUE);
					curl_setopt($ch,CURLOPT_POSTFIELDS,$narration_params_dest);
					$narration_res_dest 	= curl_exec($ch);
					if($this->trace) 
					{
						echo '<br><br>'.$narration_res_dest;
					}
			    }
			/* generating narration - end */
			
				
				if($column_to_update)
				{
					$update_generalinfo = "UPDATE tbl_companymaster_generalinfo SET ".trim($column_to_update,",")." WHERE parentid ='".$this->dist_parentid."'";
					//echo '<br> res :: '.$result_generalinfo	= $conn_iro->query_sql($update_generalinfo);
					$result_generalinfo =	parent::execQuery($update_generalinfo, $this->conn_iro);
					if($this->trace) 
					{
						echo '<br><br>'.$update_generalinfo;
						echo '<br><br>'.$result_generalinfo;
					}
				}

				if($column_to_update_extra){
					$update_extradetails = "UPDATE tbl_companymaster_extradetails SET ".trim($column_to_update_extra,",")." WHERE parentid ='".$this->dist_parentid."'";
					//echo '<br> res :: '.$result_extradetails	= $conn_iro->query_sql($update_extradetails);
					//~ echo "<pre>update_extradetails:--".$update_extradetails;
					$result_extradetails =	parent::execQuery($update_extradetails, $this->conn_iro);
					if($this->trace) 
					{
						echo '<br><br>'.$update_extradetails;
						echo '<br><br>'.$result_extradetails;
					}
				}
				
				if($result_generalinfo || $result_extradetails || $attributeCheck['error']['code']==0)
				{
					unset($obj_log);
					$result_msg_arr['error']['code'] = 0;
					$result_msg_arr['error']['msg'] = "Success";
					$result_msg_arr['error']['updated_parentid'] = $this->dist_parentid;
					return $result_msg_arr;
				}
				
			}
			unset($obj_log);
		}	
	}
	
	function attributeUpdate($selectedAttributes){
		//~ echo "<pre>urldetails:--";print_r($this->urldetails);		
		if($selectedAttributes!=''){
			$paramsSend	=	array();
			$paramsSend['parentid']		=	$this->params['dist_parentid'];	
			$paramsSend['data_city']	=	$this->params['data_city'];
			$paramsSend['ucode']		=	$this->params['cron'];	
			$paramsSend['module']		=	$this->params['module'];
			$paramsSend['action']		=	'check_attr';	
			$paramsSend['attrReturn']	=	'1';	
			$paramsSend['live_data']	=	'1';	
			$url = $this->urldetails['jdbox_url'].'/services/attribute_page.php';					
			$ch 		= curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,2);
			curl_setopt($ch,CURLOPT_POST, TRUE);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$paramsSend);
			$narration_res_source	= curl_exec($ch);			
			$result 		  = json_decode($narration_res_source,1);
			if($result['error']['code']==0){
				$selectedStr = implode("','",$selectedAttributes);
				$get_src_data = "SELECT * FROM tbl_companymaster_attributes WHERE parentid='".$this->src_parentid."' AND attribute_id IN ('".$selectedStr."')";
				$res_src_data = parent::execQuery($get_src_data,$this->conn_iro);
				$insert1= '';
				if($res_src_data && mysql_num_rows($res_src_data)>0){
					$getDocid = "SELECT docid, parentid FROM tbl_id_generator WHERE parentid='".$this->dist_parentid."'";
					$resDocid = parent::execQuery($getDocid, $this->conn_iro);
					if($resDocid && mysql_num_rows($resDocid)>0){
						$rowDocid = mysql_fetch_assoc($resDocid);
						$docid = $rowDocid['docid'];
					}
					while($row_src_data = mysql_fetch_assoc($res_src_data)){						
						$updatedby		 = ($this->cron) ? 'De-Duplication Cron':'De-Duplication Updation';						
						$upd = $this->empcode."-".$updatedby;
						//echo "<br>updatedby:--".$updatedby. " this->empcode ".$this->empcode;
						$insert1 .= "('".$docid."', '".$this->dist_parentid."', '".$this->data_city."','".$row_src_data['attribute_id']."', '".addslashes(stripslashes($row_src_data['attribute_name']))."', '".addslashes(stripslashes($row_src_data['attribute_dname']))."', '".addslashes(stripslashes($row_src_data['attribute_value']))."', '".$row_src_data['attribute_type']."', '".$row_src_data['attribute_sub_group']."', '".$row_src_data['sub_group_name']."', '".$row_src_data['active_flag']."', '".$row_src_data['display_flag']."', '".$row_src_data['sub_group_position']."', '".$row_src_data['attribute_position']."',  '".$row_src_data['attribute_prefix']."' ,'".$upd."','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."', '".$row_src_data['main_attribute_flag']."' , '".$row_src_data['main_attribute_position']."')" .",";						
					}
					if($insert1!=''){
						$insert1 = rtrim($insert1,",");				
						$insert_data_str = rtrim($insert1,",");
						$fin_insert = "INSERT INTO tbl_companymaster_attributes (docid,parentid,city ,attribute_id,attribute_name,attribute_dname,attribute_value,attribute_type,attribute_sub_group,sub_group_name,active_flag,display_flag,sub_group_position ,attribute_position,attribute_prefix,updatedby ,updatedon ,backenduptdate,main_attribute_flag ,main_attribute_position) VALUES $insert_data_str
						
						ON DUPLICATE KEY UPDATE
						docid  				= VALUES(docid),
						parentid  			= VALUES(parentid),
						city  				= VALUES(city),
						attribute_id	= VALUES(attribute_id),
						attribute_name  	= VALUES(attribute_name),
						attribute_dname  	= VALUES(attribute_dname),
						attribute_value  		= VALUES(attribute_value),
						attribute_type  		= VALUES(attribute_type),
						attribute_sub_group  = VALUES(attribute_sub_group),
						sub_group_name  	= VALUES(sub_group_name),
						active_flag  	= VALUES(active_flag),
						display_flag  	= VALUES(display_flag),
						sub_group_position  	= VALUES(sub_group_position),
						attribute_position  	= VALUES(attribute_position),
						attribute_prefix  	    = VALUES(attribute_prefix),
						updatedby  	= VALUES(updatedby),
						updatedon = VALUES(updatedon),
						backenduptdate = VALUES(backenduptdate),
						main_attribute_flag  	= VALUES(main_attribute_flag),
						main_attribute_position = VALUES(main_attribute_position) ";
						$res_insert = parent::execQuery($fin_insert, $this->conn_iro);
						
						if($res_insert){
							$result_msg_arr['error']['code'] = 0;
							$result_msg_arr['error']['msg'] = "Success";
							$result_msg_arr['error']['updated_parentid'] = $this->dist_parentid;
						}else{
							$result_msg_arr['error']['code'] = 1;
							$result_msg_arr['error']['msg'] = "Insert Query On Destination Contract Failed!!";
							$result_msg_arr['error']['updated_parentid'] = $this->dist_parentid;
						}
						
						$result = $this->updateAttributeSearch();
					}
				}else{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "No Attributes Found In Source Contract";
					$result_msg_arr['error']['updated_parentid'] = $this->dist_parentid;
				}
			}else{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Attribute Mapped Categories not Present in Destination Contract";
				$result_msg_arr['error']['updated_parentid'] = $this->dist_parentid;
			}
		}
		return $result_msg_arr;	
	}
	
	function updateAttributeSearch(){		
		$getSrcData = "SELECT catidlineage, parentid, attribute_search from tbl_companymaster_extradetails where parentid = '".$this->src_parentid."'";		 
		$resSrcData =	parent::execQuery($getSrcData, $this->conn_iro);
		if($resSrcData && mysql_num_rows($resSrcData)>0){
			$rowSrcData = mysql_fetch_assoc($resSrcData);
			$srcCategories = $rowSrcData['catidlineage'];
			$srcAttrSearch = $rowSrcData['attribute_search'];
		}
		$getDstData = "SELECT catidlineage, parentid, attribute_search from tbl_companymaster_extradetails where parentid = '".$this->dist_parentid."'";		 
		$resDstData =	parent::execQuery($getDstData, $this->conn_iro);
		if($resDstData && mysql_num_rows($resDstData)>0){
			$rowDstData = mysql_fetch_assoc($resDstData);
			$dstCategories = $rowDstData['catidlineage'];
			$dstAttrSearch = $rowDstData['attribute_search'];
		}
		if($srcCategories!='' && $dstCategories!=''){
			$src_catidlineage_arr = explode("/,/",trim($srcCategories,'/'));
			$dst_catidlineage_arr = explode("/,/",trim($dstCategories,'/'));
			
			$src_catidlineage_arr = array_unique(array_filter($src_catidlineage_arr));
			$dst_catidlineage_arr = array_unique(array_filter($dst_catidlineage_arr));			
			$catid_diff  = array_merge(array_diff($src_catidlineage_arr,$dst_catidlineage_arr), array_diff($dst_catidlineage_arr, $src_catidlineage_arr));			
			if(count($catid_diff)<=0){
				$arr = array(); $dstAttrSearch_arr = array();
				$dstAttrSearch_arr = array_unique(array_filter(explode("#",$dstAttrSearch)));				
				$srcAttrSearch_arr = array_unique(array_filter(explode("#",$srcAttrSearch)));				
				foreach($srcAttrSearch_arr as $key=>$value){					
					if(!in_array($value,$dstAttrSearch_arr)){
						array_push($dstAttrSearch_arr, $value);
					}
				}				
			}else{
				$dstAttrSearch_arr = array();
				$dstAttrSearch_arr = array_unique(array_filter(explode("#",$dstAttrSearch))); //existing+ attr's based on categories
				$CatStr 		   = implode(',',$dst_catidlineage_arr);
				//~ echo "<br>CatStr:--".$CatStr;
				$cat_params = array();
				$cat_params['page'] ='duplicate_freeze_check_class';
				$cat_params['data_city'] 	= $this->data_city;
				$cat_params['return']		= 'national_catid,catid,mapped_attribute';				

				$where_arr  	=	array();
				if(count($dst_catidlineage_arr)>0){
					$where_arr['catid']		= $CatStr;
					$cat_params['where']	= json_encode($where_arr);
					$cat_res 		=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);					
					$cat_res_arr 	= array();
					if($cat_res!=''){
						$cat_res_arr = json_decode($cat_res,TRUE);
					}
				}
				$mapped_attr_arr = array();				
				if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0){					
					foreach ($cat_res_arr['results'] as $key => $cat_arr) {
						$result = array();
						$result = array_filter(explode(",",$cat_arr['mapped_attribute']));
						foreach($result as $key =>$value){
							$mapped_attr_arr[] = $value;
						}
					}
				}
				if(count($mapped_attr_arr)>0){
					$mapped_attr_arr = array_unique($mapped_attr_arr);
					$get_details = "SELECT attribute_name, unique_code, attribute_group, attribute_sub_group, display_flag, filter_flag,active_flag FROM (SELECT attribute_name, unique_code, attribute_group, attribute_sub_group, display_flag, filter_flag,active_flag FROM d_jds.tbl_attribute_mapping WHERE unique_code IN ('".implode("','",$mapped_attr_arr)."') ) as t group by unique_code";					
					$res_details = parent::execQuery($get_details, $this->conn_local);	
					if($res_details &&  parent::numRows($res_details)>0){
						$attribute_search_arr = array();
						while($row_details = parent::fetchData($res_details)){							
							if( ($row_details['display_flag']==0 && $row_details['filter_flag']==1) || ($row_details['display_flag']==1 && $row_details['filter_flag']==1) || ($row_details['display_flag']==2 && $row_details['filter_flag']==1) ){
								$attribute_search_arr[]    = $row_details['unique_code'];			
							}
						}
					}
				}
				if(count($attribute_search_arr)>0){					
					foreach($attribute_search_arr as $key=>$value){						
						array_push($dstAttrSearch_arr,$value);
					}					
				}				
			}
			if(count($dstAttrSearch_arr)>0){
				$attr_srch_str = '';
				$attr_srch_str = implode("#",$dstAttrSearch_arr);
				$sqlUpdate = "UPDATE tbl_companymaster_extradetails SET attribute_search='#".$attr_srch_str."#' WHERE parentid ='".$this->dist_parentid."' ";				
				$resUpdate = parent::execQuery($sqlUpdate, $this->conn_iro);
			}
		}
		
	}
	
	function returnColumndiff($parentid,$key,$source_data,$dest_data,$case)
	{
		//print_r($dest_data);
		if($source_data)
		{
			if(stristr($key,'_addinfo'))
			{
				
				if($this->merging_process)
					$source_main_data_arr = explode('|~|',$source_data);
				else
					$source_main_data_arr = $source_data;
				
				foreach($source_main_data_arr as $source_main_data_addinfo)
				{
					
					$source_main_data_addinfo_arr = explode('|^|',$source_main_data_addinfo);
					if($source_main_data_addinfo_arr[0])
					{
						$source_mobile_addinfo_arr[$source_main_data_addinfo_arr[0]] = $source_main_data_addinfo_arr[1];
					}
				}
				//echo 'vdab==';print_r($source_main_data_arr);
				
				$dest_mobile_addinfo_arr = array();
				$dest_main_data_arr = array();
				
				//print_r($dest_main_data_arr);
				
				
				$dest_main_data_arr = explode('|~|',$dest_data);
				
				
				foreach($dest_main_data_arr as  $dest_main_data_addinfo)
				{
					$dest_main_data_addinfo_arr = explode('|^|',$dest_main_data_addinfo);
					
					if($dest_main_data_addinfo_arr[0])
					{
						$dest_mobile_addinfo_arr[] = $dest_main_data_addinfo_arr[0];
					}
				}
				//
				//print_r($source_mobile_addinfo_arr);
				$count_addinfo_diff_arr = array_diff(array_keys($source_mobile_addinfo_arr),$dest_mobile_addinfo_arr);
				$count_addinfo_diff_arr = array_filter($count_addinfo_diff_arr);
				$count_addinfo_diff_arr = array_unique($count_addinfo_diff_arr);
			
			
			
				if(count($count_addinfo_diff_arr)>0)
				{
					foreach($count_addinfo_diff_arr as $diff_mobile)
					{
						
						$new_dest_addinfo_arr [] = $diff_mobile.'|^|'.$source_mobile_addinfo_arr[$diff_mobile];
					}
					
					//print_r($new_dest_addinfo_arr);
					
					if(count($new_dest_addinfo_arr) && $dest_data)
					{
						$dest_data = $dest_data."|~|".implode("|~|",$new_dest_addinfo_arr);
					}else if (count($new_dest_addinfo_arr)){
						$dest_data = implode("|~|",$new_dest_addinfo_arr);
					}
					
					if($dest_data)
					{
						//$update_column 	   = "'".$dest_data."',";
						$update_column 	   = $key."= '".$dest_data."',";
						return trim($update_column);
					}
					
				}
			
			}
			else if(stristr($key,'payment_type')) 
			{
				
				if($this->merging_process)
					$source_data = explode('~',$source_data);
				
				
				$source_data_arr = array_filter($source_data);
				$source_data_arr = array_unique($source_data_arr);
				$source_data_arr = array_map('strtolower', $source_data_arr);
				
				$dest_data_arr	 = explode('~',$dest_data);
				$dest_data_arr	 = array_filter($dest_data_arr);
				$dest_data_arr	 = array_unique($dest_data_arr);
				$dest_data_arr 	 = array_map('strtolower', $dest_data_arr);
				
				$diff_data_arr   = array_diff($source_data_arr,$dest_data_arr);
				$diff_data_arr	 = array_filter($diff_data_arr);
				$diff_data_arr	 = array_unique($diff_data_arr);
				
				if(count($diff_data_arr))
				{
					
					$new_dest_data_arr = array_merge($dest_data_arr,$diff_data_arr);
					
					$new_dest_data_arr = array_map('ucwords', $new_dest_data_arr);
					
					$update_column 	   = $key."= '".implode("~",$new_dest_data_arr)."',";
					
					return trim($update_column);
					
				}
				
			} 
			else if(stristr($key,'social_media_url')) 
			{
				
				if($this->merging_process)
					$source_data = explode('|~|',$source_data);
				
				
				$source_data_arr = array_filter($source_data);
				$source_data_arr = array_unique($source_data_arr);
				$source_data_arr = array_map('strtolower', $source_data_arr);
				
				$dest_data_arr	 = explode('|~|',$dest_data);
				$dest_data_arr	 = array_filter($dest_data_arr);
				$dest_data_arr	 = array_unique($dest_data_arr);
				$dest_data_arr 	 = array_map('strtolower', $dest_data_arr);
				
				$diff_data_arr   = array_diff($source_data_arr,$dest_data_arr);
				$diff_data_arr	 = array_filter($diff_data_arr);
				$diff_data_arr	 = array_unique($diff_data_arr);
				
				if(count($diff_data_arr))
				{
					
					$new_dest_data_arr = array_merge($dest_data_arr,$diff_data_arr);
					
					//$new_dest_data_arr = array_map('ucwords', $new_dest_data_arr);
					
					$update_column 	   = $key."= '".implode("|~|",$new_dest_data_arr)."',";
					
					return trim($update_column);
					
				}
				
			} 
			else if(stristr($key,'working_time_start') || stristr($key,'working_time_end')) 
			{
				$junk_data = array(',,,,,,,','-,-,-,-,-,-,-,',',','0',',,','-,','-,-,-,-,-,-,Closed-,',',,,,,,','00:00-,-,-,-,-,-,-,','0,0,0,0,0,0,0','00:00,','-,-,-,-,-,-,-','00:00,,,,,,,','-,Closed-,-,-,-,-,-,');
				if( (trim($dest_data) == '' || in_array(trim($dest_data),$junk_data)) &&   (trim($source_data) != '' && !in_array(trim($source_data),$junk_data))  )
				{
					$update_column 	   = $key."= '".$source_data."',";
					
					return trim($update_column);					
				}
			}
			else 
			{	
				
				if($this->merging_process)
					$source_data = explode(',',$source_data);
					
				//echo $source_data_arr = explode(',',$source_data);
				$source_data_arr = array_filter($source_data);
				$source_data_arr = array_unique($source_data_arr);
				//echo 'vsavsv';print_r($source_data_arr);		
				//print_r($key);	
				$dest_data_arr	 = explode(',',$dest_data);
				$dest_data_arr	 = array_filter($dest_data_arr);
				$dest_data_arr	 = array_unique($dest_data_arr);
				//echo 'vsavsv';print_r($dest_data_arr);	
				if($key == 'latitude' || $key == 'longitude' || $key == 'geocode_accuracy_level' )
				{
					$update_column 	   = $key."= '".implode(",",$source_data_arr)."',";
					return trim($update_column);
				}
				
				$diff_data_arr   = array_diff($source_data_arr,$dest_data_arr);
				//print_r($diff_data_arr);
				
				
				
				$diff_data_arr	 = array_filter($diff_data_arr);
				$diff_data_arr	 = array_unique($diff_data_arr);
				if(count($diff_data_arr) && ($key != 'geocode_accuracy_level'))
				{
					
					$new_dest_data_arr = array_merge($dest_data_arr,$diff_data_arr);
					
					$update_column 	   = $key."= '".implode(",",$new_dest_data_arr)."',";
					
					return trim($update_column);
					
				}
			}
			
			}
	}
		
	function callinsertapi($parentid,$key,$source_data,$dest_data,$dest_parentid,$data_city,$field_value)
	{
		//print_r($dest_data);die;
		if($this->trace) 
		{
			 echo '<br> source parentid :: '.$parentid." dest parentid :: ". $dest_parentid. " key :: ".$key;
			 echo '<hr>';
			 echo '<br> source data ';
			 print_r($source_data);
			 echo '<hr>';
			 echo '<br> dest data ';
			 print_r($dest_data);
		}
		// die;
		 
		 $source_catidlineage_arr = array();$source_nonpaid_catidlineage_arr = array();
		 $dest_catidlineage_arr   = array();$dest_nonpaid_catidlineage_arr   = array();
		 $dest_catidlineage_final_arr   = array();$dest_nonpaid_catidlineage_final_arr   = array();
		 $diff_catidlineage_arr   = array();$diff_nonpaid_catidlineage_arr   = array();
		
		 if(count($source_data[catidlineage])>0){
			$source_catidlineage_arr = array_values($source_data[catidlineage]);
			$source_catidlineage_arr = array_filter($source_catidlineage_arr);
			$source_catidlineage_arr = array_unique($source_catidlineage_arr);
		 }
		 // echo 'bsd';print_r($source_catidlineage_arr);
		 if(count($source_data[catidlineage_nonpaid])>0){
			$source_nonpaid_catidlineage_arr = array_values($source_data[catidlineage_nonpaid]);
			$source_nonpaid_catidlineage_arr = array_filter($source_nonpaid_catidlineage_arr);
			$source_nonpaid_catidlineage_arr = array_unique($source_nonpaid_catidlineage_arr);
		 }
		//echo 'bsb';print_r($source_data[catidlineage_nonpaid]);
			$dest_catidlineage_arr = explode("/,/",trim($dest_data[catidlineage],'/'));
			$dest_catidlineage_arr = array_filter($dest_catidlineage_arr);
			$dest_catidlineage_arr = array_unique($dest_catidlineage_arr);
			
		 if(count($source_catidlineage_arr) || count($dest_catidlineage_arr) )
		 {
			$diff_catidlineage_arr = array_diff($source_catidlineage_arr,$dest_catidlineage_arr);
			
			
			if(count($diff_catidlineage_arr)>0  && !$this->checkPaidStatus($dest_parentid))
			{
				if($this->trace) 
				echo '<br> its nonpaid contract <br>';
				
				$dest_catidlineage_final_arr = array_merge($dest_catidlineage_arr,$diff_catidlineage_arr);
				
			}else if(count($diff_catidlineage_arr)>0)
			{
				$source_nonpaid_catidlineage_arr = array_merge($source_nonpaid_catidlineage_arr,$diff_catidlineage_arr);
				$source_nonpaid_catidlineage_arr = array_filter($source_nonpaid_catidlineage_arr);
				$source_nonpaid_catidlineage_arr = array_unique($source_nonpaid_catidlineage_arr);
				
			}
			
			if($this->trace)
			print_r($dest_catidlineage_final_arr);
			
			if(!count($dest_catidlineage_final_arr) &&  count($dest_catidlineage_arr)>0)
			{
				$dest_catidlineage_final_arr = $dest_catidlineage_arr;
			}
		
		 }
		 
		 
		 $dest_nonpaid_catidlineage_arr = explode("/,/",trim($dest_data[catidlineage_nonpaid],'/'));
		 $dest_nonpaid_catidlineage_arr = array_filter($dest_nonpaid_catidlineage_arr);
		 $dest_nonpaid_catidlineage_arr = array_unique($dest_nonpaid_catidlineage_arr);
		
		 if(count($source_nonpaid_catidlineage_arr) ||  count($dest_nonpaid_catidlineage_arr))
		 {
			
				
			$diff_nonpaid_catidlineage_arr = array_diff($source_nonpaid_catidlineage_arr,$dest_nonpaid_catidlineage_arr);
			if(count($diff_nonpaid_catidlineage_arr)>0)
			{
				$dest_nonpaid_catidlineage_final_arr = array_merge($dest_nonpaid_catidlineage_arr,$diff_nonpaid_catidlineage_arr);
				
			}
			else if (count($dest_nonpaid_catidlineage_arr)>0)
			{
				$dest_nonpaid_catidlineage_final_arr = $dest_nonpaid_catidlineage_arr;
			}
			
		  }
		  
		  
		  if(count($dest_catidlineage_final_arr)>0 || count($dest_nonpaid_catidlineage_final_arr)>0)
		  {
			  
			  $total_catidlineage_final_arr = array_merge($dest_catidlineage_final_arr,$dest_nonpaid_catidlineage_final_arr);
		  }
		  
		  if($this->trace) {
			echo '<br> total catid : ';
			print_r($total_catidlineage_final_arr);//die;
		}
		if(count($total_catidlineage_final_arr)>0)
		{
			
			if(count($diff_catidlineage_arr)>0 || count($diff_nonpaid_catidlineage_arr)>0)
			{
				
				$total_new_catidlineage_array = array_merge($diff_catidlineage_arr,$diff_nonpaid_catidlineage_arr);
				$total_new_catidlineage_array = array_filter($total_new_catidlineage_array);
				$total_new_catidlineage_array = array_unique($total_new_catidlineage_array);
				
				$total_existing_catidlineage_array = array_merge($dest_catidlineage_arr,$dest_nonpaid_catidlineage_arr);
				$total_existing_catidlineage_array = array_filter($total_existing_catidlineage_array);
				$total_existing_catidlineage_array = array_unique($total_existing_catidlineage_array);
				
				$total_catidlineage_array = array_merge($total_new_catidlineage_array,$total_existing_catidlineage_array);
				$total_catidlineage_array = array_filter($total_catidlineage_array);
				$total_catidlineage_array = array_unique($total_catidlineage_array);
				
				if($this->trace) 
				{
					echo '<br> <pre>new catid  to check :: ';print_r($total_new_catidlineage_array);
					
					echo '<br> <pre>total catid  to check :: ';print_r($total_catidlineage_array);
				}
				$catids_to_check = implode(",",$total_catidlineage_array);
				
				$exist_catids_to_check = implode(",",$total_existing_catidlineage_array);
				
				
				$parentage_params['parentid']    = $dest_parentid;
				$parentage_params['ucode']       = 'duplicate_data_merge_process';
				$parentage_params['module']      = 'webedit';
				$parentage_params['action']      = 'check_multiparentage';
				$parentage_params['catid_list']  = $catids_to_check;
				$parentage_params['exist_catid'] = $exist_catids_to_check;
				
				
				$cs_url	    	=	$this->urldetails['url'];
				
				$parentage_check_rul = $cs_url."/api/multiparentage_check.php";
				
				//?parentid=".$dest_parentid."&ucode=10000760&module=webedit&action=check_multiparentage&catid_list=".$catids_to_check."&exist_catid=".$exist_catids_to_check;
				
				if($this->trace) 
				{
					echo '<br><br>'.$parentage_check_rul;
					print_r($parentage_params);
				}
				//http://172.29.40.217:81/api/multiparentage_check.php?parentid=PXX20.XX20.000281090855.U3F1&ucode=10000760&module=webedit&action=check_multiparentage&catid_list=7468,57443,3663,149685,149947,152534,1041804750&trace=1&exist_catid=7468,57443
				
				//http://172.29.40.217:81/api/multiparentage_check.php?parentid=PXX20.XX20.000281090855.U3F1&ucode=10000760&module=jda&action=check_multiparentage&catid_list=7468,57443,3663,149685,149947,152534,1041804750&trace=1
				//http://172.29.40.217:81/api/multiparentage_check.php?parentid=PXX20.XX20.000281090855.U3F1&ucode=10000760&module=webedit&action=check_multiparentage&catid_list=7468,57443,3663,149685,149947,152534,1041804750&trace=1&exist_catid=7468,57443
				//http://172.29.40.217:81/api/multiparentage_check.php?parentid=PXX20.XX20.000281090855.U3F1&ucode=10000760&module=webedit&action=check_multiparentage&catid_list=3663,149685,149947,152534,1041804750&exist_catid=7468,57443
				
				//echo '<br><br>'.$parentage_check_rul = "http://imteyazraja.jdsoftware.com/csgenio/api/multiparentage_check.php?parentid=".$dest_parentid."&ucode=10000760&module=jda&action=check_multiparentage&catid_list=".$catids_to_check."";
				$ch 		= curl_init();
				curl_setopt($ch, CURLOPT_URL, $parentage_check_rul);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,2);
				curl_setopt($ch,CURLOPT_POST, TRUE);
				curl_setopt($ch,CURLOPT_POSTFIELDS,$parentage_params);
				$resmsg 	= curl_exec($ch);
				$resmsg_arr = json_decode($resmsg,true);	
				
				if($this->trace) 
				{
					echo '<br><br>';
					echo $resmsg;
					print_r($resmsg_arr);
					echo '<br><br>';
				}
				curl_close($ch);
			
				if(count($resmsg_arr) && (!count($resmsg_arr['data']) || strtolower($resmsg_arr['data']['popup_type']) == 'edit'))
				{
					if(strtolower($resmsg_arr['data']['popup_type']) == 'edit')
					{
						
						//$catid_to_remove_arr = explode('|~|',$resmsg_arr['data']['catid']);
						
						$response['code'] = -1;
						$response['catname'] = $resmsg_arr['data']['catname'];
						
						return $response;
					}
					
				}
			
			}
			
			$total_category_details = " source catid :: ".$source_data[catidlineage]." source ncatid :: ".$source_data[catidlineage_nonpaid]." dest catid :: ".$dest_data[catidlineage]." dest ncatid :: ".$dest_data[catidlineage_nonpaid];
			
			$sql_insert_log = "INSERT INTO tbl_contract_merging_logs(`parentid`,`contract_details`,`category_details`,`other_details`,`entry_date`,`source`,`data_city`) VALUES
							   ('".$dest_parentid."','','".addslashes(json_encode($total_category_details))."','".addslashes($resmsg)."','".date('Y-m-d H:i:s')."','De-Duplication Updation','".$data_city."')";
		//	$res_insert_log = $this->local -> query_sql($sql_insert_log);
			
			if($this->trace) 
			{
				echo '<br> <pre>catid to remove :: ';print_r($catid_to_remove_arr);
				echo '<br> <pre> final catids :: '; print_r($dest_catidlineage_final_arr);
				echo '<br> <pre> final nonpaid catids :: ';print_r($dest_nonpaid_catidlineage_final_arr);//die;
			}	
			
			if( trim($dest_data['tag_catid']) == '' && ( trim($source_data['tag_catid'])  && ((in_array($source_data['tag_catid'], $dest_catidlineage_final_arr) || in_array($source_data['tag_catid'], $dest_nonpaid_catidlineage_final_arr))) ) )
			{
				$post_arr['tag_catid']	 =  $source_data['tag_catid'];
				
				$post_arr['tag_catname'] =  $source_data['tag_catname'];
			}
			
			if(count($dest_catidlineage_final_arr))
			{
				if(count($catid_to_remove_arr)>0)
				{
					foreach($dest_catidlineage_final_arr as $key=>$value)
					{
						if(in_array($value,$catid_to_remove_arr))
						{
							unset($dest_catidlineage_final_arr[$key]);
						}
					}
				}
				$post_arr['category']				= '/'.implode("/,/",$dest_catidlineage_final_arr).'/';
				
			
			}
			if(count($dest_nonpaid_catidlineage_final_arr))
			{
				if(count($catid_to_remove_arr)>0)
				{
					foreach($dest_nonpaid_catidlineage_final_arr as $key=>$value)
					{
						if(in_array($value,$catid_to_remove_arr))
						{
							unset($dest_nonpaid_catidlineage_final_arr[$key]);
						}
					}
				}
				
				$post_arr['catidlineage_nonpaid'] 	 = '/'.implode("/,/",$dest_nonpaid_catidlineage_final_arr).'/';
				
				$nonpaidCatStr = implode(",",$dest_nonpaid_catidlineage_final_arr);
				//$natNonPaid = "SELECT GROUP_CONCAT(distinct national_catid) as natCat FROM d_jds.tbl_categorymaster_generalinfo WHERE catid IN (".$nonpaidCatStr.")";
				//$qryNatNonPaid =	parent::execQuery($natNonPaid, $this->conn_iro);

				$cat_params = array();
				$cat_params['page'] ='duplicate_freeze_check_class';
				$cat_params['data_city'] 	= $this->data_city;
				$cat_params['return']		= 'national_catid';				

				$where_arr  	=	array();
				if(count($dest_nonpaid_catidlineage_final_arr)>0){
					$where_arr['catid']		= $nonpaidCatStr;
					$cat_params['where']	= json_encode($where_arr);
					$cat_res 		=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
					$cat_res_arr 	= array();
					if($cat_res!=''){
						$cat_res_arr = json_decode($cat_res,TRUE);
					}
				}
				$nat_catid_arr = array();
				if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
				{
					//$rowNatNonPaid = mysql_fetch_assoc($qryNatNonPaid);
					foreach ($cat_res_arr['results'] as $key => $cat_arr) {
						$nat_catid_arr[]= $cat_arr['national_catid'];
					}
				}
				$post_arr['national_catidlineage_nonpaid'] = "/".implode('/,/',array_unique($nat_catid_arr))."/";
				
			}
			
			
			$post_arr['parentid'] 	 = $dest_parentid;
			$post_arr['companyname'] = trim($dest_data['companyname']);
			$post_arr['sphinx_id'] 	 = trim($dest_data['sphinx_id']);
			$post_arr['ucode']		 = ($this->cron) ? 'De-Duplication Cron':'De-Duplication Updation';
			$post_arr['data_city'] 	 = $data_city;
			if(defined('REMOTE_CITY_MODULE'))
			{
				$post_arr['is_remote'] = 'REMOTE';
			}
			
			if($this->trace) 
			{
				echo '<br> posting data :: <pre>';
				print_r($post_arr);
			}
			
			$jdbox_ip_url	=	$this->urldetails['jdbox_url'];
			//print_r($urldetails);
				
			// Calling new API to insert into new table for Nonpaid contracts
			if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']))
			{
				$curl_url_insert 	= $jdbox_ip_url."insert_api.php";
			}
			else
			{
				//$curl_url 	= "http://". $_SERVER['HTTP_HOST']."/jdbox/insert_api.php";
				$curl_url_insert 	= "http://vishalvinodrana.jdsoftware.com/jdbox/insert_api.php";
			}
			
			if($this->trace) 
			echo '<br><br> curl url :: '.$curl_url_insert;
			//die('heree');
			$ch 		= curl_init();
			curl_setopt($ch, CURLOPT_URL, $curl_url_insert);
			curl_setopt($ch, CURLOPT_POST      ,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS ,$post_arr);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$resmsg_insert = curl_exec($ch);		
			
			if($this->trace) 
			{
				echo '<br><br>';
				echo '<br><br>res :: '.$resmsg_insert;
			}
			curl_close($ch);
			//die('all done');
		}
			
	}
	
	function checkPaidStatus($parentid)
	{
		$sql = "SELECT parentid,campaignid,balance FROM tbl_companymaster_finance WHERE parentid='".$parentid."' AND campaignid IN (1,2) AND (balance >0 OR (expired =1 AND DATEDIFF(NOW(),expired_on) < 90) )";
		$res = parent::execQuery($sql, $this->conn_fnc);
		if($res && mysql_num_rows($res))
		{
			return true;
		}else{
			return false;
		}
	}

	

}	
?>
