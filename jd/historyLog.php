<?php 
class contractLog extends DB
{
	private $parentid, $module, $userid;
	private $logflag; /* 0 = reset level, 1 = taken old logs, 2 = taken new logs */
	private $showflag; /* variable for deciding whether to show the logs or the whole process to run*/
	private $oldGeneralDetails,$newGeneralDetails;   /* details array */
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	
	function __construct($pid, $mod, $uid, $data_city, $flagValue = 0) {
		$this->parentid		= $pid;
		$this->module		= $mod;
		$this->userid		= $uid;
		$this->logflag 		= $flagValue;
		$this->datacity		= $data_city;
		$this->setServers();
		$this->categoryClass_obj = new categoryClass();
		$this->companyClass_obj  = new companyClass();
		
		$this->LogCurrentInfo()	;
	}
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->datacity), $this->dataservers)) ? strtolower($this->datacity) : 'remote');
		
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->dbConDjds  		= $db[$data_city]['d_jds']['master'];
		$this->dbConTmeJds 		= $db[$data_city]['tme_jds']['master'];
		$this->dbConFin    		= $db[$data_city]['fin']['master'];
		$this->dbConIdc   		= $db[$data_city]['idc']['master'];
		$this->dbConReseller	= $db['reseller']['master'];					
	}
	function __destruct()
	{
		if($this->logflag != 2)
		{
			$this->updateLog();
		}
		
		unset($this->parentid);
		unset($this->module);
		unset($this->userid);
		unset($this->logflag);
		unset($this->showflag);
	}

	function updateLog()
	{
		if($this->logflag == 1)
		{
			$this->LogCurrentInfo();		
		}	
		
		if($this->logflag == 2)
		{
			
			$paidstatus 	= $this->newGeneralDetails['paid'];
			$compname		= $this->newGeneralDetails['companyname'];
			$updatedOn		= $this->newGeneralDetails['updatedOn'];
			$compname1		= $this->oldGeneralDetails['companyname'];
			$pincode_new	=$this-> newGeneralDetails['pincode'];
			$latitude_new	=$this-> newGeneralDetails['latitude'];
			$longitude_new	=$this-> newGeneralDetails['longitude'];
			
			$pincode_old	=$this-> oldGeneralDetails['pincode'];
			$latitude_old	=$this-> oldGeneralDetails['latitude'];
			$longitude_old	=$this-> oldGeneralDetails['longitude'];
			$this->removematchlog();
			
			$this->companyNameChange($compname1, $compname, $pincode_old, $pincode_new, $latitude_old, $latitude_new, $longitude_old, $longitude_new);

			$sql_insert ="INSERT INTO tbl_contract_update_trail SET
									parentid				= '".$this->parentid."',
									update_time				= '".date('Y-m-d H:i:s')."',
									updated_by				= '".$this->userid."',
									paidstatus				= '".$paidstatus."',
									compname				= '".addslashes(stripslashes($compname))."',
									business_details_old	= '".http_build_query($this->oldGeneralDetails)."',
									business_details_new	= '".http_build_query($this->newGeneralDetails)."' ";

			$res_insert =	parent::execQuery($sql_insert, $this->dbConDjds);
			
			$this->oldGeneralDetails = array();
			$this->newGeneralDetails = array();					
			$this->logflag =0;
		}
	}
	
	function removematchlog()
	{
		if($this->logflag == 2)
		{
			if(count($this->oldGeneralDetails) > 0)
			{
				foreach($this->newGeneralDetails as $key => $value)
				{
					if($key != 'catList')
					{
						if($value == $this->oldGeneralDetails[$key])
						{
							unset($this->oldGeneralDetails[$key]);
							unset($this->newGeneralDetails[$key]);
						}
					}
				}
			}					
		}
		return false;
	}
	
	function LogCurrentInfo()
	{
		$general_log_array = $this->LogGeneralDetails();
		
		switch($this->logflag)
		{
			case '0':
				$this->oldGeneralDetails = $general_log_array;
				$this->logflag = 1;					
				break;
			case '1':
				$this->newGeneralDetails = $general_log_array;				
				$this->logflag = 2;			

				break;
		}
	}
	function LogGeneralDetails()
	{
			$general_log_array	=	array();
			$sqlGen	=	"SELECT * FROM tbl_companymaster_generalinfo WHERE parentid='".$this->parentid."'";
			
			$comp_params = array();
			$comp_params['data_city'] 	= $this->datacity;
			$comp_params['table'] 		= 'gen_info_id';		
			$comp_params['parentid'] 	= $this->parentid;
			$comp_params['fields']		= 'nationalid,sphinx_id,regionid,companyname,parentid,docid,master_id,country,state,city,display_city,area,area_display,subarea,office_no,building_name,street,street_direction,street_suffix,landmark,landmark_custom,pincode,pincode_addinfo,latitude,longitude,latitude_actual,longitude_actual,geocode_accuracy_level,full_address,stdcode,landline,dialable_landline,landline_display,dialable_landline_display,landline_feedback,mobile,mobile_admin,dialable_mobile,mobile_display,dialable_mobile_display,mobile_feedback,mobile_feedback_nft,fax,tollfree,tollfree_display,email,email_display,email_feedback,sms_scode,website,website_non_display,contact_person,contact_person_display,callconnect,virtualNumber,dialable_virtualnumber,virtual_mapped_number,blockforvirtual,othercity_number,paid,displayType,company_callcnt,company_callcnt_rolling,data_city,master_data_city,hide_address,helpline,helpline_display,pri_number,cc_status,von_info,call_fwd_num,did_num,reach_count,reach_count_rolling,unique_user_rolling,messenger_enabled,category_count,pincode_count';
			$comp_params['action']		= 'fetchdata';
			$comp_params['page']		= 'historyLog';
			$comp_params['skip_log']	= 1;

			$comp_api_res 	= '';
			$comp_api_arr	= array();
			$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
			if($comp_api_res!=''){
				$comp_api_arr 	= json_decode($comp_api_res,TRUE);
			}
			
			if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['gen_info_id']=='1')
			{
				$genArray 	= $comp_api_arr['results']['data'][$this->parentid];	
				//$genArray	=	mysql_fetch_assoc($resGen);		
			}
			//$resGen	=	parent::execQuery($sqlGen, $this->dbConIro);
						
			$sqlExt	=	"SELECT contact_person_addinfo,attributes,attributes_edit,attribute_search,social_media_url,turnover,working_time_start,working_time_end,payment_type,year_establishment,accreditations,certificates,no_employee,business_group,email_feedback_freq,statement_flag,alsoServeFlag,averageRating,ratings,web_ratings,number_of_reviews,group_id,catidlineage,catidlineage_search,national_catidlineage,national_catidlineage_search,hotcategory,flags,vertical_flags,business_assoc_flags,map_pointer_flags,guarantee,Jdright,LifestyleTag,contract_calltype,batch_group,createdby,createdtime,datavalidity_flag,deactflg,display_flag,flgActive,flgApproval,freeze,mask,future_contract_flag,hidden_flag,lockDateTime,lockedBy,temp_deactive_start,temp_deactive_end,micrcode,prompt_cat_temp,promptype,referto,serviceName,srcEmp,telComm,newbusinessflag,tme_code,original_creator,original_date,updatedBy,updatedOn,backenduptdate,catidlineage_nonpaid,helpline_flag,closedown_flag as company_status,closedown_date,fb_prefered_language,trending_flag,tag_catid,tag_catname,misc_flag FROM tbl_companymaster_extradetails WHERE parentid='".$this->parentid."'";

			//$resExt	=	parent::execQuery($sqlExt, $this->dbConIro);
			
			//~ if($resExt && mysql_num_rows($resExt))
			//~ {
					//~ $extArray	=	mysql_fetch_assoc($resExt)	;
			//~ }
		   $comp_params = array();
		   $comp_params['data_city'] 	= $this->datacity;
		   $comp_params['table'] 		= 'extra_det_id';		
		   $comp_params['parentid'] 	= $this->parentid;
		   $comp_params['fields']		= 'contact_person_addinfo,attributes,attributes_edit,attribute_search,social_media_url,turnover,working_time_start,working_time_end,payment_type,year_establishment,accreditations,certificates,no_employee,business_group,email_feedback_freq,statement_flag,alsoServeFlag,averageRating,ratings,web_ratings,number_of_reviews,group_id,catidlineage,catidlineage_search,national_catidlineage,national_catidlineage_search,hotcategory,flags,vertical_flags,business_assoc_flags,map_pointer_flags,guarantee,Jdright,LifestyleTag,contract_calltype,batch_group,createdby,createdtime,datavalidity_flag,deactflg,display_flag,flgActive,flgApproval,freeze,mask,future_contract_flag,hidden_flag,lockDateTime,lockedBy,temp_deactive_start,temp_deactive_end,micrcode,prompt_cat_temp,promptype,referto,serviceName,srcEmp,telComm,newbusinessflag,tme_code,original_creator,original_date,updatedBy,updatedOn,backenduptdate,catidlineage_nonpaid,helpline_flag,closedown_flag as company_status,closedown_date,fb_prefered_language,trending_flag,tag_catid,tag_catname,misc_flag';
		   $comp_params['action']		= 'fetchdata';
		   $comp_params['page']			= 'historyLog';
		   $comp_params['skip_log']		= 1;
			
		   $comp_api_res 	= '';
		   $comp_api_arr	= array();
		   $comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
		   if($comp_api_res!=''){
			   $comp_api_arr 	= json_decode($comp_api_res,TRUE);
		   }
		   
		   if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['extra_det_id']=='1')
		   {
				$extArray 	= $comp_api_arr['results']['data'][$this->parentid]; 						
		   }
			
			if(trim($extArray['serviceName']) == '~~ ~~ ~~')
			{
				$extArray['serviceName'] = '';
			}
			if(trim($extArray['working_time_start']) == ',')
			{
				$extArray['working_time_start'] = '';
			}
			if(trim($extArray['working_time_end']) == ',')
			{
				$extArray['working_time_end'] = '';
			}
		
			$catids		  = preg_replace("/[^ 0-9 ]/", ' ', $extArray['catidlineage']);
			$catidArray	  = explode(' ',$catids);
			$catidArray	  = array_merge(array_filter($catidArray));
			$catidString  = implode(',',$catidArray);
			
			if($catidString) {
				
				$sqlCatName = "SELECT GROUP_CONCAT(DISTINCT a.category_name ORDER BY a.category_name) AS catName FROM tbl_categorymaster_generalinfo a JOIN tbl_categorymaster_parentinfo b USING (catid)  WHERE (a.catid IN (".$catidString.") ) and   ((a.mask_status=0 ) OR a.category_source = 1 OR (a.mask_status=1 AND a.display_flag&2=2) ) AND (a.biddable_type='1' OR (a.biddable_type=0 AND b.parent_flag=1)) AND a.isdeleted = 0";

				//$qryCatName		=	parent::execQuery($sqlCatName, $this->dbConDjds);
				$cat_params = array();
				$cat_params['page'] 		= 'historyLog';
				$cat_params['parentid'] 	= $this->parentid;
				$cat_params['data_city'] 	= $this->datacity;
				$cat_params['return']		= 'catid,category_name';

				$where_arr  	=	array();
				if($catidString!=''){
					$where_arr['catid']				= $catidString;
					$where_arr['biddable_type']	 	= '1';					
					$where_arr['mask_status']	 	= '0';					
					$where_arr['isdeleted']	 		= '0';					
					$cat_params['where']	= json_encode($where_arr);
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
				}
				$cat_res_arr = array();
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}
				
				if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
				{
					//$rowCatName	=	mysql_fetch_assoc($qryCatName);
					$category_name_arr  = array();
					foreach($cat_res_arr['results'] as $key =>$cat_arr){
						$category_name_arr[] = $cat_arr['category_name'];
					}
					if(count($category_name_arr)>0){
						$catName =	implode(",",$category_name_arr);
					}
				}
				
				$extArray['catList']	=	$catName;
			}
			
			// catList_NonPaid start
			if(!empty($extArray['catidlineage_nonpaid']))
			{
				$catids_nonpaid		  = preg_replace("/[^ 0-9 ]/", ' ', $extArray['catidlineage_nonpaid']);
				$catid_nonpaid_Array  = explode(' ',$catids_nonpaid);
				$catid_nonpaid_Array  = array_merge(array_filter($catid_nonpaid_Array));
				$catid_nonpaid_String = implode(',',$catid_nonpaid_Array);
				
				if($catid_nonpaid_String) {
					
					$sqlCatName1 = "SELECT GROUP_CONCAT(DISTINCT a.category_name ORDER BY a.category_name) AS catName FROM tbl_categorymaster_generalinfo a JOIN tbl_categorymaster_parentinfo b USING (catid)  WHERE (a.catid IN (".$catid_nonpaid_String.") ) and   ((a.mask_status=0 ) OR a.category_source = 1 OR (a.mask_status=1 AND a.display_flag&2=2) ) AND (a.biddable_type='1' OR (a.biddable_type=0 AND b.parent_flag=1)) AND a.isdeleted = 0";

					//$qryCatName1		=	parent::execQuery($sqlCatName1, $this->dbConDjds);
					$cat_params = array();
				$cat_params['page'] 		= 'historyLog';
				$cat_params['parentid'] 	= $this->parentid;
				$cat_params['data_city'] 	= $this->datacity;
				$cat_params['return']		= 'catid,category_name';
				
				$cat_res = "";
				$where_arr  	=	array();
				if($catid_nonpaid_String!=''){
					$where_arr['catid']				= $catid_nonpaid_String;
					$where_arr['biddable_type']	 	= '1';					
					$where_arr['mask_status']	 	= '0';					
					$where_arr['isdeleted']	 		= '0';					
					$cat_params['where']	= json_encode($where_arr);
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
				}
				$cat_res_arr = array();
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}
					
					if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
					{
						//$rowCatName1	=	mysql_fetch_assoc($qryCatName1);
						$category_name_arr  = array();
						foreach($cat_res_arr['results'] as $key =>$cat_arr){
							$category_name_arr[] = $cat_arr['category_name'];
						}
						if(count($category_name_arr)>0){
							$catName =	implode(",",$category_name_arr);
						}
					}
					
					$extArray['catList_NonPaid']	=	$catName;
				}
			}
			// catList_NonPaid end
			
			

			$sqladdInfo	=	"SELECT add_infotxt FROM tbl_comp_addInfo WHERE parentid='".$this->parentid."' ORDER BY lockDateTime DESC LIMIT 1";
			$resaddInfo	=	parent::execQuery($sqladdInfo, $this->dbConDjds);
			if($resaddInfo && mysql_num_rows($resaddInfo)>0)
			{
				$rowaddInfo	=	mysql_fetch_assoc($resaddInfo);
				$extArray['Add_Info_Text']	=	$rowaddInfo['add_infotxt'];
			}
			
			//fb_prefered_language start			
			$sqlFB 	= 	"SELECT language FROM db_iro.tbl_language_master WHERE language_id = '".$extArray['fb_prefered_language']."'";
			$resFB	=	parent::execQuery($sqlFB, $this->dbConDjds);
			if($resFB && mysql_num_rows($resFB) > 0)
			{
				$rowFB	=	mysql_fetch_assoc($resFB);
				$extArray['fb_prefered_language']	=	$rowFB['language'];
			}			
			//fb_prefered_language end			
			
			//photo/video option
			$extArray['owner_content_preference']	=	 $this->photo_option_log(); 		

			if(!empty($genArray) && !empty($extArray))
			{
				$general_log_array	=	array_merge($genArray,$extArray);
			}
			
			return $general_log_array;
	}
		
	function newContractLog($pid)
	{
		$this->updateLog();		
		if($pid != '')
		{		
			$this->parentid 	= $pid;
			$this->logflag 		= 0;
			$this->LogCurrentInfo()	;
		}
	}
	function companyNameChange($old_company,$new_company,$pincode_old, $pincode_new, $latitude_old, $latitude_new, $longitude_old, $longitude_new)
	{
		$compname_new	= '';
		$doc_id = '';
		$city = '';
		$data_city = '';
		$compname_new	= trim($new_company);
		$compname_new 	= preg_replace('/\s+/', '', $compname_new);
		
		$compname_new = trim($compname_new);
		
		$compname_old	= '';
		$compname_old   = trim($old_company);
		$compname_old 	= preg_replace('/\s+/', '', $compname_old);
		
		$compname_old = trim($compname_old);
		
		$selDocid = 'SELECT docid FROM db_iro.tbl_id_generator WHERE parentid="'.$this->parentid.'" ';
		$resDocid = parent::execQuery($selDocid, $this->dbConIro);
		if($resDocid && mysql_num_rows($resDocid) > 0)
		{
			$rowDocid = mysql_fetch_assoc($resDocid);
			if($rowDocid['docid'])
			{
				$doc_id = $rowDocid['docid'];
			}
		}
		
		//$selCity = 'SELECT city,data_city FROM db_iro.tbl_companymaster_generalinfo WHERE parentid="'.$this->parentid.'" ';
		//$resCity = parent::execQuery($selCity, $this->dbConIro);
		$comp_params = array();
		$comp_params['data_city'] 	= $this->datacity;
		$comp_params['table'] 		= 'gen_info_id';		
		$comp_params['parentid'] 	= $this->parentid;
		$comp_params['fields']		= 'city,data_city';
		$comp_params['action']		= 'fetchdata';
		$comp_params['page']		= 'historyLog';

		$comp_api_res 	= '';
		$comp_api_arr	= array();
		$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
		if($comp_api_res!=''){
			$comp_api_arr 	= json_decode($comp_api_res,TRUE);
		}
		
		if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['gen_info_id']=='1')
		{
			//$rowCity = mysql_fetch_assoc($resCity);
			$rowCity   =	$comp_api_arr['results']['data'][$this->parentid];	
			if ($rowCity['city'])
			{
				$city = $rowCity['city'];
			}
			if ($rowCity['data_city'])
			{
				$data_city = $rowCity['data_city'];
			}
		}
		
		if(((strtolower($compname_new) != strtolower($compname_old))  && ($compname_new) && ($compname_old)) ||( ($pincode_old) && ($pincode_new) && ($pincode_old != $pincode_new)) || (($latitude_old) && ($latitude_new) && (doubleval($latitude_old) != doubleval($latitude_new))) || ( ($longitude_old) && ($longitude_new) && (doubleval($longitude_old) != doubleval($longitude_new))))
		{
			$sql_insertNameChange = "INSERT INTO tbl_contract_change_details SET 
									parentid				= '".$this->parentid."',
									docid					= '".$doc_id."' ,
									city					= '".$city."' ,
									data_city				= '".$data_city."' ,
									update_time				= '".date('Y-m-d H:i:s')."',
									updated_by				= '".$this->userid."',
									paidstatus				= '0',
									compname_old			= '".addslashes(stripslashes($old_company))."',
									compname_new			= '".addslashes(stripcslashes($new_company))."',
									done_flag				= '0',
									pincode_old				= '".$pincode_old."' ,
									pincode_new				= '".$pincode_new."' ,
									latitude_old			= '".$latitude_old."' ,
									latitude_new			= '".$latitude_new."' ,
									longitude_old			= '".$longitude_old."' ,
									longitude_new			= '".$longitude_new."' 				
									";
			$res_NameChange = parent::execQuery($sql_insertNameChange, $this->dbConIro);
		}
	}		
	function photo_option_log()
	{
		include_once('library/configclass.php');
		$configclassobj= new configclass();		
		
		$urldetails		=	$configclassobj->get_url(urldecode($this->datacity));
		$ph_url = $urldetails['jdbox_service_url']."location_api.php";
		
		$param_ph = Array();
		$param_ph['rquest'] 	=	'photo_option';
		$param_ph['parentid'] 	=	$this->parentid;
		$param_ph['data_city'] 	=	$this->datacity;
		$param_ph['type'] 		=	'get';
		
		$ch 		= curl_init();
		curl_setopt($ch, CURLOPT_URL, $ph_url);
		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS ,$param_ph);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$resdata = curl_exec($ch);
		curl_close($ch);
		$photo_data = json_decode($resdata,true);
		
		return $photo_data['result']['option'];
	}	
}
?>
