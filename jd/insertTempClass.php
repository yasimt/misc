<?php
/**
 * Filename : insertLiveClass.php
 * Date		: 19/08/2013
 * Author	: Neelam Rasal
 * Purpose	: This file is used to insert data into all live tables
 * param	: $insert_arr - this array will contain all fields data which need to insert into live tables
 * */
class insertToTemp extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $dbConIdc    	= null;
	var  $insert_arr  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	var  $tableno		= null;
	
	function __construct($insert_arr)
	{		
		$this->insert_arr = $insert_arr;		
		$this->setServers();
		
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
		
		
		if(trim($this->insert_arr['data_city']) != "" && $this->insert_arr['data_city'] != null)
		{
			$this->data_city  = $this->insert_arr['data_city']; //initialize datacity
		}
		/* Code for companymasterclass logic ends */		
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
				$sql			= "SELECT category_name as catname FROM tbl_categorymaster_generalinfo WHERE category_name = '".addslashes(trim($business_name))."' OR category_name = '".addslashes($b1)."'";				
				$res_catname 	= parent::execQuery($sql, $this->dbConDjds);
				$num_rows		= mysql_num_rows($res_catname);
				if($res_catname && $num_rows > 0)
				{
					$err_msg = 'Companyname is matching with category';
					$arr_errors['error'][] = $err_msg;
				}
				else
				{
					$sql_cat ="SELECT category_name AS catname FROM d_jds.tbl_categorymaster_generalinfo
							WHERE category_name= '".addslashes(trim($business_name))."'
							OR REPLACE(category_name,' ','') = '".addslashes(trim($business_name))."'
							UNION
							SELECT category_name AS catname FROM d_jds.tbl_categorymaster_generalinfo
							WHERE category_name='".addslashes($b1)."'
							OR REPLACE(category_name,' ','') = '".addslashes($b1)."'";
					
					$res_cat 	= parent::execQuery($sql_cat, $this->dbConDjds);
					$num_rows	= mysql_num_rows($res_cat);
					if($res_cat && $num_rows > 0)
					{
						$err_msg = 'Companyname is matching with category';
						$arr_errors['error'][] = $err_msg;
					}
				}
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
	
	
	// Function to fetch sphinx_id
	function getContractSphinxId($parentid) 
	{		
		$sphinx_id = 0;
		
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

		$extradetails_sphinxid_query 	= " SELECT sphinx_id FROM ".$extradetails_table." WHERE parentid = '" . $parentid . "' ";
		
		//$sphinxid_res   				= parent::execQuery($extradetails_sphinxid_query, $this->dbConIro_slave);
		$sphinxid_res   				= parent::execQuery($extradetails_sphinxid_query, $this->dbConIro);
		
		$sphinxid_rows  				= mysql_num_rows($sphinxid_res);

		if (!$sphinxid_rows) {

			$idgenerator_sphinxid_query		= " SELECT sphinx_id FROM tbl_id_generator WHERE parentid = '" . $parentid . "' ";
			
			//$sphinxid_res               	= parent::execQuery($idgenerator_sphinxid_query, $this->dbConIro_slave);
			$sphinxid_res               	= parent::execQuery($idgenerator_sphinxid_query, $this->dbConIro);
			
			$sphinxid_rows              	= mysql_num_rows($sphinxid_res);
		}

		if ($sphinxid_rows) 
		{
			$sphinxid_values      		 	= mysql_fetch_assoc($sphinxid_res);
			
			$sphinx_id            		 	= $sphinxid_values['sphinx_id'];
			
			$fun_sphinxid_parentid 			= $sphinx_id;
		}

		if (intval($sphinx_id) == 0) {

			echo '<h3>Sphinx Id not found for this parentid : ' . $parentid . ' </h3>';
			exit();

		}
		return $sphinx_id;
	}
	
	// Function to fetch national catid
	function getNationalCatlineage($catid)
	{
		if(!empty($catid))
		{
			$catid_list				=	str_replace("/","'",$catid);
			$sql_national_catids 	= "SELECT catid,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN (".$catid_list.") AND isdeleted = 0 AND mask_status = 0 AND active_flag = 1 AND biddable_type = 1 GROUP BY catid,category_name ORDER BY callcount DESC";
						
			//$res_national_catids	=	parent::execQuery($sql_national_catids, $this->dbConDjds_slave);
			$res_national_catids	=	parent::execQuery($sql_national_catids, $this->dbConDjds);
			
			if($res_national_catids && mysql_num_rows($res_national_catids))
			{
				while($row_national_catids = mysql_fetch_assoc($res_national_catids))
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
		$catidliststr 	= implode(",",$catidlistarr);

		$sql = "SELECT group_concat( DISTINCT associate_national_catid) as associate_national_catid FROM tbl_categorymaster_generalinfo where catid in (".$catidliststr.") AND catid > 0 AND category_name != '' ";
		//$res = parent::execQuery($sql, $this->dbConDjds_slave);
		$res = parent::execQuery($sql, $this->dbConDjds);			
		
		if($res && mysql_num_rows($res))
		{
			$row = mysql_fetch_assoc($res);
			if($row['associate_national_catid'])
			{
				
				$associate_national_catid_arr = explode(',',$row['associate_national_catid']);			
				
				$associate_national_catid_arr = array_unique($associate_national_catid_arr);
				$associate_national_catid_arr = array_filter($associate_national_catid_arr);
				$associate_national_catid_str = implode(",",$associate_national_catid_arr);
				
				// fetching the catid from national_catid and removing original catid
				$sql = "SELECT group_concat( DISTINCT catid) as parent_categories FROM tbl_categorymaster_generalinfo where national_catid IN (".$associate_national_catid_str.") and catid NOT IN (".$catidliststr.") AND catid > 0 AND category_name != '' ";
			
				//$res = parent::execQuery($sql, $this->dbConDjds_slave);
				$res = parent::execQuery($sql, $this->dbConDjds);
				
				if($res && mysql_num_rows($res))
				{
					$row = mysql_fetch_assoc($res);
					if($row['parent_categories'])
					{
						$parent_categories_arr = explode(',',$row['parent_categories']);
						
						$parent_categories_arr = array_unique($parent_categories_arr);
						$parent_categories_arr = array_filter($parent_categories_arr);					
					}
				}			
			}
		}
		return $parent_categories_arr;
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
	
	
	function insertBform()
	{
		require_once("library/bformclass.php");
		//require_once("library/classgeocode.php");
		$bformClass_obj= new bformClass($this->insert_arr);
		//$geocode_obj = new Geocode($dbarr, $genio_variables['parentid'], $_SESSION['module'], $_REQUEST['flgNew'], $_POST);
		
		$bformClass_obj->populateShadowTable($this->insert_arr,$this->dbConIro,$this->dbConDjds);
	}
	
	
	function InsertIntoTempTables()
	{
		//echo "InsertIntoTempTables called";
		
		$pagename = strtolower($this->insert_arr['pagename']);
		
		switch($pagename)
		{
			case 'bform':
			$this->insertBform();
		}
	}
	
	
}



?>
