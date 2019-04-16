<?php

/**
 * Filename : insertLiveClass.php
 * Date		: 19/08/2013
 * Author	: Neelam Rasal
 * Purpose	: This file is used to insert data into all live tables
 * param	: $insert_arr - this array will contain all fields data which need to insert into live tables
 * */
class insertToLive extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $dbConIdc    	= null;
	var  $dnc  			= null;
	var  $insert_arr  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	var  $tableno		= null;
	
	function __construct($insert_arr)
	{		
		$this->insert_arr = $insert_arr;
		$this->mongo_obj 	= new MongoClass();
		$this->setServers();
		$this->categoryClass_obj = new categoryClass();
		$this->companyClass_obj  = new companyClass();
		/* Code for companymasterclass logic starts */
		if($this->insert_arr['is_remote'] == 'REMOTE')
		{
			$this->is_split = FALSE;	 // when split table goes live then make it TRUE		
		}
		else
		{
			$this->is_split = FALSE;			
		}

		if(trim($this->insert_arr['parentid']) != "")
		{
			$this->parentid  = $this->insert_arr['parentid']; //initialize paretnid
		}
		
		//$this->setCompanyTables();
		
		if(trim($this->insert_arr['data_city']) != "" && $this->insert_arr['data_city'] != null)
		{
			$this->data_city  = $this->insert_arr['data_city']; //initialize datacity
		}
		$this->setMapTableindex();

		/* Code for companymasterclass logic ends */		
	}
	
	function setMapTableindex() // For companymasterclass
	{
		if($this->is_split === FALSE)
		{
			$this->tableno = "";
			return 1; // integrating table mapping now so commenting it
		}

		$sql = "SELECT tableno FROM tbl_company_datacity_mapping WHERE data_city = '".$this -> data_city."'";
		$res = parent::execQuery($sql, $this->dbConIro);
		if(mysql_num_rows($res)>0)
		{
			$res_arr= mysql_fetch_assoc($res);
			if(trim($res_arr['tableno'])!="")
			{
				$this->tableno = $res_arr['tableno'];
				return 1;
			}
			else
			{
				mail("shitalpatil@justdial.com","data_city is absent in tbl_company_datacity_mapping table data_city=".$this -> data_city,"Data city mapping");
			}
		}
		else
		{
			mail("shitalpatil@justdial.com","data_city is absent in tbl_company_datacity_mapping table data_city=".$this -> data_city, "Data city mapping");
		}
	}
	
	function getFinalTableName($tablename, $tableno) // For companymasterclass
	{
		if(!empty($tableno))
		{
			$returntblname = $tablename."_".$tableno; //when split logic will be implemented it will be removed
		}
		else
		{
			$returntblname = $tablename;
		}
		
		return $returntblname;
	}
	
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->insert_arr['data_city']), $this->dataservers)) ? strtolower($this->insert_arr['data_city']) : 'remote');
		
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->dbConDjds  		= $db[$data_city]['d_jds']['master'];
		$this->dbConDjds_slave	= $db[$data_city]['d_jds']['slave'];
		$this->dbConIro_slave	= $db[$data_city]['iro']['slave'];
		$this->dbConIdc   		= $db[$data_city]['idc']['master'];
		$this->dnc   			= $db['dnc'];
		$this->dbConTmeJds 		= $db[$data_city]['tme_jds']['master'];
		//$this->dbConFin    		= $db[$data_city]['fin']['master'];
		//$this->dbConReseller	= $db['reseller']['master'];
		$this->finance   		= $db[$data_city]['fin']['master'];
		$this->data_correction  		= $db[$data_city]['data_correction']['master'];
	}
	
	function validateRules()
	{
		if(!empty($this->insert_arr))
		{
			$err_duplicate 			=	$this->checkDuplicateNum();
			
			$err_companyname		=	$this->checkCompanyname();
			
			$err_contactnumber		=	$this->checkContactNumber();
			
			$err_address			=	$this->checkAddress();
			
			$err_category			=	$this->checkCategory();
			
			$err_companycategory	=	$this->checkCompanyCategory();
			
			$err_catsynonym			=	$this->checkCatsynonym();
			
			$err_brandname			=	$this->checkBrandname();
			
			$err_blocknum			=	$this->checkBlockednum();
			
			$err_virtual			=	$this->checkVitualNumber();
			
			$err_tollfree			=	$this->checkTollfree();
			
			$err_contactperson		=	$this->checkContactPerson();
			
			$err_email				=	$this->checkEmail();
			
			$err_url				=	$this->checkUrl();
			
			$arr_error				=	array($err_duplicate, $err_companyname, $err_contactnumber, $err_address, $err_category, $err_companycategory, $err_catsynonym, $err_brandname, $err_blocknum, $err_virtual,$err_tollfree, $err_contactperson, $err_email, $err_url);
			
			$arr_error		=	array_values(array_filter($arr_error));
			
			return $arr_error;
		}
	}
	
    function autoRejectDownsell(){
        $parentid   =$this->insert_arr['parentid'];
        $tme        =$this->insert_arr['ucode'];
        if(isset($this->insert_arr['source'])){
            $this->insert_arr['source']=$this->insert_arr['source'];
        }else{
            $this->insert_arr['source']='SAVE';
        }
        if((strtoupper($this->insert_arr['source']) == 'DE' || strtoupper($this->insert_arr['source']) == 'CS' || strtoupper($this->insert_arr['source']) == 'SAVE')){
            $now    = date('Y-m-d H:i:s');
            $stmt   = parent::execQuery("UPDATE online_regis.downsell_trn SET backend_update_flag=1, status=2, updated_at='$now', updated_by='Back end' WHERE parentid='$parentid' AND status=0 AND delete_flag='0'", $this->dbConIdc);
            $err_msg = 'Down sell request auto rejected';

            $insertData = parent::execQuery("INSERT INTO tbl_lock_company set parentId='$parentid',updateBy='$tme',updatedDate=now(),UpdateFlag='1' on duplicate key update parentId='$parentid',updateBy='$tme',updatedDate=now(),UpdateFlag='1'", $this->dbConDjds);
        }else{
            $err_msg = 'Down sell request remains the same since the request came from '.$this->insert_arr['source'];
        }
    }
	function checkDuplicateNum()
	{
		$err_msg	=	'';
		if(!empty($this->insert_arr))
		{
			$numbers 	= array();		
			$numbers[] 	= $this->insert_arr['landline'];
			$numbers[] 	= $this->insert_arr['mobile'];
			
			$number_filter 	= array_values(array_filter($numbers));
			$numbers_cs 	= implode(',',$number_filter);
			$city			= $this->insert_arr['data_city'];
			
			if(!empty($numbers_cs) && !empty($city))
			{
				$url 			= "http://".WEB_SERVICES_API."/web_services/PhoneSearch.php?phone_nos=".$numbers_cs."&city=".$city;
				$json_parentids = json_decode(getData($url),true);
				
				if(!empty($json_parentids)) // if found through API, mark it as duplicate
				{
					$err_msg = 'Duplicate number found';
					$arr_errors['error'][] = $err_msg;
				}
			}
			
			/*if(!empty($numbers) && !empty($city))
			{
				$json_parentids = json_decode(getData("http://192.168.1.121/web_services/PhoneSearch.php?phone_nos=$numbers&city=$city"),true);
				$str_docids 	= getDocids($json_parentids);	// get comma separated parentids
				
				if(!empty($str_docids))
				{
					$returned_content = getData("http://192.168.1.121/web_services/CompanyDetails.php?docid=$str_docids&json=1");
					if(!empty($returned_content))
					{
						$err_msg = 'Duplicate number found';
						$arr_errors['error'][] = $err_msg;
						$arr_errors['duplicate_json'][] = $returned_content;
					}
				}
			}*/
		}
		return $err_msg;
	}
	
	function getDocids($json_parentids)
	{
		if(is_array($json_parentids) && count($json_parentids) > 0)
		{
			foreach($json_parentids as $key => $arr_vals)
			{
				$arr_docid[] = $arr_vals['docid'];
			}
			$im_docid	= implode(",",$arr_docid);		
			return $im_docid;
		}
		else
		{
			return false;
		}
	}
	
	function checkCompanyname()
	{
		$err_msg	=	'';
		if(!empty($this->insert_arr) && !empty($this->insert_arr['companyname']))
		{
			$companyname	=	$this->insert_arr['companyname'];
			
			$arr_string 	= array('www','http','https','mr', 'mrs', 'ms', 'dr');
			$count_words 	= 0;
			$cnt 			= count($arr_string);
			for($i=0; $i < $cnt; $i++)
			{
				$patt = "/\b".$arr_string[$i]."\b/i";
				if(preg_match($patt,$companyname))
				{
					$count_words++;
				}
			}
			
			$result_profainity 	= 	checkBadWords($companyname);
			
			$result_minlength	=	$this->checkMinLength($companyname, 4);
			
			$result_maxlength	=	$this->checkMaxLength($companyname, 120);
			
			$result_maxnum		=	$this->checkNumericDigit($companyname, 4);
			
			$result_repeatchar	=	$this->checkRepeatChar($companyname);
			
			$result_splchar		=	$this->checkCompSplChar($companyname);
			// Length of companyname is more thane 120 will be rejected
			if($result_maxlength == 'ERROR') 
			{
				$err_msg	= 'Company name length is more than 120 characters will be rejected';
				$arr_errors['error'][] = $err_msg;
				//continue;
			}
			//Company name contains more than 4 numerical characters will be rejected.
			elseif($result_maxnum == 'ERROR')
			{
				$err_msg	= 'Company name contains more than 4 numerical characters will be rejected';
				$arr_errors['error'][] = $err_msg;
				//continue;
			}
			// Company name contains special characters will be rejected.
			elseif($result_splchar == 'ERROR') 
			{
				$err_msg	= 'Company name contains special characters will be rejected';
				$arr_errors['error'][] = $err_msg;
				//continue;
			}
			// Company name length is less than 4 characters will be rejected.
			elseif($result_minlength == 'ERROR') 
			{
				$err_msg	= 'Company name length is less than 4 characters will be rejected';
				$arr_errors['error'][] = $err_msg;
				//continue;
			}
			//Company name contains repeated series of alphabets will be rejected (AAAAAA, CCCCC) (MAX 2 is allowed)
			elseif($result_repeatchar == 'ERROR') 
			{
				$err_msg	= 'Company name contains repeated series of alphabets will be rejected (AAAAAA, CCCCC)';
				$arr_errors['error'][] = $err_msg;
				//continue;
			}
			//Company name having Profrain words will not go live but commenced under data rejection module.
			elseif($result_profainity == 'Profanity')
			{
				$err_msg	= 'Company name contains Profain words';
				$arr_errors['error'][] = $err_msg;
				//continue;
			}
			// Company name contains characters like www. or http:// etc. wil be rejected
			// Company name contains salutaions will be rejected.
			elseif($count_words > 0) 
			{
				$err_msg	= 'Company name contains characters like www. or http:// etc. or contains salutaions will be rejected';
				$arr_errors['error'][] = $err_msg;
				//continue;
			}
		}
		return $err_msg;
	}
	
	function checkTollfree()
	{
		$err_msg = '';
		if(!empty($this->insert_arr['tollfree']))
		{
			$arr_tollfree	=	explode(',',$this->insert_arr['tollfree']);
			
			$cnt_tollfree	=	count($arr_tollfree);
			
			for($i=0; $i<$cnt_tollfree; $i++)
			{
				$tollfree	=	$arr_tollfree[$i];
				
				$err_cnt1	=	0;
				$err_cnt2	=	0;
				
				$result_minlength	=	$this->checkMinLength($tollfree, 8);
				
				$result_maxlength	=	$this->checkMaxLength($tollfree, 12);
				
				if($result_minlength == 'ERROR' || $result_maxlength == 'ERROR')
				{
					$err_cnt1 = $err_cnt1 + 1;
				}
				elseif((substr($tollfree, 0, 4) != '1800' && substr($tollfree, 0, 4) != '1860'))
				{
					$err_cnt2 = $err_cnt2 + 1;
				}
			}
			if($err_cnt1 > 0)
			{
				$err_msg	=	'Toll Free Nos will be 8-12 digit only';
				$arr_errors['error'][] = $err_msg;
			}
			
			if($err_cnt2 > 0)
			{
				$err_msg	=	'Tollfree Number should start with 1800 or 1860';
				$arr_errors['error'][] = $err_msg;
			}
		}
		return $err_msg;
	}
	
	function checkContactPerson() 
	{
		$err_msg = '';
		if(!empty($this->insert_arr['contact_person']))
		{
			$err_cnt1	=	0;
			$err_cnt2	=	0;
			$err_cnt3	=	0;
			
			$arr_contact_person	=	explode(',',$this->insert_arr['contact_person']);
			
			$cnt	=	count($arr_contact_person);
			
			for($i=0; $i<$cnt; $i++)
			{
				$contactperson_temp		=	$arr_contact_person[$i];
				// separating designation from name
				$arr_contactperson_temp	=	explode('(',$contactperson_temp);
				
				$contact_person			=	$arr_contactperson_temp[0];
				
				$result_contact_alpha	=	$this->checkOnlyAlphabets($contact_person);
				
				$result_repeatchar		=	$this->checkRepeatChar($contact_person);
				
				$result_repeattext		=	$this->checkRepeatText($contact_person);
				
				
				if($result_contact_alpha == 'ERROR')
				{
					$err_cnt1	=	$err_cnt1 + 1;
				}
				elseif($result_repeatchar == 'ERROR')
				{
					$err_cnt2	=	$err_cnt2 + 1;
				}
				elseif($result_repeattext == 'ERROR')
				{
					$err_cnt3	=	$err_cnt3 + 1;
				}
			}
			
			if($err_cnt1 > 0)
			{
				$err_msg	=	'Contact person should contain only alphabets';
				$arr_errors['error'][] = $err_msg;
			}
			if($err_cnt2 > 0)
			{
				$err_msg	=	'Contact person contains repeated series of alphabets will be rejected (AAAAAA, CCCCC)';
				$arr_errors['error'][] = $err_msg;
			}
			if($err_cnt3 > 0)
			{
				$err_msg	=	'Contact person contains repeated text';
				$arr_errors['error'][] = $err_msg;
			}		
		}
		return $err_msg;
	}
	
	function checkEmail()
	{
		$err_msg = '';
		if(!empty($this->insert_arr['email']))
		{
			$arr_email	=	explode(',',$this->insert_arr['email']);
			
			$err_cnt	=	0;
			
			$cnt = count($arr_email);
			
			for($i=0; $i<$cnt; $i++)
			{
				if(!filter_var($arr_email[$i], FILTER_VALIDATE_EMAIL))
				{
					$err_cnt = $err_cnt + 1;
				}
			}
			if($err_cnt > 0)
			{
				$err_msg = 'Invalid email id';
			}
			elseif(count($arr_email) !== count(array_unique($arr_email)))
			{
				$err_msg = 'Same email id is repeated';
				$arr_errors['error'][] = $err_msg;
			}
		}
		return $err_msg;
	}
	
	function checkUrl()
	{
		$err_msg	=	'';
		if(!empty($this->insert_arr['website']))
		{
			$arr_website	=	explode(',',$this->insert_arr['website']);
			
			$err_cnt	=	0;
			
			$cnt = count($arr_website);
			
			for($i=0; $i<$cnt; $i++)
			{
				if(!preg_match( '%^((https?://)|(www\.))([a-z0-9-].?)+(:[0-9]+)?(/.*)?$%i' ,$arr_website[$i]))
				{
					$err_cnt = $err_cnt + 1;
				}
			}
			if($err_cnt > 0)
			{
				$err_msg = 'Invalid website url';
				$arr_errors['error'][] = $err_msg;
			}
			elseif(count($arr_website) !== count(array_unique($arr_website)))
			{
				$err_msg 	= 	'Same website is repeated';
				$arr_errors['error'][] = $err_msg;
			}
			
		}
		return $err_msg;
	}
	
	function getSingular($str = '')
	{
		$s = array();
		$t = explode(' ',$str);
		$e = array('shoes'=>'shoe','glasses'=>'glass','mattresses'=>'mattress','mattress'=>'mattress','watches'=>'watch');
		$r = array('ss'=>false,'os'=>'o','ies'=>'y','xes'=>'x','oes'=>'o','ies'=>'y','ves'=>'f','s'=>'');
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
		return implode(' ',$s);
	}

	function checkCompanyCategory()
	{
		$err_msg 	= '';
		if(!empty($this->insert_arr['companyname']))
		{
			$business_name 	= trim($this->insert_arr['companyname']);
			$business_name 	= preg_replace('/\s+/', ' ', $business_name); 
			$b1				= strtolower($this->getSingular($business_name));
			$msg			= '';
			
			if(!preg_match('/([a-zA-Z0-9])/', $b1))
			{
				$err_msg = 'Invalid companyname';
				$arr_errors['error'][] = $err_msg;
			}
			else
			{
				//$sql			= "SELECT category_name as catname FROM tbl_categorymaster_generalinfo WHERE category_name = LOWER('".addslashes($business_name)."') OR category_name  = '".addslashes(trim($business_name))."' or category_name like '".addslashes($b1)."%' ";
				//$sql			= "SELECT category_name as catname FROM tbl_categorymaster_generalinfo WHERE category_name = '".addslashes(trim($business_name))."' OR category_name = '".addslashes($b1)."'";
				//$res_catname 	= parent::execQuery($sql, $this->dbConDjds_slave);
				//$res_catname 	= parent::execQuery($sql, $this->dbConDjds);
				//$num_rows		= mysql_num_rows($res_catname);
				$cat_params = array();
				$cat_params['page'] 		= 'insertLiveClass';
				$cat_params['data_city'] 	= $this->data_city;
				$cat_params['return']		= 'category_name';

				$where_arr  	=	array();
				if($business_name!=''){
					$where_arr['category_name']		= $business_name;
					$cat_params['where']			= json_encode($where_arr);
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
				}
				$cat_res_arr = array();
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}

				if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results']) > 0)
				{
					$err_msg = 'Companyname is matching with category';
					$arr_errors['error'][] = $err_msg;
				}
				/*else
				{
					//$sql_cat	= "SELECT category_name as catname FROM d_jds.tbl_categorymaster_generalinfo WHERE replace(category_name,' ','') = lower('".addslashes($business_name)."') OR replace(category_name,' ','') like '%".addslashes($business_name)."%' OR replace(category_name,' ','') like '%".addslashes($b1)."%'";
					$sql_cat ="SELECT category_name AS catname FROM d_jds.tbl_categorymaster_generalinfo
							WHERE category_name= '".addslashes(trim($business_name))."'
							OR REPLACE(category_name,' ','') = '".addslashes(trim($business_name))."'
							UNION
							SELECT category_name AS catname FROM d_jds.tbl_categorymaster_generalinfo
							WHERE category_name='".addslashes($b1)."'
							OR REPLACE(category_name,' ','') = '".addslashes($b1)."'";
					//$res_cat 	= parent::execQuery($sql_cat, $this->dbConDjds_slave);
					$res_cat 	= parent::execQuery($sql_cat, $this->dbConDjds);
					$num_rows	= mysql_num_rows($res_cat);
					if($res_cat && $num_rows > 0)
					{
						$err_msg = 'Companyname is matching with category';
						$arr_errors['error'][] = $err_msg;
					}
				}*/
			}
		}
		return $err_msg;
	}

	function checkCatsynonym()
	{
		$err_msg 	= '';
		if(!empty($this->insert_arr['companyname']))
		{
			$business_name 	= trim($this->insert_arr['companyname']);
			$business_name 	= preg_replace('/\s+/', ' ', $business_name); 
			$sql_cat_syn	= "SELECT synonym_name FROM tbl_synonym WHERE synonym_name = lower('".addslashes($business_name)."') OR synonym_name like '%".addslashes($business_name)."%'";
			//$res_cat_syn	= parent::execQuery($sql_cat_syn, $this->dbConDjds_slave);
			$res_cat_syn	= parent::execQuery($sql_cat_syn, $this->dbConDjds);
			$num_rows		= mysql_num_rows($res_cat_syn);
			if($res_cat_syn && $num_rows > 0)
			{
				$err_msg = 'Companyname is matching with synonym';
				$arr_errors['error'][] = $err_msg;
			}
		}
		return $err_msg;
	}

	function checkBrandname()
	{
		global $conn;
		$err_msg 	= '';
		if(!empty($this->insert_arr['companyname']))
		{
			$business_name 	= trim($this->insert_arr['companyname']);
			
			$companystr = strtolower($business_name); 
			$companystr = preg_replace("/[^A-Za-z0-9\s]/", " ", $companystr);
			
			$sql_brand	= "SELECT GROUP_CONCAT(brand_name separator '|~|') as brand_name, GROUP_CONCAT(source separator '|~|') as source FROM tbl_brand_names WHERE MATCH(brand_name) AGAINST('".$companystr."' IN BOOLEAN MODE) LIMIT 1";			
			$res_brand	= parent::execQuery($sql_brand, $this->dbConIro);
			$num_rows	= mysql_num_rows($res_brand);
			if($res_brand && $num_rows > 0)
			{
				$row = mysql_fetch_assoc($res_brand);
				$brand_name = trim($row['brand_name']);
				$brand_name = strtolower($brand_name);
				$source 	= trim($row['source']);
				$brand_name_arr = explode("|~|",$brand_name);
				$source_arr = explode("|~|",$source);
				$matched_brand = '';
				$matched_source = ''; 
				if(count($brand_name_arr)>0){
					foreach($brand_name_arr as $key => $value){
						if(strpos($companystr, $value) !== false) {
							$matched_brand = $value;
							$matched_source = $source_arr[$key];
							break;
						}
					}
				}
				if($matched_brand){
					$err_msg = 'Companyname is matching with brandname';
					$arr_errors['error'][] = $err_msg;
				}
			}
		}
		return $err_msg;
	}

	function checkBlockednum()
	{
		$err_msg	=	'';
		if(!$this->insert_arr['landline'] || !empty($this->insert_arr['mobile']))
		{			
			$arr_landline	= explode(",",trim($this->insert_arr['landline']));
			$arr_mobile		= explode(",",trim($this->insert_arr['mobile']));
			$arr_number		= array_values(array_filter(array_merge($arr_landline,$arr_mobile)));
			$numbers_cs 	= implode("','",$arr_number);
			
			$sql_block	= "SELECT reason FROM dnc.tbl_blockNumbers WHERE blocknumber IN ('".$numbers_cs."') AND block_status = '1'";
			$res_block	= parent::execQuery($sql_block, $this->dnc);
			$num_rows	= mysql_num_rows($res_block);
			if($res_block && $num_rows > 0)
			{
				$err_msg = 'Blocked number';
				$arr_errors['error'][] = $err_msg;
			}
		}
		return $err_msg;
	}

	function isVirtualNumber($landline,$CITY)
	{
		global $techinfo_pri_series;
		$CITY = strtoupper($CITY);
		
		if(intval($landline) > 0)
		{
			switch($CITY)
			{
				case 'SANAND'		: $CITY='AHMEDABAD';
									  break;	
				case 'AHMEDABAD'	: $CITY='AHMEDABAD';
									  break;	
				case 'BANGALORE'	: $CITY='BANGALORE';
									  break;	
				case 'CHENNAI'		: $CITY='CHENNAI';
									  break;	
				case 'NOIDA'		: $CITY='DELHI';
									  break;	
				case 'DELHI'		: $CITY='DELHI';
									  break;	
				case 'GHAZIABAD'	: $CITY='DELHI';
									  break;	
				case 'GURGAON'		: $CITY='DELHI';
									  break;	
				case 'FARIDABAD'	: $CITY='DELHI';
									  break;	
				case 'HYDERABAD'	: $CITY='HYDERABAD';
									  break;	
				case 'KOLKATA'		: $CITY='KOLKATA';
									  break;	
				case 'RAIGAD-MAHARASHTRA'	: $CITY='MUMBAI';
									  break;	
				case 'MUMBAI'		: $CITY='MUMBAI';
									  break;	
				case 'NAVI MUMBAI'	: $CITY='MUMBAI';
									  break;	
				case 'THANE'		: $CITY='MUMBAI';
									  break;	
				case 'PUNE'			: $CITY='PUNE';
									  break;	
			}
			if($CITY=='MUMBAI' || $CITY=='DELHI' || $CITY=='KOLKATA' || $CITY=='BANGALORE' || $CITY=='CHENNAI' || $CITY=='PUNE' || $CITY=='HYDERABAD' || $CITY=='AHMEDABAD')
			{

				foreach($techinfo_pri_series[$CITY] as $series)
				{
					if($landline >= $series['MIN'] && $landline <= $series['MAX'])
					{
						return false;
					}
				}
				return true;
			}
			else
			{
				return true;			
			}
		}
		return true;	
	}

	function checkVitualNumber()
	{		
		$landline		=	$this->insert_arr['landline'];
		$arr_landline	= 	explode(",",$landline);
		
		if(is_array($arr_landline) && !empty($arr_landline))
		{
			foreach($arr_landline as $number)
			{
				$dis_error = $this->isVirtualNumber($number, strtoupper($this->insert_arr['city']));
				if(!$dis_error)
				{
					$err_msg = "This landline number (".$number.") is not allowed since same number exist in our virtual number series, Please change number";
					$arr_errors['error'][] = $err_msg;
				}
			}
		}
		return $err_msg;
	}
	
	function checkContactNumber() // need to optimize
	{
		$err_msg	=	'';
		if(!empty($this->insert_arr))
		{
			$landline 	= $this->insert_arr['landline'];
			$mobile 	= $this->insert_arr['mobile'];
			
			$arr_landline 	= explode(',',$landline);
			
			$arr_mobile		= explode(',',$mobile);
			
			$tele_1		=	$arr_landline[0];
			$tele_2		=	$arr_landline[1];
			$tele_3		=	$arr_landline[2];
			$tele_4		=	$arr_landline[3];
			
			$mobile_1	=	$arr_mobile[0];
			$mobile_2	=	$arr_mobile[1];
			$mobile_3	=	$arr_mobile[2];
			$mobile_4	=	$arr_mobile[3];
			
			//Telephone or Mobile Numbers are containing characters other than numbers (0-9) will be rejected.
			if((!empty($tele_1) && !ctype_digit($tele_1)) || (!empty($tele_2) && !ctype_digit($tele_2)) || (!empty($tele_3) && !ctype_digit($tele_3)) || (!empty($tele_4) && !ctype_digit($tele_4)) || (!empty($mobile_1) && !ctype_digit($mobile_1)) || (!empty($mobile_2) && !ctype_digit($mobile_2)) || (!empty($mobile_3) && !ctype_digit($mobile_3)) || (!empty($mobile_4) && !ctype_digit($mobile_4)))
			{
				$err_msg	=	'Telephone or Mobile Numbers are containing characters other than numbers (0-9) will be rejected';
				$arr_errors['error'][] = $err_msg;
			}
			//Mobile Numbers Length is more than 10 digits will be rejected.
			elseif((!empty($mobile_1) && strlen($mobile_1) > 10) || (!empty($mobile_2) && strlen($mobile_2) > 10) || (!empty($mobile_3) && strlen($mobile_3) > 10) || (!empty($mobile_4) && strlen($mobile_4) > 10))
			{
				$err_msg	= 'Mobile Numbers Length is more than 10 digits will be rejected';
				$arr_errors['error'][] = $err_msg;
			}
			//Mobile Numbers are starting with 0 or 1 will be rejected.
			elseif($mobile_1[0] == '1' || $mobile_2[0] == '1' || $mobile_3[0] == '1' || $mobile_4[0] == '1' || $mobile_1[0] == '0' || $mobile_2[0] == '0' || $mobile_3[0] == '0' || $mobile_4[0] == '0')
			{
				$err_msg	= 'Mobile Numbers are starting with 0 or 1 will be rejected';
				$arr_errors['error'][] = $err_msg;
			}
			//Telephone Numbers length is less than 6 digits will be rejected.
			elseif((!empty($tele_1) && strlen($tele_1) < 6) || (!empty($tele_2) && strlen($tele_2) < 6) || (!empty($tele_3) && strlen($tele_3) < 6) || (!empty($tele_4) && strlen($tele_4) < 6))
			{
				$err_msg	= 'Telephone Numbers length is less than 6 digits will be rejected';
				$arr_errors['error'][] = $err_msg;
			}
			// Telephone & Mobile number fields are blank will be rejected.
			elseif(empty($tele_1) &&  empty($tele_2) && empty($tele_3) && empty($tele_4) && empty($mobile_1) && empty($mobile_2) && empty($mobile_3) && empty($mobile_4))
			{
				$err_msg	= 'Telephone & Mobile number fields are blank will be rejected';
				$arr_errors['error'][] = $err_msg;
			}
		}
		return $err_msg;
	}
	
	function checkAddress()
	{
		$err_msg	=	'';
		if(!empty($this->insert_arr))
		{
			$building_name 	= trim($this->insert_arr['building_name']);
			$street 		= trim($this->insert_arr['street']);
			$landmark 		= trim($this->insert_arr['landmark']);
			$area 			= trim($this->insert_arr['area']);
		
			//Total Data length is less 5 characters (Building_name + street + landmark + area) will be rejected.
			$full_address 	= $building_name.$street.$landmark.$area;
			
			$result_minlength			=	$this->checkMinLength($full_address, 5);
			
			$res_repeatchar_building	=	$this->checkRepeatChar($building_name);
			
			$res_repeatchar_street		=	$this->checkRepeatChar($street);
			
			$res_repeatchar_landmark	=	$this->checkRepeatChar($landmark);
			
			$res_repeatchar_area		=	$this->checkRepeatChar($area);
			
			if($result_minlength == 'ERROR')
			{
				$err_msg	= 'Total Data length is less 5 characters (Building_name + street + landmark + area) will be rejected';
				$arr_errors['error'][] = $err_msg;
			}
			//Repeated text in the address will be rejected (Same Text in Buidling Name or Street Etc)
			elseif($res_repeatchar_building == 'ERROR' || $res_repeatchar_street == 'ERROR' || $res_repeatchar_landmark == 'ERROR' || $res_repeatchar_area == 'ERROR')
			{
				$err_msg	= 'Repeated text in the address will be rejected (Same Text in Buidling Name or Street Etc)';
				$arr_errors['error'][] = $err_msg;
			}
		}
		return $err_msg;
	}
	
	function checkCategory()
	{
		$err_msg = '';
		if(!empty($this->insert_arr) && !empty($this->insert_arr['category']))
		{
			$count_category = count(explode(',',str_replace('/','',$this->insert_arr['category'])));
			
			if($count_category > 100)
			{
				$err_msg = 'Categories mentioned are more than 100 will be rejected';
				$arr_errors['error'][] = $err_msg;
			}
		}
		return $err_msg;
	}
	
	function checkMinLength($str, $minlength)
	{
		$msg	=	'';
		if(!empty($str) && !empty($minlength))
		{
			if(strlen($str) < $minlength)
			{
				$msg = 'ERROR';
			}
		}
		return $msg;
	}
	
	function checkMaxLength($str, $maxlength)
	{
		$msg	=	'';
		if(!empty($str) && !empty($maxlength))
		{
			if(strlen($str) > $maxlength)
			{
				$msg = 'Error';
			}
		}
		return $msg;
	}
	
	function checkRepeatChar($str)
	{
		$msg = '';
		if(!empty($str))
		{
			if(preg_match('/(\w)\1{2,}/i', $str))
			{
				$msg = 'ERROR';
			}
		}
		return $msg;
	}
	
	function checkRepeatText($str)
	{
		$msg = '';
		if(!empty($str))
		{
			$arr_str	=	explode(' ',$str);
			$arr_unique	=	array_unique($arr_str);
			
			if(count($arr_str) != count($arr_unique))
			{
				$msg	= 'ERROR';
			}
		}
		return $msg;
	}
	
	function checkNumericDigit($str, $allowdigit)
	{
		$msg	= 	'';
		if(!empty($str) && !empty($allowdigit))
		{
			if(count(array_filter(str_split($str),'is_numeric')) > $allowdigit)
			{
				$msg	=	'ERROR';
			}
		}
		return $msg;
	}
	
	function checkOnlyAlphabets($str)
	{
		$msg 	=	'';
		if(!preg_match('/^[a-zA-Z ]+$/', $str))
		{
			$msg = 'ERROR';
		}
		return $msg;
	}
	
	// Allow Space, Ampersand, Open Bracket, Close Bracket, Hyphen, At Symbol, dot, exclamation are allowed in Business name
	function checkCompSplChar($str)
	{
		$msg	=	'';
		if(!preg_match('/[a-zA-Z0-9- &()@ .]!*$/', $str))
		{
			$msg	=	'ERROR';
		}
		return $msg;
	}
	
	
	// Main function to insert data in all live tables
	/**
	 * tbl_companymaster_generalinfo - paid
	 * tbl_companymaster_extradetails - paid
	 * tbl_companymaster_search - paid
	 * tbl_contract_tmeDetails
	 * tbl_tmesearch - paid
	 * tbl_company_source - paid
	 * bus_facility_dump - paid
	 * tbl_compcatarea_regen_log
	 * tbl_compcatarea_non_paid
	 * tbl_autosuggest_company
	 * JD gurantee Process
	 * Virtual number process
	 * IRO Cards generation  - create_cards()
	 * tbl_company_consolidate   - log table
	 * tbl_paid_narration - paid
	 * tbl_companymaster_extradetails_national - paid
	 * tbl_companymaster_search_national - paid
	 **/ 
	function finalInsert()
	{
		$api_ip_arr = array('10033648','10000760','10015427','10015416');
		$this->live_comp = 0;
		if(in_array($this->insert_arr['ucode'],$api_ip_arr)){
			$this->live_comp = 1;
		}	
		
		$this->logCsDetails();		
		
		$this->curl_web_api($this->insert_arr,'1');
		
		/************************** GEOCODE HANDLING STARTS *************************************/		
		$geocode_update_source_arr	=	array('JDA','ME');
		$geocode_ucode_arr	=	array('013080','10026651','003319','011058','10015416','10015427');
		if(isset($this->insert_arr['flow_module']) && $this->insert_arr['flow_module'] == 'DE' && $this->insert_arr['module']=='CS' && in_array($this->insert_arr['ucode'],$geocode_ucode_arr))
		{	
			$geocode_accuracy_arr	= $this->get_geocode($this->insert_arr);
			if(is_array($geocode_accuracy_arr) && count($geocode_accuracy_arr)>0)
			{
				$this->insert_arr['geocode_accuracy_level'] = $geocode_accuracy_arr['data']['geocode_accuracy_level'];
				$this->insert_arr['latitude'] 				= $geocode_accuracy_arr['data']['latitude'];
				$this->insert_arr['longitude'] 				= $geocode_accuracy_arr['data']['longitude'];
				$this->insert_arr['map_pointer_flags'] 		= $geocode_accuracy_arr['data']['map_pointer_flags'];
				$this->insert_arr['flags'] 					= $geocode_accuracy_arr['data']['flags'];
			}
		}

		$compOldData	=	$this->get_cmp_old_data($this->insert_arr['parentid']);
		
		
		$social_media_url_arr = Array();
		if((isset($this->insert_arr['sm_val']) && $this->insert_arr['sm_val'])  == '1')
		{
			if(isset($this->insert_arr['social_media_url']) && !empty($this->insert_arr['social_media_url']))
			{
				$social_media_url_arr = explode("|~|",$this->insert_arr['social_media_url']);
				//$social_media_url_arr = array_filter(array_unique($social_media_url_arr));
				$smu_final_arr = Array();
				foreach($social_media_url_arr AS $kkk=>$vv_arr)
				{	
					$smu_arr =Array();
					$smu_arr = explode(",",trim($vv_arr,","));	
					$smu_final_arr	=	array_merge($smu_arr,$smu_final_arr);
				}
				$smu_final_arr = array_unique(array_filter($smu_final_arr));
				if(count($smu_final_arr)>0)
				{
					foreach($smu_final_arr as $key => $value)
					{
						$value = str_replace(" ","",trim($value));
						if(strpos(strtolower($value), "facebook") > -1)
						{
							$smu_consolidated[0] = $value;	
						}
						else if(strpos(strtolower($value), "twitter") > -1)
						{
							$smu_consolidated[1] = $value;	
						}
						else if(strpos(strtolower($value), "google") > -1)
						{
							$smu_consolidated[2] = $value;	
						}
						
						else if(strpos(strtolower($value), "youtube") > -1)
						{
							$smu_consolidated[3] = $value;	
						}
						else if(strpos(strtolower($value), "linkedin") > -1)
						{
							$smu_consolidated[4] = $value;	
						}
						else if(strpos(strtolower($value), "instagram") > -1)
						{
							$smu_consolidated[5] = $value;	
						}
						 else if($value !='' && strpos(strtolower($value), "facebook") == 0 && strpos(strtolower($value), "twitter") == 0 && strpos(strtolower($value), "google") == 0 && strpos(strtolower($value), "youtube") == 0 && strpos(strtolower($value), "linkedin") == 0 && strpos(strtolower($value), "instagram") == 0)
						{
							
							//echo $value."==".strpos(strtolower($value), "twitter")."<br>";
							if($value != "")
							{
								$other_string = trim($other_string.",".$value,",");	
							}
												
						} 
						 
					}
					
				 	if($other_string != "")
					{
						$implode_arr = explode(",",$other_string);
						$smu_consolidated[6] = implode(",",($implode_arr));
					}
					ksort($smu_consolidated);					
					//$this->insert_arr['social_media_url']  = implode("|~|",($smu_consolidated));
				}
			}
			for($sm=0;$sm<=6;$sm++) //6 stands for total smu
			{
				$smu_consolidated_final[$sm] = $smu_consolidated[$sm];
			}
			
			$this->insert_arr['social_media_url']  = implode("|~|",($smu_consolidated_final));
		}
		
		
		
		
		//5+ Mobile Number check
		
		$skip_source_5_plus_mobile_arr = Array('CS','DE','JOINFREE','AD PROGRAM','ADPROGRAMS','ADPROGROGRAMS','5+ MOBILE NUMBER VALIDATION','TM DATA CORRECTION (F10)','DIALER VALIDATION','JOINFREE DATA (WAP)','JOINFREE DATA (APP)','F9 IRO DATA CORRECTION');
		if(!in_array(strtoupper($this->insert_arr['source']),$skip_source_5_plus_mobile_arr) && !(isset($this->insert_arr['flow_module']) && strtolower($this->insert_arr['flow_module']) == 'common_audit'))
		{
			if(!(isset($this->insert_arr['omit_5_plus_mobile_audit']) && $this->insert_arr['omit_5_plus_mobile_audit'] == '1'))
			{
				$five_mobile_arr	=	$this->five_plus_mobile_check($this->insert_arr);
				if(!empty($five_mobile_arr) && count($five_mobile_arr)>0 && $five_mobile_arr['five_plus_exits_flag'] == 1)
				{
					if($this->insert_arr['paid'] == 0)
					{
						$this->insert_arr['mobile']				= $five_mobile_arr['mobile_new']; 
						$this->insert_arr['mobile_display']		= $five_mobile_arr['mobile_new']; 
					}
					
					$extra_info['priority_field'] = $five_mobile_arr['five_plus_mobile'];
					$extra_info['mobile_type'] = "five_plus_mobile";
					$this->data_correction_api($this->insert_arr,'FIVE_PLUS_MOBILE',$extra_info,$compOldData);	
				}
				
				$two_plus_mobile_source_arr	= array("JDA","ME");
				if(!empty($five_mobile_arr) && count($five_mobile_arr)>0 && $five_mobile_arr['two_plus_exits_flag'] == 1 && in_array(strtoupper($this->insert_arr['source']),$two_plus_mobile_source_arr))
				{
					$extra_info['priority_field'] = $five_mobile_arr['two_plus_mobile'];
					$extra_info['mobile_type'] = "two_plus_mobile";
					$this->data_correction_api($this->insert_arr,'FIVE_PLUS_MOBILE',$extra_info,$compOldData);	
				}
			}
		}	
		
		// working time handling - start		
		$hrs_empty_flag=0;
		/*if(!isset($this->insert_arr['working_time_start']) || !isset($this->insert_arr['working_time_end'])) 
			$hrs_empty_flag=1;
		*/	
		if(isset($this->insert_arr['working_time_start'])) 
		{
			$working_time_start = $this->insert_arr['working_time_start'];
			$working_time_start	= str_replace("-", "",$working_time_start);
			if (strpos($working_time_start, 'undefined') !== false) 
				$hrs_empty_flag=1;
			
			$time_start_arr = explode(",", trim($working_time_start, ","));
			if ($time_start_arr[0] == '') 
				$hrs_empty_flag=1;			
		}
		if(isset($this->insert_arr['working_time_end'])) 
		{
			$working_time_end = $this->insert_arr['working_time_end'];
			$working_time_end = str_replace("-", "", $working_time_end);
			if (strpos($working_time_end, 'undefined') !== false) 
				$hrs_empty_flag=1;
			
			$time_start_arr = explode(",", trim($working_time_end, ","));
			if ($time_start_arr[0] == '') 
				$hrs_empty_flag=1;	
		}
		if($hrs_empty_flag==1)
		{
			$this->insert_arr['working_time_start'] = "";
			$this->insert_arr['working_time_end'] = "";
		}
		// working time handling - END
		
		// Added Prefix'Near' in landmark in case of absent 
		$landmark_check_source_arr	=	array('DIALER VALIDATION','F9 IRO Data Correction','TM DATA CORRECTION (F10)','JDA','ME');
		if((in_array(strtoupper($this->insert_arr['source']),$landmark_check_source_arr) || (isset($this->insert_arr['flow_module']) && strtolower($this->insert_arr['flow_module']) == 'common_audit')) && !empty($this->insert_arr['landmark']))
		{
			$ret_landmark_arr = $this->check_landmark(trim($this->insert_arr['landmark']));
			if($ret_landmark_arr['valid_flag'] == '0')
			{
				$this->insert_arr['landmark'] = $ret_landmark_arr['new_landmark'];
			}
		}
		// Full address standanrd format added by shital patil - 23-02-2018 
		$addrarray	=Array();	
		if(!empty($this->insert_arr['building_name']))
			$addrarray[0] = trim($this->insert_arr['building_name']);
		if(!empty($this->insert_arr['landmark']))
			$addrarray[1] = trim($this->insert_arr['landmark']);
		if(!empty($this->insert_arr['street']))
			$addrarray[2] = trim($this->insert_arr['street']);
		if(!empty($this->insert_arr['area']))
			$addrarray[3] = trim($this->insert_arr['area']);
		$fulladdress="";
		if(count($addrarray) > 0)
		{
			ksort($addrarray);
			$addrarray1 = array_values(array_filter($addrarray));
			$fulladdress = implode(',',$addrarray1);		
		}
		$fulladdress = trim($fulladdress,",");
		if(!empty($this->insert_arr['pincode']))
		{
			$fulladdress .=	"-".$this->insert_arr['pincode'];
		}
		if(!empty($fulladdress))
			$this->insert_arr['full_address']	=	$fulladdress;
		
		// END - Full address
		
		$arr_column_genaralinfo			=	$this->getColumnList('tbl_companymaster_generalinfo');				

		$arr_column_extradetails		=	$this->getColumnList('tbl_companymaster_extradetails');
		
		unset($this->insert_arr['area_display']);	
		if(!empty($this->insert_arr) && !empty($this->insert_arr['companyname']) && !empty($this->insert_arr['data_city']))
		{
			$parentid 		=	$this->insert_arr['parentid'];
			
			$sphinx_id 		=	$this->insert_arr['sphinx_id'];
			
			$this->insert_arr['updatedOn'] 	= date("Y-m-d H:i:s");
			
			$compnameval = trim($this->insert_arr['companyname']);
			
			$compinitial = substr($compnameval, 0, 3);
			$compinitial1 = substr($compnameval, 0, 4);
			
			$management_contracts_arr = Array('PXX22.XX22.160429120109.N7T5','PXX22.XX22.170427125056.K3U2','PXX22.XX22.160920113435.X1T4','PXX22.XX22.161028082301.U5R2','PXX22.XX22.161006202148.G5Z1');
			if((strtolower($compinitial) == 'zxy' || strtolower($compinitial1) == 'zqvd') && !(in_array($this->insert_arr['parentid'],$management_contracts_arr )))
			{
				
				$misc_flag_val = 1;
				
				//misc_flag + if(misc_flag&1=1,0,1)
				if($this->live_comp == 1){
					$this->insert_arr['misc_flag']['set'] = '1'; 
				}
				else{
					$this->insert_arr['misc_flag'] = "misc_flag + if(misc_flag&".$misc_flag_val."=".$misc_flag_val.",0,".$misc_flag_val.")" ;					
				}
			}
			
			
			// If parentid is blank, generate new parentid and sphinx_id
			if(empty($parentid))
			{
				$parentid 	= 	$this->generateParentid($this->insert_arr['data_city'],$this->insert_arr['source'],$this->insert_arr['is_remote']);
		
				$sphinx_id	=	$this->getContractSphinxId($parentid);
				
				$this->insert_arr['parentid']		=	$parentid;
				
				$this->insert_arr['sphinx_id']		=	$sphinx_id;
			}
			
			if(($parentid)) // we need to check intry alway on id_generator 
			{
				$sphinx_id	=	$this->getContractSphinxId($parentid);
				if(intval($sphinx_id)>0){
					$this->updateSphinxId($sphinx_id);
					$this->insert_arr['sphinx_id']		=	$sphinx_id;
				}else
				{
					$this->insert_tbl_absent_parentid_table_details($parentid,'tbl_id_generator',$failed_sql);
				}
			}
			$docid	=	$this->getDocidForParentid($parentid);

			$this->insert_arr['docid']			=	$docid;	
			// End
			
			if(trim($parentid) == '')
			{
				echo 'Fatal error: Blank parentid'; 
				echo  '<br/><h3>Please try again or contact Help Desk</h3>';
				die();
			}
			
			$configclassobj= new configclass();
			$urldetails= $configclassobj->get_url(urldecode($this->insert_arr['data_city']));
			
			if (!empty($this->insert_arr['category']) && trim($this->insert_arr['category']) != '//' && trim($this->insert_arr['category']) != '/' && trim($this->insert_arr['category']) != '')
			{
				$cat_ids_list				=	explode('/,/', trim($this->insert_arr['category'],'/'));
				$cat_ids_list				= 	$this->get_valid_categories($cat_ids_list);
				
				$extra_catlin_nonpaid_str 	= '';
				$extra_catlin_nonpaid_str 	= str_replace('/','',$this->insert_arr['catidlineage_nonpaid']);	
					
				if(!empty($extra_catlin_nonpaid_str))
				{
					$extra_catlin_nonpaid_arr	= array();
					$extra_catlin_nonpaid_arr 	= explode(',',$extra_catlin_nonpaid_str);
					$extra_catlin_nonpaid_arr 	= $this->get_valid_categories($extra_catlin_nonpaid_arr);
				}
				
				if(is_array($extra_catlin_nonpaid_arr) && count($extra_catlin_nonpaid_arr)>0)
					$total_catids_arr = array_merge($extra_catlin_nonpaid_arr,$cat_ids_list);
				else
					$total_catids_arr = $cat_ids_list;
				
				
				//echo '<pre><br>before :: ';
				//print_r($cat_ids_list);
				//print_r($extra_catlin_nonpaid_arr);
				//print_r($total_catids_arr);
				
				$cat_param['module']	  = $this->insert_arr['source'];
				$cat_param['action']	  = 'getMergeCategory';
				$cat_param['data_city']	  = urlencode($this->insert_arr['data_city']);
				$cat_param['category']    = json_encode($total_catids_arr);
				$cat_url 	= $urldetails['jdbox_service_url']."category_tag.php";
				$cat_res    = json_decode($this->call_curl_post($cat_url,($cat_param)),true);	
				
				
				if( $cat_res['error']['code'] == 0 && count($cat_res['error']['res'])>0 )
				{
					
					foreach($cat_res['error']['res'] as $source_catid => $destination_value)
					{
					   	foreach($cat_ids_list as $cat_idx => $src_catid)
					   	{
							if($src_catid == $source_catid)
							{
								unset($cat_ids_list[$cat_idx]);
								$cat_ids_list[] = $destination_value['dest_catid'];
							}
						}
						
						foreach($extra_catlin_nonpaid_arr as $np_cat_idx => $np_src_catid)
					   	{
							if($np_src_catid == $source_catid)
							{
								unset($extra_catlin_nonpaid_arr[$np_cat_idx]);
								$extra_catlin_nonpaid_arr[] = $destination_value['dest_catid'];
							}
						}
						
					}
				}
				//echo '<br>after :: ';
				///print_r($cat_ids_list);
				//print_r($extra_catlin_nonpaid_arr);
				
				
				$catid						=	implode(',',$cat_ids_list);
				$row_catid_parent			=	$this->getParentCategories($catid);
			
				if(count($row_catid_parent) > 0)
				{
					$arr_catlineage_search_merge  	= array_merge($row_catid_parent,$cat_ids_list);
					$arr_catlineage_search			= array_unique($arr_catlineage_search_merge);
				}
				else
				{
					$arr_catlineage_search 	= $cat_ids_list;
					$arr_catlineage_search 	= array_unique($arr_catlineage_search);
				}
				
				
				$param_legal['parentid'] = $this->insert_arr['parentid'];
				$param_legal['companyname'] = urlencode($this->insert_arr['companyname']);
				$param_legal['ucode'] = $this->insert_arr['ucode'];
				$param_legal['module'] = $this->insert_arr['source'];
				$param_legal['uname'] = urlencode($this->insert_arr['uname']);
				$param_legal['data_city'] = urlencode($this->insert_arr['data_city']);
				$param_legal['catid'] = json_encode($arr_catlineage_search);
				$param_legal['post_data'] = '1';
				// Calling new API to insert into new table for Nonpaid contracts
				
				
				$curl_url 	= $urldetails['jdbox_service_url']."legal_cat_search.php";
				
				$ret = $this->call_curl_post($curl_url,($param_legal)); 
				//echo $curl_url."?".http_build_query($param_legal);exit;
				$ret =	json_decode($ret,true);

				/**************** Logic for premium categories -- STARTS here ********/
				$city 			= '';
				$contarcttype 	= 0;
				$category_array = array();
				
				if($this->insert_arr['is_remote'] = 'REMOTE')
				{
					$city = $this->insert_arr['data_city'];
				}
				else
				{
					$city = $this->insert_arr['city'];
				}
				
				$category_array = $this->auditPremiumCategoryNonpaid($parentid, $cat_ids_list, $arr_catlineage_search, $this->insert_arr['source'], $this->insert_arr['ucode'], $city, $this->insert_arr['uname'], $this->insert_arr['companyname'], $this->insert_arr['paid']);
				
					
				if(is_array($extra_catlin_nonpaid_arr) && count($extra_catlin_nonpaid_arr)>0)
				{					
					$final_catlin_srch = array_merge($extra_catlin_nonpaid_arr,$category_array['catLineageSrch']);
					$category_array['catLineageSrch'] = $final_catlin_srch;
					
					$final_nonpaid_catidlineage	= implode('/,/',$extra_catlin_nonpaid_arr);
					$final_nonpaid_catidlineage	= '/'.$final_nonpaid_catidlineage.'/';
				}
				$category_array['catLineageSrch'] = array_unique(array_filter($category_array['catLineageSrch']));
				
				$catidlineage 			= implode('/,/',$category_array['catLineage']);
				$catidlineage_search  	= implode('/,/',$category_array['catLineageSrch']);
				
				
				if($catidlineage != '')
					$catidlineage 		= '/'.$catidlineage.'/';
				
						
				if($catidlineage_search != '')	
					$catidlineage_search = '/'.$catidlineage_search.'/';
								
								
				// check if any selected category is category_type&256=256 then email_display field will be blank.
				if(count($category_array['catLineage']) > 0)
				{
					$final_catStr = "'" . implode("','",$category_array['catLineage']) . "'";
					//$query_email_flag = "SELECT catid,category_type FROM tbl_categorymaster_generalinfo WHERE catid IN (".$final_catStr.") AND category_type&256=256";
					//$res_email_flag =  parent::execQuery($query_email_flag, $this->dbConDjds_slave);
					//$res_email_flag =  parent::execQuery($query_email_flag, $this->dbConDjds);
					
					$cat_params = array();
					$cat_params['page'] 		= 'insertLiveClass';
					$cat_params['skip_log'] 	= '1';
					$cat_params['data_city'] 	= $this->data_city;
					$cat_params['return']		= 'catid,category_type';

					$where_arr  	=	array();
					if(count($category_array['catLineage'])>0){
						$where_arr['catid']				= implode(",",$category_array['catLineage']);
						$where_arr['category_type']		= '256';
						$cat_params['where']	= json_encode($where_arr);
						$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
					}
					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}
					if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
					{
						$this->insert_arr['email_display']		=	'';
					}
				}
				/***************** Logic for premium categories --ENDS here ********/
				
				if(!empty($catidlineage))
				{
					$national_catidlineage 			= $this->getNationalCatlineage($catidlineage);
					$catids_array_national = array();
					$catids_array_national = explode("/,/",trim($national_catidlineage,"/"));
					$catids_array_national = $this->get_valid_categories($catids_array_national);
					$moviehallNationalcat = "10099752"; //Cinema halls category
					
					if(count($catids_array_national)>0 && in_array($moviehallNationalcat, $catids_array_national))
					{
						$this->insert_arr['working_time_start'] = "";
						$this->insert_arr['working_time_end'] = "";
					}
				}
				
				if(!empty($catidlineage_search))
				{
					$national_catidlineage_search 	= $this->getNationalCatlineage($catidlineage_search);
				}
				
				if(!empty($catidlineage))
				{
					$hot_category					= $this->getHotCategory($catidlineage);
				}

				$this->insert_arr['catidlineage']					=	$catidlineage;
				
				$this->insert_arr['catidlineage_nonpaid']			=	$final_nonpaid_catidlineage;
				
				$this->insert_arr['catidlineage_search']			=	$catidlineage_search;
				
				$this->insert_arr['national_catidlineage']			=	$national_catidlineage;
				
				$this->insert_arr['national_catidlineage_search']	=	$national_catidlineage_search;
				
				$this->insert_arr['hotcategory']					=	$hot_category;
			}
			else if((trim($this->insert_arr['category']) == '//' || trim($this->insert_arr['category']) == '/' || trim($this->insert_arr['category']) == '') && trim($this->insert_arr['nocategory'] == 1) && (trim($this->insert_arr['source'] == 'cs') || trim($this->insert_arr['source'] == 'CS')))
			{				
				$this->insert_arr['catidlineage']					=	null;
				$this->insert_arr['catidlineage_search']			=	null;
				$this->insert_arr['national_catidlineage']			=	null;
				$this->insert_arr['national_catidlineage_search']	=	null;
				$this->insert_arr['hotcategory']					=	null;
			
			}
			// Added for Dr to Dr. changes for doctor category contracts
			$ret_dr = $this->check_doctor_contract($this->insert_arr);
			
			if($ret_dr['result']['dr_contract_flag'] == '1')
			{
				$this->insert_arr['companyname'] = $ret_dr['result']['companyname_new'];
			} 
			
			// Added for Advocate in company name
			$ret_adv = $this->check_advocate_contract($this->insert_arr);
			if($ret_adv['result']['comp_change'] == '1')
			{
				$this->insert_arr['companyname'] = $ret_adv['result']['companyname_new'];
			} 
			
			// Added by shital patil 26-07-2016 - Hours of operation rule for 24 hours category handling
			if(!empty($this->insert_arr['catidlineage_search']) && $this->insert_arr['closedown_flag'] ==0)
			{
				$catidlist = str_replace("/","",$this->insert_arr['catidlineage_search']);
				$catidlist_wt = trim(trim($catidlist,","));
				
				if(!empty($catidlist_wt))
				{
					//$sql = "SELECT DISTINCT category_name FROM tbl_categorymaster_generalinfo where catid in (".$catidlist_wt.") AND misc_cat_flag&256=256 ";
					//$res = parent::execQuery($sql, $this->dbConDjds);			
					$cat_params = array();
					$cat_params['page'] 		= 'insertLiveClass';
					$cat_params['skip_log'] 	= '1';
					$cat_params['data_city'] 	= $this->data_city;
					$cat_params['return']		= 'category_name';

					$where_arr  	=	array();
					if($catidlist_wt!=''){
						$where_arr['catid']			= $catidlist_wt;
						$where_arr['misc_cat_flag']	= '256';
						$cat_params['where']	= json_encode($where_arr);
						$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
					}
					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}					
						
					if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
					{
						if($this->insert_arr['flow_module'] == 'DE')
							$sourceTime = "DE";
						else
							$sourceTime =	$this->insert_arr['source'];

						$insertLogTime = "INSERT INTO tbl_24_hours_category_log
										SET
									parentid				=	'".$this->insert_arr['parentid']."',
									catid					=	'".$this->insert_arr['catidlineage_search']."',
									working_time_start_user	=	'".$this->insert_arr['working_time_start']."',
									working_time_end_user	=	'".$this->insert_arr['working_time_end']."',
									working_time_start_new	=	'00:00,00:00,00:00,00:00,00:00,00:00,00:00',
									working_time_end_new	=	'23:59,23:59,23:59,23:59,23:59,23:59,23:59',
									ucode					=	'".$this->insert_arr['ucode']."',
									uname 					=	'".$this->insert_arr['uname']."',
									module					=	'".$sourceTime."',
									insert_date				=	now()";	
						$resTime = parent::execQuery($insertLogTime, $this->dbConDjds);				
										
						$this->insert_arr['working_time_start'] = "00:00,00:00,00:00,00:00,00:00,00:00,00:00";
						$this->insert_arr['working_time_end'] 	= "23:59,23:59,23:59,23:59,23:59,23:59,23:59";
					}
				}
			}	
			// Added by shital patil 02-02-2016 
			$edit_flag = 0;
			//$sql_check_edit= "SELECT parentid,companyname,landmark,latitude,longitude,geocode_accuracy_level FROM tbl_companymaster_generalinfo WHERE parentid='".$this->insert_arr['parentid']."'"; 
			//$res_check_edit =  parent::execQuery($sql_check_edit, $this->dbConIro);
			$comp_params = array();
			$comp_params['data_city'] 	= $this->data_city;
			$comp_params['table'] 		= 'gen_info_id,extra_det_id';		
			$comp_params['parentid'] 	= $this->insert_arr['parentid'];
			$comp_params['fields']		= 'parentid,companyname,landmark,latitude,longitude,geocode_accuracy_level,national_catidlineage_search,hotcategory,freeze,mask';
			$comp_params['action']		= 'fetchdata';
			$comp_params['page']		= 'insertLiveClass';
			$comp_params['skip_log']	= 1;
	
			$comp_api_res  	= '';
			$comp_api_arr	= array();
			$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
			if($comp_api_res!=''){
				$comp_api_arr 	= json_decode($comp_api_res,TRUE);
			}
			
			if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['gen_info_id']=='1')
			{
				$edit_flag = 1;
				//$row_edit_data			=	mysql_fetch_assoc($res_check_edit);
				$row_edit_data 	= $comp_api_arr['results']['data'][$this->insert_arr['parentid']];
							
				$this->insert_arr['companyname_old']	=	trim($row_edit_data['companyname']);
				$this->insert_arr['building_name_old']	=	trim($row_edit_data['building_name']);
				$this->insert_arr['landmark_old']		=	trim($row_edit_data['landmark']);
				$this->insert_arr['latitude_old']		=	trim($row_edit_data['latitude']);
				$this->insert_arr['longitude_old']		=	trim($row_edit_data['longitude']);
				$this->insert_arr['geocode_accuracy_level_old']		=	trim($row_edit_data['geocode_accuracy_level']);
				$this->insert_arr['freeze_old']		=	trim($row_edit_data['freeze']);
				$this->insert_arr['mask_old']		=	trim($row_edit_data['mask']);
			}
			
			if($edit_flag == 0)
			{
				$insert_new_data = "INSERT IGNORE INTO tbl_jd_new_data
									 SET 
									parentid = '".$this->insert_arr['parentid']."',
									data_city = '".$this->insert_arr['data_city']."',
									source = '".$this->insert_arr['source']."',
									ucode = '".$this->insert_arr['ucode']."',
									uname = '".$this->insert_arr['uname']."',
									insert_date = now()";
				$res_insert_new_data =  parent::execQuery($insert_new_data, $this->dbConDjds);						
			}

			if($this->insert_arr['latitude_old'] != $this->insert_arr['latitude'] || $this->insert_arr['longitude_old'] != $this->insert_arr['longitude'] || $this->insert_arr['geocode_accuracy_level_old'] != $this->insert_arr['geocode_accuracy_level'])
			{
				$insert_geocode_log = "INSERT INTO tbl_geocode_update_trail 
										SET
									parentid	=	'".$this->insert_arr['parentid']."',
									companyname	=	'".addslashes($this->insert_arr['companyname'])."',
									data_city	=	'".$this->insert_arr['data_city']."',
									latitude_old	=	'".$this->insert_arr['latitude_old']."',
									longitude_old	=	'".$this->insert_arr['longitude_old']."',
									latitude_new	=	'".$this->insert_arr['latitude']."',
									longitude_new	=	'".$this->insert_arr['longitude']."',
									geocode_accuracy_level_old	=	'".$this->insert_arr['geocode_accuracy_level_old']."',
									geocode_accuracy_level_new	=	'".$this->insert_arr['geocode_accuracy_level']."',
									source	=	'".$this->insert_arr['source']."',
									paid	=	'".$this->insert_arr['paid']."',
									updated_by	=	'".$this->insert_arr['ucode']."',
									update_time	=	now()";
				 $res_geocode_log =  parent::execQuery($insert_geocode_log, $this->dbConDjds);						
			}
			
			//$sqlExtraDetailsInfo = "SELECT national_catidlineage_search,hotcategory FROM tbl_companymaster_extradetails WHERE parentid = '".$this->insert_arr['parentid']."'";
			//$resExtraDetailsInfo =  parent::execQuery($sqlExtraDetailsInfo, $this->dbConIro);
			if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['extra_det_id']=='1')
			{
				$row_extradetails 	= $comp_api_arr['results']['data'][$this->insert_arr['parentid']];
				$old_ncatsrch 		= trim($row_extradetails['national_catidlineage_search']);
				$old_hotcat		    = trim($row_extradetails['hotcategory']);
		    }

			if(!empty($this->insert_arr['category']) && !empty($this->insert_arr['area']) && !empty($this->insert_arr['pincode']))
			{
				if($edit_flag == 0)
				{
					$catidlineage_array = explode(",",(str_replace("/","",$this->insert_arr['category'])));
					//$landmark_cat = "SELECT catid,category_name FROM d_jds.tbl_categorymaster_generalinfo WHERE category_name IN ('Malls','Shopping Centres','Cinema Halls','4 Star Hotels','5 Star Hotels','Railway Station','Industrial Estates','airport')";
					
					//$res_landmark_cat =  parent::execQuery($landmark_cat, $this->dbConIro);
					$cat_params = array();
					$cat_params['page'] 		= 'insertLiveClass';
					$cat_params['data_city'] 	= $this->data_city;
					$cat_params['return']		= 'catid,category_name';

					$where_arr  	=	array();					
					$where_arr['category_name']		= 'Malls,Shopping Centres,Cinema Halls,4 Star Hotels,5 Star Hotels,Railway Station,Industrial Estates,airport';
					$cat_params['where']	= json_encode($where_arr);
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
					
					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}

					if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
					{
						foreach($cat_res_arr['results'] as $key =>$row_landmark_cat)
						{
							
							$landmark_cat_arr[$row_landmark_cat['catid']] = $row_landmark_cat['category_name'];
							$landmark_catid_arr[] = $row_landmark_cat['catid'];						
							if(in_array($row_landmark_cat['catid'],$catidlineage_array))
							{
								$check_exist = "SELECT * FROM d_jds.tbl_areamaster_consolidated_v3 WHERE entity_area='".$this->insert_arr['companyname']."' AND parent_area = '".$this->insert_arr[area]."' AND pincode = '".$this->insert_arr[pincode]."' AND data_city = '".$this->data_city."' AND type_flag in (2,3) AND display_flag=1";								
								$res_check_exist =  parent::execQuery($check_exist, $this->dbConIro);
								
								
								if($res_check_exist && mysql_num_rows($res_check_exist)>0)
								{
									
								}
								else
								{	
									$select_pin = "SELECT * FROM d_jds.tbl_areamaster_consolidated_v3 WHERE pincode = '".$this->insert_arr[pincode]."' AND display_flag=1 LIMIT 1";
									$res_select_pin =  parent::execQuery($select_pin, $this->dbConIro);
									if($res_select_pin && mysql_num_rows($res_select_pin)>0)
									{
										$row_pin = mysql_fetch_assoc($res_select_pin);
										$zoneid  = $row_pin['zoneid'];
										$stdcode_pin  = $row_pin['stdcode'];
										$latitude_pincode  = $row_pin['latitude_pincode'];
										$longitude_pincode  = $row_pin['longitude_pincode'];
									}
									$insert_landmark = "INSERT IGNORE INTO online_regis1.tbl_area_master_category_match SET
									parentid = '".$this->parentid."',
									companyname = '".addslashes($this->insert_arr['companyname'])."',
									category_name = '".addslashes($row_landmark_cat['category_name'])."',
									areaname  = '".ucwords(addslashes($this->insert_arr['companyname']))."-".addslashes($row_landmark_cat['category_name'])."',
									mainarea = '".addslashes($this->insert_arr['area'])."',
									pincode = '".$this->insert_arr['pincode']."',
									state = '".addslashes($this->insert_arr['state'])."',
									city = '".addslashes($this->insert_arr['city'])."',
									data_city  = '".addslashes($this->insert_arr['data_city'])."',
									stdcode  = '".$stdcode_pin."',
									zoneid  = '".$zoneid."',
									type_flag  = '2',
									latitude_area  = '".$this->insert_arr['latitude']."',
									longitude_area  = '".$this->insert_arr['longitude']."',
									latitude_pincode  = '".$latitude_pincode."',
									longitude_pincode  = '".$longitude_pincode."',
									latitude_final  = '".$this->insert_arr['latitude']."',
									longitude_final  = '".$this->insert_arr['longitude']."',
									create_time = now(),
									created_by = 'CS'";
									$res_insert =  parent::execQuery($insert_landmark, $this->dbConIdc);
								}	
							}
						}
					}	
				}	
			} // End
			
			/*--------------------------------------------------------------------*/
			/******************* Landline Mapped category - will go only in consolidate table*****************************/
			if($this->insert_arr['consolidate'] == '1')
			{
				$this->landlineMappedCat($this->insert_arr);
			}
			else
			{
			/********************Landline Mapped category - END   ******************/
			
			
			/********************Deleted category shopfront api   ******************/
			if(strtolower($this->insert_arr['source'])== 'cs')
			{	
				$curparentid = $this->insert_arr['parentid'];
					
				$totalcategoryarr_new = array_merge(explode(",",str_replace("/","",$this->insert_arr['catidlineage'])), explode(",",str_replace("/","",$this->insert_arr['catidlineage_nonpaid'])) );
				$totalcategoryarr_new = array_unique($totalcategoryarr_new);
				$totalcategoryarr_new = array_filter($totalcategoryarr_new);
		

				if($totalcategoryarr_new) // there is difference we found in category so it is going to be deleted
				{
					$catdiffstr = implode(",",$totalcategoryarr_new);
					// fetching national catid
					//$nationacatdiffstr ="select group_concat(national_catid)  as natcatid from d_jds.tbl_categorymaster_generalinfo where catid in (".$catdiffstr.")";							
					//$nationacatdiffrs =  parent::execQuery($nationacatdiffstr, $this->dbConIro);
					$cat_params = array();
					$cat_params['page'] 		= 'insertLiveClass';
					$cat_params['data_city'] 	= $this->data_city;
					$cat_params['return']		= 'national_catid';

					$where_arr  	=	array();					
					$where_arr['catid']		= $catdiffstr;
					$cat_params['where']	= json_encode($where_arr);
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
					
					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}

					if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
					{
						//$nationacatdiff_arr =mysql_fetch_assoc($nationacatdiffrs);
						$national_catid_arr = array();
						foreach ($cat_res_arr['results'] as $key => $nationacatdiff_arr) {
							if($nationacatdiff_arr['natcatid']!=''){
								$national_catid_arr[] = $nationacatdiff_arr['national_catid'];
							}	
						}
						if(count($national_catid_arr)>0){
							$nationacatdatring = implode(",",$national_catid_arr);
						}
						
						// geting docid 
						$docidsql ="select docid from db_iro.iro_cards   where parentid='".$this->insert_arr['parentid']."'";
						$docidrs =  parent::execQuery($docidsql, $this->dbConIro);
						$docidarr= mysql_fetch_assoc($docidrs);
						
						
						if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']))
						{
							$sfdeactivtionurl="http://192.168.20.105:1080/services/disable_quotes_by_catid.php?docid=".$docidarr['docid']."&national_catid=".$nationacatdatring."&updated_by==csgenio-".urlencode($this->insert_arr['ucode']);
							
						}
						else
						{
							// development
							$sfdeactivtionurl="http://pankajpatil.jdsoftware.com/QUICK_SERVICES/services/disable_quotes_by_catid.php?docid=".$docidarr['docid']."&national_catid=".$nationacatdatring."&updated_by=csgenio-".urlencode($this->insert_arr['ucode']);
						}
						$this->general_curl_call($sfdeactivtionurl);
						
					}
				}
			}
			/******************** Deleted category shopfront api   ******************/	
				
			/**************** tbl_companymaster_generalinfo START *****************/
			/*foreach($this->insert_arr AS $keyField => $valField)
			{
				if(in_array($keyField,$arr_column_genaralinfo))
				{
					$arr_insert_gen[] = $keyField."='".addslashes($valField)."'";
					// $keyField != 'regionid' && $keyField != 'paid'
					if($keyField != 'nationalid' && $keyField != 'parentid' && $keyField != 'sphinx_id' && $keyField != 'virtualNumber' && $keyField != 'virtual_mapped_number' && $keyField != 'docid')
					{
						$arr_update_gen[] = $keyField."='".addslashes($valField)."'";
					}
				}
			}*/
			
			// Added for Building/Landmark level geocode update based on building master matching data
			if(isset($this->insert_arr['flow_module']) && $post_arr['flow_module'] == 'DE' && $post_arr['module']=='CS' && in_array($post_arr['ucode'],$geocode_ucode_arr))
			{
			
			}
			else
			{
				if($this->insert_arr['geocode_accuracy_level'] != 1 || (($edit_flag == 1) &&  (strtoupper($this->insert_arr['building_name_old']) !=strtoupper($this->insert_arr['building_name']))))
				{
					if(!empty($this->insert_arr['building_name']))
					{
						
						$sql_building_check = "SELECT * FROM online_regis1.tbl_building_master_sphinx WHERE entity_area like '%".addslashes($this->insert_arr['building_name'])."%' AND type_flag NOT IN (1,2,3,4) AND display_flag=1 AND pincode='".$this->insert_arr['pincode']."' AND /*areaname='".$this->insert_arr['area']."' and*/ parent_area='".$this->insert_arr['area']."'";
						$res_building_check =  parent::execQuery($sql_building_check, $this->dbConIdc);
						if($res_building_check && mysql_num_rows($res_building_check) == 1)
						{
							$row_building_check = mysql_fetch_assoc($res_building_check);
							$this->insert_arr['geocode_accuracy_level'] = 1;
							$this->insert_arr['latitude']  = $row_building_check['latitude_final'];
							$this->insert_arr['longitude'] = $row_building_check['longitude_final'];
							
							$params_new = Array();
							$params_new['parentid']	=	$this->insert_arr['parentid'];
							$params_new['latitude']	=	$this->insert_arr['latitude'];
							$params_new['longitude']=	$this->insert_arr['longitude'];
							$params_new['data_city']=	$this->insert_arr['data_city'];						
							$params_new['geocode_accuracy_level']=	$this->insert_arr['geocode_accuracy_level'];
							$params_new['rquest']	=	'map_pointer_flag';

							$map_url	=	$urldetails['jdbox_service_url'].'location_api.php';	
							$map_res = json_decode($this->call_curl_post($map_url,($params_new)),true);			
							
							$this->insert_arr['map_pointer_flags'] 		= $map_res['result']['map_pointer_flags'];
							$this->insert_arr['flags'] 					= $map_res['result']['flags'];	
						}
					}
				}
				if(($this->insert_arr['geocode_accuracy_level'] != 2 && $this->insert_arr['geocode_accuracy_level'] != 1) || ($this->insert_arr['geocode_accuracy_level'] != 1 && $edit_flag == 1 && strtoupper($this->insert_arr['landmark_old']) !=strtoupper($this->insert_arr['landmark'])))
				{
					if(!empty($this->insert_arr['landmark']))
					{
						$skip_chars_arr = array('opps','beside','opposite','besides','opp','adjoing','oppoing','adjacent','opping','adjacent to','near','adjacent','nr','adjacent to','closeto','adjent','close to','adj','in front','nxt','infront','nxt to','behind','nearby','b/h','out side','bh','outside','backside','above','back side','below','bhnd','next to','back','next');

						for($i = 0; $i < count($skip_chars_arr); $i++)
						{
							$landmark_sanitised = str_replace($skip_chars_arr[$i],'',strtolower($this->insert_arr['landmark']));
						}
						
						$sql_landmark_check = "SELECT * FROM online_regis1.tbl_areamaster_consolidated_v3 WHERE entity_area like '%".addslashes($landmark_sanitised)."%' AND type_flag = 2 AND display_flag=1  AND pincode='".$this->insert_arr['pincode']."' /*areaname='".$this->insert_arr['area']."'*/ AND parent_area='".$this->insert_arr['area']."'";
						$res_landmark_check =  parent::execQuery($sql_landmark_check, $this->dbConIdc);
						if($res_landmark_check && mysql_num_rows($res_landmark_check) == 1)
						{
							$row_landmark_check = mysql_fetch_assoc($res_landmark_check);
							$this->insert_arr['geocode_accuracy_level'] = 2;
							$this->insert_arr['latitude']  = $row_landmark_check['latitude_final'];
							$this->insert_arr['longitude'] = $row_landmark_check['longitude_final'];
							
							$params_new = Array();
							$params_new['parentid']	=	$this->insert_arr['parentid'];
							$params_new['latitude']	=	$this->insert_arr['latitude'];
							$params_new['longitude']=	$this->insert_arr['longitude'];
							$params_new['data_city']=	$this->insert_arr['data_city'];						
							$params_new['geocode_accuracy_level']=	$this->insert_arr['geocode_accuracy_level'];
							$params_new['rquest']	=	'map_pointer_flag';

							$map_url	=	$urldetails['jdbox_service_url'].'location_api.php';	
							$map_res = json_decode($this->call_curl_post($map_url,($params_new)),true);			
							
							$this->insert_arr['map_pointer_flags'] 		= $map_res['result']['map_pointer_flags'];
							$this->insert_arr['flags'] 					= $map_res['result']['flags'];	
						}
					}
				}
			}
			$badword_check_source			=	Array('CS','DIALER VALIDATION','TME','ME','TME MODULE - OTHERS FIELD LIVE','TME NEW ENTRY','JDA','JOINFREE','AD PROGRAM','ADPROGRAMS','ADPROGROGRAMS','TME MASK_AUTO PROCESS','JOINFREE-WEBSITE','VC DIGITIZATION DATA','VC PRINTING PRESS','JOINFREE DATA (WAP)','JOINFREE DATA (APP)');
			$address_badword_check_source	=	Array('JOINFREE','AD PROGRAM','ADPROGRAMS','ADPROGROGRAMS');
			
			if((!array_key_exists('badword_check',$this->insert_arr)) || $this->insert_arr['badword_check'] != 'skip_check_badword')
			{
				if((trim(strtolower($this->insert_arr['companyname_old'])) !=trim(strtolower($this->insert_arr['companyname'])))&&((in_array(strtoupper($this->insert_arr['source']), $badword_check_source) && strtoupper($this->insert_arr['source']) != 'CS') || (strtoupper($this->insert_arr['source']) == 'CS' && isset($this->insert_arr['flow_module']) && $this->insert_arr['flow_module']!='DE')))
				{	
					$badword_flag = $this->badword_check($this->insert_arr);
					if(in_array(strtoupper($this->insert_arr['source']), $badword_check_source) && isset($this->insert_arr['paid']) && $this->insert_arr['paid'] == 0 && (strtolower($badword_flag['type']) == 'slang word' || strtolower($badword_flag['type']) == 'legal' || strtolower($badword_flag['type']) == 'confine word' || strtolower($badword_flag['type']) == 'brand restricted'))
					{
						$this->insert_arr['mask'] = '1'; 
						$insert_mask_log = "INSERT INTO tbl_compMask_details
								SET 
							parentid 	= '".$this->insert_arr['parentid']."',
							contractId 	= '".$this->insert_arr['parentid']."',
							reason		= 'Restricted Word-Mask',
							createdBy	= '".$this->insert_arr['updatedBy']."',	
							mask		= '1',
							date_time   = now()";	 
						$res_mask_log =  parent::execQuery($insert_mask_log, $this->dbConDjds);	
					}	
					unset($this->insert_arr['badword_check']);	
				} 
			}
			
			if(isset($this->insert_arr['send_to_common_audit']) && $this->insert_arr['send_to_common_audit']==1)
				$badword_flag = $this->badword_check($this->insert_arr);
			
			############################Start:-push contract to datacorrection,if address have badword###################
			if(in_array(strtoupper($this->insert_arr['source']), $address_badword_check_source) && (!isset($badword_flag['allow_flag']) || ($badword_flag['allow_flag']=='0')))
			{
				$badword_flag = $this->badword_check($this->insert_arr,'address');
			}
			############################End:-push contract to datacorrection,if address have badword#######################
			
			
			$badword_sent_flag	=	0;
			if(in_array(strtoupper($this->insert_arr['source']), $badword_check_source) || (isset($this->insert_arr['send_to_common_audit']) && $this->insert_arr['send_to_common_audit']==1))
			{	
				if(isset($badword_flag['allow_flag']) && ($badword_flag['allow_flag'] == '1' || $badword_flag['allow_flag'] == '2'))
				{
					$badword_sent_flag	=	1;
					$extra_info	=array();
					$this->data_correction_api($this->insert_arr,'BADWORD',$extra_info,$compOldData);	
			    }
			}
			
			if(in_array(strtoupper($this->insert_arr['source']), $address_badword_check_source) && $badword_sent_flag == 0)
			{
				$extra_info	=array();
				if(isset($badword_flag['allow_flag']) && $badword_flag['allow_flag'] == '2')
					$this->data_correction_api($this->insert_arr,'BADWORD',$extra_info,$compOldData);	
			}
			
			
			
			
			############################Start:-check overwritten data############################################
			$overwritten_check_source_arr	=	array('TME','JDA','ME','TME MASK_AUTO PROCESS');
			if(in_array(strtoupper($this->insert_arr['source']),$overwritten_check_source_arr) && (isset($this->insert_arr['freeze']) && $this->insert_arr['freeze'] == '0')  && (isset($this->insert_arr['mask'])  && $this->insert_arr['mask'] == '0'))
			{
				$check_overwritten_data = $this->check_overwritten_data($this->insert_arr,'OVERWRITTEN_DATA');
			}
			############################End:-check overwritten data############################################
			
			//send mail to dbescalations if super premium and authorized categories removed by users 
			
			//$compOldData	=	$this->get_cmp_old_data($this->insert_arr['parentid']);
			$category_check_source_arr	=	array('TME','JDA','ME','TME MODULE - OTHERS FIELD LIVE','TME NEW ENTRY');
			if(in_array(strtoupper($this->insert_arr['source']),$category_check_source_arr) && !empty($compOldData) && count($compOldData)>0)
			{
				$check_category_arr = $this->check_categories($this->insert_arr['catidlineage'],$compOldData['catidlineage'],$this->insert_arr);
			}
		
			//check companyname and send for moderation 
			$this->check_repeated_words($this->insert_arr);
			
			//push send_to_common_audit
			if(isset($this->insert_arr['send_to_common_audit']) && $this->insert_arr['send_to_common_audit']==1 && (!isset($badword_flag['allow_flag']) || ($badword_flag['allow_flag']=='0') ))
			{
				$extra_info['priority_field'] = 'BACKEND_DATA';
				$this->data_correction_api($this->insert_arr,'UNIVERSAL_RULE',$extra_info);
			}
			
			//populate chain outlets main table
			if(strtoupper($this->insert_arr['source']) == 'DE' || strtoupper($this->insert_arr['source']) == 'CS')
			{
				$response_arr	=	$this->populate_chain_outlet_main_tbl($this->insert_arr);
			}
			
			// Added  by shital patil to update area_display column of table tbl_companymaster_generalinfo - 18-01-2016 
			$area 			= "";
			$area_display 	= "";

			$comp_update_arr = array();
			$comp_update_arr['usrid']		=$this->insert_arr['ucode'];
			$comp_update_arr['usrnm'] 		=$this->insert_arr['uname'];
			$comp_update_arr['data_city'] 	=$this->data_city;
			$comp_update_arr['parentid'] 	=$this->parentid;
			$comp_update_arr['rsrc'] 	 	=$this->insert_arr['source'];
			
			$update_data	 = array();
			$gen_table_arr	 = array();
			$extra_table_arr = array();
			
			foreach($this->insert_arr AS $keyField => $valField)
			{
				if(in_array($keyField,$arr_column_genaralinfo))
				{
					switch(strtolower(trim($keyField)))
					{
						case 'building_name':
						case 'street'       :
						case 'landmark'     :
						case 'full_address' :
						case 'contact_person':
						case 'contact_person_display':
							$valField = str_replace(',',', ',$valField);
							$valField = str_replace('(','( ',$valField);
							$valField = ucwords($valField);
							$valField = str_replace(', ',',',$valField);
							$valField = str_replace('( ','(',$valField);
							$valField = str_replace(' To ',' to ',$valField);
							$valField = str_replace(' And ',' and ',$valField);
						break;
						default:
					}
					if($keyField == "area")
					{
						$arr_insert_gen[] = $keyField."='".addslashes($valField)."'";
						$area = $valField;
						$sql_area = "SELECT areaname_display_wo_cityname FROM tbl_areamaster_consolidated_v3 WHERE areaname='".$valField."' AND type_flag=1 AND display_flag=1 LIMIT 1";
						$res_area =  parent::execQuery($sql_area, $this->dbConDjds);
						if($res_area && mysql_num_rows($res_area)>0)
						{
							$row_area = mysql_fetch_assoc($res_area);
							$area_display = $row_area['areaname_display_wo_cityname'];
							$arr_insert_gen[] = "area_display='".addslashes($area_display)."'";							
						}
					}
					else if($keyField == "full_address")
					{
						$full_address_display  = str_replace($area,$area_display,$valField);
						$arr_insert_gen[] = "full_address='".addslashes($full_address_display)."'";		
					}
					else
					{
						$arr_insert_gen[] = $keyField."='".addslashes($valField)."'";
					}
					
					// $keyField != 'regionid' && $keyField != 'paid'
					if($keyField != 'nationalid' && $keyField != 'parentid' && $keyField != 'virtualNumber' && $keyField != 'virtual_mapped_number' && $keyField != 'docid')
					{	
						$area 			= "";
						$area_display 	= "";	
						if($keyField == "area")
						{
							$arr_update_gen[] = $keyField."='".addslashes($valField)."'";
							$area = $valField;
							$sql_area = "SELECT areaname_display_wo_cityname FROM tbl_areamaster_consolidated_v3 WHERE areaname='".$valField."' AND type_flag=1 AND display_flag=1 LIMIT 1";
							$res_area =  parent::execQuery($sql_area, $this->dbConDjds);
							if($res_area && mysql_num_rows($res_area)>0)
							{
								$row_area = mysql_fetch_assoc($res_area);
								$area_display = $row_area['areaname_display_wo_cityname'];
								$arr_update_gen[] = "area_display='".addslashes($area_display)."'";			
							}							
						}
						else if($keyField == "full_address")
						{
							$full_address_display  = str_replace($area,$area_display,$valField);
							$arr_update_gen[] = "full_address='".addslashes($full_address_display)."'";		
						}
						else
						{
							$arr_update_gen[] = $keyField."='".addslashes($valField)."'";
						}						
					}
				}
			}
			//End
			if(!empty($arr_insert_gen) && is_array($arr_insert_gen) && count($arr_insert_gen) > 0)
				$sql_str_gen	=	implode(",",$arr_insert_gen);
			
			if(!empty($arr_update_gen) && is_array($arr_update_gen) && count($arr_update_gen) > 0)
				$sql_str_updt_gen	=	implode(",",$arr_update_gen);
			
			if(!empty($arr_insert_gen) && is_array($arr_insert_gen) && count($arr_insert_gen) > 0){
				foreach($arr_insert_gen as $index=>$gen_info_column){
					$gen_col_arr = array();
					
					if($gen_info_column!=''){
						$gen_col_arr =	explode("=",$gen_info_column);
					}
					$gen_col_name 	='';
					$gen_col_val 	='';				
					$gen_col_name 		=	$gen_col_arr['0'];
					$gen_col_val 		=	$gen_col_arr['1'];
					if($gen_col_name!=''){
						$gen_table_arr[$gen_col_name] = trim($gen_col_val,"''");
					}
				}
			}
			if(!empty($arr_update_gen) && is_array($arr_update_gen) && count($arr_update_gen) > 0){
				foreach($arr_update_gen as $index1=>$gen_upd_column){
					$gen_col_arr = array();
					if($gen_upd_column!=''){
						$gen_col_arr =	explode("=",$gen_upd_column);
					}
					$gen_col_name 	='';
					$gen_col_val 	='';				
					$gen_col_name 		=	$gen_col_arr['0'];
					$gen_col_val 		=	$gen_col_arr['1'];
					
					if(!array_key_exists($gen_col_name,$gen_table_arr)){
						$gen_table_arr[$gen_col_name] = trim($gen_col_val,"''");		
					}
				}
			}
			if($this->insert_arr['freeze'] == '1')
			{
				$this->insert_arr['deactflg'] = 'F';
			}
			else if($this->insert_arr['freeze'] == '0')
			{
				$this->insert_arr['deactflg'] = 'N';
			}	
			// For companymaster class
			if($this->insert_arr['session_tme']	== 'session_tme' && $this->insert_arr['paid'] == 1)
			{
				$generalinfo_table 	= 'tbl_companymaster_generalinfo';
				$extradetails_table = 'tbl_companymaster_extradetails';
			}
			else
			{
				$generalinfo_table 		= $this->getFinalTableName('tbl_companymaster_generalinfo',$this->tableno);
				$extradetails_table 	= $this->getFinalTableName('tbl_companymaster_extradetails',$this->tableno);
			}
			//	End
			$query_insert_gen	=	"INSERT INTO ".$generalinfo_table." SET ";
			
			$query_on_dup_gen	=	" ON DUPLICATE KEY UPDATE ";
			
			if(!empty($sql_str_gen) &&  trim($this->insert_arr['sphinx_id']))
			{
				$query_insert_gen  .= 	$sql_str_gen.$query_on_dup_gen.$sql_str_updt_gen;
				
				if($this->insert_arr['session_tme']	== 'session_tme' && $this->insert_arr['paid'] == 1)
				{
					$result_gen			=	parent::execQuery($query_insert_gen, $this->dbConIdc);
				}
				else
				{
					if($this->live_comp == 1){
						//$result_gen			=	parent::execQuery($query_insert_gen, $this->dbConIro);
					}
					else{						
						$result_gen			=	parent::execQuery($query_insert_gen, $this->dbConIro);
					}
				}
			}else
			{
				$this->insert_tbl_absent_parentid_table_details($parentid,'tbl_id_generator',$sql_str_gen);
			}
			
			/******************** tbl_companymaster_generalinfo END ****************/
			/*---------------------------------------------------------------------*/
			/******************* tbl_companymaster_extradetails START **************/
			foreach($this->insert_arr AS $keyField => $valField)
			{
				if(in_array($keyField,$arr_column_extradetails))
				{
					if($keyField == 'misc_flag'){
						if($this->live_comp == 1){
							//$arr_insert_extra[] = $keyField."=".$valField;
						}
						else{							
							$arr_insert_extra[] = $keyField."=".$valField;
						}					
					}else{
						$arr_insert_extra[] = $keyField."='".addslashes($valField)."'";
					}
					//&& $keyField != 'regionid' 
					if($keyField != 'nationalid' && $keyField != 'parentid' && $keyField != 'createdby' && $keyField != 'createdtime' && $keyField != 'original_creator' && $keyField != 'original_date')
					{
						if($keyField == 'misc_flag'){
							if($this->live_comp == 1){
								//$arr_update_extra[] = $keyField."=".$valField;
							}
							else{								
								$arr_update_extra[] = $keyField."=".$valField;
							}
						}else{
							$arr_update_extra[] = $keyField."='".addslashes($valField)."'";
						}
					}
				}
			}
                   
          $live_update = 0;
            			
			if(!empty($arr_insert_extra) && is_array($arr_insert_extra) && count($arr_insert_extra) > 0)
				$sql_str_extra		=	implode(",",$arr_insert_extra);
				
			if(!empty($arr_update_extra) && is_array($arr_update_extra) && count($arr_update_extra) > 0)
				$sql_str_updt_extra	=	implode(",",$arr_update_extra);
			
			$bitwise_arr = array('type_flag','iro_type_flag','website_type_flag','businesstags','misc_flag','flags');			
			
			if(!empty($arr_insert_extra) && is_array($arr_insert_extra) && count($arr_insert_extra) > 0){
				foreach($arr_insert_extra as $idx=>$exta_info_column){
					$extra_col_arr = array();
					
					if($exta_info_column!=''){
						$extra_col_arr =	explode("=",$exta_info_column);
					}
					$extra_col_name 	='';
					$extra_col_val 		='';				
					$extra_col_name 	=	$extra_col_arr['0'];
					$extra_col_val 		=	$extra_col_arr['1'];
					
					//$post_arr['type_flag']['set']; 
					
					if(in_array(strtolower($extra_col_name),$bitwise_arr)){												
						if(is_array($this->insert_arr[$extra_col_name])){
							if($this->insert_arr[$extra_col_name]['set']!='' || $this->insert_arr[$extra_col_name]['unset']!=''){
								//echo "<br>inside ".$extra_col_name;
								$extra_table_arr[$extra_col_name] = $this->insert_arr[$extra_col_name];
							}
						}
					}
					else if($extra_col_name!=''){
						$extra_table_arr[$extra_col_name] = trim($extra_col_val,"''");	
					}		
				}
			}
			if(!empty($arr_update_extra) && is_array($arr_update_extra) && count($arr_update_extra) > 0){
				foreach($arr_update_extra as $idx1=>$exta_upd_column){
					$extra_col_arr = array();
					if($exta_upd_column!=''){
						$extra_col_arr =	explode("=",$exta_upd_column);
					}
					$extra_col_name 	='';
					$extra_col_val 		='';				
					$extra_col_name 	=	$extra_col_arr['0'];
					$extra_col_val 		=	$extra_col_arr['1'];
					
					if(!array_key_exists($extra_col_name,$extra_table_arr)){
						if(in_array(strtolower($extra_col_name),$bitwise_arr)){
							//echo "inside";
							if(is_array($this->insert_arr[$extra_col_name])){
								if($this->insert_arr[$extra_col_name]['set']!='' || $this->insert_arr[$extra_col_name]['unset']!=''){
									$extra_table_arr[$extra_col_name] = $this->insert_arr[$extra_col_name];
								}
							}
						}
						else if($extra_col_name!=''){
							$extra_table_arr[$extra_col_name] = trim($extra_col_val,"''");
						}		
					}	
				}
			}
			
			$query_insert_extra	=	"INSERT INTO ".$extradetails_table." SET ";
			
			$query_on_dup_extra	=	" ON DUPLICATE KEY UPDATE ";
			
			if(!empty($sql_str_extra) && $result_gen &&  trim($this->insert_arr['sphinx_id']))
			{
				$query_insert_extra  .=	$sql_str_extra.$query_on_dup_extra.$sql_str_updt_extra;
				
				if($this->insert_arr['session_tme']	== 'session_tme' && $this->insert_arr['paid'] == 1)
				{
					$result_extra		=	parent::execQuery($query_insert_extra, $this->dbConIdc);
				}
				else
				{
					$live_update = 1;
					
					if($this->live_comp == 1){
						//~ $result_extra		=	parent::execQuery($query_insert_extra, $this->dbConIro);
					
						//~ if(!$result_extra)
						//~ {
							//~ $this->insert_tbl_absent_parentid_table_details($parentid,'tbl_companymaster_extradetails',$query_insert_extra);
							
						//~ }
					}
					else{						
						$result_extra		=	parent::execQuery($query_insert_extra, $this->dbConIro);					
						if(!$result_extra)
						{
							$this->insert_tbl_absent_parentid_table_details($parentid,'tbl_companymaster_extradetails',$query_insert_extra);
							
						}
					}					
				}
			}else
			{
				if($this->live_comp == 1){
					//$this->insert_tbl_absent_parentid_table_details($parentid,'tbl_companymaster_generalinfo',$query_insert_gen);
				}
				else{					
					$this->insert_tbl_absent_parentid_table_details($parentid,'tbl_companymaster_generalinfo',$query_insert_gen);
				}
			}	
			
			//~ $query_insert_gen  .= 	$sql_str_gen.$query_on_dup_gen.$sql_str_updt_gen;
			//~ $query_insert_extra  .=	$sql_str_extra.$query_on_dup_extra.$sql_str_updt_extra;
			
			//echo "<pre>extra table arr";print_r($extra_table_arr);
			$update_data['gen_info_id'] 	= $gen_table_arr;
			$update_data['extra_det_id'] 	= $extra_table_arr;
						
			$comp_update_arr['update_data'] = json_encode($update_data);
			$comp_update_arr['page'] 		= 'insertLiveClass';
			$comp_update_arr['action'] 		= 'updatedata';
						
			$comp_upd_res 	= '';
			$comp_upd_arr 	= array();
			if($this->live_comp == 1){
				$comp_upd_res	=	$this->companyClass_obj->getCompanyInfo($comp_update_arr);
				if($comp_upd_res!=''){
					$comp_upd_arr = 	json_decode($comp_upd_res,TRUE);
				}			
			}
			
			/******************* tbl_companymaster_extradetails END  **************/
			//if($this->insert_arr['calling_source'] == 'delite')
			if(isset($this->insert_arr['add_infotxt']))
			{
				$insert_addInfo = "INSERT INTO d_jds.tbl_comp_addInfo
				SET 
				contractId = '".$this->insert_arr['parentid']."',
				parentid = '".$this->insert_arr['parentid']."',
				lockDateTime = now(),
				data_city  = '".$this->insert_arr['data_city']."',
				add_infotxt =  \"".addslashes($this->insert_arr['add_infotxt'])."\"";
				$res_insert_addInfo 	= parent::execQuery($insert_addInfo, $this->dbConDjds);
			}
			
			/*$contract_edit_flag = 0;
			$sql_check_live =	"SELECT parentid FROM tbl_companymaster_extradetails WHERE parentid ='".$this->insert_arr['parentid']."'";
			$res_check_live = 	parent::execQuery($sql_check_live,$this->dbConIro);
			if(parent::numRows($res_check_live)>0){
				$contract_edit_flag = 1;
			}*/
			if($live_update == 1){
			$curl_url =	$urldetails['jdbox_service_url'].'add_update_social.php';
			$social_params_arr = array();
			$social_params_arr['parentid'] 	= 	$this->insert_arr['parentid'];
			if($this->insert_arr['city']!=''){
				$city 	=	$this->insert_arr['city'];
			}
			else{
				$city 	=	$this->insert_arr['data_city'];
			}	
			$social_params_arr['city'] 					= 	$city;
			$social_params_arr['data_city'] 			= 	$this->insert_arr['data_city'];
			$social_params_arr['companyname'] 			=	$this->insert_arr['companyname'];
			//$social_params_arr['contract_edit_flag'] 	=	$live_update;
			$social_params_arr['mobile'] 				=	$this->insert_arr['mobile'];

			$this->call_curl_post($curl_url,json_encode($social_params_arr));
			}			
			/*********************calling attributes api here starts*******************************/
			if(isset($this->insert_arr['flow_module'])){
				$params_arr = Array();
				$params_arr['parentid']	 =	$this->insert_arr['parentid'];
				$params_arr['data_city'] =	$this->insert_arr['data_city'];
				$params_arr['action'] 	 = 'temp_to_main';
				$params_arr['module']    =  $this->insert_arr['flow_module'];
				$params_arr['ucode']     =  $this->insert_arr['ucode'];
				$configclassobj = new configclass();
				$urldetails = $configclassobj->get_url(urldecode($this->insert_arr['data_city']));
				$curl_url	=	$urldetails['jdbox_service_url'].'attributes_temp_to_main.php';	
				$result     = json_decode($this->call_curl_post($curl_url,$params_arr),true);	

			}
			/*********************calling attributes api here ends*******************************/
			
			/*-------------Updating Category Reset Flag Starts----------------------*/

			if(strtolower($this->insert_arr['source']) == 'me'){	

				$sql_update_cs_info = "UPDATE tbl_cs_fetch_info 
										SET
									 	edit_flag = 0,updatedate = '".date('Y-m-d H:i:s')."' WHERE
										parentid = '".$this->insert_arr['parentid']."' ";
			
				$res_cs_info 		=	parent::execQuery($sql_update_cs_info,$this->dbConIdc);
				
			}
			
			if(strtolower($this->insert_arr['source'])== 'cs' || strtolower($this->insert_arr['source'])== 'tme' || strtolower($this->insert_arr['source'])== 'me')
			{
				$cat_res = $this->resetCatFlag();
				$this->updateCCRDealcloseFlag();
			}
			
			/*-------------Updating Category Reset Flag Ends------------------------*/
			/*--------------------------------------------------------------------*/
			/*********************** Brand Name Auditing Module START *************/
			if(($this->insert_arr['paid'] != '1') && ($this->insert_arr['flow_module'] != 'DE'))
			{
				$curl_brand_url =	$urldetails['jdbox_service_url'].'update_brand_api.php';
				$this->updateBrandName($this->insert_arr,$curl_brand_url);
			}
			/*************************Brand Name Auditing Module END **************/
			/*--------------------------------------------------------------------*/
			/******** tbl_companymaster_extradetails_national, tbl_companymaster_search_national START ********/
			// In case of multicity contracts
			
			if($this->insert_arr['multicity'] == 'multicity')
			{
				$this->updateNationalListing($this->insert_arr);
			}
			
			/******** tbl_companymaster_extradetails_national, tbl_companymaster_search_national END **********/
			/*--------------------------------------------------------------------*/
			/*********************** tbl_companymaster_search START ***************/
			
			if($this->insert_arr['mask'] == 1 && $this->insert_arr['paid'] == 0)
			{
				$this->insertCompanymasterSearch($this->insert_arr);
			}
			
			/*************************tbl_companymaster_search END ****************/
			/*--------------------------------------------------------------------*/
			/*********************** tbl_contract_tmeDetails START ****************/
			
			//$this->insertContractTmeDetails($this->insert_arr);
			
			/*********************** tbl_contract_tmeDetails END ******************/
			/*--------------------------------------------------------------------*/
			/*********************** tbl_tmesearch START **************************/
			
			$this->insertTmeSearch($this->insert_arr);
			
			/*********************** tbl_tmesearch END ****************************/
			/*--------------------------------------------------------------------*/
			/*********************** tbl_company_source START *********************/

			$this->insertCompanySource($this->insert_arr);
			
			/*********************** tbl_company_source END ***********************/
			
			if($this->insert_arr['savenonpaidjda'] == 1){
				$this->saveDialerInfo();
			}

			/*--------------------------------------------------------------------*/
			/*********************** Used in Paid Data Correction for Supreme package by Sneha Start **********************************/
			if($this->insert_arr['paid'] == 1)
			{
				$this->curl_generate_bidcat_details($this->insert_arr['bid_cat_url']);
			}

			if( (isset($this->insert_arr['save_nonpaid_cat']) && $this->insert_arr['save_nonpaid_cat'] == 1) || isset($this->insert_arr['paidflow']) && $this->insert_arr['paidflow']=='savenext')
			{
				$this->purePackageBiddingtableUpdate();
			}
			
			/*********************** curl_generate_bidcat_details END **********************************/
			/*--------------------------------------------------------------------*/
			/*********************** web_api END **********************************/
			//web_api will b called in ALL cases, except contract which goes on IDC (paid contracts - ME & TME)
			if(($this->insert_arr['source']	== 'TME' || $this->insert_arr['source']	== 'ME') && $this->insert_arr['paid'] == 1)
			{
				//  DONT CALL WEB_API AS DATA GOES ON .233 SERVERS
			}
			else
			{
				$this->curl_web_api($this->insert_arr,'2');
			}

			if($live_update == 1)
			{
				require_once('services/includes/category_sendinfo_class.php');
				$imgParams['parentid']                      = $this->insert_arr['parentid'];
				$imgParams['data_city']                     = $this->insert_arr['data_city'];
				$imgParams['national_catidlineage_search'] 	= $old_ncatsrch;
				$imgParams['hotcategory']                   = $old_hotcat;
				$cat_info_obj = new category_sendinfo_class($imgParams);
				$data_arr 	  = $cat_info_obj->sendCatInfo();

			}
			
			/*********************** web_api END **********************************/
			/*--------------------------------------------------------------------*/
			/*********************** tbl_company_consolidate START ****************/
			
			$this->insertCompanyConsolidate($this->insert_arr);
			
			/*********************** tbl_company_consolidate END ******************/
			/*--------------------------------------------------------------------*/
			/*********************** tbl_paid_narration START ********************/
			
			$this->insertPaidNarration($this->insert_arr);
			
			/*********************** tbl_paid_narration END ********************/
			
			/*********************** top listing START ********************/
			$brandname_check			=	$this->checkBrandname();			
			$omit_source = Array('TME DC','TM DATA CORRECTION (F10)','DE','CS','DIALER VALIDATION','TME NEW ENTRY');			
			if((isset($this->insert_arr['paid']) && $this->insert_arr['paid'] == 0 && !in_array(strtoupper($this->insert_arr['source']),$omit_source) && !(isset($this->insert_arr['flow_module']) && strtolower($this->insert_arr['flow_module']) == 'common_audit') && strtolower($brandname_check)  != 'companyname is matching with brandname' && $edit_flag == 0) || strtolower($this->insert_arr['source']) == 'tme mask_auto process')
			{
				if(!(isset($this->insert_arr['skip_top_listing_check'])) && $this->insert_arr['skip_top_listing_check'] != 1)
				{
					$top_listing_result = $this->check_top_listing($this->insert_arr);
						if($top_listing_result && $top_listing_result['result']['top_five_listing']	==	'1')
							$result	=	$this->data_correction_api($this->insert_arr,'top_listing');
				}
			}
			/*********************** top listing END ********************/
			
			/*********************** Instant Live START ********************/
			
			//take instant insta porting if contacts updated by JD Employee
			$omit_insta_source_arr = array('JDA','ME');	
			if(!in_array(strtoupper($this->insert_arr['source']),$omit_insta_source_arr) && isset($this->insert_arr['mask']) && $this->insert_arr['mask'] ==0 && isset($this->insert_arr['freeze']) && $this->insert_arr['freeze'] == 0)
			{
				$jd_emp_arr	=	$this->check_jd_employee(trim($this->insert_arr['updatedBy']),$this->insert_arr['data_city']);
				if(!empty($jd_emp_arr) && count($jd_emp_arr)>0 && $jd_emp_arr['errorCode'] ==0 && $jd_emp_arr['numRows']>0)
				{
					$this->insert_arr['instantlive_flag'] = 1;
				}
			}
			
			
			// Added by shital patil - instant live for DE/CS active non paid contracts only
			if($this->insert_arr['instantlive_flag'] == 1)
			{				 
				//$sql_status_check = "SELECT blockforweb FROM tbl_companymaster_search WHERE parentid = '".$this->insert_arr['parentid']."'";
				//$res_status_check	= parent::execQuery($sql_status_check, $this->dbConIro);
				
				//if($res_status_check && mysql_num_rows($res_status_check)>0)
				//{
					//$row_status_check = mysql_fetch_assoc($res_status_check);
					//if($row_status_check['blockforweb'] == '0')
					//{ 
						$configclassobj= new configclass();
						$urldetails= $configclassobj->get_url(urldecode($this->insert_arr['data_city']));
						$curl_url_il=$urldetails['jdbox_service_url'].'instant_live.php';
						
						$param_array['parentid'] 		=	$this->insert_arr['parentid'];
						$param_array['data_city'] 		=	$this->insert_arr['data_city'];
						$param_array['module'] 			=	$this->insert_arr['source'];	
						$param_array['ucode'] 			=	$this->insert_arr['ucode'];	
						if(isset($this->insert_arr['movie_insta']) && $this->insert_arr['movie_insta']=='1')
							$param_array['movie_insta'] =	'1';	
						
						$instant_url =  $curl_url_il."?".http_build_query($param_array);
						$datacity_upper = strtoupper(trim($param_array['data_city']));
						$maincities_array = array("MUMBAI","DELHI","KOLKATA","BANGALORE","CHENNAI","PUNE","HYDERABAD","AHMEDABAD");
						if(in_array($datacity_upper,$maincities_array)){
							$datacitystr = $param_array['data_city'];
						}else{
							$datacitystr = "Remote";
							$instant_url = "http://192.168.17.217:811/services/instant_live.php?".http_build_query($param_array);						
						}
						$insert_instant = "INSERT INTO online_regis1.tbl_instant_live 
						SET 
						parentid	=	'".$param_array['parentid']."',
						data_city	=	'".$datacitystr."',
						url			=	'".$instant_url."',
						source		=	'".$this->insert_arr['source']."',
						ucode		=	'".$this->insert_arr['ucode']."',
						entry_date	=	now()";
						$res_instant =  parent::execQuery($insert_instant, $this->dbConIdc);					 
					//}				
				//} 	 				
			}
			
			/*********************** Instant Live END ********************/			
			$omit_source_badword = Array('DE','CS');
			//if($this->insert_arr['paid'] == 0 && !in_array(strtoupper($this->insert_arr['source']),$omit_source_badword))
			
				
			
		}
		}
		else
		{
			die('Companyname, city and data_city can not be blank');
		}
	}
	
	function logCsDetails(){
		
		$configclassobj= new configclass();
		$urldetails= $configclassobj->get_url(urldecode($this->insert_arr['data_city']));
		$curlurl=$urldetails['jdbox_service_url'].'contract_type.php';
		
		$data['parentid'] 	= 	$this->insert_arr['parentid'];
		$data['data_city'] 	= 	$this->insert_arr['data_city'];
		$data['rquest']		=	'get_contract_type';
		$ret = $this->call_curl_post($curlurl,($data)); 
		$ret =	json_decode($ret,true);
		$paid_flag = $ret['result']['paid'];
		
		$comp_params = array();
		$comp_params['data_city'] 	= $this->data_city;
		$comp_params['table'] 		= 'extra_det_id,gen_info_id';		
		$comp_params['parentid'] 	= $this->insert_arr['parentid'];
		$comp_params['fields']		= 'sphinx_id,parentid,companyname,city,state,building_name,area,pincode,street,landmark,mobile,landline,tollfree,email,website,data_city,catidlineage,';
		$comp_params['action']		= 'fetchdata';
		$comp_params['page']			= 'insertLiveClass';
		$comp_params['skip_log']		= 1;


		$comp_api_arr	= array();
		$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
		if($comp_api_res!=''){
		   $comp_api_arr 	= json_decode($comp_api_res,TRUE);
		}
		if($comp_api_arr['errors']['code']=='0')
		{
			$live_array= $comp_api_arr['results']['data'][$this->insert_arr['parentid']];
			
			if(strtoupper($this->insert_arr['source']) == 'DE' || strtoupper($this->insert_arr['source']) == 'CS')
			{
				$change_val_array = Array('sphinx_id','parentid','companyname','city','state','building_name','area','pincode','street','landmark','mobile','landline','tollfree','email','website','category','data_city','source');		
				$changed_array = Array();
				foreach($this->insert_arr as $key=>$value){
					if(in_array($key,$change_val_array)){
						$changed_array[$key] = $value;						
					}					
				}
				if($this->insert_arr['page'] == 'save_main') 	
						$changed_array['category'] = $live_array['catidlineage'];
						
					
				if(array_diff($live_array,$changed_array))
				{
				
					$insert = "INSERT INTO  d_jds.tbl_cs_logdetails SET
						sphinx_id 		=	'".$live_array['sphinx_id']."',
						parentid 		=	'".$live_array['parentid']."',
						companyname_old =	'".addslashes($live_array['companyname'])."',
						companyname_new =	'".addslashes($changed_array['companyname'])."',
						city_old 		=	'".$live_array['city']."',
						city_new 		=	'".$changed_array['city']."',
						state_old 		=	'".$live_array['state']."',
						state_new 		=	'".$changed_array['state']."',
						building_old 	=	'".addslashes($live_array['building_name'])."',
						building_new 	=	'".addslashes($changed_array['building_name'])."',
						area_old 		=	'".addslashes($live_array['area'])."',
						area_new 		=	'".addslashes($changed_array['area'])."',
						pincode_old 	=	'".$live_array['pincode']."',
						pincode_new 	=	'".$changed_array['pincode']."',
						street_old 		=	'".addslashes($live_array['street'])."',
						street_new 		=	'".addslashes($changed_array['street'])."',
						landmark_old 	=	'".addslashes($live_array['landmark'])."',
						landmark_new 	=	'".addslashes($changed_array['landmark'])."',
						mobile_old 		=	'".$live_array['mobile']."',
						mobile_new 		=	'".$changed_array['mobile']."',
						landline_old 	=	'".$live_array['landline']."',
						landline_new 	=	'".$changed_array['landline']."',
						tollfree_old 	=	'".$live_array['tollfree']."',
						tollfree_new 	=	'".$changed_array['tollfree']."',
						email_old 		=	'".addslashes($live_array['email'])."',
						email_new 		=	'".addslashes($changed_array['email'])."',
						website_old 	=	'".addslashes($live_array['website'])."',
						website_new 	=	'".addslashes($changed_array['website'])."',
						category_old 	=	'".$live_array['category']."',
						category_new 	=	'".$changed_array['category']."',
						data_city_old 	=	'".$live_array['data_city']."',
						data_city_new 	=	'".$changed_array['data_city']."',
						paid 			=	'".$paid_flag."',					
						module_type     =	'".$this->insert_arr['source']."',					
						changed_by 		=	'".$this->insert_arr['updatedBy']."',
						changed_on 		=	'".$this->insert_arr['updatedOn']."'";
					$res_ins =  parent::execQuery($insert, $this->data_correction);
				}			
			}	 
			
			$data_change_val_array = Array('companyname','city','area','landmark','data_city');		
			$data_changed_array = Array();
			foreach($this->insert_arr as $key=>$value)
			{
				if(in_array($key,$data_change_val_array))
				{
					$data_changed_array[$key] = $value;	
					$live_arr_det[$key] = $live_array[$key];							
				}					
			}
			 		
			if(array_diff($live_arr_det,$data_changed_array))
			{
				$insert_data = "INSERT INTO  d_jds.tbl_data_change_logdetails SET
					parentid 		=	'".$live_array['parentid']."',
					companyname_old =	'".addslashes($live_array['companyname'])."',
					companyname_new =	'".addslashes($data_changed_array['companyname'])."',
					city_old 		=	'".$live_array['city']."',
					city_new 		=	'".$data_changed_array['city']."',
					area_old 		=	'".addslashes($live_array['area'])."',
					area_new 		=	'".addslashes($data_changed_array['area'])."',
					landmark_old 	=	'".addslashes($live_array['landmark'])."',
					landmark_new 	=	'".addslashes($data_changed_array['landmark'])."',
					data_city_old 	=	'".$live_array['data_city']."',
					data_city_new 	=	'".$data_changed_array['data_city']."',
					paid 			=	'".$paid_flag."',					
					module_type     =	'".$this->insert_arr['source']."',					
					changed_by 		=	'".$this->insert_arr['updatedBy']."',
					changed_on 		=	'".$this->insert_arr['updatedOn']."'";
				$res_ins_data =  parent::execQuery($insert_data, $this->dbConDjds);
			}
		}	
	}
	
	
	function check_top_listing($compData)
	{
		$configclassobj= new configclass();
		$urldetails= $configclassobj->get_url(urldecode($this->insert_arr['data_city']));
		$check_top_listion_url=$urldetails['jdbox_service_url'].'location_api.php';
		
		$param					=	array();
		$param['rquest']        =  'top_five_listing';
		$param['parentid']      =  $compData['parentid'];
		$param['companyname']   =  $compData['companyname'];
		$param['data_city']     =  $compData['data_city'];
		$param['listing_type']  =  '2';
		
		$response				=	$this->call_curl_post($check_top_listion_url,$param);		
		$response				= 	json_decode($response,true);
		return $response;
	}
	
 	function badword_check($compData,$field)
	{	
		$configclassobj	=	new configclass();
		$urldetails		=	$configclassobj->get_url(urldecode($this->insert_arr['data_city']));
		$badword_url	=	$urldetails['jdbox_service_url'].'location_api.php';
	
		$param_bw				   =  array();
		$param_bw['rquest']        =  'badword_check';
		$param_bw['companyname']   =  urlencode($compData['companyname']);
		$param_bw['data_city']     =  urlencode($compData['data_city']);
		
		if(!empty($field) && $field == 'address')
			$param_bw['companyname']   =  urlencode($compData['full_address']);
		else
			$param_bw['companyname']   =  urlencode($compData['companyname']);
		
		
		$response				=	$this->call_curl_post($badword_url,$param_bw);		
		$response				= 	json_decode($response,true);
		return $response['result'];
	}
	function check_doctor_contract($compData)
	{
		$configclassobj= new configclass();
		$dr_urldetails= $configclassobj->get_url(urldecode($compData['data_city']));
		
		$catidlineage_arr = Array();
		$catidlineage_arr = explode(",",str_replace('/','',trim($compData['catidlineage']," ")));
		
		$catids = implode(",",$catidlineage_arr);
		
		$param_dr  =Array();
		$param_dr['rquest'] 		=	'check_doctor_contract';
		$param_dr['data_city'] 		=	$compData['data_city'];
		$param_dr['companyname'] 	=   $compData['companyname'];
		$param_dr['catid'] 			= 	trim($catids,",");		 
		$curl_dr_url 				=	$dr_urldetails['jdbox_service_url']."location_api.php";
		
		$ret_dr 		=	$this->call_curl_post($curl_dr_url,($param_dr)); 
		$ret_dr			=	json_decode($ret_dr,true);	
		 
		return $ret_dr;
	}
	function check_advocate_contract($compData)
	{
		$sql_exclusion	=	"SELECT * FROM db_iro.tbl_contract_bypass_exclusion WHERE parentid='".$compData['parentid']."' AND reasonid=7";
		$res_exclusion 	= parent::execQuery($sql_exclusion, $this->dbConIro);
		$compnm_change = '0';
		if(mysql_num_rows($res_exclusion) > 0)	
		{
			$compname_new = $compData['companyname'];
		}
		else
		{
			$adv_word_arr = Array('advocates ','advocate ','adv .','adv ');
			$compname_new = $compData['companyname'];
			foreach($adv_word_arr AS $key=>$val)
			{
				if(strtolower(substr($compData['companyname'], 0, strlen($val))) == strtolower($val))
				{
					$compname_new = str_ireplace($val,"Adv. ",$compData['companyname']);
					$compnm_change = '1';
					break;
				}	
			}
		}
		$ret_adv_comp_arr = Array();
		$ret_adv_comp_arr['result']['comp_change'] 	  = $compnm_change;
		$ret_adv_comp_arr['result']['companyname_old'] = $compData['companyname'];
		$ret_adv_comp_arr['result']['companyname_new'] = $compname_new;
		 
		return $ret_adv_comp_arr;
	}
	function check_landmark($landmark)
	{
		$response_arr	=	array();
		$valid_flag		=	1;
		$orig_landmark	= 	$landmark;
		$new_landmark	=	$landmark;
		
		if(!empty($landmark))
		{
			$get_landmark	=	"SELECT ignore_words FROM tbl_ignore_words_for_geocode";
			$res_landmark 	= parent::execQuery($get_landmark, $this->dbConDjds);
			
			if(parent::numRows($res_landmark)>0)
			{
				$records_arr	=	array();
				while($rows_data = parent::fetchData($res_landmark))
				{
					$ignore_words_arr[]	=	strtolower($rows_data['ignore_words']);
				}
			}
			$ignore_word_exists	=	0;
			if(in_array(trim(strtolower($landmark)),$ignore_words_arr))
			{
				$valid_flag		=	0;
				$new_landmark	=	'';
			}
			else
			{
				foreach($ignore_words_arr as $key_ignore => $val_ignore)
				{
					if(stripos(trim(strtolower($landmark)), strtolower($val_ignore)) === 0)
					{
						$ignore_word_exists	=	1;						
						break;
					}
				}
				if($ignore_word_exists	==	0)
				{
					$valid_flag		=	0;
					$new_landmark	=	'Near '.$landmark;
				}				 
			}			 
		}
		$response_arr['valid_flag']			=	$valid_flag;		
		$response_arr['orig_landmark']		=	$orig_landmark;		
		$response_arr['new_landmark']		=	$new_landmark;		
		
		return $response_arr;
	}
	
	function get_geocode($compData)
	{
		$configclassobj	=	new configclass();
		$urldetails		=	$configclassobj->get_url(urldecode($this->insert_arr['data_city']));
		$url	=	$urldetails['jdbox_service_url'].'geocode_api.php';
		
		if(empty($compData['latitude']) || empty($compData['longitude']))
		{
			$cmp_old_data_arr	=	$this->get_cmp_old_data($compData['parentid']);
			$compData['latitude']	=	$cmp_old_data_arr['latitude'];
			$compData['longitude']	=	$cmp_old_data_arr['longitude'];
		}
		
		$param_arr	=	array();
		$param_arr['rquest']		=	'get_geocode';
		$param_arr['parentid']		=	$compData['parentid'];
		$param_arr['building_name']	=	$compData['building_name'];
		$param_arr['landmark']		=	$compData['landmark'];
		$param_arr['street']		=	$compData['street'];
		$param_arr['area']			=	$compData['area'];
		$param_arr['pincode']		=	$compData['pincode'];	
		$param_arr['city']			=	$compData['city'];
		$param_arr['data_city']		=	$compData['data_city']; 
		$param_arr['latitude']		=	$compData['latitude'];
		$param_arr['longitude']		=	$compData['longitude'];
		$param_arr['source']		=	$compData['source'];
		$param_arr['geocode_accuracy_level']	=	$compData['geocode_accuracy_level'];
		
		$curlresponse				=	$this->call_curl_post($url,$param_arr);				
		$response_arr				= 	json_decode($curlresponse,true);
		
		return $response_arr;
	}
	
	function five_plus_mobile_check($compData)
	{
		$configclassobj	=	new configclass();
		$urldetails		=	$configclassobj->get_url(urldecode($this->insert_arr['data_city']));
		$url			=	$urldetails['jdbox_service_url'].'mobile_check.php';
		
		$params				   	=   array();	
		$params['rquest']		=	'mobile_employee_check';
		$params['data_city']	=	$compData['data_city'];
		$params['module']		=	$compData['source'];
		$params['mobile']		=	$compData['mobile'];
		$params['parentid']		=	$compData['parentid'];
		
		$curlresponse			=	$this->call_curl_post($url,$params);		
		$res_arr				= 	json_decode($curlresponse,true);
		
		$five_plus_mobile_arr = array();
		if(!empty($res_arr) && count($res_arr['data'])>0)
		{
			foreach($res_arr['data'] as $key => $val)
			{
				if($val['company_count']>=5)
				{
					$five_plus_mobile_arr[] = $key;
				}
				elseif($val['company_count']>=2)
				{
					$two_plus_mobile_arr[] = $key;
				}
			}
		}
		$response_arr	=	array();
		$response_arr['five_plus_exits_flag'] = 0;
		if(count($five_plus_mobile_arr)>0)
		{
			$user_mobile_arr	=	explode(",",$compData['mobile']);
			$final_mobile_arr	=	array_diff($user_mobile_arr,$five_plus_mobile_arr);
			$response_arr['five_plus_exits_flag'] 	= 1;
			$response_arr['mobile_new'] 			= implode(',',$final_mobile_arr);
			$response_arr['five_plus_mobile'] 		= implode(',',$five_plus_mobile_arr);
		}
		if(count($two_plus_mobile_arr)>0)
		{
			$response_arr['two_plus_exits_flag'] 	= 1;
			$response_arr['two_plus_mobile'] 		= implode(',',$two_plus_mobile_arr);
		}
		return $response_arr;
	}
	
	function get_cmp_old_data($parentid)
	{
		if(!empty($parentid))
		{
			$sql_cmp_pld_data = "SELECT * FROM tbl_companymaster_generalinfo a JOIN tbl_companymaster_extradetails  b ON a.parentid=b.parentid WHERE a.parentid='".$parentid."' LIMIT 1";
			$res_cmp_pld_data =  parent::execQuery($sql_cmp_pld_data, $this->dbConIro);
			if($res_cmp_pld_data && parent::numRows($res)>0)
			{
				$rows_cmp_pld_data = parent::fetchData($res);
				return $rows_cmp_pld_data;
			}	 
		}
	}
	
	function check_repeated_words($compData)
	{	
		$skip_source_arr = Array('dialer validation','f9 iro data correction','tme dc','event data','tm data correction (f10)');
		
		if($compData['freeze'] == 0 && $compData['mask'] == 0 && ((empty($compData['companyname_old']) || trim(strtolower($this->insert_arr['companyname_old'])) !=trim(strtolower($this->insert_arr['companyname']))) || ($compData['freeze'] == 0 && $compData['mask'] == 0 && ($compData['freeze_old'] == 1 || $compData['mask_old'] == 1))) && !((isset($compData['flow_module']) && $compData['flow_module']=='DE') || (in_array(strtolower($compData['source']),$skip_source_arr) || (isset($this->insert_arr['flow_module']) && strtolower($this->insert_arr['flow_module']) == 'common_audit'))))
		{
			$configclassobj	=	new configclass();
			$urldetails		=	$configclassobj->get_url(urldecode($this->insert_arr['data_city']));
			$url	=	$urldetails['jdbox_service_url'].'location_api.php';
			
			$param_rw				   =  array();
			$param_rw['rquest']        =  'check_repeated_words';
			$param_rw['parentid']	   =  $compData['parentid'];
			$param_rw['str']  		   =  $compData['companyname'];
			$param_rw['field']  	   =  'companyname';
			$param_rw['ucode']  	   =  $compData['ucode'];
			$param_rw['data_city']     =  $compData['data_city'];
			$param_rw['user_data']     =  json_encode($compData);
			
			$response				=	$this->call_curl_post($url,$param_rw);		
			$response				= 	json_decode($response,true);
			return $response['result'];
		}
	}
	
	function check_jd_employee($usercode,$data_city)
	{
		$configclassobj	=	new configclass();
		$urldetails		=	$configclassobj->get_url(urldecode($this->insert_arr['data_city']));
		$url	=	$urldetails['jdbox_service_url'].'location_api.php';
		
		$param_rw				   =  array();
		$param_rw['rquest']        =  'check_jd_employee';
		$param_rw['usercode']	   =  $usercode;
		$param_rw['data_city']     =  $data_city;
		
		$response				=	$this->call_curl_post($url,$param_rw);		
		$response				= 	json_decode($response,true);
		return $response;
	}
	
	function populate_chain_outlet_main_tbl($compData)
	{
		$configclassobj	=	new configclass();
		$urldetails		=	$configclassobj->get_url(urldecode($this->insert_arr['data_city']));
		$curl_url			=	$urldetails['jdbox_service_url'].'location_api.php';
		
		$param_arr	=  array();
		$param_arr['rquest']        =  'populate_chain_outlet_main_tbl';
		$param_arr['parentid']   	=  $compData['parentid'];
		$param_arr['data_city']     =  $compData['data_city'];
		$param_arr['ucode']     	=  $compData['ucode'];
		$param_arr['uname']     	=  $compData['uname'];
		
		$response				=	$this->call_curl_post($curl_url,$param_arr);	
	}
	
	function check_categories($user_catidlineage,$old_catidlineage,$compData)
	{
		$configclassobj	=	new configclass();
		$urldetails		=	$configclassobj->get_url(urldecode($this->insert_arr['data_city']));
		$curl_url			=	$urldetails['jdbox_service_url'].'location_api.php';
		
		$param_arr	=  array();
		$param_arr['rquest']        		=  'check_removed_category';
		$param_arr['user_catidlineage']   	=  $user_catidlineage;
		$param_arr['old_catidlineage']     	=  $old_catidlineage;
		$param_arr['data_city']     		=  $compData['data_city'];
		$param_arr['user_data']     		=  json_encode($compData);		

		$response						=  $this->call_curl_post($curl_url,$param_arr);	
	}
	
	function check_overwritten_data($compData,$modtype)
	{
		$configclassobj	=	new configclass();
		$urldetails		=	$configclassobj->get_url(urldecode($this->insert_arr['data_city']));
		$curl_url			=	$urldetails['jdbox_service_url'].'location_api.php';
		
		$param_arr	=  array();
		$param_arr['rquest']        =  'overwritten_check';
		$param_arr['parentid']   	=  $compData['parentid'];
		$param_arr['companyname']   =  $compData['companyname'];
		$param_arr['area']   		=  $compData['area'];
		$param_arr['pincode']   	=  $compData['pincode'];
		$param_arr['data_city']     =  $compData['data_city'];
		$param_arr['landline']     	=  $compData['landline'];
		$param_arr['mobile']     	=  $compData['mobile'];
		$param_arr['tollfree']     	=  $compData['tollfree'];
		$param_arr['ucode']     	=  $compData['ucode'];
		$param_arr['user_data']     =  json_encode($compData);
		$response				=	$this->call_curl_post($curl_url,$param_arr);	
	}
	
 	function data_correction_api($compData,$modtype,$extra_details_arr=array(),$compOldData)
	{
		$param = Array();
		$user_data_arr = Array();
		
		$param['parentid']		=	$compData['parentid'];
		$param['mod_type']		=	$modtype;
		$param['userid']		=	$compData['ucode'];
		$param['edited_date']	=	date('Y-m-d H:i:s');
		$param['data_city']		=	$compData['data_city'];
		$param['source']		=	$compData['source'];
		
		if(count($extra_details_arr)>0)
		{
			$user_data_arr	=	$compData;
			$user_data_arr['priority_field']	=	$extra_details_arr['priority_field'];		
			$user_data_arr['mobile_type']		=	$extra_details_arr['mobile_type'];		
			$user_data_arr['jdbox_flag']		=	"1";		
			$param['user_data']					=	json_encode($user_data_arr);
		}
		if(strtoupper($modtype) == "BADWORD")
		{
			$user_data_arr	=	$compData;
			$param['user_data']					=	json_encode($user_data_arr);
		}
		if(count($compOldData)>0)	
		{
			$param['old_data']					=	json_encode($compOldData);
		}
		switch(strtoupper($compData['data_city']))
		{
			case 'MUMBAI' 		:	$url = "http://172.29.0.237:97/";	break;
			case 'DELHI' 	 	:	$url = "http://172.29.8.237:97/";	break;
			case 'KOLKATA' 		:	$url = "http://172.29.16.237:97/";	break;
			case 'BANGALORE' 	:	$url = "http://172.29.26.237:97/";	break;
			case 'CHENNAI' 		:	$url = "http://172.29.32.237:97/";	break;		
			case 'PUNE' 		:	$url = "http://172.29.40.237:97/";	break;
			case 'HYDERABAD' 	:	$url = "http://172.29.50.237:97/";	break;		
			case 'AHMEDABAD' 	:	$url = "http://192.168.35.237:97/";	break;			
			default: 				$url = "http://192.168.17.237:197/";	break;
		}
		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']))
		{				
			$url = "http://nareshbhati.jdsoftware.com/tmegenio/";
		}				
		$curl_url = $url ."api_dc/datacorrection_api.php";				
		
		$ch 		= curl_init();
		curl_setopt($ch, CURLOPT_URL, $curl_url);
		curl_setopt($ch, CURLOPT_POST      ,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS ,$param);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$resmsg = curl_exec($ch);		
		curl_close($ch);
		return $resmsg;
	}
	
	// Function to fetch coulmn names
	function getColumnList($tablename)
	{
		if(!empty($tablename))
		{
			$sql_column_list	=	"SELECT * FROM ".$tablename." LIMIT 1";

			if($this->insert_arr['session_tme']	== 'session_tme' && $this->insert_arr['paid'] == 1 && $tablename != 'd_jds.tbl_company_consolidate')
			{
				$res_column_list	=	parent::execQuery($sql_column_list, $this->dbConIdc);
			}
			else
			{
				//$res_column_list 	= 	parent::execQuery($sql_column_list, $this->dbConIro_slave);
				$res_column_list 	= 	parent::execQuery($sql_column_list, $this->dbConIro);
			}
			
			$field = mysql_num_fields($res_column_list);
			for ($i = 0; $i < $field; $i++) {
				$coulmn_names[] = mysql_field_name($res_column_list, $i);
			}
			return $coulmn_names;
		}
	}
	// Function to generate parentid
	function generateParentid($data_city,$source,$remote_flag){
		
		for($i = 0; $i < 3; $i++){	//Random String Generator
			 $aChars = array('A', 'B', 'C', 'D', 'E','F','G','H', 'I', 'J', 'K', 'L','M','N','P', 'Q', 'R', 'S', 'T','U','V','W', 'X', 'Y', 'Z');
			 $iTotal = count($aChars) - 1;
			 $iIndex = rand(0, $iTotal);
			 $sCode .= $aChars[$iIndex];
			 $sCode .= chr(rand(49, 57));
		}
		$stdcode = "XXXX";
		if($data_city){
			$sql = "SELECT stdcode FROM city_master WHERE ct_name = '".$data_city."' and stdcode!='' LIMIT 1";
			$res = parent::execQuery($sql, $this->dbConDjds);
			if($res && mysql_num_rows($res)){
				$row = mysql_fetch_assoc($res);
				$stdcode = $row['stdcode'];
			}
		}
		$stdcode = substr($stdcode,1);
		$stdcode = str_pad($stdcode,4,"X",STR_PAD_LEFT);

		if($stdcode=="XXXX"){
			echo '<h1>STD code for city '.$data_city.' found to be blank.</h1>';
			echo "<h2>Please contact to software team immediately</h2>";
			die;
		}

		$stdcode_destination_component = $stdcode; // 4 digit
    	$time_component = substr(date("YmdHis",time()),2); // 12 digit
		$random_number_component = substr($sCode,2); // 4 digit

		$cCode = $stdcode_destination_component.".".$stdcode_destination_component.".".$time_component.".".$random_number_component; //24 + 3 = 27 digits
		/*Genrating Sphinx id*/
		
		if($cCode){
			if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST'])){
				$cs_app_url = $this->fetchCSLiveUrl($data_city);
			}else{
				$cs_app_url = "http://imteyazraja.jdsoftware.com/csgenio/";
			}
			$PCode="P".$cCode;
			$url=$cs_app_url."api_services/api_idgeneration.php?source=".$source."&rquest=idgenerator&module=".$source."&datacity=".urlencode($data_city)."&parentid=".$PCode."&rflag=".$remote_flag;
			$strNewsphinxId = json_decode($this->general_curl_call($url),true);
		}
		/*--------------------*/
		return ('P'.$cCode);
	}
	//function to generate docid
	function getDocidForParentid($parentid)
	{
		$validationCode = $this->insert_arr['validationcode'];
				
		if($parentid)
		{
			$docid = 0;
			$Qry = "SELECT docid FROM db_iro.tbl_id_generator WHERE parentid='".$parentid."'";
			$Res_doc = parent::execQuery($Qry, $this->dbConIro);
			$row_doc = mysql_fetch_assoc($Res_doc);
			if($row_doc['docid'])
			{
				$docid = $row_doc['docid'];
			}
			else
			{
				//docid not found so, inserting into log table
				$subject ='Docid Not Found';
				$Qry = "INSERT INTO tbl_docid_notfound_log(parentid, source,updatedby,updatedOn,subject, validationCode) VALUES('".$parentid."', '".$this->insert_arr['source']."', '".urlencode($this->insert_arr['ucode'])."','".date("Y-m-d H:i:s")."' ,'".$subject."' , '".$validationCode."')";
				$Res_Qry = parent::execQuery($Qry, $this->dbConIro);
				
				if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
				{
					//mail("saritha.pc@justdial.com","docid Not Found for the parentid='".$parentid."' and from (LIVE)the Module=".$this->insert_arr['source'],"DOCID NOT FOUND");
				}
			}		
		}
		
		return $docid;
	}
	
	function resetCatFlag()
	{
		if(strtolower($this->insert_arr['source'])== 'cs')
		{
			$qryReset = "UPDATE d_jds.tbl_temp_intermediate SET cat_reset_flag = 0 WHERE parentid='".$this->parentid."'";
			$resReset = parent::execQuery($qryReset, $this->dbConDjds);
		}
		else if(strtolower($this->insert_arr['source'])== 'me')
		{			
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= 'me';
			$mongo_data = array();
			
			$intermd_tbl = "tbl_temp_intermediate";
			$intermd_upt = array();
			
			$intermd_upt['cat_reset_flag'] 			= "0";
			$mongo_data[$intermd_tbl]['updatedata'] = $intermd_upt;
			
			$mongo_inputs['table_data'] = $mongo_data;
			$resReset 					= $this->mongo_obj->updateData($mongo_inputs);
		
			//$qryReset = "UPDATE tbl_temp_intermediate SET cat_reset_flag = 0 WHERE parentid='".$this->parentid."'";
			//$resReset = parent::execQuery($qryReset, $this->dbConIdc);			
		}
		else if(strtolower($this->insert_arr['source'])== 'tme')
		{
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= 'tme';
			$mongo_data = array();
			
			$intermd_tbl = "tbl_temp_intermediate";
			$intermd_upt = array();
			
			$intermd_upt['cat_reset_flag'] 			= "0";
			$mongo_data[$intermd_tbl]['updatedata'] = $intermd_upt;
			
			$mongo_inputs['table_data'] = $mongo_data;
			$resReset 					= $this->mongo_obj->updateData($mongo_inputs);
			
			//$qryReset = "UPDATE tbl_temp_intermediate SET cat_reset_flag = 0 WHERE parentid='".$this->parentid."'";
			//$qryReset = $qryReset."/* TMEMONGOQRY */";
			//$resReset = parent::execQuery($qryReset, $this->dbConTmeJds);
		}
		
		
	}

	function updateCCRDealcloseFlag()
	{
		$sqlFetchUniqueID = "SELECT ccr_uniqueid FROM tbl_temp_flow_status WHERE parentid='".$this->parentid."' AND ccr_uniqueid > 0";
		if(strtolower($this->insert_arr['source'])== 'cs')
		{
			$resFetchUniqueID = parent::execQuery($sqlFetchUniqueID, $this->dbConDjds);
		}
		else if(strtolower($this->insert_arr['source'])== 'tme')
		{
			$resFetchUniqueID = parent::execQuery($sqlFetchUniqueID, $this->dbConTmeJds);
		}
		else if(strtolower($this->insert_arr['source'])== 'me')
		{
			$resFetchUniqueID = parent::execQuery($sqlFetchUniqueID, $this->dbConIdc);
		}
		if($resFetchUniqueID && mysql_num_rows($resFetchUniqueID)>0)
		{
			$row_data 	= mysql_fetch_assoc($resFetchUniqueID);
			$uniqueid	= trim($row_data['ccr_uniqueid']);
			$sqlFetchRecords = "SELECT uniqueid FROM tbl_ccr_multiparent_contract WHERE parentid='".$this->parentid."' AND uniqueid= '".$uniqueid."'";
			$resFetchRecords = parent::execQuery($sqlFetchRecords, $this->dbConDjds);
			if($resFetchRecords && mysql_num_rows($resFetchRecords)>0)
			{
				$sqlUpdtDealcloseFlag = "UPDATE  tbl_ccr_multiparent_contract SET dealclose_flag='1' WHERE parentid='".$this->parentid."' AND uniqueid= '".$uniqueid."'";
				$resUpdtDealcloseFlag = parent::execQuery($sqlUpdtDealcloseFlag, $this->dbConDjds);
			}
		}

	}



	// Function to fetch sphinx_id
	function getContractSphinxId($parentid) 
	{		
		$sphinx_id = 0;
		$idgenerator_sphinxid_query		= " SELECT sphinx_id FROM tbl_id_generator WHERE parentid = '" . $parentid . "' ";
		$sphinxid_res               	= parent::execQuery($idgenerator_sphinxid_query, $this->dbConIro);
		
		if ($sphinxid_res && parent::numRows($sphinxid_res)) {
			$sphinxid_values      		 	= parent::fetchData($sphinxid_res);
			$sphinx_id            		 	= intval($sphinxid_values['sphinx_id']);
		}
		if (intval($sphinx_id) == 0) {
			$this->insert_tbl_absent_parentid_table_details($parentid,'tbl_id_generator',$failed_sql);
			echo '<h3>Sphinx Id not found for this parentid : ' . $parentid . ' </h3>';
			exit();
		}
		return $sphinx_id;
	}
	
	function insert_tbl_absent_parentid_table_details($parentid,$absenttablename,$failed_sql)
	{	
		if($failed_sql!=''){
			$query_new		= trim(addslashes($failed_sql));
			$query_new		= preg_replace("/[\r\n]{2,}/", " ", $query_new);
			$query_new		= preg_replace("/[\t]{2,}/", " ", $query_new);
			
			$con = mysql_connect($this->dbConIro[0], $this->dbConIro[1], $this->dbConIro[2]);								
			$query_new = mysql_real_escape_string($query_new,$con);
		}
		
		$absenttable_query		= " INSERT INTO tbl_absent_parentid_table_details set parentid = '" . $parentid . "' , tablename='".$absenttablename."',done_flag=0,failed_query='".$query_new."' ";
		parent::execQuery($absenttable_query, $this->dbConIro);
		echo '<h3> Parentid not present on table ' . $absenttablename . ' </h3>';
		#exit();
	}
	
	// Function to fetch hotcategory
	function getHotCategory($catidlist)
	{
		if(!empty($catidlist))
		{
			$catid_list				=	str_replace("/","",$catidlist);
			$catid_list				= 	trim($catid_list, ",'");
			//$sql_hot_catid 			= "SELECT catid FROM tbl_categorymaster_generalinfo WHERE catid IN('".$catid_list."') Order BY callcount DESC LIMIT 1";
						
			//$res_national_catids	=	parent::execQuery($sql_national_catids, $this->dbConDjds_slave);
			//$res_hot_catid			=	parent::execQuery($sql_hot_catid, $this->dbConDjds);
			$cat_params = array();
			$cat_params['page'] 		= 'insertLiveClass';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'catid';
			$cat_params['limit']		= '1';
			$cat_params['orderby']		= 'callcount DESC';

			$where_arr  	=	array();
			if($catid_list!=''){
				$where_arr['catid']		= $catid_list;
				$cat_params['where']	= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}
			
			//$row_hot_catid			= mysql_fetch_assoc($res_hot_catid);
			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0){
				$hot_category =	$cat_res_arr['results']['0']['catid'];
			}
			//$hot_category			= $row_hot_catid['catid'];
			return $hot_category;
		}
	}
	
	// Function to fetch national catid
	function getNationalCatlineage($catid)
	{
		$catids_array = array();
		$catids_array = explode("/,/",trim($catid,"/"));
		$catids_array = $this->get_valid_categories($catids_array);
		if(count($catids_array))
		{
			$catid_list = implode(",",$catids_array);
			//$sql_national_catids 	= "SELECT catid,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catid_list."')";
			//$res_national_catids	=	parent::execQuery($sql_national_catids, $this->dbConDjds);
			$cat_params = array();
			$cat_params['page'] 		= 'insertLiveClass';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'catid,national_catid';
			
			$where_arr  	=	array();
			if($catid_list!=''){
				$where_arr['catid']		= $catid_list;
				$cat_params['where']	= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
			{
				foreach($cat_res_arr['results'] as $key => $row_national_catids)
				{
					$arr_national_catids[] = $row_national_catids['national_catid']; 
				}
			}
			
			$national_catids = '';
			
			if (is_array($arr_national_catids) && count($arr_national_catids))
			{			
				$national_catids = implode('/,/', $arr_national_catids);
			
				if (trim($national_catids) != '')
				{
					$national_catids = '/'.$national_catids.'/';
				}
			}

			return $national_catids;
		}
	}
	
	// Function to fetch parent categories
	function getParentCategories($catidlist)
	{	
		$parent_categories_arr = array();
		$catidarray		= null;
		
		$catidlistarr 	= explode(",",$catidlist);	
		$catidlistarr 	= array_unique($catidlistarr);
		$catidlistarr 	= array_filter($catidlistarr);
		if(count($catidlistarr)>0)
		{
			$catidliststr 	= implode(",",$catidlistarr);

			//$sql = "SELECT group_concat( DISTINCT associate_national_catid) as associate_national_catid FROM tbl_categorymaster_generalinfo where catid in (".$catidliststr.") AND catid > 0 AND category_name != '' ";
			//$res = parent::execQuery($sql, $this->dbConDjds_slave);
			//$res = parent::execQuery($sql, $this->dbConDjds);			
			$cat_params = array();
			$cat_params['page'] 		= 'insertLiveClass';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'associate_national_catid';			

			$where_arr  	=	array();
			if($catidliststr!=''){
				$where_arr['catid']		= $catidliststr;
				$cat_params['where']	= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}
			
			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
			{
				//$row = mysql_fetch_assoc($res);
				$associate_national_catid_arr = array();
				foreach ($cat_res_arr['results'] as $key => $cat_arr) {
					$associate_national_catid =  $cat_arr['associate_national_catid'];
					if($associate_national_catid!=''){
						$associate_national_catid_arr[]= $associate_national_catid;
					}
				}

				if(count($associate_national_catid_arr)>0)
				{
					
					//$associate_national_catid_arr = explode(',',$row['associate_national_catid']);			
					
					$associate_national_catid_arr = array_unique($associate_national_catid_arr);
					$associate_national_catid_arr = array_filter($associate_national_catid_arr);
					$associate_national_catid_str = implode(",",$associate_national_catid_arr);
					
					// fetching the catid from national_catid and removing original catid
					//$sql = "SELECT group_concat( DISTINCT catid) as parent_categories FROM tbl_categorymaster_generalinfo where national_catid IN (".$associate_national_catid_str.") and catid NOT IN (".$catidliststr.") AND catid > 0 AND category_name != '' ";
				
					//$res = parent::execQuery($sql, $this->dbConDjds_slave);
					//$res = parent::execQuery($sql, $this->dbConDjds);
					$cat_params = array();
					$cat_params['page'] 		= 'insertLiveClass';
					$cat_params['data_city'] 	= $this->data_city;
					$cat_params['return']		= 'catid';			

					$where_arr  	=	array();
					if($associate_national_catid_str!=''){
						$where_arr['national_catid']		= $associate_national_catid_str;
						$where_arr['catid']					= "!".$catidliststr;
						$cat_params['where']				= json_encode($where_arr);
						$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
					}
					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}
					
					if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
					{
						//$row = mysql_fetch_assoc($res);
						$parent_categories_arr = array();
						foreach ($cat_res_arr['results'] as $key => $cat_arr) {
							if($cat_arr['catid']!=''){
								$parent_categories_arr[] = $cat_arr['catid'];
							}
						}
						if(count($parent_categories_arr)>0)
						{
							//$parent_categories_arr = explode(',',$row['parent_categories']);
							
							$parent_categories_arr = array_unique($parent_categories_arr);
							$parent_categories_arr = array_filter($parent_categories_arr);					
						}
					}			
				}
			}
		}
		return $parent_categories_arr;
	}
	
	// Function for premium category handling
	function auditPremiumCategoryNonpaid($parentId, $temp_catid_arr, $arrayCatLinSrch, $module, $ucode, $city, $uname, $companyname='', $paid = 0)
	{
		
		$orignal_catid_arr	= array();
		$pre_cat_arr		= array();
		$non_pre_cat_arr	= array();
		$extra_cat_id_arr 	= array();
		$compname 			= '';
		$dbname 			= '';
		
		
		$compname			= $companyname;
		
		if(strtolower($module) == 'tme mask_auto process')
		{
			$new_added_catid_arr	= $temp_catid_arr;
		}
		else if(strtolower($module) == 'relevant ctg auto update')
		{
			$new_added_catid_arr	= Array();
		}
		else
		{
			// To get Original set of catids from  tbl_companymaster_extradetails(LIVE)  -- Starts here
			// For companymasterclass
			if($this->insert_arr['session_tme']	== 'session_tme' && $this->insert_arr['paid'] == 1)
			{
				$extradetails_table = 'tbl_companymaster_extradetails';
			}
			else
			{
				$extradetails_table = $this->getFinalTableName('tbl_companymaster_extradetails',$this->tableno);
			}
			// End
			
			$sql_extra_cat_id 		= "SELECT catidlineage FROM ".$extradetails_table." WHERE parentid = '".$parentId."'";
			//$res_extra_cat_id 		= parent::execQuery($sql_extra_cat_id, $this->dbConIro_slave);
			$res_extra_cat_id 		= parent::execQuery($sql_extra_cat_id, $this->dbConIro);
			
			if($res_extra_cat_id && mysql_num_rows($res_extra_cat_id) > 0)
			{
				$row_extra_cat_id 	= mysql_fetch_assoc($res_extra_cat_id);
				$extra_cat_id 		= str_replace('/','',$row_extra_cat_id['catidlineage']);
			}
				
			$extra_cat_id_arr 		= explode(',',$extra_cat_id);    
			// To get Original set of catid from  tbl_companymaster_extradetails  -- Ends here
			
			// To get new added categoires    ----- Starts here   	
			$new_added_catid_arr 	= array_diff($temp_catid_arr,$extra_cat_id_arr);  // contains both premium or normal categories
			// To get new added categoires    -----  Ends here 
			if($this->insert_arr['flow_module']=='DE'){
				$catLinge_catLingSrch_array = array(); 
				$catLinge_catLingSrch_array['catLineage'] 		= $temp_catid_arr;
				$catLinge_catLingSrch_array['catLineageSrch'] 	= $arrayCatLinSrch;	
				return $catLinge_catLingSrch_array;
			}
		}	
		if(COUNT($new_added_catid_arr) > 0)
		{
			$all_catids 	= implode(",",$new_added_catid_arr);
			//$sql_pre_cat 	= "SELECT distinct(catid) FROM tbl_categorymaster_generalinfo where catid IN ('".$all_catids."') AND premium_flag = 1";
			//$res_pre_cat 	= parent::execQuery($sql_pre_cat, $this->dbConDjds_slave);
			//$res_pre_cat 	= parent::execQuery($sql_pre_cat, $this->dbConDjds);
			$cat_params = array();
			$cat_params['page'] 		= 'insertLiveClass';
			$cat_params['skip_log'] 	= '1';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'catid';			

			$where_arr  	=	array();
			
			//De-Duplication Cron':'De-Duplication Updation
			
			if( $all_catids!='' &&  !in_array(trim($this->insert_arr['ucode']), array('De-Duplication Cron','De-Duplication Updation')) ){
				$where_arr['catid']				= $all_catids;
				$where_arr['premium_flag']		= '1';
				$cat_params['where']			= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}
			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			{
				foreach($cat_res_arr['results'] as $key =>$row_pre_cat)
				{
					$pre_cat_arr[] = $row_pre_cat['catid']; 
				}
			}
			$legal_flag_arr = array();
			//$sql_legal_flag =	"SELECT catid,bfc_bifurcation_flag FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$all_catids."') AND premium_flag=1";
			//$res_legal_flag	=	parent::execQuery($sql_legal_flag,$this->dbConDjds);
			$cat_params = array();
			$cat_params['page'] 		= 'insertLiveClass';
			$cat_params['skip_log'] 	= '1';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'catid,bfc_bifurcation_flag';			

			$where_arr  	=	array();
			if($all_catids!=''){
				$where_arr['catid']				= $all_catids;
				$where_arr['premium_flag']		= '1';
				$cat_params['where']			= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0){
				foreach($cat_res_arr['results']  as $key =>$row_legal_flag)
				{
					$catid 					= $row_legal_flag['catid']; 
					$legal_flag_arr[$catid] = $row_legal_flag['bfc_bifurcation_flag']; 
				}	
			}
			
			if(count($pre_cat_arr) > 0)
			{
				// For paid contracts insert categories along with premium cat, for nonpaid exclude premium cat
				$non_pre_cat_arr = (($paid == 0) ? array_diff($temp_catid_arr,$pre_cat_arr) : $temp_catid_arr); 
				$non_pre_cat_arr = array_filter($non_pre_cat_arr);
				$non_pre_cat_arr = array_unique($non_pre_cat_arr);
				$catLinge_catLingSrch_array['catLineage'] = $non_pre_cat_arr;
			}
			else
			{
				$catLinge_catLingSrch_array['catLineage'] = $temp_catid_arr;
			}
			
			if(count($pre_cat_arr)>0)
			{
				foreach($pre_cat_arr as $key=>$value)
				{	if($legal_flag_arr[$value]==1){
						$legal_condn = " , legal_flag = 1 ";
					}
					$remote_flag = 0;
					$city_arr = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');
					if(!in_array(strtolower($city), $city_arr)){
						$remote_flag = 1;
					}
					$de_remotezone	= 0;
					$de_tourist		= 0;
					if($remote_flag ==1){						
						$sql_get_zones  = "SELECT mapped_cityname,de_remotezone,de_tourist FROM tbl_city_master WHERE ct_name ='".$city."' ";
						$res_get_zones = parent::execQuery($sql_get_zones, $this->dbConDjds);
						if(parent::numRows($res_get_zones)>0){
							$row_get_zones 	=	parent::fetchData($res_get_zones);
							$mapped_cityname 	= trim($row_get_zones['mapped_cityname']);
							$de_remotezone 		= trim($row_get_zones['de_remotezone']);
							$de_tourist 		= trim($row_get_zones['de_tourist']);							
						}
					}
					
					$insert_into_premium_cat = "INSERT INTO tbl_premium_categories_audit SET
									companyname		= '".addslashes($compname)."',
									parentid		= '".$parentId."',
									catids			= '".$value."',
									username		= '".addslashes($uname)."',
									Userid			= '".$ucode."',
									Dept			= '".$module."',
									de_remotezone 	= '".$de_remotezone."',
									de_tourist 		= '".$de_tourist."',
									mapped_cityname = '".addslashes($mapped_cityname)."',
									City			= '".addslashes($city)."',
									updatetime		= '".date('Y-m-d H:i:s')."',
									paid_status		= '".$paid."',
									paid_category	= '".$paid."',
									approval_status = '0'
									".$legal_condn."
									ON DUPLICATE KEY UPDATE
									
									companyname		= '".addslashes($compname)."',
									username		= '".addslashes($uname)."',
									Userid			= '".$ucode."',
									Dept			= '".$module."',
									de_remotezone 	= '".$de_remotezone."',
									de_tourist 		= '".$de_tourist."',
									mapped_cityname = '".addslashes($mapped_cityname)."',
									City			= '".$city."',
									updatetime		= '".date('Y-m-d H:i:s')."',
									paid_status		= '".$paid."',
									paid_category	= '".$paid."',
									approval_status = '0'
									".$legal_condn." "; 
					$res_premium_cat = parent::execQuery($insert_into_premium_cat, $this->dbConDjds);

					$insert_into_premium_cat_log = "INSERT INTO tbl_premium_categories_audit_log SET
												companyname		= '".addslashes($compname)."',
												parentid		= '".$parentId."',
												catids			= '".$value."',
												username		= '".addslashes($uname)."',
												Userid			= '".$ucode."',
												Dept			= '".$module."',
												City			= '".$city."',
												updatetime		= '".date('Y-m-d H:i:s')."',
												paid_status		= '".$paid."',
												approval_status = '0'
												".$legal_condn." ";
										
					$res_premium_cat_log = parent::execQuery($insert_into_premium_cat_log, $this->dbConDjds);
				}
				
				if($paid == 0)
				{
					foreach($arrayCatLinSrch as $key => $value)
					{
						if(in_array($value,$pre_cat_arr))
						{
							unset($arrayCatLinSrch[$key]);
						}
					}
				}
			}

			$catLinge_catLingSrch_array['catLineageSrch'] 	= $arrayCatLinSrch;
		}
		else
		{
			$catLinge_catLingSrch_array['catLineage'] 		= $temp_catid_arr;
			$catLinge_catLingSrch_array['catLineageSrch'] 	= $arrayCatLinSrch;		
		}
		return $catLinge_catLingSrch_array;
	}
	
	// Function for brandname audit module	
	function updateBrandName($compData,$curl_url_brand)
	{
		if(!empty($compData))
		{
			$new_company	=	$compData['companyname'];
			$old_company	=	$compData['companyname_old'];
			$parentid		=	$compData['parentid'];
			$ucode			=	$compData['ucode'];
			$uname			=	$compData['uname'];
			$ct_name		=	$compData['data_city'];
			$dept			=	$compData['source'];
			$paid			=	$compData['paid'];
			
			if($paid == '0')
			{
				$brand_params_arr = array();
				$brand_params_arr['parentid'] 		= $parentid;
				$brand_params_arr['new_company'] 	= $new_company;
				$brand_params_arr['old_company'] 	= $old_company;
				$brand_params_arr['ucode'] 			= $ucode;
				$brand_params_arr['uname'] 			= $uname;
				$brand_params_arr['ct_name'] 		= $ct_name;
				$brand_params_arr['dept'] 			= $dept;
				$brand_params_arr['data_city'] 		= $ct_name;
				$brand_params_arr['paid'] 			= $paid;
				$this->call_curl_post($curl_url_brand,json_encode($brand_params_arr));
			}
		}
	}
	
	function dataInString($seperator,$numbarray)
	{
		if(count($numbarray) > 0)
		{
			$numbstring=implode($seperator,$numbarray);
			return $numbstring;
		}
		else
		{
			return false;
		}
	}

	function applyIgnore($str)
	{
		$ig_strt = array('/^\bthe\b/i','/^\bdr\.\s/i','/^\bdr\b/i','/^\bprof\.\s/i','/^\bprof\b/i','/^\band\b/i','/^\bbe\b/i');
		$ig_last = array('/\bpvt\.\s/i','/\bltd\.\s/i','/\bpvt\b/i','/\bltd\b/i','/\bprivate\b/i','/\blimited\b/i');
		$s = trim($str);
		$s = preg_replace($ig_strt,'',$s);
		$s = preg_replace($ig_last,'',trim($s));
		$s = trim(preg_replace('/\s\s+/',' ',$s));
		return (strlen($s)<=1) ? $str : $s;
	}
	
	// Function to insert into tbl_companymaster_search
	function insertCompanymasterSearch($compData)
	{
		if(!empty($compData))
		{
			
			$address 			= 	$compData['full_address'].",".addslashes($compData['city']).",".addslashes($compData['state']);
							
			$date				= date('Y-m-d H:i:s');
			
			$singular		 	= $this->applyIgnore($compData['companyname']);
			$singularWOspace 	= str_replace(" ","",$singular);
			
			if($compData['freeze'] == 1 || $compData['mask'] == 1)
			{
				$display_flag = 0;
			}
			else
			{
				$display_flag = 1;
			}
			// For companymasterclass
			if($this->insert_arr['session_tme']	== 'session_tme' && $this->insert_arr['paid'] == 1)
			{
				$search_table 		= 'tbl_companymaster_search';
			}
			else
			{
				$search_table 		= $this->getFinalTableName('tbl_companymaster_search',$this->tableno);				
			}
			// End
			
			$search_table_arr 	= array();
			$search_table_arr['nationalid'] 						= '';
			$search_table_arr['sphinx_id'] 							= $compData['sphinx_id'];
			$search_table_arr['regionid'] 							= $compData['regionid'];
			$search_table_arr['docid'] 								= $compData['docid'];
			$search_table_arr['companyname'] 						= $compData['companyname'];
			$search_table_arr['parentid'] 							= $compData['parentid'];
			$search_table_arr['companyname_search'] 				= $compData['companyname'];
			$search_table_arr['companyname_search_area'] 			= $compData['companyname_search_area'];
			$search_table_arr['companyname_search_stem'] 			= $compData['companyname_search_stem'];
			$search_table_arr['companyname_search_WS'] 				= $compData['companyname_search_WS'];
			$search_table_arr['companyname_search_stem_WS'] 		= $compData['companyname_search_stem_WS'];
			$search_table_arr['latitude'] 							= $compData['latitude'];
			$search_table_arr['longitude'] 							= $compData['longitude'];
			$search_table_arr['state'] 								= $compData['state'];
			$search_table_arr['city'] 								= $compData['city'];
			$search_table_arr['pincode'] 							= $compData['pincode'];
			$search_table_arr['phone_search'] 						= $compData['phone_search'];
			$search_table_arr['address'] 							= $compData['address'];
			$search_table_arr['contact_person'] 					= $compData['contact_person'];
			$search_table_arr['email'] 								= $compData['email_display'];
			$search_table_arr['website'] 							= $compData['website'];
			$search_table_arr['catidlineage_search'] 				= $compData['catidlineage_search'];
			$search_table_arr['national_catidlineage_search'] 		= $compData['national_catidlineage_search'];
			$search_table_arr['length'] 							= strlen($compData['companyname']);
			$search_table_arr['display_flag'] 						= $display_flag;
			$search_table_arr['prompt_flag'] 						= $compData['prompt_flag'];
			$search_table_arr['paid'] 								= $compData['paid'];
			$search_table_arr['updatedBy'] 							= $compData['updatedBy'];
			$search_table_arr['updatedOn'] 							= $date;
			$search_table_arr['data_city'] 							= $compData['data_city'];
			$search_table_arr['compname_search_singular'] 			= $singular;
			$search_table_arr['compname_search_singular_wo_space'] 	= $singularWOspace;
			$search_table_arr['area'] 								= $compData['area'];
			$search_table_arr['compname_search_processed_ignore'] 	= $compData['compname_search_processed_ignore'];
			$search_table_arr['compname_search_processed_ignore_wo_space'] 		= $compData['compname_search_processed_ignore_wo_space'];
			$search_table_arr['area_processed'] 								= $compData['area_processed'];
			$search_table_arr['compname_area_search_processed_ignore_wo_space']	= $compData['compname_area_search_processed_ignore_wo_space'];
			$search_table_arr['compname_area_search_processed_ignore'] 			= $compData['compname_area_search_processed_ignore'];
			$search_table_arr['address_search'] 								= $compData['address_search'];
			
			/******************* Insert into tbl_companymaster_search****************************/
			$insert_comp_srch = "INSERT INTO ".$search_table." SET 
								nationalid 					= '',
								sphinx_id 					= '".$compData['sphinx_id']."',
								regionid 					= '".$compData['regionid']."',
								docid						= '".$compData['docid']."',
								companyname					= '".addslashes($compData['companyname'])."',
								parentid					= '".$compData['parentid']."',
								companyname_search			= '".addslashes($compData['companyname'])."',
								companyname_search_area		= '".addslashes($compData['companyname_search_area'])."',
								companyname_search_stem		= '".addslashes($compData['companyname_search_stem'])."',
								companyname_search_WS		= '".addslashes($compData['companyname_search_WS'])."',
								companyname_search_stem_WS	= '".addslashes($compData['companyname_search_stem_WS'])."',
								latitude					= '".$compData['latitude']."',
								longitude					= '".$compData['longitude']."',
								state						= '".addslashes($compData['state'])."',
								city						= '".addslashes($compData['city'])."',
								pincode						= '".$compData['pincode']."',
								phone_search				= '".$compData['phone_search']."',
								address						= '".addslashes($address)."',
								contact_person				= '".addslashes($compData['contact_person'])."',
								email						= '".addslashes($compData['email_display'])."',
								website						= '".addslashes($compData['website'])."',
								catidlineage_search			= '".$compData['catidlineage_search']."',
								national_catidlineage_search= '".$compData['national_catidlineage_search']."',
								length						= '".strlen($compData['companyname'])."',
								display_flag				= '".$display_flag."',
								prompt_flag					= '".$compData['prompt_flag']."',
								paid						= '".$compData['paid']."',
								updatedBy					= '".$compData['updatedBy']."',
								updatedOn					= '".$date."',
								data_city					= '".addslashes($compData['data_city'])."',
								compname_search_singular	= '".addslashes($singular)."',
								compname_search_singular_wo_space = '".addslashes($singularWOspace)."',
								area							  			= '".addslashes($compData['area'])."',
								compname_search_processed_ignore  			= '".addslashes($compData['compname_search_processed_ignore'])."',
								compname_search_processed_ignore_wo_space 	= '".addslashes($compData['compname_search_processed_ignore_wo_space'])."',
								area_processed 								= '".addslashes($compData['area_processed'])."',
								compname_area_search_processed_ignore_wo_space = '".addslashes($compData['compname_area_search_processed_ignore_wo_space'])."',
								compname_area_search_processed_ignore		= '".addslashes($compData['compname_area_search_processed_ignore'])."',
								address_search								= '".addslashes($compData['address_search'])."'

								ON DUPLICATE KEY UPDATE

								docid						= '".$compData['docid']."',
								companyname					= '".addslashes($compData['companyname'])."',
								companyname_search			= '".addslashes($compData['companyname'])."',
								companyname_search_area		= '".addslashes($compData['companyname_search_area'])."',
								companyname_search_stem		= '".addslashes($compData['compname_stem'])."',
								companyname_search_WS		= '".addslashes($compData['compname_search_stem'])."',
								companyname_search_stem_WS	= '".addslashes($compData['compname_search_stem_ws'])."',
								latitude					= '".$compData['latitude']."',
								longitude					= '".$compData['longitude']."',
								state						= '".addslashes($compData['state'])."',
								city						= '".addslashes($compData['city'])."',
								pincode						= '".$compData['pincode']."',
								phone_search				= '".$compData['phone_search']."',
								address						= '".addslashes($address)."',
								contact_person				= '".addslashes($compData['contact_person'])."',
								email						= '".addslashes($compData['email_display'])."',
								website						= '".addslashes($compData['website'])."',
								catidlineage_search			= '".$compData['catidlineage_search']."',
								national_catidlineage_search= '".$compData['national_catidlineage_search']."',
								length						= '".strlen($compData['companyname'])."',
								display_flag				= '".$display_flag."',
								prompt_flag					= '".$compData['prompt_flag']."',
								paid						= '".$compData['paid']."',
								updatedBy					= '".$compData['updatedBy']."',
								updatedOn					= '".$date."',
								data_city					= '".addslashes($compData['data_city'])."',
								compname_search_singular	= '".addslashes($singular)."',
								compname_search_singular_wo_space = '".addslashes($singularWOspace)."',
								area							  			= '".addslashes($compData['area'])."',
								compname_search_processed_ignore  			= '".addslashes($compData['compname_search_processed_ignore'])."',
								compname_search_processed_ignore_wo_space 	= '".addslashes($compData['compname_search_processed_ignore_wo_space'])."',
								area_processed 								= '".addslashes($compData['area_processed'])."',
								compname_area_search_processed_ignore_wo_space = '".addslashes($compData['compname_area_search_processed_ignore_wo_space'])."',
								compname_area_search_processed_ignore		= '".addslashes($compData['compname_area_search_processed_ignore'])."',
								address_search								= '".addslashes($compData['address_search'])."'";
								
			if($this->live_comp == 1){
				//$res_comp_srch	=	parent::execQuery($insert_comp_srch, $this->dbConIro);				
			}
			else{				
				$res_comp_srch	=	parent::execQuery($insert_comp_srch, $this->dbConIro);				
			}
			/********************Insert into tbl_companymaster_search END ******************************/
			$comp_update_arr = array();			 
			
			$comp_update_arr['usrid']		=$this->insert_arr['ucode'];
			$comp_update_arr['usrnm'] 		=$this->insert_arr['uname'];
			$comp_update_arr['data_city'] 	=$this->data_city;
			$comp_update_arr['parentid'] 	=$this->parentid;
			$comp_update_arr['rsrc'] 	 	=$this->insert_arr['source'];						
			
			$update_data['srch_det_id'] 	= $search_table_arr;
			$comp_update_arr['update_data'] = json_encode($update_data);
			
			$comp_update_arr['page'] 		= 'insertLiveClass_search';
			$comp_update_arr['action'] 		= 'updatedata';
									
			$comp_upd_res 	= '';
			$comp_upd_arr 	= array();	
			if($this->live_comp == 1){
				$comp_upd_res	=	$this->companyClass_obj->getCompanyInfo($comp_update_arr);
			}
						
			if($comp_upd_res!=''){
				$comp_upd_arr = 	json_decode($comp_upd_res,TRUE);
			}		
		}
	}
	
	// Function to insert into tbl_contract_tmeDetails
	function insertContractTmeDetails($compData)
	{
		if(!empty($compData))
		{
			$sql_insert = "INSERT into tbl_contract_tmeDetails SET 
						contractId 		= '".$compData['parentid']."',
						parentid 		= '".$compData['parentid']."',
						employeeCode 	= '".$compData['employeeCode']."',
						iroCode 		= '".$compData['iroCode']."',
						meCode 			= '".$compData['meCode']."',
						mCode 			= '".$compData['mCode']."',
						tmeCode 		= '".$compData['tmeCode']."',
						tmeName 		= '".addslashes($compData['tmeName'])."'
							ON DUPLICATE KEY UPDATE
						parentid 		= '".$compData['parentid']."',
						employeeCode 	= '".$compData['employeeCode']."',
						iroCode 		= '".$compData['iroCode']."',
						meCode 			= '".$compData['meCode']."',
						mCode 			= '".$compData['mCode']."',
						tmeCode 		= '".$compData['tmeCode']."',
						tmeName 		= '".addslashes($compData['tmeName'])."'";
			
			if($compData['session_tme']	== 'session_tme' && $compData['paid'] == 1)
			{
				$res_insert	=	parent::execQuery($sql_insert, $this->dbConIdc);
			}
			else
			{			
				$res_insert =	parent::execQuery($sql_insert, $this->dbConDjds);
			}
		}
	}
	
	// Function to insert into tbl_tmesearch
	function insertTmeSearch($compData)
	{
		if(!empty($compData))
		{
			/* checking if its already exit - start*/
			$sql_tmesearch_check = "SELECT mainsource, subsource FROM tbl_tmesearch WHERE parentid = '".$compData['parentid']."'";
			//$res_tmesearch_check = parent::execQuery($sql_tmesearch_check, $this->dbConDjds_slave);
			$res_tmesearch_check = parent::execQuery($sql_tmesearch_check, $this->dbConDjds);
			if($res_tmesearch_check && mysql_num_rows($res_tmesearch_check))
			{
				$row_tmesearch_check = mysql_fetch_assoc($res_tmesearch_check);
			}

			$mainsource = trim($row_tmesearch_check['mainsource'].','.$compData['mainsource'],',');
			$subsource  = trim($row_tmesearch_check['subsource'].','.$compData['subsource'],',');
			
			$date				= ((isset($compData['datasource_date'])) && (strtolower($compData['original_creator']) == 'iro-appointment')) ? $compData['datasource_date'] : date('Y-m-d H:i:s');
			
			$sql_tmesearch = "INSERT INTO tbl_tmesearch SET
								parentid 		= '".$compData['parentid']."',
								contractid 		= '".$compData['parentid']."',
								compname 		= '".addslashes(stripslashes($compData['companyname']))."',
								paidstatus 		= '".$compData['paid']."',
								pincode			= '".$compData['pincode']."',
								freez 			= '".$compData['freeze']."', 
								mask 			= '".$compData['mask']."',
								mainsource 		= '".addslashes(stripslashes($mainsource))."',
								subsource 		= '".addslashes(stripslashes($subsource))."',
								datesource 		= '".$compData['datesource']."',
								data_source		= '".addslashes(stripslashes($compData['source']))."',
								datasource_date = '".$date."',
								contact_details = '".$compData['contact_details']."',
								arealineage 	= '".addslashes($compData['arealineage'])."',
								data_city		= '".addslashes($compData['data_city'])."',
								tmecode			= '".$compData['tmeCode']."',
								empCode			= '".$compData['ucode']."',
								area			= '".$compData['area']."',
								contact_person	= '".addslashes(stripslashes($compData['contact_person']))."',
								latitude			= '".$compData['latitude']."',
								longitude		= '".$compData['longitude']."',
								landline			= '".$compData['landline']."',
								mobile			= '".$compData['mobile']."'

								ON DUPLICATE KEY UPDATE

								contractid 		= '".$compData['parentid']."',
								compname 		= '".addslashes(stripslashes($compData['companyname']))."',
								paidstatus 		= '".$compData['paid']."',
								pincode			= '".$compData['pincode']."',
								freez 			= '".$compData['freeze']."',
								mask 			= '".$compData['mask']."' , 
								mainsource 		= '".addslashes(stripslashes($mainsource))."',
								subsource 		= '".addslashes(stripslashes($subsource))."',
								datesource 		= '".$compData['datesource']."',
								data_source		= '".addslashes(stripslashes($compData['source']))."',
								datasource_date = '".$date."',
								contact_details = '".$compData['contact_details']."',
								arealineage 	= '".addslashes($compData['arealineage'])."',
								data_city		= '".addslashes($compData['data_city'])."',
								area			= '".$compData['area']."',
								contact_person	= '".addslashes(stripslashes($compData['contact_person']))."',
								latitude			= '".$compData['latitude']."',
								longitude		= '".$compData['longitude']."',
								landline			= '".$compData['landline']."',
								mobile			= '".$compData['mobile']."'";
								
			if($compData['session_tme']	== 'session_tme' && $compData['paid'] == 1)
			{
				$res_tmesearch	=	parent::execQuery($sql_tmesearch, $this->dbConIdc);
			}
			else
			{
				$res_tmesearch	=	parent::execQuery($sql_tmesearch, $this->dbConDjds);
			}
		}
	}
	
	// Function to insert into tbl_company_source
	function insertCompanySource($compData)
	{
		if(!empty($compData['mainsource']))
		{
			$emp_detail	=	"";
			$emp_detail = 	$compData['ucode'].",".$compData['uname'];
			
			$compData['datesource'] = !empty($compData['datesource']) ? $compData['datesource']:date('Y-m-d H:i:s');
			$sql_insert = 	"INSERT INTO tbl_company_source SET 
								contactID  		= '".$compData['parentid']."' ,
								parentid 		= '".$compData['parentid']."' ,
								mainsource 		= '".addslashes($compData['mainsource'])."',
								subsource 		= '".addslashes($compData['subsource'])."',
								datesource 		= '".$compData['datesource']."',
								data_city  		= '".$compData['data_city']."',
								emp_detail 		= '".$emp_detail."',
								paidstatus 		= '".$compData['paid']."'";
			
			
			if($compData['source_update'] == 1){
				$res_insert		=	parent::execQuery($sql_insert, $this->dbConDjds);
			}else{
				if($compData['session_tme']	== 'session_tme' && $compData['paid'] == 1){
					$res_insert		=	parent::execQuery($sql_insert, $this->dbConIdc);
				}else{
					$res_insert		=	parent::execQuery($sql_insert, $this->dbConDjds);
				}
			}
		}
		
		if(isset($compData['universal_source']) && !empty($compData['universal_source']))
		{
			$sql_get_sourcecode		=	"SELECT source_id FROM online_regis1.tbl_source_master WHERE source_name='".addslashes($compData['universal_source'])."' LIMIT 1";
			$res_get_sourcecode		=	parent::execQuery($sql_get_sourcecode, $this->dbConIdc);						
			if(parent::numRows($res_get_sourcecode)>0)
			{
				$row_get_sourcecode = parent::fetchData($res_get_sourcecode);
				$sql_insert_source = "INSERT IGNORE INTO tbl_companysource_consolidated
									SET parentid 		= '".$compData['parentid']."', 
									mainsource_name		= '".addslashes($compData['universal_source'])."',
									mainsource_code 	= '".$row_get_sourcecode['source_id']."',
									subsource			= '".addslashes($compData['subsource'])."',
									datesource			= '".$compData['datesource']."',
									paid				= '".$compData['paid']."',
									data_city			= '".addslashes($compData['data_city'])."',
									updatedby			= '".$compData['ucode']."',
									updater_name		= '".addslashes($compData['uname'])."'";
				$res_insert_source		=	parent::execQuery($sql_insert_source, $this->dbConDjds);		
				
			}				
		}
	}
	
	//function to insert into bus_facility_dump 
	function insertBusFacility($compData)
	{
		if(!empty($compData) && !empty($compData['htmldump']))
		{
			$sql_insert = 	"INSERT INTO bus_facility_dump SET 
								refno 		= '".$compData['parentid']."' ,
								htmldump 	= '".addslashes($compData['htmldump'])."',
								sloganstr 	= '".addslashes($compData['sloganstr'])."'
											
							ON DUPLICATE KEY UPDATE 
							
								htmldump 	= '".addslashes($compData['htmldump'])."',
								sloganstr 	= '".addslashes($compData['sloganstr'])."'";

			$res_insert		=	parent::execQuery($sql_insert, $this->dbConDjds);
		}
	}
	
	function curl_generate_bidcat_details($url) 
	{
		if(!empty($url))
		{
			$ch 		= curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			$resmsg = curl_exec($ch);
			print_r($resmsg);
			curl_close($ch);
		}
	}
	
	
	function general_curl_call($url) 
	{
		if(!empty($url))
		{
			$ch 		= curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			$resmsg = curl_exec($ch);
			//print_r($resmsg);
			curl_close($ch);
		}
	}
	
	// Function to call web_api from cs module
	function curl_web_api($compData,$stage)
	{
		if($stage == '1')
		{
			$sql_insert_api = "INSERT INTO tbl_web_api_backend SET 
				parentid='".$compData['parentid']."',
				done_flag	=	0,
				updatetime  =NOW()
				ON DUPLICATE KEY UPDATE 
				done_flag	=	0,
				updatetime  =NOW()";
			$result_select	=	parent::execQuery($sql_insert_api, $this->dbConDjds);		
		}
		else
		{
			if(!empty($compData))
			{
				$parentid		=	$compData['parentid'];
				$ucode			=	$compData['ucode'];
				$uname			=	$compData['uname'];
				$validationcode	=	$compData['validationcode'];
				$data_city		=	$compData['data_city'];
				$data_city 		= 	strtoupper($data_city);

				switch($data_city)
				{
					case 'MUMBAI' :
							$url = "http://".MUMBAI_CS_API."/";
							$city_indicator = "main_city";
					break;

					case 'AHMEDABAD' :
						$url = "http://".AHMEDABAD_CS_API."/";
						$city_indicator = "main_city";
					break;

					case 'BANGALORE' :
						$url = "http://".BANGALORE_CS_API."/";
						$city_indicator = "main_city";
					break;

					case 'CHENNAI' :
						$url = "http://".CHENNAI_CS_API."/";
						$city_indicator = "main_city";
					break;

					case 'DELHI' :
						$url = "http://".DELHI_CS_API."/";
						$city_indicator = "main_city";
					break;

					case 'HYDERABAD' :
						$url = "http://".HYDERABAD_CS_API."/";
						$city_indicator = "main_city";
					break;

					case 'KOLKATA' :
						$url = "http://".KOLKATA_CS_API."/";
						$city_indicator = "main_city";
					break;

					case 'PUNE' :
						$url = "http://".PUNE_CS_API."/";
						$city_indicator = "main_city";
					break;

					default: 
						$url = "http://".REMOTE_CITIES_CS_API."/";
						$city_indicator = "remote_city";
						
				}

				if(preg_match("/\bjdsoftware.com\b/i", $_SERVER['HTTP_HOST']))
				{
					if($city_indicator == "remote_city")
					{
						$curl_url	= "http://". $_SERVER['HTTP_HOST']."/csgenio/web_services/curl_serverside.php?city_indicator=".$city_indicator."&data_city=".urlencode($data_city)."&parentid=".$parentid."&ucode=".urlencode($ucode)."&validationcode=".$validationcode."&uname=".urlencode($uname)."&insta_activate=2";
					}
					else
					{
						$curl_url	= "http://". $_SERVER['HTTP_HOST']."/csgenio/web_services/curl_serverside.php?city_indicator=".$city_indicator."&data_city=".urlencode($data_city)."&parentid=".$parentid."&ucode=".urlencode($ucode)."&validationcode=".$validationcode."&uname=".urlencode($uname)."&insta_activate=2";
					}
				}
				else
				{
					$curl_url	= $url."/web_services/curl_serverside.php?city_indicator=".$city_indicator."&data_city=".urlencode($data_city)."&parentid=".$parentid."&ucode=".urlencode($ucode)."&validationcode=".$validationcode."&uname=".urlencode($uname)."&insta_activate=2";
				}
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $curl_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch,CURLOPT_TIMEOUT,10);
				$resjson = curl_exec($ch);
				curl_close($ch);
				
				if($resjson ==true)
				{
					$sql_update_flag	=	"UPDATE tbl_web_api_backend SET done_flag='1' WHERE parentid='".$compData['parentid']."'";
					$res_update_flag	=	parent::execQuery($sql_update_flag, $this->dbConDjds);		
				}
			}
		}
	}
	
	// Function to insert into tbl_company_consolidate - log table
	function insertCompanyConsolidate($compData)
	{		
		if(!empty($compData))
		{
			$parentid		=	$compData['parentid'];
			$session_key	=	$compData['session_key'];
			$flg_live		=	'';
			$query_str		=	'';
			$whr			=	'';
			$arr_column_consolidate			=	$this->getColumnList('d_jds.tbl_company_consolidate');
			
			foreach($compData AS $keyField => $valField)
			{
				if(in_array($keyField,$arr_column_consolidate))
					$query_arr[] = $keyField."='".addslashes($valField)."'";
			}
			if(!empty($query_arr) && is_array($query_arr) && count($query_arr) > 0)
					$sql_str	=	implode(",",$query_arr);
			
			/******************* Check if parentid already exist ****************************/
			$query_select	=	"SELECT parentid FROM tbl_company_consolidate WHERE parentid = '".$parentid."'";
				
			//$result_select	=	parent::execQuery($query_select, $this->dbConDjds_slave);
			$result_select	=	parent::execQuery($query_select, $this->dbConDjds);
			
			$numrows		=	mysql_num_rows($result_select);
			
			if($result_select && $numrows > 0)
			{
				$flg_live	=	", flgLive = 1";  // edited
			}
			else
			{	
				$flg_live	=	", flgLive = 0";  // live
			}
			/***********************************************************************************/
			
			/******************* Check if parentid and session_key already exist **************/
			$query_select	=	"SELECT count(1) AS cnt FROM tbl_company_consolidate WHERE parentid = '".$parentid."' AND session_key = '".$session_key."'";
			
			//$result_select	=	parent::execQuery($query_select, $this->dbConDjds_slave);
			$result_select	=	parent::execQuery($query_select, $this->dbConDjds);
			
			$row 			= 	mysql_fetch_assoc($result_select);
			
			if($result_select && $row['cnt'] > 0)
			{
				$query_str 	= "UPDATE tbl_company_consolidate SET "; // edited
				$whr 		= " WHERE parentid = '".$parentid."' AND session_key = '".$session_key."'";
			}
			else
			{	
				$query_str 	= "REPLACE INTO tbl_company_consolidate SET ";  // live
				$whr		=	'';
			}
			/***********************************************************************************/

			$query_str .= $sql_str.$flg_live.$whr;
			$result		=	parent::execQuery($query_str, $this->dbConDjds);
		}
	}
	
	// Function to insert into tbl_paid_narration
	function insertPaidNarration($compData)
	{
		if(!empty($compData) && !empty($compData['narration']))
		{
			$narration = date("l dS M, Y H:i:s")."\n".$compData['narration']."\n - ".$compData['uname'];
			$narration = nl2br($narration);
			
			$sql_insert	=	"INSERT INTO tbl_paid_narration SET 
							contractid	=	'".$compData['parentid']."',
							narration	=	'".addslashes($narration)."',
							creationDt	=	now(),
							createdBy	=	'".$compData['ucode']."',
							parentid	=	'".$compData['parentid']."',
							data_city	=	'".addslashes($compData['data_city'])."' ";
			
			if($compData['session_tme']	== 'session_tme' && $compData['paid'] == 1)
			{
				$result		=	parent::execQuery($sql_insert, $this->dbConIdc);
			}
			else
			{
				$result		=	parent::execQuery($sql_insert, $this->dbConDjds);
			}
		}
	}
	
	// Function to insert into tbl_tme_np
	function insertTmeNp($compData)
	{
		if(!empty($compData))
		{
			$sql_insert	=	"INSERT INTO tbl_tme_np SET 
							parentid 	= 	'".$compData['parentid']."',
							tmeid		=	'".$compData['tme_code']."',
							datetime	=	now()";
			$result		=	parent::execQuery($sql_insert, $this->dbConDjds);
		}
	}
	
	
	function updateMovieTimeLog($compData)
	{
		if(!empty($compData) && !empty($compData['category']) && !empty($compData['sloganstr']))
		{
			$logArr 			= array();
			$finalLogData 		= array();
			$temp_catids_arr 	= array();
			$new_logData_arr 	= array();
			$old_logData_arr 	= array();
			
			$catidsarr			= explode("/,/",trim($compData['category'],'/')); 	
			$catidsarr 			= array_filter($catidsarr);
			$temp_catid_arr 	= $catidsarr;	
			
			$final_log_values = array();
				
			$slogan_arr = explode("|$|",$compData['sloganstr']);
			$slogan_arr = array_filter($slogan_arr);
			
			// To get Original catids & Slogan From LIVE ---- starts here
			$original_arr 			= array();
			$extra_moives_catid_arr = array();
			
			$sql_original = "SELECT sloganstr FROM bus_facility_dump WHERE refno = '".$compData['parentid']."'";
			//$res_original = parent::execQuery($sql_original, $this->dbConDjds_slave);
			$res_original = parent::execQuery($sql_original, $this->dbConDjds);
			
			if($res_original && mysql_num_rows($res_original)>0)
			{
				$row_original 			= mysql_fetch_assoc($res_original);
				$original_slogan_arr 	= explode("|$|",$row_original['sloganstr']);
				$original_slogan_arr 	= array_filter($original_slogan_arr);
			}
			
			// For companymasterclass
			if($this->insert_arr['session_tme']	== 'session_tme' && $this->insert_arr['paid'] == 1)
			{
				$extradetails_table = 'tbl_companymaster_extradetails';
			}
			else
			{
				$extradetails_table = $this->getFinalTableName('tbl_companymaster_extradetails',$this->tableno);
			}
			// End
			$sql_old_cat = "SELECT catidlineage FROM ".$extradetails_table." WHERE parentid ='".$compData['parentid']."'";
			//$res_old_cat = parent::execQuery($sql_old_cat, $this->dbConIro_slave);
			$res_old_cat = parent::execQuery($sql_old_cat, $this->dbConIro);
			
			if($res_old_cat && mysql_num_rows($res_old_cat)>0)
			{
				$row_old_cat 		= 	mysql_fetch_assoc($res_old_cat);
				$extra_catids_arr	=	explode("/,/",trim($row_old_cat['catidlineage'],'/'));
			}
			// To get Original catids & Slogan From LIVE ---- Ends here
			
			if(COUNT($extra_catids_arr) > 0)
			{
				$catidsarr = array_merge($extra_catids_arr, $catidsarr);
				$catidsarr = array_filter($catidsarr);
				$catidsarr = array_unique($catidsarr);
			}

			$catids = implode(",",$catidsarr);
			
			// To find only movies related catis  -- Starts here
			//$sql_qry = "SELECT DISTINCT(catid) as catid FROM tbl_categorymaster_generalinfo WHERE catid in ('".$catids."') AND (category_verticals & 8 = 8)";
			//$res_qry  = parent::execQuery($sql_qry, $this->dbConDjds_slave);
			//$res_qry  = parent::execQuery($sql_qry, $this->dbConDjds);
			$cat_params = array();
			$cat_params['page'] 		= 'insertLiveClass';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'catid';			

			$where_arr  	=	array();
			if($catids!=''){
				$where_arr['catid']					= $catids;
				$where_arr['category_verticals']	= '8';
				$cat_params['where']			= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}
			
			$moives_catid_arr = array();
			
			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results']) > 0)
			{
				foreach($cat_res_arr['results']  as $key =>$row_qry)
				{
					foreach ($slogan_arr as $key=>$value)
					{
						if(strstr($value,$row_qry['catid']))
						{
							$moives_catid_arr[$row_qry['catid']] = $value;
						}
					}
					if(count($extra_catids_arr) > 0)
					{
						foreach ($original_slogan_arr as $key=>$value)
						{
							if(strstr($value,$row_qry['catid']))
							{
								$extra_moives_catid_arr[$row_qry['catid']] = $value;
							}
						}
					}
					$movie_catids[] = $row_qry['catid'];
				}
			}
			// To find only movies related catis  -- Ends here
			
			if(COUNT($moives_catid_arr) > 0)
			{
				foreach($moives_catid_arr as $key => $value)
				{
					if(strcmp(trim($value),trim($extra_moives_catid_arr[$key])) != 0)
					{
						$new_logData_arr[$key] = $moives_catid_arr[$key];
						$old_logData_arr[$key] = $extra_moives_catid_arr[$key];
					}
				}
			}
			//print "<pre>";print_r($new_logData_arr);exit;
			foreach($new_logData_arr as $movie_key => $movie_value)
			{
				$new_temp = array();
				$new_temp = explode('~~~',$movie_value);
				$new_logArr[$new_temp[2]] = $new_temp;
			}
			
			foreach($old_logData_arr as $movie_key => $movie_value)
			{
				$old_temp = array();
				$old_temp = explode('~~~',$movie_value);
				$old_logArr[$old_temp[2]] = $old_temp;
			}
			
			if(COUNT($new_logArr) > 0)
			{
				foreach($new_logArr as $log_key => $log_value)
				{
					if($log_value[1] !='' || $old_logArr[$log_key][1] !='')
					{
						$final_log_values[$log_key][catid] 		= $log_key;
						$final_log_values[$log_key][catname] 	= $log_value[0];
						$final_log_values[$log_key][oldvlaues] 	= $old_logArr[$log_key][1];
						$final_log_values[$log_key][newvlaues] 	= $log_value[1];
					}
				}
			}
			
			$insert_log_new_values 	= '';
			$city 					= '';
			
			if($compData['is_remote'] == 'REMOTE')
			{
				$city = $compData['data_city'];
			}
			else
			{
				$city = $compData['city'];
			}
			
			if(COUNT($final_log_values) > 0)
			{
				foreach($final_log_values as $log_key => $log_value)
				{
					// inserting new & old values
					if($insert_log_new_values == '')
					{
						$insert_log_new_values = "('".$compData['parentid']."','".$log_value[catid]."','".$log_value[catname]."','".$log_value[oldvlaues]."','".$log_value[newvlaues]."','".date('Y-m-d H:i:s')."','".$compData['ucode']."','".$city."','".$compData['source']."')";
					}
					else
					{
						$insert_log_new_values .= ",('".$compData['parentid']."','".$log_value[catid]."','".$log_value[catname]."','".$log_value[oldvlaues]."','".$log_value[newvlaues]."','".date('Y-m-d H:i:s')."','".$compData['ucode']."','".$city."','".$compData['source']."')";
					}
				}
				$sql_log = "INSERT INTO tbl_movietimes_log (parentid, catid, catname, oldtimings, newtimings, updatedOn, updatedBy, city,dept) VALUES ".$insert_log_old_values.$insert_log_new_values;
				$res_log = parent::execQuery($sql_log, $this->dbConDjds);
			}
		}
	}
	
	function updateNationalListing($compData)
	{
		if(!empty($compData) && $compData['multicity'] == 'multicity')
		{
			$sql_extra_national 	= "UPDATE db_national_listing.tbl_companymaster_extradetails_national
										 SET
										 national_catidlineage			=	'".$compData['national_catidlineage']."',
										 national_catidlineage_search	=	'".$compData['national_catidlineage']."',
										 hotcategory					=	'".$compData['hotcategory']."'
										 WHERE 
										 parentid = '".$compData['parentid']."'";
			$res_extra_national 	= parent::execQuery($sql_extra_national, $this->dbConIdc);
			
			$sql_compsearch_national 	= "UPDATE db_national_listing.tbl_companymaster_search_national SET national_catidlineage_search = '".$compData['national_catidlineage']."' WHERE parentid='".$compData['parentid']."'";
			$res_compsearch_national 	= parent::execQuery($sql_compsearch_national, $this->dbConIdc);
		}
	}
	
	function landlineMappedCat($compData)
	{		
		if(!empty($compData))
		{
			$parentid		=	$compData['parentid'];
			$session_key	=	$compData['session_key'];
			$flg_live		=	'';
			$query_str		=	'';
			$whr			=	'';
			$arr_column_consolidate		=	$this->getColumnList('d_jds.tbl_company_consolidate');
			
			foreach($compData AS $keyField => $valField)
			{
				if(in_array($keyField,$arr_column_consolidate))
					$query_arr[] = $keyField."='".addslashes($valField)."'";
			}
			if(!empty($query_arr) && is_array($query_arr) && count($query_arr) > 0)
					$sql_str	=	implode(",",$query_arr);
			
			/******************* Check if parentid already exist ****************************/
			$query_select	=	"SELECT parentid FROM tbl_company_consolidate WHERE parentid = '".$parentid."'";
				
			$result_select	=	parent::execQuery($query_select, $this->dbConDjds);
			
			$numrows		=	mysql_num_rows($result_select);
			
			if($result_select && $numrows > 0)
			{
				$flg_live	=	", flgLive = 1";  // edited
			}
			else
			{	
				$flg_live	=	", flgLive = 0";  // live
			}
			/***********************************************************************************/
			
			/******************* Check if parentid and session_key already exist **************/
			$query_select	=	"SELECT count(1) AS cnt FROM tbl_company_consolidate WHERE parentid = '".$parentid."' AND session_key = '".$session_key."'";
			
			$result_select	=	parent::execQuery($query_select, $this->dbConDjds);
			
			$row 			= 	mysql_fetch_assoc($result_select);
			
			if($result_select && $row['cnt'] > 0)
			{
				$query_str 	= "UPDATE tbl_company_consolidate SET "; // edited
				$whr 		= " WHERE parentid = '".$parentid."' AND session_key = '".$session_key."'";
			}
			else
			{	
				$query_str 	= "REPLACE INTO tbl_company_consolidate SET ";  // live
				$whr		=	'';
			}
			/***********************************************************************************/

			$query_str .= $sql_str.$flg_live.$whr;
			$result		=	parent::execQuery($query_str, $this->dbConDjds);
			
			return;
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
				if((!empty($final_catid)) && (intval($final_catid)>0))
				{
					$final_catids_arr[]	= $final_catid;
				}
			}
			$final_catids_arr = array_filter($final_catids_arr);
			$final_catids_arr = array_unique($final_catids_arr);
		}
		return $final_catids_arr;	
	}
	function call_curl_post($curlurl,$data)
	{	
		#echo $curlurl.'?'.$data;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curlurl);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$content  = curl_exec($ch);
		curl_close($ch);
		return $content;
	}
	
	function updateSphinxId($sphinx_id)
	{
		if(!empty($this->parentid))
		{
			if($this->insert_arr['session_tme']	== 'session_tme' && $this->insert_arr['paid'] == 1){
				$required_conn	=	$this->dbConIdc;
			}else{
				$required_conn	=	$this->dbConIro;
			}
			$sqlChkGeninfoEntry = "SELECT parentid FROM tbl_companymaster_generalinfo WHERE parentid = '".$this->parentid."'";
			$resChkGeninfoEntry	= parent::execQuery($sqlChkGeninfoEntry, $required_conn);
			if($resChkGeninfoEntry && parent::numRows($resChkGeninfoEntry)){			
				$sqlUpdtGeninfoSphinxId = "UPDATE tbl_companymaster_generalinfo SET sphinx_id = '".$sphinx_id."' WHERE parentid = '".$this->parentid."'";
				$resUpdtGeninfoSphinxId	= parent::execQuery($sqlUpdtGeninfoSphinxId, $required_conn);
				if($resUpdtGeninfoSphinxId){
					$sqlUpdtExtraSphinxId = "UPDATE tbl_companymaster_extradetails SET sphinx_id = '".$sphinx_id."' WHERE parentid = '".$this->parentid."'";
					$resUpdtExtraSphinxId	= parent::execQuery($sqlUpdtExtraSphinxId, $required_conn);
				}
			}
		}
	}
	function insertContractDetails($parentid,$ucode,$dept,$finance_data){
		
		if(trim($parentid)!= ''){
		
			if($this->insert_arr['session_tme']	== 'session_tme' && $this->insert_arr['paid'] == 1){
				$generalinfo_table 	= 'tbl_companymaster_generalinfo';
				$extradetails_table = 'tbl_companymaster_extradetails';
			}else{
				$generalinfo_table 		= $this->getFinalTableName('tbl_companymaster_generalinfo',$this->tableno);
				$extradetails_table 	= $this->getFinalTableName('tbl_companymaster_extradetails',$this->tableno);
			}
			
			$sql_gen = "SELECT parentid,companyname,data_city FROM ".$generalinfo_table." WHERE parentid='".$parentid."'";
			if(strtolower($dept) == 'cs' || ((strtolower($dept) == 'tme' || strtolower($dept) == 'me') && $this->insert_arr['paid'] == 0)){
				//$qry_gen = parent::execQuery($sql_gen, $this->dbConIro);
				$comp_params = array();
				$comp_params['data_city'] 	= $this->data_city;
				$comp_params['table'] 		= 'gen_info_id,extra_det_id';		
				$comp_params['parentid'] 	= $parentid;
				$comp_params['fields']		= 'parentid,companyname,data_city,original_date,updatedOn';
				$comp_params['action']		= 'fetchdata';
				$comp_params['page']		= 'insertLiveClass';

				$comp_api_res  	='';
				$comp_api_arr	= array();
				$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
				if($comp_api_res!=''){
					$comp_api_arr 	= json_decode($comp_api_res,TRUE);
				}
				if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['gen_info_id']=='1')
				{
					$row_gen 	= $comp_api_arr['results']['data'][$parentid];			
				}
			}else{
				$qry_gen = parent::execQuery($sql_gen, $this->dbConIdc);
				$row_gen = mysql_fetch_assoc($qry_gen);
				
			}
				$companyname= $row_gen['companyname'];
				$parentid	= $row_gen['parentid'];
				$data_city	= $row_gen['data_city'];
				
						
			$sql_ext = "SELECT original_date, updatedOn FROM ".$extradetails_table." WHERE parentid='".$parentid."'";
			if(strtolower($dept) == 'cs' || ((strtolower($dept) == 'tme' || strtolower($dept) == 'me') && $this->insert_arr['paid'] == 0)){
				//$qry_ext = parent::execQuery($sql_ext, $this->dbConIro);
				$create_date= $row_gen['original_date'];
				$up_date	= $row_gen['updatedOn'];	
				
			}else{
				$qry_ext = parent::execQuery($sql_ext, $this->dbConIdc);
				$row_ext = mysql_fetch_assoc($qry_ext);
				
				$create_date= $row_ext['original_date'];
				$up_date	= $row_ext['updatedOn'];
			}	
			
			$budget_det		= 0;
			$campaign_det	= 0;
			
			if(count($finance_data)){
				$camparr		= array_keys($finance_data);
				$campaign_det	= implode(",",array_keys($finance_data));
				foreach($camparr as $campid){			
					$budget_det+= $finance_data[$campid]['budget'];
				}
			}
			
			$sql_record	= "INSERT INTO db_contract_record.tbl_contract_details SET 
							parentid	= '".$parentid."',
							companyname = '".addslashes($companyname)."',
							empcode		= '".$ucode."',
							dept		= '".$dept."', 
							update_date	= '".$up_date."',
							create_date	= '".$create_date."',
							data_city	= '".addslashes($data_city)."',
							amount		= '".$budget_det."',
							campaignid	= '".$campaign_det."',
							insert_date	= '".date("Y-m-d H:i:s")."'";
			$qry_record	= parent::execQuery($sql_record,$this->dbConIdc);
		}
	}

function purePackageBiddingtableUpdate()
	{
		
		$finsql = "select campaignid,balance,version,bid_perday from tbl_companymaster_finance where parentid='".$this->parentid."' and campaignid in (1,2) and  balance>0";
		//echo $finsql
		$finrs 	= parent::execQuery($finsql, $this->finance);
		//echo "mysql_num_rows".mysql_num_rows($finrs)			;
		if(mysql_num_rows($finrs))
		{
		
			$fpbalance=0;
			$packbalance=0;
			
		
			while($fintemparr = mysql_fetch_assoc($finrs))
			{			
				if($fintemparr['campaignid']==1)
				{
					$packbalance=$fintemparr['balance'];
					$bidperday	=$fintemparr['bid_perday'];
					$packbalance=$fintemparr['balance'];
					$version	=$fintemparr['version'];
				}

				if($fintemparr['campaignid']==2)
				{
					$fpbalance=$fintemparr['balance'];
				}				
				
			}		

			
			
			if($packbalance>1 && $fpbalance<=0 )
			{				
				$curlobj = new CurlClass();
			
				$Inparray['parentid']	=	$this->parentid;
				$Inparray['version']	=	$version;
				$Inparray['data_city']	=	urldecode($this->insert_arr['data_city']);
				$Inparray['bidperday']	= 	$bidperday;
				
				$Inparray['astatus']	=	2; 
				$Inparray['astate']		=	15;
				
				
				$configclassobj= new configclass();
				$urldetails= $configclassobj->get_url(urldecode($this->insert_arr['data_city']));

				$curlurl=$urldetails['jdbox_service_url'].'invMgmt.php';
				
				//echo "curlurl".$curlurl;print_r($Inparray);
				$curlobj->setOpt(CURLOPT_CONNECTTIMEOUT, 30);
				$curlobj->setOpt(CURLOPT_TIMEOUT, 900);
				$output = $curlobj->post($curlurl,$Inparray,1);
				//echo 'output'.$output;
			}

			
		}
	}
	function saveDialerInfo(){
		$sqlInsertDialerData = "INSERT INTO tbl_dialer_data SET 
								parentid 		= '".$this->insert_arr['parentid']."',
								stdcode 		= '".$this->insert_arr['stdcode']."',
								landline 		= '".$this->insert_arr['landline']."',
								mobile 			= '".$this->insert_arr['mobile']."',
								companyname 	= '".addslashes(stripslashes($this->insert_arr['companyname']))."',
								area 			= '".addslashes(stripslashes($this->insert_arr['area']))."',
								country 		= '".addslashes(stripslashes($this->insert_arr['country']))."',
								state 			= '".addslashes(stripslashes($this->insert_arr['state']))."',
								city 			= '".addslashes(stripslashes($this->insert_arr['city']))."',
								data_source 	= 'JDA',
								data_tag 		= 'hot',
								trickle_flag 	= '0',
								datasource_date = '".date('Y-m-d h:i:s')."',
								jdacode 		= '".$this->insert_arr['ucode']."'
								ON DUPLICATE KEY UPDATE
								stdcode 		= '".$this->insert_arr['stdcode']."',
								landline 		= '".$this->insert_arr['landline']."',
								mobile 			= '".$this->insert_arr['mobile']."',
								companyname 	= '".addslashes(stripslashes($this->insert_arr['companyname']))."',
								area 			= '".addslashes(stripslashes($this->insert_arr['area']))."',
								country 		= '".addslashes(stripslashes($this->insert_arr['country']))."',
								state 			= '".addslashes(stripslashes($this->insert_arr['state']))."',
								city 			= '".addslashes(stripslashes($this->insert_arr['city']))."',
								data_source 	= 'JDA',
								data_tag 		= 'hot',
								trickle_flag 	= '0',
								datasource_date = '".date('Y-m-d h:i:s')."',
								jdacode 		= '".$this->insert_arr['ucode']."'";
		$resInsertDialerData = parent::execQuery($sqlInsertDialerData, $this->dbConDjds);
		
		$sqlInsertHotData = "INSERT INTO tbl_hotData SET 
							 parentid 			= '".$this->insert_arr['parentid']."',
							 companyname 		= '".addslashes(stripslashes($this->insert_arr['companyname']))."',
							 source 			= 'JDA',
							 contact_details 	= '".$this->insert_arr['mobile']."',
							 data_city 			= '".addslashes(stripslashes($this->insert_arr['data_city']))."',
							 source_date 		= '".date('Y-m-d h:i:s')."',
							 create_date 		= '".date('Y-m-d h:i:s')."',
							 contact_person 	= '".addslashes(stripslashes($this->insert_arr['contact_person']))."'";
		$resInsertHotData = parent::execQuery($sqlInsertHotData, $this->dbConDjds);
	}
}

function getData($url)
{
	$ch 		= curl_init();
	$timeout 	= 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data 		= curl_exec($ch);
	curl_close($ch);
	return $data;
}

function checkBadWords($string)
{
	if (isset($string) && $string != '') 
	{
		$field_val 	= $string;

		$lines 		= file('profanity.txt');
		$all_words 	= array();
		$arr_all_words = array();
		$i			= 0;
		$err_msg 	= "Profanity";

		foreach ($lines as $line_num => $line)
		{
			$explode = explode(",",$line);
			foreach ($explode as $k => $v) 
			{
				$v1 = trim(strtolower($v));
				if(!empty($v1))
					$arr_all_words[$i] = $v1;
				$i++;
			}
		}

		$split_values 	= explode(" ", $field_val);
		$split_values_2 = explode("_", $field_val);
		$split_values_3 = explode(",", $field_val);
		$split_values_4 = explode(".", $field_val);
		$split_values_5 = explode("@", $field_val);
		
		foreach ($split_values as $kie => $val) 
		{
			$val = strtolower($val);
			
			if(in_array($val,$arr_all_words))
			{
				$profanity_flag = 1;
			}
		}
		unset($kie,$val);

		foreach ($split_values_2 as $kie => $val) 
		{
			$val = strtolower($val);
			if(in_array($val,$arr_all_words))
			{
				$profanity_flag = 1;
			}
		}
		unset($kie,$val);
		
		foreach ($split_values_3 as $kie => $val) 
		{
			$val = strtolower($val);
			if(in_array($val,$arr_all_words))
			{
				$profanity_flag = 1;
			}
		}
		unset($kie,$val);

		foreach ($split_values_4 as $kie => $val) 
		{
			$val = strtolower($val);
			if(in_array($val,$arr_all_words))
			{
				$profanity_flag = 1;
			}
		}
		unset($kie,$val);

		foreach ($split_values_5 as $kie => $val) 
		{
			$val = strtolower($val);
			if(in_array($val,$arr_all_words))
			{
				$profanity_flag = 1;
			}
		}
		unset($kie,$val);
		
		if($profanity_flag == 1)
			return $err_msg;
		else
			return 'Correct';
	}
	function fetchCSLiveUrl($data_city){
		switch(strtoupper($data_city)){
			case 'MUMBAI' :
				$url = "http://".MUMBAI_CS_API."/";
			break;
			
			case 'DELHI' :
				$url = "http://".DELHI_CS_API."/";
			break;
			
			case 'KOLKATA' :
				$url = "http://".KOLKATA_CS_API."/";
			break;
			
			case 'BANGALORE' :
				$url = "http://".BANGALORE_CS_API."/";
			break;
			
			case 'CHENNAI' :
				$url = "http://".CHENNAI_CS_API."/";
			break;
			
			case 'PUNE' :
				$url = "http://".PUNE_CS_API."/";
			break;
			
			case 'HYDERABAD' :
				$url = "http://".HYDERABAD_CS_API."/";
			break;
			
			case 'AHMEDABAD' :
				$url = "http://".AHMEDABAD_CS_API."/";
			break;
			
			default: 
				$url = "http://".REMOTE_CITIES_CS_API."/";
		}
		return $cs_url;
	}
}
?>
